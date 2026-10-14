<?php
/**
 * FPE Paiement (Stripe Connect) — module marchand distribué
 * ---------------------------------------------------------------------------
 * Ce module est destiné à être installé chez des marchands. Il est volontairement
 * LÉGER et ne contient AUCUNE clé secrète de la plateforme :
 *
 *   - le marchand clique « Se connecter à Stripe » → onboarding hébergé Stripe ;
 *   - le backend Beassisted détient la clé secrète, prélève sa commission et
 *     reverse le solde au compte connecté du marchand ;
 *   - ce module ne fait qu'appeler le backend (clé API « marchand ») et
 *     confirmer les commandes.
 *
 * Sécurité : la clé de plateforme ne transite JAMAIS par ce module.
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
        $this->version = '2.0.0';
        $this->author = 'Beassisted';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        parent::__construct();

        $this->displayName = 'Paiement par carte (Stripe Connect)';
        $this->description = 'Encaissement par carte via Stripe Connect. Connexion en un clic, aucune clé à saisir.';
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => '9.99.99'];
        $this->confirmUninstall = $this->l('Désinstaller le module de paiement ?');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('paymentOptions')
            && Configuration::updateValue('FPE_STRIPE_BACKEND_URL', '')
            && Configuration::updateValue('FPE_STRIPE_MERCHANT_KEY', '')
            && Configuration::updateValue('FPE_STRIPE_ACCOUNT', '')
            && Configuration::updateValue('FPE_STRIPE_AWAITING_STATE', (int) Configuration::get('PS_OS_CHEQUE'));
    }

    public function uninstall()
    {
        return parent::uninstall()
            && Configuration::deleteByName('FPE_STRIPE_BACKEND_URL')
            && Configuration::deleteByName('FPE_STRIPE_MERCHANT_KEY')
            && Configuration::deleteByName('FPE_STRIPE_ACCOUNT')
            && Configuration::deleteByName('FPE_STRIPE_AWAITING_STATE');
    }

    /* --------------------------------------------------------------------- */
    /*  Étape paiement                                                       */
    /* --------------------------------------------------------------------- */
    public function hookPaymentOptions(array $params): array
    {
        if (!$this->active || !isset($params['cart']) || !$this->checkCurrency($params['cart'])) {
            return [];
        }
        if ($this->getMerchantKey() === '' || $this->getBackendUrl() === '') {
            return [];
        }

        $option = new PaymentOption();
        $option->setModuleName($this->name)
            ->setCallToActionText($this->l('Payer par carte bancaire'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true));

        return [$option];
    }

    private function checkCurrency(Cart $cart): bool
    {
        $currency = new Currency((int) $cart->id_currency);

        return in_array(strtoupper($currency->iso_code), ['EUR'], true);
    }

    /* --------------------------------------------------------------------- */
    /*  Configuration (BO minimal)                                           */
    /* --------------------------------------------------------------------- */
    public function getContent()
    {
        $output = '';

        // Retour de l'onboarding Stripe : le backend nous renvoie la clé + l'acct
        $key = (string) Tools::getValue('fpe_merchant_key', '');
        $account = (string) Tools::getValue('fpe_stripe_account', '');
        if ($key !== '' && $account !== '') {
            Configuration::updateValue('FPE_STRIPE_MERCHANT_KEY', $key);
            Configuration::updateValue('FPE_STRIPE_ACCOUNT', $account);
            $this->registerWebhook();
            $output .= $this->displayConfirmation($this->l('Stripe connecté : ') . $account);
        }

        if (Tools::isSubmit('submitFpeDisconnect')) {
            Configuration::updateValue('FPE_STRIPE_MERCHANT_KEY', '');
            Configuration::updateValue('FPE_STRIPE_ACCOUNT', '');
            $output .= $this->displayConfirmation($this->l('Stripe déconnecté.'));
        }

        if (Tools::isSubmit('submitFpeConfig')) {
            Configuration::updateValue('FPE_STRIPE_BACKEND_URL', rtrim((string) Tools::getValue('FPE_STRIPE_BACKEND_URL'), '/'));
            Configuration::updateValue('FPE_STRIPE_AWAITING_STATE', (int) Tools::getValue('FPE_STRIPE_AWAITING_STATE'));
            if ($this->getMerchantKey() !== '') {
                $this->registerWebhook();
            }
            $output .= $this->displayConfirmation($this->l('Paramètres enregistrés.'));
        }

        $backendUrl = $this->getBackendUrl();
        $connected = $this->getMerchantKey() !== '' && $this->getAccountId() !== '';

        $returnUrl = $this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name]);
        $connectUrl = $backendUrl !== ''
            ? rtrim($backendUrl, '/') . '/onboarding/start?' . http_build_query(['return_url' => $returnUrl])
            : '';

        $this->context->smarty->assign([
            'fpe' => [
                'backend_url' => $backendUrl,
                'connected' => $connected,
                'account' => $this->getAccountId(),
                'connect_url' => $connectUrl,
                'awaiting_state' => (int) Configuration::get('FPE_STRIPE_AWAITING_STATE'),
                'order_states' => OrderState::getOrderStates((int) $this->context->language->id),
            ],
        ]);

        return $output . $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    /* --------------------------------------------------------------------- */
    /*  Accès config                                                         */
    /* --------------------------------------------------------------------- */
    public function getBackendUrl(): string
    {
        return (string) Configuration::get('FPE_STRIPE_BACKEND_URL');
    }

    public function getMerchantKey(): string
    {
        return (string) Configuration::get('FPE_STRIPE_MERCHANT_KEY');
    }

    public function getAccountId(): string
    {
        return (string) Configuration::get('FPE_STRIPE_ACCOUNT');
    }

    /* --------------------------------------------------------------------- */
    /*  Appels au backend Beassisted                                         */
    /* --------------------------------------------------------------------- */
    private function backendRequest(string $method, string $path, array $json = []): array
    {
        $base = $this->getBackendUrl();
        if ($base === '') {
            return ['error' => 'Backend non configuré.'];
        }

        $ch = curl_init($base . $path);
        $headers = ['Content-Type: application/json'];
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_CONNECTTIMEOUT => 10,
        ];
        if ($method === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = json_encode($json);
        }
        curl_setopt_array($ch, $opts);
        $body = (string) curl_exec($ch);
        $err = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($body, true);
        if ($err) {
            return ['error' => $err];
        }
        if ($code >= 400 || !is_array($data)) {
            return ['error' => $data['error'] ?? ('HTTP ' . $code)];
        }

        return $data;
    }

    /**
     * Crée une session de paiement via le backend (commission prélevée côté plateforme).
     */
    public function createCheckoutSession(string $successUrl, string $cancelUrl, string $orderReference, float $totalEur, string $customerEmail): array
    {
        return $this->backendRequest('POST', '/create-session', [
            'merchant_key' => $this->getMerchantKey(),
            'amount_eur' => round($totalEur, 2),
            'currency' => 'eur',
            'order_ref' => $orderReference,
            'customer_email' => $customerEmail,
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);
    }

    /**
     * Récupère le statut d'une session via le backend.
     */
    public function retrieveCheckoutSession(string $sessionId): array
    {
        return $this->backendRequest('GET', '/retrieve-session?' . http_build_query([
            'merchant_key' => $this->getMerchantKey(),
            'session_id' => $sessionId,
        ]));
    }

    /**
     * Déclare l'URL du webhook du module au backend.
     */
    public function registerWebhook(): void
    {
        $this->backendRequest('POST', '/register-webhook', [
            'merchant_key' => $this->getMerchantKey(),
            'webhook_url' => $this->context->link->getModuleLink($this->name, 'webhook', [], true),
        ]);
    }

    /**
     * Vérifie la signature d'une notification envoyée par le backend.
     */
    public function verifyBackendSignature(string $payload, string $signature): bool
    {
        $key = $this->getMerchantKey();
        if ($key === '' || $signature === '') {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $payload, $key), $signature);
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

        return Validate::isLoadedObject($results[0]) ? $results[0] : null;
    }

    /**
     * Passe une commande en « paiement accepté ».
     */
    public function markOrderAsPaid(Order $order): bool
    {
        if ((int) $order->getCurrentState() === (int) Configuration::get('PS_OS_PAYMENT')) {
            return true;
        }

        $history = new OrderHistory();
        $history->id_order = (int) $order->id;
        $history->changeIdOrderState((int) Configuration::get('PS_OS_PAYMENT'), $order, true);

        return true;
    }
}
