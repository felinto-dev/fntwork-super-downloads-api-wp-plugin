<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://t.me/felinto
 * @since      1.0.0
 *
 * @package    Fntwork_Super_Downloads_Api
 * @subpackage Fntwork_Super_Downloads_Api/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap">
	<h2>Super Downloads API</h2>
	<form method="post" action="options.php">
		<?php
		settings_fields('super_downloads_api_options');
		do_settings_sections('super_downloads_api_options');
		submit_button();
		?>
	</form>
</div>