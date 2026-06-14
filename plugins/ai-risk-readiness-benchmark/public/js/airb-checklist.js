/**
 * AIRB interactive checklists.
 *
 * Progressively enhances server-rendered `.airb-checklist` blocks into
 * tickable checklists with localStorage persistence, a progress bar,
 * an optional RAG status, and "email my checklist" actions that channel
 * the user toward their SLT and the AI Awareness team.
 */
(function () {
	'use strict';

	var cfg = window.airbHubChecklist || {};
	var i18n = cfg.i18n || {};
	var contactEmail = cfg.contactEmail || '';

	function t(key, fallback) {
		return typeof i18n[key] === 'string' && i18n[key] !== '' ? i18n[key] : fallback;
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
			/* storage unavailable (private mode) — degrade silently */
		}
	}

	function el(tag, className, text) {
		var node = document.createElement(tag);
		if (className) node.className = className;
		if (text != null) node.textContent = text;
		return node;
	}

	function ready(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	}

	ready(function () {
		var boxes = document.querySelectorAll('.airb-checklist');
		Array.prototype.forEach.call(boxes, function (box, idx) {
			enhance(box, idx);
		});
	});

	function enhance(box, idx) {
		var ns = 'airb-checklist:' + location.pathname + ':' + idx;
		var showRag = box.classList.contains('airb-checklist--rag');
		var checkboxes = [];

		var items = box.querySelectorAll('.airb-checklist__item');
		Array.prototype.forEach.call(items, function (li, i) {
			var text = li.textContent.replace(/^\s*[☐☑✓]\s*/, '').trim();
			li.textContent = '';

			var label = el('label', 'airb-checklist__label');
			var cb = el('input', 'airb-checklist__cb');
			cb.type = 'checkbox';

			var key = ns + ':' + i;
			if (storageGet(key) === '1') {
				cb.checked = true;
				li.classList.add('is-checked');
			}

			cb.addEventListener('change', function () {
				storageSet(key, cb.checked ? '1' : '0');
				li.classList.toggle('is-checked', cb.checked);
				update();
			});

			label.appendChild(cb);
			label.appendChild(el('span', 'airb-checklist__text', text));
			li.appendChild(label);

			checkboxes.push({ cb: cb, text: text });
		});

		if (!checkboxes.length) {
			return;
		}

		// --- Controls ----------------------------------------------------
		var controls = el('div', 'airb-checklist__controls');

		var progressWrap = el('div', 'airb-checklist__progress');
		var bar = el('div', 'airb-checklist__bar');
		var fill = el('span', 'airb-checklist__bar-fill');
		bar.appendChild(fill);
		var count = el('p', 'airb-checklist__count');
		progressWrap.appendChild(count);
		progressWrap.appendChild(bar);

		var rag = null;
		if (showRag) {
			rag = el('span', 'airb-checklist__rag');
			progressWrap.appendChild(rag);
		}
		controls.appendChild(progressWrap);

		var actions = el('div', 'airb-checklist__actions');

		var sltBtn = el('a', 'airb-checklist__btn airb-checklist__btn--primary', t('emailSlt', 'Email my checklist to my SLT'));
		sltBtn.setAttribute('role', 'button');
		actions.appendChild(sltBtn);

		if (contactEmail) {
			var teamBtn = el('button', 'airb-checklist__btn airb-checklist__btn--team', t('emailTeam', 'Request support from AI Awareness Day'));
			teamBtn.type = 'button';
			teamBtn.addEventListener('click', function () {
				var c = counts();
				window.airbHubInterestState.checklistDone = c.done;
				window.airbHubInterestState.checklistTotal = c.total;
				if (typeof window.airbHubInterestScroll === 'function') {
					window.airbHubInterestScroll('');
				}
				setTimeout(function () {
					var msg = document.querySelector('.airb-hub-interest-form textarea[name="interest_message"]');
					if (!msg || msg.value) return;
					var pct = c.total ? Math.round((c.done / c.total) * 100) : 0;
					var heading = (document.querySelector('h1') && document.querySelector('h1').textContent.trim()) || document.title;
					msg.value = t('emailIntro', 'Here is my progress against this AI readiness checklist:') + '\n\n'
						+ heading + ': ' + c.done + '/' + c.total + ' (' + pct + '%)';
				}, 400);
			});
			actions.appendChild(teamBtn);
		}

		var resetBtn = el('button', 'airb-checklist__btn airb-checklist__btn--ghost', t('reset', 'Reset'));
		resetBtn.type = 'button';
		actions.appendChild(resetBtn);

		controls.appendChild(actions);
		box.appendChild(controls);

		resetBtn.addEventListener('click', function () {
			checkboxes.forEach(function (entry, i) {
				entry.cb.checked = false;
				storageSet(ns + ':' + i, '0');
			});
			Array.prototype.forEach.call(items, function (li) {
				li.classList.remove('is-checked');
			});
			update();
		});

		function counts() {
			var done = 0;
			checkboxes.forEach(function (entry) {
				if (entry.cb.checked) done += 1;
			});
			return { done: done, total: checkboxes.length };
		}

		function ragFor(pct) {
			if (pct >= 80) return { key: 'green', label: t('ragGreen', 'On track') };
			if (pct >= 40) return { key: 'amber', label: t('ragAmber', 'Some gaps remain') };
			return { key: 'red', label: t('ragRed', 'Significant work required') };
		}

		function buildMailto(to) {
			var c = counts();
			var pct = c.total ? Math.round((c.done / c.total) * 100) : 0;
			var heading = (document.querySelector('h1') && document.querySelector('h1').textContent.trim()) || document.title;
			var subject = t('emailSubject', 'AI readiness checklist') + ' — ' + heading;
			var lines = [t('emailIntro', 'Here is my progress against this AI readiness checklist:'), ''];
			checkboxes.forEach(function (entry) {
				lines.push((entry.cb.checked ? '[x] ' : '[ ] ') + entry.text);
			});
			lines.push('');
			lines.push(t('emailProgress', 'Completed') + ': ' + c.done + '/' + c.total + ' (' + pct + '%)');
			if (showRag) {
				lines.push(t('emailStatus', 'Status') + ': ' + ragFor(pct).label);
			}
			lines.push('', location.href);
			if (to === contactEmail) {
				lines.push('', t('emailTeamClosing', 'Please contact me for guidance and support on the gaps above, and how AI Awareness Day can help our school.'));
			}
			return 'mailto:' + (to || '') + '?subject=' + encodeURIComponent(subject) + '&body=' + encodeURIComponent(lines.join('\n'));
		}

		function update() {
			var c = counts();
			var pct = c.total ? Math.round((c.done / c.total) * 100) : 0;
			fill.style.width = pct + '%';
			count.textContent = t('progressLabel', 'Completed') + ': ' + c.done + ' / ' + c.total + ' (' + pct + '%)';
			if (rag) {
				var r = ragFor(pct);
				rag.textContent = r.label;
				rag.className = 'airb-checklist__rag is-' + r.key;
			}
			sltBtn.href = buildMailto('');
			if (typeof teamBtn !== 'undefined' && teamBtn) {
				/* teamBtn uses hub interest form — counts synced on click */
			}
			window.airbHubInterestState.checklistDone = c.done;
			window.airbHubInterestState.checklistTotal = c.total;
		}

		update();
	}
})();
