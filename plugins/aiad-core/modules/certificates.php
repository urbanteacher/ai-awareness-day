<?php
/**
 * Certificates module: certificate and thank-you letter wording, the certificate REST API, shared config for the
 * certificate / letter generator tools, and their admin pages and form submission meta boxes.
 *
 * Moved from the theme's inc/ folder. The theme loads this file from its bundled copy of the plugin when the plugin
 * isn't active, so this is the only copy. The generator tools themselves are HTML files in the theme
 * (archive/theme/generators/), and the REST bootstrap uses the theme's logo helpers.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Marks this module as loaded. See aiad_core_load_modules().
define( 'AIAD_CORE_MODULE_CERTIFICATES', __FILE__ );

// Same order and conditions the theme loaded them with.
require_once __DIR__ . '/certificates/certificate-copy.php';
require_once __DIR__ . '/certificates/letter-copy.php';
require_once __DIR__ . '/certificates/certificate-api.php';
require_once __DIR__ . '/certificates/generator-embed.php';
if ( is_admin() ) {
	require_once __DIR__ . '/certificates/certificate-admin.php';
	require_once __DIR__ . '/certificates/letter-admin.php';
}
