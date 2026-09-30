<?php
/**
 * CSV export of form submissions for mail merge with attached PDFs.
 *
 * Adds an "Export to CSV" button at the top of the Form Submissions admin
 * list. The CSV is shaped for Doug Robbins' / MAPILab merge-with-attachment
 * tools — one row per signup with the expected letter and certificate
 * filenames that match the ZIP exports.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Title-case a personal name when it looks unintentionally cased (all lower
 * or all upper). Mirrors normalizeDisplayName() in letter-generator.html /
 * certificate-generator.html so the filename slug here matches the one the
 * browser produces inside the ZIP.
 */
function aiad_normalize_display_name( string $raw ): string {
	$trimmed = trim( $raw );
	if ( $trimmed === '' ) {
		return $trimmed;
	}
	$has_lower = (bool) preg_match( '/[a-z]/', $trimmed );
	$has_upper = (bool) preg_match( '/[A-Z]/', $trimmed );
	if ( $has_lower && $has_upper ) {
		return $trimmed;
	}
	$lower = strtolower( $trimmed );
	return preg_replace_callback(
		'/(^|[\s\-\'])([a-z])/u',
		static function ( $m ) {
			return $m[1] . strtoupper( $m[2] );
		},
		$lower
	);
}

/**
 * Filename-safe slug for a participant.
 *
 * Mirrors safeFilenameFromRow() / safeCertFilenameFromRow() in the browser
 * generators: non-alphanumerics collapse to single hyphens, leading and
 * trailing hyphens trimmed, fallback to "participant-N" if the result is
 * empty after slugifying.
 */
function aiad_submission_filename_slug( string $full_name, int $fallback_index ): string {
	$normalised = aiad_normalize_display_name( $full_name );
	$slug       = preg_replace( '/[^A-Za-z0-9]+/', '-', $normalised );
	$slug       = trim( (string) $slug, '-' );
	if ( $slug === '' ) {
		return 'participant-' . ( $fallback_index + 1 );
	}
	return $slug;
}

/**
 * Add the "Export to CSV" button above the Form Submissions list table.
 */
function aiad_submissions_csv_export_button( string $which ): void {
	global $typenow;
	if ( $typenow !== 'form_submission' || $which !== 'top' ) {
		return;
	}
	$role   = isset( $_GET['aiad_role_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['aiad_role_filter'] ) ) : 'all';
	$url    = wp_nonce_url(
		add_query_arg(
			array(
				'action'           => 'aiad_export_submissions_csv',
				'aiad_role_filter' => $role,
			),
			admin_url( 'admin-post.php' )
		),
		'aiad_export_submissions_csv'
	);
	echo '<a href="' . esc_url( $url ) . '" class="button button-secondary" style="margin-left:8px;">'
		. esc_html__( 'Export to CSV (mail merge)', 'ai-awareness-day' )
		. '</a>';
}
add_action( 'manage_posts_extra_tablenav', 'aiad_submissions_csv_export_button' );

/**
 * Streams the CSV download.
 */
function aiad_handle_submissions_csv_export(): void {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Insufficient permissions.', 'ai-awareness-day' ), 403 );
	}
	check_admin_referer( 'aiad_export_submissions_csv' );

	$role_filter = isset( $_GET['aiad_role_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['aiad_role_filter'] ) ) : 'all';

	$query = new WP_Query(
		array(
			'post_type'              => 'form_submission',
			'post_status'            => 'private',
			'posts_per_page'         => -1,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
		)
	);

	$rows = array();
	$idx  = 0;
	foreach ( $query->posts as $post ) {
		if ( ! ( $post instanceof WP_Post ) ) {
			continue;
		}

		$first       = aiad_normalize_display_name( (string) get_post_meta( $post->ID, '_submission_first_name', true ) );
		$last        = aiad_normalize_display_name( (string) get_post_meta( $post->ID, '_submission_last_name', true ) );
		$full        = aiad_normalize_display_name( trim( $first . ' ' . $last ) );
		$email       = (string) get_post_meta( $post->ID, '_submission_email', true );
		$school      = (string) get_post_meta( $post->ID, '_submission_school_name', true );
		if ( $school === '' ) {
			$school = (string) get_post_meta( $post->ID, '_submission_organisation', true );
		}
		if ( $school === '' ) {
			$school = (string) get_post_meta( $post->ID, '_submission_child_school', true );
		}
		$involved_as = (string) get_post_meta( $post->ID, '_submission_involved_as', true );
		$org_type    = (string) get_post_meta( $post->ID, '_submission_org_type', true );

		// Role-filter parity with the JS getRoleKey() logic.
		$role_key = ( $involved_as === 'organisation' && $org_type !== '' )
			? 'org:' . $org_type
			: ( $involved_as !== '' ? $involved_as : 'unknown' );
		if ( $role_filter !== 'all' && $role_filter !== '' && $role_key !== $role_filter ) {
			continue;
		}

		$slug             = aiad_submission_filename_slug( $full, $idx );
		$letter_filename  = 'ai-awareness-day-thank-you-' . $slug . '.pdf';
		$cert_filename    = 'ai-awareness-day-certificate-' . $slug . '.pdf';

		$rows[] = array(
			'first_name'           => $first,
			'last_name'            => $last,
			'full_name'            => $full,
			'email'                => $email,
			'school_name'          => $school,
			'role'                 => $involved_as,
			'org_type'             => $org_type,
			'submitted_at'         => get_the_date( 'Y-m-d', $post ),
			'letter_filename'      => $letter_filename,
			'certificate_filename' => $cert_filename,
		);
		++$idx;
	}

	$timestamp = current_time( 'Y-m-d-His' );
	$suffix    = ( $role_filter && $role_filter !== 'all' )
		? '-' . preg_replace( '/[^A-Za-z0-9]+/', '-', $role_filter )
		: '';
	$filename  = 'aiad-submissions' . $suffix . '-' . $timestamp . '.csv';

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

	$out = fopen( 'php://output', 'w' );
	if ( $out === false ) {
		wp_die( esc_html__( 'Could not open output stream.', 'ai-awareness-day' ) );
	}
	// UTF-8 BOM so Excel opens accented characters correctly on Windows.
	fwrite( $out, "\xEF\xBB\xBF" );
	fputcsv(
		$out,
		array(
			'first_name',
			'last_name',
			'full_name',
			'email',
			'school_name',
			'role',
			'org_type',
			'submitted_at',
			'letter_filename',
			'certificate_filename',
		)
	);
	foreach ( $rows as $row ) {
		fputcsv( $out, array_values( $row ) );
	}
	fclose( $out );
	exit;
}
add_action( 'admin_post_aiad_export_submissions_csv', 'aiad_handle_submissions_csv_export' );

/**
 * Add a role filter dropdown above the Form Submissions list so the export
 * button can scope to one role (and the list itself reflects what you'll
 * export).
 */
function aiad_submissions_role_filter_dropdown(): void {
	global $typenow;
	if ( $typenow !== 'form_submission' ) {
		return;
	}
	$current = isset( $_GET['aiad_role_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['aiad_role_filter'] ) ) : 'all';

	$labels = array(
		'all'                    => __( 'All roles', 'ai-awareness-day' ),
		'teacher'                => __( 'Teachers', 'ai-awareness-day' ),
		'school_leader'          => __( 'School leaders', 'ai-awareness-day' ),
		'parent'                 => __( 'Parents / carers', 'ai-awareness-day' ),
		'organisation'           => __( 'Organisations (any)', 'ai-awareness-day' ),
		'org:company'            => __( 'Orgs — Companies', 'ai-awareness-day' ),
		'org:education_provider' => __( 'Orgs — Education providers', 'ai-awareness-day' ),
		'org:charity'            => __( 'Orgs — Charities', 'ai-awareness-day' ),
		'org:university'         => __( 'Orgs — Universities', 'ai-awareness-day' ),
		'org:institute'          => __( 'Orgs — Institutes', 'ai-awareness-day' ),
	);

	echo '<select name="aiad_role_filter" style="margin-right:6px;">';
	foreach ( $labels as $key => $label ) {
		printf(
			'<option value="%1$s"%2$s>%3$s</option>',
			esc_attr( $key ),
			selected( $current, $key, false ),
			esc_html( $label )
		);
	}
	echo '</select>';
}
add_action( 'restrict_manage_posts', 'aiad_submissions_role_filter_dropdown' );

/**
 * Apply the role filter to the Form Submissions list query so what you see
 * matches what the CSV export will contain.
 */
function aiad_submissions_apply_role_filter( WP_Query $query ): void {
	global $pagenow, $typenow;
	if ( ! is_admin() || $pagenow !== 'edit.php' || $typenow !== 'form_submission' || ! $query->is_main_query() ) {
		return;
	}
	$role = isset( $_GET['aiad_role_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['aiad_role_filter'] ) ) : '';
	if ( $role === '' || $role === 'all' ) {
		return;
	}

	$meta_query = array();
	if ( strpos( $role, 'org:' ) === 0 ) {
		$org_type     = substr( $role, 4 );
		$meta_query[] = array(
			'key'   => '_submission_involved_as',
			'value' => 'organisation',
		);
		$meta_query[] = array(
			'key'   => '_submission_org_type',
			'value' => $org_type,
		);
		$meta_query['relation'] = 'AND';
	} else {
		$meta_query[] = array(
			'key'   => '_submission_involved_as',
			'value' => $role,
		);
	}
	$query->set( 'meta_query', $meta_query );
}
add_action( 'pre_get_posts', 'aiad_submissions_apply_role_filter' );
