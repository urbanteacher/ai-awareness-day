<?php
/**
 * Title: Hero Section
 * Slug: aiad/hero
 * Categories: featured
 *
 * This pattern uses PHP at render time (Customizer values). The block comments are for
 * editor recognition when inserted as a pattern; for full Site Editor support it would
 * need to be a dynamic block or static markup. Slogan and subtitle are siblings of the
 * logo container for correct semantics and layout.
 */
$defaults = aiad_get_customizer_defaults();
?>
<!-- wp:group {"tagName":"section","metadata":{"name":"Hero"},"className":"hero-section","layout":{"type":"constrained"},"id":"hero"} -->
<section class="wp-block-group hero-section" id="hero">
    <!-- wp:group {"className":"container","layout":{"type":"constrained"}} -->
    <div class="wp-block-group container">
        <!-- wp:group {"className":"hero-title-block fade-up","layout":{"type":"constrained"}} -->
        <div class="wp-block-group hero-title-block fade-up">
            <!-- wp:paragraph {"className":"hero-date"} -->
            <p class="hero-date">
                <?php echo esc_html( get_theme_mod( 'aiad_hero_date', $defaults['aiad_hero_date'] ) ); ?>
            </p>
            <!-- /wp:paragraph -->

            <div class="hero-logo">
                <?php
                $hero_logo_id  = absint( get_theme_mod( 'aiad_hero_logo', 0 ) );
                $hero_logo_url = $hero_logo_id ? wp_get_attachment_image_url( $hero_logo_id, 'full' ) : '';
                if ( $hero_logo_url ) :
                    ?><img src="<?php echo esc_url( $hero_logo_url ); ?>"
                        alt="<?php echo esc_attr( get_theme_mod( 'aiad_hero_title', $defaults['aiad_hero_title'] ) ); ?>"
                        class="hero-logo__img" />
                    <?php
                else :
                    ?><span class="hero-logo__placeholder" aria-hidden="true">
                        <?php esc_html_e( 'Logo', 'ai-awareness-day' ); ?>
                    </span>
                    <?php
                endif;
                ?>
            </div>
            <p class="hero-slogan">
                <?php echo esc_html( get_theme_mod( 'aiad_hero_slogan', $defaults['aiad_hero_slogan'] ) ); ?>
            </p>
            <p class="section-desc">
                <?php echo esc_html( get_theme_mod( 'aiad_hero_subtitle', $defaults['aiad_hero_subtitle'] ) ); ?>
            </p>
        </div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:group -->
</section>
<!-- /wp:group -->