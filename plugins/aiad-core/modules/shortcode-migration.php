<?php
/**
 * Shortcodes to blocks.
 *
 * The site's tools, survey, certificate showcase and benchmark used to be pasted into content as shortcodes, and each
 * has a block now. The shortcodes are no longer registered, so content that still holds one is converted: once, for
 * every post that has one, and from then on whenever a post is saved with one in it (the seeded timeline entries are
 * written that way). A shortcode becomes the matching block, with its attributes carried over; a block is the same
 * markup, so the page does not change. The post's previous content is kept as a revision first.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode tag => block name.
 *
 * @return array<string, string>
 */
function aiad_shortcode_block_map(): array {
	return array(
		'aiad_buzzwords'              => 'aiad/buzzwords',
		'aiad_certificate_showcase'   => 'aiad/certificate-showcase',
		'aiad_computing_curriculum'   => 'aiad/computing-curriculum',
		'aiad_ict_curriculum'         => 'aiad/ict-curriculum',
		'aiad_llm_explainer'          => 'aiad/llm-explainer',
		'aiad_llm_order_game'         => 'aiad/llm-order-game',
		'aiad_misinformation_detector' => 'aiad/misinformation-detector',
		'aiad_national_survey'        => 'aiad/national-survey',
		'aiad_neu_ai_report'          => 'aiad/neu-ai-report',
		'aiad_risk_academy'           => 'aiad/risk-academy',
		'aiad_speed_quiz'             => 'aiad/speed-quiz',
		'ai_risk_benchmark'           => 'aiad/risk-benchmark',
		'ai_risk_school_dashboard'    => 'aiad/school-dashboard',
	);
}

/**
 * One shortcode's attributes as the block's attributes: names in camelCase, values cast to the block's types, and
 * left out when they are the block's default.
 *
 * @param string               $block Block name.
 * @param array<string,string> $atts  Parsed shortcode attributes.
 * @return array<string, mixed>
 */
function aiad_shortcode_atts_to_block_attrs( string $block, array $atts ): array {
	$type = WP_Block_Type_Registry::get_instance()->get_registered( $block );
	if ( ! $type || empty( $type->attributes ) ) {
		return array();
	}
	$out = array();
	foreach ( $atts as $name => $value ) {
		if ( ! is_string( $name ) ) {
			continue;
		}
		$key = lcfirst( str_replace( ' ', '', ucwords( str_replace( '_', ' ', $name ) ) ) );
		if ( ! isset( $type->attributes[ $key ] ) ) {
			continue;
		}
		$def  = $type->attributes[ $key ];
		$kind = $def['type'] ?? 'string';
		if ( 'boolean' === $kind ) {
			$value = ! in_array( strtolower( (string) $value ), array( '', '0', 'false', 'no', 'off' ), true );
		} elseif ( 'integer' === $kind || 'number' === $kind ) {
			$value = 'integer' === $kind ? (int) $value : (float) $value;
		} else {
			$value = (string) $value;
		}
		if ( array_key_exists( 'default', $def ) && $def['default'] === $value ) {
			continue;
		}
		$out[ $key ] = $value;
	}
	return $out;
}

/**
 * Replace the site's shortcodes in a piece of content with their blocks.
 *
 * @param string $content Post content.
 * @return string
 */
function aiad_convert_shortcodes_to_blocks( string $content ): string {
	if ( false === strpos( $content, '[aiad_' ) && false === strpos( $content, '[ai_risk' ) ) {
		return $content;
	}
	$map   = aiad_shortcode_block_map();
	$tags  = array_keys( $map );
	$regex = get_shortcode_regex( $tags );

	$to_block = static function ( array $m ) use ( $map ): string {
		$tag   = $m[2];
		$block = $map[ $tag ];
		$atts  = shortcode_parse_atts( $m[3] );
		$attrs = aiad_shortcode_atts_to_block_attrs( $block, is_array( $atts ) ? $atts : array() );
		return '<!-- wp:' . $block . ( $attrs ? ' ' . wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) : '' ) . ' /-->';
	};

	// A shortcode inside the Shortcode block: the whole block becomes the block.
	$content = preg_replace_callback(
		'#<!--\s*wp:shortcode\s*-->\s*(' . $regex . ')\s*<!--\s*/wp:shortcode\s*-->#s',
		static function ( array $m ) use ( $to_block ): string {
			// The inner match groups are offset by one; rebuild the array the callback expects.
			return $to_block( array_slice( $m, 1 ) );
		},
		$content
	);
	// A shortcode on its own in classic content.
	return (string) preg_replace_callback( '/' . $regex . '/s', $to_block, $content );
}

/**
 * Convert a post's content as it is saved, so a shortcode pasted or seeded later becomes a block too.
 *
 * @param array<string, mixed> $data Slashed post data.
 * @return array<string, mixed>
 */
function aiad_convert_shortcodes_on_save( array $data ): array {
	if ( empty( $data['post_content'] ) || 'revision' === ( $data['post_type'] ?? '' ) ) {
		return $data;
	}
	$content = wp_unslash( $data['post_content'] );
	$new     = aiad_convert_shortcodes_to_blocks( $content );
	if ( $new !== $content ) {
		$data['post_content'] = wp_slash( $new );
	}
	return $data;
}
add_filter( 'wp_insert_post_data', 'aiad_convert_shortcodes_on_save', 5 );

/**
 * Once: convert the shortcodes in the posts that already exist.
 */
function aiad_migrate_shortcodes_in_existing_posts(): void {
	if ( get_option( 'aiad_shortcodes_converted' ) || wp_installing() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	global $wpdb;
	$ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type NOT IN ('revision','nav_menu_item','attachment') AND ( post_content LIKE '%[aiad\\_%' OR post_content LIKE '%[ai\\_risk%' )" );
	$changed = 0;
	foreach ( $ids as $id ) {
		$post = get_post( (int) $id );
		if ( ! $post ) {
			continue;
		}
		$new = aiad_convert_shortcodes_to_blocks( $post->post_content );
		if ( $new === $post->post_content ) {
			continue;
		}
		// Keep the old content as a revision, then write the new one directly (no save hooks, no filters on the content).
		if ( function_exists( 'wp_save_post_revision' ) && post_type_supports( $post->post_type, 'revisions' ) ) {
			wp_save_post_revision( $post->ID );
		}
		$wpdb->update( $wpdb->posts, array( 'post_content' => $new ), array( 'ID' => $post->ID ) );
		clean_post_cache( $post->ID );
		++$changed;
	}
	update_option( 'aiad_shortcodes_converted', array( 'at' => gmdate( 'c' ), 'posts' => $changed ), false );
}
// After the blocks are registered (init 10) and the seeds have written their content (init 33 to 35).
add_action( 'init', 'aiad_migrate_shortcodes_in_existing_posts', 40 );
