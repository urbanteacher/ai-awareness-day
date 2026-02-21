<?php
/**
 * Resource Activity Schema validation: blocklist and save hook.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Activity Schema v1: blocklisted phrases that must not appear in resource content.
 * Reject generic filler so authors write specific, useful content.
 *
 * @return array<string>
 */
function aiad_activity_schema_blocklist(): array {
    return array(
        'as specified',
        'as needed',
        'will be met',
        'learning objectives will be met',
        'follow the guidelines',
        'follow the activity guidelines',
        'explore related topics',
        'basic materials as needed',
        'all ages',
        'duration: as specified',
    );
}

/**
 * Validate a resource against Activity Schema v1 rules.
 * Returns an array of error codes. Empty array = valid.
 * Used when status is set to "published" to block publish until fixed.
 *
 * @param int $post_id Resource post ID.
 * @return array<string> Error codes: blocklist, learning_objectives_count, instructions_duration, teacher_notes_length.
 */
function aiad_validate_resource_activity_schema( int $post_id ): array {
    $errors = array();

    $blocklist = aiad_activity_schema_blocklist();
    $texts     = array();

    $subtitle = get_post_meta( $post_id, '_aiad_subtitle', true );
    if ( is_string( $subtitle ) && $subtitle !== '' ) {
        $texts[] = $subtitle;
    }
    $prep = get_post_meta( $post_id, '_aiad_preparation', true );
    if ( is_array( $prep ) ) {
        $texts[] = implode( ' ', $prep );
    }
    $objectives = get_post_meta( $post_id, '_aiad_learning_objectives', true );
    if ( is_array( $objectives ) ) {
        foreach ( $objectives as $ob ) {
            if ( is_array( $ob ) && isset( $ob['objective'] ) ) {
                $texts[] = $ob['objective'];
            } elseif ( is_string( $ob ) ) {
                $texts[] = $ob;
            }
        }
    }
    $instructions = get_post_meta( $post_id, '_aiad_instructions', true );
    if ( is_array( $instructions ) ) {
        foreach ( $instructions as $step ) {
            if ( is_array( $step ) && isset( $step['action'] ) ) {
                $texts[] = $step['action'];
            } elseif ( is_string( $step ) ) {
                $texts[] = $step;
            }
        }
    }
    // Discussion question, suggested answers, prompts, and teacher notes are now optional
    // and not part of the Activity Schema validation surface.
    $diff = get_post_meta( $post_id, '_aiad_differentiation', true );
    if ( is_array( $diff ) ) {
        $texts[] = implode( ' ', array_values( $diff ) );
    }
    $extensions = get_post_meta( $post_id, '_aiad_extensions', true );
    if ( is_array( $extensions ) ) {
        foreach ( $extensions as $ext ) {
            if ( is_array( $ext ) && isset( $ext['activity'] ) ) {
                $texts[] = $ext['activity'];
            }
        }
    }

    $combined = implode( ' ', $texts );
    $lower    = mb_strtolower( $combined, 'UTF-8' );
    foreach ( $blocklist as $phrase ) {
        if ( $phrase !== '' && strpos( $lower, mb_strtolower( $phrase, 'UTF-8' ) ) !== false ) {
            $errors[] = 'blocklist';
            break;
        }
    }

    $lo_count = 0;
    if ( is_array( $objectives ) ) {
        foreach ( $objectives as $ob ) {
            if ( is_array( $ob ) && isset( $ob['objective'] ) && trim( (string) $ob['objective'] ) !== '' ) {
                $lo_count++;
            } elseif ( is_string( $ob ) && trim( $ob ) !== '' ) {
                $lo_count++;
            }
        }
    }
    if ( $lo_count > 0 && ( $lo_count < 2 || $lo_count > 5 ) ) {
        $errors[] = 'learning_objectives_count';
    }

    $has_duration = false;
    if ( is_array( $instructions ) && count( $instructions ) > 0 ) {
        foreach ( $instructions as $step ) {
            if ( is_array( $step ) && isset( $step['duration'] ) && trim( (string) $step['duration'] ) !== '' ) {
                $has_duration = true;
                break;
            }
        }
    }
    if ( ! $has_duration && is_array( $instructions ) && count( $instructions ) > 0 ) {
        $errors[] = 'instructions_duration';
    }

    return $errors;
}

/**
 * After saving a resource, run Activity Schema validation. If status is "published" and validation fails,
 * set status back to "in_review" and store error codes in a transient for admin notices.
 */
function aiad_validate_resource_on_save( int $post_id ): void {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( get_post_type( $post_id ) !== 'resource' ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $status = get_post_meta( $post_id, '_aiad_status', true );
    if ( $status !== 'published' ) {
        delete_transient( 'aiad_schema_validation_errors_' . $post_id );
        return;
    }

    $errors = aiad_validate_resource_activity_schema( $post_id );
    if ( empty( $errors ) ) {
        delete_transient( 'aiad_schema_validation_errors_' . $post_id );
        return;
    }

    update_post_meta( $post_id, '_aiad_status', 'in_review' );
    // 300s TTL so admin has time to see the notice; optional: key by get_current_user_id() for user-scoped notices
    set_transient( 'aiad_schema_validation_errors_' . $post_id, array_unique( $errors ), 300 );
}
add_action( 'save_post_resource', 'aiad_validate_resource_on_save', 20 );
