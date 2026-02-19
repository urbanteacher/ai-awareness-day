<?php
/**
 * 404 page template.
 *
 * @package AI_Awareness_Day
 */

get_header();
?>

<main id="main" role="main" style="padding-top: 140px; padding-bottom: 100px; text-align: center; min-height: 60vh;">
    <div class="container">
        <span class="section-label"><?php esc_html_e( '404', 'ai-awareness-day' ); ?></span>
        <h1 class="section-title"><?php esc_html_e( 'Page Not Found', 'ai-awareness-day' ); ?></h1>
        <p class="section-desc" style="margin: 1rem auto 2rem;">
            <?php esc_html_e( 'The page you\'re looking for doesn\'t exist or has been moved.', 'ai-awareness-day' ); ?>
        </p>
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-submit" style="display:inline-flex; width:auto;">
            <?php esc_html_e( 'Back to Home', 'ai-awareness-day' ); ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
        </a>
    </div>
</main>

<?php get_footer(); ?>
