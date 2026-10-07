<?php
/**
 * FULLPOWEREXHAUST — passe le catalogue en mode « marques moto ».
 *
 * Ce script :
 *   1. supprime les anciennes marques auto (BMW, Audi…) ;
 *   2. crée une liste complète de marques de moto dans le monde ;
 *   3. régénère le template `catalog/_partials/brand-filter.tpl` (liste déroulante) ;
 *   4. retire du bandeau gauche les modules catégories / filtres / fournisseurs / marques.
 *
 * Usage : php -d memory_limit=768M tools/setup_moto_catalog.php
 */

if (PHP_SAPI !== 'cli') {
    die('CLI only');
}

require_once __DIR__ . '/../config/config.inc.php';

// ─────────────────────────────────────────────────────────────────────────────
// 1. Liste complète des marques de moto (constructeurs mondiaux)
// ─────────────────────────────────────────────────────────────────────────────
$brands = [
    // Japon
    'Honda', 'Yamaha', 'Suzuki', 'Kawasaki',
    // Europe — Italie
    'Ducati', 'Aprilia', 'Moto Guzzi', 'Piaggio', 'Vespa', 'MV Agusta',
    'Benelli', 'Moto Morini', 'Bimota', 'Beta', 'Cagiva', 'Gilera',
    'Laverda', 'FB Mondial', 'Italjet', 'Lambretta', 'Malaguti', 'Fantic',
    'SWM', 'Garelli', 'Morbidelli', 'Paton', 'Magni', 'Vyrus',
    // Europe — Espagne
    'Gas Gas', 'Rieju', 'Montesa', 'Ossa', 'Bultaco', 'Derbi',
    // Europe — Autriche / Allemagne
    'KTM', 'Husqvarna', 'BMW Motorrad', 'Sachs', 'Zündapp', 'Kreidler',
    'Maico', 'Hercules', 'Horex', 'NSU',
    // Europe — France
    'Sherco', 'Peugeot Motocycles', 'Mash', 'Orcal', 'Voxan',
    // Royaume-Uni
    'Triumph', 'Norton', 'BSA', 'CCM', 'AJS', 'Matchless', 'Ariel',
    'Vincent', 'Velocette', 'Brough Superior', 'Hesketh',
    // États-Unis
    'Harley-Davidson', 'Indian', 'Buell', 'Zero Motorcycles', 'LiveWire',
    'Lightning Motorcycles', 'Janus', 'Arch Motorcycle', 'Boss Hoss',
    // Inde
    'Bajaj', 'TVS', 'Hero MotoCorp', 'Royal Enfield', 'Jawa', 'Yezdi',
    'Ola Electric', 'Ather', 'Revolt', 'Ultraviolette',
    // Chine
    'CFMoto', 'Zontes', 'QJMotor', 'Voge', 'Kove', 'Loncin', 'Lifan',
    'Zongshen', 'Haojue', 'Keeway', 'GPX', 'Shineray', 'Segway Powersports',
    // Taïwan
    'Kymco', 'SYM', 'PGO', 'Aeon', 'Hartford',
    // Corée
    'Hyosung', 'KR Motors',
    // Russie / Europe de l'Est
    'Ural', 'IZH', 'Minsk', 'Dnepr',
    // Électriques & scooters urbains
    'Energica', 'Niu', 'Super Soco', 'Horwin', 'Silence', 'Askoll',
    // Divers
    'AJP', 'TM Racing', 'Husaberg', 'Laverda', 'Kreidler',
];

// Nettoyage des doublons éventuels + tri alphabétique
$brands = array_values(array_unique($brands));
sort($brands, SORT_STRING | SORT_FLAG_CASE);

// ─────────────────────────────────────────────────────────────────────────────
// 2. Suppression des anciennes marques + création des marques moto
// ─────────────────────────────────────────────────────────────────────────────
Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'product SET id_manufacturer = 0');
Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'manufacturer_shop');
Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'manufacturer_lang');
Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'manufacturer');

echo "Anciennes marques supprimées.\n";

foreach ($brands as $name) {
    $manufacturer = new Manufacturer();
    $manufacturer->name = $name;
    $manufacturer->link_rewrite = Tools::str2url($name);
    $manufacturer->active = 1;
    if ($manufacturer->add()) {
        echo "  ✔ {$name}\n";
    } else {
        echo "  ✖ {$name}\n";
    }
}

echo count($brands) . " marques moto créées.\n";

// ─────────────────────────────────────────────────────────────────────────────
// 3. Génération du template de la liste déroulante des marques
// ─────────────────────────────────────────────────────────────────────────────
$idFr = (int) Language::getIdByIso('fr');
$manufacturers = Manufacturer::getManufacturers(false, $idFr, true, false, false, false, false, false);

$options = '';
foreach ($manufacturers as $m) {
    $url = Context::getContext()->link->getManufacturerLink($m['id_manufacturer']);
    $name = htmlspecialchars($m['name'], ENT_QUOTES, 'UTF-8');
    $options .= '        <option value="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . $name . "</option>\n";
}

$tpl = <<<TPL
{**
 * FULLPOWEREXHAUST — filtre par marque (liste déroulante).
 * Généré par tools/setup_moto_catalog.php — ne pas éditer à la main.
 *}
<div class="brand-filter">
  <label class="brand-filter__label" for="brand-filter-select">{l s='Marque' d='Shop.Theme.Catalog'}</label>
  <select
    id="brand-filter-select"
    class="brand-filter__select"
    onchange="if (this.value) window.location.href = this.value;"
    aria-label="{l s='Filtrer par marque' d='Shop.Theme.Catalog'}"
  >
    <option value="">{l s='Toutes les marques' d='Shop.Theme.Catalog'}</option>
{$options}  </select>
</div>
TPL;

$dest = __DIR__ . '/../themes/hummingbird/templates/catalog/_partials/brand-filter.tpl';
file_put_contents($dest, $tpl);
echo "Template généré : themes/hummingbird/templates/catalog/_partials/brand-filter.tpl\n";

// ─────────────────────────────────────────────────────────────────────────────
// 4. Nettoyage des hooks du bandeau gauche
// ─────────────────────────────────────────────────────────────────────────────
$toUnhook = [
    'ps_categorytree' => ['displayLeftColumn'],
    'ps_facetedsearch' => ['displayLeftColumn'],
    'ps_supplierlist' => ['displayLeftColumn'],
    'ps_brandlist' => ['displayLeftColumn', 'displayRightColumn'],
];

foreach ($toUnhook as $moduleName => $hooks) {
    $module = Module::getInstanceByName($moduleName);
    if (!$module) {
        echo "Module {$moduleName} introuvable.\n";
        continue;
    }
    foreach ($hooks as $hook) {
        if ($module->unregisterHook($hook)) {
            echo "  ↪ {$moduleName} retiré de {$hook}\n";
        } else {
            echo "  ! {$moduleName} : échec retrait de {$hook}\n";
        }
    }
}

echo "Terminé.\n";
