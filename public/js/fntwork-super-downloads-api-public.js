const fpPromise = import('https://esm3.felintonetwork.com')
	.then(FingerprintJS => FingerprintJS.load({
		monitoring: false
	}))

// Element Selectors
const DOWNLOAD_FORM = 'download-form';
const URL_INPUT = 'url-input';
const ERROR_MSG = 'error-msg';
const PROGRESS_BAR = 'progress-bar';
const PROGRESS_TIME = 'progress-time'; // Added selector for time display

// Progress simulation parameters
const PROGRESS_DURATION = 15 * 1000; // 15 seconds
const TIME_UPDATE_FREQUENCY = 100; // Update time every 100ms

// Function to simulate progress bar
let simulateProgress = (() => {
	let startTimestamp, progress;
	let stopAnimation = false;
	let lastTimeUpdateTimestamp = 0;

	const resetProgress = () => {
		startTimestamp = null;
		stopAnimation = false;
		progress = 0;
	};

	const SHOW_TIME_AFTER = 2000; // Show time after 2 seconds

	const step = (timestamp) => {
		if (!startTimestamp) startTimestamp = timestamp;
		const elapsed = timestamp - startTimestamp;

		// Update time every TIME_UPDATE_FREQUENCY ms and after SHOW_TIME_AFTER ms
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

	return () => {
		resetProgress();
		animationFrameId = requestAnimationFrame(step);
	};
})();

// Element getter functions
const getDownloadForm = () => document.getElementById(DOWNLOAD_FORM);
const getUrlInput = () => document.getElementById(URL_INPUT);
const getErrorMsg = () => document.getElementById(ERROR_MSG);
const getProgressBar = () => document.getElementById(PROGRESS_BAR);
const getProgressTime = () => document.getElementById(PROGRESS_TIME); // Added getter for time display

// UI Control functions
const showError = (msg) => {
	const errorMsg = getErrorMsg();
	errorMsg.style.display = 'block';
	errorMsg.innerHTML = msg;
};

const resetForm = ({ cleanUrlInput }) => {
	const progressBar = getProgressBar();
	progressBar.style.display = 'none';
	progressBar.style.width = '0%';

	// Reset progress time
	getProgressTime().innerText = '';

	if (cleanUrlInput) {
		getUrlInput().value = '';
	}
};

// Download function
const startDownload = () => {
	// Show progress bar and hide error message
	getProgressBar().style.display = 'block';
	getErrorMsg().style.display = 'none';

	// Simulate progress bar
	simulateProgress();

	fpPromise
		.then(fp => fp.get())
		.then(result => result.visitorId)
		.catch(() => 'UNKNOWN-BROWSER-FINGERPRINT')
		.then(visitorId => {
			const formData = new FormData(getDownloadForm());
			formData.append('user-tracking-browser-fingerprint', visitorId);

			return fetch(fntwork_ajax_object.ajaxurl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData
			});
		})
		.then(response => response.json())
		.then(response => {
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

				window.location.href = response.data.downloadUrl;
				resetForm({ cleanUrlInput: true });
			} else {
				showError(response.data.message || response.data.translations.pt_BR);
				resetForm({ cleanUrlInput: false });
			}
		});
};

// Event Handlers
const onFormSubmit = (event) => {
	event.preventDefault();
	startDownload();
};

// Setup event listeners
document.addEventListener('DOMContentLoaded', () => {
	const urlParams = new URLSearchParams(window.location.search);
	const url = urlParams.get('url');
	if (url) {
		getUrlInput().value = url;
		startDownload();
	}

	getDownloadForm().addEventListener('submit', onFormSubmit);
});
