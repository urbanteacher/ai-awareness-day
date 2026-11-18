<?php
/**
 * One-time: publish "The Milburn Review: why work-readiness should include AI literacy", AI Awareness Day's response to Alan Milburn's
 * final report on young people and work (17 November 2026), as a campaign update (the timeline post type, shown at /timeline/).
 * The text is inc/articles/milburn-review-ai-work-readiness.html. Its figure (assets/images/articles/milburn/) is imported into
 * the media library and the IMG:{name} placeholder replaced with its address; the text-free cover
 * (assets/images/articles/milburn-card.jpg, Alan Milburn's official portrait, UK Government, OGL v3) becomes the featured image.
 * Both are made by archive/design-kit/milburn.html with render-milburn.mjs. Runs once on the first request after a deploy; the post can then be edited, retitled or trashed in the
 * editor without this putting it back.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The post's title and excerpt (its standfirst), shared by the publish and the revision below.
 *
 * @return array{title: string, excerpt: string}
 */
function aiad_article_milburn_fields(): array {
	return array(
		'title'   => 'The Milburn Review: why work-readiness should include AI literacy',
		'excerpt' => 'Milburn proposes AI skills training available to every young person, but does not define those skills. Here is what they could look like in class.',
	);
}

/**
 * Publish the post once, with its figure, excerpt, topics (Policy & regulation, Teaching & learning) and cover.
 */
function aiad_migrate_article_milburn_review(): void {
	if ( get_option( 'aiad_article_milburn_added' ) === '1' || ! post_type_exists( 'timeline' ) || ! taxonomy_exists( 'timeline_category' ) ) {
		return;
	}
	$slug = 'milburn-review-ai-work-readiness';
	if ( get_page_by_path( $slug, OBJECT, 'timeline' ) ) {
		update_option( 'aiad_article_milburn_added', '1' );
		return;
	}
	$file = AIAD_DIR . '/inc/articles/milburn-review-ai-work-readiness.html';
	if ( ! is_readable( $file ) ) {
		return;
	}
	$content = str_replace( array( 'THEME_URI', 'SITE_URI' ), array( untrailingslashit( AIAD_URI ), untrailingslashit( home_url() ) ), (string) file_get_contents( $file ) );
	$dir     = AIAD_DIR . '/assets/images/articles/milburn';
	$fields  = aiad_article_milburn_fields();

	kses_remove_filters();
	$id = wp_insert_post(
		array(
			'post_type'    => 'timeline',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $fields['title'],
			'post_excerpt' => $fields['excerpt'],
			'post_content' => '',
		)
	);
	kses_init_filters();
	if ( ! $id || is_wp_error( $id ) ) {
		return;
	}

	preg_match_all( '/IMG:([a-z0-9-]+)/', $content, $found );
	foreach ( array_unique( $found[1] ) as $name ) {
		$attachment = aiad_article_import_image( (int) $id, $dir . '/' . $name . '.png', 'milburn-' . $name . '.png', '' );
		$url        = $attachment ? (string) wp_get_attachment_url( $attachment ) : untrailingslashit( AIAD_URI ) . '/assets/images/articles/milburn/' . $name . '.png';
		$content    = str_replace( 'IMG:' . $name . '"', $url . '"', $content );
	}
	global $wpdb;
	$wpdb->update( $wpdb->posts, array( 'post_content' => $content ), array( 'ID' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	clean_post_cache( $id );

	update_post_meta( $id, '_aiad_timeline_source', 'manual' );
	update_post_meta( $id, '_aiad_timeline_icon', 'announcement' );

	$cover = aiad_article_import_image( (int) $id, AIAD_DIR . '/assets/images/articles/milburn-card.jpg', 'milburn-review-ai-work-readiness.jpg', 'Alan Milburn in a dark suit and glasses on a blue, purple and pink gradient, beside a ladder with rungs in the AI Awareness Day colours and a dashed outline where the bottom rung should be' );
	if ( $cover ) {
		set_post_thumbnail( $id, $cover );
	}

	$terms = array();
	foreach ( array( 'policy-regulation', 'teaching-learning' ) as $topic ) {
		$term = get_term_by( 'slug', $topic, 'timeline_category' );
		if ( $term ) {
			$terms[] = (int) $term->term_id;
		}
	}
	if ( $terms ) {
		wp_set_object_terms( $id, $terms, 'timeline_category', false );
	}

	update_option( 'aiad_article_milburn_added', '1' );
	update_option( 'aiad_article_milburn_v3', '1' ); // a fresh publish already has the current text, title and cover.
}
add_action( 'init', static function (): void { aiad_article_run_once( 'aiad_article_milburn_added', 'aiad_migrate_article_milburn_review' ); }, 57 );

/**
 * One-time (revision 3): the post went live with the ladder cover and its first text. Bring it up to date: Alan Milburn's portrait
 * as the cover (unless revision 2 already set it), and the text, title and excerpt after the 18 November 2026 fairness and fact
 * check (more balance on critics and on the report's AI caveats, its central argument, two new sources and the photo credit). Text, title and excerpt change only if nobody has edited the post in the editor (its modified time is still its
 * published time); an edited post keeps them but still gets the cover. The figure already on the post is reused.
 */
function aiad_sync_article_milburn_v3(): void {
	$post = get_page_by_path( 'milburn-review-ai-work-readiness', OBJECT, 'timeline' );
	if ( ! $post ) {
		return; // Not published yet: the publish above will use the current text, title and cover.
	}
	$file   = AIAD_DIR . '/inc/articles/milburn-review-ai-work-readiness.html';
	$edited = abs( strtotime( $post->post_modified_gmt . ' UTC' ) - strtotime( $post->post_date_gmt . ' UTC' ) ) > 120;
	if ( ! $edited && is_readable( $file ) ) {
		$content = str_replace( array( 'THEME_URI', 'SITE_URI' ), array( untrailingslashit( AIAD_URI ), untrailingslashit( home_url() ) ), (string) file_get_contents( $file ) );
		preg_match_all( '/<img[^>]+src="([^"]+)"/', (string) $post->post_content, $have );
		preg_match_all( '/IMG:([a-z0-9-]+)/', $content, $found );
		foreach ( array_unique( $found[1] ) as $name ) {
			$url = '';
			foreach ( $have[1] as $src ) {
				if ( false !== strpos( $src, 'milburn-' . $name ) ) {
					$url = $src;
					break;
				}
			}
			if ( '' === $url ) {
				$attachment = aiad_article_import_image( (int) $post->ID, AIAD_DIR . '/assets/images/articles/milburn/' . $name . '.png', 'milburn-' . $name . '.png', '' );
				$url        = $attachment ? (string) wp_get_attachment_url( $attachment ) : untrailingslashit( AIAD_URI ) . '/assets/images/articles/milburn/' . $name . '.png';
			}
			$content = str_replace( 'IMG:' . $name . '"', $url . '"', $content );
		}
		$fields = aiad_article_milburn_fields();
		global $wpdb;
		$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->posts,
			array(
				'post_content' => $content,
				'post_title'   => $fields['title'],
				'post_excerpt' => $fields['excerpt'],
			),
			array( 'ID' => $post->ID )
		);
		clean_post_cache( $post->ID );
	}

	$thumb = (int) get_post_thumbnail_id( $post->ID );
	if ( ! $thumb || false === strpos( (string) get_attached_file( $thumb ), 'milburn-review-ai-work-readiness-cover' ) ) {
		$cover = aiad_article_import_image( (int) $post->ID, AIAD_DIR . '/assets/images/articles/milburn-card.jpg', 'milburn-review-ai-work-readiness-cover.jpg', 'Alan Milburn in a dark suit and glasses on a blue, purple and pink gradient, beside a ladder with rungs in the AI Awareness Day colours and a dashed outline where the bottom rung should be' );
		if ( $cover ) {
			update_post_meta( $post->ID, '_thumbnail_id', $cover ); // set_post_thumbnail() would also work; this leaves the modified time alone.
		}
	}
	update_option( 'aiad_article_milburn_v3', '1' );
}
add_action( 'init', static function (): void { aiad_article_run_once( 'aiad_article_milburn_v3', 'aiad_sync_article_milburn_v3' ); }, 58 );
