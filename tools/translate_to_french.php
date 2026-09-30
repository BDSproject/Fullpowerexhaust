<?php
/**
 * FULLPOWEREXHAUST — complète la traduction française :
 *   1. traduit les titres de pages (meta_lang) : Panier, Contact, etc.
 *   2. ajoute les traductions de thème manquantes (Latest arrivals, Add to cart %name%, …)
 * Usage : php -d memory_limit=768M tools/translate_to_french.php
 */

if (PHP_SAPI !== 'cli') {
    die('CLI only');
}

require_once __DIR__ . '/../config/config.inc.php';

$idFr = (int) Language::getIdByIso('fr');

// ── 1) Traduction des titres de pages (meta_lang) ────────────────────────────
$metaFr = [
    1 => 'Erreur 404',
    2 => 'Meilleures ventes',
    3 => 'Contactez-nous',
    4 => 'Accueil',
    5 => 'Marques',
    6 => 'Nouveaux produits',
    7 => 'Mot de passe oublié',
    8 => 'Promotions',
    9 => 'Plan du site',
    10 => 'Fournisseurs',
    11 => 'Adresse',
    12 => 'Adresses',
    13 => 'Connexion',
    14 => 'Inscription',
    15 => 'Panier',
    16 => 'Bons de réduction',
    17 => 'Historique des commandes',
    18 => 'Informations personnelles',
    19 => 'Mon compte',
    20 => 'Suivi de commande',
    21 => 'Avoirs',
    22 => 'Commande',
    23 => 'Recherche',
    24 => 'Magasins',
    25 => 'Suivi invité',
    26 => 'Confirmation de commande',
];

foreach ($metaFr as $idMeta => $title) {
    Db::getInstance()->update('meta_lang', ['title' => $title], 'id_meta = ' . (int) $idMeta . ' AND id_lang = ' . $idFr);
}

// ── 2) Traductions de thème manquantes (XLF) ────────────────────────────────
$additions = [
    'translations/fr-FR/ShopThemeCatalog.fr-FR.xlf' => [
        'Latest arrivals' => 'Dernières arrivées',
        'All new products' => 'Tous les nouveaux produits',
        'All best sellers' => 'Toutes les meilleures ventes',
        'Search products...' => 'Rechercher des produits…',
        'Search products' => 'Rechercher des produits',
    ],
    'translations/fr-FR/ShopThemeActions.fr-FR.xlf' => [
        'Add to cart %product_name%' => 'Ajouter au panier %product_name%',
        'See details' => 'Voir le détail',
        'Add to cart' => 'Ajouter au panier',
    ],
];

foreach ($additions as $file => $pairs) {
    $path = __DIR__ . '/../' . $file;
    if (!is_file($path)) {
        echo "✖ {$file} introuvable\n";
        continue;
    }
    $content = file_get_contents($path);
    $xml = new SimpleXMLElement($content);
    $body = $xml->file->body;

    foreach ($pairs as $source => $target) {
        $existing = $body->xpath('//trans-unit[source="' . $source . '"]');
        if ($existing) {
            continue; // déjà traduit
        }
        $tu = $body->addChild('trans-unit');
        $tu->addAttribute('id', md5($source . mt_rand()));
        $tu->addAttribute('approved', 'yes');
        $tu->addChild('source', $source);
        $t = $tu->addChild('target', $target);
        $t->addAttribute('state', 'final');
        $tu->addChild('note', 'File: fullpowerexhaust/custom');
    }

    $xml->asXML($path);
    echo "✔ {$file} mis à jour\n";
}

echo "Terminé.\n";
