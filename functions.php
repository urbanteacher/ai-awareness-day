<?php
/**
 * AI Awareness Day Theme Functions
 *
 * @package AI_Awareness_Day
 * @version 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'AIAD_VERSION', '1.3.2' );

// TEMPORARY: Force flush rewrite rules — DELETE AFTER ONE PAGE LOAD
flush_rewrite_rules();

// TEMPORARY DEBUG — remove after checking
add_action( 'admin_notices', function() {
    if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'debug-timeline' ) return;
    
    $cpt = get_post_type_object( 'aiad_timeline' );
    echo '<div class="notice" style="white-space:pre;font-family:monospace;padding:20px;">';
    echo 'CPT exists: ' . ( $cpt ? 'YES' : 'NO' ) . "\n";
    if ( $cpt ) {
        echo 'Public: ' . ( $cpt->public ? 'YES' : 'NO' ) . "\n";
        echo 'Publicly queryable: ' . ( $cpt->publicly_queryable ? 'YES' : 'NO' ) . "\n";
        echo 'Rewrite: ' . print_r( $cpt->rewrite, true ) . "\n";
    }
    
    global $wp_rewrite;
    $rules = $wp_rewrite->wp_rewrite_rules();
    echo "\n--- Rewrite rules with 'timeline' ---\n";
    $found = false;
    foreach ( $rules as $pattern => $query ) {
        if ( strpos( $pattern, 'timeline' ) !== false ) {
            echo "$pattern => $query\n";
            $found = true;
        }
    }
    if ( ! $found ) echo "NONE FOUND\n";
    echo '</div>';
});

// Define theme paths after theme directory is registered (avoids wp_is_block_theme() notice in WP 6.8+).
if ( ! defined( 'AIAD_DIR' ) ) {
    define( 'AIAD_DIR', __DIR__ );
}
add_action( 'after_setup_theme', function () {
    if ( ! defined( 'AIAD_URI' ) ) {
        define( 'AIAD_URI', get_template_directory_uri() );
    }
}, 0 );

$aiad_dir = AIAD_DIR;
if ( is_admin() ) {
    require_once $aiad_dir . '/admin/class-aiad-homepage-editor.php';
    require_once $aiad_dir . '/inc/meta-boxes.php';
    require_once $aiad_dir . '/inc/admin-columns.php';
    require_once $aiad_dir . '/inc/import-export.php';
}

require_once $aiad_dir . '/inc/setup.php';
require_once $aiad_dir . '/inc/helpers.php';
require_once $aiad_dir . '/inc/post-types.php';
require_once $aiad_dir . '/inc/field-registry.php';
require_once $aiad_dir . '/inc/customizer.php';
require_once $aiad_dir . '/inc/front-page-layout.php';
require_once $aiad_dir . '/inc/validation.php';
require_once $aiad_dir . '/inc/ajax-handlers.php';
require_once $aiad_dir . '/inc/timeline.php';
require_once $aiad_dir . '/inc/sharing.php';
require_once $aiad_dir . '/inc/seo.php';

/**
 * Timeline Event Date Configuration
 * 
 * Change the event date for the timeline countdown by modifying the date below.
 * Format: Y-m-d (e.g., '2026-06-04')
 */
add_filter( 'aiad_timeline_event_date', function() {
    return '2026-06-04'; // Change this date to update the countdown
} );
