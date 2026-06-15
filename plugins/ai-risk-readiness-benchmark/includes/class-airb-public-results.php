<?php
/**
 * Public (general adult) benchmark results.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds public-facing result payloads.
 */
class AIRB_Public_Results {

	private const FOCUS_MAX = 75;

	/**
	 * @param array<string, mixed> $results Scored results (incl. public_display_domains).
	 * @return array<string, mixed>
	 */
	public static function build( array $results ): array {
		$cfg            = AIRB_Defaults::public_result_config();
		$score          = (int) ( $results['alignment_score'] ?? 0 );
		$domain_rows    = self::domain_rows( $results, $cfg );
		$summary_metrics = self::summary_metrics( $results, $cfg );
		$focus_areas    = self::build_focus_areas( $domain_rows, $cfg );
		$strengths      = AIRB_Public_Copy::strength_statements( $results, $cfg );
		$ui             = AIRB_Public_Copy::resolve_ui( $results, $cfg );
		$share          = self::share_block( $results, $focus_areas, $cfg );

		return array(
			'ui'                   => $ui,
			'summary_metrics'      => $summary_metrics,
			'domain_rows'          => $domain_rows,
			'strengths'            => $strengths,
			'focus_areas'          => $focus_areas,
			'share'                => $share,
			'suppress_improvement' => $score >= 85,
			'resource_links'       => (array) ( $cfg['resource_links'] ?? array() ),
		);
	}

	/**
	 * Three headline metrics for the dashboard grid.
	 *
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<int, array<string, mixed>>
	 */
	public static function summary_metrics( array $results, array $cfg ): array {
		$display = (array) ( $results['public_display_domains'] ?? array() );
		$defs    = (array) ( $cfg['summary_metrics'] ?? array() );
		$out     = array();

		foreach ( $defs as $def ) {
			if ( ! is_array( $def ) ) {
				continue;
			}
			$source = (string) ( $def['source'] ?? $def['slug'] ?? '' );
			$mode   = (string) ( $def['mode'] ?? 'readiness' );
			$pct    = self::metric_value( $display, $source, $mode );
			if ( null === $pct ) {
				continue;
			}
			$slug  = (string) ( $def['slug'] ?? $source );
			$badge = AIRB_Public_Copy::summary_metric_band( $slug, $pct, $mode );
			$out[] = array(
				'slug'  => $slug,
				'label' => (string) ( $def['label'] ?? '' ),
				'value' => $pct,
				'badge' => $badge,
				'mode'  => $mode,
			);
		}

		return $out;
	}

	/**
	 * Five domain score rows for the dashboard breakdown card.
	 *
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<int, array<string, mixed>>
	 */
	public static function domain_rows( array $results, array $cfg ): array {
		$display = (array) ( $results['public_display_domains'] ?? array() );
		$defs    = (array) ( $cfg['display_domains'] ?? array() );
		$out     = array();

		foreach ( $defs as $slug => $def ) {
			if ( ! is_array( $def ) ) {
				continue;
			}
			$pct = self::metric_value( $display, (string) $slug, 'readiness' );
			if ( null === $pct ) {
				continue;
			}
			$badge = AIRB_Public_Copy::domain_badge( $pct );
			$color = (string) ( $def['color'] ?? '#378ADD' );
			$out[] = array(
				'slug'  => (string) $slug,
				'label' => (string) ( $def['label'] ?? $slug ),
				'pct'   => $pct,
				'badge' => $badge,
				'color' => $color,
			);
		}

		return $out;
	}

	/**
	 * @param array<int, array<string, mixed>> $domain_rows Domain rows.
	 * @param array<string, mixed>             $cfg         Config.
	 * @return array<int, array<string, mixed>>
	 */
	private static function build_focus_areas( array $domain_rows, array $cfg ): array {
		$slug_map = (array) ( $cfg['focus_slug_map'] ?? array() );
		$topics   = array();
		foreach ( (array) ( $cfg['focus_topics'] ?? array() ) as $topic ) {
			if ( ! is_array( $topic ) || empty( $topic['slug'] ) ) {
				continue;
			}
			$topics[ (string) $topic['slug'] ] = $topic;
		}

		$weak = array();
		foreach ( $domain_rows as $row ) {
			$pct = (int) ( $row['pct'] ?? 100 );
			if ( $pct >= self::FOCUS_MAX ) {
				continue;
			}
			$source     = (string) ( $row['slug'] ?? '' );
			$focus_slug = (string) ( $slug_map[ $source ] ?? $source );
			$topic      = (array) ( $topics[ $focus_slug ] ?? array() );
			$weak[]     = array(
				'slug'       => $source,
				'focus_slug' => $focus_slug,
				'label'      => (string) ( $topic['label'] ?? $row['label'] ?? '' ),
				'pct'        => $pct,
				'badge'      => (array) ( $row['badge'] ?? AIRB_Public_Copy::domain_badge( $pct ) ),
			);
		}

		usort(
			$weak,
			static function ( array $a, array $b ): int {
				return ( $a['pct'] ?? 0 ) <=> ( $b['pct'] ?? 0 );
			}
		);

		$out = array();
		foreach ( array_slice( $weak, 0, 3 ) as $area ) {
			$out[] = array_merge( $area, AIRB_Public_Copy::focus_block( $area, $cfg ) );
		}

		return $out;
	}

	/**
	 * @param array<string, mixed>             $results     Results.
	 * @param array<int, array<string, mixed>> $focus_areas Focus rows.
	 * @param array<string, mixed>             $cfg         Config.
	 * @return array<string, mixed>
	 */
	private static function share_block( array $results, array $focus_areas, array $cfg ): array {
		$score     = (int) ( $results['alignment_score'] ?? 0 );
		$band      = AIRB_Public_Copy::safety_band( $score );
		$gap_label = '';
		if ( ! empty( $focus_areas[0]['label'] ) ) {
			$gap_label = (string) $focus_areas[0]['label'];
		}

		$headline = sprintf(
			/* translators: %d: alignment score percentage */
			__( 'I scored %d%% on the AI Risk & Readiness Benchmark.', 'ai-risk-benchmark' ),
			$score
		);
		$subline = $band['label'];
		if ( $gap_label ) {
			$subline .= '. ' . sprintf(
				/* translators: %s: weakest domain label */
				__( '%s is my biggest gap.', 'ai-risk-benchmark' ),
				$gap_label
			);
		}
		$subline .= ' ' . __( 'Find out yours →', 'ai-risk-benchmark' );

		return array(
			'headline' => $headline,
			'subline'  => trim( $subline ),
			'score'    => $score,
			'band'     => $band,
		);
	}

	/**
	 * @param array<string, array<string, mixed>> $display Display domains.
	 * @param string                              $slug    Domain slug.
	 * @param string                              $mode    readiness|risk.
	 */
	private static function metric_value( array $display, string $slug, string $mode = 'readiness' ): ?int {
		if ( ! isset( $display[ $slug ] ) || ! is_array( $display[ $slug ] ) ) {
			return null;
		}
		$row = $display[ $slug ];
		if ( empty( $row['questions_answered'] ) ) {
			return null;
		}
		if ( 'risk' === $mode ) {
			return max( 0, min( 100, (int) round( (float) ( $row['risk_percentage'] ?? 0 ) ) ) );
		}
		return max( 0, min( 100, (int) round( (float) ( $row['readiness_percentage'] ?? 0 ) ) ) );
	}
}
