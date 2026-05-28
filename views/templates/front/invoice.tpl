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
<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <script>
            window.invoice_app_config = {
                service_url: '{$service_url}',
                invoices_ep: 'invoices?id=%s',
                images_relative_path: '{$urls.base_url}modules/apirone/views/img',
                {$invoice_app_config}
            };
        </script>
        <script type="module" crossorigin src="{$urls.base_url}modules/apirone/views/js/script.min.js"></script>
        <link rel="stylesheet" crossorigin href="{$urls.base_url}modules/apirone/views/css/style.min.css">
    </head>
    <body>
        <div id="app"></div>
    </body>
</html>
