<!DOCTYPE html>
<html <?php language_attributes(); // Adds class="no-js" (aiad_html_no_js_class()). ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); // Description, theme colour and the no-js script come first (aiad_site_head_tags()). ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <?php /* Block templates get core's skip link; the PHP templates that print this header get the same one. */ ?>
    <a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'ai-awareness-day' ); ?></a>

    <?php block_template_part( 'header' ); // parts/header.html, edited in Appearance > Editor. ?>

    <?php if ( aiad_site_value( 'show_breadcrumbs', false ) && function_exists( 'aiad_render_breadcrumbs' ) ) : ?>
        <?php aiad_render_breadcrumbs(); ?>
    <?php endif; ?>
