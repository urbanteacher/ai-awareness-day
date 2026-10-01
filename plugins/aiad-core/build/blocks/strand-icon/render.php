<?php
/**
 * Strand icon: the strand's mark, as the National Conversation page's theme cards show it.
 *
 * @package AI_Awareness_Day
 *
 * @var array $attributes Block attributes.
 */

$aiad_strand = in_array( $attributes['strand'] ?? '', array( 'safe', 'smart', 'creative', 'responsible', 'future' ), true ) ? $attributes['strand'] : 'safe';
?>
<span class="ncp-theme__icon" aria-hidden="true"><?php echo aiad_strand_icon_svg( $aiad_strand, (int) ( $attributes['size'] ?? 32 ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- the theme's own SVG. ?></span>
