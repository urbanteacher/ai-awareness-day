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
	var SNAPSHOT_KEY = 'airb_results_snapshot_v1';
	var VARIANT_KEY = 'airb_audit_variant_v1';
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

	function scrollFlowToTop() {
		if (!isMobileFlow()) return;
		var anchor = (el.root && el.root.querySelector('.airb__appbar')) || el.root;
		if (!anchor) return;
		window.requestAnimationFrame(function () {
			anchor.scrollIntoView({ behavior: 'auto', block: 'start' });
		});
	}

	function updateFlowChrome() {
		if (!el.root) return;
		var inFlow = !!(el.nav && !el.nav.hidden);
		var mobileShell = isMobileFlow() && (state.phase === 'role' || state.phase === 'results' || inFlow);
		el.root.classList.toggle('airb--nav-dock', inFlow);
		el.root.classList.toggle('airb--mobile-flow', mobileShell);
		el.root.classList.toggle('airb--phase-role', state.phase === 'role');
		el.root.classList.toggle('airb--phase-audit', state.phase === 'audit');
		el.root.classList.toggle('airb--phase-results', state.phase === 'results');
		el.root.classList.toggle('airb--intro-collapsed', introCollapsed);
		syncNavPlacement();
		document.body.classList.toggle('airb-flow-active', state.phase === 'audit' || state.phase === 'contact');
		document.body.classList.toggle('airb-results-active', state.phase === 'results');
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
			state.sections = sectionsForRole(state.role);
			if (!state.sections.length) {
				showError(i18n.error);
				return false;
			}
			applyAuditPresentation(state.role);
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
		if (r >= 90) return { slug: 'leading', label: labels.leading || 'Leading' };
		if (r >= 75) return { slug: 'strong', label: labels.strong || 'Strong' };
		if (r >= 60) return { slug: 'established', label: labels.established || 'Established' };
		if (r >= 40) return { slug: 'developing', label: labels.developing || 'Developing' };
		return { slug: 'emerging', label: labels.emerging || 'Emerging' };
	}

	function readinessBandLabel(pct) {
		var r = Math.max(0, Math.min(100, parseInt(pct, 10) || 0));
		if (r >= 60 && r <= 64) {
			return (i18n.bandsReadiness && i18n.bandsReadiness.earlyEstablished) || 'Early Established';
		}
		return readinessLevel(pct).label;
	}

	function readinessBandColor(pct) {
		var colors = {
			leading: '#15803d',
			strong: 'var(--airb-low)',
			established: '#3a8fb0',
			developing: 'var(--airb-mod)',
			emerging: 'var(--airb-crit)',
		};
		return colors[readinessLevel(pct).slug] || 'var(--airb-text)';
	}

	function readinessBandDefinitions() {
		var labels = i18n.bandsReadiness || {};
		return [
			{ slug: 'emerging', label: labels.emerging || 'Emerging', min: 0, max: 39 },
			{ slug: 'developing', label: labels.developing || 'Developing', min: 40, max: 59 },
			{ slug: 'established', label: labels.established || 'Established', min: 60, max: 74 },
			{ slug: 'strong', label: labels.strong || 'Strong', min: 75, max: 89 },
			{ slug: 'leading', label: labels.leading || 'Leading', min: 90, max: 100 },
		];
	}

	function readinessBandShortLabel(slug, fullLabel) {
		var labels = i18n.bandsReadinessShort || {};
		if (labels[slug]) return labels[slug];
		if (fullLabel && /early/i.test(fullLabel)) {
			return labels.earlyEstablished || 'Early est.';
		}
		var defaults = {
			emerging: 'Emerg.',
			developing: 'Dev.',
			established: 'Est.',
			strong: 'Str.',
			leading: 'Lead.',
		};
		return defaults[slug] || fullLabel;
	}

	function readinessBandScaleHtml(score) {
		score = Math.max(0, Math.min(100, parseInt(score, 10) || 0));
		var bandLabel = readinessBandLabel(score);
		var bandColor = readinessBandColor(score);
		var bands = readinessBandDefinitions();
		var aria = (i18n.readinessScaleAria || 'Overall benchmark readiness {score} out of 100, {band}')
			.replace('{score}', String(score))
			.replace('{band}', bandLabel);

		var html = '<div class="airb__readiness-scale" role="img" aria-label="' + esc(aria) + '">';
		html += '<div class="airb__readiness-scale-head">';
		html += '<p class="airb__readiness-scale-kicker">' + esc(i18n.readinessScaleKicker || 'Overall benchmark readiness') + '</p>';
		html += '<div class="airb__readiness-scale-values">';
		html += '<span class="airb__readiness-scale-score" style="color:' + esc(bandColor) + '">' + score + '%</span>';
		html += '<span class="airb__readiness-scale-band" style="color:' + esc(bandColor) + '">' + esc(bandLabel.toUpperCase()) + '</span>';
		html += '</div></div>';
		html += '<p class="airb__readiness-scale-help airb__muted">' + esc(
			i18n.readinessScaleNote || 'This score is calculated from your role-specific audit domains. Other metrics, such as dependency, oversight and governance, are shown separately.'
		) + '</p>';

		html += '<div class="airb__readiness-scale-track">';
		bands.forEach(function (b) {
			var span = b.max - b.min + 1;
			var mid = Math.round((b.min + b.max) / 2);
			var active = score >= b.min && score <= b.max;
			html += '<span class="airb__readiness-scale-seg airb__readiness-scale-seg--' + b.slug + (active ? ' is-active' : '') + '" style="flex:' + span + ' 1 0;background:' + esc(readinessBandColor(mid)) + '" title="' + esc(b.label + ' (' + b.min + '\u2013' + b.max + ')') + '"></span>';
		});
		html += '<span class="airb__readiness-scale-marker" style="left:' + score + '%;" aria-hidden="true"></span>';
		html += '</div>';

		html += '<div class="airb__readiness-scale-labels" aria-hidden="true">';
		bands.forEach(function (b) {
			var span = b.max - b.min + 1;
			var active = score >= b.min && score <= b.max;
			var lab = active && score >= 60 && score <= 64 ? bandLabel : b.label;
			var shortLab = readinessBandShortLabel(b.slug, lab);
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
		return role === 'teacher';
	}

	function studentDisplayScore(raw) {
		var n = Math.max(0, Math.min(100, parseInt(raw, 10) || 0));
		if (n < 5) return 5;
		return n;
	}

	function studentSkillBand(pct) {
		var score = studentDisplayScore(pct);
		var levels = (studentResult.journey_levels || [
			{ slug: 'beginning', label: 'Beginning', min: 0, max: 20 },
			{ slug: 'developing', label: 'Developing', min: 21, max: 40 },
			{ slug: 'emerging', label: 'Emerging', min: 41, max: 60 },
			{ slug: 'confident', label: 'Confident', min: 61, max: 80 },
			{ slug: 'advanced', label: 'Advanced', min: 81, max: 100 },
		]);
		for (var i = 0; i < levels.length; i++) {
			if (score >= levels[i].min && score <= levels[i].max) {
				return { slug: levels[i].slug, label: levels[i].label };
			}
		}
		return { slug: 'beginning', label: 'Beginning' };
	}

	function studentSkillColor(pct) {
		var slug = studentSkillBand(pct).slug;
		var colors = {
			beginning: 'var(--airb-crit)',
			developing: 'var(--airb-mod)',
			emerging: '#3a8fb0',
			confident: 'var(--airb-low)',
			advanced: '#15803d',
		};
		return colors[slug] || readinessBandColor(pct);
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
		scrollFlowToTop();
	}

	function isYoungRole() {
		return state.role === 'student' || state.role === 'parent';
	}

	function isStaffRole() {
		return state.role === 'teacher' || state.role === 'leader';
	}

	function hasInterestForm(r) {
		return !!(r && r.interest_form && r.interest_form.options && r.interest_form.options.length);
	}

	function interestFormButtonLabel() {
		if (isParentRole()) return i18n.requestSupportParent || i18n.requestFullReport || 'Get support';
		if (isStudentRole()) return i18n.requestSupportStudent || i18n.requestFullReport || 'Get support';
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
		if ((isTeacherRole() && r.teacher_results) || (isStudentRole() && r.student_results) || (isLeaderRole() && r.leader_results)) {
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
		domainKeys.forEach(function (slug) {
			var d = r.domain_scores[slug];
			if (!d || !d.questions_answered) return;
			var pct = Math.round(d.readiness_percentage);
			rows += '<div class="airb__res-row"><span class="airb__res-row-nm">' + esc(d.label) + '</span>';
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

	function oversightPanelHtml(r) {
		var val = oversightGaugeValue(r);
		var studentMode = isStudentRole() && !!r.student_results;
		var html = '<div class="airb__res-panel airb__res-panel--gauge"><h3>' + esc(i18n.oversight) + '</h3>';
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

	function parentResultsHtml(r) {
		var pr = r.parent_results;
		if (!pr) return parentFocusDomainsHtml(r);

		var cfg = parentResult;
		var summaryExtra = parentAdvocateSummaryHtml(pr.advocate) + parentConfidenceSummaryHtml(pr.confidence);
		var html = summaryExtra ? resultsSummaryZoneHtml({
			title: cfg.summary_title || 'Your summary',
			extraHtml: summaryExtra
		}) : '';

		var standInner = '';
		if (pr.peer_benchmark) {
			standInner += peerBenchmarkBlockHtml(pr.peer_benchmark, {
				heading: cfg.peer_benchmark_title || 'Parent benchmark comparison',
				averageLabel: i18n.parentAverage || 'Average parent',
				percentileTemplate: (i18n.peerPercentile || 'Your score is ahead of {n}% of similar schools.').replace('similar schools', 'parents'),
				estimatedNote: i18n.peerEstimated || 'Comparison uses reference benchmarks until enough parents have completed the audit.'
			});
		}
		var domainsHtml = domainReadinessRowsHtml(r);
		if (domainsHtml) {
			standInner += resultsAccordionHtml(cfg.domains_accordion_title || 'Your domain scores', domainsHtml);
		}
		var focusHtml = parentFocusDomainsHtml(r);
		if (focusHtml) {
			standInner += resultsAccordionHtml(cfg.focus_accordion_title || i18n.domainFocus || 'What to focus on', focusHtml);
		}
		if (pr.confidence) {
			standInner += resultsAccordionHtml(pr.confidence.title || 'Building your confidence', parentConfidenceDetailHtml(pr.confidence));
		}
		if (pr.advocate) {
			standInner += resultsAccordionHtml(pr.advocate.title || 'Help your school', parentAdvocateDetailHtml(pr.advocate));
		}
		html += resultsStandZoneHtml(cfg.where_you_stand_heading || 'Where you stand', standInner);
		html += resultsActionZoneHtml(pr.next_steps);
		return html;
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
		var tr = r.teacher_results;
		if (!tr || !isTeacherRole()) return '';

		var html = '<section class="airb__teacher-dashboard" aria-label="' + esc(i18n.teacherScoreBreakdown || 'Score breakdown') + '">';
		html += '<h3 class="airb__benchmark-section-title">' + esc(i18n.teacherScoreBreakdown || 'Score breakdown') + '</h3>';
		html += '<p class="airb__benchmark-bridge airb__muted">' + esc(i18n.teacherBreakdownIntro || 'The same metrics as above — shown by domain and in detail.') + '</p>';

		var gaugeHtml = oversightPanelHtml(r);
		var domainsHtml = domainReadinessRowsHtml(r, i18n.readinessScoreByDomain || 'Readiness score — by domain');

		if (gaugeHtml || domainsHtml) {
			html += '<div class="airb__res-two">';
			html += gaugeHtml || '<div></div>';
			html += domainsHtml || '<div></div>';
			html += '</div>';
		}

		var risksHtml = riskIndicatorsHtml(r, i18n.riskScoreDetail || 'AI risk score & AI Dependency Index — detail');
		if (risksHtml) html += risksHtml;

		if (tr.benchmark_summary && tr.benchmark_summary.metrics && tr.benchmark_summary.metrics.length) {
			var tbs = tr.benchmark_summary;
			html += summaryMetricsSectionHtml(
				tbs.metrics,
				tbs.title || i18n.teacherBenchmarkRecap || teacherResult.benchmark_summary_title || 'Teacher Benchmark — score recap',
				{ modifier: 'teacher' }
			);
		}

		return html + '</section>';
	}

	function teacherResultsHtml(r) {
		var tr = r.teacher_results;
		if (!tr) return '';

		var cfg = teacherResult;
		var attention = (tr.opportunities || []).map(function (opp) {
			return opp.label + (typeof opp.pct === 'number' ? ' (' + opp.pct + '%)' : '');
		});
		var priority = tr.gap_pathway && tr.gap_pathway.items && tr.gap_pathway.items.length ? tr.gap_pathway.items[0] : null;

		var html = resultsSummaryZoneHtml({
			title: cfg.summary_title || 'Your summary',
			intro: tr.performance_headline,
			strengths: tr.strengths,
			strengthsHeading: cfg.strengths_heading || 'What you\'re doing well',
			attention: attention,
			attentionHeading: cfg.opportunities_heading || 'Opportunities to strengthen further',
			priority: priority
		});

		var standInner = '';
		if (tr.school_contribution && tr.school_contribution.heading) {
			var sc = tr.school_contribution;
			var scHtml = '<div class="airb__res-panel airb__res-panel--contribution">';
			scHtml += '<p>' + esc(sc.intro || '') + '</p>';
			if (sc.items && sc.items.length) {
				scHtml += '<ul class="airb__resource-list">';
				sc.items.forEach(function (item) { scHtml += '<li>' + esc(item) + '</li>'; });
				scHtml += '</ul>';
			}
			if (sc.closing) scHtml += '<p class="airb__muted">' + esc(sc.closing) + '</p>';
			scHtml += '</div>';
			standInner += resultsAccordionHtml(sc.heading, scHtml);
		}
		if (tr.school_impact && tr.school_impact.heading) {
			var si = tr.school_impact;
			var siHtml = '<div class="airb__res-panel airb__res-panel--contribution">';
			siHtml += '<p>' + esc(si.intro || '') + '</p>';
			if (si.items && si.items.length) {
				siHtml += '<ul class="airb__resource-list">';
				si.items.forEach(function (item) { siHtml += '<li>' + esc(item) + '</li>'; });
				siHtml += '</ul>';
			}
			if (si.closing) siHtml += '<p class="airb__muted">' + esc(si.closing) + '</p>';
			siHtml += '</div>';
			standInner += resultsAccordionHtml(si.heading, siHtml);
		}
		if (tr.next_steps && tr.next_steps.rollout) {
			standInner += resultsAccordionHtml(
				tr.next_steps.rollout.title || 'Whole-school teacher benchmark',
				teacherRolloutAccordionBody(tr.next_steps.rollout)
			);
		}
		html += resultsStandZoneHtml(cfg.where_you_stand_heading || 'Where you stand', standInner);
		html += resultsActionZoneHtml(tr.next_steps);
		return html;
	}

	function studentResultsHtml(r) {
		var sr = r.student_results;
		if (!sr) return '';

		var cfg = studentResult;
		var html = resultsSummaryZoneHtml({
			title: cfg.summary_title || 'Your summary',
			intro: sr.performance_headline,
			strengths: sr.strengths,
			strengthsHeading: cfg.strengths_heading || 'What you\'re doing well'
		});

		var standInner = '';
		if (sr.peer_benchmark) {
			standInner += peerBenchmarkBlockHtml(sr.peer_benchmark, {
				heading: cfg.peer_benchmark_title || 'Students like you',
				averageLabel: i18n.studentAverage || 'Average students',
				percentileTemplate: i18n.studentPeerPercentile || 'You scored higher than {n}% of students who completed this benchmark.',
				estimatedNote: i18n.studentPeerEstimated || i18n.peerEstimated || 'Comparison uses reference benchmarks until enough students have completed the audit.'
			});
		}
		if (sr.learning_journey && sr.learning_journey.current_label) {
			var lj = sr.learning_journey;
			var ljHtml = '<section class="airb__learning-journey">';
			ljHtml += '<div class="airb__journey-steps">';
			ljHtml += '<div class="airb__journey-step airb__journey-step--current"><span class="airb__journey-label">' + esc(i18n.journeyCurrent || 'Current') + '</span><strong>' + esc(lj.current_label) + '</strong></div>';
			if (lj.next_label) {
				ljHtml += '<div class="airb__journey-arrow" aria-hidden="true">→</div>';
				ljHtml += '<div class="airb__journey-step airb__journey-step--next"><span class="airb__journey-label">' + esc(i18n.journeyNext || 'Next') + '</span><strong>' + esc(lj.next_label) + '</strong></div>';
			}
			ljHtml += '</div>';
			if (lj.focus_items && lj.focus_items.length) {
				ljHtml += '<ul class="airb__journey-focus">';
				lj.focus_items.forEach(function (item) {
					ljHtml += '<li><span class="airb__strength-mark" aria-hidden="true">✓</span> ' + esc(item) + '</li>';
				});
				ljHtml += '</ul>';
			}
			if (lj.retake_note) ljHtml += '<p class="airb__muted">' + esc(lj.retake_note) + '</p>';
			ljHtml += '</section>';
			standInner += resultsAccordionHtml(cfg.journey_accordion_title || lj.title || 'Your AI learning journey', ljHtml);
		}
		if (sr.opportunities && sr.opportunities.length) {
			standInner += resultsAccordionHtml(
				cfg.opportunities_accordion_title || cfg.opportunities_heading || 'Opportunities to improve',
				opportunitiesSectionHtml(sr.opportunities, '', 'student')
			);
		}
		if (sr.school_contribution && sr.school_contribution.heading) {
			var sc = sr.school_contribution;
			var scHtml = '<div class="airb__res-panel airb__res-panel--contribution"><p>' + esc(sc.body || '') + '</p></div>';
			standInner += resultsAccordionHtml(sc.heading, scHtml);
		}
		html += resultsStandZoneHtml(cfg.where_you_stand_heading || 'Where you stand', standInner);
		html += resultsActionZoneHtml(sr.next_steps);
		return html;
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
		html += '<tr><th scope="row">' + esc(i18n.topQuartile || 'Top quartile') + '</th><td>' + pb.top_quartile + '%</td></tr>';
		html += '</tbody></table>';
		if (pb.is_estimated) {
			html += '<p class="airb__muted airb__peer-note">' + esc(opts.estimatedNote || i18n.peerEstimated || 'Comparison uses reference benchmarks until enough responses have been collected.') + '</p>';
		} else if (typeof pb.percentile === 'number') {
			var pctTpl = opts.percentileTemplate || i18n.peerPercentile || 'Your score is ahead of {n}% of similar schools.';
			html += '<p class="airb__muted airb__peer-note">' + esc(pctTpl.replace('{n}', String(pb.percentile))) + '</p>';
		}
		return html + '</div>';
	}

	function resultsResourceLinksHtml(links) {
		if (!links || !links.length) return '';
		var html = '<ul class="airb__results-resource-links airb__leader-resource-links">';
		links.forEach(function (link) {
			html += '<li>';
			if (link.external && link.url) {
				html += '<a href="' + esc(link.url) + '" target="_blank" rel="noopener noreferrer">' + esc(link.label) + '</a>';
			} else if (link.prefill) {
				html += '<button type="button" class="airb__results-resource-link airb__leader-resource-link" data-airb-open-interest="' + esc(link.prefill) + '">' + esc(link.label) + '</button>';
			} else if (link.url) {
				html += '<a href="' + esc(link.url) + '">' + esc(link.label) + '</a>';
			}
			html += '</li>';
		});
		return html + '</ul>';
	}

	function resultsActionZoneHtml(nextSteps) {
		if (!nextSteps || !nextSteps.hero) return '';
		var hero = nextSteps.hero;
		var html = '<section class="' + resultsZoneClass('action') + '">';
		html += '<p class="airb__next-step-hero-label">' + esc(nextSteps.hero_heading || i18n.recommendedNextStep || 'Your next step') + '</p>';
		html += '<article class="airb__next-step-hero airb__next-step-hero--primary">';
		html += '<h4 class="airb__next-step-hero-title">' + esc(hero.title) + '</h4>';
		if (hero.body) html += '<p>' + esc(hero.body) + '</p>';
		if (hero.understand_items && hero.understand_items.length) {
			html += '<ul class="airb__leader-actions">';
			hero.understand_items.forEach(function (item) {
				html += '<li>' + esc(item) + '</li>';
			});
			html += '</ul>';
		}
		if (hero.deliverables && hero.deliverables.length) {
			html += '<ul class="airb__leader-actions airb__leader-actions--deliverables">';
			hero.deliverables.forEach(function (item) {
				html += '<li>' + esc(item) + '</li>';
			});
			html += '</ul>';
		}
		if (hero.cta_type === 'link' && hero.cta_url) {
			html += '<a class="airb__btn airb__btn--primary airb__btn--hero" href="' + esc(hero.cta_url) + '">' + esc(hero.cta_text || 'Continue') + '</a>';
		} else {
			html += '<button type="button" class="airb__btn airb__btn--primary airb__btn--hero" data-airb-open-interest="' + esc(hero.key || 'governance_review') + '">' + esc(hero.cta_text || 'Request support') + '</button>';
		}
		html += '</article>';
		if (nextSteps.resource_links && nextSteps.resource_links.length) {
			html += '<h5 class="airb__results-read-more-heading">' + esc(i18n.moreToRead || 'More to read') + '</h5>';
		}
		html += resultsResourceLinksHtml(nextSteps.resource_links);
		return html + '</section>';
	}

	function resultsSummaryZoneHtml(options) {
		options = options || {};
		if (!options.title && !options.intro && !(options.strengths && options.strengths.length) && !(options.attention && options.attention.length) && !options.priority && !options.extraHtml) {
			return '';
		}
		var html = '<section class="' + resultsZoneClass('summary') + ' airb__exec-summary">';
		if (options.title) html += '<h3>' + esc(options.title) + '</h3>';
		if (options.intro) html += '<p class="airb__exec-intro">' + esc(options.intro) + '</p>';
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
			html += '<ul class="airb__attention-list">';
			options.attention.forEach(function (item) {
				html += '<li><span class="airb__attention-mark" aria-hidden="true">⚠</span> ' + esc(item) + '</li>';
			});
			html += '</ul>';
		}
		if (options.priority) {
			html += '<p class="airb__exec-priority"><strong>' + esc(i18n.priorityAction || 'Priority action') + ':</strong> ' + esc(options.priority) + '</p>';
		}
		if (options.maturity && options.maturity.score != null) {
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
			html += '<ul class="airb__unlock-benefits">';
			ro.unlock_benefits.forEach(function (item) {
				html += '<li><span class="airb__strength-mark" aria-hidden="true">✓</span> ' + esc(item) + '</li>';
			});
			html += '</ul>';
		}
		if (ro.counts) {
			html += '<ul class="airb__rollout-counts">';
			html += '<li>' + esc(i18n.roleLeader || 'Leaders') + ': ' + (ro.counts.leader || 0) + '</li>';
			html += '<li>' + esc(i18n.roleTeacher || 'Teachers') + ': ' + (ro.counts.teacher || 0) + '</li>';
			html += '<li>' + esc(i18n.roleStudent || 'Students') + ': ' + (ro.counts.student || 0) + '</li>';
			html += '<li>' + esc(i18n.roleParent || 'Parents') + ': ' + (ro.counts.parent || 0) + '</li>';
			html += '</ul>';
		}
		if (!ro.unlocked) {
			var unlockCopy = (ro.unlock_copy || leaderResult.rollout_unlock_copy || 'Unlocks after {threshold} responses from your school community.')
				.replace('{threshold}', String(ro.threshold || 20))
				.replace('{remaining}', String(ro.remaining || ro.threshold || 20));
			html += '<p class="airb__rollout-unlock airb__muted">' + esc(unlockCopy) + '</p>';
		}
		html += '<p><button type="button" class="airb-hub-btn airb-hub-btn--secondary" data-airb-open-interest="whole_school_benchmark">' + esc(i18n.rolloutBenchmark || 'Roll out to all groups') + '</button></p>';
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

	function leaderFocusAreasHtml(focusAreas) {
		if (!focusAreas || !focusAreas.length) return '';
		var html = '';
		focusAreas.forEach(function (area) {
			html += '<div class="airb__res-rec airb__res-rec--leader">';
			html += '<div class="airb__leader-focus-head">';
			html += '<h4 class="airb__res-rec-title">' + esc(area.label) + ' — ' + area.pct + '%</h4>';
			if (area.summary) html += '<p class="airb__res-rec-body">' + esc(area.summary) + '</p>';
			html += '</div>';
			if ((area.likely_impact && area.likely_impact.length) || (area.actions && area.actions.length)) {
				html += '<div class="airb__leader-focus-grid">';
				if (area.likely_impact && area.likely_impact.length) {
					html += '<div class="airb__leader-focus-col airb__leader-focus-col--impact">';
					html += '<p class="airb__impact-label"><strong>' + esc(i18n.likelyImpact || 'Likely impact') + '</strong></p>';
					html += '<p class="airb__impact-intro">' + esc(leaderResult.likely_impact_intro || 'Schools with scores at this level often experience:') + '</p>';
					html += '<ul class="airb__impact-list">';
					area.likely_impact.forEach(function (item) {
						html += '<li>' + esc(item) + '</li>';
					});
					html += '</ul></div>';
				}
				if (area.actions && area.actions.length) {
					html += '<div class="airb__leader-focus-col airb__leader-focus-col--actions">';
					html += '<p class="airb__actions-label">' + esc(leaderResult.focus_actions_label || 'Recommended actions') + ':</p>';
					html += '<ul class="airb__leader-actions">';
					area.actions.forEach(function (act) {
						html += '<li>' + esc(act) + '</li>';
					});
					html += '</ul></div>';
				}
				html += '</div>';
			}
			html += '</div>';
		});
		return html;
	}

	function leaderResultsHtml(r) {
		var lr = r.leader_results;
		if (!lr) return '';

		var es = lr.executive_summary;
		var html = resultsSummaryZoneHtml({
			title: es ? (es.title || leaderResult.executive_title || 'Executive Summary') : '',
			intro: es ? es.intro : '',
			strengths: es ? es.strengths : [],
			strengthsHeading: es ? (es.strengths_heading || leaderResult.strengths_heading || 'Strengths include') : '',
			attention: es ? es.attention_areas : [],
			attentionHeading: es ? (es.attention_heading || leaderResult.attention_heading || 'Areas requiring attention') : '',
			priority: es ? es.priority_action : null,
			maturity: lr.maturity
		});

		var standInner = '';
		if (lr.peer_benchmark) {
			var pb = lr.peer_benchmark;
			standInner += peerBenchmarkBlockHtml(pb, {
				heading: leaderResult.peer_benchmark_title || 'Benchmark against similar schools',
				averageLabel: pb.phase_label || 'Average school'
			});
		}
		if (lr.focus_areas && lr.focus_areas.length) {
			var focusInner = '<div class="airb__res-panel airb__res-panel--focus airb__res-panel--leader-focus airb__res-panel--leader-focus-accordion">' +
				leaderFocusAreasHtml(lr.focus_areas) + '</div>';
			standInner += resultsAccordionHtml(
				(leaderResult.focus_heading || 'Priority focus areas') + ' (' + lr.focus_areas.length + ')',
				focusInner
			);
		}
		if (lr.risk_heatmap && lr.risk_heatmap.length) {
			standInner += resultsAccordionHtml(leaderResult.heatmap_heading || i18n.heatMap || 'Risk heat map', heatmapHtml(lr.risk_heatmap));
		}
		if (lr.next_steps && lr.next_steps.rollout) {
			standInner += resultsAccordionHtml(
				lr.next_steps.rollout.title || 'Whole-School AI Benchmark',
				leaderRolloutAccordionBody(lr.next_steps.rollout)
			);
		}
		html += resultsStandZoneHtml(leaderResult.where_you_stand_heading || 'Where your school stands', standInner);
		html += resultsActionZoneHtml(lr.next_steps);
		return html;
	}

	function resultsProfileHtml(r) {
		var roleLbl = (cfg.roles || {})[state.role] || state.role;
		var readiness = r.alignment_score;
		var risk = Math.round(r.overall_risk_percentage);
		var depVal = roleShowsDependency(state.role) ? r.dependency_index : null;
		var eyebrow = (i18n.resultsRoleResult || '{role} result').replace('{role}', roleLbl);
		var parentMode = isParentRole();
		var studentMode = isStudentRole() && !!r.student_results;
		var sr = r.student_results;
		var leaderMode = isLeaderRole() && !!r.leader_results;
		var teacherBenchmarkMode = isTeacherRole() && !!r.teacher_results;
		var bandSummary = parentMode ? parentBandSummary(readiness) : '';
		var indepVal = null;
		if (studentMode && sr && sr.learning_metrics) {
			sr.learning_metrics.forEach(function (m) {
				if (m.slug === 'independent_thinking') indepVal = m.value;
			});
		}

		var html = '<section class="airb__res-profile' +
			(parentMode ? ' airb__res-profile--parent' : '') +
			(studentMode ? ' airb__res-profile--student' : '') +
			(leaderMode ? ' airb__res-profile--leader' : '') +
			(teacherBenchmarkMode ? ' airb__res-profile--teacher' : '') +
			'">';
		html += '<span class="airb__res-eyebrow"><span class="airb__res-eyebrow-dot" aria-hidden="true"></span>' + esc(eyebrow) + '</span>';
		html += '<div class="airb__res-shead">';
		html += '<h2 class="airb__res-title">' + esc(
			teacherBenchmarkMode ? (i18n.teacherResultsTitle || 'Teacher Benchmark results') :
			(studentMode ? (i18n.studentResultsTitle || 'Your AI learning results') : (i18n.resultsProfileTitle || i18n.resultsTitle || 'Your AI Risk & Readiness profile'))
		) + '</h2>';
		html += '</div>';
		html += readinessBandScaleHtml(readiness);

		if (teacherBenchmarkMode) {
			html += '<div class="airb__benchmark-overall">';
			html += '<h3 class="airb__benchmark-section-title">' + esc(i18n.teacherOverallScores || 'Overall scores') + '</h3>';
			html += '<p class="airb__benchmark-bridge airb__muted">' + esc(i18n.teacherOverallIntro || 'Headline scores for your Teacher Benchmark — score breakdown follows below.') + '</p>';
		}

		if (studentMode && sr) {
			html += studentLearningProfileHtml(sr);
			html += studentLearnerTypeHtml(sr);
		}

		html += '<div class="airb__res-grid3' + (parentMode ? ' airb__res-grid3--two' : '') + (studentMode ? ' airb__res-grid3--student airb__res-grid3--two' : '') + '">';
		html += '<div class="airb__res-stat">';
		html += '<div class="airb__res-stat-lab">' + esc(studentMode ? (i18n.statLearningReadiness || 'Learning readiness') : (i18n.statReadiness || 'Readiness score')) + '</div>';
		html += '<div class="airb__res-stat-big" style="color:' + esc(readinessBandColor(readiness)) + '" data-count="' + readiness + '">' + readiness + '%</div>';
		if (!parentMode && !studentMode && !leaderMode) {
			html += '<div class="airb__res-stat-note">' + esc(
				teacherBenchmarkMode ? (i18n.statReadinessNoteTeacher || 'Overall score — see readiness breakdown below.') :
				(i18n.statReadinessNote || 'Weighted across every domain in this audit.')
			) + '</div>';
		} else if (studentMode) {
			html += '<div class="airb__res-stat-note">' + esc(readinessBandLabel(readiness)) + '</div>';
		} else if (leaderMode) {
			html += '<div class="airb__res-stat-note">' + esc(readinessBandLabel(readiness)) + '</div>';
		}
		html += '</div>';

		if (!studentMode) {
		html += '<div class="airb__res-stat">';
		html += '<div class="airb__res-stat-lab">' + esc(i18n.statRisk || 'AI risk score') + '</div>';
		html += '<div class="airb__res-stat-big" style="color:' + esc(riskScoreColor(risk)) + '" data-count="' + risk + '">' + risk + '%</div>';
		if (!parentMode && !leaderMode) {
			html += '<div class="airb__res-stat-note">' + esc(
				teacherBenchmarkMode ? (i18n.statRiskNoteTeacher || 'Inverse of readiness — see detail below.') :
				(i18n.statRiskNote || 'Behavioural exposure — the inverse of readiness.')
			) + '</div>';
		}
		html += '</div>';
		}

		if (!parentMode) {
		html += '<div class="airb__res-stat">';
		if (leaderMode) {
			var govScore = r.governance_maturity != null ? r.governance_maturity : null;
			html += '<div class="airb__res-stat-lab">' + esc(i18n.governanceMaturity || 'Governance Maturity') + '</div>';
			if (govScore === null) {
				html += '<div class="airb__res-stat-big airb__res-stat-big--na">—</div>';
			} else {
				html += '<div class="airb__res-stat-big" style="color:' + esc(readinessBandColor(govScore)) + '" data-count="' + govScore + '">' + govScore + '%</div>';
				html += '<div class="airb__res-stat-note">' + esc(readinessBandLabel(govScore)) + '</div>';
			}
		} else if (studentMode) {
			html += '<div class="airb__res-stat-lab">' + esc(i18n.independentThinking || 'Independent Thinking') + '</div>';
			if (indepVal === null) {
				html += '<div class="airb__res-stat-big airb__res-stat-big--na">—</div>';
			} else {
				html += '<div class="airb__res-stat-big" style="color:' + esc(studentSkillColor(indepVal)) + '" data-count="' + indepVal + '">' + indepVal + '%</div>';
				html += '<div class="airb__res-stat-note">' + esc(studentSkillBand(indepVal).label) + '</div>';
			}
		} else {
		html += '<div class="airb__res-stat-lab">' + esc(i18n.dependency || 'AI Dependency Index') + '</div>';
		if (depVal === null) {
			html += '<div class="airb__res-stat-big airb__res-stat-big--na">—</div>';
			html += '<div class="airb__res-stat-note">' + esc(i18n.statDepNa || 'Not measured for this audience.') + '</div>';
		} else {
			html += '<div class="airb__res-stat-big" style="color:' + esc(dependencyColor(depVal)) + '" data-count="' + depVal + '">' + depVal + '%</div>';
			html += '<div class="airb__res-stat-note">' + esc(
				teacherBenchmarkMode ? (i18n.statDepNoteTeacher || 'Same metric as in score recap below.') :
				(i18n.statDepNote || 'Risk indicator — higher means greater reliance on AI.')
			) + '</div>';
		}
		}
		html += '</div>';
		}
		html += '</div>';

		if (teacherBenchmarkMode) {
			html += '</div>';
		}

		if (bandSummary) {
			html += '<p class="airb__res-summary">' + esc(bandSummary) + '</p>';
		}

		if (leaderMode) {
			/* Governance content follows in leaderResultsHtml. */
		} else if (isTeacherRole() && r.teacher_results) {
			html += teacherDashboardHtml(r);
		} else if (!parentMode && !studentMode) {
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
		if (isParentRole() || isStudentRole() || isLeaderRole() || isTeacherRole()) return '';
		var gi = r.guided_improvement;
		if (r.parent_results && r.parent_results.suppress_improvement) return '';
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
		} else if (parentMode) {
			html += parentResultsHtml(r);
		} else {
			html += focusDomainsHtml(r);
		}

		html += guidedImprovementHtml(r);

		if (state.school && isStaffRole()) {
			html += '<div id="airb-school-snapshot" class="airb__school-snapshot" aria-live="polite" hidden></div>';
		}

		if (!parentMode && !teacherMode && !studentMode && !leaderMode) {
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
			if (pg.cta_url) html += resultsCtaHtml(pg.cta_url, pg.cta_text, '', 'data-airb-track="policy_support"');
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

		html += interestFormHtml(r);

		var shareHint = shareResultsHintText(r);
		if (shareHint) {
			html += '<p class="airb__muted airb__share-hint">' + esc(shareHint) + '</p>';
		}

		if (!hasInterestForm(r)) {
			html += '<div class="airb__results-actions">';
			var shareMailto = buildShareResultsMailto();
			if (shareMailto) {
				html += '<a class="airb__btn airb__btn--primary airb__btn--share" href="' + shareMailto + '" id="airb-share-results">' + esc(i18n.shareWithSchool || 'Share results with your school') + '</a>';
			}
			if (state.email) {
				html += '<button type="button" class="airb__btn airb__btn--ghost" id="airb-email-report">' + esc(i18n.emailReport) + '</button>';
			}
			html += '</div>';
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
		bindInterestForm();
		bindInterestTriggers();
		fetchSchoolSnapshot();

		animateResultsStats();
		persistRoleCompletion(r.alignment_score);
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
			scrollFlowToTop();
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
				persistResultsSnapshot();
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

	function interestFormHtml(r) {
		if (!hasInterestForm(r)) return '';
		var form = r.interest_form;
		var labels = form.labels || {};
		var fields = form.fields || {};
		var suggested = form.suggested || [];
		var weak = form.weak_domains || [];
		var summary = form.summary || {};

		var html = '<section class="airb__interest" id="airb-interest" aria-labelledby="airb-interest-heading">';
		html += '<h3 class="airb__interest-heading" id="airb-interest-heading">' + esc(labels.heading || '') + '</h3>';
		html += gatewayInfoHtml(r);
		if (labels.intro) html += '<p class="airb__muted airb__interest-intro">' + esc(labels.intro) + '</p>';

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

		if (fields.show_stakeholder_role && form.stakeholder_roles) {
			html += '<fieldset class="airb__interest-stakeholder"><legend class="airb__interest-legend">' + esc(labels.stakeholder_role || 'Which best describes you?') + '</legend>';
			Object.keys(form.stakeholder_roles).forEach(function (key) {
				var inputId = 'airb-stakeholder-' + key;
				html += '<label class="airb__interest-option airb__interest-option--radio" for="' + esc(inputId) + '">';
				html += '<input type="radio" id="' + esc(inputId) + '" name="stakeholder_role" value="' + esc(key) + '">';
				html += '<span class="airb__interest-option-text"><strong>' + esc(form.stakeholder_roles[key]) + '</strong></span></label>';
			});
			html += '</fieldset>';
		}

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
		var section = document.getElementById('airb-interest');
		if (!section) return;
		if (prefill) {
			section.querySelectorAll('input[name="interests[]"]').forEach(function (input) {
				if (input.value === prefill) input.checked = true;
			});
		}
		section.scrollIntoView({ behavior: 'smooth', block: 'start' });
		var focusTarget = section.querySelector('input[name="interests[]"]:checked') || section.querySelector('input[name="interest_email"]') || section.querySelector('.airb__interest-submit');
		if (focusTarget && focusTarget.focus) {
			focusTarget.focus({ preventScroll: true });
		}
	}

	function bindInterestTriggers() {
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
		var stakeholderInput = form.querySelector('input[name="stakeholder_role"]:checked');
		body.append('stakeholder_role', stakeholderInput ? stakeholderInput.value : '');
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
				form.querySelectorAll('input[name="interests[]"], input[name="interest_name"], input[name="interest_school"], input[name="interest_child_school"], textarea[name="interest_message"]').forEach(function (el) {
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
			html += '<p class="airb__muted airb__school-snapshot-incomplete">' + esc(i18n.schoolSnapshotIncomplete || 'Roll out the benchmark to all four groups for a complete whole-school picture.') + '</p>';
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
})();
