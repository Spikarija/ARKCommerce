###**Test Run Commentary**


----------


**Screenshot 1: Customer Shop Page Display**
The customer sees everything priced natively in ARK (custom currency+symbol, 8 decimals) 
https://github.com/Spikarija/ARKCommerce/blob/master/testing/01%20-%20customer%20shop.png


----------


**Screenshot 2: Customer Product Page Display**
Same as above
https://github.com/Spikarija/ARKCommerce/blob/master/testing/02%20-%20customer%20product.png


----------


**Screenshot 3: Customer Cart Page Display**
The customer sees a notice (in blue - theme dependent) containing the order expiry timeout within which they must carry out the transaction (timeout is adjustable by shop operator)
https://github.com/Spikarija/ARKCommerce/blob/master/testing/03%20-%20customer%20cart.png


----------


**Screenshot 4: Customer Cart Checkout Page Display**
The customer sees a notice (in blue - theme dependent) containing the order expiry timeout within which they must carry out the transaction (timeout is adjustible by shop operator) and a radio button to choose ARK payment gateway (title customisable by shop operator) and - if chosen - the description (text customisable by shop operator)
https://github.com/Spikarija/ARKCommerce/blob/master/testing/04%20-%20customer%20cart%20checkout.png


----------


**Screenshot 5: Customer "Order Received" Page Display**
The customer sees the "thank you" note and additional information provided by the plugin from first to third horizontal breaks; the information includes a QR Code of the store ARK wallet address, beside it customer instructions (text customisable by shop operator), and below a table containing transaction information (ARK Wallet Address, SmartBridge order reference and Order Expiry)
https://github.com/Spikarija/ARKCommerce/blob/master/testing/05%20-%20customer%20order%20placed.png


----------


**Screenshot 6: WooCommerce Customer Order Placed Mail Display**
Standard mail that the customer receives after placing the order containing additional information provided by the plugin from first to third horizontal breaks; the information includes a QR Code (absent due to local host deployment) of the store ARK wallet address, beside it customer instructions (text customisable by shop operator), and below a table containing transaction information (ARK Wallet Address, SmartBridge order reference and Order Expiry)
https://github.com/Spikarija/ARKCommerce/blob/master/testing/06%20-%20customer%20woocommerce%20order%20mail.png


----------


**Screenshot 7: WooCommerce Store Operator Order Received Mail Display**
Standard mail that the store operator receives upon new order placement; no customisation necessary
https://github.com/Spikarija/ARKCommerce/blob/master/testing/07%20-%20administrator%20woocommerce%20order%20mail.png


----------


**Screenshot 8: Collage of Customer's ARK Desktop Client Display**
Starting clockwise from upper left:

 - the customer copy/pastes the information received (recipient address=store ARK wallet address, the amount to be sent, the SmartBridge order reference, and own passphrase/private key)
 - the customer reviews the transaction before committing it
 - the customer has committed the transaction

https://github.com/Spikarija/ARKCommerce/blob/master/testing/08%20-%20customer%20ARK%20client.png


----------


**Screenshot 9: WooCommerce Store Order View Page Display**
Aside from usual data contained in each order, the plugin adds several metadata entries and provides notes to shop operator (shown on the right) starting from bottom up:

 - initial note created on order placement containing order status, successful scheduling of a transaction check, block height of ARK blockchain at the time the order was placed, and block height at which the order gets cancelled in case there are no transactions filling it until then
 - note containing information of an unsuccessful transaction check run where querying the blockchain produced no hits, successful scheduling of the next transaction check, and block height at the time the check was executed
 - note containing information of a successful transaction check, the block containing the transaction, a URL to display the transaction in the ARK Explorer web app, and order status change

https://github.com/Spikarija/ARKCommerce/blob/master/testing/09%20-%20administrator%20woocommerce%20order.png


----------


**Screenshot 10: WooCommerce Customer Order Complete Mail Display**
Standard mail that the customer receives upon order completion; no customisation necessary
https://github.com/Spikarija/ARKCommerce/blob/master/testing/10%20-%20customer%20woocommerce%20order%20completion%20mail.png


----------


**Screenshot 11: ARK Explorer Web App Transaction Display**
The link provided above in Screenshot 9 leads here
https://github.com/Spikarija/ARKCommerce/blob/master/testing/11%20-%20explorer%20app%20transaction.png