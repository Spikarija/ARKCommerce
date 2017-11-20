### **UI Testing Commentary**


----------


Quite a bit has been done both on the plugin's front end and back end, let's start with administration/store operator part.


----------


**Screenshot 1: WordPress Plugins Section View**

Standard description, links and metalinks found in WordPress Plugins Section 

https://github.com/Spikarija/ARKCommerce/blob/master/UI%20testing/01%20-%20WordPress%20Plugins%20Section%20View.png


----------


**Screenshot 2: WordPress Cron Scheduled Tasks View**

Displayed using a plugin; top two entries are created, scheduled and start running upon plugin activation: arkcommerce_validation_worker checks the database for open orders made via ARKCommerce and does so every minute; arkcommerce_update_exchange_rate updates the exchange rate for whatever default store currency is chosen (if supported) and does so every two minutes; in case the chosen currency is unsupported by coinmarketcap.com the plugin outputs a warning about admin having to set a fixed exchange rate. Notice that WP-Cron is disabled; standard operation triggers lookups for scheduled tasks upon page loads, which is not reliable, therefore it is imperative to use crontab to periodically trigger execution but more on that later.

https://github.com/Spikarija/ARKCommerce/blob/master/UI%20testing/02%20-%20WordPress%20Cron%20Scheduled%20Tasks%20View.png


----------


**Screenshot 3: WooCommerce Payment Gateway Settings View**

Standard WooCommerce Settings form containing the bare minimum of information that the plugin needs to function. The security still has not been thoroughly dealt with, however that mostly lies on the ARKCommerce Node side of things. On 'Save Changes' action a QR code generator is called and it creates a QR code of the store wallet address.

https://github.com/Spikarija/ARKCommerce/blob/master/UI%20testing/03%20-%20WooCommerce%20Payment%20Gateway%20Settings%20View.png


----------


**Screenshot 4: WordPress Admin Dashboard View**

There are two simple widgets supplied by the plugin: on the left is ARKCommerce Manual TX Check that serves a singular purpose of store operator manually checking for transaction IDs and getting directly to (d)arkexplorer.ark.io URL displaying the transaction data. Use for it might be in case automatic transaction discovery and order processing fail.

On the right is ARKCommerce Status widget that displays ARKCommerce Node status, current block height at the time of page load, ARK market price in USD/EUR/BTC, a link to the store's chosen (D)ARK wallet address, its balance, latest 10 incoming ARK transactions into it, and latest 10 orders made via ARKCommerce.

https://github.com/Spikarija/ARKCommerce/blob/master/UI%20testing/04%20-%20WordPress%20Admin%20Dashboard%20View.png


----------


**Screenshot 5: ARKCommerce Preferences Section View**

This page contains everything that couldn't fit onto the Settings tab within WooCommerce Settings interface;

 - Exchange Rate Settings cover every allowable combination (availability of options depends on chosen default store currency) and offer three modes: automatic with multiplication which sets an above-market rate; automatic is same as market rate; fixed is static, entirely up to user to define, and the only available option for fiat currencies unsupported by coinmarketcap.com.
 
 - Order Expiry defines the timeframe within which the customer must fulfil the order payment; settings are counted in blocks and range from roughly 15 minutes to never.
  
 - Customer Information includes every piece of text facing the customer: gateway title, description, and email instructions. All of these belong in the plugin's text domain and honor the chosen language upon first activation (if available). Dual price display is self-explanatory, so is ARK info inclusion in shopping cart view.
 
 - Email notifications are an additional means of informing the store operator for every ARKCommerce event, contents of which will be displayed below.
 
  - 'DARK Mode' Settings allow a switch to DARK Testnet backend and generates a QR code for the supplied DARK Address upon saving changes; meant as a testing tool for those interested in seeing the system work without having to spend precious ARK doing it.
  
https://github.com/Spikarija/ARKCommerce/blob/master/UI%20testing/05%20-%20ARKCommerce%20Preferences%20Section%20View.png


----------


**Screenshot 6: ARKCommerce Navigator Section View**

This section is meant to serve as an overview of ARK-related store happening and it includes latest 10 transactions details and latest 10 orders, all of them linked to even more detailed views such as (d)explorer links and WooCommerce orders.

https://github.com/Spikarija/ARKCommerce/blob/master/UI%20testing/06%20-%20ARKCommerce%20Navigator%20Section%20View.png


----------


**Screenshot 7: ARKCommerce Information Section View**

Aside from short descriptions of both ARK and ARKCommerce, this section includes quick guides on how to accomplish several important tasks concerning store setup. They are adapted to the general constraints most hosting providers have with their users, as well as the most popular management interfaces. May include screenshots in the future either within the plugin itself or on the dedicated web page. At the bottom are numerous useful links that may grow in number and scope too.

https://github.com/Spikarija/ARKCommerce/blob/master/UI%20testing/07%20-%20ARKCommerce%20Information%20Section%20View.png


----------


**Screenshot 8: ARKCommerce Server Section View**

This section is available only in service provider edition that includes user management functionalities and tinkering with different backends; still a WiP with regards to backend security, automation and entry validation/sanitization.

https://github.com/Spikarija/ARKCommerce/blob/master/UI%20testing/08%20-%20ARKCommerce%20Server%20Section%20View.png


----------


**Screenshot 9: WooCommerce Order Section View 1**

The WooCommerce Order view now incluedes a simple meta-box including ARKCommerce Node availability status and latest 5 incoming transactions into store (D)ARK wallet address. The notes made by the plugin are available on the right, starting with the oldest entry at the bottom. Custom Fields include all ARK-related order metadata. This particular order was successfully filled and completed.

https://github.com/Spikarija/ARKCommerce/blob/master/UI%20testing/09%20-%20WooCommerce%20Order%20Section%20View%201.png


----------


**Screenshot 10: WooCommerce Order Section View 2**

The WooCommerce Order view the same as above with this particular order having expired due to no payment within the specified timeframe/expiry block height. Custom Fields are displayed here and they contain the abovementioned ARK-related order metadata.

https://github.com/Spikarija/ARKCommerce/blob/master/UI%20testing/10%20-%20WooCommerce%20Order%20Section%20View%202.png


----------


**Screenshot 11: ARKCommerce Admin Order Placed Notification Mail View**

A standardised mail template that (if enabled) notifies the admin/store operator of order placement and supplies ARK-centric metadata.

https://github.com/Spikarija/ARKCommerce/blob/master/UI%20testing/11%20-%20ARKCommerce%20Admin%20Order%20Placed%20Notification%20Mail%20View.png


----------


**Screenshot 12: ARKCommerce Admin Order Filled Notification Mail View**

Same as above, only for filled orders with an embedded transaction button leading to a (d)explorer URL of the transaction filling it.

https://github.com/Spikarija/ARKCommerce/blob/master/UI%20testing/12%20-%20ARKCommerce%20Admin%20Order%20Filled%20Notification%20Mail%20View%20-%20Copy.png

----------


**Screenshot 13: ARKCommerce Admin Order Expired Notification Mail View**

Same as above, only for expired orders.

https://github.com/Spikarija/ARKCommerce/blob/master/UI%20testing/13%20-%20ARKCommerce%20Admin%20Order%20Expired%20Notification%20Mail%20View.png


----------


**Screenshot 14: ARK Explorer Order TX View**

Meant to serve as a target from the Dashboard widget in order to examine transactions that don't show up in both automatic order processing and Navigator; the store operator can see all necessary information concerning the tx and click his way to the block number (height) at the time tx was made. 

https://github.com/Spikarija/ARKCommerce/blob/master/UI%20testing/14%20-%20ARK%20Explorer%20Order%20TX%20View.png


----------


We conclude with an update for the customer side of things; Shop/Product/Cart/Order have all been already covered in earlier test run.


----------


**Screenshot 15: Store View - ARKCommerce FAQ and Converter Widgets**

Both employ client-side execution to serve users without the need to refresh a given page; the converter allows customers to see the store's current fiat-ARK exchange rate at the time of page load and do conversions off of that information, whereas FAQ widget offers answers to 5 likely questions customers may have regarding ARK and ARKCommerce. Either can be placed wherever the store operator chooses to since both are found within the standard Appearance->Widgets WordPress Section.

Notice the original fiat prices here displayed with comma decimal separator, as is common among the majority of Europeans (point had been used in previous tests); the plugin handles both without issues but always displays ARK with a point decimal separator since this has become the de facto norm among all crypto currencies.

https://github.com/Spikarija/ARKCommerce/blob/master/UI%20testing/15%20-%20Store%20View%20-%20Both%20FAQ%20and%20Converter%20Widgets.png


----------

