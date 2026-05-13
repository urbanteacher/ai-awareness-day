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
                    aria-controls="dbt-panel-real"><?php esc_html_e( 'Real example', 'ai-awareness-day' ); ?></button>
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
                <div class="display-board-mockup" aria-label="<?php esc_attr_e( 'Display board layout guide', 'ai-awareness-day' ); ?>">
                    <div class="display-board-mockup__header">
                        <span class="display-board-mockup__logo"><?php esc_html_e( 'Logo', 'ai-awareness-day' ); ?></span>
                        <div>
                            <strong><?php esc_html_e( 'AI Awareness Day 2026', 'ai-awareness-day' ); ?></strong>
                            <br><span class="display-board-mockup__tagline"><?php esc_html_e( 'Know it, Question it, Use it Wisely', 'ai-awareness-day' ); ?></span>
                        </div>
                    </div>
                    <div class="display-board-mockup__principles">
                        <?php
                        $principle_slugs  = array( 'safe', 'smart', 'creative', 'responsible', 'future' );
                        $principle_labels = array(
                            'safe'        => __( 'Safe', 'ai-awareness-day' ),
                            'smart'       => __( 'Smart', 'ai-awareness-day' ),
                            'creative'    => __( 'Creative', 'ai-awareness-day' ),
                            'responsible' => __( 'Responsible', 'ai-awareness-day' ),
                            'future'      => __( 'Future', 'ai-awareness-day' ),
                        );
                        foreach ( $principle_slugs as $slug ) :
                            $label = isset( $principle_labels[ $slug ] ) ? $principle_labels[ $slug ] : $slug;
                        ?>
                        <div class="display-board-mockup__block display-board-mockup__block--<?php echo esc_attr( $slug ); ?>">
                            <span class="display-board-mockup__block-title"><?php echo esc_html( $label ); ?></span>
                            <span class="display-board-mockup__block-hint"><?php esc_html_e( 'Key message + tips', 'ai-awareness-day' ); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="display-board-mockup__qr">
                        <span class="display-board-mockup__block-title"><?php esc_html_e( 'QR challenges', 'ai-awareness-day' ); ?></span>
                        <span class="display-board-mockup__block-hint"><?php esc_html_e( 'Scan & investigate: School policy, AI guidelines', 'ai-awareness-day' ); ?></span>
                    </div>
                    <div class="display-board-mockup__row">
                        <div class="display-board-mockup__block display-board-mockup__block--questions">
                            <span class="display-board-mockup__block-title"><?php esc_html_e( "This week's questions", 'ai-awareness-day' ); ?></span>
                            <span class="display-board-mockup__block-hint"><?php esc_html_e( 'e.g. How can we ensure AI tools are fair?', 'ai-awareness-day' ); ?></span>
                        </div>
                        <div class="display-board-mockup__block display-board-mockup__block--responses">
                            <span class="display-board-mockup__block-title"><?php esc_html_e( 'Student responses', 'ai-awareness-day' ); ?></span>
                            <span class="display-board-mockup__block-hint"><?php esc_html_e( 'Sticky notes / written answers here', 'ai-awareness-day' ); ?></span>
                        </div>
                    </div>
                    <div class="display-board-mockup__row">
                        <div class="display-board-mockup__block display-board-mockup__block--spotlight">
                            <span class="display-board-mockup__block-title"><?php esc_html_e( 'AI leaders & innovators', 'ai-awareness-day' ); ?></span>
                            <span class="display-board-mockup__block-hint"><?php esc_html_e( 'Photos + names', 'ai-awareness-day' ); ?></span>
                        </div>
                        <div class="display-board-mockup__block display-board-mockup__block--spotlight">
                            <span class="display-board-mockup__block-title"><?php esc_html_e( 'Student spotlight', 'ai-awareness-day' ); ?></span>
                            <span class="display-board-mockup__block-hint"><?php esc_html_e( 'Feature student work or projects', 'ai-awareness-day' ); ?></span>
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
                            <a href="<?php echo esc_url($url); ?>" class="theme-link fade-up">
                                <span class="theme-link__badge">
                                    <?php if ($has_theme_badge): ?>
                                        <img src="<?php echo esc_url($theme_badge_src); ?>" alt="" aria-hidden="true"
                                            class="theme-link__badge-img" onerror="this.classList.add('is-broken');" />
                                    <?php else: ?>
                                        <span class="theme-link__badge-placeholder" aria-hidden="true"><?php echo esc_html(mb_substr($term->name, 0, 1)); ?></span>
                                    <?php endif; ?>
                                </span>
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
                        '/Users/m.martin/Desktop/DEMO/.cursor/debug-5ed5d5.log',
                        ( getenv( 'HOME' ) ? getenv( 'HOME' ) . '/Desktop/DEMO/.cursor/debug-5ed5d5.log' : '' ),
                    );
                    foreach ( $____paths as $____p ) {
                        if ( $____p !== '' ) {
                            @file_put_contents( $____p, $____aiad_ndjson . "\n", FILE_APPEND );
                        }
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
                            $duration_str  = $duration_parts ? strtoupper( $duration_parts['time'] ) : ( ! empty( $duration_labels ) ? strtoupper( $duration_labels[0] ) : '' );
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

                                    <span class="resource-card__format" aria-hidden="true"><?php echo esc_html( $format_label ); ?></span>

                                    <?php if ( $duration_str ): ?>
                                        <span class="resource-card__duration-label" aria-hidden="true"><?php echo esc_html( $duration_str ); ?></span>
                                    <?php endif; ?>

                                    <h3 class="resource-card__title-overlay"><?php echo esc_html( get_the_title() ); ?></h3>
                                </a>

                                <div class="resource-card__body">
                                    <span class="resource-card__format-label"><?php echo esc_html( $format_label ); ?></span>
                                    <a href="<?php the_permalink(); ?>" class="resource-card__title-below"><?php echo esc_html( get_the_title() ); ?></a>
                                    <?php if ( has_excerpt() ): ?>
                                        <p class="resource-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
                                    <?php endif; ?>
                                    <p class="resource-card__action">
                                        <a href="<?php the_permalink(); ?>" class="resource-card__link"><?php esc_html_e( 'View resource', 'ai-awareness-day' ); ?> →</a>
                                    </p>
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
