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

	/**
	 * Filter out any options that don't have a role_name and provider_nickname
	 */
	public function get_user_role_by_provider_access_permissions()
	{
		$option_data = $this->get_option_data();

		$filtered_option_data = [];

		/**
		 * Filter out any options that don't have a role_name and provider_nickname
		 */
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
			$url =  $this->strapi_api_url . '/third-party-providers';
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

	public function generate_provider_download_url($product_page_url, $browser_fingerprint, $download_option_id)
	{
		$option_data = $this->get_option_data();

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

		$credits_spent_per_download = 1;

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
								$credits_spent_per_download = $permission_info['credits_spent_per_download'];
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

		$user_has_access = apply_filters('super_downloads_api_allow_user_access', $user_has_access);

		if (!$user_has_access) {
			return [
				'message' => $option_data['permission_denied_text'],
			];
		}

		$same_file_download_transient_name = 'user_' . get_current_user_id() . '_url_' . md5($product_page_url . $download_option_id);
		$same_file_download_transient_value = get_transient($same_file_download_transient_name);

		if ($same_file_download_transient_value) {
			return [
				'message' => $option_data['same_file_interval_text'],
			];
		} else {
			set_transient($same_file_download_transient_name, true, $option_data['same_file_interval']);
		}

		if (!isset($download_option_id)) {
			$recent_download_transient_name = 'user_' . get_current_user_id() . '_recent_download';
			$recent_download_transient_value = get_transient($recent_download_transient_name);

			if ($recent_download_transient_value) {
				return [
					'message' => $option_data['download_interval_text'],
				];
			} else {
				set_transient($recent_download_transient_name, true, $option_data['download_interval']);
			}
		}

		$user_daily_credits = apply_filters('super_downloads_api_user_daily_credits', (string) $option_data['rate_limiter_group'][0]['daily_limit']);
		$request_cost = apply_filters('super_downloads_api_download_credit_cost', (string) $credits_spent_per_download);
		$user_tracking_id = apply_filters('super_downloads_api_user_tracking_id', (string) get_current_user_id());

		$api_body = json_encode([
			'downloadParams' => [
				'url' => (string) $product_page_url,
				'optionId' => (string) $download_option_id
			],
			'key' => (string) $this->get_api_key(),
			'userTracking' => [
				'id' => (string) $user_tracking_id,
				'ip' => (string) $_SERVER['REMOTE_ADDR'],
				'browserFingerprint' => (string) $browser_fingerprint,
				'browserUserAgent' => (string) $_SERVER['HTTP_USER_AGENT'],
			],
			'rateLimiter' => [
				'userDailyCredits' => (string) $user_daily_credits,
				'requestCost' => (string) $request_cost
			]
		]);

		$api_endpoint = "{$this->n8n_api_url}/download";
		$api_response = wp_remote_request($api_endpoint, [
			'method'      => 'POST',
			'timeout'     => 60,
			'headers'     => [
				"Content-Type" => "application/json",
				"x-plugin-version" => $this->version,
			],
			'body'        => $api_body
		]);
		$response_body = wp_remote_retrieve_body($api_response);
		$response_data = json_decode($response_body, true);
		return $response_data;
	}
}
