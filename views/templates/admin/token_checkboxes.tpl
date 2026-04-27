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
<div class="coins-wrapper">
{foreach from=$coins key=abbr item=coin}
    <div class="coin-block">
        <label class="control-label" for="{$coin->checkbox_id}">
        <input class="active-coin" type="checkbox" name="visible[{$abbr}]" id="{$coin->checkbox_id}" {($coin->state) ? 'checked' : ''} />
        {$coin->icon}
        <span class="label-tooltip" data-toggle="tooltip" data-html="true" data-original-title="{$coin->tooltip}">{$coin->name}</span>
        </label>
    </div>
{/foreach}
</div>
