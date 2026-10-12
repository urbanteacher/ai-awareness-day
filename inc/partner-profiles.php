<?php
/**
 * One-time migration: give every partner profile the same set of details, on deploy.
 *
 * Each partner in inc/partner-profiles-data.php is matched by its slug. Only empty fields are filled (website, profile
 * intro, bio, AI resources link, resource links, partner type, logo alt text), so nothing a partner or editor wrote
 * is overwritten. The exceptions are listed per partner as 'fix_title' and 'fix_type'. Every value it changes is kept
 * in the aiad_partner_profiles_backup option first. A partner marked 'create' that is not on the site yet is added,
 * with its logo imported from the theme.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Run the partner profiles migration once per data version.
 */
function aiad_migrate_partner_profiles(): void {
	$version = '1';
	if ( get_option( 'aiad_partner_profiles_migrated' ) === $version || ! post_type_exists( 'partner' ) ) {
		return;
	}

	$profiles = require AIAD_DIR . '/inc/partner-profiles-data.php';
	$backup   = get_option( 'aiad_partner_profiles_backup', array() );
	$backup   = is_array( $backup ) ? $backup : array();

	foreach ( $profiles as $slug => $profile ) {
		$post = get_page_by_path( $slug, OBJECT, 'partner' );
		if ( ! $post && ! empty( $profile['create'] ) ) {
			$post = aiad_partner_profiles_create( $slug, $profile );
		}
		if ( ! $post ) {
			continue;
		}
		$changed = aiad_partner_profiles_fill( $post, $profile );
		if ( $changed ) {
			$backup[ $slug ] = $changed;
		}
	}

	update_option( 'aiad_partner_profiles_backup', $backup, false );
	update_option( 'aiad_partner_profiles_migrated', $version );
}
add_action( 'init', 'aiad_migrate_partner_profiles', 42 );

/**
 * Add a partner that is not on the site yet, with its logo from the theme's assets/images/logos folder.
 *
 * @param string $slug    Partner slug.
 * @param array  $profile Its entry in partner-profiles-data.php.
 * @return WP_Post|null The new partner, or null if it could not be created.
 */
function aiad_partner_profiles_create( string $slug, array $profile ): ?WP_Post {
	$id = wp_insert_post(
		array(
			'post_type'   => 'partner',
			'post_status' => 'publish',
			'post_name'   => $slug,
			'post_title'  => $profile['title'],
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		return null;
	}

	if ( aiad_partner_profiles_set_logo( $id, $slug, $profile ) ) {
		aiad_partner_profiles_logo_done( $slug );
	}

	return get_post( $id );
}

/**
 * Import the partner's logo from the theme's assets/images/logos folder and make it the featured image.
 *
 * @param int    $id      Partner post ID.
 * @param string $slug    Partner slug.
 * @param array  $profile Its entry in partner-profiles-data.php.
 * @return bool Whether the logo was imported.
 */
function aiad_partner_profiles_set_logo( int $id, string $slug, array $profile ): bool {
	$logo = AIAD_DIR . '/assets/images/logos/' . ( $profile['logo'] ?? '' );
	if ( empty( $profile['logo'] ) || ! is_readable( $logo ) ) {
		return false;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = wp_tempnam( basename( $logo ) );
	if ( ! $tmp || ! copy( $logo, $tmp ) ) {
		return false;
	}
	$attachment = media_handle_sideload( array( 'name' => $slug . '-logo.png', 'tmp_name' => $tmp ), $id, $profile['title'] . ' logo' );
	if ( is_wp_error( $attachment ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		return false;
	}
	update_post_meta( (int) $attachment, '_wp_attachment_image_alt', $profile['title'] );
	set_post_thumbnail( $id, (int) $attachment );
	return true;
}

/**
 * Fill a partner's empty fields from its profile entry.
 *
 * @param WP_Post $post    The partner.
 * @param array   $profile Its entry in partner-profiles-data.php.
 * @return array The previous value of every field that changed, keyed by field.
 */
function aiad_partner_profiles_fill( WP_Post $post, array $profile ): array {
	$id      = $post->ID;
	$changed = array();

	$update = array();
	if ( ! empty( $profile['fix_title'] ) && $post->post_title !== $profile['title'] ) {
		$changed['post_title'] = $post->post_title;
		$update['post_title']  = $profile['title'];
	}
	if ( ! empty( $profile['bio'] ) && trim( wp_strip_all_tags( $post->post_content ) ) === '' ) {
		$changed['post_content'] = $post->post_content;
		$update['post_content']  = implode(
			"\n\n",
			array_map(
				static fn( $p ) => "<!-- wp:paragraph -->\n<p>" . esc_html( $p ) . "</p>\n<!-- /wp:paragraph -->",
				$profile['bio']
			)
		);
	}
	if ( $update ) {
		wp_update_post( array( 'ID' => $id ) + $update );
	}

	$meta = array(
		'_partner_url'               => $profile['website'] ?? '',
		'_partner_profile_intro'     => $profile['intro'] ?? '',
		'_partner_ai_resources_url'  => $profile['ai_resources_url'] ?? '',
	);
	foreach ( $meta as $key => $value ) {
		if ( $value !== '' && (string) get_post_meta( $id, $key, true ) === '' ) {
			$changed[ $key ] = '';
			update_post_meta( $id, $key, $value );
		}
	}
	if ( ! empty( $profile['ai_resources_url'] ) && get_post_meta( $id, '_partner_provides_ai_resources', true ) !== '1' ) {
		$changed['_partner_provides_ai_resources'] = (string) get_post_meta( $id, '_partner_provides_ai_resources', true );
		update_post_meta( $id, '_partner_provides_ai_resources', '1' );
	}
	$links = get_post_meta( $id, '_partner_links', true );
	if ( ! empty( $profile['links'] ) && ( ! is_array( $links ) || ! $links ) ) {
		$changed['_partner_links'] = $links;
		update_post_meta( $id, '_partner_links', $profile['links'] );
	}

	if ( ! empty( $profile['type'] ) ) {
		$current = wp_get_post_terms( $id, 'partner_type', array( 'fields' => 'slugs' ) );
		$current = is_wp_error( $current ) ? array() : $current;
		if ( ( ! $current || ! empty( $profile['fix_type'] ) ) && $current !== array( $profile['type'] ) && aiad_partner_profiles_type_exists( $profile['type'] ) ) {
			$changed['partner_type'] = $current;
			wp_set_object_terms( $id, $profile['type'], 'partner_type' );
		}
	}

	$logo = (int) get_post_thumbnail_id( $id );
	if ( $logo && (string) get_post_meta( $logo, '_wp_attachment_image_alt', true ) === '' ) {
		$changed['logo_alt'] = '';
		update_post_meta( $logo, '_wp_attachment_image_alt', $profile['title'] );
	}

	return $changed;
}

/**
 * Whether a partner type exists, adding the types this migration introduced if the aiad-core copy on the site does
 * not have them yet.
 *
 * @param string $slug Partner type slug.
 */
function aiad_partner_profiles_type_exists( string $slug ): bool {
	if ( term_exists( $slug, 'partner_type' ) ) {
		return true;
	}
	$names = array(
		'mat'          => 'MAT',
		'edtech'       => 'EdTech',
		'broadcaster'  => 'Broadcaster',
		'organisation' => 'Organisation',
		'non-profit'   => 'Non-profit',
	);
	return isset( $names[ $slug ] ) && ! is_wp_error( wp_insert_term( $names[ $slug ], 'partner_type', array( 'slug' => $slug ) ) );
}

/**
 * Remember that a partner's logo has been dealt with, so the logo fix runs once per partner.
 *
 * @param string $slug Partner slug.
 */
function aiad_partner_profiles_logo_done( string $slug ): void {
	$done = get_option( 'aiad_partner_logos_fixed', array() );
	$done = is_array( $done ) ? $done : array();
	if ( ! in_array( $slug, $done, true ) ) {
		$done[] = $slug;
		update_option( 'aiad_partner_logos_fixed', $done, false );
	}
}

/**
 * One-time, per partner: replace a wrong logo with the theme's file ('fix_logo' in partner-profiles-data.php).
 * AILitKit's logo on the site was a picture of a broken image. A separate step from the profile migration, which
 * had already run when this was found. The old image stays in the media library and its ID is kept in the
 * aiad_partner_profiles_backup option, under "<slug>:logo".
 */
function aiad_migrate_partner_logo_fixes(): void {
	if ( ! post_type_exists( 'partner' ) ) {
		return;
	}
	$done = get_option( 'aiad_partner_logos_fixed', array() );
	$done = is_array( $done ) ? $done : array();

	foreach ( require AIAD_DIR . '/inc/partner-profiles-data.php' as $slug => $profile ) {
		if ( empty( $profile['fix_logo'] ) || in_array( $slug, $done, true ) ) {
			continue;
		}
		$post = get_page_by_path( $slug, OBJECT, 'partner' );
		if ( ! $post ) {
			continue; // Not on the site yet; the profile migration creates it with the right logo.
		}
		$old = (int) get_post_thumbnail_id( $post->ID );
		if ( aiad_partner_profiles_set_logo( $post->ID, $slug, $profile ) ) {
			$backup = get_option( 'aiad_partner_profiles_backup', array() );
			$backup = is_array( $backup ) ? $backup : array();
			$backup[ $slug . ':logo' ] = array( 'logo' => $old );
			update_option( 'aiad_partner_profiles_backup', $backup, false );
			aiad_partner_profiles_logo_done( $slug );
		}
	}
}
add_action( 'init', 'aiad_migrate_partner_logo_fixes', 43 );
