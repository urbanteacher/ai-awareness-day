<?php
/**
 * Live Timeline — SVG icon and cover renderers, and the YouTube facade.
 *
 * The icon and cover option lists and the badge label live in the aiad-core plugin
 * (plugins/aiad-core/modules/timeline/icon-options.php).
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
    // Map timeline categories onto AiAd27 strand marks.
    $strand_map = array(
        'announcement' => 'creative',
        'resource'     => 'safe',
        'partner'      => 'responsible',
        'signup'       => 'safe',
        'milestone'    => 'smart',
        'media'        => 'future',
        'event'        => 'creative',
    );
    $strand = $strand_map[ $icon ] ?? 'smart';
    if ( function_exists( 'aiad_strand_icon_svg' ) ) {
        return aiad_strand_icon_svg( $strand, 20 );
    }

    $attr = 'width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"';
    return '<svg ' . $attr . '><path d="M12 2 L21 6 V12 C21 16.8 17 20.6 12 22 C7 20.6 3 16.8 3 12 V6 Z"/></svg>';
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
