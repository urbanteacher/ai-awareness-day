<?php
/**
 * Live Timeline — Icon options & SVG renderer.
 *
 * Loaded by inc/timeline.php.
 *
 * @package AI_Awareness_Day
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ──────────────────────────────────────────────
   3. Icon Options & SVG Renderer
   ────────────────────────────────────────────── */

/**
 * Available icon types for timeline entries.
 *
 * @return array<string, string>
 */
function aiad_timeline_icon_options(): array
{
    return array(
        'announcement' => __('Announcement', 'ai-awareness-day'),
        'resource' => __('New Resource', 'ai-awareness-day'),
        'partner' => __('New Partner', 'ai-awareness-day'),
        'signup' => __('Sign-up / Submission', 'ai-awareness-day'),
        'milestone' => __('News', 'ai-awareness-day'),
        'media' => __('CPD', 'ai-awareness-day'),
        'event' => __('Event', 'ai-awareness-day'),
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
        '' => __('Auto (category gradient or tech)', 'ai-awareness-day'),
        'gradient' => __('Category gradient', 'ai-awareness-day'),
        'tech' => __('Tech pattern', 'ai-awareness-day'),
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
 * Inner gradient/tech block (no wrapper figure).
 *
 * @param string $icon     Icon key for colour class.
 * @param string $fallback Meta value.
 * @param string $title    Entry title (decorative).
 * @return string HTML
 */
function aiad_timeline_cover_fallback_inner_html(string $icon, string $fallback, string $title, bool $show_icon = false): string
{
    $mode = aiad_timeline_resolve_cover_fallback($icon, $fallback);
    $class = 'timeline-entry__cover-fallback timeline-entry__cover-fallback--minimal timeline-entry__cover-fallback--' . sanitize_html_class($icon);
    if ('tech' === $mode) {
        $class .= ' timeline-entry__cover-fallback--tech';
    }

    $icon_html = '';
    if ($show_icon) {
        $icon_html = sprintf(
            '<span class="timeline-entry__cover-fallback-icon" aria-hidden="true">%s</span>',
            aiad_timeline_icon_svg($icon) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        );
    }

    return sprintf(
        '<div class="%1$s" role="img" aria-label="%2$s">%3$s</div>',
        esc_attr($class),
        esc_attr($title),
        $icon_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    );
}

/**
 * Render gradient/tech cover when no featured image (legacy figure wrapper).
 *
 * @param string $icon     Icon key for colour class.
 * @param string $fallback Meta value.
 * @param string $title    Entry title (decorative).
 * @return string HTML
 */
function aiad_render_timeline_cover_fallback(string $icon, string $fallback, string $title): string
{
    return '<figure class="timeline-entry__image timeline-entry__image--fallback">'
        . aiad_timeline_cover_fallback_inner_html($icon, $fallback, $title)
        . '</figure>';
}


/**
 * Render an inline SVG icon for a timeline entry.
 *
 * @param string $icon Icon key.
 * @return string SVG markup.
 */
function aiad_timeline_icon_svg(string $icon): string
{
    $attr = 'width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"';

    $icons = array(
        'announcement' => '<svg ' . $attr . '><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
        'resource' => '<svg ' . $attr . '><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
        'partner' => '<svg ' . $attr . '><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'signup' => '<svg ' . $attr . '><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>',
        'milestone' => '<svg ' . $attr . '><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
        'media' => '<svg ' . $attr . '><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>',
        'event' => '<svg ' . $attr . '><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
    );

    return $icons[$icon] ?? $icons['announcement'];
}

/**
 * SVG icon for the Like button (heart outline).
 *
 * @return string SVG markup.
 */
function aiad_timeline_like_icon_svg(): string
{
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>';
}

/**
 * SVG icon for the Share button.
 *
 * @return string SVG markup.
 */
function aiad_timeline_share_icon_svg(): string
{
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>';
}

/**
 * SVG icon for the Learn more link (arrow / external link).
 *
 * @return string SVG markup.
 */
function aiad_timeline_link_icon_svg(): string
{
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>';
}

/**
 * SVG icon for the "view post" button (arrow right).
 *
 * @return string SVG markup.
 */
function aiad_timeline_view_post_icon_svg(): string
{
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>';
}

/**
 * SVG icon for print button.
 *
 * @return string SVG markup.
 */
function aiad_print_icon_svg(): string
{
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>';
}

/**
 * SVG icon for back button (arrow left).
 *
 * @return string SVG markup.
 */
function aiad_back_icon_svg(): string
{
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>';
}

/**
 * Play button SVG for lite YouTube facade.
 *
 * @return string SVG markup.
 */
function aiad_timeline_play_icon_svg(): string
{
    return '<svg width="68" height="48" viewBox="0 0 68 48" aria-hidden="true"><path class="timeline-entry__video-facade-play-bg" d="M66.52 7.74c-.78-2.93-2.49-5.41-5.42-6.19C55.79.13 34 0 34 0S12.21.13 6.9 1.55c-2.93.78-4.63 3.26-5.42 6.19C.06 13.05 0 24 0 24s.06 10.95 1.48 16.26c.78 2.93 2.49 5.41 5.42 6.19C12.21 47.87 34 48 34 48s21.79-.13 27.1-1.55c2.93-.78 4.64-3.26 5.42-6.19C67.94 34.95 68 24 68 24s-.06-10.95-1.48-16.26z"/><path d="M45 24L27 14v20" fill="#fff"/></svg>';
}

/**
 * Render YouTube lite facade HTML (thumbnail + play button overlay).
 * Uses aiad_youtube_video_id() to validate the video ID.
 *
 * @param string $video_id YouTube video ID.
 * @param string $title    Video title for accessibility.
 * @return string HTML markup for the facade.
 */
function aiad_render_youtube_facade(string $video_id, string $title = ''): string
{
    if (empty($video_id)) {
        return '';
    }

    // Validate video ID using helper function if available
    if (function_exists('aiad_youtube_video_id')) {
        // If a URL was passed, extract the ID
        $extracted_id = aiad_youtube_video_id($video_id);
        if (!empty($extracted_id)) {
            $video_id = $extracted_id;
        } elseif (!preg_match('/^[a-zA-Z0-9_-]{11}$/', $video_id)) {
            // Invalid format
            return '';
        }
    } elseif (!preg_match('/^[a-zA-Z0-9_-]{11}$/', $video_id)) {
        // Fallback validation if helper doesn't exist
        return '';
    }

    $yt_thumb = 'https://img.youtube.com/vi/' . $video_id . '/hqdefault.jpg';
    $yt_title = !empty($title) ? $title : __('YouTube video', 'ai-awareness-day');
    $aria_label = sprintf(__('Play video: %s', 'ai-awareness-day'), $yt_title);

    ob_start();
    ?>
    <div class="timeline-entry__video-facade timeline-lite-yt" data-video-id="<?php echo esc_attr($video_id); ?>"
        data-title="<?php echo esc_attr($yt_title); ?>" role="button" tabindex="0"
        aria-label="<?php echo esc_attr($aria_label); ?>">
        <span class="timeline-entry__video-facade-thumb"
            style="background-image: url(<?php echo esc_url($yt_thumb); ?>);"></span>
        <span class="timeline-entry__video-facade-play"
            aria-hidden="true"><?php echo aiad_timeline_play_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
    </div>
    <?php
    return ob_get_clean();
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
        return __('Pinned', 'ai-awareness-day');
    }
    $terms = get_the_terms($entry->ID, 'timeline_category');
    if ($terms && !is_wp_error($terms)) {
        $name = $terms[0]->name;
        return $name;
    }
    $labels = array(
        'announcement' => __('Announcement', 'ai-awareness-day'),
        'media' => __('CPD', 'ai-awareness-day'),
        'resource' => __('Resource', 'ai-awareness-day'),
        'partner' => __('Partner', 'ai-awareness-day'),
        'event' => __('Event', 'ai-awareness-day'),
        'milestone' => __('News', 'ai-awareness-day'),
        'signup' => __('Sign-up', 'ai-awareness-day'),
    );
    return $labels[$icon] ?? $labels['announcement'];
}

require_once dirname(__DIR__) . '/timeline-layouts.php';

