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
		add_action( 'wp_ajax_airb_track_event', array( __CLASS__, 'track_event' ) );
		add_action( 'wp_ajax_nopriv_airb_track_event', array( __CLASS__, 'track_event' ) );
		add_action( 'wp_ajax_airb_submit_interest', array( __CLASS__, 'submit_interest' ) );
		add_action( 'wp_ajax_nopriv_airb_submit_interest', array( __CLASS__, 'submit_interest' ) );
		add_action( 'wp_ajax_airb_allocate_certificate', array( __CLASS__, 'allocate_certificate' ) );
		add_action( 'wp_ajax_nopriv_airb_allocate_certificate', array( __CLASS__, 'allocate_certificate' ) );
		add_action( 'wp_ajax_airb_lookup_certificate', array( __CLASS__, 'lookup_certificate' ) );
		add_action( 'wp_ajax_nopriv_airb_lookup_certificate', array( __CLASS__, 'lookup_certificate' ) );
		add_action( 'wp_ajax_airb_validate_certificate_evidence', array( __CLASS__, 'validate_certificate_evidence' ) );
		add_action( 'wp_ajax_nopriv_airb_validate_certificate_evidence', array( __CLASS__, 'validate_certificate_evidence' ) );
		add_action( 'wp_ajax_airb_get_hub_context', array( __CLASS__, 'get_hub_context' ) );
		add_action( 'wp_ajax_nopriv_airb_get_hub_context', array( __CLASS__, 'get_hub_context' ) );
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
	 * Request fingerprint for lightweight anonymous rate limiting.
	 */
	private static function request_fingerprint(): string {
		return md5(
			( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' )
			. '|'
			. ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '' )
		);
	}

	/**
	 * Enforce a transient-backed rate limit for public AJAX endpoints.
	 */
	private static function enforce_rate_limit( string $scope, int $max, int $ttl ): void {
		$key   = 'airb_rate_' . sanitize_key( $scope ) . '_' . self::request_fingerprint();
		$count = (int) get_transient( $key );
		if ( $count >= $max ) {
			wp_send_json_error(
				array( 'message' => __( 'Too many requests. Please wait a moment and try again.', 'ai-risk-benchmark' ) ),
				429
			);
		}
		set_transient( $key, $count + 1, $ttl );
	}

	/**
	 * Whether a submitted role is one of the configured benchmark roles.
	 */
	private static function is_valid_role( string $role ): bool {
		return isset( AIRB_Defaults::roles()[ sanitize_key( $role ) ] );
	}

	/**
	 * Rebuild a report-safe results payload from a saved submission row.
	 *
	 * @return array<string,mixed>
	 */
	private static function results_from_submission( object $row ): array {
		$domain_scores   = json_decode( (string) ( $row->domain_scores ?? '{}' ), true );
		$recommendations = json_decode( (string) ( $row->recommendations ?? '[]' ), true );
		if ( ! is_array( $domain_scores ) ) {
			$domain_scores = array();
		}
		if ( ! is_array( $recommendations ) ) {
			$recommendations = array();
		}

		$risk_level      = sanitize_key( (string) ( $row->risk_level ?? '' ) );
		$alignment_score = (int) ( $row->alignment_score ?? 0 );

		return array(
			'alignment_score'        => $alignment_score,
			'readiness_level_label'  => AIRB_Scoring::readiness_band_label( $alignment_score ),
			'risk_level'             => $risk_level,
			'risk_level_label'       => AIRB_Scoring::display_risk_label( $risk_level, (float) ( 100 - $alignment_score ) ),
			'dependency_index'       => (int) ( $row->dependency_index ?? 0 ),
			'human_oversight_label'  => sanitize_text_field( (string) ( $row->human_oversight_label ?? '' ) ),
			'privacy_risk'           => (int) ( $row->privacy_risk ?? 0 ),
			'safeguarding_readiness' => (int) ( $row->safeguarding_readiness ?? 0 ),
			'governance_maturity'    => (int) ( $row->governance_maturity ?? 0 ),
			'domain_scores'          => $domain_scores,
			'recommendations'        => $recommendations,
		);
	}

	/**
	 * Submit benchmark results.
	 */
	public static function submit(): void {
		self::verify_nonce();
		self::enforce_rate_limit( 'submit', 10, 10 * MINUTE_IN_SECONDS );

		$role    = sanitize_key( (string) ( $_POST['role'] ?? '' ) );
		$answers = isset( $_POST['answers'] ) ? json_decode( wp_unslash( (string) $_POST['answers'] ), true ) : array();
		$session_id = sanitize_text_field( substr( (string) ( $_POST['session_id'] ?? '' ), 0, 64 ) );

		if ( ! $role || ! is_array( $answers ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid submission.', 'ai-risk-benchmark' ) ) );
		}
		if ( ! self::is_valid_role( $role ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid benchmark role.', 'ai-risk-benchmark' ) ), 400 );
		}

		$school  = sanitize_text_field( (string) ( $_POST['school_name'] ?? '' ) );
		$email   = sanitize_email( (string) ( $_POST['email'] ?? '' ) );
		$consent = ! empty( $_POST['consent'] ) || ! empty( $_POST['privacy_consent'] );
		$contact_opt_in = ! empty( $_POST['contact_opt_in'] ) || ! empty( $_POST['email_opt_in'] ) || ( '' !== $email );
		if ( '' !== $email && ! $consent ) {
			wp_send_json_error( array( 'message' => __( 'Consent is required before storing an email address.', 'ai-risk-benchmark' ) ), 400 );
		}
		$profile = array(
			'school_phase' => sanitize_key( (string) ( $_POST['school_phase'] ?? '' ) ),
			'org_type'     => sanitize_key( (string) ( $_POST['org_type'] ?? '' ) ),
			'year_group'   => sanitize_key( (string) ( $_POST['year_group'] ?? '' ) ),
		);

		if ( $profile['school_phase'] ) {
			$answers['_school_phase'] = $profile['school_phase'];
		}
		if ( $profile['org_type'] ) {
			$answers['_org_type'] = $profile['org_type'];
		}
		if ( $profile['year_group'] ) {
			$answers['_year_group'] = $profile['year_group'];
		}

		$config  = AIRB_Config::get();
		$results = AIRB_Scoring::calculate( $role, $answers, $config );
		$answered_total = 0;
		foreach ( (array) ( $results['domain_scores'] ?? array() ) as $domain_score ) {
			$answered_total += (int) ( is_array( $domain_score ) ? ( $domain_score['questions_answered'] ?? 0 ) : 0 );
		}
		if ( $answered_total < 1 ) {
			wp_send_json_error( array( 'message' => __( 'Please answer the benchmark questions before submitting.', 'ai-risk-benchmark' ) ), 400 );
		}
		$results = AIRB_Funnel::enrich( $results, $role, $profile, $config, $school, $answers );
		$results['gateway'] = AIRB_Pathway::build_gateway( $role, $config, $school );

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
		if ( $profile['year_group'] ) {
			$answers['_year_group'] = $profile['year_group'];
		}

		$id = AIRB_Database::insert(
			array(
				'session_id'             => $session_id,
				'role'                   => $role,
				'school_name'            => $school,
				'email'                  => $email,
				'consent'                => $consent ? 1 : 0,
				'contact_opt_in'         => $contact_opt_in ? 1 : 0,
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

		if ( $id && $session_id ) {
			AIRB_Events::insert(
				array(
					'session_id'    => $session_id,
					'submission_id' => $id,
					'event_type'    => 'benchmark_completed',
					'role'          => $role,
					'metadata'      => array(
						'alignment_score' => (int) ( $results['alignment_score'] ?? 0 ),
					),
				)
			);
		}

		if ( $school && ! empty( $results['leader_results'] ) ) {
			$rollout = AIRB_Leader_Results::school_rollout_counts( $school );
			$results['leader_results']['school_rollout'] = $rollout;
			if ( ! empty( $results['leader_results']['next_steps']['cards'] ) ) {
				foreach ( $results['leader_results']['next_steps']['cards'] as &$card ) {
					if ( ! is_array( $card ) || 'whole_school_benchmark' !== ( $card['key'] ?? '' ) ) {
						continue;
					}
					$card['counts']    = (array) ( $rollout['counts'] ?? array() );
					$card['unlocked']  = ! empty( $rollout['is_unlocked'] );
					$card['remaining'] = (int) ( $rollout['remaining'] ?? 0 );
					$card['threshold'] = (int) ( $rollout['threshold'] ?? 20 );
				}
				unset( $card );
			}
		}

		// Attach national benchmark comparison (null until the privacy-floor
		// sample size is reached for this role).
		$results['benchmark'] = AIRB_Database::get_benchmark_stats(
			$role,
			isset( $results['alignment_score'] ) ? (int) $results['alignment_score'] : null
		);

		// Enquiry CTAs scroll to the in-results interest form instead of mailto / generic contact.
		$results = self::apply_interest_ctas( $results, $role );

		$form = AIRB_Interest::build_form_payload( $results, $role );
		if ( $form ) {
			$results['interest_form'] = $form;
		}

		$read_seed = $id ? (int) $id : (int) crc32( $session_id . '|' . $role );
		AIRB_Defaults::patch_results_timeline_read_links( $results, $role, $read_seed );
		if ( $id ) {
			$results['certificate'] = AIRB_Certificates::status_for_submission( $id, $results, $role, $school );
		}

		wp_send_json_success(
			array(
				'submission_id' => $id,
				'results'       => $results,
			)
		);
	}

	/**
	 * Validate certificate evidence before unlock.
	 */
	public static function validate_certificate_evidence(): void {
		self::verify_nonce();

		$role            = sanitize_key( (string) ( $_POST['role'] ?? '' ) );
		$theme           = sanitize_key( (string) ( $_POST['evidence_theme'] ?? '' ) );
		$action          = sanitize_textarea_field( (string) ( $_POST['evidence_action'] ?? '' ) );
		$change          = sanitize_textarea_field( (string) ( $_POST['evidence_change'] ?? '' ) );
		$link            = esc_url_raw( (string) ( $_POST['evidence_link'] ?? '' ) );
		$benchmark_score = max( 0, min( 100, (int) ( $_POST['benchmark_score'] ?? 0 ) ) );

		if ( ! self::is_valid_role( $role ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid benchmark role.', 'ai-risk-benchmark' ) ), 400 );
		}

		wp_send_json_success(
			AIRB_Certificate_Evidence::assess( $role, $theme, $action, $change, $link, $benchmark_score )
		);
	}

	/**
	 * Allocate a named certificate after evidenced progress has been confirmed.
	 */
	public static function allocate_certificate(): void {
		self::verify_nonce();

		$submission_id = max( 0, (int) ( $_POST['submission_id'] ?? 0 ) );
		$session_id    = sanitize_text_field( substr( (string) ( $_POST['session_id'] ?? '' ), 0, 64 ) );
		$role          = sanitize_key( (string) ( $_POST['role'] ?? '' ) );
		$name          = sanitize_text_field( (string) ( $_POST['participant_name'] ?? '' ) );
		$school_name   = sanitize_text_field( (string) ( $_POST['school_name'] ?? '' ) );
		$theme         = sanitize_key( (string) ( $_POST['evidence_theme'] ?? '' ) );
		$action        = sanitize_textarea_field( (string) ( $_POST['evidence_action'] ?? '' ) );
		$change        = sanitize_textarea_field( (string) ( $_POST['evidence_change'] ?? '' ) );
		$link          = esc_url_raw( (string) ( $_POST['evidence_link'] ?? '' ) );

		if ( ! $submission_id ) {
			wp_send_json_error( array( 'message' => __( 'A saved benchmark submission is required before a certificate can be allocated.', 'ai-risk-benchmark' ) ), 400 );
		}
		if ( $role && ! self::is_valid_role( $role ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid benchmark role.', 'ai-risk-benchmark' ) ), 400 );
		}
		if ( strlen( $name ) < 2 ) {
			wp_send_json_error( array( 'message' => __( 'Please enter the name to show on the certificate.', 'ai-risk-benchmark' ) ), 400 );
		}

		$submission = AIRB_Database::get_submission( $submission_id );
		if ( ! $submission ) {
			wp_send_json_error( array( 'message' => __( 'Benchmark submission not found.', 'ai-risk-benchmark' ) ), 404 );
		}
		if ( '' === $session_id || ! hash_equals( (string) $submission->session_id, $session_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Certificate allocation must be completed from the same benchmark session.', 'ai-risk-benchmark' ) ), 403 );
		}

		$existing_cert = AIRB_Certificates::get_by_submission( $submission_id );
		if ( $existing_cert && in_array( (string) $existing_cert->status, array( 'unlocked', 'pending_review' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Certificate already submitted for this benchmark.', 'ai-risk-benchmark' ) ), 400 );
		}

		$contact_email    = sanitize_email( (string) ( $_POST['contact_email'] ?? '' ) );
		$submission_email = sanitize_email( (string) ( $submission->email ?? '' ) );

		$stored_role = sanitize_key( (string) $submission->role );
		$role        = AIRB_Certificate_Copy::normalize_role( $stored_role ?: $role );
		$posted_role = AIRB_Certificate_Copy::normalize_role( sanitize_key( (string) ( $_POST['role'] ?? '' ) ) );
		if ( $posted_role && $role && $posted_role !== $role ) {
			wp_send_json_error( array( 'message' => __( 'Certificate role does not match the saved benchmark.', 'ai-risk-benchmark' ) ), 400 );
		}

		if ( '' === $school_name ) {
			$school_name = sanitize_text_field( (string) $submission->school_name );
		}

		$score = max( 0, min( 100, (int) $submission->alignment_score ) );

		$assessment = AIRB_Certificate_Evidence::assess( $role, $theme, $action, $change, $link, $score );
		if ( empty( $assessment['can_unlock'] ) ) {
			wp_send_json_error(
				array(
					'message'    => __( 'Add more specific evidence before unlocking the certificate.', 'ai-risk-benchmark' ),
					'assessment' => $assessment,
				),
				400
			);
		}

		$requires_review = ! empty( $assessment['manual_review'] );
		$notify_email    = $submission_email ?: $contact_email;
		$roles_need_contact = in_array( $role, array( 'student', 'parent' ), true );

		if ( $requires_review && ! $notify_email ) {
			wp_send_json_error(
				array(
					'message'    => __( 'Add an email address so we can tell you when your certificate is approved.', 'ai-risk-benchmark' ),
					'assessment' => $assessment,
				),
				400
			);
		}
		if ( $roles_need_contact && ! $notify_email ) {
			wp_send_json_error(
				array(
					'message'    => __( 'Add an email address (yours or a parent/teacher contact) so we can send your certificate.', 'ai-risk-benchmark' ),
					'assessment' => $assessment,
				),
				400
			);
		}

		if ( $contact_email && ! $submission_email ) {
			global $wpdb;
			$wpdb->update(
				AIRB_Database::table_name(),
				array( 'email' => $contact_email ),
				array( 'id' => $submission_id ),
				array( '%s' ),
				array( '%d' )
			);
		}

		$cert_status = $requires_review ? 'pending_review' : 'unlocked';

		$row = AIRB_Certificates::allocate(
			array(
				'submission_id'          => $submission_id,
				'session_id'             => (string) $submission->session_id,
				'role'                   => $role,
				'participant_name'       => $name,
				'school_name'            => $school_name,
				'baseline_score'         => $score,
				'completed_score'        => $score,
				'unlock_at'              => AIRB_Certificate_Evidence::SCORE_THRESHOLD,
				'unlock_reason'          => $requires_review ? 'pending_manual_review' : 'evidenced_progress',
				'evidence_theme'         => $theme,
				'evidence_action'        => $action,
				'evidence_change'        => $change,
				'evidence_link'          => $link,
				'evidence_quality_score' => (int) ( $assessment['quality_score'] ?? 0 ),
				'evidence_quality_tier'  => (string) ( $assessment['quality_tier'] ?? '' ),
				'status'                 => $cert_status,
			)
		);

		if ( ! $row ) {
			wp_send_json_error( array( 'message' => __( 'Could not allocate the certificate. Please try again.', 'ai-risk-benchmark' ) ), 500 );
		}

		if ( $requires_review ) {
			AIRB_Certificates::notify_pending_review( $row, $submission, $notify_email );
		}

		AIRB_Events::insert(
			array(
				'session_id'    => (string) $submission->session_id,
				'submission_id' => $submission_id,
				'event_type'    => 'certificate_allocated',
				'role'          => $role,
				'metadata'      => array(
					'certificate_id'         => (string) $row->certificate_id,
					'evidence_theme'         => $theme,
					'evidence_quality_score' => (int) ( $assessment['quality_score'] ?? 0 ),
					'evidence_quality_tier'  => (string) ( $assessment['quality_tier'] ?? '' ),
				),
			)
		);

		$role_label = self::certificate_role_label( $role );
		$copy       = AIRB_Certificate_Copy::for_role( $role );

		wp_send_json_success(
			array(
				'certificate' => array(
					'certificate_id'         => (string) $row->certificate_id,
					'verification_hash'      => (string) $row->verification_hash,
					'participant_name'       => (string) $row->participant_name,
					'school_name'            => (string) $row->school_name,
					'role'                   => $role,
					'role_label'             => $role_label,
					'current_score'          => $score,
					'unlock_at'              => AIRB_Certificate_Evidence::SCORE_THRESHOLD,
					'awarded_at'             => (string) $row->awarded_at,
					'verify_url'             => home_url( '/' ),
					'evidence_theme'         => $theme,
					'evidence_quality_score' => (int) ( $assessment['quality_score'] ?? 0 ),
					'evidence_quality_tier'  => (string) ( $assessment['quality_tier'] ?? '' ),
					'status'                 => (string) $row->status,
					'pending_review'         => 'pending_review' === (string) $row->status,
					'unlocked'               => 'unlocked' === (string) $row->status,
					'assessment'             => $assessment,
					'copy'                   => array(
						'headline_primary'   => $copy['headline_primary'],
						'headline_secondary' => $copy['headline_secondary'] ?? '',
						'body'               => $copy['body'],
					),
				),
			)
		);
	}

	/**
	 * Look up an already-allocated certificate by its public verification
	 * hash, so a participant can retrieve it later without depending on
	 * browser storage or the original session (e.g. after a manual review
	 * is approved, possibly on a different device or after the local
	 * results snapshot has expired).
	 */
	public static function lookup_certificate(): void {
		self::verify_nonce();
		self::enforce_rate_limit( 'cert_lookup', 20, 10 * MINUTE_IN_SECONDS );

		$hash = sanitize_text_field( substr( (string) ( $_POST['verification_hash'] ?? '' ), 0, 64 ) );
		if ( '' === $hash ) {
			wp_send_json_error( array( 'message' => __( 'Certificate link is missing or invalid.', 'ai-risk-benchmark' ) ), 400 );
		}

		$row = AIRB_Certificates::get_by_verification_hash( $hash );
		if ( ! $row || ! in_array( (string) $row->status, array( 'unlocked', 'pending_review' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'We could not find a certificate for this link.', 'ai-risk-benchmark' ) ), 404 );
		}

		$role       = sanitize_key( (string) $row->role );
		$role_label = self::certificate_role_label( $role );
		$copy       = AIRB_Certificate_Copy::for_role( $role );

		wp_send_json_success(
			array(
				'certificate' => array(
					'certificate_id'         => (string) $row->certificate_id,
					'verification_hash'      => (string) $row->verification_hash,
					'participant_name'       => (string) $row->participant_name,
					'school_name'            => (string) $row->school_name,
					'role'                   => $role,
					'role_label'             => $role_label,
					'current_score'          => (int) $row->completed_score,
					'unlock_at'              => AIRB_Certificate_Evidence::SCORE_THRESHOLD,
					'awarded_at'             => (string) $row->awarded_at,
					'verify_url'             => home_url( '/' ),
					'evidence_theme'         => (string) $row->evidence_theme,
					'evidence_quality_score' => (int) $row->evidence_quality_score,
					'evidence_quality_tier'  => (string) $row->evidence_quality_tier,
					'status'                 => (string) $row->status,
					'pending_review'         => 'pending_review' === (string) $row->status,
					'unlocked'               => 'unlocked' === (string) $row->status,
					'copy'                   => array(
						'headline_primary'   => $copy['headline_primary'],
						'headline_secondary' => $copy['headline_secondary'] ?? '',
						'body'               => $copy['body'],
					),
				),
			)
		);
	}

	/**
	 * Human-readable certificate role label.
	 */
	private static function certificate_role_label( string $role ): string {
		$labels = array(
			'teacher'       => __( 'Teacher', 'ai-risk-benchmark' ),
			'student'       => __( 'Student', 'ai-risk-benchmark' ),
			'parent'        => __( 'Parent', 'ai-risk-benchmark' ),
			'leader'        => __( 'School Leader', 'ai-risk-benchmark' ),
			'support_staff' => __( 'Support Staff', 'ai-risk-benchmark' ),
			'support'       => __( 'Support Staff', 'ai-risk-benchmark' ),
			'public'        => __( 'Public AI Safety', 'ai-risk-benchmark' ),
		);

		return $labels[ $role ] ?? __( 'AI Awareness', 'ai-risk-benchmark' );
	}

	/**
	 * Whether a CTA should open the in-results interest form.
	 *
	 * @param string $url Configured CTA URL.
	 */
	private static function cta_should_use_interest_form( string $url ): bool {
		$url = trim( $url );
		if ( '' === $url || str_starts_with( $url, 'mailto:' ) ) {
			return true;
		}
		if ( str_starts_with( $url, '#airb-interest' ) ) {
			return true;
		}
		if ( str_contains( $url, '#contact' ) || str_contains( $url, '/contact' ) ) {
			return true;
		}

		$parsed = wp_parse_url( $url );
		if ( ! is_array( $parsed ) || empty( $parsed['host'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Infer interest checkbox from offer title when no card key is available.
	 */
	private static function infer_interest_prefill( string $title ): string {
		$title = strtolower( wp_strip_all_tags( $title ) );
		if ( str_contains( $title, 'awareness day' ) ) {
			return 'ai_awareness_day';
		}
		if ( str_contains( $title, 'policy' ) ) {
			return 'policy_support';
		}
		if ( str_contains( $title, 'cpd' ) || str_contains( $title, 'training' ) ) {
			return 'whole_school_cpd';
		}
		if ( str_contains( $title, 'governance' ) || str_contains( $title, 'consultation' ) || str_contains( $title, 'review' ) ) {
			return 'governance_review';
		}
		if ( str_contains( $title, 'parent' ) || str_contains( $title, 'webinar' ) ) {
			return 'parent_sessions';
		}
		if ( str_contains( $title, 'child' ) || str_contains( $title, 'carer' ) ) {
			return 'parent_support_child';
		}
		if ( str_contains( $title, 'student' ) || str_contains( $title, 'pupil' ) ) {
			return 'student_share_school';
		}
		if ( str_contains( $title, 'whole-school' ) || str_contains( $title, 'whole school' ) || str_contains( $title, 'benchmark' ) ) {
			return 'whole_school_benchmark';
		}
		return 'further_information';
	}

	/**
	 * Resolve enquiry CTA to interest form anchor.
	 *
	 * @param string $title    Offer title.
	 * @param string $existing Configured CTA URL.
	 * @param string $key      Optional card/offer key.
	 */
	private static function resolve_interest_cta( string $title, string $existing, string $key = '' ): string {
		if ( ! self::cta_should_use_interest_form( $existing ) ) {
			return $existing;
		}
		$map     = AIRB_Interest::gateway_prefill_map();
		$prefill = $map[ $key ] ?? self::infer_interest_prefill( $title );
		return AIRB_Interest::form_anchor( $prefill );
	}

	/**
	 * Rewrite enquiry CTAs to the in-results interest form; preserve real destination URLs.
	 *
	 * @param array<string, mixed> $results Results payload.
	 * @param string               $role    Respondent role.
	 * @return array<string, mixed>
	 */
	private static function apply_interest_ctas( array $results, string $role ): array {
		unset( $role );
		foreach ( array( 'recommendations', 'next_steps' ) as $list_key ) {
			if ( ! empty( $results[ $list_key ] ) && is_array( $results[ $list_key ] ) ) {
				foreach ( $results[ $list_key ] as &$item ) {
					if ( is_array( $item ) ) {
						$item['cta_url'] = self::resolve_interest_cta(
							(string) ( $item['title'] ?? '' ),
							(string) ( $item['cta_url'] ?? '' ),
							(string) ( $item['key'] ?? '' )
						);
					}
				}
				unset( $item );
			}
		}

		if ( ! empty( $results['gateway']['cards'] ) && is_array( $results['gateway']['cards'] ) ) {
			foreach ( $results['gateway']['cards'] as &$card ) {
				if ( ! is_array( $card ) ) {
					continue;
				}
				$key = (string) ( $card['key'] ?? '' );
				if ( 'track_progress' === $key ) {
					continue;
				}
				$card['cta_url'] = self::resolve_interest_cta(
					(string) ( $card['title'] ?? '' ),
					(string) ( $card['cta_url'] ?? '' ),
					$key
				);
			}
			unset( $card );
		}

		if ( ! empty( $results['consultation_pitch'] ) && is_array( $results['consultation_pitch'] ) ) {
			$title = (string) ( $results['consultation_pitch']['headline'] ?? __( 'Free consultation', 'ai-risk-benchmark' ) );
			$results['consultation_pitch']['cta_url'] = self::resolve_interest_cta(
				$title,
				(string) ( $results['consultation_pitch']['cta_url'] ?? '' ),
				'book_consultation'
			);
		}
		if ( ! empty( $results['policy_support'] ) && is_array( $results['policy_support'] ) ) {
			$results['policy_support']['cta_url'] = self::resolve_interest_cta(
				(string) ( $results['policy_support']['title'] ?? __( 'Develop your AI policy', 'ai-risk-benchmark' ) ),
				(string) ( $results['policy_support']['cta_url'] ?? '' ),
				'policy_support'
			);
		}

		if ( ! empty( $results['leader_results']['next_steps']['hero'] ) && is_array( $results['leader_results']['next_steps']['hero'] ) ) {
			$hero = &$results['leader_results']['next_steps']['hero'];
			if ( ! empty( $hero['cta_url'] ) ) {
				$hero['cta_url'] = self::resolve_interest_cta(
					(string) ( $hero['title'] ?? '' ),
					(string) ( $hero['cta_url'] ?? '' ),
					(string) ( $hero['key'] ?? 'governance_review' )
				);
			}
			unset( $hero );
		}

		if ( ! empty( $results['leader_results']['next_steps']['cards'] ) && is_array( $results['leader_results']['next_steps']['cards'] ) ) {
			foreach ( $results['leader_results']['next_steps']['cards'] as &$card ) {
				if ( ! is_array( $card ) || empty( $card['cta_url'] ) ) {
					continue;
				}
				$card['cta_url'] = self::resolve_interest_cta(
					(string) ( $card['title'] ?? '' ),
					(string) ( $card['cta_url'] ?? '' ),
					(string) ( $card['key'] ?? '' )
				);
			}
			unset( $card );
		}

		if ( ! empty( $results['guided_improvement']['consultation']['cta_url'] ) ) {
			$results['guided_improvement']['consultation']['cta_url'] = self::resolve_interest_cta(
				(string) ( $results['guided_improvement']['consultation']['title'] ?? __( 'AI Readiness Review', 'ai-risk-benchmark' ) ),
				(string) ( $results['guided_improvement']['consultation']['cta_url'] ?? '' ),
				'book_consultation'
			);
		}

		return $results;
	}

	/**
	 * Submit post-benchmark interest form.
	 */
	public static function submit_interest(): void {
		self::verify_nonce();

		$role           = sanitize_key( (string) ( $_POST['role'] ?? '' ) );
		$name           = sanitize_text_field( (string) ( $_POST['name'] ?? '' ) );
		$email          = sanitize_email( (string) ( $_POST['email'] ?? '' ) );
		$school         = sanitize_text_field( (string) ( $_POST['school'] ?? '' ) );
		$child_school   = sanitize_text_field( (string) ( $_POST['child_school'] ?? '' ) );
		$message        = sanitize_textarea_field( (string) ( $_POST['message'] ?? '' ) );
		$submission     = max( 0, (int) ( $_POST['submission_id'] ?? 0 ) );
		$session_id     = sanitize_text_field( substr( (string) ( $_POST['session_id'] ?? '' ), 0, 64 ) );
		$score          = max( 0, min( 100, (int) ( $_POST['alignment_score'] ?? 0 ) ) );
		$risk_level     = sanitize_key( (string) ( $_POST['risk_level'] ?? '' ) );
		$risk_label     = sanitize_text_field( (string) ( $_POST['risk_level_label'] ?? '' ) );
		$readiness_label = sanitize_text_field( (string) ( $_POST['readiness_level_label'] ?? '' ) );
		$year_group      = sanitize_key( (string) ( $_POST['year_group'] ?? '' ) );
		$stakeholder_raw  = sanitize_text_field( (string) ( $_POST['stakeholder_role'] ?? '' ) );
		$stakeholder_role = substr( $stakeholder_raw, 0, 40 );
		if ( $stakeholder_role ) {
			$stakeholder_opts = AIRB_Interest::stakeholder_role_options();
			$key = sanitize_key( $stakeholder_role );
			if ( isset( $stakeholder_opts[ $key ] ) ) {
				$stakeholder_role = $key;
			} else {
				foreach ( $stakeholder_opts as $opt_key => $label ) {
					if ( 0 === strcasecmp( (string) $label, $stakeholder_role ) ) {
						$stakeholder_role = (string) $opt_key;
						break;
					}
				}
			}
		}
		$interests      = isset( $_POST['interests'] ) ? json_decode( wp_unslash( (string) $_POST['interests'] ), true ) : array();
		$weak_domains   = isset( $_POST['weak_domains'] ) ? json_decode( wp_unslash( (string) $_POST['weak_domains'] ), true ) : array();

		if ( ! in_array( $role, AIRB_Interest::supported_roles(), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid role.', 'ai-risk-benchmark' ) ) );
		}

		$parent_tier = '';
		if ( 'parent' === $role ) {
			$parent_score = max( 0, min( 100, (int) ( $_POST['alignment_score'] ?? 0 ) ) );
			$parent_tier  = AIRB_Parent_Results::journey_tier( $parent_score );
		}

		$fields = AIRB_Interest::fields_for_role( $role );
		if ( ! empty( $fields['email_required'] ) && ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'ai-risk-benchmark' ) ) );
		}
		if ( ! is_array( $interests ) || ! $interests ) {
			wp_send_json_error( array( 'message' => __( 'Please select at least one option.', 'ai-risk-benchmark' ) ) );
		}

		$allowed   = array_column( AIRB_Interest::options_for_role( $role, $parent_tier ), 'slug' );
		$interests = array_values( array_intersect( array_map( 'sanitize_key', $interests ), $allowed ) );
		if ( ! $interests ) {
			wp_send_json_error( array( 'message' => __( 'Please select at least one option.', 'ai-risk-benchmark' ) ) );
		}

		$fingerprint = md5(
			( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' )
			. '|'
			. ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '' )
		);
		$rate_key = 'airb_interest_rate_' . $fingerprint;
		$count    = (int) get_transient( $rate_key );
		if ( $count >= 5 ) {
			wp_send_json_error(
				array( 'message' => __( 'Too many requests. Please try again in a few minutes.', 'ai-risk-benchmark' ) ),
				429
			);
		}

		$payload = array(
			'role'                  => $role,
			'name'                  => $name,
			'email'                 => $email,
			'school'                => $school,
			'child_school'          => $child_school,
			'message'               => $message,
			'submission_id'         => $submission,
			'alignment_score'       => $score,
			'risk_level'            => $risk_level,
			'risk_level_label'      => $risk_label,
			'readiness_level_label' => $readiness_label,
			'year_group'            => $year_group,
			'stakeholder_role'      => $stakeholder_role,
			'interests'             => $interests,
			'weak_domains'          => is_array( $weak_domains ) ? array_map( 'sanitize_text_field', $weak_domains ) : array(),
			'source'                => sanitize_key( (string) ( $_POST['source'] ?? 'results' ) ),
			'hub_page'              => sanitize_key( (string) ( $_POST['hub_page'] ?? '' ) ),
			'hub_title'             => sanitize_text_field( (string) ( $_POST['hub_title'] ?? '' ) ),
			'hub_ref'               => sanitize_key( (string) ( $_POST['hub_ref'] ?? '' ) ),
			'hub_url'               => esc_url_raw( (string) ( $_POST['hub_url'] ?? '' ) ),
			'checklist_done'        => max( 0, (int) ( $_POST['checklist_done'] ?? 0 ) ),
			'checklist_total'       => max( 0, (int) ( $_POST['checklist_total'] ?? 0 ) ),
		);

		$sent = AIRB_Interest::send_notification( $payload );
		if ( ! $sent ) {
			wp_send_json_error( array( 'message' => __( 'Could not send your request. Please try again.', 'ai-risk-benchmark' ) ) );
		}

		$lead_id = AIRB_Leads::insert(
			array_merge(
				$payload,
				array(
					'session_id' => $session_id,
				)
			)
		);

		set_transient( $rate_key, $count + 1, 5 * MINUTE_IN_SECONDS );

		if ( $session_id ) {
			AIRB_Events::insert(
				array(
					'session_id'    => $session_id,
					'submission_id' => $submission,
					'event_type'    => 'hub' === $payload['source'] ? 'hub_interest_submitted' : 'interest_submitted',
					'role'          => $role,
					'metadata'      => array(
						'lead_id'           => $lead_id,
						'interests'         => $interests,
						'email'             => $email,
						'name'              => $name,
						'stakeholder_role'  => $stakeholder_role,
						'school'            => $school,
						'hub_page'          => $payload['hub_page'],
						'hub_ref'           => $payload['hub_ref'],
						'checklist_done'    => (int) $payload['checklist_done'],
						'checklist_total'   => (int) $payload['checklist_total'],
					),
				)
			);
		}

		wp_send_json_success(
			array(
				'message' => AIRB_Interest::form_labels( $role )['success'],
				'lead_id' => $lead_id,
			)
		);
	}

	/**
	 * Load benchmark submission context for hub interest form (session lookup).
	 */
	public static function get_hub_context(): void {
		self::verify_nonce();

		$session_id = sanitize_text_field( substr( (string) ( $_POST['session_id'] ?? '' ), 0, 64 ) );
		$role       = sanitize_key( (string) ( $_POST['role'] ?? '' ) );
		$page_slug  = sanitize_key( (string) ( $_POST['hub_page'] ?? '' ) );
		$ref        = sanitize_key( (string) ( $_POST['hub_ref'] ?? '' ) );

		if ( ! $session_id ) {
			wp_send_json_success( array( 'submission' => null ) );
		}

		if ( $role && ! self::is_valid_role( $role ) ) {
			wp_send_json_success( array( 'submission' => null ) );
		}

		$row = AIRB_Database::get_latest_submission_by_session( $session_id, $role );
		if ( ! $row && $role ) {
			$row = AIRB_Database::get_latest_submission_by_session( $session_id, '' );
		}

		if ( ! $row ) {
			wp_send_json_success( array( 'submission' => null ) );
		}

		$results = AIRB_Hub_Interest::results_from_submission( $row );
		$sub_role = sanitize_key( (string) ( $row->role ?? '' ) );

		if ( 'parent' === $sub_role ) {
			$answers = json_decode( (string) ( $row->answers ?? '{}' ), true );
			if ( is_array( $answers ) ) {
				$results['parent_display_domains'] = AIRB_Scoring::parent_display_domain_scores( $answers, AIRB_Config::get() );
			}
		}

		$page_title = '';
		if ( $page_slug && function_exists( 'get_page_by_path' ) ) {
			$page = get_page_by_path( $page_slug, OBJECT, 'page' );
			if ( $page instanceof WP_Post ) {
				$page_title = get_the_title( $page );
			}
		}

		$journey_context = AIRB_Hub_Journey::context_from_results( $page_slug, $sub_role, $results, $page_title );

		$weak = AIRB_Interest::weak_domain_labels( $results, $sub_role );
		$suggested = AIRB_Hub_Interest::suggested_for_hub( $page_slug, $ref, $sub_role );
		$merged = array_values( array_unique( array_merge(
			$suggested,
			AIRB_Interest::suggested_from_results( $results, $sub_role )
		) ) );
		$allowed = array_column( AIRB_Interest::options_for_role( $sub_role ), 'slug' );
		$merged  = array_values( array_intersect( $merged, $allowed ) );

		wp_send_json_success(
			array(
				'submission' => array(
					'id'              => (int) $row->id,
					'role'            => $sub_role,
					'alignment_score' => (int) ( $row->alignment_score ?? 0 ),
					'risk_level'      => (string) ( $row->risk_level ?? '' ),
					'school'          => sanitize_text_field( (string) ( $row->school_name ?? '' ) ),
					'weak_domains'    => $weak,
					'suggested'       => $merged,
					'journey_context' => $journey_context,
				),
			)
		);
	}

	/**
	 * Email printable report.
	 */
	public static function email_report(): void {
		self::verify_nonce();

		$email         = sanitize_email( (string) ( $_POST['email'] ?? '' ) );
		$role          = sanitize_key( (string) ( $_POST['role'] ?? '' ) );
		$session_id    = sanitize_text_field( substr( (string) ( $_POST['session_id'] ?? '' ), 0, 64 ) );
		$submission_id = max( 0, (int) ( $_POST['submission_id'] ?? 0 ) );

		if ( ! is_email( $email ) || ! $submission_id || ! $session_id ) {
			wp_send_json_error( array( 'message' => __( 'A saved benchmark session and valid email are required.', 'ai-risk-benchmark' ) ), 400 );
		}
		if ( $role && ! self::is_valid_role( $role ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid benchmark role.', 'ai-risk-benchmark' ) ), 400 );
		}

		$submission = AIRB_Database::get_submission( $submission_id );
		if ( ! $submission || ! hash_equals( (string) $submission->session_id, $session_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Report can only be sent from the saved benchmark session.', 'ai-risk-benchmark' ) ), 403 );
		}

		$stored_role = sanitize_key( (string) ( $submission->role ?? '' ) );
		if ( $role && $stored_role && $role !== $stored_role ) {
			wp_send_json_error( array( 'message' => __( 'Report role does not match the saved benchmark.', 'ai-risk-benchmark' ) ), 400 );
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

		$role    = $stored_role ?: $role;
		$results = self::results_from_submission( $submission );
		$config  = AIRB_Config::get();
		$body    = AIRB_Report::build_email_html( $role, $results, $config );
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

	/**
	 * Record a front-end funnel event (anonymous session).
	 */
	public static function track_event(): void {
		self::verify_nonce();
		self::enforce_rate_limit( 'event', 120, MINUTE_IN_SECONDS );

		$event_type = sanitize_key( (string) ( $_POST['event_type'] ?? '' ) );
		$session_id = sanitize_text_field( substr( (string) ( $_POST['session_id'] ?? '' ), 0, 64 ) );
		$role       = sanitize_key( (string) ( $_POST['role'] ?? '' ) );
		$submission = max( 0, (int) ( $_POST['submission_id'] ?? 0 ) );
		$metadata   = isset( $_POST['metadata'] ) ? json_decode( wp_unslash( (string) $_POST['metadata'] ), true ) : array();

		if ( ! $event_type || ! $session_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid event.', 'ai-risk-benchmark' ) ) );
		}

		if ( ! is_array( $metadata ) ) {
			$metadata = array();
		}

		$id = AIRB_Events::insert(
			array(
				'session_id'    => $session_id,
				'submission_id' => $submission,
				'event_type'    => $event_type,
				'role'          => $role,
				'metadata'      => $metadata,
			)
		);

		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Could not record event.', 'ai-risk-benchmark' ) ) );
		}

		wp_send_json_success( array( 'event_id' => $id ) );
	}
}
