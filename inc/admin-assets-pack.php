<?php
/**
 * Dashboard widget: quick links to upload Assets Pack files (Customizer) and edit the public page.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Find the first page using the Assets Pack template.
 *
 * @return WP_Post|null
 */
function aiad_get_assets_pack_page(): ?WP_Post {
	$pages = get_pages(
		array(
			'meta_key'   => '_wp_page_template',
			'meta_value' => 'template-assets-pack.php',
			'number'     => 1,
			'post_status'=> array( 'publish', 'draft', 'pending', 'private' ),
		)
	);
	if ( empty( $pages ) ) {
		return null;
	}
	return $pages[0];
}

/**
 * Register the Assets Pack dashboard widget.
 */
function aiad_register_assets_pack_dashboard_widget(): void {
	if ( ! current_user_can( 'edit_theme_options' ) && ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	wp_add_dashboard_widget(
		'aiad_assets_pack_admin',
		__( 'AI Awareness Day — Assets Pack', 'ai-awareness-day' ),
		'aiad_render_assets_pack_dashboard_widget'
	);
}
add_action( 'wp_dashboard_setup', 'aiad_register_assets_pack_dashboard_widget' );

/**
 * Output dashboard widget markup.
 */
function aiad_render_assets_pack_dashboard_widget(): void {
	$page = aiad_get_assets_pack_page();

	if ( current_user_can( 'edit_theme_options' ) ) {
		$customize_url = add_query_arg(
			array(
				'autofocus[section]' => 'aiad_assets_pack',
				'return'             => rawurlencode( admin_url() ),
			),
			admin_url( 'customize.php' )
		);
		echo '<p><strong>' . esc_html__( 'Upload downloads', 'ai-awareness-day' ) . '</strong></p>';
		echo '<p>' . esc_html__( 'Add the logo and email banners (participating / participated) that visitors can download.', 'ai-awareness-day' ) . '</p>';
		echo '<p><a class="button button-primary" href="' . esc_url( $customize_url ) . '">' . esc_html__( 'Open Customizer — Assets Pack', 'ai-awareness-day' ) . '</a></p>';
	}

	if ( current_user_can( 'edit_pages' ) ) {
		echo '<p><strong>' . esc_html__( 'Public page', 'ai-awareness-day' ) . '</strong></p>';
		if ( $page ) {
			$edit = get_edit_post_link( $page->ID );
			$view = get_permalink( $page->ID );
			if ( $edit ) {
				echo '<p><a href="' . esc_url( $edit ) . '">' . esc_html__( 'Edit Assets Pack page', 'ai-awareness-day' ) . '</a></p>';
			}
			if ( $view && 'publish' === $page->post_status ) {
				echo '<p><a href="' . esc_url( $view ) . '">' . esc_html__( 'View page', 'ai-awareness-day' ) . '</a></p>';
			}
		} else {
			echo '<p>' . esc_html__( 'Create a page and set its template to “Assets Pack” so teachers can download files.', 'ai-awareness-day' ) . '</p>';
			echo '<p><a class="button" href="' . esc_url( admin_url( 'post-new.php?post_type=page' ) ) . '">' . esc_html__( 'Add new page', 'ai-awareness-day' ) . '</a></p>';
		}
	}
}
