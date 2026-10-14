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
	// Bump the version when partner-profiles-data.php gains partners; each run skips the partners the previous one handled.
	$version  = '3';
	$previous = (string) get_option( 'aiad_partner_profiles_migrated', '' );
	if ( $previous === $version || ! post_type_exists( 'partner' ) ) {
		return;
	}

	$profiles = require AIAD_DIR . '/inc/partner-profiles-data.php';
	$backup   = get_option( 'aiad_partner_profiles_backup', array() );
	$backup   = is_array( $backup ) ? $backup : array();

	foreach ( $profiles as $slug => $profile ) {
		aiad_partner_profiles_featured_resources( $profile );
		if ( '' !== $previous && isset( $backup[ $slug ] ) ) {
			continue; // Handled by an earlier run, and perhaps edited since.
		}
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
		'agency'       => 'Agency',
		'tech-network' => 'Tech Network',
		'events-company' => 'Events Company',
		'fe'           => 'FE',
		'alternative-provision' => 'Alternative Provision',
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

/**
 * Add a partner's tiles to the homepage's "Extra Resources" section (featured resources). One whose link is already
 * a featured resource is left alone, so this can run again without making copies or undoing edits.
 *
 * @param array $profile Its entry in partner-profiles-data.php.
 */
function aiad_partner_profiles_featured_resources( array $profile ): void {
	foreach ( $profile['featured_resources'] ?? array() as $tile ) {
		$existing = get_posts(
			array(
				'post_type'      => 'featured_resource',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_featured_resource_url', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one-time migration.
				'meta_value'     => $tile['url'], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		if ( $existing ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'featured_resource',
				'post_status'  => 'publish',
				'post_title'   => $tile['title'],
				'post_excerpt' => $tile['excerpt'],
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			continue;
		}
		update_post_meta( $id, '_featured_resource_url', $tile['url'] );
		update_post_meta( $id, '_featured_resource_org_name', $tile['org'] );
		wp_set_object_terms( $id, $tile['strand'], 'resource_principle' );
	}
}

/**
 * One-time: take back the "AI resources" link and tick the profiles migration put on every partner with a resources
 * page. Each one makes a partner an AI resources card at the front of the homepage grid, and 22 of them made the
 * page a long scroll. Only partners the migration set (it kept their previous values in the
 * aiad_partner_profiles_backup option) and only where the value is still the one it wrote, so nothing an editor
 * has set or changed since is touched. AILitKit keeps its link. The profile pages' resource lists stay.
 */
function aiad_migrate_partner_ai_flags_revert(): void {
	if ( get_option( 'aiad_partner_ai_flags_reverted' ) === '1' || ! post_type_exists( 'partner' ) ) {
		return;
	}
	// Wait until the profiles migration has run on this site, so there is a backup to read.
	if ( '' === (string) get_option( 'aiad_partner_profiles_migrated', '' ) ) {
		return;
	}
	$backup = get_option( 'aiad_partner_profiles_backup', array() );
	// Partners whose AI resources link was set by hand before the migration (their links differ from the ones it
	// wrote), plus AILitKit: never touched, whatever the backup says.
	$keep = array( 'ailitkit', 'bbc-bitesize', 'bcs-the-chartered-institute-for-it', 'common-sense-media', 'parent-zone', 'pearson', 'rapberry-pi-foundation', 'rm', 'tech-she-can' );

	foreach ( is_array( $backup ) ? $backup : array() as $slug => $changed ) {
		if ( in_array( $slug, $keep, true ) || str_contains( (string) $slug, ':' ) || ! is_array( $changed ) ) {
			continue;
		}
		$post = get_page_by_path( (string) $slug, OBJECT, 'partner' );
		if ( ! $post ) {
			continue;
		}
		if ( array_key_exists( '_partner_provides_ai_resources', $changed ) && '1' === (string) get_post_meta( $post->ID, '_partner_provides_ai_resources', true ) ) {
			$before = (string) $changed['_partner_provides_ai_resources'];
			'' === $before ? delete_post_meta( $post->ID, '_partner_provides_ai_resources' ) : update_post_meta( $post->ID, '_partner_provides_ai_resources', $before );
		}
		if ( array_key_exists( '_partner_ai_resources_url', $changed ) && '' === (string) $changed['_partner_ai_resources_url'] ) {
			delete_post_meta( $post->ID, '_partner_ai_resources_url' );
		}
	}
	update_option( 'aiad_partner_ai_flags_reverted', '1' );
}
add_action( 'init', 'aiad_migrate_partner_ai_flags_revert', 44 );

/**
 * One-time: move partners the profiles migration typed to a better-fitting type. Unthinkable is an agency, Tech
 * London Advocates and Black AI Futures are tech networks, Bett is an events company Capital City College is FE and Education Links is alternative provision. A partner moves only if it still has the type the migration gave it, so a type an
 * editor has set since is kept.
 */
function aiad_migrate_partner_type_moves(): void {
	if ( get_option( 'aiad_partner_type_moves' ) === '1' || ! post_type_exists( 'partner' ) ) {
		return;
	}
	if ( '' === (string) get_option( 'aiad_partner_profiles_migrated', '' ) ) {
		return; // The profiles migration has not run yet; it will use the new types itself.
	}
	$moves = array(
		'unthinkable'           => array( 'tech-company', 'agency' ),
		'tech-london-advocates' => array( 'institute', 'tech-network' ),
		'black-futures-ai'      => array( 'institute', 'tech-network' ),
		'bett'                  => array( 'tech-company', 'events-company' ),
		'capital-city-college'  => array( 'school', 'fe' ),
		'education-links'       => array( 'school', 'alternative-provision' ),
	);
	foreach ( $moves as $slug => list( $from, $to ) ) {
		$post = get_page_by_path( $slug, OBJECT, 'partner' );
		if ( ! $post || ! aiad_partner_profiles_type_exists( $to ) ) {
			continue;
		}
		$current = wp_get_post_terms( $post->ID, 'partner_type', array( 'fields' => 'slugs' ) );
		if ( ! is_wp_error( $current ) && array( $from ) === $current ) {
			wp_set_object_terms( $post->ID, $to, 'partner_type' );
		}
	}
	update_option( 'aiad_partner_type_moves', '1' );
}
add_action( 'init', 'aiad_migrate_partner_type_moves', 45 );
