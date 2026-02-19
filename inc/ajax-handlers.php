<?php
/**
 * AJAX handlers: contact form, resource filter, filter counts cache, download tracking.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * AJAX Contact Form Handler
 */
function aiad_handle_contact_form(): void {
    check_ajax_referer( 'aiad_contact_nonce', 'nonce' );

    // Honeypot field (hidden from users, bots may fill it)
    $honeypot = isset( $_POST['aiad_website'] ) ? sanitize_text_field( wp_unslash( $_POST['aiad_website'] ) ) : '';
    if ( $honeypot !== '' ) {
        wp_send_json_error( array( 'message' => __( 'Invalid submission detected. Please refresh the page and try again.', 'ai-awareness-day' ) ) );
    }

    $first_name    = sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
    $last_name     = sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) );
    $email         = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
    $message       = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
    $involved_as   = sanitize_text_field( wp_unslash( $_POST['involved_as'] ?? '' ) );
    $school_name   = sanitize_text_field( wp_unslash( $_POST['school_name'] ?? '' ) );
    $subject       = sanitize_text_field( wp_unslash( $_POST['subject'] ?? '' ) );
    $child_school  = sanitize_text_field( wp_unslash( $_POST['child_school'] ?? '' ) );
    $role_title    = sanitize_text_field( wp_unslash( $_POST['role_title'] ?? '' ) );
    $organisation  = sanitize_text_field( wp_unslash( $_POST['organisation'] ?? '' ) );
    $org_type      = sanitize_text_field( wp_unslash( $_POST['org_type'] ?? '' ) );

    // Validate: all visible fields are compulsory
    if ( empty( $first_name ) || empty( $last_name ) || empty( $email ) || empty( $message ) ) {
        wp_send_json_error( array( 'message' => __( 'Please fill in all required fields.', 'ai-awareness-day' ) ) );
    }

    if ( empty( $involved_as ) ) {
        wp_send_json_error( array( 'message' => __( 'Please select how you\'re getting involved.', 'ai-awareness-day' ) ) );
    }

    // Role-specific required fields
    if ( ( $involved_as === 'teacher' || $involved_as === 'school_leader' ) && empty( $school_name ) ) {
        wp_send_json_error( array( 'message' => __( 'Please provide your school name.', 'ai-awareness-day' ) ) );
    }

    if ( $involved_as === 'teacher' && empty( $subject ) ) {
        wp_send_json_error( array( 'message' => __( 'Please provide your subject or area.', 'ai-awareness-day' ) ) );
    }

    if ( $involved_as === 'parent' && empty( $child_school ) ) {
        wp_send_json_error( array( 'message' => __( 'Please provide your child\'s school.', 'ai-awareness-day' ) ) );
    }

    if ( $involved_as === 'school_leader' && empty( $role_title ) ) {
        wp_send_json_error( array( 'message' => __( 'Please provide your role.', 'ai-awareness-day' ) ) );
    }

    if ( $involved_as === 'organisation' && ( empty( $organisation ) || empty( $org_type ) ) ) {
        wp_send_json_error( array( 'message' => __( 'Please provide your organisation name and type.', 'ai-awareness-day' ) ) );
    }

    if ( ! is_email( $email ) ) {
        wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'ai-awareness-day' ) ) );
    }

    $role_labels = array(
        'teacher'       => __( 'Teacher', 'ai-awareness-day' ),
        'parent'        => __( 'Parent', 'ai-awareness-day' ),
        'school_leader' => __( 'School leader', 'ai-awareness-day' ),
        'organisation'  => __( 'Organisation', 'ai-awareness-day' ),
    );
    $role_display = isset( $role_labels[ $involved_as ] ) ? $role_labels[ $involved_as ] : $involved_as;

    // Build email
    $to = get_theme_mod( 'aiad_contact_email', get_option( 'admin_email' ) );
    $subject_line = sprintf( '[AI Awareness Day] %s – %s %s', $role_display, $first_name, $last_name );

    $body  = "Getting involved as: {$role_display}\n";
    $body .= "Name: {$first_name} {$last_name}\n";
    $body .= "Email: {$email}\n";

    if ( $involved_as === 'teacher' || $involved_as === 'school_leader' ) {
        $body .= "School: {$school_name}\n";
    }
    if ( $involved_as === 'teacher' && $subject ) {
        $body .= "Subject / area: {$subject}\n";
    }
    if ( $involved_as === 'parent' && $child_school ) {
        $body .= "Child's school: {$child_school}\n";
    }
    if ( $involved_as === 'school_leader' && $role_title ) {
        $body .= "Role: {$role_title}\n";
    }
    if ( $involved_as === 'organisation' ) {
        $body .= "Organisation: {$organisation}\n";
        if ( $org_type ) {
            $body .= "Type: {$org_type}\n";
        }
    }
    $body .= "\nMessage:\n{$message}\n";

    $site_name  = get_bloginfo( 'name' );
    $site_email = get_option( 'admin_email' );
    $headers = array(
        'From: ' . $site_name . ' <' . $site_email . '>',
        'Reply-To: ' . $first_name . ' ' . $last_name . ' <' . $email . '>',
    );

    // Save submission to database
    $submission_data = array(
        'post_title'   => sprintf( '%s %s (%s)', $first_name, $last_name, $role_display ),
        'post_content' => $body,
        'post_status'  => 'publish',
        'post_type'    => 'form_submission',
    );

    $submission_id = wp_insert_post( $submission_data );

    if ( $submission_id && ! is_wp_error( $submission_id ) ) {
        // Store form data as post meta for easy retrieval
        update_post_meta( $submission_id, '_submission_first_name', $first_name );
        update_post_meta( $submission_id, '_submission_last_name', $last_name );
        update_post_meta( $submission_id, '_submission_email', $email );
        update_post_meta( $submission_id, '_submission_involved_as', $involved_as );
        update_post_meta( $submission_id, '_submission_message', $message );

        if ( $school_name ) {
            update_post_meta( $submission_id, '_submission_school_name', $school_name );
        }
        if ( $subject ) {
            update_post_meta( $submission_id, '_submission_subject', $subject );
        }
        if ( $child_school ) {
            update_post_meta( $submission_id, '_submission_child_school', $child_school );
        }
        if ( $role_title ) {
            update_post_meta( $submission_id, '_submission_role_title', $role_title );
        }
        if ( $organisation ) {
            update_post_meta( $submission_id, '_submission_organisation', $organisation );
        }
        if ( $org_type ) {
            update_post_meta( $submission_id, '_submission_org_type', $org_type );
        }
    }

    // Send email to admin
    $admin_sent = wp_mail( $to, $subject_line, $body, $headers );

    // Send confirmation email to user
    $user_subject = __( 'Thank you for your interest in AI Awareness Day', 'ai-awareness-day' );
    $user_body = sprintf(
        "Dear %s,\n\n" .
        "Thank you for getting in touch with AI Awareness Day!\n\n" .
        "We've received your submission and will be in touch soon.\n\n" .
        "Getting involved as: %s\n" .
        "%s\n\n" .
        "Best regards,\n" .
        "The AI Awareness Day Team",
        $first_name,
        $role_display,
        $message ? "Your message: {$message}\n" : ''
    );

    $user_headers = array(
        'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
    );

    $user_sent = wp_mail( $email, $user_subject, $user_body, $user_headers );

    if ( $admin_sent || $submission_id ) {
        wp_send_json_success( array( 'message' => __( 'Thank you! We\'ll be in touch soon.', 'ai-awareness-day' ) ) );
    } else {
        // Submission was saved but email failed - still show success to user
        // Admin can check submissions in the database
        wp_send_json_success( array( 'message' => __( 'Thank you! Your submission has been received. We\'ll be in touch soon.', 'ai-awareness-day' ) ) );
    }
}
add_action( 'wp_ajax_aiad_contact', 'aiad_handle_contact_form' );
add_action( 'wp_ajax_nopriv_aiad_contact', 'aiad_handle_contact_form' );

/**
 * Get filter counts given current tax_query constraints.
 * 
 * This function calculates how many resources match each filter option when combined
 * with the currently active filters. For example, if "Theme: Safe" is selected,
 * it counts how many resources match "Safe" + each available Resource Type.
 * 
 * Algorithm:
 * 1. For each taxonomy (resource_type, resource_principle, etc.):
 *    - Remove that taxonomy from the active filters (reduced_query)
 *    - For each term in that taxonomy:
 *      - Add the term to reduced_query
 *      - Count matching resources
 * 2. For key_stage (meta field, not taxonomy):
 *    - Count resources matching active filters + each key stage value
 * 
 * Results are cached for 1 hour to improve performance. Cache is invalidated
 * when resources are saved via aiad_bump_filter_counts_version().
 *
 * @param string $post_type       Post type slug ('resource' or 'featured_resource').
 * @param array  $active_tax_query Current tax_query array from active filters.
 * @return array Counts keyed by taxonomy => term_slug => count. Example:
 *               ['resource_type' => ['lesson-starter' => 5, 'assembly' => 3], ...]
 */
function aiad_get_filter_counts( string $post_type, array $active_tax_query ): array {
    // Cache key includes version number (bumped on resource save) and active filters
    $version = (int) get_option( 'aiad_filter_counts_ver', 0 );
    $cache_key = 'aiad_fc_' . $post_type . '_' . $version . '_' . md5( serialize( $active_tax_query ) );
    $cached = get_transient( $cache_key );
    if ( is_array( $cached ) ) {
        return $cached;
    }

    $taxonomies = array( 'resource_type', 'resource_principle', 'resource_duration', 'activity_type' );
    $counts     = array();

    // Process each taxonomy dimension
    foreach ( $taxonomies as $tax ) {
        $counts[ $tax ] = array();

        // Build a tax_query WITHOUT this taxonomy (so we see what's available when this filter is changed)
        // This allows us to show counts like "5 resources match Safe + Lesson Starter"
        $reduced_query = array_filter( $active_tax_query, function( $clause ) use ( $tax ) {
            return is_array( $clause ) && isset( $clause['taxonomy'] ) && $clause['taxonomy'] !== $tax;
        } );

        // Get all terms for this taxonomy (including empty ones, so counts show 0)
        $terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
        if ( ! $terms || is_wp_error( $terms ) ) {
            continue;
        }

        // Count resources for each term when combined with other active filters
        foreach ( $terms as $term ) {
            $term_query   = $reduced_query;
            $term_query[] = array(
                'taxonomy' => $tax,
                'field'    => 'slug',
                'terms'    => $term->slug,
            );
            // Add relation if multiple tax queries exist
            if ( count( $term_query ) > 1 ) {
                $term_query['relation'] = 'AND';
            }

            // Optimized query: only get IDs, no meta/term cache updates
            $count_query = new WP_Query( array(
                'post_type'               => $post_type,
                'post_status'             => 'publish',
                'posts_per_page'          => 1000, // Reasonable limit for count queries (using fields => 'ids' is lightweight)
                'fields'                  => 'ids',
                'tax_query'               => $term_query,
                'no_found_rows'           => true,
                'update_post_meta_cache'  => false,
                'update_post_term_cache'  => false,
            ) );

            $counts[ $tax ][ $term->slug ] = $count_query->post_count;
        }
    }

    // Handle key_stage separately (it's a meta field, not a taxonomy)
    if ( 'resource' === $post_type && function_exists( 'aiad_key_stage_options' ) ) {
        $counts['key_stage'] = array();
        foreach ( array_keys( aiad_key_stage_options() ) as $ks ) {
            $meta_query = array(
                array(
                    'key'     => '_aiad_key_stage',
                    'value'   => $ks,
                    'compare' => 'LIKE', // Key stage is stored as array, use LIKE to match
                ),
            );
            $count_query = new WP_Query( array(
                'post_type'               => $post_type,
                'post_status'             => 'publish',
                'posts_per_page'          => 1000, // Reasonable limit for count queries
                'fields'                  => 'ids',
                'tax_query'               => $active_tax_query, // Apply active taxonomy filters
                'meta_query'              => $meta_query, // Plus this key stage
                'no_found_rows'           => true,
                'update_post_meta_cache'  => false,
                'update_post_term_cache'  => false,
            ) );
            $counts['key_stage'][ $ks ] = $count_query->post_count;
        }
    }

    // Cache results for 1 hour
    set_transient( $cache_key, $counts, HOUR_IN_SECONDS );
    return $counts;
}

/**
 * Invalidate filter-count cache when a resource or featured_resource is saved.
 */
function aiad_bump_filter_counts_version(): void {
    $version = (int) get_option( 'aiad_filter_counts_ver', 0 );
    update_option( 'aiad_filter_counts_ver', $version + 1 );
}
add_action( 'save_post_resource', 'aiad_bump_filter_counts_version' );
add_action( 'save_post_featured_resource', 'aiad_bump_filter_counts_version' );

/**
 * AJAX handler: filter resources
 */
function aiad_ajax_filter_resources(): void {
    if ( ! isset( $_POST['filter_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['filter_nonce'] ) ), 'aiad_filter_nonce' ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh the page and try again.', 'ai-awareness-day' ) ) );
    }

    $post_type = sanitize_text_field( $_POST['post_type'] ?? 'resource' );
    if ( ! in_array( $post_type, array( 'resource', 'featured_resource' ), true ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid request. Please try again.', 'ai-awareness-day' ) ) );
    }

    $args = array(
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => 200, // Limit to 200 resources for performance (can be increased if needed)
        'orderby'        => 'title',
        'order'          => 'ASC',
    );

    $tax_query = array();

    $resource_type = sanitize_text_field( $_POST['resource_type'] ?? '' );
    if ( $resource_type ) {
        $tax_query[] = array(
            'taxonomy' => 'resource_type',
            'field'    => 'slug',
            'terms'    => $resource_type,
        );
    }

    $principle = sanitize_text_field( $_POST['principle'] ?? '' );
    if ( $principle ) {
        $tax_query[] = array(
            'taxonomy' => 'resource_principle',
            'field'    => 'slug',
            'terms'    => $principle,
        );
    }

    $duration = sanitize_text_field( $_POST['duration'] ?? '' );
    if ( $duration ) {
        $tax_query[] = array(
            'taxonomy' => 'resource_duration',
            'field'    => 'slug',
            'terms'    => $duration,
        );
    }

    $activity_type = sanitize_text_field( $_POST['activity_type'] ?? '' );
    if ( $activity_type ) {
        $tax_query[] = array(
            'taxonomy' => 'activity_type',
            'field'    => 'slug',
            'terms'    => $activity_type,
        );
    }

    if ( ! empty( $tax_query ) ) {
        $tax_query['relation'] = 'AND';
        $args['tax_query'] = $tax_query;
    }

    $key_stage = array();
    if ( ! empty( $_POST['key_stage'] ) ) {
        if ( is_array( $_POST['key_stage'] ) ) {
            $key_stage = array_map( 'sanitize_text_field', wp_unslash( $_POST['key_stage'] ) );
        } else {
            $key_stage = array( sanitize_text_field( wp_unslash( $_POST['key_stage'] ) ) );
        }
        $key_stage = array_values( array_intersect( $key_stage, array_keys( aiad_key_stage_options() ) ) );
    }
    if ( ! empty( $key_stage ) ) {
        $meta_clauses = array();
        foreach ( $key_stage as $ks ) {
            $meta_clauses[] = array(
                'key'     => '_aiad_key_stage',
                'value'   => $ks,
                'compare' => 'LIKE',
            );
        }
        $meta_clauses['relation'] = 'OR';
        $args['meta_query'] = $meta_clauses;
    }

    $query   = new WP_Query( $args );
    $results = array();

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $id = get_the_ID();

            $types      = get_the_terms( $id, 'resource_type' );
            $themes     = get_the_terms( $id, 'resource_principle' );
            $durations  = get_the_terms( $id, 'resource_duration' );
            $activities = get_the_terms( $id, 'activity_type' );

            $type_name = $types && ! is_wp_error( $types ) ? $types[0]->name : '';
            $theme_name = $themes && ! is_wp_error( $themes ) ? $themes[0]->name : '';
            $duration_name = '';
            if ( $durations && ! is_wp_error( $durations ) && function_exists( 'aiad_duration_badge_label' ) ) {
                $duration_name = aiad_duration_badge_label( $durations[0] );
            }
            $activity_names = array();
            if ( $activities && ! is_wp_error( $activities ) ) {
                foreach ( $activities as $a ) {
                    $activity_names[] = $a->name;
                }
            }

            $download_url  = get_post_meta( $id, '_resource_download_url', true );
            $featured_url  = get_post_meta( $id, '_featured_resource_url', true );

            $thumbnail = get_the_post_thumbnail_url( $id, 'medium_large' );

            $key_stage_meta = (array) get_post_meta( $id, '_aiad_key_stage', true );
            $results[] = array(
                'id'             => $id,
                'title'          => get_the_title(),
                'permalink'      => get_permalink(),
                'excerpt'        => get_the_excerpt(),
                'thumbnail'      => $thumbnail ?: '',
                'type_name'      => $type_name,
                'theme_name'     => $theme_name,
                'theme_slug'     => $themes && ! is_wp_error( $themes ) ? $themes[0]->slug : '',
                'duration_name'  => $duration_name,
                'activity_types' => $activity_names,
                'key_stages'     => array_values( array_intersect( $key_stage_meta, array_keys( aiad_key_stage_options() ) ) ),
                'download_url'   => $download_url ?: '',
                'download_label' => $download_url && function_exists( 'aiad_resource_download_label' ) ? aiad_resource_download_label( $download_url ) : '',
                'external_url'   => $featured_url ?: '',
                'org_name'       => get_post_meta( $id, '_featured_resource_org_name', true ) ?: '',
            );
        }
        wp_reset_postdata();
    }

    $counts = aiad_get_filter_counts( $post_type, $tax_query );

    wp_send_json_success( array(
        'resources'      => $results,
        'total'          => count( $results ),
        'filter_counts'  => $counts,
    ) );
}
add_action( 'wp_ajax_aiad_filter_resources', 'aiad_ajax_filter_resources' );
add_action( 'wp_ajax_nopriv_aiad_filter_resources', 'aiad_ajax_filter_resources' );

/**
 * AJAX: Track resource download
 * 
 * Increments download count for a resource. Uses nonce verification for security.
 * Available to both authenticated and unauthenticated users (stats tracking).
 */
function aiad_track_download(): void {
    // Verify nonce for CSRF protection
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'aiad_track_download_nonce' ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh the page and try again.', 'ai-awareness-day' ) ) );
    }

    $post_id = absint( $_POST['post_id'] ?? 0 );
    if ( ! $post_id || get_post_type( $post_id ) !== 'resource' ) {
        wp_send_json_error( array( 'message' => __( 'Invalid resource.', 'ai-awareness-day' ) ) );
    }

    $count = absint( get_post_meta( $post_id, '_aiad_download_count', true ) );
    $count++;
    update_post_meta( $post_id, '_aiad_download_count', $count );

    wp_send_json_success( array( 'count' => $count ) );
}
add_action( 'wp_ajax_aiad_track_download', 'aiad_track_download' );
add_action( 'wp_ajax_nopriv_aiad_track_download', 'aiad_track_download' );
