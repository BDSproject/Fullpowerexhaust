{* FPE Paiement — page de retour *}
{extends file='page.tpl'}

{block name='page_content'}
  <div class="fpe-payment-return" style="max-width:640px;margin:2rem auto;text-align:center;">
    {if isset($fpe_canceled) && $fpe_canceled}
      <h1 class="page-title-section">{l s='Paiement annulé' d='Shop.Theme.Checkout'}</h1>
      <p>{l s='Votre commande n\'a pas été finalisée. Vous pouvez réessayer.' d='Shop.Theme.Checkout'}</p>
      <a class="btn btn-primary" href="{$urls.pages.order}">{l s='Retour à la commande' d='Shop.Theme.Checkout'}</a>

    {elseif isset($fpe_pending) && $fpe_pending}
      <h1 class="page-title-section">{l s='Paiement en cours de validation' d='Shop.Theme.Checkout'}</h1>
      <p>{l s='Nous confirmons votre paiement. Cette page se rafraîchit automatiquement.' d='Shop.Theme.Checkout'}</p>
      <div class="spinner-border text-primary-emphasis" role="status"></div>
      <script>setTimeout(function () { window.location.reload(); }, 3000);</script>

    {else}
      <h1 class="page-title-section">{l s='Paiement confirmé' d='Shop.Theme.Checkout'}</h1>
    {/if}
  </div>
{/block}
