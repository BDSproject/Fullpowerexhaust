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
      <nav class="site-header__offcanvas-nav" aria-label="{l s='Main navigation' d='Shop.Theme.Global'}">
        <a class="site-header__link{if $page.page_name == 'index'} site-header__link--active{/if}" href="{$urls.base_url}">{l s='Accueil' d='Shop.Theme.FullPowerExhaust'}</a>
        <a class="site-header__link{if in_array($page.page_name, ['category', 'new-products', 'best-sales', 'prices-drop', 'search'])} site-header__link--active{/if}" href="{$urls.pages.new_products}">{l s='Catalogue' d='Shop.Theme.FullPowerExhaust'}</a>
        <a class="site-header__link{if $page.page_name == 'contact'} site-header__link--active{/if}" href="{$urls.pages.contact}">{l s='Contact' d='Shop.Theme.FullPowerExhaust'}</a>
      </nav>

      {* Sélecteur de langue dans le menu mobile *}
      <div class="site-header__offcanvas-langs">
        <span class="site-header__offcanvas-langs-title">{l s='Langue' d='Shop.Theme.FullPowerExhaust'}</span>
        <div class="site-header__offcanvas-langs-list">
          <a href="{$urls.base_url}en/">🇬🇧 English</a>
          <a href="{$urls.base_url}">🇫🇷 Français</a>
          <a href="{$urls.base_url}es/">🇪🇸 Español</a>
          <a href="{$urls.base_url}it/">🇮🇹 Italiano</a>
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
