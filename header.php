<!DOCTYPE html>
<html <?php language_attributes(); // Adds class="no-js" (aiad_html_no_js_class()). ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); // Description, theme colour and the no-js script come first (aiad_site_head_tags()). ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <?php block_template_part( 'header' ); // parts/header.html, edited in Appearance > Editor. ?>

    <?php if ( get_theme_mod( 'aiad_show_breadcrumbs', false ) && function_exists( 'aiad_render_breadcrumbs' ) ) : ?>
        <?php aiad_render_breadcrumbs(); ?>
    <?php endif; ?>
