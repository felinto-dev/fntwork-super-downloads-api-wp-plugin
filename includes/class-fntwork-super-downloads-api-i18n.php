<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://t.me/felinto
 * @since      1.0.0
 *
 * @package    Fntwork_Super_Downloads_Api
 * @subpackage Fntwork_Super_Downloads_Api/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Fntwork_Super_Downloads_Api
 * @subpackage Fntwork_Super_Downloads_Api/includes
 * @author     Felinto <emersong20.email@gmail.com>
 */
class Fntwork_Super_Downloads_Api_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'fntwork-super-downloads-api',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
