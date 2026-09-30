<?php
/**
 * FULLPOWEREXHAUST — script de démo : crée quelques produits d'échappement
 * d'occasion pour prévisualiser le design (cartes produit + catalogue).
 *
 * Usage : php -d memory_limit=768M tools/create_demo_products.php
 */

if (PHP_SAPI !== 'cli') {
    die('CLI only');
}

require_once __DIR__ . '/../config/config.inc.php';

$categoryId = 2; // Home
$langId = (int) Configuration::get('PS_LANG_DEFAULT');
$taxRulesGroup = 2; // 0% (US-AK) pour garder des prix simples

$products = [
    ['BMW',        'Série 3 E90 320d',       "Ligne d'échappement arrière", 185, 2008, '92 000 km'],
    ['Audi',       'A4 B8 2.0 TDI',          "Ligne complète d'origine",    220, 2010, '118 000 km'],
    ['Volkswagen', 'Golf VI 1.6 TDI',        'Ligne sport inox',            165, 2011, '86 000 km'],
    ['Peugeot',    '308 II 1.6 BlueHDi',     'Silencieux arrière',          95,  2015, '74 000 km'],
    ['Mercedes',   'Classe C W204 220 CDI',  'Silencieux double sortie',    210, 2009, '131 000 km'],
    ['Renault',    'Mégane III 1.5 dCi',     'Ligne complète',              120, 2012, '97 000 km'],
];

foreach ($products as $i => [$brand, $model, $piece, $price, $year, $km]) {
    $name = "$brand $model — $piece";

    $p = new Product();
    $p->name = [$langId => $name];
    $p->link_rewrite = [$langId => Tools::str2url($name)];
    $p->reference = 'PE-' . (1000 + $i);
    $p->ean13 = '';
    $p->price = $price;
    $p->id_tax_rules_group = $taxRulesGroup;
    $p->wholesale_price = round($price * 0.7, 2);
    $p->active = 1;
    $p->id_category_default = $categoryId;
    $p->is_virtual = 0;
    $p->available_for_order = 1;
    $p->show_price = 1;
    $p->state = 1;
    $p->quantity = 1;
    $p->minimal_quantity = 1;
    $p->condition = 'used';
    $p->description_short = [
        $langId => "$brand $model — $piece. Année $year · $km · Bon état.",
    ];
    $p->description = [
        $langId => "$brand $model — $piece.\n\nPièce d'échappement d'occasion contrôlée avant mise en vente.\nAnnée : $year\nKilométrage : $km",
    ];

    try {
        if ($p->add()) {
            $p->addToCategories([$categoryId]);
            StockAvailable::setQuantity($p->id, 0, 1);
            echo "✔ Créé #{$p->id} — {$name}\n";
        } else {
            echo "✖ Échec : {$name}\n";
        }
    } catch (Throwable $e) {
        echo "✖ Erreur {$name} : " . $e->getMessage() . "\n";
    }
}

echo "Terminé.\n";
