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
{extends file='module:apirone/views/templates/front/layout.tpl'}

{block name='invoice'}

{$invoice|cleanHtml nofilter}

{/block}