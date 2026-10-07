<?php
/**
 * FULLPOWEREXHAUST — optimisation SEO internationale.
 * Définit les balises title / meta description / meta keywords de la boutique
 * dans les 4 langues, avec des mots-clés ciblés « échappement d'occasion ».
 *
 * Usage : php -d memory_limit=768M tools/optimize_seo.php
 */

if (PHP_SAPI !== 'cli') {
    die('CLI only');
}

require_once __DIR__ . '/../config/config.inc.php';

$langs = [
    'fr' => (int) Language::getIdByIso('fr'),
    'en' => (int) Language::getIdByIso('en'),
    'es' => (int) Language::getIdByIso('es'),
    'it' => (int) Language::getIdByIso('it'),
];

// ── Balises méta (title / description / keywords) par langue ──────────────────
$metaTitle = [
    'fr' => "Lignes d'échappement d'occasion — Moto & Auto | FullPowerExhaust",
    'en' => 'Used Exhaust Lines & Parts — Motorcycle & Car | FullPowerExhaust',
    'es' => 'Líneas de escape de ocasión — Moto y coche | FullPowerExhaust',
    'it' => 'Scarichi usati per moto e auto | FullPowerExhaust',
];

$metaDescription = [
    'fr' => "Achetez des lignes d'échappement d'occasion contrôlées et garanties. Silencieux, catalyseurs, collecteurs et lignes complètes pour moto et auto, toutes marques. Expédition rapide.",
    'en' => 'Buy inspected, guaranteed used exhaust lines. Silencers, catalytic converters, manifolds and full exhaust systems for motorcycle and car, all brands. Fast shipping.',
    'es' => 'Compra líneas de escape de ocasión revisadas y garantizadas. Silenciosos, catalizadores, colectores y líneas completas para moto y coche, todas las marcas. Envío rápido.',
    'it' => 'Acquista linee di scarico usate controllate e garantite. Silenziatori, catalizzatori, collettori e impianti completi per moto e auto, tutti i marchi. Spedizione rapida.',
];

$metaKeywords = [
    'fr' => "ligne d'échappement occasion, pot d'échappement occasion, échappement moto occasion, silencieux occasion, catalyseur occasion, collecteur échappement, ligne complète occasion, échappement performance pas cher",
    'en' => 'used exhaust line, used motorcycle exhaust, second hand exhaust, used silencer, used catalytic converter, exhaust manifold, full exhaust system, cheap performance exhaust',
    'es' => 'escape de ocasión, escape de moto de segunda mano, línea de escape usada, silencioso usado, catalizador usado, colector de escape, escape completo, escape barato',
    'it' => 'scarico usato, scarico moto usato, marmitta usata, impianto di scarico usato, silenziatore usato, catalizzatore usato, collettore di scarico, scarico economico',
];

// Écriture des valeurs multilingues
function setMultilang($key, $values, $langs) {
    $data = [];
    foreach ($langs as $iso => $idLang) {
        $data[$idLang] = $values[$iso];
    }
    Configuration::updateValue($key, $data);
    return count($data);
}

$n = setMultilang('PS_META_TITLE', $metaTitle, $langs);
echo "✔ PS_META_TITLE : {$n} langues\n";

$n = setMultilang('PS_META_DESCRIPTION', $metaDescription, $langs);
echo "✔ PS_META_DESCRIPTION : {$n} langues\n";

$n = setMultilang('PS_META_KEYWORDS', $metaKeywords, $langs);
echo "✔ PS_META_KEYWORDS : {$n} langues\n";

// ── Meta de la page d'accueil (meta_lang, page = index) ─────────────────────
$idMeta = (int) Db::getInstance()->getValue('SELECT id_meta FROM ' . _DB_PREFIX_ . 'meta WHERE page = "index"');
if ($idMeta) {
    $cols = Db::getInstance()->executeS('SHOW COLUMNS FROM ' . _DB_PREFIX_ . 'meta_lang');
    $colNames = [];
    foreach ($cols as $c) {
        $colNames[] = $c['Field'];
    }
    foreach ($langs as $iso => $idLang) {
        $title = $metaTitle[$iso];
        $desc = $metaDescription[$iso];
        $exists = (int) Db::getInstance()->getValue('SELECT 1 FROM ' . _DB_PREFIX_ . 'meta_lang WHERE id_meta = ' . $idMeta . ' AND id_lang = ' . $idLang);
        if ($exists) {
            Db::getInstance()->update('meta_lang', ['title' => pSQL($title), 'description' => pSQL($desc)], 'id_meta = ' . $idMeta . ' AND id_lang = ' . $idLang);
        } else {
            Db::getInstance()->insert('meta_lang', ['id_meta' => $idMeta, 'id_lang' => $idLang, 'title' => pSQL($title), 'description' => pSQL($desc)]);
        }
    }
    echo "✔ Meta page d'accueil (index) : titre + description en " . count($langs) . " langues\n";
}

// ── Activer les URLs simplifiées + redirection canonical ─────────────────────
Configuration::updateValue('PS_REWRITING_SETTINGS', 1);
Configuration::updateValue('PS_CANONICAL_REDIRECT', 1);
echo "✔ URLs simplifiées + redirection canonique activées\n";

echo "\nTerminé.\n";
