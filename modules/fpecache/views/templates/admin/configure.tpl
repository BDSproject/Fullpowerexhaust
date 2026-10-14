{* FullPowerExhaust Cache — page d'administration *}

<div class="fpe-cache panel">
  <div class="panel-heading">
    <i class="icon-gears"></i> FullPowerExhaust Cache
    <span class="badge">v1.0.0</span>
  </div>

  <div class="row">
    <div class="col-lg-8">

      {* ── Actions ── *}
      <div class="panel">
        <div class="panel-heading"><i class="icon-bolt"></i> Actions</div>
        <div class="panel-body">
          <form method="post" class="form-inline" style="margin-bottom:10px;">
            <button type="submit" name="submitFpePurge" class="btn btn-default">
              <i class="icon-trash"></i> Purger tous les caches
            </button>
          </form>
          <form method="post" class="form-inline">
            <button type="submit" name="submitFpeOptimize" class="btn btn-success">
              <i class="icon-check"></i> Appliquer les réglages de production + purger
            </button>
            <small class="text-muted" style="margin-left:10px;">
              Smarty (pas de recompilation), CCC, cache navigation, Cache-Control.
            </small>
          </form>
        </div>
      </div>

      {* ── État du cache ── *}
      <div class="panel">
        <div class="panel-heading"><i class="icon-dashboard"></i> État du cache</div>
        <table class="table">
          <thead>
            <tr><th>Réglage</th><th>Valeur</th><th>Recommandé</th></tr>
          </thead>
          <tbody>
            <tr class="{if $fpe.settings.smarty.force_compile != 0}danger{/if}">
              <td>Smarty — recompilation forcée</td>
              <td>{$fpe.settings.smarty.force_compile}</td>
              <td>0</td>
            </tr>
            <tr class="{if $fpe.settings.smarty.clear_cache != 'never'}danger{/if}">
              <td>Smarty — purge du cache</td>
              <td>{$fpe.settings.smarty.clear_cache}</td>
              <td>never</td>
            </tr>
            <tr>
              <td>Smarty — cache</td>
              <td>{if $fpe.settings.smarty.cache}Activé{else}Désactivé{/if}</td>
              <td>Activé</td>
            </tr>
            <tr class="{if !$fpe.settings.ccc.css}warning{/if}">
              <td>CCC — CSS combiné</td>
              <td>{if $fpe.settings.ccc.css}Activé{else}Désactivé{/if}</td>
              <td>Activé</td>
            </tr>
            <tr class="{if !$fpe.settings.ccc.js}warning{/if}">
              <td>CCC — JS combiné</td>
              <td>{if $fpe.settings.ccc.js}Activé{else}Désactivé{/if}</td>
              <td>Activé</td>
            </tr>
            <tr class="{if !$fpe.settings.layered}warning{/if}">
              <td>Cache navigation à facettes</td>
              <td>{if $fpe.settings.layered}Activé{else}Désactivé{/if}</td>
              <td>Activé</td>
            </tr>
            <tr class="{if !$fpe.settings.cache_control}info{/if}">
              <td>En-têtes HTTP Cache-Control</td>
              <td>{if $fpe.settings.cache_control}Activé{else}Désactivé{/if}</td>
              <td>Activé</td>
            </tr>
            <tr>
              <td>Cache système (parameters.php)</td>
              <td>
                {if $fpe.settings.system.enable}{$fpe.settings.system.system|default:'—'}{else}Désactivé{/if}
              </td>
              <td>Memcached si disponible</td>
            </tr>
          </tbody>
        </table>
      </div>

      {* ── Recommandations ── *}
      <div class="panel">
        <div class="panel-heading"><i class="icon-lightbulb"></i> Recommandations</div>
        <div class="panel-body">
          {foreach from=$fpe.recommendations item=r}
            <div class="alert alert-{if $r.level == 'danger'}danger{elseif $r.level == 'warning'}warning{elseif $r.level == 'success'}success{else}info{/if}" style="padding:8px 12px;">
              {$r.text}
            </div>
          {/foreach}
        </div>
      </div>

      {* ── Poids du tunnel de commande ── *}
      <div class="panel">
        <div class="panel-heading"><i class="icon-shopping-cart"></i> Modules sur le tunnel de commande</div>
        <table class="table">
          <thead><tr><th>Hook</th><th>Modules</th></tr></thead>
          <tbody>
            {foreach from=$fpe.checkout.modules key=hook item=mods}
              <tr>
                <td>{$fpe.checkout.labels[$hook]|escape} <code>{$hook}</code></td>
                <td>
                  {if $mods|@count}
                    {foreach from=$mods item=m}<span class="label label-default">{$m|escape}</span> {/foreach}
                  {else}
                    <span class="text-muted">aucun</span>
                  {/if}
                </td>
              </tr>
            {/foreach}
          </tbody>
        </table>
        <div class="panel-body">
          <p class="text-muted">
            Les modules du hook <code>actionFrontControllerSetMedia</code> chargent du JS/CSS
            sur <strong>toutes</strong> les pages, y compris le tunnel de commande.
            C'est souvent là que se cachent les lourdeurs (SDK PayPal, liste d'envies, partage social…).
          </p>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      {* ── Cache système ── *}
      <div class="panel">
        <div class="panel-heading"><i class="icon-cogs"></i> Cache système</div>
        <div class="panel-body">
          {if !$fpe.settings.system.file_exists}
            <div class="alert alert-danger">app/config/parameters.php introuvable.</div>
          {elseif !$fpe.settings.system.writable}
            <div class="alert alert-warning">app/config/parameters.php n'est pas accessible en écriture. Corrigez ses permissions (644) pour pouvoir modifier le cache système.</div>
          {else}
            <form method="post">
              <div class="form-group">
                <label>Activer le cache système</label>
                <select name="fpe_cache_enable" class="form-control">
                  <option value="0">Non (recommandé sans Memcached/Redis)</option>
                  <option value="1" {if $fpe.settings.system.enable}selected{/if}>Oui</option>
                </select>
              </div>
              <div class="form-group">
                <label>Backend</label>
                <select name="fpe_caching_system" class="form-control">
                  {foreach from=$fpe.backends key=class item=desc}
                    <option value="{$class}" {if $fpe.settings.system.system == $class}selected{/if}>{$desc|escape} ({$class})</option>
                  {/foreach}
                </select>
              </div>
              <button type="submit" name="submitFpeCacheSystem" class="btn btn-primary">
                Enregistrer le cache système
              </button>
            </form>
            <p class="text-muted" style="margin-top:8px;">
              ⚠️ PrestaShop 9 n'a plus de cache « fichiers ». Si le backend choisi
              n'est pas installé sur le serveur (Memcached/Redis/APC), laisser ce
              cache <strong>désactivé</strong> évite les erreurs.
            </p>
          {/if}
        </div>
      </div>

      {* ── Tailles ── *}
      <div class="panel">
        <div class="panel-heading"><i class="icon-folder-open"></i> Tailles des caches</div>
        <table class="table">
          <tbody>
            <tr><td>Bundles CCC du thème</td><td>{$fpe.sizes.theme_cache}</td></tr>
            <tr><td>var/cache</td><td>{$fpe.sizes.var_cache}</td></tr>
          </tbody>
        </table>
      </div>

      {* ── Environnement ── *}
      <div class="panel">
        <div class="panel-heading"><i class="icon-server"></i> Environnement</div>
        <table class="table">
          <tbody>
            <tr><td>PHP</td><td>{$fpe.php_version}</td></tr>
            <tr><td>memory_limit</td><td>{$fpe.memory_limit}</td></tr>
            <tr><td>OPcache</td><td>{if $fpe.opcache}Activé{else}<span class="label label-warning">Désactivé</span>{/if}</td></tr>
            <tr><td>Produits</td><td>{$fpe.volumes.products}</td></tr>
            <tr><td>Catégories</td><td>{$fpe.volumes.categories}</td></tr>
            <tr><td>Modules / Hooks / Accroches</td><td>{$fpe.volumes.modules} / {$fpe.volumes.hooks} / {$fpe.volumes.hook_module}</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
