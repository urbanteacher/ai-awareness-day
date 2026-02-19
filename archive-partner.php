<?php
/**
 * Archive template for Partners (Teachers, Sponsors, Schools, Tech Companies).
 *
 * @package AI_Awareness_Day
 */

get_header();
?>

<main id="main" role="main" class="partners-archive">
    <section class="section" style="padding-top: 100px;">
        <div class="container">
            <span class="section-label"><?php esc_html_e( 'Partners', 'ai-awareness-day' ); ?></span>
            <h1 class="section-title"><?php esc_html_e( 'Teachers, Sponsors &amp; Partners', 'ai-awareness-day' ); ?></h1>
            <p class="section-desc"><?php esc_html_e( 'Schools, tech companies, sponsors, and educators supporting AI Awareness Day.', 'ai-awareness-day' ); ?></p>

            <?php
            $type_filter = isset( $_GET['partner_type'] ) ? sanitize_text_field( $_GET['partner_type'] ) : '';
            $args = array(
                'post_type'      => 'partner',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'menu_order title',
                'order'          => 'ASC',
            );
            if ( $type_filter ) {
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
            <div class="partner-filters fade-up" style="margin-bottom: 2rem;">
                <form method="get" action="<?php echo esc_url( $partner_archive_url ); ?>" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;">
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
                    <button type="submit" class="btn-submit" style="width: auto; padding: 0.5rem 1.25rem;"><?php esc_html_e( 'Filter', 'ai-awareness-day' ); ?></button>
                </form>
            </div>

            <?php if ( $partners->have_posts() ) : ?>
                <div class="partners-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 2rem; align-items: center;">
                    <?php while ( $partners->have_posts() ) : $partners->the_post();
                        $types = get_the_terms( get_the_ID(), 'partner_type' );
                        $type_name = $types && ! is_wp_error( $types ) ? $types[0]->name : '';
                        $url = get_post_meta( get_the_ID(), '_partner_url', true );
                        $tag = $url ? 'a' : 'div';
                        $attr = $url ? ' href="' . esc_url( $url ) . '" target="_blank" rel="noopener"' : '';
                        ?>
                        <<?php echo $tag; ?> class="partner-logo fade-up"<?php echo $attr; ?> style="display: flex; flex-direction: column; align-items: center; gap: 0.75rem; padding: 1rem; border-radius: var(--border-radius); transition: var(--transition);">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'medium', array( 'style' => 'max-width: 160px; height: auto; max-height: 80px; object-fit: contain;' ) ); ?>
                            <?php else : ?>
                                <span style="font-size: 1rem; font-weight: 600; color: var(--gray-700);"><?php the_title(); ?></span>
                            <?php endif; ?>
                            <?php if ( $type_name ) : ?>
                                <span style="font-size: 0.75rem; color: var(--gray-500);"><?php echo esc_html( $type_name ); ?></span>
                            <?php endif; ?>
                        </<?php echo $tag; ?>>
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
