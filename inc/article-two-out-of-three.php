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

	$cover = aiad_article_import_image( (int) $id, AIAD_DIR . '/assets/images/articles/two-of-three-card.jpg', 'two-out-of-three-principle.jpg', 'Phil Wickins on an orange, pink and purple gradient beside an orange card reading AI Awareness Day 2027, Keep Humans in the Loop, Thursday 29th April 2027' );
	if ( $cover ) {
		set_post_thumbnail( $id, $cover );
	}

	$term = get_term_by( 'slug', 'teaching-learning', 'timeline_category' );
	if ( $term ) {
		wp_set_object_terms( $id, array( (int) $term->term_id ), 'timeline_category', false );
	}

	update_option( 'aiad_article_two_of_three_added', '1' );
	update_option( 'aiad_article_two_of_three_v2', '1' ); // a fresh publish already has the photo cover.
}
add_action( 'init', static function (): void { aiad_article_run_once( 'aiad_article_two_of_three_added', 'aiad_migrate_article_two_out_of_three' ); }, 55 );

/**
 * One-time (revision 2): the post went live with the loop cover and Phil's card photo in the body. Make that photo the cover
 * (Phil and his card cut out onto the brand gradient) and take it out of the body. Only if nobody has edited the post in the editor (its modified time is
 * still its published time); an edited post is left alone. Figures already on the post are reused by name.
 */
function aiad_sync_article_two_of_three_v2(): void {
	$post = get_page_by_path( 'two-out-of-three-principle', OBJECT, 'timeline' );
	if ( ! $post ) {
		return; // Not published yet: the publish above will use the current text and cover.
	}
	$file = AIAD_DIR . '/inc/articles/two-out-of-three-principle.html';
	if ( ! is_readable( $file ) ) {
		return;
	}
	if ( abs( strtotime( $post->post_modified_gmt . ' UTC' ) - strtotime( $post->post_date_gmt . ' UTC' ) ) > 120 ) {
		update_option( 'aiad_article_two_of_three_v2', '1' );
		return;
	}
	$content = str_replace( array( 'THEME_URI', 'SITE_URI' ), array( untrailingslashit( AIAD_URI ), untrailingslashit( home_url() ) ), (string) file_get_contents( $file ) );
	$dir     = AIAD_DIR . '/assets/images/articles/two-of-three';
	preg_match_all( '/<img[^>]+src="([^"]+)"/', (string) $post->post_content, $have );
	preg_match_all( '/IMG:([a-z0-9-]+)/', $content, $found );
	foreach ( array_unique( $found[1] ) as $name ) {
		$url = '';
		foreach ( $have[1] as $src ) {
			if ( false !== strpos( $src, 'two-of-three-' . $name ) ) {
				$url = $src;
				break;
			}
		}
		if ( '' === $url ) {
			$ext        = is_readable( $dir . '/' . $name . '.png' ) ? 'png' : 'jpg';
			$attachment = aiad_article_import_image( (int) $post->ID, $dir . '/' . $name . '.' . $ext, 'two-of-three-' . $name . '.' . $ext, '' );
			$url        = $attachment ? (string) wp_get_attachment_url( $attachment ) : untrailingslashit( AIAD_URI ) . '/assets/images/articles/two-of-three/' . $name . '.' . $ext;
		}
		$content = str_replace( 'IMG:' . $name . '"', $url . '"', $content );
	}
	global $wpdb;
	$wpdb->update( $wpdb->posts, array( 'post_content' => $content ), array( 'ID' => $post->ID ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	clean_post_cache( $post->ID );

	$cover = aiad_article_import_image( (int) $post->ID, AIAD_DIR . '/assets/images/articles/two-of-three-card.jpg', 'two-out-of-three-principle-cover.jpg', 'Phil Wickins on an orange, pink and purple gradient beside an orange card reading AI Awareness Day 2027, Keep Humans in the Loop, Thursday 29th April 2027' );
	if ( $cover ) {
		update_post_meta( $post->ID, '_thumbnail_id', $cover ); // set_post_thumbnail() would also work; this leaves the modified time alone.
	}
	update_option( 'aiad_article_two_of_three_v2', '1' );
}
add_action( 'init', static function (): void { aiad_article_run_once( 'aiad_article_two_of_three_v2', 'aiad_sync_article_two_of_three_v2' ); }, 56 );
