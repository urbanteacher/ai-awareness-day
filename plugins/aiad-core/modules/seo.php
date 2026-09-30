<?php
/**
 * SEO module: its own settings (Settings → SEO & sharing), Open Graph and share messages, JSON-LD, breadcrumbs,
 * canonical URLs, search-engine verification, robots and sitemap tweaks, and the curated llms.txt.
 *
 * Moved from the theme's inc/seo.php, inc/sharing.php and llms.txt. The theme loads this file from its bundled copy
 * of the plugin when the plugin isn't active, so this is the only copy.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Marks this module as loaded. See aiad_core_load_modules().
define( 'AIAD_CORE_MODULE_SEO', __FILE__ );

require_once __DIR__ . '/seo/settings.php';
// Same order the theme loaded them in.
require_once __DIR__ . '/seo/sharing.php';
require_once __DIR__ . '/seo/seo.php';
