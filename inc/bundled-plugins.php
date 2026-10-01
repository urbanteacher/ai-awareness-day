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
		// First: the theme's header, footer, homepage and page blocks, and the other two plugins' blocks, are aiad-core's.
		'aiad-core'                   => 'aiad-core.php',
		'ai-risk-readiness-benchmark' => 'ai-risk-readiness-benchmark.php',
		'aiad-debate-network'         => 'aiad-debate-network.php',
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

	// A host that does not let the theme write into wp-content/plugins: say no once, quietly, and let the plugin load
	// from the theme folder (aiad_load_bundled_plugin_from_theme()) instead of failing on every file of every request.
	if ( ! wp_mkdir_p( $dest ) || ! wp_is_writable( $dest ) ) {
		return false;
	}

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
		'aiad-core'                   => array(
			'aiad-core.php',
			'includes/modules.php',
			'includes/blocks.php',
			'build/blocks-manifest.php',
			'build/editors/index.js',
			'modules/partner-profile.php',
			'modules/site-settings.php',
			'modules/shortcode-migration.php',
			'modules/post-types/resource-seeds.php',
			'modules/seo/sharing.php',
		),
		'ai-risk-readiness-benchmark' => array(
			'public/js/airb-front.js',
			'public/js/airb-core.js',
			'public/js/airb-results.js',
			'public/js/airb-share.js',
			'includes/class-airb-copy-tiers.php',
			'includes/class-airb-components.php',
			'includes/data/copy-tiers-teacher.json',
			'includes/data/copy-tiers-leader.json',
			'admin/views/submissions.php',
			'includes/class-airb-admin.php',
		),
		'aiad-debate-network'         => array(
			'includes/class-aiadn-activator.php',
			'includes/class-aiadn-auth.php',
			'includes/class-aiadn-calendar.php',
			'includes/class-aiadn-certificates.php',
			'includes/class-aiadn-colleague-front.php',
			'includes/class-aiadn-database.php',
			'includes/class-aiadn-debate-front.php',
			'includes/class-aiadn-debates.php',
			'includes/class-aiadn-find-front.php',
			'includes/class-aiadn-find.php',
			'includes/class-aiadn-format.php',
			'includes/class-aiadn-front.php',
			'includes/class-aiadn-golive.php',
			'includes/class-aiadn-issues.php',
			'includes/class-aiadn-mailer.php',
			'includes/class-aiadn-meeting-ics.php',
			'includes/class-aiadn-motions.php',
			'includes/class-aiadn-nominate-front.php',
			'includes/class-aiadn-nominations.php',
			'includes/class-aiadn-partner-front.php',
			'includes/class-aiadn-privacy.php',
			'includes/class-aiadn-programme-front.php',
			'includes/class-aiadn-qr.php',
			'includes/class-aiadn-referrals.php',
			'includes/class-aiadn-regions.php',
			'includes/class-aiadn-reminders.php',
			'includes/class-aiadn-result-front.php',
			'includes/class-aiadn-results.php',
			'includes/class-aiadn-schools.php',
			'includes/class-aiadn-scorecards.php',
			'includes/class-aiadn-snapshot-front.php',
			'includes/class-aiadn-stats.php',
			'includes/class-aiadn-util.php',
			'includes/class-aiadn-voice-front.php',
			'includes/class-aiadn-voice.php',
			'public/aiadn.css',
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

	if ( 'aiad-core' === $slug ) {
		// The theme has already loaded this plugin's modules from its own copy in this request (aiad_require_core_module()),
		// so including the copied plugin now, as activate_plugin() does, would declare them twice. aiad-core has no
		// activation hook, so listing it as active is all activation does; WordPress loads it from wp-content/plugins on
		// the next request, and the theme then skips its own copies.
		$active   = (array) get_option( 'active_plugins', array() );
		$active[] = $plugin_file;
		update_option( 'active_plugins', array_values( array_unique( $active ) ) );
		return true;
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

	if ( 'aiad-core' === $slug ) {
		if ( defined( 'AIAD_CORE_VERSION' ) ) {
			return false; // Loaded already: from wp-content/plugins, or from the theme earlier in this request.
		}
		require_once $source_main; // Its modules are the theme's own copies already loaded in this request, so nothing is declared twice.
		return true;
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

	if ( 'aiad-debate-network' === $slug ) {
		if ( class_exists( 'AIADN_Plugin', false ) ) {
			return false;
		}
		require_once $source_main;
		// Hosting that blocks plugin activation never runs the activator, so make sure the tables exist.
		if ( class_exists( 'AIADN_Database' ) && ! AIADN_Database::tables_exist() ) {
			AIADN_Database::create_tables();
		}
		return true;
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
// Activation validates translated plugin headers. Wait for init so translations are safe,
// but run before plugins' own init callbacks and the content migrations (priorities 30–40).
add_action( 'init', 'aiad_maybe_sync_bundled_plugins', 0 );
