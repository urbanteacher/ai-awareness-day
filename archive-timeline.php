<?php
/**
 * Archive template: Campaign updates (timeline CPT).
 *
 * @package AI_Awareness_Day
 */

get_header();
?>
<main id="main" role="main" class="container-width-standard timeline-archive">
    <section class="section timeline-archive__root">
        <div class="container">
            <span class="section-label section-label--live"><?php esc_html_e( 'Live', 'ai-awareness-day' ); ?></span>
            <h1 class="section-title timeline-archive__title"><?php esc_html_e( 'Campaign updates', 'ai-awareness-day' ); ?></h1>
            <p class="section-desc"><?php esc_html_e( 'News, partners, milestones and stories from AI Awareness Day.', 'ai-awareness-day' ); ?></p>

<?php get_template_part( 'template-parts/components/timeline-feed' ); ?>

            <p class="timeline-archive__back">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>#timeline"><?php esc_html_e( '← Back to homepage', 'ai-awareness-day' ); ?></a>
            </p>
        </div>
    </section>
</main>
<?php
get_footer();
