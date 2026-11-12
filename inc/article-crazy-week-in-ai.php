<?php
/**
 * One-time: publish "The crazy week in AI" (the seven stories of the week of 9 to 11 November 2026, each with why it
 * matters for teachers) as a campaign update (the timeline post type, shown at /timeline/). The text is
 * inc/articles/crazy-week-in-ai.html, block markup with the sources linked inline. Its seven story banners (assets/images/articles/crazy-week/)
 * are imported into the media library and the IMG:{name} placeholders replaced with their addresses. Made by
 * archive/design-kit/crazy-week.html with render-crazy-week.mjs. Runs once on the first request after a
 * deploy; the post can then be edited, retitled or trashed in the editor without this putting it back.
 *
 * Saved with the filters that strip markup from users who cannot post unfiltered HTML switched off, so the blocks are
 * stored as written. The text-free cover (assets/images/articles/crazy-week-in-ai-card.jpg) becomes the featured image.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Publish the post once, with its excerpt and topics (Research & insight first, then Teaching & learning).
 */
function aiad_migrate_article_crazy_week_in_ai(): void {
	if ( get_option( 'aiad_article_crazy_week_added' ) === '1' || ! post_type_exists( 'timeline' ) || ! taxonomy_exists( 'timeline_category' ) ) {
		return;
	}
	$slug = 'crazy-week-in-ai';
	if ( get_page_by_path( $slug, OBJECT, 'timeline' ) ) {
		update_option( 'aiad_article_crazy_week_added', '1' );
		return;
	}
	$file = AIAD_DIR . '/inc/articles/crazy-week-in-ai.html';
	if ( ! is_readable( $file ) ) {
		return;
	}
	$content = str_replace( array( 'THEME_URI', 'SITE_URI' ), array( untrailingslashit( AIAD_URI ), untrailingslashit( home_url() ) ), (string) file_get_contents( $file ) );
	$dir     = AIAD_DIR . '/assets/images/articles/crazy-week';

	kses_remove_filters();
	$id = wp_insert_post(
		array(
			'post_type'    => 'timeline',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => 'The crazy week in AI',
			'post_excerpt' => 'AI got faster, started acting on its own, and one tool went where it shouldn’t. Here are seven stories from the week, in plain English, each with what it means for teachers.',
			'post_content' => '',
		)
	);
	kses_init_filters();
	if ( ! $id || is_wp_error( $id ) ) {
		return;
	}

	// Each story banner is imported into the media library and its IMG:{name} placeholder pointed at it.
	preg_match_all( '/IMG:([a-z0-9-]+)/', $content, $found );
	foreach ( array_unique( $found[1] ) as $name ) {
		$attachment = aiad_article_import_image( (int) $id, $dir . '/' . $name . '.png', 'crazy-week-' . $name . '.png', '' );
		$url        = $attachment ? (string) wp_get_attachment_url( $attachment ) : untrailingslashit( AIAD_URI ) . '/assets/images/articles/crazy-week/' . $name . '.png';
		$content    = str_replace( 'IMG:' . $name . '"', $url . '"', $content );
	}
	global $wpdb;
	$wpdb->update( $wpdb->posts, array( 'post_content' => $content ), array( 'ID' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	clean_post_cache( $id );

	update_post_meta( $id, '_aiad_timeline_source', 'manual' );
	update_post_meta( $id, '_aiad_timeline_icon', 'announcement' );

	$cover = aiad_article_import_image( (int) $id, AIAD_DIR . '/assets/images/articles/crazy-week-in-ai-card.jpg', 'crazy-week-in-ai.jpg', 'Jensen Huang of NVIDIA between the black Grok Bot ball and a purple and pink voice-waveform orb for Dessa, on a dark gradient' );
	if ( $cover ) {
		set_post_thumbnail( $id, $cover );
	}

	$terms = array();
	foreach ( array( 'research-insight', 'teaching-learning' ) as $topic ) {
		$term = get_term_by( 'slug', $topic, 'timeline_category' );
		if ( $term ) {
			$terms[] = (int) $term->term_id;
		}
	}
	if ( $terms ) {
		wp_set_object_terms( $id, $terms, 'timeline_category', false );
	}

	update_option( 'aiad_article_crazy_week_added', '1' );
	update_option( 'aiad_article_crazy_week_v2', '1' ); // a fresh publish already has the corrected text.
}
add_action( 'init', static function (): void { aiad_article_run_once( 'aiad_article_crazy_week_added', 'aiad_migrate_article_crazy_week_in_ai' ); }, 53 );

/**
 * One-time: bring the live post up to the corrected text after the November 2026 review (claims tied to their sources, a
 * safeguarding line, lines for pupils and parents, plain-English glosses, corrected banners). The post was first published
 * from the earlier text, so this swaps in inc/articles/crazy-week-in-ai.html with fresh copies of the banners, but only
 * if nobody has edited the post in the editor (its modified time is still its published time). An edited post is left alone.
 */
function aiad_sync_article_crazy_week_v2(): void {
	$post = get_page_by_path( 'crazy-week-in-ai', OBJECT, 'timeline' );
	if ( ! $post ) {
		return; // Not published yet: the publish above will use the corrected text.
	}
	$file = AIAD_DIR . '/inc/articles/crazy-week-in-ai.html';
	if ( ! is_readable( $file ) ) {
		return;
	}
	// Edited in the editor since it was published (more than two minutes apart)? Leave it as the editors have it.
	if ( abs( strtotime( $post->post_modified_gmt . ' UTC' ) - strtotime( $post->post_date_gmt . ' UTC' ) ) > 120 ) {
		update_option( 'aiad_article_crazy_week_v2', '1' );
		return;
	}
	$content = str_replace( array( 'THEME_URI', 'SITE_URI' ), array( untrailingslashit( AIAD_URI ), untrailingslashit( home_url() ) ), (string) file_get_contents( $file ) );
	$dir     = AIAD_DIR . '/assets/images/articles/crazy-week';
	preg_match_all( '/IMG:([a-z0-9-]+)/', $content, $found );
	foreach ( array_unique( $found[1] ) as $name ) {
		$attachment = aiad_article_import_image( (int) $post->ID, $dir . '/' . $name . '.png', 'crazy-week-v2-' . $name . '.png', '' );
		$url        = $attachment ? (string) wp_get_attachment_url( $attachment ) : untrailingslashit( AIAD_URI ) . '/assets/images/articles/crazy-week/' . $name . '.png';
		$content    = str_replace( 'IMG:' . $name . '"', $url . '"', $content );
	}
	global $wpdb;
	$wpdb->update( $wpdb->posts, array( 'post_content' => $content ), array( 'ID' => $post->ID ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	clean_post_cache( $post->ID );
	update_option( 'aiad_article_crazy_week_v2', '1' );
}
add_action( 'init', static function (): void { aiad_article_run_once( 'aiad_article_crazy_week_v2', 'aiad_sync_article_crazy_week_v2' ); }, 54 );
