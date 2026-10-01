<?php
/**
 * Homepage conversion and the stored classic layout.
 * The section order and visibility the classic homepage kept in theme mods are read here only to build the block homepage.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get ordered list of front page sections.
 *
 * @return array<string> Ordered array of section slugs.
 */
function aiad_get_front_page_sections(): array {
    $sections = array(
        'hero',
        'campaign',
        'schedule',
        'timeline',
        'principles',
        'aim',
        'toolkit',
        'free_resources',
        'featured_resources',
        'tools',
        'contact',
    );

    $order = get_theme_mod( 'aiad_section_order', '' );
    if ( ! empty( $order ) ) {
        $custom_order = array_map( 'trim', explode( ',', $order ) );
        $custom_order = array_filter( $custom_order, function( $section ) use ( $sections ) {
            return in_array( $section, $sections, true );
        } );
        // Merge any missing sections at the end
        $missing = array_diff( $sections, $custom_order );
        $custom_order = array_merge( $custom_order, $missing );
        return $custom_order;
    }

    return $sections;
}

/**
 * Check if a section should be visible.
 *
 * @param string $section_slug Section slug.
 * @return bool True if section should be visible.
 */
function aiad_is_section_visible( string $section_slug ): bool {
    return (bool) get_theme_mod( 'aiad_section_visible_' . $section_slug, true );
}

/**
 * Text alignment class for sections. Fixed: the Customizer setting this read had no CSS behind any of its values.
 *
 * @return string CSS class for text alignment.
 */
function aiad_get_text_alignment_class(): string {
    return 'text-align-left';
}

/**
 * Container width class. Fixed, as the alignment is: no CSS was written for any width.
 *
 * @return string CSS class for container width.
 */
function aiad_get_container_width_class(): string {
    return 'container-width-standard';
}

/**
 * One-time conversion of the classic homepage into the block homepage.
 *
 * The homepage used to be the classic section loop, with its order, visibility and wording set in the Customizer.
 * It is a block page now (a "Home" page of section blocks, edited in the block editor), and the Customizer's homepage
 * controls and the classic loop are gone. A site that still has the classic homepage is converted the first time it
 * loads this code, with the steps the Appearance > Block homepage screen used to run by hand and that were rehearsed
 * from a live-like state: build the page from the stored order and visibility (hidden sections left out), copy the
 * stored wording into the blocks, make each section editable on the page, publish it as the front page. The Customizer
 * values are only read, never changed, so nothing is lost; the previous hero, retired, becomes the 2027 hero.
 *
 * It runs once (aiad_homepage_converted holds when and how), takes a lock so two requests cannot both run it, and if
 * it cannot finish it leaves the site on front-page.php's plain section list and records why.
 */
function aiad_maybe_convert_homepage(): void {
    if ( get_option( 'aiad_homepage_converted' ) || wp_installing() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        return;
    }
    if ( aiad_block_homepage_page() ) {
        update_option( 'aiad_homepage_converted', 'already a block homepage', false );
        return;
    }
    // A lock: add_option() fails if it exists. A lock older than ten minutes is from a request that died.
    $locked = get_option( 'aiad_homepage_converting' );
    if ( $locked && ( time() - (int) $locked ) < 10 * MINUTE_IN_SECONDS ) {
        return;
    }
    delete_option( 'aiad_homepage_converting' );
    if ( ! add_option( 'aiad_homepage_converting', time(), '', false ) ) {
        return;
    }

    // The previous hero is retired: a site that had chosen it gets the 2027 hero.
    if ( 'previous' === get_theme_mod( 'aiad_homepage_hero', 'new' ) ) {
        set_theme_mod( 'aiad_homepage_hero', 'new' );
        update_option( 'aiad_homepage_hero_retired', gmdate( 'c' ), false );
    }

    $id = aiad_create_block_homepage();
    if ( is_wp_error( $id ) ) {
        update_option( 'aiad_homepage_conversion_error', $id->get_error_message(), false );
        delete_option( 'aiad_homepage_converting' );
        return;
    }
    $page = get_post( (int) $id );
    if ( $page ) {
        aiad_rebuild_homepage_sections( $page );
    }
    $published = aiad_publish_block_homepage();
    if ( is_wp_error( $published ) ) {
        update_option( 'aiad_homepage_conversion_error', $published->get_error_message(), false );
        delete_option( 'aiad_homepage_converting' );
        return;
    }
    delete_option( 'aiad_homepage_conversion_error' );
    update_option( 'aiad_homepage_converted', gmdate( 'c' ), false );
    delete_option( 'aiad_homepage_converting' );
}
// After the section blocks, the patterns and the options are registered.
add_action( 'init', 'aiad_maybe_convert_homepage', 30 );
