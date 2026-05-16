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
    <meta name="description" content="<?php echo esc_attr($meta_description); ?>">
    <meta name="theme-color" content="#16a34a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <script>document.documentElement.className = document.documentElement.className.replace('no-js', 'js');</script>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <header class="site-header" id="site-header">
        <div class="container header-inner">

            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
                <?php
                $defaults = aiad_get_customizer_defaults();
                $title    = get_theme_mod( 'aiad_hero_title', $defaults['aiad_hero_title'] );

                $logo_url = aiad_get_logo_image_url( aiad_get_brand_logo_attachment_id(), 'full' );

                if ( $logo_url ) {
                    echo '<img src="' . esc_url( $logo_url ) . '" alt="" aria-hidden="true" class="site-logo__img" />';
                }
                ?>
                <span class="site-logo__text"><?php echo esc_html( $title ); ?><span class="site-logo__dot">.</span></span>
            </a>

            <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation" aria-expanded="false"
                aria-controls="main-nav">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <nav class="main-navigation" id="main-nav" role="navigation"
                aria-label="<?php esc_attr_e('Main Navigation', 'ai-awareness-day'); ?>">
                <?php
                if (has_nav_menu('primary')) {
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'container' => false,
                        'walker' => new AIAD_Nav_Walker(),
                        'fallback_cb' => 'aiad_fallback_menu',
                    ));
                } else {
                    aiad_fallback_menu();
                }
                ?>
            </nav>

        </div>
    </header>

    <?php /* Breadcrumbs hidden for now — re-enable by uncommenting the line below. */ ?>
    <?php /* if ( function_exists( 'aiad_render_breadcrumbs' ) ) { aiad_render_breadcrumbs(); } */ ?>