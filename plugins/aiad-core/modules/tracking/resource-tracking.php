<?php
/**
 * Resource download and view counters (AJAX).
 *
 * Moved from the theme's inc/ajax-handlers.php. The theme loads this file from its bundled copy of the plugin when
 * the plugin isn't active, so this is the only copy.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX: Track resource download
 * 
 * Increments download count for a resource. Uses nonce verification for security.
 * Available to both authenticated and unauthenticated users (stats tracking).
 */
function aiad_track_download(): void {
    // Verify nonce for CSRF protection
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'aiad_track_download_nonce' ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh the page and try again.', 'ai-awareness-day' ) ) );
    }

    $post_id = absint( $_POST['post_id'] ?? 0 );
    if ( ! $post_id || get_post_type( $post_id ) !== 'resource' ) {
        wp_send_json_error( array( 'message' => __( 'Invalid resource.', 'ai-awareness-day' ) ) );
    }

    // Throttle: one count per IP per resource per 90 seconds
    $ip         = aiad_get_client_ip();
    $throttle_key = 'aiad_dl_' . md5( $ip . (string) $post_id );
    if ( get_transient( $throttle_key ) ) {
        $count = absint( get_post_meta( $post_id, '_aiad_download_count', true ) );
        wp_send_json_success( array( 'count' => $count ) );
    }

    set_transient( $throttle_key, 1, 90 );
    $count = absint( get_post_meta( $post_id, '_aiad_download_count', true ) );
    $count++;
    update_post_meta( $post_id, '_aiad_download_count', $count );

    wp_send_json_success( array( 'count' => $count ) );
}
add_action( 'wp_ajax_aiad_track_download', 'aiad_track_download' );
add_action( 'wp_ajax_nopriv_aiad_track_download', 'aiad_track_download' );

/**
 * AJAX: Track resource page view
 *
 * Increments view count when a resource page is visited.
 * Throttled to one count per IP per resource per 24 hours.
 */
function aiad_track_resource_view(): void {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'aiad_track_view_nonce' ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'ai-awareness-day' ) ) );
    }

    $post_id = absint( $_POST['post_id'] ?? 0 );
    if ( ! $post_id || get_post_type( $post_id ) !== 'resource' ) {
        wp_send_json_error( array( 'message' => __( 'Invalid resource.', 'ai-awareness-day' ) ) );
    }

    $ip           = aiad_get_client_ip();
    $throttle_key = 'aiad_rv_' . md5( $ip . (string) $post_id );
    if ( get_transient( $throttle_key ) ) {
        $count = absint( get_post_meta( $post_id, '_aiad_view_count', true ) );
        wp_send_json_success( array( 'count' => $count ) );
    }

    set_transient( $throttle_key, 1, DAY_IN_SECONDS );
    $count = absint( get_post_meta( $post_id, '_aiad_view_count', true ) );
    $count++;
    update_post_meta( $post_id, '_aiad_view_count', $count );

    wp_send_json_success( array( 'count' => $count ) );
}
add_action( 'wp_ajax_aiad_track_resource_view', 'aiad_track_resource_view' );
add_action( 'wp_ajax_nopriv_aiad_track_resource_view', 'aiad_track_resource_view' );
