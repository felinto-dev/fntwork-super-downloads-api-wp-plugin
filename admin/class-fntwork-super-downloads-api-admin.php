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

	public function user_role_based_provider_access_metabox()
	{
		$prefix = $this->plugin_name;
		$option_key = $this->api_manager->get_user_role_based_provider_access_option_key();

		$cmb = new_cmb2_box([
			'id'           => $prefix . '_user_role_based_provider_access',
			'title'        => 'User Role Based Provider Access',
			'object_types' => ['options-page'],
			'option_key'   => $option_key,
			'parent_slug'  => 'super-downloads-api',
		]);

		$providers = [];

		if (is_admin()) {
			$providers = $this->api_manager->get_providers();
		}

		foreach ($providers as $provider) {
			$provider_nickname = $provider['attributes']['nickname'];
			$provider_description = 'Manage Access for Provider: ' . $provider_nickname;

			$group_field_id = $cmb->add_field(array(
				'id'          => $prefix . $provider['id'] . '_' . $provider_nickname,
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
					if ($value < 1) {
						$value = 1;
					} elseif ($value > 100) {
						$value = 100;
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

	public function display_plugin_setup_page()
	{
		include_once('partials/fntwork-super-downloads-api-admin-display.php');
	}


	public function add_plugin_admin_menu()
	{
		add_menu_page(
			'Super Downloads API',
			'Super Downloads API',
			'manage_options',
			'super-downloads-api',
			[$this, 'display_plugin_setup_page'],
			'dashicons-download',
		);
	}

	public function register_plugin_settings()
	{
		register_setting('super_downloads_api_options', 'super_downloads_api_options', array($this, 'validate_input'));

		add_settings_section('super_downloads_api_section', 'Configurações da API', array($this, 'display_section'), 'super_downloads_api_options');

		add_settings_field('api_key', 'API Key', array($this, 'render_api_key_field'), 'super_downloads_api_options', 'super_downloads_api_section');
	}

	public function display_section()
	{
		echo '<p>Configure os dados de acesso para obter acesso a Super Downloads API.</p>';
	}

	public function render_api_key_field()
	{
		$options = get_option('super_downloads_api_options');
		echo '<input type="text" id="api_key" name="super_downloads_api_options[api_key]" value="' . $options['api_key'] . '" size="35" autofocus>';
	}

	public function validate_input($input)
	{
		$new_input = [];

		if (isset($input['api_key'])) {
			$new_input['api_key'] = sanitize_text_field($input['api_key']);
		}

		return $new_input;
	}
}
