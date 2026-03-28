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
