<?php
/**
 * Tracking beacons: the REST routes that count resource downloads and views, and clicks, shares and views on other
 * content.
 *
 * These were admin-ajax handlers that checked a nonce printed into the page. HTML that is cached outlives a nonce, and
 * a counter is not something to protect with one anyway (the nonce was given to every visitor), so they are public
 * routes with the throttles the handlers had. The page script (assets/js/tracking.js) finds them through the REST
 * discovery link and sends them with navigator.sendBeacon().
 *
 * @see https://developer.wordpress.org/rest-api/using-the-rest-api/discovery/
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * POST aiad/v1/track/resource/{id}/{view|download}: count a resource page view or file download.
 *
 * A view counts once per address per resource per day, a download once per address per resource per 90 seconds. A
 * repeat is not an error: the response carries the current count either way.
 *
 * @param WP_REST_Request $request Request: `id` and `event`.
 * @return WP_REST_Response|WP_Error
 */
function aiad_rest_track_resource( WP_REST_Request $request ) {
	$post_id = absint( $request['id'] );
	if ( ! $post_id || 'resource' !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {
		return new WP_Error( 'aiad_invalid_resource', __( 'Invalid resource.', 'ai-awareness-day' ), array( 'status' => 404 ) );
	}

	$is_view  = 'view' === $request['event'];
	$meta_key = $is_view ? '_aiad_view_count' : '_aiad_download_count';
	$throttle = ( $is_view ? 'aiad_rv_' : 'aiad_dl_' ) . md5( aiad_get_client_ip() . (string) $post_id );

	$count = absint( get_post_meta( $post_id, $meta_key, true ) );
	if ( get_transient( $throttle ) ) {
		return new WP_REST_Response( array( 'count' => $count ) );
	}

	set_transient( $throttle, 1, $is_view ? DAY_IN_SECONDS : 90 );
	++$count;
	update_post_meta( $post_id, $meta_key, $count );

	return new WP_REST_Response( array( 'count' => $count ) );
}

/**
 * POST aiad/v1/track/engagement: count a click, share, join or view on content that has engagement counters.
 *
 * @param WP_REST_Request $request Request: `event`, `post_id` and, for a click, `target_url`.
 * @return WP_REST_Response|WP_Error
 */
function aiad_rest_track_engagement( WP_REST_Request $request ) {
	$post_id = absint( $request['post_id'] );
	$event   = sanitize_key( (string) $request['event'] );

	if ( 'hero_partners_stat' === $event ) {
		$count = (int) get_option( 'aiad_hero_partners_stat_clicks', 0 );
		++$count;
		update_option( 'aiad_hero_partners_stat_clicks', $count, false );
		return new WP_REST_Response( array( 'count' => $count ) );
	}

	if ( ! $post_id || ! aiad_engagement_is_trackable_post( $post_id, $event ) ) {
		return new WP_Error( 'aiad_invalid_content', __( 'Invalid content.', 'ai-awareness-day' ), array( 'status' => 404 ) );
	}

	$post     = get_post( $post_id );
	$meta_key = $post ? aiad_engagement_event_meta_key( $post->post_type, $event ) : null;
	if ( ! $meta_key ) {
		return new WP_Error( 'aiad_invalid_event', __( 'Invalid event.', 'ai-awareness-day' ), array( 'status' => 400 ) );
	}

	if ( 'view' === $event ) {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		if ( '' !== $ip ) {
			$key = 'aiad_engagement_viewed_' . md5( $ip . $post_id . $event );
			if ( get_transient( $key ) ) {
				return new WP_REST_Response(
					array(
						'count'   => (int) get_post_meta( $post_id, $meta_key, true ),
						'skipped' => true,
					)
				);
			}
			set_transient( $key, true, 6 * HOUR_IN_SECONDS );
		}
	}

	$count = aiad_increment_engagement_meta( $post_id, $meta_key );

	// Clicks on outbound links: also credit the destination article when it is on this site.
	$target_url = (string) $request['target_url'];
	if ( 'click' === $event && '' !== $target_url && $post && in_array( $post->post_type, aiad_engagement_post_types(), true ) ) {
		$target_id = aiad_engagement_post_id_from_url( $target_url );
		if ( $target_id > 0 && $target_id !== $post_id && aiad_engagement_is_trackable_post( $target_id, 'click' ) ) {
			$target_key = aiad_engagement_event_meta_key( get_post_type( $target_id ), 'click' );
			if ( $target_key ) {
				aiad_increment_engagement_meta( $target_id, $target_key );
			}
		}
	}

	return new WP_REST_Response( array( 'count' => $count ) );
}

/**
 * Register the routes.
 */
function aiad_register_tracking_rest_routes(): void {
	register_rest_route(
		'aiad/v1',
		'/track/resource/(?P<id>\d+)/(?P<event>view|download)',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'aiad_rest_track_resource',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'aiad/v1',
		'/track/engagement',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'aiad_rest_track_engagement',
			'permission_callback' => '__return_true',
			'args'                => array(
				'event'      => array(
					'type'     => 'string',
					'required' => true,
				),
				'post_id'    => array(
					'type'    => 'integer',
					'default' => 0,
				),
				'target_url' => array(
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'esc_url_raw',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'aiad_register_tracking_rest_routes' );
