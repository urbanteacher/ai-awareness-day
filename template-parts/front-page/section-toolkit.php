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
                <?php
                /*
                 * The board as one graphic. Built by scripts/build-display-board.py, so it
                 * keeps a fixed composition: framed, pinned, the lockup in the middle. The
                 * HTML mockup it replaces reflowed into a grid of web cards. The
                 * "How to create" tab carries the same zones as text.
                 */
                $board_svg = AIAD_URI . '/assets/images/display-board/aiad27-display-board.svg';
                $board_png = AIAD_URI . '/assets/images/display-board/aiad27-display-board.png';
                ?>
                <figure class="dbm-graphic">
                    <a class="dbm-graphic__zoom" href="<?php echo esc_url( $board_svg ); ?>" target="_blank" rel="noopener">
                        <img src="<?php echo esc_url( $board_svg ); ?>" width="2400" height="1600" loading="lazy" decoding="async"
                            alt="<?php esc_attr_e( 'Example AI Awareness Day 2027 display board. In the centre: your school logo, the date, Friday 4 June 2027, and the title with the line Your AI. Your choices. Around it, one card for each strand with its question: Safe, would you tell an AI your secret; Smart, what happens when AI acts for you; Creative, who really made it; Responsible, should AI decide; Future, what skills must stay human. Between them: this week’s questions, student responses on sticky notes, QR codes for resources and your school’s AI policy, photos of AI leaders and innovators, and a student spotlight.', 'ai-awareness-day' ); ?>" />
                        <span class="screen-reader-text"><?php esc_html_e( '(opens full size in a new tab)', 'ai-awareness-day' ); ?></span>
                    </a>
                    <figcaption class="dbm-graphic__caption">
                        <p><?php esc_html_e( 'Select the board to see it full size.', 'ai-awareness-day' ); ?></p>
                        <span class="dbm-graphic__downloads">
                            <a href="<?php echo esc_url( $board_png ); ?>" download="aiad27-display-board.png"><?php esc_html_e( 'Download for print (PNG)', 'ai-awareness-day' ); ?></a>
                            <a href="<?php echo esc_url( $board_svg ); ?>" download="aiad27-display-board.svg"><?php esc_html_e( 'Download (SVG)', 'ai-awareness-day' ); ?></a>
                        </span>
                    </figcaption>
                </figure>
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

<?php
/*
 * What the audit earns you, immediately above the audit's own promo. Self
 * contained — see inc/certificate-showcase.php, or paste
 * [aiad_certificate_showcase] anywhere else you want it.
 */
if ( function_exists( 'aiad_certificate_showcase' ) ) {
	echo aiad_certificate_showcase(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- template part escapes its own output.
}
?>

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
                    <div class="resource-tiles">
                        <?php
                        while ( $free_resources->have_posts() ) :
                            $free_resources->the_post();
                            get_template_part( 'template-parts/components/resource-tile', null, array() );
                        endwhile;
                        ?>
                    </div>
                    <a class="resource-tiles__more" href="<?php echo esc_url( $resources_archive_url ); ?>"><?php esc_html_e( 'View all resources', 'ai-awareness-day' ); ?> <span aria-hidden="true">&rarr;</span></a>
                </div>
                <?php
                wp_reset_postdata();
            endif;
            ?>
    </div>
</section>
