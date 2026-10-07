<?php
/**
 * FULLPOWEREXHAUST — traduit le contenu custom (templates + données) en
 * anglais, espagnol et italien.
 *
 * 1. Génère les fichiers XLF du domaine Shop.Theme.FullPowerExhaust
 *    (translations/{locale}/ShopThemeFullPowerExhaust.{locale}.xlf).
 * 2. Traduit les produits, catégories et caractéristiques en en/es/it.
 *
 * Usage : php -d memory_limit=768M tools/translate_site.php
 */

if (PHP_SAPI !== 'cli') {
    die('CLI only');
}

require_once __DIR__ . '/../config/config.inc.php';

// ─────────────────────────────────────────────────────────────────────────────
// 1. Table de traduction (source = français)
//    clé = chaîne source française  →  [en, es, it]
// ─────────────────────────────────────────────────────────────────────────────
$map = [
    // Header
    'Accueil' => ['Home', 'Inicio', 'Home'],
    'Catalogue' => ['Catalog', 'Catálogo', 'Catalogo'],
    'Contact' => ['Contact', 'Contacto', 'Contatto'],
    'Rechercher' => ['Search', 'Buscar', 'Cerca'],
    'Menu' => ['Menu', 'Menú', 'Menu'],
    'Fermer' => ['Close', 'Cerrar', 'Chiudi'],
    'Navigation principale' => ['Main navigation', 'Navegación principal', 'Navigazione principale'],

    // Footer
    'L\'échappement performance occasion.' => ['Used performance exhaust.', 'Escape de rendimiento de ocasión.', 'Scarichi performance usati.'],
    'CGV' => ['Terms of sale', 'Condiciones de venta', 'Condizioni di vendita'],
    'CGU' => ['Terms of use', 'Condiciones de uso', 'Condizioni d\'uso'],
    'Mentions légales' => ['Legal notice', 'Aviso legal', 'Note legali'],
    'Politique de confidentialité' => ['Privacy policy', 'Política de privacidad', 'Informativa sulla privacy'],

    // Accueil — hero
    'Lignes d\'occasion · Toutes marques' => ['Used exhaust lines · All brands', 'Líneas de ocasión · Todas las marcas', 'Linee usate · Tutti i marchi'],
    'Sonorité' => ['Sound', 'Sonido', 'Suono'],
    'Performance' => ['Performance', 'Rendimiento', 'Prestazioni'],
    'Occasion' => ['Used', 'Ocasión', 'Usato'],
    'Voir le catalogue' => ['View catalog', 'Ver catálogo', 'Vedi catalogo'],
    'Boutique eBay' => ['eBay Store', 'Tienda eBay', 'Negozio eBay'],
    'Mon compte' => ['My account', 'Mi cuenta', 'Il mio account'],
    'Contrôlée avant mise en vente' => ['Checked before sale', 'Revisado antes de la venta', 'Controllato prima della vendita'],
    'Sonorité unique' => ['Unique sound', 'Sonido único', 'Suono unico'],
    'Chaque ligne testée à l\'oreille' => ['Every exhaust line sound-tested', 'Cada línea probada de oído', 'Ogni linea testata a orecchio'],
    'Qualité garantie' => ['Guaranteed quality', 'Calidad garantizada', 'Qualità garantita'],
    'Pièces inspectées et certifiées' => ['Inspected and certified parts', 'Piezas inspeccionadas y certificadas', 'Pezzi ispezionati e certificati'],
    'Expertise' => ['Expertise', 'Experiencia', 'Esperienza'],
    '5 ans dans l\'aftermarket' => ['5 years in the aftermarket', '5 años en el aftermarket', '5 anni nell\'aftermarket'],
    'Vous ne trouvez pas votre marque ?' => ['Can\'t find your brand?', '¿No encuentras tu marca?', 'Non trovi il tuo marchio?'],
    'Parlez-nous de vos projets' => ['Tell us about your projects', 'Háblanos de tus proyectos', 'Parlaci dei tuoi progetti'],
    'Contactez-nous, nous sourçons pour toutes les marques européennes et asiatiques.' => ['Contact us, we source parts for all European and Asian brands.', 'Contáctanos, buscamos piezas de todas las marcas europeas y asiáticas.', 'Contattaci, reperiamo pezzi per tutti i marchi europei e asiatici.'],
    'Nous contacter' => ['Contact us', 'Contáctanos', 'Contattaci'],

    // Filtres catalogue
    'Marque de votre moto' => ['Your motorcycle brand', 'Marca de tu moto', 'Marca della tua moto'],
    'Marque de ligne d\'échappement' => ['Exhaust line brand', 'Marca del escape', 'Marca dello scarico'],
    'Type de pièce' => ['Part type', 'Tipo de pieza', 'Tipo di parte'],
    'État' => ['Condition', 'Estado', 'Condizione'],
    'Prix' => ['Price', 'Precio', 'Prezzo'],
    'Filtrer' => ['Filter', 'Filtrar', 'Filtra'],
    'Réinitialiser' => ['Reset', 'Restablecer', 'Reimposta'],
    'Toutes les marques' => ['All brands', 'Todas las marcas', 'Tutti i marchi'],
    'Tous les types' => ['All types', 'Todos los tipos', 'Tutti i tipi'],
    'Tous les états' => ['All conditions', 'Todos los estados', 'Tutte le condizioni'],
    'Filtrer par marque' => ['Filter by brand', 'Filtrar por marca', 'Filtra per marca'],

    // Catalogue (libellés génériques)
    'Marque' => ['Brand', 'Marca', 'Marca'],
];

// ─────────────────────────────────────────────────────────────────────────────
// 2. Génération des fichiers XLF (en, es, it)
// ─────────────────────────────────────────────────────────────────────────────
$locales = [
    'en-US' => ['en', 0],
    'es-ES' => ['es', 1],
    'it-IT' => ['it', 2],
];

foreach ($locales as $locale => [$lang, $idx]) {
    $body = '';
    foreach ($map as $src => $translations) {
        $target = isset($translations[$idx]) ? $translations[$idx] : $src;
        $id = md5($src);
        $body .= '      <trans-unit id="' . $id . '" approved="yes">' . "\n";
        $body .= '        <source>' . htmlspecialchars($src, ENT_XML1, 'UTF-8') . '</source>' . "\n";
        $body .= '        <target state="final">' . htmlspecialchars($target, ENT_XML1, 'UTF-8') . '</target>' . "\n";
        $body .= '      </trans-unit>' . "\n";
    }

    $xlf = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<xliff xmlns="urn:oasis:names:tc:xliff:document:1.2" version="1.2">' . "\n"
        . '  <file original="themes/hummingbird/templates" source-language="fr" target-language="' . $lang . '" datatype="plaintext">' . "\n"
        . '    <body>' . "\n"
        . $body
        . '    </body>' . "\n"
        . '  </file>' . "\n"
        . '</xliff>' . "\n";

    $dir = _PS_ROOT_DIR_ . '/translations/' . $locale;
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($dir . '/ShopThemeFullPowerExhaust.' . $locale . '.xlf', $xlf);
    echo "✔ XLF généré : translations/{$locale}/ShopThemeFullPowerExhaust.{$locale}.xlf\n";
}

// ─────────────────────────────────────────────────────────────────────────────
// 3. Traduction des données (produits, catégories, caractéristiques)
// ─────────────────────────────────────────────────────────────────────────────
$langs = [
    'en' => (int) Language::getIdByIso('en'),
    'es' => (int) Language::getIdByIso('es'),
    'it' => (int) Language::getIdByIso('it'),
];
$idFr = (int) Language::getIdByIso('fr');

$typeMap = [
    'Ligne complète' => ['Full exhaust line', 'Línea completa', 'Linea completa'],
    'Silencieux' => ['Silencer', 'Silencioso', 'Silenziatore'],
    'Catalyseur' => ['Catalytic converter', 'Catalizador', 'Catalizzatore'],
    'Collecteur' => ['Exhaust manifold', 'Colector', 'Collettore'],
    'Décata' => ['Decat pipe', 'Tubo sin catalizador', 'Tubo decat'],
    'Sortie d\'échappement' => ['Exhaust tip', 'Salida de escape', 'Terminale di scarico'],
    'Filtre à air' => ['Air filter', 'Filtro de aire', 'Filtro aria'],
];

$stateMap = [
    'Comme neuf' => ['Like new', 'Como nuevo', 'Come nuovo'],
    'Très bon état' => ['Very good condition', 'Muy buen estado', 'Ottime condizioni'],
    'Bon état' => ['Good condition', 'Buen estado', 'Buone condizioni'],
];

$exhaustBrandsAreProperNouns = true; // les marques restent inchangées

// Traduction des catégories
$categoryMap = [
    'Échappements moto' => ['Motorcycle exhausts', 'Escapes de moto', 'Scarichi per moto'],
    'Lignes complètes' => ['Full exhaust lines', 'Líneas completas', 'Linee complete'],
    'Silencieux' => ['Silencers', 'Silenciosos', 'Silenziatori'],
    'Catalyseurs' => ['Catalytic converters', 'Catalizadores', 'Catalizzatori'],
    'Collecteurs' => ['Exhaust manifolds', 'Colectores', 'Collettori'],
    'Décata' => ['Decat pipes', 'Tubos sin catalizador', 'Tubi decat'],
    'Sorties d\'échappement' => ['Exhaust tips', 'Salidas de escape', 'Terminali di scarico'],
    'Filtres à air' => ['Air filters', 'Filtros de aire', 'Filtri aria'],
    'Accueil' => ['Home', 'Inicio', 'Home'],
];

function translateLangTable($table, $langCols, $idFr, $langs, $map, $nameField = 'name') {
    $done = 0;
    foreach ($langs as $iso => $idLang) {
        $rows = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . $table . ' WHERE id_lang = ' . $idFr);
        foreach ($rows as $row) {
            if (!isset($map[$row[$nameField]])) {
                continue;
            }
            $translated = $map[$row[$nameField]][$iso === 'en' ? 0 : ($iso === 'es' ? 1 : 2)];
            $cols = [];
            foreach ($langCols as $col) {
                $cols[$col] = $row[$col];
            }
            $cols[$nameField] = $translated;
            $cols['id_lang'] = $idLang;
            $idCol = $table === 'category_lang' ? 'id_category' : ($table === 'feature_lang' ? 'id_feature' : 'id_feature_value');
            $idVal = $row[$idCol];
            $exists = (int) Db::getInstance()->getValue('SELECT 1 FROM ' . _DB_PREFIX_ . $table . ' WHERE id_lang = ' . $idLang . ' AND ' . $idCol . ' = ' . (int) $idVal);
            if ($exists) {
                Db::getInstance()->update($table, [$nameField => pSQL($translated)], 'id_lang = ' . $idLang . ' AND ' . $idCol . ' = ' . (int) $idVal);
            } else {
                Db::getInstance()->insert($table, $cols);
            }
            $done++;
        }
    }
    return $done;
}

$n = translateLangTable('category_lang', ['id_category', 'name', 'description', 'link_rewrite', 'meta_title', 'meta_description', 'meta_keywords'], $idFr, $langs, $categoryMap, 'name');
echo "✔ Catégories traduites : {$n} entrées\n";

$n = translateLangTable('feature_lang', ['id_feature', 'name'], $idFr, $langs, array_merge($typeMap, $stateMap), 'name');
echo "✔ Caractéristiques traduites : {$n} entrées\n";

// Traduction des valeurs de caractéristiques (feature_value_lang)
$valueMap = array_merge($typeMap, $stateMap);
$n = translateLangTable('feature_value_lang', ['id_feature_value', 'value'], $idFr, $langs, $valueMap, 'value');
echo "✔ Valeurs de caractéristiques traduites : {$n} entrées\n";

// Traduction des produits (nom + description courte)
$productDone = 0;
foreach ($langs as $iso => $idLang) {
    $idx = $iso === 'en' ? 0 : ($iso === 'es' ? 1 : 2);
    $rows = Db::getInstance()->executeS('SELECT id_product, name, description_short, description, link_rewrite FROM ' . _DB_PREFIX_ . 'product_lang WHERE id_lang = ' . $idFr);
    foreach ($rows as $row) {
        $name = $row['name'];
        // Remplace le type de pièce dans le nom du produit
        foreach ($typeMap as $fr => $tr) {
            if (strpos($name, $fr) !== false) {
                $name = str_replace($fr, $tr[$idx], $name);
                break;
            }
        }
        $exists = (int) Db::getInstance()->getValue('SELECT 1 FROM ' . _DB_PREFIX_ . 'product_lang WHERE id_lang = ' . $idLang . ' AND id_product = ' . (int) $row['id_product']);
        if ($exists) {
            Db::getInstance()->update('product_lang', ['name' => pSQL($name)], 'id_lang = ' . $idLang . ' AND id_product = ' . (int) $row['id_product']);
        } else {
            Db::getInstance()->insert('product_lang', [
                'id_product' => $row['id_product'],
                'id_lang' => $idLang,
                'name' => $name,
                'description' => $row['description'],
                'description_short' => $row['description_short'],
                'link_rewrite' => $row['link_rewrite'],
            ]);
        }
        $productDone++;
    }
}
echo "✔ Produits traduits : {$productDone} entrées\n";

echo "\nTerminé.\n";
