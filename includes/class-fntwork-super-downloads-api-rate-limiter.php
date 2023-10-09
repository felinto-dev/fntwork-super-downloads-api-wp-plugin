<?php

class Fntwork_Super_Downloads_Api_Rate_Limiter
{
	private $plugin_name;

	private $version;

	private $settings_manager;

	public function __construct($plugin_name, $version, Fntwork_Super_Downloads_Api_Settings_Manager $settings_manager)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->settings_manager = $settings_manager;
	}

	public function get_credits_left($user_id)
	{
		if (!$user_id) {
			$user_id = get_current_user_id();
		}

		$transient_key = "user_{$user_id}_super_downloads_api_credits_left";
		$transient_value = get_transient($transient_key);

		if (!$transient_value) {
			return $this->settings_manager->get_daily_download_limit();
		}

		return $transient_value;
	}
}
