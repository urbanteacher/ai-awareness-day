<?php
/**
 * Live Timeline — Entry creation & disabled auto-generation hooks.
 *
 * Loaded by inc/timeline.php.
 *
 * @package AI_Awareness_Day
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ──────────────────────────────────────────────
   4. Auto-Generation Hooks
   ────────────────────────────────────────────── */

/**
 * Create an auto-generated timeline entry.
 * Prevents duplicates by checking for existing entries with the same related_id and auto_type.
 *
 * @param array $args {
 *     @type string $title      Entry title.
 *     @type string $content    Entry body (optional).
 *     @type string $auto_type  'resource', 'partner', 'submission', 'milestone'.
 *     @type string $icon       Icon key.
 *     @type int    $related_id Related post ID (0 if none).
 *     @type string $link_url   Optional CTA URL.
 *     @type string $link_label Optional CTA label.
 *     @type string $post_name  Optional slug (e.g. new-resource-added-{resource-slug}).
 * }
 * @return int|false Post ID on success, false on failure or duplicate.
 */
function aiad_create_timeline_entry(array $args)
{
    $defaults = array(
        'title' => '',
        'content' => '',
        'auto_type' => '',
        'icon' => 'announcement',
        'related_id' => 0,
        'link_url' => '',
        'link_label' => '',
        'countdown_weeks' => 0,
        'post_name' => '',
    );
    $args = wp_parse_args($args, $defaults);

    if (empty($args['title'])) {
        return false;
    }

    // Prevent duplicates: check if an auto entry with this related_id + auto_type already exists
    if ($args['related_id'] > 0 && $args['auto_type'] !== '') {
        $existing = get_posts(array(
            'post_type' => 'timeline',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_query' => array(
                'relation' => 'AND',
                array('key' => '_aiad_timeline_related_id', 'value' => $args['related_id'], 'compare' => '='),
                array('key' => '_aiad_timeline_auto_type', 'value' => $args['auto_type'], 'compare' => '='),
            ),
        ));
        if (!empty($existing)) {
            return false;
        }
    }

    $insert = array(
        'post_type' => 'timeline',
        'post_title' => sanitize_text_field($args['title']),
        'post_content' => wp_kses_post($args['content']),
        'post_status' => 'publish',
    );
    if (!empty($args['post_name'])) {
        $insert['post_name'] = sanitize_title($args['post_name']);
    }

    $post_id = wp_insert_post($insert);

    if (!$post_id || is_wp_error($post_id)) {
        return false;
    }

    update_post_meta($post_id, '_aiad_timeline_source', 'auto');
    update_post_meta($post_id, '_aiad_timeline_auto_type', sanitize_text_field($args['auto_type']));
    update_post_meta($post_id, '_aiad_timeline_related_id', absint($args['related_id']));
    update_post_meta($post_id, '_aiad_timeline_icon', sanitize_text_field($args['icon']));
    update_post_meta($post_id, '_aiad_timeline_pinned', false);

    if ($args['link_url']) {
        update_post_meta($post_id, '_aiad_timeline_link_url', esc_url_raw($args['link_url']));
    }
    if ($args['link_label']) {
        update_post_meta($post_id, '_aiad_timeline_link_label', sanitize_text_field($args['link_label']));
    }
    if (!empty($args['countdown_weeks'])) {
        update_post_meta($post_id, '_aiad_timeline_countdown_weeks', absint($args['countdown_weeks']));
    }

    return $post_id;
}

/**
 * Find an auto timeline entry linked to another post.
 *
 * @param int    $related_id Related post ID.
 * @param string $auto_type  Auto type key (e.g. live_session).
 * @return int Timeline post ID or 0.
 */
function aiad_timeline_get_entry_by_related(int $related_id, string $auto_type, ?array $post_statuses = null): int
{
    if ($related_id <= 0 || $auto_type === '') {
        return 0;
    }

    if (null === $post_statuses) {
        $post_statuses = array('publish', 'draft', 'pending', 'future');
    }

    $existing = get_posts(
        array(
            'post_type' => 'timeline',
            'post_status' => $post_statuses,
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_aiad_timeline_related_id',
                    'value' => $related_id,
                    'compare' => '=',
                ),
                array(
                    'key' => '_aiad_timeline_auto_type',
                    'value' => $auto_type,
                    'compare' => '=',
                ),
            ),
        )
    );

    return !empty($existing) ? (int) $existing[0] : 0;
}

/**
 * Auto-type meta key for a related resource, featured resource, or partner post.
 *
 * @param WP_Post $post Related post.
 * @return string Auto type slug or empty if unsupported.
 */
function aiad_timeline_related_auto_type(WP_Post $post): string
{
    if ('partner' === $post->post_type) {
        return 'partner';
    }
    if (in_array($post->post_type, array('resource', 'featured_resource'), true)) {
        return aiad_timeline_resource_announcement_auto_type($post);
    }
    return '';
}

/**
 * Re-publish a linked timeline entry when its related post is published again.
 *
 * @param WP_Post $post Related resource, featured resource, or partner.
 * @return bool True when an existing entry was restored to publish (skip creating a new one).
 */
function aiad_timeline_maybe_republish_related_entry(WP_Post $post): bool
{
    $auto_type = aiad_timeline_related_auto_type($post);
    if ($auto_type === '') {
        return false;
    }

    $timeline_id = aiad_timeline_get_entry_by_related($post->ID, $auto_type);
    if ($timeline_id <= 0) {
        $timeline_id = aiad_timeline_get_entry_by_related($post->ID, $auto_type, array('trash'));
    }

    if ($timeline_id <= 0) {
        return false;
    }

    $timeline = get_post($timeline_id);
    if (!$timeline instanceof WP_Post) {
        return false;
    }

    if ($timeline->post_status === 'publish') {
        return true;
    }

    if ($timeline->post_status === 'trash') {
        wp_untrash_post($timeline_id);
    }

    $updated = wp_update_post(
        array(
            'ID'          => $timeline_id,
            'post_status' => 'publish',
        ),
        true
    );

    return !is_wp_error($updated) && $updated > 0;
}

/**
 * Copy partner logo onto a timeline entry when it has no featured image.
 *
 * @param int $timeline_id Timeline post ID.
 * @param int $partner_id  Partner post ID.
 */
function aiad_timeline_set_featured_image_from_partner(int $timeline_id, int $partner_id): void
{
    if ($timeline_id <= 0 || $partner_id <= 0 || has_post_thumbnail($timeline_id)) {
        return;
    }

    $thumb_id = get_post_thumbnail_id($partner_id);
    if ($thumb_id) {
        set_post_thumbnail($timeline_id, $thumb_id);
        update_post_meta($timeline_id, '_aiad_timeline_cover_fit', 'contain');
    }
}

/**
 * Copy live session featured image (or partner logo fallback) onto a timeline entry.
 *
 * @param int $timeline_id Timeline post ID.
 * @param int $session_id  live_session post ID.
 * @param int $partner_id  Partner post ID (optional).
 */
function aiad_timeline_set_featured_image_from_session(int $timeline_id, int $session_id, int $partner_id = 0): void
{
    if ($timeline_id <= 0 || $session_id <= 0 || has_post_thumbnail($timeline_id)) {
        return;
    }

    $session_thumb = (int) get_post_thumbnail_id($session_id);
    if ($session_thumb > 0) {
        set_post_thumbnail($timeline_id, $session_thumb);
        delete_post_meta($timeline_id, '_aiad_timeline_cover_fit');
        return;
    }

    aiad_timeline_set_featured_image_from_partner($timeline_id, $partner_id);
}

/**
 * Create or update a timeline entry for a live_session (schedule) post.
 *
 * @param int $session_id live_session post ID.
 */
function aiad_timeline_sync_live_session_entry(int $session_id): void
{
    $session = get_post($session_id);
    if (!$session || $session->post_type !== 'live_session') {
        return;
    }

    $timeline_id = aiad_timeline_get_entry_by_related($session_id, 'live_session');

    if ($session->post_status !== 'publish') {
        if ($timeline_id) {
            wp_update_post(
                array(
                    'ID' => $timeline_id,
                    'post_status' => 'draft',
                )
            );
        }
        return;
    }

    $start = (string) get_post_meta($session_id, '_session_start_time', true);
    $end = (string) get_post_meta($session_id, '_session_end_time', true);
    $format = (string) get_post_meta($session_id, '_session_format', true);
    $reg_url = (string) get_post_meta($session_id, '_session_registration_url', true);
    $partner_id = (int) get_post_meta($session_id, '_session_partner_id', true);
    $time_range = function_exists('aiad_format_session_time_range')
        ? aiad_format_session_time_range($start, $end)
        : '';
    $content = trim((string) $session->post_content);
    $intro_parts = array();

    if ($time_range) {
        $intro_parts[] = sprintf(
            /* translators: %s: time range e.g. 10:00 – 11:00 */
            __('Live at %s (UK time).', 'ai-awareness-day'),
            $time_range
        );
    }
    if ($format) {
        $intro_parts[] = $format;
    }
    if (!empty($intro_parts)) {
        $intro = '<p>' . esc_html(implode(' ', $intro_parts)) . '</p>';
        $content = $content ? $intro . "\n\n" . $content : $intro;
    }

    $link_url = $reg_url ? $reg_url : (get_permalink($session_id) ?: '');
    $link_label = $reg_url
        ? __('Register →', 'ai-awareness-day')
        : __('View session →', 'ai-awareness-day');

    if ($timeline_id) {
        wp_update_post(
            array(
                'ID' => $timeline_id,
                'post_title' => $session->post_title,
                'post_content' => $content,
                'post_status' => 'publish',
            )
        );
        update_post_meta($timeline_id, '_aiad_timeline_icon', 'event');
        update_post_meta($timeline_id, '_aiad_timeline_source', 'auto');
        if ($link_url) {
            update_post_meta($timeline_id, '_aiad_timeline_link_url', esc_url_raw($link_url));
            update_post_meta($timeline_id, '_aiad_timeline_link_label', $link_label);
        }
        aiad_timeline_set_featured_image_from_session($timeline_id, $session_id, $partner_id);
        return;
    }

    $new_id = aiad_create_timeline_entry(
        array(
            'title' => $session->post_title,
            'content' => $content,
            'auto_type' => 'live_session',
            'icon' => 'event',
            'related_id' => $session_id,
            'link_url' => $link_url,
            'link_label' => $link_label,
        )
    );

    if ($new_id) {
        aiad_timeline_set_featured_image_from_session((int) $new_id, $session_id, $partner_id);
    }
}

/**
 * One-time: sync all published live sessions into timeline EVENT entries.
 */
function aiad_timeline_backfill_live_session_entries(): void
{
    if (get_option('aiad_timeline_live_sessions_synced_v1')) {
        return;
    }
    if (!function_exists('aiad_get_live_sessions')) {
        return;
    }

    foreach (aiad_get_live_sessions(-1) as $session) {
        aiad_timeline_sync_live_session_entry((int) $session->ID);
    }

    update_option('aiad_timeline_live_sessions_synced_v1', true);
}
// Automatic timeline syncing is disabled — timeline entries are created manually.
// add_action('init', 'aiad_timeline_backfill_live_session_entries', 35);

/**
 * Keep timeline EVENT cards in sync when a live session is saved.
 *
 * @param int $post_id Session post ID.
 */
function aiad_timeline_on_live_session_save(int $post_id): void
{
    if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }
    if (get_post_type($post_id) !== 'live_session') {
        return;
    }
    aiad_timeline_sync_live_session_entry($post_id);
}
// Automatic timeline syncing is disabled — timeline entries are created manually.
// add_action('save_post_live_session', 'aiad_timeline_on_live_session_save', 25);

/**
 * Event date for countdown (Y-m-d). Filter to override.
 *
 * @return string Date string.
 */
function aiad_timeline_event_date(): string
{
    $defaults = aiad_get_customizer_defaults();
    $date     = get_theme_mod( 'aiad_event_date_ymd', $defaults['aiad_event_date_ymd'] );
    if ( function_exists( 'aiad_sanitize_event_date_ymd' ) ) {
        $date = aiad_sanitize_event_date_ymd( $date );
    }
    return (string) apply_filters( 'aiad_timeline_event_date', $date );
}

/**
 * Days until the event (for countdown display). Returns 0 if event has passed.
 *
 * @return int
 */
function aiad_timeline_days_until_event(): int
{
    $event_date = aiad_timeline_event_date();
    $event_ts = strtotime($event_date . ' 00:00:00 UTC');
    if (!$event_ts || $event_ts <= time()) {
        return 0;
    }
    return (int) floor(($event_ts - time()) / 86400);
}

/**
 * Count of distinct schools registered (form submissions with school name). Cached for 1 hour.
 *
 * @return int
 */
function aiad_timeline_schools_registered_count(): int
{
    $cache_key = 'aiad_timeline_schools_count';
    $cached = get_transient($cache_key);
    if (false !== $cached && is_numeric($cached)) {
        return (int) $cached;
    }

    global $wpdb;

    // Count distinct school names from individual form submissions
    $form_count = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT pm.meta_value) FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE p.post_type = %s AND p.post_status = 'publish'
        AND pm.meta_key = %s AND TRIM(pm.meta_value) != ''",
        'form_submission',
        '_submission_school_name'
    ));

    // Sum schools-in-portfolio from all published partner posts
    $partner_count = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COALESCE(SUM(CAST(pm.meta_value AS UNSIGNED)), 0) FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE p.post_type = %s AND p.post_status = 'publish'
        AND pm.meta_key = %s AND pm.meta_value > 0",
        'partner',
        '_partner_school_count'
    ));

    $count = $form_count + $partner_count;
    set_transient($cache_key, $count, HOUR_IN_SECONDS);
    return $count;
}

/**
 * Maybe create countdown "weeks to go" timeline entries. Runs on daily cron.
 * Creates one entry per beat (12, 8, 6, 4, 2, 1 weeks) when we reach that threshold.
 */
function aiad_timeline_maybe_create_countdown_entries(): void
{
    $event_date = aiad_timeline_event_date();
    $event_ts = strtotime($event_date . ' 23:59:59');
    if (!$event_ts || $event_ts <= time()) {
        return;
    }
    $days_until = (int) floor(($event_ts - time()) / 86400);
    $weeks_until = (int) floor($days_until / 7);

    $beats = array(12, 8, 6, 4, 2, 1);

    // One query to fetch all existing countdown entries instead of one per beat.
    $existing_entries = get_posts(array(
        'post_type' => 'timeline',
        'post_status' => 'publish',
        'posts_per_page' => count($beats),
        'meta_query' => array(
            array('key' => '_aiad_timeline_auto_type', 'value' => 'countdown', 'compare' => '='),
        ),
    ));
    $existing_weeks = array();
    foreach ($existing_entries as $existing_post) {
        $w = (int) get_post_meta($existing_post->ID, '_aiad_timeline_countdown_weeks', true);
        if ($w) {
            $existing_weeks[] = $w;
        }
    }

    foreach ($beats as $weeks) {
        if ($weeks_until > $weeks) {
            continue;
        }
        if (in_array($weeks, $existing_weeks, true)) {
            continue;
        }
        $title = $weeks === 1
            ? __('1 week to go — book your slot', 'ai-awareness-day')
            : sprintf(__('%d weeks to go — book your slot', 'ai-awareness-day'), $weeks);
        aiad_create_timeline_entry(array(
            'title' => $title,
            'content' => sprintf(__('AI Awareness Day is in %d week(s). Sign up and get your school involved.', 'ai-awareness-day'), $weeks),
            'auto_type' => 'countdown',
            'icon' => 'signup',
            'link_url' => home_url('#contact'),
            'link_label' => __('Join the campaign →', 'ai-awareness-day'),
            'countdown_weeks' => $weeks,
        ));
    }
}

/**
 * Automatic countdown entries are disabled — timeline entries are created manually.
 * Clear any previously scheduled cron event so it no longer fires.
 */
function aiad_timeline_clear_countdown_cron(): void
{
    $timestamp = wp_next_scheduled('aiad_timeline_countdown_daily');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'aiad_timeline_countdown_daily');
    }
    wp_clear_scheduled_hook('aiad_timeline_countdown_daily');
    delete_transient('aiad_timeline_cron_scheduled');
}
add_action('init', 'aiad_timeline_clear_countdown_cron', 20);

/**
 * Auto-type meta value for a resource announcement timeline entry.
 */
function aiad_timeline_resource_announcement_auto_type(WP_Post $post): string
{
    return $post->post_type === 'featured_resource' ? 'featured_resource' : 'resource';
}

/**
 * Create a timeline announcement for a published resource or featured_resource.
 *
 * @return int|false Timeline post ID, false if skipped or failed.
 */
function aiad_timeline_maybe_create_resource_announcement(WP_Post $post)
{
    if (!in_array($post->post_type, array('resource', 'featured_resource'), true)) {
        return false;
    }
    if ($post->post_status !== 'publish') {
        return false;
    }

    $auto_type = aiad_timeline_resource_announcement_auto_type($post);
    if (aiad_timeline_get_entry_by_related($post->ID, $auto_type)) {
        return false;
    }

    $themes = get_the_terms($post->ID, 'resource_principle');
    $theme_name = $themes && !is_wp_error($themes) ? $themes[0]->name : '';
    $suffix = $theme_name ? sprintf(' (%s)', $theme_name) : '';

    $content = $post->post_excerpt;
    if (empty($content) && !empty($post->post_content)) {
        $content = wp_trim_words(wp_strip_all_tags($post->post_content), 55);
    }

    if ($post->post_type === 'featured_resource') {
        $link_url = get_post_meta($post->ID, '_featured_resource_url', true);
        $link_label = __('Try it now →', 'ai-awareness-day');
    } else {
        $link_url = get_permalink($post->ID);
        $link_label = __('View resource →', 'ai-awareness-day');
    }

    $timeline_slug = 'new-resource-added-' . $post->post_name;

    return aiad_create_timeline_entry(array(
        'title' => sprintf(
            /* translators: %s: resource title */
            __('New resource added: %s', 'ai-awareness-day'),
            $post->post_title . $suffix
        ),
        'content' => $content,
        'auto_type' => $auto_type,
        'icon' => 'resource',
        'related_id' => $post->ID,
        'link_url' => $link_url,
        'link_label' => $link_label,
        'post_name' => $timeline_slug,
    ));
}

/**
 * Auto-generate timeline entry when a resource or featured_resource is published.
 */
function aiad_timeline_on_resource_publish(string $new_status, string $old_status, WP_Post $post): void
{
    if (!in_array($post->post_type, array('resource', 'featured_resource'), true)) {
        return;
    }
    if ($new_status !== 'publish' || $old_status === 'publish') {
        return;
    }

    if (aiad_timeline_maybe_republish_related_entry($post)) {
        return;
    }

    aiad_timeline_maybe_create_resource_announcement($post);
}
// Automatic timeline announcements are disabled — timeline entries are created manually.
// add_action('transition_post_status', 'aiad_timeline_on_resource_publish', 10, 3);

/**
 * Backfill timeline announcements for resources published before the featured_resource hook existed.
 */
function aiad_timeline_backfill_resource_announcements(): void
{
    if (get_option('aiad_timeline_resource_announcements_backfilled') === 'yes') {
        return;
    }

    foreach (array('resource', 'featured_resource') as $post_type) {
        $ids = get_posts(
            array(
                'post_type' => $post_type,
                'post_status' => 'publish',
                'numberposts' => -1,
                'fields' => 'ids',
            )
        );
        foreach ($ids as $post_id) {
            $resource = get_post((int) $post_id);
            if ($resource instanceof WP_Post) {
                aiad_timeline_maybe_create_resource_announcement($resource);
            }
        }
    }

    update_option('aiad_timeline_resource_announcements_backfilled', 'yes');
}
// Automatic timeline backfill is disabled — timeline entries are created manually.
// add_action('init', 'aiad_timeline_backfill_resource_announcements', 31);

/**
 * Trash mistaken blog posts that duplicate resource timeline announcements; store 301 targets.
 */
function aiad_timeline_migrate_resource_announcement_blog_posts(): void
{
    if (get_option('aiad_timeline_resource_blog_migrated') === 'yes') {
        return;
    }

    $redirects = (array) get_option('aiad_timeline_blog_redirects', array());

    $blog_posts = get_posts(
        array(
            'post_type' => 'post',
            'post_status' => array('publish', 'draft', 'pending', 'future'),
            'numberposts' => -1,
        )
    );

    foreach ($blog_posts as $blog_post) {
        if (!$blog_post instanceof WP_Post) {
            continue;
        }

        $name = $blog_post->post_name;
        $is_announcement = (0 === strpos($name, 'new-resource-added-'))
            || (bool) preg_match('/^New [Rr]esource [Aa]dded:/', $blog_post->post_title);

        if (!$is_announcement) {
            continue;
        }

        $resource = null;
        $resource_title = preg_replace('/^New [Rr]esource [Aa]dded:\s*/i', '', $blog_post->post_title);
        $resource_title = trim((string) preg_replace('/\s*\([^)]+\)\s*$/', '', $resource_title));

        if ($resource_title && function_exists('aiad_get_post_by_title')) {
            $resource = aiad_get_post_by_title($resource_title, 'featured_resource');
            if (!$resource) {
                $resource = aiad_get_post_by_title($resource_title, 'resource');
            }
        }

        if (!$resource && 0 === strpos($name, 'new-resource-added-')) {
            $resource_slug = substr($name, strlen('new-resource-added-'));
            $resource = get_page_by_path($resource_slug, OBJECT, 'featured_resource');
            if (!$resource) {
                $resource = get_page_by_path($resource_slug, OBJECT, 'resource');
            }
        }

        $timeline_url = '';
        if ($resource instanceof WP_Post) {
            aiad_timeline_maybe_create_resource_announcement($resource);
            $auto_type = aiad_timeline_resource_announcement_auto_type($resource);
            $timeline_id = aiad_timeline_get_entry_by_related($resource->ID, $auto_type);
            if ($timeline_id > 0) {
                $timeline_url = get_permalink($timeline_id);
            }
        }

        if ($timeline_url) {
            $redirects[$name] = $timeline_url;
        }

        if ($blog_post->post_status === 'publish') {
            wp_trash_post((int) $blog_post->ID);
        }
    }

    update_option('aiad_timeline_blog_redirects', $redirects);
    update_option('aiad_timeline_resource_blog_migrated', 'yes');
}
add_action('init', 'aiad_timeline_migrate_resource_announcement_blog_posts', 32);

/**
 * Redirect old root-level resource announcement blog URLs to /timeline/...
 */
function aiad_timeline_redirect_resource_announcement_blog_urls(): void
{
    if (is_admin()) {
        return;
    }

    $uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
    $path = trim((string) wp_parse_url($uri, PHP_URL_PATH), '/');
    if ($path === '') {
        return;
    }

    $redirects = (array) get_option('aiad_timeline_blog_redirects', array());
    if (!empty($redirects[$path])) {
        wp_safe_redirect($redirects[$path], 301);
        exit;
    }

    if (0 !== strpos($path, 'new-resource-added-')) {
        return;
    }

    $timeline = get_page_by_path($path, OBJECT, 'timeline');
    if ($timeline instanceof WP_Post) {
        wp_safe_redirect(get_permalink($timeline), 301);
        exit;
    }
}
add_action('template_redirect', 'aiad_timeline_redirect_resource_announcement_blog_urls', 0);

/**
 * Create a timeline announcement for a published partner.
 *
 * @return int|false Timeline post ID, false if skipped or failed.
 */
function aiad_timeline_maybe_create_partner_announcement(WP_Post $post)
{
    if ($post->post_type !== 'partner' || $post->post_status !== 'publish') {
        return false;
    }

    if (aiad_timeline_get_entry_by_related($post->ID, 'partner')) {
        return false;
    }

    $types = get_the_terms($post->ID, 'partner_type');
    $type_name = $types && !is_wp_error($types) ? $types[0]->name : __('Partner', 'ai-awareness-day');

    return aiad_create_timeline_entry(array(
        'title' => sprintf(
            /* translators: 1: partner name, 2: partner type */
            __('%1$s joined as %2$s', 'ai-awareness-day'),
            $post->post_title,
            $type_name
        ),
        'auto_type' => 'partner',
        'icon' => 'partner',
        'related_id' => $post->ID,
    ));
}

/**
 * Auto-generate timeline entry when a partner is published.
 */
function aiad_timeline_on_partner_publish(string $new_status, string $old_status, WP_Post $post): void
{
    if ($post->post_type !== 'partner' || $new_status !== 'publish' || $old_status === 'publish') {
        return;
    }

    if (aiad_timeline_maybe_republish_related_entry($post)) {
        return;
    }

    aiad_timeline_maybe_create_partner_announcement($post);
}
// Automatic timeline announcements are disabled — timeline entries are created manually.
// add_action('transition_post_status', 'aiad_timeline_on_partner_publish', 10, 3);

/**
 * Invalidate schools count cache when a form submission or partner is saved.
 */
function aiad_timeline_invalidate_schools_count(): void
{
    delete_transient('aiad_timeline_schools_count');
}
add_action('save_post_form_submission', 'aiad_timeline_invalidate_schools_count', 5);
add_action('save_post_partner', 'aiad_timeline_invalidate_schools_count', 5);

/**
 * Sync timeline entry status when its related post is unpublished or trashed.
 */
function aiad_timeline_on_related_post_unpublished(string $new_status, string $old_status, WP_Post $post): void
{
    if (!in_array($post->post_type, array('resource', 'featured_resource', 'partner'), true)) {
        return;
    }
    if ($old_status !== 'publish' || $new_status === 'publish') {
        return;
    }

    $auto_type = aiad_timeline_related_auto_type($post);
    if ($auto_type === '') {
        return;
    }

    $timeline_id = aiad_timeline_get_entry_by_related($post->ID, $auto_type);
    if ($timeline_id > 0) {
        if ('trash' === $new_status) {
            wp_trash_post($timeline_id);
        } else {
            wp_update_post(array(
                'ID'          => $timeline_id,
                'post_status' => 'draft',
            ));
        }
    }
}
// Automatic timeline status syncing is disabled — timeline entries are managed manually.
// add_action('transition_post_status', 'aiad_timeline_on_related_post_unpublished', 10, 3);

