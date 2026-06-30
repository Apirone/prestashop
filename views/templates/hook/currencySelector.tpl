{**
 * 2026 apirone.com
 *
 * NOTICE OF LICENSE
 *
 * This source file licensed under the MIT license
 * that is bundled with this package in the file LICENSE.txt.
 *
 * @author    Apirone OÜ <support@apirone.com>
 * @copyright 2026 Apirone OÜ
 * @license   https://opensource.org/license/mit/ MIT License
 *}
<div class="row">
    <div class="col-xs-12 col-md-12 pl-2 ml-1 mb-2">
        <form action="{$action|escape}" id="payment-form">
        <link href="{$urls.base_url}modules/apirone/views/css/coins.min.css" rel="stylesheet">
        <div id="apirone_mccp_dropdown">
            <input type="hidden" value="{$coin_first|escape}" name="coin">
            <button type="button" onclick="mccpDropdownToggle(event)" onblur="mccpDropdownBlur(event)">
                <div></div>
                <i class="material-icons">expand_more</i>
            </button>
            <div class="drop-list" style="display:none"><ul>
                {foreach $coins as $coin}
                <li><button type="button" onclick="mccpDropdownSelect(event, '{$coin->abbr|escape:'javascript':'UTF-8'}')" onblur="mccpDropdownBlur(event)">
                    <div class="coin-icon">
                        <img width="32" height="32" src="{$urls.base_url}modules/apirone/views/img/currencies/{($coin->token) ? $coin->token : $coin->network|escape:'url'}.svg" onerror="this.onerror=null;this.src='{$urls.base_url}modules/apirone/views/img/currencies/placeholder.svg'">
                        {if ($coin->token)}
                        <img width="20" class="coin-small" src="{$urls.base_url}modules/apirone/views/img/currencies/{$coin->network|escape:'url'}.svg" onerror="this.onerror=null;this.src='{$urls.base_url}modules/apirone/views/img/currencies/placeholder.svg'">
                        {/if}
                    </div>
                    {if (property_exists($coin, 'withFee') && $coin->withFee)}
                    <div class="coin-text">
                        <span class="coin-alias">{$coin->alias|escape:'htmlall':'UTF-8'}</span>
                        <span class="with-fee">{$coin->withFee|escape:'htmlall':'UTF-8'}</span>
                    </div>
                    {else}
                        <span class="coin-alias">{$coin->alias|escape:'htmlall':'UTF-8'}</span>
                    {/if}
                </button></li>
                {/foreach}
            </ul></div>
        </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    window.addEventListener('load', mccpDropdownLoaded);

    function mccpDropdownLoaded() {
        $('#apirone_mccp_dropdown>button>div').html($('#apirone_mccp_dropdown ul li:first-child button').html());
    }
    function mccpDropdownShow() {
        $('#apirone_mccp_dropdown>button>i').html('expand_less');
        $('#apirone_mccp_dropdown>.drop-list').removeAttr('style');
    }
    function mccpDropdownHide() {
        $('#apirone_mccp_dropdown>button>i').html('expand_more');
        $('#apirone_mccp_dropdown>.drop-list').css('display', 'none');
    }
    function mccpDropdownToggle(event) {
        event.preventDefault();
        if ($('#apirone_mccp_dropdown>button>i').html() == 'expand_more')
            mccpDropdownShow();
        else
            mccpDropdownHide();
    }
    function mccpDropdownSelect(event, coin) {
        event.preventDefault();

        const button = event.target.closest('button')
        if (button) {
            $('#apirone_mccp_dropdown>button>div').html(button.innerHTML);
            $('#apirone_mccp_dropdown>input').val(coin);
        }
        mccpDropdownHide();
        $('#apirone_mccp_dropdown>button').focus();
    }
    function mccpDropdownBlur(event) {
        if (event.relatedTarget?.closest('#apirone_mccp_dropdown button')) return;
        event.preventDefault();
        mccpDropdownHide();
    }
</script>
