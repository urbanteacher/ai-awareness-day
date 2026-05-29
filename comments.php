<?php
/**
 * The template for displaying comments.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * If the current post is protected by a password and the visitor has not yet
 * entered the password, return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="comments-area">

	<?php if ( have_comments() ) : ?>
		<h2 class="comments-area__title">
			<?php
			$aiad_comment_count = get_comments_number();
			if ( '1' === (string) $aiad_comment_count ) {
				esc_html_e( 'One comment', 'ai-awareness-day' );
			} else {
				printf(
					/* translators: %s: comment count number. */
					esc_html( _n( '%s comment', '%s comments', $aiad_comment_count, 'ai-awareness-day' ) ),
					esc_html( number_format_i18n( $aiad_comment_count ) )
				);
			}
			?>
		</h2>

		<ol class="comments-area__list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 44,
				)
			);
			?>
		</ol>

		<?php
		the_comments_navigation(
			array(
				'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Older comments', 'ai-awareness-day' ) . '</span>',
				'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Newer comments', 'ai-awareness-day' ) . '</span>',
			)
		);
		?>

		<?php if ( ! comments_open() ) : ?>
			<p class="comments-area__closed"><?php esc_html_e( 'Comments are closed.', 'ai-awareness-day' ); ?></p>
		<?php endif; ?>
	<?php endif; ?>

	<?php
	comment_form();
	?>

</section>
