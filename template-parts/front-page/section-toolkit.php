<?php
/**
 * Front page section: Toolkit (display board + activities — toolkit cards moved to footer links)
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}
?>
<!-- Display board: separate section -->
<section id="display-board" class="section <?php echo esc_attr( $text_alignment_class ); ?>">
    <div class="container">
        <div class="toolkit-display-board fade-up">
            <span class="section-label"><?php esc_html_e( 'Display board', 'ai-awareness-day' ); ?></span>
            <h2 class="section-title"><?php esc_html_e( 'Create a display board for your school', 'ai-awareness-day' ); ?></h2>
            <p class="section-desc"><?php esc_html_e( 'Use the layout below as a guide to build a physical display in your school or staff room.', 'ai-awareness-day' ); ?></p>
        </div>

        <?php
        $display_img_1_id  = absint( get_theme_mod( 'aiad_display_board_image_1', 0 ) );
        $display_img_1_url = $display_img_1_id ? wp_get_attachment_image_url( $display_img_1_id, 'full' ) : '';
        $display_img_2_id  = absint( get_theme_mod( 'aiad_display_board_image_2', 0 ) );
        $display_img_3_id  = absint( get_theme_mod( 'aiad_display_board_image_3', 0 ) );
        $display_img_2_url = $display_img_2_id ? wp_get_attachment_image_url( $display_img_2_id, 'full' ) : '';
        $display_img_3_url = $display_img_3_id ? wp_get_attachment_image_url( $display_img_3_id, 'full' ) : '';
        $has_real          = (bool) $display_img_1_url;
        $has_more          = $display_img_2_url || $display_img_3_url;
        $default_tab       = $has_real ? 'real' : 'blueprint';

        $contact_email = get_theme_mod( 'aiad_contact_email', get_option( 'admin_email' ) );
        $mailto_link   = 'mailto:' . esc_attr( $contact_email )
            . '?subject=' . rawurlencode( 'Our Schools Display' )
            . '&body='    . rawurlencode( "Hello,\n\nPlease find attached photos of our school's AI Awareness Day display board.\n\nThank you!" );
        ?>

        <div class="display-board-tabs js-display-board-tabs" data-default="<?php echo esc_attr( $default_tab ); ?>">

            <div class="display-board-tabbar" role="tablist" aria-label="<?php esc_attr_e( 'Display board views', 'ai-awareness-day' ); ?>">
                <?php if ( $has_real ) : ?>
                <button type="button" class="display-board-tab<?php echo $default_tab === 'real' ? ' is-active' : ''; ?>"
                    role="tab" data-tab="real" id="dbt-btn-real"
                    aria-selected="<?php echo $default_tab === 'real' ? 'true' : 'false'; ?>"
                    aria-controls="dbt-panel-real"><?php esc_html_e( 'Example', 'ai-awareness-day' ); ?></button>
                <?php endif; ?>
                <button type="button" class="display-board-tab<?php echo $default_tab === 'blueprint' ? ' is-active' : ''; ?>"
                    role="tab" data-tab="blueprint" id="dbt-btn-blueprint"
                    aria-selected="<?php echo $default_tab === 'blueprint' ? 'true' : 'false'; ?>"
                    aria-controls="dbt-panel-blueprint"><?php esc_html_e( 'Blueprint', 'ai-awareness-day' ); ?></button>
                <button type="button" class="display-board-tab"
                    role="tab" data-tab="steps" id="dbt-btn-steps"
                    aria-selected="false"
                    aria-controls="dbt-panel-steps"><?php esc_html_e( 'How to create', 'ai-awareness-day' ); ?></button>
                <?php if ( $has_more ) : ?>
                <button type="button" class="display-board-tab"
                    role="tab" data-tab="examples" id="dbt-btn-examples"
                    aria-selected="false"
                    aria-controls="dbt-panel-examples"><?php esc_html_e( 'More examples', 'ai-awareness-day' ); ?></button>
                <?php endif; ?>
            </div>

            <?php if ( $has_real ) : ?>
            <div class="display-board-panel" id="dbt-panel-real" role="tabpanel" aria-labelledby="dbt-btn-real"<?php echo $default_tab !== 'real' ? ' hidden' : ''; ?>>
                <div class="display-board-real">
                    <img src="<?php echo esc_url( $display_img_1_url ); ?>"
                        alt="<?php esc_attr_e( 'Example display board', 'ai-awareness-day' ); ?>" loading="lazy" />
                </div>
            </div>
            <?php endif; ?>

            <div class="display-board-panel" id="dbt-panel-blueprint" role="tabpanel" aria-labelledby="dbt-btn-blueprint"<?php echo $default_tab !== 'blueprint' ? ' hidden' : ''; ?>>
                <div class="dbm" aria-label="<?php esc_attr_e( 'Display board layout guide', 'ai-awareness-day' ); ?>">

                    <!-- Header -->
                    <div class="dbm__header">
                        <div class="dbm__header-logo"><span><?php esc_html_e( 'LOGO', 'ai-awareness-day' ); ?></span></div>
                        <div class="dbm__header-text">
                            <strong class="dbm__header-title"><?php esc_html_e( 'AI Awareness Day 2026', 'ai-awareness-day' ); ?></strong>
                            <span class="dbm__header-tagline"><?php esc_html_e( 'Know it, Question it, Use it Wisely', 'ai-awareness-day' ); ?></span>
                        </div>
                    </div>

                    <!-- 6 Theme Panels -->
                    <div class="dbm__panels">

                        <div class="dbm__panel dbm__panel--safe">
                            <div class="dbm__panel-header">
                                <span class="dbm__panel-icon" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>
                                <h3 class="dbm__panel-title"><?php esc_html_e( 'SAFE', 'ai-awareness-day' ); ?></h3>
                            </div>
                            <div class="dbm__panel-body">
                                <p class="dbm__panel-desc"><?php esc_html_e( 'Protect your privacy and personal data when using AI tools.', 'ai-awareness-day' ); ?></p>
                                <p class="dbm__panel-fact"><span class="dbm__fact-label"><?php esc_html_e( 'Did you know:', 'ai-awareness-day' ); ?></span> <?php esc_html_e( 'AI systems can be biased if trained on biased data. Always question the source and verify information!', 'ai-awareness-day' ); ?></p>
                            </div>
                        </div>

                        <div class="dbm__panel dbm__panel--smart">
                            <div class="dbm__panel-header">
                                <span class="dbm__panel-icon" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96-.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 1.98-3A2.5 2.5 0 0 1 9.5 2Z"/><path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96-.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-1.98-3A2.5 2.5 0 0 0 14.5 2Z"/></svg></span>
                                <h3 class="dbm__panel-title"><?php esc_html_e( 'SMART', 'ai-awareness-day' ); ?></h3>
                            </div>
                            <div class="dbm__panel-body">
                                <p class="dbm__panel-desc"><?php esc_html_e( 'AI processes info faster than humans, but humans are better at creative problem-solving!', 'ai-awareness-day' ); ?></p>
                                <p class="dbm__panel-fact"><span class="dbm__fact-label"><?php esc_html_e( 'Did you know:', 'ai-awareness-day' ); ?></span> <?php esc_html_e( 'ChatGPT was trained on 45TB of text data — that\'s equivalent to reading every book in a large library!', 'ai-awareness-day' ); ?></p>
                            </div>
                        </div>

                        <div class="dbm__panel dbm__panel--creative">
                            <div class="dbm__panel-header">
                                <span class="dbm__panel-icon" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></span>
                                <h3 class="dbm__panel-title"><?php esc_html_e( 'CREATIVE', 'ai-awareness-day' ); ?></h3>
                            </div>
                            <div class="dbm__panel-body">
                                <p class="dbm__panel-desc"><?php esc_html_e( 'AI can generate art and music, but the most creative works come from human-AI collaboration!', 'ai-awareness-day' ); ?></p>
                                <p class="dbm__panel-fact"><span class="dbm__fact-label"><?php esc_html_e( 'Did you know:', 'ai-awareness-day' ); ?></span> <?php esc_html_e( 'AI can recognise patterns humans miss and generate creative solutions in seconds!', 'ai-awareness-day' ); ?></p>
                            </div>
                        </div>

                        <div class="dbm__panel dbm__panel--responsible">
                            <div class="dbm__panel-header">
                                <span class="dbm__panel-icon" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg></span>
                                <h3 class="dbm__panel-title"><?php esc_html_e( 'RESPONSIBLE', 'ai-awareness-day' ); ?></h3>
                            </div>
                            <div class="dbm__panel-body">
                                <p class="dbm__panel-desc"><?php esc_html_e( 'Every AI decision affects real people. We must consider the impact and use technology responsibly!', 'ai-awareness-day' ); ?></p>
                                <p class="dbm__panel-fact"><span class="dbm__fact-label"><?php esc_html_e( 'Did you know:', 'ai-awareness-day' ); ?></span> <?php esc_html_e( 'AI can process information 1 million times faster than humans, but we must use it ethically!', 'ai-awareness-day' ); ?></p>
                            </div>
                        </div>

                        <div class="dbm__panel dbm__panel--future">
                            <div class="dbm__panel-header">
                                <span class="dbm__panel-icon" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg></span>
                                <h3 class="dbm__panel-title"><?php esc_html_e( 'FUTURE', 'ai-awareness-day' ); ?></h3>
                            </div>
                            <div class="dbm__panel-body">
                                <p class="dbm__panel-desc"><?php esc_html_e( 'By 2030, 85% of jobs will require AI skills. Start learning now to be future-ready!', 'ai-awareness-day' ); ?></p>
                                <p class="dbm__panel-fact"><span class="dbm__fact-label"><?php esc_html_e( 'Did you know:', 'ai-awareness-day' ); ?></span> <?php esc_html_e( 'The AI industry is growing 40% each year — learning AI skills now prepares you for tomorrow\'s jobs!', 'ai-awareness-day' ); ?></p>
                            </div>
                        </div>

                        <div class="dbm__panel dbm__panel--qr">
                            <div class="dbm__panel-header">
                                <span class="dbm__panel-icon" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M21 21v.01"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/><path d="M3 12h.01"/><path d="M12 3h.01"/><path d="M12 16v.01"/><path d="M16 12h1"/><path d="M21 12v.01"/><path d="M12 21v-1"/></svg></span>
                                <h3 class="dbm__panel-title"><?php esc_html_e( 'QR CHALLENGES', 'ai-awareness-day' ); ?></h3>
                            </div>
                            <div class="dbm__panel-body">
                                <p class="dbm__panel-desc"><?php esc_html_e( 'Scan QR codes to discover your school\'s AI policies and guidelines!', 'ai-awareness-day' ); ?></p>
                                <div class="dbm__qr-grid">
                                    <div class="dbm__qr-item">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M21 21v.01"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/><path d="M3 12h.01"/><path d="M12 3h.01"/><path d="M12 16v.01"/><path d="M16 12h1"/><path d="M21 12v.01"/><path d="M12 21v-1"/></svg>
                                        <span><?php esc_html_e( 'School Policy', 'ai-awareness-day' ); ?></span>
                                    </div>
                                    <div class="dbm__qr-item">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M21 21v.01"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/><path d="M3 12h.01"/><path d="M12 3h.01"/><path d="M12 16v.01"/><path d="M16 12h1"/><path d="M21 12v.01"/><path d="M12 21v-1"/></svg>
                                        <span><?php esc_html_e( 'AI Guidelines', 'ai-awareness-day' ); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div><!-- .dbm__panels -->

                    <!-- Interactive: Questions + Responses -->
                    <div class="dbm__row">

                        <div class="dbm__panel dbm__panel--questions">
                            <div class="dbm__panel-header">
                                <span class="dbm__panel-icon" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span>
                                <h3 class="dbm__panel-title"><?php esc_html_e( "This Week's Questions", 'ai-awareness-day' ); ?></h3>
                            </div>
                            <div class="dbm__panel-body">
                                <ul class="dbm__questions">
                                    <li><?php esc_html_e( 'How can we ensure AI tools are fair?', 'ai-awareness-day' ); ?></li>
                                    <li><?php esc_html_e( "What are AI's strengths vs humans?", 'ai-awareness-day' ); ?></li>
                                    <li><?php esc_html_e( 'How can AI enhance creativity?', 'ai-awareness-day' ); ?></li>
                                </ul>
                            </div>
                        </div>

                        <div class="dbm__panel dbm__panel--responses">
                            <div class="dbm__panel-header">
                                <span class="dbm__panel-icon" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></span>
                                <h3 class="dbm__panel-title"><?php esc_html_e( 'Student Responses', 'ai-awareness-day' ); ?></h3>
                            </div>
                            <div class="dbm__panel-body">
                                <p class="dbm__panel-quote"><?php esc_html_e( '"AI should be transparent"', 'ai-awareness-day' ); ?></p>
                                <p class="dbm__panel-quote"><?php esc_html_e( '"Humans understand emotions better"', 'ai-awareness-day' ); ?></p>
                                <p class="dbm__panel-quote"><?php esc_html_e( '"AI helps brainstorm, I add creativity"', 'ai-awareness-day' ); ?></p>
                                <p class="dbm__sticky-hint"><?php esc_html_e( 'Students write answers on sticky notes here', 'ai-awareness-day' ); ?></p>
                            </div>
                        </div>

                    </div><!-- .dbm__row -->

                    <!-- AI Leaders Gallery -->
                    <div class="dbm__panel dbm__panel--leaders dbm__panel--wide">
                        <div class="dbm__panel-header">
                            <span class="dbm__panel-icon" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
                            <h3 class="dbm__panel-title"><?php esc_html_e( 'AI Leaders & Innovators', 'ai-awareness-day' ); ?></h3>
                        </div>
                        <div class="dbm__panel-body">
                            <div class="dbm__gallery">
                                <div class="dbm__gallery-item"><span aria-hidden="true">📸</span><p><?php esc_html_e( 'Add Photo', 'ai-awareness-day' ); ?></p></div>
                                <div class="dbm__gallery-item"><span aria-hidden="true">📸</span><p><?php esc_html_e( 'Add Photo', 'ai-awareness-day' ); ?></p></div>
                                <div class="dbm__gallery-item"><span aria-hidden="true">📸</span><p><?php esc_html_e( 'Add Photo', 'ai-awareness-day' ); ?></p></div>
                            </div>
                            <p class="dbm__gallery-hint"><?php esc_html_e( 'Add photos of AI leaders like Andrew Ng, Fei-Fei Li, Yann LeCun, etc. Set students the challenge: find 3 living people working in AI!', 'ai-awareness-day' ); ?></p>
                        </div>
                    </div>

                    <!-- Student Spotlight -->
                    <div class="dbm__panel dbm__panel--spotlight dbm__panel--wide">
                        <div class="dbm__panel-header">
                            <span class="dbm__panel-icon" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
                            <h3 class="dbm__panel-title"><?php esc_html_e( 'Student Spotlight', 'ai-awareness-day' ); ?></h3>
                        </div>
                        <div class="dbm__panel-body">
                            <div class="dbm__spotlight">
                                <div class="dbm__spotlight-item">
                                    <div class="dbm__spotlight-avatar" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
                                    <div>
                                        <p class="dbm__spotlight-name"><?php esc_html_e( 'Student Name', 'ai-awareness-day' ); ?></p>
                                        <p class="dbm__spotlight-work"><?php esc_html_e( 'Add student work or project here', 'ai-awareness-day' ); ?></p>
                                    </div>
                                </div>
                                <div class="dbm__spotlight-item">
                                    <div class="dbm__spotlight-avatar" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
                                    <div>
                                        <p class="dbm__spotlight-name"><?php esc_html_e( 'Student Name', 'ai-awareness-day' ); ?></p>
                                        <p class="dbm__spotlight-work"><?php esc_html_e( 'Add student work or project here', 'ai-awareness-day' ); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- .dbm -->
            </div>

            <div class="display-board-panel" id="dbt-panel-steps" role="tabpanel" aria-labelledby="dbt-btn-steps" hidden>
                <ol class="display-board-steps__list">
                    <li class="section-desc"><?php esc_html_e( 'Select a prominent wall, noticeboard, or display area in your school or staff room.', 'ai-awareness-day' ); ?></li>
                    <li class="section-desc"><?php esc_html_e( 'Follow the blueprint layout: create five principle panels (Safe, Smart, Creative, Responsible, Future) each with a key message and practical tips.', 'ai-awareness-day' ); ?></li>
                    <li class="section-desc"><?php esc_html_e( "QR challenges: Set up QR codes for students to scan & investigate. Link to your school's AI policy and our AI guidelines or activities.", 'ai-awareness-day' ); ?></li>
                    <li class="section-desc"><?php esc_html_e( 'Add interactive elements: Include facts, tips, or QR codes linking to games and quizzes using our interactive resources.', 'ai-awareness-day' ); ?></li>
                    <li class="section-desc"><?php esc_html_e( "This week's questions: Add thought-provoking questions like \"How can we ensure AI tools are fair?\" with space for student responses.", 'ai-awareness-day' ); ?></li>
                    <li class="section-desc"><?php esc_html_e( 'Student responses: Provide space for sticky notes or written answers where students can share their thoughts and ideas.', 'ai-awareness-day' ); ?></li>
                    <li class="section-desc"><?php esc_html_e( 'AI leaders & innovators: Include photos and names of people working in AI.', 'ai-awareness-day' ); ?></li>
                    <li class="section-desc"><?php esc_html_e( 'Set them a challenge: Ask students to find 3 living people working in AI and add their discoveries to the display.', 'ai-awareness-day' ); ?></li>
                    <li class="section-desc"><?php esc_html_e( 'Student spotlight: Feature student work or projects to showcase pupil achievements and creativity.', 'ai-awareness-day' ); ?></li>
                </ol>
            </div>

            <?php if ( $has_more ) : ?>
            <div class="display-board-panel" id="dbt-panel-examples" role="tabpanel" aria-labelledby="dbt-btn-examples" hidden>
                <div class="display-board-examples__grid">
                    <?php if ( $display_img_2_url ) : ?>
                    <figure class="display-board-examples__item">
                        <img src="<?php echo esc_url( $display_img_2_url ); ?>" alt="<?php esc_attr_e( 'Example display board 2', 'ai-awareness-day' ); ?>" loading="lazy" />
                    </figure>
                    <?php endif; ?>
                    <?php if ( $display_img_3_url ) : ?>
                    <figure class="display-board-examples__item">
                        <img src="<?php echo esc_url( $display_img_3_url ); ?>" alt="<?php esc_attr_e( 'Example display board 3', 'ai-awareness-day' ); ?>" loading="lazy" />
                    </figure>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </div><!-- .display-board-tabs -->

        <div class="display-board-submit fade-up">
            <div class="display-board-submit__content">
                <div class="display-board-submit__text">
                    <h3 class="display-board-submit__heading"><?php esc_html_e( 'Submit your display board', 'ai-awareness-day' ); ?></h3>
                    <p class="section-desc"><?php esc_html_e( "Share photos of your school's display board. Accepted formats: JPG, PNG, PDF.", 'ai-awareness-day' ); ?></p>
                </div>
                <a href="<?php echo esc_url( $mailto_link ); ?>" class="resource-filter-submit display-board-submit__button">
                    <span class="display-board-submit__button-text"><?php esc_html_e( 'Submit your display board', 'ai-awareness-day' ); ?></span>
                    <span aria-hidden="true">+</span>
                </a>
            </div>
        </div>

    </div>
</section>

<!-- Activities / Time resources: By theme + By session length -->
<section id="themes" class="section <?php echo esc_attr( $text_alignment_class ); ?>">
    <div class="container">
            <div class="toolkit-explore-themes">
                <div class="fade-up">
                    <span class="section-label"><?php esc_html_e('Activities', 'ai-awareness-day'); ?></span>
                    <h2 class="section-title"><?php esc_html_e('Personalised for you', 'ai-awareness-day'); ?></h2>
                    <p class="section-desc">
                        <?php esc_html_e('Discover the thematic areas that shape AI Awareness Day activities and discussions. Filter by theme or by session length.', 'ai-awareness-day'); ?>
                    </p>
                </div>
                <?php
                // Get resources archive URL - handle permalink structure properly
                $permalink_structure = get_option('permalink_structure');
                if ($permalink_structure) {
                    // Pretty permalinks enabled - use /resources/ URL
                    $resources_url = trailingslashit(home_url()) . 'resources/';
                } else {
                    // Plain permalinks - use query string format
                    $resources_url = add_query_arg('post_type', 'resource', home_url('/'));
                }
                $theme_terms = get_terms(array('taxonomy' => 'resource_principle', 'hide_empty' => false));
                if ($theme_terms && !is_wp_error($theme_terms)):
                    $____aiad_dbg_theme = array();
                    ?>
                    <p class="explore-subheading"><?php esc_html_e('By theme', 'ai-awareness-day'); ?></p>
                    <div class="themes-links">
                        <?php foreach ($theme_terms as $term):
                            $url = add_query_arg('principle', $term->slug, $resources_url);
                            // Ensure URL uses site URL, not localhost (safety check)
                            if (strpos($url, 'localhost') !== false) {
                                $url = str_replace(parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST), parse_url(home_url(), PHP_URL_SCHEME) . '://' . parse_url(home_url(), PHP_URL_HOST), $url);
                            }
                            // Map term slug to Customizer badge setting (normalize to lowercase)
                            // Use same simple approach as display board images (which work reliably)
                            $badge_slug = strtolower($term->slug);
                            $theme_badge_id = absint(get_theme_mod('aiad_badge_' . $badge_slug, 0));
                            $theme_badge_src = $theme_badge_id ? wp_get_attachment_image_url($theme_badge_id, 'thumbnail') : '';
                            $has_theme_badge = !empty($theme_badge_src);
                            $____aiad_dbg_theme[] = array(
                                'slug'        => (string) $term->slug,
                                'badge_id'    => (int) $theme_badge_id,
                                'has_src'     => (bool) $has_theme_badge,
                                'render_mode' => $has_theme_badge ? 'img' : 'placeholder',
                            );
                            ?>
                            <a href="<?php echo esc_url($url); ?>" class="theme-link">
                                <?php if ($has_theme_badge): ?>
                                    <span class="theme-link__badge">
                                        <img src="<?php echo esc_url($theme_badge_src); ?>" alt="" aria-hidden="true"
                                            class="theme-link__badge-img" />
                                    </span>
                                <?php endif; ?>
                                <span class="theme-link__label"><?php echo esc_html($term->name); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php
                    // #region agent log
                    $____aiad_ndjson = wp_json_encode(
                        array(
                            'sessionId'    => '5ed5d5',
                            'hypothesisId' => 'H2,H5',
                            'location'     => 'section-toolkit.php:after-themes-loop',
                            'message'      => 'server theme-badge render summary',
                            'timestamp'    => (int) round( microtime( true ) * 1000 ),
                            'data'         => array(
                                'site_host'    => (string) wp_parse_url( home_url(), PHP_URL_HOST ),
                                'terms'        => $____aiad_dbg_theme,
                                'wpUsingBund'  => file_exists( get_stylesheet_directory() . '/assets/css/bundles/base.css' ),
                            ),
                        )
                    );
                    $____paths = array(
                        '/Users/m.martin/Desktop/DEMO /.cursor/debug-5ed5d5.log',
                        '/Users/m.martin/Desktop/DEMO/.cursor/debug-5ed5d5.log',
                        ( getenv( 'HOME' ) ? getenv( 'HOME' ) . '/Desktop/DEMO/.cursor/debug-5ed5d5.log' : '' ),
                    );
                    foreach ( $____paths as $____p ) {
                        if ( $____p !== '' ) {
                            @file_put_contents( $____p, $____aiad_ndjson . "\n", FILE_APPEND );
                        }
                    }
                    $____ud = wp_upload_dir();
                    if ( empty( $____ud['error'] ) && ! empty( $____ud['basedir'] ) ) {
                        @file_put_contents( trailingslashit( $____ud['basedir'] ) . 'aiad-debug-5ed5d5.ndjson', $____aiad_ndjson . "\n", FILE_APPEND );
                    }
                    // #endregion
                    ?>
                    <?php
                endif;
                $session_cards = function_exists('aiad_explore_session_cards') ? aiad_explore_session_cards() : array();
                $duration_terms = get_terms(array('taxonomy' => 'resource_duration', 'hide_empty' => false));
                $duration_slugs = array();
                if ($duration_terms && !is_wp_error($duration_terms)) {
                    $duration_slugs = wp_list_pluck($duration_terms, 'slug');
                }
                if (!empty($session_cards)):
                    ?>
                    <div class="explore-session-length-block fade-up">
                        <p class="explore-subheading"><?php esc_html_e('By session length', 'ai-awareness-day'); ?></p>
                        <div class="explore-session-cards">
                            <?php
                            foreach (array_keys($session_cards) as $slug):
                                if (!in_array($slug, $duration_slugs, true)) {
                                    continue;
                                }
                                $card = $session_cards[$slug];
                                $url = add_query_arg('duration', $slug, $resources_url);
                                if (strpos($url, 'localhost') !== false) {
                                    $url = str_replace(parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST), parse_url(home_url(), PHP_URL_SCHEME) . '://' . parse_url(home_url(), PHP_URL_HOST), $url);
                                }
                                $icon = isset($card['icon']) ? $card['icon'] : 'book';
                                $icon_bg = isset($card['icon_bg']) ? $card['icon_bg'] : '#c4b5fd';
                                $status = isset($card['status']) ? $card['status'] : '';
                                $status_live = !empty($card['status_live']);
                                $session_badge_id = absint(get_theme_mod('aiad_session_badge_' . $slug, 0));
                                $session_badge_src = $session_badge_id ? wp_get_attachment_image_url($session_badge_id, 'thumbnail') : '';
                                $has_session_badge = !empty($session_badge_src);
                                ?>
                                <a href="<?php echo esc_url($url); ?>" class="explore-session-card fade-up" style="--session-accent: <?php echo esc_attr($icon_bg); ?>">
                                    <span class="explore-session-text">
                                        <span class="explore-session-title">
                                            <span class="explore-session-title__full"><?php echo esc_html($card['title']); ?></span>
                                            <?php if ( ! empty( $card['short_title'] ) ) : ?>
                                                <span class="explore-session-title__short"><?php echo esc_html($card['short_title']); ?></span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="explore-session-desc"><?php echo esc_html($card['description']); ?></span>
                                    </span>
                                    <span class="explore-session-badge">
                                        <span class="explore-session-badge__text"><?php echo esc_html($card['badge_short']); ?></span>
                                        <?php if ($has_session_badge): ?>
                                            <img src="<?php echo esc_url($session_badge_src); ?>" alt="" class="explore-session-badge__img" aria-hidden="true" loading="lazy" onerror="this.classList.add('is-broken');" />
                                        <?php endif; ?>
                                        <span class="explore-session-badge-placeholder explore-session-badge-placeholder--fallback" aria-hidden="true"><?php echo esc_html(preg_match('/^\d+/', $card['badge_short'], $m) ? $m[0] : $card['badge_short']); ?></span>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
    </div>
</section>

<!-- Free Resources: 5-minute lesson starters only (one per theme) -->
<section id="free-resources-section" class="section <?php echo esc_attr( $text_alignment_class ); ?>">
    <div class="container">
            <!-- Free Resources block (keep id for anchors) -->
            <?php
            $free_resources = new WP_Query(array(
                'post_type' => 'resource',
                'post_status' => 'publish',
                'posts_per_page' => 3,
                'orderby' => 'menu_order title',
                'order' => 'ASC',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'resource_duration',
                        'field' => 'slug',
                        'terms' => '5-min-lesson-starters',
                    ),
                ),
            ));
            if ($free_resources->have_posts()):
                // Get resources archive URL - handle permalink structure properly
                $permalink_structure = get_option('permalink_structure');
                if ($permalink_structure) {
                    // Pretty permalinks enabled - use /resources/ URL
                    $resources_archive_url = trailingslashit(home_url()) . 'resources/';
                } else {
                    // Plain permalinks - use query string format
                    $resources_archive_url = add_query_arg('post_type', 'resource', home_url('/'));
                }
                ?>
                <div id="free-resources" class="toolkit-free-resources toolkit-free-resources--section fade-up">
                    <span class="section-label"><?php esc_html_e('Free Resources', 'ai-awareness-day'); ?></span>
                    <h2 class="section-title"><?php esc_html_e('AI Awareness Activities', 'ai-awareness-day'); ?></h2>
                    <div class="resources-grid">
                        <?php
                        while ($free_resources->have_posts()):
                            $free_resources->the_post();
                            $themes = get_the_terms(get_the_ID(), 'resource_principle');
                            $durations = get_the_terms(get_the_ID(), 'resource_duration');
                            $duration_labels = ($durations && !is_wp_error($durations) && function_exists('aiad_resource_duration_term_labels'))
                                ? aiad_resource_duration_term_labels($durations)
                                : array();
                            $theme_name    = $themes && ! is_wp_error( $themes ) ? $themes[0]->name : '';
                            $theme_slug    = in_array( strtolower( $theme_name ), array( 'safe', 'smart', 'creative', 'responsible', 'future' ), true )
                                ? strtolower( $theme_name ) : '';
                            $activity_terms = get_the_terms( get_the_ID(), 'activity_type' );
                            $format_label  = ( $activity_terms && ! is_wp_error( $activity_terms ) && ! empty( $activity_terms ) )
                                ? strtoupper( $activity_terms[0]->name ) : 'SLIDE';
                            $duration_parts = ( $durations && ! is_wp_error( $durations ) && function_exists( 'aiad_duration_badge_parts' ) )
                                ? aiad_duration_badge_parts( $durations[0] ) : null;
                            if ( $duration_parts ) {
                                $duration_str = strtoupper( $duration_parts['time'] );
                            } elseif ( ! empty( $duration_labels ) && preg_match( '/\(([^)]+)\)/', $duration_labels[0], $m ) ) {
                                $duration_str = strtoupper( trim( $m[1] ) );
                            } elseif ( ! empty( $duration_labels ) && preg_match( '/(\d+(?:[\-–]\d+)?\s*min(?:ute)?s?)/i', $duration_labels[0], $m ) ) {
                                $duration_str = strtoupper( $m[1] );
                            } else {
                                $duration_str = '';
                            }
                            $article_class = 'resource-card resource-card--pointed fade-up';
                            if ( $theme_slug ) {
                                $article_class .= ' resource-card--' . $theme_slug;
                            }
                            ?>
                            <article class="<?php echo esc_attr( $article_class ); ?>">
                                <a href="<?php the_permalink(); ?>" class="resource-card__hero" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
                                    <?php if ( has_post_thumbnail() ): ?>
                                        <?php the_post_thumbnail( 'medium_large', array( 'class' => 'resource-card__hero-img' ) ); ?>
                                    <?php else: ?>
                                        <div class="resource-card__hero-img" style="background:#111;" aria-hidden="true"></div>
                                    <?php endif; ?>

                                    <div class="resource-card__wedge" aria-hidden="true"></div>
                                    <div class="resource-card__fade"  aria-hidden="true"></div>

                                    <?php if ( $theme_name ): ?>
                                        <span class="resource-card__theme-label" aria-hidden="true"><?php echo esc_html( strtoupper( $theme_name ) ); ?></span>
                                    <?php endif; ?>

                                    <?php if ( $duration_str ): ?>
                                        <span class="resource-card__duration-label" aria-hidden="true"><?php echo esc_html( $duration_str ); ?></span>
                                    <?php endif; ?>

                                    <h3 class="resource-card__title-overlay"><?php echo esc_html( html_entity_decode( get_the_title(), ENT_QUOTES, 'UTF-8' ) ); ?></h3>
                                </a>

                                <div class="resource-card__body">
                                    <span class="resource-card__format-label"><?php echo esc_html( $format_label ); ?></span>
                                    <a href="<?php the_permalink(); ?>" class="resource-card__title-below"><?php echo esc_html( html_entity_decode( get_the_title(), ENT_QUOTES, 'UTF-8' ) ); ?></a>
                                    <?php if ( has_excerpt() ): ?>
                                        <p class="resource-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endwhile; ?>
                        <?php /* Placeholder card: mobile only, links to full resources archive */ ?>
                        <a href="<?php echo esc_url($resources_archive_url); ?>"
                            class="resource-card resource-card--placeholder free-resources-placeholder--mobile fade-up"
                            aria-label="<?php esc_attr_e('View all resources', 'ai-awareness-day'); ?>">
                            <div class="resource-card__placeholder-inner">
                                <span class="resource-card__placeholder-title"><?php esc_html_e('View all resources', 'ai-awareness-day'); ?></span>
                                <span class="resource-card__placeholder-desc"><?php esc_html_e('Browse all activities', 'ai-awareness-day'); ?></span>
                            </div>
                        </a>
                    </div>
                </div>
                <?php
                wp_reset_postdata();
            endif;
            ?>
    </div>
</section>
