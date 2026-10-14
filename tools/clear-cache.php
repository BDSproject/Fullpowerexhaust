<?php
/**
 * FULLPOWEREXHAUST — purge des caches du thème (CCC CSS/JS + cache Smarty)
 * ---------------------------------------------------------------------------
 * PrestaShop combine les CSS/JS du thème dans
 *   themes/hummingbird/assets/cache/theme-*.css
 *   themes/hummingbird/assets/cache/bottom-*.js
 * Le nom de ces fichiers dépend de la LISTE des fichiers, pas de leur CONTENU :
 * un bundle peut donc rester en place alors que le code a changé. Un bundle
 * périmé est une cause classique de panier/tunnel de commande cassé.
 *
 * Ce script supprime ces fichiers pour forcer leur régénération.
 *
 * Usage :
 *   php tools/clear-cache.php
 */

$root = dirname(__DIR__);

$count = 0;
$dirs = [
    $root . '/themes/hummingbird/assets/cache',
    $root . '/var/cache',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        continue;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $file) {
        if ($file->isDir()) {
            continue;
        }
        if (@unlink($file->getPathname())) {
            ++$count;
        }
    }
}

// Vide aussi les caches intelligents de PrestaShop (Smarty, hooks…)
if (class_exists('Tools')) {
    Tools::clearSmartyCache();
    Tools::clearCache();
}

echo "Fichiers de cache supprimés : $count\n";
echo "Rechargez ensuite le back-office (Paramètres avancés → Performances) et\n";
echo "vérifiez que de nouveaux fichiers theme-*.css / bottom-*.js sont générés.\n";
