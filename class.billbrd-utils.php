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
 * This class defines some utility functions used by the Billbrd.io for WooCommerce plugin
 */


class Billbrd_Utils {

	// Get the customer's IP address
	public static function get_ip() {

		if (getenv('REMOTE_ADDR')) {
			return getenv('REMOTE_ADDR');
		}

		if (getenv('HTTP_X_FORWARDED_FOR')) {
			return getenv('HTTP_X_FORWARDED_FOR');
		}

		if (getenv('HTTP_CLIENT_IP')) {
			return getenv('HTTP_CLIENT_IP');
		}

	}

	// Package order line item data to send back to Billbrd.io
	public static function get_item_data($items) {

		// Initialize array for storing line item data
		$item_data = array();


		// Loop through line items
		$i = 0;
		foreach ($items as $itemId => $itemData) {

			// Get product data
			$productData = $itemData->get_product();
			$productData = $productData->get_data();

			// Get product id or variation id if variation
			$item_id = $itemData->get_variation_id();

			if (!empty($item_id) and $item_id > 0) {
				$product_id = $item_id;
			} else {
				$product_id = $productData['id'];
			}

			// Set item data for current item
			$item_data[$i]['product_id'] = $product_id;
			$item_data[$i]['sku'] = (!empty($productData['sku']) ? $productData['sku'] : 'N/A');
			$item_data[$i]['quantity'] = (int)$itemData->get_quantity();
			$item_data[$i]['price'] = (float)($itemData->get_subtotal() / $itemData->get_quantity());
			$item_data[$i]['price_after_discount'] = (float)($itemData->get_total() / $itemData->get_quantity());

			// Make sure all fields are cleared for next iteration
			unset($productData, $item_id);
			
			$i++;
		}

		return $item_data;
	}

}