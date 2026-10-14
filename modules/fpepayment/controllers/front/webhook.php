<?php
/**
 * FPE Paiement — webhook (notification du backend Beassisted)
 * ---------------------------------------------------------------------------
 * Le backend reçoit les événements Stripe (clé plateforme) et transmet ici une
 * notification signée avec la clé API du marchand. Ce contrôleur vérifie la
 * signature et confirme la commande.
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
        $payload = (string) file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_BEASSISTED_SIGNATURE'] ?? '';

        if ($payload === '' || !$this->module->verifyBackendSignature($payload, $signature)) {
            http_response_code(400);
            exit('Signature invalide');
        }

        $event = json_decode($payload, true);
        if (!is_array($event) || ($event['payment_status'] ?? '') !== 'paid') {
            http_response_code(200);
            exit('Non payé');
        }

        $reference = (string) ($event['client_reference_id'] ?? '');
        if ($reference === '') {
            http_response_code(200);
            exit('Référence absente');
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
