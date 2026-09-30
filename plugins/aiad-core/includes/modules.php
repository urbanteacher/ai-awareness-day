<?php
/**
 * Modules moved out of the theme.
 *
 * Code moves from the theme's inc/ folder into modules/ one module at a time. While a module is listed here, the
 * plugin loads it and the theme skips its own copy, so a function is never declared twice. See README.md for the
 * module map and the order to move them in.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modules this plugin now provides.
 *
 * @return array<string, string> module slug => file in modules/
 */
function aiad_core_modules(): array {
	return array(
		'helpers-data'    => 'helpers-data.php',
		'post-types'      => 'post-types.php',
		'contact'         => 'contact.php',
		'resource-filter' => 'resource-filter.php',
		'tracking'        => 'tracking.php',
		'admin'           => 'admin.php',
	);
}

/**
 * Whether the plugin provides a module, so the theme should not load its own copy.
 *
 * The theme's aiad_require_core_module() checks this before loading its bundled copy of a module.
 */
function aiad_core_provides( string $module ): bool {
	return array_key_exists( $module, aiad_core_modules() );
}

/**
 * Constant a module defines once it has loaded, e.g. AIAD_CORE_MODULE_HELPERS_DATA.
 */
function aiad_core_module_constant( string $module ): string {
	return 'AIAD_CORE_MODULE_' . strtoupper( str_replace( '-', '_', $module ) );
}

/**
 * Load every module listed in aiad_core_modules().
 *
 * A module is skipped if it has already loaded. That happens when the plugin is activated: the theme has already
 * loaded its bundled copy of the module in the same request, and loading it again would declare its functions twice.
 */
function aiad_core_load_modules(): void {
	foreach ( aiad_core_modules() as $module => $file ) {
		if ( defined( aiad_core_module_constant( $module ) ) ) {
			continue;
		}
		require_once AIAD_CORE_DIR . 'modules/' . $file;
	}
}
