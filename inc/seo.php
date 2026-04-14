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
	
	// Logo
	$logo_id = absint( get_theme_mod( 'aiad_hero_logo', 0 ) );
	if ( $logo_id ) {
		$logo_url = wp_get_attachment_image_url( $logo_id, 'full' );
		if ( $logo_url ) {
			$schema['logo'] = $logo_url;
		}
	} elseif ( has_custom_logo() ) {
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		if ( $custom_logo_id ) {
			$logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
			if ( $logo_url ) {
				$schema['logo'] = $logo_url;
			}
		}
	}
	
	// Social profiles
	$same_as = array();
	$linkedin = get_theme_mod( 'aiad_linkedin', $defaults['aiad_linkedin'] );
	if ( $linkedin && $linkedin !== '#' ) {
		$same_as[] = $linkedin;
	}
	$instagram = get_theme_mod( 'aiad_instagram', $defaults['aiad_instagram'] );
	if ( $instagram && $instagram !== '#' ) {
		$same_as[] = $instagram;
	}
	if ( ! empty( $same_as ) ) {
		$schema['sameAs'] = $same_as;
	}
	
	return $schema;
}

/**
 * Get Event schema for front page.
 *
 * @return array<string, mixed> Event schema array.
 */
function aiad_get_event_schema(): array {
	$event_date = apply_filters( 'aiad_timeline_event_date', '2026-06-04' );
	
	// Get organization schema and remove @context when nesting
	$organizer = aiad_get_organization_schema();
	unset( $organizer['@context'] );
	
	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Event',
		'name'     => 'AI Awareness Day',
		'startDate' => $event_date,
		'location' => array(
			'@type' => 'Place',
			'name'  => 'United Kingdom',
		),
		'organizer' => $organizer,
		'url'       => home_url( '/' ),
	);
	
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
	
	// Learning resource type
	$resource_types = get_the_terms( $post->ID, 'resource_type' );
	if ( $resource_types && ! is_wp_error( $resource_types ) ) {
		$type_names = wp_list_pluck( $resource_types, 'name' );
		if ( ! empty( $type_names ) ) {
			$schema['learningResourceType'] = count( $type_names ) === 1 ? $type_names[0] : $type_names;
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
 * Render visible breadcrumb HTML.
 */
function aiad_render_breadcrumbs(): void {
	$trail = aiad_get_breadcrumb_trail();
	
	if ( empty( $trail ) || count( $trail ) < 2 ) {
		return;
	}
	
	$last_index = count( $trail ) - 1;
	
	echo '<nav aria-label="' . esc_attr__( 'Breadcrumbs', 'ai-awareness-day' ) . '" class="breadcrumbs">';
	echo '<ul class="breadcrumbs__list">';
	
	foreach ( $trail as $index => $crumb ) {
		$is_last = ( $index === $last_index );
		
		echo '<li class="breadcrumbs__item">';
		if ( $is_last ) {
			echo '<span class="breadcrumbs__current" aria-current="page">' . esc_html( $crumb['name'] ) . '</span>';
		} else {
			echo '<a href="' . esc_url( $crumb['url'] ) . '" class="breadcrumbs__link">' . esc_html( $crumb['name'] ) . '</a>';
		}
		echo '</li>';
	}
	
	echo '</ul>';
	echo '</nav>';
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
