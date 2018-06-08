<?php
/*
ARKCommerce
Copyright (C) 2017-2018 Milan Semen

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
//////////////////////////////////////////////////////////////////////////////////////////
// START OF ARKCOMMERCE WOO INTEGRATION FUNCTIONS										//
//////////////////////////////////////////////////////////////////////////////////////////
// Prohibit direct access
if( !defined( 'ABSPATH' ) ) exit;

//////////////////////////////////////////////////////////////////////////////////////////
// Add ARK Crypto Currency to WooCommerce List of Store Currencies (As Custom Currency)	//
// @return str $currencies																//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_add_ark_currency( $currencies ) 
{
	// Add ISO 4217 currency identifier
	$currencies['ARK'] = 'ARK';
	return $currencies;
}
add_filter( 'woocommerce_currencies', 'arkcommerce_add_ark_currency' );

//////////////////////////////////////////////////////////////////////////////////////////
// Add the ARK Crypto Currency Symbol to WooCommerce (As Custom Currency)				//
// @return str $currency_symbol															//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_add_ark_currency_symbol( $currency_symbol, $currency ) 
{
	switch( $currency ) 
	{
		// Add ISO 4217 currency symbol
		case 'ARK': $currency_symbol = 'Ѧ'; break;
	}
	return $currency_symbol;
}
add_filter('woocommerce_currency_symbol', 'arkcommerce_add_ark_currency_symbol', 10, 2);

//////////////////////////////////////////////////////////////////////////////////////////
// Convert Potentially Complex Price Sring(s) to Float(s)								//
// @param str $price_input																//
// @return float $price/float arr $amounts												//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_price_number_conversion( $price_input )
{
	// Gather and/or set variables
	$cs = get_woocommerce_currency_symbol();
	
	// Determine whether input is one or more strings
	if( substr_count( $price_input, $cs ) > 1 )
	{
		// More than one price contained in input string
		$pricearray = explode( " ", $price_input );
		foreach( $pricearray as $price )
		{
			// Clear witespaces and currency symbol
			$price = trim( $price );
			$price = str_replace( ' ', '', $price );
			$price = str_replace( $cs, '', $price );
    
			// Check case where string has "," and "."
			$dot = strpos( $price, '.' );
			$semi = strpos( $price, ',' );
			if( $dot !== false && $semi !== false )
			{
				// Change fraction sign to #, we change it again later
				$price = str_replace( '#', '', $price ); 
				if( $dot < $semi ) $price = str_replace( ',', '#', $price );
				else $price = str_replace( '.', '#', $price );
		
				// Remove another ",", "." and change "#" to "."
				$price = str_replace( [',', '.', '#'], ['','', '.'], $price );
			}
			// Clear usless elements
			$price = str_replace( ',', '.', $price ); 
			$price = preg_replace( "/[^0-9\.]/", "", $price );
	
			// Convert to float
			$price = floatval( $price );
		
			// Add to result array if not 0 (the process produces several 0 values) 
			if( $price != 0 ) $amounts[] = $price;
		}
		return $amounts;
	}
	// One price contained in input string
	else
	{
		// Clear witespaces and currency symbol
		$price = trim( $price_input );
		$price = str_replace( ' ', '', $price );
		$price = str_replace( $cs, '', $price );
    
		// Check case where string has "," and "."
		$dot = strpos( $price, '.' );
		$semi = strpos( $price, ',' );
		if( $dot !== false && $semi !== false )
		{
			// Change fraction sign to #, we change it again later
			$price = str_replace( '#', '', $price ); 
			if( $dot < $semi ) $price = str_replace( ',', '#', $price );
			else $price = str_replace( '.', '#', $price );
		
			// Remove another ",", "." and change "#" to "."
			$price = str_replace( [',', '.', '#'], ['','', '.'], $price );
		}
		// Clear usless elements
		$price = str_replace( ',', '.', $price ); 
		$price = preg_replace( "/[^0-9\.]/", "", $price );
	
		// Convert to float and return the result
		$price = floatval( $price );
		
		// Return result
		return $price;
	}
}
//////////////////////////////////////////////////////////////////////////////////////////
// Currency Check of the Store															//
// @return bool $currency_supported														//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_check_currency_support() 
{
	// Gather and/or set variables
	$store_currency = get_woocommerce_currency();
	
	// List supported currencies (coinmarketcap.com listings as of 6/2018)
	$supported_currencies = array( "ARK", "BTC", "USD", "AUD", "BRL", "CAD", "CHF", "CLP", "CNY", "CZK", "DKK", "EUR", "GBP", "HKD", "HUF", "IDR", "ILS", "INR", "JPY", "KRW", "MXN", "MYR", "NOK", "NZD", "PHP", "PKR", "PLN", "RUB", "SEK", "SGD", "THB", "TRY", "TWD", "ZAR" );
	
	// Currency support check
	if( in_array( $store_currency, $supported_currencies ) ) $currency_supported = true;
	else $currency_supported = false;
	
	// Return result
	return $currency_supported;
}
//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Dual Price Display														//
// @param str $price																	//
// @return str $price																	//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_dual_price_display( $price )
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$store_currency = get_woocommerce_currency();

	// Show ARK price if default store currency other than ARK is chosen and dual price display is switched on
	if( $store_currency != 'ARK' && $arkgatewaysettings['arkdualprice'] == 'on' )
	{
		// Clean up price string and convert to float
		$float_price = arkcommerce_price_number_conversion( $price );
		
		if( is_array( $float_price ) )
		{
			// Variable price detected
			if( substr_count( $price, "&ndash" ) > 0 )
			{
				$arkprice = ( '<br>Ѧ' . arkcommerce_conversion_into_ark ( $float_price[0] ) . ' –' );
				$arkprice .= ( '<br>Ѧ' . arkcommerce_conversion_into_ark ( $float_price[1] ) );
				$price .= $arkprice;
				return $price;
			}
			// Sale price detected
			else
			{
				$arkprice = ( '<br><del>Ѧ' . arkcommerce_conversion_into_ark ( $float_price[0] ) . '</del>' );
				$arkprice .= ( '<br>Ѧ' . arkcommerce_conversion_into_ark ( $float_price[1] ) );
				$price .= $arkprice;
				return $price;
			}
		}
		// Regular price detected
		else
		{
			$arkprice = ( '<br>Ѧ' . arkcommerce_conversion_into_ark ( $float_price ) );
			$price .= $arkprice;
			return $price;
		}
	}
	// Price already in ARK
	else return $price;
}
add_filter( 'woocommerce_get_price_html', 'arkcommerce_dual_price_display' );
add_filter( 'woocommerce_cart_item_price', 'arkcommerce_dual_price_display' );
add_filter( 'woocommerce_cart_item_subtotal', 'arkcommerce_dual_price_display' );

//////////////////////////////////////////////////////////////////////////////////////////
// Display ARK Price+Timeout Notice To Customers at Cart Checkout						//
// @output arkcommercecheckoutnotice													//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_display_checkout_arkprice() 
{
	// Gather and/or set variables
	global $woocommerce;
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$store_currency = get_woocommerce_currency();

	// Check if ARK is already chosen as main currency and do nothing if so
	if( $store_currency != 'ARK' ) 
	{
		// Gather and prepare fiat prices
		$total_float = arkcommerce_price_number_conversion( WC()->cart->get_cart_total() );
		$shipping_float = arkcommerce_price_number_conversion( WC()->cart->get_shipping_total() );
		
		// Execute conversion from fiat to ARK
		$arkprice = arkcommerce_conversion_into_ark( ( $total_float + $shipping_float ) );
		
		// Assemble the cart notice
		$arkcommercecheckoutnotice = ( '<span class="dashicons-before dashicons-arkcommerce"> </span> <strong>' . __( 'Total', 'arkcommerce' ) . ': Ѧ' . $arkprice . '<br></strong><small>' . __( 'Order Expiry', 'arkcommerce' ) . ': ' . arkcommerce_get_order_timeout() . '</small>' );
	}
	else $arkcommercecheckoutnotice = ( '<span class="dashicons-before dashicons-arkcommerce"> </span> <small>' . __( 'Order Expiry', 'arkcommerce' ). ': ' . arkcommerce_get_order_timeout() . '</small>' );
	
	// Output the checkout notice
	wc_print_notice( $arkcommercecheckoutnotice, 'notice' );
}
add_action( 'woocommerce_review_order_before_payment', 'arkcommerce_display_checkout_arkprice' );

//////////////////////////////////////////////////////////////////////////////////////////
// Display ARK Price+Timeout Notice to Customers at Cart Checkout						//
// @output arkcommercecheckoutnotice													//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_display_cart_arkprice() 
{
	// Gather and/or set variables
	global $woocommerce;
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$store_currency = get_woocommerce_currency();

	// Check if ARK is already chosen as main currency and do nothing if so
	if( $store_currency != 'ARK' ) 
	{
		// Gather and prepare fiat prices
		$total_float = arkcommerce_price_number_conversion( WC()->cart->get_cart_total() );
		$shipping_float = arkcommerce_price_number_conversion( WC()->cart->get_shipping_total() );
		
		// Execute conversion from fiat to ARK
		$arkprice = arkcommerce_conversion_into_ark( ( $total_float + $shipping_float ) );
		
		// Assemble the cart notice
		$arkcommercecartnotice = ( '<span class="dashicons-before dashicons-arkcommerce"> </span> <strong>' . __( 'Total', 'arkcommerce' ) . ': Ѧ' . $arkprice . '<br></strong><small>' . __( 'Order Expiry', 'arkcommerce' ) . ': ' . arkcommerce_get_order_timeout() . '</small>' );
	}
	else $arkcommercecartnotice = ( '<span class="dashicons-before dashicons-arkcommerce"> </span> <small>' . __( 'Order Expiry', 'arkcommerce' ). ': ' . arkcommerce_get_order_timeout() . '</small>' );
	
	// Output the cart notice if enabled
	if( $arkgatewaysettings['arkdisplaycart'] == 'on' )	wc_print_notice( $arkcommercecartnotice, 'notice' );
}
add_action( 'woocommerce_proceed_to_checkout', 'arkcommerce_display_cart_arkprice' );

//////////////////////////////////////////////////////////////////////////////////////////
// 		Content for Order Data for Received/'Thank You' Page And Order Email			//
// 		@param $order_id																//
// 		@param $arkprice																//
// 		@return str $arkcommerceinformation												//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_order_data_content( $order_id, $arkprice ) 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$store_currency = get_woocommerce_currency();
	$timeout = arkcommerce_get_order_timeout();
	
	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' ) $storewalletaddress = $arkgatewaysettings['darkaddress'];
	else $storewalletaddress = $arkgatewaysettings['arkaddress'];

	// Include the QR Code of store ARK wallet address and form a table containing the store ARK address, order number, and amount total
	$qrcode = sprintf( '<hr><table><tr><th><img alt="QRCODE" width="130" height="130" src="%s"></th><td>%s</td></tr></table><hr>', ( plugin_dir_url( __FILE__ ) . 'assets/images/qrcode.png' ), wptexturize( $arkgatewaysettings['instructions'] ) );
	$arktable = sprintf( '<table><tr><th><b>' . __( 'ARK Wallet Address', 'arkcommerce' ) . '</b></th><td>%s</td></tr><tr><th><b>SmartBridge</b></th><td>%s</td></tr><tr><th><b>' . __( 'ARK Total', 'arkcommerce' ) . '</b></th><td>Ѧ%s</td></tr><tr><th><b>' . __( 'Order Expiry', 'arkcommerce' ) . '</b></th><td>%s</td></tr></table><hr>', $storewalletaddress, $order_id, $arkprice, $timeout );	
				
	// Compese and return the QR Code, admin-defined instructions in the complete ARKCommerce data table
	$arkcommerceinformation = ( $qrcode . wptexturize ( $arktable ) );
	return $arkcommerceinformation;
}
//////////////////////////////////////////////////////////////////////////////////////////
// END OF ARKCOMMERCE WOO INTEGRATION FUNCTIONS											//
//////////////////////////////////////////////////////////////////////////////////////////