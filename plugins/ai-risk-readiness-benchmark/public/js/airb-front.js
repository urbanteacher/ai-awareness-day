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
	var teacherResult = cfg.teacher_result || {};
	var studentResult = cfg.student_result || {};
	var leaderResult = cfg.leader_result || {};
	var improvementHub = cfg.improvement_hub || {};
	var STORAGE_KEY = 'airb_completed_roles_v1';
	var SESSION_KEY = 'airb_session_id_v1';
	var introCollapsed = false;

	var state = {
		phase: 'role',
		role: '',
		step: 0,
		sections: [],
		questions: [],
		answers: {},
		results: null,
		school: '',
		email: '',
		schoolPhase: '',
		orgType: '',
		yearGroup: '',
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

	function getSessionId() {
		try {
			var id = localStorage.getItem(SESSION_KEY);
			if (!id) {
				id = 'ses_' + Date.now().toString(36) + '_' + Math.random().toString(36).slice(2, 11);
				localStorage.setItem(SESSION_KEY, id);
			}
			return id;
		} catch (e) {
			return 'ses_' + Date.now().toString(36);
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

	function updateFlowChrome() {
		if (!el.root) return;
		var inFlow = !!(el.nav && !el.nav.hidden);
		var mobileShell = isMobileFlow() && (state.phase === 'role' || inFlow);
		el.root.classList.toggle('airb--nav-dock', inFlow);
		el.root.classList.toggle('airb--mobile-flow', mobileShell);
		el.root.classList.toggle('airb--phase-role', state.phase === 'role');
		el.root.classList.toggle('airb--phase-audit', state.phase === 'audit');
		el.root.classList.toggle('airb--intro-collapsed', introCollapsed);
		syncNavPlacement();
		document.body.classList.toggle('airb-flow-active', state.phase === 'audit' || state.phase === 'contact');
	}

	function syncNavPlacement() {
		if (!el.nav) return;
		var inlineHost = null;
		if (isMobileFlow() && !el.nav.hidden) {
			if (state.phase === 'audit') inlineHost = el.audit;
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

	function beginAudit() {
		try {
			hideError();
			if (!state.role) {
				showError(i18n.chooseRole);
				return false;
			}
			state.sections = sectionsForRole(state.role);
			state.questions = state.sections.reduce(function (acc, s) { return acc.concat(s.questions); }, []);
			if (!state.sections.length) {
				showError(i18n.error);
				return false;
			}
			state.phase = 'audit';
			state.step = 0;
			state.answers = {};
			collapseIntro();
			renderAuditSection();
			return true;
		} catch (err) {
			if (window.console && console.error) {
				console.error('AIRB beginAudit failed', err);
			}
			showError(i18n.error || 'Something went wrong. Please refresh and try again.');
			return false;
		}
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

	function readinessLevel(pct) {
		var r = Math.max(0, Math.min(100, parseInt(pct, 10) || 0));
		var labels = (i18n.bandsReadiness || {});
		if (r >= 76) return { slug: 'strong', label: labels.strong || 'Strong' };
		if (r >= 51) return { slug: 'established', label: labels.established || 'Established' };
		if (r >= 26) return { slug: 'developing', label: labels.developing || 'Developing' };
		return { slug: 'emerging', label: labels.emerging || 'Emerging' };
	}

	function readinessBandLabel(pct) {
		return readinessLevel(pct).label;
	}

	function readinessBandColor(pct) {
		var colors = {
			strong: 'var(--airb-low)',
			established: '#3a8fb0',
			developing: 'var(--airb-mod)',
			emerging: 'var(--airb-crit)',
		};
		return colors[readinessLevel(pct).slug] || 'var(--airb-text)';
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
		return role === 'teacher' || role === 'student';
	}

	function isParentRole() {
		return state.role === 'parent';
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

	function parentOverallFromDisplay(parentDisplay) {
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

	function studentOverallFromDomains(domainScores) {
		var slugs = ['ai_dependency', 'assessment_integrity', 'human_oversight', 'ai_literacy', 'privacy'];
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
		return {
			overall_risk: Math.round(overall * 10) / 10,
			alignment_score: Math.round(100 - overall),
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
		var extra = { teacher: ['t_without_ai', 't_ai_before_task', 't_feedback_ai'], student: ['s_attempt_first', 's_without_ai', 's_submitted_ai'] };
		var scores = [];
		(cfg.questions || []).forEach(function (q) {
			if (q.role !== role || !answers[q.id]) return;
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
				{ label: 'AI Dependency Index™', value: results.dependency_index + '%', band: depBand, tone: 'risk', band_label: bandLabel(depBand) },
				{ label: 'Human Oversight Ratio™', value: results.human_oversight_label, band: oversightBandFromLabel(results.human_oversight_label), tone: 'oversight' },
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

	function calculate(role, answers) {
		var sums = {};
		var counts = {};
		domainKeys.forEach(function (k) { sums[k] = 0; counts[k] = 0; });

		(cfg.questions || []).forEach(function (q) {
			if (q.role !== role || !answers[q.id]) return;
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
			results.readiness_level = readinessBand(studentOverall.alignment_score);
			results.readiness_level_label = readinessBandLabel(studentOverall.alignment_score);
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

	function domainColor(slug) {
		return domainColors[slug] || 'var(--airb-accent-fill)';
	}

	function sectionsForRole(role) {
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

	function persistRoleCompletion(score) {
		if (!state.role || typeof score !== 'number') return;
		try {
			var data = loadRoleCompletions();
			data[state.role] = { alignment: score, ts: Date.now() };
			localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
		} catch (e) { /* private browsing */ }
	}

	function questionsForRole(role) {
		return (cfg.questions || []).filter(function (q) { return q.role === role; });
	}

	function renderRole() {
		var completions = loadRoleCompletions();
		var html = '<div class="airb__panel"><h3 class="airb__panel-title">' + esc(i18n.chooseRole) + '</h3><div class="airb__role-grid">';
		var benchmarks = cfg.role_benchmarks || {};
		Object.keys(cfg.roles || {}).forEach(function (slug) {
			var active = state.role === slug ? ' is-selected' : '';
			var bench = benchmarks[slug] || {};
			var done = completions[slug];
			html += '<button type="button" class="airb__role-card' + active + '" data-role="' + esc(slug) + '">';
			if (done && typeof done.alignment === 'number') {
				html += '<span class="airb__role-done">' + esc((i18n.roleDone || 'Done · {n}%').replace('{n}', String(done.alignment))) + '</span>';
			}
			html += '<span class="airb__role-card-title">' + esc(cfg.roles[slug]) + '</span>';
			if (bench.title) html += '<span class="airb__role-card-sub">' + esc(bench.title) + '</span>';
			if (bench.measures && bench.measures.length) {
				html += '<span class="airb__role-card-blurb">' + esc(bench.measures.slice(0, 2).join(' · ')) + '</span>';
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
			html += '<div class="airb__options airb__options--pills">';
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
			input.addEventListener('change', highlightNextButton);
		});
	}

	function highlightNextButton() {
		if (!el.next || el.nav.hidden) return;
		el.next.classList.add('airb__btn--ready');
		window.setTimeout(function () {
			el.next.classList.remove('airb__btn--ready');
		}, 700);
	}

	function renderAuditSection() {
		var section = state.sections[state.step];
		if (!section) return;

		var domainLabel = domains[section.domain] || section.domain;
		var html = '<div class="airb__panel airb__panel--audit">';
		html += '<header class="airb__audit-head">';
		html += '<div class="airb__domtag"><span class="airb__domtag-sq" style="background:' + esc(domainColor(section.domain)) + '"></span>';
		html += '<span class="airb__domtag-text">';
		html += '<span class="airb__domtag-section">' + esc(section.name) + '</span>';
		html += '<span class="airb__domtag-domain">' + esc(domainLabel) + '</span>';
		html += '</span></div></header>';

		html += '<div class="airb__audit-questions">';
		section.questions.forEach(function (q) {
			html += '<div class="airb__q-block">';
			html += '<p class="airb__q-title">' + esc(q.text) + '</p>';
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
		el.back.hidden = state.step === 0;
		if (el.next) {
			el.next.textContent = i18n.next;
		}
		el.progress.hidden = false;
		updateStepper(state.sections.length, state.step);
		if (el.progressLbl) {
			el.progressLbl.textContent = (i18n.section || 'Section') + ' ' + (state.step + 1) + ' ' + i18n.of + ' ' + state.sections.length;
		}
		bindSectionInputs(section);
		updateFlowChrome();
	}

	function isYoungRole() {
		return state.role === 'student' || state.role === 'parent';
	}

	function isStaffRole() {
		return state.role === 'teacher' || state.role === 'leader';
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
		var hint = state.role === 'teacher' ? (i18n.profileHintTeacher || i18n.profileHint) : i18n.profileHint;
		var html = '<div class="airb__contact-grid">';
		html += '<div class="airb__contact-field">';
		html += '<label class="airb__label" for="airb-school-phase">' + esc(i18n.schoolPhase) + '</label>';
		html += '<select class="airb__select" id="airb-school-phase">';
		html += '<option value="">' + esc(i18n.schoolPhaseChoose) + '</option>';
		html += '<option value="primary"' + (state.schoolPhase === 'primary' ? ' selected' : '') + '>' + esc(i18n.schoolPhasePrimary) + '</option>';
		html += '<option value="secondary"' + (state.schoolPhase === 'secondary' ? ' selected' : '') + '>' + esc(i18n.schoolPhaseSecondary) + '</option>';
		html += '<option value="all_through"' + (state.schoolPhase === 'all_through' ? ' selected' : '') + '>' + esc(i18n.schoolPhaseAllThrough) + '</option>';
		html += '</select></div>';
		html += '<div class="airb__contact-field">';
		html += '<label class="airb__label" for="airb-org-type">' + esc(i18n.orgType) + '</label>';
		html += '<select class="airb__select" id="airb-org-type">';
		html += '<option value="">' + esc(i18n.orgTypeChoose) + '</option>';
		html += '<option value="standalone"' + (state.orgType === 'standalone' ? ' selected' : '') + '>' + esc(i18n.orgStandalone) + '</option>';
		html += '<option value="mat"' + (state.orgType === 'mat' ? ' selected' : '') + '>' + esc(i18n.orgMat) + '</option>';
		html += '</select></div></div>';
		if (hint) html += '<p class="airb__muted airb__profile-hint">' + esc(hint) + '</p>';
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
		var html = '<div class="airb__panel"><h3 class="airb__panel-title">' + esc(i18n.contactTitle || 'Almost done') + '</h3>';
		html += roleSummaryHtml();

		if (isYoungRole()) {
			if (state.role === 'parent' && i18n.contactHintParent) {
				html += '<p class="airb__muted">' + esc(i18n.contactHintParent) + '</p>';
			} else if (i18n.contactHintYoung) {
				html += '<p class="airb__muted">' + esc(i18n.contactHintYoung) + '</p>';
			}
			var ygLabel = state.role === 'parent' ? (i18n.yearGroupParent || i18n.yearGroup) : i18n.yearGroup;
			html += '<label class="airb__label" for="airb-year-group">' + esc(ygLabel) + '</label>' +
				'<select class="airb__select" id="airb-year-group">' + yearGroupOptionsHtml() + '</select>';
		} else {
			if (state.role === 'teacher' && i18n.contactHintTeacher) {
				html += '<p class="airb__muted">' + esc(i18n.contactHintTeacher) + '</p>';
			} else if (i18n.contactHint) {
				html += '<p class="airb__muted">' + esc(i18n.contactHint) + '</p>';
			}

			if (state.role === 'leader' || state.role === 'teacher') {
				html += '<label class="airb__label" for="airb-school">' + esc(i18n.schoolOptional) + '</label>' +
					'<input type="text" class="airb__input" id="airb-school" value="' + esc(state.school) + '" autocomplete="organization" />';
			}

			if (state.role === 'leader' || state.role === 'teacher') {
				html += staffProfileFieldsHtml();
			}

			html += '<label class="airb__label" for="airb-email">' + esc(i18n.emailOptional) + '</label>' +
				'<input type="email" class="airb__input" id="airb-email" value="' + esc(state.email) + '" autocomplete="email" />';
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
		if (isParentRole() || (isTeacherRole() && r.teacher_results) || (isStudentRole() && r.student_results) || (isLeaderRole() && r.leader_results)) {
			if (isParentRole()) {
			var topics = parentResult.focus_topics || [];
			if (!topics.length) return '';
			var html = '<div class="airb__res-panel airb__res-panel--focus airb__res-panel--parent-focus"><h3>' + esc(i18n.domainFocus || 'What to focus on') + '</h3>';
			topics.forEach(function (topic) {
				html += '<div class="airb__res-rec airb__res-rec--parent">';
				html += '<h4 class="airb__res-rec-title">' + esc(topic.label) + '</h4>';
				html += '<p class="airb__res-rec-body">' + esc(topic.body) + '</p></div>';
			});
			return html + '</div>';
			}
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

	function domainReadinessRowsHtml(r) {
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
		domainKeys.forEach(function (slug) {
			var d = r.domain_scores[slug];
			if (!d || !d.questions_answered) return;
			var pct = Math.round(d.readiness_percentage);
			rows += '<div class="airb__res-row"><span class="airb__res-row-nm">' + esc(d.label) + '</span>';
			rows += '<span class="airb__res-track"><i style="width:' + pct + '%;background:' + esc(readinessBandColor(pct)) + '"></i></span>';
			rows += '<span class="airb__res-row-pc">' + pct + '%</span></div>';
		});
		if (!rows) return '';
		return '<div class="airb__res-panel airb__res-panel--domains"><h3>' + esc(i18n.readinessBreakdown || 'Readiness breakdown') + '</h3>' + rows + '</div>';
	}

	function riskIndicatorsHtml(r) {
		if (isParentRole() || (isLeaderRole() && !!r.leader_results)) return '';

		var rows = '';
		var dep = r.dependency_index;
		if (roleShowsDependency(state.role) && typeof dep === 'number') {
			rows += '<div class="airb__res-row airb__res-row--risk">';
			rows += '<span class="airb__res-row-nm">' + esc(i18n.dependencyRisk || 'AI Dependency Risk') + '</span>';
			rows += '<span class="airb__res-track"><i style="width:' + dep + '%;background:' + esc(dependencyColor(dep)) + '"></i></span>';
			rows += '<span class="airb__res-row-pc" style="color:' + esc(dependencyColor(dep)) + '">' + dep + '%</span></div>';
		}

		var risk = Math.round(r.overall_risk_percentage || 0);
		rows += '<div class="airb__res-row airb__res-row--risk">';
		rows += '<span class="airb__res-row-nm">' + esc(i18n.statRisk || 'AI risk score') + '</span>';
		rows += '<span class="airb__res-track"><i style="width:' + risk + '%;background:' + esc(riskScoreColor(risk)) + '"></i></span>';
		rows += '<span class="airb__res-row-pc" style="color:' + esc(riskScoreColor(risk)) + '">' + risk + '%</span></div>';

		if (!rows) return '';
		return '<div class="airb__res-panel airb__res-panel--domains airb__res-panel--risk"><h3>' + esc(i18n.riskIndicators || 'Risk indicators') + '</h3>' + rows + '</div>';
	}

	function oversightPanelHtml(r) {
		var val = oversightGaugeValue(r);
		var studentMode = isStudentRole() && !!r.student_results;
		var html = '<div class="airb__res-panel airb__res-panel--gauge"><h3>' + esc(i18n.oversight) + '<span class="airb__tm">™</span></h3>';
		if (val === null) {
			html += '<p class="airb__res-na">' + esc(i18n.oversightNa || 'Not measured for this audience.') + '</p>';
		} else {
			var label = r.human_oversight_label || '';
			var help = studentMode
				? 'How often you check, edit or question AI answers before relying on them.'
				: 'Share of AI output reviewed or changed before use. Below 26% signals reliance without meaningful human review.';
			html += '<div class="airb__res-gauge-wrap">' + oversightGaugeSvg(val, esc(i18n.oversight) + ': ' + Math.round(val) + '%') + '</div>';
			if (label) html += '<p class="airb__gauge-band" style="color:' + oversightZoneColor(val) + '">' + esc(label) + '</p>';
			html += '<p class="airb__gauge-help">' + esc(help) + '</p>';
		}
		return html + '</div>';
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
		html += '<h3 class="airb__opportunities-title">' + esc(heading) + '</h3>';
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
			html += '</div>';
		});
		return html + '</div></section>';
	}

	function teacherResultsHtml(r) {
		var tr = r.teacher_results;
		if (!tr) return '';

		var html = '';

		if (tr.performance_headline) {
			html += '<p class="airb__res-headline">' + esc(tr.performance_headline) + '</p>';
		}

		if (tr.strengths && tr.strengths.length) {
			html += strengthsSectionHtml(tr.strengths, teacherResult.strengths_heading || 'What you\'re doing well');
		}

		if (tr.opportunities && tr.opportunities.length) {
			html += opportunitiesSectionHtml(
				tr.opportunities,
				teacherResult.opportunities_heading || 'Opportunities to strengthen further',
				'teacher'
			);
		}

		if (tr.champion_pathway) {
			var cp = tr.champion_pathway;
			html += '<aside class="airb__insight airb__insight--teacher">';
			html += '<span class="airb__insight-label">' + esc(cp.next_step_label || teacherResult.champion_pathway && teacherResult.champion_pathway.next_step_label || 'Recommended next step') + '</span>';
			html += '<h4 class="airb__pathway-title">' + esc(cp.title) + '</h4>';
			if (cp.intro) html += '<p>' + esc(cp.intro) + '</p>';
			if (cp.roles && cp.roles.length) {
				html += '<ul class="airb__pathway-list">';
				cp.roles.forEach(function (role) {
					html += '<li>' + esc(role) + '</li>';
				});
				html += '</ul>';
			}
			html += '</aside>';
		} else if (tr.gap_pathway && tr.gap_pathway.items && tr.gap_pathway.items.length) {
			html += '<aside class="airb__insight airb__insight--teacher airb__insight--gap">';
			html += '<span class="airb__insight-label">' + esc(tr.gap_pathway.label || 'Recommended next step') + '</span>';
			if (tr.gap_pathway.intro) html += '<p>' + esc(tr.gap_pathway.intro) + '</p>';
			html += '<ul class="airb__pathway-list">';
			tr.gap_pathway.items.forEach(function (item) {
				html += '<li><strong>' + esc(item) + '</strong></li>';
			});
			html += '</ul></aside>';
		}

		var resources = tr.suggested_resources || [];
		if (resources.length && tr.champion_pathway) {
			html += '<div class="airb__res-panel airb__res-panel--resources">';
			html += '<h3>' + esc(tr.champion_pathway.resources_heading || 'Suggested resources') + '</h3>';
			html += '<ul class="airb__resource-list">';
			resources.forEach(function (res) {
				if (res.url) {
					html += '<li><a href="' + esc(res.url) + '" target="_blank" rel="noopener">' + esc(res.label) + '</a></li>';
				} else {
					html += '<li>' + esc(res.label) + '</li>';
				}
			});
			html += '</ul></div>';
		}

		return html;
	}

	function studentResultsHtml(r) {
		var sr = r.student_results;
		if (!sr) return '';

		var html = '';

		if (sr.performance_headline) {
			html += '<p class="airb__res-headline">' + esc(sr.performance_headline) + '</p>';
		}

		if (sr.learning_metrics && sr.learning_metrics.length) {
			var profileMetrics = sr.learning_metrics.map(function (row) {
				return {
					label: row.label,
					value: row.value + '%',
					color: row.type === 'risk' ? riskScoreColor(row.value) : readinessBandColor(row.value),
				};
			});
			html += summaryMetricsSectionHtml(
				profileMetrics,
				sr.profile_title || studentResult.profile_title || 'Your AI Learning Profile',
				{ modifier: 'student' }
			);
		}

		if (sr.strengths && sr.strengths.length) {
			html += strengthsSectionHtml(sr.strengths, studentResult.strengths_heading || 'What you\'re doing well');
		}

		if (sr.opportunities && sr.opportunities.length) {
			html += opportunitiesSectionHtml(
				sr.opportunities,
				studentResult.opportunities_heading || 'Opportunities to improve',
				'student'
			);
		}

		if (sr.learning_challenge) {
			var lc = sr.learning_challenge;
			html += '<aside class="airb__insight airb__insight--student">';
			html += '<span class="airb__insight-label">' + esc(lc.label || studentResult.learning_challenge && studentResult.learning_challenge.label || 'Recommended next step') + '</span>';
			html += '<h4 class="airb__pathway-title">' + esc(lc.title || '') + '</h4>';
			if (lc.intro) html += '<p>' + esc(lc.intro) + '</p>';
			if (lc.steps && lc.steps.length) {
				html += '<ul class="airb__pathway-list">';
				lc.steps.forEach(function (step) {
					html += '<li>' + esc(step) + '</li>';
				});
				html += '</ul>';
			}
			if (lc.closing) html += '<p class="airb__muted">' + esc(lc.closing) + '</p>';
			html += '</aside>';
		}

		var resources = sr.student_resources || [];
		if (resources.length) {
			html += '<div class="airb__res-panel airb__res-panel--resources">';
			html += '<h3>' + esc(studentResult.resources_heading || 'Free student resources') + '</h3>';
			html += '<ul class="airb__resource-list">';
			resources.forEach(function (res) {
				if (res.url) {
					html += '<li><a href="' + esc(res.url) + '" target="_blank" rel="noopener">' + esc(res.label) + '</a></li>';
				} else {
					html += '<li>' + esc(res.label) + '</li>';
				}
			});
			html += '</ul></div>';
		}

		if (sr.school_contribution) {
			var sc = sr.school_contribution;
			html += '<div class="airb__res-panel airb__res-panel--contribution">';
			html += '<h3>' + esc(sc.heading || 'School contribution') + '</h3>';
			if (sc.body) html += '<p>' + esc(sc.body) + '</p>';
			html += '</div>';
		}

		return html;
	}

	function leaderResultsHtml(r) {
		var lr = r.leader_results;
		if (!lr) return '';

		var html = '';
		var es = lr.executive_summary;

		if (es) {
			html += '<section class="airb__exec-summary">';
			html += '<h3>' + esc(es.title || leaderResult.executive_title || 'Executive Summary') + '</h3>';
			if (es.intro) html += '<p class="airb__exec-intro">' + esc(es.intro) + '</p>';

			if (es.strengths && es.strengths.length) {
				html += '<h4 class="airb__exec-sub">' + esc(es.strengths_heading || leaderResult.strengths_heading || 'Strengths include') + '</h4>';
				html += '<ul class="airb__strength-list airb__strength-list--exec">';
				es.strengths.forEach(function (item) {
					html += '<li><span class="airb__strength-mark" aria-hidden="true">✓</span> ' + esc(item) + '</li>';
				});
				html += '</ul>';
			}

			if (es.attention_areas && es.attention_areas.length) {
				html += '<h4 class="airb__exec-sub">' + esc(es.attention_heading || leaderResult.attention_heading || 'Areas requiring attention') + '</h4>';
				html += '<ul class="airb__attention-list">';
				es.attention_areas.forEach(function (item) {
					html += '<li><span class="airb__attention-mark" aria-hidden="true">⚠</span> ' + esc(item) + '</li>';
				});
				html += '</ul>';
			}

			html += '<div class="airb__exec-scores">';
			html += '<p><strong>' + esc(i18n.dfeAlignment || 'Overall DfE Alignment Score') + ':</strong> ' + (es.alignment_score || r.alignment_score) + '%</p>';
			if (es.risk_level_label) {
				html += '<p><strong>' + esc(i18n.statRisk || 'Risk Level') + ':</strong> ' + esc(es.risk_level_label) + '</p>';
			}
			if (es.priority_action) {
				html += '<p class="airb__exec-priority"><strong>' + esc(i18n.priorityAction || 'Priority Action') + ':</strong> ' + esc(es.priority_action) + '</p>';
			}
			html += '</div></section>';
		}

		if (lr.maturity) {
			var mat = lr.maturity;
			html += '<div class="airb__res-panel airb__res-panel--maturity">';
			html += '<h3>' + esc(leaderResult.maturity_heading || 'Governance maturity') + '</h3>';
			html += '<p class="airb__maturity-badge">' + esc(mat.label) + '</p>';
			if (mat.description) html += '<p class="airb__muted">' + esc(mat.description) + '</p>';
			html += '</div>';
		}

		if (lr.peer_benchmark) {
			var pb = lr.peer_benchmark;
			html += '<section class="airb__peer-benchmark">';
			html += '<h4>' + esc(leaderResult.peer_benchmark_title || 'Benchmark against similar schools') + '</h4>';
			html += '<table class="airb__summary-table airb__summary-table--peer"><tbody>';
			html += '<tr><th scope="row">' + esc(i18n.yourScore || 'Your score') + '</th><td style="color:' + esc(readinessBandColor(pb.your_score)) + '">' + pb.your_score + '%</td></tr>';
			html += '<tr><th scope="row">' + esc(pb.phase_label || 'Average school') + '</th><td>' + pb.average_score + '%</td></tr>';
			html += '<tr><th scope="row">' + esc(i18n.topQuartile || 'Top Quartile Schools') + '</th><td>' + pb.top_quartile + '%</td></tr>';
			html += '</tbody></table>';
			if (pb.is_estimated) {
				html += '<p class="airb__muted airb__peer-note">' + esc(i18n.peerEstimated || 'Comparison uses reference benchmarks until enough similar schools have completed the audit.') + '</p>';
			} else if (typeof pb.percentile === 'number') {
				html += '<p class="airb__muted airb__peer-note">' + esc((i18n.peerPercentile || 'Your score is ahead of {n}% of similar schools.').replace('{n}', String(pb.percentile))) + '</p>';
			}
			html += '</section>';
		}

		if (lr.focus_areas && lr.focus_areas.length) {
			html += '<div class="airb__res-panel airb__res-panel--focus airb__res-panel--leader-focus">';
			html += '<h3>' + esc(leaderResult.focus_heading || 'Priority focus areas') + '</h3>';
			lr.focus_areas.forEach(function (area) {
				html += '<div class="airb__res-rec airb__res-rec--leader">';
				html += '<h4 class="airb__res-rec-title">' + esc(area.label) + ' — ' + area.pct + '%</h4>';
				if (area.summary) html += '<p class="airb__res-rec-body">' + esc(area.summary) + '</p>';
				if (area.actions && area.actions.length) {
					html += '<p class="airb__actions-label">' + esc(leaderResult.focus_actions_label || 'Recommended actions') + ':</p>';
					html += '<ul class="airb__leader-actions">';
					area.actions.forEach(function (act) {
						html += '<li>' + esc(act) + '</li>';
					});
					html += '</ul>';
				}
				html += '</div>';
			});
			html += '</div>';
		}

		if (lr.risk_heatmap && lr.risk_heatmap.length) {
			html += '<h4>' + esc(leaderResult.heatmap_heading || i18n.heatMap || 'Risk heat map') + '</h4>' + heatmapHtml(lr.risk_heatmap);
		}

		if (lr.next_steps) {
			var ns = lr.next_steps;
			html += '<section class="airb__next-steps">';
			html += '<p class="airb__funnel-stage">' + esc(ns.title || 'Next steps') + '</p>';
			html += '<h4>' + esc(ns.subtitle || 'Recommended for your school') + '</h4>';
			if (ns.intro) html += '<p class="airb__muted">' + esc(ns.intro) + '</p>';
			html += '<div class="airb__next-steps-grid">';
			(ns.cards || []).forEach(function (card) {
				html += '<article class="airb__next-step-card airb__next-step-card--' + esc(card.key || '') + '">';
				html += '<h5>' + esc(card.title) + '</h5>';
				if (card.body) html += '<p>' + esc(card.body) + '</p>';
				if (card.key === 'whole_school_benchmark' && card.counts) {
					html += '<ul class="airb__rollout-counts">';
					html += '<li>' + esc(i18n.roleLeader || 'Leaders') + ': ' + (card.counts.leader || 0) + '</li>';
					html += '<li>' + esc(i18n.roleTeacher || 'Teachers') + ': ' + (card.counts.teacher || 0) + '</li>';
					html += '<li>' + esc(i18n.roleStudent || 'Students') + ': ' + (card.counts.student || 0) + '</li>';
					html += '<li>' + esc(i18n.roleParent || 'Parents') + ': ' + (card.counts.parent || 0) + '</li>';
					html += '</ul>';
					if (!card.unlocked) {
						var unlockCopy = (leaderResult.rollout_unlock_copy || 'Whole-school benchmarking unlocks after {threshold}+ responses.')
							.replace('{threshold}', String(card.threshold || 20));
						html += '<p class="airb__muted">' + esc(unlockCopy) + '</p>';
					}
				}
				if (card.topics && card.topics.length) {
					html += '<ul class="airb__leader-actions">';
					card.topics.forEach(function (topic) {
						html += '<li>' + esc(topic) + '</li>';
					});
					html += '</ul>';
				}
				if (card.deliverables && card.deliverables.length) {
					html += '<ul class="airb__leader-actions">';
					card.deliverables.forEach(function (item) {
						html += '<li>' + esc(item) + '</li>';
					});
					html += '</ul>';
				}
				if (card.cta_url) {
					html += '<a class="airb__btn airb__btn--primary airb__btn--sm" href="' + esc(card.cta_url) + '" target="_blank" rel="noopener">' + esc(card.cta_text || 'Find out more') + '</a>';
				}
				html += '</article>';
			});
			html += '</div></section>';
		}

		return html;
	}

	function resultsProfileHtml(r) {
		var roleLbl = (cfg.roles || {})[state.role] || state.role;
		var readiness = r.alignment_score;
		var risk = Math.round(r.overall_risk_percentage);
		var readinessLabel = (r.readiness_level_label || readinessBandLabel(readiness)).toUpperCase();
		var depVal = roleShowsDependency(state.role) ? r.dependency_index : null;
		var eyebrow = (i18n.resultsRoleResult || '{role} result').replace('{role}', roleLbl);
		var parentMode = isParentRole();
		var studentMode = isStudentRole() && !!r.student_results;
		var leaderMode = isLeaderRole() && !!r.leader_results;
		var bandSummary = parentMode ? parentBandSummary(readiness) : '';

		var html = '<section class="airb__res-profile' + (parentMode ? ' airb__res-profile--parent' : '') + '">';
		html += '<span class="airb__res-eyebrow"><span class="airb__res-eyebrow-dot" aria-hidden="true"></span>' + esc(eyebrow) + '</span>';
		html += '<div class="airb__res-shead">';
		html += '<h2 class="airb__res-title">' + esc(i18n.resultsProfileTitle || i18n.resultsTitle || 'Your AI Risk & Readiness profile') + '</h2>';
		html += '<span class="airb__res-band" style="color:' + esc(readinessBandColor(readiness)) + '">' + esc(readinessLabel) + '</span>';
		html += '</div>';

		if (isTeacherRole() || studentMode) {
			html += oversightPanelHtml(r);
		}

		html += '<div class="airb__res-grid3' + (parentMode ? ' airb__res-grid3--two' : '') + '">';
		html += '<div class="airb__res-stat">';
		html += '<div class="airb__res-stat-lab">' + esc(i18n.statReadiness || 'Readiness score') + '</div>';
		html += '<div class="airb__res-stat-big" style="color:' + esc(readinessBandColor(readiness)) + '" data-count="' + readiness + '">' + readiness + '%</div>';
		if (!parentMode) {
			html += '<div class="airb__res-stat-note">' + esc(i18n.statReadinessNote || 'Weighted across every domain in this audit.') + '</div>';
		}
		html += '</div>';

		html += '<div class="airb__res-stat">';
		html += '<div class="airb__res-stat-lab">' + esc(i18n.statRisk || 'AI risk score') + '</div>';
		html += '<div class="airb__res-stat-big" style="color:' + esc(riskScoreColor(risk)) + '" data-count="' + risk + '">' + risk + '%</div>';
		if (!parentMode) {
			html += '<div class="airb__res-stat-note">' + esc(i18n.statRiskNote || 'Behavioural exposure — the inverse of readiness.') + '</div>';
		}
		html += '</div>';

		if (!parentMode) {
		html += '<div class="airb__res-stat">';
		if (leaderMode) {
			html += '<div class="airb__res-stat-lab">' + esc(i18n.dfeAlignment || 'DfE Alignment Score') + '</div>';
			html += '<div class="airb__res-stat-big" style="color:' + esc(readinessBandColor(readiness)) + '" data-count="' + readiness + '">' + readiness + '%</div>';
			html += '<div class="airb__res-stat-note">' + esc(i18n.statDfeNote || 'Overall alignment with DfE AI guidance for schools.') + '</div>';
		} else {
		html += '<div class="airb__res-stat-lab">' + esc(i18n.dependency || 'AI Dependency Index') + '<span class="airb__tm">™</span></div>';
		if (depVal === null) {
			html += '<div class="airb__res-stat-big airb__res-stat-big--na">—</div>';
			html += '<div class="airb__res-stat-note">' + esc(i18n.statDepNa || 'Not measured for this audience.') + '</div>';
		} else {
			html += '<div class="airb__res-stat-big" style="color:' + esc(dependencyColor(depVal)) + '" data-count="' + depVal + '">' + depVal + '%</div>';
			html += '<div class="airb__res-stat-note">' + esc(i18n.statDepNote || 'Risk indicator — higher means greater reliance on AI.') + '</div>';
		}
		}
		html += '</div>';
		}
		html += '</div>';

		if (bandSummary) {
			html += '<p class="airb__res-summary">' + esc(bandSummary) + '</p>';
		}

		if (parentMode) {
			html += domainReadinessRowsHtml(r);
		} else if (leaderMode) {
			/* Governance content follows in leaderResultsHtml. */
		} else if (isTeacherRole() || studentMode) {
			html += domainReadinessRowsHtml(r);
			html += riskIndicatorsHtml(r);
			if (isTeacherRole() && r.teacher_results && r.teacher_results.benchmark_summary) {
				var tbs = r.teacher_results.benchmark_summary;
				html += summaryMetricsSectionHtml(
					tbs.metrics,
					tbs.title || 'Teacher Benchmark Summary',
					{ modifier: 'teacher' }
				);
			}
		} else {
			html += '<div class="airb__res-two">' + oversightPanelHtml(r) + domainReadinessRowsHtml(r) + '</div>';
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

	// Reusable zoned semicircle gauge SVG for the Human Oversight Ratio — the band
	// scale (critical / high / moderate / strong) is drawn into the arc itself.
	function oversightGaugeSvg(val, aria) {
		val = Math.max(0, Math.min(100, val));
		var A0 = -120, A1 = 120, cx = 120, cy = 120, rr = 92;
		function toAngle(v) { return A0 + (v / 100) * (A1 - A0); }
		function polar(x, y, rad, deg) { var a = (deg - 90) * Math.PI / 180; return [x + rad * Math.cos(a), y + rad * Math.sin(a)]; }
		function arc(x, y, rad, s, e) {
			var p0 = polar(x, y, rad, s), p1 = polar(x, y, rad, e);
			var large = (e - s) <= 180 ? 0 : 1;
			return 'M ' + p0[0].toFixed(2) + ' ' + p0[1].toFixed(2) + ' A ' + rad + ' ' + rad + ' 0 ' + large + ' 1 ' + p1[0].toFixed(2) + ' ' + p1[1].toFixed(2);
		}
		var zones = [[0, 10], [10, 25], [25, 50], [50, 100]];
		var npt = polar(cx, cy, rr - 14, toAngle(val));
		var svg = '<svg viewBox="0 0 240 172" class="airb__gauge-svg" role="img" aria-label="' + esc(aria || ('Human Oversight Ratio ' + Math.round(val) + '%')) + '">';
		svg += '<path d="' + arc(cx, cy, rr, A0, A1) + '" fill="none" stroke="var(--airb-border)" stroke-width="16" stroke-linecap="round"></path>';
		zones.forEach(function (z, i) {
			var cap = (i === 0 || i === zones.length - 1) ? 'round' : 'butt';
			svg += '<path d="' + arc(cx, cy, rr, toAngle(z[0]), toAngle(z[1])) + '" fill="none" stroke="' + oversightZoneColor(z[1] - 0.1) + '" stroke-width="16" stroke-linecap="' + cap + '"></path>';
		});
		svg += '<line x1="' + cx + '" y1="' + cy + '" x2="' + npt[0].toFixed(2) + '" y2="' + npt[1].toFixed(2) + '" stroke="var(--airb-brand)" stroke-width="3.5" stroke-linecap="round"></line>';
		svg += '<circle cx="' + cx + '" cy="' + cy + '" r="7" fill="var(--airb-brand)"></circle>';
		svg += '<text x="' + cx + '" y="' + (cy - 16) + '" text-anchor="middle" class="airb__gauge-num">' + Math.round(val) + '<tspan font-size="20">%</tspan></text>';
		return svg + '</svg>';
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
		var gi = r.guided_improvement;
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
				html += '<ul class="airb__guided-resources">';
				block.resources.forEach(function (res) {
					var kindLabel = kinds[res.kind] || res.kind || 'Read';
					var linkUrl = appendSessionToUrl(res.url || '');
					var pillar = block.slug || '';
					html += '<li><a href="' + esc(linkUrl) + '" class="airb__guided-link airb__guided-link--' + esc(res.kind || 'read') + '" data-airb-track="guided" data-airb-pillar="' + esc(pillar) + '" data-airb-kind="' + esc(res.kind || 'read') + '" data-airb-label="' + esc(res.label || '') + '"><span class="airb__guided-kind">' + esc(kindLabel) + '</span> ' + esc(res.label) + '</a></li>';
				});
				html += '</ul>';
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
				html += '<a class="airb__btn airb__btn--primary airb__btn--sm" href="' + esc(appendSessionToUrl(c.cta_url)) + '" data-airb-track="consultation">' + esc(c.cta_text || 'Book your free review') + '</a>';
			}
			html += '</aside>';
		}

		return html + '</section>';
	}

	function renderResults() {
		var r = state.results;
		if (!r) return;
		var parentMode = isParentRole();
		var teacherMode = isTeacherRole() && !!r.teacher_results;
		var studentMode = isStudentRole() && !!r.student_results;

		var leaderMode = isLeaderRole() && !!r.leader_results;

		var html = '<div class="airb__results' + (parentMode ? ' airb__results--parent' : '') + (teacherMode ? ' airb__results--teacher' : '') + (studentMode ? ' airb__results--student' : '') + (leaderMode ? ' airb__results--leader' : '') + '">';
		html += resultsProfileHtml(r);

		if (teacherMode) {
			html += teacherResultsHtml(r);
		} else if (studentMode) {
			html += studentResultsHtml(r);
		} else if (leaderMode) {
			html += leaderResultsHtml(r);
		} else {
			html += focusDomainsHtml(r);
		}

		html += guidedImprovementHtml(r);

		if (parentMode) {
			html += parentPriorityFocusHtml();
			html += parentExposureListHtml();
		} else if (!teacherMode && !studentMode && !leaderMode) {
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

		if (!parentMode && !teacherMode && !studentMode && !leaderMode && r.policy_support) {
			var pg = r.policy_support;
			html += '<article class="airb__policy-gen airb__pathway-card">';
			html += '<span class="airb__pathway-badge airb__pathway-badge--policy">' + esc(i18n.policyGen) + '</span>';
			html += '<h5>' + esc(pg.title) + '</h5><p>' + esc(pg.body) + '</p>';
			if (pg.cta_url) html += '<a class="airb__btn airb__btn--primary airb__btn--sm" href="' + esc(pg.cta_url) + '" target="_blank" rel="noopener">' + esc(pg.cta_text) + '</a>';
			html += '</article>';
		}

		if (!parentMode && !teacherMode && !studentMode && !leaderMode && r.stage2_products && r.stage2_products.length) {
			html += '<section class="airb__stage2"><p class="airb__funnel-stage">' + esc(i18n.stage2) + '</p>';
			html += '<ul class="airb__stage2-list">';
			r.stage2_products.forEach(function (item) {
				html += '<li><span class="airb__stage2-reason">' + esc(item.reason) + '</span> → <strong>' + esc(item.product) + '</strong></li>';
			});
			html += '</ul></section>';
		}

		if (!parentMode && !teacherMode && !studentMode && !leaderMode && r.key_exposure_areas && r.key_exposure_areas.length) {
			html += '<h4>' + esc(i18n.exposure) + '</h4>' + exposureCardsHtml(r.key_exposure_areas);
		}

		if (!parentMode && !teacherMode && !studentMode && !leaderMode) {
			html += benchmarkHtml(r);
		}

		if (!teacherMode && !studentMode && !leaderMode && r.gateway && r.gateway.cards && r.gateway.cards.length) {
			html += '<section class="airb__gateway">';
			html += '<h4>' + esc(r.gateway.headline || i18n.gatewayTitle) + '</h4>';
			if (r.gateway.intro) html += '<p class="airb__muted">' + esc(r.gateway.intro) + '</p>';
			html += '<div class="airb__gateway-grid">';
			r.gateway.cards.forEach(function (card) {
				html += '<article class="airb__gateway-card"><h5>' + esc(card.title) + '</h5><p>' + esc(card.body) + '</p>';
				if (card.cta_url) {
					html += '<a class="airb__btn airb__btn--primary airb__btn--sm" href="' + esc(card.cta_url) + '">' + esc(card.cta_text) + '</a>';
				}
				html += '</article>';
			});
			html += '</div></section>';
		}

		var shareHint = shareResultsHintText(r);
		if (shareHint) {
			html += '<p class="airb__muted airb__share-hint">' + esc(shareHint) + '</p>';
		}

		html += '<div class="airb__results-actions">';
		var shareMailto = buildShareResultsMailto();
		if (shareMailto) {
			html += '<a class="airb__btn airb__btn--primary airb__btn--share" href="' + shareMailto + '" id="airb-share-results">' + esc(i18n.shareWithSchool || 'Share results with your school') + '</a>';
		}
		if (isStaffRole()) {
			var reportMailto = buildReportRequestMailto();
			if (reportMailto) {
				html += '<a class="airb__btn airb__btn--primary airb__btn--premium" href="' + reportMailto + '" id="airb-request-report">' + esc(i18n.requestFullReport) + '</a>';
			}
		}
		if (state.email) {
			html += '<button type="button" class="airb__btn airb__btn--ghost" id="airb-email-report">' + esc(i18n.emailReport) + '</button>';
		}
		html += '</div>';
		if (state.school && !isYoungRole()) {
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

		animateResultsStats();
		persistRoleCompletion(r.alignment_score);
		updateAppbarCompletions();
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

		var shareBtn = document.getElementById('airb-share-results');
		if (shareBtn) {
			shareBtn.addEventListener('click', function () {
				trackEvent('share_click', {});
			});
		}

		var reportBtn = document.getElementById('airb-request-report');
		if (reportBtn) {
			reportBtn.addEventListener('click', function () {
				trackEvent('report_request_click', {});
			});
		}

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

	function saveSectionAnswers(section) {
		if (!section) return false;
		var complete = true;
		section.questions.forEach(function (q) {
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
		if (state.phase === 'audit') {
			var section = state.sections[state.step];
			if (!saveSectionAnswers(section)) { showError(i18n.required); return; }
			if (state.step < state.sections.length - 1) {
				state.step++;
				renderAuditSection();
				return;
			}
			state.phase = 'contact';
			renderContact();
			return;
		}
		if (state.phase === 'contact') {
			if (!isYoungRole()) {
				state.school = (state.role === 'leader' || state.role === 'teacher') ? ((document.getElementById('airb-school') || {}).value || '') : '';
				state.email = (document.getElementById('airb-email') || {}).value || '';
				state.schoolPhase = (document.getElementById('airb-school-phase') || {}).value || '';
				state.orgType = (document.getElementById('airb-org-type') || {}).value || '';
				if (state.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(state.email)) {
					showError(i18n.emailInvalid);
					return;
				}
			} else {
				state.school = '';
				state.email = '';
				state.yearGroup = (document.getElementById('airb-year-group') || {}).value || '';
			}

			state.results = calculate(state.role, state.answers);
			state.phase = 'results';
			el.results.innerHTML = '<p class="airb__muted airb__loading">' + esc(i18n.saving || 'Preparing your results…') + '</p>';
			el.results.hidden = false;
			el.role.hidden = true;
			el.audit.hidden = true;
			el.contact.hidden = true;
			el.nav.hidden = true;
			el.progress.hidden = true;
			updateFlowChrome();
			submitResults(function () { renderResults(); });
		}
	}

	function goBack() {
		hideError();
		if (state.phase === 'audit' && state.step > 0) {
			saveSectionAnswers(state.sections[state.step]);
			state.step--;
			renderAuditSection();
			return;
		}
		if (state.phase === 'audit' && state.step === 0) {
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
			renderAuditSection();
		}
	}

	function submitResults(done) {
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
				if (json.success && json.data && json.data.results) {
					state.results = json.data.results;
				}
				if (json.success && json.data && json.data.submission_id) {
					state.submissionId = parseInt(json.data.submission_id, 10) || 0;
				}
				if (done) done();
			})
			.catch(function () {
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
		return i18n.shareResultsHint || '';
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
		if (isMobileFlow()) {
			collapseIntro();
		}
		renderRole();
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

	window.airbRenderSchoolDashboard = function (rollup, container) {
		if (!rollup || !container) return;
		var html = '<div class="airb__school-results">';
		html += '<h3 class="airb__panel-title">' + esc(rollup.school_name) + '</h3>';
		html += '<p class="airb__muted">' + rollup.roles_complete + ' of ' + rollup.roles_total + ' groups</p>';
		html += '<div class="airb__role-bars">';
		Object.keys(rollup.roles || {}).forEach(function (slug) {
			var d = rollup.roles[slug];
			html += '<div class="airb__role-bar' + (d.readiness == null ? ' is-missing' : '') + '">';
			html += '<span class="airb__role-bar-label">' + esc(d.label) + '</span>';
			if (d.readiness != null) {
				html += '<div class="airb__bar-track"><div class="airb__bar-fill" style="width:' + d.readiness + '%"></div></div>';
				html += '<span class="airb__role-bar-val">' + d.readiness + '%</span>';
			} else {
				html += '<span class="airb__role-bar-val airb__muted">Awaiting audit</span>';
			}
			html += '</div>';
		});
		html += '</div>';
		html += '<div class="airb__cards">';
		html += card({
			label: 'Overall DfE AI Alignment Score',
			value: rollup.overall_alignment + '%',
			band: readinessBand(rollup.overall_alignment),
			tone: 'readiness',
			band_label: readinessBandLabel(rollup.overall_alignment),
		});
		html += card({
			label: 'Risk level',
			value: rollup.overall_risk_label,
			band: rollup.overall_risk_level,
			tone: 'risk',
			band_label: rollup.overall_risk_label,
		});
		html += '</div>';
		if (rollup.exposure_breakdown && rollup.exposure_breakdown.length) {
			html += '<h4>Exposure breakdown</h4><table class="airb__exposure-table"><tbody>';
			rollup.exposure_breakdown.forEach(function (row) {
				html += '<tr><td>' + esc(row.label) + '</td><td><span class="airb__exposure-pill airb__exposure-pill--' + esc((row.band_label || 'low').toLowerCase()) + '">' + esc(row.band_label) + '</span></td></tr>';
			});
			html += '</tbody></table>';
		}
		if (rollup.key_exposure_areas && rollup.key_exposure_areas.length) {
			html += '<h4>Key exposure areas</h4>' + exposureCardsHtml(rollup.key_exposure_areas);
		}
		html += '</div>';
		container.innerHTML = html;
	};
})();
