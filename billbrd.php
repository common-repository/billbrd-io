<?php
/**
* Plugin Name: Billbrd.io for WooCommerce
* Plugin URI: https://www.billbrd.io/
* Description: This plugin integrates <a href="https://www.billbrd.io">Billbrd.io</a> affiliate tracking with your WooCommerce store.
* Version: 1.1.0
* Author: Billbrd Technologies Ltd.
* License: GPLV2
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
**/

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
 * This file initializes the Billrd.io for WooCommerce plugin and sets up the proper hooks for
 * connecting the site's WooCommerce store to Billbrd.io
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Constant definitions
define('BILLBRD_API_URL','https://billbrd.io/api/1.1/wf/');
define('BILLBRD_AFFILIATE_SIGNUP_URL', 'https://billbrd.io/affiliate-signup');
define('BILLBRD_ENDPOINT_PREFIX', 'wc_');


// Hook the 'plugin_loaded' action to check if WooCommerce installed when plugin loaded
add_action('plugins_loaded', 'blbrd_woocommerce_check');

function blbrd_woocommerce_check() {
	if (!class_exists( 'woocommerce' )) {
		add_action(
			'admin_notices',
			function() {
				echo '<div class="error"><p><strong>' . sprintf('Billbrd.io for WooCommerce requires WooCommerce to be installed and active. You can download %s here.', '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
			}
		);
	}
	return;
}


// Include main class
require_once(plugin_dir_path(__FILE__) . 'class.billbrd.php');


// Instantiate object of class Billbrd
$blbrd = new Billbrd();


// If user is a customer (not an admin)
if (!is_admin()) {

	// Hook to run tracking scripts if tracking is enabled before other functions are executed
	add_action('wp_enqueue_scripts', array($blbrd, 'blbrd_enqueue_script'),0);
	
	// Hook to add customer recruitment button if enabled after other page scripts have been executed
	add_action('wp_footer', array($blbrd, 'blbrd_cr_button'));

	// Hook to append tracking ID if tracking link is used
	add_action('template_redirect', array($blbrd, 'blbrd_redirect'));

}

// If user is an admin
if (is_admin()) {

	// Include admin class
	require_once(plugin_dir_path(__FILE__) . 'class.billbrd-admin.php');

	// Instantiate object of class Billbrd_Admin (adds and initiates plugin settings page)
	$billbrd_admin = new Billbrd_Admin();

	// Hook to show relevant message once plugin activated
	add_action('admin_notices', array($billbrd_admin, 'show_setup_message'));


	// Add 'Settings' link on Plugins screen
	add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($billbrd_admin, 'set_plugin_settings_page_link'));


	// Set plugin settings page styling
	add_action('admin_enqueue_scripts', array($billbrd_admin, 'set_plugin_settings_page_css'));


	// Run color picker script for settings page
	add_action( 'admin_enqueue_scripts', 'blbrd_enqueue_color_picker' );
	function blbrd_enqueue_color_picker( $hook_suffix ) {
		if ($hook_suffix == "woocommerce_page_billbrd-admin-settings") {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'color-picker-script', plugins_url('billbrd-admin.js', __FILE__ ), array( 'wp-color-picker' ), '1.0', true );
		}
	}

}


// Hooks to send order data to Billbrd.io for "processing" and "completed" orders (i.e., orders that have been paid)
add_action('woocommerce_order_status_processing', array($blbrd, 'blbrd_woocommerce_order_status_processing_completed'));
add_action('woocommerce_order_status_completed', array($blbrd, 'blbrd_woocommerce_order_status_processing_completed'));

// Hook to send order data to Billbrd.io for cancelled orders
add_action('woocommerce_order_status_cancelled', array($blbrd, 'blbrd_woocommerce_order_status_cancelled'));

// Hook to send order data to Billbrd.io for refunded orders
add_action('woocommerce_order_status_refunded', array($blbrd, 'blbrd_woocommerce_order_status_refunded'));

// Hook to send checkout data to Billbrd.io when customer lands on thank you page
add_action('woocommerce_thankyou', array($blbrd, 'blbrd_thankyou'));

// Hook to show customer recruitment pop-up on thank you page if enabled
add_action('woocommerce_thankyou', array($blbrd, 'blbrd_recruit'));