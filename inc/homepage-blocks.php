<?php
/**
 * Homepage section blocks: each front-page section as a block, so the homepage can be a page built in the block
 * editor (move, remove or add sections by moving blocks).
 *
 * Each block renders its section's existing template (template-parts/front-page/section-{slug}.php) with the same
 * arguments front-page.php passes, and adds no wrapper, so a block homepage prints exactly the markup the section
 * loop printed. Stage 2 of docs/BLOCK-THEME-MIGRATION.md.
 *
 * The blocks are registered in PHP; one small script (assets/js/homepage-section-blocks.js) gives each an editor
 * preview. When a page uses them, the theme's stylesheets load in the editor canvas so the previews look like the site.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The homepage sections that have a block: section slug => [ title, description, icon ].
 *
 * @return array<string, array{0: string, 1: string, 2: string}>
 */
function aiad_homepage_section_blocks(): array {
	return array(
		'hero'               => array( __( 'Homepage hero', 'ai-awareness-day' ), __( 'The top of the homepage: headline, countdown and the National AI Conversation panel.', 'ai-awareness-day' ), 'cover-image' ),
		'campaign'           => array( __( 'Campaign', 'ai-awareness-day' ), __( 'The campaign introduction, video and partner logos.', 'ai-awareness-day' ), 'megaphone' ),
		'schedule'           => array( __( 'Events schedule', 'ai-awareness-day' ), __( 'Upcoming live sessions.', 'ai-awareness-day' ), 'calendar-alt' ),
		'timeline'           => array( __( 'Live timeline', 'ai-awareness-day' ), __( 'The latest timeline entries, with topic filters.', 'ai-awareness-day' ), 'clock' ),
		'principles'         => array( __( 'Principles', 'ai-awareness-day' ), __( 'The five strands: Safe, Smart, Creative, Responsible, Future.', 'ai-awareness-day' ), 'star-filled' ),
		'aim'                => array( __( 'Our aim', 'ai-awareness-day' ), __( 'The campaign aim.', 'ai-awareness-day' ), 'flag' ),
		'toolkit'            => array( __( 'Toolkit', 'ai-awareness-day' ), __( 'Display board, themes, the certificate showcase and free resources.', 'ai-awareness-day' ), 'portfolio' ),
		'free_resources'     => array( __( 'Free resources', 'ai-awareness-day' ), __( 'Hand-picked free resources.', 'ai-awareness-day' ), 'media-document' ),
		'featured_resources' => array( __( 'Featured partner resources', 'ai-awareness-day' ), __( 'Resources from partners.', 'ai-awareness-day' ), 'awards' ),
		'tools'              => array( __( 'AI tools', 'ai-awareness-day' ), __( 'A selection from the AI tools directory.', 'ai-awareness-day' ), 'admin-tools' ),
		'contact'            => array( __( 'Get involved form', 'ai-awareness-day' ), __( 'The Get Involved contact form.', 'ai-awareness-day' ), 'email' ),
	);
}

/**
 * Block name for a section slug: free_resources => aiad/section-free-resources.
 */
function aiad_homepage_section_block_name( string $slug ): string {
	return 'aiad/section-' . str_replace( '_', '-', $slug );
}

/**
 * Render one homepage section, exactly as front-page.php's section loop does.
 */
function aiad_render_homepage_section( string $slug ): string {
	ob_start();
	get_template_part(
		'template-parts/front-page/section',
		$slug,
		array(
			'text_alignment_class' => aiad_get_text_alignment_class(),
			'container_class'      => aiad_get_container_width_class(),
		)
	);
	return (string) ob_get_clean();
}

/**
 * A block category for the homepage sections.
 *
 * @param array<int, array<string, mixed>> $categories Existing categories.
 * @return array<int, array<string, mixed>>
 */
function aiad_homepage_block_category( array $categories ): array {
	array_unshift(
		$categories,
		array(
			'slug'  => 'aiad-homepage',
			'title' => __( 'Homepage sections', 'ai-awareness-day' ),
			'icon'  => null,
		)
	);
	return $categories;
}
add_filter( 'block_categories_all', 'aiad_homepage_block_category' );

/**
 * Register the section blocks and their editor script.
 */
function aiad_register_homepage_section_blocks(): void {
	$script = AIAD_DIR . '/assets/js/homepage-section-blocks.js';
	wp_register_script(
		'aiad-homepage-section-blocks',
		AIAD_URI . '/assets/js/homepage-section-blocks.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-server-side-render' ),
		file_exists( $script ) ? (string) filemtime( $script ) : AIAD_VERSION,
		true
	);

	$names = array();
	foreach ( aiad_homepage_section_blocks() as $slug => $info ) {
		$name    = aiad_homepage_section_block_name( $slug );
		$names[] = $name;
		register_block_type(
			$name,
			array(
				'api_version'          => 3,
				'title'                => $info[0],
				'description'          => $info[1],
				'icon'                 => $info[2],
				'category'             => 'aiad-homepage',
				'keywords'             => array( 'homepage', 'section' ),
				'supports'             => array(
					'html'     => false,
					'multiple' => false, // The sections use fixed element IDs.
					'reusable' => false,
				),
				'editor_script_handles' => array( 'aiad-homepage-section-blocks' ),
				'render_callback'      => static function () use ( $slug ): string {
					return aiad_render_homepage_section( $slug );
				},
			)
		);
	}
	wp_add_inline_script( 'aiad-homepage-section-blocks', 'window.aiadHomepageSections = ' . wp_json_encode( $names ) . ';', 'before' );
}
add_action( 'init', 'aiad_register_homepage_section_blocks' );

/**
 * Whether a post's content uses any homepage section block.
 *
 * @param WP_Post|int|null $post Post, ID, or the current post.
 */
function aiad_post_has_homepage_sections( $post = null ): bool {
	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}
	foreach ( array_keys( aiad_homepage_section_blocks() ) as $slug ) {
		if ( has_block( aiad_homepage_section_block_name( $slug ), $post ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Whether the homepage is a block page: a static front page built from section blocks.
 */
function aiad_block_homepage_page(): ?WP_Post {
	if ( 'page' !== get_option( 'show_on_front' ) ) {
		return null;
	}
	$page = get_post( (int) get_option( 'page_on_front' ) );
	return ( $page && aiad_post_has_homepage_sections( $page ) ) ? $page : null;
}

/**
 * Render the block homepage's sections in order. Each top-level block is rendered on its own, without the_content's
 * filters (wpautop, wptexturize), so the sections print exactly what their templates print, and the whitespace between
 * blocks in the saved content does not reach the page.
 */
function aiad_render_block_homepage( WP_Post $page ): string {
	$html = '';
	foreach ( parse_blocks( $page->post_content ) as $block ) {
		if ( empty( $block['blockName'] ) ) {
			continue;
		}
		$html .= render_block( $block );
	}
	return $html;
}

/**
 * In the editor, give pages that use the section blocks the theme's front-end stylesheets, so the previews in the
 * editor canvas look like the site.
 */
function aiad_homepage_section_editor_styles(): void {
	if ( ! is_admin() || ! aiad_post_has_homepage_sections() ) {
		return;
	}
	wp_enqueue_style( 'aiad-fonts-fallback', AIAD_URI . '/assets/css/base/fonts.css', array(), AIAD_VERSION );
	$style = get_stylesheet_directory() . '/style.css';
	wp_enqueue_style( 'aiad-style', get_stylesheet_uri(), array(), file_exists( $style ) ? (string) filemtime( $style ) : AIAD_VERSION );
	if ( function_exists( 'aiad_enqueue_modular_theme_styles' ) ) {
		aiad_enqueue_modular_theme_styles();
	}
	// The front page also loads these; aiad_scripts() leaves them out of wp-admin.
	foreach ( array( 'aiad-tools' => 'components/tools.css', 'aiad-entry-figure' => 'components/entry-figure.css', 'aiad-timeline' => 'components/timeline.css' ) as $handle => $file ) {
		$path = AIAD_DIR . '/assets/css/' . $file;
		if ( file_exists( $path ) ) {
			wp_enqueue_style( $handle, AIAD_URI . '/assets/css/' . $file, array( 'aiad-style' ), (string) filemtime( $path ) );
		}
	}
	// The certificate showcase enqueues its stylesheet while it renders, which in the editor happens in a REST request.
	if ( function_exists( 'aiad_register_certificate_showcase_assets' ) ) {
		aiad_register_certificate_showcase_assets();
		wp_enqueue_style( 'aiad-certificate-showcase' );
	}
}
add_action( 'enqueue_block_assets', 'aiad_homepage_section_editor_styles' );

/**
 * Build a "Home" page from the current homepage (section order and visibility) and make it the front page.
 *
 * @return int|WP_Error The new page's ID.
 */
function aiad_create_block_homepage() {
	$blocks = array();
	foreach ( aiad_get_front_page_sections() as $slug ) {
		if ( isset( aiad_homepage_section_blocks()[ $slug ] ) && aiad_is_section_visible( $slug ) ) {
			$blocks[] = '<!-- wp:' . aiad_homepage_section_block_name( $slug ) . ' /-->';
		}
	}
	$id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => __( 'Home', 'ai-awareness-day' ),
			'post_name'    => 'home',
			'post_content' => implode( "\n\n", $blocks ),
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		return $id;
	}
	update_option( 'aiad_block_homepage_id', (int) $id, false );
	update_option( 'page_on_front', (int) $id );
	update_option( 'show_on_front', 'page' );
	return (int) $id;
}

/**
 * The block homepage created earlier, if it still exists and uses section blocks (for switching back to it).
 */
function aiad_saved_block_homepage(): ?WP_Post {
	$page = get_post( (int) get_option( 'aiad_block_homepage_id', 0 ) );
	return ( $page && 'page' === $page->post_type && 'trash' !== $page->post_status && aiad_post_has_homepage_sections( $page ) ) ? $page : null;
}

/**
 * Appearance → Block homepage: explain, create the block homepage, or switch back to the classic homepage.
 */
function aiad_block_homepage_admin_page(): void {
	add_theme_page(
		__( 'Block homepage', 'ai-awareness-day' ),
		__( 'Block homepage', 'ai-awareness-day' ),
		'edit_theme_options',
		'aiad-block-homepage',
		'aiad_render_block_homepage_admin_page'
	);
}
add_action( 'admin_menu', 'aiad_block_homepage_admin_page' );

/**
 * Handle the page's two actions.
 */
function aiad_handle_block_homepage_actions(): void {
	if ( empty( $_POST['aiad_block_homepage_action'] ) || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	check_admin_referer( 'aiad_block_homepage' );
	$action = sanitize_key( wp_unslash( $_POST['aiad_block_homepage_action'] ) );
	if ( 'create' === $action && ! aiad_block_homepage_page() && ! aiad_saved_block_homepage() ) {
		$id = aiad_create_block_homepage();
		$message = is_wp_error( $id ) ? 'error' : 'created';
	} elseif ( 'classic' === $action ) {
		update_option( 'show_on_front', 'posts' );
		$message = 'classic';
	} elseif ( 'block' === $action && aiad_saved_block_homepage() ) {
		update_option( 'page_on_front', aiad_saved_block_homepage()->ID );
		update_option( 'show_on_front', 'page' );
		$message = 'switched';
	} else {
		$message = 'error';
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'aiad-block-homepage', 'aiad_message' => $message ), admin_url( 'themes.php' ) ) );
	exit;
}
add_action( 'admin_init', 'aiad_handle_block_homepage_actions' );

/**
 * Render Appearance → Block homepage.
 */
function aiad_render_block_homepage_admin_page(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$page     = aiad_block_homepage_page();
	$messages = array(
		'created'  => __( 'The block homepage was created and is now the front page.', 'ai-awareness-day' ),
		'switched' => __( 'The block homepage is the front page again.', 'ai-awareness-day' ),
		'classic'  => __( 'The homepage is back to the classic sections (Customizer order and visibility).', 'ai-awareness-day' ),
		'error'    => __( 'That did not work. Nothing was changed.', 'ai-awareness-day' ),
	);
	$message = isset( $_GET['aiad_message'] ) ? sanitize_key( wp_unslash( $_GET['aiad_message'] ) ) : '';
	$saved    = $page ? null : aiad_saved_block_homepage();
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<?php if ( isset( $messages[ $message ] ) ) : ?>
			<div class="notice <?php echo 'error' === $message ? 'notice-error' : 'notice-success'; ?> is-dismissible"><p><?php echo esc_html( $messages[ $message ] ); ?></p></div>
		<?php endif; ?>
		<?php if ( $page ) : ?>
			<p><?php esc_html_e( 'The homepage is a page built from blocks. Move, remove or add sections in the block editor.', 'ai-awareness-day' ); ?></p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( get_edit_post_link( $page->ID ) ); ?>"><?php esc_html_e( 'Edit the homepage', 'ai-awareness-day' ); ?></a>
				<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'View it', 'ai-awareness-day' ); ?></a>
			</p>
			<form method="post" style="margin-top:2em">
				<?php wp_nonce_field( 'aiad_block_homepage' ); ?>
				<input type="hidden" name="aiad_block_homepage_action" value="classic" />
				<p><?php esc_html_e( 'Switching back shows the classic homepage again (Customizer order and visibility). The block page is kept, so you can switch back to it later.', 'ai-awareness-day' ); ?></p>
				<?php submit_button( __( 'Switch back to the classic homepage', 'ai-awareness-day' ), 'secondary', 'submit', false ); ?>
			</form>
		<?php elseif ( $saved ) : ?>
			<p><?php esc_html_e( 'The homepage is showing the classic sections (Customizer order and visibility). The block homepage you created is kept.', 'ai-awareness-day' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'aiad_block_homepage' ); ?>
				<input type="hidden" name="aiad_block_homepage_action" value="block" />
				<?php submit_button( __( 'Use the block homepage again', 'ai-awareness-day' ) ); ?>
			</form>
			<p><a href="<?php echo esc_url( get_edit_post_link( $saved->ID ) ); ?>"><?php esc_html_e( 'Edit the block homepage', 'ai-awareness-day' ); ?></a></p>
		<?php else : ?>
			<p><?php esc_html_e( 'The homepage is built from the classic sections, in the order and visibility set in the Customizer.', 'ai-awareness-day' ); ?></p>
			<p><?php esc_html_e( 'Creating the block homepage makes a "Home" page with one block per section, in the same order, with hidden sections left out, and makes it the front page. The site looks the same; you then arrange sections in the block editor.', 'ai-awareness-day' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'aiad_block_homepage' ); ?>
				<input type="hidden" name="aiad_block_homepage_action" value="create" />
				<?php submit_button( __( 'Create the block homepage', 'ai-awareness-day' ) ); ?>
			</form>
		<?php endif; ?>
	</div>
	<?php
}
