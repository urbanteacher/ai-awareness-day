<?php
/**
 * Shared admin markup for resource taxonomies (Theme, Session length, Activity type).
 * Used by resource and featured_resource meta boxes for consistent UI and slug-based values.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allowed term slugs for a taxonomy (all terms, including empty-count).
 *
 * @param string $taxonomy Taxonomy slug.
 * @return string[]
 */
function aiad_get_term_slugs_for_taxonomy( string $taxonomy ): array {
	$list = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $list ) || ! is_array( $list ) ) {
		return array();
	}
	return wp_list_pluck( $list, 'slug' );
}

/**
 * Theme (resource_principle): pill radios or single select; values are term slugs.
 *
 * @param int   $post_id Post ID.
 * @param array $args {
 *     @type string $mode        'pills' | 'select_single'.
 *     @type string $input_name  POST field name (slug value).
 *     @type string $select_id   HTML id for select mode.
 *     @type string $description Optional description below control (select mode).
 *     @type string $empty_label Option label for "no theme" in select mode.
 * }
 */
function aiad_render_resource_principle_control( int $post_id, array $args = array() ): void {
	$args = wp_parse_args(
		$args,
		array(
			'mode'        => 'pills',
			'input_name'  => 'aiad_resource_principle',
			'select_id'   => 'aiad_featured_resource_principle',
			'description' => '',
			'empty_label' => '',
		)
	);

	$theme_terms = get_terms(
		array(
			'taxonomy'   => 'resource_principle',
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $theme_terms ) || empty( $theme_terms ) ) {
		echo '<div class="aiad-rd-section"><p class="description">' . esc_html__( 'No themes found. Add themes under Resources → Themes.', 'ai-awareness-day' ) . '</p></div>';
		return;
	}

	$current = wp_get_object_terms( $post_id, 'resource_principle' );
	$current_slug = ( $current && ! is_wp_error( $current ) && ! empty( $current ) ) ? $current[0]->slug : '';

	$theme_slugs = array( 'safe', 'smart', 'creative', 'responsible', 'future' );

	if ( 'select_single' === $args['mode'] ) {
		$empty_label = $args['empty_label'] !== '' ? $args['empty_label'] : __( 'Select a theme (optional)', 'ai-awareness-day' );
		echo '<div class="aiad-rd-section">';
		echo '<strong class="aiad-rd-label">' . esc_html__( 'Theme', 'ai-awareness-day' ) . '</strong>';
		echo '<p><label for="' . esc_attr( $args['select_id'] ) . '" class="screen-reader-text">' . esc_html__( 'Theme', 'ai-awareness-day' ) . '</label>';
		echo '<select id="' . esc_attr( $args['select_id'] ) . '" name="' . esc_attr( $args['input_name'] ) . '" class="widefat">';
		echo '<option value="">' . esc_html( $empty_label ) . '</option>';
		foreach ( $theme_terms as $term ) {
			if ( is_wp_error( $term ) ) {
				continue;
			}
			echo '<option value="' . esc_attr( $term->slug ) . '" ' . selected( $current_slug, $term->slug, false ) . '>' . esc_html( $term->name ) . '</option>';
		}
		echo '</select></p>';
		if ( $args['description'] !== '' ) {
			echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
		}
		echo '</div>';
		return;
	}

	// pills (default)
	echo '<div class="aiad-rd-section"><strong class="aiad-rd-label">' . esc_html__( 'Theme', 'ai-awareness-day' ) . '</strong><div class="aiad-rd-pills">';
	foreach ( $theme_terms as $term ) {
		if ( is_wp_error( $term ) ) {
			continue;
		}
		$pill_class = in_array( $term->slug, $theme_slugs, true ) ? ' aiad-pill--' . $term->slug : '';
		echo '<label class="aiad-pill' . esc_attr( $pill_class ) . '"><input type="radio" name="' . esc_attr( $args['input_name'] ) . '" value="' . esc_attr( $term->slug ) . '" ' . checked( $current_slug, $term->slug, false ) . ' /> ' . esc_html( $term->name ) . '</label> ';
	}
	echo '</div></div>';
}

/**
 * Session length (resource_duration): checkboxes; values are term slugs.
 *
 * @param int   $post_id Post ID.
 * @param array $args {
 *     @type string $input_name  Base name; outputs as name[] for checkboxes.
 *     @type string $description Intro description above checkboxes.
 * }
 */
function aiad_render_resource_duration_control( int $post_id, array $args = array() ): void {
	$args = wp_parse_args(
		$args,
		array(
			'input_name'      => 'aiad_resource_duration',
			'description'     => __( 'Select all slots this resource fits (e.g. both a 5-minute starter and a 20-minute assembly).', 'ai-awareness-day' ),
			'checkbox_block'  => false,
		)
	);

	$name_with_array = $args['input_name'] . '[]';

	$duration_terms = get_terms(
		array(
			'taxonomy'   => 'resource_duration',
			'hide_empty' => false,
		)
	);

	$current_duration = wp_get_object_terms( $post_id, 'resource_duration' );
	$current_slugs    = $current_duration && ! is_wp_error( $current_duration ) ? wp_list_pluck( $current_duration, 'slug' ) : array();

	echo '<div class="aiad-rd-section"><strong class="aiad-rd-label">' . esc_html__( 'Session length', 'ai-awareness-day' ) . '</strong>';
	if ( $args['description'] !== '' ) {
		echo '<p class="description" style="margin:0 0 0.5rem;">' . esc_html( $args['description'] ) . '</p>';
	}
	echo '<div class="aiad-rd-checkboxes">';
	if ( is_wp_error( $duration_terms ) || empty( $duration_terms ) ) {
		echo '<p class="description">' . esc_html__( 'No session lengths found.', 'ai-awareness-day' ) . '</p>';
		echo '</div></div>';
		return;
	}
	foreach ( $duration_terms as $term ) {
		if ( is_wp_error( $term ) ) {
			continue;
		}
		$label   = function_exists( 'aiad_duration_badge_label' ) ? aiad_duration_badge_label( $term ) : $term->name;
		$checked = in_array( $term->slug, $current_slugs, true );
		if ( ! empty( $args['checkbox_block'] ) ) {
			echo '<label style="display:block;"><input type="checkbox" name="' . esc_attr( $name_with_array ) . '" value="' . esc_attr( $term->slug ) . '" ' . checked( $checked, true, false ) . ' /> ' . esc_html( $label ) . '</label>';
		} else {
			echo '<label><input type="checkbox" name="' . esc_attr( $name_with_array ) . '" value="' . esc_attr( $term->slug ) . '" ' . checked( $checked, true, false ) . ' /> ' . esc_html( $label ) . '</label><br>';
		}
	}
	echo '</div></div>';
}

/**
 * Activity type: checkboxes (multi) or single select; values are term slugs.
 *
 * @param int   $post_id Post ID.
 * @param array $args {
 *     @type string $mode        'checkboxes' | 'select_single'.
 *     @type string $input_name  POST base name (slugs).
 *     @type string $select_id   HTML id for select.
 *     @type string $description Optional description (select mode).
 *     @type string $empty_label Empty option label for select.
 * }
 */
function aiad_render_activity_type_control( int $post_id, array $args = array() ): void {
	$args = wp_parse_args(
		$args,
		array(
			'mode'        => 'checkboxes',
			'input_name'  => 'aiad_activity_type',
			'select_id'   => 'aiad_featured_resource_activity',
			'description' => '',
			'empty_label' => '',
		)
	);

	$activity_terms = get_terms(
		array(
			'taxonomy'   => 'activity_type',
			'hide_empty' => false,
		)
	);
	if ( is_wp_error( $activity_terms ) || empty( $activity_terms ) ) {
		echo '<div class="aiad-rd-section"><p class="description">' . esc_html__( 'No activity types found. Add them under Resources → Activity types.', 'ai-awareness-day' ) . '</p></div>';
		return;
	}

	$current_activities = wp_get_object_terms( $post_id, 'activity_type' );

	if ( 'select_single' === $args['mode'] ) {
		$current_slug = '';
		if ( $current_activities && ! is_wp_error( $current_activities ) && ! empty( $current_activities ) ) {
			$current_slug = $current_activities[0]->slug;
		}
		$empty_label = $args['empty_label'] !== '' ? $args['empty_label'] : __( 'Select an activity type (optional)', 'ai-awareness-day' );
		echo '<div class="aiad-rd-section">';
		echo '<strong class="aiad-rd-label">' . esc_html__( 'Activity type', 'ai-awareness-day' ) . '</strong>';
		echo '<p><label for="' . esc_attr( $args['select_id'] ) . '" class="screen-reader-text">' . esc_html__( 'Activity type', 'ai-awareness-day' ) . '</label>';
		echo '<select id="' . esc_attr( $args['select_id'] ) . '" name="' . esc_attr( $args['input_name'] ) . '" class="widefat">';
		echo '<option value="">' . esc_html( $empty_label ) . '</option>';
		foreach ( $activity_terms as $term ) {
			if ( is_wp_error( $term ) ) {
				continue;
			}
			echo '<option value="' . esc_attr( $term->slug ) . '" ' . selected( $current_slug, $term->slug, false ) . '>' . esc_html( $term->name ) . '</option>';
		}
		echo '</select></p>';
		if ( $args['description'] !== '' ) {
			echo '<p class="description">' . esc_html( $args['description'] ) . '</p>';
		}
		echo '</div>';
		return;
	}

	// checkboxes (multi)
	$current_slugs = $current_activities && ! is_wp_error( $current_activities ) ? wp_list_pluck( $current_activities, 'slug' ) : array();
	$name_with_array = $args['input_name'] . '[]';

	echo '<div class="aiad-rd-section"><strong class="aiad-rd-label">' . esc_html__( 'Activity type', 'ai-awareness-day' ) . '</strong><div class="aiad-rd-checkboxes">';
	foreach ( $activity_terms as $term ) {
		if ( is_wp_error( $term ) ) {
			continue;
		}
		$checked = in_array( $term->slug, $current_slugs, true );
		echo '<label><input type="checkbox" name="' . esc_attr( $name_with_array ) . '" value="' . esc_attr( $term->slug ) . '" ' . checked( $checked, true, false ) . ' /> ' . esc_html( $term->name ) . '</label><br>';
	}
	echo '</div></div>';
}
