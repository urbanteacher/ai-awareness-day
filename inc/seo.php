<?php
/**
 * SEO functionality: JSON-LD structured data, breadcrumbs, and canonical URLs.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if SEO plugins are active and we should skip output.
 *
 * @return bool True if we should output, false if SEO plugin is active.
 */
function aiad_seo_should_output(): bool {
	// Skip if Yoast SEO / Yoast SEO Premium is active
	if ( defined( 'WPSEO_VERSION' ) ) {
		return false;
	}
	// Skip if Rank Math is active
	if ( defined( 'RANK_MATH_VERSION' ) ) {
		return false;
	}
	// Skip if All in One SEO is active
	if ( defined( 'AIOSEO_VERSION' ) ) {
		return false;
	}
	// Skip if SEOPress is active
	if ( defined( 'SEOPRESS_VERSION' ) ) {
		return false;
	}
	// Skip if The SEO Framework is active
	if ( defined( 'THE_SEO_FRAMEWORK_VERSION' ) ) {
		return false;
	}
	// Skip if Slim SEO is active
	if ( class_exists( 'Slim_SEO' ) ) {
		return false;
	}
	// Skip if SmartCrawl (by WPMU DEV) is active
	if ( defined( 'SMARTCRAWL_VERSION' ) ) {
		return false;
	}
	return true;
}

/**
 * Attachment ID used for schema logos and event images (PNG site icon preferred).
 *
 * @return int Attachment ID or 0.
 */
function aiad_get_schema_logo_attachment_id(): int {
	$site_icon = (int) get_option( 'site_icon' );
	if ( $site_icon ) {
		return $site_icon;
	}
	if ( has_custom_logo() ) {
		$custom_logo = (int) get_theme_mod( 'custom_logo' );
		if ( $custom_logo ) {
			return $custom_logo;
		}
	}
	return absint( get_theme_mod( 'aiad_hero_logo', 0 ) );
}

/**
 * ImageObject for JSON-LD (Organization logo, Event image, etc.).
 *
 * @param string $image_size WP image size name or [width, height].
 * @return array<string, mixed>|null
 */
function aiad_get_schema_image_object( $image_size = 'full' ): ?array {
	$logo_id = aiad_get_schema_logo_attachment_id();
	if ( ! $logo_id ) {
		return null;
	}
	$logo_url = wp_get_attachment_image_url( $logo_id, $image_size );
	if ( ! $logo_url ) {
		return null;
	}
	$meta = wp_get_attachment_metadata( $logo_id );
	return array(
		'@type'  => 'ImageObject',
		'url'    => $logo_url,
		'width'  => isset( $meta['width'] ) ? (int) $meta['width'] : 512,
		'height' => isset( $meta['height'] ) ? (int) $meta['height'] : 512,
	);
}

/**
 * Social share image (Site Icon → custom logo → hero), aiad_social size when available.
 *
 * @return array{image_id: int, image: string}
 */
function aiad_get_social_share_image_data(): array {
	$logo_id = aiad_get_schema_logo_attachment_id();
	if ( ! $logo_id ) {
		return array(
			'image_id' => 0,
			'image'    => '',
		);
	}
	$url = wp_get_attachment_image_url( $logo_id, 'aiad_social' );
	if ( ! $url ) {
		$url = wp_get_attachment_image_url( $logo_id, 'full' );
	}
	return array(
		'image_id' => $logo_id,
		'image'    => $url ?: '',
	);
}

/**
 * Convert live_session datetime-local meta to ISO 8601 (Europe/London).
 *
 * @param string $datetime_local Value from _session_start_time / _session_end_time.
 * @return string ISO 8601 or empty.
 */
function aiad_session_meta_to_iso( string $datetime_local ): string {
	if ( $datetime_local === '' ) {
		return '';
	}
	try {
		$tz = new DateTimeZone( 'Europe/London' );
		$dt = new DateTime( $datetime_local, $tz );
		return $dt->format( DATE_ATOM );
	} catch ( Exception $e ) {
		return '';
	}
}

/**
 * Get Organization schema data.
 *
 * @return array<string, mixed> Organization schema array.
 */
function aiad_get_organization_schema(): array {
	$defaults = aiad_get_customizer_defaults();
	$site_name = get_theme_mod( 'aiad_hero_title', $defaults['aiad_hero_title'] ) ?: get_bloginfo( 'name' );

	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Organization',
		'name'     => $site_name,
		'url'      => home_url( '/' ),
	);

	$description = get_bloginfo( 'description' );
	if ( $description ) {
		$schema['description'] = $description;
	}

	// Logo: site icon → custom logo → hero (ImageObject per Google Organization guidance).
	$logo = aiad_get_schema_image_object( 'full' );
	if ( $logo ) {
		$schema['logo'] = $logo;
	}

	// Social profiles (sameAs) — each Customizer field below contributes.
	$same_as_settings = array(
		'aiad_linkedin', 'aiad_instagram', 'aiad_twitter', 'aiad_facebook',
		'aiad_youtube', 'aiad_tiktok', 'aiad_github',
	);
	$same_as = array();
	foreach ( $same_as_settings as $setting ) {
		$val = get_theme_mod( $setting, $defaults[ $setting ] ?? '' );
		if ( $val && $val !== '#' && filter_var( $val, FILTER_VALIDATE_URL ) ) {
			$same_as[] = $val;
		}
	}
	if ( ! empty( $same_as ) ) {
		$schema['sameAs'] = array_values( array_unique( $same_as ) );
	}

	return $schema;
}

/**
 * WebSite schema with SearchAction — eligible for the Google sitelinks search box.
 *
 * @return array<string, mixed>
 */
function aiad_get_website_schema(): array {
	$defaults  = aiad_get_customizer_defaults();
	$site_name = get_theme_mod( 'aiad_hero_title', $defaults['aiad_hero_title'] ) ?: get_bloginfo( 'name' );

	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'WebSite',
		'name'            => $site_name,
		'url'             => home_url( '/' ),
		'potentialAction' => array(
			'@type'       => 'SearchAction',
			'target'      => array(
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			),
			'query-input' => 'required name=search_term_string',
		),
	);
}

/**
 * Get Event schema for front page.
 *
 * @return array<string, mixed> Event schema array.
 */
function aiad_get_event_schema(): array {
	$event_date = apply_filters( 'aiad_timeline_event_date', '2026-06-04' );
	$event_url  = home_url( '/' );

	// ISO 8601 with UK timezone for clearer indexing of 4 June 2026.
	$start_date = $event_date . 'T00:00:00+01:00';
	$end_date   = $event_date . 'T23:59:59+01:00';

	// Get organization schema and remove @context when nesting.
	$organizer = aiad_get_organization_schema();
	unset( $organizer['@context'] );

	$performer = array(
		'@type' => 'Organization',
		'name'  => $organizer['name'] ?? 'AI Awareness Day',
		'url'   => $event_url,
	);

	$schema = array(
		'@context'              => 'https://schema.org',
		'@type'                 => 'Event',
		'name'                  => 'AI Awareness Day',
		'alternateName'         => array( '#AIAwarenessDay', 'National AI Awareness Day' ),
		'description'           => 'A nationwide UK campaign designed to build AI literacy across schools, with pupils, teachers and staff committing to at least one AI activity on the day.',
		'startDate'             => $start_date,
		'endDate'               => $end_date,
		'eventAttendanceMode'   => 'https://schema.org/MixedEventAttendanceMode',
		'eventStatus'           => 'https://schema.org/EventScheduled',
		'location'              => array(
			'@type'   => 'Place',
			'name'    => 'UK schools and online',
			'address' => array(
				'@type'          => 'PostalAddress',
				'addressCountry' => 'GB',
			),
		),
		'organizer'             => $organizer,
		'performer'             => $performer,
		'offers'                => array(
			'@type'           => 'Offer',
			'url'             => $event_url,
			'price'           => '0',
			'priceCurrency'   => 'GBP',
			'availability'    => 'https://schema.org/InStock',
			'validFrom'       => '2026-01-01',
		),
		'url'                   => $event_url,
		'inLanguage'            => 'en-GB',
		'isAccessibleForFree'   => true,
		'sameAs'                => array(
			'https://www.wikidata.org/wiki/Q139799162',
			$event_url,
		),
	);

	$event_image = aiad_get_schema_image_object( array( 192, 192 ) );
	if ( ! $event_image ) {
		$event_image = aiad_get_schema_image_object( 'full' );
	}
	if ( $event_image ) {
		$schema['image'] = array( $event_image['url'] );
	}

	return $schema;
}

/**
 * Event schema for a single live_session post.
 *
 * @param WP_Post $post Live session post.
 * @return array<string, mixed>
 */
function aiad_get_live_session_event_schema( WP_Post $post ): array {
	$start_raw = (string) get_post_meta( $post->ID, '_session_start_time', true );
	$end_raw   = (string) get_post_meta( $post->ID, '_session_end_time', true );
	$reg_url   = (string) get_post_meta( $post->ID, '_session_registration_url', true );
	$permalink = get_permalink( $post );

	$start_iso = aiad_session_meta_to_iso( $start_raw );
	$end_iso   = aiad_session_meta_to_iso( $end_raw );
	if ( $start_iso && ! $end_iso ) {
		$end_iso = $start_iso;
	}
	if ( ! $start_iso ) {
		$event_date = apply_filters( 'aiad_timeline_event_date', '2026-06-04' );
		$start_iso  = $event_date . 'T00:00:00+01:00';
		$end_iso    = $event_date . 'T23:59:59+01:00';
	}

	$organizer = aiad_get_organization_schema();
	unset( $organizer['@context'] );

	$performer = array(
		'@type' => 'Organization',
		'name'  => $organizer['name'] ?? 'AI Awareness Day',
		'url'   => home_url( '/' ),
	);
	$partner_id = (int) get_post_meta( $post->ID, '_session_partner_id', true );
	if ( $partner_id ) {
		$partner_post = get_post( $partner_id );
		if ( $partner_post ) {
			$performer = array(
				'@type' => 'Organization',
				'name'  => $partner_post->post_title,
			);
			$partner_url = get_post_meta( $partner_id, '_partner_url', true );
			if ( is_string( $partner_url ) && $partner_url !== '' ) {
				$performer['url'] = esc_url_raw( $partner_url );
			}
		}
	}

	$description = has_excerpt( $post ) ? get_the_excerpt( $post ) : '';
	if ( ! $description && ! empty( $post->post_content ) ) {
		$description = wp_trim_words( wp_strip_all_tags( $post->post_content ), 40, '…' );
	}
	if ( ! $description ) {
		$description = __( 'Live session for AI Awareness Day on 4 June 2026.', 'ai-awareness-day' );
	}

	$is_online  = $reg_url !== '';
	$attendance = $is_online
		? 'https://schema.org/OnlineEventAttendanceMode'
		: 'https://schema.org/MixedEventAttendanceMode';

	$location = $is_online
		? array(
			'@type' => 'VirtualLocation',
			'name'  => __( 'Online live stream', 'ai-awareness-day' ),
			'url'   => esc_url( $reg_url ),
		)
		: array(
			'@type'   => 'Place',
			'name'    => 'UK schools and online',
			'address' => array(
				'@type'          => 'PostalAddress',
				'addressCountry' => 'GB',
			),
		);

	$schema = array(
		'@context'              => 'https://schema.org',
		'@type'                 => 'Event',
		'name'                  => get_the_title( $post ),
		'description'           => $description,
		'startDate'             => $start_iso,
		'endDate'               => $end_iso,
		'eventAttendanceMode'   => $attendance,
		'eventStatus'           => 'https://schema.org/EventScheduled',
		'location'              => $location,
		'organizer'             => $organizer,
		'performer'             => $performer,
		'offers'                => array(
			'@type'         => 'Offer',
			'url'           => $reg_url ? esc_url( $reg_url ) : $permalink,
			'price'         => '0',
			'priceCurrency' => 'GBP',
			'availability'  => 'https://schema.org/InStock',
		),
		'url'                   => $permalink,
		'inLanguage'            => 'en-GB',
		'isAccessibleForFree'   => true,
	);

	if ( has_post_thumbnail( $post ) ) {
		$thumb = wp_get_attachment_image_url( get_post_thumbnail_id( $post ), 'full' );
		if ( $thumb ) {
			$schema['image'] = array( $thumb );
		}
	} elseif ( $partner_id ) {
		$partner_thumb = get_the_post_thumbnail_url( $partner_id, 'full' );
		if ( $partner_thumb ) {
			$schema['image'] = array( $partner_thumb );
		}
	}
	if ( empty( $schema['image'] ) ) {
		$event_image = aiad_get_schema_image_object( array( 192, 192 ) );
		if ( $event_image ) {
			$schema['image'] = array( $event_image['url'] );
		}
	}

	return $schema;
}

/**
 * NewsArticle schema for timeline campaign updates.
 *
 * @param WP_Post $post Timeline post.
 * @return array<string, mixed>
 */
function aiad_get_timeline_article_schema( WP_Post $post ): array {
	$publisher = aiad_get_organization_schema();
	unset( $publisher['@context'] );

	$description = has_excerpt( $post ) ? get_the_excerpt( $post ) : '';
	if ( ! $description && ! empty( $post->post_content ) ) {
		$description = wp_trim_words( wp_strip_all_tags( $post->post_content ), 30, '…' );
	}

	$schema = array(
		'@context'      => 'https://schema.org',
		'@type'         => 'NewsArticle',
		'headline'      => get_the_title( $post ),
		'url'           => get_permalink( $post ),
		'datePublished' => get_the_date( 'c', $post ),
		'dateModified'  => get_the_modified_date( 'c', $post ),
		'author'        => $publisher,
		'publisher'     => $publisher,
		'inLanguage'    => 'en-GB',
	);
	if ( $description ) {
		$schema['description'] = $description;
	}
	if ( has_post_thumbnail( $post ) ) {
		$thumb = wp_get_attachment_image_url( get_post_thumbnail_id( $post ), 'aiad_social' );
		if ( $thumb ) {
			$schema['image'] = array( $thumb );
		}
	}
	return $schema;
}

/**
 * Map key stage slugs to schema.org educational level values.
 *
 * @param array<string> $key_stages Array of key stage slugs.
 * @return array<string> Array of educational level strings.
 */
function aiad_map_key_stage_to_educational_level( array $key_stages ): array {
	if ( empty( $key_stages ) ) {
		return array();
	}
	
	$mapping = array(
		'eyfs' => 'Early Years Foundation Stage',
		'ks1'  => 'Key Stage 1',
		'ks2'  => 'Key Stage 2',
		'ks3'  => 'Key Stage 3',
		'ks4'  => 'Key Stage 4',
		'ks5'  => 'Key Stage 5',
	);
	
	$levels = array();
	foreach ( $key_stages as $ks ) {
		if ( isset( $mapping[ $ks ] ) ) {
			$levels[] = $mapping[ $ks ];
		}
	}
	
	return $levels;
}

/**
 * Get EducationalResource schema for single resource.
 *
 * @param WP_Post $post Resource post object.
 * @return array<string, mixed> EducationalResource schema array.
 */
function aiad_get_educational_resource_schema( WP_Post $post ): array {
	// Get organization schema and remove @context when nesting
	$organization = aiad_get_organization_schema();
	unset( $organization['@context'] );
	
	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'EducationalResource',
		'name'     => get_the_title( $post ),
		'url'      => get_permalink( $post ),
		'isPartOf' => $organization,
	);
	
	// Description
	$subtitle = get_post_meta( $post->ID, '_aiad_subtitle', true );
	if ( $subtitle ) {
		$schema['description'] = $subtitle;
	} elseif ( has_excerpt( $post ) ) {
		$schema['description'] = get_the_excerpt( $post );
	}
	
	// Educational level
	$key_stages = (array) get_post_meta( $post->ID, '_aiad_key_stage', true );
	if ( ! empty( $key_stages ) ) {
		$levels = aiad_map_key_stage_to_educational_level( $key_stages );
		if ( ! empty( $levels ) ) {
			$schema['educationalLevel'] = count( $levels ) === 1 ? $levels[0] : $levels;
		}
	}
	
	// Session length / slot (unified taxonomy — replaces legacy "Format")
	$session_terms = get_the_terms( $post->ID, 'resource_duration' );
	if ( $session_terms && ! is_wp_error( $session_terms ) && function_exists( 'aiad_resource_duration_term_labels' ) ) {
		$labels = aiad_resource_duration_term_labels( $session_terms );
		if ( ! empty( $labels ) ) {
			$schema['learningResourceType'] = count( $labels ) === 1 ? $labels[0] : $labels;
		}
	}
	
	return $schema;
}

/**
 * Get Organization schema for single partner.
 *
 * @param WP_Post $post Partner post object.
 * @return array<string, mixed> Organization schema array.
 */
function aiad_get_partner_organization_schema( WP_Post $post ): array {
	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Organization',
		'name'     => get_the_title( $post ),
	);
	
	// URL
	$partner_url = get_post_meta( $post->ID, '_partner_url', true );
	if ( $partner_url ) {
		$schema['url'] = esc_url_raw( $partner_url );
	}
	
	// Logo
	if ( has_post_thumbnail( $post ) ) {
		$logo_url = get_the_post_thumbnail_url( $post, 'full' );
		if ( $logo_url ) {
			$schema['logo'] = $logo_url;
		}
	}
	
	return $schema;
}

/**
 * Get breadcrumb trail array.
 *
 * @return array<int, array{name: string, url: string}> Breadcrumb items.
 */
function aiad_get_breadcrumb_trail(): array {
	$trail = array();
	
	// Home
	$trail[] = array(
		'name' => __( 'Home', 'ai-awareness-day' ),
		'url'  => home_url( '/' ),
	);
	
	// Front page - just home
	if ( is_front_page() ) {
		return $trail;
	}
	
	// Archive pages
	if ( is_post_type_archive() ) {
		$post_type = get_post_type();
		$archive_title = post_type_archive_title( '', false );
		if ( $archive_title ) {
			$trail[] = array(
				'name' => $archive_title,
				'url'  => get_post_type_archive_link( $post_type ),
			);
		}
		return $trail;
	}
	
	// Single posts
	if ( is_singular() ) {
		global $post;
		$post_type = get_post_type( $post );
		
		// Add archive if post type has archive
		if ( post_type_exists( $post_type ) ) {
			$archive_link = get_post_type_archive_link( $post_type );
			if ( $archive_link ) {
				$archive_title = post_type_archive_title( '', false );
				if ( $archive_title ) {
					$trail[] = array(
						'name' => $archive_title,
						'url'  => $archive_link,
					);
				}
			}
		}
		
		// Add current post (not linked in visible breadcrumbs, but included in schema)
		$trail[] = array(
			'name' => get_the_title( $post ),
			'url'  => get_permalink( $post ),
		);
		
		return $trail;
	}
	
	return $trail;
}

/**
 * Render a visible breadcrumb trail (skipped on the front page).
 * Echoes nothing if the trail has fewer than 2 items.
 */
function aiad_render_breadcrumbs(): void {
	if ( is_front_page() ) {
		return;
	}
	$trail = aiad_get_breadcrumb_trail();
	if ( count( $trail ) < 2 ) {
		return;
	}
	$last_index = count( $trail ) - 1;
	echo '<nav class="aiad-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'ai-awareness-day' ) . '">';
	echo '<div class="container"><ol class="aiad-breadcrumbs__list">';
	foreach ( $trail as $i => $item ) {
		$is_current = ( $i === $last_index );
		echo '<li class="aiad-breadcrumbs__item' . ( $is_current ? ' is-current' : '' ) . '">';
		if ( $is_current ) {
			echo '<span aria-current="page">' . esc_html( $item['name'] ) . '</span>';
		} else {
			echo '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['name'] ) . '</a>';
		}
		if ( ! $is_current ) {
			echo '<span class="aiad-breadcrumbs__sep" aria-hidden="true">/</span>';
		}
		echo '</li>';
	}
	echo '</ol></div></nav>';
}

/**
 * Get BreadcrumbList schema.
 *
 * @return array<string, mixed> BreadcrumbList schema array.
 */
function aiad_get_breadcrumb_schema(): array {
	$trail = aiad_get_breadcrumb_trail();
	
	if ( empty( $trail ) || count( $trail ) < 2 ) {
		return array();
	}
	
	$items = array();
	$position = 1;
	
	foreach ( $trail as $crumb ) {
		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $position++,
			'name'     => $crumb['name'],
			'item'     => $crumb['url'],
		);
	}
	
	return array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $items,
	);
}


/**
 * Output JSON-LD structured data schemas.
 */
function aiad_output_json_ld_schemas(): void {
	if ( ! aiad_seo_should_output() ) {
		return;
	}
	
	// Organization schema on all pages
	$org_schema = aiad_get_organization_schema();
	if ( ! empty( $org_schema ) ) {
		echo '<script type="application/ld+json">' . "\n";
		echo wp_json_encode( $org_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		echo "\n" . '</script>' . "\n";
	}

	// WebSite schema (with SearchAction) on the front page — eligible for sitelinks search box.
	if ( is_front_page() ) {
		$website_schema = aiad_get_website_schema();
		if ( ! empty( $website_schema ) ) {
			echo '<script type="application/ld+json">' . "\n";
			echo wp_json_encode( $website_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			echo "\n" . '</script>' . "\n";
		}
	}

	// Event schema on front page
	if ( is_front_page() ) {
		$event_schema = aiad_get_event_schema();
		if ( ! empty( $event_schema ) ) {
			echo '<script type="application/ld+json">' . "\n";
			echo wp_json_encode( $event_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			echo "\n" . '</script>' . "\n";
		}
	}
	
	// EducationalResource schema on single resources
	if ( is_singular( 'resource' ) ) {
		global $post;
		$resource_schema = aiad_get_educational_resource_schema( $post );
		if ( ! empty( $resource_schema ) ) {
			echo '<script type="application/ld+json">' . "\n";
			echo wp_json_encode( $resource_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			echo "\n" . '</script>' . "\n";
		}
	}
	
	// Partner Organization schema on single partners
	if ( is_singular( 'partner' ) ) {
		global $post;
		$partner_schema = aiad_get_partner_organization_schema( $post );
		if ( ! empty( $partner_schema ) ) {
			echo '<script type="application/ld+json">' . "\n";
			echo wp_json_encode( $partner_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			echo "\n" . '</script>' . "\n";
		}
	}

	// Event schema on single live sessions
	if ( is_singular( 'live_session' ) ) {
		global $post;
		$session_event_schema = aiad_get_live_session_event_schema( $post );
		if ( ! empty( $session_event_schema ) ) {
			echo '<script type="application/ld+json">' . "\n";
			echo wp_json_encode( $session_event_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			echo "\n" . '</script>' . "\n";
		}
	}

	// NewsArticle schema on timeline updates
	if ( is_singular( 'timeline' ) ) {
		global $post;
		$article_schema = aiad_get_timeline_article_schema( $post );
		if ( ! empty( $article_schema ) ) {
			echo '<script type="application/ld+json">' . "\n";
			echo wp_json_encode( $article_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			echo "\n" . '</script>' . "\n";
		}
	}
	
	// BreadcrumbList schema on all pages (except front page)
	if ( ! is_front_page() ) {
		$breadcrumb_schema = aiad_get_breadcrumb_schema();
		if ( ! empty( $breadcrumb_schema ) ) {
			echo '<script type="application/ld+json">' . "\n";
			echo wp_json_encode( $breadcrumb_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
			echo "\n" . '</script>' . "\n";
		}
	}
}
add_action( 'wp_head', 'aiad_output_json_ld_schemas', 10 );

/**
 * Output search-engine verification <meta> tags from Customizer settings.
 * Accepts either the raw token or the full meta tag pasted by the user —
 * the regex pulls the content="…" value out either way.
 */
function aiad_output_search_verification_meta(): void {
	$map = array(
		'aiad_verify_google'    => 'google-site-verification',
		'aiad_verify_bing'      => 'msvalidate.01',
		'aiad_verify_pinterest' => 'p:domain_verify',
	);
	foreach ( $map as $setting => $meta_name ) {
		$raw = trim( (string) get_theme_mod( $setting, '' ) );
		if ( $raw === '' ) {
			continue;
		}
		// If the user pasted the full meta tag, extract the content attribute.
		if ( preg_match( '/content\s*=\s*["\']([^"\']+)["\']/i', $raw, $m ) ) {
			$raw = $m[1];
		}
		echo '<meta name="' . esc_attr( $meta_name ) . '" content="' . esc_attr( $raw ) . '" />' . "\n";
	}
}
add_action( 'wp_head', 'aiad_output_search_verification_meta', 1 );

/**
 * Output canonical URL.
 */
function aiad_output_canonical_url(): void {
	if ( ! aiad_seo_should_output() ) {
		return;
	}
	
	$canonical = '';
	
	// Front page
	if ( is_front_page() ) {
		$canonical = home_url( '/' );
	}
	// Single posts/pages
	elseif ( is_singular() ) {
		$canonical = get_permalink();
	}
	// Archive pages (strip query params for filtered views)
	elseif ( is_post_type_archive() ) {
		$post_type = get_post_type();
		$canonical = get_post_type_archive_link( $post_type );
	}
	// Other archives
	elseif ( is_archive() ) {
		$canonical = get_permalink();
	}
	// Default
	else {
		$canonical = home_url( add_query_arg( null, null ) );
		// Remove query params for canonical
		$canonical = strtok( $canonical, '?' );
	}
	
	if ( $canonical ) {
		echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n";
	}
}
add_action( 'wp_head', 'aiad_output_canonical_url', 1 );

/**
 * Persistent admin notice when "Discourage search engines from indexing this
 * site" is enabled (Settings → Reading). This option silently noindexes every
 * page on the site and is the most common cause of WP sites disappearing from
 * Google. Shown to anyone who can change the setting, with a direct link to fix.
 */
function aiad_notice_search_engines_discouraged(): void {
	if ( (int) get_option( 'blog_public', 1 ) !== 0 ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$fix_url = admin_url( 'options-reading.php' );
	echo '<div class="notice notice-error"><p><strong>'
		. esc_html__( 'Search engine indexing is currently DISABLED.', 'ai-awareness-day' )
		. '</strong> '
		. esc_html__( 'Your site is hidden from Google and other search engines because "Discourage search engines from indexing this site" is enabled.', 'ai-awareness-day' )
		. ' <a href="' . esc_url( $fix_url ) . '">'
		. esc_html__( 'Fix this now in Settings → Reading.', 'ai-awareness-day' )
		. '</a></p></div>';
}
add_action( 'admin_notices', 'aiad_notice_search_engines_discouraged' );

/**
 * Remove author/user URLs from the XML sitemap (thin archive pages).
 *
 * @param WP_Sitemaps_Provider|false $provider Provider instance.
 * @param string                     $name     Provider name.
 * @return WP_Sitemaps_Provider|false
 */
function aiad_disable_users_sitemap( $provider, string $name ) {
	if ( 'users' === $name ) {
		return false;
	}
	return $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'aiad_disable_users_sitemap', 10, 2 );

/**
 * Noindex low-value WordPress archives (author, media attachment).
 *
 * @param array<string, bool|string> $robots Robots directives.
 * @return array<string, bool|string>
 */
function aiad_noindex_low_value_archives( array $robots ): array {
	if ( is_author() || is_attachment() ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'aiad_noindex_low_value_archives' );

/**
 * Serve curated llms.txt from the theme (replaces thin Hostinger auto-generated lists).
 *
 * @see https://llmstxt.org/
 */
function aiad_maybe_serve_llms_txt(): void {
	if ( php_sapi_name() === 'cli' ) {
		return;
	}
	$uri = isset( $_SERVER['REQUEST_URI'] )
		? (string) wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH )
		: '';
	if ( ! preg_match( '#^/llms\.txt/?$#', $uri ) ) {
		return;
	}
	$file = AIAD_DIR . '/llms.txt';
	if ( ! is_readable( $file ) ) {
		return;
	}
	$contents = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( false === $contents ) {
		return;
	}
	header( 'Content-Type: text/plain; charset=utf-8' );
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- plain text file
	echo $contents;
	exit;
}
add_action( 'init', 'aiad_maybe_serve_llms_txt', 0 );
