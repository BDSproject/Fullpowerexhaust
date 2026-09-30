<?php
/**
 * FULLPOWEREXHAUST — traduit le contenu restant en français :
 *   - descriptions meta (footer)
 *   - pages CMS par défaut (livraison, mentions, CGU, à propos, paiement)
 * Usage : php -d memory_limit=768M tools/translate_content.php
 */

if (PHP_SAPI !== 'cli') {
    die('CLI only');
}

require_once __DIR__ . '/../config/config.inc.php';

$idFr = (int) Language::getIdByIso('fr');

// ── 1) Descriptions meta_lang ───────────────────────────────────────────────
$metaDesc = [
    1 => 'Cette page est introuvable',
    2 => 'Nos meilleures ventes',
    3 => 'Utilisez notre formulaire pour nous contacter',
    5 => 'Liste des marques',
    6 => 'Nos nouveaux produits',
    7 => 'Saisissez l\'adresse e-mail utilisée pour vous connecter afin de recevoir un e-mail avec un nouveau mot de passe',
    8 => 'Nos promotions',
    9 => 'Perdu ? Trouvez ce que vous cherchez',
    10 => 'Liste des fournisseurs',
];
foreach ($metaDesc as $idMeta => $desc) {
    Db::getInstance()->update('meta_lang', ['description' => pSQL($desc)], 'id_meta = ' . (int) $idMeta . ' AND id_lang = ' . $idFr);
}

// ── 2) Pages CMS par défaut ────────────────────────────────────────────────
$cms = [
    1 => [
        'meta_title' => 'Livraison',
        'meta_description' => 'Nos conditions de livraison',
        'link_rewrite' => 'livraison',
        'content' => '<h2>Livraison</h2><p>Vos pièces d\'échappement sont expédiées sous 3 à 5 jours ouvrés après réception du paiement, avec un emballage soigné et un numéro de suivi.</p>',
    ],
    2 => [
        'meta_title' => 'Mentions légales',
        'meta_description' => 'Mentions légales du site',
        'link_rewrite' => 'mentions-legales',
        'content' => '<h2>Mentions légales</h2><p>FullPowerExhaust — e-commerce de pièces d\'échappement d\'occasion.</p>',
    ],
    3 => [
        'meta_title' => 'Conditions générales de vente',
        'meta_description' => 'Nos conditions générales de vente',
        'link_rewrite' => 'conditions-generales-de-vente',
        'content' => '<h2>Conditions générales de vente</h2><p>Les présentes conditions régissent l\'ensemble des ventes réalisées via fullpowerexhaust.com.</p>',
    ],
    4 => [
        'meta_title' => 'À propos',
        'meta_description' => 'Qui sommes-nous ?',
        'link_rewrite' => 'a-propos',
        'content' => '<h2>À propos</h2><p>FullPowerExhaust est spécialiste de l\'échappement performance d\'occasion, toutes marques confondues.</p>',
    ],
    5 => [
        'meta_title' => 'Paiement sécurisé',
        'meta_description' => 'Nos moyens de paiement sécurisés',
        'link_rewrite' => 'paiement-securise',
        'content' => '<h2>Paiement sécurisé</h2><p>Réglez en toute sécurité par carte bancaire, PayPal ou virement.</p>',
    ],
];

foreach ($cms as $idCms => $data) {
    $safe = array_map('pSQL', $data);
    Db::getInstance()->update('cms_lang', $safe, 'id_cms = ' . (int) $idCms . ' AND id_lang = ' . $idFr);
}

echo "✔ Contenu traduit (meta descriptions + CMS).\n";
