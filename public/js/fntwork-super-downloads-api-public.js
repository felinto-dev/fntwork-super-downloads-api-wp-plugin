// Importação e configuração da biblioteca de impressões digitais
const fpPromise = import('https://esm3.felintonetwork.com')
	.then(FingerprintJS => FingerprintJS.load({ monitoring: false }));

// Definição dos seletores de elementos
const DOWNLOAD_FORM = 'download-form';
const URL_INPUT = 'url-input';
const ERROR_MSG = 'error-msg';
const PROGRESS_BAR = 'progress-bar';
const PROGRESS_TIME = 'progress-time';
const EXTRA_DOWNLOAD_OPTIONS = 'extra-download-options';
const DOWNLOAD_OPTIONS_LINKS = 'extra-download-options-links';

// Parâmetros para a simulação do progresso do download
const PROGRESS_DURATION = 15 * 1000;
const TIME_UPDATE_FREQUENCY = 100;
const SHOW_TIME_AFTER = 2000;

// Função que retorna uma promessa que resolve para a ID de impressão digital do usuário
const getVisitorId = () => {
	return fpPromise
		.then(fp => fp.get())
		.then(result => result.visitorId)
		.catch(() => 'UNKNOWN-BROWSER-FINGERPRINT');
};

// Funções que obtêm elementos da interface do usuário
const getDownloadForm = () => document.getElementById(DOWNLOAD_FORM);
const getUrlInput = () => document.getElementById(URL_INPUT);
const getErrorMsg = () => document.getElementById(ERROR_MSG);
const getProgressBar = () => document.getElementById(PROGRESS_BAR);
const getProgressTime = () => document.getElementById(PROGRESS_TIME);
const getExtraDownloadOptions = () => document.getElementById(EXTRA_DOWNLOAD_OPTIONS);
const getDownloadOptionsLinks = () => document.getElementById(DOWNLOAD_OPTIONS_LINKS);

// Funções de controle da interface do usuário
const showError = (msg) => {
	const errorMsg = getErrorMsg();
	errorMsg.style.display = 'block';
	errorMsg.innerHTML = msg;
};

const resetForm = ({ cleanUrlInput }) => {
	getDownloadOptionsLinks().innerHTML = '';

	const progressBar = getProgressBar();
	progressBar.style.display = 'none';
	progressBar.style.width = '0%';

	// Reset and hide progress time
	const progressTime = getProgressTime();
	progressTime.innerText = '';
	progressTime.style.display = 'none';

	if (cleanUrlInput) {
		getUrlInput().value = '';
	}
};

const simulateProgress = (() => {
	let startTimestamp, progress;
	let stopAnimation = false;
	let lastTimeUpdateTimestamp = 0;

	const resetProgress = () => {
		startTimestamp = null;
		stopAnimation = false;
		progress = 0;
	};

	const stopProgress = () => {
		stopAnimation = true;
	};

	const step = (timestamp) => {
		if (!startTimestamp) startTimestamp = timestamp;
		const elapsed = timestamp - startTimestamp;
		if ((timestamp - lastTimeUpdateTimestamp >= TIME_UPDATE_FREQUENCY) && (elapsed > SHOW_TIME_AFTER)) {
			getProgressTime().innerText = ((elapsed) / 1000).toFixed(1) + "s";
			lastTimeUpdateTimestamp = timestamp;
			getProgressTime().style.display = 'flex';
		}
		progress = Math.min(99, (elapsed / PROGRESS_DURATION) * 100);
		getProgressBar().style.width = progress + "%";
		if (!stopAnimation) {
			requestAnimationFrame(step);
		}
	};

	return {
		start: () => {
			resetProgress();
			requestAnimationFrame(step);
		},
		stop: stopProgress
	};
})();

// Função que inicia o download
const startDownload = () => {
	getProgressBar().style.display = 'block';
	getErrorMsg().style.display = 'none';
	getDownloadForm().style.display = 'none';
	getExtraDownloadOptions().style.display = 'none';
	getDownloadOptionsLinks().innerHTML = '';
	simulateProgress.start();
	getVisitorId().then(visitorId => {
		const formData = new FormData(getDownloadForm());
		formData.append('user-tracking-browser-fingerprint', visitorId);
		return fetch(fntwork_ajax_object.ajaxurl, {
			method: 'POST',
			credentials: 'same-origin',
			body: formData
		});
	}).then(response => response.json())
		.then(response => {
			getDownloadForm().style.display = 'flex';

			if (response.data.downloadUrl) {
				confetti({
					particleCount: 150,
					spread: 2000,
					startVelocity: 20,
					ticks: 1000,
					origin: {
						y: 0.5,
						x: 0.5
					}
				});
				simulateProgress.stop();
				window.location.href = response.data.downloadUrl;
				resetForm({ cleanUrlInput: true });
			} else if (response.data.code === '1007') {
				getExtraDownloadOptions().style.display = 'flex';
				const links = getDownloadOptionsLinks();
				const downloadOptions = response.data.downloadOptions;

				simulateProgress.stop();
				resetForm({ cleanUrlInput: false });

				downloadOptions.forEach((downloadOption) => {
					const button = document.createElement("button");
					button.id = downloadOption.id;
					button.innerHTML = downloadOption.title;
					button.type = 'submit';
					button.addEventListener('click', (event) => {
						event.preventDefault();

						const downloadForm = getDownloadForm();
						let inputHiddenDownloadOptionId = downloadForm.querySelector('input[name="download-option-id"]');

						if (inputHiddenDownloadOptionId) {
							inputHiddenDownloadOptionId.value = event.target.getAttribute('id');
						} else {
							inputHiddenDownloadOptionId = document.createElement('input');
							inputHiddenDownloadOptionId.type = 'hidden';
							inputHiddenDownloadOptionId.name = 'download-option-id';
							inputHiddenDownloadOptionId.value = event.target.getAttribute('id');
							downloadForm.appendChild(inputHiddenDownloadOptionId);
						}

						resetForm({ cleanUrlInput: false });
						startDownload();
					})
					links.appendChild(button);
				});
			} else {
				showError(response.data.message || response.data.translations.pt_BR);
				simulateProgress.stop();
				resetForm({ cleanUrlInput: false });
			}
		});
};

// Adicionando listeners de eventos ao carregar o documento
document.addEventListener('DOMContentLoaded', () => {
	const urlParams = new URLSearchParams(window.location.search);
	const url = urlParams.get('url');
	if (url) {
		getUrlInput().value = url;
		startDownload();
	}
	getDownloadForm().addEventListener('submit', (event) => {
		event.preventDefault();
		startDownload();
	});
});
