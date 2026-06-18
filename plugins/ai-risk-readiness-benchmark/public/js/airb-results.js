/**
 * airb-results.js
 *
 * Shared result component builders for all role screens.
 * Called by airb-roles.js — never calls role-specific functions directly.
 *
 * Mirrors AIRB_Components (PHP) for the live client-side render.
 * PHP version is used for email/export/server render.
 *
 * ----------------------------------------------------------------------------
 * Module split map
 * ----------------------------------------------------------------------------
 *
 * airb-audit.js      Question flow, state machine, section nav, local storage
 *                    Functions: beginAudit, sectionsForRole, renderAudit,
 *                               nextSection, prevSection, collectAnswers,
 *                               syncNavPlacement, progressStepper
 *
 * airb-results.js    THIS FILE — shared component builders
 *                    Functions: scoreHeroHtml, metricCardHtml, metricGridHtml,
 *                               domainBarRowHtml, domainBarListHtml,
 *                               focusCardHtml, strengthRowHtml, strengthListHtml,
 *                               oversightGaugePanelHtml, oversightGaugeSvg,
 *                               oversightGaugeGeometry, oversightZoneColor,
 *                               peerBenchmarkRowHtml, wholeSchoolUnlockHtml,
 *                               ctaBlockHtml, sharePanelHtml
 *
 * airb-roles.js      Role-specific result assemblers — call shared components
 *                    Functions: teacherResultsHtml, leaderResultsHtml,
 *                               studentResultsHtml, parentResultsHtml,
 *                               supportResultsHtml, publicResultsHtml,
 *                               resultsProfileHtml (shared profile block)
 *
 * airb-share.js      Canvas PNG export, share image, social copy
 *                    Functions: shareOversightGaugeImage,
 *                               buildOversightSharePngBlob,
 *                               drawOversightGaugeOnCanvas,
 *                               canvasToPngBlob, shareResultsText
 * ----------------------------------------------------------------------------
 */

'use strict';

// ---------------------------------------------------------------------------
// Score hero
// ---------------------------------------------------------------------------

/**
 * @param {object} opts
 * @param {number} opts.score
 * @param {string} opts.label         Band label e.g. 'Strong'
 * @param {string} opts.subLabel      e.g. 'Strong position'
 * @param {string} opts.consequence   Plain-English meaning
 * @param {string} opts.colorRamp     red|amber|blue|green|purple
 * @param {string[]} opts.bands       Band label array
 * @param {number} opts.activeBand    0-indexed active band
 * @param {string} opts.metricLabel   e.g. 'Overall readiness'
 * @returns {string} HTML
 */
function scoreHeroHtml(opts) {
    var score      = Math.round(opts.score || 0);
    var label      = esc(opts.label || '');
    var subLabel   = esc(opts.subLabel || '');
    var consequence = esc(opts.consequence || '');
    var ramp       = opts.colorRamp || 'blue';
    var bands      = opts.bands || [];
    var active     = opts.activeBand || 0;
    var metricLbl  = esc(opts.metricLabel || 'Overall readiness');

    var html = '<div class="airb__score-hero airb__score-hero--' + ramp + '">';
    html += '<div class="airb__score-hero__top">';
    html += '<span class="airb__score-hero__num">' + score + '</span>';
    html += '<div class="airb__score-hero__meta">';
    html += '<span class="airb__score-hero__label">' + label + '</span>';
    if (subLabel) html += '<span class="airb__score-hero__sub">' + subLabel + '</span>';
    html += '<span class="airb__score-hero__metric-lbl">' + metricLbl + '</span>';
    html += '</div></div>';

    if (consequence) {
        html += '<p class="airb__score-hero__consequence">' + consequence + '</p>';
    }

    if (bands.length) {
        html += '<div class="airb__band-bar" role="img" aria-label="' + esc(label) + '">';
        bands.forEach(function(_, i) {
            html += '<div class="airb__band-bar__segment' + (i === active ? ' airb__band-bar__segment--active' : '') + '" aria-hidden="true"></div>';
        });
        html += '</div>';
        html += '<div class="airb__band-labels" aria-hidden="true">';
        bands.forEach(function(b) { html += '<span>' + esc(b) + '</span>'; });
        html += '</div>';
    }

    html += '</div>';
    return html;
}

// ---------------------------------------------------------------------------
// Metric cards
// ---------------------------------------------------------------------------

/**
 * @param {object} opts
 * @param {string} opts.label
 * @param {number} opts.value
 * @param {string} opts.signal       Band signal text
 * @param {string} opts.description
 * @param {string} opts.colorRamp
 * @param {boolean} [opts.pct=true]  Append % to value
 */
function metricCardHtml(opts) {
    var label   = esc(opts.label || '');
    var value   = opts.value || 0;
    var signal  = esc(opts.signal || '');
    var desc    = esc(opts.description || '');
    var ramp    = opts.colorRamp || 'blue';
    var showPct = opts.pct !== false;
    var display = showPct ? Math.round(value) + '%' : esc(String(value));

    var html = '<div class="airb__metric-card airb__metric-card--' + ramp + '">';
    html += '<div class="airb__metric-card__label">' + label + '</div>';
    html += '<div class="airb__metric-card__value">' + display + '</div>';
    if (signal) html += '<div class="airb__metric-card__signal">' + signal + '</div>';
    if (desc)   html += '<p class="airb__metric-card__desc">' + desc + '</p>';
    html += '</div>';
    return html;
}

/**
 * @param {string[]} cards  Array of metricCardHtml() strings
 * @param {number} [cols=3]
 */
function metricGridHtml(cards, cols) {
    return '<div class="airb__metric-grid airb__metric-grid--cols-' + (cols || 3) + '">'
         + cards.join('')
         + '</div>';
}

// ---------------------------------------------------------------------------
// Domain bar list
// ---------------------------------------------------------------------------

/**
 * @param {object} opts
 * @param {string} opts.label
 * @param {number} opts.score        0–100
 * @param {string} [opts.badge]      Optional badge text
 * @param {string} [opts.badgeRamp]  Override badge ramp
 */
function domainBarRowHtml(opts) {
    var label     = esc(opts.label || '');
    var score     = Math.round(opts.score || 0);
    var badge     = opts.badge ? esc(opts.badge) : '';
    var barRamp   = scoreToramp(score);
    var badgeRamp = opts.badgeRamp || barRamp;

    var html = '<div class="airb__domain-row">';
    html += '<span class="airb__domain-row__label">' + label + '</span>';
    html += '<div class="airb__domain-row__bar-wrap" role="img" aria-label="' + label + ': ' + score + '%">';
    html += '<div class="airb__domain-row__bar airb__domain-row__bar--' + barRamp + '" style="width:' + score + '%"></div>';
    html += '</div>';
    html += '<span class="airb__domain-row__val airb__domain-row__val--' + barRamp + '">' + score + '%</span>';
    if (badge) html += '<span class="airb__badge airb__badge--' + badgeRamp + '">' + badge + '</span>';
    html += '</div>';
    return html;
}

/**
 * @param {Array<object>} domains  Array of domainBarRowHtml() opts objects
 */
function domainBarListHtml(domains) {
    return '<div class="airb__card airb__domain-list">'
         + domains.map(domainBarRowHtml).join('')
         + '</div>';
}

// ---------------------------------------------------------------------------
// Focus cards
// ---------------------------------------------------------------------------

/**
 * @param {object} opts
 * @param {string}   opts.title
 * @param {number}   opts.score
 * @param {string}   opts.severity      critical|high|moderate
 * @param {string}   opts.summary
 * @param {string[]} opts.impact
 * @param {string}   [opts.impactTitle] Default 'In practice this means'
 * @param {string[]} opts.actions
 * @param {string}   [opts.badgeText]
 */
function focusCardHtml(opts) {
    var title       = esc(opts.title || opts.label || '');
    var score       = Math.round(opts.score || opts.pct || 0);
    var severity    = opts.severity || 'moderate';
    var summary     = esc(opts.summary || '');
    var impact      = opts.impact || opts.likely_impact || [];
    var impactTitle = esc(opts.impactTitle || opts.impact_title || 'In practice this means');
    var actions     = opts.actions || [];
    var badgeText   = opts.badgeText || opts.badge_text || (severity.charAt(0).toUpperCase() + severity.slice(1) + ' · ' + score + '%');
    var variant     = opts.variant || 'teacher';
    var belowThreshold = isBelowFocusGuidanceMax(score, opts);
    var belowCardClass = belowThreshold ? ' airb__focus-card--below-threshold' : '';
    var belowBadgeClass = belowThreshold ? ' airb__focus-badge--below-threshold' : '';
    var areaOpts = Object.assign({}, opts, { belowThreshold: belowThreshold });

    if (variant === 'parent' || variant === 'public') {
        var badgeSlug = (severity === 'critical' || severity === 'risk') ? 'risk' : 'attention';
        var html = '<div class="airb__parent-topic-card airb__parent-topic-card--' + esc(severity) + belowCardClass + '">';
        html += '<div class="airb__parent-topic-header">';
        html += '<h4 class="airb__parent-topic-title">' + title + '</h4>';
        html += '<span class="airb__parent-metric-badge airb__parent-metric-badge--' + esc(badgeSlug) + belowBadgeClass + '">' + esc(badgeText) + '</span>';
        html += '</div>';
        if (summary) html += '<p class="airb__parent-topic-summary">' + summary + '</p>';
        if (impact.length) {
            html += '<div class="airb__parent-topic-challenge airb__parent-topic-challenge--' + esc(severity) + '">';
            html += '<div class="airb__parent-topic-challenge-title' + (belowThreshold ? ' airb__focus-practice-title--below-threshold' : '') + '">' + impactTitle + '</div>';
            impact.forEach(function (item) {
                html += '<div class="airb__parent-topic-challenge-bullet' + (belowThreshold ? ' airb__parent-topic-challenge-bullet--below-threshold' : '') + '">' + esc(item) + '</div>';
            });
            html += '</div>';
        }
        if (actions.length) {
            actions.forEach(function (item, idx) {
                html += '<div class="airb__parent-action-row">';
                html += '<span class="airb__parent-action-num">' + (idx + 1) + '</span>';
                html += '<span class="airb__parent-action-text">' + esc(item) + '</span>';
                html += '</div>';
            });
        }
        html += '</div>';
        return html;
    }

    var badgeSlug = severity === 'critical' ? 'critical' : (severity === 'high' ? 'high' : 'moderate');
    var mod = variant === 'teacher' ? ' airb__teacher-focus-card' : (variant === 'support' ? ' airb__support-focus-card' : '');
    var html = '<div class="airb__focus-card' + mod + ' airb__focus-card--' + severity + belowCardClass + '">';
    html += '<div class="airb__focus-card-header">';
    html += '<h4 class="airb__focus-card-title">' + title + '</h4>';
    html += '<span class="airb__focus-badge airb__focus-badge--' + badgeSlug + belowBadgeClass + '">' + esc(badgeText) + '</span>';
    html += '</div>';
    if (summary) html += '<p class="airb__focus-card-summary">' + summary + '</p>';
    var guidanceInner = '';
    if (impact.length) {
        guidanceInner += '<div class="airb__focus-practice airb__teacher-focus-practice">';
        guidanceInner += '<div class="airb__focus-practice-title' + (belowThreshold ? ' airb__focus-practice-title--below-threshold' : '') + '">' + impactTitle + '</div>';
        impact.forEach(function (item) {
            guidanceInner += '<div class="airb__teacher-focus-impact' + (belowThreshold ? ' airb__teacher-focus-impact--below-threshold' : '') + '">' + esc(item) + '</div>';
        });
        guidanceInner += '</div>';
    }
    if (actions.length) {
        actions.forEach(function (item, idx) {
            guidanceInner += '<div class="airb__teacher-action-row">';
            guidanceInner += '<span class="airb__teacher-action-num">' + (idx + 1) + '</span>';
            guidanceInner += '<span class="airb__teacher-action-text">' + esc(item) + '</span>';
            guidanceInner += '</div>';
        });
    }
    if (guidanceInner) {
        if (opts.guidanceAccordionHtml) {
            html += focusGuidanceAccordionMarkup(
                areaOpts,
                opts.guidanceToggle || 'Tips & steps to try',
                guidanceInner,
                belowThreshold
            );
        } else {
            html += guidanceInner;
        }
    }
    html += '</div>';
    return html;
}

/**
 * Parent/public focus topic stack.
 * @param {Array<object>} focusAreas
 * @param {object} opts  { esc, parentFocusBadge, focusGuidanceAccordionHtml, i18n }
 */
function parentFocusTopicsHtml(focusAreas, opts) {
    opts = opts || {};
    var escFn = opts.esc || esc;
    if (!focusAreas || !focusAreas.length) return '';
    var html = '';
    focusAreas.forEach(function (area) {
        var severity = area.severity || (area.badge && area.badge.slug === 'risk' ? 'risk' : 'attention');
        var belowThreshold = isBelowFocusGuidanceMax(focusAreaPct(area), opts);
        var belowCardClass = belowThreshold ? ' airb__focus-card--below-threshold' : '';
        var belowBadgeClass = belowThreshold ? ' airb__focus-badge--below-threshold' : '';
        var areaOpts = Object.assign({}, opts, { belowThreshold: belowThreshold });
        html += '<div class="airb__parent-topic-card airb__parent-topic-card--' + escFn(severity) + belowCardClass + '">';
        html += '<div class="airb__parent-topic-header">';
        html += '<h4 class="airb__parent-topic-title">' + escFn(area.label) + '</h4>';
        html += '<span class="airb__parent-metric-badge airb__parent-metric-badge--' + escFn((area.badge && area.badge.slug) || 'attention') + belowBadgeClass + '">' + escFn(opts.parentFocusBadge ? opts.parentFocusBadge(area) : '') + '</span>';
        html += '</div>';
        if (area.summary && !opts.hideFocusSummary) {
            html += '<p class="airb__parent-topic-summary">' + escFn(area.summary) + '</p>';
        }
        var guidance = '';
        if (area.challenge_heading && (area.challenge_body || (area.challenge_bullets && area.challenge_bullets.length))) {
            guidance += '<div class="airb__parent-topic-challenge airb__parent-topic-challenge--' + escFn(severity) + '">';
            guidance += '<div class="airb__parent-topic-challenge-title' + (belowThreshold ? ' airb__focus-practice-title--below-threshold' : '') + '">' + escFn(area.challenge_heading) + '</div>';
            if (area.challenge_body) {
                guidance += '<p class="airb__parent-topic-challenge-body' + (belowThreshold ? ' airb__parent-topic-challenge-bullet--below-threshold' : '') + '">' + escFn(area.challenge_body) + '</p>';
            }
            if (area.challenge_bullets && area.challenge_bullets.length) {
                area.challenge_bullets.forEach(function (item) {
                    guidance += '<div class="airb__parent-topic-challenge-bullet' + (belowThreshold ? ' airb__parent-topic-challenge-bullet--below-threshold' : '') + '">' + escFn(item) + '</div>';
                });
            }
            guidance += '</div>';
        }
        if (area.actions && area.actions.length) {
            area.actions.forEach(function (item, idx) {
                guidance += '<div class="airb__parent-action-row">';
                guidance += '<span class="airb__parent-action-num">' + (idx + 1) + '</span>';
                guidance += '<span class="airb__parent-action-text">' + escFn(item) + '</span>';
                guidance += '</div>';
            });
        }
        if (guidance) {
            var parentGuidanceLabel = area.challenge_heading || (opts.i18n && opts.i18n.focusGuidanceToggle) || opts.guidanceToggle || 'Tips & steps to try';
            html += focusGuidanceAccordionMarkup(areaOpts, parentGuidanceLabel, guidance, belowThreshold);
        }
        html += '</div>';
    });
    return html;
}

/**
 * Teacher focus area cards (accordion guidance, optional bias note on safeguarding).
 *
 * @param {Array<object>} focusAreas
 * @param {object|null} biasHealth
 * @param {object} opts
 */
function teacherFocusAreasHtml(focusAreas, biasHealth, opts) {
    opts = opts || {};
    var escFn = opts.esc || esc;
    if (!focusAreas || !focusAreas.length) return '';
    var practiceHeading = opts.practiceHeading || 'In practice this means';
    var dashboardLayout = opts.layout === 'dashboard';
    var domainList = opts.domains || [];
    var html = '';
    focusAreas.forEach(function (area) {
        var severity = opts.leaderFocusSeverity ? opts.leaderFocusSeverity(area.pct) : 'moderate';
        var badge = opts.leaderFocusBadge ? opts.leaderFocusBadge(area.pct) : { slug: 'attention', text: (area.pct || 0) + '%' };
        var domainTone = dashboardLayout ? matchingDashboardDomainTone(area, domainList) : null;
        var toneLabel = domainTone ? domainTone.label : badge.text;
        var toneClass = domainTone ? domainTone.className : ('airb__focus-badge--' + escFn(badge.slug));
        var belowThreshold = isBelowFocusGuidanceMax(focusAreaPct(area), opts);
        var belowCardClass = belowThreshold ? ' airb__focus-card--below-threshold' : '';
        var belowBadgeClass = belowThreshold ? ' airb__focus-badge--below-threshold' : '';
        var areaOpts = Object.assign({}, opts, { belowThreshold: belowThreshold });

        html += '<div class="airb__focus-card airb__teacher-focus-card airb__focus-card--' + severity + belowCardClass + '">';
        html += '<div class="airb__focus-card-header">';
        html += '<h4 class="airb__focus-card-title">' + escFn(area.label) + '</h4>';
        if (!dashboardLayout) {
            html += '<span class="airb__focus-badge ' + toneClass + belowBadgeClass + '">' + escFn(badge.text) + '</span>';
        }
        html += '</div>';
        if (dashboardLayout) {
            html += '<div class="airb__focus-score-row">';
            html += '<p class="airb__focus-card-score' + (belowThreshold ? ' airb__focus-card-score--below-threshold' : '') + '">' + (area.pct || 0) + '%</p>';
            html += '<span class="airb__focus-badge ' + toneClass + belowBadgeClass + '">' + escFn(toneLabel) + '</span>';
            html += '</div>';
        }
        if (area.summary && !opts.hideFocusSummary) {
            html += '<p class="airb__focus-card-summary">' + escFn(area.summary) + '</p>';
        }
        if (area.slug === 'safeguarding' && biasHealth && biasHealth.score != null && opts.teacherBiasEqualityFocusNote) {
            var biasNote = opts.teacherBiasEqualityFocusNote(biasHealth.score);
            if (biasNote) {
                html += '<p class="airb__focus-card-bias-note">' + escFn(biasNote) + '</p>';
            }
        }
        var guidance = focusStackGuidanceInnerHtml(Object.assign({}, area, {
            challenge_heading: area.challenge_heading || practiceHeading,
        }), areaOpts);
        if (guidance) {
            html += focusGuidanceAccordionMarkup(
                areaOpts,
                dashboardLayout ? (opts.guidanceToggleClassroom || 'View classroom impact') : (opts.guidanceToggle || 'Tips & steps to try'),
                guidance,
                belowThreshold
            );
        }
        html += '</div>';
    });
    return html;
}

function matchingDashboardDomainTone(area, domains) {
    if (!domains || !domains.length) return null;
    var match = domains.find(function (domain) {
        return String(domain.label || '').toLowerCase() === String(area.label || '').toLowerCase();
    });
    if (!match) return null;
    var labels = { secure: 'secure', practice: 'practise', attention: 'focus' };
    var classes = {
        secure: 'airb__domain-badge--secure',
        practice: 'airb__domain-badge--practice',
        attention: 'airb__domain-badge--attention',
    };
    return {
        label: labels[match.tone] || 'focus',
        className: classes[match.tone] || classes.attention,
    };
}

/**
 * Support-staff focus area cards.
 *
 * @param {Array<object>} focusAreas
 * @param {object} opts
 */
function supportFocusAreasHtml(focusAreas, opts) {
    opts = opts || {};
    var escFn = opts.esc || esc;
    if (!focusAreas || !focusAreas.length) return '';
    var html = '';
    focusAreas.forEach(function (area) {
        var severity = opts.supportFocusSeverity
            ? opts.supportFocusSeverity(area.pct, area.severity)
            : (area.severity || 'moderate');
        var belowThreshold = isBelowFocusGuidanceMax(focusAreaPct(area), opts);
        var belowCardClass = belowThreshold ? ' airb__focus-card--below-threshold' : '';
        var belowBadgeClass = belowThreshold ? ' airb__focus-badge--below-threshold' : '';
        var areaOpts = Object.assign({}, opts, { belowThreshold: belowThreshold });
        html += '<div class="airb__focus-card airb__support-focus-card airb__focus-card--' + severity + belowCardClass + '">';
        html += '<div class="airb__focus-card-header">';
        html += '<h4 class="airb__focus-card-title">' + escFn(area.label) + '</h4>';
        html += '<span class="airb__focus-badge airb__focus-badge--' + (severity === 'critical' ? 'critical' : 'moderate') + belowBadgeClass + '">' + escFn(area.badge_text || ((area.pct || 0) + '%')) + '</span>';
        html += '</div>';
        if (area.summary && !opts.hideFocusSummary) html += '<p class="airb__focus-card-summary">' + escFn(area.summary) + '</p>';
        var guidance = '';
        if (area.challenge_bullets && area.challenge_bullets.length) {
            guidance += '<div class="airb__support-focus-challenge airb__support-focus-challenge--' + severity + '">';
            if (area.challenge_heading) {
                guidance += '<div class="airb__support-focus-challenge-title' + (belowThreshold ? ' airb__focus-practice-title--below-threshold' : '') + '">' + escFn(area.challenge_heading) + '</div>';
            }
            area.challenge_bullets.forEach(function (item) {
                guidance += '<div class="airb__support-focus-challenge-item' + (belowThreshold ? ' airb__teacher-focus-impact--below-threshold' : '') + '">' + escFn(item) + '</div>';
            });
            guidance += '</div>';
        }
        if (area.actions && area.actions.length) {
            area.actions.forEach(function (item, idx) {
                guidance += '<div class="airb__support-action-row">';
                guidance += '<span class="airb__support-action-num">' + (idx + 1) + '</span>';
                guidance += '<span class="airb__support-action-text">' + escFn(item) + '</span>';
                guidance += '</div>';
            });
        }
        if (guidance) {
            var supportGuidanceLabel = area.challenge_heading || opts.guidanceToggle || 'Tips & steps to try';
            html += focusGuidanceAccordionMarkup(areaOpts, supportGuidanceLabel, guidance, belowThreshold);
        }
        html += '</div>';
    });
    return html;
}

/**
 * Leader focus area cards (split badge, list-style practice/actions).
 *
 * @param {Array<object>} focusAreas
 * @param {object|null} biasHealth
 * @param {object} labelCfg
 * @param {object} opts
 */
function leaderFocusAreasHtml(focusAreas, biasHealth, labelCfg, opts) {
    opts = opts || {};
    labelCfg = labelCfg || {};
    var escFn = opts.esc || esc;
    if (!focusAreas || !focusAreas.length) return '';
    var practiceHeading = labelCfg.focus_practice_heading || opts.focusPracticeHeading || 'What this means in practice';
    var practiceHeadingShort = labelCfg.focus_practice_heading_short || opts.focusPracticeHeadingShort || 'In practice this means';
    var actionsHeading = labelCfg.focus_actions_heading || opts.focusActionsHeading || 'Actions';
    var html = '';
    focusAreas.forEach(function (area) {
        var severity = opts.leaderFocusSeverity ? opts.leaderFocusSeverity(area.pct) : 'moderate';
        var badge = opts.leaderFocusBadge ? opts.leaderFocusBadge(area.pct) : { slug: 'attention', core: '', detail: '' };
        var showPractice = area.likely_impact && area.likely_impact.length;
        var belowThreshold = isBelowFocusGuidanceMax(focusAreaPct(area), opts);
        var belowCardClass = belowThreshold ? ' airb__focus-card--below-threshold' : '';
        var belowBadgeClass = belowThreshold ? ' airb__focus-badge--below-threshold' : '';
        var areaOpts = Object.assign({}, opts, { belowThreshold: belowThreshold });
        html += '<div class="airb__focus-card airb__focus-card--' + severity + belowCardClass + '">';
        html += '<div class="airb__focus-card-header">';
        html += '<h4 class="airb__focus-card-title">' + escFn(area.label) + '</h4>';
        html += '<span class="airb__focus-badge airb__focus-badge--' + escFn(badge.slug) + belowBadgeClass + '">';
        html += '<span class="airb__focus-badge-core' + (belowThreshold ? ' airb__focus-badge-core--below-threshold' : '') + '">' + escFn(badge.core) + '</span>';
        if (badge.detail) {
            html += '<span class="airb__focus-badge-detail' + (belowThreshold ? ' airb__focus-badge-detail--below-threshold' : '') + '">' + escFn(badge.detail) + '</span>';
        }
        html += '</span></div>';
        if (area.summary && !opts.hideFocusSummary) {
            html += '<p class="airb__focus-card-summary">' + escFn(area.summary) + '</p>';
        }
        if (area.slug === 'safeguarding' && biasHealth && biasHealth.score != null && opts.leaderBiasEqualityFocusNote) {
            var biasNote = opts.leaderBiasEqualityFocusNote(biasHealth.score);
            if (biasNote) {
                html += '<p class="airb__focus-card-bias-note">' + escFn(biasNote) + '</p>';
            }
        }
        var guidance = '';
        if (showPractice) {
            guidance += '<div class="airb__focus-practice">';
            guidance += '<div class="airb__focus-practice-title' + (belowThreshold ? ' airb__focus-practice-title--below-threshold' : '') + '">' + (opts.leaderResponsiveLabel ? opts.leaderResponsiveLabel(practiceHeading, practiceHeadingShort) : escFn(practiceHeading)) + '</div>';
            guidance += '<ul class="airb__focus-practice-list">';
            area.likely_impact.forEach(function (item) {
                guidance += '<li class="' + (belowThreshold ? 'airb__focus-practice-list-item--below-threshold' : '') + '">' + escFn(item) + '</li>';
            });
            guidance += '</ul></div>';
        }
        if (area.actions && area.actions.length) {
            guidance += '<div class="airb__focus-practice airb__focus-actions">';
            guidance += '<div class="airb__focus-practice-title">' + escFn(actionsHeading) + '</div>';
            guidance += '<ul class="airb__focus-practice-list">';
            area.actions.forEach(function (item) {
                guidance += '<li>' + escFn(item) + '</li>';
            });
            guidance += '</ul></div>';
        }
        if (guidance) {
            html += focusGuidanceAccordionMarkup(
                areaOpts,
                opts.guidanceToggle || 'Tips & steps to try',
                guidance,
                belowThreshold
            );
        }
        html += '</div>';
    });
    return html;
}

/**
 * Oversight gauge panel (shared across staff/public roles).
 *
 * @param {object} opts
 * @param {number} opts.value
 * @param {string} opts.signal
 * @param {string} opts.consequence
 * @param {string} [opts.title]
 * @param {string} [opts.gaugeSvg]
 */
function oversightGaugePanelHtml(opts) {
    opts = opts || {};
    var val = Math.round(opts.value || 0);
    var signal = esc(opts.signal || '');
    var help = esc(opts.consequence || '');
    var title = esc(opts.title || 'Human oversight');
    var zoneColor = oversightZoneColor(val);
    var html = '<div class="airb__res-panel airb__res-panel--gauge" data-oversight-value="' + val + '">';
    html += '<h3>' + title + '</h3>';
    html += '<div class="airb__res-gauge-wrap">';
    if (opts.gaugeSvg) {
        html += opts.gaugeSvg;
    }
    html += '</div>';
    if (signal) {
        html += '<p class="airb__gauge-band" style="color:' + zoneColor + '">' + signal + '</p>';
    }
    if (help) {
        html += '<p class="airb__gauge-help">' + help + '</p>';
    }
    html += '</div>';
    return html;
}

// ---------------------------------------------------------------------------
// Strength list
// ---------------------------------------------------------------------------

/**
 * @param {object} opts
 * @param {string} opts.title
 * @param {string} opts.description
 */
function strengthRowHtml(opts) {
    var html = '<div class="airb__strength-row">';
    html += '<div class="airb__strength-row__tick" aria-hidden="true"></div>';
    html += '<div class="airb__strength-row__copy">';
    html += '<p class="airb__strength-row__title">' + esc(opts.title || '') + '</p>';
    if (opts.description) {
        html += '<p class="airb__strength-row__desc">' + esc(opts.description) + '</p>';
    }
    html += '</div></div>';
    return html;
}

/**
 * Teacher strength rows — matches legacy airb__teacher-strength-* markup.
 *
 * @param {Array<string|object>} strengths
 * @param {object} opts  { esc }
 */
function teacherStrengthListHtml(strengths, opts) {
    opts = opts || {};
    var escFn = opts.esc || esc;
    if (!strengths || !strengths.length) return '';
    var html = '';
    strengths.forEach(function (item) {
        var title = '';
        var detail = '';
        if (typeof item === 'string') {
            var parts = item.split(' — ');
            title = parts[0] || item;
            detail = parts[1] || '';
        } else if (item) {
            title = item.title || '';
            detail = item.detail || item.description || '';
        }
        if (!title) return;
        html += '<div class="airb__teacher-strength-row">';
        html += '<span class="airb__teacher-strength-tick" aria-hidden="true">✓</span>';
        html += '<div class="airb__teacher-strength-copy">';
        html += '<p class="airb__teacher-strength-title">' + escFn(title) + '</p>';
        if (detail) {
            html += '<p class="airb__teacher-strength-detail">' + escFn(detail) + '</p>';
        }
        html += '</div></div>';
    });
    return html;
}

/**
 * Support strength rows — legacy airb__support-strength-* markup.
 */
function supportStrengthListHtml(strengths, opts) {
    opts = opts || {};
    var escFn = opts.esc || esc;
    if (!strengths || !strengths.length) return '';
    var html = '';
    strengths.forEach(function (item) {
        var title = '';
        var detail = '';
        if (typeof item === 'string') {
            var parts = item.split(' — ');
            title = parts[0] || item;
            detail = parts[1] || '';
        } else if (item) {
            title = item.title || '';
            detail = item.detail || item.description || '';
        }
        if (!title) return;
        html += '<div class="airb__support-strength-row">';
        html += '<span class="airb__support-strength-tick" aria-hidden="true">✓</span>';
        html += '<div class="airb__support-strength-copy">';
        html += '<p class="airb__support-strength-title">' + escFn(title) + '</p>';
        if (detail) html += '<p class="airb__support-strength-detail">' + escFn(detail) + '</p>';
        html += '</div></div>';
    });
    return html;
}

/**
 * Student strength rows — legacy airb__student-strength-* markup.
 */
function studentStrengthListHtml(strengths, opts) {
    opts = opts || {};
    var escFn = opts.esc || esc;
    if (!strengths || !strengths.length) return '';
    var html = '';
    strengths.forEach(function (item) {
        var title = typeof item === 'string' ? item : (item.title || '');
        var detail = typeof item === 'object' && item ? (item.detail || item.description || '') : '';
        if (!title) return;
        html += '<div class="airb__student-strength-row">';
        html += '<span class="airb__student-strength-tick" aria-hidden="true">✓</span>';
        html += '<div class="airb__student-strength-copy">';
        html += '<p class="airb__student-strength-title">' + escFn(title) + '</p>';
        if (detail) html += '<p class="airb__student-strength-detail">' + escFn(detail) + '</p>';
        html += '</div></div>';
    });
    return html;
}

/**
 * Public strength rows — legacy airb__public-strength-* markup.
 */
function publicStrengthListHtml(strengths, opts) {
    opts = opts || {};
    var escFn = opts.esc || esc;
    if (!strengths || !strengths.length) return '';
    var html = '';
    strengths.forEach(function (item) {
        var title = typeof item === 'string' ? item : (item.title || '');
        var detail = typeof item === 'object' && item ? (item.detail || item.description || '') : '';
        if (!title) return;
        html += '<div class="airb__public-strength-row">';
        html += '<span class="airb__public-strength-tick" aria-hidden="true">✓</span>';
        html += '<div class="airb__public-strength-copy">';
        html += '<p class="airb__public-strength-title">' + escFn(title) + '</p>';
        if (detail) html += '<p class="airb__public-strength-detail">' + escFn(detail) + '</p>';
        html += '</div></div>';
    });
    return html;
}

/**
 * Student focus area cards.
 */
function studentFocusAreasHtml(focusAreas, opts) {
    opts = opts || {};
    var escFn = opts.esc || esc;
    if (!focusAreas || !focusAreas.length) return '';
    var html = '';
    focusAreas.forEach(function (area) {
        var badge = opts.studentFocusBadge ? opts.studentFocusBadge(area) : { className: 'developing', text: (area.pct || 0) + '%' };
        var belowThreshold = isBelowFocusGuidanceMax(focusAreaPct(area), opts);
        var belowCardClass = belowThreshold ? ' airb__focus-card--below-threshold' : '';
        var belowBadgeClass = belowThreshold ? ' airb__student-skill-badge--below-threshold' : '';
        var areaOpts = Object.assign({}, opts, { belowThreshold: belowThreshold });
        html += '<div class="airb__student-focus-card' + belowCardClass + '">';
        html += '<div class="airb__student-focus-header">';
        html += '<h4 class="airb__student-focus-title">' + escFn(area.label) + '</h4>';
        html += '<span class="airb__student-skill-badge airb__student-skill-badge--' + escFn(badge.className) + belowBadgeClass + '">' + escFn(badge.text) + '</span>';
        html += '</div>';
        if (area.summary) html += '<p class="airb__student-focus-summary">' + escFn(area.summary) + '</p>';
        var guidance = '';
        if (area.challenge_heading && (area.challenge_body || (area.challenge_bullets && area.challenge_bullets.length))) {
            guidance += '<div class="airb__student-focus-challenge">';
            guidance += '<div class="airb__student-focus-challenge-title' + (belowThreshold ? ' airb__focus-practice-title--below-threshold' : '') + '">' + escFn(area.challenge_heading) + '</div>';
            if (area.challenge_body) guidance += '<p class="airb__student-focus-challenge-body' + (belowThreshold ? ' airb__teacher-focus-impact--below-threshold' : '') + '">' + escFn(area.challenge_body) + '</p>';
            if (area.challenge_bullets && area.challenge_bullets.length) {
                area.challenge_bullets.forEach(function (item) {
                    guidance += '<div class="airb__student-focus-challenge-bullet' + (belowThreshold ? ' airb__teacher-focus-impact--below-threshold' : '') + '">' + escFn(item) + '</div>';
                });
            }
            guidance += '</div>';
        }
        if (area.actions && area.actions.length) {
            area.actions.forEach(function (item, idx) {
                guidance += '<div class="airb__student-action-row">';
                guidance += '<span class="airb__student-action-num">' + (idx + 1) + '</span>';
                guidance += '<span class="airb__student-action-text">' + escFn(item) + '</span>';
                guidance += '</div>';
            });
        }
        if (guidance) {
            html += focusGuidanceAccordionMarkup(
                areaOpts,
                area.challenge_heading || opts.guidanceToggle || 'Tips & steps to try',
                guidance,
                belowThreshold
            );
        }
        html += '</div>';
    });
    return html;
}

/**
 * @param {Array<object>} strengths  Array of strengthRowHtml() opts
 */
function strengthListHtml(strengths) {
    if (!strengths || !strengths.length) return '';
    return '<div class="airb__card airb__strength-list">'
         + strengths.map(strengthRowHtml).join('')
         + '</div>';
}

// ---------------------------------------------------------------------------
// Peer benchmark row
// ---------------------------------------------------------------------------

/**
 * @param {object} opts
 * @param {number} opts.yourScore
 * @param {number} opts.avgScore
 * @param {string} opts.avgLabel
 * @param {number} opts.topScore
 * @param {string} opts.topLabel
 */
function peerBenchmarkRowHtml(opts) {
    var yours    = Math.round(opts.yourScore || 0);
    var avg      = Math.round(opts.avgScore || 0);
    var avgLbl   = esc(opts.avgLabel || 'Average school');
    var top      = Math.round(opts.topScore || 0);
    var topLbl   = esc(opts.topLabel || 'Top quartile');
    var gapAvg   = avg - yours;
    var gapTop   = top - yours;

    var gapAvgText = gapAvg > 0
        ? gapAvg + ' points below average'
        : (gapAvg < 0 ? Math.abs(gapAvg) + ' points above average' : 'Equal to average');
    var gapTopText = gapTop > 0
        ? gapTop + ' points below top quartile'
        : (gapTop < 0 ? Math.abs(gapTop) + ' points above top quartile' : 'Equal to top quartile');

    var html = '<div class="airb__peer-bench">';
    html += '<div class="airb__peer-bench__scores">';
    html += _peerScore(yours, 'You', 'red');
    html += _peerScore(avg, avgLbl, 'gray');
    html += _peerScore(top, topLbl, 'green');
    html += '</div>';
    html += '<div class="airb__peer-bench__gaps">';
    html += '<span>' + esc(gapAvgText) + '</span>';
    html += '<span>' + esc(gapTopText) + '</span>';
    html += '</div></div>';
    return html;
}

function _peerScore(score, label, ramp) {
    return '<div class="airb__peer-bench__score">'
         + '<span class="airb__peer-bench__score-val airb__peer-bench__score-val--' + ramp + '">' + score + '%</span>'
         + '<span class="airb__peer-bench__score-lbl">' + esc(label) + '</span>'
         + '</div>';
}

// ---------------------------------------------------------------------------
// Whole-school unlock panel
// ---------------------------------------------------------------------------

/**
 * @param {object} opts
 * @param {Array<{label,count,target}>} opts.slots
 * @param {string} opts.intro
 * @param {string} opts.ctaText
 */
function wholeSchoolUnlockHtml(opts) {
    var slots   = opts.slots || [];
    var intro   = esc(opts.intro || '');
    var ctaText = esc(opts.ctaText || 'Roll out to your school');

    var html = '<div class="airb__card airb__unlock-panel">';
    if (intro) html += '<p class="airb__unlock-panel__intro">' + intro + '</p>';

    if (slots.length) {
        html += '<div class="airb__unlock-panel__grid">';
        slots.forEach(function(s) {
            var done = s.count >= s.target;
            html += '<div class="airb__unlock-slot' + (done ? ' airb__unlock-slot--done' : '') + '">';
            html += '<span class="airb__unlock-slot__label">' + esc(s.label) + '</span>';
            html += '<span class="airb__unlock-slot__count">' + s.count + ' of ' + s.target + '</span>';
            html += '</div>';
        });
        html += '</div>';
    }

    html += '<button type="button" class="airb__btn airb__btn--secondary airb__unlock-panel__cta">';
    html += ctaText + ' ↗</button></div>';
    return html;
}

// ---------------------------------------------------------------------------
// CTA block
// ---------------------------------------------------------------------------

/**
 * @param {object} opts
 * @param {string}   opts.eyebrow
 * @param {string}   opts.title
 * @param {string}   opts.description
 * @param {string[]} opts.includes
 * @param {string[]} opts.tags
 * @param {string}   opts.button
 * @param {string}   opts.colorRamp   gray|green|purple
 */
function ctaBlockHtml(opts) {
    var eyebrow = esc(opts.eyebrow || 'Recommended next step');
    var title   = esc(opts.title || '');
    var desc    = esc(opts.description || '');
    var incl    = opts.includes || [];
    var tags    = opts.tags || [];
    var button  = esc(opts.button || 'Get started');
    var ramp    = opts.colorRamp || 'gray';

    var html = '<div class="airb__cta-block airb__cta-block--' + ramp + '">';
    html += '<span class="airb__cta-block__eyebrow">' + eyebrow + '</span>';
    html += '<h3 class="airb__cta-block__title">' + title + '</h3>';
    if (desc) html += '<p class="airb__cta-block__desc">' + desc + '</p>';

    if (incl.length) {
        html += '<ul class="airb__cta-block__includes">';
        incl.forEach(function(i) { html += '<li>' + esc(i) + '</li>'; });
        html += '</ul>';
    }

    if (tags.length) {
        html += '<div class="airb__cta-block__tags">';
        tags.forEach(function(t) { html += '<span class="airb__cta-tag">' + esc(t) + '</span>'; });
        html += '</div>';
    }

    html += '<button type="button" class="airb__btn airb__btn--primary airb__cta-block__btn">';
    html += button + ' ↗</button></div>';
    return html;
}

// ---------------------------------------------------------------------------
// Share panel (public role)
// ---------------------------------------------------------------------------

/**
 * @param {object} opts
 * @param {string} opts.shareText   Pre-populated share copy
 * @param {string} opts.colorRamp
 */
function sharePanelHtml(opts) {
    var shareText = esc(opts.shareText || '');
    var ramp      = opts.colorRamp || 'purple';

    var html = '<div class="airb__share-panel airb__share-panel--' + ramp + '">';
    html += '<span class="airb__share-panel__eyebrow">Share your results</span>';
    html += '<h3 class="airb__share-panel__title">Most people don\'t know how they really use AI</h3>';
    if (shareText) {
        html += '<div class="airb__share-panel__preview"><p>' + shareText + '</p></div>';
    }
    html += '<div class="airb__share-panel__actions">';
    html += '<button type="button" class="airb__btn airb__btn--primary" data-airb-share-social>Share on social ↗</button>';
    html += '<button type="button" class="airb__btn airb__btn--ghost" data-airb-retake>Retake the benchmark</button>';
    html += '</div></div>';
    return html;
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/** Map a 0-100 score to a CSS ramp name */
function scoreToramp(score) {
    if (score <= 34) return 'red';
    if (score <= 59) return 'amber';
    if (score <= 74) return 'blue';
    return 'green';
}

/** Oversight zone color (used for gauge band label) */
function oversightZoneColor(pct) {
    if (pct >= 76) return '#3B6D11';
    if (pct >= 51) return '#185FA5';
    if (pct >= 26) return '#854F0B';
    return '#A32D2D';
}

/** Role thresholds for focus guidance (mirrors AIRB_Results_Guidance::focus_max_for_role). */
var FOCUS_GUIDANCE_MAX_BY_ROLE = {
    public: 50,
    teacher: 75,
    student: 70,
    parent: 75,
    leader: 70,
    support_staff: 75,
};

function focusGuidanceMaxFromOpts(opts) {
    opts = opts || {};
    if (opts.focusGuidanceMax != null) {
        var parsed = parseInt(opts.focusGuidanceMax, 10);
        if (!isNaN(parsed)) {
            return parsed;
        }
    }
    var role = opts.role ? String(opts.role) : '';
    if (role && FOCUS_GUIDANCE_MAX_BY_ROLE[role] != null) {
        return FOCUS_GUIDANCE_MAX_BY_ROLE[role];
    }
    return 70;
}

function focusAreaPct(area) {
    if (!area) {
        return 0;
    }
    if (area.pct != null) {
        return parseInt(area.pct, 10) || 0;
    }
    if (area.value != null) {
        return parseInt(area.value, 10) || 0;
    }
    return 0;
}

function isBelowFocusGuidanceMax(pct, opts) {
    var score = parseInt(pct, 10);
    if (isNaN(score)) {
        score = 0;
    }
    // Guidance eligibility and visual severity are different concepts.
    // Reserve the red below-threshold treatment for genuine attention scores.
    return score < 25;
}

function focusGuidanceAccordionMarkup(opts, summary, guidance, belowThreshold) {
    if (!guidance || !opts.focusGuidanceAccordionHtml) {
        return guidance || '';
    }
    return opts.focusGuidanceAccordionHtml(
        summary,
        guidance,
        opts.guidanceOpen === true,
        !!belowThreshold
    );
}

/** Build inner HTML for focus guidance (improvement areas + action tips). */
function focusStackGuidanceInnerHtml(area, opts) {
    opts = opts || {};
    var escFn = opts.esc || esc;
    var guidance = '';
    var impactHeading = area.challenge_heading || opts.practiceHeading || 'Areas to improve';
    var impact = area.likely_impact || area.challenge_bullets || [];
    if (impact.length) {
        guidance += '<div class="airb__focus-practice airb__teacher-focus-practice">';
        guidance += '<div class="airb__focus-practice-title' + (opts.belowThreshold ? ' airb__focus-practice-title--below-threshold' : '') + '">' + escFn(impactHeading) + '</div>';
        impact.forEach(function (item) {
            var impactClass = 'airb__teacher-focus-impact' + (opts.belowThreshold ? ' airb__teacher-focus-impact--below-threshold' : '');
            guidance += '<div class="' + impactClass + '">' + escFn(item) + '</div>';
        });
        guidance += '</div>';
    }
    if (area.actions && area.actions.length) {
        if (impact.length) {
            guidance += '<div class="airb__focus-practice-title" style="margin-top:0.75rem">' + escFn(opts.tipsHeading || 'Practical next steps') + '</div>';
        }
        area.actions.forEach(function (item, index) {
            guidance += '<div class="airb__teacher-action-row">';
            guidance += '<span class="airb__teacher-action-num">' + (index + 1) + '</span>';
            guidance += '<span class="airb__teacher-action-text">' + escFn(item) + '</span>';
            guidance += '</div>';
        });
    }
    return guidance;
}

/** Accordion wrapper for a focus area; collapsed by default unless opts.guidanceOpen is true. */
function focusGuidanceToggleLabel(domain, opts) {
    opts = opts || {};
    if (typeof opts.guidanceToggleForDomain === 'function') {
        return opts.guidanceToggleForDomain(domain);
    }
    if (opts.guidanceToggleClassroom) {
        return opts.guidanceToggleClassroom;
    }
    if (opts.guidanceToggleGovernance) {
        return opts.guidanceToggleGovernance;
    }
    if (opts.guidanceToggleOperational) {
        return opts.guidanceToggleOperational;
    }
    if (opts.guidanceToggleImpact) {
        return opts.guidanceToggleImpact;
    }
    var label = (domain && domain.label) ? String(domain.label) : 'guidance';
    return 'View ' + label.toLowerCase() + ' impact';
}

function focusGuidanceAccordionForArea(area, opts, domain) {
    opts = opts || {};
    var guidance = focusStackGuidanceInnerHtml(area, opts);
    if (!guidance || !opts.focusGuidanceAccordionHtml) return '';
    var openGuidance = opts.guidanceOpen === true;
    var label = opts.guidanceToggle || focusGuidanceToggleLabel(domain, opts);
    return opts.focusGuidanceAccordionHtml(label, guidance, openGuidance, !!opts.belowThreshold);
}

function normaliseKey(value) {
    return String(value || '')
        .toLowerCase()
        .replace(/&/g, 'and')
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '');
}

var DOMAIN_FOCUS_ALIASES = {
    assessment_design: ['assessment_integrity'],
    bias_and_equality: ['bias_equality', 'bias_readiness'],
    bias_equality: ['bias_and_equality', 'bias_readiness'],
    data_and_privacy: ['privacy', 'privacy_data_protection'],
    deepfake_and_scam_awareness: ['online_risk', 'online_risk_awareness', 'deepfake_scam_awareness'],
    independent_practice: ['ai_dependency'],
    parent_ai_dependency: ['ai_dependency'],
    personal_ai_use: ['ai_dependency'],
    privacy: ['privacy_data_protection', 'data_and_privacy'],
    verification: ['human_oversight', 'verification_habit'],
    workplace_ai: ['workplace_ai_use'],
};

function expandFocusKeys(keys) {
    var out = [];
    (keys || []).forEach(function (key) {
        if (!key || out.indexOf(key) !== -1) return;
        out.push(key);
        (DOMAIN_FOCUS_ALIASES[key] || []).forEach(function (alias) {
            var normalised = normaliseKey(alias);
            if (normalised && out.indexOf(normalised) === -1) {
                out.push(normalised);
            }
        });
    });
    return out;
}

function focusAreaForDomain(domain, focusAreas) {
    var domainKeys = expandFocusKeys([
        normaliseKey(domain.key),
        normaliseKey(domain.slug),
        normaliseKey(domain.label)
    ].filter(Boolean));

    for (var i = 0; i < (focusAreas || []).length; i++) {
        var area = focusAreas[i] || {};
        var areaKeys = expandFocusKeys([
            normaliseKey(area.key),
            normaliseKey(area.slug),
            normaliseKey(area.focus_slug),
            normaliseKey(area.label)
        ].filter(Boolean));

        for (var d = 0; d < domainKeys.length; d++) {
            if (areaKeys.indexOf(domainKeys[d]) !== -1) {
                return area;
            }
        }
    }

    return null;
}

function domainGridWithGuidanceHtml(domains, focusAreas, opts) {
    opts = opts || {};
    if (!domains || !domains.length) return '';
    var escFn = opts.esc || esc;
    var toneMap = opts.toneMap || {};
    var defaultTone = toneMap.practice || {
        border: '',
        bg: '',
        text: '',
        label: '',
        bar: '#64748b'
    };
    var accordionOpts = Object.assign({}, opts);
    var html = '<div class="benchmark-domain-grid benchmark-domain-grid--with-guidance">';

    domains.forEach(function (domain) {
        var tone = toneMap[domain.tone] || defaultTone;
        var toneKey = normaliseKey(domain.tone || 'practice');
        var area = focusAreaForDomain(domain, focusAreas);
        var hasGuidance = !!(area && ((area.likely_impact && area.likely_impact.length) || (area.challenge_bullets && area.challenge_bullets.length) || (area.challenge_body) || (area.actions && area.actions.length)));
        var domainValue = parseInt(domain.value, 10);
        if (isNaN(domainValue)) {
            domainValue = 0;
        }
        var belowThreshold = domainValue < 25;

        html += '<section class="benchmark-metric-card benchmark-metric-card--tone-' + escFn(toneKey) + ' ' + (tone.border || '') + (hasGuidance ? ' benchmark-metric-card--has-guidance' : '') + (belowThreshold ? ' benchmark-metric-card--below-threshold' : '') + '">';
        html += '<div class="benchmark-metric-card__header">';
        html += '<h3 class="benchmark-metric-card__title">' + escFn(domain.label) + '</h3>';
        html += '<span class="benchmark-metric-card__badge ' + (tone.bg || '') + ' ' + (tone.text || '') + (belowThreshold ? ' benchmark-metric-card__badge--below-threshold' : '') + '">' + escFn(tone.label || '') + '</span>';
        html += '</div>';
        html += '<div class="benchmark-metric-card__body">';
        html += '<p class="benchmark-metric-card__value' + (belowThreshold ? ' benchmark-metric-card__value--below-threshold' : '') + '">' + escFn(domain.value) + '%</p>';
        html += '</div>';
        html += '<div class="benchmark-metric-card__bar"><span style="width:' + escFn(domain.value) + '%;background:' + escFn(tone.bar || '#64748b') + '"></span></div>';

        if (hasGuidance) {
            html += focusGuidanceAccordionForArea(area, Object.assign({}, accordionOpts, {
                belowThreshold: belowThreshold,
            }), domain);
        }

        html += '</section>';
    });

    return html + '</div>';
}

/** HTML escape — prefers AIRB.esc from airb-core.js */
function esc(str) {
    if (window.AIRB && AIRB.esc) return AIRB.esc(str);
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

window.AIRB = window.AIRB || {};
AIRB.Results = {
    scoreHeroHtml: scoreHeroHtml,
    metricCardHtml: metricCardHtml,
    metricGridHtml: metricGridHtml,
    domainBarRowHtml: domainBarRowHtml,
    domainBarListHtml: domainBarListHtml,
    focusCardHtml: focusCardHtml,
    focusStackGuidanceInnerHtml: focusStackGuidanceInnerHtml,
    focusGuidanceToggleLabel: focusGuidanceToggleLabel,
    focusGuidanceAccordionForArea: focusGuidanceAccordionForArea,
    focusGuidanceMaxFromOpts: focusGuidanceMaxFromOpts,
    isBelowFocusGuidanceMax: isBelowFocusGuidanceMax,
    domainGridWithGuidanceHtml: domainGridWithGuidanceHtml,
    parentFocusTopicsHtml: parentFocusTopicsHtml,
    teacherFocusAreasHtml: teacherFocusAreasHtml,
    supportFocusAreasHtml: supportFocusAreasHtml,
    leaderFocusAreasHtml: leaderFocusAreasHtml,
    oversightGaugePanelHtml: oversightGaugePanelHtml,
    strengthRowHtml: strengthRowHtml,
    strengthListHtml: strengthListHtml,
    teacherStrengthListHtml: teacherStrengthListHtml,
    supportStrengthListHtml: supportStrengthListHtml,
    studentStrengthListHtml: studentStrengthListHtml,
    publicStrengthListHtml: publicStrengthListHtml,
    studentFocusAreasHtml: studentFocusAreasHtml,
    peerBenchmarkRowHtml: peerBenchmarkRowHtml,
    ctaBlockHtml: ctaBlockHtml,
    sharePanelHtml: sharePanelHtml,
    scoreToramp: scoreToramp,
    oversightZoneColor: oversightZoneColor,
    esc: esc,
};
