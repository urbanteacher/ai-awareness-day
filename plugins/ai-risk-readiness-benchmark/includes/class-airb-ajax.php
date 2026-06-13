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
			'year_group'   => sanitize_key( (string) ( $_POST['year_group'] ?? '' ) ),
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
		if ( $profile['year_group'] ) {
			$answers['_year_group'] = $profile['year_group'];
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

		// Enquiry-only CTAs become mailto; real destination pages (homepage, resources) stay as links.
		$results = self::apply_email_ctas( $results, $role );

		wp_send_json_success(
			array(
				'submission_id' => $id,
				'results'       => $results,
			)
		);
	}

	/**
	 * Contact address that results CTAs email. Honours a theme-set contact email
	 * (Customizer `aiad_contact_email`); otherwise uses the campaign inbox.
	 */
	private static function contact_email(): string {
		$email = '';
		if ( function_exists( 'get_theme_mod' ) ) {
			$email = sanitize_email( (string) get_theme_mod( 'aiad_contact_email', '' ) );
		}
		return $email ? $email : 'info@aiawarenessday.co.uk';
	}

	/**
	 * Build a mailto: link for a given offer, with a per-offer subject and a
	 * short prefilled body.
	 *
	 * @param string $title Offer/recommendation title (becomes the subject).
	 * @param string $role  Respondent role, for context in the body.
	 */
	private static function mailto( string $title, string $role ): string {
		$title = trim( wp_strip_all_tags( $title ) );
		if ( '' === $title ) {
			$title = __( 'AI Risk & Readiness Benchmark enquiry', 'ai-risk-benchmark' );
		}
		$role_labels = AIRB_Defaults::roles();
		$role_label  = $role_labels[ $role ] ?? $role;

		$subject = sprintf( /* translators: %s: offer name */ __( 'Benchmark follow-up: %s', 'ai-risk-benchmark' ), $title );
		$body    = sprintf(
			/* translators: 1: offer name, 2: role label */
			__( "Hello,\n\nFollowing our AI Risk & Readiness Benchmark, we'd like to know more about \"%1\$s\".\n\nRole: %2\$s\nSchool / Trust:\nName:\n\nThank you.", 'ai-risk-benchmark' ),
			$title,
			$role_label
		);

		return 'mailto:' . self::contact_email()
			. '?subject=' . rawurlencode( $subject )
			. '&body=' . rawurlencode( $body );
	}

	/**
	 * Whether a configured CTA should become a mailto enquiry instead of the URL.
	 *
	 * Keeps homepage, resources and external guidance links intact.
	 *
	 * @param string $url Configured CTA URL.
	 */
	private static function cta_should_mailto( string $url ): bool {
		$url = trim( $url );
		if ( '' === $url ) {
			return true;
		}
		if ( str_starts_with( $url, 'mailto:' ) ) {
			return false;
		}

		$parsed = wp_parse_url( $url );
		if ( ! is_array( $parsed ) || empty( $parsed['host'] ) ) {
			return true;
		}

		$host = strtolower( (string) $parsed['host'] );
		$path = '/' . trim( (string) ( $parsed['path'] ?? '' ), '/' );
		if ( '/' === $path ) {
			$path = '';
		}

		if ( ! str_contains( $host, 'aiawarenessday.co.uk' ) ) {
			return false;
		}

		if ( '' === $path || str_starts_with( $path, '/resources' ) ) {
			return false;
		}

		if ( function_exists( 'home_url' ) ) {
			$home_host = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
			if ( $home_host && $host === $home_host && ( '' === $path || str_starts_with( $path, '/resources' ) ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Resolve a CTA URL — mailto enquiry or preserved destination link.
	 *
	 * @param string $title    Offer title (mailto subject).
	 * @param string $role     Respondent role.
	 * @param string $existing Configured CTA URL.
	 */
	private static function resolve_cta_url( string $title, string $role, string $existing ): string {
		if ( ! self::cta_should_mailto( $existing ) ) {
			return $existing;
		}
		return self::mailto( $title, $role );
	}

	/**
	 * Rewrite enquiry CTAs to pre-addressed email; preserve real destination URLs.
	 *
	 * @param array<string, mixed> $results Results payload.
	 * @param string               $role    Respondent role.
	 * @return array<string, mixed>
	 */
	private static function apply_email_ctas( array $results, string $role ): array {
		// Lists of {title, cta_url} items.
		foreach ( array( 'recommendations', 'next_steps' ) as $list_key ) {
			if ( ! empty( $results[ $list_key ] ) && is_array( $results[ $list_key ] ) ) {
				foreach ( $results[ $list_key ] as &$item ) {
					if ( is_array( $item ) ) {
						$item['cta_url'] = self::resolve_cta_url(
							(string) ( $item['title'] ?? '' ),
							$role,
							(string) ( $item['cta_url'] ?? '' )
						);
					}
				}
				unset( $item );
			}
		}

		// Gateway cards — enquiry routes to email; campaign pages stay linked.
		if ( ! empty( $results['gateway']['cards'] ) && is_array( $results['gateway']['cards'] ) ) {
			foreach ( $results['gateway']['cards'] as &$card ) {
				if ( is_array( $card ) ) {
					$card['cta_url'] = self::resolve_cta_url(
						(string) ( $card['title'] ?? '' ),
						$role,
						(string) ( $card['cta_url'] ?? '' )
					);
				}
			}
			unset( $card );
		}

		// Single-offer blocks.
		if ( ! empty( $results['consultation_pitch'] ) && is_array( $results['consultation_pitch'] ) ) {
			$title = (string) ( $results['consultation_pitch']['headline'] ?? __( 'Free consultation', 'ai-risk-benchmark' ) );
			$results['consultation_pitch']['cta_url'] = self::resolve_cta_url(
				$title,
				$role,
				(string) ( $results['consultation_pitch']['cta_url'] ?? '' )
			);
		}
		if ( ! empty( $results['policy_generator'] ) && is_array( $results['policy_generator'] ) ) {
			$results['policy_generator']['cta_url'] = self::resolve_cta_url(
				(string) ( $results['policy_generator']['title'] ?? __( 'AI Policy Generator', 'ai-risk-benchmark' ) ),
				$role,
				(string) ( $results['policy_generator']['cta_url'] ?? '' )
			);
		}

		return $results;
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
