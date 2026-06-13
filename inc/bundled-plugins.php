<?php
/**
 * Sync theme-bundled plugins into wp-content/plugins on deploy.
 *
 * Git deploy updates the theme only; WordPress does not load plugins from
 * wp-content/themes/.../plugins/. This copies and activates bundled plugins
 * so production matches local Docker behaviour.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bundled plugins shipped inside the theme.
 *
 * @return array<string, string> slug => main file relative to plugin root
 */
function aiad_bundled_plugins(): array {
	return array(
		'ai-risk-readiness-benchmark' => 'ai-risk-readiness-benchmark.php',
	);
}

/**
 * Source directory for a bundled plugin inside the theme.
 */
function aiad_bundled_plugin_source_dir( string $slug ): string {
	return trailingslashit( AIAD_DIR ) . 'plugins/' . $slug;
}

/**
 * Destination directory under wp-content/plugins.
 */
function aiad_bundled_plugin_dest_dir( string $slug ): string {
	return trailingslashit( WP_PLUGIN_DIR ) . $slug;
}

/**
 * Read the Version header from a plugin main file.
 */
function aiad_bundled_plugin_version( string $main_file ): string {
	if ( ! is_readable( $main_file ) ) {
		return '';
	}
	$data = get_file_data(
		$main_file,
		array( 'version' => 'Version' ),
		'plugin'
	);
	return (string) ( $data['version'] ?? '' );
}

/**
 * Recursively copy a directory.
 */
function aiad_copy_dir( string $src, string $dest ): bool {
	if ( ! is_dir( $src ) ) {
		return false;
	}

	wp_mkdir_p( $dest );

	try {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $src, FilesystemIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $item ) {
			/** @var SplFileInfo $item */
			$target = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathname();
			if ( $item->isDir() ) {
				wp_mkdir_p( $target );
				continue;
			}
			if ( ! copy( $item->getPathname(), $target ) ) {
				return false;
			}
		}
	} catch ( Exception $e ) {
		return false;
	}

	return true;
}

/**
 * Copy one bundled plugin into wp-content/plugins when missing or outdated.
 */
function aiad_sync_bundled_plugin( string $slug, string $main_file ): bool {
	$source_dir = aiad_bundled_plugin_source_dir( $slug );
	$dest_dir   = aiad_bundled_plugin_dest_dir( $slug );
	$source_main = trailingslashit( $source_dir ) . $main_file;
	$dest_main   = trailingslashit( $dest_dir ) . $main_file;

	if ( ! is_readable( $source_main ) ) {
		return false;
	}

	$source_version = aiad_bundled_plugin_version( $source_main );
	$option_key     = 'aiad_bundled_plugin_' . $slug . '_version';
	$installed      = aiad_bundled_plugin_version( $dest_main );

	$needs_copy = ! is_readable( $dest_main )
		|| ( $source_version && $source_version !== $installed );

	if ( ! $needs_copy ) {
		return true;
	}

	if ( ! aiad_copy_dir( $source_dir, $dest_dir ) ) {
		return false;
	}

	if ( $source_version ) {
		update_option( $option_key, $source_version, false );
	}

	return true;
}

/**
 * Activate a bundled plugin if it is installed but inactive.
 */
function aiad_activate_bundled_plugin( string $slug, string $main_file ): void {
	$plugin_file = $slug . '/' . $main_file;

	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( is_plugin_active( $plugin_file ) ) {
		return;
	}

	$dest_main = trailingslashit( aiad_bundled_plugin_dest_dir( $slug ) ) . $main_file;
	if ( ! is_readable( $dest_main ) ) {
		return;
	}

	activate_plugin( $plugin_file, '', false, true );
}

/**
 * Sync and activate all bundled plugins after theme deploy.
 */
function aiad_maybe_sync_bundled_plugins(): void {
	static $ran = false;
	if ( $ran ) {
		return;
	}
	$ran = true;

	foreach ( aiad_bundled_plugins() as $slug => $main_file ) {
		if ( ! aiad_sync_bundled_plugin( $slug, $main_file ) ) {
			continue;
		}
		aiad_activate_bundled_plugin( $slug, $main_file );
	}
}
add_action( 'plugins_loaded', 'aiad_maybe_sync_bundled_plugins', 1 );
