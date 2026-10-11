{**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * FULLPOWEREXHAUST — badges produit.
 *
 * Le badge « Neuf » affiché par défaut est le flag `new` de PrestaShop : il
 * signifie « produit ajouté récemment » (ProductLazyArray::getNew()) et NON
 * « produit neuf ». Sur une boutique de pièces d'occasion, il induisait donc
 * en erreur.
 *
 * On le remplace par l'ÉTAT RÉEL du produit ($product.condition, exposé par
 * ProductLazyArray::getCondition() : new / used / refurbished). L'état se règle
 * produit par produit dans le back-office (onglet « Options » → État) et
 * s'affiche traduit dans les 4 langues de la boutique.
 *}
{if !empty($product.flags) || !empty($product.condition)}
  {block name='product_flags'}
    <ul class="product-flags js-product-flags">
      {if !empty($product.condition)}
        <li class="badge {$product.condition.type}">{$product.condition.label}</li>
      {/if}
      {foreach from=$product.flags item=flag}
        {if $flag.type != 'new'}
          <li class="badge {$flag.type}">{$flag.label}</li>
        {/if}
      {/foreach}
    </ul>
  {/block}
{/if}
