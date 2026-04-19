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
		$customize_assets = add_query_arg(
			array(
				'autofocus[section]' => 'aiad_assets_pack',
				'return'             => rawurlencode( admin_url() ),
			),
			admin_url( 'customize.php' )
		);
		$customize_pr = add_query_arg(
			array(
				'autofocus[section]' => 'aiad_press_release',
				'return'             => rawurlencode( admin_url() ),
			),
			admin_url( 'customize.php' )
		);

		echo '<p><strong>' . esc_html__( 'Assets Pack', 'ai-awareness-day' ) . '</strong></p>';
		echo '<p>' . esc_html__( 'Upload the logo and email banners (participating / participated) for the public Assets Pack page.', 'ai-awareness-day' ) . '</p>';
		echo '<p><a class="button button-primary" href="' . esc_url( $customize_assets ) . '">' . esc_html__( 'Customizer — Assets Pack', 'ai-awareness-day' ) . '</a></p>';

		echo '<p><strong>' . esc_html__( 'Press Release', 'ai-awareness-day' ) . '</strong></p>';
		echo '<p>' . esc_html__( 'Upload the press release PDF (or file) for the Press Release page. The footer links to that page when published.', 'ai-awareness-day' ) . '</p>';
		echo '<p><a class="button button-primary" href="' . esc_url( $customize_pr ) . '">' . esc_html__( 'Customizer — Press Release', 'ai-awareness-day' ) . '</a></p>';
	}

	if ( current_user_can( 'edit_pages' ) ) {
		echo '<p><strong>' . esc_html__( 'Public pages', 'ai-awareness-day' ) . '</strong></p>';

		if ( $page ) {
			$edit = get_edit_post_link( $page->ID );
			$view = get_permalink( $page->ID );
			if ( $edit ) {
				echo '<p><a href="' . esc_url( $edit ) . '">' . esc_html__( 'Edit Assets Pack page', 'ai-awareness-day' ) . '</a>';
				if ( $view && 'publish' === $page->post_status ) {
					echo ' · <a href="' . esc_url( $view ) . '">' . esc_html__( 'View', 'ai-awareness-day' ) . '</a>';
				}
				echo '</p>';
			}
		} else {
			echo '<p>' . esc_html__( 'No page uses the “Assets Pack” template yet.', 'ai-awareness-day' ) . ' ';
			echo '<a href="' . esc_url( admin_url( 'post-new.php?post_type=page' ) ) . '">' . esc_html__( 'Add page', 'ai-awareness-day' ) . '</a></p>';
		}

		if ( $pr_page ) {
			$edit = get_edit_post_link( $pr_page->ID );
			$view = get_permalink( $pr_page->ID );
			if ( $edit ) {
				echo '<p><a href="' . esc_url( $edit ) . '">' . esc_html__( 'Edit Press Release page', 'ai-awareness-day' ) . '</a>';
				if ( $view && 'publish' === $pr_page->post_status ) {
					echo ' · <a href="' . esc_url( $view ) . '">' . esc_html__( 'View', 'ai-awareness-day' ) . '</a>';
				}
				echo '</p>';
			}
		} else {
			echo '<p>' . esc_html__( 'No page uses the “Press Release” template yet.', 'ai-awareness-day' ) . ' ';
			echo '<a href="' . esc_url( admin_url( 'post-new.php?post_type=page' ) ) . '">' . esc_html__( 'Add page', 'ai-awareness-day' ) . '</a></p>';
		}
	}
}
