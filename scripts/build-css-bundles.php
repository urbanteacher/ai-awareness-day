<?php
/**
 * Build CSS bundles for production. Run from theme root: php scripts/build-css-bundles.php
 * Creates assets/css/bundles/base.css, layout.css, pages.css to reduce HTTP requests.
 *
 * @package AI_Awareness_Day
 */

$theme_dir = dirname( __DIR__ );
$css_dir   = $theme_dir . '/assets/css';
$out_dir   = $css_dir . '/bundles';

$bundles = array(
    'base'   => array(
        'base/reset.css',
        'base/shared.css',
        'base/animations.css',
        'base/wp-core.css',
    ),
    'layout' => array(
        'layout/navigation.css',
        'layout/hero.css',
        'layout/footer.css',
        'components/principles.css',
    ),
    'pages'  => array(
        'pages/campaign.css',
        'pages/momentum.css',
        'pages/themes.css',
        'pages/aim.css',
        'pages/toolkit.css',
        'pages/get-involved.css',
        'pages/resources-archive.css',
        'pages/partners-archive.css',
        'responsive/responsive.css',
        'responsive/mobile.css',
    ),
);

if ( ! is_dir( $out_dir ) ) {
    mkdir( $out_dir, 0755, true );
}

foreach ( $bundles as $name => $files ) {
    $out = '';
    foreach ( $files as $file ) {
        $path = $css_dir . '/' . $file;
        if ( file_exists( $path ) ) {
            $out .= "/* === " . $file . " === */\n" . file_get_contents( $path ) . "\n";
        }
    }
    file_put_contents( $out_dir . '/' . $name . '.css', $out );
    echo "Wrote " . $out_dir . '/' . $name . ".css\n";
}

echo "Done. Enqueue will use bundles when assets/css/bundles/base.css exists.\n";
