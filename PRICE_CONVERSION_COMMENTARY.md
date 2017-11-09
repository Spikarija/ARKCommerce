### **Price Conversion Commentary**


----------


The exchange rate is sourced from coinmarketcap.com API via a WP-Cron scheduled task that regularly updates into a WordPress database variable which gets called every time a price is displayed on a page; the plugin function takes the price string, strips it of currency symbol(s), converts the leftover number(s) string into either a float number or an array of float numbers in case it detects that the price in question is for a product that is on sale, or happens to be of variable kind; the number(s) are passed on to currency conversion function after which they get displayed alongside fiat prices


----------


**Screenshot 1: Customer View Shop Page Display**

The customer sees everything priced in both chosen store fiat currency and ARK conversion (custom currency+symbol, 8 decimals) 

https://github.com/Spikarija/ARKCommerce/blob/master/price%20conversion%20testing/01%20-%20customer%20view%20shop%20page.png


----------


**Screenshot 2: Customer View Regular Product Page Display**

Same as above

https://github.com/Spikarija/ARKCommerce/blob/master/price%20conversion%20testing/02%20-%20customer%20view%20product%20page.png


----------


**Screenshot 3: Customer View Regular Product on Sale Page Display**

The customer sees both the regular price and sale price in dual currencies in matching markup

https://github.com/Spikarija/ARKCommerce/blob/master/price%20conversion%20testing/03%20-%20customer%20view%20product%20on%20sale%20page.png


----------


**Screenshot 4: Customer View Variable Product Page Display**

The customer sees the product line price range and chosen variant in dual currencies and matching markup

https://github.com/Spikarija/ARKCommerce/blob/master/price%20conversion%20testing/04%20-%20customer%20view%20variable%20product%20page.png


----------


**Screenshot 5: Customer View Variable Product on Sale Page Display**

Same as above

https://github.com/Spikarija/ARKCommerce/blob/master/price%20conversion%20testing/05%20-%20customer%20view%20variable%20product%20on%20sale%20page.png


----------


**Screenshot 6: Customer View Affiliate Product Page Display**

Same as above

https://github.com/Spikarija/ARKCommerce/blob/master/price%20conversion%20testing/06%20-%20customer%20view%20affiliate%20product%20page.png


----------


**Screenshot 7: Customer View Affiliate Product on Sale Page Display**

Same as above

https://github.com/Spikarija/ARKCommerce/blob/master/price%20conversion%20testing/07%20-%20customer%20view%20affiliate%20product%20%20on%20sale%20page.png
