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

                    <div class="dbm__header">
                        <div class="dbm__header-logo" aria-hidden="true">
                            <span class="dbm__header-logo-label"><?php esc_html_e( 'School logo', 'ai-awareness-day' ); ?></span>
                        </div>
                        <div class="dbm__header-brand">
                            <img class="dbm__header-lockup" src="<?php echo esc_url( AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-reverse.svg' ); ?>" alt="<?php esc_attr_e( 'AI Awareness Day 2027', 'ai-awareness-day' ); ?>" width="300" height="56" />
                        </div>
                    </div>

                    <div class="dbm__invitation">
                        <p class="dbm__eyebrow"><?php esc_html_e( 'Our classroom AI conversation', 'ai-awareness-day' ); ?></p>
                        <h3><?php esc_html_e( 'Big questions. Human answers.', 'ai-awareness-day' ); ?></h3>
                        <p><?php esc_html_e( 'Pick a question. Talk it through. Add your voice to the board.', 'ai-awareness-day' ); ?></p>
                        <span class="dbm__teacher-note"><?php esc_html_e( 'Teacher tip: choose one strand each week and refresh the responses together.', 'ai-awareness-day' ); ?></span>
                    </div>

                    <div class="dbm__panels">
                        <?php
                        $dbm_strands = array(
                            'safe' => array(
                                'title' => __( 'Safe', 'ai-awareness-day' ),
                                'desc'  => __( 'Would you tell an AI your secret?', 'ai-awareness-day' ),
                                'fact'  => __( 'Start with what should stay private — trust, sharing and the data AI holds about you.', 'ai-awareness-day' ),
                            ),
                            'smart' => array(
                                'title' => __( 'Smart', 'ai-awareness-day' ),
                                'desc'  => __( 'What happens when AI acts for you?', 'ai-awareness-day' ),
                                'fact'  => __( 'Decide what an AI must always ask about before it acts.', 'ai-awareness-day' ),
                            ),
                            'creative' => array(
                                'title' => __( 'Creative', 'ai-awareness-day' ),
                                'desc'  => __( 'Who really made it?', 'ai-awareness-day' ),
                                'fact'  => __( 'Own what you make with AI — authorship, attribution and honest creative work.', 'ai-awareness-day' ),
                            ),
                            'responsible' => array(
                                'title' => __( 'Responsible', 'ai-awareness-day' ),
                                'desc'  => __( 'Should AI decide?', 'ai-awareness-day' ),
                                'fact'  => __( 'Keep human judgement in consequential moments — when the output matters.', 'ai-awareness-day' ),
                            ),
                            'future' => array(
                                'title' => __( 'Future', 'ai-awareness-day' ),
                                'desc'  => __( 'What skills must stay human?', 'ai-awareness-day' ),
                                'fact'  => __( 'Name the skills worth keeping human — and practise them on purpose.', 'ai-awareness-day' ),
                            ),
                        );
                        foreach ( $dbm_strands as $slug => $strand ) :
                            ?>
                        <div class="dbm__panel dbm__panel--<?php echo esc_attr( $slug ); ?>">
                            <div class="dbm__panel-header">
                                <span class="dbm__panel-icon" aria-hidden="true"><?php echo aiad_strand_icon_svg( $slug, 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                                <h3 class="dbm__panel-title"><?php echo esc_html( $strand['title'] ); ?></h3>
                            </div>
                            <div class="dbm__panel-body">
                                <p class="dbm__panel-desc"><?php echo esc_html( $strand['desc'] ); ?></p>
                                <p class="dbm__panel-fact"><?php echo esc_html( $strand['fact'] ); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <div class="dbm__panel dbm__panel--qr">
                            <div class="dbm__panel-header">
                                <h3 class="dbm__panel-title"><?php esc_html_e( 'QR challenges', 'ai-awareness-day' ); ?></h3>
                            </div>
                            <div class="dbm__panel-body">
                                <p class="dbm__panel-desc"><?php esc_html_e( 'Connect the conversation to your school’s AI policy and classroom guidelines.', 'ai-awareness-day' ); ?></p>
                                <p class="dbm__panel-fact"><?php esc_html_e( 'Teacher setup: add your own QR codes in the spaces below.', 'ai-awareness-day' ); ?></p>
                                <div class="dbm__qr-grid">
                                    <div class="dbm__qr-item">
                                        <span class="dbm__qr-mark" aria-hidden="true"></span>
                                        <span><?php esc_html_e( 'School policy', 'ai-awareness-day' ); ?></span>
                                    </div>
                                    <div class="dbm__qr-item">
                                        <span class="dbm__qr-mark" aria-hidden="true"></span>
                                        <span><?php esc_html_e( 'AI guidelines', 'ai-awareness-day' ); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dbm__row">
                        <div class="dbm__panel dbm__panel--questions">
                            <div class="dbm__panel-header">
                                <h3 class="dbm__panel-title"><?php esc_html_e( 'This week: pause, pair, share', 'ai-awareness-day' ); ?></h3>
                            </div>
                            <div class="dbm__panel-body">
                                <p class="dbm__panel-desc"><?php esc_html_e( 'Choose one question. Compare answers with a partner. What changed your mind?', 'ai-awareness-day' ); ?></p>
                                <ul class="dbm__questions">
                                    <li><?php esc_html_e( 'Would you tell an AI your secret?', 'ai-awareness-day' ); ?></li>
                                    <li><?php esc_html_e( 'What happens when AI acts for you?', 'ai-awareness-day' ); ?></li>
                                    <li><?php esc_html_e( 'Who really made it?', 'ai-awareness-day' ); ?></li>
                                </ul>
                            </div>
                        </div>

                        <div class="dbm__panel dbm__panel--responses">
                            <div class="dbm__panel-header">
                                <h3 class="dbm__panel-title"><?php esc_html_e( 'Your voice belongs here', 'ai-awareness-day' ); ?></h3>
                            </div>
                            <div class="dbm__panel-body">
                                <p class="dbm__panel-fact"><?php esc_html_e( 'Example responses — replace these with your class’s ideas.', 'ai-awareness-day' ); ?></p>
                                <p class="dbm__panel-quote"><?php esc_html_e( '“I’d tell a person first.”', 'ai-awareness-day' ); ?></p>
                                <p class="dbm__panel-quote"><?php esc_html_e( '“It can draft — I decide.”', 'ai-awareness-day' ); ?></p>
                                <p class="dbm__panel-quote"><?php esc_html_e( '“I made it. AI helped.”', 'ai-awareness-day' ); ?></p>
                                <p class="dbm__sticky-hint"><?php esc_html_e( 'Leave space for sticky notes here.', 'ai-awareness-day' ); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="dbm__panel dbm__panel--leaders dbm__panel--wide">
                        <div class="dbm__panel-header">
                            <h3 class="dbm__panel-title"><?php esc_html_e( 'AI leaders & innovators', 'ai-awareness-day' ); ?></h3>
                        </div>
                        <div class="dbm__panel-body">
                            <div class="dbm__gallery">
                                <div class="dbm__gallery-item"><span class="dbm__gallery-slot" aria-hidden="true"></span><p><?php esc_html_e( 'Add photo', 'ai-awareness-day' ); ?></p></div>
                                <div class="dbm__gallery-item"><span class="dbm__gallery-slot" aria-hidden="true"></span><p><?php esc_html_e( 'Add photo', 'ai-awareness-day' ); ?></p></div>
                                <div class="dbm__gallery-item"><span class="dbm__gallery-slot" aria-hidden="true"></span><p><?php esc_html_e( 'Add photo', 'ai-awareness-day' ); ?></p></div>
                            </div>
                            <p class="dbm__gallery-hint"><?php esc_html_e( 'Challenge: find three living people working in AI and add them here.', 'ai-awareness-day' ); ?></p>
                        </div>
                    </div>

                    <div class="dbm__panel dbm__panel--spotlight dbm__panel--wide">
                        <div class="dbm__panel-header">
                            <h3 class="dbm__panel-title"><?php esc_html_e( 'Student spotlight', 'ai-awareness-day' ); ?></h3>
                        </div>
                        <div class="dbm__panel-body">
                            <div class="dbm__spotlight">
                                <div class="dbm__spotlight-item">
                                    <div class="dbm__spotlight-avatar" aria-hidden="true"></div>
                                    <div>
                                        <p class="dbm__spotlight-name"><?php esc_html_e( 'Student name', 'ai-awareness-day' ); ?></p>
                                        <p class="dbm__spotlight-work"><?php esc_html_e( 'Add student work or project here', 'ai-awareness-day' ); ?></p>
                                    </div>
                                </div>
                                <div class="dbm__spotlight-item">
                                    <div class="dbm__spotlight-avatar" aria-hidden="true"></div>
                                    <div>
                                        <p class="dbm__spotlight-name"><?php esc_html_e( 'Student name', 'ai-awareness-day' ); ?></p>
                                        <p class="dbm__spotlight-work"><?php esc_html_e( 'Add student work or project here', 'ai-awareness-day' ); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
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

<?php get_template_part( 'template-parts/front-page/part', 'benchmark-promo' ); ?>

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
                    ?>
                    <p class="explore-subheading"><?php esc_html_e('By theme', 'ai-awareness-day'); ?></p>
                    <div class="themes-links">
                        <?php foreach ($theme_terms as $term):
                            $url = add_query_arg('principle', $term->slug, $resources_url);
                            // Ensure URL uses site URL, not localhost (safety check)
                            if (strpos($url, 'localhost') !== false) {
                                $url = str_replace(parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST), parse_url(home_url(), PHP_URL_SCHEME) . '://' . parse_url(home_url(), PHP_URL_HOST), $url);
                            }
                            $theme_badge_src = function_exists( 'aiad_theme_link_badge_src' )
                                ? aiad_theme_link_badge_src( $term->slug )
                                : '';
                            ?>
                            <a href="<?php echo esc_url($url); ?>" class="theme-link theme-link--<?php echo esc_attr( strtolower( $term->slug ) ); ?>">
                                <?php if ( $theme_badge_src ) : ?>
                                    <span class="theme-link__badge">
                                        <img src="<?php echo esc_url( $theme_badge_src ); ?>" alt="" aria-hidden="true"
                                            class="theme-link__badge-img" width="48" height="48" />
                                    </span>
                                <?php endif; ?>
                                <span class="theme-link__label"><?php echo esc_html($term->name); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
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
                            class="resource-card resource-card--placeholder resource-card--placeholder-pointed free-resources-placeholder--mobile fade-up"
                            aria-label="<?php esc_attr_e('View all resources', 'ai-awareness-day'); ?>">
                            <span class="resource-card__placeholder-hero" aria-hidden="true">
                                <span class="resource-card__placeholder-title"><?php esc_html_e('View all resources', 'ai-awareness-day'); ?></span>
                            </span>
                            <span class="resource-card__placeholder-body">
                                <span class="resource-card__format-label"><?php esc_html_e('Archive', 'ai-awareness-day'); ?></span>
                                <span class="resource-card__placeholder-title-below"><?php esc_html_e('View all resources', 'ai-awareness-day'); ?></span>
                                <span class="resource-card__placeholder-desc"><?php esc_html_e('Browse all activities', 'ai-awareness-day'); ?></span>
                            </span>
                        </a>
                    </div>
                </div>
                <?php
                wp_reset_postdata();
            endif;
            ?>
    </div>
</section>
