<?php
/**
 * Lessons in the block editor.
 *
 * A lesson (the resource post type) keeps its content in post meta, which the
 * theme's single-resource.php renders. In WordPress 7.1 the meta boxes that
 * edited it sit in a collapsed drawer under the canvas, so an editor opened a
 * lesson to an empty page. Now the canvas holds the Lesson plan block
 * (src/blocks/lesson-plan), which edits that meta in the lesson's own order,
 * and the sidebar holds a Lesson details panel. The block saves nothing to the
 * page: the meta stays the one source the page, the deck sync and the debate
 * section all read. The old boxes remain for the classic editor only.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** The block, as it sits in a lesson's content: locked, so it cannot be deleted by accident. */
function aiad_lesson_plan_block_markup(): string {
	return '<!-- wp:aiad/lesson-plan {"lock":{"remove":true}} /-->';
}

/* A new lesson starts with the block. */
add_filter(
	'register_post_type_args',
	static function ( array $args, string $post_type ): array {
		if ( 'resource' === $post_type ) {
			$args['template'] = array( array( 'aiad/lesson-plan', array( 'lock' => array( 'remove' => true ) ) ) );
		}
		return $args;
	},
	10,
	2
);

/*
 * An existing lesson gets the block when the editor loads it. Added to what the
 * editor is sent rather than inserted by script, so opening a lesson does not
 * mark it as changed; the block is saved into the content the first time the
 * lesson is saved.
 */
add_filter(
	'rest_prepare_resource',
	static function ( WP_REST_Response $response, WP_Post $post, WP_REST_Request $request ): WP_REST_Response {
		if ( 'edit' !== $request['context'] ) {
			return $response;
		}
		$data = $response->get_data();
		if ( ! isset( $data['content']['raw'] ) || false !== strpos( (string) $data['content']['raw'], '<!-- wp:aiad/lesson-plan' ) ) {
			return $response;
		}
		$raw                     = trim( (string) $data['content']['raw'] );
		$data['content']['raw']  = ( '' === $raw ? '' : $raw . "\n\n" ) . aiad_lesson_plan_block_markup();
		$response->set_data( $data );
		return $response;
	},
	10,
	3
);

/*
 * What the block and the panel need to know, in the editor's settings:
 * which lessons take their steps, preparation and debate from SlideForge (so
 * the editor can say those fields are replaced by the next export), and the
 * option lists the old boxes held.
 */
add_filter(
	'block_editor_settings_all',
	static function ( array $settings, WP_Block_Editor_Context $context ): array {
		if ( ! $context->post || 'resource' !== $context->post->post_type ) {
			return $settings;
		}
		$synced = array();
		$file   = get_template_directory() . '/assets/lessons/2027/lessons.json';
		if ( is_readable( $file ) ) {
			$lessons = json_decode( (string) file_get_contents( $file ), true );
			if ( is_array( $lessons ) ) {
				$synced = array_values( array_filter( array_map( static fn( $l ) => (string) ( $l['wp'] ?? '' ), $lessons ) ) );
			}
		}
		$settings['aiadLesson'] = array(
			'slideforge' => in_array( $context->post->post_name, $synced, true ),
			'keyStages'  => function_exists( 'aiad_key_stage_options' ) ? aiad_key_stage_options() : array(),
		);
		return $settings;
	},
	10,
	2
);

/**
 * The lesson's introduction: whatever the content holds besides the Lesson plan
 * block, rendered. Empty when there is none, so the page can leave the
 * introduction out rather than print an empty wrapper around the block's
 * comment.
 *
 * @param int $post_id Lesson ID.
 * @return string Rendered HTML.
 */
function aiad_lesson_intro_html( int $post_id ): string {
	$html = apply_filters( 'the_content', (string) get_post_field( 'post_content', $post_id ) );
	return '' === trim( wp_strip_all_tags( (string) $html ) ) ? '' : (string) $html;
}
