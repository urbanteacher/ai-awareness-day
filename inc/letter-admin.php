<?php
/**
 * WP Admin: thank-you letter generator (PDF) for form submissions.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URL to the static letter tool (theme root).
 */
function aiad_letter_generator_url( int $submission_id = 0 ): string {
	$url = trailingslashit( get_template_directory_uri() ) . 'letter-generator.html';
	if ( $submission_id > 0 ) {
		$url = add_query_arg( 'submission', $submission_id, $url );
	}
	return $url;
}

/**
 * Submenu under Form Submissions.
 */
function aiad_register_letter_admin_page(): void {
	add_submenu_page(
		'edit.php?post_type=form_submission',
		__( 'Thank-you letters', 'ai-awareness-day' ),
		__( 'Thank-you letters', 'ai-awareness-day' ),
		'edit_posts',
		'aiad-letter-generator',
		'aiad_render_letter_admin_page'
	);
}
add_action( 'admin_menu', 'aiad_register_letter_admin_page' );

/**
 * Full-width iframe (admin-only; uses same REST auth as certificates).
 */
function aiad_render_letter_admin_page(): void {
	$submission_id = isset( $_GET['submission'] ) ? absint( $_GET['submission'] ) : 0;
	$iframe_url    = aiad_letter_generator_url( $submission_id );
	?>
	<div class="wrap aiad-letter-admin-wrap">
		<h1><?php esc_html_e( 'Thank-you letters', 'ai-awareness-day' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Personalised thank-you letters for everyone who took part. Download one PDF per person, or export all participants in a single PDF.', 'ai-awareness-day' ); ?>
		</p>
		<iframe
			title="<?php esc_attr_e( 'Thank-you letter generator', 'ai-awareness-day' ); ?>"
			src="<?php echo esc_url( $iframe_url ); ?>"
			class="aiad-letter-admin-frame"
			style="width:100%;min-height:calc(100vh - 120px);border:1px solid #c3c4c7;border-radius:4px;background:#f4f4f5;"
		></iframe>
	</div>
	<?php
}

/**
 * Row action on each submission.
 *
 * @param array<string,string> $actions Row actions.
 * @param WP_Post              $post    Post object.
 * @return array<string,string>
 */
function aiad_form_submission_letter_row_action( array $actions, WP_Post $post ): array {
	if ( $post->post_type !== 'form_submission' ) {
		return $actions;
	}
	$url = add_query_arg(
		array(
			'post_type'  => 'form_submission',
			'page'       => 'aiad-letter-generator',
			'submission' => $post->ID,
		),
		admin_url( 'edit.php' )
	);
	$actions['aiad_letter'] = sprintf(
		'<a href="%s">%s</a>',
		esc_url( $url ),
		esc_html__( 'Letter', 'ai-awareness-day' )
	);
	return $actions;
}
add_filter( 'post_row_actions', 'aiad_form_submission_letter_row_action', 10, 2 );

/**
 * Sidebar on submission edit screen.
 */
function aiad_form_submission_letter_meta_box(): void {
	add_meta_box(
		'aiad_thank_you_letter',
		__( 'Thank-you letter', 'ai-awareness-day' ),
		'aiad_form_submission_letter_meta_box_render',
		'form_submission',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes_form_submission', 'aiad_form_submission_letter_meta_box' );

/**
 * @param WP_Post $post Submission post.
 */
function aiad_form_submission_letter_meta_box_render( WP_Post $post ): void {
	$url = add_query_arg(
		array(
			'post_type'  => 'form_submission',
			'page'       => 'aiad-letter-generator',
			'submission' => $post->ID,
		),
		admin_url( 'edit.php' )
	);
	?>
	<p><?php esc_html_e( 'Download a personalised thank-you letter (PDF) for this participant.', 'ai-awareness-day' ); ?></p>
	<p>
		<a class="button button-secondary" href="<?php echo esc_url( $url ); ?>">
			<?php esc_html_e( 'Open letter generator', 'ai-awareness-day' ); ?>
		</a>
	</p>
	<?php
}
