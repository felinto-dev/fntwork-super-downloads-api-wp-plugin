<?php

class Fntwork_Super_Downloads_Api_Settings_Manager
{
	private $plugin_name;
	private $version;

	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
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

	public function get_providers_settings()
	{
		$option_data = $this->get_option_data();

		$filtered_option_data = [];

		/**
		 * Filter out any options that don't have a role_name and provider_nickname
		 */
		foreach ($option_data as $key => $value) {
			if (
				is_array($value)
				&& isset($value[0]['role_name'])
				&& isset($value[0]['provider_id'])
			) {
				$filtered_option_data[$key] = $value;
			}
		}

		return $filtered_option_data;
	}

	public function get_daily_download_limit()
	{
		$daily_download_limit = $this->get_option_data()['rate_limiter_group'][0]['daily_limit'];
		return apply_filters('super_downloads_api_user_daily_credits', $daily_download_limit);
	}

	public function get_unsupported_service_text()
	{
		return $this->get_option_data()['unsupported_service_text'];
	}

	public function get_permission_denied_text()
	{
		return $this->get_option_data()['permission_denied_text'];
	}

	public function get_same_file_interval()
	{
		return $this->get_option_data()['same_file_interval'];
	}

	public function get_same_file_interval_text()
	{
		return $this->get_option_data()['same_file_interval_text'];
	}

	public function get_download_interval()
	{
		return $this->get_option_data()['download_interval'];
	}

	public function get_download_interval_text()
	{
		return $this->get_option_data()['download_interval_text'];
	}
}
