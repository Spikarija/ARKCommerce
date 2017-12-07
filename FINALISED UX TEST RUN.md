### **Finalised UX Test Run Demonstration**


**A detailed walthrough as if carried out by a WooCommerce online merchant invilving user registration, plugin deployment, and execution of a test payment using sandbox mode that employs DARK Devnet blockchain and its token, DARK. ARK Mainnet blockchain and its token ARK are identical, the only difference being that using them costs a transaction fee, whereas DARK tokens are freely available either through KristjanK's DARK Paper Wallet Generator or ARK Official Slack.**

For the sake ov brevity, omitted are WooCommerce customer emails and ARKCommerce administrator emails, both have been featured in this repo under "testing" and "UI testing" folders.


----------


**Screenshot 1: ARKCommerce Website Home Page**

A merchant and future ARKCommerce user clicks TRY IT. 

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/01%20-%20ARKCommerce%20Website.jpg


----------


**Screenshot 2: ARKCommerce Website, ARKCommerce Access Service**

To register for access, the merchant adds the service to cart.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/02%20-%20ARKCommerce%20Access.jpg


----------


**Screenshot 3: ARKCommerce Website, Shop Cart**

Standard WooCommerce cart view, the merchant proceeds to checkout.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/03%20-%20ARKCommerce%20Access%20Cart.jpg


----------


**Screenshot 4: ARKCommerce Website, Shop Cart Checkout**

The merchant enters his information and places an order.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/04%20-%20ARKCommerce%20Access%20Checkout.jpg


----------


**Screenshot 5: ARKCommerce Website, Order Received**

The merchant sees his order placed, waits for registration email to enter his mailbox.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/05%20-%20ARKCommerce%20Access%20Order%20Received.jpg


----------


**Screenshot 6: ARKCommerce Registration Email**

Using information provided by the merchant at checkout, the system generates a new user and sends credentials to the merchant's mailbox. The email contains a link to download the plugin; when the plugin is approved by WordPress it will be available from within the WordPress administration interface as well as from wordpress.org/plugins website.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/06%20-%20ARKCommerce%20Registration%20Email.jpg


----------


**Screenshot 7: Merchant's WordPress Administration Interface, Section Plugins, Add New, Upload Plugin**

The merchant selects the plugin package downloaded through the link from the registration email as seen in the previous screenshot.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/07%20-%20ARKCommerce%20Add%20Downloaded%20ARKCommerce%20Plugin.jpg


----------


**Screenshot 8: Merchant's WordPress Administration Interface, Section Plugins**

The plugin has successfully installed, the merchant clicks on "Activate".

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/08%20-%20ARKCommerce%20Downloaded%20Plugin%20Installed.jpg


----------


**Screenshot 9: Merchant's WordPress Administration Interface, Section Plugins**

ARKCommerce plugin is now activated and its notification for disabled state appears. The merchant follows the link to plugin Settings.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/09%20-%20ARKCommerce%20Downloaded%20Plugin%20Activated.jpg


----------


**Screenshot 10: Merchant's WordPress Administration Interface, Section WooCommerce, Tab Checkout, Entry ARKCommerce**

The merchant inputs the information received from the registration email, as well as ARK store wallet address. The screenshot displays DARK Mode option checked alongside DARK store wallet address entry; DARK Mode switches the plugin queries to DARK Devnet blockchain (development blockchain of ARK) and is meant for testing purposes (sandbox). We expect it to be tried by most users in order to familiarise themselves with plugin functions at no cost since DARK tokens are available for free.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/10%20-%20ARKCommerce%20Plugin%20Settings%20Input.jpg


----------


**Screenshot 11: Merchant's WordPress Administration Interface, Section WooCommerce, Tab Checkout, Entry ARKCommerce**

The merchant saves changes, in the background, a QR Code of the active store wallet address is generated (when DARK mode is enabled it uses DARK address). Another notification appears, this time addressing "Hard Cron" issue. WordPress by default employs what is referred to as "Soft Cron", a mechanism running scheduled tasks that gets triggered on each page refresh. Since this means that there may be irregular periods of time between refreshes, it is highly recommended to employ "Hard Cron" approach, as shown in the following screenshots.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/11%20-%20ARKCommerce%20Plugin%20Settings%20Saved.jpg


----------


**Screenshot 12: Merchant's WordPress Administration Interface, Section ARKCommerce, Information**

The merchant follows the link from the notification and sees the Information page which contains basic information on ARK, ARKCommerce and a couple of quick guides for proper setup.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/12%20-%20ARKCommerce%20Plugin%20Information.jpg


----------


**Screenshot 13: Merchant's WordPress Administration Interface, Section ARKCommerce, Information; SSH Server Connection**

The merchant connects to their server through FTP, Remote Desktop, or SSH; pictured is SSH session with "wp-config.php" file open. The "define" line is copy/pasted from the Information page and works on all supported platforms by WordPress. The merchant saves the change and it goes into effect immediately.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/13%20-%20ARKCommerce%20Plugin%20WP-Cron%20Edit%20wp-config.php.jpg


----------


**Screenshot 14: Merchant's WordPress Administration Interface, Section ARKCommerce, Information; SSH Server Connection**

The merchant stays with their remote connection to the server through FTP, Remote Desktop, or SSH; pictured is SSH session after "sudo crontab -e" command has been committed. This particular server runs on Linux so that it the proper way to invoke it; the Information page also includes guides included for Windows servers and web interface usually found at web hosting providers. The merchant copy/pastes the required line from the Information page and saves the change, which goes into effect immediately.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/14%20-%20ARKCommerce%20Plugin%20WP-Cron%20Crontab%20Entry.jpg


----------


**Screenshot 15: Merchant's WordPress Administration Interface, Section ARKCommerce, Preferences**

The merchant clicks on Preferences to review available options, perhaps change them to their preferences, in this screenshot the order timeout has been changed from the default value of 225 blocks (cca 30 min) to 450 blocks (cca 60 min). Notice the absence of "Hard Cron" notification; the plugin has already picked up on the change.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/15%20-%20ARKCommerce%20Plugin%20Preferences.jpg


----------


**Screenshot 16: Merchant's Store**

Not pictured here is the process of adding the bundled ARKCommerce widgets to the Sidebar (on the left), which is done the same way any other widget is added. Since the merchant has not touched the store currency, ARKCommerce picked up on it immediately and started synchronising its exchange rate with ARK. The prices reflect that and stay true to WooCommerce original pricing display, here pictured in all possible WooCommerce product combinations. The exchange rate in use by the store is displayed within the Currency Converter widget.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/16%20-%20ARKCommerce%20Plugin%20Test%20Shop.jpg


----------


**Screenshot 17: Merchant's Store, Testing Cart**

The merchant decides to test the plugin's function and adds several products to cart. Aside from ARK prices accompanying fiat prices by the products there is also a notification below fiat total containing the converted ARK total and information about the store's ARK order expiry timeout (this notification can be switched off in Cart page within ARKCommerce Preferences).

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/17%20-%20ARKCommerce%20Plugin%20Test%20Cart.jpg


----------


**Screenshot 18: Merchant's Store, Testing Checkout**

The merchant enters test information, selects ARK Payment as chosen payment gateway and places the order.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/18%20-%20ARKCommerce%20Plugin%20Test%20Checkout.jpg


----------


**Screenshot 19: Merchant's Store, Testing Order Received**

Aside from usual presentation of WooCommerce information, the merchant sees ARKCommerce addition of the QR Code of active store wallet, payment instructions (configurable in ARKCommerce Preferences), and a table containing all necessary information to carry out the transaction: store wallet address, order total in ARK, SmartBridge reference, and order expiry timeout.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/19%20-%20ARKCommerce%20Plugin%20Test%20Order%20Received.jpg


----------


**Screenshot 20: Merchant's WordPress Administration Interface, Dashboard**

The merchant enters his dashboard and sees ARKCommerce widgets which are automatically added (can be removed like any others through "Screen Options" sliding menu); the system already displays "Latest Orders via ARKCommerce" - the very order they have just placed. Please disregard "Latest 10 Transactions" table in this case since DARK address used here is reused from prior testing. A fresh wallet would show no results since it would have no incoming transactions to display.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/20%20-%20ARKCommerce%20Plugin%20WordPress%20Dashboard%20Widgets.jpg


----------


**Screenshot 21: Merchant's WordPress Administration Interface, WooCommerce Section, Orders**

The merchant clicked on the Order link from the widget shown in previous screenshot and sees an addition to his usual view - ARKCommerce Wallet Monitor displaying last 5 incoming transactions to the store wallet address; again, please disregard this data due to this DARK address having been reused. Order notes display ARKCommerce records: the initial note stating the block height, order expiry block height, and order status change; several subsequent record show that blockchain queries produced no results, as expected since we have not yet carried out the payment transaction.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/21%20-%20ARKCommerce%20Plugin%20Test%20WooCommerce%20Order%20Awaiting%20Payment.jpg


----------


**Screenshot 22: ARK Desktop Wallet, Transaction Commit Dialogue**

The transaction contains all copy/pasted information provided at Order Received page, as well as customer email. The transaction is committed by clicking on the "Send" button.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/22%20-%20ARKCommerce%20Plugin%20Test%20ARK%20Payment%20Transaction.jpg


----------


**Screenshot 23: Merchant's WordPress Administration Interface, WooCommerce Section, Orders**

At next blockchain query cycle, ARKCommerce detects the transaction carried out in the previous screenshot. A new entry is added to Order Notes containing the block number where the transaction has been discovered, order status change, and a generated link leading to the ARK or DARK (depending on plugin operation mode) blockchain explorer, a publicly accessible external web application showing data from the blockchain.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/23%20-%20ARKCommerce%20Plugin%20Test%20WooCommerce%20Order%20Payment%20Completed.jpg


----------


**Screenshot 24: Merchant's WordPress Administration Interface, Section ARKCommerce, Navigator**

We conclude with a display of ARKCommerce Navigator page displaying latest 10 incoming transactions of the store wallet and up to 10 latest WooCommerce orders carried our via ARK payment gateway. Each of the entries is hyperlinked in relevant columns and leads either internally to WooCommerce orders or externally to ARK or DARK (depending on plugin operation mode) blockchain explorer web application.

https://github.com/Spikarija/ARKCommerce/blob/master/Finalised%20UX%20Test%20Run/24%20-%20ARKCommerce%20Plugin%20Navigator.jpg

