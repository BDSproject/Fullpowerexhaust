<?php
/**
 * FULLPOWEREXHAUST — structure les catégories PrestaShop et y relie les produits.
 *
 * Crée l'arborescence :
 *   Accueil
 *   └── Échappements moto
 *       ├── Lignes complètes
 *       ├── Silencieux
 *       ├── Catalyseurs
 *       ├── Collecteurs
 *       ├── Décata
 *       ├── Sorties d'échappement
 *       └── Filtres à air
 *
 * puis assigne chaque produit à sa catégorie (id_category_default).
 *
 * Usage : php -d memory_limit=768M tools/setup_categories.php
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
// 1. Création (find-or-create) de la catégorie racine + sous-catégories
// ─────────────────────────────────────────────────────────────────────────────
function findOrCreateCategory($name, $idParent, $langId) {
    $existing = Db::getInstance()->getValue(
        'SELECT c.id_category FROM ' . _DB_PREFIX_ . 'category c
         JOIN ' . _DB_PREFIX_ . 'category_lang cl ON cl.id_category = c.id_category
         WHERE cl.name = "' . pSQL($name) . '" AND cl.id_lang = ' . $langId . ' AND c.id_parent = ' . (int) $idParent
    );
    if ($existing) {
        return (int) $existing;
    }
    $cat = new Category();
    $cat->name = [$langId => $name];
    $cat->link_rewrite = [$langId => Tools::str2url($name)];
    $cat->id_parent = (int) $idParent;
    $cat->active = 1;
    $cat->is_root_category = 0;
    $cat->add();
    return (int) $cat->id;
}

$idHome = 2; // Accueil

$idRoot = findOrCreateCategory('Échappements moto', $idHome, $langId);
echo "✔ Catégorie racine « Échappements moto » = #{$idRoot}\n";

$types = [
    'Ligne complète'        => 'Lignes complètes',
    'Silencieux'            => 'Silencieux',
    'Catalyseur'            => 'Catalyseurs',
    'Collecteur'            => 'Collecteurs',
    'Décata'                => 'Décata',
    'Sortie d\'échappement' => 'Sorties d\'échappement',
    'Filtre à air'          => 'Filtres à air',
];

$catByType = [];
foreach ($types as $featureValue => $catName) {
    $id = findOrCreateCategory($catName, $idRoot, $langId);
    $catByType[$featureValue] = $id;
    echo "   ✔ {$catName} = #{$id}\n";
}

// ─────────────────────────────────────────────────────────────────────────────
// 2. Suppression de l'ancienne catégorie « Échappements » (voiture, vide)
// ─────────────────────────────────────────────────────────────────────────────
$oldCat = Db::getInstance()->getValue(
    'SELECT c.id_category FROM ' . _DB_PREFIX_ . 'category c
     JOIN ' . _DB_PREFIX_ . 'category_lang cl ON cl.id_category = c.id_category
     WHERE cl.name = "Échappements" AND cl.id_lang = ' . $langId . ' AND c.id_parent = ' . $idHome
);
if ($oldCat) {
    $c = new Category((int) $oldCat);
    if ($c->id && $c->id != $idRoot) {
        $c->delete();
        echo "✔ Ancienne catégorie « Échappements » (#{$oldCat}) supprimée\n";
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// 3. Association des produits à leur catégorie par type de pièce
// ─────────────────────────────────────────────────────────────────────────────
// id de la caractéristique « Type de pièce »
$idTypeFeature = 0;
foreach (Feature::getFeatures($langId) as $f) {
    if ($f['name'] === 'Type de pièce') {
        $idTypeFeature = (int) $f['id_feature'];
    }
}

$valueNameById = [];
if ($idTypeFeature) {
    foreach (FeatureValue::getFeatureValuesWithLang($langId, $idTypeFeature) as $v) {
        $valueNameById[(int) $v['id_feature_value']] = $v['value'];
    }
}

$assigned = 0;
$products = Db::getInstance()->executeS('SELECT id_product FROM ' . _DB_PREFIX_ . 'product');
foreach ($products as $p) {
    $idProduct = (int) $p['id_product'];

    // type de pièce du produit
    $idValue = (int) Db::getInstance()->getValue(
        'SELECT id_feature_value FROM ' . _DB_PREFIX_ . 'feature_product
         WHERE id_product = ' . $idProduct . ' AND id_feature = ' . $idTypeFeature
    );
    $type = $idValue ? ($valueNameById[$idValue] ?? '') : '';
    if (!$type || !isset($catByType[$type])) {
        continue;
    }

    $idCat = $catByType[$type];
    $product = new Product($idProduct);
    if (!$product->id) {
        continue;
    }

    $product->addToCategories([$idCat]);
    $product->id_category_default = $idCat;
    $product->update();
    $assigned++;
}

echo "\n{$assigned} produits reliés à leur catégorie.\n";
echo "Terminé.\n";
