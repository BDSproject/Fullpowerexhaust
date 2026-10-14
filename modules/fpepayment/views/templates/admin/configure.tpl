{* FPE Paiement — configuration *}
<div class="panel">
  <div class="panel-heading">
    <i class="icon-credit-card"></i> FPE Paiement (Stripe) — page hébergée
  </div>
  <div class="panel-body">
    <form method="post" class="form-horizontal">
      <div class="form-group">
        <label class="control-label col-lg-3">Mode</label>
        <div class="col-lg-9">
          <select name="FPE_STRIPE_MODE" class="form-control fixed-width-xl">
            <option value="test" {if $fpe.mode == 'test'}selected{/if}>Test (clés sk_test_)</option>
            <option value="live" {if $fpe.mode == 'live'}selected{/if}>Production (clés sk_live_)</option>
          </select>
          <p class="help-block">Utilisez le mode Test tant que les paiements réels ne sont pas validés.</p>
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-lg-3">Clé secrète de test (sk_test_…)</label>
        <div class="col-lg-9">
          <input type="text" name="FPE_STRIPE_TEST_SECRET" value="{$fpe.test_secret|escape:'html':'UTF-8'}" class="form-control fixed-width-xxl">
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-lg-3">Clé secrète de production (sk_live_…)</label>
        <div class="col-lg-9">
          <input type="text" name="FPE_STRIPE_LIVE_SECRET" value="{$fpe.live_secret|escape:'html':'UTF-8'}" class="form-control fixed-width-xxl">
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-lg-3">Clé de signature du webhook (whsec_…)</label>
        <div class="col-lg-9">
          <input type="text" name="FPE_STRIPE_WEBHOOK_SECRET" value="{$fpe.webhook_secret|escape:'html':'UTF-8'}" class="form-control fixed-width-xxl">
          <p class="help-block">Créez un endpoint dans le Dashboard Stripe et recopiez sa « signing secret ».</p>
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
          <p class="help-block">État donné à la commande au moment de la redirection vers Stripe (avant validation du paiement).</p>
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-lg-3">URL du webhook</label>
        <div class="col-lg-9">
          <input type="text" value="{$fpe.webhook_url|escape:'html':'UTF-8'}" class="form-control fixed-width-xxl" readonly onclick="this.select()">
          <p class="help-block">À coller dans Stripe → Développeurs → Webhooks → Ajouter un endpoint (événement <code>checkout.session.completed</code>).</p>
        </div>
      </div>

      <div class="panel-footer">
        <button type="submit" name="submitFpeStripe" class="btn btn-primary pull-right">
          <i class="process-icon-save"></i> Enregistrer
        </button>
      </div>
    </form>
  </div>
</div>
