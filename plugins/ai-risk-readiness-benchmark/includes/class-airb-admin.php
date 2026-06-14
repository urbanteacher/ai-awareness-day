<?php
/**
 * WordPress admin UI.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin menus and handlers.
 */
class AIRB_Admin {

	/**
	 * Register admin hooks.
	 */
	public static function register(): void {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_airb_save_settings', array( __CLASS__, 'save_settings' ) );
		add_action( 'admin_post_airb_reset_defaults', array( __CLASS__, 'reset_defaults' ) );
		add_action( 'admin_post_airb_update_lead_status', array( __CLASS__, 'update_lead_status' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_export_csv' ) );
	}

	/**
	 * Admin menu pages.
	 */
	public static function menu(): void {
		add_menu_page(
			__( 'AI Risk Benchmark', 'ai-risk-benchmark' ),
			__( 'AI Risk Benchmark', 'ai-risk-benchmark' ),
			'manage_options',
			'airb-benchmark',
			array( __CLASS__, 'render_submissions' ),
			'dashicons-shield-alt',
			58
		);

		add_submenu_page(
			'airb-benchmark',
			__( 'Submissions', 'ai-risk-benchmark' ),
			__( 'Submissions', 'ai-risk-benchmark' ),
			'manage_options',
			'airb-benchmark',
			array( __CLASS__, 'render_submissions' )
		);

		add_submenu_page(
			'airb-benchmark',
			__( 'Leads', 'ai-risk-benchmark' ),
			__( 'Leads', 'ai-risk-benchmark' ),
			'manage_options',
			'airb-leads',
			array( __CLASS__, 'render_leads' )
		);

		add_submenu_page(
			'airb-benchmark',
			__( 'Funnel & events', 'ai-risk-benchmark' ),
			__( 'Funnel & events', 'ai-risk-benchmark' ),
			'manage_options',
			'airb-funnel',
			array( __CLASS__, 'render_funnel' )
		);

		add_submenu_page(
			'airb-benchmark',
			__( 'School Dashboard', 'ai-risk-benchmark' ),
			__( 'School Dashboard', 'ai-risk-benchmark' ),
			'manage_options',
			'airb-school-dashboard',
			array( __CLASS__, 'render_school_dashboard' )
		);

		add_submenu_page(
			'airb-benchmark',
			__( 'Settings', 'ai-risk-benchmark' ),
			__( 'Settings', 'ai-risk-benchmark' ),
			'manage_options',
			'airb-settings',
			array( __CLASS__, 'render_settings' )
		);
	}

	/**
	 * CSV export handler.
	 */
	public static function maybe_export_csv(): void {
		if ( isset( $_GET['airb_export'] ) && 'csv' === $_GET['airb_export'] ) {
			AIRB_Csv::export();
		}
		if ( isset( $_GET['airb_export'] ) && 'leads_csv' === $_GET['airb_export'] ) {
			AIRB_Csv::export_leads();
		}
	}

	/**
	 * Submissions list page.
	 */
	public static function render_submissions(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$per_page = 50;
		$paged    = max( 1, (int) ( $_GET['paged'] ?? 1 ) );

		$filters = array(
			'role'       => sanitize_key( (string) ( $_GET['role'] ?? '' ) ),
			'risk_level' => sanitize_text_field( (string) ( $_GET['risk_level'] ?? '' ) ),
			'school'     => sanitize_text_field( (string) ( $_GET['school'] ?? '' ) ),
			'date_from'  => sanitize_text_field( (string) ( $_GET['date_from'] ?? '' ) ),
			'date_to'    => sanitize_text_field( (string) ( $_GET['date_to'] ?? '' ) ),
			'limit'      => $per_page,
			'offset'     => ( $paged - 1 ) * $per_page,
		);

		$rows  = AIRB_Database::get_submissions( $filters );
		$total = AIRB_Database::count_submissions( $filters );
		$roles = AIRB_Defaults::roles();
		$stats = array(
			'total'            => AIRB_Database::count_submissions( array() ),
			'with_school'      => AIRB_Database::count_with_school(),
			'benchmark_consent'=> AIRB_Database::count_submissions( array( 'consent' => 1 ) ),
			'contact_opt_ins'  => AIRB_Database::count_contact_opt_ins(),
		);

		include AIRB_PLUGIN_DIR . 'admin/views/submissions.php';
	}

	/**
	 * Leads list and detail.
	 */
	public static function render_leads(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$per_page = 50;
		$paged    = max( 1, (int) ( $_GET['paged'] ?? 1 ) );

		$filters = array(
			'status'    => sanitize_key( (string) ( $_GET['status'] ?? '' ) ),
			'source'    => sanitize_key( (string) ( $_GET['source'] ?? '' ) ),
			'role'      => sanitize_key( (string) ( $_GET['role'] ?? '' ) ),
			'school'    => sanitize_text_field( (string) ( $_GET['school'] ?? '' ) ),
			'date_from' => sanitize_text_field( (string) ( $_GET['date_from'] ?? '' ) ),
			'date_to'   => sanitize_text_field( (string) ( $_GET['date_to'] ?? '' ) ),
			'limit'     => $per_page,
			'offset'    => ( $paged - 1 ) * $per_page,
		);

		$detail_id = max( 0, (int) ( $_GET['lead_id'] ?? 0 ) );
		$detail    = $detail_id ? AIRB_Leads::get( $detail_id ) : null;
		$rows      = AIRB_Leads::get_leads( $filters );
		$total     = AIRB_Leads::count_leads( $filters );
		$roles     = AIRB_Defaults::roles();
		$statuses  = AIRB_Leads::statuses();
		$status_counts = AIRB_Leads::count_by_status();

		include AIRB_PLUGIN_DIR . 'admin/views/leads.php';
	}

	/**
	 * Update lead status from admin.
	 */
	public static function update_lead_status(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'ai-risk-benchmark' ) );
		}
		check_admin_referer( 'airb_update_lead_status' );

		$id     = max( 0, (int) ( $_POST['lead_id'] ?? 0 ) );
		$status = sanitize_key( (string) ( $_POST['status'] ?? '' ) );

		if ( $id && AIRB_Leads::update_status( $id, $status ) ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'page'    => 'airb-leads',
						'lead_id' => $id,
						'updated' => '1',
					),
					admin_url( 'admin.php' )
				)
			);
			exit;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=airb-leads' ) );
		exit;
	}

	/**
	 * Funnel metrics and event log.
	 */
	public static function render_funnel(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$per_page = 50;
		$paged    = max( 1, (int) ( $_GET['paged'] ?? 1 ) );

		$filters = array(
			'event_type' => sanitize_key( (string) ( $_GET['event_type'] ?? '' ) ),
			'role'       => sanitize_key( (string) ( $_GET['role'] ?? '' ) ),
			'date_from'  => sanitize_text_field( (string) ( $_GET['date_from'] ?? '' ) ),
			'date_to'    => sanitize_text_field( (string) ( $_GET['date_to'] ?? '' ) ),
			'limit'      => $per_page,
			'offset'     => ( $paged - 1 ) * $per_page,
		);

		$event_counts   = AIRB_Events::count_by_type( $filters );
		$role_counts    = AIRB_Database::count_by_role();
		$weak_domains = AIRB_Events::weak_domain_counts();
		$events       = AIRB_Events::get_events( $filters );
		$total_events = AIRB_Events::count_events( $filters );
		$total_subs   = AIRB_Database::count_submissions( array() );
		$event_labels   = AIRB_Events::event_labels();
		$roles          = AIRB_Defaults::roles();
		$domains        = AIRB_Defaults::domains();

		include AIRB_PLUGIN_DIR . 'admin/views/funnel.php';
	}

	/**
	 * School dashboard admin page.
	 */
	public static function render_school_dashboard(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$school = sanitize_text_field( (string) ( $_GET['school'] ?? '' ) );
		$rollup = $school ? AIRB_School_Dashboard::get_rollup( $school ) : null;
		$schools = AIRB_School_Dashboard::list_schools();

		include AIRB_PLUGIN_DIR . 'admin/views/school-dashboard.php';
	}

	/**
	 * Settings page.
	 */
	public static function render_settings(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$config  = AIRB_Config::get();
		$domains = AIRB_Defaults::domains();
		$roles   = AIRB_Defaults::roles();
		$saved   = isset( $_GET['saved'] );

		include AIRB_PLUGIN_DIR . 'admin/views/settings.php';
	}

	/**
	 * Save settings from admin form.
	 */
	public static function save_settings(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'ai-risk-benchmark' ) );
		}
		check_admin_referer( 'airb_save_settings' );

		$config = AIRB_Config::get();

		$config['disclaimer'] = sanitize_textarea_field( (string) ( $_POST['disclaimer'] ?? '' ) );
		$config['intro']      = sanitize_textarea_field( (string) ( $_POST['intro'] ?? '' ) );
		$config['positioning'] = array(
			'headline' => sanitize_text_field( (string) ( $_POST['positioning_headline'] ?? '' ) ),
			'problem'  => sanitize_textarea_field( (string) ( $_POST['positioning_problem'] ?? '' ) ),
			'solution' => sanitize_textarea_field( (string) ( $_POST['positioning_solution'] ?? '' ) ),
		);
		$config['consultation_cta'] = array(
			'title' => sanitize_text_field( (string) ( $_POST['cta_title'] ?? '' ) ),
			'url'   => esc_url_raw( (string) ( $_POST['cta_url'] ?? '' ) ),
			'text'  => sanitize_text_field( (string) ( $_POST['cta_text'] ?? '' ) ),
		);
		$config['gateway'] = array(
			'headline' => sanitize_text_field( (string) ( $_POST['gateway_headline'] ?? '' ) ),
			'intro'    => sanitize_textarea_field( (string) ( $_POST['gateway_intro'] ?? '' ) ),
			'track_progress' => array(
				'title'    => sanitize_text_field( (string) ( $_POST['gateway_track_title'] ?? '' ) ),
				'body'     => sanitize_textarea_field( (string) ( $_POST['gateway_track_body'] ?? '' ) ),
				'cta_text' => sanitize_text_field( (string) ( $_POST['gateway_track_cta'] ?? '' ) ),
				'cta_url'  => esc_url_raw( (string) ( $_POST['gateway_track_url'] ?? '' ) ),
			),
			'book_cpd' => array(
				'title'    => sanitize_text_field( (string) ( $_POST['gateway_cpd_title'] ?? '' ) ),
				'body'     => sanitize_textarea_field( (string) ( $_POST['gateway_cpd_body'] ?? '' ) ),
				'cta_text' => sanitize_text_field( (string) ( $_POST['gateway_cpd_cta'] ?? '' ) ),
				'cta_url'  => esc_url_raw( (string) ( $_POST['gateway_cpd_url'] ?? '' ) ),
			),
			'book_consultation' => array(
				'title'    => sanitize_text_field( (string) ( $_POST['gateway_consult_title'] ?? '' ) ),
				'body'     => sanitize_textarea_field( (string) ( $_POST['gateway_consult_body'] ?? '' ) ),
				'cta_text' => sanitize_text_field( (string) ( $_POST['gateway_consult_cta'] ?? '' ) ),
				'cta_url'  => esc_url_raw( (string) ( $_POST['gateway_consult_url'] ?? '' ) ),
			),
		);

		// Questions — rebuild from POST arrays.
		if ( ! empty( $_POST['q_id'] ) && is_array( $_POST['q_id'] ) ) {
			$questions = array();
			$count     = count( $_POST['q_id'] );
			for ( $i = 0; $i < $count; $i++ ) {
				$q = array(
					'id'     => sanitize_key( (string) $_POST['q_id'][ $i ] ),
					'role'   => sanitize_key( (string) ( $_POST['q_role'][ $i ] ?? '' ) ),
					'domain' => sanitize_key( (string) ( $_POST['q_domain'][ $i ] ?? '' ) ),
					'type'   => sanitize_key( (string) ( $_POST['q_type'][ $i ] ?? 'radio' ) ),
					'text'   => sanitize_textarea_field( (string) ( $_POST['q_text'][ $i ] ?? '' ) ),
				);
				$opts_raw = (string) ( $_POST['q_options'][ $i ] ?? '' );
				$q['options'] = self::parse_options_lines( $opts_raw );
				if ( $q['id'] && $q['text'] ) {
					$questions[] = $q;
				}
			}
			if ( $questions ) {
				$config['questions'] = $questions;
			}
		}

		// Recommendations.
		if ( ! empty( $_POST['r_domain'] ) && is_array( $_POST['r_domain'] ) ) {
			$recs  = array();
			$rc    = count( $_POST['r_domain'] );
			for ( $i = 0; $i < $rc; $i++ ) {
				$rec = array(
					'domain'   => sanitize_key( (string) $_POST['r_domain'][ $i ] ),
					'min_band' => sanitize_key( (string) ( $_POST['r_min_band'][ $i ] ?? 'high' ) ),
					'title'    => sanitize_text_field( (string) ( $_POST['r_title'][ $i ] ?? '' ) ),
					'body'     => sanitize_textarea_field( (string) ( $_POST['r_body'][ $i ] ?? '' ) ),
					'cta_text' => sanitize_text_field( (string) ( $_POST['r_cta_text'][ $i ] ?? '' ) ),
					'cta_url'  => esc_url_raw( (string) ( $_POST['r_cta_url'][ $i ] ?? '' ) ),
				);
				if ( $rec['title'] ) {
					$recs[] = $rec;
				}
			}
			if ( $recs ) {
				$config['recommendations'] = $recs;
			}
		}

		AIRB_Config::save( $config );
		wp_safe_redirect( add_query_arg( 'saved', '1', admin_url( 'admin.php?page=airb-settings' ) ) );
		exit;
	}

	/**
	 * Parse option lines: value|label|score per line.
	 *
	 * @param string $raw Raw text.
	 * @return array<int, array<string, mixed>>
	 */
	private static function parse_options_lines( string $raw ): array {
		$out   = array();
		$lines = preg_split( '/\r\n|\r|\n/', $raw ) ?: array();
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( ! $line ) {
				continue;
			}
			$parts = array_map( 'trim', explode( '|', $line ) );
			if ( count( $parts ) >= 2 ) {
				$out[] = array(
					'value' => sanitize_key( $parts[0] ),
					'label' => sanitize_text_field( $parts[1] ),
					'score' => isset( $parts[2] ) ? (int) $parts[2] : 0,
				);
			}
		}
		return $out;
	}

	/**
	 * Reset config to defaults.
	 */
	public static function reset_defaults(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'ai-risk-benchmark' ) );
		}
		check_admin_referer( 'airb_reset_defaults' );
		update_option( AIRB_OPTION_CONFIG, AIRB_Defaults::config(), false );
		wp_safe_redirect( add_query_arg( 'saved', '1', admin_url( 'admin.php?page=airb-settings' ) ) );
		exit;
	}

	/**
	 * Format options for textarea editing.
	 *
	 * @param array<int, array<string, mixed>> $options Options.
	 */
	public static function options_to_lines( array $options ): string {
		$lines = array();
		foreach ( $options as $opt ) {
			$lines[] = sprintf(
				'%s|%s|%d',
				(string) ( $opt['value'] ?? '' ),
				(string) ( $opt['label'] ?? '' ),
				(int) ( $opt['score'] ?? 0 )
			);
		}
		return implode( "\n", $lines );
	}
}
