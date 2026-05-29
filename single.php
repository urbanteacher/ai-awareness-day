<?php
/**
 * Single blog post template.
 *
 * @package AI_Awareness_Day
 */

get_header();
?>

<main id="main" role="main" class="single-post section">
	<div class="container single-post__inner">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-post-entry' ); ?>>
				<header class="single-post-entry__header">
					<?php if ( is_sticky() ) : ?>
						<p class="single-post-entry__badge"><?php esc_html_e( 'Featured', 'ai-awareness-day' ); ?></p>
					<?php endif; ?>
					<h1 class="single-post-entry__title section-title"><?php the_title(); ?></h1>
					<p class="single-post-entry__meta">
						<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
					</p>
				</header>
				<div class="entry-content entry-content--post">
					<?php
					the_content();
					wp_link_pages(
						array(
							'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Page', 'ai-awareness-day' ) . '">' . esc_html__( 'Pages:', 'ai-awareness-day' ),
							'after'  => '</nav>',
						)
					);
					?>
				</div>
			</article>
			<?php
			the_post_navigation(
				array(
					'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous', 'ai-awareness-day' ) . '</span><span class="nav-title">%title</span>',
					'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next', 'ai-awareness-day' ) . '</span><span class="nav-title">%title</span>',
				)
			);

			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</div>
</main>

<?php
get_footer();
