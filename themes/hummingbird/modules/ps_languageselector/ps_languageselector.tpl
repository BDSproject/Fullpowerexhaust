{**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *}

<div id="_desktop_ps_languageselector">
  <div class="ps-languageselector">
    <select class="form-select js-language-selector" aria-label="{l s='Change language' d='Shop.Theme.Global'}">
      {foreach from=$languages item=language}
        <option value="{url entity='language' id=$language.id_lang}"{if $language.id_lang == $current_language.id_lang} selected="selected"{/if} data-iso-code="{$language.iso_code}">
          {if $language.iso_code == 'en'}🇬🇧 {elseif $language.iso_code == 'fr'}🇫🇷 {elseif $language.iso_code == 'es'}🇪🇸 {elseif $language.iso_code == 'it'}🇮🇹 {/if}
          {$language.name_simple}
        </option>
      {/foreach}
    </select>
  </div>
</div>
