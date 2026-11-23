<?php
/**
 * Live Timeline — icon and cover fallback options, and the card badge label.
 *
 * Moved from the theme's inc/timeline/icons.php, whose SVG renderers stay in the theme.
 *
 * @package AIAD_Core
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Available icon types for timeline entries.
 *
 * @return array<string, string>
 */
function aiad_timeline_icon_options(): array
{
    return array(
        'announcement' => __('Announcement', 'aiad-core'),
        'resource' => __('New Resource', 'aiad-core'),
        'partner' => __('New Partner', 'aiad-core'),
        'signup' => __('Sign-up / Submission', 'aiad-core'),
        'milestone' => __('News', 'aiad-core'),
        'media' => __('CPD', 'aiad-core'),
        'event' => __('Event', 'aiad-core'),
    );
}

/**
 * Cover fallback options when no featured image is set.
 *
 * @return array<string, string>
 */
function aiad_timeline_cover_fallback_options(): array
{
    return array(
        '' => __('Auto (category gradient or tech)', 'aiad-core'),
        'gradient' => __('Category gradient', 'aiad-core'),
        'tech' => __('Tech pattern', 'aiad-core'),
    );
}

/**
 * Resolve cover fallback mode for an icon + stored preference.
 *
 * @param string $icon     Timeline icon key.
 * @param string $fallback Stored meta (empty = auto).
 * @return string 'gradient' or 'tech'
 */
function aiad_timeline_resolve_cover_fallback(string $icon, string $fallback = ''): string
{
    if ('tech' === $fallback) {
        return 'tech';
    }
    if ('gradient' === $fallback) {
        return 'gradient';
    }
    if (in_array($icon, array('milestone', 'signup', 'resource'), true)) {
        return 'tech';
    }
    return 'gradient';
}

/**
 * Featured badge label for a timeline entry (theme-dependent: Announcement, Update, category, etc.).
 *
 * @param WP_Post $entry Timeline post.
 * @param bool    $pinned Whether the entry is pinned.
 * @param string  $icon   Icon key (e.g. announcement, media, resource).
 * @return string Badge text for the card header.
 */
function aiad_timeline_featured_badge_label(WP_Post $entry, bool $pinned, string $icon): string
{
    if ($pinned) {
        return __('Pinned', 'aiad-core');
    }
    // In assigned order: the first is the primary topic (see 'sort' on the taxonomy).
    $terms = wp_get_object_terms($entry->ID, 'timeline_category', array('orderby' => 'term_order'));
    if ($terms && !is_wp_error($terms)) {
        // Stored with & as &amp;; callers escape on output, so decode here or it shows twice.
        return html_entity_decode($terms[0]->name, ENT_QUOTES, 'UTF-8');
    }
    $labels = array(
        'announcement' => __('Announcement', 'aiad-core'),
        'media' => __('CPD', 'aiad-core'),
        'resource' => __('Resource', 'aiad-core'),
        'partner' => __('Partner', 'aiad-core'),
        'event' => __('Event', 'aiad-core'),
        'milestone' => __('News', 'aiad-core'),
        'signup' => __('Sign-up', 'aiad-core'),
    );
    return $labels[$icon] ?? $labels['announcement'];
}
