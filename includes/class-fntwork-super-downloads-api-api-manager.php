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
		Fntwork_Super_Downloads_Api_Settings_Manager $settings_manager
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
		$user_id = apply_filters('super_downloads_api_user_tracking_id', (string) get_current_user_id());
		$found_provider_id = $this->find_provider_id_by_product_page_url($product_page_url);

		if (!$found_provider_id) {
			return [
				'message' => $this->settings_manager->get_unsupported_service_text(),
			];
		}

		$credits_spent_per_download = 1;

		$provider_settings = $this->settings_manager->get_providers_settings();

		$user_has_access = false;

		if (!$provider_settings) {
			// If there are no permissions defined, grant access to the user
			$user_has_access = true;
		} else {
			$user = wp_get_current_user();
			$user_roles = $user->roles;

			foreach ($provider_settings as $provider_setting) {
				if ($provider_setting[0]['provider_id'] === $found_provider_id) {
					foreach ($user_roles as $user_role) {
						if (in_array($user_role, $provider_setting[0]['role_name'])) {
							$user_has_access = true;
							$credits_spent_per_download = $provider_setting[0]['credits_spent_per_download'];
							break;
						}
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
				'message' => $this->settings_manager->get_permission_denied_text(),
			];
		}

		$same_file_download_transient_name = 'user_' . $user_id . '_url_' . md5($product_page_url . $download_option_id);
		$same_file_download_transient_value = get_transient($same_file_download_transient_name);

		if ($same_file_download_transient_value) {
			return [
				'message' => $this->settings_manager->get_same_file_interval_text(),
			];
		} else {
			set_transient($same_file_download_transient_name, true, $this->settings_manager->get_same_file_interval());
		}

		if (!isset($download_option_id)) {
			$recent_download_transient_name = 'user_' . $user_id . '_recent_download';
			$recent_download_transient_value = get_transient($recent_download_transient_name);

			if ($recent_download_transient_value) {
				return [
					'message' => $this->settings_manager->get_same_file_interval_text(),
				];
			} else {
				set_transient($recent_download_transient_name, true, $this->settings_manager->get_same_file_interval());
			}
		}

		$request_cost = apply_filters('super_downloads_api_download_credit_cost', (string) $credits_spent_per_download);
		$user_tracking_id = apply_filters('super_downloads_api_user_tracking_id', (string) get_current_user_id());

		$api_body = json_encode([
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

		$api_endpoint = "{$this->n8n_api_url}/download";
		$api_response = wp_remote_request($api_endpoint, [
			'method'      => 'POST',
			'timeout'     => 60,
			'headers'     => [
				"Content-Type" => "application/json",
				"x-plugin-version" => $this->version,
				"x-api-key" => (string) $this->settings_manager->get_api_key(),
			],
			'body'        => $api_body
		]);
		$response_body = wp_remote_retrieve_body($api_response);
		$response_data = json_decode($response_body, true);
		return $response_data;
	}
}
