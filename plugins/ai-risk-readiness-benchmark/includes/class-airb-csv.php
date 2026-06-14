<?php
/**
 * CSV export for admin.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Export submissions as CSV.
 */
class AIRB_Csv {

	/**
	 * Stream CSV download.
	 */
	public static function export(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'ai-risk-benchmark' ) );
		}

		check_admin_referer( 'airb_export_csv' );

		$args = array(
			'role'       => sanitize_key( (string) ( $_GET['role'] ?? '' ) ),
			'risk_level' => sanitize_text_field( (string) ( $_GET['risk_level'] ?? '' ) ),
			'school'     => sanitize_text_field( (string) ( $_GET['school'] ?? '' ) ),
			'date_from'  => sanitize_text_field( (string) ( $_GET['date_from'] ?? '' ) ),
			'date_to'    => sanitize_text_field( (string) ( $_GET['date_to'] ?? '' ) ),
			'limit'      => 10000,
			'offset'     => 0,
		);

		$rows = AIRB_Database::get_submissions( $args );

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=airb-submissions-' . gmdate( 'Y-m-d' ) . '.csv' );

		$out = fopen( 'php://output', 'w' );

		// UTF-8 BOM so Excel renders accented school names correctly.
		fwrite( $out, "\xEF\xBB\xBF" );

		fputcsv(
			$out,
			array(
				'ID',
				'Date',
				'Role',
				'School',
				'Email',
				'Session ID',
				'Risk Level',
				'Alignment Score',
				'Dependency Index',
				'Human Oversight',
				'Privacy Risk',
				'Safeguarding Readiness',
				'Governance Maturity',
			)
		);

		foreach ( $rows as $row ) {
			fputcsv(
				$out,
				array(
					$row->id,
					$row->created_at,
					$row->role,
					self::escape_cell( (string) $row->school_name ),
					self::escape_cell( (string) $row->email ),
					self::escape_cell( (string) ( $row->session_id ?? '' ) ),
					$row->risk_level,
					$row->alignment_score,
					$row->dependency_index,
					self::escape_cell( (string) $row->human_oversight_label ),
					$row->privacy_risk,
					$row->safeguarding_readiness,
					$row->governance_maturity,
				)
			);
		}

		fclose( $out );
		exit;
	}

	/**
	 * Neutralise CSV/spreadsheet formula injection.
	 *
	 * A cell beginning with =, +, -, @, tab or carriage return can be executed
	 * as a formula by Excel/Sheets. Prefix such values with a single quote.
	 *
	 * @param string $value Raw cell value.
	 */
	private static function escape_cell( string $value ): string {
		if ( '' !== $value && in_array( $value[0], array( '=', '+', '-', '@', "\t", "\r" ), true ) ) {
			return "'" . $value;
		}
		return $value;
	}
}
