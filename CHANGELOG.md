### Version 2.0.0

* Now the plugin source code is based on [Apirone SDK PHP library 2.0](https://github.com/Apirone/apirone-sdk-php).
* The “**Invoice** application” is a separate SPA now. This means invoice rendering occurs client-side. This SPA is also a part of the SDK, but can be accessed as an [independent application](https://github.com/Apirone/invoice-app).
* The "**Include fees**" option was added to the main **Settings** tab. It adds service and network fees to the total. The final amount per coin in fiat will be shown to the customer.
* The currency selector now has an image for every currency. If fees are not included in the total amount, the text for a currency contains only its name. If included, the total amount in fiat (plus the fees), is added to the text.
* Support for TON coin & USDT stable coin on TON added

### Version 1.0.4

* Added BNB coin, USDT and USDC stable coins on Binance smart chain
* The SDK updated to 1.2.9

### Version 1.0.3

* SDK updated
* Refactoring the use of deprecated SDK methods
* Settings storage updated
* Added Ethereum network & tokens

### Version 1.0.2

* Enabled display of test networks for all (uses * as a wildcard)
* Updated Makefile & files mode
* Relative path bug fixed
* Minor & misprint fixes
* Vendor update

### Version 1.0.1

* Vendor update

### Version 1.0.0

* Plugin first version is released.
