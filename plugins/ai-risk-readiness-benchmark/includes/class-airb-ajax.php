<?php
/**
 * AJAX handlers.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front-end AJAX.
 */
class AIRB_Ajax {

	/**
	 * Register hooks.
	 */
	public static function register(): void {
		add_action( 'wp_ajax_airb_submit_benchmark', array( __CLASS__, 'submit' ) );
		add_action( 'wp_ajax_nopriv_airb_submit_benchmark', array( __CLASS__, 'submit' ) );
		add_action( 'wp_ajax_airb_email_report', array( __CLASS__, 'email_report' ) );
		add_action( 'wp_ajax_nopriv_airb_email_report', array( __CLASS__, 'email_report' ) );
	}

	/**
	 * Verify nonce.
	 */
	private static function verify_nonce(): void {
		if ( ! check_ajax_referer( 'airb_benchmark_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'ai-risk-benchmark' ) ), 403 );
		}
	}

	/**
	 * Submit benchmark results.
	 */
	public static function submit(): void {
		self::verify_nonce();

		$role    = sanitize_key( (string) ( $_POST['role'] ?? '' ) );
		$answers = isset( $_POST['answers'] ) ? json_decode( wp_unslash( (string) $_POST['answers'] ), true ) : array();
		$consent = ! empty( $_POST['consent'] );

		if ( ! $role || ! is_array( $answers ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid submission.', 'ai-risk-benchmark' ) ) );
		}

		$school  = sanitize_text_field( (string) ( $_POST['school_name'] ?? '' ) );
		$email   = sanitize_email( (string) ( $_POST['email'] ?? '' ) );
		$profile = array(
			'school_phase' => sanitize_key( (string) ( $_POST['school_phase'] ?? '' ) ),
			'org_type'     => sanitize_key( (string) ( $_POST['org_type'] ?? '' ) ),
		);

		$config  = AIRB_Config::get();
		$results = AIRB_Scoring::calculate( $role, $answers, $config );
		$results = AIRB_Funnel::enrich( $results, $role, $profile, $config );
		$results['gateway'] = AIRB_Pathway::build_gateway( $role, $config, $school, $consent );

		if ( ! empty( $results['aad_promo']['cta_url'] ) && is_array( $results['gateway'] ) ) {
			$promo = $results['aad_promo'];
			$results['gateway']['cards'][] = array(
				'key'      => 'aad_day',
				'title'    => (string) $promo['title'],
				'body'     => (string) $promo['body'],
				'cta_text' => (string) $promo['cta_text'],
				'cta_url'  => (string) $promo['cta_url'],
			);
		}

		if ( $profile['school_phase'] ) {
			$answers['_school_phase'] = $profile['school_phase'];
		}
		if ( $profile['org_type'] ) {
			$answers['_org_type'] = $profile['org_type'];
		}

		$id = 0;
		if ( $consent ) {
			$id = AIRB_Database::insert(
				array(
					'role'                   => $role,
					'school_name'            => $school,
					'email'                  => $email,
					'consent'                => 1,
					'risk_level'             => $results['risk_level'],
					'alignment_score'        => $results['alignment_score'],
					'dependency_index'       => $results['dependency_index'],
					'human_oversight_label'  => $results['human_oversight_label'],
					'privacy_risk'           => $results['privacy_risk'],
					'safeguarding_readiness' => $results['safeguarding_readiness'],
					'governance_maturity'    => $results['governance_maturity'],
					'domain_scores'          => $results['domain_scores'],
					'answers'                => $answers,
					'recommendations'        => $results['recommendations'],
				)
			);
		}

		// Attach national benchmark comparison (null until the privacy-floor
		// sample size is reached for this role).
		$results['benchmark'] = AIRB_Database::get_benchmark_stats(
			$role,
			isset( $results['alignment_score'] ) ? (int) $results['alignment_score'] : null
		);

		wp_send_json_success(
			array(
				'submission_id' => $id,
				'results'       => $results,
			)
		);
	}

	/**
	 * Email printable report.
	 */
	public static function email_report(): void {
		self::verify_nonce();

		$email   = sanitize_email( (string) ( $_POST['email'] ?? '' ) );
		$results = isset( $_POST['results'] ) ? json_decode( wp_unslash( (string) $_POST['results'] ), true ) : null;
		$role    = sanitize_key( (string) ( $_POST['role'] ?? '' ) );

		if ( ! is_email( $email ) || ! is_array( $results ) ) {
			wp_send_json_error( array( 'message' => __( 'Valid email and results required.', 'ai-risk-benchmark' ) ) );
		}

		// Rate limit: 3 emails per fingerprint per hour to stop the open relay being abused for spam.
		$fingerprint = md5(
			( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' )
			. '|'
			. ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '' )
		);
		$rate_key = 'airb_email_rate_' . $fingerprint;
		$count    = (int) get_transient( $rate_key );
		if ( $count >= 3 ) {
			wp_send_json_error(
				array( 'message' => __( 'Too many report emails requested. Please try again later or use print/download.', 'ai-risk-benchmark' ) ),
				429
			);
		}

		$config = AIRB_Config::get();
		$body   = AIRB_Report::build_email_html( $role, $results, $config );
		$subject = sprintf(
			/* translators: %s: site name */
			__( '%s — AI Risk & Readiness Benchmark Report', 'ai-risk-benchmark' ),
			get_bloginfo( 'name' )
		);

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$sent    = wp_mail( $email, $subject, $body, $headers );

		if ( ! $sent ) {
			wp_send_json_error( array( 'message' => __( 'Could not send email. Try print/download instead.', 'ai-risk-benchmark' ) ) );
		}

		set_transient( $rate_key, $count + 1, HOUR_IN_SECONDS );

		wp_send_json_success( array( 'message' => __( 'Report sent.', 'ai-risk-benchmark' ) ) );
	}
}
