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

	/**
	 * Seed hub pages if not already done.
	 */
	public static function maybe_seed(): void {
		if ( get_option( self::OPTION ) === 'yes' ) {
			self::maybe_remove_embedded_benchmark();
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
