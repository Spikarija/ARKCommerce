<?php
/*
Plugin Name: ARKCommerce
Plugin URI:  https://www.arkcommerce.net
Description: ARKCommerce is a payment gateway that takes advantage of the open infrastructure for the ARK blockchain, and enables crypto currency payment services for WooCommerce store operators on WordPress platform. It does so without requiring or storing wallet passphrases. Fully based on open source code with the goal of wider market acceptance of ARK.
Version:     1.1.0
Author:      Spika
Author URI:  https://github.com/Spikarija/ARKCommerce
License:     MIT License
Text Domain: arkcommerce
Domain Path: /languages
WC requires at least: 3.2.4
WC tested up to: 3.4.2

ARKCommerce
Copyright (C) 2017-2018 Milan Semen

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
//////////////////////////////////////////////////////////////////////////////////////////
// START OF ARKCOMMERCE																	//
//////////////////////////////////////////////////////////////////////////////////////////
define( 'ARKCOMMERCE_VERSION', '1.1.0' );

// Prohibit direct access
if( !defined( 'ABSPATH' ) ) exit;

// Make sure WooCommerce is active
if( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

//////////////////////////////////////////////////////////////////////////////////////////
// Load Plugin Textdomain 'arkcommerce'	Which Allows for Gettext-based Lcalisation		//
// PLUGIN_DIR//languages/arkcommerce.pot is the main template file						//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_load_textdomain() 
{
	// Textdomain is arkcommerce
	load_plugin_textdomain( 'arkcommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'arkcommerce_load_textdomain' );

//////////////////////////////////////////////////////////////////////////////////////////
// Add wp_cron Minutely and Biminutely Interval Schedules								//
// @param array $schedules																//
// @return array $schedules																//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_wpcron_schedule( $schedules ) 
{
	// Create 120s schedule and add to array
	$schedules['arkcommerce_biminutely'] = array(
				'interval'  => 120,
				'display'   => __( 'Biminutely', 'arkcommerce' ) );
	
	// Create 60s schedule and add to array
	$schedules['arkcommerce_minutely'] = array(
				'interval'  => 60,
				'display'   => __( 'Minutely', 'arkcommerce' ) );
    return $schedules;
}
add_filter( 'cron_schedules', 'arkcommerce_wpcron_schedule' );

//////////////////////////////////////////////////////////////////////////////////////////
// Register Activation/Feactivation/Uninstall Hooks										//
//////////////////////////////////////////////////////////////////////////////////////////
register_activation_hook( __FILE__, 'arkcommerce_activation' );
register_deactivation_hook( __FILE__, 'arkcommerce_deactivation' );
register_uninstall_hook( __FILE__, 'arkcommerce_uninstall' );

//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Plugin Activation Function												//
// @record option array woocommerce_ark_gateway_settings								//
// @record start scheduled tasks														//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_activation() 
{
    // Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$adminslist = get_users( array( 'role' => 'administrator' ) );
	foreach( $adminslist as $adminuser ) $adminusermail = $adminuser->user_email;
	
	// Set default ARKCommerce options values array if none exist already
	// Add ARKCommerce enable/disable switch to options array
	if( empty( $arkgatewaysettings['enabled'] ) ) $arkgatewaysettings['enabled'] = 'no';
	
	// Add ARKCommerce notification target administrator (last from the set of fetched admins) to options array
	if( empty( $arkgatewaysettings['arknotify'] ) ) $arkgatewaysettings['arknotify'] = $adminusermail;
	
	// Add ARKCommerce order fulfillment admin notification to options array
	if( empty( $arkgatewaysettings['arkorderfillednotify'] ) ) $arkgatewaysettings['arkorderfillednotify'] = 'on';
	
	// Add ARKCommerce order placement admin notification to options array
	if( empty( $arkgatewaysettings['arkorderplacednotify'] ) ) $arkgatewaysettings['arkorderplacednotify'] = 'on';
	
	// Add ARKCommerce order expiry admin notification to options array
	if( empty( $arkgatewaysettings['arkorderexpirednotify'] ) ) $arkgatewaysettings['arkorderexpirednotify'] = 'on';
	
	// Add ARKCommerce initial exchange rate to options array
	if( empty( $arkgatewaysettings['arkexchangerate'] ) ) $arkgatewaysettings['arkexchangerate'] = arkcommerce_update_exchange_rate();
	
	// Add ARKCommerce store exchange rate type to options array: autorate/multirate/fixedrate
	if( empty( $arkgatewaysettings['arkexchangetype'] ) ) $arkgatewaysettings['arkexchangetype'] = 'autorate';
	
	// Add ARKCommerce store exchange rate multiplier to options array
	if( empty( $arkgatewaysettings['arkmultiplier'] ) ) $arkgatewaysettings['arkmultiplier'] = 1.01;
	
	// Add ARKCommerce store manual exchange rate to options array
	if( empty( $arkgatewaysettings['arkmanual'] ) ) $arkgatewaysettings['arkmanual'] = null;
	
	// Add ARKCommerce order expiry to options array
	if( empty( $arkgatewaysettings['arktimeout'] ) ) $arkgatewaysettings['arktimeout'] = 225;
	
	// Add ARKCommerce dual price display option to options array
	if( empty( $arkgatewaysettings['arkdualprice'] ) ) $arkgatewaysettings['arkdualprice'] = 'on';
	
	// Add ARKCommerce cart display option to options array
	if( empty( $arkgatewaysettings['arkdisplaycart'] ) ) $arkgatewaysettings['arkdisplaycart'] = 'on';
	
	// Add ARKCommerce ARK Node hostname to options array (standard relay node)
	if( empty( $arkgatewaysettings['arknode'] ) ) $arkgatewaysettings['arknode'] = 'arknode.arkcommerce.net';
	
	// Add ARKCommerce DARK Node hostname to options array (standard relay node)
	if( empty( $arkgatewaysettings['darknode'] ) ) $arkgatewaysettings['darknode'] = 'darknode.arkcommerce.net';
	
	// Add ARKCommerce ARK/DARK encrypted communication flag to options array
	if( empty( $arkgatewaysettings['nodeencryption'] ) ) $arkgatewaysettings['nodeencryption'] = 'yes';
	
	// Add ARKCommerce DARK Mode option to options array
	if( empty( $arkgatewaysettings['darkmode'] ) ) $arkgatewaysettings['darkmode'] = '';
	
	// Add ARKCommerce store ARK wallet address to options array
	if( empty( $arkgatewaysettings['arkaddress'] ) ) $arkgatewaysettings['arkaddress'] = '';
	
	// Add ARKCommerce store DARK wallet address to options array
	if( empty( $arkgatewaysettings['darkaddress'] ) ) $arkgatewaysettings['darkaddress'] = '';
	
	// Add ARKCommerce payment title to options array
	if( empty( $arkgatewaysettings['title'] ) ) $arkgatewaysettings['title'] = __( 'ARK Payment', 'arkcommerce' );
	
	// Add ARKCommerce payment description to options array
	if( empty( $arkgatewaysettings['description'] ) ) $arkgatewaysettings['description'] = __( 'Pay for your purchase with ARK crypto currency by making a direct transaction to the ARK wallet address of the store.', 'arkcommerce' );
	
	// Add ARKCommerce order instructions to options array
	if( empty( $arkgatewaysettings['instructions'] ) ) $arkgatewaysettings['instructions'] = __( 'Please carry out the ARK transaction using the supplied data. Be aware of the ARK network fee (0.1 ARK) and do not use an exchange wallet for the transaction.', 'arkcommerce' );
	
	// Add ARKCommerce service status to options array
	if( empty( $arkgatewaysettings['arkservice'] ) ) $arkgatewaysettings['arkservice'] = 0;
	
	// Update the ARKCommerce plugin settings array	
	update_option( 'woocommerce_ark_gateway_settings', $arkgatewaysettings );
	
	// Plugin upgrade from v1.0.x to v1.1.0 (if applicable)
	if( !empty( $arkgatewaysettings['arkapikey'] ) ) arkcommerce_upgrade_plugin_once();
	
	// Conclude with the exchange rate update and order lookup Cron jobs
    if( !wp_next_scheduled( 'arkcommerce_refresh_exchange_rate' ) ) wp_schedule_event( time(), 'arkcommerce_biminutely', 'arkcommerce_refresh_exchange_rate' );
	if( !wp_next_scheduled( 'arkcommerce_check_for_open_orders' ) ) wp_schedule_event( time(), 'arkcommerce_minutely', 'arkcommerce_check_for_open_orders' );
}
//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Plugin Deactivation Function												//
// @record kill scheduled tasks															//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_deactivation() 
{
	// Kill recurring tasks
	wp_clear_scheduled_hook( 'arkcommerce_refresh_exchange_rate' );
	wp_clear_scheduled_hook( 'arkcommerce_check_for_open_orders' );
}
//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Plugin Uninstall Function												//
// @record remove arr arkgatewaysettings												//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_uninstall()
{
	// Gather and/or set variables
	$arkgatewaysettings = 'woocommerce_ark_gateway_settings';
	
	// Remove ARKCommerce configuration array entry for Multi Site or Single Site deployment
	if( is_multisite() ) delete_site_option( $arkgatewaysettings );
	else delete_option( $arkgatewaysettings );
}
//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Plugin Upgrade Function for Existing Deployments							//
// @record option array woocommerce_ark_gateway_settings								//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_upgrade_plugin_once()
{
	// Fetch current settings
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
		
	// Remove deprecated ARKCommerce Node-related settings from the options array
	unset( $arkgatewaysettings['nodeapikey'] );
	unset( $arkgatewaysettings['darkapikey'] );
	unset( $arkgatewaysettings['arkapikey'] );
	unset( $arkgatewaysettings['arkusername'] );
	unset( $arkgatewaysettings['arkpassword'] );
	unset( $arkgatewaysettings['arkemail'] );
	unset( $arkgatewaysettings['arknodeadmin'] );
	
	// Change ARKCommerce ARK Node hostname in options array (standard relay node)
	$arkgatewaysettings['arknode'] = 'arknode.arkcommerce.net';
	
	// Add ARKCommerce DARK Node hostname to options array (standard relay node)
	$arkgatewaysettings['darknode'] = 'darknode.arkcommerce.net';
	
	// Add ARKCommerce ARK/DARK encrypted communication flag to options array
	$arkgatewaysettings['nodeencryption'] = 'yes';
	
	// Record updated settings
	update_option( 'woocommerce_ark_gateway_settings', $arkgatewaysettings );
}
//////////////////////////////////////////////////////////////////////////////////////////
// QR Code Generator, ARKCommerce Modules Inclusion										//
//////////////////////////////////////////////////////////////////////////////////////////
if( file_exists( plugin_dir_path( __FILE__ ) . 'includes/phpqrcode.php' ) ) include( plugin_dir_path( __FILE__ ) . 'includes/phpqrcode.php' );
if( file_exists( plugin_dir_path( __FILE__ ) . 'arkfaqwidget.php' ) ) include( plugin_dir_path( __FILE__ ) . 'arkfaqwidget.php' );
if( file_exists( plugin_dir_path( __FILE__ ) . 'arkccwidget.php' ) ) include( plugin_dir_path( __FILE__ ) . 'arkccwidget.php' );
if( file_exists( plugin_dir_path( __FILE__ ) . 'arkcommercegui.php' ) ) include( plugin_dir_path( __FILE__ ) . 'arkcommercegui.php' );
if( file_exists( plugin_dir_path( __FILE__ ) . 'arkcommercewoo.php' ) ) include( plugin_dir_path( __FILE__ ) . 'arkcommercewoo.php' );
if( file_exists( plugin_dir_path( __FILE__ ) . 'arkcommercenotify.php' ) ) include( plugin_dir_path( __FILE__ ) . 'arkcommercenotify.php' );
if( file_exists( plugin_dir_path( __FILE__ ) . 'arkcommerceutil.php' ) ) include( plugin_dir_path( __FILE__ ) . 'arkcommerceutil.php' );

//////////////////////////////////////////////////////////////////////////////////////////
// Add the Gateway to WooCommerce														//
// @param array $gateways all available WC gateways										//
// @return array $gateways all WC gateways + WC_Gateway_ARK								//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_add_to_gateways( $gateways ) 
{
	// Add to gateways array and commit
	$gateways[] = 'WC_Gateway_ARK';
	return $gateways;
}
add_filter( 'woocommerce_payment_gateways', 'arkcommerce_add_to_gateways' );

//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Payment Gateway															//
// @class 		WC_Gateway_ARK															//
// @extends		WC_Payment_Gateway														//
// @package		WooCommerce/Classes/Payment												//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_gateway_init() 
{
	// ARKCommerce payment gateway class
	class WC_Gateway_ARK extends WC_Payment_Gateway 
	{
		public function __construct() 
		{
			// Gather and/or set variables
			$this->id                 = 'ark_gateway';
			$this->icon               = apply_filters( 'woocommerce_gateway_icon', plugin_dir_url( __FILE__ ) . 'assets/images/arkicon.png' );
			$this->has_fields         = false;
			$this->method_title       = 'ARKCommerce';
			$this->method_description = arkcommerce_headers( 'settings' );
			
			// Load the settings
			$this->init_form_fields();
			$this->init_settings();
			
			// Define variables
			$this->arknotify				= $this->get_option( 'arknotify' );
			$this->arkorderfillednotify		= $this->get_option( 'arkorderfillednotify' );
			$this->arkorderplacednotify		= $this->get_option( 'arkorderplacednotify' );
			$this->arkorderexpirednotify	= $this->get_option( 'arkorderexpirednotify' );
			$this->arkdisplaycart			= $this->get_option( 'arkdisplaycart' );
			$this->arkexchangetype			= $this->get_option( 'arkexchangetype' );
			$this->arkexchangerate			= $this->get_option( 'arkexchangerate' );
			$this->arkmultiplier			= $this->get_option( 'arkmultiplier' );
			$this->arkmanual				= $this->get_option( 'arkmanual' );
			$this->arktimeout				= $this->get_option( 'arktimeout' );
			$this->arkdualprice				= $this->get_option( 'arkdualprice' );
			$this->enabled					= $this->get_option( 'enabled' );
			$this->title					= $this->get_option( 'title' );
			$this->description				= $this->get_option( 'description' );
			$this->arkaddress				= $this->get_option( 'arkaddress' );
			$this->darkaddress				= $this->get_option( 'darkaddress' );
			$this->arknode					= $this->get_option( 'arknode' );
			$this->darknode					= $this->get_option( 'darknode' );
			$this->nodeencryption			= $this->get_option( 'nodeencryption' );
			$this->darkmode					= $this->get_option( 'darkmode' );
			$this->arkservice				= $this->get_option( 'arkservice' );
			$this->instructions				= $this->get_option( 'instructions', $this->description );
			
			// Actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'arkcommerce_order_placed_page' ) );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, 'arkcommerce_generate_qr_code' );
			add_action( 'woocommerce_email_before_order_table', array( $this, 'arkcommerce_order_placed_email' ), 10, 3 );
			
			// Filters
			add_filter( 'woocommerce_settings_api_sanitized_fields_' . $this->id, array( $this, 'sanitize_settings_array' ) );
		}
//////////////////////////////////////////////////////////////////////////////////////////
//		 Initialize ARKCommerce Payment Gateway Settings Form Fields					//
//////////////////////////////////////////////////////////////////////////////////////////
		public function init_form_fields() 
		{
	  		$this->form_fields = apply_filters( 'wc_ark_gateway_fields', array(
				'enabled'	 		=> array(
					'title'			=> __( 'Enable/Disable', 'arkcommerce' ),
					'type'			=> 'checkbox',
					'description'	=> __( 'Turn on ARKCommerce payment gateway to offer it to customers on checkout.', 'arkcommerce' ),
					'label'			=> __( 'Enable ARK Payments', 'arkcommerce' ),
					'default'		=> 'no',
					'desc_tip'		=> true, ),
				'arkaddress' 		=> array(
					'title'			=> __( 'ARK Wallet Address', 'arkcommerce' ),
					'type'			=> 'text',
					'description'	=> __( 'The ARK wallet address to be used for payments to his store.', 'arkcommerce' ),
					'default'		=> '',
					'desc_tip'		=> true, ),
				'arknode' 			=> array(
					'title'			=> __( 'ARK Node Address', 'arkcommerce' ),
					'type'			=> 'text',
					'description'	=> __( 'IP address or the hostname of an ARK Mainnet node used to query the blockchain. If port is left out, the plugin uses either 443 for https or 80 for http connections. For directly accessible nodes the default port is 4001 and without https encryption.', 'arkcommerce' ),
					'default'		=> 'api.arkcommerce.net',
					'desc_tip'		=> true, ),
				'nodeencryption'	=> array(
					'title'			=> __( 'Connection Encryption', 'arkcommerce' ),
					'type'			=> 'checkbox',
					'description'	=> __( 'Turn on https encrypted communication with ARK/DARK Node API.', 'arkcommerce' ),
					'label'			=> __( 'Https encrypted/http unencrypted ARK/DARK Node Communication', 'arkcommerce' ),
					'default'		=> 'no',
					'desc_tip'		=> true, ),
				'darkmode' 			=> array(
					'title'			=> __( 'DARK Mode (sandbox)', 'arkcommerce' ),
					'type'			=> 'checkbox',
					'description'	=> __( 'DARK Mode is for testing purposes only; when it is enabled, ARKCommerce connects to the ARK Devnet blockchain and uses the supplied DARK wallet address.', 'arkcommerce' ),
					'label'			=> __( 'Enable DARK Mode (for testing purposes only)', 'arkcommerce' ),
					'default'		=> 'no',
					'desc_tip'		=> true, ),
				'darknode' 			=> array(
					'title'			=> __( 'DARK Node Address', 'arkcommerce' ),
					'type'			=> 'text',
					'description'	=> __( 'IP address or the hostname of an ARK Devnet node used to query the blockchain. If port is left out, the plugin uses either 443 for https or 80 for http connections. For directly accessible nodes the default port is 4002 and without https encryption.', 'arkcommerce' ),
					'default'		=> '',
					'desc_tip'		=> true, ),
				'darkaddress' 		=> array(
					'title'			=> __( 'DARK Wallet Address', 'arkcommerce' ),
					'type'			=> 'text',
					'description'	=> __( 'The DARK wallet address to be used for payment testing in DARK Mode.', 'arkcommerce' ),
					'default'		=> '',
					'desc_tip'		=> true, ) ) );
		}
//////////////////////////////////////////////////////////////////////////////////////////
// 		Validate ARK Wallet Address Field												//
// 		@param str $key																	//
//		@param str $value																//
//		@return str $value																//
//////////////////////////////////////////////////////////////////////////////////////////
		public function validate_arkaddress_field( $key, $value )
		{
			// Check if the ARK address is exactly 34 characters long and starts with 'A', throw an error if not
			if( strlen( trim( $value ) ) == 34 && strpos( trim( $value ), 'A' ) === 0 ) return trim( $value );
			else WC_Admin_Settings::add_error( esc_html( __( 'Error in ARK Address formatting.', 'arkcommerce' ) ) );
		}
//////////////////////////////////////////////////////////////////////////////////////////
// 		Validate DARK Wallet Address Field												//
// 		@param str $key																	//
//		@param str $value																//
//		@return str $value																//
//////////////////////////////////////////////////////////////////////////////////////////
		public function validate_darkaddress_field( $key, $value )
		{
			// Check if the ARK address is exactly 34 characters long and starts with 'A', throw an error if not
			if( !empty( $value ) )
			{
				if ( strlen( trim( $value ) ) == 34 && strpos( trim( $value ), 'D' ) === 0 ) return trim( $value );
				else WC_Admin_Settings::add_error( esc_html( __( 'Error in DARK Address formatting.', 'arkcommerce' ) ) );
			}
		}
//////////////////////////////////////////////////////////////////////////////////////////
// 		Sanitize Settings 																//
// 		@param arr $settings															//
//		@return arr $settings															//
//////////////////////////////////////////////////////////////////////////////////////////		
		public function sanitize_settings_array( $settings )
		{
			if( isset( $settings) )
			{
				// Sanitize ARK wallet address
				if( isset( $settings['arkaddress'] ) ) $settings['arkaddress'] = sanitize_text_field( trim( $settings['arkaddress'] ) );
				
				// Sanitize DARK wallet address
				if( isset( $settings['darkaddress'] ) ) $settings['darkaddress'] = sanitize_text_field( trim( $settings['darkaddress'] ) );
			}
			// Return sanitized array
			return $settings;
		}
//////////////////////////////////////////////////////////////////////////////////////////
// 		Order Change to On-hold, Reduce Stock, Empty Cart, Notify Admin					//
// 		@param int $order_id															//
//		@return array																	//
//		@record post order																//
//////////////////////////////////////////////////////////////////////////////////////////
		public function process_payment( $order_id ) 
		{
			// Gather and/or set variables
			global $woocommerce;
			$arkopenorders = arkcommerce_open_order_queue_count();
			$order = wc_get_order( $order_id );
			$ark_neworder_data = $order->get_data();
			$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
			$ark_neworder_currency = get_woocommerce_currency();
			$arkblockheight = arkcommerce_get_block_height();
			$arkexchangerate = arkcommerce_get_exchange_rate();
			$storewalletaddress = $arkgatewaysettings['arkaddress'];
			
			// Check for max open order count
			if( intval( $arkopenorders ) <= 48 )
			{
				// DARK Mode settings
				if( $arkgatewaysettings['darkmode'] == 'yes' ) $storewalletaddress = $arkgatewaysettings['darkaddress'];
			
				// Establish if ARK is default currency and convert to arktoshi
				if( $ark_neworder_currency == 'ARK' ) $ark_neworder_total = $ark_neworder_data['total'] * 100000000;
			
				// Convert from fiat currency into ARK and subsequent result into arktoshi
				else 
				{
					$ark_converted_total = arkcommerce_conversion_into_ark( $ark_neworder_data['total'] );
					$ark_neworder_total = $ark_converted_total * 100000000;
				}
			
				// Validate order in arktoshi not being zero due to conversion error and validate block height
				if( $ark_neworder_total != 0 && $arkblockheight != 0 ) 
				{
					if( $arkgatewaysettings['arktimeout'] == 'never' ) 
					{
						// Set order expiration to never
						$arkorderexpiryblock = 'never';

						// Record order metadata
						$order->update_meta_data( 'ark_total', ( $ark_neworder_total / 100000000 ) );
						$order->update_meta_data( 'ark_arktoshi_total', $ark_neworder_total );
						$order->update_meta_data( 'ark_store_currency', $ark_neworder_currency );
						$order->update_meta_data( 'ark_order_block', $arkblockheight );
						$order->update_meta_data( 'ark_expiration_block', $arkorderexpiryblock );
						$order->update_meta_data( 'ark_exchange_rate', $arkexchangerate );
						$order->update_meta_data( 'ark_store_wallet_address', $storewalletaddress );
						$order->save();
					
						// Mark as on-hold (awaiting the payment) and notify admin of triggering the initial payment check
						$ark_order_onhold_note = ( __( 'Awaiting initial ARK transaction check for the order. Current block height', 'arkcommerce' ) . ': ' . $arkblockheight . '. ' . __( 'The order does not expire.', 'arkcommerce' ) );
						$order->update_status( 'on-hold', $ark_order_onhold_note );
					}
					else 
					{
						// Calculate order expiration
						$arkorderexpiryblock = ( $arkblockheight + $arkgatewaysettings['arktimeout'] );
					
						// Record order metadata
						$order->update_meta_data( 'ark_total', ( $ark_neworder_total / 100000000 ) );
						$order->update_meta_data( 'ark_arktoshi_total', $ark_neworder_total );
						$order->update_meta_data( 'ark_store_currency', $ark_neworder_currency );
						$order->update_meta_data( 'ark_order_block', $arkblockheight );
						$order->update_meta_data( 'ark_expiration_block', $arkorderexpiryblock );
						$order->update_meta_data( 'ark_exchange_rate', $arkexchangerate );
						$order->update_meta_data( 'ark_store_wallet_address', $storewalletaddress );
						$order->save();
					
						// Mark as on-hold (awaiting the payment) and notify admin of triggering the initial payment check
						$ark_order_onhold_note = ( __( 'Awaiting initial ARK transaction check for the order. Current block height', 'arkcommerce' ) . ': ' . $arkblockheight . '. ' . __( 'This order expires at block height:', 'arkcommerce' ) . ' ' . $arkorderexpiryblock . '.' );
						$order->update_status( 'on-hold', $ark_order_onhold_note );
					}
					// Reduce stock levels
					wc_reduce_stock_levels( $order_id );
				
					// Remove cart
					WC()->cart->empty_cart();
				
					// Notify admin if enabled
					if( $arkgatewaysettings['arkorderplacednotify'] == 'on' ) arkcommerce_admin_notification( $order_id, 'orderplaced' );
				
					// Return successful result and redirect
					return array( 'result' => 'success', 'redirect' => $this->get_return_url( $order ) );
				}
				// Error: currency conversion error
				elseif( $ark_neworder_total == 0 && $arkblockheight != 0 ) 
				{
					// Output the error notice
					wc_add_notice( ( '<span class="dashicons-before dashicons-arkcommerce"> </span> ' . __( 'Error: ARK currency conversion service unresponsive, please try again later.', 'arkcommerce' ) ), 'error' );
					
					// Mark as cancelled (currency conversion malfunction)
					$ark_order_cancelled_note = __( 'ARKCommerce ARK currency conversion error.', 'arkcommerce' );
					$order->update_status( 'cancelled', $ark_order_cancelled_note );
					return;
				}
				// Error: ARK/DARK Node unresponsive error
				elseif( $ark_neworder_total != 0 && $arkblockheight == 0 ) 
				{
					// Output the error notice
					wc_add_notice( ( '<span class="dashicons-before dashicons-arkcommerce"> </span> ' . __( 'Error: ARK network unresponsive, please try again later.', 'arkcommerce' ) ), 'error' );
					
					// Mark as cancelled (ARK network unresponsive)
					$ark_order_cancelled_note = __( 'ARK network unresponsive or unreachable.', 'arkcommerce' );
					$order->update_status( 'cancelled', $ark_order_cancelled_note );
					return;
				}
			}
			// Open order queue full (>48 open orders) due to ARK/DARK Node API query result count limit (50 total)
			else
			{
				// Output the error notice
				wc_add_notice( ( '<span class="dashicons-before dashicons-arkcommerce"> </span> ' . __( 'Error: ARK payment gateway order queue full, please try again later.', 'arkcommerce' ) ), 'error' );
				
				// Mark as cancelled (queue full)
				$ark_order_cancelled_note = __( 'ARKCommerce open order queue full.', 'arkcommerce' );
				$order->update_status( 'cancelled', $ark_order_cancelled_note );
				return;
			}
		}
//////////////////////////////////////////////////////////////////////////////////////////
// 		Add Content to the Order Received/'Thank You' Page								//
// 		@param WC_Order $order_id														//
// 		@output ARKCommerce information													//
//////////////////////////////////////////////////////////////////////////////////////////
		public function arkcommerce_order_placed_page( $order_id ) 
		{
			if( $this->instructions ) 
			{
				// Gather and/or set variables
				$orderdata = wc_get_order($order_id);
				$arkorderid = $orderdata->get_id();
				$arkprice = $orderdata->get_meta( $key = 'ark_total' );
				$arkcontent = arkcommerce_order_data_content( $arkorderid, $arkprice );
								
				// Output the QR Code, admin-defined instructions, and the ARK data table
				echo $arkcontent;
			}
		}
//////////////////////////////////////////////////////////////////////////////////////////
// 		Add Content to the Order Email Before Order Table								//
// 		@param WC_Order $order															//
//		@param bool $sent_to_admin														//
// 		@param bool $plain_text															//
// 		@output ARKCommerce information													//
//////////////////////////////////////////////////////////////////////////////////////////
		public function arkcommerce_order_placed_email( $order, $sent_to_admin, $plain_text = false ) 
		{
			if( $this->instructions && !$sent_to_admin && $this->id === $order->get_payment_method() && $order->has_status( 'on-hold' ) ) 
			{
				// Gather and/or set variables
				$arkorderid = $order->get_id();
				$arkprice = get_metadata( 'post', $arkorderid, 'ark_total', true );
				$arkcontent = arkcommerce_order_data_content( $arkorderid, $arkprice );				
				
				// Output the QR Code, admin-defined instructions, and the ARK data table
				echo( $arkcontent . PHP_EOL );
			}
		}
	}
}
add_action( 'plugins_loaded', 'arkcommerce_gateway_init', 11 );

//////////////////////////////////////////////////////////////////////////////////////////
// END OF ARKCOMMERCE 																	//
//////////////////////////////////////////////////////////////////////////////////////////