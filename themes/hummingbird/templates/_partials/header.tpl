{**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * FULLPOWEREXHAUST — header fidèle au modèle Figma.
 * Structure BEM : .site-header · .site-header__inner · .site-header__logo ·
 * .site-header__nav · .site-header__link · .site-header__actions
 *}
{$headerBanner = 'header-banner'}
{$headerNavFullWidth = 'header-nav-full-width'}

{capture name="header_banner"}{hook h='displayBanner'}{/capture}
{block name='header_banner'}
  {if !empty($smarty.capture.header_banner)}
    <div class="{$headerBanner}">
      {$smarty.capture.header_banner nofilter}
    </div>
  {/if}
{/block}

{block name='header_main'}
  <header class="site-header">
    <div class="site-header__inner">

      {* ── Logo (gauche) ── *}
      {if $page.page_name == 'index'}
        <h1 class="site-header__title">
          <a class="site-header__logo" href="{$urls.base_url}" aria-label="{$shop.name}">
            FULL<span class="site-header__logo-accent">POWER</span>EXHAUST
          </a>
        </h1>
      {else}
        <a class="site-header__logo" href="{$urls.base_url}" aria-label="{$shop.name}">
          FULL<span class="site-header__logo-accent">POWER</span>EXHAUST
        </a>
      {/if}

      {* ── Navigation (centre, desktop) : Accueil / Catalogue / Contact ── *}
      <nav class="site-header__nav" aria-label="{l s='Main navigation' d='Shop.Theme.Global'}">
        <a
          class="site-header__link{if $page.page_name == 'index'} site-header__link--active{/if}"
          href="{$urls.base_url}"
        >{l s='Accueil' d='Shop.Theme.FullPowerExhaust'}</a>

        <a
          class="site-header__link{if in_array($page.page_name, ['category', 'new-products', 'best-sales', 'prices-drop', 'search'])} site-header__link--active{/if}"
          href="{$urls.pages.new_products}"
        >{l s='Catalogue' d='Shop.Theme.FullPowerExhaust'}</a>

        <a
          class="site-header__link{if $page.page_name == 'contact'} site-header__link--active{/if}"
          href="{$urls.pages.contact}"
        >{l s='Contact' d='Shop.Theme.FullPowerExhaust'}</a>
      </nav>

      {* ── Actions (droite) : menu mobile + recherche + compte + panier ── *}
      <div class="site-header__actions">

        {* Burger mobile (ouvre le menu offcanvas) — visible sur mobile uniquement *}
        <button
          class="site-header__burger"
          type="button"
          data-bs-toggle="offcanvas"
          data-bs-target="#siteHeaderMobileMenu"
          aria-controls="siteHeaderMobileMenu"
          aria-label="{l s='Menu' d='Shop.Theme.Global'}"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>

        {* Recherche (loupe) *}
        <a
          class="site-header__icon"
          href="{$urls.pages.search}"
          aria-label="{l s='Search' d='Shop.Theme.Catalog'}"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
        </a>

        {* Drapeau de la langue courante (mobile) : ouvre le menu où les 4 langues
           sont proposées en grand. Un <select> natif ne permet pas de n’afficher
           que le drapeau, d’où ce bouton dédié. *}
        <button
          class="site-header__lang-btn d-md-none"
          type="button"
          data-bs-toggle="offcanvas"
          data-bs-target="#siteHeaderMobileMenu"
          aria-controls="siteHeaderMobileMenu"
          aria-label="{l s='Change language' d='Shop.Theme.Global'} — {$language.name}"
          title="{$language.name}"
        >
          {if $language.iso_code == 'en'}🇬🇧{elseif $language.iso_code == 'fr'}🇫🇷{elseif $language.iso_code == 'es'}🇪🇸{elseif $language.iso_code == 'it'}🇮🇹{else}🌐{/if}
        </button>

        {* Compte (connexion) + Panier *}
        {hook h='displayNav2'}

        {* Placeholders mobile (remplis par le JS responsive du thème) *}
        <div id="_mobile_ps_customersignin" class="d-md-none"></div>
        {if !$configuration.is_catalog}
          <div id="_mobile_ps_shoppingcart" class="d-md-none"></div>
        {/if}
      </div>
    </div>
  </header>

  {* ── Menu mobile (offcanvas) : Accueil / Catalogue / Contact ── *}
  <div
    class="offcanvas offcanvas-start site-header__offcanvas"
    tabindex="-1"
    id="siteHeaderMobileMenu"
    aria-labelledby="siteHeaderMobileMenuLabel"
  >
    <div class="offcanvas-header">
      <span class="site-header__offcanvas-title" id="siteHeaderMobileMenuLabel">{l s='Menu' d='Shop.Theme.Global'}</span>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{l s='Close' d='Shop.Theme.Global'}"></button>
    </div>
    <div class="offcanvas-body">
      {* Recherche : sur mobile la loupe du header est remplacée par un vrai
         champ de saisie, plus direct qu'un lien vers la page de recherche. *}
      <form class="site-header__offcanvas-search" method="get" action="{$urls.pages.search}" role="search">
        <input type="hidden" name="controller" value="search">
        <label class="visually-hidden" for="fpe-offcanvas-search">{l s='Search' d='Shop.Theme.Catalog'}</label>
        <input
          id="fpe-offcanvas-search"
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

      <nav class="site-header__offcanvas-nav" aria-label="{l s='Main navigation' d='Shop.Theme.Global'}">
        <a class="site-header__link{if $page.page_name == 'index'} site-header__link--active{/if}" href="{$urls.base_url}">{l s='Accueil' d='Shop.Theme.FullPowerExhaust'}</a>
        <a class="site-header__link{if in_array($page.page_name, ['category', 'new-products', 'best-sales', 'prices-drop', 'search'])} site-header__link--active{/if}" href="{$urls.pages.new_products}">{l s='Catalogue' d='Shop.Theme.FullPowerExhaust'}</a>
        <a class="site-header__link{if $page.page_name == 'contact'} site-header__link--active{/if}" href="{$urls.pages.contact}">{l s='Contact' d='Shop.Theme.FullPowerExhaust'}</a>
        {* Compte : masqué du header sur mobile, il reste accessible ici. *}
        {if isset($customer) && $customer.is_logged}
          <a class="site-header__link{if $page.page_name == 'my-account'} site-header__link--active{/if}" href="{$urls.pages.my_account}">{l s='Mon compte' d='Shop.Theme.Customeraccount'}</a>
        {else}
          <a class="site-header__link{if $page.page_name == 'authentication'} site-header__link--active{/if}" href="{$urls.pages.authentication}">{l s='Connexion' d='Shop.Theme.Actions'}</a>
        {/if}
      </nav>

      {* Sélecteur de langue dans le menu mobile.
         `$urls.alternative_langs` fournit l’URL de la PAGE EN COURS dans chaque
         langue (même source que les balises hreflang) : changer de langue ne
         renvoie donc jamais le visiteur à l’accueil. *}
      <div class="site-header__offcanvas-langs">
        <span class="site-header__offcanvas-langs-title">{l s='Langue' d='Shop.Theme.FullPowerExhaust'}</span>
        <div class="site-header__offcanvas-langs-list">
          {foreach from=$urls.alternative_langs item=pageUrl key=code}
            {assign var='iso' value=$code|substr:0:2}
            {if $iso == 'en'}{assign var='langLabel' value='English'}
            {elseif $iso == 'fr'}{assign var='langLabel' value='Français'}
            {elseif $iso == 'es'}{assign var='langLabel' value='Español'}
            {elseif $iso == 'it'}{assign var='langLabel' value='Italiano'}
            {else}{assign var='langLabel' value=$code}{/if}
            {if $iso == 'en'}{assign var='langFlag' value='🇬🇧'}
            {elseif $iso == 'fr'}{assign var='langFlag' value='🇫🇷'}
            {elseif $iso == 'es'}{assign var='langFlag' value='🇪🇸'}
            {elseif $iso == 'it'}{assign var='langFlag' value='🇮🇹'}
            {else}{assign var='langFlag' value='🌐'}{/if}
            <a href="{$pageUrl}"
               hreflang="{$code}"
               {if $iso == $language.iso_code}class="is-active" aria-current="true"{/if}>
              {$langFlag} {$langLabel}
            </a>
          {/foreach}
        </div>
      </div>
    </div>
  </div>

  {capture name="nav_full_width"}{hook h='displayNavFullWidth'}{/capture}
  {if !empty($smarty.capture.nav_full_width)}
    <div class="{$headerNavFullWidth}">
      {$smarty.capture.nav_full_width nofilter}
    </div>
  {/if}
{/block}
