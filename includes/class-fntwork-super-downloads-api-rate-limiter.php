<?php

class Fntwork_Super_Downloads_Api_Rate_Limiter
{
	private $plugin_name;

	private $version;

	private $settings_manager;

	private $key = 'super_downloads_api_credits_left';

	private $column_name = 'Créditos restantes';

	public function __construct($plugin_name, $version, Fntwork_Super_Downloads_Api_Settings_Manager $settings_manager)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->settings_manager = $settings_manager;
	}

	private function get_transient_key(Int $user_id)
	{
		return "user_{$user_id}_{$this->key}";
	}

	public function get_credits_left(Int $user_id)
	{
		if (!$user_id) {
			$user_id = get_current_user_id();
		}

		$transient_value = get_transient($this->get_transient_key($user_id));

		if (!$transient_value) {
			return $this->settings_manager->get_daily_download_limit();
		}

		return $transient_value;
	}

	public function set_credits_left(Int $user_id, Int $credits_left)
	{
		$current_time = current_time('timestamp');
		$midnight = strtotime('tomorrow midnight', $current_time);
		$transient_expires_time = $midnight - $current_time;

		if ($transient_expires_time >= 1 && $transient_expires_time <= 86400) {
			set_transient(
				$this->get_transient_key($user_id),
				$credits_left,
				$transient_expires_time
			);
		}
	}

	public function add_user_credits_left_table_list_column($column)
	{
		$column[$this->key] = $this->column_name;
		return $column;
	}

	public function populate_user_credits_left_table_list_column($value, $column_name, $user_id)
	{
		if ($column_name === $this->key) {
			return $this->get_credits_left($user_id);
		}

		return $value;
	}

	public function sortable_user_credits_left_table_list_column($columns)
	{
		$columns[$this->key] = $this->column_name;
		return $columns;
	}
}
