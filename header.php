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
    <script>document.documentElement.className = document.documentElement.className.replace('no-js', 'js');</script>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <header class="site-header" id="site-header">
        <div class="container header-inner">

            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo">
                <?php
                $defaults = aiad_get_customizer_defaults();
                $logo_html = '';
                if ( has_custom_logo() ) {
                    $logo_html = get_custom_logo();
                } else {
                    $logo_id = absint( get_theme_mod( 'aiad_header_logo', 0 ) ) ?: absint( get_theme_mod( 'aiad_hero_logo', 0 ) );
                    $logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
                    $title = get_theme_mod( 'aiad_hero_title', $defaults['aiad_hero_title'] );
                    if ( $logo_url ) {
                        $logo_html = '<img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $title ) . '" class="site-logo__img" />';
                    } else {
                        $logo_html = '<span class="site-logo__text">' . esc_html( $title ) . '<span class="site-logo__dot">.</span></span>';
                    }
                }
                echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- logo is built from escaped parts
                ?>
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