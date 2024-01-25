# PrestaShop Crypto Payments

## About

PrestaShop payment module powered by [Apirone][Apirone]

## Description

Accept the most popular cryptocurrencies (BTC, LTC, BCH, Doge, etc.) in your store all around the world. Use any crypto supported by the provider to accept coins using the Forwarding payment process.

**Key features:**

* The payment is automatically forwarded from a temporarily generated crypto-address directly into your wallet (temp address associates payment with an exact order)

* The payment gateway has a fixed fee which does not depend on the amount of the order. Small payments are free. [https://apirone.com/pricing][Pricing]

* White label processing (your online store accepts payments on the store side without redirects, iframes, advertisements, logos, etc.)

* This plugin works well all over the world.

* Tor network support.

## How does it work?

The Buyer adds items into the cart and prepares the order. Using API requests, the store generates crypto (BTC, LTC, BCH, Doge) addresses for payment and shows a QR code. Then, the buyer scans the QR code and pays for the order. This transaction goes to the blockchain. The payment gateway immediately notifies the store about the payment. The store completes the transaction.

## Requirements & License

Prestashop 1.7.x, 8.x

This module is released under the [MIT][MIT] license

## Third Party API & License Information

* **API website:** [https://apirone.com][Apirone]
* **API docs:** [https://apirone.com/docs/][Docs]
* **Privacy policy:** [https://apirone.com/privacy-policy][Privacy]
* **Support:** <support@apirone.com>

## Frequently Asked Questions

**I will get money in USD, EUR, CAD, JPY, RUR...?**

> No, you will get crypto only. You can enter the crypto address of your trading platform account and convert crypto (BTC, LTC, BCH, Doge) to fiat money at any time.

**How can The Store cancel orders and return bitcoins?**

> This process is fully manual because you will get all payments to your specified wallet. Only you control your money. Contact the Customer, ask for an address and finish the deal. Bitcoin protocol has no refunds, chargebacks, or transaction cancellations. Only the store manager makes decisions on underpaid or overpaid orders whether to cancel the order or return the rest directly to the customers.

**I would like to accept Litecoin only. What should I do?**

> Just enter your LTC address on settings and keep other fields empty.

**Fee:**

>The plugin uses the free Rest API of the Apirone crypto payment gateway. The pricing page [https://apirone.com/pricing][Pricing]

[Pricing]: https://apirone.com/pricing
[Apirone]: https://apirone.com
[Docs]: https://apirone.com/docs
[Privacy]: https://apirone.com/privacy-policy
[MIT]: https://opensource.org/license/mit/

## Changelog

### Version 1.0.0 ###

* Plugin first version is released.
