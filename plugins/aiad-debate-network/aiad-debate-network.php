<?php
/**
 * Plugin Name:       AI Awareness Day Debate Network
 * Plugin URI:        https://aiawarenessday.co.uk/
 * Description:       National AI Conversation & Debate Network: school code, email sign-in, SLT approval and class PINs (slice 1).
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            AI Awareness Day
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       aiad-debate-network
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AIADN_VERSION', '0.1.0' );
define( 'AIADN_PLUGIN_FILE', __FILE__ );
define( 'AIADN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AIADN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-util.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-database.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-mailer.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-schools.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-auth.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-front.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-activator.php';

/**
 * Plugin bootstrap.
 */
final class AIADN_Plugin {

	/** @var self|null */
	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		register_activation_hook( AIADN_PLUGIN_FILE, array( 'AIADN_Activator', 'activate' ) );
		register_deactivation_hook( AIADN_PLUGIN_FILE, array( 'AIADN_Activator', 'deactivate' ) );
		// Hooks are added as soon as the plugin loads (not inside init), so they also work
		// when the theme activates the plugin part-way through a request.
		AIADN_Mailer::register();
		AIADN_Front::register();
		add_action( 'init', array( 'AIADN_Database', 'maybe_upgrade' ) );
	}
}

AIADN_Plugin::instance();
