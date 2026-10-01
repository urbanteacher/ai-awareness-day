<?php
/**
 * Title: Platform walkthrough page
 * Slug: aiad/walkthrough
 * Categories: aiad-pages
 * Post Types: page
 * Block Types: core/post-content
 * Keywords: walkthrough, tour, screenshots, national conversation, platform
 * Description: The platform walkthrough in blocks, so its text and screenshots are edited on the page. Starts with the screens of page-walkthrough.php.
 *
 * The parts and screens come from aiad_walkthrough_parts(), the same list page-walkthrough.php prints. Everything sits
 * in one main group (main#main.wt, as on the PHP page; templates/page-walkthrough.html has only the header, the post
 * content and the footer). Text is core blocks; the screens are Styled list items (blocks/item) holding an Image that
 * opens full size (blocks/zoom-image) and their words. The demo link's address is the {demo_url} placeholder, filled
 * in when the page renders and left out away from the presenter's laptop (inc/editable-pages.php).
 *
 * @package AI_Awareness_Day
 */

$aiad_nc_url = aiad_national_conversation_page_url();
$aiad_parts  = aiad_walkthrough_parts( $aiad_nc_url );

// One screenshot that opens full size: blocks/zoom-image, as the editor saves it.
$aiad_zoom = static function ( string $file, int $width, int $height, string $title ): string {
	$src = esc_url( AIAD_URI . '/assets/images/walkthrough/' . $file . '.jpg' );
	/* translators: %s: screen name */
	$alt = sprintf( __( 'Screenshot: %s', 'ai-awareness-day' ), $title );
	return '<!-- wp:aiad/zoom-image ' . serialize_block_attributes( array( 'width' => $width, 'height' => $height, 'className' => 'wt-shot__frame' ) ) . " -->\n"
		. '<a href="' . $src . '" target="_blank" rel="noopener" class="wt-shot__frame"><img src="' . $src . '" width="' . $width . '" height="' . $height . '" alt="' . str_replace( '&#039;', "'", esc_attr( $alt ) ) . '" loading="lazy" decoding="async"/><span class="wt-shot__zoom">' . aiad_block_text( __( 'View full screen', 'ai-awareness-day' ) ) . "</span></a>\n<!-- /wp:aiad/zoom-image -->";
};

$aiad_button = static fn( string $url, string $label, string $kind ): string => '<a class="wt-btn wt-btn--' . $kind . '" href="' . $url . '">' . aiad_block_text( $label ) . '</a>';

$aiad_out = array();

// Hero.
$aiad_out[] = aiad_block_group(
	array(
		aiad_block_group(
			array(
				aiad_block_paragraph( aiad_block_text( __( 'National AI Conversation 2027', 'ai-awareness-day' ) ), 'wt-eyebrow' ),
				aiad_block_heading( 1, aiad_block_text( __( 'Platform walkthrough', 'ai-awareness-day' ) ), 'wt-title', 'wt-title' ),
				aiad_block_paragraph( aiad_block_text( __( 'A tour of the platform schools will use from 1 January 2027, from signing up to the programme team\'s dashboard.', 'ai-awareness-day' ) ), 'wt-lead' ),
				aiad_block_paragraph( aiad_block_text( __( 'Every screen shows made-up demo data: Willowbrook Primary School and the schools it debates.', 'ai-awareness-day' ) ), 'wt-demo-note' ),
				aiad_block_paragraph( $aiad_button( esc_url( $aiad_nc_url ), __( 'What is the National AI Conversation?', 'ai-awareness-day' ), 'primary' ) . $aiad_button( '{demo_url}', __( 'Open the interactive demo', 'ai-awareness-day' ), 'ghost' ), 'wt-actions' ),
				aiad_block_styled_list(
					array(
						aiad_block_list(
							array_map(
								static fn( int $i, array $part ): string => '<a href="#wt-' . esc_attr( $part['id'] ) . '">' . aiad_block_text( ( $i + 1 ) . '. ' . $part['title'] ) . '</a>',
								array_keys( $aiad_parts ),
								$aiad_parts
							),
							true
						),
					),
					'wt-parts',
					'nav',
					array( 'aria-label' => __( 'Parts of the walkthrough', 'ai-awareness-day' ) )
				),
			),
			'container'
		),
	),
	'wt-hero',
	'section',
	'',
	__( 'Hero', 'ai-awareness-day' )
);

// The parts, each a list of screens.
$aiad_n = 0;
foreach ( $aiad_parts as $aiad_i => $aiad_part ) {
	$aiad_shots = array();
	foreach ( $aiad_part['shots'] as $aiad_shot ) {
		++$aiad_n;
		list( $aiad_file, $aiad_w, $aiad_h, $aiad_title, $aiad_desc, $aiad_live ) = $aiad_shot;
		$aiad_shots[] = aiad_block_styled_item(
			array(
				$aiad_zoom( $aiad_file, $aiad_w, $aiad_h, $aiad_title ),
				aiad_block_group(
					array(
						/* translators: %d: screen number */
						aiad_block_paragraph( aiad_block_text( sprintf( __( 'Screen %d', 'ai-awareness-day' ), $aiad_n ) ), 'wt-shot__step' ),
						aiad_block_heading( 3, aiad_block_text( $aiad_title ) ),
						aiad_block_paragraph( aiad_block_text( $aiad_desc ) ),
						$aiad_live ? aiad_block_paragraph( '<a href="' . esc_url( $aiad_live ) . '">' . aiad_block_text( __( 'Open the live page', 'ai-awareness-day' ) ) . '</a>', 'wt-shot__live' ) : '',
					),
					'wt-shot__body'
				),
			),
			'wt-shot' . ( $aiad_w < $aiad_h * 0.5 ? ' wt-shot--phone' : '' )
		);
	}
	$aiad_out[] = aiad_block_group(
		array(
			aiad_block_group(
				array(
					/* translators: %d: part number */
					aiad_block_paragraph( aiad_block_text( sprintf( __( 'Part %d', 'ai-awareness-day' ), $aiad_i + 1 ) ), 'wt-part__number' ),
					aiad_block_heading( 2, aiad_block_text( $aiad_part['title'] ), '', 'wt-' . $aiad_part['id'] . '-title' ),
					aiad_block_paragraph( aiad_block_text( $aiad_part['intro'] ), 'wt-part__intro' ),
					aiad_block_styled_list( $aiad_shots, 'wt-shots', 'ol' ),
				),
				'container'
			),
		),
		'wt-part' . ( 1 === $aiad_i % 2 ? ' wt-part--card' : '' ),
		'section',
		'wt-' . $aiad_part['id'],
		$aiad_part['title']
	);
}

// Closing call to action.
$aiad_out[] = aiad_block_group(
	array(
		aiad_block_group(
			array(
				aiad_block_heading( 2, aiad_block_text( __( 'Schools can register from 1 January 2027', 'ai-awareness-day' ) ), '', 'wt-cta' ),
				aiad_block_paragraph( aiad_block_text( __( 'Find out more about the National AI Conversation, or get in touch.', 'ai-awareness-day' ) ) ),
				aiad_block_paragraph( $aiad_button( esc_url( $aiad_nc_url ), __( 'The National AI Conversation', 'ai-awareness-day' ), 'primary' ) . $aiad_button( esc_url( home_url( '/#contact' ) ), __( 'Get in touch', 'ai-awareness-day' ), 'ghost' ), 'wt-actions' ),
			),
			'container'
		),
	),
	'wt-cta',
	'section',
	'',
	__( 'Find out more', 'ai-awareness-day' )
);

// The page's main element is its outermost block, so the editor shows the content inside .wt as the site does.
echo aiad_block_group( $aiad_out, 'wt', 'main', 'main', __( 'Platform walkthrough', 'ai-awareness-day' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block markup; every value is escaped above.
