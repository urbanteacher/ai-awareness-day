<?php
/**
 * Admin AJAX: fetch a card image from LoremFlickr and set it as the post's featured image.
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
 * AJAX: fetch a card image from LoremFlickr using admin-supplied keywords,
 * sideload it into the media library, and set it as the post's featured image.
 */
function aiad_fetch_card_image(): void {
    check_ajax_referer( 'aiad_fetch_card_image', 'nonce' );

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( 'Unauthorised' );
    }

    $post_id  = absint( $_POST['post_id'] ?? 0 );
    $keywords = sanitize_text_field( wp_unslash( $_POST['keywords'] ?? '' ) );

    if ( ! $post_id || ! $keywords ) {
        wp_send_json_error( 'Missing post ID or keywords.' );
    }

    // Build LoremFlickr URL — comma-separate keywords, random seed busts cache
    $kw_slug = implode( ',', array_map( 'trim', explode( ',', $keywords ) ) );
    $img_url = 'https://loremflickr.com/640/640/' . rawurlencode( $kw_slug ) . '?lock=' . wp_rand( 1, 99999 );

    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // download_url follows redirects and saves to a temp file — avoids the
    // extension-check that media_sideload_image applies to the URL string.
    $tmp = download_url( $img_url, 30 );
    if ( is_wp_error( $tmp ) ) {
        wp_send_json_error( 'Download failed: ' . $tmp->get_error_message() );
    }

    $file_array = array(
        'name'     => sanitize_title( $keywords ) . '.jpg',
        'tmp_name' => $tmp,
    );

    $attachment_id = media_handle_sideload( $file_array, $post_id, $keywords );

    // Clean up temp file if sideload failed
    if ( is_wp_error( $attachment_id ) ) {
        @unlink( $tmp );
        wp_send_json_error( $attachment_id->get_error_message() );
    }

    set_post_thumbnail( $post_id, $attachment_id );

    wp_send_json_success( array(
        'attachment_id' => $attachment_id,
        'thumb_url'     => get_the_post_thumbnail_url( $post_id, 'medium' ),
    ) );
}
add_action( 'wp_ajax_aiad_fetch_card_image', 'aiad_fetch_card_image' );
