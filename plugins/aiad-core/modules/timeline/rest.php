<?php
/**
 * Live Timeline: the REST routes behind the filter pills and the like button.
 *
 * Loaded by modules/timeline.php. These were admin-ajax handlers that checked a nonce printed into the page. A nonce
 * in cached HTML goes stale, and both calls are for anyone, so they are public routes: nothing here depends on who
 * asks, and the like keeps its own limit (one per visitor address per entry per day).
 *
 * The browser finds the routes through the REST discovery link in the page head (assets/js/timeline.js).
 *
 * @see https://developer.wordpress.org/rest-api/using-the-rest-api/discovery/
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GET aiad/v1/timeline: the feed for a topic filter, as HTML for the page to swap in.
 *
 * @param WP_REST_Request $request Request: `filter` (a topic slug or "all") and `archive` (the /timeline/ page).
 * @return WP_REST_Response
 */
function aiad_rest_timeline_feed( WP_REST_Request $request ): WP_REST_Response {
	$filter = (string) $request->get_param( 'filter' );

	if ( $request->get_param( 'archive' ) ) {
		$result = aiad_get_timeline_archive_entries( 1, $filter );
		$html   = aiad_render_timeline_archive_feed( $result['entries'] );
	} else {
		$result = aiad_get_timeline_entries( aiad_timeline_feed_per_page(), 0, $filter );
		$html   = aiad_render_timeline_feed_layouts( $result['entries'] );
	}

	return new WP_REST_Response(
		array(
			'html'  => $html,
			'count' => count( $result['entries'] ),
		)
	);
}

/**
 * POST aiad/v1/timeline/{id}/like: add one like to an entry. A visitor address can like an entry once a day.
 *
 * @param WP_REST_Request $request Request: `id`.
 * @return WP_REST_Response|WP_Error
 */
function aiad_rest_timeline_like( WP_REST_Request $request ) {
	$entry_id = absint( $request['id'] );
	$post     = $entry_id ? get_post( $entry_id ) : null;
	if ( ! $post || 'timeline' !== $post->post_type || 'publish' !== $post->post_status ) {
		return new WP_Error( 'aiad_invalid_entry', __( 'Invalid entry.', 'ai-awareness-day' ), array( 'status' => 404 ) );
	}

	$ip_address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	if ( '' === $ip_address ) {
		return new WP_Error( 'aiad_unverified', __( 'Unable to verify request.', 'ai-awareness-day' ), array( 'status' => 400 ) );
	}

	$count          = (int) get_post_meta( $entry_id, '_aiad_timeline_like_count', true );
	$rate_limit_key = 'aiad_timeline_liked_' . md5( $ip_address . $entry_id );
	if ( get_transient( $rate_limit_key ) ) {
		return new WP_Error(
			'aiad_already_liked',
			__( 'You have already liked this entry.', 'ai-awareness-day' ),
			array(
				'status' => 429,
				'count'  => $count,
			)
		);
	}

	++$count;
	update_post_meta( $entry_id, '_aiad_timeline_like_count', $count );
	set_transient( $rate_limit_key, true, DAY_IN_SECONDS );

	return new WP_REST_Response( array( 'count' => $count ) );
}

/**
 * Register the routes.
 */
function aiad_register_timeline_rest_routes(): void {
	register_rest_route(
		'aiad/v1',
		'/timeline',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'aiad_rest_timeline_feed',
			'permission_callback' => '__return_true',
			'args'                => array(
				'filter'  => array(
					'type'              => 'string',
					'default'           => 'all',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'archive' => array(
					'type'    => 'boolean',
					'default' => false,
				),
			),
		)
	);

	register_rest_route(
		'aiad/v1',
		'/timeline/(?P<id>\d+)/like',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'aiad_rest_timeline_like',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'aiad_register_timeline_rest_routes' );
