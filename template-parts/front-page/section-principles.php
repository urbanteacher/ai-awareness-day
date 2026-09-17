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
            $principle_slugs = array( 'safe', 'smart', 'creative', 'responsible', 'future' );
            $principle_default_titles = array(
                'safe'       => __( 'Safe', 'ai-awareness-day' ),
                'smart'      => __( 'Smart', 'ai-awareness-day' ),
                'creative'   => __( 'Creative', 'ai-awareness-day' ),
                'responsible' => __( 'Responsible', 'ai-awareness-day' ),
                'future'     => __( 'Future', 'ai-awareness-day' ),
            );
            $principle_default_descs = array(
                'safe'       => __( 'Start with what should stay private — trust, sharing and the data AI holds about you.', 'ai-awareness-day' ),
                'smart'      => __( 'Question AI that acts on your behalf — decisions, shortcuts and who is really choosing.', 'ai-awareness-day' ),
                'creative'   => __( 'Own what you make with AI — authorship, attribution and honest creative work.', 'ai-awareness-day' ),
                'responsible' => __( 'Keep human judgement in consequential moments — when the output matters.', 'ai-awareness-day' ),
                'future'     => __( 'Name the skills worth keeping human — and practise them on purpose.', 'ai-awareness-day' ),
            );
            foreach ( $principle_slugs as $index => $slug ) :
                $title_mod = get_theme_mod( 'aiad_principle_title_' . $slug, '' );
                $title    = ! empty( $title_mod ) ? $title_mod : ( isset( $principle_default_titles[ $slug ] ) ? $principle_default_titles[ $slug ] : ucfirst( $slug ) );
                $desc_mod  = get_theme_mod( 'aiad_principle_desc_' . $slug, '' );
                $desc     = ! empty( $desc_mod ) ? $desc_mod : ( isset( $principle_default_descs[ $slug ] ) ? $principle_default_descs[ $slug ] : '' );
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
            <?php endforeach; ?>

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
                <h3><?php esc_html_e( 'Your AI. Your choices.', 'ai-awareness-day' ); ?></h3>
                <p class="section-desc"><?php esc_html_e( 'These five strands are one literacy — Keep Humans in the Loop.', 'ai-awareness-day' ); ?></p>
            </div>
        </div>
    </div>
</section>
