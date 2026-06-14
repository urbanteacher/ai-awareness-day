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
	 * Submit benchmark results.
	 */
	public static function submit(): void {
		self::verify_nonce();

		$role    = sanitize_key( (string) ( $_POST['role'] ?? '' ) );
		$answers = isset( $_POST['answers'] ) ? json_decode( wp_unslash( (string) $_POST['answers'] ), true ) : array();
		$session_id = sanitize_text_field( substr( (string) ( $_POST['session_id'] ?? '' ), 0, 64 ) );

		if ( ! $role || ! is_array( $answers ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid submission.', 'ai-risk-benchmark' ) ) );
		}

		$school  = sanitize_text_field( (string) ( $_POST['school_name'] ?? '' ) );
		$email   = sanitize_email( (string) ( $_POST['email'] ?? '' ) );
		$profile = array(
			'school_phase' => sanitize_key( (string) ( $_POST['school_phase'] ?? '' ) ),
			'org_type'     => sanitize_key( (string) ( $_POST['org_type'] ?? '' ) ),
			'year_group'   => sanitize_key( (string) ( $_POST['year_group'] ?? '' ) ),
		);

		$config  = AIRB_Config::get();
		$results = AIRB_Scoring::calculate( $role, $answers, $config );
		$results = AIRB_Funnel::enrich( $results, $role, $profile, $config, $school );
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
				'consent'                => ! empty( $_POST['consent'] ) ? 1 : 0,
				'contact_opt_in'         => ! empty( $_POST['contact_opt_in'] ) ? 1 : 0,
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

		wp_send_json_success(
			array(
				'submission_id' => $id,
				'results'       => $results,
			)
		);
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
		$stakeholder_role = sanitize_key( (string) ( $_POST['stakeholder_role'] ?? '' ) );
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
					'email'           => sanitize_email( (string) ( $row->email ?? '' ) ),
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

	/**
	 * Record a front-end funnel event (anonymous session).
	 */
	public static function track_event(): void {
		self::verify_nonce();

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
