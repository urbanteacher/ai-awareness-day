<?php
/**
 * Front page section: Aim
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}
?>
<section class="section section--green <?php echo esc_attr( $text_alignment_class ); ?>" id="aim">
    <div class="container">
        <div class="fade-up">
            <span class="section-label"><?php esc_html_e( 'Aim', 'ai-awareness-day' ); ?></span>
            <h2 class="section-title"><?php esc_html_e( 'Keep humans in the loop', 'ai-awareness-day' ); ?></h2>
        </div>

        <?php
        $aims = array(
            __( 'Help young people keep humans in the loop — knowing when to trust AI, when to check it, and when to decide without it.', 'ai-awareness-day' ),
            __( 'Give classrooms a shared language for five conversations: Safe, Smart, Creative, Responsible and Future.', 'ai-awareness-day' ),
            __( 'Build the habit of questioning what AI knows about you, what it decides for you, and what it makes in your name.', 'ai-awareness-day' ),
            __( 'Strengthen digital resilience so students can navigate an AI-shaped world with judgement, not fear.', 'ai-awareness-day' ),
            __( 'Inspire creative and responsible use of AI across the curriculum — with authorship and attribution kept honest.', 'ai-awareness-day' ),
            __( 'Grow a national conversation about the AI already in young people’s lives: Your AI. Your choices.', 'ai-awareness-day' ),
        );
        $aim_expand_threshold = 3;
        ?>
        <ol class="aims-list" id="aims-list">
            <?php foreach ( $aims as $index => $aim ) : ?>
                <li class="aim-item fade-up stagger-<?php echo $index + 1; ?>">
                    <span class="aim-num"><?php echo str_pad( $index + 1, 2, '0', STR_PAD_LEFT ); ?></span>
                    <p class="aim-text"><?php echo esc_html( $aim ); ?></p>
                </li>
            <?php endforeach; ?>
        </ol>

        <?php if ( count( $aims ) > $aim_expand_threshold ) : ?>
            <div class="aims-expand-wrap">
                <button
                    type="button"
                    class="aims-expand"
                    id="aim-expand"
                    aria-expanded="false"
                    aria-controls="aims-list"
                    data-label-more="<?php echo esc_attr__( 'Show more', 'ai-awareness-day' ); ?>"
                    data-label-less="<?php echo esc_attr__( 'Show less', 'ai-awareness-day' ); ?>"
                >
                    <?php esc_html_e( 'Show more', 'ai-awareness-day' ); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>
</section>
