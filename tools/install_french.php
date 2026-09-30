<?php
/**
 * FULLPOWEREXHAUST — finalise l'installation du français :
 *  - définit le français par défaut, désactive l'anglais
 *  - copie le contenu existant (produits, catégories…) vers le français
 *  - traduit les libellés par défaut (Home → Accueil, Root → Racine)
 * Usage : php -d memory_limit=768M tools/install_french.php
 */

if (PHP_SAPI !== 'cli') {
    die('CLI only');
}

require_once __DIR__ . '/../config/config.inc.php';

$idFr = (int) Language::getIdByIso('fr');
if (!$idFr) {
    echo "✖ Français introuvable — relancez d'abord l'installation du pack.\n";
    exit(1);
}
$idEn = (int) Language::getIdByIso('en');

// 1) Français par défaut + désactivation de l'anglais
Configuration::updateValue('PS_LANG_DEFAULT', $idFr);
Db::getInstance()->update('lang', ['active' => 0], 'id_lang = ' . $idEn);
Db::getInstance()->update('lang', ['active' => 1], 'id_lang = ' . $idFr);

// 2) Copie générique du contenu (toutes les tables *_lang) de l'anglais vers le français
$tables = Db::getInstance()->executeS('SHOW TABLES LIKE "' . str_replace('_', '\\_', _DB_PREFIX_) . '%\\_lang"');
$copied = 0;
foreach ($tables as $row) {
    $table = reset($row);
    $columns = Db::getInstance()->executeS('SHOW COLUMNS FROM `' . bqSQL($table) . '`');
    $colNames = [];
    foreach ($columns as $c) {
        $colNames[] = $c['Field'];
    }
    if (!in_array('id_lang', $colNames)) {
        continue;
    }

    $select = [];
    foreach ($colNames as $col) {
        $select[] = ($col === 'id_lang') ? (string) $idFr : '`' . bqSQL($col) . '`';
    }
    $sql = 'INSERT IGNORE INTO `' . bqSQL($table) . '` (`' . implode('`,`', $colNames) . '`)
            SELECT ' . implode(', ', $select) . ' FROM `' . bqSQL($table) . '` WHERE id_lang = ' . $idEn;
    if (Db::getInstance()->execute($sql, false)) {
        $copied++;
    }
}

// 3) Traduit les catégories par défaut
Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'category_lang SET name = "Accueil" WHERE id_lang = ' . $idFr . ' AND name = "Home"');
Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'category_lang SET name = "Racine" WHERE id_lang = ' . $idFr . ' AND name = "Root"');

// 4) Nettoie le cache de traduction
Language::resetCache();

echo "✔ Français défini par défaut (id_lang={$idFr}), {$copied} tables de contenu copiées.\n";
