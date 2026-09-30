<?php
/**
 * Title: Homepage: Principles
 * Slug: aiad/homepage-principles
 * Categories: aiad-homepage
 * Keywords: homepage, principles, strands, section
 * Description: The Principles section in blocks, so its heading and cards are edited on the page. Replaces the Principles section block (template-parts/front-page/section-principles.php).
 *
 * The heading is core blocks; the cards are principle card blocks in a principles grid (blocks/), because a whole
 * card is a link, which core blocks cannot make. The cards start with the site's current wording (the Customizer's,
 * or the standard wording).
 *
 * @package AI_Awareness_Day
 */

$aiad_cards = array();
foreach ( array_keys( aiad_principle_cards() ) as $aiad_strand ) {
	$aiad_wording = aiad_principle_card_wording( $aiad_strand );
	$aiad_attrs   = array(
		'strand' => $aiad_strand,
		'title'  => esc_html( $aiad_wording[0] ),
		'text'   => esc_html( $aiad_wording[1] ),
	);
	if ( 'safe' === $aiad_strand ) {
		unset( $aiad_attrs['strand'] ); // The default; the editor leaves it out too.
	}
	$aiad_cards[] = '<!-- wp:aiad/principle-card ' . serialize_block_attributes( $aiad_attrs ) . ' /-->';
}
?>
<!-- wp:group {"tagName":"section","metadata":{"name":"Principles","patternName":"aiad/homepage-principles"},"className":"section","layout":{"type":"default"},"anchor":"principles"} -->
<section id="principles" class="wp-block-group section"><!-- wp:group {"className":"container","layout":{"type":"default"}} -->
<div class="wp-block-group container"><!-- wp:group {"className":"fade-up","layout":{"type":"default"}} -->
<div class="wp-block-group fade-up"><!-- wp:paragraph {"className":"section-label"} -->
<p class="section-label"><?php esc_html_e( 'Five strands', 'ai-awareness-day' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"section-title"} -->
<h2 class="wp-block-heading section-title"><?php esc_html_e( 'Safe. Smart. Creative. Responsible. Future.', 'ai-awareness-day' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"section-desc"} -->
<p class="section-desc"><?php esc_html_e( 'One campaign, five classroom starters. Each strand has a colour, a word and a mark — and a conversation worth having about the AI already in young people’s lives.', 'ai-awareness-day' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:aiad/principles-grid -->
<?php echo implode( "\n\n", $aiad_cards ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block comments; the values are escaped and serialised above. ?>
<!-- /wp:aiad/principles-grid --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
