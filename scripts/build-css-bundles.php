<?php
/**
 * Optional: concatenate modular CSS into assets/css/bundles/ for local perf testing.
 *
 * WordPress does NOT load these bundles — production always enqueues source files
 * from assets/css/bundle-manifest.php via aiad_enqueue_modular_theme_styles().
 *
 * Run from theme root: php scripts/build-css-bundles.php
 *
 * @package AI_Awareness_Day
 */

$theme_dir = dirname( __DIR__ );
$css_dir   = $theme_dir . '/assets/css';
$out_dir   = $css_dir . '/bundles';
$manifest  = $css_dir . '/bundle-manifest.php';

if ( ! file_exists( $manifest ) ) {
	fwrite( STDERR, "Missing manifest: {$manifest}\n" );
	exit( 1 );
}

$bundles = require $manifest;
if ( ! is_array( $bundles ) ) {
	fwrite( STDERR, "Invalid bundle manifest.\n" );
	exit( 1 );
}

if ( ! is_dir( $out_dir ) ) {
	mkdir( $out_dir, 0755, true );
}

foreach ( $bundles as $name => $files ) {
	$out = '';
	foreach ( $files as $file ) {
		$path = $css_dir . '/' . $file;
		if ( file_exists( $path ) ) {
			$out .= "/* === {$file} === */\n" . file_get_contents( $path ) . "\n";
		} else {
			echo "WARNING: Missing source file: {$file} (skipped)\n";
		}
	}
	file_put_contents( $out_dir . '/' . $name . '.css', $out );
	echo "Wrote {$out_dir}/{$name}.css\n";
}

echo "Done. Bundles are gitignored; live site uses modular CSS only.\n";
