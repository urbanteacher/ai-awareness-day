<?php
/**
 * National Survey: The survey_response post type, where each response is stored with _survey_* meta.
 *
 * Part of the survey module (modules/survey.php).
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Post type
// ---------------------------------------------------------------------------

function aiad_register_survey_response_post_type(): void {
	register_post_type(
		'survey_response',
		array(
			'labels'              => array(
				'name'          => __( 'Survey Responses', 'aiad-core' ),
				'singular_name' => __( 'Survey Response', 'aiad-core' ),
				'all_items'     => __( 'All Survey Responses', 'aiad-core' ),
				'search_items'  => __( 'Search Responses', 'aiad-core' ),
				'edit_item'     => __( 'View Response', 'aiad-core' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'menu_icon'           => 'dashicons-chart-bar',
			'supports'            => array( 'title' ),
			'capability_type'     => 'post',
			'capabilities'        => array( 'create_posts' => false ),
			'map_meta_cap'        => true,
		)
	);
}
add_action( 'init', 'aiad_register_survey_response_post_type' );
