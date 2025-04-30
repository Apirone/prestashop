<div class="coins-wrapper">
{foreach from=$coins item=coin}
    {assign var="coin_active" value="{$coin->abbr}_active"}
    <div class="coin-block">
        <label for="{$coin->abbr}_active">
        <i class="icon-coin {$coin->abbr|replace:'@':'_'}"></i>
        <span>{strtoupper($coin->abbr)}</span>
        <input class="active-coin" type="checkbox" name="{$coin->abbr}_active" id="{$coin->abbr}_active" {(isset($values[$coin_active]) && $values[$coin_active] == 'on') ? 'checked' : '' } />
        </label>
    </div>
{/foreach}
</div>