<?php
/**
 * FPE Payment — module de paiement Stripe allégé (page hébergée)
 * ---------------------------------------------------------------------------
 * Remplace un module de paiement lourd (SDK JS) par un flux de redirection :
 * la carte est saisie sur la page hébergée de Stripe, JAMAIS sur la boutique.
 * → conforme PCI-DSS sans effort, et ~10x plus léger côté client.
 *
 * Flux :
 *   hookPaymentOptions  → affiche « Payer par carte »
 *   controllers/front/validation.php → crée une Checkout Session + la commande,
 *                                      puis redirige vers Stripe
 *   Stripe héberge le paiement, puis renvoie vers payment_return
 *   controllers/front/webhook.php    → confirme la commande (checkout.session.completed)
 *
 * Aucune dépendance Composer : l'API Stripe est appelée en cURL.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class FpePayment extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'fpepayment';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'FullPowerExhaust';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        parent::__construct();

        $this->displayName = 'FPE Paiement (Stripe)';
        $this->description = 'Paiement par carte via la page hébergée Stripe — léger, sans SDK, conforme PCI-DSS.';
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => '9.99.99'];
        $this->confirmUninstall = $this->l('Désinstaller le module de paiement Stripe ?');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('paymentOptions')
            && Configuration::updateValue('FPE_STRIPE_MODE', 'test')
            && Configuration::updateValue('FPE_STRIPE_TEST_SECRET', '')
            && Configuration::updateValue('FPE_STRIPE_LIVE_SECRET', '')
            && Configuration::updateValue('FPE_STRIPE_WEBHOOK_SECRET', '')
            && Configuration::updateValue('FPE_STRIPE_AWAITING_STATE', (int) Configuration::get('PS_OS_CHEQUE'))
            && Configuration::updateValue('FPE_STRIPE_CONNECT_ENABLED', 0)
            && Configuration::updateValue('FPE_STRIPE_CONNECTED_ACCOUNT', '')
            && Configuration::updateValue('FPE_STRIPE_COMMISSION_TYPE', 'percent')
            && Configuration::updateValue('FPE_STRIPE_COMMISSION_VALUE', '0');
    }

    public function uninstall()
    {
        return parent::uninstall()
            && Configuration::deleteByName('FPE_STRIPE_MODE')
            && Configuration::deleteByName('FPE_STRIPE_TEST_SECRET')
            && Configuration::deleteByName('FPE_STRIPE_LIVE_SECRET')
            && Configuration::deleteByName('FPE_STRIPE_WEBHOOK_SECRET')
            && Configuration::deleteByName('FPE_STRIPE_AWAITING_STATE')
            && Configuration::deleteByName('FPE_STRIPE_CONNECT_ENABLED')
            && Configuration::deleteByName('FPE_STRIPE_CONNECTED_ACCOUNT')
            && Configuration::deleteByName('FPE_STRIPE_COMMISSION_TYPE')
            && Configuration::deleteByName('FPE_STRIPE_COMMISSION_VALUE');
    }

    /* --------------------------------------------------------------------- */
    /*  Paiement : ajoute l'option à l'étape paiement                        */
    /* --------------------------------------------------------------------- */
    public function hookPaymentOptions(array $params): array
    {
        if (!$this->active || !isset($params['cart']) || !$this->checkCurrency($params['cart'])) {
            return [];
        }

        $option = new PaymentOption();
        $option->setModuleName($this->name)
            ->setCallToActionText($this->l('Payer par carte bancaire (Stripe)'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true))
            ->setLogo('');

        return [$option];
    }

    private function checkCurrency(Cart $cart): bool
    {
        $currency = new Currency((int) $cart->id_currency);

        return in_array(strtoupper($currency->iso_code), ['EUR'], true);
    }

    /* --------------------------------------------------------------------- */
    /*  Configuration                                                        */
    /* --------------------------------------------------------------------- */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitFpeStripe')) {
            Configuration::updateValue('FPE_STRIPE_MODE', (string) Tools::getValue('FPE_STRIPE_MODE', 'test'));
            Configuration::updateValue('FPE_STRIPE_TEST_SECRET', (string) Tools::getValue('FPE_STRIPE_TEST_SECRET'));
            Configuration::updateValue('FPE_STRIPE_LIVE_SECRET', (string) Tools::getValue('FPE_STRIPE_LIVE_SECRET'));
            Configuration::updateValue('FPE_STRIPE_WEBHOOK_SECRET', (string) Tools::getValue('FPE_STRIPE_WEBHOOK_SECRET'));
            Configuration::updateValue('FPE_STRIPE_AWAITING_STATE', (int) Tools::getValue('FPE_STRIPE_AWAITING_STATE'));
            Configuration::updateValue('FPE_STRIPE_CONNECT_ENABLED', (int) Tools::getValue('FPE_STRIPE_CONNECT_ENABLED', 0));
            Configuration::updateValue('FPE_STRIPE_CONNECTED_ACCOUNT', (string) Tools::getValue('FPE_STRIPE_CONNECTED_ACCOUNT'));
            Configuration::updateValue('FPE_STRIPE_COMMISSION_TYPE', (string) Tools::getValue('FPE_STRIPE_COMMISSION_TYPE', 'percent'));
            Configuration::updateValue('FPE_STRIPE_COMMISSION_VALUE', (string) Tools::getValue('FPE_STRIPE_COMMISSION_VALUE', '0'));
            $output .= $this->displayConfirmation($this->l('Paramètres enregistrés.'));
        }

        // Lien du webhook à recopier dans Stripe
        $webhookUrl = $this->context->link->getModuleLink($this->name, 'webhook', [], true);

        $this->context->smarty->assign([
            'fpe' => [
                'mode' => Configuration::get('FPE_STRIPE_MODE'),
                'test_secret' => Configuration::get('FPE_STRIPE_TEST_SECRET'),
                'live_secret' => Configuration::get('FPE_STRIPE_LIVE_SECRET'),
                'webhook_secret' => Configuration::get('FPE_STRIPE_WEBHOOK_SECRET'),
                'awaiting_state' => (int) Configuration::get('FPE_STRIPE_AWAITING_STATE'),
                'webhook_url' => $webhookUrl,
                'order_states' => OrderState::getOrderStates((int) $this->context->language->id),
                'connect_enabled' => (int) Configuration::get('FPE_STRIPE_CONNECT_ENABLED'),
                'connected_account' => Configuration::get('FPE_STRIPE_CONNECTED_ACCOUNT'),
                'commission_type' => Configuration::get('FPE_STRIPE_COMMISSION_TYPE'),
                'commission_value' => Configuration::get('FPE_STRIPE_COMMISSION_VALUE'),
            ],
        ]);

        return $output . $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    /* --------------------------------------------------------------------- */
    /*  Accès à l'API Stripe (cURL, sans dépendance)                          */
    /* --------------------------------------------------------------------- */
    public function getSecretKey(): string
    {
        return Configuration::get('FPE_STRIPE_MODE') === 'live'
            ? (string) Configuration::get('FPE_STRIPE_LIVE_SECRET')
            : (string) Configuration::get('FPE_STRIPE_TEST_SECRET');
    }

    public function getWebhookSecret(): string
    {
        return (string) Configuration::get('FPE_STRIPE_WEBHOOK_SECRET');
    }

    /**
     * Appelle l'API Stripe et renvoie le tableau décodé.
     * En cas d'erreur, renvoie ['error' => message].
     */
    public function stripeRequest(string $method, string $path, array $params = []): array
    {
        $url = 'https://api.stripe.com/v1' . $path;
        $headers = [
            'Authorization: Bearer ' . $this->getSecretKey(),
            'Content-Type: application/x-www-form-urlencoded',
        ];

        $ch = curl_init();
        $opts = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_CONNECTTIMEOUT => 10,
        ];

        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = http_build_query($params);
        } elseif ($method === 'GET' && $params) {
            $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
        }

        curl_setopt_array($ch, $opts);
        $body = curl_exec($ch);
        $err = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode((string) $body, true);

        if ($err) {
            return ['error' => $err];
        }
        if ($httpCode >= 400 || !is_array($json)) {
            return ['error' => $json['error']['message'] ?? ('HTTP ' . $httpCode)];
        }

        return $json;
    }

    /**
     * Crée une Checkout Session (page hébergée) pour un montant total.
     * En mode Stripe Connect, la commission (application_fee) est prélevée au
     * profit du compte plateforme et le solde est reversé au compte connecté.
     */
    public function createCheckoutSession(string $successUrl, string $cancelUrl, string $orderReference, float $totalEur, string $customerEmail): array
    {
        $params = [
            'mode' => 'payment',
            'line_items[0][price_data][currency]' => 'eur',
            'line_items[0][price_data][unit_amount]' => (int) round($totalEur * 100),
            'line_items[0][price_data][product_data][name]' => 'Commande ' . $orderReference,
            'line_items[0][quantity]' => 1,
            'customer_email' => $customerEmail,
            'client_reference_id' => $orderReference,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'payment_intent_data[metadata][order_reference]' => $orderReference,
        ];

        if ($this->isConnectEnabled()) {
            $destination = $this->getConnectedAccountId();
            if ($destination !== '') {
                $params['payment_intent_data[application_fee_amount]'] = $this->getCommissionCents($totalEur);
                $params['payment_intent_data[transfer_data][destination]'] = $destination;
            }
        }

        return $this->stripeRequest('POST', '/checkout/sessions', $params);
    }

    /**
     * Le mode marketplace (Stripe Connect) est-il activé ?
     */
    public function isConnectEnabled(): bool
    {
        return (bool) Configuration::get('FPE_STRIPE_CONNECT_ENABLED', null, null, null, 0);
    }

    /**
     * Identifiant du compte connecté (acct_…) qui reçoit le solde.
     */
    public function getConnectedAccountId(): string
    {
        return (string) Configuration::get('FPE_STRIPE_CONNECTED_ACCOUNT');
    }

    /**
     * Calcule la commission de la plateforme (Beassisted) en centimes.
     * La commission est plafonnée pour rester strictement inférieure au total.
     */
    public function getCommissionCents(float $totalEur): int
    {
        $type = (string) Configuration::get('FPE_STRIPE_COMMISSION_TYPE', null, null, null, 'percent');
        $value = (float) Configuration::get('FPE_STRIPE_COMMISSION_VALUE', null, null, null, 0);
        $totalCents = (int) round($totalEur * 100);

        $feeCents = $type === 'fixed'
            ? (int) round($value * 100)
            : (int) round($totalEur * $value);

        if ($feeCents < 0) {
            $feeCents = 0;
        }
        // Stripe exige que la commission soit inférieure au montant total :
        // on laisse toujours au moins 1 centime au compte connecté.
        if ($feeCents >= $totalCents) {
            $feeCents = max(0, $totalCents - 1);
        }

        return $feeCents;
    }

    public function retrieveCheckoutSession(string $sessionId): array
    {
        return $this->stripeRequest('GET', '/checkout/sessions/' . rawurlencode($sessionId));
    }

    /**
     * Vérifie la signature du webhook Stripe (HMAC-SHA256, comparaison à temps constant).
     */
    public function verifyWebhookSignature(string $payload, string $signatureHeader): bool
    {
        $secret = $this->getWebhookSecret();
        if ($secret === '' || $signatureHeader === '') {
            return false;
        }

        // Format : t=<timestamp>,v1=<signature>
        if (!preg_match('/t=(\d+),v1=([a-f0-9]+)/', $signatureHeader, $m)) {
            return false;
        }
        $timestamp = $m[1];
        $signature = $m[2];

        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * Retrouve une commande par sa référence.
     */
    public function getOrderByReference(string $reference): ?Order
    {
        $collection = Order::getByReference($reference);
        $results = $collection->getResults();

        if (empty($results) || !($results[0] instanceof Order)) {
            return null;
        }
        $order = $results[0];

        return Validate::isLoadedObject($order) ? $order : null;
    }

    /**
     * Passe une commande en « paiement accepté ».
     */
    public function markOrderAsPaid(Order $order): bool
    {
        if ((int) $order->getCurrentState() === (int) Configuration::get('PS_OS_PAYMENT')) {
            return true; // déjà payée (idempotent)
        }

        $history = new OrderHistory();
        $history->id_order = (int) $order->id;
        $history->changeIdOrderState((int) Configuration::get('PS_OS_PAYMENT'), $order, true);

        return true;
    }
}
