<?php
/**
 * Theme Customizer settings (hero, campaign, badges, YouTube, display board, contact). The site-wide settings (footer links,
 * breadcrumbs, downloads, social links) moved to Settings → AI Awareness Day (plugins/aiad-core/modules/site-settings.php).
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
 * Main Customizer registration function.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function aiad_customize_register( WP_Customize_Manager $wp_customize ): void {
    // Register panels first so sections can be retrofitted into them.
    $wp_customize->add_panel( 'aiad_panel_brand', array(
        'title'       => __( 'Brand & Identity', 'ai-awareness-day' ),
        'description' => __( 'The principle and theme badges.', 'ai-awareness-day' ),
        'priority'    => 25,
    ) );
    $wp_customize->add_panel( 'aiad_panel_front_page', array(
        'title'       => __( 'Front Page Sections', 'ai-awareness-day' ),
        'description' => __( 'Configure each homepage section: hero, campaign, video, themes, display board, get involved, and related layout settings.', 'ai-awareness-day' ),
        'priority'    => 30,
    ) );

    aiad_register_hero_section( $wp_customize );
    aiad_register_campaign_section( $wp_customize );
    aiad_register_badges_section( $wp_customize );
    aiad_register_featured_linkedin_section( $wp_customize );
    aiad_register_contact_section( $wp_customize );
    aiad_register_front_page_layout_section( $wp_customize );

    // Retrofit panel assignments so we don't have to edit each section's
    // registration. Sections not listed here remain at the top level.
    $assignments = array(
        // Brand & Identity
        'aiad_badges'                 => 'aiad_panel_brand',
        // Front Page Sections
        'aiad_front_page_layout'      => 'aiad_panel_front_page',
        'aiad_hero'                   => 'aiad_panel_front_page',
        'aiad_campaign'               => 'aiad_panel_front_page',
        'aiad_featured_linkedin'      => 'aiad_panel_front_page',
        'aiad_contact'                => 'aiad_panel_front_page',
    );
    foreach ( $assignments as $section_id => $panel_id ) {
        $section = $wp_customize->get_section( $section_id );
        if ( $section ) {
            $section->panel = $panel_id;
        }
    }
}
add_action( 'customize_register', 'aiad_customize_register' );

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

    // The one switch for the soft launch: back to the previous homepage hero without uploading an older theme.
    $wp_customize->add_setting( 'aiad_homepage_hero', array(
        'default'           => 'new',
        'sanitize_callback' => static function ( $value ) {
            return in_array( $value, array( 'new', 'previous' ), true ) ? $value : 'new';
        },
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'aiad_homepage_hero', array(
        'label'       => __( 'Homepage hero', 'ai-awareness-day' ),
        'description' => __( 'Which version of the top of the homepage visitors see. Switch back at any time; nothing else on the site changes.', 'ai-awareness-day' ),
        'section'     => 'aiad_hero',
        'type'        => 'radio',
        'priority'    => 1,
        'choices'     => array(
            'new'      => __( 'New: the 2027 National AI Conversation', 'ai-awareness-day' ),
            'previous' => __( 'Previous: "Keep Humans in the Loop" with Get involved and Check your AI readiness', 'ai-awareness-day' ),
        ),
    ) );

    // The 2027 hero's words, shown while the new hero is on. Edit Homepage shares the same fields (aiad_hero27_fields()).
    $is_new_hero      = static function () {
        return ! aiad_homepage_hero_is_previous();
    };
    $is_previous_hero = static function () {
        return aiad_homepage_hero_is_previous();
    };
    $priority = 2;
    foreach ( aiad_hero27_fields() as $key => $field ) {
        $wp_customize->add_setting( $key, array(
            'default'           => $field['default'],
            'sanitize_callback' => $field['sanitize'],
            'transport'         => 'refresh',
        ) );
        $wp_customize->add_control( $key, array(
            'label'           => $field['label'],
            'description'     => $field['description'] ?? '',
            'section'         => 'aiad_hero',
            'type'            => $field['type'],
            'priority'        => $priority++,
            'active_callback' => $is_new_hero,
        ) );
    }

    $wp_customize->add_setting( 'aiad_hero_slogan', array(
        'default'           => $defaults['aiad_hero_slogan'],
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'aiad_hero_slogan', array(
        'label'           => __( 'Previous hero: slogan under the logo', 'ai-awareness-day' ),
        'section'         => 'aiad_hero',
        'type'            => 'text',
        'priority'        => 51,
        'active_callback' => $is_previous_hero,
    ) );

    $wp_customize->add_setting( 'aiad_hero_title', array(
        'default'           => $defaults['aiad_hero_title'],
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'aiad_hero_title', array(
        'label'       => __( 'Site name', 'ai-awareness-day' ),
        'description' => __( 'Used in the footer, search results and link previews, and as the previous hero\'s title.', 'ai-awareness-day' ),
        'section'     => 'aiad_hero',
        'type'        => 'text',
        'priority'    => 60,
    ) );

    $wp_customize->add_setting( 'aiad_hero_date', array(
        'default'           => $defaults['aiad_hero_date'],
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'aiad_hero_date', array(
        'label'       => __( 'Event date text', 'ai-awareness-day' ),
        'description' => __( 'Used in link previews and by the previous hero. The new hero shows the date from the Event Date below.', 'ai-awareness-day' ),
        'section'     => 'aiad_hero',
        'type'        => 'text',
        'priority'    => 61,
    ) );

    $wp_customize->add_setting( 'aiad_hero_subtitle', array(
        'default'           => $defaults['aiad_hero_subtitle'],
        'sanitize_callback' => 'sanitize_textarea_field',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'aiad_hero_subtitle', array(
        'label'       => __( 'Site description', 'ai-awareness-day' ),
        'description' => __( 'Used in link previews and as the previous hero\'s subtitle. The new hero\'s intro is edited above.', 'ai-awareness-day' ),
        'section'     => 'aiad_hero',
        'type'        => 'textarea',
        'priority'    => 63,
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
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'aiad_campaign_title', array(
        'label'   => __( 'Campaign Title', 'ai-awareness-day' ),
        'section' => 'aiad_campaign',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'aiad_campaign_text', array(
        'default'           => $defaults['aiad_campaign_text'],
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'aiad_campaign_text', array(
        'label'   => __( 'Campaign Description', 'ai-awareness-day' ),
        'section' => 'aiad_campaign',
        'type'    => 'textarea',
    ) );

    $wp_customize->add_setting( 'aiad_campaign_text_2', array(
        'default'           => $defaults['aiad_campaign_text_2'],
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'refresh',
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
        'title'       => __( 'Principles wording', 'ai-awareness-day' ),
        'description' => __( 'The title and description of each of the Five Core Principles. The badge images are on Settings → AI Awareness Day.', 'ai-awareness-day' ),
        'priority'    => 33,
    ) );

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
            'transport'         => 'refresh',
        ) );
        $wp_customize->add_control( 'aiad_principle_title_' . $slug, array(
            'label'   => sprintf( __( 'Principle "%s" title', 'ai-awareness-day' ), $defaults['title'] ),
            'section' => 'aiad_badges',
            'type'    => 'text',
        ) );

        $wp_customize->add_setting( 'aiad_principle_desc_' . $slug, array(
            'default'           => $defaults['desc'],
            'sanitize_callback' => 'sanitize_textarea_field',
            'transport'         => 'refresh',
        ) );
        $wp_customize->add_control( 'aiad_principle_desc_' . $slug, array(
            'label'   => sprintf( __( 'Principle "%s" description', 'ai-awareness-day' ), $defaults['title'] ),
            'section' => 'aiad_badges',
            'type'    => 'textarea',
        ) );
    }
}

/**
 * Register the featured LinkedIn post (homepage featured-resources card).
 *
 * It was registered inside the Social Links section until those moved to Settings → AI Awareness Day; it is homepage
 * content, so it stays with the other front page sections.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function aiad_register_featured_linkedin_section( WP_Customize_Manager $wp_customize ): void {
    $defaults = aiad_get_customizer_defaults();

    $wp_customize->add_section( 'aiad_featured_linkedin', array(
        'title'    => __( 'Featured LinkedIn post', 'ai-awareness-day' ),
        'priority' => 36,
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
        'section'     => 'aiad_featured_linkedin',
        'type'        => 'url',
    ) );
}

/**
 * Register Contact / Get Involved section.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function aiad_register_contact_section( WP_Customize_Manager $wp_customize ): void {
    $defaults = aiad_get_customizer_defaults();

    $wp_customize->add_section( 'aiad_contact', array(
        'title'       => __( 'Get Involved Section', 'ai-awareness-day' ),
        'description' => __( 'The wording above the contact form. The address that receives submissions, and the note on email delivery, are on Settings → AI Awareness Day.', 'ai-awareness-day' ),
        'priority'    => 37,
    ) );

    $wp_customize->add_setting( 'aiad_contact_title', array(
        'default'           => $defaults['aiad_contact_title'],
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'aiad_contact_title', array(
        'label'   => __( 'Section Title', 'ai-awareness-day' ),
        'section' => 'aiad_contact',
        'type'    => 'text',
    ) );

    $wp_customize->add_setting( 'aiad_contact_desc', array(
        'default'           => $defaults['aiad_contact_desc'],
        'sanitize_callback' => 'wp_kses_post',
        'transport'         => 'refresh',
    ) );
    $wp_customize->add_control( 'aiad_contact_desc', array(
        'label'   => __( 'Contact Description', 'ai-awareness-day' ),
        'section' => 'aiad_contact',
        'type'    => 'textarea',
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
        'description' => __( 'Control section visibility and ordering on the front page.', 'ai-awareness-day' ),
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
