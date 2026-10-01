<?php
/**
 * Dashboard widget: Customizer + page links for Assets Pack and Press Release downloads; the Assets Pack's downloads,
 * shared by template-assets-pack.php and its editable page (patterns/assets-pack.php).
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
 * Footer / nav URL for Assets Pack: prefer published page, then Customizer URL.
 *
 * @return string
 */
function aiad_get_assets_pack_public_url(): string {
	$page = aiad_get_assets_pack_page();
	if ( $page && 'publish' === $page->post_status ) {
		return (string) get_permalink( $page );
	}
	return (string) get_theme_mod( 'aiad_asset_pack_url', '' );
}

/**
 * Ensure a published Assets Pack page exists so the footer link works.
 */
function aiad_ensure_assets_pack_page(): void {
	$stored_id = absint( get_option( 'aiad_assets_pack_page_id', 0 ) );
	if ( $stored_id ) {
		$stored = get_post( $stored_id );
		if ( $stored instanceof WP_Post && 'page' === $stored->post_type && 'trash' !== $stored->post_status ) {
			$template = (string) get_post_meta( $stored_id, '_wp_page_template', true );
			if ( 'template-assets-pack.php' !== $template ) {
				update_post_meta( $stored_id, '_wp_page_template', 'template-assets-pack.php' );
			}
			return;
		}
	}

	$existing = aiad_get_assets_pack_page();
	if ( $existing ) {
		update_option( 'aiad_assets_pack_page_id', $existing->ID, false );
		return;
	}

	$page_id = wp_insert_post(
		array(
			'post_title'   => __( 'Assets Pack', 'ai-awareness-day' ),
			'post_name'    => 'assets-pack',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
		),
		true
	);

	if ( is_wp_error( $page_id ) || ! $page_id ) {
		return;
	}

	update_post_meta( $page_id, '_wp_page_template', 'template-assets-pack.php' );
	update_option( 'aiad_assets_pack_page_id', (int) $page_id, false );
}
add_action( 'init', 'aiad_ensure_assets_pack_page', 20 );

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

/**
 * The Assets Pack's downloads, in order: the campaign's brand files, then the logo and email banners chosen in the
 * Customizer (left out while none is chosen). template-assets-pack.php prints them, and patterns/assets-pack.php turns
 * them into Download card blocks.
 *
 * @return array<int, array<string, mixed>> url or id (an attachment), label, description, btn_label.
 */
function aiad_assets_pack_downloads(): array {
	$logo_id        = absint( get_theme_mod( 'aiad_asset_logo', 0 ) );
	$banner_part_id = absint( get_theme_mod( 'aiad_asset_banner_participating', 0 ) );
	$banner_done_id = absint( get_theme_mod( 'aiad_asset_banner_participated', 0 ) );

	return array(
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup.svg',
			'label'       => __( 'Campaign lockup (ink)', 'ai-awareness-day' ),
			'description' => __( 'Use on cream or white ground. Includes Keep Humans in the Loop.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download lockup', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-reverse.svg',
			'label'       => __( 'Campaign lockup (reverse)', 'ai-awareness-day' ),
			'description' => __( 'Use on ink or dark grounds only — cream type reverse.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download reverse lockup', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-safe.svg',
			'label'       => __( 'Safe lockup (deep)', 'ai-awareness-day' ),
			'description' => __( 'Deep Safe mark for cream grounds. Never use bright as body text on cream.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Safe lockup', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-safe-bright.svg',
			'label'       => __( 'Safe lockup (bright)', 'ai-awareness-day' ),
			'description' => __( 'Bright Safe mark for ink grounds only.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Safe bright', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-smart.svg',
			'label'       => __( 'Smart lockup (deep)', 'ai-awareness-day' ),
			'description' => __( 'Deep Smart mark for cream grounds.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Smart lockup', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-smart-bright.svg',
			'label'       => __( 'Smart lockup (bright)', 'ai-awareness-day' ),
			'description' => __( 'Bright Smart mark for ink grounds only.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Smart bright', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-creative.svg',
			'label'       => __( 'Creative lockup (deep)', 'ai-awareness-day' ),
			'description' => __( 'Deep Creative mark for cream grounds.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Creative lockup', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-creative-bright.svg',
			'label'       => __( 'Creative lockup (bright)', 'ai-awareness-day' ),
			'description' => __( 'Bright Creative mark for ink grounds only.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Creative bright', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-responsible.svg',
			'label'       => __( 'Responsible lockup (deep)', 'ai-awareness-day' ),
			'description' => __( 'Deep Responsible mark for cream grounds.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Responsible lockup', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-responsible-bright.svg',
			'label'       => __( 'Responsible lockup (bright)', 'ai-awareness-day' ),
			'description' => __( 'Bright Responsible mark for ink grounds only.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Responsible bright', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-future.svg',
			'label'       => __( 'Future lockup (deep)', 'ai-awareness-day' ),
			'description' => __( 'Deep Future mark for cream grounds.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Future lockup', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-future-bright.svg',
			'label'       => __( 'Future lockup (bright)', 'ai-awareness-day' ),
			'description' => __( 'Bright Future mark for ink grounds only.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Future bright', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/icon-safe.svg',
			'label'       => __( 'Safe icon', 'ai-awareness-day' ),
			'description' => __( 'Strand mark for Safe. Pair with the Safe bright ground or deep ink.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Safe icon', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/icon-smart.svg',
			'label'       => __( 'Smart icon', 'ai-awareness-day' ),
			'description' => __( 'Strand mark for Smart.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Smart icon', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/icon-creative.svg',
			'label'       => __( 'Creative icon', 'ai-awareness-day' ),
			'description' => __( 'Strand mark for Creative.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Creative icon', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/icon-responsible.svg',
			'label'       => __( 'Responsible icon', 'ai-awareness-day' ),
			'description' => __( 'Strand mark for Responsible.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Responsible icon', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/icon-future.svg',
			'label'       => __( 'Future icon', 'ai-awareness-day' ),
			'description' => __( 'Strand mark for Future.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Future icon', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/shape-chamfer-panel.svg',
			'label'       => __( 'Chamfer panel shape', 'ai-awareness-day' ),
			'description' => __( 'Campaign chamfer signature for large panels. Use sparingly.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download chamfer panel', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/shape-chamfer-tile.svg',
			'label'       => __( 'Chamfer tile shape', 'ai-awareness-day' ),
			'description' => __( 'Campaign chamfer signature for ballot tiles and small marks.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download chamfer tile', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/poster-safe.svg',
			'label'       => __( 'Safe poster graphic', 'ai-awareness-day' ),
			'description' => __( 'A bright Safe ground and the shield mark for conversations about privacy, trust and sharing.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Safe graphic', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/poster-smart.svg',
			'label'       => __( 'Smart poster graphic', 'ai-awareness-day' ),
			'description' => __( 'The Smart strand graphic for decisions about AI acting on your behalf.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Smart graphic', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/poster-creative.svg',
			'label'       => __( 'Creative poster graphic', 'ai-awareness-day' ),
			'description' => __( 'The Creative strand graphic for attribution, authorship and making.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Creative graphic', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/poster-responsible.svg',
			'label'       => __( 'Responsible poster graphic', 'ai-awareness-day' ),
			'description' => __( 'The Responsible strand graphic for human judgement and consequential decisions.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Responsible graphic', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/brand/aiad27/poster-future.svg',
			'label'       => __( 'Future poster graphic', 'ai-awareness-day' ),
			'description' => __( 'The Future strand graphic for skills worth keeping human.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Future graphic', 'ai-awareness-day' ),
		),
		array(
			'id'          => $logo_id,
			'label'       => __( 'Logo', 'ai-awareness-day' ),
			'description' => __( 'Use in documents, presentations, and school communications.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Logo', 'ai-awareness-day' ),
		),
		array(
			'id'          => $banner_part_id,
			'label'       => __( 'Email Banner — Participating', 'ai-awareness-day' ),
			'description' => __( 'Add to your email signature to let others know your school is taking part.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Banner', 'ai-awareness-day' ),
		),
		array(
			'id'          => $banner_done_id,
			'label'       => __( 'Email Banner — Participated', 'ai-awareness-day' ),
			'description' => __( 'Swap into your email signature after the event to celebrate taking part.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Download Banner', 'ai-awareness-day' ),
		),
	);
}

/**
 * The Assets Pack's two links to the review pages (the style guide and the student slides), shown first.
 *
 * @return array<int, array<string, string>> url, label, description, btn_label, preview, badge.
 */
function aiad_assets_pack_review_pages(): array {
	return array(
		array(
			'url'         => AIAD_URI . '/assets/aiad27-review/style.html',
			'label'       => __( 'Style guide', 'ai-awareness-day' ),
			'description' => __( 'Live tokens, colour swatches, contrast tables and campaign marks.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Open style guide', 'ai-awareness-day' ),
			'preview'     => AIAD_URI . '/assets/brand/aiad27/aiad27-lockup.svg',
			'badge'       => __( 'Guide', 'ai-awareness-day' ),
		),
		array(
			'url'         => AIAD_URI . '/assets/aiad27-review/preview.html',
			'label'       => __( 'Student slides', 'ai-awareness-day' ),
			'description' => __( 'All 35 AiAd27 classroom slides — click any slide to view it full size.', 'ai-awareness-day' ),
			'btn_label'   => __( 'Open student slides', 'ai-awareness-day' ),
			'preview'     => AIAD_URI . '/assets/brand/aiad27/poster-safe.svg',
			'badge'       => __( 'Slides', 'ai-awareness-day' ),
		),
	);
}

/**
 * The editable page's placeholder (inc/editable-pages.php): the local demo's start address, empty away from the
 * presenter's laptop, so the quiet Demo link at the foot of the page is left out, as template-assets-pack.php does.
 *
 * @return array<string, string>
 */
function aiad_assets_pack_placeholders(): array {
	$demo = function_exists( 'aiad_walkthrough_demo_url' ) && '' !== aiad_walkthrough_demo_url();
	return array( '{demo_start_url}' => $demo ? esc_url( AIAD_URI . '/demo-start.php' ) : '' );
}
