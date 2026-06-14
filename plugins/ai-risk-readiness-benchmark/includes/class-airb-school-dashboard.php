<?php
/**
 * School-wide aggregation and dashboard.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Roll up submissions by school and role.
 */
class AIRB_School_Dashboard {

	/**
	 * Register shortcode and AJAX.
	 */
	public static function register(): void {
		add_shortcode( 'ai_risk_school_dashboard', array( __CLASS__, 'render_shortcode' ) );
		add_action( 'wp_ajax_airb_school_dashboard', array( __CLASS__, 'ajax_lookup' ) );
		add_action( 'wp_ajax_nopriv_airb_school_dashboard', array( __CLASS__, 'ajax_lookup' ) );
	}

	/**
	 * Normalize school name for grouping.
	 *
	 * @param string $name School name.
	 */
	public static function normalize_school( string $name ): string {
		$name = trim( wp_strip_all_tags( $name ) );
		$name = preg_replace( '/\s+/', ' ', $name ) ?? $name;
		return strtolower( $name );
	}

	/**
	 * List schools with submission counts (admin).
	 *
	 * @return array<int, array{school_name:string,submission_count:int,roles_covered:int}>
	 */
	public static function list_schools(): array {
		global $wpdb;
		$table = AIRB_Database::table_name();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			"SELECT school_name, COUNT(*) AS submission_count, COUNT(DISTINCT role) AS roles_covered
			FROM {$table}
			WHERE school_name != ''
			GROUP BY school_name
			ORDER BY submission_count DESC
			LIMIT 200",
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Build school roll-up from stored submissions.
	 *
	 * @param string $school_name School name (case-insensitive match).
	 * @return array<string, mixed>|null
	 */
	public static function get_rollup( string $school_name ): ?array {
		global $wpdb;

		$school_name = trim( $school_name );
		if ( '' === $school_name ) {
			return null;
		}

		$table = AIRB_Database::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE LOWER(school_name) = LOWER(%s) ORDER BY created_at DESC",
				$school_name
			)
		);

		if ( ! $rows ) {
			return null;
		}

		$roles         = AIRB_Defaults::roles();
		$by_role       = array();
		$domain_totals = array();
		$domain_counts = array();

		foreach ( array_keys( AIRB_Defaults::domains() ) as $slug ) {
			$domain_totals[ $slug ] = 0.0;
			$domain_counts[ $slug ] = 0;
		}

		foreach ( $rows as $row ) {
			$role = (string) $row->role;
			if ( ! isset( $by_role[ $role ] ) ) {
				$by_role[ $role ] = array(
					'count'             => 0,
					'alignment_sum'     => 0,
					'dependency_sum'    => 0,
					'risk_levels'       => array(),
					'latest'            => (string) $row->created_at,
				);
			}
			$by_role[ $role ]['count']++;
			$by_role[ $role ]['alignment_sum']  += (int) $row->alignment_score;
			$by_role[ $role ]['dependency_sum'] += (int) $row->dependency_index;
			$by_role[ $role ]['risk_levels'][]     = (string) $row->risk_level;

			$domains = json_decode( (string) $row->domain_scores, true );
			if ( is_array( $domains ) ) {
				foreach ( $domains as $slug => $dom ) {
					if ( ! isset( $domain_totals[ $slug ] ) || empty( $dom['questions_answered'] ) ) {
						continue;
					}
					$domain_totals[ $slug ] += (float) ( $dom['risk_percentage'] ?? 0 );
					$domain_counts[ $slug ]++;
				}
			}
		}

		$role_scores = array();
		$weights     = AIRB_Defaults::role_weights();
		$weight_sum  = 0.0;
		$weighted    = 0.0;

		foreach ( $roles as $slug => $label ) {
			if ( empty( $by_role[ $slug ] ) ) {
				$role_scores[ $slug ] = array(
					'label'           => $label,
					'readiness'       => null,
					'dependency'      => null,
					'submissions'     => 0,
					'status'          => 'missing',
				);
				continue;
			}
			$data    = $by_role[ $slug ];
			$ready = (int) round( $data['alignment_sum'] / $data['count'] );
			$dep   = (int) round( $data['dependency_sum'] / $data['count'] );
			$w     = (float) ( $weights[ $slug ] ?? 1.0 );
			$weight_sum += $w;
			$weighted   += $ready * $w;

			$role_scores[ $slug ] = array(
				'label'       => $label,
				'readiness'    => $ready,
				'dependency'   => $dep,
				'submissions'  => $data['count'],
				'status'       => 'complete',
				'risk_level'   => self::dominant_risk_band( $data['risk_levels'] ),
			);
		}

		$overall_alignment = $weight_sum > 0 ? (int) round( $weighted / $weight_sum ) : 0;
		$overall_risk      = 100 - $overall_alignment;
		$overall_band      = AIRB_Scoring::risk_band( (float) $overall_risk );

		$exposure = array();
		foreach ( AIRB_Defaults::domains() as $slug => $label ) {
			if ( $domain_counts[ $slug ] < 1 ) {
				continue;
			}
			$exposure[] = array(
				'slug'  => $slug,
				'label' => $label,
				'risk'  => round( $domain_totals[ $slug ] / $domain_counts[ $slug ], 1 ),
			);
		}
		usort(
			$exposure,
			static function ( $a, $b ) {
				return $b['risk'] <=> $a['risk'];
			}
		);
		$exposure = array_slice( $exposure, 0, 3 );

		$breakdown = self::exposure_breakdown( $domain_totals, $domain_counts );

		$roles_complete = count(
			array_filter(
				$role_scores,
				static function ( $r ) {
					return ( $r['submissions'] ?? 0 ) > 0;
				}
			)
		);

		return array(
			'school_name'              => $school_name,
			'roles'                    => $role_scores,
			'roles_complete'           => $roles_complete,
			'roles_total'              => count( $roles ),
			'overall_alignment'        => $overall_alignment,
			'overall_risk_level'       => $overall_band,
			'overall_risk_label'       => AIRB_Scoring::display_risk_label( $overall_band, (float) $overall_risk ),
			'key_exposure_areas'       => $exposure,
			'exposure_breakdown'     => $breakdown,
			'total_submissions'        => count( $rows ),
		);
	}

	/**
	 * Full exposure breakdown with simplified risk labels.
	 *
	 * @param array<string, float> $domain_totals Totals.
	 * @param array<string, int>   $domain_counts Counts.
	 * @return array<int, array<string, mixed>>
	 */
	private static function exposure_breakdown( array $domain_totals, array $domain_counts ): array {
		$short = array(
			'ai_dependency'        => __( 'Dependency', 'ai-risk-benchmark' ),
			'human_oversight'      => __( 'Oversight', 'ai-risk-benchmark' ),
			'governance'           => __( 'Governance', 'ai-risk-benchmark' ),
			'privacy'              => __( 'Privacy', 'ai-risk-benchmark' ),
			'safeguarding'         => __( 'Safeguarding', 'ai-risk-benchmark' ),
			'assessment_integrity' => __( 'Assessment', 'ai-risk-benchmark' ),
			'ai_literacy'          => __( 'AI Literacy', 'ai-risk-benchmark' ),
			'safe_adoption'        => __( 'Safe Adoption', 'ai-risk-benchmark' ),
		);
		$rows  = array();
		foreach ( AIRB_Defaults::domains() as $slug => $label ) {
			if ( ( $domain_counts[ $slug ] ?? 0 ) < 1 ) {
				continue;
			}
			$risk = round( $domain_totals[ $slug ] / $domain_counts[ $slug ], 1 );
			$rows[] = array(
				'slug'       => $slug,
				'label'      => $short[ $slug ] ?? $label,
				'risk'       => $risk,
				'band_label' => self::simple_risk_label( $risk ),
			);
		}
		usort( $rows, static fn( $a, $b ) => $b['risk'] <=> $a['risk'] );
		return $rows;
	}

	/**
	 * Simplified High / Medium / Low for school dashboard table.
	 *
	 * @param float $risk_pct Risk percentage.
	 */
	private static function simple_risk_label( float $risk_pct ): string {
		if ( $risk_pct <= 30 ) {
			return __( 'Low', 'ai-risk-benchmark' );
		}
		if ( $risk_pct <= 60 ) {
			return __( 'Medium', 'ai-risk-benchmark' );
		}
		return __( 'High', 'ai-risk-benchmark' );
	}

	/**
	 * Most common risk band in a list.
	 *
	 * @param string[] $levels Risk level slugs.
	 */
	private static function dominant_risk_band( array $levels ): string {
		if ( ! $levels ) {
			return 'low';
		}
		$counts = array_count_values( $levels );
		arsort( $counts );
		return (string) array_key_first( $counts );
	}

	/**
	 * Shortcode: [ai_risk_school_dashboard school="Optional School Name"]
	 *
	 * @param array<string, string>|string $atts Attributes.
	 */
	public static function render_shortcode( $atts = array() ): string {
		AIRB_Shortcode::enqueue_assets();

		$atts = shortcode_atts(
			array(
				'school' => '',
			),
			is_array( $atts ) ? $atts : array(),
			'ai_risk_school_dashboard'
		);

		$school  = sanitize_text_field( (string) ( $atts['school'] ?: ( $_GET['school'] ?? '' ) ) );
		$rollup  = $school ? self::get_rollup( $school ) : null;

		ob_start();
		$airb_school_rollup = $rollup;
		$airb_school_name   = $school;
		include AIRB_PLUGIN_DIR . 'templates/school-dashboard.php';
		return (string) ob_get_clean();
	}

	/**
	 * AJAX school lookup.
	 */
	public static function ajax_lookup(): void {
		if ( ! check_ajax_referer( 'airb_benchmark_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'ai-risk-benchmark' ) ), 403 );
		}

		$school = sanitize_text_field( (string) ( $_POST['school_name'] ?? '' ) );
		$rollup = self::get_rollup( $school );

		if ( ! $rollup ) {
			wp_send_json_error(
				array(
					'message' => __( 'No benchmark results found for this school yet. Complete audits from each stakeholder group first.', 'ai-risk-benchmark' ),
				)
			);
		}

		wp_send_json_success( array( 'rollup' => $rollup ) );
	}
}
