<?php
/**
 * Title: Press Release page
 * Slug: aiad/press-release
 * Categories: aiad-pages
 * Post Types: page
 * Block Types: core/post-content
 * Keywords: press release, media, download, pdf
 * Description: The Press Release page in blocks, so its text and file are edited on the page. Starts with the file chosen in the Customizer, as template-press-release.php shows it.
 *
 * The file comes from aiad_press_release_download(), as template-press-release.php shows it: a Download card block
 * (blocks/download-card), whose file is then chosen from the media library; a document shows its type in place of a
 * preview. With no file chosen yet, the page says the file is being prepared, as the PHP page does. The page's own
 * text, if it had any, stays as the introduction. The page renders from templates/theme-page.html
 * (inc/editable-pages.php).
 *
 * @package AI_Awareness_Day
 *
 * @var WP_Post|null $aiad_page The page, when the blocks are made for it (aiad_editable_page_content()).
 */

$aiad_page  = isset( $aiad_page ) && $aiad_page instanceof WP_Post ? $aiad_page : null;
$aiad_intro = $aiad_page ? trim( $aiad_page->post_content ) : '';
$aiad_title = $aiad_page && '' !== $aiad_page->post_title ? $aiad_page->post_title : __( 'Press Release', 'ai-awareness-day' );
$aiad_pr    = aiad_press_release_download();

if ( $aiad_pr['id'] && $aiad_pr['url'] ) {
	$aiad_file = aiad_block_download_card(
		array(
			'href'        => $aiad_pr['url'],
			'filename'    => $aiad_pr['filename'],
			'preview'     => $aiad_pr['is_image'] ? $aiad_pr['preview'] : '',
			'alt'         => $aiad_pr['label'],
			'badge'       => strtoupper( pathinfo( $aiad_pr['filename'], PATHINFO_EXTENSION ) ?: 'PDF' ),
			'docLabel'    => __( 'Press release file', 'ai-awareness-day' ),
			'button'      => $aiad_pr['btn_label'],
			'title'       => $aiad_pr['label'],
			'description' => $aiad_pr['description'],
		)
	);
} else {
	$aiad_file = aiad_block_paragraph( aiad_block_text( __( 'The press release file is being prepared — check back soon.', 'ai-awareness-day' ) ), 'assets-pack__empty section-desc' );
}

if ( '' === $aiad_intro ) {
	$aiad_intro_block = aiad_block_paragraph( aiad_block_text( $aiad_pr['description'] ), 'section-desc' );
} else {
	// The page's own text, as the PHP template showed it: its blocks, or its classic content with paragraphs added.
	$aiad_intro_block = aiad_block_group( array( has_blocks( $aiad_intro ) ? $aiad_intro : wpautop( $aiad_intro ) ), 'assets-pack__intro section-desc' );
}

echo aiad_block_group( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block markup; every value is escaped above.
	array(
		aiad_block_group(
			array(
				aiad_block_group(
					array(
						aiad_block_styled_text( aiad_block_text( __( 'Media', 'ai-awareness-day' ) ), 'span', 'section-label' ),
						aiad_block_heading( 1, aiad_block_text( $aiad_title ), 'section-title' ),
						$aiad_intro_block,
					),
					'assets-pack__header fade-up'
				),
				aiad_block_group( array( $aiad_file ), 'assets-pack__grid' ),
			),
			'container'
		),
	),
	'assets-pack-page press-release-page',
	'main',
	'main',
	$aiad_title
);
