/**
 * airb-certificate-evidence.js
 *
 * Client-side evidence quality scoring (mirrors class-airb-certificate-evidence.php).
 *
 * Depends on: airb-core
 * Exposes: AIRB.CertificateEvidence
 */
'use strict';

(function () {
	window.AIRB = window.AIRB || {};
	var CE = AIRB.CertificateEvidence || {};
	AIRB.CertificateEvidence = CE;

	var THEME_SLUGS = ['smart', 'creative', 'responsible', 'future', 'safe'];
	var MIN_SELF_DECLARED_CHARS = 40;

	var VERBS = [
		'taught', 'teach', 'led', 'lead', 'discussed', 'discuss', 'ran', 'run', 'created', 'create',
		'designed', 'design', 'updated', 'update', 'shared', 'share', 'introduced', 'introduce',
		'modelled', 'modeled', 'model', 'facilitated', 'facilitate', 'delivered', 'deliver',
		'implemented', 'implement', 'reviewed', 'review', 'checked', 'check', 'verified', 'verify',
		'adapted', 'adapt', 'planned', 'plan', 'organised', 'organized', 'organize', 'demonstrated',
		'demonstrate', 'practised', 'practiced', 'practice', 'applied', 'apply', 'completed', 'complete',
		'started', 'start', 'built', 'build', 'wrote', 'write', 'presented', 'present', 'coached',
		'coach', 'supported', 'support', 'guided', 'guide', 'explored', 'explore', 'trained', 'train',
		'attended', 'attend', 'published', 'publish', 'added', 'add', 'revised', 'revise', 'tested',
		'test', 'used', 'use', 'tried', 'try', 'asked', 'ask', 'explained', 'explain', 'reflected',
		'reflect', 'talked', 'talk', 'showed', 'show', 'helped', 'help', 'changed', 'change', 'improved',
		'improve', 'rehearsed', 'rehearse', 'drafted', 'draft', 'circulated',
	];

	var INVOLVEMENT = [
		'pupil', 'student', 'learner', 'class', 'colleague', 'staff', 'parent', 'carer', 'team',
		'year ', 'children', 'child', 'family', 'form', 'tutor', 'department', 'slt', 'governor',
		'trust', 'school', 'office', 'reception', 'my class', 'our school', 'my child', 'young people',
	];

	var ROLE_KEYWORDS = {
		teacher: ['lesson', 'class', 'pupil', 'student', 'classroom', 'teaching', 'marking', 'subject', 'curriculum'],
		student: ['study', 'homework', 'assignment', 'revision', 'learning', 'exam', 'coursework', 'school work'],
		parent: ['child', 'home', 'family', 'homework', 'conversation', 'carer', 'parent', 'kitchen table'],
		leader: ['staff', 'policy', 'governance', 'trust', 'slt', 'safeguarding', 'leadership', 'whole school'],
		support: ['office', 'admin', 'data', 'reception', 'operations', 'hr', 'finance', 'records'],
		public: ['personal', 'privacy', 'online', 'family', 'myself', 'home', 'account', 'password'],
	};

	function normalizeRole(role) {
		role = String(role || '').trim();
		if (role === 'support_staff') return 'support';
		return role || 'teacher';
	}

	function unlockConfig() {
		var cfg = window.airbBenchmark || {};
		return cfg.certificateUnlock || {};
	}

	function threshold(key, fallback) {
		var cfg = unlockConfig();
		return cfg[key] != null ? cfg[key] : fallback;
	}

	function tierLabels() {
		var cfg = unlockConfig();
		return cfg.tier_labels || {
			needs_more_detail: 'Needs more detail',
			likely_valid: 'Likely valid',
			strong_evidence: 'Strong evidence',
			needs_manual_review: 'Needs manual review',
		};
	}

	function pathwayConfig() {
		var cfg = unlockConfig();
		return cfg.pathways || [
			{ key: 'self_declared', label: 'Self-declared action', hint: 'Choose a theme and describe what you did in at least 40 characters.' },
			{ key: 'structured_reflection', label: 'Structured reflection', hint: 'Concrete action verb plus at least 120 characters across your answers.' },
			{ key: 'evidence_link', label: 'Evidence link', hint: 'Add a link to a slide, activity, policy note, or CPD artefact.' },
			{ key: 'quality_validated', label: 'Quality-checked evidence', hint: 'Reach an evidence quality score of at least 70.' },
		];
	}

	function hasConcreteAction(text) {
		text = String(text || '').toLowerCase();
		if (text.length < 12) return false;
		for (var i = 0; i < VERBS.length; i++) {
			var re = new RegExp('\\b' + VERBS[i].replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\b', 'i');
			if (re.test(text)) return true;
		}
		return false;
	}

	function mentionsInvolvement(text) {
		var lower = String(text || '').toLowerCase();
		for (var i = 0; i < INVOLVEMENT.length; i++) {
			if (lower.indexOf(INVOLVEMENT[i]) !== -1) return true;
		}
		return false;
	}

	function isGeneric(text) {
		var lower = String(text || '').toLowerCase().replace(/\s+/g, ' ').trim();
		if (lower.length < 40) return true;
		var generic = [
			'i used ai', 'used chatgpt', 'used ai tools', 'completed the benchmark',
			'did ai awareness day', 'participated in ai awareness day', 'learned about ai', 'explored ai',
		];
		for (var i = 0; i < generic.length; i++) {
			if (lower === generic[i] || lower === generic[i] + '.') return true;
		}
		return false;
	}

	function matchesRole(role, text) {
		role = normalizeRole(role);
		var needles = ROLE_KEYWORDS[role] || [];
		var lower = String(text || '').toLowerCase();
		if (!needles.length) return lower.length >= 40;
		for (var i = 0; i < needles.length; i++) {
			if (lower.indexOf(needles[i]) !== -1) return true;
		}
		return false;
	}

	function isValidEvidenceLink(link) {
		link = String(link || '').trim();
		if (!link) return false;
		try {
			var url = new URL(link);
			return url.protocol === 'http:' || url.protocol === 'https:';
		} catch (e) {
			return false;
		}
	}

	function buildPathways(themeOk, action, combined, link, verbOk, reflectionOk, qualityScore, qualityThreshold) {
		return {
			self_declared: themeOk && action.length >= MIN_SELF_DECLARED_CHARS && !isGeneric(action),
			structured_reflection: themeOk && verbOk && reflectionOk,
			evidence_link: themeOk && isValidEvidenceLink(link),
			quality_validated: themeOk && qualityScore >= qualityThreshold,
		};
	}

	function tierForScore(score, manualReview, qualityThreshold) {
		if (manualReview && score >= qualityThreshold) return 'needs_manual_review';
		if (score >= 85) return 'strong_evidence';
		if (score >= qualityThreshold) return 'likely_valid';
		return 'needs_more_detail';
	}

	CE.assess = function (role, theme, action, change, link, benchmarkScore) {
		role = normalizeRole(role);
		theme = String(theme || '').trim();
		action = String(action || '').trim();
		change = String(change || '').trim();
		link = String(link || '').trim();
		benchmarkScore = parseInt(benchmarkScore, 10) || 0;
		var combined = (action + ' ' + change).trim();
		var minChars = threshold('min_reflection_chars', 120);
		var qualityThreshold = threshold('quality_threshold', 70);
		var scoreThreshold = threshold('score_threshold', 70);
		var checks = {};
		var messages = [];
		var score = 0;

		var themeOk = THEME_SLUGS.indexOf(theme) !== -1;
		checks.theme = themeOk;
		if (themeOk) score += 20;
		else messages.push('Choose one AI Awareness Day theme.');

		var verbOk = hasConcreteAction(action);
		checks.concrete_action = verbOk;
		if (verbOk) score += 25;
		else messages.push('Describe a specific action with a clear activity (for example: taught, discussed, reviewed, planned, modelled).');

		var reflectionLen = combined.length;
		var reflectionOk = reflectionLen >= minChars;
		checks.reflection_length = reflectionOk;
		if (reflectionOk) score += 20;
		else messages.push('Add more detail — at least ' + minChars + ' characters across your answers (currently ' + reflectionLen + ').');

		var involvedOk = mentionsInvolvement(combined);
		checks.involvement = involvedOk;
		if (involvedOk) score += 15;
		else messages.push('Say who was involved (for example: pupils, colleagues, parents, learners, or your class).');

		var generic = isGeneric(combined);
		checks.not_generic = !generic;
		if (!generic) score += 10;
		else {
			messages.push('Your answer looks too generic — add a specific example from your context.');
			score = Math.max(0, score - 15);
		}

		var roleOk = matchesRole(role, combined);
		checks.role_match = roleOk;
		if (roleOk) score += 10;
		else messages.push('Make the example clearly relevant to your benchmark role.');

		var manualReview = !!link;
		if (link) {
			score = Math.min(100, score + 5);
			messages.push('Evidence link added — submit for review. Download unlocks after AI Awareness Day approves your evidence.');
		}

		score = Math.max(0, Math.min(100, score));
		var tier = tierForScore(score, manualReview, qualityThreshold);
		var labels = tierLabels();
		var pathways = buildPathways(themeOk, action, combined, link, verbOk, reflectionOk, score, qualityThreshold);
		var evidenceSatisfied = pathways.self_declared || pathways.structured_reflection || pathways.evidence_link || pathways.quality_validated;
		var scoreEligible = benchmarkScore >= scoreThreshold;
		var canUnlock = scoreEligible && themeOk && evidenceSatisfied;

		if (!scoreEligible) {
			messages.unshift('Benchmark score must be at least ' + scoreThreshold + '% (currently ' + benchmarkScore + '%).');
		} else if (!evidenceSatisfied) {
			messages.push('Complete one evidence option: self-declared action, structured reflection, evidence link, or quality score of at least 70.');
		}

		return {
			quality_score: score,
			quality_tier: tier,
			tier_label: labels[tier] || tier,
			can_unlock: canUnlock,
			score_eligible: scoreEligible,
			evidence_satisfied: evidenceSatisfied,
			pathways: pathways,
			checks: checks,
			messages: messages,
			reflection_chars: reflectionLen,
			manual_review: manualReview,
		};
	};

	CE.normalizeRole = normalizeRole;
	CE.scoreThreshold = function () {
		return threshold('score_threshold', 70);
	};
	CE.pathwayConfig = pathwayConfig;
}());
