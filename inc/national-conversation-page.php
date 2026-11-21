<?php
/**
 * The landing page that explains the National AI Conversation: /national-conversation/.
 *
 * An ordinary page of blocks (patterns/national-conversation.php, templates/page-national-conversation.html), public
 * and indexable (the platform's own /conversation/ pages are not). A fresh install has it without anyone creating
 * content: the first time the theme loads, aiad_maybe_convert_theme_pages() (inc/editable-pages.php) creates it.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether this request is the landing page.
 */
function aiad_is_national_conversation_page(): bool {
	$page = aiad_national_conversation_post();
	return $page && is_page( $page->ID );
}

/**
 * The landing page: a published page at /national-conversation/ built from blocks, created the first time the theme
 * loads (aiad_maybe_convert_theme_pages() in inc/editable-pages.php).
 */
function aiad_national_conversation_post(): ?WP_Post {
	return aiad_editable_page_post( 'national-conversation' );
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
	$tz    = wp_timezone();
	$opens = defined( 'AIAD_CONVERSATION_OPENS' ) ? AIAD_CONVERSATION_OPENS : '2027-01-01';
	$event = aiad_campaign_event_date(); // aiad-core: the campaign settings, else the standard date.
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
 * One campaign update as the news feed's card (inc/timeline-layouts.php), for the landing page's "In the news" section
 * (block aiad/nc-news). The update is found by its slug, so the page shows nothing if it is unpublished or renamed.
 * The card has no like or share buttons: those need the feed's script. The title and the "Read the article" link go
 * to the update.
 *
 * @param array $attributes Block attributes: slug.
 */
function aiad_render_national_conversation_news( array $attributes ): string {
	$slug  = sanitize_title( (string) ( $attributes['slug'] ?? '' ) );
	$entry = '' !== $slug ? get_page_by_path( $slug, OBJECT, 'timeline' ) : null;
	if ( ! $entry instanceof WP_Post || 'publish' !== $entry->post_status || ! function_exists( 'aiad_render_timeline_magazine_row' ) ) {
		return '';
	}
	$pinned  = (bool) get_post_meta( $entry->ID, '_aiad_timeline_pinned', true );
	$icon    = get_post_meta( $entry->ID, '_aiad_timeline_icon', true ) ?: 'announcement';
	$url     = get_permalink( $entry ) ?: '#';
	$excerpt = aiad_timeline_entry_excerpt_text( $entry );
	ob_start();
	?>
<article class="timeline-magazine__card-inner ncp-news__card">
	<div class="timeline-magazine__card-media">
		<a href="<?php echo esc_url( $url ); ?>" class="timeline-magazine__card-media-link timeline-magazine__card-media-link--chamfer <?php echo esc_attr( aiad_timeline_cover_modifier_class( $icon, $pinned ) ); ?>" tabindex="-1" aria-hidden="true">
			<?php echo aiad_timeline_entry_cover_visual( $entry, 'timeline-magazine', 'thumb' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span class="timeline-magazine__cover-scrim timeline-magazine__cover-scrim--top" aria-hidden="true"></span>
		</a>
		<?php
		/* translators: %s: a time span, such as 4 weeks */
		echo aiad_timeline_magazine_cover_meta_html( aiad_timeline_featured_badge_label( $entry, $pinned, $icon ), $icon, $pinned, sprintf( __( '%s ago', 'ai-awareness-day' ), human_time_diff( get_post_timestamp( $entry ), time() ) ), get_the_date( 'c', $entry ), get_the_date( 'j F Y', $entry ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
	</div>
	<div class="timeline-magazine__card-body">
		<h3 class="timeline-magazine__card-title"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( get_the_title( $entry ) ); ?></a></h3>
		<?php if ( '' !== $excerpt ) : ?>
		<p class="timeline-magazine__card-excerpt"><?php echo esc_html( $excerpt ); ?></p>
		<?php endif; ?>
		<a class="ncp-news__more" href="<?php echo esc_url( $url ); ?>" aria-hidden="true" tabindex="-1"><?php esc_html_e( 'Read the article', 'ai-awareness-day' ); ?> <span aria-hidden="true">&rarr;</span></a>
	</div>
</article>
	<?php
	return (string) ob_get_clean();
}

add_action(
	'init',
	static function (): void {
		register_block_type(
			'aiad/nc-news',
			array(
				'api_version'     => 3,
				'title'           => __( 'Campaign update card', 'ai-awareness-day' ),
				'description'     => __( 'One campaign update, by its slug, as the news feed shows it.', 'ai-awareness-day' ),
				'category'        => 'theme',
				'icon'            => 'megaphone',
				'attributes'      => array( 'slug' => array( 'type' => 'string', 'default' => '' ) ),
				'supports'        => array( 'html' => false, 'reusable' => false ),
				'render_callback' => 'aiad_render_national_conversation_news',
			)
		);
	}
);

// The news card's styles: the feed's own stylesheets, which only load where the feed is.
add_action(
	'wp_enqueue_scripts',
	static function (): void {
		if ( is_admin() || ! aiad_is_national_conversation_page() || ! has_block( 'aiad/nc-news', aiad_national_conversation_post() ) ) {
			return;
		}
		foreach ( array( 'aiad-entry-figure' => 'entry-figure', 'aiad-timeline' => 'timeline' ) as $handle => $file ) {
			$path = AIAD_DIR . '/assets/css/components/' . $file . '.css';
			if ( is_readable( $path ) && ! wp_style_is( $handle, 'enqueued' ) ) {
				wp_enqueue_style( $handle, AIAD_URI . '/assets/css/components/' . $file . '.css', array( 'aiad-style' ), (string) filemtime( $path ) );
			}
		}
	},
	16
);

/**
 * A theme's pop-up: a sample big question (with "Show on the board"), a key word from the AI Literacy Periodic Table
 * (aiad_render_literacy_theme_word()) and the newest resources in that theme from the library, as the library's own
 * cards (template-parts/components/resource-tile.php), with a link to the library filtered to the theme. The
 * National Conversation's theme tiles open it (block aiad/nc-theme-resources), and so do the homepage's principle
 * cards. It is a <dialog>, so Escape and the browser's focus handling come with it. Empty for a theme with no
 * resources, and drawn once per page.
 *
 * @param string $strand safe, smart, creative, responsible or future.
 */
function aiad_theme_resources_dialog( string $strand ): string {
	static $drawn = array();
	$strand = sanitize_key( $strand );
	if ( isset( $drawn[ $strand ] ) ) {
		return '';
	}
	$term = '' !== $strand ? get_term_by( 'slug', $strand, 'resource_principle' ) : false;
	if ( ! $term instanceof WP_Term ) {
		return '';
	}
	$query = new WP_Query(
		array(
			'post_type'           => 'resource',
			'post_status'         => 'publish',
			'posts_per_page'      => 6,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'tax_query'           => array( array( 'taxonomy' => 'resource_principle', 'field' => 'term_id', 'terms' => $term->term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		)
	);
	if ( ! $query->have_posts() ) {
		return '';
	}
	$id      = 'ncp-theme-resources-' . $strand;
	$library = get_post_type_archive_link( 'resource' ) ?: home_url( '/resources/' );

	// A sample big question: the newest of these resources that has one (its lesson page shows it the same way). The
	// lesson's "(from Slide 3)" pointer means nothing away from its slides, so it is left off.
	$sample = null;
	foreach ( $query->posts as $post ) {
		$asked = trim( (string) preg_replace( '/\s*\(from slide[^)]*\)\s*$/i', '', (string) get_post_meta( $post->ID, '_aiad_discussion_question', true ) ) );
		if ( '' !== $asked ) {
			$sample = array( 'text' => $asked, 'url' => get_permalink( $post ), 'title' => get_the_title( $post ) );
			break;
		}
	}
	/* translators: %s: theme name, such as Safe */
	$title = sprintf( __( '%s resources', 'ai-awareness-day' ), $term->name );

	$drawn[ $strand ] = true;
	$css = AIAD_DIR . '/assets/css/components/theme-resources.css';
	wp_enqueue_style( 'aiad-theme-resources', AIAD_URI . '/assets/css/components/theme-resources.css', array( 'aiad-style' ), is_readable( $css ) ? (string) filemtime( $css ) : AIAD_VERSION );

	ob_start();
	?>
<dialog id="<?php echo esc_attr( $id ); ?>" class="ncp-theme-dialog ncp-theme-dialog--<?php echo esc_attr( $strand ); ?>" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
	<div class="ncp-theme-dialog__head">
		<h3 id="<?php echo esc_attr( $id ); ?>-title" class="ncp-theme-dialog__title"><?php echo aiad_strand_icon_svg( $strand, 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed paths. ?><?php echo esc_html( $title ); ?></h3>
		<form method="dialog"><button type="submit" class="ncp-theme-dialog__close"><?php esc_html_e( 'Close', 'ai-awareness-day' ); ?></button></form>
	</div>
	<?php $key_word = function_exists( 'aiad_render_literacy_theme_word' ) ? aiad_render_literacy_theme_word( $strand ) : ''; ?>
	<?php if ( $sample || '' !== $key_word ) : ?>
	<div class="ncp-theme-dialog__lead">
	<?php endif; ?>
	<?php if ( $sample ) : ?>
	<div class="ncp-bigq" data-ncp-board-panel tabindex="-1">
		<p class="ncp-bigq__label"><?php esc_html_e( 'Big question', 'ai-awareness-day' ); ?></p>
		<p class="ncp-bigq__text"><?php echo wptexturize( esc_html( $sample['text'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
		<p class="ncp-bigq__actions">
			<button type="button" class="ncp-bigq__show" data-ncp-board data-close="<?php esc_attr_e( 'Close', 'ai-awareness-day' ); ?>"><?php esc_html_e( 'Show on the board', 'ai-awareness-day' ); ?></button>
			<a class="ncp-bigq__from" href="<?php echo esc_url( $sample['url'] ); ?>"><?php echo esc_html( sprintf( /* translators: %s: resource title */ __( 'From the lesson: %s', 'ai-awareness-day' ), html_entity_decode( $sample['title'], ENT_QUOTES, 'UTF-8' ) ) ); ?></a>
		</p>
	</div>
	<?php endif; ?>
	<?php echo $key_word; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in aiad_render_literacy_theme_word(). ?>
	<?php if ( $sample || '' !== $key_word ) : ?>
	</div>
	<?php endif; ?>
	<div class="resource-tiles">
		<?php
		while ( $query->have_posts() ) {
			$query->the_post();
			get_template_part( 'template-parts/components/resource-tile', null, array() );
		}
		wp_reset_postdata();
		?>
	</div>
	<a class="resource-tiles__more" href="<?php echo esc_url( add_query_arg( 'principle', $strand, $library ) ); ?>"><?php echo esc_html( sprintf( /* translators: %s: theme name */ __( 'See all %s resources', 'ai-awareness-day' ), $term->name ) ); ?> <span aria-hidden="true">&rarr;</span></a>
</dialog>
	<?php
	// One small script for every pop-up on the page: open it from its button or principle card, close it on a click on the backdrop, and put a big question
	// on the board (full screen where the browser allows it, else filling the window), as resource-lesson.js does.
	if ( ! wp_script_is( 'aiad-nc-theme-resources', 'enqueued' ) ) {
		wp_register_script( 'aiad-nc-theme-resources', false, array(), AIAD_VERSION, true );
		wp_enqueue_script( 'aiad-nc-theme-resources' );
		wp_add_inline_script(
			'aiad-nc-theme-resources',
			<<<'JS'
(function () {
	var board = null;
	function setBoard(panel, on) {
		var button = panel.querySelector('[data-ncp-board]');
		panel.classList.toggle('is-board', on);
		if (!button.dataset.label) button.dataset.label = button.textContent;
		button.textContent = on ? button.dataset.close : button.dataset.label;
		board = on ? panel : null;
		if (on) {
			if (panel.requestFullscreen) panel.requestFullscreen().catch(function () {});
			panel.focus();
		} else if (document.fullscreenElement) {
			document.exitFullscreen().catch(function () {});
		}
	}
	document.addEventListener('click', function (e) {
		var t = e.target.closest ? e.target : e.target.parentElement;
		var open = t.closest('[data-ncp-dialog]');
		if (open) {
			var d = document.getElementById(open.getAttribute('data-ncp-dialog'));
			// A link (the homepage hero's theme icons) goes to its page when the page has no pop-up for it.
			if (d && d.showModal) { e.preventDefault(); d.showModal(); }
			return;
		}
		// The homepage's principle cards (blocks/principle-card) open the same pop-up, where the page has it; the
		// card's own link is the way in without JavaScript.
		var card = t.closest('a.principle-card');
		var strand = card && card.className.match(/principle-card--(safe|smart|creative|responsible|future)\b/);
		var pop = strand && document.getElementById('ncp-theme-resources-' + strand[1]);
		if (pop && pop.showModal) {
			e.preventDefault();
			pop.showModal();
			return;
		}
		var show = t.closest('[data-ncp-board]');
		if (show) {
			var panel = show.closest('[data-ncp-board-panel]');
			setBoard(panel, !panel.classList.contains('is-board'));
			return;
		}
		if (t.classList.contains('ncp-theme-dialog')) t.close();
	});
	// Leaving full screen (Esc) leaves the board too. Without full screen, Esc closes the board, not the pop-up.
	document.addEventListener('fullscreenchange', function () {
		if (board && !document.fullscreenElement) setBoard(board, false);
	});
	document.addEventListener('cancel', function (e) {
		if (board) { e.preventDefault(); setBoard(board, false); }
	}, true);
})();
JS
		);
	}
	return (string) ob_get_clean();
}

/**
 * A theme tile's "Show resources" button and its pop-up (block aiad/nc-theme-resources). No button for a theme with
 * no resources.
 *
 * @param array $attributes Block attributes: strand (safe, smart, creative, responsible or future).
 */
function aiad_render_national_conversation_theme_resources( array $attributes ): string {
	$strand = sanitize_key( (string) ( $attributes['strand'] ?? '' ) );
	$dialog = aiad_theme_resources_dialog( $strand );
	if ( '' === $dialog ) {
		return '';
	}
	$term = get_term_by( 'slug', $strand, 'resource_principle' );
	return '<button type="button" class="ncp-theme__show" data-ncp-dialog="ncp-theme-resources-' . esc_attr( $strand ) . '" aria-haspopup="dialog">'
		. esc_html__( 'Show resources', 'ai-awareness-day' )
		. ' <span class="screen-reader-text">' . esc_html( sprintf( /* translators: %s: theme name */ __( 'for %s', 'ai-awareness-day' ), $term->name ) ) . '</span></button>'
		. $dialog;
}

// The homepage's principle cards open the same pop-ups: they are drawn at the foot of the page.
add_action(
	'wp_footer',
	static function (): void {
		if ( ! is_front_page() ) {
			return;
		}
		foreach ( array( 'safe', 'smart', 'creative', 'responsible', 'future' ) as $strand ) {
			echo aiad_theme_resources_dialog( $strand ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in aiad_theme_resources_dialog().
		}
	},
	5
);

add_action(
	'init',
	static function (): void {
		register_block_type(
			'aiad/nc-theme-resources',
			array(
				'api_version'     => 3,
				'title'           => __( 'Theme resources pop-up', 'ai-awareness-day' ),
				'description'     => __( 'A Show resources button and a pop-up of one theme\'s newest resources from the library.', 'ai-awareness-day' ),
				'category'        => 'theme',
				'icon'            => 'portfolio',
				'attributes'      => array( 'strand' => array( 'type' => 'string', 'default' => '' ) ),
				'supports'        => array( 'html' => false, 'reusable' => false ),
				'render_callback' => 'aiad_render_national_conversation_theme_resources',
			)
		);
	}
);
