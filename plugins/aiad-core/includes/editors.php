<?php
/**
 * Record editors: the Details panels that are not part of a block.
 *
 * A content type is edited the 7.1 way when its fields are on the editor's own
 * screen: a Details panel in the sidebar and, where the content is structured
 * meta, a block on the canvas (the lesson's Lesson plan block). Types whose
 * content is the post body (timeline entries) need only the panel. Panels are
 * written in src/editors/ and compiled by `npm run build` into build/editors/;
 * the build is committed, as the blocks' is. The shared pieces are in
 * src/shared/record-editor/. See docs/WP71-STANDARDISATION.md.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load the panels in the block editor, for the types that have one.
 *
 * Every panel checks the post type itself, so loading for these screens only
 * saves the bytes on the others.
 */
function aiad_core_enqueue_record_editors(): void {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || ! in_array( $screen->post_type, apply_filters( 'aiad_core_record_editor_post_types', array( 'timeline', 'live_session', 'partner', 'featured_resource', 'ai_tool' ) ), true ) ) {
		return;
	}
	$asset_file = AIAD_CORE_DIR . 'build/editors/index.asset.php';
	if ( ! file_exists( $asset_file ) ) {
		return;
	}
	$asset = require $asset_file;
	wp_enqueue_script( 'aiad-core-record-editors', AIAD_CORE_URL . 'build/editors/index.js', $asset['dependencies'], $asset['version'], true );
	if ( file_exists( AIAD_CORE_DIR . 'build/editors/index.css' ) ) {
		wp_enqueue_style( 'aiad-core-record-editors', AIAD_CORE_URL . 'build/editors/index.css', array( 'wp-components' ), $asset['version'] );
	}
}
add_action( 'enqueue_block_editor_assets', 'aiad_core_enqueue_record_editors' );

/**
 * The nonce the Fetch image button needs (modules/admin/card-image.php), for the
 * types that have one.
 *
 * @param array                    $settings Editor settings.
 * @param WP_Block_Editor_Context $context  The screen being edited.
 * @return array
 */
function aiad_core_image_fetch_settings( array $settings, WP_Block_Editor_Context $context ): array {
	if ( $context->post && in_array( $context->post->post_type, array( 'resource', 'featured_resource' ), true ) ) {
		$settings['aiadImageFetch'] = array( 'nonce' => wp_create_nonce( 'aiad_fetch_card_image' ) );
	}
	return $settings;
}
add_filter( 'block_editor_settings_all', 'aiad_core_image_fetch_settings', 10, 2 );
