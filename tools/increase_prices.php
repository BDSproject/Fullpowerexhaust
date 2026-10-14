<?php
/**
 * FULLPOWEREXHAUST — augmentation des prix de vente (marge intégrée)
 * ---------------------------------------------------------------------------
 * Rehausse les prix de vente de tous les produits d'un pourcentage donné,
 * pour intégrer les frais de paiement (commission Stripe) directement dans le
 * prix — option légale, contrairement à une « surcharge carte ».
 *
 * ⚠️ Le coefficient est MULTIPLICATIF : relancer l'outil applique de nouveau
 *    la hausse (effet composé). Notez le pourcentage déjà appliqué.
 * ⚠️ Si votre module d'import eBay écrase les prix à chaque import, relancez
 *    cet outil après chaque import (ou réglez une marge dans le module d'import
 *    s'il le permet).
 *
 * Usage :
 *   php tools/increase_prices.php --percent=2.5          → +2,5 % sur tout
 *   php tools/increase_prices.php --percent=3 --dry      → simulation
 *   php tools/increase_prices.php --percent=2.5 --id=19  → un seul produit
 */

$root = dirname(__DIR__);
require_once $root . '/config/config.inc.php';

$percent = null;
$dry = false;
$idProduct = null;
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--percent=')) {
        $percent = (float) substr($arg, 10);
    } elseif ($arg === '--dry') {
        $dry = true;
    } elseif (str_starts_with($arg, '--id=')) {
        $idProduct = (int) substr($arg, 5);
    }
}

if ($percent === null || $percent <= 0 || $percent > 100) {
    fwrite(STDERR, "Usage : php tools/increase_prices.php --percent=2.5 [--dry] [--id=N]\n");

    exit(1);
}

$factor = 1 + ($percent / 100);
$db = Db::getInstance();
$where = $idProduct ? ' WHERE id_product = ' . $idProduct : '';

/* 1. Lecture du total actuel (pour le rapport) */
$rows = $db->executeS(
    'SELECT id_product, price FROM ' . _DB_PREFIX_ . 'product' . $where
) ?: [];

$before = 0.0;
$after = 0.0;
$count = count($rows);

foreach ($rows as $row) {
    $old = (float) $row['price'];
    $new = round($old * $factor, 2);
    $before += $old;
    $after += $new;
}

echo 'Produits concernés : ' . $count . "\n";
echo sprintf("Prix moyen actuel : %.2f € HT\n", $count ? $before / $count : 0);
echo sprintf("Hausse appliquée  : +%.2f %%\n", $percent);
echo sprintf("Total avant : %.2f €  →  Total après : %.2f €\n", $before, $after);
echo "\n";

if ($dry) {
    echo "[SIMULATION] aucune modification écrite.\n";

    exit(0);
}

/* 2. Application sur la table produit (+ déclinaisons éventuelles) */
$updated = 0;

$result = $db->execute(
    'UPDATE ' . _DB_PREFIX_ . 'product SET price = ROUND(price * ' . (float) $factor . ', 2)' . $where
);
if ($result !== false) {
    $updated += $db->Affected_Rows();
}

$result = $db->execute(
    'UPDATE ' . _DB_PREFIX_ . 'product_shop SET price = ROUND(price * ' . (float) $factor . ', 2)' . $where
);
if ($result !== false) {
    $updated += $db->Affected_Rows();
}

// Déclinaisons (si le catalogue en utilise)
$hasAttributes = (int) $db->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'product_attribute');
if ($hasAttributes > 0) {
    $attrWhere = $idProduct ? ' WHERE id_product = ' . $idProduct : '';
    $db->execute('UPDATE ' . _DB_PREFIX_ . 'product_attribute SET price = ROUND(price * ' . (float) $factor . ', 2)' . $attrWhere);
    $db->execute('UPDATE ' . _DB_PREFIX_ . 'product_attribute_shop SET price = ROUND(price * ' . (float) $factor . ', 2)' . $attrWhere);
}

echo "Lignes mises à jour : $updated\n";
echo "Prix de vente augmentés de +$percent % (arrondis à 0,01 €).\n";
echo "⚠️  Relancez cet outil après chaque import eBay si le module écrase les prix.\n";
