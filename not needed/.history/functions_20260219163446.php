<?php
/**
 * AI Awareness Day Theme Functions
 *
 * @package AI_Awareness_Day
 * @version 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'AIAD_VERSION', '1.1.0' );
define( 'AIAD_DIR', get_template_directory() );
define( 'AIAD_URI', get_template_directory_uri() );

if ( is_admin() ) {
    require_once AIAD_DIR . '/admin/class-aiad-homepage-editor.php';
}

/**
 * Theme Setup
 */
function aiad_setup(): void {
    load_theme_textdomain( 'ai-awareness-day', AIAD_DIR . '/languages' );

    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );

    // Custom image sizes
    add_image_size( 'hero-large', 1200, 675, true );
    add_image_size( 'hero-small', 600, 450, true );
    add_image_size( 'theme-thumb', 400, 400, true );

    // Register navigation menus
    register_nav_menus( array(
        'primary' => __( 'Primary Navigation', 'ai-awareness-day' ),
    ) );

    // HTML5 support
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );

    // Custom logo
    add_theme_support( 'custom-logo', array(
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ) );

    // Block editor: alignments, responsive embeds, block styles (WP 5.9+)
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'align-wide' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'editor-styles' );
    add_editor_style( 'assets/css/editor-style.css' );
    add_theme_support( 'custom-units' );

    // 6.5: Appearance tools for classic themes (margin, padding, border, line-height, etc.)
    add_theme_support( 'appearance-tools' );
}
add_action( 'after_setup_theme', 'aiad_setup' );

/**
 * WordPress 6.9+ compatibility: classic theme block styles
 *
 * WP 6.9 loads block styles on demand in classic themes, which can break layouts
 * when plugins (e.g. Gravity Forms, WooCommerce blocks) expect all block CSS.
 * Uncomment the filter below if you see broken block/plugin layouts after upgrading.
 *
 * @see https://core.trac.wordpress.org/ticket/64099
 * @see https://wordpress.org/support/topic/wp-6-9-1still-have-issue-with-load-block-styles-on-demand-in-classic-themes/
 */
// add_filter( 'should_load_separate_core_block_assets', '__return_false' );

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
 * Register Key Stage post meta for resource (EYFS, KS1–KS5)
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

    $array_sections = array( '_aiad_discussion_prompts', '_aiad_suggested_answers' );
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

/**
 * Duration badge label (slug or term → "Lesson Starters (5-min)" style)
 *
 * @param object|string $term_or_slug WP_Term or duration slug.
 * @return string
 */
function aiad_duration_badge_label( object|string $term_or_slug ): string {
    $slug = is_object( $term_or_slug ) ? $term_or_slug->slug : $term_or_slug;
    $labels = array(
        '5-min-lesson-starters'    => __( 'Lesson Starters (5-min)', 'ai-awareness-day' ),
        '15-20-min-tutor-time'     => __( 'Tutor time plans (15–20 min)', 'ai-awareness-day' ),
        '20-min-assemblies'        => __( 'Assemblies (20-min)', 'ai-awareness-day' ),
        '30-45-min-after-school'   => __( 'After-school sessions (30–45 min)', 'ai-awareness-day' ),
    );
    return isset( $labels[ $slug ] ) ? $labels[ $slug ] : ( is_object( $term_or_slug ) ? $term_or_slug->name : $term_or_slug );
}

/**
 * Explore section: session length cards (icon, title, description, badge)
 * Keys match resource_duration slugs.
 *
 * @return array<string, array{title: string, description: string, badge_short: string, icon_bg: string, icon: string}>
 */
function aiad_explore_session_cards(): array {
    return array(
        '5-min-lesson-starters' => array(
            'title'       => __( 'Lesson Starters', 'ai-awareness-day' ),
            'description' => __( 'Quick 5-minute AI discussions to kick off any lesson', 'ai-awareness-day' ),
            'badge_short' => '5 min',
            'icon_bg'     => '#93c5fd',
            'icon'        => 'clock',
        ),
        '15-20-min-tutor-time' => array(
            'title'       => __( 'Tutor Time', 'ai-awareness-day' ),
            'description' => __( '15-20 minute group activities for form time', 'ai-awareness-day' ),
            'badge_short' => '15-20 min',
            'icon_bg'     => '#86efac',
            'icon'        => 'people',
        ),
        '20-min-assemblies' => array(
            'title'       => __( 'Assemblies', 'ai-awareness-day' ),
            'description' => __( '20-minute whole-school presentations', 'ai-awareness-day' ),
            'badge_short' => '20 min',
            'icon_bg'     => '#c4b5fd',
            'icon'        => 'presentation',
        ),
        '30-45-min-after-school' => array(
            'title'       => __( 'After-School Clubs', 'ai-awareness-day' ),
            'description' => __( '30-45 minute hands-on projects and activities', 'ai-awareness-day' ),
            'badge_short' => '30-45 min',
            'icon_bg'     => '#fdba74',
            'icon'        => 'book',
        ),
    );
}

/**
 * Partner URL meta box
 */
function aiad_partner_url_meta_box(): void {
    add_meta_box(
        'aiad_partner_url',
        __( 'Partner URL', 'ai-awareness-day' ),
        'aiad_partner_url_callback',
        'partner',
        'normal'
    );
}
function aiad_partner_url_callback( WP_Post $post ): void {
    wp_nonce_field( 'aiad_partner_url_nonce', 'aiad_partner_url_nonce' );
    $url = get_post_meta( $post->ID, '_partner_url', true );
    echo '<p><label for="partner_url">' . esc_html__( 'Website URL (optional)', 'ai-awareness-day' ) . '</label><br>';
    echo '<input type="url" id="partner_url" name="partner_url" value="' . esc_attr( $url ) . '" class="widefat"></p>';
}
function aiad_save_partner_url( int $post_id ): void {
    if ( ! isset( $_POST['aiad_partner_url_nonce'] ) || ! wp_verify_nonce( $_POST['aiad_partner_url_nonce'], 'aiad_partner_url_nonce' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( isset( $_POST['partner_url'] ) ) {
        update_post_meta( $post_id, '_partner_url', esc_url_raw( wp_unslash( $_POST['partner_url'] ) ) );
    }
}
add_action( 'add_meta_boxes', 'aiad_partner_url_meta_box' );

/**
 * Partner Stats Meta Box
 */
function aiad_partner_stats_meta_box(): void {
    add_meta_box(
        'aiad_partner_stats',
        __( 'Partner Statistics', 'ai-awareness-day' ),
        'aiad_partner_stats_meta_box_callback',
        'partner',
        'normal',
        'high'
    );
}

function aiad_partner_stats_meta_box_callback( $post ): void {
    wp_nonce_field( 'aiad_partner_stats_save', 'aiad_partner_stats_nonce' );
    $stats = get_post_meta( $post->ID, '_partner_stats', true );
    ?>
    <p>
        <label for="partner_stats"><?php esc_html_e( 'Statistics/Description', 'ai-awareness-day' ); ?></label><br>
        <input type="text" id="partner_stats" name="partner_stats" value="<?php echo esc_attr( $stats ); ?>" style="width: 100%;" placeholder="<?php esc_attr_e( 'e.g., 32,000 students', 'ai-awareness-day' ); ?>">
        <span class="description"><?php esc_html_e( 'Enter statistics or description to display on the Momentum section (e.g., "32,000 students", "20 schools across Bedfordshire")', 'ai-awareness-day' ); ?></span>
    </p>
    <?php
}

function aiad_partner_stats_save( $post_id ): void {
    if ( ! isset( $_POST['aiad_partner_stats_nonce'] ) || ! wp_verify_nonce( $_POST['aiad_partner_stats_nonce'], 'aiad_partner_stats_save' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['partner_stats'] ) ) {
        update_post_meta( $post_id, '_partner_stats', sanitize_text_field( wp_unslash( $_POST['partner_stats'] ) ) );
    }
}
add_action( 'add_meta_boxes', 'aiad_partner_stats_meta_box' );
add_action( 'save_post', 'aiad_partner_stats_save' );
add_action( 'save_post_partner', 'aiad_save_partner_url' );

/**
 * Featured resource (from other orgs): URL + organisation
 */
function aiad_featured_resource_meta_box(): void {
    add_meta_box(
        'aiad_featured_resource',
        __( 'External resource link & attribution', 'ai-awareness-day' ),
        'aiad_featured_resource_callback',
        'featured_resource',
        'normal'
    );
}
function aiad_featured_resource_callback( WP_Post $post ): void {
    wp_nonce_field( 'aiad_featured_resource_nonce', 'aiad_featured_resource_nonce' );
    $url    = get_post_meta( $post->ID, '_featured_resource_url', true );
    $org    = get_post_meta( $post->ID, '_featured_resource_org_name', true );
    $org_url = get_post_meta( $post->ID, '_featured_resource_org_url', true );
    echo '<p><label for="featured_resource_url">' . esc_html__( 'Resource URL *', 'ai-awareness-day' ) . '</label><br>';
    echo '<input type="url" id="featured_resource_url" name="featured_resource_url" value="' . esc_attr( $url ) . '" class="widefat" required placeholder="https://..."></p>';
    echo '<p><label for="featured_resource_org_name">' . esc_html__( 'Organisation name', 'ai-awareness-day' ) . '</label><br>';
    echo '<input type="text" id="featured_resource_org_name" name="featured_resource_org_name" value="' . esc_attr( $org ) . '" class="widefat" placeholder="e.g. STEM Learning"></p>';
    echo '<p><label for="featured_resource_org_url">' . esc_html__( 'Organisation website (optional)', 'ai-awareness-day' ) . '</label><br>';
    echo '<input type="url" id="featured_resource_org_url" name="featured_resource_org_url" value="' . esc_attr( $org_url ) . '" class="widefat" placeholder="https://..."></p>';
}
function aiad_save_featured_resource( int $post_id ): void {
    if ( ! isset( $_POST['aiad_featured_resource_nonce'] ) || ! wp_verify_nonce( $_POST['aiad_featured_resource_nonce'], 'aiad_featured_resource_nonce' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( isset( $_POST['featured_resource_url'] ) ) {
        update_post_meta( $post_id, '_featured_resource_url', esc_url_raw( wp_unslash( $_POST['featured_resource_url'] ) ) );
    }
    if ( isset( $_POST['featured_resource_org_name'] ) ) {
        update_post_meta( $post_id, '_featured_resource_org_name', sanitize_text_field( wp_unslash( $_POST['featured_resource_org_name'] ) ) );
    }
    if ( isset( $_POST['featured_resource_org_url'] ) ) {
        update_post_meta( $post_id, '_featured_resource_org_url', esc_url_raw( wp_unslash( $_POST['featured_resource_org_url'] ) ) );
    }
}
add_action( 'add_meta_boxes', 'aiad_featured_resource_meta_box' );
add_action( 'save_post_featured_resource', 'aiad_save_featured_resource' );

/**
 * Seed default partners (homepage campaign section).
 * Runs once when option is not set and no partner posts exist; creates default partner entries.
 */
function aiad_seed_partners(): void {
    if ( get_option( 'aiad_partners_seeded' ) === 'yes' ) {
        return;
    }

    $existing = get_posts( array(
        'post_type'      => 'partner',
        'posts_per_page' => 1,
        'post_status'    => 'any',
    ) );
    if ( ! empty( $existing ) ) {
        update_option( 'aiad_partners_seeded', 'yes' );
        return;
    }

    $defaults = array(
        array( 'name' => 'Capital City College', 'stats' => '32,000 students' ),
        array( 'name' => 'Chiltern Learning Trust', 'stats' => '20 schools across Bedfordshire and Luton' ),
        array( 'name' => 'Harris Federation', 'stats' => '50 schools, 33,000 students' ),
        array( 'name' => 'E-ACT', 'stats' => '38 academies, 25,000 students' ),
        array( 'name' => 'Apps for Good', 'stats' => '28,000 students annually' ),
        array( 'name' => 'Tech London Advocates', 'stats' => '15,000+ tech leaders' ),
        array( 'name' => 'UKBT Institute', 'stats' => '20,000 tech professionals' ),
        array( 'name' => 'Central District Alliance', 'stats' => '400 businesses in Central London' ),
        array( 'name' => 'Education Links', 'stats' => 'Alternative Provision Free School (Academy)' ),
    );

    foreach ( $defaults as $i => $item ) {
        $post_id = wp_insert_post( array(
            'post_type'   => 'partner',
            'post_title'  => $item['name'],
            'post_status' => 'publish',
            'post_author' => 1,
            'menu_order'  => $i,
        ) );
        if ( $post_id && ! is_wp_error( $post_id ) ) {
            update_post_meta( $post_id, '_partner_stats', $item['stats'] );
        }
    }

    update_option( 'aiad_partners_seeded', 'yes' );
}
add_action( 'init', 'aiad_seed_partners', 20 );

/**
 * Seed default partner resources (interactive AI games and learning tools).
 * Runs once when option is not set; creates 5 featured_resource posts.
 */
function aiad_seed_partner_resources(): void {
    if ( get_option( 'aiad_partner_resources_seeded' ) === 'yes' ) {
        return;
    }

    $items = array(
        array(
            'title'   => 'Quick, Draw!',
            'excerpt' => __( 'Draw everyday objects and watch as an AI tries to guess what you\'re sketching in real time. A fun way to see how machines learn from patterns.', 'ai-awareness-day' ),
            'url'     => 'https://quickdraw.withgoogle.com/',
            'org'     => 'Google',
            'theme'   => 'smart',
        ),
        array(
            'title'   => 'Guess the Line',
            'excerpt' => __( 'Draw imaginative prompts (like styles or abstract ideas) and see if an AI can recognise your artwork. A more artistic twist on AI guessing games.', 'ai-awareness-day' ),
            'url'     => 'https://artsandculture.google.com/experiment/guess-the-line/ogH0ouxdq_sR6w?hl=en',
            'org'     => 'Google Arts & Culture',
            'theme'   => 'creative',
        ),
        array(
            'title'   => 'Quiz: AI or Real?',
            'excerpt' => __( 'Test your ability to tell the difference between human-made and AI-generated content.', 'ai-awareness-day' ),
            'url'     => 'https://www.bbc.co.uk/bitesize/articles/zqnwxg8',
            'org'     => 'BBC Bitesize',
            'theme'   => 'safe',
        ),
        array(
            'title'   => 'Turing Test Live',
            'excerpt' => __( 'Chat and guess: are you talking to a human or an AI? A modern take on a classic AI question.', 'ai-awareness-day' ),
            'url'     => 'https://turingtest.live/',
            'org'     => 'Turing Test',
            'theme'   => 'safe',
        ),
        array(
            'title'   => 'How Could AI Affect Your Job?',
            'excerpt' => __( 'Explore how artificial intelligence may change careers, skills, and workplaces in the future.', 'ai-awareness-day' ),
            'url'     => 'https://www.bbc.co.uk/bitesize/articles/zm9scxs',
            'org'     => 'BBC Bitesize',
            'theme'   => 'future',
        ),
    );

    foreach ( $items as $item ) {
        $post_id = wp_insert_post( array(
            'post_type'    => 'featured_resource',
            'post_title'   => $item['title'],
            'post_excerpt' => $item['excerpt'],
            'post_status'  => 'publish',
            'post_author'  => 1,
        ) );
        if ( $post_id && ! is_wp_error( $post_id ) ) {
            update_post_meta( $post_id, '_featured_resource_url', $item['url'] );
            update_post_meta( $post_id, '_featured_resource_org_name', $item['org'] );
            wp_set_object_terms( $post_id, array( $item['theme'] ), 'resource_principle' );
        }
    }

    update_option( 'aiad_partner_resources_seeded', 'yes' );
}
add_action( 'init', 'aiad_seed_partner_resources', 25 );

/**
 * Seed the 5 free lesson starters (one per theme: Safe, Smart, Creative, Responsible, Future).
 * Your own resources – 5-minute lesson starters only. Runs once when option is not set.
 */
function aiad_seed_lesson_starters(): void {
    if ( get_option( 'aiad_lesson_starters_seeded' ) === 'yes' ) {
        return;
    }

    $items = array(
        array(
            'title'   => __( "Who's Really Behind the Screen?", 'ai-awareness-day' ),
            'excerpt' => __( 'Understanding AI-generated content and deepfakes. 5-Minute Lesson Starter.', 'ai-awareness-day' ),
            'theme'   => 'safe',
        ),
        array(
            'title'   => __( "How Does AI Actually 'Think'?", 'ai-awareness-day' ),
            'excerpt' => __( 'Understanding the technology behind the tools you use.', 'ai-awareness-day' ),
            'theme'   => 'smart',
        ),
        array(
            'title'   => __( 'AI as Your Creative Partner', 'ai-awareness-day' ),
            'excerpt' => __( 'Using AI to amplify human creativity, not replace it.', 'ai-awareness-day' ),
            'theme'   => 'creative',
        ),
        array(
            'title'   => __( 'The Hidden Costs of AI', 'ai-awareness-day' ),
            'excerpt' => __( 'Understanding the environmental and ethical impact of AI.', 'ai-awareness-day' ),
            'theme'   => 'responsible',
        ),
        array(
            'title'   => __( 'Your AI-Ready Future', 'ai-awareness-day' ),
            'excerpt' => __( 'Preparing for careers in an AI-transformed world.', 'ai-awareness-day' ),
            'theme'   => 'future',
        ),
    );

    $resource_type = 'Lesson Starter';
    $duration_slug = '5-min-lesson-starters';

    foreach ( $items as $item ) {
        $post_id = wp_insert_post( array(
            'post_type'    => 'resource',
            'post_title'   => $item['title'],
            'post_excerpt' => $item['excerpt'],
            'post_status'  => 'publish',
            'post_author'  => 1,
        ) );
        if ( $post_id && ! is_wp_error( $post_id ) ) {
            wp_set_object_terms( $post_id, array( $resource_type ), 'resource_type' );
            wp_set_object_terms( $post_id, array( $item['theme'] ), 'resource_principle' );
            wp_set_object_terms( $post_id, array( $duration_slug ), 'resource_duration' );
        }
    }

    update_option( 'aiad_lesson_starters_seeded', 'yes' );
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
 * Seed function for "How Does AI Actually 'Think'?" resource.
 * 
 * REMOVED: This resource is now imported via WXR file (how-does-ai-actually-think.wxr.xml).
 * The seed function has been removed to avoid hard-coded content in production.
 * 
 * To import: Use Resources → Import demo resources → Upload how-does-ai-actually-think.wxr.xml
 */
function aiad_seed_how_does_ai_think_resource(): void {
    // Function body removed - resource now imported via WXR file (how-does-ai-actually-think.wxr.xml)
    return;
}

/**
 * Seed a 5-minute lesson starter: "Who's Really Behind the Screen?"
 * Deepfake safety and verification, using full Activity Schema content.
 */
function aiad_seed_whos_really_behind_screen_resource(): void {
    if ( get_option( 'aiad_whos_really_behind_screen_seeded' ) ) {
        return;
    }

    $title    = __( "Who's Really Behind the Screen?", 'ai-awareness-day' );
    $existing = aiad_get_post_by_title( $title, 'resource' );
    $post_id  = $existing ? $existing->ID : wp_insert_post( array(
        'post_type'    => 'resource',
        'post_title'   => $title,
        'post_content' => '',
        'post_excerpt' => __( 'Quick 5-minute starter on deepfakes, online safety, and how to verify if content is genuine.', 'ai-awareness-day' ),
        'post_status'  => 'publish',
        'post_author'  => 1,
    ) );

    if ( ! $post_id || is_wp_error( $post_id ) ) {
        return;
    }

    // Taxonomy terms
    wp_set_object_terms( $post_id, array( 'Lesson Starter' ), 'resource_type' );
    wp_set_object_terms( $post_id, array( 'safe' ), 'resource_principle' );
    wp_set_object_terms( $post_id, array( '5-min-lesson-starters' ), 'resource_duration' );
    wp_set_object_terms( $post_id, array( 'discussion' ), 'activity_type' );

    // Core meta
    update_post_meta( $post_id, '_aiad_subtitle', __( '5-minute SAFE starter on deepfakes, online image abuse, and how to check what is real.', 'ai-awareness-day' ) );
    update_post_meta( $post_id, '_aiad_duration', '5 min' );
    update_post_meta( $post_id, '_aiad_level', 'beginner' );
    update_post_meta( $post_id, '_aiad_status', 'published' );

    // Preparation
    update_post_meta( $post_id, '_aiad_preparation', array(
        __( 'Load slides 1–4 on the projector or board.', 'ai-awareness-day' ),
        __( 'Check you can briefly demonstrate a reverse image search (optional).', 'ai-awareness-day' ),
    ) );

    // Key definitions
    update_post_meta( $post_id, '_aiad_key_definitions', array(
        array(
            'term'              => 'Deepfake',
            'definition'        => 'AI-generated or manipulated video, image, or audio that convincingly shows something that never happened. Can make people appear to say or do things they never did.',
            'key_stage_adapted' => false,
        ),
        array(
            'term'              => 'Reverse Image Search',
            'definition'        => 'A way to check where an image came from online. You upload or paste an image into a search engine to see if it appears elsewhere or has been edited.',
            'key_stage_adapted' => false,
        ),
    ) );

    // Learning objectives
    update_post_meta( $post_id, '_aiad_learning_objectives', array(
        array(
            'objective'  => 'Understand what deepfakes are and the scale of the problem',
            'assessable' => true,
        ),
        array(
            'objective'  => 'Recognise that AI-generated intimate images are illegal abuse',
            'assessable' => true,
        ),
        array(
            'objective'  => 'Know basic steps for staying safe online when you see suspicious images or videos',
            'assessable' => true,
        ),
    ) );

    // Instructions (teacher script)
    update_post_meta( $post_id, '_aiad_instructions', array(
        array(
            'step'           => 1,
            'action'         => 'Display Slide 1 (title) to introduce the topic of deepfakes and online image abuse.',
            'duration'       => '',
            'resource_ref'   => 'Slide 1',
            'student_action' => '',
            'teacher_tip'    => '',
        ),
        array(
            'step'           => 2,
            'action'         => 'Show Slide 2 (debate question) and ask students to discuss in pairs.',
            'duration'       => '60 seconds',
            'resource_ref'   => 'Slide 2',
            'student_action' => __( 'Pair discussion', 'ai-awareness-day' ),
            'teacher_tip'    => __( 'Listen for students who are unsure how to respond or who assume anything shared by friends must be real.', 'ai-awareness-day' ),
        ),
        array(
            'step'           => 3,
            'action'         => "Display Slide 3 (Did You Know facts) and read the key statistics aloud about deepfake abuse and image-based harm.",
            'duration'       => '',
            'resource_ref'   => 'Slide 3',
            'student_action' => '',
            'teacher_tip'    => __( 'Pause briefly after each fact and check for understanding. Reassure students that support is available if they are worried.', 'ai-awareness-day' ),
        ),
        array(
            'step'           => 4,
            'action'         => 'Show Slide 4 (answers and key takeaway) and emphasise safe responses and reporting routes.',
            'duration'       => '',
            'resource_ref'   => 'Slide 4',
            'student_action' => '',
            'teacher_tip'    => __( 'Highlight that victims are never to blame and that sharing harmful images can be a serious offence.', 'ai-awareness-day' ),
        ),
    ) );

    // Discussion
    update_post_meta( $post_id, '_aiad_discussion_prompts', array(
        "How would you react if you received a suspicious image or video of someone you know?",
        'Why do you think deepfake abuse often targets young people and those with less power?',
        "What is one thing you can do differently online after learning about deepfakes and verification?",
    ) );

    update_post_meta(
        $post_id,
        '_aiad_discussion_question',
        "If you couldn't tell whether a video of your friend was real or AI-generated, what would you do?"
    );

    update_post_meta( $post_id, '_aiad_suggested_answers', array(
        // Main question – core safe responses.
        "Don't share suspicious content with others – spreading it can cause real harm even if you think you are warning people.",
        'Check the source carefully: is it from an official or verified account, and where did it originally come from?',
        'Use tools like reverse image search to see if the content appears elsewhere or has been flagged as fake.',
        'Look for visual clues of deepfakes: unnatural blinking, strange lighting, warped backgrounds, or blurry edges around the face or hair.',
        'Ask a trusted adult (teacher, parent, safeguarding lead) to help you verify anything you are unsure about.',
        'If the content shows intimate or abusive material, do not view, save, or share it – report it immediately.',

        // Sub-question: How would you verify if content is genuine?
        'Check if the same story or video appears on trusted news or fact-checking sites such as Full Fact or BBC Reality Check.',
        'Look at the account that posted it – is it verified, how long has it existed, and what else do they post?',
        'Search for the person’s official accounts to see if they have commented on or debunked the content.',
        'Ask yourself who benefits if people believe this is real – could someone be trying to manipulate opinions or cause drama?',

        // Sub-question: What if it was shared by someone you trust?
        'Remember that even trusted friends and adults can be fooled by convincing deepfakes.',
        'The person sharing may have no idea it is fake – misinformation often spreads through well-meaning people.',
        'Verify the content independently before you believe it or pass it on to anyone else.',
        'If you discover it is fake, gently let the person who shared it know and explain why you think it is not genuine.',

        // Sub-question: Should there be laws about creating AI content of real people?
        'Creating AI-generated intimate images of anyone is already illegal and treated as a form of sexual abuse.',
        'The Online Safety Act and similar laws require platforms to remove illegal and harmful content.',
        'Open questions for debate: should all AI depictions of real people require consent, including satire and parody?',
        'Technology often moves faster than legislation, so laws can struggle to keep up with new AI capabilities.',
    ) );

    // Teacher notes
    update_post_meta(
        $post_id,
        '_aiad_teacher_notes',
        "This topic may be sensitive or triggering for students who have experienced image-based abuse or online harassment. Emphasise that victims are never at fault – responsibility always lies with the people creating, editing, or sharing harmful content. Have clear safeguarding information ready and invite students to speak to you or a trusted adult privately if they are worried. Remind the class that creating or sharing deepfake intimate images is illegal even if it is framed as ‘just a joke’, and that seeking help early is always the right thing to do."
    );

    // Basic differentiation and extension (optional but helpful).
    update_post_meta(
        $post_id,
        '_aiad_differentiation',
        array(
            'support' => __( 'Provide simple, student-friendly definitions of deepfake and reverse image search with visuals. Allow extra processing time and check understanding in smaller groups.', 'ai-awareness-day' ),
            'stretch' => __( 'Ask students to research a real-world case of deepfake misuse and prepare one slide on the impact and response.', 'ai-awareness-day' ),
            'send'    => __( 'Pre-teach key vocabulary one-to-one or in a small group. Offer written prompts or sentence starters for the discussion so students can participate safely.', 'ai-awareness-day' ),
        )
    );

    update_post_meta(
        $post_id,
        '_aiad_extensions',
        array(
            array(
                'activity' => __( 'As a follow-up, ask students to create a short poster or slide for younger pupils explaining how to respond if they see a suspicious image or video online.', 'ai-awareness-day' ),
                'type'     => 'next_lesson',
            ),
        )
    );

    update_option( 'aiad_whos_really_behind_screen_seeded', true );
}

/**
 * Seed a 5-minute lesson starter: "The Hidden Costs of AI"
 * Focus on data centres, carbon footprint, and environmental trade-offs.
 */
function aiad_seed_hidden_costs_of_ai_resource(): void {
    if ( get_option( 'aiad_hidden_costs_of_ai_seeded' ) ) {
        return;
    }

    $title    = __( 'The Hidden Costs of AI', 'ai-awareness-day' );
    $existing = aiad_get_post_by_title( $title, 'resource' );
    $post_id  = $existing ? $existing->ID : wp_insert_post( array(
        'post_type'    => 'resource',
        'post_title'   => $title,
        'post_content' => '',
        'post_excerpt' => __( '5-minute starter exploring how AI relies on data centres, electricity, and water – and what that means for the planet.', 'ai-awareness-day' ),
        'post_status'  => 'publish',
        'post_author'  => 1,
    ) );

    if ( ! $post_id || is_wp_error( $post_id ) ) {
        return;
    }

    // Taxonomy terms
    wp_set_object_terms( $post_id, array( 'Lesson Starter' ), 'resource_type' );
    wp_set_object_terms( $post_id, array( 'future' ), 'resource_principle' );
    wp_set_object_terms( $post_id, array( '5-min-lesson-starters' ), 'resource_duration' );
    wp_set_object_terms( $post_id, array( 'discussion' ), 'activity_type' );

    // Core meta
    update_post_meta( $post_id, '_aiad_subtitle', __( 'Quick 5-minute starter on the environmental footprint of AI and the data centres that power it.', 'ai-awareness-day' ) );
    update_post_meta( $post_id, '_aiad_duration', '5 min' );
    update_post_meta( $post_id, '_aiad_level', 'beginner' );
    update_post_meta( $post_id, '_aiad_status', 'published' );

    // Preparation
    update_post_meta( $post_id, '_aiad_preparation', array(
        __( 'Load slides 1–4 on the projector or board.', 'ai-awareness-day' ),
        __( 'Optional: have one short article or graphic ready showing AI energy use or data centres.', 'ai-awareness-day' ),
    ) );

    // Key definitions (from the starter slide)
    update_post_meta( $post_id, '_aiad_key_definitions', array(
        array(
            'term'              => 'Data Centre',
            'definition'        => 'A facility housing thousands of computer servers that store and process data. AI relies on massive data centres that use large amounts of electricity (for computing) and water (for cooling).',
            'key_stage_adapted' => false,
        ),
        array(
            'term'              => 'Carbon Footprint',
            'definition'        => 'The total greenhouse gas emissions caused by an activity. AI’s carbon footprint comes from electricity generation, hardware manufacturing, and cooling systems in data centres.',
            'key_stage_adapted' => false,
        ),
    ) );

    // Learning objectives
    update_post_meta( $post_id, '_aiad_learning_objectives', array(
        array(
            'objective'  => 'Understand that AI runs in physical data centres that use electricity and water',
            'assessable' => true,
        ),
        array(
            'objective'  => 'Recognise that AI has a carbon footprint linked to energy use and hardware',
            'assessable' => true,
        ),
        array(
            'objective'  => 'Consider how to balance the benefits of AI with its environmental costs',
            'assessable' => true,
        ),
    ) );

    // Instructions
    update_post_meta( $post_id, '_aiad_instructions', array(
        array(
            'step'           => 1,
            'action'         => 'Display Slide 1 (title) and briefly introduce the idea that AI has hidden environmental costs.',
            'duration'       => '',
            'resource_ref'   => 'Slide 1',
            'student_action' => '',
            'teacher_tip'    => '',
        ),
        array(
            'step'           => 2,
            'action'         => 'Show Slide 2 (debate or starter question) and ask students to discuss in pairs.',
            'duration'       => '60 seconds',
            'resource_ref'   => 'Slide 2',
            'student_action' => __( 'Pair discussion', 'ai-awareness-day' ),
            'teacher_tip'    => __( 'Listen for students who assume digital activities are “free” for the planet because they are online.', 'ai-awareness-day' ),
        ),
        array(
            'step'           => 3,
            'action'         => 'Display Slide 3 (Did You Know facts about data centres and emissions) and read the key points aloud.',
            'duration'       => '',
            'resource_ref'   => 'Slide 3',
            'student_action' => '',
            'teacher_tip'    => __( 'Use simple comparisons (e.g. number of kettles or homes powered) to make the energy use feel concrete.', 'ai-awareness-day' ),
        ),
        array(
            'step'           => 4,
            'action'         => 'Show Slide 4 (reflection or answers) and highlight practical ways to use AI more thoughtfully.',
            'duration'       => '',
            'resource_ref'   => 'Slide 4',
            'student_action' => '',
            'teacher_tip'    => __( 'Keep the tone balanced: the goal is not to scare students away from AI, but to promote informed, responsible use.', 'ai-awareness-day' ),
        ),
    ) );

    // Discussion
    update_post_meta( $post_id, '_aiad_discussion_prompts', array(
        'If every AI query used a bit of extra energy and water, when is it worth using AI and when is it not?',
        'What are some examples where AI might be worth the environmental cost – and where it might not be?',
        'How could schools, companies, or governments reduce the carbon footprint of AI systems?',
    ) );

    update_post_meta(
        $post_id,
        '_aiad_discussion_question',
        'If you knew that using AI every day had a real carbon footprint, how would that change the way you choose to use it?'
    );

    update_post_meta( $post_id, '_aiad_suggested_answers', array(
        // Main question – core ideas.
        'I might still use AI for tasks where it genuinely helps my learning or saves lots of time, but avoid using it just for fun when I am bored.',
        'I would think twice before generating lots of unnecessary images, long chats, or repeated queries that I do not really need.',
        'I could combine tasks into fewer, better-planned prompts instead of sending many small ones.',

        // Trade-offs and examples.
        'Sometimes AI can reduce other emissions – for example, by optimising delivery routes or helping design more efficient buildings.',
        'In other cases, AI adds extra energy use on top of activities we would have done anyway, without giving much extra benefit.',
        'A key question is: does using AI here create enough value to justify the extra energy and resources it uses?',

        // System-level thinking.
        'Large tech companies can choose to power data centres with renewable energy and improve cooling systems.',
        'Governments can set standards or incentives for lower-carbon data centres and transparent reporting of AI energy use.',
        'Users can push for more transparency and choose tools that are open about their environmental impact.',
    ) );

    // Teacher notes
    update_post_meta(
        $post_id,
        '_aiad_teacher_notes',
        "Students often assume that anything ‘online’ is weightless and has no impact on the physical world. This starter helps them see that AI runs in real buildings full of hardware that use electricity and water. Keep the tone balanced: the aim is not to make students feel guilty for every search or prompt, but to encourage critical thinking about when AI genuinely adds value. Use concrete comparisons (such as powering homes or boiling kettles) to make the numbers meaningful. Encourage questions about who is responsible for reducing AI’s carbon footprint – from individuals through to companies and governments."
    );

    // Differentiation and extension.
    update_post_meta(
        $post_id,
        '_aiad_differentiation',
        array(
            'support' => __( 'Provide a simple diagram of a data centre and a short glossary of key terms. Check understanding in small groups and allow extra thinking time.', 'ai-awareness-day' ),
            'stretch' => __( 'Invite students to research one real example of a data centre or AI service and estimate its potential carbon footprint using public sources.', 'ai-awareness-day' ),
            'send'    => __( 'Use visual supports and step-by-step explanations. Offer alternative ways to respond (spoken, written, or using sentence starters) for students who find open discussion challenging.', 'ai-awareness-day' ),
        )
    );

    update_post_meta(
        $post_id,
        '_aiad_extensions',
        array(
            array(
                'activity' => __( 'Ask students to design a short “eco‑smart AI use” poster or slide for your classroom, with 3 practical tips for using AI responsibly.', 'ai-awareness-day' ),
            'type'     => 'next_lesson',
            ),
        )
    );

    update_option( 'aiad_hidden_costs_of_ai_seeded', true );
}

/**
 * Seed function for "Your AI-Ready Future" resource.
 * 
 * REMOVED: This resource is now imported via WXR file (your-ai-ready-future.wxr.xml).
 * The seed function has been removed to avoid hard-coded content in production.
 * 
 * To import: Use Resources → Import demo resources → Upload your-ai-ready-future.wxr.xml
 */
function aiad_seed_your_ai_ready_future_resource(): void {
    // Function body removed - resource now imported via WXR file (your-ai-ready-future.wxr.xml)
    return;
}

/**
 * Seed function for "AI as Your Creative Partner" resource.
 * 
 * REMOVED: This resource is now imported via WXR file (ai-as-your-creative-partner.wxr.xml).
 * The seed function has been removed to avoid hard-coded content in production.
 * 
 * To import: Use Resources → Import demo resources → Upload ai-as-your-creative-partner.wxr.xml
 */

/**
 * Resources → Import demo resources admin page.
 */
function aiad_register_resource_import_page(): void {
    add_submenu_page(
        'edit.php?post_type=resource',
        __( 'Import demo resources', 'ai-awareness-day' ),
        __( 'Import demo resources', 'ai-awareness-day' ),
        'manage_options',
        'aiad-import-resources',
        'aiad_render_resource_import_page'
    );
}
add_action( 'admin_menu', 'aiad_register_resource_import_page' );

/**
 * Resources → Export demo resources admin page.
 */
function aiad_register_resource_export_page(): void {
    add_submenu_page(
        'edit.php?post_type=resource',
        __( 'Export demo resources', 'ai-awareness-day' ),
        __( 'Export demo resources', 'ai-awareness-day' ),
        'manage_options',
        'aiad-export-resources',
        'aiad_render_resource_export_page'
    );
}
add_action( 'admin_menu', 'aiad_register_resource_export_page' );

/**
 * Generate WXR file for "AI as Your Creative Partner" resource.
 * Creates a standalone WXR file from seed function data.
 */
function aiad_generate_creative_partner_wxr(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to generate WXR files.', 'ai-awareness-day' ) );
    }

    // Resource data from seed function
    $post_data = array(
        'title'        => 'AI as Your Creative Partner',
        'excerpt'      => 'Using AI to amplify human creativity, not replace it.',
        'content'      => '',
        'status'       => 'publish',
        'post_name'    => 'ai-as-your-creative-partner',
        'post_date'    => current_time( 'mysql' ),
        'post_date_gmt' => current_time( 'mysql', 1 ),
    );

    // Taxonomy terms
    $taxonomies = array(
        'resource_type'     => array( 'Lesson Starter' ),
        'resource_principle' => array( 'creative' ),
        'resource_duration'  => array( '5-min-lesson-starters' ),
        'activity_type'      => array( 'discussion' ),
    );

    // Post meta data
    $meta_data = array(
        '_aiad_subtitle' => 'Using AI to amplify human creativity, not replace it. A 5-minute discussion starter.',
        '_aiad_duration' => '5 min',
        '_aiad_level'    => 'beginner',
        '_aiad_status'   => 'published',
        '_aiad_preparation' => array(
            'Display or share screen for a short prompt',
            'Optional: one device with an AI image or text tool for a quick demo',
        ),
        '_aiad_key_definitions' => array(
            array(
                'term'              => 'Generative AI',
                'definition'        => 'AI that creates new content (text, images, music, code) based on patterns in training data. It recombines existing patterns rather than having original ideas.',
                'key_stage_adapted' => false,
            ),
            array(
                'term'              => 'Authenticity (in creative work)',
                'definition'        => 'The quality of being genuine and original — reflecting your own voice, experiences, and creative choices rather than copying or delegating to others.',
                'key_stage_adapted' => false,
            ),
        ),
        '_aiad_learning_objectives' => array(
            array(
                'objective'  => 'Recognise that AI can support creative work without replacing human ideas',
                'assessable' => true,
            ),
            array(
                'objective'  => 'Understand that the best results often come from human direction plus AI assistance',
                'assessable' => true,
            ),
        ),
        '_aiad_instructions' => array(
            array(
                'step'           => 1,
                'action'         => 'Ask: "When you use AI to make something — a story, picture, or song — who do you think is the creator?"',
                'duration'       => '1 min',
                'resource_ref'   => '',
                'student_action' => 'Quick think or pair share',
                'teacher_tip'    => 'Accept a range of answers; you will refine in step 3.',
            ),
            array(
                'step'           => 2,
                'action'         => 'Share a simple example: e.g. "I gave the AI a idea and it made a picture. Was the idea or the picture more important?"',
                'duration'       => '2 min',
                'resource_ref'   => '',
                'student_action' => 'Discussion',
                'teacher_tip'    => '',
            ),
            array(
                'step'           => 3,
                'action'         => 'Summarise: "AI is a creative partner. You bring the idea and the choices; AI helps you explore. The human is still in charge."',
                'duration'       => '1 min',
                'resource_ref'   => '',
                'student_action' => '',
                'teacher_tip'    => '',
            ),
        ),
        '_aiad_discussion_question' => 'When you use AI to create something, who do you think is the real creator — you or the AI?',
        '_aiad_discussion_prompts' => array(
            'What would be boring or wrong if we let AI do everything without our input?',
            'How can you stay "in charge" when using AI for a project?',
        ),
        '_aiad_suggested_answers' => array(
            'It depends on HOW AI was used and how much of YOUR creative input and decision-making was involved.',
            'Using AI for brainstorming or starting points is often still your work — like using a dictionary or thesaurus.',
            'Letting AI generate almost the entire piece with minimal editing means it is much less clearly "yours".',
            'Key question: can you explain and defend every creative choice in the final work?',
            'Ask yourself: would you be comfortable if a teacher or employer knew exactly how AI was used in this piece?',
            'The human brings vision, judgement, emotional truth, personal perspective, and the final decisions — AI follows instructions.',
        ),
        '_aiad_teacher_notes' => 'Students may worry that AI "takes over" creativity. Emphasise that AI is a tool that responds to human prompts and choices. Good analogy: like a brush for a painter — the brush does not decide the painting. Encourage examples from their own use of AI (e.g. image generators, chatbots) and link to the idea of staying critical and in control.',
        '_aiad_differentiation' => array(
            'support' => 'Use a single, concrete example (e.g. one image prompt) and keep the discussion to "who had the idea?".',
            'stretch' => 'Ask students to design a short "human + AI" creative project and say what they would do vs what they would ask AI to do.',
            'send'    => 'Pre-teach "creator" and "partner". Use visual cues (e.g. you + robot icon) to show human in charge.',
        ),
        '_aiad_extensions' => array(
            array(
                'activity' => 'Try one small creative task with an AI tool and note what you decided vs what the AI did',
                'type'     => 'homework',
            ),
        ),
    );

    // Generate filename
    $filename = 'ai-as-your-creative-partner-' . date( 'Y-m-d-His' ) . '.xml';

    // Set headers for download
    header( 'Content-Type: application/xml; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename=' . $filename );
    header( 'Pragma: no-cache' );
    header( 'Expires: 0' );

    // Start XML output
    echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
    ?>
    <rss version="2.0"
        xmlns:excerpt="http://wordpress.org/export/<?php echo esc_attr( aiad_get_wxr_version() ); ?>/excerpt/"
        xmlns:content="http://purl.org/rss/1.0/modules/content/"
        xmlns:wfw="http://wellformedweb.org/CommentAPI/"
        xmlns:dc="http://purl.org/dc/elements/1.1/"
        xmlns:wp="http://wordpress.org/export/<?php echo esc_attr( aiad_get_wxr_version() ); ?>/"
    >
        <channel>
            <title><?php bloginfo_rss( 'name' ); ?></title>
            <link><?php bloginfo_rss( 'url' ); ?></link>
            <description><?php bloginfo_rss( 'description' ); ?></description>
            <pubDate><?php echo esc_html( date( 'D, d M Y H:i:s +0000' ) ); ?></pubDate>
            <language><?php bloginfo_rss( 'language' ); ?></language>
            <wp:wxr_version><?php echo esc_html( aiad_get_wxr_version() ); ?></wp:wxr_version>
            <wp:base_site_url><?php echo esc_url( site_url() ); ?></wp:base_site_url>
            <wp:base_blog_url><?php echo esc_url( home_url() ); ?></wp:base_blog_url>

            <?php
            // Export taxonomy terms
            foreach ( $taxonomies as $taxonomy => $terms ) {
                foreach ( $terms as $term_name ) {
                    $term_obj = get_term_by( 'name', $term_name, $taxonomy );
                    if ( $term_obj ) {
                        ?>
                        <wp:term>
                            <wp:term_id><?php echo (int) $term_obj->term_id; ?></wp:term_id>
                            <wp:term_taxonomy><?php echo esc_html( $taxonomy ); ?></wp:term_taxonomy>
                            <wp:term_slug><?php echo esc_html( $term_obj->slug ); ?></wp:term_slug>
                            <wp:term_name><?php echo esc_html( $term_obj->name ); ?></wp:term_name>
                            <?php if ( ! empty( $term_obj->description ) ) : ?>
                                <wp:term_description><?php echo aiad_wxr_cdata( $term_obj->description ); ?></wp:term_description>
                            <?php endif; ?>
                        </wp:term>
                        <?php
                    }
                }
            }

            // Export the post
            ?>
            <item>
                <title><?php echo esc_html( $post_data['title'] ); ?></title>
                <link><?php echo esc_url( home_url( '/?resource=' . $post_data['post_name'] ) ); ?></link>
                <pubDate><?php echo esc_html( mysql2date( 'D, d M Y H:i:s +0000', $post_data['post_date_gmt'], false ) ); ?></pubDate>
                <dc:creator><?php echo esc_html( wp_get_current_user()->user_login ); ?></dc:creator>
                <guid isPermaLink="false"><?php echo esc_url( home_url( '/?resource=' . $post_data['post_name'] ) ); ?></guid>
                <description></description>
                <content:encoded><?php echo aiad_wxr_cdata( $post_data['content'] ); ?></content:encoded>
                <excerpt:encoded><?php echo aiad_wxr_cdata( $post_data['excerpt'] ); ?></excerpt:encoded>
                <wp:post_id>0</wp:post_id>
                <wp:post_date><?php echo esc_html( $post_data['post_date'] ); ?></wp:post_date>
                <wp:post_date_gmt><?php echo esc_html( $post_data['post_date_gmt'] ); ?></wp:post_date_gmt>
                <wp:comment_status>closed</wp:comment_status>
                <wp:ping_status>closed</wp:ping_status>
                <wp:post_name><?php echo esc_html( $post_data['post_name'] ); ?></wp:post_name>
                <wp:status><?php echo esc_html( $post_data['status'] ); ?></wp:status>
                <wp:post_parent>0</wp:post_parent>
                <wp:menu_order>0</wp:menu_order>
                <wp:post_type>resource</wp:post_type>
                <wp:post_password></wp:post_password>
                <wp:is_sticky>0</wp:is_sticky>

                <?php
                // Export taxonomy terms
                foreach ( $taxonomies as $taxonomy => $terms ) {
                    foreach ( $terms as $term_name ) {
                        $term_obj = get_term_by( 'name', $term_name, $taxonomy );
                        if ( $term_obj ) {
                            ?>
                            <category domain="<?php echo esc_attr( $taxonomy ); ?>" nicename="<?php echo esc_attr( $term_obj->slug ); ?>">
                                <?php echo esc_html( $term_obj->name ); ?>
                            </category>
                            <?php
                        }
                    }
                }

                // Export post meta
                foreach ( $meta_data as $meta_key => $meta_value ) {
                    // Serialize arrays/objects for storage
                    if ( is_array( $meta_value ) || is_object( $meta_value ) ) {
                        $meta_value = maybe_serialize( $meta_value );
                    }
                    ?>
                    <wp:postmeta>
                        <wp:meta_key><?php echo esc_html( $meta_key ); ?></wp:meta_key>
                        <wp:meta_value><?php echo aiad_wxr_cdata( $meta_value ); ?></wp:meta_value>
                    </wp:postmeta>
                    <?php
                }
                ?>
            </item>
        </channel>
    </rss>
    <?php
    exit;
}

/**
 * Render the Export demo resources admin page.
 */
function aiad_render_resource_export_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to access this page.', 'ai-awareness-day' ) );
    }

    // Handle single resource WXR generation
    if ( isset( $_GET['aiad_generate_creative_partner'] ) && check_admin_referer( 'aiad_generate_creative_partner', 'aiad_generate_nonce' ) ) {
        aiad_generate_creative_partner_wxr();
        exit;
    }

    // Handle export request
    if ( isset( $_GET['aiad_export_resources'] ) && check_admin_referer( 'aiad_export_resources', 'aiad_export_nonce' ) ) {
        aiad_export_resources_to_wxr();
        exit;
    }

    // Count existing resources
    $resource_count = wp_count_posts( 'resource' );
    $total_resources = (int) $resource_count->publish + (int) $resource_count->draft + (int) $resource_count->pending;

    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Export demo resources', 'ai-awareness-day' ); ?></h1>
        <p><?php esc_html_e( 'Export all Resource posts to a WordPress WXR (.xml) file. This file can be imported on another WordPress site using the Import demo resources tool.', 'ai-awareness-day' ); ?></p>
        
        <?php if ( $total_resources > 0 ) : ?>
            <div class="notice notice-info">
                <p>
                    <?php
                    /* translators: %d: number of resources */
                    printf( esc_html__( 'Found %d resource(s) to export.', 'ai-awareness-day' ), $total_resources );
                    ?>
                </p>
            </div>
            <p>
                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=aiad-export-resources&aiad_export_resources=1' ), 'aiad_export_resources', 'aiad_export_nonce' ) ); ?>" class="button button-primary">
                    <?php esc_html_e( 'Download WXR Export File', 'ai-awareness-day' ); ?>
                </a>
            </p>
            <p class="description">
                <?php esc_html_e( 'The exported WXR file will include all Resource posts with their metadata, taxonomy terms, and custom fields. You can import this file on another WordPress site using the Import demo resources tool.', 'ai-awareness-day' ); ?>
            </p>
            <hr />
            <h2><?php esc_html_e( 'Single Resource Export (Test)', 'ai-awareness-day' ); ?></h2>
            <p><?php esc_html_e( 'Generate a WXR file for "AI as Your Creative Partner" resource from seed function data. Use this to test the import process before exporting all resources.', 'ai-awareness-day' ); ?></p>
            <p>
                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=aiad-export-resources&aiad_generate_creative_partner=1' ), 'aiad_generate_creative_partner', 'aiad_generate_nonce' ) ); ?>" class="button button-secondary">
                    <?php esc_html_e( 'Generate WXR: AI as Your Creative Partner', 'ai-awareness-day' ); ?>
                </a>
            </p>
        <?php else : ?>
            <div class="notice notice-warning">
                <p>
                    <?php esc_html_e( 'No resources found to export.', 'ai-awareness-day' ); ?>
                </p>
                <p>
                    <?php esc_html_e( 'To generate demo content for export, you can temporarily enable the seed functions in functions.php (uncomment the add_action hooks), then refresh this page after the resources are created.', 'ai-awareness-day' ); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Export all Resource posts to a WXR file.
 * Generates WordPress eXtended RSS (WXR) format XML and triggers download.
 */
function aiad_export_resources_to_wxr(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to export resources.', 'ai-awareness-day' ) );
    }

    // Get all resource posts
    $resources = get_posts( array(
        'post_type'      => 'resource',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'orderby'        => 'ID',
        'order'          => 'ASC',
    ) );

    if ( empty( $resources ) ) {
        wp_die( esc_html__( 'No resources found to export.', 'ai-awareness-day' ) );
    }

    // Generate filename with timestamp
    $filename = 'ai-awareness-day-resources-' . date( 'Y-m-d-His' ) . '.xml';

    // Set headers for download
    header( 'Content-Type: application/xml; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename=' . $filename );
    header( 'Pragma: no-cache' );
    header( 'Expires: 0' );

    // Start XML output
    echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
    ?>
    <rss version="2.0"
        xmlns:excerpt="http://wordpress.org/export/<?php echo esc_attr( aiad_get_wxr_version() ); ?>/excerpt/"
        xmlns:content="http://purl.org/rss/1.0/modules/content/"
        xmlns:wfw="http://wellformedweb.org/CommentAPI/"
        xmlns:dc="http://purl.org/dc/elements/1.1/"
        xmlns:wp="http://wordpress.org/export/<?php echo esc_attr( aiad_get_wxr_version() ); ?>/"
    >
        <channel>
            <title><?php bloginfo_rss( 'name' ); ?></title>
            <link><?php bloginfo_rss( 'url' ); ?></link>
            <description><?php bloginfo_rss( 'description' ); ?></description>
            <pubDate><?php echo esc_html( date( 'D, d M Y H:i:s +0000' ) ); ?></pubDate>
            <language><?php bloginfo_rss( 'language' ); ?></language>
            <wp:wxr_version><?php echo esc_html( aiad_get_wxr_version() ); ?></wp:wxr_version>
            <wp:base_site_url><?php echo esc_url( site_url() ); ?></wp:base_site_url>
            <wp:base_blog_url><?php echo esc_url( home_url() ); ?></wp:base_blog_url>

            <?php
            // Export each resource post
            foreach ( $resources as $resource ) {
                aiad_export_resource_post( $resource );
            }

            // Export taxonomy terms
            $taxonomies = array( 'resource_type', 'resource_principle', 'resource_duration', 'activity_type' );
            foreach ( $taxonomies as $taxonomy ) {
                aiad_export_taxonomy_terms( $taxonomy );
            }
            ?>
        </channel>
    </rss>
    <?php
}

/**
 * Get WXR version string.
 *
 * @return string WXR version.
 */
function aiad_get_wxr_version(): string {
    return '1.2';
}

/**
 * Export a single resource post to WXR format.
 *
 * @param WP_Post $post Resource post object.
 */
function aiad_export_resource_post( WP_Post $post ): void {
    $post = get_post( $post->ID );
    setup_postdata( $post );

    // Get all post meta
    $post_meta = get_post_meta( $post->ID );

    // Get taxonomy terms
    $taxonomies = array( 'resource_type', 'resource_principle', 'resource_duration', 'activity_type' );
    $terms      = array();
    foreach ( $taxonomies as $taxonomy ) {
        $post_terms = wp_get_object_terms( $post->ID, $taxonomy );
        if ( ! is_wp_error( $post_terms ) && ! empty( $post_terms ) ) {
            foreach ( $post_terms as $term ) {
                $terms[] = array(
                    'domain'   => $taxonomy,
                    'slug'     => $term->slug,
                    'name'     => $term->name,
                );
            }
        }
    }

    ?>
    <item>
        <title><?php echo esc_html( apply_filters( 'the_title_rss', $post->post_title ) ); ?></title>
        <link><?php echo esc_url( get_permalink( $post->ID ) ); ?></link>
        <pubDate><?php echo esc_html( mysql2date( 'D, d M Y H:i:s +0000', $post->post_date_gmt, false ) ); ?></pubDate>
        <dc:creator><?php echo esc_html( get_the_author_meta( 'login', $post->post_author ) ); ?></dc:creator>
        <guid isPermaLink="false"><?php echo esc_url( get_permalink( $post->ID ) ); ?></guid>
        <description></description>
        <content:encoded><?php echo aiad_wxr_cdata( apply_filters( 'the_content_export', $post->post_content ) ); ?></content:encoded>
        <excerpt:encoded><?php echo aiad_wxr_cdata( apply_filters( 'the_excerpt_export', $post->post_excerpt ) ); ?></excerpt:encoded>
        <wp:post_id><?php echo (int) $post->ID; ?></wp:post_id>
        <wp:post_date><?php echo esc_html( $post->post_date ); ?></wp:post_date>
        <wp:post_date_gmt><?php echo esc_html( $post->post_date_gmt ); ?></wp:post_date_gmt>
        <wp:comment_status><?php echo esc_html( $post->comment_status ); ?></wp:comment_status>
        <wp:ping_status><?php echo esc_html( $post->ping_status ); ?></wp:ping_status>
        <wp:post_name><?php echo esc_html( $post->post_name ); ?></wp:post_name>
        <wp:status><?php echo esc_html( $post->post_status ); ?></wp:status>
        <wp:post_parent><?php echo (int) $post->post_parent; ?></wp:post_parent>
        <wp:menu_order><?php echo (int) $post->menu_order; ?></wp:menu_order>
        <wp:post_type><?php echo esc_html( $post->post_type ); ?></wp:post_type>
        <wp:post_password><?php echo esc_html( $post->post_password ); ?></wp:post_password>
        <wp:is_sticky><?php echo is_sticky( $post->ID ) ? '1' : '0'; ?></wp:is_sticky>

        <?php
        // Export taxonomy terms
        foreach ( $terms as $term ) {
            ?>
            <category domain="<?php echo esc_attr( $term['domain'] ); ?>" nicename="<?php echo esc_attr( $term['slug'] ); ?>">
                <?php echo esc_html( $term['name'] ); ?>
            </category>
            <?php
        }

        // Export post meta
        foreach ( $post_meta as $meta_key => $meta_values ) {
            // Skip internal WordPress meta
            if ( strpos( $meta_key, '_edit_' ) === 0 || strpos( $meta_key, '_wp_' ) === 0 ) {
                continue;
            }

            foreach ( $meta_values as $meta_value ) {
                // Serialize arrays/objects for storage
                if ( is_array( $meta_value ) || is_object( $meta_value ) ) {
                    $meta_value = maybe_serialize( $meta_value );
                }
                ?>
                <wp:postmeta>
                    <wp:meta_key><?php echo esc_html( $meta_key ); ?></wp:meta_key>
                    <wp:meta_value><?php echo aiad_wxr_cdata( $meta_value ); ?></wp:meta_value>
                </wp:postmeta>
                <?php
            }
        }
        ?>
    </item>
    <?php
    wp_reset_postdata();
}

/**
 * Export taxonomy terms to WXR format.
 *
 * @param string $taxonomy Taxonomy name.
 */
function aiad_export_taxonomy_terms( string $taxonomy ): void {
    $terms = get_terms( array(
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
    ) );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return;
    }

    foreach ( $terms as $term ) {
        ?>
        <wp:term>
            <wp:term_id><?php echo (int) $term->term_id; ?></wp:term_id>
            <wp:term_taxonomy><?php echo esc_html( $taxonomy ); ?></wp:term_taxonomy>
            <wp:term_slug><?php echo esc_html( $term->slug ); ?></wp:term_slug>
            <wp:term_name><?php echo esc_html( $term->name ); ?></wp:term_name>
            <?php if ( ! empty( $term->description ) ) : ?>
                <wp:term_description><?php echo aiad_wxr_cdata( $term->description ); ?></wp:term_description>
            <?php endif; ?>
        </wp:term>
        <?php
    }
}

/**
 * Wrap content in CDATA section for safe XML export.
 *
 * @param string|mixed $str Content to wrap.
 * @return string CDATA-wrapped content.
 */
function aiad_wxr_cdata( $str ): string {
    if ( ! is_string( $str ) ) {
        $str = (string) $str;
    }
    if ( ! seems_utf8( $str ) ) {
        $str = utf8_encode( $str );
    }
    $str = '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $str ) . ']]>';
    return $str;
}

/**
 * Render the Import demo resources admin page.
 */
function aiad_render_resource_import_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to access this page.', 'ai-awareness-day' ) );
    }

    $message = '';
    $type    = '';

    if ( isset( $_POST['aiad_resource_import_submit'] ) && check_admin_referer( 'aiad_resource_import', 'aiad_resource_import_nonce' ) ) {
        if ( ! empty( $_FILES['aiad_wxr_file']['tmp_name'] ) ) {
            $file = $_FILES['aiad_wxr_file'];
            if ( ! empty( $file['error'] ) ) {
                $message = esc_html__( 'Upload error. Please try again.', 'ai-awareness-day' );
                $type    = 'error';
            } else {
                $result = aiad_import_resources_from_wxr( $file['tmp_name'], $file['name'] );
                if ( is_wp_error( $result ) ) {
                    /* translators: %s: error message */
                    $message = sprintf( esc_html__( 'Import failed: %s', 'ai-awareness-day' ), $result->get_error_message() );
                    $type    = 'error';
                } else {
                    /* translators: %d: number of resources imported */
                    $message = sprintf( esc_html__( 'Import completed. %d resources processed.', 'ai-awareness-day' ), (int) $result );
                    $message .= ' <a href="' . esc_url( admin_url( 'edit.php?post_type=resource' ) ) . '">' . esc_html__( 'View all resources', 'ai-awareness-day' ) . '</a>';
                    $type    = 'updated';
                }
            }
        } else {
            $message = esc_html__( 'Please choose a WXR (.xml) file to upload.', 'ai-awareness-day' );
            $type    = 'error';
        }
    }

    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Import demo resources', 'ai-awareness-day' ); ?></h1>
        <p><?php esc_html_e( 'Use this tool to import demo Resource content from a WordPress WXR (.xml) export file. This is intended for development and testing – it will create or update Resource posts.', 'ai-awareness-day' ); ?></p>
        <?php if ( $message ) : ?>
            <div class="<?php echo $type === 'error' ? 'notice notice-error' : 'notice notice-success'; ?> is-dismissible">
                <p><?php echo esc_html( $message ); ?></p>
            </div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field( 'aiad_resource_import', 'aiad_resource_import_nonce' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="aiad_wxr_file"><?php esc_html_e( 'WXR file', 'ai-awareness-day' ); ?></label>
                    </th>
                    <td>
                        <input type="file" id="aiad_wxr_file" name="aiad_wxr_file" accept=".xml" class="regular-text" />
                        <p class="description">
                            <?php esc_html_e( 'Export your Resources from another site (Tools → Export → Resources) and upload the .xml file here.', 'ai-awareness-day' ); ?>
                        </p>
                    </td>
                </tr>
            </table>
            <?php submit_button( __( 'Import demo resources', 'ai-awareness-day' ), 'primary', 'aiad_resource_import_submit' ); ?>
        </form>
    </div>
    <?php
}

/**
 * Import Resources from a WXR file.
 * First tries WordPress Importer plugin if available, otherwise uses simple XML parser.
 *
 * @param string $file_path Absolute path to the uploaded WXR file.
 * @param string $original_name Original filename (for messages/logging).
 * @return int|\WP_Error Number of Resource posts processed on success, or WP_Error on failure.
 */
function aiad_import_resources_from_wxr( string $file_path, string $original_name = '' ) {
    if ( ! file_exists( $file_path ) ) {
        return new WP_Error( 'aiad_import_missing_file', __( 'Import file not found.', 'ai-awareness-day' ) );
    }

    // Try WordPress Importer plugin first if available
    if ( class_exists( 'WP_Import' ) ) {
        require_once ABSPATH . 'wp-admin/includes/import.php';

        $importer = new WP_Import();
        $importer->fetch_attachments = false;

        $count = 0;

        add_filter(
            'wp_import_post_exists',
            static function ( $post_exists, $post_id, $postdata ) {
                if ( isset( $postdata['post_type'] ) && $postdata['post_type'] === 'resource' ) {
                    if ( ! empty( $postdata['post_name'] ) ) {
                        $existing = get_page_by_path( $postdata['post_name'], OBJECT, 'resource' );
                        if ( $existing instanceof WP_Post ) {
                            return $existing->ID;
                        }
                    }
                }
                return $post_exists;
            },
            10,
            3
        );

        add_action(
            'wp_import_post_data_processed',
            static function ( $postdata ) use ( &$count ) {
                if ( isset( $postdata['post_type'] ) && $postdata['post_type'] === 'resource' ) {
                    $count++;
                }
            }
        );

        ob_start();
        $importer->import( $file_path );
        ob_end_clean();

        return $count;
    }

    // Fallback: Simple XML parser for Resource posts only
    return aiad_import_resources_from_wxr_simple( $file_path );
}

/**
 * Simple WXR import parser for Resource posts (no plugin required).
 *
 * @param string $file_path Absolute path to the WXR file.
 * @return int|\WP_Error Number of resources imported.
 */
function aiad_import_resources_from_wxr_simple( string $file_path ) {
    if ( ! file_exists( $file_path ) ) {
        return new WP_Error( 'aiad_import_missing_file', __( 'Import file not found.', 'ai-awareness-day' ) );
    }

    // Load XML file
    libxml_use_internal_errors( true );
    $xml = simplexml_load_file( $file_path );
    
    if ( $xml === false ) {
        $errors = libxml_get_errors();
        libxml_clear_errors();
        return new WP_Error( 'aiad_import_xml_error', __( 'Invalid XML file.', 'ai-awareness-day' ) );
    }

    // Register namespaces
    $xml->registerXPathNamespace( 'wp', 'http://wordpress.org/export/1.2/' );
    $xml->registerXPathNamespace( 'content', 'http://purl.org/rss/1.0/modules/content/' );
    $xml->registerXPathNamespace( 'excerpt', 'http://wordpress.org/export/1.2/excerpt/' );

    $count = 0;

    // Process each item
    foreach ( $xml->channel->item as $item ) {
        // Check if it's a resource post
        $post_type = (string) $item->children( 'wp', true )->post_type;
        if ( $post_type !== 'resource' ) {
            continue;
        }

        // Check if post already exists by slug
        $post_name = (string) $item->children( 'wp', true )->post_name;
        if ( ! empty( $post_name ) ) {
            $existing = get_page_by_path( $post_name, OBJECT, 'resource' );
            if ( $existing instanceof WP_Post ) {
                $post_id = $existing->ID;
            } else {
                $post_id = 0;
            }
        } else {
            $post_id = 0;
        }

        // Prepare post data
        $post_status = (string) $item->children( 'wp', true )->status;
        // Ensure status is valid, default to 'publish'
        if ( ! in_array( $post_status, array( 'publish', 'draft', 'pending', 'private' ), true ) ) {
            $post_status = 'publish';
        }
        
        $post_data = array(
            'post_type'    => 'resource',
            'post_title'   => (string) $item->title,
            'post_content' => (string) $item->children( 'content', true )->encoded,
            'post_excerpt' => (string) $item->children( 'excerpt', true )->encoded,
            'post_status'  => $post_status,
            'post_name'    => $post_name,
            'post_date'    => (string) $item->children( 'wp', true )->post_date,
            'post_date_gmt' => (string) $item->children( 'wp', true )->post_date_gmt,
            'post_author'   => 1,
        );

        // Insert or update post
        if ( $post_id > 0 ) {
            $post_data['ID'] = $post_id;
            $result = wp_update_post( $post_data, true );
        } else {
            $result = wp_insert_post( $post_data, true );
        }

        if ( is_wp_error( $result ) ) {
            continue;
        }

        $post_id = $result;

        // Import taxonomy terms
        $taxonomies_to_assign = array();
        foreach ( $item->category as $category ) {
            $domain = (string) $category['domain'];
            $name   = (string) $category;
            $slug   = (string) $category['nicename'];
            
            if ( in_array( $domain, array( 'resource_type', 'resource_principle', 'resource_duration', 'activity_type' ), true ) ) {
                // Try to get term by slug first (more reliable), then by name
                $term = false;
                if ( ! empty( $slug ) ) {
                    $term = get_term_by( 'slug', $slug, $domain );
                }
                if ( ! $term && ! empty( $name ) ) {
                    $term = get_term_by( 'name', $name, $domain );
                }
                
                // Create term if it doesn't exist
                if ( ! $term || is_wp_error( $term ) ) {
                    $term_args = array();
                    if ( ! empty( $slug ) ) {
                        $term_args['slug'] = $slug;
                    }
                    $term_result = wp_insert_term( $name, $domain, $term_args );
                    if ( ! is_wp_error( $term_result ) ) {
                        $term = get_term( $term_result['term_id'], $domain );
                    }
                }
                
                if ( $term && ! is_wp_error( $term ) ) {
                    if ( ! isset( $taxonomies_to_assign[ $domain ] ) ) {
                        $taxonomies_to_assign[ $domain ] = array();
                    }
                    $taxonomies_to_assign[ $domain ][] = (int) $term->term_id;
                }
            }
        }
        
        // Assign all taxonomy terms (replace existing, don't append)
        foreach ( $taxonomies_to_assign as $taxonomy => $term_ids ) {
            wp_set_object_terms( $post_id, $term_ids, $taxonomy, false );
        }

        // Import post meta
        foreach ( $item->children( 'wp', true )->postmeta as $postmeta ) {
            $meta_key   = (string) $postmeta->meta_key;
            $meta_value = (string) $postmeta->meta_value;
            
            // Skip internal WordPress meta
            if ( strpos( $meta_key, '_edit_' ) === 0 || strpos( $meta_key, '_wp_' ) === 0 ) {
                continue;
            }

            // Unserialize if needed
            $unserialized = maybe_unserialize( $meta_value );
            update_post_meta( $post_id, $meta_key, $unserialized !== false ? $unserialized : $meta_value );
        }

        $count++;
    }

    // Flush rewrite rules to ensure permalinks work
    flush_rewrite_rules( false );

    return $count;
}

/**
 * Remove default taxonomy meta boxes; unified Resource Details handles them.
 */
function aiad_remove_default_taxonomy_boxes(): void {
    remove_meta_box( 'resource_typediv', 'resource', 'side' );
    remove_meta_box( 'resource_principlediv', 'resource', 'side' );
    remove_meta_box( 'resource_durationdiv', 'resource', 'side' );
    remove_meta_box( 'activity_typediv', 'resource', 'side' );
}
add_action( 'add_meta_boxes', 'aiad_remove_default_taxonomy_boxes', 99 );

/**
 * Unified Resource Details meta box (Format, Theme, Session length, Activity type, Download, Key Stage)
 */
function aiad_resource_details_meta_box(): void {
    add_meta_box(
        'aiad_resource_details',
        __( 'Resource Details', 'ai-awareness-day' ),
        'aiad_resource_details_callback',
        'resource',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'aiad_resource_details_meta_box' );

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
 * Callback: Resource Details meta box content
 *
 * @param WP_Post $post Resource post.
 */
function aiad_resource_details_callback( WP_Post $post ): void {
    wp_nonce_field( 'aiad_resource_details_nonce', 'aiad_resource_details_nonce' );

    $type_terms     = get_terms( array( 'taxonomy' => 'resource_type', 'hide_empty' => false ) );
    $theme_terms    = get_terms( array( 'taxonomy' => 'resource_principle', 'hide_empty' => false ) );
    $duration_terms = get_terms( array( 'taxonomy' => 'resource_duration', 'hide_empty' => false ) );
    $activity_terms = get_terms( array( 'taxonomy' => 'activity_type', 'hide_empty' => false ) );

    $current_type     = wp_get_object_terms( $post->ID, 'resource_type' );
    $current_theme    = wp_get_object_terms( $post->ID, 'resource_principle' );
    $current_duration = wp_get_object_terms( $post->ID, 'resource_duration' );
    $current_activities = wp_get_object_terms( $post->ID, 'activity_type' );
    $key_stages      = (array) get_post_meta( $post->ID, '_aiad_key_stage', true );
    $download_url    = get_post_meta( $post->ID, '_resource_download_url', true );
    $filename        = $download_url ? basename( (string) wp_parse_url( $download_url, PHP_URL_PATH ) ) : '';
    $subtitle        = get_post_meta( $post->ID, '_aiad_subtitle', true );
    $duration_str    = get_post_meta( $post->ID, '_aiad_duration', true );
    $level           = get_post_meta( $post->ID, '_aiad_level', true );
    $status          = get_post_meta( $post->ID, '_aiad_status', true );
    if ( $status === '' ) {
        $status = get_post_status( $post->ID ) === 'publish' ? 'published' : 'draft';
    }

    $theme_slugs = array( 'safe', 'smart', 'creative', 'responsible', 'future' );

    echo '<div class="aiad-resource-details">';

    // Subtitle (Activity Schema: one sentence, max 120 chars)
    echo '<div class="aiad-rd-section"><strong class="aiad-rd-label">' . esc_html__( 'Subtitle', 'ai-awareness-day' ) . '</strong>';
    echo '<input type="text" name="aiad_subtitle" value="' . esc_attr( $subtitle ) . '" class="large-text" maxlength="120" placeholder="' . esc_attr__( 'One sentence: what students will actually do', 'ai-awareness-day' ) . '" />';
    echo '<p class="description">' . esc_html__( 'Max 120 characters.', 'ai-awareness-day' ) . '</p></div>';

    // Duration string (Activity Schema: specific e.g. "5 min", "45 min")
    echo '<div class="aiad-rd-section"><strong class="aiad-rd-label">' . esc_html__( 'Duration', 'ai-awareness-day' ) . '</strong>';
    echo '<input type="text" name="aiad_duration" value="' . esc_attr( $duration_str ) . '" class="regular-text" placeholder="' . esc_attr__( 'e.g. 5 min, 45 min, 2 x 50 min sessions', 'ai-awareness-day' ) . '" />';
    echo '<p class="description">' . esc_html__( 'Be specific. Not "as specified".', 'ai-awareness-day' ) . '</p></div>';

    // Level (Activity Schema: beginner, intermediate, advanced)
    echo '<div class="aiad-rd-section"><strong class="aiad-rd-label">' . esc_html__( 'Level', 'ai-awareness-day' ) . '</strong><div class="aiad-rd-radios">';
    $levels = array( 'beginner' => __( 'Beginner', 'ai-awareness-day' ), 'intermediate' => __( 'Intermediate', 'ai-awareness-day' ), 'advanced' => __( 'Advanced', 'ai-awareness-day' ) );
    foreach ( $levels as $slug => $label ) {
        echo '<label><input type="radio" name="aiad_level" value="' . esc_attr( $slug ) . '" ' . checked( $level, $slug, false ) . ' /> ' . esc_html( $label ) . '</label> ';
    }
    echo '</div></div>';

    // Status (Activity Schema: draft, in_review, published)
    echo '<div class="aiad-rd-section"><strong class="aiad-rd-label">' . esc_html__( 'Status', 'ai-awareness-day' ) . '</strong><div class="aiad-rd-radios">';
    $statuses = array( 'draft' => __( 'Draft', 'ai-awareness-day' ), 'in_review' => __( 'In review', 'ai-awareness-day' ), 'published' => __( 'Published', 'ai-awareness-day' ) );
    foreach ( $statuses as $slug => $label ) {
        echo '<label><input type="radio" name="aiad_status" value="' . esc_attr( $slug ) . '" ' . checked( $status, $slug, false ) . ' /> ' . esc_html( $label ) . '</label> ';
    }
    echo '</div></div>';

    // Format (radio)
    echo '<div class="aiad-rd-section"><strong class="aiad-rd-label">' . esc_html__( 'Format', 'ai-awareness-day' ) . '</strong><div class="aiad-rd-radios">';
    $current_type_slug = $current_type && ! is_wp_error( $current_type ) ? $current_type[0]->slug : '';
    foreach ( $type_terms as $term ) {
        if ( is_wp_error( $term ) ) {
            continue;
        }
        $id = 'aiad_type_' . $term->slug;
        echo '<label><input type="radio" name="aiad_resource_type" value="' . esc_attr( $term->slug ) . '" ' . checked( $current_type_slug, $term->slug, false ) . ' /> ' . esc_html( $term->name ) . '</label> ';
    }
    echo '</div></div>';

    // Theme (pill-style radios)
    echo '<div class="aiad-rd-section"><strong class="aiad-rd-label">' . esc_html__( 'Theme', 'ai-awareness-day' ) . '</strong><div class="aiad-rd-pills">';
    $current_theme_slug = $current_theme && ! is_wp_error( $current_theme ) ? $current_theme[0]->slug : '';
    foreach ( $theme_terms as $term ) {
        if ( is_wp_error( $term ) ) {
            continue;
        }
        $pill_class = in_array( $term->slug, $theme_slugs, true ) ? ' aiad-pill--' . $term->slug : '';
        $id = 'aiad_theme_' . $term->slug;
        echo '<label class="aiad-pill' . esc_attr( $pill_class ) . '"><input type="radio" name="aiad_resource_principle" value="' . esc_attr( $term->slug ) . '" ' . checked( $current_theme_slug, $term->slug, false ) . ' /> ' . esc_html( $term->name ) . '</label> ';
    }
    echo '</div></div>';

    // Session length (radio)
    echo '<div class="aiad-rd-section"><strong class="aiad-rd-label">' . esc_html__( 'Session length', 'ai-awareness-day' ) . '</strong><div class="aiad-rd-radios">';
    $current_duration_slug = $current_duration && ! is_wp_error( $current_duration ) ? $current_duration[0]->slug : '';
    foreach ( $duration_terms as $term ) {
        if ( is_wp_error( $term ) ) {
            continue;
        }
        $label = function_exists( 'aiad_duration_badge_label' ) ? aiad_duration_badge_label( $term ) : $term->name;
        echo '<label><input type="radio" name="aiad_resource_duration" value="' . esc_attr( $term->slug ) . '" ' . checked( $current_duration_slug, $term->slug, false ) . ' /> ' . esc_html( $label ) . '</label> ';
    }
    echo '</div></div>';

    // Activity type (checkboxes, multi-select)
    echo '<div class="aiad-rd-section"><strong class="aiad-rd-label">' . esc_html__( 'Activity type', 'ai-awareness-day' ) . '</strong><div class="aiad-rd-checkboxes">';
    $current_activity_slugs = $current_activities && ! is_wp_error( $current_activities ) ? wp_list_pluck( $current_activities, 'slug' ) : array();
    foreach ( $activity_terms as $term ) {
        if ( is_wp_error( $term ) ) {
            continue;
        }
        $checked = in_array( $term->slug, $current_activity_slugs, true );
        echo '<label><input type="checkbox" name="aiad_activity_type[]" value="' . esc_attr( $term->slug ) . '" ' . checked( $checked, true, false ) . ' /> ' . esc_html( $term->name ) . '</label><br>';
    }
    echo '</div></div>';

    // Download file
    echo '<div class="aiad-rd-section"><strong class="aiad-rd-label">' . esc_html__( 'Download file', 'ai-awareness-day' ) . '</strong><div class="aiad-rd-download">';
    echo '<input type="hidden" id="resource_download_url" name="resource_download_url" value="' . esc_attr( $download_url ) . '">';
    echo '<button type="button" class="button" id="aiad_upload_download_btn">' . esc_html__( 'Upload / Select PDF or PPTX', 'ai-awareness-day' ) . '</button> ';
    echo '<button type="button" class="button" id="aiad_remove_download_btn" style="' . ( $download_url ? '' : 'display:none;' ) . '">' . esc_html__( 'Remove', 'ai-awareness-day' ) . '</button>';
    echo '<p id="aiad_download_filename" class="description" style="' . ( $filename ? '' : 'display:none;' ) . '">' . esc_html__( 'File:', 'ai-awareness-day' ) . ' <strong>' . esc_html( $filename ) . '</strong></p>';
    echo '</div></div>';

    // Key Stage (checkboxes)
    echo '<div class="aiad-rd-section"><strong class="aiad-rd-label">' . esc_html__( 'Key stage', 'ai-awareness-day' ) . '</strong><div class="aiad-rd-checkboxes">';
    $key_stage_opts = aiad_key_stage_options();
    foreach ( $key_stage_opts as $slug => $label ) {
        $checked = in_array( $slug, $key_stages, true );
        echo '<label><input type="checkbox" name="aiad_key_stage[]" value="' . esc_attr( $slug ) . '" ' . checked( $checked, true, false ) . ' /> ' . esc_html( $label ) . '</label> ';
    }
    echo '</div></div>';

    echo '</div>';
}

/**
 * Save Resource Details (terms, download URL, key stage)
 *
 * @param int $post_id Resource post ID.
 */
function aiad_save_resource_details( int $post_id ): void {
    if ( ! isset( $_POST['aiad_resource_details_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aiad_resource_details_nonce'] ) ), 'aiad_resource_details_nonce' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( get_post_type( $post_id ) !== 'resource' ) {
        return;
    }

    $type = isset( $_POST['aiad_resource_type'] ) ? sanitize_text_field( wp_unslash( $_POST['aiad_resource_type'] ) ) : '';
    if ( $type ) {
        wp_set_object_terms( $post_id, array( $type ), 'resource_type' );
    }

    $principle = isset( $_POST['aiad_resource_principle'] ) ? sanitize_text_field( wp_unslash( $_POST['aiad_resource_principle'] ) ) : '';
    if ( $principle ) {
        wp_set_object_terms( $post_id, array( $principle ), 'resource_principle' );
    }

    $duration = isset( $_POST['aiad_resource_duration'] ) ? sanitize_text_field( wp_unslash( $_POST['aiad_resource_duration'] ) ) : '';
    if ( $duration ) {
        wp_set_object_terms( $post_id, array( $duration ), 'resource_duration' );
    }

    $activities = array();
    if ( ! empty( $_POST['aiad_activity_type'] ) && is_array( $_POST['aiad_activity_type'] ) ) {
        $activities = array_map( 'sanitize_text_field', wp_unslash( $_POST['aiad_activity_type'] ) );
    }
    wp_set_object_terms( $post_id, $activities, 'activity_type' );

    if ( isset( $_POST['resource_download_url'] ) ) {
        update_post_meta( $post_id, '_resource_download_url', esc_url_raw( wp_unslash( $_POST['resource_download_url'] ) ) );
    }

    $key_stages = array();
    if ( ! empty( $_POST['aiad_key_stage'] ) && is_array( $_POST['aiad_key_stage'] ) ) {
        $allowed = array_keys( aiad_key_stage_options() );
        $key_stages = array_values( array_intersect( array_map( 'sanitize_text_field', wp_unslash( $_POST['aiad_key_stage'] ) ), $allowed ) );
    }
    update_post_meta( $post_id, '_aiad_key_stage', $key_stages );

    if ( isset( $_POST['aiad_subtitle'] ) ) {
        update_post_meta( $post_id, '_aiad_subtitle', sanitize_text_field( wp_unslash( $_POST['aiad_subtitle'] ) ) );
    }
    if ( isset( $_POST['aiad_duration'] ) ) {
        update_post_meta( $post_id, '_aiad_duration', sanitize_text_field( wp_unslash( $_POST['aiad_duration'] ) ) );
    }
    if ( isset( $_POST['aiad_level'] ) ) {
        $l = sanitize_text_field( wp_unslash( $_POST['aiad_level'] ) );
        if ( in_array( $l, array( 'beginner', 'intermediate', 'advanced' ), true ) ) {
            update_post_meta( $post_id, '_aiad_level', $l );
        }
    }
    if ( isset( $_POST['aiad_status'] ) ) {
        $s = sanitize_text_field( wp_unslash( $_POST['aiad_status'] ) );
        if ( in_array( $s, array( 'draft', 'in_review', 'published' ), true ) ) {
            update_post_meta( $post_id, '_aiad_status', $s );
        }
    }
}
add_action( 'save_post_resource', 'aiad_save_resource_details' );

/**
 * Meta box: Resource content sections (definitions, objectives, instructions, discussion, teacher notes)
 */
function aiad_resource_content_sections_meta_box(): void {
    add_meta_box(
        'aiad_resource_content_sections',
        __( 'Resource content sections', 'ai-awareness-day' ),
        'aiad_resource_content_sections_callback',
        'resource',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'aiad_resource_content_sections_meta_box' );

/**
 * Callback: Resource content sections meta box
 *
 * @param WP_Post $post Resource post.
 */
/**
 * Normalise learning objectives to array of { objective, assessable } (Activity Schema v1).
 *
 * @param mixed $raw Meta value.
 * @return array<int, array{objective: string, assessable: bool}>
 */
function aiad_normalise_learning_objectives( $raw ): array {
    if ( ! is_array( $raw ) ) {
        if ( is_string( $raw ) && $raw !== '' ) {
            $lines = array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $raw ) ) ) );
            return array_map( function ( $line ) {
                return array( 'objective' => $line, 'assessable' => false );
            }, $lines );
        }
        return array();
    }
    $out = array();
    foreach ( $raw as $item ) {
        if ( is_array( $item ) && isset( $item['objective'] ) ) {
            $out[] = array(
                'objective'  => isset( $item['objective'] ) ? (string) $item['objective'] : '',
                'assessable' => ! empty( $item['assessable'] ),
            );
        } elseif ( is_string( $item ) && $item !== '' ) {
            $out[] = array( 'objective' => $item, 'assessable' => false );
        }
    }
    return $out;
}

/**
 * Normalise instructions to array of step objects (Activity Schema v1).
 *
 * @param mixed $raw Meta value.
 * @return array<int, array{step: int, action: string, duration?: string, resource_ref?: string, student_action?: string, teacher_tip?: string}>
 */
function aiad_normalise_instructions( $raw ): array {
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

function aiad_resource_content_sections_callback( WP_Post $post ): void {
    wp_nonce_field( 'aiad_content_sections_nonce', 'aiad_content_sections_nonce' );

    $key_definitions    = (array) get_post_meta( $post->ID, '_aiad_key_definitions', true );
    $learning_obj      = get_post_meta( $post->ID, '_aiad_learning_objectives', true );
    $instructions      = get_post_meta( $post->ID, '_aiad_instructions', true );
    $discussion_prompts = get_post_meta( $post->ID, '_aiad_discussion_prompts', true );
    $discussion_q      = get_post_meta( $post->ID, '_aiad_discussion_question', true );
    $suggested_answers = get_post_meta( $post->ID, '_aiad_suggested_answers', true );
    $teacher_notes     = get_post_meta( $post->ID, '_aiad_teacher_notes', true );
    $preparation       = (array) get_post_meta( $post->ID, '_aiad_preparation', true );
    $differentiation   = (array) get_post_meta( $post->ID, '_aiad_differentiation', true );
    $extensions        = (array) get_post_meta( $post->ID, '_aiad_extensions', true );
    $resources         = (array) get_post_meta( $post->ID, '_aiad_resources', true );

    $learning_obj  = aiad_normalise_learning_objectives( $learning_obj );
    $instructions  = aiad_normalise_instructions( $instructions );
    if ( is_string( $discussion_prompts ) ) {
        $discussion_prompts = $discussion_prompts !== '' ? array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $discussion_prompts ) ) ) ) : array();
    }
    $discussion_prompts = is_array( $discussion_prompts ) ? $discussion_prompts : array();
    if ( is_string( $suggested_answers ) ) {
        $suggested_answers = $suggested_answers !== '' ? array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $suggested_answers ) ) ) ) : array();
    }
    $suggested_answers = is_array( $suggested_answers ) ? $suggested_answers : array();

    $diff_support = isset( $differentiation['support'] ) ? $differentiation['support'] : '';
    $diff_stretch = isset( $differentiation['stretch'] ) ? $differentiation['stretch'] : '';
    $diff_send    = isset( $differentiation['send'] ) ? $differentiation['send'] : '';

    echo '<p class="description" style="margin-bottom: 1rem;">' . esc_html__( 'Structured content (Activity Schema v1). Use Add/Remove for consistent formatting.', 'ai-awareness-day' ) . '</p>';

    echo '<div class="aiad-content-sections-fields">';

    // Preparation (required in schema)
    echo '<div class="aiad-cs-field" style="margin-bottom: 1.5rem;"><strong class="aiad-rd-label">' . esc_html__( 'Preparation', 'ai-awareness-day' ) . '</strong>';
    echo '<p class="description">' . esc_html__( 'What the teacher must have ready before the lesson. One concrete action per item.', 'ai-awareness-day' ) . '</p>';
    echo '<div class="aiad-repeatable-list" data-name="aiad_preparation">';
    if ( empty( $preparation ) ) {
        $preparation = array( '' );
    }
    foreach ( $preparation as $i => $val ) {
        echo '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem;"><input type="text" name="aiad_preparation[]" value="' . esc_attr( is_string( $val ) ? $val : '' ) . '" class="large-text" /> <button type="button" class="button button-small aiad-remove-row">' . esc_html__( 'Remove', 'ai-awareness-day' ) . '</button></div>';
    }
    echo '</div><button type="button" class="button aiad-add-row" data-name="aiad_preparation">' . esc_html__( 'Add item', 'ai-awareness-day' ) . '</button></div>';

    echo '<div class="aiad-cs-field aiad-repeatable-definitions" style="margin-bottom: 1.5rem;"><strong class="aiad-rd-label">' . esc_html__( 'Key definitions', 'ai-awareness-day' ) . '</strong>';
    echo '<p class="description">' . esc_html__( 'Term, definition; tick if simplified for key stage.', 'ai-awareness-day' ) . '</p>';
    echo '<div class="aiad-repeatable-rows" data-name-prefix="aiad_key_definitions">';
    if ( empty( $key_definitions ) ) {
        $key_definitions = array( array( 'term' => '', 'definition' => '', 'key_stage_adapted' => false ) );
    }
    foreach ( $key_definitions as $i => $item ) {
        $term   = is_array( $item ) && isset( $item['term'] ) ? $item['term'] : '';
        $def    = is_array( $item ) && isset( $item['definition'] ) ? $item['definition'] : '';
        $ks_adapt = ! empty( $item['key_stage_adapted'] );
        echo '<div class="aiad-repeatable-row" style="margin-bottom: 0.75rem; padding: 0.5rem; background: #f6f7f7; border-radius: 4px;">';
        echo '<label style="display:block; margin-bottom: 0.25rem;">' . esc_html__( 'Term', 'ai-awareness-day' ) . '</label>';
        echo '<input type="text" name="aiad_key_definitions[' . (int) $i . '][term]" value="' . esc_attr( $term ) . '" class="regular-text" style="margin-bottom: 0.5rem;" /> ';
        echo '<label style="display:block; margin-bottom: 0.25rem;">' . esc_html__( 'Definition', 'ai-awareness-day' ) . '</label>';
        echo '<textarea name="aiad_key_definitions[' . (int) $i . '][definition]" rows="2" class="large-text" style="width:100%;">' . esc_textarea( $def ) . '</textarea>';
        echo '<label style="display:inline-block; margin-left: 0.5rem;"><input type="checkbox" name="aiad_key_definitions[' . (int) $i . '][key_stage_adapted]" value="1" ' . checked( $ks_adapt, true, false ) . ' /> ' . esc_html__( 'Key stage adapted', 'ai-awareness-day' ) . '</label>';
        echo ' <button type="button" class="button button-small aiad-remove-row">' . esc_html__( 'Remove', 'ai-awareness-day' ) . '</button>';
        echo '</div>';
    }
    echo '</div><button type="button" class="button aiad-add-definition">' . esc_html__( 'Add definition', 'ai-awareness-day' ) . '</button></div>';

    // Learning objectives: objective + assessable (min 2, max 5; Bloom's verb hint)
    echo '<div class="aiad-cs-field" style="margin-bottom: 1.5rem;"><strong class="aiad-rd-label">' . esc_html__( 'Learning objectives', 'ai-awareness-day' ) . '</strong>';
    echo '<p class="description">' . esc_html__( 'Start with a Bloom\'s verb (understand, recognise, analyse, evaluate, create, apply). Min 2, max 5.', 'ai-awareness-day' ) . '</p>';
    echo '<div class="aiad-repeatable-list" data-name="aiad_learning_objectives">';
    if ( empty( $learning_obj ) ) {
        $learning_obj = array( array( 'objective' => '', 'assessable' => false ) );
    }
    foreach ( $learning_obj as $i => $ob ) {
        $obj_text = is_array( $ob ) ? ( $ob['objective'] ?? '' ) : (string) $ob;
        $assess  = is_array( $ob ) && ! empty( $ob['assessable'] );
        echo '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem; padding: 0.35rem 0;">';
        echo '<input type="text" name="aiad_learning_objectives[' . (int) $i . '][objective]" value="' . esc_attr( $obj_text ) . '" class="large-text" placeholder="' . esc_attr__( 'e.g. Understand that AI predicts patterns', 'ai-awareness-day' ) . '" /> ';
        echo '<label><input type="checkbox" name="aiad_learning_objectives[' . (int) $i . '][assessable]" value="1" ' . checked( $assess, true, false ) . ' /> ' . esc_html__( 'Assessable', 'ai-awareness-day' ) . '</label>';
        echo ' <button type="button" class="button button-small aiad-remove-row">' . esc_html__( 'Remove', 'ai-awareness-day' ) . '</button></div>';
    }
    echo '</div><button type="button" class="button aiad-add-row" data-name="aiad_learning_objectives">' . esc_html__( 'Add objective', 'ai-awareness-day' ) . '</button></div>';

    // Instructions: rich steps (action, duration, resource_ref, student_action, teacher_tip)
    echo '<div class="aiad-cs-field" style="margin-bottom: 1.5rem;"><strong class="aiad-rd-label">' . esc_html__( 'Instructions', 'ai-awareness-day' ) . '</strong>';
    echo '<p class="description">' . esc_html__( 'Teacher script. At least one step should have a duration. Min 2 steps.', 'ai-awareness-day' ) . '</p>';
    echo '<div class="aiad-repeatable-list aiad-instruction-steps" data-name="aiad_instructions">';
    if ( empty( $instructions ) ) {
        $instructions = array( array( 'step' => 1, 'action' => '', 'duration' => '', 'resource_ref' => '', 'student_action' => '', 'teacher_tip' => '' ) );
    }
    foreach ( $instructions as $i => $step ) {
        $st = is_array( $step ) ? $step : array( 'step' => $i + 1, 'action' => (string) $step );
        $action   = isset( $st['action'] ) ? $st['action'] : '';
        $duration = isset( $st['duration'] ) ? $st['duration'] : '';
        $res_ref  = isset( $st['resource_ref'] ) ? $st['resource_ref'] : '';
        $stu_act  = isset( $st['student_action'] ) ? $st['student_action'] : '';
        $tip      = isset( $st['teacher_tip'] ) ? $st['teacher_tip'] : '';
        $step_num = isset( $st['step'] ) ? (int) $st['step'] : ( $i + 1 );
        echo '<div class="aiad-repeatable-row aiad-instruction-row" style="margin-bottom: 1rem; padding: 0.75rem; background: #f6f7f7; border-radius: 4px;">';
        echo '<label>Step <input type="number" name="aiad_instructions[' . (int) $i . '][step]" value="' . esc_attr( (string) $step_num ) . '" min="1" style="width:4em;" /></label> ';
        echo '<label>' . esc_html__( 'Duration', 'ai-awareness-day' ) . ' <input type="text" name="aiad_instructions[' . (int) $i . '][duration]" value="' . esc_attr( $duration ) . '" placeholder="e.g. 60 seconds" style="width:10em;" /></label><br style="margin-bottom:0.5rem;" />';
        echo '<label style="display:block; margin-top:0.35rem;">' . esc_html__( 'Action', 'ai-awareness-day' ) . '</label>';
        echo '<textarea name="aiad_instructions[' . (int) $i . '][action]" rows="2" class="large-text" style="width:100%;">' . esc_textarea( $action ) . '</textarea>';
        echo '<label style="display:block; margin-top:0.35rem;">' . esc_html__( 'Resource ref', 'ai-awareness-day' ) . ' <input type="text" name="aiad_instructions[' . (int) $i . '][resource_ref]" value="' . esc_attr( $res_ref ) . '" placeholder="e.g. Slide 6" class="regular-text" /></label>';
        echo '<label style="display:block; margin-top:0.35rem;">' . esc_html__( 'Student action', 'ai-awareness-day' ) . ' <input type="text" name="aiad_instructions[' . (int) $i . '][student_action]" value="' . esc_attr( $stu_act ) . '" placeholder="e.g. Pair discussion" class="large-text" /></label>';
        echo '<label style="display:block; margin-top:0.35rem;">' . esc_html__( 'Teacher tip', 'ai-awareness-day' ) . ' <textarea name="aiad_instructions[' . (int) $i . '][teacher_tip]" rows="1" class="large-text" style="width:100%;">' . esc_textarea( $tip ) . '</textarea></label>';
        echo ' <button type="button" class="button button-small aiad-remove-row" style="margin-top:0.5rem;">' . esc_html__( 'Remove step', 'ai-awareness-day' ) . '</button>';
        echo '</div>';
    }
    echo '</div><button type="button" class="button aiad-add-instruction">' . esc_html__( 'Add step', 'ai-awareness-day' ) . '</button></div>';

    echo '<div class="aiad-cs-field" style="margin-bottom: 1.5rem;"><strong class="aiad-rd-label">' . esc_html__( 'Discussion prompts', 'ai-awareness-day' ) . '</strong>';
    echo '<div class="aiad-repeatable-list" data-name="aiad_discussion_prompts">';
    if ( empty( $discussion_prompts ) ) {
        $discussion_prompts = array( '' );
    }
    foreach ( $discussion_prompts as $i => $val ) {
        echo '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem;"><input type="text" name="aiad_discussion_prompts[]" value="' . esc_attr( $val ) . '" class="large-text" /> <button type="button" class="button button-small aiad-remove-row">' . esc_html__( 'Remove', 'ai-awareness-day' ) . '</button></div>';
    }
    echo '</div><button type="button" class="button aiad-add-row" data-name="aiad_discussion_prompts">' . esc_html__( 'Add prompt', 'ai-awareness-day' ) . '</button></div>';

    echo '<div class="aiad-cs-field" style="margin-bottom: 1.25rem;"><label for="aiad_discussion_question"><strong>' . esc_html__( 'Discussion question', 'ai-awareness-day' ) . '</strong></label>';
    echo '<input type="text" id="aiad_discussion_question" name="aiad_discussion_question" value="' . esc_attr( $discussion_q ) . '" class="large-text" style="width:100%;" /></div>';

    echo '<div class="aiad-cs-field" style="margin-bottom: 1.5rem;"><strong class="aiad-rd-label">' . esc_html__( 'Suggested answers', 'ai-awareness-day' ) . '</strong>';
    echo '<div class="aiad-repeatable-list" data-name="aiad_suggested_answers">';
    if ( empty( $suggested_answers ) ) {
        $suggested_answers = array( '' );
    }
    foreach ( $suggested_answers as $i => $val ) {
        echo '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem;"><input type="text" name="aiad_suggested_answers[]" value="' . esc_attr( $val ) . '" class="large-text" /> <button type="button" class="button button-small aiad-remove-row">' . esc_html__( 'Remove', 'ai-awareness-day' ) . '</button></div>';
    }
    echo '</div><button type="button" class="button aiad-add-row" data-name="aiad_suggested_answers">' . esc_html__( 'Add answer', 'ai-awareness-day' ) . '</button></div>';

    echo '<div class="aiad-cs-field" style="margin-bottom: 1.25rem;"><label for="aiad_teacher_notes"><strong>' . esc_html__( 'Teacher notes (optional)', 'ai-awareness-day' ) . '</strong></label>';
    echo '<p class="description">' . esc_html__( 'Optional background, misconceptions, and tips for teachers. This does not affect publishing.', 'ai-awareness-day' ) . '</p>';
    echo '<textarea id="aiad_teacher_notes" name="aiad_teacher_notes" rows="5" class="large-text" style="width:100%;">' . esc_textarea( $teacher_notes ) . '</textarea></div>';

    // Differentiation (support, stretch, send)
    echo '<div class="aiad-cs-field" style="margin-bottom: 1.5rem;"><strong class="aiad-rd-label">' . esc_html__( 'Differentiation', 'ai-awareness-day' ) . '</strong>';
    echo '<p class="description">' . esc_html__( 'How to adapt for different learners.', 'ai-awareness-day' ) . '</p>';
    echo '<label style="display:block; margin-top:0.5rem;">' . esc_html__( 'Support (struggling)', 'ai-awareness-day' ) . '</label><textarea name="aiad_differentiation[support]" rows="2" class="large-text" style="width:100%;">' . esc_textarea( $diff_support ) . '</textarea>';
    echo '<label style="display:block; margin-top:0.5rem;">' . esc_html__( 'Stretch (high ability)', 'ai-awareness-day' ) . '</label><textarea name="aiad_differentiation[stretch]" rows="2" class="large-text" style="width:100%;">' . esc_textarea( $diff_stretch ) . '</textarea>';
    echo '<label style="display:block; margin-top:0.5rem;">' . esc_html__( 'SEND (additional needs)', 'ai-awareness-day' ) . '</label><textarea name="aiad_differentiation[send]" rows="2" class="large-text" style="width:100%;">' . esc_textarea( $diff_send ) . '</textarea></div>';

    // Extension activities
    $extension_types = array( 'homework' => __( 'Homework', 'ai-awareness-day' ), 'next_lesson' => __( 'Next lesson', 'ai-awareness-day' ), 'cross_curricular' => __( 'Cross-curricular', 'ai-awareness-day' ), 'independent' => __( 'Independent', 'ai-awareness-day' ) );
    echo '<div class="aiad-cs-field" style="margin-bottom: 1.5rem;"><strong class="aiad-rd-label">' . esc_html__( 'Extension activities', 'ai-awareness-day' ) . '</strong>';
    echo '<p class="description">' . esc_html__( 'Specific, actionable task + type.', 'ai-awareness-day' ) . '</p>';
    echo '<div class="aiad-repeatable-list" data-name="aiad_extensions">';
    if ( empty( $extensions ) ) {
        $extensions = array( array( 'activity' => '', 'type' => 'homework' ) );
    }
    foreach ( $extensions as $i => $ext ) {
        $act = is_array( $ext ) ? ( $ext['activity'] ?? '' ) : '';
        $typ = is_array( $ext ) && isset( $ext['type'] ) ? $ext['type'] : 'homework';
        if ( ! array_key_exists( $typ, $extension_types ) ) {
            $typ = 'homework';
        }
        echo '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem;">';
        echo '<input type="text" name="aiad_extensions[' . (int) $i . '][activity]" value="' . esc_attr( $act ) . '" class="large-text" placeholder="' . esc_attr__( 'Specific task', 'ai-awareness-day' ) . '" /> ';
        echo '<select name="aiad_extensions[' . (int) $i . '][type]">';
        foreach ( $extension_types as $k => $v ) {
            echo '<option value="' . esc_attr( $k ) . '" ' . selected( $typ, $k, false ) . '>' . esc_html( $v ) . '</option>';
        }
        echo '</select> <button type="button" class="button button-small aiad-remove-row">' . esc_html__( 'Remove', 'ai-awareness-day' ) . '</button></div>';
    }
    echo '</div><button type="button" class="button aiad-add-extension">' . esc_html__( 'Add extension', 'ai-awareness-day' ) . '</button></div>';

    // Resources (name, type, url)
    $resource_types = array( 'slides' => __( 'Slides', 'ai-awareness-day' ), 'worksheet' => __( 'Worksheet', 'ai-awareness-day' ), 'handout' => __( 'Handout', 'ai-awareness-day' ), 'video' => __( 'Video', 'ai-awareness-day' ), 'link' => __( 'Link', 'ai-awareness-day' ), 'other' => __( 'Other', 'ai-awareness-day' ) );
    echo '<div class="aiad-cs-field" style="margin-bottom: 1.5rem;"><strong class="aiad-rd-label">' . esc_html__( 'Resources', 'ai-awareness-day' ) . '</strong>';
    echo '<p class="description">' . esc_html__( 'Slide deck, worksheet, etc. (optional link).', 'ai-awareness-day' ) . '</p>';
    echo '<div class="aiad-repeatable-list" data-name="aiad_resources">';
    if ( empty( $resources ) ) {
        $resources = array( array( 'name' => '', 'type' => 'other', 'url' => '' ) );
    }
    foreach ( $resources as $i => $res ) {
        $name = is_array( $res ) ? ( $res['name'] ?? '' ) : '';
        $type = is_array( $res ) && isset( $res['type'] ) ? $res['type'] : 'other';
        $url  = is_array( $res ) && isset( $res['url'] ) ? $res['url'] : '';
        if ( ! array_key_exists( $type, $resource_types ) ) {
            $type = 'other';
        }
        echo '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem;">';
        echo '<input type="text" name="aiad_resources[' . (int) $i . '][name]" value="' . esc_attr( $name ) . '" placeholder="' . esc_attr__( 'e.g. Slide deck', 'ai-awareness-day' ) . '" class="regular-text" /> ';
        echo '<select name="aiad_resources[' . (int) $i . '][type]">';
        foreach ( $resource_types as $k => $v ) {
            echo '<option value="' . esc_attr( $k ) . '" ' . selected( $type, $k, false ) . '>' . esc_html( $v ) . '</option>';
        }
        echo '</select> <input type="url" name="aiad_resources[' . (int) $i . '][url]" value="' . esc_attr( $url ) . '" placeholder="URL" class="medium-text" /> ';
        echo '<button type="button" class="button button-small aiad-remove-row">' . esc_html__( 'Remove', 'ai-awareness-day' ) . '</button></div>';
    }
    echo '</div><button type="button" class="button aiad-add-resource">' . esc_html__( 'Add resource', 'ai-awareness-day' ) . '</button></div>';

    echo '</div>';
    ?>
    <script>
    jQuery(function($) {
        $(document).on('click', '.aiad-remove-row', function() {
            var row = $(this).closest('.aiad-repeatable-row');
            if ( row.siblings('.aiad-repeatable-row').length >= 1 ) {
                row.remove();
            }
        });
        $(document).on('click', '.aiad-add-row', function() {
            var name = $(this).data('name');
            var list = $(this).prev('.aiad-repeatable-list');
            var idx = list.find('.aiad-repeatable-row').length;
            var html;
            if ( name === 'aiad_preparation' ) {
                html = '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem;"><input type="text" name="aiad_preparation[]" value="" class="large-text" /> <button type="button" class="button button-small aiad-remove-row"><?php echo esc_js( __( 'Remove', 'ai-awareness-day' ) ); ?></button></div>';
            } else if ( name === 'aiad_learning_objectives' ) {
                html = '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem; padding: 0.35rem 0;"><input type="text" name="aiad_learning_objectives[' + idx + '][objective]" value="" class="large-text" /> <label><input type="checkbox" name="aiad_learning_objectives[' + idx + '][assessable]" value="1" /> <?php echo esc_js( __( 'Assessable', 'ai-awareness-day' ) ); ?></label> <button type="button" class="button button-small aiad-remove-row"><?php echo esc_js( __( 'Remove', 'ai-awareness-day' ) ); ?></button></div>';
            } else {
                html = '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem;"><input type="text" name="' + name + '[]" value="" class="large-text" /> <button type="button" class="button button-small aiad-remove-row"><?php echo esc_js( __( 'Remove', 'ai-awareness-day' ) ); ?></button></div>';
            }
            list.append(html);
        });
        $(document).on('click', '.aiad-add-definition', function() {
            var container = $(this).prev('.aiad-repeatable-rows');
            var idx = container.find('.aiad-repeatable-row').length;
            var html = '<div class="aiad-repeatable-row" style="margin-bottom: 0.75rem; padding: 0.5rem; background: #f6f7f7; border-radius: 4px;">' +
                '<label style="display:block;"><?php echo esc_js( __( 'Term', 'ai-awareness-day' ) ); ?></label><input type="text" name="aiad_key_definitions[' + idx + '][term]" value="" class="regular-text" style="margin-bottom: 0.5rem;" /> ' +
                '<label style="display:block;"><?php echo esc_js( __( 'Definition', 'ai-awareness-day' ) ); ?></label><textarea name="aiad_key_definitions[' + idx + '][definition]" rows="2" class="large-text" style="width:100%;"></textarea> ' +
                '<label style="display:inline-block; margin-left: 0.5rem;"><input type="checkbox" name="aiad_key_definitions[' + idx + '][key_stage_adapted]" value="1" /> <?php echo esc_js( __( 'Key stage adapted', 'ai-awareness-day' ) ); ?></label> ' +
                '<button type="button" class="button button-small aiad-remove-row"><?php echo esc_js( __( 'Remove', 'ai-awareness-day' ) ); ?></button></div>';
            container.append(html);
        });
        $(document).on('click', '.aiad-add-instruction', function() {
            var list = $(this).prev('.aiad-repeatable-list');
            var idx = list.find('.aiad-repeatable-row').length;
            var stepNum = idx + 1;
            var html = '<div class="aiad-repeatable-row aiad-instruction-row" style="margin-bottom: 1rem; padding: 0.75rem; background: #f6f7f7; border-radius: 4px;">' +
                '<label>Step <input type="number" name="aiad_instructions[' + idx + '][step]" value="' + stepNum + '" min="1" style="width:4em;" /></label> ' +
                '<label><?php echo esc_js( __( 'Duration', 'ai-awareness-day' ) ); ?> <input type="text" name="aiad_instructions[' + idx + '][duration]" value="" placeholder="e.g. 60 seconds" style="width:10em;" /></label><br style="margin-bottom:0.5rem;" />' +
                '<label style="display:block; margin-top:0.35rem;"><?php echo esc_js( __( 'Action', 'ai-awareness-day' ) ); ?></label>' +
                '<textarea name="aiad_instructions[' + idx + '][action]" rows="2" class="large-text" style="width:100%;"></textarea>' +
                '<label style="display:block; margin-top:0.35rem;"><?php echo esc_js( __( 'Resource ref', 'ai-awareness-day' ) ); ?> <input type="text" name="aiad_instructions[' + idx + '][resource_ref]" value="" placeholder="e.g. Slide 6" class="regular-text" /></label>' +
                '<label style="display:block; margin-top:0.35rem;"><?php echo esc_js( __( 'Student action', 'ai-awareness-day' ) ); ?> <input type="text" name="aiad_instructions[' + idx + '][student_action]" value="" class="large-text" /></label>' +
                '<label style="display:block; margin-top:0.35rem;"><?php echo esc_js( __( 'Teacher tip', 'ai-awareness-day' ) ); ?> <textarea name="aiad_instructions[' + idx + '][teacher_tip]" rows="1" class="large-text" style="width:100%;"></textarea></label>' +
                ' <button type="button" class="button button-small aiad-remove-row" style="margin-top:0.5rem;"><?php echo esc_js( __( 'Remove step', 'ai-awareness-day' ) ); ?></button></div>';
            list.append(html);
        });
        $(document).on('click', '.aiad-add-extension', function() {
            var list = $(this).prev('.aiad-repeatable-list');
            var idx = list.find('.aiad-repeatable-row').length;
            var opts = '<?php
            $opts = array( 'homework' => __( 'Homework', 'ai-awareness-day' ), 'next_lesson' => __( 'Next lesson', 'ai-awareness-day' ), 'cross_curricular' => __( 'Cross-curricular', 'ai-awareness-day' ), 'independent' => __( 'Independent', 'ai-awareness-day' ) );
            $parts = array();
            foreach ( $opts as $k => $v ) {
                $parts[] = '<option value="' . esc_attr( $k ) . '">' . esc_html( $v ) . '</option>';
            }
            echo implode( '', $parts );
            ?>';
            var html = '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem;"><input type="text" name="aiad_extensions[' + idx + '][activity]" value="" class="large-text" /> <select name="aiad_extensions[' + idx + '][type]">' + opts + '</select> <button type="button" class="button button-small aiad-remove-row"><?php echo esc_js( __( 'Remove', 'ai-awareness-day' ) ); ?></button></div>';
            list.append(html);
        });
        $(document).on('click', '.aiad-add-resource', function() {
            var list = $(this).prev('.aiad-repeatable-list');
            var idx = list.find('.aiad-repeatable-row').length;
            var opts = '<?php
            $r_opts = array( 'slides' => __( 'Slides', 'ai-awareness-day' ), 'worksheet' => __( 'Worksheet', 'ai-awareness-day' ), 'handout' => __( 'Handout', 'ai-awareness-day' ), 'video' => __( 'Video', 'ai-awareness-day' ), 'link' => __( 'Link', 'ai-awareness-day' ), 'other' => __( 'Other', 'ai-awareness-day' ) );
            $r_parts = array();
            foreach ( $r_opts as $k => $v ) {
                $r_parts[] = '<option value="' . esc_attr( $k ) . '">' . esc_html( $v ) . '</option>';
            }
            echo implode( '', $r_parts );
            ?>';
            var html = '<div class="aiad-repeatable-row" style="margin-bottom: 0.5rem;"><input type="text" name="aiad_resources[' + idx + '][name]" value="" class="regular-text" /> <select name="aiad_resources[' + idx + '][type]">' + opts + '</select> <input type="url" name="aiad_resources[' + idx + '][url]" value="" class="medium-text" /> <button type="button" class="button button-small aiad-remove-row"><?php echo esc_js( __( 'Remove', 'ai-awareness-day' ) ); ?></button></div>';
            list.append(html);
        });
    });
    </script>
    <?php
}

/**
 * Save Resource content sections
 *
 * @param int $post_id Resource post ID.
 */
function aiad_save_resource_content_sections( int $post_id ): void {
    if ( ! isset( $_POST['aiad_content_sections_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aiad_content_sections_nonce'] ) ), 'aiad_content_sections_nonce' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( get_post_type( $post_id ) !== 'resource' ) {
        return;
    }

    $defs = array();
    if ( ! empty( $_POST['aiad_key_definitions'] ) && is_array( $_POST['aiad_key_definitions'] ) ) {
        foreach ( $_POST['aiad_key_definitions'] as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }
            $term   = isset( $item['term'] ) ? sanitize_text_field( wp_unslash( $item['term'] ) ) : '';
            $def    = isset( $item['definition'] ) ? sanitize_textarea_field( wp_unslash( $item['definition'] ) ) : '';
            $ks_adapt = ! empty( $item['key_stage_adapted'] );
            if ( $term !== '' || $def !== '' ) {
                $defs[] = array( 'term' => $term, 'definition' => $def, 'key_stage_adapted' => $ks_adapt );
            }
        }
    }
    update_post_meta( $post_id, '_aiad_key_definitions', $defs );

    if ( ! empty( $_POST['aiad_preparation'] ) && is_array( $_POST['aiad_preparation'] ) ) {
        $prep = array_values( array_filter( array_map( function ( $v ) {
            return sanitize_text_field( wp_unslash( $v ) );
        }, $_POST['aiad_preparation'] ) ) );
        update_post_meta( $post_id, '_aiad_preparation', $prep );
    }

    if ( ! empty( $_POST['aiad_learning_objectives'] ) && is_array( $_POST['aiad_learning_objectives'] ) ) {
        $objs = array();
        foreach ( $_POST['aiad_learning_objectives'] as $i => $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }
            $obj = isset( $item['objective'] ) ? sanitize_text_field( wp_unslash( $item['objective'] ) ) : '';
            if ( $obj === '' ) {
                continue;
            }
            $objs[] = array(
                'objective'  => $obj,
                'assessable' => ! empty( $item['assessable'] ),
            );
        }
        update_post_meta( $post_id, '_aiad_learning_objectives', $objs );
    }

    if ( ! empty( $_POST['aiad_instructions'] ) && is_array( $_POST['aiad_instructions'] ) ) {
        $steps = array();
        foreach ( $_POST['aiad_instructions'] as $i => $item ) {
            if ( ! is_array( $item ) || ! isset( $item['action'] ) ) {
                continue;
            }
            $action = sanitize_textarea_field( wp_unslash( $item['action'] ) );
            if ( $action === '' ) {
                continue;
            }
            $steps[] = array(
                'step'           => isset( $item['step'] ) ? max( 1, (int) $item['step'] ) : ( count( $steps ) + 1 ),
                'action'         => $action,
                'duration'       => isset( $item['duration'] ) ? sanitize_text_field( wp_unslash( $item['duration'] ) ) : '',
                'resource_ref'   => isset( $item['resource_ref'] ) ? sanitize_text_field( wp_unslash( $item['resource_ref'] ) ) : '',
                'student_action' => isset( $item['student_action'] ) ? sanitize_text_field( wp_unslash( $item['student_action'] ) ) : '',
                'teacher_tip'    => isset( $item['teacher_tip'] ) ? sanitize_textarea_field( wp_unslash( $item['teacher_tip'] ) ) : '',
            );
        }
        update_post_meta( $post_id, '_aiad_instructions', $steps );
    }

    $array_keys = array(
        '_aiad_discussion_prompts' => 'aiad_discussion_prompts',
        '_aiad_suggested_answers'   => 'aiad_suggested_answers',
    );
    foreach ( $array_keys as $meta_key => $post_key ) {
        if ( isset( $_POST[ $post_key ] ) && is_array( $_POST[ $post_key ] ) ) {
            $arr = array_values( array_filter( array_map( function ( $v ) {
                return is_string( $v ) ? sanitize_text_field( wp_unslash( $v ) ) : '';
            }, $_POST[ $post_key ] ) ) );
            update_post_meta( $post_id, $meta_key, $arr );
        }
    }

    if ( isset( $_POST['aiad_discussion_question'] ) ) {
        update_post_meta( $post_id, '_aiad_discussion_question', sanitize_text_field( wp_unslash( $_POST['aiad_discussion_question'] ) ) );
    }
    if ( isset( $_POST['aiad_teacher_notes'] ) ) {
        $notes = sanitize_textarea_field( wp_unslash( $_POST['aiad_teacher_notes'] ) );
        update_post_meta( $post_id, '_aiad_teacher_notes', $notes );
        if ( strlen( $notes ) > 0 && strlen( $notes ) < 100 ) {
            set_transient( 'aiad_teacher_notes_short_' . $post_id, 1, 30 );
        } else {
            delete_transient( 'aiad_teacher_notes_short_' . $post_id );
        }
    }

    if ( ! empty( $_POST['aiad_differentiation'] ) && is_array( $_POST['aiad_differentiation'] ) ) {
        $diff = array(
            'support' => isset( $_POST['aiad_differentiation']['support'] ) ? sanitize_textarea_field( wp_unslash( $_POST['aiad_differentiation']['support'] ) ) : '',
            'stretch' => isset( $_POST['aiad_differentiation']['stretch'] ) ? sanitize_textarea_field( wp_unslash( $_POST['aiad_differentiation']['stretch'] ) ) : '',
            'send'    => isset( $_POST['aiad_differentiation']['send'] ) ? sanitize_textarea_field( wp_unslash( $_POST['aiad_differentiation']['send'] ) ) : '',
        );
        update_post_meta( $post_id, '_aiad_differentiation', $diff );
    }

    if ( ! empty( $_POST['aiad_extensions'] ) && is_array( $_POST['aiad_extensions'] ) ) {
        $ext_types = array( 'homework', 'next_lesson', 'cross_curricular', 'independent' );
        $exts = array();
        foreach ( $_POST['aiad_extensions'] as $item ) {
            if ( ! is_array( $item ) || ! isset( $item['activity'] ) ) {
                continue;
            }
            $act = sanitize_text_field( wp_unslash( $item['activity'] ) );
            if ( $act === '' ) {
                continue;
            }
            $typ = isset( $item['type'] ) ? sanitize_text_field( wp_unslash( $item['type'] ) ) : 'homework';
            if ( ! in_array( $typ, $ext_types, true ) ) {
                $typ = 'homework';
            }
            $exts[] = array( 'activity' => $act, 'type' => $typ );
        }
        update_post_meta( $post_id, '_aiad_extensions', $exts );
    }

    if ( ! empty( $_POST['aiad_resources'] ) && is_array( $_POST['aiad_resources'] ) ) {
        $res_types = array( 'slides', 'worksheet', 'handout', 'video', 'link', 'other' );
        $res_list = array();
        foreach ( $_POST['aiad_resources'] as $item ) {
            if ( ! is_array( $item ) ) {
                continue;
            }
            $name = isset( $item['name'] ) ? sanitize_text_field( wp_unslash( $item['name'] ) ) : '';
            $type = isset( $item['type'] ) ? sanitize_text_field( wp_unslash( $item['type'] ) ) : 'other';
            if ( ! in_array( $type, $res_types, true ) ) {
                $type = 'other';
            }
            $url = isset( $item['url'] ) ? esc_url_raw( wp_unslash( $item['url'] ) ) : '';
            $res_list[] = array( 'name' => $name, 'type' => $type, 'url' => $url );
        }
        update_post_meta( $post_id, '_aiad_resources', $res_list );
    }
}
add_action( 'save_post_resource', 'aiad_save_resource_content_sections' );

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
    set_transient( 'aiad_schema_validation_errors_' . $post_id, array_unique( $errors ), 60 );
}
add_action( 'save_post_resource', 'aiad_validate_resource_on_save', 20 );

/**
 * Helper: get resource download label from URL (e.g. "Download PDF")
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
 * Admin: enqueue media and script for resource download upload
 */
function aiad_resource_download_admin_scripts( string $hook ): void {
    if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
        return;
    }
    $screen = get_current_screen();
    if ( ! $screen || $screen->post_type !== 'resource' ) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_style(
        'aiad-resource-editor',
        AIAD_URI . '/admin/css/aiad-resource-editor.css',
        array(),
        AIAD_VERSION
    );
    wp_register_script( 'aiad-resource-download-admin', false, array( 'jquery' ) );
    wp_enqueue_script( 'aiad-resource-download-admin' );
    wp_add_inline_script( 'aiad-resource-download-admin', "
jQuery(function($) {
    if ( typeof wp === 'undefined' || ! wp.media ) return;
    var frame;
    $('#aiad_upload_download_btn').on('click', function(e) {
        e.preventDefault();
        if ( frame ) { frame.open(); return; }
        frame = wp.media({
            title: 'Select or upload PDF or PPTX',
            library: { type: ['application/pdf','application/vnd.openxmlformats-officedocument.presentationml.presentation','application/vnd.ms-powerpoint'] },
            button: { text: 'Use this file' },
            multiple: false
        });
        frame.on('select', function() {
            var att = frame.state().get('selection').first().toJSON();
            if ( att && att.url ) {
                $('#resource_download_url').val(att.url);
                var name = att.filename || att.url.split('/').pop().split('?')[0];
                $('#aiad_download_filename strong').text(name);
                $('#aiad_download_filename').show();
                $('#aiad_remove_download_btn').show();
            }
        });
        frame.open();
    });
    $('#aiad_remove_download_btn').on('click', function(e) {
        e.preventDefault();
        $('#resource_download_url').val('');
        $('#aiad_download_filename').hide();
        $(this).hide();
    });
});
" );
}
add_action( 'admin_enqueue_scripts', 'aiad_resource_download_admin_scripts' );

/**
 * Admin notice on Resource edit screen: Type, Theme and Session length are all set here.
 * The sidebar items "Resource Types", "Themes", "Session length" are only for managing the options list.
 */
function aiad_resource_edit_screen_notice(): void {
    $screen = get_current_screen();
    if ( ! $screen || $screen->post_type !== 'resource' || $screen->base !== 'post' ) {
        return;
    }
    global $post;
    ?>
    <div class="notice notice-info is-dismissible" style="margin-top: 12px;">
        <p><?php esc_html_e( 'Set Resource Type, Theme, and Session length on this page (in the boxes on the right, or below on mobile). The separate "Resource Types", "Themes", and "Session length" items in the Resources menu are only for managing the list of options (e.g. adding a new theme); you don’t need to open them when adding a resource.', 'ai-awareness-day' ); ?></p>
    </div>
    <?php
    $schema_errors = $post && get_post_type( $post ) === 'resource' ? get_transient( 'aiad_schema_validation_errors_' . $post->ID ) : false;
    if ( $schema_errors && is_array( $schema_errors ) && ! empty( $schema_errors ) ) {
        $messages = array();
        if ( in_array( 'blocklist', $schema_errors, true ) ) {
            $messages[] = __( 'Remove generic filler phrases (e.g. "as specified", "follow the guidelines", "explore related topics"). Write specific content instead.', 'ai-awareness-day' );
        }
        if ( in_array( 'learning_objectives_count', $schema_errors, true ) ) {
            $messages[] = __( 'Learning objectives: require between 2 and 5 objectives.', 'ai-awareness-day' );
        }
        if ( in_array( 'instructions_duration', $schema_errors, true ) ) {
            $messages[] = __( 'Instructions: at least one step must have a duration (e.g. "60 seconds", "5 min").', 'ai-awareness-day' );
        }
        if ( ! empty( $messages ) ) {
            ?>
            <div class="notice notice-error is-dismissible" style="margin-top: 8px;">
                <p><strong><?php esc_html_e( 'Activity Schema validation failed. Status was set to "In review" until fixed.', 'ai-awareness-day' ); ?></strong></p>
                <ul style="list-style: disc; margin-left: 1.5rem;">
                    <?php foreach ( $messages as $msg ) : ?>
                        <li><?php echo esc_html( $msg ); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php
        }
    }
}
add_action( 'admin_notices', 'aiad_resource_edit_screen_notice' );

/**
 * Allow PDF and PPTX uploads in WordPress
 *
 * @param array<string, string> $mimes Mime types keyed by extension.
 * @return array<string, string>
 */
function aiad_allow_resource_upload_mimes( array $mimes ): array {
    $mimes['pdf']  = 'application/pdf';
    $mimes['pptx'] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
    $mimes['ppt']  = 'application/vnd.ms-powerpoint';
    return $mimes;
}
add_filter( 'upload_mimes', 'aiad_allow_resource_upload_mimes' );

/**
 * Enqueue Styles & Scripts
 */
function aiad_scripts(): void {
    // Intel Clear for titles
    wp_enqueue_style(
        'aiad-intel-font',
        'https://fonts.cdnfonts.com/css/intel-clear',
        array(),
        null
    );

    // Google Fonts (body)
    wp_enqueue_style(
        'aiad-google-fonts',
        'https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400&display=swap',
        array(),
        null
    );

    // Main stylesheet
    wp_enqueue_style(
        'aiad-style',
        get_stylesheet_uri(),
        array( 'aiad-intel-font', 'aiad-google-fonts' ),
        AIAD_VERSION
    );

    // Main script (defer on WordPress 6.3+ for better performance)
    $script_args = version_compare( get_bloginfo( 'version' ), '6.3', '>=' )
        ? array( 'in_footer' => true, 'strategy' => 'defer' )
        : true;
    wp_enqueue_script(
        'aiad-main',
        AIAD_URI . '/assets/js/main.js',
        array(),
        AIAD_VERSION,
        $script_args
    );

    // Localize for AJAX
    wp_localize_script( 'aiad-main', 'aiad_ajax', array(
        'url'          => admin_url( 'admin-ajax.php' ),
        'nonce'        => wp_create_nonce( 'aiad_contact_nonce' ),
        'filter_nonce' => wp_create_nonce( 'aiad_filter_nonce' ),
    ) );

    if ( is_post_type_archive( 'resource' ) || is_post_type_archive( 'featured_resource' ) ) {
        wp_enqueue_script(
            'aiad-resource-filters',
            AIAD_URI . '/assets/js/resource-filters.js',
            array( 'aiad-main' ),
            AIAD_VERSION,
            $script_args
        );
    }
}
add_action( 'wp_enqueue_scripts', 'aiad_scripts' );

/**
 * Theme Customizer Settings
 */
function aiad_customize_register( WP_Customize_Manager $wp_customize ): void {

    // === Header Logo ===
    $wp_customize->add_section( 'aiad_header', array(
        'title'    => __( 'Header', 'ai-awareness-day' ),
        'priority' => 29,
    ) );

    $wp_customize->add_setting( 'aiad_header_logo', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'aiad_header_logo', array(
        'label'       => __( 'Header Logo', 'ai-awareness-day' ),
        'description' => __( 'Logo image displayed in the header before the site title. If not set, the Hero Logo will be used.', 'ai-awareness-day' ),
        'section'     => 'aiad_header',
    ) ) );

    // === Hero Section ===
    $wp_customize->add_section( 'aiad_hero', array(
        'title'    => __( 'Hero Section', 'ai-awareness-day' ),
        'priority' => 30,
    ) );

    $wp_customize->add_setting( 'aiad_hero_logo', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'aiad_hero_logo', array(
        'label'       => __( 'Hero Logo', 'ai-awareness-day' ),
        'description' => __( 'Image shown above the date. Leave empty to show a placeholder.', 'ai-awareness-day' ),
        'section'     => 'aiad_hero',
    ) ) );

    $wp_customize->add_setting( 'aiad_hero_slogan', array(
        'default'           => 'Know it, Question it, Use it Wisely',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'aiad_hero_slogan', array(
        'label'   => __( 'Hero Slogan (under logo)', 'ai-awareness-day' ),
        'section' => 'aiad_hero',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'aiad_hero_title', array(
        'default'           => 'AI Awareness Day',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'aiad_hero_title', array(
        'label'   => __( 'Hero Title', 'ai-awareness-day' ),
        'section' => 'aiad_hero',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'aiad_hero_date', array(
        'default'           => 'Thursday 4th June 2026',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'aiad_hero_date', array(
        'label'   => __( 'Event Date Text', 'ai-awareness-day' ),
        'section' => 'aiad_hero',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'aiad_hero_subtitle', array(
        'default'           => 'A nationwide day for schools, students, and parents to explore AI together.',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'aiad_hero_subtitle', array(
        'label'   => __( 'Hero Subtitle', 'ai-awareness-day' ),
        'section' => 'aiad_hero',
        'type'    => 'textarea',
    ) );

    // === Campaign Section ===
    $wp_customize->add_section( 'aiad_campaign', array(
        'title'    => __( 'Campaign Section', 'ai-awareness-day' ),
        'priority' => 31,
    ) );

    $wp_customize->add_setting( 'aiad_campaign_title', array(
        'default'           => 'What is AI Awareness Day?',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'aiad_campaign_title', array(
        'label'   => __( 'Campaign Title', 'ai-awareness-day' ),
        'section' => 'aiad_campaign',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'aiad_campaign_text', array(
        'default'           => 'National AI Awareness Day (4th June 2026) is a new nationwide campaign designed to build AI literacy across UK schools. The model is simple: schools commit to running just one activity.',
        'sanitize_callback' => 'wp_kses_post',
    ) );
    $wp_customize->add_control( 'aiad_campaign_text', array(
        'label'   => __( 'Campaign Description', 'ai-awareness-day' ),
        'section' => 'aiad_campaign',
        'type'    => 'textarea',
    ) );

    $wp_customize->add_setting( 'aiad_campaign_text_2', array(
        'default'           => 'Our goal is to create a unified moment where the entire education community comes together to engage positively and critically with AI — preparing the next generation for a world increasingly shaped by intelligent technology.',
        'sanitize_callback' => 'wp_kses_post',
    ) );
    $wp_customize->add_control( 'aiad_campaign_text_2', array(
        'label'   => __( 'Campaign Paragraph 2', 'ai-awareness-day' ),
        'section' => 'aiad_campaign',
        'type'    => 'textarea',
    ) );

    // === Principle / Theme Badges (Safe, Smart, Creative, Responsible, Future) ===
    $wp_customize->add_section( 'aiad_badges', array(
        'title'       => __( 'Principle & Theme Badges', 'ai-awareness-day' ),
        'description' => __( 'Upload badge images for the Five Core Principles and the By theme links. Same images are used in both sections.', 'ai-awareness-day' ),
        'priority'    => 33,
    ) );

    $badge_slugs = array( 'safe', 'smart', 'creative', 'responsible', 'future' );
    $badge_labels = array(
        'safe'       => __( 'Safe', 'ai-awareness-day' ),
        'smart'      => __( 'Smart', 'ai-awareness-day' ),
        'creative'   => __( 'Creative', 'ai-awareness-day' ),
        'responsible'=> __( 'Responsible', 'ai-awareness-day' ),
        'future'     => __( 'Future', 'ai-awareness-day' ),
    );
    foreach ( $badge_slugs as $slug ) {
        $wp_customize->add_setting( 'aiad_badge_' . $slug, array(
            'default'           => '',
            'sanitize_callback' => 'absint',
        ) );
        $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'aiad_badge_' . $slug, array(
            'label'       => isset( $badge_labels[ $slug ] ) ? $badge_labels[ $slug ] : ucfirst( $slug ),
            'section'     => 'aiad_badges',
            'mime_type'   => 'image',
        ) ) );
    }

    $principle_defaults = array(
        'safe'        => array( 'title' => __( 'Safe', 'ai-awareness-day' ), 'desc' => __( 'Ensuring safe and secure interactions with AI technologies.', 'ai-awareness-day' ) ),
        'smart'       => array( 'title' => __( 'Smart', 'ai-awareness-day' ), 'desc' => __( 'Building intelligent understanding of how AI works.', 'ai-awareness-day' ) ),
        'creative'    => array( 'title' => __( 'Creative', 'ai-awareness-day' ), 'desc' => __( 'Harnessing AI as a tool for creativity and innovation.', 'ai-awareness-day' ) ),
        'responsible' => array( 'title' => __( 'Responsible', 'ai-awareness-day' ), 'desc' => __( 'Promoting ethical and responsible use of AI.', 'ai-awareness-day' ) ),
        'future'      => array( 'title' => __( 'Future', 'ai-awareness-day' ), 'desc' => __( 'Preparing for an AI-shaped future with confidence.', 'ai-awareness-day' ) ),
    );
    foreach ( $principle_defaults as $slug => $defaults ) {
        $wp_customize->add_setting( 'aiad_principle_title_' . $slug, array(
            'default'           => $defaults['title'],
            'sanitize_callback' => 'sanitize_text_field',
        ) );
        $wp_customize->add_control( 'aiad_principle_title_' . $slug, array(
            'label'   => sprintf( __( 'Principle "%s" title', 'ai-awareness-day' ), $defaults['title'] ),
            'section' => 'aiad_badges',
            'type'    => 'text',
        ) );
        $wp_customize->add_setting( 'aiad_principle_desc_' . $slug, array(
            'default'           => $defaults['desc'],
            'sanitize_callback' => 'sanitize_text_field',
        ) );
        $wp_customize->add_control( 'aiad_principle_desc_' . $slug, array(
            'label'   => sprintf( __( 'Principle "%s" description', 'ai-awareness-day' ), $defaults['title'] ),
            'section' => 'aiad_badges',
            'type'    => 'textarea',
        ) );
    }

    // === YouTube / Video Section ===
    $wp_customize->add_section( 'aiad_youtube', array(
        'title'    => __( 'YouTube / Video Section', 'ai-awareness-day' ),
        'priority' => 34,
    ) );

    $wp_customize->add_setting( 'aiad_youtube_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'aiad_youtube_url', array(
        'label'       => __( 'YouTube video URL', 'ai-awareness-day' ),
        'description' => __( 'Paste a YouTube link, e.g. https://www.youtube.com/watch?v=VIDEO_ID', 'ai-awareness-day' ),
        'section'     => 'aiad_youtube',
        'type'        => 'url',
    ) );

    $wp_customize->add_setting( 'aiad_youtube_title', array(
        'default'           => __( 'Watch', 'ai-awareness-day' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'aiad_youtube_title', array(
        'label'   => __( 'Section title', 'ai-awareness-day' ),
        'section' => 'aiad_youtube',
        'type'    => 'text',
    ) );

    // === Display board examples (Toolkit) ===
    $wp_customize->add_section( 'aiad_display_board', array(
        'title'    => __( 'Display board examples', 'ai-awareness-day' ),
        'description' => __( 'Optional images shown in the Toolkit display board guide. Upload photos of example boards to inspire teachers.', 'ai-awareness-day' ),
        'priority' => 35,
    ) );

    foreach ( array( 1, 2, 3 ) as $num ) {
        $wp_customize->add_setting( 'aiad_display_board_image_' . $num, array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ) );
        $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'aiad_display_board_image_' . $num, array(
            'label'   => sprintf( __( 'Example display board %d', 'ai-awareness-day' ), $num ),
            'section' => 'aiad_display_board',
        ) ) );
    }

    // === Contact / Get Involved ===
    $wp_customize->add_section( 'aiad_contact', array(
        'title'    => __( 'Get Involved Section', 'ai-awareness-day' ),
        'priority' => 36,
    ) );

    $wp_customize->add_setting( 'aiad_contact_title', array(
        'default'           => 'Get Involved',
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'aiad_contact_title', array(
        'label'   => __( 'Section Title', 'ai-awareness-day' ),
        'section' => 'aiad_contact',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'aiad_contact_desc', array(
        'default'           => 'Whether you\'re a teacher, school leader, parent, or organisation — we\'d love to hear from you. Join the movement and help shape how the next generation engages with AI.',
        'sanitize_callback' => 'wp_kses_post',
    ) );
    $wp_customize->add_control( 'aiad_contact_desc', array(
        'label'   => __( 'Contact Description', 'ai-awareness-day' ),
        'section' => 'aiad_contact',
        'type'    => 'textarea',
    ) );

    $wp_customize->add_setting( 'aiad_contact_email', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_email',
    ) );
    $wp_customize->add_control( 'aiad_contact_email', array(
        'label'       => __( 'Notification Email', 'ai-awareness-day' ),
        'description' => __( 'Form submissions are sent to this email.', 'ai-awareness-day' ),
        'section'     => 'aiad_contact',
        'type'        => 'email',
    ) );

    // === Social Links ===
    $wp_customize->add_section( 'aiad_social', array(
        'title'    => __( 'Social Links', 'ai-awareness-day' ),
        'priority' => 37,
    ) );

    $wp_customize->add_setting( 'aiad_linkedin', array(
        'default'           => '#',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'aiad_linkedin', array(
        'label'   => __( 'LinkedIn URL', 'ai-awareness-day' ),
        'section' => 'aiad_social',
        'type'    => 'url',
    ) );

    $wp_customize->add_setting( 'aiad_instagram', array(
        'default'           => '#',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'aiad_instagram', array(
        'label'   => __( 'Instagram URL', 'ai-awareness-day' ),
        'section' => 'aiad_social',
        'type'    => 'url',
    ) );
}
add_action( 'customize_register', 'aiad_customize_register' );

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
 * AJAX Contact Form Handler
 */
function aiad_handle_contact_form(): void {
    check_ajax_referer( 'aiad_contact_nonce', 'nonce' );

    $honeypot = isset( $_POST['aiad_website'] ) ? sanitize_text_field( wp_unslash( $_POST['aiad_website'] ) ) : '';
    if ( $honeypot !== '' ) {
        wp_send_json_error( array( 'message' => __( 'Submission rejected.', 'ai-awareness-day' ) ) );
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
        wp_send_json_error( array( 'message' => __( 'Something went wrong. Please try again later.', 'ai-awareness-day' ) ) );
    }
}
add_action( 'wp_ajax_aiad_contact', 'aiad_handle_contact_form' );
add_action( 'wp_ajax_nopriv_aiad_contact', 'aiad_handle_contact_form' );

/**
 * Add admin columns for form submissions
 */
function aiad_form_submission_columns( $columns ): array {
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = __( 'Name', 'ai-awareness-day' );
    $new_columns['email'] = __( 'Email', 'ai-awareness-day' );
    $new_columns['role'] = __( 'Role', 'ai-awareness-day' );
    $new_columns['date'] = $columns['date'];
    return $new_columns;
}
add_filter( 'manage_form_submission_posts_columns', 'aiad_form_submission_columns' );

/**
 * Populate admin columns for form submissions
 */
function aiad_form_submission_column_content( $column, $post_id ): void {
    switch ( $column ) {
        case 'email':
            $email = get_post_meta( $post_id, '_submission_email', true );
            if ( $email ) {
                echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
            }
            break;
        case 'role':
            $involved_as = get_post_meta( $post_id, '_submission_involved_as', true );
            $role_labels = array(
                'teacher'       => __( 'Teacher', 'ai-awareness-day' ),
                'parent'        => __( 'Parent', 'ai-awareness-day' ),
                'school_leader' => __( 'School leader', 'ai-awareness-day' ),
                'organisation' => __( 'Organisation', 'ai-awareness-day' ),
            );
            $role_display = isset( $role_labels[ $involved_as ] ) ? $role_labels[ $involved_as ] : $involved_as;
            echo esc_html( $role_display );
            break;
    }
}
add_action( 'manage_form_submission_posts_custom_column', 'aiad_form_submission_column_content', 10, 2 );

/**
 * Make email and role columns sortable
 */
function aiad_form_submission_sortable_columns( $columns ): array {
    $columns['email'] = 'email';
    $columns['role']  = 'role';
    return $columns;
}
add_filter( 'manage_edit-form_submission_sortable_columns', 'aiad_form_submission_sortable_columns' );

/**
 * Handle sorting by email and role meta keys in the admin list table
 */
function aiad_form_submission_orderby( WP_Query $query ): void {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( $query->get( 'post_type' ) !== 'form_submission' ) {
        return;
    }
    $orderby = $query->get( 'orderby' );
    if ( $orderby === 'email' ) {
        $query->set( 'meta_key', '_submission_email' );
        $query->set( 'orderby', 'meta_value' );
    } elseif ( $orderby === 'role' ) {
        $query->set( 'meta_key', '_submission_involved_as' );
        $query->set( 'orderby', 'meta_value' );
    }
}
add_action( 'pre_get_posts', 'aiad_form_submission_orderby' );

/**
 * Display submission details in admin edit screen
 */
function aiad_form_submission_meta_box(): void {
    global $post;
    
    if ( $post->post_type !== 'form_submission' ) {
        return;
    }
    
    $first_name = get_post_meta( $post->ID, '_submission_first_name', true );
    $last_name = get_post_meta( $post->ID, '_submission_last_name', true );
    $email = get_post_meta( $post->ID, '_submission_email', true );
    $involved_as = get_post_meta( $post->ID, '_submission_involved_as', true );
    $message = get_post_meta( $post->ID, '_submission_message', true );
    $school_name = get_post_meta( $post->ID, '_submission_school_name', true );
    $subject = get_post_meta( $post->ID, '_submission_subject', true );
    $child_school = get_post_meta( $post->ID, '_submission_child_school', true );
    $role_title = get_post_meta( $post->ID, '_submission_role_title', true );
    $organisation = get_post_meta( $post->ID, '_submission_organisation', true );
    $org_type = get_post_meta( $post->ID, '_submission_org_type', true );
    
    $role_labels = array(
        'teacher'       => __( 'Teacher', 'ai-awareness-day' ),
        'parent'        => __( 'Parent', 'ai-awareness-day' ),
        'school_leader' => __( 'School leader', 'ai-awareness-day' ),
        'organisation' => __( 'Organisation', 'ai-awareness-day' ),
    );
    $role_display = isset( $role_labels[ $involved_as ] ) ? $role_labels[ $involved_as ] : $involved_as;
    
    ?>
    <div class="form-submission-details" style="padding: 20px;">
        <h3><?php esc_html_e( 'Submission Details', 'ai-awareness-day' ); ?></h3>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e( 'Name', 'ai-awareness-day' ); ?></th>
                <td><?php echo esc_html( $first_name . ' ' . $last_name ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Email', 'ai-awareness-day' ); ?></th>
                <td><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Getting involved as', 'ai-awareness-day' ); ?></th>
                <td><?php echo esc_html( $role_display ); ?></td>
            </tr>
            <?php if ( $school_name ) : ?>
            <tr>
                <th><?php esc_html_e( 'School', 'ai-awareness-day' ); ?></th>
                <td><?php echo esc_html( $school_name ); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ( $subject ) : ?>
            <tr>
                <th><?php esc_html_e( 'Subject / Area', 'ai-awareness-day' ); ?></th>
                <td><?php echo esc_html( $subject ); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ( $child_school ) : ?>
            <tr>
                <th><?php esc_html_e( 'Child\'s School', 'ai-awareness-day' ); ?></th>
                <td><?php echo esc_html( $child_school ); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ( $role_title ) : ?>
            <tr>
                <th><?php esc_html_e( 'Role Title', 'ai-awareness-day' ); ?></th>
                <td><?php echo esc_html( $role_title ); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ( $organisation ) : ?>
            <tr>
                <th><?php esc_html_e( 'Organisation', 'ai-awareness-day' ); ?></th>
                <td><?php echo esc_html( $organisation ); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ( $org_type ) : ?>
            <tr>
                <th><?php esc_html_e( 'Organisation Type', 'ai-awareness-day' ); ?></th>
                <td><?php echo esc_html( $org_type ); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ( $message ) : ?>
            <tr>
                <th><?php esc_html_e( 'Message', 'ai-awareness-day' ); ?></th>
                <td><?php echo nl2br( esc_html( $message ) ); ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    <?php
}
add_action( 'edit_form_after_title', 'aiad_form_submission_meta_box' );

/**
 * Get filter counts given current tax_query constraints.
 * For each filter dimension, count how many resources match
 * the OTHER active filters plus each term in this dimension.
 *
 * @param string $post_type       Post type slug.
 * @param array  $active_tax_query Current tax_query (from active filters).
 * @return array Counts keyed by taxonomy => term_slug => count.
 */
function aiad_get_filter_counts( string $post_type, array $active_tax_query ): array {
    $version = (int) get_option( 'aiad_filter_counts_ver', 0 );
    $cache_key = 'aiad_fc_' . $post_type . '_' . $version . '_' . md5( serialize( $active_tax_query ) );
    $cached = get_transient( $cache_key );
    if ( is_array( $cached ) ) {
        return $cached;
    }

    $taxonomies = array( 'resource_type', 'resource_principle', 'resource_duration', 'activity_type' );
    $counts     = array();

    foreach ( $taxonomies as $tax ) {
        $counts[ $tax ] = array();

        // Build a tax_query WITHOUT this taxonomy (so we see what's available).
        $reduced_query = array_filter( $active_tax_query, function( $clause ) use ( $tax ) {
            return is_array( $clause ) && isset( $clause['taxonomy'] ) && $clause['taxonomy'] !== $tax;
        } );

        $terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
        if ( ! $terms || is_wp_error( $terms ) ) {
            continue;
        }

        foreach ( $terms as $term ) {
            $term_query   = $reduced_query;
            $term_query[] = array(
                'taxonomy' => $tax,
                'field'    => 'slug',
                'terms'    => $term->slug,
            );
            if ( count( $term_query ) > 1 ) {
                $term_query['relation'] = 'AND';
            }

            $count_query = new WP_Query( array(
                'post_type'               => $post_type,
                'post_status'             => 'publish',
                'posts_per_page'          => -1,
                'fields'                  => 'ids',
                'tax_query'               => $term_query,
                'no_found_rows'           => true,
                'update_post_meta_cache'  => false,
                'update_post_term_cache'  => false,
            ) );

            $counts[ $tax ][ $term->slug ] = $count_query->post_count;
        }
    }

    if ( 'resource' === $post_type && function_exists( 'aiad_key_stage_options' ) ) {
        $counts['key_stage'] = array();
        foreach ( array_keys( aiad_key_stage_options() ) as $ks ) {
            $meta_query = array(
                array(
                    'key'     => '_aiad_key_stage',
                    'value'   => $ks,
                    'compare' => 'LIKE',
                ),
            );
            $count_query = new WP_Query( array(
                'post_type'               => $post_type,
                'post_status'             => 'publish',
                'posts_per_page'          => -1,
                'fields'                  => 'ids',
                'tax_query'               => $active_tax_query,
                'meta_query'              => $meta_query,
                'no_found_rows'           => true,
                'update_post_meta_cache'  => false,
                'update_post_term_cache'  => false,
            ) );
            $counts['key_stage'][ $ks ] = $count_query->post_count;
        }
    }

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
        wp_send_json_error( 'Invalid nonce' );
    }

    $post_type = sanitize_text_field( $_POST['post_type'] ?? 'resource' );
    if ( ! in_array( $post_type, array( 'resource', 'featured_resource' ), true ) ) {
        wp_send_json_error( 'Invalid post type' );
    }

    $args = array(
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => -1,
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
 */
function aiad_track_download(): void {
    $post_id = absint( $_POST['post_id'] ?? 0 );
    if ( ! $post_id || get_post_type( $post_id ) !== 'resource' ) {
        wp_send_json_error( 'Invalid resource' );
    }

    $count = absint( get_post_meta( $post_id, '_aiad_download_count', true ) );
    $count++;
    update_post_meta( $post_id, '_aiad_download_count', $count );

    wp_send_json_success( array( 'count' => $count ) );
}
add_action( 'wp_ajax_aiad_track_download', 'aiad_track_download' );
add_action( 'wp_ajax_nopriv_aiad_track_download', 'aiad_track_download' );

/**
 * Add Downloads column to resource list table
 *
 * @param array $columns List table columns.
 * @return array
 */
function aiad_resource_admin_columns( array $columns ): array {
    $columns['downloads'] = __( 'Downloads', 'ai-awareness-day' );
    return $columns;
}
add_filter( 'manage_resource_posts_columns', 'aiad_resource_admin_columns' );

/**
 * Output Downloads column content
 *
 * @param string $column  Column name.
 * @param int    $post_id Post ID.
 */
function aiad_resource_admin_column_content( string $column, int $post_id ): void {
    if ( 'downloads' === $column ) {
        $count = absint( get_post_meta( $post_id, '_aiad_download_count', true ) );
        echo esc_html( number_format_i18n( $count ) );
    }
}
add_action( 'manage_resource_posts_custom_column', 'aiad_resource_admin_column_content', 10, 2 );

/**
 * Make Downloads column sortable
 *
 * @param array $columns Sortable columns.
 * @return array
 */
function aiad_resource_sortable_columns( array $columns ): array {
    $columns['downloads'] = 'downloads';
    return $columns;
}
add_filter( 'manage_edit-resource_sortable_columns', 'aiad_resource_sortable_columns' );

/**
 * Order by downloads in admin when requested
 *
 * @param WP_Query $query Main query.
 */
function aiad_resource_admin_order_by_downloads( WP_Query $query ): void {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( 'resource' !== ( $query->get( 'post_type' ) ?? '' ) ) {
        return;
    }
    if ( 'downloads' !== ( $query->get( 'orderby' ) ?? '' ) ) {
        return;
    }
    $query->set( 'meta_key', '_aiad_download_count' );
    $query->set( 'orderby', 'meta_value_num' );
}
add_action( 'pre_get_posts', 'aiad_resource_admin_order_by_downloads' );

/**
 * Custom Walker for Navigation
 */
class AIAD_Nav_Walker extends Walker_Nav_Menu {
    /** @param object $args Optional. Not used in this walker. */
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ): void {
        $classes = implode( ' ', $item->classes );
        $output .= '<li class="' . esc_attr( $classes ) . '">';

        $atts = array(
            'href'  => esc_url( $item->url ),
            'class' => '',
        );

        // Add CTA class to last menu item
        if ( in_array( 'menu-item-cta', $item->classes ) ) {
            $atts['class'] = 'nav-cta';
        }

        $output .= '<a';
        foreach ( $atts as $attr => $value ) {
            if ( ! empty( $value ) ) {
                $output .= ' ' . $attr . '="' . $value . '"';
            }
        }
        $output .= '>' . esc_html( $item->title ) . '</a>';
    }
}

/**
 * Fallback navigation menu (anchors must match section IDs on front page)
 */
function aiad_fallback_menu(): void {
    echo '<ul>';
    echo '<li><a href="' . esc_url( home_url( '/#campaign' ) ) . '">' . esc_html__( 'Campaign', 'ai-awareness-day' ) . '</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/#reach' ) ) . '">' . esc_html__( 'Reach', 'ai-awareness-day' ) . '</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/#aim' ) ) . '">' . esc_html__( 'Aim', 'ai-awareness-day' ) . '</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/#toolkit' ) ) . '">' . esc_html__( 'Toolkit', 'ai-awareness-day' ) . '</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/#display-board' ) ) . '">' . esc_html__( 'Display board', 'ai-awareness-day' ) . '</a></li>';
    echo '<li><a href="' . esc_url( home_url( '/#contact' ) ) . '" class="nav-cta">' . esc_html__( 'Get Involved', 'ai-awareness-day' ) . '</a></li>';
    echo '</ul>';
}
