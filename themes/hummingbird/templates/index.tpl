{**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * FULLPOWEREXHAUST — page d'accueil, classes Tailwind identiques au Figma.
 *}
{extends file=$layout}

{block name='breadcrumb'}{/block}

{block name='content_columns'}
  {block name='left_column'}{/block}

  {block name='content_wrapper'}
    <div id="center-column" class="center-column page">
      {hook h="displayContentWrapperTop"}

      {block name='content'}
        {block name='page_content_container'}
          <div id="content" class="page-content page-content--home">
            {block name='page_content_top'}{/block}

            {block name='page_content'}
              {block name='home_hero'}
                {* ── Hero plein écran (Figma) ── *}
                <section class="relative w-full" style="height:100svh;min-height:520px;">
                  <img
                    class="absolute inset-0 w-full h-full object-cover"
                    src="{$urls.theme_assets}img/fullpower/hero.jpeg"
                    alt="FullPowerExhaust — Spécialiste de l'échappement performance"
                    fetchpriority="high"
                  >
                  <div class="absolute inset-0" style="background:linear-gradient(to right, rgba(13,13,13,0.92) 0%, rgba(13,13,13,0.75) 40%, rgba(13,13,13,0.2) 70%, transparent 100%)"></div>
                  <div class="absolute inset-x-0 bottom-0 h-32" style="background:linear-gradient(to top, #0d0d0d, transparent)"></div>

                  <div class="absolute inset-0 flex items-center">
                    <div class="max-w-[1320px] w-full mx-auto px-8 md:px-16">
                      <p class="text-primary text-xs tracking-[0.3em] uppercase mb-5">{l s='Lignes d\'occasion · Toutes marques' d='Shop.Theme.FullPowerExhaust'}</p>
                      <h1 class="font-display text-7xl md:text-9xl font-extrabold text-white leading-[0.9] tracking-tight mb-8">
                        {l s='Sonorité' d='Shop.Theme.FullPowerExhaust'}<br>
                        <span class="text-primary">{l s='Performance' d='Shop.Theme.FullPowerExhaust'}</span><br>
                        {l s='Occasion' d='Shop.Theme.FullPowerExhaust'}
                      </h1>
                      <div class="w-10 h-0.5 bg-primary mb-8"></div>
                      <div class="fpe-hero-actions flex flex-wrap gap-4">
                        <a class="bg-primary text-white px-10 py-4 text-base font-bold tracking-widest uppercase hover:bg-primary-hover transition-colors duration-200 flex items-center gap-2 rounded-md" href="{$urls.pages.new_products}">
                          {l s='Voir le catalogue' d='Shop.Theme.FullPowerExhaust'} <span class="material-icons text-lg">&#xE315;</span>
                        </a>
                        <a class="border-2 border-accent text-accent px-10 py-4 text-base font-bold tracking-widest uppercase hover:bg-primary hover:text-white transition-colors duration-200 flex items-center gap-2 rounded-md" href="https://www.ebay.fr/str/alchimistebmw" target="_blank" rel="noopener nofollow">
                          {l s='Boutique eBay' d='Shop.Theme.FullPowerExhaust'} <span class="material-icons text-lg">&#xE89E;</span>
                        </a>
                        <a class="border-2 border-accent text-accent px-10 py-4 text-base font-bold tracking-widest uppercase hover:bg-primary hover:text-white transition-colors duration-200 rounded-md" href="{$urls.pages.my_account}">
                          {l s='Mon compte' d='Shop.Theme.FullPowerExhaust'}
                        </a>
                      </div>
                    </div>
                  </div>
                </section>
              {/block}

              {block name='home_features'}
                {* ── Bande réassurance ── *}
                <section class="border-y border-border bg-card">
                  <div class="max-w-[1320px] mx-auto px-5 py-10 grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="flex flex-col gap-2">
                      <svg class="text-primary" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
                      <p class="font-display text-base font-bold tracking-wider text-foreground uppercase">{l s='Performance' d='Shop.Theme.FullPowerExhaust'}</p>
                      <p class="text-xs text-muted-foreground">{l s='Contrôlée avant mise en vente' d='Shop.Theme.FullPowerExhaust'}</p>
                    </div>
                    <div class="flex flex-col gap-2">
                      <svg class="text-primary" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4.702a.705.705 0 0 0-1.203-.498L6.413 7.587A1.4 1.4 0 0 1 5.416 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.416a1.4 1.4 0 0 1 .997.413l3.383 3.384A.705.705 0 0 0 11 19.298z"/><path d="M16 9a5 5 0 0 1 0 6"/><path d="M19.364 18.364a9 9 0 0 0 0-12.728"/></svg>
                      <p class="font-display text-base font-bold tracking-wider text-foreground uppercase">{l s='Sonorité unique' d='Shop.Theme.FullPowerExhaust'}</p>
                      <p class="text-xs text-muted-foreground">{l s='Chaque ligne testée à l\'oreille' d='Shop.Theme.FullPowerExhaust'}</p>
                    </div>
                    <div class="flex flex-col gap-2">
                      <svg class="text-primary" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                      <p class="font-display text-base font-bold tracking-wider text-foreground uppercase">{l s='Qualité garantie' d='Shop.Theme.FullPowerExhaust'}</p>
                      <p class="text-xs text-muted-foreground">{l s='Pièces inspectées et certifiées' d='Shop.Theme.FullPowerExhaust'}</p>
                    </div>
                    <div class="flex flex-col gap-2">
                      <svg class="text-primary" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                      <p class="font-display text-base font-bold tracking-wider text-foreground uppercase">{l s='Expertise' d='Shop.Theme.FullPowerExhaust'}</p>
                      <p class="text-xs text-muted-foreground">{l s='5 ans dans l\'aftermarket' d='Shop.Theme.FullPowerExhaust'}</p>
                    </div>
                  </div>
                </section>
              {/block}

              {block name='hook_home'}
                {$HOOK_HOME nofilter}
              {/block}

              {block name='home_cta'}
                {* ── CTA « marque introuvable » (Figma) ── *}
                <section class="border-t border-border bg-card">
                  <div class="max-w-[1320px] mx-auto px-5 py-16 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div>
                      <h2 class="font-display text-3xl md:text-4xl font-extrabold text-foreground tracking-tight">{l s='Parlez-nous de vos projets' d='Shop.Theme.FullPowerExhaust'}</h2>
                      <p class="text-muted-foreground text-sm mt-2">{l s='Contactez-nous, nous sourçons pour toutes les marques européennes et asiatiques.' d='Shop.Theme.FullPowerExhaust'}</p>
                    </div>
                    <a class="shrink-0 bg-primary text-white px-10 py-4 text-base font-bold tracking-widest uppercase hover:bg-primary-hover transition-colors duration-200 rounded-md" href="{$urls.pages.contact}">{l s='Nous contacter' d='Shop.Theme.FullPowerExhaust'}</a>
                  </div>
                </section>
              {/block}
            {/block}
          </div>
        {/block}
      {/block}

      {hook h="displayContentWrapperBottom"}
    </div>
  {/block}

  {block name='right_column'}{/block}
{/block}
