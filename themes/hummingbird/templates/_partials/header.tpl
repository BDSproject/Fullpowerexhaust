{**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * FULLPOWEREXHAUST — header (structure flexbox propre, fidèle au Figma) :
 *   gauche : logo  ·  centre : Accueil/Catalogue/Contact  ·  droite : icônes
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
  <header class="fpe-header">
    <div class="fpe-header-inner">

      {* ── Logo (gauche) ── *}
      <div class="fpe-logo-wrap">
        {if $page.page_name == 'index'}
          <h1 class="mb-0">
            <a class="fpe-logo font-display text-2xl font-bold tracking-widest text-foreground hover:text-primary transition-colors duration-200" href="{$urls.base_url}" aria-label="{$shop.name}">
              FULL<span class="text-primary">POWER</span>EXHAUST
            </a>
          </h1>
        {else}
          <a class="fpe-logo font-display text-2xl font-bold tracking-widest text-foreground hover:text-primary transition-colors duration-200" href="{$urls.base_url}" aria-label="{$shop.name}">
            FULL<span class="text-primary">POWER</span>EXHAUST
          </a>
        {/if}
      </div>

      {* ── Navigation (centre, desktop) ── *}
      <nav class="fpe-nav">
        <a
          href="{$urls.base_url}"
          class="text-sm font-medium tracking-widest uppercase transition-colors duration-200 pb-0.5 {if $page.page_name == 'index'}text-primary border-b border-primary{else}text-muted-foreground hover:text-foreground{/if}"
        >Accueil</a>
        <a
          href="{$urls.pages.new_products}"
          class="text-sm font-medium tracking-widest uppercase transition-colors duration-200 pb-0.5 {if in_array($page.page_name, ['category', 'new-products', 'best-sales', 'prices-drop', 'search'])}text-primary border-b border-primary{else}text-muted-foreground hover:text-foreground{/if}"
        >Catalogue</a>
        <a
          href="{$urls.pages.contact}"
          class="text-sm font-medium tracking-widest uppercase transition-colors duration-200 pb-0.5 {if $page.page_name == 'contact'}text-primary border-b border-primary{else}text-muted-foreground hover:text-foreground{/if}"
        >Contact</a>
      </nav>

      {* ── Groupe de droite ── *}
      <div class="fpe-header-right">

        {* Menu mobile (burger + offcanvas) — masqué sur desktop en CSS *}
        {hook h='displayTop'}

        {* Recherche (icône) *}
        <a
          href="{$urls.pages.search}"
          class="fpe-icon-btn text-muted-foreground hover:text-foreground transition-colors duration-200"
          aria-label="Rechercher"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
        </a>

        {* Connexion (compte) + Panier *}
        {hook h='displayNav2'}

        {* Inscription *}
        {if !$customer.is_logged}
          <a class="fpe-register-btn d-none d-lg-inline-flex" href="{$urls.pages.register}">
            S'inscrire
          </a>
        {/if}

        {* Placeholders mobile (remplis par le JS responsive du thème) *}
        <div id="_mobile_ps_customersignin" class="d-md-none"></div>
        {if !$configuration.is_catalog}
          <div id="_mobile_ps_shoppingcart" class="d-md-none"></div>
        {/if}
      </div>
    </div>
  </header>

  {capture name="nav_full_width"}{hook h='displayNavFullWidth'}{/capture}
  {if !empty($smarty.capture.nav_full_width)}
    <div class="{$headerNavFullWidth}">
      {$smarty.capture.nav_full_width nofilter}
    </div>
  {/if}
{/block}
