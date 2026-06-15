/**
 * airb-roles.js
 *
 * Role-specific result screen assemblers.
 * Each function builds the full results HTML for one role
 * by calling shared builders from AIRB.Results (airb-results.js).
 *
 * Depends on:
 *   airb-core.js    — AIRB.esc, AIRB.state helpers
 *   airb-results.js — AIRB.Results component builders
 *
 * Exposes: AIRB.Roles
 *
 * ----------------------------------------------------------------------------
 * Migration note
 * Pull these functions out of airb-front.js one at a time.
 * Each one replaces its equivalent in the monolith:
 *
 *   teacherFocusAreasHtml()   → AIRB.Roles.teacherFocusAreas()
 *   leaderFocusAreasHtml()    → AIRB.Roles.leaderFocusAreas()
 *   supportFocusAreasHtml()   → AIRB.Roles.supportFocusAreas()
 *   studentFocusAreasHtml()   → AIRB.Roles.studentFocusAreas()
 *   parentFocusAreasHtml()    → AIRB.Roles.parentFocusAreas()
 *   publicFocusAreasHtml()    → AIRB.Roles.publicFocusAreas()
 *
 * After migrating each one, delete the old function from airb-front.js.
 * ----------------------------------------------------------------------------
 */

'use strict';

(function () {
	window.AIRB = window.AIRB || {};
	var R = AIRB.Roles || {};
	AIRB.Roles = R;

	// Shorthand
	var Results = AIRB.Results || {};
	var esc = AIRB.esc || function (s) { return String(s); };

	/** Map wp role slugs to copy_tiers registry keys. */
	function normalizeRole(role) {
		if (role === 'support_staff') {
			return 'support';
		}
		return role;
	}

	/** JSON domain keys → scoring slugs in domain_scores. */
	var SCORE_SLUG = {
		independent_practice: 'ai_dependency',
		privacy_data_protection: 'privacy',
		data_protection_awareness: 'privacy',
		governance_consistency: 'governance',
		bias_awareness: 'bias_equality',
	};

	/** JSON strength keys → copy-tiers strengths map keys. */
	var STRENGTH_SLUG = {
		privacy_data_protection: 'privacy',
	};

	function domainReadinessScore(domains, key, results) {
		if (key === 'bias_awareness' && results && results.bias_readiness != null) {
			return Math.round(results.bias_readiness);
		}
		var scoreKey = SCORE_SLUG[key] || key;
		var d = domains[scoreKey];
		if (d == null) {
			return null;
		}
		if (typeof d === 'number') {
			return Math.round(d);
		}
		if (typeof d === 'object') {
			if (typeof d.readiness_percentage === 'number') {
				return Math.round(d.readiness_percentage);
			}
			if (typeof d.risk_percentage === 'number') {
				return Math.round(100 - d.risk_percentage);
			}
		}
		return null;
	}

	function strengthTierKey(domainKey) {
		return STRENGTH_SLUG[domainKey] || domainKey;
	}

	// -------------------------------------------------------------------------
	// Copy tier resolver
	// Reads from airbBenchmark.config.copy_tiers[role] (JSON passed via
	// wp_localize_script). Falls back to empty object so callers never throw.
	// -------------------------------------------------------------------------

	function tiersFor(role) {
		role = normalizeRole(role);
		var ct = (window.airbBenchmark && airbBenchmark.config && airbBenchmark.config.copy_tiers) || {};
		return ct[role] || {};
	}

    /**
     * Resolve a domain focus card from copy tiers.
     * Returns {label, summary, impact[], actions[], severity, score}
     */
    function resolveDomainFocus(role, domainKey, score) {
        role = normalizeRole(role);
        var tiers   = tiersFor(role);
        var domains = tiers.domains || {};
        var d       = domains[domainKey] || {};
        var card    = d.focus_card || {};

        var severity = score <= 34 ? 'critical' : (score <= 59 ? 'high' : 'moderate');
        var block    = card[severity] || card['high'] || card['moderate'] || {};

        return {
            label    : d.label || domainKey,
            summary  : block.summary || '',
            impact   : block.impact || [],
            actions  : block.actions || [],
            severity : severity,
            score    : score
        };
    }

    /**
     * Resolve oversight copy from copy tiers for a given role + pct.
     */
    function resolveOversight(role, pct) {
        role = normalizeRole(role);
        var tiers    = tiersFor(role);
        var sections = tiers.oversight || {};
        for (var key in sections) {
            var t = sections[key];
            if (pct >= t.min && pct <= t.max) {
                return { signal: t.signal || '', consequence: t.consequence || '', tone: t.tone || 'neutral' };
            }
        }
        return { signal: '', consequence: '', tone: 'neutral' };
    }

    /**
     * Resolve governance maturity copy (leader only).
     */
    function resolveGovernanceMaturity(pct) {
        var tiers    = tiersFor('leader');
        var sections = tiers.governance_maturity || {};
        for (var key in sections) {
            var t = sections[key];
            if (pct >= t.min && pct <= t.max) {
                return { label: t.label || '', signal: t.signal || '', consequence: t.consequence || '', tone: t.tone || 'neutral' };
            }
        }
        return { label: '', signal: '', consequence: '', tone: 'neutral' };
    }

    /**
     * Resolve the most urgent single action from leader copy tiers.
     * Checks triggers in priority order.
     */
    function resolveLeaderUrgentAction(domainScores, overall) {
        var dp = domainReadinessScore(domainScores, 'data_protection_awareness');
        if (dp === null) {
            dp = domainReadinessScore(domainScores, 'privacy');
        }
        dp = dp == null ? 100 : dp;
        var sg = domainReadinessScore(domainScores, 'safeguarding');
        sg = sg == null ? 100 : sg;
        var hu = domainReadinessScore(domainScores, 'human_oversight');
        hu = hu == null ? 100 : hu;
        var as = domainReadinessScore(domainScores, 'assessment_integrity');
        as = as == null ? 100 : as;
        var gv = domainReadinessScore(domainScores, 'governance_consistency');
        if (gv === null) {
            gv = domainReadinessScore(domainScores, 'governance');
        }
        gv = gv == null ? 100 : gv;

        var ua = tiersFor('leader').urgent_action || {};

        if (dp === 0 && sg === 0) return ua.both_critical || null;
        if (dp === 0)             return ua.data_protection_zero || null;
        if (sg === 0)             return ua.safeguarding_zero || null;
        if (gv < 30)              return ua.governance_critical || null;
        if (hu < 33)              return ua.human_oversight_critical || null;
        if (as < 33)              return ua.assessment_critical || null;
        if (overall >= 75)        return ua.all_strong || null;
        return null;
    }

    /**
     * Resolve CTA block copy for a given role + score.
     */
    function resolveCta(role, score) {
        role = normalizeRole(role);
        var tiers  = tiersFor(role);
        var bands  = tiers.readiness_bands || {};
        var ctaMap = tiers.cta || {};

        for (var key in bands) {
            var b = bands[key];
            if (score >= b.min && score <= b.max) {
                return ctaMap[key] || {};
            }
        }
        return {};
    }

    /**
     * Resolve readiness band block for a given role + score.
     */
    function resolveBand(role, score) {
        role = normalizeRole(role);
        var tiers = tiersFor(role);
        var bands = tiers.readiness_bands || {};
        for (var key in bands) {
            var b = bands[key];
            if (score >= b.min && score <= b.max) {
                return b;
            }
        }
        return {};
    }

    // -------------------------------------------------------------------------
    // Shared band labels per role
    // -------------------------------------------------------------------------

    var BAND_LABELS = {
        teacher : ['At risk · Act now', 'Concern · Review needed', 'Established', 'Strong', 'Leading'],
        leader  : ['Critical · Act now', 'Concern · Review needed', 'Established', 'Strong', 'Leading'],
        support : ['At risk · Act now', 'Concern · Review needed', 'Established', 'Strong', 'Leading'],
        student : ['Needs attention', 'Take care', 'Aware', 'Confident', 'Advanced'],
        parent  : ['Your child needs your help', 'Some gaps at home', 'Aware', 'Confident', 'Well prepared'],
        public  : ['At risk', 'Take care', 'Aware', 'Confident', 'Advanced']
    };

    function activeBandIndex(score) {
        if (score <= 39)  return 0;
        if (score <= 59)  return 1;
        if (score <= 74)  return 2;
        if (score <= 89)  return 3;
        return 4;
    }

    // -------------------------------------------------------------------------
    // Teacher focus areas
    // -------------------------------------------------------------------------

    /**
     * Build teacher priority focus areas HTML.
     * Prefers server-computed focus_areas (full copy + guidance); falls back to client domain build.
     *
     * @param {object} r         Full results payload from server
     * @param {object} renderOpts Optional render helpers from airb-front.js
     * @returns {string}  HTML
     */
    R.teacherFocusAreas = function (r, renderOpts) {
        renderOpts = renderOpts || {};
        var tr = r.teacher_results || {};
        var trCfg = renderOpts.teacherResult || (window.airbBenchmark && airbBenchmark.config && airbBenchmark.config.teacher_result) || {};
        var heading = trCfg.focus_section_heading || 'Priority focus areas — what to strengthen';
        var headingShort = trCfg.focus_section_heading_short || 'Priority focus areas';
        var focusAreas = tr.focus_areas && tr.focus_areas.length ? tr.focus_areas : null;

        if (focusAreas && focusAreas.length && Results.teacherFocusAreasHtml) {
            var stackOpts = {
                esc: esc,
                guidanceOpen: true,
                practiceHeading: trCfg.focus_practice_heading_short || 'In practice this means',
            };
            for (var key in renderOpts) {
                if (Object.prototype.hasOwnProperty.call(renderOpts, key)) {
                    stackOpts[key] = renderOpts[key];
                }
            }
            var stack = Results.teacherFocusAreasHtml(focusAreas, tr.bias_health, stackOpts);
            if (!stack) return '';
            var labelHtml = renderOpts.sectionLabelHtml
                ? renderOpts.sectionLabelHtml(heading, headingShort)
                : '<h3 class="airb__benchmark-card-heading">' + esc(heading) + '</h3>';
            return labelHtml + '<div class="airb__leader-focus-stack">' + stack + '</div>';
        }

        var domains  = r.domain_scores || {};
        var html     = '';
        var focusCount = 0;

        var TEACHER_DOMAINS = [
            'independent_practice',
            'privacy_data_protection',
            'human_oversight',
            'safeguarding',
            'assessment_integrity',
            'ai_literacy',
            'bias_awareness'
        ];

        var cardOpts = {
            variant: 'teacher',
            guidanceOpen: true,
            guidanceToggle: renderOpts.guidanceToggle || 'Tips & steps to try',
            guidanceAccordionHtml: renderOpts.focusGuidanceAccordionHtml || null,
        };

        TEACHER_DOMAINS.forEach(function (key) {
            var score = domainReadinessScore(domains, key, r);
            if (score === null || score >= 75 || focusCount >= 3) return;

            if (!Results.focusCardHtml) return;
            var focus = resolveDomainFocus('teacher', key, score);
            html += Results.focusCardHtml(Object.assign({}, cardOpts, {
                title      : focus.label,
                score      : score,
                severity   : focus.severity,
                summary    : focus.summary,
                impact     : focus.impact,
                actions    : focus.actions,
            }));
            focusCount++;
        });

        if (!html) return '';

        var labelHtml = renderOpts.sectionLabelHtml
            ? renderOpts.sectionLabelHtml(heading, headingShort)
            : '<h3 class="airb__benchmark-card-heading">' + esc(heading) + '</h3>';
        return labelHtml + '<div class="airb__leader-focus-stack">' + html + '</div>';
    };

    // -------------------------------------------------------------------------
    // Teacher strengths
    // -------------------------------------------------------------------------

    R.teacherStrengths = function (r, renderOpts) {
        renderOpts = renderOpts || {};
        var tr = r.teacher_results || {};
        var trCfg = renderOpts.teacherResult || (window.airbBenchmark && airbBenchmark.config && airbBenchmark.config.teacher_result) || {};
        var heading = trCfg.strengths_heading || 'What you\'re doing well';
        var strengths = tr.strengths && tr.strengths.length ? tr.strengths : null;
        var rows = [];

        if (!strengths) {
            var domains = r.domain_scores || {};
            var tiers   = tiersFor('teacher');
            var strMap  = tiers.strengths || {};
            var STRENGTH_DOMAINS = [
                'safe_adoption', 'human_oversight', 'independent_practice',
                'privacy_data_protection', 'safeguarding', 'assessment_integrity',
                'ai_literacy', 'bias_awareness'
            ];

            STRENGTH_DOMAINS.forEach(function (key) {
                var score = domainReadinessScore(domains, key, r);
                if (score === null || score < 76) return;

                var strengthKey = strengthTierKey(key);
                var thresholds = [100, 90, 76];
                var copy = '';
                for (var i = 0; i < thresholds.length; i++) {
                    if (score >= thresholds[i]) {
                        copy = strMap[strengthKey + '_' + thresholds[i]] || '';
                        if (copy) break;
                    }
                }
                if (!copy) return;

                var parts = copy.split(' — ');
                rows.push({ title: parts[0], description: parts[1] || '' });
            });
        }

        var items = strengths || rows;
        if (!items.length || !Results.teacherStrengthListHtml) return '';

        var headingHtml = renderOpts.cardHeadingHtml
            ? renderOpts.cardHeadingHtml(heading)
            : '<h3 class="airb__benchmark-card-heading">' + esc(heading) + '</h3>';

        return '<div class="airb__teacher-strength-card">'
             + headingHtml
             + Results.teacherStrengthListHtml(items, renderOpts)
             + '</div>';
    };

    // -------------------------------------------------------------------------
    // Leader focus areas
    // -------------------------------------------------------------------------

    R.leaderFocusAreas = function (r) {
        var domains = r.domain_scores || {};
        var html    = '';
        var focusCount = 0;

        var LEADER_DOMAINS = [
            'data_protection_awareness',
            'safeguarding',
            'human_oversight',
            'assessment_integrity',
            'governance_consistency',
            'bias_awareness',
            'ai_literacy'
        ];

        LEADER_DOMAINS.forEach(function (key) {
            var score = domainReadinessScore(domains, key);
            if (score === null || score >= 75 || focusCount >= 4) return;

            var focus = resolveDomainFocus('leader', key, score);
            html += Results.focusCardHtml({
                title      : focus.label,
                score      : score,
                severity   : focus.severity,
                summary    : focus.summary,
                impact     : focus.impact,
                actions    : focus.actions,
                variant    : 'leader',
            });
            focusCount++;
        });

        if (!html) return '';

        return '<div class="airb__section">'
             + '<h3 class="airb__section__title">Priority focus areas — what to fix and how</h3>'
             + html
             + '</div>';
    };

    // -------------------------------------------------------------------------
    // Leader urgent action panel
    // -------------------------------------------------------------------------

    R.leaderUrgentAction = function (r) {
        var domains = r.domain_scores || {};
        var overall = Math.round(r.alignment_score || 0);
        var action  = resolveLeaderUrgentAction(domains, overall);
        if (!action) return '';

        return '<div class="airb__card airb__urgent-action">'
             + '<div class="airb__urgent-action__label">Your single most urgent action</div>'
             + '<div class="airb__urgent-action__inner">'
             + '<div class="airb__urgent-action__icon" aria-hidden="true"></div>'
             + '<div>'
             + '<p class="airb__urgent-action__title">' + esc(action.title || '') + '</p>'
             + '<p class="airb__urgent-action__body">' + esc(action.body || action.rationale || '') + '</p>'
             + '</div>'
             + '</div>'
             + '</div>';
    };

    // -------------------------------------------------------------------------
    // Leader governance maturity card
    // -------------------------------------------------------------------------

    R.leaderGovernanceMaturity = function (r) {
        var score   = Math.round(r.governance_maturity_score || r.domain_scores && r.domain_scores.governance || 0);
        var copy    = resolveGovernanceMaturity(score);

        return '<div class="airb__card airb__governance-maturity">'
             + '<div class="airb__governance-maturity__label">Governance maturity</div>'
             + '<div class="airb__governance-maturity__row">'
             + '<span class="airb__governance-maturity__score">' + score + '%</span>'
             + '<span class="airb__badge airb__badge--' + (score >= 65 ? 'green' : score >= 50 ? 'blue' : 'amber') + '">'
             + esc(copy.label || '') + '</span>'
             + '</div>'
             + '<p class="airb__governance-maturity__desc">' + esc(copy.consequence || '') + '</p>'
             + '</div>';
    };

    // -------------------------------------------------------------------------
    // Support staff focus areas
    // -------------------------------------------------------------------------

    R.supportFocusAreas = function (r) {
        var domains = r.domain_scores || {};
        var html    = '';
        var focusCount = 0;

        var SUPPORT_DOMAINS = [
            'safeguarding',
            'privacy_data_protection',
            'human_oversight',
            'ai_literacy',
            'bias_awareness'
        ];

        SUPPORT_DOMAINS.forEach(function (key) {
            var score = domainReadinessScore(domains, key);
            if (score === null || score >= 75 || focusCount >= 3) return;

            var focus = resolveDomainFocus('support', key, score);
            html += Results.focusCardHtml({
                title      : focus.label,
                score      : score,
                severity   : focus.severity,
                summary    : focus.summary,
                impact     : focus.impact,
                impactTitle: 'In your role this matters because',
                actions    : focus.actions
            });
            focusCount++;
        });

        if (!html) return '';

        return '<div class="airb__section">'
             + '<h3 class="airb__section__title">Priority focus areas — what to strengthen</h3>'
             + html
             + '</div>';
    };

    // -------------------------------------------------------------------------
    // Student focus areas
    // -------------------------------------------------------------------------

    R.studentFocusAreas = function (r) {
        var domains = r.domain_scores || {};
        var html    = '';
        var focusCount = 0;

        var STUDENT_DOMAINS = [
            'independent_thinking',
            'privacy_awareness',
            'verification_skills',
            'ai_literacy'
        ];

        STUDENT_DOMAINS.forEach(function (key) {
            var score = domainReadinessScore(domains, key);
            if (score === null || score >= 75 || focusCount >= 2) return;

            var focus = resolveDomainFocus('student', key, score);
            html += Results.focusCardHtml({
                title        : focus.label,
                score        : score,
                severity     : focus.severity,
                summary      : focus.summary,
                impact       : focus.impact,
                impactTitle  : 'Your learning challenge',
                actions      : focus.actions,
                badgeText    : (focus.severity === 'critical' ? 'Needs attention' : 'Take care') + ' · ' + score + '%'
            });
            focusCount++;
        });

        if (!html) return '';

        return '<div class="airb__section">'
             + '<h3 class="airb__section__title">Where to improve — ' + focusCount + ' area' + (focusCount > 1 ? 's' : '') + ' to focus on</h3>'
             + html
             + '</div>';
    };

    // -------------------------------------------------------------------------
    // Parent focus topics
    // -------------------------------------------------------------------------

    R.parentFocusTopics = function (r) {
        var domains = r.domain_scores || {};
        var html    = '';
        var focusCount = 0;

        var PARENT_DOMAINS = [
            'deepfake_risk_awareness',
            'homework_support',
            'home_ai_safety',
            'parent_awareness'
        ];

        PARENT_DOMAINS.forEach(function (key) {
            var score = domainReadinessScore(domains, key);
            if (score === null || score >= 65 || focusCount >= 2) return;

            var focus = resolveDomainFocus('parent', key, score);
            html += Results.focusCardHtml({
                title     : focus.label,
                score     : score,
                severity  : focus.severity,
                summary   : focus.summary,
                impact    : focus.impact,
                impactTitle: 'What your child may encounter',
                actions   : focus.actions,
                badgeText : (score <= 34 ? 'Low awareness' : 'Needs attention') + ' · ' + score + '%'
            });
            focusCount++;
        });

        if (!html) return '';

        return '<div class="airb__section">'
             + '<h3 class="airb__section__title">Focus topics — what to tackle at home</h3>'
             + html
             + '</div>';
    };

    // -------------------------------------------------------------------------
    // Public focus areas
    // -------------------------------------------------------------------------

    R.publicFocusAreas = function (r) {
        var domains = r.domain_scores || {};
        var html    = '';
        var focusCount = 0;

        var PUBLIC_DOMAINS = [
            'data_privacy',
            'deepfake_scam_awareness',
            'workplace_ai',
            'verification',
            'emotional_social',
            'personal_ai_use'
        ];

        PUBLIC_DOMAINS.forEach(function (key) {
            var score = domainReadinessScore(domains, key);
            if (score === null || score >= 70 || focusCount >= 3) return;

            var focus = resolveDomainFocus('public', key, score);
            html += Results.focusCardHtml({
                title     : focus.label,
                score     : score,
                severity  : focus.severity,
                summary   : focus.summary,
                impact    : focus.impact,
                impactTitle: 'What this means for you',
                actions   : focus.actions,
                badgeText : (focus.severity === 'critical' ? 'At risk' : 'Needs work') + ' · ' + score + '%'
            });
            focusCount++;
        });

        if (!html) return '';

        return '<div class="airb__section">'
             + '<h3 class="airb__section__title">Priority focus areas</h3>'
             + html
             + '</div>';
    };

    // -------------------------------------------------------------------------
    // Score hero (role-aware wrapper)
    // -------------------------------------------------------------------------

    R.scoreHero = function (role, score) {
        var band  = resolveBand(role, score);
        var bands = BAND_LABELS[role] || BAND_LABELS.teacher;

        return Results.scoreHeroHtml({
            score       : score,
            label       : band.label || '',
            subLabel    : band.sub_label || '',
            consequence : band.consequence || '',
            colorRamp   : band.color_ramp || 'blue',
            bands       : bands,
            activeBand  : activeBandIndex(score),
            metricLabel : role === 'public' ? 'Overall AI safety score' : 'Overall readiness'
        });
    };

    // -------------------------------------------------------------------------
    // CTA block (role-aware wrapper)
    // -------------------------------------------------------------------------

    R.ctaBlock = function (role, score) {
        var cta  = resolveCta(role, score);
        var ramp = role === 'teacher' ? 'green'
                 : role === 'leader'  ? 'gray'
                 : role === 'public'  ? 'purple'
                 : 'gray';
        if (!cta.title) return '';
        return Results.ctaBlockHtml({
            eyebrow    : 'Recommended next step',
            title      : cta.title,
            description: cta.description,
            includes   : cta.includes || [],
            tags       : cta.tags || [],
            button     : cta.button,
            colorRamp  : ramp
        });
    };

    // -------------------------------------------------------------------------
    // Oversight panel (role-aware wrapper)
    // -------------------------------------------------------------------------

    R.oversightPanel = function (role, r) {
        var pct  = Math.round(r.human_oversight_ratio || r.human_oversight_readiness || 0);
        var copy = resolveOversight(role, pct);
        var title = (window.airbBenchmark && airbBenchmark.i18n && airbBenchmark.i18n.oversight) || 'Human oversight';
        var gaugeSvg = '';
        if (window.AIRB && AIRB.Share && AIRB.Share.oversightGaugeSvg) {
            gaugeSvg = AIRB.Share.oversightGaugeSvg(pct, title + ': ' + pct + '%');
        }
        if (!Results.oversightGaugePanelHtml) {
            return '';
        }
        return Results.oversightGaugePanelHtml({
            value      : pct,
            signal     : copy.signal,
            consequence: copy.consequence,
            title      : title,
            gaugeSvg   : gaugeSvg,
            role       : role,
        });
    };

    // -------------------------------------------------------------------------
    // Legacy server-computed focus stacks (PHP focus_block until full client build)
    // -------------------------------------------------------------------------

    R.renderTeacherFocusStack = function (focusAreas, biasHealth, opts) {
        return Results.teacherFocusAreasHtml ? Results.teacherFocusAreasHtml(focusAreas, biasHealth, opts) : '';
    };

    R.renderLeaderFocusStack = function (focusAreas, biasHealth, labelCfg, opts) {
        return Results.leaderFocusAreasHtml ? Results.leaderFocusAreasHtml(focusAreas, biasHealth, labelCfg, opts) : '';
    };

    R.renderSupportFocusStack = function (focusAreas, opts) {
        return Results.supportFocusAreasHtml ? Results.supportFocusAreasHtml(focusAreas, opts) : '';
    };

    R.renderParentFocusStack = function (focusAreas, opts) {
        return Results.parentFocusTopicsHtml ? Results.parentFocusTopicsHtml(focusAreas, opts) : '';
    };

}());
