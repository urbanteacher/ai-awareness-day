<?php
/**
 * Theme pages that can be edited in blocks: pages the theme builds itself at their own address (a PHP template with
 * its words in the theme), which Pages → Theme pages can turn into an ordinary page of blocks with the same words
 * and design. While that page is published, the address shows it; going back shows the built-in page again and
 * keeps the edited one as a draft.
 *
 * Each page's blocks come from a pattern (patterns/{slug}.php, built with inc/block-markup.php), also offered when a
 * new page is created. Its outermost block is the page's main group, so the editor canvas is styled as the site is;
 * templates/page-{slug}.html holds only the header, the post content and the footer.
 *
 * What changes on its own is written as placeholders ({opens}...) filled in when the page renders, and explained in
 * a notice in its editor. A link whose placeholder is empty is left out (the walkthrough's demo link, away from the
 * presenter's laptop).
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The theme pages that can be edited in blocks: slug => settings.
 *
 * title:        the page's title when it is created.
 * builtin:      the PHP template that renders the built-in page.
 * style:        the handle of the page's stylesheet (assets/css/pages/{slug}.css), loaded in its editor too.
 * placeholders: a function returning placeholder => HTML, or ''.
 * note:         a sentence for the editor notice, or ''.
 *
 * @return array<string, array<string, string>>
 */
function aiad_editable_pages(): array {
	return array(
		'national-conversation' => array(
			'title'        => __( 'The National AI Conversation', 'ai-awareness-day' ),
			'builtin'      => 'page-national-conversation.php',
			'style'        => 'aiad-national-conversation',
			'placeholders' => 'aiad_national_conversation_placeholders',
			'note'         => __( 'The buttons change on the day the conversation opens, so they are a block of their own.', 'ai-awareness-day' ),
		),
		'walkthrough'           => array(
			'title'        => __( 'Platform walkthrough', 'ai-awareness-day' ),
			'builtin'      => 'page-walkthrough.php',
			'style'        => 'aiad-walkthrough',
			'placeholders' => 'aiad_walkthrough_placeholders',
			'note'         => '',
		),
	);
}

/**
 * A theme page's editable page, if it has been created and is published.
 *
 * @param string $slug A slug from aiad_editable_pages().
 */
function aiad_editable_page_post( string $slug ): ?WP_Post {
	static $pages = array();
	if ( ! array_key_exists( $slug, $pages ) ) {
		$found          = get_page_by_path( $slug );
		$pages[ $slug ] = ( $found && 'publish' === $found->post_status && has_blocks( $found ) ) ? $found : null;
	}
	return $pages[ $slug ];
}

/**
 * The editable theme page this request shows, or ''.
 */
function aiad_current_editable_page(): string {
	if ( is_admin() || ! is_page() ) {
		return '';
	}
	foreach ( array_keys( aiad_editable_pages() ) as $slug ) {
		$page = aiad_editable_page_post( $slug );
		if ( $page && get_queried_object_id() === $page->ID ) {
			return $slug;
		}
	}
	return '';
}

/**
 * The slug of the theme page being edited in the block editor, or ''.
 */
function aiad_edited_editable_page(): string {
	$post = is_admin() ? get_post() : null;
	return ( $post && 'page' === $post->post_type && isset( aiad_editable_pages()[ $post->post_name ] ) ) ? $post->post_name : '';
}

/**
 * A theme page's placeholders and what they show now.
 *
 * @param string $slug A slug from aiad_editable_pages().
 * @return array<string, string>
 */
function aiad_editable_page_placeholders( string $slug ): array {
	$callback = aiad_editable_pages()[ $slug ]['placeholders'] ?? '';
	return ( $callback && function_exists( $callback ) ) ? (array) call_user_func( $callback ) : array();
}

/**
 * A theme page's blocks, as its pattern builds them. Read from the pattern file itself, since WordPress's list of
 * theme patterns can lag behind a deploy (aiad_homepage_pattern_content()).
 *
 * @param string $slug A slug from aiad_editable_pages().
 */
function aiad_editable_page_content( string $slug ): string {
	$file = AIAD_DIR . '/patterns/' . sanitize_key( $slug ) . '.php';
	if ( ! isset( aiad_editable_pages()[ $slug ] ) || ! is_readable( $file ) ) {
		return '';
	}
	ob_start();
	include $file;
	return (string) ob_get_clean();
}

/**
 * On an editable theme page: fill in the placeholders (leaving out a link whose placeholder is empty), name each
 * section and aside by its first heading (aria-labelledby), which a core group cannot carry, and give the theme's
 * own images their size, which an image with no attachment has no other way to get. They are below the fold on
 * these pages, so they load lazily, as on the PHP pages.
 *
 * @param string $block_content The block's HTML.
 * @param array  $block         The parsed block.
 */
function aiad_editable_page_render_block( string $block_content, array $block ): string {
	static $slug = null;
	$slug = $slug ?? aiad_current_editable_page();
	if ( '' === $slug ) {
		return $block_content;
	}
	if ( 'core/post-content' === $block['blockName'] ) {
		// Once, on the whole page, so nothing is replaced twice.
		$block_content = strtr( $block_content, aiad_editable_page_placeholders( $slug ) );
		$block_content = (string) preg_replace( '#\s*<a\b[^>]*\bhref=""[^>]*>.*?</a>#s', '', $block_content );
	}
	if ( 'core/group' === $block['blockName'] && preg_match( '/^\s*<(section|aside)\b/', $block_content, $tag ) && preg_match( '/<h[1-6][^>]*\bid="([^"]+)"/', $block_content, $m ) ) {
		$html = new WP_HTML_Tag_Processor( $block_content );
		if ( $html->next_tag( $tag[1] ) && null === $html->get_attribute( 'aria-labelledby' ) ) {
			$html->set_attribute( 'aria-labelledby', $m[1] );
			$block_content = $html->get_updated_html();
		}
	}
	if ( 'core/image' === $block['blockName'] && str_contains( $block_content, AIAD_URI . '/assets/images/' ) ) {
		$html = new WP_HTML_Tag_Processor( $block_content );
		if ( $html->next_tag( 'img' ) && null === $html->get_attribute( 'width' ) ) {
			$file = AIAD_DIR . substr( strtok( (string) $html->get_attribute( 'src' ), '?' ), strlen( AIAD_URI ) );
			$size = is_readable( $file ) ? wp_getimagesize( $file ) : false;
			if ( $size ) {
				$html->set_attribute( 'width', (string) $size[0] );
				$html->set_attribute( 'height', (string) $size[1] );
				$html->set_attribute( 'loading', 'lazy' );
				$block_content = $html->get_updated_html();
			}
		}
	}
	return $block_content;
}
add_filter( 'render_block', 'aiad_editable_page_render_block', 10, 2 );

/**
 * The pattern category for whole pages, offered when a page is created.
 */
function aiad_register_page_pattern_category(): void {
	register_block_pattern_category( 'aiad-pages', array( 'label' => __( 'Pages', 'ai-awareness-day' ) ) );
}
add_action( 'init', 'aiad_register_page_pattern_category' );

/**
 * In the editor of a theme page, its own stylesheet, so the canvas looks like the site.
 */
function aiad_editable_page_editor_styles(): void {
	$slug = aiad_edited_editable_page();
	if ( '' === $slug ) {
		return;
	}
	$file = 'assets/css/pages/' . $slug . '.css';
	if ( is_readable( AIAD_DIR . '/' . $file ) ) {
		wp_enqueue_style( aiad_editable_pages()[ $slug ]['style'], AIAD_URI . '/' . $file, array( 'aiad-style' ), AIAD_VERSION . '.' . (string) filemtime( AIAD_DIR . '/' . $file ) );
	}
}
add_action( 'enqueue_block_assets', 'aiad_editable_page_editor_styles', 20 );

/**
 * In the editor of a theme page with placeholders, a notice saying what the words in braces stand for, with what
 * they show today.
 */
function aiad_editable_page_editor_notice(): void {
	$slug = aiad_edited_editable_page();
	$shown = array();
	foreach ( '' !== $slug ? aiad_editable_page_placeholders( $slug ) : array() as $placeholder => $value ) {
		$value   = wp_strip_all_tags( $value );
		$shown[] = $placeholder . ' = ' . ( '' !== $value ? $value : __( '(nothing here: the link is left out)', 'ai-awareness-day' ) );
	}
	if ( ! $shown ) {
		return;
	}
	/* translators: %s: the placeholders and their values, e.g. "{opens} = 1 January 2027; {event} = …" */
	$message = trim( sprintf( __( 'Words in braces are filled in when the page is shown, so they stay right on their own: %s.', 'ai-awareness-day' ), implode( '; ', $shown ) ) . ' ' . aiad_editable_pages()[ $slug ]['note'] );
	wp_add_inline_script( 'wp-edit-post', 'wp.domReady( function () { wp.data.dispatch( "core/notices" ).createInfoNotice( ' . wp_json_encode( $message ) . ', { id: "aiad-page-placeholders", isDismissible: true } ); } );' );
}
add_action( 'enqueue_block_editor_assets', 'aiad_editable_page_editor_notice' );

/**
 * Pages → Theme pages.
 */
function aiad_editable_pages_admin_page(): void {
	add_pages_page(
		__( 'Theme pages', 'ai-awareness-day' ),
		__( 'Theme pages', 'ai-awareness-day' ),
		'edit_pages',
		'aiad-theme-pages',
		'aiad_render_editable_pages_admin_page'
	);
}
add_action( 'admin_menu', 'aiad_editable_pages_admin_page' );

/**
 * Handle the screen's two actions: create (or publish again) a theme page's editable page, or go back to the
 * built-in page.
 */
function aiad_handle_editable_page_actions(): void {
	if ( empty( $_POST['aiad_theme_page_action'] ) || empty( $_POST['aiad_theme_page'] ) || ! current_user_can( 'publish_pages' ) ) {
		return;
	}
	check_admin_referer( 'aiad_theme_pages' );
	$action  = sanitize_key( wp_unslash( $_POST['aiad_theme_page_action'] ) );
	$slug    = sanitize_key( wp_unslash( $_POST['aiad_theme_page'] ) );
	$pages   = aiad_editable_pages();
	$current = isset( $pages[ $slug ] ) ? get_page_by_path( $slug ) : null;
	$message = 'error';
	if ( 'create' === $action && isset( $pages[ $slug ] ) && ! aiad_editable_page_post( $slug ) ) {
		if ( $current && 'trash' !== $current->post_status ) {
			// A page kept from before (a draft, after going back to the built-in page): publish it again as it was.
			$id = wp_update_post( array( 'ID' => $current->ID, 'post_status' => 'publish' ), true );
		} else {
			$id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $pages[ $slug ]['title'],
					'post_name'    => $slug,
					'post_content' => wp_slash( aiad_editable_page_content( $slug ) ),
				),
				true
			);
		}
		$message = is_wp_error( $id ) ? 'error' : 'created';
	} elseif ( 'builtin' === $action && $current && 'publish' === $current->post_status ) {
		$id      = wp_update_post( array( 'ID' => $current->ID, 'post_status' => 'draft' ), true );
		$message = is_wp_error( $id ) ? 'error' : 'builtin';
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'aiad-theme-pages', 'aiad_message' => $message, 'aiad_theme_page' => $slug ), admin_url( 'edit.php?post_type=page' ) ) );
	exit;
}
add_action( 'admin_init', 'aiad_handle_editable_page_actions' );

/**
 * Render Pages → Theme pages.
 */
function aiad_render_editable_pages_admin_page(): void {
	$message  = isset( $_GET['aiad_message'] ) ? sanitize_key( wp_unslash( $_GET['aiad_message'] ) ) : '';
	$messages = array(
		'created' => __( 'The page is ready to edit, and is what its address shows now.', 'ai-awareness-day' ),
		'builtin' => __( 'The address shows the built-in page again. Your page is kept as a draft.', 'ai-awareness-day' ),
		'error'   => __( 'That did not work. Nothing was changed.', 'ai-awareness-day' ),
	);
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<?php if ( isset( $messages[ $message ] ) ) : ?>
			<div class="notice <?php echo 'error' === $message ? 'notice-error' : 'notice-success'; ?> is-dismissible"><p><?php echo esc_html( $messages[ $message ] ); ?></p></div>
		<?php endif; ?>
		<p><?php esc_html_e( 'These pages are built by the theme, with their wording in the theme. Each can be turned into a page of blocks with the same wording and design, at the same address, so its text is edited in the block editor. Going back shows the built-in page again and keeps your page as a draft.', 'ai-awareness-day' ); ?></p>
		<?php
		foreach ( aiad_editable_pages() as $slug => $settings ) :
			$page = aiad_editable_page_post( $slug );
			$kept = $page ? null : get_page_by_path( $slug );
			$kept = ( $kept && 'draft' === $kept->post_status ) ? $kept : null;
			$url  = home_url( '/' . $slug . '/' );
			?>
			<h2><?php echo esc_html( $settings['title'] ); ?> <code style="font-size:0.75em"><?php echo esc_html( wp_parse_url( $url, PHP_URL_PATH ) ); ?></code></h2>
			<?php if ( $page ) : ?>
				<p><?php esc_html_e( 'A page built from blocks: edit its text in the block editor.', 'ai-awareness-day' ); ?></p>
				<p>
					<a class="button button-primary" href="<?php echo esc_url( get_edit_post_link( $page->ID ) ); ?>"><?php esc_html_e( 'Edit the page', 'ai-awareness-day' ); ?></a>
					<a class="button" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'View it', 'ai-awareness-day' ); ?></a>
				</p>
			<?php else : ?>
				<p><?php echo esc_html( $kept ? __( 'The built-in page. Your edited page is kept as a draft; using it again publishes it as you left it.', 'ai-awareness-day' ) : sprintf( /* translators: %s: PHP template file */ __( 'The built-in page (%s).', 'ai-awareness-day' ), $settings['builtin'] ) ); ?></p>
			<?php endif; ?>
			<form method="post">
				<?php wp_nonce_field( 'aiad_theme_pages' ); ?>
				<input type="hidden" name="aiad_theme_page" value="<?php echo esc_attr( $slug ); ?>" />
				<input type="hidden" name="aiad_theme_page_action" value="<?php echo $page ? 'builtin' : 'create'; ?>" />
				<?php
				if ( $page ) {
					submit_button( __( 'Show the built-in page instead', 'ai-awareness-day' ), 'secondary', 'submit', false );
				} else {
					submit_button( $kept ? __( 'Use the edited page again', 'ai-awareness-day' ) : __( 'Create the editable page', 'ai-awareness-day' ), 'primary', 'submit', false );
				}
				?>
			</form>
		<?php endforeach; ?>
	</div>
	<?php
}
