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

    if (variant === 'parent' || variant === 'public') {
        var badgeSlug = (severity === 'critical' || severity === 'risk') ? 'risk' : 'attention';
        var html = '<div class="airb__parent-topic-card airb__parent-topic-card--' + esc(severity) + '">';
        html += '<div class="airb__parent-topic-header">';
        html += '<h4 class="airb__parent-topic-title">' + title + '</h4>';
        html += '<span class="airb__parent-metric-badge airb__parent-metric-badge--' + esc(badgeSlug) + '">' + esc(badgeText) + '</span>';
        html += '</div>';
        if (summary) html += '<p class="airb__parent-topic-summary">' + summary + '</p>';
        if (impact.length) {
            html += '<div class="airb__parent-topic-challenge airb__parent-topic-challenge--' + esc(severity) + '">';
            html += '<div class="airb__parent-topic-challenge-title">' + impactTitle + '</div>';
            impact.forEach(function (item) { html += '<div class="airb__parent-topic-challenge-bullet">' + esc(item) + '</div>'; });
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

    var badgeSlug = severity === 'critical' ? 'risk' : (severity === 'high' ? 'attention' : 'moderate');
    var mod = variant === 'teacher' ? ' airb__teacher-focus-card' : (variant === 'support' ? ' airb__support-focus-card' : '');
    var html = '<div class="airb__focus-card' + mod + ' airb__focus-card--' + severity + '">';
    html += '<div class="airb__focus-card-header">';
    html += '<h4 class="airb__focus-card-title">' + title + '</h4>';
    html += '<span class="airb__focus-badge airb__focus-badge--' + badgeSlug + '">' + esc(badgeText) + '</span>';
    html += '</div>';
    if (summary) html += '<p class="airb__focus-card-summary">' + summary + '</p>';
    if (impact.length) {
        html += '<div class="airb__focus-practice airb__teacher-focus-practice">';
        html += '<div class="airb__focus-practice-title">' + impactTitle + '</div>';
        impact.forEach(function (item) { html += '<div class="airb__teacher-focus-impact">' + esc(item) + '</div>'; });
        html += '</div>';
    }
    if (actions.length) {
        actions.forEach(function (item, idx) {
            html += '<div class="airb__teacher-action-row">';
            html += '<span class="airb__teacher-action-num">' + (idx + 1) + '</span>';
            html += '<span class="airb__teacher-action-text">' + esc(item) + '</span>';
            html += '</div>';
        });
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
        html += '<div class="airb__parent-topic-card airb__parent-topic-card--' + escFn(severity) + '">';
        html += '<div class="airb__parent-topic-header">';
        html += '<h4 class="airb__parent-topic-title">' + escFn(area.label) + '</h4>';
        html += '<span class="airb__parent-metric-badge airb__parent-metric-badge--' + escFn((area.badge && area.badge.slug) || 'attention') + '">' + escFn(opts.parentFocusBadge ? opts.parentFocusBadge(area) : '') + '</span>';
        html += '</div>';
        if (area.summary) {
            html += '<p class="airb__parent-topic-summary">' + escFn(area.summary) + '</p>';
        }
        var guidance = '';
        if (area.challenge_heading && (area.challenge_body || (area.challenge_bullets && area.challenge_bullets.length))) {
            guidance += '<div class="airb__parent-topic-challenge airb__parent-topic-challenge--' + escFn(severity) + '">';
            guidance += '<div class="airb__parent-topic-challenge-title">' + escFn(area.challenge_heading) + '</div>';
            if (area.challenge_body) {
                guidance += '<p class="airb__parent-topic-challenge-body">' + escFn(area.challenge_body) + '</p>';
            }
            if (area.challenge_bullets && area.challenge_bullets.length) {
                area.challenge_bullets.forEach(function (item) {
                    guidance += '<div class="airb__parent-topic-challenge-bullet">' + escFn(item) + '</div>';
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
        if (guidance && opts.focusGuidanceAccordionHtml) {
            var parentGuidanceLabel = area.challenge_heading || (opts.i18n && opts.i18n.focusGuidanceToggle) || 'Tips & steps to try';
            html += opts.focusGuidanceAccordionHtml(parentGuidanceLabel, guidance);
        } else if (guidance) {
            html += guidance;
        }
        html += '</div>';
    });
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
    parentFocusTopicsHtml: parentFocusTopicsHtml,
    strengthRowHtml: strengthRowHtml,
    strengthListHtml: strengthListHtml,
    peerBenchmarkRowHtml: peerBenchmarkRowHtml,
    ctaBlockHtml: ctaBlockHtml,
    sharePanelHtml: sharePanelHtml,
    scoreToramp: scoreToramp,
    oversightZoneColor: oversightZoneColor,
    esc: esc,
};
