{* FPE Paiement — configuration (BO minimal) *}
<div class="panel">
  <div class="panel-heading">
    <i class="icon-credit-card"></i> Paiement par carte (Stripe Connect)
  </div>
  <div class="panel-body">

    {* ── Connexion Stripe ── *}
    <div class="well">
      {if $fpe.connected}
        <p>
          <span class="label label-success" style="font-size:0.9em;">
            <i class="icon-check"></i> Connecté à Stripe
          </span>
          &nbsp; Compte : <code>{$fpe.account|escape:'html':'UTF-8'}</code>
        </p>
        <form method="post">
          <button type="submit" name="submitFpeDisconnect" class="btn btn-danger">
            <i class="icon-unlink"></i> Déconnecter
          </button>
        </form>
      {else}
        <p>Connectez la boutique à Stripe en un clic — aucune clé à saisir.</p>
        {if $fpe.connect_url}
          <a href="{$fpe.connect_url|escape:'html':'UTF-8'}" class="btn btn-primary btn-lg">
            <i class="icon-external-link"></i> Se connecter à Stripe
          </a>
        {else}
          <div class="alert alert-warning">Renseignez d'abord l'adresse du backend Beassisted ci-dessous.</div>
        {/if}
      {/if}
    </div>

    <form method="post" class="form-horizontal">
      <div class="form-group">
        <label class="control-label col-lg-3">Backend Beassisted</label>
        <div class="col-lg-9">
          <input type="text" name="FPE_STRIPE_BACKEND_URL" value="{$fpe.backend_url|escape:'html':'UTF-8'}" class="form-control fixed-width-xxl" placeholder="https://pay.beassistednow.com">
          <p class="help-block">L'adresse du service de paiement (fournie par Beassisted).</p>
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-lg-3">État « en attente de paiement »</label>
        <div class="col-lg-9">
          <select name="FPE_STRIPE_AWAITING_STATE" class="form-control fixed-width-xl">
            {foreach from=$fpe.order_states item=state}
              <option value="{$state.id_order_state}" {if $fpe.awaiting_state == $state.id_order_state}selected{/if}>{$state.name|escape:'html':'UTF-8'}</option>
            {/foreach}
          </select>
        </div>
      </div>

      <div class="panel-footer">
        <button type="submit" name="submitFpeConfig" class="btn btn-primary pull-right">
          <i class="process-icon-save"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>
