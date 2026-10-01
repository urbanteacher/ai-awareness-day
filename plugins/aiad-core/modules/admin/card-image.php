<?php
/**
 * Admin: fetch a card image from LoremFlickr and set it as the post's featured image.
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
 * POST aiad/v1/card-image/{id}: fetch a card image from LoremFlickr using the editor's keywords, sideload it into the
 * media library and set it as the post's featured image.
 *
 * It was an admin-ajax handler behind a nonce. The REST route checks that the person can edit this post, and the
 * block editor and the classic screen's script send the REST nonce through wp.apiFetch.
 *
 * @param WP_REST_Request $request Request: `id` and `keywords`.
 * @return WP_REST_Response|WP_Error
 */
function aiad_rest_fetch_card_image( WP_REST_Request $request ) {
    $post_id  = absint( $request['id'] );
    $keywords = sanitize_text_field( (string) $request->get_param( 'keywords' ) );

    if ( ! $post_id || ! $keywords ) {
        return new WP_Error( 'aiad_missing', __( 'Missing post ID or keywords.', 'ai-awareness-day' ), array( 'status' => 400 ) );
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
        return new WP_Error( 'aiad_download', 'Download failed: ' . $tmp->get_error_message(), array( 'status' => 502 ) );
    }

    $file_array = array(
        'name'     => sanitize_title( $keywords ) . '.jpg',
        'tmp_name' => $tmp,
    );

    $attachment_id = media_handle_sideload( $file_array, $post_id, $keywords );

    // Clean up temp file if sideload failed
    if ( is_wp_error( $attachment_id ) ) {
        @unlink( $tmp );
        return new WP_Error( 'aiad_sideload', $attachment_id->get_error_message(), array( 'status' => 500 ) );
    }

    set_post_thumbnail( $post_id, $attachment_id );

    return new WP_REST_Response(
        array(
            'attachment_id' => $attachment_id,
            'thumb_url'     => get_the_post_thumbnail_url( $post_id, 'medium' ),
        )
    );
}

/**
 * Register the route.
 */
function aiad_register_card_image_rest_route(): void {
    register_rest_route(
        'aiad/v1',
        '/card-image/(?P<id>\d+)',
        array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'aiad_rest_fetch_card_image',
            'permission_callback' => static function ( WP_REST_Request $request ): bool {
                return current_user_can( 'edit_post', absint( $request['id'] ) );
            },
        )
    );
}
add_action( 'rest_api_init', 'aiad_register_card_image_rest_route' );
