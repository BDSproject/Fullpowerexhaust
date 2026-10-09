<?php
/**
 * FULLPOWEREXHAUST — installation des traductions de modules
 * ---------------------------------------------------------------------------
 * PrestaShop 9 ne charge les traductions d'un module QUE depuis
 * `modules/<module>/translations/<locale>/` (cf. TranslatorLanguageLoader).
 * Or les catalogues livrés par les packs de langue sont restés dans
 * `translations/<locale>/Modules<Module>*.xlf`, donc IGNORÉS : toutes les
 * chaînes de modules s'affichaient en anglais sur les pages FR/ES/IT.
 *
 * Ce script recopie chaque catalogue dans le dossier attendu par PrestaShop.
 *
 * Usage : php tools/install_module_translations.php [--dry]
 */

$root = dirname(__DIR__);
$dry = in_array('--dry', $argv, true);
$locales = ['fr-FR', 'en-US', 'es-ES', 'it-IT'];

/** Reproduit DomainHelper::buildModuleBaseDomain() de PrestaShop. */
function moduleBaseDomain(string $moduleName): string
{
    if (str_starts_with($moduleName, 'ps_')) {
        $moduleName = substr($moduleName, 3);
    }

    return ucfirst(strtolower(str_replace(['_', '-'], '', $moduleName)));
}

$modules = [];
foreach (glob($root . '/modules/*', GLOB_ONLYDIR) as $dir) {
    $name = basename($dir);
    if ($name === 'index.php') {
        continue;
    }
    $modules[$name] = moduleBaseDomain($name);
}

$copied = 0;
$details = [];

foreach ($locales as $locale) {
    foreach (glob($root . "/translations/$locale/Modules*.xlf") as $file) {
        $basename = basename($file);                 // ModulesEmailsubscriptionShop.fr-FR.xlf
        if (!str_contains($basename, ".$locale.")) {
            continue;
        }
        $domain = explode('.', $basename)[0];        // ModulesEmailsubscriptionShop
        $rest = substr($domain, strlen('Modules'));

        // Module dont le domaine de base préfixe le nom du fichier
        $match = null;
        foreach ($modules as $name => $base) {
            if ($base !== '' && str_starts_with($rest, $base)) {
                // on garde la correspondance la plus longue (Emailalerts > Email)
                if ($match === null || strlen($base) > strlen($modules[$match])) {
                    $match = $name;
                }
            }
        }
        if ($match === null) {
            $details['sans module'][] = $basename;
            continue;
        }

        $targetDir = "$root/modules/$match/translations/$locale";
        $target = "$targetDir/$basename";
        if (is_file($target) && filesize($target) === filesize($file)) {
            continue;   // déjà installé
        }
        if (!$dry) {
            @mkdir($targetDir, 0755, true);
            copy($file, $target);
        }
        ++$copied;
        $details[$match][] = $basename;
    }
}

echo ($dry ? '[SIMULATION] ' : '') . "Catalogues installés : $copied\n";
ksort($details);
foreach ($details as $mod => $files) {
    printf("  %-32s %d fichier(s)\n", $mod, count($files));
}
