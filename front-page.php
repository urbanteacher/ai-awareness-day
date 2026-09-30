<?php
/**
 * Template Name: Front Page
 * The main homepage template for AI Awareness Day.
 *
 * @package AI_Awareness_Day
 */

get_header();

// Get container/alignment classes
$container_class = aiad_get_container_width_class();
$text_alignment_class = aiad_get_text_alignment_class();
?>

<main id="main" role="main" class="<?php echo esc_attr($container_class); ?>">

    <?php
    // A block homepage (Appearance → Block homepage) prints its section blocks, in the order set in the editor.
    $block_homepage = function_exists( 'aiad_block_homepage_page' ) ? aiad_block_homepage_page() : null;
    if ( $block_homepage ) {
        echo aiad_render_block_homepage( $block_homepage ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- each section template escapes its own output.
    } else {
        foreach ( aiad_get_front_page_sections() as $section_slug ) {
            if ( ! aiad_is_section_visible( $section_slug ) ) {
                continue;
            }
            get_template_part(
                'template-parts/front-page/section',
                $section_slug,
                array(
                    'text_alignment_class' => $text_alignment_class,
                    'container_class'      => $container_class,
                )
            );
        }
    }
    ?>

</main>

<?php get_footer(); ?>