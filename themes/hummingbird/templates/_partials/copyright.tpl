{**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{block name='copyright'}
  <div class="copyright">
    {block name='copyright_link'}
      {* FULLPOWEREXHAUST — liens légaux du footer (modèle Figma) *}
      <div class="copyright__legal">
        <a href="{url entity='cms' id=3}">{l s='CGV' d='Shop.Theme.FullPowerExhaust'}</a>
        <a href="{url entity='cms' id=6}">{l s='CGU' d='Shop.Theme.FullPowerExhaust'}</a>
        <a href="{url entity='cms' id=2}">{l s='Mentions légales' d='Shop.Theme.FullPowerExhaust'}</a>
        <a href="{url entity='cms' id=7}">{l s='Politique de confidentialité' d='Shop.Theme.FullPowerExhaust'}</a>
      </div>
      <div class="copyright__right">
        <span>© {'Y'|date} FULLPOWEREXHAUST</span>
      </div>
    {/block}
  </div>

  {* Crédit centré en bas du footer *}
  <div class="site-footer__credit">
    Made with <span class="site-footer__credit-heart">♥</span> by
    <a href="https://beassistnow.com/" target="_blank" rel="noopener">Beassist</a>
  </div>
{/block}
