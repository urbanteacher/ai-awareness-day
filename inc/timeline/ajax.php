<?php
/**
 * Live Timeline — AJAX: filter & like handlers.
 *
 * Loaded by inc/timeline.php.
 *
 * @package AI_Awareness_Day
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ──────────────────────────────────────────────
   7. AJAX: Filter
   ────────────────────────────────────────────── */

function aiad_ajax_timeline_filter(): void
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'aiad_timeline_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'ai-awareness-day')));
    }

    $filter = isset($_POST['filter']) ? sanitize_text_field(wp_unslash($_POST['filter'])) : 'all';
    $archive = !empty($_POST['archive']);

    if ($archive) {
        $result = aiad_get_timeline_archive_entries(1, $filter);
        $html = aiad_render_timeline_archive_feed($result['entries']);
    } else {
        $per_page = aiad_timeline_feed_per_page();
        $result = aiad_get_timeline_entries($per_page, 0, $filter);
        $html = aiad_render_timeline_feed_layouts($result['entries']);
    }

    wp_send_json_success(array(
        'html' => $html,
        'count' => count($result['entries']),
    ));
}
add_action('wp_ajax_aiad_timeline_filter', 'aiad_ajax_timeline_filter');
add_action('wp_ajax_nopriv_aiad_timeline_filter', 'aiad_ajax_timeline_filter');

/* ──────────────────────────────────────────────
   7. AJAX: Like
   ────────────────────────────────────────────── */

/**
 * Increment like count for a timeline entry.
 * Rate limited: prevents the same visitor (by IP) from liking an entry more than once per 24 hours.
 */
function aiad_ajax_timeline_like(): void
{
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'aiad_timeline_nonce')) {
        wp_send_json_error(array('message' => __('Security check failed.', 'ai-awareness-day')));
    }

    $entry_id = isset($_POST['entry_id']) ? absint($_POST['entry_id']) : 0;
    if (!$entry_id) {
        wp_send_json_error(array('message' => __('Invalid entry.', 'ai-awareness-day')));
    }

    $post = get_post($entry_id);
    if (!$post || $post->post_type !== 'timeline') {
        wp_send_json_error(array('message' => __('Invalid entry.', 'ai-awareness-day')));
    }

    // Rate limiting: check if this IP has already liked this entry
    $ip_address = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
    if (empty($ip_address)) {
        wp_send_json_error(array('message' => __('Unable to verify request.', 'ai-awareness-day')));
    }

    $rate_limit_key = 'aiad_timeline_liked_' . md5($ip_address . $entry_id);
    if (get_transient($rate_limit_key)) {
        // Already liked within the last 24 hours
        $current_count = (int) get_post_meta($entry_id, '_aiad_timeline_like_count', true);
        wp_send_json_error(array(
            'message' => __('You have already liked this entry.', 'ai-awareness-day'),
            'count' => $current_count,
        ));
    }

    // Increment like count
    $count = (int) get_post_meta($entry_id, '_aiad_timeline_like_count', true);
    $count++;
    update_post_meta($entry_id, '_aiad_timeline_like_count', $count);

    // Set rate limit transient (24 hours)
    set_transient($rate_limit_key, true, DAY_IN_SECONDS);

    wp_send_json_success(array('count' => $count));
}
add_action('wp_ajax_aiad_timeline_like', 'aiad_ajax_timeline_like');
add_action('wp_ajax_nopriv_aiad_timeline_like', 'aiad_ajax_timeline_like');

