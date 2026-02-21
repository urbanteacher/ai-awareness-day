<?php
/**
 * Theme setup: supports, menus, image sizes, scripts, navigation.
 *
 * @package AI_Awareness_Day
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme Setup
 */
function aiad_setup(): void
{
    load_theme_textdomain('ai-awareness-day', AIAD_DIR . '/languages');

    add_theme_support('automatic-feed-links');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');

    // Custom image sizes
    add_image_size('hero-large', 1200, 675, true);
    add_image_size('hero-small', 600, 450, true);
    add_image_size('theme-thumb', 400, 400, true);

    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Navigation', 'ai-awareness-day'),
    ));

    // HTML5 support
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));

    // Custom logo
    add_theme_support('custom-logo', array(
        'height' => 60,
        'width' => 200,
        'flex-height' => true,
        'flex-width' => true,
    ));

    // Block editor: alignments, responsive embeds, block styles (WP 5.9+)
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('wp-block-styles');
    add_theme_support('editor-styles');
    add_editor_style('assets/css/editor-style.css');
    add_theme_support('custom-units');

    // 6.5: Appearance tools for classic themes (margin, padding, border, line-height, etc.)
    add_theme_support('appearance-tools');
}
add_action('after_setup_theme', 'aiad_setup');

/**
 * Flush rewrite rules on theme activation so /resources/ and /partners/ work without a manual Permalinks save.
 */
function aiad_flush_rewrite_rules_on_activation(): void {
    flush_rewrite_rules( false );
}
add_action( 'after_switch_theme', 'aiad_flush_rewrite_rules_on_activation' );

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
 * Enqueue Styles & Scripts
 */
function aiad_scripts(): void
{
    // Font fallbacks for non-block contexts
    wp_enqueue_style(
        'aiad-fonts-fallback',
        AIAD_URI . '/assets/css/base/fonts.css',
        array(),
        AIAD_VERSION
    );

    // Main stylesheet (WordPress Theme Header - must load first for Safari compatibility)
    // Use filemtime for better Safari cache busting
    $style_version = file_exists(get_stylesheet_directory() . '/style.css')
        ? filemtime(get_stylesheet_directory() . '/style.css')
        : AIAD_VERSION;
    wp_enqueue_style(
        'aiad-style',
        get_stylesheet_uri(),
        array(),
        $style_version,
        'all' // Explicit media type for Android Chrome compatibility
    );

    // CSS: use 3 bundles if present (run build script or build step to generate), else enqueue modules
    $bundles_dir = AIAD_DIR . '/assets/css/bundles';
    $use_bundles = file_exists($bundles_dir . '/base.css');
    if ($use_bundles) {
        foreach (array('base', 'layout', 'pages') as $bundle) {
            $path = $bundles_dir . '/' . $bundle . '.css';
            if (file_exists($path)) {
                $ver = filemtime($path) ?: AIAD_VERSION;
                wp_enqueue_style(
                    'aiad-bundle-' . $bundle,
                    AIAD_URI . '/assets/css/bundles/' . $bundle . '.css',
                    array('aiad-style'),
                    $ver,
                    'all'
                );
            }
        }
    } else {
        $css_files = array(
            'base/reset.css',
            'base/shared.css',
            'base/animations.css',
            'base/wp-core.css',
            'layout/navigation.css',
            'layout/hero.css',
            'layout/footer.css',
            'components/principles.css',
            'pages/campaign.css',
            'pages/momentum.css',
            'pages/themes.css',
            'pages/aim.css',
            'pages/toolkit.css',
        'pages/get-involved.css',
        'pages/resources-archive.css',
        'pages/partners-archive.css',
        'responsive/responsive.css',
            'responsive/mobile.css',
        );
        foreach ($css_files as $file) {
            $handle = 'aiad-' . str_replace(array('/', '.css'), array('-', ''), $file);
            $file_path = AIAD_DIR . '/assets/css/' . $file;
            $file_version = file_exists($file_path) ? filemtime($file_path) : AIAD_VERSION;
            wp_enqueue_style(
                $handle,
                AIAD_URI . '/assets/css/' . $file,
                array('aiad-style'),
                $file_version,
                'all'
            );
        }
    }

    // Main script (defer on WordPress 6.3+ for better performance)
    $script_args = version_compare(get_bloginfo('version'), '6.3', '>=')
        ? array('in_footer' => true, 'strategy' => 'defer')
        : true;
    wp_enqueue_script(
        'aiad-main',
        AIAD_URI . '/assets/js/main.js',
        array(),
        AIAD_VERSION,
        $script_args
    );

    // Localize for AJAX: only output nonces where they are used to reduce payload
    $aiad_ajax = array('url' => admin_url('admin-ajax.php'));
    if (is_front_page()) {
        $aiad_ajax['nonce'] = wp_create_nonce('aiad_contact_nonce');
    }
    if (is_post_type_archive('resource') || is_post_type_archive('featured_resource')) {
        $aiad_ajax['filter_nonce'] = wp_create_nonce('aiad_filter_nonce');
        $aiad_ajax['track_download_nonce'] = wp_create_nonce('aiad_track_download_nonce');
    }
    wp_localize_script('aiad-main', 'aiad_ajax', $aiad_ajax);

    // Register Interactivity API module for Partners
    if (function_exists('wp_enqueue_script_module')) {
        wp_enqueue_script_module(
            'aiad-partners',
            AIAD_URI . '/assets/js/partners.js',
            array('@wordpress/interactivity'),
            AIAD_VERSION
        );
    }

    if (is_post_type_archive('resource') || is_post_type_archive('featured_resource')) {
        wp_enqueue_script(
            'aiad-resource-filters',
            AIAD_URI . '/assets/js/resource-filters.js',
            array('aiad-main'),
            AIAD_VERSION,
            $script_args
        );
    }
}
add_action('wp_enqueue_scripts', 'aiad_scripts');

/**
 * Custom Walker for Navigation
 */
class AIAD_Nav_Walker extends Walker_Nav_Menu
{
    /** @param object $args Optional. Not used in this walker. */
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0): void
    {
        $classes = implode(' ', $item->classes);
        $output .= '<li class="' . esc_attr($classes) . '">';

        $atts = array(
            'href' => esc_url($item->url),
            'class' => '',
        );

        // Add CTA class to last menu item
        if (in_array('menu-item-cta', $item->classes)) {
            $atts['class'] = 'nav-cta';
        }

        $output .= '<a';
        foreach ($atts as $attr => $value) {
            if ( $attr === 'href' && $value === '' ) {
                $value = '#';
            }
            $output .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
        }
        $output .= '>' . esc_html($item->title) . '</a>';
    }
}

/**
 * Fallback navigation menu (anchors must match section IDs on front page)
 */
function aiad_fallback_menu(): void
{
    echo '<ul>';
    echo '<li><a href="' . esc_url(home_url('/#campaign')) . '">' . esc_html__('Campaign', 'ai-awareness-day') . '</a></li>';
    echo '<li><a href="' . esc_url(home_url('/#reach')) . '">' . esc_html__('Reach', 'ai-awareness-day') . '</a></li>';
    echo '<li><a href="' . esc_url(home_url('/#aim')) . '">' . esc_html__('Aim', 'ai-awareness-day') . '</a></li>';
    echo '<li><a href="' . esc_url(home_url('/#toolkit')) . '">' . esc_html__('Toolkit', 'ai-awareness-day') . '</a></li>';
    echo '<li><a href="' . esc_url(home_url('/#display-board')) . '">' . esc_html__('Display board', 'ai-awareness-day') . '</a></li>';
    echo '<li><a href="' . esc_url(home_url('/#contact')) . '" class="nav-cta">' . esc_html__('Get Involved', 'ai-awareness-day') . '</a></li>';
    echo '</ul>';
}
