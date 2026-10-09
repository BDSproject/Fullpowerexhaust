<?php
/**
 * FULLPOWEREXHAUST — audit des traductions
 * ---------------------------------------------------------------------------
 * Compare les chaînes `{l s='…' d='Domaine'}` présentes dans les templates du
 * thème (et les surcharges de modules) avec les catalogues XLIFF de chaque
 * langue active, puis liste les traductions MANQUANTES.
 *
 * Usage :
 *   php tools/audit-translations.php            → rapport complet
 *   php tools/audit-translations.php --missing  → uniquement les manquantes
 */

$root = dirname(__DIR__);
$locales = ['fr-FR' => 2, 'en-US' => 1, 'es-ES' => 3, 'it-IT' => 4];

/* Domaines « métier » du thème : ceux que l’on maintient nous-mêmes. */
$themeDirs = [
    $root . '/themes/hummingbird/templates',
    $root . '/themes/hummingbird/modules',
];

// Templates des modules réellement rendus côté boutique (front + hooks).
// Sans eux, des chaînes comme « You may unsubscribe at any moment… »
// restaient en anglais sur les pages françaises.
foreach (glob($root . '/modules/*/views/templates', GLOB_ONLYDIR) as $modDir) {
    foreach (['front', 'hook', '_partials'] as $sub) {
        if (is_dir("$modDir/$sub")) {
            $themeDirs[] = "$modDir/$sub";
        }
    }
}

/* 1. Collecte des chaînes source ------------------------------------------- */
$strings = [];   // domaine => [ chaîne => fichier ]
$files = [];
foreach ($themeDirs as $dir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if ($f->isFile() && preg_match('/\.tpl$/', $f->getFilename())) {
            $files[] = $f->getPathname();
        }
    }
}
foreach ($files as $file) {
    $src = file_get_contents($file);
    // {l s='…' d='…'}  ·  {l s="…" d="…"}  (gère les apostrophes échappées \')
    if (preg_match_all('/\{l\s+s=([\'"])((?:\\\\.|(?!\1).)*)\1\s+d=([\'"])([^\'"]+)\3/is', $src, $m, PREG_SET_ORDER)) {
        foreach ($m as $one) {
            $s = str_replace("\\'", "'", $one[2]);
            $d = $one[4];
            if (!isset($strings[$d][$s])) {
                $strings[$d][$s] = str_replace($root . '/', '', $file);
            }
        }
    }
}

/* 2. Chargement des catalogues XLIFF --------------------------------------- */
function loadXlf(string $path): array
{
    if (!is_file($path)) {
        return [];
    }
    // Les catalogues XLIFF de PrestaShop déclarent un namespace par défaut
    // (urn:oasis:names:tc:xliff:document:1.2) : on le retire pour pouvoir
    // parcourir l’arbre simplement avec SimpleXML.
    $raw = preg_replace('/\sxmlns(:[a-zA-Z0-9]+)?="[^"]*"/', '', file_get_contents($path));
    $xml = @simplexml_load_string($raw);
    if (!$xml) {
        return [];
    }
    $out = [];
    foreach ($xml->file as $fileNode) {
        foreach ($fileNode->body->{'trans-unit'} as $unit) {
            $source = (string) $unit->source;
            $target = (string) $unit->target;
            if ($source !== '') {
                $out[$source] = $target;
            }
        }
    }

    return $out;
}

$catalogs = [];   // locale => domaine => [source => target]
foreach (array_keys($locales) as $locale) {
    foreach (array_keys($strings) as $domain) {
        // PrestaShop nomme ses fichiers sans les points du domaine, chaque
        // segment étant capitalisé :
        //   Shop.Theme.Actions        → ShopThemeActions
        //   Modules.Emailalerts.Shop  → ModulesEmailalertsShop
        $base = implode('', array_map('ucfirst', explode('.', $domain)));
        $candidates = [
            $root . "/translations/$locale/$base.$locale.xlf",
            $root . "/themes/hummingbird/translations/$locale/$base.$locale.xlf",
            $root . "/app/Resources/translations/$locale/$base.$locale.xlf",
        ];
        $merged = [];
        foreach ($candidates as $c) {
            $merged += loadXlf($c);
        }
        $catalogs[$locale][$domain] = $merged;
    }
}

/* 3. Rapport --------------------------------------------------------------- */
$onlyMissing = in_array('--missing', $argv, true);
$totalStrings = 0;
$report = [];

foreach ($strings as $domain => $list) {
    $totalStrings += count($list);
    foreach ($list as $source => $file) {
        foreach (array_keys($locales) as $locale) {
            $target = $catalogs[$locale][$domain][$source] ?? '';
            if ($target === '') {
                $report[$locale][$domain][] = ['source' => $source, 'file' => $file];
            }
        }
    }
}

echo "=== AUDIT DES TRADUCTIONS =========================================\n";
echo 'Templates analysés   : ' . count($files) . "\n";
echo 'Domaines             : ' . count($strings) . "\n";
echo 'Chaînes source       : ' . $totalStrings . "\n\n";

foreach (array_keys($locales) as $locale) {
    $missing = $report[$locale] ?? [];
    $count = 0;
    foreach ($missing as $rows) {
        $count += count($rows);
    }
    printf("── %s : %d chaîne(s) sans traduction\n", $locale, $count);
    if ($onlyMissing && !$count) {
        continue;
    }
    foreach ($missing as $domain => $rows) {
        printf("   [%s] %d\n", $domain, count($rows));
        foreach (array_slice($rows, 0, 40) as $r) {
            printf("      · %s\n        (%s)\n", $r['source'], $r['file']);
        }
        if (count($rows) > 40) {
            printf("      … +%d\n", count($rows) - 40);
        }
    }
    echo "\n";
}

/* 4. Fichier de sortie exploitable ---------------------------------------- */
$json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
file_put_contents($root . '/var/audit-translations.json', $json);
echo "Détail JSON : var/audit-translations.json\n";
