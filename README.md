# **ARKCommerce**

# Introduction

<p align="center">
    <a href="https://arkcommunity.fund/"><img src="https://arkcommunity.fund/media-kit/funded/banner.png" /></a>
</p>

#### WordPress Payment Gateway for WooCommerce
----------

![WordPress Payment Gateway Plugin for WooCommerce](https://imgur.com/HaXPX4D.jpg)

----------

### **Description**
ARKCommerce is a payment gateway that provides the infrastructure for ARK crypto currency payment services to WooCommerce store operators on WordPress platform and does so without requiring or storing wallet passphrases. All orders placed via ARKCommerce are kept on-hold until the ARK blockchain queries reveal a transaction for an appropriate amount of ARK and a correct order reference making a deposit into the monitored ARK wallet address belonging to the store, after which the order is marked as complete. There is the option to limit the timeframe within which the customer must execute the transaction, or else the order gets cancelled.

ARKCommerce solution leverages the versatility, resilience and quickness of ARK blockchain which features a special field named SmartBridge that allows for inserting text, links, order number reference and so on into the transaction. The blockchain also features 8 second block times allowing for quick transaction confirmations. Fully based on open source code and architecture, ARKCommerce aims to provide the infrastructure with the goal of wider market acceptance for ARK.

----------

## **V1.0 Released**

All plugin files have been published in this repository, as well as on https://wordpress.org/plugins/arkcommerce
The service is free for the foreseeable future, by all means take advantage of it; register on https://www.arkcommerce.net/

## **V1.0.1 Released**

Update to Coinmarketcap.com v2 API
Update to redesigned WooCommerce UI
Compatible with latest WordPress Core 4.9.6
Compatible with latest WooCommerce 3.4.0

----------

## **Mission Objectives**
 - Develop an ARK payment gateway with automatic order processing and reliable transaction validation
 - Make the deployment and configiration easy and rapid via common WordPress and cPanel administration interfaces that are common with web hosting providers
 - Provide a configurable order expiry timeout mechanism
 - Enable both native ARK pricing and automatic conversion from a multitude of supported currencies and offer a dual price display option
 - Employ configurable exchange rates: automatic (market price), multiplier (above-market price), and fixed (static)
 - Support automatic market exchange rate synchronisation for many currencies*
 - Achieve seamless integration into WooCommerce/WordPress with Settings, Preferences, Information and Navigation sections
 - Provide an Admin Dashboard Widget and a WooCommerce Metabox Widget
 - Integrate a QR Code generator for store wallet address
 - Receive support from ARK Community Fund at https://arkcommunityfund.com
 - Get deployed into the official ark.io store at https://shop.ark.io

> *Initially, supported automatic pricing shall be based on coinmarketcap.com: **ARK** – ARK crypto currency, **BTC** – Bitcoin crypto currency, **USD** – US Dollar, **EUR** - Euro, **GBP** – Pound Sterling, **CHF** – Swiss Franc, **CNY** – Chinese Yuan, **JPY** – Japanese Yen, **KRW** – Korean Won, **CAD** – Canadian Dollar, **AUD** – Australian Dollar, **SEK** – Swedish Kroner **NOK** – Norwegian Kroner, **INR** – Indian Rupee, **BRL** – Brazilian Real, **CLP** – Chilean Peso, **CZK** – Czech Koruna, **DKK** – Danish Kroner, **HKD** – Hong Kong Dollar, **HUF** – Hungarian Forint, **IDR** – Indonesian Rupiah, **ILS** – Israeli New Shekel, **MXN** – Mexican Peso, **MYR** – Malaysian Ringgit,, **NZD** – New Zealand Dollar, **PHP** – Phillippine Peso, **PKR** – Pakistani Rupee, **PLN** – Polish Zloty, **RUB** – Russian Ruble, **SGD** – Singaporean Dollar, **THB**- Thai Baht, **TRY** – Turkish Lira, **TWD** – Taiwanese Dollar, **ZAR** – South African Rand

----------

## **Milestones to v1.0**
When the plugin reaches its final form and infrastructure for running it is secure, v1.0 will be published both here on GitHub and WordPress.org for it relies on an ARKCommerce Node that is in intermittent operation and totally inaccessible from WAN at this time.

**The initial development is nearly complete. Currently awaiting the arrival of hardware the system shall run on. As soon as the infrastructure is deployed, secured, and publicly accessible, UA testing shall commence.**

  - [x] Successful initial deployment and automated order processing
 
  - [x] Exchange rate synchronisation automation
 
  - [x] QR Code integration
 
  - [x] UI features implementation
 
  - [x] UX finalisation
 
  - [x] Translation provision
 
  - [x] Testing, code QA and optimisation
 
  - [x] UA testing
 
  - [X] Self-service user registration process implementation
 
  - [x] Website deployment and content creation
 
There will be an initial promotional period of absolutely free service. Subseqent introduction of fees may be necessary in order to cover infrastructure costs as the system relies on the ARKCommerce Node acting as a bridge between merchants and ARK blockchain queries.

----------
## **Current List of External Issues**
 - <s>WooCommerce pricing issue</s>: https://github.com/woocommerce/woocommerce/issues/17581
<s>*remedied in next release (3.2.4)</s> current release includes the fix
 - <s>ARK Desktop Client transaction altering issue</s>: https://github.com/ArkEcosystem/ark-desktop/issues/385
<s>*remedied in next release (1.4.2)</s> current release includes the fix

----------
## **Author**
Spikarija - Milan Semen

----------
## **License**

**MIT License**

Copyright 2017 Milan Semen

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
