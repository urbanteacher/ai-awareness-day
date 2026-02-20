<?php
/**
 * Template Name: Front Page
 * The main homepage template for AI Awareness Day.
 *
 * @package AI_Awareness_Day
 */

get_header();

// Get container/alignment classes
$container_class = aiad_get_container_width_class();
$text_alignment_class = aiad_get_text_alignment_class();
?>

<main id="main" role="main" class="<?php echo esc_attr( $container_class ); ?>">

    <!-- ============================================
         SECTION 1: HERO
         ============================================ -->
    <?php if ( aiad_is_section_visible( 'hero' ) ) : ?>
    <section class="hero-section <?php echo esc_attr( $text_alignment_class ); ?>" id="hero">
        <div class="container">

            <div class="hero-title-block fade-up">
                <p class="hero-date"><?php echo esc_html( aiad_get_theme_mod_default( 'aiad_hero_date', aiad_get_customizer_defaults()['aiad_hero_date'] ) ); ?></p>
                <div class="hero-logo">
                    <?php
                    $defaults = aiad_get_customizer_defaults();
                    $hero_logo_id = absint( get_theme_mod( 'aiad_hero_logo', 0 ) );
                    $hero_logo_url = $hero_logo_id ? wp_get_attachment_image_url( $hero_logo_id, 'full' ) : '';
                    if ( $hero_logo_url ) :
                        ?><img src="<?php echo esc_url( $hero_logo_url ); ?>" alt="<?php echo esc_attr( aiad_get_theme_mod_default( 'aiad_hero_title', $defaults['aiad_hero_title'] ) ); ?>" class="hero-logo__img" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';" />
                        <span class="hero-logo__placeholder" aria-hidden="true" style="display: none;"><?php esc_html_e( 'Logo', 'ai-awareness-day' ); ?></span><?php
                    else :
                        ?><span class="hero-logo__placeholder" aria-hidden="true"><?php esc_html_e( 'Logo', 'ai-awareness-day' ); ?></span><?php
                    endif;
                    ?>
                    <p class="hero-slogan"><?php echo esc_html( aiad_get_theme_mod_default( 'aiad_hero_slogan', $defaults['aiad_hero_slogan'] ) ); ?></p>
                    <p class="section-desc"><?php echo esc_html( aiad_get_theme_mod_default( 'aiad_hero_subtitle', $defaults['aiad_hero_subtitle'] ) ); ?></p>
                </div>
            </div>

        </div>
    </section>
    <?php endif; ?>

    <!-- ============================================
         SECTION 2: CAMPAIGN
         ============================================ -->
    <?php if ( aiad_is_section_visible( 'campaign' ) ) : ?>
    <?php
    $defaults = aiad_get_customizer_defaults();
    $campaign_embed_src = esc_url( get_theme_mod( 'aiad_campaign_linkedin_embed_src', $defaults['aiad_campaign_linkedin_embed_src'] ) );
    $campaign_has_embed = ! empty( $campaign_embed_src );
    ?>
    <section class="section <?php echo esc_attr( $text_alignment_class ); ?> <?php echo $campaign_has_embed ? 'campaign--split' : ''; ?>" id="campaign">
        <div class="container">
            <div class="campaign-split<?php echo $campaign_has_embed ? '' : ' campaign-split--single'; ?>">
                <div class="campaign-content fade-up">
                    <span class="section-label"><?php esc_html_e( 'Campaign', 'ai-awareness-day' ); ?></span>
                    <h2 class="section-title"><?php echo esc_html( aiad_get_theme_mod_default( 'aiad_campaign_title', $defaults['aiad_campaign_title'] ) ); ?></h2>
                    <p class="section-desc"><?php echo wp_kses_post( aiad_get_theme_mod_default( 'aiad_campaign_text', $defaults['aiad_campaign_text'] ) ); ?></p>
                    <p class="section-desc"><?php echo wp_kses_post( aiad_get_theme_mod_default( 'aiad_campaign_text_2', $defaults['aiad_campaign_text_2'] ) ); ?></p>
                </div>
                <?php if ( $campaign_has_embed ) : ?>
                <div class="campaign-embed fade-up">
                    <div class="campaign-embed__wrapper">
                        <iframe
                            src="<?php echo esc_url( $campaign_embed_src ); ?>"
                            height="399"
                            width="504"
                            frameborder="0"
                            allowfullscreen
                            title="<?php esc_attr_e( 'Embedded LinkedIn post', 'ai-awareness-day' ); ?>"
                            loading="lazy"
                        ></iframe>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Momentum Section (Reach) -->
            <div id="reach" class="momentum-section fade-up" style="margin-top: 4rem; padding-top: 3rem; border-top: 1px solid var(--gray-200);">
                <div class="momentum-intro">
                    <span class="section-label"><?php esc_html_e( 'Traction', 'ai-awareness-day' ); ?></span>
                    <h2 class="section-title"><?php esc_html_e( '500,000 reach so far', 'ai-awareness-day' ); ?></h2>
                    <p class="section-desc"><?php esc_html_e( 'The support for AI Awareness Day 2026 is growing fast. With schools now signing up across the UK, we estimate we\'re already reaching over 500,000 students and we\'re confident we\'ll hit 1 million in the coming months. We\'re thrilled to welcome our interested schools, charities, and partners. Together, we\'re building a national movement.', 'ai-awareness-day' ); ?></p>
                </div>

                <div class="partners-grid">
                    <?php
                    $partner_posts = new WP_Query( array(
                        'post_type'      => 'partner',
                        'posts_per_page' => 50,
                        'orderby'        => 'menu_order title',
                        'order'          => 'ASC',
                        'post_status'    => 'publish',
                    ) );

                    $partners = array();
                    if ( $partner_posts->have_posts() ) {
                        while ( $partner_posts->have_posts() ) {
                            $partner_posts->the_post();
                            $partner_stats = get_post_meta( get_the_ID(), '_partner_stats', true );
                            $partners[] = array(
                                'name'  => get_the_title(),
                                'stats' => $partner_stats ? $partner_stats : '',
                                'logo'  => get_the_post_thumbnail_url( get_the_ID(), 'medium' ),
                            );
                        }
                        wp_reset_postdata();
                    }

                    $partners_count = count( $partners );
                    $initial_show = 10; // Show first 10 partners at all times
                    
                    foreach ( $partners as $index => $partner ) :
                        if ( empty( $partner['name'] ) ) {
                            continue;
                        }
                        // Always show first 10 partners, hide only if more than 10 exist
                        $is_hidden = $partners_count > $initial_show && $index >= $initial_show;
                        ?>
                        <div class="partner-card fade-up stagger-<?php echo $index + 1; ?> <?php echo $is_hidden ? 'partner-card--hidden' : ''; ?>" data-partner-index="<?php echo $index; ?>">
                            <div class="partner-logo">
                                <?php if ( $partner['logo'] ) : ?>
                                    <img src="<?php echo esc_url( $partner['logo'] ); ?>" alt="<?php echo esc_attr( $partner['name'] ); ?>" class="partner-logo__img" onerror="this.classList.add('is-broken');" />
                                <?php endif; ?>
                            </div>
                            <h3><?php echo esc_html( $partner['name'] ); ?></h3>
                            <?php if ( $partner['stats'] ) : ?>
                                <p class="partner-stats"><?php echo esc_html( $partner['stats'] ); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php
                    // Add dummy "Join the campaign" partner card that links to form
                    $dummy_index = $partners_count;
                    // Always show dummy card - it's always visible regardless of partner count
                    $dummy_is_hidden = false;
                    ?>
                    <a href="#contact" class="partner-card partner-card--dummy fade-up stagger-<?php echo $dummy_index + 1; ?>" data-partner-index="<?php echo $dummy_index; ?>">
                        <div class="partner-logo">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--gray-400);">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                <line x1="12" y1="8" x2="12" y2="16"/>
                                <line x1="8" y1="12" x2="16" y2="12"/>
                            </svg>
                        </div>
                        <h3><?php esc_html_e( 'Join the campaign', 'ai-awareness-day' ); ?></h3>
                        <p class="partner-stats"><?php esc_html_e( 'Complete form to join movement', 'ai-awareness-day' ); ?></p>
                    </a>
                </div>
                
                <?php if ( $partners_count > $initial_show ) : ?>
                    <div class="partners-reveal-wrapper" style="text-align: center; margin-top: 2.5rem;">
                        <button type="button" class="partners-reveal-btn" id="partners-reveal-btn">
                            <span class="reveal-text"><?php esc_html_e( 'Show More Partners', 'ai-awareness-day' ); ?></span>
                            <span class="hide-text" style="display: none;"><?php esc_html_e( 'Show Less', 'ai-awareness-day' ); ?></span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 0.5rem; transition: transform 0.3s;">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ============================================
         SECTION: YOUTUBE / VIDEO
         ============================================ -->
    <?php
    if ( aiad_is_section_visible( 'youtube' ) ) :
        $defaults = aiad_get_customizer_defaults();
        $youtube_url = aiad_get_theme_mod_default( 'aiad_youtube_url', $defaults['aiad_youtube_url'] );
        $youtube_title = aiad_get_theme_mod_default( 'aiad_youtube_title', $defaults['aiad_youtube_title'] );
        $youtube_video_id = aiad_youtube_video_id( $youtube_url );
        if ( $youtube_video_id ) :
            ?>
    <section class="section section--alt <?php echo esc_attr( $text_alignment_class ); ?>" id="youtube">
        <div class="container">
            <div class="youtube-header fade-up">
                <?php if ( $youtube_title ) : ?>
                    <span class="section-label"><?php esc_html_e( 'Video', 'ai-awareness-day' ); ?></span>
                    <h2 class="section-title"><?php echo esc_html( $youtube_title ); ?></h2>
                <?php endif; ?>
            </div>
            <div class="video-wrapper video-wrapper--left">
                <iframe
                    src="https://www.youtube.com/embed/<?php echo esc_attr( $youtube_video_id ); ?>?rel=0&modestbranding=1"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen
                    loading="lazy"
                    width="560"
                    height="315"
                    title="<?php echo esc_attr( $youtube_title ?: __( 'YouTube video', 'ai-awareness-day' ) ); ?>"
                ></iframe>
            </div>
        </div>
    </section>
        <?php
        endif;
    endif;
    ?>

    <!-- ============================================
         SECTION 3: PRINCIPLES
         ============================================ -->
    <?php if ( aiad_is_section_visible( 'principles' ) ) : ?>
    <section class="section section--alt <?php echo esc_attr( $text_alignment_class ); ?>" id="principles">
        <div class="container">
            <div class="fade-up">
                <span class="section-label"><?php esc_html_e( 'Principles', 'ai-awareness-day' ); ?></span>
                <h2 class="section-title"><?php esc_html_e( 'Five Core Principles', 'ai-awareness-day' ); ?></h2>
                <p class="section-desc"><?php esc_html_e( 'Our educational framework is built on five foundational principles that guide how we approach AI learning.', 'ai-awareness-day' ); ?></p>
            </div>

            <div class="principles-grid">
                <?php
                $principle_slugs = array( 'safe', 'smart', 'creative', 'responsible', 'future' );
                $principle_default_titles = array(
                    'safe'        => __( 'Safe', 'ai-awareness-day' ),
                    'smart'       => __( 'Smart', 'ai-awareness-day' ),
                    'creative'    => __( 'Creative', 'ai-awareness-day' ),
                    'responsible' => __( 'Responsible', 'ai-awareness-day' ),
                    'future'      => __( 'Future', 'ai-awareness-day' ),
                );
                $principle_default_descs = array(
                    'safe'        => __( 'Ensuring safe and secure interactions with AI technologies.', 'ai-awareness-day' ),
                    'smart'       => __( 'Building intelligent understanding of how AI works.', 'ai-awareness-day' ),
                    'creative'    => __( 'Harnessing AI as a tool for creativity and innovation.', 'ai-awareness-day' ),
                    'responsible' => __( 'Promoting ethical and responsible use of AI.', 'ai-awareness-day' ),
                    'future'      => __( 'Preparing for an AI-shaped future with confidence.', 'ai-awareness-day' ),
                );
                foreach ( $principle_slugs as $index => $slug ) :
                    // Get title: use customizer value if set and not empty, otherwise use default
                    $title_mod = get_theme_mod( 'aiad_principle_title_' . $slug, '' );
                    $title = ! empty( $title_mod ) ? $title_mod : ( isset( $principle_default_titles[ $slug ] ) ? $principle_default_titles[ $slug ] : ucfirst( $slug ) );
                    
                    // Get description: use customizer value if set and not empty, otherwise use default
                    $desc_mod = get_theme_mod( 'aiad_principle_desc_' . $slug, '' );
                    $desc = ! empty( $desc_mod ) ? $desc_mod : ( isset( $principle_default_descs[ $slug ] ) ? $principle_default_descs[ $slug ] : '' );
                    
                    $p = array(
                        'title' => $title,
                        'desc'  => $desc,
                    );
                    // Use same simple approach as display board images (which work reliably)
                    $badge_id = absint( get_theme_mod( 'aiad_badge_' . $slug, 0 ) );
                    $badge_src = $badge_id ? wp_get_attachment_image_url( $badge_id, 'medium' ) : '';
                    $has_badge_image = ! empty( $badge_src );
                    ?>
                <div class="principle-card principle-card--<?php echo esc_attr( $slug ); ?> fade-up stagger-<?php echo $index + 1; ?>">
                    <div class="principle-badge">
                        <?php if ( $has_badge_image ) : ?>
                            <img src="<?php echo esc_url( $badge_src ); ?>" alt="" aria-hidden="true" class="principle-badge__img" onerror="this.classList.add('is-broken');" />
                        <?php endif; ?>
                    </div>
                    <h3><?php echo esc_html( $p['title'] ); ?></h3>
                    <p class="section-desc"><?php echo esc_html( $p['desc'] ); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ============================================
         SECTION 5: AIM
         ============================================ -->
    <?php if ( aiad_is_section_visible( 'aim' ) ) : ?>
    <section class="section section--green <?php echo esc_attr( $text_alignment_class ); ?>" id="aim">
        <div class="container">
            <div class="fade-up">
                <span class="section-label"><?php esc_html_e( 'Aim', 'ai-awareness-day' ); ?></span>
                <h2 class="section-title"><?php esc_html_e( 'What We Hope to Achieve', 'ai-awareness-day' ); ?></h2>
            </div>

            <div class="aims-list">
                <?php
                $aims = array(
                    __( 'Demystify AI for students, parents, and educators — making it accessible, understandable, and less intimidating.', 'ai-awareness-day' ),
                    __( 'Develop critical thinking skills that enable young people to evaluate AI-generated content and make informed decisions.', 'ai-awareness-day' ),
                    __( 'Build digital resilience so students can navigate an AI-powered world safely and confidently.', 'ai-awareness-day' ),
                    __( 'Inspire creative and responsible use of AI tools across the curriculum and beyond the classroom.', 'ai-awareness-day' ),
                    __( 'Foster a national conversation about the role of AI in education, skills development, and the future of work.', 'ai-awareness-day' ),
                );

                foreach ( $aims as $index => $aim ) : ?>
                <div class="aim-item fade-up stagger-<?php echo $index + 1; ?>">
                    <div class="aim-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <p class="section-desc"><?php echo esc_html( $aim ); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- ============================================
         SECTION 6: TOOLKIT
         ============================================ -->
    <?php if ( aiad_is_section_visible( 'toolkit' ) ) : ?>
    <section class="section <?php echo esc_attr( $text_alignment_class ); ?>" id="toolkit">
        <div class="container">
            <div class="fade-up">
                <span class="section-label"><?php esc_html_e( 'Toolkit', 'ai-awareness-day' ); ?></span>
                <h2 class="section-title"><?php esc_html_e( 'Plug-and-Play Toolkit', 'ai-awareness-day' ); ?></h2>
                <p class="section-desc"><?php esc_html_e( 'Everything your school needs to participate — ready to use, easy to adapt.', 'ai-awareness-day' ); ?></p>
            </div>

            <div class="toolkit-grid">
                <?php
                $newsletter_url     = esc_url_raw( get_theme_mod( 'aiad_newsletter_url', 'https://aiawarenessday.beehiiv.com/p/ai-awareness-day-launched' ) );
                $sample_letters_url = esc_url_raw( get_theme_mod( 'aiad_sample_letters_url', 'https://beehiiv-publication-files.s3.amazonaws.com/uploads/downloadables/54845583-4adb-4ee9-8457-f9f4065c7216/a21336a3-e31b-4383-a127-6aada6856882/SLT%20APPROVAL.pdf?X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=AKIAQCMHTQSE2JGAGXHJ%2F20260219%2Fus-east-1%2Fs3%2Faws4_request&X-Amz-Date=20260219T134149Z&X-Amz-Expires=604800&X-Amz-SignedHeaders=host&X-Amz-Signature=63e9e703294592ac0831c1514a8cb35998b38153e36dd02ad109ab57226d2625' ) );
                $toolkit_items = array(
                    array(
                        'title' => __( 'Implementation Guide', 'ai-awareness-day' ),
                        'desc'  => __( 'Step-by-step instructions to run AI Awareness Day in your school.', 'ai-awareness-day' ),
                        'icon'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline>',
                        'url'   => '',
                    ),
                    array(
                        'title' => __( 'Sample Letters & Communications', 'ai-awareness-day' ),
                        'desc'  => __( 'Pre-written letters for parents, governors, and local press.', 'ai-awareness-day' ),
                        'icon'  => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline>',
                        'url'   => $sample_letters_url,
                    ),
                    array(
                        'title' => __( 'Latest Newsletter', 'ai-awareness-day' ),
                        'desc'  => __( 'Click to read the latest AI Awareness Day newsletter and updates.', 'ai-awareness-day' ),
                        'icon'  => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline>',
                        'url'   => $newsletter_url,
                    ),
                );

                foreach ( $toolkit_items as $index => $item ) :
                    $card_url = ! empty( $item['url'] ) ? $item['url'] : '';
                    $is_link  = $card_url !== '';
                    ?>
                <?php if ( $is_link ) : ?>
                <a href="<?php echo esc_url( $card_url ); ?>" class="toolkit-card toolkit-card--link fade-up stagger-<?php echo $index + 1; ?>" target="_blank" rel="noopener noreferrer">
                <?php else : ?>
                <div class="toolkit-card fade-up stagger-<?php echo $index + 1; ?>">
                <?php endif; ?>
                    <div class="toolkit-header">
                        <div class="toolkit-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <?php echo $item['icon']; ?>
                            </svg>
                        </div>
                        <h3><?php echo esc_html( $item['title'] ); ?></h3>
                    </div>
                    <p class="section-desc"><?php echo esc_html( $item['desc'] ); ?></p>
                <?php if ( $is_link ) : ?>
                </a>
                <?php else : ?>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Display board guide: layout mock-up + instructions for teachers -->
            <div id="display-board" class="toolkit-display-board fade-up" style="margin-top: 3rem; padding-top: 2.5rem; border-top: 1px solid var(--gray-200);">
                <span class="section-label"><?php esc_html_e( 'Display board', 'ai-awareness-day' ); ?></span>
                <h2 class="section-title"><?php esc_html_e( 'Create a display board for your school', 'ai-awareness-day' ); ?></h2>
                <p class="section-desc"><?php esc_html_e( 'Use the layout below as a guide to build a physical display in your school or staff room. Toggle between blueprint and a real example, then follow the steps.', 'ai-awareness-day' ); ?></p>

                <?php
                $display_img_1_id = absint( get_theme_mod( 'aiad_display_board_image_1', 0 ) );
                $display_img_1_url = $display_img_1_id ? wp_get_attachment_image_url( $display_img_1_id, 'full' ) : '';
                ?>
                <div class="display-board-preview js-display-board-preview<?php echo $display_img_1_url ? ' display-board-preview--has-real' : ''; ?>" data-view="blueprint">
                    <div class="display-board-flip-toggle" role="tablist" aria-label="<?php esc_attr_e( 'View blueprint or real example', 'ai-awareness-day' ); ?>">
                        <button type="button" class="display-board-flip-btn is-active" role="tab" aria-selected="true" aria-controls="display-board-blueprint" id="tab-blueprint"><?php esc_html_e( 'Blueprint', 'ai-awareness-day' ); ?></button>
                        <button type="button" class="display-board-flip-btn<?php echo $display_img_1_url ? '' : ' display-board-flip-btn--disabled'; ?>" role="tab" aria-selected="false" aria-controls="display-board-real" id="tab-real"<?php echo $display_img_1_url ? '' : ' disabled aria-disabled="true"'; ?>><?php esc_html_e( 'Real example', 'ai-awareness-day' ); ?></button>
                    </div>
                    <div class="display-board-preview__view display-board-preview__view--blueprint" id="display-board-blueprint" role="tabpanel" aria-labelledby="tab-blueprint">
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
                        $principle_slugs = array( 'safe', 'smart', 'creative', 'responsible', 'future' );
                        $principle_labels = array(
                            'safe'       => __( 'Be Safe', 'ai-awareness-day' ),
                            'smart'      => __( 'Be Smart', 'ai-awareness-day' ),
                            'creative'   => __( 'Be Creative', 'ai-awareness-day' ),
                            'responsible' => __( 'Be Responsible', 'ai-awareness-day' ),
                            'future'     => __( 'Be Future', 'ai-awareness-day' ),
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
                    <div class="display-board-preview__view display-board-preview__view--real" id="display-board-real" role="tabpanel" aria-labelledby="tab-real"<?php echo $display_img_1_url ? '' : ' hidden'; ?>>
                        <?php if ( $display_img_1_url ) : ?>
                            <div class="display-board-real">
                                <img src="<?php echo esc_url( $display_img_1_url ); ?>" alt="<?php esc_attr_e( 'Example display board', 'ai-awareness-day' ); ?>" loading="lazy" />
                            </div>
                        <?php else : ?>
                            <div class="display-board-real" style="padding: 2rem; text-align: center; color: var(--gray-500);">
                                <p><?php esc_html_e( 'No real example available. Upload an image in the Customizer to see it here.', 'ai-awareness-day' ); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="display-board-steps-wrapper">
                <div class="display-board-steps">
                    <button type="button" class="display-board-steps__toggle" aria-expanded="true" aria-controls="display-board-steps-list">
                        <span class="display-board-steps__title"><?php esc_html_e( 'How to create yours', 'ai-awareness-day' ); ?></span>
                        <span class="display-board-steps__icon" aria-hidden="true">−</span>
                    </button>
                    <div class="container">
                        <ol class="display-board-steps__list" id="display-board-steps-list">
                            <li class="section-desc"><?php esc_html_e( 'Select a prominent wall, noticeboard, or display area in your school or staff room.', 'ai-awareness-day' ); ?></li>
                            <li class="section-desc"><?php esc_html_e( 'Follow the blueprint layout: create five principle panels (Safe, Smart, Creative, Responsible, Future) each with a key message and practical tips.', 'ai-awareness-day' ); ?></li>
                            <li class="section-desc"><?php esc_html_e( 'QR challenges: Set up QR codes for students to scan & investigate. Link to your school\'s AI policy and our AI guidelines or activities.', 'ai-awareness-day' ); ?></li>
                            <li class="section-desc"><?php esc_html_e( 'Add interactive elements: Include facts, tips, or QR codes linking to games and quizzes using our interactive resources.', 'ai-awareness-day' ); ?></li>
                            <li class="section-desc"><?php esc_html_e( "This week's questions: Add thought-provoking questions like \"How can we ensure AI tools are fair?\" with space for student responses.", 'ai-awareness-day' ); ?></li>
                            <li class="section-desc"><?php esc_html_e( 'Student responses: Provide space for sticky notes or written answers where students can share their thoughts and ideas.', 'ai-awareness-day' ); ?></li>
                            <li class="section-desc"><?php esc_html_e( 'AI leaders & innovators: Include photos and names of people working in AI.', 'ai-awareness-day' ); ?></li>
                            <li class="section-desc"><?php esc_html_e( 'Set them a challenge: Ask students to find 3 living people working in AI and add their discoveries to the display.', 'ai-awareness-day' ); ?></li>
                            <li class="section-desc"><?php esc_html_e( 'Student spotlight: Feature student work or projects to showcase pupil achievements and creativity.', 'ai-awareness-day' ); ?></li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="container">
                <div class="display-board-submit fade-up">
                    <div class="display-board-submit__content">
                        <div class="display-board-submit__text">
                            <h3 class="display-board-steps__title"><?php esc_html_e( 'Submit your display board', 'ai-awareness-day' ); ?></h3>
                            <p class="section-desc"><?php esc_html_e( 'Share photos of your school\'s display board. Accepted formats: JPG, PNG, PDF.', 'ai-awareness-day' ); ?></p>
                        </div>
                        <?php
                        $contact_email = get_theme_mod( 'aiad_contact_email', get_option( 'admin_email' ) );
                        $email_subject = rawurlencode( 'Our Schools Display' );
                        $email_body = rawurlencode( 'Hello,\n\nPlease find attached photos of our school\'s AI Awareness Day display board.\n\nThank you!' );
                        $mailto_link = 'mailto:' . esc_attr( $contact_email ) . '?subject=' . $email_subject . '&body=' . $email_body;
                        ?>
                        <a href="<?php echo esc_url( $mailto_link ); ?>" class="resource-filter-submit display-board-submit__button">
                            <?php esc_html_e( 'Submit your display board design', 'ai-awareness-day' ); ?>
                        </a>
                    </div>
                </div>

                <?php
                $display_img_2_id = absint( get_theme_mod( 'aiad_display_board_image_2', 0 ) );
                $display_img_3_id = absint( get_theme_mod( 'aiad_display_board_image_3', 0 ) );
                $display_img_2_url = $display_img_2_id ? wp_get_attachment_image_url( $display_img_2_id, 'full' ) : '';
                $display_img_3_url = $display_img_3_id ? wp_get_attachment_image_url( $display_img_3_id, 'full' ) : '';
                if ( $display_img_2_url || $display_img_3_url ) :
                    ?>
                    <div class="display-board-examples" hidden>
                        <h3 class="display-board-examples__title"><?php esc_html_e( 'More example display boards', 'ai-awareness-day' ); ?></h3>
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
            </div>

            <div id="themes" class="toolkit-explore-themes" style="margin-top: 3rem; padding-top: 2.5rem; border-top: 1px solid var(--gray-200);">
                <div class="fade-up">
                    <span class="section-label"><?php esc_html_e( 'Themes', 'ai-awareness-day' ); ?></span>
                    <h2 class="section-title"><?php esc_html_e( 'Explore the Themes', 'ai-awareness-day' ); ?></h2>
                    <p class="section-desc"><?php esc_html_e( 'Discover the thematic areas that shape AI Awareness Day activities and discussions. Filter by theme or by session length.', 'ai-awareness-day' ); ?></p>
                </div>
                <?php
                // Get resources archive URL - handle permalink structure properly
                $permalink_structure = get_option( 'permalink_structure' );
                if ( $permalink_structure ) {
                    // Pretty permalinks enabled - use /resources/ URL
                    $resources_url = trailingslashit( home_url() ) . 'resources/';
                } else {
                    // Plain permalinks - use query string format
                    $resources_url = add_query_arg( 'post_type', 'resource', home_url( '/' ) );
                }
                $theme_terms = get_terms( array( 'taxonomy' => 'resource_principle', 'hide_empty' => false ) );
                if ( $theme_terms && ! is_wp_error( $theme_terms ) ) :
                    ?>
                    <p class="explore-subheading" style="margin-top: 2rem; margin-bottom: 0.75rem; font-family: var(--font-display); font-size: 0.85rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--gray-700);"><?php esc_html_e( 'By theme', 'ai-awareness-day' ); ?></p>
                    <div class="themes-links">
                        <?php foreach ( $theme_terms as $term ) :
                            $url = add_query_arg( 'principle', $term->slug, $resources_url );
                            // Ensure URL uses site URL, not localhost (safety check)
                            if ( strpos( $url, 'localhost' ) !== false ) {
                                $url = str_replace( parse_url( $url, PHP_URL_SCHEME ) . '://' . parse_url( $url, PHP_URL_HOST ), parse_url( home_url(), PHP_URL_SCHEME ) . '://' . parse_url( home_url(), PHP_URL_HOST ), $url );
                            }
                            // Map term slug to Customizer badge setting (normalize to lowercase)
                            // Use same simple approach as display board images (which work reliably)
                            $badge_slug = strtolower( $term->slug );
                            $theme_badge_id = absint( get_theme_mod( 'aiad_badge_' . $badge_slug, 0 ) );
                            $theme_badge_src = $theme_badge_id ? wp_get_attachment_image_url( $theme_badge_id, 'thumbnail' ) : '';
                            $has_theme_badge = ! empty( $theme_badge_src );
                            ?>
                            <a href="<?php echo esc_url( $url ); ?>" class="theme-link fade-up">
                                <?php if ( $has_theme_badge ) : ?>
                                    <span class="theme-link__badge">
                                        <img src="<?php echo esc_url( $theme_badge_src ); ?>" alt="" aria-hidden="true" class="theme-link__badge-img" onerror="this.classList.add('is-broken');" />
                                    </span>
                                <?php endif; ?>
                                <span class="theme-link__label"><?php echo esc_html( $term->name ); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php
                endif;
                $session_cards = function_exists( 'aiad_explore_session_cards' ) ? aiad_explore_session_cards() : array();
                $duration_terms = get_terms( array( 'taxonomy' => 'resource_duration', 'hide_empty' => false ) );
                $duration_slugs = array();
                if ( $duration_terms && ! is_wp_error( $duration_terms ) ) {
                    $duration_slugs = wp_list_pluck( $duration_terms, 'slug' );
                }
                if ( ! empty( $session_cards ) ) :
                    ?>
                    <p class="explore-subheading" style="margin-top: 2.5rem; margin-bottom: 0.75rem; font-family: var(--font-display); font-size: 0.85rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--gray-700);"><?php esc_html_e( 'By session length', 'ai-awareness-day' ); ?></p>
                    <div class="explore-session-cards">
                        <?php
                        foreach ( array_keys( $session_cards ) as $slug ) :
                            if ( ! in_array( $slug, $duration_slugs, true ) ) {
                                continue;
                            }
                            $card = $session_cards[ $slug ];
                            $url  = add_query_arg( 'duration', $slug, $resources_url );
                            // Ensure URL uses site URL, not localhost
                            if ( strpos( $url, 'localhost' ) !== false ) {
                                $url = str_replace( parse_url( $url, PHP_URL_SCHEME ) . '://' . parse_url( $url, PHP_URL_HOST ), parse_url( home_url(), PHP_URL_SCHEME ) . '://' . parse_url( home_url(), PHP_URL_HOST ), $url );
                            }
                            $icon = isset( $card['icon'] ) ? $card['icon'] : 'book';
                            $icon_bg = isset( $card['icon_bg'] ) ? $card['icon_bg'] : '#c4b5fd';
                            ?>
                            <a href="<?php echo esc_url( $url ); ?>" class="explore-session-card fade-up">
                                <span class="explore-session-icon" style="background: <?php echo esc_attr( $icon_bg ); ?>">
                                    <?php
                                    if ( $icon === 'clock' ) : ?>
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    <?php elseif ( $icon === 'people' ) : ?>
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                    <?php elseif ( $icon === 'presentation' ) : ?>
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                                    <?php else : ?>
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path><line x1="8" y1="7" x2="16" y2="7"></line><line x1="8" y1="11" x2="16" y2="11"></line></svg>
                                    <?php endif; ?>
                                </span>
                                <span class="explore-session-text">
                                    <span class="explore-session-title"><?php echo esc_html( $card['title'] ); ?></span>
                                    <span class="explore-session-desc"><?php echo esc_html( $card['description'] ); ?></span>
                                </span>
                                <span class="explore-session-badge"><?php echo esc_html( $card['badge_short'] ); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Free Resources: 5-minute lesson starters only (one per theme) -->
            <?php
            $free_resources = new WP_Query( array(
                'post_type'      => 'resource',
                'post_status'    => 'publish',
                'posts_per_page' => 3,
                'orderby'        => 'menu_order title',
                'order'          => 'ASC',
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'resource_duration',
                        'field'    => 'slug',
                        'terms'    => '5-min-lesson-starters',
                    ),
                ),
            ) );
            if ( $free_resources->have_posts() ) :
                // Get resources archive URL - handle permalink structure properly
                $permalink_structure = get_option( 'permalink_structure' );
                if ( $permalink_structure ) {
                    // Pretty permalinks enabled - use /resources/ URL
                    $resources_archive_url = trailingslashit( home_url() ) . 'resources/';
                } else {
                    // Plain permalinks - use query string format
                    $resources_archive_url = add_query_arg( 'post_type', 'resource', home_url( '/' ) );
                }
                ?>
                <div id="free-resources" class="toolkit-free-resources fade-up" style="margin-top: 3rem; padding-top: 2.5rem; border-top: 1px solid var(--gray-200);">
                    <span class="section-label"><?php esc_html_e( 'Free Resources', 'ai-awareness-day' ); ?></span>
                    <h2 class="section-title"><?php esc_html_e( 'AI Awareness Activities', 'ai-awareness-day' ); ?></h2>
                    <p class="section-desc"><?php esc_html_e( 'Free Resources', 'ai-awareness-day' ); ?></p>
                    <div class="resources-grid" style="margin-top: 2rem;">
                        <?php
                        while ( $free_resources->have_posts() ) :
                            $free_resources->the_post();
                            $types     = get_the_terms( get_the_ID(), 'resource_type' );
                            $themes    = get_the_terms( get_the_ID(), 'resource_principle' );
                            $durations = get_the_terms( get_the_ID(), 'resource_duration' );
                            $type_name   = $types && ! is_wp_error( $types ) ? $types[0]->name : '';
                            $theme_name  = $themes && ! is_wp_error( $themes ) ? $themes[0]->name : '';
                            $duration_name = '';
                            if ( $durations && ! is_wp_error( $durations ) && function_exists( 'aiad_duration_badge_label' ) ) {
                                $duration_name = aiad_duration_badge_label( $durations[0] );
                            } elseif ( $durations && ! is_wp_error( $durations ) ) {
                                $duration_name = $durations[0]->name;
                            }
                            $download_url = get_post_meta( get_the_ID(), '_resource_download_url', true );
                            $duration_slug = $durations && ! is_wp_error( $durations ) ? $durations[0]->slug : '';
                            $session_cards = function_exists( 'aiad_explore_session_cards' ) ? aiad_explore_session_cards() : array();
                            $placeholder_text = __( 'Starter Slide', 'ai-awareness-day' );
                            ?>
                            <article class="resource-card resource-card--download fade-up">
                                <a href="<?php the_permalink(); ?>" class="resource-card__image-link">
                                    <?php if ( has_post_thumbnail() ) : ?>
                                        <?php the_post_thumbnail( 'medium_large', array( 'class' => 'resource-card__image' ) ); ?>
                                    <?php else : ?>
                                        <div class="resource-card__image-placeholder" aria-hidden="true">
                                            <span class="resource-card__image-placeholder-text"><?php echo esc_html( $placeholder_text ); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php
                                    $pill_duration = ( $duration_slug && isset( $session_cards[ $duration_slug ]['badge_short'] ) )
                                        ? ucwords( $session_cards[ $duration_slug ]['badge_short'] )
                                        : ( $duration_name ? $duration_name : '' );
                                    $has_overlay = $pill_duration || $theme_name;
                                    ?>
                                    <?php if ( $has_overlay ) : ?>
                                        <div class="resource-card__image-overlay" aria-hidden="true">
                                            <div class="resource-card__image-top">
                                                <?php if ( $theme_name ) : ?>
                                                    <?php
                                                    $theme_slug = strtolower( $theme_name );
                                                    $pill_class = 'resource-card__pill--theme';
                                                    if ( in_array( $theme_slug, array( 'safe', 'smart', 'creative', 'responsible', 'future' ), true ) ) {
                                                        $pill_class .= ' resource-card__pill--' . $theme_slug;
                                                    }
                                                    ?>
                                                    <span class="resource-card__pill <?php echo esc_attr( $pill_class ); ?>"><?php echo esc_html( $theme_name ); ?></span>
                                                <?php endif; ?>
                                                <?php if ( $pill_duration ) : ?>
                                                    <span class="resource-card__pill resource-card__pill--type"><?php echo esc_html( $pill_duration ); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </a>
                                <div class="resource-card__body">
                                    <h2 class="resource-card__title">
                                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    </h2>
                                    <?php if ( has_excerpt() ) : ?>
                                        <p class="resource-card__excerpt section-desc"><?php echo esc_html( get_the_excerpt() ); ?></p>
                                    <?php endif; ?>
                                    <p class="resource-card__action">
                                        <?php if ( $download_url ) : ?>
                                            <?php $download_label = function_exists( 'aiad_resource_download_label' ) ? aiad_resource_download_label( $download_url ) : __( 'Download', 'ai-awareness-day' ); ?>
                                            <a href="<?php echo esc_url( $download_url ); ?>" class="resource-card__link" download target="_blank" rel="noopener"><?php echo esc_html( $download_label ); ?> →</a>
                                        <?php else : ?>
                                            <a href="<?php the_permalink(); ?>" class="resource-card__link"><?php esc_html_e( 'View resource', 'ai-awareness-day' ); ?> →</a>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                    <p style="margin-top: 1.5rem;">
                        <a href="<?php echo esc_url( $resources_archive_url ); ?>" class="resource-filter-submit"><?php esc_html_e( 'View all resources', 'ai-awareness-day' ); ?></a>
                    </p>
                </div>
                <?php
                wp_reset_postdata();
            endif;
            ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ============================================
         SECTION: PARTNER RESOURCES (FEATURED)
         ============================================ -->
    <?php
    if ( aiad_is_section_visible( 'featured_resources' ) ) :
        // Query featured resources (external resources from partner organizations)
        $featured_resources = new WP_Query( array(
        'post_type'      => 'featured_resource',
        'post_status'    => 'publish',
        'posts_per_page' => 3,
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
    ) );

    if ( $featured_resources->have_posts() ) :
        ?>
        <section class="section section--alt" id="partner-resources">
            <div class="container">
                <div class="fade-up">
                    <span class="section-label"><?php esc_html_e( 'Extra Resources', 'ai-awareness-day' ); ?></span>
                    <h2 class="section-title"><?php esc_html_e( 'Handpicked Quality Resources', 'ai-awareness-day' ); ?></h2>
                    <p class="section-desc"><?php esc_html_e( 'A curated selection of interactive AI games and learning tools from trusted organisations.', 'ai-awareness-day' ); ?></p>
                </div>

                <div class="resources-grid" style="margin-top: 2rem;">
                    <?php while ( $featured_resources->have_posts() ) : $featured_resources->the_post();
                        $types      = get_the_terms( get_the_ID(), 'resource_type' );
                        $themes     = get_the_terms( get_the_ID(), 'resource_principle' );
                        $durations  = get_the_terms( get_the_ID(), 'resource_duration' );
                        $type_name  = $types && ! is_wp_error( $types ) ? $types[0]->name : '';
                        $theme_name = $themes && ! is_wp_error( $themes ) ? $themes[0]->name : '';
                        $duration_name = '';
                        if ( $durations && ! is_wp_error( $durations ) && function_exists( 'aiad_duration_badge_label' ) ) {
                            $duration_name = aiad_duration_badge_label( $durations[0] );
                        } elseif ( $durations && ! is_wp_error( $durations ) ) {
                            $duration_name = $durations[0]->name;
                        }
                        $url      = get_post_meta( get_the_ID(), '_featured_resource_url', true );
                        $org_name = get_post_meta( get_the_ID(), '_featured_resource_org_name', true );
                        $org_url  = get_post_meta( get_the_ID(), '_featured_resource_org_url', true );
                        $link    = $url ? $url : get_permalink();
                        $activity_terms = get_the_terms( get_the_ID(), 'activity_type' );
                        $placeholder_type = ( $activity_terms && ! is_wp_error( $activity_terms ) && ! empty( $activity_terms ) )
                            ? $activity_terms[0]->name
                            : ( $type_name ? $type_name : '—' );
                        ?>
                        <article class="resource-card resource-card--external fade-up">
                            <?php
                            $meta_label = trim( $type_name . ( $type_name && $theme_name ? ' · ' : '' ) . $theme_name );
                            ?>
                            <a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener noreferrer" class="resource-card__image-link">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'medium_large', array( 'class' => 'resource-card__image' ) ); ?>
                                <?php else : ?>
                                    <div class="resource-card__image-placeholder" aria-hidden="true">
                                        <span class="resource-card__image-placeholder-text"><?php echo esc_html( $placeholder_type ); ?></span>
                                    </div>
                                <?php endif; ?>
                            <?php if ( $org_name || $meta_label ) : ?>
                                <div class="resource-card__image-overlay" aria-hidden="true">
                                    <div class="resource-card__image-top">
                                        <?php if ( $theme_name ) : ?>
                                            <?php
                                            $theme_slug = strtolower( $theme_name );
                                            $pill_class = 'resource-card__pill--theme';
                                            if ( in_array( $theme_slug, array( 'safe', 'smart', 'creative', 'responsible', 'future' ), true ) ) {
                                                $pill_class .= ' resource-card__pill--' . $theme_slug;
                                            }
                                            ?>
                                            <span class="resource-card__pill <?php echo esc_attr( $pill_class ); ?>"><?php echo esc_html( $theme_name ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $org_name ) : ?>
                                            <span class="resource-card__pill resource-card__pill--org"><?php echo esc_html( $org_name ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="resource-card__image-title"><?php the_title(); ?></div>
                                </div>
                            <?php endif; ?>
                            </a>
                            <div class="resource-card__body">
                                <?php if ( $org_name || $type_name || $theme_name ) : ?>
                                    <p class="resource-card__meta">
                                        <?php
                                        $meta_parts = array_filter( array( $org_name, $type_name, $theme_name ) );
                                        echo esc_html( implode( ' · ', $meta_parts ) );
                                        ?>
                                    </p>
                                <?php endif; ?>
                                <?php if ( $duration_name ) : ?>
                                    <span class="duration-badge duration-badge--card"><?php echo esc_html( $duration_name ); ?></span>
                                <?php endif; ?>
                                <h2 class="resource-card__title">
                                    <a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener noreferrer"><?php the_title(); ?></a>
                                </h2>
                                <?php if ( has_excerpt() ) : ?>
                                    <p class="resource-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
                                <?php endif; ?>
                                <p class="resource-card__action">
                                    <a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener noreferrer" class="resource-card__link"><?php esc_html_e( 'View resource', 'ai-awareness-day' ); ?> →</a>
                                </p>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>
        <?php
        wp_reset_postdata();
    endif;
    endif;

    $linkedin_post_url = esc_url_raw( get_theme_mod( 'aiad_linkedin_post_url', '' ) );
    if ( ! empty( $linkedin_post_url ) ) :
        ?>
    <!-- LinkedIn post card -->
    <section class="section <?php echo esc_attr( $text_alignment_class ); ?>" id="linkedin-post" aria-labelledby="linkedin-post-title">
        <div class="container">
            <div class="linkedin-card-wrapper fade-up">
                <a href="<?php echo esc_url( $linkedin_post_url ); ?>" class="linkedin-card" target="_blank" rel="noopener noreferrer">
                    <span class="linkedin-card__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    </span>
                    <div class="linkedin-card__content">
                        <h2 id="linkedin-post-title" class="linkedin-card__title"><?php esc_html_e( 'Latest from LinkedIn', 'ai-awareness-day' ); ?></h2>
                        <p class="linkedin-card__desc"><?php esc_html_e( 'See our latest post and join the conversation.', 'ai-awareness-day' ); ?></p>
                        <span class="linkedin-card__cta"><?php esc_html_e( 'View post on LinkedIn', 'ai-awareness-day' ); ?> →</span>
                    </div>
                </a>
            </div>
        </div>
    </section>
        <?php
    endif;
    ?>

    <!-- ============================================
         SECTION 7: GET INVOLVED (FORM)
         ============================================ -->
    <?php if ( aiad_is_section_visible( 'contact' ) ) : ?>
    <section class="section section--alt <?php echo esc_attr( $text_alignment_class ); ?>" id="contact">
        <div class="container">
            <div class="contact-wrapper">

                <div class="contact-info fade-up">
                    <span class="section-label"><?php esc_html_e( 'Get Involved', 'ai-awareness-day' ); ?></span>
                    <?php
                    $defaults = aiad_get_customizer_defaults();
                    ?>
                    <h2 class="section-title"><?php echo esc_html( aiad_get_theme_mod_default( 'aiad_contact_title', $defaults['aiad_contact_title'] ) ); ?></h2>
                    <p class="section-desc"><?php echo wp_kses_post( aiad_get_theme_mod_default( 'aiad_contact_desc', $defaults['aiad_contact_desc'] ) ); ?></p>
                </div>

                <div class="contact-form fade-up stagger-2">
                    <form id="aiad-contact-form" novalidate>
                        <div class="form-group">
                            <label for="involved_as"><?php esc_html_e( 'I\'m getting involved as *', 'ai-awareness-day' ); ?></label>
                            <select id="involved_as" name="involved_as" required aria-describedby="involved_as-desc">
                                <option value=""><?php esc_html_e( 'Select...', 'ai-awareness-day' ); ?></option>
                                <option value="teacher"><?php esc_html_e( 'Teacher', 'ai-awareness-day' ); ?></option>
                                <option value="parent"><?php esc_html_e( 'Parent', 'ai-awareness-day' ); ?></option>
                                <option value="school_leader"><?php esc_html_e( 'School leader', 'ai-awareness-day' ); ?></option>
                                <option value="organisation"><?php esc_html_e( 'Organisation', 'ai-awareness-day' ); ?></option>
                            </select>
                            <span id="involved_as-desc" class="screen-reader-text"><?php esc_html_e( 'Choose the option that best describes you.', 'ai-awareness-day' ); ?></span>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name"><?php esc_html_e( 'First Name *', 'ai-awareness-day' ); ?></label>
                                <input type="text" id="first_name" name="first_name" required placeholder="<?php esc_attr_e( 'Your first name', 'ai-awareness-day' ); ?>">
                            </div>
                            <div class="form-group">
                                <label for="last_name"><?php esc_html_e( 'Last Name *', 'ai-awareness-day' ); ?></label>
                                <input type="text" id="last_name" name="last_name" required placeholder="<?php esc_attr_e( 'Your last name', 'ai-awareness-day' ); ?>">
                            </div>
                        </div>

                        <div class="form-group form-group-role" data-role="teacher school_leader" style="display: none;">
                            <label for="school_name"><?php esc_html_e( 'School name *', 'ai-awareness-day' ); ?></label>
                            <input type="text" id="school_name" name="school_name" required placeholder="<?php esc_attr_e( 'Your school', 'ai-awareness-day' ); ?>">
                        </div>
                        <div class="form-group form-group-role" data-role="teacher" style="display: none;">
                            <label for="subject"><?php esc_html_e( 'Subject / area *', 'ai-awareness-day' ); ?></label>
                            <input type="text" id="subject" name="subject" required placeholder="<?php esc_attr_e( 'e.g. Computing, Maths', 'ai-awareness-day' ); ?>">
                        </div>

                        <div class="form-group form-group-role" data-role="parent" style="display: none;">
                            <label for="child_school"><?php esc_html_e( 'Child\'s school *', 'ai-awareness-day' ); ?></label>
                            <input type="text" id="child_school" name="child_school" required placeholder="<?php esc_attr_e( 'School name', 'ai-awareness-day' ); ?>">
                        </div>

                        <div class="form-group form-group-role" data-role="school_leader" style="display: none;">
                            <label for="role_title"><?php esc_html_e( 'Your role *', 'ai-awareness-day' ); ?></label>
                            <input type="text" id="role_title" name="role_title" required placeholder="<?php esc_attr_e( 'e.g. Head teacher, Deputy', 'ai-awareness-day' ); ?>">
                        </div>

                        <div class="form-group form-group-role" data-role="organisation" style="display: none;">
                            <label for="organisation"><?php esc_html_e( 'Organisation name *', 'ai-awareness-day' ); ?></label>
                            <input type="text" id="organisation" name="organisation" required placeholder="<?php esc_attr_e( 'Company or organisation', 'ai-awareness-day' ); ?>">
                        </div>
                        <div class="form-group form-group-role" data-role="organisation" style="display: none;">
                            <label for="org_type"><?php esc_html_e( 'Type *', 'ai-awareness-day' ); ?></label>
                            <select id="org_type" name="org_type" required>
                                <option value=""><?php esc_html_e( 'Select...', 'ai-awareness-day' ); ?></option>
                                <option value="sponsor"><?php esc_html_e( 'Sponsor', 'ai-awareness-day' ); ?></option>
                                <option value="tech_company"><?php esc_html_e( 'Tech company', 'ai-awareness-day' ); ?></option>
                                <option value="other"><?php esc_html_e( 'Other', 'ai-awareness-day' ); ?></option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="email"><?php esc_html_e( 'Email *', 'ai-awareness-day' ); ?></label>
                            <input type="email" id="email" name="email" required placeholder="<?php esc_attr_e( 'your@email.com', 'ai-awareness-day' ); ?>">
                        </div>

                        <div class="form-group">
                            <label for="message"><?php esc_html_e( 'Message *', 'ai-awareness-day' ); ?></label>
                            <textarea id="message" name="message" required placeholder="<?php esc_attr_e( 'Tell us how you\'d like to get involved...', 'ai-awareness-day' ); ?>"></textarea>
                        </div>

                        <?php /* Honeypot: leave empty; bots that fill all fields will be rejected */ ?>
                        <div class="aiad-honeypot" aria-hidden="true">
                            <label for="aiad_website"><?php esc_html_e( 'Website', 'ai-awareness-day' ); ?></label>
                            <input type="text" id="aiad_website" name="aiad_website" value="" tabindex="-1" autocomplete="off">
                        </div>

                        <button type="submit" class="btn-submit">
                            <?php esc_html_e( 'Send Message', 'ai-awareness-day' ); ?>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>

                        <div id="form-status" aria-live="polite" aria-atomic="true" style="margin-top:1rem; text-align:center; font-size:0.95rem;"></div>
                    </form>
                </div>

            </div>
        </div>
    </section>
    <?php endif; ?>

</main>

<?php get_footer(); ?>
