<?php
/**
 * WP Admin: certificate generator page linked to form submissions.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URL to the static certificate tool (theme root) with REST nonce for admin auth.
 */
function aiad_certificate_generator_url( int $submission_id = 0 ): string {
	return aiad_generator_html_url( 'certificate-generator.html', $submission_id );
}

/**
 * Submenu under Form Submissions.
 */
function aiad_register_certificate_admin_page(): void {
	add_submenu_page(
		'edit.php?post_type=form_submission',
		__( 'Generate certificate', 'ai-awareness-day' ),
		__( 'Certificates', 'ai-awareness-day' ),
		'edit_posts',
		'aiad-certificate-generator',
		'aiad_render_certificate_admin_page'
	);
}
add_action( 'admin_menu', 'aiad_register_certificate_admin_page' );

/**
 * Full-width iframe: same origin as WP so admin cookie authorises the REST API.
 */
function aiad_render_certificate_admin_page(): void {
	$submission_id = isset( $_GET['submission'] ) ? absint( $_GET['submission'] ) : 0;
	$iframe_url    = aiad_certificate_generator_url( $submission_id );
	?>
	<div class="wrap aiad-certificate-admin-wrap">
		<h1><?php esc_html_e( 'Participation certificates', 'ai-awareness-day' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Pick a signup from the list (or open a submission and choose “Certificate”). Choose Individual or Whole school before downloading. Logos and copy load from this WordPress site.', 'ai-awareness-day' ); ?>
		</p>
		<iframe
			title="<?php esc_attr_e( 'Certificate generator', 'ai-awareness-day' ); ?>"
			src="<?php echo esc_url( $iframe_url ); ?>"
			class="aiad-certificate-admin-frame"
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
function aiad_form_submission_certificate_row_action( array $actions, WP_Post $post ): array {
	if ( $post->post_type !== 'form_submission' ) {
		return $actions;
	}
	$url = add_query_arg(
		array(
			'post_type'  => 'form_submission',
			'page'       => 'aiad-certificate-generator',
			'submission' => $post->ID,
		),
		admin_url( 'edit.php' )
	);
	$actions['aiad_certificate'] = sprintf(
		'<a href="%s">%s</a>',
		esc_url( $url ),
		esc_html__( 'Certificate', 'ai-awareness-day' )
	);
	return $actions;
}
add_filter( 'post_row_actions', 'aiad_form_submission_certificate_row_action', 10, 2 );

/**
 * Link from the submission edit screen sidebar.
 */
function aiad_form_submission_certificate_meta_box(): void {
	add_meta_box(
		'aiad_certificate',
		__( 'Certificate', 'ai-awareness-day' ),
		'aiad_form_submission_certificate_meta_box_render',
		'form_submission',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes_form_submission', 'aiad_form_submission_certificate_meta_box' );

/**
 * @param WP_Post $post Submission post.
 */
function aiad_form_submission_certificate_meta_box_render( WP_Post $post ): void {
	$url = add_query_arg(
		array(
			'post_type'  => 'form_submission',
			'page'       => 'aiad-certificate-generator',
			'submission' => $post->ID,
		),
		admin_url( 'edit.php' )
	);
	?>
	<p><?php esc_html_e( 'Generate a participation PDF for this signup.', 'ai-awareness-day' ); ?></p>
	<p>
		<a class="button button-primary" href="<?php echo esc_url( $url ); ?>">
			<?php esc_html_e( 'Open certificate generator', 'ai-awareness-day' ); ?>
		</a>
	</p>
	<?php
}
