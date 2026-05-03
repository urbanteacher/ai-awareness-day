<?php
/**
 * Front page section: Get Started Now
 *
 * Showcases the three time-banded entry-point resources, the Live Assemblies
 * announcement, and AI Quizzes & Games from partners.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}
?>
<section class="section get-started-section fade-up" id="get-started">
    <div class="container">
        <span class="section-label"><?php esc_html_e( 'Resources', 'ai-awareness-day' ); ?></span>
        <h2 class="section-title"><?php esc_html_e( 'Get started now', 'ai-awareness-day' ); ?></h2>
        <p class="section-desc">
            <?php esc_html_e( 'Pick a session length that fits your day — every resource is free, ready to use, and designed for schools.', 'ai-awareness-day' ); ?>
        </p>

        <div class="gs-grid">

            <!-- 5 min: Lesson Starter -->
            <a href="<?php echo esc_url( home_url( '/resources/how-does-ai-actually-think/' ) ); ?>"
               class="gs-card gs-card--5min fade-up stagger-1">
                <div class="gs-card__header">
                    <span class="gs-card__time">5</span>
                    <span class="gs-card__unit"><?php esc_html_e( 'MIN', 'ai-awareness-day' ); ?></span>
                </div>
                <div class="gs-card__body">
                    <p class="gs-card__type"><?php esc_html_e( 'Lesson Starter', 'ai-awareness-day' ); ?></p>
                    <h3 class="gs-card__title"><?php esc_html_e( 'How Does AI Actually Think?', 'ai-awareness-day' ); ?></h3>
                    <div class="gs-card__formats">
                        <span class="gs-card__format-pill"><?php esc_html_e( 'PPT', 'ai-awareness-day' ); ?></span>
                        <span class="gs-card__format-pill"><?php esc_html_e( 'Video', 'ai-awareness-day' ); ?></span>
                    </div>
                </div>
                <span class="gs-card__cta"><?php esc_html_e( 'View resource', 'ai-awareness-day' ); ?> →</span>
            </a>

            <!-- 15 min: Tutor Time -->
            <a href="<?php echo esc_url( home_url( '/resources/how-ai-actually-works-bbc-ideas/' ) ); ?>"
               class="gs-card gs-card--15min fade-up stagger-2">
                <div class="gs-card__header">
                    <span class="gs-card__time">15</span>
                    <span class="gs-card__unit"><?php esc_html_e( 'MIN', 'ai-awareness-day' ); ?></span>
                </div>
                <div class="gs-card__body">
                    <p class="gs-card__type"><?php esc_html_e( 'Tutor Time', 'ai-awareness-day' ); ?></p>
                    <h3 class="gs-card__title"><?php esc_html_e( 'How AI Actually Works – BBC Ideas', 'ai-awareness-day' ); ?></h3>
                    <div class="gs-card__formats">
                        <span class="gs-card__format-pill"><?php esc_html_e( 'Video', 'ai-awareness-day' ); ?></span>
                    </div>
                </div>
                <span class="gs-card__cta"><?php esc_html_e( 'View resource', 'ai-awareness-day' ); ?> →</span>
            </a>

            <!-- 20 min: Assembly -->
            <a href="<?php echo esc_url( home_url( '/resources/ai-is-already-here/' ) ); ?>"
               class="gs-card gs-card--20min fade-up stagger-3">
                <div class="gs-card__header">
                    <span class="gs-card__time">20</span>
                    <span class="gs-card__unit"><?php esc_html_e( 'MIN', 'ai-awareness-day' ); ?></span>
                </div>
                <div class="gs-card__body">
                    <p class="gs-card__type"><?php esc_html_e( 'Assembly', 'ai-awareness-day' ); ?></p>
                    <h3 class="gs-card__title"><?php esc_html_e( 'AI Is Already Here', 'ai-awareness-day' ); ?></h3>
                    <div class="gs-card__formats">
                        <span class="gs-card__format-pill"><?php esc_html_e( 'PPT', 'ai-awareness-day' ); ?></span>
                        <span class="gs-card__format-pill"><?php esc_html_e( 'Video', 'ai-awareness-day' ); ?></span>
                    </div>
                </div>
                <span class="gs-card__cta"><?php esc_html_e( 'View resource', 'ai-awareness-day' ); ?> →</span>
            </a>

            <!-- Live Assemblies — Just Announced -->
            <a href="#timeline" class="gs-card gs-card--assemblies fade-up stagger-4">
                <span class="gs-card__new-badge"><?php esc_html_e( 'Just Announced', 'ai-awareness-day' ); ?></span>
                <div class="gs-card__body">
                    <p class="gs-card__type"><?php esc_html_e( 'Live Assemblies', 'ai-awareness-day' ); ?></p>
                    <h3 class="gs-card__title"><?php esc_html_e( 'KS1, KS2 &amp; KS3 Live Assemblies', 'ai-awareness-day' ); ?></h3>
                    <p class="gs-card__desc">
                        <?php esc_html_e( 'Book a live AI assembly for your school. Visit our timeline for dates and booking information.', 'ai-awareness-day' ); ?>
                    </p>
                </div>
                <span class="gs-card__cta"><?php esc_html_e( 'See timeline', 'ai-awareness-day' ); ?> →</span>
            </a>

            <!-- AI Quizzes & Games -->
            <a href="<?php echo esc_url( home_url( '/from-partners/' ) ); ?>"
               class="gs-card gs-card--games fade-up stagger-5">
                <div class="gs-card__body">
                    <p class="gs-card__type"><?php esc_html_e( 'From Partners', 'ai-awareness-day' ); ?></p>
                    <h3 class="gs-card__title"><?php esc_html_e( 'AI Quizzes &amp; Games', 'ai-awareness-day' ); ?></h3>
                    <p class="gs-card__desc">
                        <?php esc_html_e( 'Interactive AI tools, games and learning activities from trusted organisations.', 'ai-awareness-day' ); ?>
                    </p>
                </div>
                <span class="gs-card__cta"><?php esc_html_e( 'Browse all', 'ai-awareness-day' ); ?> →</span>
            </a>

        </div>
    </div>
</section>
