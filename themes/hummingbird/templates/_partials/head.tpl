{**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{block name='head_charset'}
  <meta charset="utf-8">
{/block}

{* Détection de la langue du navigateur → redirection vers la langue du visiteur *}
<script>
(function () {
  try {
    if (document.cookie.indexOf('fpe_lang_detected=') !== -1) { return; }
    document.cookie = 'fpe_lang_detected=1; max-age=31536000; path=/';

    var current = '{$language.iso_code}';
    var prefixes = { fr: '', en: '/en', es: '/es', it: '/it' };

    var detected = null;
    var browserLangs = (navigator.languages && navigator.languages.length) ? navigator.languages : [navigator.language || navigator.userLanguage || 'fr'];
    for (var i = 0; i < browserLangs.length; i++) {
      var l = String(browserLangs[i] || '').toLowerCase().split('-')[0];
      if (typeof prefixes[l] !== 'undefined') { detected = l; break; }
    }
    if (!detected || detected === current) { return; }

    var path = window.location.pathname;
    var curPrefix = prefixes[current] || '';
    if (curPrefix && path.indexOf(curPrefix + '/') === 0) {
      path = path.substring(curPrefix.length) || '/';
    } else if (curPrefix && path === curPrefix) {
      path = '/';
    }
    var newPrefix = prefixes[detected] || '';
    var target = newPrefix + path;
    if (target.charAt(0) !== '/') { target = '/' + target; }

    window.location.replace(target + window.location.search);
  } catch (e) {}
})();
</script>

{block name='head_ie_compatibility'}
  <meta http-equiv="x-ua-compatible" content="ie=edge">
{/block}

{block name='head_seo'}
  {block name='head_preload'}
    {include file='_partials/preload.tpl'}
  {/block}

  <title>{block name='head_seo_title'}{$page.meta.title}{/block}</title>

  {block name='hook_after_title_tag'}
    {hook h='displayAfterTitleTag'}
  {/block}

  <meta name="description" content="{block name='head_seo_description'}{$page.meta.description}{/block}">

  {if $page.meta.robots !== 'index'}
    <meta name="robots" content="{$page.meta.robots}">
  {/if}

  {if $page.canonical}
    <link rel="canonical" href="{$page.canonical}">
  {/if}

  {block name='head_hreflang'}
    {foreach from=$urls.alternative_langs item=pageUrl key=code}
      <link rel="alternate" href="{$pageUrl}" hreflang="{$code}">
    {/foreach}
  {/block}

  {block name='head_microdata'}
    {include file='_partials/microdata/head-jsonld.tpl'}
  {/block}

  {block name='head_microdata_special'}{/block}

  {block name='head_pagination_seo'}
    {include file='_partials/pagination-seo.tpl'}
  {/block}

  {block name='head_open_graph'}
    <meta property="og:title" content="{$page.meta.title}">
    <meta property="og:description" content="{$page.meta.description}">
    <meta property="og:url" content="{$urls.current_url}">
    <meta property="og:site_name" content="{$shop.name}">
    {if !isset($product) && $page.page_name != 'product'}<meta property="og:type" content="website">{/if}
  {/block}
{/block}

{block name='head_viewport'}
  <meta name="viewport" content="width=device-width, initial-scale=1">
{/block}

{block name='head_icons'}
  <link rel="icon" type="image/vnd.microsoft.icon" href="{$shop.favicon}?{$shop.favicon_update_time}">
  <link rel="shortcut icon" type="image/x-icon" href="{$shop.favicon}?{$shop.favicon_update_time}">
{/block}

{block name='head_fonts'}
  {* FULLPOWEREXHAUST — polices du design system (Outfit + Barlow Condensed) *}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Outfit:wght@300;400;500;600&display=swap">
{/block}

{block name='stylesheets'}
  {include file='_partials/stylesheets.tpl' stylesheets=$stylesheets}
  {* FULLPOWEREXHAUST — custom.css est déjà enregistré par PrestaShop
     (FrontController, id « theme-custom », priorité 1000) et combiné dans le
     bundle CCC : le re-déclarer ici le téléchargeait DEUX FOIS. On ne garde
     que Tailwind, qui n'est pas enregistré par le cœur. *}
  <link rel="stylesheet" href="{$urls.theme_assets}css/tailwind.css" type="text/css" media="all">
{/block}

{block name='javascript_head'}
  {include file='_partials/javascript.tpl' javascript=$javascript.head vars=$js_custom_vars}
{/block}

{block name='hook_header'}
  {$HOOK_HEADER nofilter}
{/block}

{block name='hook_extra'}{/block}
