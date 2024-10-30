<?php

/*
Billbrd.io for WooCommerce is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Billbrd.io for WooCommerce is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Billbrd.io for WooCommerce. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

/*
 * This class defines the main methods used by the Billbrd.io for WooCommerce plugin
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Include the utilities class
require_once(plugin_dir_path(__FILE__) . 'class.billbrd-utils.php');

class Billbrd {
	
	// Run Billbrd.io's tracking script
	public function blbrd_enqueue_script() {

		// Settings set by admin
		$options = get_option('billbrd_settings');

		// If tracking enabled and public API key provided, run tracking script
		if ($options['billbrd_tracking_status'] && strlen($options['billbrd_public_api_key']) > 0) {

			$blbrd_vars = array(
				"public_api_key" => $options['billbrd_public_api_key']
			);
			wp_register_script("billbrd-wc-tracking", rtrim(plugin_dir_url(__FILE__), '/') . "/billbrd-tracking.js", array(), '1.0', false);
			wp_enqueue_script("billbrd-wc-tracking");
			wp_localize_script("billbrd-wc-tracking", "blbrd_vars", $blbrd_vars);

		}
	}
	
	// Send order data to Billbrd.io for orders that are "processing" or "completed"
	public function blbrd_woocommerce_order_status_processing_completed($order_id)
	{

		// Settings set by admin
		$options = get_option('billbrd_settings');

		// If tracking enabled and public and private API keys are provided, get order data and post to Billbrd.io
		if ($options['billbrd_tracking_status'] and strlen($options['billbrd_public_api_key']) > 0 and strlen($options['billbrd_private_api_key']) > 0) {

			// Initialize array for storing order data
			$order_data = array();

			// Fetch order data from WooCommerce and store in new variable
			$order = new WC_Order($order_id);
			
			// Set store domain
			$order_data['store_domain'] = sanitize_url($_SERVER['HTTP_HOST']);

			// Set order id
			$order_data['order_id'] = $order_id;
			
			// Set WooCommerce order key
			$order_data['order_key'] = $order->get_order_key();

			// Set order status
			$order_data['status'] = $order->get_status();
		
			// Set date created, completed, and paid
			$order_data['date_created'] = $order->get_date_created();
			$order_data['date_paid'] = $order->get_date_paid();
			$order_data['date_completed'] = $order->get_date_completed();
		
			// Set order line items
			$items = $order->get_items();
			$order_data['items'] = Billbrd_Utils::get_item_data($items);

			// Set order subtotal after discount, total, shipping fees, and total tax
			$order_data['subtotal_after_discounts'] = $order->get_subtotal() - abs($order->get_total_discount());
			$order_data['total'] = $order->get_total();
			$order_data['shipping'] = $order->get_total_shipping();
			$order_data['tax'] = $order->get_total_tax();

			// Set used Billbrd.io coupons and total disocunt
			$coupon_codes = $order->get_coupon_codes();
			$blbrd_coupon_codes = null;
			if (count($coupon_codes) > 0) {
				$blbrd_coupon_codes = implode(',', $coupon_codes);
			}
			$order_data['total_discount'] = abs($order->get_total_discount());

			$order_data['promo_codes'] = $blbrd_coupon_codes;

			// Set affiliate revenue based on used coupons
			foreach ($coupon_codes as $coupon_code) {

				$coupon = new WC_Coupon($coupon_code);
				
				if ($coupon->meta_exists('billbrd_coupon')) {
					$affiliate_revenue = 0;
					if ($coupon->enable_free_shipping() or count($coupon_codes) > 1) {
						$affiliate_revenue = $order_data['subtotal_after_discounts'];
					} else {
						foreach ($order_data['items'] as $item) {
							if ($item['price'] !== $item['price_after_discount']) {
								$affiliate_revenue = $affiliate_revenue + $item['price_after_discount']*$item['quantity'];
							}
						}
					}
					$order_data['affiliate_revenue'] = $affiliate_revenue == 0 ? $order_data['subtotal_after_discounts'] : $affiliate_revenue;
					break;
				}
			}


			// Set order currency
			$order_data['currency_code'] = $order->get_currency();

			// Set customer first name, last name, country, and phone number
			$first_name = trim(@get_post_meta($order_id, 'billing_first_name', true));
			$address_type = (strlen($first_name) > 0) ? "_billing_" : "_shipping_";

			$order_data['customer']['first_name'] = $order->get_billing_first_name();
			$order_data['customer']['last_name'] = $order->get_billing_last_name();
			$order_data['customer']['country'] = $order->get_billing_country();
			$order_data['customer']['phone'] = $order->get_billing_phone();

			// Set customer email
			$order_data['customer']['email'] = (!empty($order->get_billing_email()) ? $order->get_billing_email() : null);

			// Set customer ip
			$order_data['customer']['ip_address'] = Billbrd_Utils::get_ip();


			// Set up request data
			$request_content = array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Basic ' . base64_encode($options['billbrd_public_api_key']) . ':' . base64_encode($options['billbrd_private_api_key']),
				), 'body' => wp_json_encode($order_data)
			);

			// Post data to Billbrd.io
			wp_safe_remote_post(BILLBRD_API_URL . BILLBRD_ENDPOINT_PREFIX . 'order_processing_completed', $request_content);
		}

	}

	// Send order data to Billbrd.io for orders that are "cancelled"
	public function blbrd_woocommerce_order_status_cancelled($order_id)
	{

		// Settings set by admin
		$options = get_option('billbrd_settings');

		// If tracking enabled and public and private API keys are provided, post refunded order to Billbrd.io
		if ($options['billbrd_tracking_status'] and strlen($options['billbrd_public_api_key']) > 0 and strlen($options['billbrd_private_api_key']) > 0) {

			// Initialize array for storing order data
			$refund_data = array();
			
			// Set store domain
			$refund_data['store_domain'] = sanitize_url($_SERVER['HTTP_HOST']);

			// Set order id of the refunded order
			$refund_data['order_id'] = $order_id;

			// Fetch order data from WooCommerce and store in new variable
			$order = new WC_Order($order_id);

			// Set order line items of the refunded order
			$items = $order->get_items();
			$refund_data['items'] = Billbrd_Utils::get_item_data($items);

			// Set up request data
			$request_content = array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Basic ' . base64_encode($options['billbrd_public_api_key']) . ':' . base64_encode($options['billbrd_private_api_key']),
				), 'body' => wp_json_encode($refund_data)
			);

			// Post data to Billbrd.io
			return wp_safe_remote_post(BILLBRD_API_URL . BILLBRD_ENDPOINT_PREFIX . 'order_cancelled', $request_content);

		}

	}

	// Send order data to Billbrd.io for orders that are "refunded"
	public function blbrd_woocommerce_order_status_refunded($order_id)
	{

		// Settings set by admin
		$options = get_option('billbrd_settings');

		// If tracking enabled and public and private API keys are provided, post refunded order to Billbrd.io
		if ($options['billbrd_tracking_status'] and strlen($options['billbrd_public_api_key']) > 0 and strlen($options['billbrd_private_api_key']) > 0) {

			// Initialize array for storing order data
			$refund_data = array();
			
			// Set store domain
			$refund_data['store_domain'] = sanitize_url($_SERVER['HTTP_HOST']);

			// Set order id of the refunded order
			$refund_data['order_id'] = $order_id;

			// Fetch order data from WooCommerce and store in new variable
			$order = new WC_Order($order_id);

			// Set order line items of the refunded order
			$items = $order->get_items();
			$refund_data['items'] = Billbrd_Utils::get_item_data($items);

			// Set up request data
			$request_content = array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Basic ' . base64_encode($options['billbrd_public_api_key']) . ':' . base64_encode($options['billbrd_private_api_key']),
				), 'body' => wp_json_encode($refund_data)
			);

			// Post data to Billbrd.io
			return wp_safe_remote_post(BILLBRD_API_URL . BILLBRD_ENDPOINT_PREFIX . 'order_refunded', $request_content);

		}

	}

	// Send checkout data to Billbrd.io
	public function blbrd_thankyou($order_id) {

		// Fetch order data from WooCommerce
	    $order = wc_get_order($order_id);
		
		// Settings set by admin
		$options = get_option('billbrd_settings');

		// If tracking enabled and public API key provided, send checkout data to Billbrd.io
		if ($options['billbrd_tracking_status'] && strlen($options['billbrd_public_api_key']) > 0) {

			wp_register_script( 'send-checkout-data', '',);
			wp_enqueue_script( 'send-checkout-data' );
			wp_add_inline_script( 'send-checkout-data', '(function blbrd_thankyou() {window.tracking_obj ? tracking_obj.sendCheckoutEventData(' . wp_kses_post($order_id) . ') : setTimeout(blbrd_thankyou, 10);})();');

		}
	}

	// Show Billbrd.io customer recruitment button
	public function blbrd_cr_button() {

		// Settings set by admin
		$options = get_option('billbrd_settings');

		// Button position variable
		$btn_pos = $options['billbrd_cr_button_position'] ? 'left: 30px; right: unset;' : 'left: unset; right: 30px;';
		
		// If "Become an affiliate" button is enabled, affiliate program id is provided and points to a valid affiliate program, show customer recruitment button
		if ($options['billbrd_cr_button'] and strlen($options['billbrd_cr_id']) > 0 and strlen($options['billbrd_cr_tracking_type']) > 0) {
			?>
				<div style="width: auto;min-width: 0px;min-height: 0px;<?php echo wp_kses_post($btn_pos) ?>top: unset;bottom: 30px;max-width: calc(100% - 60px);height: auto;max-height: calc(100% - 60px);position: fixed !important;z-index: 1514;justify-content: center;border-radius: 0px; display: flex; flex-direction: row; flex-wrap: wrap; overflow: visible; vertical-align: baseline;">
					<button id="blbrd_become_an_affiliate" style="background-color: <?php echo wp_kses_post($options['billbrd_cr_button_color']) ?>; font-family: var(--font_default); font-size: 15px; font-weight: 600; color: <?php echo wp_kses_post($options['billbrd_cr_font_color']) ?>; text-align: center; line-height: 1; border-radius: 40px; padding: 12px 12px; transition: background 200ms ease 0s; align-self: flex-start; min-width: 130px; max-width: 200px; order: 1; min-height: 45px; width: 130px; flex-grow: 1; height: max-content; margin: 0px; z-index: 2; background-image: none; background-position: initial; background-size: initial; background-repeat: initial; background-attachment: initial; background-origin: initial; background-clip: initial; box-shadow: 0px 0px 10px <?php echo wp_kses_post($options['billbrd_cr_button_color']) ?>;border:0px; cursor: pointer;">Become an affiliate</button>
				</div>
			<?php

			wp_register_script( 'rcrt-tn-click-event', '',);
			wp_enqueue_script( 'rcrt-tn-click-event' );
			wp_add_inline_script( 'rcrt-tn-click-event', 'document.getElementById("blbrd_become_an_affiliate").onclick = () => {
						window.open("' . wp_kses_post(BILLBRD_AFFILIATE_SIGNUP_URL) . '?referrer_domain=' . wp_kses_post($_SERVER["HTTP_HOST"]) . '&pid=' . wp_kses_post($options["billbrd_cr_id"]) . '");
					}');

		}
	}

	// Show Billbrd.io cutsomer recruitment modal
	public function blbrd_recruit($order_id) {

		// Settings set by admin
		$options = get_option('billbrd_settings');

		// If customer recruitment is enabled, affiliate program id is provided and points to a valid affiliate program, show customer recruitment modal
		if ($options['billbrd_cr_status'] and strlen($options['billbrd_cr_id']) > 0 and strlen($options['billbrd_cr_tracking_type']) > 0) {
			// Get customer details from the woocommerce order
			$order = new WC_Order($order_id);
			$billing_email = $order->get_billing_email();
			$billing_first_name = $order->get_billing_first_name();
			$billing_last_name = $order->get_billing_last_name();

			// Set data to send to customer recruitment modal
			$blbrd_cr_vars = array(
				"email" => $billing_email,
				"first_name" => $billing_first_name,
				"last_name" => $billing_last_name,
				"domain" => sanitize_url($_SERVER['HTTP_HOST']),
				"id" => $options['billbrd_cr_id'],
				"promotion_type" => $options['billbrd_cr_tracking_type'],
				"commission" => $options['billbrd_cr_commission'],
				"clearing" => $options['billbrd_cr_clearing'],
				"cookie_window" => $options['billbrd_cr_cookie_window'],
				"code_discount_type" => $options['billbrd_cr_code_type'],
				"code_discount_amount" => $options['billbrd_cr_code_amount'],
				"code_free_shipping" => $options['billbrd_cr_code_free_shipping'],
				"code_min_order_amount" => $options['billbrd_cr_code_min_order_amount'],
				"code_expiry" => $options['billbrd_cr_code_expiry'],
				"code_usage_limit" => $options['billbrd_cr_code_usage_limit'],
				"currency" => get_woocommerce_currency(),
				"loc" => BILLBRD_AFFILIATE_SIGNUP_URL,
				"color" => $options['billbrd_cr_button_color'],
				"font_color" => $options['billbrd_cr_font_color']
			);

			// Run the customer recruitment script
			wp_register_script("scripts_blbrd_cr", plugin_dir_url(__FILE__) . "billbrd-customer-recruitment.js", array(), '1.0', true);
			wp_enqueue_script("scripts_blbrd_cr");
			wp_localize_script("scripts_blbrd_cr", "blbrd_cr_vars", $blbrd_cr_vars);
		}
	}

	// Get corresponding tracking id and edirect to homepage when Billbrd.io affiliate link is used
	public function blbrd_redirect() {

		// If page is not found, check if Billbrd.io affiliate link and fetch tracking id
		if ( is_404() ) {

			// Settings set by admin
			$options = get_option('billbrd_settings');

			// Set data to send to Billbrd.io for retrieving tracking id
			$url = "http://" . sanitize_url($_SERVER['HTTP_HOST']) . sanitize_url($_SERVER['REQUEST_URI']);
			$url_data = array();
			$url_data['store_domain'] = sanitize_url($_SERVER['HTTP_HOST']);
			$url_data['slug'] = basename(wp_parse_url($url, PHP_URL_PATH));
			$parts = wp_parse_url($url);
			parse_str($parts['query'], $query);
			$url_data['billbrd_request_id'] = $query['billbrd_request_id'];
			$request_content = array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Basic ' . base64_encode($options['billbrd_public_api_key']) . ':' . base64_encode($options['billbrd_private_api_key']),
				), 'body' => wp_json_encode($url_data)
			);

			// Get tracking id from Billbrd.io
			$json = wp_safe_remote_post(BILLBRD_API_URL . 'get_tracking_id', $request_content);
			
			// If tracking id exists, redirect to home URL and append tracking id
			if ( is_wp_error($json) ){

				wp_register_script( 'refresh-page', '',);
				wp_enqueue_script( 'refresh-page' );
				wp_add_inline_script( 'refresh-page', 'window.location=document.location.href;');

			} else {
			
				$body = json_decode($json['body'],TRUE);
				$tracking_id = $body['response']['tracking_id'];

				if ($tracking_id) {
					wp_redirect(esc_url(home_url('?billbrd=' . $tracking_id)));
					exit();
				}
			}
		}
	}
}