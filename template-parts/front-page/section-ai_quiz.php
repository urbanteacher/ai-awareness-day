<?php
/**
 * Front page section: AI literacy quiz.
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}
?>
<section class="section section--alt <?php echo esc_attr( $text_alignment_class ); ?>" id="ai-quiz">
    <div class="container">
        <div class="ai-quiz fade-up" data-ai-quiz>
            <span class="section-label"><?php esc_html_e( 'AI Literacy Check', 'ai-awareness-day' ); ?></span>
            <h2 class="section-title"><?php esc_html_e( 'How AI-literate are you?', 'ai-awareness-day' ); ?></h2>
            <p class="section-desc"><?php esc_html_e( 'Answer five quick questions and find out instantly.', 'ai-awareness-day' ); ?></p>

            <form class="ai-quiz__form" novalidate>

                <fieldset class="ai-quiz__question" data-question="q1" data-correct="b">
                    <legend><?php esc_html_e( '1. What does AI model bias mean?', 'ai-awareness-day' ); ?></legend>
                    <label class="ai-quiz__option"><input type="radio" name="q1" value="a"> <?php esc_html_e( 'It runs faster on some laptops', 'ai-awareness-day' ); ?></label>
                    <label class="ai-quiz__option"><input type="radio" name="q1" value="b"> <?php esc_html_e( 'It can produce unfair outcomes for some groups', 'ai-awareness-day' ); ?></label>
                    <label class="ai-quiz__option"><input type="radio" name="q1" value="c"> <?php esc_html_e( 'It always improves over time', 'ai-awareness-day' ); ?></label>
                </fieldset>

                <fieldset class="ai-quiz__question" data-question="q2" data-correct="c">
                    <legend><?php esc_html_e( '2. Best practice for using AI-generated content in schoolwork?', 'ai-awareness-day' ); ?></legend>
                    <label class="ai-quiz__option"><input type="radio" name="q2" value="a"> <?php esc_html_e( 'Submit it straight away', 'ai-awareness-day' ); ?></label>
                    <label class="ai-quiz__option"><input type="radio" name="q2" value="b"> <?php esc_html_e( 'Only use it if a friend agrees it is correct', 'ai-awareness-day' ); ?></label>
                    <label class="ai-quiz__option"><input type="radio" name="q2" value="c"> <?php esc_html_e( 'Fact-check and edit it before use', 'ai-awareness-day' ); ?></label>
                </fieldset>

                <fieldset class="ai-quiz__question" data-question="q3" data-correct="a">
                    <legend><?php esc_html_e( '3. What should you avoid sharing with public AI tools?', 'ai-awareness-day' ); ?></legend>
                    <label class="ai-quiz__option"><input type="radio" name="q3" value="a"> <?php esc_html_e( 'Personal or sensitive data', 'ai-awareness-day' ); ?></label>
                    <label class="ai-quiz__option"><input type="radio" name="q3" value="b"> <?php esc_html_e( 'Maths questions', 'ai-awareness-day' ); ?></label>
                    <label class="ai-quiz__option"><input type="radio" name="q3" value="c"> <?php esc_html_e( 'Creative writing prompts', 'ai-awareness-day' ); ?></label>
                </fieldset>

                <fieldset class="ai-quiz__question" data-question="q4" data-correct="c">
                    <legend><?php esc_html_e( '4. Why does prompt quality matter?', 'ai-awareness-day' ); ?></legend>
                    <label class="ai-quiz__option"><input type="radio" name="q4" value="a"> <?php esc_html_e( 'It changes your keyboard layout', 'ai-awareness-day' ); ?></label>
                    <label class="ai-quiz__option"><input type="radio" name="q4" value="b"> <?php esc_html_e( 'It only matters for image tools', 'ai-awareness-day' ); ?></label>
                    <label class="ai-quiz__option"><input type="radio" name="q4" value="c"> <?php esc_html_e( 'Clear prompts usually produce much better outputs', 'ai-awareness-day' ); ?></label>
                </fieldset>

                <fieldset class="ai-quiz__question" data-question="q5" data-correct="b">
                    <legend><?php esc_html_e( '5. Which statement about AI answers is true?', 'ai-awareness-day' ); ?></legend>
                    <label class="ai-quiz__option"><input type="radio" name="q5" value="a"> <?php esc_html_e( 'They are always accurate', 'ai-awareness-day' ); ?></label>
                    <label class="ai-quiz__option"><input type="radio" name="q5" value="b"> <?php esc_html_e( 'They can sound confident and still be wrong', 'ai-awareness-day' ); ?></label>
                    <label class="ai-quiz__option"><input type="radio" name="q5" value="c"> <?php esc_html_e( 'They never need human review', 'ai-awareness-day' ); ?></label>
                </fieldset>

                <div class="ai-quiz__actions">
                    <button type="button" class="resource-filter-submit ai-quiz__submit" data-ai-quiz-submit>
                        <?php esc_html_e( 'Get my score', 'ai-awareness-day' ); ?>
                    </button>
                    <button type="button" class="ai-quiz__reset" data-ai-quiz-reset style="display:none;">
                        <?php esc_html_e( 'Try again', 'ai-awareness-day' ); ?>
                    </button>
                </div>

                <div class="ai-quiz__result" data-ai-quiz-result aria-live="polite" aria-atomic="true"></div>

            </form>
        </div>
    </div>
</section>
