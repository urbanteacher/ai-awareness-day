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

    // Taxonomy: Resource Type (Lesson Starter, Lesson Activity, Assembly) – linked to Themes
    register_taxonomy( 'resource_type', array( 'resource', 'featured_resource' ), array(
        'labels'            => array(
            'name'          => __( 'Resource Types', 'ai-awareness-day' ),
            'singular_name' => __( 'Resource Type', 'ai-awareness-day' ),
            'add_new_item'  => __( 'Add New Resource Type', 'ai-awareness-day' ),
            'description'   => __( 'Format of the resource. Used with Themes (Safe, Smart, Creative, Responsible, Future).', 'ai-awareness-day' ),
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
    ) );

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

    // Taxonomy: Duration / Session length (5-min, 15-20 min, 20-min, 30-45 min)
    register_taxonomy( 'resource_duration', array( 'resource', 'featured_resource' ), array(
        'labels'            => array(
            'name'          => __( 'Session length', 'ai-awareness-day' ),
            'singular_name' => __( 'Session length', 'ai-awareness-day' ),
            'add_new_item'  => __( 'Add New Session length', 'ai-awareness-day' ),
            'description'   => __( 'Time allocated for this resource. Shown in the Explore section.', 'ai-awareness-day' ),
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

    // CPT: Resource (Lesson starter, Lesson activity, Assembly)
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
        'show_ui'      => true,
        'show_in_menu' => true,
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
 * - Resource post type: _resource_* for download URLs, _aiad_* for resource-specific fields
 * - Featured resource post type: _featured_resource_* prefix (see inc/meta-boxes.php)
 * - Partner post type: _partner_* prefix (see inc/meta-boxes.php)
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

    $array_sections = array( '_aiad_discussion_prompts' );
    foreach ( $array_sections as $key ) {
        register_post_meta( 'resource', $key, array(
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
    }

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
                        'assessable' => array( 'type' => 'boolean' ),
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
 * Pre-populate Resource Type and Theme terms (Themes = Safe, Smart, Creative, Responsible, Future)
 */
function aiad_default_terms(): void {
    if ( get_option( 'aiad_terms_seeded' ) ) {
        return;
    }

    $resource_types = array( 'Lesson Starter', 'Lesson Activity', 'Assembly' );
    foreach ( $resource_types as $name ) {
        if ( ! term_exists( $name, 'resource_type' ) ) {
            wp_insert_term( $name, 'resource_type' );
        }
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
        'Lesson Starters (5-min)'           => array( 'slug' => '5-min-lesson-starters' ),
        'Tutor time plans (15–20 min)'      => array( 'slug' => '15-20-min-tutor-time' ),
        'Assemblies (20-min)'               => array( 'slug' => '20-min-assemblies' ),
        'After-school sessions (30–45 min)' => array( 'slug' => '30-45-min-after-school' ),
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
