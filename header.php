<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $og_data = function_exists('aiad_get_og_data') ? aiad_get_og_data() : null;
    $meta_description = $og_data && isset($og_data['description']) 
        ? $og_data['description'] 
        : get_bloginfo('description');
    ?>
    <?php if ( ! function_exists( 'aiad_seo_should_output' ) || aiad_seo_should_output() ) : ?>
    <meta name="description" content="<?php echo esc_attr( $meta_description ); ?>">
    <?php endif; ?>
    <meta name="theme-color" content="#00BEDD">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <script>document.documentElement.className = document.documentElement.className.replace('no-js', 'js');</script>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <?php block_template_part( 'header' ); // parts/header.html, edited in Appearance > Editor. ?>

    <?php if ( get_theme_mod( 'aiad_show_breadcrumbs', false ) && function_exists( 'aiad_render_breadcrumbs' ) ) : ?>
        <?php aiad_render_breadcrumbs(); ?>
    <?php endif; ?>
