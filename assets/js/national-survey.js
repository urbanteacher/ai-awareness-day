/* National Survey 2026 — [aiad_national_survey]
 *
 * Step sequences:
 *   Participant path    : profile → school-readiness → hopes → participant-feedback → reach-impact → contact
 *   Non-participant path: profile → non-participant-a → school-readiness → reach-impact → contact
 *
 * Path is decided on the profile step based on the "participated" radio.
 */
(function () {
	'use strict';

	var PARTICIPANT_STEPS     = ['profile', 'school-readiness', 'hopes', 'participant-feedback', 'reach-impact', 'contact'];
	var NON_PARTICIPANT_STEPS = ['profile', 'non-participant-a', 'school-readiness', 'reach-impact', 'contact'];

	function init() {
		var form        = document.getElementById('aiad-survey-form');
		var success     = document.getElementById('aiad-survey-success');
		var errorBox    = document.getElementById('aiad-survey-error');
		var backBtn     = document.getElementById('aiad-survey-back');
		var nextBtn     = document.getElementById('aiad-survey-next');
		var submitBtn   = document.getElementById('aiad-survey-submit');
		var stepperEl   = document.getElementById('aiad-survey-stepper');
		var progressEl  = document.getElementById('aiad-survey-progress');
		var progressLbl = document.getElementById('aiad-survey-progress-label');

		if (!form) return;

		var sequence    = PARTICIPANT_STEPS.slice(); // default; overridden when leaving profile
		var currentIdx  = 0;

		function isParticipationScaleHidden() {
			var scaleWrap = document.getElementById('survey-participation-scale-wrap');
			return !scaleWrap || scaleWrap.style.display === 'none';
		}

		function shouldSkipField(input, allowHiddenSteps) {
			if (input.closest('#survey-participation-scale-wrap') && isParticipationScaleHidden()) {
				return true;
			}
			if (input.closest('.aiad-survey__path-participant[hidden], .aiad-survey__path-non-participant[hidden]')) {
				return true;
			}
			if (!allowHiddenSteps && input.offsetParent === null) {
				return true;
			}
			return false;
		}

		function isParticipantPath() {
			var participated = document.querySelector('input[name="participated"]:checked');
			return participated && participated.value === 'yes';
		}

		function toggleReachImpactPath() {
			var participant = isParticipantPath();
			var participantBlock = document.querySelector('.aiad-survey__path-participant');
			var nonParticipantBlock = document.querySelector('.aiad-survey__path-non-participant');
			var recommendParticipant = document.querySelector('.aiad-survey__recommend-label-participant');
			var recommendNonParticipant = document.querySelector('.aiad-survey__recommend-label-non-participant');

			if (participantBlock) {
				participantBlock.hidden = !participant;
			}
			if (nonParticipantBlock) {
				nonParticipantBlock.hidden = participant;
			}
			if (recommendParticipant) {
				recommendParticipant.hidden = !participant;
			}
			if (recommendNonParticipant) {
				recommendNonParticipant.hidden = participant;
			}
		}

		function lockSequenceFromProfile() {
			var profileEl = getStepEl('profile');
			if (!profileEl) return false;

			var participated = profileEl.querySelector('input[name="participated"]:checked');
			if (!participated) {
				return false;
			}

			sequence = participated.value === 'yes' ? PARTICIPANT_STEPS.slice() : NON_PARTICIPANT_STEPS.slice();
			return true;
		}

		// ── Participation scale visibility ───────────────────────────────────
		var scaleWrap = document.getElementById('survey-participation-scale-wrap');
		document.querySelectorAll('input[name="participated"]').forEach(function (radio) {
			radio.addEventListener('change', function () {
				if (scaleWrap) {
					scaleWrap.style.display = this.value === 'yes' ? '' : 'none';
				}
				sequence = this.value === 'yes' ? PARTICIPANT_STEPS.slice() : NON_PARTICIPANT_STEPS.slice();
				if (currentIdx > sequence.length - 1) {
					currentIdx = sequence.length - 1;
				}
				toggleReachImpactPath();
				showStep(currentIdx);
			});
		});

		// ── Year groups visibility (hide for HE/FE/MAT) ──────────────────────
		var schoolTypeSelect = document.getElementById('survey-school-type');
		var yearGroupsWrap = document.getElementById('survey-year-groups-wrap');
		if (schoolTypeSelect && yearGroupsWrap) {
			schoolTypeSelect.addEventListener('change', function () {
				// Hide year groups for institutions that don't use KS stages
				var hideYearGroups = ['higher_education', 'fe_college', 'mat_trust'].indexOf(this.value) !== -1;
				yearGroupsWrap.style.display = hideYearGroups ? 'none' : '';
				// Clear checkboxes when hidden to avoid submitting nonsensical data
				if (hideYearGroups) {
					yearGroupsWrap.querySelectorAll('input[type="checkbox"]').forEach(function (cb) {
						cb.checked = false;
					});
				}
			});
		}

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

		function updateNavButtons() {
			var isLast = currentIdx === sequence.length - 1;
			nextBtn.hidden   = isLast;
			submitBtn.hidden = !isLast;
		}

		function showStep(idx) {
			document.querySelectorAll('.aiad-survey__step').forEach(function (el) {
				el.hidden = true;
				el.classList.remove('aiad-survey__step--active', 'aiad-survey__step--error');
			});

			var stepId = sequence[idx];
			var el = getStepEl(stepId);
			if (el) {
				el.hidden = false;
				el.classList.add('aiad-survey__step--active');
				el.setAttribute('tabindex', '-1');
				el.focus({ preventScroll: true });
				el.scrollIntoView({ behavior: 'smooth', block: 'start' });
			}

			buildStepper(sequence.length);
			updateStepper(idx);
			progressLbl.textContent = 'Step ' + (idx + 1) + ' of ' + sequence.length;
			backBtn.hidden = idx === 0;
			updateNavButtons();
			hideError();

			if (stepId === 'reach-impact') {
				toggleReachImpactPath();
			}
		}

		// ── Validation ───────────────────────────────────────────────────────
		function validateStepElement(el, stepId, allowHiddenSteps) {
			var radioGroups = {};
			el.querySelectorAll('input[type="radio"][required]').forEach(function (r) {
				if (shouldSkipField(r, allowHiddenSteps)) {
					return;
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

			var firstEmpty = null;
			el.querySelectorAll('input[type="text"][required], input[type="email"][required], textarea[required], select[required]').forEach(function (input) {
				if (shouldSkipField(input, allowHiddenSteps)) {
					return;
				}
				if (!input.value.trim()) {
					firstEmpty = firstEmpty || input;
				}
			});
			if (firstEmpty) {
				el.classList.add('aiad-survey__step--error');
				showError('Please fill in the required fields before continuing.');
				firstEmpty.focus();
				return false;
			}

			if (stepId === 'profile') {
				var participated = el.querySelector('input[name="participated"]:checked');
				if (!participated) {
					el.classList.add('aiad-survey__step--error');
					showError('Please tell us whether your school participated.');
					return false;
				}
			}

			return true;
		}

		function validateStep(idx) {
			var stepId = sequence[idx];
			var el = getStepEl(stepId);
			if (!el) return true;

			if (!validateStepElement(el, stepId, false)) {
				return false;
			}

			if (stepId === 'profile') {
				lockSequenceFromProfile();
				buildStepper(sequence.length);
				updateStepper(idx);
				updateNavButtons();
			}

			return true;
		}

		function validateAllSteps() {
			if (!lockSequenceFromProfile()) {
				currentIdx = 0;
				showStep(0);
				var profileEl = getStepEl('profile');
				if (profileEl) {
					validateStepElement(profileEl, 'profile', true);
				}
				return false;
			}

			for (var i = 0; i < sequence.length; i++) {
				var stepId = sequence[i];
				var el = getStepEl(stepId);
				if (!el) continue;
				if (!validateStepElement(el, stepId, true)) {
					currentIdx = i;
					showStep(i);
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

		form.addEventListener('keydown', function (e) {
			if (e.key !== 'Enter') return;
			if (e.target.tagName === 'TEXTAREA') return;
			e.preventDefault();
			if (currentIdx < sequence.length - 1) {
				nextBtn.click();
			} else if (!submitBtn.hidden) {
				submitSurvey();
			}
		});

		form.addEventListener('submit', function (e) {
			e.preventDefault();
		});

		function submitSurvey() {
			hideError();

			if (currentIdx !== sequence.length - 1) {
				showError('Please complete all steps before submitting.');
				return;
			}

			if (!validateAllSteps()) {
				return;
			}

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
						if (progressEl) progressEl.hidden = true;
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
		}

		submitBtn.addEventListener('click', submitSurvey);

		// Initialise
		document.querySelectorAll('.aiad-survey__step').forEach(function (el) {
			el.hidden = true;
		});
		if (scaleWrap) scaleWrap.style.display = 'none';
		toggleReachImpactPath();
		showStep(0);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
