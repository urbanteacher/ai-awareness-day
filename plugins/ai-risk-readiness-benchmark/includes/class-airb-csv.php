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
	 * Stream submissions CSV download.
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
				'School Phase',
				'Organisation Type',
				'Year Group',
				'Session ID',
				'Risk Level',
				'Alignment Score',
				'Dependency Index',
				'Human Oversight',
				'Privacy Risk',
				'Safeguarding Readiness',
				'Governance Maturity',
				'Certificate ID',
				'Certificate Status',
				'Certificate Name',
				'Certificate Theme',
				'Evidence Quality Score',
				'Evidence Quality Tier',
			)
		);

		foreach ( $rows as $row ) {
			$answers = json_decode( (string) $row->answers, true );
			if ( ! is_array( $answers ) ) {
				$answers = array();
			}
			$certificate = AIRB_Certificates::get_by_submission( (int) $row->id );
			fputcsv(
				$out,
				array(
					$row->id,
					$row->created_at,
					$row->role,
					self::escape_cell( (string) $row->school_name ),
					self::escape_cell( (string) $row->email ),
					self::escape_cell( (string) ( $answers['_school_phase'] ?? '' ) ),
					self::escape_cell( (string) ( $answers['_org_type'] ?? '' ) ),
					self::escape_cell( (string) ( $answers['_year_group'] ?? '' ) ),
					self::escape_cell( (string) ( $row->session_id ?? '' ) ),
					$row->risk_level,
					$row->alignment_score,
					$row->dependency_index,
					self::escape_cell( (string) $row->human_oversight_label ),
					$row->privacy_risk,
					$row->safeguarding_readiness,
					$row->governance_maturity,
					$certificate ? self::escape_cell( (string) $certificate->certificate_id ) : '',
					$certificate ? self::escape_cell( (string) $certificate->status ) : '',
					$certificate ? self::escape_cell( (string) $certificate->participant_name ) : '',
					$certificate ? self::escape_cell( (string) $certificate->evidence_theme ) : '',
					$certificate ? (int) $certificate->evidence_quality_score : '',
					$certificate ? self::escape_cell( (string) $certificate->evidence_quality_tier ) : '',
				)
			);
		}

		fclose( $out );
		exit;
	}

	/**
	 * Stream leads CSV download.
	 */
	public static function export_leads(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'ai-risk-benchmark' ) );
		}

		check_admin_referer( 'airb_export_leads_csv' );

		$args = array(
			'status'    => sanitize_key( (string) ( $_GET['status'] ?? '' ) ),
			'source'    => sanitize_key( (string) ( $_GET['source'] ?? '' ) ),
			'role'      => sanitize_key( (string) ( $_GET['role'] ?? '' ) ),
			'school'    => sanitize_text_field( (string) ( $_GET['school'] ?? '' ) ),
			'date_from' => sanitize_text_field( (string) ( $_GET['date_from'] ?? '' ) ),
			'date_to'   => sanitize_text_field( (string) ( $_GET['date_to'] ?? '' ) ),
			'limit'     => 10000,
			'offset'    => 0,
		);

		$rows     = AIRB_Leads::get_leads( $args );
		$statuses = AIRB_Leads::statuses();

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=airb-leads-' . gmdate( 'Y-m-d' ) . '.csv' );

		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" );

		fputcsv(
			$out,
			array(
				'ID',
				'Date',
				'Status',
				'Source',
				'Role',
				'Name',
				'Email',
				'School',
				'Child School',
				'Submission ID',
				'Session ID',
				'Alignment Score',
				'Risk Level',
				'Readiness Label',
				'Stakeholder Role',
				'Year Group',
				'Interests',
				'Weak Domains',
				'Hub Page',
				'Hub Ref',
				'Hub URL',
				'Checklist Done',
				'Checklist Total',
				'Notes',
				'Message',
			)
		);

		foreach ( $rows as $row ) {
			fputcsv(
				$out,
				array(
					$row->id,
					$row->created_at,
					$statuses[ $row->status ] ?? $row->status,
					$row->source,
					$row->role,
					self::escape_cell( (string) $row->name ),
					self::escape_cell( (string) $row->email ),
					self::escape_cell( (string) $row->school ),
					self::escape_cell( (string) $row->child_school ),
					$row->submission_id,
					self::escape_cell( (string) $row->session_id ),
					$row->alignment_score,
					$row->risk_level,
					self::escape_cell( (string) $row->readiness_level_label ),
					$row->stakeholder_role,
					$row->year_group,
					self::escape_cell( implode( '; ', AIRB_Leads::decode_list( (string) $row->interests ) ) ),
					self::escape_cell( implode( '; ', AIRB_Leads::decode_list( (string) $row->weak_domains ) ) ),
					$row->hub_page,
					$row->hub_ref,
					self::escape_cell( (string) $row->hub_url ),
					$row->checklist_done,
					$row->checklist_total,
					self::escape_cell( (string) ( $row->notes ?? '' ) ),
					self::escape_cell( (string) $row->message ),
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
