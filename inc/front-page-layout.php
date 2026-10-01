<?php
/**
 * Front Page Layout Helper Functions
 * Handles section visibility, ordering, and alignment from Customizer.
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
 * Get text alignment class for sections.
 *
 * @return string CSS class for text alignment.
 */
function aiad_get_text_alignment_class(): string {
    // Fixed: the Customizer setting this read had no CSS behind any of its values, so the choice did nothing.
    return 'text-align-left';
}

/**
 * Get container width class.
 *
 * @return string CSS class for container width.
 */
function aiad_get_container_width_class(): string {
    // Fixed, as the alignment is: no CSS was written for any width, so the Customizer choice did nothing.
    return 'container-width-standard';
}
