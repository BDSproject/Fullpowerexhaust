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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use LinkBay\Config\Config;
use LinkBay\Repository\LinkBayTaxonomyCacheRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Thin wrapper around the eBay REST APIs.
 *
 * The deprecated Trading API (XML, 410 Gone since eBay sunset it) has been
 * replaced by:
 *  - the Sell Inventory API for listing retrieval and stock/price updates
 *    (items are keyed by SKU),
 *  - the Commerce Taxonomy API for category browsing.
 *
 * The OAuth authorization code flow is unchanged.
 */
final class EbayApiClient
{
    public const ENV_PRODUCTION = 'production';
    public const ENV_SANDBOX = 'sandbox';

    /** Trading API site id => REST marketplace id. */
    private const MARKETPLACES = [
        0 => 'EBAY_US',
        2 => 'EBAY_CA',
        3 => 'EBAY_GB',
        15 => 'EBAY_AU',
        16 => 'EBAY_AT',
        23 => 'EBAY_BE_FR',
        71 => 'EBAY_FR',
        77 => 'EBAY_DE',
        101 => 'EBAY_IT',
        123 => 'EBAY_BE_NL',
        146 => 'EBAY_NL',
        186 => 'EBAY_ES',
        193 => 'EBAY_CH',
        205 => 'EBAY_IE',
        206 => 'EBAY_MY',
        207 => 'EBAY_PH',
        210 => 'EBAY_CA_FR',
        211 => 'EBAY_SG',
    ];

    /**
     * Builds a self-authenticating OAuth state value.
     *
     * The state is "<base64url(payload)>.<hmac>" where the payload carries a
     * nonce and the shop's own OAuth callback URL. The HMAC (keyed with the shop
     * cookie key) lets the shop validate the state on the callback, while the
     * activation service only decodes the payload to learn where to redirect the
     * browser back. This lets the OAuth callback relay through a different
     * domain (the hosted activation service) with no shared cookie.
     */
    public static function generateOauthState(string $returnUrl): string
    {
        $payload = self::base64UrlEncode((string) json_encode([
            'nonce' => bin2hex(random_bytes(16)),
            'return_url' => $returnUrl,
        ], JSON_UNESCAPED_SLASHES));

        return $payload . '.' . hash_hmac('sha256', $payload, _COOKIE_KEY_);
    }

    /**
     * Validates a state value produced by generateOauthState().
     */
    public static function validateOauthState(string $state): bool
    {
        $parts = explode('.', $state, 2);

        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $parts[0], _COOKIE_KEY_);

        return hash_equals($expected, $parts[1]);
    }

    /**
     * URL-safe base64 without padding (must match the activation service).
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * OAuth scopes requested from eBay.
     *
     * The base scope covers the Commerce Taxonomy API; sell.inventory is needed
     * for listing retrieval and stock updates. Both must be enabled for the
     * application keyset in the eBay developer portal.
     */
    private const SCOPES = [
        'https://api.ebay.com/oauth/api_scope',
        'https://api.ebay.com/oauth/api_scope/sell.inventory',
        'https://api.ebay.com/oauth/api_scope/sell.fulfillment',
    ];

    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    private string $environment;
    private string $username;
    private TokenStorage $tokenStorage;
    private ?Client $httpClient = null;
    private ?LinkBayTaxonomyCacheRepository $taxonomyCacheRepository = null;

    /**
     * Lazily instantiated local cache for the eBay taxonomy.
     */
    private function taxonomyCache(): LinkBayTaxonomyCacheRepository
    {
        if ($this->taxonomyCacheRepository === null) {
            $this->taxonomyCacheRepository = new LinkBayTaxonomyCacheRepository();
        }

        return $this->taxonomyCacheRepository;
    }

    public function __construct(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
        $this->clientId = (string) \Configuration::get(Config::CLIENT_ID);
        $this->clientSecret = (string) \Configuration::get(Config::CLIENT_SECRET);
        $this->redirectUri = (string) \Configuration::get(Config::REDIRECT_URI);
        $this->environment = (string) \Configuration::get(Config::ENVIRONMENT) ?: self::ENV_PRODUCTION;
        $this->username = (string) \Configuration::get(Config::EBAY_USERNAME);
    }

    /**
     * Whether the demo mode is active (simulated eBay data, no API call).
     */
    private function isDemo(): bool
    {
        return (string) \Configuration::get(Config::DEMO_MODE) === '1';
    }

    /**
     * Builds per-SKU success responses for the demo mode (no eBay call).
     *
     * @param array<int, array<string, mixed>> $updates
     *
     * @return array<int, array<string, mixed>>
     */
    private function demoStockResponses(array $updates): array
    {
        $responses = [];

        foreach ($updates as $update) {
            $sku = (string) ($update['sku'] ?? '');
            if ($sku !== '') {
                $responses[] = ['sku' => $sku, 'statusCode' => 200, 'errors' => []];
            }
        }

        return $responses;
    }

    /**
     * Whether the OAuth application credentials are filled in.
     */
    public function isConfigured(): bool
    {
        if ($this->isDemo()) {
            return true;
        }

        return $this->clientId !== '' && $this->clientSecret !== '' && $this->redirectUri !== '';
    }

    /**
     * Builds the eBay OAuth authorization URL.
     */
    public function buildAuthorizationUrl(string $state): string
    {
        return $this->authEndpoint() . '?' . http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', self::SCOPES),
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Exchanges an authorization code for an access token and a refresh token.
     *
     * @return array{access_token: string, refresh_token: string, expires_in: int}
     *
     * @throws \RuntimeException when eBay rejects the exchange
     */
    public function exchangeAuthorizationCode(string $code): array
    {
        $data = $this->requestToken([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
        ]);

        return $this->normalizeTokenResponse($data);
    }

    /**
     * Refreshes an expired access token.
     *
     * @return array{access_token: string, refresh_token: string, expires_in: int}
     *
     * @throws \RuntimeException when eBay rejects the refresh
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        $data = $this->requestToken([
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'scope' => implode(' ', self::SCOPES),
        ]);

        return $this->normalizeTokenResponse($data);
    }

    /**
     * Returns a valid access token, refreshing it transparently when needed.
     */
    public function getValidAccessToken(): ?string
    {
        $accessToken = $this->tokenStorage->decrypt((string) \Configuration::get(Config::ACCESS_TOKEN));
        $expiresAt = (int) \Configuration::get(Config::TOKEN_EXPIRES_AT);

        if ($accessToken !== '' && $expiresAt > time() + 60) {
            return $accessToken;
        }

        $refreshToken = $this->tokenStorage->decrypt((string) \Configuration::get(Config::REFRESH_TOKEN));
        if ($refreshToken === '') {
            return $accessToken !== '' ? $accessToken : null;
        }

        try {
            $tokens = $this->refreshAccessToken($refreshToken);
        } catch (\RuntimeException $e) {
            return null;
        }

        $this->storeTokens($tokens);

        return $tokens['access_token'];
    }

    /**
     * Persists a token response in an encrypted form.
     *
     * @param array{access_token: string, refresh_token?: string, expires_in: int} $tokens
     */
    public function storeTokens(array $tokens): void
    {
        $accessToken = $tokens['access_token'];
        if ($accessToken === '') {
            return;
        }

        $expiresIn = (int) $tokens['expires_in'];
        \Configuration::updateValue(Config::ACCESS_TOKEN, $this->tokenStorage->encrypt($accessToken));
        \Configuration::updateValue(Config::TOKEN_EXPIRES_AT, (string) (time() + max(0, $expiresIn - 60)));

        if (!empty($tokens['refresh_token'])) {
            \Configuration::updateValue(Config::REFRESH_TOKEN, $this->tokenStorage->encrypt($tokens['refresh_token']));
        }
    }

    /**
     * Fetches the OAuth tokens from the hosted activation service.
     *
     * eBay redirects the merchant to the activation service (the single shared
     * redirect target), which exchanges the authorization code and sends the
     * browser back here with a one-time "handoff" code. The shop then collects
     * the tokens server-to-server, so they never travel through the browser URL.
     *
     * @return array{access_token: string, refresh_token: string, expires_in: int}
     */
    public function fetchTokensFromActivationService(string $handoff): array
    {
        try {
            $response = $this->http()->post(Config::ACTIVATION_SERVICE_URL . '/linkbay/fetch.php', [
                'http_errors' => false,
                'json' => [
                    'handoff' => $handoff,
                    'shop_domain' => \Tools::getShopDomainSsl(true),
                ],
            ]);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Unable to reach the activation service: ' . $e->getMessage());
        }

        $data = json_decode((string) $response->getBody(), true);
        if (!is_array($data) || empty($data['success']) || empty($data['credentials'])) {
            $error = is_array($data) ? (string) ($data['error'] ?? '') : '';

            throw new \RuntimeException($error !== '' ? $error : 'The activation service did not return any token.');
        }

        return $this->decryptActivationPayload((string) $data['credentials']);
    }

    /**
     * Decrypts and verifies a token payload produced by the activation service.
     *
     * @return array{access_token: string, refresh_token: string, expires_in: int}
     */
    private function decryptActivationPayload(string $credentials): array
    {
        $raw = base64_decode($credentials, true);
        if ($raw === false || strlen($raw) < 48) {
            throw new \RuntimeException('Invalid activation payload.');
        }

        $iv = substr($raw, 0, 16);
        $hmac = substr($raw, 16, 32);
        $cipher = substr($raw, 48);

        $expected = hash_hmac('sha256', $cipher, Config::ACTIVATION_PSK, true);
        if (!hash_equals($expected, $hmac)) {
            throw new \RuntimeException('Invalid activation signature.');
        }

        $json = openssl_decrypt($cipher, 'AES-256-CBC', hash('sha256', Config::ACTIVATION_PSK, true), OPENSSL_RAW_DATA, $iv);
        if ($json === false) {
            throw new \RuntimeException('Unable to decrypt the activation payload.');
        }

        $data = json_decode($json, true);
        if (!is_array($data) || empty($data['access_token'])) {
            throw new \RuntimeException('The activation payload is missing the access token.');
        }

        return $this->normalizeTokenResponse($data);
    }

    /**
     * Whether the store currently holds any OAuth token.
     */
    public function isAuthenticated(): bool
    {
        if ($this->isDemo()) {
            return true;
        }

        return $this->tokenStorage->decrypt((string) \Configuration::get(Config::ACCESS_TOKEN)) !== ''
            || $this->tokenStorage->decrypt((string) \Configuration::get(Config::REFRESH_TOKEN)) !== '';
    }

    /**
     * Performs a lightweight connectivity check against the Taxonomy API
     * (covered by the base OAuth scope, so it works even without sell.inventory).
     */
    public function testConnection(): bool
    {
        if ($this->isDemo()) {
            return true;
        }

        $data = $this->restGet('/commerce/taxonomy/v1/get_default_category_tree_id', [
            'marketplace_id' => $this->marketplaceId(),
        ]);

        return !empty($data['categoryTreeId']);
    }

    /**
     * Fetches every active listing of the configured seller.
     *
     * Listings are read through the Buy Browse API (buyer view), which covers
     * listings created through any flow — including the ones the Sell Inventory
     * API cannot see. The mapping key is the classic eBay item id (legacyItemId).
     *
     * @return array<int, array<string, mixed>> normalized listing entries
     *                                          (item_id, sku, title, description, images, quantity, price, currency,
     *                                          category_id, category_name, variations)
     *
     * @throws \RuntimeException on API failure
     */
    public function getActiveListings(): array
    {
        if ($this->isDemo()) {
            return DemoData::catalog();
        }

        if ($this->username === '') {
            throw new \RuntimeException('Please fill in your eBay username in the module settings.');
        }

        $listings = [];
        $seen = [];
        $healthyMarketplaces = 0;

        foreach ($this->marketplaceIds() as $marketplaceId) {
            [$summaries, $searchSucceeded] = $this->searchSellerListings($this->username, $marketplaceId);
            if ($searchSucceeded) {
                ++$healthyMarketplaces;
            }

            $listings = array_merge($listings, $this->summariesToDetails($summaries, $marketplaceId, $seen));
        }

        if ($healthyMarketplaces === 0) {
            throw new \RuntimeException('The eBay listing search failed on every selected marketplace; the import was aborted so that existing products are not deactivated by mistake.');
        }

        return $listings;
    }

    /**
     * Fetches the seller's active listings published in a given eBay category
     * (including its subcategories). Much cheaper than getActiveListings():
     * one search per marketplace instead of one search per root category.
     *
     * @return array<int, array<string, mixed>> same normalized shape as getActiveListings() entries
     *
     * @throws \RuntimeException on API failure
     */
    public function getListingsInCategory(string $categoryId): array
    {
        if ($this->isDemo()) {
            return DemoData::listings($categoryId);
        }

        if ($this->username === '') {
            throw new \RuntimeException('Please fill in your eBay username in the module settings.');
        }

        $categoryId = (string) $categoryId;
        if ($categoryId === '' || $categoryId === '0') {
            throw new \RuntimeException('Please select a valid eBay category.');
        }

        $listings = [];
        $seen = [];
        $healthyMarketplaces = 0;

        foreach ($this->marketplaceIds() as $marketplaceId) {
            [$summaries, $searchSucceeded] = $this->searchSellerListings($this->username, $marketplaceId, $categoryId);
            if ($searchSucceeded) {
                ++$healthyMarketplaces;
            }

            $listings = array_merge($listings, $this->summariesToDetails($summaries, $marketplaceId, $seen));
        }

        if ($healthyMarketplaces === 0) {
            throw new \RuntimeException('The eBay listing search failed on every selected marketplace. Please try again.');
        }

        return $listings;
    }

    /**
     * Converts Buy Browse search summaries into full listing details,
     * deduplicated by item id across marketplaces.
     *
     * @param array<int, array<string, mixed>> $summaries
     * @param array<string, bool> $seen
     *
     * @return array<int, array<string, mixed>>
     */
    private function summariesToDetails(array $summaries, string $marketplaceId, array &$seen): array
    {
        $details = [];

        foreach ($summaries as $summary) {
            $itemId = (string) ($summary['itemId'] ?? '');
            if ($itemId === '' || isset($seen[$itemId])) {
                continue;
            }
            $seen[$itemId] = true;

            $itemGroupId = null;
            $href = (string) ($summary['itemGroupHref'] ?? '');
            if (preg_match('/[?&]item_group_id=(\d+)/', $href, $matches)) {
                $itemGroupId = $matches[1];
            }

            try {
                $details[] = $this->getItemDetail($itemId, $itemGroupId, $marketplaceId);
            } catch (\RuntimeException $e) {
                continue;
            }
        }

        return $details;
    }

    /**
     * Fetches the full detail of a single listing (Buy Browse API).
     *
     * When the listing is a multi-variation listing, every variation child is
     * fetched through the item group endpoint and exposed in "variations" with
     * its own price, quantity and varying aspects.
     *
     * @return array<string, mixed> same normalized shape as getActiveListings() entries
     *
     * @throws \RuntimeException on API failure
     */
    public function getItemDetail(string $itemId, ?string $itemGroupId = null, ?string $marketplaceId = null): array
    {
        if ($this->isDemo()) {
            return DemoData::itemDetail($itemId);
        }

        $candidates = array_values(array_unique(array_filter(array_merge(
            [$marketplaceId, $this->marketplaceId()],
            $this->marketplaceIds()
        ))));

        $data = null;
        $resolved = null;

        foreach ($candidates as $candidate) {
            try {
                $data = $this->restGet('/buy/browse/v1/item/' . rawurlencode($itemId), [], false, $candidate);
                $resolved = $candidate;
                break;
            } catch (\RuntimeException $e) {
                continue;
            }
        }

        if ($data === null) {
            throw new \RuntimeException('Listing "' . $itemId . '" was not found on the selected marketplaces (' . implode(', ', $candidates) . '). It may have ended or been removed from eBay.');
        }

        $images = [];
        $cover = $data['image']['imageUrl'] ?? '';
        if (is_string($cover) && $cover !== '') {
            $images[] = $cover;
        }
        foreach (($data['additionalImages'] ?? []) as $image) {
            $url = $image['imageUrl'] ?? '';
            if (is_string($url) && $url !== '') {
                $images[] = $url;
            }
        }

        $quantity = $this->extractEstimatedQuantity($data['estimatedAvailabilities'] ?? []);

        $groupId = $itemGroupId ?? $this->detectItemGroupId($data);

        // The get_item response never carries the item group fields, but the
        // Buy Browse itemId embeds the context id: "v1|<legacyItemId>|<context>".
        // A non-zero context marks a multi-variation listing, whose group id is
        // the legacy item id. Fetching the group lets getItemDetail return the
        // variations for the parent, the children and the sync/import flows.
        if ($groupId === null) {
            $contextParts = explode('|', (string) ($data['itemId'] ?? ''));
            $legacyItemId = (string) ($data['legacyItemId'] ?? '');
            if ($legacyItemId !== '' && (string) ($contextParts[2] ?? '0') !== '0') {
                $groupId = $legacyItemId;
            }
        }

        return [
            'item_id' => (string) ($data['itemId'] ?? ''),
            'legacy_item_id' => (string) ($data['legacyItemId'] ?? ''),
            'marketplace_id' => (string) $resolved,
            'sku' => '',
            'title' => (string) ($data['title'] ?? ''),
            'description' => (string) ($data['description'] ?? ''),
            'images' => $images,
            'quantity' => $quantity,
            'price' => (float) ($data['price']['value'] ?? 0),
            'currency' => strtoupper((string) ($data['price']['currency'] ?? '')),
            'category_id' => (string) ($data['categoryId'] ?? ''),
            'category_name' => (string) ($data['categoryPath'] ?? ''),
            'variations' => $groupId !== null ? $this->fetchGroupVariations($groupId, $resolved) : [],
        ];
    }

    /**
     * Extracts the item group id from a Buy Browse item when it is a
     * multi-variation listing.
     *
     * @param array<string, mixed> $data
     */
    private function detectItemGroupId(array $data): ?string
    {
        if (($data['itemGroupType'] ?? '') !== 'SELLER_DEFINED_VARIATIONS') {
            return null;
        }

        $href = (string) ($data['itemGroupHref'] ?? '');
        if (preg_match('/[?&]item_group_id=(\d+)/', $href, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Fetches every variation child of a multi-variation listing.
     *
     * The varying dimensions are detected by diffing the children aspects:
     * any aspect whose value changes between children is a variation dimension.
     *
     * @return array<int, array{sku: string, quantity: int, price: float, attributes: array<string, string>}>
     */
    private function fetchGroupVariations(string $itemGroupId, ?string $marketplaceId = null): array
    {
        try {
            $group = $this->restGet('/buy/browse/v1/item/get_items_by_item_group', [
                'item_group_id' => $itemGroupId,
            ], false, $marketplaceId);
        } catch (\RuntimeException $e) {
            return [];
        }

        $children = $group['items'] ?? [];
        if (!is_array($children) || empty($children)) {
            return [];
        }

        $varyingNames = $this->findVaryingAspectNames($children);
        $variations = [];

        foreach ($children as $child) {
            if (!is_array($child)) {
                continue;
            }

            $attributes = [];
            foreach (($child['localizedAspects'] ?? []) as $aspect) {
                $name = (string) ($aspect['name'] ?? '');
                if ($name === '' || !isset($varyingNames[$name])) {
                    continue;
                }
                $attributes[$name] = (string) ($aspect['value'] ?? '');
            }

            $variations[] = [
                'sku' => (string) ($child['itemId'] ?? ''),
                'quantity' => $this->extractEstimatedQuantity($child['estimatedAvailabilities'] ?? []),
                'price' => (float) ($child['price']['value'] ?? 0),
                'attributes' => $attributes,
            ];
        }

        return $variations;
    }

    /**
     * Extracts the best available quantity from a Buy Browse availability list
     * (exact quantity when provided, otherwise the remaining-quantity estimate).
     *
     * @param array<int, array<string, mixed>> $availabilities
     */
    private function extractEstimatedQuantity(array $availabilities): int
    {
        $quantity = 0;

        foreach ($availabilities as $availability) {
            if (!is_array($availability) || ($availability['estimatedAvailabilityStatus'] ?? '') !== 'IN_STOCK') {
                continue;
            }

            $candidate = (int) (
                $availability['estimatedAvailableQuantity']
                ?? $availability['estimatedRemainingQuantity']
                ?? 0
            );
            $quantity = max($quantity, $candidate);
        }

        return $quantity;
    }

    /**
     * Returns the aspect names whose values differ between the group children.
     *
     * @param array<int, array<string, mixed>> $children
     *
     * @return array<string, bool>
     */
    private function findVaryingAspectNames(array $children): array
    {
        $valuesByName = [];

        foreach ($children as $child) {
            foreach (($child['localizedAspects'] ?? []) as $aspect) {
                $name = (string) ($aspect['name'] ?? '');
                if ($name === '') {
                    continue;
                }
                $value = (string) ($aspect['value'] ?? '');
                $valuesByName[$name][$value] = true;
            }
        }

        $varying = [];

        foreach ($valuesByName as $name => $values) {
            if (count($values) > 1) {
                $varying[$name] = true;
            }
        }

        return $varying;
    }

    /**
     * Searches the seller's active listings on one marketplace.
     *
     * The Buy Browse seller filter requires a keyword or a category, so the
     * root categories are enumerated and results deduplicated by item id. When
     * a category id is given, a single search over that category (and its
     * subcategories) is performed instead.
     *
     * @return array{0: array<int, array<string, mixed>>, 1: bool} [summaries, searchSucceeded]
     */
    private function searchSellerListings(string $username, string $marketplaceId, ?string $categoryId = null): array
    {
        $seen = [];
        $summaries = [];
        $searchSucceeded = false;

        if ($categoryId !== null && $categoryId !== '' && $categoryId !== '0') {
            $categoryIds = [$categoryId];
        } else {
            $categoryIds = array_map(
                static fn (array $root): string => (string) ($root['CategoryID'] ?? ''),
                $this->getCategories(-1, 1, $marketplaceId)
            );
        }

        foreach ($categoryIds as $id) {
            if ($id === '' || $id === '0') {
                continue;
            }

            try {
                $page = $this->fetchAll('/buy/browse/v1/item_summary/search', 'itemSummaries', [
                    'category_ids' => $id,
                    'filter' => 'sellers:{' . $username . '}',
                ], $marketplaceId);
                $searchSucceeded = true;
            } catch (\RuntimeException $e) {
                continue;
            }

            foreach ($page as $summary) {
                $itemId = (string) ($summary['itemId'] ?? '');
                if ($itemId !== '' && !isset($seen[$itemId])) {
                    $seen[$itemId] = true;
                    $summaries[] = $summary;
                }
            }
        }

        return [$summaries, $searchSucceeded];
    }

    /**
     * Pushes stock changes back to eBay and returns the per-SKU results so the
     * caller can mark successes and failures individually.
     *
     * @param array<int, array{sku: string, quantity: int}> $updates
     *
     * @return array<int, array<string, mixed>> one entry per request (sku, statusCode, errors)
     *
     * @throws \RuntimeException on transport failure
     */
    public function updateStock(array $updates): array
    {
        if ($this->isDemo()) {
            return $this->demoStockResponses($updates);
        }

        if (empty($updates)) {
            return [];
        }

        $requests = [];

        foreach ($updates as $update) {
            $sku = (string) $update['sku'];
            if ($sku === '') {
                continue;
            }

            $requests[] = [
                'sku' => $sku,
                'shipToLocationAvailability' => [
                    'quantity' => (int) $update['quantity'],
                ],
            ];
        }

        if (empty($requests)) {
            return [];
        }

        $data = $this->restPost('/sell/inventory/v1/bulk_update_price_quantity', ['requests' => $requests], true);

        return is_array($data['responses'] ?? null) ? $data['responses'] : [];
    }

    /**
     * Pushes price changes back to eBay and returns the per-SKU results.
     *
     * Only inventory-managed listings (with a SKU) can be updated; classic
     * listings are read-only from PrestaShop.
     *
     * @param array<int, array{sku: string, price: float, currency: string}> $updates
     *
     * @return array<int, array<string, mixed>> one entry per request (sku, statusCode, errors)
     *
     * @throws \RuntimeException on transport failure
     */
    public function updatePrices(array $updates): array
    {
        if ($this->isDemo()) {
            return $this->demoStockResponses($updates);
        }

        if (empty($updates)) {
            return [];
        }

        $requests = [];

        foreach ($updates as $update) {
            $sku = (string) $update['sku'];
            $price = (float) $update['price'];
            $currency = strtoupper((string) $update['currency']);

            if ($sku === '' || $price <= 0 || $currency === '') {
                continue;
            }

            $requests[] = [
                'sku' => $sku,
                'offers' => [
                    [
                        'price' => [
                            'value' => (string) round($price, 2),
                            'currency' => $currency,
                        ],
                    ],
                ],
            ];
        }

        if (empty($requests)) {
            return [];
        }

        $data = $this->restPost('/sell/inventory/v1/bulk_update_price_quantity', ['requests' => $requests], true);

        return is_array($data['responses'] ?? null) ? $data['responses'] : [];
    }

    /**
     * Lists the seller's orders through the Sell Fulfillment API.
     *
     * @param string|null $sinceDate ISO-8601 date (creationDate lower bound)
     * @param string|null $orderStatus optional fulfillment status filter
     *                                 (NOT_STARTED, IN_PROGRESS, FULFILLED)
     *
     * @return array<int, array<string, mixed>> normalized order entries
     *
     * @throws \RuntimeException on API failure
     */
    public function getOrders(?string $sinceDate = null, ?string $orderStatus = null): array
    {
        $query = [];

        if ($sinceDate !== null && $sinceDate !== '') {
            $query['filter'] = 'creationdate:[' . $sinceDate . '..]';
        }

        if ($orderStatus !== null && $orderStatus !== '') {
            $query['orderfulfillmentstatus'] = $orderStatus;
        }

        return $this->fetchAll('/sell/fulfillment/v1/order', 'orders', $query);
    }

    /**
     * Fetches one eBay order (Sell Fulfillment API).
     *
     * @return array<string, mixed> the raw order payload
     *
     * @throws \RuntimeException on API failure
     */
    public function getOrder(string $orderId): array
    {
        return $this->restGet('/sell/fulfillment/v1/order/' . rawurlencode($orderId));
    }

    /**
     * Fetches the eBay signature public key for a notification key id.
     *
     * @return array<string, mixed>
     */
    public function getPublicKey(string $publicKeyId): array
    {
        return $this->restGet('/commerce/notification/v1/public_key/' . rawurlencode($publicKeyId));
    }

    /**
     * Creates an eBay notification subscription for a topic on a destination.
     *
     * @return string|null the subscription id, or null on failure
     */
    public function createNotificationSubscription(string $topicId, string $destinationId): ?string
    {
        try {
            $data = $this->notificationApiRequest('POST', '/commerce/notification/v1/subscription', [
                'topicId' => $topicId,
                'status' => 'ENABLED',
                'deliveryFormat' => 'PAYLOAD',
                'payload' => [
                    'schemaVersion' => '1.0',
                    'deliveryProtocol' => 'HTTPS',
                    'format' => 'JSON',
                ],
                'destinationId' => $destinationId,
            ]);
        } catch (\RuntimeException $e) {
            return null;
        }

        $id = (string) ($data['subscriptionId'] ?? '');

        return $id !== '' ? $id : null;
    }

    /**
     * Lists the notification subscriptions.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listNotificationSubscriptions(): array
    {
        try {
            $data = $this->notificationApiRequest('GET', '/commerce/notification/v1/subscription', null);
        } catch (\RuntimeException $e) {
            return [];
        }

        return is_array($data['subscriptions'] ?? null) ? $data['subscriptions'] : [];
    }

    /**
     * Finds (or creates) the notification destination for a webhook URL and
     * returns its destination id.
     */
    public function findOrCreateDestination(string $url, string $verificationToken): ?string
    {
        try {
            $data = $this->notificationApiRequest('GET', '/commerce/notification/v1/destination', null);
        } catch (\RuntimeException $e) {
            return null;
        }

        foreach ($data['destinations'] ?? [] as $destination) {
            if (($destination['deliveryConfig']['endpoint'] ?? '') === $url) {
                return (string) ($destination['destinationId'] ?? '');
            }
        }

        try {
            $this->notificationApiRequest('POST', '/commerce/notification/v1/destination', [
                'name' => 'LinkBay webhook',
                'status' => 'ENABLED',
                'deliveryConfig' => [
                    'endpoint' => $url,
                    'verificationToken' => $verificationToken,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return null;
        }

        // The creation returns 201 with no body: re-read the list instead.
        try {
            $data = $this->notificationApiRequest('GET', '/commerce/notification/v1/destination', null);
        } catch (\RuntimeException $e) {
            return null;
        }

        foreach ($data['destinations'] ?? [] as $destination) {
            if (($destination['deliveryConfig']['endpoint'] ?? '') === $url) {
                return (string) ($destination['destinationId'] ?? '');
            }
        }

        return null;
    }

    /**
     * Deletes an eBay notification subscription.
     */
    public function deleteNotificationSubscription(string $subscriptionId): bool
    {
        if ($subscriptionId === '') {
            return false;
        }

        try {
            $this->notificationApiRequest('DELETE', '/commerce/notification/v1/subscription/' . rawurlencode($subscriptionId), null);

            return true;
        } catch (\RuntimeException $e) {
            return false;
        }
    }

    /**
     * Calls the Notification API with the application-level (client
     * credentials) token, required by the destination/subscription endpoints.
     *
     * @param string $method
     * @param string $path
     * @param array<string, mixed>|null $json
     *
     * @return array<string, mixed>
     */
    private function notificationApiRequest(string $method, string $path, ?array $json = null): array
    {
        $data = $this->requestToken([
            'grant_type' => 'client_credentials',
            'scope' => 'https://api.ebay.com/oauth/api_scope',
        ]);

        $token = (string) ($data['access_token'] ?? '');
        if ($token === '') {
            throw new \RuntimeException('Unable to obtain an application token for the eBay notification API.');
        }

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];

        if ($json !== null) {
            $options['json'] = $json;
        }

        try {
            $response = $this->http()->request($method, $this->apiBase() . $path, $options);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('eBay notification API request failed: ' . $this->describeGuzzleError($e), 0, $e);
        }

        return $this->decodeJson((string) $response->getBody());
    }

    /**
     * Fetches eBay categories for the configured site (Taxonomy API).
     *
     * Kept compatible with the historical Trading API shape so callers only
     * consume CategoryID / CategoryName / LeafCategory / CategoryParentID.
     *
     * @return array<int, array<string, mixed>> list of Category entries
     *
     * @throws \RuntimeException on API failure
     */
    public function getCategories(int $categoryParent = -1, int $levelLimit = 1, ?string $marketplaceId = null): array
    {
        if ($this->isDemo()) {
            return DemoData::categories($categoryParent);
        }

        $marketplaceId = $marketplaceId ?? $this->marketplaceId();
        $cacheKey = 'list:' . $marketplaceId . ':' . $categoryParent;

        $cached = $this->taxonomyCache()->get($cacheKey);
        if ($cached !== null) {
            /* @var array<int, array<string, mixed>> $cached */
            return $cached;
        }

        // An API refresh is a good moment to tidy up the cache.
        $this->taxonomyCache()->prune();

        $treeId = $this->getDefaultCategoryTreeId($marketplaceId);

        if ($categoryParent <= 0) {
            $data = $this->restGet('/commerce/taxonomy/v1/category_tree/' . $treeId, [], false, $marketplaceId);
            $nodes = $data['rootCategoryNode']['childCategoryTreeNodes'] ?? [];
        } else {
            $data = $this->restGet(
                '/commerce/taxonomy/v1/category_tree/' . $treeId . '/get_category_subtree',
                ['category_id' => (string) $categoryParent],
                false,
                $marketplaceId
            );
            $nodes = $data['categorySubtreeNode']['childCategoryTreeNodes'] ?? [];
        }

        $categories = [];

        foreach ($nodes as $node) {
            $categories[] = [
                'CategoryID' => (string) ($node['category']['categoryId'] ?? ''),
                'CategoryName' => (string) ($node['category']['categoryName'] ?? ''),
                'LeafCategory' => (bool) ($node['category']['leafCategoryTreeNode'] ?? false),
                'CategoryParentID' => $categoryParent <= 0 ? 0 : $categoryParent,
            ];
        }

        $this->taxonomyCache()->set($cacheKey, $categories);

        // Warm the "cat:" cache for every listed category: the subsequent
        // getCategoryInfo calls (breadcrumb, back button) then cost no
        // extra API round-trip.
        foreach ($categories as $category) {
            $this->taxonomyCache()->set('cat:' . $marketplaceId . ':' . $category['CategoryID'], [
                'Category' => [
                    'CategoryID' => $category['CategoryID'],
                    'CategoryParentID' => $category['CategoryParentID'],
                    'CategoryName' => $category['CategoryName'],
                ],
            ]);
        }

        return $categories;
    }

    /**
     * Fetches metadata for a single eBay category (used for breadcrumb navigation).
     *
     * @return array<string, mixed> category info (CategoryID, CategoryParentID, CategoryName)
     *
     * @throws \RuntimeException on API failure
     */
    public function getCategoryInfo(string $categoryId, ?string $marketplaceId = null): array
    {
        if ($this->isDemo()) {
            return DemoData::categoryInfo($categoryId);
        }

        $marketplaceId = $marketplaceId ?? $this->marketplaceId();
        $cacheKey = 'cat:' . $marketplaceId . ':' . $categoryId;

        $cached = $this->taxonomyCache()->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $data = $this->restGet(
            '/commerce/taxonomy/v1/category_tree/' . $this->getDefaultCategoryTreeId($marketplaceId) . '/get_category_subtree',
            ['category_id' => $categoryId],
            false,
            $marketplaceId
        );

        $node = $data['categorySubtreeNode'] ?? [];
        $parent = 0;

        $href = (string) ($node['parentCategoryTreeNodeHref'] ?? '');
        if (preg_match('/[?&]category_id=(\d+)/', $href, $matches)) {
            $parent = (int) $matches[1];
        }

        $info = [
            'Category' => [
                'CategoryID' => $categoryId,
                'CategoryParentID' => $parent,
                'CategoryName' => (string) ($node['category']['categoryName'] ?? ''),
            ],
        ];

        $this->taxonomyCache()->set($cacheKey, $info);

        return $info;
    }

    /**
     * Resolves the configured site ids to REST marketplace ids.
     *
     * @return string[]
     */
    private function marketplaceIds(): array
    {
        $ids = [];

        foreach (explode(',', (string) \Configuration::get(Config::EBAY_SITE_ID)) as $siteId) {
            $siteId = (int) trim($siteId);
            if (isset(self::MARKETPLACES[$siteId])) {
                $ids[] = self::MARKETPLACES[$siteId];
            }
        }

        $ids = array_values(array_unique($ids));

        return !empty($ids) ? $ids : ['EBAY_US'];
    }

    /**
     * First selected marketplace (used for taxonomy browsing, tests and pushes).
     */
    private function marketplaceId(): string
    {
        $ids = $this->marketplaceIds();

        return $ids[0];
    }

    /**
     * Default taxonomy category tree id per marketplace (cache).
     *
     * @var array<string, string>
     */
    private array $categoryTreeIds = [];

    private function getDefaultCategoryTreeId(?string $marketplaceId = null): string
    {
        $marketplaceId = $marketplaceId ?? $this->marketplaceId();

        if (isset($this->categoryTreeIds[$marketplaceId])) {
            return $this->categoryTreeIds[$marketplaceId];
        }

        $data = $this->restGet('/commerce/taxonomy/v1/get_default_category_tree_id', [
            'marketplace_id' => $marketplaceId,
        ], false, $marketplaceId);

        $this->categoryTreeIds[$marketplaceId] = (string) ($data['categoryTreeId'] ?? '0');

        return $this->categoryTreeIds[$marketplaceId];
    }

    /**
     * Paginates through a REST list endpoint and returns every entry.
     *
     * @param string $path
     * @param string $listKey
     * @param array<string, mixed> $query
     * @param string|null $marketplaceId
     *
     * @return array<int, array<string, mixed>>
     */
    private function fetchAll(string $path, string $listKey, array $query = [], ?string $marketplaceId = null): array
    {
        $all = [];
        $offset = 0;
        $limit = 100;
        $iterations = 0;

        do {
            $data = $this->restGet(
                $path,
                array_merge($query, ['limit' => $limit, 'offset' => $offset]),
                false,
                $marketplaceId
            );

            $page = $data[$listKey] ?? [];
            if (!is_array($page)) {
                $page = [];
            }

            foreach ($page as $entry) {
                if (is_array($entry)) {
                    $all[] = $entry;
                }
            }

            $total = (int) ($data['total'] ?? 0);
            $offset += count($page);
            ++$iterations;
        } while ($offset < $total && !empty($page) && $iterations < 1000);

        return $all;
    }

    /**
     * Performs an authenticated GET request against an eBay REST endpoint.
     *
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     *
     * @throws \RuntimeException on HTTP or API error
     */
    private function restGet(string $path, array $query = [], bool $allow404 = false, ?string $marketplaceId = null): array
    {
        $token = $this->getValidAccessToken();
        if ($token === null || $token === '') {
            throw new \RuntimeException('LinkBay is not connected to eBay yet.');
        }

        try {
            $response = $this->sendWithRetry(function () use ($token, $path, $query, $marketplaceId) {
                return $this->http()->get($this->apiBase() . $path, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Accept' => 'application/json',
                        'X-EBAY-C-MARKETPLACE-ID' => $marketplaceId ?? $this->marketplaceId(),
                    ],
                    'query' => $query,
                ]);
            });
        } catch (GuzzleException $e) {
            if ($allow404
                && method_exists($e, 'getResponse')
                && $e->getResponse() !== null
                && $e->getResponse()->getStatusCode() === 404) {
                return [];
            }

            throw new \RuntimeException($this->formatRequestError('GET', $path, $e), 0, $e);
        }

        return $this->decodeJson((string) $response->getBody());
    }

    /**
     * Performs an authenticated POST request against an eBay REST endpoint.
     *
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed>
     *
     * @throws \RuntimeException on HTTP or API error
     */
    private function restPost(string $path, array $body, bool $allow400 = false, ?string $marketplaceId = null): array
    {
        $token = $this->getValidAccessToken();
        if ($token === null || $token === '') {
            throw new \RuntimeException('LinkBay is not connected to eBay yet.');
        }

        try {
            $response = $this->sendWithRetry(function () use ($token, $path, $body, $marketplaceId) {
                return $this->http()->post($this->apiBase() . $path, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'X-EBAY-C-MARKETPLACE-ID' => $marketplaceId ?? $this->marketplaceId(),
                    ],
                    'json' => $body,
                ]);
            });
        } catch (GuzzleException $e) {
            // Some endpoints (e.g. bulk_update_price_quantity) return per-request
            // business results inside a 400 body instead of a transport error.
            if ($allow400
                && method_exists($e, 'getResponse')
                && $e->getResponse() !== null
                && $e->getResponse()->getStatusCode() >= 400
                && $e->getResponse()->getStatusCode() < 500) {
                return $this->decodeJson((string) $e->getResponse()->getBody());
            }

            throw new \RuntimeException($this->formatRequestError('POST', $path, $e), 0, $e);
        }

        return $this->decodeJson((string) $response->getBody());
    }

    /**
     * Builds a precise, actionable API error message: verb, endpoint, HTTP
     * status and eBay's own explanation.
     */
    private function formatRequestError(string $method, string $path, GuzzleException $e): string
    {
        $status = 0;

        if (method_exists($e, 'getResponse') && $e->getResponse() !== null) {
            $status = $e->getResponse()->getStatusCode();
        }

        return sprintf(
            'eBay API request failed [%s %s] (HTTP %s): %s',
            $method,
            $path,
            $status > 0 ? (string) $status : 'no response',
            $this->describeGuzzleError($e)
        );
    }

    /**
     * Sends a request, retrying once on eBay throttling (429) and transient
     * server errors (5xx) after a short pause.
     */
    private function sendWithRetry(callable $request): \Psr\Http\Message\ResponseInterface
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= 2; ++$attempt) {
            try {
                return $request();
            } catch (GuzzleException $e) {
                $lastException = $e;

                $status = method_exists($e, 'getResponse') && $e->getResponse() !== null
                    ? $e->getResponse()->getStatusCode()
                    : 0;

                if ($attempt < 2 && ($status === 429 || $status >= 500)) {
                    sleep(2);
                    continue;
                }

                throw $e;
            }
        }

        throw $lastException ?? new \RuntimeException('eBay API request failed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(string $raw): array
    {
        if (trim($raw) === '') {
            return [];
        }

        $data = json_decode($raw, true);

        if (!is_array($data)) {
            throw new \RuntimeException('eBay API error: unexpected response.');
        }

        return $data;
    }

    /**
     * Extracts a human readable message from an eBay REST error response.
     */
    private function describeGuzzleError(GuzzleException $e): string
    {
        $message = $e->getMessage();

        if (method_exists($e, 'getResponse') && $e->getResponse() !== null) {
            $status = $e->getResponse()->getStatusCode();

            $data = json_decode((string) $e->getResponse()->getBody(), true);
            if (is_array($data) && isset($data['errors'][0])) {
                $error = $data['errors'][0];
                $message = (string) ($error['longMessage'] ?? $error['message'] ?? $message);
            }

            if ($status === 401 || $status === 403) {
                $message .= ' — the eBay connection may have expired; click "Connect to eBay" in the module to reconnect.';
            }
        }

        return $message;
    }

    /**
     * Performs an OAuth token request against the token endpoint.
     *
     * @param array<string, string> $params
     *
     * @return array<string, mixed>
     *
     * @throws \RuntimeException on HTTP or API error
     */
    private function requestToken(array $params): array
    {
        try {
            $response = $this->http()->post($this->tokenEndpoint(), [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => $params,
            ]);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('eBay token request failed: ' . $this->describeGuzzleError($e), 0, $e);
        }

        $data = json_decode((string) $response->getBody(), true);

        if (!is_array($data)) {
            throw new \RuntimeException('eBay OAuth error: invalid response.');
        }

        if (isset($data['error'])) {
            throw new \RuntimeException('eBay OAuth error: ' . (string) ($data['error_description'] ?? $data['error']));
        }

        return $data;
    }

    /**
     * Normalizes the response of an OAuth token grant.
     *
     * @param array<string, mixed> $data
     *
     * @return array{access_token: string, refresh_token: string, expires_in: int}
     */
    private function normalizeTokenResponse(array $data): array
    {
        return [
            'access_token' => (string) ($data['access_token'] ?? ''),
            'refresh_token' => (string) ($data['refresh_token'] ?? ''),
            'expires_in' => (int) ($data['expires_in'] ?? 7200),
        ];
    }

    private function http(): Client
    {
        if ($this->httpClient === null) {
            $this->httpClient = new Client([
                'timeout' => 30,
                'connect_timeout' => 10,
                'http_errors' => true,
            ]);
        }

        return $this->httpClient;
    }

    private function apiBase(): string
    {
        return $this->environment === self::ENV_SANDBOX
            ? 'https://api.sandbox.ebay.com'
            : 'https://api.ebay.com';
    }

    private function authEndpoint(): string
    {
        return $this->environment === self::ENV_SANDBOX
            ? 'https://auth.sandbox.ebay.com/oauth2/authorize'
            : 'https://auth.ebay.com/oauth2/authorize';
    }

    private function tokenEndpoint(): string
    {
        return $this->environment === self::ENV_SANDBOX
            ? 'https://api.sandbox.ebay.com/identity/v1/oauth2/token'
            : 'https://api.ebay.com/identity/v1/oauth2/token';
    }
}
