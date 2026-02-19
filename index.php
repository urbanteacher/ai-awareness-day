<?php
/**
 * The main template file.
 *
 * @package AI_Awareness_Day
 */

get_header();
?>

<main id="main" role="main" style="padding-top: 100px;">
    <div class="container">

        <?php if ( have_posts() ) : ?>

            <?php while ( have_posts() ) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="margin-bottom: 3rem;">
                <h2 style="margin-bottom: 0.5rem;">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h2>
                <p style="color: var(--gray-500); font-size: 0.9rem; margin-bottom: 1rem;">
                    <?php echo esc_html( get_the_date() ); ?>
                </p>
                <div>
                    <?php the_excerpt(); ?>
                </div>
            </article>
            <?php endwhile; ?>

            <?php the_posts_navigation(); ?>

        <?php else : ?>
            <p><?php esc_html_e( 'No posts found.', 'ai-awareness-day' ); ?></p>
        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>
