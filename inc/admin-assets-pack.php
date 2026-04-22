<?php
/**
 * Dashboard widget: Customizer + page links for Assets Pack and Press Release downloads.
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
			'meta_key'    => '_wp_page_template',
			'meta_value'  => 'template-assets-pack.php',
			'number'      => 1,
			'post_status' => array( 'publish', 'draft', 'pending', 'private' ),
		)
	);
	if ( empty( $pages ) ) {
		return null;
	}
	return $pages[0];
}

/**
 * Admin URL to open the Customizer focused on a section.
 *
 * @param string $section_id Section ID registered with the Customizer.
 */
function aiad_customizer_section_admin_url( string $section_id ): string {
	return add_query_arg(
		array(
			'autofocus[section]' => $section_id,
			'return'             => rawurlencode( admin_url() ),
		),
		admin_url( 'customize.php' )
	);
}

/**
 * Dashboard widget: edit (and optional view) links for a page.
 */
function aiad_echo_dashboard_page_edit_row( WP_Post $post, string $edit_link_text ): void {
	$edit = get_edit_post_link( $post->ID );
	$view = get_permalink( $post->ID );
	if ( ! $edit ) {
		return;
	}
	echo '<p><a href="' . esc_url( $edit ) . '">' . esc_html( $edit_link_text ) . '</a>';
	if ( $view && 'publish' === $post->post_status ) {
		echo ' · <a href="' . esc_url( $view ) . '">' . esc_html__( 'View', 'ai-awareness-day' ) . '</a>';
	}
	echo '</p>';
}

/**
 * Register the downloads dashboard widget.
 */
function aiad_register_assets_pack_dashboard_widget(): void {
	if ( ! current_user_can( 'edit_theme_options' ) && ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	wp_add_dashboard_widget(
		'aiad_assets_pack_admin',
		__( 'AI Awareness Day — Downloads', 'ai-awareness-day' ),
		'aiad_render_assets_pack_dashboard_widget'
	);
}
add_action( 'wp_dashboard_setup', 'aiad_register_assets_pack_dashboard_widget' );

/**
 * Output dashboard widget markup (Assets Pack + Press Release).
 */
function aiad_render_assets_pack_dashboard_widget(): void {
	$page    = aiad_get_assets_pack_page();
	$pr_page = function_exists( 'aiad_get_press_release_page' ) ? aiad_get_press_release_page() : null;

	if ( current_user_can( 'edit_theme_options' ) ) {
		echo '<p><strong>' . esc_html__( 'Footer links (Newsletter, Assets Pack page, Implementation Guide)', 'ai-awareness-day' ) . '</strong></p>';
		echo '<p>' . esc_html__( 'These are URL fields only—not file uploads. Upload files in Assets Pack / Press Release below.', 'ai-awareness-day' ) . '</p>';
		echo '<p><a class="button" href="' . esc_url( aiad_customizer_section_admin_url( 'aiad_footer_resource_links' ) ) . '">' . esc_html__( 'Customizer — Footer resource links', 'ai-awareness-day' ) . '</a></p>';

		echo '<p><strong>' . esc_html__( 'Assets Pack (uploads)', 'ai-awareness-day' ) . '</strong></p>';
		echo '<p>' . esc_html__( 'Logo and email banners for the Assets Pack download page.', 'ai-awareness-day' ) . '</p>';
		echo '<p><a class="button button-primary" href="' . esc_url( aiad_customizer_section_admin_url( 'aiad_assets_pack' ) ) . '">' . esc_html__( 'Customizer — Assets Pack files', 'ai-awareness-day' ) . '</a></p>';

		echo '<p><strong>' . esc_html__( 'Press Release (upload)', 'ai-awareness-day' ) . '</strong></p>';
		echo '<p>' . esc_html__( 'Press release PDF for the Press Release page. Footer uses the published page when possible.', 'ai-awareness-day' ) . '</p>';
		echo '<p><a class="button button-primary" href="' . esc_url( aiad_customizer_section_admin_url( 'aiad_press_release' ) ) . '">' . esc_html__( 'Customizer — Press Release file', 'ai-awareness-day' ) . '</a></p>';
	}

	if ( current_user_can( 'edit_pages' ) ) {
		echo '<p><strong>' . esc_html__( 'Public pages', 'ai-awareness-day' ) . '</strong></p>';

		if ( $page ) {
			aiad_echo_dashboard_page_edit_row( $page, __( 'Edit Assets Pack page', 'ai-awareness-day' ) );
		} else {
			echo '<p>' . esc_html__( 'No page uses the “Assets Pack” template yet.', 'ai-awareness-day' ) . ' ';
			echo '<a href="' . esc_url( admin_url( 'post-new.php?post_type=page' ) ) . '">' . esc_html__( 'Add page', 'ai-awareness-day' ) . '</a></p>';
		}

		if ( $pr_page ) {
			aiad_echo_dashboard_page_edit_row( $pr_page, __( 'Edit Press Release page', 'ai-awareness-day' ) );
		} else {
			echo '<p>' . esc_html__( 'No page uses the “Press Release” template yet.', 'ai-awareness-day' ) . ' ';
			echo '<a href="' . esc_url( admin_url( 'post-new.php?post_type=page' ) ) . '">' . esc_html__( 'Add page', 'ai-awareness-day' ) . '</a></p>';
		}
	}
}
