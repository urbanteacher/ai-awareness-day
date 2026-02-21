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

<main id="main" role="main" class="<?php echo esc_attr( $container_class ); ?>">

<?php
foreach ( aiad_get_front_page_sections() as $section_slug ) {
    if ( ! aiad_is_section_visible( $section_slug ) ) {
        continue;
    }
    get_template_part( 'front-page/section', $section_slug );
}
?>

</main>

<?php get_footer(); ?>
