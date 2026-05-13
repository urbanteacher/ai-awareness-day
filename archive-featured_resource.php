<?php
/**
 * Archive: Resources from other organisations (featured / external).
 * Same layout and filtering as Resources; links go to external URL instead of download.
 *
 * @package AI_Awareness_Day
 */

get_header();
?>

<main id="main" role="main" class="resources-archive featured-resources-archive">
    <section class="section pt-100">
        <div class="container">
            <span class="section-label"><?php esc_html_e( 'Handpicked resources', 'ai-awareness-day' ); ?></span>
            <h1 class="section-title"><?php esc_html_e( 'Curated resources', 'ai-awareness-day' ); ?></h1>
            <p class="section-desc"><?php esc_html_e( 'AI isn\'t just something we read about, it\'s something we can play with, question, and understand. Below is a curated selection of interactive AI games and learning tools from trusted organisations like Google, the BBC, Microsoft, and the Turing Test.', 'ai-awareness-day' ); ?></p>

            <?php
            // Display theme badges (same as Resources + homepage toolkit section)
            $featured_resources_url = get_post_type_archive_link( 'featured_resource' );
            $theme_terms = get_terms( array( 'taxonomy' => 'resource_principle', 'hide_empty' => false ) );
            if ( $featured_resources_url && $theme_terms && ! is_wp_error( $theme_terms ) ) :
                ?>
                <div class="resources-theme-badges fade-up mt-1-5rem mb-2rem">
                    <div class="themes-links">
                        <?php foreach ( $theme_terms as $term ) :
                            $url = add_query_arg( 'principle', $term->slug, $featured_resources_url );
                            // Map term slug to Customizer badge setting (normalize to lowercase)
                            // Use same simple approach as display board images (which work reliably)
                            $badge_slug = strtolower( $term->slug );
                            $theme_badge_id = absint( get_theme_mod( 'aiad_badge_' . $badge_slug, 0 ) );
                            $theme_badge_src = $theme_badge_id ? wp_get_attachment_image_url( $theme_badge_id, 'thumbnail' ) : '';
                            ?>
                            <a href="<?php echo esc_url( $url ); ?>" class="theme-link">
                                <?php if ( $theme_badge_src ) : ?>
                                    <span class="theme-link__badge">
                                        <img src="<?php echo esc_url( $theme_badge_src ); ?>" alt="" aria-hidden="true" class="theme-link__badge-img" />
                                    </span>
                                <?php endif; ?>
                                <span class="theme-link__label"><?php echo esc_html( $term->name ); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            $principle_filter = isset( $_GET['principle'] ) ? sanitize_text_field( wp_unslash( $_GET['principle'] ) ) : '';
            $duration_filter  = isset( $_GET['duration'] ) ? sanitize_text_field( wp_unslash( $_GET['duration'] ) ) : '';
            $legacy_type_filter = isset( $_GET['resource_type'] ) ? sanitize_text_field( wp_unslash( $_GET['resource_type'] ) ) : '';
            if ( $legacy_type_filter && $duration_filter === '' && function_exists( 'aiad_legacy_resource_type_slug_to_duration_slug' ) ) {
                $mapped = aiad_legacy_resource_type_slug_to_duration_slug( $legacy_type_filter );
                if ( $mapped ) {
                    $duration_filter = $mapped;
                }
            }
            $activity_filter  = isset( $_GET['activity_type'] ) ? sanitize_text_field( wp_unslash( $_GET['activity_type'] ) ) : '';

            $args = array(
                'post_type'      => 'featured_resource',
                'post_status'    => 'publish',
                'posts_per_page' => 200, // Limit to 200 resources for performance (can be increased if needed)
                'orderby'        => 'menu_order title',
                'order'          => 'ASC',
            );
            $tax_query = array();
            if ( $principle_filter ) {
                $tax_query[] = array(
                    'taxonomy' => 'resource_principle',
                    'field'    => 'slug',
                    'terms'    => $principle_filter,
                );
            }
            if ( $duration_filter ) {
                $tax_query[] = array(
                    'taxonomy' => 'resource_duration',
                    'field'    => 'slug',
                    'terms'    => $duration_filter,
                );
            }
            if ( $activity_filter ) {
                $tax_query[] = array(
                    'taxonomy' => 'activity_type',
                    'field'    => 'slug',
                    'terms'    => $activity_filter,
                );
            }
            if ( ! empty( $tax_query ) ) {
                $args['tax_query'] = array_merge( array( 'relation' => 'AND' ), $tax_query );
            }

            $resources = new WP_Query( $args );
            $has_resources = $resources->post_count > 0;
            ?>

            <div class="resource-filters fade-up">
                <?php
                $featured_filter_action = get_post_type_archive_link( 'featured_resource' ) ?: home_url( '/from-partners/' );
                ?>
                <form method="get" class="resource-filter-form" action="<?php echo esc_url( $featured_filter_action ); ?>">
                    <?php if ( ! get_option( 'permalink_structure' ) ) : ?>
                        <input type="hidden" name="post_type" value="featured_resource" />
                    <?php endif; ?>
                    <div class="resource-filter-group">
                        <label for="principle" class="resource-filter-label"><?php esc_html_e( 'Theme', 'ai-awareness-day' ); ?></label>
                        <select id="principle" name="principle" class="resource-filter-select" data-filter="true">
                            <option value=""><?php esc_html_e( 'All themes', 'ai-awareness-day' ); ?></option>
                            <?php
                            $themes = get_terms( array( 'taxonomy' => 'resource_principle', 'hide_empty' => false ) );
                            foreach ( $themes as $term ) :
                                ?>
                                <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $principle_filter, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="resource-filter-group">
                        <label for="duration" class="resource-filter-label"><?php esc_html_e( 'Session length', 'ai-awareness-day' ); ?></label>
                        <select id="duration" name="duration" class="resource-filter-select" data-filter="true">
                            <option value=""><?php esc_html_e( 'All session lengths', 'ai-awareness-day' ); ?></option>
                            <?php
                            $durations = get_terms( array( 'taxonomy' => 'resource_duration', 'hide_empty' => false ) );
                            foreach ( $durations as $term ) :
                                $badge_label = function_exists( 'aiad_duration_badge_label' ) ? aiad_duration_badge_label( $term ) : $term->name;
                                ?>
                                <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $duration_filter, $term->slug ); ?>><?php echo esc_html( $badge_label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="resource-filter-group">
                        <label for="activity_type" class="resource-filter-label"><?php esc_html_e( 'Format', 'ai-awareness-day' ); ?></label>
                        <select id="activity_type" name="activity_type" class="resource-filter-select" data-filter="true">
                            <option value=""><?php esc_html_e( 'All formats', 'ai-awareness-day' ); ?></option>
                            <?php
                            $activity_terms = get_terms( array( 'taxonomy' => 'activity_type', 'hide_empty' => false ) );
                            if ( $activity_terms && ! is_wp_error( $activity_terms ) ) :
                                foreach ( $activity_terms as $term ) :
                                    ?>
                                    <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $activity_filter, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
                                <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div class="resource-filter-group resource-filter-group--submit">
                        <span class="resource-filter-label" aria-hidden="true">&nbsp;</span>
                        <a href="<?php echo esc_url( $featured_filter_action ); ?>" class="resource-filters-clear resource-filter-submit"><?php esc_html_e( 'Clear filters', 'ai-awareness-day' ); ?></a>
                    </div>
                </form>
            </div>

            <div class="resources-loading" style="display:none" aria-live="polite"><?php esc_html_e( 'Loading…', 'ai-awareness-day' ); ?></div>
            <div class="resources-grid">
            <?php if ( $resources->have_posts() ) : ?>
                    <?php while ( $resources->have_posts() ) : $resources->the_post();
                        $themes     = get_the_terms( get_the_ID(), 'resource_principle' );
                        $durations  = get_the_terms( get_the_ID(), 'resource_duration' );
                        $duration_labels = ( $durations && ! is_wp_error( $durations ) && function_exists( 'aiad_resource_duration_term_labels' ) )
                            ? aiad_resource_duration_term_labels( $durations )
                            : array();
                        $theme_name = $themes && ! is_wp_error( $themes ) ? $themes[0]->name : '';
                        $duration_name = ! empty( $duration_labels ) ? $duration_labels[0] : '';
                        $url      = get_post_meta( get_the_ID(), '_featured_resource_url', true );
                        $org_name = get_post_meta( get_the_ID(), '_featured_resource_org_name', true );
                        $org_url  = get_post_meta( get_the_ID(), '_featured_resource_org_url', true );
                        $link    = $url ? $url : get_permalink();
                        $activity_terms = get_the_terms( get_the_ID(), 'activity_type' );
                        $theme_slug     = in_array( strtolower( $theme_name ), array( 'safe', 'smart', 'creative', 'responsible', 'future' ), true )
                            ? strtolower( $theme_name ) : '';
                        $format_label   = ( $activity_terms && ! is_wp_error( $activity_terms ) && ! empty( $activity_terms ) )
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
                        $article_class  = 'resource-card resource-card--pointed fade-up';
                        if ( $theme_slug ) {
                            $article_class .= ' resource-card--' . $theme_slug;
                        }
                        ?>
                        <article class="<?php echo esc_attr( $article_class ); ?>">
                            <a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener noreferrer"
                                class="resource-card__hero" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <?php the_post_thumbnail( 'medium_large', array( 'class' => 'resource-card__hero-img' ) ); ?>
                                <?php else : ?>
                                    <div class="resource-card__hero-img" style="background:#111;" aria-hidden="true"></div>
                                <?php endif; ?>

                                <div class="resource-card__wedge" aria-hidden="true"></div>
                                <div class="resource-card__fade"  aria-hidden="true"></div>

                                <?php if ( $theme_name ) : ?>
                                    <span class="resource-card__theme-label" aria-hidden="true"><?php echo esc_html( strtoupper( $theme_name ) ); ?></span>
                                <?php endif; ?>

                                <?php if ( $duration_str ) : ?>
                                    <span class="resource-card__duration-label" aria-hidden="true"><?php echo esc_html( $duration_str ); ?></span>
                                <?php endif; ?>

                                <h3 class="resource-card__title-overlay"><?php echo esc_html( html_entity_decode( get_the_title(), ENT_QUOTES, 'UTF-8' ) ); ?></h3>
                            </a>

                            <div class="resource-card__body">
                                <span class="resource-card__format-label"><?php echo esc_html( $format_label ); ?></span>
                                <a href="<?php echo esc_url( $link ); ?>" class="resource-card__title-below" <?php if ( $url ) echo 'target="_blank" rel="noopener noreferrer"'; ?>><?php echo esc_html( html_entity_decode( get_the_title(), ENT_QUOTES, 'UTF-8' ) ); ?></a>
                                <?php
                                $summary = '';
                                if ( has_excerpt() ) {
                                    $summary = get_the_excerpt();
                                } else {
                                    $content = get_the_content( '' );
                                    if ( $content ) {
                                        $summary = wp_trim_words( wp_strip_all_tags( $content ), 30 );
                                    }
                                }
                                if ( $summary ) : ?>
                                    <p class="resource-card__excerpt"><?php echo esc_html( $summary ); ?></p>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            <?php endif; ?>
            </div>
            <p class="section-desc resources-empty-message"<?php echo $has_resources ? ' style="display:none"' : ''; ?>><?php esc_html_e( 'No resources found. Try changing the filters or add resources in the admin under Resources from partners.', 'ai-awareness-day' ); ?></p>
        </div>
    </section>
</main>

<?php get_footer(); ?>
