<?php
/**
 * Parent / carer results — tiered journeys, benchmarking and school advocacy.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds parent-facing result payloads.
 */
class AIRB_Parent_Results {

	private const HIGH_SCORE_THRESHOLD      = 85;
	private const LOW_SCORE_THRESHOLD       = 40;
	private const CONFIDENCE_FOCUS_MAX      = 75;
	private const STRENGTH_READINESS_MIN    = 70;
	private const FOCUS_MAX                 = 75;

	/**
	 * @param array<string, mixed> $results Scored results (incl. parent_display_domains).
	 * @param array<string, mixed> $answers Optional answer map.
	 * @param array<string, mixed> $config  Plugin config.
	 * @return array<string, mixed>
	 */
	public static function build( array $results, array $answers = array(), array $config = array() ): array {
		if ( empty( $config ) && class_exists( 'AIRB_Config' ) ) {
			$config = AIRB_Config::get();
		}
		$cfg            = AIRB_Defaults::parent_result_config();
		$score          = (int) ( $results['alignment_score'] ?? 0 );
		$tier           = self::journey_tier( $score );
		$home_metrics   = self::home_metrics( $results, $cfg );
		$focus_areas    = self::build_focus_areas( $home_metrics, $results, $cfg, $answers, $config );
		$ui             = AIRB_Parent_Copy::resolve_ui( $results, $cfg );
		$conversations  = AIRB_Parent_Copy::conversation_starters( $focus_areas, $cfg );

		return array(
			'journey_tier'           => $tier,
			'suppress_improvement'   => 'high' === $tier,
			'ui'                     => $ui,
			'home_metrics'           => $home_metrics,
			'focus_areas'            => $focus_areas,
			'conversation_starters'  => $conversations,
			'peer_benchmark'         => self::peer_benchmark( $results, $cfg ),
			'advocate'               => 'high' === $tier ? self::advocate_block( $results, $cfg ) : null,
			'confidence'             => self::confidence_block( $results, $cfg ),
			'next_steps'             => self::next_steps( $tier, $cfg ),
			'resource_links'         => (array) ( $cfg['resource_links'] ?? array() ),
		);
	}

	/**
	 * Parent-facing awareness band (distinct from school readiness bands).
	 *
	 * @param int $score Alignment score 0-100.
	 * @return array{slug:string,label:string}
	 */
	public static function awareness_band( int $score ): array {
		$score  = max( 0, min( 100, $score ) );
		$levels = AIRB_Defaults::parent_awareness_levels();

		foreach ( $levels as $level ) {
			if ( $score >= (int) $level['min'] && $score <= (int) $level['max'] ) {
				return array(
					'slug'  => (string) $level['slug'],
					'label' => (string) $level['label'],
				);
			}
		}

		return array(
			'slug'  => 'developing',
			'label' => __( 'Developing', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * Presentation metrics for the parent home safety card.
	 *
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<int, array<string, mixed>>
	 */
	public static function home_metrics( array $results, array $cfg ): array {
		$display = (array) ( $results['parent_display_domains'] ?? array() );
		$defs    = (array) ( $cfg['home_metrics'] ?? array() );
		$out     = array();

		foreach ( $defs as $def ) {
			if ( ! is_array( $def ) ) {
				continue;
			}
			$source = (string) ( $def['source'] ?? $def['slug'] ?? '' );
			$pct    = self::metric_readiness( $display, $source );
			if ( null === $pct ) {
				continue;
			}
			$badge = AIRB_Parent_Copy::metric_badge( $pct );
			$out[] = array(
				'slug'       => (string) ( $def['slug'] ?? $source ),
				'label'      => (string) ( $def['label'] ?? '' ),
				'subtitle'   => (string) ( $def['subtitle'] ?? '' ),
				'icon'       => (string) ( $def['icon'] ?? 'eye' ),
				'value'      => $pct,
				'badge'      => $badge,
				'focus_slug' => (string) ( $def['focus_slug'] ?? '' ),
				'source'     => $source,
			);
		}

		return $out;
	}

	/**
	 * Focus cards for the weakest home metrics.
	 *
	 * @param array<int, array<string, mixed>> $metrics Home metrics.
	 * @param array<string, mixed>             $results Results.
	 * @param array<string, mixed>             $cfg     Config.
	 * @return array<int, array<string, mixed>>
	 */
	private static function build_focus_areas( array $metrics, array $results, array $cfg, array $answers = array(), array $config = array() ): array {
		$slug_map = (array) ( $cfg['focus_slug_map'] ?? array() );
		$topics   = array();
		$display  = (array) ( $cfg['display_domains'] ?? array() );
		foreach ( (array) ( $cfg['focus_topics'] ?? array() ) as $topic ) {
			if ( ! is_array( $topic ) || empty( $topic['slug'] ) ) {
				continue;
			}
			$topics[ (string) $topic['slug'] ] = $topic;
		}

		$weak = array();
		foreach ( $metrics as $metric ) {
			$pct = (int) ( $metric['value'] ?? 100 );
			if ( $pct >= self::FOCUS_MAX ) {
				continue;
			}
			$source     = (string) ( $metric['source'] ?? $metric['slug'] ?? '' );
			$focus_slug = (string) ( $metric['focus_slug'] ?? ( $slug_map[ $source ] ?? $source ) );
			if ( ! $focus_slug && isset( $topics[ $source ] ) ) {
				$focus_slug = $source;
			}
			if ( ! $focus_slug ) {
				continue;
			}
			$topic = (array) ( $topics[ $source ] ?? array() );
			$weak[] = array(
				'slug'       => $source,
				'focus_slug' => $focus_slug,
				'label'      => (string) ( $metric['label'] ?? $topic['label'] ?? '' ),
				'pct'        => $pct,
				'badge'      => (array) ( $metric['badge'] ?? AIRB_Parent_Copy::metric_badge( $pct ) ),
				'summary'    => (string) ( $topic['body'] ?? '' ),
			);
		}

		$display = (array) ( $results['parent_display_domains'] ?? array() );
		foreach ( array( 'parent_ai_dependency' ) as $extra_slug ) {
			$pct = self::metric_readiness( $display, $extra_slug );
			if ( null === $pct || $pct >= self::FOCUS_MAX ) {
				continue;
			}
			$already = false;
			foreach ( $weak as $row ) {
				if ( (string) ( $row['slug'] ?? '' ) === $extra_slug ) {
					$already = true;
					break;
				}
			}
			if ( $already ) {
				continue;
			}
			$topic = (array) ( $topics[ $extra_slug ] ?? array() );
			$weak[] = array(
				'slug'       => $extra_slug,
				'focus_slug' => (string) ( $slug_map[ $extra_slug ] ?? $extra_slug ),
				'label'      => (string) ( $topic['label'] ?? '' ),
				'pct'        => $pct,
				'badge'      => AIRB_Parent_Copy::metric_badge( $pct ),
				'summary'    => (string) ( $topic['body'] ?? '' ),
			);
		}

		usort(
			$weak,
			static function ( $a, $b ) {
				return ( $a['pct'] ?? 100 ) <=> ( $b['pct'] ?? 100 );
			}
		);

		$out = array();
		foreach ( $weak as $area ) {
			$block = AIRB_Parent_Copy::focus_block( $area, $cfg );
			$pct   = (int) ( $area['pct'] ?? 100 );
			$block = AIRB_Results_Guidance::enrich_focus_block(
				$block,
				$pct,
				'parent',
				array( (string) ( $area['slug'] ?? '' ) ),
				$answers,
				$config,
				$display
			);
			$out[] = array_merge( $area, $block );
		}

		return $out;
	}

	/**
	 * @param array<string, mixed> $display Parent display domains.
	 * @param string               $slug    Domain slug.
	 */
	private static function metric_readiness( array $display, string $slug ): ?int {
		$dom = (array) ( $display[ $slug ] ?? array() );
		if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
			return null;
		}
		$is_risk = ( 'risk' === ( $dom['metric_type'] ?? 'score' ) );
		return (int) round( (float) ( $is_risk ? ( 100 - ( $dom['risk_percentage'] ?? 0 ) ) : ( $dom['readiness_percentage'] ?? 0 ) ) );
	}

	/**
	 * @param int $score Alignment score 0-100.
	 */
	public static function journey_tier( int $score ): string {
		if ( $score >= self::HIGH_SCORE_THRESHOLD ) {
			return 'high';
		}
		if ( $score < self::LOW_SCORE_THRESHOLD ) {
			return 'low';
		}
		return 'medium';
	}

	/**
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<string, mixed>
	 */
	private static function peer_benchmark( array $results, array $cfg ): array {
		$score    = (int) ( $results['alignment_score'] ?? 0 );
		$stats    = AIRB_Database::get_benchmark_stats( 'parent', $score );
		$fallback = (array) ( $cfg['peer_benchmark_fallback'] ?? array() );

		if ( $stats ) {
			return array(
				'your_score'    => $score,
				'average_score' => (int) ( $stats['average'] ?? $stats['averages']['alignment_score'] ?? 58 ),
				'top_quartile'  => (int) ( $stats['top_quartile'] ?? 82 ),
				'sample_size'   => (int) ( $stats['sample_size'] ?? 0 ),
				'percentile'    => isset( $stats['percentile'] ) ? (int) $stats['percentile'] : null,
				'is_estimated'  => false,
			);
		}

		return array(
			'your_score'    => $score,
			'average_score' => (int) ( $fallback['average'] ?? 58 ),
			'top_quartile'  => (int) ( $fallback['top_quartile'] ?? 82 ),
			'sample_size'   => 0,
			'percentile'    => null,
			'is_estimated'  => true,
		);
	}

	/**
	 * High-scorer advocacy block.
	 *
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<string, mixed>
	 */
	private static function advocate_block( array $results, array $cfg ): array {
		$display   = (array) ( $results['parent_display_domains'] ?? array() );
		$labels    = (array) ( $cfg['advocate_strength_labels'] ?? array() );
		$strengths = array();

		foreach ( $labels as $slug => $label ) {
			$pct = self::metric_readiness( $display, (string) $slug );
			if ( null === $pct || $pct < self::STRENGTH_READINESS_MIN ) {
				continue;
			}
			$strengths[] = (string) $label;
		}

		return array(
			'title'       => (string) ( $cfg['advocate_title'] ?? __( 'You\'re ahead of most parents', 'ai-risk-benchmark' ) ),
			'intro'       => (string) ( $cfg['advocate_intro'] ?? __( 'You demonstrate strong awareness of AI use at home. Help your school build a whole-community picture.', 'ai-risk-benchmark' ) ),
			'strengths'   => $strengths,
			'help_title'  => (string) ( $cfg['advocate_help_title'] ?? __( 'Help your school', 'ai-risk-benchmark' ) ),
			'help_items'  => (array) ( $cfg['advocate_help_items'] ?? array() ),
		);
	}

	/**
	 * Confidence guidance when score is below threshold.
	 *
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<string, mixed>|null
	 */
	private static function confidence_block( array $results, array $cfg ): ?array {
		$readiness = self::metric_readiness( (array) ( $results['parent_display_domains'] ?? array() ), 'homework_oversight' );
		if ( null === $readiness || $readiness >= self::CONFIDENCE_FOCUS_MAX ) {
			return null;
		}

		$copy = (array) ( $cfg['confidence_copy'] ?? array() );

		return array(
			'title'          => (string) ( $copy['title'] ?? __( 'Strengthening homework oversight', 'ai-risk-benchmark' ) ),
			'score'          => $readiness,
			'impact_heading' => (string) ( $copy['impact_heading'] ?? __( 'Families with weaker homework oversight often report:', 'ai-risk-benchmark' ) ),
			'impact_items'   => (array) ( $copy['impact_items'] ?? array() ),
			'improve_heading'=> (string) ( $copy['improve_heading'] ?? __( 'Practical next steps:', 'ai-risk-benchmark' ) ),
			'improve_items'  => (array) ( $copy['improve_items'] ?? array() ),
		);
	}

	/**
	 * Tier-specific non-commercial next steps.
	 *
	 * @param string               $tier Journey tier.
	 * @param array<string, mixed> $cfg  Config.
	 * @return array<string, mixed>
	 */
	private static function next_steps( string $tier, array $cfg ): array {
		$blocks = (array) ( $cfg['journey_next_steps'] ?? array() );
		$block  = (array) ( $blocks[ $tier ] ?? $blocks['medium'] ?? array() );

		return array(
			'hero_heading'   => (string) ( $cfg['hero_next_step_heading'] ?? __( 'Your next step', 'ai-risk-benchmark' ) ),
			'hero'           => array(
				'key'              => (string) ( $block['cta_key'] ?? 'parent_resources' ),
				'title'            => (string) ( $block['title'] ?? __( 'Suggested next steps', 'ai-risk-benchmark' ) ),
				'body'             => (string) ( $block['intro'] ?? '' ),
				'understand_items' => (array) ( $block['items'] ?? array() ),
				'cta_text'         => (string) ( $block['cta_text'] ?? __( 'Get parent guides', 'ai-risk-benchmark' ) ),
			),
			'resource_links' => (array) ( $cfg['resource_links'] ?? array() ),
		);
	}
}
