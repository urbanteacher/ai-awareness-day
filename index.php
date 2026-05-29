<?php
/**
 * The main template file (blog index and archive fallback).
 *
 * @package AI_Awareness_Day
 */

get_header();
?>

<main id="main" role="main" class="blog-index section">
	<div class="container blog-index__inner">

		<?php if ( have_posts() ) : ?>

			<?php if ( is_home() && ! is_front_page() ) : ?>
				<header class="blog-index__header">
					<h1 class="section-title"><?php single_post_title(); ?></h1>
				</header>
			<?php elseif ( is_archive() ) : ?>
				<header class="blog-index__header">
					<h1 class="section-title"><?php the_archive_title(); ?></h1>
					<?php the_archive_description( '<div class="blog-index__description">', '</div>' ); ?>
				</header>
			<?php elseif ( is_search() ) : ?>
				<header class="blog-index__header">
					<h1 class="section-title">
						<?php
						printf(
							/* translators: %s: search query. */
							esc_html__( 'Search results for: %s', 'ai-awareness-day' ),
							'<span>' . esc_html( get_search_query() ) . '</span>'
						);
						?>
					</h1>
				</header>
			<?php endif; ?>

			<div class="blog-index__list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'blog-post-card' ); ?>>
						<h2 class="blog-post-card__title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h2>
						<p class="blog-post-card__meta">
							<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						</p>
						<div class="blog-post-card__excerpt">
							<?php the_excerpt(); ?>
						</div>
					</article>
					<?php
				endwhile;
				?>
			</div>

			<?php the_posts_navigation(); ?>

		<?php else : ?>
			<p class="blog-index__empty"><?php esc_html_e( 'No posts found.', 'ai-awareness-day' ); ?></p>
		<?php endif; ?>

	</div>
</main>

<?php get_footer(); ?>
