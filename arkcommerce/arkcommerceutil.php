<?php
/*
ARKCommerce
Copyright (C) 2017-2018 Milan Semen

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
//////////////////////////////////////////////////////////////////////////////////////////
// START OF ARKCOMMERCE UTILITIES														//
//////////////////////////////////////////////////////////////////////////////////////////
// Prohibit direct access
if( !defined( 'ABSPATH' ) ) exit;

//////////////////////////////////////////////////////////////////////////////////////////
// Get ARK Blockchain Current Block Height												//
// @return int $arklastblock															//
// @record int arkservice																//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_get_block_height() 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	
	// Encryption flag and URI prefix
	if( $arkgatewaysettings['nodeencryption'] = 'yes' ) $arknodeprefix = 'https://';
	else $arknodeprefix = 'http://';
	
	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' ) $arknode = ( $arknodeprefix . $arkgatewaysettings['darknode'] );
	else $arknode = ( $arknodeprefix . $arkgatewaysettings['arknode'] );
	
	// Construct a block height query URI for ARK/DARK Node
	$ark_blockquery = "$arknode/api/blocks/getHeight";
	
	// Query ARK/DARK Node API for current block height
	$arkblockresponse = wp_remote_get( $ark_blockquery );
	
	// API response
	if( is_array( $arkblockresponse ) ) 
	{
		$arkblockheight = json_decode( $arkblockresponse['body'], true );
		
		// Validate response
		if( $arkblockheight['success'] === true )
		{
			// Get current block height
			$arklastblock = $arkblockheight['height'];
			$arkgatewaysettings['arkservice'] = 0;
		}
		else
		{
			// Response invalid
			$arkgatewaysettings['arkservice'] = 1;
			$arklastblock = 0;
		}
	}
	else
	{
		// Node unreachable
		$arkgatewaysettings['arkservice'] = 1;
		$arklastblock = 0;
	}
	// Record ARK/DARK Node status
	update_option( 'woocommerce_ark_gateway_settings', $arkgatewaysettings );
	
	// Return ARK blockchain last block
	return $arklastblock;
}
//////////////////////////////////////////////////////////////////////////////////////////
// Generate Store ARK Wallet Address QR Code											//
// @record blob qrcode																	//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_generate_qr_code() 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$filepath = ( plugin_dir_path( __FILE__ ) . 'assets/images/qrcode.png' );
	$backcolor = 0xFFFFFF;
	$forecolor = 0x4AB6FF;
	
	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' ) $storewalletaddress = $arkgatewaysettings['darkaddress'];
	else $storewalletaddress = $arkgatewaysettings['arkaddress'];
	
	// Adhere to the proper ARK QR code format
	$storewalletaddress = ( '{"a":"' . $storewalletaddress . '"}' );
	
	// Execute the external PHP QR Code Generator
	if( $storewalletaddress != null ) QRcode::png( $storewalletaddress, $filepath, "L", 8, 1, $backcolor, $forecolor);
}
//////////////////////////////////////////////////////////////////////////////////////////
// Determine the Order Expiry Timeout and Return the Value to Be Displayed to Customer	//
// @return str $timeout																	//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_get_order_timeout() 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	
	// Determine order expiry timeout
	if( $arkgatewaysettings['arktimeout'] == 110 ) $timeout = ( __( '110 blocks (cca 15 min)', 'arkcommerce' ) );
	elseif( $arkgatewaysettings['arktimeout'] == 225 ) $timeout = ( __( '225 blocks (cca 30 min)', 'arkcommerce' ) );
	elseif( $arkgatewaysettings['arktimeout'] == 450 ) $timeout = ( __( '450 blocks (cca 60 min)', 'arkcommerce' ) );
	elseif( $arkgatewaysettings['arktimeout'] == 900 ) $timeout = ( __( '900 blocks (cca 2 hours)', 'arkcommerce' ) );
	elseif( $arkgatewaysettings['arktimeout'] == 1800 ) $timeout = ( __( '1800 blocks (cca 4 hours)', 'arkcommerce' ) );
	elseif( $arkgatewaysettings['arktimeout'] == 3600 ) $timeout = ( __( '3600 blocks (cca 8 hours)', 'arkcommerce' ) );
	elseif( $arkgatewaysettings['arktimeout'] == 5400 ) $timeout = ( __( '5400 blocks (cca 12 hours)', 'arkcommerce' ) );
	elseif( $arkgatewaysettings['arktimeout'] == 10800 ) $timeout = ( __( '10800 blocks (cca 24 hours)', 'arkcommerce' ) );
	elseif( $arkgatewaysettings['arktimeout'] == 75600 ) $timeout = ( __( '75600 blocks (cca 7 days)', 'arkcommerce' ) );
	elseif( $arkgatewaysettings['arktimeout'] == 151200 ) $timeout = ( __( '151200 blocks (cca 2 weeks)', 'arkcommerce' ) );
	elseif( $arkgatewaysettings['arktimeout'] == 324000 ) $timeout = ( __( '324000 blocks (cca 1 month)', 'arkcommerce' ) );
	elseif( $arkgatewaysettings['arktimeout'] == 'never' ) $timeout = ( __( 'None (order never expires)', 'arkcommerce' ) );
	
	// Return the result
	return $timeout;
}
//////////////////////////////////////////////////////////////////////////////////////////
// Periodic Worker Triggering Transaction Validation Jobs on ARKCommerce Open Orders	//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_validation_worker() 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	global $wpdb;
	
	// Construct a query for all WC orders made using ARKCommerce payment gateway
	$arkordersquery = ( "SELECT post_id FROM " . $wpdb->postmeta . " WHERE meta_value='ark_gateway';" );
	
	// Execute the query
	$arkorders = $wpdb->get_results( $arkordersquery );
	
	// Determine valid database connection
	if( !empty( $arkorders ) ) 
	{
		// Iterate through open orders and commence tx check processing for each open order
		foreach( $arkorders as $arkorder ):setup_postdata( $arkorder );
			$order = wc_get_order( $arkorder->post_id );
			if( $order->has_status( 'on-hold' ) ) arkcommerce_ark_transaction_validation( $arkorder->post_id );
		endforeach;	
	}
}
add_action ( 'arkcommerce_check_for_open_orders', 'arkcommerce_validation_worker' );

//////////////////////////////////////////////////////////////////////////////////////////
// Function Checking for Order Payment Fulfillment, Handling and Notification			//
// @param int $order_id																	//
// @record int arkservice																//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_ark_transaction_validation( $order_id ) 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$ark_transaction_found = false;
	$order = wc_get_order( $order_id );
	$ark_order_data = $order->get_data();
	$ark_order_id = $ark_order_data['id'];
	$arkblockheight = arkcommerce_get_block_height();
	$ark_arktoshi_total = $order->get_meta( $key = 'ark_arktoshi_total' );
	$arkorderblock = $order->get_meta( $key = 'ark_order_block' );
	$arkorderexpiryblock = $order->get_meta( $key = 'ark_expiration_block' );
	$storewalletaddress = $order->get_meta( $key = 'ark_store_wallet_address' );
	
	// Encryption flag and URI prefix
	if( $arkgatewaysettings['nodeencryption'] = 'yes' ) $arknodeprefix = 'https://';
	else $arknodeprefix = 'http://';
	
	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' ) 
	{
		$arknode = ( $arknodeprefix . $arkgatewaysettings['darknode'] );
		$explorerurl = 'https://dexplorer.ark.io/tx/';
	}
	else
	{
		$arknode = ( $arknodeprefix . $arkgatewaysettings['arknode'] );
		$explorerurl = 'https://explorer.ark.io/tx/';
	}
	// Validate the order is on hold and the chosen payment method is ARKCommerce
	if( $order->has_status( 'on-hold' ) && 'ark_gateway' === $order->get_payment_method() ) 
	{
		// Fetch last 50 transactions into store wallet
		$arktxrecordarray = arkcommerce_fetch_transactions( 50 );
		
		if( is_array( $arktxrecordarray ) && $arkblockheight !=0 ) 
		{
			// ARK/DARK node responsive
			$arkgatewaysettings['arkservice'] = 0;
			
			// Process each result in array to establish the correct transaction filling this particular order
			foreach( $arktxrecordarray as $arktxrecord ):setup_postdata( $arktxrecord );
			
				// Validate TX as correct payment tx for the order
				if( $arktxrecord['vendorField'] == $ark_order_id && intval( $arktxrecord['amount'] ) == intval( $ark_arktoshi_total ) ) 
				{
					// Extract the potential transaction ID
					$arktxrecordtxid = $arktxrecord['id'];
					
					// Construct query for the specific potential transaction URI for ARK/DARK Node
					$ark_txquery = "$arknode/api/transactions/get?id=$arktxrecordtxid";
					
					// Query ARK/DARK Node API for the specific transaction
					$arknodetxresponse = wp_remote_get( $ark_txquery );
	
					// API response
					if( is_array( $arknodetxresponse ) ) 
					{
						$arktxresponse = json_decode( $arknodetxresponse['body'], true );
		
						// Validate response
						if( $arktxresponse['success'] === true ) $arktxblockheight = $arktxresponse['transaction']['height'];
					}
					// Validate found TX block height is higher or equal to blockchain block height at the time order was made and the order has not expired at that time
					if( $arkorderexpiryblock != 'never' && intval( $arktxblockheight ) <= intval( $arkorderexpiryblock ) && intval( $arktxblockheight ) >= intval( $arkorderblock ) ) 
					{
						// Correct payment TX found
						$ark_transaction_found = true;
						$ark_transaction_identifier = $arktxrecordtxid;
						$ark_transaction_block = $arktxblockheight;
					}
					// Alternatively, validate found TX block height is higher or equal to blockchain block height at the time order was made
					elseif( $arkorderexpiryblock == 'never' && intval( $arktxblockheight ) >= intval( $arkorderblock ) ) 
					{
						// Correct payment TX found
						$ark_transaction_found = true;
						$ark_transaction_identifier = $arktxrecordtxid;
						$ark_transaction_block = $arktxblockheight;
					}
				}
			endforeach;
			
			// Payment TX found and it occurred after the order had been committed
			if( $ark_transaction_found === true ) 
			{
				// Mark as complete (the payment has been made), add transaction id plus payment block number to order metadata
				$ark_order_completed_note = ( __( 'ARK order filled at block:', 'arkcommerce' ) . ' ' . $ark_transaction_block . ' TX: <a href="' . $explorerurl . $ark_transaction_identifier . '">' . $ark_transaction_identifier . '</a>.' );
				$order->update_meta_data( 'ark_transaction_id', $ark_transaction_identifier );
				$order->update_meta_data( 'ark_payment_block', $ark_transaction_block );
				$order->save();
				$order->update_status( 'completed', $ark_order_completed_note );
				
				// Notify admin if enabled
				if( $arkgatewaysettings['arkorderfillednotify'] == 'on' ) arkcommerce_admin_notification( $order_id, 'orderfilled' );
			}
			// No payment TX for the order number found at this time
			elseif( $arkorderexpiryblock != 'never' && $ark_transaction_found === false && intval( $arkblockheight ) <= intval( $arkorderexpiryblock ) ) 
			{
				// Make note of unsuccessful check (the payment has not yet been made)
				$order->add_order_note( ( __( 'ARK transaction check yields no matches. Current block height', 'arkcommerce' ) . ': ' . $arkblockheight . '.' ), false, true );
			}
			// No payment TX for the order number found at this time (for orders that do not expire)
			elseif( $arkorderexpiryblock == 'never' && $ark_transaction_found === false ) 
			{
				// Make note of unsuccessful check (the payment has not yet been made)
				$order->add_order_note( ( __( 'ARK transaction check yields no matches. Current block height', 'arkcommerce' ) . ': ' . $arkblockheight . '.' ), false, true );
			}
			// The order has expired - the timeout since order commit exceeds set limit
			elseif( $arkorderexpiryblock != 'never' && $ark_transaction_found === false && intval( $arkblockheight ) > intval( $arkorderexpiryblock ) ) 
			{
				// Make note of order timeout reached (the payment has not been made within the set window) and mark as cancelled
				$ark_order_cancelled_note = ( __( 'ARK order expired at block height:', 'arkcommerce' ) . ' ' . $arkorderexpiryblock . '. ' . __( 'Current block height', 'arkcommerce' ) . ': ' . $arkblockheight . '.' );
				$order->update_status( 'cancelled', $ark_order_cancelled_note );
				
				// Notify admin if enabled
				if( $arkgatewaysettings['arkorderexpirednotify'] == 'on' ) arkcommerce_admin_notification( $order_id, 'orderexpired' );
			}
		}
		// ARK/DARK API response invalid or node unreachable
		else 
		{
			// Make note of unsuccessful check (the payment has not yet been made)
			$order->add_order_note( __( 'ARK network unresponsive or unreachable.', 'arkcommerce' ), false, true );
			$arkgatewaysettings['arkservice'] = 1;
		}
		// Record ARK/DARK Node status
		update_option( 'woocommerce_ark_gateway_settings', $arkgatewaysettings );
	}
}
//////////////////////////////////////////////////////////////////////////////////////////
// Update Currency Market Exchange Rate Between Chosen Store Fiat and ARK Pairs 		//
// @record float arkexchangerate														//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_update_exchange_rate() 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$store_currency = get_woocommerce_currency();
	
	// Check for supported currency
	$currency_supported = arkcommerce_check_currency_support();
	
	// Currency supported
	if( $currency_supported === true ) 
	{
		// Check if ARK already chosen as main currency
		if( $store_currency == 'ARK' ) $arkexchangerate = 1;
		
		// Query coinmarketcap.com APIv2
		else 
		{
			// Construct a query URI for Coinmarketcap v2 API; expected number of results: 1
			$cmc_query = "https://api.coinmarketcap.com/v2/ticker/1586/?convert=$store_currency";
			
			// Query CoinMarketCap API for ARK market price in supported chosen currency
			$cmcresponse = wp_remote_get( $cmc_query );
			
			// CMC API response validation
			if( is_array( $cmcresponse) ) 
			{
				$arkmarketprice = json_decode( $cmcresponse['body'], true );
				
				// Construct a suitable key identifier for in-array lookup
				$chosen_currency_var = strtoupper( $store_currency );
				
				// Determine the exchange rate
				$arkexchangerate = $arkmarketprice[data][quotes][$chosen_currency_var][price];
			}
		}
	}
	// Currency not supported
	else $arkexchangerate = 0;
	
	// Update the gateway settings containing the variable 'arkexchangerate'
	$arkgatewaysettings['arkexchangerate'] = $arkexchangerate;
	update_option( 'woocommerce_ark_gateway_settings', $arkgatewaysettings );
}
add_action ( 'arkcommerce_refresh_exchange_rate', 'arkcommerce_update_exchange_rate' );

//////////////////////////////////////////////////////////////////////////////////////////
// Get Store Fiat-ARK Exchange Rate														//
// @return float $arkexchangerate														//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_get_exchange_rate() 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$store_currency = get_woocommerce_currency();
	
	// ARK is the chosen default store currency
	if( $store_currency == 'ARK' ) $arkexchangerate = 1;
	
	// Establish and set the correct exchange rate (autorate/multirate/fixedrate)
	else
	{
		if( $arkgatewaysettings['arkexchangetype'] == 'autorate' ) $arkexchangerate = $arkgatewaysettings['arkexchangerate'];
		elseif( $arkgatewaysettings['arkexchangetype'] == 'multirate' ) $arkexchangerate = ( $arkgatewaysettings['arkexchangerate'] / $arkgatewaysettings['arkmultiplier'] );
		elseif( $arkgatewaysettings['arkexchangetype'] == 'fixedrate' ) $arkexchangerate = $arkgatewaysettings['arkmanual'];
	}
	// Return exchange rate
	return $arkexchangerate;
}

//////////////////////////////////////////////////////////////////////////////////////////
// Internal Currency Conversion Between Fiat and ARK Pairs								//
// @param int/float fiat $amount														//
// @return float $arkamount																//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_conversion_into_ark( $amount ) 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$store_currency = get_woocommerce_currency();
	$arkexchangerate = arkcommerce_get_exchange_rate();
	
	// Check for supported currency
	$currency_supported = arkcommerce_check_currency_support();
	
	// Supported fiat currency input
	if( $store_currency != 'ARK' && $arkexchangerate != 0 && $currency_supported === true ) $arkamount = number_format( ( float )( $amount / $arkexchangerate ), 8, '.', '' );
	
	// ARK input equals ARK output
	elseif( $store_currency == 'ARK' ) $arkamount = $amount;
	
	// Currency not supported and fixed rate not chosen
	elseif( $currency_supported === false && $arkgatewaysettings['arkexchangetype'] != 'fixedrate' ) $arkamount = 0;
	
	// Unsupported fiat currency output using fixed exchange rate
	elseif( $currency_supported === false && $arkexchangerate != 0 && $arkgatewaysettings['arkexchangetype'] == 'fixedrate' ) $arkamount = number_format( ( float )( $amount / $arkexchangerate ), 8, '.', '' );
	
	// Return converted amount
	return $arkamount;
}
//////////////////////////////////////////////////////////////////////////////////////////
// Get ARK Wallet Balance																//
// @return float $result																//
// @record int arkservice																//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_get_wallet_balance()
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	
	// Encryption flag and URI prefix
	if( $arkgatewaysettings['nodeencryption'] = 'yes' ) $arknodeprefix = 'https://';
	else $arknodeprefix = 'http://';
	
	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' )
	{
		$storewalletaddress = $arkgatewaysettings['darkaddress'];
		$arknode = ( $arknodeprefix . $arkgatewaysettings['darknode'] );
	}
	else
	{
		$storewalletaddress = $arkgatewaysettings['arkaddress'];
		$arknode = ( $arknodeprefix . $arkgatewaysettings['arknode'] );
	}
	// Construct a store ARK wallet address balance query URI for ARK/DARK Node
	$ark_balancequery = "$arknode/api/accounts/getBalance?address=$storewalletaddress";
	
	// Query ARK/DARK Node API for store ARK wallet address balance
	$arkbalanceresponse = wp_remote_get( $ark_balancequery );
	
	// API response
	if( is_array( $arkbalanceresponse ) )
	{
		$arkbaresponse = json_decode( $arkbalanceresponse['body'], true );
		
		// Validate response
		if( $arkbaresponse['success'] === true )
		{
			// Get wallet balance
			$result = number_format( ( float ) $arkbaresponse['balance'] / 100000000, 8, '.', '' );
			$arkgatewaysettings['arkservice'] = 0;
		}
		else
		{
			// Response invalid
			$arkgatewaysettings['arkservice'] = 1;
			$result = 0;
		}
	}
	else
	{
		// Node unreachable
		$arkgatewaysettings['arkservice'] = 1;
		$result = 0;
	}
	// Record ARK/DARK Node status
	update_option( 'woocommerce_ark_gateway_settings', $arkgatewaysettings );

	// Return result
	return $result;
}
//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Fetch Last x Incoming Transactions										//
// @param int $txnumbercount															//
// @return arr $transactions															//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_fetch_transactions( $txnumbercount ) 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	
	// Encryption flag and URI prefix
	if( $arkgatewaysettings['nodeencryption'] = 'yes' ) $arknodeprefix = 'https://';
	else $arknodeprefix = 'http://';
	
	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' )
	{
		$storewalletaddress = $arkgatewaysettings['darkaddress'];
		$arknode = ( $arknodeprefix . $arkgatewaysettings['darknode'] );
	}
	else
	{
		$storewalletaddress = $arkgatewaysettings['arkaddress'];
		$arknode = ( $arknodeprefix . $arkgatewaysettings['arknode'] );
	}
	// Make sure there is a number specified
	if( !empty( $txnumbercount ) )
	{
		// Construct a store ARK wallet address last x transactions query URI for ARK/DARK Node
		$ark_txquery = "$arknode/api/transactions?recipientId=$storewalletaddress&orderBy=height%3Adesc&limit=$txnumbercount";
	
		// Query ARK/DARK Node API for last x store ARK wallet address transactions
		$arkbridgeresponsetx = wp_remote_get( $ark_txquery );
	
		// API response
		if( is_array( $arkbridgeresponsetx ) ) 
		{
			$arktxresponse = json_decode( $arkbridgeresponsetx['body'], true );
		
			// Validate response
			if( $arktxresponse['success'] === true ) $arktxarray = $arktxresponse['transactions'];
		}
	}
	else $arktxarray = 0;
	
	// Return result
	return $arktxarray;
}
//////////////////////////////////////////////////////////////////////////////////////////
// Open ARKCommerce Orders Queue Count Checker											//
// @return int $arkopenordercount														//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_open_order_queue_count() 
{
	// Gather and/or set variables
	global $wpdb;
	$arkopenordercount = 0;
	
	// Construct a query for all WC orders made using ARKCommerce payment gateway
	$arkordersquery = ( "SELECT post_id FROM " . $wpdb->postmeta . " WHERE meta_value='ark_gateway';" );
	
	// Execute the query
	$arkorders = $wpdb->get_results( $arkordersquery );
	
	// Determine valid database connection
	if( !empty( $arkorders ) ) 
	{
		// Iterate through open orders and count total of open orders
		foreach( $arkorders as $arkorder ):setup_postdata( $arkorder );
			$order = wc_get_order( $arkorder->post_id );
			if( $order->has_status( 'on-hold' ) ) $arkopenordercount = ( $arkopenordercount + 1 );
		endforeach;
	}
	// Return result
	return $arkopenordercount;
}
//////////////////////////////////////////////////////////////////////////////////////////
// END OF ARKCOMMERCE UTILITIES															//
//////////////////////////////////////////////////////////////////////////////////////////