/**
 * airb-audit.js
 *
 * Audit helpers + gradual migration module (V1 flow API).
 * Live question flow remains in airb-front.js until Step 4 wiring is complete.
 *
 * Depends on: airb-core.js
 * Exposes: AIRB.Audit
 */
(function () {
	'use strict';

	window.AIRB = window.AIRB || {};

	// -------------------------------------------------------------------------
	// Helpers used by airb-front.js today (profile phase + conditional questions)
	// -------------------------------------------------------------------------

	function questionApplies(q, answers, profilePhase) {
		answers = answers || {};
		var unless = q.show_unless_answer || {};
		var depQid;
		for (depQid in unless) {
			if (!Object.prototype.hasOwnProperty.call(unless, depQid)) {
				continue;
			}
			var depVal = answers[depQid] !== undefined ? String(answers[depQid]) : '';
			if (depVal && unless[depQid].indexOf(depVal) >= 0) {
				return false;
			}
		}
		var phases = q.show_for_phases || [];
		if (!phases.length) {
			return true;
		}
		var phase = typeof profilePhase === 'function' ? profilePhase() : profilePhase;
		if (!phase && answers && answers._school_phase) {
			phase = answers._school_phase;
		}
		if (!phase) {
			return false;
		}
		return phases.indexOf(phase) >= 0;
	}

	function questionsForRole(role, cfg, answers, profilePhase) {
		return (cfg.questions || []).filter(function (q) {
			return q.role === role && questionApplies(q, answers, profilePhase);
		});
	}

	function sectionsForRole(role, cfg, answers, profilePhase) {
		var sections = [];
		var index = {};
		questionsForRole(role, cfg, answers, profilePhase).forEach(function (q) {
			var key = q.section || 'General';
			if (!index[key]) {
				index[key] = { name: key, domain: q.domain, questions: [] };
				sections.push(index[key]);
			}
			index[key].questions.push(q);
		});
		return sections;
	}

	// -------------------------------------------------------------------------
	// V1 audit flow (bridged via window._airbRenderContact / _airbRenderRole)
	// -------------------------------------------------------------------------

	var flowState = {
		phase: 'role',
		role: null,
		sections: [],
		questions: [],
		currentSection: 0,
		answers: {},
		profile: {},
		results: null,
		submitting: false,
		error: null,
	};

	function flowSectionsForRole(role) {
		var cfg = (window.airbBenchmark && airbBenchmark.config) || {};
		var profilePhase = typeof window._airbProfilePhase === 'function' ? window._airbProfilePhase : function () { return ''; };
		return sectionsForRole(role, cfg, flowState.answers, profilePhase);
	}

	function flowQuestionsForRole(role) {
		var cfg = (window.airbBenchmark && airbBenchmark.config) || {};
		var profilePhase = typeof window._airbProfilePhase === 'function' ? window._airbProfilePhase : function () { return ''; };
		return questionsForRole(role, cfg, flowState.answers, profilePhase);
	}

	function selectRole(role) {
		flowState.role = role;
		flowState.sections = flowSectionsForRole(role);
		flowState.questions = flowQuestionsForRole(role);
		flowState.currentSection = 0;
		flowState.answers = {};
		flowState.phase = 'audit';
		renderAudit();
	}

	function renderAudit() {
		var screen = document.getElementById('airb-screen-audit');
		if (!screen) {
			return;
		}
		var sec = flowState.sections[flowState.currentSection];
		if (!sec) {
			return;
		}
		var total = flowState.sections.length;
		var current = flowState.currentSection + 1;
		var escFn = AIRB.esc || function (s) { return String(s); };
		var html = '';
		html += progressStepperHtml(current, total, flowState.sections);
		html += '<div class="airb__section-header">';
		html += '<h2 class="airb__section-header__title">' + escFn(sec.name || sec.label || '') + '</h2>';
		html += '<p class="airb__section-header__count">Question ' + current + ' of ' + total + '</p>';
		html += '</div>';
		html += '<div class="airb__questions">';
		(sec.questions || []).forEach(function (q) {
			html += questionHtml(q);
		});
		html += '</div>';
		screen.innerHTML = html;
		restoreAnswers(sec.questions || []);
		bindQuestionEvents(sec.questions || []);
		syncNavPlacement();
	}

	function questionHtml(q) {
		var escFn = AIRB.esc || function (s) { return String(s); };
		var html = '<div class="airb__question" data-question-id="' + escFn(q.id) + '">';
		html += '<p class="airb__question__text">' + escFn(q.displayText || q.text) + '</p>';
		if (q.type === 'slider') {
			html += sliderHtml(q);
		} else if (q.type === 'select') {
			html += selectHtml(q);
		} else {
			html += radioHtml(q);
		}
		html += '</div>';
		return html;
	}

	function radioHtml(q) {
		var escFn = AIRB.esc || function (s) { return String(s); };
		var html = '<div class="airb__radio-group" role="group">';
		(q.options || []).forEach(function (opt) {
			var id = 'opt-' + escFn(q.id) + '-' + escFn(opt.value);
			html += '<label class="airb__radio-option" for="' + id + '">';
			html += '<input type="radio" id="' + id + '" name="' + escFn(q.id) + '" value="' + escFn(opt.value) + '">';
			html += '<span class="airb__radio-option__label">' + escFn(opt.label) + '</span>';
			html += '</label>';
		});
		return html + '</div>';
	}

	function selectHtml(q) {
		var escFn = AIRB.esc || function (s) { return String(s); };
		var html = '<select class="airb__select" name="' + escFn(q.id) + '"><option value="">' + escFn((airbBenchmark && airbBenchmark.i18n && airbBenchmark.i18n.required) || 'Required') + '</option>';
		(q.options || []).forEach(function (opt) {
			html += '<option value="' + escFn(opt.value) + '">' + escFn(opt.label) + '</option>';
		});
		return html + '</select>';
	}

	function sliderHtml(q) {
		var escFn = AIRB.esc || function (s) { return String(s); };
		var min = q.min !== undefined ? q.min : 0;
		var max = q.max !== undefined ? q.max : 100;
		var step = q.step || 1;
		var value = flowState.answers[q.id] !== undefined ? flowState.answers[q.id] : Math.round((min + max) / 2);
		return '<div class="airb__slider-wrap">'
			+ '<input type="range" class="airb__slider" id="slider-' + escFn(q.id) + '" name="' + escFn(q.id) + '" '
			+ 'min="' + min + '" max="' + max + '" step="' + step + '" value="' + value + '">'
			+ '<output class="airb__slider__output" for="slider-' + escFn(q.id) + '">' + Math.round(value) + '</output>'
			+ '</div>';
	}

	function progressStepperHtml(current, total, sections) {
		var escFn = AIRB.esc || function (s) { return String(s); };
		var html = '<div class="airb__progress-stepper" role="progressbar" aria-valuenow="' + current + '" aria-valuemin="1" aria-valuemax="' + total + '">';
		sections.forEach(function (sec, i) {
			var done = i < current - 1;
			var active = i === current - 1;
			var cls = 'airb__progress-step'
				+ (done ? ' airb__progress-step--done' : '')
				+ (active ? ' airb__progress-step--active' : '');
			html += '<div class="' + cls + '" aria-label="' + escFn(sec.name || sec.label || '') + '"></div>';
		});
		html += '<div class="airb__progress-stepper__track" style="width:' + Math.round((current / total) * 100) + '%"></div>';
		return html + '</div>';
	}

	function nextSection() {
		if (!validateCurrentSection()) {
			showValidationError();
			return;
		}
		saveCurrentAnswers();
		if (flowState.currentSection < flowState.sections.length - 1) {
			flowState.currentSection++;
			renderAudit();
		} else {
			flowState.phase = 'contact';
			renderContact();
		}
		scrollToTop();
	}

	function prevSection() {
		saveCurrentAnswers();
		if (flowState.currentSection > 0) {
			flowState.currentSection--;
			renderAudit();
		} else {
			flowState.phase = 'role';
			renderRole();
		}
		scrollToTop();
	}

	function validateCurrentSection() {
		var sec = flowState.sections[flowState.currentSection];
		if (!sec) {
			return true;
		}
		return (sec.questions || []).every(function (q) {
			return flowState.answers[q.id] !== undefined && flowState.answers[q.id] !== '';
		});
	}

	function showValidationError() {
		var toast = document.getElementById('airb-error');
		if (toast) {
			toast.textContent = (airbBenchmark && airbBenchmark.i18n && airbBenchmark.i18n.answerAll)
				|| 'Please answer all questions before continuing.';
			toast.hidden = false;
			window.setTimeout(function () { toast.hidden = true; }, 3000);
		}
	}

	function saveCurrentAnswers() {
		var sec = flowState.sections[flowState.currentSection];
		if (!sec) {
			return;
		}
		(sec.questions || []).forEach(function (q) {
			var el;
			if (q.type === 'slider') {
				el = document.getElementById('slider-' + q.id);
				if (el) {
					flowState.answers[q.id] = Number(el.value);
				}
				return;
			}
			if (q.type === 'select') {
				el = document.querySelector('select[name="' + q.id + '"]');
				if (el && el.value) {
					flowState.answers[q.id] = el.value;
				}
				return;
			}
			el = document.querySelector('input[name="' + q.id + '"]:checked');
			if (el) {
				flowState.answers[q.id] = el.value;
			}
		});
	}

	function restoreAnswers(questions) {
		questions.forEach(function (q) {
			var saved = flowState.answers[q.id];
			if (saved === undefined) {
				return;
			}
			if (q.type === 'slider') {
				var slider = document.getElementById('slider-' + q.id);
				if (slider) {
					slider.value = saved;
					var out = slider.parentNode && slider.parentNode.querySelector('.airb__slider__output');
					if (out) {
						out.textContent = Math.round(saved);
					}
				}
				return;
			}
			if (q.type === 'select') {
				var sel = document.querySelector('select[name="' + q.id + '"]');
				if (sel) {
					sel.value = saved;
				}
				return;
			}
			var radio = document.querySelector('input[name="' + q.id + '"][value="' + saved + '"]');
			if (radio) {
				radio.checked = true;
			}
		});
	}

	function collectAnswers() {
		saveCurrentAnswers();
		return Object.keys(flowState.answers).map(function (id) {
			return { id: id, value: flowState.answers[id] };
		});
	}

	function bindQuestionEvents(questions) {
		questions.forEach(function (q) {
			if (q.type === 'slider') {
				var slider = document.getElementById('slider-' + q.id);
				if (!slider) {
					return;
				}
				slider.addEventListener('input', function () {
					flowState.answers[q.id] = Number(slider.value);
					var out = slider.parentNode && slider.parentNode.querySelector('.airb__slider__output');
					if (out) {
						out.textContent = Math.round(slider.value);
					}
				});
				return;
			}
			var inputs = document.querySelectorAll('input[name="' + q.id + '"], select[name="' + q.id + '"]');
			inputs.forEach(function (input) {
				input.addEventListener('change', function () {
					flowState.answers[q.id] = input.value;
				});
			});
		});
	}

	function syncNavPlacement() {
		var nav = document.getElementById('airb-nav');
		var screen = document.getElementById('airb-screen-' + flowState.phase);
		if (!nav || !screen) {
			return;
		}
		if (window.innerWidth <= 768) {
			screen.appendChild(nav);
		} else {
			var slot = document.getElementById('airb-nav-slot');
			if (slot && nav.parentNode !== slot) {
				slot.appendChild(nav);
			}
		}
	}

	var LS_KEY = 'airb_completed_roles_v1';

	function completedRoles() {
		try {
			return JSON.parse(localStorage.getItem(LS_KEY) || '[]');
		} catch (e) {
			return [];
		}
	}

	function markRoleComplete(role) {
		try {
			var done = completedRoles();
			if (done.indexOf(role) === -1) {
				done.push(role);
				localStorage.setItem(LS_KEY, JSON.stringify(done));
			}
		} catch (e) { /* localStorage unavailable */ }
	}

	function scrollToTop() {
		var benchmark = document.getElementById('airb-benchmark');
		if (benchmark) {
			benchmark.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}
	}

	function renderContact() {
		if (typeof window._airbRenderContact === 'function') {
			window._airbRenderContact();
		}
	}

	function renderRole() {
		if (typeof window._airbRenderRole === 'function') {
			window._airbRenderRole();
		}
	}

	AIRB.Audit = AIRB.Audit || {};
	Object.assign(AIRB.Audit, {
		questionApplies: questionApplies,
		questionsForRole: questionsForRole,
		sectionsForRole: sectionsForRole,
		state: flowState,
		flowState: flowState,
		selectRole: selectRole,
		renderAudit: renderAudit,
		nextSection: nextSection,
		prevSection: prevSection,
		syncNavPlacement: syncNavPlacement,
		collectAnswers: collectAnswers,
		completedRoles: completedRoles,
		markRoleComplete: markRoleComplete,
	});
}());
