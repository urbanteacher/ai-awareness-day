<?php
/**
 * Theme pages in blocks: pages the theme builds itself, with their words in the theme, as ordinary pages of blocks.
 *
 * Two kinds. A page at its own address (National Conversation, the walkthrough) is a page with that slug. A page with
 * a page template chosen (the Assets Pack, the Press Release) is whichever page has that template, from
 * theme.json's customTemplates (templates/assets-pack.html, templates/press-release.html).
 *
 * The pages are made for the site: the first time the theme loads, aiad_maybe_convert_theme_pages() creates a missing
 * page of the first kind, and puts the second kind's page (the one with the old PHP template chosen, or the one
 * the Assets Pack link created) on its new template and gives it its blocks. The old content of a converted page is
 * kept in _aiad_builtin_content.
 *
 * Each page's blocks come from a pattern (patterns/{slug}.php, built with inc/block-markup.php), also offered when a
 * new page is created. Its outermost block is the page's main group, so the editor canvas is styled as the site is;
 * the templates hold only the header, the post content and the footer.
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
 * template:     for a page with a page template chosen, that template (theme.json customTemplates); '' for a page at its own address.
 * page:         for a page with a page template chosen, a function returning its post.
 * style:        the handle of the page's stylesheet (assets/css/pages/{slug}.css), loaded in its editor too; '' when
 *               the theme loads it everywhere.
 * placeholders: a function returning placeholder => HTML, or ''.
 * note:         a sentence for the editor notice, or ''.
 *
 * @return array<string, array<string, string>>
 */
function aiad_editable_pages(): array {
	return array(
		'national-conversation' => array(
			'title'        => __( 'The National AI Conversation', 'ai-awareness-day' ),
			'template'     => '',
			'page'         => '',
			'style'        => 'aiad-national-conversation',
			'placeholders' => 'aiad_national_conversation_placeholders',
			'note'         => __( 'The buttons change on the day the conversation opens, so they are a block of their own.', 'ai-awareness-day' ),
		),
		'walkthrough'           => array(
			'title'        => __( 'Platform walkthrough', 'ai-awareness-day' ),
			'template'     => '',
			'page'         => '',
			'style'        => 'aiad-walkthrough',
			'placeholders' => 'aiad_walkthrough_placeholders',
			'note'         => '',
		),
		'assets-pack'           => array(
			'title'        => __( 'Assets Pack', 'ai-awareness-day' ),
			'template'     => 'assets-pack',
			'page'         => 'aiad_get_assets_pack_page',
			'style'        => '',
			'placeholders' => 'aiad_assets_pack_placeholders',
			'note'         => '',
		),
		'press-release'         => array(
			'title'        => __( 'Press Release', 'ai-awareness-day' ),
			'template'     => 'press-release',
			'page'         => 'aiad_get_press_release_page',
			'style'        => '',
			'placeholders' => '',
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
		$found = aiad_editable_page_base( $slug );
		$ok    = $found && 'publish' === $found->post_status && has_blocks( $found );
		if ( $ok && '' !== ( aiad_editable_pages()[ $slug ]['template'] ?? '' ) ) {
			$ok = (bool) get_post_meta( $found->ID, '_aiad_block_page', true );
		}
		$pages[ $slug ] = $ok ? $found : null;
	}
	return $pages[ $slug ];
}

/**
 * The post behind a theme page, edited or not: the page at its address, or the page with its template chosen.
 *
 * @param string $slug A slug from aiad_editable_pages().
 */
function aiad_editable_page_base( string $slug ): ?WP_Post {
	$settings = aiad_editable_pages()[ $slug ] ?? null;
	if ( ! $settings ) {
		return null;
	}
	if ( '' !== $settings['page'] ) {
		$page = function_exists( $settings['page'] ) ? call_user_func( $settings['page'] ) : null;
		return $page instanceof WP_Post ? $page : null;
	}
	return get_page_by_path( $slug );
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
	if ( ! $post || 'page' !== $post->post_type ) {
		return '';
	}
	foreach ( aiad_editable_pages() as $slug => $settings ) {
		$base = '' !== $settings['page'] ? aiad_editable_page_base( $slug ) : null;
		if ( $base ? $base->ID === $post->ID : $slug === $post->post_name ) {
			return $slug;
		}
	}
	return '';
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
 * @param string       $slug      A slug from aiad_editable_pages().
 * @param WP_Post|null $aiad_page The page they are for, if it exists (the Assets Pack keeps the page's own text as
 *                                its introduction).
 */
function aiad_editable_page_content( string $slug, ?WP_Post $aiad_page = null ): string {
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
		$block_content = (string) preg_replace( '#\s*<p\b[^>]*>\s*</p>#', '', $block_content ); // A paragraph that held only that link.
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
	if ( '' === $slug || '' === aiad_editable_pages()[ $slug ]['style'] ) {
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
 * Give a page with a page template chosen its blocks ('create'), keeping the content it had in _aiad_builtin_content
 * (and, going back with 'builtin', blocks in _aiad_edited_content). The page's revisions keep every step too.
 *
 * @param WP_Post $page   The page.
 * @param string  $slug   A slug from aiad_editable_pages().
 * @param string  $action create or builtin.
 * @return string The message to show: created, builtin or error.
 */
function aiad_switch_template_page( WP_Post $page, string $slug, string $action ): string {
	$on = (bool) get_post_meta( $page->ID, '_aiad_block_page', true );
	if ( 'create' === $action && ! $on ) {
		$kept    = (string) get_post_meta( $page->ID, '_aiad_edited_content', true );
		$content = '' !== $kept ? $kept : aiad_editable_page_content( $slug, $page );
		update_post_meta( $page->ID, '_aiad_builtin_content', wp_slash( $page->post_content ) );
		// The site writes this content itself, so it is saved as it is written, whoever's request makes it.
		$kses = has_filter( 'content_save_pre', 'wp_filter_post_kses' );
		if ( $kses ) {
			kses_remove_filters();
		}
		$id = wp_update_post( array( 'ID' => $page->ID, 'post_content' => wp_slash( $content ) ), true );
		if ( $kses ) {
			kses_init_filters();
		}
		if ( is_wp_error( $id ) ) {
			return 'error';
		}
		update_post_meta( $page->ID, '_aiad_block_page', '1' );
		return 'created';
	}
	if ( 'builtin' === $action && $on ) {
		update_post_meta( $page->ID, '_aiad_edited_content', wp_slash( $page->post_content ) );
		$id = wp_update_post( array( 'ID' => $page->ID, 'post_content' => wp_slash( (string) get_post_meta( $page->ID, '_aiad_builtin_content', true ) ) ), true );
		if ( is_wp_error( $id ) ) {
			return 'error';
		}
		delete_post_meta( $page->ID, '_aiad_block_page' );
		return 'builtin';
	}
	return 'error';
}

/**
 * The first page that has one of the given page templates chosen, published or not.
 *
 * @param string[] $templates Template names, e.g. array( 'press-release', 'template-press-release.php' ).
 */
function aiad_find_page_by_template( array $templates ): ?WP_Post {
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => 1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'key'     => '_wp_page_template',
					'value'   => $templates,
					'compare' => 'IN',
				),
			),
		)
	);
	return $pages ? $pages[0] : null;
}

/**
 * Make the theme pages editable pages, once: a missing page at its own address is created from its pattern (a draft
 * kept from before is published again), and a page with the old PHP template chosen is moved to its new page
 * template and given its blocks. Records when it ran in aiad_theme_pages_converted and any failure in
 * aiad_theme_pages_conversion_error, and tries again on the next load if it could not finish.
 */
function aiad_maybe_convert_theme_pages(): void {
	if ( get_option( 'aiad_theme_pages_converted' ) || wp_installing() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	$locked = get_option( 'aiad_theme_pages_converting' );
	if ( $locked && ( time() - (int) $locked ) < 10 * MINUTE_IN_SECONDS ) {
		return;
	}
	delete_option( 'aiad_theme_pages_converting' );
	if ( ! add_option( 'aiad_theme_pages_converting', time(), '', false ) ) {
		return;
	}

	// The site writes this content itself, so it is saved as it is written, whoever's request makes it.
	$kses = has_filter( 'content_save_pre', 'wp_filter_post_kses' );
	if ( $kses ) {
		kses_remove_filters();
	}

	$error = '';
	foreach ( aiad_editable_pages() as $slug => $settings ) {
		if ( '' === $settings['template'] ) {
			if ( aiad_editable_page_post( $slug ) ) {
				continue;
			}
			$current = get_page_by_path( $slug, OBJECT, 'page' );
			if ( $current && 'trash' !== $current->post_status ) {
				// A page kept from before (a draft): publish it again as it was.
				$id = wp_update_post( array( 'ID' => $current->ID, 'post_status' => 'publish' ), true );
			} else {
				$id = wp_insert_post(
					array(
						'post_type'    => 'page',
						'post_status'  => 'publish',
						'post_title'   => $settings['title'],
						'post_name'    => $slug,
						'post_content' => wp_slash( aiad_editable_page_content( $slug ) ),
					),
					true
				);
			}
			if ( is_wp_error( $id ) ) {
				$error = $id->get_error_message();
			}
			continue;
		}

		// A page with a page template chosen: the old PHP template (before this change) or the new one.
		$page = aiad_find_page_by_template( array( $settings['template'], 'template-' . $settings['template'] . '.php' ) );
		if ( ! $page ) {
			continue; // No page has it chosen; the Assets Pack page is made by aiad_ensure_assets_pack_page().
		}
		update_post_meta( $page->ID, '_wp_page_template', $settings['template'] );
		if ( ! get_post_meta( $page->ID, '_aiad_block_page', true ) ) {
			$result = aiad_switch_template_page( $page, $slug, 'create' );
			if ( 'error' === $result ) {
				$error = sprintf( 'Could not give the %s page its blocks.', $slug );
			}
		}
	}

	if ( $kses ) {
		kses_init_filters();
	}
	if ( '' !== $error ) {
		update_option( 'aiad_theme_pages_conversion_error', $error, false );
		delete_option( 'aiad_theme_pages_converting' );
		return;
	}
	delete_option( 'aiad_theme_pages_conversion_error' );
	update_option( 'aiad_theme_pages_converted', gmdate( 'c' ), false );
	delete_option( 'aiad_theme_pages_converting' );
}
// After the Assets Pack page is ensured (priority 20) and the homepage is converted (30).
add_action( 'init', 'aiad_maybe_convert_theme_pages', 31 );
