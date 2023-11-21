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
<!doctype html>
<html lang="">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    {block name='head_seo'}
      <title>{block name='head_seo_title'}{/block}</title>
      <meta name="description" content="{block name='head_seo_description'}{/block}">
      <meta name="keywords" content="{block name='head_seo_keywords'}{/block}">
    {/block}
    <meta name="viewport" content="width=device-width, initial-scale=1">
   {block name='head_icons'}
     <link rel="icon" type="image/vnd.microsoft.icon" href="{$shop.favicon}?{$shop.favicon_update_time}">
     <link rel="shortcut icon" type="image/x-icon" href="{$shop.favicon}?{$shop.favicon_update_time}">
   {/block}

    <script src="/modules/apirone/views/js/front.js" type="text/javascript"></script>
    <link href="/modules/apirone/views/css/front.css" rel="stylesheet">

  </head>

  <body style="margin: 0;">
    {block name='invoice'}
    {/block}
  </body>

</html>
