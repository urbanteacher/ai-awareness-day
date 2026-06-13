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

	var state = {
		phase: 'role',
		role: '',
		step: 0,
		questions: [],
		answers: {},
		results: null,
		school: '',
		email: '',
		consent: false,
		schoolPhase: '',
		orgType: '',
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
	};

	function riskBand(pct) {
		if (pct <= 30) return 'low';
		if (pct <= 60) return 'moderate';
		if (pct <= 80) return 'high';
		return 'critical';
	}

	function bandLabel(band) {
		return band.charAt(0).toUpperCase() + band.slice(1);
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
		return riskBand(Math.max(0, 100 - readiness));
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
		var label = modifyPct !== null ? oversightLabel(modifyPct) :
			(readiness >= 51 ? 'Strong oversight' : readiness >= 26 ? 'Moderate oversight' : readiness >= 11 ? 'High reliance' : 'Critical reliance');
		return { pct: modifyPct, label: label, readiness: readiness };
	}

	function roleResultCards(role, results) {
		var dom = results.domain_scores || {};
		var cards = [];
		if (role === 'teacher') {
			cards = [
				{ label: 'Teacher AI Risk Score', value: Math.round(results.overall_risk_percentage) + '%', band: results.risk_level },
				{ label: 'Teacher AI Readiness Score', value: results.alignment_score + '/100', band: readinessBand(results.alignment_score) },
				{ label: 'AI Dependency Index™', value: results.dependency_index + '%', band: riskBand(results.dependency_index) },
				{ label: 'Human Oversight Ratio™', value: results.human_oversight_label, band: results.risk_level },
			];
		} else if (role === 'student') {
			var lit = Math.round(dom.ai_literacy ? dom.ai_literacy.readiness_percentage : 0);
			cards = [
				{ label: 'Student Learning Risk Score', value: Math.round(results.overall_risk_percentage) + '%', band: results.risk_level },
				{ label: 'Student AI Dependency Score', value: results.dependency_index + '%', band: riskBand(results.dependency_index) },
				{ label: 'Student AI Literacy Score', value: lit + '%', band: readinessBand(lit) },
			];
		} else if (role === 'parent') {
			var aware = Math.round(dom.ai_literacy ? dom.ai_literacy.readiness_percentage : results.alignment_score);
			var safe = Math.round(dom.safeguarding ? dom.safeguarding.readiness_percentage : results.safeguarding_readiness);
			cards = [
				{ label: 'Parent AI Awareness Score', value: aware + '%', band: readinessBand(aware) },
				{ label: 'Parent Digital Safety Score', value: safe + '%', band: readinessBand(safe) },
				{ label: 'Parent Readiness Score', value: results.alignment_score + '/100', band: readinessBand(results.alignment_score) },
			];
		} else if (role === 'leader') {
			cards = [
				{ label: 'Governance Maturity Score', value: results.governance_maturity + '%', band: readinessBand(results.governance_maturity) },
				{ label: 'Safeguarding Readiness Score', value: results.safeguarding_readiness + '%', band: readinessBand(results.safeguarding_readiness) },
				{ label: 'DfE AI Alignment Score', value: results.alignment_score + '/100', band: readinessBand(results.alignment_score) },
			];
		}
		return cards;
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
			domainScores[slug] = {
				label: domains[slug],
				risk_percentage: Math.round(avg * 10) / 10,
				readiness_percentage: Math.round((100 - avg) * 10) / 10,
				band: band,
				band_label: bandLabel(band),
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
		results.role_result_cards = roleResultCards(role, results);
		return results;
	}

	function showError(msg) {
		el.error.textContent = msg;
		el.error.hidden = false;
	}

	function hideError() {
		el.error.hidden = true;
		el.error.textContent = '';
	}

	function questionsForRole(role) {
		return (cfg.questions || []).filter(function (q) { return q.role === role; });
	}

	function renderRole() {
		var html = '<div class="airb__panel"><h3 class="airb__panel-title">' + esc(i18n.chooseRole) + '</h3><div class="airb__role-grid">';
		var benchmarks = cfg.role_benchmarks || {};
		Object.keys(cfg.roles || {}).forEach(function (slug) {
			var active = state.role === slug ? ' is-selected' : '';
			var bench = benchmarks[slug] || {};
			html += '<button type="button" class="airb__role-card' + active + '" data-role="' + esc(slug) + '">';
			html += '<span class="airb__role-card-title">' + esc(cfg.roles[slug]) + '</span>';
			if (bench.title) html += '<span class="airb__role-card-sub">' + esc(bench.title) + '</span>';
			html += '</button>';
		});
		html += '</div>';
		if (state.role && benchmarks[state.role]) {
			var b = benchmarks[state.role];
			html += '<div class="airb__role-detail">';
			if (b.measures && b.measures.length) {
				html += '<p class="airb__role-detail-label">' + esc(i18n.measures) + '</p><ul class="airb__role-detail-list">';
				b.measures.forEach(function (m) { html += '<li>' + esc(m) + '</li>'; });
				html += '</ul>';
			}
			if (b.outputs && b.outputs.length) {
				html += '<p class="airb__role-detail-label">' + esc(i18n.outputs) + '</p><ul class="airb__role-detail-list airb__role-detail-list--outputs">';
				b.outputs.forEach(function (o) { html += '<li>' + esc(o) + '</li>'; });
				html += '</ul>';
			}
			html += '</div>';
		}
		html += '</div>';
		el.role.innerHTML = html;
		el.role.hidden = false;
		el.audit.hidden = true;
		el.contact.hidden = true;
		el.results.hidden = true;
		el.nav.hidden = false;
		el.back.hidden = true;
		el.next.textContent = i18n.next;
		el.progress.hidden = true;

		el.role.querySelectorAll('[data-role]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				state.role = btn.getAttribute('data-role');
				renderRole();
			});
		});
	}

	function renderAuditQuestion() {
		var q = state.questions[state.step];
		if (!q) return;

		var html = '<div class="airb__panel"><p class="airb__q-num">' + i18n.step + ' ' + (state.step + 1) + ' ' + i18n.of + ' ' + state.questions.length + '</p>';
		if (q.section) {
			html += '<p class="airb__audit-section">' + esc(q.section) + '</p>';
		}
		html += '<h3 class="airb__panel-title">' + esc(q.text) + '</h3>';
		html += '<p class="airb__domain-tag">' + esc(domains[q.domain] || q.domain) + '</p>';

		if (q.type === 'slider') {
			var val = state.answers[q.id] !== undefined ? state.answers[q.id] : 50;
			html += '<div class="airb__slider-wrap"><input type="range" class="airb__slider" id="airb-q-input" min="0" max="100" step="1" value="' + val + '" />';
			html += '<output class="airb__slider-out" for="airb-q-input">' + val + '% ' + esc(i18n.modifyLabel) + '</output></div>';
		} else if (q.type === 'select') {
			html += '<select class="airb__select" id="airb-q-input"><option value="">' + esc(i18n.required) + '</option>';
			(q.options || []).forEach(function (o) {
				var sel = state.answers[q.id] === o.value ? ' selected' : '';
				html += '<option value="' + esc(o.value) + '"' + sel + '>' + esc(o.label) + '</option>';
			});
			html += '</select>';
		} else {
			html += '<div class="airb__options">';
			(q.options || []).forEach(function (o) {
				var checked = state.answers[q.id] === o.value ? ' checked' : '';
				html += '<label class="airb__option"><input type="radio" name="airb-q" value="' + esc(o.value) + '"' + checked + ' />' + esc(o.label) + '</label>';
			});
			html += '</div>';
		}
		html += '</div>';

		el.audit.innerHTML = html;
		el.audit.hidden = false;
		el.role.hidden = true;
		el.contact.hidden = true;
		el.results.hidden = true;
		el.nav.hidden = false;
		el.back.hidden = state.step === 0;
		el.next.textContent = state.step === state.questions.length - 1 ? i18n.submit : i18n.next;
		el.progress.hidden = false;
		updateStepper(state.questions.length, state.step);

		var slider = document.getElementById('airb-q-input');
		if (slider && slider.type === 'range') {
			var out = el.audit.querySelector('.airb__slider-out');
			slider.addEventListener('input', function () {
				if (out) out.textContent = slider.value + '% ' + i18n.modifyLabel;
			});
		}
	}

	function renderContact() {
		var html = '<div class="airb__panel"><h3 class="airb__panel-title">' + esc(i18n.contactTitle || i18n.schoolOptional) + '</h3>';
		if (i18n.contactHint) html += '<p class="airb__muted">' + esc(i18n.contactHint) + '</p>';
		html += '<label class="airb__label" for="airb-school">' + esc(i18n.schoolOptional) + '</label>' +
			'<input type="text" class="airb__input" id="airb-school" value="' + esc(state.school) + '" autocomplete="organization" />';

		if (state.role === 'leader' || state.role === 'teacher') {
			html += '<label class="airb__label" for="airb-school-phase">' + esc(i18n.schoolPhase) + '</label>' +
				'<select class="airb__select" id="airb-school-phase">' +
				'<option value="">' + esc(i18n.schoolPhaseChoose) + '</option>' +
				'<option value="primary"' + (state.schoolPhase === 'primary' ? ' selected' : '') + '>' + esc(i18n.schoolPhasePrimary) + '</option>' +
				'<option value="secondary"' + (state.schoolPhase === 'secondary' ? ' selected' : '') + '>' + esc(i18n.schoolPhaseSecondary) + '</option>' +
				'<option value="all_through"' + (state.schoolPhase === 'all_through' ? ' selected' : '') + '>' + esc(i18n.schoolPhaseAllThrough) + '</option>' +
				'</select>' +
				'<label class="airb__label" for="airb-org-type">' + esc(i18n.orgType) + '</label>' +
				'<select class="airb__select" id="airb-org-type">' +
				'<option value="">' + esc(i18n.orgTypeChoose) + '</option>' +
				'<option value="standalone"' + (state.orgType === 'standalone' ? ' selected' : '') + '>' + esc(i18n.orgStandalone) + '</option>' +
				'<option value="mat"' + (state.orgType === 'mat' ? ' selected' : '') + '>' + esc(i18n.orgMat) + '</option>' +
				'</select>' +
				'<p class="airb__muted airb__profile-hint">' + esc(i18n.profileHint) + '</p>';
		}

		html += '<label class="airb__label" for="airb-email">' + esc(i18n.emailOptional) + '</label>' +
			'<input type="email" class="airb__input" id="airb-email" value="' + esc(state.email) + '" autocomplete="email" />' +
			'<label class="airb__consent"><input type="checkbox" id="airb-consent" ' + (state.consent ? 'checked' : '') + ' /> ' + esc(i18n.consentLabel) + '</label></div>';

		el.contact.innerHTML = html;

		el.contact.hidden = false;
		el.role.hidden = true;
		el.audit.hidden = true;
		el.results.hidden = true;
		el.back.hidden = false;
		el.next.textContent = i18n.submit;
	}

	function barHtml(label, pct, band, invert) {
		var display = invert ? (100 - pct) : pct;
		return '<div class="airb__bar-row airb__bar-row--' + band + '">' +
			'<span class="airb__bar-label">' + esc(label) + '</span>' +
			'<div class="airb__bar-track" role="progressbar" aria-valuenow="' + display + '" aria-valuemin="0" aria-valuemax="100">' +
			'<div class="airb__bar-fill" style="width:' + display + '%"></div></div>' +
			'<span class="airb__bar-val">' + display + '% <span class="airb__bar-band">(' + esc(bandLabel(band)) + ')</span></span></div>';
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
		html += '<p class="airb__benchmark-note">' + esc((i.sampleNote || 'Based on {n} consented submissions for your role.').replace('{n}', b.sample_size)) + '</p>';
		return html + '</section>';
	}

	function heatmapHtml(cells) {
		if (!cells || !cells.length) return '';
		var html = '<div class="airb__heatmap">';
		cells.forEach(function (cell) {
			html += '<div class="airb__heat-cell airb__heat-cell--' + esc(cell.band) + '" title="' + esc(cell.label) + ' — ' + cell.risk + '%">';
			html += '<span class="airb__heat-label">' + esc(cell.label) + '</span>';
			html += '<span class="airb__heat-val">' + cell.risk + '%</span></div>';
		});
		return html + '</div>';
	}

	function renderResults() {
		var r = state.results;
		if (!r) return;

		var html = '<div class="airb__results">';
		html += '<p class="airb__funnel-stage">' + esc(i18n.stage1) + '</p>';
		html += '<h3 class="airb__panel-title">' + esc(i18n.resultsTitle) + '</h3>';
		html += '<p class="airb__muted">' + esc(i18n.riskLevel) + ': <strong>' + esc(r.risk_level_label) + '</strong></p>';

		// Hero score ring — animated circular gauge for the headline alignment score.
		var alignBand = readinessBand(r.alignment_score);
		html += '<div class="airb__scorering airb__scorering--' + alignBand + '" data-score="' + r.alignment_score + '">';
		html += '<svg class="airb__scorering-svg" viewBox="0 0 120 120" aria-hidden="true">';
		html += '<circle class="airb__scorering-track" cx="60" cy="60" r="52"></circle>';
		html += '<circle class="airb__scorering-fill" cx="60" cy="60" r="52"></circle>';
		html += '</svg>';
		html += '<div class="airb__scorering-center">';
		html += '<span class="airb__scorering-num" data-count="' + r.alignment_score + '">0</span>';
		html += '<span class="airb__scorering-max">/ 100</span>';
		html += '<span class="airb__scorering-label">' + esc(i18n.alignment) + '</span>';
		html += '</div></div>';

		html += '<div class="airb__cards">';
		(r.role_result_cards || []).forEach(function (c) {
			html += card(c.label, c.value, c.band);
		});
		html += '</div>';

		if (r.funnel_closing) {
			html += '<p class="airb__funnel-close">' + esc(r.funnel_closing) + '</p>';
		}

		if (r.risk_heatmap && r.risk_heatmap.length) {
			html += '<h4>' + esc(i18n.heatMap) + '</h4>' + heatmapHtml(r.risk_heatmap);
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
				html += '<h5>' + esc(i18n.highRiskAreas) + '</h5><ul class="airb__exposure-list">';
				rep.high_risk_areas.forEach(function (a) {
					html += '<li>' + esc(a.label) + ' — ' + a.risk + '%</li>';
				});
				html += '</ul>';
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

		if (r.policy_generator) {
			var pg = r.policy_generator;
			html += '<article class="airb__policy-gen airb__pathway-card">';
			html += '<span class="airb__pathway-badge airb__pathway-badge--policy">' + esc(i18n.policyGen) + '</span>';
			html += '<h5>' + esc(pg.title) + '</h5><p>' + esc(pg.body) + '</p>';
			if (pg.cta_url) html += '<a class="airb__btn airb__btn--primary airb__btn--sm" href="' + esc(pg.cta_url) + '" target="_blank" rel="noopener">' + esc(pg.cta_text) + '</a>';
			html += '</article>';
		}

		if (r.stage2_products && r.stage2_products.length) {
			html += '<section class="airb__stage2"><p class="airb__funnel-stage">' + esc(i18n.stage2) + '</p>';
			html += '<ul class="airb__stage2-list">';
			r.stage2_products.forEach(function (item) {
				html += '<li><span class="airb__stage2-reason">' + esc(item.reason) + '</span> → <strong>' + esc(item.product) + '</strong></li>';
			});
			html += '</ul></section>';
		}

		if (r.key_exposure_areas && r.key_exposure_areas.length) {
			html += '<h4>' + esc(i18n.exposure) + '</h4><ul class="airb__exposure-list">';
			r.key_exposure_areas.forEach(function (area) {
				html += '<li>' + esc(area.label) + ' — ' + esc(String(area.risk)) + '%</li>';
			});
			html += '</ul>';
		}

		if (r.next_steps && r.next_steps.length) {
			var principle = (cfg.after_audit && cfg.after_audit.principle) ? cfg.after_audit.principle : i18n.afterPrinciple;
			html += '<section class="airb__pathway"><h4>' + esc(i18n.nextSteps) + '</h4>';
			if (principle) html += '<p class="airb__principle airb__principle--inline">' + esc(principle) + '</p>';
			html += '<div class="airb__pathway-list">';
			r.next_steps.forEach(function (step) {
				html += '<article class="airb__pathway-card">';
				if (step.type_label) {
					html += '<span class="airb__pathway-badge airb__pathway-badge--' + esc(step.offer_type || 'template') + '">' + esc(step.type_label) + '</span>';
				}
				html += '<h5>' + esc(step.title) + '</h5><p>' + esc(step.body) + '</p>';
				if (step.cta_url) {
					html += '<a class="airb__btn airb__btn--ghost airb__btn--sm" href="' + esc(step.cta_url) + '" target="_blank" rel="noopener">' + esc(step.cta_text || step.title) + '</a>';
				}
				html += '</article>';
			});
			html += '</div></section>';
		}

		html += '<h4>' + esc(i18n.domainScores) + '</h4>';
		domainKeys.forEach(function (slug) {
			var d = r.domain_scores[slug];
			if (!d || !d.questions_answered) return;
			html += barHtml(d.label, d.risk_percentage, d.band, false);
		});

		html += benchmarkHtml(r);

		if (r.recommendations && r.recommendations.length) {
			html += '<h4>' + esc(i18n.recommendations) + '</h4><div class="airb__recs">';
			r.recommendations.forEach(function (rec) {
				if (r.next_steps && r.next_steps.some(function (s) { return s.title === rec.title; })) return;
				html += '<div class="airb__rec"><h5>' + esc(rec.title) + '</h5><p>' + esc(rec.body) + '</p>';
				if (rec.cta_url) {
					html += '<a class="airb__btn airb__btn--ghost airb__btn--sm" href="' + esc(rec.cta_url) + '" target="_blank" rel="noopener">' + esc(rec.cta_text || rec.title) + '</a>';
				}
				html += '</div>';
			});
			html += '</div>';
		}

		if (r.consultation_pitch && r.consultation_pitch.cta_url) {
			var pitch = r.consultation_pitch;
			html += '<section class="airb__consultation">';
			html += '<p class="airb__funnel-stage">' + esc(pitch.headline || i18n.stage4) + '</p>';
			html += '<p class="airb__consultation-msg">' + esc(pitch.message) + '</p>';
			html += '<a class="airb__btn airb__btn--primary" href="' + esc(pitch.cta_url) + '">' + esc(pitch.cta_text) + '</a>';
			html += '</section>';
		}

		if (r.gateway && r.gateway.cards && r.gateway.cards.length) {
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
		} else {
			var cta = cfg.consultation_cta || {};
			if (cta.url) {
				html += '<p class="airb__cta-wrap"><a class="airb__btn airb__btn--primary" href="' + esc(cta.url) + '">' + esc(cta.title || cta.text) + '</a></p>';
			}
		}

		html += '<div class="airb__results-actions">';
		html += '<button type="button" class="airb__btn airb__btn--ghost" id="airb-print">' + esc(i18n.printReport) + '</button>';
		if (state.email) {
			html += '<button type="button" class="airb__btn airb__btn--primary" id="airb-email-report">' + esc(i18n.emailReport) + '</button>';
		}
		html += '</div>';
		if (state.school && state.consent) {
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

		document.getElementById('airb-print').addEventListener('click', printReport);
		var emailBtn = document.getElementById('airb-email-report');
		if (emailBtn) emailBtn.addEventListener('click', emailReport);

		animateScoreRing();
	}

	function animateScoreRing() {
		var ring = el.results.querySelector('.airb__scorering');
		if (!ring) return;
		var fill = ring.querySelector('.airb__scorering-fill');
		var num = ring.querySelector('.airb__scorering-num');
		var score = parseInt(ring.getAttribute('data-score'), 10) || 0;
		var circ = 2 * Math.PI * 52; // matches r="52"
		var target = circ * (1 - score / 100);
		var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		// Always render the FINAL state first, so the gauge is correct even if the
		// animation frame never fires (background tab / throttled rAF). The motion
		// is pure enhancement layered on top.
		if (fill) {
			fill.style.strokeDasharray = circ;
			fill.style.strokeDashoffset = target;
			if (!reduce && typeof fill.animate === 'function') {
				try {
					fill.animate(
						[ { strokeDashoffset: circ }, { strokeDashoffset: target } ],
						{ duration: 1050, easing: 'cubic-bezier(0.22, 1, 0.36, 1)' }
					);
				} catch ( e ) {}
			}
		}

		if (num) {
			num.textContent = score; // final value up front
			if (reduce) { return; }
			var start = null;
			var dur = 950;
			function step(ts) {
				if (start === null) start = ts;
				var p = Math.min((ts - start) / dur, 1);
				var eased = 1 - Math.pow(1 - p, 3); // ease-out cubic
				num.textContent = Math.round(eased * score);
				if (p < 1) { requestAnimationFrame(step); }
				else { num.textContent = score; }
			}
			requestAnimationFrame(step);
		}
	}

	function card(title, value, band) {
		return '<div class="airb__card airb__card--' + band + '"><span class="airb__card-title">' + esc(title) + '</span><strong class="airb__card-value">' + esc(String(value)) + '</strong></div>';
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

	function saveCurrentAnswer() {
		var q = state.questions[state.step];
		if (!q) return true;
		if (q.type === 'slider') {
			var sl = document.getElementById('airb-q-input');
			if (!sl) return false;
			state.answers[q.id] = sl.value;
			return true;
		}
		if (q.type === 'select') {
			var sel = document.getElementById('airb-q-input');
			if (!sel || !sel.value) return false;
			state.answers[q.id] = sel.value;
			return true;
		}
		var picked = el.audit.querySelector('input[name="airb-q"]:checked');
		if (!picked) return false;
		state.answers[q.id] = picked.value;
		return true;
	}

	function goNext() {
		hideError();
		if (state.phase === 'role') {
			if (!state.role) { showError(i18n.chooseRole); return; }
			state.questions = questionsForRole(state.role);
			if (!state.questions.length) { showError(i18n.error); return; }
			state.phase = 'audit';
			state.step = 0;
			renderAuditQuestion();
			return;
		}
		if (state.phase === 'audit') {
			if (!saveCurrentAnswer()) { showError(i18n.required); return; }
			if (state.step < state.questions.length - 1) {
				state.step++;
				renderAuditQuestion();
				return;
			}
			state.phase = 'contact';
			renderContact();
			return;
		}
		if (state.phase === 'contact') {
			state.school = (document.getElementById('airb-school') || {}).value || '';
			state.email = (document.getElementById('airb-email') || {}).value || '';
			state.consent = !!(document.getElementById('airb-consent') || {}).checked;
			state.schoolPhase = (document.getElementById('airb-school-phase') || {}).value || '';
			state.orgType = (document.getElementById('airb-org-type') || {}).value || '';
			if (state.consent && state.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(state.email)) {
				showError(i18n.emailInvalid);
				return;
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
			submitResults(function () { renderResults(); });
		}
	}

	function goBack() {
		hideError();
		if (state.phase === 'audit' && state.step > 0) {
			saveCurrentAnswer();
			state.step--;
			renderAuditQuestion();
			return;
		}
		if (state.phase === 'audit' && state.step === 0) {
			state.phase = 'role';
			renderRole();
			return;
		}
		if (state.phase === 'contact') {
			state.phase = 'audit';
			state.step = state.questions.length - 1;
			renderAuditQuestion();
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
		body.append('consent', state.consent ? '1' : '0');
		body.append('school_phase', state.schoolPhase);
		body.append('org_type', state.orgType);

		fetch(airbBenchmark.ajaxurl, { method: 'POST', body: body, credentials: 'same-origin' })
			.then(function (res) { return res.json(); })
			.then(function (json) {
				if (json.success && json.data && json.data.results) {
					state.results = json.data.results;
				}
				if (done) done();
			})
			.catch(function () {
				if (done) done();
			});
	}

	function printReport() {
		var r = state.results;
		if (!r) return;
		var w = window.open('', '_blank', 'width=800,height=900');
		if (!w) return;
		var roleLbl = (cfg.roles || {})[state.role] || state.role;
		var html = '<!DOCTYPE html><html><head><title>AI Risk Benchmark Report</title><style>body{font-family:sans-serif;padding:2rem;max-width:720px;margin:0 auto}table{width:100%;border-collapse:collapse}td,th{border:1px solid #ccc;padding:8px}</style></head><body>';
		html += '<h1>AI Risk & Readiness Benchmark</h1><p><strong>Role:</strong> ' + esc(roleLbl) + '</p>';
		html += '<p><strong>Alignment:</strong> ' + r.alignment_score + '/100 &mdash; <strong>Risk:</strong> ' + esc(r.risk_level_label) + '</p>';
		html += '<p>' + esc(cfg.disclaimer || '') + '</p><h2>Domain scores</h2><table><tr><th>Domain</th><th>Risk %</th><th>Band</th></tr>';
		domainKeys.forEach(function (slug) {
			var d = r.domain_scores[slug];
			if (!d || !d.questions_answered) return;
			html += '<tr><td>' + esc(d.label) + '</td><td>' + d.risk_percentage + '</td><td>' + esc(d.band_label) + '</td></tr>';
		});
		html += '</table>';
		if (r.next_steps && r.next_steps.length) {
			html += '<h2>Recommended for you</h2>';
			r.next_steps.forEach(function (step) {
				html += '<h3>' + esc(step.title);
				if (step.type_label) html += ' <small>(' + esc(step.type_label) + ')</small>';
				html += '</h3><p>' + esc(step.body) + '</p>';
			});
		}
		if (r.recommendations && r.recommendations.length) {
			html += '<h2>Recommendations</h2>';
			r.recommendations.forEach(function (rec) {
				html += '<h3>' + esc(rec.title) + '</h3><p>' + esc(rec.body) + '</p>';
			});
		}
		html += '</body></html>';
		w.document.write(html);
		w.document.close();
		w.focus();
		w.print();
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
				if (json.success) showError(i18n.emailed);
				else showError((json.data && json.data.message) || i18n.error);
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
	if (el.role) renderRole();

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
		html += card('Overall DfE AI Alignment Score', rollup.overall_alignment + '%', rollup.overall_risk_level);
		html += card('Risk level', rollup.overall_risk_label, rollup.overall_risk_level);
		html += '</div>';
		if (rollup.exposure_breakdown && rollup.exposure_breakdown.length) {
			html += '<h4>Exposure breakdown</h4><table class="airb__exposure-table"><tbody>';
			rollup.exposure_breakdown.forEach(function (row) {
				html += '<tr><td>' + esc(row.label) + '</td><td><span class="airb__exposure-pill airb__exposure-pill--' + esc((row.band_label || 'low').toLowerCase()) + '">' + esc(row.band_label) + '</span></td></tr>';
			});
			html += '</tbody></table>';
		}
		if (rollup.key_exposure_areas && rollup.key_exposure_areas.length) {
			html += '<h4>Key exposure areas</h4><ul class="airb__exposure-list">';
			rollup.key_exposure_areas.forEach(function (a) {
				html += '<li>' + esc(a.label) + ' — ' + a.risk + '% risk</li>';
			});
			html += '</ul>';
		}
		html += '</div>';
		container.innerHTML = html;
	};
})();
