<?php
/**
 * Front page section: Toolkit
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}
?>
<section class="section <?php echo esc_attr( $text_alignment_class ); ?>" id="toolkit">
    <div class="container">
                <div class="fade-up">
                    <span class="section-label"><?php esc_html_e('Toolkit', 'ai-awareness-day'); ?></span>
                    <h2 class="section-title"><?php esc_html_e('Plug-and-Play Toolkit', 'ai-awareness-day'); ?></h2>
                    <p class="section-desc">
                        <?php esc_html_e('Everything your school needs to participate — ready to use, easy to adapt.', 'ai-awareness-day'); ?>
                    </p>
                </div>

                <div class="toolkit-grid">
                    <?php
                    $implementation_guide_url = esc_url_raw(get_theme_mod('aiad_implementation_guide_url', ''));
                    $sample_letters_url = esc_url_raw(get_theme_mod('aiad_sample_letters_url', 'https://beehiiv-publication-files.s3.amazonaws.com/uploads/downloadables/54845583-4adb-4ee9-8457-f9f4065c7216/a21336a3-e31b-4383-a127-6aada6856882/SLT%20APPROVAL.pdf?X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=AKIAQCMHTQSE2JGAGXHJ%2F20260219%2Fus-east-1%2Fs3%2Faws4_request&X-Amz-Date=20260219T134149Z&X-Amz-Expires=604800&X-Amz-SignedHeaders=host&X-Amz-Signature=63e9e703294592ac0831c1514a8cb35998b38153e36dd02ad109ab57226d2625'));
                    $newsletter_url = esc_url_raw(get_theme_mod('aiad_newsletter_url', 'https://aiawarenessday.beehiiv.com/p/ai-awareness-day-launched'));
                    $toolkit_items = array(
                        array(
                            'title' => __('Implementation Guide', 'ai-awareness-day'),
                            'desc' => __('Step-by-step instructions to run AI Awareness Day in your school.', 'ai-awareness-day'),
                            'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline>',
                            'url' => $implementation_guide_url,
                        ),
                        array(
                            'title' => __('Sample Letters & Communications', 'ai-awareness-day'),
                            'desc' => __('Pre-written letters for parents, governors, and local press.', 'ai-awareness-day'),
                            'icon' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline>',
                            'url' => $sample_letters_url,
                        ),
                        array(
                            'title' => __('Latest Newsletter', 'ai-awareness-day'),
                            'desc' => __('Click to read the latest AI Awareness Day newsletter and updates.', 'ai-awareness-day'),
                            'icon' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline>',
                            'url' => $newsletter_url,
                        ),
                    );

                    foreach ($toolkit_items as $index => $item):
                        $card_url = !empty($item['url']) ? $item['url'] : '';
                        $is_link = $card_url !== '';
                        ?>
                        <?php
                        $toolkit_image_id = absint( get_theme_mod( 'aiad_toolkit_image_' . ( $index + 1 ), 0 ) );
                        $toolkit_image_url = $toolkit_image_id ? wp_get_attachment_image_url( $toolkit_image_id, 'medium_large' ) : '';
                        ?>
                        <?php if ($is_link): ?>
                            <a href="<?php echo esc_url($card_url); ?>"
                                class="toolkit-card toolkit-card--link fade-up stagger-<?php echo $index + 1; ?>" target="_blank"
                                rel="noopener noreferrer">
                            <?php else: ?>
                                <div class="toolkit-card fade-up stagger-<?php echo $index + 1; ?>">
                                <?php endif; ?>
                                <div class="toolkit-card__image-wrap">
                                    <?php if ( $toolkit_image_url ) : ?>
                                        <img src="<?php echo esc_url( $toolkit_image_url ); ?>" alt="" class="toolkit-card__image" loading="lazy" />
                                    <?php else : ?>
                                        <div class="toolkit-card__image-placeholder" aria-hidden="true">
                                            <span class="toolkit-card__image-placeholder-text"><?php esc_html_e( 'Add image', 'ai-awareness-day' ); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="toolkit-header">
                                    <h3><?php echo esc_html($item['title']); ?></h3>
                                </div>
                                <p class="section-desc"><?php echo esc_html($item['desc']); ?></p>
                                <?php if ($is_link): ?>
                            </a>
                        <?php else: ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
    </div>
</section>

<!-- Display board: separate section -->
<section id="display-board" class="section <?php echo esc_attr( $text_alignment_class ); ?>">
    <div class="container">
            <div class="toolkit-display-board fade-up">
                <span class="section-label"><?php esc_html_e('Display board', 'ai-awareness-day'); ?></span>
                <h2 class="section-title">
                    <?php esc_html_e('Create a display board for your school', 'ai-awareness-day'); ?>
                </h2>
                <p class="section-desc">
                    <?php esc_html_e('Use the layout below as a guide to build a physical display in your school or staff room. Toggle between blueprint and a real example, then follow the steps.', 'ai-awareness-day'); ?>
                </p>

                <?php
                $display_img_1_id = absint(get_theme_mod('aiad_display_board_image_1', 0));
                $display_img_1_url = $display_img_1_id ? wp_get_attachment_image_url($display_img_1_id, 'full') : '';
                ?>
                <div class="display-board-preview js-display-board-preview<?php echo $display_img_1_url ? ' display-board-preview--has-real' : ''; ?>"
                    data-view="blueprint">
                    <div class="display-board-flip-toggle" role="tablist"
                        aria-label="<?php esc_attr_e('View blueprint or real example', 'ai-awareness-day'); ?>">
                        <button type="button" class="display-board-flip-btn is-active" role="tab" aria-selected="true"
                            aria-controls="display-board-blueprint"
                            id="tab-blueprint"><?php esc_html_e('Blueprint', 'ai-awareness-day'); ?></button>
                        <button type="button"
                            class="display-board-flip-btn<?php echo $display_img_1_url ? '' : ' display-board-flip-btn--disabled'; ?>"
                            role="tab" aria-selected="false" aria-controls="display-board-real" id="tab-real" <?php echo $display_img_1_url ? '' : ' disabled aria-disabled="true"'; ?>><?php esc_html_e('Real example', 'ai-awareness-day'); ?></button>
                    </div>
                    <div class="display-board-preview__view display-board-preview__view--blueprint"
                        id="display-board-blueprint" role="tabpanel" aria-labelledby="tab-blueprint">
                        <div class="display-board-mockup"
                            aria-label="<?php esc_attr_e('Display board layout guide', 'ai-awareness-day'); ?>">
                            <div class="display-board-mockup__header">
                                <span
                                    class="display-board-mockup__logo"><?php esc_html_e('Logo', 'ai-awareness-day'); ?></span>
                                <div>
                                    <strong><?php esc_html_e('AI Awareness Day 2026', 'ai-awareness-day'); ?></strong>
                                    <br><span
                                        class="display-board-mockup__tagline"><?php esc_html_e('Know it, Question it, Use it Wisely', 'ai-awareness-day'); ?></span>
                                </div>
                            </div>
                            <div class="display-board-mockup__principles">
                                <?php
                                $principle_slugs = array('safe', 'smart', 'creative', 'responsible', 'future');
                                $principle_labels = array(
                                    'safe' => __('Be Safe', 'ai-awareness-day'),
                                    'smart' => __('Be Smart', 'ai-awareness-day'),
                                    'creative' => __('Be Creative', 'ai-awareness-day'),
                                    'responsible' => __('Be Responsible', 'ai-awareness-day'),
                                    'future' => __('Be Future', 'ai-awareness-day'),
                                );
                                foreach ($principle_slugs as $slug):
                                    $label = isset($principle_labels[$slug]) ? $principle_labels[$slug] : $slug;
                                    ?>
                                    <div
                                        class="display-board-mockup__block display-board-mockup__block--<?php echo esc_attr($slug); ?>">
                                        <span class="display-board-mockup__block-title"><?php echo esc_html($label); ?></span>
                                        <span
                                            class="display-board-mockup__block-hint"><?php esc_html_e('Key message + tips', 'ai-awareness-day'); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="display-board-mockup__qr">
                                <span
                                    class="display-board-mockup__block-title"><?php esc_html_e('QR challenges', 'ai-awareness-day'); ?></span>
                                <span
                                    class="display-board-mockup__block-hint"><?php esc_html_e('Scan & investigate: School policy, AI guidelines', 'ai-awareness-day'); ?></span>
                            </div>
                            <div class="display-board-mockup__row">
                                <div class="display-board-mockup__block display-board-mockup__block--questions">
                                    <span
                                        class="display-board-mockup__block-title"><?php esc_html_e("This week's questions", 'ai-awareness-day'); ?></span>
                                    <span
                                        class="display-board-mockup__block-hint"><?php esc_html_e('e.g. How can we ensure AI tools are fair?', 'ai-awareness-day'); ?></span>
                                </div>
                                <div class="display-board-mockup__block display-board-mockup__block--responses">
                                    <span
                                        class="display-board-mockup__block-title"><?php esc_html_e('Student responses', 'ai-awareness-day'); ?></span>
                                    <span
                                        class="display-board-mockup__block-hint"><?php esc_html_e('Sticky notes / written answers here', 'ai-awareness-day'); ?></span>
                                </div>
                            </div>
                            <div class="display-board-mockup__row">
                                <div class="display-board-mockup__block display-board-mockup__block--spotlight">
                                    <span
                                        class="display-board-mockup__block-title"><?php esc_html_e('AI leaders & innovators', 'ai-awareness-day'); ?></span>
                                    <span
                                        class="display-board-mockup__block-hint"><?php esc_html_e('Photos + names', 'ai-awareness-day'); ?></span>
                                </div>
                                <div class="display-board-mockup__block display-board-mockup__block--spotlight">
                                    <span
                                        class="display-board-mockup__block-title"><?php esc_html_e('Student spotlight', 'ai-awareness-day'); ?></span>
                                    <span
                                        class="display-board-mockup__block-hint"><?php esc_html_e('Feature student work or projects', 'ai-awareness-day'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="display-board-preview__view display-board-preview__view--real" id="display-board-real"
                        role="tabpanel" aria-labelledby="tab-real" <?php echo $display_img_1_url ? '' : ' hidden'; ?>>
                        <?php if ($display_img_1_url): ?>
                            <div class="display-board-real">
                                <img src="<?php echo esc_url($display_img_1_url); ?>"
                                    alt="<?php esc_attr_e('Example display board', 'ai-awareness-day'); ?>" loading="lazy" />
                            </div>
                        <?php else: ?>
                            <div class="display-board-real" style="padding: 2rem; text-align: center; color: var(--gray-500);">
                                <p><?php esc_html_e('No real example available. Upload an image in the Customizer to see it here.', 'ai-awareness-day'); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="display-board-steps-wrapper">
                <div class="display-board-steps">
                    <button type="button" class="display-board-steps__toggle" aria-expanded="false"
                        aria-controls="display-board-steps-list">
                        <span
                            class="display-board-steps__title"><?php esc_html_e('How to create yours', 'ai-awareness-day'); ?></span>
                        <span class="display-board-steps__icon" aria-hidden="true">+</span>
                    </button>
                    <div class="container">
                        <ol class="display-board-steps__list" id="display-board-steps-list">
                            <li class="section-desc">
                                <?php esc_html_e('Select a prominent wall, noticeboard, or display area in your school or staff room.', 'ai-awareness-day'); ?>
                            </li>
                            <li class="section-desc">
                                <?php esc_html_e('Follow the blueprint layout: create five principle panels (Safe, Smart, Creative, Responsible, Future) each with a key message and practical tips.', 'ai-awareness-day'); ?>
                            </li>
                            <li class="section-desc">
                                <?php esc_html_e('QR challenges: Set up QR codes for students to scan & investigate. Link to your school\'s AI policy and our AI guidelines or activities.', 'ai-awareness-day'); ?>
                            </li>
                            <li class="section-desc">
                                <?php esc_html_e('Add interactive elements: Include facts, tips, or QR codes linking to games and quizzes using our interactive resources.', 'ai-awareness-day'); ?>
                            </li>
                            <li class="section-desc">
                                <?php esc_html_e("This week's questions: Add thought-provoking questions like \"How can we ensure AI tools are fair?\" with space for student responses.", 'ai-awareness-day'); ?>
                            </li>
                            <li class="section-desc">
                                <?php esc_html_e('Student responses: Provide space for sticky notes or written answers where students can share their thoughts and ideas.', 'ai-awareness-day'); ?>
                            </li>
                            <li class="section-desc">
                                <?php esc_html_e('AI leaders & innovators: Include photos and names of people working in AI.', 'ai-awareness-day'); ?>
                            </li>
                            <li class="section-desc">
                                <?php esc_html_e('Set them a challenge: Ask students to find 3 living people working in AI and add their discoveries to the display.', 'ai-awareness-day'); ?>
                            </li>
                            <li class="section-desc">
                                <?php esc_html_e('Student spotlight: Feature student work or projects to showcase pupil achievements and creativity.', 'ai-awareness-day'); ?>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="container">
                <div class="display-board-submit fade-up">
                    <div class="display-board-submit__content">
                        <div class="display-board-submit__text">
                            <h3 class="display-board-steps__title">
                                <?php esc_html_e('Submit your display board', 'ai-awareness-day'); ?>
                            </h3>
                            <p class="section-desc">
                                <?php esc_html_e('Share photos of your school\'s display board. Accepted formats: JPG, PNG, PDF.', 'ai-awareness-day'); ?>
                            </p>
                        </div>
                        <?php
                        $contact_email = get_theme_mod('aiad_contact_email', get_option('admin_email'));
                        $email_subject = rawurlencode('Our Schools Display');
                        $email_body = rawurlencode('Hello,\n\nPlease find attached photos of our school\'s AI Awareness Day display board.\n\nThank you!');
                        $mailto_link = 'mailto:' . esc_attr($contact_email) . '?subject=' . $email_subject . '&body=' . $email_body;
                        ?>
                        <a href="<?php echo esc_url($mailto_link); ?>"
                            class="resource-filter-submit display-board-submit__button">
                            <?php esc_html_e('Submit your display board design', 'ai-awareness-day'); ?>
                        </a>
                    </div>
                </div>

                <?php
                $display_img_2_id = absint(get_theme_mod('aiad_display_board_image_2', 0));
                $display_img_3_id = absint(get_theme_mod('aiad_display_board_image_3', 0));
                $display_img_2_url = $display_img_2_id ? wp_get_attachment_image_url($display_img_2_id, 'full') : '';
                $display_img_3_url = $display_img_3_id ? wp_get_attachment_image_url($display_img_3_id, 'full') : '';
                if ($display_img_2_url || $display_img_3_url):
                    ?>
                    <div class="display-board-examples" hidden>
                        <h3 class="display-board-examples__title">
                            <?php esc_html_e('More example display boards', 'ai-awareness-day'); ?>
                        </h3>
                        <div class="display-board-examples__grid">
                            <?php if ($display_img_2_url): ?>
                                <figure class="display-board-examples__item">
                                    <img src="<?php echo esc_url($display_img_2_url); ?>"
                                        alt="<?php esc_attr_e('Example display board 2', 'ai-awareness-day'); ?>" loading="lazy" />
                                </figure>
                            <?php endif; ?>
                            <?php if ($display_img_3_url): ?>
                                <figure class="display-board-examples__item">
                                    <img src="<?php echo esc_url($display_img_3_url); ?>"
                                        alt="<?php esc_attr_e('Example display board 3', 'ai-awareness-day'); ?>" loading="lazy" />
                                </figure>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
    </div>
</section>

<!-- Activities / Time resources: By theme + By session length -->
<section id="themes" class="section <?php echo esc_attr( $text_alignment_class ); ?>">
    <div class="container">
            <div class="toolkit-explore-themes">
                <div class="fade-up">
                    <span class="section-label"><?php esc_html_e('Activities', 'ai-awareness-day'); ?></span>
                    <h2 class="section-title"><?php esc_html_e('Personalised to you', 'ai-awareness-day'); ?></h2>
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
                            // Map term slug to Customizer badge setting (normalize to lowercase)
                            // Use same simple approach as display board images (which work reliably)
                            $badge_slug = strtolower($term->slug);
                            $theme_badge_id = absint(get_theme_mod('aiad_badge_' . $badge_slug, 0));
                            $theme_badge_src = $theme_badge_id ? wp_get_attachment_image_url($theme_badge_id, 'thumbnail') : '';
                            $has_theme_badge = !empty($theme_badge_src);
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
                                        <span class="explore-session-title"><?php echo esc_html($card['title']); ?></span>
                                        <?php if ($status): ?>
                                            <span class="explore-session-status <?php echo $status_live ? 'explore-session-status--live' : ''; ?>"><?php echo esc_html($status); ?></span>
                                        <?php endif; ?>
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
                <div id="free-resources" class="toolkit-free-resources fade-up"
                    style="margin-top: 3rem; padding-top: 2.5rem; border-top: 1px solid var(--gray-200);">
                    <span class="section-label"><?php esc_html_e('Free Resources', 'ai-awareness-day'); ?></span>
                    <h2 class="section-title"><?php esc_html_e('AI Awareness Activities', 'ai-awareness-day'); ?></h2>
                    <div class="resources-grid" style="margin-top: 2rem;">
                        <?php
                        while ($free_resources->have_posts()):
                            $free_resources->the_post();
                            $types = get_the_terms(get_the_ID(), 'resource_type');
                            $themes = get_the_terms(get_the_ID(), 'resource_principle');
                            $durations = get_the_terms(get_the_ID(), 'resource_duration');
                            $type_name = $types && !is_wp_error($types) ? $types[0]->name : '';
                            $theme_name = $themes && !is_wp_error($themes) ? $themes[0]->name : '';
                            $duration_name = '';
                            if ($durations && !is_wp_error($durations) && function_exists('aiad_duration_badge_label')) {
                                $duration_name = aiad_duration_badge_label($durations[0]);
                            } elseif ($durations && !is_wp_error($durations)) {
                                $duration_name = $durations[0]->name;
                            }
                            $duration_slug = $durations && !is_wp_error($durations) ? $durations[0]->slug : '';
                            $session_cards = function_exists('aiad_explore_session_cards') ? aiad_explore_session_cards() : array();
                            $placeholder_text = __('Starter Slide', 'ai-awareness-day');
                            ?>
                            <article class="resource-card resource-card--download fade-up">
                                <a href="<?php the_permalink(); ?>" class="resource-card__image-link">
                                    <?php if (has_post_thumbnail()): ?>
                                        <?php the_post_thumbnail('medium_large', array('class' => 'resource-card__image')); ?>
                                    <?php else: ?>
                                        <div class="resource-card__image-placeholder" aria-hidden="true">
                                            <span
                                                class="resource-card__image-placeholder-text"><?php echo esc_html($placeholder_text); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php
                                    $pill_duration = ($duration_slug && isset($session_cards[$duration_slug]['badge_short']))
                                        ? ucwords($session_cards[$duration_slug]['badge_short'])
                                        : ($duration_name ? $duration_name : '');
                                    $has_overlay = $pill_duration || $theme_name;
                                    ?>
                                    <?php if ($has_overlay): ?>
                                        <div class="resource-card__image-overlay" aria-hidden="true">
                                            <div class="resource-card__image-top">
                                                <?php if ($theme_name): ?>
                                                    <?php
                                                    $theme_slug = strtolower($theme_name);
                                                    $pill_class = 'resource-card__pill--theme';
                                                    if (in_array($theme_slug, array('safe', 'smart', 'creative', 'responsible', 'future'), true)) {
                                                        $pill_class .= ' resource-card__pill--' . $theme_slug;
                                                    }
                                                    ?>
                                                    <span
                                                        class="resource-card__pill <?php echo esc_attr($pill_class); ?>"><?php echo esc_html($theme_name); ?></span>
                                                <?php endif; ?>
                                                <?php if ($pill_duration): ?>
                                                    <span
                                                        class="resource-card__pill resource-card__pill--type"><?php echo esc_html($pill_duration); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </a>
                                <div class="resource-card__body">
                                    <h2 class="resource-card__title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h2>
                                    <?php if (has_excerpt()): ?>
                                        <p class="resource-card__excerpt section-desc"><?php echo esc_html(get_the_excerpt()); ?></p>
                                    <?php endif; ?>
                                    <p class="resource-card__action">
                                        <a href="<?php the_permalink(); ?>"
                                            class="resource-card__link"><?php esc_html_e('View resource', 'ai-awareness-day'); ?>
                                            →</a>
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
