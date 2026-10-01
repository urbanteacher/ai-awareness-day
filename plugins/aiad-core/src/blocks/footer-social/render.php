<?php
/**
 * Social icons block: prints template-parts/components/footer-social.php.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

ob_start();
get_template_part( 'template-parts/components/footer-social' );
$aiad_html = (string) ob_get_clean();
if ( '' !== trim( $aiad_html ) ) { // Nothing to show prints nothing, so the editor can say why.
	echo $aiad_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the component escapes its output.
}
