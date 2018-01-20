<?php
/*
ARKCommerce
Copyright (C) 2017 Milan Semen

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
//////////////////////////////////////////////////////////////////////////////////////////
//	START OF ARKCOMMERCE FAQ WIDGET														//
//////////////////////////////////////////////////////////////////////////////////////////
define( 'ARKCOMMERCE_FAQ_VERSION', '1.0.0' );

// Prohibit direct access
if( !defined( 'ABSPATH' ) ) exit;

//////////////////////////////////////////////////////////////////////////////////////////
// Add the widget to WP Widgets															//
//////////////////////////////////////////////////////////////////////////////////////////
function arkcommerce_faq_widget_load_faq_widget()
{
    register_widget( 'WP_Widget_ARKFAQ' );
}
add_action( 'widgets_init', 'arkcommerce_faq_widget_load_faq_widget' );

//////////////////////////////////////////////////////////////////////////////////////////
// Add ARKCommerce FAQ Widget scripts													//
//////////////////////////////////////////////////////////////////////////////////////////

function arkcommerce_faq_widget_add_scripts()
{
	wp_enqueue_script( 'arkcommerce_faq_widget_ui_script', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js' );
	wp_enqueue_script( 'jquery_accordion', plugin_dir_url( __FILE__ ) . 'assets/js/accordion.js', array('jquery') );
}
add_action( 'wp_enqueue_scripts', 'arkcommerce_faq_widget_add_scripts', 99 );

//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce FAQ Widget																//
// @class 		WP_Widget_ARKFAQ														//
// @extends		WP_Widget																//
// @package		WordPress/Classes/Widget												//
//////////////////////////////////////////////////////////////////////////////////////////
class WP_Widget_ARKFAQ extends WP_Widget 
{
	function __construct()
	{
		parent::__construct( 'arkcommerce_faq_widget', 'ARKCommerce FAQ', array( 'description' => __( 'ARKCommerce FAQ', 'arkcommerce' ), ) );
	}
//////////////////////////////////////////////////////////////////////////////////////////
//	ARKCommerce FAQ Widget presentation													//
//	@output ARKCommerce FAQ Widget														//
//////////////////////////////////////////////////////////////////////////////////////////
	public function widget( $args, $instance )
	{
		// Before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if( ! empty( $title ) ) echo $args['before_title'] . $title . $args['after_title'];
		
		// Output FAQ section
		echo( '
			<b>
				' . __( 'Frequenty Asked Questions', 'arkcommerce' ) . '
			</b>
			<hr>
			<div id="accordion">
				<h5>
					' . __( 'What is ARK?', 'arkcommerce' ) . '
				</h5>
				<div>
					' . __( 'ARK is a crypto currency that provides users, developers, and startups with innovative blockchain technologies. Its aim is to create an entire ecosystem of linked chains and a virtual spiderweb of endless use-cases that make ARK highly flexible, adaptable, and scalable.', 'arkcommerce' ) . '
				</div>
				<h5>
					' . __( 'Why are ARK prices changing?', 'arkcommerce' ) . '
				</h5>
				<div>
					' . __( 'The store originally prices items in fiat currency and uses automatic exchange rate synchronisation that executes biminutely updates and applies the updated exchange rate on page loads. This makes their conversion into ARK prices reflect exchange rate changes.', 'arkcommerce' ) . '
				</div>
				<h5>
					' . __( "Why mustn't I use an exchange wallet?", 'arkcommerce' ) . '
				</h5>
				<div>
					' . __( 'Because none of the exchanges allow users to enter anything into the SmartBridge field, whereas this store necessitates order number reference entry that enables automatic transaction discovery and order processing by the system.', 'arkcommerce' ) . '
				</div>
				<h5>
					' . __( 'What is order expiry?', 'arkcommerce' ) . '
				</h5>
				<div>
					' . __( 'The store can set up an order expiry period within which customers must execute the ARK payment transaction for their orders, otherwise they get cancelled. This feature serves as a risk management mechanism protecting against crypto currency market volatility.', 'arkcommerce' ) . '
				</div>
				<h5>
					' . __( 'How do I carry out an ARK transaction?', 'arkcommerce' ) . '
				</h5>
				<div>
					' . __( 'Use the ARK Online or ARK Desktop or ARK Mobile wallets to do so. When making a transaction simply follow the instructions provided both upon order placement and inside the order confirmation email. They include store wallet address, SmartBridge reference and the amount total.', 'arkcommerce' ) . '
				</div>
				<h5>
					' . __( 'I paid for my order and got no response!', 'arkcommerce' ) . '
				</h5>
				<div>
					' . __( 'There may be an error storeside that caused automatic order transaction discovery and order processing to fail. Do not hesitate to request assistance via store support channel with your Transaction ID so they are able to manually verify the payment.', 'arkcommerce' ) . '
				</div>
			</div>
			<hr>' );
		
		// After widget arguments defined by themes
		echo $args['after_widget'];
	}
//////////////////////////////////////////////////////////////////////////////////////////
//	ARKCommerce FAQ Widget administration												//
//	@param array $instance																//
//	@output ARKCommerce FAQ Widget Settings												//
//////////////////////////////////////////////////////////////////////////////////////////
	public function form( $instance )
	{
		// Gather and/or set variables
		if ( isset( $instance[ 'title' ] ) ) $title = $instance[ 'title' ];
		else $title = __( 'ARKCommerce FAQ', 'arkcommerce' );
	
		// Widget admin form
		echo( '<p><label for="' . $this->get_field_id( 'title' ) . '"><' . __( 'Title:', 'arkcommerce' ) . '</label> <input class="widefat" id="' . $this->get_field_id( 'title' ) . '" name="' . $this->get_field_name( 'title' ) . '" type="text" value="' . esc_attr( $title ) . '" /></p>' );
	}
//////////////////////////////////////////////////////////////////////////////////////////
//	ARKCommerce FAQ Widget instance updating											//
//	return arr $instance																//
//////////////////////////////////////////////////////////////////////////////////////////
	public function update( $new_instance, $old_instance )
	{
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
}
//////////////////////////////////////////////////////////////////////////////////////////
//	END OF ARKCOMMERCE FAQ WIDGET														//
//////////////////////////////////////////////////////////////////////////////////////////