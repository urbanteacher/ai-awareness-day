<?php
/**
 * One-time: publish "The human bottleneck in the AI workplace" as a campaign update (the timeline post type, shown at
 * /timeline/). The text is inc/articles/the-human-bottleneck.html, block markup made from the fact-checked Word document
 * (the numbered sources are paragraphs with ids, which the [1]-style notes in the text link to). Runs once on the first
 * request after a deploy; the article can then be edited, retitled or trashed in the editor without this putting it back.
 *
 * Saved with the filters that strip markup from users who cannot post unfiltered HTML switched off, so the blocks and
 * the source ids are stored as written. The text-free cover (assets/images/articles/human-bottleneck-card.jpg, from
 * archive/design-kit/ig-article-bottleneck-card.html) becomes the featured image.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import an image from the theme into the media library, attached to a post.
 *
 * @param int    $post_id Post to attach it to.
 * @param string $file    Path of the image in the theme.
 * @param string $name    File name to give it in the media library.
 * @param string $alt     Its alternative text.
 * @return int The attachment ID, or 0 if it could not be imported.
 */
function aiad_article_import_image( int $post_id, string $file, string $name, string $alt ): int {
	if ( ! is_readable( $file ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = wp_tempnam( basename( $file ) );
	if ( ! $tmp || ! copy( $file, $tmp ) ) {
		return 0;
	}
	$attachment = media_handle_sideload( array( 'name' => $name, 'tmp_name' => $tmp ), $post_id, $alt );
	if ( is_wp_error( $attachment ) ) {
		@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		return 0;
	}
	update_post_meta( (int) $attachment, '_wp_attachment_image_alt', $alt );
	return (int) $attachment;
}

/**
 * Publish the article once, with its excerpt, topics (Research & insight first, then Skills & careers) and cover.
 */
function aiad_migrate_article_human_bottleneck(): void {
	if ( get_option( 'aiad_article_human_bottleneck_added' ) === '1' || ! post_type_exists( 'timeline' ) || ! taxonomy_exists( 'timeline_category' ) ) {
		return;
	}
	$slug = 'human-bottleneck-ai-workplace';
	if ( get_page_by_path( $slug, OBJECT, 'timeline' ) ) {
		update_option( 'aiad_article_human_bottleneck_added', '1' );
		return;
	}
	$content = is_readable( AIAD_DIR . '/inc/articles/the-human-bottleneck.html' ) ? (string) file_get_contents( AIAD_DIR . '/inc/articles/the-human-bottleneck.html' ) : '';
	if ( '' === $content ) {
		return;
	}

	kses_remove_filters();
	$id = wp_insert_post(
		array(
			'post_type'    => 'timeline',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => 'The human bottleneck in the AI workplace',
			'post_excerpt' => 'Anthropic offers a glimpse of work organised around tireless agents. The pressure to remove human delays could reshape careers and determine who shares in the gains.',
			'post_content' => $content,
		)
	);
	kses_init_filters();
	if ( ! $id || is_wp_error( $id ) ) {
		return;
	}

	update_post_meta( $id, '_aiad_timeline_source', 'manual' );
	update_post_meta( $id, '_aiad_timeline_icon', 'announcement' );

	$cover = aiad_article_import_image( (int) $id, AIAD_DIR . '/assets/images/articles/human-bottleneck-card.jpg', 'human-bottleneck-ai-workplace.jpg', 'A stream of coloured tiles thinning to a few as they squeeze past a gate towards one person' );
	if ( $cover ) {
		set_post_thumbnail( $id, $cover );
	}

	$terms = array();
	foreach ( array( 'research-insight', 'skills-careers' ) as $topic ) {
		$term = get_term_by( 'slug', $topic, 'timeline_category' );
		if ( $term ) {
			$terms[] = (int) $term->term_id;
		}
	}
	if ( $terms ) {
		wp_set_object_terms( $id, $terms, 'timeline_category', false );
	}

	update_option( 'aiad_article_human_bottleneck_added', '1' );
}
add_action( 'init', 'aiad_migrate_article_human_bottleneck', 50 );
