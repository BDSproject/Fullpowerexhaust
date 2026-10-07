<?php
/**
 * FULLPOWEREXHAUST — enrichit le catalogue :
 *   1. crée les caractéristiques « Type de pièce » et « État » ;
 *   2. crée des produits moto de démonstration (marque + caractéristiques + prix) ;
 *   3. configure la recherche facettée (marque, prix, type de pièce, état).
 *
 * Usage : php -d memory_limit=768M tools/setup_catalog_filters.php
 */

if (PHP_SAPI !== 'cli') {
    die('CLI only');
}

require_once __DIR__ . '/../config/config.inc.php';

$langId = (int) Configuration::get('PS_LANG_DEFAULT');
$frId = (int) Language::getIdByIso('fr');
$langId = $frId ?: $langId;

// ─────────────────────────────────────────────────────────────────────────────
// 1. Caractéristiques (features) + valeurs
// ─────────────────────────────────────────────────────────────────────────────
function createFeatureWithValues($name, $values, $langId) {
    $feature = new Feature();
    $feature->name = [$langId => $name];
    $feature->position = 1;
    if (!$feature->add()) {
        echo "  ✖ feature {$name}\n";
        return null;
    }
    $valueIds = [];
    foreach ($values as $i => $valueName) {
        $fv = new FeatureValue();
        $fv->id_feature = $feature->id;
        $fv->value = [$langId => $valueName];
        $fv->add();
        $valueIds[$valueName] = $fv->id;
    }
    echo "  ✔ Feature #{$feature->id} « {$name} » (" . count($values) . " valeurs)\n";
    return [$feature->id, $valueIds];
}

echo "Création des caractéristiques…\n";
list($featTypeId, $typeValues) = createFeatureWithValues('Type de pièce', [
    'Ligne complète', 'Silencieux', 'Catalyseur', 'Collecteur',
    'Décata', 'Sortie d\'échappement', 'Filtre à air',
], $langId);

list($featStateId, $stateValues) = createFeatureWithValues('État', [
    'Neuf', 'Comme neuf', 'Très bon état', 'Bon état', 'À rénover',
], $langId);

// ─────────────────────────────────────────────────────────────────────────────
// 2. Produits moto de démonstration
// ─────────────────────────────────────────────────────────────────────────────
echo "\nProduits de démonstration…\n";

// Supprime les anciens produits (auto) pour repartir propre
foreach (Db::getInstance()->executeS('SELECT id_product FROM ' . _DB_PREFIX_ . 'product') as $row) {
    $old = new Product($row['id_product']);
    $old->delete();
}
echo "  anciens produits supprimés.\n";

$manufacturerByName = [];
foreach (Manufacturer::getManufacturers(false, $langId) as $m) {
    $manufacturerByName[$m['name']] = $m['id_manufacturer'];
}

$products = [
    // [marque, modèle, type, état, prix, année, km]
    ['Yamaha', 'MT-07', 'Ligne complète', 'Très bon état', 320, 2018, '24 000 km'],
    ['Honda', 'CB650R', 'Silencieux', 'Neuf', 180, 2021, '0 km'],
    ['Kawasaki', 'Z900', 'Collecteur', 'Bon état', 140, 2019, '18 000 km'],
    ['Ducati', 'Monster 821', 'Ligne complète', 'Comme neuf', 520, 2020, '6 500 km'],
    ['Suzuki', 'GSX-R 750', 'Silencieux', 'Très bon état', 210, 2017, '15 000 km'],
    ['BMW Motorrad', 'R 1250 GS', 'Catalyseur', 'Bon état', 380, 2019, '29 000 km'],
    ['Triumph', 'Street Triple 765', 'Décata', 'Neuf', 95, 2022, '0 km'],
    ['KTM', '390 Duke', 'Sortie d\'échappement', 'Très bon état', 75, 2020, '11 000 km'],
    ['Harley-Davidson', 'Sportster 883', 'Ligne complète', 'Bon état', 450, 2016, '31 000 km'],
    ['Aprilia', 'RS 660', 'Ligne complète', 'Neuf', 610, 2022, '0 km'],
    ['Husqvarna', '701 Supermoto', 'Silencieux', 'Comme neuf', 165, 2021, '8 000 km'],
    ['Royal Enfield', 'Interceptor 650', 'Collecteur', 'Très bon état', 120, 2020, '9 500 km'],
];

$taxRulesGroup = 2; // 0% pour des prix simples

foreach ($products as $i => [$brand, $model, $type, $state, $price, $year, $km]) {
    $name = "$brand $model — $type";

    $p = new Product();
    $p->name = [$langId => $name];
    $p->link_rewrite = [$langId => Tools::str2url($name)];
    $p->reference = 'FPE-' . (2000 + $i);
    $p->price = $price;
    $p->id_tax_rules_group = $taxRulesGroup;
    $p->wholesale_price = round($price * 0.6, 2);
    $p->active = 1;
    $p->id_category_default = 2; // Home
    $p->is_virtual = 0;
    $p->available_for_order = 1;
    $p->show_price = 1;
    $p->state = 1;
    $p->quantity = 1;
    $p->minimal_quantity = 1;
    $p->condition = ($state === 'Neuf') ? 'new' : 'used';
    $p->id_manufacturer = isset($manufacturerByName[$brand]) ? (int) $manufacturerByName[$brand] : 0;
    $p->description_short = [$langId => "$brand $model — $type. $year · $km · $state."];
    $p->description = [$langId => "$brand $model — $type.\n\nPièce d'échappement d'occasion contrôlée avant mise en vente.\nModèle : $model\nAnnée : $year\nKilométrage : $km\nÉtat : $state"];

    try {
        if ($p->add()) {
            $p->addToCategories([2]);
            StockAvailable::setQuantity($p->id, 0, 1);

            // Association des caractéristiques
            $features = [];
            if (isset($typeValues[$type])) {
                $features[$featTypeId] = $typeValues[$type];
            }
            if (isset($stateValues[$state])) {
                $features[$featStateId] = $stateValues[$state];
            }
            foreach ($features as $idFeature => $idValue) {
                $p->addFeaturesToDB($idFeature, $idValue);
            }

            echo "  ✔ #{$p->id} — {$name}\n";
        }
    } catch (Throwable $e) {
        echo "  ✖ {$name} : " . $e->getMessage() . "\n";
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// 3. Configuration de la recherche facettée
// ─────────────────────────────────────────────────────────────────────────────
echo "\nConfiguration de la recherche facettée…\n";

$idShop = (int) Context::getContext()->shop->id;
$idHome = 2;

$filters = [
    'shop_list' => [$idShop],
    'categories' => [$idHome],
    'controllers' => ['category', 'search', 'newproducts', 'best-sales', 'prices-drop', 'manufacturer'],
    'layered_selection_manufacturer' => ['filter_type' => 2, 'filter_show_limit' => 0],
    'layered_selection_price_slider' => ['filter_type' => 3, 'filter_show_limit' => 0],
    'layered_selection_feat_' . $featTypeId => ['filter_type' => 2, 'filter_show_limit' => 0],
    'layered_selection_feat_' . $featStateId => ['filter_type' => 2, 'filter_show_limit' => 0],
];

$serialized = serialize($filters);

// Écrase le template existant (id 1) ou le crée
$existing = (int) Db::getInstance()->getValue('SELECT id_layered_filter FROM ' . _DB_PREFIX_ . 'layered_filter ORDER BY id_layered_filter ASC');

if ($existing) {
    Db::getInstance()->execute(
        'UPDATE ' . _DB_PREFIX_ . 'layered_filter SET name = "Catalogue moto", filters = "' . pSQL($serialized) . '", n_categories = ' . count($filters['categories']) . ' WHERE id_layered_filter = ' . $existing
    );
    Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'layered_filter_shop WHERE id_layered_filter = ' . $existing);
} else {
    Db::getInstance()->execute(
        'INSERT INTO ' . _DB_PREFIX_ . 'layered_filter (name, filters, n_categories, date_add) VALUES ("Catalogue moto", "' . pSQL($serialized) . '", ' . count($filters['categories']) . ', NOW())'
    );
    $existing = (int) Db::getInstance()->Insert_ID();
}

foreach ($filters['shop_list'] as $sid) {
    Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'layered_filter_shop (id_layered_filter, id_shop) VALUES (' . $existing . ', ' . (int) $sid . ')');
}

// Invalide le cache des blocs de filtres
Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'layered_filter_block');

echo "  ✔ Recherche facettée configurée (template #{$existing})\n";

// ─────────────────────────────────────────────────────────────────────────────
// 4. Indexation des caractéristiques
// ─────────────────────────────────────────────────────────────────────────────
Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'layered_indexable_feature');
foreach ([$featTypeId, $featStateId] as $fid) {
    Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'layered_indexable_feature (id_feature, indexable) VALUES (' . (int) $fid . ', 1)');
    foreach (Db::getInstance()->executeS('SELECT id_feature_value FROM ' . _DB_PREFIX_ . 'feature_value WHERE id_feature = ' . (int) $fid) as $v) {
        Db::getInstance()->execute('INSERT IGNORE INTO ' . _DB_PREFIX_ . 'layered_indexable_feature_value_lang_value (id_feature_value, id_lang, url_name, meta_title) VALUES (' . (int) $v['id_feature_value'] . ', ' . $langId . ', "", "")');
    }
}
echo "  ✔ Caractéristiques indexées.\n";

echo "\nTerminé.\n";
