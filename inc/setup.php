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
    add_image_size('aiad_social', 1200, 630, true); // OG / social share card (1.91:1)

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

    // Site icon / favicon
    add_theme_support('site-icon');

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
function aiad_flush_rewrite_rules_on_activation(): void
{
    flush_rewrite_rules(true);
}
add_action('after_switch_theme', 'aiad_flush_rewrite_rules_on_activation');

/**
 * Flush rewrite rules once whenever AIAD_VERSION changes.
 * Fires on the first admin page load after a theme update so new CPT rewrite
 * slugs (e.g. /timeline/) are registered without requiring a manual
 * Settings → Permalinks → Save.
 */
add_action('admin_init', function (): void {
    if (get_option('aiad_rewrite_version') === AIAD_VERSION) {
        return;
    }
    flush_rewrite_rules(true);
    update_option('aiad_rewrite_version', AIAD_VERSION);
});

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
            'components/explore-sessions.css',
            'components/section-green.css',
            'components/display-board.css',
            'pages/campaign.css',
            'pages/momentum.css',
            'pages/themes.css',
            'pages/aim.css',
            'pages/toolkit.css',
            'pages/get-involved.css',
            'pages/resources-archive.css',
            'pages/single-resource.css',
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
    $main_js_path = AIAD_DIR . '/assets/js/main.js';
    wp_enqueue_script(
        'aiad-main',
        AIAD_URI . '/assets/js/main.js',
        array(),
        file_exists( $main_js_path ) ? filemtime( $main_js_path ) : AIAD_VERSION,
        $script_args
    );

    // Localize for AJAX: only output nonces where they are used to reduce payload
    $aiad_ajax = array('url' => admin_url('admin-ajax.php'));
    if (is_front_page()) {
        $aiad_ajax['nonce'] = wp_create_nonce('aiad_contact_nonce');
        $aiad_ajax['timeline_nonce'] = wp_create_nonce('aiad_timeline_nonce');
    }
    if (is_singular('timeline')) {
        $aiad_ajax['timeline_nonce'] = wp_create_nonce('aiad_timeline_nonce');
    }
    if (is_post_type_archive('resource') || is_post_type_archive('featured_resource')) {
        $aiad_ajax['filter_nonce'] = wp_create_nonce('aiad_filter_nonce');
        $aiad_ajax['track_download_nonce'] = wp_create_nonce('aiad_track_download_nonce');
    }
    if (is_singular('resource')) {
        $aiad_ajax['track_download_nonce'] = wp_create_nonce('aiad_track_download_nonce');
        $aiad_ajax['track_view_nonce']     = wp_create_nonce('aiad_track_view_nonce');
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

    // Enqueue resource sharing script on single resource pages
    if (is_singular('resource')) {
        wp_enqueue_script(
            'aiad-resource-sharing',
            AIAD_URI . '/assets/js/resource-sharing.js',
            array('aiad-main'),
            AIAD_VERSION,
            true
        );
    }

    // Enqueue tools CSS on front page and tools archive
    if ( ( is_front_page() || is_post_type_archive( 'ai_tool' ) ) && ! is_admin() ) {
        $tools_css = AIAD_DIR . '/assets/css/components/tools.css';
        wp_enqueue_style(
            'aiad-tools',
            AIAD_URI . '/assets/css/components/tools.css',
            array( 'aiad-style' ),
            file_exists( $tools_css ) ? filemtime( $tools_css ) : AIAD_VERSION
        );
    }

    // Enqueue tools filter JS on tools archive only
    if ( is_post_type_archive( 'ai_tool' ) && ! is_admin() ) {
        $tools_filter_js = AIAD_DIR . '/assets/js/tools-filter.js';
        wp_enqueue_script(
            'aiad-tools-filter',
            AIAD_URI . '/assets/js/tools-filter.js',
            array(),
            file_exists( $tools_filter_js ) ? filemtime( $tools_filter_js ) : AIAD_VERSION,
            true
        );
    }

    // Enqueue timeline assets on front page and single timeline entry pages
    if ((is_front_page() || is_singular('timeline')) && !is_admin()) {
        $timeline_css = AIAD_DIR . '/assets/css/components/timeline.css';
        $timeline_js  = AIAD_DIR . '/assets/js/timeline.js';
        wp_enqueue_style(
            'aiad-timeline',
            AIAD_URI . '/assets/css/components/timeline.css',
            array('aiad-style'),
            file_exists($timeline_css) ? filemtime($timeline_css) : AIAD_VERSION
        );
        wp_enqueue_script(
            'aiad-timeline',
            AIAD_URI . '/assets/js/timeline.js',
            array('aiad-main'),
            file_exists($timeline_js) ? filemtime($timeline_js) : AIAD_VERSION,
            true
        );
    }

    // Enqueue single timeline entry styles and share script
    if (is_singular('timeline') && !is_admin()) {
        $single_timeline_css = AIAD_DIR . '/assets/css/pages/single-timeline.css';
        wp_enqueue_style(
            'aiad-single-timeline',
            AIAD_URI . '/assets/css/pages/single-timeline.css',
            array('aiad-timeline'),
            file_exists($single_timeline_css) ? filemtime($single_timeline_css) : AIAD_VERSION
        );
        $single_timeline_js = AIAD_DIR . '/assets/js/single-timeline.js';
        wp_enqueue_script(
            'aiad-single-timeline',
            AIAD_URI . '/assets/js/single-timeline.js',
            array(),
            file_exists($single_timeline_js) ? filemtime($single_timeline_js) : AIAD_VERSION,
            true
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

        $item_title = isset($item->title) ? (string) $item->title : '';
        $item_url   = isset($item->url) ? (string) $item->url : '';

        $output .= '<li class="' . esc_attr($classes) . '">';

        // Keep nav wording/order consistent with existing menu slots:
        // Toolkit slot -> Resources (Activities), Display board slot -> AI Tools.
        if (
            strcasecmp(trim($item_title), 'Toolkit') === 0 ||
            strpos($item_url, '#toolkit') !== false
        ) {
            $item_title = __('Resources', 'ai-awareness-day');
            $item_url   = home_url('/#themes');
        }

        // Legacy "Display board" now routes to AI Tools.
        if (strcasecmp(trim($item_title), 'Display board') === 0 || strpos($item_url, '#display-board') !== false) {
            $item_title = __('AI Tools', 'ai-awareness-day');
            $item_url   = home_url('/#ai-tools');
        }

        // Resources menu item should jump to Activities section on front page.
        if (
            strcasecmp(trim($item_title), 'Resources') === 0 ||
            strpos($item_url, '/resources') !== false ||
            strpos($item_url, 'post_type=resource') !== false
        ) {
            $item_title = __('Resources', 'ai-awareness-day');
            $item_url   = home_url('/#themes');
        }

        $atts = array(
            'href' => esc_url($item_url),
            'class' => '',
        );

        // Add CTA class to last menu item
        if (in_array('menu-item-cta', $item->classes)) {
            $atts['class'] = 'nav-cta';
        }

        $output .= '<a';
        foreach ($atts as $attr => $value) {
            if ($attr === 'href' && $value === '') {
                $value = '#';
            }
            $output .= ' ' . $attr . '="' . esc_attr($value) . '"';
        }
        $output .= '>' . esc_html($item_title) . '</a>';
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
    echo '<li><a href="' . esc_url(home_url('/#themes')) . '">' . esc_html__('Resources', 'ai-awareness-day') . '</a></li>';
    echo '<li><a href="' . esc_url(home_url('/#ai-tools')) . '">' . esc_html__('AI Tools', 'ai-awareness-day') . '</a></li>';
    echo '<li><a href="' . esc_url(home_url('/#contact')) . '" class="nav-cta">' . esc_html__('Get Involved', 'ai-awareness-day') . '</a></li>';
    echo '</ul>';
}
