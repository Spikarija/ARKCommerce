<?php
/*
ARKCommerce
Copyright (C) 2017-2018 Milan Semen

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
//////////////////////////////////////////////////////////////////////////////////////////
// START OF ARKCOMMERCE MAIL NOTIFICATIONS												//
//////////////////////////////////////////////////////////////////////////////////////////
// Prohibit direct access
if( !defined( 'ABSPATH' ) ) exit;

//////////////////////////////////////////////////////////////////////////////////////////
// ARKCommerce Administrator Mail Notifications on Order Events							//
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

	// DARK Mode settings
	if( $arkgatewaysettings['darkmode'] == 'yes' )
	{
		$storewalletaddress = $arkgatewaysettings['darkaddress'];
		$explorerurl = 'https://dexplorer.ark.io/tx/';
		$exploreraddressurl = 'https://dexplorer.ark.io/address/';
	}
	else
	{
		$storewalletaddress = $arkgatewaysettings['arkaddress'];
		$explorerurl = 'https://explorer.ark.io/tx/';
		$exploreraddressurl = 'https://explorer.ark.io/address/';
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
		if( $order->get_meta( $key = 'ark_expiration_block' ) != 'never' ) $orderexpiry = __( 'This order expires at block', 'arkcommerce' ) . ': ' . $order->get_meta( $key = 'ark_expiration_block' );
		
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
}
//////////////////////////////////////////////////////////////////////////////////////////
// END OF ARKCOMMERCE NOTIFICATIONS														//
//////////////////////////////////////////////////////////////////////////////////////////