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
				'product' => __( 'Develop your AI policy (DfE template)', 'ai-risk-benchmark' ),
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
	public static function enrich( array $results, string $role, array $profile, array $config, string $school = '' ): array {
		$domain_scores = (array) ( $results['domain_scores'] ?? array() );

		$results['risk_heatmap']        = self::risk_heatmap( $domain_scores );
		$results['funnel_closing']      = self::stage1_closing( $results, $role );
		$results['stage2_products']     = self::matched_stage2_products( $domain_scores );
		$results['leadership_report']   = self::leadership_report( $results, $role, $profile );
		$results['consultation_pitch']  = self::consultation_pitch( $results, $role, $config );
		$results['policy_support']      = self::policy_support_offer( $domain_scores, $profile, $config );
		$results['aad_promo']           = self::aad_promo( $config );

		if ( 'teacher' === $role ) {
			$gap_products                 = $results['stage2_products'];
			$results['funnel_closing']    = '';
			$results['teacher_results']   = AIRB_Teacher_Results::build( $results, $school, $config, $gap_products );
			$results['stage2_products']   = array();
			if ( isset( $results['leadership_report'] ) ) {
				$results['leadership_report']['show_full'] = false;
			}
		}

		if ( 'student' === $role ) {
			$results['funnel_closing']  = '';
			$results['student_results'] = AIRB_Student_Results::build( $results, $school );
			$results['stage2_products'] = array();
			$results['key_exposure_areas'] = array();
			if ( isset( $results['leadership_report'] ) ) {
				$results['leadership_report']['show_full'] = false;
			}
		}

		if ( 'leader' === $role ) {
			$policy_gen = $results['policy_support'] ?? null;
			$aad_promo  = $results['aad_promo'] ?? null;
			$results['funnel_closing']    = '';
			$results['leader_results']    = AIRB_Leader_Results::build(
				$results,
				$school,
				$profile,
				$config,
				is_array( $policy_gen ) ? $policy_gen : null,
				is_array( $aad_promo ) ? $aad_promo : null
			);
			$results['stage2_products']   = array();
			$results['key_exposure_areas'] = array();
			if ( isset( $results['leadership_report'] ) ) {
				$results['leadership_report']['show_full'] = false;
			}
		}

		if ( 'parent' === $role ) {
			$results['funnel_closing']  = '';
			$results['parent_results']  = AIRB_Parent_Results::build( $results );
			$results['stage2_products'] = array();
			$results['key_exposure_areas'] = array();
			if ( isset( $results['leadership_report'] ) ) {
				$results['leadership_report']['show_full'] = false;
			}
		}

		if ( 'support_staff' === $role ) {
			$gap_products                 = $results['stage2_products'];
			$results['funnel_closing']    = '';
			$results['support_results']   = AIRB_Support_Results::build( $results, $config, $school );
			$results['stage2_products']   = array();
			$results['key_exposure_areas'] = array();
			if ( isset( $results['leadership_report'] ) ) {
				$results['leadership_report']['show_full'] = false;
			}
		}

		if ( 'public' === $role ) {
			$results['funnel_closing']     = '';
			$results['public_results']     = AIRB_Public_Results::build( $results );
			$results['stage2_products']    = array();
			$results['key_exposure_areas'] = array();
			if ( isset( $results['leadership_report'] ) ) {
				$results['leadership_report']['show_full'] = false;
			}
		}

		if ( ! in_array( $role, array( 'parent', 'student', 'leader', 'teacher', 'support_staff', 'public' ), true ) ) {
			$results['guided_improvement'] = AIRB_Improvement_Pathways::build( $role, $results );
		} else {
			$results['guided_improvement'] = array();
		}

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
		if ( in_array( $role, array( 'parent', 'teacher', 'student' ), true ) ) {
			return '';
		}

		$areas = array();
		$map   = array(
			'governance'           => __( 'AI Policy', 'ai-risk-benchmark' ),
			'human_oversight'      => __( 'Staff Training', 'ai-risk-benchmark' ),
			'assessment_integrity' => __( 'Assessment Controls', 'ai-risk-benchmark' ),
			'ai_dependency'        => __( 'Independent Practice', 'ai-risk-benchmark' ),
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
			'show_full'           => 'leader' === $role,
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
			$actions[] = __( 'Implement or refresh your AI policy (adapt the DfE AI policy template)', 'ai-risk-benchmark' );
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
	 * Support contact block for teachers and leaders after results.
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

		$cta = (array) ( $config['consultation_cta'] ?? array() );
		$url = (string) ( $cta['url'] ?? '' );

		return array(
			'headline' => __( 'Get support from the AI Awareness Day team', 'ai-risk-benchmark' ),
			'message'  => __( 'Questions about your results or what to do next? Contact the AI Awareness Day team — we can help you interpret your scores and plan practical next steps for your school.', 'ai-risk-benchmark' ),
			'cta_text' => (string) ( $cta['title'] ?? __( 'Contact the AI Awareness Day team', 'ai-risk-benchmark' ) ),
			'cta_url'  => $url,
		);
	}

	/**
	 * AI policy support offer (official DfE template) when governance is weak.
	 *
	 * @param array<string, array<string, mixed>> $domain_scores Scores.
	 * @param array<string, string>               $profile       Profile.
	 * @param array<string, mixed>                  $config        Config.
	 * @return array<string, string>|null
	 */
	public static function policy_support_offer( array $domain_scores, array $profile, array $config ): ?array {
		if ( ! self::domain_at_least( $domain_scores, 'governance', 'moderate' ) ) {
			return null;
		}

		$services = (array) ( $config['services'] ?? array() );
		$url      = AIRB_Defaults::dfe_url_using_ai();
		foreach ( (array) ( $services['items'] ?? array() ) as $item ) {
			if ( false !== stripos( (string) ( $item['label'] ?? '' ), 'AI Policy' ) ) {
				$url = (string) ( $item['url'] ?? $url );
				break;
			}
		}

		$profile_txt = self::format_school_profile( $profile );
		$body        = $profile_txt
			? sprintf(
				/* translators: %s: school profile e.g. "Secondary · Standalone school" */
				__( 'Audit complete — school profile identified (%s). The DfE publishes a free AI policy template you can adapt to your phase, structure and governance maturity. Contact the AI Awareness Day team if you need support tailoring it.', 'ai-risk-benchmark' ),
				$profile_txt
			)
			: __( 'Audit complete. Adapt the official DfE AI policy template to your school phase, structure, AI usage and governance maturity. Contact the AI Awareness Day team if you need support tailoring it.', 'ai-risk-benchmark' );

		return array(
			'title'    => __( 'Develop your AI policy', 'ai-risk-benchmark' ),
			'body'     => $body,
			'cta_text' => __( 'View DfE AI policy template', 'ai-risk-benchmark' ),
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
