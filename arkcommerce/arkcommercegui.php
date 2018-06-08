<?php
/*
ARKCommerce
Copyright (C) 2017-2018 Milan Semen

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
//////////////////////////////////////////////////////////////////////////////////////////
// START OF ARKCOMMERCE GUI																//
//////////////////////////////////////////////////////////////////////////////////////////
// Prohibit direct access
if( !defined( 'ABSPATH' ) ) exit;

//////////////////////////////////////////////////////////////////////////////////////////
// Add Plugin Page Links																//
// @param array $links all plugin links													//
// @return array $links all plugin links and custom ARKCommerce links					//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_plugin_links( $links ) 
{
	// List of links added to plugin entry
	$plugin_links = array( '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=ark_gateway' ) . '">' . __( 'Settings', 'arkcommerce' ) . '</a>', '<a href="' . admin_url( 'admin.php?page=arkcommerce_preferences' ) . '">' . __( 'Preferences', 'arkcommerce' ) . '</a>' );
	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'arkcommerce_plugin_links' );

//////////////////////////////////////////////////////////////////////////////////////////
// Add Plugin Page Meta Row Links														//
// @param array $links all plugin links													//
// @return array $links all plugin meta links and custom ARKCommerce meta link			//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_plugin_meta_links( $links, $file )
{
	// Gather and/or set variables
	$base = plugin_basename(__FILE__);
	
	// Validate
	if( $file == $base )
	{
		// List of links added to plugin description entry
		$links[] = '<a href="' . admin_url( 'admin.php?page=arkcommerce_information' ) . '">' . __( 'Information', 'arkcommerce' ) . '</a>';
		$links[] = '<a href="https://explorer.ark.io/address/AXaDj4ADMgzw67zik3ynwktARVKgwfv1WP">' . __( 'Donate ARK', 'arkcommerce' ) . '</a>';
	}
	// Add entry to existing links
	return $links;
}
add_filter( 'plugin_row_meta',  'arkcommerce_plugin_meta_links', 10, 2 );

//////////////////////////////////////////////////////////////////////////////////////////
// Add ARKCommerce CSS Admin Stylesheets												//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_register_styles_scripts() 
{
	// CSS for all ARKCommerce admin styles (administrator-facing)
	wp_register_style( 'arkcommerce_style', plugin_dir_url( __FILE__ ) . 'assets/css/arkcommerce.css' );
	wp_register_script( 'arkcommerce_script', plugin_dir_url( __FILE__ ) . 'assets/js/arkexplorer.js' );
	wp_enqueue_style( 'arkcommerce_style' );
	wp_enqueue_script( 'arkcommerce_script' );
}
add_action( 'admin_enqueue_scripts', 'arkcommerce_register_styles_scripts' );

//////////////////////////////////////////////////////////////////////////////////////////
// Add ARKCommerce CSS WP Stylesheets													//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_register_wp_styles() 
{
	// CSS for all ARKCommerce WP styles (customer-facing)
	wp_register_style( 'wp_arkcommerce', plugin_dir_url( __FILE__ ) . 'assets/css/wp_arkcommerce.css' );
	wp_enqueue_style( 'wp_arkcommerce' );
}
add_action( 'wp_enqueue_scripts', 'arkcommerce_register_wp_styles' );

//////////////////////////////////////////////////////////////////////////////////////////
// Perform Checks and Display Admin Notifications										//
// @output ARKCommerce administrator notices											//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_attention_required() 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$currency_supported = arkcommerce_check_currency_support();
	$arkadmurl = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=ark_gateway' );
	$arkprefurl = admin_url( 'admin.php?page=arkcommerce_preferences' );
	$arkinfourl = admin_url( 'admin.php?page=arkcommerce_information' );
	
	// Settings check
	if( $arkgatewaysettings['arkaddress'] == "" && $arkgatewaysettings['enabled'] == "yes" && $currency_supported === true && $arkgatewaysettings['darkmode'] != 'yes' ) 
	{
		// Display error notice
		echo( '<div class="notice notice-error is-dismissible"><p><span class="dashicons-before dashicons-arkcommerce" style="vertical-align:middle;"> </span> <strong><a href="' . $arkadmurl . '">ARKCommerce</a> ' . __( 'requires your attention for a functioning setup. Please enter a valid ARK Wallet Address or disable ARKCommerce.', 'arkcommerce' ) . '</strong></p></div>' );
	}
	elseif( $currency_supported === false && $arkgatewaysettings['enabled'] == "yes" && $arkgatewaysettings['arkexchangetype'] != 'fixedrate') 
	{
		// Display error notice
		echo( '<div class="notice notice-error is-dismissible"><p><span class="dashicons-before dashicons-arkcommerce" style="vertical-align:middle;"> </span> <strong>' . __( 'Currently selected store currency is not supported in automatic exchange rate mode. Please switch to the fixed exchange rate in', 'arkcommerce' ) . ' <a href="' . $arkprefurl . '">ARKCommerce ' . __( 'Preferences', 'arkcommerce' ) . '</a>.</strong></p></div>' );
	}
	elseif( $currency_supported === false && $arkgatewaysettings['enabled'] == "yes" && $arkgatewaysettings['arkexchangetype'] == 'fixedrate' && empty( $arkgatewaysettings['arkmanual'] ) ) 
	{
		// Display error notice
		echo( '<div class="notice notice-error is-dismissible"><p><span class="dashicons-before dashicons-arkcommerce" style="vertical-align:middle;"> </span> <strong>' . __( 'Currently selected store currency does not have its fixed exchange rate defined, please do so in', 'arkcommerce' ) . ' <a href="' . $arkprefurl . '">ARKCommerce ' . __( 'Preferences', 'arkcommerce' ) . '</a>.</strong></p></div>' );
	}
	elseif( $currency_supported === true && $arkgatewaysettings['enabled'] == "yes" && $arkgatewaysettings['arkexchangetype'] == 'multirate' && empty( $arkgatewaysettings['arkmultiplier'] ) ) 
	{
		// Display error notice
		echo( '<div class="notice notice-error is-dismissible"><p><span class="dashicons-before dashicons-arkcommerce" style="vertical-align:middle;"> </span> <strong>' . __( 'Currently selected store currency exchange rate multiplier has not been defined, please do so in', 'arkcommerce' ) . ' <a href="' . $arkprefurl . '">ARKCommerce ' . __( 'Preferences', 'arkcommerce' ) . '</a>.</strong></p></div>' );
	}
	elseif( $arkgatewaysettings['enabled'] == "no" ) 
	{		
		// Display info notice
		echo( '<div class="notice notice-info is-dismissible"><p><span class="dashicons-before dashicons-arkcommerce" style="vertical-align:middle;"> </span> <strong><a href="' . $arkadmurl . '">ARKCommerce</a> ' . __( 'payment gateway plugin is currently not enabled. Please configure and enable it or deactivate ARKCommerce.', 'arkcommerce' ) . '</strong></p></div>' );
	}
	elseif( $arkgatewaysettings['darkaddress'] == "" && $arkgatewaysettings['enabled'] == "yes" && $arkgatewaysettings['darkmode'] == 'yes' )
	{
		// Display error notice
		echo( '<div class="notice notice-error is-dismissible"><p><span class="dashicons-before dashicons-arkcommerce" style="vertical-align:middle;"> </span> <strong><a href="' . $arkadmurl . '">ARKCommerce</a> ' . __( 'requires your attention for a functioning DARK Mode setup. Please enter a valid DARK Wallet Address or disable DARK Mode.', 'arkcommerce' ) . '</strong></p></div>' );
	}
	elseif( $currency_supported === true && $arkgatewaysettings['enabled'] == "yes" && DISABLE_WP_CRON !== true )
	{		
		// Display info notice
		echo( '<div class="notice notice-info is-dismissible"><p><span class="dashicons-before dashicons-arkcommerce" style="vertical-align:middle;"> </span> <strong> ' . __( 'It is highly recommended turning on "Hard Cron" scheduled task operation. Guides are found in', 'arkcommerce' ) . ' <a href="' . $arkinfourl . '">ARKCommerce ' . __( 'Information', 'arkcommerce' ) . '</a></strong></p></div>' );
	}
	elseif( $arkgatewaysettings['enabled'] == "yes" && $arkgatewaysettings['arkservice'] === 1 ) 
	{		
		// Display warning notice
		echo( '<div class="notice notice-warning is-dismissible"><p><span class="dashicons-before dashicons-arkcommerce" style="vertical-align:middle;"> </span> <strong>ARKCommerce ' . __( 'ARK network unresponsive or unreachable. Payment gateway service currently unavailable.', 'arkcommerce' ) . '</strong></p></div>' );
	}
	elseif( $arkgatewaysettings['enabled'] == "yes" && !empty( $arkgatewaysettings['nodeapikey'] ) &&  ARKCOMMERCE_VERSION == '1.1.0' ) 
	{		
		// Display error notice
		echo( '<div class="notice notice-error is-dismissible"><p><span class="dashicons-before dashicons-arkcommerce" style="vertical-align:middle;"> </span> <strong>ARKCommerce ' . __( 'ARKCommerce requires deactivation and reactivation to upgrade properly and resume operation.', 'arkcommerce' ) . '</strong></p></div>' );
	}
}
add_action( 'admin_notices', 'arkcommerce_attention_required' );

//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Status Admin Dashboard Widget											//
// @output ARKCommerce Status Widget													//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_display_status_widget() 
{
	// Gather and/or set variables
	global $wpdb;
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$arkmarketexchangerate = $arkgatewaysettings['arkexchangerate'];
	$store_currency = get_woocommerce_currency();
	$arkexchangerate = arkcommerce_get_exchange_rate();
	$arkblockheight = arkcommerce_get_block_height();
	$wallet_balance = arkcommerce_get_wallet_balance();
	$arktxarray = arkcommerce_fetch_transactions( 10 );
	
	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' )
	{
		$storewalletaddress = $arkgatewaysettings['darkaddress'];
		$explorerurl = 'https://dexplorer.ark.io/';
	}
	else
	{
		$storewalletaddress = $arkgatewaysettings['arkaddress'];
		$explorerurl = 'https://explorer.ark.io/';
	}
	// Validate block height response
	if( $arkblockheight != 0 ) echo( '<span class="dashicons dashicons-info" style="color:lime;"> </span> <b style="color:black;">' . __( 'ARKCommerce operational', 'arkcommerce' ) . '. ' . __( 'ARK block height', 'arkcommerce' ) . ': ' . $arkblockheight . '</b>' );
	else echo( '<span class="dashicons dashicons-info" style="color:red;"> </span> <b style="color:black;">' . __( 'ARK network unresponsive or unreachable', 'arkcommerce' ) . '.</b>' ); 
	
	// Display Exchange rate information
	echo( '<hr><b>' . __( 'Market exchange rate', 'arkcommerce' ) . '</b>:</span> <i>' . $arkgatewaysettings['arkexchangerate'] . ' ' . $store_currency . '/ARK</i><br><b>' . __( 'Store exchange rate', 'arkcommerce' ) . '</b>: <i>' . $arkexchangerate . ' ' . $store_currency . '/ARK</i>' );
		
	// Determine whether the ARK/DARK Node has any hits for the store wallet address query
	if( !empty( $arktxarray[0] ) ) 
	{
		// Construct table header for wallet info
		$table_header_tx = sprintf( '<hr><b>' . __( 'Store Wallet', 'arkcommerce' ) . ' <a class=arkcommerce-link" target="_blank" href="' . $explorerurl. 'address/%s">%s</a> ' . __( 'Balance', 'arkcommerce' ) . ': Ѧ%s</b><hr>', $storewalletaddress, $storewalletaddress, $wallet_balance );
			
		// Form table and iterate rows through the array
		$content = ( '<b>' . __( 'Latest 10 Transactions', 'arkcommerce' ) . '</b><hr><table class="arkcommerce-table"><b><thead><tr><th>ID</th><th>' . __( 'Sender', 'arkcommerce' ) . '</th><th>' . __( 'Amount', 'arkcommerce' ) . ' (Ѧ)</th><th>SmartBridge</th></thead></b></tr>' );
		foreach( $arktxarray as $arktx ):setup_postdata( $arktx );
			$content .= ( '<tr><td><a target="_blank" href="' . $explorerurl . 'tx/' . $arktx[id] . '">TX</a></td><td><a target="_blank" href="' . $explorerurl . 'address/' . $arktx['senderId'] . '">' . __( 'Address', 'arkcommerce' ) . '</a></td><td>' . number_format( ( float ) $arktx['amount'] / 100000000, 8, '.', '' ) . '</td><td>' . $arktx['vendorField'] .'</td></tr>' );
		endforeach;
		$content .= '</table>';
			
		//Output
		echo( $table_header_tx . $content );
	}
	// Construct a query for all WC orders made using ARKCommerce payment gateway
	$arkordersquery = ( "SELECT post_id FROM " . $wpdb->postmeta . " WHERE meta_value='ark_gateway' ORDER BY post_id DESC LIMIT 10;" );
	
	// Execute the query
	$arkorders = $wpdb->get_results( $arkordersquery );
	
	// Determine valid database connection
	if( !empty( $arkorders ) ) 
	{
		// Conclude with a table containing information on last 10 ARKCommerce payment gateway orders
		$table_header_orders = '<hr><b>' . __( 'Latest Orders via ARKCommerce', 'arkcommerce' ) . '</b><hr><table class="arkcommerce-table"><b><thead><tr><th>' . __( 'Order ID', 'arkcommerce' ) . '</th><th>' . __( 'Order Total (Ѧ)', 'arkcommerce' ) . '</th><th>' . __( 'Order Status', 'arkcommerce' ) . '</th></thead></b></tr>';
		foreach( $arkorders as $arkorder ):setup_postdata( $arkorder );
			$order = wc_get_order( $arkorder->post_id );
			$ark_order_data = $order->get_data();
			$wcorderlink = admin_url( 'post.php?post=' . $arkorder->post_id . '&action=edit' );
			$ordercontent .= ( '<tr><td><a target="_blank" href="' . $wcorderlink . '">' . $arkorder->post_id . '</a></td><td>' . number_format( ( float ) $order->get_meta( $key = 'ark_total' ), 8, '.', '' ) . '</td><td>' . $ark_order_data['status'] . '</td></tr>' );
		endforeach;
		$ordercontent .= ( '</table>' );
		
		//Output
		echo( $table_header_orders . $ordercontent );
	}
}
//////////////////////////////////////////////////////////////////////////////////////////
// Add ARKCommerce Status Widget to the Admin Dashboard									//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_add_status_widget() 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	
	// Settings check
	if( $arkgatewaysettings['arkaddress'] != "" && $arkgatewaysettings['enabled'] == "yes" ) wp_add_dashboard_widget( 'arkcommercestatuswidget', 'ARKCommerce Status', 'arkcommerce_display_status_widget' );
}
add_action( 'wp_dashboard_setup', 'arkcommerce_add_status_widget' );

//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Manual TX Check Admin Dashboard Widget									//
// @output ARKCommerce Manual TX Check  Widget											//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_display_tx_check_widget() 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	
	// DARK Mode settings and display DARK Mode info (if enabled)
	if( $arkgatewaysettings['darkmode'] == 'yes' )
	{
		$storewalletaddress = $arkgatewaysettings['darkaddress'];
		$explorerurl = 'https://dexplorer.ark.io/';
		$displaydarkinfo = ( '<span class="dashicons dashicons-info" style="color:black;"> </span> <b>' . __( 'ARKCommerce DARK Mode enabled', 'arkcommerce' ) . '</b>.<hr>' );
	}
	else
	{
		$storewalletaddress = $arkgatewaysettings['arkaddress'];
		$explorerurl = 'https://explorer.ark.io/';
	}
	// Display form
	echo( '<form onsubmit="return false;"><table class="form-table">' . $displaydarkinfo . '<b>' . __( 'Store Wallet', 'arkcommerce' ) . ': <a class=arkcommerce-link" target="_blank" href="' . $explorerurl. 'address/' . $storewalletaddress . '">' . $storewalletaddress . '</a></b><hr><fieldset><legend><span class="dashicons dashicons-admin-links"> </span> <strong>' . __( 'Transaction ID', 'arkcommerce' ) . '</strong></legend><input type="hidden" id="explorerurl" value="' . $explorerurl . 'tx/"><input type="text" style="width:100%;" id="txid_manual_entry"></fieldset><br><input type="button" style="width: 100%;" value="' . __( 'Open in Explorer', 'arkcommerce' ) . '" onclick="' . "ARKExplorer(document.getElementById('explorerurl').value, document.getElementById('txid_manual_entry').value);return false;" . '"></table></form>' );
}
//////////////////////////////////////////////////////////////////////////////////////////
// Add ARKCommerce Manual TX Check Widget to the Admin Dashboard						//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_add_tx_check_widget() 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	
	// Settings check
	if( $arkgatewaysettings['arkaddress'] != "" && $arkgatewaysettings['enabled'] == "yes" ) wp_add_dashboard_widget( 'arkcommercemanualtxcheckwidget', __( 'ARKCommerce Manual TX Check', 'arkcommerce' ), 'arkcommerce_display_tx_check_widget' );
}
add_action( 'wp_dashboard_setup', 'arkcommerce_add_tx_check_widget' );

//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Meta Box Widget															//
// @output ARKCommerce Meta Box WooCommerce Widget										//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_display_meta_box_widget() 
{
	// Gather and/or set variables
    $arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$arktxarray = arkcommerce_fetch_transactions( 5 );
	
	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' ) 
	{
		$storewalletaddress = $arkgatewaysettings['darkaddress'];
		$explorerurl = 'https://dexplorer.ark.io/';
	}
	else
	{
		$storewalletaddress = $arkgatewaysettings['arkaddress'];
		$explorerurl = 'https://explorer.ark.io/';
	}
	// Determine whether the ARK/DARK Node has any hits for the store wallet address query
	if( !empty( $arktxarray[0] ) ) 
	{
		// Display info
		$table_header_tx = sprintf( '<span class="dashicons dashicons-info" style="color:lime;"> </span> <b style="color:black;">' . __( 'ARKCommerce operational', 'arkcommerce' ) . '.<br>' . __( 'Latest ARK transactions', 'arkcommerce' ) . ': <a class=arkcommerce-link" target="_blank" href="' . $explorerurl . 'address/%s">' . __( 'ARK wallet', 'arkcommerce' ) .  '</a>:</b><hr>', $storewalletaddress );
			
		// Form table and iterate rows through the array
		$content = '<table class="arkcommerce-meta-table"><b><thead><tr><th>' . __( 'Amount', 'arkcommerce' ) . ' (Ѧ)</th><th>SmartBridge</th></thead></b></tr>';
		foreach( $arktxarray as $arktx ):setup_postdata( $arktx );
			$content .= ( '<tr><td><a target="_blank" href="' . $explorerurl . 'tx/' . $arktx[id] . '">' . number_format( ( float ) $arktx['amount'] / 100000000, 8, '.', '' ) . '</a></td><td>' . $arktx['vendorField'] . '</td></tr>' );
		endforeach;
		$content .= '</table>';
		echo( $table_header_tx . $content );
	}
	// No transactions
	else echo( '<span class="dashicons dashicons-info" style="color:red;"> </span> <b style="color:black;">' . __( 'No incoming transactions found.', 'arkcommerce' ) . '.</b>' ); 
} 
//////////////////////////////////////////////////////////////////////////////////////////
// Add ARKCommerce Meta Box to the WooCommerce Order Admin Page							//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_add_woocommerce_meta_box() 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	
	// Settings check
	if( $arkgatewaysettings['arkaddress'] != "" && $arkgatewaysettings['enabled'] == "yes" ) add_meta_box( 'woocommerce-order-my-custom', 'ARKCommerce Wallet Monitor', 'arkcommerce_display_meta_box_widget', 'shop_order', 'side', 'default' );
}
add_action( 'add_meta_boxes', 'arkcommerce_add_woocommerce_meta_box' );

//////////////////////////////////////////////////////////////////////////////////////////
// Add ARKCommerce Menus for Pages														//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_add_menu_pages() 
{
    // Add Top-level menu ARKCommerce
	add_menu_page(		'ARKCommerce',
						'ARKCommerce',
						'administrator',
						'arkcommerce_root',
						'arkcommerce_navigator',
						'dashicons-arkcommerce' );
	
	// Add Sub menu ARKCommerce Navigator page
	add_submenu_page(	'arkcommerce_root',
						__( 'ARKCommerce Navigator', 'arkcommerce' ),
						__( 'Navigator', 'arkcommerce' ),
						'administrator',
						'arkcommerce_navigator',
						'arkcommerce_navigator' );
						    
	// Add Sub menu ARKCommerce Preferences page
	add_submenu_page(	'arkcommerce_root',
						__( 'ARKCommerce Preferences', 'arkcommerce' ),
						__( 'Preferences', 'arkcommerce' ),
						'administrator',
						'arkcommerce_preferences',
						'arkcommerce_preferences' );
    
	// Add Sub menu ARKCommerce Information page
	add_submenu_page(	'arkcommerce_root',
						__( 'ARKCommerce Information', 'arkcommerce' ),
						__( 'Information', 'arkcommerce' ),
						'administrator',
						'arkcommerce_information',
						'arkcommerce_information' );

	// Remove automatically-added Sub menu ARKCommerce
	remove_submenu_page( 'arkcommerce_root','arkcommerce_root' );
}
add_action( 'admin_menu', 'arkcommerce_add_menu_pages' );

//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Admin Preferences Page													//
// @output ARKCommerce Admin Preferences page											//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_preferences() 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$store_currency = get_woocommerce_currency();
	$arktimeout = arkcommerce_get_order_timeout();
	$formchange = false;
	$supported_store_currency = arkcommerce_check_currency_support();
	$arkexchangerate = arkcommerce_get_exchange_rate();
	$arkchosen = '';
	$exoticcurrencychosen = '';
	$adminslist = get_users( ['role__in' => ['administrator', 'shop_manager'] ] );
	$nonce = wp_nonce_field( 'arkcommerce_preferences', 'arkcommerce_preferences_form' );
	
	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' )
	{
		$storewalletaddress = $arkgatewaysettings['darkaddress'];
		$explorerurl = 'https://dexplorer.ark.io/';
	}
	else
	{
		$storewalletaddress = $arkgatewaysettings['arkaddress'];
		$explorerurl = 'https://explorer.ark.io/';
	}
	// Construct an array of possible order expiry timeout options
	$timeoutoptions = array(	110 => ( __( '110 blocks (unpaid order expires in cca 15 min)', 'arkcommerce' ) ),
								225 => ( __( '225 blocks (unpaid order expires in cca 30 min)', 'arkcommerce' ) ),
								450 => ( __( '450 blocks (unpaid order expires in cca 60 min)', 'arkcommerce' ) ),
								900 => ( __( '900 blocks (unpaid order expires in cca 2 hours)', 'arkcommerce' ) ),
								1800 => ( __( '1800 blocks (unpaid order expires in cca 4 hours)', 'arkcommerce' ) ),
								3600 => ( __( '3600 blocks (unpaid order expires in cca 8 hours)', 'arkcommerce' ) ),
								5400 => ( __( '5400 blocks (unpaid order expires in cca 12 hours)', 'arkcommerce' ) ),
								10800 => ( __( '10800 blocks (unpaid order expires in cca 24 hours)', 'arkcommerce' ) ),
								75600 => ( __( '75600 blocks (unpaid order expires in cca 7 days)', 'arkcommerce' ) ),
								151200 => ( __( '151200 blocks (unpaid order expires in cca 2 weeks)', 'arkcommerce' ) ),
								324000 => ( __( '324000 blocks (unpaid order expires in cca 1 month)', 'arkcommerce' ) ),
								'never' => ( __( 'None (order never expires)', 'arkcommerce' ) ) );
	
	// Compose dropdown list of order expiry options
	foreach( $timeoutoptions as $timoutoption => $timeoutvalue ) 
	{
		if( $arktimeout == $timoutoption ) $ddsel = ' selected';
		else $ddsel = '';
		$dropdowntimeout = $dropdowntimeout . '<option value="' . $timoutoption . '"' . $ddsel . '>' . $timeoutvalue . '</option>';
	}
	// Compose dropdown list of admins and display correct radio button and checkbox selections
	foreach( $adminslist as $adminuser ) 
	{
		if( $arkgatewaysettings['arknotify'] == $adminuser->user_email ) $ddsel = ' selected';
		else $ddsel = '';
		$dropdownadmin = $dropdownadmin . '<option value="' . $adminuser->user_email . '"' . $ddsel . '>' . ( $adminuser->display_name . ' ('.$adminuser->user_email . ')' ) . '</option>';
	}
	// Establish which checkboxes and radio options are checked/unchecked
	if( $arkgatewaysettings['arkexchangetype'] == 'autorate' ) $typeautorate = ' checked';
	else $typeautorate = ' unchecked';
	if( $arkgatewaysettings['arkexchangetype'] == 'multirate' ) $typemultirate = ' checked';
	else $typemultirate = ' unchecked';
	if( $arkgatewaysettings['arkexchangetype'] == 'fixedrate' ) $typefixedrate = ' checked';
	else $typefixedrate = ' unchecked';
	if( $arkgatewaysettings['arkdisplaycart'] == 'on' ) $prefinfocartcheck = ' checked';
	else $prefinfocartcheck = ' unchecked';
	if( $arkgatewaysettings['arkdualprice'] == 'on' ) $prefdualpricecheck = ' checked';
	else $prefdualpricecheck = ' unchecked';
	if( $arkgatewaysettings['arkorderexpirednotify'] == 'on' ) $prefnotifyonexpirycheck = ' checked';
	else $prefnotifyonexpirycheck = ' unchecked';
	if( $arkgatewaysettings['arkorderfillednotify'] == 'on' ) $prefnotifyonpaymentcheck = ' checked';
	else $prefnotifyonpaymentcheck = ' unchecked';
	if( $arkgatewaysettings['arkorderplacednotify'] == 'on' ) $prefnotifyonplacementcheck = ' checked';
	else $prefnotifyonplacementcheck = ' unchecked';
	
	// Establish currency matters, various combiation permissions, and set the correct exchange rate (autorate/multirate/fixedrate)
	if( $supported_store_currency === false )
	{
		$exoticcurrencychosen = ' disabled';
	}
	if( $supported_store_currency === false && $arkgatewaysettings['arkexchangetype'] == 'fixedrate' )
	{
		$displayexchangerate = ( '<span class="dashicons dashicons-info" style="color:#4ab6ff;"> </span> <span style="color:black;"><b>' . __( 'Current store exchange rate', 'arkcommerce' ) . '</b>:</span> <i>' . $arkexchangerate . ' ' . $store_currency . ' ' . __( 'per ARK', 'arkcommerce' ) . '</i>' );
	}
	elseif( $supported_store_currency === false && $arkgatewaysettings['arkexchangetype'] != 'fixedrate' )
	{
		$displayexchangerate = ( '<span class="dashicons dashicons-info" style="color:red;"> </span> <span style="color:black;"><b>' . __( 'Unable to determine the exchange rate', 'arkcommerce' ) . '</b>.</span>' );
	}
	elseif( $supported_store_currency === true && $store_currency != 'ARK' )
	{
		$displayexchangerate = ( '<span class="dashicons dashicons-info" style="color:#4ab6ff;"> </span> <span style="color:black;"><b>' . __( 'Current market exchange rate', 'arkcommerce' ) . '</b>:</span> <i>' . $arkgatewaysettings['arkexchangerate'] . ' ' . $store_currency . ' ' . __( 'per ARK', 'arkcommerce' ) . '</i> | <b>' . __( 'Current store exchange rate', 'arkcommerce' ) . '</b>: <i>' . $arkexchangerate . ' ' . $store_currency . ' ' . __( 'per ARK', 'arkcommerce' ) . '</i>' );
	}
	elseif( $supported_store_currency === true && $store_currency == 'ARK' ) 
	{
		$arkchosen = ' disabled';
		$displayexchangerate = ( '<span class="dashicons dashicons-info" style="color:#4ab6ff;"> </span> <span style="color:black;"><b>' . __( 'ARK is the currently chosen default store currency', 'arkcommerce' ) . '</b>.</span>' );
	}
	// Display the form page
	echo( 
			arkcommerce_headers( 'preferences' ) . '
			<hr>
			' . $displayexchangerate . '
			<hr>
			<b>
				' . __( 'ARK wallet address', 'arkcommerce' ) . '
			</b>: 
			<a class="arkcommerce-link" target="_blank" href="' . $explorerurl . 'address/' . $storewalletaddress . '">
				' . $storewalletaddress . '
			</a>
			<i> 
				(' . __( 'opens in the official ARK blockchain explorer application', 'arkcommerce' ) . ')
			</i>
		</div>
		<div>
			<form method="post" action="admin-post.php">
				<input type="hidden" name="action" value="arkcommerce_preferences_form">
					' . $nonce . '
				<table class="form-table">
					<tr>
						<th scope="row" class="titledesc">
							' . __( 'Exchange Rate Settings', 'arkcommerce' ) . '
						</th>
						<td>
							<fieldset>
								<p>
									<input type="radio" name="autoexchange" value="multirate" class="arkcommerce-radio"' . $typemultirate . $arkchosen . $exoticcurrencychosen . '> 
										' . __( 'Automatic exchange rate with multiplication', 'arkcommerce' ) . ' 
										<i>
											(' . __( 'e.g. 1.01 multiplier is 1 percent over market rate', 'arkcommerce' ) . ')
										</i>: 
										<input name="multiplier_rate" type="number" step="0.01" value="' . $arkgatewaysettings['arkmultiplier'] . '" class="arkcommerce-input"' . $arkchosen . $exoticcurrencychosen . '>
									<br>
									<input type="radio" name="autoexchange" value="autorate"' . $typeautorate . ' class="arkcommerce-radio"' . $arkchosen . $exoticcurrencychosen . '> 
										' . __( 'Automatic exchange rate', 'arkcommerce' ) . '
									<br>
									<input type="radio" name="autoexchange" value="fixedrate"' . $typefixedrate . ' class="arkcommerce-radio"' . $arkchosen . '> 
										' . __( 'Fixed exchange rate', 'arkcommerce' ) . ' 
										<i>
											(' . __( 'per ARK', 'arkcommerce' ) . ')
										</i>: 
										<input name="manual_rate" type="number" step="0.01" value="' . $arkgatewaysettings['arkmanual'] . '" class="arkcommerce-input"' . $arkchosen . '>
									<p class="description">
										(' . __( 'Ignored if ARK is the chosen default store currency. Automatic exchange rate for supported currencies sourced through periodic updates from coinmarketcap.com. Unsupported currencies only allow for a fixed exchange rate', 'arkcommerce' ) . ')
									</p>
								</p>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row" class="titledesc">
							' . __( 'Order Expiry', 'arkcommerce' ) . '
						</th>
						<td>
							<fieldset>
								<p>
									<select name="order_expiry" class="arkcommerce-select">
										' . $dropdowntimeout . '
									</select>
									<p class="description">
										(' . __( 'Timeframe within which the customer must carry out the ARK transaction or the order gets automatically cancelled', 'arkcommerce' ) . ')
									</p>
								</p>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row" class="titledesc">
							' . __( 'Customer Information', 'arkcommerce' ) . '
						</th>
						<td>
							<fieldset>
								<p>
									' . __( 'Payment gateway title the customer sees during checkout', 'arkcommerce' ) . ': 
									<input type="text" name="gateway_title" value="' . $arkgatewaysettings['title'] . '">
									<hr>
									' . __( 'Payment method description the customer sees during checkout', 'arkcommerce' ) . ':
									<br>
									<textarea name="gateway_description" rows="2" cols="20" placeholder="" class="arkcommerce-textarea">' . $arkgatewaysettings['description'] . '</textarea>
									<hr>
									' . __( 'Instructions that will be added to the thank you page and emails', 'arkcommerce' ) . ':
									<br>
									<textarea name="gateway_instructions" rows="2" cols="20" placeholder="" class="arkcommerce-textarea">' . $arkgatewaysettings['instructions'] . '</textarea>
									<hr>
									<input type="checkbox" name="display_dual_price" id="display_dual_price"' . $prefdualpricecheck . $arkchosen . '> 
										' . __( 'Display dual prices side by side in chosen default store currency and ARK', 'arkcommerce' ) . '
									<br>
									<input type="checkbox" name="display_info_cart" id="display_info_cart"' . $prefinfocartcheck . '> 
										' . __( 'Display ARK information on cart page', 'arkcommerce' ) . '
									<p class="description">
										(' . __( 'ARK information is displayed in a notice consisting of either just order expiry timeout in case of ARK being the store default currency, or cart total in ARK and order expiry timeout in case of other currency being the store default. This setting does not apply to cart checkout page', 'arkcommerce' ) . ')
									</p>
								</p>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row" class="titledesc">' . __( 'Email Notifications', 'arkcommerce' ) . '
						</th>
						<td>
							<fieldset>
								<p>
									<select name="notify_target" class="arkcommerce-select">
										' . $dropdownadmin . '
										</select>
									<hr>
									<input type="checkbox" name="order_placed_notify" id="order_placed_notify"' . $prefnotifyonplacementcheck . '> 
										' . __( 'Whenever an order is placed via ARKCommerce payment gateway', 'arkcommerce' ) . '
									<br>
									<input type="checkbox" name="order_filled_notify" id="order_filled_notify"' . $prefnotifyonpaymentcheck . '> 
										' . __( 'Whenever an order is filled via ARKCommerce payment gateway', 'arkcommerce' ) . '
									<br>
									<input type="checkbox" name="order_expired_notify" id="order_expired_notify"' . $prefnotifyonexpirycheck . '> 
										' . __( 'Whenever an order made via ARKCommerce payment gateway has expired', 'arkcommerce' ) . '
									<p class="description">
										(' . __( 'Notifications contain additional order information and get sent to the chosen administrator or shop manager user email address on selected order events', 'arkcommerce' ) . ')
									</p>
								</p>
							</fieldset>
						</td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" name="submit" id="submit" class="button-primary woocommerce-save-button" value="' . __( 'Save Changes', 'arkcommerce' ) . '">
				</p>
			</form>
			</div>
		</div>' );
	
	// Return success/error message on apply changes attempt
	if( isset( $_GET['error'] ) )
	{
		if( $_GET['error'] == '0' ) echo( '<div id="message" class="notice notice-success"><p><span class="dashicons-before dashicons-arkcommerce" style="vertical-align:middle;"></span> <strong>' . __( 'Your preferences have been saved.', 'arkcommerce' ) . '</strong></p></div>' );
		elseif( $_GET['error'] == '1' ) echo( '<div id="message" class="notice notice-error"><p><span class="dashicons-before dashicons-arkcommerce" style="vertical-align:middle;"></span> <strong>' . __( 'Incorrect entry, please review the failed setting change and retry.', 'arkcommerce' ) . '</strong></p></div>' );
	}
}
//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Preferences Form Handler													//
// @param array $_POST																	//
// @return result URI																	//
// @record  option array woocommerce_ark_gateway_settings								//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_preferences_form() 
{
	// Gather and/or set variables and check nonce
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$formchange = false;
	$formerror = false;
	wp_verify_nonce( $_REQUEST['arkcommerce_preferences'], 'arkcommerce_preferences_form' );
  	
	// Check for value changes in options array
	if( $_POST['multiplier_rate'] != $arkgatewaysettings['arkmultiplier'] ) 
	{
		$arkgatewaysettings['arkmultiplier'] = trim( $_POST['multiplier_rate'] );
		$formchange = true;
	}
	if( $_POST['manual_rate'] != $arkgatewaysettings['arkmanual'] ) 
	{
		$arkgatewaysettings['arkmanual'] = trim( $_POST['manual_rate'] );
		$formchange = true;
	}
	if( $_POST["autoexchange"] != $arkgatewaysettings['arkexchangetype'] ) 
	{
		$arkgatewaysettings['arkexchangetype'] = $_POST["autoexchange"];
		$formchange = true;
	}
	if( $_POST['display_dual_price'] != $arkgatewaysettings['arkdualprice'] ) 
	{
		$arkgatewaysettings['arkdualprice'] = $_POST['display_dual_price'];	
		$formchange = true;
	}
	if( $_POST['display_info_cart'] != $arkgatewaysettings['arkdisplaycart'] ) 
	{
		$arkgatewaysettings['arkdisplaycart'] = $_POST['display_info_cart'];	
		$formchange = true;
	}
	if( $_POST['order_expired_notify'] != $arkgatewaysettings['arkorderexpirednotify'] ) 
	{
		$arkgatewaysettings['arkorderexpirednotify'] = $_POST['order_expired_notify'];
		$formchange = true;
	}
	if( $_POST['order_filled_notify'] != $arkgatewaysettings['arkorderfillednotify'] ) 
	{
		$arkgatewaysettings['arkorderfillednotify'] = $_POST['order_filled_notify'];
		$formchange = true;
	}
	if( $_POST['order_placed_notify'] != $arkgatewaysettings['arkorderplacednotify'] ) 
	{
		$arkgatewaysettings['arkorderplacednotify'] = $_POST['order_placed_notify'];
		$formchange = true;
	}
	if( $_POST['notify_target'] != $arkgatewaysettings['arknotify'] ) 
	{
		$arkgatewaysettings['arknotify'] = $_POST['notify_target'];
		$formchange = true;
	}
	if( intval( $_POST['order_expiry'] ) != $arkgatewaysettings['arktimeout'] ) 
	{
		$arkgatewaysettings['arktimeout'] = intval( $_POST['order_expiry'] );
		$formchange = true;
	}
	if( $_POST['gateway_instructions'] != $arkgatewaysettings['instructions'] ) 
	{
		// Validate and sanitize the value
		if( strlen( $_POST['gateway_instructions'] ) != 0 )
		{
			$arkgatewaysettings['instructions'] = sanitize_text_field( trim( $_POST['gateway_instructions'] ) );
			$formchange = true;
		}
		else $formerror = true;
	}
	if( $_POST['gateway_title'] != $arkgatewaysettings['title'] ) 
	{
		// Validate and sanitize the value
		if( strlen( $_POST['gateway_title'] ) != 0 )
		{
			$arkgatewaysettings['title'] = sanitize_text_field( trim( $_POST['gateway_title'] ) );
			$formchange = true;
		}
		else $formerror = true;
	}
	if( $_POST['gateway_description'] != $arkgatewaysettings['description'] ) 
	{
		// Validate and sanitize the value
		if( strlen( $_POST['gateway_description'] ) != 0 )
		{
			$arkgatewaysettings['description'] = sanitize_text_field( trim( $_POST['gateway_description'] ) );
			$formchange = true;
		}
		else $formerror = true;
	}
	// Check for errors
	if( $formerror === true )
	{
		// Check for valid changes and apply them
		if( $formchange === true && is_admin() )
		{
			update_option( 'woocommerce_ark_gateway_settings', $arkgatewaysettings );
			wp_redirect( admin_url( 'admin.php?page=arkcommerce_preferences&error=1' ) );
		}
		// No valid changes made
		else wp_redirect( admin_url( 'admin.php?page=arkcommerce_preferences&error=1' ) );
	}
	// Update the options array (only admins allowed)
	else
	{
		// Check for changes and apply them
		if( $formchange === true && is_admin() ) 
		{
			update_option( 'woocommerce_ark_gateway_settings', $arkgatewaysettings );
			wp_redirect( admin_url( 'admin.php?page=arkcommerce_preferences&error=0' ) );
		}
		// No changes made
		else wp_redirect( admin_url( 'admin.php?page=arkcommerce_preferences' ) );
	}
}
add_action( 'admin_post_arkcommerce_preferences_form', 'arkcommerce_preferences_form' );

//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Navigator Page															//
// @output ARKCommerce Admin Navigator page												//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_navigator() 
{
	// Gather and/or set variables
	global $wpdb;
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$arkexchangerate = arkcommerce_get_exchange_rate();
	$store_currency = get_woocommerce_currency();
	$arkblockheight = arkcommerce_get_block_height();
	$wallet_balance = arkcommerce_get_wallet_balance();
	$arktxarray = arkcommerce_fetch_transactions( 10 );
	
	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' )
	{
		$storewalletaddress = $arkgatewaysettings['darkaddress'];
		$explorerurl = 'https://dexplorer.ark.io/';
	}
	else
	{
		$storewalletaddress = $arkgatewaysettings['arkaddress'];
		$explorerurl = 'https://explorer.ark.io/';
	}
	// Validate block height response
	if( $arkblockheight != 0 ) $arknodestatus = ( '<span class="dashicons dashicons-info" style="color:lime;"> </span> <b style="color:black;">' . __( 'ARKCommerce operational', 'arkcommerce' ) . '. ' . __( 'ARK block height', 'arkcommerce' ) . ': ' . $arkblockheight . '</b>' );
	else $arknodestatus = ( '<span class="dashicons dashicons-info" style="color:red;"> </span> <b style="color:black;">' . __( 'ARK network unresponsive or unreachable', 'arkcommerce' ) . '.</b>' );
	
	// Display header
	echo( arkcommerce_headers ( 'navigator' ) . '<hr>' . $arknodestatus . '<hr><b>' . __( 'ARK wallet address', 'arkcommerce' ) . ' <a class="arkcommerce-link" target="_blank" href="' . $explorerurl . 'address/' . $storewalletaddress . '">' . $storewalletaddress . '</a> ' . __( 'balance', 'arkcommerce' ) . ': Ѧ' . $wallet_balance . '</b><i> (' . __( 'opens in ARK blockchain explorer application', 'arkcommerce' ) . ')</i></div>' );
	
	// Determine whether the ARK/DARK Node has any hits for the store wallet address query
	if( !empty( $arktxarray[0] ) ) 
	{
		// Form table and iterate rows through the array
		$table_header_tx = '<p><h3>' . __( 'Latest 10 ARK Transactions', 'arkcommerce' ) . '</h3></p><table class="arkcommerce-table"><b><thead><tr><th>' . __( 'Transaction ID', 'arkcommerce' ) . '</th><th>' . __( 'Sender', 'arkcommerce' ) . '</th><th>' . __( 'Amount', 'arkcommerce' ) . ' (Ѧ)</th><th>SmartBridge</th></thead></b></tr>';
		foreach( $arktxarray as $arktx ):setup_postdata( $arktx );
			$content .= ( '<tr><td><a target="_blank" href="' . $explorerurl . 'tx/' . $arktx["id"] . '">' . $arktx["id"] . '</a></td><td><a target="_blank" href="' . $explorerurl . 'address/' . $arktx["senderId"] . '">' . $arktx["senderId"] . '</a></td><td>' . number_format( ( float ) $arktx["amount"] / 100000000, 8, '.', '' ) . '</td><td>' . $arktx["vendorField"] .'</td></tr>' );
		endforeach;
		$content .= ( '</table>' );
			
		//Output
		echo( $table_header_tx . $content );
	}
	// No transactions
	else echo( '<span class="dashicons dashicons-info" style="color:red;"> </span> <b style="color:black;">' . __( 'No incoming transactions found.', 'arkcommerce' ) . '.</b>' );
	
	// Construct a query for all WC orders made using ARKCommerce payment gateway
	$arkordersquery = ( "SELECT post_id FROM " . $wpdb->postmeta . " WHERE meta_value='ark_gateway' ORDER BY post_id DESC LIMIT 10;" );
	
	// Execute the query
	$arkorders = $wpdb->get_results( $arkordersquery );
	
	// Determine valid database connection
	if( !empty( $arkorders ) ) 
	{
		// Conclude with a table containing information on last 10 ARKCommerce payment gateway orders
		$table_header_orders = '<p><h3>' . __( 'Latest 10 ARKCommerce Orders', 'arkcommerce' ) . '</h3></p><table class="arkcommerce-table"><b><thead><tr><th>' . __( 'Order ID', 'arkcommerce' ) . '</th><th>' . __( 'Order Total (Ѧ)', 'arkcommerce' ) . '</th><th>' . __( 'Order Status', 'arkcommerce' ) . '</th><th>' . __( 'Order Block', 'arkcommerce' ) . '</th><th>' . __( 'Payment Block', 'arkcommerce' ) . '</th><th>' . __( 'Expiry Block', 'arkcommerce' ) . '</th><th>' . __( 'Transaction ID', 'arkcommerce' ) . '</th></thead></b></tr>';
		foreach( $arkorders as $arkorder ):setup_postdata( $arkorder );
			$order = wc_get_order( $arkorder->post_id );
			$ark_order_data = $order->get_data();
			if( $order->get_meta( $key = 'ark_transaction_id' ) != null ) $arktxlink = ( '<a target="_blank" href="' . $explorerurl . 'tx/' . $order->get_meta( $key = 'ark_transaction_id' ) . '">TX ID</a>' );
			else $arktxlink = __( 'N/A', 'arkcommerce' );
			if( $order->get_meta( $key = 'ark_payment_block' ) != null ) $arkpaymentblock = $order->get_meta( $key = 'ark_payment_block' );
			else $arkpaymentblock = __( 'N/A', 'arkcommerce' );
			$wcorderlink = admin_url( 'post.php?post=' . $arkorder->post_id . '&action=edit' );
			$ordercontent .= ( '<tr><td><a target="_blank" href="' . $wcorderlink . '">' . $arkorder->post_id . '</a></td><td>' . number_format( ( float ) $order->get_meta( $key = 'ark_total' ), 8, '.', '' ) . '</td><td>' . $ark_order_data['status'] . '</td><td>' . $order->get_meta( $key = 'ark_order_block' ) . '</td><td>' . $arkpaymentblock . '</td><td>' . $order->get_meta( $key = 'ark_expiration_block' ) . '</td><td>' . $arktxlink . '</td></tr>' );
		endforeach;
		$ordercontent .= ( '</table><br>' );
		
		//Output
		echo( $table_header_orders . $ordercontent );
	}
	echo( '</div>' );
}
//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Information Page															//
// @output ARKCommerce Admin Information page											//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_information() 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	
	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' ) $explorerurl = 'https://dexplorer.ark.io/';
	else $explorerurl = 'https://explorer.ark.io/';
	
	// Determine whether ARKCommerce is enabled
	if( $arkgatewaysettings['enabled'] == 'yes' )
	{
		if( $arkgatewaysettings['darkmode'] == 'yes' ) $displayinfo = ( '<span class="dashicons dashicons-info" style="color:black;"> </span> <b>' . __( 'ARKCommerce DARK Mode enabled', 'arkcommerce' ) . '</b>.' );
		else $displayinfo = ( '<span class="dashicons dashicons-info" style="color:#4ab6ff;"> </span> <span style="color:black;"><b>' . __( 'ARKCommerce payment gateway enabled', 'arkcommerce' ) . '</b>.</span>' );
	}
	else $displayinfo = ( '<span class="dashicons dashicons-info" style="color:red;"> </span> <span style="color:black;"><b>' . __( 'ARKCommerce payment gateway disabled', 'arkcommerce' ) . '</b>.</span>' );
	
	// Compose ARKCommerce content
	$usefulinks = sprintf( __( 'ARK Wallet Guides', 'arkcommerce' ) . ': <a class="arkcommerce-link" target="_blank" href="%s">%s</a> | <a class="arkcommerce-link" target="_blank" href="%s">%s</a><br>' . __( 'ARK Blockchain Information', 'arkcommerce' ) . ': <a class="arkcommerce-link" target="_blank" href="%s">%s</a> | <a class="arkcommerce-link" target="_blank" href="%s">%s</a><br>' . __( 'ARK News Sources', 'arkcommerce' ) . ': <a class="arkcommerce-link" target="_blank" href="%s">%s</a> | <a class="arkcommerce-link" target="_blank" href="%s">%s</a> | <a class="arkcommerce-link" target="_blank" href="%s">%s</a><br>' . __( 'WordPress Cron Information', 'arkcommerce' ) . ': <a class="arkcommerce-link" target="_blank" href="%s">%s</a><br>' . __( 'Free ARK', 'arkcommerce' ) . ': <a class="arkcommerce-link" target="_blank" href="%s">%s</a><br>' . __( 'Free DARK', 'arkcommerce' ) . ': <a class="arkcommerce-link" target="_blank" href="%s">%s</a>', 'https://blog.ark.io/how-to-generate-your-own-ark-address-and-passphrase-5e4e1257ca5e', __( 'ARK Wallet Setup', 'arkcommerce' ), 'https://blog.ark.io/full-ledger-nano-s-hardware-wallet-guide-for-ark-7bf7bfff4cef', __( 'Ledger Nano S Hardware Wallet Guide', 'arkcommerce' ), 'https://blog.ark.io/how-to-vote-or-un-vote-an-ark-delegate-and-how-does-it-all-work-819c5439da68', __( 'ARK Voting', 'arkcommerce' ), $explorerurl, __( 'ARK Blockchain Explorer', 'arkcommerce' ), 'https://blog.ark.io/', __( 'ARK Official Blog', 'arkcommerce' ), 'https://arkecosystem.slack.com', __( 'ARK Community Fund', 'arkcommerce' ), 'https://arkcommunityfund.com/', __( 'ARK Official Slack', 'arkcommerce' ), 'https://developer.wordpress.org/plugins/cron/hooking-into-the-system-task-scheduler/', __( 'Hooking WP-Cron Into the System Task Scheduler', 'arkcommerce' ), 'https://classicdelegate.biz/faucet', __( 'Biz_Classic ARK Community Faucet', 'arkcommerce' ), 'https://kristjank.github.io/dark-paperwallet/', __('DARK Paper Wallet Generator (automatically dispenses free DARK tokens)', 'arkcommerce' ) );
	$commandstring = "define('DISABLE_WP_CRON', true);";
	
	// Display body
	echo( 
			arkcommerce_headers( 'information' ) . '
			<hr>
			' . $displayinfo . '
			<hr>
			<b>
				' . __( 'Thank you for choosing ARKCommerce. This project was supported by Ark Community Fund', 'arkcommerce' ) . '.
			</b>
		</div>
			<p>
				<h3>
					' . __( 'About ARK', 'arkcommerce' ) . '
				</h3>
			</p>
			<p>
				' . __( 'In a sea of carbon copies among crypto currencies, ARK stands out for providing the users, developers, and startups with innovative blockchain technologies aiming to create an entire ecosystem of linked chains and a virtual spiderweb of endless use-cases that make it highly flexible, adaptable, and scalable. Having launched in late March 2017, it has gained many supporters for its DPoS (Delegated Proof of Stake) blockchain securing mechanism as a green alternative to traditional PoW (Proof of Work) cryto currencies, one that enables much faster and less expensive execution of transactions, and one that lends itself well to processing automation. A big win for ARK is its inherent system of governance through user voting with their wallet balances for delegates where the best of them get rewarded with loyalty, longevity, and profit sharing whereas bad actors or low-performance delegates eventually get kicked out of the top 51 forging delegates. These are the only ones that actually "mine" ARK blockchain, receive transaction fees, and reap the rewards (2 ARK per block) stemming from it. As a currency, ARK features a modest inflation rate which makes it suitable as a long-term hold.', 'arkcommerce' ) . '
			</p>
			<p>
				' . __( 'The roadmap for future development is very ambitious for the steadily growing and capable team behind it intends to incorporate various features like ArkVM (Virtual Machine) such as the one found in Ethereum, as well as coming integrations of IPFS (Interplanetary File System) and IPDB (Interplanetary Database) transactions, push-button deployable blockchains, a distributed token exchange, and so on. From this standpoint, ARK is poised to become one of the top contenders for crypto currency throne in the future. The user base and the development ecosystem that has sprung up around ARK is truly fruitful and dedicated, which makes the success of the entire project that much more likely.', 'arkcommerce' ) . '
			</p>
			<p>
				<h3>
					' . __( 'About ARKCommerce', 'arkcommerce' ) . '
				</h3>
			</p>
			<p>
				' . __( 'ARKCommerce is a payment gateway that provides crypto currency payment services for WooCommerce store operators on WordPress platform by utilizing the ARK blockchain. Fully based on open source code and architecture, ARKCommerce aims to provide the necessary e-commerce infrastructure with the goal of wider market acceptance for ARK by both customers and merchants. Online merchants struggle with risk-free and timely digital product delivery via established fiat currency payment intermediaries. This makes for a particularly suitable use case for crypto currency payments that happen in trustless, straightforward, and automated fashion.', 'arkcommerce' ) . '
			</p>
			<p>
				' . __( 'ARKCommerce leverages the versatility, resilience and quickness of the ARK blockchain that features a special field called SmartBridge which enables user input as part of the transaction, whereas 8 second block times facilitate prompt transaction confirmations. All orders placed through ARKCommerce are placed on-hold until repetitive ARK blockchain queries for open orders reveal a transaction for an appropriate amount of ARK and with a correct order reference making a deposit into the monitored ARK wallet address belonging to the store, all without requiring or storing wallet passphrases.', 'arkcommerce' ) . '
			</p>
			<p>
				<h3>
					' . __( 'Quick Guides', 'arkcommerce' ) . '
				</h3>
			</p>
			<p>
				<h4>
					' . __( 'Set ARK as Default Currency', 'arkcommerce' ) . '
				</h4>
			</p>
			<p>
				<strong>
					' . __( 'Step 1', 'arkcommerce' ) . '
				</strong>
				: ' . __( 'Open WordPress administration interface at', 'arkcommerce' ) . ' 
				<a class="arkcommerce-link" target="_blank" href="' . get_site_url() . '/wp-admin' . '">
					' . get_site_url() . '/wp-admin' . '
				</a> 
				' . __( 'and select', 'arkcommerce' ) . ' "WooCommerce" -> "' . __( 'Settings', 'arkcommerce' ) . '"
				<br>
				<strong>
					' . __( 'Step 2', 'arkcommerce' ) . '
				</strong>
				: ' . __( 'Scroll down and select "Currency" dropdown entry "ARK (Ѧ)", and enter "8" into "Number of decimals" field.', 'arkcommerce' ) . '
				<br>
				<strong>
					' . __( 'Step 3', 'arkcommerce' ) . '
				</strong>
				: ' . __( 'Click on the button "Save changes" to apply the changes effective immediately.', 'arkcommerce' ) . '
			</p>
			<p>
				<h4>
					' . __( 'Hard Cron Quick Guide', 'arkcommerce' ) . '
				</h4>
			<p>
				<span style="color:#4ab6ff;" class="dashicons dashicons-clock"> 
				</span> 
				<b style="color:black;"> 
					' . __( 'Both automatic exchange rate synchronization and transaction verification queries depend on "WP-Cron" task being triggered as regularly as possible, therefore in case this store is low-traffic (i.e. does not get hits every minute) it is highly recommended to implement "Hard Cron" solution to ensure proper scheduled task execution. A general guide on how to do so in two steps is found below, however your specific permissions and allowed methods may depend on your hosting provider so refer to them for assistance in case there are difficulties.', 'arkcommerce' ) . '
				</b>
			</p>
			<h5>
				' . __( 'Option 1: Using cPanel', 'arkcommerce' ) . '
			</h5>
			<p>
				<strong>
					' . __( 'Step 1', 'arkcommerce' ) . '
				</strong>: 
				' . __( 'FTP or SSH into your web host, edit "wp-config.php" file which usually resides under "htdocs" folder and place the following code under the first "define" line', 'arkcommerce' ) . ':
				<br>
				<code>
					' . $commandstring . '
				</code>
				<br>
				<strong>
					' . __( 'Step 2', 'arkcommerce' ) . '
				</strong>
				: ' . __( 'Log into cPanel > Advanced > Cron Jobs > Add New > Set the interval to 1 minute by using the string below; in case your hosting provider offers only longer intervals, pick the shortest one but be aware that such a setup is suboptimal', 'arkcommerce' ) . ':
				<br>
				<code>
					wget -q -O - ' . get_site_url() . '/wp-cron.php?doing_wp_cron > /dev/null 2>&1;
				</code>
			</p>
			<h5>
				' . __( 'Option 2: Using Command Line', 'arkcommerce' ) . '
			</h5>
			<p>
				<strong>
					' . __( 'Step 1', 'arkcommerce' ) . '
				</strong>
				: ' . __( 'SSH or connect using Remote Desktop into your web host, edit "wp-config.php" file which usually resides under "htdocs" folder and place the following code under the first "define" line', 'arkcommerce' ) . ':
				<br>
				<code>
					' . $commandstring . '
				</code>
				<br>
				<strong>
					' . __( 'Step 2A (Linux)', 'arkcommerce' ) . '
				</strong>
				: ' . __( 'Edit the crontab with the following command', 'arkcommerce' ) . ':
				<br>
				<code>
					user@system:/$ sudo crontab -e
				</code>
				<br>
				<strong>
					' . __( 'Step 3A', 'arkcommerce' ) . '
				</strong>
				: ' . __( 'Scroll all the way down and insert the following line', 'arkcommerce' ) . ':
				<br>
				<code>
					*/1 * * * * curl ' . get_site_url() . '/wp-cron.php?doing_wp_cron > /dev/null 2>&1
				</code>
				<br>
				<strong>
					' . __( 'Step 2B (Windows)', 'arkcommerce' ) . '
				</strong>
				: ' . __( 'Open the Windows Command Line (CMD.EXE) as Administrator and enter the following command', 'arkcommerce' ) . ':
				<br>
				<code>
					' . "C:\> schtasks /create /sc MINUTE /tn WP-Cron /tr \"cmd.exe 'curl --silent --compressed " . get_site_url() . "/wp-cron.php?doing_wp_cron'\" /ru SYSTEM" . '
				</code>
			</p>
			<p>
				<h3>
					' . __( 'Useful External Links', 'arkcommerce' ) . '
				</h3>
			</p>
			<p>
				' . $usefulinks . '
			</p>
		</div>' );
}
//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Headers for Settings, Preferences, Information, and Navigator pages		//
// @param str $headertype																//
// @return str $header																	//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_headers( $headertype )
{
	// Gather and/or set variables
	$arkcommerce_link = ( '<a class="arkcommerce-link" target="_blank" href="https://arkcommerce.net/">' . __( 'Website', 'arkcommerce' ) . '</a>' );
	$gateway_settings_link = sprintf( '<a class="arkcommerce-link" target="_blank" href="%s"> %s</a>', admin_url( 'admin.php?page=wc-settings&tab=checkout&section=ark_gateway' ), __( 'Settings', 'arkcommerce' ) );
	$gateway_preferences_link = sprintf( '<a class="arkcommerce-link" target="_blank" href="%s">%s</a>', admin_url( 'admin.php?page=arkcommerce_preferences' ), __( 'Preferences', 'arkcommerce' ) );
	$gateway_navigator_link = sprintf( '<a class="arkcommerce-link" target="_blank" href="%s">%s</a>', admin_url( 'admin.php?page=arkcommerce_navigator' ), __( 'Navigator', 'arkcommerce' ) );
	$gateway_information_link = sprintf( '<a class="arkcommerce-link" target="_blank" href="%s">%s</a>', admin_url( 'admin.php?page=arkcommerce_information' ), __( 'Information', 'arkcommerce' ) );
	$arkcommerce_logo = ( plugin_dir_url( __FILE__ ) . 'assets/images/arkcommerce.png' );
	$ark_links = ( '|<b> ARK </b><a class="arkcommerce-link" target="_blank" href="https://ark.io/">' . __( 'Website', 'arkcommerce' ) . '</a> | <a class="arkcommerce-link" target="_blank" href="https://arkecosystem.github.io/ark-lite-wallet/app/">' . __( 'Online Wallet', 'arkcommerce' ) . '</a> | <a class="arkcommerce-link" target="_blank" href="https://github.com/ArkEcosystem/ark-desktop/releases">' . __( 'Desktop Wallet', 'arkcommerce' ) . '</a> | <a class="arkcommerce-link" target="_blank" href="https://github.com/ArkEcosystem/ark-mobile">' . __( 'Mobile Wallet', 'arkcommerce' ) . '</a> | <b>ARKCommerce </b>' );
	
	if( $headertype == 'settings' )
	{
		$header = ( '<span class="dashicons-before dashicons-arkcommerce" style="vertical-align:middle;"> </span><b>ARKCommerce Payment Gateway</b>' );
	}
	elseif( $headertype == 'navigator' )
	{
		$header = ( '<div class="wrap"><p><h1>ARKCommerce ' . __( 'Navigator', 'arkcommerce' ) . '</h1></p><div class="arkcommerce-wrap"><img width="100" height="80" alt="ARKCommerce" class="arkcommerce-pic-left" src="' . $arkcommerce_logo . '">' . $ark_links . $arkcommerce_link . ' | ' . $gateway_settings_link . ' | ' . $gateway_preferences_link . ' | ' . $gateway_information_link . ' |<img width="80" height="80" alt="spika" class="arkcommerce-pic-right" src="' . plugin_dir_url( __FILE__ ) . 'assets/images/spika.png' . '">' );
	}
	elseif( $headertype == 'preferences' )
	{
		$header = ( '<div class="wrap"><p><h1>ARKCommerce ' . __( 'Preferences', 'arkcommerce' ) . '</h1></p><div class="arkcommerce-wrap"><img width="100" height="80" alt="ARKCommerce" class="arkcommerce-pic-left" src="' . $arkcommerce_logo . '">' . $ark_links . $arkcommerce_link . ' | ' . $gateway_settings_link . ' | ' . $gateway_navigator_link . ' | ' . $gateway_information_link . ' |<img width="80" height="80" alt="QRCODE" class="arkcommerce-pic-right" src="' . plugin_dir_url( __FILE__ ) . 'assets/images/qrcode.png' . '">' );
	}
	elseif( $headertype == 'information' )
	{
		$header = ( '<div class="wrap"><p><h1>ARKCommerce ' . __( 'Information', 'arkcommerce' ) . '</h1></p><div class="arkcommerce-wrap"><img width="100" height="80" alt="ARKCommerce" class="arkcommerce-pic-left" src="' . $arkcommerce_logo . '">' . $ark_links . $arkcommerce_link . ' | ' . $gateway_settings_link . ' | ' . $gateway_preferences_link . ' | ' . $gateway_navigator_link . ' |<img width="80" height="80" alt="spika" class="arkcommerce-pic-right" src="' . plugin_dir_url( __FILE__ ) . 'assets/images/spika.png' . '">' );
	}
	return $header;
}
//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Footer on All ARKCommerce Pages											//
// @output ARKCommerce Footer															//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_footer_message() 
{
	// Form footer text
	$arkfooter = sprintf( __( 'If you enjoy using <b>ARKCommerce</b> please leave us a %s rating on %s.', 'arkcommerce' ), "<a href='https://wordpress.org/plugins/arkcommerce/' target='_blank'>&#9733;&#9733;&#9733;&#9733;&#9733;</a>", "<a href='https://wordpress.org/plugins/arkcommerce/' target='_blank'>WordPress.org</a>" );
	
	// Display footer
	echo( '<span id="footer-thankyou">' . $arkfooter . '</span>' );
}
if( isset( $_GET["page"] ) && $_GET["page"] == "arkcommerce_information" ) add_filter( 'admin_footer_text', 'arkcommerce_footer_message' );
if( isset( $_GET["page"] ) && $_GET["page"] == "arkcommerce_preferences" ) add_filter( 'admin_footer_text', 'arkcommerce_footer_message' );
if( isset( $_GET["page"] ) && $_GET["page"] == "arkcommerce_navigator" ) add_filter( 'admin_footer_text', 'arkcommerce_footer_message' );
if( isset( $_GET["page"] ) && isset( $_GET["section"] ) && $_GET["page"] == "wc-settings" && $_GET["section"] == "ark_gateway" ) add_filter( 'admin_footer_text', 'arkcommerce_footer_message' );

//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Footer Version Display on All ARKCommerce Pages							//
// @output ARKCommerce Footer Version													//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_footer_version()
{
	// Form version footer text
	$arkversion = ( '<small>ARKCommerce ' . ARKCOMMERCE_VERSION . '</small>' );
	
	// Display version footer
	echo $arkversion;
}
if( isset( $_GET["page"] ) && $_GET["page"] == "arkcommerce_information" ) add_filter( 'update_footer', 'arkcommerce_footer_version', 11 );
if( isset( $_GET["page"] ) && $_GET["page"] == "arkcommerce_preferences" ) add_filter( 'update_footer', 'arkcommerce_footer_version', 11 );
if( isset( $_GET["page"] ) && $_GET["page"] == "arkcommerce_navigator" ) add_filter( 'update_footer', 'arkcommerce_footer_version', 11 );
if( isset( $_GET["page"] ) && isset( $_GET["section"] ) && $_GET["page"] == "wc-settings" && $_GET["section"] == "ark_gateway" ) add_filter( 'update_footer', 'arkcommerce_footer_version', 11 );

//////////////////////////////////////////////////////////////////////////////////////////
// END OF ARKCOMMERCE GUI																//
//////////////////////////////////////////////////////////////////////////////////////////