<?php
/**
 * Plugin Name:       AI Risk & Readiness Benchmark
 * Plugin URI:        https://aiawarenessday.co.uk/
 * Description:       DfE-aligned AI Risk & Readiness Benchmark for UK schools. Shortcodes: [ai_risk_benchmark] [ai_risk_school_dashboard]
 * Version:           1.9.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            AI Awareness Day
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ai-risk-benchmark
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AIRB_VERSION', '1.9.0' );
define( 'AIRB_PLUGIN_FILE', __FILE__ );
define( 'AIRB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AIRB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'AIRB_TABLE', 'airb_submissions' );
define( 'AIRB_OPTION_CONFIG', 'airb_benchmark_config' );

require_once AIRB_PLUGIN_DIR . 'includes/class-airb-defaults.php';
require_once AIRB_PLUGIN_DIR . 'includes/class-airb-config.php';
require_once AIRB_PLUGIN_DIR . 'includes/class-airb-database.php';
require_once AIRB_PLUGIN_DIR . 'includes/class-airb-scoring.php';
require_once AIRB_PLUGIN_DIR . 'includes/class-airb-questions.php';
require_once AIRB_PLUGIN_DIR . 'includes/class-airb-pathway.php';
require_once AIRB_PLUGIN_DIR . 'includes/class-airb-funnel.php';
require_once AIRB_PLUGIN_DIR . 'includes/class-airb-activator.php';
require_once AIRB_PLUGIN_DIR . 'includes/class-airb-shortcode.php';
require_once AIRB_PLUGIN_DIR . 'includes/class-airb-ajax.php';
require_once AIRB_PLUGIN_DIR . 'includes/class-airb-report.php';
require_once AIRB_PLUGIN_DIR . 'includes/class-airb-csv.php';
require_once AIRB_PLUGIN_DIR . 'includes/class-airb-school-dashboard.php';
require_once AIRB_PLUGIN_DIR . 'includes/class-airb-admin.php';

/**
 * Main plugin bootstrap.
 */
final class AIRB_Plugin {

	/**
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * @return self
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		register_activation_hook( AIRB_PLUGIN_FILE, array( 'AIRB_Activator', 'activate' ) );
		register_deactivation_hook( AIRB_PLUGIN_FILE, array( 'AIRB_Activator', 'deactivate' ) );

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'ai-risk-benchmark', false, dirname( plugin_basename( AIRB_PLUGIN_FILE ) ) . '/languages' );
	}

	public function init(): void {
		AIRB_Shortcode::register();
		AIRB_School_Dashboard::register();
		AIRB_Ajax::register();

		if ( is_admin() ) {
			AIRB_Admin::register();
		}
	}
}

AIRB_Plugin::instance();
