<?php
/**
 * Ajoute les traductions françaises manquantes (libellés du thème) de façon
 * sûre : détection des sources existantes via l'espace de noms XLIFF + ids uniques.
 * Usage : php -d memory_limit=768M tools/add_missing_fr.php
 */

if (PHP_SAPI !== 'cli') {
    die('CLI only');
}

$NS = 'urn:oasis:names:tc:xliff:document:1.2';

// Liste finale : uniquement les chaînes ABSENTES du pack d'origine.
$additions = [
    'translations/fr-FR/ShopThemeCatalog.fr-FR.xlf' => [
        'Latest arrivals' => 'Dernières arrivées',
        'All new products' => 'Tous les nouveaux produits',
        'All best sellers' => 'Toutes les meilleures ventes',
        'Search products...' => 'Rechercher des produits…',
        'Search products' => 'Rechercher des produits',
        'Price: ' => 'Prix : ',
        'Regular price: ' => 'Prix habituel : ',
    ],
    'translations/fr-FR/ShopThemeActions.fr-FR.xlf' => [
        'Add to cart %product_name%' => 'Ajouter au panier %product_name%',
        'See details' => 'Voir le détail',
    ],
    'translations/fr-FR/ShopThemeGlobal.fr-FR.xlf' => [
        'Product availability:' => 'Disponibilité du produit :',
    ],
    'translations/fr-FR/ModulesProductcommentsShop.fr-FR.xlf' => [
        'Rating for ' => 'Note pour ',
    ],
];

foreach ($additions as $file => $pairs) {
    $path = __DIR__ . '/../' . $file;
    if (!is_file($path)) {
        echo "✖ {$file} introuvable\n";
        continue;
    }

    $xml = simplexml_load_file($path);
    $xml->registerXPathNamespace('x', $NS);

    // Sources déjà présentes
    $existingSources = [];
    foreach ($xml->xpath('//x:trans-unit/x:source') as $srcNode) {
        $existingSources[(string) $srcNode] = true;
    }

    $body = $xml->file->body;
    $added = 0;

    foreach ($pairs as $source => $target) {
        if (isset($existingSources[$source])) {
            continue; // déjà traduit
        }
        $tu = $body->addChild('trans-unit', null, $NS);
        $tu->addAttribute('id', md5($source . uniqid('', true)));
        $tu->addAttribute('approved', 'yes');
        $tu->addChild('source', $source, $NS);
        $t = $tu->addChild('target', $target, $NS);
        $t->addAttribute('state', 'final');
        $tu->addChild('note', 'File: fullpowerexhaust/custom', $NS);
        $added++;
        $existingSources[$source] = true;
    }

    $xml->asXML($path);
    echo "✔ {$file} : {$added} ajout(s)\n";
}

echo "Terminé.\n";
