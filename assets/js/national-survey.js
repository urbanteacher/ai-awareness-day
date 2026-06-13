/* National Survey 2026 — [aiad_national_survey]
 *
 * Step sequences:
 *   Participant path    : profile → gate → resource-friction → learning-efficacy → strategic-roadmap → contact
 *   Non-participant path: profile → gate → non-participant-a → non-participant-b → contact
 *
 * Path is decided on the "gate" step based on the "participated" radio.
 */
(function () {
	'use strict';

	var PARTICIPANT_STEPS     = ['profile', 'gate', 'hopes', 'resource-friction', 'learning-efficacy', 'strategic-roadmap', 'contact'];
	var NON_PARTICIPANT_STEPS = ['profile', 'gate', 'non-participant-a', 'non-participant-b', 'contact'];

	function init() {
		var form        = document.getElementById('aiad-survey-form');
		var success     = document.getElementById('aiad-survey-success');
		var errorBox    = document.getElementById('aiad-survey-error');
		var backBtn     = document.getElementById('aiad-survey-back');
		var nextBtn     = document.getElementById('aiad-survey-next');
		var submitBtn   = document.getElementById('aiad-survey-submit');
		var stepperEl   = document.getElementById('aiad-survey-stepper');
		var progressLbl = document.getElementById('aiad-survey-progress-label');

		if (!form) return;

		var sequence    = PARTICIPANT_STEPS.slice(); // default; overridden at gate
		var currentIdx  = 0;

		// ── Participation scale visibility ───────────────────────────────────
		var scaleWrap = document.getElementById('survey-participation-scale-wrap');
		document.querySelectorAll('input[name="participated"]').forEach(function (radio) {
			radio.addEventListener('change', function () {
				if (scaleWrap) {
					scaleWrap.style.display = this.value === 'yes' ? '' : 'none';
				}
				// Rebuild sequence and refresh button + stepper state
				sequence = this.value === 'yes' ? PARTICIPANT_STEPS.slice() : NON_PARTICIPANT_STEPS.slice();
				if (currentIdx > sequence.length - 1) {
					currentIdx = sequence.length - 1;
				}
				buildStepper(sequence.length);
				updateStepper(currentIdx);
				var isLast = currentIdx === sequence.length - 1;
				nextBtn.hidden   = isLast;
				submitBtn.hidden = !isLast;
			});
		});

		// ── Star rating interaction ──────────────────────────────────────────
		document.querySelectorAll('.aiad-survey__stars').forEach(function (group) {
			var labels = Array.from(group.querySelectorAll('.aiad-survey__star-label'));

			function reflectChecked() {
				var checked = group.querySelector('.aiad-survey__star-input:checked');
				var checkedIdx = checked ? labels.indexOf(checked.closest('.aiad-survey__star-label')) : -1;
				labels.forEach(function (l, i) {
					l.querySelector('.aiad-survey__star').style.color = i <= checkedIdx ? '#f59e0b' : '#d1d5db';
				});
			}

			labels.forEach(function (label, index) {
				label.addEventListener('mouseenter', function () {
					labels.forEach(function (l, i) {
						l.querySelector('.aiad-survey__star').style.color = i <= index ? '#f59e0b' : '#d1d5db';
					});
				});
				label.querySelector('.aiad-survey__star-input').addEventListener('change', reflectChecked);
			});

			group.addEventListener('mouseleave', reflectChecked);
		});

		// ── Step rendering ───────────────────────────────────────────────────
		function getStepEl(stepId) {
			return document.querySelector('.aiad-survey__step[data-step-id="' + stepId + '"]');
		}

		// Build one segment per step in the active path (rebuilds if the path length changes).
		function buildStepper(total) {
			if (!stepperEl || stepperEl.children.length === total) return;
			var html = '';
			for (var i = 0; i < total; i++) {
				html += '<span class="aiad-survey__step-seg" role="listitem"></span>';
			}
			stepperEl.innerHTML = html;
		}

		function updateStepper(idx) {
			if (!stepperEl) return;
			var segs = stepperEl.querySelectorAll('.aiad-survey__step-seg');
			for (var i = 0; i < segs.length; i++) {
				segs[i].classList.remove('is-done', 'is-current');
				if (i < idx) {
					segs[i].classList.add('is-done');
				} else if (i === idx) {
					segs[i].classList.add('is-current');
				}
			}
		}

		function showStep(idx) {
			// Hide all steps
			document.querySelectorAll('.aiad-survey__step').forEach(function (el) {
				el.hidden = true;
				el.classList.remove('aiad-survey__step--active', 'aiad-survey__step--error');
			});

			var stepId = sequence[idx];
			var el = getStepEl(stepId);
			if (el) {
				el.hidden = false;
				el.classList.add('aiad-survey__step--active');
				// Move focus to the step so screen readers announce the new section.
				el.setAttribute('tabindex', '-1');
				el.focus({ preventScroll: true });
				el.scrollIntoView({ behavior: 'smooth', block: 'start' });
			}

			var total = sequence.length;
			var humanStep = idx + 1;

			// Progress
			buildStepper(total);
			updateStepper(idx);
			progressLbl.textContent = 'Step ' + humanStep + ' of ' + total;

			// Back button
			backBtn.hidden = idx === 0;

			// Next vs Submit
			var isLast = idx === sequence.length - 1;
			nextBtn.hidden   = isLast;
			submitBtn.hidden = !isLast;

			hideError();
		}

		// ── Validation ───────────────────────────────────────────────────────
		function validateStep(idx) {
			var stepId = sequence[idx];
			var el = getStepEl(stepId);
			if (!el) return true;

			// Required radio groups — ignore any inside a hidden sub-section
			// (e.g. the participation-scale question when "did not participate"
			// is chosen). Validating a hidden required field would block Next
			// forever, freezing the survey.
			var radioGroups = {};
			el.querySelectorAll('input[type="radio"][required]').forEach(function (r) {
				if (r.offsetParent === null) {
					return; // not rendered / hidden
				}
				radioGroups[r.name] = radioGroups[r.name] || [];
				radioGroups[r.name].push(r);
			});
			for (var name in radioGroups) {
				var checked = radioGroups[name].some(function (r) { return r.checked; });
				if (!checked) {
					el.classList.add('aiad-survey__step--error');
					showError('Please select an answer before continuing.');
					radioGroups[name][0].focus();
					return false;
				}
			}

			// Gate step: must have a participated answer
			if (stepId === 'gate') {
				var participated = el.querySelector('input[name="participated"]:checked');
				if (!participated) {
					el.classList.add('aiad-survey__step--error');
					showError('Please tell us whether your school participated.');
					return false;
				}
				// Lock in the sequence based on answer
				sequence = participated.value === 'yes' ? PARTICIPANT_STEPS.slice() : NON_PARTICIPANT_STEPS.slice();
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

		// Clear the panel error highlight as soon as the user answers.
		form.addEventListener('change', function (e) {
			var step = e.target.closest('.aiad-survey__step--error');
			if (step) {
				step.classList.remove('aiad-survey__step--error');
				hideError();
			}
		});

		// ── Navigation ───────────────────────────────────────────────────────
		nextBtn.addEventListener('click', function () {
			if (!validateStep(currentIdx)) return;
			if (currentIdx < sequence.length - 1) {
				currentIdx++;
				showStep(currentIdx);
			}
		});

		backBtn.addEventListener('click', function () {
			if (currentIdx > 0) {
				currentIdx--;
				showStep(currentIdx);
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

			var data = new FormData(form);
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
						if (stepperEl) stepperEl.hidden = true;
						document.querySelector('.aiad-survey__progress-label').hidden = true;
						success.hidden = false;
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

		// Initialise — hide all, then show first step; hide participation scale until "yes" chosen
		document.querySelectorAll('.aiad-survey__step').forEach(function (el) {
			el.hidden = true;
		});
		if (scaleWrap) scaleWrap.style.display = 'none';
		showStep(0);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
