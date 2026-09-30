<?php
/**
 * REST API for certificate demo: brand logo + form submissions.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether certificate read endpoints are allowed (local demo or editors).
 */
function aiad_certificate_api_can_read(): bool {
	if ( function_exists( 'wp_get_environment_type' ) ) {
		$env = wp_get_environment_type();
		if ( in_array( $env, array( 'local', 'development' ), true ) ) {
			return true;
		}
	}
	return current_user_can( 'edit_posts' );
}

/**
 * Resolve a school logo URL from partner posts (School type) by name.
 *
 * @param string $school_name School or organisation name from submission.
 * @return string Image URL or empty.
 */
function aiad_certificate_resolve_school_logo_url( string $school_name ): string {
	$school_name = trim( $school_name );
	if ( $school_name === '' ) {
		return '';
	}

	$school_term = get_term_by( 'name', 'School', 'partner_type' );
	$term_ids    = array();
	if ( $school_term && ! is_wp_error( $school_term ) ) {
		$term_ids[] = (int) $school_term->term_id;
	}

	// Exact title match first.
	$exact = get_page_by_title( $school_name, OBJECT, 'partner' );
	if ( $exact instanceof WP_Post ) {
		$url = get_the_post_thumbnail_url( $exact, 'medium' );
		if ( $url ) {
			return (string) $url;
		}
	}

	$query_args = array(
		'post_type'              => 'partner',
		'post_status'            => 'publish',
		'posts_per_page'         => 1,
		'orderby'                => 'title',
		'order'                  => 'ASC',
		's'                      => $school_name,
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	);

	if ( $term_ids ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy' => 'partner_type',
				'field'    => 'term_id',
				'terms'    => $term_ids,
			),
		);
	}

	$search = new WP_Query( $query_args );
	if ( $search->have_posts() ) {
		$url = get_the_post_thumbnail_url( $search->posts[0], 'medium' );
		if ( $url ) {
			return (string) $url;
		}
	}

	return '';
}

/**
 * Build certificate-ready row from a form_submission post.
 *
 * @param WP_Post $post Submission post.
 * @return array<string, mixed>
 */
function aiad_certificate_submission_to_row( WP_Post $post ): array {
	$first = (string) get_post_meta( $post->ID, '_submission_first_name', true );
	$last  = (string) get_post_meta( $post->ID, '_submission_last_name', true );
	$school = (string) get_post_meta( $post->ID, '_submission_school_name', true );
	if ( $school === '' ) {
		$school = (string) get_post_meta( $post->ID, '_submission_organisation', true );
	}
	if ( $school === '' ) {
		$school = (string) get_post_meta( $post->ID, '_submission_child_school', true );
	}

	$checklist = (array) get_post_meta( $post->ID, '_submission_checklist', true );
	$logo_id   = (int) get_post_meta( $post->ID, '_submission_school_logo_id', true );
	$school_logo_url = '';
	if ( $logo_id ) {
		$school_logo_url = (string) wp_get_attachment_image_url( $logo_id, 'medium' );
	}
	if ( $school_logo_url === '' ) {
		$school_logo_url = aiad_certificate_resolve_school_logo_url( $school );
	}

	$involved_as = (string) get_post_meta( $post->ID, '_submission_involved_as', true );
	$org_type    = (string) get_post_meta( $post->ID, '_submission_org_type', true );

	return array(
		'id'                   => $post->ID,
		'first_name'           => $first,
		'last_name'            => $last,
		'full_name'            => trim( $first . ' ' . $last ),
		'school_name'          => $school,
		'email'                => (string) get_post_meta( $post->ID, '_submission_email', true ),
		'involved_as'          => $involved_as,
		'org_type'             => $org_type,
		'role_title'           => (string) get_post_meta( $post->ID, '_submission_role_title', true ),
		'submitted_at'         => get_the_date( 'c', $post ),
		'submitted_display'    => get_the_date( 'j F Y', $post ),
		'school_logo_url'      => $school_logo_url,
		'wants_logo_supporter' => in_array( 'school_leader_logo_supporter', $checklist, true ),
		'certificate_copy'     => aiad_certificate_copy_for_rest( $involved_as, $org_type ),
		'thank_you_letter'     => aiad_thank_you_letter_copy_for_rest(),
	);
}

/**
 * GET /aiad/v1/certificate/thank-you-letter
 */
function aiad_rest_thank_you_letter_copy( WP_REST_Request $request ): WP_REST_Response { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	return new WP_REST_Response( aiad_thank_you_letter_copy_for_rest(), 200 );
}

/**
 * Best URL for the site brand logo on certificates and letters.
 *
 * @return string
 */
function aiad_certificate_brand_logo_url(): string {
	if ( ! function_exists( 'aiad_get_brand_logo_attachment_id' ) ) {
		return '';
	}
	$logo_id = aiad_get_brand_logo_attachment_id();
	if ( ! $logo_id ) {
		return '';
	}
	foreach ( array( 'full', 'large', 'medium' ) as $size ) {
		$url = function_exists( 'aiad_get_logo_image_url' )
			? aiad_get_logo_image_url( $logo_id, $size )
			: '';
		if ( $url === '' ) {
			$url = (string) wp_get_attachment_image_url( $logo_id, $size );
		}
		if ( $url !== '' ) {
			return $url;
		}
	}
	return '';
}

/**
 * GET /aiad/v1/certificate/bootstrap
 */
function aiad_rest_certificate_bootstrap( WP_REST_Request $request ): WP_REST_Response { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	$logo_id  = function_exists( 'aiad_get_brand_logo_attachment_id' )
		? aiad_get_brand_logo_attachment_id()
		: 0;
	$logo_url = aiad_certificate_brand_logo_url();

	return new WP_REST_Response(
		array(
			'site_name'       => get_bloginfo( 'name' ),
			'site_url'        => home_url( '/' ),
			'brand_logo_url'  => $logo_url,
			'brand_logo_id'   => $logo_id,
		),
		200
	);
}

/**
 * GET /aiad/v1/certificate/submissions
 */
function aiad_rest_certificate_submissions( WP_REST_Request $request ): WP_REST_Response {
	$max_per_page = (int) apply_filters( 'aiad_certificate_submissions_per_page_max', 500 );
	$per_page     = min( $max_per_page, max( 1, (int) $request->get_param( 'per_page' ) ) );
	$page         = max( 1, (int) $request->get_param( 'page' ) );
	$search       = sanitize_text_field( (string) $request->get_param( 'search' ) );

	$args = array(
		'post_type'              => 'form_submission',
		'post_status'            => 'private',
		'posts_per_page'         => $per_page,
		'paged'                  => $page,
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'no_found_rows'          => false,
		'update_post_meta_cache' => true,
	);

	if ( $search !== '' ) {
		$args['s'] = $search;
	}

	$query = new WP_Query( $args );
	$rows  = array();
	foreach ( $query->posts as $post ) {
		if ( $post instanceof WP_Post ) {
			$rows[] = aiad_certificate_submission_to_row( $post );
		}
	}

	$total       = (int) $query->found_posts;
	$total_pages = (int) $query->max_num_pages;

	return new WP_REST_Response(
		array(
			'submissions'  => $rows,
			'total'        => $total,
			'page'         => $page,
			'per_page'     => $per_page,
			'total_pages'  => $total_pages,
		),
		200
	);
}

/**
 * Register routes.
 */
function aiad_register_certificate_rest_routes(): void {
	register_rest_route(
		'aiad/v1',
		'/certificate/bootstrap',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'aiad_rest_certificate_bootstrap',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'aiad/v1',
		'/certificate/thank-you-letter',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'aiad_rest_thank_you_letter_copy',
			'permission_callback' => 'aiad_certificate_api_can_read',
		)
	);

	register_rest_route(
		'aiad/v1',
		'/certificate/submissions',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'aiad_rest_certificate_submissions',
			'permission_callback' => 'aiad_certificate_api_can_read',
			'args'                => array(
				'per_page' => array(
					'default'           => 500,
					'sanitize_callback' => 'absint',
				),
				'page'     => array(
					'default'           => 1,
					'sanitize_callback' => 'absint',
				),
				'search'   => array(
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'aiad_register_certificate_rest_routes' );
