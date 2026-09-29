<?php
/**
 * Plugin Name:       AI Awareness Day Debate Network
 * Plugin URI:        https://aiawarenessday.co.uk/
 * Description:       National AI Conversation & Debate Network: school code, email sign-in, SLT approval, class PINs, debates, judges, scoring, results, certificates, Student Voice and reminders (slices 1 to 4).
 * Version:           0.15.0
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

define( 'AIADN_VERSION', '0.15.0' );
define( 'AIADN_PLUGIN_FILE', __FILE__ );
define( 'AIADN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AIADN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-util.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-database.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-mailer.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-schools.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-motions.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-format.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-regions.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-referrals.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-stats.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-debates.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-find.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-privacy.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-nominations.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-scorecards.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-issues.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-certificates.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-results.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-calendar.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-meeting-ics.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-voice.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-qr.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-reminders.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-auth.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-front.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-debate-front.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-result-front.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-voice-front.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-programme-front.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-snapshot-front.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-colleague-front.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-partner-front.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-find-front.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-privacy-front.php';
require_once AIADN_PLUGIN_DIR . 'includes/class-aiadn-nominate-front.php';
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
		AIADN_Reminders::register();
		AIADN_Privacy::register();
		add_action( 'init', array( 'AIADN_Database', 'maybe_upgrade' ) );
	}
}

AIADN_Plugin::instance();
