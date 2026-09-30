<?php
/**
 * Archive template for Partners (Teachers, Sponsors, Schools, Tech Companies, Charities, Universities, Institutes).
 *
 * @package AI_Awareness_Day
 */

get_header();
?>

<main id="main" role="main" class="partners-archive">
    <section class="section section--top">
        <div class="container">
            <span class="section-label"><?php esc_html_e( 'Partners', 'ai-awareness-day' ); ?></span>
            <h1 class="section-title"><?php esc_html_e( 'Teachers, Sponsors &amp; Partners', 'ai-awareness-day' ); ?></h1>
            <p class="section-desc"><?php esc_html_e( 'Schools, charities, universities, institutes, tech companies, sponsors, and educators supporting AI Awareness Day.', 'ai-awareness-day' ); ?></p>

<?php get_template_part( 'template-parts/components/partners-directory' ); ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
