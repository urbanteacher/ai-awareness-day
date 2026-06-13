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

	/**
	 * Seed hub pages if not already done.
	 */
	public static function maybe_seed(): void {
		if ( get_option( self::OPTION ) === 'yes' ) {
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
