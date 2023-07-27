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
			<button type="submit" id="download-btn">
				<svg id="fi_3039520" enable-background="new 0 0 510 510" height="24" viewBox="0 0 510 510" width="24" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
					<linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="179.548" x2="179.548" y1="16.564" y2="79.544">
						<stop offset="0" stop-color="#ff9100"></stop>
						<stop offset="1" stop-color="#e63950"></stop>
					</linearGradient>
					<linearGradient id="SVGID_2_" gradientUnits="userSpaceOnUse" x1="134.718" x2="134.718" y1="16.564" y2="79.544">
						<stop offset="0" stop-color="#fdbf00"></stop>
						<stop offset="1" stop-color="#ff9100"></stop>
					</linearGradient>
					<linearGradient id="SVGID_3_" gradientUnits="userSpaceOnUse" x1="58.115" x2="356.085" y1="35.005" y2="332.976">
						<stop offset="0" stop-color="#ffda2d"></stop>
						<stop offset="1" stop-color="#fdbf00"></stop>
					</linearGradient>
					<linearGradient id="lg1">
						<stop offset="0" stop-color="#76e2f8"></stop>
						<stop offset="1" stop-color="#48b2e3"></stop>
					</linearGradient>
					<linearGradient id="SVGID_4_" gradientUnits="userSpaceOnUse" x1="378.706" x2="393.086" xlink:href="#lg1" y1="109.598" y2="120.555"></linearGradient>
					<linearGradient id="SVGID_5_" gradientUnits="userSpaceOnUse" x1="418.837" x2="433.217" xlink:href="#lg1" y1="109.598" y2="120.555"></linearGradient>
					<linearGradient id="SVGID_6_" gradientUnits="userSpaceOnUse" x1="458.967" x2="473.348" xlink:href="#lg1" y1="109.598" y2="120.555"></linearGradient>
					<linearGradient id="lg2">
						<stop offset="0" stop-color="#ff9100" stop-opacity="0"></stop>
						<stop offset="1" stop-color="#ff9100"></stop>
					</linearGradient>
					<linearGradient id="SVGID_7_" gradientUnits="userSpaceOnUse" x1="255" x2="255" xlink:href="#lg2" y1="342.315" y2="425.163"></linearGradient>
					<linearGradient id="SVGID_8_" gradientUnits="userSpaceOnUse" x1="434.826" x2="241.01" xlink:href="#lg2" y1="406.791" y2="281.556"></linearGradient>
					<linearGradient id="SVGID_9_" gradientUnits="userSpaceOnUse" x1="209.289" x2="458.848" y1="268.711" y2="518.271">
						<stop offset="0" stop-color="#b9dd39"></stop>
						<stop offset="1" stop-color="#0b799d"></stop>
					</linearGradient>
					<linearGradient id="SVGID_10_" gradientUnits="userSpaceOnUse" x1="247.812" x2="-18.188" y1="347.781" y2="575.781">
						<stop offset="0" stop-color="#0b799d" stop-opacity="0"></stop>
						<stop offset=".3645" stop-color="#096380" stop-opacity=".365"></stop>
						<stop offset=".76" stop-color="#084f67" stop-opacity=".76"></stop>
						<stop offset="1" stop-color="#07485e"></stop>
					</linearGradient>
					<g>
						<g>
							<path d="m266.783 79.505h-174.47v-49.69c0-16.467 13.348-29.815 29.814-29.815h114.841c16.466 0 29.815 13.348 29.815 29.815z" fill="url(#SVGID_1_)"></path>
							<path d="m221.953 79.505h-174.471v-49.69c0-16.467 13.349-29.815 29.815-29.815h114.841c16.466 0 29.815 13.348 29.815 29.815z" fill="url(#SVGID_2_)"></path>
							<path d="m174.47 56.592v-26.777c0-16.467-13.348-29.815-29.814-29.815h-114.841c-16.467 0-29.815 13.348-29.815 29.815v321.925c0 23.458 19.017 42.475 42.475 42.475h425.05c23.458 0 42.475-19.017 42.475-42.475v-239.698c0-23.458-19.017-42.475-42.475-42.475h-280.08c-7.166 0-12.975-5.809-12.975-12.975z" fill="url(#SVGID_3_)"></path>
							<g>
								<path d="m387.317 128h-10.229c-5.867 0-10.623-4.756-10.623-10.623v-10.229c0-5.867 4.756-10.623 10.623-10.623h10.229c5.867 0 10.623 4.756 10.623 10.623v10.229c0 5.867-4.756 10.623-10.623 10.623z" fill="url(#SVGID_4_)"></path>
								<path d="m427.448 128h-10.229c-5.867 0-10.623-4.756-10.623-10.623v-10.229c0-5.867 4.756-10.623 10.623-10.623h10.229c5.867 0 10.623 4.756 10.623 10.623v10.229c0 5.867-4.756 10.623-10.623 10.623z" fill="url(#SVGID_5_)"></path>
								<path d="m467.579 128h-10.229c-5.867 0-10.623-4.756-10.623-10.623v-10.229c0-5.867 4.756-10.623 10.623-10.623h10.229c5.867 0 10.623 4.756 10.623 10.623v10.229c0 5.867-4.756 10.623-10.623 10.623z" fill="url(#SVGID_6_)"></path>
							</g>
							<path d="m0 304.771v46.969c0 23.458 19.017 42.475 42.475 42.475h425.05c23.458 0 42.475-19.017 42.475-42.475v-46.969z" fill="url(#SVGID_7_)"></path>
						</g>
						<path d="m509.153 360.208-186.638-186.639c-4.586-6.357-12.052-7.498-20.49-7.498h-88.095c-13.947 0-22.252 8.305-22.252 22.252v98.309h-38.598c-25.104 0-37.39 24.632-24.051 45.899l35.685 61.682h302.811c20.558.001 37.703-14.605 41.628-34.005z" fill="url(#SVGID_8_)"></path>
						<g>
							<path d="m356.92 283.631h-29.641v-95.307c0-13.947-11.307-25.254-25.254-25.254h-88.095c-13.947 0-25.254 11.307-25.254 25.254v95.307h-35.596c-25.104 0-40.392 27.634-27.053 48.901l101.92 162.502c12.516 19.955 41.59 19.955 54.106 0l101.92-162.502c13.339-21.268-1.949-48.901-27.053-48.901z" fill="url(#SVGID_9_)"></path>
							<path d="m356.92 283.631h-29.641v-95.307c0-13.947-11.307-25.254-25.254-25.254h-47.025v346.93c10.398 0 20.795-4.989 27.053-14.966l101.92-162.502c13.339-21.268-1.949-48.901-27.053-48.901z" fill="url(#SVGID_10_)"></path>
						</g>
					</g>
				</svg>
				Fazer download
			</button>
		</form>
		<p class="error" id="error-msg">Algo deu errado ao buscar seu link, verifique a URL e tente novamente!</p>
		<div id="progress-container">
			<div id="progress-bar">
				<span id="progress-time">0s</span>
			</div>
		</div>
		<div id="extra-download-options">
			<div>
				<span id="extra-download-options-heading">
					Selecione o formato do arquivo que você deseja fazer download:
				</span>
			</div>
			<div id="extra-download-options-links">
				<button type="submit">4K / 4096 x 2160 / MP4</button>
				<button type="submit">4K / 3840 x 2160 / MP4</button>
				<button type="submit">hd / 1920 x 1080 / MP4</button>
			</div>
		</div>
	<?php else : ?>
		<p class="error" style="display: block;">Você precisa estar logado para acessar esta página.</p>
		<a href="<?php echo wp_login_url(get_permalink()); ?>">Clique aqui para fazer login</a>
	<?php endif; ?>
</div>