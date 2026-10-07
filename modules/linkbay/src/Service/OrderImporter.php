<?php
/**
 * Copyright since 2024 Beassisted
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/afl-3.0.php
 *
 * @author    Beassisted
 * @copyright Copyright (c) 2024-present Beassisted
 * @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

declare(strict_types=1);

namespace LinkBay\Service;

use LinkBay\Config\Config;
use LinkBay\Entity\LinkBayLog;
use LinkBay\Repository\LinkBayLogRepository;
use LinkBay\Repository\LinkBayProductRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Imports eBay orders into PrestaShop.
 *
 * Orders are fetched through the Sell Fulfillment API and recreated as
 * PrestaShop orders: a shared guest customer, one address per order, a cart,
 * and an order marked as paid (the payment already happened on eBay). The
 * eBay order id is stored in the order reference to prevent duplicates.
 */
final class OrderImporter
{
    private const GUEST_EMAIL = 'ebay-buyer@example.com';
    private const REFERENCE_PREFIX = 'eBay-';

    /**
     * eBay marketplace id -> payment label.
     *
     * The Sell Fulfillment API returns a marketplace id such as "EBAY_FR" or
     * "EBAY_IT". We translate it into the familiar "EBAY.FR" / "EBAY.IT" label
     * so the payment recorded on the PrestaShop order is always affiliated to
     * the marketplace where the sale actually happened.
     */
    private const MARKETPLACE_LABELS = [
        'EBAY_US' => 'EBAY.COM',
        'EBAY_MOTORS' => 'EBAY.COM',
        'EBAY_GB' => 'EBAY.CO.UK',
        'EBAY_AU' => 'EBAY.COM.AU',
        'EBAY_FR' => 'EBAY.FR',
        'EBAY_IT' => 'EBAY.IT',
        'EBAY_DE' => 'EBAY.DE',
        'EBAY_ES' => 'EBAY.ES',
        'EBAY_CA' => 'EBAY.CA',
        'EBAY_AT' => 'EBAY.AT',
        'EBAY_BE' => 'EBAY.BE',
        'EBAY_BENL' => 'EBAY.BE',
        'EBAY_CH' => 'EBAY.CH',
        'EBAY_IE' => 'EBAY.IE',
        'EBAY_NL' => 'EBAY.NL',
        'EBAY_PL' => 'EBAY.PL',
    ];

    private EbayApiClient $apiClient;
    private LinkBayProductRepository $productLinkRepository;
    private LinkBayLogRepository $logRepository;
    private \Context $context;

    public function __construct(
        EbayApiClient $apiClient,
        LinkBayProductRepository $productLinkRepository,
        LinkBayLogRepository $logRepository,
        \Context $context,
    ) {
        $this->apiClient = $apiClient;
        $this->productLinkRepository = $productLinkRepository;
        $this->logRepository = $logRepository;
        $this->context = $context;
    }

    /**
     * Imports the eBay orders created after the last successful sync.
     *
     * @return array{imported: int, cancelled: int, skipped: int, errors: int}
     */
    public function importOrders(): array
    {
        $summary = ['imported' => 0, 'cancelled' => 0, 'skipped' => 0, 'errors' => 0];

        if ((string) \Configuration::get(Config::DEMO_MODE) === '1') {
            return $summary;
        }

        $since = (string) \Configuration::get(Config::LAST_ORDER_SYNC);

        try {
            $orders = $this->apiClient->getOrders($since !== '' ? $since : null);
        } catch (\Throwable $e) {
            $this->log(0, '', 'order_import', LinkBayLog::STATUS_ERROR, 'Unable to fetch eBay orders: ' . $e->getMessage());
            ++$summary['errors'];

            return $summary;
        }

        // Auto-detect the seller's eBay username from the first order: this
        // removes a setup step — the merchant only needs to paste their code
        // and connect.
        if ((string) \Configuration::get(Config::EBAY_USERNAME) === '') {
            foreach ($orders as $order) {
                $sellerId = trim((string) ($order['sellerId'] ?? ''));
                if ($sellerId !== '') {
                    \Configuration::updateValue(Config::EBAY_USERNAME, $sellerId);
                    break;
                }
            }
        }

        $latest = $since;

        foreach ($orders as $order) {
            $orderId = (string) ($order['orderId'] ?? '');
            if ($orderId === '') {
                continue;
            }

            // A cancellation is never re-imported: the existing PrestaShop
            // order status is updated instead.
            if ($this->isCancelled($order)) {
                if ($this->cancelOrder($orderId)) {
                    ++$summary['cancelled'];
                } else {
                    ++$summary['skipped'];
                }
                continue;
            }

            if ($this->isImported($orderId)) {
                ++$summary['skipped'];
                continue;
            }

            try {
                $this->importOrder($order);
                ++$summary['imported'];
                $this->log(0, $orderId, 'order_import', LinkBayLog::STATUS_SUCCESS, 'eBay order imported.');
            } catch (\Throwable $e) {
                ++$summary['errors'];
                $this->log(0, $orderId, 'order_import', LinkBayLog::STATUS_ERROR, 'Order import failed: ' . $e->getMessage());
            }

            $creation = (string) ($order['creationDate'] ?? '');
            if ($creation !== '' && ($latest === '' || $creation > $latest)) {
                $latest = $creation;
            }
        }

        \Configuration::updateValue(Config::LAST_ORDER_SYNC, $latest !== '' ? $latest : date('c'));

        return $summary;
    }

    /**
     * Imports a single eBay order immediately (webhook-driven).
     *
     * @return bool true when the order was imported, false when skipped or failed
     */
    public function importOrderById(string $orderId): bool
    {
        if ($orderId === '' || (string) \Configuration::get(Config::DEMO_MODE) === '1') {
            return false;
        }

        try {
            $order = $this->apiClient->getOrder($orderId);

            // A cancelled eBay order must not be re-imported: update the
            // existing PrestaShop order status instead.
            if ($this->isCancelled($order)) {
                return $this->cancelOrder($orderId);
            }

            if ($this->isImported($orderId)) {
                return false;
            }

            $this->importOrder($order);
            $this->log(0, $orderId, 'order_import', LinkBayLog::STATUS_SUCCESS, 'eBay order imported (instant webhook).');

            return true;
        } catch (\Throwable $e) {
            $this->log(0, $orderId, 'order_import', LinkBayLog::STATUS_ERROR, 'Order import failed: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Whether the eBay order carries a definitive cancellation.
     *
     * @param array<string, mixed> $order
     */
    private function isCancelled(array $order): bool
    {
        return strtoupper((string) ($order['cancelStatus'] ?? '')) === 'CANCELLED';
    }

    /**
     * Updates the existing PrestaShop order to the shop's "Cancelled" state.
     *
     * PrestaShop's state-change flow restores the stock automatically, so the
     * product quantity goes back up without any extra work.
     */
    private function cancelOrder(string $orderId): bool
    {
        $idOrder = (int) \Db::getInstance()->getValue(
            'SELECT `id_order` FROM ' . _DB_PREFIX_ . 'linkbay_order WHERE `ebay_order_id` = "' . pSQL($orderId) . '"'
        );

        if ($idOrder <= 0) {
            return false;
        }

        $order = new \Order($idOrder);
        if (!\Validate::isLoadedObject($order)) {
            return false;
        }

        $cancelledState = (int) \Configuration::get('PS_OS_CANCELED');
        if ((int) $order->current_state === $cancelledState) {
            return true;
        }

        $history = new \OrderHistory();
        $history->id_order = $idOrder;
        $history->changeIdOrderState($cancelledState, $idOrder);
        $history->add();

        $this->log(0, $orderId, 'order_cancel', LinkBayLog::STATUS_SUCCESS, 'eBay order cancelled: the PrestaShop order status was updated and the stock was restored.');

        return true;
    }

    /**
     * Creates the PrestaShop order for one eBay order.
     *
     * @param array<string, mixed> $order
     *
     * @throws \RuntimeException when the order cannot be created
     */
    private function importOrder(array $order): void
    {
        $orderId = (string) ($order['orderId'] ?? '');

        $lineItems = $this->mapLineItems((array) ($order['lineItems'] ?? []));
        if (empty($lineItems)) {
            throw new \RuntimeException('No line item could be mapped to a PrestaShop product (listings must be imported first).');
        }

        $currencyCode = strtoupper((string) ($order['pricingSummary']['total']['currency'] ?? 'EUR'));
        $currencyId = $this->currencyId($currencyCode);

        $customer = $this->guestCustomer();
        $address = $this->createAddress($customer, $order);
        $cart = $this->buildCart($customer, $address, $currencyId, $lineItems);

        $orderStateId = (int) \Configuration::get(Config::ORDER_STATE) ?: (int) \Configuration::get('PS_OS_PAYMENT');
        $paymentLabel = $this->paymentLabel($order);

        $totalPaid = (float) ($order['pricingSummary']['total']['value'] ?? 0);
        $subtotal = (float) ($order['pricingSummary']['subtotal']['value'] ?? $totalPaid);
        $totalTax = (float) ($order['pricingSummary']['totalTax']['value'] ?? 0);
        $totalShipping = (float) ($order['pricingSummary']['deliveryCost']['value'] ?? 0);

        $psOrder = new \Order();
        $psOrder->id_cart = (int) $cart->id;
        $psOrder->id_customer = (int) $customer->id;
        $psOrder->id_address_delivery = (int) $address->id;
        $psOrder->id_address_invoice = (int) $address->id;
        $psOrder->id_currency = $currencyId;
        $psOrder->id_lang = (int) \Configuration::get('PS_LANG_DEFAULT');
        $psOrder->id_carrier = (int) $cart->id_carrier;
        $psOrder->id_shop_group = (int) $cart->id_shop_group;
        $psOrder->id_shop = (int) $cart->id_shop;
        $psOrder->payment = $paymentLabel;
        $psOrder->module = 'linkbay';
        $psOrder->reference = self::REFERENCE_PREFIX . $orderId;
        $psOrder->secure_key = (string) $customer->secure_key;
        $psOrder->total_paid = $totalPaid;
        $psOrder->total_paid_real = $totalPaid;
        // Import the eBay tax as-is, whatever the marketplace: the gross
        // subtotal is stored tax-included and the tax amount is deducted to
        // get the tax-excluded total.
        $psOrder->total_products_wt = $subtotal;
        $psOrder->total_products = max(0, $subtotal - $totalTax);
        $psOrder->total_shipping_tax_incl = $totalShipping;
        $psOrder->total_shipping = $totalShipping;
        $psOrder->total_shipping_tax_excl = $totalShipping;
        $psOrder->carrier_tax_rate = 0;
        $psOrder->conversion_rate = 1;
        $psOrder->current_state = $orderStateId;
        $psOrder->valid = true;
        $psOrder->date_add = date('Y-m-d H:i:s');
        $psOrder->date_upd = date('Y-m-d H:i:s');

        if (!$psOrder->add()) {
            throw new \RuntimeException('Unable to create the PrestaShop order.');
        }

        $orderDetail = new \OrderDetail();
        $orderDetail->createList($psOrder, $cart, $orderStateId, $cart->getProducts());

        $history = new \OrderHistory();
        $history->id_order = (int) $psOrder->id;
        $history->changeIdOrderState($orderStateId, (int) $psOrder->id);
        $history->add();

        // Remember the eBay -> PrestaShop order mapping so later events
        // (cancellation, updates) can find the order instantly.
        \Db::getInstance()->insert('linkbay_order', [
            'ebay_order_id' => pSQL($orderId),
            'id_order' => (int) $psOrder->id,
            'date_add' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Maps the eBay line items to PrestaShop products/combinations.
     *
     * @param array<int, array<string, mixed>> $lineItems
     *
     * @return array<int, array{id_product: int, id_product_attribute: int, quantity: int}>
     */
    private function mapLineItems(array $lineItems): array
    {
        $mapped = [];

        foreach ($lineItems as $lineItem) {
            $sku = (string) ($lineItem['sku'] ?? '');
            $legacyItemId = (string) ($lineItem['legacyItemId'] ?? '');
            $quantity = max(1, (int) ($lineItem['quantity'] ?? 1));

            $link = $this->productLinkRepository->findBySku($sku);
            if ($link === null && $legacyItemId !== '') {
                $link = $this->productLinkRepository->findOneByEbayItemId($legacyItemId);
            }

            if ($link === null || (int) $link->getIdProduct() <= 0) {
                continue;
            }

            $mapped[] = [
                'id_product' => (int) $link->getIdProduct(),
                'id_product_attribute' => (int) $link->getIdProductAttribute(),
                'quantity' => $quantity,
            ];
        }

        return $mapped;
    }

    /**
     * Returns the shared guest customer used for every imported order.
     */
    private function guestCustomer(): \Customer
    {
        $idCustomer = (int) \Customer::customerExists(self::GUEST_EMAIL, true);
        if ($idCustomer > 0) {
            $customer = new \Customer($idCustomer);
            if (\Validate::isLoadedObject($customer)) {
                return $customer;
            }
        }

        $customer = new \Customer();
        $customer->email = self::GUEST_EMAIL;
        $customer->firstname = 'eBay';
        $customer->lastname = 'Buyer';
        $customer->passwd = \Tools::hash((string) \Tools::passwdGen(16));
        $customer->is_guest = true;
        $customer->active = true;
        $customer->newsletter = false;
        $customer->optin = false;

        if (!$customer->add()) {
            throw new \RuntimeException('Unable to create the eBay guest customer.');
        }

        return $customer;
    }

    /**
     * Builds the shipping/billing address from the eBay order payload.
     *
     * @param array<string, mixed> $order
     */
    private function createAddress(\Customer $customer, array $order): \Address
    {
        $shipTo = $this->extractShipTo($order);

        $fullName = trim((string) ($shipTo['fullName'] ?? ''));
        $names = $this->splitName($fullName !== '' ? $fullName : (string) ($order['buyer']['username'] ?? 'eBay Buyer'));

        $rawAddress = (array) ($shipTo['shippingAddress'] ?? $shipTo['contactAddress'] ?? []);

        $address = new \Address();
        $address->id_customer = (int) $customer->id;
        $address->firstname = $names['firstname'];
        $address->lastname = $names['lastname'];
        $address->address1 = trim((string) ($rawAddress['addressLine1'] ?? '')) ?: 'eBay';
        $address->address2 = trim((string) ($rawAddress['addressLine2'] ?? ''));
        $address->postcode = trim((string) ($rawAddress['postalCode'] ?? '')) ?: '00000';
        $address->city = trim((string) ($rawAddress['city'] ?? '')) ?: 'eBay';
        $address->id_country = $this->countryId((string) ($rawAddress['countryCode'] ?? ''));
        $address->id_state = $this->stateId((int) $address->id_country, (string) ($rawAddress['stateOrProvince'] ?? ''));
        $address->phone = trim((string) ($shipTo['primaryPhone']['phoneNumber'] ?? ''));
        // Some countries (Spain, Chile…) require an identification number:
        // eBay does not provide it, so a placeholder keeps the import going.
        $address->dni = '00000000X';
        $address->alias = 'eBay';
        $address->deleted = false;

        try {
            if ($address->add()) {
                return $address;
            }
        } catch (\Throwable $e) {
            // Fall through to the sanitized retry below.
        }

        // Never block the import on a country/format quirk (postal code,
        // missing state, exotic country): retry with the shop's default
        // country and a generic postcode.
        $address->id_country = (int) \Configuration::get('PS_COUNTRY_DEFAULT');
        $address->id_state = 0;
        $address->postcode = '00000';
        $address->phone = '';

        if (!$address->add()) {
            throw new \RuntimeException('Unable to create the order address.');
        }

        return $address;
    }

    /**
     * Builds the cart for the imported order.
     *
     * @param array<int, array{id_product: int, id_product_attribute: int, quantity: int}> $lineItems
     */
    private function buildCart(\Customer $customer, \Address $address, int $idCurrency, array $lineItems): \Cart
    {
        $cart = new \Cart();
        $cart->id_customer = (int) $customer->id;
        $cart->id_address_delivery = (int) $address->id;
        $cart->id_address_invoice = (int) $address->id;
        $cart->id_currency = $idCurrency;
        $cart->id_lang = (int) \Configuration::get('PS_LANG_DEFAULT');
        $cart->id_shop_group = (int) \Shop::getContextShopGroupID();
        $cart->id_shop = (int) \Shop::getContextShopID();
        $cart->secure_key = (string) $customer->secure_key;
        $cart->id_carrier = $this->defaultCarrierId();

        if (!$cart->add()) {
            throw new \RuntimeException('Unable to create the order cart.');
        }

        // Register the cart in the context: the cron runs without an employee
        // context, and PrestaShop's price computation (and third-party cart
        // hooks) die in that case when the context is incomplete.
        $context = $this->context;
        $context->language = new \Language((int) \Configuration::get('PS_LANG_DEFAULT'));
        $context->currency = new \Currency($idCurrency);
        $context->cart = $cart;

        foreach ($lineItems as $item) {
            if (!$cart->updateQty(
                $item['quantity'],
                $item['id_product'],
                $item['id_product_attribute'],
                false,
                'up',
                (int) $address->id
            )) {
                throw new \RuntimeException('Unable to add product ' . $item['id_product'] . ' to the cart.');
            }
        }

        return $cart;
    }

    /**
     * @param array<string, mixed> $order
     *
     * @return array<string, mixed>
     */
    private function extractShipTo(array $order): array
    {
        $instructions = $order['fulfillmentStartInstructions'] ?? [];
        if (!is_array($instructions)) {
            return [];
        }

        foreach ($instructions as $instruction) {
            $shipTo = $instruction['shippingStep']['shipTo'] ?? null;
            if (is_array($shipTo)) {
                return $shipTo;
            }
        }

        return [];
    }

    /**
     * @return array{firstname: string, lastname: string}
     */
    private function splitName(string $fullName): array
    {
        $fullName = trim((string) preg_replace('/\s+/', ' ', $fullName));
        $parts = explode(' ', $fullName, 2);

        return [
            'firstname' => $parts[0] !== '' ? $parts[0] : 'eBay',
            'lastname' => isset($parts[1]) && $parts[1] !== '' ? $parts[1] : 'Buyer',
        ];
    }

    private function isImported(string $orderId): bool
    {
        // The eBay -> PrestaShop mapping table is the single source of truth:
        // the eBay order id does not fit in the truncated PrestaShop order
        // reference.
        return (int) \Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'linkbay_order WHERE `ebay_order_id` = "' . pSQL($orderId) . '"'
        ) > 0;
    }

    /**
     * Returns the payment label affiliated to the eBay marketplace.
     *
     * A sale made on ebay.fr records "EBAY.FR", one made on ebay.it records
     * "EBAY.IT", and so on. When the marketplace id is unknown or missing, the
     * configured fallback label (default "eBay") is used instead.
     *
     * @param array<string, mixed> $order
     */
    private function paymentLabel(array $order): string
    {
        $marketplaceId = strtoupper(trim((string) ($order['marketplaceId'] ?? '')));

        if ($marketplaceId !== '' && isset(self::MARKETPLACE_LABELS[$marketplaceId])) {
            return self::MARKETPLACE_LABELS[$marketplaceId];
        }

        // Unknown marketplace ids still follow the "EBAY_XX" pattern: turn the
        // underscore into a dot ("EBAY_NL" -> "EBAY.NL") so every marketplace
        // is covered without an exhaustive list.
        if (strpos($marketplaceId, 'EBAY_') === 0) {
            return str_replace('_', '.', $marketplaceId);
        }

        return (string) \Configuration::get(Config::ORDER_PAYMENT) ?: 'eBay';
    }

    private function countryId(string $isoCode): int
    {
        $isoCode = strtoupper(trim($isoCode));

        if ($isoCode === '') {
            return (int) \Configuration::get('PS_COUNTRY_DEFAULT');
        }

        $id = (int) \Country::getByIso($isoCode);

        return $id > 0 ? $id : (int) \Configuration::get('PS_COUNTRY_DEFAULT');
    }

    private function stateId(int $idCountry, string $stateName): int
    {
        $stateName = trim($stateName);

        if ($stateName === '' || $idCountry <= 0) {
            return 0;
        }

        // Match the state inside the target country (case-insensitive), so
        // two countries sharing a state name never collide.
        foreach ((array) \State::getStatesByIdCountry($idCountry) as $state) {
            if (!is_array($state)) {
                continue;
            }
            if (strcasecmp((string) ($state['name'] ?? ''), $stateName) === 0) {
                return (int) ($state['id_state'] ?? 0);
            }
        }

        return 0;
    }

    private function currencyId(string $isoCode): int
    {
        $isoCode = strtoupper(trim($isoCode));
        if ($isoCode === '') {
            return (int) \Configuration::get('PS_CURRENCY_DEFAULT');
        }

        $id = (int) \Currency::getIdByIsoCode($isoCode);

        // Never block the import on an exotic currency: fall back to the
        // shop's default currency when eBay's is not installed.
        return $id > 0 ? $id : (int) \Configuration::get('PS_CURRENCY_DEFAULT');
    }

    private function defaultCarrierId(): int
    {
        $carriers = \Carrier::getCarriers((int) \Configuration::get('PS_LANG_DEFAULT'), true);
        if (!is_array($carriers)) {
            return 0;
        }

        foreach ($carriers as $carrier) {
            if (!empty($carrier['id_carrier'])) {
                return (int) $carrier['id_carrier'];
            }
        }

        return 0;
    }

    private function log(int $idProduct, string $ebayItemId, string $action, string $status, string $message): void
    {
        try {
            $log = new LinkBayLog();
            $log->setIdProduct($idProduct)
                ->setEbayItemId($ebayItemId)
                ->setAction($action)
                ->setStatus($status)
                ->setMessage($message);

            $this->logRepository->add($log);
        } catch (\Throwable $e) {
            // Logging must never break the import.
        }
    }
}
