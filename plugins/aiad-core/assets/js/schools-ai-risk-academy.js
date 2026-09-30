/**
 * Schools AI Risk Academy — [aiad_risk_academy]
 * Instance-safe: initialises each .sara root independently.
 */
(function () {
	'use strict';

	var EXPOSURE_MAX = 11;
	var RELIANCE_MAX = 18;

	function bandExposure(pct) {
		if (pct <= 32) {
			return { key: 'low', label: 'Low', action: 'Keep', desc: 'A genuine check on understanding; little room for unmanaged use.' };
		}
		if (pct <= 66) {
			return { key: 'mod', label: 'Moderate', action: 'Decide', desc: 'Make AI use intentional, or add supervision/filtering — then write the decision into policy.' };
		}
		return { key: 'high', label: 'High', action: 'Redesign', desc: 'Move it in-person, supervise, filter, or teach permitted use. For graded work, check JCQ rules.' };
	}

	function bandReliance(pct) {
		if (pct <= 33) {
			return { key: 'healthy', label: 'Healthy use', action: 'Keep building', desc: 'You are using AI as a support tool, not a substitute for your own thinking.' };
		}
		if (pct <= 66) {
			return { key: 'leaning', label: 'Leaning on it', action: 'Recalibrate', desc: 'Try doing a first attempt without AI, then use it to check or explain — not to produce the work.' };
		}
		return { key: 'over', label: 'Over-reliant', action: 'Step back', desc: 'When AI produces the work, the grade may arrive but the learning does not. Start with your own attempt.' };
	}

	function qs(root, sel) {
		return root.querySelector(sel);
	}

	function qsa(root, sel) {
		return Array.prototype.slice.call(root.querySelectorAll(sel));
	}

	function initRoot(root) {
		if (root.getAttribute('data-sara-init') === '1') {
			return;
		}
		root.setAttribute('data-sara-init', '1');

		var prefix = root.getAttribute('data-sara-id');
		if (!prefix) {
			prefix = 'sara-' + Math.random().toString(36).slice(2, 9);
			root.setAttribute('data-sara-id', prefix);
		}

		qsa(root, '[name]').forEach(function (el) {
			var name = el.getAttribute('name');
			if (name && name.indexOf(prefix + '-') !== 0) {
				el.setAttribute('name', prefix + '-' + name);
			}
		});
		qsa(root, '[id]').forEach(function (el) {
			var id = el.getAttribute('id');
			if (id && id.indexOf(prefix + '-') !== 0) {
				el.setAttribute('id', prefix + '-' + id);
			}
		});
		qsa(root, 'label[for]').forEach(function (el) {
			var f = el.getAttribute('for');
			if (f && f.indexOf(prefix + '-') !== 0) {
				el.setAttribute('for', prefix + '-' + f);
			}
		});

		var activities = [];

		/* ── Sticky nav + mobile menu ── */
		var navToggle = qs(root, '[data-sara-nav-toggle]');
		var navLinks = qs(root, '[data-sara-nav-links]');
		if (navToggle && navLinks) {
			navToggle.addEventListener('click', function () {
				var open = navLinks.classList.toggle('is-open');
				navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			});
			qsa(root, '[data-sara-nav-links] a').forEach(function (link) {
				link.addEventListener('click', function () {
					navLinks.classList.remove('is-open');
					navToggle.setAttribute('aria-expanded', 'false');
				});
			});
		}

		/* ── Scroll reveal ── */
		if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			var revealEls = qsa(root, '[data-sara-reveal]');
			if (revealEls.length && 'IntersectionObserver' in window) {
				var io = new IntersectionObserver(
					function (entries) {
						entries.forEach(function (entry) {
							if (entry.isIntersecting) {
								entry.target.classList.add('is-visible');
								io.unobserve(entry.target);
							}
						});
					},
					{ threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
				);
				revealEls.forEach(function (el) {
					io.observe(el);
				});
			} else {
				revealEls.forEach(function (el) {
					el.classList.add('is-visible');
				});
			}
		} else {
			qsa(root, '[data-sara-reveal]').forEach(function (el) {
				el.classList.add('is-visible');
			});
		}

		/* ── Meter tabs ── */
		var tabBtns = qsa(root, '[data-sara-tab]');
		var tabPanels = qsa(root, '[data-sara-panel]');
		tabBtns.forEach(function (btn) {
			btn.addEventListener('click', function () {
				var id = btn.getAttribute('data-sara-tab');
				tabBtns.forEach(function (b) {
					b.classList.remove('is-active');
					b.setAttribute('aria-selected', 'false');
				});
				tabPanels.forEach(function (p) {
					var on = p.getAttribute('data-sara-panel') === id;
					p.hidden = !on;
					p.classList.toggle('is-active', on);
				});
				btn.classList.add('is-active');
				btn.setAttribute('aria-selected', 'true');
			});
		});

		/* ── Accordions ── */
		qsa(root, '[data-sara-accordion]').forEach(function (acc) {
			var trigger = qs(acc, '[data-sara-acc-trigger]');
			var body = qs(acc, '[data-sara-acc-body]');
			if (!trigger || !body) {
				return;
			}
			trigger.addEventListener('click', function () {
				var open = acc.classList.toggle('is-open');
				trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
				body.hidden = !open;
			});
		});

		/* ── Activity exposure scorer ── */
		var expForm = qs(root, '[data-sara-exposure-form]');
		var expResult = qs(root, '[data-sara-exposure-result]');
		var expScoreBtn = qs(root, '[data-sara-exposure-score]');
		var expAddBtn = qs(root, '[data-sara-exposure-add]');
		var expNameInput = qs(root, '[data-sara-exposure-name]');

		function readExposurePoints() {
			var sum = 0;
			qsa(expForm, 'input[type="radio"]:checked').forEach(function (r) {
				sum += parseInt(r.value, 10) || 0;
			});
			return sum;
		}

		function exposureComplete() {
			var names = {};
			qsa(expForm, 'input[type="radio"]').forEach(function (r) {
				names[r.name] = true;
			});
			var ok = true;
			Object.keys(names).forEach(function (name) {
				if (!expForm.querySelector('input[name="' + name + '"]:checked')) {
					ok = false;
				}
			});
			return ok;
		}

		function renderExposureResult(pct, band) {
			if (!expResult) {
				return;
			}
			expResult.hidden = false;
			expResult.className = 'sara-result sara-result--' + band.key;
			expResult.innerHTML =
				'<p class="sara-result__score"><span class="sara-result__num">' + pct + '</span>/100</p>' +
				'<p class="sara-result__band">' + band.label + ' — <strong>' + band.action + '</strong></p>' +
				'<p class="sara-result__desc">' + band.desc + '</p>';
		}

		if (expScoreBtn && expForm) {
			expScoreBtn.addEventListener('click', function () {
				if (!exposureComplete()) {
					expResult.hidden = false;
					expResult.className = 'sara-result sara-result--error';
					expResult.innerHTML = '<p>Please answer all four factors before scoring.</p>';
					return;
				}
				var raw = readExposurePoints();
				var pct = Math.round((raw / EXPOSURE_MAX) * 100);
				renderExposureResult(pct, bandExposure(pct));
				root._saraLastExposure = { pct: pct, band: bandExposure(pct), raw: raw };
			});
		}

		if (expAddBtn && expForm) {
			expAddBtn.addEventListener('click', function () {
				if (!root._saraLastExposure) {
					if (!exposureComplete()) {
						return;
					}
					var raw = readExposurePoints();
					root._saraLastExposure = { pct: Math.round((raw / EXPOSURE_MAX) * 100), band: bandExposure(Math.round((raw / EXPOSURE_MAX) * 100)), raw: raw };
				}
				var name = expNameInput && expNameInput.value.trim() ? expNameInput.value.trim() : 'Unnamed activity';
				activities.push({ name: name, pct: root._saraLastExposure.pct, band: root._saraLastExposure.band });
				updateClassPicture(root, activities);
				if (expNameInput) {
					expNameInput.value = '';
				}
				root._saraLastExposure = null;
				if (expResult) {
					expResult.hidden = true;
				}
				/* Switch to class tab */
				var classTab = qs(root, '[data-sara-tab="class"]');
				if (classTab) {
					classTab.click();
				}
			});
		}

		/* ── Student reliance ── */
		var relForm = qs(root, '[data-sara-reliance-form]');
		var relResult = qs(root, '[data-sara-reliance-result]');
		var relScoreBtn = qs(root, '[data-sara-reliance-score]');

		if (relScoreBtn && relForm) {
			relScoreBtn.addEventListener('click', function () {
				var items = qsa(relForm, 'select');
				if (items.length < 6) {
					return;
				}
				var incomplete = items.some(function (sel) {
					return sel.value === '';
				});
				if (incomplete) {
					relResult.hidden = false;
					relResult.className = 'sara-result sara-result--error';
					relResult.innerHTML = '<p>Please answer all six statements.</p>';
					return;
				}
				var sum = 0;
				items.forEach(function (sel) {
					var v = parseInt(sel.value, 10) || 0;
					if (sel.getAttribute('data-reverse') === '1') {
						sum += 3 - v;
					} else {
						sum += v;
					}
				});
				var pct = Math.round((sum / RELIANCE_MAX) * 100);
				var band = bandReliance(pct);
				relResult.hidden = false;
				relResult.className = 'sara-result sara-result--' + band.key;
				relResult.innerHTML =
					'<p class="sara-result__score"><span class="sara-result__num">' + pct + '</span>/100</p>' +
					'<p class="sara-result__band">' + band.label + ' — <strong>' + band.action + '</strong></p>' +
					'<p class="sara-result__desc">' + band.desc + '</p>' +
					'<p class="sara-result__note">Self-reported and not stored. This is not a diagnostic instrument.</p>';
			});
		}

		/* ── Verify routine ── */
		var verifyList = qs(root, '[data-sara-verify-steps]');
		var verifyDone = qs(root, '[data-sara-verify-done]');
		if (verifyList) {
			qsa(verifyList, '[data-sara-verify-step]').forEach(function (step) {
				step.addEventListener('click', function () {
					step.classList.toggle('is-done');
					var doneCount = qsa(verifyList, '.is-done').length;
					if (verifyDone) {
						verifyDone.hidden = doneCount < 4;
					}
				});
			});
		}

		/* ── Enrolment (front-end only) ── */
		var enrolForm = qs(root, '[data-sara-enrol]');
		if (enrolForm) {
			enrolForm.addEventListener('submit', function (e) {
				e.preventDefault();
				var email = qs(enrolForm, 'input[type="email"]');
				var msg = qs(root, '[data-sara-enrol-msg]');
				if (!email || !email.value.trim() || email.value.indexOf('@') < 1) {
					if (msg) {
						msg.textContent = 'Please enter a valid email address.';
						msg.hidden = false;
					}
					return;
				}
				if (msg) {
					msg.textContent = 'Thank you — we will be in touch when live classes open. (Demo: not sent to a server.)';
					msg.hidden = false;
				}
				enrolForm.reset();
			});
		}

		updateClassPicture(root, activities);
	}

	function updateClassPicture(root, activities) {
		var list = qs(root, '[data-sara-class-list]');
		var empty = qs(root, '[data-sara-class-empty]');
		var avgEl = qs(root, '[data-sara-class-avg]');
		var warn = qs(root, '[data-sara-class-warn]');
		var needle = qs(root, '[data-sara-gauge-needle]');
		var gaugeLabel = qs(root, '[data-sara-gauge-label]');

		if (!list) {
			return;
		}

		list.innerHTML = '';
		if (!activities.length) {
			if (empty) {
				empty.hidden = false;
			}
			if (avgEl) {
				avgEl.textContent = '—';
			}
			if (warn) {
				warn.hidden = true;
			}
			if (needle) {
				needle.style.transform = 'rotate(-90deg)';
			}
			if (gaugeLabel) {
				gaugeLabel.textContent = 'Add assessed activities to see your school picture';
			}
			return;
		}

		if (empty) {
			empty.hidden = true;
		}

		var sum = 0;
		activities.forEach(function (a) {
			sum += a.pct;
			var li = document.createElement('li');
			li.className = 'sara-class-item sara-class-item--' + a.band.key;
			li.innerHTML =
				'<span class="sara-class-item__name">' + escapeHtml(a.name) + '</span>' +
				'<span class="sara-class-item__band">' + escapeHtml(a.band.label) + '</span>' +
				'<span class="sara-class-item__score">' + a.pct + '/100</span>';
			list.appendChild(li);
		});

		var avg = Math.round(sum / activities.length);
		var band = bandExposure(avg);
		if (avgEl) {
			avgEl.textContent = avg + '/100 (' + band.label + ')';
		}
		if (warn) {
			warn.hidden = band.key !== 'high';
		}
		if (gaugeLabel) {
			gaugeLabel.textContent = 'School AI risk level: ' + band.label + ' (' + avg + '/100)';
		}
		if (needle) {
			var deg = (avg - 50) * 1.8;
			needle.style.transform = 'rotate(' + deg + 'deg)';
		}
	}

	function escapeHtml(str) {
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function initAll() {
		document.querySelectorAll('.sara').forEach(initRoot);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAll);
	} else {
		initAll();
	}
})();
