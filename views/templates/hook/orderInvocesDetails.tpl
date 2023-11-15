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
<div class="card mt-2" id="view_order_payments_block">
    <div class="card-header">
        <h3 class="card-header-title">Apirone Invoices</h3>
    </div>
    <div class="card-body">

    <table class="table" data-role="payments-grid-table">
        <thead>
            <tr>
            <th class="table-head-date">Date</th>
            <th class="table-head-invoice">Invoice</th>
            <th class="table-head-payment">Address</th>
            <th class="table-head-amount">Amount</th>
            <th class="table-head-transaction">Status</th>
            <th></th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$invoices item=details}
            <tr>
                <td data-role="date-column">{$details->date}</td>
                <td data-role="transaction-id-column">{$details->invoice}</td>
                <td data-role="payment-method-column"><a href="{$details->addressUrl}" target="_blank">{$details->address}</a></td>
                <td data-role="amount-column">{$details->amount}</td>
                <td data-role="transaction-id-column">{$details->status}</td>
                <td class="text-right">
                    <button class="btn btn-sm btn-outline-secondary js-payment-details-btn">{l s='Details' d='Admin.Global'}</button>
                </td>
            </tr>
            <tr class="d-none" data-role="payment-details">
                <td colspan="5">
                    {foreach from=$details->history item=item}
                    <p class="mb-0">
                        {$item->date}
                        {$item->status}
                        {if property_exists($item, 'amount')}
                            <a href="{$item->txid}">{$item->amount}</a>
                        {/if}
                    {/foreach}
                </td>
            </tr>
            {/foreach}
        </tbody>
        </table>
    </div>
</div>
