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

define( 'AIAD_VERSION', '1.1.3' );
define( 'AIAD_DIR', get_template_directory() );
define( 'AIAD_URI', get_template_directory_uri() );

if ( is_admin() ) {
    require_once AIAD_DIR . '/admin/class-aiad-homepage-editor.php';
    require_once AIAD_DIR . '/inc/meta-boxes.php';
    require_once AIAD_DIR . '/inc/admin-columns.php';
    require_once AIAD_DIR . '/inc/import-export.php';
}

require_once AIAD_DIR . '/inc/setup.php';
require_once AIAD_DIR . '/inc/helpers.php';
// Post types, taxonomies, and register_post_meta for resource (_aiad_key_stage, _aiad_subtitle, etc.)
require_once AIAD_DIR . '/inc/post-types.php';
require_once AIAD_DIR . '/inc/customizer.php';
require_once AIAD_DIR . '/inc/front-page-layout.php';
require_once AIAD_DIR . '/inc/validation.php';
require_once AIAD_DIR . '/inc/ajax-handlers.php';
