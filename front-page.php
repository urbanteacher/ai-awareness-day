<?php
/**
 * Template Name: Front Page
 * The main homepage template for AI Awareness Day.
 *
 * The homepage is a block page ("Home", a static front page of section blocks) edited in the block editor; its
 * sections print here in the order they have on the page. A site that has not been converted yet is converted the first
 * time the theme loads (inc/homepage-migration.php); if that could not finish, the sections print in their standard
 * order so the homepage is never empty.
 *
 * @package AI_Awareness_Day
 */

get_header();

$container_class      = aiad_get_container_width_class();
$text_alignment_class = aiad_get_text_alignment_class();
$block_homepage       = aiad_block_homepage_page();
?>

<main id="main" role="main" class="<?php echo esc_attr( $container_class ); ?>">

    <?php
    if ( $block_homepage ) {
        echo aiad_render_block_homepage( $block_homepage ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- each section template escapes its own output.
    } else {
        foreach ( array_keys( aiad_homepage_section_blocks() ) as $section_slug ) {
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
