<?php
/**
 * Single template for a Partner.
 *
 * @package AI_Awareness_Day
 */

get_header();
?>

<main id="main" role="main" class="single-partner">
    <section class="section" style="padding-top: 100px;">
        <div class="container" style="max-width: 600px; text-align: center;">
            <?php while ( have_posts() ) : the_post();
                $types = get_the_terms( get_the_ID(), 'partner_type' );
                $type_name = $types && ! is_wp_error( $types ) ? $types[0]->name : '';
                $url = get_post_meta( get_the_ID(), '_partner_url', true );
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <?php if ( $type_name ) : ?>
                        <p class="section-label" style="margin-bottom: 0.5rem;"><?php echo esc_html( $type_name ); ?></p>
                    <?php endif; ?>
                    <h1 class="section-title"><?php echo esc_html( get_the_title() ); ?></h1>
                    <?php if ( has_post_thumbnail() ) : ?>
                        <figure style="margin: 2rem auto; max-width: 240px;">
                            <?php if ( $url ) : ?>
                                <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php the_post_thumbnail( 'medium' ); ?></a>
                            <?php else : ?>
                                <?php the_post_thumbnail( 'medium' ); ?>
                            <?php endif; ?>
                        </figure>
                    <?php endif; ?>
                    <?php if ( get_the_content() ) : ?>
                        <div class="entry-content" style="margin-top: 1rem;"><?php the_content(); ?></div>
                    <?php endif; ?>
                    <?php if ( $url ) : ?>
                        <p style="margin-top: 1.5rem;">
                            <a href="<?php echo esc_url( $url ); ?>" class="btn-submit" style="display: inline-flex; width: auto;" target="_blank" rel="noopener"><?php esc_html_e( 'Visit website', 'ai-awareness-day' ); ?></a>
                        </p>
                    <?php endif; ?>
                    <p style="margin-top: 2rem;">
                        <a href="<?php echo esc_url( get_post_type_archive_link( 'partner' ) ); ?>"><?php esc_html_e( 'Back to Partners', 'ai-awareness-day' ); ?></a>
                    </p>
                </article>
            <?php endwhile; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
