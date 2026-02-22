<?php
/**
 * Meta boxes: partner, featured resource, resource details, resource content sections; seeds; download/upload helpers.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Partner URL meta box
 * 
 * Meta field naming: Uses _partner_* prefix for partner post type fields.
 * - _partner_url: Partner website URL
 * - _partner_stats: Partner statistics/description
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
 * 
 * Meta field naming: Uses _featured_resource_* prefix for featured_resource post type fields.
 * - _featured_resource_url: External resource URL (required)
 * - _featured_resource_org_name: Organisation name (text field)
 * - _featured_resource_org_url: Organisation website URL (optional)
 * 
 * Note: Organisation name is stored as text, not as a relationship to the partner post type.
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
    // Meta field naming convention: _featured_resource_* prefix for featured_resource post type
    $url      = get_post_meta( $post->ID, '_featured_resource_url', true );
    $org_name = get_post_meta( $post->ID, '_featured_resource_org_name', true );
    $org_url  = get_post_meta( $post->ID, '_featured_resource_org_url', true );
    echo '<p><label for="featured_resource_url">' . esc_html__( 'Resource URL *', 'ai-awareness-day' ) . '</label><br>';
    echo '<input type="url" id="featured_resource_url" name="featured_resource_url" value="' . esc_attr( $url ) . '" class="widefat" required placeholder="https://..."></p>';
    echo '<p><label for="featured_resource_org_name">' . esc_html__( 'Organisation name', 'ai-awareness-day' ) . '</label><br>';
    echo '<input type="text" id="featured_resource_org_name" name="featured_resource_org_name" value="' . esc_attr( $org_name ) . '" class="widefat" placeholder="e.g. STEM Learning"></p>';
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
// Disabled: Demo content seeding
// add_action( 'init', 'aiad_seed_partners', 20 );

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
            update_post_meta( $post_id, '_featured_resource_org_name', $item['org_name'] ?? $item['org'] ?? '' );
            wp_set_object_terms( $post_id, array( $item['theme'] ), 'resource_principle' );
        }
    }

    update_option( 'aiad_partner_resources_seeded', 'yes' );
}
// Disabled: Demo content seeding
// add_action( 'init', 'aiad_seed_partner_resources', 25 );

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
 * Seed function for "Who's Really Behind the Screen?" resource.
 *
 * REMOVED: This resource is now imported via WXR file (whos-really-behind-the-screen.wxr.xml).
 * The seed function has been removed to avoid hard-coded content in production.
 *
 * To import: Use Resources → Import demo resources → Upload whos-really-behind-the-screen.wxr.xml
 */
function aiad_seed_whos_really_behind_screen_resource(): void {
    // Function body removed - resource now imported via WXR file (whos-really-behind-the-screen.wxr.xml)
    return;
}

/**
 * Seed function for "The Hidden Costs of AI" resource.
 *
 * REMOVED: This resource is now imported via WXR file (the-hidden-costs-of-ai.wxr.xml).
 * The seed function has been removed to avoid hard-coded content in production.
 *
 * To import: Use Resources → Import demo resources → Upload the-hidden-costs-of-ai.wxr.xml
 */
function aiad_seed_hidden_costs_of_ai_resource(): void {
    // Function body removed - resource now imported via WXR file (the-hidden-costs-of-ai.wxr.xml)
    return;
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
    // Meta field naming: _resource_download_url for resource post type download files
    $download_url    = get_post_meta( $post->ID, '_resource_download_url', true );
    $filename        = $download_url ? basename( (string) wp_parse_url( $download_url, PHP_URL_PATH ) ) : '';

    $theme_slugs = array( 'safe', 'smart', 'creative', 'responsible', 'future' );

    echo '<div class="aiad-resource-details">';

    // Render fields from registry (subtitle, duration, level, status)
    $fields = aiad_get_section_fields( 'resource_details' );
    foreach ( $fields as $meta_key => $config ) {
        $value = get_post_meta( $post->ID, $meta_key, true );
        
        // Handle default values
        if ( $value === '' && isset( $config['default'] ) ) {
            $value = $config['default'];
        }
        
        // Special handling for status field
        if ( $meta_key === '_aiad_status' && $value === '' ) {
            $value = get_post_status( $post->ID ) === 'publish' ? 'published' : 'draft';
        }

        $name = str_replace( '_aiad_', 'aiad_', $meta_key );
        echo aiad_render_field( $config, $value, $name );
    }

    // Format (radio) - Taxonomy field, not in registry
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
 * Enqueue scripts for resource content sections meta box
 *
 * @param string $hook Current admin page hook.
 */
function aiad_resource_content_meta_box_enqueue_scripts( string $hook ): void {
    if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
        return;
    }
    $screen = get_current_screen();
    if ( ! $screen || $screen->post_type !== 'resource' ) {
        return;
    }

    wp_enqueue_script(
        'aiad-admin-meta-boxes',
        AIAD_URI . '/assets/js/admin-meta-boxes.js',
        array( 'jquery' ),
        AIAD_VERSION,
        true
    );

    // Prepare extension options HTML
    $opts = array(
        'homework'        => __( 'Homework', 'ai-awareness-day' ),
        'next_lesson'     => __( 'Next lesson', 'ai-awareness-day' ),
        'cross_curricular' => __( 'Cross-curricular', 'ai-awareness-day' ),
        'independent'     => __( 'Independent', 'ai-awareness-day' ),
    );
    $ext_parts = array();
    foreach ( $opts as $k => $v ) {
        $ext_parts[] = '<option value="' . esc_attr( $k ) . '">' . esc_html( $v ) . '</option>';
    }

    // Prepare resource options HTML
    $r_opts = array(
        'slides'   => __( 'Slides', 'ai-awareness-day' ),
        'worksheet' => __( 'Worksheet', 'ai-awareness-day' ),
        'handout'  => __( 'Handout', 'ai-awareness-day' ),
        'video'    => __( 'Video', 'ai-awareness-day' ),
        'link'     => __( 'Link', 'ai-awareness-day' ),
        'other'    => __( 'Other', 'ai-awareness-day' ),
    );
    $res_parts = array();
    foreach ( $r_opts as $k => $v ) {
        $res_parts[] = '<option value="' . esc_attr( $k ) . '">' . esc_html( $v ) . '</option>';
    }

    wp_localize_script( 'aiad-admin-meta-boxes', 'aiadAdminMeta', array(
        'removeText'          => __( 'Remove', 'ai-awareness-day' ),
        'assessableText'      => __( 'Assessable', 'ai-awareness-day' ),
        'termText'            => __( 'Term', 'ai-awareness-day' ),
        'definitionText'     => __( 'Definition', 'ai-awareness-day' ),
        'keyStageAdaptedText' => __( 'Key stage adapted', 'ai-awareness-day' ),
        'durationText'       => __( 'Duration', 'ai-awareness-day' ),
        'actionText'          => __( 'Action', 'ai-awareness-day' ),
        'resourceRefText'     => __( 'Resource ref', 'ai-awareness-day' ),
        'studentActionText'   => __( 'Student action', 'ai-awareness-day' ),
        'teacherTipText'      => __( 'Teacher tip', 'ai-awareness-day' ),
        'removeStepText'      => __( 'Remove step', 'ai-awareness-day' ),
        'extensionOptions'    => implode( '', $ext_parts ),
        'resourceOptions'     => implode( '', $res_parts ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'aiad_resource_content_meta_box_enqueue_scripts' );

/**
 * Callback: Resource content sections meta box
 *
 * @param WP_Post $post Resource post.
 */
function aiad_resource_content_sections_callback( WP_Post $post ): void {
    wp_nonce_field( 'aiad_content_sections_nonce', 'aiad_content_sections_nonce' );

    $key_definitions    = (array) get_post_meta( $post->ID, '_aiad_key_definitions', true );
    $learning_obj      = get_post_meta( $post->ID, '_aiad_learning_objectives', true );
    $instructions      = get_post_meta( $post->ID, '_aiad_instructions', true );
    $discussion_prompts = get_post_meta( $post->ID, '_aiad_discussion_prompts', true );
    $discussion_q      = get_post_meta( $post->ID, '_aiad_discussion_question', true );
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
    $diff_support = isset( $differentiation['support'] ) ? $differentiation['support'] : '';
    $diff_stretch = isset( $differentiation['stretch'] ) ? $differentiation['stretch'] : '';
    $diff_send    = isset( $differentiation['send'] ) ? $differentiation['send'] : '';

    echo '<p class="description" style="margin-bottom: 1rem;">' . esc_html__( 'Structured content (Activity Schema v1). Use Add/Remove for consistent formatting.', 'ai-awareness-day' ) . '</p>';

    echo '<div class="aiad-content-sections-fields">';

    // Render fields from registry
    $fields = aiad_get_section_fields( 'content_sections' );
    foreach ( $fields as $meta_key => $config ) {
        $value = get_post_meta( $post->ID, $meta_key, true );
        $name = str_replace( '_aiad_', 'aiad_', $meta_key );
        $type = $config['type'] ?? 'text';

        // Normalize values for specific fields
        if ( $meta_key === '_aiad_discussion_prompts' && is_string( $value ) ) {
            $value = $value !== '' ? array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $value ) ) ) ) : array();
        }
        if ( $meta_key === '_aiad_suggested_answers' && is_string( $value ) ) {
            $value = $value !== '' ? array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', $value ) ) ) ) : array();
        }
        if ( $meta_key === '_aiad_learning_objectives' ) {
            $value = aiad_normalise_learning_objectives( $value );
        }
        if ( $meta_key === '_aiad_instructions' ) {
            $value = aiad_normalise_instructions( $value );
        }
        if ( $meta_key === '_aiad_differentiation' && ! is_array( $value ) ) {
            $value = array();
        }

        // Render based on field type
        switch ( $type ) {
            case 'repeatable_text':
                $value = is_array( $value ) ? $value : array();
                echo aiad_render_repeatable_text_field( $config, $value, $name );
                break;

            case 'repeatable_object':
                $value = is_array( $value ) ? $value : array();
                echo aiad_render_repeatable_object_field( $config, $value, $name );
                break;

            case 'object':
                $value = is_array( $value ) ? $value : array();
                echo aiad_render_object_field( $config, $value, $name );
                break;

            default:
                // Simple text/textarea fields
                $value = is_string( $value ) ? $value : '';
                echo aiad_render_field( $config, $value, $name );
                break;
        }
    }

    echo '</div>';
    // JavaScript is now externalized to assets/js/admin-meta-boxes.js
    // Enqueued via aiad_resource_content_meta_box_enqueue_scripts()
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
    wp_enqueue_script(
        'aiad-resource-download-admin',
        AIAD_URI . '/assets/js/admin-resource-download.js',
        array( 'jquery' ),
        AIAD_VERSION,
        true
    );
    wp_localize_script( 'aiad-resource-download-admin', 'aiadAdminDownload', array(
        'selectFileText' => __( 'Select or upload PDF or PPTX', 'ai-awareness-day' ),
        'useFileText'    => __( 'Use this file', 'ai-awareness-day' ),
    ) );
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

