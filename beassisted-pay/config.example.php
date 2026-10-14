<?php
/**
 * Beassisted Pay — configuration du backend (plateforme Stripe Connect)
 * ---------------------------------------------------------------------------
 * Ce fichier détient la clé secrète de la PLATEFORME Beassisted. Il ne doit
 * JAMAIS être distribué ni versionné : il reste sur le serveur de Beassisted.
 *
 * Copiez-le en `config.local.php` et renseignez vos valeurs.
 */

return [
    // Clé secrète de la PLATEFORME Beassisted (Dashboard Stripe → Clés API)
    // Attention : ne JAMAIS mettre cette clé dans le module distribué.
    'stripe_secret_key' => 'sk_live_xxxxxxxxxxxxxxxxxxxx',

    // ID client Connect (Standard) : Dashboard → Paramètres → Connect
    // Format : ca_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
    'connect_client_id' => 'ca_xxxxxxxxxxxxxxxxxxxx',

    // URL publique de CE backend (ex. https://pay.beassistednow.com)
    'backend_url' => 'https://pay.beassistednow.com',

    // Secret de signature du webhook Stripe (Dashboard → Webhooks)
    'webhook_secret' => 'whsec_xxxxxxxxxxxx',

    // Commission par défaut prélevée par Beassisted sur chaque paiement
    'commission_type' => 'percent',   // 'percent' ou 'fixed'
    'commission_value' => 3,          // 3 (%) ou 1.50 (€)

    // Dossier de stockage (marchands + états d'onboarding) — doit être en écriture
    'storage_dir' => __DIR__ . '/data',
];
