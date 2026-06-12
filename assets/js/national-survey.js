/* National Survey 2026 — [aiad_national_survey] */
(function () {
	'use strict';

	const TOTAL_STEPS = 6;

	function init() {
		const form     = document.getElementById('aiad-survey-form');
		const success  = document.getElementById('aiad-survey-success');
		const errorBox = document.getElementById('aiad-survey-error');
		const backBtn  = document.getElementById('aiad-survey-back');
		const nextBtn  = document.getElementById('aiad-survey-next');
		const submitBtn = document.getElementById('aiad-survey-submit');
		const progressBar   = document.getElementById('aiad-survey-progress-bar');
		const progressLabel = document.getElementById('aiad-survey-progress-label');

		if (!form) return;

		let currentStep = 1;

		// ── Star rating interaction ──────────────────────────────────────────
		document.querySelectorAll('.aiad-survey__stars').forEach(function (group) {
			const labels = Array.from(group.querySelectorAll('.aiad-survey__star-label'));

			labels.forEach(function (label, index) {
				const input = label.querySelector('.aiad-survey__star-input');
				const star  = label.querySelector('.aiad-survey__star');

				// Hover: illuminate up to hovered star
				label.addEventListener('mouseenter', function () {
					labels.forEach(function (l, i) {
						l.querySelector('.aiad-survey__star').style.color = i <= index ? '#f59e0b' : '#d1d5db';
					});
				});

				group.addEventListener('mouseleave', function () {
					reflectChecked();
				});

				input.addEventListener('change', function () {
					reflectChecked();
				});

				function reflectChecked() {
					const checked = group.querySelector('.aiad-survey__star-input:checked');
					const checkedIndex = checked ? labels.indexOf(checked.closest('.aiad-survey__star-label')) : -1;
					labels.forEach(function (l, i) {
						l.querySelector('.aiad-survey__star').style.color = i <= checkedIndex ? '#f59e0b' : '#d1d5db';
					});
				}
			});
		});

		// ── Step navigation ──────────────────────────────────────────────────
		function showStep(step) {
			document.querySelectorAll('.aiad-survey__step').forEach(function (el) {
				el.classList.remove('aiad-survey__step--active');
				el.hidden = true;
			});
			const target = document.querySelector('.aiad-survey__step[data-step="' + step + '"]');
			if (target) {
				target.classList.add('aiad-survey__step--active');
				target.hidden = false;
			}

			// Back button
			if (step > 1) {
				backBtn.hidden = false;
			} else {
				backBtn.hidden = true;
			}

			// Next vs Submit
			if (step === TOTAL_STEPS) {
				nextBtn.hidden   = true;
				submitBtn.hidden = false;
			} else {
				nextBtn.hidden   = false;
				submitBtn.hidden = true;
			}

			// Progress
			const pct = Math.round(((step - 1) / TOTAL_STEPS) * 100);
			progressBar.style.width = pct + '%';
			progressLabel.textContent = 'Step ' + step + ' of ' + TOTAL_STEPS;

			hideError();
		}

		function validateStep(step) {
			const stepEl = document.querySelector('.aiad-survey__step[data-step="' + step + '"]');
			if (!stepEl) return true;

			const requiredSelects = stepEl.querySelectorAll('select[required]');
			for (var i = 0; i < requiredSelects.length; i++) {
				if (!requiredSelects[i].value) {
					showError('Please complete the required field before continuing.');
					requiredSelects[i].focus();
					return false;
				}
			}
			return true;
		}

		function showError(msg) {
			errorBox.textContent = msg;
			errorBox.hidden = false;
			errorBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
		}

		function hideError() {
			errorBox.hidden = true;
			errorBox.textContent = '';
		}

		nextBtn.addEventListener('click', function () {
			if (!validateStep(currentStep)) return;
			if (currentStep < TOTAL_STEPS) {
				currentStep++;
				showStep(currentStep);
			}
		});

		backBtn.addEventListener('click', function () {
			if (currentStep > 1) {
				currentStep--;
				showStep(currentStep);
			}
		});

		// ── Submit ───────────────────────────────────────────────────────────
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			hideError();

			if (!window.aiadSurvey || !window.aiadSurvey.ajaxurl) {
				showError('Configuration error. Please reload the page and try again.');
				return;
			}

			submitBtn.disabled = true;
			submitBtn.textContent = 'Submitting…';

			const data = new FormData(form);
			data.append('action', 'aiad_survey_submit');
			data.append('nonce', window.aiadSurvey.nonce);

			fetch(window.aiadSurvey.ajaxurl, {
				method: 'POST',
				body: data,
				credentials: 'same-origin',
			})
				.then(function (res) { return res.json(); })
				.then(function (json) {
					if (json.success) {
						form.hidden = true;
						document.querySelector('.aiad-survey__progress').hidden = true;
						document.querySelector('.aiad-survey__progress-label').hidden = true;
						success.hidden = false;
						progressBar.style.width = '100%';
					} else {
						var msg = (json.data && json.data.message) ? json.data.message : 'Something went wrong. Please try again.';
						showError(msg);
						submitBtn.disabled = false;
						submitBtn.textContent = 'Submit survey';
					}
				})
				.catch(function () {
					showError('Network error. Please check your connection and try again.');
					submitBtn.disabled = false;
					submitBtn.textContent = 'Submit survey';
				});
		});

		// Initialise: hide all steps then show step 1
		document.querySelectorAll('.aiad-survey__step').forEach(function (el) {
			el.hidden = true;
		});
		showStep(1);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
