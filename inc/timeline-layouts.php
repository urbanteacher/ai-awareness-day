<?php
/**
 * Timeline responsive layouts: mobile swipe deck + desktop magazine.
 *
 * @package AI_Awareness_Day
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Resolve cover image URL + fit mode (featured image, or partner logo for live sessions).
 *
 * @param WP_Post $entry Timeline post.
 * @param string  $size  thumb | hero.
 * @return array{url: string, fit: string, focal_post_id: int} fit is 'cover' or 'contain'; focal_post_id is post used for focal meta.
 */
function aiad_timeline_entry_cover_image_data(WP_Post $entry, string $size = 'thumb'): array
{
    $sizes = 'hero' === $size
        ? array('large', 'medium_large', 'medium')
        : array('medium_large', 'medium', 'large');

    $cover_fit = get_post_meta($entry->ID, '_aiad_timeline_cover_fit', true);
    foreach ($sizes as $img_size) {
        $url = get_the_post_thumbnail_url($entry->ID, $img_size);
        if ($url) {
            return array(
                'url' => $url,
                'fit' => 'contain' === $cover_fit ? 'contain' : 'cover',
                'focal_post_id' => $entry->ID,
            );
        }
    }

    $auto_type = get_post_meta($entry->ID, '_aiad_timeline_auto_type', true);
    $related = (int) get_post_meta($entry->ID, '_aiad_timeline_related_id', true);
    if ('live_session' === $auto_type && $related > 0) {
        $session_sizes = 'hero' === $size
            ? array('large', 'medium_large', 'medium')
            : array('medium_large', 'medium', 'large');

        foreach ($session_sizes as $img_size) {
            $session_url = get_the_post_thumbnail_url($related, $img_size);
            if ($session_url) {
                return array(
                    'url' => $session_url,
                    'fit' => 'cover',
                    'focal_post_id' => $related,
                );
            }
        }

        $partner_id = (int) get_post_meta($related, '_session_partner_id', true);
        if ($partner_id > 0) {
            foreach (array('medium_large', 'medium', 'large') as $img_size) {
                $logo = get_the_post_thumbnail_url($partner_id, $img_size);
                if ($logo) {
                    return array(
                        'url' => $logo,
                        'fit' => 'contain',
                        'focal_post_id' => $partner_id,
                    );
                }
            }
        }
    }

    return array(
        'url' => '',
        'fit' => 'cover',
        'focal_post_id' => $entry->ID,
    );
}

/**
 * Cover visual for swipe / magazine (featured image or gradient/tech fallback).
 *
 * @param WP_Post $entry   Timeline post.
 * @param string  $wrapper BEM block prefix (timeline-swipe | timeline-magazine).
 * @param string  $size    thumb | hero.
 * @return string HTML
 */
function aiad_timeline_entry_cover_visual(WP_Post $entry, string $wrapper, string $size = 'thumb'): string
{
    $icon = get_post_meta($entry->ID, '_aiad_timeline_icon', true) ?: 'announcement';
    $pinned = (bool) get_post_meta($entry->ID, '_aiad_timeline_pinned', true);
    $video_url = get_post_meta($entry->ID, '_aiad_timeline_video_url', true);
    $yt_id = function_exists('aiad_youtube_video_id') ? aiad_youtube_video_id($video_url) : '';
    $cover_image = aiad_timeline_entry_cover_image_data($entry, $size);
    $cover_fallback = get_post_meta($entry->ID, '_aiad_timeline_cover_fallback', true);
    $title = get_the_title($entry);
    $is_swipe = ('timeline-swipe' === $wrapper);

    if (!empty($yt_id)) {
        $yt_thumb = 'https://img.youtube.com/vi/' . $yt_id . '/hqdefault.jpg';
        return sprintf(
            '<figure class="%1$s__cover %1$s__cover--video"><img src="%2$s" alt="" loading="lazy" /></figure>',
            esc_attr($wrapper),
            esc_url($yt_thumb)
        );
    }

    if (!empty($cover_image['url'])) {
        $fit_class = 'contain' === $cover_image['fit'] ? ' resource-activity-figure__img--fit-contain timeline-cover-img--fit-contain' : '';
        $focal_id = isset($cover_image['focal_post_id']) ? (int) $cover_image['focal_post_id'] : $entry->ID;
        $style_attr = function_exists('aiad_entry_figure_img_style_attr')
            ? aiad_entry_figure_img_style_attr($focal_id, (string) $cover_image['fit'], 'feed')
            : '';
        return sprintf(
            '<figure class="%1$s__cover"><img class="%1$s__cover-img resource-activity-figure__img%3$s" src="%2$s" alt="" loading="lazy" width="800" height="600"%4$s /></figure>',
            esc_attr($wrapper),
            esc_url($cover_image['url']),
            esc_attr($fit_class),
            $style_attr // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
        );
    }

    $cover_mod = function_exists('aiad_timeline_cover_modifier_class')
        ? aiad_timeline_cover_modifier_class($icon, $pinned)
        : '';

    return sprintf(
        '<figure class="%1$s__cover %1$s__cover--fallback%2$s">%3$s</figure>',
        esc_attr($wrapper),
        $cover_mod ? ' ' . esc_attr($cover_mod) : '',
        aiad_timeline_cover_fallback_inner_html($icon, (string) $cover_fallback, $title, $is_swipe) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    );
}

/**
 * BEM modifier for magazine cover chrome (icon gradient / wedge).
 *
 * @param string $icon   Timeline icon key.
 * @param bool   $pinned Whether the entry is pinned.
 * @return string CSS class
 */
function aiad_timeline_cover_modifier_class(string $icon, bool $pinned): string
{
    if ($pinned) {
        return 'timeline-cover--pinned';
    }
    return 'timeline-cover--' . sanitize_html_class($icon);
}

/**
 * Optional overlay layers on magazine covers (gradient lives on inner fallback).
 *
 * @return string HTML
 */
function aiad_timeline_magazine_cover_layers_html(): string
{
    return '<span class="timeline-magazine__cover-scrim timeline-magazine__cover-scrim--top" aria-hidden="true"></span>'
        . '<span class="timeline-magazine__cover-fade" aria-hidden="true"></span>';
}

/**
 * Bottom scrim only (mobile swipe — no top gradient).
 *
 * @return string HTML
 */
function aiad_timeline_swipe_cover_layers_html(): string
{
    return '<span class="timeline-magazine__cover-fade" aria-hidden="true"></span>';
}

/**
 * Date only on mobile swipe cover (badge lives in panel).
 *
 * @param string $date_label Human-readable date.
 * @param string $date_iso   ISO datetime attribute.
 * @param string $date_title Optional full date for title attribute.
 * @return string HTML
 */
function aiad_timeline_swipe_cover_date_html(
    string $date_label,
    string $date_iso,
    string $date_title = ''
): string {
    $title_attr = $date_title ? ' title="' . esc_attr($date_title) . '"' : '';

    return sprintf(
        '<div class="timeline-magazine__cover-meta timeline-swipe__cover-meta" aria-hidden="true">'
        . '<time class="timeline-entry__date" datetime="%1$s"%2$s>%3$s</time>'
        . '</div>',
        esc_attr($date_iso),
        $title_attr,
        esc_html($date_label)
    );
}

/**
 * Partner pill + time on magazine cover (original entry badge/date styles).
 *
 * @param string $badge      Badge label.
 * @param string $icon       Icon key for badge colour.
 * @param bool   $pinned     Pinned state.
 * @param string $date_label Human-readable date.
 * @param string $date_iso   ISO datetime attribute.
 * @param string $date_title Optional full date for title attribute.
 * @return string HTML
 */
function aiad_timeline_magazine_cover_meta_html(
    string $badge,
    string $icon,
    bool $pinned,
    string $date_label,
    string $date_iso,
    string $date_title = ''
): string {
    $badge_class = $pinned ? 'pinned' : $icon;
    $title_attr = $date_title ? ' title="' . esc_attr($date_title) . '"' : '';

    return sprintf(
        '<div class="timeline-magazine__cover-meta" aria-hidden="true">'
        . '<div class="timeline-magazine__cover-meta-badge"><span class="timeline-entry__badge timeline-entry__badge--%1$s">%2$s</span></div>'
        . '<time class="timeline-entry__date" datetime="%3$s"%4$s>%5$s</time>'
        . '</div>',
        esc_attr($badge_class),
        esc_html($badge),
        esc_attr($date_iso),
        $title_attr,
        esc_html($date_label)
    );
}

/**
 * Shared action row for swipe hero and magazine hero.
 *
 * @param WP_Post $entry Timeline post.
 * @return string HTML
 */
function aiad_timeline_entry_actions_html(WP_Post $entry): string
{
    $likes = (int) get_post_meta($entry->ID, '_aiad_timeline_like_count', true);
    $link_url = get_post_meta($entry->ID, '_aiad_timeline_link_url', true);
    $link_label = get_post_meta($entry->ID, '_aiad_timeline_link_label', true);
    $entry_url = get_permalink($entry) ?: home_url('/');
    $entry_title = get_the_title($entry);
    $link_label = $link_label ?: __('Learn more', 'ai-awareness-day');

    ob_start();
    ?>
    <div class="timeline-entry__actions" aria-label="<?php esc_attr_e('Actions', 'ai-awareness-day'); ?>">
        <button type="button" class="timeline-entry__like" data-entry-id="<?php echo esc_attr((string) $entry->ID); ?>"
            aria-pressed="false" aria-label="<?php esc_attr_e('Like this update', 'ai-awareness-day'); ?>">
            <span class="timeline-entry__like-icon"
                aria-hidden="true"><?php echo aiad_timeline_like_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
            <span class="timeline-entry__like-count"><?php echo esc_html((string) $likes); ?></span>
        </button>
        <button type="button" class="timeline-entry__share" data-entry-id="<?php echo esc_attr((string) $entry->ID); ?>"
            data-url="<?php echo esc_url($entry_url); ?>" data-title="<?php echo esc_attr($entry_title); ?>"
            aria-label="<?php esc_attr_e('Share this update', 'ai-awareness-day'); ?>">
            <span class="timeline-entry__share-icon"
                aria-hidden="true"><?php echo aiad_timeline_share_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
        </button>
        <?php if ($link_url): ?>
            <a href="<?php echo esc_url($link_url); ?>" class="timeline-entry__link timeline-entry__link--action"
                data-entry-id="<?php echo esc_attr((string) $entry->ID); ?>"
                aria-label="<?php echo esc_attr($link_label); ?>">
                <span class="timeline-entry__link-icon"
                    aria-hidden="true"><?php echo aiad_timeline_link_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
            </a>
        <?php endif; ?>
        <a href="<?php echo esc_url($entry_url); ?>"
            class="timeline-entry__link timeline-entry__link--action timeline-entry__view-post"
            data-entry-id="<?php echo esc_attr((string) $entry->ID); ?>"
            aria-label="<?php esc_attr_e('View full post', 'ai-awareness-day'); ?>">
            <span class="timeline-entry__link-icon"
                aria-hidden="true"><?php echo aiad_timeline_view_post_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
        </a>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Whether a timeline entry embeds an interactive shortcode (no full body in feed hero).
 *
 * @param WP_Post $entry Timeline post.
 */
function aiad_timeline_entry_has_interactive_shortcode(WP_Post $entry): bool
{
    $needles = array(
        '[aiad_buzzwords',
        '[aiad_llm_explainer',
        '[aiad_llm_order_game',
        '[aiad_speed_quiz',
    );
    foreach ($needles as $needle) {
        if (false !== strpos($entry->post_content, $needle)) {
            return true;
        }
    }
    return false;
}

/**
 * Teaser HTML for magazine hero (excerpt + CTA instead of full interactive markup).
 *
 * @param WP_Post $entry Timeline post.
 * @return string HTML
 */
function aiad_timeline_hero_teaser_html(WP_Post $entry): string
{
    if (!aiad_timeline_entry_has_interactive_shortcode($entry)) {
        return (string) apply_filters('the_content', (string) get_post_field('post_content', $entry));
    }

    $excerpt = aiad_timeline_entry_excerpt_text($entry);
    $url = get_permalink($entry) ?: '';
    $cta = __('Open interactive activity →', 'ai-awareness-day');

    if (false !== strpos($entry->post_content, '[aiad_speed_quiz')) {
        $cta = __('Take the speed quiz →', 'ai-awareness-day');
    } elseif (false !== strpos($entry->post_content, '[aiad_llm_explainer')) {
        $cta = __('Try the 6-step explainer →', 'ai-awareness-day');
    } elseif (false !== strpos($entry->post_content, '[aiad_buzzwords')) {
        $cta = __('Explore the glossary →', 'ai-awareness-day');
    }

    return '<p>' . esc_html($excerpt) . '</p><p><a class="timeline-magazine__hero-cta" href="'
        . esc_url($url) . '">' . esc_html($cta) . '</a></p>';
}

/**
 * Plain-text excerpt for cards.
 *
 * @param WP_Post $entry Timeline post.
 * @return string
 */
function aiad_timeline_entry_excerpt_text(WP_Post $entry): string
{
    $excerpt = get_the_excerpt($entry);
    if ($excerpt) {
        return wp_strip_all_tags($excerpt);
    }
    $content = get_post_field('post_content', $entry);
    return wp_trim_words(wp_strip_all_tags($content), 40, '…');
}

/**
 * Short plain-text blurb for mobile swipe panel (not full post body).
 *
 * @param WP_Post $entry Timeline post.
 * @return string
 */
function aiad_timeline_swipe_excerpt_text(WP_Post $entry): string
{
    $text = aiad_timeline_entry_excerpt_text($entry);
    $text = preg_replace('/\s+/u', ' ', trim($text));

    return wp_trim_words($text, 28, '…');
}

/**
 * Render one mobile swipe slide (portrait: cover top, copy below).
 *
 * @param WP_Post $entry Timeline post.
 * @return string HTML
 */
function aiad_render_timeline_swipe_slide(WP_Post $entry): string
{
    $pinned = (bool) get_post_meta($entry->ID, '_aiad_timeline_pinned', true);
    $icon = get_post_meta($entry->ID, '_aiad_timeline_icon', true) ?: 'announcement';
    $badge = aiad_timeline_featured_badge_label($entry, $pinned, $icon);
    $date_human = human_time_diff(get_post_timestamp($entry), time());
    $date_label = sprintf(__('%s ago', 'ai-awareness-day'), $date_human);
    $date_full = get_the_date('j M Y', $entry);
    $entry_url = get_permalink($entry) ?: '#';
    $link_url = get_post_meta($entry->ID, '_aiad_timeline_link_url', true);
    $link_label = get_post_meta($entry->ID, '_aiad_timeline_link_label', true) ?: __('Learn more', 'ai-awareness-day');
    $cta_url = $link_url ? $link_url : $entry_url;

    ob_start();
    ?>
    <article class="timeline-swipe__slide" data-entry-id="<?php echo esc_attr((string) $entry->ID); ?>"
        aria-roledescription="slide">
        <div class="timeline-swipe__media">
            <?php echo aiad_timeline_entry_cover_visual($entry, 'timeline-swipe', 'hero'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php echo aiad_timeline_swipe_cover_layers_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php
            echo aiad_timeline_swipe_cover_date_html( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                $date_label,
                get_the_date('c', $entry),
                $date_full
            );
            ?>
            <div class="timeline-swipe__media-overlay">
                <h3 class="timeline-swipe__title"><?php echo esc_html(get_the_title($entry)); ?></h3>
            </div>
            <span class="timeline-swipe__media-hint" aria-hidden="true"><?php esc_html_e('Swipe', 'ai-awareness-day'); ?>
                →</span>
        </div>
        <div class="timeline-swipe__panel">
            <div class="timeline-swipe__panel-header">
                <span
                    class="timeline-entry__badge timeline-entry__badge--<?php echo esc_attr($pinned ? 'pinned' : $icon); ?>"><?php echo esc_html($badge); ?></span>
            </div>
            <div class="timeline-swipe__excerpt"><?php echo esc_html(aiad_timeline_swipe_excerpt_text($entry)); ?></div>
            <div class="timeline-swipe__panel-footer">
                <?php echo aiad_timeline_entry_actions_html($entry); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <a href="<?php echo esc_url($cta_url); ?>"
                    class="timeline-swipe__cta btn-action"><?php echo esc_html($link_label); ?></a>
            </div>
        </div>
    </article>
    <?php
    return ob_get_clean();
}

/**
 * Mobile swipe deck wrapper.
 *
 * @param WP_Post[] $entries Timeline posts.
 * @return string HTML
 */
function aiad_render_timeline_swipe_deck(array $entries): string
{
    if (empty($entries)) {
        return '';
    }

    ob_start();
    ?>
    <div class="timeline-swipe" data-slide-count="<?php echo esc_attr((string) count($entries)); ?>">
        <div class="timeline-swipe__viewport" tabindex="0" role="region"
            aria-label="<?php esc_attr_e('Campaign updates, swipe horizontally', 'ai-awareness-day'); ?>">
            <?php foreach ($entries as $entry): ?>
                <?php echo aiad_render_timeline_swipe_slide($entry); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Magazine supporting row (compact).
 *
 * @param WP_Post $entry Timeline post.
 * @return string HTML
 */
function aiad_render_timeline_magazine_row(WP_Post $entry): string
{
    $pinned = (bool) get_post_meta($entry->ID, '_aiad_timeline_pinned', true);
    $icon = get_post_meta($entry->ID, '_aiad_timeline_icon', true) ?: 'announcement';
    $badge = aiad_timeline_featured_badge_label($entry, $pinned, $icon);
    $cover_mod = aiad_timeline_cover_modifier_class($icon, $pinned);
    $date_human = human_time_diff(get_post_timestamp($entry), time());
    $date_label = sprintf(__('%s ago', 'ai-awareness-day'), $date_human);
    $date_iso = get_the_date('c', $entry);
    $entry_url = get_permalink($entry) ?: '#';

    ob_start();
    ?>
    <li class="timeline-magazine__card">
        <article class="timeline-magazine__card-inner">
            <div class="timeline-magazine__card-media">
                <a href="<?php echo esc_url($entry_url); ?>"
                    class="timeline-magazine__card-media-link timeline-magazine__card-media-link--chamfer <?php echo esc_attr($cover_mod); ?>"
                    tabindex="-1" aria-hidden="true">
                    <?php echo aiad_timeline_entry_cover_visual($entry, 'timeline-magazine', 'thumb'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <span class="timeline-magazine__cover-scrim timeline-magazine__cover-scrim--top"
                        aria-hidden="true"></span>
                </a>
                <?php echo aiad_timeline_magazine_cover_meta_html($badge, $icon, $pinned, $date_label, $date_iso); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
            <div class="timeline-magazine__card-body">
                <h4 class="timeline-magazine__card-title">
                    <a href="<?php echo esc_url($entry_url); ?>"><?php echo esc_html(get_the_title($entry)); ?></a>
                </h4>
                <p class="timeline-magazine__card-excerpt">
                    <?php echo esc_html(aiad_timeline_entry_excerpt_text($entry)); ?></p>
                <?php echo aiad_timeline_entry_actions_html($entry); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </article>
    </li>
    <?php
    return ob_get_clean();
}

/**
 * Desktop magazine layout (hero + supporting grid).
 *
 * @param WP_Post[] $entries Timeline posts.
 * @return string HTML
 */
function aiad_render_timeline_magazine(array $entries): string
{
    if (empty($entries)) {
        return '';
    }

    $hero = $entries[0];
    $subs = function_exists('aiad_timeline_magazine_sub_count') ? aiad_timeline_magazine_sub_count() : 4;
    $rest = array_slice($entries, 1, $subs);
    $pinned = (bool) get_post_meta($hero->ID, '_aiad_timeline_pinned', true);
    $icon = get_post_meta($hero->ID, '_aiad_timeline_icon', true) ?: 'announcement';
    $badge = aiad_timeline_featured_badge_label($hero, $pinned, $icon);
    $cover_mod = aiad_timeline_cover_modifier_class($icon, $pinned);
    $date_human = human_time_diff(get_post_timestamp($hero), time());
    $date_label = sprintf(__('%s ago', 'ai-awareness-day'), $date_human);
    $date_full = get_the_date('j M Y', $hero);
    $date_iso = get_the_date('c', $hero);
    $hero_permalink = get_permalink($hero) ?: '';
    $hero_content = aiad_timeline_hero_teaser_html($hero);
    $hero_media_tag = $hero_permalink ? 'a' : 'div';
    $hero_media_label = sprintf(
        /* translators: %s: timeline entry title */
        __('Read full update: %s', 'ai-awareness-day'),
        get_the_title($hero)
    );

    ob_start();
    ?>
    <div class="timeline-magazine">
        <article class="timeline-magazine__hero">
            <div class="timeline-magazine__hero-media">
                <<?php echo esc_html($hero_media_tag); ?>
                    <?php if ($hero_permalink) : ?>
                        href="<?php echo esc_url($hero_permalink); ?>"
                    <?php endif; ?>
                    class="timeline-magazine__hero-media-frame timeline-magazine__hero-media-link <?php echo esc_attr($cover_mod); ?>"
                    <?php if ($hero_permalink) : ?>
                        aria-label="<?php echo esc_attr($hero_media_label); ?>"
                    <?php endif; ?>>
                    <?php echo aiad_timeline_entry_cover_visual($hero, 'timeline-magazine', 'hero'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php echo aiad_timeline_magazine_cover_layers_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php echo aiad_timeline_magazine_cover_meta_html($badge, $icon, $pinned, $date_label, $date_iso, $date_full); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <div class="timeline-magazine__hero-media-overlay">
                        <h3 class="timeline-magazine__hero-title"><?php echo esc_html(get_the_title($hero)); ?></h3>
                    </div>
                </<?php echo esc_html($hero_media_tag); ?>>
            </div>
            <div class="timeline-magazine__hero-text">
                <?php if ($hero_content): ?>
                    <div class="timeline-magazine__hero-content timeline-entry__content">
                        <?php echo wp_kses_post($hero_content); ?></div>
                <?php endif; ?>
                <?php if ($hero_permalink): ?>
                    <a class="timeline-magazine__read-more" href="<?php echo esc_url($hero_permalink); ?>">
                        <?php esc_html_e('Read full update →', 'ai-awareness-day'); ?>
                    </a>
                <?php endif; ?>
                <?php echo aiad_timeline_entry_actions_html($hero); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </article>
        <?php if (!empty($rest)): ?>
            <ul class="timeline-magazine__more">
                <?php foreach ($rest as $entry): ?>
                    <?php echo aiad_render_timeline_magazine_row($entry); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * /timeline/ archive — grid of magazine-style rows (all breakpoints).
 *
 * @param WP_Post[] $entries Timeline posts.
 * @return string HTML
 */
function aiad_render_timeline_archive_feed(array $entries): string
{
    if (empty($entries)) {
        return '<div class="timeline-feed__body timeline-feed__body--archive"><p class="timeline-feed__empty">'
            . esc_html__('No entries found.', 'ai-awareness-day')
            . '</p></div>';
    }

    ob_start();
    ?>
    <div class="timeline-feed__body timeline-feed__body--archive">
        <ul class="timeline-archive__list">
            <?php foreach ($entries as $entry): ?>
                <?php echo aiad_render_timeline_magazine_row($entry); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Mobile swipe + desktop magazine wrappers.
 *
 * @param WP_Post[] $entries Timeline posts.
 * @return string HTML
 */
function aiad_render_timeline_feed_layouts(array $entries): string
{
    if (empty($entries)) {
        return '<div class="timeline-feed__body"><p class="timeline-feed__empty">' . esc_html__('No entries found.', 'ai-awareness-day') . '</p></div>';
    }

    ob_start();
    ?>
    <div class="timeline-feed__body">
        <div class="timeline-feed__mobile">
            <?php echo aiad_render_timeline_swipe_deck($entries); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
        <div class="timeline-feed__desktop">
            <?php echo aiad_render_timeline_magazine($entries); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
