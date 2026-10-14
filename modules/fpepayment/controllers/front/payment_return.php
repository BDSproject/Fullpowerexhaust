<?php
/**
 * FPE Paiement — retour du client depuis la page hébergée Stripe.
 *   - succès : vérifie la session, confirme la commande si besoin, redirige
 *     vers la confirmation de commande ;
 *   - annulation : renvoie vers le tunnel.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class FpePaymentPaymentReturnModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function postProcess()
    {
        $sessionId = (string) Tools::getValue('session_id', '');
        $cancel = (bool) Tools::getValue('cancel', false);

        if ($cancel) {
            $this->context->smarty->assign('fpe_canceled', true);
            $this->setTemplate('module:fpepayment/views/templates/front/payment_return.tpl');

            return;
        }

        if ($sessionId === '' || $sessionId === '{CHECKOUT_SESSION_ID}') {
            Tools::redirect('index.php?controller=order');
        }

        $session = $this->module->retrieveCheckoutSession($sessionId);
        $paid = ($session['payment_status'] ?? '') === 'paid';

        if ($paid) {
            $reference = $session['client_reference_id'] ?? '';
            $order = $reference !== '' ? $this->module->getOrderByReference($reference) : null;

            if ($order) {
                $this->module->markOrderAsPaid($order);

                $customer = new Customer((int) $order->id_customer);
                $cart = new Cart((int) $order->id_cart);

                // Redirection officielle vers la confirmation de commande
                Tools::redirect(
                    'index.php?controller=order-confirmation&id_cart=' . (int) $cart->id
                    . '&id_module=' . (int) $this->module->id
                    . '&id_order=' . (int) $order->id
                    . '&key=' . $customer->secure_key
                );
            }
        }

        // Paiement pas encore confirmé (le webhook peut être en retard) : page
        // de confirmation « en cours », rafraîchie automatiquement.
        $this->context->smarty->assign([
            'fpe_paid' => $paid,
            'fpe_pending' => !$paid,
        ]);
        $this->setTemplate('module:fpepayment/views/templates/front/payment_return.tpl');
    }
}
