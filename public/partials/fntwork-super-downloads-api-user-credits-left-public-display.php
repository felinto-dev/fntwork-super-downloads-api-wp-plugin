<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://t.me/felinto
 * @since      1.0.0
 *
 * @package    Fntwork_Super_Downloads_Api
 * @subpackage Fntwork_Super_Downloads_Api/public/partials
 */
?>

<!--
	Desenvolvedor: Felinto
	Telegram: https://t.me/felinto
-->

<?php
if (is_user_logged_in()) : ?>
	<span id="super-downloads-api-credits-left-counter"><?php echo $user_credits_left ?></span>
<?php endif; ?>