<?php
/**
 * FULLPOWEREXHAUST — audit des adresses e-mail du site
 * ---------------------------------------------------------------------------
 * Recense toutes les adresses présentes dans la base et dans les fichiers du
 * thème, et signale celles qui diffèrent de l'adresse officielle.
 *
 * Usage :
 *   php tools/audit-emails.php                      → adresse officielle par défaut
 *   php tools/audit-emails.php --email=autre@x.com  → comparer à une autre adresse
 */

require dirname(__DIR__) . '/config/config.inc.php';

$expected = 'contact@power-exhaust.com';
foreach ($argv as $a) {
    if (str_starts_with($a, '--email=')) {
        $expected = substr($a, 8);
    }
}

/* Adresses signalées pour information (choix délibéré, pas une anomalie). */
$informative = [
    'employee.email',   // identifiant de connexion au back-office, distinct de l'adresse publique
];

/* Adresses techniques à ne pas signaler (comptes de service, licences). */
$ignore = [
    'anonymous@psgdpr.com',        // compte technique créé par le module RGPD
    'pub@prestashop.com',          // compte de démonstration PrestaShop
    'contact@prestashop.com',
    'license@prestashop.com',
    'support@prestashop.com',
    'your@email.com',              // gabarits d'e-mail
    'votre@email.com',
    'name@example.com',
];

echo "Adresse officielle attendue : $expected\n";
echo str_repeat('=', 74) . "\n\n";

$db = Db::getInstance();

/* ── 1. Base de données ─────────────────────────────────────────────────── */
$tables = [
    'contact' => ['email'],
    'employee' => ['email'],
    'customer' => ['email'],
    'customer_thread' => ['email'],
    'emailsubscription' => ['email'],
    'mailalert_customer_oos' => ['customer_email'],
    'store' => ['email'],
];

echo "── BASE DE DONNÉES\n";
$found = 0;
foreach ($tables as $table => $columns) {
    $full = _DB_PREFIX_ . $table;
    foreach ($columns as $column) {
        $rows = $db->executeS("SELECT `$column` AS v FROM `$full` WHERE `$column` <> ''");
        if (!$rows) {
            continue;
        }
        foreach ($rows as $row) {
            $mail = trim($row['v']);
            if ($mail === '' || in_array($mail, $ignore, true)) {
                continue;
            }
            $key = "$table.$column";
            $ok = strcasecmp($mail, $expected) === 0;
            if (in_array($key, $informative, true)) {
                printf("  %-6s %-28s %s\n", 'ℹ️', $key, $mail);
                continue;
            }
            printf("  %-6s %-28s %s\n", $ok ? '✓' : '⚠️', $key, $mail);
            if (!$ok) {
                ++$found;
            }
        }
    }
}

/* Réglages et contenus multilingues */
$settings = $db->executeS(
    'SELECT name, value FROM ' . _DB_PREFIX_ . 'configuration
     WHERE value LIKE "%@%" AND value NOT LIKE "%{%"'
);
foreach ($settings as $s) {
    if (preg_match_all('/[\w.+-]+@[\w.-]+\.[a-z]{2,}/i', $s['value'], $m)) {
        foreach ($m[0] as $mail) {
            if (in_array($mail, $ignore, true)) {
                continue;
            }
            $ok = strcasecmp($mail, $expected) === 0;
            printf("  %-6s %-28s %s\n", $ok ? '✓' : '⚠️', 'configuration.' . $s['name'], $mail);
            if (!$ok) {
                ++$found;
            }
        }
    }
}

/* Contenus (CMS, catégories, produits, caractéristiques) */
$langTables = ['cms_lang' => 'content', 'category_lang' => 'description', 'product_lang' => 'description'];
foreach ($langTables as $table => $column) {
    $rows = $db->executeS('SELECT id_lang, `' . $column . '` AS v FROM ' . _DB_PREFIX_ . $table . ' WHERE `' . $column . '` LIKE "%@%"');
    foreach ($rows as $row) {
        if (preg_match_all('/[\w.+-]+@[\w.-]+\.[a-z]{2,}/i', (string) $row['v'], $m)) {
            foreach (array_unique($m[0]) as $mail) {
                if (in_array($mail, $ignore, true)) {
                    continue;
                }
                $ok = strcasecmp($mail, $expected) === 0;
                printf("  %-6s %-28s %s (langue %d)\n", $ok ? '✓' : '⚠️', "$table.$column", $mail, $row['id_lang']);
                if (!$ok) {
                    ++$found;
                }
            }
        }
    }
}

/* ── 2. Fichiers du thème et des surcharges ─────────────────────────────── */
echo "\n── FICHIERS (thème + surcharges de modules)\n";
$dirs = [
    _PS_ROOT_DIR_ . '/themes/hummingbird/templates',
    _PS_ROOT_DIR_ . '/themes/hummingbird/modules',
];
$scanned = 0;
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        continue;
    }
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if (!$file->isFile() || !preg_match('/\.(tpl|php|js|css)$/', $file->getFilename())) {
            continue;
        }
        ++$scanned;
        $content = file_get_contents($file->getPathname());
        if (!preg_match_all('/[\w.+-]+@[\w.-]+\.[a-z]{2,}/i', $content, $m)) {
            continue;
        }
        foreach (array_unique($m[0]) as $mail) {
            if (in_array($mail, $ignore, true)) {
                continue;
            }
            $ok = strcasecmp($mail, $expected) === 0;
            $rel = str_replace(_PS_ROOT_DIR_ . '/', '', $file->getPathname());
            printf("  %-6s %-28s %s\n", $ok ? '✓' : '⚠️', basename($rel), $mail);
            if (!$ok) {
                ++$found;
            }
        }
    }
}
echo "  ($scanned fichiers analysés)\n";

/* ── Conclusion ─────────────────────────────────────────────────────────── */
echo "\n" . str_repeat('=', 74) . "\n";
if ($found === 0) {
    echo "✅ Aucune adresse différente de « $expected » sur le site.\n";
    echo "   (ℹ️  = identifiant de connexion au back-office, volontairement distinct)\n";
} else {
    echo "⚠️  $found adresse(s) à corriger.\n";
}
