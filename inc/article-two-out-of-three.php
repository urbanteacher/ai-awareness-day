<?php
/**
 * One-time: publish the guest post "The 2 out of 3 principle" by Phil Wickins (Run Don't Walk, an AI Awareness Day partner) as a
 * campaign update (the timeline post type, shown at /timeline/). The text is inc/articles/two-out-of-three-principle.html, edited
 * from Phil's draft so it reads as insight rather than promotion: his resources appear once in the body and in the author box.
 * Its figures (assets/images/articles/two-of-three/: Phil's photo, logo and slides from his draft, and our diagram) are imported into the media library and the IMG:{name} placeholders
 * replaced with their addresses; the text-free cover (assets/images/articles/two-of-three-card.jpg) becomes the featured image.
 * Made by archive/design-kit/two-of-three.html with render-two-of-three.mjs. Runs once on the first request after a deploy; the
 * post can then be edited, retitled or trashed in the editor without this putting it back.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Publish the post once, with its figures, excerpt, topic (Teaching & learning) and cover.
 */
function aiad_migrate_article_two_out_of_three(): void {
	if ( get_option( 'aiad_article_two_of_three_added' ) === '1' || ! post_type_exists( 'timeline' ) || ! taxonomy_exists( 'timeline_category' ) ) {
		return;
	}
	$slug = 'two-out-of-three-principle';
	if ( get_page_by_path( $slug, OBJECT, 'timeline' ) ) {
		update_option( 'aiad_article_two_of_three_added', '1' );
		return;
	}
	$file = AIAD_DIR . '/inc/articles/two-out-of-three-principle.html';
	if ( ! is_readable( $file ) ) {
		return;
	}
	$content = str_replace( array( 'THEME_URI', 'SITE_URI' ), array( untrailingslashit( AIAD_URI ), untrailingslashit( home_url() ) ), (string) file_get_contents( $file ) );
	$dir     = AIAD_DIR . '/assets/images/articles/two-of-three';

	kses_remove_filters();
	$id = wp_insert_post(
		array(
			'post_type'    => 'timeline',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => 'The 2 Out of 3 Principle: keeping humans in the loop with AI',
			'post_excerpt' => 'Split any task into thinking, doing and evaluating, and keep humans in charge of at least two. Then let pupils argue about which two.',
			'post_content' => '',
		)
	);
	kses_init_filters();
	if ( ! $id || is_wp_error( $id ) ) {
		return;
	}

	preg_match_all( '/IMG:([a-z0-9-]+)/', $content, $found );
	foreach ( array_unique( $found[1] ) as $name ) {
		$ext        = is_readable( $dir . '/' . $name . '.png' ) ? 'png' : 'jpg'; // Photos are JPEGs, slides and diagrams PNGs.
		$attachment = aiad_article_import_image( (int) $id, $dir . '/' . $name . '.' . $ext, 'two-of-three-' . $name . '.' . $ext, '' );
		$url        = $attachment ? (string) wp_get_attachment_url( $attachment ) : untrailingslashit( AIAD_URI ) . '/assets/images/articles/two-of-three/' . $name . '.' . $ext;
		$content    = str_replace( 'IMG:' . $name . '"', $url . '"', $content );
	}
	global $wpdb;
	$wpdb->update( $wpdb->posts, array( 'post_content' => $content ), array( 'ID' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	clean_post_cache( $id );

	update_post_meta( $id, '_aiad_timeline_source', 'manual' );
	update_post_meta( $id, '_aiad_timeline_icon', 'announcement' );

	$cover = aiad_article_import_image( (int) $id, AIAD_DIR . '/assets/images/articles/two-of-three-card.jpg', 'two-out-of-three-principle.jpg', 'Phil Wickins on a dark background inside a ring of three arcs: a lightbulb on a solid blue arc, a dashed arc with an AI sparkles icon at the top, and a tick on a solid purple arc' );
	if ( $cover ) {
		set_post_thumbnail( $id, $cover );
	}

	$term = get_term_by( 'slug', 'teaching-learning', 'timeline_category' );
	if ( $term ) {
		wp_set_object_terms( $id, array( (int) $term->term_id ), 'timeline_category', false );
	}

	update_option( 'aiad_article_two_of_three_added', '1' );
}
add_action( 'init', static function (): void { aiad_article_run_once( 'aiad_article_two_of_three_added', 'aiad_migrate_article_two_out_of_three' ); }, 55 );
