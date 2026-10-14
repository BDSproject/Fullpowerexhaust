<?php
/**
 * FPE Paiement — webhook Stripe
 * ---------------------------------------------------------------------------
 * Reçoit les événements Stripe (checkout.session.completed) et confirme la
 * commande. Vérifie la signature HMAC avant tout traitement.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class FpePaymentWebhookModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function init()
    {
        parent::init();
        $this->ajax = true;
    }

    public function postProcess()
    {
        $payload = @file_get_contents('php://input');
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        if ($payload === false || $payload === '' || !$this->module->verifyWebhookSignature($payload, $signature)) {
            http_response_code(400);
            exit('Signature invalide');
        }

        $event = json_decode($payload, true);
        if (!is_array($event) || empty($event['type'])) {
            http_response_code(400);
            exit('Événement invalide');
        }

        if ($event['type'] !== 'checkout.session.completed') {
            http_response_code(200);
            exit('Ignoré : ' . $event['type']);
        }

        $session = $event['data']['object'] ?? [];
        $reference = $session['client_reference_id'] ?? '';
        if ($reference === '' || ($session['payment_status'] ?? '') !== 'paid') {
            http_response_code(200);
            exit('Session non payée');
        }

        $order = $this->module->getOrderByReference($reference);
        if (!$order) {
            http_response_code(200);
            exit('Commande introuvable : ' . $reference);
        }

        $this->module->markOrderAsPaid($order);

        http_response_code(200);
        exit('OK');
    }
}
