{**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{capture name="footer_before"}{hook h='displayFooterBefore'}{/capture}
{if $smarty.capture.footer_before}
  {block name='hook_footer_before'}
    <div class="footer footer__before">
      {$smarty.capture.footer_before nofilter}
    </div>
  {/block}
{/if}

{block name='footer_main'}
  <div class="footer footer__main">
    <div class="container">
      {* FULLPOWEREXHAUST — bloc marque + contact en tête de footer *}
      <div class="fpe-footer-head">
        <div>
          <span class="fpe-footer-brand">FULL<span class="accent">POWER</span>EXHAUST</span>
          <p class="fpe-footer-tagline">L'échappement performance occasion.</p>
        </div>
        <div class="fpe-footer-contact">
          <a href="mailto:contact@fullpowerexhaust.com">contact@fullpowerexhaust.com</a>
          <span>fullpowerexhaust.com</span>
        </div>
      </div>

      {capture name="footer_main_top"}{hook h='displayFooter'}{/capture}
      {if $smarty.capture.footer_main_top}
        {block name='hook_footer_main'}
          <div class="footer__main-top row">
            {$smarty.capture.footer_main_top nofilter}
          </div>
        {/block}
      {/if}

      {capture name="footer_main_bottom"}{hook h='displayFooterAfter'}{/capture}
      {if isset($smarty.capture.footer_main_bottom) && $smarty.capture.footer_main_bottom}
        {block name='hook_footer_after'}
          <div class="footer__main-bottom row">
            {$smarty.capture.footer_main_bottom nofilter}
          </div>
        {/block}
      {/if}

      {include file='_partials/copyright.tpl'}
    </div>
  </div>
{/block}
