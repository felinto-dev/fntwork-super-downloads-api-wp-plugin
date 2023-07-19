<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://t.me/felinto
 * @since      1.0.0
 *
 * @package    Fntwork_Super_Downloads_Api
 * @subpackage Fntwork_Super_Downloads_Api/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fntwork_Super_Downloads_Api
 * @subpackage Fntwork_Super_Downloads_Api/admin
 * @author     Felinto <emersong20.email@gmail.com>
 */
class Fntwork_Super_Downloads_Api_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $api_manager;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version, $api_manager)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->api_manager = $api_manager;
	}

	public function super_downloads_api_options_metabox()
	{
		$prefix = $this->plugin_name;
		$option_key = $this->api_manager->get_option_key();

		do_action('qm/debug', get_option($option_key));

		$cmb = new_cmb2_box([
			'id'           => $prefix . '_options_page',
			'title'        => 'Super Downloads API',
			'object_types' => ['options-page'],
			'option_key'   => $option_key,
			'icon_url'     => 'dashicons-download',
		]);

		$cmb->add_field([
			'id' => 'api_key',
			'name' => 'API Key',
			'type' => 'text',
			'sanitization_cb' => function ($value) {
				return $value;
			}
		]);

		$cmb->add_field([
			'name' => 'Download Interval',
			'desc' => 'Number of seconds user must wait between downloads',
			'id'   => 'download_interval',
			'type' => 'text_small',
			'default' => 30,
			'attributes' => array(
				'type' => 'number',
			),
			'sanitization_cb' => 'absint',
		]);

		$cmb->add_field([
			'name' => 'Download Interval Error Message',
			'desc' => 'Message shown when interval between downloads not met',
			'id'   => 'download_interval_text',
			'type' => 'textarea_small',
			'default' => 'You must wait before downloading again',
		]);

		$cmb->add_field([
			'name' => 'Same File Download Interval',
			'desc' => 'Number of seconds user must wait before re-downloading the same file',
			'id'   => 'same_file_interval',
			'type' => 'text_small',
			'default' => 60,
			'attributes' => [
				'type' => 'number',
			],
			'sanitization_cb' => 'absint',
		]);

		$cmb->add_field([
			'name' => 'Same File Interval Error Message',
			'desc' => 'Message shown when same file download interval not met',
			'id'   => 'same_file_interval_text',
			'type' => 'textarea_small',
			'default' => 'You must wait before downloading this file again',
		]);

		$cmb->add_field([
			'name' => 'Unsupported Service Error Message',
			'desc' => 'Message shown when a download URL from an unsupported service is provided',
			'id'   => 'unsupported_service_text',
			'type' => 'textarea_small',
			'default' => 'Downloads from this service are not supported',
		]);

		$cmb->add_field([
			'name' => 'Permission Denied Error Message',
			'desc' => 'Message shown when the user\'s plan does not allow downloading this file',
			'id'   => 'permission_denied_text',
			'type' => 'textarea_small',
			'default' => 'Your user role does not have permission to download this file',
		]);

		$cmb->add_field([
			'name' => 'Ad Blocking Software Message',
			'desc' => 'Message shown when ad blocking software is detected. Explain that this can interfere with browser data collection.',
			'id'   => 'ad_blocking_detected_text',
			'type' => 'textarea_small',
			'default' => 'Please, try to disable the ad blocking software or use another browser',
		]);

		$rate_limiter_group = $cmb->add_field(array(
			'id' => 'rate_limiter_group',
			'type' => 'group',
			'description' => 'Rate Limiter Settings',
			'repeatable'  => false,
		));

		$cmb->add_group_field($rate_limiter_group, array(
			'name' => 'Daily Download Limit',
			'desc' => 'Maximum number of downloads per user per day',
			'id' => 'daily_limit',
			'type' => 'text_small',
			'default' => 20,
			'attributes' => [
				'type' => 'number',
			],
			'sanitization_cb' => function ($value) {
				$value = intval($value);
				if ($value < 0) {
					$value = 0;
				}

				return $value;
			},
		));

		$cmb->add_group_field($rate_limiter_group, array(
			'name' => 'Daily Limit Error Message',
			'desc' => 'Message shown when daily limit is reached',
			'id' => 'daily_limit_text',
			'type' => 'textarea_small',
			'default' => 'You have reached your daily download limit',
		));

		$providers = [];

		if (is_admin()) {
			$providers = $this->api_manager->get_providers();
		}

		foreach ($providers as $provider) {
			$provider_nickname = $provider['attributes']['nickname'];
			$provider_description = 'Manage Access for Provider: ' . $provider_nickname;

			$group_field_id = $cmb->add_field(array(
				'id'          => "{$provider_nickname}_provider_options",
				'type'        => 'group',
				'description' => $provider_description,
				'repeatable'  => false,
				'options'     => array(
					'group_title'   => $provider_description,
					'sortable'      => false,
				),
			));

			global $wp_roles;
			$roles = $wp_roles->roles;

			$role_options = array();
			foreach ($roles as $role_slug => $role_info) {
				$role_options[$role_slug] = $role_info['name'];
			}

			$cmb->add_group_field($group_field_id, array(
				'name' => 'Credits Spent per Download',
				'id' => 'credits_spent_per_download',
				'type' => 'text',
				'default' => 1,
				'desc'    => 'This value represents the number of credits that are deducted from a user\'s account each time they download content from a specific provider.',
				'sanitization_cb' => function ($value) {
					$value = intval($value);
					if ($value < 0) {
						$value = 0;
					}

					return $value;
				},
			));

			$cmb->add_group_field($group_field_id, array(
				'name'    => 'Role Name',
				'id'      => 'role_name',
				'options' => $role_options,
				'type'    => 'multicheck_inline',
				'select_all_button' => false,
				'default' => ['administrator']
			));

			// add provider nickname to the options
			$cmb->add_group_field($group_field_id, array(
				'name'    => 'Provider Nickname',
				'id'      => 'provider_nickname',
				'type'    => 'hidden',
				'default' => $provider_nickname
			));
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fntwork_Super_Downloads_Api_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fntwork_Super_Downloads_Api_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/fntwork-super-downloads-api-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fntwork_Super_Downloads_Api_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fntwork_Super_Downloads_Api_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/fntwork-super-downloads-api-admin.js', array('jquery'), $this->version, false);
	}
}
