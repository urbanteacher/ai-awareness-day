<?php
/**
 * Commercial funnel — Stages 1–4 enrichment on benchmark results.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Turns raw scores into acquisition → intervention funnel payloads.
 */
class AIRB_Funnel {

	/**
	 * Stage 2 domain → product mapping (automated recommendations).
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function stage2_products(): array {
		return array(
			'governance'           => array(
				'product' => __( 'AI Policy Generator', 'ai-risk-benchmark' ),
				'reason'  => __( 'Low governance score', 'ai-risk-benchmark' ),
			),
			'human_oversight'      => array(
				'product' => __( 'Teacher AI Verification Framework', 'ai-risk-benchmark' ),
				'reason'  => __( 'Low human oversight score', 'ai-risk-benchmark' ),
			),
			'privacy'              => array(
				'product' => __( 'AI Data Protection Checklist', 'ai-risk-benchmark' ),
				'reason'  => __( 'Low privacy score', 'ai-risk-benchmark' ),
			),
			'assessment_integrity' => array(
				'product' => __( 'JCQ-Aligned Assessment Review Pack', 'ai-risk-benchmark' ),
				'reason'  => __( 'Low assessment integrity score', 'ai-risk-benchmark' ),
			),
		);
	}

	/**
	 * Enrich scoring results with funnel stages 1–4 data.
	 *
	 * @param array<string, mixed> $results Full scoring results.
	 * @param string               $role    Role slug.
	 * @param array<string, string> $profile School profile (phase, org_type).
	 * @param array<string, mixed> $config  Plugin config.
	 * @return array<string, mixed>
	 */
	public static function enrich( array $results, string $role, array $profile, array $config ): array {
		$domain_scores = (array) ( $results['domain_scores'] ?? array() );

		$results['risk_heatmap']        = self::risk_heatmap( $domain_scores );
		$results['funnel_closing']      = self::stage1_closing( $results, $role );
		$results['stage2_products']     = self::matched_stage2_products( $domain_scores );
		$results['leadership_report']   = self::leadership_report( $results, $role, $profile );
		$results['consultation_pitch']  = self::consultation_pitch( $results, $role, $config );
		$results['policy_generator']    = self::policy_generator_offer( $domain_scores, $profile, $config );
		$results['aad_promo']           = self::aad_promo( $config );

		return $results;
	}

	/**
	 * Risk heat map cells for all answered domains.
	 *
	 * @param array<string, array<string, mixed>> $domain_scores Domain scores.
	 * @return array<int, array<string, mixed>>
	 */
	public static function risk_heatmap( array $domain_scores ): array {
		$cells = array();
		foreach ( AIRB_Defaults::domains() as $slug => $label ) {
			$dom = (array) ( $domain_scores[ $slug ] ?? array() );
			if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
				continue;
			}
			$cells[] = array(
				'slug'  => $slug,
				'label' => $label,
				'risk'  => (float) ( $dom['risk_percentage'] ?? 0 ),
				'band'  => (string) ( $dom['band'] ?? 'low' ),
			);
		}
		usort(
			$cells,
			static function ( $a, $b ) {
				return $b['risk'] <=> $a['risk'];
			}
		);
		return $cells;
	}

	/**
	 * Stage 1 soft close — no sales pitch.
	 *
	 * @param array<string, mixed> $results Results.
	 * @param string               $role    Role.
	 */
	public static function stage1_closing( array $results, string $role ): string {
		$areas = array();
		$map   = array(
			'governance'           => __( 'AI Policy', 'ai-risk-benchmark' ),
			'human_oversight'      => __( 'Staff Training', 'ai-risk-benchmark' ),
			'assessment_integrity' => __( 'Assessment Controls', 'ai-risk-benchmark' ),
			'ai_dependency'        => __( 'AI Dependency', 'ai-risk-benchmark' ),
			'privacy'              => __( 'Data Protection', 'ai-risk-benchmark' ),
		);
		$band_rank = array( 'low' => 0, 'moderate' => 1, 'high' => 2, 'critical' => 3 );

		foreach ( (array) ( $results['domain_scores'] ?? array() ) as $slug => $dom ) {
			if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 || ! isset( $map[ $slug ] ) ) {
				continue;
			}
			$band = (string) ( $dom['band'] ?? 'low' );
			if ( ( $band_rank[ $band ] ?? 0 ) >= 2 ) {
				$areas[] = $map[ $slug ];
			}
		}
		$areas = array_unique( $areas );
		if ( ! $areas ) {
			$areas = array( __( 'annual reassessment', 'ai-risk-benchmark' ) );
		}
		return sprintf(
			/* translators: %s: comma-separated review areas */
			__( 'Based on your results, we recommend reviewing your %s.', 'ai-risk-benchmark' ),
			implode( ', ', $areas )
		);
	}

	/**
	 * Stage 2 matched products by domain band.
	 *
	 * @param array<string, array<string, mixed>> $domain_scores Scores.
	 * @return array<int, array<string, string>>
	 */
	public static function matched_stage2_products( array $domain_scores ): array {
		$products  = self::stage2_products();
		$band_rank = array( 'low' => 0, 'moderate' => 1, 'high' => 2, 'critical' => 3 );
		$out       = array();

		foreach ( $products as $slug => $item ) {
			$dom = (array) ( $domain_scores[ $slug ] ?? array() );
			if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
				continue;
			}
			$band = (string) ( $dom['band'] ?? 'low' );
			if ( ( $band_rank[ $band ] ?? 0 ) < 2 ) {
				continue;
			}
			$out[] = array(
				'domain'  => $slug,
				'reason'  => $item['reason'],
				'product' => $item['product'],
				'risk'    => (string) round( (float) ( $dom['risk_percentage'] ?? 0 ) ),
			);
		}
		return $out;
	}

	/**
	 * Stage 3 — DfE Alignment / Leadership Report.
	 *
	 * @param array<string, mixed> $results Results.
	 * @param string               $role    Role.
	 * @param array<string, string> $profile Profile.
	 * @return array<string, mixed>
	 */
	public static function leadership_report( array $results, string $role, array $profile ): array {
		$high_risk = array();
		foreach ( (array) ( $results['key_exposure_areas'] ?? array() ) as $area ) {
			$high_risk[] = array(
				'label' => (string) ( $area['label'] ?? '' ),
				'risk'  => (float) ( $area['risk'] ?? 0 ),
			);
		}

		$actions = self::recommended_actions( $results, $role );

		$report = array(
			'title'               => __( 'DfE Alignment Report', 'ai-risk-benchmark' ),
			'overall_score'       => (int) ( $results['alignment_score'] ?? 0 ),
			'risk_level_label'    => (string) ( $results['risk_level_label'] ?? '' ),
			'high_risk_areas'     => $high_risk,
			'recommended_actions' => $actions,
			'show_full'           => in_array( $role, array( 'leader', 'teacher' ), true ),
		);

		if ( $profile['school_phase'] || $profile['org_type'] ) {
			$report['school_profile'] = self::format_school_profile( $profile );
		}

		return $report;
	}

	/**
	 * Numbered recommended actions for leadership report.
	 *
	 * @param array<string, mixed> $results Results.
	 * @param string               $role    Role.
	 * @return array<int, string>
	 */
	public static function recommended_actions( array $results, string $role ): array {
		$actions   = array();
		$domains   = (array) ( $results['domain_scores'] ?? array() );
		$band_rank = array( 'low' => 0, 'moderate' => 1, 'high' => 2, 'critical' => 3 );

		if ( self::domain_at_least( $domains, 'governance', 'moderate' ) ) {
			$actions[] = __( 'Implement or refresh your AI policy (AI Policy Generator)', 'ai-risk-benchmark' );
		}
		if ( self::domain_at_least( $domains, 'human_oversight', 'moderate' ) ) {
			$actions[] = __( 'Train staff on verification and human oversight', 'ai-risk-benchmark' );
		}
		if ( self::domain_at_least( $domains, 'assessment_integrity', 'moderate' ) ) {
			$actions[] = __( 'Conduct a JCQ-aligned assessment review', 'ai-risk-benchmark' );
		}
		if ( self::domain_at_least( $domains, 'privacy', 'moderate' ) ) {
			$actions[] = __( 'Complete the AI Data Protection Checklist with your DPO', 'ai-risk-benchmark' );
		}
		if ( in_array( $role, array( 'leader', 'teacher' ), true ) ) {
			$actions[] = __( 'Establish an annual governance and benchmark review', 'ai-risk-benchmark' );
		}
		if ( in_array( $role, array( 'leader', 'teacher' ), true ) ) {
			$actions[] = __( 'Plan an AI Awareness Day for the whole school community', 'ai-risk-benchmark' );
		}

		return array_values( array_unique( $actions ) );
	}

	/**
	 * Stage 4 — score-led consultation framing (not a sales pitch).
	 *
	 * @param array<string, mixed> $results Results.
	 * @param string               $role    Role.
	 * @param array<string, mixed> $config  Config.
	 * @return array<string, string>
	 */
	public static function consultation_pitch( array $results, string $role, array $config ): array {
		if ( ! in_array( $role, array( 'leader', 'teacher' ), true ) ) {
			return array();
		}

		$lines = array();
		$domains = (array) ( $results['domain_scores'] ?? array() );

		foreach ( array( 'human_oversight' => __( 'Human Oversight', 'ai-risk-benchmark' ), 'governance' => __( 'Governance', 'ai-risk-benchmark' ) ) as $slug => $label ) {
			$dom = (array) ( $domains[ $slug ] ?? array() );
			if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
				continue;
			}
			$readiness = (int) round( (float) ( $dom['readiness_percentage'] ?? 0 ) );
			$lines[]   = sprintf(
				'%d%% on %s',
				$readiness,
				$label
			);
		}

		$cta = (array) ( $config['consultation_cta'] ?? array() );
		$url = (string) ( $cta['url'] ?? '' );

		if ( $lines ) {
			$message = sprintf(
				/* translators: %s: e.g. "42% on Human Oversight and 51% on Governance" */
				__( 'Your benchmark shows %s. Let\'s walk through the findings together — not a sales pitch.', 'ai-risk-benchmark' ),
				implode( ' ' . __( 'and', 'ai-risk-benchmark' ) . ' ', $lines )
			);
		} else {
			$message = __( 'Your benchmark results are ready. Let\'s walk through the findings together — not a sales pitch.', 'ai-risk-benchmark' );
		}

		return array(
			'headline' => __( 'Stage 4: Walk through your findings', 'ai-risk-benchmark' ),
			'message'  => $message,
			'cta_text' => (string) ( $cta['title'] ?? __( 'Book a free consultation', 'ai-risk-benchmark' ) ),
			'cta_url'  => $url,
		);
	}

	/**
	 * Policy Generator offer when governance is weak.
	 *
	 * @param array<string, array<string, mixed>> $domain_scores Scores.
	 * @param array<string, string>               $profile       Profile.
	 * @param array<string, mixed>                  $config        Config.
	 * @return array<string, string>|null
	 */
	public static function policy_generator_offer( array $domain_scores, array $profile, array $config ): ?array {
		if ( ! self::domain_at_least( $domain_scores, 'governance', 'moderate' ) ) {
			return null;
		}

		$services = (array) ( $config['services'] ?? array() );
		$url      = 'https://aiawarenessday.co.uk/resources/';
		foreach ( (array) ( $services['items'] ?? array() ) as $item ) {
			if ( false !== stripos( (string) ( $item['label'] ?? '' ), 'Policy Generator' ) ) {
				$url = (string) ( $item['url'] ?? $url );
				break;
			}
		}

		$profile_txt = self::format_school_profile( $profile );
		$body        = $profile_txt
			? sprintf(
				/* translators: %s: school profile e.g. "Secondary · Standalone school" */
				__( 'Audit complete — school profile identified (%s). Generate a draft AI policy tailored to your phase, structure, usage patterns and governance maturity — not a static PDF.', 'ai-risk-benchmark' ),
				$profile_txt
			)
			: __( 'Audit complete. Generate a draft AI policy tailored to your school phase, structure, teacher and student AI usage, governance maturity and assessment approach.', 'ai-risk-benchmark' );

		return array(
			'title'    => __( 'AI Policy Generator', 'ai-risk-benchmark' ),
			'body'     => $body,
			'cta_text' => __( 'Start AI Policy Generator', 'ai-risk-benchmark' ),
			'cta_url'  => $url,
		);
	}

	/**
	 * AI Awareness Day™ promo — the intervention schools already buy.
	 *
	 * @param array<string, mixed> $config Config.
	 * @return array<string, string>|null
	 */
	public static function aad_promo( array $config ): ?array {
		$aad = (array) ( $config['aad_2027'] ?? array() );
		if ( empty( $aad['enabled'] ) || empty( $aad['headline'] ) ) {
			return null;
		}
		return array(
			'title'    => (string) $aad['headline'],
			'body'     => __( 'Schools already invest in INSET, safeguarding and digital citizenship days. AI Awareness Day™ maps directly to your benchmark gaps — teachers, leaders, students and parents in one programme.', 'ai-risk-benchmark' ),
			'cta_text' => (string) ( $aad['cta_text'] ?? __( 'Plan AI Awareness Day', 'ai-risk-benchmark' ) ),
			'cta_url'  => (string) ( $aad['cta_url'] ?? '' ),
		);
	}

	/**
	 * @param array<string, string> $profile Profile.
	 */
	private static function format_school_profile( array $profile ): string {
		$parts = array();
		$phases = array(
			'primary'     => __( 'Primary', 'ai-risk-benchmark' ),
			'secondary'   => __( 'Secondary', 'ai-risk-benchmark' ),
			'all_through' => __( 'All-through', 'ai-risk-benchmark' ),
		);
		$orgs = array(
			'standalone' => __( 'Standalone school', 'ai-risk-benchmark' ),
			'mat'        => __( 'MAT', 'ai-risk-benchmark' ),
		);
		if ( ! empty( $profile['school_phase'] ) && isset( $phases[ $profile['school_phase'] ] ) ) {
			$parts[] = $phases[ $profile['school_phase'] ];
		}
		if ( ! empty( $profile['org_type'] ) && isset( $orgs[ $profile['org_type'] ] ) ) {
			$parts[] = $orgs[ $profile['org_type'] ];
		}
		return implode( ' · ', $parts );
	}

	/**
	 * @param array<string, array<string, mixed>> $domains Domains.
	 * @param string                                $slug    Domain slug.
	 * @param string                                $min     Minimum band.
	 */
	private static function domain_at_least( array $domains, string $slug, string $min ): bool {
		$dom = (array) ( $domains[ $slug ] ?? array() );
		if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
			return false;
		}
		$band_rank = array( 'low' => 0, 'moderate' => 1, 'high' => 2, 'critical' => 3 );
		$band      = (string) ( $dom['band'] ?? 'low' );
		return ( $band_rank[ $band ] ?? 0 ) >= ( $band_rank[ $min ] ?? 1 );
	}
}
