<?php
/**
 * The landing page that explains the National AI Conversation: /national-conversation/.
 *
 * It starts as a route, not a WordPress page, so a fresh install has it without anyone creating content, and it is
 * public and indexable (the platform's own /conversation/ pages are not); page-national-conversation.php renders it.
 * Pages → National Conversation can create an editable page in blocks at the same address
 * (patterns/national-conversation.php, templates/page-national-conversation.html), which the address then shows.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const AIAD_NC_QUERY_VAR = 'aiad_nc_page';

/**
 * Whether this request is the landing page.
 */
function aiad_is_national_conversation_page(): bool {
	if ( '' !== (string) get_query_var( AIAD_NC_QUERY_VAR ) ) {
		return true; // The virtual page (page-national-conversation.php).
	}
	$page = aiad_national_conversation_post();
	return $page && is_page( $page->ID );
}

/**
 * The editable page, once it has been created (Appearance → National Conversation): a published page at
 * /national-conversation/ built from blocks. While it exists the address shows it; without it, the virtual page.
 */
function aiad_national_conversation_post(): ?WP_Post {
	static $page = false;
	if ( false === $page ) {
		$found = get_page_by_path( 'national-conversation' );
		$page  = ( $found && 'publish' === $found->post_status && has_blocks( $found ) ) ? $found : null;
	}
	return $page;
}

/**
 * Where the landing page lives.
 */
function aiad_national_conversation_page_url(): string {
	return home_url( '/national-conversation/' );
}

/**
 * The two dates the page is built around: the day the conversation opens and AI Awareness Day itself.
 * They come from the same places as the homepage countdown, so the two can never disagree.
 *
 * @return array{opens:DateTimeImmutable,event:DateTimeImmutable}
 */
function aiad_national_conversation_dates(): array {
	$tz       = wp_timezone();
	$defaults = aiad_get_customizer_defaults();
	$opens    = defined( 'AIAD_CONVERSATION_OPENS' ) ? AIAD_CONVERSATION_OPENS : '2027-01-01';
	$event    = (string) get_theme_mod( 'aiad_event_date_ymd', $defaults['aiad_event_date_ymd'] );
	return array(
		'opens' => new DateTimeImmutable( $opens . ' 12:00:00', $tz ),
		'event' => new DateTimeImmutable( $event . ' 12:00:00', $tz ),
	);
}

/**
 * Whether schools can use the registration and sign-in portal yet. Until the day the conversation opens the public
 * pages send people to the landing page instead. Define AIAD_PORTAL_LIVE in wp-config.php to open it earlier
 * (true) or hold it back (false).
 */
function aiad_portal_is_live(): bool {
	if ( defined( 'AIAD_PORTAL_LIVE' ) ) {
		return (bool) AIAD_PORTAL_LIVE;
	}
	$opens = defined( 'AIAD_CONVERSATION_OPENS' ) ? AIAD_CONVERSATION_OPENS : '2027-01-01';
	return time() >= ( new DateTimeImmutable( $opens . ' 00:00:00', wp_timezone() ) )->getTimestamp();
}

add_filter(
	'query_vars',
	static function ( array $vars ): array {
		$vars[] = AIAD_NC_QUERY_VAR;
		return $vars;
	}
);

add_action(
	'init',
	static function (): void {
		add_rewrite_rule( '^national-conversation/?$', 'index.php?' . AIAD_NC_QUERY_VAR . '=1', 'top' );
	},
	4
);

/** Once the editable page exists, the address shows it rather than the virtual page. */
add_filter(
	'request',
	static function ( array $vars ): array {
		if ( empty( $vars[ AIAD_NC_QUERY_VAR ] ) ) {
			return $vars;
		}
		$page = aiad_national_conversation_post();
		return $page ? array( 'page_id' => $page->ID ) : $vars;
	}
);

/** Flush once per theme version, so a deploy never needs a manual permalink reset. */
add_action(
	'init',
	static function (): void {
		if ( AIAD_VERSION === get_option( 'aiad_nc_rewrite_version' ) ) {
			return;
		}
		flush_rewrite_rules( false );
		update_option( 'aiad_nc_rewrite_version', AIAD_VERSION, false );
	},
	99
);

/** The route has no post behind it, so say plainly that it is a real page. */
add_action(
	'template_redirect',
	static function (): void {
		if ( ! aiad_is_national_conversation_page() ) {
			return;
		}
		global $wp_query;
		$wp_query->is_404  = false;
		$wp_query->is_home = false;
		status_header( 200 );
	},
	1
);

add_filter(
	'template_include',
	static function ( string $template ): string {
		if ( ! aiad_is_national_conversation_page() || aiad_national_conversation_post() ) {
			return $template; // The editable page has its own template (templates/page-national-conversation.html).
		}
		$custom = get_template_directory() . '/page-national-conversation.php';
		return is_readable( $custom ) ? $custom : $template;
	},
	20
);

add_filter(
	'pre_get_document_title',
	static function ( string $title ): string {
		return aiad_is_national_conversation_page()
			? __( 'The National AI Conversation 2027 | AI Awareness Day', 'ai-awareness-day' )
			: $title;
	},
	20
);

add_filter(
	'body_class',
	static function ( array $classes ): array {
		if ( aiad_is_national_conversation_page() ) {
			$classes[] = 'national-conversation-page';
		}
		return $classes;
	}
);

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		if ( is_admin() || ! aiad_is_national_conversation_page() ) {
			return;
		}
		$css = AIAD_DIR . '/assets/css/pages/national-conversation.css';
		if ( is_readable( $css ) ) {
			wp_enqueue_style( 'aiad-national-conversation', AIAD_URI . '/assets/css/pages/national-conversation.css', array( 'aiad-style' ), AIAD_VERSION . '.' . (string) filemtime( $css ) );
		}
	},
	15
);

/**
 * Placeholders the editable page's text can use for what changes on its own: the two dates, the year, the date
 * contact details are deleted, and "Opens" or "Opened".
 *
 * @return array<string, string> Placeholder => HTML.
 */
function aiad_national_conversation_placeholders(): array {
	$dates     = aiad_national_conversation_dates();
	$retention = class_exists( 'AIADN_Privacy' ) ? wp_date( 'j F Y', strtotime( AIADN_Privacy::retention_date() ) ) : '31 August 2027';
	return array(
		'{opens}'       => esc_html( wp_date( 'j F Y', $dates['opens']->getTimestamp() ) ),
		'{event}'       => esc_html( wp_date( 'l j F Y', $dates['event']->getTimestamp() ) ),
		'{event_year}'  => esc_html( wp_date( 'Y', $dates['event']->getTimestamp() ) ),
		'{retention}'   => esc_html( $retention ),
		'{opens_label}' => time() >= $dates['opens']->getTimestamp() ? esc_html__( 'Opened', 'ai-awareness-day' ) : esc_html__( 'Opens', 'ai-awareness-day' ),
	);
}

/**
 * On the editable page: fill in the placeholders, name each section (and the code of conduct's aside) by its first
 * heading (aria-labelledby), which a core group cannot carry, and give the photos their size, which a theme image in
 * an image block has no attachment to take it from.
 *
 * @param string $block_content The block's HTML.
 * @param array  $block         The parsed block.
 */
function aiad_national_conversation_render_block( string $block_content, array $block ): string {
	static $placeholders = null;
	if ( ! aiad_national_conversation_post() || ! aiad_is_national_conversation_page() || is_admin() ) {
		return $block_content;
	}
	if ( 'core/post-content' === $block['blockName'] ) {
		// Once, on the whole page, so nothing is replaced twice.
		$placeholders  = $placeholders ?? aiad_national_conversation_placeholders();
		$block_content = strtr( $block_content, $placeholders );
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
				$html->set_attribute( 'loading', 'lazy' ); // Every photo is below the hero, as on the PHP page.
				$block_content = $html->get_updated_html();
			}
		}
	}
	return $block_content;
}
add_filter( 'render_block', 'aiad_national_conversation_render_block', 10, 2 );

/**
 * The pattern category for whole pages (patterns/national-conversation.php), offered when a page is created.
 */
function aiad_register_page_pattern_category(): void {
	register_block_pattern_category( 'aiad-pages', array( 'label' => __( 'Pages', 'ai-awareness-day' ) ) );
}
add_action( 'init', 'aiad_register_page_pattern_category' );

/**
 * In the editor, the page's own stylesheet for the National Conversation page, so the canvas looks like the site.
 */
function aiad_national_conversation_editor_styles(): void {
	$post = is_admin() ? get_post() : null;
	if ( ! $post || 'page' !== $post->post_type || 'national-conversation' !== $post->post_name ) {
		return;
	}
	$css = AIAD_DIR . '/assets/css/pages/national-conversation.css';
	wp_enqueue_style( 'aiad-national-conversation', AIAD_URI . '/assets/css/pages/national-conversation.css', array( 'aiad-style' ), AIAD_VERSION . '.' . (string) filemtime( $css ) );
}
add_action( 'enqueue_block_assets', 'aiad_national_conversation_editor_styles', 20 );

/**
 * In the editor of the National Conversation page, a notice saying what the words in braces stand for, with what
 * they show today.
 */
function aiad_national_conversation_editor_notice(): void {
	$post = get_post();
	if ( ! $post || 'page' !== $post->post_type || 'national-conversation' !== $post->post_name ) {
		return;
	}
	$shown = array();
	foreach ( aiad_national_conversation_placeholders() as $placeholder => $value ) {
		$shown[] = $placeholder . ' = ' . wp_strip_all_tags( $value );
	}
	/* translators: %s: the placeholders and their values, e.g. "{opens} = 1 January 2027; {event} = …" */
	$message = sprintf( __( 'Words in braces are filled in when the page is shown, so they stay right on their own: %s. The buttons change on the day the conversation opens, so they are a block of their own.', 'ai-awareness-day' ), implode( '; ', $shown ) );
	wp_add_inline_script( 'wp-edit-post', 'wp.domReady( function () { wp.data.dispatch( "core/notices" ).createInfoNotice( ' . wp_json_encode( $message ) . ', { id: "aiad-nc-placeholders", isDismissible: true } ); } );' );
}
add_action( 'enqueue_block_editor_assets', 'aiad_national_conversation_editor_notice' );

/**
 * The page's blocks, as patterns/national-conversation.php builds them. Read from the pattern file itself, since
 * WordPress's list of theme patterns can lag behind a deploy (aiad_homepage_pattern_content()).
 */
function aiad_national_conversation_page_content(): string {
	ob_start();
	include AIAD_DIR . '/patterns/national-conversation.php';
	return (string) ob_get_clean();
}

/**
 * Pages → National Conversation: create the editable page, or go back to the built-in one.
 */
function aiad_national_conversation_admin_page(): void {
	add_pages_page(
		__( 'National Conversation page', 'ai-awareness-day' ),
		__( 'National Conversation', 'ai-awareness-day' ),
		'edit_pages',
		'aiad-national-conversation',
		'aiad_render_national_conversation_admin_page'
	);
}
add_action( 'admin_menu', 'aiad_national_conversation_admin_page' );

/**
 * Handle the admin page's two actions.
 */
function aiad_handle_national_conversation_actions(): void {
	if ( empty( $_POST['aiad_nc_action'] ) || ! current_user_can( 'publish_pages' ) ) {
		return;
	}
	check_admin_referer( 'aiad_nc_page' );
	$action  = sanitize_key( wp_unslash( $_POST['aiad_nc_action'] ) );
	$current = get_page_by_path( 'national-conversation' );
	$message = 'error';
	if ( 'create' === $action && ! aiad_national_conversation_post() ) {
		$content = wp_slash( aiad_national_conversation_page_content() );
		if ( $current && 'trash' !== $current->post_status ) {
			// A page kept from before (a draft, after going back to the built-in page): publish it again as it was.
			$id = wp_update_post( array( 'ID' => $current->ID, 'post_status' => 'publish' ), true );
		} else {
			$id = wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => __( 'The National AI Conversation', 'ai-awareness-day' ),
					'post_name'    => 'national-conversation',
					'post_content' => $content,
				),
				true
			);
		}
		$message = is_wp_error( $id ) ? 'error' : 'created';
	} elseif ( 'builtin' === $action && $current && 'publish' === $current->post_status ) {
		$id      = wp_update_post( array( 'ID' => $current->ID, 'post_status' => 'draft' ), true );
		$message = is_wp_error( $id ) ? 'error' : 'builtin';
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'aiad-national-conversation', 'aiad_message' => $message ), admin_url( 'edit.php?post_type=page' ) ) );
	exit;
}
add_action( 'admin_init', 'aiad_handle_national_conversation_actions' );

/**
 * Render Pages → National Conversation.
 */
function aiad_render_national_conversation_admin_page(): void {
	$page     = aiad_national_conversation_post();
	$kept     = $page ? null : get_page_by_path( 'national-conversation' );
	$kept     = ( $kept && 'draft' === $kept->post_status ) ? $kept : null;
	$messages = array(
		'created' => __( 'The page is ready to edit, and is what /national-conversation/ shows now.', 'ai-awareness-day' ),
		'builtin' => __( '/national-conversation/ shows the built-in page again. Your page is kept as a draft.', 'ai-awareness-day' ),
		'error'   => __( 'That did not work. Nothing was changed.', 'ai-awareness-day' ),
	);
	$message  = isset( $_GET['aiad_message'] ) ? sanitize_key( wp_unslash( $_GET['aiad_message'] ) ) : '';
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<?php if ( isset( $messages[ $message ] ) ) : ?>
			<div class="notice <?php echo 'error' === $message ? 'notice-error' : 'notice-success'; ?> is-dismissible"><p><?php echo esc_html( $messages[ $message ] ); ?></p></div>
		<?php endif; ?>
		<?php if ( $page ) : ?>
			<p><?php esc_html_e( '/national-conversation/ is a page built from blocks: edit its text in the block editor.', 'ai-awareness-day' ); ?></p>
			<p><?php esc_html_e( 'Some words are filled in when the page is shown, so they stay right on their own: {opens} and {event} (the two dates), {event_year}, {opens_label} ("Opens", then "Opened") and {retention} (when contact details are deleted). The buttons change on the day the conversation opens, so they are a block of their own.', 'ai-awareness-day' ); ?></p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( get_edit_post_link( $page->ID ) ); ?>"><?php esc_html_e( 'Edit the page', 'ai-awareness-day' ); ?></a>
				<a class="button" href="<?php echo esc_url( aiad_national_conversation_page_url() ); ?>"><?php esc_html_e( 'View it', 'ai-awareness-day' ); ?></a>
			</p>
			<form method="post" style="margin-top:2em">
				<?php wp_nonce_field( 'aiad_nc_page' ); ?>
				<input type="hidden" name="aiad_nc_action" value="builtin" />
				<p><?php esc_html_e( 'Going back shows the built-in page (page-national-conversation.php) again, with the theme\'s standard wording. Your page is kept as a draft, so you can come back to it.', 'ai-awareness-day' ); ?></p>
				<?php submit_button( __( 'Show the built-in page instead', 'ai-awareness-day' ), 'secondary', 'submit', false ); ?>
			</form>
		<?php else : ?>
			<p><?php esc_html_e( '/national-conversation/ shows the built-in page, whose wording is in the theme (page-national-conversation.php).', 'ai-awareness-day' ); ?></p>
			<p><?php echo esc_html( $kept ? __( 'Your edited page is kept as a draft. Using it again publishes it as you left it.', 'ai-awareness-day' ) : __( 'Creating the page makes a page from blocks with the same wording and design, at the same address, so its text can be edited in the block editor. The site looks the same.', 'ai-awareness-day' ) ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'aiad_nc_page' ); ?>
				<input type="hidden" name="aiad_nc_action" value="create" />
				<?php submit_button( $kept ? __( 'Use the edited page again', 'ai-awareness-day' ) : __( 'Create the editable page', 'ai-awareness-day' ) ); ?>
			</form>
		<?php endif; ?>
	</div>
	<?php
}
