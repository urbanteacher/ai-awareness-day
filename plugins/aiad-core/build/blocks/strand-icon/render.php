<?php
/**
 * Strand icon: the strand's mark, as the National Conversation page's theme cards show it.
 *
 * @package AI_Awareness_Day
 *
 * @var array $attributes Block attributes.
 */

// This block prints the AI Awareness Day theme's markup with the theme's own functions; under another theme it
// prints nothing rather than stopping the page with a fatal error.
foreach ( array( 'aiad_strand_icon_svg' ) as $aiad_theme_fn ) {
	if ( ! function_exists( $aiad_theme_fn ) ) {
		return;
	}
}

$aiad_strand = in_array( $attributes['strand'] ?? '', array( 'safe', 'smart', 'creative', 'responsible', 'future' ), true ) ? $attributes['strand'] : 'safe';
?>
<span class="ncp-theme__icon" aria-hidden="true"><?php echo aiad_strand_icon_svg( $aiad_strand, (int) ( $attributes['size'] ?? 32 ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- the theme's own SVG. ?></span>
