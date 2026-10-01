<?php
/**
 * Title: Assets Pack page
 * Slug: aiad/assets-pack
 * Categories: aiad-pages
 * Post Types: page
 * Block Types: core/post-content
 * Keywords: assets, downloads, logos, banners, brand
 * Description: The Assets Pack in blocks, so its text and downloads are edited on the page. Starts with the downloads of template-assets-pack.php.
 *
 * The downloads come from aiad_assets_pack_review_pages() and aiad_assets_pack_downloads(), the lists
 * template-assets-pack.php prints, as Download card blocks (blocks/download-card), whose file is chosen from the media
 * library. The logo and email banners chosen in the Customizer are included if they are set; after that, a card's
 * file is changed on the page. Everything sits in one main group (main#main.assets-pack-page, as on the PHP page);
 * the page then renders from templates/theme-page.html (inc/editable-pages.php). The page's own text, if it had
 * any, stays as the introduction. The Demo link's address is the {demo_start_url} placeholder, left out away from the
 * presenter's laptop.
 *
 * @package AI_Awareness_Day
 *
 * @var WP_Post|null $aiad_page The page, when the blocks are made for it (aiad_editable_page_content()).
 */

$aiad_page  = isset( $aiad_page ) && $aiad_page instanceof WP_Post ? $aiad_page : null;
$aiad_intro = $aiad_page ? trim( $aiad_page->post_content ) : '';
$aiad_title = $aiad_page && '' !== $aiad_page->post_title ? $aiad_page->post_title : __( 'Assets Pack', 'ai-awareness-day' );

$aiad_cards = array();
foreach ( aiad_assets_pack_review_pages() as $aiad_review ) {
	$aiad_cards[] = aiad_block_download_card(
		array(
			'kind'        => 'page',
			'href'        => $aiad_review['url'],
			'preview'     => $aiad_review['preview'],
			'badge'       => $aiad_review['badge'],
			'button'      => $aiad_review['btn_label'],
			'title'       => $aiad_review['label'],
			'description' => $aiad_review['description'],
		)
	);
}
foreach ( aiad_assets_pack_downloads() as $aiad_asset ) {
	$aiad_id  = absint( $aiad_asset['id'] ?? 0 );
	$aiad_url = $aiad_id ? (string) wp_get_attachment_url( $aiad_id ) : ( $aiad_asset['url'] ?? '' );
	if ( '' === $aiad_url ) {
		continue; // A Customizer file that has not been chosen.
	}
	$aiad_full    = $aiad_id ? (string) wp_get_attachment_image_url( $aiad_id, 'large' ) : $aiad_url;
	$aiad_cards[] = aiad_block_download_card(
		array(
			'href'        => $aiad_url,
			'filename'    => basename( $aiad_id ? ( get_attached_file( $aiad_id ) ?: $aiad_url ) : $aiad_url ),
			'preview'     => $aiad_full ?: $aiad_url,
			'alt'         => $aiad_asset['label'],
			'button'      => $aiad_asset['btn_label'],
			'title'       => $aiad_asset['label'],
			'description' => $aiad_asset['description'],
		)
	);
}

if ( '' === $aiad_intro ) {
	$aiad_intro_block = aiad_block_paragraph( aiad_block_text( __( 'Download AiAd27 lockups, strand icons, posters and chamfer shapes. Deep marks on cream; bright marks on ink. Never put bright type on cream.', 'ai-awareness-day' ) ), 'section-desc' );
} else {
	// The page's own text, as the PHP template showed it: its blocks, or its classic content with paragraphs added.
	$aiad_intro_block = aiad_block_group( array( has_blocks( $aiad_intro ) ? $aiad_intro : wpautop( $aiad_intro ) ), 'assets-pack__intro section-desc' );
}

$aiad_out = array(
	aiad_block_group(
		array(
			aiad_block_group(
				array(
					aiad_block_styled_text( aiad_block_text( __( 'Free Downloads', 'ai-awareness-day' ) ), 'span', 'section-label' ),
					aiad_block_heading( 1, aiad_block_text( $aiad_title ), 'section-title' ),
					$aiad_intro_block,
				),
				'assets-pack__header fade-up'
			),
			aiad_block_group( $aiad_cards, 'assets-pack__grid' ),
		),
		'container'
	),
	aiad_block_paragraph( '<a href="{demo_start_url}" rel="nofollow">Demo</a>', 'assets-pack__demo' ),
);

echo aiad_block_group( $aiad_out, 'assets-pack-page', 'main', 'main', $aiad_title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block markup; every value is escaped above.
