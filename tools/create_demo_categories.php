<?php
/**
 * FULLPOWEREXHAUST — crée des catégories de démonstration pour vérifier que
 * le menu du header s'adapte à l'ajout de catégories PrestaShop.
 * Usage : php -d memory_limit=768M tools/create_demo_categories.php
 */

if (PHP_SAPI !== 'cli') {
    die('CLI only');
}

require_once __DIR__ . '/../config/config.inc.php';

$idFr = (int) Language::getIdByIso('fr');
$parent = 2; // « Accueil » (racine)

$cats = [
    ['Échappements', 'echappements'],
    ['BMW', 'bmw'],
    ['Audi', 'audi'],
];

foreach ($cats as [$name, $rewrite]) {
    $existing = (int) Db::getInstance()->getValue(
        'SELECT id_category FROM ' . _DB_PREFIX_ . 'category_lang WHERE name = "' . pSQL($name) . '" AND id_lang = ' . $idFr
    );
    if ($existing) {
        echo "— catégorie « {$name} » déjà présente (#{$existing}), ignorée\n";
        continue;
    }

    $cat = new Category();
    $cat->name = [$idFr => $name];
    $cat->link_rewrite = [$idFr => $rewrite];
    $cat->id_parent = $parent;
    $cat->active = 1;
    $cat->description = [$idFr => $name];

    if ($cat->add()) {
        echo "✔ catégorie « {$name} » créée (#{$cat->id})\n";
    } else {
        echo "✖ échec création « {$name} »\n";
    }
}

echo "Terminé.\n";
