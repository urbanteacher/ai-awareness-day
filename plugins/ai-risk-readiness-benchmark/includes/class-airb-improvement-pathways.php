<?php
/**
 * Guided improvement pathways — links weak benchmark scores to hub resources.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds Level 2 "Learn how to improve this score" payloads per role.
 */
class AIRB_Improvement_Pathways {

	private const WEAK_READINESS_MAX = 70;
	private const WEAK_DEPENDENCY_MIN = 40;
	private const MAX_BLOCKS         = 4;

	/**
	 * Build guided improvement section for results.
	 *
	 * @param string               $role    Role slug.
	 * @param array<string, mixed> $results Scored + enriched results.
	 * @return array<string, mixed>
	 */
	public static function build( string $role, array $results ): array {
		$cfg    = AIRB_Defaults::improvement_hub_config();
		$labels = (array) ( $cfg['labels'] ?? array() );
		$blocks = self::blocks_for_role( $role, $results, $cfg );

		usort(
			$blocks,
			static function ( $a, $b ) {
				return ( $a['sort_key'] ?? 0 ) <=> ( $b['sort_key'] ?? 0 );
			}
		);

		$blocks = array_slice( $blocks, 0, self::MAX_BLOCKS );
		foreach ( $blocks as &$block ) {
			unset( $block['sort_key'] );
		}
		unset( $block );

		$out = array(
			'heading' => (string) ( $labels['heading'] ?? __( 'Learn how to improve this score', 'ai-risk-benchmark' ) ),
			'intro'   => (string) ( $labels['intro'] ?? '' ),
			'blocks'  => $blocks,
		);

		if ( 'leader' === $role && ! empty( $cfg['leader_consultation'] ) ) {
			$out['consultation'] = self::consultation_block( (array) $cfg['leader_consultation'] );
		}

		return $out;
	}

	/**
	 * @param string               $role    Role.
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Hub config.
	 * @return array<int, array<string, mixed>>
	 */
	private static function blocks_for_role( string $role, array $results, array $cfg ): array {
		$role_cfg = (array) ( $cfg['roles'][ $role ] ?? array() );
		if ( ! $role_cfg ) {
			return array();
		}

		$blocks = array();
		$domains = (array) ( $results['domain_scores'] ?? array() );

		foreach ( (array) ( $role_cfg['domains'] ?? array() ) as $slug => $pillar ) {
			$dom = (array) ( $domains[ $slug ] ?? array() );
			if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
				continue;
			}
			$readiness = (int) round( (float) ( $dom['readiness_percentage'] ?? 0 ) );
			if ( $readiness >= self::WEAK_READINESS_MAX ) {
				continue;
			}
			$blocks[] = self::format_block( (array) $pillar, $readiness, 'readiness' );
		}

		foreach ( (array) ( $role_cfg['metrics'] ?? array() ) as $metric_slug => $pillar ) {
			$pillar = (array) $pillar;
			$score  = self::metric_value( $role, $metric_slug, $results, $domains );
			if ( null === $score ) {
				continue;
			}
			$type = (string) ( $pillar['score_type'] ?? 'readiness' );
			if ( 'dependency' === $type && $score < self::WEAK_DEPENDENCY_MIN ) {
				continue;
			}
			if ( 'readiness' === $type && $score >= self::WEAK_READINESS_MAX ) {
				continue;
			}
			if ( 'risk' === $type && $score < ( 100 - self::WEAK_READINESS_MAX ) ) {
				continue;
			}
			$blocks[] = self::format_block( $pillar, $score, $type );
		}

		if ( 'parent' === $role ) {
			$blocks = array_merge( $blocks, self::parent_display_blocks( $results, $role_cfg ) );
		}

		return $blocks;
	}

	/**
	 * @param string               $role        Role.
	 * @param string               $metric_slug Metric key.
	 * @param array<string, mixed> $results     Results.
	 * @param array<string, mixed> $domains     Domain scores.
	 */
	private static function metric_value( string $role, string $metric_slug, array $results, array $domains ): ?int {
		if ( 'ai_dependency' === $metric_slug ) {
			return isset( $results['dependency_index'] ) ? (int) $results['dependency_index'] : null;
		}
		if ( 'human_oversight_ratio' === $metric_slug ) {
			if ( isset( $results['human_oversight_ratio'] ) ) {
				return (int) $results['human_oversight_ratio'];
			}
			$dom = (array) ( $domains['human_oversight'] ?? array() );
			return (int) round( (float) ( $dom['readiness_percentage'] ?? 0 ) );
		}
		return null;
	}

	/**
	 * @param array<string, mixed> $results  Results.
	 * @param array<string, mixed> $role_cfg Role hub config.
	 * @return array<int, array<string, mixed>>
	 */
	private static function parent_display_blocks( array $results, array $role_cfg ): array {
		$blocks  = array();
		$display = (array) ( $results['parent_display_domains'] ?? array() );
		$map     = (array) ( $role_cfg['parent_domains'] ?? array() );

		foreach ( $map as $slug => $pillar ) {
			$dom = (array) ( $display[ $slug ] ?? array() );
			if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
				continue;
			}
			$is_risk = ( 'risk' === ( $dom['metric_type'] ?? 'score' ) );
			$value   = (int) round( (float) ( $is_risk ? ( $dom['risk_percentage'] ?? 0 ) : ( $dom['readiness_percentage'] ?? 0 ) ) );
			if ( $is_risk ) {
				if ( $value < ( 100 - self::WEAK_READINESS_MAX ) ) {
					continue;
				}
				$blocks[] = self::format_block( (array) $pillar, $value, 'risk' );
			} elseif ( $value >= self::WEAK_READINESS_MAX ) {
				continue;
			} else {
				$blocks[] = self::format_block( (array) $pillar, $value, 'readiness' );
			}
		}

		return $blocks;
	}

	/**
	 * @param array<string, mixed> $pillar Pillar config.
	 * @param int                    $score  Display score.
	 * @param string                 $type   readiness|dependency|risk.
	 * @return array<string, mixed>
	 */
	private static function format_block( array $pillar, int $score, string $type ): array {
		$resources = array();
		foreach ( (array) ( $pillar['resources'] ?? array() ) as $res ) {
			$res = (array) $res;
			$resources[] = array(
				'kind'  => (string) ( $res['kind'] ?? 'read' ),
				'label' => (string) ( $res['label'] ?? '' ),
				'url'   => AIRB_Defaults::hub_page_url( (string) ( $res['path'] ?? '' ) ),
			);
		}

		$sort = $score;
		if ( 'readiness' === $type ) {
			$sort = $score;
		} elseif ( 'dependency' === $type || 'risk' === $type ) {
			$sort = 100 - $score;
		}

		return array(
			'slug'           => (string) ( $pillar['slug'] ?? '' ),
			'label'          => (string) ( $pillar['metric_label'] ?? '' ),
			'score'          => $score,
			'score_type'     => $type,
			'why_heading'    => (string) ( $pillar['why_heading'] ?? __( 'Why this matters', 'ai-risk-benchmark' ) ),
			'why_body'       => (string) ( $pillar['why_body'] ?? '' ),
			'why_risks'      => (array) ( $pillar['why_risks'] ?? array() ),
			'actions_heading'=> (string) ( $pillar['actions_heading'] ?? __( 'Improve your score', 'ai-risk-benchmark' ) ),
			'resources'      => $resources,
			'sort_key'       => $sort,
		);
	}

	/**
	 * @param array<string, mixed> $cfg Consultation config.
	 * @return array<string, mixed>
	 */
	private static function consultation_block( array $cfg ): array {
		return array(
			'title'    => (string) ( $cfg['title'] ?? __( 'Book a free 30-minute AI Readiness Review', 'ai-risk-benchmark' ) ),
			'intro'    => (string) ( $cfg['intro'] ?? '' ),
			'items'    => (array) ( $cfg['items'] ?? array() ),
			'closing'  => (string) ( $cfg['closing'] ?? __( 'No obligation.', 'ai-risk-benchmark' ) ),
			'cta_text' => (string) ( $cfg['cta_text'] ?? __( 'Book your free review', 'ai-risk-benchmark' ) ),
			'cta_url'  => AIRB_Defaults::hub_page_url( (string) ( $cfg['cta_path'] ?? 'contact' ) ),
		);
	}
}
