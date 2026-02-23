<?php
/**
 * Theme Customizer settings (header, hero, campaign, badges, YouTube, display board, contact, social).
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Customizer validate_callback: require a valid URL (or empty).
 *
 * @param WP_Error $validity
 * @param mixed    $value
 * @return WP_Error
 */
function aiad_customizer_validate_url( WP_Error $validity, $value ): WP_Error {
    if ( ! empty( $value ) && ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
        $validity->add( 'invalid_url', __( 'Please enter a valid URL.', 'ai-awareness-day' ) );
    }
    return $validity;
}

/**
 * Customizer validate_callback: require a valid URL or '#' placeholder (or empty).
 *
 * @param WP_Error $validity
 * @param mixed    $value
 * @return WP_Error
 */
function aiad_customizer_validate_url_or_hash( WP_Error $validity, $value ): WP_Error {
    if ( ! empty( $value ) && $value !== '#' && ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
        $validity->add( 'invalid_url', __( 'Please enter a valid URL.', 'ai-awareness-day' ) );
    }
    return $validity;
}

/**
 * Main Customizer registration function.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function aiad_customize_register( WP_Customize_Manager $wp_customize ): void {
    aiad_register_header_section( $wp_customize );
    aiad_register_hero_section( $wp_customize );
    aiad_register_campaign_section( $wp_customize );
    aiad_register_badges_section( $wp_customize );
    aiad_register_youtube_section( $wp_customize );
    aiad_register_toolkit_section( $wp_customize );
    aiad_register_time_resources_display_section( $wp_customize );
    aiad_register_display_board_section( $wp_customize );
    aiad_register_contact_section( $wp_customize );
    aiad_register_social_section( $wp_customize );
    aiad_register_front_page_layout_section( $wp_customize );
}
add_action( 'customize_register', 'aiad_customize_register' );

/**
 * Register Header section.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function aiad_register_header_section( WP_Customize_Manager $wp_customize ): void {
    $wp_customize->add_section( 'aiad_header', array(
        'title'    => __( 'Header', 'ai-awareness-day' ),
        'priority' => 29,
    ) );

    $wp_customize->add_setting( 'aiad_header_logo', array(
        'default'           => 0,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'aiad_header_logo', array(
        'label'       => __( 'Header Logo', 'ai-awareness-day' ),
        'description' => __( 'Logo image displayed in the header before the site title. If not set, the Hero Logo will be used.', 'ai-awareness-day' ),
        'section'     => 'aiad_header',
        'mime_type'   => 'image',
    ) ) );
}

/**
 * Register Hero section.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function aiad_register_hero_section( WP_Customize_Manager $wp_customize ): void {
    $defaults = aiad_get_customizer_defaults();

    $wp_customize->add_section( 'aiad_hero', array(
        'title'    => __( 'Hero Section', 'ai-awareness-day' ),
        'priority' => 30,
    ) );

    $wp_customize->add_setting( 'aiad_hero_logo', array(
        'default'           => 0,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'aiad_hero_logo', array(
        'label'       => __( 'Hero Logo', 'ai-awareness-day' ),
        'description' => __( 'Image shown above the date. Leave empty to show a placeholder.', 'ai-awareness-day' ),
        'section'     => 'aiad_hero',
        'mime_type'   => 'image',
    ) ) );

    $wp_customize->add_setting( 'aiad_hero_slogan', array(
        'default'           => $defaults['aiad_hero_slogan'],
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'aiad_hero_slogan', array(
        'label'   => __( 'Hero Slogan (under logo)', 'ai-awareness-day' ),
        'section' => 'aiad_hero',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'aiad_hero_title', array(
        'default'           => $defaults['aiad_hero_title'],
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'aiad_hero_title', array(
        'label'   => __( 'Hero Title', 'ai-awareness-day' ),
        'section' => 'aiad_hero',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'aiad_hero_date', array(
        'default'           => $defaults['aiad_hero_date'],
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'aiad_hero_date', array(
        'label'       => __( 'Event Date Text', 'ai-awareness-day' ),
        'description' => __( 'Displayed prominently in the hero section. Format: "Thursday 4th June 2026"', 'ai-awareness-day' ),
        'section'     => 'aiad_hero',
        'type'        => 'text',
    ) );

    $wp_customize->add_setting( 'aiad_hero_subtitle', array(
        'default'           => $defaults['aiad_hero_subtitle'],
        'sanitize_callback' => 'sanitize_textarea_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'aiad_hero_subtitle', array(
        'label'   => __( 'Hero Subtitle', 'ai-awareness-day' ),
        'section' => 'aiad_hero',
        'type'    => 'textarea',
    ) );
}

/**
 * Register Campaign section.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function aiad_register_campaign_section( WP_Customize_Manager $wp_customize ): void {
    $defaults = aiad_get_customizer_defaults();

    $wp_customize->add_section( 'aiad_campaign', array(
        'title'    => __( 'Campaign Section', 'ai-awareness-day' ),
        'priority' => 31,
    ) );

    $wp_customize->add_setting( 'aiad_campaign_title', array(
        'default'           => $defaults['aiad_campaign_title'],
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'aiad_campaign_title', array(
        'label'   => __( 'Campaign Title', 'ai-awareness-day' ),
        'section' => 'aiad_campaign',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'aiad_campaign_text', array(
        'default'           => $defaults['aiad_campaign_text'],
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'aiad_campaign_text', array(
        'label'   => __( 'Campaign Description', 'ai-awareness-day' ),
        'section' => 'aiad_campaign',
        'type'    => 'textarea',
    ) );

    $wp_customize->add_setting( 'aiad_campaign_text_2', array(
        'default'           => $defaults['aiad_campaign_text_2'],
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'aiad_campaign_text_2', array(
        'label'   => __( 'Campaign Paragraph 2', 'ai-awareness-day' ),
        'section' => 'aiad_campaign',
        'type'    => 'textarea',
    ) );

    $wp_customize->add_setting( 'aiad_campaign_linkedin_embed_src', array(
        'default'           => $defaults['aiad_campaign_linkedin_embed_src'],
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'aiad_campaign_linkedin_embed_src', array(
        'label'       => __( 'LinkedIn embed URL', 'ai-awareness-day' ),
        'description' => __( 'Optional. Paste the embed src URL of a LinkedIn post to show it next to the campaign text (e.g. from LinkedIn’s "Embed this post"). Leave empty for text only.', 'ai-awareness-day' ),
        'section'     => 'aiad_campaign',
        'type'        => 'url',
    ) );
}

/**
 * Register Principle & Theme Badges section.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function aiad_register_badges_section( WP_Customize_Manager $wp_customize ): void {
    $wp_customize->add_section( 'aiad_badges', array(
        'title'       => __( 'Principle & Theme Badges', 'ai-awareness-day' ),
        'description' => __( 'Upload badge images for the Five Core Principles and the By theme links. Same images are used in both sections.', 'ai-awareness-day' ),
        'priority'    => 33,
    ) );

    $badge_slugs = array( 'safe', 'smart', 'creative', 'responsible', 'future' );
    $badge_labels = array(
        'safe'        => __( 'Safe', 'ai-awareness-day' ),
        'smart'       => __( 'Smart', 'ai-awareness-day' ),
        'creative'    => __( 'Creative', 'ai-awareness-day' ),
        'responsible' => __( 'Responsible', 'ai-awareness-day' ),
        'future'      => __( 'Future', 'ai-awareness-day' ),
    );

    foreach ( $badge_slugs as $slug ) {
        $wp_customize->add_setting( 'aiad_badge_' . $slug, array(
            'default'           => 0,
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        ) );
        $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'aiad_badge_' . $slug, array(
            'label'     => isset( $badge_labels[ $slug ] ) ? $badge_labels[ $slug ] : ucfirst( $slug ),
            'section'   => 'aiad_badges',
            'mime_type' => 'image',
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
            'transport'         => 'postMessage',
        ) );
        $wp_customize->add_control( 'aiad_principle_title_' . $slug, array(
            'label'   => sprintf( __( 'Principle "%s" title', 'ai-awareness-day' ), $defaults['title'] ),
            'section' => 'aiad_badges',
            'type'    => 'text',
        ) );

        $wp_customize->add_setting( 'aiad_principle_desc_' . $slug, array(
            'default'           => $defaults['desc'],
            'sanitize_callback' => 'sanitize_textarea_field',
            'transport'         => 'postMessage',
        ) );
        $wp_customize->add_control( 'aiad_principle_desc_' . $slug, array(
            'label'   => sprintf( __( 'Principle "%s" description', 'ai-awareness-day' ), $defaults['title'] ),
            'section' => 'aiad_badges',
            'type'    => 'textarea',
        ) );
    }

    $wp_customize->add_setting( 'aiad_ai_literacy_logo', array(
        'default'           => 0,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'aiad_ai_literacy_logo', array(
        'label'       => __( 'Our AI Literacy logo', 'ai-awareness-day' ),
        'description' => __( 'Badge image for the "Our AI literacy" card (6th cell in the principles grid). Leave empty to show an "AI" placeholder.', 'ai-awareness-day' ),
        'section'     => 'aiad_badges',
        'mime_type'   => 'image',
    ) ) );
}

/**
 * Register YouTube / Video section.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function aiad_register_youtube_section( WP_Customize_Manager $wp_customize ): void {
    $defaults = aiad_get_customizer_defaults();

    $wp_customize->add_section( 'aiad_youtube', array(
        'title'    => __( 'YouTube / Video Section', 'ai-awareness-day' ),
        'priority' => 34,
    ) );

    $wp_customize->add_setting( 'aiad_youtube_url', array(
        'default'           => $defaults['aiad_youtube_url'],
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
        'validate_callback' => 'aiad_customizer_validate_url',
    ) );
    $wp_customize->add_control( 'aiad_youtube_url', array(
        'label'       => __( 'YouTube video URL', 'ai-awareness-day' ),
        'description' => __( 'Paste a YouTube link, e.g. https://www.youtube.com/watch?v=VIDEO_ID', 'ai-awareness-day' ),
        'section'     => 'aiad_youtube',
        'type'        => 'url',
    ) );

    $wp_customize->add_setting( 'aiad_youtube_title', array(
        'default'           => $defaults['aiad_youtube_title'],
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'aiad_youtube_title', array(
        'label'   => __( 'Section title', 'ai-awareness-day' ),
        'section' => 'aiad_youtube',
        'type'    => 'text',
    ) );
}

/**
 * Register Toolkit section (newsletter link for toolkit cards).
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function aiad_register_toolkit_section( WP_Customize_Manager $wp_customize ): void {
    $wp_customize->add_section( 'aiad_toolkit', array(
        'title'       => __( 'Toolkit Section', 'ai-awareness-day' ),
        'description' => __( 'Settings for the Plug-and-Play Toolkit section on the front page.', 'ai-awareness-day' ),
        'priority'    => 32,
    ) );

    $wp_customize->add_setting( 'aiad_implementation_guide_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'aiad_implementation_guide_url', array(
        'label'       => __( 'Implementation Guide URL', 'ai-awareness-day' ),
        'description' => __( 'URL for the "Implementation Guide" toolkit card (e.g. PDF or page). When set, the card becomes a clickable link that opens in a new tab.', 'ai-awareness-day' ),
        'section'     => 'aiad_toolkit',
        'type'        => 'url',
    ) );

    $wp_customize->add_setting( 'aiad_sample_letters_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'aiad_sample_letters_url', array(
        'label'       => __( 'Sample Letters & Communications URL', 'ai-awareness-day' ),
        'description' => __( 'URL for the "Sample Letters & Communications" card (e.g. SLT approval letter PDF). When set, the card is clickable. If your link expires, paste a new one here.', 'ai-awareness-day' ),
        'section'     => 'aiad_toolkit',
        'type'        => 'url',
    ) );

    $wp_customize->add_setting( 'aiad_newsletter_url', array(
        'default'           => 'https://aiawarenessday.beehiiv.com/p/ai-awareness-day-launched',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'aiad_newsletter_url', array(
        'label'       => __( 'Latest Newsletter URL', 'ai-awareness-day' ),
        'description' => __( 'URL for the "Latest Newsletter" toolkit card. When set, the card becomes a clickable link that opens in a new tab.', 'ai-awareness-day' ),
        'section'     => 'aiad_toolkit',
        'type'        => 'url',
    ) );

    $toolkit_card_labels = array(
        1 => __( 'Implementation Guide', 'ai-awareness-day' ),
        2 => __( 'Sample Letters & Communications', 'ai-awareness-day' ),
        3 => __( 'Latest Newsletter', 'ai-awareness-day' ),
    );
    foreach ( $toolkit_card_labels as $num => $label ) {
        $wp_customize->add_setting( 'aiad_toolkit_image_' . $num, array(
            'default'           => 0,
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        ) );
        $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'aiad_toolkit_image_' . $num, array(
            'label'       => sprintf( __( 'Card image: %s', 'ai-awareness-day' ), $label ),
            'description' => __( 'Optional. Upload an image to show at the top of this toolkit card. Leave empty to show a placeholder.', 'ai-awareness-day' ),
            'section'     => 'aiad_toolkit',
            'mime_type'   => 'image',
        ) ) );
    }
}

/**
 * Register Time Resources Display section (By session length badge images, mobile).
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function aiad_register_time_resources_display_section( WP_Customize_Manager $wp_customize ): void {
    $wp_customize->add_section( 'aiad_time_resources_display', array(
        'title'       => __( 'Time Resources Display', 'ai-awareness-day' ),
        'description' => __( 'Images for the "By session length" cards (5 min, 15 min, 20 min, 30 min). Shown in the badge holder on mobile. Leave empty for placeholder.', 'ai-awareness-day' ),
        'priority'    => 35,
    ) );

    $session_badge_slugs = array( '5-min-lesson-starters', '15-20-min-tutor-time', '20-min-assemblies', '30-45-min-after-school' );
    $session_badge_labels = array(
        '5-min-lesson-starters'   => __( '5 min – image', 'ai-awareness-day' ),
        '15-20-min-tutor-time'   => __( '15 min – image', 'ai-awareness-day' ),
        '20-min-assemblies'      => __( '20 min – image', 'ai-awareness-day' ),
        '30-45-min-after-school' => __( '30 min – image', 'ai-awareness-day' ),
    );
    foreach ( $session_badge_slugs as $sess_slug ) {
        $wp_customize->add_setting( 'aiad_session_badge_' . $sess_slug, array(
            'default'           => 0,
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        ) );
        $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'aiad_session_badge_' . $sess_slug, array(
            'label'     => isset( $session_badge_labels[ $sess_slug ] ) ? $session_badge_labels[ $sess_slug ] : $sess_slug,
            'section'   => 'aiad_time_resources_display',
            'mime_type' => 'image',
        ) ) );
    }
}

/**
 * Register Display board examples section.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function aiad_register_display_board_section( WP_Customize_Manager $wp_customize ): void {
    $wp_customize->add_section( 'aiad_display_board', array(
        'title'       => __( 'Display board examples', 'ai-awareness-day' ),
        'description' => __( 'Optional images shown in the Toolkit display board guide. Upload photos of example boards to inspire teachers.', 'ai-awareness-day' ),
        'priority'    => 36,
    ) );

    foreach ( array( 1, 2, 3 ) as $num ) {
        $wp_customize->add_setting( 'aiad_display_board_image_' . $num, array(
            'default'           => 0,
            'sanitize_callback' => 'absint',
            'transport'         => 'refresh',
        ) );
        $wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'aiad_display_board_image_' . $num, array(
            'label'     => sprintf( __( 'Example display board %d', 'ai-awareness-day' ), $num ),
            'section'   => 'aiad_display_board',
            'mime_type' => 'image',
        ) ) );
    }
}

/**
 * Register Contact / Get Involved section.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function aiad_register_contact_section( WP_Customize_Manager $wp_customize ): void {
    $defaults = aiad_get_customizer_defaults();

    // Define custom control class only in Customizer context (avoids fatal on front-end).
    if ( ! class_exists( 'AIAD_SMTP_Info_Control' ) ) {
        require_once AIAD_DIR . '/inc/customizer-smtp-control.php';
    }

    $wp_customize->add_section( 'aiad_contact', array(
        'title'       => __( 'Get Involved Section', 'ai-awareness-day' ),
        'description' => __( 'Configure the contact form settings. IMPORTANT: For reliable email delivery, install the WP Mail SMTP plugin and configure SMTP settings. See instructions below.', 'ai-awareness-day' ),
        'priority'    => 36,
    ) );

    $wp_customize->add_setting( 'aiad_contact_title', array(
        'default'           => $defaults['aiad_contact_title'],
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'aiad_contact_title', array(
        'label'   => __( 'Section Title', 'ai-awareness-day' ),
        'section' => 'aiad_contact',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'aiad_contact_desc', array(
        'default'           => $defaults['aiad_contact_desc'],
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'aiad_contact_desc', array(
        'label'   => __( 'Contact Description', 'ai-awareness-day' ),
        'section' => 'aiad_contact',
        'type'    => 'textarea',
    ) );

    $wp_customize->add_setting( 'aiad_contact_email', array(
        'default'           => $defaults['aiad_contact_email'],
        'sanitize_callback' => 'sanitize_email',
        'capability'        => 'manage_options',
        'transport'         => 'refresh',
        'validate_callback' => function( $validity, $value ) {
            if ( ! empty( $value ) && ! is_email( $value ) ) {
                $validity->add( 'invalid_email', __( 'Please enter a valid email address.', 'ai-awareness-day' ) );
            }
            return $validity;
        },
    ) );
    $wp_customize->add_control( 'aiad_contact_email', array(
        'label'       => __( 'Notification Email', 'ai-awareness-day' ),
        'description' => __( 'Form submissions are sent to this email address. Make sure to install WP Mail SMTP plugin for reliable email delivery.', 'ai-awareness-day' ),
        'section'     => 'aiad_contact',
        'type'        => 'email',
    ) );

    // Add informational control about SMTP plugin
    $wp_customize->add_setting( 'aiad_smtp_info', array(
        'default'           => '',
        'sanitize_callback' => '__return_empty_string',
    ) );
    $wp_customize->add_control( new AIAD_SMTP_Info_Control( $wp_customize, 'aiad_smtp_info', array(
        'section'  => 'aiad_contact',
        'priority' => 20,
    ) ) );
}

/**
 * Register Social Links section.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function aiad_register_social_section( WP_Customize_Manager $wp_customize ): void {
    $defaults = aiad_get_customizer_defaults();

    $wp_customize->add_section( 'aiad_social', array(
        'title'    => __( 'Social Links', 'ai-awareness-day' ),
        'priority' => 37,
    ) );

    $wp_customize->add_setting( 'aiad_linkedin', array(
        'default'           => $defaults['aiad_linkedin'],
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
        'validate_callback' => 'aiad_customizer_validate_url_or_hash',
    ) );
    $wp_customize->add_control( 'aiad_linkedin', array(
        'label'   => __( 'LinkedIn URL', 'ai-awareness-day' ),
        'section' => 'aiad_social',
        'type'    => 'url',
    ) );

    $wp_customize->add_setting( 'aiad_instagram', array(
        'default'           => $defaults['aiad_instagram'],
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
        'validate_callback' => 'aiad_customizer_validate_url_or_hash',
    ) );
    $wp_customize->add_control( 'aiad_instagram', array(
        'label'   => __( 'Instagram URL', 'ai-awareness-day' ),
        'section' => 'aiad_social',
        'type'    => 'url',
    ) );

    $wp_customize->add_setting( 'aiad_linkedin_post_url', array(
        'default'           => $defaults['aiad_linkedin_post_url'],
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
        'validate_callback' => 'aiad_customizer_validate_url',
    ) );
    $wp_customize->add_control( 'aiad_linkedin_post_url', array(
        'label'       => __( 'Featured LinkedIn post URL', 'ai-awareness-day' ),
        'description' => __( 'Optional. Paste the URL of a LinkedIn post to show a "Latest from LinkedIn" card on the front page. Leave empty to hide the card.', 'ai-awareness-day' ),
        'section'     => 'aiad_social',
        'type'        => 'url',
    ) );
}

/**
 * Register Front Page Layout section (visibility, ordering, alignment).
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function aiad_register_front_page_layout_section( WP_Customize_Manager $wp_customize ): void {
    $wp_customize->add_section( 'aiad_front_page_layout', array(
        'title'       => __( 'Front Page Layout', 'ai-awareness-day' ),
        'description' => __( 'Control section visibility, ordering, and alignment on the front page.', 'ai-awareness-day' ),
        'priority'    => 25,
    ) );

    // Section visibility toggles
    $sections = array(
        'hero'              => __( 'Hero Section', 'ai-awareness-day' ),
        'campaign'          => __( 'Campaign Section', 'ai-awareness-day' ),
        'timeline'          => __( 'Latest Updates / Timeline', 'ai-awareness-day' ),
        'principles'        => __( 'Principles Section', 'ai-awareness-day' ),
        'aim'               => __( 'Aim Section', 'ai-awareness-day' ),
        'toolkit'           => __( 'Toolkit Section', 'ai-awareness-day' ),
        'free_resources'    => __( 'Free Resources Section', 'ai-awareness-day' ),
        'featured_resources' => __( 'Featured Resources Section', 'ai-awareness-day' ),
        'contact'           => __( 'Get Involved Section', 'ai-awareness-day' ),
    );

    foreach ( $sections as $slug => $label ) {
        $wp_customize->add_setting( 'aiad_section_visible_' . $slug, array(
            'default'           => true,
            'sanitize_callback' => 'wp_validate_boolean',
            'transport'         => 'refresh',
        ) );
        $wp_customize->add_control( 'aiad_section_visible_' . $slug, array(
            'label'   => sprintf( __( 'Show %s', 'ai-awareness-day' ), $label ),
            'section' => 'aiad_front_page_layout',
            'type'    => 'checkbox',
        ) );
    }

    // Text alignment options
    $wp_customize->add_setting( 'aiad_text_alignment', array(
        'default'           => 'left',
        'sanitize_callback' => function( $value ) {
            return in_array( $value, array( 'left', 'center', 'right' ), true ) ? $value : 'left';
        },
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'aiad_text_alignment', array(
        'label'       => __( 'Default Text Alignment', 'ai-awareness-day' ),
        'description' => __( 'Default text alignment for section content.', 'ai-awareness-day' ),
        'section'     => 'aiad_front_page_layout',
        'type'        => 'select',
        'choices'     => array(
            'left'   => __( 'Left', 'ai-awareness-day' ),
            'center' => __( 'Center', 'ai-awareness-day' ),
            'right'  => __( 'Right', 'ai-awareness-day' ),
        ),
    ) );

    // Container width
    $wp_customize->add_setting( 'aiad_container_width', array(
        'default'           => 'standard',
        'sanitize_callback' => function( $value ) {
            return in_array( $value, array( 'narrow', 'standard', 'wide', 'full' ), true ) ? $value : 'standard';
        },
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'aiad_container_width', array(
        'label'       => __( 'Container Width', 'ai-awareness-day' ),
        'description' => __( 'Control the maximum width of content containers.', 'ai-awareness-day' ),
        'section'     => 'aiad_front_page_layout',
        'type'        => 'select',
        'choices'     => array(
            'narrow'   => __( 'Narrow (960px)', 'ai-awareness-day' ),
            'standard' => __( 'Standard (1200px)', 'ai-awareness-day' ),
            'wide'     => __( 'Wide (1400px)', 'ai-awareness-day' ),
            'full'     => __( 'Full Width', 'ai-awareness-day' ),
        ),
    ) );

    // Section ordering (stored as comma-separated list)
    $default_order = implode( ',', array_keys( $sections ) );
    $wp_customize->add_setting( 'aiad_section_order', array(
        'default'           => $default_order,
        'sanitize_callback' => function( $value ) use ( $sections ) {
            $valid_sections = array_keys( $sections );
            $order = array_map( 'trim', explode( ',', $value ) );
            $order = array_filter( $order, function( $section ) use ( $valid_sections ) {
                return in_array( $section, $valid_sections, true );
            } );
            // Ensure all sections are included
            $missing = array_diff( $valid_sections, $order );
            $order = array_merge( $order, $missing );
            return implode( ',', $order );
        },
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'aiad_section_order', array(
        'label'       => __( 'Section Order', 'ai-awareness-day' ),
        'description' => sprintf(
            /* translators: %s: Default section order */
            __( 'Comma-separated list of section slugs. Default: %s. Available sections: hero, campaign, timeline, principles, aim, toolkit, free_resources, featured_resources, contact', 'ai-awareness-day' ),
            '<code>' . esc_html( $default_order ) . '</code>'
        ),
        'section'     => 'aiad_front_page_layout',
        'type'        => 'text',
    ) );
}
