{if (isset($status) == true) && ($status == 'ok')}
<h3>{l s='Your order on %s is complete.' sprintf=$shop_name mod='apirone'}</h3>
<p>
	<br />- {l s='Amount' mod='apirone'} : <span class="price"><strong>{$total|escape:'htmlall':'UTF-8'}</strong></span>
	<br />- {l s='Reference' mod='apirone'} : <span class="reference"><strong>{$reference|escape:'html':'UTF-8'}</strong></span>
	<br /><br />{l s='An email has been sent with this information.' mod='apirone'}
	<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='apirone'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='apirone'}</a>
</p>
{else}
<h3>{l s='Your order on %s has not been accepted.' sprintf=$shop_name mod='apirone'}</h3>
<p>
	<br />- {l s='Reference' mod='apirone'} <span class="reference"> <strong>{$reference|escape:'html':'UTF-8'}</strong></span>
	<br /><br />{l s='Please, try to order again.' mod='apirone'}
	<br /><br />{l s='If you have questions, comments or concerns, please contact our' mod='apirone'} <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">{l s='expert customer support team.' mod='apirone'}</a>
</p>
{/if}
<hr />
