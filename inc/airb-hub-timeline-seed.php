<?php
/**
 * Mirror AIRB benchmark hub Pages as draft Timeline entries.
 *
 * Canonical benchmark links still use WordPress Pages (flat URLs). Timeline copies
 * live at /timeline/{slug}/ as drafts until URLs are swapped in a future migration.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Timeline auto_type value for benchmark hub resource mirrors.
 */
function aiad_airb_hub_timeline_auto_type(): string {
	return 'hub_resource';
}

/**
 * Whether a timeline entry is a mirrored benchmark hub resource.
 */
function aiad_is_hub_resource_timeline( ?WP_Post $post = null ): bool {
	if ( null === $post ) {
		if ( ! is_singular( 'timeline' ) ) {
			return false;
		}
		$post = get_queried_object();
	}

	if ( ! $post instanceof WP_Post || 'timeline' !== $post->post_type ) {
		return false;
	}

	return aiad_airb_hub_timeline_auto_type() === (string) get_post_meta( $post->ID, '_aiad_timeline_auto_type', true );
}

/**
 * Apply timeline meta for a hub resource mirror.
 */
function aiad_set_airb_hub_timeline_meta( int $timeline_id, int $page_id, string $slug ): void {
	update_post_meta( $timeline_id, '_aiad_timeline_source', 'manual' );
	update_post_meta( $timeline_id, '_aiad_timeline_icon', 'resource' );
	update_post_meta( $timeline_id, '_aiad_timeline_auto_type', aiad_airb_hub_timeline_auto_type() );
	update_post_meta( $timeline_id, '_aiad_timeline_related_id', max( 0, $page_id ) );
	update_post_meta( $timeline_id, '_aiad_timeline_hub_slug', sanitize_title( $slug ) );
}

/**
 * Ensure the Benchmark resources timeline category exists.
 *
 * @return int Term ID or 0.
 */
function aiad_ensure_airb_hub_timeline_category(): int {
	if ( ! taxonomy_exists( 'timeline_category' ) ) {
		return 0;
	}

	$term = term_exists( 'benchmark-resources', 'timeline_category' );
	if ( is_array( $term ) && ! empty( $term['term_id'] ) ) {
		return (int) $term['term_id'];
	}

	$created = wp_insert_term(
		__( 'Benchmark resources', 'ai-awareness-day' ),
		'timeline_category',
		array(
			'slug' => 'benchmark-resources',
		)
	);

	if ( is_wp_error( $created ) || empty( $created['term_id'] ) ) {
		return 0;
	}

	return (int) $created['term_id'];
}

/**
 * Source row for one hub resource (prefer live Page content when present).
 *
 * @return array{slug:string,title:string,excerpt:string,content:string,page_id:int}|null
 */
function aiad_airb_hub_timeline_source_row( array $def ): ?array {
	$slug = sanitize_title( (string) ( $def['slug'] ?? '' ) );
	if ( '' === $slug ) {
		return null;
	}

	$page    = get_page_by_path( $slug, OBJECT, 'page' );
	$page_id = $page instanceof WP_Post ? (int) $page->ID : 0;

	$title   = $page instanceof WP_Post ? (string) $page->post_title : (string) ( $def['title'] ?? $slug );
	$excerpt = $page instanceof WP_Post ? (string) $page->post_excerpt : (string) ( $def['excerpt'] ?? '' );
	$content = $page instanceof WP_Post ? (string) $page->post_content : (string) ( $def['content'] ?? '' );

	if ( '' === trim( $content ) && class_exists( 'AIRB_Hub_Content' ) ) {
		$cta     = __( 'Take the free AI Risk & Readiness Benchmark™', 'ai-risk-benchmark' );
		$content = AIRB_Hub_Content::for_slug( $slug, $excerpt, $cta );
	}

	return array(
		'slug'    => $slug,
		'title'   => $title,
		'excerpt' => $excerpt,
		'content' => $content,
		'page_id' => $page_id,
	);
}

/**
 * Create or update one draft timeline mirror for a hub page.
 *
 * @return int Timeline post ID or 0.
 */
function aiad_ensure_airb_hub_timeline_entry( array $def ): int {
	$row = aiad_airb_hub_timeline_source_row( $def );
	if ( null === $row ) {
		return 0;
	}

	$existing = get_page_by_path( $row['slug'], OBJECT, 'timeline' );
	if ( $existing instanceof WP_Post ) {
		wp_update_post(
			array(
				'ID'           => (int) $existing->ID,
				'post_title'   => $row['title'],
				'post_excerpt' => $row['excerpt'],
				'post_content' => $row['content'],
			)
		);
		aiad_set_airb_hub_timeline_meta( (int) $existing->ID, $row['page_id'], $row['slug'] );
		$term_id = aiad_ensure_airb_hub_timeline_category();
		if ( $term_id > 0 ) {
			wp_set_object_terms( (int) $existing->ID, array( $term_id ), 'timeline_category', false );
		}
		return (int) $existing->ID;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'timeline',
			'post_title'   => $row['title'],
			'post_name'    => $row['slug'],
			'post_excerpt' => $row['excerpt'],
			'post_content' => $row['content'],
			'post_status'  => 'draft',
			'post_author'  => 1,
		),
		true
	);

	if ( ! $post_id || is_wp_error( $post_id ) ) {
		return 0;
	}

	aiad_set_airb_hub_timeline_meta( (int) $post_id, $row['page_id'], $row['slug'] );

	$term_id = aiad_ensure_airb_hub_timeline_category();
	if ( $term_id > 0 ) {
		wp_set_object_terms( (int) $post_id, array( $term_id ), 'timeline_category', false );
	}

	return (int) $post_id;
}

/**
 * One-time seed: draft timeline mirrors for all AIRB hub pages.
 */
function aiad_seed_airb_hub_timeline_entries(): void {
	if ( ! class_exists( 'AIRB_Defaults' ) || ! method_exists( 'AIRB_Defaults', 'hub_page_definitions' ) ) {
		return;
	}

	if ( 'yes' === get_option( 'aiad_airb_hub_timeline_seeded_v1' ) ) {
		return;
	}

	$created = 0;
	foreach ( AIRB_Defaults::hub_page_definitions() as $def ) {
		if ( aiad_ensure_airb_hub_timeline_entry( $def ) ) {
			++$created;
		}
	}

	if ( $created > 0 ) {
		update_option( 'aiad_airb_hub_timeline_seeded_v1', 'yes', false );
	}
}
add_action( 'init', 'aiad_seed_airb_hub_timeline_entries', 35 );

/**
 * Register hub mirror meta for REST/admin visibility.
 */
function aiad_register_airb_hub_timeline_meta(): void {
	register_post_meta(
		'timeline',
		'_aiad_timeline_hub_slug',
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_title',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', 'aiad_register_airb_hub_timeline_meta', 16 );
