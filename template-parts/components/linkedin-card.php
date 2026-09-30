<?php
/**
 * The "Latest from LinkedIn" card, a section of its own after the featured resources: the homepage's featured
 * resources section and the LinkedIn card block (blocks/linkedin-card) both print it. Nothing without an address.
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}
$text_alignment_class = (string) ( $args['text_alignment_class'] ?? '' );
        $linkedin_post_url = esc_url_raw( (string) ( $args['url'] ?? '' ) );
    if (!empty($linkedin_post_url)):
        ?>
        <!-- LinkedIn post card -->
        <section class="section <?php echo esc_attr($text_alignment_class); ?>" id="linkedin-post"
            aria-labelledby="linkedin-post-title">
            <div class="container">
                <div class="linkedin-card-wrapper fade-up">
                    <a href="<?php echo esc_url($linkedin_post_url); ?>" class="linkedin-card" target="_blank"
                        rel="noopener noreferrer">
                        <span class="linkedin-card__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="currentColor" aria-hidden="true">
                                <path
                                    d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                            </svg>
                        </span>
                        <div class="linkedin-card__content">
                            <h2 id="linkedin-post-title" class="linkedin-card__title">
                                <?php esc_html_e('Latest from LinkedIn', 'ai-awareness-day'); ?>
                            </h2>
                            <p class="linkedin-card__desc">
                                <?php esc_html_e('See our latest post and join the conversation.', 'ai-awareness-day'); ?>
                            </p>
                            <span
                                class="linkedin-card__cta"><?php esc_html_e('View post on LinkedIn', 'ai-awareness-day'); ?>
                                →</span>
                        </div>
                    </a>
                </div>
            </div>
        </section>
        <?php
        endif;
