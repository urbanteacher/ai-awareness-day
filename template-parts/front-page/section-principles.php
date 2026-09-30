<?php
/**
 * Front page section: Principles
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$text_alignment_class = isset( $args['text_alignment_class'] ) ? (string) $args['text_alignment_class'] : aiad_get_text_alignment_class();
?>
<section class="section <?php echo esc_attr( $text_alignment_class ); ?>" id="principles">
    <div class="container">
        <div class="fade-up">
            <span class="section-label"><?php esc_html_e( 'Five strands', 'ai-awareness-day' ); ?></span>
            <h2 class="section-title"><?php esc_html_e( 'Safe. Smart. Creative. Responsible. Future.', 'ai-awareness-day' ); ?></h2>
            <p class="section-desc">
                <?php esc_html_e( 'One campaign, five classroom starters. Each strand has a colour, a word and a mark — and a conversation worth having about the AI already in young people’s lives.', 'ai-awareness-day' ); ?>
            </p>
        </div>

        <div class="principles-grid" role="region"
            aria-label="<?php echo esc_attr__( 'Core principles — swipe sideways to explore each card', 'ai-awareness-day' ); ?>">
            <?php
            // Wording: the Customizer's, else the standard wording (aiad_principle_cards() in inc/homepage-blocks.php).
            $principle_slugs = array( 'safe', 'smart', 'creative', 'responsible', 'future' );
            foreach ( $principle_slugs as $index => $slug ) :
                list( $title, $desc ) = aiad_principle_card_wording( $slug );
                $p        = array( 'title' => $title, 'desc' => $desc );
                $badge_src = function_exists( 'aiad_strand_icon_uri' )
                    ? aiad_strand_icon_uri( $slug )
                    : ( AIAD_URI . '/assets/brand/aiad27/icon-' . $slug . '.svg' );
                $themes_href = '#themes';
                $card_label  = sprintf(
                    /* translators: %s: strand name e.g. Safe */
                    __( 'Explore %s activities', 'ai-awareness-day' ),
                    $title
                );
                ?>
                <a href="<?php echo esc_url( $themes_href ); ?>"
                    class="principle-card principle-card--<?php echo esc_attr( $slug ); ?> fade-up stagger-<?php echo $index + 1; ?>"
                    aria-label="<?php echo esc_attr( $card_label ); ?>">
                    <div class="principle-badge">
                        <img src="<?php echo esc_url( $badge_src ); ?>" alt="" aria-hidden="true" class="principle-badge__img" />
                    </div>
                    <h3><?php echo esc_html( $p['title'] ); ?></h3>
                    <p class="section-desc"><?php echo esc_html( $p['desc'] ); ?></p>
                </a>
            <?php endforeach; list( $literacy_title, $literacy_desc ) = aiad_principle_card_wording( 'literacy' ); ?>

            <div class="ai-literacy-box principle-card fade-up stagger-6">
                <div class="principle-badge">
                    <?php
                    $literacy_logo_src = aiad_get_logo_image_url( aiad_get_literacy_logo_attachment_id(), 'medium' );
                    if ( $literacy_logo_src ) :
                        ?>
                        <img src="<?php echo esc_url( $literacy_logo_src ); ?>" alt="" aria-hidden="true"
                            class="principle-badge__img" onerror="this.classList.add('is-broken');" />
                    <?php else : ?>
                        <div class="principle-badge__placeholder" aria-hidden="true">
                            <span class="principle-badge__placeholder-text"><?php esc_html_e( 'AI', 'ai-awareness-day' ); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <h3><?php echo esc_html( $literacy_title ); ?></h3>
                <p class="section-desc"><?php echo esc_html( $literacy_desc ); ?></p>
            </div>
        </div>
    </div>
</section>
