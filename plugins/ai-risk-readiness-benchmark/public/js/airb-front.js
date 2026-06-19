/**
 * AI Risk & Readiness Benchmark — front-end
 */
(function () {
	'use strict';

	if (!window.airbBenchmark) return;

	var cfg = airbBenchmark.config;
	var i18n = airbBenchmark.i18n;
	var domains = cfg.domains || {};
	var domainKeys = Object.keys(domains);
	var domainColors = cfg.domain_colors || {};
	var domainRecs = cfg.domain_recommendations || {};
	var parentResult = cfg.parent_result || {};
	var publicResult = cfg.public_result || {};
	var teacherResult = cfg.teacher_result || {};
	var studentResult = cfg.student_result || {};
	var supportResult = cfg.support_result || {};
	var leaderResult = cfg.leader_result || {};
	var improvementHub = cfg.improvement_hub || {};
	var copyTiersRegistry = cfg.copy_tiers || {};
	var STORAGE_KEY = 'airb_completed_roles_v1';
	var SESSION_KEY = 'airb_session_id_v1';
	var SNAPSHOT_KEY = 'airb_results_snapshot_v1';
	var VARIANT_KEY = 'airb_audit_variant_v1';
	var introCollapsed = false;

	var state = {
		phase: 'role',
		role: '',
		step: 0,
		questionStep: 0,
		sections: [],
		questions: [],
		answers: {},
		results: null,
		school: '',
		email: '',
		schoolPhase: '',
		orgType: '',
		yearGroup: '',
		sessionId: '',
	};

	var el = {
		role: document.getElementById('airb-screen-role'),
		audit: document.getElementById('airb-screen-audit'),
		contact: document.getElementById('airb-screen-contact'),
		results: document.getElementById('airb-screen-results'),
		nav: document.getElementById('airb-nav'),
		back: document.getElementById('airb-back'),
		next: document.getElementById('airb-next'),
		progress: document.getElementById('airb-progress'),
		stepper: document.getElementById('airb-stepper'),
		progressLbl: document.getElementById('airb-progress-label'),
		error: document.getElementById('airb-error'),
		printHost: document.getElementById('airb-print-host'),
		navSlot: document.getElementById('airb-nav-slot'),
		hero: document.querySelector('.airb__hero'),
		deck: document.getElementById('airb-deck'),
		root: document.getElementById('airb-benchmark'),
	};

	function persistResultsSnapshot() {
		var r = state.results;
		if (!r || !state.role) return;
		try {
			var weak = (r.interest_form && r.interest_form.weak_domains) ? r.interest_form.weak_domains : [];
			localStorage.setItem(SNAPSHOT_KEY, JSON.stringify({
				submissionId: state.submissionId || 0,
				role: state.role,
				alignment_score: r.alignment_score,
				weak_domains: weak,
				email: state.email || '',
				school: state.school || '',
				ts: Date.now(),
			}));
		} catch (e) { /* private browsing */ }
	}

	function getSessionId() {
		try {
			var id = localStorage.getItem(SESSION_KEY);
			if (!id) {
				id = 'ses_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2, 11);
				localStorage.setItem(SESSION_KEY, id);
			}
			state.sessionId = id;
			return id;
		} catch (e) {
			state.sessionId = state.sessionId || ('ses_' + Date.now().toString(36));
			return state.sessionId;
		}
	}

	function appendSessionToUrl(url) {
		if (!url || url.indexOf('mailto:') === 0) return url;
		var sep = url.indexOf('?') >= 0 ? '&' : '?';
		return url + sep + 'airb_session=' + encodeURIComponent(getSessionId());
	}

	function trackEvent(eventType, metadata) {
		if (!airbBenchmark.ajaxurl || !eventType) return;
		var body = new FormData();
		body.append('action', 'airb_track_event');
		body.append('nonce', airbBenchmark.nonce);
		body.append('session_id', getSessionId());
		body.append('event_type', eventType);
		body.append('role', state.role || '');
		if (state.submissionId) {
			body.append('submission_id', String(state.submissionId));
		}
		body.append('metadata', JSON.stringify(metadata || {}));
		if (navigator.sendBeacon) {
			navigator.sendBeacon(airbBenchmark.ajaxurl, body);
			return;
		}
		fetch(airbBenchmark.ajaxurl, { method: 'POST', body: body, credentials: 'same-origin', keepalive: true }).catch(function () {});
	}

	function isMobileFlow() {
		return window.matchMedia('(max-width: 768px)').matches;
	}

	function scrollBenchmarkToTop() {
		if (!el.root) return;
		if (state.phase === 'role' || state.phase === 'results') {
			if (!isMobileFlow()) return;
		}
		function applyScroll() {
			var anchor = null;
			if (state.phase === 'audit' && el.progress && !el.progress.hidden) {
				anchor = el.progress;
			} else if (state.phase === 'audit' || state.phase === 'leader_profile') {
				anchor = (el.audit && el.audit.querySelector('.airb__audit-head')) || el.audit;
			} else if (state.phase === 'contact') {
				anchor = el.contact;
			} else {
				anchor = el.root.querySelector('.airb__appbar') || el.root;
			}
			if (!anchor) return;
			try {
				anchor.scrollIntoView({ behavior: 'auto', block: 'start' });
			} catch (err) { /* ignore */ }
			var rect = anchor.getBoundingClientRect();
			var adminBar = document.getElementById('wpadminbar');
			var offset = (adminBar ? adminBar.offsetHeight : 0) + (isMobileFlow() ? 72 : 28);
			if (rect.top < 0 || rect.top > offset + 8) {
				window.scrollTo({
					top: Math.max(0, rect.top + window.pageYOffset - offset),
					behavior: 'auto',
				});
			}
		}
		window.requestAnimationFrame(function () {
			window.requestAnimationFrame(applyScroll);
		});
	}

	function scrollFlowToTop() {
		scrollBenchmarkToTop();
	}

	function updateFlowChrome() {
		if (!el.root) return;
		var inFlow = !!(el.nav && !el.nav.hidden);
		var mobileShell = isMobileFlow() && (state.phase === 'role' || state.phase === 'results' || inFlow);
		el.root.classList.toggle('airb--nav-dock', inFlow);
		el.root.classList.toggle('airb--mobile-flow', mobileShell);
		el.root.classList.toggle('airb--single-question', state.phase === 'audit');
		el.root.classList.toggle('airb--phase-role', state.phase === 'role');
		el.root.classList.toggle('airb--phase-audit', state.phase === 'audit' || state.phase === 'leader_profile');
		el.root.classList.toggle('airb--phase-results', state.phase === 'results');
		el.root.classList.toggle('airb--intro-collapsed', introCollapsed);
		syncNavPlacement();
		document.body.classList.toggle('airb-flow-active', state.phase === 'audit' || state.phase === 'contact' || state.phase === 'leader_profile');
		document.body.classList.toggle('airb-results-active', state.phase === 'results');
	}

	function syncNavPlacement() {
		if (!el.nav) return;
		var inlineHost = null;
		if (isMobileFlow() && !el.nav.hidden) {
			if (state.phase === 'audit' || state.phase === 'leader_profile') inlineHost = el.audit;
			if (state.phase === 'contact') inlineHost = el.contact;
		}
		el.nav.classList.toggle('airb__nav--inline', !!inlineHost);
		if (inlineHost) {
			if (el.error) {
				inlineHost.appendChild(el.error);
			}
			inlineHost.appendChild(el.nav);
		} else {
			if (el.navSlot) {
				el.navSlot.appendChild(el.nav);
			}
			if (el.error && el.navSlot && el.navSlot.parentNode) {
				el.navSlot.parentNode.insertBefore(el.error, el.navSlot);
			}
		}
	}

	function profilePhase() {
		return state.schoolPhase || state.answers._school_phase || '';
	}

	function answersWithProfile() {
		var answers = Object.assign({}, state.answers);
		if (profilePhase()) {
			answers._school_phase = profilePhase();
		}
		return answers;
	}

	function questionApplies(q, answers) {
		if (window.AIRB && AIRB.Audit && AIRB.Audit.questionApplies) {
			return AIRB.Audit.questionApplies(q, answers, profilePhase);
		}
		answers = answers || state.answers || {};
		var unless = q.show_unless_answer || {};
		var depQid;
		for (depQid in unless) {
			if (!Object.prototype.hasOwnProperty.call(unless, depQid)) continue;
			var depVal = answers[depQid] !== undefined ? String(answers[depQid]) : '';
			if (depVal && unless[depQid].indexOf(depVal) >= 0) {
				return false;
			}
		}
		var phases = q.show_for_phases || [];
		if (!phases.length) {
			return true;
		}
		var phase = profilePhase();
		if (!phase && answers && answers._school_phase) {
			phase = answers._school_phase;
		}
		if (!phase) {
			return false;
		}
		return phases.indexOf(phase) >= 0;
	}

	function visibleQuestionsInSection(section, answers) {
		return (section && section.questions ? section.questions : []).filter(function (q) {
			return questionApplies(q, answers);
		});
	}

	function auditQuestionCounts(sections, answers, step) {
		sections = sections || [];
		answers = answers || {};
		var total = 0;
		var offsetBeforeSection = 0;
		sections.forEach(function (section, index) {
			var count = visibleQuestionsInSection(section, answers).length;
			total += count;
			if (index < step) {
				offsetBeforeSection += count;
			}
		});
		var currentSection = sections[step] || null;
		return {
			total: total,
			offsetBeforeSection: offsetBeforeSection,
			countInSection: visibleQuestionsInSection(currentSection, answers).length,
		};
	}

	function questionNumberLabel(globalIndex, total) {
		var label = i18n.question || 'Question';
		var ofWord = i18n.of || 'of';
		return label + ' ' + globalIndex + ' ' + ofWord + ' ' + total;
	}

	function auditProgressLabel(counts) {
		if (!counts || !counts.total) {
			return '';
		}
		if (usesSingleQuestionFlow()) {
			return questionNumberLabel(counts.offsetBeforeSection + state.questionStep + 1, counts.total);
		}
		if (counts.countInSection <= 1) {
			return questionNumberLabel(counts.offsetBeforeSection + 1, counts.total);
		}
		var start = counts.offsetBeforeSection + 1;
		var end = counts.offsetBeforeSection + counts.countInSection;
		var questionsLabel = i18n.questions || 'Questions';
		var ofWord = i18n.of || 'of';
		return questionsLabel + ' ' + start + '\u2013' + end + ' ' + ofWord + ' ' + counts.total;
	}

	function usesSingleQuestionFlow() {
		return state.phase === 'audit';
	}

	function visibleQuestionsForCurrentSection(answers) {
		var section = state.sections[state.step];
		return visibleQuestionsInSection(section, answers || state.answers);
	}

	function clampQuestionStep(visibleCount) {
		if (!visibleCount) {
			state.questionStep = 0;
			return;
		}
		if (state.questionStep >= visibleCount) {
			state.questionStep = visibleCount - 1;
		}
		if (state.questionStep < 0) {
			state.questionStep = 0;
		}
	}

	function syncAuditProgressUi(sections, answers, step) {
		var counts = auditQuestionCounts(sections, answers, step);
		updateAuditProgressStepper(sections, answers, step);
		if (el.progressLbl) {
			el.progressLbl.textContent = auditProgressLabel(counts);
		}
		return counts;
	}

	function sectionAnswersDraft(section) {
		var draft = Object.assign({}, state.answers);
		if (!section) return draft;
		section.questions.forEach(function (q) {
			if (q.type === 'slider') {
				var sl = document.getElementById('airb-q-' + q.id);
				if (sl) draft[q.id] = sl.value;
				return;
			}
			if (q.type === 'select') {
				var sel = document.getElementById('airb-q-' + q.id);
				if (sel && sel.value) draft[q.id] = sel.value;
				return;
			}
			var picked = el.audit && el.audit.querySelector('input[name="airb-q-' + q.id + '"]:checked');
			if (picked) draft[q.id] = picked.value;
		});
		return draft;
	}

	function refreshSectionConditionalVisibility(section) {
		if (!section || !el.audit) return;
		var currentBlock = el.audit.querySelector('.airb__q-block:not([hidden])');
		var currentQuestionId = currentBlock ? (currentBlock.getAttribute('data-airb-qid') || '') : '';
		var draft = sectionAnswersDraft(section);
		var step = state.sections.indexOf(section);
		if (step < 0) {
			step = state.step;
		}
		var visible = section.questions.filter(function (q) {
			return questionApplies(q, draft);
		});

		if (usesSingleQuestionFlow()) {
			section.questions.forEach(function (q) {
				var block = el.audit.querySelector('[data-airb-qid="' + q.id + '"]');
				if (!block) return;
				var applies = questionApplies(q, draft);
				if (!applies) {
					delete state.answers[q.id];
					block.querySelectorAll('input:checked').forEach(function (inp) {
						inp.checked = false;
					});
					var sel = block.querySelector('select');
					if (sel) sel.value = '';
				}
			});
			visible = section.questions.filter(function (q) {
				return questionApplies(q, draft);
			});
			var currentQuestionIndex = visible.findIndex(function (q) {
				return q.id === currentQuestionId;
			});
			if (currentQuestionIndex >= 0) {
				state.questionStep = currentQuestionIndex;
			} else {
				clampQuestionStep(visible.length);
			}
			section.questions.forEach(function (q) {
				var block = el.audit.querySelector('[data-airb-qid="' + q.id + '"]');
				if (!block) return;
				var applies = questionApplies(q, draft);
				if (!applies) {
					block.hidden = true;
					return;
				}
				block.hidden = visible.indexOf(q) !== state.questionStep;
			});
			syncAuditProgressUi(state.sections, draft, step);
			return;
		}

		section.questions.forEach(function (q) {
			var block = el.audit.querySelector('[data-airb-qid="' + q.id + '"]');
			if (!block) return;
			var applies = questionApplies(q, draft);
			block.hidden = !applies;
			if (!applies) {
				delete state.answers[q.id];
				block.querySelectorAll('input:checked').forEach(function (inp) {
					inp.checked = false;
				});
				var sel = block.querySelector('select');
				if (sel) sel.value = '';
			}
		});
		var counts = auditQuestionCounts(state.sections, draft, step);
		section.questions.forEach(function (q) {
			var block = el.audit.querySelector('[data-airb-qid="' + q.id + '"]');
			if (!block) return;
			var meta = block.querySelector('.airb__q-meta');
			if (!meta) return;
			var pos = visible.indexOf(q);
			var globalIndex = counts.offsetBeforeSection + pos + 1;
			meta.textContent = pos >= 0 && counts.total > 1 ? questionNumberLabel(globalIndex, counts.total) : '';
			meta.hidden = !(pos >= 0 && counts.total > 1);
		});
	}

	function pruneInapplicableAnswers() {
		if (!state.role) return;
		(cfg.questions || []).forEach(function (q) {
			if (q.role !== state.role) return;
			if (!questionApplies(q, state.answers) && state.answers[q.id] !== undefined) {
				delete state.answers[q.id];
			}
		});
	}

	function syncProfileIntoAnswers() {
		if (profilePhase()) {
			state.answers._school_phase = profilePhase();
		}
		pruneInapplicableAnswers();
	}

	function auditVariantSeed(role) {
		try {
			var key = VARIANT_KEY + '_' + role;
			var n = parseInt(localStorage.getItem(key) || '0', 10) + 1;
			localStorage.setItem(key, String(n));
			return n;
		} catch (e) {
			return Date.now();
		}
	}

	function variantIndexForQuestion(seed, qid, count) {
		if (count < 2) {
			return 0;
		}
		var h = 0;
		var s = String(seed) + ':' + qid;
		for (var i = 0; i < s.length; i++) {
			h = ((h << 5) - h + s.charCodeAt(i)) | 0;
		}
		return Math.abs(h) % count;
	}

	function questionDisplayText(q, seed) {
		var variants = q.text_variants || [];
		if (!variants.length) {
			return q.text;
		}
		var pool = [q.text].concat(variants);
		return pool[variantIndexForQuestion(seed, q.id, pool.length)] || q.text;
	}

	function seededRng(seed, salt) {
		var h = 0;
		var s = String(seed) + ':' + salt;
		for (var i = 0; i < s.length; i++) {
			h = ((h << 5) - h + s.charCodeAt(i)) | 0;
		}
		var n = Math.abs(h) || 1;
		return function () {
			n = (n * 1664525 + 1013904223) >>> 0;
			return n / 4294967296;
		};
	}

	function shuffleWithRng(list, rng) {
		var arr = list.slice();
		for (var i = arr.length - 1; i > 0; i--) {
			var j = Math.floor(rng() * (i + 1));
			var tmp = arr[i];
			arr[i] = arr[j];
			arr[j] = tmp;
		}
		return arr;
	}

	function cloneSections(sections) {
		return sections.map(function (section) {
			return {
				name: section.name,
				domain: section.domain,
				questions: section.questions.map(function (q) {
					var copy = Object.assign({}, q);
					if (q.text_variants) {
						copy.text_variants = q.text_variants.slice();
					}
					if (q.options) {
						copy.options = q.options.map(function (opt) {
							return Object.assign({}, opt);
						});
					}
					return copy;
				}),
			};
		});
	}

	function applyAuditPresentation(role) {
		var seed = auditVariantSeed(role);
		state.variantSeed = seed;
		state.sections = cloneSections(state.sections);

		state.sections.forEach(function (section) {
			section.questions.forEach(function (q) {
				q.displayText = questionDisplayText(q, seed);
			});
			section.questions = shuffleWithRng(section.questions, seededRng(seed, 'q:' + section.name));
		});

		if (seed > 1) {
			state.sections = shuffleWithRng(state.sections, seededRng(seed, 'sections'));
		}

		state.questions = state.sections.reduce(function (acc, s) {
			return acc.concat(s.questions);
		}, []);
	}

	function beginAudit() {
		try {
			hideError();
			if (!state.role) {
				showError(i18n.chooseRole);
				return false;
			}
			if (state.role === 'leader' && !profilePhase()) {
				state.phase = 'leader_profile';
				state.step = 0;
				state.answers = {};
				collapseIntro();
				renderLeaderProfile();
				return true;
			}
			return startAuditQuestions();
		} catch (err) {
			if (window.console && console.error) {
				console.error('AIRB beginAudit failed', err);
			}
			showError(i18n.error || 'Something went wrong. Please refresh and try again.');
			return false;
		}
	}

	function startAuditQuestions() {
		syncProfileIntoAnswers();
		state.sections = sectionsForRole(state.role);
		if (!state.sections.length) {
			showError(i18n.error);
			return false;
		}
		applyAuditPresentation(state.role);
		state.phase = 'audit';
		state.step = 0;
		state.questionStep = 0;
		if (!Object.keys(state.answers).length) {
			state.answers = {};
		}
		renderAuditSection();
		return true;
	}

	function bindRoleCardActions() {
		if (!el.root || el.root.dataset.airbRoleBound === '1') {
			return;
		}
		el.root.dataset.airbRoleBound = '1';
		el.root.addEventListener('click', function (e) {
			if (state.phase !== 'role') {
				return;
			}
			var btn = e.target.closest('.airb__role-card[data-role]');
			if (!btn || !el.role || !el.role.contains(btn)) {
				return;
			}
			e.preventDefault();
			state.role = btn.getAttribute('data-role') || '';
			state.schoolPhase = '';
			state.orgType = '';
			state.yearGroup = '';
			state.answers = {};
			state.results = null;
			beginAudit();
		});
	}

	function collapseIntro() {
		if (introCollapsed) {
			return;
		}
		introCollapsed = true;
		if (el.hero) {
			el.hero.hidden = true;
		}
		if (el.deck) {
			el.deck.hidden = true;
		}
	}

	function expandIntro() {
		introCollapsed = false;
		if (el.hero) {
			el.hero.hidden = false;
		}
		if (el.deck) {
			el.deck.hidden = false;
		}
	}

	function riskBand(pct) {
		if (pct <= 30) return 'low';
		if (pct <= 60) return 'moderate';
		if (pct <= 80) return 'high';
		return 'critical';
	}

	function bandLabel(band) {
		return band.charAt(0).toUpperCase() + band.slice(1);
	}

	function readinessLevel(pct, role) {
		role = role || state.role || 'leader';
		if (role === 'student') {
			return studentSkillBand(pct);
		}
		if (role === 'parent') {
			return parentAwarenessBand(pct);
		}
		var r = Math.max(0, Math.min(100, parseInt(pct, 10) || 0));
		var bands = roleHeroBandDefinitions(role);
		for (var i = 0; i < bands.length; i++) {
			var band = bands[i];
			if (r >= band.min && r <= band.max) {
				return { slug: band.slug, label: band.label };
			}
		}
		return { slug: bands[0].slug, label: bands[0].label };
	}

	function readinessBandLabel(pct, role) {
		role = role || state.role || 'leader';
		var r = Math.max(0, Math.min(100, parseInt(pct, 10) || 0));
		if ((role === 'leader' || role === 'teacher' || role === 'support_staff') && r >= 60 && r <= 64) {
			return (i18n.bandsReadiness && i18n.bandsReadiness.earlyEstablished) || 'Early Established';
		}
		return readinessLevel(pct, role).label;
	}

	function readinessBandColor(pct, role) {
		var colors = {
			leading: '#15803d',
			strong: 'var(--airb-low)',
			established: '#3a8fb0',
			developing: 'var(--airb-mod)',
			emerging: 'var(--airb-crit)',
			beginning: '#a32d2d',
			advanced: '#15803d',
			confident: '#1d9e75',
			aware: '#0c6b8a',
			just_starting: '#a32d2d',
			well_prepared: '#15803d',
		};
		return colors[readinessLevel(pct, role).slug] || 'var(--airb-text)';
	}

	function roleHeroBandDefinitions(role) {
		role = role || state.role || 'leader';
		if (role === 'student') {
			return studentBandDefinitions();
		}
		if (role === 'parent') {
			return parentBandDefinitions();
		}
		if (role === 'public') {
			return publicResult.hero_bands && publicResult.hero_bands.length
				? publicResult.hero_bands
				: readinessBandDefinitionsLegacy();
		}
		var cfg = role === 'teacher' ? teacherResult : role === 'support_staff' ? supportResult : role === 'public' ? publicResult : leaderResult;
		if (cfg && cfg.hero_bands && cfg.hero_bands.length) {
			return cfg.hero_bands;
		}
		return readinessBandDefinitionsLegacy();
	}

	function readinessBandDefinitionsLegacy() {
		var labels = i18n.bandsReadiness || {};
		return [
			{ slug: 'emerging', label: labels.emerging || 'At risk', min: 0, max: 39, tone: 'alarm' },
			{ slug: 'developing', label: labels.developing || 'Action required', min: 40, max: 59, tone: 'concern' },
			{ slug: 'established', label: labels.established || 'Stable', min: 60, max: 74 },
			{ slug: 'strong', label: labels.strong || 'Confident', min: 75, max: 89 },
			{ slug: 'leading', label: labels.leading || 'Responsible', min: 90, max: 100 },
		];
	}

	function readinessBandDefinitions(role) {
		return roleHeroBandDefinitions(role);
	}

	function readinessBandShortLabel(slug, fullLabel) {
		var labels = i18n.bandsReadinessShort || {};
		if (labels[slug]) return labels[slug];
		if (fullLabel && /early/i.test(fullLabel)) {
			return labels.earlyEstablished || 'Early est.';
		}
		var defaults = {
			emerging: 'At risk',
			developing: 'Concern',
			established: 'Est.',
			strong: 'Str.',
			leading: 'Lead.',
		};
		return defaults[slug] || fullLabel;
	}

	function studentBandShortLabel(slug, fullLabel) {
		var labels = i18n.studentJourneyShort || {};
		if (labels[slug]) return labels[slug];
		var defaults = {
			beginning: 'Attention',
			developing: 'Take care',
			emerging: 'Aware',
			confident: 'Conf.',
			advanced: 'Adv.',
		};
		return defaults[slug] || fullLabel;
	}

	function parentBandShortLabel(slug, fullLabel) {
		var labels = i18n.parentAwarenessShort || {};
		if (labels[slug]) return labels[slug];
		var defaults = {
			just_starting: 'Needs help',
			developing: 'Gaps',
			aware: 'Aware',
			confident: 'Conf.',
			well_prepared: 'Prepared',
		};
		return defaults[slug] || fullLabel;
	}

	function publicBandShortLabel(slug, fullLabel) {
		var defaults = {
			at_risk: 'At risk',
			take_care: 'Take care',
			aware: 'Aware',
			confident: 'Confident',
			advanced: 'Advanced',
		};
		return defaults[slug] || fullLabel;
	}

	function publicHeroBarShortLabelFn(role) {
		if (role === 'public') return publicBandShortLabel;
		if (role === 'parent') return parentBandShortLabel;
		if (role === 'student') return studentBandShortLabel;
		return readinessBandShortLabel;
	}

	function heroBarBandLabelHtml(band, shortLabelFn) {
		var shortLabel = band.label_short || shortLabelFn(band.slug, band.label);
		var toneClass = band.tone ? ' airb__hero-bar-lab--' + band.tone : '';
		return '<span class="airb__hero-bar-lab' + toneClass + '">' + leaderResponsiveLabel(band.label, shortLabel) + '</span>';
	}

	function readinessBandPillHtml(slug, label) {
		slug = String(slug || 'developing').replace(/[^a-z0-9_-]/gi, '');
		return '<span class="airb__band-pill airb__band-pill--' + slug + '">' + esc(label || '') + '</span>';
	}

	function focusRiskBadge(pct) {
		var score = Math.max(0, Math.min(100, parseInt(pct, 10) || 0));
		if (score <= 25) {
			return { label: i18n.riskCritical || 'Critical', slug: 'critical' };
		}
		if (score <= 40) {
			return { label: i18n.riskHigh || 'High', slug: 'high' };
		}
		return { label: i18n.riskModerate || 'Moderate', slug: 'moderate' };
	}

	function leaderFocusBadge(pct) {
		var badge = focusRiskBadge(pct);
		var core = badge.slug === 'high' ? (i18n.riskHighLong || 'High risk') : badge.label;
		if (badge.slug === 'high') {
			core = i18n.riskHigh || 'High';
		}
		return {
			slug: badge.slug,
			text: core + ' · ' + pct + '%',
			core: core,
			detail: ' · ' + pct + '%',
		};
	}

	function leaderFocusSeverity(pct) {
		var score = Math.max(0, Math.min(100, parseInt(pct, 10) || 0));
		if (score <= 25) return 'critical';
		if (score <= 40) return 'high';
		return 'moderate';
	}

	function heatmapExposureBadge(risk) {
		risk = Math.max(0, Math.min(100, Math.round(parseFloat(risk) || 0)));
		var band = riskBand(risk);
		var labels = {
			critical: i18n.riskCritical || 'critical',
			high: i18n.riskHigh || 'high',
			moderate: i18n.riskModerate || 'moderate',
			low: i18n.riskLow || 'low',
		};
		return {
			slug: band,
			text: risk + '% ' + (labels[band] || band),
			short: String(risk) + '%',
		};
	}

	function heatmapBarColor(risk) {
		risk = Math.round(parseFloat(risk) || 0);
		if (risk > 80) return '#e24b4a';
		if (risk > 50) return '#ef9f27';
		if (risk > 30) return '#378add';
		return '#3b6d11';
	}

	function leaderSectionDivider() {
		return '<div class="airb__leader-section-divider" aria-hidden="true"></div>';
	}

	function leaderResponsiveLabel(longText, shortText) {
		shortText = shortText || longText;
		return '<span class="airb__lbl-long">' + esc(longText) + '</span>' +
			'<span class="airb__lbl-short">' + esc(shortText) + '</span>';
	}

	function leaderSectionLabel(longText, shortText) {
		if (!longText) return '';
		return '<h3 class="airb__leader-section-label">' + leaderResponsiveLabel(longText, shortText) + '</h3>';
	}

	function metricToneClass(tone) {
		var allowed = { urgent: true, warning: true, neutral: true, positive: true };
		return allowed[tone] ? tone : 'neutral';
	}

	function leaderMetricSignals(type, slug) {
		var bag = (leaderResult.metric_signals || {})[type] || {};
		var entry = bag[slug] || {};
		return {
			signal: entry.signal || '',
			tone: metricToneClass(entry.tone || 'neutral'),
			consequence: entry.consequence || '',
		};
	}

	function leaderGovernanceLevel(score) {
		var levels = leaderResult.maturity_levels || [];
		score = Math.max(0, Math.min(100, parseInt(score, 10) || 0));
		for (var i = 0; i < levels.length; i++) {
			var min = parseInt(levels[i].min, 10) || 0;
			var max = parseInt(levels[i].max, 10) || 100;
			if (score >= min && score <= max) {
				return levels[i];
			}
		}
		return levels[0] || { slug: 'emerging', label: 'At risk' };
	}

	function leaderMetricCardHtml(opts) {
		opts = opts || {};
		var tone = metricToneClass(opts.tone || 'neutral');
		var pctDisplay = opts.pct === '—' ? '—' : (String(opts.pct) + '%');
		var html = '<div class="airb__res-stat airb__metric-card airb__metric-card--leader airb__metric-card--tone-' + tone + (opts.higherIsWorse ? ' airb__metric-card--risk' : '') + '">';
		html += '<div class="airb__metric-card-lab">' + esc(opts.label || '') + '</div>';
		html += '<div class="airb__metric-card-score-row">';
		if (opts.pct === '—') {
			html += '<span class="airb__metric-card-pct airb__res-stat-big--na">—</span>';
		} else {
			html += '<span class="airb__metric-card-pct" style="color:' + esc(opts.pctColor || 'inherit') + '">' + pctDisplay + '</span>';
		}
		if (opts.signal) {
			html += '<span class="airb__metric-signal airb__metric-signal--' + tone + '">' + esc(opts.signal) + '</span>';
		}
		html += '</div>';
		if (opts.note) {
			html += '<p class="airb__metric-card-note airb__muted">' + esc(opts.note) + '</p>';
		}
		if (opts.consequence) {
			html += '<p class="airb__metric-card-consequence">' + esc(opts.consequence) + '</p>';
		}
		return html + '</div>';
	}

	function leaderSignalLine(bandLabel, signal) {
		if (!signal) return bandLabel || '';
		var tail = signal.charAt(0).toLowerCase() + signal.slice(1);
		return bandLabel ? (bandLabel + ' — ' + tail) : signal;
	}

	function leaderUiMetric(uiBlock, type, slug) {
		if (uiBlock && (uiBlock.signal || uiBlock.consequence)) {
			return {
				signal: uiBlock.signal || '',
				tone: metricToneClass(uiBlock.tone || 'neutral'),
				consequence: uiBlock.consequence || '',
			};
		}
		return leaderMetricSignals(type, slug);
	}

	function leaderReadinessHeroHtml(score, uiHero) {
		score = Math.max(0, Math.min(100, parseInt(score, 10) || 0));
		var bandLabel = readinessBandLabel(score, state.role);
		var bandSlug = readinessLevel(score, state.role).slug;
		var heroSig = uiHero && (uiHero.signal || uiHero.consequence)
			? leaderUiMetric(uiHero, 'readiness', bandSlug)
			: leaderMetricSignals('readiness', bandSlug);
		var tone = heroSig.tone || 'neutral';
		var bands = roleHeroBandDefinitions(state.role);
		var signalLine = state.role === 'public'
			? bandLabel
			: leaderSignalLine(bandLabel, heroSig.signal);
		var heroCfg = state.role === 'teacher' ? teacherResult
			: state.role === 'support_staff' ? supportResult
			: state.role === 'student' ? studentResult
			: state.role === 'parent' ? parentResult
			: leaderResult;
		var kicker = state.role === 'public'
			? (publicResult.hero_metric_label || 'Overall AI safety score')
			: (heroCfg.metric_labels && heroCfg.metric_labels.readiness
				? heroCfg.metric_labels.readiness
				: (i18n.readinessScaleKicker || 'Overall benchmark readiness'));

		var html = '<div class="airb__leader-hero airb__leader-hero--tone-' + tone + '" role="img" aria-label="' + esc(
			(i18n.readinessScaleAria || 'Overall benchmark readiness {score} out of 100, {band}')
				.replace('{score}', String(score))
				.replace('{band}', signalLine || bandLabel)
		) + '">';
		html += '<div class="airb__leader-hero-head">';
		html += '<span class="airb__leader-hero-pct">' + score + '%</span>';
		html += '<div class="airb__leader-hero-meta">';
		if (heroSig.signal) {
			html += '<div class="airb__leader-hero-signal airb__leader-hero-signal--desktop">' + esc(signalLine) + '</div>';
			html += '<div class="airb__leader-hero-signal-mobile" aria-hidden="true">';
			html += '<div class="airb__leader-hero-band">' + esc(bandLabel) + '</div>';
			html += '<div class="airb__leader-hero-action">' + esc(heroSig.signal) + '</div>';
			html += '</div>';
		}
		html += '<div class="airb__leader-hero-kicker">' + esc(kicker) + '</div>';
		html += '</div></div>';
		if (heroSig.consequence) {
			html += '<p class="airb__leader-hero-consequence">' + esc(heroSig.consequence) + '</p>';
		}
		html += '<div class="airb__leader-hero-bar" aria-hidden="true">';
		bands.forEach(function (b) {
			html += '<span class="airb__leader-hero-seg airb__leader-hero-seg--' + b.slug + (bandSlug === b.slug ? ' is-active' : '') + '"></span>';
		});
		html += '</div>';
		html += '<div class="airb__leader-hero-bar-labels" aria-hidden="true">';
		bands.forEach(function (b) {
			html += heroBarBandLabelHtml(b, publicHeroBarShortLabelFn(state.role));
		});
		html += '</div></div>';
		return html;
	}

	function publicSummaryMetricColor(slug) {
		if (slug === 'good') return '#3B6D11';
		if (slug === 'developing') return '#185FA5';
		if (slug === 'attention') return '#854F0B';
		return '#A32D2D';
	}

	function publicSummaryMetricsGridHtml(metrics) {
		if (!metrics || !metrics.length) return '';
		var html = '<div class="airb__public-metric-grid">';
		metrics.forEach(function (row) {
			var pct = parseInt(row.value, 10) || 0;
			var badge = row.badge || {};
			var color = publicSummaryMetricColor(badge.slug || 'developing');
			html += '<div class="airb__public-metric-cell">';
			html += '<div class="airb__public-metric-lbl">' + esc(row.label) + '</div>';
			html += '<div class="airb__public-metric-val" style="color:' + esc(color) + '">' + pct + '%</div>';
			if (badge.label) {
				html += '<div class="airb__public-metric-sub" style="color:' + esc(color) + '">' + esc(badge.label) + '</div>';
			}
			if (badge.note) {
				html += '<p class="airb__public-metric-desc">' + esc(badge.note) + '</p>';
			}
			html += '</div>';
		});
		return html + '</div>';
	}

	function publicDomainBadgeClass(slug) {
		if (slug === 'good') return 'good';
		if (slug === 'risk') return 'critical';
		if (slug === 'developing') return 'developing';
		return 'moderate';
	}

	function publicDomainScoresCardHtml(domainRows) {
		if (!domainRows || !domainRows.length) return '';
		var rows = '';
		domainRows.forEach(function (row) {
			var pct = parseInt(row.pct, 10) || 0;
			var color = row.color || readinessBandColor(pct);
			var badge = row.badge || { slug: 'developing', label: 'Developing' };
			rows += '<div class="airb__public-domain-row">';
			rows += '<span class="airb__public-domain-label">' + esc(row.label) + '</span>';
			rows += '<div class="airb__public-domain-bar-wrap"><div class="airb__public-domain-bar" style="width:' + pct + '%;background:' + esc(color) + '"></div></div>';
			rows += '<span class="airb__public-domain-val" style="color:' + esc(color) + '">' + pct + '%</span>';
			rows += '<span class="airb__public-domain-badge airb__public-domain-badge--' + publicDomainBadgeClass(badge.slug) + '">' + esc(badge.label) + '</span>';
			rows += '</div>';
		});
		if (!rows) return '';
		var headingShort = publicResult.domains_section_heading_short || '5 domains';
		return '<div class="airb__public-domain-card">' + benchmarkCardHeadingHtml(headingShort) + rows + '</div>';
	}

	function publicShareCardHtml(pr) {
		if (!pr || !pr.share) return '';
		var share = pr.share;
		var html = '<div class="airb__public-share-card">';
		html += '<div class="airb__public-share-kicker">' + esc(publicResult.share_section_kicker || 'Share your results') + '</div>';
		html += '<h4 class="airb__public-share-title">' + esc(publicResult.share_section_title || 'Most people don\'t know how they really use AI') + '</h4>';
		html += '<p class="airb__public-share-body">' + esc(publicResult.share_section_body || '') + '</p>';
		html += '<div class="airb__public-share-preview">';
		html += '<div class="airb__public-share-preview-lbl">' + esc(publicResult.share_preview_label || 'Your shareable result') + '</div>';
		html += '<div class="airb__public-share-preview-headline">' + esc(share.headline || '') + '</div>';
		if (share.subline) {
			html += '<div class="airb__public-share-preview-sub">' + esc(share.subline) + '</div>';
		}
		html += '</div>';
		html += '<div class="airb__public-share-actions">';
		html += '<button type="button" class="airb__btn airb__public-share-btn airb__public-share-btn--primary" id="airb-public-share-social">' + esc(publicResult.share_cta_primary || 'Share on social') + ' ↗</button>';
		html += '<button type="button" class="airb__btn airb__public-share-btn airb__public-share-btn--secondary" data-airb-public-retake="1">' + esc(publicResult.share_cta_retake || i18n.retakeAudit || 'Retake the benchmark') + ' ↗</button>';
		html += '<button type="button" class="airb__btn airb__public-share-btn airb__public-share-btn--secondary" data-airb-open-interest="public_resources">' + esc(publicResult.share_cta_guide || 'Get your safety guide') + ' ↗</button>';
		html += '</div></div>';
		return html;
	}

	function restartPublicAudit() {
		state.phase = 'role';
		state.step = 0;
		state.answers = {};
		state.results = null;
		state.submissionId = 0;
		renderRole();
	}

	function bindPublicRetakeTriggers() {
		el.results.querySelectorAll('[data-airb-public-retake]').forEach(function (btn) {
			btn.addEventListener('click', function (e) {
				e.preventDefault();
				restartPublicAudit();
			});
		});
		var shareBtn = document.getElementById('airb-public-share-social');
		if (shareBtn) {
			shareBtn.addEventListener('click', function () {
				var text = buildShareScoreText(state.results);
				if (!text) return;
				if (navigator.share) {
					navigator.share({ text: text, url: (window.location.href || '').split('#')[0] }).catch(function () {});
					trackEvent('share_click', { channel: 'native' });
					return;
				}
				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(text).then(function () {
						shareBtn.textContent = (i18n.copiedResult || 'Copied!') + ' ↗';
						trackEvent('share_copy', { channel: 'clipboard' });
						setTimeout(function () {
							shareBtn.textContent = (publicResult.share_cta_primary || 'Share on social') + ' ↗';
						}, 2000);
					});
				}
			});
		}
	}

	function leaderSupportingCardHtml(opts) {
		opts = opts || {};
		var tone = metricToneClass(opts.tone || 'warning');
		var html = '<div class="airb__leader-support-card airb__leader-support-card--tone-' + tone + '">';
		html += '<div class="airb__leader-support-label">' + esc(opts.label || '') + '</div>';
		if (opts.pct === '—') {
			html += '<div class="airb__leader-support-pct airb__leader-support-pct--na">—</div>';
		} else {
			html += '<div class="airb__leader-support-pct">' + opts.pct + '%</div>';
		}
		if (opts.signal) {
			html += '<div class="airb__leader-support-signal">' + esc(opts.signal) + '</div>';
		}
		if (opts.consequence) {
			html += '<p class="airb__leader-support-note">' + esc(opts.consequence) + '</p>';
		}
		return html + '</div>';
	}

	function teacherDependencyTier(dep) {
		dep = parseInt(dep, 10);
		if (isNaN(dep)) dep = 0;
		if (dep >= 80) return 'high';
		if (dep >= 60) return 'moderate_high';
		if (dep >= 40) return 'moderate';
		if (dep >= 20) return 'low';
		return 'minimal';
	}

	function teacherUiMetric(uiBlock, type, slug) {
		if (uiBlock && (uiBlock.signal || uiBlock.consequence)) {
			return {
				signal: uiBlock.signal || '',
				tone: metricToneClass(uiBlock.tone || 'neutral'),
				consequence: uiBlock.consequence || '',
			};
		}
		if (type === 'risk') {
			return leaderMetricSignals('risk', slug);
		}
		var bag = (teacherResult.metric_signals || {})[type] || {};
		var entry = bag[slug] || {};
		return {
			signal: entry.signal || '',
			tone: metricToneClass(entry.tone || 'neutral'),
			consequence: entry.consequence || '',
		};
	}

	function teacherBiasMetricSignals(slug) {
		var bag = (teacherResult.copy_tiers || {}).bias || (teacherResult.metric_signals || {}).bias || {};
		var entry = bag[slug] || {};
		return {
			signal: entry.signal || '',
			tone: metricToneClass(entry.tone || 'neutral'),
			consequence: entry.consequence || '',
		};
	}

	function teacherBiasEqualityFocusNote(score) {
		score = parseInt(score, 10);
		if (isNaN(score) || score >= 50) return '';
		var tiers = teacherResult.focus_tiers && teacherResult.focus_tiers.bias_equality;
		if (!tiers) return '';
		var block = tiers[leaderBiasTier(score)] || {};
		return block.summary || '';
	}

	function teacherSupportingMetricsHtml(r, risk) {
		var ui = r.teacher_results && r.teacher_results.ui ? r.teacher_results.ui : null;
		var labels = teacherResult.metric_labels || {};
		var riskLvl = r.risk_level || riskBand(risk);
		var riskSig = ui && ui.risk_card ? teacherUiMetric(ui.risk_card, 'risk', riskLvl) : leaderMetricSignals('risk', riskLvl);
		var dep = r.dependency_index;
		var depTier = teacherDependencyTier(dep);
		var depSig = ui && ui.dependency_card ? teacherUiMetric(ui.dependency_card, 'dependency', depTier) : teacherUiMetric(null, 'dependency', depTier);
		var hoVal = oversightGaugeValue(r);
		var hoUi = ui && ui.oversight ? ui.oversight : null;
		var hoSig = hoUi && hoUi.signal ? hoUi : { signal: r.human_oversight_label || '', tone: 'positive', consequence: teacherResult.oversight_metric_note || '' };
		var biasScore = r.bias_readiness != null ? r.bias_readiness : null;
		var hasBias = biasScore !== null;
		var html = '<div class="airb__teacher-metric-grid' + (hasBias ? ' airb__teacher-metric-grid--with-bias' : '') + '">';
		html += '<div class="airb__teacher-metric-cell airb__teacher-metric-cell--tone-' + (riskSig.tone || 'neutral') + '">';
		html += '<div class="airb__teacher-metric-lbl">' + esc(labels.risk || i18n.leaderMetricRisk || 'AI risk exposure') + '</div>';
		html += '<div class="airb__teacher-metric-val" style="color:' + esc(riskScoreColor(risk)) + '">' + risk + '%</div>';
		html += '<div class="airb__teacher-metric-sub" style="color:' + esc(riskScoreColor(risk)) + '">' + esc(riskSig.signal || displayRiskLabel(riskLvl, risk)) + '</div>';
		if (riskSig.consequence) html += '<p class="airb__teacher-metric-desc">' + esc(riskSig.consequence) + '</p>';
		html += '</div>';
		html += '<div class="airb__teacher-metric-cell airb__teacher-metric-cell--tone-' + (depSig.tone || 'warning') + '">';
		html += '<div class="airb__teacher-metric-lbl">' + esc(labels.dependency || i18n.dependency || 'AI Dependency Index') + '</div>';
		if (dep === null || dep === undefined) {
			html += '<div class="airb__teacher-metric-val airb__teacher-metric-val--na">—</div>';
		} else {
			html += '<div class="airb__teacher-metric-val" style="color:' + esc(dependencyColor(dep)) + '">' + dep + '%</div>';
			html += '<div class="airb__teacher-metric-sub" style="color:' + esc(dependencyColor(dep)) + '">' + esc(depSig.signal || '') + '</div>';
			if (depSig.consequence) html += '<p class="airb__teacher-metric-desc">' + esc(depSig.consequence) + '</p>';
		}
		html += '</div>';
		if (hoVal !== null) {
			html += '<div class="airb__teacher-metric-cell airb__teacher-metric-cell--tone-' + metricToneClass(hoSig.tone || 'positive') + '">';
			html += '<div class="airb__teacher-metric-lbl">' + esc(i18n.humanOversightRatio || 'Human Oversight Ratio') + '</div>';
			html += '<div class="airb__teacher-metric-val" style="color:' + esc(oversightZoneColor(hoVal)) + '">' + hoVal + '%</div>';
			html += '<div class="airb__teacher-metric-sub" style="color:' + esc(oversightZoneColor(hoVal)) + '">' + esc(hoSig.signal || '') + '</div>';
			html += '<p class="airb__teacher-metric-desc">' + esc(teacherResult.oversight_metric_note || 'Share of AI output reviewed or changed before use.') + '</p>';
			html += '</div>';
		}
		if (hasBias) {
			var biasLvl = leaderBiasTier(biasScore);
			var biasSig = ui && ui.bias_card
				? teacherUiMetric(ui.bias_card, 'bias', biasLvl)
				: teacherBiasMetricSignals(biasLvl);
			var biasHealth = r.teacher_results && r.teacher_results.bias_health ? r.teacher_results.bias_health : null;
			var biasConsequence = biasSig.consequence || '';
			if (!biasConsequence && biasHealth && biasHealth.show_callout && biasHealth.callout) {
				biasConsequence = biasHealth.callout;
			}
			html += '<div class="airb__teacher-metric-cell airb__teacher-metric-cell--tone-' + metricToneClass(biasSig.tone || 'neutral') + '">';
			html += '<div class="airb__teacher-metric-lbl">' + esc(labels.bias || i18n.biasReadiness || 'Bias & equality readiness') + '</div>';
			html += '<div class="airb__teacher-metric-val" style="color:' + esc(readinessBandColor(biasScore)) + '">' + biasScore + '%</div>';
			html += '<div class="airb__teacher-metric-sub" style="color:' + esc(readinessBandColor(biasScore)) + '">' + esc(biasSig.signal || (biasHealth && biasHealth.band_label) || '') + '</div>';
			if (biasConsequence) html += '<p class="airb__teacher-metric-desc">' + esc(biasConsequence) + '</p>';
			html += '</div>';
		}
		return html + '</div>';
	}

	function oversightTierFromPct(pct) {
		if (pct <= 10) return 'critical';
		if (pct <= 25) return 'high';
		if (pct <= 50) return 'moderate';
		if (pct <= 75) return 'strong';
		return 'exemplary';
	}

	function oversightCopyFromRegistry(role, pct) {
		var tiers = copyTiersRegistry[role] || {};
		var bands = tiers.oversight || {};
		var key;
		for (key in bands) {
			if (!Object.prototype.hasOwnProperty.call(bands, key)) continue;
			var t = bands[key];
			if (pct >= t.min && pct <= t.max) {
				return {
					signal: t.signal || '',
					consequence: t.consequence || '',
					tone: t.tone || 'neutral',
				};
			}
		}
		return null;
	}

	function oversightUiCopy(r, pct) {
		var teacherOversight = isTeacherRole() && r.teacher_results && r.teacher_results.ui ? r.teacher_results.ui.oversight : null;
		if (teacherOversight && teacherOversight.signal) return teacherOversight;

		var role = state.role || 'teacher';
		var fromRegistry = oversightCopyFromRegistry(role, pct);
		if (fromRegistry && (fromRegistry.signal || fromRegistry.consequence)) {
			return fromRegistry;
		}

		var tier = oversightTierFromPct(pct);
		var resultCfg = teacherResult;
		if (isPublicRole()) resultCfg = publicResult;
		else if (isStudentRole()) resultCfg = studentResult;
		else if (isLeaderRole()) resultCfg = leaderResult;
		else if (isSupportStaffRole()) resultCfg = supportResult;
		else if (isParentRole()) resultCfg = parentResult;

		var tiers = (resultCfg.copy_tiers || {}).oversight || {};
		var block = tiers[tier] || {};
		return {
			signal: block.signal || r.human_oversight_label || oversightLabel(pct),
			consequence: block.consequence || '',
			tone: block.tone || 'neutral',
		};
	}

	function benchmarkOversightGaugeSectionHtml(r) {
		var panel = oversightPanelHtml(r);
		if (!panel) return '';
		return '<div class="airb__benchmark-oversight-section airb__teacher-oversight-section">' + panel + '</div>';
	}

	function teacherOversightGaugeSectionHtml(r) {
		return benchmarkOversightGaugeSectionHtml(r);
	}

	function teacherDomainSectionHtml(r) {
		var rows = '';
		var domainHints = teacherResult.domain_descriptions || {};
		domainKeys.forEach(function (slug) {
			var d = r.domain_scores[slug];
			if (!d || !d.questions_answered) return;
			var pct = Math.round(d.readiness_percentage);
			var color = readinessBandColor(pct);
			var hint = domainHints[slug] || '';
			rows += '<div class="airb__teacher-domain-row">';
			rows += '<span class="airb__teacher-domain-label"' + (hint ? ' title="' + esc(hint) + '"' : '') + '>' + esc(d.label) + '</span>';
			rows += '<div class="airb__teacher-domain-bar-wrap"><div class="airb__teacher-domain-bar" style="width:' + pct + '%;background:' + esc(color) + '"></div></div>';
			rows += '<span class="airb__teacher-domain-val" style="color:' + esc(color) + '">' + pct + '%</span>';
			rows += '</div>';
		});
		if (!rows) return '';
		var headingShort = teacherResult.domains_section_heading_short || 'By domain';
		return '<div class="airb__teacher-domain-card">' + benchmarkCardHeadingHtml(headingShort) + rows + '</div>';
	}

	function teacherRolloutCardHtml(ro) {
		if (!ro) return '';
		var html = leaderRolloutCardHtml(ro);
		return html.replace('airb__leader-rollout-card', 'airb__leader-rollout-card airb__leader-rollout-card--teacher');
	}

	function teacherPathwayCtaHtml(nextSteps) {
		if (!nextSteps || !nextSteps.hero) return '';
		var hero = nextSteps.hero;
		var heading = nextSteps.hero_heading || teacherResult.hero_next_step_heading || 'Your next step';
		var headingShort = nextSteps.hero_next_step_heading_short || teacherResult.hero_next_step_heading_short || 'Your next step';
		var deliverables = hero.deliverables && hero.deliverables.length ? hero.deliverables : (hero.understand_items || []);
		var isPathway = !!(hero.pathway_kicker || (hero.secondary_cta_text && hero.secondary_key));
		var html = '<article class="' + (isPathway ? 'airb__teacher-pathway-card' : 'airb__leader-cta-card') + '">';
		if (hero.pathway_kicker) {
			html += '<div class="airb__teacher-pathway-kicker">' + esc(hero.pathway_kicker) + '</div>';
		}
		html += '<h4 class="' + (isPathway ? 'airb__teacher-pathway-title' : 'airb__leader-cta-title') + '">' + esc(hero.title) + '</h4>';
		if (hero.body) {
			html += '<p class="' + (isPathway ? 'airb__teacher-pathway-body' : 'airb__leader-cta-body') + '">' + esc(hero.body) + '</p>';
		}
		if (deliverables.length) {
			html += '<div class="' + (isPathway ? 'airb__teacher-pathway-deliverables' : 'airb__leader-cta-deliverables') + '" role="list">';
			deliverables.forEach(function (item) {
				if (isPathway) {
					html += '<div class="airb__teacher-pathway-item" role="listitem"><span class="airb__teacher-pathway-arrow" aria-hidden="true">→</span><span>' + esc(item) + '</span></div>';
				} else {
					html += '<span class="airb__leader-cta-deliverable" role="listitem">' + esc(item) + '</span>';
				}
			});
			html += '</div>';
		}
		html += '<div class="airb__teacher-pathway-actions">';
		html += '<button type="button" class="airb__btn airb__btn--premium' + (isPathway ? ' airb__teacher-pathway-btn airb__teacher-pathway-btn--primary' : ' airb__leader-cta-btn') + '" data-airb-open-interest="' + esc(hero.key || 'whole_school_cpd') + '">' + esc(hero.cta_text || 'Request support') + '</button>';
		if (hero.secondary_cta_text && hero.secondary_key) {
			html += '<button type="button" class="airb__btn airb__teacher-pathway-btn airb__teacher-pathway-btn--secondary" data-airb-open-interest="' + esc(hero.secondary_key) + '">' + esc(hero.secondary_cta_text) + '</button>';
		}
		html += '</div></article>';
		return html;
	}

	function benchmarkCardHeadingHtml(text) {
		return '<h3 class="airb__benchmark-card-heading">' + esc(text) + '</h3>';
	}

	function benchmarkShareUrl() {
		return (i18n.shareSiteUrl || airbBenchmark.homeUrl || window.location.href || '').trim();
	}

	function benchmarkShareFocus(model) {
		if (!model) return '';
		if (model.focusAreas && model.focusAreas.length && model.focusAreas[0].label) {
			return String(model.focusAreas[0].label);
		}
		if (model.domains && model.domains.length) {
			var weakest = model.domains.reduce(function (min, domain) {
				return !min || domain.value < min.value ? domain : min;
			}, null);
			if (weakest && weakest.label) return String(weakest.label);
		}
		return '';
	}

	function benchmarkShareText(model) {
		var role = model && model.label ? String(model.label).toLowerCase() : 'participant';
		var focus = benchmarkShareFocus(model);
		var action = model && (model.priority || model.nextAction || model.motif) ? String(model.priority || model.nextAction || model.motif) : '';
		var text = 'I completed the AI Risk & Readiness Benchmark with AI Awareness Day as a ' + role + '.';
		if (focus) text += ' My focus is ' + focus + '.';
		if (action) text += ' My next action: ' + action;
		return text;
	}

	function dashboardSharePromptHtml() {
		var model = state.dashboardModel;
		if (!model) return '';
		var focus = benchmarkShareFocus(model);
		var action = model.priority || model.nextAction || model.motif || '';
		var shareText = benchmarkShareText(model);
		var url = benchmarkShareUrl();
		return '<section class="airb__dashboard-share" aria-label="Share your benchmark action">' +
			'<div class="airb__dashboard-share-copy">' +
			'<p class="airb__leader-section-label">Share your next AI action</p>' +
			'<h3 class="airb__dashboard-share-title">Turn your result into a public commitment</h3>' +
			'<p class="airb__dashboard-share-body">Share the benchmark and the one habit you are going to strengthen. Your score stays private.</p>' +
			(action ? '<p class="airb__dashboard-share-action"><strong>' + esc(focus || 'Focus') + ':</strong> ' + esc(action) + '</p>' : '') +
			'</div>' +
			'<div class="airb__dashboard-share-actions">' +
			'<button type="button" class="airb__btn airb__btn--primary airb__dashboard-share-btn" data-airb-share-action data-airb-share-title="AI Risk & Readiness Benchmark" data-airb-share-url="' + esc(url) + '" data-airb-share-text="' + esc(shareText) + '">Share my AI action</button>' +
			'<p class="airb__muted airb__dashboard-share-status" data-airb-share-action-status hidden role="status" aria-live="polite"></p>' +
			'</div>' +
			'</section>';
	}

	function benchmarkResultsBodyHtml(html) {
		if (!html) return '';
		return '<div class="airb__benchmark-results-body">' + html + dashboardSharePromptHtml() + '</div>';
	}

	function benchmarkHelpSupportHtml(nextSteps, heading, headingShort) {
		if (!nextSteps || !nextSteps.resource_links || !nextSteps.resource_links.length) return '';
		heading = heading || i18n.helpSupportHeading || 'Further reading and tips to guide you';
		headingShort = headingShort || i18n.helpSupportHeadingShort || 'Read more & tips';
		return '<section class="airb__leader-help-support airb__benchmark-help-support">' +
			leaderSectionLabel(heading, headingShort) +
			resultsResourceLinksHtml(nextSteps.resource_links, { cardMode: true }) +
			'</section>';
	}

	function teacherHelpSupportHtml(nextSteps) {
		if (!nextSteps || !nextSteps.resource_links || !nextSteps.resource_links.length) return '';
		return benchmarkHelpSupportHtml(
			nextSteps,
			nextSteps.help_support_heading || teacherResult.help_support_heading,
			nextSteps.help_support_heading_short || teacherResult.help_support_heading_short
		);
	}

	function studentHelpSupportHtml(nextSteps) {
		if (!nextSteps || !nextSteps.resource_links || !nextSteps.resource_links.length) return '';
		return benchmarkHelpSupportHtml(
			nextSteps,
			nextSteps.help_support_heading || studentResult.help_support_heading,
			nextSteps.help_support_heading_short || studentResult.help_support_heading_short
		);
	}

	function teacherPeerBenchmarkHtml(tr) {
		if (!tr || !tr.peer_benchmark) return '';
		var pb = tr.peer_benchmark;
		var avgGap = typeof pb.gap_vs_average === 'number' ? pb.gap_vs_average : ((parseInt(pb.average_score, 10) || 0) - (parseInt(pb.your_score, 10) || 0));
		var topGap = typeof pb.gap_vs_top_quartile === 'number' ? pb.gap_vs_top_quartile : ((parseInt(pb.top_quartile, 10) || 0) - (parseInt(pb.your_score, 10) || 0));
		var yourColor = readinessBandColor(parseInt(pb.your_score, 10) || 0);
		if (avgGap <= 0 && topGap <= 0) {
			yourColor = 'var(--airb-good, #1d9e75)';
		}
		return peerBenchmarkBarHtml(pb, {
			cfg: teacherResult,
			yourScoreColor: yourColor,
			comparisonLabel: teacherResult.peer_comparison_label || i18n.teacherPeerComparison || 'How you compare to other teachers',
			comparisonLabelShort: teacherResult.peer_comparison_label_short || i18n.peerComparisonShort || 'How you compare',
			youLabel: teacherResult.peer_you_label || i18n.peerYou || 'You',
			avgLong: teacherResult.peer_average_label || i18n.teacherNationalAverage || 'National average',
			avgShort: teacherResult.peer_average_label_short || i18n.teacherNationalAverageShort || 'Nat. avg',
			avgMobile: teacherResult.peer_average_label_short || i18n.teacherNationalAverageShort || 'Nat. avg',
			topLabel: i18n.teacherTopQuartileShort || i18n.studentTopQuartileShort || 'Top quartile',
			estimatedNote: i18n.teacherPeerEstimated || i18n.peerEstimated || 'Comparison uses reference benchmarks until enough teachers have completed the audit.',
			percentileTemplate: i18n.teacherPeerPercentile || '',
		});
	}

	function studentPeerBenchmarkHtml(sr) {
		if (!sr || !sr.peer_benchmark) return '';
		var pb = sr.peer_benchmark;
		var avgGap = (parseInt(pb.average_score, 10) || 0) - (parseInt(pb.your_score, 10) || 0);
		var topGap = (parseInt(pb.top_quartile, 10) || 0) - (parseInt(pb.your_score, 10) || 0);
		var yourColor = studentSkillColor(parseInt(pb.your_score, 10) || 0);
		if (avgGap <= 0 && topGap <= 0) {
			yourColor = 'var(--airb-good, #1d9e75)';
		}
		return peerBenchmarkBarHtml(pb, {
			cfg: studentResult,
			yourScoreColor: yourColor,
			comparisonLabel: studentResult.peer_comparison_label || i18n.studentPeerComparison || 'How you compare to other students',
			comparisonLabelShort: studentResult.peer_comparison_label_short || i18n.peerComparisonShort || 'How you compare',
			youLabel: studentResult.peer_you_label || i18n.peerYou || 'You',
			avgLong: i18n.studentAverage || 'Average students',
			avgShort: 'Avg students',
			avgMobile: 'Avg students',
			topLabel: i18n.studentTopQuartileShort || 'Top quartile',
			estimatedNote: i18n.studentPeerEstimated || i18n.peerEstimated || 'Comparison uses reference benchmarks until enough students have completed the audit.',
			percentileTemplate: i18n.studentPeerPercentile || '',
		});
	}

	function leaderBiasTier(score) {
		score = parseInt(score, 10) || 0;
		if (score < 25) return 'critical';
		if (score < 50) return 'high';
		if (score < 75) return 'moderate';
		return 'low';
	}

	function leaderBiasEqualityFocusNote(score) {
		score = parseInt(score, 10);
		if (isNaN(score) || score >= 50) return '';
		var tiers = leaderResult.focus_tiers && leaderResult.focus_tiers.bias_equality;
		if (!tiers) return '';
		var block = tiers[leaderBiasTier(score)] || {};
		return block.summary || '';
	}

	function leaderSupportingMetricsHtml(r, risk) {
		var metricLabels = leaderResult.metric_labels || {};
		var ui = r.leader_results && r.leader_results.ui ? r.leader_results.ui : null;
		var riskLvl = r.risk_level || riskBand(risk);
		var riskSig = ui && ui.risk_card ? leaderUiMetric(ui.risk_card, 'risk', riskLvl) : leaderMetricSignals('risk', riskLvl);
		var govScore = r.governance_maturity != null ? r.governance_maturity : null;
		var biasScore = r.bias_readiness != null ? r.bias_readiness : null;
		var hasBias = biasScore !== null;
		var html = '<div class="airb__leader-support-grid' + (hasBias ? ' airb__leader-support-grid--three' : '') + '">';
		html += leaderSupportingCardHtml({
			label: metricLabels.risk || i18n.leaderMetricRisk || 'AI risk exposure',
			pct: risk,
			signal: riskSig.signal || displayRiskLabel(riskLvl, risk),
			tone: riskSig.tone,
			consequence: riskSig.consequence || leaderResult.risk_score_note || '',
		});
		if (govScore === null) {
			html += leaderSupportingCardHtml({
				label: metricLabels.governance || i18n.governanceMaturity || 'Governance maturity',
				pct: '—',
				tone: 'warning',
			});
		} else {
			var govLvl = leaderGovernanceLevel(govScore);
			var govSig = ui && ui.governance_card
				? leaderUiMetric(ui.governance_card, 'governance', govLvl.slug)
				: leaderMetricSignals('governance', govLvl.slug);
			html += leaderSupportingCardHtml({
				label: metricLabels.governance || i18n.governanceMaturity || 'Governance maturity',
				pct: govScore,
				signal: govSig.signal,
				tone: govSig.tone,
				consequence: govSig.consequence,
			});
		}
		if (hasBias) {
			var biasLvl = leaderBiasTier(biasScore);
			var biasSig = ui && ui.bias_card
				? leaderUiMetric(ui.bias_card, 'bias', biasLvl)
				: leaderMetricSignals('bias', biasLvl);
			var biasHealth = r.leader_results && r.leader_results.bias_health ? r.leader_results.bias_health : null;
			var biasConsequence = biasSig.consequence || '';
			if (!biasConsequence && biasHealth && biasHealth.show_callout && biasHealth.callout) {
				biasConsequence = biasHealth.callout;
			}
			html += leaderSupportingCardHtml({
				label: metricLabels.bias || i18n.biasReadiness || 'Bias & equality readiness',
				pct: biasScore,
				signal: biasSig.signal || (biasHealth && biasHealth.band_label) || '',
				tone: biasSig.tone,
				consequence: biasConsequence,
			});
		}
		return html + '</div>';
	}

	function peerGapText(gap, kind, cfg) {
		cfg = cfg || leaderResult;
		gap = parseInt(gap, 10);
		if (isNaN(gap)) gap = 0;
		var tpl;
		if (kind === 'top') {
			if (gap > 0) {
				tpl = cfg.peer_gap_below_top || i18n.peerGapBelowTop || '{n} points below top quartile';
			} else if (gap < 0) {
				tpl = cfg.peer_gap_above_top || i18n.peerGapAboveTop || '{n} points above top quartile';
			} else {
				return cfg.peer_gap_at_top || i18n.peerGapAtTop || 'In line with top quartile';
			}
		} else {
			if (gap > 0) {
				tpl = cfg.peer_gap_below_average || i18n.peerGapBelowAverage || '{n} points below average';
			} else if (gap < 0) {
				tpl = cfg.peer_gap_above_average || i18n.peerGapAboveAverage || '{n} points above average';
			} else {
				return cfg.peer_gap_at_average || i18n.peerGapAtAverage || 'In line with average';
			}
		}
		return tpl.replace('{n}', String(Math.abs(gap)));
	}

	function peerBenchmarkBarHtml(pb, opts) {
		opts = opts || {};
		if (!pb) return '';
		var cfg = opts.cfg || leaderResult;
		var avgGap = typeof pb.gap_vs_average === 'number' ? pb.gap_vs_average : ((parseInt(pb.average_score, 10) || 0) - (parseInt(pb.your_score, 10) || 0));
		var topGap = typeof pb.gap_vs_top_quartile === 'number' ? pb.gap_vs_top_quartile : ((parseInt(pb.top_quartile, 10) || 0) - (parseInt(pb.your_score, 10) || 0));
		var yourColor = opts.yourScoreColor || (avgGap > 0 ? 'var(--airb-crit, #a32d2d)' : (topGap <= 0 ? 'var(--airb-good, #1d9e75)' : 'inherit'));
		var avgLong = String(opts.avgLong || pb.phase_label || i18n.parentAverage || 'Average');
		var avgShort = opts.avgShort || avgLong.replace(/^Average\s+/i, 'Avg ');
		var avgMobile = opts.avgMobile || cfg.peer_phase_short || i18n.avgSchool || avgShort;
		var topLabel = opts.topLabel || i18n.topQuartile || 'Top quartile';
		var gapAvg = peerGapText(avgGap, 'average', cfg);
		var gapTop = peerGapText(topGap, 'top', cfg);
		var gapTopShortTpl = cfg.peer_gap_below_top_short || i18n.peerGapBelowTopShort || '{n} below top quartile';
		var gapTopShort = topGap > 0
			? gapTopShortTpl.replace('{n}', String(topGap))
			: (topGap < 0
				? (cfg.peer_gap_above_top_short || '{n} above top quartile').replace('{n}', String(Math.abs(topGap)))
				: (cfg.peer_gap_at_top_short || 'In line with top quartile'));
		var comparisonLong = opts.comparisonLabel || cfg.peer_comparison_label || i18n.peerComparisonLabel || 'How you compare';
		var comparisonShort = opts.comparisonLabelShort || cfg.peer_comparison_label_short || i18n.peerComparisonShort || 'How you compare';
		var youLabel = opts.youLabel || cfg.peer_you_label || i18n.peerYou || 'You';
		var estimatedNote = opts.estimatedNote || i18n.peerEstimated || 'Comparison uses reference benchmarks until enough responses have been collected.';
		var html = '<section class="airb__leader-peer">';
		html += '<div class="airb__leader-peer-inner">';
		html += '<div class="airb__leader-peer-scores">';
		html += '<span class="airb__leader-peer-label">' + leaderResponsiveLabel(comparisonLong, comparisonShort) + '</span>';
		html += '<div class="airb__leader-peer-row">';
		html += '<div class="airb__leader-peer-stat"><div class="airb__leader-peer-val" style="color:' + esc(yourColor) + '">' + pb.your_score + '%</div><div class="airb__leader-peer-sub">' + esc(youLabel) + '</div></div>';
		html += '<span class="airb__leader-peer-divider" aria-hidden="true"></span>';
		html += '<div class="airb__leader-peer-stat"><div class="airb__leader-peer-val">' + pb.average_score + '%</div><div class="airb__leader-peer-sub"><span class="airb__peer-phase-long">' + esc(avgShort) + '</span><span class="airb__peer-phase-short">' + esc(avgMobile) + '</span></div></div>';
		html += '<span class="airb__leader-peer-divider" aria-hidden="true"></span>';
		html += '<div class="airb__leader-peer-stat"><div class="airb__leader-peer-val airb__leader-peer-val--top">' + pb.top_quartile + '%</div><div class="airb__leader-peer-sub">' + esc(topLabel) + '</div></div>';
		html += '</div>';
		html += '<p class="airb__leader-peer-gaps-combined">' + esc(gapAvg + ' · ' + gapTopShort) + '</p>';
		html += '</div>';
		html += '<div class="airb__leader-peer-gaps">';
		html += '<div class="airb__leader-peer-gap-primary">' + esc(gapAvg) + '</div>';
		html += '<div class="airb__leader-peer-gap-secondary">' + esc(gapTop) + '</div>';
		html += '</div></div>';
		if (pb.is_estimated) {
			html += '<p class="airb__muted airb__peer-note">' + esc(estimatedNote) + '</p>';
		} else if (typeof pb.percentile === 'number' && opts.percentileTemplate) {
			html += '<p class="airb__muted airb__peer-note">' + esc(opts.percentileTemplate.replace('{n}', String(pb.percentile))) + '</p>';
		}
		return html + '</section>';
	}

	function leaderPeerBenchmarkBarHtml(pb) {
		return peerBenchmarkBarHtml(pb, {});
	}

	function leaderUrgentActionHtml(detail) {
		if (!detail || !detail.title) return '';
		var html = '<section class="airb__leader-urgent">';
		html += '<h3 class="airb__leader-urgent-heading">' + leaderResponsiveLabel(
			leaderResult.urgent_action_heading || i18n.urgentActionHeading || 'Your single most urgent action',
			leaderResult.urgent_action_heading_short || i18n.urgentActionHeadingShort || 'Your most urgent action'
		) + '</h3>';
		html += '<div class="airb__leader-urgent-body">';
		html += '<span class="airb__leader-urgent-icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></span>';
		html += '<div class="airb__leader-urgent-copy">';
		html += '<p class="airb__leader-urgent-title">' + esc(detail.title) + '</p>';
		if (detail.rationale) {
			html += '<p class="airb__leader-urgent-rationale">' + esc(detail.rationale) + '</p>';
		}
		html += '</div></div></section>';
		return html;
	}

	function readinessBandScaleHtml(score, opts) {
		opts = opts || {};
		score = Math.max(0, Math.min(100, parseInt(score, 10) || 0));
		var bandLabel = readinessBandLabel(score, state.role);
		var bandSlug = readinessLevel(score, state.role).slug;
		var bandColor = readinessBandColor(score, state.role);
		var heroSig = opts.leaderHero ? leaderMetricSignals('readiness', bandSlug) : null;
		var bands = roleHeroBandDefinitions(state.role);
		var aria = (i18n.readinessScaleAria || 'Overall benchmark readiness {score} out of 100, {band}')
			.replace('{score}', String(score))
			.replace('{band}', bandLabel);

		var html = '<div class="airb__readiness-scale' + (opts.hero ? ' airb__readiness-scale--hero' : '') + '" role="img" aria-label="' + esc(aria) + '">';
		if (opts.hero) {
			html += '<div class="airb__score-hero">';
			html += '<div class="airb__score-hero-score" style="color:' + esc(bandColor) + '">' + score + '%</div>';
			html += '<div class="airb__score-hero-meta">';
		}
		html += '<div class="airb__readiness-scale-head">';
		html += '<p class="airb__readiness-scale-kicker">' + esc(i18n.readinessScaleKicker || 'Overall benchmark readiness') + '</p>';
		if (!opts.hero) {
			html += '<div class="airb__readiness-scale-values">';
			html += '<span class="airb__readiness-scale-score" style="color:' + esc(bandColor) + '">' + score + '%</span>';
			html += '<span class="airb__readiness-scale-band" style="color:' + esc(bandColor) + '">' + esc(bandLabel.toUpperCase()) + '</span>';
			html += '</div>';
		} else {
			if (opts.leaderHero && heroSig && heroSig.signal) {
				html += '<span class="airb__metric-signal airb__metric-signal--' + heroSig.tone + '">' + esc(heroSig.signal) + '</span>';
			} else {
				html += readinessBandPillHtml(bandSlug, bandLabel);
			}
		}
		html += '</div>';
		if (!opts.hero) {
			html += '<p class="airb__readiness-scale-help airb__muted">' + esc(
				i18n.readinessScaleNote || 'This score is calculated from your role-specific audit domains. Other metrics, such as dependency, oversight and governance, are shown separately.'
			) + '</p>';
		}

		html += '<div class="airb__readiness-scale-track">';
		bands.forEach(function (b) {
			var span = b.max - b.min + 1;
			var mid = Math.round((b.min + b.max) / 2);
			var active = score >= b.min && score <= b.max;
			html += '<span class="airb__readiness-scale-seg airb__readiness-scale-seg--' + b.slug + (active ? ' is-active' : '') + '" style="flex:' + span + ' 1 0;background:' + esc(readinessBandColor(mid, state.role)) + '" title="' + esc(b.label + ' (' + b.min + '\u2013' + b.max + ')') + '"></span>';
		});
		html += '<span class="airb__readiness-scale-marker" style="left:' + score + '%;" aria-hidden="true"></span>';
		html += '</div>';

		html += '<div class="airb__readiness-scale-labels" aria-hidden="true">';
		bands.forEach(function (b) {
			var span = b.max - b.min + 1;
			var active = score >= b.min && score <= b.max;
			var lab = active && score >= 60 && score <= 64 && (state.role === 'leader' || state.role === 'teacher' || state.role === 'support_staff')
				? ((i18n.bandsReadiness && i18n.bandsReadiness.earlyEstablished) || 'Early Established')
				: (b.label_short || b.label);
			var shortLab = b.label_short || readinessBandShortLabel(b.slug, lab);
			html += '<span class="airb__readiness-scale-lab airb__readiness-scale-lab--' + b.slug + (active ? ' is-active' : '') + '" style="flex:' + span + ' 1 0" title="' + esc(lab + ' (' + b.min + '\u2013' + b.max + ')') + '">';
			html += '<span class="airb__readiness-scale-lab-name">';
			html += '<span class="airb__readiness-scale-lab-name-full">' + esc(lab) + '</span>';
			html += '<span class="airb__readiness-scale-lab-name-short">' + esc(shortLab) + '</span>';
			html += '</span>';
			html += '<span class="airb__readiness-scale-lab-range">' + b.min + '\u2013' + b.max + '</span>';
			html += '</span>';
		});
		html += '</div>';

		if (score >= 60 && score <= 64) {
			html += '<p class="airb__readiness-scale-note airb__muted">' + esc(i18n.earlyEstablishedNote || 'Early established — strong foundation with room to reach full Established (65%+).') + '</p>';
		}

		if (opts.hero) {
			if (opts.leaderHero && heroSig && heroSig.consequence) {
				html += '<p class="airb__score-hero-consequence">' + esc(heroSig.consequence) + '</p>';
			}
			html += '<p class="airb__readiness-scale-help airb__muted">' + esc(
				i18n.readinessScaleNote || 'This score is calculated from your role-specific audit domains. Other metrics, such as dependency, oversight and governance, are shown separately.'
			) + '</p>';
			html += '</div></div>';
		}

		return html + '</div>';
	}

	function riskScoreColor(pct) {
		if (pct >= 55) return 'var(--airb-crit)';
		if (pct >= 40) return 'var(--airb-mod)';
		return 'var(--airb-low)';
	}

	function dependencyColor(pct) {
		if (pct >= 60) return 'var(--airb-crit)';
		if (pct >= 35) return 'var(--airb-mod)';
		return 'var(--airb-low)';
	}

	function roleShowsDependency(role) {
		return role === 'teacher' || role === 'support_staff';
	}

	function studentDisplayScore(raw) {
		var n = Math.max(0, Math.min(100, parseInt(raw, 10) || 0));
		if (n < 5) return 5;
		return n;
	}

	function studentSkillBand(pct) {
		var score = studentDisplayScore(pct);
		var levels = (studentResult.journey_levels || [
			{ slug: 'beginning', label: 'Needs attention', min: 0, max: 20, tone: 'alarm' },
			{ slug: 'developing', label: 'Take care', min: 21, max: 40, tone: 'concern' },
			{ slug: 'emerging', label: 'Aware', min: 41, max: 60 },
			{ slug: 'confident', label: 'Confident', min: 61, max: 80 },
			{ slug: 'advanced', label: 'Advanced', min: 81, max: 100 },
		]);
		for (var i = 0; i < levels.length; i++) {
			if (score >= levels[i].min && score <= levels[i].max) {
				return { slug: levels[i].slug, label: levels[i].label };
			}
		}
		return { slug: 'beginning', label: 'Needs attention' };
	}

	function studentSkillBandLabel(pct) {
		return studentSkillBand(pct).label;
	}

	function studentSkillColor(pct) {
		var slug = studentSkillBand(pct).slug;
		var colors = {
			beginning: '#a32d2d',
			developing: '#185fa5',
			emerging: '#0c6b8a',
			confident: '#1d9e75',
			advanced: '#15803d',
		};
		return colors[slug] || '#64748b';
	}

	function studentBandDefinitions() {
		return studentResult.journey_levels || [
			{ slug: 'beginning', label: 'Needs attention', min: 0, max: 20, tone: 'alarm' },
			{ slug: 'developing', label: 'Take care', min: 21, max: 40, tone: 'concern' },
			{ slug: 'emerging', label: 'Aware', min: 41, max: 60 },
			{ slug: 'confident', label: 'Confident', min: 61, max: 80 },
			{ slug: 'advanced', label: 'Advanced', min: 81, max: 100 },
		];
	}

	function studentSignalLine(bandLabel, signal) {
		if (!signal) return bandLabel;
		return bandLabel + ' — ' + signal;
	}

	function studentSkillBadgeClass(slug) {
		if (slug === 'advanced') return 'advanced';
		if (slug === 'confident') return 'confident';
		if (slug === 'developing') return 'developing';
		if (slug === 'beginning') return 'beginning';
		return 'emerging';
	}

	function studentReadinessHeroHtml(score, uiHero) {
		score = studentDisplayScore(score);
		var band = studentSkillBand(score);
		var heroSig = uiHero && (uiHero.signal || uiHero.consequence)
			? { signal: uiHero.signal || '', tone: metricToneClass(uiHero.tone || 'neutral'), consequence: uiHero.consequence || '' }
			: { signal: '', tone: 'neutral', consequence: '' };
		var tone = heroSig.tone || 'neutral';
		var bands = studentBandDefinitions();
		var signalLine = studentSignalLine(band.label, heroSig.signal);
		var kicker = studentResult.hero_metric_label || i18n.studentSkillLevel || 'Overall AI skills level';

		var html = '<div class="airb__student-hero airb__student-hero--tone-' + tone + '" role="img" aria-label="' + esc(
			(i18n.studentSkillLevel || 'Overall AI skills level') + ' ' + score + '%, ' + (signalLine || band.label)
		) + '">';
		html += '<div class="airb__student-hero-head">';
		html += '<span class="airb__student-hero-pct">' + score + '%</span>';
		html += '<div class="airb__student-hero-meta">';
		if (heroSig.signal) {
			html += '<div class="airb__student-hero-signal airb__student-hero-signal--desktop">' + esc(signalLine) + '</div>';
			html += '<div class="airb__student-hero-signal-mobile" aria-hidden="true">';
			html += '<div class="airb__leader-hero-band">' + esc(band.label) + '</div>';
			html += '<div class="airb__leader-hero-action">' + esc(heroSig.signal) + '</div>';
			html += '</div>';
		} else {
			html += '<div class="airb__student-hero-signal airb__student-hero-signal--desktop">' + esc(band.label) + '</div>';
		}
		html += '<div class="airb__student-hero-kicker">' + esc(kicker) + '</div>';
		html += '</div></div>';
		if (heroSig.consequence) {
			html += '<p class="airb__student-hero-consequence">' + esc(heroSig.consequence) + '</p>';
		}
		html += '<div class="airb__student-hero-bar" aria-hidden="true">';
		bands.forEach(function (b) {
			html += '<span class="airb__student-hero-seg airb__student-hero-seg--' + b.slug + (band.slug === b.slug ? ' is-active' : '') + '"></span>';
		});
		html += '</div>';
		html += '<div class="airb__student-hero-bar-labels" aria-hidden="true">';
		bands.forEach(function (b) {
			html += heroBarBandLabelHtml(b, studentBandShortLabel);
		});
		html += '</div></div>';
		return html;
	}

	function studentSkillsSectionHtml(metrics) {
		if (!metrics || !metrics.length) return '';
		var heading = studentResult.skills_section_heading_short || studentResult.skills_section_heading || 'Core skills';
		var html = '<div class="airb__student-skills-card">';
		html += benchmarkCardHeadingHtml(heading);
		metrics.forEach(function (row) {
			var pct = parseInt(row.value, 10) || 0;
			var band = row.skill_band && row.skill_band.label ? row.skill_band : studentSkillBand(pct);
			var badgeClass = studentSkillBadgeClass(band.slug);
			var color = studentSkillColor(pct);
			html += '<div class="airb__student-skill-row">';
			html += '<span class="airb__student-skill-label">' + esc(row.label) + '</span>';
			html += '<div class="airb__student-skill-bar-wrap"><div class="airb__student-skill-bar" style="width:' + pct + '%;background:' + esc(color) + '"></div></div>';
			html += '<span class="airb__student-skill-val" style="color:' + esc(color) + '">' + pct + '%</span>';
			html += '<span class="airb__student-skill-badge airb__student-skill-badge--' + badgeClass + '">' + esc(band.label) + '</span>';
			html += '</div>';
		});
		return html + '</div>';
	}

	function studentFocusBadge(area) {
		var band = area.skill_band && area.skill_band.label ? area.skill_band : studentSkillBand(area.pct);
		var badgeClass = studentSkillBadgeClass(band.slug);
		return {
			className: badgeClass,
			text: band.label + ' · ' + (parseInt(area.pct, 10) || 0) + '%',
		};
	}

	function studentResourceIconClass(icon) {
		var map = { book: 'book', shield: 'shield', brain: 'brain' };
		return map[icon] || 'book';
	}

	function studentResourcesSectionHtml(resources, intro) {
		if (!resources || !resources.length) return '';
		var heading = studentResult.resources_section_heading || 'Study resources';
		var headingShort = studentResult.resources_section_heading_short || heading;
		var html = leaderSectionDivider();
		html += leaderSectionLabel(heading, headingShort);
		html += '<div class="airb__student-resources-card">';
		if (intro) {
			html += '<p class="airb__student-resources-intro">' + esc(intro) + '</p>';
		}
		html += '<div class="airb__student-resource-list">';
		resources.forEach(function (res) {
			if (!res.url) return;
			html += '<a class="airb__student-resource-row" href="' + esc(res.url) + '" target="_blank" rel="noopener noreferrer">';
			html += '<span class="airb__student-resource-icon airb__student-resource-icon--' + esc(studentResourceIconClass(res.icon)) + '" aria-hidden="true"></span>';
			html += '<span class="airb__student-resource-body">';
			html += '<span class="airb__student-resource-label">' + esc(res.label) + '</span>';
			if (res.description) {
				html += '<span class="airb__student-resource-desc">' + esc(res.description) + '</span>';
			}
			html += '</span>';
			html += '<span class="airb__student-resource-arrow" aria-hidden="true">→</span>';
			html += '</a>';
		});
		return html + '</div></div>';
	}

	function studentRetakeCardHtml(sr, score) {
		if (!sr) return '';
		score = studentDisplayScore(parseInt(score, 10) || 0);
		var threshold = parseInt(studentResult.retake_at_risk_threshold, 10);
		if (isNaN(threshold)) threshold = 35;
		var atRisk = score < threshold;
		var retakeUrl = (sr.next_steps && sr.next_steps.hero && sr.next_steps.hero.cta_url) ? sr.next_steps.hero.cta_url : '';
		var shareLabel = studentResult.share_cta_primary || i18n.shareWithSchool || 'Share with school';
		var retakeLabel = studentResult.share_cta_secondary || studentResult.retake_cta || i18n.studentRetake || 'Retake the benchmark';
		var heading = atRisk
			? (studentResult.retake_at_risk_heading || i18n.studentRetakeAtRiskHeading || 'Needs attention — build your skills first')
			: '';
		var body = atRisk
			? (studentResult.retake_at_risk_body || i18n.studentRetakeAtRiskBody || 'You scored below 35%, which puts you in the at-risk band. Explore the articles and study resources above before you retake.')
			: (studentResult.retake_body_default || i18n.studentRetakeBody || 'When you are ready, retake the benchmark to see how your AI skills have improved.');
		var html = '<div class="airb__student-share-card airb__student-retake-card' + (atRisk ? ' airb__student-retake-card--at-risk' : '') + '">';
		if (atRisk && heading) {
			html += '<h4 class="airb__student-retake-heading">' + esc(heading) + '</h4>';
		}
		if (body) {
			html += '<p class="airb__student-retake-body">' + esc(body) + '</p>';
		}
		html += '<div class="airb__student-share-actions">';
		var shareMailto = buildShareResultsMailto();
		if (shareMailto) {
			html += '<a class="airb__btn airb__btn--primary airb__student-share-btn airb__student-share-btn--primary" href="' + esc(shareMailto) + '" id="airb-share-results">' + esc(shareLabel) + ' ↗</a>';
		} else {
			html += '<button type="button" class="airb__btn airb__btn--primary airb__student-share-btn airb__student-share-btn--primary" data-airb-open-interest="student_share_school">' + esc(shareLabel) + ' ↗</button>';
		}
		if (retakeUrl) {
			html += '<a class="airb__btn airb__student-share-btn airb__student-share-btn--secondary" href="' + esc(retakeUrl) + '">' + esc(retakeLabel) + ' ↗</a>';
		} else {
			html += '<button type="button" class="airb__btn airb__student-share-btn airb__student-share-btn--secondary" data-airb-student-retake="benchmark">' + esc(retakeLabel) + ' ↗</button>';
		}
		html += '</div></div>';
		return html;
	}

	function restartStudentAudit(focusSchool) {
		hideError();
		state.role = 'student';
		state.results = null;
		state.submissionId = 0;
		state.studentFocusSchool = !!focusSchool;
		collapseIntro();
		startAuditQuestions();
	}

	function parentBandDefinitions() {
		return parentResult.awareness_levels || [
			{ slug: 'just_starting', label: 'Your child needs your help', min: 0, max: 20, tone: 'alarm' },
			{ slug: 'developing', label: 'Some gaps at home', min: 21, max: 40, tone: 'concern' },
			{ slug: 'aware', label: 'Aware', min: 41, max: 60 },
			{ slug: 'confident', label: 'Confident', min: 61, max: 80 },
			{ slug: 'well_prepared', label: 'Well prepared', min: 81, max: 100 },
		];
	}

	function parentAwarenessBand(score) {
		score = Math.max(0, Math.min(100, parseInt(score, 10) || 0));
		var levels = parentBandDefinitions();
		for (var i = 0; i < levels.length; i++) {
			if (score >= levels[i].min && score <= levels[i].max) {
				return { slug: levels[i].slug, label: levels[i].label };
			}
		}
		return { slug: 'just_starting', label: 'Your child needs your help' };
	}

	function parentSignalLine(bandLabel, signal) {
		if (!signal) return bandLabel;
		return bandLabel + ' — ' + signal;
	}

	function parentMetricColor(badgeSlug, pct) {
		if (badgeSlug === 'good') return '#639922';
		if (badgeSlug === 'risk') return '#e24b4a';
		if (badgeSlug === 'attention') return '#ef9f27';
		return '#378add';
	}

	function parentMetricTextColor(badgeSlug) {
		if (badgeSlug === 'good') return '#3b6d11';
		if (badgeSlug === 'risk') return '#a32d2d';
		if (badgeSlug === 'attention') return '#854f0b';
		return '#185fa5';
	}

	function parentReadinessHeroHtml(score, uiHero) {
		score = Math.max(0, Math.min(100, parseInt(score, 10) || 0));
		var band = parentAwarenessBand(score);
		var heroSig = uiHero && (uiHero.signal || uiHero.consequence)
			? { signal: uiHero.signal || '', tone: metricToneClass(uiHero.tone || 'warning'), consequence: uiHero.consequence || '' }
			: { signal: '', tone: 'warning', consequence: '' };
		var tone = heroSig.tone || 'warning';
		var bands = parentBandDefinitions();
		var signalLine = parentSignalLine(band.label, heroSig.signal);
		var kicker = parentResult.hero_metric_label || i18n.parentAwarenessLevel || 'Overall home AI awareness';

		var html = '<div class="airb__parent-hero airb__parent-hero--tone-' + tone + '" role="img" aria-label="' + esc(
			kicker + ' ' + score + '%, ' + (signalLine || band.label)
		) + '">';
		html += '<div class="airb__parent-hero-head">';
		html += '<span class="airb__parent-hero-pct">' + score + '%</span>';
		html += '<div class="airb__parent-hero-meta">';
		html += '<div class="airb__parent-hero-signal airb__parent-hero-signal--desktop">' + esc(signalLine || band.label) + '</div>';
		html += '<div class="airb__parent-hero-signal-mobile" aria-hidden="true">';
		html += '<div class="airb__leader-hero-band">' + esc(band.label) + '</div>';
		if (heroSig.signal) {
			html += '<div class="airb__leader-hero-action">' + esc(heroSig.signal) + '</div>';
		}
		html += '</div>';
		html += '<div class="airb__parent-hero-kicker">' + esc(kicker) + '</div>';
		html += '</div></div>';
		if (heroSig.consequence) {
			html += '<p class="airb__parent-hero-consequence">' + esc(heroSig.consequence) + '</p>';
		}
		html += '<div class="airb__parent-hero-bar" aria-hidden="true">';
		bands.forEach(function (b) {
			html += '<span class="airb__parent-hero-seg airb__parent-hero-seg--' + b.slug + (band.slug === b.slug ? ' is-active' : '') + '"></span>';
		});
		html += '</div>';
		html += '<div class="airb__parent-hero-bar-labels" aria-hidden="true">';
		bands.forEach(function (b) {
			html += heroBarBandLabelHtml(b, parentBandShortLabel);
		});
		html += '</div></div>';
		return html;
	}

	function parentHomeMetricsSectionHtml(metrics) {
		if (!metrics || !metrics.length) return '';
		var headingShort = isPublicRole()
			? (publicResult.metrics_section_heading_short || '5 area scores')
			: (parentResult.metrics_section_heading_short || '5 home safety scores');
		var html = '<div class="airb__parent-metrics-card">';
		html += benchmarkCardHeadingHtml(headingShort);
		metrics.forEach(function (row) {
			var pct = parseInt(row.value, 10) || 0;
			var badge = row.badge || { slug: 'developing', label: 'Building' };
			var barColor = parentMetricColor(badge.slug, pct);
			var textColor = parentMetricTextColor(badge.slug);
			html += '<div class="airb__parent-metric-row">';
			html += '<span class="airb__parent-metric-icon airb__parent-metric-icon--' + esc(row.icon || 'eye') + ' airb__parent-metric-icon--' + esc(badge.slug) + '" aria-hidden="true"></span>';
			html += '<div class="airb__parent-metric-copy">';
			html += '<div class="airb__parent-metric-label">' + esc(row.label) + '</div>';
			if (row.subtitle) html += '<div class="airb__parent-metric-sub">' + esc(row.subtitle) + '</div>';
			html += '</div>';
			html += '<div class="airb__parent-metric-bar-col">';
			html += '<div class="airb__parent-metric-bar-wrap"><div class="airb__parent-metric-bar" style="width:' + pct + '%;background:' + esc(barColor) + '"></div></div>';
			html += '<div class="airb__parent-metric-pct" style="color:' + esc(textColor) + '">' + pct + '%</div>';
			html += '</div>';
			html += '<span class="airb__parent-metric-badge airb__parent-metric-badge--' + esc(badge.slug) + '">' + esc(badge.label) + '</span>';
			html += '</div>';
		});
		return html + '</div>';
	}

	function parentFocusBadge(area) {
		var badge = area.badge || { slug: 'attention', label: 'Needs attention' };
		return badge.label + ' · ' + (parseInt(area.pct, 10) || 0) + '%';
	}

	function parentConversationStartersHtml(starters, intro) {
		if (!starters || !starters.length) return '';
		var headingShort = parentResult.conversation_section_heading_short || 'Conversation starters';
		var html = '<div class="airb__parent-convo-card">';
		html += benchmarkCardHeadingHtml(headingShort);
		if (intro) {
			html += '<p class="airb__parent-convo-intro">' + esc(intro) + '</p>';
		}
		html += '<p class="airb__parent-convo-instruction">Choose one that feels natural and start there.</p>';
		html += '<ol class="airb__parent-convo-list">';
		starters.forEach(function (item, index) {
			html += '<li class="airb__parent-convo-item">';
			html += '<span class="airb__parent-convo-number" aria-hidden="true">' + (index + 1) + '</span>';
			html += '<div class="airb__parent-convo-copy">';
			html += '<q class="airb__parent-convo-question">' + esc(item.question || '') + '</q>';
			if (item.hint) html += '<p class="airb__parent-convo-hint">' + esc(item.hint) + '</p>';
			html += '</div></li>';
		});
		return html + '</ol></div>';
	}

	function parentShareCardHtml(pr) {
		if (!pr) return '';
		var advocate = pr.advocate;
		var body = parentResult.share_section_body || '';
		if (advocate && advocate.intro) {
			body = advocate.intro + (body ? ' ' + body : '');
		}
		var html = '<div class="airb__parent-share-card">';
		html += '<div class="airb__parent-share-kicker">' + esc(parentResult.share_section_kicker || 'Share with your school') + '</div>';
		html += '<h4 class="airb__parent-share-title">' + esc((advocate && advocate.title) || parentResult.share_section_title || 'Help your school support your child better') + '</h4>';
		html += '<p class="airb__parent-share-body">' + esc(body) + '</p>';
		if (advocate && advocate.strengths && advocate.strengths.length) {
			html += '<ul class="airb__parent-share-strengths">';
			advocate.strengths.forEach(function (item) {
				html += '<li>' + esc(item) + '</li>';
			});
			html += '</ul>';
		}
		html += '<div class="airb__parent-share-actions">';
		var mailto = buildShareResultsMailto();
		if (mailto) {
			html += '<a class="airb__btn airb__parent-share-btn airb__parent-share-btn--primary" href="' + esc(mailto) + '">' + esc(parentResult.share_cta_primary || i18n.parentShareSchool || 'Share with school') + ' ↗</a>';
		} else {
			html += '<button type="button" class="airb__btn airb__parent-share-btn airb__parent-share-btn--primary" data-airb-open-interest="parent_share_with_school">' + esc(parentResult.share_cta_primary || i18n.parentShareSchool || 'Share with school') + ' ↗</button>';
		}
		html += '<button type="button" class="airb__btn airb__parent-share-btn airb__parent-share-btn--secondary" data-airb-open-interest="parent_resources">' + esc(parentResult.share_cta_secondary || i18n.parentSafetyGuide || 'Parent safety guide') + ' ↗</button>';
		html += '</div></div>';
		return html;
	}

	function supportReadinessHeroHtml(score, uiHero) {
		score = Math.max(0, Math.min(100, parseInt(score, 10) || 0));
		var bandLabel = readinessBandLabel(score, state.role);
		var bandSlug = readinessLevel(score, state.role).slug;
		var heroSig = uiHero && (uiHero.signal || uiHero.consequence)
			? leaderUiMetric(uiHero, 'readiness', bandSlug)
			: leaderMetricSignals('readiness', bandSlug);
		var tone = heroSig.tone || 'neutral';
		var bands = roleHeroBandDefinitions(state.role);
		var signalLine = leaderSignalLine(bandLabel, heroSig.signal);
		var kicker = supportResult.hero_metric_label || i18n.statReadiness || 'Overall readiness';

		var html = '<div class="airb__support-hero airb__leader-hero airb__leader-hero--tone-' + tone + '" role="img" aria-label="' + esc(
			kicker + ' ' + score + '%, ' + (signalLine || bandLabel)
		) + '">';
		html += '<div class="airb__leader-hero-head">';
		html += '<span class="airb__leader-hero-pct">' + score + '%</span>';
		html += '<div class="airb__leader-hero-meta">';
		if (heroSig.signal) {
			html += '<div class="airb__leader-hero-signal airb__leader-hero-signal--desktop">' + esc(signalLine) + '</div>';
			html += '<div class="airb__leader-hero-signal-mobile" aria-hidden="true">';
			html += '<div class="airb__leader-hero-band">' + esc(bandLabel) + '</div>';
			html += '<div class="airb__leader-hero-action">' + esc(heroSig.signal) + '</div>';
			html += '</div>';
		}
		html += '<div class="airb__leader-hero-kicker">' + esc(kicker) + '</div>';
		html += '</div></div>';
		if (heroSig.consequence) {
			html += '<p class="airb__leader-hero-consequence">' + esc(heroSig.consequence) + '</p>';
		}
		html += '<div class="airb__leader-hero-bar" aria-hidden="true">';
		bands.forEach(function (b) {
			html += '<span class="airb__leader-hero-seg airb__leader-hero-seg--' + b.slug + (bandSlug === b.slug ? ' is-active' : '') + '"></span>';
		});
		html += '</div>';
		html += '<div class="airb__leader-hero-bar-labels" aria-hidden="true">';
		bands.forEach(function (b) {
			html += heroBarBandLabelHtml(b, readinessBandShortLabel);
		});
		html += '</div></div>';
		return html;
	}

	function supportMetricGridHtml(r, sr) {
		var signals = sr && sr.metric_signals ? sr.metric_signals : {};
		var risk = Math.round(r.overall_risk_percentage || 0);
		var roleRisk = typeof sr.role_specific_risk === 'number' ? sr.role_specific_risk : risk;
		var riskCard = signals.risk_exposure || {};
		var roleCard = signals.role_risk || {};
		var html = '<div class="airb__support-metric-grid">';
		html += '<div class="airb__support-metric-cell airb__support-metric-cell--tone-' + metricToneClass(riskCard.tone || 'warning') + '">';
		html += '<div class="airb__support-metric-lbl">' + esc(i18n.leaderMetricRisk || i18n.statRisk || 'AI risk exposure') + '</div>';
		html += '<div class="airb__support-metric-val" style="color:' + esc(riskScoreColor(risk)) + '">' + risk + '%</div>';
		html += '<div class="airb__support-metric-sub" style="color:' + esc(riskScoreColor(risk)) + '">' + esc(riskCard.signal || displayRiskLabel(r.risk_level, risk)) + '</div>';
		if (riskCard.consequence) html += '<p class="airb__support-metric-desc">' + esc(riskCard.consequence) + '</p>';
		html += '</div>';
		html += '<div class="airb__support-metric-cell airb__support-metric-cell--tone-' + metricToneClass(roleCard.tone || 'urgent') + '">';
		html += '<div class="airb__support-metric-lbl">' + esc(i18n.supportRoleRisk || 'Role-specific risk') + '</div>';
		html += '<div class="airb__support-metric-val" style="color:' + esc(riskScoreColor(roleRisk)) + '">' + roleRisk + '%</div>';
		html += '<div class="airb__support-metric-sub" style="color:' + esc(riskScoreColor(roleRisk)) + '">' + esc(roleCard.signal || i18n.supportRoleRiskSignal || 'Higher in your role') + '</div>';
		if (roleCard.consequence) html += '<p class="airb__support-metric-desc">' + esc(roleCard.consequence) + '</p>';
		html += '</div></div>';
		return html;
	}

	function supportDomainBadgeClass(slug) {
		if (slug === 'good') return 'good';
		if (slug === 'critical') return 'critical';
		return 'moderate';
	}

	function supportDomainsSectionHtml(domainRows) {
		if (!domainRows || !domainRows.length) return '';
		var rows = '';
		domainRows.forEach(function (row) {
			var pct = parseInt(row.pct, 10) || 0;
			var color = readinessBandColor(pct);
			var badge = row.badge || { slug: 'moderate', label: 'Needs work' };
			rows += '<div class="airb__support-domain-row">';
			rows += '<span class="airb__support-domain-label">' + esc(row.label) + '</span>';
			rows += '<div class="airb__support-domain-bar-wrap"><div class="airb__support-domain-bar" style="width:' + pct + '%;background:' + esc(color) + '"></div></div>';
			rows += '<span class="airb__support-domain-val" style="color:' + esc(color) + '">' + pct + '%</span>';
			rows += '<span class="airb__support-domain-badge airb__support-domain-badge--' + supportDomainBadgeClass(badge.slug) + '">' + esc(badge.label) + '</span>';
			rows += '</div>';
		});
		if (!rows) return '';
		var headingShort = supportResult.domains_section_heading_short || 'By domain';
		return '<div class="airb__support-domain-card">' + benchmarkCardHeadingHtml(headingShort) + rows + '</div>';
	}

	function supportFocusSeverity(pct, severity) {
		if (severity === 'critical' || pct < 35) return 'critical';
		if (pct < 45) return 'moderate';
		return 'attention';
	}

	function supportRolloutCardHtml(ro) {
		if (!ro) return '';
		var html = leaderRolloutCardHtml(ro);
		return html.replace('airb__leader-rollout-card', 'airb__leader-rollout-card airb__leader-rollout-card--support');
	}

	function supportCtaCardHtml(nextSteps) {
		if (!nextSteps || !nextSteps.hero) return '';
		var hero = nextSteps.hero;
		var deliverables = hero.deliverables && hero.deliverables.length ? hero.deliverables : (hero.understand_items || []);
		var html = '<article class="airb__support-cta-card">';
		if (hero.pathway_kicker) {
			html += '<div class="airb__support-cta-kicker">' + esc(hero.pathway_kicker) + '</div>';
		}
		html += '<h4 class="airb__support-cta-title">' + esc(hero.title) + '</h4>';
		if (hero.body) html += '<p class="airb__support-cta-body">' + esc(hero.body) + '</p>';
		if (deliverables.length) {
			html += '<div class="airb__support-cta-deliverables" role="list">';
			deliverables.forEach(function (item) {
				html += '<span class="airb__support-cta-deliverable" role="listitem">' + esc(item) + '</span>';
			});
			html += '</div>';
		}
		html += '<div class="airb__support-cta-actions">';
		html += '<button type="button" class="airb__btn airb__btn--premium airb__support-cta-btn airb__support-cta-btn--primary" data-airb-open-interest="' + esc(hero.key || 'support_cpd') + '">' + esc(hero.cta_text || 'Book CPD session') + ' ↗</button>';
		if (hero.secondary_cta_text && hero.secondary_key) {
			html += '<button type="button" class="airb__btn airb__support-cta-btn airb__support-cta-btn--secondary" data-airb-open-interest="' + esc(hero.secondary_key) + '">' + esc(hero.secondary_cta_text) + ' ↗</button>';
		}
		html += '</div></article>';
		return html;
	}

	function supportHelpSupportHtml(nextSteps) {
		if (!nextSteps || !nextSteps.resource_links || !nextSteps.resource_links.length) return '';
		return benchmarkHelpSupportHtml(
			nextSteps,
			nextSteps.help_support_heading || supportResult.help_support_heading,
			nextSteps.help_support_heading_short || supportResult.help_support_heading_short
		);
	}

	function studentLearnerTypeHtml(sr) {
		if (!sr || !sr.learner_type || !sr.learner_type.title) return '';
		var lt = sr.learner_type;
		var html = '<section class="airb__learner-type">';
		html += '<p class="airb__learner-type-brand">' + esc(lt.brand || studentResult.learner_types_brand || 'AI Learner Types') + '</p>';
		html += '<h3 class="airb__learner-type-title">' + esc(lt.title) + '</h3>';
		if (lt.description) html += '<p class="airb__learner-type-body">' + esc(lt.description) + '</p>';
		if (lt.focus_items && lt.focus_items.length) {
			html += '<p class="airb__learner-type-focus-label"><strong>' + esc(lt.focus_heading || 'Focus:') + '</strong></p>';
			html += '<ul class="airb__learner-type-focus">';
			lt.focus_items.forEach(function (item) {
				html += '<li>' + esc(item) + '</li>';
			});
			html += '</ul>';
		}
		return html + '</section>';
	}

	function studentLearningProfileHtml(sr) {
		if (!sr || !sr.learning_metrics || !sr.learning_metrics.length) return '';
		var profileMetrics = sr.learning_metrics.map(function (row) {
			return {
				label: row.label,
				value: row.value + '%',
				band: row.skill_band && row.skill_band.label ? row.skill_band.label : studentSkillBand(row.value).label,
				color: studentSkillColor(row.value),
			};
		});
		return summaryMetricsSectionHtml(
			profileMetrics,
			sr.profile_title || studentResult.profile_title || 'Your AI Learning Profile',
			{ modifier: 'student', showBand: true }
		);
	}

	function isParentRole() {
		return state.role === 'parent';
	}

	function isPublicRole() {
		return state.role === 'public';
	}

	function isTeacherRole() {
		return state.role === 'teacher';
	}

	function isStudentRole() {
		return state.role === 'student';
	}

	function isLeaderRole() {
		return state.role === 'leader';
	}

	function supportDisplayDomainScores(answers) {
		var qid = 'ss_report_issue';
		var question = null;
		(cfg.questions || []).forEach(function (q) {
			if (q.id === qid) question = q;
		});
		if (!question || !answers[qid]) return {};
		var score = scoreAnswer(question, answers[qid]);
		var avgRisk = score / 3 * 100;
		var readiness = Math.round(100 - avgRisk);
		return {
			safeguarding: {
				label: i18n.safeguarding || 'Safeguarding awareness',
				risk_percentage: Math.round(avgRisk * 10) / 10,
				readiness_percentage: Math.round((100 - avgRisk) * 10) / 10,
				band: riskBand(avgRisk),
				readiness_band: readinessBand(readiness),
				questions_answered: 1,
			},
		};
	}

	function parentDisplayDomainScores(role, answers) {
		var defs = parentResult.display_domains || {};
		var questionsById = {};
		(cfg.questions || []).forEach(function (q) {
			if (q.id) questionsById[q.id] = q;
		});
		var out = {};
		Object.keys(defs).forEach(function (slug) {
			var def = defs[slug];
			var scores = [];
			(def.questions || []).forEach(function (qid) {
				if (!answers[qid] || !questionsById[qid]) return;
				scores.push(scoreAnswer(questionsById[qid], answers[qid]));
			});
			if (!scores.length) return;
			var avgRisk = (scores.reduce(function (a, b) { return a + b; }, 0) / scores.length) / 3 * 100;
			var readiness = Math.round(100 - avgRisk);
			out[slug] = {
				label: def.label || slug,
				metric_type: def.metric_type || 'score',
				color: def.color || '#475569',
				risk_percentage: Math.round(avgRisk * 10) / 10,
				readiness_percentage: Math.round((100 - avgRisk) * 10) / 10,
				band: riskBand(avgRisk),
				readiness_band: readinessBand(readiness),
				questions_answered: scores.length,
			};
		});
		return out;
	}

	function publicDisplayDomainScores(answers) {
		var defs = publicResult.display_domains || {};
		var questionsById = {};
		(cfg.questions || []).forEach(function (q) {
			if (q.id) questionsById[q.id] = q;
		});
		var out = {};
		Object.keys(defs).forEach(function (slug) {
			var def = defs[slug];
			var scores = [];
			(def.questions || []).forEach(function (qid) {
				if (!answers[qid] || !questionsById[qid]) return;
				scores.push(scoreAnswer(questionsById[qid], answers[qid]));
			});
			if (!scores.length) return;
			var avgRisk = (scores.reduce(function (a, b) { return a + b; }, 0) / scores.length) / 3 * 100;
			var readiness = Math.round(100 - avgRisk);
			out[slug] = {
				label: def.label || slug,
				metric_type: def.metric_type || 'score',
				color: def.color || '#475569',
				risk_percentage: Math.round(avgRisk * 10) / 10,
				readiness_percentage: Math.round((100 - avgRisk) * 10) / 10,
				band: riskBand(avgRisk),
				readiness_band: readinessBand(readiness),
				questions_answered: scores.length,
			};
		});
		return out;
	}

	function publicOverallFromDisplay(publicDisplay) {
		var weights = publicResult.domain_weights || {};
		var weightedSum = 0;
		var weightTotal = 0;
		Object.keys(publicDisplay || {}).forEach(function (slug) {
			var dom = publicDisplay[slug];
			if (!dom || !dom.questions_answered) return;
			var weight = parseFloat(weights[slug] || 0);
			if (!weight) return;
			var readiness = dom.readiness_percentage;
			weightedSum += readiness * weight;
			weightTotal += weight;
		});
		if (!weightTotal) {
			var riskValues = [];
			Object.keys(publicDisplay || {}).forEach(function (slug) {
				var dom = publicDisplay[slug];
				if (!dom || !dom.questions_answered) return;
				riskValues.push(dom.risk_percentage);
			});
			if (!riskValues.length) {
				return { overall_risk: 0, alignment_score: 0, risk_level: 'low' };
			}
			var overall = riskValues.reduce(function (a, b) { return a + b; }, 0) / riskValues.length;
			return {
				overall_risk: Math.round(overall * 10) / 10,
				alignment_score: Math.round(100 - overall),
				risk_level: riskBand(overall),
			};
		}
		var alignment = weightedSum / weightTotal;
		var overallRisk = 100 - alignment;
		return {
			overall_risk: Math.round(overallRisk * 10) / 10,
			alignment_score: Math.round(alignment),
			risk_level: riskBand(overallRisk),
		};
	}

	function parentOverallFromDisplay(parentDisplay) {
		var weights = parentResult.domain_weights || {};
		var weightedSum = 0;
		var weightTotal = 0;
		Object.keys(parentDisplay || {}).forEach(function (slug) {
			var dom = parentDisplay[slug];
			if (!dom || !dom.questions_answered) return;
			var weight = parseFloat(weights[slug] || 0);
			if (!weight) return;
			var isRisk = dom.metric_type === 'risk';
			var readiness = isRisk ? (100 - dom.risk_percentage) : dom.readiness_percentage;
			weightedSum += readiness * weight;
			weightTotal += weight;
		});
		if (!weightTotal) {
			var riskValues = [];
			Object.keys(parentDisplay || {}).forEach(function (slug) {
				var dom = parentDisplay[slug];
				if (!dom || !dom.questions_answered) return;
				riskValues.push(dom.risk_percentage);
			});
			if (!riskValues.length) {
				return { overall_risk: 0, alignment_score: 0, risk_level: 'low' };
			}
			var overall = riskValues.reduce(function (a, b) { return a + b; }, 0) / riskValues.length;
			return {
				overall_risk: Math.round(overall * 10) / 10,
				alignment_score: Math.round(100 - overall),
				risk_level: riskBand(overall),
			};
		}
		var alignment = weightedSum / weightTotal;
		var overallRisk = 100 - alignment;
		return {
			overall_risk: Math.round(overallRisk * 10) / 10,
			alignment_score: Math.round(alignment),
			risk_level: riskBand(overallRisk),
		};
	}

	function parentJourneyTier(score) {
		var s = parseInt(score, 10) || 0;
		if (s >= 85) return 'high';
		if (s < 40) return 'low';
		return 'medium';
	}

	function studentOverallFromDomains(domainScores) {
		var slugs = ['ai_dependency', 'assessment_integrity', 'human_oversight', 'ai_literacy', 'privacy', 'safeguarding'];
		var riskValues = [];
		slugs.forEach(function (slug) {
			var dom = domainScores[slug];
			if (!dom || !dom.questions_answered) return;
			riskValues.push(dom.risk_percentage);
		});
		if (!riskValues.length) {
			return { overall_risk: 0, alignment_score: 0, risk_level: 'low' };
		}
		var overall = riskValues.reduce(function (a, b) { return a + b; }, 0) / riskValues.length;
		var alignment = studentDisplayScore(Math.round(100 - overall));
		return {
			overall_risk: Math.round((100 - alignment) * 10) / 10,
			alignment_score: alignment,
			risk_level: riskBand(overall),
		};
	}

	function parentBandSummary(readiness) {
		var summaries = parentResult.band_summaries || {};
		var slug = readinessBand(readiness);
		return summaries[slug] || '';
	}

	function oversightGaugeValue(r) {
		var ho = r.domain_scores && r.domain_scores.human_oversight;
		if (ho && ho.questions_answered) {
			return Math.round(ho.readiness_percentage);
		}
		if (typeof r.human_oversight_ratio === 'number') return r.human_oversight_ratio;
		if (typeof r.human_oversight_readiness === 'number' && r.human_oversight_readiness > 0) {
			return r.human_oversight_readiness;
		}
		return null;
	}

	function sliderScore(pct) {
		if (pct >= 51) return 0;
		if (pct >= 26) return 1;
		if (pct >= 11) return 2;
		return 3;
	}

	function oversightLabel(pct) {
		if (pct <= 10) return 'Critical reliance';
		if (pct <= 25) return 'High reliance';
		if (pct <= 50) return 'Moderate oversight';
		return 'Strong oversight';
	}

	function scoreAnswer(q, value) {
		if (q.type === 'slider') {
			var p = Math.max(0, Math.min(100, parseInt(value, 10) || 0));
			return sliderScore(p);
		}
		var opts = q.options || [];
		for (var i = 0; i < opts.length; i++) {
			if (String(opts[i].value) === String(value)) {
				return Math.max(0, Math.min(3, parseInt(opts[i].score, 10) || 0));
			}
		}
		return 0;
	}

	function displayRiskLabel(band, riskPct) {
		if (band === 'moderate' && riskPct >= 48) return 'Moderate-High';
		if (band === 'high' && riskPct >= 72) return 'High–Critical';
		if (band === 'low' && riskPct >= 22) return 'Low–Moderate';
		return bandLabel(band);
	}

	function readinessBand(readiness) {
		return readinessLevel(readiness).slug;
	}

	function compositeDependency(role, answers, domainScores) {
		var extra = { teacher: ['t_without_ai', 't_ai_before_task', 't_feedback_ai'], student: ['s_attempt_first', 's_without_ai', 's_submitted_ai'], support_staff: ['ss_draft_comms', 'ss_without_ai', 'ss_task_approach'], public: ['pub_use_dependency', 'pub_social_advice', 'pub_social_relationship'] };
		var scores = [];
		(cfg.questions || []).forEach(function (q) {
			if (q.role !== role || !answers[q.id]) return;
			if (!questionApplies(q, answers)) return;
			var inExtra = (extra[role] || []).indexOf(q.id) >= 0;
			if (q.domain === 'ai_dependency' || inExtra) {
				scores.push(scoreAnswer(q, answers[q.id]));
			}
		});
		if (!scores.length) return Math.round(domainScores.ai_dependency ? domainScores.ai_dependency.risk_percentage : 0);
		var avg = scores.reduce(function (a, b) { return a + b; }, 0) / scores.length;
		return Math.round((avg / 3) * 100);
	}

	function compositeOversight(role, answers) {
		var vals = [];
		var modifyPct = null;
		(cfg.questions || []).forEach(function (q) {
			if (q.role !== role || !answers[q.id]) return;
			if (!questionApplies(q, answers)) return;
			if (q.type === 'slider' && q.domain === 'human_oversight') {
				modifyPct = Math.max(0, Math.min(100, parseInt(answers[q.id], 10) || 0));
				vals.push(modifyPct);
			} else if (q.domain === 'human_oversight') {
				var sc = scoreAnswer(q, answers[q.id]);
				vals.push(100 - (sc / 3) * 100);
			}
		});
		if (!vals.length) return { pct: null, label: 'Not assessed', readiness: 0 };
		var readiness = Math.round(vals.reduce(function (a, b) { return a + b; }, 0) / vals.length);
		var label = oversightLabel(readiness);
		return { pct: readiness, label: label, readiness: readiness };
	}

	function roleResultCards(role, results) {
		var dom = results.domain_scores || {};
		var riskLabel = displayRiskLabel(results.risk_level, results.overall_risk_percentage);
		var cards = [];
		if (role === 'teacher') {
			var align = results.alignment_score;
			var depBand = riskBand(results.dependency_index);
			cards = [
				{ label: 'Teacher AI Risk Score', value: Math.round(results.overall_risk_percentage) + '%', band: results.risk_level, tone: 'risk', band_label: riskLabel },
				{ label: 'Teacher AI Readiness Score', value: align + '/100', band: readinessBand(align), tone: 'readiness', band_label: readinessBandLabel(align) },
				{ label: 'AI Dependency Index', value: results.dependency_index + '%', band: depBand, tone: 'risk', band_label: bandLabel(depBand) },
				{ label: 'Human Oversight Ratio', value: results.human_oversight_label, band: oversightBandFromLabel(results.human_oversight_label), tone: 'oversight' },
			];
		} else if (role === 'student') {
			var lit = Math.round(dom.ai_literacy ? dom.ai_literacy.readiness_percentage : 0);
			var sDepBand = riskBand(results.dependency_index);
			cards = [
				{ label: 'Student Learning Risk Score', value: Math.round(results.overall_risk_percentage) + '%', band: results.risk_level, tone: 'risk', band_label: riskLabel },
				{ label: 'Student AI Dependency Score', value: results.dependency_index + '%', band: sDepBand, tone: 'risk', band_label: bandLabel(sDepBand) },
				{ label: 'Student AI Literacy Score', value: lit + '%', band: readinessBand(lit), tone: 'readiness', band_label: readinessBandLabel(lit) },
			];
		} else if (role === 'parent') {
			var parentDisplay = results.parent_display_domains || {};
			Object.keys(parentDisplay).forEach(function (slug) {
				var dom = parentDisplay[slug];
				if (!dom || !dom.questions_answered) return;
				var isRisk = dom.metric_type === 'risk';
				var value = Math.round(isRisk ? dom.risk_percentage : dom.readiness_percentage);
				cards.push({
					label: dom.label,
					value: value + '%',
					band: isRisk ? dom.band : readinessBand(value),
					tone: isRisk ? 'risk' : 'readiness',
					band_label: isRisk ? bandLabel(dom.band) : readinessBandLabel(value),
				});
			});
		} else if (role === 'leader') {
			var gov = results.governance_maturity;
			var lSafe = results.safeguarding_readiness;
			var lAlign = results.alignment_score;
			cards = [
				{ label: 'Governance Maturity Score', value: gov + '%', band: readinessBand(gov), tone: 'readiness', band_label: readinessBandLabel(gov) },
				{ label: 'Safeguarding Readiness Score', value: lSafe + '%', band: readinessBand(lSafe), tone: 'readiness', band_label: readinessBandLabel(lSafe) },
				{ label: 'DfE AI Alignment Score', value: lAlign + '/100', band: readinessBand(lAlign), tone: 'readiness', band_label: readinessBandLabel(lAlign) },
			];
		} else if (role === 'support_staff') {
			var sAlign = results.alignment_score;
			var sDepBand = riskBand(results.dependency_index);
			var hoDom = dom.human_oversight || {};
			var hoPct = Math.round(hoDom.readiness_percentage || results.human_oversight_ratio || 0);
			var dpPct = Math.round((dom.privacy || {}).readiness_percentage || 0);
			cards = [
				{ label: 'Readiness Score', value: sAlign + '/100', band: readinessBand(sAlign), tone: 'readiness', band_label: readinessBandLabel(sAlign) },
				{ label: 'Operational Dependency Index', value: results.dependency_index + '%', band: sDepBand, tone: 'risk', band_label: bandLabel(sDepBand) },
				{ label: 'Human Oversight Ratio', value: hoPct + '%', band: readinessBand(hoPct), tone: 'readiness', band_label: readinessBandLabel(hoPct) },
				{ label: 'Data Protection Readiness', value: dpPct + '%', band: readinessBand(dpPct), tone: 'readiness', band_label: readinessBandLabel(dpPct) },
			];
		} else if (role === 'public') {
			var publicDisplay = results.public_display_domains || {};
			Object.keys(publicDisplay).forEach(function (slug) {
				var dom = publicDisplay[slug];
				if (!dom || !dom.questions_answered) return;
				var value = Math.round(dom.readiness_percentage);
				cards.push({
					label: dom.label,
					value: value + '%',
					band: readinessBand(value),
					tone: 'readiness',
					band_label: readinessBandLabel(value, 'public'),
				});
			});
		}
		return cards;
	}

	function oversightBandFromLabel(label) {
		var text = String(label || '');
		if (text.indexOf('Strong') >= 0) return 'low';
		if (text.indexOf('Moderate') >= 0) return 'moderate';
		if (text.indexOf('High') >= 0) return 'high';
		if (text.indexOf('Critical') >= 0) return 'critical';
		return 'moderate';
	}

	function keyExposureAreas(domainScores) {
		var rows = [];
		domainKeys.forEach(function (slug) {
			var d = domainScores[slug];
			if (!d || !d.questions_answered) return;
			rows.push({ slug: slug, label: d.label, risk: d.risk_percentage });
		});
		rows.sort(function (a, b) { return b.risk - a.risk; });
		return rows.slice(0, 3);
	}

	function localLeaderFocusAreas(domainScores) {
		var rows = [];
		domainKeys.forEach(function (slug) {
			var d = domainScores[slug];
			if (!d || !d.questions_answered) return;
			rows.push({
				slug: slug,
				label: d.label || domains[slug] || slug,
				pct: Math.round(d.readiness_percentage || 0),
				summary: 'This is one of the lowest readiness signals in this leader audit and should be reviewed with owners, evidence and a follow-up date.',
				likely_impact: [
					'Practice may vary across teams because expectations are not yet fully evidenced',
					'Governors or SLT may not have enough assurance to track improvement',
				],
				actions: [
					'Assign an owner and review date for this area',
					'Record one piece of evidence showing how practice will improve',
				],
			});
		});
		rows.sort(function (a, b) { return a.pct - b.pct; });
		return rows.slice(0, 4);
	}

	function calculate(role, answers) {
		var sums = {};
		var counts = {};
		domainKeys.forEach(function (k) { sums[k] = 0; counts[k] = 0; });

		(cfg.questions || []).forEach(function (q) {
			if (q.role !== role || !answers[q.id]) return;
			if (!questionApplies(q, answers)) return;
			var val = answers[q.id];
			var sc = scoreAnswer(q, val);
			if (sums[q.domain] !== undefined) {
				sums[q.domain] += sc;
				counts[q.domain] += 1;
			}
		});

		var domainScores = {};
		var riskVals = [];

		domainKeys.forEach(function (slug) {
			var avg = counts[slug] ? (sums[slug] / counts[slug]) / 3 * 100 : 0;
			var band = riskBand(avg);
			var readinessPct = Math.round((100 - avg) * 10) / 10;
			domainScores[slug] = {
				label: domains[slug],
				risk_percentage: Math.round(avg * 10) / 10,
				readiness_percentage: readinessPct,
				band: band,
				band_label: bandLabel(band),
				readiness_band: readinessBand(readinessPct),
				readiness_band_label: readinessBandLabel(readinessPct),
				questions_answered: counts[slug],
			};
			if (counts[slug]) riskVals.push(avg);
		});

		var overall = riskVals.length ? riskVals.reduce(function (a, b) { return a + b; }, 0) / riskVals.length : 0;
		var oband = riskBand(overall);
		var oversight = compositeOversight(role, answers);
		var depIndex = compositeDependency(role, answers, domainScores);

		var recs = [];
		var rank = { low: 0, moderate: 1, high: 2, critical: 3 };
		(cfg.recommendations || []).forEach(function (r) {
			var dom = domainScores[r.domain];
			if (!dom) return;
			if ((rank[dom.band] || 0) >= (rank[r.min_band] || 2)) recs.push(r);
		});

		var results = {
			role: role,
			risk_level: oband,
			risk_level_label: displayRiskLabel(oband, overall),
			readiness_level: readinessBand(Math.round(100 - overall)),
			readiness_level_label: readinessBandLabel(Math.round(100 - overall)),
			alignment_score: Math.round(100 - overall),
			dependency_index: depIndex,
			human_oversight_ratio: oversight.pct,
			human_oversight_label: oversight.label,
			privacy_risk: Math.round(domainScores.privacy ? domainScores.privacy.risk_percentage : 0),
			safeguarding_readiness: Math.round(domainScores.safeguarding ? domainScores.safeguarding.readiness_percentage : 0),
			governance_maturity: Math.round(domainScores.governance ? domainScores.governance.readiness_percentage : 0),
			overall_risk_percentage: Math.round(overall * 10) / 10,
			domain_scores: domainScores,
			recommendations: recs,
			key_exposure_areas: keyExposureAreas(domainScores),
		};
		if (role === 'parent') {
			results.parent_display_domains = parentDisplayDomainScores(role, answers);
			var parentOverall = parentOverallFromDisplay(results.parent_display_domains);
			results.alignment_score = parentOverall.alignment_score;
			results.overall_risk_percentage = parentOverall.overall_risk;
			results.risk_level = parentOverall.risk_level;
			results.risk_level_label = displayRiskLabel(parentOverall.risk_level, parentOverall.overall_risk);
			results.readiness_level = readinessBand(parentOverall.alignment_score);
			results.readiness_level_label = readinessBandLabel(parentOverall.alignment_score);
			results.key_exposure_areas = [];
		}
		if (role === 'student') {
			var studentOverall = studentOverallFromDomains(domainScores);
			results.alignment_score = studentOverall.alignment_score;
			results.overall_risk_percentage = studentOverall.overall_risk;
			results.risk_level = studentOverall.risk_level;
			results.risk_level_label = displayRiskLabel(studentOverall.risk_level, studentOverall.overall_risk);
			results.readiness_level = studentSkillBand(studentOverall.alignment_score).slug;
			results.readiness_level_label = studentSkillBandLabel(studentOverall.alignment_score);
			results.key_exposure_areas = [];
			results.recommendations = [];
		}
		if (role === 'support_staff') {
			results.support_display_domains = supportDisplayDomainScores(answers);
		}
		if (role === 'leader') {
			var leaderFocus = localLeaderFocusAreas(domainScores);
			var priority = leaderFocus.length && leaderFocus[0].actions && leaderFocus[0].actions.length
				? leaderFocus[0].actions[0]
				: 'Assign owners, evidence and review dates to the two weakest AI readiness domains.';
			results.key_exposure_areas = [];
			results.leader_results = {
				executive_summary: {
					intro: 'Your leader benchmark is ready. Use the lowest domains to prioritise governance, safeguarding and staff support.',
					strengths: [],
					priority_action: priority,
					priority_action_detail: {
						title: priority,
						body: 'This local result is shown while the full server report is prepared.',
					},
				},
				maturity: {
					title: i18n.leaderMetricGovernance || 'Governance Maturity',
					score: results.governance_maturity,
					description: '',
				},
				peer_benchmark: {
					your_score: results.alignment_score,
					average_score: Math.max(45, Math.min(70, results.alignment_score + 4)),
					top_quartile: Math.max(65, Math.min(90, results.alignment_score + 16)),
					sample_size: 0,
					is_estimated: true,
				},
				focus_areas: leaderFocus,
				risk_heatmap: keyExposureAreas(domainScores),
				next_steps: {
					hero: {
						title: 'Turn this audit into evidence',
						body: 'Use the weakest domain to plan one governance action before retaking the benchmark.',
						cta_text: 'Request support',
						key: 'governance_review',
					},
					resource_links: [],
				},
				school_rollout: null,
				ui: null,
			};
		}
		if (role === 'public') {
			results.public_display_domains = publicDisplayDomainScores(answers);
			var publicOverall = publicOverallFromDisplay(results.public_display_domains);
			results.alignment_score = publicOverall.alignment_score;
			results.overall_risk_percentage = publicOverall.overall_risk;
			results.risk_level = publicOverall.risk_level;
			results.risk_level_label = displayRiskLabel(publicOverall.risk_level, publicOverall.overall_risk);
			results.readiness_level = readinessLevel(publicOverall.alignment_score, 'public').slug;
			results.readiness_level_label = readinessBandLabel(publicOverall.alignment_score, 'public');
			results.key_exposure_areas = [];
			results.recommendations = [];
		}
		results.role_result_cards = roleResultCards(role, results);
		return results;
	}

	function showError(msg) {
		if (!el.error) return;
		el.error.textContent = msg;
		el.error.hidden = false;
		if (el.root) {
			el.root.classList.add('airb--show-error');
		}
		window.requestAnimationFrame(function () {
			el.error.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
		});
	}

	function hideError() {
		if (!el.error) return;
		el.error.hidden = true;
		el.error.textContent = '';
		if (el.root) {
			el.root.classList.remove('airb--show-error');
		}
	}

	function oversightZoneColor(v) {
		if (v <= 10) return 'var(--airb-crit)';
		if (v <= 25) return 'var(--airb-high)';
		if (v <= 50) return 'var(--airb-mod)';
		return 'var(--airb-low)';
	}

	function oversightZoneColorHex(v) {
		if (v <= 10) return '#b91c1c';
		if (v <= 25) return '#c2410c';
		if (v <= 50) return '#a16207';
		return '#15803d';
	}

	function domainColor(slug) {
		return domainColors[slug] || 'var(--airb-accent-fill)';
	}

	function sectionsForRole(role) {
		if (window.AIRB && AIRB.Audit && AIRB.Audit.sectionsForRole) {
			return AIRB.Audit.sectionsForRole(role, cfg, state.answers, profilePhase);
		}
		var sections = [];
		var index = {};
		questionsForRole(role).forEach(function (q) {
			var key = q.section || 'General';
			if (!index[key]) {
				index[key] = { name: key, domain: q.domain, questions: [] };
				sections.push(index[key]);
			}
			index[key].questions.push(q);
		});
		return sections;
	}

	function loadRoleCompletions() {
		try {
			return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
		} catch (e) {
			return {};
		}
	}

	function roleCompletionBandLabel(roleSlug, alignment, storedLabel) {
		if (roleSlug === 'student') {
			return studentSkillBandLabel(alignment);
		}
		return storedLabel || readinessBandLabel(alignment);
	}

	function persistRoleCompletion(resultsOrScore) {
		if (!state.role) return;
		var entry = { ts: Date.now() };
		if (typeof resultsOrScore === 'number') {
			entry.alignment = resultsOrScore;
			entry.readiness_label = roleCompletionBandLabel(state.role, resultsOrScore, '');
		} else if (resultsOrScore && typeof resultsOrScore === 'object') {
			if (typeof resultsOrScore.alignment_score !== 'number') return;
			entry.alignment = resultsOrScore.alignment_score;
			entry.readiness_label = roleCompletionBandLabel(
				state.role,
				resultsOrScore.alignment_score,
				resultsOrScore.readiness_level_label || ''
			);
			entry.risk_level = resultsOrScore.risk_level || '';
		} else {
			return;
		}
		try {
			var data = loadRoleCompletions();
			data[state.role] = entry;
			localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
		} catch (e) { /* private browsing */ }
	}

	function roleCompletionSummary(done, roleSlug) {
		if (!done || typeof done.alignment !== 'number') return '';
		var band = roleCompletionBandLabel(roleSlug, done.alignment, done.readiness_label);
		return (i18n.roleLastResult || 'Your last result: {n}% · {band}')
			.replace('{n}', String(done.alignment))
			.replace('{band}', band);
	}

	function questionsForRole(role) {
		if (window.AIRB && AIRB.Audit && AIRB.Audit.questionsForRole) {
			return AIRB.Audit.questionsForRole(role, cfg, state.answers, profilePhase);
		}
		return (cfg.questions || []).filter(function (q) {
			return q.role === role && questionApplies(q, state.answers);
		});
	}

	function leaderPhaseOptionsHtml() {
		var html = '<option value="">' + esc(i18n.schoolPhaseChoose) + '</option>';
		html += '<option value="primary"' + (state.schoolPhase === 'primary' ? ' selected' : '') + '>' + esc(i18n.schoolPhasePrimary) + '</option>';
		html += '<option value="secondary"' + (state.schoolPhase === 'secondary' ? ' selected' : '') + '>' + esc(i18n.schoolPhaseSecondary) + '</option>';
		html += '<option value="college"' + (state.schoolPhase === 'college' ? ' selected' : '') + '>' + esc(i18n.schoolPhaseCollege) + '</option>';
		html += '<option value="university"' + (state.schoolPhase === 'university' ? ' selected' : '') + '>' + esc(i18n.schoolPhaseUniversity) + '</option>';
		html += '<option value="other"' + (state.schoolPhase === 'other' ? ' selected' : '') + '>' + esc(i18n.schoolPhaseOther) + '</option>';
		return html;
	}

	function renderLeaderProfile() {
		var html = '<div class="airb__panel"><h3 class="airb__panel-title">' + esc(i18n.leaderProfileTitle || 'About your school') + '</h3>';
		if (i18n.leaderProfileHint) {
			html += '<p class="airb__muted">' + esc(i18n.leaderProfileHint) + '</p>';
		}
		html += '<label class="airb__label" for="airb-leader-phase">' + esc(i18n.leaderProfilePhase || i18n.schoolPhase) + '</label>';
		html += '<select class="airb__select" id="airb-leader-phase" required>' + leaderPhaseOptionsHtml() + '</select>';
		if (i18n.leaderProfilePhaseNote) {
			html += '<p class="airb__muted airb__profile-hint">' + esc(i18n.leaderProfilePhaseNote) + '</p>';
		}
		html += '</div>';

		el.audit.innerHTML = html;
		el.audit.hidden = false;
		el.role.hidden = true;
		el.contact.hidden = true;
		el.results.hidden = true;
		el.back.hidden = false;
		el.nav.hidden = false;
		if (el.next) {
			el.next.textContent = i18n.continueAudit || i18n.next || 'Continue';
		}
		el.progress.hidden = true;
		updateFlowChrome();
		scrollFlowToTop();
	}

	function renderRole() {
		var completions = loadRoleCompletions();
		var html = '<div class="airb__panel"><h3 class="airb__panel-title">' + esc(i18n.chooseRoleHeading || i18n.chooseRole) + '</h3><div class="airb__role-grid">';
		var benchmarks = cfg.role_benchmarks || {};
		Object.keys(cfg.roles || {}).forEach(function (slug) {
			var active = state.role === slug ? ' is-selected' : '';
			var bench = benchmarks[slug] || {};
			var done = completions[slug];
			html += '<button type="button" class="airb__role-card' + active + '" data-role="' + esc(slug) + '">';
			if (done && typeof done.alignment === 'number') {
				html += '<span class="airb__role-done">' + esc((i18n.roleDoneScore || '{n}%').replace('{n}', String(done.alignment))) + '</span>';
			}
			html += '<span class="airb__role-card-title">' + esc(cfg.roles[slug]) + '</span>';
			var tagline = bench.tagline || i18n.roleCardTagline || '';
			if (tagline) {
				html += '<span class="airb__role-card-blurb">' + esc(tagline) + '</span>';
			}
			if (done && typeof done.alignment === 'number') {
				html += '<span class="airb__role-card-result">' + esc(roleCompletionSummary(done, slug)) + '</span>';
			}
			html += '<span class="airb__role-card-go">' + esc(done ? (i18n.retakeAudit || 'Retake audit') : (i18n.startAudit || 'Start audit')) + ' →</span>';
			html += '</button>';
		});
		html += '</div>';
		html += '</div>';
		el.role.innerHTML = html;
		el.role.hidden = false;
		el.audit.hidden = true;
		el.contact.hidden = true;
		el.results.hidden = true;
		el.nav.hidden = true;
		el.back.hidden = true;
		el.progress.hidden = true;
		updateFlowChrome();
	}

	function questionInputHtml(q) {
		var qid = q.id;
		var html = '';

		if (q.type === 'slider') {
			var val = state.answers[qid] !== undefined ? state.answers[qid] : 50;
			var numVal = parseInt(val, 10) || 0;
			html += '<div class="airb__slider-wrap"><input type="range" class="airb__slider" id="airb-q-' + esc(qid) + '" data-airb-q="' + esc(qid) + '" min="0" max="100" step="1" value="' + val + '" />';
			html += '<div class="airb__slider-scale" aria-hidden="true"><span>0% unchanged</span><span>100% heavily edited</span></div>';
			html += '<output class="airb__slider-out" for="airb-q-' + esc(qid) + '">' + val + '% ' + esc(i18n.modifyLabel) + '</output></div>';
			html += '<p class="airb__slider-band" id="airb-band-' + esc(qid) + '" style="color:' + oversightZoneColor(numVal) + '">' + esc(oversightLabel(numVal)) + '</p>';
		} else if (q.type === 'select') {
			html += '<select class="airb__select" id="airb-q-' + esc(qid) + '" data-airb-q="' + esc(qid) + '"><option value="">' + esc(i18n.required) + '</option>';
			(q.options || []).forEach(function (o) {
				var sel = state.answers[qid] === o.value ? ' selected' : '';
				html += '<option value="' + esc(o.value) + '"' + sel + '>' + esc(o.label) + '</option>';
			});
			html += '</select>';
		} else {
			var maxLabelLen = (q.options || []).reduce(function (max, o) {
				return Math.max(max, String(o.label || '').length);
			}, 0);
			var optionClass = 'airb__options airb__options--pills' + (maxLabelLen > 22 ? ' airb__options--long' : '');
			html += '<div class="' + optionClass + '">';
			(q.options || []).forEach(function (o) {
				var checked = state.answers[qid] === o.value ? ' checked' : '';
				html += '<label class="airb__option"><input type="radio" name="airb-q-' + esc(qid) + '" data-airb-q="' + esc(qid) + '" value="' + esc(o.value) + '"' + checked + ' />' + esc(o.label) + '</label>';
			});
			html += '</div>';
		}
		return html;
	}

	function bindSectionInputs(section) {
		section.questions.forEach(function (q) {
			var input = document.getElementById('airb-q-' + q.id);
			if (!input) return;
			if (input.type === 'range') {
				var out = el.audit.querySelector('output[for="airb-q-' + q.id + '"]');
				var band = document.getElementById('airb-band-' + q.id);
				input.addEventListener('input', function () {
					var n = parseInt(input.value, 10) || 0;
					if (out) out.textContent = input.value + '% ' + i18n.modifyLabel;
					if (band) {
						band.textContent = oversightLabel(n);
						band.style.color = oversightZoneColor(n);
					}
					highlightNextButton();
				});
			}
		});
		el.audit.querySelectorAll('[data-airb-q]').forEach(function (input) {
			input.addEventListener('change', function () {
				refreshSectionConditionalVisibility(section);
				highlightNextButton();
			});
		});
	}

	function highlightNextButton() {
		if (!el.next || el.nav.hidden) return;
		el.next.classList.add('airb__btn--ready');
		window.setTimeout(function () {
			el.next.classList.remove('airb__btn--ready');
		}, 700);
	}

	function renderAuditSection(opts) {
		opts = opts || {};
		var section = state.sections[state.step];
		if (!section) return;

		var domainLabel = domains[section.domain] || section.domain;
		var html = '<div class="airb__panel airb__panel--audit">';
		html += '<header class="airb__audit-head">';
		html += '<div class="airb__domtag"><span class="airb__domtag-sq" style="background:' + esc(domainColor(section.domain)) + '"></span>';
		html += '<span class="airb__domtag-text">';
		html += '<span class="airb__domtag-section">' + esc(section.name) + '</span>';
		html += '<span class="airb__domtag-domain">' + esc(domainLabel) + '</span>';
		html += '</span></div>';
		html += '<p class="airb__audit-note">Choose the answer closest to current practice. The benchmark measures behaviour and readiness, not perfection.</p>';
		html += '</header>';

		html += '<div class="airb__audit-questions">';
		var visibleQuestions = visibleQuestionsInSection(section, state.answers);
		clampQuestionStep(visibleQuestions.length);
		var questionCounts = auditQuestionCounts(state.sections, state.answers, state.step);
		var singleQuestion = usesSingleQuestionFlow();
		section.questions.forEach(function (q) {
			var applies = questionApplies(q, state.answers);
			var qIndex = visibleQuestions.indexOf(q);
			var showQuestion = applies && (!singleQuestion || qIndex === state.questionStep);
			html += '<div class="airb__q-block" data-airb-qid="' + esc(q.id) + '"' + (showQuestion ? '' : ' hidden') + '>';
			var globalIndex = questionCounts.offsetBeforeSection + qIndex + 1;
			html += '<p class="airb__q-meta"' + (applies && questionCounts.total > 1 && !singleQuestion ? '' : ' hidden') + '>' + (applies && questionCounts.total > 1 && !singleQuestion ? questionNumberLabel(globalIndex, questionCounts.total) : '') + '</p>';
			html += '<p class="airb__q-title">' + esc(q.displayText || q.text) + '</p>';
			html += questionInputHtml(q);
			html += '</div>';
		});
		html += '</div></div>';

		el.audit.innerHTML = html;
		el.audit.hidden = false;
		el.role.hidden = true;
		el.contact.hidden = true;
		el.results.hidden = true;
		el.nav.hidden = false;
		el.back.hidden = state.step === 0 && (!singleQuestion || state.questionStep === 0);
		if (el.next) {
			el.next.textContent = i18n.next;
		}
		el.progress.hidden = false;
		syncAuditProgressUi(state.sections, state.answers, state.step);
		bindSectionInputs(section);
		refreshSectionConditionalVisibility(section);
		updateFlowChrome();
		var shouldScroll = opts.scrollToTop === true || (opts.scrollToTop !== false && state.questionStep === 0);
		if (shouldScroll) {
			scrollBenchmarkToTop();
			window.setTimeout(scrollBenchmarkToTop, 60);
		}
	}

	function isYoungRole() {
		return state.role === 'student' || state.role === 'parent';
	}

	function isSupportStaffRole() {
		return state.role === 'support_staff';
	}

	function isStaffRole() {
		return state.role === 'teacher' || state.role === 'leader' || state.role === 'support_staff';
	}

	function hasInterestForm(r) {
		return !!(r && r.interest_form && r.interest_form.options && r.interest_form.options.length);
	}

	function roleShowsInterestForm(role) {
		return role === 'teacher' || role === 'leader' || role === 'parent' || role === 'support_staff' || role === 'student';
	}

	function resolveInterestPrefill(slug) {
		slug = String(slug || '').trim();
		if (!slug) return '';
		var map = airbBenchmark.interestPrefillMap || {};
		return map[slug] || slug;
	}

	function mergeInterestFormShell(results, shell) {
		if (!results || !shell || hasInterestForm(results)) return results;
		var merged = JSON.parse(JSON.stringify(shell));
		merged.summary = {
			score: results.alignment_score != null ? results.alignment_score : 0,
			readiness_label: results.readiness_level_label || '',
			risk_level: results.risk_level || '',
			risk_level_label: results.risk_level_label || '',
		};
		if (results.interest_form && results.interest_form.weak_domains) {
			merged.weak_domains = results.interest_form.weak_domains;
		}
		results.interest_form = merged;
		return results;
	}

	function ensureInterestFormRendered() {
		if (!state.results || !state.role || !roleShowsInterestForm(state.role)) return false;
		var shells = airbBenchmark.interestForms || {};
		if (!hasInterestForm(state.results) && shells[state.role]) {
			mergeInterestFormShell(state.results, shells[state.role]);
		}
		if (!hasInterestForm(state.results) || !el.results) return false;
		if (document.getElementById('airb-interest')) return true;
		el.results.insertAdjacentHTML('beforeend', interestFormHtml(state.results));
		bindInterestForm();
		return !!document.getElementById('airb-interest');
	}

	function interestFormButtonLabel() {
		if (isParentRole()) return i18n.requestSupportParent || i18n.requestFullReport || 'Get support';
		if (isStudentRole()) return i18n.requestSupportStudent || i18n.requestFullReport || 'Get support';
		if (isSupportStaffRole()) return i18n.requestSupportSupport || i18n.requestFullReport || 'Request support';
		return i18n.requestFullReport || 'Request support from AI Awareness Day';
	}

	function yearGroupOptionsHtml() {
		var groups = i18n.yearGroups || {};
		var html = '<option value="">' + esc(i18n.yearGroupChoose || 'Select year group…') + '</option>';
		Object.keys(groups).forEach(function (key) {
			var sel = state.yearGroup === key ? ' selected' : '';
			html += '<option value="' + esc(key) + '"' + sel + '>' + esc(groups[key]) + '</option>';
		});
		return html;
	}

	function staffProfileFieldsHtml() {
		var html = '<div class="airb__contact-grid">';
		var hidePhase = state.role === 'leader' && profilePhase();
		if (!hidePhase) {
			html += '<div class="airb__contact-field">';
			html += '<label class="airb__label" for="airb-school-phase">' + esc(i18n.schoolPhase) + '</label>';
			html += '<select class="airb__select" id="airb-school-phase">' + leaderPhaseOptionsHtml() + '</select></div>';
		}
		html += '<div class="airb__contact-field">';
		html += '<label class="airb__label" for="airb-org-type">' + esc(i18n.orgType) + '</label>';
		html += '<select class="airb__select" id="airb-org-type">';
		html += '<option value="">' + esc(i18n.orgTypeChoose) + '</option>';
		html += '<option value="standalone"' + (state.orgType === 'standalone' ? ' selected' : '') + '>' + esc(i18n.orgStandalone) + '</option>';
		html += '<option value="mat"' + (state.orgType === 'mat' ? ' selected' : '') + '>' + esc(i18n.orgMat) + '</option>';
		html += '</select></div></div>';
		return html;
	}

	function roleSummaryHtml() {
		var roleLabel = (cfg.roles || {})[state.role] || state.role;
		return '<div class="airb__contact-role">' +
			'<span class="airb__label" id="airb-contact-role-label">' + esc(i18n.yourRole || 'Your role') + '</span>' +
			'<p class="airb__contact-role-value" id="airb-contact-role" aria-labelledby="airb-contact-role-label">' + esc(roleLabel) + '</p>' +
			'</div>';
	}

	function renderContact() {
		var contactHint = i18n.contactHint;
		if (state.role === 'teacher' && i18n.contactHintTeacher) contactHint = i18n.contactHintTeacher;
		if (state.role === 'support_staff' && i18n.contactHintSupport) contactHint = i18n.contactHintSupport;
		if (isPublicRole() && i18n.contactHintPublic) contactHint = i18n.contactHintPublic;

		var html = '<div class="airb__panel airb__contact-panel">';
		html += '<header class="airb__contact-header">';
		html += '<p class="airb__contact-eyebrow">' + esc(i18n.contactEyebrow || 'Final step') + '</p>';
		html += '<h3 class="airb__panel-title">' + esc(i18n.contactTitle || 'Personalise your results') + '</h3>';
		if (contactHint) html += '<p class="airb__contact-intro">' + esc(contactHint) + '</p>';
		html += '</header>';
		html += roleSummaryHtml();

		if (isYoungRole()) {
			var youngHint = state.role === 'parent' ? i18n.contactHintParent : i18n.contactHintYoung;
			html += '<section class="airb__contact-group" aria-labelledby="airb-contact-context-title">';
			html += '<h4 class="airb__contact-group-title" id="airb-contact-context-title">' + esc(i18n.contactContextTitle || 'Tailor your recommendations') + '</h4>';
			if (youngHint) html += '<p class="airb__contact-group-copy">' + esc(youngHint) + '</p>';
			var ygLabel = state.role === 'parent' ? (i18n.yearGroupParent || i18n.yearGroup) : i18n.yearGroup;
			html += '<label class="airb__label" for="airb-year-group">' + esc(ygLabel) + '</label>' +
				'<select class="airb__select" id="airb-year-group">' + yearGroupOptionsHtml() + '</select>';
			html += '</section>';
		} else {
			if (state.role === 'leader' || state.role === 'teacher' || state.role === 'support_staff') {
				html += '<section class="airb__contact-group" aria-labelledby="airb-contact-context-title">';
				html += '<h4 class="airb__contact-group-title" id="airb-contact-context-title">' + esc(i18n.contactContextTitle || 'Tailor your recommendations') + '</h4>';
				html += '<p class="airb__contact-group-copy">' + esc(i18n.contactContextHint || 'Add school context to make your recommendations more relevant.') + '</p>';
				html += '<label class="airb__label" for="airb-school">' + esc(i18n.schoolOptional) + '</label>' +
					'<input type="text" class="airb__input" id="airb-school" value="' + esc(state.school) + '" autocomplete="organization"' + (i18n.schoolOptionalHint ? ' aria-describedby="airb-school-hint"' : '') + ' />';
				if (i18n.schoolOptionalHint) {
					html += '<p class="airb__field-hint" id="airb-school-hint">' + esc(i18n.schoolOptionalHint) + '</p>';
				}
				if (state.role === 'teacher' || state.role === 'support_staff') {
					html += staffProfileFieldsHtml();
				}
				html += '</section>';
			}

			html += '<section class="airb__contact-group airb__contact-group--email" aria-labelledby="airb-contact-email-title">';
			html += '<h4 class="airb__contact-group-title" id="airb-contact-email-title">' + esc(i18n.contactEmailTitle || 'Get a copy') + '</h4>';
			html += '<p class="airb__contact-group-copy">' + esc(i18n.contactEmailHint || 'Enter an email only if you want your report sent to you.') + '</p>';
			html += '<label class="airb__label" for="airb-email">' + esc(i18n.emailOptional) + '</label>' +
				'<input type="email" class="airb__input" id="airb-email" value="' + esc(state.email) + '" autocomplete="email"' + (i18n.emailOptionalHint ? ' aria-describedby="airb-email-hint"' : '') + ' />';
			if (i18n.emailOptionalHint) {
				html += '<p class="airb__field-hint" id="airb-email-hint">' + esc(i18n.emailOptionalHint) + '</p>';
			}
			html += '</section>';
		}

		if (i18n.contactPrivacyNote) {
			html += '<p class="airb__contact-note">' + esc(i18n.contactPrivacyNote) + '</p>';
		}

		html += '</div>';

		el.contact.innerHTML = html;

		el.contact.hidden = false;
		el.role.hidden = true;
		el.audit.hidden = true;
		el.results.hidden = true;
		el.back.hidden = false;
		el.nav.hidden = false;
		if (el.next) {
			el.next.textContent = i18n.submit;
		}
		el.progress.hidden = true;
		updateFlowChrome();
		if (state.studentFocusSchool) {
			var schoolInput = document.getElementById('airb-school');
			if (schoolInput && schoolInput.focus) {
				schoolInput.focus();
			}
			state.studentFocusSchool = false;
		}
		scrollFlowToTop();
	}

	function barHtml(slug, label, pct, band, invert) {
		var display = invert ? (100 - pct) : pct;
		var color = domainColor(slug);
		return '<div class="airb__bar-row airb__bar-row--' + band + '">' +
			'<span class="airb__bar-label"><span class="airb__bar-dot" style="background:' + esc(color) + '"></span>' + esc(label) + '</span>' +
			'<div class="airb__bar-track" role="progressbar" aria-valuenow="' + display + '" aria-valuemin="0" aria-valuemax="100">' +
			'<div class="airb__bar-fill" style="width:' + display + '%;background:' + esc(color) + '"></div></div>' +
			'<span class="airb__bar-val">' + display + '% <span class="airb__bar-band">(' + esc(bandLabel(band)) + ')</span></span></div>';
	}

	function focusDomainsHtml(r) {
		if (isParentRole()) {
			return '';
		}
		if ((isTeacherRole() && r.teacher_results) || (isStudentRole() && r.student_results) || (isLeaderRole() && r.leader_results) || (isSupportStaffRole() && r.support_results)) {
			return '';
		}

		var scored = [];
		domainKeys.forEach(function (slug) {
			var d = r.domain_scores[slug];
			if (!d || !d.questions_answered) return;
			scored.push({
				slug: slug,
				label: d.label,
				readiness: d.readiness_percentage,
			});
		});
		if (!scored.length) return '';
		scored.sort(function (a, b) { return a.readiness - b.readiness; });
		var weakest = scored.slice(0, 3);
		var html = '<div class="airb__res-panel airb__res-panel--focus"><h3>' + esc(i18n.domainFocus || 'What to focus on') + '</h3>';
		weakest.forEach(function (item) {
			var rec = domainRecs[item.slug] || '';
			var pct = Math.round(item.readiness);
			html += '<div class="airb__res-rec"><span class="airb__res-rec-dot" style="background:' + esc(readinessBandColor(pct)) + '"></span>';
			html += '<span><strong>' + esc(item.label) + ' — ' + pct + '%.</strong> ' + esc(rec) + '</span></div>';
		});
		return html + '</div>';
	}

	function domainReadinessRowsHtml(r, panelHeading) {
		if (isParentRole() && r.parent_display_domains) {
			var rows = '';
			var heading = parentResult.scores_heading || i18n.parentScores || i18n.domainBreakdown || 'Your scores';
			Object.keys(r.parent_display_domains).forEach(function (slug) {
				var d = r.parent_display_domains[slug];
				if (!d || !d.questions_answered) return;
				var isRisk = d.metric_type === 'risk';
				var pct = Math.round(isRisk ? d.risk_percentage : d.readiness_percentage);
				var color = isRisk ? riskScoreColor(pct) : (d.color || readinessBandColor(pct));
				var barWidth = isRisk ? pct : pct;
				rows += '<div class="airb__res-row' + (isRisk ? ' airb__res-row--risk' : '') + '">';
				rows += '<span class="airb__res-row-nm">' + esc(d.label) + '</span>';
				rows += '<span class="airb__res-track"><i style="width:' + barWidth + '%;background:' + esc(color) + '"></i></span>';
				rows += '<span class="airb__res-row-pc" style="color:' + esc(color) + '">' + pct + '%</span></div>';
			});
			if (!rows) return '';
			return '<div class="airb__res-panel airb__res-panel--domains"><h3>' + esc(heading) + '</h3>' + rows + '</div>';
		}

		var rows = '';
		var domainHints = (isTeacherRole() && teacherResult.domain_descriptions) ? teacherResult.domain_descriptions : {};
		domainKeys.forEach(function (slug) {
			var d = r.domain_scores[slug];
			if (!d || !d.questions_answered) return;
			var pct = Math.round(d.readiness_percentage);
			var hint = domainHints[slug] || '';
			rows += '<div class="airb__res-row"><span class="airb__res-row-nm"' + (hint ? ' title="' + esc(hint) + '"' : '') + '>' + esc(d.label) + '</span>';
			rows += '<span class="airb__res-track"><i style="width:' + pct + '%;background:' + esc(readinessBandColor(pct)) + '"></i></span>';
			rows += '<span class="airb__res-row-pc">' + pct + '%</span></div>';
		});
		if (!rows) return '';
		var heading = panelHeading || i18n.readinessBreakdown || 'Readiness breakdown';
		return '<div class="airb__res-panel airb__res-panel--domains"><h3>' + esc(heading) + '</h3>' + rows + '</div>';
	}

	function riskIndicatorsHtml(r, panelHeading) {
		if (isParentRole() || (isLeaderRole() && !!r.leader_results)) return '';

		var rows = '';
		var dep = r.dependency_index;
		if (roleShowsDependency(state.role) && typeof dep === 'number') {
			rows += '<div class="airb__res-row airb__res-row--risk">';
			rows += '<span class="airb__res-row-nm">' + esc(
				isTeacherRole() ? (i18n.dependency || 'AI Dependency Index') : (i18n.dependencyRisk || 'AI Dependency Risk')
			) + '</span>';
			rows += '<span class="airb__res-track"><i style="width:' + dep + '%;background:' + esc(dependencyColor(dep)) + '"></i></span>';
			rows += '<span class="airb__res-row-pc" style="color:' + esc(dependencyColor(dep)) + '">' + dep + '%</span></div>';
		}

		var risk = Math.round(r.overall_risk_percentage || 0);
		rows += '<div class="airb__res-row airb__res-row--risk">';
		rows += '<span class="airb__res-row-nm">' + esc(i18n.statRisk || 'AI risk score') + '</span>';
		rows += '<span class="airb__res-track"><i style="width:' + risk + '%;background:' + esc(riskScoreColor(risk)) + '"></i></span>';
		rows += '<span class="airb__res-row-pc" style="color:' + esc(riskScoreColor(risk)) + '">' + risk + '%</span></div>';

		if (!rows) return '';
		var heading = panelHeading || i18n.riskIndicators || 'Risk indicators';
		return '<div class="airb__res-panel airb__res-panel--domains airb__res-panel--risk"><h3>' + esc(heading) + '</h3>' + rows + '</div>';
	}

	function oversightPanelHtml(r, opts) {
		opts = opts || {};
		var val = oversightGaugeValue(r);
		var studentMode = isStudentRole() && !!r.student_results;
		var supportMode = isSupportStaffRole() && !!r.support_results;
		var html = '<div class="airb__res-panel airb__res-panel--gauge" data-oversight-value="' + Math.round(val) + '"><h3>' + esc(i18n.oversight) + '</h3>';
		if (val === null) {
			if (!opts.showNa) return '';
			html += '<p class="airb__res-na">' + esc(i18n.oversightNa || 'Not measured for this audience.') + '</p>';
			return html + '</div>';
		}
		var oversightCopy = oversightUiCopy(r, val);
		var label = oversightCopy.signal || (r.human_oversight_label || '');
		var help = studentMode
			? 'How often you check, edit or question AI answers before relying on them.'
			: supportMode
				? 'Share of AI output reviewed or changed before use in emails, letters and reports.'
				: (oversightCopy.consequence
					? oversightCopy.consequence
					: 'Share of AI output reviewed or changed before use. Below 26% signals reliance without meaningful human review.');
		html += '<div class="airb__res-gauge-wrap">' + oversightGaugeSvg(val, esc(i18n.oversight) + ': ' + Math.round(val) + '%') + '</div>';
		if (label) html += '<p class="airb__gauge-band" style="color:' + oversightZoneColor(val) + '">' + esc(label) + '</p>';
		html += '<p class="airb__gauge-help">' + esc(help) + '</p>';
		return html + '</div>';
	}

	function parentFocusDomainsHtml(r) {
		var pr = r.parent_results;
		if (pr && pr.journey_tier === 'high') return '';
		var topicsBySlug = {};
		(parentResult.focus_topics || []).forEach(function (topic) {
			if (topic.slug) topicsBySlug[topic.slug] = topic;
		});
		var scored = [];
		Object.keys(r.parent_display_domains || {}).forEach(function (slug) {
			var d = r.parent_display_domains[slug];
			if (!d || !d.questions_answered) return;
			var isRisk = d.metric_type === 'risk';
			var readiness = isRisk ? (100 - d.risk_percentage) : d.readiness_percentage;
			scored.push({ slug: slug, readiness: readiness, label: d.label });
		});
		scored.sort(function (a, b) { return a.readiness - b.readiness; });
		var weakest = scored.filter(function (s) { return s.readiness < 75; }).slice(0, 3);
		if (!weakest.length) return '';
		var html = '<div class="airb__res-panel airb__res-panel--focus airb__res-panel--parent-focus"><h3>' + esc(i18n.domainFocus || 'What to focus on') + '</h3>';
		html += '<div class="airb__parent-focus-grid">';
		weakest.forEach(function (item) {
			var topic = topicsBySlug[item.slug] || { label: item.label, body: '' };
			html += '<article class="airb__parent-focus-card">';
			html += '<h4 class="airb__parent-focus-title">' + esc(topic.label || item.label) + '</h4>';
			if (topic.body) html += '<p class="airb__parent-focus-body">' + esc(topic.body) + '</p>';
			html += '</article>';
		});
		return html + '</div></div>';
	}

	function parentHelpSupportHtml(pr) {
		if (!pr) return '';
		var links = (pr.next_steps && pr.next_steps.resource_links && pr.next_steps.resource_links.length)
			? pr.next_steps.resource_links
			: (pr.resource_links || []);
		if (!links.length) return '';
		return benchmarkHelpSupportHtml(
			{ resource_links: links },
			parentResult.help_support_heading || i18n.helpSupportHeading || 'Further reading and tips to guide you',
			parentResult.help_support_heading_short || i18n.helpSupportHeadingShort || 'Read more & tips'
		);
	}

	function parentResultsHtml(r) {
		var pr = r.parent_results;
		if (!pr) return '';

		var model = state.dashboardModel;
		if (!model && window.AIRB && AIRB.DashboardModel && AIRB.DashboardModel.buildParent) {
			model = AIRB.DashboardModel.buildParent(r, cfg);
		}

		if (model && window.AIRB && AIRB.ParentDashboard && AIRB.ParentDashboard.render) {
			var renderOpts = parentDashboardRenderOpts();
			var starters = pr.conversation_starters && pr.conversation_starters.length
				? pr.conversation_starters
				: (parentResult.conversation_starters || []);
			if (starters.length) {
				renderOpts.conversationHtml = function () {
					return parentConversationStartersHtml(starters, parentResult.conversation_section_intro || '');
				};
			}
			renderOpts.shareCardHtml = function () {
				return parentShareCardHtml(pr);
			};
			return AIRB.ParentDashboard.render(r, model, renderOpts);
		}

		var html = '';
		try {
			if (window.AIRB && AIRB.Roles && AIRB.Roles.parentFocusTopics) {
				html += AIRB.Roles.parentFocusTopics(r, parentFocusRenderOpts());
			}
		} catch (err) {
			if (window.console && console.error) console.error('AIRB parentFocusTopics failed', err);
		}

		var starters = pr.conversation_starters && pr.conversation_starters.length
			? pr.conversation_starters
			: (parentResult.conversation_starters || []);
		if (starters.length) {
			html += parentConversationStartersHtml(starters, parentResult.conversation_section_intro || '');
		}

		html += parentShareCardHtml(pr);
		html += parentHelpSupportHtml(pr);
		return benchmarkResultsBodyHtml(html);
	}

	function parentDashboardRenderOpts() {
		return {
			esc: esc,
			parentResult: parentResult,
			sectionLabelHtml: leaderSectionLabel,
			cardHeadingHtml: benchmarkCardHeadingHtml,
			practiceHeading: parentResult.focus_practice_heading_short || i18n.focusPracticeHeadingShort || 'Areas to improve',
			tipsHeading: 'Practical next steps',
			guidanceToggle: 'View areas to improve',
			focusGuidanceMax: 75,
			focusGuidanceAccordionHtml: focusGuidanceAccordionHtml,
			hideFocusSummary: true,
			resourcesHtml: function () {
				var pr = state.results && state.results.parent_results;
				var links = (pr && pr.resource_links) ? pr.resource_links : [];
				if (!links.length && pr && pr.next_steps && pr.next_steps.resource_links) {
					links = pr.next_steps.resource_links;
				}
				var html = '';
				if (links.length) {
					var intro = 'Suggested next steps after the audit, kept separate from the follow-up request form below.';
					html += '<section class="airb__leader-help-support airb__benchmark-help-support">' +
						'<div class="airb__resources-header">' +
						'<p class="airb__leader-section-label">' + esc(parentResult.help_support_heading || 'Parent guides & resources') + '</p>' +
						'<p class="airb__resources-intro">' + esc(intro) + '</p>' +
						'</div>' +
						resultsResourceLinksHtml(links, { cardMode: true, demoCards: true }) +
						'</section>';
				}
				html += teacherBreakingNowResourceHtml(links);
				return html;
			},
			resultsBodyHtml: benchmarkResultsBodyHtml,
		};
	}

	function bindParentDashboard() {
		if (window.AIRB && AIRB.ParentDashboard && AIRB.ParentDashboard.bind) {
			AIRB.ParentDashboard.bind(el.results);
		}
	}

	function publicHelpSupportHtml(pr) {
		if (!pr) return '';
		var links = pr.resource_links || [];
		if (!links.length) return '';
		return benchmarkHelpSupportHtml(
			{ resource_links: links },
			publicResult.help_support_heading || i18n.helpSupportHeading || 'Further reading',
			publicResult.help_support_heading_short || i18n.helpSupportHeadingShort || 'Read more'
		);
	}

	function publicResultsHtml(r) {
		var pr = r.public_results;
		if (!pr) return '';

		var model = state.dashboardModel;
		if (!model && window.AIRB && AIRB.DashboardModel && AIRB.DashboardModel.buildPublic) {
			model = AIRB.DashboardModel.buildPublic(r, cfg);
		}

		if (model && window.AIRB && AIRB.PublicDashboard && AIRB.PublicDashboard.render) {
			var renderOpts = publicDashboardRenderOpts();
			renderOpts.shareCardHtml = function () {
				return publicShareCardHtml(pr);
			};
			return AIRB.PublicDashboard.render(r, model, renderOpts);
		}

		var html = '';

		if (pr.domain_rows && pr.domain_rows.length) {
			html += leaderSectionLabel(
				publicResult.domains_section_heading || 'Your scores — 5 domains',
				publicResult.domains_section_heading_short || '5 domains'
			);
			html += publicDomainScoresCardHtml(pr.domain_rows);
		}

		try {
			if (window.AIRB && AIRB.Roles && AIRB.Roles.publicStrengths) {
				html += AIRB.Roles.publicStrengths(r, publicFocusRenderOpts());
			}
		} catch (err) {
			if (window.console && console.error) console.error('AIRB publicStrengths failed', err);
		}

		try {
			if (window.AIRB && AIRB.Roles && AIRB.Roles.publicFocusAreas) {
				html += AIRB.Roles.publicFocusAreas(r, publicFocusRenderOpts());
			}
		} catch (err) {
			if (window.console && console.error) console.error('AIRB publicFocusAreas failed', err);
		}

		html += publicShareCardHtml(pr);
		html += publicHelpSupportHtml(pr);
		return benchmarkResultsBodyHtml(html);
	}

	function publicDashboardRenderOpts() {
		return {
			esc: esc,
			publicResult: publicResult,
			sectionLabelHtml: leaderSectionLabel,
			cardHeadingHtml: benchmarkCardHeadingHtml,
			practiceHeading: publicResult.focus_practice_heading_short || i18n.focusPracticeHeadingShort || 'Areas to improve',
			tipsHeading: publicResult.focus_tips_heading_short || 'Practical next steps',
			guidanceToggle: 'View areas to improve',
			focusGuidanceMax: 50,
			focusStackIntro: 'These cards expand your lowest domain scores into privacy risks and habits to tighten.',
			focusGuidanceAccordionHtml: focusGuidanceAccordionHtml,
			hideFocusSummary: true,
			resourcesHtml: function () {
				var pr = state.results && state.results.public_results;
				var links = (pr && pr.resource_links) ? pr.resource_links : [];
				var html = '';
				if (links.length) {
					var intro = 'Suggested reading to strengthen your personal AI safety habits.';
					html += '<section class="airb__leader-help-support airb__benchmark-help-support">' +
						'<div class="airb__resources-header">' +
						'<p class="airb__leader-section-label">' + esc(publicResult.help_support_heading || 'Further reading') + '</p>' +
						'<p class="airb__resources-intro">' + esc(intro) + '</p>' +
						'</div>' +
						resultsResourceLinksHtml(links, { cardMode: true, demoCards: true }) +
						'</section>';
				}
				html += teacherBreakingNowResourceHtml(links);
				return html;
			},
			resultsBodyHtml: benchmarkResultsBodyHtml,
		};
	}

	function bindPublicDashboard() {
		if (window.AIRB && AIRB.PublicDashboard && AIRB.PublicDashboard.bind) {
			AIRB.PublicDashboard.bind(el.results);
		}
	}

	function parentPriorityFocusHtml() {
		var focus = parentResult.priority_focus || {};
		if (!focus.intro && !focus.title) return '';
		var html = '<aside class="airb__insight airb__insight--parent">';
		html += '<span class="airb__insight-label">' + esc(i18n.insightLabel || 'Your priority focus') + '</span>';
		if (focus.intro && focus.title) {
			html += '<p>' + esc(focus.intro) + ' ' + esc(focus.title) + '</p>';
		} else if (focus.title) {
			html += '<p>' + esc(focus.title) + '</p>';
		} else if (focus.intro) {
			html += '<p>' + esc(focus.intro) + '</p>';
		}
		if (focus.body) html += '<p>' + esc(focus.body) + '</p>';
		html += '<button type="button" class="airb__btn airb__btn--primary airb__btn--sm" data-airb-open-interest="parent_resources">' + esc(i18n.requestSupportParent || 'Get parent support') + '</button>';
		return html + '</aside>';
	}

	function parentExposureListHtml() {
		var areas = parentResult.exposure_areas || [];
		if (!areas.length) return '';
		var html = '<div class="airb__res-panel airb__res-panel--exposure"><h3>' + esc(i18n.exposure || 'Key exposure areas') + '</h3><ol class="airb__exposure-list">';
		areas.forEach(function (item) {
			html += '<li>' + esc(item) + '</li>';
		});
		return html + '</ol></div>';
	}

	function strengthsSectionHtml(strengths, heading) {
		if (!strengths || !strengths.length) return '';
		var html = '<section class="airb__strengths">';
		html += '<h3 class="airb__strengths-title">' + esc(heading) + '</h3>';
		html += '<div class="airb__strength-grid">';
		strengths.forEach(function (item) {
			html += '<article class="airb__strength-card">';
			html += '<span class="airb__strength-mark" aria-hidden="true">✓</span>';
			html += '<p class="airb__strength-text">' + esc(item) + '</p>';
			html += '</article>';
		});
		return html + '</div></section>';
	}

	function opportunitiesSectionHtml(opportunities, heading, variant) {
		if (!opportunities || !opportunities.length) return '';
		var html = '<section class="airb__opportunities' + (variant ? ' airb__opportunities--' + variant : '') + '">';
		if (heading) {
			html += '<h3 class="airb__opportunities-title">' + esc(heading) + '</h3>';
		}
		html += '<div class="airb__opportunity-grid">';
		opportunities.forEach(function (opp) {
			html += '<article class="airb__opportunity-card">';
			html += '<header class="airb__opportunity-head">';
			html += '<h4 class="airb__opportunity-label">' + esc(opp.label) + '</h4>';
			if (typeof opp.pct === 'number') {
				html += '<span class="airb__opportunity-pct">' + opp.pct + '%</span>';
			}
			html += '</header>';
			if (opp.summary) {
				html += '<p class="airb__opportunity-summary">' + esc(opp.summary) + '</p>';
			}
			var tipsHtml = '';
			if (opp.tips && opp.tips.length) {
				tipsHtml += '<ul class="airb__opportunity-tips">';
				opp.tips.forEach(function (tip) {
					tipsHtml += '<li>' + esc(tip) + '</li>';
				});
				tipsHtml += '</ul>';
			}
			var detailHtml = opp.detail ? '<p class="airb__opportunity-detail">' + esc(opp.detail) + '</p>' : '';
			if (variant === 'student' && opp.slug !== 'privacy') {
				html += detailHtml + tipsHtml;
			} else {
				html += tipsHtml + detailHtml;
			}
			html += '</article>';
		});
		return html + '</div></section>';
	}

	function summaryMetricsSectionHtml(metrics, title, options) {
		if (!metrics || !metrics.length) return '';
		options = options || {};
		var sectionClass = 'airb__summary-metrics' + (options.modifier ? ' airb__summary-metrics--' + options.modifier : '');
		var html = '<section class="' + sectionClass + '">';
		if (title) {
			html += '<h4 class="airb__summary-metrics-title">' + esc(title) + '</h4>';
		}
		html += '<div class="airb__summary-grid">';
		metrics.forEach(function (row) {
			html += '<div class="airb__summary-metric">';
			html += '<span class="airb__summary-metric-lab">' + esc(row.label) + '</span>';
			var valStyle = row.color ? ' style="color:' + esc(row.color) + '"' : '';
			html += '<span class="airb__summary-metric-val"' + valStyle + '>' + esc(row.value) + '</span>';
			if (options.showBand && row.band) {
				html += '<span class="airb__summary-metric-band">' + esc(row.band) + '</span>';
			}
			html += '</div>';
		});
		return html + '</div></section>';
	}

	function teacherDashboardHtml(r) {
		if (!r || !isTeacherRole()) return '';

		var html = '<section class="airb__teacher-dashboard" aria-label="' + esc(i18n.teacherScoreBreakdown || 'Score breakdown') + '">';
		var domainsHtml = domainReadinessRowsHtml(r, i18n.readinessScoreByDomain || 'Readiness score — by domain');
		if (domainsHtml) {
			html += domainsHtml;
		}
		var risksHtml = riskIndicatorsHtml(r, i18n.riskScoreDetail || 'AI risk score & AI Dependency Index — detail');
		if (risksHtml) html += risksHtml;
		return html + '</section>';
	}

	function teacherResultsHtml(r) {
		var tr = r.teacher_results;
		if (!tr) return '';

		var model = state.dashboardModel;
		if (!model && window.AIRB && AIRB.DashboardModel && AIRB.DashboardModel.buildTeacher) {
			model = AIRB.DashboardModel.buildTeacher(r, cfg);
		}

		if (model && window.AIRB && AIRB.TeacherDashboard && AIRB.TeacherDashboard.render) {
			var dashboardOpts = teacherDashboardRenderOpts();
			dashboardOpts.resourcesHtml = function () {
				var nextSteps = tr.next_steps;
				var links = (nextSteps && nextSteps.resource_links) ? nextSteps.resource_links : [];
				var html = '';
				if (tr.next_steps && tr.next_steps.rollout) {
					html += teacherRolloutCardHtml(tr.next_steps.rollout);
				}
				if (links.length) {
					var intro = 'Suggested next steps after the audit, kept separate from the follow-up request form below.';
					html += '<section class="airb__leader-help-support airb__benchmark-help-support">' +
						'<div class="airb__resources-header">' +
						'<p class="airb__leader-section-label">CPD for teachers and students</p>' +
						'<p class="airb__resources-intro">' + esc(intro) + '</p>' +
						'</div>' +
						resultsResourceLinksHtml(links, { cardMode: true, demoCards: true }) +
						'</section>';
				}
				html += teacherBreakingNowResourceHtml(links);
				return html;
			};
			return AIRB.TeacherDashboard.render(r, model, dashboardOpts);
		}

		var html = '';
		try {
			if (window.AIRB && AIRB.Roles && AIRB.Roles.teacherStrengths) {
				html += AIRB.Roles.teacherStrengths(r, teacherStrengthRenderOpts());
			}
		} catch (err) {
			if (window.console && console.error) {
				console.error('AIRB teacherStrengths failed', err);
			}
		}

		try {
			if (window.AIRB && AIRB.Roles && AIRB.Roles.teacherFocusAreas) {
				html += AIRB.Roles.teacherFocusAreas(r, teacherFocusRenderOpts());
			}
		} catch (err) {
			if (window.console && console.error) {
				console.error('AIRB teacherFocusAreas failed', err);
			}
		}

		if (tr.next_steps && tr.next_steps.rollout) {
			html += leaderSectionLabel(
				teacherResult.rollout_section_heading || 'Your next unlock — whole-school picture',
				teacherResult.rollout_section_heading_short || 'Your next unlock'
			);
			html += teacherRolloutCardHtml(tr.next_steps.rollout);
		}

		html += teacherPathwayCtaHtml(tr.next_steps);
		html += teacherHelpSupportHtml(tr.next_steps);
		return benchmarkResultsBodyHtml(html);
	}

	function teacherDashboardRenderOpts() {
		return {
			esc: esc,
			teacherResult: teacherResult,
			sectionLabelHtml: leaderSectionLabel,
			cardHeadingHtml: benchmarkCardHeadingHtml,
			practiceHeading: teacherResult.focus_practice_heading_short || i18n.focusPracticeHeadingShort || 'Areas to improve',
			tipsHeading: 'Practical next steps',
			guidanceToggle: 'View areas to improve',
			focusGuidanceMax: 75,
			guidanceToggleClassroom: 'View classroom impact',
			hideFocusSummary: true,
			leaderFocusBadge: leaderFocusBadge,
			leaderFocusSeverity: leaderFocusSeverity,
			teacherBiasEqualityFocusNote: teacherBiasEqualityFocusNote,
			focusGuidanceAccordionHtml: focusGuidanceAccordionHtml,
			rolloutCardHtml: teacherRolloutCardHtml,
			resultsBodyHtml: benchmarkResultsBodyHtml,
		};
	}

	function bindTeacherDashboard() {
		if (window.AIRB && AIRB.TeacherDashboard && AIRB.TeacherDashboard.bind) {
			AIRB.TeacherDashboard.bind(el.results);
		}
	}

	function teacherStrengthRenderOpts() {
		return {
			esc: esc,
			teacherResult: teacherResult,
			cardHeadingHtml: benchmarkCardHeadingHtml,
		};
	}

	function teacherFocusRenderOpts() {
		return {
			esc: esc,
			teacherResult: teacherResult,
			sectionLabelHtml: leaderSectionLabel,
			practiceHeading: teacherResult.focus_practice_heading_short || i18n.focusPracticeHeadingShort || 'Areas to improve',
			tipsHeading: 'Practical next steps',
			guidanceToggle: 'View areas to improve',
			focusGuidanceMax: 75,
			leaderFocusBadge: leaderFocusBadge,
			leaderFocusSeverity: leaderFocusSeverity,
			teacherBiasEqualityFocusNote: teacherBiasEqualityFocusNote,
			focusGuidanceAccordionHtml: focusGuidanceAccordionHtml,
		};
	}

	function supportFocusRenderOpts() {
		return {
			esc: esc,
			supportResult: supportResult,
			sectionLabelHtml: leaderSectionLabel,
			cardHeadingHtml: benchmarkCardHeadingHtml,
			practiceHeading: supportResult.focus_practice_heading_short || i18n.focusPracticeHeadingShort || 'Areas to improve',
			tipsHeading: 'Practical next steps',
			guidanceToggle: 'View areas to improve',
			focusGuidanceMax: 75,
			supportFocusSeverity: supportFocusSeverity,
			focusGuidanceAccordionHtml: focusGuidanceAccordionHtml,
		};
	}

	function studentFocusRenderOpts() {
		return {
			esc: esc,
			studentResult: studentResult,
			sectionLabelHtml: leaderSectionLabel,
			cardHeadingHtml: benchmarkCardHeadingHtml,
			practiceHeading: studentResult.focus_practice_heading_short || i18n.focusPracticeHeadingShort || 'Areas to improve',
			tipsHeading: 'Practical next steps',
			guidanceToggle: 'View areas to improve',
			focusGuidanceMax: 70,
			studentFocusBadge: studentFocusBadge,
			focusGuidanceAccordionHtml: focusGuidanceAccordionHtml,
		};
	}

	function parentFocusRenderOpts() {
		return {
			esc: esc,
			parentResult: parentResult,
			sectionLabelHtml: leaderSectionLabel,
			parentFocusBadge: parentFocusBadge,
			focusGuidanceAccordionHtml: focusGuidanceAccordionHtml,
			practiceHeading: parentResult.focus_practice_heading_short || i18n.focusPracticeHeadingShort || 'Areas to improve',
			tipsHeading: 'Practical next steps',
			guidanceToggle: 'View areas to improve',
			focusGuidanceMax: 75,
			hideFocusSummary: true,
			i18n: i18n,
			legacyFocusHtml: parentFocusDomainsHtml,
		};
	}

	function publicFocusRenderOpts() {
		return {
			esc: esc,
			publicResult: publicResult,
			sectionLabelHtml: leaderSectionLabel,
			cardHeadingHtml: benchmarkCardHeadingHtml,
			parentFocusBadge: parentFocusBadge,
			focusGuidanceAccordionHtml: focusGuidanceAccordionHtml,
			practiceHeading: publicResult.focus_practice_heading_short || i18n.focusPracticeHeadingShort || 'Areas to improve',
			tipsHeading: publicResult.focus_tips_heading_short || 'Practical next steps',
			guidanceToggle: 'View areas to improve',
			focusGuidanceMax: 50,
			i18n: i18n,
		};
	}

	function supportResultsHtml(r) {
		var sr = r.support_results;
		if (!sr) return '';

		var model = state.dashboardModel;
		if (!model && window.AIRB && AIRB.DashboardModel && AIRB.DashboardModel.buildSupport) {
			model = AIRB.DashboardModel.buildSupport(r, cfg);
		}

		if (model && window.AIRB && AIRB.SupportDashboard && AIRB.SupportDashboard.render) {
			var renderOpts = supportDashboardRenderOpts();
			return AIRB.SupportDashboard.render(r, model, renderOpts);
		}

		var html = '';
		try {
			if (window.AIRB && AIRB.Roles && AIRB.Roles.supportStrengths) {
				html += AIRB.Roles.supportStrengths(r, supportFocusRenderOpts());
			}
		} catch (err) {
			if (window.console && console.error) console.error('AIRB supportStrengths failed', err);
		}

		try {
			if (window.AIRB && AIRB.Roles && AIRB.Roles.supportFocusAreas) {
				html += AIRB.Roles.supportFocusAreas(r, supportFocusRenderOpts());
			}
		} catch (err) {
			if (window.console && console.error) console.error('AIRB supportFocusAreas failed', err);
		}

		if (sr.next_steps && sr.next_steps.rollout) {
			html += leaderSectionLabel(
				supportResult.rollout_section_heading || 'Your next unlock — whole-school picture',
				supportResult.rollout_section_heading_short || 'Your next unlock'
			);
			html += supportRolloutCardHtml(sr.next_steps.rollout);
		}

		html += supportCtaCardHtml(sr.next_steps);
		html += supportHelpSupportHtml(sr.next_steps);
		return benchmarkResultsBodyHtml(html);
	}

	function supportDashboardRenderOpts() {
		return {
			esc: esc,
			supportResult: supportResult,
			sectionLabelHtml: leaderSectionLabel,
			cardHeadingHtml: benchmarkCardHeadingHtml,
			practiceHeading: supportResult.focus_practice_heading_short || i18n.focusPracticeHeadingShort || 'Areas to improve',
			tipsHeading: 'Practical next steps',
			guidanceToggle: 'View areas to improve',
			focusGuidanceMax: 75,
			guidanceToggleOperational: 'View operational impact',
			focusStackIntro: 'These cards expand the lowest domain scores into operational impact and recommended actions.',
			focusGuidanceAccordionHtml: focusGuidanceAccordionHtml,
			hideFocusSummary: true,
			resourcesHtml: function () {
				var sr = state.results && state.results.support_results;
				var nextSteps = sr && sr.next_steps;
				var links = (nextSteps && nextSteps.resource_links) ? nextSteps.resource_links : [];
				var html = '';
				if (links.length) {
					var intro = 'Suggested next steps after the audit, kept separate from the follow-up request form below.';
					html += '<section class="airb__leader-help-support airb__benchmark-help-support">' +
						'<div class="airb__resources-header">' +
						'<p class="airb__leader-section-label">' + esc(supportResult.help_support_heading || 'Further reading and tips to guide you') + '</p>' +
						'<p class="airb__resources-intro">' + esc(intro) + '</p>' +
						'</div>' +
						resultsResourceLinksHtml(links, { cardMode: true, demoCards: true }) +
						'</section>';
				}
				html += teacherBreakingNowResourceHtml(links);
				return html;
			},
			resultsBodyHtml: benchmarkResultsBodyHtml,
		};
	}

	function bindSupportDashboard() {
		if (window.AIRB && AIRB.SupportDashboard && AIRB.SupportDashboard.bind) {
			AIRB.SupportDashboard.bind(el.results);
		}
	}

	function studentResultsHtml(r) {
		var sr = r.student_results;
		if (!sr) return '';

		var model = state.dashboardModel;
		if (!model && window.AIRB && AIRB.DashboardModel && AIRB.DashboardModel.buildStudent) {
			model = AIRB.DashboardModel.buildStudent(r, cfg);
		}

		if (model && window.AIRB && AIRB.StudentDashboard && AIRB.StudentDashboard.render) {
			var renderOpts = studentDashboardRenderOpts();
			renderOpts.retakeCardHtml = function () {
				return studentRetakeCardHtml(sr, r.alignment_score);
			};
			return AIRB.StudentDashboard.render(r, model, renderOpts);
		}

		var html = '';
		try {
			if (window.AIRB && AIRB.Roles && AIRB.Roles.studentStrengths) {
				html += AIRB.Roles.studentStrengths(r, studentFocusRenderOpts());
			}
		} catch (err) {
			if (window.console && console.error) console.error('AIRB studentStrengths failed', err);
		}

		try {
			if (window.AIRB && AIRB.Roles && AIRB.Roles.studentFocusAreas) {
				html += AIRB.Roles.studentFocusAreas(r, studentFocusRenderOpts());
			}
		} catch (err) {
			if (window.console && console.error) console.error('AIRB studentFocusAreas failed', err);
		}

		html += studentHelpSupportHtml(sr.next_steps);
		html += studentRetakeCardHtml(sr, r.alignment_score);
		return benchmarkResultsBodyHtml(html);
	}

	function studentDashboardRenderOpts() {
		return {
			esc: esc,
			studentResult: studentResult,
			sectionLabelHtml: leaderSectionLabel,
			cardHeadingHtml: benchmarkCardHeadingHtml,
			practiceHeading: studentResult.focus_practice_heading_short || i18n.focusPracticeHeadingShort || 'Areas to improve',
			tipsHeading: 'Practical next steps',
			guidanceToggle: 'View areas to improve',
			focusGuidanceMax: 70,
			focusGuidanceAccordionHtml: focusGuidanceAccordionHtml,
			hideFocusSummary: true,
			resourcesHtml: function () {
				var sr = state.results && state.results.student_results;
				var nextSteps = sr && sr.next_steps;
				var links = (nextSteps && nextSteps.resource_links) ? nextSteps.resource_links : [];
				var html = '';
				if (links.length) {
					var intro = 'Suggested next steps after the audit, kept separate from the follow-up request form below.';
					html += '<section class="airb__leader-help-support airb__benchmark-help-support">' +
						'<div class="airb__resources-header">' +
						'<p class="airb__leader-section-label">' + esc(studentResult.resources_section_heading || 'Study resources') + '</p>' +
						'<p class="airb__resources-intro">' + esc(intro) + '</p>' +
						'</div>' +
						resultsResourceLinksHtml(links, { cardMode: true, demoCards: true }) +
						'</section>';
				}
				html += teacherBreakingNowResourceHtml(links);
				return html;
			},
			resultsBodyHtml: benchmarkResultsBodyHtml,
		};
	}

	function bindStudentDashboard() {
		if (window.AIRB && AIRB.StudentDashboard && AIRB.StudentDashboard.bind) {
			AIRB.StudentDashboard.bind(el.results);
		}
	}

	function resultsZoneClass(modifier) {
		var base = 'airb__results-zone airb__leader-zone';
		return modifier ? base + ' airb__results-zone--' + modifier + ' airb__leader-zone--' + modifier : base;
	}

	function resultsAccordionClass() {
		return 'airb__results-accordion airb__leader-accordion';
	}

	function resultsAccordionHtml(summary, innerHtml, isOpen) {
		if (!innerHtml) return '';
		return '<details class="' + resultsAccordionClass() + '"' + (isOpen ? ' open' : '') + '>' +
			'<summary>' + esc(summary) + '</summary>' +
			innerHtml + '</details>';
	}

	function focusGuidanceAccordionHtml(summary, innerHtml, isOpen, belowThreshold) {
		if (!innerHtml) return '';
		var label = summary || i18n.focusGuidanceToggle || 'Tips & steps to try';
		var belowClass = belowThreshold ? ' airb__focus-guidance-accordion--below-threshold' : '';
		return '<details class="' + resultsAccordionClass() + ' airb__focus-guidance-accordion' + belowClass + '"' + (isOpen === true ? ' open' : '') + '>' +
			'<summary>' + esc(label) + '</summary>' +
			'<div class="airb__focus-guidance-body">' + innerHtml + '</div>' +
			'</details>';
	}

	function peerBenchmarkBlockHtml(pb, opts) {
		opts = opts || {};
		if (!pb) return '';
		var html = '<div class="airb__results-stand-block airb__leader-stand-block">';
		if (opts.heading) {
			html += '<h4 class="airb__results-stand-label airb__leader-stand-label">' + esc(opts.heading) + '</h4>';
		}
		html += '<table class="airb__summary-table airb__summary-table--peer"><tbody>';
		html += '<tr><th scope="row">' + esc(i18n.yourScore || 'Your score') + '</th><td style="color:' + esc(readinessBandColor(pb.your_score)) + '">' + pb.your_score + '%</td></tr>';
		html += '<tr><th scope="row">' + esc(pb.phase_label || opts.averageLabel || i18n.parentAverage || 'Average') + '</th><td>' + pb.average_score + '%</td></tr>';
		html += '<tr><th scope="row">' + esc(opts.topQuartileLabel || i18n.topQuartile || 'Top quartile') + '</th><td>' + pb.top_quartile + '%</td></tr>';
		html += '</tbody></table>';
		if (pb.is_estimated) {
			html += '<p class="airb__muted airb__peer-note">' + esc(opts.estimatedNote || i18n.peerEstimated || 'Comparison uses reference benchmarks until enough responses have been collected.') + '</p>';
		} else if (typeof pb.percentile === 'number') {
			var pctTpl = opts.percentileTemplate || i18n.peerPercentile || 'Your score is ahead of {n}% of similar schools.';
			html += '<p class="airb__muted airb__peer-note">' + esc(pctTpl.replace('{n}', String(pb.percentile))) + '</p>';
		}
		return html + '</div>';
	}

	function resolveResourceLinkUrl(link) {
		if (!link) return '';
		if (link.url) return String(link.url);
		var slug = link.slug ? String(link.slug) : '';
		if (!slug) return '';
		var catalog = airbBenchmark.timelineReadCatalog || {};
		if (catalog[slug] && catalog[slug].url) {
			return String(catalog[slug].url);
		}
		var paths = airbBenchmark.timelineReadPaths || {};
		return paths[slug] || '';
	}

	function resourceLinkSlug(link) {
		if (!link) return '';
		if (link.slug) return String(link.slug);
		var url = resolveResourceLinkUrl(link) || (link.url ? String(link.url) : '');
		if (!url) return '';
		var match = url.match(/\/timeline\/([^/?#]+)/);
		return match ? decodeURIComponent(match[1]) : '';
	}

	function enrichResourceLink(link) {
		if (!link) return null;
		var slug = resourceLinkSlug(link);
		var catalog = airbBenchmark.timelineReadCatalog || {};
		var catalogItem = slug && catalog[slug] ? catalog[slug] : null;
		var enriched = Object.assign({}, catalogItem || {}, link);
		if (catalogItem) {
			enriched.url = enriched.url || catalogItem.url || '';
			enriched.label = enriched.label || catalogItem.label || '';
			enriched.image = enriched.image || catalogItem.image || '';
			enriched.slug = enriched.slug || catalogItem.slug || slug;
		}
		return enriched;
	}

	function resourceLinkArrowSvg() {
		return '<svg class="airb__resource-link-arrow" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>';
	}

	function resourceCardBodyHtml(link, demoCards) {
		var html = '<span class="airb__resource-link-body">';
		if (demoCards && link.kicker) {
			html += '<span class="airb__resource-link-kicker">' + escText(link.kicker) + '</span>';
		}
		html += '<span class="airb__resource-link-label">' + escText(link.label) + '</span>';
		if (demoCards && link.description) {
			html += '<span class="airb__resource-link-description">' + escText(link.description) + '</span>';
		}
		html += '</span>';
		if (demoCards) {
			html += resourceLinkArrowSvg();
		}
		return html;
	}

	function resourceFeaturedCardHtml(link) {
		var url = resolveResourceLinkUrl(link) || (link.url ? String(link.url) : '');
		if (!url) return '';
		var html = '<a class="airb__results-resource-card airb__results-resource-card--featured" href="' + esc(url) + '"';
		if (link.external) {
			html += ' target="_blank" rel="noopener noreferrer"';
		}
		html += '>';
		if (link.image) {
			html += '<span class="airb__resource-link-media"><img class="airb__resource-link-thumb" src="' + esc(link.image) + '" alt="" loading="lazy" decoding="async" /></span>';
		} else {
			html += '<span class="airb__resource-link-media airb__resource-link-media--icon" aria-hidden="true"></span>';
		}
		html += resourceCardBodyHtml(link, true);
		return html + '</a>';
	}

	function teacherBreakingNowResourceHtml(links) {
		var list = (links || []).slice();
		var breaking = list.length ? list[0] : null;

		// Fallback when no links are provided for this role.
		if (!breaking) {
			var breakingSlug = 'how-does-a-large-language-model-work';
			var catalog = airbBenchmark.timelineReadCatalog || {};
			if (catalog[breakingSlug]) breaking = Object.assign({}, catalog[breakingSlug]);
		}
		if (!breaking) return '';
		breaking = enrichResourceLink(Object.assign({}, breaking, {
			kicker: 'Breaking now',
			description: breaking.description || 'The top timeline explainer to help staff and students understand what sits behind AI answers.',
		}));
		if (!resolveResourceLinkUrl(breaking)) return '';
		return '<section class="airb__breaking-resource" aria-labelledby="airb-breaking-now-title">' +
			'<p class="airb__leader-section-label" id="airb-breaking-now-title">Breaking now</p>' +
			resultsResourceLinksHtml([breaking], { cardMode: true, demoCards: true }) +
			'</section>';
	}

	function resultsResourceLinksHtml(links, options) {
		options = options || {};
		if (!links || !links.length) return '';
		var cardMode = options.cardMode !== false;
		var leaderGrid = !!options.leaderGrid;
		var demoCards = !!options.demoCards;
		var html = '<ul class="airb__results-resource-links airb__leader-resource-links' +
			(cardMode ? ' airb__results-resource-links--cards' : '') +
			(leaderGrid ? ' airb__results-resource-links--leader-grid' : '') + '">';
		links.forEach(function (link) {
			link = enrichResourceLink(link) || link;
			var url = resolveResourceLinkUrl(link);
			var prefill = link.prefill ? String(link.prefill) : '';
			if (!url && !prefill) return;

			html += '<li class="airb__results-resource-item">';
			if (link.external && url) {
				html += '<a class="airb__results-resource-card" href="' + esc(url) + '" target="_blank" rel="noopener noreferrer">';
			} else if (prefill) {
				html += '<button type="button" class="airb__results-resource-card airb__results-resource-link airb__leader-resource-link" data-airb-open-interest="' + esc(prefill) + '">';
			} else if (url) {
				html += '<a class="airb__results-resource-card" href="' + esc(url) + '"' + (link.external ? ' target="_blank" rel="noopener noreferrer"' : '') + '>';
			}
			if (link.image) {
				html += '<span class="airb__resource-link-media"><img class="airb__resource-link-thumb" src="' + esc(link.image) + '" alt="" loading="lazy" decoding="async" /></span>';
			} else {
				html += '<span class="airb__resource-link-media airb__resource-link-media--icon" aria-hidden="true"></span>';
			}
			html += resourceCardBodyHtml(link, demoCards);
			html += link.prefill ? '</button>' : '</a>';
			html += '</li>';
		});
		return html + '</ul>';
	}

	function leaderGovernanceCtaHtml(nextSteps) {
		if (!nextSteps || !nextSteps.hero) return '';
		var hero = nextSteps.hero;
		var heading = nextSteps.hero_heading || leaderResult.hero_next_step_heading || i18n.recommendedNextStep || 'Your next step';
		var headingShort = leaderResult.hero_next_step_heading_short || 'Your next step';
		var html = '<article class="airb__leader-cta-card">';
		html += '<h4 class="airb__leader-cta-title">' + esc(hero.title) + '</h4>';
		if (hero.body) {
			html += '<p class="airb__leader-cta-body">' + esc(hero.body) + '</p>';
		}
		if (hero.deliverables && hero.deliverables.length) {
			html += '<div class="airb__leader-cta-deliverables" role="list">';
			hero.deliverables.forEach(function (item) {
				html += '<span class="airb__leader-cta-deliverable" role="listitem">' + esc(item) + '</span>';
			});
			html += '</div>';
		}
		if (hero.cta_type === 'link' && hero.cta_url) {
			html += '<a class="airb__btn airb__btn--premium airb__leader-cta-btn" href="' + esc(hero.cta_url) + '">' + esc(hero.cta_text || 'Continue') + '</a>';
		} else {
			html += '<button type="button" class="airb__btn airb__btn--premium airb__leader-cta-btn" data-airb-open-interest="' + esc(hero.key || 'governance_review') + '">' + esc(hero.cta_text || 'Request support') + '</button>';
		}
		return html + '</article>';
	}

	function resultsActionZoneHtml(nextSteps, zoneOpts) {
		zoneOpts = zoneOpts || {};
		if (!nextSteps || !nextSteps.hero) return '';
		var hero = nextSteps.hero;
		var leaderCta = !!zoneOpts.leaderCta;
		var html = '<section class="' + resultsZoneClass('action') + (leaderCta ? ' airb__results-zone--leader-cta' : '') + '">';
		html += '<p class="airb__next-step-hero-label">' + esc(nextSteps.hero_heading || i18n.recommendedNextStep || 'Your next step') + '</p>';
		html += '<article class="airb__next-step-hero airb__next-step-hero--primary' + (leaderCta ? ' airb__next-step-hero--leader-card' : '') + '">';
		html += '<h4 class="airb__next-step-hero-title">' + esc(hero.title) + '</h4>';
		if (hero.body) html += '<p>' + esc(hero.body) + '</p>';
		if (hero.understand_items && hero.understand_items.length) {
			html += '<ul class="airb__leader-actions airb__cta-bullets">';
			hero.understand_items.forEach(function (item) {
				html += '<li>' + esc(item) + '</li>';
			});
			html += '</ul>';
		}
		if (hero.deliverables && hero.deliverables.length) {
			if (leaderCta) {
				html += '<div class="airb__cta-tags">';
				hero.deliverables.forEach(function (item) {
					html += '<span class="airb__cta-tag">' + esc(item) + '</span>';
				});
				html += '</div>';
			} else {
				html += '<ul class="airb__leader-actions airb__leader-actions--deliverables">';
				hero.deliverables.forEach(function (item) {
					html += '<li>' + esc(item) + '</li>';
				});
				html += '</ul>';
			}
		}
		if (hero.cta_type === 'link' && hero.cta_url) {
			html += '<a class="airb__btn airb__btn--primary airb__btn--hero" href="' + esc(hero.cta_url) + '">' + esc(hero.cta_text || 'Continue') + '</a>';
		} else {
			html += '<button type="button" class="airb__btn airb__btn--primary airb__btn--hero" data-airb-open-interest="' + esc(hero.key || 'governance_review') + '">' + esc(hero.cta_text || 'Request support') + '</button>';
		}
		html += '</article>';
		if (!leaderCta) {
			if (nextSteps.resource_links && nextSteps.resource_links.length) {
				html += '<h5 class="airb__results-read-more-heading">' + esc(nextSteps.timeline_heading || i18n.benchmarkOutcomes || 'Further reading and support articles') + '</h5>';
			}
			html += resultsResourceLinksHtml(nextSteps.resource_links);
		}
		if (nextSteps.hub_resources && nextSteps.hub_resources.length) {
			html += '<h5 class="airb__results-read-more-heading airb__results-read-more-heading--hub">' + esc(nextSteps.hub_heading || i18n.usefulResources || 'Useful resources') + '</h5>';
			html += resultsResourceLinksHtml(nextSteps.hub_resources);
		}
		return html + '</section>';
	}

	function leaderMaturityCardHtml(maturity) {
		if (!maturity || maturity.score == null) return '';
		var bandSlug = readinessLevel(maturity.score).slug;
		var html = '<section class="' + resultsZoneClass('maturity') + ' airb__leader-maturity-card">';
		html += '<h3 class="airb__card-title">' + esc(maturity.title || i18n.governanceMaturity || 'Governance maturity') + '</h3>';
		html += '<p class="airb__leader-maturity-body">';
		html += '<strong>' + esc(String(maturity.score)) + '%</strong>';
		if (maturity.band_label) {
			html += ' · ' + readinessBandPillHtml(bandSlug, maturity.band_label);
		}
		if (maturity.description) {
			html += '<span class="airb__leader-maturity-note"> — ' + esc(maturity.description) + '</span>';
		}
		html += '</p></section>';
		return html;
	}

	function resultsSummaryZoneHtml(options) {
		options = options || {};
		if (!options.title && !options.intro && !(options.strengths && options.strengths.length) && !(options.attention && options.attention.length) && !options.priority && !options.extraHtml) {
			return '';
		}
		var leaderStyle = !!options.leaderStyle;
		var html = '<section class="' + resultsZoneClass('summary') + ' airb__exec-summary' + (leaderStyle ? ' airb__exec-summary--leader' : '') + '">';
		if (options.title) html += '<h3 class="airb__card-title">' + esc(options.title) + '</h3>';
		if (options.intro) {
			html += '<div class="airb__exec-intro-callout"><p class="airb__exec-intro">' + esc(options.intro) + '</p></div>';
		}
		if (options.strengths && options.strengths.length) {
			html += '<h4 class="airb__exec-sub">' + esc(options.strengthsHeading || 'Strengths include') + '</h4>';
			html += '<ul class="airb__strength-list airb__strength-list--exec">';
			options.strengths.forEach(function (item) {
				html += '<li><span class="airb__strength-mark" aria-hidden="true">✓</span> ' + esc(item) + '</li>';
			});
			html += '</ul>';
		}
		if (options.attention && options.attention.length) {
			html += '<h4 class="airb__exec-sub">' + esc(options.attentionHeading || 'Areas requiring attention') + '</h4>';
			html += '<ul class="airb__attention-list' + (leaderStyle ? ' airb__attention-list--leader' : '') + '">';
			options.attention.forEach(function (item) {
				html += '<li>';
				if (leaderStyle) {
					html += '<span class="airb__attention-dot" aria-hidden="true"></span>';
				} else {
					html += '<span class="airb__attention-mark" aria-hidden="true">⚠</span> ';
				}
				html += esc(item) + '</li>';
			});
			html += '</ul>';
		}
		if (options.priority) {
			html += '<div class="airb__exec-priority-callout">';
			html += '<strong class="airb__exec-priority-label">' + esc(i18n.priorityAction || 'Priority action') + '</strong>';
			html += '<p class="airb__exec-priority-text">' + esc(options.priority) + '</p>';
			html += '</div>';
		}
		if (!leaderStyle && options.maturity && options.maturity.score != null) {
			var mat = options.maturity;
			html += '<p class="airb__exec-maturity"><strong>' + esc(mat.title || i18n.governanceMaturity || 'Governance Maturity') + ':</strong> ';
			html += esc(String(mat.score)) + '%';
			if (mat.band_label) html += ' · ' + esc(mat.band_label);
			if (mat.description) html += '<span class="airb__exec-maturity-note"> — ' + esc(mat.description) + '</span>';
			html += '</p>';
		}
		if (options.extraHtml) html += options.extraHtml;
		return html + '</section>';
	}

	function resultsStandZoneHtml(title, innerHtml) {
		if (!innerHtml) return '';
		return '<section class="' + resultsZoneClass('stand') + '">' +
			'<h3 class="airb__results-zone-title airb__leader-zone-title">' + esc(title) + '</h3>' +
			innerHtml + '</section>';
	}

	function leaderRolloutAccordionBody(ro) {
		var html = '<div class="airb__leader-rollout">';
		if (ro.unlock_benefits && ro.unlock_benefits.length) {
			html += '<div class="airb__rollout-grid">';
			ro.unlock_benefits.forEach(function (item) {
				html += '<div class="airb__rollout-item"><span class="airb__rollout-tick" aria-hidden="true">✓</span> ' + esc(item) + '</div>';
			});
			html += '</div>';
		}
		if (ro.counts) {
			html += '<div class="airb__resp-grid">';
			html += '<div class="airb__resp-item"><div class="airb__resp-role">' + esc(i18n.roleLeader || 'Leaders') + '</div><div class="airb__resp-count">' + (ro.counts.leader || 0) + '</div></div>';
			html += '<div class="airb__resp-item"><div class="airb__resp-role">' + esc(i18n.roleTeacher || 'Teachers') + '</div><div class="airb__resp-count">' + (ro.counts.teacher || 0) + '</div></div>';
			html += '<div class="airb__resp-item"><div class="airb__resp-role">' + esc(i18n.roleSupportStaff || 'Support staff') + '</div><div class="airb__resp-count">' + (ro.counts.support_staff || 0) + '</div></div>';
			html += '<div class="airb__resp-item"><div class="airb__resp-role">' + esc(i18n.roleStudent || 'Students') + '</div><div class="airb__resp-count">' + (ro.counts.student || 0) + '</div></div>';
			html += '</div>';
		}
		if (!ro.unlocked) {
			var unlockCopy = (ro.unlock_copy || leaderResult.rollout_unlock_copy || 'Unlocks after {threshold} responses from your school community.')
				.replace('{threshold}', String(ro.threshold || 20))
				.replace('{remaining}', String(ro.remaining || ro.threshold || 20));
			html += '<div class="airb__unlock-notice"><p>' + esc(unlockCopy) + '</p>';
			html += '<p><button type="button" class="airb-hub-btn airb-hub-btn--secondary" data-airb-open-interest="whole_school_benchmark">' + esc(i18n.rolloutBenchmark || 'Roll out to all groups') + '</button></p></div>';
		} else {
			html += '<p><button type="button" class="airb-hub-btn airb-hub-btn--secondary" data-airb-open-interest="whole_school_benchmark">' + esc(i18n.rolloutBenchmark || 'Roll out to all groups') + '</button></p>';
		}
		return html + '</div>';
	}

	function teacherRolloutAccordionBody(rollout) {
		if (!rollout) return '';
		var progress = rollout.progress || {};
		var copy = rollout.copy || {};
		var html = '<div class="airb__leader-rollout">';
		if (progress.has_school) {
			var withCount = (copy.with_count || 'Your school currently has {n} teacher responses. A whole-school benchmark becomes available after {threshold} responses.')
				.replace('{n}', String(progress.teacher_responses || 0))
				.replace('{threshold}', String(progress.threshold || 20));
			html += '<p class="airb__muted">' + esc(withCount) + '</p>';
			if (progress.whole_school_available && copy.unlocked) {
				html += '<p class="airb__muted">' + esc(copy.unlocked) + '</p>';
			}
		} else if (copy.without_school) {
			html += '<p class="airb__muted">' + esc(copy.without_school) + '</p>';
		}
		html += '<p><button type="button" class="airb-hub-btn airb-hub-btn--secondary" data-airb-open-interest="whole_school_benchmark">' + esc(i18n.rolloutBenchmark || 'Roll out to all groups') + '</button></p>';
		return html + '</div>';
	}

	function parentAdvocateSummaryHtml(adv) {
		if (!adv) return '';
		var html = '<div class="airb__parent-advocate airb__parent-advocate--summary">';
		html += '<h4>' + esc(adv.title || 'You\'re ahead of most parents') + '</h4>';
		if (adv.intro) html += '<p>' + esc(adv.intro) + '</p>';
		if (adv.strengths && adv.strengths.length) {
			html += '<ul class="airb__strength-list airb__strength-list--parent">';
			adv.strengths.forEach(function (item) {
				html += '<li><span class="airb__strength-mark" aria-hidden="true">✓</span> ' + esc(item) + '</li>';
			});
			html += '</ul>';
		}
		return html + '</div>';
	}

	function parentConfidenceSummaryHtml(conf) {
		if (!conf) return '';
		var html = '<div class="airb__parent-confidence airb__parent-confidence--summary">';
		html += '<h4>' + esc(conf.title || 'Building your confidence') + '</h4>';
		if (typeof conf.score === 'number') {
			html += '<p class="airb__parent-confidence-score">' + esc(i18n.parentConfidence || 'Parent Confidence') + ': <strong>' + conf.score + '%</strong></p>';
		}
		if (conf.improve_items && conf.improve_items.length) {
			html += '<ul class="airb__parent-confidence-tips">';
			conf.improve_items.slice(0, 3).forEach(function (item) {
				html += '<li>' + esc(item) + '</li>';
			});
			html += '</ul>';
		}
		return html + '</div>';
	}

	function parentConfidenceDetailHtml(conf) {
		if (!conf) return '';
		var html = '<section class="airb__parent-confidence">';
		html += '<h4>' + esc(conf.title || 'Building your confidence') + '</h4>';
		if (typeof conf.score === 'number') {
			html += '<p class="airb__parent-confidence-score">' + esc(i18n.parentConfidence || 'Parent Confidence') + ': <strong>' + conf.score + '%</strong></p>';
		}
		if (conf.impact_items && conf.impact_items.length) {
			html += '<p class="airb__impact-label"><strong>' + esc(conf.impact_heading || i18n.likelyImpact || 'Likely impact') + '</strong></p>';
			html += '<ul class="airb__impact-list">';
			conf.impact_items.forEach(function (item) {
				html += '<li>' + esc(item) + '</li>';
			});
			html += '</ul>';
		}
		if (conf.improve_items && conf.improve_items.length) {
			html += '<p class="airb__impact-label"><strong>' + esc(conf.improve_heading || 'To improve confidence:') + '</strong></p>';
			html += '<ul class="airb__parent-confidence-tips">';
			conf.improve_items.forEach(function (item) {
				html += '<li>' + esc(item) + '</li>';
			});
			html += '</ul>';
		}
		return html + '</section>';
	}

	function parentAdvocateDetailHtml(adv) {
		if (!adv) return '';
		var html = '<section class="airb__parent-advocate">';
		html += '<h4>' + esc(adv.title || 'You\'re ahead of most parents') + '</h4>';
		if (adv.intro) html += '<p>' + esc(adv.intro) + '</p>';
		if (adv.strengths && adv.strengths.length) {
			html += '<ul class="airb__strength-list airb__strength-list--parent">';
			adv.strengths.forEach(function (item) {
				html += '<li><span class="airb__strength-mark" aria-hidden="true">✓</span> ' + esc(item) + '</li>';
			});
			html += '</ul>';
		}
		if (adv.help_title && adv.help_items && adv.help_items.length) {
			html += '<p class="airb__advocate-help-title"><strong>' + esc(adv.help_title) + '</strong></p>';
			html += '<ul class="airb__leader-actions">';
			adv.help_items.forEach(function (item) {
				html += '<li>' + esc(item) + '</li>';
			});
			html += '</ul>';
		}
		return html + '</section>';
	}

	function leaderHeatmapCardHtml(cells) {
		if (!cells || !cells.length) return '';
		var title = leaderResult.heatmap_card_title || i18n.heatmapCardTitle || 'Risk heat map — all domains';
		var titleShort = leaderResult.heatmap_card_title_short || i18n.heatmapCardTitleShort || 'Risk heat map';
		var help = leaderResult.heatmap_card_help || i18n.heatmapCardHelp || 'Showing risk exposure %. Higher = more risk in that area.';
		var html = '<div class="airb__leader-heatmap-card">';
		html += '<h4 class="airb__leader-heatmap-title">' + leaderResponsiveLabel(title, titleShort) + '</h4>';
		html += '<p class="airb__leader-heatmap-help airb__muted">' + esc(help) + '</p>';
		html += '<div class="airb__leader-heatmap-rows">';
		cells.forEach(function (cell) {
			var risk = Math.round(cell.risk);
			var badge = heatmapExposureBadge(risk);
			var barColor = heatmapBarColor(risk);
			html += '<div class="airb__leader-hm-row">';
			html += '<span class="airb__leader-hm-label">' + esc(cell.label) + '</span>';
			html += '<div class="airb__leader-hm-bar-wrap"><div class="airb__leader-hm-bar" style="width:' + risk + '%;background:' + esc(barColor) + '"></div></div>';
			html += '<span class="airb__focus-badge airb__focus-badge--' + badge.slug + ' airb__leader-hm-badge">';
			html += '<span class="airb__hm-badge-full">' + esc(badge.text) + '</span>';
			html += '<span class="airb__hm-badge-short">' + esc(badge.short) + '</span>';
			html += '</span>';
			html += '</div>';
		});
		return html + '</div></div>';
	}

	function leaderRolloutCardHtml(ro) {
		if (!ro) return '';
		var intro = ro.intro || leaderResult.rollout_intro || '';
		var introShort = ro.intro_short || leaderResult.rollout_intro_short || 'Get your whole-school picture when staff, students and parents complete their audits.';
		var threshold = ro.threshold || 20;
		var counts = ro.counts || {};
		var total = typeof ro.total === 'number' ? ro.total : 0;
		var lockedItems = ro.locked_items && ro.locked_items.length
			? ro.locked_items
			: (leaderResult.rollout_locked_items || []);
		var html = '<div class="airb__leader-rollout-card">';
		if (intro) {
			html += '<p class="airb__leader-rollout-intro">' + leaderResponsiveLabel(intro, introShort) + '</p>';
		}
		if (lockedItems.length) {
			html += '<div class="airb__leader-rollout-locks">';
			lockedItems.forEach(function (item) {
				var key = item.count_key || 'total';
				var n = key === 'total' ? total : (parseInt(counts[key], 10) || 0);
				html += '<div class="airb__leader-rollout-lock">';
				html += '<span class="airb__leader-rollout-lock-icon" aria-hidden="true">🔒</span>';
				html += '<div class="airb__leader-rollout-lock-copy">';
				html += '<div class="airb__leader-rollout-lock-label">' + esc(item.label || '') + '</div>';
				html += '<div class="airb__leader-rollout-lock-count">';
				html += '<span class="airb__rollout-count-long">' + n + ' of ' + threshold + ' ' + esc(i18n.responsesLabel || 'responses') + '</span>';
				html += '<span class="airb__rollout-count-short">' + n + ' / ' + threshold + ' ' + esc(i18n.responsesLabel || 'responses') + '</span>';
				html += '</div>';
				html += '</div></div>';
			});
			html += '</div>';
		}
		if (ro.rollout_note) {
			html += '<p class="airb__leader-rollout-note airb__leader-rollout-progress">' + esc(ro.rollout_note) + '</p>';
		} else if (!ro.unlocked && ro.unlock_copy) {
			html += '<p class="airb__leader-rollout-note airb__muted">' + esc(
				ro.unlock_copy.replace('{threshold}', String(threshold)).replace('{remaining}', String(ro.remaining || threshold))
			) + '</p>';
		}
		html += '<button type="button" class="airb__btn airb__btn--secondary airb__btn--rollout-full" data-airb-open-interest="whole_school_benchmark">' + esc(ro.rollout_cta || leaderResult.rollout_rollout_cta || i18n.rolloutBenchmark || 'Roll out to your school community') + '</button>';
		return html + '</div>';
	}

	function leaderHelpSupportHtml(nextSteps) {
		if (!nextSteps || !nextSteps.resource_links || !nextSteps.resource_links.length) return '';
		return benchmarkHelpSupportHtml(
			nextSteps,
			nextSteps.help_support_heading || leaderResult.help_support_heading,
			nextSteps.help_support_heading_short || leaderResult.help_support_heading_short
		);
	}

	function leaderFocusRenderOpts() {
		return {
			esc: esc,
			leaderResult: leaderResult,
			sectionLabelHtml: leaderSectionLabel,
			practiceHeading: leaderResult.focus_practice_heading_short || i18n.focusPracticeHeadingShort || 'Areas to improve',
			tipsHeading: 'Practical next steps',
			guidanceToggle: 'View areas to improve',
			focusGuidanceMax: 70,
			focusPracticeHeading: i18n.focusPracticeHeading || 'What this means in practice',
			focusPracticeHeadingShort: i18n.focusPracticeHeadingShort || 'In practice this means',
			focusActionsHeading: i18n.focusActionsHeading || 'Actions',
			leaderFocusBadge: leaderFocusBadge,
			leaderFocusSeverity: leaderFocusSeverity,
			leaderBiasEqualityFocusNote: leaderBiasEqualityFocusNote,
			leaderResponsiveLabel: leaderResponsiveLabel,
			focusGuidanceAccordionHtml: focusGuidanceAccordionHtml,
		};
	}

	function leaderResultsHtml(r) {
		var lr = r.leader_results;
		if (!lr) return '';

		var model = state.dashboardModel;
		if (!model && window.AIRB && AIRB.DashboardModel && AIRB.DashboardModel.buildLeader) {
			model = AIRB.DashboardModel.buildLeader(r, cfg);
		}

		if (model && window.AIRB && AIRB.LeaderDashboard && AIRB.LeaderDashboard.render) {
			var renderOpts = leaderDashboardRenderOpts();
			// Keep the leader dashboard "progress" panel focused on certificate progress.
			// Heatmap/rollout/governance CTA live in the overview/resources panels.
			return AIRB.LeaderDashboard.render(r, model, renderOpts);
		}

		var html = '';
		try {
			if (window.AIRB && AIRB.Roles && AIRB.Roles.leaderUrgentAction) {
				html += AIRB.Roles.leaderUrgentAction(r, { urgentActionHtml: leaderUrgentActionHtml });
			} else {
				var es = lr.executive_summary;
				html += leaderUrgentActionHtml(es ? es.priority_action_detail : null);
			}
		} catch (err) {
			if (window.console && console.error) {
				console.error('AIRB leaderUrgentAction failed', err);
			}
			var esFallback = lr.executive_summary;
			html += leaderUrgentActionHtml(esFallback ? esFallback.priority_action_detail : null);
		}

		if (lr.peer_benchmark) {
			html += leaderPeerBenchmarkBarHtml(lr.peer_benchmark);
		}

		try {
			if (window.AIRB && AIRB.Roles && AIRB.Roles.leaderFocusAreas) {
				html += AIRB.Roles.leaderFocusAreas(r, leaderFocusRenderOpts());
			}
		} catch (err) {
			if (window.console && console.error) {
				console.error('AIRB leaderFocusAreas failed', err);
			}
		}

		if (lr.risk_heatmap && lr.risk_heatmap.length) {
			html += leaderSectionLabel(
				leaderResult.heatmap_section_heading || 'Full risk picture',
				leaderResult.heatmap_section_heading_short || 'Full risk picture'
			);
			html += leaderHeatmapCardHtml(lr.risk_heatmap);
		}

		if (lr.next_steps && lr.next_steps.rollout) {
			html += leaderSectionLabel(
				leaderResult.rollout_section_heading || 'Your next unlock — whole-school picture',
				leaderResult.rollout_section_heading_short || 'Your next unlock'
			);
			html += leaderRolloutCardHtml(lr.next_steps.rollout);
		}

		html += leaderGovernanceCtaHtml(lr.next_steps);
		html += leaderHelpSupportHtml(lr.next_steps);
		return benchmarkResultsBodyHtml(html);
	}

	function leaderDashboardRenderOpts() {
		return {
			esc: esc,
			leaderResult: leaderResult,
			sectionLabelHtml: leaderSectionLabel,
			cardHeadingHtml: benchmarkCardHeadingHtml,
			practiceHeading: leaderResult.focus_practice_heading_short || i18n.focusPracticeHeadingShort || 'Areas to improve',
			tipsHeading: 'Practical next steps',
			guidanceToggle: 'View areas to improve',
			focusGuidanceMax: 70,
			guidanceToggleGovernance: 'View governance impact',
			focusStackIntro: 'These cards expand the lowest domain scores into governance impact and recommended actions.',
			focusGuidanceAccordionHtml: focusGuidanceAccordionHtml,
			hideFocusSummary: true,
			resourcesHtml: function () {
				var lr = state.results && state.results.leader_results;
				var nextSteps = lr && lr.next_steps;
				var links = (nextSteps && nextSteps.resource_links) ? nextSteps.resource_links : [];
				var html = '';
				if (links.length) {
					var intro = 'Suggested next steps after the audit, kept separate from the follow-up request form below.';
					html += '<section class="airb__leader-help-support airb__benchmark-help-support">' +
						'<div class="airb__resources-header">' +
						'<p class="airb__leader-section-label">' + esc(leaderResult.help_support_heading || 'CPD for teachers and students') + '</p>' +
						'<p class="airb__resources-intro">' + esc(intro) + '</p>' +
						'</div>' +
						resultsResourceLinksHtml(links, { cardMode: true, demoCards: true }) +
						'</section>';
				}
				html += teacherBreakingNowResourceHtml(links);
				return html;
			},
			resultsBodyHtml: benchmarkResultsBodyHtml,
		};
	}

	function bindLeaderDashboard() {
		if (window.AIRB && AIRB.LeaderDashboard && AIRB.LeaderDashboard.bind) {
			AIRB.LeaderDashboard.bind(el.results);
		}
	}

	function resultsProfileHtml(r) {
		var roleLbl = (cfg.roles || {})[state.role] || state.role;
		var readiness = r.alignment_score;
		var risk = Math.round(r.overall_risk_percentage);
		var depVal = roleShowsDependency(state.role) ? r.dependency_index : null;
		var parentMode = isParentRole();
		var publicMode = isPublicRole() && !!r.public_results;
		var parentResults = r.parent_results;
		var publicResults = r.public_results;
		var studentMode = isStudentRole() && !!r.student_results;
		var studentResults = r.student_results;
		var leaderMode = isLeaderRole() && !!r.leader_results;
		var teacherBenchmarkMode = isTeacherRole() && !!r.teacher_results;
		var supportBenchmarkMode = isSupportStaffRole() && !!r.support_results;
		var supportResults = r.support_results;
		var dashboardMode = !!state.dashboardModel && (
			leaderMode ||
			teacherBenchmarkMode ||
			studentMode ||
			parentMode ||
			publicMode ||
			supportBenchmarkMode
		);

		if (dashboardMode) {
			if (leaderMode && window.AIRB && AIRB.LeaderDashboard && AIRB.LeaderDashboard.coreSummaryHtml) {
				return AIRB.LeaderDashboard.coreSummaryHtml(state.dashboardModel);
			}
			if (teacherBenchmarkMode && window.AIRB && AIRB.TeacherDashboard && AIRB.TeacherDashboard.coreSummaryHtml) {
				return AIRB.TeacherDashboard.coreSummaryHtml(state.dashboardModel);
			}
			if (studentMode && window.AIRB && AIRB.StudentDashboard && AIRB.StudentDashboard.coreSummaryHtml) {
				return AIRB.StudentDashboard.coreSummaryHtml(state.dashboardModel);
			}
			if (parentMode && window.AIRB && AIRB.ParentDashboard && AIRB.ParentDashboard.coreSummaryHtml) {
				return AIRB.ParentDashboard.coreSummaryHtml(state.dashboardModel);
			}
			if (publicMode && window.AIRB && AIRB.PublicDashboard && AIRB.PublicDashboard.coreSummaryHtml) {
				return AIRB.PublicDashboard.coreSummaryHtml(state.dashboardModel);
			}
			if (supportBenchmarkMode && window.AIRB && AIRB.SupportDashboard && AIRB.SupportDashboard.coreSummaryHtml) {
				return AIRB.SupportDashboard.coreSummaryHtml(state.dashboardModel);
			}
		}

		var eyebrow = studentMode
			? (i18n.studentResultsEyebrow || 'Student · AI skills benchmark')
			: publicMode
				? (i18n.publicResultsEyebrow || 'Public · AI risk & readiness benchmark')
			: parentMode
				? (i18n.parentResultsEyebrow || 'Parent / carer · AI awareness benchmark')
				: supportBenchmarkMode
					? (i18n.supportResultsEyebrow || 'Support staff · AI risk & readiness benchmark')
					: (i18n.resultsRoleResult || '{role} result').replace('{role}', roleLbl);
		var bandSummary = parentMode && !parentResults ? parentBandSummary(readiness) : '';
		var indepVal = null;
		if (studentMode && studentResults && studentResults.learning_metrics) {
			studentResults.learning_metrics.forEach(function (m) {
				if (m.slug === 'independent_thinking') indepVal = m.value;
			});
		}

		var html = '<section class="airb__res-profile' +
			(parentMode ? ' airb__res-profile--parent' : '') +
			(publicMode ? ' airb__res-profile--public' : '') +
			(studentMode ? ' airb__res-profile--student' : '') +
			(leaderMode ? ' airb__res-profile--leader' : '') +
			(teacherBenchmarkMode ? ' airb__res-profile--teacher' : '') +
			(supportBenchmarkMode ? ' airb__res-profile--support' : '') +
			'">';
		var profileTitle = teacherBenchmarkMode ? (i18n.teacherResultsTitle || i18n.leaderResultsTitle || 'Your results') :
			supportBenchmarkMode ? (supportResult.profile_title || i18n.supportResultsTitle || 'Your results') :
			studentMode ? (i18n.studentResultsTitle || 'Your learning profile') :
			publicMode ? (i18n.publicResultsTitle || publicResult.profile_title || 'Your AI safety profile') :
			parentMode ? (i18n.parentResultsTitle || parentResult.profile_title || 'Your home AI picture') :
			leaderMode ? (i18n.leaderResultsTitle || 'Your results') :
			(i18n.resultsProfileTitle || i18n.resultsTitle || 'Your AI Risk & Readiness profile');
		html += '<span class="airb__res-eyebrow"><span class="airb__res-eyebrow-dot" aria-hidden="true"></span>' + esc(eyebrow) + '</span>';
		html += '<div class="airb__res-shead">';
		html += '<h2 class="airb__res-title">' + esc(profileTitle) + '</h2>';
		html += '</div>';
		if (leaderMode) {
			if (window.AIRB && AIRB.LeaderDashboard && AIRB.LeaderDashboard.coreSummaryHtml && state.dashboardModel) {
				html += AIRB.LeaderDashboard.coreSummaryHtml(state.dashboardModel);
			} else {
				var uiHero = r.leader_results && r.leader_results.ui ? r.leader_results.ui.hero : null;
				html += leaderReadinessHeroHtml(readiness, uiHero);
				html += leaderSupportingMetricsHtml(r, risk);
			}
		} else if (teacherBenchmarkMode) {
			if (window.AIRB && AIRB.TeacherDashboard && AIRB.TeacherDashboard.coreSummaryHtml && state.dashboardModel) {
				html += AIRB.TeacherDashboard.coreSummaryHtml(state.dashboardModel);
			} else {
				var trFallback = r.teacher_results;
				var tUiHero = trFallback && trFallback.ui ? trFallback.ui.hero : null;
				html += leaderReadinessHeroHtml(readiness, tUiHero);
				html += teacherPeerBenchmarkHtml(trFallback);
			}
		} else if (studentMode && studentResults) {
			if (window.AIRB && AIRB.StudentDashboard && AIRB.StudentDashboard.coreSummaryHtml && state.dashboardModel) {
				html += AIRB.StudentDashboard.coreSummaryHtml(state.dashboardModel);
			} else {
				var sUiHero = studentResults.ui && studentResults.ui.hero ? studentResults.ui.hero : null;
				html += studentReadinessHeroHtml(readiness, sUiHero);
				html += studentPeerBenchmarkHtml(studentResults);
				html += benchmarkOversightGaugeSectionHtml(r);
				html += studentSkillsSectionHtml(studentResults.learning_metrics || []);
			}
		} else if (parentMode && parentResults) {
			if (window.AIRB && AIRB.ParentDashboard && AIRB.ParentDashboard.coreSummaryHtml && state.dashboardModel) {
				html += AIRB.ParentDashboard.coreSummaryHtml(state.dashboardModel);
			} else {
				var pUiHero = parentResults.ui && parentResults.ui.hero ? parentResults.ui.hero : null;
				html += parentReadinessHeroHtml(readiness, pUiHero);
				html += parentHomeMetricsSectionHtml(parentResults.home_metrics);
			}
		} else if (publicMode && publicResults) {
			if (window.AIRB && AIRB.PublicDashboard && AIRB.PublicDashboard.coreSummaryHtml && state.dashboardModel) {
				html += AIRB.PublicDashboard.coreSummaryHtml(state.dashboardModel);
			} else {
				var pubUiHero = publicResults.ui && publicResults.ui.hero ? publicResults.ui.hero : null;
				html += leaderReadinessHeroHtml(readiness, pubUiHero);
				html += publicSummaryMetricsGridHtml(publicResults.summary_metrics);
			}
		} else if (supportBenchmarkMode && supportResults) {
			if (window.AIRB && AIRB.SupportDashboard && AIRB.SupportDashboard.coreSummaryHtml && state.dashboardModel) {
				html += AIRB.SupportDashboard.coreSummaryHtml(state.dashboardModel);
			} else {
				var supUiHero = supportResults.ui && supportResults.ui.hero ? supportResults.ui.hero : null;
				html += supportReadinessHeroHtml(readiness, supUiHero);
				html += supportMetricGridHtml(r, supportResults);
				html += benchmarkOversightGaugeSectionHtml(r);
				html += supportDomainsSectionHtml(supportResults.domain_rows);
			}
		} else {
		html += readinessBandScaleHtml(readiness);

		html += '<div class="airb__res-grid3' + (parentMode ? ' airb__res-grid3--two' : '') + '">';
		html += '<div class="airb__res-stat airb__metric-card">';
		html += '<div class="airb__res-stat-lab">' + esc(i18n.statReadiness || 'Readiness score') + '</div>';
		html += '<div class="airb__res-stat-big" style="color:' + esc(readinessBandColor(readiness)) + '" data-count="' + readiness + '">' + readiness + '%</div>';
		if (!parentMode && !teacherBenchmarkMode) {
			html += '<div class="airb__res-stat-note">' + esc(i18n.statReadinessNote || 'Weighted across every domain in this audit.') + '</div>';
		}
		html += '</div>';

		if (!teacherBenchmarkMode) {
		html += '<div class="airb__res-stat airb__metric-card">';
		html += '<div class="airb__res-stat-lab">' + esc(i18n.statRisk || 'AI risk score') + '</div>';
		html += '<div class="airb__res-stat-big" style="color:' + esc(riskScoreColor(risk)) + '" data-count="' + risk + '">' + risk + '%</div>';
		if (!parentMode) {
			html += '<div class="airb__res-stat-note">' + esc(i18n.statRiskNote || 'Behavioural exposure — the inverse of readiness.') + '</div>';
		}
		html += '</div>';
		}

		if (!parentMode && !teacherBenchmarkMode) {
		html += '<div class="airb__res-stat airb__metric-card">';
		html += '<div class="airb__res-stat-lab">' + esc(i18n.dependency || 'AI Dependency Index') + '</div>';
		if (depVal === null) {
			html += '<div class="airb__res-stat-big airb__res-stat-big--na">—</div>';
			html += '<div class="airb__res-stat-note">' + esc(i18n.statDepNa || 'Not measured for this audience.') + '</div>';
		} else {
			html += '<div class="airb__res-stat-big" style="color:' + esc(dependencyColor(depVal)) + '" data-count="' + depVal + '">' + depVal + '%</div>';
			html += '<div class="airb__res-stat-note">' + esc(i18n.statDepNote || 'Risk indicator — higher means greater reliance on AI.') + '</div>';
		}
		html += '</div>';
		}
		html += '</div>';
		}

		if (bandSummary && !parentMode) {
			html += '<p class="airb__res-summary">' + esc(bandSummary) + '</p>';
		}

		if (leaderMode) {
			/* Governance content follows in leaderResultsHtml. */
		} else if (teacherBenchmarkMode) {
			/* Domains render in teacherDomainSectionHtml above. */
		} else if (parentMode && parentResults) {
			/* Metrics render in parentHomeMetricsSectionHtml above. */
		} else if (supportBenchmarkMode && supportResults) {
			/* Domains render in supportDomainsSectionHtml above; body in supportResultsHtml. */
		} else if (publicMode && publicResults) {
			/* Public dashboard/focus content follows in publicResultsHtml. */
		} else if (!parentMode && !studentMode) {
			html += '<div class="airb__res-two">' + oversightPanelHtml(r, { showNa: true }) + domainReadinessRowsHtml(r) + '</div>';
		}
		return html + '</section>';
	}

	function benchmarkHtml(r) {
		var b = r.benchmark;
		if (!b || !b.averages) return '';

		var i = i18n.benchmark || {};
		// Metrics: key, label, higher-is-better?
		var metrics = [
			{ key: 'alignment_score', label: i18n.alignment, better: 'high', mine: r.alignment_score },
			{ key: 'dependency_index', label: i18n.dependency, better: 'low', mine: r.dependency_index },
			{ key: 'privacy_risk', label: i18n.privacy, better: 'low', mine: r.privacy_risk },
			{ key: 'safeguarding_readiness', label: i18n.safeguarding, better: 'high', mine: r.safeguarding_readiness },
			{ key: 'governance_maturity', label: i18n.governance, better: 'high', mine: r.governance_maturity }
		];

		var html = '<section class="airb__benchmark" aria-label="' + esc(i.title || 'National benchmark') + '">';
		html += '<h4>' + esc(i.title || 'How you compare nationally') + '</h4>';

		if (typeof b.percentile === 'number') {
			html += '<p class="airb__benchmark-headline">' +
				esc((i.percentilePre || 'Your alignment score is ahead of') + ' ') +
				'<strong>' + b.percentile + '%</strong> ' +
				esc(i.percentilePost || 'of schools benchmarked.') + '</p>';
		}

		html += '<div class="airb__benchmark-rows">';
		metrics.forEach(function (m) {
			if (typeof m.mine !== 'number') return;
			var avg = b.averages[m.key];
			if (typeof avg !== 'number') return;
			var delta = m.mine - avg;
			// "Good" direction: high-is-better wants positive delta; low-is-better wants negative.
			var good = (m.better === 'high') ? (delta >= 0) : (delta <= 0);
			var sign = delta > 0 ? '+' : '';
			var cls = delta === 0 ? 'airb__benchmark-delta--flat' : (good ? 'airb__benchmark-delta--good' : 'airb__benchmark-delta--bad');
			html += '<div class="airb__benchmark-row">' +
				'<span class="airb__benchmark-metric">' + esc(m.label) + '</span>' +
				'<span class="airb__benchmark-you">' + m.mine + '</span>' +
				'<span class="airb__benchmark-avg">' + esc(i.avgShort || 'avg') + ' ' + avg + '</span>' +
				'<span class="airb__benchmark-delta ' + cls + '">' + sign + delta + '</span>' +
				'</div>';
		});
		html += '</div>';
		html += '<p class="airb__benchmark-note">' + esc((i.sampleNote || 'Based on {n} submissions for your role.').replace('{n}', b.sample_size)) + '</p>';
		return html + '</section>';
	}

	var shareModuleReady = false;

	function oversightGaugeGeometry(val) {
		if (window.AIRB && AIRB.Share && AIRB.Share.oversightGaugeGeometry) {
			return AIRB.Share.oversightGaugeGeometry(val);
		}
		val = Math.max(0, Math.min(100, val));
		return { val: val, zones: [[0, 10], [10, 25], [25, 50], [50, 100]] };
	}

	function initShareModule() {
		if (!window.AIRB || !AIRB.Share || shareModuleReady) return;
		AIRB.Share.init({
			i18n: i18n,
			cfg: cfg,
			getState: function () { return state; },
			oversightLabel: oversightLabel,
			oversightZoneColor: oversightZoneColor,
			esc: esc,
			trackEvent: trackEvent,
		});
		shareModuleReady = true;
	}

	function shareDependencyIndexImage(btn) {
		initShareModule();
		if (window.AIRB && AIRB.Share && AIRB.Share.shareDependencyIndexImage) {
			AIRB.Share.shareDependencyIndexImage(btn);
		}
	}

	function bindDependencyIndexShare() {
		if (!el.results) return;
		el.results.querySelectorAll('[data-airb-share-dependency-index]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				shareDependencyIndexImage(btn);
			});
		});
	}

	function oversightGaugeSvg(val, aria) {
		initShareModule();
		if (window.AIRB && AIRB.Share && AIRB.Share.oversightGaugeSvg) {
			return AIRB.Share.oversightGaugeSvg(val, aria);
		}
		return '';
	}

	// Static demo gauge on the intro hero (signature-metric preview).
	function renderDemoGauge() {
		var host = document.querySelector('[data-airb-demo-gauge]');
		if (!host) return;
		var val = parseInt(host.getAttribute('data-airb-demo-gauge'), 10);
		if (isNaN(val)) val = 34;
		host.innerHTML = oversightGaugeSvg(val, 'Human Oversight Ratio example: ' + val + '%');
	}

	function exposureCardsHtml(cells) {
		if (!cells || !cells.length) return '';
		var html = '<div class="airb__res-grid3">';
		cells.forEach(function (cell) {
			var risk = Math.round(cell.risk);
			html += '<div class="airb__res-stat">';
			html += '<div class="airb__res-stat-lab">' + esc(cell.label) + '</div>';
			html += '<div class="airb__res-stat-big" style="color:' + esc(riskScoreColor(risk)) + '">' + risk + '%</div>';
			html += '</div>';
		});
		return html + '</div>';
	}

	function heatmapHtml(cells) {
		if (!cells || !cells.length) return '';
		var html = '<div class="airb__heatmap">';
		cells.forEach(function (cell) {
			var risk = Math.round(cell.risk);
			html += '<div class="airb__heat-cell" title="' + esc(cell.label) + ' — ' + risk + '%">';
			html += '<span class="airb__heat-lab">' + esc(cell.label) + '</span>';
			html += '<span class="airb__heat-big" style="color:' + esc(riskScoreColor(risk)) + '">' + risk + '%</span></div>';
		});
		return html + '</div>';
	}

	function guidedImprovementHtml(r) {
		if (isParentRole() || isPublicRole() || isStudentRole() || isLeaderRole() || isTeacherRole() || isSupportStaffRole()) return '';
		var gi = r.guided_improvement;
		if (r.parent_results && r.parent_results.suppress_improvement) return '';
		if (r.public_results && r.public_results.suppress_improvement) return '';
		if (!gi) return '';
		if ((!gi.blocks || !gi.blocks.length) && !gi.consultation) return '';

		var kinds = improvementHub.resource_kinds || {};
		var html = '<section class="airb__guided-improvement" aria-label="' + esc(gi.heading || i18n.guidedImprovement || 'Learn how to improve this score') + '">';
		html += '<h3 class="airb__guided-title">' + esc(gi.heading || i18n.guidedImprovement || 'Learn how to improve this score') + '</h3>';
		if (gi.intro) html += '<p class="airb__guided-intro">' + esc(gi.intro) + '</p>';

		gi.blocks.forEach(function (block) {
			html += '<article class="airb__guided-block">';
			html += '<h4 class="airb__guided-metric">' + esc(block.label) + ': <span class="airb__guided-pct">' + block.score + '%</span></h4>';
			if (block.why_heading) html += '<p class="airb__guided-why-label"><strong>' + esc(block.why_heading) + '</strong></p>';
			if (block.why_body) html += '<p class="airb__guided-why">' + esc(block.why_body) + '</p>';
			if (block.why_risks && block.why_risks.length) {
				html += '<ul class="airb__guided-risks">';
				block.why_risks.forEach(function (risk) {
					html += '<li>' + esc(risk) + '</li>';
				});
				html += '</ul>';
			}
			if (block.actions_heading) html += '<p class="airb__guided-actions-label"><strong>' + esc(block.actions_heading) + '</strong></p>';
			if (block.resources && block.resources.length) {
				var res = block.resources[0];
				var kindLabel = kinds[res.kind] || res.kind || 'Read';
				var linkUrl = appendSessionToUrl(res.url || '');
				var pillar = block.slug || '';
				html += '<p class="airb__guided-primary"><a href="' + esc(linkUrl) + '" class="airb__btn airb__btn--primary airb__guided-primary-btn" target="_blank" rel="noopener noreferrer" data-airb-track="guided" data-airb-pillar="' + esc(pillar) + '" data-airb-kind="' + esc(res.kind || 'read') + '" data-airb-label="' + esc(res.label || '') + '"><span class="airb__guided-kind">' + esc(kindLabel) + '</span> ' + esc(res.label) + '</a></p>';
			}
			html += '</article>';
		});

		if (gi.consultation) {
			var c = gi.consultation;
			html += '<aside class="airb__guided-consult airb__insight airb__insight--consult">';
			html += '<span class="airb__insight-label">' + esc(c.title) + '</span>';
			if (c.intro) html += '<p>' + esc(c.intro) + '</p>';
			if (c.items && c.items.length) {
				html += '<ul class="airb__guided-risks">';
				c.items.forEach(function (item) {
					html += '<li>' + esc(item) + '</li>';
				});
				html += '</ul>';
			}
			if (c.closing) html += '<p class="airb__muted">' + esc(c.closing) + '</p>';
			if (c.cta_url) {
				html += resultsCtaHtml(c.cta_url, c.cta_text || 'Book your free review', '', 'data-airb-track="consultation"');
			}
			html += '</aside>';
		}

		return html + '</section>';
	}

	function syncDashboardModel(r) {
		state.dashboardModel = null;
		if (!r || !window.AIRB || !AIRB.DashboardModel || !AIRB.DashboardModel.build) {
			return;
		}
		try {
			state.dashboardModel = AIRB.DashboardModel.build(r, state.role);
		} catch (err) {
			if (window.console && console.error) {
				console.error('AIRB dashboard model failed', err);
			}
		}
	}

	function renderResults() {
		var r = state.results;
		if (!r) return;
		syncDashboardModel(r);
		try {
			renderResultsBody(r);
		} catch (err) {
			if (window.console && console.error) {
				console.error('AIRB renderResults failed', err);
			}
			el.results.innerHTML = '<div class="airb__panel"><p class="airb__error">' + esc(i18n.error || 'Something went wrong. Please try again.') + '</p></div>';
			el.results.hidden = false;
			el.role.hidden = true;
			el.audit.hidden = true;
			el.contact.hidden = true;
			el.nav.hidden = true;
			el.progress.hidden = true;
			updateFlowChrome();
		}
	}

	function renderResultsBody(r) {
		var parentMode = isParentRole();
		var publicMode = isPublicRole() && !!r.public_results;
		var teacherMode = isTeacherRole() && !!r.teacher_results;
		var studentMode = isStudentRole() && !!r.student_results;

		var leaderMode = isLeaderRole() && !!r.leader_results;
		var supportMode = isSupportStaffRole() && !!r.support_results;
		var benchmarkResultsMode = studentMode || teacherMode || leaderMode || parentMode || publicMode || supportMode;

		var html = '<div class="airb__results' + (parentMode ? ' airb__results--parent' + (state.dashboardModel && isParentRole() && r.parent_results ? ' airb__results--parent-dash' : '') : '') + (publicMode ? ' airb__results--public' + (state.dashboardModel ? ' airb__results--public-dash' : '') : '') + (teacherMode ? ' airb__results--teacher' + (state.dashboardModel ? ' airb__results--teacher-dash' : '') : '') + (studentMode ? ' airb__results--student' + (state.dashboardModel ? ' airb__results--student-dash' : '') : '') + (leaderMode ? ' airb__results--leader' + (state.dashboardModel ? ' airb__results--leader-dash' : '') : '') + (supportMode ? ' airb__results--support' + (state.dashboardModel ? ' airb__results--support-dash' : '') : '') + '">';
		if (!(publicMode && state.dashboardModel)) {
			html += resultsProfileHtml(r);
		}

		if (teacherMode) {
			html += teacherResultsHtml(r);
		} else if (studentMode) {
			html += studentResultsHtml(r);
		} else if (leaderMode) {
			html += leaderResultsHtml(r);
		} else if (supportMode) {
			html += supportResultsHtml(r);
		} else if (parentMode) {
			html += parentResultsHtml(r);
		} else if (publicMode) {
			html += publicResultsHtml(r);
		} else {
			html += focusDomainsHtml(r);
		}

		html += guidedImprovementHtml(r);

		if (state.school && isStaffRole()) {
			html += '<div id="airb-school-snapshot" class="airb__school-snapshot" aria-live="polite" hidden></div>';
		}

		if (!parentMode && !teacherMode && !studentMode && !leaderMode && !supportMode && !publicMode) {
			if (r.funnel_closing) {
				html += '<aside class="airb__insight"><span class="airb__insight-label">' + esc(i18n.insightLabel) + '</span><p>' + esc(r.funnel_closing) + '</p></aside>';
			}

			if (r.risk_heatmap && r.risk_heatmap.length) {
				html += '<h4>' + esc(i18n.heatMap) + '</h4>' + heatmapHtml(r.risk_heatmap);
			}
		}

		if (r.leadership_report && r.leadership_report.show_full) {
			var rep = r.leadership_report;
			html += '<section class="airb__leadership-report">';
			html += '<p class="airb__funnel-stage">' + esc(i18n.stage3) + '</p>';
			html += '<h4>' + esc(rep.title) + '</h4>';
			html += '<p class="airb__report-score">' + esc(i18n.overallScore) + ': <strong>' + rep.overall_score + '%</strong>';
			if (rep.risk_level_label) html += ' · ' + esc(rep.risk_level_label);
			html += '</p>';
			if (rep.school_profile) html += '<p class="airb__muted">' + esc(rep.school_profile) + '</p>';
			if (rep.high_risk_areas && rep.high_risk_areas.length) {
				html += '<h5>' + esc(i18n.highRiskAreas) + '</h5>' + exposureCardsHtml(rep.high_risk_areas);
			}
			if (rep.recommended_actions && rep.recommended_actions.length) {
				html += '<h5>' + esc(i18n.recommendedActions) + '</h5><ol class="airb__action-list">';
				rep.recommended_actions.forEach(function (act) {
					html += '<li>' + esc(act) + '</li>';
				});
				html += '</ol>';
			}
			html += '</section>';
		}

		if (!parentMode && !teacherMode && !studentMode && !leaderMode && !publicMode && r.policy_support) {
			var pg = r.policy_support;
			html += '<article class="airb__policy-gen airb__pathway-card">';
			html += '<span class="airb__pathway-badge airb__pathway-badge--policy">' + esc(i18n.policyGen) + '</span>';
			html += '<h5>' + esc(pg.title) + '</h5><p>' + esc(pg.body) + '</p>';
			if (pg.cta_url) html += resultsCtaHtml(pg.cta_url, pg.cta_text, '', 'data-airb-track="policy_support"');
			html += '</article>';
		}

		if (!parentMode && !teacherMode && !studentMode && !leaderMode && !publicMode && r.stage2_products && r.stage2_products.length) {
			html += '<section class="airb__stage2"><p class="airb__funnel-stage">' + esc(i18n.stage2) + '</p>';
			html += '<ul class="airb__stage2-list">';
			r.stage2_products.forEach(function (item) {
				html += '<li><span class="airb__stage2-reason">' + esc(item.reason) + '</span> → <strong>' + esc(item.product) + '</strong></li>';
			});
			html += '</ul></section>';
		}

		if (!parentMode && !teacherMode && !studentMode && !leaderMode && !publicMode && r.key_exposure_areas && r.key_exposure_areas.length) {
			html += '<h4>' + esc(i18n.exposure) + '</h4>' + exposureCardsHtml(r.key_exposure_areas);
		}

		if (!parentMode && !teacherMode && !studentMode && !leaderMode && !supportMode && !publicMode) {
			html += benchmarkHtml(r);
		}

		if (!benchmarkResultsMode) {
			var shareHint = shareResultsHintText(r);
			if (shareHint) {
				html += '<p class="airb__muted airb__share-hint">' + esc(shareHint) + '</p>';
			}

			html += '<div class="airb__results-actions">';
			html += '<button type="button" class="airb__btn airb__btn--ghost airb__btn--copy-result" id="airb-copy-result">' + esc(i18n.copyResult || 'Copy result') + '</button>';
			var shareMailto = buildShareResultsMailto();
			if (shareMailto) {
				html += '<a class="airb__btn airb__btn--primary airb__btn--share" href="' + shareMailto + '" id="airb-share-results">' + esc(i18n.shareWithSchool || 'Share results with your school') + '</a>';
			}
			if (!hasInterestForm(r) && state.email) {
				html += '<button type="button" class="airb__btn airb__btn--ghost" id="airb-email-report">' + esc(i18n.emailReport) + '</button>';
			}
			html += '</div>';
		}

		if (roleShowsInterestForm(state.role)) {
			if (!hasInterestForm(r)) {
				var shells = airbBenchmark.interestForms || {};
				if (shells[state.role]) {
					mergeInterestFormShell(r, shells[state.role]);
				}
			}
			html += interestFormHtml(r);
		}

		if (state.school && !isStaffRole()) {
			html += '<p class="airb__school-link"><a class="airb__btn airb__btn--ghost airb__btn--sm" href="?school=' + encodeURIComponent(state.school) + '#airb-school-dashboard">' + esc(i18n.viewSchool) + '</a></p>';
			html += '<p class="airb__muted airb__school-hint">' + esc(i18n.schoolHint) + '</p>';
		}
		html += '</div>';

		el.results.innerHTML = html;
		el.results.hidden = false;
		el.role.hidden = true;
		el.audit.hidden = true;
		el.contact.hidden = true;
		el.nav.hidden = true;
		el.progress.hidden = true;
		updateFlowChrome();

		var emailBtn = document.getElementById('airb-email-report');
		if (emailBtn) emailBtn.addEventListener('click', emailReport);

		bindResultsTracking();
		bindDependencyIndexShare();
		bindInterestForm();
		bindInterestTriggers();
		bindStudentRetakeTriggers();
		bindPublicRetakeTriggers();
		bindTeacherDashboard();
		bindStudentDashboard();
		bindParentDashboard();
		bindLeaderDashboard();
		bindSupportDashboard();
		bindPublicDashboard();
		fetchSchoolSnapshot();

		animateResultsStats();
		persistRoleCompletion(r);
		updateAppbarCompletions();
		scrollFlowToTop();
	}

	function bindResultsTracking() {
		trackEvent('results_viewed', {
			alignment_score: state.results && state.results.alignment_score,
			submission_id: state.submissionId || 0,
		});

		el.results.querySelectorAll('[data-airb-track="guided"]').forEach(function (link) {
			link.addEventListener('click', function () {
				trackEvent('guided_resource_click', {
					pillar: link.getAttribute('data-airb-pillar') || '',
					kind: link.getAttribute('data-airb-kind') || '',
					label: link.getAttribute('data-airb-label') || '',
					url: link.getAttribute('href') || '',
				});
			});
		});

		var copyBtn = document.getElementById('airb-copy-result');
		if (copyBtn) {
			var copyLabel = i18n.copyResult || 'Copy result';
			var copiedLabel = i18n.copiedResult || 'Copied!';
			copyBtn.addEventListener('click', function () {
				var text = buildShareScoreText(state.results);
				if (!text || !navigator.clipboard || !navigator.clipboard.writeText) return;
				navigator.clipboard.writeText(text).then(function () {
					copyBtn.textContent = copiedLabel;
					trackEvent('share_copy', {});
					setTimeout(function () {
						copyBtn.textContent = copyLabel;
					}, 2000);
				});
			});
		}

		var shareBtn = document.getElementById('airb-share-results');
		if (shareBtn) {
			shareBtn.addEventListener('click', function () {
				trackEvent('share_click', {});
			});
		}

		el.results.querySelectorAll('[data-airb-share-action]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var title = btn.getAttribute('data-airb-share-title') || 'AI Risk & Readiness Benchmark';
				var text = btn.getAttribute('data-airb-share-text') || '';
				var url = btn.getAttribute('data-airb-share-url') || benchmarkShareUrl();
				var status = btn.closest('.airb__dashboard-share') && btn.closest('.airb__dashboard-share').querySelector('[data-airb-share-action-status]');
				var done = function (message) {
					if (!status) return;
					status.textContent = message;
					status.hidden = false;
					setTimeout(function () {
						status.hidden = true;
						status.textContent = '';
					}, 2500);
				};
				if (navigator.share) {
					navigator.share({ title: title, text: text, url: url }).then(function () {
						trackEvent('share_click', { mode: 'action', role: state.role || '' });
					}).catch(function (err) {
						if (err && err.name === 'AbortError') return;
						if (navigator.clipboard && navigator.clipboard.writeText) {
							navigator.clipboard.writeText(text + ' Try the benchmark: ' + url).then(function () {
								done('Share text copied.');
								trackEvent('share_copy', { mode: 'action', role: state.role || '' });
							});
						}
					});
					return;
				}
				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(text + ' Try the benchmark: ' + url).then(function () {
						done('Share text copied.');
						trackEvent('share_copy', { mode: 'action', role: state.role || '' });
					});
				}
			});
		});

		var reportBtn = document.getElementById('airb-request-report');
		if (reportBtn) {
			reportBtn.addEventListener('click', function () {
				trackEvent('report_request_click', {});
			});
		}

		el.results.querySelectorAll('[data-airb-track="leader_next_step"]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				trackEvent('leader_next_step_click', {
					key: btn.getAttribute('data-airb-card-key') || '',
				});
			});
		});

		el.results.querySelectorAll('[data-airb-track="consultation"]').forEach(function (link) {
			link.addEventListener('click', function () {
				trackEvent('consultation_click', {
					url: link.getAttribute('href') || '',
				});
			});
		});
	}

	function animateResultsStats() {
		var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		el.results.querySelectorAll('.airb__res-stat-big[data-count]').forEach(function (num) {
			var score = parseInt(num.getAttribute('data-count'), 10) || 0;
			if (reduce) {
				num.textContent = score + '%';
				return;
			}
			var start = null;
			var dur = 850;
			function step(ts) {
				if (start === null) start = ts;
				var p = Math.min((ts - start) / dur, 1);
				var eased = 1 - Math.pow(1 - p, 3);
				num.textContent = Math.round(eased * score) + '%';
				if (p < 1) requestAnimationFrame(step);
				else num.textContent = score + '%';
			}
			requestAnimationFrame(step);
		});
	}

	function card(item) {
		var band = item.band || 'moderate';
		var bandText = item.band_label || bandLabel(band);
		var html = '<article class="airb__card airb__card--' + band + '">' +
			'<span class="airb__card-title">' + esc(item.label) + '</span>' +
			'<strong class="airb__card-value">' + esc(String(item.value)) + '</strong>';
		if (item.tone !== 'oversight') {
			html += '<span class="airb__card-band">' + esc(bandText) + '</span>';
		}
		return html + '</article>';
	}

	function updateAuditProgressStepper(sections, answers, step) {
		if (!el.stepper) return;
		var counts = auditQuestionCounts(sections, answers, step);
		if (usesSingleQuestionFlow()) {
			var currentGlobal = counts.offsetBeforeSection + state.questionStep + 1;
			var pct = counts.total ? Math.round((currentGlobal / counts.total) * 100) : 0;
			el.stepper.className = 'airb__stepper airb__stepper--bar';
			el.stepper.innerHTML = '<span class="airb__progress-fill" style="width:' + pct + '%" role="progressbar" aria-valuenow="' + currentGlobal + '" aria-valuemin="1" aria-valuemax="' + counts.total + '"></span>';
			return;
		}
		el.stepper.className = 'airb__stepper';
		var html = '';
		for (var i = 0; i < counts.total; i++) {
			var cls = i < counts.offsetBeforeSection ? 'is-done' : (i < counts.offsetBeforeSection + counts.countInSection ? 'is-current' : '');
			html += '<span class="airb__seg ' + cls + '" role="listitem"></span>';
		}
		el.stepper.innerHTML = html;
	}

	function updateStepper(total, idx) {
		if (!el.stepper) return;
		var html = '';
		for (var i = 0; i < total; i++) {
			var cls = i < idx ? 'is-done' : (i === idx ? 'is-current' : '');
			html += '<span class="airb__seg ' + cls + '" role="listitem"></span>';
		}
		el.stepper.innerHTML = html;
		if (el.progressLbl) {
			el.progressLbl.textContent = i18n.step + ' ' + (idx + 1) + ' ' + i18n.of + ' ' + total;
		}
	}

	function saveQuestionAnswer(q) {
		if (!q || !questionApplies(q, state.answers)) {
			return true;
		}
		if (q.type === 'slider') {
			var sl = document.getElementById('airb-q-' + q.id);
			if (!sl) return false;
			state.answers[q.id] = sl.value;
			return true;
		}
		if (q.type === 'select') {
			var sel = document.getElementById('airb-q-' + q.id);
			if (!sel || !sel.value) return false;
			state.answers[q.id] = sel.value;
			return true;
		}
		var picked = el.audit && el.audit.querySelector('input[name="airb-q-' + q.id + '"]:checked');
		if (!picked) return false;
		state.answers[q.id] = picked.value;
		return true;
	}

	function saveSectionAnswers(section) {
		if (!section) return false;
		var draft = sectionAnswersDraft(section);
		var complete = true;
		section.questions.forEach(function (q) {
			if (!questionApplies(q, draft)) {
				delete state.answers[q.id];
				return;
			}
			if (q.type === 'slider') {
				var sl = document.getElementById('airb-q-' + q.id);
				if (!sl) { complete = false; return; }
				state.answers[q.id] = sl.value;
				return;
			}
			if (q.type === 'select') {
				var sel = document.getElementById('airb-q-' + q.id);
				if (!sel || !sel.value) { complete = false; return; }
				state.answers[q.id] = sel.value;
				return;
			}
			var picked = el.audit.querySelector('input[name="airb-q-' + q.id + '"]:checked');
			if (!picked) { complete = false; return; }
			state.answers[q.id] = picked.value;
		});
		return complete;
	}

	function goNext() {
		hideError();
		if (state.phase === 'role') {
			beginAudit();
			return;
		}
		if (state.phase === 'leader_profile') {
			var phaseSel = document.getElementById('airb-leader-phase');
			state.schoolPhase = phaseSel ? (phaseSel.value || '') : '';
			if (!state.schoolPhase) {
				showError(i18n.leaderProfilePhaseRequired || i18n.required);
				return;
			}
			syncProfileIntoAnswers();
			startAuditQuestions();
			return;
		}
		if (state.phase === 'audit') {
			var section = state.sections[state.step];
			if (usesSingleQuestionFlow()) {
				var visible = visibleQuestionsForCurrentSection();
				var currentQ = visible[state.questionStep];
				if (!currentQ || !saveQuestionAnswer(currentQ)) {
					showError(i18n.required);
					return;
				}
				if (state.questionStep < visible.length - 1) {
					state.questionStep++;
					renderAuditSection();
					return;
				}
			}
			if (!saveSectionAnswers(section)) { showError(i18n.required); return; }
			if (state.step < state.sections.length - 1) {
				state.step++;
				state.questionStep = 0;
				renderAuditSection();
				return;
			}
			state.phase = 'contact';
			renderContact();
			return;
		}
		if (state.phase === 'contact') {
			if (!isYoungRole()) {
				state.school = (state.role === 'leader' || state.role === 'teacher' || state.role === 'support_staff') ? ((document.getElementById('airb-school') || {}).value || '') : '';
				state.email = (document.getElementById('airb-email') || {}).value || '';
				var phaseInput = document.getElementById('airb-school-phase');
				if (phaseInput) {
					state.schoolPhase = phaseInput.value || '';
				}
				state.orgType = (document.getElementById('airb-org-type') || {}).value || '';
				if (state.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(state.email)) {
					showError(i18n.emailInvalid);
					return;
				}
			} else {
				state.yearGroup = (document.getElementById('airb-year-group') || {}).value || '';
				if (state.role === 'student') {
					state.school = (document.getElementById('airb-school') || {}).value || '';
				} else {
					state.school = '';
				}
				state.email = '';
			}

			syncProfileIntoAnswers();
			state.results = calculate(state.role, answersWithProfile());
			state.phase = 'results';
			el.results.innerHTML = '<p class="airb__muted airb__loading">' + esc(i18n.saving || 'Preparing your results…') + '</p>';
			el.results.hidden = false;
			el.role.hidden = true;
			el.audit.hidden = true;
			el.contact.hidden = true;
			el.nav.hidden = true;
			el.progress.hidden = true;
			updateFlowChrome();
			scrollFlowToTop();
			var resultsRendered = false;
			var resultsFallbackTimer = window.setTimeout(function () {
				if (state.phase !== 'results' || resultsRendered) return;
				resultsRendered = true;
				renderResults();
			}, 3000);
			submitResults(function () {
				window.clearTimeout(resultsFallbackTimer);
				resultsRendered = true;
				renderResults();
			});
		}
	}

	function goBack() {
		hideError();
		if (state.phase === 'audit' && usesSingleQuestionFlow()) {
			var section = state.sections[state.step];
			var visible = visibleQuestionsForCurrentSection();
			var currentQ = visible[state.questionStep];
			if (currentQ) {
				saveQuestionAnswer(currentQ);
			}
			if (state.questionStep > 0) {
				state.questionStep--;
				renderAuditSection();
				return;
			}
		}
		if (state.phase === 'audit' && state.step > 0) {
			saveSectionAnswers(state.sections[state.step]);
			state.step--;
			if (usesSingleQuestionFlow()) {
				var prevVisible = visibleQuestionsInSection(state.sections[state.step], state.answers);
				state.questionStep = Math.max(0, prevVisible.length - 1);
			} else {
				state.questionStep = 0;
			}
			renderAuditSection();
			return;
		}
		if (state.phase === 'audit' && state.step === 0) {
			if (state.role === 'leader') {
				state.phase = 'leader_profile';
				renderLeaderProfile();
				return;
			}
			state.phase = 'role';
			if (!isMobileFlow()) {
				expandIntro();
			}
			renderRole();
			return;
		}
		if (state.phase === 'contact') {
			state.phase = 'audit';
			state.step = state.sections.length - 1;
			renderAuditSection({ scrollToTop: true });
			return;
		}
		if (state.phase === 'leader_profile') {
			state.phase = 'role';
			state.schoolPhase = '';
			if (!isMobileFlow()) {
				expandIntro();
			}
			renderRole();
		}
	}

	function submitResults(done) {
		syncProfileIntoAnswers();
		var body = new FormData();
		body.append('action', 'airb_submit_benchmark');
		body.append('nonce', airbBenchmark.nonce);
		body.append('role', state.role);
		body.append('answers', JSON.stringify(state.answers));
		body.append('school_name', state.school);
		body.append('email', state.email);
		body.append('session_id', getSessionId());
		body.append('school_phase', state.schoolPhase);
		body.append('org_type', state.orgType);
		body.append('year_group', state.yearGroup);

		fetch(airbBenchmark.ajaxurl, { method: 'POST', body: body, credentials: 'same-origin' })
			.then(function (res) { return res.json(); })
			.then(function (json) {
				try {
					if (json.success && json.data && json.data.results) {
						state.results = json.data.results;
					} else if (state.results && state.role && roleShowsInterestForm(state.role)) {
						var shells = airbBenchmark.interestForms || {};
						if (shells[state.role]) {
							mergeInterestFormShell(state.results, shells[state.role]);
						}
					}
					if (json.success && json.data && json.data.submission_id) {
						state.submissionId = parseInt(json.data.submission_id, 10) || 0;
					}
					persistResultsSnapshot();
				} catch (err) {
					if (window.console && console.error) {
						console.error('AIRB submitResults handler failed', err);
					}
				}
				if (done) done();
			})
			.catch(function (err) {
				if (window.console && console.error) {
					console.error('AIRB submitResults request failed', err);
				}
				if (state.results && state.role && roleShowsInterestForm(state.role)) {
					var shells = airbBenchmark.interestForms || {};
					if (shells[state.role]) {
						mergeInterestFormShell(state.results, shells[state.role]);
					}
				}
				if (done) done();
			});
	}

	function shareResultsHintText(r) {
		if (isParentRole()) {
			return parentResult.share_hint || i18n.shareResultsHintParent || i18n.shareResultsHint || '';
		}
		if (isStudentRole()) {
			return (r && r.student_results && r.student_results.share_hint) || studentResult.share_hint || i18n.shareResultsHint || '';
		}
		if (isTeacherRole()) {
			return teacherResult.share_hint || i18n.shareResultsHintTeacher || i18n.shareResultsHint || '';
		}
		if (isLeaderRole()) {
			return leaderResult.share_hint || i18n.shareResultsHintLeader || i18n.shareResultsHintTeacher || i18n.shareResultsHint || '';
		}
		if (isSupportStaffRole()) {
			return (r && r.support_results && r.support_results.share_hint) || supportResult.share_hint || i18n.shareResultsHintSupport || i18n.shareResultsHintTeacher || i18n.shareResultsHint || '';
		}
		return i18n.shareResultsHint || '';
	}

	function isInterestCtaUrl(url) {
		if (!url) return false;
		return url.indexOf('#airb-interest') === 0 || url.indexOf('mailto:') === 0;
	}

	function interestPrefillFromUrl(url) {
		if (!url) return '';
		if (url.indexOf('mailto:') === 0) return 'further_information';
		if (url.indexOf('#airb-interest') !== 0) return '';
		var q = url.split('?')[1] || '';
		try {
			return new URLSearchParams(q).get('prefill') || '';
		} catch (e) {
			return '';
		}
	}

	function resultsCtaHtml(url, text, extraClass, trackAttr) {
		extraClass = extraClass || '';
		trackAttr = trackAttr || '';
		if (isInterestCtaUrl(url)) {
			var prefill = interestPrefillFromUrl(url);
			return '<button type="button" class="airb__btn airb__btn--primary airb__btn--sm airb__btn--interest ' + esc(extraClass) + '" data-airb-open-interest="' + esc(prefill) + '" ' + trackAttr + '>' + esc(text) + '</button>';
		}
		var target = url.indexOf('mailto:') === 0 ? '' : ' target="_blank" rel="noopener noreferrer"';
		return '<a class="airb__btn airb__btn--primary airb__btn--sm ' + esc(extraClass) + '" href="' + esc(url) + '"' + target + ' ' + trackAttr + '>' + esc(text) + '</a>';
	}

	function gatewayInfoHtml(r) {
		if (isLeaderRole() || isTeacherRole() || isParentRole()) return '';
		if (!isStaffRole() || !r.gateway || !r.gateway.cards || !r.gateway.cards.length) return '';
		var headline = r.gateway.headline || i18n.gatewayTitle || 'What happens next?';
		var html = '<details class="airb__about airb__gateway-info">';
		html += '<summary class="airb__about-toggle"><span>' + esc(headline) + '</span><span class="airb__about-icon" aria-hidden="true"></span></summary>';
		html += '<div class="airb__about-body">';
		if (r.gateway.intro) html += '<p class="airb__muted airb__gateway-info-intro">' + esc(r.gateway.intro) + '</p>';
		html += '<ul class="airb__gateway-points">';
		r.gateway.cards.forEach(function (card) {
			html += '<li class="airb__gateway-point"><strong>' + esc(card.title) + '</strong>';
			if (card.body) html += '<span>' + esc(card.body) + '</span>';
			html += '</li>';
		});
		html += '</ul></div></details>';
		return html;
	}

	function parentDashboardFollowUpHtml(r, form, labels, fields, suggested) {
		var demoOptionSlugs = ['parent_share_with_school', 'parent_resources', 'parent_school_take_part'];
		var options = (form.options || []).filter(function (opt) {
			return demoOptionSlugs.indexOf(opt.slug) >= 0;
		});
		options.sort(function (a, b) {
			return demoOptionSlugs.indexOf(a.slug) - demoOptionSlugs.indexOf(b.slug);
		});
		if (!options.length) {
			options = (form.options || []).slice(0, 3);
		}

		var weakestLabel = '';
		if (state.dashboardModel && state.dashboardModel.domains && state.dashboardModel.domains.length) {
			var weakest = state.dashboardModel.domains.reduce(function (min, domain) {
				return !min || domain.value < min.value ? domain : min;
			}, null);
			if (weakest) weakestLabel = weakest.label.toLowerCase();
		}
		var messagePlaceholder = weakestLabel
			? 'Example: We need help with ' + weakestLabel + ' at home.'
			: 'Example: We want help sharing our results with school.';

		var html = '<section class="airb__interest airb__teacher-dash-follow-up" id="airb-interest" aria-labelledby="airb-interest-heading">';
		html += '<span id="benchmark-follow-up" class="airb__sr-only" aria-hidden="true"></span>';
		html += '<div class="airb__teacher-follow-up-head">';
		html += '<span class="airb__teacher-follow-up-icon" aria-hidden="true">';
		html += '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
		html += '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>';
		html += '</svg></span>';
		html += '<div class="airb__teacher-follow-up-copy">';
		html += '<p class="airb__teacher-follow-up-eyebrow">Optional next step</p>';
		html += '<h2 class="airb__interest-heading" id="airb-interest-heading">Want help with your next step?</h2>';
		html += '<p class="airb__interest-intro">' + esc(
			labels.intro || 'Your results help your school understand how families use AI at home. Tell us if you want guides, school engagement, or AI Awareness Day support.'
		) + '</p>';
		html += '</div></div>';

		html += '<form class="airb__interest-form airb__teacher-follow-up-form" id="airb-interest-form" novalidate>';
		html += '<fieldset class="airb__interest-options airb__teacher-follow-up-options">';
		html += '<legend class="airb__interest-legend airb__sr-only">' + esc(labels.interests || 'What would you like?') + '</legend>';
		options.forEach(function (opt) {
			var checked = suggested.indexOf(opt.slug) >= 0;
			if (!checked && opt.slug === 'parent_share_with_school' && !suggested.length) {
				checked = true;
			}
			var inputId = 'airb-interest-' + opt.slug;
			html += '<label class="airb__interest-option" for="' + esc(inputId) + '">';
			html += '<input type="checkbox" id="' + esc(inputId) + '" name="interests[]" value="' + esc(opt.slug) + '"' + (checked ? ' checked' : '') + '>';
			html += '<span class="airb__interest-option-text">';
			html += '<span class="airb__interest-option-label">' + esc(opt.label) + '</span>';
			if (opt.description) {
				html += '<span class="airb__interest-option-desc">' + esc(opt.description) + '</span>';
			}
			html += '</span></label>';
		});
		html += '</fieldset>';

		html += '<div class="airb__interest-fields airb__teacher-follow-up-fields">';
		if (fields.show_name) {
			html += '<label class="airb__field"><span class="airb__label">' + esc(labels.name || 'Your name') + '</span>';
			html += '<input class="airb__input" type="text" name="interest_name" autocomplete="name" placeholder="Alex Morgan"></label>';
		}
		if (fields.show_email) {
			html += '<label class="airb__field"><span class="airb__label">' + esc(labels.email || 'Email address') + (fields.email_required ? ' *' : '') + '</span>';
			html += '<input class="airb__input" type="email" name="interest_email"' + (fields.email_required ? ' required' : '') + ' autocomplete="email" value="' + esc(state.email || '') + '">';
			if (labels.email_hint) html += '<span class="airb__field-hint airb__muted">' + esc(labels.email_hint) + '</span>';
			html += '</label>';
		}
		if (fields.show_child_school) {
			html += '<label class="airb__field airb__field--full"><span class="airb__label">' + esc(labels.child_school || 'Child\'s school (optional)') + '</span>';
			html += '<input class="airb__input" type="text" name="interest_child_school" autocomplete="organization" placeholder="Riverside Academy"></label>';
		}
		if (fields.show_school) {
			html += '<label class="airb__field airb__field--full"><span class="airb__label">' + esc(labels.school || 'School / trust name') + '</span>';
			html += '<input class="airb__input" type="text" name="interest_school" autocomplete="organization" value="' + esc(state.school || '') + '" placeholder="Riverside Academy"></label>';
		}
		html += '<label class="airb__field airb__field--full"><span class="airb__label">' + esc(labels.message || 'Anything else we should know?') + '</span>';
		html += '<textarea class="airb__input airb__textarea" name="interest_message" rows="3" placeholder="' + esc(messagePlaceholder) + '"></textarea></label>';
		html += '</div>';

		html += '<p class="airb__interest-status" id="airb-interest-status" role="status" aria-live="polite" hidden></p>';
		html += '<button type="submit" class="airb__btn airb__btn--primary airb__interest-submit airb__teacher-follow-up-submit">';
		html += esc(labels.submit_short || 'Request follow-up');
		html += '<svg class="airb__teacher-follow-up-submit-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>';
		html += '</button>';
		html += '</form></section>';
		return html;
	}

	function supportDashboardFollowUpHtml(r, form, labels, fields, suggested) {
		var demoOptionSlugs = ['support_data_checklist', 'support_verification_resources', 'support_staff_cpd'];
		var options = (form.options || []).filter(function (opt) {
			return demoOptionSlugs.indexOf(opt.slug) >= 0;
		});
		options.sort(function (a, b) {
			return demoOptionSlugs.indexOf(a.slug) - demoOptionSlugs.indexOf(b.slug);
		});
		if (!options.length) {
			options = (form.options || []).slice(0, 3);
		}

		var weakestLabel = '';
		if (state.dashboardModel && state.dashboardModel.domains && state.dashboardModel.domains.length) {
			var weakest = state.dashboardModel.domains.reduce(function (min, domain) {
				return !min || domain.value < min.value ? domain : min;
			}, null);
			if (weakest) weakestLabel = weakest.label.toLowerCase();
		}
		var messagePlaceholder = weakestLabel
			? 'Example: We need help with ' + weakestLabel + ' in our office workflows.'
			: 'Example: We need a data protection checklist for support staff.';

		var html = '<section class="airb__interest airb__teacher-dash-follow-up" id="airb-interest" aria-labelledby="airb-interest-heading">';
		html += '<span id="benchmark-follow-up" class="airb__sr-only" aria-hidden="true"></span>';
		html += '<div class="airb__teacher-follow-up-head">';
		html += '<span class="airb__teacher-follow-up-icon" aria-hidden="true">';
		html += '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
		html += '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>';
		html += '</svg></span>';
		html += '<div class="airb__teacher-follow-up-copy">';
		html += '<p class="airb__teacher-follow-up-eyebrow">Optional next step</p>';
		html += '<h2 class="airb__interest-heading" id="airb-interest-heading">Want help with your next step?</h2>';
		html += '<p class="airb__interest-intro">' + esc(
			labels.intro || 'Tell us if you would like data protection checklists, verification resources, or support-staff CPD.'
		) + '</p>';
		html += '</div></div>';

		html += '<form class="airb__interest-form airb__teacher-follow-up-form" id="airb-interest-form" novalidate>';
		html += '<fieldset class="airb__interest-options airb__teacher-follow-up-options">';
		html += '<legend class="airb__interest-legend airb__sr-only">' + esc(labels.interests || 'What would you like?') + '</legend>';
		options.forEach(function (opt) {
			var checked = suggested.indexOf(opt.slug) >= 0;
			if (!checked && opt.slug === 'support_data_checklist' && !suggested.length) {
				checked = true;
			}
			var inputId = 'airb-interest-' + opt.slug;
			html += '<label class="airb__interest-option" for="' + esc(inputId) + '">';
			html += '<input type="checkbox" id="' + esc(inputId) + '" name="interests[]" value="' + esc(opt.slug) + '"' + (checked ? ' checked' : '') + '>';
			html += '<span class="airb__interest-option-text">';
			html += '<span class="airb__interest-option-label">' + esc(opt.label) + '</span>';
			if (opt.description) {
				html += '<span class="airb__interest-option-desc">' + esc(opt.description) + '</span>';
			}
			html += '</span></label>';
		});
		html += '</fieldset>';

		html += '<div class="airb__interest-fields airb__teacher-follow-up-fields">';
		if (fields.show_name) {
			html += '<label class="airb__field"><span class="airb__label">' + esc(labels.name || 'Your name') + '</span>';
			html += '<input class="airb__input" type="text" name="interest_name" autocomplete="name" placeholder="Alex Morgan"></label>';
		}
		if (fields.show_email) {
			html += '<label class="airb__field"><span class="airb__label">' + esc(labels.email || 'Email address') + (fields.email_required ? ' *' : '') + '</span>';
			html += '<input class="airb__input" type="email" name="interest_email"' + (fields.email_required ? ' required' : '') + ' autocomplete="email" value="' + esc(state.email || '') + '">';
			if (labels.email_hint) html += '<span class="airb__field-hint airb__muted">' + esc(labels.email_hint) + '</span>';
			html += '</label>';
		}
		if (fields.show_school) {
			html += '<label class="airb__field airb__field--full"><span class="airb__label">' + esc(labels.school || 'School / trust name') + '</span>';
			html += '<input class="airb__input" type="text" name="interest_school" autocomplete="organization" value="' + esc(state.school || '') + '" placeholder="Riverside Academy"></label>';
		}
		html += '<label class="airb__field airb__field--full"><span class="airb__label">' + esc(labels.message || 'Anything else we should know?') + '</span>';
		html += '<textarea class="airb__input airb__textarea" name="interest_message" rows="3" placeholder="' + esc(messagePlaceholder) + '"></textarea></label>';
		html += '</div>';

		html += '<p class="airb__interest-status" id="airb-interest-status" role="status" aria-live="polite" hidden></p>';
		html += '<button type="submit" class="airb__btn airb__btn--primary airb__interest-submit airb__teacher-follow-up-submit">';
		html += esc(labels.submit_short || 'Request follow-up');
		html += '<svg class="airb__teacher-follow-up-submit-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>';
		html += '</button>';
		html += '</form></section>';
		return html;
	}

	function leaderDashboardFollowUpHtml(r, form, labels, fields, suggested) {
		var demoOptionSlugs = ['whole_school_benchmark', 'governance_review', 'whole_school_cpd'];
		var options = (form.options || []).filter(function (opt) {
			return demoOptionSlugs.indexOf(opt.slug) >= 0;
		});
		options.sort(function (a, b) {
			return demoOptionSlugs.indexOf(a.slug) - demoOptionSlugs.indexOf(b.slug);
		});
		if (!options.length) {
			options = (form.options || []).slice(0, 3);
		}

		var weakestLabel = '';
		if (state.dashboardModel && state.dashboardModel.domains && state.dashboardModel.domains.length) {
			var weakest = state.dashboardModel.domains.reduce(function (min, domain) {
				return !min || domain.value < min.value ? domain : min;
			}, null);
			if (weakest) weakestLabel = weakest.label.toLowerCase();
		}
		var messagePlaceholder = weakestLabel
			? 'Example: We need help strengthening ' + weakestLabel + ' before our next governor meeting.'
			: 'Example: We need a whole-school benchmark rollout plan.';

		var html = '<section class="airb__interest airb__teacher-dash-follow-up" id="airb-interest" aria-labelledby="airb-interest-heading">';
		html += '<span id="benchmark-follow-up" class="airb__sr-only" aria-hidden="true"></span>';
		html += '<div class="airb__teacher-follow-up-head">';
		html += '<span class="airb__teacher-follow-up-icon" aria-hidden="true">';
		html += '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
		html += '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>';
		html += '</svg></span>';
		html += '<div class="airb__teacher-follow-up-copy">';
		html += '<p class="airb__teacher-follow-up-eyebrow">Optional next step</p>';
		html += '<h2 class="airb__interest-heading" id="airb-interest-heading">Want help with your next step?</h2>';
		html += '<p class="airb__interest-intro">' + esc(
			labels.intro || 'You have seen your results — tell us if you would like governance support, whole-school CPD, or a benchmark rollout.'
		) + '</p>';
		html += '</div></div>';

		html += '<form class="airb__interest-form airb__teacher-follow-up-form" id="airb-interest-form" novalidate>';
		html += '<fieldset class="airb__interest-options airb__teacher-follow-up-options">';
		html += '<legend class="airb__interest-legend airb__sr-only">' + esc(labels.interests || 'What would you like?') + '</legend>';
		options.forEach(function (opt) {
			var checked = suggested.indexOf(opt.slug) >= 0;
			if (!checked && opt.slug === 'governance_review' && !suggested.length) {
				checked = true;
			}
			var inputId = 'airb-interest-' + opt.slug;
			html += '<label class="airb__interest-option" for="' + esc(inputId) + '">';
			html += '<input type="checkbox" id="' + esc(inputId) + '" name="interests[]" value="' + esc(opt.slug) + '"' + (checked ? ' checked' : '') + '>';
			html += '<span class="airb__interest-option-text">';
			html += '<span class="airb__interest-option-label">' + esc(opt.label) + '</span>';
			if (opt.description) {
				html += '<span class="airb__interest-option-desc">' + esc(opt.description) + '</span>';
			}
			html += '</span></label>';
		});
		html += '</fieldset>';

		html += '<div class="airb__interest-fields airb__teacher-follow-up-fields">';
		if (fields.show_name) {
			html += '<label class="airb__field"><span class="airb__label">' + esc(labels.name || 'Your name') + '</span>';
			html += '<input class="airb__input" type="text" name="interest_name" autocomplete="name" placeholder="Alex Morgan"></label>';
		}
		if (fields.show_email) {
			html += '<label class="airb__field"><span class="airb__label">' + esc(labels.email || 'Email address') + (fields.email_required ? ' *' : '') + '</span>';
			html += '<input class="airb__input" type="email" name="interest_email"' + (fields.email_required ? ' required' : '') + ' autocomplete="email" value="' + esc(state.email || '') + '">';
			if (labels.email_hint) html += '<span class="airb__field-hint airb__muted">' + esc(labels.email_hint) + '</span>';
			html += '</label>';
		}
		if (fields.show_school) {
			html += '<label class="airb__field airb__field--full"><span class="airb__label">' + esc(labels.school || 'School / trust name') + '</span>';
			html += '<input class="airb__input" type="text" name="interest_school" autocomplete="organization" value="' + esc(state.school || '') + '" placeholder="Riverside Academy"></label>';
		}
		if (fields.show_stakeholder_role && form.stakeholder_roles) {
			html += stakeholderRoleFieldHtml(labels, form.stakeholder_roles);
		}
		html += '<label class="airb__field airb__field--full"><span class="airb__label">' + esc(labels.message || 'Anything else we should know?') + '</span>';
		html += '<textarea class="airb__input airb__textarea" name="interest_message" rows="3" placeholder="' + esc(messagePlaceholder) + '"></textarea></label>';
		html += '</div>';

		html += '<p class="airb__interest-status" id="airb-interest-status" role="status" aria-live="polite" hidden></p>';
		html += '<button type="submit" class="airb__btn airb__btn--primary airb__interest-submit airb__teacher-follow-up-submit">';
		html += esc(labels.submit_short || 'Request follow-up');
		html += '<svg class="airb__teacher-follow-up-submit-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>';
		html += '</button>';
		html += '</form></section>';
		return html;
	}

	function studentDashboardFollowUpHtml(r, form, labels, fields, suggested) {
		var demoOptionSlugs = ['student_share_school', 'student_school_programme', 'student_learn_ai'];
		var options = (form.options || []).filter(function (opt) {
			return demoOptionSlugs.indexOf(opt.slug) >= 0;
		});
		options.sort(function (a, b) {
			return demoOptionSlugs.indexOf(a.slug) - demoOptionSlugs.indexOf(b.slug);
		});
		if (!options.length) {
			options = (form.options || []).slice(0, 3);
		}

		var weakestLabel = '';
		if (state.dashboardModel && state.dashboardModel.domains && state.dashboardModel.domains.length) {
			var weakest = state.dashboardModel.domains.reduce(function (min, domain) {
				return !min || domain.value < min.value ? domain : min;
			}, null);
			if (weakest) weakestLabel = weakest.label.toLowerCase();
		}
		var messagePlaceholder = weakestLabel
			? 'Example: I want help improving my ' + weakestLabel + ' habits.'
			: 'Example: I want help sharing my results with my school.';

		var html = '<section class="airb__interest airb__teacher-dash-follow-up" id="airb-interest" aria-labelledby="airb-interest-heading">';
		html += '<span id="benchmark-follow-up" class="airb__sr-only" aria-hidden="true"></span>';
		html += '<div class="airb__teacher-follow-up-head">';
		html += '<span class="airb__teacher-follow-up-icon" aria-hidden="true">';
		html += '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
		html += '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>';
		html += '</svg></span>';
		html += '<div class="airb__teacher-follow-up-copy">';
		html += '<p class="airb__teacher-follow-up-eyebrow">Optional next step</p>';
		html += '<h2 class="airb__interest-heading" id="airb-interest-heading">Want help with your next step?</h2>';
		html += '<p class="airb__interest-intro">' + esc(
			labels.intro || 'Your results help your school understand how students use AI. Tell us if you want to share your results, learn more, or ask about AI Awareness Day.'
		) + '</p>';
		html += '</div></div>';

		html += '<form class="airb__interest-form airb__teacher-follow-up-form" id="airb-interest-form" novalidate>';
		html += '<fieldset class="airb__interest-options airb__teacher-follow-up-options">';
		html += '<legend class="airb__interest-legend airb__sr-only">' + esc(labels.interests || 'What would you like?') + '</legend>';
		options.forEach(function (opt) {
			var checked = suggested.indexOf(opt.slug) >= 0;
			if (!checked && opt.slug === 'student_share_school' && !suggested.length) {
				checked = true;
			}
			var inputId = 'airb-interest-' + opt.slug;
			html += '<label class="airb__interest-option" for="' + esc(inputId) + '">';
			html += '<input type="checkbox" id="' + esc(inputId) + '" name="interests[]" value="' + esc(opt.slug) + '"' + (checked ? ' checked' : '') + '>';
			html += '<span class="airb__interest-option-text">';
			html += '<span class="airb__interest-option-label">' + esc(opt.label) + '</span>';
			if (opt.description) {
				html += '<span class="airb__interest-option-desc">' + esc(opt.description) + '</span>';
			}
			html += '</span></label>';
		});
		html += '</fieldset>';

		html += '<div class="airb__interest-fields airb__teacher-follow-up-fields">';
		if (fields.show_name) {
			html += '<label class="airb__field"><span class="airb__label">' + esc(labels.name || 'Your name') + '</span>';
			html += '<input class="airb__input" type="text" name="interest_name" autocomplete="name" placeholder="Alex Morgan"></label>';
		}
		if (fields.show_email) {
			html += '<label class="airb__field"><span class="airb__label">' + esc(labels.email || 'Email address') + (fields.email_required ? ' *' : '') + '</span>';
			html += '<input class="airb__input" type="email" name="interest_email"' + (fields.email_required ? ' required' : '') + ' autocomplete="email" value="' + esc(state.email || '') + '">';
			if (labels.email_hint) html += '<span class="airb__field-hint airb__muted">' + esc(labels.email_hint) + '</span>';
			html += '</label>';
		}
		if (fields.show_school) {
			html += '<label class="airb__field airb__field--full"><span class="airb__label">' + esc(labels.school || 'Your school (optional)') + '</span>';
			html += '<input class="airb__input" type="text" name="interest_school" autocomplete="organization" value="' + esc(state.school || '') + '" placeholder="Riverside Academy"></label>';
		}
		html += '<label class="airb__field airb__field--full"><span class="airb__label">' + esc(labels.message || 'Anything else we should know?') + '</span>';
		html += '<textarea class="airb__input airb__textarea" name="interest_message" rows="3" placeholder="' + esc(messagePlaceholder) + '"></textarea></label>';
		html += '</div>';

		html += '<p class="airb__interest-status" id="airb-interest-status" role="status" aria-live="polite" hidden></p>';
		html += '<button type="submit" class="airb__btn airb__btn--primary airb__interest-submit airb__teacher-follow-up-submit">';
		html += esc(labels.submit_short || 'Request follow-up');
		html += '<svg class="airb__teacher-follow-up-submit-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>';
		html += '</button>';
		html += '</form></section>';
		return html;
	}

	function teacherDashboardFollowUpHtml(r, form, labels, fields, suggested) {
		var demoOptionSlugs = ['whole_school_cpd', 'teacher_activity_day', 'whole_school_benchmark'];
		var options = (form.options || []).filter(function (opt) {
			return demoOptionSlugs.indexOf(opt.slug) >= 0;
		});
		options.sort(function (a, b) {
			return demoOptionSlugs.indexOf(a.slug) - demoOptionSlugs.indexOf(b.slug);
		});
		if (!options.length) {
			options = (form.options || []).slice();
		}

		var html = '<section class="airb__interest airb__teacher-dash-follow-up" id="airb-interest" aria-labelledby="airb-interest-heading">';
		html += '<span id="benchmark-follow-up" class="airb__sr-only" aria-hidden="true"></span>';
		html += '<div class="airb__teacher-follow-up-head">';
		html += '<span class="airb__teacher-follow-up-icon" aria-hidden="true">';
		html += '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
		html += '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>';
		html += '</svg></span>';
		html += '<div class="airb__teacher-follow-up-copy">';
		html += '<p class="airb__teacher-follow-up-eyebrow">Optional next step</p>';
		html += '<h2 class="airb__interest-heading" id="airb-interest-heading">Want help with your next step?</h2>';
		html += '<p class="airb__interest-intro">You have seen your results — tell us if you would like CPD, a literacy display board, or a whole-school rollout.</p>';
		html += '</div></div>';

		html += '<form class="airb__interest-form airb__teacher-follow-up-form" id="airb-interest-form" novalidate>';
		html += '<fieldset class="airb__interest-options airb__teacher-follow-up-options">';
		html += '<legend class="airb__interest-legend airb__sr-only">' + esc(labels.interests || 'What would you like?') + '</legend>';
		options.forEach(function (opt) {
			var checked = suggested.indexOf(opt.slug) >= 0;
			if (!checked && opt.slug === 'whole_school_cpd' && !suggested.length) {
				checked = true;
			}
			var inputId = 'airb-interest-' + opt.slug;
			html += '<label class="airb__interest-option" for="' + esc(inputId) + '">';
			html += '<input type="checkbox" id="' + esc(inputId) + '" name="interests[]" value="' + esc(opt.slug) + '"' + (checked ? ' checked' : '') + '>';
			html += '<span class="airb__interest-option-text">';
			html += '<span class="airb__interest-option-label">' + esc(opt.label) + '</span>';
			if (opt.description) {
				html += '<span class="airb__interest-option-desc">' + esc(opt.description) + '</span>';
			}
			html += '</span></label>';
		});
		html += '</fieldset>';

		html += '<div class="airb__interest-fields airb__teacher-follow-up-fields">';
		if (fields.show_name) {
			html += '<label class="airb__field"><span class="airb__label">' + esc(labels.name || 'Your name') + '</span>';
			html += '<input class="airb__input" type="text" name="interest_name" autocomplete="name" placeholder="Alex Morgan"></label>';
		}
		if (fields.show_email) {
			html += '<label class="airb__field"><span class="airb__label">' + esc(labels.email || 'Email address') + (fields.email_required ? ' *' : '') + '</span>';
			html += '<input class="airb__input" type="email" name="interest_email"' + (fields.email_required ? ' required' : '') + ' autocomplete="email" value="' + esc(state.email || '') + '">';
			if (labels.email_hint) html += '<span class="airb__field-hint airb__muted">' + esc(labels.email_hint) + '</span>';
			html += '</label>';
		}
		if (fields.show_school) {
			html += '<label class="airb__field airb__field--full"><span class="airb__label">' + esc(labels.school || 'School / trust name') + '</span>';
			html += '<input class="airb__input" type="text" name="interest_school" autocomplete="organization" value="' + esc(state.school || '') + '" placeholder="Riverside Academy"></label>';
		}
		if (fields.show_stakeholder_role && form.stakeholder_roles) {
			html += stakeholderRoleFieldHtml(labels, form.stakeholder_roles);
		}
		if (fields.show_child_school) {
			html += '<label class="airb__field airb__field--full"><span class="airb__label">' + esc(labels.child_school || '') + '</span>';
			html += '<input class="airb__input" type="text" name="interest_child_school" autocomplete="organization"></label>';
		}
		html += '<label class="airb__field airb__field--full"><span class="airb__label">' + esc(labels.message || 'Anything else we should know?') + '</span>';
		html += '<textarea class="airb__input airb__textarea" name="interest_message" rows="3" placeholder="Example: We need help with assessment design across Year 8 lessons."></textarea></label>';
		html += '</div>';

		html += '<p class="airb__interest-status" id="airb-interest-status" role="status" aria-live="polite" hidden></p>';
		html += '<button type="submit" class="airb__btn airb__btn--primary airb__interest-submit airb__teacher-follow-up-submit">';
		html += esc(labels.submit_short || 'Request follow-up');
		html += '<svg class="airb__teacher-follow-up-submit-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>';
		html += '</button>';
		html += '</form></section>';
		return html;
	}

	function stakeholderRoleFieldHtml(labels, stakeholderRoles) {
		if (!stakeholderRoles || !Object.keys(stakeholderRoles).length) return '';
		var datalistId = 'airb-stakeholder-role-options';
		var html = '<label class="airb__field airb__field--full airb__teacher-follow-up-stakeholder">';
		html += '<span class="airb__label">' + esc(labels.stakeholder_role || 'Job title') + '</span>';
		html += '<input class="airb__input" type="text" id="airb-stakeholder-role" name="stakeholder_role" list="' + esc(datalistId) + '" autocomplete="organization-title" placeholder="e.g. Teacher, Head of Department, SENCO">';
		html += '<datalist id="' + esc(datalistId) + '">';
		Object.keys(stakeholderRoles).forEach(function (key) {
			html += '<option value="' + esc(stakeholderRoles[key]) + '"></option>';
		});
		html += '</datalist>';
		html += '</label>';
		return html;
	}

	function interestFormHtml(r) {
		if (!hasInterestForm(r)) return '';
		var form = r.interest_form;
		var labels = form.labels || {};
		var fields = form.fields || {};
		var suggested = form.suggested || [];
		var weak = form.weak_domains || [];
		var summary = form.summary || {};
		var teacherDashFollowUp = isTeacherRole() && state.dashboardModel;
		var studentDashFollowUp = isStudentRole() && state.dashboardModel;
		var parentDashFollowUp = isParentRole() && state.dashboardModel;
		var leaderDashFollowUp = isLeaderRole() && state.dashboardModel;
		var supportDashFollowUp = isSupportStaffRole() && state.dashboardModel;

		if (teacherDashFollowUp) {
			return teacherDashboardFollowUpHtml(r, form, labels, fields, suggested);
		}
		if (studentDashFollowUp) {
			return studentDashboardFollowUpHtml(r, form, labels, fields, suggested);
		}
		if (parentDashFollowUp) {
			return parentDashboardFollowUpHtml(r, form, labels, fields, suggested);
		}
		if (leaderDashFollowUp) {
			return leaderDashboardFollowUpHtml(r, form, labels, fields, suggested);
		}
		if (supportDashFollowUp) {
			return supportDashboardFollowUpHtml(r, form, labels, fields, suggested);
		}

		var html = '<section class="airb__interest" id="airb-interest" aria-labelledby="airb-interest-heading">';
		html += '<h3 class="airb__interest-heading" id="airb-interest-heading">' + esc(labels.heading || '') + '</h3>';
		html += gatewayInfoHtml(r);
		if (labels.intro) {
			html += '<p class="airb__muted airb__interest-intro">' + esc(labels.intro) + '</p>';
		}

		html += '<div class="airb__interest-summary" aria-live="polite">';
		html += '<p><strong>' + esc(labels.score_label || '') + ':</strong> ' + esc(String(summary.score != null ? summary.score : (r.alignment_score != null ? r.alignment_score : '—'))) + '%';
		if (summary.readiness_label) html += ' · ' + esc(summary.readiness_label);
		html += '</p>';
		if (weak.length) {
			html += '<p><strong>' + esc(labels.weak_label || '') + ':</strong> ' + esc(weak.join('; ')) + '</p>';
		}
		if (i18n.alignmentDisclaimer) {
			html += '<p class="airb__interest-disclaimer airb__muted">' + esc(i18n.alignmentDisclaimer) + '</p>';
		}
		html += '</div>';

		html += '<form class="airb__interest-form" id="airb-interest-form" novalidate>';
		html += '<fieldset class="airb__interest-options"><legend class="airb__interest-legend">' + esc(labels.interests || '') + '</legend>';
		(form.options || []).forEach(function (opt) {
			var checked = suggested.indexOf(opt.slug) >= 0 ? ' checked' : '';
			var inputId = 'airb-interest-' + opt.slug;
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
			html += '<label class="airb__field"><span class="airb__label">' + esc(labels.email || '') + (fields.email_required ? ' *' : '') + '</span>';
			html += '<input class="airb__input" type="email" name="interest_email"' + (fields.email_required ? ' required' : '') + ' autocomplete="email" value="' + esc(state.email || '') + '">';
			if (labels.email_hint) html += '<span class="airb__field-hint airb__muted">' + esc(labels.email_hint) + '</span>';
			html += '</label>';
		}
		if (fields.show_school) {
			html += '<label class="airb__field"><span class="airb__label">' + esc(labels.school || '') + '</span><input class="airb__input" type="text" name="interest_school" autocomplete="organization" value="' + esc(state.school || '') + '"></label>';
		}
		if (fields.show_stakeholder_role && form.stakeholder_roles) {
			html += stakeholderRoleFieldHtml(labels, form.stakeholder_roles);
		}
		if (fields.show_child_school) {
			html += '<label class="airb__field"><span class="airb__label">' + esc(labels.child_school || '') + '</span><input class="airb__input" type="text" name="interest_child_school" autocomplete="organization"></label>';
		}
		html += '<label class="airb__field"><span class="airb__label">' + esc(labels.message || '') + '</span><textarea class="airb__input airb__textarea" name="interest_message" rows="3"></textarea></label>';
		html += '</div>';

		html += '<p class="airb__interest-status" id="airb-interest-status" role="status" aria-live="polite" hidden></p>';
		html += '<button type="submit" class="airb__btn airb__btn--primary airb__interest-submit">' + esc(labels.submit || 'Send') + '</button>';
		html += '</form></section>';

		if (isStudentRole()) {
			return '<details class="airb__results-form-optional">' +
				'<summary>' + esc(i18n.shareWithSchool || 'Share with your school (optional)') + '</summary>' +
				html + '</details>';
		}
		return html;
	}

	function scrollToInterestForm(prefill) {
		if (!ensureInterestFormRendered()) {
			window.alert(i18n.interestFormScrollError || 'The request form could not be opened. Refresh the page and try again.');
			return;
		}
		var section = document.getElementById('airb-interest');
		if (!section) {
			window.alert(i18n.interestFormScrollError || 'The request form could not be opened. Refresh the page and try again.');
			return;
		}
		var details = section.closest('details');
		if (details) details.open = true;
		prefill = resolveInterestPrefill(prefill);
		if (prefill) {
			section.querySelectorAll('input[name="interests[]"]').forEach(function (input) {
				input.checked = input.value === prefill;
			});
		}
		section.classList.add('is-targeted');
		setTimeout(function () { section.classList.remove('is-targeted'); }, 2400);
		section.scrollIntoView({ behavior: 'smooth', block: 'start' });
		var focusTarget = section.querySelector('input[name="interests[]"]:checked') || section.querySelector('input[name="interest_email"]') || section.querySelector('.airb__interest-submit');
		if (focusTarget && focusTarget.focus) {
			focusTarget.focus({ preventScroll: true });
		}
		trackEvent('interest_form_open', { prefill: prefill || '', role: state.role || '' });
	}

	function bindStudentRetakeTriggers() {
		el.results.querySelectorAll('[data-airb-student-retake]').forEach(function (btn) {
			btn.addEventListener('click', function (e) {
				e.preventDefault();
				var mode = btn.getAttribute('data-airb-student-retake') || '';
				restartStudentAudit(mode === 'school');
			});
		});
	}

	function bindInterestTriggers() {
		el.results.querySelectorAll('[data-airb-scroll-interest]').forEach(function (btn) {
			btn.addEventListener('click', function (e) {
				e.preventDefault();
				scrollToInterestForm('');
			});
		});
		el.results.querySelectorAll('[data-airb-open-interest]').forEach(function (btn) {
			btn.addEventListener('click', function (e) {
				e.preventDefault();
				scrollToInterestForm(btn.getAttribute('data-airb-open-interest') || '');
			});
		});
		el.results.querySelectorAll('a[href^="#airb-interest"]').forEach(function (link) {
			link.addEventListener('click', function (e) {
				e.preventDefault();
				scrollToInterestForm(interestPrefillFromUrl(link.getAttribute('href') || ''));
			});
		});
	}

	function bindInterestForm() {
		var form = document.getElementById('airb-interest-form');
		if (!form) return;
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			submitInterestForm(form);
		});
	}

	function submitInterestForm(form) {
		var statusEl = document.getElementById('airb-interest-status');
		var r = state.results;
		if (!r) return;

		var emailInput = form.querySelector('input[name="interest_email"]');
		var email = emailInput ? emailInput.value.trim() : '';
		if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
			if (statusEl) {
				statusEl.hidden = false;
				statusEl.className = 'airb__interest-status airb__interest-status--error';
				statusEl.textContent = i18n.emailInvalid || 'Please enter a valid email address.';
			}
			if (emailInput) emailInput.focus();
			return;
		}

		var interests = [];
		form.querySelectorAll('input[name="interests[]"]:checked').forEach(function (input) {
			interests.push(input.value);
		});
		if (!interests.length) {
			if (statusEl) {
				statusEl.hidden = false;
				statusEl.className = 'airb__interest-status airb__interest-status--error';
				statusEl.textContent = i18n.interestRequired || 'Please select at least one option.';
			}
			return;
		}

		var submitBtn = form.querySelector('.airb__interest-submit');
		if (submitBtn) {
			submitBtn.disabled = true;
			submitBtn.textContent = i18n.saving || 'Sending…';
		}
		if (statusEl) statusEl.hidden = true;

		var body = new FormData();
		body.append('action', 'airb_submit_interest');
		body.append('nonce', airbBenchmark.nonce);
		body.append('role', state.role);
		body.append('session_id', state.sessionId || '');
		body.append('submission_id', String(state.submissionId || 0));
		body.append('alignment_score', String(r.alignment_score != null ? r.alignment_score : 0));
		body.append('risk_level', r.risk_level || '');
		body.append('name', (form.querySelector('input[name="interest_name"]') || {}).value || '');
		body.append('email', email);
		body.append('school', (form.querySelector('input[name="interest_school"]') || {}).value || state.school || '');
		body.append('child_school', (form.querySelector('input[name="interest_child_school"]') || {}).value || '');
		body.append('message', (form.querySelector('textarea[name="interest_message"]') || {}).value || '');
		body.append('risk_level', r.risk_level || '');
		body.append('risk_level_label', r.risk_level_label || '');
		body.append('readiness_level_label', r.readiness_level_label || '');
		body.append('year_group', state.yearGroup || '');
		var stakeholderValue = ((form.querySelector('input[name="stakeholder_role"]') || {}).value || '').trim();
		body.append('stakeholder_role', stakeholderValue);
		body.append('interests', JSON.stringify(interests));
		body.append('weak_domains', JSON.stringify((r.interest_form && r.interest_form.weak_domains) || []));

		fetch(airbBenchmark.ajaxurl, { method: 'POST', body: body, credentials: 'same-origin' })
			.then(function (res) { return res.json(); })
			.then(function (json) {
				if (submitBtn) {
					submitBtn.disabled = false;
					submitBtn.textContent = (r.interest_form && r.interest_form.labels && r.interest_form.labels.submit) || i18n.submit || 'Send';
				}
				if (!json || !json.success) {
					if (statusEl) {
						statusEl.hidden = false;
						statusEl.className = 'airb__interest-status airb__interest-status--error';
						statusEl.textContent = (json && json.data && json.data.message) || i18n.error || 'Something went wrong.';
					}
					return;
				}
				if (statusEl) {
					statusEl.hidden = false;
					statusEl.className = 'airb__interest-status airb__interest-status--success';
					statusEl.textContent = (json.data && json.data.message) || (r.interest_form && r.interest_form.labels && r.interest_form.labels.success) || 'Thank you.';
				}
				form.querySelectorAll('input[name="interests[]"], input[name="interest_name"], input[name="interest_school"], input[name="interest_child_school"], input[name="stakeholder_role"], textarea[name="interest_message"]').forEach(function (el) {
					if (el.type === 'checkbox') el.checked = false;
					else el.value = '';
				});
			})
			.catch(function () {
				if (submitBtn) {
					submitBtn.disabled = false;
					submitBtn.textContent = (r.interest_form && r.interest_form.labels && r.interest_form.labels.submit) || 'Send';
				}
				if (statusEl) {
					statusEl.hidden = false;
					statusEl.className = 'airb__interest-status airb__interest-status--error';
					statusEl.textContent = i18n.error || 'Something went wrong.';
				}
			});
	}

	function buildReportRequestMailto() {
		var r = state.results;
		var to = airbBenchmark.contactEmail || '';
		if (!r || !to || !isStaffRole()) return '';

		var roleLbl = (cfg.roles || {})[state.role] || state.role;
		var lines = [
			i18n.reportEmailIntro,
			'',
			i18n.reportEmailRole + ': ' + roleLbl,
			i18n.alignment + ': ' + (r.alignment_score != null ? r.alignment_score : '—') + '/100',
			(i18n.readinessLevel || 'Readiness level') + ': ' + (r.readiness_level_label || '—'),
			i18n.riskLevel + ': ' + (r.risk_level_label || '—'),
		];

		if (r.dependency_index != null) {
			lines.push(i18n.dependency + ': ' + r.dependency_index + '%');
		}
		if (state.school) {
			lines.push(i18n.schoolOptional.replace(/\s*\([^)]*\)\s*/g, '').trim() + ': ' + state.school);
		}
		if (state.email) {
			lines.push('Email: ' + state.email);
		}
		lines.push('', i18n.reportEmailClosing);

		return 'mailto:' + to
			+ '?subject=' + encodeURIComponent(i18n.reportEmailSubject)
			+ '&body=' + encodeURIComponent(lines.join('\n'));
	}

	function buildShareScoreText(r) {
		if (!r) return '';

		var roleLbl = (cfg.roles || {})[state.role] || state.role;
		var score = r.alignment_score;
		var scoreStr = score != null
			? (isParentRole() ? score + '%' : score + '/100')
			: '—';
		var readiness = r.readiness_level_label || '';
		var parts = [];

		parts.push(
			(i18n.shareCopyIntro || 'I scored {score} on the AI Risk & Readiness Benchmark as a {role}{readiness}.')
				.replace('{score}', scoreStr)
				.replace('{role}', roleLbl)
				.replace('{readiness}', readiness ? ' (' + readiness + ')' : '')
		);

		var b = r.benchmark;
		if (b && typeof b.average === 'number') {
			parts.push(
				(i18n.shareCopyBenchmarkAvg || 'National average for {role}s: {avg}/100.')
					.replace('{role}', roleLbl)
					.replace('{avg}', String(b.average))
			);
			if (typeof b.percentile === 'number') {
				parts.push(
					(i18n.shareCopyBenchmarkPercentile || 'That puts me ahead of {percentile}% of participants.')
						.replace('{percentile}', String(b.percentile))
				);
			}
		}

		var url = (window.location.href || '').split('#')[0];
		parts.push(
			(i18n.shareCopyTry || 'Try it: {url}').replace('{url}', url)
		);

		return parts.join(' ');
	}

	function buildShareResultsMailto() {
		var r = state.results;
		if (!r) return '';

		var roleLbl = (cfg.roles || {})[state.role] || state.role;
		var intro = i18n.shareEmailIntro || 'Hello, I completed the free AI Risk & Readiness Benchmark and wanted to share my results with the school.';
		var closing = i18n.shareEmailClosing || 'Please share this with the relevant teacher or school leader so our school can build the whole-school picture.';
		if (isTeacherRole()) {
			intro = i18n.shareEmailIntroTeacher || intro;
			closing = i18n.shareEmailClosingTeacher || closing;
		} else if (isLeaderRole()) {
			intro = i18n.shareEmailIntroLeader || intro;
			closing = i18n.shareEmailClosingLeader || closing;
		} else if (isStudentRole()) {
			intro = i18n.shareEmailIntroStudent || intro;
			closing = i18n.shareEmailClosingStudent || closing;
		} else if (isParentRole()) {
			intro = i18n.shareEmailIntroParent || intro;
			closing = i18n.shareEmailClosingParent || closing;
		}
		var lines = [
			intro,
			'',
			(i18n.reportEmailRole || 'Role') + ': ' + roleLbl,
			(i18n.readinessLevel || 'Readiness level') + ': ' + (r.readiness_level_label || '—'),
		];
		if (isParentRole()) {
			lines.push((i18n.statReadiness || 'Readiness score') + ': ' + (r.alignment_score != null ? r.alignment_score : '—') + '%');
		} else {
			lines.push(i18n.alignment + ': ' + (r.alignment_score != null ? r.alignment_score : '—') + '/100');
		}
		lines.push(i18n.riskLevel + ': ' + (r.risk_level_label || '—'));

		if (r.dependency_index != null) {
			lines.push(i18n.dependency + ': ' + r.dependency_index + '%');
		}
		if (state.yearGroup) {
			var yg = (i18n.yearGroups || {})[state.yearGroup] || state.yearGroup;
			lines.push((i18n.yearGroupParent || i18n.yearGroup || 'Year group') + ': ' + yg);
		}
		lines.push('', closing);

		return 'mailto:?subject=' + encodeURIComponent(i18n.shareEmailSubject || 'AI Risk Benchmark results to share with school')
			+ '&body=' + encodeURIComponent(lines.join('\n'));
	}

	function emailReport() {
		if (!state.email || !state.results) return;
		var body = new FormData();
		body.append('action', 'airb_email_report');
		body.append('nonce', airbBenchmark.nonce);
		body.append('email', state.email);
		body.append('role', state.role);
		body.append('results', JSON.stringify(state.results));

		fetch(airbBenchmark.ajaxurl, { method: 'POST', body: body, credentials: 'same-origin' })
			.then(function (res) { return res.json(); })
			.then(function (json) {
				if (json.success) {
					trackEvent('email_report', { email: state.email });
					showError(i18n.emailed);
				} else showError((json.data && json.data.message) || i18n.error);
			})
			.catch(function () { showError(i18n.error); });
	}

	function decodeHtmlEntities(str) {
		var text = String(str == null ? '' : str);
		if (!text || text.indexOf('&') === -1) return text;
		var el = document.createElement('textarea');
		el.innerHTML = text;
		return el.value;
	}

	function escText(str) {
		return esc(decodeHtmlEntities(str));
	}

	function esc(str) {
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	if (el.next) el.next.addEventListener('click', goNext);
	if (el.back) el.back.addEventListener('click', goBack);
	bindRoleCardActions();
	if (el.role) {
		collapseIntro();
		renderRole();
		if (el.root) {
			el.root.classList.add('airb--role-ready');
		}
	}

	// App-bar completion pill: reflect how many of the 4 audits this device has done.
	function updateAppbarCompletions() {
		var root = document.getElementById('airb-benchmark');
		var status = root && root.querySelector('.airb__appbar-status');
		if (!status) return;
		var done = Object.keys(loadRoleCompletions()).length;
		var total = Object.keys(cfg.roles || {}).length || 4;
		if (done > 0) {
			status.innerHTML = '<span class="airb__appbar-dot" aria-hidden="true"></span>'
				+ '<span class="airb__appbar-count">' + done + ' / ' + total + '</span> '
				+ esc(i18n.auditsComplete || 'audits complete');
		}
	}
	updateAppbarCompletions();
	renderDemoGauge();

	function schoolSnapshotHtml(rollup, opts) {
		opts = opts || {};
		var compact = !!opts.compact;
		var alignmentLabel = rollup.alignment_score_label || i18n.dfeAlignment || i18n.alignment || 'DfE Readiness Alignment';
		var groupsText = (i18n.schoolSnapshotGroups || '{complete} of {total} stakeholder groups complete')
			.replace('{complete}', rollup.roles_complete)
			.replace('{total}', rollup.roles_total);

		var html = '<div class="airb__school-results' + (compact ? ' airb__school-results--compact' : '') + '">';
		html += '<h3 class="airb__panel-title">' + esc(i18n.schoolSnapshotTitle || 'Whole-School AI Readiness Snapshot') + '</h3>';
		html += '<p class="airb__muted airb__school-snapshot-sub">' + esc(rollup.school_name) + ' · ' + esc(groupsText) + '</p>';
		html += '<div class="airb__role-bars">';
		Object.keys(rollup.roles || {}).forEach(function (slug) {
			var d = rollup.roles[slug];
			html += '<div class="airb__role-bar' + (d.readiness == null ? ' is-missing' : '') + '">';
			html += '<span class="airb__role-bar-label">' + esc(d.label) + '</span>';
			if (d.readiness != null) {
				html += '<div class="airb__bar-track"><div class="airb__bar-fill" style="width:' + d.readiness + '%"></div></div>';
				html += '<span class="airb__role-bar-val">' + d.readiness + '%';
				if (d.readiness_band_label) html += ' <span class="airb__role-bar-band">' + esc(d.readiness_band_label) + '</span>';
				html += '</span>';
			} else {
				html += '<span class="airb__role-bar-val airb__muted">' + esc(i18n.awaitingAudit || 'Awaiting audit') + '</span>';
			}
			html += '</div>';
		});
		html += '</div>';
		html += '<div class="airb__cards">';
		html += card({
			label: alignmentLabel,
			value: rollup.overall_alignment + '%',
			band: readinessBand(rollup.overall_alignment),
			tone: 'readiness',
			band_label: rollup.overall_readiness_band || readinessBandLabel(rollup.overall_alignment),
		});
		html += card({
			label: i18n.riskLevel || 'Risk level',
			value: rollup.overall_risk_label,
			band: rollup.overall_risk_level,
			tone: 'risk',
			band_label: rollup.overall_risk_label,
		});
		html += '</div>';
		if (rollup.alignment_disclaimer) {
			html += '<p class="airb__muted airb__alignment-disclaimer">' + esc(rollup.alignment_disclaimer) + '</p>';
		}
		if (rollup.key_exposure_areas && rollup.key_exposure_areas.length) {
			html += '<h4>' + esc(i18n.schoolHighestRisk || 'Highest risk areas') + '</h4>' + exposureCardsHtml(rollup.key_exposure_areas);
		}
		if (rollup.recommended_priorities && rollup.recommended_priorities.length) {
			html += '<h4>' + esc(i18n.schoolPriorities || 'Recommended school priorities') + '</h4><ol class="airb__school-priorities">';
			rollup.recommended_priorities.forEach(function (item) {
				html += '<li>' + esc(item) + '</li>';
			});
			html += '</ol>';
		}
		if (rollup.exposure_breakdown && rollup.exposure_breakdown.length) {
			html += '<details class="airb__school-exposure-details"><summary>' + esc(i18n.schoolExposureDetails || 'Full exposure breakdown') + '</summary>';
			html += '<table class="airb__exposure-table"><tbody>';
			rollup.exposure_breakdown.forEach(function (row) {
				html += '<tr><td>' + esc(row.label) + '</td><td><span class="airb__exposure-pill airb__exposure-pill--' + esc((row.band_label || 'low').toLowerCase()) + '">' + esc(row.band_label) + '</span></td></tr>';
			});
			html += '</tbody></table></details>';
		}
		if (rollup.roles_complete < rollup.roles_total) {
			html += '<p class="airb__muted airb__school-snapshot-incomplete">' + esc(i18n.schoolSnapshotIncomplete || 'Roll out the benchmark to all five stakeholder groups for a complete whole-school picture.') + '</p>';
		}
		if (compact) {
			html += '<p class="airb__school-link"><a class="airb__btn airb__btn--ghost airb__btn--sm" href="?school=' + encodeURIComponent(rollup.school_name) + '#airb-school-dashboard">' + esc(i18n.viewSchool || 'View school-wide dashboard') + '</a></p>';
		}
		html += '</div>';
		return html;
	}

	function fetchSchoolSnapshot() {
		var mount = document.getElementById('airb-school-snapshot');
		if (!mount || !state.school || !window.airbBenchmark) return;

		mount.hidden = false;
		mount.innerHTML = '<p class="airb__muted">' + esc(i18n.schoolSnapshotLoading || 'Loading whole-school snapshot…') + '</p>';

		var body = new FormData();
		body.append('action', 'airb_school_dashboard');
		body.append('nonce', airbBenchmark.nonce);
		body.append('school_name', state.school);

		fetch(airbBenchmark.ajaxurl, { method: 'POST', body: body, credentials: 'same-origin' })
			.then(function (res) { return res.json(); })
			.then(function (json) {
				if (!json || !json.success || !json.data || !json.data.rollup) {
					mount.hidden = true;
					mount.innerHTML = '';
					return;
				}
				mount.innerHTML = schoolSnapshotHtml(json.data.rollup, { compact: true });
			})
			.catch(function () {
				mount.hidden = true;
				mount.innerHTML = '';
			});
	}

	window.airbRenderSchoolDashboard = function (rollup, container) {
		if (!rollup || !container) return;
		container.innerHTML = schoolSnapshotHtml(rollup, { compact: false });
	};

	if (window.AIRB) {
		window._airbRenderContact = renderContact;
		window._airbRenderRole = renderRole;
		window._airbProfilePhase = profilePhase;
		AIRB.registerRuntime({ state: state, el: el, cfg: cfg, i18n: i18n });
		AIRB.Audit.beginAudit = beginAudit;
		AIRB.Roles.renderResults = renderResults;
		AIRB.Roles.publicResultsHtml = publicResultsHtml;
		AIRB.Roles.teacherResultsHtml = teacherResultsHtml;
		AIRB.Roles.leaderResultsHtml = leaderResultsHtml;
		AIRB.Roles.studentResultsHtml = studentResultsHtml;
		AIRB.Roles.parentResultsHtml = parentResultsHtml;
		AIRB.Roles.supportResultsHtml = supportResultsHtml;
	}
})();
