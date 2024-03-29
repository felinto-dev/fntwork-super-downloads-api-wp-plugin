<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://t.me/felinto
 * @since             1.0.0
 * @package           Fntwork_Super_Downloads_Api
 *
 * @wordpress-plugin
 * Plugin Name:       Super Downloads API
 * Plugin URI:        https://t.me/felinto
 * Description:       Plugin para integração do WordPress com a plataforma Super Downloads API.
 * Version:           3.1.1
 * Author:            Felinto
 * Author URI:        https://t.me/felinto
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       fntwork-super-downloads-api
 * Domain Path:       /languages
 * GitHub Plugin URI: felinto-dev/fntwork-super-downloads-api-wp-plugin
 * Primary Branch: main
 * Requires PHP: 8.1
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
	require_once __DIR__ . '/vendor/autoload.php';
}

define('FNTWORK_SUPER_DOWNLOAD_API_PLUGIN_NAME', 'fntwork-super-downloads-api');

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('FNTWORK_SUPER_DOWNLOADS_API_VERSION', '3.1.1');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-fntwork-super-downloads-api-activator.php
 */
function activate_fntwork_super_downloads_api()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-fntwork-super-downloads-api-activator.php';
	Fntwork_Super_Downloads_Api_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-fntwork-super-downloads-api-deactivator.php
 */
function deactivate_fntwork_super_downloads_api()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-fntwork-super-downloads-api-deactivator.php';
	Fntwork_Super_Downloads_Api_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_fntwork_super_downloads_api');
register_deactivation_hook(__FILE__, 'deactivate_fntwork_super_downloads_api');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-fntwork-super-downloads-api.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_fntwork_super_downloads_api()
{

	$plugin = new Fntwork_Super_Downloads_Api();
	$plugin->run();
}

function fntwork_super_downloads_api_get_providers_from_api()
{
	$settings_manager = new Fntwork_Super_Downloads_Api_Settings_Manager(
		FNTWORK_SUPER_DOWNLOAD_API_PLUGIN_NAME,
		FNTWORK_SUPER_DOWNLOADS_API_VERSION
	);

	$api_manager = new Fntwork_Super_Downloads_API_Manager(
		FNTWORK_SUPER_DOWNLOAD_API_PLUGIN_NAME,
		FNTWORK_SUPER_DOWNLOADS_API_VERSION,
		$settings_manager,
	);

	return $api_manager->get_providers() ?? [];
}

function fntwork_super_downloads_api_get_providers_from_settings()
{
	$settings_manager = new Fntwork_Super_Downloads_Api_Settings_Manager(
		FNTWORK_SUPER_DOWNLOAD_API_PLUGIN_NAME,
		FNTWORK_SUPER_DOWNLOADS_API_VERSION
	);

	return $settings_manager->get_providers_settings() ?? [];
}

function fntwork_get_ip_address()
{
	foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
		if (array_key_exists($key, $_SERVER) === true) {
			foreach (explode(',', $_SERVER[$key]) as $ip) {
				$ip = trim($ip);

				if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
					return $ip;
				}
			}
		}
	}
}

run_fntwork_super_downloads_api();
