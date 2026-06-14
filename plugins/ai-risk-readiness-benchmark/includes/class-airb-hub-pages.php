<?php
/**
 * Seeds resource hub pages linked from benchmark improvement pathways.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates editable WordPress pages for the improvement hub (once per site).
 */
class AIRB_Hub_Pages {

	private const OPTION = 'airb_hub_pages_seeded_v1';

	private const PATCH_NO_BENCHMARK = 'airb_hub_pages_no_benchmark_v1';

	private const PATCH_INTERVENTION = 'airb_hub_intervention_v19';

	/**
	 * Register front-end hooks (interactive checklist assets).
	 */
	public static function register(): void {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_hub_assets' ) );
	}

	/**
	 * Enqueue checklist + contextual interest form on intervention hub pages.
	 */
	public static function enqueue_hub_assets(): void {
		if ( ! function_exists( 'is_page' ) || ! is_page() ) {
			return;
		}

		$post = get_queried_object();
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		if ( ! in_array( $post->post_name, AIRB_Hub_Content::intervention_slugs(), true ) ) {
			return;
		}

		wp_enqueue_style(
			'airb-front',
			AIRB_PLUGIN_URL . 'public/css/airb-front.css',
			array(),
			AIRB_VERSION
		);
		wp_enqueue_style(
			'airb-hub-interest',
			AIRB_PLUGIN_URL . 'public/css/airb-hub-interest.css',
			array( 'airb-front' ),
			AIRB_VERSION
		);
		wp_enqueue_style(
			'airb-checklist',
			AIRB_PLUGIN_URL . 'public/css/airb-checklist.css',
			array(),
			AIRB_VERSION
		);
		wp_enqueue_script(
			'airb-hub-interest',
			AIRB_PLUGIN_URL . 'public/js/airb-hub-interest.js',
			array(),
			AIRB_VERSION,
			true
		);
		wp_enqueue_script(
			'airb-checklist',
			AIRB_PLUGIN_URL . 'public/js/airb-checklist.js',
			array( 'airb-hub-interest' ),
			AIRB_VERSION,
			true
		);

		$config = AIRB_Hub_Interest::client_config( $post );
		wp_localize_script(
			'airb-hub-interest',
			'airbHubInterest',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'airb_benchmark_nonce' ),
				'config'  => $config ? $config : array(),
				'i18n'    => array(
					'emailInvalid'     => __( 'Please enter a valid email address.', 'ai-risk-benchmark' ),
					'interestRequired' => __( 'Please select at least one option.', 'ai-risk-benchmark' ),
					'error'            => __( 'Something went wrong. Please try again.', 'ai-risk-benchmark' ),
					'sending'          => __( 'Sending…', 'ai-risk-benchmark' ),
					'loading'          => __( 'Loading your benchmark context…', 'ai-risk-benchmark' ),
				),
			)
		);
		wp_localize_script(
			'airb-checklist',
			'airbHubChecklist',
			array(
				'contactEmail' => AIRB_Hub_Content::campaign_contact_email(),
				'i18n'         => array(
					'progressLabel'   => __( 'Completed', 'ai-risk-benchmark' ),
					'emailSlt'        => __( 'Email my checklist to my SLT', 'ai-risk-benchmark' ),
					'emailTeam'       => __( 'Request support from AI Awareness Day', 'ai-risk-benchmark' ),
					'reset'           => __( 'Reset', 'ai-risk-benchmark' ),
					'ragGreen'        => __( 'On track', 'ai-risk-benchmark' ),
					'ragAmber'        => __( 'Some gaps remain', 'ai-risk-benchmark' ),
					'ragRed'          => __( 'Significant work required', 'ai-risk-benchmark' ),
					'emailSubject'    => __( 'AI readiness checklist', 'ai-risk-benchmark' ),
					'emailIntro'      => __( 'Here is my progress against this AI readiness checklist:', 'ai-risk-benchmark' ),
					'emailProgress'   => __( 'Completed', 'ai-risk-benchmark' ),
					'emailStatus'     => __( 'Status', 'ai-risk-benchmark' ),
					'emailTeamClosing'=> __( 'Please contact me for guidance and support on the gaps above, and how AI Awareness Day can help our school.', 'ai-risk-benchmark' ),
				),
			)
		);
	}

	/**
	 * @deprecated Use enqueue_hub_assets().
	 */
	public static function enqueue_checklist_assets(): void {
		self::enqueue_hub_assets();
	}

	/**
	 * Seed hub pages if not already done.
	 */
	public static function maybe_seed(): void {
		if ( get_option( self::OPTION ) === 'yes' ) {
			self::maybe_remove_embedded_benchmark();
			self::maybe_upgrade_intervention_content();
			return;
		}

		if ( ! function_exists( 'wp_insert_post' ) ) {
			return;
		}

		$pages = AIRB_Defaults::hub_page_definitions();
		foreach ( $pages as $page ) {
			self::ensure_page( (array) $page );
		}

		update_option( self::OPTION, 'yes', false );
		self::maybe_remove_embedded_benchmark();
		self::maybe_upgrade_intervention_content();
	}

	/**
	 * Hub pages are resource articles — remove legacy full benchmark embeds.
	 */
	public static function maybe_remove_embedded_benchmark(): void {
		if ( get_option( self::PATCH_NO_BENCHMARK ) === 'yes' ) {
			return;
		}

		if ( ! function_exists( 'get_page_by_path' ) || ! function_exists( 'wp_update_post' ) ) {
			return;
		}

		foreach ( AIRB_Defaults::hub_page_definitions() as $def ) {
			$slug = sanitize_title( (string) ( $def['slug'] ?? '' ) );
			if ( ! $slug ) {
				continue;
			}

			$existing = get_page_by_path( $slug, OBJECT, 'page' );
			if ( ! $existing instanceof WP_Post ) {
				continue;
			}

			$content = (string) $existing->post_content;
			if ( false === strpos( $content, '[ai_risk_benchmark]' ) ) {
				continue;
			}

			$content = (string) preg_replace(
				'/<!--\s*wp:shortcode\s*-->\s*\[ai_risk_benchmark\]\s*<!--\s*\/wp:shortcode\s*-->\s*/i',
				'',
				$content
			);
			$content = str_replace( '[ai_risk_benchmark]', '', $content );

			wp_update_post(
				array(
					'ID'           => (int) $existing->ID,
					'post_content' => trim( $content ),
				)
			);
		}

		update_option( self::PATCH_NO_BENCHMARK, 'yes', false );
	}

	/**
	 * Upgrade hub pages to latest DfE-aligned intervention framework content.
	 */
	public static function maybe_upgrade_intervention_content(): void {
		if ( get_option( self::PATCH_INTERVENTION ) === 'yes' ) {
			return;
		}

		if ( ! function_exists( 'get_page_by_path' ) || ! function_exists( 'wp_update_post' ) ) {
			return;
		}

		$marker = AIRB_Hub_Content::CONTENT_VERSION_MARKER;

		foreach ( AIRB_Defaults::hub_page_definitions() as $def ) {
			$slug = sanitize_title( (string) ( $def['slug'] ?? '' ) );
			if ( ! $slug || ! in_array( $slug, AIRB_Hub_Content::intervention_slugs(), true ) ) {
				continue;
			}

			$content  = (string) ( $def['content'] ?? '' );
			$existing = get_page_by_path( $slug, OBJECT, 'page' );

			if ( ! $existing instanceof WP_Post ) {
				self::ensure_page( (array) $def );
				continue;
			}

			$current = (string) $existing->post_content;
			if ( false !== strpos( $current, $marker ) ) {
				continue;
			}

			wp_update_post(
				array(
					'ID'           => (int) $existing->ID,
					'post_title'   => (string) ( $def['title'] ?? $existing->post_title ),
					'post_excerpt' => (string) ( $def['excerpt'] ?? $existing->post_excerpt ),
					'post_content' => $content,
				)
			);
		}

		update_option( self::PATCH_INTERVENTION, 'yes', false );
	}

	/**
	 * @param array<string, mixed> $def Page definition.
	 */
	private static function ensure_page( array $def ): void {
		$slug = sanitize_title( (string) ( $def['slug'] ?? '' ) );
		if ( ! $slug ) {
			return;
		}

		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $existing instanceof WP_Post ) {
			return;
		}

		$title   = (string) ( $def['title'] ?? $slug );
		$excerpt = (string) ( $def['excerpt'] ?? '' );
		$content = (string) ( $def['content'] ?? '' );

		wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_excerpt' => $excerpt,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_author'  => 1,
			),
			true
		);
	}
}
