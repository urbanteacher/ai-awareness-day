<?php
/**
 * Post types module: the resource, partner, featured resource and form submission post types, their taxonomies and
 * meta, the resource field registry, taxonomy fields shared by the meta boxes, save validation, and one-off seeds.
 *
 * Moved from the theme's inc/ folder. The theme loads this file from its bundled copy of the plugin when the plugin
 * isn't active, so this is the only copy.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Marks this module as loaded. See aiad_core_load_modules().
define( 'AIAD_CORE_MODULE_POST_TYPES', __FILE__ );

// Same order the theme loaded them in.
require_once __DIR__ . '/post-types/post-types.php';
require_once __DIR__ . '/post-types/resource-seeds.php';
require_once __DIR__ . '/post-types/field-registry.php';
require_once __DIR__ . '/post-types/admin-taxonomy-fields.php';
require_once __DIR__ . '/post-types/validation.php';
require_once __DIR__ . '/post-types/resource-editor.php';
