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
 * Sentinel paths used to detect stale plugin copies when the header version was not bumped.
 *
 * @return array<string, array<int, string>> slug => paths relative to plugin root
 */
function aiad_bundled_plugin_sentinel_files(): array {
	return array(
		'ai-risk-readiness-benchmark' => array(
			'public/js/airb-front.js',
			'admin/views/submissions.php',
			'includes/class-airb-admin.php',
		),
	);
}

/**
 * Whether the bundled plugin in wp-content/plugins is older than the theme copy.
 */
function aiad_bundled_plugin_is_stale( string $slug, string $main_file ): bool {
	$source_dir  = aiad_bundled_plugin_source_dir( $slug );
	$dest_dir    = aiad_bundled_plugin_dest_dir( $slug );
	$source_main = trailingslashit( $source_dir ) . $main_file;
	$dest_main   = trailingslashit( $dest_dir ) . $main_file;

	if ( ! is_readable( $source_main ) ) {
		return false;
	}

	if ( ! is_readable( $dest_main ) ) {
		return true;
	}

	$source_version = aiad_bundled_plugin_version( $source_main );
	$installed      = aiad_bundled_plugin_version( $dest_main );

	if ( $source_version && $source_version !== $installed ) {
		return true;
	}

	$sentinels = aiad_bundled_plugin_sentinel_files();
	if ( isset( $sentinels[ $slug ] ) ) {
		foreach ( (array) $sentinels[ $slug ] as $relative ) {
			$source_file = trailingslashit( $source_dir ) . $relative;
			$dest_file   = trailingslashit( $dest_dir ) . $relative;
			if ( is_readable( $source_file ) && ! is_readable( $dest_file ) ) {
				return true;
			}
			if ( is_readable( $source_file ) && is_readable( $dest_file ) ) {
				if ( (int) filemtime( $source_file ) > (int) filemtime( $dest_file ) ) {
					return true;
				}
			}
		}
	}

	return false;
}

/**
 * Copy one bundled plugin into wp-content/plugins when missing or outdated.
 *
 * @return bool True when files were copied; false when unchanged or copy failed.
 */
function aiad_sync_bundled_plugin( string $slug, string $main_file ): bool {
	$source_dir  = aiad_bundled_plugin_source_dir( $slug );
	$dest_dir    = aiad_bundled_plugin_dest_dir( $slug );
	$source_main = trailingslashit( $source_dir ) . $main_file;
	$dest_main   = trailingslashit( $dest_dir ) . $main_file;

	if ( ! is_readable( $source_main ) ) {
		return false;
	}

	if ( ! aiad_bundled_plugin_is_stale( $slug, $main_file ) ) {
		return false;
	}

	if ( ! aiad_copy_dir( $source_dir, $dest_dir ) ) {
		return false;
	}

	$source_version = aiad_bundled_plugin_version( $source_main );
	$option_key     = 'aiad_bundled_plugin_' . $slug . '_version';
	if ( $source_version ) {
		update_option( $option_key, $source_version, false );
	}

	return true;
}

/**
 * Activate a bundled plugin if it is installed but inactive.
 *
 * @return bool True when activation was attempted and the plugin was previously inactive.
 */
function aiad_activate_bundled_plugin( string $slug, string $main_file ): bool {
	$plugin_file = $slug . '/' . $main_file;

	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	if ( is_plugin_active( $plugin_file ) ) {
		return false;
	}

	$dest_main = trailingslashit( aiad_bundled_plugin_dest_dir( $slug ) ) . $main_file;
	if ( ! is_readable( $dest_main ) ) {
		return false;
	}

	activate_plugin( $plugin_file, '', false, true );
	return is_plugin_active( $plugin_file );
}

/**
 * Load a bundled plugin directly from the theme when copy/activate is blocked on hosting.
 */
function aiad_load_bundled_plugin_from_theme( string $slug, string $main_file ): bool {
	$source_main = trailingslashit( aiad_bundled_plugin_source_dir( $slug ) ) . $main_file;
	if ( ! is_readable( $source_main ) ) {
		return false;
	}

	if ( 'ai-risk-readiness-benchmark' === $slug ) {
		if ( class_exists( 'AIRB_Plugin', false ) ) {
			if ( class_exists( 'AIRB_Activator' ) && class_exists( 'AIRB_Database' ) ) {
				global $wpdb;
				$table = AIRB_Database::table_name();
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
					AIRB_Activator::activate();
				}
			}
			return false;
		}
		if ( shortcode_exists( 'ai_risk_benchmark' ) ) {
			return false;
		}
	}

	require_once $source_main;

	if ( 'ai-risk-readiness-benchmark' === $slug && class_exists( 'AIRB_Activator' ) && class_exists( 'AIRB_Database' ) ) {
		global $wpdb;
		$table = AIRB_Database::table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			AIRB_Activator::activate();
		}
	}

	return true;
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
		$copied    = aiad_sync_bundled_plugin( $slug, $main_file );
		$activated = aiad_activate_bundled_plugin( $slug, $main_file );
		if ( $copied || $activated ) {
			set_transient( 'aiad_flush_rewrites', 1, MINUTE_IN_SECONDS );
		}
	}

	// Hosting often blocks copying into wp-content/plugins — load from theme instead.
	foreach ( aiad_bundled_plugins() as $slug => $main_file ) {
		if ( aiad_load_bundled_plugin_from_theme( $slug, $main_file ) ) {
			set_transient( 'aiad_flush_rewrites', 1, MINUTE_IN_SECONDS );
		}
	}
}
// Theme functions.php loads after plugins_loaded, so that hook is too early.
add_action( 'after_setup_theme', 'aiad_maybe_sync_bundled_plugins', 1 );
aiad_maybe_sync_bundled_plugins();
