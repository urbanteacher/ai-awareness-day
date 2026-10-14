<?php
/**
 * Write each age's "How to run a debate" guide as a standalone HTML page, for
 * scripts/export-debate-guides.mjs to print to PDF.
 *
 * It renders AIADN_Guide_Front::document(), the same markup /conversation/guide/
 * shows, with the portal stylesheet and the brand font linked from the theme, so
 * the PDF can never drift from the page.
 *
 * Usage, inside the WordPress container (the export script runs it for you):
 *   php wp-content/themes/ai-awareness-day/scripts/build-debate-guides.php <out-dir>
 */

if ( 'cli' !== PHP_SAPI ) {
	exit;
}
$out = rtrim( (string) ( $argv[1] ?? '' ), '/' );
if ( '' === $out || ! is_dir( $out ) ) {
	fwrite( STDERR, "Usage: php build-debate-guides.php <existing out-dir>\n" );
	exit( 1 );
}

$_SERVER['HTTP_HOST'] = 'localhost:8888';
require dirname( __DIR__, 4 ) . '/wp-load.php';

$theme  = dirname( __DIR__ );
$plugin = AIADN_PLUGIN_DIR;
$fonts  = '';
foreach ( array( 'Regular' => 400, 'Semibold' => 600, 'Bold' => 700 ) as $cut => $weight ) {
	$fonts .= "@font-face { font-family: 'AIAD Sans'; src: url('../../assets/fonts/aiad27/UncutSans-{$cut}.woff2') format('woff2'); font-weight: {$weight}; }\n";
}
$css  = file_get_contents( $theme . '/assets/css/base/tokens.css' ) . "\n" . file_get_contents( $plugin . 'public/aiadn.css' );
$logo = 'data:image/svg+xml;base64,' . base64_encode( (string) file_get_contents( $theme . '/assets/brand/aiad27/aiad27-lockup.svg' ) );

foreach ( array_keys( AIADN_Guide_Front::AGES ) as $age ) {
	$doc = AIADN_Guide_Front::document( $age );
	$doc = preg_replace( '#src="[^"]*aiad27-lockup\.svg"#', 'src="' . $logo . '"', $doc );
	$html = '<!doctype html><html lang="en-GB"><head><meta charset="utf-8"><title>How to run a debate: ' . esc_html( AIADN_Guide_Front::AGES[ $age ]['name'] ) . '</title>'
		. '<style>' . $fonts . $css . "\nbody { margin: 0; font-family: 'AIAD Sans', Arial, sans-serif; background: #F6F4ED; }\n.aiadn__body { padding: 0; }\n@page { size: A4; margin: 14mm 14mm 16mm; }</style></head>"
		. '<body><main class="aiadn aiadn--smart"><div class="aiadn__body"><div class="aiadn__wrap" style="max-width:none">' . $doc . '</div></div></main></body></html>';
	file_put_contents( $out . '/debate-guide-' . $age . '.html', $html );
	echo $age, "\n";
}
