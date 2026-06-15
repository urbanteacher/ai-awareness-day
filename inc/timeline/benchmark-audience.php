<?php
/**
 * Timeline benchmark audience — tag posts for role-specific "More to read" outcomes.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Decode timeline post titles for plain-text UI labels (avoids &#8216; showing in cards).
 */
function aiad_timeline_post_link_label( WP_Post $post ): string {
	return wp_specialchars_decode( (string) get_the_title( $post ), ENT_QUOTES );
}

/**
 * Audience taxonomy slug.
 */
function aiad_timeline_audience_taxonomy(): string {
	return 'timeline_audience';
}

/**
 * Role slugs used by the benchmark and in the admin UI.
 *
 * @return array<string, string>
 */
function aiad_timeline_audience_role_options(): array {
	return array(
		'student'       => __( 'Students', 'ai-awareness-day' ),
		'parent'        => __( 'Parents & carers', 'ai-awareness-day' ),
		'teacher'       => __( 'Teachers', 'ai-awareness-day' ),
		'leader'        => __( 'School leaders', 'ai-awareness-day' ),
		'support_staff' => __( 'Education support staff', 'ai-awareness-day' ),
	);
}

/**
 * Register audience taxonomy for timeline entries.
 */
function aiad_register_timeline_audience_taxonomy(): void {
	register_taxonomy(
		aiad_timeline_audience_taxonomy(),
		'timeline',
		array(
			'labels'            => array(
				'name'          => __( 'Benchmark audience', 'ai-awareness-day' ),
				'singular_name' => __( 'Benchmark audience', 'ai-awareness-day' ),
				'add_new_item'  => __( 'Add audience', 'ai-awareness-day' ),
				'search_items'  => __( 'Search audiences', 'ai-awareness-day' ),
				'all_items'     => __( 'All audiences', 'ai-awareness-day' ),
				'edit_item'     => __( 'Edit audience', 'ai-awareness-day' ),
				'update_item'   => __( 'Update audience', 'ai-awareness-day' ),
			),
			'hierarchical'      => false,
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'meta_box_cb'       => false,
		)
	);
}
add_action( 'init', 'aiad_register_timeline_audience_taxonomy', 12 );

/**
 * Register pin meta for benchmark outcome links.
 */
function aiad_register_timeline_benchmark_audience_meta(): void {
	register_post_meta(
		'timeline',
		'_airb_benchmark_outcome_pin',
		array(
			'type'              => 'boolean',
			'single'            => true,
			'default'           => false,
			'show_in_rest'      => true,
			'sanitize_callback' => static function ( $value ) {
				return (bool) $value;
			},
			'auth_callback'     => static function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', 'aiad_register_timeline_benchmark_audience_meta', 16 );

/**
 * Ensure default audience terms exist.
 */
function aiad_seed_timeline_audience_terms(): void {
	if ( ! taxonomy_exists( aiad_timeline_audience_taxonomy() ) ) {
		return;
	}

	foreach ( aiad_timeline_audience_role_options() as $slug => $label ) {
		if ( ! term_exists( $slug, aiad_timeline_audience_taxonomy() ) ) {
			wp_insert_term( $label, aiad_timeline_audience_taxonomy(), array( 'slug' => $slug ) );
		}
	}
}
add_action( 'init', 'aiad_seed_timeline_audience_terms', 13 );

/**
 * Launch articles for benchmark "More to read" — slug + optional path fallback.
 *
 * @return array<string, array<int, array{slug: string, path?: string}>>
 */
function aiad_timeline_benchmark_audience_launch_map(): array {
	return array(
		'student' => array(
			array(
				'slug' => 'stop-asking-if-students-should-use-ai-start-asking-how-students-perspective',
				'path' => '/timeline/stop-asking-if-students-should-use-ai-start-asking-how-students-perspective/',
			),
			array(
				'slug' => 'ai-mental-health-student-perspective-student-voice',
				'path' => '/timeline/ai-mental-health-student-perspective-student-voice/',
			),
			array(
				'slug' => 'the-future-of-ai-through-a-students-perspective',
				'path' => '/timeline/the-future-of-ai-through-a-students-perspective/',
			),
		),
		'parent'  => array(
			array(
				'slug' => 'parent-tips',
				'path' => '/timeline/parent-tips/',
			),
			array(
				'slug' => 'bbc-bitesize-ai-awareness-day-teaching-resources',
				'path' => '/timeline/bbc-bitesize-ai-awareness-day-teaching-resources/',
			),
			array(
				'slug' => 'parent-zone',
				'path' => '/timeline/parent-zone/',
			),
		),
		'teacher' => array(
			array(
				'slug' => 'misinformation-detector-teachers',
				'path' => '/timeline/misinformation-detector-teachers/',
			),
			array(
				'slug' => '15-ai-buzzwords-teachers-2026',
				'path' => '/timeline/15-ai-buzzwords-teachers-2026/',
			),
			array(
				'slug' => 'how-does-a-large-language-model-work',
				'path' => '/timeline/how-does-a-large-language-model-work/',
			),
		),
		'leader'  => array(
			array(
				'slug' => 'ai-micro-credentials-and-short-courses',
				'path' => '/timeline/ai-micro-credentials-and-short-courses/',
			),
			array(
				'slug' => '🔥🔥teachers-have-you-heard-of-ai-agents-yet🔥🔥',
				'path' => '/timeline/teachers-have-you-heard-of-ai-agents-yet/',
			),
			array(
				'slug' => 'beyond-the-holy-grail',
				'path' => '/timeline/beyond-the-holy-grail/',
			),
		),
	);
}

/**
 * Resolve a timeline post from slug and/or path fragment.
 *
 * @param array{slug?: string, path?: string} $item Launch map entry.
 */
function aiad_timeline_find_benchmark_audience_post( array $item ): ?WP_Post {
	$slug = (string) ( $item['slug'] ?? '' );
	if ( '' !== $slug ) {
		$post = get_page_by_path( $slug, OBJECT, 'timeline' );
		if ( $post instanceof WP_Post ) {
			return $post;
		}
	}

	$path = (string) ( $item['path'] ?? '' );
	if ( '' !== $path ) {
		$path_slug = trim( basename( untrailingslashit( wp_parse_url( $path, PHP_URL_PATH ) ?: '' ) ) );
		if ( '' !== $path_slug ) {
			$post = get_page_by_path( $path_slug, OBJECT, 'timeline' );
			if ( $post instanceof WP_Post ) {
				return $post;
			}
			$query = new WP_Query(
				array(
					'post_type'              => 'timeline',
					'post_status'            => 'any',
					'name'                     => $path_slug,
					'posts_per_page'           => 1,
					'no_found_rows'            => true,
					'update_post_meta_cache'   => false,
					'update_post_term_cache'   => false,
				)
			);
			if ( ! empty( $query->posts[0] ) && $query->posts[0] instanceof WP_Post ) {
				return $query->posts[0];
			}
		}
	}

	if ( '' !== $slug && str_contains( $slug, 'ai-agents' ) ) {
		$query = new WP_Query(
			array(
				'post_type'              => 'timeline',
				'post_status'            => 'any',
				's'                        => 'AI Agents yet',
				'posts_per_page'           => 1,
				'no_found_rows'            => true,
				'update_post_meta_cache'   => false,
				'update_post_term_cache'   => false,
			)
		);
		if ( ! empty( $query->posts[0] ) && $query->posts[0] instanceof WP_Post ) {
			return $query->posts[0];
		}
	}

	return null;
}

/**
 * Assign benchmark audience term(s) to a timeline post (merge, do not replace).
 *
 * @param int    $post_id Post ID.
 * @param string $role    Audience slug.
 */
function aiad_timeline_assign_benchmark_audience( int $post_id, string $role ): void {
	$role = sanitize_key( $role );
	if ( $post_id <= 0 || ! taxonomy_exists( aiad_timeline_audience_taxonomy() ) ) {
		return;
	}
	if ( ! isset( aiad_timeline_audience_role_options()[ $role ] ) ) {
		return;
	}

	$existing = wp_get_object_terms( $post_id, aiad_timeline_audience_taxonomy(), array( 'fields' => 'slugs' ) );
	if ( is_wp_error( $existing ) ) {
		$existing = array();
	}

	$merged = array_values( array_unique( array_merge( $existing, array( $role ) ) ) );
	wp_set_object_terms( $post_id, $merged, aiad_timeline_audience_taxonomy(), false );
}

/**
 * Assign launch benchmark articles to their audiences.
 *
 * @return int Number of posts tagged.
 */
function aiad_timeline_assign_benchmark_audience_launch_posts(): int {
	aiad_seed_timeline_audience_terms();

	$tagged = 0;
	foreach ( aiad_timeline_benchmark_audience_launch_map() as $role => $items ) {
		foreach ( $items as $item ) {
			$post = aiad_timeline_find_benchmark_audience_post( $item );
			if ( ! ( $post instanceof WP_Post ) ) {
				continue;
			}
			aiad_timeline_assign_benchmark_audience( (int) $post->ID, $role );
			++$tagged;
		}
	}

	return $tagged;
}

/**
 * One-time: assign audiences to the launch benchmark outcome articles.
 */
function aiad_seed_timeline_benchmark_audience_posts(): void {
	if ( get_option( 'aiad_timeline_benchmark_audience_seeded_v2' ) === 'yes' ) {
		return;
	}

	aiad_timeline_assign_benchmark_audience_launch_posts();

	update_option( 'aiad_timeline_benchmark_audience_seeded_v2', 'yes' );
}
add_action( 'init', 'aiad_seed_timeline_benchmark_audience_posts', 20 );

/**
 * Seeded ordering for benchmark read links (stable per submission).
 *
 * @param WP_Post[] $posts Timeline posts.
 * @param int       $seed  Seed value.
 * @return WP_Post[]
 */
function aiad_timeline_seeded_shuffle_posts( array $posts, int $seed ): array {
	if ( count( $posts ) <= 1 ) {
		return $posts;
	}

	usort(
		$posts,
		static function ( WP_Post $a, WP_Post $b ) use ( $seed ): int {
			$ha = crc32( $seed . '|' . $a->ID );
			$hb = crc32( $seed . '|' . $b->ID );
			return $ha <=> $hb;
		}
	);

	return $posts;
}

/**
 * Build benchmark "More to read" links for a role from tagged timeline posts.
 *
 * @param string $role  student|parent|teacher|leader|support_staff.
 * @param int    $limit Max links.
 * @param int    $seed  Stable shuffle seed (e.g. submission ID).
 * @return array<int, array{label: string, url: string}>
 */
function aiad_timeline_benchmark_read_links( string $role, int $limit = 3, int $seed = 0 ): array {
	$role = sanitize_key( $role );
	if ( ! isset( aiad_timeline_audience_role_options()[ $role ] ) ) {
		return array();
	}

	$limit = max( 1, min( 6, $limit ) );
	$seed  = 0 !== $seed ? $seed : (int) crc32( $role . '|' . gmdate( 'Y-m-d' ) );

	$outcome_only = ( 'support_staff' === $role );

	$pinned_query = new WP_Query(
		array(
			'post_type'              => 'timeline',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => aiad_timeline_audience_taxonomy(),
					'field'    => 'slug',
					'terms'    => array( $role ),
				),
			),
			'meta_query'             => array(
				array(
					'key'   => '_airb_benchmark_outcome_pin',
					'value' => '1',
				),
			),
		)
	);

	if ( $outcome_only ) {
		$chosen = array();
		foreach ( $pinned_query->posts as $post ) {
			if ( ! ( $post instanceof WP_Post ) ) {
				continue;
			}
			$url = get_permalink( $post );
			if ( ! is_string( $url ) || '' === $url ) {
				continue;
			}
			$image = get_the_post_thumbnail_url( $post, 'thumbnail' );
			$chosen[] = array(
				'label' => aiad_timeline_post_link_label( $post ),
				'url'   => $url,
				'image' => is_string( $image ) ? $image : '',
			);
			if ( count( $chosen ) >= $limit ) {
				break;
			}
		}
		wp_reset_postdata();
		return $chosen;
	}

	$pool_query = new WP_Query(
		array(
			'post_type'              => 'timeline',
			'post_status'            => 'publish',
			'posts_per_page'         => 40,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => aiad_timeline_audience_taxonomy(),
					'field'    => 'slug',
					'terms'    => array( $role ),
				),
			),
			'meta_query'             => array(
				'relation' => 'OR',
				array(
					'key'     => '_airb_benchmark_outcome_pin',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => '_airb_benchmark_outcome_pin',
					'value'   => '1',
					'compare' => '!=',
				),
			),
		)
	);

	$chosen  = array();
	$seen    = array();
	$pinned  = $pinned_query->posts;
	$pool    = aiad_timeline_seeded_shuffle_posts( $pool_query->posts, $seed );
	$ordered = array_merge( $pinned, $pool );

	foreach ( $ordered as $post ) {
		if ( ! ( $post instanceof WP_Post ) ) {
			continue;
		}
		if ( isset( $seen[ $post->ID ] ) ) {
			continue;
		}
		$url = get_permalink( $post );
		if ( ! is_string( $url ) || '' === $url ) {
			continue;
		}
		$seen[ $post->ID ] = true;
		$image             = get_the_post_thumbnail_url( $post, 'thumbnail' );
		$chosen[]          = array(
			'label' => aiad_timeline_post_link_label( $post ),
			'url'   => $url,
			'image' => is_string( $image ) ? $image : '',
		);
		if ( count( $chosen ) >= $limit ) {
			break;
		}
	}

	wp_reset_postdata();

	return $chosen;
}
