<?php
/**
 * Config storage and retrieval.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Benchmark configuration.
 */
class AIRB_Config {

	/**
	 * Seed defaults on first run.
	 */
	public static function maybe_seed_defaults(): void {
		if ( get_option( AIRB_OPTION_CONFIG ) ) {
			self::maybe_upgrade_config();
			return;
		}
		update_option( AIRB_OPTION_CONFIG, AIRB_Defaults::config(), false );
	}

	/**
	 * Merge new default keys into existing saved config (non-destructive).
	 */
	public static function maybe_upgrade_config(): void {
		$config  = get_option( AIRB_OPTION_CONFIG );
		$defaults = AIRB_Defaults::config();
		if ( ! is_array( $config ) ) {
			return;
		}

		$changed = false;
		$top_keys = array( 'framework', 'domain_sources', 'positioning', 'domain_descriptions', 'guidance_refs', 'gateway', 'pathway_offers', 'role_benchmarks', 'signature_metrics', 'after_audit', 'services', 'aad_2027' );
		foreach ( $top_keys as $key ) {
			if ( empty( $config[ $key ] ) && ! empty( $defaults[ $key ] ) ) {
				$config[ $key ] = $defaults[ $key ];
				$changed        = true;
			}
		}

		if ( is_array( $config['positioning'] ?? null ) && is_array( $defaults['positioning'] ?? null ) ) {
			foreach ( $defaults['positioning'] as $sub_key => $sub_val ) {
				if ( empty( $config['positioning'][ $sub_key ] ) && ! empty( $sub_val ) ) {
					$config['positioning'][ $sub_key ] = $sub_val;
					$changed = true;
				}
			}

			$stored_headline = (string) ( $config['positioning']['headline'] ?? '' );
			$inaccurate_headlines = array(
				"The UK's First DfE-Aligned AI Risk & Readiness Benchmark for Schools",
				'The UK\'s First DfE-Aligned AI Risk & Readiness Benchmark for Schools',
			);
			if ( in_array( $stored_headline, $inaccurate_headlines, true ) ) {
				$config['positioning']['headline'] = (string) ( $defaults['positioning']['headline'] ?? '' );
				$changed                           = true;
			}
		}

		if ( (int) ( $config['version'] ?? 0 ) < 4 ) {
			$config['questions'] = AIRB_Questions::all();
			$config['version']   = 4;
			$changed             = true;
		} elseif ( (int) ( $config['version'] ?? 0 ) < (int) ( $defaults['version'] ?? 0 ) ) {
			$config['version'] = (int) $defaults['version'];
			$changed           = true;
		}

		if ( $changed ) {
			update_option( AIRB_OPTION_CONFIG, $config, false );
		}
	}

	/**
	 * Get full config.
	 *
	 * @return array<string, mixed>
	 */
	public static function get(): array {
		self::maybe_upgrade_config();
		$config = get_option( AIRB_OPTION_CONFIG );
		if ( ! is_array( $config ) || empty( $config['questions'] ) ) {
			$config = AIRB_Defaults::config();
			update_option( AIRB_OPTION_CONFIG, $config, false );
		}
		return $config;
	}

	/**
	 * Save config (admin).
	 *
	 * @param array<string, mixed> $config Config array.
	 */
	public static function save( array $config ): void {
		update_option( AIRB_OPTION_CONFIG, $config, false );
	}

	/**
	 * Questions for a role.
	 *
	 * @param string $role Role slug.
	 * @return array<int, array<string, mixed>>
	 */
	public static function questions_for_role( string $role ): array {
		$config = self::get();
		$out    = array();
		foreach ( (array) ( $config['questions'] ?? array() ) as $q ) {
			if ( isset( $q['role'] ) && $q['role'] === $role ) {
				$out[] = $q;
			}
		}
		return $out;
	}

	/**
	 * Public config for front-end (strip internal fields if needed).
	 *
	 * @return array<string, mixed>
	 */
	public static function public_config(): array {
		$config = self::get();

		// Route the static consultation CTA to email instead of a placeholder page.
		$cta = (array) ( $config['consultation_cta'] ?? array() );
		$cta_title = (string) ( $cta['title'] ?? __( 'Book a free consultation', 'ai-risk-benchmark' ) );
		$email = function_exists( 'get_theme_mod' ) ? sanitize_email( (string) get_theme_mod( 'aiad_contact_email', '' ) ) : '';
		if ( ! $email ) {
			$email = 'info@aiawarenessday.co.uk';
		}
		$cta['url'] = 'mailto:' . $email
			. '?subject=' . rawurlencode( sprintf( /* translators: %s: offer name */ __( 'Benchmark follow-up: %s', 'ai-risk-benchmark' ), $cta_title ) )
			. '&body=' . rawurlencode( __( "Hello,\n\nFollowing our AI Risk & Readiness Benchmark, we'd like to book a consultation.\n\nSchool / Trust:\nName:\n\nThank you.", 'ai-risk-benchmark' ) );

		return array(
			'disclaimer'          => (string) ( $config['disclaimer'] ?? '' ),
			'intro'               => (string) ( $config['intro'] ?? '' ),
			'positioning'         => $config['positioning'] ?? array(),
			'framework'           => $config['framework'] ?? array(),
			'domain_sources'      => $config['domain_sources'] ?? array(),
			'domain_descriptions' => $config['domain_descriptions'] ?? array(),
			'roles'                 => AIRB_Defaults::roles(),
			'domains'               => AIRB_Defaults::domains(),
			'domain_colors'         => AIRB_Defaults::domain_colors(),
			'domain_recommendations'=> AIRB_Defaults::domain_recommendations(),
			'role_meta'             => AIRB_Defaults::role_meta(),
			'questions'           => $config['questions'] ?? array(),
			'recommendations'     => $config['recommendations'] ?? array(),
			'guidance_refs'       => $config['guidance_refs'] ?? array(),
			'consultation_cta'    => $cta,
			'role_benchmarks'     => $config['role_benchmarks'] ?? array(),
			'signature_metrics'   => $config['signature_metrics'] ?? array(),
			'after_audit'         => $config['after_audit'] ?? array(),
		);
	}
}
