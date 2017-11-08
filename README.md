# **ARKCommerce**
#### WordPress Payment Gateway for WooCommerce
----------

![WordPress Payment Gateway Plugin for WooCommerce](https://imgur.com/HaXPX4D.jpg)

----------

### **Description**
ARKCommerce is a payment gateway that provides the infrastructure for ARK crypto currency payment services to WooCommerce store operators on WordPress platform and does so without requiring or storing wallet passphrases. All orders placed via ARKCommerce are kept on-hold until the ARK blockchain queries reveal a transaction for an appropriate amount of ARK and a correct order reference making a deposit into the monitored ARK wallet address belonging to the store, after which the order is marked as complete. There is the option to limit the timeframe within which the customer must execute the transaction, or else the order gets cancelled.

ARKCommerce solution leverages the versatility, resilience and quickness of ARK blockchain which features a special field named SmartBridge that allows for inserting text, links, order number reference and so on into the transaction. The blockchain also features 8 second block times allowing for quick transaction confirmations. Fully based on open source code and architecture, ARKCommerce aims to provide the infrastructure with the goal of wider market acceptance for ARK.

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

## **Milestones to v1.0.0**
When the plugin reaches its final form and infrastructure for running it is secure, v1.0.0 will be published both here on GitHub and WordPress.org for it relies on an ARKCommerce Node that is in intermittent operation and totally inaccessible from WAN at this time.

  - [x] Successful initial deployment and automated order processing
 
  - [x] Exchange rate synchronisation automation
 
  - [x] QR Code integration
 
  - [ ] UI features implementation
 
  - [ ] UX finalisation
 
  - [x] Translation provision
 
  - [ ] Testing, code QA and optimisation
 
  - [ ] UA testing
 
  - [ ] Self-service API key registration process implementation
 
  - [ ] Website deployment and content creation
 
There will be an initial promotional period of absolutely free service. Subseqent introduction of fees may be necessary in order to cover infrastructure costs as the system relies on the ARKCommerce Node acting as a bridge between merchants and ARK blockchain queries.

----------
## **Current List of External Issues**
 - <s>WooCommerce pricing issue</s>: https://github.com/woocommerce/woocommerce/issues/17581
*remedied in next release (3.2.4)
 - <s>ARK Desktop Client transaction altering issue</s>: https://github.com/ArkEcosystem/ark-desktop/issues/385
*remedied in next release (1.4.2)

----------
## **Author**
Spikarija - Milan Semen

----------
## **License**

**GNU General Public License v3.0**

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details: https://www.gnu.org/licenses/gpl-3.0.html
