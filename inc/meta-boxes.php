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
 * - _partner_provides_ai_resources: '1' when partner links to AI learning resources (Reach grid).
 * - _partner_ai_resources_url: Optional dedicated URL; if empty, Partner URL is used when the card is linked.
 *
 * Field definitions: partner_details in inc/field-registry.php.
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
/**
 * Render partner meta fields from the partner_details registry subset.
 *
 * @param WP_Post $post      Partner post.
 * @param string[] $meta_keys Meta keys to render (order preserved).
 */
function aiad_render_partner_section_fields( WP_Post $post, array $meta_keys ): void {
    if ( ! function_exists( 'aiad_get_section_fields' ) || ! function_exists( 'aiad_render_field' ) ) {
        return;
    }
    $fields = aiad_get_section_fields( 'partner_details' );
    echo '<div class="aiad-resource-details">';
    foreach ( $meta_keys as $meta_key ) {
        if ( ! isset( $fields[ $meta_key ] ) ) {
            continue;
        }
        $config = $fields[ $meta_key ];
        $value  = get_post_meta( $post->ID, $meta_key, true );
        if ( '_partner_school_count' === $meta_key && (int) $value === 0 ) {
            $value = '';
        }
        echo aiad_render_field( $config, $value );
    }
    echo '</div>';
}

/**
 * Save partner_details registry fields for the given meta keys.
 *
 * @param int      $post_id   Post ID.
 * @param string[] $meta_keys Keys to persist.
 */
function aiad_save_partner_details_fields( int $post_id, array $meta_keys ): void {
    if ( ! function_exists( 'aiad_get_section_fields' ) || ! function_exists( 'aiad_registry_field_post_name' ) ) {
        return;
    }
    $fields = aiad_get_section_fields( 'partner_details' );
    foreach ( $meta_keys as $meta_key ) {
        if ( ! isset( $fields[ $meta_key ] ) ) {
            continue;
        }
        $cfg       = $fields[ $meta_key ];
        $post_name = aiad_registry_field_post_name( $cfg, $meta_key );
        $type      = $cfg['type'] ?? 'text';

        if ( 'checkbox' === $type ) {
            if ( ! empty( $_POST[ $post_name ] ) ) {
                update_post_meta( $post_id, $meta_key, '1' );
            } else {
                delete_post_meta( $post_id, $meta_key );
            }
            continue;
        }

        if ( ! isset( $_POST[ $post_name ] ) ) {
            continue;
        }

        $raw = wp_unslash( $_POST[ $post_name ] );

        switch ( $type ) {
            case 'url':
                $url = esc_url_raw( $raw );
                if ( '_partner_ai_resources_url' === $meta_key && $url === '' ) {
                    delete_post_meta( $post_id, $meta_key );
                } else {
                    update_post_meta( $post_id, $meta_key, $url );
                }
                break;
            case 'number':
                update_post_meta( $post_id, $meta_key, absint( $raw ) );
                break;
            default:
                update_post_meta( $post_id, $meta_key, sanitize_text_field( $raw ) );
        }
    }
}

function aiad_partner_url_callback( WP_Post $post ): void {
    wp_nonce_field( 'aiad_partner_url_nonce', 'aiad_partner_url_nonce' );
    aiad_render_partner_section_fields(
        $post,
        array( '_partner_url', '_partner_provides_ai_resources', '_partner_ai_resources_url' )
    );
}
function aiad_save_partner_url( int $post_id ): void {
    if ( ! isset( $_POST['aiad_partner_url_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aiad_partner_url_nonce'] ) ), 'aiad_partner_url_nonce' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    aiad_save_partner_details_fields(
        $post_id,
        array( '_partner_url', '_partner_provides_ai_resources', '_partner_ai_resources_url' )
    );
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
    aiad_render_partner_section_fields(
        $post,
        array( '_partner_stats', '_partner_school_count' )
    );
}

function aiad_partner_stats_save( $post_id ): void {
    if ( ! isset( $_POST['aiad_partner_stats_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aiad_partner_stats_nonce'] ) ), 'aiad_partner_stats_save' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    aiad_save_partner_details_fields(
        $post_id,
        array( '_partner_stats', '_partner_school_count' )
    );
}
add_action( 'add_meta_boxes', 'aiad_partner_stats_meta_box' );
add_action( 'save_post_partner', 'aiad_partner_stats_save' );
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
        __( 'External resource link, theme & attribution', 'ai-awareness-day' ),
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
    echo '<div class="aiad-resource-details">';
    echo '<p><label for="featured_resource_url">' . esc_html__( 'Resource URL *', 'ai-awareness-day' ) . '</label><br>';
    echo '<input type="url" id="featured_resource_url" name="featured_resource_url" value="' . esc_attr( $url ) . '" class="widefat" required placeholder="https://..."></p>';
    echo '<p><label for="featured_resource_org_name">' . esc_html__( 'Organisation name', 'ai-awareness-day' ) . '</label><br>';
    echo '<input type="text" id="featured_resource_org_name" name="featured_resource_org_name" value="' . esc_attr( $org_name ) . '" class="widefat" placeholder="e.g. STEM Learning"></p>';
    echo '<p><label for="featured_resource_org_url">' . esc_html__( 'Organisation website (optional)', 'ai-awareness-day' ) . '</label><br>';
    echo '<input type="url" id="featured_resource_org_url" name="featured_resource_org_url" value="' . esc_attr( $org_url ) . '" class="widefat" placeholder="https://..."></p>';

    if ( function_exists( 'aiad_render_resource_principle_control' ) ) {
        aiad_render_resource_principle_control(
            $post->ID,
            array(
                'mode'        => 'select_single',
                'input_name'  => 'aiad_featured_resource_principle',
                'select_id'   => 'aiad_featured_resource_principle',
                'description' => __( 'Used for the Safe / Smart / Creative / Responsible / Future pill and filters.', 'ai-awareness-day' ),
            )
        );
    }
    if ( function_exists( 'aiad_render_activity_type_control' ) ) {
        aiad_render_activity_type_control(
            $post->ID,
            array(
                'mode'        => 'select_single',
                'input_name'  => 'aiad_featured_resource_activity',
                'select_id'   => 'aiad_featured_resource_activity',
                'description' => __( 'Used for the “Activity” filter (Game, Quiz, Creative Task, etc.).', 'ai-awareness-day' ),
            )
        );
    }
    if ( function_exists( 'aiad_render_resource_duration_control' ) ) {
        aiad_render_resource_duration_control(
            $post->ID,
            array(
                'input_name'     => 'aiad_featured_resource_duration',
                'description'    => __( 'Select all slots this resource fits.', 'ai-awareness-day' ),
                'checkbox_block' => true,
            )
        );
    }
    echo '</div>';
}
function aiad_save_featured_resource( int $post_id ): void {
    if ( ! isset( $_POST['aiad_featured_resource_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aiad_featured_resource_nonce'] ) ), 'aiad_featured_resource_nonce' ) ) {
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

    if ( isset( $_POST['aiad_featured_resource_principle'] ) ) {
        $slug      = sanitize_text_field( wp_unslash( $_POST['aiad_featured_resource_principle'] ) );
        $allowed_p = function_exists( 'aiad_get_term_slugs_for_taxonomy' ) ? aiad_get_term_slugs_for_taxonomy( 'resource_principle' ) : array();
        if ( $slug !== '' && in_array( $slug, $allowed_p, true ) ) {
            wp_set_object_terms( $post_id, array( $slug ), 'resource_principle' );
        } else {
            wp_set_object_terms( $post_id, array(), 'resource_principle' );
        }
    }

    if ( isset( $_POST['aiad_featured_resource_activity'] ) ) {
        $slug      = sanitize_text_field( wp_unslash( $_POST['aiad_featured_resource_activity'] ) );
        $allowed_a = function_exists( 'aiad_get_term_slugs_for_taxonomy' ) ? aiad_get_term_slugs_for_taxonomy( 'activity_type' ) : array();
        if ( $slug !== '' && in_array( $slug, $allowed_a, true ) ) {
            wp_set_object_terms( $post_id, array( $slug ), 'activity_type' );
        } else {
            wp_set_object_terms( $post_id, array(), 'activity_type' );
        }
    }

    $dur_slugs = array();
    if ( ! empty( $_POST['aiad_featured_resource_duration'] ) && is_array( $_POST['aiad_featured_resource_duration'] ) ) {
        $dur_term_list = get_terms(
            array(
                'taxonomy'   => 'resource_duration',
                'hide_empty' => false,
            )
        );
        $allowed = ( ! is_wp_error( $dur_term_list ) && is_array( $dur_term_list ) ) ? wp_list_pluck( $dur_term_list, 'slug' ) : array();
        $dur_slugs = array_values(
            array_intersect(
                array_map( 'sanitize_text_field', wp_unslash( $_POST['aiad_featured_resource_duration'] ) ),
                $allowed
            )
        );
    }
    wp_set_object_terms( $post_id, $dur_slugs, 'resource_duration' );
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
        // Existing five seed resources
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

        // New handpicked resources (from interactive AI games & tools)
        array(
            'title'   => 'Teachable Machine',
            'excerpt' => __( 'Train simple machine‑learning models in the browser and see how data shapes predictions — perfect for classroom demos about data → algorithm → prediction.', 'ai-awareness-day' ),
            'url'     => 'https://teachablemachine.withgoogle.com/',
            'org'     => 'Google',
            'theme'   => 'smart',
        ),
        array(
            'title'   => 'Harmony Square',
            'excerpt' => __( 'Play through a fictional social media town to learn how disinformation spreads — then spot the same tricks in the real world.', 'ai-awareness-day' ),
            'url'     => 'https://harmonysquare.game/en',
            'org'     => 'U.S. Department of State & partners',
            'theme'   => 'responsible',
        ),
        array(
            'title'   => 'Emoji Scavenger Hunt',
            'excerpt' => __( 'Use your device camera to find real‑world objects that match emojis while an AI model tries to recognise them in real time.', 'ai-awareness-day' ),
            'url'     => 'https://emojiscavengerhunt.withgoogle.com/',
            'org'     => 'Google',
            'theme'   => 'smart',
        ),
        array(
            'title'   => 'FreddieMeter',
            'excerpt' => __( 'Sing along to Queen and get a score for how closely your pitch, melody and timbre match Freddie Mercury — a fun doorway into AI audio analysis.', 'ai-awareness-day' ),
            'url'     => 'https://freddiemeter.withyoutube.com/',
            'org'     => 'YouTube & Google Creative Lab',
            'theme'   => 'creative',
        ),
        array(
            'title'   => 'Alexa Skill Blueprints',
            'excerpt' => __( 'Create simple custom Alexa skills from templates — stories, quizzes and lists — without writing code, great for “how does Alexa work?” lessons.', 'ai-awareness-day' ),
            'url'     => 'https://blueprints.amazon.com/',
            'org'     => 'Amazon',
            'theme'   => 'future',
        ),
        array(
            'title'   => 'AI Quests',
            'excerpt' => __( 'Hands-on AI quests and classroom-friendly challenges that walk students through data, models and real-world applications of AI.', 'ai-awareness-day' ),
            'url'     => 'https://research.google/ai-quests/intl/en_gb',
            'org'     => 'Google Research',
            'theme'   => 'smart',
        ),
        array(
            'title'   => 'Spot the Deepfake',
            'excerpt' => __( 'Interactive activities that explain how deepfakes work and help students practice spotting manipulated media.', 'ai-awareness-day' ),
            'url'     => 'https://www.spotdeepfakes.org/en-US',
            'org'     => 'Microsoft',
            'theme'   => 'safe',
        ),
        array(
            'title'   => 'AI Quests',
            'excerpt' => __( 'Hands-on AI quests and classroom-friendly challenges that walk students through data, models and real-world applications of AI.', 'ai-awareness-day' ),
            'url'     => 'https://research.google/ai-quests/intl/en_gb',
            'org'     => 'Google Research',
            'theme'   => 'smart',
        ),
        array(
            'title'   => 'Spot the Deepfake',
            'excerpt' => __( 'Interactive activities that explain how deepfakes work and help students practice spotting manipulated media.', 'ai-awareness-day' ),
            'url'     => 'https://www.spotdeepfakes.org/en-US',
            'org'     => 'Microsoft',
            'theme'   => 'safe',
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
 * Seed additional partner resources (interactive AI games and tools).
 * Runs once on existing sites to add newer recommended resources.
 */
function aiad_seed_additional_partner_resources(): void {
    if ( get_option( 'aiad_partner_resources_extended_seeded' ) === 'yes' ) {
        return;
    }

    $items = array(
        array(
            'title'   => 'Teachable Machine',
            'excerpt' => __( 'Train simple machine‑learning models in the browser and see how data shapes predictions — perfect for classroom demos about data → algorithm → prediction.', 'ai-awareness-day' ),
            'url'     => 'https://teachablemachine.withgoogle.com/',
            'org'     => 'Google',
            'theme'   => 'smart',
        ),
        array(
            'title'   => 'Harmony Square',
            'excerpt' => __( 'Play through a fictional social media town to learn how disinformation spreads — then spot the same tricks in the real world.', 'ai-awareness-day' ),
            'url'     => 'https://harmonysquare.game/en',
            'org'     => 'U.S. Department of State & partners',
            'theme'   => 'responsible',
        ),
        array(
            'title'   => 'Emoji Scavenger Hunt',
            'excerpt' => __( 'Use your device camera to find real‑world objects that match emojis while an AI model tries to recognise them in real time.', 'ai-awareness-day' ),
            'url'     => 'https://emojiscavengerhunt.withgoogle.com/',
            'org'     => 'Google',
            'theme'   => 'smart',
        ),
        array(
            'title'   => 'FreddieMeter',
            'excerpt' => __( 'Sing along to Queen and get a score for how closely your pitch, melody and timbre match Freddie Mercury — a fun doorway into AI audio analysis.', 'ai-awareness-day' ),
            'url'     => 'https://freddiemeter.withyoutube.com/',
            'org'     => 'YouTube & Google Creative Lab',
            'theme'   => 'creative',
        ),
        array(
            'title'   => 'Alexa Skill Blueprints',
            'excerpt' => __( 'Create simple custom Alexa skills from templates — stories, quizzes and lists — without writing code, great for “how does Alexa work?” lessons.', 'ai-awareness-day' ),
            'url'     => 'https://blueprints.amazon.com/',
            'org'     => 'Amazon',
            'theme'   => 'future',
        ),
    );

    foreach ( $items as $item ) {
        // Avoid duplicates by title.
        $existing = get_page_by_title( $item['title'], OBJECT, 'featured_resource' );
        if ( $existing ) {
            continue;
        }

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
            if ( ! empty( $item['theme'] ) ) {
                wp_set_object_terms( $post_id, array( $item['theme'] ), 'resource_principle' );
            }
        }
    }

    update_option( 'aiad_partner_resources_extended_seeded', 'yes' );
}
add_action( 'init', 'aiad_seed_additional_partner_resources', 26 );

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
            wp_set_object_terms( $post_id, array( $item['theme'] ), 'resource_principle' );
            wp_set_object_terms( $post_id, array( $duration_slug ), 'resource_duration' );
        }
    }

    update_option( 'aiad_lesson_starters_seeded', 'yes' );
}

/**
 * Remove default taxonomy meta boxes; unified Resource Details handles them.
 */
function aiad_remove_default_taxonomy_boxes(): void {
    if ( taxonomy_exists( 'resource_type' ) ) {
        remove_meta_box( 'resource_typediv', 'resource', 'side' );
        remove_meta_box( 'resource_typediv', 'featured_resource', 'side' );
    }
    remove_meta_box( 'resource_principlediv', 'resource', 'side' );
    remove_meta_box( 'resource_durationdiv', 'resource', 'side' );
    remove_meta_box( 'resource_durationdiv', 'featured_resource', 'side' );
    remove_meta_box( 'activity_typediv', 'resource', 'side' );
}
add_action( 'add_meta_boxes', 'aiad_remove_default_taxonomy_boxes', 99 );

/**
 * Unified Resource Details meta box (Theme, Session length, Activity type, Download, Key Stage)
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

    $key_stages      = (array) get_post_meta( $post->ID, '_aiad_key_stage', true );
    $download_url       = get_post_meta( $post->ID, '_aiad_download_url', true );
    $preview_video_url  = get_post_meta( $post->ID, '_aiad_preview_video_url', true );
    $filename           = $download_url ? basename( (string) wp_parse_url( $download_url, PHP_URL_PATH ) ) : '';

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

        $name = function_exists( 'aiad_registry_field_post_name' )
            ? aiad_registry_field_post_name( $config, $meta_key )
            : str_replace( '_aiad_', 'aiad_', $meta_key );
        echo aiad_render_field( $config, $value, $name );
    }

    if ( function_exists( 'aiad_render_resource_principle_control' ) ) {
        aiad_render_resource_principle_control(
            $post->ID,
            array(
                'mode'       => 'pills',
                'input_name' => 'aiad_resource_principle',
            )
        );
    }
    if ( function_exists( 'aiad_render_resource_duration_control' ) ) {
        aiad_render_resource_duration_control( $post->ID, array() );
    }
    if ( function_exists( 'aiad_render_activity_type_control' ) ) {
        aiad_render_activity_type_control(
            $post->ID,
            array(
                'mode'       => 'checkboxes',
                'input_name' => 'aiad_activity_type',
            )
        );
    }

    // Download file
    echo '<div class="aiad-rd-section"><strong class="aiad-rd-label">' . esc_html__( 'Download file', 'ai-awareness-day' ) . '</strong><div class="aiad-rd-download">';
    echo '<input type="hidden" id="aiad_download_url" name="aiad_download_url" value="' . esc_attr( $download_url ) . '">';
    echo '<button type="button" class="button" id="aiad_upload_download_btn">' . esc_html__( 'Upload / Select PDF or PPTX', 'ai-awareness-day' ) . '</button> ';
    echo '<button type="button" class="button" id="aiad_remove_download_btn" style="' . ( $download_url ? '' : 'display:none;' ) . '">' . esc_html__( 'Remove', 'ai-awareness-day' ) . '</button>';
    echo '<p id="aiad_download_filename" class="description" style="' . ( $filename ? '' : 'display:none;' ) . '">' . esc_html__( 'File:', 'ai-awareness-day' ) . ' <strong>' . esc_html( $filename ) . '</strong></p>';
    echo '</div></div>';

    echo '<div class="aiad-rd-section"><strong class="aiad-rd-label">' . esc_html__( 'Video preview (optional)', 'ai-awareness-day' ) . '</strong>';
    echo '<p class="description" style="margin:0 0 0.5rem;">' . esc_html__( 'Paste a YouTube or Vimeo link, or a direct link to an MP4/WebM file hosted on your site. When set, this appears in the preview area instead of the Microsoft Office slide viewer (PPTX).', 'ai-awareness-day' ) . '</p>';
    echo '<input type="url" name="aiad_preview_video_url" id="aiad_preview_video_url" class="widefat" value="' . esc_attr( is_string( $preview_video_url ) ? $preview_video_url : '' ) . '" placeholder="https://" autocomplete="off" />';
    echo '</div>';

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
 * POST field name for resource meta keys (registry-backed or legacy `_aiad_*`).
 *
 * @param string $meta_key e.g. `_aiad_subtitle`, `_aiad_duration`.
 * @return string e.g. `aiad_subtitle`.
 */
function aiad_resource_details_post_name( string $meta_key ): string {
    if ( function_exists( 'aiad_get_field_config' ) && function_exists( 'aiad_registry_field_post_name' ) ) {
        $cfg = aiad_get_field_config( $meta_key );
        if ( is_array( $cfg ) ) {
            return aiad_registry_field_post_name( $cfg, $meta_key );
        }
    }
    return str_replace( '_aiad_', 'aiad_', $meta_key );
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

    if ( isset( $_POST['aiad_resource_principle'] ) ) {
        $principle = sanitize_text_field( wp_unslash( $_POST['aiad_resource_principle'] ) );
        $allowed_p = function_exists( 'aiad_get_term_slugs_for_taxonomy' ) ? aiad_get_term_slugs_for_taxonomy( 'resource_principle' ) : array();
        if ( $principle !== '' && in_array( $principle, $allowed_p, true ) ) {
            wp_set_object_terms( $post_id, array( $principle ), 'resource_principle' );
        } else {
            wp_set_object_terms( $post_id, array(), 'resource_principle' );
        }
    }

    $duration_slugs = array();
    if ( ! empty( $_POST['aiad_resource_duration'] ) && is_array( $_POST['aiad_resource_duration'] ) ) {
        $allowed_d = function_exists( 'aiad_get_term_slugs_for_taxonomy' ) ? aiad_get_term_slugs_for_taxonomy( 'resource_duration' ) : array();
        $duration_slugs = array_values(
            array_intersect(
                array_map( 'sanitize_text_field', wp_unslash( $_POST['aiad_resource_duration'] ) ),
                $allowed_d
            )
        );
    }
    wp_set_object_terms( $post_id, $duration_slugs, 'resource_duration' );

    $activities = array();
    if ( ! empty( $_POST['aiad_activity_type'] ) && is_array( $_POST['aiad_activity_type'] ) ) {
        $allowed_a = function_exists( 'aiad_get_term_slugs_for_taxonomy' ) ? aiad_get_term_slugs_for_taxonomy( 'activity_type' ) : array();
        $activities = array_values(
            array_intersect(
                array_map( 'sanitize_text_field', wp_unslash( $_POST['aiad_activity_type'] ) ),
                $allowed_a
            )
        );
    }
    wp_set_object_terms( $post_id, $activities, 'activity_type' );

    if ( isset( $_POST['aiad_download_url'] ) ) {
        update_post_meta( $post_id, '_aiad_download_url', esc_url_raw( wp_unslash( $_POST['aiad_download_url'] ) ) );
    }

    if ( isset( $_POST['aiad_preview_video_url'] ) ) {
        update_post_meta( $post_id, '_aiad_preview_video_url', esc_url_raw( trim( wp_unslash( (string) $_POST['aiad_preview_video_url'] ) ) ) );
    }

    $key_stages = array();
    if ( ! empty( $_POST['aiad_key_stage'] ) && is_array( $_POST['aiad_key_stage'] ) ) {
        $allowed = array_keys( aiad_key_stage_options() );
        $key_stages = array_values( array_intersect( array_map( 'sanitize_text_field', wp_unslash( $_POST['aiad_key_stage'] ) ), $allowed ) );
    }
    update_post_meta( $post_id, '_aiad_key_stage', $key_stages );

    $pn_subtitle = aiad_resource_details_post_name( '_aiad_subtitle' );
    if ( isset( $_POST[ $pn_subtitle ] ) ) {
        update_post_meta( $post_id, '_aiad_subtitle', sanitize_text_field( wp_unslash( $_POST[ $pn_subtitle ] ) ) );
    }
    $pn_duration = aiad_resource_details_post_name( '_aiad_duration' );
    if ( isset( $_POST[ $pn_duration ] ) ) {
        update_post_meta( $post_id, '_aiad_duration', sanitize_text_field( wp_unslash( $_POST[ $pn_duration ] ) ) );
    }
    $pn_level = aiad_resource_details_post_name( '_aiad_level' );
    if ( isset( $_POST[ $pn_level ] ) ) {
        $l = sanitize_text_field( wp_unslash( $_POST[ $pn_level ] ) );
        if ( in_array( $l, array( 'beginner', 'intermediate', 'advanced' ), true ) ) {
            update_post_meta( $post_id, '_aiad_level', $l );
        }
    }
    $pn_status = aiad_resource_details_post_name( '_aiad_status' );
    if ( isset( $_POST[ $pn_status ] ) ) {
        $s = sanitize_text_field( wp_unslash( $_POST[ $pn_status ] ) );
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
    $discussion_q      = get_post_meta( $post->ID, '_aiad_discussion_question', true );
    $teacher_notes     = get_post_meta( $post->ID, '_aiad_teacher_notes', true );
    $preparation       = (array) get_post_meta( $post->ID, '_aiad_preparation', true );
    $differentiation   = (array) get_post_meta( $post->ID, '_aiad_differentiation', true );
    $extensions        = (array) get_post_meta( $post->ID, '_aiad_extensions', true );
    $resources         = (array) get_post_meta( $post->ID, '_aiad_resources', true );

    $learning_obj  = aiad_normalise_learning_objectives( $learning_obj );
    $instructions  = aiad_normalise_instructions( $instructions );
    $diff_support = isset( $differentiation['support'] ) ? $differentiation['support'] : '';
    $diff_stretch = isset( $differentiation['stretch'] ) ? $differentiation['stretch'] : '';
    $diff_send    = isset( $differentiation['send'] ) ? $differentiation['send'] : '';

    echo '<p class="description" style="margin-bottom: 1rem;">' . esc_html__( 'Structured content (Activity Schema v1). Use Add/Remove for consistent formatting.', 'ai-awareness-day' ) . '</p>';

    echo '<div class="aiad-content-sections-fields">';

    // Render fields from registry
    $fields = aiad_get_section_fields( 'content_sections' );
    foreach ( $fields as $meta_key => $config ) {
        $value = get_post_meta( $post->ID, $meta_key, true );
        $name  = function_exists( 'aiad_registry_field_post_name' )
            ? aiad_registry_field_post_name( $config, $meta_key )
            : str_replace( '_aiad_', 'aiad_', $meta_key );
        $type = $config['type'] ?? 'text';

        // Normalize values for specific fields
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
 * Enqueue shared admin common CSS on all theme CPT edit screens.
 * Provides consistent meta box styling across resource, partner,
 * featured_resource, timeline, ai_tool, and form_submission post types.
 *
 * @param string $hook Current admin page hook.
 */
function aiad_admin_common_styles( string $hook ): void {
    if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
        return;
    }
    $screen = get_current_screen();
    if ( ! $screen ) {
        return;
    }
    $theme_cpts = array( 'resource', 'partner', 'featured_resource', 'timeline', 'ai_tool', 'form_submission' );
    if ( ! in_array( $screen->post_type, $theme_cpts, true ) ) {
        return;
    }
    $css_path = AIAD_DIR . '/admin/css/aiad-admin-common.css';
    wp_enqueue_style(
        'aiad-admin-common',
        AIAD_URI . '/admin/css/aiad-admin-common.css',
        array(),
        file_exists( $css_path ) ? filemtime( $css_path ) : AIAD_VERSION
    );
}
add_action( 'admin_enqueue_scripts', 'aiad_admin_common_styles' );

/**
 * Admin notice on Resource edit screen: theme, session length, and activity are set in Resource Details.
 * Taxonomy admin screens under Resources are for managing term lists only.
 */
function aiad_resource_edit_screen_notice(): void {
    $screen = get_current_screen();
    if ( ! $screen || $screen->post_type !== 'resource' || $screen->base !== 'post' ) {
        return;
    }
    global $post;
    ?>
    <div class="notice notice-info is-dismissible" style="margin-top: 12px;">
        <p><?php esc_html_e( 'Set Theme, Session length, and Activity type in Resource Details on this page (and in the boxes on the right on desktop, or below on mobile). The Themes, Session length, and Activity type items under Resources are only for managing the list of options (for example, adding a new theme). You do not need to open those screens while editing a resource.', 'ai-awareness-day' ); ?></p>
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

