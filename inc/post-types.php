<?php
/**
 * Custom Post Types, taxonomies, resource meta registration, and term seeding.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Custom Post Types: Resources & Partners
 */
function aiad_register_post_types(): void {

    // Legacy Format taxonomy — registered only until one-time migration merges into Session length (resource_duration).
    if ( ! get_option( 'aiad_resource_type_merged_v2' ) ) {
        register_taxonomy(
            'resource_type',
            array( 'resource', 'featured_resource' ),
            array(
                'labels'            => array(
                    'name'          => __( 'Resource Types (legacy)', 'ai-awareness-day' ),
                    'singular_name' => __( 'Resource Type', 'ai-awareness-day' ),
                ),
                'hierarchical'      => true,
                'public'            => false,
                'show_ui'           => false,
                'show_admin_column' => false,
            )
        );
    }

    // Taxonomy: Themes (Safe, Smart, Creative, Responsible, Future) – same as site Themes section
    // Note: Taxonomy slug is 'resource_principle' but UI label is 'Themes' for clarity.
    // This taxonomy stores the five core themes/principles that resources are categorized under.
    register_taxonomy( 'resource_principle', array( 'resource', 'featured_resource' ), array(
        'labels'            => array(
            'name'          => __( 'Themes', 'ai-awareness-day' ),
            'singular_name' => __( 'Theme', 'ai-awareness-day' ),
            'add_new_item'  => __( 'Add New Theme', 'ai-awareness-day' ),
            'description'   => __( 'Thematic area for this resource. Links to the Explore the Themes section.', 'ai-awareness-day' ),
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
    ) );

    // Taxonomy: Session length (slot + time — e.g. Lesson Starter 5 min, Assembly 20 min)
    register_taxonomy( 'resource_duration', array( 'resource', 'featured_resource' ), array(
        'labels'            => array(
            'name'          => __( 'Session length', 'ai-awareness-day' ),
            'singular_name' => __( 'Session length', 'ai-awareness-day' ),
            'add_new_item'  => __( 'Add New Session length', 'ai-awareness-day' ),
            'description'   => __( 'Where this fits in the day and how long it runs (starter, tutor time, assembly, etc.).', 'ai-awareness-day' ),
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
    ) );

    // Taxonomy: Activity Type (Discussion, Quiz, Video, Hands-On, etc.)
    register_taxonomy( 'activity_type', array( 'resource', 'featured_resource' ), array(
        'labels'            => array(
            'name'          => __( 'Activity Types', 'ai-awareness-day' ),
            'singular_name' => __( 'Activity Type', 'ai-awareness-day' ),
            'add_new_item'  => __( 'Add New Activity Type', 'ai-awareness-day' ),
            'description'   => __( 'What kind of activity is this? (Discussion, Quiz, Hands-on, etc.)', 'ai-awareness-day' ),
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
    ) );

    // CPT: Resource (activities and materials; categorised by session length, theme, etc.)
    register_post_type( 'resource', array(
        'labels'       => array(
            'name'               => __( 'Resources', 'ai-awareness-day' ),
            'singular_name'      => __( 'Resource', 'ai-awareness-day' ),
            'add_new'            => __( 'Add New', 'ai-awareness-day' ),
            'add_new_item'       => __( 'Add New Resource', 'ai-awareness-day' ),
            'edit_item'          => __( 'Edit Resource', 'ai-awareness-day' ),
            'view_item'          => __( 'View Resource', 'ai-awareness-day' ),
        ),
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => array( 'slug' => 'resources' ),
        'menu_icon'    => 'dashicons-media-document',
        'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
        'show_in_rest' => true,
    ) );

    // Taxonomy: Partner Type (Teacher, Sponsor, School, Tech Company)
    register_taxonomy( 'partner_type', array( 'partner' ), array(
        'labels'            => array(
            'name'          => __( 'Partner Types', 'ai-awareness-day' ),
            'singular_name' => __( 'Partner Type', 'ai-awareness-day' ),
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
    ) );

    // CPT: Partner (Teachers, Sponsors, Schools, Tech companies – name + logo)
    register_post_type( 'partner', array(
        'labels'       => array(
            'name'               => __( 'Partners', 'ai-awareness-day' ),
            'singular_name'      => __( 'Partner', 'ai-awareness-day' ),
            'add_new'            => __( 'Add New', 'ai-awareness-day' ),
            'add_new_item'       => __( 'Add New Partner', 'ai-awareness-day' ),
        ),
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => array( 'slug' => 'partners' ),
        'menu_icon'    => 'dashicons-groups',
        'supports'     => array( 'title', 'thumbnail', 'editor' ),
        'show_in_rest' => true,
    ) );

    // CPT: Featured resource (from other organisations – external links)
    register_post_type( 'featured_resource', array(
        'labels'       => array(
            'name'               => __( 'Resources from partners', 'ai-awareness-day' ),
            'singular_name'      => __( 'Resource from partner', 'ai-awareness-day' ),
            'add_new'            => __( 'Add New', 'ai-awareness-day' ),
            'add_new_item'       => __( 'Add resource from another org', 'ai-awareness-day' ),
        ),
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => array( 'slug' => 'from-partners' ),
        'menu_icon'    => 'dashicons-share',
        'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
        'show_in_rest' => true,
    ) );

    // CPT: Form Submission (for storing contact form submissions)
    register_post_type( 'form_submission', array(
        'labels'       => array(
            'name'               => __( 'Form Submissions', 'ai-awareness-day' ),
            'singular_name'      => __( 'Form Submission', 'ai-awareness-day' ),
            'add_new'            => __( 'Add New', 'ai-awareness-day' ),
            'add_new_item'       => __( 'Add New Submission', 'ai-awareness-day' ),
            'edit_item'          => __( 'View Submission', 'ai-awareness-day' ),
            'view_item'          => __( 'View Submission', 'ai-awareness-day' ),
            'all_items'          => __( 'All Submissions', 'ai-awareness-day' ),
            'search_items'       => __( 'Search Submissions', 'ai-awareness-day' ),
        ),
        'public'       => false,
        'publicly_queryable' => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'show_in_rest' => false,
        'menu_icon'    => 'dashicons-email-alt',
        'supports'     => array( 'title', 'editor' ),
        'capability_type' => 'post',
        'capabilities' => array(
            'create_posts' => false, // Disable creating new posts manually
        ),
        'map_meta_cap' => true,
    ) );
}
add_action( 'init', 'aiad_register_post_types' );

/**
 * Register post meta fields for resources.
 * 
 * Meta field naming conventions:
 * - Resource post type: _aiad_* prefix for all resource-specific fields (including _aiad_download_url)
 * - Featured resource post type: _featured_resource_* prefix (see inc/meta-boxes.php)
 * - Partner post type: _partner_* prefix (see inc/meta-boxes.php)
 * - Timeline post type: _aiad_timeline_* prefix (see inc/timeline.php)
 * 
 * @see aiad_register_post_types() for post type definitions
 */
function aiad_register_resource_meta(): void {
    register_post_meta( 'resource', '_aiad_key_stage', array(
        'type'          => 'array',
        'single'        => true,
        'default'       => array(),
        'show_in_rest'  => array(
            'schema' => array(
                'type'  => 'array',
                'items' => array( 'type' => 'string' ),
            ),
        ),
        'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
    ) );

    register_post_meta( 'resource', '_aiad_subtitle', array(
        'type'          => 'string',
        'single'        => true,
        'default'       => '',
        'show_in_rest'  => true,
        'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
    ) );
    register_post_meta( 'resource', '_aiad_duration', array(
        'type'          => 'string',
        'single'        => true,
        'default'       => '',
        'show_in_rest'  => true,
        'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
    ) );
    register_post_meta( 'resource', '_aiad_level', array(
        'type'          => 'string',
        'single'        => true,
        'default'       => '',
        'show_in_rest'  => true,
        'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
    ) );
    register_post_meta( 'resource', '_aiad_status', array(
        'type'          => 'string',
        'single'        => true,
        'default'       => 'draft',
        'show_in_rest'  => true,
        'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
    ) );
    register_post_meta( 'resource', '_aiad_preparation', array(
        'type'          => 'array',
        'single'        => true,
        'default'       => array(),
        'show_in_rest'  => array(
            'schema' => array(
                'type'  => 'array',
                'items' => array( 'type' => 'string' ),
            ),
        ),
        'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
    ) );
    register_post_meta( 'resource', '_aiad_differentiation', array(
        'type'          => 'object',
        'single'        => true,
        'default'       => array( 'support' => '', 'stretch' => '', 'send' => '' ),
        'show_in_rest'  => array(
            'schema' => array(
                'type'       => 'object',
                'properties' => array(
                    'support' => array( 'type' => 'string' ),
                    'stretch' => array( 'type' => 'string' ),
                    'send'    => array( 'type' => 'string' ),
                ),
            ),
        ),
        'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
    ) );
    register_post_meta( 'resource', '_aiad_extensions', array(
        'type'          => 'array',
        'single'        => true,
        'default'       => array(),
        'show_in_rest'  => array(
            'schema' => array(
                'type'  => 'array',
                'items' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'activity' => array( 'type' => 'string' ),
                        'type'     => array( 'type' => 'string' ),
                    ),
                ),
            ),
        ),
        'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
    ) );
    register_post_meta( 'resource', '_aiad_resources', array(
        'type'          => 'array',
        'single'        => true,
        'default'       => array(),
        'show_in_rest'  => array(
            'schema' => array(
                'type'  => 'array',
                'items' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'name' => array( 'type' => 'string' ),
                        'type' => array( 'type' => 'string' ),
                        'url'  => array( 'type' => 'string' ),
                    ),
                ),
            ),
        ),
        'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
    ) );

    register_post_meta( 'resource', '_aiad_discussion_question', array(
        'type'          => 'string',
        'single'        => true,
        'default'       => '',
        'show_in_rest'  => true,
        'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
    ) );
    register_post_meta( 'resource', '_aiad_teacher_notes', array(
        'type'          => 'string',
        'single'        => true,
        'default'       => '',
        'show_in_rest'  => true,
        'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
    ) );

    register_post_meta( 'resource', '_aiad_learning_objectives', array(
        'type'          => 'array',
        'single'        => true,
        'default'       => array(),
        'show_in_rest'  => array(
            'schema' => array(
                'type'  => 'array',
                'items' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'objective'  => array( 'type' => 'string' ),
                    ),
                ),
            ),
        ),
        'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
    ) );
    register_post_meta( 'resource', '_aiad_instructions', array(
        'type'          => 'array',
        'single'        => true,
        'default'       => array(),
        'show_in_rest'  => array(
            'schema' => array(
                'type'  => 'array',
                'items' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'step'          => array( 'type' => 'integer' ),
                        'action'        => array( 'type' => 'string' ),
                        'duration'      => array( 'type' => 'string' ),
                        'resource_ref'  => array( 'type' => 'string' ),
                        'student_action'=> array( 'type' => 'string' ),
                        'teacher_tip'   => array( 'type' => 'string' ),
                    ),
                ),
            ),
        ),
        'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
    ) );

    register_post_meta( 'resource', '_aiad_key_definitions', array(
        'type'          => 'array',
        'single'        => true,
        'default'       => array(),
        'show_in_rest'  => array(
            'schema' => array(
                'type'  => 'array',
                'items' => array(
                    'type'       => 'object',
                    'properties' => array(
                        'term'              => array( 'type' => 'string' ),
                        'definition'        => array( 'type' => 'string' ),
                        'key_stage_adapted' => array( 'type' => 'boolean' ),
                    ),
                ),
            ),
        ),
        'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
    ) );
}
add_action( 'init', 'aiad_register_resource_meta', 15 );

/**
 * Migrate old meta keys to new naming convention.
 * 
 * Migrations:
 * - _resource_download_url → _aiad_download_url (consistency with other resource meta)
 * - aiad_homepage_handpicked_resource_* → aiad_handpicked_resource_* (simplified naming)
 * - aiad_homepage_free_resource_* → aiad_free_resource_* (simplified naming)
 * 
 * @since 1.1.0
 */
function aiad_migrate_meta_keys(): void {
    if ( get_option( 'aiad_meta_migration_complete' ) ) {
        return;
    }

    // Migrate _resource_download_url → _aiad_download_url
    $resources = get_posts( array(
        'post_type'      => 'resource',
        'posts_per_page' => -1,
        'post_status'    => 'any',
        'meta_key'       => '_resource_download_url',
        'meta_compare'   => 'EXISTS',
    ) );

    foreach ( $resources as $post ) {
        $old_value = get_post_meta( $post->ID, '_resource_download_url', true );
        if ( $old_value ) {
            // Only migrate if new key doesn't exist
            $new_value = get_post_meta( $post->ID, '_aiad_download_url', true );
            if ( ! $new_value ) {
                update_post_meta( $post->ID, '_aiad_download_url', $old_value );
            }
            // Delete old key
            delete_post_meta( $post->ID, '_resource_download_url' );
        }
    }

    // Migrate theme_mod keys: aiad_homepage_handpicked_resource_* → aiad_handpicked_resource_*
    for ( $i = 1; $i <= 3; $i++ ) {
        $old_key = 'aiad_homepage_handpicked_resource_' . $i;
        $new_key = 'aiad_handpicked_resource_' . $i;
        $old_value = get_theme_mod( $old_key );
        if ( $old_value && ! get_theme_mod( $new_key ) ) {
            set_theme_mod( $new_key, $old_value );
            remove_theme_mod( $old_key );
        }
    }

    // Migrate theme_mod keys: aiad_homepage_free_resource_* → aiad_free_resource_*
    for ( $i = 1; $i <= 6; $i++ ) {
        $old_key = 'aiad_homepage_free_resource_' . $i;
        $new_key = 'aiad_free_resource_' . $i;
        $old_value = get_theme_mod( $old_key );
        if ( $old_value && ! get_theme_mod( $new_key ) ) {
            set_theme_mod( $new_key, $old_value );
            remove_theme_mod( $old_key );
        }
    }

    update_option( 'aiad_meta_migration_complete', true );
}
add_action( 'admin_init', 'aiad_migrate_meta_keys' );

/**
 * Pre-populate Resource Type and Theme terms (Themes = Safe, Smart, Creative, Responsible, Future)
 */
function aiad_default_terms(): void {
    if ( get_option( 'aiad_terms_seeded' ) ) {
        return;
    }

    $themes = array( 'Safe', 'Smart', 'Creative', 'Responsible', 'Future' );
    foreach ( $themes as $name ) {
        if ( ! term_exists( $name, 'resource_principle' ) ) {
            wp_insert_term( $name, 'resource_principle' );
        }
    }

    $partner_types = array( 'Teacher', 'Sponsor', 'School', 'Tech Company' );
    foreach ( $partner_types as $name ) {
        if ( ! term_exists( $name, 'partner_type' ) ) {
            wp_insert_term( $name, 'partner_type' );
        }
    }

    update_option( 'aiad_terms_seeded', true );
}
add_action( 'init', 'aiad_default_terms', 20 );

/**
 * Pre-populate Resource Duration terms (Explore section – badge format)
 */
function aiad_duration_terms(): void {
    if ( get_option( 'aiad_duration_terms_seeded' ) ) {
        return;
    }
    $durations = array(
        'Lesson Starter (5 min)'    => array( 'slug' => '5-min-lesson-starters' ),
        'Tutor Time (15 min)'       => array( 'slug' => '15-20-min-tutor-time' ),
        'Assembly (20 min)'        => array( 'slug' => '20-min-assemblies' ),
        'After School (30 min)'    => array( 'slug' => '30-45-min-after-school' ),
    );
    foreach ( $durations as $name => $opts ) {
        if ( ! term_exists( $opts['slug'], 'resource_duration' ) ) {
            wp_insert_term( $name, 'resource_duration', array( 'slug' => $opts['slug'] ) );
        }
    }
    update_option( 'aiad_duration_terms_seeded', true );
}
add_action( 'init', 'aiad_duration_terms', 21 );

/**
 * One-time: merge old Format (resource_type) into Session length (resource_duration), then drop resource_type.
 */
function aiad_migrate_resource_type_into_session_length(): void {
    if ( get_option( 'aiad_resource_type_merged_v2' ) ) {
        return;
    }
    if ( ! taxonomy_exists( 'resource_duration' ) ) {
        return;
    }
    if ( taxonomy_exists( 'resource_type' ) && function_exists( 'aiad_resource_type_term_to_duration_slugs' ) ) {
        $ptypes = array( 'resource', 'featured_resource' );
        foreach ( $ptypes as $pt ) {
            $ids = get_posts(
                array(
                    'post_type'      => $pt,
                    'post_status'    => 'any',
                    'posts_per_page' => -1,
                    'fields'         => 'ids',
                )
            );
            foreach ( $ids as $post_id ) {
                $old_types = wp_get_object_terms( $post_id, 'resource_type' );
                if ( is_wp_error( $old_types ) ) {
                    continue;
                }
                if ( empty( $old_types ) ) {
                    wp_set_object_terms( $post_id, array(), 'resource_type' );
                    continue;
                }
                $add_slugs = array();
                foreach ( $old_types as $t ) {
                    $add_slugs = array_merge( $add_slugs, aiad_resource_type_term_to_duration_slugs( $t ) );
                }
                $current = wp_get_object_terms( $post_id, 'resource_duration', array( 'fields' => 'slugs' ) );
                if ( is_wp_error( $current ) ) {
                    $current = array();
                }
                $merged = array_values( array_unique( array_merge( $current, $add_slugs ) ) );
                wp_set_object_terms( $post_id, $merged, 'resource_duration' );
                wp_set_object_terms( $post_id, array(), 'resource_type' );
            }
        }
        $terms = get_terms(
            array(
                'taxonomy'   => 'resource_type',
                'hide_empty' => false,
            )
        );
        if ( ! is_wp_error( $terms ) ) {
            foreach ( $terms as $t ) {
                wp_delete_term( (int) $t->term_id, 'resource_type' );
            }
        }
        unregister_taxonomy_for_object_type( 'resource_type', 'resource' );
        unregister_taxonomy_for_object_type( 'resource_type', 'featured_resource' );
    }

    update_option( 'aiad_resource_type_merged_v2', true );
    if ( function_exists( 'aiad_bump_filter_counts_version' ) ) {
        aiad_bump_filter_counts_version();
    }
}
add_action( 'init', 'aiad_migrate_resource_type_into_session_length', 100 );

/**
 * One-time merge of duplicate "5 minute lesson starter" session-length terms into the canonical slug.
 * Fixes sites where an extra term (e.g. "Lesson Starters (5-min)") was created manually or by an old import,
 * so filters and admin radios show a single option and all resources resolve under one term.
 */
function aiad_merge_duplicate_resource_duration_terms(): void {
    if ( get_option( 'aiad_duration_duplicate_merge_v1' ) ) {
        return;
    }
    if ( ! taxonomy_exists( 'resource_duration' ) ) {
        return;
    }

    $canonical = get_term_by( 'slug', '5-min-lesson-starters', 'resource_duration' );
    if ( ! $canonical || is_wp_error( $canonical ) ) {
        return;
    }

    $dup_slugs = array(
        'lesson-starters-5-min',
        'lesson-starters-5min',
        'lesson-starter-5-min',
        'lesson-starter-5min',
    );
    $dup_names_lower = array(
        'lesson starters (5-min)',
        'lesson starters (5 min)',
        'lesson starter (5-min)',
    );

    $terms = get_terms(
        array(
            'taxonomy'   => 'resource_duration',
            'hide_empty' => false,
        )
    );
    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        update_option( 'aiad_duration_duplicate_merge_v1', true );
        return;
    }

    $canonical_id = (int) $canonical->term_id;
    $duplicate_ids  = array();

    foreach ( $terms as $term ) {
        if ( (int) $term->term_id === $canonical_id ) {
            continue;
        }
        if ( in_array( $term->slug, $dup_slugs, true ) ) {
            $duplicate_ids[] = (int) $term->term_id;
            continue;
        }
        $n = mb_strtolower( trim( $term->name ), 'UTF-8' );
        if ( in_array( $n, $dup_names_lower, true ) ) {
            $duplicate_ids[] = (int) $term->term_id;
        }
    }
    $duplicate_ids = array_unique( array_filter( $duplicate_ids ) );

    foreach ( $duplicate_ids as $dup_id ) {
        aiad_reassign_posts_from_term_to_term( $dup_id, $canonical_id, 'resource_duration' );
        if ( function_exists( 'wp_delete_term' ) ) {
            wp_delete_term( $dup_id, 'resource_duration' );
        }
    }

    update_option( 'aiad_duration_duplicate_merge_v1', true );
}

/**
 * Move all objects using $from_term_id to use $to_term_id for the given taxonomy (replaces in term list).
 *
 * @param int    $from_term_id Source term ID.
 * @param int    $to_term_id   Target term ID.
 * @param string $taxonomy     Taxonomy name.
 */
function aiad_reassign_posts_from_term_to_term( int $from_term_id, int $to_term_id, string $taxonomy ): void {
    $tax = get_taxonomy( $taxonomy );
    if ( ! $tax ) {
        return;
    }
    foreach ( $tax->object_type as $post_type ) {
        $post_ids = get_posts(
            array(
                'post_type'      => $post_type,
                'posts_per_page'   => -1,
                'fields'           => 'ids',
                'post_status'      => 'any',
                'suppress_filters' => true,
                'tax_query'        => array(
                    array(
                        'taxonomy' => $taxonomy,
                        'field'    => 'term_id',
                        'terms'    => $from_term_id,
                    ),
                ),
            )
        );
        foreach ( $post_ids as $post_id ) {
            $current = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
            if ( is_wp_error( $current ) ) {
                continue;
            }
            $current   = array_map( 'intval', $current );
            $current   = array_values( array_unique( array_diff( $current, array( $from_term_id ) ) ) );
            $current[] = $to_term_id;
            $current   = array_values( array_unique( $current ) );
            wp_set_object_terms( $post_id, $current, $taxonomy, false );
        }
    }
}
add_action( 'init', 'aiad_merge_duplicate_resource_duration_terms', 99 );

/**
 * Pre-populate Activity Type terms
 */
function aiad_activity_type_terms(): void {
    if ( get_option( 'aiad_activity_type_terms_seeded' ) ) {
        return;
    }
    $types = array(
        'Discussion'    => 'discussion',
        'Quiz'          => 'quiz',
        'Video'         => 'video',
        'Hands-On'      => 'hands-on',
        'Role Play'     => 'role-play',
        'Investigation' => 'investigation',
        'Creative Task' => 'creative-task',
        'Game'          => 'game',
        'Presentation'  => 'presentation',
        'Reflection'    => 'reflection',
    );
    foreach ( $types as $name => $slug ) {
        if ( ! term_exists( $slug, 'activity_type' ) ) {
            wp_insert_term( $name, 'activity_type', array( 'slug' => $slug ) );
        }
    }
    update_option( 'aiad_activity_type_terms_seeded', true );
}
add_action( 'init', 'aiad_activity_type_terms', 22 );

/**
 * Resource Settings: submenu page under Resources in wp-admin.
 */
function aiad_resource_settings_menu(): void {
    add_submenu_page(
        'edit.php?post_type=resource',
        __( 'Resource Settings', 'ai-awareness-day' ),
        __( 'Settings', 'ai-awareness-day' ),
        'manage_options',
        'aiad-resource-settings',
        'aiad_resource_settings_page'
    );
}
add_action( 'admin_menu', 'aiad_resource_settings_menu' );

function aiad_resource_settings_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    if ( isset( $_POST['aiad_resource_settings_nonce'] ) &&
         wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aiad_resource_settings_nonce'] ) ), 'aiad_resource_settings_save' ) ) {
        update_option( 'aiad_show_resource_stats', isset( $_POST['aiad_show_resource_stats'] ) ? 1 : 0 );
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'ai-awareness-day' ) . '</p></div>';
    }
    $show_stats = (bool) get_option( 'aiad_show_resource_stats', 0 );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Resource Settings', 'ai-awareness-day' ); ?></h1>
        <form method="post">
            <?php wp_nonce_field( 'aiad_resource_settings_save', 'aiad_resource_settings_nonce' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Show stats on resource pages', 'ai-awareness-day' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="aiad_show_resource_stats" value="1" <?php checked( $show_stats ); ?> />
                            <?php esc_html_e( 'Display download and preview counts in the resource header', 'ai-awareness-day' ); ?>
                        </label>
                        <p class="description"><?php esc_html_e( 'Recommended to enable once resources have meaningful engagement numbers.', 'ai-awareness-day' ); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
