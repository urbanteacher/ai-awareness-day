<?php
/**
 * Open Graph meta tags and social sharing functionality.
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
function aiad_sharing_should_output(): bool {
	// Skip if Yoast SEO is active
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
	return true;
}

/**
 * Get Open Graph data for current page.
 *
 * @return array<string, mixed> Array with 'title', 'description', 'image', 'image_id', 'url', 'type', 'site_name'.
 */
function aiad_get_og_data(): array {
	$defaults  = aiad_get_customizer_defaults();
	$site_name = get_theme_mod( 'aiad_hero_title', $defaults['aiad_hero_title'] ) ?: get_bloginfo( 'name' );

	$data = array(
		'title'       => '',
		'description' => '',
		'image'       => '',
		'image_id'    => 0,
		'url'         => home_url( '/' ),
		'type'        => 'website',
		'site_name'   => $site_name,
	);

	// Front page
	if ( is_front_page() ) {
		$event_date    = get_theme_mod( 'aiad_hero_date', $defaults['aiad_hero_date'] );
		$data['title'] = $event_date
			? sprintf( '%s — %s', $site_name, $event_date )
			: $site_name;

		$campaign_text        = get_theme_mod( 'aiad_campaign_text', $defaults['aiad_campaign_text'] );
		$subtitle             = get_theme_mod( 'aiad_hero_subtitle', $defaults['aiad_hero_subtitle'] );
		$data['description']  = sprintf(
			'%s %s',
			$campaign_text ?: '',
			$subtitle ?: get_bloginfo( 'description' )
		);
		// Trim to ~160 chars for optimal preview display
		if ( strlen( $data['description'] ) > 160 ) {
			$data['description'] = wp_trim_words( $data['description'], 25, '…' );
		}

		$logo_id = absint( get_theme_mod( 'aiad_hero_logo', 0 ) );
		if ( $logo_id ) {
			$data['image_id'] = $logo_id;
			$data['image']    = wp_get_attachment_image_url( $logo_id, 'aiad_social' ) ?: '';
		} elseif ( has_custom_logo() ) {
			$custom_logo_id = absint( get_theme_mod( 'custom_logo' ) );
			if ( $custom_logo_id ) {
				$data['image_id'] = $custom_logo_id;
				$data['image']    = wp_get_attachment_image_url( $custom_logo_id, 'aiad_social' ) ?: '';
			}
		}

	} elseif ( is_singular( 'resource' ) || is_singular( 'partner' ) || is_singular( 'timeline' ) ) {
		global $post;
		$data['title'] = sprintf( '%s — %s', get_the_title( $post ), $site_name );
		$data['url']   = get_permalink( $post );
		$data['type']  = 'article';

		$subtitle = get_post_meta( $post->ID, '_aiad_subtitle', true );
		if ( $subtitle ) {
			$data['description'] = $subtitle;
		} elseif ( has_excerpt( $post ) ) {
			$data['description'] = get_the_excerpt( $post );
		} else {
			$data['description'] = wp_trim_words( strip_shortcodes( $post->post_content ), 25, '…' );
		}

		if ( has_post_thumbnail( $post ) ) {
			$data['image_id'] = get_post_thumbnail_id( $post );
			$data['image']    = wp_get_attachment_image_url( $data['image_id'], 'aiad_social' ) ?: '';
		} else {
			$logo_id = absint( get_theme_mod( 'aiad_hero_logo', 0 ) );
			if ( $logo_id ) {
				$data['image_id'] = $logo_id;
				$data['image']    = wp_get_attachment_image_url( $logo_id, 'aiad_social' ) ?: '';
			}
		}

	} elseif ( is_post_type_archive() ) {
		$post_type     = get_post_type();
		$archive_title = post_type_archive_title( '', false );
		$data['title'] = sprintf( '%s — %s', $archive_title ?: ucfirst( $post_type ), $site_name );
		$data['url']   = get_post_type_archive_link( $post_type );

		if ( 'resource' === $post_type ) {
			$data['description'] = __( 'Lesson starters, lesson activities, and assembly materials for AI Awareness Day.', 'ai-awareness-day' );
		} elseif ( 'partner' === $post_type ) {
			$data['description'] = __( 'Schools, tech companies, sponsors, and educators supporting AI Awareness Day.', 'ai-awareness-day' );
		} else {
			$data['description'] = get_bloginfo( 'description' );
		}

		$logo_id = absint( get_theme_mod( 'aiad_hero_logo', 0 ) );
		if ( $logo_id ) {
			$data['image_id'] = $logo_id;
			$data['image']    = wp_get_attachment_image_url( $logo_id, 'aiad_social' ) ?: '';
		}

	} else {
		// Default fallback
		$data['title']       = $site_name;
		$data['description'] = get_bloginfo( 'description' );
		$data['url']         = is_singular() ? get_permalink() : home_url( '/' );

		$logo_id = absint( get_theme_mod( 'aiad_hero_logo', 0 ) );
		if ( $logo_id ) {
			$data['image_id'] = $logo_id;
			$data['image']    = wp_get_attachment_image_url( $logo_id, 'aiad_social' ) ?: '';
		}
	}

	// Normalise description: strip HTML and collapse whitespace for all contexts
	if ( ! empty( $data['description'] ) ) {
		$data['description'] = wp_strip_all_tags( $data['description'] );
		$data['description'] = trim( preg_replace( '/\s+/', ' ', $data['description'] ) );
	}

	return $data;
}

/**
 * Generate context-specific share message for Web Share API.
 *
 * @param string   $context Context: 'resource', 'front_page', 'archive', 'timeline'.
 * @param WP_Post|null $post Post object for resource context.
 * @return string Share message with {URL} placeholder.
 */
function aiad_get_share_message( string $context, ?WP_Post $post = null ): string {
	$defaults = aiad_get_customizer_defaults();
	
	switch ( $context ) {
		case 'resource':
			if ( ! $post ) {
				global $post;
			}
			if ( ! $post ) {
				return sprintf( __( 'I found this free AI activity for AI Awareness Day 👉 {URL}', 'ai-awareness-day' ) );
			}
			
			// Get key stage
			$key_stages = (array) get_post_meta( $post->ID, '_aiad_key_stage', true );
			$key_stage_labels = array();
			if ( function_exists( 'aiad_key_stage_options' ) && ! empty( $key_stages ) ) {
				$ks_options = aiad_key_stage_options();
				foreach ( $key_stages as $ks ) {
					if ( isset( $ks_options[ $ks ] ) ) {
						$key_stage_labels[] = $ks_options[ $ks ];
					}
				}
			}
			$key_stage_str = ! empty( $key_stage_labels ) ? implode( '/', $key_stage_labels ) : '';
			
			// Get duration
			$duration_str = '';
			$duration_terms = get_the_terms( $post->ID, 'resource_duration' );
			if ( $duration_terms && ! is_wp_error( $duration_terms ) && function_exists( 'aiad_duration_badge_label' ) ) {
				$duration_str = aiad_duration_badge_label( $duration_terms[0] );
			} elseif ( $duration_terms && ! is_wp_error( $duration_terms ) ) {
				$duration_str = $duration_terms[0]->name;
			} else {
				$duration_meta = get_post_meta( $post->ID, '_aiad_duration', true );
				if ( $duration_meta ) {
					$duration_str = $duration_meta;
				}
			}
			
			// Build message
			if ( $key_stage_str && $duration_str ) {
				return sprintf(
					__( 'I found this free AI activity for %s — takes just %s. Worth a look for AI Awareness Day 👉 {URL}', 'ai-awareness-day' ),
					$key_stage_str,
					$duration_str
				);
			} elseif ( $key_stage_str ) {
				return sprintf(
					__( 'I found this free AI activity for %s. Worth a look for AI Awareness Day 👉 {URL}', 'ai-awareness-day' ),
					$key_stage_str
				);
			} elseif ( $duration_str ) {
				return sprintf(
					__( 'I found this free AI activity — takes just %s. Worth a look for AI Awareness Day 👉 {URL}', 'ai-awareness-day' ),
					$duration_str
				);
			}
			return __( 'I found this free AI activity for AI Awareness Day 👉 {URL}', 'ai-awareness-day' );
			
		case 'front_page':
			$event_date = get_theme_mod( 'aiad_hero_date', $defaults['aiad_hero_date'] );
			if ( $event_date ) {
				return sprintf(
					__( 'Our school is taking part in AI Awareness Day (%s). Free resources for every key stage 👉 {URL}', 'ai-awareness-day' ),
					$event_date
				);
			}
			return __( 'Our school is taking part in AI Awareness Day. Free resources for every key stage 👉 {URL}', 'ai-awareness-day' );
			
		case 'archive':
			return __( 'Check out these free AI resources for AI Awareness Day 👉 {URL}', 'ai-awareness-day' );
			
		case 'timeline':
			// Timeline entries use their title as the message (handled in timeline.js)
			return '';
			
		default:
			return __( 'Check out AI Awareness Day 👉 {URL}', 'ai-awareness-day' );
	}
}

/**
 * Output Open Graph and Twitter Card meta tags.
 */
function aiad_output_og_tags(): void {
	if ( ! aiad_sharing_should_output() ) {
		return;
	}
	
	$og_data = aiad_get_og_data();
	
	if ( empty( $og_data['title'] ) ) {
		return;
	}
	
	// Open Graph tags
	echo '<meta property="og:title" content="' . esc_attr( $og_data['title'] ) . '" />' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $og_data['description'] ) . '" />' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $og_data['url'] ) . '" />' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( $og_data['type'] ) . '" />' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( $og_data['site_name'] ) . '" />' . "\n";
	echo '<meta property="og:locale" content="en_GB" />' . "\n";
	
	if ( ! empty( $og_data['image'] ) ) {
		echo '<meta property="og:image" content="' . esc_url( $og_data['image'] ) . '" />' . "\n";
		$image_id = ! empty( $og_data['image_id'] ) ? (int) $og_data['image_id'] : 0;
		if ( $image_id ) {
			$image_meta = wp_get_attachment_metadata( $image_id );
			if ( isset( $image_meta['width'], $image_meta['height'] ) ) {
				echo '<meta property="og:image:width" content="' . esc_attr( $image_meta['width'] ) . '" />' . "\n";
				echo '<meta property="og:image:height" content="' . esc_attr( $image_meta['height'] ) . '" />' . "\n";
			}
			$image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
			if ( $image_alt ) {
				echo '<meta property="og:image:alt" content="' . esc_attr( $image_alt ) . '" />' . "\n";
			}
		}
	}
	
	// Twitter Card tags
	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $og_data['title'] ) . '" />' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $og_data['description'] ) . '" />' . "\n";
	if ( ! empty( $og_data['image'] ) ) {
		echo '<meta name="twitter:image" content="' . esc_url( $og_data['image'] ) . '" />' . "\n";
	}
	
	// Article metadata for singular posts (improves freshness signals)
	if ( 'article' === $og_data['type'] && is_singular() ) {
		global $post;
		if ( $post ) {
			echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c', $post ) ) . '" />' . "\n";
			echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c', $post ) ) . '" />' . "\n";
		}
	}
}
add_action( 'wp_head', 'aiad_output_og_tags', 5 );
