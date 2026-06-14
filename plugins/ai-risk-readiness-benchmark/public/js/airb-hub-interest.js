/**
 * Contextual interest form on intervention hub pages.
 */
(function () {
	'use strict';

	var cfg = window.airbHubInterest || {};
	var i18n = cfg.i18n || {};
	var hub = cfg.config || {};
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

	function renderForm(mount, context) {
		if (!mount || !hub.options || !hub.options.length) return;

		var labels = hub.labels || {};
		var fields = hub.fields || {};
		var suggested = mergeSuggested(hub.suggested, context.suggested);
		var weak = context.weak_domains || [];
		var score = context.alignment_score;
		var submissionId = context.submission_id || 0;
		var email = context.email || '';
		var school = context.school || '';
		var role = context.role || hub.role;

		var html = '';
		html += '<h3 class="airb__interest-heading">' + esc(labels.heading || '') + '</h3>';
		if (labels.intro) html += '<p class="airb__muted airb__interest-intro">' + esc(labels.intro) + '</p>';

		html += '<div class="airb__interest-summary">';
		if (labels.context_label && hub.pageTitle) {
			html += '<p><strong>' + esc(labels.context_label) + ':</strong> ' + esc(hub.pageTitle) + '</p>';
		}
		if (hub.ref) {
			html += '<p class="airb__muted airb-hub-interest-ref">' + esc(hub.ref.replace(/_/g, ' ')) + '</p>';
		}
		if (score != null && score !== '') {
			html += '<p><strong>' + esc(labels.benchmark_label || labels.score_label || '') + ':</strong> ' + esc(String(score)) + '%</p>';
		}
		if (weak.length) {
			html += '<p><strong>' + esc(labels.weak_label || '') + ':</strong> ' + esc(weak.join('; ')) + '</p>';
		}
		html += '</div>';

		html += '<form class="airb__interest-form airb-hub-interest-form" novalidate data-airb-role="' + esc(role) + '" data-airb-submission="' + esc(String(submissionId)) + '">';
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
			html += '<input class="airb__input" type="email" name="interest_email" required autocomplete="email" value="' + esc(email) + '">';
			if (labels.email_hint) html += '<span class="airb__field-hint airb__muted">' + esc(labels.email_hint) + '</span>';
			html += '</label>';
		}
		if (fields.show_school) {
			html += '<label class="airb__field"><span class="airb__label">' + esc(labels.school || '') + '</span><input class="airb__input" type="text" name="interest_school" autocomplete="organization" value="' + esc(school) + '"></label>';
		}
		if (fields.show_child_school) {
			html += '<label class="airb__field"><span class="airb__label">' + esc(labels.child_school || '') + '</span><input class="airb__input" type="text" name="interest_child_school" autocomplete="organization"></label>';
		}
		html += '<label class="airb__field"><span class="airb__label">' + esc(labels.message || '') + '</span><textarea class="airb__input airb__textarea" name="interest_message" rows="3"></textarea></label>';
		html += '</div>';

		html += '<p class="airb__interest-status airb-hub-interest-status" role="status" aria-live="polite" hidden></p>';
		html += '<button type="submit" class="airb__btn airb__btn--primary airb__interest-submit">' + esc(labels.submit || 'Send') + '</button>';
		html += '</form>';

		mount.innerHTML = html;
		bindForm(mount.querySelector('form'), context);
		bindPrefillLinks();
		applyPrefill(getPrefillFromUrl());
	}

	function applyPrefill(prefill) {
		if (!prefill) return;
		var section = document.getElementById('airb-hub-interest');
		if (!section) return;
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
		};

		var sessionId = getSessionId();
		if (!sessionId || !cfg.ajaxurl) {
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
				}
				callback(context);
			})
			.catch(function () {
				callback(context);
			});
	}

	function init() {
		var mount = document.querySelector('[data-airb-hub-interest-mount]');
		if (!mount || !hub.pageSlug) return;

		mount.innerHTML = '<p class="airb__muted">' + esc(i18n.loading || 'Loading…') + '</p>';
		fetchContext(function (context) {
			renderForm(mount, context);
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
			section.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}
		applyPrefill(prefill || '');
	};
})();
