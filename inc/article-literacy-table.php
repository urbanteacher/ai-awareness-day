<?php
/**
 * One-time: publish "Introducing the AI Literacy Periodic Table", AI Awareness Day's post about the new table
 * (now in the aiad-core plugin's literacy-table module) and five ways to use it: the National AI Conversation, revising after the AI Risk Readiness
 * Benchmark, display boards, showing a definition to the class, and suggesting new elements. A campaign update (the
 * timeline post type, shown at /timeline/). The text is inc/articles/ai-literacy-periodic-table.html; its text-free cover
 * (assets/images/articles/literacy-table-card.jpg) is made by archive/design-kit/literacy-table-card.html, and its four
 * section banners (assets/images/articles/literacy-table/) by archive/design-kit/literacy-table-banners.html. Runs once on
 * the first request after a deploy; the post can then be edited, retitled or trashed in the editor without this putting
 * it back.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Publish the post once, with its excerpt, topics (Classroom resources, Teaching & learning), audiences and cover.
 */
function aiad_migrate_article_literacy_table(): void {
	if ( get_option( 'aiad_article_literacy_table_added' ) === '1' || ! post_type_exists( 'timeline' ) || ! taxonomy_exists( 'timeline_category' ) ) {
		return;
	}
	$slug = 'ai-literacy-periodic-table-launch';
	if ( get_page_by_path( $slug, OBJECT, 'timeline' ) ) {
		update_option( 'aiad_article_literacy_table_added', '1' );
		return;
	}
	$file = AIAD_DIR . '/inc/articles/ai-literacy-periodic-table.html';
	if ( ! is_readable( $file ) ) {
		return;
	}
	$content = str_replace( array( 'THEME_URI', 'SITE_URI' ), array( untrailingslashit( AIAD_URI ), untrailingslashit( home_url() ) ), (string) file_get_contents( $file ) );
	$dir     = AIAD_DIR . '/assets/images/articles/literacy-table';

	kses_remove_filters();
	$id = wp_insert_post(
		array(
			'post_type'    => 'timeline',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => 'The AI Literacy Periodic Table: 142 AI terms and five ways to use them in school',
			'post_excerpt' => 'A free table of 142 AI terms in plain English, with the issues they raise, a question for class on every one, and ready-made lessons for schools.',
			'post_content' => '',
		)
	);
	kses_init_filters();
	if ( ! $id || is_wp_error( $id ) ) {
		return;
	}

	// Each section banner is imported into the media library and its IMG:{name} placeholder pointed at it.
	preg_match_all( '/IMG:([a-z0-9-]+)/', $content, $found );
	foreach ( array_unique( $found[1] ) as $name ) {
		$attachment = aiad_article_import_image( (int) $id, $dir . '/' . $name . '.png', 'literacy-table-' . $name . '.png', '' );
		$url        = $attachment ? (string) wp_get_attachment_url( $attachment ) : untrailingslashit( AIAD_URI ) . '/assets/images/articles/literacy-table/' . $name . '.png';
		$content    = str_replace( 'IMG:' . $name . '"', $url . '"', $content );
	}
	global $wpdb;
	$wpdb->update( $wpdb->posts, array( 'post_content' => $content ), array( 'ID' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	clean_post_cache( $id );

	update_post_meta( $id, '_aiad_timeline_source', 'manual' );
	update_post_meta( $id, '_aiad_timeline_icon', 'announcement' );

	$cover = aiad_article_import_image( (int) $id, AIAD_DIR . '/assets/images/articles/literacy-table-card.jpg', 'ai-literacy-periodic-table-cover.jpg', 'A periodic table of AI terms on a dark background: chamfered tiles in seven pale colours, some with symbols such as Ai, Ll, Dk, Ag and Pv, and a large pink Hi tile for Human-in-the-Loop in the panel at the top' );
	if ( $cover ) {
		set_post_thumbnail( $id, $cover );
	}

	$terms = array();
	foreach ( array( 'classroom-resources', 'teaching-learning' ) as $topic ) {
		$term = get_term_by( 'slug', $topic, 'timeline_category' );
		if ( $term ) {
			$terms[] = (int) $term->term_id;
		}
	}
	if ( $terms ) {
		wp_set_object_terms( $id, $terms, 'timeline_category', false );
	}
	if ( taxonomy_exists( 'timeline_audience' ) ) {
		$audiences = array();
		foreach ( array( 'teacher', 'student', 'leader' ) as $who ) {
			$term = get_term_by( 'slug', $who, 'timeline_audience' );
			if ( $term ) {
				$audiences[] = (int) $term->term_id;
			}
		}
		if ( $audiences ) {
			wp_set_object_terms( $id, $audiences, 'timeline_audience', false );
		}
	}

	update_option( 'aiad_article_literacy_table_added', '1' );
}
add_action( 'init', static function (): void { aiad_article_run_once( 'aiad_article_literacy_table_added', 'aiad_migrate_article_literacy_table' ); }, 59 );
