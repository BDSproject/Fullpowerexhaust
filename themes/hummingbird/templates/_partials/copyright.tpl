{**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}
{block name='copyright'}
  <div class="copyright">
    {block name='copyright_link'}
      {* FULLPOWEREXHAUST — liens légaux du footer (modèle Figma) *}
      <div class="copyright__legal">
        <a href="{url entity='cms' id=3}">CGV</a>
        <a href="{url entity='cms' id=6}">CGU</a>
        <a href="{url entity='cms' id=2}">Mentions légales</a>
        <a href="{url entity='cms' id=7}">Politique de confidentialité</a>
      </div>
      <div class="copyright__right">
        <span>© {'Y'|date} FULLPOWEREXHAUST</span>
        <span class="copyright__prestashop">
          {l s='%copyright% %year% - Ecommerce software by %prestashop%' sprintf=['%prestashop%' => 'PrestaShop™', '%year%' => 'Y'|date, '%copyright%' => '©'] d='Shop.Theme.Global'}
        </span>
      </div>
    {/block}
  </div>
{/block}
