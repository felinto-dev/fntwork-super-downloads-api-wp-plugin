<?php

class Fntwork_Super_Downloads_API_Manager
{

	private $strapi_api_url;
	private $n8n_api_url;
	private $plugin_name;
	private $version;

	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->strapi_api_url = 'https://strapi.fnt.work/api';
		$this->n8n_api_url = 'https://n8n.fnt.work/webhook/super-downloads-api';
	}

	public function get_option_key()
	{
		return $this->plugin_name . '-settings';
	}

	public function get_option_data()
	{
		$option_key = $this->get_option_key();
		return get_option($option_key);
	}

	public function get_api_key()
	{
		return $this->get_option_data()['api_key'];
	}

	public function get_user_role_by_provider_access_permissions()
	{
		$option_data = $this->get_option_data();

		$filtered_option_data = [];

		foreach ($option_data as $key => $value) {
			if (is_array($value) && isset($value[0]['role_name']) && isset($value[0]['provider_nickname'])) {
				$filtered_option_data[$key] = $value;
			}
		}

		return $filtered_option_data;
	}

	public function get_providers()
	{
		$transient_key = 'cached_providers';

		$cached_data = get_transient($transient_key);

		$should_refresh_cache = isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'no-cache';

		if ($cached_data !== false && !$should_refresh_cache) {
			return $cached_data;
		} else {
			$url =  $this->strapi_api_url . '/third-party-providers?fields[0]=nickname';
			$response = wp_remote_get($url);

			if (is_wp_error($response)) {
				return [];
			}

			$body = wp_remote_retrieve_body($response);
			$data = json_decode($body, true);

			if (isset($data['data'])) {
				set_transient($transient_key, $data['data'], 1 * WEEK_IN_SECONDS);
				return $data['data'];
			} else {
				return [];
			}
		}
	}

	public function generate_provider_download_url($product_page_url, $browser_fingerprint)
	{
		$option_data = $this->get_option_data();

		if (!preg_match('/^[0-9a-f]{32}$/i', $browser_fingerprint)) {
			return [
				'message' => $option_data['ad_blocking_detected_text'],
			];
		}

		$transient_name = 'user_' . get_current_user_id() . '_url_' . md5($product_page_url);
		$transient_value = get_transient($transient_name);

		if ($transient_value) {
			return [
				'message' => $option_data['same_file_interval_text'],
			];
		} else {
			set_transient($transient_name, true, 120);
		}

		$providers = $this->get_providers();
		$provider_nickname = null;

		foreach ($providers as $provider) {
			foreach ($provider['attributes']['patterns'] as $pattern) {
				$regex = $pattern['regex'];
				if (preg_match('/' . $regex . '/', $product_page_url)) {
					$provider_nickname = $provider['attributes']['nickname'];
					break;
				}
			}

			if ($provider_nickname != null) {
				break;
			}
		}

		if ($provider_nickname == null) {
			return [
				'message' => $option_data['unsupported_service_text'],
			];
		}

		$user_permissions = $this->get_user_role_by_provider_access_permissions();

		$user_has_access = false;

		if (empty($user_permissions)) {
			// If there are no permissions defined, grant access to the user
			$user_has_access = true;
		} else {
			$user = wp_get_current_user();
			$user_roles = $user->roles;

			foreach ($user_permissions as $permission) {
				foreach ($permission as $permission_info) {
					if ($permission_info['provider_nickname'] == $provider_nickname) {
						foreach ($user_roles as $user_role) {
							if (in_array($user_role, $permission_info['role_name'])) {
								$user_has_access = true;
								break;
							}
						}
					}

					if ($user_has_access) {
						break;
					}
				}

				if ($user_has_access) {
					break;
				}
			}
		}

		if (!$user_has_access) {
			return [
				'message' => $option_data['permission_denied_text'],
			];
		}

		// Add check for downloads in the last 20 seconds
		$recent_download_transient_name = 'user_' . get_current_user_id() . '_recent_download';
		$recent_download_transient_value = get_transient($recent_download_transient_name);

		if ($recent_download_transient_value) {
			return [
				'message' => $option_data['download_interval_text'],
			];
		} else {
			set_transient($recent_download_transient_name, true, 20);
		}

		$api_query = http_build_query([
			'url' => $product_page_url,
			'key' => $this->get_api_key(),
			'user-tracking-id' => get_current_user_id(),
			'user-tracking-ip' =>  $_SERVER['REMOTE_ADDR'],
			'user-tracking-browser-fingerprint' => $browser_fingerprint,
			'rate-limiter-user-daily-credits' => $option_data['daily_limit'],
			'rate-limiter-request-cost' => '0',

		]);
		$api_endpoint = $this->n8n_api_url . '?' . $api_query;
		$api_response = wp_remote_request($api_endpoint, [
			'method'      => 'GET',
			'timeout'     => 60,
		]);
		$response_body = wp_remote_retrieve_body($api_response);
		$response_data = json_decode($response_body, true);
		return $response_data;
	}
}
