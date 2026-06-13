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
	var STORAGE_KEY = 'airb_completed_roles_v1';

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
		consent: false,
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
		hero: document.querySelector('.airb__hero'),
		deck: document.getElementById('airb-deck'),
	};

	var introCollapsed = false;

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
		if (r >= 75) return { slug: 'strong', label: labels.strong || 'Strong' };
		if (r >= 60) return { slug: 'established', label: labels.established || 'Established' };
		if (r >= 45) return { slug: 'developing', label: labels.developing || 'Developing' };
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

	function oversightGaugeValue(r) {
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
		var label = modifyPct !== null ? oversightLabel(modifyPct) :
			(readiness >= 51 ? 'Strong oversight' : readiness >= 26 ? 'Moderate oversight' : readiness >= 11 ? 'High reliance' : 'Critical reliance');
		return { pct: modifyPct, label: label, readiness: readiness };
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
			var aware = Math.round(dom.ai_literacy ? dom.ai_literacy.readiness_percentage : results.alignment_score);
			var safe = Math.round(dom.safeguarding ? dom.safeguarding.readiness_percentage : results.safeguarding_readiness);
			var pAlign = results.alignment_score;
			cards = [
				{ label: 'Parent AI Awareness Score', value: aware + '%', band: readinessBand(aware), tone: 'readiness', band_label: readinessBandLabel(aware) },
				{ label: 'Parent Digital Safety Score', value: safe + '%', band: readinessBand(safe), tone: 'readiness', band_label: readinessBandLabel(safe) },
				{ label: 'Parent Readiness Score', value: pAlign + '/100', band: readinessBand(pAlign), tone: 'readiness', band_label: readinessBandLabel(pAlign) },
			];
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
				});
			}
		});
	}

	function renderAuditSection() {
		var section = state.sections[state.step];
		if (!section) return;

		var html = '<div class="airb__panel">';
		html += '<div class="airb__domtag"><span class="airb__domtag-sq" style="background:' + esc(domainColor(section.domain)) + '"></span>';
		html += esc(section.name) + ' · ' + esc(domains[section.domain] || section.domain) + '</div>';

		section.questions.forEach(function (q) {
			html += '<div class="airb__q-block">';
			html += '<p class="airb__q-title">' + esc(q.text) + '</p>';
			html += questionInputHtml(q);
			html += '</div>';
		});
		html += '</div>';

		el.audit.innerHTML = html;
		el.audit.hidden = false;
		el.role.hidden = true;
		el.contact.hidden = true;
		el.results.hidden = true;
		el.nav.hidden = false;
		el.back.hidden = state.step === 0;
		el.next.textContent = i18n.next;
		el.progress.hidden = false;
		updateStepper(state.sections.length, state.step);
		if (el.progressLbl) {
			el.progressLbl.textContent = (i18n.section || 'Section') + ' ' + (state.step + 1) + ' ' + i18n.of + ' ' + state.sections.length;
		}
		bindSectionInputs(section);
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

	function renderContact() {
		var html = '<div class="airb__panel"><h3 class="airb__panel-title">' + esc(i18n.contactTitle || 'Almost done') + '</h3>';

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
		el.next.textContent = i18n.submit;
		el.progress.hidden = true;
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
		return '<div class="airb__res-panel airb__res-panel--domains"><h3>' + esc(i18n.domainBreakdown || 'Domain breakdown') + '</h3>' + rows + '</div>';
	}

	function oversightPanelHtml(r) {
		var val = oversightGaugeValue(r);
		var html = '<div class="airb__res-panel airb__res-panel--gauge"><h3>' + esc(i18n.oversight) + '<span class="airb__tm">™</span></h3>';
		if (val === null) {
			html += '<p class="airb__res-na">' + esc(i18n.oversightNa || 'Not measured for this audience.') + '</p>';
		} else {
			var label = r.human_oversight_label || '';
			var help = 'Share of AI output reviewed or changed before use. Below 26% signals reliance without meaningful human review.';
			html += '<div class="airb__res-gauge-wrap">' + oversightGaugeSvg(val, esc(i18n.oversight) + ': ' + Math.round(val) + '%') + '</div>';
			if (label) html += '<p class="airb__gauge-band" style="color:' + oversightZoneColor(val) + '">' + esc(label) + '</p>';
			html += '<p class="airb__gauge-help">' + esc(help) + '</p>';
		}
		return html + '</div>';
	}

	function resultsProfileHtml(r) {
		var roleLbl = (cfg.roles || {})[state.role] || state.role;
		var readiness = r.alignment_score;
		var risk = Math.round(r.overall_risk_percentage);
		var readinessLabel = (r.readiness_level_label || readinessBandLabel(readiness)).toUpperCase();
		var depVal = roleShowsDependency(state.role) ? r.dependency_index : null;
		var eyebrow = (i18n.resultsRoleResult || '{role} result').replace('{role}', roleLbl);

		var html = '<section class="airb__res-profile">';
		html += '<span class="airb__res-eyebrow"><span class="airb__res-eyebrow-dot" aria-hidden="true"></span>' + esc(eyebrow) + '</span>';
		html += '<div class="airb__res-shead">';
		html += '<h2 class="airb__res-title">' + esc(i18n.resultsProfileTitle || i18n.resultsTitle || 'Your AI Risk & Readiness profile') + '</h2>';
		html += '<span class="airb__res-band" style="color:' + esc(readinessBandColor(readiness)) + '">' + esc(readinessLabel) + '</span>';
		html += '</div>';

		html += '<div class="airb__res-grid3">';
		html += '<div class="airb__res-stat">';
		html += '<div class="airb__res-stat-lab">' + esc(i18n.statReadiness || 'Readiness score') + '</div>';
		html += '<div class="airb__res-stat-big" style="color:' + esc(readinessBandColor(readiness)) + '" data-count="' + readiness + '">' + readiness + '%</div>';
		html += '<div class="airb__res-stat-note">' + esc(i18n.statReadinessNote || 'Weighted across every domain in this audit.') + '</div>';
		html += '</div>';

		html += '<div class="airb__res-stat">';
		html += '<div class="airb__res-stat-lab">' + esc(i18n.statRisk || 'AI risk score') + '</div>';
		html += '<div class="airb__res-stat-big" style="color:' + esc(riskScoreColor(risk)) + '" data-count="' + risk + '">' + risk + '%</div>';
		html += '<div class="airb__res-stat-note">' + esc(i18n.statRiskNote || 'Behavioural exposure — the inverse of readiness.') + '</div>';
		html += '</div>';

		html += '<div class="airb__res-stat">';
		html += '<div class="airb__res-stat-lab">' + esc(i18n.dependency || 'AI Dependency Index') + '<span class="airb__tm">™</span></div>';
		if (depVal === null) {
			html += '<div class="airb__res-stat-big airb__res-stat-big--na">—</div>';
			html += '<div class="airb__res-stat-note">' + esc(i18n.statDepNa || 'Not measured for this audience.') + '</div>';
		} else {
			html += '<div class="airb__res-stat-big" style="color:' + esc(dependencyColor(depVal)) + '" data-count="' + depVal + '">' + depVal + '</div>';
			html += '<div class="airb__res-stat-note">' + esc(i18n.statDepNote || 'Higher means greater reliance on AI.') + '</div>';
		}
		html += '</div>';
		html += '</div>';

		html += '<div class="airb__res-two">' + oversightPanelHtml(r) + domainReadinessRowsHtml(r) + '</div>';
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
		html += '<p class="airb__benchmark-note">' + esc((i.sampleNote || 'Based on {n} consented submissions for your role.').replace('{n}', b.sample_size)) + '</p>';
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

	function renderResults() {
		var r = state.results;
		if (!r) return;

		var html = '<div class="airb__results">';
		html += resultsProfileHtml(r);
		html += focusDomainsHtml(r);

		if (r.funnel_closing) {
			html += '<aside class="airb__insight"><span class="airb__insight-label">' + esc(i18n.insightLabel) + '</span><p>' + esc(r.funnel_closing) + '</p></aside>';
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
			html += '<h4>' + esc(i18n.exposure) + '</h4>' + exposureCardsHtml(r.key_exposure_areas);
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
		} else if (isStaffRole()) {
			var cta = cfg.consultation_cta || {};
			if (cta.url) {
				html += '<p class="airb__cta-wrap"><a class="airb__btn airb__btn--primary" href="' + esc(cta.url) + '">' + esc(cta.title || cta.text) + '</a></p>';
			}
		} else if (i18n.shareResultsHint) {
			html += '<p class="airb__muted airb__share-hint">' + esc(i18n.shareResultsHint) + '</p>';
		}

		html += '<div class="airb__results-actions">';
		if (isStaffRole()) {
			var reportMailto = buildReportRequestMailto();
			if (reportMailto) {
				html += '<a class="airb__btn airb__btn--primary airb__btn--premium" href="' + reportMailto + '" id="airb-request-report">' + esc(i18n.requestFullReport) + '</a>';
			}
		} else {
			var shareMailto = buildShareResultsMailto();
			if (shareMailto) {
				html += '<a class="airb__btn airb__btn--ghost" href="' + shareMailto + '" id="airb-share-results">' + esc(i18n.shareWithSchool || 'Share results with your school') + '</a>';
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

		var emailBtn = document.getElementById('airb-email-report');
		if (emailBtn) emailBtn.addEventListener('click', emailReport);

		animateResultsStats();
		persistRoleCompletion(r.alignment_score);
		updateAppbarCompletions();
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
			if (!state.role) { showError(i18n.chooseRole); return; }
			state.sections = sectionsForRole(state.role);
			state.questions = state.sections.reduce(function (acc, s) { return acc.concat(s.questions); }, []);
			if (!state.sections.length) { showError(i18n.error); return; }
			state.phase = 'audit';
			state.step = 0;
			collapseIntro();
			renderAuditSection();
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
				state.school = (document.getElementById('airb-school') || {}).value || '';
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
			state.consent = false;
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
			saveSectionAnswers(state.sections[state.step]);
			state.step--;
			renderAuditSection();
			return;
		}
		if (state.phase === 'audit' && state.step === 0) {
			state.phase = 'role';
			expandIntro();
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
		body.append('consent', state.consent ? '1' : '0');
		body.append('school_phase', state.schoolPhase);
		body.append('org_type', state.orgType);
		body.append('year_group', state.yearGroup);

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
		if (!r || isStaffRole()) return '';

		var roleLbl = (cfg.roles || {})[state.role] || state.role;
		var lines = [
			i18n.shareEmailIntro || 'Hello, I completed the free AI Risk & Readiness Benchmark and wanted to share my results with the school.',
			'',
			(i18n.reportEmailRole || 'Role') + ': ' + roleLbl,
			(i18n.readinessLevel || 'Readiness level') + ': ' + (r.readiness_level_label || '—'),
			i18n.alignment + ': ' + (r.alignment_score != null ? r.alignment_score : '—') + '/100',
			i18n.riskLevel + ': ' + (r.risk_level_label || '—'),
		];

		if (r.dependency_index != null) {
			lines.push(i18n.dependency + ': ' + r.dependency_index + '%');
		}
		if (state.yearGroup) {
			var yg = (i18n.yearGroups || {})[state.yearGroup] || state.yearGroup;
			lines.push((i18n.yearGroupParent || i18n.yearGroup || 'Year group') + ': ' + yg);
		}
		lines.push('', i18n.shareEmailClosing || 'Please share this with the relevant teacher or school leader so our school can build the whole-school picture.');

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
