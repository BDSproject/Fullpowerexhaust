<?php
/**
 * FULLPOWEREXHAUST — état « Occasion » sur toutes les fiches produit
 * ---------------------------------------------------------------------------
 * Le badge « Neuf » affiché par défaut est le flag `new` de PrestaShop
 * (« produit ajouté récemment »), indépendant de l'état réel du produit.
 * Le thème affiche désormais l'ÉTAT du produit (`condition`), réglable dans le
 * back-office, onglet « Options » → « État ».
 *
 * Ce script passe tous les produits en « Occasion » (`used`) et active
 * l'affichage de l'état. Il est IDEMPOTENT : relancez-le après chaque import
 * eBay si le module réinitialise l'état des annonces.
 *
 * Usage :
 *   php tools/set_products_used.php            → tous les produits
 *   php tools/set_products_used.php --dry      → simulation
 *   php tools/set_products_used.php --id=144   → un seul produit
 *   php tools/set_products_used.php --new-since="2026-10-01"  → import récent
 */

require dirname(__DIR__) . '/config/config.inc.php';

$dry = in_array('--dry', $argv, true);
$id = null;
$since = null;
foreach ($argv as $a) {
    if (str_starts_with($a, '--id=')) {
        $id = (int) substr($a, 5);
    }
    if (str_starts_with($a, '--new-since=')) {
        $since = substr($a, 12);
    }
}

$where = [];
if ($id) {
    $where[] = 'p.id_product = ' . $id;
}
if ($since) {
    $where[] = "p.date_add >= '" . pSQL($since) . " 00:00:00'";
}
$whereSql = $where ? ' AND ' . implode(' AND ', $where) : '';

$countSql = 'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'product p WHERE (p.`condition` <> "used" OR p.show_condition = 0)' . $whereSql;
$total = (int) Db::getInstance()->getValue($countSql);

echo "Produits à corriger : $total\n";
if ($dry) {
    echo "[SIMULATION] aucune modification écrite\n";
    exit(0);
}

Db::getInstance()->execute(
    'UPDATE ' . _DB_PREFIX_ . 'product p SET p.`condition` = "used", p.show_condition = 1 WHERE 1' . $whereSql
);
Db::getInstance()->execute(
    'UPDATE ' . _DB_PREFIX_ . 'product_shop ps
     JOIN ' . _DB_PREFIX_ . 'product p ON p.id_product = ps.id_product
     SET ps.`condition` = "used", ps.show_condition = 1 WHERE 1' . $whereSql
);

echo "Produits passés en « Occasion » : $total\n";

/* Contrôle */
$rest = (int) Db::getInstance()->getValue(
    'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'product p WHERE (p.`condition` <> "used" OR p.show_condition = 0)' . $whereSql
);
echo $rest === 0 ? "✅ Tous les produits sont en « Occasion »\n" : "⚠️  $rest produit(s) restant(s)\n";

Tools::clearSmartyCache();
Tools::clearCache();
echo "Cache vidé.\n";
