<?php
// debug-cpt.php — put in WordPress root, delete after use
require_once __DIR__ . '/wp-load.php';

if ( ! current_user_can( 'manage_options' ) ) {
    die( 'Not authorized' );
}

$cpt = get_post_type_object( 'aiad_timeline' );
echo '<pre>';
echo 'CPT exists: ' . ( $cpt ? 'YES' : 'NO' ) . "\n";
if ( $cpt ) {
    echo 'Public: ' . ( $cpt->public ? 'YES' : 'NO' ) . "\n";
    echo 'Publicly queryable: ' . ( $cpt->publicly_queryable ? 'YES' : 'NO' ) . "\n";
    echo 'Rewrite: ';
    print_r( $cpt->rewrite );
}

echo "\n\n--- Rewrite rules containing 'timeline' ---\n";
global $wp_rewrite;
$rules = $wp_rewrite->wp_rewrite_rules();
$found = false;
foreach ( $rules as $pattern => $query ) {
    if ( strpos( $pattern, 'timeline' ) !== false ) {
        echo "$pattern => $query\n";
        $found = true;
    }
}
if ( ! $found ) {
    echo "NONE FOUND — this is the problem\n";
}
echo '</pre>';
