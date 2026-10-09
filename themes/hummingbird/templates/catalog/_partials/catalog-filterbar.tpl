{**
 * FULLPOWEREXHAUST — barre de filtres du catalogue.
 * Généré par tools/generate_filter_bar.php — ne pas éditer à la main.
 *}
{* Recherche : le champ est placé ici (pages catalogue et recherche), et non
   dans l’en-tête, conformément au design. *}
<form class="catalog-searchbar" method="get" action="{$urls.pages.search}" role="search">
  <input type="hidden" name="controller" value="search">
  <label class="visually-hidden" for="catalog-search-input">{l s='Search' d='Shop.Theme.Catalog'}</label>
  <input
    id="catalog-search-input"
    type="search"
    name="s"
    value="{$search_string|default:''|escape:'html':'UTF-8'}"
    placeholder="{l s='Rechercher un produit…' d='Shop.Theme.FullPowerExhaust'}"
    autocomplete="off"
  >
  <button type="submit" aria-label="{l s='Search' d='Shop.Theme.Catalog'}">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
  </button>
</form>

<div class="catalog-filterbar">
  <div class="filter-control filter-control--brand">
    <label class="filter-control__label" for="filter-brand">{l s='Marque de votre moto' d='Shop.Theme.FullPowerExhaust'}</label>
    <select id="filter-brand" class="filter-control__select">
      <option value="">{l s='Toutes les marques' d='Shop.Theme.FullPowerExhaust'}</option>
        <option value="Aeon">Aeon</option>
        <option value="AJP">AJP</option>
        <option value="AJS">AJS</option>
        <option value="Aprilia">Aprilia</option>
        <option value="Arch Motorcycle">Arch Motorcycle</option>
        <option value="Ariel">Ariel</option>
        <option value="Askoll">Askoll</option>
        <option value="Ather">Ather</option>
        <option value="Bajaj">Bajaj</option>
        <option value="Benelli">Benelli</option>
        <option value="Beta">Beta</option>
        <option value="Bimota">Bimota</option>
        <option value="BMW Motorrad">BMW Motorrad</option>
        <option value="Boss Hoss">Boss Hoss</option>
        <option value="Brough Superior">Brough Superior</option>
        <option value="BSA">BSA</option>
        <option value="Buell">Buell</option>
        <option value="Bultaco">Bultaco</option>
        <option value="Cagiva">Cagiva</option>
        <option value="CCM">CCM</option>
        <option value="CFMoto">CFMoto</option>
        <option value="Derbi">Derbi</option>
        <option value="Dnepr">Dnepr</option>
        <option value="Ducati">Ducati</option>
        <option value="Energica">Energica</option>
        <option value="Fantic">Fantic</option>
        <option value="FB Mondial">FB Mondial</option>
        <option value="Garelli">Garelli</option>
        <option value="Gas Gas">Gas Gas</option>
        <option value="Gilera">Gilera</option>
        <option value="GPX">GPX</option>
        <option value="Haojue">Haojue</option>
        <option value="Harley-Davidson">Harley-Davidson</option>
        <option value="Hartford">Hartford</option>
        <option value="Hercules">Hercules</option>
        <option value="Hero MotoCorp">Hero MotoCorp</option>
        <option value="Hesketh">Hesketh</option>
        <option value="Honda">Honda</option>
        <option value="Horex">Horex</option>
        <option value="Horwin">Horwin</option>
        <option value="Husaberg">Husaberg</option>
        <option value="Husqvarna">Husqvarna</option>
        <option value="Hyosung">Hyosung</option>
        <option value="Indian">Indian</option>
        <option value="Italjet">Italjet</option>
        <option value="IZH">IZH</option>
        <option value="Janus">Janus</option>
        <option value="Jawa">Jawa</option>
        <option value="Kawasaki">Kawasaki</option>
        <option value="Keeway">Keeway</option>
        <option value="Kove">Kove</option>
        <option value="KR Motors">KR Motors</option>
        <option value="Kreidler">Kreidler</option>
        <option value="KTM">KTM</option>
        <option value="Kymco">Kymco</option>
        <option value="Lambretta">Lambretta</option>
        <option value="Laverda">Laverda</option>
        <option value="Lifan">Lifan</option>
        <option value="Lightning Motorcycles">Lightning Motorcycles</option>
        <option value="LiveWire">LiveWire</option>
        <option value="Loncin">Loncin</option>
        <option value="Magni">Magni</option>
        <option value="Maico">Maico</option>
        <option value="Malaguti">Malaguti</option>
        <option value="Mash">Mash</option>
        <option value="Matchless">Matchless</option>
        <option value="Minsk">Minsk</option>
        <option value="Montesa">Montesa</option>
        <option value="Morbidelli">Morbidelli</option>
        <option value="Moto Guzzi">Moto Guzzi</option>
        <option value="Moto Morini">Moto Morini</option>
        <option value="MV Agusta">MV Agusta</option>
        <option value="Niu">Niu</option>
        <option value="Norton">Norton</option>
        <option value="NSU">NSU</option>
        <option value="Ola Electric">Ola Electric</option>
        <option value="Orcal">Orcal</option>
        <option value="Ossa">Ossa</option>
        <option value="Paton">Paton</option>
        <option value="Peugeot Motocycles">Peugeot Motocycles</option>
        <option value="PGO">PGO</option>
        <option value="Piaggio">Piaggio</option>
        <option value="QJMotor">QJMotor</option>
        <option value="Revolt">Revolt</option>
        <option value="Rieju">Rieju</option>
        <option value="Royal Enfield">Royal Enfield</option>
        <option value="Sachs">Sachs</option>
        <option value="Segway Powersports">Segway Powersports</option>
        <option value="Sherco">Sherco</option>
        <option value="Shineray">Shineray</option>
        <option value="Silence">Silence</option>
        <option value="Super Soco">Super Soco</option>
        <option value="Suzuki">Suzuki</option>
        <option value="SWM">SWM</option>
        <option value="SYM">SYM</option>
        <option value="TM Racing">TM Racing</option>
        <option value="Triumph">Triumph</option>
        <option value="TVS">TVS</option>
        <option value="Ultraviolette">Ultraviolette</option>
        <option value="Ural">Ural</option>
        <option value="Velocette">Velocette</option>
        <option value="Vespa">Vespa</option>
        <option value="Vincent">Vincent</option>
        <option value="Voge">Voge</option>
        <option value="Voxan">Voxan</option>
        <option value="Vyrus">Vyrus</option>
        <option value="Yamaha">Yamaha</option>
        <option value="Yezdi">Yezdi</option>
        <option value="Zero Motorcycles">Zero Motorcycles</option>
        <option value="Zongshen">Zongshen</option>
        <option value="Zontes">Zontes</option>
        <option value="Zündapp">Zündapp</option>
    </select>
  </div>

  <div class="filter-control filter-control--brand">
    <label class="filter-control__label" for="filter-exhaust-brand">{l s='Marque de ligne d\'échappement' d='Shop.Theme.FullPowerExhaust'}</label>
    <select id="filter-exhaust-brand" class="filter-control__select">
      <option value="">{l s='Toutes les marques' d='Shop.Theme.FullPowerExhaust'}</option>
        <option value="Akrapovic">Akrapovic</option>
        <option value="Akrapovič">Akrapovič</option>
        <option value="Arrow">Arrow</option>
        <option value="Bassani">Bassani</option>
        <option value="Beet">Beet</option>
        <option value="Beowulf">Beowulf</option>
        <option value="Bidalot">Bidalot</option>
        <option value="BlueFlame">BlueFlame</option>
        <option value="BOS Exhaust">BOS Exhaust</option>
        <option value="Brock&#039;s Performance">Brock&#039;s Performance</option>
        <option value="Cobra Exhaust">Cobra Exhaust</option>
        <option value="Competition Werkes">Competition Werkes</option>
        <option value="D&amp;D Exhaust">D&amp;D Exhaust</option>
        <option value="Delkevic">Delkevic</option>
        <option value="Devil">Devil</option>
        <option value="Domino">Domino</option>
        <option value="Dynomax">Dynomax</option>
        <option value="Exan">Exan</option>
        <option value="FMF Racing">FMF Racing</option>
        <option value="Fresco">Fresco</option>
        <option value="Fuel Exhausts">Fuel Exhausts</option>
        <option value="Giannelli">Giannelli</option>
        <option value="GPR Exhaust">GPR Exhaust</option>
        <option value="Graves Motorsports">Graves Motorsports</option>
        <option value="HINDLE">HINDLE</option>
        <option value="HMF Racing">HMF Racing</option>
        <option value="HP Corse">HP Corse</option>
        <option value="IXIL">IXIL</option>
        <option value="IxRace">IxRace</option>
        <option value="Jekill &amp; Hyde">Jekill &amp; Hyde</option>
        <option value="K-Factory">K-Factory</option>
        <option value="Kitaco">Kitaco</option>
        <option value="Laser">Laser</option>
        <option value="LeoVince">LeoVince</option>
        <option value="Leovince">Leovince</option>
        <option value="M4 Exhaust">M4 Exhaust</option>
        <option value="Malossi">Malossi</option>
        <option value="Marving">Marving</option>
        <option value="Mivv">Mivv</option>
        <option value="Moriwaki">Moriwaki</option>
        <option value="Moto Master">Moto Master</option>
        <option value="MTC Exhaust">MTC Exhaust</option>
        <option value="Nojima">Nojima</option>
        <option value="Over Racing">Over Racing</option>
        <option value="Polini">Polini</option>
        <option value="Pro Circuit">Pro Circuit</option>
        <option value="QD Exhaust">QD Exhaust</option>
        <option value="Quill Exhausts">Quill Exhausts</option>
        <option value="Remus">Remus</option>
        <option value="Rinehart Racing">Rinehart Racing</option>
        <option value="RoadsItalia">RoadsItalia</option>
        <option value="S&amp;S Cycle">S&amp;S Cycle</option>
        <option value="SC-Project">SC-Project</option>
        <option value="Scorpion Exhausts">Scorpion Exhausts</option>
        <option value="Sebring">Sebring</option>
        <option value="Shark">Shark</option>
        <option value="Sito">Sito</option>
        <option value="SP Tadao">SP Tadao</option>
        <option value="Spark">Spark</option>
        <option value="Stage6">Stage6</option>
        <option value="Takegawa">Takegawa</option>
        <option value="Tecnigas">Tecnigas</option>
        <option value="Termignoni">Termignoni</option>
        <option value="Toce Performance">Toce Performance</option>
        <option value="TSR">TSR</option>
        <option value="Two Brothers Racing">Two Brothers Racing</option>
        <option value="Vance &amp; Hines">Vance &amp; Hines</option>
        <option value="VooDoo Exhaust">VooDoo Exhaust</option>
        <option value="Wolfman">Wolfman</option>
        <option value="Yasuni">Yasuni</option>
        <option value="Yoshimura">Yoshimura</option>
        <option value="Yoshimura R&amp;D">Yoshimura R&amp;D</option>
        <option value="Zard">Zard</option>
        <option value="Zeta">Zeta</option>
    </select>
  </div>

  <div class="filter-control">
    <label class="filter-control__label" for="filter-type">{l s='Type de pièce' d='Shop.Theme.FullPowerExhaust'}</label>
    <select id="filter-type" class="filter-control__select">
      <option value="">{l s='Tous les types' d='Shop.Theme.FullPowerExhaust'}</option>
        <option value="Catalyseur">Catalyseur</option>
        <option value="Collecteur">Collecteur</option>
        <option value="Décata">Décata</option>
        <option value="Filtre à air">Filtre à air</option>
        <option value="Ligne complète">Ligne complète</option>
        <option value="Silencieux">Silencieux</option>
        <option value="Sortie d&#039;échappement">Sortie d&#039;échappement</option>
    </select>
  </div>

  <div class="filter-control">
    <label class="filter-control__label" for="filter-state">{l s='État' d='Shop.Theme.FullPowerExhaust'}</label>
    <select id="filter-state" class="filter-control__select">
      <option value="">{l s='Tous les états' d='Shop.Theme.FullPowerExhaust'}</option>
        <option value="Bon état">Bon état</option>
        <option value="Comme neuf">Comme neuf</option>
        <option value="Très bon état">Très bon état</option>
    </select>
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