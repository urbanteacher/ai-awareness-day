<?php
/**
 * National Survey: Admin: the notice and action that add the survey to the timeline.
 *
 * Part of the survey module (modules/survey.php).
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Admin: CSV export
// ---------------------------------------------------------------------------

function aiad_survey_admin_notices(): void {
	$screen = get_current_screen();
	if ( ! $screen || $screen->post_type !== 'survey_response' || $screen->base !== 'edit' ) {
		return;
	}

	// Success / error feedback after timeline action
	$timeline_result = isset( $_GET['aiad_timeline'] ) ? sanitize_key( $_GET['aiad_timeline'] ) : '';
	if ( $timeline_result === 'created' ) {
		$timeline_id  = isset( $_GET['tid'] ) ? (int) $_GET['tid'] : 0;
		$edit_url     = $timeline_id ? get_edit_post_link( $timeline_id ) : '';
		echo '<div class="notice notice-success is-dismissible"><p>';
		esc_html_e( 'Timeline entry created successfully.', 'aiad-core' );
		if ( $edit_url ) {
			echo ' <a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit entry →', 'aiad-core' ) . '</a>';
		}
		echo '</p></div>';
	} elseif ( $timeline_result === 'exists' ) {
		$timeline_id = isset( $_GET['tid'] ) ? (int) $_GET['tid'] : 0;
		$edit_url    = $timeline_id ? get_edit_post_link( $timeline_id ) : '';
		echo '<div class="notice notice-warning is-dismissible"><p>';
		esc_html_e( 'A survey timeline entry already exists.', 'aiad-core' );
		if ( $edit_url ) {
			echo ' <a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit entry →', 'aiad-core' ) . '</a>';
		}
		echo '</p></div>';
	} elseif ( $timeline_result === 'error' ) {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Could not create the timeline entry. Please try again.', 'aiad-core' ) . '</p></div>';
	}

	$survey_exists = aiad_survey_timeline_entry_exists();
	?>
	<div class="aiad-survey-export-bar" style="margin:16px 0 0; display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
		<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'aiad_export_survey_csv', '_wpnonce' => wp_create_nonce( 'aiad_export_survey_csv' ) ), admin_url( 'admin-post.php' ) ) ); ?>"
			class="button button-primary">
			<?php esc_html_e( 'Export all responses to CSV', 'aiad-core' ); ?>
		</a>
		<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'aiad_survey_add_to_timeline', '_wpnonce' => wp_create_nonce( 'aiad_survey_add_to_timeline' ) ), admin_url( 'admin-post.php' ) ) ); ?>"
			class="button<?php echo $survey_exists ? ' disabled' : ''; ?>"
			<?php echo $survey_exists ? 'aria-disabled="true"' : ''; ?>>
			<?php echo $survey_exists ? esc_html__( 'Survey entry already exists', 'aiad-core' ) : esc_html__( 'Add survey to Timeline', 'aiad-core' ); ?>
		</a>
	</div>
	<?php
}
add_action( 'admin_notices', 'aiad_survey_admin_notices' );

/**
 * Check whether a timeline entry for the national survey already exists.
 */
function aiad_survey_timeline_entry_exists(): bool {
	$existing = get_posts( array(
		'post_type'      => 'timeline',
		'post_status'    => array( 'publish', 'draft', 'pending', 'future' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'   => '_aiad_timeline_auto_type',
				'value' => 'national_survey_2026',
			),
		),
	) );
	return ! empty( $existing );
}

/**
 * Get the existing survey timeline entry ID (0 if none).
 */
function aiad_survey_get_timeline_entry_id(): int {
	$existing = get_posts( array(
		'post_type'      => 'timeline',
		'post_status'    => array( 'publish', 'draft', 'pending', 'future' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'   => '_aiad_timeline_auto_type',
				'value' => 'national_survey_2026',
			),
		),
	) );
	return ! empty( $existing ) ? (int) $existing[0] : 0;
}

/**
 * Admin-post handler: create the survey timeline entry.
 */
function aiad_survey_add_to_timeline_handler(): void {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Permission denied.', 'aiad-core' ) );
	}
	check_admin_referer( 'aiad_survey_add_to_timeline' );

	$redirect = admin_url( 'edit.php?post_type=survey_response' );

	// Return early if entry already exists
	$existing_id = aiad_survey_get_timeline_entry_id();
	if ( $existing_id > 0 ) {
		wp_safe_redirect( add_query_arg( array( 'aiad_timeline' => 'exists', 'tid' => $existing_id ), $redirect ) );
		exit;
	}

	// Try to find the survey page automatically (page using the shortcode)
	$survey_page = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		's'              => 'aiad_national_survey',
	) );
	$survey_url = ! empty( $survey_page ) ? get_permalink( $survey_page[0] ) : home_url( '/survey/' );

	// Create the timeline entry via the shared helper
	$post_id = aiad_create_timeline_entry( array(
		'title'      => __( '📋 National Survey — Share your AI Awareness Day experience', 'aiad-core' ),
		'content'    => '<p>' . esc_html__( 'We want to hear from you! Tell us how AI Awareness Day 2026 went for your school or organisation, and share your ideas for 2027. The survey takes around 3 minutes.', 'aiad-core' ) . '</p>',
		'auto_type'  => 'national_survey_2026',
		'icon'       => 'announcement',
		'related_id' => 0,
		'link_url'   => (string) $survey_url,
		'link_label' => __( 'Take the survey →', 'aiad-core' ),
		'post_name'  => 'national-survey-2026',
	) );

	if ( ! $post_id ) {
		wp_safe_redirect( add_query_arg( 'aiad_timeline', 'error', $redirect ) );
		exit;
	}

	// Pin it so it appears prominently
	update_post_meta( (int) $post_id, '_aiad_timeline_pinned', true );

	wp_safe_redirect( add_query_arg( array( 'aiad_timeline' => 'created', 'tid' => $post_id ), $redirect ) );
	exit;
}
add_action( 'admin_post_aiad_survey_add_to_timeline', 'aiad_survey_add_to_timeline_handler' );
