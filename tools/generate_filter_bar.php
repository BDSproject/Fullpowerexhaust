<?php
/**
 * FULLPOWEREXHAUST — génère la barre de filtres du catalogue
 * (marque / prix / type de pièce / état) avec filtres combinables.
 *
 * Usage : php -d memory_limit=768M tools/generate_filter_bar.php
 */

if (PHP_SAPI !== 'cli') {
    die('CLI only');
}

require_once __DIR__ . '/../config/config.inc.php';

$langId = (int) Language::getIdByIso('fr');
if (!$langId) {
    $langId = (int) Configuration::get('PS_LANG_DEFAULT');
}

// ── Marques (manufacturers) ──────────────────────────────────────────────────
$brandOptions = '';
$brands = Manufacturer::getManufacturers(false, $langId, true, false, false, false, false, false);
foreach ($brands as $b) {
    $name = htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8');
    $brandOptions .= '        <option value="' . $name . '">' . $name . "</option>\n";
}

// ── Caractéristiques (features) ──────────────────────────────────────────────
function findFeatureId($label, $langId) {
    foreach (Feature::getFeatures($langId) as $f) {
        if ($f['name'] === $label) {
            return (int) $f['id_feature'];
        }
    }
    return 0;
}

function buildFeatureOptions($idFeature, $langId) {
    if (!$idFeature) {
        return '';
    }
    $html = '';
    foreach (FeatureValue::getFeatureValuesWithLang($langId, $idFeature) as $v) {
        $name = htmlspecialchars($v['value'], ENT_QUOTES, 'UTF-8');
        $html .= '        <option value="' . $name . '">' . $name . "</option>\n";
    }
    return $html;
}

$idType = findFeatureId('Type de pièce', $langId);
$idState = findFeatureId('État', $langId);
$idExhaustBrand = findFeatureId('Marque de ligne d\'échappement', $langId);
$typeOptions = buildFeatureOptions($idType, $langId);
$stateOptions = buildFeatureOptions($idState, $langId);
$exhaustBrandOptions = buildFeatureOptions($idExhaustBrand, $langId);

// ── Template ─────────────────────────────────────────────────────────────────
$tpl = <<<'TPL'
{**
 * FULLPOWEREXHAUST — barre de filtres du catalogue.
 * Généré par tools/generate_filter_bar.php — ne pas éditer à la main.
 *}
<div class="catalog-filterbar">
  <div class="filter-control filter-control--brand">
    <label class="filter-control__label" for="filter-brand">{l s='Marque de votre moto' d='Shop.Theme.FullPowerExhaust'}</label>
    <select id="filter-brand" class="filter-control__select">
      <option value="">{l s='Toutes les marques' d='Shop.Theme.FullPowerExhaust'}</option>
__BRANDS__    </select>
  </div>

  <div class="filter-control filter-control--brand">
    <label class="filter-control__label" for="filter-exhaust-brand">{l s='Marque de ligne d\'échappement' d='Shop.Theme.FullPowerExhaust'}</label>
    <select id="filter-exhaust-brand" class="filter-control__select">
      <option value="">{l s='Toutes les marques' d='Shop.Theme.FullPowerExhaust'}</option>
__EXHAUST_BRANDS__    </select>
  </div>

  <div class="filter-control">
    <label class="filter-control__label" for="filter-type">{l s='Type de pièce' d='Shop.Theme.FullPowerExhaust'}</label>
    <select id="filter-type" class="filter-control__select">
      <option value="">{l s='Tous les types' d='Shop.Theme.FullPowerExhaust'}</option>
__TYPES__    </select>
  </div>

  <div class="filter-control">
    <label class="filter-control__label" for="filter-state">{l s='État' d='Shop.Theme.FullPowerExhaust'}</label>
    <select id="filter-state" class="filter-control__select">
      <option value="">{l s='Tous les états' d='Shop.Theme.FullPowerExhaust'}</option>
__STATES__    </select>
  </div>

  <div class="filter-control">
    <label class="filter-control__label" for="filter-price-min">{l s='Prix' d='Shop.Theme.FullPowerExhaust'}</label>
    <div class="filter-control__price">
      <input type="number" id="filter-price-min" class="filter-control__input" placeholder="Min" min="0" step="1">
      <span class="filter-control__sep">—</span>
      <input type="number" id="filter-price-max" class="filter-control__input" placeholder="Max" min="0" step="1">
    </div>
  </div>

  <div class="filter-control filter-control--actions">
    <button type="button" class="filter-control__apply" onclick="applyCatalogFilters()">{l s='Filtrer' d='Shop.Theme.FullPowerExhaust'}</button>
    <a class="filter-control__reset" href="{$urls.current_url}">{l s='Réinitialiser' d='Shop.Theme.FullPowerExhaust'}</a>
  </div>
</div>

<script>
  (function () {
    function splitEscaped(str, sep) {
      var parts = [], cur = '', i;
      for (i = 0; i < str.length; i++) {
        var c = str[i];
        if (c === '\\' && str[i + 1] === sep) { cur += sep; i++; }
        else if (c === sep) { parts.push(cur); cur = ''; }
        else { cur += c; }
      }
      parts.push(cur);
      return parts;
    }
    function esc(v) {
      return String(v).replace(/\\/g, '\\\\').replace(/-/g, '\\-').replace(/\//g, '\\/');
    }
    function parseQ(q) {
      var facets = {}, i;
      if (!q) return facets;
      var parts = splitEscaped(q, '/');
      for (i = 0; i < parts.length; i++) {
        var v = splitEscaped(parts[i], '-');
        facets[v[0]] = v.slice(1);
      }
      return facets;
    }
    function getQ() {
      return new URLSearchParams(window.location.search).get('q') || '';
    }

    window.applyCatalogFilters = function () {
      var parts = [];
      var brand = document.getElementById('filter-brand').value;
      var exhaustBrand = document.getElementById('filter-exhaust-brand').value;
      var type = document.getElementById('filter-type').value;
      var state = document.getElementById('filter-state').value;
      var pmin = document.getElementById('filter-price-min').value;
      var pmax = document.getElementById('filter-price-max').value;

      if (brand) { parts.push('Marque-' + esc(brand)); }
      if (exhaustBrand) { parts.push('Marque de ligne d\'échappement-' + esc(exhaustBrand)); }
      if (type) { parts.push('Type de pièce-' + esc(type)); }
      if (state) { parts.push('État-' + esc(state)); }
      if (pmin !== '' || pmax !== '') {
        parts.push('Prix-EUR-' + (pmin === '' ? 0 : pmin) + '-' + (pmax === '' ? 999999 : pmax));
      }

      var q = parts.join('/');
      var url = new URL(window.location.href);
      if (q) { url.searchParams.set('q', q); } else { url.searchParams.delete('q'); }
      window.location.href = url.toString();
    };

    // Pré-sélection des filtres actifs à partir de l'URL
    document.addEventListener('DOMContentLoaded', function () {
      var f = parseQ(getQ());
      function setSelect(id, value) {
        var sel = document.getElementById(id);
        for (var i = 0; i < sel.options.length; i++) {
          if (sel.options[i].value === value) { sel.selectedIndex = i; return; }
        }
      }
      if (f['Marque']) { setSelect('filter-brand', f['Marque'][0]); }
      if (f['Marque de ligne d\'échappement']) { setSelect('filter-exhaust-brand', f['Marque de ligne d\'échappement'][0]); }
      if (f['Type de pièce']) { setSelect('filter-type', f['Type de pièce'][0]); }
      if (f['État']) { setSelect('filter-state', f['État'][0]); }
      if (f['Prix']) {
        document.getElementById('filter-price-min').value = f['Prix'][1] || '';
        document.getElementById('filter-price-max').value = f['Prix'][2] || '';
      }
    });
  })();
</script>
TPL;

$tpl = str_replace('__BRANDS__', $brandOptions, $tpl);
$tpl = str_replace('__EXHAUST_BRANDS__', $exhaustBrandOptions, $tpl);
$tpl = str_replace('__TYPES__', $typeOptions, $tpl);
$tpl = str_replace('__STATES__', $stateOptions, $tpl);

$dest = __DIR__ . '/../themes/hummingbird/templates/catalog/_partials/catalog-filterbar.tpl';
file_put_contents($dest, $tpl);

echo "Template généré : themes/hummingbird/templates/catalog/_partials/catalog-filterbar.tpl\n";
echo "  marques moto          : " . count($brands) . "\n";
echo "  marques d'échappement : " . substr_count($exhaustBrandOptions, '<option') . "\n";
echo "  types                 : " . substr_count($typeOptions, '<option') . "\n";
echo "  états                 : " . substr_count($stateOptions, '<option') . "\n";
