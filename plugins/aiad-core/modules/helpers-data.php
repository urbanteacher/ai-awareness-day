<?php
/**
 * Data helpers: resource durations and key stages, organisation types, post lookup by title, YouTube IDs, resource
 * download labels, learning objective and instruction normalisers, and the school pledge count.
 *
 * Moved from the theme's inc/helpers.php. The theme loads this file from its bundled copy of the plugin when the
 * plugin isn't active, so this is the only copy. Presentation helpers stay in the theme.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Marks this module as loaded. See aiad_core_load_modules().
define( 'AIAD_CORE_MODULE_HELPERS_DATA', __FILE__ );

/**
 * Duration badge label (slug or term → canonical label, e.g. "Lesson Starter (5 min)")
 *
 * @param object|string $term_or_slug WP_Term or duration slug.
 * @return string
 */
function aiad_duration_badge_label( object|string $term_or_slug ): string {
    $slug = is_object( $term_or_slug ) ? $term_or_slug->slug : $term_or_slug;
    $labels = array(
        '5-min-lesson-starters'    => __( 'Lesson Starter (5 min)', 'ai-awareness-day' ),
        '15-20-min-tutor-time'     => __( 'Tutor Time (15 min)', 'ai-awareness-day' ),
        '20-min-assemblies'        => __( 'Assembly (20 min)', 'ai-awareness-day' ),
        '30-45-min-after-school'   => __( 'After School (30 min)', 'ai-awareness-day' ),
    );
    return isset( $labels[ $slug ] ) ? $labels[ $slug ] : ( is_object( $term_or_slug ) ? $term_or_slug->name : $term_or_slug );
}

/**
 * Session length split for resource card pills (narrow/mobile): label + time on separate lines.
 *
 * @param object|string $term_or_slug WP_Term or resource_duration slug.
 * @return array{slot: string, time: string}|null
 */
function aiad_duration_badge_parts( object|string $term_or_slug ): ?array {
    $slug = is_object( $term_or_slug ) ? $term_or_slug->slug : $term_or_slug;
    $map  = array(
        '5-min-lesson-starters'  => array(
            'slot' => __( 'Lesson Starter', 'ai-awareness-day' ),
            'time' => __( '5 min', 'ai-awareness-day' ),
        ),
        '15-20-min-tutor-time'   => array(
            'slot' => __( 'Tutor Time', 'ai-awareness-day' ),
            'time' => __( '15 min', 'ai-awareness-day' ),
        ),
        '20-min-assemblies'      => array(
            'slot' => __( 'Assembly', 'ai-awareness-day' ),
            'time' => __( '20 min', 'ai-awareness-day' ),
        ),
        '30-45-min-after-school' => array(
            'slot' => __( 'After School', 'ai-awareness-day' ),
            'time' => __( '30 min', 'ai-awareness-day' ),
        ),
    );
    return isset( $map[ $slug ] ) ? $map[ $slug ] : null;
}

/**
 * Map legacy Format (resource_type) term to Session length (resource_duration) slugs — used by one-time migration.
 *
 * @param WP_Term $term Term in resource_type taxonomy.
 * @return string[] Duration term slugs.
 */
function aiad_resource_type_term_to_duration_slugs( WP_Term $term ): array {
    $slug = $term->slug;
    $name = strtolower( $term->name );

    $by_slug = array(
        'lesson-starter'    => array( '5-min-lesson-starters' ),
        'lesson-starters'   => array( '5-min-lesson-starters' ),
        'lesson-activity'   => array( '15-20-min-tutor-time' ),
        'lesson-activities' => array( '15-20-min-tutor-time' ),
        'assembly'          => array( '20-min-assemblies' ),
    );
    if ( isset( $by_slug[ $slug ] ) ) {
        return $by_slug[ $slug ];
    }
    if ( false !== strpos( $name, 'lesson starter' ) || 'lesson starter' === $name ) {
        return array( '5-min-lesson-starters' );
    }
    if ( false !== strpos( $name, 'lesson activity' ) || false !== strpos( $name, 'tutor' ) ) {
        return array( '15-20-min-tutor-time' );
    }
    if ( false !== strpos( $name, 'assembly' ) ) {
        return array( '20-min-assemblies' );
    }
    return array();
}

/**
 * Legacy query/AJAX: old ?resource_type= slug → resource_duration slug (after Format merged into Session length).
 *
 * @param string $resource_type_slug Former resource_type term slug.
 * @return string Duration slug or empty.
 */
function aiad_legacy_resource_type_slug_to_duration_slug( string $resource_type_slug ): string {
    $resource_type_slug = sanitize_title( $resource_type_slug );
    $map                = array(
        'lesson-starter'    => '5-min-lesson-starters',
        'lesson-starters'   => '5-min-lesson-starters',
        'lesson-activity'   => '15-20-min-tutor-time',
        'lesson-activities' => '15-20-min-tutor-time',
        'assembly'          => '20-min-assemblies',
    );
    return isset( $map[ $resource_type_slug ] ) ? $map[ $resource_type_slug ] : '';
}

/**
 * Display labels for multiple session-length terms (badge text).
 *
 * @param WP_Term[] $terms resource_duration terms.
 * @return string[]
 */
function aiad_resource_duration_term_labels( array $terms ): array {
    $out = array();
    foreach ( $terms as $t ) {
        if ( $t instanceof WP_Term ) {
            $out[] = function_exists( 'aiad_duration_badge_label' ) ? aiad_duration_badge_label( $t ) : $t->name;
        }
    }
    return array_values( array_unique( array_filter( $out ) ) );
}

/**
 * Get a single post by title and post type (replacement for deprecated get_page_by_title).
 *
 * @param string $title     Post title (exact match).
 * @param string $post_type Post type.
 * @return WP_Post|null Post object or null if not found.
 */
function aiad_get_post_by_title( string $title, string $post_type = 'post' ): ?WP_Post {
    $q = new WP_Query( array(
        'post_type'              => $post_type,
        'title'                  => $title,
        'post_status'            => 'any',
        'posts_per_page'         => 1,
        'no_found_rows'          => true,
        'ignore_sticky_posts'    => true,
        'update_post_term_cache' => false,
        'update_post_meta_cache' => false,
    ) );
    return $q->have_posts() ? $q->posts[0] : null;
}

/**
 * The oldest post with exactly this title, of any status: the WP_Query replacement for the deprecated
 * get_page_by_title() (deprecated in WordPress 6.2), as recommended in its dev note.
 *
 * @see https://make.wordpress.org/core/2023/03/06/get_page_by_title-deprecated/
 *
 * @param string $title     Post title (exact match).
 * @param string $post_type Post type.
 * @return WP_Post|null
 */
function aiad_core_get_post_by_exact_title( string $title, string $post_type = 'page' ): ?WP_Post {
	$query = new WP_Query(
		array(
			'post_type'              => $post_type,
			'title'                  => $title,
			'post_status'            => 'all',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'orderby'                => 'date ID',
			'order'                  => 'ASC',
		)
	);
	return $query->have_posts() ? $query->posts[0] : null;
}

/**
 * Key stage options (slug => label)
 *
 * @return array<string, string>
 */
function aiad_key_stage_options(): array {
    return array(
        'eyfs' => 'EYFS',
        'ks1'  => 'KS1',
        'ks2'  => 'KS2',
        'ks3'  => 'KS3',
        'ks4'  => 'KS4',
        'ks5'  => 'KS5',
    );
}

/**
 * Organisation type options for Get Involved form (UK-focused).
 * Returns slug => label for dropdown and display.
 *
 * @return array<string, string>
 */
function aiad_get_organisation_type_options(): array {
    return array(
        'charity'            => __( 'Charity', 'ai-awareness-day' ),
        'public_body'        => __( 'Public body', 'ai-awareness-day' ),
        'institution'        => __( 'Institution', 'ai-awareness-day' ),
        'company'            => __( 'Company', 'ai-awareness-day' ),
        'education_provider' => __( 'Education provider', 'ai-awareness-day' ),
        'other'              => __( 'Other', 'ai-awareness-day' ),
    );
}

/**
 * Extract YouTube video ID from URL (watch, youtu.be, or embed)
 */
function aiad_youtube_video_id( string $url ): string {
    if ( empty( $url ) ) {
        return '';
    }
    $url = trim( $url );
    // youtu.be/VIDEO_ID (with or without query string)
    if ( preg_match( '#youtu\.be/([a-zA-Z0-9_-]{11})#', $url, $m ) ) {
        return $m[1];
    }
    // youtube.com/watch?v=VIDEO_ID or youtube.com/embed/VIDEO_ID
    if ( preg_match( '#(?:youtube\.com/watch\?v=|youtube\.com/embed/)([a-zA-Z0-9_-]{11})#', $url, $m ) ) {
        return $m[1];
    }
    // Raw 11-char ID
    if ( preg_match( '/^[a-zA-Z0-9_-]{11}$/', $url ) ) {
        return $url;
    }
    return '';
}

/**
 * Resource download button label from file URL (e.g. "Download PDF", "Download PPTX").
 * Used on frontend (single-resource.php, archive-resource.php) and in AJAX filter handler.
 *
 * @param string $url Download file URL.
 * @return string Translated label.
 */
function aiad_resource_download_label( string $url ): string {
    if ( ! $url ) {
        return __( 'Download', 'ai-awareness-day' );
    }
    $path = wp_parse_url( $url, PHP_URL_PATH );
    if ( $path && preg_match( '/\.(pdf|pptx?)$/i', $path, $m ) ) {
        return $m[1] === 'pdf' ? __( 'Download PDF', 'ai-awareness-day' ) : __( 'Download PPTX', 'ai-awareness-day' );
    }
    return __( 'Download', 'ai-awareness-day' );
}

/**
 * Normalise learning objectives to array of { objective } (Activity Schema v1).
 * Used on frontend (single-resource.php) and in admin (meta box).
 *
 * @param mixed $raw Meta value.
 * @return array<int, array{objective: string}>
 */
function aiad_normalise_learning_objectives( $raw ): array {
    if ( is_string( $raw ) && $raw !== '' ) {
        if ( is_serialized( $raw ) ) {
            $raw = maybe_unserialize( $raw );
        } elseif ( isset( $raw[0] ) && in_array( $raw[0], array( '[', '{' ), true ) ) {
            $decoded = json_decode( $raw, true );
            if ( is_array( $decoded ) ) {
                $raw = $decoded;
            }
        }
    }
    if ( ! is_array( $raw ) ) {
        if ( is_string( $raw ) && $raw !== '' ) {
            $lines = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ) ) );
            return array_map( function ( $line ) {
                return array( 'objective' => $line );
            }, $lines );
        }
        return array();
    }
    $out = array();
    foreach ( $raw as $item ) {
        if ( is_array( $item ) && isset( $item['objective'] ) ) {
            $out[] = array(
                'objective'  => isset( $item['objective'] ) ? (string) $item['objective'] : '',
            );
        } elseif ( is_string( $item ) && $item !== '' ) {
            $out[] = array( 'objective' => $item );
        }
    }
    return $out;
}

/**
 * Get total school pledges recorded from contact form submissions.
 *
 * @return int
 */
function aiad_get_school_pledge_count(): int {
    return max( 0, (int) get_option( 'aiad_school_pledge_count', 0 ) );
}

/**
 * Campaign target used for the pledge progress bar display.
 *
 * @return int
 */
function aiad_get_school_pledge_goal(): int {
    return (int) apply_filters( 'aiad_school_pledge_goal', 500 );
}

/**
 * Increment the school pledge count when a teacher or school leader submits the form.
 * Returns the updated count.
 *
 * @param string $involved_as The role from the contact form.
 * @return int Updated pledge count.
 */
function aiad_maybe_increment_school_pledge_count( string $involved_as ): int {
    if ( ! in_array( $involved_as, array( 'teacher', 'school_leader' ), true ) ) {
        return aiad_get_school_pledge_count();
    }
    $current = aiad_get_school_pledge_count();
    $updated = $current + 1;
    update_option( 'aiad_school_pledge_count', $updated, false );
    return $updated;
}

/**
 * Normalise resource key stages from raw meta or POST input (flat list of slugs).
 *
 * @param mixed $raw Post meta value, POST array, or legacy encoded string.
 * @return list<string>
 */
function aiad_normalize_resource_key_stages( $raw ): array {
	if ( ! is_array( $raw ) ) {
		if ( is_string( $raw ) && $raw !== '' ) {
			$unserialized = maybe_unserialize( $raw );
			$raw          = is_array( $unserialized ) ? $unserialized : array( $raw );
		} else {
			return array();
		}
	}

	$allowed = function_exists( 'aiad_key_stage_options' ) ? array_keys( aiad_key_stage_options() ) : array();
	$stages  = array();

	foreach ( $raw as $item ) {
		if ( is_array( $item ) ) {
			foreach ( $item as $sub ) {
				if ( is_string( $sub ) && $sub !== '' ) {
					$stages[] = sanitize_text_field( $sub );
				}
			}
		} elseif ( is_string( $item ) && $item !== '' ) {
			$stages[] = sanitize_text_field( $item );
		}
	}

	$stages = array_values( array_unique( $stages ) );
	if ( ! empty( $allowed ) ) {
		$stages = array_values( array_intersect( $stages, $allowed ) );
	}

	return $stages;
}

/**
 * Resource key stages for a post (single flat array in meta).
 *
 * @param int $post_id Resource post ID.
 * @return list<string>
 */
function aiad_get_resource_key_stages( int $post_id ): array {
	return aiad_normalize_resource_key_stages( get_post_meta( $post_id, '_aiad_key_stage', true ) );
}

/**
 * Normalise instructions to array of step objects (Activity Schema v1).
 * Used on frontend (single-resource.php) and in admin (meta box).
 *
 * @param mixed $raw Meta value.
 * @return array<int, array{step: int, action: string, duration?: string, resource_ref?: string, student_action?: string, teacher_tip?: string}>
 */
function aiad_normalise_instructions( $raw ): array {
    if ( is_string( $raw ) && $raw !== '' ) {
        if ( is_serialized( $raw ) ) {
            $raw = maybe_unserialize( $raw );
        } elseif ( isset( $raw[0] ) && in_array( $raw[0], array( '[', '{' ), true ) ) {
            $decoded = json_decode( $raw, true );
            if ( is_array( $decoded ) ) {
                $raw = $decoded;
            }
        }
    }
    if ( ! is_array( $raw ) ) {
        if ( is_string( $raw ) && $raw !== '' ) {
            $lines = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ) ) );
            $out  = array();
            foreach ( $lines as $i => $line ) {
                $out[] = array( 'step' => $i + 1, 'action' => $line );
            }
            return $out;
        }
        return array();
    }
    $out = array();
    $step = 1;
    foreach ( $raw as $item ) {
        if ( is_array( $item ) && isset( $item['action'] ) ) {
            $out[] = array(
                'step'           => isset( $item['step'] ) ? max( 1, (int) $item['step'] ) : $step,
                'action'         => (string) $item['action'],
                'duration'       => isset( $item['duration'] ) ? (string) $item['duration'] : '',
                'resource_ref'   => isset( $item['resource_ref'] ) ? (string) $item['resource_ref'] : '',
                'student_action' => isset( $item['student_action'] ) ? (string) $item['student_action'] : '',
                'teacher_tip'    => isset( $item['teacher_tip'] ) ? (string) $item['teacher_tip'] : '',
            );
            $step = $out[ count( $out ) - 1 ]['step'] + 1;
        } elseif ( is_string( $item ) && $item !== '' ) {
            $out[] = array( 'step' => $step++, 'action' => $item );
        }
    }
    return $out;
}
