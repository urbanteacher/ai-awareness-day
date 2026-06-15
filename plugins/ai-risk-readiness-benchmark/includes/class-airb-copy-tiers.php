<?php
/**
 * AIRB_Copy_Tiers
 *
 * Loads and resolves role-specific copy from JSON data files.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * JSON copy registry with typed accessors and legacy config bridge.
 */
class AIRB_Copy_Tiers {

	/** @var array<string, mixed> */
	private $data = array();

	/** @var string */
	private $role = '';

	/** @var array<string, self> */
	private static $instances = array();

	/** Oversight / dependency min-max bands (shared across roles). */
	private const OVERSIGHT_BANDS = array(
		'exemplary' => array( 'min' => 90, 'max' => 100 ),
		'strong'    => array( 'min' => 76, 'max' => 89 ),
		'moderate'  => array( 'min' => 51, 'max' => 75 ),
		'low'       => array( 'min' => 26, 'max' => 50 ),
		'critical'  => array( 'min' => 0, 'max' => 25 ),
	);

	/** Readiness score ranges per role (band key => min/max). */
	private const READINESS_RANGES = array(
		'teacher' => array(
			'emerging'    => array( 0, 39 ),
			'developing'  => array( 40, 59 ),
			'established' => array( 60, 74 ),
			'strong'      => array( 75, 89 ),
			'leading'     => array( 90, 100 ),
		),
		'leader' => array(
			'emerging'    => array( 0, 39 ),
			'developing'  => array( 40, 59 ),
			'established' => array( 60, 74 ),
			'strong'      => array( 75, 89 ),
			'leading'     => array( 90, 100 ),
		),
		'support' => array(
			'emerging'    => array( 0, 39 ),
			'developing'  => array( 40, 59 ),
			'established' => array( 60, 74 ),
			'strong'      => array( 75, 89 ),
			'leading'     => array( 90, 100 ),
		),
		'student' => array(
			'beginning'  => array( 0, 39 ),
			'developing' => array( 40, 54 ),
			'emerging'   => array( 55, 69 ),
			'confident'  => array( 70, 84 ),
			'advanced'   => array( 85, 100 ),
		),
		'parent' => array(
			'just_starting' => array( 0, 39 ),
			'developing'    => array( 40, 54 ),
			'aware'         => array( 55, 69 ),
			'confident'     => array( 70, 84 ),
			'well_prepared' => array( 85, 100 ),
		),
		'public' => array(
			'at_risk'   => array( 0, 29 ),
			'take_care' => array( 30, 54 ),
			'aware'     => array( 55, 69 ),
			'confident' => array( 70, 84 ),
			'advanced'  => array( 85, 100 ),
		),
	);

	/**
	 * @param string $role teacher|leader|student|parent|support|public
	 */
	public static function for_role( string $role ): self {
		if ( ! isset( self::$instances[ $role ] ) ) {
			self::$instances[ $role ] = new self( $role );
		}
		return self::$instances[ $role ];
	}

	public static function flush_cache(): void {
		self::$instances = array();
	}

	/**
	 * Whether JSON data loaded for this role.
	 */
	public function has_data(): bool {
		return ! empty( $this->data ) && ! empty( $this->data['readiness_bands'] ?? $this->data['copy_tiers'] ?? null );
	}

	/**
	 * Raw JSON (translated). Prefer typed accessors.
	 *
	 * @return array<string, mixed>
	 */
	public function raw(): array {
		return $this->data;
	}

	/**
	 * All roles' raw JSON for front-end copy resolution.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function registry_for_js(): array {
		$roles = array( 'teacher', 'leader', 'student', 'parent', 'support', 'public' );
		$out   = array();
		foreach ( $roles as $role ) {
			$raw = self::for_role( $role )->raw();
			if ( ! empty( $raw ) ) {
				$out[ $role ] = $raw;
			}
		}
		return $out;
	}

	/**
	 * Merge JSON copy into legacy PHP tier-file shape for class-airb-defaults.
	 *
	 * @return array<string, mixed>
	 */
	public function to_legacy_overlay(): array {
		if ( ! $this->has_data() ) {
			return array();
		}

		$overlay = array();

		if ( ! empty( $this->data['readiness_bands'] ) ) {
			$overlay['copy_tiers']['readiness'] = $this->readiness_to_legacy();
		}

		foreach ( array( 'oversight', 'dependency', 'bias_readiness' ) as $section ) {
			if ( empty( $this->data[ $section ] ) ) {
				continue;
			}
			$legacy_key = ( 'bias_readiness' === $section ) ? 'bias' : $section;
			$overlay['copy_tiers'][ $legacy_key ] = $this->tier_section_to_legacy( $section );
		}

		if ( ! empty( $this->data['domains'] ) ) {
			$overlay['focus_tiers'] = $this->domains_to_focus_tiers();
		}

		if ( ! empty( $this->data['governance_maturity'] ) && 'leader' === $this->role ) {
			$overlay['copy_tiers']['governance'] = $this->governance_maturity_to_legacy();
		}

		if ( ! empty( $this->data['ai_risk_exposure'] ) && 'leader' === $this->role ) {
			$overlay['copy_tiers']['risk'] = $this->tier_section_to_legacy( 'ai_risk_exposure' );
		}

		if ( ! empty( $this->data['risk'] ) ) {
			$overlay['copy_tiers']['risk'] = $this->tier_section_to_legacy( 'risk' );
		}

		return $overlay;
	}

	/**
	 * Build JSON from legacy PHP tier file (for migration / missing JSON files).
	 *
	 * @param string $role Role slug.
	 * @return array<string, mixed>
	 */
	public static function build_json_from_php( string $role ): array {
		$file = AIRB_PLUGIN_DIR . 'includes/data/' . $role . '-copy-tiers.php';
		if ( ! file_exists( $file ) ) {
			return array();
		}
		/** @var array<string, mixed> $php */
		$php         = require $file;
		$copy_tiers  = (array) ( $php['copy_tiers'] ?? array() );
		$focus_tiers = (array) ( $php['focus_tiers'] ?? array() );
		$ranges      = self::READINESS_RANGES[ $role ] ?? self::READINESS_RANGES['teacher'];

		$json = array(
			'_meta' => array(
				'role'        => $role,
				'version'     => '1.0.0',
				'description' => 'Migrated from ' . $role . '-copy-tiers.php',
			),
		);

		$readiness_bands = array();
		foreach ( $ranges as $key => $bounds ) {
			$legacy = (array) ( $copy_tiers['readiness'][ $key ] ?? array() );
			if ( empty( $legacy ) ) {
				continue;
			}
			$readiness_bands[ $key ] = array_merge(
				array(
					'min'        => $bounds[0],
					'max'        => $bounds[1],
					'label'      => (string) ( $legacy['signal'] ?? $key ),
					'sub_label'  => (string) ( $legacy['signal'] ?? '' ),
					'color_ramp' => self::tone_to_ramp( (string) ( $legacy['tone'] ?? 'neutral' ) ),
					'consequence' => (string) ( $legacy['consequence'] ?? '' ),
					'urgent_action' => null,
				)
			);
		}
		if ( ! empty( $readiness_bands ) ) {
			$json['readiness_bands'] = $readiness_bands;
		}

		foreach ( array( 'oversight', 'dependency', 'bias' ) as $section ) {
			$source_key = ( 'bias' === $section ) ? 'bias' : $section;
			$target_key = ( 'bias' === $section ) ? 'bias_readiness' : $section;
			$tiers      = (array) ( $copy_tiers[ $source_key ] ?? array() );
			if ( empty( $tiers ) ) {
				continue;
			}
			$converted = array();
			foreach ( $tiers as $tier_key => $tier ) {
				if ( ! is_array( $tier ) ) {
					continue;
				}
				$bounds = self::OVERSIGHT_BANDS[ $tier_key ] ?? array( 'min' => 0, 'max' => 100 );
				$converted[ $tier_key ] = array_merge(
					$bounds,
					array(
						'signal'      => (string) ( $tier['signal'] ?? '' ),
						'consequence' => (string) ( $tier['consequence'] ?? '' ),
						'tone'        => (string) ( $tier['tone'] ?? 'neutral' ),
					)
				);
			}
			if ( ! empty( $converted ) ) {
				$json[ $target_key ] = $converted;
			}
		}

		$domains = array();
		foreach ( $focus_tiers as $slug => $levels ) {
			if ( ! is_array( $levels ) ) {
				continue;
			}
			$focus_card = array();
			foreach ( $levels as $level => $block ) {
				if ( ! is_array( $block ) ) {
					continue;
				}
				$focus_card[ $level ] = array(
					'summary' => (string) ( $block['summary'] ?? '' ),
					'impact'  => (array) ( $block['likely_impact'] ?? $block['impact'] ?? array() ),
					'actions' => (array) ( $block['actions'] ?? array() ),
				);
			}
			$domains[ $slug ] = array(
				'label'       => $slug,
				'description' => (string) ( $php['domain_descriptions'][ $slug ] ?? '' ),
				'focus_card'  => $focus_card,
			);
		}
		if ( ! empty( $domains ) ) {
			$json['domains'] = $domains;
		}

		if ( 'leader' === $role ) {
			if ( empty( $json['readiness_bands'] ) ) {
				$json['readiness_bands'] = self::leader_readiness_bands_seed();
			}
			$json = self::merge_teacher_shared_sections( $json );
		}

		return $json;
	}

	/**
	 * Leader readiness bands (sourced from leader_result_config metric_signals + hero labels).
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private static function leader_readiness_bands_seed(): array {
		return array(
			'emerging'    => array(
				'min'           => 0,
				'max'           => 39,
				'label'         => 'Critical · Act now',
				'sub_label'     => 'Critical',
				'color_ramp'    => 'red',
				'consequence'   => 'Your school has significant AI risk exposure. Without leadership action, staff are likely using AI tools without consistent safeguards, oversight, or policy guidance in place.',
				'urgent_action' => 'Prioritise your two weakest domains and brief SLT this term.',
			),
			'developing'  => array(
				'min'           => 40,
				'max'           => 59,
				'label'         => 'Concern · Review needed',
				'sub_label'     => 'Concern',
				'color_ramp'    => 'amber',
				'consequence'   => 'Awareness is growing, but governance and oversight need strengthening before AI use scales.',
				'urgent_action' => 'Address governance gaps before expanding approved AI tools.',
			),
			'established' => array(
				'min'           => 60,
				'max'           => 74,
				'label'         => 'Established',
				'sub_label'     => 'On the right track',
				'color_ramp'    => 'blue',
				'consequence'   => 'Solid foundations are in place — embed consistent practice across all staff groups.',
				'urgent_action' => null,
			),
			'strong'      => array(
				'min'           => 75,
				'max'           => 89,
				'label'         => 'Strong',
				'sub_label'     => 'Strong position',
				'color_ramp'    => 'green',
				'consequence'   => 'Strong readiness — focus on sustaining consistency as AI use evolves.',
				'urgent_action' => null,
			),
			'leading'     => array(
				'min'           => 90,
				'max'           => 100,
				'label'         => 'Leading',
				'sub_label'     => 'Sector benchmark',
				'color_ramp'    => 'green',
				'consequence'   => 'Your school demonstrates mature, governed AI adoption — maintain oversight as tools change.',
				'urgent_action' => null,
			),
		);
	}

	/**
	 * Leader shares oversight/dependency copy with staff roles (no separate tier file keys).
	 *
	 * @param array<string, mixed> $json Partial JSON.
	 * @return array<string, mixed>
	 */
	private static function merge_teacher_shared_sections( array $json ): array {
		$teacher_path = AIRB_PLUGIN_DIR . 'includes/data/copy-tiers-teacher.json';
		if ( ! is_readable( $teacher_path ) ) {
			return $json;
		}
		$teacher = json_decode( (string) file_get_contents( $teacher_path ), true );
		if ( ! is_array( $teacher ) ) {
			return $json;
		}
		foreach ( array( 'oversight', 'dependency' ) as $section ) {
			if ( empty( $json[ $section ] ) && ! empty( $teacher[ $section ] ) ) {
				$json[ $section ] = $teacher[ $section ];
			}
		}
		return $json;
	}

	/**
	 * Write copy-tiers JSON files for roles that do not have one yet (one-time migration from PHP).
	 */
	public static function export_missing_json_files(): void {
		foreach ( array( 'leader', 'student', 'parent', 'support' ) as $role ) {
			$path = AIRB_PLUGIN_DIR . 'includes/data/copy-tiers-' . $role . '.json';
			if ( file_exists( $path ) ) {
				continue;
			}
			$json = self::build_json_from_php( $role );
			if ( empty( $json['readiness_bands'] ) ) {
				continue;
			}
			$encoded = wp_json_encode( $json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
			if ( ! is_string( $encoded ) ) {
				continue;
			}
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			file_put_contents( $path, $encoded . "\n" );
		}
		self::flush_cache();
	}

	/**
	 * @param int $score 0–100
	 * @return array<string, mixed>
	 */
	public function readiness_band( int $score ): array {
		$bands = (array) ( $this->data['readiness_bands'] ?? array() );
		foreach ( $bands as $band ) {
			if ( ! is_array( $band ) ) {
				continue;
			}
			if ( $score >= (int) ( $band['min'] ?? 0 ) && $score <= (int) ( $band['max'] ?? 100 ) ) {
				return $band;
			}
		}
		return $this->empty_band();
	}

	public function readiness_band_key( int $score ): string {
		$bands = (array) ( $this->data['readiness_bands'] ?? array() );
		foreach ( $bands as $key => $band ) {
			if ( ! is_array( $band ) ) {
				continue;
			}
			if ( $score >= (int) ( $band['min'] ?? 0 ) && $score <= (int) ( $band['max'] ?? 100 ) ) {
				return (string) $key;
			}
		}
		return '';
	}

	/**
	 * @return array{signal:string,consequence:string,tone:string}
	 */
	public function oversight( int $pct ): array {
		return $this->resolve_tier( 'oversight', $pct );
	}

	/**
	 * @return array{signal:string,consequence:string,tone:string}
	 */
	public function dependency( int $pct ): array {
		return $this->resolve_tier( 'dependency', $pct );
	}

	/**
	 * @return array{signal:string,consequence:string,tone:string}
	 */
	public function bias_readiness( int $pct ): array {
		return $this->resolve_tier( 'bias_readiness', $pct );
	}

	/**
	 * @param string $domain Domain slug.
	 * @param int    $score  0–100.
	 * @return array<string, mixed>
	 */
	public function domain_focus( string $domain, int $score ): array {
		$domains = (array) ( $this->data['domains'] ?? array() );
		if ( ! isset( $domains[ $domain ] ) || ! is_array( $domains[ $domain ] ) ) {
			return $this->empty_focus();
		}

		$d        = $domains[ $domain ];
		$card     = (array) ( $d['focus_card'] ?? array() );
		$severity = $this->domain_severity( $score );

		if ( isset( $card[ $severity ] ) && is_array( $card[ $severity ] ) ) {
			return array_merge(
				array(
					'label'       => (string) ( $d['label'] ?? $domain ),
					'description' => (string) ( $d['description'] ?? '' ),
					'severity'    => $severity,
					'score'       => $score,
				),
				$card[ $severity ]
			);
		}

		return $this->empty_focus();
	}

	/**
	 * @return array<string, mixed>
	 */
	public function cta( int $score ): array {
		$band_key = $this->readiness_band_key( $score );
		return (array) ( $this->data['cta'][ $band_key ] ?? array() );
	}

	public function strength( string $domain, int $score ): string {
		$strengths = (array) ( $this->data['strengths'] ?? array() );
		foreach ( array( 100, 90, 76 ) as $threshold ) {
			if ( $score >= $threshold ) {
				$key = $domain . '_' . $threshold;
				if ( isset( $strengths[ $key ] ) ) {
					return (string) $strengths[ $key ];
				}
			}
		}
		return '';
	}

	/**
	 * Leader governance maturity band (JSON schema).
	 *
	 * @param int $score 0–100.
	 * @return array<string, mixed>
	 */
	public function governance_maturity( int $score ): array {
		$tiers = (array) ( $this->data['governance_maturity'] ?? array() );
		foreach ( $tiers as $tier ) {
			if ( ! is_array( $tier ) ) {
				continue;
			}
			if ( $score >= (int) ( $tier['min'] ?? 0 ) && $score <= (int) ( $tier['max'] ?? 100 ) ) {
				return $tier;
			}
		}
		return array(
			'label'       => '',
			'signal'      => '',
			'consequence' => '',
			'tone'        => 'neutral',
		);
	}

	public function share_text( int $score ): string {
		$band_key = $this->readiness_band_key( $score );
		return (string) ( $this->data['share'][ $band_key ] ?? '' );
	}

	private function __construct( string $role ) {
		$this->role = $role;
		$this->data = $this->load( $role );
	}

	/**
	 * @return array<string, mixed>
	 */
	private function load( string $role ): array {
		if ( ! self::use_json_copy() ) {
			return array();
		}

		$file = AIRB_PLUGIN_DIR . 'includes/data/copy-tiers-' . $role . '.json';
		if ( ! file_exists( $file ) ) {
			return self::load_from_php_fallback( $role );
		}

		$json = file_get_contents( $file );
		if ( false === $json ) {
			return array();
		}

		$decoded = json_decode( $json, true );
		if ( ! is_array( $decoded ) ) {
			return array();
		}

		return self::translate_tree( $decoded );
	}

	/**
	 * Fallback: build in-memory JSON from legacy PHP tier file when JSON not authored yet.
	 *
	 * @param string $role Role slug.
	 * @return array<string, mixed>
	 */
	private static function load_from_php_fallback( string $role ): array {
		$built = self::build_json_from_php( $role );
		if ( empty( $built['readiness_bands'] ) && empty( $built['copy_tiers'] ) ) {
			return array();
		}
		return self::translate_tree( $built );
	}

	public static function use_json_copy(): bool {
		if ( defined( 'AIRB_USE_JSON_COPY' ) ) {
			return (bool) AIRB_USE_JSON_COPY;
		}
		return true;
	}

	/**
	 * Recursively apply __() to string leaves.
	 *
	 * @param mixed $node Node.
	 * @return mixed
	 */
	private static function translate_tree( $node ) {
		if ( is_string( $node ) ) {
			return __( $node, 'ai-risk-benchmark' );
		}
		if ( ! is_array( $node ) ) {
			return $node;
		}
		$out = array();
		foreach ( $node as $key => $value ) {
			if ( '_meta' === $key ) {
				$out[ $key ] = $value;
				continue;
			}
			$out[ $key ] = self::translate_tree( $value );
		}
		return $out;
	}

	/**
	 * @return array<string, array{signal:string,tone:string,consequence:string}>
	 */
	private function readiness_to_legacy(): array {
		$out   = array();
		$bands = (array) ( $this->data['readiness_bands'] ?? array() );
		foreach ( $bands as $key => $band ) {
			if ( ! is_array( $band ) ) {
				continue;
			}
			$out[ $key ] = array(
				'signal'      => (string) ( $band['label'] ?? $band['sub_label'] ?? $key ),
				'tone'        => self::ramp_to_tone( (string) ( $band['color_ramp'] ?? 'blue' ) ),
				'consequence' => (string) ( $band['consequence'] ?? '' ),
			);
		}
		return $out;
	}

	/**
	 * @param string $section JSON section key.
	 * @return array<string, array<string, mixed>>
	 */
	private function tier_section_to_legacy( string $section ): array {
		$out    = array();
		$tiers  = (array) ( $this->data[ $section ] ?? array() );
		foreach ( $tiers as $key => $tier ) {
			if ( ! is_array( $tier ) ) {
				continue;
			}
			$out[ $key ] = array(
				'signal'      => (string) ( $tier['signal'] ?? '' ),
				'tone'        => (string) ( $tier['tone'] ?? 'neutral' ),
				'consequence' => (string) ( $tier['consequence'] ?? '' ),
				'min'         => isset( $tier['min'] ) ? (int) $tier['min'] : null,
				'max'         => isset( $tier['max'] ) ? (int) $tier['max'] : null,
			);
		}
		return $out;
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	private function domains_to_focus_tiers(): array {
		$out      = array();
		$domains  = (array) ( $this->data['domains'] ?? array() );
		foreach ( $domains as $slug => $domain ) {
			if ( ! is_array( $domain ) ) {
				continue;
			}
			$card = (array) ( $domain['focus_card'] ?? array() );
			$out[ $slug ] = array();
			foreach ( $card as $level => $block ) {
				if ( ! is_array( $block ) ) {
					continue;
				}
				$out[ $slug ][ $level ] = array(
					'summary'       => (string) ( $block['summary'] ?? '' ),
					'likely_impact' => (array) ( $block['impact'] ?? $block['likely_impact'] ?? array() ),
					'actions'       => (array) ( $block['actions'] ?? array() ),
				);
			}
		}

		if ( 'leader' === $this->role ) {
			foreach ( self::leader_domain_slug_aliases() as $json_key => $php_slug ) {
				if ( isset( $out[ $json_key ] ) && ! isset( $out[ $php_slug ] ) ) {
					$out[ $php_slug ] = $out[ $json_key ];
				}
			}
		}

		return $out;
	}

	/**
	 * Map V1 leader JSON domain keys to legacy PHP/scoring slugs.
	 *
	 * @return array<string, string>
	 */
	private static function leader_domain_slug_aliases(): array {
		return array(
			'data_protection_awareness' => 'privacy',
			'governance_consistency'    => 'governance',
			'bias_awareness'            => 'bias_equality',
		);
	}

	/**
	 * @return array<string, array{signal:string,tone:string,consequence:string}>
	 */
	private function governance_maturity_to_legacy(): array {
		$out   = array();
		$tiers = (array) ( $this->data['governance_maturity'] ?? array() );
		$map   = array(
			'critical'   => 'not_in_place',
			'developing' => 'gaps',
			'partial'    => 'partial',
			'mostly'     => 'mostly',
			'full'       => 'full',
		);
		foreach ( $tiers as $key => $tier ) {
			if ( ! is_array( $tier ) ) {
				continue;
			}
			$legacy_key = $map[ $key ] ?? $key;
			$out[ $legacy_key ] = array(
				'signal'      => (string) ( $tier['label'] ?? $tier['signal'] ?? '' ),
				'tone'        => (string) ( $tier['tone'] ?? 'neutral' ),
				'consequence' => (string) ( $tier['consequence'] ?? '' ),
			);
		}
		return $out;
	}

	/**
	 * @return array{signal:string,consequence:string,tone:string}
	 */
	private function resolve_tier( string $section, int $value ): array {
		$tiers = (array) ( $this->data[ $section ] ?? array() );
		foreach ( $tiers as $tier ) {
			if ( ! is_array( $tier ) ) {
				continue;
			}
			if ( $value >= (int) ( $tier['min'] ?? 0 ) && $value <= (int) ( $tier['max'] ?? 100 ) ) {
				return array(
					'signal'      => (string) ( $tier['signal'] ?? '' ),
					'consequence' => (string) ( $tier['consequence'] ?? '' ),
					'tone'        => (string) ( $tier['tone'] ?? 'neutral' ),
				);
			}
		}
		return array(
			'signal'      => '',
			'consequence' => '',
			'tone'        => 'neutral',
		);
	}

	private function domain_severity( int $score ): string {
		if ( $score <= 34 ) {
			return 'critical';
		}
		if ( $score <= 59 ) {
			return 'high';
		}
		return 'moderate';
	}

	/**
	 * @return array<string, mixed>
	 */
	private function empty_band(): array {
		return array(
			'label'         => '',
			'sub_label'     => '',
			'color_ramp'    => 'gray',
			'consequence'   => '',
			'urgent_action' => null,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function empty_focus(): array {
		return array(
			'label'       => '',
			'description' => '',
			'severity'    => '',
			'score'       => 0,
			'summary'     => '',
			'impact'      => array(),
			'actions'     => array(),
		);
	}

	private static function tone_to_ramp( string $tone ): string {
		$map = array(
			'urgent'   => 'red',
			'critical' => 'red',
			'warning'  => 'amber',
			'neutral'  => 'blue',
			'positive' => 'green',
		);
		return $map[ $tone ] ?? 'blue';
	}

	private static function ramp_to_tone( string $ramp ): string {
		$map = array(
			'red'    => 'urgent',
			'amber'  => 'warning',
			'blue'   => 'neutral',
			'green'  => 'positive',
			'purple' => 'positive',
			'teal'   => 'positive',
			'gray'   => 'neutral',
		);
		return $map[ $ramp ] ?? 'neutral';
	}
}
