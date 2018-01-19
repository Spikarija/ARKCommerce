<?php
/*
Plugin Name: ARKCommerce
Plugin URI:  https://www.arkcommerce.net
Description: ARKCommerce is a payment gateway that provides the infrastructure for ARK crypto currency payment services for WooCommerce store operators on WordPress platform and does so without requiring or storing wallet passphrases. Fully based on open source code with the goal of wider market acceptance of ARK.
Version:     1.0
Author:      Spika
Author URI:  https://github.com/Spikarija/ARKCommerce
License:     GNU General Public License v3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: arkcommerce
Domain Path: /languages

ARKCommerce
Copyright (C) 2017 Milan Semen

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see https://www.gnu.org/licenses/
*/
//////////////////////////////////////////////////////////////////////////////////////////
//	START OF ARKCOMMERCE																//
//////////////////////////////////////////////////////////////////////////////////////////
define( 'ARKCOMMERCE_VERSION', '1.0.0' );

// Prohibit direct access
if( !defined( 'ABSPATH' ) ) exit;

// Make sure WooCommerce is active
if( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

//////////////////////////////////////////////////////////////////////////////////////////
// Load plugin textdomain 'arkcommerce'	which allows for gettext-based localisation		//
// PLUGIN_DIR//languages/arkcommerce.pot is the main template file						//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_load_textdomain() 
{
	// Textdomain is arkcommerce
	load_plugin_textdomain( 'arkcommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}
add_action( 'plugins_loaded', 'arkcommerce_load_textdomain' );

//////////////////////////////////////////////////////////////////////////////////////////
// Add wp_cron minutely and biminutely interval schedules								//
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
// Register activation/deactivation/uninstall hooks										//
//////////////////////////////////////////////////////////////////////////////////////////
register_activation_hook( __FILE__, 'arkcommerce_activation' );
register_deactivation_hook( __FILE__, 'arkcommerce_deactivation' );
register_uninstall_hook( __FILE__, 'arkcommerce_uninstall' );

//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce plugin activation function												//
// @record array arkgatewaysettings														//
// @output scheduled tasks																//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_activation() 
{
    // Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$adminslist = get_users( array( 'role' => 'administrator' ) );
	foreach( $adminslist as $adminuser ) $adminusermail = $adminuser->user_email;
	
	// Set default ARKCommerce values if none exist
	// Add ARKCommerce enable/disable switch to array
	if( empty( $arkgatewaysettings['enabled'] ) ) $arkgatewaysettings['enabled'] = 'no';
	
	// Add ARKCommerce notification target administrator (last from the set of fetched admins) to array
	if( empty( $arkgatewaysettings['arknotify'] ) ) $arkgatewaysettings['arknotify'] = $adminusermail;
	
	// Add ARKCommerce order fulfillment admin notification to array
	if( empty( $arkgatewaysettings['arkorderfillednotify'] ) ) $arkgatewaysettings['arkorderfillednotify'] = 'on';
	
	// Add ARKCommerce order placement admin notification to array
	if( empty( $arkgatewaysettings['arkorderplacednotify'] ) ) $arkgatewaysettings['arkorderplacednotify'] = 'on';
	
	// Add ARKCommerce order expiry admin notification to array
	if( empty( $arkgatewaysettings['arkorderexpirednotify'] ) ) $arkgatewaysettings['arkorderexpirednotify'] = 'on';
	
	// Add ARKCommerce initial exchange rate to array
	if( empty( $arkgatewaysettings['arkexchangerate'] ) ) $arkgatewaysettings['arkexchangerate'] = arkcommerce_update_exchange_rate();
	
	// Add ARKCommerce store exchange rate type to array: autorate/multirate/fixedrate
	if( empty( $arkgatewaysettings['arkexchangetype'] ) ) $arkgatewaysettings['arkexchangetype'] = 'autorate';
	
	// Add ARKCommerce store exchange rate multiplier to array
	if( empty( $arkgatewaysettings['arkmultiplier'] ) ) $arkgatewaysettings['arkmultiplier'] = 1.01;
	
	// Add ARKCommerce store manual exchange rate to array
	if( empty( $arkgatewaysettings['arkmanual'] ) ) $arkgatewaysettings['arkmanual'] = null;
	
	// Add ARKCommerce order expiry to array
	if( empty( $arkgatewaysettings['arktimeout'] ) ) $arkgatewaysettings['arktimeout'] = 225;
	
	// Add ARKCommerce dual price display option to array
	if( empty( $arkgatewaysettings['arkdualprice'] ) ) $arkgatewaysettings['arkdualprice'] = 'on';
	
	// Add ARKCommerce cart display option to array
	if( empty( $arkgatewaysettings['arkdisplaycart'] ) ) $arkgatewaysettings['arkdisplaycart'] = 'on';
	
	// Add ARKCommerce Node hostname to array
	if( empty( $arkgatewaysettings['arknode'] ) ) $arkgatewaysettings['arknode'] = 'api.arkcommerce.net';
	
	// Add ARKCommerce DARK Mode option to array
	if( empty( $arkgatewaysettings['darkmode'] ) ) $arkgatewaysettings['darkmode'] = '';
	
	// Add ARKCommerce Node API key to array	
	if( empty( $arkgatewaysettings['nodeapikey'] ) ) $arkgatewaysettings['nodeapikey'] = '36fda24fe5588fa4285ac6c6c2fdfbdb6b6bc9834699774c9bf777f706d05a88';
	
	// Add ARKCommerce DARK Node API key to array	
	if( empty( $arkgatewaysettings['darkapikey'] ) ) $arkgatewaysettings['darkapikey'] = '87cf5bfc995ef94272806b174aaebc400bef88ccb362e9982ffccf17508ffd1d';
	
	// Add ARKCommerce ARK Node API key to array	
	if( empty( $arkgatewaysettings['arkapikey'] ) ) $arkgatewaysettings['arkapikey'] = 'fcc71f2a6d0abf687c91c2371684175a3529989c0ed4010a1150b4bbae239771';
	
	// Add ARKCommerce Node admin user flaf to array
	if( empty( $arkgatewaysettings['arknodeadmin'] ) ) $arkgatewaysettings['arknodeadmin'] = '';
	
	// Add ARKCommerce Node API username to array
	if( empty( $arkgatewaysettings['arkusername'] ) ) $arkgatewaysettings['arkusername'] = null;
	
	// Add ARKCommerce Node API user password to array
	if( empty( $arkgatewaysettings['arkpassword'] ) ) $arkgatewaysettings['arkpassword'] = null;
	
	// Add ARKCommerce Node API user email (last from the set of fetched admins) to array
	if( empty( $arkgatewaysettings['arkemail'] ) ) $arkgatewaysettings['arkemail'] = '';
	
	// Add ARKCommerce store ARK wallet address to array
	if( empty( $arkgatewaysettings['arkaddress'] ) ) $arkgatewaysettings['arkaddress'] = '';
	
	// Add ARKCommerce store DARK wallet address to array
	if( empty( $arkgatewaysettings['darkaddress'] ) ) $arkgatewaysettings['darkaddress'] = '';
	
	// Add ARKCommerce payment title to array
	if( empty( $arkgatewaysettings['title'] ) ) $arkgatewaysettings['title'] = __( 'ARK Payment', 'arkcommerce' );
	
	// Add ARKCommerce payment description to array
	if( empty( $arkgatewaysettings['description'] ) ) $arkgatewaysettings['description'] = __( 'Pay for your purchase with ARK crypto currency by making a direct transaction to the ARK wallet address of the store.', 'arkcommerce' );
	
	// Add ARKCommerce order instructions to array
	if( empty( $arkgatewaysettings['instructions'] ) ) $arkgatewaysettings['instructions'] = __( 'Please carry out the ARK transaction using the supplied data. Be aware of the ARK network fee (0.1 ARK) and do not use an exchange wallet for the transaction.', 'arkcommerce' );
	
	// Add ARKCommerce app identifiers to array
	if( empty( $arkgatewaysettings['arkapps'] ) ) $arkgatewaysettings['arkapps'] = array( 'node' => array ( 'aid' => '2', 'rid' => '1' ), 'ark' => array ( 'aid' => '4', 'rid' => '1' ), 'dark' => array ( 'aid' => '5', 'rid' => '1' ) );
	
	// Add ARKCommerce service status to array
	if( empty( $arkgatewaysettings['arkservice'] ) ) $arkgatewaysettings['arkservice'] = 0;

	// Update the ARKCommerce settings array	
	update_option( 'woocommerce_ark_gateway_settings', $arkgatewaysettings );
	
	// Conclude with the exchange rate update and order lookup Cron jobs
    if( !wp_next_scheduled( 'arkcommerce_refresh_exchange_rate' ) ) wp_schedule_event( time(), 'arkcommerce_biminutely', 'arkcommerce_refresh_exchange_rate' );
	if( !wp_next_scheduled( 'arkcommerce_check_for_open_orders' ) ) wp_schedule_event( time(), 'arkcommerce_minutely', 'arkcommerce_check_for_open_orders' );
}
//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce plugin deactivation function												//
// @output kill scheduled tasks															//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_deactivation() 
{
	// Kill recurring tasks
	wp_clear_scheduled_hook( 'arkcommerce_refresh_exchange_rate' );
	wp_clear_scheduled_hook( 'arkcommerce_check_for_open_orders' );
}
//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce plugin uninstall function												//
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
// QR Code Generator, ARKCommerce Converter and FAQ widgets, Server edition(if present)	//
//////////////////////////////////////////////////////////////////////////////////////////
if( file_exists( plugin_dir_path( __FILE__ ) . 'includes/phpqrcode.php' ) ) include( plugin_dir_path( __FILE__ ) . 'includes/phpqrcode.php' );
if( file_exists( plugin_dir_path( __FILE__ ) . 'arkfaqwidget.php' ) ) include( plugin_dir_path( __FILE__ ) . 'arkfaqwidget.php' );
if( file_exists( plugin_dir_path( __FILE__ ) . 'arkccwidget.php' ) ) include( plugin_dir_path( __FILE__ ) . 'arkccwidget.php' );
if( file_exists( plugin_dir_path( __FILE__ ) . 'arkcommerceserver.php' ) ) include( plugin_dir_path( __FILE__ ) . 'arkcommerceserver.php' );

//////////////////////////////////////////////////////////////////////////////////////////
// Add the gateway to WooCommerce														//
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
// Adds plugin page links																//
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
// Adds plugin page meta row links														//
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
		if( file_exists( plugin_dir_path( __FILE__ ) . 'arkcommerceserver.php' ) ) $links[] = '<a href="' . admin_url( 'admin.php?page=arkcommerce_server' ) . '">' . __( 'Server', 'arkcommerce' ) . '</a>';
		$links[] = '<a href="' . admin_url( 'admin.php?page=arkcommerce_information' ) . '">' . __( 'Information', 'arkcommerce' ) . '</a>';
		$links[] = '<a href="https://explorer.ark.io/address/AGSNhDkqjXdsQMWQgu9G1RFWy9FavaXXsb">' . __( 'Donate ARK', 'arkcommerce' ) . '</a>';
	}
	// Add entry to existing links
	return $links;
}
add_filter( 'plugin_row_meta',  'arkcommerce_plugin_meta_links', 10, 2 );

//////////////////////////////////////////////////////////////////////////////////////////
// Add ARKCommerce CSS admin stylesheets												//
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
// Add ARKCommerce CSS WP stylesheets													//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_register_wp_styles() 
{
	// CSS for all ARKCommerce WP styles (customer-facing)
	wp_register_style( 'wp_arkcommerce', plugin_dir_url( __FILE__ ) . 'assets/css/wp_arkcommerce.css' );
	wp_enqueue_style( 'wp_arkcommerce' );
}
add_action( 'wp_enqueue_scripts', 'arkcommerce_register_wp_styles' );

//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce payment gateway															//
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
			$this->arkmultiplier			= $this->get_option( 'arkmultiplier' );
			$this->arkmanual				= $this->get_option( 'arkmanual' );
			$this->arktimeout				= $this->get_option( 'arktimeout' );
			$this->arkdualprice				= $this->get_option( 'arkdualprice' );
			$this->enabled					= $this->get_option( 'enabled' );
			$this->title					= $this->get_option( 'title' );
			$this->description				= $this->get_option( 'description' );
			$this->arkaddress				= $this->get_option( 'arkaddress' );
			$this->darkaddress				= $this->get_option( 'darkaddress' );
			$this->darkapikey				= $this->get_option( 'darkapikey' );
			$this->arkapikey				= $this->get_option( 'arkapikey' );
			$this->nodeapikey				= $this->get_option( 'nodeapikey' );
			$this->arknodeadmin				= $this->get_option( 'arknodeadmin' );
			$this->arkapps					= $this->get_option( 'arkapps' );
			$this->arknode					= $this->get_option( 'arknode' );
			$this->darkmode					= $this->get_option( 'darkmode' );
			$this->arkexchangerate			= $this->get_option( 'arkexchangerate' );
			$this->arkemail					= $this->get_option( 'arkemail' );
			$this->arkusername				= $this->get_option( 'arkusername' );
			$this->arkpassword				= $this->get_option( 'arkpassword' );
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
//		 Initialize ARKCommerce payment gateway settings form fields					//
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
				'arkemail' 			=> array(
					'title'			=> __( 'ARKCommerce User Email', 'arkcommerce' ),
					'type'			=> 'email',
					'description'	=> __( 'The email address required to access the ARKCommerce Node API.', 'arkcommerce' ),
					'default'		=> '',
					'desc_tip'		=> true, ),
				'arkusername' 		=> array(
					'title'			=> __( 'ARKCommerce Username', 'arkcommerce' ),
					'type'			=> 'username',
					'description'	=> __( 'The username required to access the ARKCommerce Node API.', 'arkcommerce' ),
					'default'		=> '',
					'desc_tip'		=> true, ),
				'arkpassword' 		=> array(
					'title'			=> __( 'ARKCommerce Password', 'arkcommerce' ),
					'type'			=> 'password',
					'description'	=> __( 'The password required to access the ARKCommerce Node API.', 'arkcommerce' ),
					'default'		=> '',
					'desc_tip'		=> true, ),
				'darkmode' 			=> array(
					'title'			=> __( 'DARK Mode (sandbox)', 'arkcommerce' ),
					'type'			=> 'checkbox',
					'description'	=> __( 'DARK Mode is for testing purposes only; when it is enabled, ARKCommerce connects to the ARK Devnet blockchain and uses the supplied DARK wallet address.', 'arkcommerce' ),
					'label'			=> __( 'Enable DARK Mode (for testing purposes only)', 'arkcommerce' ),
					'default'		=> 'no',
					'desc_tip'		=> true, ),
				'darkaddress' 		=> array(
					'title'			=> __( 'DARK Wallet Address', 'arkcommerce' ),
					'type'			=> 'text',
					'description'	=> __( 'The DARK wallet address to be used for payment testing in DARK Mode.', 'arkcommerce' ),
					'default'		=> '',
					'desc_tip'		=> true, ) ) );
		}
//////////////////////////////////////////////////////////////////////////////////////////
// 		Validate ARK Wallet Address field												//
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
// 		Validate DARK Wallet Address field												//
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
// 		Validate ARK Username field														//
// 		@param str $key																	//
//		@param str $value																//
//		@return str $value																//
//////////////////////////////////////////////////////////////////////////////////////////
		public function validate_arkusername_field( $key, $value )
		{
			// Check if the ARK Username is more than 5 characters long, throw an error if not
			if( strlen( trim( $value ) ) > 5 ) return trim( $value );
			else WC_Admin_Settings::add_error( esc_html( __( 'Error in Username length, 6 character minimum required.', 'arkcommerce' ) ) );
		}
//////////////////////////////////////////////////////////////////////////////////////////
// 		Validate ARKCommerce Node API user email field									//
// 		@param str $key																	//
//		@param str $value																//
//		@return str $value																//
//////////////////////////////////////////////////////////////////////////////////////////		
		public function validate_arkemail_field( $key, $value )
		{
			// Check if the email is valid, throw an error if not
			if( is_email( trim( $value ) ) ) return trim( $value );
			else WC_Admin_Settings::add_error( esc_html( __( 'Error in User Email, please review the setting and retry.', 'arkcommerce' ) ) );
		}
//////////////////////////////////////////////////////////////////////////////////////////
// 		Sanitize settings 																//
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

				// Sanitize ARK Node username
				if( isset( $settings['arkusername'] ) ) $settings['arkusername'] = sanitize_text_field( trim( $settings['arkusername'] ) );

				// Sanitize ARK Node user password
				if( isset( $settings['arkpassword'] ) ) $settings['arkpassword'] = sanitize_text_field( trim( $settings['arkpassword'] ) );

				// Sanitize ARK Node user email
				if( isset( $settings['arkemail'] ) ) $settings['arkemail'] = sanitize_email( trim( $settings['arkemail'] ) );
			}
			// Return sanitized array
			return $settings;
		}
//////////////////////////////////////////////////////////////////////////////////////////
// 		Order changes to on-hold, reduce stock, empty cart, notify admin				//
// 		@param int $order_id															//
//		@return array																	//
//		@record post order																//
//////////////////////////////////////////////////////////////////////////////////////////
		public function process_payment( $order_id ) 
		{
			// Gather and/or set variables
			global $woocommerce;
			$order = wc_get_order( $order_id );
			$ark_neworder_data = $order->get_data();
			$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
			$ark_neworder_currency = get_woocommerce_currency();
			$arkblockheight = arkcommerce_get_block_height();
			$arkexchangerate = arkcommerce_get_exchange_rate();
			$storewalletaddress = $arkgatewaysettings['arkaddress'];
			
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
				return array(
					'result' 	=> 'success',
					'redirect'	=> $this->get_return_url( $order ) );
			}
			// Error: currency conversion error
			elseif( $ark_neworder_total == 0 && $arkblockheight != 0 ) 
			{
				// Output the error notice
				wc_add_notice( ( '<span class="dashicons-before dashicons-arkcommerce"> </span> ' . __( 'Error: ARK currency conversion service unresponsive', 'arkcommerce' ) ), 'error' );
				return;
			}
			// Error: ARKCommerce node unresponsive error
			elseif( $ark_neworder_total != 0 && $arkblockheight == 0 ) 
			{
				// Output the error notice
				wc_add_notice( ( '<span class="dashicons-before dashicons-arkcommerce"> </span> ' . __( 'Error: ARK payment gateway unresponsive', 'arkcommerce' ) ), 'error' );
				return;
			}
		}
//////////////////////////////////////////////////////////////////////////////////////////
// 		Add content to the the Order Received/'Thank You' page							//
// 		@param WC_Order $order_id														//
// 		@output ARKCommerce information													//
//////////////////////////////////////////////////////////////////////////////////////////
		public function arkcommerce_order_placed_page( $order_id ) 
		{
			if( $this->instructions ) 
			{
				// Gather and/or set variables
				$orderdata = wc_get_order($order_id);
				$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
				$store_currency = get_woocommerce_currency();
				$timeout = arkcommerce_get_order_timeout();
				$qrcurl = ( plugin_dir_url( __FILE__ ) . 'assets/images/qrcode.png' );
				$arkprice = $orderdata->get_meta( $key = 'ark_total' );
				$storewalletaddress = $arkgatewaysettings['arkaddress'];
				
				// DARK Mode settings
				if( $arkgatewaysettings['darkmode'] == 'yes' ) $storewalletaddress = $arkgatewaysettings['darkaddress'];

				// Display QR Code of store ARK wallet address and form a table containing the address and order number
				$qrcode = sprintf( '<hr><table><tr><th><img alt="QRCODE" width="130" height="130" src="%s"></th><td>%s</td></tr></table><hr>', $qrcurl, wptexturize( $arkgatewaysettings['instructions'] ) );
				
				// Check if ARK is already chosen as main store currency
				if( $store_currency != 'ARK' ) $arktable = sprintf( '<table><tr><th><b>' . __( 'ARK Wallet Address', 'arkcommerce' ) . '</b></th><td>%s</td></tr><tr><th><b>SmartBridge</b></th><td>%s</td></tr><tr><th><b>' . __( 'ARK Total', 'arkcommerce' ) . '</b></th><td>Ѧ%s</td></tr><tr><th><b>' . __( 'Order Expiry', 'arkcommerce' ) . '</b></th><td>%s</td></tr></table><hr>', $storewalletaddress, $orderdata->get_id(), $arkprice, $timeout );
				else $arktable = sprintf( '<p><table><tr><th><b>' . __( 'ARK Wallet Address', 'arkcommerce' ) . '</b></th><td>%s</td></tr><tr><th><b>SmartBridge</b></th><td>%s</td></tr><tr><th><b>' . __( 'Order Expiry', 'arkcommerce' ) . '</b></th><td>%s</td></tr></table></p><hr>', $storewalletaddress, $orderdata->get_id(), $timeout );	
				
				// Output the QR Code, admin-defined instructions, and the ARK data table
				echo( $qrcode . wptexturize ( $arktable ) );
			}
		}
//////////////////////////////////////////////////////////////////////////////////////////
// 		Add content to the WC order email before order table							//
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
				$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
				$store_currency = get_woocommerce_currency();
				$timeout = arkcommerce_get_order_timeout();
				$qrcurl = ( plugin_dir_url( __FILE__ ) . 'assets/images/qrcode.png' );
				$arkprice = get_metadata( 'post', $order->get_id(), 'ark_total', true );
				$storewalletaddress = $arkgatewaysettings['arkaddress'];
				
				// DARK Mode settings
				if( $arkgatewaysettings['darkmode'] == 'yes' ) $storewalletaddress = $arkgatewaysettings['darkaddress'];

				// Display QR Code of store ARK wallet address and form a table containing the address and order number
				$qrcode = sprintf( '<hr><table><tr><th><img alt="QRCODE" width="130" height="130" src="%s"></th><td>%s</td></tr></table><hr>', $qrcurl, wptexturize( $arkgatewaysettings['instructions'] ) );
				
				// Check if ARK is already chosen as main store currency
				if( $store_currency != 'ARK' ) $arktable = sprintf( '<table><tr><th><b>' . __( 'ARK Wallet Address', 'arkcommerce' ) . '</b></th><td>%s</td></tr><tr><th><b>SmartBridge</b></th><td>%s</td></tr><tr><th><b>' . __( 'ARK Total', 'arkcommerce' ) . '</b></th><td>Ѧ%s</td></tr><tr><th><b>' . __( 'Order Expiry', 'arkcommerce' ) . '</b></th><td>%s</td></tr></table><hr>', $storewalletaddress, $order->get_id(), $arkprice, $timeout );
				else $arktable = sprintf( '<p><table><tr><th><b>' . __( 'ARK Wallet Address', 'arkcommerce' ) . '</b></th><td>%s</td></tr><tr><th><b>SmartBridge</b></th><td>%s</td></tr><tr><th><b>' . __( 'Order Expiry', 'arkcommerce' ) . '</b></th><td>%s</td></tr></table></p><hr>', $storewalletaddress, $order->get_id(), $timeout );
				
				// Output the QR Code, admin-defined instructions, and the ARK data table
				echo( $qrcode . wptexturize ( $arktable ) . PHP_EOL );
			}
		}
	}
}
add_action( 'plugins_loaded', 'arkcommerce_gateway_init', 11 );

//////////////////////////////////////////////////////////////////////////////////////////
// Perform checks and display admin notifications										//
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
		echo( '<div class="notice notice-warning is-dismissible"><p><span class="dashicons-before dashicons-arkcommerce" style="vertical-align:middle;"> </span> <strong>ARKCommerce ' . __( 'Node unresponsive. Payment gateway service currently unavailable.', 'arkcommerce' ) . '</strong></p></div>' );
	}
	elseif( $arkgatewaysettings['enabled'] == "yes" && $arkgatewaysettings['arkservice'] === 2 ) 
	{		
		// Display error notice
		echo( '<div class="notice notice-error is-dismissible"><p><span class="dashicons-before dashicons-arkcommerce" style="vertical-align:middle;"> </span> <strong>ARKCommerce ' . __( 'user account inactive. Please renew it as soon as possible or deactivate ARKCommerce. Payment gateway services currently unavailable.', 'arkcommerce' ) . '</strong></p></div>' );
	}
	elseif( $arkgatewaysettings['arkservice'] === 3 )
	{		
		// Display info notice
		echo( '<div class="notice notice-info is-dismissible"><p><span class="dashicons-before dashicons-arkcommerce" style="vertical-align:middle;"> </span> <strong>ARKCommerce ' . __( 'user credentials missing. To obtain them please register at', 'arkcommerce' ) . ' <a href="https://www.arkcommerce.net">www.arkcommerce.net</a></strong></p></div>' );
	}
}
add_action( 'admin_notices', 'arkcommerce_attention_required' );

//////////////////////////////////////////////////////////////////////////////////////////
// Get ARKCommerce Node session token													//
// @return str $sessiontoken															//
// @record int arkservice																//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_get_session_token()
{ 
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$arkapikey = $arkgatewaysettings['nodeapikey'];
	$arknode = $arkgatewaysettings['arknode'];
	$arkemail = $arkgatewaysettings['arkemail'];
	$arkpassword = $arkgatewaysettings['arkpassword'];
	
	// Detect if credentials are missing
	if( empty( $arkemail ) && empty( $arkpassword ) )
	{
		$arkgatewaysettings['arkservice'] = 3;
		$sessiontoken = 'error';
	}
	// Credentials present
	else
	{
		// Assemble query parameters
		if( $arkgatewaysettings['arknodeadmin'] == 'on' ) $tokenqueryurl = "https://$arknode/api/v2/system/admin/session";
		else $tokenqueryurl = "https://$arknode/api/v2/user/session";
		$param = array( 'method'		=> 'POST',
						'headers'		=> array(	"Content-type"				=> "application/json",
													"Accept"					=> "application/json",
													"X-DreamFactory-Api-Key"	=> $arkapikey ),
						'body'			=> json_encode( array(	"email" 		=> $arkemail,
																"password"		=> $arkpassword ) ) );
	
		// Execute user session query
		$bridgeresponse = wp_remote_post( $tokenqueryurl, $param );
	
		// Evaluate response and return result
		if( is_array( $bridgeresponse ) ) 
		{
			$response = json_decode( $bridgeresponse['body'], true );
			$sessiontoken = $response['session_token'];
			if( $arkgatewaysettings['arknodeadmin'] != 'on' && $response['role'] != 'ARKCOMMERCE' )
			{
				$arkgatewaysettings['arkservice'] = 2;
				$sessiontoken = 'error';
			}
			else $arkgatewaysettings['arkservice'] = 0;
		}
		else
		{
			$sessiontoken = 'error';
			$arkgatewaysettings['arkservice'] = 1;	
		}
	}
	// Record state
	update_option( 'woocommerce_ark_gateway_settings', $arkgatewaysettings );
	
	// Return result
	return $sessiontoken;
}
//////////////////////////////////////////////////////////////////////////////////////////
// Get ARK blockchain current block height												//
// @return int $arklastblock															//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_get_block_height() 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$arkrestapikey = $arkgatewaysettings['arkapikey'];
	$arknode = $arkgatewaysettings['arknode'];
	$sessiontoken = arkcommerce_get_session_token();
	
	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' ) $darkrestapikey = $arkgatewaysettings['darkapikey'];
	
	// Construct a block height query URI for ARKCommerce Node
	if( $arkgatewaysettings['darkmode'] == 'yes' ) $ark_blockqueryurl = "https://$arknode/api/v2/dark/_table/blocks?fields=ac_block_height_count&api_key=$darkrestapikey&session_token=$sessiontoken";
	else $ark_blockqueryurl = "https://$arknode/api/v2/ark/_table/blocks?fields=ac_block_height_count&api_key=$arkrestapikey&session_token=$sessiontoken";
	
	// Query ARKCommerce API for current block height
	$arkblockresponse = wp_remote_get( $ark_blockqueryurl );
	
	// ARKCommerce API response
	if( is_array( $arkblockresponse ) ) 
	{
		$arkblockheight = json_decode( $arkblockresponse['body'], true );
		$arklastblock = $arkblockheight['resource'][0]['ac_block_height_count'];
	}
	// ARKCommerce API response invalid or node unreachable
	else $arklastblock = 0;
	
	// Return ARK blockchain last block
	return $arklastblock;
}
//////////////////////////////////////////////////////////////////////////////////////////
// Generate store ARK wallet address QR Code											//
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
	
	// Execute the external PHP QR Code Generator
	if( $storewalletaddress != null ) QRcode::png( $storewalletaddress, $filepath, "L", 8, 1, $backcolor, $forecolor);
}
//////////////////////////////////////////////////////////////////////////////////////////
// Add the ARK crypto currency to WooCommerce list of available currencies				//
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
// Add the ARK crypto currency symbol to WooCommerce (custom currency)					//
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
// Determine the order expiry timeout and return the value to be displayed to customer	//
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
// Periodic worker triggering transaction validation jobs on ARKCommerce open orders	//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_validation_worker() 
{
	// Gather and/or set variables
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
// Function checking for order payment fulfillment, handling and notification			//
// @param int $order_id																	//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_ark_transaction_validation( $order_id ) 
{
	// Gather and/or set variables
	$order = wc_get_order( $order_id );
	$ark_order_data = $order->get_data();
	$ark_order_id = $ark_order_data['id'];
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$arkrestapikey = $arkgatewaysettings['arkapikey'];
	$arknode = $arkgatewaysettings['arknode'];
	$ark_transaction_found = false;
	$arkblockheight = arkcommerce_get_block_height();
	$ark_arktoshi_total = $order->get_meta( $key = 'ark_arktoshi_total' );
	$arkorderblock = $order->get_meta( $key = 'ark_order_block' );
	$arkorderexpiryblock = $order->get_meta( $key = 'ark_expiration_block' );
	$storewalletaddress = $order->get_meta( $key = 'ark_store_wallet_address' );
	$explorerurl = 'https://explorer.ark.io/tx/';
	$sessiontoken = arkcommerce_get_session_token();
	
	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' ) 
	{
		$explorerurl = 'https://dexplorer.ark.io/tx/';
		$darkrestapikey = $arkgatewaysettings['darkapikey'];
	}	
	// Validate the order is on hold and the chosen payment method is ARKCommerce
	if( $order->has_status( 'on-hold' ) && 'ark_gateway' === $order->get_payment_method() ) 
	{
		// Construct a transaction query URI for ARKCommerce Node
		if( $arkgatewaysettings['darkmode'] == 'yes' ) $ark_txqueryurl = "https://$arknode/api/v2/dark/_table/transactions?fields=id%2CsenderId%2CrecipientId%2Camount%2CvendorField%2CblockId&filter=(recipientId%3D$storewalletaddress)%20AND%20(vendorField%20LIKE%20%25$ark_order_id%25)%20AND%20(amount%3D$ark_arktoshi_total)&api_key=$darkrestapikey&session_token=$sessiontoken";
		else $ark_txqueryurl = "https://$arknode/api/v2/ark/_table/transactions?fields=id%2CsenderId%2CrecipientId%2Camount%2CvendorField%2CblockId&filter=(recipientId%3D$storewalletaddress)%20AND%20(vendorField%20LIKE%20%25$ark_order_id%25)%20AND%20(amount%3D$ark_arktoshi_total)&api_key=$arkrestapikey&session_token=$sessiontoken";
		
		// Query ARKCommerce API for matching transactions
		$arkbridgeresponse = wp_remote_get( $ark_txqueryurl );
		
		// ARKCommerce API response validation and block height validation
		if( is_array( $arkbridgeresponse ) && $arkblockheight !=0 ) 
		{
			$arktxrecords = json_decode( $arkbridgeresponse['body'], true );
			$arktxrecordarray = $arktxrecords['resource'];
			
			// If there are more than 1 TX query results, process each to establish the correct one for this particular order
			foreach( $arktxrecordarray as $arktxrecord ) 
			{
				// Gather and/or set transaction-specific variables
				$arktxblockid = $arktxrecord['blockId'];
				
				// Validate TX as correct payment tx for the order
				if( $arktxrecord['recipientId'] == $storewalletaddress && intval( $arktxrecord['amount'] ) == intval( $ark_arktoshi_total ) ) 
				{
					// Construct a transaction block query URI for ARKCommerce Node
					if( $arkgatewaysettings['darkmode'] == 'yes' ) $ark_blockidqueryurl = "https://$arknode/api/v2/dark/_table/blocks?fields=height&filter=(id%3D$arktxblockid)&api_key=$darkrestapikey&session_token=$sessiontoken";
					else $ark_blockidqueryurl = "https://$arknode/api/v2/ark/_table/blocks?fields=height&filter=(id%3D$arktxblockid)&api_key=$arkrestapikey&session_token=$sessiontoken";
					
					// Query ARKCommerce API for block number containing the discovered transaction
					$arkblockidresponse = wp_remote_get( $ark_blockidqueryurl );
					
					// ARKCommerce API response validation
					if( is_array( $arkblockidresponse ) ) 
					{
						$arkblockidheight = json_decode( $arkblockidresponse['body'], true );
						$arktxblockheight = $arkblockidheight['resource'][0]['height'];
						
						// Validate found TX block height is higher or equal to blockchain block height at the time order was made and the order has not expired at that time
						if( $arkorderexpiryblock != 'never' && intval( $arktxblockheight ) <= intval( $arkorderexpiryblock ) && intval( $arktxblockheight ) >= intval( $arkorderblock ) ) 
						{
							// Correct payment TX found
							$ark_transaction_found = true;
							$ark_transaction_identifier = $arktxrecord['id'];
							$ark_transaction_block = $arktxblockheight;
						}
						// Alternatively, validate found TX block height is higher or equal to blockchain block height at the time order was made
						elseif( $arkorderexpiryblock == 'never' && intval( $arktxblockheight ) >= intval( $arkorderblock ) ) 
						{
							// Correct payment TX found
							$ark_transaction_found = true;
							$ark_transaction_identifier = $arktxrecord['id'];
							$ark_transaction_block = $arktxblockheight;
						}
					}
				}
			}
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
		// ARKCommerce API response invalid or node unreachable
		else 
		{
			// Make note of unsuccessful check (the payment has not yet been made)
			$order->add_order_note( __( 'ARKCommerce Node unresponsive.', 'arkcommerce' ), false, true );
			
			// Notify administrator of ARKCommerce Node inaccessibility and the urgency to process the payment manually
			arkcommerce_admin_notification( $order_id, 'arkcommerceunresponsive' );
		}
	}
}
//////////////////////////////////////////////////////////////////////////////////////////
// Convert potentially complex price string(s) to float number(s)						//
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
// Currency check of the store															//
// @return bool $currency_supported														//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_check_currency_support() 
{
	// Gather and/or set variables
	$store_currency = get_woocommerce_currency();
	
	// List supported currencies (coinmarketcap.com listings as of 12/2017)
	$supported_currencies = array( "ARK", "BTC", "USD", "AUD", "BRL", "CAD", "CHF", "CLP", "CNY", "CZK", "DKK", "EUR", "GBP", "HKD", "HUF", "IDR", "ILS", "INR", "JPY", "KRW", "MXN", "MYR", "NOK", "NZD", "PHP", "PKR", "PLN", "RUB", "SEK", "SGD", "THB", "TRY", "TWD", "ZAR" );
	
	// Currency support check
	if( in_array( $store_currency, $supported_currencies ) ) $currency_supported = true;
	else $currency_supported = false;
	
	// Return result
	return $currency_supported;
}
//////////////////////////////////////////////////////////////////////////////////////////
// Update currency exchange rate between store fiat and ARK pairs						//
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
		
		// Query coinmarketcap.com API
		else 
		{
			// Construct a query URI for Coinmarketcap; expected number of results: 1
			$cmc_queryurl = "https://api.coinmarketcap.com/v1/ticker/ark/?convert=$store_currency";
			
			// Query CoinMarketCap API for ARK market price in supported chosen currency
			$cmcresponse = wp_remote_get( $cmc_queryurl );
			
			// CMC API response validation
			if( is_array( $cmcresponse) ) 
			{
				$arkmarketprice = json_decode( $cmcresponse['body'], true );
				
				// Construct a suitable key identifier for in-array lookup
				$chosen_currency_var = sprintf( "price_%s", ( strtolower( $store_currency ) ) );
				
				// Determine the exchange rate
				$arkexchangerate = $arkmarketprice[0][$chosen_currency_var];
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
// Get store fiat-ARK exchange rate														//
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
// Internal currency conversion between fiat and ARK pairs								//
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
// Get ARK wallet balance																//
// @return float $result																//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_get_wallet_balance()
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$arkrestapikey = $arkgatewaysettings['arkapikey'];
	$arknode = $arkgatewaysettings['arknode'];
	$storewalletaddress = $arkgatewaysettings['arkaddress'];
	$sessiontoken = arkcommerce_get_session_token();
	
	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' )
	{
		$storewalletaddress = $arkgatewaysettings['darkaddress'];
		$darkrestapikey = $arkgatewaysettings['darkapikey'];
	}
	// Construct a store ARK wallet address balance query URI for ARKCommerce Node
	if( $arkgatewaysettings['darkmode'] == 'yes' ) $ark_balancequeryurl = "https://$arknode/api/v2/dark/_table/mem_accounts?fields=balance&filter=address%3D$storewalletaddress&api_key=$darkrestapikey&session_token=$sessiontoken";
	else $ark_balancequeryurl = "https://$arknode/api/v2/ark/_table/mem_accounts?fields=balance&filter=address%3D$storewalletaddress&api_key=$arkrestapikey&session_token=$sessiontoken";
	
	// Query ARKCommerce Node API for store ARK wallet address balance
	$arkbridgeresponseba = wp_remote_get( $ark_balancequeryurl );
	
	// ARKCommerce API response validation
	if( is_array( $arkbridgeresponseba ) )
	{
		$arkbaresponse = json_decode( $arkbridgeresponseba['body'], true );
		$result = number_format( ( float ) $arkbaresponse['resource'][0]['balance'] / 100000000, 8, '.', '' );
	}
	else $result = 0;
	
	// Return result
	return $result;
}
//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce administrator mail notifications on order events							//
// @param int $order_id																	//
// @param str $messagetype																//
// @output html email																	//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_admin_notification( $order_id, $messagetype ) 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$mailheaderdef = array( 'Content-Type: text/html; charset=UTF-8' );
	$order = wc_get_order( $order_id );
	$ark_order_data = $order->get_data();
	$storewalletaddress = $arkgatewaysettings['arkaddress'];
	$explorerurl = 'https://explorer.ark.io/tx/';
	$exploreraddressurl = 'https://explorer.ark.io/address/';

	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' )
	{
		$explorerurl = 'https://dexplorer.ark.io/tx/';
		$exploreraddressurl = 'https://dexplorer.ark.io/address/';
		$storewalletaddress = $arkgatewaysettings['darkaddress'];
	}
	//Determine the type of message to be sent
	if( $messagetype == 'orderfilled' && $arkgatewaysettings['arkorderfillednotify'] == 'on' ) 
	{
		// ARK not native currency
		if( $order->get_meta( $key = 'ark_store_currency' ) != 'ARK' ) $orderorigininfo = ( __( 'Order total converted to ARK from', 'arkcommerce' ) . ' ' . $order->get_meta( $key = 'ark_store_currency' ) . ' ' . __( 'at the following exchange rate', 'arkcommerce' ) . ': ' . $order->get_meta( $key = 'ark_exchange_rate' ) . ' ' . $order->get_meta( $key = 'ark_store_currency' ) . ' ' . __( 'per ARK', 'arkcommerce' ) );
		
		// ARK native currency
		else $orderorigininfo = ( __( 'Order total originally priced in ARK at', 'arkcommerce' ) . ': Ѧ' . number_format( ( float ) $order->get_meta( $key = 'ark_total' ), 8, '.', '' ) );
		
		// Compose mail
		$mailsubject = ( __( 'ARKCommerce Order', 'arkcommerce' ) . ' ' . $ark_order_data['id'] . ' ' . __( 'Filled', 'arkcommerce' ) );
		$mailmessagecontent = ( '
			<!DOCTYPE html>
			<html>
				<head>
					<meta charset="utf-8">
					<meta name="viewport" content="width=device-width">
					<meta http-equiv="Content-Type" content="text/html">
					<meta name="x-apple-disable-message-reformatting">
					<title>
						' . __( 'ARKCommerce Order Filled' , 'arkcommerce' ) . '
					</title>
				</head>
				<body width="100%" bgcolor="#000000" style="margin: 0; mso-line-height-rule: exactly;">
					<center style="width: 100%; background: #000000; text-align: left;">
						<div style="max-width: 600px; margin: auto;">
							<table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 600px;">
								<tr>
									<td style="padding: 20px 0; text-align: center">
										<img src="' . plugin_dir_url( __FILE__ ) . 'assets/images/arkcommercemailicon.png' . '" width="100" height="80" alt="ARKCommerce" border="0" style="background: #dddddd; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
									</td>
								</tr>
							</table>
							<table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 600px;">
								<tr>
									<td bgcolor="#ffffff">
										<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
											<tr>
												<td style="padding: 40px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
													<h1 style="margin: 0 0 10px 0; font-family: sans-serif; font-size: 24px; line-height: 27px; color: #333333; font-weight: normal;">
														' . __( 'ARKCommerce Order', 'arkcommerce' ) . ' ' . $ark_order_data['id'] . ' ' . __( 'Filled', 'arkcommerce' ) . '
													</h1>
													<p style="margin: 0;">
														' . __( 'The order number', 'arkcommerce' ) . ' ' . $ark_order_data['id'] . ' ' . __( 'with order total', 'arkcommerce' ) . ' Ѧ' . number_format( ( float ) $order->get_meta( $key = 'ark_total' ), 8, '.', '' ) . ' ' . __( 'has been paid in full to store wallet address', 'arkcommerce' ) . ' ' . $storewalletaddress . '.
														<hr>
														' . $orderorigininfo . '
														<br>
														' . __( 'Order placed at block', 'arkcommerce' ) . ': ' . $order->get_meta( $key = 'ark_order_block' ) . '
														<br>
														' . __( 'Order filled at block', 'arkcommerce' ) . ': ' . $order->get_meta( $key = 'ark_payment_block' ) . '
														<br>
														' . __( 'Order transaction ID', 'arkcommerce' ) . ': ' . $order->get_meta( $key = 'ark_transaction_id' ) . '
														<hr>
														' . __( 'This WooCommerce order status is now complete. The button below links to the ARK Blockchain Explorer web application displaying the details of this transaction', 'arkcommerce' ) . '.
													</p>
												</td>
											</tr>
											<tr>
												<td style="padding: 0 40px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
													<table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin: auto;">
														<tr>
															<td style="border-radius: 3px; background: #000000; text-align: center;">
																<a href="' . $explorerurl . $order->get_meta( $key = 'ark_transaction_id' ) . '" style="background: #000000; border: 15px solid #000000; text-align: center; text-decoration: none; display: block; border-radius: 3px; font-weight: bold; color:#ffffff;">' . __( 'Transaction', 'arkcommerce' ) . '
																</a>
															</td>
														</tr>
														<tr>
															<td style="padding: 40px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
																<i>
																	<small>
																		' . __( 'Thank you for choosing ARKCommerce', 'arkcommerce' ) . '.
																	</small>
																</i>
															</td>
														</tr>
													</table>
													<br>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td aria-hidden="true" height="40" style="font-size: 0; line-height: 0;">&nbsp;
									</td>
								</tr>
							</table>
						</div>
					</center>
				</body>
			</html>' );
		
		// Send mail to admin
		wp_mail( $arkgatewaysettings['arknotify'], $mailsubject, $mailmessagecontent, $mailheaderdef );
	}
	elseif( $messagetype == 'orderplaced' && $arkgatewaysettings['arkorderplacednotify'] == 'on' ) 
	{
		// ARK not native currency
		if( $order->get_meta( $key = 'ark_store_currency' ) != 'ARK' ) $orderorigininfo = ( __( 'Order total converted to ARK from', 'arkcommerce' ) . ' ' . $order->get_meta( $key = 'ark_store_currency' ) . ' ' . __( 'at the following exchange rate', 'arkcommerce' ) . ': ' . $order->get_meta( $key = 'ark_exchange_rate' ) . ' ' . $order->get_meta( $key = 'ark_store_currency' ) . ' ' . __( 'per ARK', 'arkcommerce' ) );
		
		// ARK native currency
		else $orderorigininfo = ( __( 'Order total originally priced in ARK at', 'arkcommerce' ) . ': Ѧ' . number_format( ( float ) $order->get_meta( $key = 'ark_total' ), 8, '.', '' ) );
		
		// Order expiry
		if( $order->get_meta( $key = 'ark_expiration_block' ) != 2147483646 ) $orderexpiry = __( 'This order expires at block', 'arkcommerce' ) . ': ' . $order->get_meta( $key = 'ark_expiration_block' );
		
		// Order doesn't expire
		else $orderexpiry = ( __( 'This order does not expire', 'arkcommerce' ) . '.' );
		
		// Compose mail
		$mailsubject = ( __( 'ARKCommerce Order', 'arkcommerce' ) . ' ' . $ark_order_data['id'] . ' ' . __( 'Placed', 'arkcommerce' ) );
		$mailmessagecontent = ( '
			<!DOCTYPE html>
			<html>
				<head>
					<meta charset="utf-8">
					<meta name="viewport" content="width=device-width">
					<meta http-equiv="Content-Type" content="text/html">
					<meta name="x-apple-disable-message-reformatting">
					<title>
						' . __( 'ARKCommerce Order Placed' , 'arkcommerce' ) . '
					</title>
				</head>
				<body width="100%" bgcolor="#000000" style="margin: 0; mso-line-height-rule: exactly;">
					<center style="width: 100%; background: #000000; text-align: left;">
						<div style="max-width: 600px; margin: auto;">
							<table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 600px;">
								<tr>
									<td style="padding: 20px 0; text-align: center">
										<img src="' . plugin_dir_url( __FILE__ ) . 'assets/images/arkcommercemailicon.png' . '"  alt="ARKCommerce" border="0" width="100" height="80" style="background: #dddddd; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
									</td>
								</tr>
							</table>
							<table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 600px;">
								<tr>
									<td bgcolor="#ffffff">
										<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
											<tr>
												<td style="padding: 40px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
													<h1 style="margin: 0 0 10px 0; font-family: sans-serif; font-size: 24px; line-height: 27px; color: #333333; font-weight: normal;">
														' . __( 'ARKCommerce Order', 'arkcommerce' ) . ' ' . $ark_order_data['id'] . ' ' . __( 'Placed', 'arkcommerce' ) . '
													</h1>
													<p style="margin: 0;">
														' . __( 'The order number', 'arkcommerce' ) . ' ' . $ark_order_data['id'] . ' ' . __( 'with order total', 'arkcommerce' ) . ' Ѧ' . number_format( ( float ) $order->get_meta( $key = 'ark_total' ), 8, '.', '' ) . ' ' . __( 'has been placed', 'arkcommerce' ) . '.
														<hr>
														' . $orderorigininfo . '
														<br>
														' . __( 'Order placed at block', 'arkcommerce' ) . ': ' . $order->get_meta( $key = 'ark_order_block' ) . '
														<br>
														' . $orderexpiry . '
														<hr>
														' . __( 'This WooCommerce order status is now on hold and ARK blockchain is currently being queried for a customer transaction fulfilling it. As soon as such a transaction is detected the order status will change to complete', 'arkcommerce' ) . '.
													</p>
												</td>
											</tr>
											<tr>
												<td>
													<table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin: auto;">
														<tr>
															<td style="text-align: center; padding: 40px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
																<i>
																	<small>
																		' . __( 'Thank you for choosing ARKCommerce', 'arkcommerce' ) . '.
																	</small>
																</i>
															</td>
														</tr>
													</table>
													<br>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td aria-hidden="true" height="40" style="font-size: 0; line-height: 0;">&nbsp;
									</td>
								</tr>
							</table>
						</div>
					</center>
				</body>
			</html>' );
		
		// Send mail to admin
		wp_mail( $arkgatewaysettings['arknotify'], $mailsubject, $mailmessagecontent, $mailheaderdef );
	}
	elseif( $messagetype == 'orderexpired' && $arkgatewaysettings['arkorderexpirednotify'] == 'on' ) 
	{
		// Establish block height and validate response
		$arkblockheight = arkcommerce_get_block_height();
		if( $arkblockheight == 0 ) $arkblockheight = 'error';
		
		// ARK not native currency
		if( $order->get_meta( $key = 'ark_store_currency' ) != 'ARK' ) $orderorigininfo = ( __( 'Order total converted to ARK from', 'arkcommerce' ) . ' ' . $order->get_meta( $key = 'ark_store_currency' ) . ' ' . __( 'at the following exchange rate', 'arkcommerce' ) . ': ' . $order->get_meta( $key = 'ark_exchange_rate' ) . ' ' . $order->get_meta( $key = 'ark_store_currency' ) . ' ' . __( 'per ARK', 'arkcommerce' ) );
		
		// ARK native currency
		else $orderorigininfo = ( __( 'Order total originally priced in ARK at', 'arkcommerce' ) . ': Ѧ' . number_format( ( float ) $order->get_meta( $key = 'ark_total' ), 8, '.', '' ) );
		
		// Compose mail
		$mailsubject = ( __( 'ARKCommerce Order', 'arkcommerce' ) . ' ' . $ark_order_data['id'] . ' ' . __( 'Expired', 'arkcommerce' ) );
		$mailmessagecontent = ( '
			<!DOCTYPE html>
			<html>
				<head>
					<meta charset="utf-8">
					<meta name="viewport" content="width=device-width">
					<meta http-equiv="Content-Type" content="text/html">
					<meta name="x-apple-disable-message-reformatting">
					<title>
						' . __( 'ARKCommerce Order Expired' , 'arkcommerce' ) . '
					</title>
				</head>
				<body width="100%" bgcolor="#000000" style="margin: 0; mso-line-height-rule: exactly;">
					<center style="width: 100%; background: #000000; text-align: left;">
						<div style="max-width: 600px; margin: auto;">
							<table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 600px;">
								<tr>
									<td style="padding: 20px 0; text-align: center">
										<img src="' . plugin_dir_url( __FILE__ ) . 'assets/images/arkcommercemailicon.png' . '" width="100" height="80" alt="ARKCommerce" border="0" style="background: #dddddd; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
									</td>
								</tr>
							</table>
							<table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 600px;">
								<tr>
									<td bgcolor="#ffffff">
										<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
											<tr>
												<td style="padding: 40px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
												<h1 style="margin: 0 0 10px 0; font-family: sans-serif; font-size: 24px; line-height: 27px; color: #333333; font-weight: normal;">
													' . __( 'ARKCommerce Order', 'arkcommerce' ) . ' ' . $ark_order_data['id'] . ' ' . __( 'Expired', 'arkcommerce' ) . '
												</h1>
												<p style="margin: 0;">
													' . __( 'The order number', 'arkcommerce' ) . ' ' . $ark_order_data['id'] . ' ' . __( 'with order total', 'arkcommerce' ) . ' Ѧ' . number_format( ( float ) $order->get_meta( $key = 'ark_total' ), 8, '.', '' ) . ' ' . __( 'has expired', 'arkcommerce' ) . '.
													<hr>
													' . $orderorigininfo . '
													<br>
													' . __( 'Order placed at block', 'arkcommerce' ) . ': ' . $order->get_meta( $key = 'ark_order_block' ) . '
													<br>
													' . __( 'Order expired at block', 'arkcommerce' ) . ': ' . $order->get_meta( $key = 'ark_expiration_block' ) . '
													<br>
													' . __( 'Current block height', 'arkcommerce' ) . ': ' . $arkblockheight . '
													<hr>
													' . __( 'This WooCommerce order status is now cancelled', 'arkcommerce' ) . '.
												</p>
											</td>
										</tr>
										<tr>
											<td>
												<table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin: auto;">
													<tr>
														<td style="text-align: center; padding: 40px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
															<i>
																<small>
																	' . __( 'Thank you for choosing ARKCommerce', 'arkcommerce' ) . '.
																</small>
															</i>
														</td>
													</tr>
												</table>
												<br>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td aria-hidden="true" height="40" style="font-size: 0; line-height: 0;">&nbsp;
								</td>
							</tr>
						</table>
					</div>
				</center>
			</body>
		</html>' );
		
		// Send mail to admin
		wp_mail( $arkgatewaysettings['arknotify'], $mailsubject, $mailmessagecontent, $mailheaderdef );
	}
	elseif( $messagetype == 'arkcommerceunresponsive' ) 
	{
		// ARK not native currency
		if( $order->get_meta( $key = 'ark_store_currency' ) != 'ARK' ) $orderorigininfo = ( __( 'Order total converted to ARK from', 'arkcommerce' ) . ' ' . $order->get_meta( $key = 'ark_store_currency' ) . ' ' . __( 'at the following exchange rate', 'arkcommerce' ) . ': ' . $order->get_meta( $key = 'ark_exchange_rate' ) . ' ' . $order->get_meta( $key = 'ark_store_currency' ) . ' ' . __( 'per ARK', 'arkcommerce' ) );
		
		// ARK native currency
		else $orderorigininfo = ( __( 'Order total originally priced in ARK at', 'arkcommerce' ) . ': Ѧ' . number_format( ( float ) $order->get_meta( $key = 'ark_total' ), 8, '.', '' ) );
		
		// Order expiry
		if( $order->get_meta( $key = 'ark_expiration_block' ) != 2147483646 ) $orderexpiry = __( 'This order expires at block', 'arkcommerce' ) . ': ' . $order->get_meta( $key = 'ark_expiration_block' );
		
		// Order doesn't expire
		else $orderexpiry = ( __( 'This order does not expire', 'arkcommerce' ) . '.' );
		
		// Compose mail
		$mailsubject = ( __( 'ARKCommerce Order', 'arkcommerce' ) . ' ' . $ark_order_data['id'] . ' ' . __( 'Demands Attention', 'arkcommerce' ) );
		$mailmessagecontent = ( '
			<!DOCTYPE html>
			<html>
				<head>
					<meta charset="utf-8">
					<meta name="viewport" content="width=device-width">
					<meta http-equiv="Content-Type" content="text/html">
					<meta name="x-apple-disable-message-reformatting">
					<title>
						' . __( 'ARKCommerce Order Demands Manual Processing' , 'arkcommerce' ) . '
					</title>
				</head>
				<body width="100%" bgcolor="#000000" style="margin: 0; mso-line-height-rule: exactly;">
					<center style="width: 100%; background: #000000; text-align: left;">
						<div style="max-width: 600px; margin: auto;">
							<table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 600px;">
								<tr>
									<td style="padding: 20px 0; text-align: center">
										<img src="' . plugin_dir_url( __FILE__ ) . 'assets/images/arkcommercemailicon.png' . '"  alt="ARKCommerce" border="0" width="100" height="80" style="background: #dddddd; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
									</td>
								</tr>
							</table>
							<table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="max-width: 600px;">
								<tr>
									<td bgcolor="#ffffff">
										<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
											<tr>
												<td style="padding: 40px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
													<h1 style="margin: 0 0 10px 0; font-family: sans-serif; font-size: 24px; line-height: 27px; color: #333333; font-weight: normal;">
														' . __( 'ARKCommerce Order', 'arkcommerce' ) . ' ' . $ark_order_data['id'] . ' ' . __( 'Demands Manual Processing', 'arkcommerce' ) . '
													</h1>
													<p style="margin: 0;">
														' . __( 'The order number', 'arkcommerce' ) . ' ' . $ark_order_data['id'] . ' ' . __( 'with order total', 'arkcommerce' ) . ' Ѧ' . number_format( ( float ) $order->get_meta( $key = 'ark_total' ), 8, '.', '' ) . ' ' . __( 'demands manual processing due to ARKCommerce Node inaccessibility', 'arkcommerce' ) . '.
														<hr>
														' . $orderorigininfo . '
														<br>
														' . __( 'Order placed at block', 'arkcommerce' ) . ': ' . $order->get_meta( $key = 'ark_order_block' ) . '
														<br>
														' . $orderexpiry . '
														<hr>
														' . __( 'This WooCommerce order status is now on hold and you are advised to monitor the store ARK wallet address for incoming customer transaction fulfilling it. As soon as such a transaction appears please change its status to complete', 'arkcommerce' ) . '.
													</p>
												</td>
											</tr>
											<tr>
												<td style="padding: 0 40px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
													<table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin: auto;">
														<tr>
															<td style="border-radius: 3px; background: #000000; text-align: center;">
																<a href="' . $exploreraddressurl . $storewalletaddress . '" style="background: #000000; border: 15px solid #000000; text-align: center; text-decoration: none; display: block; border-radius: 3px; font-weight: bold; color:#ffffff;">' . __( 'Store Wallet', 'arkcommerce' ) . '
																</a>
															</td>
														</tr>
														<tr>
															<td style="padding: 40px; font-family: sans-serif; font-size: 15px; line-height: 20px; color: #555555;">
																<i>
																	<small>
																		' . __( 'Thank you for choosing ARKCommerce', 'arkcommerce' ) . '.
																	</small>
																</i>
															</td>
														</tr>
													</table>
													<br>
												</td>
											</tr>
										</table>
									</td>
								</tr>
								<tr>
									<td aria-hidden="true" height="40" style="font-size: 0; line-height: 0;">&nbsp;
									</td>
								</tr>
							</table>
						</div>
					</center>
				</body>
			</html>' );
		
		// Send mail to admin
		wp_mail( $arkgatewaysettings['arknotify'], $mailsubject, $mailmessagecontent, $mailheaderdef );
	}
}
//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce dual price display														//
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
// Display ARK price+timeout notice at cart checkout									//
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
// Display ARK price+timeout notice at cart checkout									//
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
// ARKCommerce Status admin dashboard widget											//
// @output ARKCommerce Status Dashboard Widget											//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_display_status_widget() 
{
	// Gather and/or set variables
	global $wpdb;
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$arkrestapikey = $arkgatewaysettings['arkapikey'];
	$arknode = $arkgatewaysettings['arknode'];
	$storewalletaddress = $arkgatewaysettings['arkaddress'];
	$arkblockheight = arkcommerce_get_block_height();
	$wallet_balance = arkcommerce_get_wallet_balance();
	$sessiontoken = arkcommerce_get_session_token();
	$explorerurl = 'https://explorer.ark.io/';
	
	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' )
	{
		$explorerurl = 'https://dexplorer.ark.io/';
		$storewalletaddress = $arkgatewaysettings['darkaddress'];
		$darkrestapikey = $arkgatewaysettings['darkapikey'];
	}
	// Validate block height response
	if( $arkblockheight != 0 ) echo( '<span class="dashicons dashicons-info" style="color:lime;"> </span> <b style="color:black;">' . __( 'ARKCommerce Node operational', 'arkcommerce' ) . '. ' . __( 'ARK block height', 'arkcommerce' ) . ': ' . $arkblockheight . '</b>' );
	else echo( '<span class="dashicons dashicons-info" style="color:red;"> </span> <b style="color:black;">' . __( 'ARKCommerce Node unresponsive', 'arkcommerce' ) . '.</b>' ); 
	
	// Construct a query URI for Coinmarketcap; expected number of results: 1
	$cmc_queryurl = "https://api.coinmarketcap.com/v1/ticker/ark/?convert=EUR";
	
	// Query CoinMarketCap API for ARK market price (USD, EUR, BTC)
	$cmcresponse = wp_remote_get( $cmc_queryurl );
	
	// CMC API response validation
	if( is_array( $cmcresponse) ) 
	{
		$arkmarketprice = json_decode( $cmcresponse['body'], true );
		
		// Determine whether the CMC node produces valid result
		if( !empty( $arkmarketprice[0][price_usd] ) ) echo( sprintf( '<hr><b>' . __( 'ARK Market Price', 'arkcommerce') . ': %s USD | %s EUR | %s BTC </b>', $arkmarketprice[0][price_usd], $arkmarketprice[0][price_eur], $arkmarketprice[0][price_btc] ) );
		
		// Construct CMC error info
		else echo( '<hr><span class="dashicons dashicons-info" style="color:red;"> </span> <b style="color:black;">' . __( 'Unable to display current market prices for ARK, coinmarketcap.com unresponsive.', 'arkcommerce' ) . '</b>' );
	}
	// Construct CMC error info
	else echo( '<hr><span class="dashicons dashicons-info" style="color:red;"> </span> <b style="color:black;">' . __( 'Unable to display current market prices for ARK, coinmarketcap.com unresponsive.', 'arkcommerce' ) . '</b>' );
	
	// Construct a store ARK wallet address transactions query URI for ARKCommerce Node
	if( $arkgatewaysettings['darkmode'] == 'yes' ) $ark_txqueryurl = "https://$arknode/api/v2/dark/_table/transactions?fields=id%2C%20senderId%2C%20amount%2C%20vendorField&filter=recipientId%3D$storewalletaddress&limit=10&order=timestamp%20dsc&api_key=$darkrestapikey&session_token=$sessiontoken";
	else $ark_txqueryurl = "https://$arknode/api/v2/ark/_table/transactions?fields=id%2C%20senderId%2C%20amount%2C%20vendorField&filter=recipientId%3D$storewalletaddress&limit=10&order=timestamp%20dsc&api_key=$arkrestapikey&session_token=$sessiontoken";
	
	// Query ARKCommerce Node API for store ARK wallet address transactions
	$arkbridgeresponsetx = wp_remote_get( $ark_txqueryurl );

	// ARKCommerce API response validation
	if( is_array( $arkbridgeresponsetx ) ) 
	{
		$arktxresponse = json_decode( $arkbridgeresponsetx['body'], true );
		$arktxarray = $arktxresponse['resource'];
		
		// Determine whether the ARKCommerce Node has a valid database connection
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
	}
	// Construct a query for all WC orders made using ARKCommerce payment gateway
	$arkordersquery = ( "SELECT post_id FROM " . $wpdb->postmeta . " WHERE meta_value='ark_gateway' ORDER BY post_id DESC LIMIT 10;" );
	
	// Execute the query
	$arkorders = $wpdb->get_results( $arkordersquery );
	
	// Determine valid database connection
	if( !empty( $arkorders ) ) 
	{
		// Conclude with a table containing information on last 10 ARKCommerce payment gateway orders
		$table_header_orders = '<hr><b>Latest Orders via ARKCommerce</b><hr><table class="arkcommerce-table"><b><thead><tr><th>' . __( 'Order ID', 'arkcommerce' ) . '</th><th>' . __( 'Order Total (Ѧ)', 'arkcommerce' ) . '</th><th>' . __( 'Order Status', 'arkcommerce' ) . '</th></thead></b></tr>';
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
// Add ARKCommerce Status widget to the admin dashboard									//
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
// ARKCommerce Manual TX Check admin dashboard widget									//
// @output ARKCommerce Manual TX Check Dashboard Widget									//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_display_tx_check_widget() 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$storewalletaddress = $arkgatewaysettings['arkaddress'];
	$explorerurl = 'https://explorer.ark.io/';
	
	// DARK Mode settings and display (if enabled)
	if( $arkgatewaysettings['darkmode'] == 'yes' )
	{
		$explorerurl = 'https://dexplorer.ark.io/';
		$storewalletaddress = $arkgatewaysettings['darkaddress'];
		$displaydarkinfo = ( '<span class="dashicons dashicons-info" style="color:black;"> </span> <b>' . __( 'ARKCommerce DARK Mode enabled', 'arkcommerce' ) . '</b>.<hr>' );
	}	
	// Display form
	echo( '<form onsubmit="return false;"><table class="form-table">' . $displaydarkinfo . '<b>' . __( 'Store Wallet', 'arkcommerce' ) . ': <a class=arkcommerce-link" target="_blank" href="' . $explorerurl. 'address/' . $storewalletaddress . '">' . $storewalletaddress . '</a></b><hr><fieldset><legend><span class="dashicons dashicons-admin-links"> </span> <strong>' . __( 'Transaction ID', 'arkcommerce' ) . '</strong></legend><input type="hidden" id="explorerurl" value="' . $explorerurl . 'tx/"><input type="text" style="width:100%;" id="txid_manual_entry"></fieldset><br><input type="button" style="width: 100%;" value="' . __( 'Open in Explorer', 'arkcommerce' ) . '" onclick="' . "ARKExplorer(document.getElementById('explorerurl').value, document.getElementById('txid_manual_entry').value);return false;" . '"></table></form>' );
}
//////////////////////////////////////////////////////////////////////////////////////////
// Add ARKCommerce Manual TX Check widget to the admin dashboard						//
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
// ARKCommerce Meta Box widget															//
// @output ARKCommerce Meta Box WooCommerce Widget										//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_display_meta_box_widget() 
{
	// Gather and/or set variables
    $arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$arkrestapikey = $arkgatewaysettings['arkapikey'];
	$arknode = $arkgatewaysettings['arknode'];
	$storewalletaddress = $arkgatewaysettings['arkaddress'];
	$sessiontoken = arkcommerce_get_session_token();
	$explorerurl = 'https://explorer.ark.io/';
	
	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' ) 
	{
		$explorerurl = 'https://dexplorer.ark.io/';
		$storewalletaddress = $arkgatewaysettings['darkaddress'];
		$darkrestapikey = $arkgatewaysettings['darkapikey'];
	}
	// Construct a store ARK wallet address query URI for ARKCommerce Node
	if( $arkgatewaysettings['darkmode'] == 'yes' ) $ark_txqueryurl = "https://$arknode/api/v2/dark/_table/transactions?fields=id%2C%20amount%2C%20vendorField&filter=recipientId%3D$storewalletaddress&limit=5&order=timestamp%20dsc&api_key=$darkrestapikey&session_token=$sessiontoken";
	else $ark_txqueryurl = "https://$arknode/api/v2/ark/_table/transactions?fields=id%2C%20amount%2C%20vendorField&filter=recipientId%3D$storewalletaddress&limit=5&order=timestamp%20dsc&api_key=$arkrestapikey&session_token=$sessiontoken";
	
	// Query ARKCommerce API for transactions
	$arkbridgeresponse = wp_remote_get( $ark_txqueryurl );
	
	// ARKCommerce API response validation
	if( is_array( $arkbridgeresponse ) ) 
	{
		$arkresponse = json_decode( $arkbridgeresponse['body'], true );
		$arktxarray = $arkresponse['resource'];
		
		// Determine whether the ARKCommerce Node has a valid database connection		
		if( !empty( $arktxarray[0] ) ) 
		{
			// Display info
			$table_header_tx = sprintf( '<span class="dashicons dashicons-info" style="color:lime;"> </span> <b style="color:black;">' . __( 'ARKCommerce Node operational', 'arkcommerce' ) . '.<br>' . __( 'Latest ARK transactions into', 'arkcommerce' ) . ' <a class=arkcommerce-link" target="_blank" href="' . $explorerurl . 'address/%s">' . __( 'store wallet', 'arkcommerce' ) .  '</a>:</b><hr>', $storewalletaddress );
			
			// Form table and iterate rows through the array
			$content = '<table class="arkcommerce-meta-table"><b><thead><tr><th>' . __( 'Amount', 'arkcommerce' ) . ' (Ѧ)</th><th>SmartBridge</th></thead></b></tr>';
			foreach( $arktxarray as $arktx ):setup_postdata( $arktx );
				$content .= ( '<tr><td><a target="_blank" href="' . $explorerurl . 'tx/' . $arktx[id] . '">' . number_format( ( float ) $arktx['amount'] / 100000000, 8, '.', '' ) . '</a></td><td>' . $arktx['vendorField'] . '</td></tr>' );
			endforeach;
			$content .= '</table>';
			echo( $table_header_tx . $content );
		}
		else echo( '<b style="color:orange;">&#9733; </b><b style="color:black;">' . __( 'ARKCommerce Node database currently unavailable', 'arkcommerce' ) . '.</b>' );
	}
	// ARKCommerce Node unavailable
	else echo( '<span class="dashicons dashicons-info" style="color:red;"> </span> <b style="color:black;">' . __( 'ARKCommerce Node unresponsive', 'arkcommerce' ) . '.</b>' ); 
} 
//////////////////////////////////////////////////////////////////////////////////////////
// Add ARKCommerce Meta Box to the WooCommerce order admin page							//
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
	
	// Add Sub menu ARKCommerce Server and Manager pages if service provider edition
	if( file_exists( plugin_dir_path( __FILE__ ) . 'arkcommerceserver.php' ) )
	{
		add_submenu_page(	'arkcommerce_root',
						__( 'ARKCommerce Server', 'arkcommerce' ),
						__( 'Server', 'arkcommerce' ),
						'administrator',
						'arkcommerce_server',
						'arkcommerce_server' );
		add_submenu_page(	'arkcommerce_root',
						__( 'ARKCommerce Manager', 'arkcommerce' ),
						__( 'Manager', 'arkcommerce' ),
						'administrator',
						'arkcommerce_manager',
						'arkcommerce_manager' );
	}
	// Remove automatically-added Sub menu ARKCommerce
	remove_submenu_page( 'arkcommerce_root','arkcommerce_root' );
}
add_action( 'admin_menu', 'arkcommerce_add_menu_pages' );

//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Admin Preferences page													//
// @output ARKCommerce Admin Preferences page											//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_preferences() 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$store_currency = get_woocommerce_currency();
	$arktimeout = arkcommerce_get_order_timeout();
	$storewalletaddress = $arkgatewaysettings['arkaddress'];
	$formchange = false;
	$supported_store_currency = arkcommerce_check_currency_support();
	$arkexchangerate = arkcommerce_get_exchange_rate();
	$arkchosen = '';
	$exoticcurrencychosen = '';
	$adminslist = get_users( array( 'role' => 'administrator' ) );
	$nonce = wp_nonce_field( 'arkcommerce_preferences', 'arkcommerce_preferences_form' );
	$explorerurl = 'https://explorer.ark.io/';
	
	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' )
	{
		$storewalletaddress = $arkgatewaysettings['darkaddress'];
		$explorerurl = 'https://dexplorer.ark.io/';
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
				(' . __( 'opens in ARK blockchain explorer application; also accessible via QR Code', 'arkcommerce' ) . ')
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
											(' . __( 'e.g. 1.01 multiplier is 1% over market rate', 'arkcommerce' ) . ')
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
										(' . __( 'Ignored if ARK is the chosen default store currency. Automatic exchange rate for supported currencies sourced through periodic updates from coinmarketcap.com. Unsupported currencies only allow a fixed exchange rate', 'arkcommerce' ) . ')
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
										(' . __( 'Notifications contain additional order information and get sent to the chosen administration user email address; in case of open orders during ARKCommerce Node inaccessibility the administration user is alerted to monitor and complete them manually', 'arkcommerce' ) . ')
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
// ARKCommerce Preferences Form handler													//
// @param array $_POST																	//
// @return result URI																	//
// @record array arkgatewaysettings														//
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
// ARKCommerce Navigator page															//
// @output ARKCommerce Admin Navigator page												//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_navigator() 
{
	// Gather and/or set variables
	global $wpdb;
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$arkrestapikey = $arkgatewaysettings['arkapikey'];
	$arknode = $arkgatewaysettings['arknode'];
	$storewalletaddress = $arkgatewaysettings['arkaddress'];
	$arkblockheight = arkcommerce_get_block_height();
	$wallet_balance = arkcommerce_get_wallet_balance();
	$sessiontoken = arkcommerce_get_session_token();
	$explorerurl = 'https://explorer.ark.io/';
	
	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' )
	{
		$storewalletaddress = $arkgatewaysettings['darkaddress'];
		$darkrestapikey = $arkgatewaysettings['darkapikey'];
		$explorerurl = 'https://dexplorer.ark.io/';
	}
	// Validate block height response
	if( $arkblockheight != 0 ) $arknodestatus = ( '<span class="dashicons dashicons-info" style="color:lime;"> </span> <b style="color:black;">' . __( 'ARKCommerce Node operational', 'arkcommerce' ) . '. ' . __( 'ARK block height', 'arkcommerce' ) . ': ' . $arkblockheight . '</b>' );
	else $arknodestatus = ( '<span class="dashicons dashicons-info" style="color:red;"> </span> <b style="color:black;">' . __( 'ARKCommerce Node unresponsive', 'arkcommerce' ) . '.</b>' );
	
	// Construct a query URI for Coinmarketcap; expected number of results: 1
	$cmc_queryurl = "https://api.coinmarketcap.com/v1/ticker/ark/?convert=EUR";
	
	// Query CoinMarketCap API for ARK market price (USD, EUR, BTC)
	$cmcresponse = wp_remote_get( $cmc_queryurl );
	
	// CMC API response validation
	if( is_array( $cmcresponse) ) 
	{
		$arkmarketprice = json_decode( $cmcresponse['body'], true );
		
		// Determine whether the CMC node produces valid result, if so construct price info
		if( !empty( $arkmarketprice[0][price_usd] ) ) $cmc_prices = sprintf( ( '<b>' . __( 'ARK market price', 'arkcommerce') . ': %s USD | %s EUR | %s BTC </b>' ), $arkmarketprice[0][price_usd], $arkmarketprice[0][price_eur], $arkmarketprice[0][price_btc] );
		
		// Construct CMC error info
		else $cmc_prices = ( '<span class="dashicons dashicons-info" style="color:red;"> </span> <b style="color:black;">' . __( 'Unable to display ARK market price from coinmarketcap.com', 'arkcommerce' ) . '</b>' );
	}
	// Construct CMC error info
	else $cmc_prices = ( '<span class="dashicons dashicons-info" style="color:red;"> </span> <b style="color:black;">' . __( 'Unable to display ARK market price from coinmarketcap.com', 'arkcommerce' ) . '</b>' );
	
	// Display header
	echo( arkcommerce_headers ( 'navigator' ) . '<hr>' . $arknodestatus . ' | ' . $cmc_prices . '<hr><b>' . __( 'ARK wallet address', 'arkcommerce' ) . ' <a class="arkcommerce-link" target="_blank" href="' . $explorerurl . 'address/' . $storewalletaddress . '">' . $storewalletaddress . '</a> ' . __( 'balance', 'arkcommerce' ) . ': Ѧ' . $wallet_balance . '</b><i> (' . __( 'opens in ARK blockchain explorer application', 'arkcommerce' ) . ')</i></div>' );
	
	// Construct a store ARK wallet address transactions query URI for ARKCommerce Node
	if( $arkgatewaysettings['darkmode'] == 'yes' ) $ark_txqueryurl = "https://$arknode/api/v2/dark/_table/transactions?fields=id%2C%20senderId%2C%20amount%2C%20vendorField&filter=recipientId%3D$storewalletaddress&limit=10&order=timestamp%20dsc&api_key=$darkrestapikey&session_token=$sessiontoken";
	else $ark_txqueryurl = "https://$arknode/api/v2/ark/_table/transactions?fields=id%2C%20senderId%2C%20amount%2C%20vendorField&filter=recipientId%3D$storewalletaddress&limit=10&order=timestamp%20dsc&api_key=$arkrestapikey&session_token=$sessiontoken";
	
	// Query ARKCommerce Node API for store ARK wallet address transactions
	$arkbridgeresponsetx = wp_remote_get( $ark_txqueryurl );
	
	// ARKCommerce API response validation
	if( is_array( $arkbridgeresponsetx ) ) 
	{
		$arktxresponse = json_decode( $arkbridgeresponsetx['body'], true );
		$arktxarray = $arktxresponse['resource'];
		
		// Determine whether the ARKCommerce Node has a valid database connection
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
	}
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
// ARKCommerce Information page															//
// @output ARKCommerce Admin Information page											//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_information() 
{
	// Gather and/or set variables
	$arkgatewaysettings = get_option( 'woocommerce_ark_gateway_settings' );
	$explorerurl = 'https://explorer.ark.io/';
	
	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' ) $explorerurl = 'https://dexplorer.ark.io/';
	
	// Determine whether ARKCommerce is enabled
	if( $arkgatewaysettings['enabled'] == 'yes' )
	{
		if( $arkgatewaysettings['darkmode'] == 'yes' ) $displayinfo = ( '<span class="dashicons dashicons-info" style="color:black;"> </span> <b>' . __( 'ARKCommerce DARK Mode enabled', 'arkcommerce' ) . '</b>.' );
		else $displayinfo = ( '<span class="dashicons dashicons-info" style="color:#4ab6ff;"> </span> <span style="color:black;"><b>' . __( 'ARKCommerce payment gateway enabled', 'arkcommerce' ) . '</b>.</span>' );
	}
	else $displayinfo = ( '<span class="dashicons dashicons-info" style="color:red;"> </span> <span style="color:black;"><b>' . __( 'ARKCommerce payment gateway disabled', 'arkcommerce' ) . '</b>.</span>' );
	
	// Compose ARKCommerce content
	$usefulinks = sprintf( __( 'ARK Wallet Guides', 'arkcommerce' ) . ': <a class="arkcommerce-link" target="_blank" href="%s">%s</a> | <a class="arkcommerce-link" target="_blank" href="%s">%s</a><br>' . __( 'ARK Blockchain Information', 'arkcommerce' ) . ': <a class="arkcommerce-link" target="_blank" href="%s">%s</a> | <a class="arkcommerce-link" target="_blank" href="%s">%s</a> | <a class="arkcommerce-link" target="_blank" href="%s">%s</a><br>' . __( 'ARK News Sources', 'arkcommerce' ) . ': <a class="arkcommerce-link" target="_blank" href="%s">%s</a> | <a class="arkcommerce-link" target="_blank" href="%s">%s</a> | <a class="arkcommerce-link" target="_blank" href="%s">%s</a><br>' . __( 'WordPress Cron Information', 'arkcommerce' ) . ': <a class="arkcommerce-link" target="_blank" href="%s">%s</a><br>' . __( 'Free ARK', 'arkcommerce' ) . ': <a class="arkcommerce-link" target="_blank" href="%s">%s</a><br>' . __( 'Free DARK', 'arkcommerce' ) . ': <a class="arkcommerce-link" target="_blank" href="%s">%s</a>', 'https://blog.ark.io/how-to-generate-your-own-ark-address-and-passphrase-5e4e1257ca5e', __( 'ARK Wallet Setup', 'arkcommerce' ), 'https://blog.ark.io/full-ledger-nano-s-hardware-wallet-guide-for-ark-7bf7bfff4cef', __( 'Ledger Nano S Hardware Wallet Guide', 'arkcommerce' ), 'https://blog.ark.io/how-to-vote-or-un-vote-an-ark-delegate-and-how-does-it-all-work-819c5439da68', __( 'ARK Voting', 'arkcommerce' ), $explorerurl, __( 'ARK Blockchain Explorer', 'arkcommerce' ), 'https://www.arknode.net/VoteReport.txt', __( 'ARK Delegates Report', 'arkcommerce' ), 'https://blog.ark.io/', __( 'ARK Official Blog', 'arkcommerce' ), 'https://arkecosystem.slack.com', __( 'ARK Community Fund', 'arkcommerce' ), 'https://arkcommunityfund.com/', __( 'ARK Official Slack', 'arkcommerce' ), 'https://developer.wordpress.org/plugins/cron/hooking-into-the-system-task-scheduler/', __( 'Hooking WP-Cron Into the System Task Scheduler', 'arkcommerce' ), 'https://classicdelegate.biz/faucet', __( 'Biz_Classic ARK Community Faucet', 'arkcommerce' ), 'https://kristjank.github.io/dark-paperwallet/', __('DARK Paper Wallet Generator (automatically dispenses free DARK tokens)', 'arkcommerce' ) );
	$commandstring = "define('DISABLE_WP_CRON', true);";
	
	// Display body
	echo( 
			arkcommerce_headers( 'information' ) . '
			<hr>
			' . $displayinfo . '
			<hr>
			<b>
				' . __( 'Thank you for choosing ARKCommerce', 'arkcommerce' ) . '.
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
		$header = ( '<div class="arkcommerce-wrap"><img width="120" height="95" alt="ARKCommerce" class="arkcommerce-pic-left" src="' . $arkcommerce_logo . '">' . $ark_links . $arkcommerce_link . ' | ' . $gateway_preferences_link . ' | ' . $gateway_navigator_link . ' | ' . $gateway_information_link . ' |<hr><span style="color:red;" class="dashicons dashicons-warning"> </span> <b style="color:black;">' . __( 'WARNING: WHEN CREATING A NEW WALLET MAKE SURE TO SAFELY STORE THE PASSPHRASE! THERE IS NO WAY OF RECOVERING A LOST ONE!', 'arkcommerce' ) . '<hr>' . __( 'ARKCommerce payment gateway enables merchants to receive payments in ARK crypto currency without requiring or storing wallet passphrases.', 'arkcommerce' ) . '</b></div>' );
		if( DISABLE_WP_CRON !== true ) $header .= ( '<p><span style="color:#4ab6ff;" class="dashicons dashicons-clock"> </span> <b style="color:black;">' . __( 'Both automatic exchange rate synchronization and transaction verification queries depend on "WP-Cron" task; for details refer to', 'arkcommerce' ) . ' <a class="arkcommerce-link" target="_blank" href="' . admin_url( "admin.php?page=arkcommerce_information" ) . '">' . __( 'ARKCommerce Information', 'arkcommerce' ) . '</a></b>.</p>' );
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
		$header = ( '<div class="wrap"><p><h1>ARKCommerce ' . __( 'Information', 'arkcommerce' ) . '</h1></p><div class="arkcommerce-wrap"><img width="100" height="80" alt="ARKCommerce" class="arkcommerce-pic-left" src="' . $arkcommerce_logo . '">' . $ark_links . $arkcommerce_link . ' | ' . $gateway_settings_link . ' | ' . $gateway_preferences_link . ' | ' . $gateway_navigator_link . ' |<img width="161" height="80" alt="GPLv3" class="arkcommerce-pic-right" src="' . plugin_dir_url( __FILE__ ) . 'assets/images/gplv3.png' . '">' );
	}
	elseif( $headertype == 'server' && file_exists( plugin_dir_path( __FILE__ ) . 'arkcommerceserver.php' ) )
	{
		$header = ( '<div class="wrap"><p><h1>ARKCommerce ' . __( 'Server', 'arkcommerce' ) . '</h1></p><div class="arkcommerce-wrap"><img width="100" height="80" alt="ARKCommerce" class="arkcommerce-pic-left" src="' . $arkcommerce_logo . '">' . $ark_links . $arkcommerce_link . ' | ' . $gateway_settings_link . ' | ' . $gateway_preferences_link . ' | ' . $gateway_navigator_link . ' | ' . $gateway_information_link . ' |<img width="80" height="80" alt="spika" class="arkcommerce-pic-right" src="' . plugin_dir_url( __FILE__ ) . 'assets/images/spika.png' . '">' );
	}
	elseif( $headertype == 'manager' && file_exists( plugin_dir_path( __FILE__ ) . 'arkcommerceserver.php' ) )
	{
		$header = ( '<div class="wrap"><p><h1>ARKCommerce ' . __( 'Manager', 'arkcommerce' ) . '</h1></p><div class="arkcommerce-wrap"><img width="100" height="80" alt="ARKCommerce" class="arkcommerce-pic-left" src="' . $arkcommerce_logo . '">' . $ark_links . $arkcommerce_link . ' | ' . $gateway_settings_link . ' | ' . $gateway_preferences_link . ' | ' . $gateway_navigator_link . ' | ' . $gateway_information_link . ' |<img width="80" height="80" alt="spika" class="arkcommerce-pic-right" src="' . plugin_dir_url( __FILE__ ) . 'assets/images/spika.png' . '">' );
	}
	return $header;
}
//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Footer on all ARKCommerce pages											//
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
if( file_exists( plugin_dir_path( __FILE__ ) . 'arkcommerceserver.php' ) && isset( $_GET["page"] ) && $_GET["page"] == "arkcommerce_server" ) add_filter( 'admin_footer_text', 'arkcommerce_footer_message' );
if( file_exists( plugin_dir_path( __FILE__ ) . 'arkcommerceserver.php' ) && isset( $_GET["page"] ) && $_GET["page"] == "arkcommerce_manager" ) add_filter( 'admin_footer_text', 'arkcommerce_footer_message' );
if( isset( $_GET["page"] ) && isset( $_GET["section"] ) && $_GET["page"] == "wc-settings" && $_GET["section"] == "ark_gateway" ) add_filter( 'admin_footer_text', 'arkcommerce_footer_message' );

//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Footer version display on all ARKCommerce pages							//
// @output ARKCommerce Foorer Version													//
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
if( file_exists( plugin_dir_path( __FILE__ ) . 'arkcommerceserver.php' ) && isset( $_GET["page"] ) && $_GET["page"] == "arkcommerce_server" ) add_filter( 'update_footer', 'arkcommerce_footer_version', 11 );
if( file_exists( plugin_dir_path( __FILE__ ) . 'arkcommerceserver.php' ) && isset( $_GET["page"] ) && $_GET["page"] == "arkcommerce_manager" ) add_filter( 'update_footer', 'arkcommerce_footer_version', 11 );
if( isset( $_GET["page"] ) && isset( $_GET["section"] ) && $_GET["page"] == "wc-settings" && $_GET["section"] == "ark_gateway" ) add_filter( 'update_footer', 'arkcommerce_footer_version', 11 );

//////////////////////////////////////////////////////////////////////////////////////////
// END OF ARKCOMMERCE																	//
//////////////////////////////////////////////////////////////////////////////////////////