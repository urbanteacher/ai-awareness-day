<?php
/**
 * Get Involved contact form: the REST route, checklist labels, and client IP / fingerprint for rate limiting.
 *
 * Moved from the theme's inc/ajax-handlers.php. The theme loads this file from its bundled copy of the plugin when
 * the plugin isn't active, so this is the only copy.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Marks this module as loaded. See aiad_core_load_modules().
define( 'AIAD_CORE_MODULE_CONTACT', __FILE__ );

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
 * POST aiad/v1/contact: the Get Involved form.
 *
 * It was an admin-ajax handler behind a nonce printed into the page. HTML that is cached outlives a nonce, and a
 * submission with a stale one got a bare "-1" the page script could not read, so the form said "Network error". The
 * form is for anyone, so it is a public route; what protects it is what the handler already did: a hidden honeypot
 * field, three submissions per visitor per five minutes, and validation. The page script (assets/js/contact-form.js)
 * finds the route through the REST discovery link.
 *
 * @param WP_REST_Request $request The form's fields.
 * @return WP_REST_Response|WP_Error
 */
function aiad_rest_contact_form( WP_REST_Request $request ) {
    $error = static function ( string $message, int $status = 400 ): WP_Error {
        return new WP_Error( 'aiad_contact', $message, array( 'status' => $status ) );
    };
    // A text field is a string; anything else sent under its name (an array, say) counts as empty.
    $field = static function ( string $key ) use ( $request ): string {
        $value = $request->get_param( $key );
        return is_scalar( $value ) ? (string) $value : '';
    };

    // Honeypot field (hidden from users, bots may fill it)
    $honeypot = sanitize_text_field( $field( 'aiad_website' ) );
    if ( $honeypot !== '' ) {
        return $error( __( 'Invalid submission detected. Please refresh the page and try again.', 'ai-awareness-day' ) );
    }

    // Rate limit: 3 submissions per IP per 5 minutes
    $limit_key = 'aiad_contact_limit_' . aiad_get_client_fingerprint();
    $count    = (int) get_transient( $limit_key );
    if ( $count >= 3 ) {
        return $error( __( 'Too many submissions from your address. Please try again in a few minutes.', 'ai-awareness-day' ), 429 );
    }

    $first_name    = sanitize_text_field( $field( 'first_name' ) );
    $last_name     = sanitize_text_field( $field( 'last_name' ) );
    $email         = sanitize_email( $field( 'email' ) );
    $message       = sanitize_textarea_field( $field( 'message' ) );
    $involved_as   = sanitize_text_field( $field( 'involved_as' ) );
    $school_name   = sanitize_text_field( $field( 'school_name' ) );
    $subject       = sanitize_text_field( $field( 'subject' ) );
    $child_school  = sanitize_text_field( $field( 'child_school' ) );
    $role_title    = sanitize_text_field( $field( 'role_title' ) );
    $organisation  = sanitize_text_field( $field( 'organisation' ) );
    $org_type      = sanitize_text_field( $field( 'org_type' ) );

    // Optional checklist (role-specific; only submitted checkboxes are sent)
    $checklist_raw   = is_array( $request->get_param( 'aiad_checklist' ) ) ? $request->get_param( 'aiad_checklist' ) : array();
    $checklist_labels = aiad_get_contact_checklist_labels();
    $checklist       = array();
    $checklist_keys  = array();
    foreach ( $checklist_raw as $key ) {
        $key = sanitize_text_field( (string) $key );
        if ( isset( $checklist_labels[ $key ] ) ) {
            $checklist[]      = $checklist_labels[ $key ];
            $checklist_keys[] = $key;
        }
    }

    // Validate: all visible fields are compulsory
    if ( empty( $first_name ) || empty( $last_name ) || empty( $email ) || empty( $message ) ) {
        return $error( __( 'Please fill in all required fields.', 'ai-awareness-day' ) );
    }

    if ( empty( $involved_as ) ) {
        return $error( __( 'Please select how you\'re getting involved.', 'ai-awareness-day' ) );
    }

    // Role-specific required fields
    if ( ( $involved_as === 'teacher' || $involved_as === 'school_leader' ) && empty( $school_name ) ) {
        return $error( __( 'Please provide your school name.', 'ai-awareness-day' ) );
    }

    if ( $involved_as === 'teacher' && empty( $subject ) ) {
        return $error( __( 'Please provide your subject or area.', 'ai-awareness-day' ) );
    }

    if ( $involved_as === 'parent' && empty( $child_school ) ) {
        return $error( __( 'Please provide your child\'s school.', 'ai-awareness-day' ) );
    }

    if ( $involved_as === 'school_leader' && empty( $role_title ) ) {
        return $error( __( 'Please provide your role.', 'ai-awareness-day' ) );
    }

    if ( $involved_as === 'organisation' && ( empty( $organisation ) || empty( $org_type ) ) ) {
        return $error( __( 'Please provide your organisation name and type.', 'ai-awareness-day' ) );
    }
    $org_type_options = function_exists( 'aiad_get_organisation_type_options' ) ? aiad_get_organisation_type_options() : array();
    if ( $involved_as === 'organisation' && $org_type && ! isset( $org_type_options[ $org_type ] ) ) {
        $org_type = 'other';
    }

    if ( ! is_email( $email ) ) {
        return $error( __( 'Please enter a valid email address.', 'ai-awareness-day' ) );
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
    $to = aiad_campaign_setting( 'contact_email' ) ?: get_option( 'admin_email' );
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
        return new WP_REST_Response(
            array(
                'message'      => __( 'Thank you! We\'ll be in touch soon.', 'ai-awareness-day' ),
                'pledge_count' => $pledge_count,
                'pledge_goal'  => $pledge_goal,
            )
        );
    } else {
        // Submission was saved but email failed — still show success to user.
        return new WP_REST_Response(
            array(
                'message'      => __( 'Thank you! Your submission has been received. We\'ll be in touch soon.', 'ai-awareness-day' ),
                'pledge_count' => $pledge_count,
                'pledge_goal'  => $pledge_goal,
            )
        );
    }
}

/**
 * Register the route.
 */
function aiad_register_contact_rest_route(): void {
    register_rest_route(
        'aiad/v1',
        '/contact',
        array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'aiad_rest_contact_form',
            'permission_callback' => '__return_true',
        )
    );
}
add_action( 'rest_api_init', 'aiad_register_contact_rest_route' );
