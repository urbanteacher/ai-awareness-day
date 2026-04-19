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
 * Get optional checklist option labels (for contact form and admin display).
 *
 * @return array<string, string> Map of option key => label.
 */
function aiad_get_contact_checklist_labels(): array {
    return array(
        'teacher_display_board'       => __( 'Interested in creating a display board', 'ai-awareness-day' ),
        'teacher_activity_day'        => __( 'I want to do an activity for the day', 'ai-awareness-day' ),
        'teacher_learn_ai'            => _x( 'I want to learn more about AI', 'Teacher checklist option', 'ai-awareness-day' ),
        'parent_support_child'        => __( 'I want to support my child in AI', 'ai-awareness-day' ),
        'parent_learn_ai'             => _x( 'I want to learn more about AI', 'Parent checklist option', 'ai-awareness-day' ),
        'parent_school_take_part'     => __( "I'd like my child's school to take part", 'ai-awareness-day' ),
        'school_leader_staff_activity' => __( 'I want my staff to do an activity', 'ai-awareness-day' ),
        'school_leader_logo_supporter' => __( 'I want our logo as a supporter', 'ai-awareness-day' ),
        'school_leader_school_promote' => __( 'I want our school to promote AI Awareness Day', 'ai-awareness-day' ),
        'org_brand_sponsor'           => __( 'Brand Sponsor', 'ai-awareness-day' ),
        'org_theme_sponsor'           => __( 'Theme Sponsor', 'ai-awareness-day' ),
        'org_campaign_sponsor'        => __( 'Campaign Sponsor', 'ai-awareness-day' ),
    );
}

/**
 * Get client IP for rate limiting (REMOTE_ADDR only; no proxy headers).
 *
 * @return string
 */
function aiad_get_client_ip(): string {
    return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
}

/**
 * Build a best-effort fingerprint for per-client throttling.
 *
 * @return string
 */
function aiad_get_client_fingerprint(): string {
    $ip = aiad_get_client_ip();
    $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
    return md5( $ip . '|' . $user_agent );
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

    // Rate limit: 3 submissions per IP per 5 minutes
    $limit_key = 'aiad_contact_limit_' . aiad_get_client_fingerprint();
    $count    = (int) get_transient( $limit_key );
    if ( $count >= 3 ) {
        wp_send_json_error( array( 'message' => __( 'Too many submissions from your address. Please try again in a few minutes.', 'ai-awareness-day' ) ) );
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

    // Optional checklist (role-specific; only submitted checkboxes are sent)
    $checklist_raw   = isset( $_POST['aiad_checklist'] ) && is_array( $_POST['aiad_checklist'] ) ? wp_unslash( $_POST['aiad_checklist'] ) : array();
    $checklist_labels = aiad_get_contact_checklist_labels();
    $checklist       = array();
    $checklist_keys  = array();
    foreach ( $checklist_raw as $key ) {
        $key = sanitize_text_field( $key );
        if ( isset( $checklist_labels[ $key ] ) ) {
            $checklist[]      = $checklist_labels[ $key ];
            $checklist_keys[] = $key;
        }
    }

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
    $org_type_options = function_exists( 'aiad_get_organisation_type_options' ) ? aiad_get_organisation_type_options() : array();
    if ( $involved_as === 'organisation' && $org_type && ! isset( $org_type_options[ $org_type ] ) ) {
        $org_type = 'other';
    }

    if ( ! is_email( $email ) ) {
        wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'ai-awareness-day' ) ) );
    }

    // Increment rate-limit counter only for valid submissions.
    set_transient( $limit_key, $count + 1, 300 );

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
            $org_type_label = isset( $org_type_options[ $org_type ] ) ? $org_type_options[ $org_type ] : $org_type;
            $body .= "Type: {$org_type_label}\n";
        }
    }
    if ( ! empty( $checklist ) ) {
        $body .= "\nInterested in:\n";
        foreach ( $checklist as $label ) {
            $body .= "• {$label}\n";
        }
    }
    $body .= "\nMessage:\n" . ( $message !== '' ? "\n{$message}\n" : "\n" );

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
        'post_status'  => 'private',
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
        if ( ! empty( $checklist_keys ) ) {
            update_post_meta( $submission_id, '_submission_checklist', $checklist_keys );
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
        "Best regards,\n" .
        "The AI Awareness Day Team",
        $first_name
    );

    $user_headers = array(
        'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
    );

    $user_sent = wp_mail( $email, $user_subject, $user_body, $user_headers );

    // Increment school pledge count for teachers and school leaders.
    $pledge_count = aiad_maybe_increment_school_pledge_count( $involved_as );
    $pledge_goal  = aiad_get_school_pledge_goal();

    if ( $admin_sent || $submission_id ) {
        wp_send_json_success( array(
            'message'      => __( 'Thank you! We\'ll be in touch soon.', 'ai-awareness-day' ),
            'pledge_count' => $pledge_count,
            'pledge_goal'  => $pledge_goal,
        ) );
    } else {
        // Submission was saved but email failed — still show success to user.
        wp_send_json_success( array(
            'message'      => __( 'Thank you! Your submission has been received. We\'ll be in touch soon.', 'ai-awareness-day' ),
            'pledge_count' => $pledge_count,
            'pledge_goal'  => $pledge_goal,
        ) );
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
 * 1. For each taxonomy (resource_principle, resource_duration, etc.):
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
 *               ['resource_duration' => ['5-min-lesson-starters' => 5, ...], ...]
 */
function aiad_get_filter_counts( string $post_type, array $active_tax_query ): array {
    // Cache key includes version number (bumped on resource save) and active filters
    $version = (int) get_option( 'aiad_filter_counts_ver', 0 );
    $normalized_tax_query = aiad_normalize_tax_query_for_cache( $active_tax_query );
    $cache_key = 'aiad_fc_' . $post_type . '_' . $version . '_' . md5( wp_json_encode( $normalized_tax_query ) );
    $cached = get_transient( $cache_key );
    if ( is_array( $cached ) ) {
        return $cached;
    }

    $taxonomies = array( 'resource_principle', 'resource_duration', 'activity_type' );
    $counts     = array();
    foreach ( $taxonomies as $tax ) {
        $counts[ $tax ] = array();
        $terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
        if ( $terms && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                $counts[ $tax ][ $term->slug ] = 0;
            }
        }
    }

    // Process each taxonomy with a single ID query + a single term-object query.
    foreach ( $taxonomies as $tax ) {
        $reduced_query = array_values(
            array_filter(
                $active_tax_query,
                static function ( $clause ) use ( $tax ) {
                    return is_array( $clause ) && isset( $clause['taxonomy'] ) && $clause['taxonomy'] !== $tax;
                }
            )
        );
        if ( count( $reduced_query ) > 1 ) {
            $reduced_query['relation'] = 'AND';
        }

        $base_ids = get_posts(
            array(
                'post_type'              => $post_type,
                'post_status'            => 'publish',
                'posts_per_page'         => -1,
                'fields'                 => 'ids',
                'tax_query'              => $reduced_query,
                'no_found_rows'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            )
        );
        if ( empty( $base_ids ) ) {
            continue;
        }

        $term_rows = wp_get_object_terms(
            $base_ids,
            $tax,
            array(
                'fields' => 'all_with_object_id',
            )
        );
        if ( is_wp_error( $term_rows ) || empty( $term_rows ) ) {
            continue;
        }

        $seen_by_term = array();
        foreach ( $term_rows as $row ) {
            $slug = isset( $row->slug ) ? (string) $row->slug : '';
            $object_id = isset( $row->object_id ) ? (int) $row->object_id : 0;
            if ( '' === $slug || ! $object_id ) {
                continue;
            }
            if ( ! isset( $seen_by_term[ $slug ] ) ) {
                $seen_by_term[ $slug ] = array();
            }
            $seen_by_term[ $slug ][ $object_id ] = true;
        }

        foreach ( $seen_by_term as $slug => $post_ids_map ) {
            $counts[ $tax ][ $slug ] = count( $post_ids_map );
        }
    }

    // Handle key_stage separately (it's a meta field, not a taxonomy)
    if ( 'resource' === $post_type && function_exists( 'aiad_key_stage_options' ) ) {
        $counts['key_stage'] = array();
        foreach ( array_keys( aiad_key_stage_options() ) as $ks ) {
            $counts['key_stage'][ $ks ] = 0;
        }

        $filtered_ids = get_posts(
            array(
                'post_type'              => $post_type,
                'post_status'            => 'publish',
                'posts_per_page'         => -1,
                'fields'                 => 'ids',
                'tax_query'              => $active_tax_query,
                'no_found_rows'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            )
        );

        if ( ! empty( $filtered_ids ) ) {
            global $wpdb;
            $id_list = implode( ',', array_map( 'absint', $filtered_ids ) );
            $rows = $wpdb->get_results(
                "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_aiad_key_stage' AND post_id IN ($id_list)", // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                ARRAY_A
            );

            if ( is_array( $rows ) ) {
                $valid_stages = array_keys( aiad_key_stage_options() );
                foreach ( $rows as $row ) {
                    $values = maybe_unserialize( $row['meta_value'] ?? '' );
                    if ( ! is_array( $values ) ) {
                        continue;
                    }
                    foreach ( array_unique( $values ) as $stage ) {
                        if ( in_array( $stage, $valid_stages, true ) ) {
                            $counts['key_stage'][ $stage ]++;
                        }
                    }
                }
            }
        }
    }

    // Cache results for 1 hour
    set_transient( $cache_key, $counts, HOUR_IN_SECONDS );
    return $counts;
}

/**
 * Create stable ordering for tax_query cache keys.
 *
 * @param array $tax_query Tax query clauses.
 * @return array
 */
function aiad_normalize_tax_query_for_cache( array $tax_query ): array {
    $relation = isset( $tax_query['relation'] ) ? $tax_query['relation'] : '';
    $clauses = array_values(
        array_filter(
            $tax_query,
            static function ( $clause ) {
                return is_array( $clause ) && isset( $clause['taxonomy'] );
            }
        )
    );
    usort(
        $clauses,
        static function ( $a, $b ) {
            $a_tax = (string) ( $a['taxonomy'] ?? '' );
            $b_tax = (string) ( $b['taxonomy'] ?? '' );
            if ( $a_tax === $b_tax ) {
                return strcmp( wp_json_encode( $a ), wp_json_encode( $b ) );
            }
            return strcmp( $a_tax, $b_tax );
        }
    );
    if ( $relation ) {
        $clauses['relation'] = $relation;
    }
    return $clauses;
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
    // No nonce required: this endpoint only returns public, published post data.
    // A nonce would expire on cached pages and break filtering for all visitors.

    $post_type = sanitize_text_field( $_POST['post_type'] ?? 'resource' );
    if ( ! in_array( $post_type, array( 'resource', 'featured_resource' ), true ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid request. Please try again.', 'ai-awareness-day' ) ) );
    }

    $args = array(
        'post_type'              => $post_type,
        'post_status'            => 'publish',
        'posts_per_page'         => 200,
        'orderby'                => 'title',
        'order'                  => 'ASC',
        'update_post_meta_cache' => true, // Prime all meta in one query — prevents N+1 inside the loop
        'update_post_term_cache' => true, // Prime all term caches in one query
    );

    $tax_query = array();

    $principle = sanitize_text_field( $_POST['principle'] ?? '' );
    if ( $principle ) {
        $tax_query[] = array(
            'taxonomy' => 'resource_principle',
            'field'    => 'slug',
            'terms'    => $principle,
        );
    }

    $duration = sanitize_text_field( $_POST['duration'] ?? '' );
    $legacy_type = sanitize_text_field( $_POST['resource_type'] ?? '' );
    if ( ! $duration && $legacy_type && function_exists( 'aiad_legacy_resource_type_slug_to_duration_slug' ) ) {
        $duration = aiad_legacy_resource_type_slug_to_duration_slug( $legacy_type );
    }
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
                'value'   => '"' . $ks . '"', // Match exact serialised value to prevent substring false positives
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

            $themes     = get_the_terms( $id, 'resource_principle' );
            $durations  = get_the_terms( $id, 'resource_duration' );
            $activities = get_the_terms( $id, 'activity_type' );

            $duration_names = array();
            $duration_slugs = array();
            if ( $durations && ! is_wp_error( $durations ) && function_exists( 'aiad_resource_duration_term_labels' ) ) {
                $duration_names = aiad_resource_duration_term_labels( $durations );
                foreach ( $durations as $dterm ) {
                    $duration_slugs[] = $dterm->slug;
                }
            }
            $duration_name = ! empty( $duration_names ) ? $duration_names[0] : '';
            $theme_name = $themes && ! is_wp_error( $themes ) ? $themes[0]->name : '';
            $activity_names = array();
            if ( $activities && ! is_wp_error( $activities ) ) {
                foreach ( $activities as $a ) {
                    $activity_names[] = $a->name;
                }
            }

            $download_url  = get_post_meta( $id, '_aiad_download_url', true );
            $featured_url  = get_post_meta( $id, '_featured_resource_url', true );

            $thumbnail = get_the_post_thumbnail_url( $id, 'medium_large' );

            $key_stage_meta = (array) get_post_meta( $id, '_aiad_key_stage', true );
            $results[] = array(
                'id'             => $id,
                'title'          => get_the_title(),
                'permalink'      => get_permalink(),
                'excerpt'        => get_the_excerpt(),
                'thumbnail'      => $thumbnail ?: '',
                'type_name'      => $duration_name,
                'type_names'     => $duration_names,
                'duration_names' => $duration_names,
                'duration_slugs' => $duration_slugs,
                'duration_name'  => $duration_name,
                'theme_name'     => $theme_name,
                'theme_slug'     => $themes && ! is_wp_error( $themes ) ? $themes[0]->slug : '',
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

    // Throttle: one count per IP per resource per 90 seconds
    $ip         = aiad_get_client_ip();
    $throttle_key = 'aiad_dl_' . md5( $ip . (string) $post_id );
    if ( get_transient( $throttle_key ) ) {
        $count = absint( get_post_meta( $post_id, '_aiad_download_count', true ) );
        wp_send_json_success( array( 'count' => $count ) );
    }

    set_transient( $throttle_key, 1, 90 );
    $count = absint( get_post_meta( $post_id, '_aiad_download_count', true ) );
    $count++;
    update_post_meta( $post_id, '_aiad_download_count', $count );

    wp_send_json_success( array( 'count' => $count ) );
}
add_action( 'wp_ajax_aiad_track_download', 'aiad_track_download' );
add_action( 'wp_ajax_nopriv_aiad_track_download', 'aiad_track_download' );

/**
 * AJAX: Track resource page view
 *
 * Increments view count when a resource page is visited.
 * Throttled to one count per IP per resource per 24 hours.
 */
function aiad_track_resource_view(): void {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'aiad_track_view_nonce' ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'ai-awareness-day' ) ) );
    }

    $post_id = absint( $_POST['post_id'] ?? 0 );
    if ( ! $post_id || get_post_type( $post_id ) !== 'resource' ) {
        wp_send_json_error( array( 'message' => __( 'Invalid resource.', 'ai-awareness-day' ) ) );
    }

    $ip           = aiad_get_client_ip();
    $throttle_key = 'aiad_rv_' . md5( $ip . (string) $post_id );
    if ( get_transient( $throttle_key ) ) {
        $count = absint( get_post_meta( $post_id, '_aiad_view_count', true ) );
        wp_send_json_success( array( 'count' => $count ) );
    }

    set_transient( $throttle_key, 1, DAY_IN_SECONDS );
    $count = absint( get_post_meta( $post_id, '_aiad_view_count', true ) );
    $count++;
    update_post_meta( $post_id, '_aiad_view_count', $count );

    wp_send_json_success( array( 'count' => $count ) );
}
add_action( 'wp_ajax_aiad_track_resource_view', 'aiad_track_resource_view' );
add_action( 'wp_ajax_nopriv_aiad_track_resource_view', 'aiad_track_resource_view' );
