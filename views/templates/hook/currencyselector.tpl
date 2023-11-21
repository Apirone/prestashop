{**
 * 2017-2023 apirone.com
 *
 * NOTICE OF LICENSE
 *
 * This source file licensed under the MIT license 
 * that is bundled with this package in the file LICENSE.txt.
 * 
 * @author    Apirone OÜ <support@apirone.com>
 * @copyright 2017-2023 Apirone OÜ
 * @license   https://opensource.org/license/mit/ MIT License
 *}
<div class="row">
	<div class="col-xs-12 col-md-6 pl-2 ml-1 mb-2">
        <form action="{$action}" id="payment-form">
        {if count($coins) == 1}
            <span class="form-control form-control-input">{$coins.0->name}: {$coins.0->amount}</span>
            <input type="hidden" value="{$coins.0->abbr}" name="coin" required>
            <input type="hidden" name="tst" required>
        {else}
            <select class="form-control form-control-select" name="coin" required>
            {foreach $coins as $coin}
                <option value="{$coin->abbr}">{$coin->name}: {$coin->amount}</option>
            {/foreach}
            </select>
        {/if}
        </form>
	</div>
</div>
