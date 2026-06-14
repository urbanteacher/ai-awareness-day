/**
 * Intervention hub improvement journeys — action checklists, pathways, ladders and optional support.
 */
(function () {
	'use strict';

	var cfg = window.airbHubInterest || {};
	var i18n = cfg.i18n || {};
	var hub = cfg.config || {};
	var journey = hub.journey || {};
	var SESSION_KEY = 'airb_session_id_v1';
	var SNAPSHOT_KEY = 'airb_results_snapshot_v1';

	window.airbHubInterestState = window.airbHubInterestState || {
		checklistDone: 0,
		checklistTotal: 0,
	};

	function esc(s) {
		if (s == null) return '';
		return String(s)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function storageKey(suffix) {
		return 'airb-hub-journey:' + (hub.pageSlug || 'page') + ':' + (hub.role || 'all') + ':' + suffix;
	}

	function storageGet(key) {
		try {
			return window.localStorage.getItem(key);
		} catch (e) {
			return null;
		}
	}

	function storageSet(key, value) {
		try {
			window.localStorage.setItem(key, value);
		} catch (e) {
			/* ignore */
		}
	}

	function getSessionId() {
		try {
			var params = new URLSearchParams(window.location.search);
			var fromUrl = params.get('airb_session');
			if (fromUrl) return fromUrl;
			return localStorage.getItem(SESSION_KEY) || '';
		} catch (e) {
			return '';
		}
	}

	function getPrefillFromUrl() {
		var hash = window.location.hash || '';
		if (hash.indexOf('airb-hub-interest') !== 0 && hash.indexOf('#airb-hub-interest') !== 0) {
			var params = new URLSearchParams(window.location.search);
			return params.get('prefill') || '';
		}
		var q = hash.split('?')[1] || '';
		try {
			return new URLSearchParams(q).get('prefill') || '';
		} catch (e) {
			return '';
		}
	}

	function loadSnapshot() {
		try {
			var raw = localStorage.getItem(SNAPSHOT_KEY);
			return raw ? JSON.parse(raw) : null;
		} catch (e) {
			return null;
		}
	}

	function mergeSuggested(base, extra) {
		var out = (base || []).slice();
		(extra || []).forEach(function (slug) {
			if (out.indexOf(slug) < 0) out.push(slug);
		});
		return out;
	}

	function parseWeakLabel(raw) {
		var s = String(raw || '');
		var m = s.match(/^(.+?)\s*\((\d+)%/);
		if (m) return { label: m[1].trim(), score: parseInt(m[2], 10) };
		return { label: s, score: null };
	}

	function enrichJourneyContext(jc, context) {
		if (!jc) return null;
		if (!jc.focus_label && context.weak_domains && context.weak_domains.length) {
			var parsed = parseWeakLabel(context.weak_domains[0]);
			jc.focus_label = parsed.label;
			if (jc.focus_score == null) jc.focus_score = parsed.score;
		}
		if (jc.has_benchmark == null && jc.alignment_score != null) {
			jc.has_benchmark = jc.alignment_score > 0;
		}
		if (jc.show_leading == null && jc.alignment_score != null) {
			jc.show_leading = jc.alignment_score >= 75;
		}
		return jc;
	}

	function mergeJourneyContext(jc) {
		if (!jc) return journey;
		var merged = Object.assign({}, journey);
		if (jc.pathway && jc.pathway.length) merged.pathway = jc.pathway;
		merged.context = jc;
		return merged;
	}

	function isActionDone(actionId, context, jc) {
		if (storageGet(storageKey('action:' + actionId)) === '1') return true;
		if (actionId === 'read_guide') return true;
		if (actionId === 'retake_benchmark') return false;
		return false;
	}

	function isPathwayDone(step, context, jc) {
		if (step.type === 'link' && step.id === 'retake') return false;
		if (storageGet(storageKey('path:' + step.id)) === '1') return true;
		if (step.auto === true) return true;
		if (step.auto === 'benchmark' && jc && jc.has_benchmark) return true;
		if (step.id === 'resource') return true;
		if (step.id === 'benchmark' && jc && jc.has_benchmark) return true;
		return false;
	}

	function syncChecklistCounts(actions) {
		var done = 0;
		(actions || []).forEach(function (action) {
			if (action.done) done += 1;
		});
		window.airbHubInterestState.checklistDone = done;
		window.airbHubInterestState.checklistTotal = (actions || []).length;
	}

	function renderNextStep(context, j, jc) {
		var html = '<section class="airb-hub-next-step">';
		html += '<h3 class="airb-hub-section-title">' + esc(j.next_step_heading || 'Your next step') + '</h3>';

		if (jc && jc.has_benchmark) {
			html += '<p class="airb-hub-next-intro">' + esc(j.next_step_intro || 'Based on your benchmark:') + '</p>';
			html += '<div class="airb-hub-focus-score">';
			if (jc.focus_label && jc.focus_score != null) {
				html += '<p class="airb-hub-focus-line"><strong>' + esc(jc.focus_label) + ':</strong> ' + esc(String(jc.focus_score)) + '%</p>';
			}
			html += '<p class="airb-hub-overall-line"><strong>' + esc(hub.labels && hub.labels.score_label ? hub.labels.score_label : 'Readiness') + ':</strong> ';
			html += esc(String(jc.alignment_score)) + '%';
			if (jc.readiness_label) html += ' · ' + esc(jc.readiness_label);
			html += '</p></div>';
		} else {
			html += '<p class="airb__muted">' + esc(i18n.noBenchmark || 'Complete the benchmark to personalise your next steps.') + '</p>';
			html += '<p><a class="airb-hub-btn airb-hub-btn--primary" href="' + esc(j.benchmark_url || hub.benchmarkUrl || '#') + '">' + esc(i18n.startBenchmark || 'Take the benchmark') + '</a></p>';
		}

		if (j.actions && j.actions.length) {
			html += '<h4 class="airb-hub-subtitle">' + esc(j.actions_heading || 'Recommended actions') + '</h4>';
			html += '<ul class="airb-hub-action-list">';
			j.actions.forEach(function (action) {
				var id = action.id || '';
				var done = isActionDone(id, context, jc);
				action.done = done;
				if (action.type === 'link' && action.url) {
					html += '<li class="airb-hub-action-item' + (done ? ' is-done' : '') + '">';
					html += '<span class="airb-hub-action-box" aria-hidden="true">→</span>';
					html += '<a href="' + esc(action.url) + '">' + esc(action.label) + '</a></li>';
					return;
				}
				var inputId = 'airb-hub-action-' + id;
				html += '<li class="airb-hub-action-item' + (done ? ' is-done' : '') + '">';
				html += '<label for="' + esc(inputId) + '">';
				html += '<input type="checkbox" class="airb-hub-action-cb" id="' + esc(inputId) + '" data-action-id="' + esc(id) + '"' + (done ? ' checked disabled' : '') + '>';
				html += '<span class="airb-hub-action-text">' + esc(action.label) + '</span></label></li>';
			});
			html += '</ul>';
			syncChecklistCounts(j.actions);
		}

		html += '</section>';
		return html;
	}

	function renderPathway(j, jc) {
		if (!j.pathway || !j.pathway.length) return '';
		var html = '<section class="airb-hub-pathway">';
		html += '<h3 class="airb-hub-section-title">' + esc(j.journey_title || 'AI Awareness Journey') + '</h3>';
		html += '<ol class="airb-hub-pathway-list">';
		j.pathway.forEach(function (step) {
			var done = isPathwayDone(step, null, jc);
			var cls = done ? ' is-done' : ' is-todo';
			html += '<li class="airb-hub-pathway-step' + cls + '">';
			html += '<span class="airb-hub-pathway-mark" aria-hidden="true">' + (done ? '✓' : '□') + '</span>';
			if (step.type === 'link' && step.url) {
				html += '<a href="' + esc(step.url) + '">' + esc(step.label) + '</a>';
			} else {
				html += '<span>' + esc(step.label) + '</span>';
			}
			html += '</li>';
		});
		html += '</ol></section>';
		return html;
	}

	function renderLeading(j, jc) {
		var block = j.leading_behaviors;
		if (!block || !block.items || !block.items.length) return '';
		if (!jc || !jc.show_leading) return '';
		var html = '<section class="airb-hub-leading">';
		html += '<h3 class="airb-hub-section-title">' + esc(block.title || 'What leading parents typically do') + '</h3>';
		html += '<ul class="airb-hub-leading-list">';
		block.items.forEach(function (item) {
			html += '<li><span class="airb-hub-leading-mark" aria-hidden="true">✓</span> ' + esc(item) + '</li>';
		});
		html += '</ul></section>';
		return html;
	}

	function renderSchool(j) {
		var block = j.school_participation;
		if (!block) return '';
		var html = '<section class="airb-hub-school">';
		html += '<h3 class="airb-hub-section-title">' + esc(block.title || '') + '</h3>';
		if (block.intro) html += '<p>' + esc(block.intro) + '</p>';
		if (block.items && block.items.length) {
			html += '<ul class="airb-hub-school-list">';
			block.items.forEach(function (item) {
				html += '<li><span class="airb-hub-leading-mark" aria-hidden="true">✓</span> ' + esc(item) + '</li>';
			});
			html += '</ul>';
		}
		if (block.cta_label) {
			var prefill = block.cta_prefill || '';
			html += '<p><button type="button" class="airb-hub-btn airb-hub-btn--secondary" data-airb-hub-support-open data-prefill="' + esc(prefill) + '">' + esc(block.cta_label) + '</button></p>';
		}
		html += '</section>';
		return html;
	}

	function renderLadder(j) {
		if (!j.resource_ladder || !j.resource_ladder.length) return '';
		var html = '<section class="airb-hub-ladder">';
		html += '<h3 class="airb-hub-section-title">' + esc(j.ladder_heading || 'Continue learning') + '</h3>';
		html += '<ol class="airb-hub-ladder-list">';
		j.resource_ladder.forEach(function (item, idx) {
			var cls = item.current ? ' is-current' : '';
			html += '<li class="airb-hub-ladder-item' + cls + '">';
			html += '<span class="airb-hub-ladder-num">' + (idx + 1) + '.</span> ';
			if (item.current) {
				html += '<strong>' + esc(item.label) + ' ✓</strong>';
			} else if (item.url) {
				html += '<a href="' + esc(item.url) + '">' + esc(item.label) + '</a>';
			} else {
				html += esc(item.label);
			}
			html += '</li>';
		});
		html += '</ol></section>';
		return html;
	}

	function renderSupportForm(context) {
		if (!hub.options || !hub.options.length) return '';

		var labels = hub.labels || {};
		var fields = hub.fields || {};
		var suggested = mergeSuggested(hub.suggested, context.suggested);
		var role = context.role || hub.role;

		var html = '<details class="airb-hub-support">';
		html += '<summary class="airb-hub-support-summary">' + esc(journey.support_heading || 'Need further support?') + '</summary>';
		html += '<div class="airb-hub-support-body airb__interest">';
		if (journey.support_intro) {
			html += '<p class="airb__muted">' + esc(journey.support_intro) + '</p>';
		}
		html += '<h4 class="airb__interest-heading">' + esc(labels.heading || 'Contact AI Awareness Day') + '</h4>';
		if (labels.intro) html += '<p class="airb__muted airb__interest-intro">' + esc(labels.intro) + '</p>';

		html += '<div class="airb__interest-summary airb__interest-summary--compact">';
		if (labels.context_label && hub.pageTitle) {
			html += '<p><strong>' + esc(labels.context_label) + ':</strong> ' + esc(hub.pageTitle) + '</p>';
		}
		if (context.alignment_score != null && context.alignment_score !== '') {
			html += '<p><strong>' + esc(labels.benchmark_label || '') + ':</strong> ' + esc(String(context.alignment_score)) + '%</p>';
		}
		if (context.weak_domains && context.weak_domains.length) {
			html += '<p><strong>' + esc(labels.weak_label || '') + ':</strong> ' + esc(context.weak_domains.join('; ')) + '</p>';
		}
		html += '</div>';

		html += '<form class="airb__interest-form airb-hub-interest-form" novalidate data-airb-role="' + esc(role) + '" data-airb-submission="' + esc(String(context.submission_id || 0)) + '">';
		html += '<fieldset class="airb__interest-options"><legend class="airb__interest-legend">' + esc(labels.interests || '') + '</legend>';
		hub.options.forEach(function (opt) {
			var checked = suggested.indexOf(opt.slug) >= 0 ? ' checked' : '';
			var inputId = 'airb-hub-interest-' + opt.slug;
			html += '<label class="airb__interest-option" for="' + esc(inputId) + '">';
			html += '<input type="checkbox" id="' + esc(inputId) + '" name="interests[]" value="' + esc(opt.slug) + '"' + checked + '>';
			html += '<span class="airb__interest-option-text"><strong>' + esc(opt.label) + '</strong>';
			if (opt.description) html += '<span class="airb__interest-option-desc">' + esc(opt.description) + '</span>';
			html += '</span></label>';
		});
		html += '</fieldset>';

		html += '<div class="airb__interest-fields">';
		if (fields.show_name) {
			html += '<label class="airb__field"><span class="airb__label">' + esc(labels.name || '') + '</span><input class="airb__input" type="text" name="interest_name" autocomplete="name"></label>';
		}
		if (fields.show_email) {
			html += '<label class="airb__field"><span class="airb__label">' + esc(labels.email || '') + ' *</span>';
			html += '<input class="airb__input" type="email" name="interest_email" required autocomplete="email" value="' + esc(context.email || '') + '">';
			if (labels.email_hint) html += '<span class="airb__field-hint airb__muted">' + esc(labels.email_hint) + '</span>';
			html += '</label>';
		}
		if (fields.show_school) {
			html += '<label class="airb__field"><span class="airb__label">' + esc(labels.school || '') + '</span><input class="airb__input" type="text" name="interest_school" autocomplete="organization" value="' + esc(context.school || '') + '"></label>';
		}
		if (fields.show_child_school) {
			html += '<label class="airb__field"><span class="airb__label">' + esc(labels.child_school || '') + '</span><input class="airb__input" type="text" name="interest_child_school" autocomplete="organization"></label>';
		}
		html += '<label class="airb__field"><span class="airb__label">' + esc(labels.message || '') + '</span><textarea class="airb__input airb__textarea" name="interest_message" rows="3"></textarea></label>';
		html += '</div>';

		html += '<p class="airb__interest-status airb-hub-interest-status" role="status" aria-live="polite" hidden></p>';
		html += '<button type="submit" class="airb__btn airb__btn--primary airb__interest-submit">' + esc(labels.submit || 'Send message') + '</button>';
		html += '</form></div></details>';
		return html;
	}

	function renderJourney(mount, context) {
		var jc = enrichJourneyContext(context.journey_context || null, context);
		var j = mergeJourneyContext(jc);
		var html = '';

		html += renderNextStep(context, j, jc);
		html += renderPathway(j, jc);
		html += renderLeading(j, jc);
		html += renderSchool(j);
		html += renderLadder(j);
		html += renderSupportForm(context);

		mount.innerHTML = html;
		bindJourneyInteractions(mount, context, j, jc);
		bindPrefillLinks();
		applyPrefill(getPrefillFromUrl());
	}

	function bindJourneyInteractions(mount, context, j, jc) {
		mount.querySelectorAll('.airb-hub-action-cb').forEach(function (cb) {
			cb.addEventListener('change', function () {
				var id = cb.getAttribute('data-action-id');
				if (!id) return;
				storageSet(storageKey('action:' + id), cb.checked ? '1' : '0');
				var li = cb.closest('.airb-hub-action-item');
				if (li) li.classList.toggle('is-done', cb.checked);
				if (j.actions) syncChecklistCounts(j.actions.map(function (a) {
					return { done: isActionDone(a.id, context, jc) || (a.id === id && cb.checked) };
				}));
			});
		});

		mount.querySelectorAll('[data-airb-hub-support-open]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var prefill = btn.getAttribute('data-prefill') || '';
				var details = mount.querySelector('.airb-hub-support');
				if (details) details.open = true;
				if (typeof window.airbHubInterestScroll === 'function') {
					window.airbHubInterestScroll(prefill);
				}
			});
		});

		var form = mount.querySelector('.airb-hub-interest-form');
		if (form) bindForm(form, context);
	}

	function applyPrefill(prefill) {
		if (!prefill) return;
		var section = document.getElementById('airb-hub-interest');
		if (!section) return;
		var details = section.querySelector('.airb-hub-support');
		if (details) details.open = true;
		section.querySelectorAll('input[name="interests[]"]').forEach(function (input) {
			if (input.value === prefill) input.checked = true;
		});
	}

	function bindPrefillLinks() {
		document.querySelectorAll('a[href^="#airb-hub-interest"]').forEach(function (link) {
			link.addEventListener('click', function (e) {
				e.preventDefault();
				var href = link.getAttribute('href') || '';
				var prefill = '';
				if (href.indexOf('?') >= 0) {
					try {
						prefill = new URLSearchParams(href.split('?')[1]).get('prefill') || '';
					} catch (err) { /* ignore */ }
				}
				var section = document.getElementById('airb-hub-interest');
				if (section) {
					var details = section.querySelector('.airb-hub-support');
					if (details) details.open = true;
					section.scrollIntoView({ behavior: 'smooth', block: 'start' });
				}
				applyPrefill(prefill);
			});
		});
	}

	function bindForm(form, context) {
		if (!form) return;
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			submitForm(form, context);
		});
	}

	function submitForm(form, context) {
		var statusEl = form.parentElement.querySelector('.airb-hub-interest-status');
		var emailInput = form.querySelector('input[name="interest_email"]');
		var email = emailInput ? emailInput.value.trim() : '';
		if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
			showStatus(statusEl, i18n.emailInvalid || 'Please enter a valid email address.', true);
			if (emailInput) emailInput.focus();
			return;
		}

		var interests = [];
		form.querySelectorAll('input[name="interests[]"]:checked').forEach(function (input) {
			interests.push(input.value);
		});
		if (!interests.length) {
			showStatus(statusEl, i18n.interestRequired || 'Please select at least one option.', true);
			return;
		}

		var submitBtn = form.querySelector('.airb__interest-submit');
		if (submitBtn) {
			submitBtn.disabled = true;
			submitBtn.textContent = i18n.sending || 'Sending…';
		}

		var role = form.getAttribute('data-airb-role') || hub.role;
		var body = new FormData();
		body.append('action', 'airb_submit_interest');
		body.append('nonce', cfg.nonce || '');
		body.append('source', 'hub');
		body.append('role', role);
		body.append('session_id', getSessionId());
		body.append('submission_id', form.getAttribute('data-airb-submission') || String(context.submission_id || 0));
		body.append('alignment_score', String(context.alignment_score != null ? context.alignment_score : 0));
		body.append('name', (form.querySelector('input[name="interest_name"]') || {}).value || '');
		body.append('email', email);
		body.append('school', (form.querySelector('input[name="interest_school"]') || {}).value || '');
		body.append('child_school', (form.querySelector('input[name="interest_child_school"]') || {}).value || '');
		body.append('message', (form.querySelector('textarea[name="interest_message"]') || {}).value || '');
		body.append('hub_page', hub.pageSlug || '');
		body.append('hub_title', hub.pageTitle || '');
		body.append('hub_ref', hub.ref || '');
		body.append('hub_url', window.location.href);
		body.append('checklist_done', String(window.airbHubInterestState.checklistDone || 0));
		body.append('checklist_total', String(window.airbHubInterestState.checklistTotal || 0));
		body.append('interests', JSON.stringify(interests));
		body.append('weak_domains', JSON.stringify(context.weak_domains || []));

		fetch(cfg.ajaxurl, { method: 'POST', body: body, credentials: 'same-origin' })
			.then(function (res) { return res.json(); })
			.then(function (json) {
				if (submitBtn) {
					submitBtn.disabled = false;
					submitBtn.textContent = (hub.labels && hub.labels.submit) || 'Send';
				}
				if (!json || !json.success) {
					showStatus(statusEl, (json && json.data && json.data.message) || i18n.error || 'Error', true);
					return;
				}
				showStatus(statusEl, (json.data && json.data.message) || 'Thank you.', false);
			})
			.catch(function () {
				if (submitBtn) {
					submitBtn.disabled = false;
					submitBtn.textContent = (hub.labels && hub.labels.submit) || 'Send';
				}
				showStatus(statusEl, i18n.error || 'Error', true);
			});
	}

	function showStatus(el, msg, isError) {
		if (!el) return;
		el.hidden = false;
		el.className = 'airb__interest-status airb-hub-interest-status' + (isError ? ' airb__interest-status--error' : ' airb__interest-status--success');
		el.textContent = msg;
	}

	function fetchContext(callback) {
		var snapshot = loadSnapshot();
		var context = {
			submission_id: snapshot ? snapshot.submissionId : 0,
			alignment_score: snapshot ? snapshot.alignment_score : null,
			weak_domains: snapshot ? snapshot.weak_domains : [],
			suggested: hub.suggested || [],
			email: snapshot ? snapshot.email : '',
			school: snapshot ? snapshot.school : '',
			role: hub.role,
			journey_context: null,
		};

		var sessionId = getSessionId();
		if (!sessionId || !cfg.ajaxurl) {
			if (context.alignment_score != null) {
				context.journey_context = {
					has_benchmark: true,
					alignment_score: context.alignment_score,
					focus_label: context.weak_domains && context.weak_domains.length ? context.weak_domains[0] : '',
					show_leading: context.alignment_score >= 75,
				};
			}
			callback(context);
			return;
		}

		var body = new FormData();
		body.append('action', 'airb_get_hub_context');
		body.append('nonce', cfg.nonce || '');
		body.append('session_id', sessionId);
		body.append('role', hub.role || '');
		body.append('hub_page', hub.pageSlug || '');
		body.append('hub_ref', hub.ref || '');

		fetch(cfg.ajaxurl, { method: 'POST', body: body, credentials: 'same-origin' })
			.then(function (res) { return res.json(); })
			.then(function (json) {
				if (json && json.success && json.data && json.data.submission) {
					var sub = json.data.submission;
					context.submission_id = sub.id || context.submission_id;
					context.alignment_score = sub.alignment_score != null ? sub.alignment_score : context.alignment_score;
					context.weak_domains = sub.weak_domains && sub.weak_domains.length ? sub.weak_domains : context.weak_domains;
					context.suggested = mergeSuggested(context.suggested, sub.suggested);
					context.email = sub.email || context.email;
					context.school = sub.school || context.school;
					context.role = sub.role || context.role;
					context.journey_context = sub.journey_context || null;
				}
				callback(context);
			})
			.catch(function () {
				callback(context);
			});
	}

	function init() {
		var mount = document.querySelector('[data-airb-hub-journey-mount]');
		if (!mount || !hub.pageSlug) return;

		mount.innerHTML = '<p class="airb__muted">' + esc(i18n.loading || 'Loading…') + '</p>';
		fetchContext(function (context) {
			renderJourney(mount, context);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	window.airbHubInterestScroll = function (prefill) {
		var section = document.getElementById('airb-hub-interest');
		if (section) {
			var details = section.querySelector('.airb-hub-support');
			if (details) details.open = true;
			section.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}
		applyPrefill(prefill || '');
	};
})();
