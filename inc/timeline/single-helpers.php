<?php
/**
 * Live Timeline — Single template helpers.
 *
 * Loaded by inc/timeline.php.
 *
 * @package AI_Awareness_Day
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ──────────────────────────────────────────────
   Single timeline template helpers (single-timeline.php)
   ────────────────────────────────────────────── */

/**
 * Human-readable relative date (e.g. "1 day ago") for timeline singles.
 *
 * @param int|WP_Post|null $post Post ID or object.
 */
function aiad_timeline_human_date_label($post = null): string
{
    $post = get_post($post);
    if (!$post) {
        return '';
    }

    $timestamp = get_post_timestamp($post);
    if (!$timestamp) {
        return '';
    }

    return sprintf(
        /* translators: %s: human time diff e.g. "3 hours", "1 day" */
        __('%s ago', 'ai-awareness-day'),
        human_time_diff($timestamp, time())
    );
}

/**
 * Author avatar for stacked timeline single (site logo in pointed chamfer frame).
 *
 * @param int $user_id Author user ID (fallback when no brand logo).
 * @param int $size    Image size in pixels.
 */
function aiad_timeline_single_author_avatar_html(int $user_id, int $size = 44): string
{
    $class = 'single-timeline-entry__author-avatar';
    $wrap  = 'single-timeline-entry__author-avatar-wrap';

    $logo_id = function_exists('aiad_get_brand_logo_attachment_id')
        ? aiad_get_brand_logo_attachment_id()
        : 0;

    if ($logo_id) {
        $img = wp_get_attachment_image(
            $logo_id,
            array($size, $size),
            false,
            array(
                'class'    => $class,
                'alt'      => '',
                'loading'  => 'lazy',
                'decoding' => 'async',
            )
        );
        if ($img) {
            return '<span class="' . esc_attr($wrap) . '">' . $img . '</span>';
        }
    }

    $default = function_exists('aiad_get_default_avatar_url') ? aiad_get_default_avatar_url() : '';

    return '<span class="' . esc_attr($wrap) . '">' . get_avatar(
        $user_id,
        $size,
        $default,
        '',
        array('class' => $class)
    ) . '</span>';
}

/**
 * Move the hashtag paragraph out of the body so it can sit after “More to read”.
 *
 * @return array{body: string, tags_html: string}
 */
function aiad_timeline_single_split_tags_from_content($html): array
{
    $tags_html = '';
    $body      = is_string($html) ? $html : '';

    if ('' === $body) {
        return array(
            'body'      => '',
            'tags_html' => '',
        );
    }

    if (preg_match('#<p[^>]*\bentry-content__tags\b[^>]*>.*?</p>#is', $body, $matches)) {
        $tags_html = $matches[0];
        $body      = (string) preg_replace('#<p[^>]*\bentry-content__tags\b[^>]*>.*?</p>#is', '', $body, 1);
    } else {
        $paragraphs = array();
        if (preg_match_all('#<p[^>]*>(.*?)</p>#is', $body, $paragraphs, PREG_SET_ORDER) && ! empty($paragraphs)) {
            $last = $paragraphs[ count($paragraphs) - 1 ];
            $last_text  = trim(wp_strip_all_tags($last[1]));
            $tag_match  = $last_text !== '' ? preg_match('/^(?:#\w[\w-]*\s*)+$/u', $last_text) : 0;
            if (1 === $tag_match) {
                $tags_html = $last[0];
                $body      = (string) preg_replace('#' . preg_quote($last[0], '#') . '\s*$#s', '', $body, 1);
            }
        }
    }

    return array(
        'body'      => trim($body),
        'tags_html' => $tags_html,
    );
}

/**
 * Echo tags block at end of stacked single (after more-to-read, before footer).
 */
function aiad_timeline_single_render_tags($tags_html): void
{
    $tags_html = is_string($tags_html) ? $tags_html : '';
    if ('' === trim($tags_html)) {
        return;
    }

    if (false === strpos($tags_html, 'single-timeline-entry__tags')) {
        $tags_html = str_replace(
            'entry-content__tags',
            'entry-content__tags single-timeline-entry__tags',
            $tags_html
        );
    }

    echo wp_kses_post($tags_html);
}

/**
 * Related timeline entries markup for single template.
 */
function aiad_timeline_single_render_related(int $post_id): void
{
    $related_query = new WP_Query(
        array(
            'post_type'              => 'timeline',
            'posts_per_page'         => 3,
            'post__not_in'           => array($post_id),
            'orderby'                => 'date',
            'order'                  => 'DESC',
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        )
    );

    if (!$related_query->have_posts()) {
        return;
    }
    ?>
    <section class="single-timeline-entry__related" aria-labelledby="related-heading-timeline">
        <h2 id="related-heading-timeline" class="single-timeline-entry__related-title">
            <?php esc_html_e('Suggested Readings', 'ai-awareness-day'); ?>
        </h2>
        <ul class="single-timeline-entry__related-list">
            <?php
            while ($related_query->have_posts()) :
                $related_query->the_post();
                ?>
                <li>
                    <a href="<?php the_permalink(); ?>" class="single-timeline-entry__related-link">
                        <?php
                        if (has_post_thumbnail()) {
                            the_post_thumbnail(
                                'thumbnail',
                                array(
                                    'class' => 'single-timeline-entry__related-thumb',
                                    'alt'   => '',
                                )
                            );
                        }
                        ?>
                        <div class="single-timeline-entry__related-meta">
                            <p class="single-timeline-entry__related-date">
                                <?php
                                echo esc_html(
                                    function_exists('aiad_timeline_human_date_label')
                                        ? aiad_timeline_human_date_label(get_the_ID())
                                        : get_the_date('j F Y')
                                );
                                ?>
                            </p>
                            <p class="single-timeline-entry__related-headline">
                                <?php the_title(); ?>
                            </p>
                        </div>
                    </a>
                </li>
                <?php
            endwhile;
            wp_reset_postdata();
            ?>
        </ul>
    </section>
    <?php
}
