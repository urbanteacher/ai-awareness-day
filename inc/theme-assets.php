<?php
/**
 * Theme asset manifests and enqueue helpers.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Public URL for a file under assets/images/ (e.g. logos/cas.png).
 */
function aiad_theme_image_uri( string $relative_path ): string {
	$relative_path = ltrim( str_replace( '\\', '/', $relative_path ), '/' );
	return trailingslashit( AIAD_URI ) . 'assets/images/' . $relative_path;
}

/**
 * Filesystem path for a file under assets/images/.
 */
function aiad_theme_image_path( string $relative_path ): string {
	$relative_path = ltrim( str_replace( '\\', '/', $relative_path ), '/' );
	return AIAD_DIR . '/assets/images/' . $relative_path;
}

/**
 * URL for a principle strand mark (AiAd27 poster graphic).
 * Legacy name kept for callers; polygons were retired in favour of campaign posters.
 */
function aiad_principle_polygon_uri( string $slug ): string {
	$slug = sanitize_title( $slug );
	$allowed = array( 'safe', 'smart', 'creative', 'responsible', 'future' );
	if ( ! in_array( $slug, $allowed, true ) ) {
		$slug = 'safe';
	}
	return trailingslashit( AIAD_URI ) . 'assets/brand/aiad27/poster-' . $slug . '.svg';
}

/**
 * URL for an AiAd27 strand icon (24×24 mark).
 */
function aiad_strand_icon_uri( string $slug ): string {
	$slug = sanitize_title( $slug );
	$allowed = array( 'safe', 'smart', 'creative', 'responsible', 'future' );
	if ( ! in_array( $slug, $allowed, true ) ) {
		$slug = 'safe';
	}
	return trailingslashit( AIAD_URI ) . 'assets/brand/aiad27/icon-' . $slug . '.svg';
}

/**
 * The 24x24 path of each AiAd27 strand icon, as in assets/brand/aiad27/icon-<strand>.svg.
 *
 * @return array<string, string>
 */
function aiad_strand_icon_paths(): array {
	return array(
		'safe'        => 'M12 2 L21 6 V12 C21 16.8 17 20.6 12 22 C7 20.6 3 16.8 3 12 V6 Z',
		'smart'       => 'M12 2 A7 7 0 0 0 8 14.8 V17 h8 v-2.2 A7 7 0 0 0 12 2 Z M9 18.6 h6 v1.6 h-6 Z M10 21.2 h4 v1.4 h-4 Z',
		'creative'    => 'M12 1 L14.4 8.6 L22 11 L14.4 13.4 L12 21 L9.6 13.4 L2 11 L9.6 8.6 Z',
		'responsible' => 'M11 2 h2 v3.2 h6.4 v1.8 H13 V20 h5 v2 H6 v-2 h5 V7 H4.6 V5.2 H11 Z M4.6 8.4 L1.4 15 h6.4 Z M19.4 8.4 L16.2 15 h6.4 Z',
		'future'      => 'M3 21 C3 14 8 12 12 12 C16 12 18 10 18 6 h-3.4 L19.6 1 L24 6 h-3.4 c0 6-4 8-8.6 8 C8.4 14 5.6 15.6 5.4 21 Z',
	);
}

/**
 * Inline AiAd27 strand icon SVG (fill follows currentColor).
 *
 * @param string $slug Strand slug.
 * @param int    $size Pixel size.
 * @return string SVG markup.
 */
function aiad_strand_icon_svg( string $slug, int $size = 24 ): string {
	$slug  = sanitize_title( $slug );
	$paths = aiad_strand_icon_paths();
	if ( ! isset( $paths[ $slug ] ) ) {
		$slug = 'safe';
	}
	$size = max( 12, min( 96, $size ) );
	return sprintf(
		'<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="%2$s"/></svg>',
		$size,
		$paths[ $slug ]
	);
}

/**
 * The strand's poster graphic at 16:10: an ink panel with the strand icon in its
 * bright colour, after assets/brand/aiad27/poster-<strand>.svg. Used as a card
 * hero when a resource has no photo, so a card never falls back to a flat box.
 *
 * The icon is sized by the bounds of its own path rather than its 24-unit box,
 * so all five marks share one visual height, and it bleeds off the right edge.
 * The fill comes from --a27-<strand> so the palette has one source. It is set in
 * style, not the fill attribute, because var() in a presentation attribute is
 * not reliable across browsers.
 *
 * @param string $slug Strand slug; anything else draws the ink panel alone.
 * @return string SVG markup.
 */
function aiad_strand_poster_svg( string $slug ): string {
	$paths = aiad_strand_icon_paths();
	// x, y, width, height of each path inside its 24-unit box, measured with getBBox().
	$bbox = array(
		'safe'        => array( 3, 2, 18, 20 ),
		'smart'       => array( 4.918, 2, 14.163, 20.6 ),
		'creative'    => array( 2, 1, 20, 20 ),
		'responsible' => array( 1.4, 2, 21.2, 20 ),
		'future'      => array( 3, 1, 21, 20 ),
	);
	$mark = '';
	if ( isset( $paths[ $slug ], $bbox[ $slug ] ) ) {
		list( $x, $y, $w, $h ) = $bbox[ $slug ];
		$k    = 124 / $h;                   // 124 of the 100-unit height: it crops, on purpose.
		$mark = sprintf(
			'<path style="fill:var(--a27-%1$s)" transform="translate(%2$.2f %3$.2f) scale(%4$.4f)" d="%5$s"/>',
			esc_attr( $slug ),
			118 - ( $x + $w / 2 ) * $k,
			58 - ( $y + $h / 2 ) * $k,
			$k,
			esc_attr( $paths[ $slug ] )
		);
	}
	return '<svg class="resource-tile__poster" viewBox="0 0 160 100" preserveAspectRatio="xMidYMid slice" aria-hidden="true" focusable="false">'
		. '<rect width="160" height="100" style="fill:var(--a27-ink, #231F20)"/>' . $mark . '</svg>';
}

/**
 * Theme-link badge image: Customizer upload if set, otherwise AiAd27 strand icon.
 */
function aiad_theme_link_badge_src( string $slug ): string {
	$slug = strtolower( sanitize_title( $slug ) );
	$badge_id = absint( get_theme_mod( 'aiad_badge_' . $slug, 0 ) );
	if ( $badge_id ) {
		$src = wp_get_attachment_image_url( $badge_id, 'thumbnail' );
		if ( $src ) {
			return $src;
		}
	}
	$allowed = array( 'safe', 'smart', 'creative', 'responsible', 'future' );
	if ( in_array( $slug, $allowed, true ) ) {
		return aiad_strand_icon_uri( $slug );
	}
	return '';
}

/**
 * CSS bundle groups for optional concat build (scripts/build-css-bundles.php).
 *
 * @return array<string, array<int, string>>
 */
function aiad_css_bundle_manifest(): array {
	static $manifest = null;

	if ( null === $manifest ) {
		$path = AIAD_DIR . '/assets/css/bundle-manifest.php';
		$manifest = file_exists( $path ) ? require $path : array();
		if ( ! is_array( $manifest ) ) {
			$manifest = array();
		}
	}

	return $manifest;
}

/**
 * Flat ordered list of modular stylesheet paths under assets/css/.
 *
 * @return array<int, string>
 */
function aiad_modular_stylesheet_paths(): array {
	$paths = array();
	foreach ( aiad_css_bundle_manifest() as $files ) {
		if ( ! is_array( $files ) ) {
			continue;
		}
		foreach ( $files as $file ) {
			$paths[] = $file;
		}
	}
	return $paths;
}

/**
 * Enqueue modular theme CSS (source of truth — no generated bundles on the live site).
 */
function aiad_enqueue_modular_theme_styles(): void {
	foreach ( aiad_modular_stylesheet_paths() as $file ) {
		$handle    = 'aiad-' . str_replace( array( '/', '.css' ), array( '-', '' ), $file );
		$file_path = AIAD_DIR . '/assets/css/' . $file;
		$version   = file_exists( $file_path ) ? (string) filemtime( $file_path ) : AIAD_VERSION;

		wp_enqueue_style(
			$handle,
			AIAD_URI . '/assets/css/' . $file,
			array( 'aiad-style' ),
			$version,
			'all'
		);
	}
}
