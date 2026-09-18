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

	var Art = AIRB.CertificateArt || {};

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

	function submissionEmailFromRuntime() {
		return (AIRB.runtime && AIRB.runtime.state && AIRB.runtime.state.email) || '';
	}

	function showContactEmailField(submissionEmail) {
		return !submissionEmail;
	}

	function roleRequiresContactEmail(role, submissionEmail, needsReview) {
		if (submissionEmail) return false;
		if (needsReview) return true;
		role = normalizeRole(role);
		return role === 'student' || role === 'parent';
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
		var accent = model.accent || '#006A7D';
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
		if (!model || !model.priority) return '';
		var escFn = opts.esc || esc;
		/* No jump buttons: they existed because the tabs did not look
		   clickable. The tabs are buttons now, so these only competed with
		   them and said the same thing twice. */
		return '<section class="teacher-dash-card airb__guidance-card airb__guidance-card--practice">' +
			'<h3 class="teacher-dash-domain-heading">Your next step</h3>' +
			'<p class="airb__guidance-focus-body">' + escFn(model.priority) + '</p>' +
			'</section>';
	};

	function tierClass(tier) {
		if (tier === 'strong_evidence') return 'is-strong';
		if (tier === 'likely_valid') return 'is-valid';
		if (tier === 'needs_manual_review') return 'is-review';
		return 'is-weak';
	}

	function previewHtml(data) {
		return Art.previewHtml({
			name: data.participantName || 'Name pending',
			body: data.body,
			awarded: formatDate(data.awardedAt) || 'on completion',
			certificateId: data.certificateId,
			verifyUrl: data.verifyUrl,
			theme: data.theme,
		});
	}

	function paintPreviews(scope) {
		return Art.paint(scope);
	}

	function qualityHtml(assessment) {
		if (!assessment) return '';
		var cls = tierClass(assessment.quality_tier);
		/* Every criterion and every unmet message used to render at full
		   height before the user had typed a character — a wall of red on
		   arrival. The score stays visible; the detail opens once there is
		   progress worth reading. */
		var started = (parseInt(assessment.quality_score, 10) || 0) > 0;
		var html = '<div class="benchmark-certificate-quality ' + cls + '" data-airb-certificate-quality>';
		html += '<details class="benchmark-certificate-quality__detail"' + (started ? ' open' : '') + '>';
		html += '<summary class="benchmark-certificate-quality__head">';
		html += '<span class="benchmark-certificate-quality__label">Evidence quality</span>';
		html += '<strong class="benchmark-certificate-quality__score">' + esc(assessment.quality_score) + '/100</strong>';
		html += '<span class="benchmark-certificate-quality__tier">' + esc(assessment.tier_label || '') + '</span>';
		html += '</summary>';
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
		html += '</details>';
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
		var host = panel.querySelector('[data-airb-certificate-preview]');
		if (!host) return;
		var role = panel.dataset.airbRole || roleFromRuntime();
		var name = ((panel.querySelector('[data-airb-certificate-name]') || {}).value || '').trim();
		if (panel.dataset.airbUnlocked !== '1') {
			/* Before unlock the preview tracks the form, so the strand the
			   user picks is visible on the artwork they are working toward. */
			host.dataset.certName = name || 'Name pending';
			host.dataset.certBody = certificateBody(null, role);
			host.dataset.certTheme = readEvidence(panel).theme || '';
		}
		paintPreviews(host);
	}

	function syncUnlockState(panel, cert, assessment) {
		var allocate = panel.querySelector('[data-airb-certificate-allocate]');
		var download = panel.querySelector('[data-airb-certificate-download]');
		var i18n = (window.airbBenchmark && airbBenchmark.i18n) || {};
		var unlocked = cert && cert.unlocked;
		var pendingReview = cert && cert.pendingReview;
		var canUnlock = assessment && assessment.can_unlock;
		var needsReview = assessment && assessment.manual_review;
		if (allocate) {
			if (unlocked || pendingReview) {
				allocate.disabled = true;
				allocate.textContent = pendingReview
					? (i18n.certificateSubmittedForReview || 'Submitted for review')
					: (i18n.certificateAllocated || 'Certificate allocated');
			} else {
				allocate.disabled = !canUnlock;
				allocate.textContent = needsReview
					? (i18n.certificateSubmitForReview || 'Submit for review')
					: (i18n.certificateUnlock || 'Unlock certificate');
			}
		}
		if (download) {
			download.disabled = !unlocked;
		}
		var printBtn = panel.querySelector('[data-airb-certificate-print]');
		if (printBtn) {
			printBtn.disabled = !unlocked;
		}
	}

	function contactEmailFieldHtml(role, submissionEmail, locked, value) {
		if (!showContactEmailField(submissionEmail)) {
			return '';
		}
		var i18n = (window.airbBenchmark && airbBenchmark.i18n) || {};
		return '<label class="benchmark-certificate-reflection">' + esc(i18n.certificateContactEmail || 'Email for certificate updates') +
			'<input type="email" data-airb-certificate-contact-email value="' + esc(value || '') + '" placeholder="' + esc(i18n.certificateContactEmailPlaceholder || 'you@school.org or parent@email.com') + '" autocomplete="email"' + (locked ? ' readonly' : '') + '>' +
			'<span class="benchmark-certificate-reflection-hint">' + esc(i18n.certificateContactEmailHint || 'Required so we can email your certificate or tell you when it is approved.') + '</span></label>';
	}

	/**
	 * Step 2: what you did. Evidence fields only — the requirements checklist
	 * and the gate message are rendered above the form by panelHtml, because
	 * "what do I need?" has to be answerable before "fill this in".
	 */
	function evidenceFormHtml(role, cert, scoreEligible, unlocked) {
		cert = cert || {};
		unlocked = !!unlocked;
		var copy = roleCopy(role);
		var cfg = unlockConfig();
		var themes = cfg.themes || [];
		var disabled = !scoreEligible || unlocked;
		var html = '<section class="benchmark-certificate-step">';

		html += '<h4 class="benchmark-certificate-step-title"><span>2</span>Your evidence</h4>';

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

		html += '</section>';
		return html;
	}

	function certificateCanvas(scope) {
		return Art.canvasIn(scope);
	}

	function certificateFilename(scope) {
		var host = scope && scope.querySelector && scope.querySelector('[data-airb-certificate-preview]');
		var id = (host && host.dataset.certId) || '';
		var slug = String(id).replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '');
		return 'ai-awareness-day-certificate' + (slug && slug.toLowerCase() !== 'pending' ? '-' + slug : '') + '.png';
	}

	/* A real file, not a print dialog. The previous "download" only ever
	   opened a print window, which popup blockers eat and mobile handles
	   badly — and it left the user with nothing to attach or post. */
	function downloadCertificate(panel) {
		var canvas = certificateCanvas(panel);
		if (!canvas) return;
		paintPreviews(panel).then(function () {
			var done = function (blob) {
				if (!blob) {
					setStatus(panel, 'Could not build the certificate image. Try printing it instead.', true);
					return;
				}
				var url = URL.createObjectURL(blob);
				var link = document.createElement('a');
				link.href = url;
				link.download = certificateFilename(panel);
				document.body.appendChild(link);
				link.click();
				document.body.removeChild(link);
				setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
				setStatus(panel, 'Certificate downloaded.', false);
			};
			if (canvas.toBlob) canvas.toBlob(done, 'image/png');
			else done(null);
		});
	}

	function printCertificate(panel) {
		var canvas = certificateCanvas(panel);
		if (!canvas) return;
		paintPreviews(panel).then(function () {
			var win = window.open('', '_blank', 'width=1000,height=760');
			if (!win) {
				setStatus(panel, (window.airbBenchmark && airbBenchmark.i18n && airbBenchmark.i18n.certificatePopupBlocked) || 'Allow pop-ups to print your certificate, or use Download instead.', true);
				return;
			}
			var html = '<!doctype html><html><head><meta charset="utf-8"><title>AI Awareness Day Certificate</title>';
			html += '<style>@page{size:A4 landscape;margin:12mm}html,body{margin:0;padding:0;background:#fff}img{display:block;width:100%;height:auto}</style>';
			html += '</head><body><img alt="" src="' + canvas.toDataURL('image/png') + '"></body></html>';
			win.document.open();
			win.document.write(html);
			win.document.close();
			win.focus();
			setTimeout(function () {
				try { win.print(); } catch (e) { /* ignore */ }
			}, 400);
		});
	}

	Cert.panelHtml = function (model, role, accent) {
		model = model || {};
		role = role || roleFromRuntime();
		var cert = model.certificate || {};
		var title = certificateTitle(model, role);
		var unlocked = !!cert.unlocked;
		var pendingReview = !!cert.pendingReview || cert.status === 'pending_review';
		var participantName = cert.participantName || '';
		var submissionEmail = submissionEmailFromRuntime();
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
			verifyUrl: 'aiawarenessday.co.uk',
			theme: storedCert.evidence_theme || '',
		};
		var submissionId = submissionIdFromRuntime();
		var benchmarkScore = cert.currentScore || model.score || 0;

		var html = '<section class="teacher-dash-card benchmark-certificate-layout' + (unlocked ? ' is-unlocked' : '') + (pendingReview ? ' is-pending-review' : '') + '" data-airb-certificate-panel data-airb-role="' + esc(role) + '" data-airb-submission-id="' + esc(submissionId) + '" data-airb-benchmark-score="' + esc(benchmarkScore) + '" data-airb-score-eligible="' + (scoreEligible ? '1' : '0') + '" data-airb-unlocked="' + (unlocked ? '1' : '0') + '" data-airb-pending-review="' + (pendingReview ? '1' : '0') + '" data-airb-submission-email="' + esc(submissionEmail) + '">';
		html += '<div class="benchmark-certificate-summary">';
		html += '<div><p class="teacher-dash-scene" style="color:' + esc(accent || model.accent || '#006A7D') + '">Certificate</p>';
		html += '<h3 class="teacher-dash-progress-title">' + esc(title) + '</h3>';
		/* When the score gate is closed the line below states the gap and the
		   next move, so a generic "complete the evidence step below" here
		   just repeats it above a collapsed form. */
		var gateBlocks = !scoreEligible && !unlocked && !pendingReview;
		html += gateBlocks ? '</div>' : ('<p class="teacher-dash-cert-note">' + (unlocked
			? 'Certificate allocated. You can download or print it now.'
			: (pendingReview
				? 'Your evidence has been submitted. We will email you when your certificate is approved.'
				: 'Complete the evidence step below to unlock your AI Risk & Readiness Benchmark\u2122 Certificate.')) + '</p></div>');
		html += '<div class="benchmark-certificate-stats">';
		html += '<div><span>Current</span><strong>' + esc(cert.currentScore || model.score || 0) + '%</strong></div>';
		html += '<div><span>Need</span><strong>' + esc(threshold) + '%</strong></div>';
		html += '<div><span>Gap</span><strong>' + (scoreEligible ? 'Met' : ('+' + esc(cert.needed != null ? cert.needed : Math.max(0, threshold - (cert.currentScore || model.score || 0))))) + '</strong></div>';
		html += '</div></div>';
		html += '<div class="benchmark-certificate-grid">';
		html += '<div class="benchmark-certificate-form">';

		/* Below the score gate the whole form is disabled, so rendering it at
		   full height buries the one thing that matters — the gap, and what to
		   do about it — under a screen of dead fields. Lead with the gap;
		   the form waits behind a disclosure. */
		var gated = !scoreEligible && !unlocked && !pendingReview;

		/* Order follows the questions people actually ask, in order:
		   where am I (stats, above) -> what is required (checklist) ->
		   who am I (step 1) -> what did I do (step 2) -> the certificate. */
		if (gated) {
			html += '<p class="benchmark-certificate-gate is-blocked">' +
				'Reach at least ' + esc(threshold) + '% to unlock your certificate. You are at ' +
				esc(cert.currentScore || model.score || 0) + '% — work on your weakest areas in Overview, then retake the audit.' +
				'</p>';
		} else if (!unlocked && !pendingReview) {
			html += '<p class="benchmark-certificate-gate is-open">' + esc(unlockConfig().unlock_intro || 'Reach the benchmark score threshold and complete one of the evidence options below.') + '</p>';
		}

		// What unlocks it, before being asked to fill anything in.
		html += '<div data-airb-certificate-quality-wrap></div>';

		if (gated) {
			html += '<details class="benchmark-certificate-prep">';
			html += '<summary>See what you will need to provide</summary>';
		}

		html += '<section class="benchmark-certificate-step">';
		html += '<h4 class="benchmark-certificate-step-title"><span>1</span>Your details</h4>';
		html += '<label>Name on certificate<input type="text" data-airb-certificate-name value="' + esc(participantName) + '" placeholder="' + esc(namePlaceholder(role)) + '"' + ((unlocked || pendingReview) ? ' readonly' : '') + '></label>';
		html += contactEmailFieldHtml(role, submissionEmail, unlocked || pendingReview, submissionEmail);
		html += '</section>';

		html += evidenceFormHtml(role, storedCert, scoreEligible, unlocked || pendingReview);
		html += '<button type="button" class="airb__btn airb__btn--primary" data-airb-certificate-allocate ' + ((unlocked || pendingReview) ? 'disabled' : '') + '>' + (pendingReview ? 'Submitted for review' : (unlocked ? 'Certificate allocated' : 'Unlock certificate')) + '</button>';
		if (gated) {
			html += '</details>';
		}
		html += '<p class="benchmark-certificate-status" data-airb-certificate-status>' + (unlocked
			? ('Certificate ID ' + esc(cert.certificateId || ''))
			: (pendingReview
				? 'Waiting for AI Awareness Day to approve your evidence. Download will unlock after approval.'
				: 'Evidence is checked before unlock. This recognises progress — not certification as an expert user.')) + '</p>';
		html += '</div>';
		html += '<div class="benchmark-certificate-preview-wrap" data-airb-certificate-preview-wrap>';
		html += '<p class="benchmark-certificate-preview-label">' + (unlocked ? 'Your certificate' : 'Preview — updates as you fill in the form') + '</p>';
		html += previewHtml(preview);
		html += '</div>';
		/* Outside the preview wrap on purpose: unlocking re-renders that wrap's
		   innerHTML, which would throw these away along with their handlers.
		   Sits with the artwork visually; CSS hides it until unlocked, because
		   disabled download buttons read as broken. */
		html += '<div class="benchmark-certificate-actions">';
		html += '<button type="button" class="airb__btn airb__btn--ghost" data-airb-certificate-download ' + (unlocked ? '' : 'disabled') + '>Download certificate (PNG)</button>';
		html += '<button type="button" class="airb__btn airb__btn--ghost" data-airb-certificate-print ' + (unlocked ? '' : 'disabled') + '>Print</button>';
		html += '</div>';
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
		var i18n = (window.airbBenchmark && airbBenchmark.i18n) || {};
		var isPending = cert.status === 'pending_review' || !!cert.pending_review;
		var isUnlocked = cert.status === 'unlocked' || (!isPending && !!cert.certificate_id);
		panel.dataset.airbUnlocked = isUnlocked ? '1' : '0';
		panel.dataset.airbPendingReview = isPending ? '1' : '0';
		var previewWrap = panel.querySelector('[data-airb-certificate-preview-wrap]');
		if (previewWrap && isUnlocked) {
			previewWrap.innerHTML = '<p class="benchmark-certificate-preview-label">Your certificate</p>' + previewHtml({
				title: certificateTitle(null, role),
				role: role,
				participantName: cert.participant_name,
				body: copy.body || certificateBody(null, role),
				awardedAt: cert.awarded_at,
				certificateId: cert.certificate_id,
				verifyUrl: cert.verify_url || 'aiawarenessday.co.uk',
				theme: cert.evidence_theme || readEvidence(panel).theme || '',
			});
			paintPreviews(previewWrap);
		}
		/* The panel intro still read "complete the evidence step below to
		   unlock" after unlocking, so the one moment worth celebrating
		   looked like a form that had not been submitted. */
		var note = panel.querySelector('.teacher-dash-cert-note');
		if (note) {
			note.textContent = isPending
				? 'Your evidence has been submitted. We will email you when your certificate is approved.'
				: (isUnlocked ? 'Certificate unlocked. Download it as an image, or print it.' : note.textContent);
		}
		panel.classList.toggle('is-unlocked', isUnlocked);
		panel.classList.toggle('is-pending-review', isPending);
		if (isUnlocked && previewWrap) {
			previewWrap.setAttribute('tabindex', '-1');
			try {
				previewWrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
				previewWrap.focus({ preventScroll: true });
			} catch (e) {
				previewWrap.scrollIntoView();
			}
		}
		var allocate = panel.querySelector('[data-airb-certificate-allocate]');
		var download = panel.querySelector('[data-airb-certificate-download]');
		if (allocate) {
			allocate.disabled = true;
			allocate.textContent = isPending
				? (i18n.certificateSubmittedForReview || 'Submitted for review')
				: (i18n.certificateAllocated || 'Certificate allocated');
		}
		if (download) download.disabled = !isUnlocked;
		var printBtn = panel.querySelector('[data-airb-certificate-print]');
		if (printBtn) printBtn.disabled = !isUnlocked;
		if (cert.assessment) updateQuality(panel, cert.assessment);
		if (isPending) {
			setStatus(panel, i18n.certificatePendingReview || 'Submitted for review. We will email you when your certificate is approved.', false);
		} else if (isUnlocked) {
			setStatus(panel, (i18n.certificateAllocatedId || 'Certificate ID {id} allocated.').replace('{id}', cert.certificate_id || ''), false);
		}
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
				/* The strand chosen here colours the certificate, so the
				   preview has to follow the form, not just the name. */
				updatePreview(panel);
			};
			field.addEventListener('input', handler);
			field.addEventListener('change', handler);
		});
	}

	Cert.bind = function (root) {
		if (!root) return;
		var panels = root.querySelectorAll('[data-airb-certificate-panel]');
		panels.forEach(function (panel) {
			bindEvidenceInputs(panel);
			var unlocked = panel.dataset.airbUnlocked === '1';
			var pendingReview = panel.dataset.airbPendingReview === '1';
			var initialAssessment = assessPanel(panel);
			updateQuality(panel, initialAssessment);
			syncUnlockState(panel, { unlocked: unlocked, pendingReview: pendingReview }, initialAssessment);

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
					downloadCertificate(panel);
				});
			}

			var printBtn = panel.querySelector('[data-airb-certificate-print]');
			if (printBtn && !printBtn.dataset.airbBound) {
				printBtn.dataset.airbBound = '1';
				printBtn.addEventListener('click', function () {
					printCertificate(panel);
				});
			}

			paintPreviews(panel);

			var allocate = panel.querySelector('[data-airb-certificate-allocate]');
			if (!allocate || allocate.dataset.airbBound) return;
			allocate.dataset.airbBound = '1';
			allocate.addEventListener('click', function () {
				var cfg = window.airbBenchmark || {};
				var role = panel.dataset.airbRole || roleFromRuntime();
				var name = (panel.querySelector('[data-airb-certificate-name]') || {}).value || '';
				var evidence = readEvidence(panel);
				var assessment = assessPanel(panel);
				var contactEmailInput = panel.querySelector('[data-airb-certificate-contact-email]');
				var contactEmail = contactEmailInput ? contactEmailInput.value.trim() : '';
				var submissionEmail = panel.dataset.airbSubmissionEmail || submissionEmailFromRuntime();
				if (!name.trim()) {
					setStatus(panel, 'Add the name to show on the certificate.', true);
					return;
				}
				if (roleRequiresContactEmail(role, submissionEmail, !!assessment.manual_review) && !contactEmail && !submissionEmail) {
					setStatus(panel, (cfg.i18n && cfg.i18n.certificateContactEmailRequired) || 'Add an email address so we can send your certificate.', true);
					if (contactEmailInput) contactEmailInput.focus();
					return;
				}
				if (assessment.manual_review && !submissionEmail && !contactEmail) {
					setStatus(panel, (cfg.i18n && cfg.i18n.certificateContactEmailRequired) || 'Add an email address so we can tell you when your certificate is approved.', true);
					if (contactEmailInput) contactEmailInput.focus();
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
				body.append('contact_email', contactEmail);
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

	/**
	 * Standalone certificate view for the "check your certificate" magic
	 * link (?airb_verify=<hash>), rendered independently of the audit flow
	 * or any locally stored results — used when a certificate needed manual
	 * review and the participant is returning later, possibly on a
	 * different device or after their local snapshot has expired.
	 */
	Cert.standaloneViewHtml = function (cert) {
		cert = cert || {};
		var copy = cert.copy || roleCopy(cert.role);
		var pendingReview = !!cert.pending_review || cert.status === 'pending_review';
		var html = '<section class="teacher-dash-card benchmark-certificate-layout benchmark-certificate-standalone">';

		if (pendingReview) {
			html += '<p class="airb__notice">' + esc('This certificate is still awaiting manual review. We will email you as soon as it is approved.') + '</p>';
			return html + '</section>';
		}

		var title = copy.headline_secondary ? (copy.headline_primary + ' ' + copy.headline_secondary) : (copy.headline_primary || certificateTitle(null, cert.role));
		html += previewHtml({
			title: title,
			role: cert.role,
			participantName: cert.participant_name,
			body: copy.body || certificateBody(null, cert.role),
			awardedAt: cert.awarded_at,
			certificateId: cert.certificate_id,
			verifyUrl: cert.verify_url || 'aiawarenessday.co.uk',
			theme: cert.evidence_theme || '',
		});
		html += '<div class="benchmark-certificate-actions" style="margin-top:1rem;">';
		html += '<button type="button" class="airb__btn airb__btn--primary" data-airb-certificate-standalone-download>' + esc('Download certificate (PNG)') + '</button>';
		html += '<button type="button" class="airb__btn airb__btn--ghost" data-airb-certificate-standalone-print>' + esc('Print') + '</button>';
		html += '</div>';
		html += '<p class="benchmark-certificate-status" data-airb-certificate-status></p>';
		html += '</section>';
		return html;
	};

	Cert.bindStandalone = function (root) {
		if (!root) return;
		paintPreviews(root);
		var btn = root.querySelector('[data-airb-certificate-standalone-download]');
		if (btn && !btn.dataset.airbBound) {
			btn.dataset.airbBound = '1';
			btn.addEventListener('click', function () {
				downloadCertificate(root);
			});
		}
		var printBtn = root.querySelector('[data-airb-certificate-standalone-print]');
		if (printBtn && !printBtn.dataset.airbBound) {
			printBtn.dataset.airbBound = '1';
			printBtn.addEventListener('click', function () {
				printCertificate(root);
			});
		}
	};

	Cert.roleCopy = roleCopy;
}());
