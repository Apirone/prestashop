{**
 * 2017-2023 apirone.com
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Apirone OÜ <support@apirone.com>
 * @copyright 2017-2023 Apirone OÜ
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}
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
