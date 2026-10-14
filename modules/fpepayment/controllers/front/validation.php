<?php
/**
 * FPE Paiement — contrôleur de validation
 * ---------------------------------------------------------------------------
 * Étape « Passer la commande » :
 *   1. crée la commande PrestaShop en « attente de paiement » ;
 *   2. crée une Checkout Session Stripe ;
 *   3. redirige le client vers la page de paiement hébergée par Stripe.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class FpePaymentValidationModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function init()
    {
        parent::init();
        $this->ajax = false;
    }

    public function postProcess()
    {
        $cart = $this->context->cart;

        if (!Validate::isLoadedObject($cart) || !$cart->nbProducts()) {
            Tools::redirect('index.php?controller=order');
        }

        $customer = new Customer((int) $cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order');
        }

        $currency = new Currency((int) $cart->id_currency);
        if (strtoupper($currency->iso_code) !== 'EUR') {
            Tools::redirect('index.php?controller=order');
        }

        $total = (float) $cart->getOrderTotal();
        $awaitingState = (int) Configuration::get('FPE_STRIPE_AWAITING_STATE', null, null, null, (int) Configuration::get('PS_OS_CHEQUE'));

        // 1. Créer la commande « en attente de paiement »
        $this->module->validateOrder(
            (int) $cart->id,
            $awaitingState,
            $total,
            $this->module->displayName,
            null,
            [],
            (int) $currency->id,
            false,
            $customer->secure_key
        );

        $order = Order::getOrderByCartId((int) $cart->id);
        if (!$order) {
            $this->errors[] = $this->l('Impossible de créer la commande.');
            $this->redirectWithNotifications('index.php?controller=order');
        }
        $order = new Order((int) $order);
        $reference = $order->reference;

        // 2. Créer la session Stripe
        $successUrl = $this->context->link->getModuleLink($this->module->name, 'payment_return', ['session_id' => '{CHECKOUT_SESSION_ID}'], true);
        $cancelUrl = $this->context->link->getModuleLink($this->module->name, 'payment_return', ['cancel' => '1'], true);

        $session = $this->module->createCheckoutSession($successUrl, $cancelUrl, $reference, $total, $customer->email);

        if (isset($session['error'])) {
            $this->errors[] = 'Erreur Stripe : ' . $session['error'];
            $this->redirectWithNotifications('index.php?controller=order');
        }

        // 3. Rediriger vers la page hébergée Stripe
        if (!empty($session['url'])) {
            Tools::redirect($session['url']);
        }

        $this->errors[] = 'Stripe n\'a pas renvoyé d\'URL de paiement.';
        $this->redirectWithNotifications('index.php?controller=order');
    }
}
