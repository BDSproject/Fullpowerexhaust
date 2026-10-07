<?php
/**
 * FULLPOWEREXHAUST — ajoute la caractéristique « Marque de ligne d'échappement »
 * (Akrapovič, Termignoni, Yoshimura, …), l'associe aux produits et l'ajoute
 * à la recherche facettée. Idempotent (find-or-create).
 *
 * Usage : php -d memory_limit=768M tools/setup_exhaust_brands.php
 */

if (PHP_SAPI !== 'cli') {
    die('CLI only');
}

require_once __DIR__ . '/../config/config.inc.php';

$langId = (int) Language::getIdByIso('fr');
if (!$langId) {
    $langId = (int) Configuration::get('PS_LANG_DEFAULT');
}

// ─────────────────────────────────────────────────────────────────────────────
// 1. Liste des marques de lignes d'échappement (constructeurs d'échappements)
// ─────────────────────────────────────────────────────────────────────────────
$exhaustBrands = [
    // Slovénie / Europe
    'Akrapovič', 'Termignoni', 'Arrow', 'LeoVince', 'SC-Project', 'Remus',
    'Mivv', 'GPR Exhaust', 'Giannelli', 'Zard', 'QD Exhaust', 'HP Corse',
    'Spark', 'Fresco', 'Marving', 'RoadsItalia', 'Sito', 'IXIL', 'Tecnigas',
    'Yasuni', 'Bidalot', 'Malossi', 'Polini', 'Stage6', 'Jekill & Hyde',
    'Sebring', 'BOS Exhaust', 'Laser', 'IxRace', 'Exan', 'Wolfman',
    'Shark', 'Devil', 'Domino', 'Moto Master',
    // Japon
    'Yoshimura', 'Moriwaki', 'Over Racing', 'Beet', 'K-Factory', 'Nojima',
    'TSR', 'SP Tadao',
    // États-Unis
    'Vance & Hines', 'FMF Racing', 'Pro Circuit', 'HMF Racing', 'Bassani',
    'Cobra Exhaust', 'Toce Performance', 'M4 Exhaust', 'D&D Exhaust',
    'Two Brothers Racing', 'Rinehart Racing', 'S&S Cycle', 'VooDoo Exhaust',
    'Competition Werkes', 'Graves Motorsports', "Brock's Performance",
    'HINDLE', 'Dynomax',
    // Royaume-Uni
    'Scorpion Exhausts', 'Delkevic', 'Fuel Exhausts', 'Beowulf', 'MTC Exhaust',
    'Quill Exhausts', 'BlueFlame',
    // Divers
    'Leovince', 'Akrapovic', 'Zeta', 'Kitaco', 'Takegawa', 'Yoshimura R&D',
];

// Nettoyage : doublons + tri
$exhaustBrands = array_values(array_unique($exhaustBrands));
sort($exhaustBrands, SORT_STRING | SORT_FLAG_CASE);

// ─────────────────────────────────────────────────────────────────────────────
// 2. Création (find-or-create) de la caractéristique + valeurs
// ─────────────────────────────────────────────────────────────────────────────
function findFeatureIdByName($label, $langId) {
    foreach (Feature::getFeatures($langId) as $f) {
        if ($f['name'] === $label) {
            return (int) $f['id_feature'];
        }
    }
    return 0;
}

$idFeature = findFeatureIdByName('Marque de ligne d\'échappement', $langId);
if (!$idFeature) {
    $feature = new Feature();
    $feature->name = [$langId => 'Marque de ligne d\'échappement'];
    $feature->position = 1;
    $feature->add();
    $idFeature = (int) $feature->id;
    echo "✔ Caractéristique créée : #{$idFeature} « Marque de ligne d'échappement »\n";
} else {
    echo "✔ Caractéristique existante : #{$idFeature}\n";
}

$valueIds = [];
$existing = [];
foreach (FeatureValue::getFeatureValuesWithLang($langId, $idFeature) as $v) {
    $existing[$v['value']] = (int) $v['id_feature_value'];
}

$created = 0;
foreach ($exhaustBrands as $name) {
    if (isset($existing[$name])) {
        $valueIds[$name] = $existing[$name];
        continue;
    }
    $fv = new FeatureValue();
    $fv->id_feature = $idFeature;
    $fv->value = [$langId => $name];
    $fv->add();
    $valueIds[$name] = (int) $fv->id;
    $created++;
}
echo "   {$created} valeurs créées (" . count($exhaustBrands) . " marques au total).\n";

// ─────────────────────────────────────────────────────────────────────────────
// 3. Association aux produits de démonstration
// ─────────────────────────────────────────────────────────────────────────────
$productBrandMap = [
    'FPE-2000' => 'Akrapovič',        // Yamaha MT-07
    'FPE-2001' => 'Yoshimura',        // Honda CB650R
    'FPE-2002' => 'Arrow',            // Kawasaki Z900
    'FPE-2003' => 'Termignoni',       // Ducati Monster 821
    'FPE-2004' => 'LeoVince',         // Suzuki GSX-R 750
    'FPE-2005' => 'Akrapovič',        // BMW R 1250 GS
    'FPE-2006' => 'SC-Project',       // Triumph Street Triple
    'FPE-2007' => 'Mivv',             // KTM 390 Duke
    'FPE-2008' => 'Vance & Hines',    // Harley Sportster 883
    'FPE-2009' => 'SC-Project',       // Aprilia RS 660
    'FPE-2010' => 'FMF Racing',       // Husqvarna 701
    'FPE-2011' => 'GPR Exhaust',      // Royal Enfield Interceptor 650
];

$assigned = 0;
foreach ($productBrandMap as $ref => $brand) {
    $idProduct = (int) Db::getInstance()->getValue(
        'SELECT id_product FROM ' . _DB_PREFIX_ . 'product WHERE reference = "' . pSQL($ref) . '"'
    );
    if (!$idProduct || !isset($valueIds[$brand])) {
        continue;
    }
    $p = new Product($idProduct);
    if ($p->id) {
        $p->addFeaturesToDB($idFeature, $valueIds[$brand]);
        $assigned++;
    }
}
echo "   {$assigned} produits associés.\n";

// ─────────────────────────────────────────────────────────────────────────────
// 4. Ajout à la recherche facettée
// ─────────────────────────────────────────────────────────────────────────────
$idTemplate = (int) Db::getInstance()->getValue(
    'SELECT id_layered_filter FROM ' . _DB_PREFIX_ . 'layered_filter ORDER BY id_layered_filter ASC'
);
if ($idTemplate) {
    $filters = Tools::unSerialize(
        Db::getInstance()->getValue('SELECT filters FROM ' . _DB_PREFIX_ . 'layered_filter WHERE id_layered_filter = ' . $idTemplate)
    );
    $filters['layered_selection_feat_' . $idFeature] = ['filter_type' => 2, 'filter_show_limit' => 0];
    Db::getInstance()->execute(
        'UPDATE ' . _DB_PREFIX_ . 'layered_filter SET filters = "' . pSQL(serialize($filters)) . '" WHERE id_layered_filter = ' . $idTemplate
    );
}

$module = Module::getInstanceByName('ps_facetedsearch');
if ($module && method_exists($module, 'buildLayeredCategories')) {
    $module->buildLayeredCategories();
}

// Indexation de la nouvelle caractéristique
Db::getInstance()->execute('INSERT IGNORE INTO ' . _DB_PREFIX_ . 'layered_indexable_feature (id_feature, indexable) VALUES (' . $idFeature . ', 1)');
foreach (FeatureValue::getFeatureValuesWithLang($langId, $idFeature) as $v) {
    Db::getInstance()->execute(
        'INSERT IGNORE INTO ' . _DB_PREFIX_ . 'layered_indexable_feature_value_lang_value (id_feature_value, id_lang, url_name, meta_title) VALUES (' . (int) $v['id_feature_value'] . ', ' . $langId . ', "", "")'
    );
}

echo "   Recherche facettée mise à jour.\n";
echo "\nTerminé.\n";
