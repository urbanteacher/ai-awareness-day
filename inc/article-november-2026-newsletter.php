<?php
/**
 * One-time: publish the November 2026 newsletter (Oracy is back, plus the October round-up) as a campaign update, the way the October issue
 * is at /timeline/october-newsletter/ (this one at /timeline/november-2026/). The text is inc/articles/november-2026-newsletter.html, the same words as the
 * Beehiiv issue (archive/newsletter/2026-11-november/newsletter-copy.md). Its banners, in
 * assets/images/newsletter/2026-11/ (from archive/design-kit/nov-banners.html), are imported into the media library and
 * the IMG:{name} placeholders replaced with their addresses; the text-free website-cover.jpg becomes the featured image. Runs once
 * on the first request after a deploy; the update can then be edited or trashed in the editor without this putting it
 * back.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Publish the update once, with its banners, excerpt, topic (Campaign news) and featured image.
 */
function aiad_migrate_november_2026_newsletter(): void {
	if ( get_option( 'aiad_newsletter_2026_11_added' ) === '1' || ! post_type_exists( 'timeline' ) || ! taxonomy_exists( 'timeline_category' ) ) {
		return;
	}
	$slug = 'november-2026';
	if ( get_page_by_path( $slug, OBJECT, 'timeline' ) ) {
		update_option( 'aiad_newsletter_2026_11_added', '1' );
		return;
	}
	$file = AIAD_DIR . '/inc/articles/november-2026-newsletter.html';
	if ( ! is_readable( $file ) ) {
		return;
	}
	$content = str_replace( 'SITE_URI', untrailingslashit( home_url() ), (string) file_get_contents( $file ) );

	kses_remove_filters();
	$id = wp_insert_post(
		array(
			'post_type'    => 'timeline',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => 'November Newsletter: Oracy Is Back on the Curriculum',
			'post_excerpt' => 'Oracy joins reading and writing in England’s national curriculum, taught from September 2028: perfect timing for AI Awareness Day 2027. Plus October in one read: BBC Bitesize, a free AI audit, lesson starters and new partners.',
			'post_content' => '',
		)
	);
	kses_init_filters();
	if ( ! $id || is_wp_error( $id ) ) {
		return;
	}

	// The text-free cover is the featured image; each banner is imported and its placeholder pointed at it.
	$dir   = AIAD_DIR . '/assets/images/newsletter/2026-11';
	$cover = aiad_article_import_image( (int) $id, $dir . '/website-cover.jpg', 'newsletter-2026-11-cover.jpg', 'The five AI Awareness Day theme tiles in a loop around a person, on a dark background' );
	if ( $cover ) {
		set_post_thumbnail( $id, $cover );
	}
	preg_match_all( '/IMG:([a-z0-9-]+)/', $content, $found );
	foreach ( array_unique( $found[1] ) as $name ) {
		$attachment = aiad_article_import_image( (int) $id, $dir . '/' . $name . '.png', 'newsletter-2026-11-' . $name . '.png', '' );
		$url        = $attachment ? (string) wp_get_attachment_url( $attachment ) : untrailingslashit( AIAD_URI ) . '/assets/images/newsletter/2026-11/' . $name . '.png';
		$content    = str_replace( 'IMG:' . $name . '"', $url . '"', $content );
	}

	global $wpdb;
	$wpdb->update( $wpdb->posts, array( 'post_content' => $content ), array( 'ID' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	clean_post_cache( $id );

	update_post_meta( $id, '_aiad_timeline_source', 'manual' );
	update_post_meta( $id, '_aiad_timeline_icon', 'announcement' );
	$term = get_term_by( 'slug', 'campaign-news', 'timeline_category' );
	if ( $term ) {
		wp_set_object_terms( $id, array( (int) $term->term_id ), 'timeline_category', false );
	}

	update_option( 'aiad_newsletter_2026_11_added', '1' );
}
add_action( 'init', static function (): void { aiad_article_run_once( 'aiad_newsletter_2026_11_added', 'aiad_migrate_november_2026_newsletter' ); }, 52 );
