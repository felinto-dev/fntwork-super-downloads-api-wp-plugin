<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://t.me/felinto
 * @since      1.0.0
 *
 * @package    Fntwork_Super_Downloads_Api
 * @subpackage Fntwork_Super_Downloads_Api/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Fntwork_Super_Downloads_Api
 * @subpackage Fntwork_Super_Downloads_Api/public
 * @author     Felinto <emersong20.email@gmail.com>
 */
class Fntwork_Super_Downloads_Api_Public
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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version, $api_manager)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->api_manager = $api_manager;
	}

	public function super_downloads_api_shortcode()
	{
		wp_enqueue_script($this->plugin_name);
		wp_enqueue_script($this->plugin_name . '-confetti-lib');

		do_action('before_super_downloads_api_shortcode');
		ob_start();
		include(plugin_dir_path(__FILE__) . 'partials/fntwork-super-downloads-api-public-display.php');
		do_action('after_super_downloads_api_shortcode');
		return ob_get_clean();
	}

	public function process_download_form()
	{
		check_ajax_referer('download_form_nonce');
		$download_url_input = esc_url($_POST['url-input']);
		$browser_fingerprint = $_POST['user-tracking-browser-fingerprint'];
		$download_option_id = $_POST['download-option-id'] ?? null;

		$response_data = $this->api_manager->generate_provider_download_url(
			$download_url_input,
			$browser_fingerprint,
			$download_option_id
		);

		if (!$response_data || !isset($response_data['code'])) {
			return wp_send_json_error([
				'message' =>  isset($response_data['message'])
					? $response_data['message']
					: 'Um erro inesperado ocorreu. Por favor, entre em contato com o suporte.',
				'debug' => [
					'api_response' => $response_data,
				],
			]);
		}

		return wp_send_json_success($response_data);
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/fntwork-super-downloads-api-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_register_script(
			$this->plugin_name . '-confetti-lib',
			'https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js',
			[],
			null,
			[
				'strategy' => 'async',
				'in_footer' => true,
			],
		);

		wp_register_script(
			$this->plugin_name,
			plugin_dir_url(__FILE__) . 'js/fntwork-super-downloads-api-public.js',
			[$this->plugin_name . '-confetti-lib'],
			$this->version,
			[
				'strategy' => 'async',
				'in_footer' => true,
			],
		);

		wp_localize_script($this->plugin_name, 'fntwork_ajax_object', array(
			'ajaxurl' => admin_url('admin-ajax.php'),
		));
	}
}
