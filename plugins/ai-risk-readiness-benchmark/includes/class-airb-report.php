<?php
/**
 * Printable / emailable HTML reports.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Report builder.
 */
class AIRB_Report {

	/**
	 * Build HTML email body.
	 *
	 * @param string               $role    Role slug.
	 * @param array<string, mixed> $results Scored results.
	 * @param array<string, mixed> $config  Config.
	 */
	public static function build_email_html( string $role, array $results, array $config ): string {
		$roles   = AIRB_Defaults::roles();
		$role_lbl = $roles[ $role ] ?? $role;

		ob_start();
		include AIRB_PLUGIN_DIR . 'templates/report-email.php';
		return (string) ob_get_clean();
	}

	/**
	 * Render print-friendly report fragment for front-end.
	 *
	 * @param string               $role    Role.
	 * @param array<string, mixed> $results Results.
	 */
	public static function render_print_fragment( string $role, array $results ): string {
		$config = AIRB_Config::get();
		ob_start();
		$airb_report_role    = $role;
		$airb_report_results = $results;
		$airb_report_config  = $config;
		include AIRB_PLUGIN_DIR . 'templates/report-print.php';
		return (string) ob_get_clean();
	}
}
