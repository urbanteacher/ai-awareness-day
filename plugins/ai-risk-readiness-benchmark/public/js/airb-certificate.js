/**
 * airb-certificate.js
 *
 * Evidence-backed certificate allocation UI for benchmark dashboards.
 *
 * Depends on: airb-core, airb-certificate-evidence
 * Exposes: AIRB.Certificate
 */
'use strict';

(function () {
	window.AIRB = window.AIRB || {};
	var Cert = AIRB.Certificate || {};
	AIRB.Certificate = Cert;

	var Evidence = AIRB.CertificateEvidence || {};
	var esc = AIRB.esc || function (s) {
		return String(s == null ? '' : s)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	};

	function unlockConfig() {
		return (window.airbBenchmark && airbBenchmark.certificateUnlock) || {};
	}

	function localizedCopyMap() {
		return (window.airbBenchmark && airbBenchmark.certificateCopy) || {};
	}

	var FALLBACK_COPY = {
		headline_primary: 'AI Risk & Readiness Benchmark\u2122',
		headline_secondary: 'Certificate',
		body: 'has completed the AI Risk & Readiness Benchmark\u2122 and submitted evidence of a real action linked to AI Awareness Day.',
		name_placeholder: 'Example: Alex Teacher',
	};

	function normalizeRole(role) {
		return Evidence.normalizeRole ? Evidence.normalizeRole(role) : String(role || 'teacher');
	}

	function roleCopy(role) {
		role = String(role || '').trim() || 'teacher';
		var map = localizedCopyMap();
		if (map[role]) return map[role];
		var normalized = normalizeRole(role);
		if (map[normalized]) return map[normalized];
		return FALLBACK_COPY;
	}

	function roleFromRuntime() {
		return (AIRB.runtime && AIRB.runtime.state && AIRB.runtime.state.role) || '';
	}

	function submissionIdFromRuntime() {
		return (AIRB.runtime && AIRB.runtime.state && AIRB.runtime.state.submissionId) || 0;
	}

	function sessionIdFromRuntime() {
		return (AIRB.runtime && AIRB.runtime.state && AIRB.runtime.state.sessionId) || '';
	}

	function formatDate(value) {
		if (!value) return new Date().toLocaleDateString(undefined, { day: 'numeric', month: 'long', year: 'numeric' });
		var normalised = String(value).replace(' ', 'T');
		var date = new Date(normalised);
		if (isNaN(date.getTime())) return String(value);
		return date.toLocaleDateString(undefined, { day: 'numeric', month: 'long', year: 'numeric' });
	}

	function splitCertificateTitle(title) {
		title = String(title || '').trim();
		var suffix = ' Certificate';
		if (title.length > suffix.length && title.slice(-suffix.length) === suffix) {
			return {
				primary: title.slice(0, -suffix.length),
				secondary: 'Certificate',
			};
		}
		return { primary: title, secondary: '' };
	}

	function certificateHeadlines(model, role) {
		var copy = roleCopy(role);
		if (copy.headline_secondary) {
			return {
				primary: copy.headline_primary || FALLBACK_COPY.headline_primary,
				secondary: copy.headline_secondary,
			};
		}
		if (model && model.certificateTitle) {
			return splitCertificateTitle(model.certificateTitle);
		}
		if (copy.headline_primary) {
			return splitCertificateTitle(copy.headline_primary);
		}
		return {
			primary: FALLBACK_COPY.headline_primary,
			secondary: FALLBACK_COPY.headline_secondary,
		};
	}

	function certificateTitle(model, role) {
		var lines = certificateHeadlines(model, role);
		return lines.secondary ? lines.primary + ' ' + lines.secondary : lines.primary;
	}

	function certificateHeadlineHtml(model, role) {
		var lines = certificateHeadlines(model, role);
		var html = '<h1 class="certificate-preview__headline">';
		html += '<span class="certificate-preview__headline-primary">' + esc(lines.primary) + '</span>';
		if (lines.secondary) {
			html += '<span class="certificate-preview__headline-secondary">' + esc(lines.secondary) + '</span>';
		}
		html += '</h1>';
		return html;
	}

	function certificateBody(model, role) {
		if (model && model.certificateBody) return model.certificateBody;
		return roleCopy(role).body || FALLBACK_COPY.body;
	}

	function namePlaceholder(role) {
		return roleCopy(role).name_placeholder || FALLBACK_COPY.name_placeholder;
	}

	function scoreThreshold() {
		return Evidence.scoreThreshold ? Evidence.scoreThreshold() : 70;
	}

	function certificateProgress(cert, model) {
		cert = cert || {};
		model = model || {};
		var threshold = cert.scoreThreshold != null ? cert.scoreThreshold : (cert.unlockAt != null ? cert.unlockAt : scoreThreshold());
		var current = cert.currentScore != null ? cert.currentScore : (model.score != null ? model.score : 0);
		var needed = cert.needed != null ? cert.needed : Math.max(0, threshold - current);
		return {
			threshold: threshold,
			current: current,
			needed: needed,
			scoreEligible: cert.scoreEligible != null ? !!cert.scoreEligible : current >= threshold,
			unlocked: !!cert.unlocked,
		};
	}

	function certificateStatusNote(progress, escFn) {
		var i18n = (window.airbBenchmark && airbBenchmark.i18n) || {};
		if (progress.unlocked) {
			return (i18n.certificateUnlockedLabel || 'Certificate unlocked') + '. ' +
				(i18n.certificateUnlockedNote || 'Open Progress & certificate to download or print yours.');
		}
		if (progress.scoreEligible) {
			return (i18n.certificateThresholdMetLabel || 'Certificate threshold met ({n}%)').replace('{n}', String(progress.threshold)) + ' ' +
				(i18n.certificateEligibleNote || 'You scored {score}% — complete evidence in Progress & certificate to unlock.').replace('{score}', String(progress.current));
		}
		return (i18n.certificateGapNote || '{gap} point{plural} to certificate unlock at {n}%.')
			.replace('{gap}', String(progress.needed))
			.replace('{plural}', progress.needed === 1 ? '' : 's')
			.replace('{n}', String(progress.threshold));
	}

	Cert.incentiveHtml = function (model) {
		if (!model || !model.certificate) {
			return '';
		}
		var progress = certificateProgress(model.certificate, model);
		var stateClass = progress.unlocked ? 'airb__cert-progress--unlocked' : (progress.scoreEligible ? 'airb__cert-progress--met' : 'airb__cert-progress--gap');
		return '<p class="teacher-dash-cert-note airb__cert-progress-note ' + stateClass + '" role="status">' +
			esc(certificateStatusNote(progress, esc)) +
			'</p>';
	};

	Cert.certificateProgressHtml = function (model, escFn) {
		escFn = escFn || esc;
		if (!model || !model.certificate) {
			return '';
		}
		var progress = certificateProgress(model.certificate, model);
		var accent = model.accent || '#2563eb';
		var gapDisplay = progress.unlocked ? 'Done' : (progress.scoreEligible ? 'Met' : ('+' + progress.needed));
		var barWidth = progress.threshold > 0 ? Math.min(100, Math.round((progress.current / progress.threshold) * 100)) : 0;
		var stateClass = progress.unlocked ? 'airb__cert-progress--unlocked' : (progress.scoreEligible ? 'airb__cert-progress--met' : 'airb__cert-progress--gap');
		var statusText = certificateStatusNote(progress, escFn);

		return '<div class="airb__cert-progress ' + stateClass + '">' +
			'<div class="benchmark-certificate-stats">' +
			'<div><span>Current</span><strong>' + escFn(progress.current) + '%</strong></div>' +
			'<div><span>Target</span><strong>' + escFn(progress.threshold) + '%</strong></div>' +
			'<div><span>Gap</span><strong>' + escFn(gapDisplay) + '</strong></div>' +
			'</div>' +
			'<div class="teacher-dash-progress-bar airb__cert-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="' + escFn(progress.threshold) + '" aria-valuenow="' + escFn(progress.current) + '" aria-label="' + escFn('Progress toward certificate threshold') + '">' +
			'<span style="width:' + barWidth + '%;background:' + esc(accent) + '"></span></div>' +
			'<p class="teacher-dash-cert-note airb__cert-progress-note" role="status">' + escFn(statusText) + '</p>' +
			'</div>';
	};

	Cert.guidanceCtaHtml = function (model, opts) {
		opts = opts || {};
		var escFn = opts.esc || esc;
		if (!model || !model.priority) {
			return '';
		}
		var accent = model.accent || '#2563eb';
		var focusHeading = opts.focusHeading || 'Priority focus';
		var primaryLabel = opts.primaryLabel || 'Request support';
		var practiceScene = opts.sceneLabel || (opts.primaryTab ? 'Your next step' : 'Need more guidance?');
		var supportTitle = opts.title || 'Need more guidance?';
		var supportCopy = opts.supportCopy || 'Get practical help turning your results into safer everyday habits.';
		var certProgress = Cert.certificateProgressHtml(model, escFn);
		var html = '';

		if (model.certificate && certProgress) {
			var progress = certificateProgress(model.certificate, model);
			var jumpLabel = progress.unlocked
				? (opts.certViewLabel || 'View certificate')
				: (progress.scoreEligible ? (opts.certUnlockLabel || 'Unlock certificate') : (opts.certTargetLabel || 'See certificate target'));
			html += '<section class="teacher-dash-card airb__guidance-card airb__guidance-card--cert" aria-labelledby="airb-guidance-cert-title">';
			html += '<p class="teacher-dash-scene" style="color:' + esc(accent) + '">Certificate progress</p>';
			html += '<h3 class="teacher-dash-domain-heading" id="airb-guidance-cert-title">Unlock your certificate</h3>';
			html += certProgress;
			html += '<div class="airb__guidance-section-actions">';
			html += '<button type="button" class="airb__btn airb__btn--primary airb__guidance-btn" data-airb-dashboard-tab-jump="progress">' + escFn(jumpLabel) + '</button>';
			html += '</div>';
			html += '</section>';
		}

		html += '<section class="teacher-dash-card airb__guidance-card airb__guidance-card--practice" aria-labelledby="airb-guidance-practice-title">';
		html += '<p class="teacher-dash-scene" style="color:' + esc(accent) + '">' + escFn(practiceScene) + '</p>';
		html += '<h3 class="teacher-dash-domain-heading" id="airb-guidance-practice-title">' + escFn(focusHeading) + '</h3>';
		html += '<p class="airb__guidance-focus-body">' + escFn(model.priority) + '</p>';
		if (!opts.primaryTab) {
			html += '<div class="airb__guidance-support">';
			html += '<p class="airb__guidance-support-label">' + escFn(supportTitle) + '</p>';
			html += '<p class="airb__guidance-support-copy">' + escFn(supportCopy) + '</p>';
			html += '</div>';
		}
		html += '<div class="airb__guidance-section-actions">';
		if (opts.primaryTab) {
			html += '<button type="button" class="airb__btn airb__btn--primary airb__guidance-btn" data-airb-dashboard-tab-jump="' + escFn(opts.primaryTab) + '">' + escFn(primaryLabel) + '</button>';
		} else {
			html += '<button type="button" class="airb__btn airb__btn--primary airb__guidance-btn" data-airb-scroll-interest="1">' + escFn(primaryLabel) + '</button>';
		}
		html += '</div>';
		html += '</section>';

		return html;
	};

	function bindTabJumps(root) {
		if (!root || root.dataset.airbTabJumpsBound === '1') {
			return;
		}
		root.dataset.airbTabJumpsBound = '1';
		root.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-airb-dashboard-tab-jump]');
			if (!btn || !root.contains(btn)) {
				return;
			}
			var tabKey = btn.getAttribute('data-airb-dashboard-tab-jump');
			if (!tabKey) {
				return;
			}
			var tab = root.querySelector('[data-airb-dashboard-tab="' + tabKey + '"]');
			if (tab) {
				tab.click();
			}
		});
	}

	function tierClass(tier) {
		if (tier === 'strong_evidence') return 'is-strong';
		if (tier === 'likely_valid') return 'is-valid';
		if (tier === 'needs_manual_review') return 'is-review';
		return 'is-weak';
	}

	function previewHtml(data) {
		var role = data.role || roleFromRuntime();
		return '<div class="certificate-preview certificate-preview--compact" data-airb-certificate-preview>' +
			'<div class="certificate-preview__frame">' +
			'<div class="certificate-preview__content">' +
			certificateHeadlineHtml({ certificateTitle: data.title }, role) +
			'<p class="certificate-preview__lead">This certifies that</p>' +
			'<p class="certificate-preview__name">' + esc(data.participantName || 'Name pending') + '</p>' +
			'<p class="certificate-preview__body">' + esc(data.body) + '</p>' +
			'<p class="certificate-preview__date">Awarded: ' + esc(formatDate(data.awardedAt)) + '</p>' +
			'<footer class="certificate-preview__footer">' +
			'<span class="certificate-preview__id">Certificate ID: ' + esc(data.certificateId || 'Pending') + '</span>' +
			'<span class="certificate-preview__issuer">Issued by: AI Awareness Day</span>' +
			'<span class="certificate-preview__verify">' + esc(data.verifyUrl || 'ai-awareness-day.org') + '</span>' +
			'</footer>' +
			'</div>' +
			'</div>' +
			'</div>';
	}

	function qualityHtml(assessment) {
		if (!assessment) return '';
		var cls = tierClass(assessment.quality_tier);
		var html = '<div class="benchmark-certificate-quality ' + cls + '" data-airb-certificate-quality>';
		html += '<div class="benchmark-certificate-quality__head">';
		html += '<span class="benchmark-certificate-quality__label">Evidence quality</span>';
		html += '<strong class="benchmark-certificate-quality__score">' + esc(assessment.quality_score) + '/100</strong>';
		html += '<span class="benchmark-certificate-quality__tier">' + esc(assessment.tier_label || '') + '</span>';
		html += '</div>';
		var pathwayDefs = Evidence.pathwayConfig ? Evidence.pathwayConfig() : [];
		if (pathwayDefs.length && assessment.pathways) {
			html += '<ul class="benchmark-certificate-pathways">';
			pathwayDefs.forEach(function (item) {
				var met = !!assessment.pathways[item.key];
				html += '<li class="benchmark-certificate-pathways__item' + (met ? ' is-met' : '') + '">';
				html += '<span class="benchmark-certificate-pathways__status" aria-hidden="true">' + (met ? '✓' : '○') + '</span>';
				html += '<span><strong>' + esc(item.label) + '</strong><br><span class="benchmark-certificate-pathways__hint">' + esc(item.hint || '') + '</span></span>';
				html += '</li>';
			});
			html += '</ul>';
		}
		if (assessment.messages && assessment.messages.length) {
			html += '<ul class="benchmark-certificate-quality__messages">';
			assessment.messages.forEach(function (msg) {
				html += '<li>' + esc(msg) + '</li>';
			});
			html += '</ul>';
		}
		html += '</div>';
		return html;
	}

	function readEvidence(panel) {
		var themeEl = panel.querySelector('[data-airb-certificate-theme]:checked');
		return {
			theme: themeEl ? themeEl.value : '',
			action: ((panel.querySelector('[data-airb-certificate-action]') || {}).value || '').trim(),
			change: ((panel.querySelector('[data-airb-certificate-change]') || {}).value || '').trim(),
			link: ((panel.querySelector('[data-airb-certificate-link]') || {}).value || '').trim(),
		};
	}

	function assessPanel(panel) {
		var role = panel.dataset.airbRole || roleFromRuntime();
		var evidence = readEvidence(panel);
		var benchmarkScore = parseInt(panel.dataset.airbBenchmarkScore || '0', 10) || 0;
		return Evidence.assess(role, evidence.theme, evidence.action, evidence.change, evidence.link, benchmarkScore);
	}

	function updateQuality(panel, assessment) {
		var wrap = panel.querySelector('[data-airb-certificate-quality-wrap]');
		if (!wrap) return;
		wrap.innerHTML = qualityHtml(assessment);
	}

	function updatePreview(panel) {
		var previewWrap = panel.querySelector('.benchmark-certificate-preview-wrap');
		if (!previewWrap) return;
		var role = panel.dataset.airbRole || roleFromRuntime();
		var name = ((panel.querySelector('[data-airb-certificate-name]') || {}).value || '').trim();
		previewWrap.innerHTML = previewHtml({
			title: certificateTitle(null, role),
			role: role,
			participantName: name || 'Name pending',
			body: certificateBody(null, role),
			awardedAt: '',
			certificateId: 'Pending',
			verifyUrl: 'ai-awareness-day.org',
		});
	}

	function syncUnlockState(panel, cert, assessment) {
		var allocate = panel.querySelector('[data-airb-certificate-allocate]');
		var download = panel.querySelector('[data-airb-certificate-download]');
		var unlocked = cert && cert.unlocked;
		var canUnlock = assessment && assessment.can_unlock;
		if (allocate) {
			allocate.disabled = unlocked || !canUnlock;
		}
		if (download) {
			download.disabled = !unlocked;
		}
	}

	function evidenceFormHtml(role, cert, scoreEligible, unlocked) {
		cert = cert || {};
		unlocked = !!unlocked;
		var copy = roleCopy(role);
		var cfg = unlockConfig();
		var unlockIntro = cfg.unlock_intro || 'Reach the benchmark score threshold and complete one of the evidence options below.';
		var themes = cfg.themes || [];
		var threshold = scoreThreshold();
		var disabled = !scoreEligible || unlocked;
		var html = '';

		if (!scoreEligible) {
			html += '<p class="benchmark-certificate-gate is-blocked">Reach at least ' + esc(threshold) + '% on the benchmark before unlocking. Retake the audit to improve your score.</p>';
		} else if (!unlocked) {
			html += '<p class="benchmark-certificate-gate is-open">' + esc(unlockIntro) + '</p>';
		}

		html += '<fieldset class="benchmark-certificate-themes"' + (disabled ? ' disabled' : '') + '>';
		html += '<legend>Choose one theme</legend>';
		html += '<div class="benchmark-certificate-theme-grid">';
		themes.forEach(function (theme) {
			var checked = cert.evidence_theme === theme.slug ? ' checked' : '';
			html += '<label class="benchmark-certificate-theme-option">';
			html += '<input type="radio" name="airb-cert-theme-' + esc(role) + '" value="' + esc(theme.slug) + '" data-airb-certificate-theme' + checked + '>';
			html += '<span>' + esc(theme.label) + '</span>';
			html += '</label>';
		});
		html += '</div></fieldset>';

		html += '<label class="benchmark-certificate-reflection">' + esc(copy.evidence_action_label || 'What did you do?');
		html += '<textarea rows="3" data-airb-certificate-action placeholder="' + esc(copy.evidence_action_placeholder || '') + '"' + (disabled ? ' disabled' : '') + '>' + esc(cert.evidence_action || '') + '</textarea></label>';

		html += '<label class="benchmark-certificate-reflection">' + esc(copy.evidence_change_label || 'What changed in your practice, lesson, discussion, or understanding?');
		html += '<textarea rows="3" data-airb-certificate-change placeholder="' + esc(copy.evidence_change_placeholder || '') + '"' + (disabled ? ' disabled' : '') + '>' + esc(cert.evidence_change || '') + '</textarea></label>';

		html += '<label class="benchmark-certificate-reflection">' + esc(copy.evidence_link_label || 'Optional evidence link');
		html += '<input type="url" data-airb-certificate-link value="' + esc(cert.evidence_link || '') + '" placeholder="' + esc(copy.evidence_link_placeholder || 'https://...') + '"' + (disabled ? ' disabled' : '') + '></label>';

		html += '<div data-airb-certificate-quality-wrap></div>';
		return html;
	}

	function printCertificate(panel) {
		var preview = panel && panel.querySelector('[data-airb-certificate-preview]');
		if (!preview) return;
		var win = window.open('', '_blank', 'noopener,noreferrer,width=1000,height=760');
		if (!win) return;
		var cfg = window.airbBenchmark || {};
		var role = panel.dataset.airbRole || roleFromRuntime() || 'teacher';
		win.document.write('<!doctype html><html><head><title>AI Awareness Day Certificate</title>');
		if (cfg.pluginUrl) {
			win.document.write('<link rel="stylesheet" href="' + esc(cfg.pluginUrl + 'public/css/airb-teacher-dashboard.css') + '">');
		}
		win.document.write('</head><body class="airb__results--' + esc(role) + '-dash" style="padding:32px;background:#fff;">');
		win.document.write(preview.outerHTML);
		win.document.write('</body></html>');
		win.document.close();
		win.focus();
		setTimeout(function () { win.print(); }, 250);
	}

	Cert.panelHtml = function (model, role, accent) {
		model = model || {};
		role = role || roleFromRuntime();
		var cert = model.certificate || {};
		var title = certificateTitle(model, role);
		var unlocked = !!cert.unlocked;
		var participantName = cert.participantName || '';
		var scoreEligible = cert.scoreEligible != null ? !!cert.scoreEligible : (cert.currentScore || model.score || 0) >= scoreThreshold();
		var threshold = scoreThreshold();
		var storedCert = {
			evidence_theme: cert.evidenceTheme || cert.evidence_theme || '',
			evidence_action: cert.evidenceAction || cert.evidence_action || '',
			evidence_change: cert.evidenceChange || cert.evidence_change || '',
			evidence_link: cert.evidenceLink || cert.evidence_link || '',
		};
		var preview = {
			title: title,
			role: role,
			participantName: participantName,
			body: certificateBody(model, role),
			awardedAt: cert.awardedAt || '',
			certificateId: cert.certificateId || '',
			verifyUrl: 'ai-awareness-day.org',
		};
		var submissionId = submissionIdFromRuntime();
		var benchmarkScore = cert.currentScore || model.score || 0;

		var html = '<section class="teacher-dash-card benchmark-certificate-layout" data-airb-certificate-panel data-airb-role="' + esc(role) + '" data-airb-submission-id="' + esc(submissionId) + '" data-airb-benchmark-score="' + esc(benchmarkScore) + '" data-airb-score-eligible="' + (scoreEligible ? '1' : '0') + '" data-airb-unlocked="' + (unlocked ? '1' : '0') + '">';
		html += '<div class="benchmark-certificate-summary">';
		html += '<div><p class="teacher-dash-scene" style="color:' + esc(accent || model.accent || '#2563eb') + '">Certificate</p>';
		html += '<h3 class="teacher-dash-progress-title">' + esc(title) + '</h3>';
		html += '<p class="teacher-dash-cert-note">' + (unlocked ? 'Certificate allocated. You can download or print it now.' : 'Complete the evidence step below to unlock your AI Risk & Readiness Benchmark\u2122 Certificate.') + '</p></div>';
		html += '<div class="benchmark-certificate-stats">';
		html += '<div><span>Current</span><strong>' + esc(cert.currentScore || model.score || 0) + '%</strong></div>';
		html += '<div><span>Need</span><strong>' + esc(threshold) + '%</strong></div>';
		html += '<div><span>Gap</span><strong>' + (scoreEligible ? 'Met' : ('+' + esc(cert.needed != null ? cert.needed : Math.max(0, threshold - (cert.currentScore || model.score || 0))))) + '</strong></div>';
		html += '</div></div>';
		html += '<div class="benchmark-certificate-grid">';
		html += '<div class="benchmark-certificate-form">';
		html += '<label>Name on certificate<input type="text" data-airb-certificate-name value="' + esc(participantName) + '" placeholder="' + esc(namePlaceholder(role)) + '"' + (unlocked ? ' readonly' : '') + '></label>';
		html += evidenceFormHtml(role, storedCert, scoreEligible, unlocked);
		html += '<button type="button" class="airb__btn airb__btn--primary" data-airb-certificate-allocate ' + (unlocked ? 'disabled' : 'disabled') + '>' + (unlocked ? 'Certificate allocated' : 'Unlock certificate') + '</button>';
		html += '<button type="button" class="airb__btn airb__btn--ghost" data-airb-certificate-download ' + (unlocked ? '' : 'disabled') + '>Download / print certificate</button>';
		html += '<p class="benchmark-certificate-status" data-airb-certificate-status>' + (unlocked ? ('Certificate ID ' + esc(cert.certificateId || '')) : 'Evidence is checked before unlock. This recognises progress — not certification as an expert user.') + '</p>';
		html += '</div>';
		html += '<div class="benchmark-certificate-preview-wrap">' + previewHtml(preview) + '</div>';
		html += '</div>';
		html += '</section>';
		return html;
	};

	function setStatus(panel, message, isError) {
		var status = panel.querySelector('[data-airb-certificate-status]');
		if (!status) return;
		status.textContent = message;
		status.classList.toggle('is-error', !!isError);
	}

	function updateFromResponse(panel, payload) {
		var cert = payload && payload.certificate;
		if (!cert) return;
		var role = cert.role || panel.dataset.airbRole || roleFromRuntime();
		var copy = cert.copy || roleCopy(role);
		var previewWrap = panel.querySelector('.benchmark-certificate-preview-wrap');
		if (previewWrap) {
			previewWrap.innerHTML = previewHtml({
				title: certificateTitle(null, role),
				role: role,
				participantName: cert.participant_name,
				body: copy.body || certificateBody(null, role),
				awardedAt: cert.awarded_at,
				certificateId: cert.certificate_id,
				verifyUrl: cert.verify_url || 'ai-awareness-day.org',
			});
		}
		var allocate = panel.querySelector('[data-airb-certificate-allocate]');
		var download = panel.querySelector('[data-airb-certificate-download]');
		if (allocate) {
			allocate.disabled = true;
			allocate.textContent = 'Certificate allocated';
		}
		if (download) download.disabled = false;
		if (cert.assessment) updateQuality(panel, cert.assessment);
		setStatus(panel, 'Certificate ID ' + (cert.certificate_id || '') + ' allocated.', false);
	}

	function bindEvidenceInputs(panel) {
		var fields = panel.querySelectorAll('[data-airb-certificate-theme], [data-airb-certificate-action], [data-airb-certificate-change], [data-airb-certificate-link]');
		fields.forEach(function (field) {
			if (field.dataset.airbBound) return;
			field.dataset.airbBound = '1';
			var handler = function () {
				var assessment = assessPanel(panel);
				updateQuality(panel, assessment);
				syncUnlockState(panel, { unlocked: false }, assessment);
			};
			field.addEventListener('input', handler);
			field.addEventListener('change', handler);
		});
	}

	Cert.bind = function (root) {
		if (!root) return;
		bindTabJumps(root);
		var panels = root.querySelectorAll('[data-airb-certificate-panel]');
		panels.forEach(function (panel) {
			bindEvidenceInputs(panel);
			var unlocked = panel.dataset.airbUnlocked === '1';
			var initialAssessment = assessPanel(panel);
			updateQuality(panel, initialAssessment);
			syncUnlockState(panel, { unlocked: unlocked }, initialAssessment);

			var nameInput = panel.querySelector('[data-airb-certificate-name]');
			if (nameInput && !nameInput.dataset.airbBound) {
				nameInput.dataset.airbBound = '1';
				nameInput.addEventListener('input', function () {
					updatePreview(panel);
				});
			}

			var download = panel.querySelector('[data-airb-certificate-download]');
			if (download && !download.dataset.airbBound) {
				download.dataset.airbBound = '1';
				download.addEventListener('click', function () {
					printCertificate(panel);
				});
			}

			var allocate = panel.querySelector('[data-airb-certificate-allocate]');
			if (!allocate || allocate.dataset.airbBound) return;
			allocate.dataset.airbBound = '1';
			allocate.addEventListener('click', function () {
				var cfg = window.airbBenchmark || {};
				var name = (panel.querySelector('[data-airb-certificate-name]') || {}).value || '';
				var evidence = readEvidence(panel);
				var assessment = assessPanel(panel);
				if (!name.trim()) {
					setStatus(panel, 'Add the name to show on the certificate.', true);
					return;
				}
				if (!assessment.can_unlock) {
					updateQuality(panel, assessment);
					setStatus(panel, assessment.score_eligible === false
						? ('Reach at least ' + scoreThreshold() + '% on the benchmark before unlocking.')
						: 'Complete one evidence option before unlocking the certificate.', true);
					return;
				}
				if (!cfg.ajaxurl || !cfg.nonce) {
					setStatus(panel, 'Certificate allocation is not available on this page.', true);
					return;
				}
				allocate.disabled = true;
				setStatus(panel, 'Allocating certificate...', false);
				var body = new FormData();
				body.append('action', 'airb_allocate_certificate');
				body.append('nonce', cfg.nonce);
				body.append('submission_id', panel.dataset.airbSubmissionId || submissionIdFromRuntime());
				body.append('session_id', sessionIdFromRuntime());
				body.append('role', panel.dataset.airbRole || roleFromRuntime());
				body.append('participant_name', name.trim());
				body.append('school_name', '');
				body.append('evidence_theme', evidence.theme);
				body.append('evidence_action', evidence.action);
				body.append('evidence_change', evidence.change);
				body.append('evidence_link', evidence.link);
				fetch(cfg.ajaxurl, { method: 'POST', body: body, credentials: 'same-origin' })
					.then(function (res) { return res.json(); })
					.then(function (json) {
						if (!json || !json.success) {
							if (json && json.data && json.data.assessment) {
								updateQuality(panel, json.data.assessment);
							}
							throw new Error((json && json.data && json.data.message) || 'Could not allocate the certificate.');
						}
						updateFromResponse(panel, json.data);
					})
					.catch(function (err) {
						allocate.disabled = false;
						syncUnlockState(panel, { unlocked: false }, assessPanel(panel));
						setStatus(panel, err.message || 'Could not allocate the certificate.', true);
					});
			});
		});
	};

	Cert.roleCopy = roleCopy;
}());
