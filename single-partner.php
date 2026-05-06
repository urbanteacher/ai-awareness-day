<?php
/**
 * Single template for a Partner.
 *
 * @package AI_Awareness_Day
 */

get_header();
?>

<main id="main" role="main" class="single-partner">
    <section class="section section--top">
        <div class="container">
            <?php while ( have_posts() ) : the_post();
                $types = get_the_terms( get_the_ID(), 'partner_type' );
                $type_name = $types && ! is_wp_error( $types ) ? $types[0]->name : '';
                $url = get_post_meta( get_the_ID(), '_partner_url', true );
                $stats = get_post_meta( get_the_ID(), '_partner_stats', true );
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'partner-bio' ); ?>>
                    <header class="partner-bio__header fade-up">
                        <?php if ( $type_name ) : ?>
                            <p class="section-label"><?php echo esc_html( $type_name ); ?></p>
                        <?php endif; ?>
                        <h1 class="section-title partner-bio__title"><?php echo esc_html( get_the_title() ); ?></h1>
                        <?php if ( $stats ) : ?>
                            <p class="partner-bio__stats"><?php echo esc_html( (string) $stats ); ?></p>
                        <?php endif; ?>
                    </header>

                    <div class="partner-bio__card fade-up">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="partner-bio__logo">
                                <?php if ( $url ) : ?>
                                    <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php the_post_thumbnail( 'medium', array( 'class' => 'partner-bio__logo-img' ) ); ?>
                                    </a>
                                <?php else : ?>
                                    <?php the_post_thumbnail( 'medium', array( 'class' => 'partner-bio__logo-img' ) ); ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( get_the_content() ) : ?>
                            <div class="partner-bio__content entry-content">
                                <?php the_content(); ?>
                            </div>
                        <?php endif; ?>

                        <div class="partner-bio__actions">
                            <?php if ( $url ) : ?>
                                <a href="<?php echo esc_url( $url ); ?>" class="btn-submit partner-bio__cta" target="_blank" rel="noopener noreferrer">
                                    <?php esc_html_e( 'Visit website', 'ai-awareness-day' ); ?>
                                </a>
                            <?php endif; ?>
                            <a href="<?php echo esc_url( get_post_type_archive_link( 'partner' ) ); ?>" class="partner-bio__back">
                                <?php esc_html_e( 'Back to Partners', 'ai-awareness-day' ); ?>
                            </a>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
