<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php bloginfo('description'); ?>">
    <script>document.documentElement.className = document.documentElement.className.replace('no-js', 'js');</script>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <header class="site-header" id="site-header">
        <div class="container header-inner">

            <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo">
                <?php if (has_custom_logo()): ?>
                    <?php the_custom_logo(); ?>
                <?php else:
                    $defaults = aiad_get_customizer_defaults();
                    $header_logo_id = absint(get_theme_mod('aiad_header_logo', 0));
                    if (!$header_logo_id) {
                        $header_logo_id = absint(get_theme_mod('aiad_hero_logo', 0));
                    }
                    $header_logo_url = $header_logo_id ? wp_get_attachment_image_url($header_logo_id, 'full') : '';
                    if ($header_logo_url): ?>
                        <img src="<?php echo esc_url($header_logo_url); ?>"
                            alt="<?php echo esc_attr(aiad_get_theme_mod_default('aiad_hero_title', $defaults['aiad_hero_title'])); ?>"
                            class="site-logo__img"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" />
                        <span class="site-logo__text" style="display: none;">
                            <?php echo esc_html(aiad_get_theme_mod_default('aiad_hero_title', $defaults['aiad_hero_title'])); ?>
                            <span class="site-logo__dot">.</span>
                        </span>
                    <?php else: ?>
                        <span class="site-logo__text">
                            <?php echo esc_html(aiad_get_theme_mod_default('aiad_hero_title', $defaults['aiad_hero_title'])); ?>
                            <span class="site-logo__dot">.</span>
                        </span>
                    <?php endif;
                endif; ?>
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