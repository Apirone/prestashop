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
{$message}
{$settings}
{$currencies}
<div class="panel">
    <div class="panel-heading">
	    <i class="icon-info-circle"></i> Tips & Info
	</div>
    <div class="panel-body">
        <div>
            <h2>{l s='How to use testnet' mod='apirone'}</h2>
            <p>{l s='Please do not spend real crypto if you want to test the plugin. Testnet is free and such coins will not be stuck in case the network fee will be too high. Free faucets to get testnet coins are as follows:' mod="apirone"}</p>
            <a target="_blank" href="https://coinfaucet.eu/en/btc-testnet/?lid=apirone">Coinfaucet</a><br>
            <a target="_blank" href="https://bitcoinfaucet.uo1.net?lid=apirone">Bitcoinfaucet</a><br>
            <a target="_blank" href="https://testnet-faucet.com/btc-testnet/?lid=apirone">Testnet faucet</a><br>
            <a target="_blank" href="https://kuttler.eu/en/bitcoin/btc/faucet/?lid=apirone">Kuttler</a>
            <hr>
            <p><strong>{l s='Read more:' mod='apirone'}</strong> <a href="https://apirone.com/faq" target="_blank">https://apirone.com/faq</a></p>
        </div>
        <div>
            <h2>{l s='Plugin info' mod='apirone'}</h2>
            <p>
                <strong>{l s='Apirone account:' mod='apirone'}</strong> {$apirone_account}<br/>
                <strong>{l s='Plugin version:' mod='apirone'}</strong> {$plugin_version}<br/>
                <strong>{l s='PHP version:' mod='apirone'}</strong> {$php_version}<br/>
                <strong>{l s='PrestaShop version' mod='apirone'}</strong>: {$ps_version}<br/>
            </p>
            <hr>
            <p><strong>{l s='Apirone support:' mod='apirone'}</strong> <a href="mailto:support@apirone.com">support@apirone.com</a></p>
        </div>
    </div>
    <div class="panel-footer">
        <h4>{l s='Please <a href="https://www.smartsurvey.co.uk/s/2N0R8O/" target="_blank">fill-up</a> our survey to improve the PrestaShop plugin. The results will help us shape a road map for the future.' mod='apirone'}</h4>
    </div>
</div>