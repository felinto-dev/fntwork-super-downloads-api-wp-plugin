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

<div id="super-downloads-api">
	<?php
	if (is_user_logged_in()) : ?>
		<form id="download-form">
			<?php wp_nonce_field('download_form_nonce'); ?>
			<input type="hidden" name="action" value="process_download_form">
			<input type="url" name="url-input" id="url-input" placeholder="Cole aqui o link" required autofocus>
			<button type="submit" id="download-btn">⬇️ Fazer download</button>
		</form>
		<p class="error" id="error-msg">Algo deu errado ao buscar seu link, verifique a URL e tente novamente!</p>
		<div id="progress-container">
			<div id="progress-bar"></div>
		</div>
	<?php else : ?>
		<p class="error" style="display: block;">Você precisa estar logado para acessar esta página.</p>
		<a href="<?php echo wp_login_url(get_permalink()); ?>">Clique aqui para fazer login</a>
	<?php endif; ?>
</div>