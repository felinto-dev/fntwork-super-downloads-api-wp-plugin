<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://t.me/felinto
 * @since      1.0.0
 *
 * @package    Fntwork_Super_Downloads_Api
 * @subpackage Fntwork_Super_Downloads_Api/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Fntwork_Super_Downloads_Api
 * @subpackage Fntwork_Super_Downloads_Api/includes
 * @author     Felinto <emersong20.email@gmail.com>
 */
class Fntwork_Super_Downloads_Api
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Fntwork_Super_Downloads_Api_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('FNTWORK_SUPER_DOWNLOADS_API_VERSION')) {
			$this->version = FNTWORK_SUPER_DOWNLOADS_API_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = FNTWORK_SUPER_DOWNLOAD_API_PLUGIN_NAME;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Fntwork_Super_Downloads_Api_Loader. Orchestrates the hooks of the plugin.
	 * - Fntwork_Super_Downloads_Api_i18n. Defines internationalization functionality.
	 * - Fntwork_Super_Downloads_Api_Admin. Defines all hooks for the admin area.
	 * - Fntwork_Super_Downloads_Api_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-fntwork-super-downloads-api-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-fntwork-super-downloads-api-i18n.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-fntwork-super-downloads-api-api-manager.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-fntwork-super-downloads-api-settings-manager.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-fntwork-super-downloads-api-rate-limiter.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-fntwork-super-downloads-api-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-fntwork-super-downloads-api-public.php';

		$this->loader = new Fntwork_Super_Downloads_Api_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Fntwork_Super_Downloads_Api_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Fntwork_Super_Downloads_Api_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{
		$settings_manager = new Fntwork_Super_Downloads_Api_Settings_Manager($this->get_plugin_name(), $this->get_version());
		$api_manager = new Fntwork_Super_Downloads_API_Manager(
			$this->get_plugin_name(),
			$this->get_version(),
			$settings_manager,
		);
		$plugin_admin = new Fntwork_Super_Downloads_Api_Admin(
			$this->get_plugin_name(),
			$this->get_version(),
			$api_manager,
			$settings_manager,
		);
		$rate_limiter = new Fntwork_Super_Downloads_Api_Rate_Limiter(
			$this->get_plugin_name(),
			$this->get_version(),
			$settings_manager,
		);

		$this->loader->add_filter('manage_users_columns', $rate_limiter, 'add_user_credits_left_table_list_column');
		$this->loader->add_filter('manage_users_custom_column', $rate_limiter, 'populate_user_credits_left_table_list_column', 10, 3);
		$this->loader->add_filter('manage_users_sortable_columns', $rate_limiter, 'sortable_user_credits_left_table_list_column');

		// $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		// $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('cmb2_admin_init', $plugin_admin, 'super_downloads_api_options_metabox');
		$this->loader->add_filter('cmb2_can_save', $plugin_admin, 'before_save_metabox', 10, 2);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{
		$settings_manager = new Fntwork_Super_Downloads_Api_Settings_Manager($this->get_plugin_name(), $this->get_version());
		$api_manager = new Fntwork_Super_Downloads_API_Manager($this->get_plugin_name(), $this->get_version(), $settings_manager);
		$rate_limiter = new Fntwork_Super_Downloads_Api_Rate_Limiter($this->get_plugin_name(), $this->get_version(), $settings_manager);
		$plugin_public = new Fntwork_Super_Downloads_Api_Public($this->get_plugin_name(), $this->get_version(), $api_manager, $rate_limiter);

		add_shortcode('super-downloads-api', [$plugin_public, 'super_downloads_api_shortcode']);
		add_shortcode('super-downloads-api-user-credits-left', [$plugin_public, 'super_downloads_api_user_credits_left_shortcode']);

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
		$this->loader->add_action('wp_ajax_process_download_form', $plugin_public, 'process_download_form');
		$this->loader->add_action('super_downloads_api_after_api_request', $plugin_public, 'on_new_download', 10, 3);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Fntwork_Super_Downloads_Api_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}
