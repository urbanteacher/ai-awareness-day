<?php
/**
 * Archive template for Partners (Teachers, Sponsors, Schools, Tech Companies).
 *
 * @package AI_Awareness_Day
 */

get_header();
?>

<main id="main" role="main" class="partners-archive">
    <section class="section section--top">
        <div class="container">
            <span class="section-label"><?php esc_html_e( 'Partners', 'ai-awareness-day' ); ?></span>
            <h1 class="section-title"><?php esc_html_e( 'Teachers, Sponsors &amp; Partners', 'ai-awareness-day' ); ?></h1>
            <p class="section-desc"><?php esc_html_e( 'Schools, tech companies, sponsors, and educators supporting AI Awareness Day.', 'ai-awareness-day' ); ?></p>

            <?php
            $type_filter = isset( $_GET['partner_type'] ) ? sanitize_text_field( wp_unslash( $_GET['partner_type'] ) ) : '';
            $valid_slugs = array();
            $partner_types = get_terms( array( 'taxonomy' => 'partner_type', 'hide_empty' => false ) );
            if ( ! is_wp_error( $partner_types ) ) {
                $valid_slugs = wp_list_pluck( $partner_types, 'slug' );
            }
            if ( $type_filter !== '' && ! in_array( $type_filter, $valid_slugs, true ) ) {
                $type_filter = '';
            }
            // Capped at 100 for performance; add pagination (e.g. paginate_links) if partner count grows.
            $args = array(
                'post_type'      => 'partner',
                'post_status'    => 'publish',
                'posts_per_page' => 100,
                'orderby'        => 'menu_order title',
                'order'          => 'ASC',
            );
            if ( $type_filter !== '' ) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'partner_type',
                        'field'    => 'slug',
                        'terms'    => $type_filter,
                    ),
                );
            }
            $partners = new WP_Query( $args );
            ?>

            <?php
            $partner_archive_url = get_post_type_archive_link( 'partner' ) ?: home_url( '/partners/' );
            ?>
            <div class="partner-filters fade-up">
                <form method="get" action="<?php echo esc_url( $partner_archive_url ); ?>">
                    <?php if ( ! get_option( 'permalink_structure' ) ) : ?>
                        <input type="hidden" name="post_type" value="partner" />
                    <?php endif; ?>
                    <label for="partner_type" class="screen-reader-text"><?php esc_html_e( 'Partner Type', 'ai-awareness-day' ); ?></label>
                    <select id="partner_type" name="partner_type">
                        <option value=""><?php esc_html_e( 'All types', 'ai-awareness-day' ); ?></option>
                        <?php
                        $types = get_terms( array( 'taxonomy' => 'partner_type', 'hide_empty' => false ) );
                        foreach ( $types as $term ) :
                            ?>
                            <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $type_filter, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-submit"><?php esc_html_e( 'Filter', 'ai-awareness-day' ); ?></button>
                </form>
            </div>

            <?php if ( $partners->have_posts() ) : ?>
                <div class="partners-grid">
                    <?php while ( $partners->have_posts() ) : $partners->the_post();
                        $types = get_the_terms( get_the_ID(), 'partner_type' );
                        $type_name = $types && ! is_wp_error( $types ) ? $types[0]->name : '';
                        $url = get_post_meta( get_the_ID(), '_partner_url', true );
                        $tag = $url ? 'a' : 'div';
                        $attr = $url ? ' href="' . esc_url( $url ) . '" target="_blank" rel="noopener"' : '';
                        ?>
                        <<?php echo esc_attr( $tag ); ?> class="partner-logo fade-up"<?php echo $attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $attr is built from esc_url ?>>
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'medium', array( 'class' => 'partner-logo__img' ) ); ?>
                            <?php else : ?>
                                <span class="partner-logo__name"><?php echo esc_html( get_the_title() ); ?></span>
                            <?php endif; ?>
                            <?php if ( $type_name ) : ?>
                                <span class="partner-logo__type"><?php echo esc_html( $type_name ); ?></span>
                            <?php endif; ?>
                        </<?php echo esc_attr( $tag ); ?>>
                    <?php endwhile; ?>
                </div>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <p class="section-desc"><?php esc_html_e( 'No partners yet. Add partners in the admin under Partners.', 'ai-awareness-day' ); ?></p>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
