<?php
/**
 * One-time migration: refresh campaign copy from 2026 → 2027 after deploy.
 *
 * Updates site title/tagline, theme mods, and public post titles/excerpts/content
 * that still brand "AI Awareness Day 2026". Leaves NEU Report 2026 citations,
 * survey data keys, and media upload paths untouched.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Run the 2027 branding migration once.
 */
function aiad_migrate_2027_branding(): void {
	if ( get_option( 'aiad_2027_branding_migrated' ) === '1' ) {
		return;
	}

	$tagline = (string) get_option( 'blogdescription', '' );
	if ( $tagline !== '' && false !== stripos( $tagline, '2026' ) ) {
		update_option( 'blogdescription', '2027' );
	}

	// Production often used an empty tagline with the date only in the site title.
	$blogname = (string) get_option( 'blogname', '' );
	if ( $blogname !== '' && ( false !== stripos( $blogname, '2026' ) || false !== stripos( $blogname, 'june' ) ) ) {
		update_option( 'blogname', 'AI Awareness Day' );
		if ( (string) get_option( 'blogdescription', '' ) === '' ) {
			update_option( 'blogdescription', '2027' );
		}
	} elseif ( (string) get_option( 'blogdescription', '' ) === '' ) {
		update_option( 'blogdescription', '2027' );
	}

	set_theme_mod( 'aiad_hero_date', 'AI Awareness Day 2027' );
	set_theme_mod( 'aiad_event_date_ymd', '2027-06-04' );

	$campaign = (string) get_theme_mod( 'aiad_campaign_text', '' );
	if ( $campaign !== '' && false !== stripos( $campaign, '2026' ) ) {
		$campaign = str_ireplace(
			array(
				'National AI Awareness Day (4th June 2026)',
				'National AI Awareness Day (4 June 2026)',
				'4th June 2026',
				'4 June 2026',
			),
			array(
				'National AI Awareness Day',
				'National AI Awareness Day',
				'2027',
				'2027',
			),
			$campaign
		);
		set_theme_mod( 'aiad_campaign_text', $campaign );
	}

	aiad_migrate_2027_replace_in_posts();

	update_option( 'aiad_2027_branding_migrated', '1' );
}
add_action( 'init', 'aiad_migrate_2027_branding', 5 );

/**
 * Replace public campaign 2026 branding inside published content.
 */
function aiad_migrate_2027_replace_in_posts(): void {
	global $wpdb;

	$pairs = array(
		'AI Awareness Day 2026' => 'AI Awareness Day 2027',
		'15 AI Buzzwords Every Teacher Should Know in 2026' => '15 AI Buzzwords Every Teacher Should Know in 2027',
		'During the build-up to AI Awareness Day 2026' => 'During the build-up to AI Awareness Day 2027',
		'educators hear most in 2026' => 'educators hear most in 2027',
		'If you teach in 2026' => 'If you teach in 2027',
		'4th June 2026' => 'AI Awareness Day 2027',
		'4 June 2026' => 'AI Awareness Day 2027',
	);

	// Avoid rewriting the NEU publication title / slug content.
	$exclude_like = array(
		'%NEU State of Education%',
		'%neu-ai-report-2026%',
		'%unpacking-neu-ai-report%',
		'%national-survey-2026%',
	);

	$post_types = array( 'post', 'page', 'timeline', 'resource', 'featured_resource', 'live_session', 'partner' );
	$type_in    = "'" . implode( "','", array_map( 'esc_sql', $post_types ) ) . "'";

	foreach ( $pairs as $from => $to ) {
		if ( $from === $to ) {
			continue;
		}
		$like_from = '%' . $wpdb->esc_like( $from ) . '%';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- type list is escaped above.
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts}
				WHERE post_status IN ('publish','draft','private','future')
				AND post_type IN ({$type_in})
				AND (
					post_title LIKE %s
					OR post_excerpt LIKE %s
					OR post_content LIKE %s
				)",
				$like_from,
				$like_from,
				$like_from
			)
		);

		if ( empty( $ids ) ) {
			continue;
		}

		foreach ( $ids as $post_id ) {
			$post = get_post( (int) $post_id );
			if ( ! $post instanceof WP_Post ) {
				continue;
			}

			$blob = $post->post_title . "\n" . $post->post_excerpt . "\n" . $post->post_name . "\n" . $post->post_content;
			$skip = false;
			foreach ( $exclude_like as $needle ) {
				$plain = trim( $needle, '%' );
				if ( false !== stripos( $blob, $plain ) ) {
					// Still allow replacing other campaign phrases outside NEU-only posts.
					if ( false !== stripos( $post->post_title, 'NEU' ) || false !== stripos( $post->post_name, 'neu-ai-report' ) ) {
						$skip = true;
						break;
					}
				}
			}
			if ( $skip ) {
				continue;
			}

			$update = array( 'ID' => (int) $post_id );
			$dirty  = false;
			foreach ( array( 'post_title', 'post_excerpt', 'post_content' ) as $field ) {
				$value = (string) $post->$field;
				if ( $value === '' || false === strpos( $value, $from ) ) {
					continue;
				}
				$update[ $field ] = str_replace( $from, $to, $value );
				$dirty            = true;
			}
			if ( $dirty ) {
				wp_update_post( $update );
			}
		}
	}
}
