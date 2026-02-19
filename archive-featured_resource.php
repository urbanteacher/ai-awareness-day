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
    <section class="section" class="pt-100">
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
                <div class="resources-theme-badges fade-up" style="margin-top: 1.5rem; margin-bottom: 2rem;">
                    <div class="themes-links">
                        <?php foreach ( $theme_terms as $term ) :
                            $url = add_query_arg( 'principle', $term->slug, $featured_resources_url );
                            $theme_badge_id = absint( get_theme_mod( 'aiad_badge_' . $term->slug, 0 ) );
                            $theme_badge_src = $theme_badge_id ? wp_get_attachment_image_url( $theme_badge_id, 'thumbnail' ) : '';
                            ?>
                            <a href="<?php echo esc_url( $url ); ?>" class="theme-link">
                                <?php if ( $theme_badge_src ) : ?>
                                    <span class="theme-link__badge">
                                        <img src="<?php echo esc_url( $theme_badge_src ); ?>" alt="" aria-hidden="true" class="theme-link__badge-img" aria-hidden="true" />
                                    </span>
                                <?php endif; ?>
                                <span class="theme-link__label"><?php echo esc_html( $term->name ); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            $type_filter      = isset( $_GET['resource_type'] ) ? sanitize_text_field( $_GET['resource_type'] ) : '';
            $principle_filter = isset( $_GET['principle'] ) ? sanitize_text_field( $_GET['principle'] ) : '';
            $duration_filter  = isset( $_GET['duration'] ) ? sanitize_text_field( $_GET['duration'] ) : '';
            $activity_filter  = isset( $_GET['activity_type'] ) ? sanitize_text_field( $_GET['activity_type'] ) : '';

            $args = array(
                'post_type'      => 'featured_resource',
                'post_status'    => 'publish',
                'posts_per_page' => 200, // Limit to 200 resources for performance (can be increased if needed)
                'orderby'        => 'menu_order title',
                'order'          => 'ASC',
            );
            $tax_query = array();
            if ( $type_filter ) {
                $tax_query[] = array(
                    'taxonomy' => 'resource_type',
                    'field'    => 'slug',
                    'terms'    => $type_filter,
                );
            }
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
                        <label for="resource_type" class="resource-filter-label"><?php esc_html_e( 'Resource Type', 'ai-awareness-day' ); ?></label>
                        <select id="resource_type" name="resource_type" class="resource-filter-select" data-filter="true">
                            <option value=""><?php esc_html_e( 'All types', 'ai-awareness-day' ); ?></option>
                            <?php
                            $types = get_terms( array( 'taxonomy' => 'resource_type', 'hide_empty' => false ) );
                            foreach ( $types as $term ) :
                                ?>
                                <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $type_filter, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
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
                        <label for="activity_type" class="resource-filter-label"><?php esc_html_e( 'Activity Type', 'ai-awareness-day' ); ?></label>
                        <select id="activity_type" name="activity_type" class="resource-filter-select" data-filter="true">
                            <option value=""><?php esc_html_e( 'All activity types', 'ai-awareness-day' ); ?></option>
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
                                        <?php if ( $org_name ) : ?>
                                            <span class="resource-card__pill resource-card__pill--org"><?php echo esc_html( $org_name ); ?></span>
                                        <?php endif; ?>
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
                <?php wp_reset_postdata(); ?>
            <?php endif; ?>
            </div>
            <p class="section-desc resources-empty-message"<?php echo $resources->have_posts() ? ' style="display:none"' : ''; ?>><?php esc_html_e( 'No resources found. Try changing the filters or add resources in the admin under Resources from partners.', 'ai-awareness-day' ); ?></p>
        </div>
    </section>
</main>

<?php get_footer(); ?>
