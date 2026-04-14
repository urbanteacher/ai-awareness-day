<?php
/**
 * The template for displaying pages.
 *
 * @package AI_Awareness_Day
 */

get_header();
?>

<main id="main" role="main" style="padding-top: 100px;">
    <div class="container">

        <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <h1 class="section-title" style="margin-bottom: 2rem;"><?php echo esc_html( get_the_title() ); ?></h1>
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>
        <?php endwhile; ?>

    </div>
</main>

<?php get_footer(); ?>
