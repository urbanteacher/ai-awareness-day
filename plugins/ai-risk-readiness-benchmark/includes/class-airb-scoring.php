<?php
/**
 * Scoring engine.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Calculate benchmark scores from answers.
 */
class AIRB_Scoring {

	/**
	 * Risk band thresholds.
	 *
	 * @param float $risk_pct Risk percentage 0-100.
	 * @return string low|moderate|high|critical
	 */
	public static function risk_band( float $risk_pct ): string {
		if ( $risk_pct <= 30 ) {
			return 'low';
		}
		if ( $risk_pct <= 60 ) {
			return 'moderate';
		}
		if ( $risk_pct <= 80 ) {
			return 'high';
		}
		return 'critical';
	}

	/**
	 * Human-readable band label.
	 *
	 * @param string $band Band slug.
	 */
	public static function band_label( string $band ): string {
		$labels = array(
			'low'      => __( 'Low', 'ai-risk-benchmark' ),
			'moderate' => __( 'Moderate', 'ai-risk-benchmark' ),
			'high'     => __( 'High', 'ai-risk-benchmark' ),
			'critical' => __( 'Critical', 'ai-risk-benchmark' ),
		);
		return $labels[ $band ] ?? ucfirst( $band );
	}

	/**
	 * Display label with nuance (e.g. Moderate–High).
	 *
	 * @param string $band     Base band.
	 * @param float  $risk_pct Overall risk percentage.
	 */
	public static function display_risk_label( string $band, float $risk_pct ): string {
		$base = self::band_label( $band );
		if ( 'moderate' === $band && $risk_pct >= 48 ) {
			return __( 'Moderate-High', 'ai-risk-benchmark' );
		}
		if ( 'high' === $band && $risk_pct >= 72 ) {
			return __( 'High–Critical', 'ai-risk-benchmark' );
		}
		if ( 'low' === $band && $risk_pct >= 22 ) {
			return __( 'Low–Moderate', 'ai-risk-benchmark' );
		}
		return $base;
	}

	/**
	 * Role-specific result card definitions.
	 *
	 * @param string               $role    Role slug.
	 * @param array<string, mixed> $results Scored results.
	 * @return array<int, array{key:string,label:string,value:string,band:string}>
	 */
	public static function role_result_cards( string $role, array $results ): array {
		$dom         = $results['domain_scores'] ?? array();
		$risk_level  = (string) ( $results['risk_level'] ?? 'low' );
		$overall_risk = (float) ( $results['overall_risk_percentage'] ?? 0 );
		$risk_label  = self::display_risk_label( $risk_level, $overall_risk );
		$cards       = array();

		switch ( $role ) {
			case 'teacher':
				$align = (int) ( $results['alignment_score'] ?? 0 );
				$dep   = (int) ( $results['dependency_index'] ?? 0 );
				$dep_band = self::risk_band( (float) $dep );
				$cards = array(
					array(
						'key'        => 'risk',
						'label'      => __( 'Teacher AI Risk Score', 'ai-risk-benchmark' ),
						'value'      => (int) round( $overall_risk ) . '%',
						'band'       => $risk_level,
						'tone'       => 'risk',
						'band_label' => $risk_label,
					),
					array(
						'key'        => 'ready',
						'label'      => __( 'Teacher AI Readiness Score', 'ai-risk-benchmark' ),
						'value'      => $align . '/100',
						'band'       => self::readiness_band( $align ),
						'tone'       => 'readiness',
						'band_label' => self::readiness_band_label( $align ),
					),
					array(
						'key'        => 'dep',
						'label'      => __( 'AI Dependency Index™', 'ai-risk-benchmark' ),
						'value'      => $dep . '%',
						'band'       => $dep_band,
						'tone'       => 'risk',
						'band_label' => self::band_label( $dep_band ),
					),
					array(
						'key'   => 'over',
						'label' => __( 'Human Oversight Ratio™', 'ai-risk-benchmark' ),
						'value' => (string) ( $results['human_oversight_label'] ?? '' ),
						'band'  => self::oversight_band_from_label( (string) ( $results['human_oversight_label'] ?? '' ) ),
						'tone'  => 'oversight',
					),
				);
				break;
			case 'student':
				$lit = (int) round( $dom['ai_literacy']['readiness_percentage'] ?? 0 );
				$dep = (int) ( $results['dependency_index'] ?? 0 );
				$dep_band = self::risk_band( (float) $dep );
				$cards = array(
					array(
						'key'        => 'risk',
						'label'      => __( 'Student Learning Risk Score', 'ai-risk-benchmark' ),
						'value'      => (int) round( $overall_risk ) . '%',
						'band'       => $risk_level,
						'tone'       => 'risk',
						'band_label' => $risk_label,
					),
					array(
						'key'        => 'dep',
						'label'      => __( 'Student AI Dependency Score', 'ai-risk-benchmark' ),
						'value'      => $dep . '%',
						'band'       => $dep_band,
						'tone'       => 'risk',
						'band_label' => self::band_label( $dep_band ),
					),
					array(
						'key'        => 'lit',
						'label'      => __( 'Student AI Literacy Score', 'ai-risk-benchmark' ),
						'value'      => $lit . '%',
						'band'       => self::readiness_band( $lit ),
						'tone'       => 'readiness',
						'band_label' => self::readiness_band_label( $lit ),
					),
				);
				break;
			case 'parent':
				$aware  = (int) round( $dom['ai_literacy']['readiness_percentage'] ?? $results['alignment_score'] ?? 0 );
				$safety = (int) round( $dom['safeguarding']['readiness_percentage'] ?? $results['safeguarding_readiness'] ?? 0 );
				$align  = (int) ( $results['alignment_score'] ?? 0 );
				$cards  = array(
					array(
						'key'        => 'aware',
						'label'      => __( 'Parent AI Awareness Score', 'ai-risk-benchmark' ),
						'value'      => $aware . '%',
						'band'       => self::readiness_band( $aware ),
						'tone'       => 'readiness',
						'band_label' => self::readiness_band_label( $aware ),
					),
					array(
						'key'        => 'safe',
						'label'      => __( 'Parent Digital Safety Score', 'ai-risk-benchmark' ),
						'value'      => $safety . '%',
						'band'       => self::readiness_band( $safety ),
						'tone'       => 'readiness',
						'band_label' => self::readiness_band_label( $safety ),
					),
					array(
						'key'        => 'ready',
						'label'      => __( 'Parent Readiness Score', 'ai-risk-benchmark' ),
						'value'      => $align . '/100',
						'band'       => self::readiness_band( $align ),
						'tone'       => 'readiness',
						'band_label' => self::readiness_band_label( $align ),
					),
				);
				break;
			case 'leader':
				$gov  = (int) ( $results['governance_maturity'] ?? 0 );
				$safe = (int) ( $results['safeguarding_readiness'] ?? 0 );
				$align = (int) ( $results['alignment_score'] ?? 0 );
				$cards = array(
					array(
						'key'        => 'gov',
						'label'      => __( 'Governance Maturity Score', 'ai-risk-benchmark' ),
						'value'      => $gov . '%',
						'band'       => self::readiness_band( $gov ),
						'tone'       => 'readiness',
						'band_label' => self::readiness_band_label( $gov ),
					),
					array(
						'key'        => 'safe',
						'label'      => __( 'Safeguarding Readiness Score', 'ai-risk-benchmark' ),
						'value'      => $safe . '%',
						'band'       => self::readiness_band( $safe ),
						'tone'       => 'readiness',
						'band_label' => self::readiness_band_label( $safe ),
					),
					array(
						'key'        => 'dfe',
						'label'      => __( 'DfE AI Alignment Score', 'ai-risk-benchmark' ),
						'value'      => $align . '/100',
						'band'       => self::readiness_band( $align ),
						'tone'       => 'readiness',
						'band_label' => self::readiness_band_label( $align ),
					),
				);
				break;
			default:
				$align = (int) ( $results['alignment_score'] ?? 0 );
				$cards = array(
					array(
						'key'        => 'align',
						'label'      => __( 'DfE AI Alignment Score', 'ai-risk-benchmark' ),
						'value'      => $align . '/100',
						'band'       => self::readiness_band( $align ),
						'tone'       => 'readiness',
						'band_label' => self::readiness_band_label( $align ),
					),
				);
		}

		return $cards;
	}

	/**
	 * Readiness score to positive band slug.
	 *
	 * @param int $readiness Readiness 0-100.
	 */
	public static function readiness_band( int $readiness ): string {
		if ( $readiness >= 75 ) {
			return 'strong';
		}
		if ( $readiness >= 60 ) {
			return 'established';
		}
		if ( $readiness >= 45 ) {
			return 'developing';
		}
		return 'emerging';
	}

	/**
	 * Human-readable readiness band label.
	 *
	 * @param int $readiness Readiness 0-100.
	 */
	public static function readiness_band_label( int $readiness ): string {
		$labels = array(
			'strong'      => __( 'Strong', 'ai-risk-benchmark' ),
			'established' => __( 'Established', 'ai-risk-benchmark' ),
			'developing'  => __( 'Developing', 'ai-risk-benchmark' ),
			'emerging'    => __( 'Emerging', 'ai-risk-benchmark' ),
		);
		$band = self::readiness_band( $readiness );
		return $labels[ $band ] ?? ucfirst( $band );
	}

	/**
	 * Oversight label to band for styling.
	 *
	 * @param string $label Oversight label.
	 */
	public static function oversight_band_from_label( string $label ): string {
		if ( false !== stripos( $label, 'Strong' ) ) {
			return 'low';
		}
		if ( false !== stripos( $label, 'Moderate' ) ) {
			return 'moderate';
		}
		if ( false !== stripos( $label, 'High' ) ) {
			return 'high';
		}
		if ( false !== stripos( $label, 'Critical' ) ) {
			return 'critical';
		}
		return 'moderate';
	}

	/**
	 * Top domain exposure areas.
	 *
	 * @param array<string, array<string, mixed>> $domain_scores Domain scores.
	 * @param int                                 $limit         Max items.
	 * @return array<int, array{slug:string,label:string,risk:float}>
	 */
	public static function key_exposure_areas( array $domain_scores, int $limit = 3 ): array {
		$rows = array();
		foreach ( $domain_scores as $slug => $dom ) {
			if ( empty( $dom['questions_answered'] ) ) {
				continue;
			}
			$rows[] = array(
				'slug'  => (string) $slug,
				'label' => (string) ( $dom['label'] ?? $slug ),
				'risk'  => (float) ( $dom['risk_percentage'] ?? 0 ),
			);
		}
		usort(
			$rows,
			static function ( $a, $b ) {
				return $b['risk'] <=> $a['risk'];
			}
		);
		return array_slice( $rows, 0, $limit );
	}

	/**
	 * Composite AI Dependency Index™ across dependency + related questions.
	 *
	 * @param string               $role    Role.
	 * @param array<string, mixed> $answers Answers.
	 * @param array<string, mixed> $config  Config.
	 */
	public static function composite_dependency_index( string $role, array $answers, array $config ): int {
		$qids = array(
			'teacher' => array( 't_without_ai', 't_ai_before_task', 't_feedback_ai' ),
			'student' => array( 's_attempt_first', 's_without_ai', 's_submitted_ai' ),
			'parent'  => array(),
			'leader'  => array(),
		);
		$scores = array();

		foreach ( (array) ( $config['questions'] ?? array() ) as $question ) {
			if ( (string) ( $question['role'] ?? '' ) !== $role ) {
				continue;
			}
			$qid = (string) ( $question['id'] ?? '' );
			if ( ! array_key_exists( $qid, $answers ) ) {
				continue;
			}
			$dom = (string) ( $question['domain'] ?? '' );
			$in_list = in_array( $qid, $qids[ $role ] ?? array(), true );
			if ( 'ai_dependency' === $dom || $in_list ) {
				$scores[] = self::score_answer( $question, $answers[ $qid ] );
			}
		}

		if ( ! $scores ) {
			return 0;
		}
		$avg = array_sum( $scores ) / count( $scores );
		return (int) round( ( $avg / 3 ) * 100 );
	}

	/**
	 * Composite Human Oversight Ratio™ readiness (0-100) and label.
	 *
	 * @param string               $role    Role.
	 * @param array<string, mixed> $answers Answers.
	 * @param array<string, mixed> $config  Config.
	 * @return array{readiness:int,label:string,pct:int|null}
	 */
	public static function composite_human_oversight( string $role, array $answers, array $config ): array {
		$readiness_vals = array();
		$modify_pct     = null;

		foreach ( (array) ( $config['questions'] ?? array() ) as $question ) {
			if ( (string) ( $question['role'] ?? '' ) !== $role ) {
				continue;
			}
			$qid = (string) ( $question['id'] ?? '' );
			if ( ! array_key_exists( $qid, $answers ) ) {
				continue;
			}
			$value = $answers[ $qid ];
			$dom   = (string) ( $question['domain'] ?? '' );

			if ( 'slider' === ( $question['type'] ?? '' ) && 'human_oversight' === $dom ) {
				$modify_pct       = max( 0, min( 100, (int) $value ) );
				$readiness_vals[] = $modify_pct;
				continue;
			}

			if ( 'human_oversight' === $dom ) {
				$score            = self::score_answer( $question, $value );
				$readiness_vals[] = 100 - ( ( $score / 3 ) * 100 );
			}
		}

		if ( ! $readiness_vals ) {
			return array(
				'readiness' => 0,
				'label'     => __( 'Not assessed', 'ai-risk-benchmark' ),
				'pct'       => null,
			);
		}

		$readiness = (int) round( array_sum( $readiness_vals ) / count( $readiness_vals ) );

		if ( null !== $modify_pct ) {
			$label = self::human_oversight_label( $modify_pct );
		} elseif ( $readiness >= 51 ) {
			$label = __( 'Strong oversight', 'ai-risk-benchmark' );
		} elseif ( $readiness >= 26 ) {
			$label = __( 'Moderate oversight', 'ai-risk-benchmark' );
		} elseif ( $readiness >= 11 ) {
			$label = __( 'High reliance', 'ai-risk-benchmark' );
		} else {
			$label = __( 'Critical reliance', 'ai-risk-benchmark' );
		}

		return array(
			'readiness' => $readiness,
			'label'     => $label,
			'pct'       => $modify_pct,
		);
	}

	/**
	 * Human oversight label from modify-percentage slider.
	 *
	 * @param int $pct Percentage 0-100.
	 */
	public static function human_oversight_label( int $pct ): string {
		if ( $pct <= 10 ) {
			return __( 'Critical reliance', 'ai-risk-benchmark' );
		}
		if ( $pct <= 25 ) {
			return __( 'High reliance', 'ai-risk-benchmark' );
		}
		if ( $pct <= 50 ) {
			return __( 'Moderate oversight', 'ai-risk-benchmark' );
		}
		return __( 'Strong oversight', 'ai-risk-benchmark' );
	}

	/**
	 * Score slider modify % to risk 0-3.
	 *
	 * @param int $pct Percentage modified.
	 */
	public static function slider_modify_score( int $pct ): int {
		if ( $pct >= 51 ) {
			return 0;
		}
		if ( $pct >= 26 ) {
			return 1;
		}
		if ( $pct >= 11 ) {
			return 2;
		}
		return 3;
	}

	/**
	 * Score a single answer.
	 *
	 * @param array<string, mixed> $question Question config.
	 * @param mixed                $value    Answer value.
	 */
	public static function score_answer( array $question, $value ): int {
		$type = (string) ( $question['type'] ?? 'radio' );

		if ( 'slider' === $type ) {
			$pct = max( 0, min( 100, (int) $value ) );
			return self::slider_modify_score( $pct );
		}

		foreach ( (array) ( $question['options'] ?? array() ) as $opt ) {
			if ( (string) ( $opt['value'] ?? '' ) === (string) $value ) {
				return max( 0, min( 3, (int) ( $opt['score'] ?? 0 ) ) );
			}
		}

		return 0;
	}

	/**
	 * Full scoring pass.
	 *
	 * @param string               $role    Role slug.
	 * @param array<string, mixed> $answers question_id => value.
	 * @param array<string, mixed> $config  Full config.
	 * @return array<string, mixed>
	 */
	public static function calculate( string $role, array $answers, array $config ): array {
		$domains       = AIRB_Defaults::domains();
		$domain_scores = array();
		$domain_sums   = array();
		$domain_counts = array();

		foreach ( array_keys( $domains ) as $slug ) {
			$domain_sums[ $slug ]   = 0.0;
			$domain_counts[ $slug ] = 0;
		}

		$human_oversight_pct   = null;
		$human_oversight_label = __( 'Not assessed', 'ai-risk-benchmark' );

		foreach ( (array) ( $config['questions'] ?? array() ) as $question ) {
			if ( (string) ( $question['role'] ?? '' ) !== $role ) {
				continue;
			}
			$qid = (string) ( $question['id'] ?? '' );
			if ( ! $qid || ! array_key_exists( $qid, $answers ) ) {
				continue;
			}

			$value = $answers[ $qid ];
			$score = self::score_answer( $question, $value );
			$dom   = (string) ( $question['domain'] ?? '' );

			if ( isset( $domain_sums[ $dom ] ) ) {
				$domain_sums[ $dom ]   += $score;
				$domain_counts[ $dom ] += 1;
			}

			if ( 't_modify_pct' === $qid || ( 'slider' === ( $question['type'] ?? '' ) && 'human_oversight' === $dom ) ) {
				$human_oversight_pct   = max( 0, min( 100, (int) $value ) );
				$human_oversight_label = self::human_oversight_label( $human_oversight_pct );
			}
		}

		$risk_values = array();
		foreach ( $domains as $slug => $label ) {
			$count = $domain_counts[ $slug ];
			if ( $count > 0 ) {
				$avg_risk = ( $domain_sums[ $slug ] / $count ) / 3 * 100;
			} else {
				$avg_risk = 0;
			}
			$band = self::risk_band( $avg_risk );
			$readiness_pct = (int) round( 100 - $avg_risk );
			$domain_scores[ $slug ] = array(
				'label'                => $label,
				'risk_percentage'      => round( $avg_risk, 1 ),
				'readiness_percentage' => round( 100 - $avg_risk, 1 ),
				'band'                 => $band,
				'band_label'           => self::band_label( $band ),
				'readiness_band'       => self::readiness_band( $readiness_pct ),
				'readiness_band_label' => self::readiness_band_label( $readiness_pct ),
				'questions_answered'   => $count,
			);
			if ( $count > 0 ) {
				$risk_values[] = $avg_risk;
			}
		}

		$overall_risk       = $risk_values ? array_sum( $risk_values ) / count( $risk_values ) : 0;
		$overall_band       = self::risk_band( $overall_risk );
		$alignment_score    = (int) round( 100 - $overall_risk );
		$dependency_index   = self::composite_dependency_index( $role, $answers, $config );
		$oversight          = self::composite_human_oversight( $role, $answers, $config );
		$privacy_risk       = (int) round( $domain_scores['privacy']['risk_percentage'] ?? 0 );
		$safeguarding_ready = (int) round( $domain_scores['safeguarding']['readiness_percentage'] ?? 0 );
		$governance_mature  = (int) round( $domain_scores['governance']['readiness_percentage'] ?? 0 );

		$recommendations = self::match_recommendations( $domain_scores, (array) ( $config['recommendations'] ?? array() ), $role );

		$human_oversight_pct   = $oversight['pct'];
		$human_oversight_label = $oversight['label'];

		$key_exposure = self::key_exposure_areas( $domain_scores );
		$result_payload = array(
			'role'                    => $role,
			'risk_level'              => $overall_band,
			'alignment_score'         => $alignment_score,
			'dependency_index'        => $dependency_index,
			'human_oversight_label'   => $human_oversight_label,
			'overall_risk_percentage' => round( $overall_risk, 1 ),
			'domain_scores'           => $domain_scores,
			'governance_maturity'     => $governance_mature,
			'safeguarding_readiness'  => $safeguarding_ready,
		);
		$result_cards = self::role_result_cards( $role, $result_payload );

		$results = array(
			'role'                    => $role,
			'risk_level'              => $overall_band,
			'risk_level_label'        => self::display_risk_label( $overall_band, $overall_risk ),
			'readiness_level'         => self::readiness_band( $alignment_score ),
			'readiness_level_label'   => self::readiness_band_label( $alignment_score ),
			'alignment_score'         => $alignment_score,
			'dependency_index'        => $dependency_index,
			'human_oversight_ratio'   => $human_oversight_pct,
			'human_oversight_readiness' => $oversight['readiness'],
			'human_oversight_label'   => $human_oversight_label,
			'privacy_risk'            => $privacy_risk,
			'safeguarding_readiness'  => $safeguarding_ready,
			'governance_maturity'     => $governance_mature,
			'overall_risk_percentage' => round( $overall_risk, 1 ),
			'domain_scores'           => $domain_scores,
			'recommendations'         => $recommendations,
			'key_exposure_areas'      => $key_exposure,
			'role_result_cards'       => $result_cards,
			'next_steps'              => AIRB_Pathway::match_next_steps( $role, $answers, $domain_scores, $result_payload, $config ),
		);

		return $results;
	}

	/**
	 * Match recommendations by domain band (optionally filtered by role).
	 *
	 * @param array<string, array<string, mixed>> $domain_scores Domain scores.
	 * @param array<int, array<string, mixed>>   $rules         Recommendation rules.
	 * @param string                             $role          Role slug.
	 * @return array<int, array<string, mixed>>
	 */
	public static function match_recommendations( array $domain_scores, array $rules, string $role = '' ): array {
		$band_rank = array( 'low' => 0, 'moderate' => 1, 'high' => 2, 'critical' => 3 );
		$out       = array();

		foreach ( $rules as $rule ) {
			$roles = (array) ( $rule['roles'] ?? array() );
			if ( $role && $roles && ! in_array( $role, $roles, true ) ) {
				continue;
			}
			$dom = (string) ( $rule['domain'] ?? '' );
			if ( ! isset( $domain_scores[ $dom ] ) ) {
				continue;
			}
			$min_band = (string) ( $rule['min_band'] ?? 'high' );
			$actual   = (string) ( $domain_scores[ $dom ]['band'] ?? 'low' );
			if ( ( $band_rank[ $actual ] ?? 0 ) >= ( $band_rank[ $min_band ] ?? 2 ) ) {
				$out[] = $rule;
			}
		}

		return $out;
	}
}
