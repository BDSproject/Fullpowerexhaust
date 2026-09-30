{**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{extends file='page.tpl'}

{block name='page_header_container'}
  {* FULLPOWEREXHAUST — en-tête de page légale (classes Tailwind du Figma) *}
  <div class="page-header page-header--cms max-w-3xl mx-auto px-5">
    <p class="page-header__eyebrow text-xs tracking-widest uppercase text-primary mb-1">Informations légales</p>
    <h1 class="page-title-section font-display text-4xl font-extrabold text-foreground tracking-tight mb-10">{$cms.meta_title}</h1>
  </div>
{/block}

{block name='page_content_container'}
  <section id="content" class="page-content page-content--cms rich-text js-page-content-cms max-w-3xl mx-auto px-5 pb-20 text-sm text-muted-foreground leading-relaxed">
    {block name='cms_content'}
      {$cms.content nofilter}
    {/block}

    {block name='hook_cms_dispute_information'}
      {hook h='displayCMSDisputeInformation'}
    {/block}

    {block name='hook_cms_print_button'}
      {hook h='displayCMSPrintButton'}
    {/block}
  </section>
{/block}
