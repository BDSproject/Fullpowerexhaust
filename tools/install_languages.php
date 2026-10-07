<?php
/**
 * FULLPOWEREXHAUST — installe/active les langues (anglais, espagnol, italien).
 * Nécessite le boot du kernel Symfony (conteneur requis par Language::installLanguagePack).
 *
 * Usage : php -d memory_limit=768M tools/install_languages.php
 */

if (PHP_SAPI !== 'cli') {
    die('CLI only');
}

require_once __DIR__ . '/../config/config.inc.php';

// Boot du kernel Symfony (front) pour rendre le conteneur disponible
require_once _PS_ROOT_DIR_ . '/vendor/autoload.php';
require_once _PS_ROOT_DIR_ . '/app/AppKernel.php';
require_once _PS_ROOT_DIR_ . '/app/FrontKernel.php';

global $kernel;
$kernel = new FrontKernel(_PS_ENV_, _PS_MODE_DEV_);
$kernel->boot();

foreach (['en', 'es', 'it'] as $iso) {
    $id = (int) Language::getIdByIso($iso);
    if ($id) {
        Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'lang SET active = 1 WHERE id_lang = ' . $id);
        if ($iso !== 'en') {
            // Réinstalle (met à jour) le pack pour compléter les tables *_lang
            Language::downloadAndInstallLanguagePack($iso);
        }
        echo "✔ {$iso} activé (id {$id})\n";
    } else {
        $res = Language::downloadAndInstallLanguagePack($iso);
        echo ($res === true ? "✔ {$iso} installé" : "✖ {$iso} : " . json_encode($res)) . "\n";
    }
}

echo "Terminé.\n";
