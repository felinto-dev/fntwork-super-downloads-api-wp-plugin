<?php

class Fntwork_Super_Downloads_API_Manager
{

	private $strapi_api_url;
	private $n8n_api_url;
	private $plugin_name;
	private $version;
	private $settings_manager;

	public function __construct(
		string $plugin_name,
		string $version,
		Fntwork_Super_Downloads_Api_Settings_Manager $settings_manager,
	) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->strapi_api_url = 'https://strapi.fnt.work/api';
		$this->n8n_api_url = 'https://n8n.fnt.work/webhook/super-downloads-api';
		$this->settings_manager = $settings_manager;
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

	public function find_provider_id_by_product_page_url($product_page_url)
	{
		$providers = $this->get_providers();
		$found_provider_id = null;

		foreach ($providers as $provider) {
			foreach ($provider['attributes']['patterns'] as $pattern) {
				$regex = $pattern['regex'];
				if (preg_match('/' . $regex . '/', $product_page_url)) {
					$found_provider_id = $provider['attributes']['provider_id'];
					break;
				}
			}

			if ($found_provider_id) {
				break;
			}
		}

		return $found_provider_id;
	}

	public function generate_provider_download_url($product_page_url, $browser_fingerprint, $download_option_id)
	{
		do_action('super_downloads_api_before_generate_download_url', $product_page_url, $download_option_id);

		$user_id = get_current_user_id();
		$found_provider_id = $this->find_provider_id_by_product_page_url($product_page_url);

		if (!$found_provider_id) {
			return [
				'message' => $this->settings_manager->get_unsupported_service_text(),
			];
		}

		$credits_spent_per_download = apply_filters('super_downloads_api_credits_spent_per_download_default', 1);

		$provider_settings = $this->settings_manager->get_providers_settings();

		$user_has_download_access = false;

		if (!$provider_settings) {
			return [
				'message' => $this->settings_manager->get_permission_denied_text(),
			];
		}


		$user_roles = wp_get_current_user()->roles;
		$provider_settings = $this->settings_manager->get_provider_settings_by_id($found_provider_id);
		$credits_spent_per_download = $provider_settings['credits_spent_per_download'] ?? $credits_spent_per_download;

		foreach ($user_roles as $user_role) {
			if (in_array($user_role, $provider_settings['role_access_list'])) {
				$user_has_download_access = true;
				break;
			}
		}

		$user_has_download_access = apply_filters(
			'super_downloads_api_filter_user_access',
			$user_has_download_access,
			$found_provider_id,
			$product_page_url,
			$download_option_id
		);

		if (!$user_has_download_access) {
			return [
				'message' => $this->settings_manager->get_permission_denied_text(),
			];
		}

		do_action('super_downloads_api_after_user_access_check', $user_has_download_access);

		$download_params_hash = md5("{$product_page_url} // {$download_option_id}");
		$same_file_download_transient_name = "user_{$user_id}_url_{$download_params_hash}";
		$same_file_download_transient_value = get_transient($same_file_download_transient_name);
		if ($same_file_download_transient_value) {
			return [
				'message' => $this->settings_manager->get_same_file_interval_text(),
			];
		} else {
			set_transient($same_file_download_transient_name, true, $this->settings_manager->get_same_file_interval());
		}

		do_action('super_downloads_api_after_check_same_file');

		if (!isset($download_option_id)) {
			$recent_download_transient_name = "user_{$user_id}_recent_download";
			$recent_download_transient_value = get_transient($recent_download_transient_name);

			if ($recent_download_transient_value) {
				return [
					'message' => $this->settings_manager->get_download_interval_text(),
				];
			} else {
				set_transient($recent_download_transient_name, true, $this->settings_manager->get_download_interval());
			}
		}

		do_action('super_downloads_api_after_check_recent_download');

		$request_cost = apply_filters('super_downloads_api_request_cost', (string) $credits_spent_per_download);
		$user_tracking_id = apply_filters('super_downloads_api_user_tracking_id', (string) get_current_user_id());

		$api_request_body = apply_filters('super_downloads_api_request_body', [
			'downloadParams' => [
				'url' => (string) $product_page_url,
				'optionId' => (string) $download_option_id
			],
			'userTracking' => [
				'id' => (string) $user_tracking_id,
				'ip' => (string) $_SERVER['REMOTE_ADDR'],
				'browserFingerprint' => (string) $browser_fingerprint,
				'browserUserAgent' => (string) $_SERVER['HTTP_USER_AGENT'],
			],
			'rateLimiter' => [
				'userDailyCredits' => (string) $this->settings_manager->get_daily_download_limit(),
				'requestCost' => (string) $request_cost,
			]
		]);

		$api_body = json_encode($api_request_body);
		$api_endpoint = "{$this->n8n_api_url}/download";
		$api_response = wp_remote_request($api_endpoint, [
			'method'      => 'POST',
			'timeout'     => 120,
			'headers'     => [
				"Content-Type" => "application/json",
				"x-plugin-version" => $this->version,
				"x-api-key" => (string) $this->settings_manager->get_api_key(),
			],
			'body'        => $api_body
		]);
		do_action('super_downloads_api_before_api_request', $api_endpoint, $api_body);

		$response_body = wp_remote_retrieve_body($api_response);
		$response_data = json_decode($response_body, true);
		do_action('super_downloads_api_after_api_request', $api_endpoint, $api_body, $response_data);

		return apply_filters('super_downloads_api_generate_provider_download_url_response', $response_data);
	}

	public function custom_reached_daily_limit_download_message($response_data) {
		$message = $response_data['translations']['pt_BR'] ?? null;

		if ($message AND $response_data['code'] === '1100') {
			$response_data['translations']['pt_BR'] = $this->settings_manager->get_daily_download_limit_text();
		}

		return $response_data;
	}
}
