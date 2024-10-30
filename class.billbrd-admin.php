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
 * This class adds the admin settings page for the Billbrd.io for WooCommerce plugin and defines
 * other admin-related methods
 */


class Billbrd_Admin {

	// Attribute to store admin settings
	private $settings;

	// Construct settings page for admin
	public function __construct()
	{

		if (is_admin()) {

			// Get settings set by admin
			$settings = get_option('billbrd_settings');

			// Hook to admn_init to create settings page fields
			add_action('admin_init', array($this, 'add_settings_page_fields'));

			// Add link to admin settings page in side menu
			add_action('admin_menu', array($this, 'add_submenu_page_link'));


			// Hook the 'activate_[PLUGINNAME]' and 'deactivate_[PLUGINNAME]' actions to update tracking status on Billbrd.io
			register_activation_hook(plugin_dir_path( __FILE__ ) . 'billbrd.php', array($this, 'plugin_activate'));
			register_deactivation_hook(plugin_dir_path( __FILE__ ) . 'billbrd.php', array($this, 'plugin_deactivate'));
		}


	}

	// Send tracking status on plugin activation
	public function plugin_activate()
	{
		$this->send_tracking_status(get_option('billbrd_settings')['billbrd_tracking_status']);
	}
	
	// Send tracking status on plugin deactivation
	public function plugin_deactivate()
	{
		$this->send_tracking_status(false);
	}

	// Add link to admin settings page as a submenu of the WooCommerce menu
	public function add_submenu_page_link()
	{

		add_submenu_page(
			'woocommerce',
			'Billbrd.io',
			'Billbrd.io',
			'manage_options',
			'billbrd-admin-settings',
			array($this, 'display_admin_settings_page')
		);

	}

	// Render admin settings page
	public function display_admin_settings_page()
	{

		global $billbrd_admin;
		
		$billbrd_admin->settings = get_option('billbrd_settings');

		// Exit if user doesn't have permission
		if (!current_user_can('manage_options')) {
			return;
		}

		// Admin settings page header
		echo('<div class="billbrd-header"><h1>Billbrd.io Admin Settings</h1></div>');

		// Show success message after settings are successfully updated
		if (isset($_GET['settings-updated'])) {
			echo '<div class="notice notice-success is-dismissible"><p>Your settings have been saved.</p></div>';
		}

		?>

		<div class="billbrd-wrap">

			<div>

				<img src="<?php echo wp_kses_post(plugins_url('billbrd-logo.png', __FILE__)) ?>" alt="Billbrd.io"/>

				<p>
					<?php echo 'Enable the Billbrd.io for WooCommerce plugin and make sure you enter your API keys correctly to setup tracking on your WooCommerce store.'; ?>
				</p>

				<p>
					<?php echo 'If you do not already have a <a href="https://www.billbrd.io">Billbrd.io</a> account, you can <a href="https://www.billbrd.io/signup">sign up now</a>.'; ?>
				</p>

			</div>

			<div>

				<form method="post" action="options.php">

					<?php

					settings_fields('billbrd_settings');
					do_settings_sections('billbrd-setting-admin');

					?>             
		            <?php

					submit_button();
					?>

				</form>

			</div>

		</div>

		<?php
	}

	// Add and register settings page fields
	public function add_settings_page_fields()
	{

		// Setup section

		add_settings_section(
			'setup_section',
			'Setup',
			array($this, 'setup_section_callback'),
			'billbrd-setting-admin'
		);

		add_settings_field(
			'billbrd_tracking_status',
			'Enable Plugin',
			array($this, 'billbrd_tracking_status_callback'),
			'billbrd-setting-admin',
			'setup_section'
		);

		add_settings_field(
			'billbrd_public_api_key',
			'Your Billbrd.io Public API Key',
			array($this, 'billbrd_public_api_key_callback'),
			'billbrd-setting-admin',
			'setup_section'
		);

		add_settings_field(
			'billbrd_private_api_key',
			'Your Billbrd.io Private API Key',
			array($this, 'billbrd_private_api_key_callback'),
			'billbrd-setting-admin',
			'setup_section'
		);


		// Customer Recruitment section
		
		add_settings_section(
			'customer_recruitment_section',
			'Customer Recruitment',
			array($this, 'customer_recruitment_section_callback'),
			'billbrd-setting-admin'
		);

		add_settings_field(
			'billbrd_cr_status',
			'Enable Customer Recruitment',
			array($this, 'billbrd_cr_status_callback'),
			'billbrd-setting-admin',
			'customer_recruitment_section'
		);
		
		add_settings_field(
			'billbrd_cr_button',
			'"Become an affiliate" Button',
			array($this, 'billbrd_cr_button_callback'),
			'billbrd-setting-admin',
			'customer_recruitment_section'
		);

		add_settings_field(
			'billbrd_cr_button_position',
			'Button Position',
			array($this, 'billbrd_cr_button_position_callback'),
			'billbrd-setting-admin',
			'customer_recruitment_section'
		);
		
		add_settings_field(
			'billbrd_cr_button_color',
			'Button Color',
			array($this, 'billbrd_cr_button_color_callback'),
			'billbrd-setting-admin',
			'customer_recruitment_section'
		);

		add_settings_field(
			'billbrd_cr_font_color',
			'Font Color',
			array($this, 'billbrd_cr_font_color_callback'),
			'billbrd-setting-admin',
			'customer_recruitment_section'
		);

		add_settings_field(
			'billbrd_cr_id',
			'Affiliate Program Id',
			array($this, 'billbrd_cr_id_callback'),
			'billbrd-setting-admin',
			'customer_recruitment_section'
		);
		
		add_settings_field(
			'billbrd_cr_tracking_type',
			'Tracking Type',
			array($this, 'billbrd_cr_tracking_type_callback'),
			'billbrd-setting-admin',
			'customer_recruitment_section'
		);
		
		add_settings_field(
			'billbrd_cr_commission',
			'Commission Percentage',
			array($this, 'billbrd_cr_commission_callback'),
			'billbrd-setting-admin',
			'customer_recruitment_section'
		);

		add_settings_field(
			'billbrd_cr_clearing',
			'Clearing Period',
			array($this, 'billbrd_cr_clearing_callback'),
			'billbrd-setting-admin',
			'customer_recruitment_section'
		);
		
		add_settings_field(
			'billbrd_cr_cookie_window',
			'Link Cookie Window',
			array($this, 'billbrd_cr_cookie_window_callback'),
			'billbrd-setting-admin',
			'customer_recruitment_section'
		);
		
		add_settings_field(
			'billbrd_cr_code_type',
			'Promo Code Discount Type',
			array($this, 'billbrd_cr_code_type_callback'),
			'billbrd-setting-admin',
			'customer_recruitment_section'
		);
		
		add_settings_field(
			'billbrd_cr_code_amount',
			'Promo Code Discount Amount',
			array($this, 'billbrd_cr_code_amount_callback'),
			'billbrd-setting-admin',
			'customer_recruitment_section'
		);
		
		add_settings_field(
			'billbrd_cr_code_free_shipping',
			'Promo Code Free Shipping',
			array($this, 'billbrd_cr_code_free_shipping_callback'),
			'billbrd-setting-admin',
			'customer_recruitment_section'
		);
		
		add_settings_field(
			'billbrd_cr_code_min_order_amount',
			'Promo Code Minimum Order Amount',
			array($this, 'billbrd_cr_code_min_order_amount_callback'),
			'billbrd-setting-admin',
			'customer_recruitment_section'
		);
		
		add_settings_field(
			'billbrd_cr_code_expiry',
			'Promo Code Expiry Date',
			array($this, 'billbrd_cr_code_expiry_callback'),
			'billbrd-setting-admin',
			'customer_recruitment_section'
		);
		
		add_settings_field(
			'billbrd_cr_code_usage_limit',
			'Promo Code Usage Limit',
			array($this, 'billbrd_cr_code_usage_limit_callback'),
			'billbrd-setting-admin',
			'customer_recruitment_section'
		);


		// Register setting

		register_setting(
			'billbrd_settings',
			'billbrd_settings',
			array($this, 'sanitize')
		);
	}

	// Text to display before Setup section
	public function setup_section_callback()
	{


	}

	// Text to display before Customer Recruitment section
	public function customer_recruitment_section_callback()
	{

		print '</br>Click "Get prgram info" once recruitment is enabled and a valid program ID of an approved affiliate program is entered.';
		print '</br></br><b>Note that tracking must be enabled and a valid affiliate program ID entered for customer recruitment and the "Become an affiliate" button to be enabled.</b>';

	}
	
	// Callback for tracking status field
	public function billbrd_tracking_status_callback()
	{

		?><select id="billbrd_tracking_status" name="billbrd_settings[billbrd_tracking_status]" style="width:300px;">
				<option value="0" <?php selected($this->settings['billbrd_tracking_status'],0) ?>>No</option>
				<option value="1"<?php selected($this->settings['billbrd_tracking_status'],1) ?>>Yes</option>
			  </select>
		<?php
		
		// Send selected tracking status to Billbrd.io
		$this->send_tracking_status($this->settings['billbrd_tracking_status']);

		wp_register_script( 'toggle-rcrt-input', '',);
		wp_enqueue_script( 'toggle-rcrt-input' );
		wp_add_inline_script( 'toggle-rcrt-input', 'document.getElementById("billbrd_tracking_status").addEventListener("change", () => {
			var recruitment_modal_input = document.getElementById("billbrd_cr_status");
			if (document.getElementById("billbrd_tracking_status").value !== "1") {
				recruitment_modal_input.disabled = true;
				recruitment_modal_input.value = 0;
				document.getElementById("billbrd_cr_id_placeholder").disabled = true;
			} else {
				recruitment_modal_input.removeAttribute("disabled");
			}

			var recruitment_button_input = document.getElementById("billbrd_cr_button");
			if (document.getElementById("billbrd_tracking_status").value !== "1") {
				recruitment_button_input.disabled = true;
				recruitment_button_input.value = 0;
			} else {
				recruitment_button_input.removeAttribute("disabled");
			}
		})');
	}

	// Send selected tracking status to Billbrd.io
	public function send_tracking_status($tracking_status)
	{
		
		global $billbrd_api_keys_verified;
		
		// Extend timeout
		add_filter( 'http_request_timeout', 'timeout_extend' );
		function timeout_extend( $time )
		{
			// Default timeout is 5
			return 20;
		}
		
		// Set up request data
		if (strlen(get_option('billbrd_settings')['billbrd_public_api_key']) > 0 and strlen(get_option('billbrd_settings')['billbrd_private_api_key']) > 0) {
			$request_content = array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'Authorization' => 'Basic ' . base64_encode(get_option('billbrd_settings')['billbrd_public_api_key']) . ':' . base64_encode(get_option('billbrd_settings')['billbrd_private_api_key']),
				), 'body' => wp_json_encode(array(
					'tracking_active' => (boolean) $tracking_status,
					'gmt_offset' => get_option('gmt_offset'),
					'domain' => sanitize_url($_SERVER['HTTP_HOST']),
				)),
			);
			// Send tracking status to Billbrd.io
			$response = wp_safe_remote_post(BILLBRD_API_URL . 'tracking_status', $request_content);
			$billbrd_api_keys_verified = json_decode($response['body'])->response->api_keys_verified;
		}
	}

	// Callback for public API key field
	public function billbrd_public_api_key_callback()
	{

		global $billbrd_api_keys_verified;
		
		// Pre-fill field with provided value if available
		if (isset($this->settings['billbrd_public_api_key'])) {
			printf(
				'<input type="text" id="billbrd_public_api_key" name="billbrd_settings[billbrd_public_api_key]" value="%s" style="width:300px;" />', esc_attr($this->settings['billbrd_public_api_key'])
			);
		} else {
			printf(
				'<input type="text" id="billbrd_public_api_key" name="billbrd_settings[billbrd_public_api_key]" value="" style="width:300px;" />'
			);
		}
		
		// Show a disabled input field instead if correct API keys have been entered
		if ($billbrd_api_keys_verified != 1) {

			wp_register_script( 'enable-pub-key-input', '',);
			wp_enqueue_script( 'enable-pub-key-input' );
			wp_add_inline_script( 'enable-pub-key-input', 'document.getElementById("billbrd_public_api_key").style.setProperty("display","inline-block");');
					
		} else {

			wp_register_script( 'disable-pub-key-input', '',);
			wp_enqueue_script( 'disable-pub-key-input' );
			wp_add_inline_script( 'disable-pub-key-input', 'document.getElementById("billbrd_public_api_key").style.setProperty("display","none");');
						
			printf(
				'<input type="text" id="billbrd_public_api_key_placeholder" value="%s" style="width:300px;" disabled=true />',
				esc_attr($this->settings['billbrd_public_api_key'])
			);
			
		}
		
	}

	// Callback for private API key field
	public function billbrd_private_api_key_callback()
	{

		global $billbrd_api_keys_verified;
		
		// Pre-fill field with provided value if available
		if (isset($this->settings['billbrd_private_api_key'])) {
			printf(
				'<input type="text" id="billbrd_private_api_key" name="billbrd_settings[billbrd_private_api_key]" value="%s" style="width:300px;" />', esc_attr($this->settings['billbrd_private_api_key'])
			);
		} else {
			printf(
				'<input type="text" id="billbrd_private_api_key" name="billbrd_settings[billbrd_private_api_key]" value="" style="width:300px;" />'
			);
		}
		
		// Show a disabled input field instead if correct API keys have been entered
		if ($billbrd_api_keys_verified != 1) {

			wp_register_script( 'enable-priv-key-input', '',);
			wp_enqueue_script( 'enable-priv-key-input' );
			wp_add_inline_script( 'enable-priv-key-input', 'document.getElementById("billbrd_private_api_key").style.setProperty("display","inline-block");');
					
		} else {
			
			wp_register_script( 'disable-priv-key-input', '',);
			wp_enqueue_script( 'disable-priv-key-input' );
			wp_add_inline_script( 'disable-priv-key-input', 'document.getElementById("billbrd_private_api_key").style.setProperty("display","none");');
			
			printf(
				'<input type="text" id="billbrd_private_api_key_placeholder" value="%s" style="width:300px;" disabled=true />',
				esc_attr($this->settings['billbrd_private_api_key'])
			);
			
		}
		
	}
	
	// Callback for customer recruitment field
	public function billbrd_cr_status_callback()
	{

		// Enable field only if tracking is enabled
		if (isset($this->settings['billbrd_tracking_status']) and $this->settings['billbrd_tracking_status'] == 1)
		{
				
			?><select id="billbrd_cr_status" name="billbrd_settings[billbrd_cr_status]" style="max-width:fit-content;">
				<option value="0" <?php selected($this->settings['billbrd_cr_status'],0) ?>>No, turn off Billbrd.io customer recruitment</option>
				<option value="1" <?php selected($this->settings['billbrd_cr_status'],1) ?>>Yes, prompt customers to become affiliates after checkout</option>
			  </select>
			<?php
		}
		else
		{
			?><select disabled=true id="billbrd_cr_status" name="billbrd_settings[billbrd_cr_status]" style="max-width:fit-content;">
				<option value="0" selected=selected>No, turn off Billbrd.io customer recruitment</option>
				<option value="1">Yes, prompt customers to become affiliates after checkout</option>
			  </select>
			<?php
		}
		
		wp_register_script( 'toggle-id-input', '',);
		wp_enqueue_script( 'toggle-id-input' );
		wp_add_inline_script( 'toggle-id-input', 'document.getElementById("billbrd_cr_status").addEventListener("change", () => {
			var recruitment_program_id_input = document.getElementById("billbrd_cr_id_placeholder");
			if (document.getElementById("billbrd_cr_status").value !== "1" &&  document.getElementById("billbrd_cr_button").value !== "1") {
				recruitment_program_id_input.disabled = true;
			} else {
				recruitment_program_id_input.removeAttribute("disabled");
			}
		})');
	}
	
	// Callback for customer recruitment button field
	public function billbrd_cr_button_callback()
	{

		// Enable field only if tracking is enabled
		if (isset($this->settings['billbrd_tracking_status']) and $this->settings['billbrd_tracking_status'] == 1)
		{
				
			?><select id="billbrd_cr_button" name="billbrd_settings[billbrd_cr_button]" style="width: 300px;">
				<option value="0" <?php selected($this->settings['billbrd_cr_button'],0) ?>>Disable</option>
				<option value="1" <?php selected($this->settings['billbrd_cr_button'],1) ?>>Enable</option>
			  </select>
			<?php
		}
		else
		{
			?><select disabled=true id="billbrd_cr_button" name="billbrd_settings[billbrd_cr_button]" style="width: 300px;">
				<option value="0" selected=selected>Disable</option>
				<option value="1">Enable</option>
			  </select>
			<?php
		}

		wp_register_script( 'toggle-id-input', '',);
		wp_enqueue_script( 'toggle-id-input' );
		wp_add_inline_script( 'toggle-id-input', 'document.getElementById("billbrd_cr_button").addEventListener("change", () => {
			var recruitment_program_id_input = document.getElementById("billbrd_cr_id_placeholder");
			if (document.getElementById("billbrd_cr_status").value !== "1" &&  document.getElementById("billbrd_cr_button").value !== "1") {
				recruitment_program_id_input.disabled = true;
			} else {
				recruitment_program_id_input.removeAttribute("disabled");
			}
		})');
	}

	// Callback for customer recruitment button position field
	public function billbrd_cr_button_position_callback()
	{

		?><select id="billbrd_cr_button_position" name="billbrd_settings[billbrd_cr_button_position]" style="width: 300px;">
			<option value="0" <?php selected($this->settings['billbrd_cr_button_position'],0) ?>>Bottom-right of page</option>
			<option value="1" <?php selected($this->settings['billbrd_cr_button_position'],1) ?>>Bottom-left of page</option>
		  </select>
		<?php
		
	}
	
	// Callback for customer recruitment button color field
	public function billbrd_cr_button_color_callback()
	{

		// Pre-fill field with provided value if available
		if (isset($this->settings['billbrd_cr_button_color'])) {
			printf(
				'<input type="text" id="billbrd_cr_button_color" name="billbrd_settings[billbrd_cr_button_color]" value="%s" class="color_field" data-default-color="#9d00ff" />', esc_attr($this->settings['billbrd_cr_button_color'])
			);
		} else {
			printf(
				'<input type="text" id="billbrd_cr_button_color" name="billbrd_settings[billbrd_cr_button_color]" value="#9d00ff" class="color_field" data-default-color="#9d00ff" />'
			);
		}
		
		wp_register_script( 'display-color-picker', '', array("jquery"), '', true);
		wp_enqueue_script( 'display-color-picker' );
		wp_add_inline_script( 'display-color-picker', 'jQuery(document).ready(function($){$(".color_field").wpColorPicker();});');
	}

	// Callback for customer recruitment font color field
	public function billbrd_cr_font_color_callback()
	{

		// Pre-fill field with provided value if available
		if (isset($this->settings['billbrd_cr_font_color'])) {
			printf(
				'<input type="text" id="billbrd_cr_font_color" name="billbrd_settings[billbrd_cr_font_color]" value="%s" class="color_field" data-default-color="#ffffff" />', esc_attr($this->settings['billbrd_cr_font_color'])
			);
		} else {
			printf(
				'<input type="text" id="billbrd_cr_font_color" name="billbrd_settings[billbrd_cr_font_color]" value="#ffffff" class="color_field" data-default-color="#ffffff" />'
			);
		}
		
		wp_register_script( 'display-color-picker', '', array("jquery"), '', true);
		wp_enqueue_script( 'display-color-picker' );
		wp_add_inline_script( 'display-color-picker', 'jQuery(document).ready(function($){$(".color_field").wpColorPicker();});');
	}
	
	// Callback for customer recruitment program id field
	public function billbrd_cr_id_callback()
	{

		// Field containing actual program id value (always hidden)
		if (isset($this->settings['billbrd_cr_id'])) {
			printf(
				'<input type="text" id="billbrd_cr_id" name="billbrd_settings[billbrd_cr_id]" value="%s" style="width:300px; display:none" />', esc_attr($this->settings['billbrd_cr_id'])
			);
		} else {
			printf(
				'<input type="text" id="billbrd_cr_id" name="billbrd_settings[billbrd_cr_id]" value="" style="width:300px; display:none" />'
			);
		}
		
		// Placeholder field for entering temporary value used to check if program exists (enabled/disabled based on if customer recruitment modal/button is enabled/disabled)
		if ((isset($this->settings['billbrd_cr_status']) and $this->settings['billbrd_cr_status'] == 1) or (isset($this->settings['billbrd_cr_button']) and $this->settings['billbrd_cr_button'] == 1))
		{
		
			// Pre-fill field with provided value if available
			if (isset($this->settings['billbrd_cr_id'])) {
				printf(
					'<input type="text" id="billbrd_cr_id_placeholder" value="%s" style="width:300px;" />', esc_attr($this->settings['billbrd_cr_id'])
				);
			} else {
				printf(
					'<input type="text" id="billbrd_cr_id_placeholder" value="" style="width:300px;" />'
				);
			}
			
		} else {
			
			// Pre-fill field with provided value if available
			if (isset($this->settings['billbrd_cr_id'])) {
				printf(
					'<input type="text" id="billbrd_cr_id_placeholder" value="%s" style="width:300px;" disabled=true />', esc_attr($this->settings['billbrd_cr_id'])
				);
			} else {
				printf(
					'<input type="text" id="billbrd_cr_id_placeholder" value="" style="width:300px;" disabled=true />'
				);
			}
			
		}
		
		?>
			<!-- Add button for getting program info -->
			<div>
				<button id="get_program_data" class="button button-primary" style="margin-top:15px">Get program info</button>
			</div>
			<div id="program_info_message" style="font-size:13px; font-family:inherit; margin-top:10px">
			</div>
		<?php

		wp_register_script( 'get-info-btn-click', '',);
		wp_enqueue_script( 'get-info-btn-click' );
		wp_add_inline_script( 'get-info-btn-click', 'async function getInfo(e)
		{
			e.preventDefault(); // Prevent submit
			document.getElementById("billbrd_cr_id").value = document.getElementById("billbrd_cr_id_placeholder").value; // Set value of field input only when button is clicked
			
			var msg = document.getElementById("program_info_message");
			if (document.getElementById("billbrd_tracking_status").value !== "1") {
				msg.innerHTML = "Tracking must be enabled before enabling recruitment.";
			}
			else if (document.getElementById("billbrd_cr_status").value !== "1" && document.getElementById("billbrd_cr_button").value !== "1") {
				msg.innerHTML = "Please enable the customer recruitment option or the \"Become an affiliate\" button first.";
			}
			else if (document.getElementById("billbrd_cr_id").value == "") {
				msg.innerHTML = "Please enter the ID of Affiliate Program you wish to prompt your customers to join after checkout.";
			}
			else {
				msg.innerHTML = "Loading...";
				// Make the API call
				var body = await fetch("' . wp_kses_post(BILLBRD_API_URL . "affiliate_program_info") . '",{
					method: "POST",
					headers: {
						"Content-Type": "application/json",
						"Authorization": "Basic " + btoa(document.getElementById("billbrd_public_api_key").value) + ":" + btoa(document.getElementById("billbrd_private_api_key").value),
					},
					body: JSON.stringify({"id": document.getElementById("billbrd_cr_id").value,
										  "domain": "' . wp_kses_post($_SERVER["HTTP_HOST"]) . '"
										 }),
				}).then(response => response.json())
				.then(response => response.response);
				// Set field values based on returned data
				if (!body.status) {
					msg.innerHTML = "The entered Affiliate Program ID was not found. Please make sure you enter a valid program ID, and that your entered API credentials are correct.";
					document.querySelectorAll(".program_data").forEach(input => input.value = "");
				}
				else if (body.status == "Pending") {
					msg.innerHTML = "The selected affiliate program is still pending approval. Please enter a different program ID or wait until the selected program is approved.";
					document.querySelectorAll(".program_data").forEach(input => input.value = "");
				}
				else {
					msg.innerHTML = "<b>Program info retrieved. Make sure to save the changes page before you leave.</b>"
					document.querySelectorAll(".program_data").forEach(input => input.value = "");
					document.getElementById("billbrd_cr_tracking_type").value = body.type;
					document.getElementById("billbrd_cr_tracking_type_placeholder").value = body.type;
					document.getElementById("billbrd_cr_commission").value = body.commission;
					document.getElementById("billbrd_cr_commission_placeholder").value = body.commission;
					document.getElementById("billbrd_cr_clearing").value = body.clearing ? body.clearing + " days" : "Immediately";
					document.getElementById("billbrd_cr_clearing_placeholder").value = body.clearing ? body.clearing + " days" : "Immediately";;
					if (body.type !== "Link") {
						document.getElementById("billbrd_cr_code_type").value = body.code_discount_type.replace("_"," ");
						document.getElementById("billbrd_cr_code_type_placeholder").value = body.code_discount_type.replace("_"," ");
						document.getElementById("billbrd_cr_code_amount").value = body.code_discount_amount;
						document.getElementById("billbrd_cr_code_amount_placeholder").value = body.code_discount_amount;
						document.getElementById("billbrd_cr_code_free_shipping").value = body.code_free_shipping
						document.getElementById("billbrd_cr_code_free_shipping_placeholder").value = body.code_free_shipping;
						document.getElementById("billbrd_cr_code_min_order_amount").value = body.code_min_order_amount ? body.code_min_order_amount : "None";
						document.getElementById("billbrd_cr_code_min_order_amount_placeholder").value = body.code_min_order_amount ? body.code_min_order_amount : "None";
						document.getElementById("billbrd_cr_code_expiry").value = body.code_expiry ? body.code_expiry + " days after activation" : "None";
						document.getElementById("billbrd_cr_code_expiry_placeholder").value = body.code_expiry ? body.code_expiry + " days after activation" : "None";
						document.getElementById("billbrd_cr_code_usage_limit").value = body.code_usage_limit ? body.code_usage_limit : "None";
						document.getElementById("billbrd_cr_code_usage_limit_placeholder").value = body.code_usage_limit ? body.code_usage_limit : "None";
					}
					if (body.type !== "Promo Code") {
						document.getElementById("billbrd_cr_cookie_window").value = body.cookie_window ? body.cookie_window + " days after latest link click" : "None";
						document.getElementById("billbrd_cr_cookie_window_placeholder").value = body.cookie_window ? body.cookie_window + " days after latest link click" : "None";
					}
				}
			}
		}
		document.getElementById("get_program_data").addEventListener("click", getInfo);');
		
	}
	
	// Callback for customer recruitment program tracking type field
	public function billbrd_cr_tracking_type_callback()
	{

		// Field containing actual program tracking type value (always hidden)
		if (get_option('billbrd_settings')['billbrd_cr_tracking_type']) {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_tracking_type" name="billbrd_settings[billbrd_cr_tracking_type]" value="%s" style="width:300px; display:none" />', wp_kses_post(get_option('billbrd_settings')['billbrd_cr_tracking_type'])
			);
		} else {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_tracking_type" name="billbrd_settings[billbrd_cr_tracking_type]" value="" style="width:300px; display:none" />'
			);
		}

		// Placeholder field (always disabled)
		if (get_option('billbrd_settings')['billbrd_cr_tracking_type']) {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_tracking_type_placeholder" value="%s" style="width:300px;" disabled=true />', wp_kses_post(get_option('billbrd_settings')['billbrd_cr_tracking_type'])
			);
		} else {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_tracking_type_placeholder" value="" style="width:300px;" disabled=true />'
			);
		}

		wp_register_script( 'reset-inputs', '',);
		wp_enqueue_script( 'reset-inputs' );
		wp_add_inline_script( 'reset-inputs', 'if (document.getElementById("billbrd_cr_tracking_type").value == "") {
			document.getElementById("billbrd_cr_id").value = "";
			document.getElementById("billbrd_cr_id_placeholder").value = "";
		}');
	}
	
	// Callback for customer recruitment program commission field
	public function billbrd_cr_commission_callback()
	{

		// Field containing actual program commission value (always hidden)
		if (get_option('billbrd_settings')['billbrd_cr_commission']) {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_commission" name="billbrd_settings[billbrd_cr_commission]" value="%s" style="width:300px; display:none" />', wp_kses_post(get_option('billbrd_settings')['billbrd_cr_commission'])
			);
		} else {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_commission" name="billbrd_settings[billbrd_cr_commission]" value="" style="width:300px; display:none" />'
			);
		}

		// Placeholder field (always disabled)
		if (get_option('billbrd_settings')['billbrd_cr_commission']) {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_commission_placeholder" value="%s" style="width:300px;" disabled=true />', wp_kses_post(get_option('billbrd_settings')['billbrd_cr_commission'])
			);
		} else {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_commission_placeholder" value="" style="width:300px;" disabled=true />'
			);
		}

	}

	// Callback for customer recruitment program clearing period field
	public function billbrd_cr_clearing_callback()
	{

		// Field containing actual program clearing period value (always hidden)
		if (get_option('billbrd_settings')['billbrd_cr_clearing']) {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_clearing" name="billbrd_settings[billbrd_cr_clearing]" value="%s" style="width:300px; display:none" />', wp_kses_post(get_option('billbrd_settings')['billbrd_cr_clearing'])
			);
		} else {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_clearing" name="billbrd_settings[billbrd_cr_clearing]" value="" style="width:300px; display:none" />'
			);
		}

		// Placeholder field (always disabled)
		if (get_option('billbrd_settings')['billbrd_cr_clearing']) {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_clearing_placeholder" value="%s" style="width:300px;" disabled=true />', wp_kses_post(get_option('billbrd_settings')['billbrd_cr_clearing'])
			);
		} else {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_clearing_placeholder" value="" style="width:300px;" disabled=true />'
			);
		}

	}
	
	// Callback for customer recruitment program link cookie window field
	public function billbrd_cr_cookie_window_callback()
	{

		// Field containing actual program link cookie window value (always hidden)
		if (get_option('billbrd_settings')['billbrd_cr_cookie_window']) {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_cookie_window" name="billbrd_settings[billbrd_cr_cookie_window]" value="%s" style="width:300px; display:none" />', wp_kses_post(get_option('billbrd_settings')['billbrd_cr_cookie_window'])
			);
		} else {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_cookie_window" name="billbrd_settings[billbrd_cr_cookie_window]" value="" style="width:300px; display:none" />'
			);
		}

		// Placeholder field (always disabled)
		if (get_option('billbrd_settings')['billbrd_cr_cookie_window']) {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_cookie_window_placeholder" value="%s" style="width:300px;" disabled=true />', wp_kses_post(get_option('billbrd_settings')['billbrd_cr_cookie_window'])
			);
		} else {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_cookie_window_placeholder" value="" style="width:300px;" disabled=true />'
			);
		}

	}
	
	// Callback for customer recruitment program code discount type field
	public function billbrd_cr_code_type_callback()
	{

		// Field containing actual program code discount type value (always hidden)
		if (get_option('billbrd_settings')['billbrd_cr_code_type']) {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_type" name="billbrd_settings[billbrd_cr_code_type]" value="%s" style="width:300px; display:none" />', wp_kses_post(get_option('billbrd_settings')['billbrd_cr_code_type'])
			);
		} else {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_type" name="billbrd_settings[billbrd_cr_code_type]" value="" style="width:300px; display:none" />'
			);
		}

		// Placeholder field (always disabled)
		if (get_option('billbrd_settings')['billbrd_cr_code_type']) {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_type_placeholder" value="%s" style="width:300px; text-transform:capitalize" disabled=true />', wp_kses_post(get_option('billbrd_settings')['billbrd_cr_code_type'])
			);
		} else {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_type_placeholder" value="" style="width:300px; text-transform:capitalize" disabled=true />'
			);
		}

	}

	// Callback for customer recruitment program code discount amount field
	public function billbrd_cr_code_amount_callback()
	{

		// Field containing actual program code discount amount value (always hidden)
		if (get_option('billbrd_settings')['billbrd_cr_code_amount']) {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_amount" name="billbrd_settings[billbrd_cr_code_amount]" value="%s" style="width:300px; display:none" />', wp_kses_post(get_option('billbrd_settings')['billbrd_cr_code_amount'])
			);
		} else {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_amount" name="billbrd_settings[billbrd_cr_code_amount]" value="" style="width:300px; display:none" />'
			);
		}

		// Placeholder field (always disabled)
		if (get_option('billbrd_settings')['billbrd_cr_code_amount']) {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_amount_placeholder" value="%s" style="width:300px;" disabled=true />', wp_kses_post(get_option('billbrd_settings')['billbrd_cr_code_amount'])
			);
		} else {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_amount_placeholder" value="" style="width:300px; text-transform:capitalize" disabled=true />'
			);
		}

	}

	// Callback for customer recruitment program code free shipping field
	public function billbrd_cr_code_free_shipping_callback()
	{

		// Field containing actual program code free shipping value (always hidden)
		if (get_option('billbrd_settings')['billbrd_cr_code_free_shipping']) {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_free_shipping" name="billbrd_settings[billbrd_cr_code_free_shipping]" value="%s" style="width:300px; display:none" />', wp_kses_post(get_option('billbrd_settings')['billbrd_cr_code_free_shipping'])
			);
		} else {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_free_shipping" name="billbrd_settings[billbrd_cr_code_free_shipping]" value="" style="width:300px; display:none" />'
			);
		}

		// Placeholder field (always disabled)
		if (get_option('billbrd_settings')['billbrd_cr_code_free_shipping']) {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_free_shipping_placeholder" value="%s" style="width:300px; text-transform:capitalize" disabled=true />', wp_kses_post(get_option('billbrd_settings')['billbrd_cr_code_free_shipping'])
			);
		} else {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_free_shipping_placeholder" value="" style="width:300px;" disabled=true />'
			);
		}

	}

	// Callback for customer recruitment program code expiry field
	public function billbrd_cr_code_expiry_callback()
	{

		// Field containing actual program code expiry value (always hidden)
		if (get_option('billbrd_settings')['billbrd_cr_code_expiry']) {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_expiry" name="billbrd_settings[billbrd_cr_code_expiry]" value="%s" style="width:300px; display:none" />', wp_kses_post(get_option('billbrd_settings')['billbrd_cr_code_expiry'])
			);
		} else {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_expiry" name="billbrd_settings[billbrd_cr_code_expiry]" value="" style="width:300px; display:none" />'
			);
		}

		// Placeholder field (always disabled)
		if (get_option('billbrd_settings')['billbrd_cr_code_expiry']) {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_expiry_placeholder" value="%s" style="width:300px;" disabled=true />', wp_kses_post(get_option('billbrd_settings')['billbrd_cr_code_expiry'])
			);
		} else {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_expiry_placeholder" value="" style="width:300px; text-transform:capitalize" disabled=true />'
			);
		}

	}

	// Callback for customer recruitment program code minimum order amount field
	public function billbrd_cr_code_min_order_amount_callback()
	{

		// Field containing actual program code minimum order amount value (always hidden)
		if (get_option('billbrd_settings')['billbrd_cr_code_min_order_amount']) {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_min_order_amount" name="billbrd_settings[billbrd_cr_code_min_order_amount]" value="%s" style="width:300px; display:none" />', wp_kses_post(get_option('billbrd_settings')['billbrd_cr_code_min_order_amount'])
			);
		} else {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_min_order_amount" name="billbrd_settings[billbrd_cr_code_min_order_amount]" value="" style="width:300px; display:none" />'
			);
		}

		// Placeholder field (always disabled)
		if (get_option('billbrd_settings')['billbrd_cr_code_min_order_amount']) {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_min_order_amount_placeholder" value="%s" style="width:300px;" disabled=true />', wp_kses_post(get_option('billbrd_settings')['billbrd_cr_code_min_order_amount'])
			);
		} else {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_min_order_amount_placeholder" value="" style="width:300px; text-transform:capitalize" disabled=true />'
			);
		}

	}

	// Callback for customer recruitment program code usage limit field
	public function billbrd_cr_code_usage_limit_callback()
	{

		// Field containing actual program code usage limit value (always hidden)
		if (get_option('billbrd_settings')['billbrd_cr_code_usage_limit']) {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_usage_limit" name="billbrd_settings[billbrd_cr_code_usage_limit]" value="%s" style="width:300px; display:none" />', wp_kses_post(get_option('billbrd_settings')['billbrd_cr_code_usage_limit'])
			);
		} else {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_usage_limit" name="billbrd_settings[billbrd_cr_code_usage_limit]" value="" style="width:300px; display:none" />'
			);
		}

		// Placeholder field (always disabled)
		if (get_option('billbrd_settings')['billbrd_cr_code_usage_limit']) {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_usage_limit_placeholder" value="%s" style="width:300px;" disabled=true />', wp_kses_post(get_option('billbrd_settings')['billbrd_cr_code_usage_limit'])
			);
		} else {
			printf(
				'<input type="text" class="program_data" id="billbrd_cr_code_usage_limit_placeholder" value="" style="width:300px; text-transform:capitalize" disabled=true />'
			);
		}

		
		wp_register_script( 'reset-inputs', '',);
		wp_enqueue_script( 'reset-inputs' );
		wp_add_inline_script( 'reset-inputs', 'if (document.getElementById("billbrd_cr_id").value == "") {
			document.querySelectorAll(".program_data").forEach(input => input.value = "");
			document.getElementById("billbrd_cr_status").value = "0";
			document.getElementById("billbrd_cr_button").value = "0";
			document.getElementById("billbrd_cr_id_placeholder").disabled = true;
		}');
	}

	// Sanitize input values
	public function sanitize($input)
	{

		$sanitized_values = array();

		if (isset($input['billbrd_public_api_key'])) {
			$sanitized_values['billbrd_public_api_key'] = sanitize_text_field($input['billbrd_public_api_key']);
		}

		if (isset($input['billbrd_private_api_key'])) {
			$sanitized_values['billbrd_private_api_key'] = sanitize_text_field($input['billbrd_private_api_key']);
		}

		if (isset($input['billbrd_tracking_status']) && in_array((int)$input['billbrd_tracking_status'], array(0, 1), true)) {
			$sanitized_values['billbrd_tracking_status'] = $input['billbrd_tracking_status'];
		}
		
		if (isset($input['billbrd_cr_status']) && in_array((int)$input['billbrd_cr_status'], array(0, 1), true)) {
			$sanitized_values['billbrd_cr_status'] = $input['billbrd_cr_status'];
		}
		
		if (isset($input['billbrd_cr_button']) && in_array((int)$input['billbrd_cr_button'], array(0, 1), true)) {
			$sanitized_values['billbrd_cr_button'] = $input['billbrd_cr_button'];
		}
		
		if (isset($input['billbrd_cr_button_position']) && in_array((int)$input['billbrd_cr_button_position'], array(0, 1), true)) {
			$sanitized_values['billbrd_cr_button_position'] = $input['billbrd_cr_button_position'];
		}

		if (isset($input['billbrd_cr_button_color'])) {
			$sanitized_values['billbrd_cr_button_color'] = sanitize_hex_color($input['billbrd_cr_button_color']);
		}

		if (isset($input['billbrd_cr_font_color'])) {
			$sanitized_values['billbrd_cr_font_color'] = sanitize_hex_color($input['billbrd_cr_font_color']);
		}
		
		if (isset($input['billbrd_cr_id'])) {
			$sanitized_values['billbrd_cr_id'] = sanitize_text_field($input['billbrd_cr_id']);
		}
		
		if (isset($input['billbrd_cr_tracking_type'])) {
			$sanitized_values['billbrd_cr_tracking_type'] = sanitize_text_field($input['billbrd_cr_tracking_type']);
		}
		
		if (isset($input['billbrd_cr_commission'])) {
			$sanitized_values['billbrd_cr_commission'] = sanitize_text_field($input['billbrd_cr_commission']);
		}

		if (isset($input['billbrd_cr_clearing'])) {
			$sanitized_values['billbrd_cr_clearing'] = sanitize_text_field($input['billbrd_cr_clearing']);
		}
		
		if (isset($input['billbrd_cr_cookie_window'])) {
			$sanitized_values['billbrd_cr_cookie_window'] = sanitize_text_field($input['billbrd_cr_cookie_window']);
		}
		
		if (isset($input['billbrd_cr_code_type'])) {
			$sanitized_values['billbrd_cr_code_type'] = sanitize_text_field($input['billbrd_cr_code_type']);
		}
		
		if (isset($input['billbrd_cr_code_amount'])) {
			$sanitized_values['billbrd_cr_code_amount'] = sanitize_text_field($input['billbrd_cr_code_amount']);
		}
		
		if (isset($input['billbrd_cr_code_free_shipping'])) {
			$sanitized_values['billbrd_cr_code_free_shipping'] = sanitize_text_field($input['billbrd_cr_code_free_shipping']);
		}
		
		if (isset($input['billbrd_cr_code_min_order_amount'])) {
			$sanitized_values['billbrd_cr_code_min_order_amount'] = sanitize_text_field($input['billbrd_cr_code_min_order_amount']);
		}
		
		if (isset($input['billbrd_cr_code_expiry'])) {
			$sanitized_values['billbrd_cr_code_expiry'] = sanitize_text_field($input['billbrd_cr_code_expiry']);
		}
		
		if (isset($input['billbrd_cr_code_usage_limit'])) {
			$sanitized_values['billbrd_cr_code_usage_limit'] = sanitize_text_field($input['billbrd_cr_code_usage_limit']);
		}

		return $sanitized_values;

	}

	// Display message for admin to finish setup after installation
	public function show_setup_message()
	{
		
		if (is_array(get_option('billbrd_settings'))) {
			return;
		}
		
		$content = sprintf("<strong>You're almost done connecting your store to Billbrd.io! Finish your store set up by </strong><a href='admin.php?page=billbrd-admin-settings'>entering your API keys</a>");
		echo sprintf('<div id="billbrd-message-warning" class="updated fade"><p>%1$s</p></div>', wp_kses_post($content));
		
	}

	// Add plugin 'Settings' link in Plugins screen
	public function set_plugin_settings_page_link($links)
	{

		$settings_link = '<a href="' . get_admin_url() . "admin.php?page=billbrd-admin-settings" . '">Settings</a>';
		array_unshift($links, $settings_link);

		return $links;
	}

	// Set plugin settings page styling
	public function set_plugin_settings_page_css($suffix)
	{
		if ($suffix == 'woocommerce_page_billbrd-admin-settings') {
			wp_enqueue_style('billbrd_settings_css', plugin_dir_url(__FILE__) . 'billbrd-settings-page-style.css');
		}
	}

}