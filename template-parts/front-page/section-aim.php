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
            <h2 class="section-title"><?php esc_html_e( 'What We Hope to Achieve', 'ai-awareness-day' ); ?></h2>
        </div>

        <?php
        $aims = array(
            __( 'Demystify AI for students, parents, and educators — making it accessible, understandable, and less intimidating.', 'ai-awareness-day' ),
            __( 'Develop critical thinking skills that enable young people to evaluate AI-generated content and make informed decisions.', 'ai-awareness-day' ),
            __( 'Build digital resilience so students can navigate an AI-powered world safely and confidently.', 'ai-awareness-day' ),
            __( 'Inspire creative and responsible use of AI tools across the curriculum and beyond the classroom.', 'ai-awareness-day' ),
            __( 'Foster a national conversation about the role of AI in education, skills development, and the future of work.', 'ai-awareness-day' ),
            __( 'Encourage students, educators, and parents to know what AI is, question how it works, and use it wisely in their everyday lives.', 'ai-awareness-day' ),
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
