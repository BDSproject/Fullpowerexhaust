<?php
/**
 * Beassisted Pay — backend de la plateforme (Stripe Connect)
 * ---------------------------------------------------------------------------
 * API légère, sans framework, qui détient la clé secrète de la plateforme et
 * prend une commission sur chaque paiement.
 *
 * Endpoints :
 *   GET  /onboarding/start?return_url=…     → redirige vers l'onboarding Stripe
 *   GET  /onboarding/callback?code=…&state=… → échange le code, enregistre le
 *                                              marchand, renvoie vers le BO
 *   POST /create-session                    → crée une Checkout Session (commission
 *                                              + reversement au compte connecté)
 *   GET  /retrieve-session                  → statut d'une session (pour le module)
 *   POST /webhook                           → événements Stripe (confirmation)
 *
 * Sécurité :
 *   - la clé secrète de plateforme ne quitte jamais ce serveur ;
 *   - chaque marchand a une clé API dédiée (marchand) pour s'authentifier ;
 *   - le webhook Stripe est vérifié (HMAC-SHA256).
 */

declare(strict_types=1);

// ── Configuration ───────────────────────────────────────────────────────────
$configFile = __DIR__ . '/config.local.php';
if (!is_file($configFile)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Backend non configuré (config.local.php manquant).']);
    exit;
}
$config = require $configFile;
$storageDir = rtrim((string) $config['storage_dir'], '/');
if (!is_dir($storageDir)) {
    @mkdir($storageDir, 0755, true);
}

/** Lit un stockage JSON (marchands / états). */
function storageRead(string $storageDir, string $name): array
{
    $file = $storageDir . '/' . $name . '.json';
    if (!is_file($file)) {
        return [];
    }
    $data = json_decode((string) file_get_contents($file), true);

    return is_array($data) ? $data : [];
}

/** Écrit un stockage JSON. */
function storageWrite(string $storageDir, string $name, array $data): void
{
    file_put_contents(
        $storageDir . '/' . $name . '.json',
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        LOCK_EX
    );
}

/** Appel HTTP à l'API Stripe (cURL). */
function stripeApi(string $secretKey, string $method, string $path, array $params = []): array
{
    $url = 'https://api.stripe.com/v1' . $path;
    $headers = [
        'Authorization: Bearer ' . $secretKey,
        'Content-Type: application/x-www-form-urlencoded',
    ];

    $ch = curl_init();
    $opts = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
    ];
    if ($method === 'POST') {
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = http_build_query($params);
    } elseif ($method === 'GET' && $params) {
        $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
    }
    curl_setopt_array($ch, $opts);
    $body = (string) curl_exec($ch);
    $err = curl_error($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($body, true);
    if ($err) {
        return ['error' => $err];
    }
    if ($code >= 400 || !is_array($json)) {
        return ['error' => $json['error']['message'] ?? ('HTTP ' . $code)];
    }

    return $json;
}

/** Réponse JSON + arrêt. */
function jsonResponse(int $code, array $data): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

/** Calcule la commission de la plateforme en centimes. */
function commissionCents(array $config, float $amountEur): int
{
    $type = (string) ($config['commission_type'] ?? 'percent');
    $value = (float) ($config['commission_value'] ?? 0);
    $totalCents = (int) round($amountEur * 100);

    $fee = $type === 'fixed' ? (int) round($value * 100) : (int) round($amountEur * $value);
    if ($fee < 0) {
        $fee = 0;
    }
    if ($fee >= $totalCents) {
        $fee = max(0, $totalCents - 1);
    }

    return $fee;
}

// ── Routage ─────────────────────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$base = rtrim(parse_url((string) $config['backend_url'], PHP_URL_PATH) ?? '', '/');
if ($base !== '' && str_starts_with($path, $base)) {
    $path = substr($path, strlen($base));
}
$path = '/' . trim($path, '/');

/* ---------------------------------------------------------------------------
 * GET /onboarding/start?return_url=…
 * ------------------------------------------------------------------------- */
if ($method === 'GET' && $path === '/onboarding/start') {
    $returnUrl = (string) ($_GET['return_url'] ?? '');
    if ($returnUrl === '' || !preg_match('#^https?://#', $returnUrl)) {
        jsonResponse(400, ['error' => 'return_url invalide']);
    }

    $state = bin2hex(random_bytes(18));
    $states = storageRead($storageDir, 'states');
    $states[$state] = ['return_url' => $returnUrl, 'created_at' => time()];
    storageWrite($storageDir, 'states', $states);

    $redirectUrl = $config['backend_url'] . '/onboarding/callback';
    $oauth = 'https://connect.stripe.com/oauth/authorize?' . http_build_query([
        'response_type' => 'code',
        'client_id' => $config['connect_client_id'],
        'scope' => 'read_write',
        'redirect_uri' => $redirectUrl,
        'state' => $state,
    ]);

    header('Location: ' . $oauth);
    exit;
}

/* ---------------------------------------------------------------------------
 * GET /onboarding/callback?code=…&state=…
 * ------------------------------------------------------------------------- */
if ($method === 'GET' && $path === '/onboarding/callback') {
    $code = (string) ($_GET['code'] ?? '');
    $state = (string) ($_GET['state'] ?? '');

    $states = storageRead($storageDir, 'states');
    $pending = $states[$state] ?? null;
    if ($pending === null || $code === '') {
        jsonResponse(400, ['error' => 'État d\'onboarding inconnu ou code manquant.']);
    }
    unset($states[$state]);
    storageWrite($storageDir, 'states', $states);

    // Échange du code contre l'identifiant du compte connecté
    // (endpoint OAuth dédié : connect.stripe.com, PAS api.stripe.com)
    $ch = curl_init('https://connect.stripe.com/oauth/token');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'client_secret' => $config['stripe_secret_key'],
            'code' => $code,
            'grant_type' => 'authorization_code',
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    $oauthBody = (string) curl_exec($ch);
    curl_close($ch);
    $res = json_decode($oauthBody, true) ?: [];

    if (isset($res['error']) || empty($res['stripe_user_id'])) {
        jsonResponse(502, ['error' => 'Échec de l\'échange du code : ' . ($res['error_description'] ?? $res['error'] ?? 'inconnu')]);
    }

    $stripeAccountId = (string) $res['stripe_user_id'];

    // Crée (ou retrouve) le marchand et lui attribue une clé API
    $merchants = storageRead($storageDir, 'merchants');
    $merchantKey = null;
    foreach ($merchants as $key => $m) {
        if (($m['stripe_account_id'] ?? '') === $stripeAccountId) {
            $merchantKey = $key;
            break;
        }
    }
    if ($merchantKey === null) {
        $merchantKey = bin2hex(random_bytes(24));
    }
    $merchants[$merchantKey] = [
        'stripe_account_id' => $stripeAccountId,
        'created_at' => time(),
    ];
    storageWrite($storageDir, 'merchants', $merchants);

    $returnUrl = $pending['return_url'];
    $sep = str_contains($returnUrl, '?') ? '&' : '?';
    header('Location: ' . $returnUrl . $sep . 'fpe_merchant_key=' . urlencode($merchantKey)
        . '&fpe_stripe_account=' . urlencode($stripeAccountId));
    exit;
}

/* ---------------------------------------------------------------------------
 * POST /create-session
 * JSON : merchant_key, amount_eur, currency, order_ref, customer_email,
 *        success_url, cancel_url
 * ------------------------------------------------------------------------- */
if ($method === 'POST' && $path === '/create-session') {
    $body = json_decode((string) file_get_contents('php://input'), true) ?: [];

    $merchantKey = (string) ($body['merchant_key'] ?? '');
    $amountEur = (float) ($body['amount_eur'] ?? 0);
    $currency = strtolower((string) ($body['currency'] ?? 'eur'));
    $orderRef = (string) ($body['order_ref'] ?? '');
    $email = (string) ($body['customer_email'] ?? '');
    $successUrl = (string) ($body['success_url'] ?? '');
    $cancelUrl = (string) ($body['cancel_url'] ?? '');

    $merchants = storageRead($storageDir, 'merchants');
    $merchant = $merchants[$merchantKey] ?? null;
    if ($merchant === null) {
        jsonResponse(401, ['error' => 'Clé marchand invalide.']);
    }
    if ($amountEur <= 0 || $orderRef === '' || !preg_match('#^https?://#', $successUrl)) {
        jsonResponse(400, ['error' => 'Paramètres manquants ou invalides.']);
    }

    $totalCents = (int) round($amountEur * 100);
    $fee = commissionCents($config, $amountEur);

    $session = stripeApi((string) $config['stripe_secret_key'], 'POST', '/checkout/sessions', [
        'mode' => 'payment',
        'line_items[0][price_data][currency]' => $currency,
        'line_items[0][price_data][unit_amount]' => $totalCents,
        'line_items[0][price_data][product_data][name]' => 'Commande ' . $orderRef,
        'line_items[0][quantity]' => 1,
        'customer_email' => $email,
        'client_reference_id' => $orderRef,
        'success_url' => $successUrl,
        'cancel_url' => $cancelUrl,
        // Commission plateforme + reversement au marchand
        'payment_intent_data[application_fee_amount]' => $fee,
        'payment_intent_data[transfer_data][destination]' => $merchant['stripe_account_id'],
        'payment_intent_data[metadata][merchant]' => $merchantKey,
    ]);

    if (isset($session['error']) || empty($session['url'])) {
        jsonResponse(502, ['error' => $session['error'] ?? 'Session non créée']);
    }

    jsonResponse(200, ['url' => $session['url'], 'session_id' => $session['id']]);
}

/* ---------------------------------------------------------------------------
 * GET /retrieve-session?merchant_key=…&session_id=…
 * ------------------------------------------------------------------------- */
if ($method === 'GET' && $path === '/retrieve-session') {
    $merchantKey = (string) ($_GET['merchant_key'] ?? '');
    $sessionId = (string) ($_GET['session_id'] ?? '');

    $merchants = storageRead($storageDir, 'merchants');
    $merchant = $merchants[$merchantKey] ?? null;
    if ($merchant === null || $sessionId === '') {
        jsonResponse(401, ['error' => 'Accès refusé.']);
    }

    $session = stripeApi((string) $config['stripe_secret_key'], 'GET', '/checkout/sessions/' . rawurlencode($sessionId));
    if (isset($session['error'])) {
        jsonResponse(502, ['error' => $session['error']]);
    }

    // Vérifie que la session appartient bien à ce marchand
    $destination = $session['payment_intent']['transfer_data']['destination'] ?? '';
    if ($destination !== $merchant['stripe_account_id']) {
        jsonResponse(403, ['error' => 'Session étrangère.']);
    }

    jsonResponse(200, [
        'payment_status' => $session['payment_status'] ?? '',
        'client_reference_id' => $session['client_reference_id'] ?? '',
    ]);
}

/* ---------------------------------------------------------------------------
 * POST /register-webhook  (le module enregistre son URL de notification)
 * JSON : merchant_key, webhook_url
 * ------------------------------------------------------------------------- */
if ($method === 'POST' && $path === '/register-webhook') {
    $body = json_decode((string) file_get_contents('php://input'), true) ?: [];
    $merchantKey = (string) ($body['merchant_key'] ?? '');
    $webhookUrl = (string) ($body['webhook_url'] ?? '');

    $merchants = storageRead($storageDir, 'merchants');
    if (!isset($merchants[$merchantKey])) {
        jsonResponse(401, ['error' => 'Clé marchand invalide.']);
    }
    if ($webhookUrl === '' || !preg_match('#^https?://#', $webhookUrl)) {
        jsonResponse(400, ['error' => 'webhook_url invalide']);
    }

    $merchants[$merchantKey]['webhook_url'] = $webhookUrl;
    storageWrite($storageDir, 'merchants', $merchants);

    jsonResponse(200, ['ok' => true]);
}

/* ---------------------------------------------------------------------------
 * POST /webhook  (événements Stripe de la plateforme)
 * ------------------------------------------------------------------------- */
if ($method === 'POST' && $path === '/webhook') {
    $payload = (string) file_get_contents('php://input');
    $sig = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
    $secret = (string) $config['webhook_secret'];

    if ($secret === '' || !preg_match('/t=(\d+),v1=([a-f0-9]+)/', $sig, $m)) {
        jsonResponse(400, ['error' => 'Signature manquante.']);
    }
    $expected = hash_hmac('sha256', $m[1] . '.' . $payload, $secret);
    if (!hash_equals($expected, $m[2])) {
        jsonResponse(400, ['error' => 'Signature invalide.']);
    }

    $event = json_decode($payload, true);
    if (($event['type'] ?? '') !== 'checkout.session.completed') {
        jsonResponse(200, ['received' => true]);
    }

    $session = $event['data']['object'] ?? [];
    $merchantKey = (string) ($session['metadata']['merchant'] ?? '');
    $merchants = storageRead($storageDir, 'merchants');
    $merchant = $merchants[$merchantKey] ?? null;

    // Le module ne peut pas vérifier la signature Stripe (clé plateforme) :
    // on lui transfère une notification signée avec SA clé marchand.
    if ($merchant !== null && !empty($merchant['webhook_url'])) {
        $body = json_encode([
            'session_id' => $session['id'] ?? '',
            'client_reference_id' => $session['client_reference_id'] ?? '',
            'payment_status' => $session['payment_status'] ?? '',
        ]);
        $sig = hash_hmac('sha256', $body, $merchantKey);
        $ch = curl_init($merchant['webhook_url']);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-Beassisted-Signature: ' . $sig],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    jsonResponse(200, ['received' => true]);
}

jsonResponse(404, ['error' => 'Endpoint inconnu.']);
