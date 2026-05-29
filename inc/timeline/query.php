<?php
/**
 * Live Timeline — Query helpers.
 *
 * Loaded by inc/timeline.php.
 *
 * @package AI_Awareness_Day
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ──────────────────────────────────────────────
   5. Query Helpers
   ────────────────────────────────────────────── */

/**
 * Get timeline entries for display.
 * Pinned entries first (by date), then remaining entries by date.
 *
 * @param int    $per_page Number of entries to return.
 * @param int    $offset   Offset for pagination (excludes pinned on page > 1).
 * @param string $filter   Filter by icon type (optional).
 * @return array{ entries: WP_Post[], has_more: bool }
 */
/**
 * Max supporting cards under the magazine hero (desktop).
 */
function aiad_timeline_magazine_sub_count(): int
{
    return max(0, (int) apply_filters('aiad_timeline_magazine_sub_count', 4));
}

function aiad_timeline_feed_per_page(): int
{
    /**
     * Front-page feed size. -1 = every published entry (full mobile swipe deck).
     * Positive values cap total entries (e.g. 5 = 1 hero + 4 magazine subs on desktop).
     */
    return (int) apply_filters('aiad_timeline_feed_per_page', -1);
}

/**
 * Entries per page on the /timeline/ archive.
 */
function aiad_timeline_archive_per_page(): int
{
    return max(1, (int) apply_filters('aiad_timeline_archive_per_page', 12));
}

/**
 * Query timeline posts for the archive (paginated, optional icon filter).
 *
 * @param int    $paged  Current page (1-based).
 * @param string $filter Icon key or empty / all.
 * @return array{ entries: WP_Post[], max_pages: int, found: int }
 */
function aiad_get_timeline_archive_entries(int $paged = 1, string $filter = ''): array
{
    $per_page = aiad_timeline_archive_per_page();
    $args = array(
        'post_type' => 'timeline',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'paged' => max(1, $paged),
        'orderby' => 'date',
        'order' => 'DESC',
    );

    if (!empty($filter) && 'all' !== $filter) {
        $args['meta_query'] = array(
            array(
                'key' => '_aiad_timeline_icon',
                'value' => $filter,
                'compare' => '=',
            ),
        );
    }

    $query = new WP_Query($args);

    return array(
        'entries' => $query->posts,
        'max_pages' => (int) $query->max_num_pages,
        'found' => (int) $query->found_posts,
    );
}

function aiad_get_timeline_entries(int $per_page = 4, int $offset = 0, string $filter = ''): array
{
    $entries = array();

    // Build meta query for filtering
    $meta_query = array();
    if (!empty($filter) && $filter !== 'all') {
        $meta_query = array(
            array('key' => '_aiad_timeline_icon', 'value' => $filter, 'compare' => '='),
        );
    }

    // Full feed (mobile swipe scrolls every entry).
    if ($per_page < 0) {
        if ($offset > 0) {
            return array('entries' => array(), 'has_more' => false);
        }

        $pinned_args = array(
            'post_type' => 'timeline',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array(
                array('key' => '_aiad_timeline_pinned', 'value' => '1', 'compare' => '='),
            ),
        );
        if (!empty($meta_query)) {
            $pinned_args['meta_query'][] = $meta_query[0];
        }
        $pinned = get_posts($pinned_args);

        $rest_args = array(
            'post_type' => 'timeline',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'post__not_in' => wp_list_pluck($pinned, 'ID'),
        );
        if (!empty($meta_query)) {
            $rest_args['meta_query'] = $meta_query;
        }
        $rest = get_posts($rest_args);

        return array(
            'entries' => array_merge($pinned, $rest),
            'has_more' => false,
        );
    }

    // First page: get pinned entries first
    if (0 === $offset) {
        $pinned_args = array(
            'post_type' => 'timeline',
            'post_status' => 'publish',
            'posts_per_page' => 3, // Max 3 pinned items
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array(
                array('key' => '_aiad_timeline_pinned', 'value' => '1', 'compare' => '='),
            ),
        );
        if (!empty($meta_query)) {
            $pinned_args['meta_query'][] = $meta_query;
        }
        $pinned = get_posts($pinned_args);
        $entries = $pinned;
    }

    // Fill remaining slots with non-pinned (or all if offset > 0)
    $remaining = $per_page - count($entries);
    if ($remaining > 0) {
        $exclude_ids = wp_list_pluck($entries, 'ID');
        $args = array(
            'post_type' => 'timeline',
            'post_status' => 'publish',
            'posts_per_page' => $remaining + 1, // +1 to check if more exist
            'offset' => $offset > 0 ? $offset : 0,
            'orderby' => 'date',
            'order' => 'DESC',
            'post__not_in' => $exclude_ids,
        );

        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }

        $more_entries = get_posts($args);
        $has_more = count($more_entries) > $remaining;
        $entries = array_merge($entries, array_slice($more_entries, 0, $remaining));

        return array('entries' => $entries, 'has_more' => $has_more);
    }

    // Pinned entries fill the page; check if any non-pinned entries exist before claiming more.
    $check = get_posts(array(
        'post_type' => 'timeline',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'post__not_in' => wp_list_pluck($entries, 'ID'),
        'fields' => 'ids',
    ));
    return array('entries' => $entries, 'has_more' => !empty($check));
}

/**
 * Allowed HTML for oEmbed video output (iframe). wp_kses_post() strips iframes; timeline needs them for YouTube/Vimeo.
 *
 * @return array<string, array<string, bool>> Allowed HTML for wp_kses.
 */
function aiad_timeline_oembed_allowed_html(): array
{
    return array(
        'iframe' => array(
            'src' => true,
            'width' => true,
            'height' => true,
            'frameborder' => true,
            'allowfullscreen' => true,
            'allow' => true,
            'title' => true,
            'loading' => true,
            'referrerpolicy' => true,
        ),
    );
}

