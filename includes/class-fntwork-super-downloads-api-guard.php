<?php

class Fntwork_Super_Downloads_Api_Guard
{
	private $plugin_name;
	private $version;
	private $settings_manager;

	public function __construct(
		$plugin_name,
		 $version,
		Fntwork_Super_Downloads_Api_Settings_Manager $settings_manager )
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->settings_manager = $settings_manager;
	}

	public function has_recently_downloaded_same_file(
		$product_page_url,
		$download_option_id,
	) {
		$user_id = get_current_user_id();

		$download_params_hash = md5("{$product_page_url} // {$download_option_id}");
		$same_file_download_transient_name = "user_{$user_id}_url_{$download_params_hash}";
		$same_file_download_transient_value = get_transient($same_file_download_transient_name);

		if ($same_file_download_transient_value) {
			return true;
		}

		set_transient($same_file_download_transient_name, true, $this->settings_manager->get_same_file_interval());
		return false;
	}
}
