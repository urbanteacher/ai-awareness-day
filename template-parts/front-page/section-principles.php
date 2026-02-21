<?php
/**
 * Front page section: Principles
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}
?>
<section class="section section--alt <?php echo esc_attr( $text_alignment_class ); ?>" id="principles">
    <div class="container">
        <div class="fade-up">
            <span class="section-label"><?php esc_html_e( 'Principles', 'ai-awareness-day' ); ?></span>
            <h2 class="section-title"><?php esc_html_e( 'Five Core Principles', 'ai-awareness-day' ); ?></h2>
            <p class="section-desc">
                <?php esc_html_e( 'Our educational framework is built on five foundational principles that guide how we approach AI learning.', 'ai-awareness-day' ); ?>
            </p>
        </div>

        <div class="principles-grid">
            <?php
            $principle_slugs = array( 'safe', 'smart', 'creative', 'responsible', 'future' );
            $principle_default_titles = array(
                'safe'       => __( 'Safe', 'ai-awareness-day' ),
                'smart'      => __( 'Smart', 'ai-awareness-day' ),
                'creative'   => __( 'Creative', 'ai-awareness-day' ),
                'responsible' => __( 'Responsible', 'ai-awareness-day' ),
                'future'     => __( 'Future', 'ai-awareness-day' ),
            );
            $principle_default_descs = array(
                'safe'       => __( 'Ensuring safe and secure interactions with AI technologies.', 'ai-awareness-day' ),
                'smart'      => __( 'Building intelligent understanding of how AI works.', 'ai-awareness-day' ),
                'creative'   => __( 'Harnessing AI as a tool for creativity and innovation.', 'ai-awareness-day' ),
                'responsible' => __( 'Promoting ethical and responsible use of AI.', 'ai-awareness-day' ),
                'future'     => __( 'Preparing for an AI-shaped future with confidence.', 'ai-awareness-day' ),
            );
            foreach ( $principle_slugs as $index => $slug ) :
                $title_mod = get_theme_mod( 'aiad_principle_title_' . $slug, '' );
                $title    = ! empty( $title_mod ) ? $title_mod : ( isset( $principle_default_titles[ $slug ] ) ? $principle_default_titles[ $slug ] : ucfirst( $slug ) );
                $desc_mod  = get_theme_mod( 'aiad_principle_desc_' . $slug, '' );
                $desc     = ! empty( $desc_mod ) ? $desc_mod : ( isset( $principle_default_descs[ $slug ] ) ? $principle_default_descs[ $slug ] : '' );
                $p        = array( 'title' => $title, 'desc' => $desc );
                $badge_id = absint( get_theme_mod( 'aiad_badge_' . $slug, 0 ) );
                $badge_src = $badge_id ? wp_get_attachment_image_url( $badge_id, 'medium' ) : '';
                $has_badge_image = ! empty( $badge_src );
                ?>
                <div class="principle-card principle-card--<?php echo esc_attr( $slug ); ?> fade-up stagger-<?php echo $index + 1; ?>">
                    <div class="principle-badge">
                        <?php if ( $has_badge_image ) : ?>
                            <img src="<?php echo esc_url( $badge_src ); ?>" alt="" aria-hidden="true"
                                class="principle-badge__img" onerror="this.classList.add('is-broken');" />
                        <?php endif; ?>
                    </div>
                    <h3><?php echo esc_html( $p['title'] ); ?></h3>
                    <p class="section-desc"><?php echo esc_html( $p['desc'] ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
