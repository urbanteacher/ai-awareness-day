/**
 * airb-dashboard-model.js
 *
 * Maps live benchmark API payloads into a role dashboard view model aligned
 * with the education-focused results layout (domain grid, focus stack, progress).
 *
 * Depends on: airb-core.js
 * Exposes: AIRB.DashboardModel
 */
'use strict';

(function () {
	window.AIRB = window.AIRB || {};
	var DM = AIRB.DashboardModel || {};
	AIRB.DashboardModel = DM;

	var SCORE_SLUG = {
		independent_practice: 'ai_dependency',
		privacy_data_protection: 'privacy',
		assessment_design: 'assessment_integrity',
		bias_equality: 'bias_readiness',
	};

	var TEACHER_DOMAIN_ORDER = [
		'safe_adoption',
		'human_oversight',
		'independent_practice',
		'privacy',
		'assessment_design',
		'ai_literacy',
		'safeguarding',
		'bias_equality',
	];

	var TEACHER_DOMAIN_LABELS = {
		safe_adoption: 'Safe adoption',
		human_oversight: 'Human oversight',
		independent_practice: 'Independent practice',
		privacy: 'Privacy',
		assessment_design: 'Assessment design',
		ai_literacy: 'AI literacy',
		safeguarding: 'Safeguarding',
		bias_equality: 'Bias & equality',
	};

	var TEACHER_DOMAIN_PROMPTS = {
		safe_adoption: 'Assess tools first',
		human_oversight: 'Check before use',
		independent_practice: 'Try before AI',
		privacy: 'Keep pupil data out',
		assessment_design: 'Require thinking evidence',
		ai_literacy: 'Know limits',
		safeguarding: 'Respond to AI harms',
		bias_equality: 'Check unfair outputs',
	};

	/** Actionable guidance CTA copy aligned with the education dashboard demo. */
	var GUIDANCE_PRIORITIES = {
		safe_adoption: 'Review one AI tool against school approval criteria before your next classroom use.',
		human_oversight: 'Verify one AI-generated resource before pupils see it this week.',
		independent_practice: 'Attempt one planning or feedback task independently before using AI to refine it.',
		privacy: 'Remove identifiable pupil data from your next AI prompt.',
		assessment_design: 'Redesign one AI-assisted task so pupils must show thinking beyond the first generated answer.',
		ai_literacy: 'Test one AI tool on an edge case and note where it commonly fails.',
		safeguarding: 'Respond to one AI-related pupil concern using your school safeguarding route.',
		bias_equality: 'Add a simple bias spot-check to your verify-before-use routine this week.',
	};

	function clampPct(value) {
		var n = Math.round(Number(value));
		if (isNaN(n)) return 0;
		return Math.max(0, Math.min(100, n));
	}

	function config() {
		return (window.airbBenchmark && airbBenchmark.config) || {};
	}

	function teacherResultConfig(cfg) {
		return (cfg && cfg.teacher_result) || {};
	}

	function studentResultConfig(cfg) {
		return (cfg && cfg.student_result) || {};
	}

	var STUDENT_DOMAIN_ORDER = [
		'independent_thinking',
		'verification',
		'assessment_integrity',
		'privacy_awareness',
		'ai_literacy',
		'safeguarding',
		'bias_fairness',
	];

	var STUDENT_DOMAIN_LABELS = {
		independent_thinking: 'Independent thinking',
		verification: 'Verification',
		assessment_integrity: 'Assessment integrity',
		privacy_awareness: 'Privacy awareness',
		ai_literacy: 'AI literacy',
		safeguarding: 'Safeguarding',
		bias_fairness: 'Bias & fairness',
	};

	var STUDENT_DOMAIN_PROMPTS = {
		independent_thinking: 'Make first attempt',
		verification: 'Check the answer',
		assessment_integrity: 'Make it your own',
		privacy_awareness: 'Protect identity',
		ai_literacy: 'Know limits',
		safeguarding: 'Report fake media',
		bias_fairness: 'Spot unfair outputs',
	};

	var STUDENT_GUIDANCE_PRIORITIES = {
		independent_thinking: 'Spend five minutes attempting the work before asking AI for help or explanation.',
		verification: 'Check one AI answer against a trusted source before you hand work in.',
		assessment_integrity: 'Rewrite one AI-assisted answer in your own words and explain the thinking behind it.',
		privacy_awareness: 'Remove personal details from your next AI prompt.',
		ai_literacy: 'Test one AI answer on a topic you know well and note where it goes wrong.',
		safeguarding: 'Tell a trusted adult if you see suspicious AI-generated images or harmful content.',
		bias_fairness: 'Check one AI answer for unfair or stereotyped language before using it in schoolwork.',
	};

	function copyTiersFor(role) {
		var ct = (config().copy_tiers || {})[role] || {};
		return ct;
	}

	function domainLabel(key, cfg) {
		var domains = (cfg && cfg.domains) || {};
		var tiers = copyTiersFor('teacher').domains || {};
		var scoreSlug = SCORE_SLUG[key] || key;
		if (TEACHER_DOMAIN_LABELS[key]) {
			return TEACHER_DOMAIN_LABELS[key];
		}
		if (tiers[key] && tiers[key].label) {
			return tiers[key].label;
		}
		if (domains[scoreSlug] && domains[scoreSlug].label) {
			return domains[scoreSlug].label;
		}
		return key;
	}

	function domainPrompt(key, pct, cfg) {
		if (TEACHER_DOMAIN_PROMPTS[key]) {
			return TEACHER_DOMAIN_PROMPTS[key];
		}
		var recs = (cfg && cfg.domain_recommendations) || {};
		var scoreSlug = SCORE_SLUG[key] || key;
		var text = recs[scoreSlug] || '';
		if (!text) return '';
		if (pct >= 75) {
			return text.split('.')[0].slice(0, 48);
		}
		return text.length > 56 ? text.slice(0, 53) + '…' : text;
	}

	function domainTone(pct) {
		if (pct >= 75) return 'secure';
		if (pct >= 50) return 'practice';
		return 'attention';
	}

	function readinessBand(score) {
		var pct = clampPct(score);
		if (pct >= 90) return 'leading';
		if (pct >= 75) return 'strong';
		if (pct >= 60) return 'established';
		if (pct >= 40) return 'developing';
		return 'emerging';
	}

	function domainReadinessScore(domains, key, results) {
		if (key === 'bias_equality') {
			if (results && results.bias_readiness != null) {
				return clampPct(results.bias_readiness);
			}
			return null;
		}

		var scoreKey = SCORE_SLUG[key] || key;
		var dom = domains[scoreKey];
		if (!dom) return null;
		if (typeof dom === 'number') {
			return clampPct(dom);
		}
		if (typeof dom.readiness_percentage === 'number') {
			if (dom.questions_answered != null && dom.questions_answered < 1) {
				return null;
			}
			return clampPct(dom.readiness_percentage);
		}
		return null;
	}

	function oversightPct(results) {
		var domains = results.domain_scores || {};
		var ho = domains.human_oversight;
		if (ho && ho.questions_answered) {
			return clampPct(ho.readiness_percentage);
		}
		if (results.human_oversight_ratio != null) {
			return clampPct(results.human_oversight_ratio);
		}
		return null;
	}

	function dependencyNote(results, trCfg) {
		var dep = clampPct(results.dependency_index || 0);
		if (dep >= 60) {
			return 'Feedback drafting is a common first pressure point.';
		}
		if (dep >= 40) {
			return 'Build try-first habits before reaching for AI.';
		}
		return trCfg.dependency_score_note || 'Lower % means less reliance on AI.';
	}

	function roleDashboardCopy(dashboard, group, key, fallback) {
		var values = (dashboard && dashboard[group]) || {};
		var scoreKey = SCORE_SLUG[key] || key;
		return values[key] || values[scoreKey] || values.default || fallback || '';
	}

	function oversightNote(results, tr) {
		var ui = tr && tr.ui && tr.ui.oversight ? tr.ui.oversight : null;
		if (ui && ui.consequence) return ui.consequence;
		var pct = oversightPct(results);
		if (pct == null) return '';
		if (pct >= 76) return 'Strong checking before content reaches pupils.';
		if (pct >= 51) return 'Moderate checking — not yet a consistent habit.';
		return 'Most AI output may be going out without meaningful review.';
	}

	function mapStrengths(tr) {
		var items = (tr && tr.strengths) || [];
		return items.map(function (item) {
			if (typeof item === 'string') {
				return { title: item, detail: '' };
			}
			return {
				title: item.title || '',
				detail: item.detail || '',
			};
		});
	}

	function mapFocusAreas(tr, results) {
		var areas = (tr && tr.focus_areas) || [];
		var out = areas.map(function (area) {
			return {
				label: area.label || '',
				pct: clampPct(area.pct),
				summary: area.summary || '',
				likely_impact: area.likely_impact || [],
				actions: area.actions || [],
				challenge_heading: area.challenge_heading || '',
				slug: area.slug || '',
			};
		});

		var biasHealth = tr && tr.bias_health ? tr.bias_health : null;
		var biasScore = results && results.bias_readiness != null ? clampPct(results.bias_readiness) : null;
		var hasBiasFocus = out.some(function (area) {
			return area.slug === 'bias_equality' || /bias|equality/i.test(area.label || '');
		});

		if (!hasBiasFocus && biasScore !== null && biasScore < 75) {
			out.push({
				label: 'Bias & equality',
				pct: biasScore,
				summary: (biasHealth && biasHealth.callout) || 'AI outputs can be unfair or discriminatory. Build a simple bias spot-check into your verify-before-use routine.',
				likely_impact: [
					'Stereotypes or unfair assumptions may reach pupils unchecked',
					'Equality and safeguarding risks may be missed in everyday AI use',
					'Pupils may not see bias challenged clearly by adults',
				],
				actions: [
					'Check AI examples for protected characteristics and stereotypes',
					'Add bias review to your normal output verification habit',
					'Use one lesson example to show pupils how AI can be unfair',
				],
				slug: 'bias_equality',
			});
		}

		out.sort(function (a, b) {
			return (a.pct || 0) - (b.pct || 0);
		});
		return out;
	}

	function mapResources(nextSteps) {
		var links = (nextSteps && nextSteps.resource_links) || [];
		return links.map(function (link) {
			return {
				label: link.label || '',
				url: link.url || '',
				image: link.image || '',
				external: !!link.external,
				kicker: link.kicker || '',
				description: link.description || '',
			};
		});
	}

	function mapPeer(tr) {
		var pb = (tr && tr.peer_benchmark) || {};
		return {
			comparisonLabel: 'How you compare to other teachers',
			averageScore: clampPct(pb.average_score),
			topQuartile: clampPct(pb.top_quartile),
			yourScore: clampPct(pb.your_score),
			sampleSize: pb.sample_size || 0,
			isEstimated: !!pb.is_estimated,
		};
	}

	function mapCertificate(results) {
		var score = clampPct(results.alignment_score || 0);
		var source = (results && results.certificate) || {};
		var threshold = source.score_threshold != null ? clampPct(source.score_threshold) : 70;
		var currentScore = clampPct(source.current_score == null ? score : source.current_score);
		return {
			currentScore: currentScore,
			unlockAt: threshold,
			needed: source.needed == null ? Math.max(0, threshold - currentScore) : Math.max(0, parseInt(source.needed, 10) || 0),
			scoreThreshold: threshold,
			scoreEligible: source.score_eligible != null ? !!source.score_eligible : currentScore >= threshold,
			unlocked: !!source.unlocked,
			status: source.status || (source.unlocked ? 'unlocked' : 'draft'),
			certificateId: source.certificate_id || '',
			participantName: source.participant_name || '',
			schoolName: source.school_name || '',
			awardedAt: source.awarded_at || '',
			verificationHash: source.verification_hash || '',
			evidenceTheme: source.evidence_theme || '',
			evidenceAction: source.evidence_action || '',
			evidenceChange: source.evidence_change || '',
			evidenceLink: source.evidence_link || '',
			evidenceQualityScore: source.evidence_quality_score || 0,
			evidenceQualityTier: source.evidence_quality_tier || '',
		};
	}

	function mapFollowUp(results, tr) {
		var interest = results.interest_form || {};
		var hero = (tr && tr.next_steps && tr.next_steps.hero) || {};
		return {
			supportOptions: interest.support_options || [],
			suggestedInterests: interest.suggested_interests || [],
			weakDomains: interest.weak_domains || [],
			heroKey: hero.key || '',
			heroTitle: hero.title || '',
			heroBody: hero.body || '',
		};
	}

	function mapDomains(results, cfg) {
		var domains = results.domain_scores || {};
		var out = [];

		TEACHER_DOMAIN_ORDER.forEach(function (key) {
			var value = domainReadinessScore(domains, key, results);
			if (value === null) return;
			out.push({
				key: key,
				label: domainLabel(key, cfg),
				value: value,
				tone: domainTone(value),
				prompt: domainPrompt(key, value, cfg),
			});
		});

		return out;
	}

	function headline(results, tr) {
		if (tr && tr.performance_headline) {
			return tr.performance_headline;
		}
		var ui = tr && tr.ui && tr.ui.hero ? tr.ui.hero : null;
		if (ui && ui.consequence) {
			return ui.consequence;
		}
		return '';
	}

	function weakestDomain(domains) {
		if (!domains || !domains.length) return null;
		return domains.reduce(function (min, domain) {
			return !min || domain.value < min.value ? domain : min;
		}, null);
	}

	function weakestFocusArea(focus) {
		if (!focus || !focus.length) return null;
		return focus.reduce(function (min, area) {
			return !min || area.pct < min.pct ? area : min;
		}, null);
	}

	function priority(results, tr, domains) {
		var weakest = weakestDomain(domains);
		if (weakest && weakest.key && GUIDANCE_PRIORITIES[weakest.key]) {
			return GUIDANCE_PRIORITIES[weakest.key];
		}

		var focus = mapFocusAreas(tr, results);
		var weakestFocus = weakestFocusArea(focus);
		if (weakestFocus && weakestFocus.actions && weakestFocus.actions.length) {
			return weakestFocus.actions[0];
		}
		if (weakestFocus && weakestFocus.summary) {
			return weakestFocus.summary;
		}
		if (focus.length && focus[0].actions && focus[0].actions.length) {
			return focus[0].actions[0];
		}
		if (focus.length && focus[0].summary) {
			return focus[0].summary;
		}
		return '';
	}

	function studentHeadline(sr) {
		if (sr && sr.performance_headline) {
			return sr.performance_headline;
		}
		var ui = sr && sr.ui && sr.ui.hero ? sr.ui.hero : null;
		if (ui && ui.consequence) {
			return ui.consequence;
		}
		return '';
	}

	function metricBySlug(metrics, slug) {
		if (!metrics || !slug) return null;
		for (var i = 0; i < metrics.length; i++) {
			if (metrics[i].slug === slug) {
				return metrics[i];
			}
		}
		return null;
	}

	function studentDomainValue(key, metrics, results) {
		if (key === 'independent_thinking') {
			var independent = metricBySlug(metrics, 'independent_thinking');
			if (independent) {
				return clampPct(independent.value);
			}
			if (results.dependency_index != null) {
				return clampPct(100 - results.dependency_index);
			}
			return null;
		}

		var metricSlug = {
			verification: 'verification_skills',
			privacy_awareness: 'privacy_awareness',
			ai_literacy: 'ai_literacy',
			bias_fairness: 'bias_fairness',
		}[key];
		if (metricSlug) {
			var row = metricBySlug(metrics, metricSlug);
			if (row) {
				return clampPct(row.value);
			}
		}

		if (key === 'assessment_integrity') {
			var assessment = (results.domain_scores || {}).assessment_integrity;
			if (assessment && assessment.questions_answered >= 1) {
				return clampPct(assessment.readiness_percentage);
			}
		}

		if (key === 'safeguarding') {
			var dom = (results.domain_scores || {}).safeguarding;
			if (dom && dom.questions_answered >= 1) {
				return clampPct(dom.readiness_percentage);
			}
		}

		if (key === 'bias_fairness') {
			var biasHealth = results.student_results && results.student_results.bias_health;
			if (biasHealth && biasHealth.score != null) {
				return clampPct(biasHealth.score);
			}
			if (results.bias_readiness != null) {
				return clampPct(results.bias_readiness);
			}
		}

		return null;
	}

	function mapStudentDomains(metrics, results) {
		var out = [];
		STUDENT_DOMAIN_ORDER.forEach(function (key) {
			var value = studentDomainValue(key, metrics, results);
			if (value === null) return;
			out.push({
				key: key,
				label: STUDENT_DOMAIN_LABELS[key],
				value: value,
				tone: domainTone(value),
				prompt: STUDENT_DOMAIN_PROMPTS[key],
			});
		});
		return out;
	}

	function mapStudentFocusAreas(sr, results) {
		var areas = (sr && sr.focus_areas) || [];
		var out = areas.map(function (area) {
			var impact = area.likely_impact || area.challenge_bullets || [];
			if (!impact.length && area.challenge_body) {
				impact = [area.challenge_body];
			}
			return {
				label: area.label || '',
				pct: clampPct(area.pct),
				summary: area.summary || '',
				likely_impact: impact,
				actions: area.actions || [],
				challenge_heading: area.challenge_heading || '',
				slug: area.slug || '',
			};
		});

		var biasHealth = sr && sr.bias_health ? sr.bias_health : null;
		var biasScore = biasHealth && biasHealth.score != null
			? clampPct(biasHealth.score)
			: (results && results.bias_readiness != null ? clampPct(results.bias_readiness) : null);
		var hasBiasFocus = out.some(function (area) {
			return area.slug === 'bias_fairness' || /bias|fairness/i.test(area.label || '');
		});

		if (!hasBiasFocus && biasScore !== null && biasScore < 75) {
			out.push({
				label: 'Bias & fairness',
				pct: biasScore,
				summary: (biasHealth && biasHealth.callout) || 'AI can produce unfair or stereotyped answers. Learning to spot this helps you use AI safely and fairly.',
				likely_impact: [
					'Unfair assumptions may appear in answers that sound confident',
					'Schoolwork may repeat stereotypes without you noticing',
					'You may miss when an AI answer treats groups of people unfairly',
				],
				actions: [
					'Check one AI answer for stereotypes or unfair language',
					'Ask whether different groups of people are represented fairly',
					'Tell a teacher if an AI answer feels harmful or discriminatory',
				],
				slug: 'bias_fairness',
			});
		}

		out.sort(function (a, b) {
			return (a.pct || 0) - (b.pct || 0);
		});
		return out.slice(0, 4);
	}

	function mapStudentStrengths(sr) {
		var items = (sr && sr.strength_items) || [];
		if (items.length) {
			return items.map(function (item) {
				return {
					title: item.title || '',
					detail: item.detail || '',
				};
			});
		}
		return mapStrengths({
			strengths: (sr.strengths || []).map(function (item) {
				if (typeof item === 'string') {
					return { title: item, detail: '' };
				}
				return item;
			}),
		});
	}

	function mapStudentPeer(sr) {
		var pb = (sr && sr.peer_benchmark) || {};
		return {
			comparisonLabel: 'How you compare to other students',
			averageScore: clampPct(pb.average_score),
			topQuartile: clampPct(pb.top_quartile),
			yourScore: clampPct(pb.your_score),
			sampleSize: pb.sample_size || 0,
			isEstimated: !!pb.is_estimated,
		};
	}

	function studentPriority(sr, domains, results) {
		var weakest = weakestDomain(domains);
		if (weakest && weakest.key && STUDENT_GUIDANCE_PRIORITIES[weakest.key]) {
			return STUDENT_GUIDANCE_PRIORITIES[weakest.key];
		}

		var focus = mapStudentFocusAreas(sr, results);
		var weakestFocus = weakestFocusArea(focus);
		if (weakestFocus && weakestFocus.actions && weakestFocus.actions.length) {
			return weakestFocus.actions[0];
		}
		if (weakestFocus && weakestFocus.summary) {
			return weakestFocus.summary;
		}
		if (focus.length && focus[0].actions && focus[0].actions.length) {
			return focus[0].actions[0];
		}
		if (focus.length && focus[0].summary) {
			return focus[0].summary;
		}
		return '';
	}

	function independentNote(metric, srCfg) {
		if (!metric) return '';
		var pct = clampPct(metric.value);
		if (pct >= 60) return 'Strong try-first habits before reaching for AI.';
		if (pct >= 40) return 'Try-first habit needs practice.';
		return srCfg.independent_thinking_note || 'Build independence before asking AI for help.';
	}

	function verificationNote(metric) {
		if (!metric) return '';
		var pct = clampPct(metric.value);
		if (pct >= 76) return 'You often check AI answers before relying on them.';
		if (pct >= 51) return 'Checks happen when stakes are high.';
		return 'Practice checking AI answers even for low-stakes work.';
	}

	function studentJourney(sr) {
		var journey = sr && sr.learning_journey;
		if (journey && journey.length) {
			return journey.map(function (step) {
				if (typeof step === 'string') return step;
				return step.label || step.title || '';
			}).filter(Boolean);
		}
		return ['Aware learner', 'Independent attempt', 'Verification habit', 'AI study mentor'];
	}

	function parentResultConfig(cfg) {
		return (cfg && cfg.parent_result) || {};
	}

	var PARENT_DOMAIN_DEFS = [
		{ source: 'parent_awareness', label: 'Awareness', prompt: 'Know what they use' },
		{ source: 'home_ai_safety', label: 'Home AI safety', prompt: 'Set boundaries' },
		{ source: 'homework_oversight', label: 'Homework oversight', prompt: 'Explain in own words' },
		{ source: 'parent_ai_dependency', label: 'Balanced AI use', prompt: 'Try first at home' },
		{ source: 'online_risk_awareness', label: 'Deepfake awareness', prompt: 'Know what to do' },
		{ source: 'school_partnership', label: 'School partnership', prompt: 'Ask for expectations' },
	];

	var PARENT_GUIDANCE_PRIORITIES = {
		parent_awareness: 'Talk openly with your child about which AI tools they use and what they use them for.',
		home_ai_safety: 'Agree simple home rules for AI — including what personal information never goes into a tool.',
		homework_oversight: 'Create a simple home agreement for AI-assisted homework and talk through one example together.',
		parent_ai_dependency: 'Model try-first habits at home: use AI to check or explain, not to produce the homework answer.',
		online_risk_awareness: 'Agree what your child should do if they see AI-generated harmful or fake content online.',
		school_partnership: 'Check your school\'s AI expectations together and ask one teacher how AI should be used at home.',
	};

	function parentHeadline(pr) {
		var ui = pr && pr.ui && pr.ui.hero ? pr.ui.hero : null;
		if (ui && ui.consequence) return ui.consequence;
		return '';
	}

	function parentMetricBySource(homeMetrics, source) {
		if (!homeMetrics) return null;
		for (var i = 0; i < homeMetrics.length; i++) {
			var m = homeMetrics[i];
			if (m.source === source || m.slug === source) return m;
		}
		return null;
	}

	function parentDomainValue(source, homeMetrics, display) {
		var metric = parentMetricBySource(homeMetrics, source);
		if (metric) return clampPct(metric.value);
		var dom = (display || {})[source];
		if (!dom) return null;
		if (dom.metric_type === 'risk') {
			return clampPct(100 - (dom.risk_percentage || 0));
		}
		return clampPct(dom.readiness_percentage);
	}

	function mapParentDomains(homeMetrics, results) {
		var display = (results && results.parent_display_domains) || {};
		var out = [];
		PARENT_DOMAIN_DEFS.forEach(function (def) {
			var value = parentDomainValue(def.source, homeMetrics, display);
			if (value === null) return;
			out.push({
				key: def.source,
				label: def.label,
				value: value,
				tone: domainTone(value),
				prompt: def.prompt,
			});
		});
		return out;
	}

	function mapParentFocusAreas(pr) {
		var areas = (pr && pr.focus_areas) || [];
		return areas.map(function (area) {
			var impact = area.likely_impact || area.challenge_bullets || area.impact || [];
			if (!impact.length && area.challenge_body) {
				impact = [area.challenge_body];
			}
			if (!impact.length && area.impact_items) {
				impact = area.impact_items;
			}
			return {
				label: area.label || '',
				pct: clampPct(area.pct),
				summary: area.summary || '',
				likely_impact: impact,
				actions: area.actions || area.improve_items || [],
				challenge_heading: area.challenge_heading || '',
				slug: area.slug || area.focus_slug || '',
			};
		});
	}

	function mapParentStrengths(pr, homeMetrics, prCfg) {
		var bySource = {};
		(homeMetrics || []).forEach(function (m) {
			bySource[m.source || m.slug] = m;
		});
		var templates = [
			{ source: 'school_partnership', title: 'You are building a shared picture with school expectations' },
			{ source: 'home_ai_safety', title: 'You set boundaries for AI use at home' },
			{ source: 'parent_awareness', title: 'You know what AI tools your child uses' },
			{ source: 'homework_oversight', title: 'You check homework beyond the first AI answer' },
		];
		var out = [];
		templates.forEach(function (t) {
			var m = bySource[t.source];
			if (m && (m.value || 0) >= 55) {
				var detailLabel = t.source.replace(/_/g, ' ');
				PARENT_DOMAIN_DEFS.forEach(function (d) {
					if (d.source === t.source) detailLabel = d.label.toLowerCase();
				});
				out.push({
					title: t.title,
					detail: detailLabel + ' ' + m.value + '%.',
				});
			}
		});
		if (out.length) return out.slice(0, 2);

		var advocate = pr && pr.advocate;
		if (advocate && advocate.strengths && advocate.strengths.length) {
			var labels = (prCfg && prCfg.advocate_strength_labels) || {};
			return advocate.strengths.slice(0, 2).map(function (title) {
				var detail = '';
				Object.keys(labels).forEach(function (slug) {
					if (labels[slug] === title && bySource[slug]) {
						detail = labels[slug].toLowerCase() + ' ' + bySource[slug].value + '%.';
					}
				});
				return { title: title, detail: detail };
			});
		}

		return (homeMetrics || [])
			.slice()
			.sort(function (a, b) { return (b.value || 0) - (a.value || 0); })
			.slice(0, 2)
			.map(function (m) {
				return {
					title: 'You show strength in ' + String(m.label || '').toLowerCase(),
					detail: String(m.label || '').toLowerCase() + ' ' + m.value + '%.',
				};
			});
	}

	function mapParentPeer(pr) {
		var pb = (pr && pr.peer_benchmark) || {};
		return {
			comparisonLabel: 'How you compare to other parents',
			averageScore: clampPct(pb.average_score),
			topQuartile: clampPct(pb.top_quartile),
			yourScore: clampPct(pb.your_score),
			sampleSize: pb.sample_size || 0,
			isEstimated: !!pb.is_estimated,
		};
	}

	function parentPriority(pr, domains) {
		var weakest = weakestDomain(domains);
		if (weakest && weakest.key && PARENT_GUIDANCE_PRIORITIES[weakest.key]) {
			return PARENT_GUIDANCE_PRIORITIES[weakest.key];
		}
		var focus = mapParentFocusAreas(pr);
		var weakestFocus = weakestFocusArea(focus);
		if (weakestFocus && weakestFocus.actions && weakestFocus.actions.length) {
			return weakestFocus.actions[0];
		}
		if (weakestFocus && weakestFocus.summary) return weakestFocus.summary;
		return '';
	}

	function homeworkOversightNote(metric) {
		if (!metric) return '';
		var pct = clampPct(metric.value);
		if (pct >= 70) return 'Strong explain-in-own-words habits at home.';
		if (pct >= 50) return 'Explain-in-own-words habit is uneven.';
		return 'Build a simple check-in after homework — ask them to explain their thinking.';
	}

	function schoolPartnershipNote(metric) {
		if (!metric) return '';
		var pct = clampPct(metric.value);
		if (pct >= 70) return 'Good base for shared expectations with school.';
		if (pct >= 50) return 'Some alignment with school — worth confirming expectations.';
		return 'Ask your school how they expect pupils to use AI at home.';
	}

	function parentJourney(pr) {
		return ['Aware at home', 'Homework routine', 'Safety conversations', 'School partnership'];
	}

	function leaderResultConfig(cfg) {
		return (cfg && cfg.leader_result) || {};
	}

	function supportResultConfig(cfg) {
		return (cfg && cfg.support_result) || {};
	}

	var LEADER_DOMAIN_DEFS = [
		{ key: 'governance', label: 'Governance', prompt: 'Assign ownership' },
		{ key: 'safe_adoption', label: 'Safe adoption', prompt: 'Assess new tools' },
		{ key: 'safeguarding', label: 'Safeguarding', prompt: 'Refresh procedures' },
		{ key: 'bias_equality', label: 'Bias & equality', prompt: 'Check unfair outputs' },
		{ key: 'privacy', label: 'Privacy', prompt: 'Check tool approvals' },
		{ key: 'assessment_integrity', label: 'Assessment controls', prompt: 'Evidence JCQ alignment' },
		{ key: 'human_oversight', label: 'Staff CPD', prompt: 'Target training' },
		{ key: 'ai_literacy', label: 'Pupil AI literacy', prompt: 'Build curriculum' },
	];

	var SUPPORT_DOMAIN_DEFS = [
		{ key: 'ai_literacy', label: 'AI literacy', prompt: 'Spot limits' },
		{ key: 'human_oversight', label: 'Human oversight', prompt: 'Review before sending' },
		{ key: 'ai_dependency', label: 'Operational dependency', prompt: 'Keep manual route' },
		{ key: 'privacy', label: 'Data protection', prompt: 'Know what not to enter' },
		{ key: 'safe_adoption', label: 'Safe adoption', prompt: 'Check approval' },
		{ key: 'safeguarding', label: 'Safeguarding awareness', prompt: 'Know reporting route' },
	];

	var SUPPORT_GUIDANCE_PRIORITIES = {
		privacy: 'Post approved-tool guidance next to the tasks where sensitive data is most often handled.',
		human_oversight: 'Review AI-drafted communications before sending to parents or staff.',
		safe_adoption: 'Check approval routes before adopting new AI tools in daily workflows.',
		ai_literacy: 'Treat AI outputs as drafts and test one tool on an edge case this week.',
		ai_dependency: 'Attempt one email or letter yourself before using AI to draft it.',
		safeguarding: 'Check the reporting route for AI-related safeguarding or data protection concerns.',
	};

	var LEADER_GUIDANCE_PRIORITIES = {
		governance: 'Assign owners, evidence, and review dates for governance before your next SLT meeting.',
		safeguarding: 'Refresh safeguarding procedures for AI-generated harms and document staff reporting routes.',
		privacy: 'Audit approved AI tools against data protection rules and publish clear staff guidance.',
		assessment_integrity: 'Assign an assessment integrity owner and evidence JCQ alignment in one faculty.',
		ai_literacy: 'Map where pupils learn AI limits, bias, privacy, and verification across tutor time or curriculum.',
		human_oversight: 'Target staff CPD so colleagues understand AI limits before expanding classroom use.',
		safe_adoption: 'Publish an approved-tool list and check one department against it this term.',
		bias_equality: 'Add bias and equality checks to safeguarding, tool approval, and classroom AI guidance.',
	};

	function leaderHeadline(lr) {
		var ui = lr && lr.ui && lr.ui.hero ? lr.ui.hero : null;
		if (ui && ui.consequence) return ui.consequence;
		var es = lr && lr.executive_summary;
		if (es && es.intro) return es.intro;
		return '';
	}

	function mapLeaderDomains(results) {
		var domains = results.domain_scores || {};
		var out = [];
		LEADER_DOMAIN_DEFS.forEach(function (def) {
			var value = domainReadinessScore(domains, def.key, results);
			if (value === null) return;
			out.push({
				key: def.key,
				label: def.label,
				value: value,
				tone: domainTone(value),
				prompt: def.prompt,
			});
		});
		return out;
	}

	function mapLeaderFocusAreas(lr, results) {
		var areas = (lr && lr.focus_areas) || [];
		var out = areas.map(function (area) {
			return {
				label: area.label || '',
				pct: clampPct(area.pct),
				summary: area.summary || '',
				likely_impact: area.likely_impact || [],
				actions: area.actions || [],
				challenge_heading: area.challenge_heading || '',
				slug: area.slug || '',
			};
		});
		var biasHealth = lr && lr.bias_health ? lr.bias_health : null;
		var biasScore = biasHealth && biasHealth.score != null
			? clampPct(biasHealth.score)
			: (results && results.bias_readiness != null ? clampPct(results.bias_readiness) : null);
		var hasBiasFocus = out.some(function (area) {
			return area.slug === 'bias_equality' || /bias|equality/i.test(area.label || '');
		});
		if (!hasBiasFocus && biasScore !== null && biasScore < 70) {
			out.push({
				label: 'Bias & equality',
				pct: biasScore,
				summary: (biasHealth && biasHealth.callout) || 'AI tools can produce unfair or discriminatory outputs. Leadership needs a visible review process across safeguarding, tool approval, and classroom use.',
				likely_impact: [
					'Staff may miss unfair or stereotyped AI outputs affecting pupils',
					'Safeguarding and equality duties may not be reflected in AI procedures',
				],
				actions: [
					'Add a bias and equality check to approved-tool review',
					'Brief staff on how to spot unfair AI outputs before classroom use',
				],
				slug: 'bias_equality',
			});
		}
		out.sort(function (a, b) {
			return (a.pct || 0) - (b.pct || 0);
		});
		return out.slice(0, 4);
	}

	function mapLeaderStrengths(lr, domains) {
		var es = lr && lr.executive_summary;
		var strengthLabels = (es && es.strengths) || [];
		if (strengthLabels.length) {
			return strengthLabels.slice(0, 2).map(function (title) {
				var detail = '';
				(domains || []).forEach(function (d) {
					if (title.toLowerCase().indexOf(d.label.toLowerCase()) >= 0) {
						detail = d.label.toLowerCase() + ' ' + d.value + '%.';
					}
				});
				return { title: title, detail: detail };
			});
		}
		return (domains || [])
			.slice()
			.sort(function (a, b) { return b.value - a.value; })
			.slice(0, 2)
			.map(function (d) {
				return {
					title: d.label + ' is a relative strength',
					detail: d.label.toLowerCase() + ' ' + d.value + '%.',
				};
			});
	}

	function mapLeaderPeer(lr) {
		var pb = (lr && lr.peer_benchmark) || {};
		return {
			comparisonLabel: 'How you compare to similar schools',
			averageScore: clampPct(pb.average_score),
			topQuartile: clampPct(pb.top_quartile),
			yourScore: clampPct(pb.your_score),
			sampleSize: pb.sample_size || 0,
			isEstimated: !!pb.is_estimated,
		};
	}

	function leaderPriority(lr, domains, results) {
		var weakest = weakestDomain(domains);
		if (weakest && weakest.key && LEADER_GUIDANCE_PRIORITIES[weakest.key]) {
			return LEADER_GUIDANCE_PRIORITIES[weakest.key];
		}
		var focus = mapLeaderFocusAreas(lr, results);
		var weakestFocus = weakestFocusArea(focus);
		if (weakestFocus && weakestFocus.actions && weakestFocus.actions.length) {
			return weakestFocus.actions[0];
		}
		if (weakestFocus && weakestFocus.summary) return weakestFocus.summary;
		var es = lr && lr.executive_summary;
		if (es && es.priority_action) return es.priority_action;
		return 'Assign owners, evidence, and review dates to the two weakest domains before the next SLT meeting.';
	}

	function governanceMaturityNote(score, lr) {
		var maturity = lr && lr.maturity;
		if (maturity && maturity.description) return maturity.description;
		var pct = clampPct(score);
		if (pct >= 76) return 'Embedded governance and oversight across teams.';
		if (pct >= 51) return 'Policy exists, practice varies.';
		return 'Formal policy, staff training and monitoring need strengthening.';
	}

	function safeguardingReadinessNote(score) {
		var pct = clampPct(score);
		if (pct >= 76) return 'Procedures updated for AI-related harms.';
		if (pct >= 51) return 'Procedures partly updated.';
		return 'Safeguarding routes for AI harms need refreshing.';
	}

	function leaderJourney(lrCfg) {
		var levels = (lrCfg && lrCfg.maturity_levels) || [];
		if (levels.length) {
			return levels.map(function (level) { return level.label || ''; }).filter(Boolean);
		}
		return ['At risk', 'Action required', 'Stable', 'Responsible'];
	}

	function supportHeadline(sr) {
		if (sr && sr.performance_headline) return sr.performance_headline;
		var ui = sr && sr.ui && sr.ui.hero ? sr.ui.hero : null;
		if (ui && ui.consequence) return ui.consequence;
		return '';
	}

	function mapSupportDomains(sr, results) {
		var rows = (sr && sr.domain_rows) || [];
		if (rows.length) {
			var mapped = rows.map(function (row) {
				var slug = row.slug || '';
				var prompt = '';
				SUPPORT_DOMAIN_DEFS.forEach(function (def) {
					if (def.key === slug) prompt = def.prompt;
				});
				return {
					key: slug,
					label: row.label || slug,
					value: clampPct(row.pct),
					tone: domainTone(row.pct),
					prompt: prompt,
				};
			});
			appendSupportSafeguarding(mapped, results);
			return mapped;
		}
		var domains = results.domain_scores || {};
		var out = [];
		SUPPORT_DOMAIN_DEFS.forEach(function (def) {
			var value = domainReadinessScore(domains, def.key, results);
			if (value === null && def.key === 'ai_dependency' && results.dependency_index != null) {
				value = clampPct(100 - results.dependency_index);
			}
			if (value === null && def.key === 'safeguarding') {
				value = supportDisplayReadiness(results.support_display_domains || {}, 'safeguarding');
			}
			if (value === null) return;
			out.push({
				key: def.key,
				label: def.label,
				value: value,
				tone: domainTone(value),
				prompt: def.prompt,
			});
		});
		return out;
	}

	function supportDisplayReadiness(display, slug) {
		var row = display && display[slug];
		if (!row || !row.questions_answered) return null;
		return clampPct(row.readiness_percentage);
	}

	function appendSupportSafeguarding(domains, results) {
		var exists = domains.some(function (domain) {
			return domain.key === 'safeguarding' || /safeguarding/i.test(domain.label || '');
		});
		if (exists) return;
		var value = supportDisplayReadiness((results && results.support_display_domains) || {}, 'safeguarding');
		if (value === null) return;
		domains.push({
			key: 'safeguarding',
			label: 'Safeguarding awareness',
			value: value,
			tone: domainTone(value),
			prompt: 'Know reporting route',
		});
	}

	function mapSupportFocusAreas(sr) {
		var areas = (sr && sr.focus_areas) || [];
		return areas.map(function (area) {
			var impact = area.likely_impact || area.challenge_bullets || [];
			return {
				label: area.label || '',
				pct: clampPct(area.pct),
				summary: area.summary || '',
				likely_impact: impact,
				actions: area.actions || [],
				challenge_heading: area.challenge_heading || '',
				slug: area.slug || area.focus_slug || '',
			};
		});
	}

	function mapSupportStrengths(sr) {
		var items = (sr && sr.strength_items) || [];
		if (items.length) {
			return items.slice(0, 2).map(function (item) {
				return {
					title: item.title || '',
					detail: item.detail || '',
				};
			});
		}
		return (sr && sr.strengths || []).slice(0, 2).map(function (title) {
			return { title: title, detail: '' };
		});
	}

	function mapSupportPeer(results, cfg) {
		var rb = (cfg && cfg.role_benchmarks && cfg.role_benchmarks.support_staff) || {};
		var score = clampPct(results.alignment_score || 0);
		return {
			comparisonLabel: 'How you compare to other support staff',
			averageScore: clampPct(rb.average != null ? rb.average : (rb.average_score != null ? rb.average_score : 58)),
			topQuartile: clampPct(rb.top_quartile != null ? rb.top_quartile : 70),
			yourScore: score,
			sampleSize: rb.sample_size || 0,
			isEstimated: true,
		};
	}

	function supportPriority(sr, domains) {
		var weakest = weakestDomain(domains);
		if (weakest && weakest.key && SUPPORT_GUIDANCE_PRIORITIES[weakest.key]) {
			return SUPPORT_GUIDANCE_PRIORITIES[weakest.key];
		}
		var focus = mapSupportFocusAreas(sr);
		var weakestFocus = weakestFocusArea(focus);
		if (weakestFocus && weakestFocus.actions && weakestFocus.actions.length) {
			return weakestFocus.actions[0];
		}
		if (weakestFocus && weakestFocus.summary) return weakestFocus.summary;
		return 'Put approved-tool guidance and reporting routes next to the tasks where AI is most often used.';
	}

	function operationalDependencyNote(dep, sr) {
		var signals = sr && sr.metric_signals ? sr.metric_signals : {};
		var depCard = signals.dependency || signals.operational_dependency || {};
		if (depCard.consequence) return depCard.consequence;
		var pct = clampPct(dep);
		if (pct >= 60) return 'AI may be handling too much of your daily communications.';
		if (pct >= 35) return 'AI used mainly for communications.';
		return 'Healthy independence before reaching for AI.';
	}

	function dataProtectionNote(score, sr) {
		var signals = sr && sr.metric_signals ? sr.metric_signals : {};
		var card = signals.data_protection || signals.privacy || {};
		if (card.consequence) return card.consequence;
		var pct = clampPct(score);
		if (pct >= 76) return 'Strong rules for what must not enter public AI tools.';
		if (pct >= 51) return 'Rules known, approval routes less clear.';
		return 'Clarify what personal or pupil data must never go into AI tools.';
	}

	function supportJourney() {
		return ['AI aware', 'Approved tools', 'Data confident', 'Safe workflow'];
	}

	function publicResultConfig(cfg) {
		return (cfg && cfg.public_result) || {};
	}

	var PUBLIC_DOMAIN_DEFS = [
		{ key: 'personal_ai_use', label: 'Personal AI use', prompt: 'Useful habits' },
		{ key: 'verification', label: 'Verification', prompt: 'Check sources' },
		{ key: 'data_privacy', label: 'Data & privacy', prompt: 'Remove details' },
		{ key: 'workplace_ai', label: 'Workplace AI', prompt: 'Disclose use' },
		{ key: 'emotional_social', label: 'Emotional & social', prompt: 'Keep perspective' },
	];

	var PUBLIC_GUIDANCE_PRIORITIES = {
		data_privacy: 'Remove names, addresses, health details, and workplace data before using public AI tools.',
		workplace_ai: 'Disclose AI use when your employer expects transparency on generated content.',
		personal_ai_use: 'Review how often you reach for AI before trying the task yourself.',
		verification: 'Check one AI answer against a trusted source before acting on it.',
		emotional_social: 'Keep perspective when using AI for emotional or social advice.',
	};

	var PUBLIC_FOCUS_FALLBACKS = {
		personal_ai_use: {
			summary: 'AI is useful, but it should not become the first step for every task.',
			likely_impact: [
				'AI may become the default before you have tried the task yourself',
				'You may trust fluent answers without checking whether they fit the situation',
			],
			actions: [
				'Try the task yourself first, then use AI to improve or check it',
				'Pause before acting on AI advice in high-stakes situations',
			],
		},
		verification: {
			summary: 'You may be trusting AI outputs too readily. Build a simple checking habit before acting on important answers.',
			likely_impact: [
				'Confident but incorrect AI answers may influence decisions',
				'Deepfake, scam or biased content may be harder to spot',
			],
			actions: [
				'Check important AI answers against a trusted source',
				'Look for original sources before sharing AI-generated claims',
			],
		},
		data_privacy: {
			summary: 'Some sensitive information may be reaching AI tools. Tighten what you share.',
			likely_impact: [
				'Personal, work or family details may be stored by third-party tools',
				'Information about other people may be shared without their consent',
			],
			actions: [
				'Remove names, addresses, health details and workplace data from prompts',
				'Check privacy settings in the AI tools you use most',
			],
		},
		workplace_ai: {
			summary: 'Workplace AI use needs clearer boundaries around data, policy and disclosure.',
			likely_impact: [
				'Confidential work or client data may enter public AI tools',
				'AI-generated work may be shared without the transparency your employer expects',
			],
			actions: [
				'Check whether your employer has an AI policy',
				'Never paste confidential client or company data into public AI tools',
			],
		},
		emotional_social: {
			summary: 'AI can be helpful, but it should not replace human perspective for personal decisions.',
			likely_impact: [
				'Personal choices may be shaped by AI without independent advice',
				'AI summaries may narrow how you understand news or relationships',
			],
			actions: [
				'Seek a human perspective alongside AI for important personal decisions',
				'Check AI summaries against original sources for current events',
			],
		},
		deepfake_scam_awareness: {
			summary: 'AI is now used to create convincing fake voices, videos and scam messages. Awareness needs to translate into a response plan.',
			likely_impact: [
				'Voice cloning scams may impersonate family members or colleagues',
				'Urgent fake messages may pressure you to respond before checking',
			],
			actions: [
				'Pause and verify unexpected urgent requests independently',
				'Agree a safe word or verification route with close family members',
			],
		},
	};

	function publicHeadline(pr) {
		var ui = pr && pr.ui && pr.ui.hero ? pr.ui.hero : null;
		if (ui && ui.consequence) return ui.consequence;
		return '';
	}

	function publicDisplayValue(display, slug) {
		var row = display[slug];
		if (!row || !row.questions_answered) return null;
		return clampPct(row.readiness_percentage);
	}

	function publicSummaryMetric(summaryMetrics, slug) {
		if (!summaryMetrics) return null;
		for (var i = 0; i < summaryMetrics.length; i++) {
			var row = summaryMetrics[i];
			if (row.slug === slug || row.source === slug) return row;
		}
		return null;
	}

	function mapPublicDomains(pr, results) {
		var rows = (pr && pr.domain_rows) || [];
		if (rows.length) {
			return rows.map(function (row) {
				var slug = row.slug || '';
				var prompt = '';
				var label = row.label || slug;
				PUBLIC_DOMAIN_DEFS.forEach(function (def) {
					if (def.key === slug) {
						prompt = def.prompt;
						label = def.label;
					}
				});
				return {
					key: slug,
					label: label,
					value: clampPct(row.pct),
					tone: domainTone(row.pct),
					prompt: prompt,
				};
			});
		}
		var display = (results && results.public_display_domains) || {};
		var out = [];
		PUBLIC_DOMAIN_DEFS.forEach(function (def) {
			var value = publicDisplayValue(display, def.key);
			if (value === null) return;
			out.push({
				key: def.key,
				label: def.label,
				value: value,
				tone: domainTone(value),
				prompt: def.prompt,
			});
		});
		return out;
	}

	function mapPublicFocusAreas(pr) {
		var areas = (pr && pr.focus_areas) || [];
		return areas.map(function (area) {
			var impact = area.likely_impact || area.challenge_bullets || [];
			if (!impact.length && area.challenge_body) {
				impact = [area.challenge_body];
			}
			var slug = area.focus_slug || area.slug || '';
			var fallback = PUBLIC_FOCUS_FALLBACKS[slug] || PUBLIC_FOCUS_FALLBACKS[area.slug] || PUBLIC_FOCUS_FALLBACKS[area.focus_slug] || {};
			return {
				label: area.label || '',
				pct: clampPct(area.pct),
				summary: area.summary || fallback.summary || '',
				likely_impact: impact.length ? impact : (fallback.likely_impact || []),
				actions: (area.actions && area.actions.length) ? area.actions : (fallback.actions || []),
				challenge_heading: area.challenge_heading || '',
				slug: slug,
			};
		});
	}

	function mapPublicStrengths(pr) {
		var items = (pr && pr.strengths) || [];
		if (items.length && items[0].title) {
			return items.slice(0, 2).map(function (item) {
				return {
					title: item.title || '',
					detail: item.detail || '',
				};
			});
		}
		return items.slice(0, 2).map(function (title) {
			return { title: title, detail: '' };
		});
	}

	function mapPublicPeer(results, cfg) {
		var rb = (cfg && cfg.role_benchmarks && cfg.role_benchmarks.public) || {};
		var score = clampPct(results.alignment_score || 0);
		return {
			comparisonLabel: 'How you compare nationally',
			averageScore: clampPct(rb.average != null ? rb.average : (rb.average_score != null ? rb.average_score : 60)),
			topQuartile: clampPct(rb.top_quartile != null ? rb.top_quartile : 75),
			yourScore: score,
			sampleSize: rb.sample_size || 0,
			isEstimated: true,
		};
	}

	function publicPriority(pr, domains) {
		var weakest = weakestDomain(domains);
		if (weakest && weakest.key && PUBLIC_GUIDANCE_PRIORITIES[weakest.key]) {
			return PUBLIC_GUIDANCE_PRIORITIES[weakest.key];
		}
		var focus = mapPublicFocusAreas(pr);
		var weakestFocus = weakestFocusArea(focus);
		if (weakestFocus && weakestFocus.actions && weakestFocus.actions.length) {
			return weakestFocus.actions[0];
		}
		if (weakestFocus && weakestFocus.summary) return weakestFocus.summary;
		return 'Remove identifiable personal and workplace data before your next AI prompt.';
	}

	function publicVerificationNote(score, pr) {
		var metric = publicSummaryMetric(pr && pr.summary_metrics, 'verification_habit');
		if (metric && metric.badge && metric.badge.note) return metric.badge.note;
		var pct = clampPct(score);
		if (pct >= 76) return 'Strong source-checking habits.';
		if (pct >= 51) return 'Checks happen when stakes feel high.';
		return 'Practice checking AI answers even for low-stakes tasks.';
	}

	function publicDataPrivacyNote(score, pr) {
		var metric = publicSummaryMetric(pr && pr.summary_metrics, 'data_risk_exposure');
		if (metric && metric.badge && metric.badge.note) return metric.badge.note;
		var pct = clampPct(score);
		if (pct >= 76) return 'Strong rules for what must not enter public AI tools.';
		if (pct >= 51) return 'Occasional personal data exposure.';
		return 'Clarify what personal or workplace data must never go into AI tools.';
	}

	function publicJourney() {
		return ['Aware user', 'Privacy reset', 'Verification habit', 'Confident practice'];
	}

	/**
	 * Build the parent dashboard view model from a scored results payload.
	 */
	DM.buildParent = function (results, cfg) {
		if (!results || !results.parent_results) {
			return null;
		}

		cfg = cfg || config();
		var pr = results.parent_results;
		var prCfg = parentResultConfig(cfg);
		var score = clampPct(results.alignment_score || 0);
		var risk = clampPct(results.overall_risk_percentage || 0);
		var homeMetrics = pr.home_metrics || [];
		var domains = mapParentDomains(homeMetrics, results);
		var homework = parentMetricBySource(homeMetrics, 'homework_oversight');
		var partnership = parentMetricBySource(homeMetrics, 'school_partnership');
		var homeworkValue = homework ? clampPct(homework.value) : parentDomainValue('homework_oversight', homeMetrics, results.parent_display_domains);
		var partnershipValue = partnership ? clampPct(partnership.value) : parentDomainValue('school_partnership', homeMetrics, results.parent_display_domains);
		var nextSteps = pr.next_steps || {};
		var focusAreas = mapParentFocusAreas(pr);
		var weakest = weakestDomain(domains);
		var weakestFocus = weakestFocusArea(focusAreas);
		var dashboard = prCfg.dashboard || {};
		var dashboardKey = (weakestFocus && weakestFocus.slug) || (weakest && weakest.key) || '';
		var tier = pr.performance_tier || readinessBand(score);

		return {
			role: 'parent',
			label: 'Parent',
			audience: 'Home support',
			accent: '#9333ea',
			soft: '#f3e8ff',
			ink: '#581c87',
			scene: roleDashboardCopy(dashboard, 'scene', dashboardKey, 'Kitchen table conversation'),
			headline: roleDashboardCopy(dashboard, 'headline', tier, parentHeadline(pr)),
			scoreLabel: prCfg.hero_metric_label || 'Awareness',
			score: score,
			risk: risk,
			motif: roleDashboardCopy(dashboard, 'motif', dashboardKey, (pr.ui && pr.ui.hero && pr.ui.hero.signal) || 'Ask them to explain it back'),
			metricA: {
				label: homework ? homework.label : 'Homework Oversight',
				value: homeworkValue !== null ? homeworkValue + '%' : '—',
				note: homeworkValue !== null ? homeworkOversightNote(homework || { value: homeworkValue }) : '',
			},
			metricB: {
				label: partnership ? partnership.label : 'School Partnership',
				value: partnershipValue !== null ? partnershipValue + '%' : '—',
				note: partnershipValue !== null ? schoolPartnershipNote(partnership || { value: partnershipValue }) : '',
			},
			priority: parentPriority(pr, domains),
			nextAction: roleDashboardCopy(dashboard, 'next_action', dashboardKey, nextSteps.cta_text || 'Open parent conversation guide'),
			journey: parentJourney(pr),
			domains: domains,
			focusAreas: focusAreas,
			strengths: mapParentStrengths(pr, homeMetrics, prCfg),
			resources: mapResources({ resource_links: pr.resource_links || nextSteps.resource_links || [] }),
			peer: mapParentPeer(pr),
			certificate: mapCertificate(results),
			conversationStarters: pr.conversation_starters || [],
		};
	};

	/**
	 * Build the student dashboard view model from a scored results payload.
	 *
	 * @param {object} results Full AJAX results object.
	 * @param {object} [cfg]    Optional config override (defaults to airbBenchmark.config).
	 * @returns {object|null}
	 */
	DM.buildStudent = function (results, cfg) {
		if (!results || !results.student_results) {
			return null;
		}

		cfg = cfg || config();
		var sr = results.student_results;
		var srCfg = studentResultConfig(cfg);
		var score = clampPct(results.alignment_score || 0);
		var risk = clampPct(results.overall_risk_percentage || 0);
		var metrics = sr.learning_metrics || [];
		var domains = mapStudentDomains(metrics, results);
		var independent = metricBySlug(metrics, 'independent_thinking');
		var verification = metricBySlug(metrics, 'verification_skills');
		var focusAreas = mapStudentFocusAreas(sr, results);
		var weakest = weakestDomain(domains);
		var weakestFocus = weakestFocusArea(focusAreas);
		var dashboard = srCfg.dashboard || {};
		var dashboardKey = (weakestFocus && weakestFocus.slug) || (weakest && weakest.key) || '';
		var tier = sr.performance_tier || readinessBand(score);

		return {
			role: 'student',
			label: 'Student',
			audience: 'Learning habits',
			accent: '#0f766e',
			soft: '#ccfbf1',
			ink: '#134e4a',
			scene: roleDashboardCopy(dashboard, 'scene', dashboardKey, 'Study skills passport'),
			headline: roleDashboardCopy(dashboard, 'headline', tier, studentHeadline(sr)),
			scoreLabel: srCfg.hero_metric_label || 'AI skills',
			score: score,
			risk: risk,
			motif: roleDashboardCopy(dashboard, 'motif', dashboardKey, (sr.ui && sr.ui.hero && sr.ui.hero.signal) || 'Think first, prompt second'),
			metricA: {
				label: independent ? independent.label : 'Independent Thinking',
				value: independent ? independent.value + '%' : '—',
				note: independentNote(independent, srCfg),
			},
			metricB: {
				label: verification ? verification.label : 'Verification Skills',
				value: verification ? verification.value + '%' : '—',
				note: verificationNote(verification),
			},
			priority: studentPriority(sr, domains, results),
			nextAction: roleDashboardCopy(dashboard, 'next_action', dashboardKey, (sr.next_steps && sr.next_steps.hero && sr.next_steps.hero.cta_text) || 'Start Think First, Prompt Second'),
			journey: studentJourney(sr),
			domains: domains,
			focusAreas: focusAreas,
			strengths: mapStudentStrengths(sr),
			resources: mapResources(sr.next_steps),
			peer: mapStudentPeer(sr),
			certificate: mapCertificate(results),
			schoolProgress: sr.school_progress || null,
			performanceTier: sr.performance_tier || '',
		};
	};

	/**
	 * Build the school leader dashboard view model from a scored results payload.
	 */
	DM.buildLeader = function (results, cfg) {
		if (!results || !results.leader_results) {
			return null;
		}

		cfg = cfg || config();
		var lr = results.leader_results;
		var lrCfg = leaderResultConfig(cfg);
		var metricLabels = lrCfg.metric_labels || {};
		var score = clampPct(results.alignment_score || 0);
		var risk = clampPct(results.overall_risk_percentage || 0);
		var domains = mapLeaderDomains(results);
		var govScore = results.governance_maturity != null
			? clampPct(results.governance_maturity)
			: domainReadinessScore(results.domain_scores || {}, 'governance', results);
		var safeScore = results.safeguarding_readiness != null
			? clampPct(results.safeguarding_readiness)
			: domainReadinessScore(results.domain_scores || {}, 'safeguarding', results);
		var nextSteps = lr.next_steps || {};
		var focusAreas = mapLeaderFocusAreas(lr, results);
		var weakest = weakestDomain(domains);
		var weakestFocus = weakestFocusArea(focusAreas);
		var dashboard = lrCfg.dashboard || {};
		var dashboardKey = (weakestFocus && weakestFocus.slug) || (weakest && weakest.key) || '';
		var tier = lr.performance_tier || readinessBand(score);

		return {
			role: 'leader',
			label: '',
			audience: 'Governance view',
			accent: '#475569',
			soft: '#e2e8f0',
			ink: '#0f172a',
			scene: roleDashboardCopy(dashboard, 'scene', dashboardKey, 'Governor evidence pack'),
			headline: roleDashboardCopy(dashboard, 'headline', tier, leaderHeadline(lr)),
			scoreLabel: 'DfE alignment',
			score: score,
			risk: risk,
			motif: roleDashboardCopy(dashboard, 'motif', dashboardKey, (lr.ui && lr.ui.hero && lr.ui.hero.signal) || 'Turn scores into governance evidence'),
			metricA: {
				label: metricLabels.governance || 'Governance Maturity',
				value: govScore == null ? '—' : govScore + '%',
				note: governanceMaturityNote(govScore, lr),
			},
			metricB: {
				label: metricLabels.safeguarding || 'Safeguarding Readiness',
				value: safeScore == null ? '—' : safeScore + '%',
				note: safeguardingReadinessNote(safeScore),
			},
			priority: leaderPriority(lr, domains, results),
			nextAction: roleDashboardCopy(dashboard, 'next_action', dashboardKey, (nextSteps.hero && nextSteps.hero.cta_text) || 'Open policy generator'),
			journey: leaderJourney(lrCfg),
			domains: domains,
			focusAreas: focusAreas,
			strengths: mapLeaderStrengths(lr, domains),
			resources: mapResources(nextSteps),
			peer: mapLeaderPeer(lr),
			certificate: mapCertificate(results),
			followUp: mapFollowUp(results, { next_steps: nextSteps }),
		};
	};

	/**
	 * Build the teacher dashboard view model from a scored results payload.
	 *
	 * @param {object} results Full AJAX results object.
	 * @param {object} [cfg]    Optional config override (defaults to airbBenchmark.config).
	 * @returns {object|null}
	 */
	DM.buildTeacher = function (results, cfg) {
		if (!results || !results.teacher_results) {
			return null;
		}

		cfg = cfg || config();
		var tr = results.teacher_results;
		var trCfg = teacherResultConfig(cfg);
		var score = clampPct(results.alignment_score || 0);
		var risk = clampPct(results.overall_risk_percentage || 0);
		var dep = results.dependency_index != null ? clampPct(results.dependency_index) : null;
		var oversight = oversightPct(results);
		var domains = mapDomains(results, cfg);
		var focusAreas = mapFocusAreas(tr, results);
		var weakest = weakestDomain(domains);
		var weakestFocus = weakestFocusArea(focusAreas);
		var dashboard = trCfg.dashboard || {};
		var dashboardKey = (weakestFocus && weakestFocus.slug) || (weakest && weakest.key) || '';
		var tier = tr.performance_tier || readinessBand(score);

		return {
			role: 'teacher',
			label: 'Teacher',
			audience: 'Classroom practice',
			accent: '#2563eb',
			soft: '#dbeafe',
			ink: '#172554',
			scene: roleDashboardCopy(dashboard, 'scene', dashboardKey, 'Feedback:'),
			headline: roleDashboardCopy(dashboard, 'headline', tier, headline(results, tr)),
			scoreLabel: trCfg.metric_labels && trCfg.metric_labels.readiness
				? trCfg.metric_labels.readiness
				: 'Readiness',
			score: score,
			risk: risk,
			motif: roleDashboardCopy(dashboard, 'motif', dashboardKey, (tr.ui && tr.ui.hero && tr.ui.hero.signal) || 'Verify before pupils see it'),
			metricA: {
				label: (trCfg.metric_labels && trCfg.metric_labels.dependency) || 'AI Dependency Index',
				value: dep == null ? '—' : dep + '%',
				note: dependencyNote(results, trCfg),
			},
			metricB: {
				label: 'Human Oversight Ratio',
				value: oversight == null ? '—' : oversight + '%',
				note: oversightNote(results, tr),
			},
			priority: priority(results, tr, domains),
			nextAction: roleDashboardCopy(dashboard, 'next_action', dashboardKey, (tr.next_steps && tr.next_steps.hero && tr.next_steps.hero.cta_text) || 'Request support'),
			journey: ['Audit complete', 'Verification ready', 'Responsible practitioner', 'AI champion'],
			domains: domains,
			focusAreas: focusAreas,
			strengths: mapStrengths(tr),
			resources: mapResources(tr.next_steps),
			peer: mapPeer(tr),
			certificate: mapCertificate(results),
			followUp: mapFollowUp(results, tr),
			schoolProgress: tr.school_progress || null,
			biasHealth: tr.bias_health || null,
			performanceTier: tr.performance_tier || '',
		};
	};

	DM.domainTone = domainTone;
	DM.teacherDomainOrder = function () {
		return TEACHER_DOMAIN_ORDER.slice();
	};

	/**
	 * Build the support staff dashboard view model from a scored results payload.
	 */
	DM.buildSupport = function (results, cfg) {
		if (!results || !results.support_results) {
			return null;
		}

		cfg = cfg || config();
		var sr = results.support_results;
		var srCfg = supportResultConfig(cfg);
		var score = clampPct(results.alignment_score || 0);
		var risk = clampPct(results.overall_risk_percentage || 0);
		var dep = sr.operational_dependency_index != null
			? clampPct(sr.operational_dependency_index)
			: (results.dependency_index != null ? clampPct(results.dependency_index) : null);
		var dataProtection = sr.data_protection_readiness != null
			? clampPct(sr.data_protection_readiness)
			: null;
		var domains = mapSupportDomains(sr, results);
		var nextSteps = sr.next_steps || {};
		var focusAreas = mapSupportFocusAreas(sr);
		var weakest = weakestDomain(domains);
		var weakestFocus = weakestFocusArea(focusAreas);
		var dashboard = srCfg.dashboard || {};
		var dashboardKey = (weakestFocus && weakestFocus.slug) || (weakest && weakest.key) || '';
		var tier = sr.performance_tier || readinessBand(score);

		return {
			role: 'support',
			label: 'Support Staff',
			audience: 'Operational practice',
			accent: '#b45309',
			soft: '#fef3c7',
			ink: '#78350f',
			scene: roleDashboardCopy(dashboard, 'scene', dashboardKey, 'Office workflow check'),
			headline: roleDashboardCopy(dashboard, 'headline', tier, supportHeadline(sr)),
			scoreLabel: srCfg.hero_metric_label || 'Readiness',
			score: score,
			risk: risk,
			motif: roleDashboardCopy(dashboard, 'motif', dashboardKey, (sr.ui && sr.ui.hero && sr.ui.hero.signal) || 'Know the route before the risk'),
			metricA: {
				label: 'Operational Dependency',
				value: dep == null ? '—' : dep + '%',
				note: operationalDependencyNote(dep, sr),
			},
			metricB: {
				label: 'Data Protection',
				value: dataProtection == null ? '—' : dataProtection + '%',
				note: dataProtectionNote(dataProtection, sr),
			},
			priority: supportPriority(sr, domains),
			nextAction: roleDashboardCopy(dashboard, 'next_action', dashboardKey, (nextSteps.hero && nextSteps.hero.cta_text) || 'Open data protection checklist'),
			journey: supportJourney(),
			domains: domains,
			focusAreas: focusAreas,
			strengths: mapSupportStrengths(sr),
			resources: mapResources(nextSteps),
			peer: mapSupportPeer(results, cfg),
			certificate: mapCertificate(results),
			followUp: mapFollowUp(results, { next_steps: nextSteps }),
			schoolProgress: sr.school_progress || null,
			performanceTier: sr.performance_tier || '',
		};
	};

	/**
	 * Build the public dashboard view model from a scored results payload.
	 */
	DM.buildPublic = function (results, cfg) {
		if (!results || !results.public_results) {
			return null;
		}

		cfg = cfg || config();
		var pr = results.public_results;
		var prCfg = publicResultConfig(cfg);
		var display = results.public_display_domains || {};
		var score = clampPct(results.alignment_score || 0);
		var risk = clampPct(results.overall_risk_percentage || 0);
		var domains = mapPublicDomains(pr, results);
		var verification = publicDisplayValue(display, 'verification');
		if (verification === null) {
			var verifyMetric = publicSummaryMetric(pr.summary_metrics, 'verification_habit');
			if (verifyMetric) verification = clampPct(verifyMetric.value);
		}
		var dataPrivacy = publicDisplayValue(display, 'data_privacy');
		if (dataPrivacy === null) {
			var privacyMetric = publicSummaryMetric(pr.summary_metrics, 'data_risk_exposure');
			if (privacyMetric && privacyMetric.mode === 'risk') {
				dataPrivacy = clampPct(100 - privacyMetric.value);
			} else if (privacyMetric) {
				dataPrivacy = clampPct(privacyMetric.value);
			}
		}
		var focusAreas = mapPublicFocusAreas(pr);
		var weakest = weakestDomain(domains);
		var weakestFocus = weakestFocusArea(focusAreas);
		var dashboard = prCfg.dashboard || {};
		var dashboardKey = (weakestFocus && weakestFocus.slug) || (weakest && weakest.key) || '';
		var tier = pr.performance_tier || readinessBand(score);

		return {
			role: 'public',
			label: 'Public',
			audience: 'Personal AI use',
			accent: '#dc2626',
			soft: '#fee2e2',
			ink: '#7f1d1d',
			scene: roleDashboardCopy(dashboard, 'scene', dashboardKey, 'Personal AI habits'),
			headline: roleDashboardCopy(dashboard, 'headline', tier, publicHeadline(pr)),
			scoreLabel: prCfg.hero_metric_label || 'AI readiness',
			score: score,
			risk: risk,
			motif: roleDashboardCopy(dashboard, 'motif', dashboardKey, (pr.ui && pr.ui.hero && pr.ui.hero.signal) || 'Use AI without giving too much away'),
			metricA: {
				label: 'Verification',
				value: verification == null ? '—' : verification + '%',
				note: publicVerificationNote(verification, pr),
			},
			metricB: {
				label: 'Data & Privacy',
				value: dataPrivacy == null ? '—' : dataPrivacy + '%',
				note: publicDataPrivacyNote(dataPrivacy, pr),
			},
			priority: publicPriority(pr, domains),
			nextAction: roleDashboardCopy(dashboard, 'next_action', dashboardKey, 'Open personal AI safety checklist'),
			journey: publicJourney(),
			domains: domains,
			focusAreas: focusAreas,
			strengths: mapPublicStrengths(pr),
			resources: mapResources({ resource_links: pr.resource_links || [] }),
			peer: mapPublicPeer(results, cfg),
			certificate: mapCertificate(results),
		};
	};

	/**
	 * @param {object} results
	 * @param {string} [role]
	 * @returns {object|null}
	 */
	DM.build = function (results, role) {
		role = role || (results && results.role) || (AIRB.runtime && AIRB.runtime.state && AIRB.runtime.state.role) || '';
		if (role === 'teacher') {
			return DM.buildTeacher(results);
		}
		if (role === 'student') {
			return DM.buildStudent(results);
		}
		if (role === 'parent') {
			return DM.buildParent(results);
		}
		if (role === 'leader') {
			return DM.buildLeader(results);
		}
		if (role === 'support_staff' || role === 'support') {
			return DM.buildSupport(results);
		}
		if (role === 'public') {
			return DM.buildPublic(results);
		}
		return null;
	};
}());
