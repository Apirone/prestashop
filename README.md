# Apirone Crypto Payments for PrestaShop

## About

PrestaShop payment module powered by [Apirone]

## Description

Accept the most popular cryptocurrencies (BTC, LTC, BCH, Doge, etc.) in your store all around the world. Use any crypto supported by the provider to accept coins using the Forwarding payment process.

**Key features:**

* Payments are automatically forwarded from a temporarily generated crypto-address directly into your wallet (the temporary address associates the payment with an exact order).

* The payment gateway charges either a fixed fee which does not depend on the amount of the order or a percentage fee in the amount of 1% of the transfer. Small payments are totally free of service fees. See about fee plans on [https://apirone.com](https://apirone.com)

* You do not need to complete a KYC/Documentation to start using our plugin. Just fill in settings and start your business.

* White label processing (your online store accepts payments on the store side without redirects, iframes, advertisements, logo, etc.).

* This plugin works well all over the world.

* Tor network support.

## Installation

1. Download the build apirone.vX.X.X.zip from [Releases]
2. Go to **Modules** » **Module manager** and upload a plugin file with **Upload a module** button.
3. Click the **Configure** button.
4. Enter your **cryptocurrency addresses** for desired cryptos.
5. In the **Currencies** section of the plugin settings for currencies that have tokens and a valid address filled, check the check-boxes for the main currency of the network or any token.

In total to make **Pay with crypto** method available to customers those minimal settings must be set:
- Apirone plugin must not be disabled.
- For one or more currencies a valid address must be set.
- If a valid address is specified only for currencies with tokens, then a minimum one check-box for the main currency of the network or any token must be checked.

## Upgrade

1. Without deleting the old plugin version, upgrade using the same steps as in **Installation** section above.
2. All values should be from the previous version.
3. In the **Currencies** section of the plugin settings for currencies that have tokens and a valid address filled, check the check-boxes for the main currency of the network or any token.

## How does it work?

The Buyer adds items into the cart and prepares the order. Using API requests, the store generates crypto (BTC, LTC, BCH, Doge) addresses for payment and shows a QR code. Then, the buyer scans the QR code and pays for the order. This transaction goes to the blockchain. The payment gateway immediately notifies the store about the payment. The store completes the transaction.

## Requirements & License

PrestaShop 1.7.x, 8.x

Since version 2.0.0 the plugin has been based on [Apirone SDK PHP](https://github.com/Apirone/apirone-sdk-php) that works on PHP v.7.4+. So the minimum PHP version is 7.4. PHP v.8.0+ is recommended.

This module is released under the [MIT] license

## Third Party API & License Information

* **API website:** [Apirone]
* **API docs:** [Docs]
* **Privacy policy:** [Privacy]
* **Support:** <support@apirone.com>

## Frequently Asked Questions

**I will get money in USD, EUR, CAD, JPY, RUR...?**

> No, you will get crypto only. You can enter the crypto address of your trading platform account and convert crypto (BTC, LTC, BCH, Doge) to fiat money at any time.

**How can The Store cancel orders and return bitcoins?**

> This process is fully manual because you will get all payments to your specified wallet. Only you control your money. Contact the Customer, ask for an address and finish the deal. Bitcoin protocol has no refunds, chargebacks, or transaction cancellations. Only the store manager makes decisions on underpaid or overpaid orders whether to cancel the order or return the rest directly to the customers.

**I would like to accept Litecoin only. What should I do?**

> Just enter your LTC address on settings and keep other fields empty.

**Fee:**

>The plugin uses the free Rest API of the Apirone crypto payment gateway. The pricing page [Pricing]

[Pricing]: https://apirone.com/pricing
[Apirone]: https://apirone.com
[Docs]: https://apirone.com/docs
[Privacy]: https://apirone.com/privacy-policy
[MIT]: https://opensource.org/license/mit/
[Releases]: https://github.com/Apirone/prestashop/releases

## Changelog

See `CHANGELOG.md`
