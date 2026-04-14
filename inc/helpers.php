<?php
/**
 * Helper functions: duration badge, explore cards, get post by title, key stage options,
 * YouTube ID, resource content normalisers (learning objectives, instructions), and
 * resource download label (frontend + admin).
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Default URL for the front-page "Sample Letters & Communications" toolkit card.
 * Keep this empty in code so secrets/expiring presigned URLs are never committed.
 * Set via Appearance > Customize > Toolkit Section (prefer Media Library URLs).
 *
 * @return string
 */
function aiad_default_sample_letters_url(): string {
    return '';
}

/**
 * Duration badge label (slug or term → "Lesson Starters (5-min)" style)
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
 * Explore section: session length cards (icon, title, description, badge)
 * Keys match resource_duration slugs.
 *
 * @return array<string, array{title: string, description: string, badge_short: string, icon_bg: string, icon: string, status?: string, status_live?: bool}>
 */
function aiad_explore_session_cards(): array {
    return array(
        '5-min-lesson-starters' => array(
            'title'       => __( 'Lesson Starter', 'ai-awareness-day' ),
            'description' => __( 'Quick 5-minute AI discussions to kick off any lesson', 'ai-awareness-day' ),
            'badge_short' => '5 min',
            'icon_bg'     => '#93c5fd',
            'icon'        => 'clock',
            'status'      => __( 'Live', 'ai-awareness-day' ),
            'status_live' => true,
        ),
        '15-20-min-tutor-time' => array(
            'title'       => __( 'Tutor Time', 'ai-awareness-day' ),
            'description' => __( '15 minute group activities for form time', 'ai-awareness-day' ),
            'badge_short' => '15 min',
            'icon_bg'     => '#86efac',
            'icon'        => 'people',
            'status'      => __( 'March 2026', 'ai-awareness-day' ),
            'status_live' => false,
        ),
        '20-min-assemblies' => array(
            'title'       => __( 'Assembly', 'ai-awareness-day' ),
            'description' => __( '20 minute whole-school presentations', 'ai-awareness-day' ),
            'badge_short' => '20 min',
            'icon_bg'     => '#c4b5fd',
            'icon'        => 'presentation',
            'status'      => __( 'April 2026', 'ai-awareness-day' ),
            'status_live' => false,
        ),
        '30-45-min-after-school' => array(
            'title'       => __( 'After School', 'ai-awareness-day' ),
            'description' => __( '30 minute hands-on projects and activities', 'ai-awareness-day' ),
            'badge_short' => '30 min',
            'icon_bg'     => '#fdba74',
            'icon'        => 'book',
            'status'      => __( 'April 2026', 'ai-awareness-day' ),
            'status_live' => false,
        ),
    );
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
 * Get default values for customizer settings.
 *
 * @return array<string, mixed> Array of setting names => default values.
 */
function aiad_get_customizer_defaults(): array {
    static $defaults = null;
    if ( null !== $defaults ) {
        return $defaults;
    }
    $defaults = array(
        'aiad_hero_logo'         => '',
        'aiad_hero_slogan'        => __( 'Know it, Question it, Use it Wisely', 'ai-awareness-day' ),
        'aiad_hero_title'         => __( 'AI Awareness Day', 'ai-awareness-day' ),
        'aiad_hero_date'          => __( 'Thursday 4th June 2026', 'ai-awareness-day' ),
        'aiad_hero_subtitle'      => __( 'A nationwide day for schools, students, and parents to explore AI together.', 'ai-awareness-day' ),
        'aiad_campaign_title'     => __( 'What is AI Awareness Day?', 'ai-awareness-day' ),
        'aiad_campaign_text'      => __( 'National AI Awareness Day (4th June 2026) is a new nationwide campaign designed to build AI literacy across UK schools. The model is simple: schools commit to running just one activity.', 'ai-awareness-day' ),
        'aiad_campaign_text_2'    => __( 'Our goal is to create a unified moment where the entire education community comes together to engage positively and critically with AI — preparing the next generation for a world increasingly shaped by intelligent technology.', 'ai-awareness-day' ),
        'aiad_campaign_linkedin_embed_src' => '',
        'aiad_youtube_url'        => '',
        'aiad_youtube_title'      => __( 'Watch', 'ai-awareness-day' ),
        'aiad_contact_title'      => __( 'Get Involved', 'ai-awareness-day' ),
        'aiad_contact_desc'       => __( 'Whether you\'re a teacher, school leader, parent, or organisation — we\'d love to hear from you. Join the movement and help shape how the next generation engages with AI.', 'ai-awareness-day' ),
        'aiad_contact_email'      => '',
        'aiad_linkedin'           => 'https://www.linkedin.com/company/110126438/',
        'aiad_instagram'          => '#',
        'aiad_linkedin_post_url'   => '',
    );
    return $defaults;
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
