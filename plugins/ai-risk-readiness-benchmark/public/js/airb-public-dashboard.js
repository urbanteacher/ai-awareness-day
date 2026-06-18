/**
 * airb-public-dashboard.js
 *
 * Public (general adult) results dashboard — aligned with archive/demo BenchmarkDashboardsDemo.
 *
 * Depends on: airb-core, airb-results, airb-dashboard-model
 * Exposes: AIRB.PublicDashboard
 */
'use strict';

(function () {
	window.AIRB = window.AIRB || {};
	var PD = AIRB.PublicDashboard || {};
	AIRB.PublicDashboard = PD;

	var esc = AIRB.esc || function (s) { return String(s); };

	var READINESS_BANDS = [
		{ slug: 'emerging', label: 'At risk', min: 0, max: 39, color: '#dc2626', short: 'At risk' },
		{ slug: 'developing', label: 'Action required', min: 40, max: 59, color: '#f59e0b', short: 'Action required' },
		{ slug: 'established', label: 'Stable', min: 60, max: 74, color: '#eab308', short: 'Stable' },
		{ slug: 'strong', label: 'Confident', min: 75, max: 89, color: '#22c55e', short: 'Confident' },
		{ slug: 'leading', label: 'Responsible', min: 90, max: 100, color: '#16a34a', short: 'Responsible' },
	];

	var TONE_MAP = {
		secure: { border: 'border-emerald-300', bg: 'bg-emerald-50', text: 'text-emerald-800', label: 'secure', bar: '#16a34a' },
		practice: { border: 'border-amber-300', bg: 'bg-amber-50', text: 'text-amber-800', label: 'practise', bar: '#f59e0b' },
		attention: { border: 'border-rose-300', bg: 'bg-rose-50', text: 'text-rose-800', label: 'focus', bar: '#e11d48' },
	};

	var TABS = [
		{ key: 'overview', label: 'Overview' },
		{ key: 'progress', label: 'Progress & certificate' },
		{ key: 'resources', label: 'Resources' },
	];

	function scoreReadinessBand(score) {
		var clamped = Math.max(0, Math.min(100, score));
		for (var i = 0; i < READINESS_BANDS.length; i++) {
			if (clamped >= READINESS_BANDS[i].min && clamped <= READINESS_BANDS[i].max) {
				return READINESS_BANDS[i];
			}
		}
		return READINESS_BANDS[0];
	}

	function peerGapAverageText(yourScore, averageScore) {
		var gap = averageScore - yourScore;
		if (gap > 0) return gap + ' points below average';
		if (gap < 0) return Math.abs(gap) + ' points above average';
		return 'In line with average';
	}

	function peerGapTopShort(yourScore, topQuartile) {
		var gap = topQuartile - yourScore;
		if (gap > 0) return gap + ' below top quartile';
		if (gap < 0) return Math.abs(gap) + ' above top quartile';
		return 'In line with top quartile';
	}

	function peerYourScoreColor(yourScore, averageScore, topQuartile) {
		if (averageScore - yourScore > 0) return '#a32d2d';
		if (topQuartile - yourScore <= 0) return '#1d9e75';
		return null;
	}

	function focusSeverity(pct) {
		if (pct <= 25) return 'critical';
		if (pct <= 40) return 'high';
		return 'moderate';
	}

	function matchingDomainTone(area, domains) {
		if (!domains || !domains.length) return TONE_MAP.attention;
		var label = String(area.label || '').toLowerCase();
		for (var i = 0; i < domains.length; i++) {
			if (String(domains[i].label || '').toLowerCase() === label) {
				return TONE_MAP[domains[i].tone] || TONE_MAP.attention;
			}
		}
		return TONE_MAP.attention;
	}

	function readinessScaleHtml(score) {
		var active = scoreReadinessBand(score);
		var html = '<div class="teacher-dash-readiness" aria-hidden="true">';
		html += '<div class="teacher-dash-readiness__track">';
		READINESS_BANDS.forEach(function (band) {
			html += '<span class="teacher-dash-readiness__seg' + (band.slug === active.slug ? ' is-active' : '') + '" style="background:' + band.color + '"></span>';
		});
		html += '</div><div class="teacher-dash-readiness__labels">';
		READINESS_BANDS.forEach(function (band) {
			html += '<span style="color:' + (band.slug === active.slug ? band.color : '#94a3b8') + '">' + esc(band.label) + '</span>';
		});
		html += '</div></div>';
		return html;
	}

	function peerComparisonHtml(model) {
		var peer = model.peer || {};
		var yourScore = model.score;
		var averageScore = peer.averageScore || 0;
		var topQuartile = peer.topQuartile || 0;
		var yourColor = peerYourScoreColor(yourScore, averageScore, topQuartile) || model.accent || '#dc2626';

		var html = '<section class="teacher-dash-peer" aria-label="' + esc(peer.comparisonLabel || 'How you compare nationally') + '">';
		html += '<div class="teacher-dash-peer__head">';
		html += '<p class="teacher-dash-peer__label">Cohort context</p>';
		html += '<p class="teacher-dash-peer__gaps">' + esc(peerGapAverageText(yourScore, averageScore)) + ' · ' + esc(peerGapTopShort(yourScore, topQuartile)) + '</p>';
		html += '</div>';
		html += '<div class="teacher-dash-peer__grid">';
		[
			{ label: 'You', value: yourScore, color: yourColor },
			{ label: 'Nat. avg', value: averageScore, color: '#64748b' },
			{ label: 'Top quartile', value: topQuartile, color: '#16a34a' },
		].forEach(function (item) {
			html += '<div class="teacher-dash-peer__stat">';
			html += '<p class="teacher-dash-peer__stat-label">' + esc(item.label) + '</p>';
			html += '<p class="teacher-dash-peer__stat-value" style="color:' + esc(item.color) + '">' + item.value + '%</p>';
			html += '</div>';
		});
		html += '</div>';
		html += '<div class="teacher-dash-peer__bar" aria-hidden="true">';
		html += '<span class="teacher-dash-peer__bar-fill" style="width:' + yourScore + '%;background:' + esc(yourColor) + '"></span>';
		html += '<span class="teacher-dash-peer__bar-mark" style="left:' + averageScore + '%"></span>';
		html += '<span class="teacher-dash-peer__bar-mark teacher-dash-peer__bar-mark--top" style="left:' + topQuartile + '%"></span>';
		html += '</div>';
		html += '<div class="teacher-dash-peer__scale"><span>0</span><span>100</span></div>';
		html += '</section>';
		return html;
	}

	PD.coreSummaryHtml = function (model) {
		if (!model) return '';
		var band = scoreReadinessBand(model.score);
		var html = '<section class="teacher-dash-core" aria-label="Result summary">';
		html += '<div class="teacher-dash-core__stripe" aria-hidden="true"></div>';
		html += '<div class="teacher-dash-core__body">';
		html += '<div class="teacher-dash-core__copy">';
		html += '<p class="teacher-dash-core__eyebrow">' + esc(model.label) + ' · ' + esc(model.audience) + '</p>';
		html += '<div class="teacher-dash-core__score-row">';
		html += '<p class="teacher-dash-core__score-label">' + esc(model.scoreLabel) + '</p>';
		html += '<p class="teacher-dash-core__score" style="color:' + esc(band.color) + '">' + model.score + '</p>';
		html += '<p class="teacher-dash-core__denom">/100</p>';
		html += '<p class="teacher-dash-core__band" style="color:' + esc(band.color) + '">' + esc(band.label) + '</p>';
		html += '</div>';
		html += '<p class="teacher-dash-core__risk"><strong>' + model.risk + '%</strong> behavioural risk</p>';
		html += '</div>';
		html += '<span class="teacher-dash-core__icon" aria-hidden="true">U</span>';
		html += '</div>';
		if (model.motif) {
			html += '<div class="teacher-dash-core__motif"><p>' + esc(model.motif) + '</p></div>';
		}
		html += readinessScaleHtml(model.score);
		html += peerComparisonHtml(model);
		html += '</section>';
		return html;
	};

	function domainGridHtml(model, opts) {
		var domains = model.domains || [];
		if (!domains || !domains.length) return '';
		if (Results.domainGridWithGuidanceHtml) {
			return Results.domainGridWithGuidanceHtml(domains, model.focusAreas || [], Object.assign({}, opts || {}, {
				esc: esc,
				toneMap: TONE_MAP,
			}));
		}
		var html = '<div class="benchmark-domain-grid">';
		domains.forEach(function (domain) {
			var tone = TONE_MAP[domain.tone] || TONE_MAP.practice;
			var belowThreshold = domain.value < 50;
			html += '<section class="benchmark-metric-card ' + tone.border + (belowThreshold ? ' benchmark-metric-card--below-threshold' : '') + '">';
			html += '<div class="benchmark-metric-card__header">';
			html += '<h3 class="benchmark-metric-card__title">' + esc(domain.label) + '</h3>';
			html += '<span class="benchmark-metric-card__badge ' + tone.bg + ' ' + tone.text + (belowThreshold ? ' benchmark-metric-card__badge--below-threshold' : '') + '">' + esc(tone.label) + '</span>';
			html += '</div>';
        html += '<div class="benchmark-metric-card__body">';
        html += '<p class="benchmark-metric-card__value' + (belowThreshold ? ' benchmark-metric-card__value--below-threshold' : '') + '">' + domain.value + '%</p>';
        html += '</div>';
			html += '<div class="benchmark-metric-card__bar"><span style="width:' + domain.value + '%;background:' + tone.bar + '"></span></div>';
			html += '</section>';
		});
		return html + '</div>';
	}

	function readinessMetricHtml(metric, accent) {
		if (!metric) return '';
		var html = '<div class="teacher-dash-metric">';
		html += '<p class="teacher-dash-metric__label">' + esc(metric.label) + '</p>';
		html += '<p class="teacher-dash-metric__value" style="color:' + esc(accent || '#dc2626') + '">' + esc(metric.value) + '</p>';
		if (metric.note) {
			html += '<p class="teacher-dash-metric__note">' + esc(metric.note) + '</p>';
		}
		html += '</div>';
		return html;
	}

	function strengthCardHtml(model, opts) {
		if (!model.strengths || !model.strengths.length) return '';
		var heading = (opts.publicResult && opts.publicResult.strengths_heading) || "What you're doing well";
		var html = '<div class="demo-airb airb__teacher-strength-card">';
		html += '<h3 class="airb__benchmark-card-heading">' + esc(heading) + '</h3>';
		html += '<div class="airb__teacher-strength-grid">';
		model.strengths.forEach(function (strength) {
			var title = strength.title || '';
			var detail = strength.detail || '';
			var match = detail.match(/^(.*?)\s+(\d+)%\.?$/);
			html += '<section class="airb__teacher-strength-row">';
			html += '<div class="airb__teacher-strength-heading">';
			html += '<span class="airb__teacher-strength-tick" aria-hidden="true">✓</span>';
			html += '<p class="airb__teacher-strength-title">' + esc(title) + '</p>';
			html += '</div>';
			if (match) {
				html += '<div class="airb__teacher-strength-score">';
				html += '<p class="airb__teacher-strength-value">' + esc(match[2]) + '%</p>';
				html += '<p class="airb__teacher-strength-detail">' + esc(match[1].trim()) + '</p>';
				html += '</div>';
			} else if (detail) {
				html += '<p class="airb__teacher-strength-detail">' + esc(detail) + '</p>';
			}
			html += '</section>';
		});
		html += '</div></div>';
		return html;
	}

	function guidanceCtaHtml(model) {
		if (!model.priority) return '';
		if (AIRB.Certificate && AIRB.Certificate.guidanceCtaHtml) {
			return AIRB.Certificate.guidanceCtaHtml(model, {
				esc: esc,
				sceneLabel: 'Your next step',
				focusHeading: 'What to do now',
				primaryLabel: model.nextAction || 'Open personal AI safety checklist',
				primaryTab: 'resources',
			});
		}
		var cta = model.nextAction || 'Open personal AI safety checklist';
		return '<article class="demo-airb airb__leader-cta-card">' +
			'<h4 class="airb__leader-cta-title">Your next step</h4>' +
			'<p class="airb__leader-cta-body">' + esc(model.priority) + '</p>' +
			'<button type="button" class="airb__btn airb__btn--premium airb__leader-cta-btn" data-airb-dashboard-goto-tab="resources">' + esc(cta) + '</button>' +
			'</article>';
	}

	function overviewPanelHtml(model, r, opts) {
		var html = '<div class="teacher-dash-stack" data-airb-dashboard-panel="overview">';
		html += '<section class="teacher-dash-card">';
		html += '<p class="teacher-dash-scene">' + esc(model.scene) + '</p>';
		if (model.headline) {
			html += '<h2 class="teacher-dash-headline">' + esc(model.headline) + '</h2>';
		}
		html += strengthCardHtml(model, opts);
		html += '<div class="teacher-dash-metric-row">';
		html += readinessMetricHtml(model.metricA, model.accent);
		html += readinessMetricHtml(model.metricB, model.accent);
		html += '</div>';
		if (model.domains && model.domains.length) {
			html += '<h3 class="teacher-dash-domain-heading">Domain breakdown</h3>';
			html += '<div class="teacher-dash-domain-grid-wrap">' + domainGridHtml(model, opts) + '</div>';
		}
		html += '</section>';
		html += guidanceCtaHtml(model);
		html += '</div>';
		return html;
	}

	function progressPanelHtml(model, opts) {
		opts = opts || {};
		var cert = model.certificate || {};
		var accent = model.accent || '#dc2626';
		var soft = model.soft || '#fee2e2';
		var weakest = (model.domains && model.domains.length)
			? model.domains.reduce(function (min, d) { return !min || d.value < min.value ? d : min; }, null)
			: null;
		var journey = model.journey || publicJourneyFallback();
		var steps = [
			{ title: journey[0] || 'Aware user', body: 'Audit complete — baseline habits captured.' },
			{ title: journey[1] || 'Privacy reset', body: weakest ? ('Tighten ' + weakest.label.toLowerCase() + ' before your next prompt.') : 'Strip identifiers from your next AI prompt.' },
			{ title: journey[2] || 'Verification habit', body: 'Return and reach ' + (cert.unlockAt || 0) + '% to evidence improvement.' },
			{ title: journey[3] || 'Confident practice', body: 'Generate a shareable certificate once progress is evidenced.' },
		];
		var currentIndex = cert.unlocked ? steps.length - 1 : Math.min(1, steps.length - 1);

		var html = '<div class="teacher-dash-stack" data-airb-dashboard-panel="progress" hidden>';
		html += '<section class="teacher-dash-card">';
		html += '<div class="teacher-dash-progress-head">';
		html += '<div><p class="teacher-dash-scene" style="color:' + esc(accent) + '">Habit passport</p>';
		html += '<h3 class="teacher-dash-progress-title">From audit to evidence</h3></div>';
		html += '<p class="teacher-dash-progress-stamp">' + (currentIndex + 1) + ' of ' + steps.length + ' stamped</p>';
		html += '</div>';
		html += '<div class="teacher-dash-progress-bar"><span style="width:' + (((currentIndex + 1) / steps.length) * 100) + '%;background:' + esc(accent) + '"></span></div>';
		html += '<div class="benchmark-passport-grid">';
		steps.forEach(function (step, index) {
			var unlocked = index <= currentIndex;
			var active = index === currentIndex + 1;
			var bg = active ? soft : unlocked ? '#f0fdf4' : '#f8fafc';
			var iconBg = unlocked ? accent : '#fff';
			var iconColor = unlocked ? '#fff' : active ? accent : '#64748b';
			var ring = active ? accent : '#cbd5e1';
			html += '<section class="teacher-dash-passport-step" style="background:' + esc(bg) + '">';
			html += '<div class="teacher-dash-passport-step__head">';
			html += '<span class="teacher-dash-passport-step__icon" style="background:' + esc(iconBg) + ';color:' + esc(iconColor) + ';box-shadow:inset 0 0 0 1px ' + esc(ring) + '">' + (unlocked ? '✓' : String(index + 1)) + '</span>';
			html += '<span class="teacher-dash-passport-step__status">' + (unlocked ? 'Stamped' : active ? 'Next' : 'Locked') + '</span>';
			html += '</div>';
			html += '<h4>' + esc(step.title) + '</h4>';
			html += '<p>' + esc(step.body) + '</p>';
			html += '</section>';
		});
		html += '</div></section>';

		html += (AIRB.Certificate && AIRB.Certificate.panelHtml) ? AIRB.Certificate.panelHtml(model, 'public', accent) : '';

		if (opts.shareCardHtml) {
			html += '<section class="teacher-dash-card teacher-dash-share" id="airb-public-share-card">' + opts.shareCardHtml() + '</section>';
		}
		html += '</div>';
		return html;
	}

	function publicJourneyFallback() {
		return ['Aware user', 'Privacy reset', 'Verification habit', 'Confident practice'];
	}

	function resourcesPanelHtml(model, opts) {
		var html = '<div data-airb-dashboard-panel="resources" hidden>';
		if (opts.resourcesHtml) {
			html += '<div class="demo-airb airb__resources-panel">' + opts.resourcesHtml(model) + '</div>';
		} else {
			html += '<p class="airb__muted">No further reading links are available yet.</p>';
		}
		html += '</div>';
		return html;
	}

	function tabsHtml(activeTab) {
		var html = '<div class="teacher-dash-tabs" role="tablist" aria-label="Result sections">';
		TABS.forEach(function (tab) {
			var active = tab.key === activeTab;
			html += '<button type="button" class="teacher-dash-tab' + (active ? ' is-active' : '') + '" role="tab" aria-selected="' + (active ? 'true' : 'false') + '" data-airb-dashboard-tab="' + tab.key + '">' + esc(tab.label) + '</button>';
		});
		html += '</div>';
		return html;
	}

	PD.render = function (r, model, opts) {
		opts = opts || {};
		if (!model) return '';

		var html = '<section class="teacher-dash-section" data-airb-public-dashboard>';
		html += PD.coreSummaryHtml(model);
		html += tabsHtml('overview');
		html += '<div class="teacher-dash-panels">';
		html += overviewPanelHtml(model, r, opts);
		html += progressPanelHtml(model, opts);
		html += resourcesPanelHtml(model, opts);
		html += '</div></section>';

		return opts.resultsBodyHtml ? opts.resultsBodyHtml(html) : html;
	};

	PD.bind = function (root) {
		if (!root) return;
		var dashboard = root.querySelector('[data-airb-public-dashboard]');
		if (!dashboard) return;

		var tabs = dashboard.querySelectorAll('[data-airb-dashboard-tab]');
		if (AIRB.Certificate && AIRB.Certificate.bind) {
			AIRB.Certificate.bind(dashboard);
		}
		var panels = dashboard.querySelectorAll('[data-airb-dashboard-panel]');

		function activate(tabKey) {
			tabs.forEach(function (tab) {
				var active = tab.getAttribute('data-airb-dashboard-tab') === tabKey;
				tab.classList.toggle('is-active', active);
				tab.setAttribute('aria-selected', active ? 'true' : 'false');
			});
			panels.forEach(function (panel) {
				panel.hidden = panel.getAttribute('data-airb-dashboard-panel') !== tabKey;
			});
		}

		tabs.forEach(function (tab) {
			tab.addEventListener('click', function () {
				activate(tab.getAttribute('data-airb-dashboard-tab') || 'overview');
			});
		});

		dashboard.querySelectorAll('[data-airb-dashboard-goto-tab]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var tabKey = btn.getAttribute('data-airb-dashboard-goto-tab') || 'resources';
				activate(tabKey);
				dashboard.scrollIntoView({ behavior: 'smooth', block: 'start' });
			});
		});
	};
}());
