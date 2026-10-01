<?php
/**
 * The homepage: the "Home" page's section blocks in the order they have on the page. A site that has not been
 * converted yet is converted the first time the theme loads (inc/homepage-migration.php); if that could not finish,
 * the sections print in their standard order so the homepage is never empty. Printed by the aiad/front-page block
 * (templates/front-page.html); it was front-page.php.
 *
 * @package AI_Awareness_Day
 */

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
