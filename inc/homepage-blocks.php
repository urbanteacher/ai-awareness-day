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
 * The principles cards in order: strand => [ standard title, standard description ]. "literacy" is the closing
 * "Your AI. Your choices." card. Used by the section template, the principle card block and its pattern.
 *
 * @return array<string, array{0: string, 1: string}>
 */
function aiad_principle_cards(): array {
	return array(
		'safe'        => array( __( 'Safe', 'ai-awareness-day' ), __( 'Start with what should stay private — trust, sharing and the data AI holds about you.', 'ai-awareness-day' ) ),
		'smart'       => array( __( 'Smart', 'ai-awareness-day' ), __( 'Question AI that acts on your behalf — decisions, shortcuts and who is really choosing.', 'ai-awareness-day' ) ),
		'creative'    => array( __( 'Creative', 'ai-awareness-day' ), __( 'Own what you make with AI — authorship, attribution and honest creative work.', 'ai-awareness-day' ) ),
		'responsible' => array( __( 'Responsible', 'ai-awareness-day' ), __( 'Keep human judgement in consequential moments — when the output matters.', 'ai-awareness-day' ) ),
		'future'      => array( __( 'Future', 'ai-awareness-day' ), __( 'Name the skills worth keeping human — and practise them on purpose.', 'ai-awareness-day' ) ),
		'literacy'    => array( __( 'Your AI. Your choices.', 'ai-awareness-day' ), __( 'These five strands are one literacy — Keep Humans in the Loop.', 'ai-awareness-day' ) ),
	);
}

/**
 * A principle card's wording as the site shows it: the Customizer's value (aiad_principle_title_{strand} /
 * aiad_principle_desc_{strand}), else the standard wording. The literacy card has no Customizer fields.
 *
 * @return array{0: string, 1: string} Plain-text title and description.
 */
function aiad_principle_card_wording( string $strand ): array {
	$cards = aiad_principle_cards();
	if ( ! isset( $cards[ $strand ] ) ) {
		return array( '', '' );
	}
	if ( 'literacy' === $strand ) {
		return $cards[ $strand ];
	}
	$title = (string) get_theme_mod( 'aiad_principle_title_' . $strand, '' );
	$desc  = (string) get_theme_mod( 'aiad_principle_desc_' . $strand, '' );
	return array( ! empty( $title ) ? $title : $cards[ $strand ][0], ! empty( $desc ) ? $desc : $cards[ $strand ][1] ); // empty(), as the template always did.
}

/**
 * Block name for a section slug: free_resources => aiad/section-free-resources.
 */
function aiad_homepage_section_block_name( string $slug ): string {
	return 'aiad/section-' . str_replace( '_', '-', $slug );
}

/**
 * Each section block's wording fields: section slug => [ theme mod => [ label, type, default, sanitize callback, help ] ].
 *
 * These are the theme mods the section's template reads. A block that has a value for one gives it to the template
 * (see aiad_render_homepage_section()); an empty value leaves the Customizer's value or the template's default.
 * The event date, contact email, site name and images stay in their own settings.
 *
 * @return array<string, array<string, array{0: string, 1: string, 2: ?string, 3: string, 4?: string}>>
 */
function aiad_homepage_section_fields(): array {
	$d      = function_exists( 'aiad_get_customizer_defaults' ) ? aiad_get_customizer_defaults() : array();
	$fields = array();

	if ( function_exists( 'aiad_hero27_fields' ) ) {
		foreach ( aiad_hero27_fields() as $key => $f ) {
			$fields['hero'][ $key ] = array( $f['label'], $f['type'], (string) $f['default'], $f['sanitize'], (string) ( $f['description'] ?? '' ) );
		}
	}
	$fields['campaign'] = array(
		'aiad_campaign_title'              => array( __( 'Title', 'ai-awareness-day' ), 'text', $d['aiad_campaign_title'] ?? null, 'sanitize_text_field' ),
		'aiad_campaign_text'               => array( __( 'Description', 'ai-awareness-day' ), 'textarea', $d['aiad_campaign_text'] ?? null, 'wp_kses_post' ),
		'aiad_campaign_text_2'             => array( __( 'Second paragraph', 'ai-awareness-day' ), 'textarea', $d['aiad_campaign_text_2'] ?? null, 'wp_kses_post' ),
		'aiad_campaign_linkedin_embed_src' => array( __( 'LinkedIn embed URL', 'ai-awareness-day' ), 'url', $d['aiad_campaign_linkedin_embed_src'] ?? null, 'esc_url_raw', __( 'Optional: the src of a LinkedIn post embed, shown beside the text.', 'ai-awareness-day' ) ),
	);
	foreach ( array( 'safe' => __( 'Safe', 'ai-awareness-day' ), 'smart' => __( 'Smart', 'ai-awareness-day' ), 'creative' => __( 'Creative', 'ai-awareness-day' ), 'responsible' => __( 'Responsible', 'ai-awareness-day' ), 'future' => __( 'Future', 'ai-awareness-day' ) ) as $slug => $name ) {
		/* translators: %s: strand name, e.g. Safe */
		$fields['principles'][ 'aiad_principle_title_' . $slug ] = array( sprintf( __( '%s: title', 'ai-awareness-day' ), $name ), 'text', null, 'sanitize_text_field', __( 'Leave empty for the standard wording.', 'ai-awareness-day' ) );
		/* translators: %s: strand name, e.g. Safe */
		$fields['principles'][ 'aiad_principle_desc_' . $slug ] = array( sprintf( __( '%s: description', 'ai-awareness-day' ), $name ), 'textarea', null, 'sanitize_textarea_field', __( 'Leave empty for the standard wording.', 'ai-awareness-day' ) );
	}
	$fields['free_resources'] = array(
		'aiad_free_resources_title' => array( __( 'Title', 'ai-awareness-day' ), 'text', __( 'Free Resources', 'ai-awareness-day' ), 'sanitize_text_field' ),
		'aiad_free_resources_desc'  => array( __( 'Description', 'ai-awareness-day' ), 'textarea', __( 'Ready-to-use activities and materials for AI Awareness Day.', 'ai-awareness-day' ), 'sanitize_textarea_field' ),
	);
	$fields['featured_resources'] = array(
		'aiad_handpicked_resources_title' => array( __( 'Title', 'ai-awareness-day' ), 'text', __( 'Handpicked Quality Resources', 'ai-awareness-day' ), 'sanitize_text_field' ),
		'aiad_handpicked_resources_desc'  => array( __( 'Description', 'ai-awareness-day' ), 'textarea', __( 'A curated selection of interactive AI games and learning tools from trusted organisations.', 'ai-awareness-day' ), 'sanitize_textarea_field' ),
		'aiad_linkedin_post_url'          => array( __( 'LinkedIn post URL', 'ai-awareness-day' ), 'url', '', 'esc_url_raw', __( 'Optional.', 'ai-awareness-day' ) ),
	);
	$fields['contact'] = array(
		'aiad_contact_title' => array( __( 'Title', 'ai-awareness-day' ), 'text', $d['aiad_contact_title'] ?? null, 'sanitize_text_field' ),
		'aiad_contact_desc'  => array( __( 'Description', 'ai-awareness-day' ), 'textarea', $d['aiad_contact_desc'] ?? null, 'wp_kses_post' ),
	);
	return $fields;
}

/**
 * What a wording field shows when its block leaves it empty: the Customizer's value, else the default.
 */
function aiad_homepage_field_fallback( string $key, ?string $default ): string {
	$mods   = get_theme_mods();
	$stored = is_array( $mods ) && isset( $mods[ $key ] ) ? trim( (string) $mods[ $key ] ) : '';
	return '' !== $stored ? $stored : (string) $default;
}

/**
 * A block's wording values for its section, sanitised; empty values dropped.
 *
 * @param mixed $wording The block's wording attribute.
 * @return array<string, string>
 */
function aiad_homepage_section_wording( string $slug, $wording ): array {
	$fields = aiad_homepage_section_fields()[ $slug ] ?? array();
	$clean  = array();
	foreach ( (array) $wording as $key => $value ) {
		if ( ! isset( $fields[ $key ] ) || ! is_scalar( $value ) ) {
			continue;
		}
		$value = trim( (string) call_user_func( $fields[ $key ][3], (string) $value ) );
		if ( '' !== $value ) {
			$clean[ $key ] = $value;
		}
	}
	return $clean;
}

/**
 * Render one homepage section, exactly as front-page.php's section loop does.
 *
 * Wording the block sets is handed to the template through the theme_mod_{name} filters while it renders, so the
 * template itself is unchanged; the filters are removed straight after.
 *
 * @param array<string, string> $wording Sanitised wording (aiad_homepage_section_wording()).
 */
function aiad_render_homepage_section( string $slug, array $wording = array() ): string {
	$filters = array();
	foreach ( $wording as $key => $value ) {
		$filters[ $key ] = static function () use ( $value ) {
			return $value;
		};
		add_filter( "theme_mod_{$key}", $filters[ $key ], 999 );
	}
	ob_start();
	get_template_part(
		'template-parts/front-page/section',
		$slug,
		array(
			'text_alignment_class' => aiad_get_text_alignment_class(),
			'container_class'      => aiad_get_container_width_class(),
		)
	);
	$html = (string) ob_get_clean();
	foreach ( $filters as $key => $filter ) {
		remove_filter( "theme_mod_{$key}", $filter, 999 );
	}
	return $html;
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
	$hero_script = AIAD_DIR . '/assets/js/homepage-hero-edit.js';
	wp_register_script(
		'aiad-homepage-hero-edit',
		AIAD_URI . '/assets/js/homepage-hero-edit.js',
		array( 'wp-element', 'wp-block-editor', 'wp-rich-text', 'wp-escape-html' ),
		file_exists( $hero_script ) ? (string) filemtime( $hero_script ) : AIAD_VERSION,
		true
	);
	wp_register_script(
		'aiad-homepage-section-blocks',
		AIAD_URI . '/assets/js/homepage-section-blocks.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-data', 'aiad-homepage-hero-edit' ),
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
				'attributes'           => isset( aiad_homepage_section_fields()[ $slug ] ) ? array( 'wording' => array( 'type' => 'object', 'default' => array() ) ) : array(),
				'editor_script_handles' => array( 'aiad-homepage-section-blocks' ),
				'render_callback'      => static function ( $attributes ) use ( $slug ): string {
					return aiad_render_homepage_section( $slug, aiad_homepage_section_wording( $slug, $attributes['wording'] ?? array() ) );
				},
			)
		);
	}
	wp_add_inline_script( 'aiad-homepage-section-blocks', 'window.aiadHomepageSections = ' . wp_json_encode( $names ) . ';', 'before' );
}
add_action( 'init', 'aiad_register_homepage_section_blocks' );

/**
 * Give the editor each block's wording fields, with what shows when a field is empty.
 */
function aiad_homepage_section_editor_data(): void {
	$data = array();
	foreach ( aiad_homepage_section_fields() as $slug => $fields ) {
		foreach ( $fields as $key => $f ) {
			$data[ aiad_homepage_section_block_name( $slug ) ][] = array(
				'key'         => $key,
				'label'       => $f[0],
				'type'        => $f[1],
				'placeholder' => wp_strip_all_tags( aiad_homepage_field_fallback( $key, $f[2] ) ),
				'help'        => (string) ( $f[4] ?? '' ),
			);
		}
	}
	wp_add_inline_script( 'aiad-homepage-section-blocks', 'window.aiadHomepageFields = ' . wp_json_encode( $data ) . ';', 'before' );
}
add_action( 'enqueue_block_editor_assets', 'aiad_homepage_section_editor_data' );

/**
 * Copy the wording the Customizer holds into a page's section blocks, for fields the blocks leave empty.
 * The page then shows exactly what it showed before, with the wording owned by its blocks.
 *
 * @return int Number of fields copied.
 */
function aiad_copy_customizer_wording_into_blocks( WP_Post $page ): int {
	$mods   = get_theme_mods();
	$mods   = is_array( $mods ) ? $mods : array();
	$fields = aiad_homepage_section_fields();
	$copied = 0;
	$blocks = parse_blocks( $page->post_content );
	foreach ( $blocks as &$block ) {
		$slug = empty( $block['blockName'] ) ? '' : str_replace( '-', '_', (string) substr( $block['blockName'], strlen( 'aiad/section-' ) ) );
		if ( 0 !== strpos( (string) $block['blockName'], 'aiad/section-' ) || empty( $fields[ $slug ] ) ) {
			continue;
		}
		$wording = (array) ( $block['attrs']['wording'] ?? array() );
		foreach ( $fields[ $slug ] as $key => $f ) {
			$stored = isset( $mods[ $key ] ) ? trim( (string) $mods[ $key ] ) : '';
			if ( '' !== $stored && '' === trim( (string) ( $wording[ $key ] ?? '' ) ) ) {
				$wording[ $key ] = $stored;
				++$copied;
			}
		}
		if ( $wording ) {
			$block['attrs']['wording'] = $wording;
		}
	}
	unset( $block );
	if ( $copied ) {
		// wp_update_post() unslashes its input; slash first, or the \u0026 that serialize_blocks() writes for & loses its backslash.
		wp_update_post( array( 'ID' => $page->ID, 'post_content' => wp_slash( serialize_blocks( $blocks ) ) ) );
	}
	return $copied;
}

/**
 * Swap each section block on a page that has a pattern (aiad_homepage_section_patterns()) for the pattern's blocks,
 * carrying over the wording set in its sidebar, as "Edit on the page" does in the editor
 * (assets/js/homepage-section-blocks.js). The page keeps a revision of how it was. The hero keeps its block: it is
 * edited on the canvas already.
 *
 * @return int Number of sections rebuilt.
 */
function aiad_rebuild_homepage_sections( WP_Post $page ): int {
	$registry = WP_Block_Patterns_Registry::get_instance();
	$targets  = aiad_homepage_pattern_wording_targets();
	$out      = array();
	$rebuilt  = 0;
	foreach ( parse_blocks( $page->post_content ) as $block ) {
		$name       = (string) $block['blockName'];
		$slug       = 0 === strpos( $name, 'aiad/section-' ) ? str_replace( '-', '_', substr( $name, strlen( 'aiad/section-' ) ) ) : '';
		$pattern    = $slug ? ( aiad_homepage_section_patterns()[ $slug ] ?? '' ) : '';
		$registered = $pattern ? $registry->get_registered( $pattern ) : null;
		if ( ! $registered ) {
			$out[] = $block;
			continue;
		}
		$wording        = aiad_homepage_section_wording( $slug, $block['attrs']['wording'] ?? array() );
		$seen           = array();
		$pattern_blocks = parse_blocks( $registered['content'] );
		aiad_apply_pattern_wording( $pattern_blocks, $wording, $targets[ $slug ] ?? array(), $seen );
		foreach ( $pattern_blocks as $pattern_block ) {
			if ( ! empty( $pattern_block['blockName'] ) ) {
				$out[] = $pattern_block;
			}
		}
		++$rebuilt;
	}
	if ( $rebuilt ) {
		// wp_update_post() unslashes its input; slash first (see aiad_copy_customizer_wording_into_blocks()).
		wp_update_post( array( 'ID' => $page->ID, 'post_content' => wp_slash( serialize_blocks( $out ) ) ) );
	}
	return $rebuilt;
}

/**
 * Put sidebar wording into a pattern's parsed blocks (the PHP side of applyWording() in
 * assets/js/homepage-section-blocks.js). The values are already sanitised with their fields' callbacks.
 *
 * @param array<int, array>                   $blocks  Parsed blocks, changed in place.
 * @param array<string, string>               $wording Sanitised wording, by theme mod.
 * @param array<string, array<string, mixed>> $targets aiad_homepage_pattern_wording_targets() for the section.
 * @param array<string, int>                  $seen    How many blocks with each target's class have gone by.
 */
function aiad_apply_pattern_wording( array &$blocks, array $wording, array $targets, array &$seen ): void {
	foreach ( $blocks as &$block ) {
		$name    = (string) $block['blockName'];
		$classes = preg_split( '/\s+/', (string) ( $block['attrs']['className'] ?? '' ) );
		foreach ( $targets as $key => $target ) {
			if ( ! empty( $target['block'] ) ) {
				if ( $target['block'] === $name && isset( $wording[ $key ] ) ) {
					$block['attrs'][ $target['attr'] ] = $wording[ $key ];
				}
				continue;
			}
			if ( ! in_array( $target['class'], $classes, true ) || ! in_array( $name, array( 'core/paragraph', 'core/heading' ), true ) ) {
				continue;
			}
			$nth          = $seen[ $key ] ?? 0;
			$seen[ $key ] = $nth + 1;
			if ( isset( $wording[ $key ] ) && ( $target['nth'] ?? 0 ) === $nth ) {
				$value = empty( $target['html'] ) ? esc_html( $wording[ $key ] ) : $wording[ $key ];
				// A paragraph or heading holds its text between its tag's ">" and "</".
				$html                  = (string) $block['innerHTML'];
				$html                  = substr( $html, 0, strpos( $html, '>' ) + 1 ) . $value . substr( $html, strrpos( $html, '</' ) );
				$block['innerHTML']    = $html;
				$block['innerContent'] = array( $html );
			}
		}
		if ( 'aiad/principle-card' === $name ) {
			$strand = (string) ( $block['attrs']['strand'] ?? 'safe' );
			foreach ( array( 'title' => 'aiad_principle_title_', 'text' => 'aiad_principle_desc_' ) as $attr => $prefix ) {
				if ( isset( $wording[ $prefix . $strand ] ) ) {
					$block['attrs'][ $attr ] = esc_html( $wording[ $prefix . $strand ] );
				}
			}
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			aiad_apply_pattern_wording( $block['innerBlocks'], $wording, $targets, $seen );
		}
	}
	unset( $block );
}

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
	// A section rebuilt from core blocks keeps its pattern's name in the outer group's metadata.
	return str_contains( $post->post_content, '"patternName":"aiad/homepage-' );
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
 * In the editor, give pages that use the section blocks, and the Site Editor, the theme's front-end stylesheets, so the
 * previews in the editor canvas look like the site.
 */
function aiad_homepage_section_editor_styles(): void {
	// The Site Editor edits the header and footer template parts (inc/site-parts.php), which need the same styles.
	$site_editor = function_exists( 'get_current_screen' ) && get_current_screen() && 'site-editor' === get_current_screen()->base;
	if ( ! is_admin() || ! ( $site_editor || aiad_post_has_homepage_sections() ) ) {
		return;
	}
	wp_enqueue_style( 'aiad-fonts-fallback', AIAD_URI . '/assets/css/base/fonts.css', array(), AIAD_VERSION );
	$style = get_stylesheet_directory() . '/style.css';
	wp_enqueue_style( 'aiad-style', get_stylesheet_uri(), array(), file_exists( $style ) ? (string) filemtime( $style ) : AIAD_VERSION );
	if ( function_exists( 'aiad_enqueue_modular_theme_styles' ) ) {
		aiad_enqueue_modular_theme_styles();
	}
	// On the site, main.js fades .fade-up content in as it scrolls into view; the editor has no main.js, so show it.
	// Small screens show three aims until "Show more" is pressed; the editor shows them all so each can be edited.
	// Some of the site's text cannot be selected (the hero headline); in the editor, text being edited can.
	wp_add_inline_style(
		'aiad-style',
		'.editor-styles-wrapper .fade-up { opacity: 1; transform: none; }
		.editor-styles-wrapper .aims-list.wp-block-list > li { display: flex !important; }
		.editor-styles-wrapper [contenteditable="true"] { -webkit-user-select: text; user-select: text; }'
	);
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
	aiad_copy_customizer_wording_into_blocks( get_post( (int) $id ) );
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
 * Handle the page's actions.
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
	} elseif ( 'copy' === $action && aiad_block_homepage_page() ) {
		$copied  = aiad_copy_customizer_wording_into_blocks( aiad_block_homepage_page() );
		$message = 'copied' . $copied;
	} elseif ( 'rebuild' === $action && aiad_block_homepage_page() ) {
		$message = 'rebuilt' . aiad_rebuild_homepage_sections( aiad_block_homepage_page() );
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
	if ( preg_match( '/^rebuilt(\d+)$/', $message, $m ) ) {
		/* translators: %d: number of homepage sections */
		$messages[ $message ] = sprintf( _n( '%d section can now be edited on the page, with its wording and design unchanged. The page\'s Revisions keep the version before.', '%d sections can now be edited on the page, with their wording and design unchanged. The page\'s Revisions keep the version before.', (int) $m[1], 'ai-awareness-day' ), (int) $m[1] );
	}
	if ( preg_match( '/^copied(\d+)$/', $message, $m ) ) {
		/* translators: %d: number of wording fields */
		$messages[ $message ] = sprintf( _n( '%d wording field was copied from the Customizer into the homepage blocks.', '%d wording fields were copied from the Customizer into the homepage blocks.', (int) $m[1], 'ai-awareness-day' ), (int) $m[1] );
	}
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
			<h2><?php esc_html_e( 'Wording', 'ai-awareness-day' ); ?></h2>
			<p><?php esc_html_e( 'Each section\'s wording is edited in its block\'s settings sidebar. An empty field shows the Customizer\'s value or the standard wording. Copying fills the empty fields from the Customizer, so the blocks hold the wording and the page stays the same.', 'ai-awareness-day' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'aiad_block_homepage' ); ?>
				<input type="hidden" name="aiad_block_homepage_action" value="copy" />
				<?php submit_button( __( 'Copy the Customizer wording into the blocks', 'ai-awareness-day' ), 'secondary', 'submit', false ); ?>
			</form>
			<h2><?php esc_html_e( 'Edit on the page', 'ai-awareness-day' ); ?></h2>
			<p><?php esc_html_e( 'Each section can be swapped for ordinary blocks with the same wording and design, so its text is edited directly on the page (the "Edit on the page" button in a section\'s sidebar). This does it for every section at once, keeping the wording each has now. The hero is edited on the page already. The page\'s Revisions keep the version before.', 'ai-awareness-day' ); ?></p>
			<form method="post">
				<?php wp_nonce_field( 'aiad_block_homepage' ); ?>
				<input type="hidden" name="aiad_block_homepage_action" value="rebuild" />
				<?php submit_button( __( 'Make every section editable on the page', 'ai-awareness-day' ), 'secondary', 'submit', false ); ?>
			</form>
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

/**
 * While the block homepage is on, the Customizer's homepage wording, section order and visibility have no effect:
 * hide those fields and say where the homepage is edited now. Site name, dates, images and the hero choice stay.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function aiad_block_homepage_customizer( $wp_customize ): void {
	$page = aiad_block_homepage_page();
	if ( ! $page ) {
		return;
	}
	foreach ( aiad_homepage_section_fields() as $fields ) {
		foreach ( array_keys( $fields ) as $key ) {
			$wp_customize->remove_control( $key );
		}
	}
	foreach ( array_keys( aiad_homepage_section_blocks() ) as $slug ) {
		$wp_customize->remove_control( 'aiad_section_visible_' . $slug );
	}
	$wp_customize->remove_control( 'aiad_section_order' );
	/* translators: %s: link to edit the homepage */
	$note = sprintf( __( 'The homepage is a block page: its sections and wording are edited in the block editor (%s).', 'ai-awareness-day' ), '<a href="' . esc_url( get_edit_post_link( $page->ID ) ) . '">' . esc_html__( 'edit the homepage', 'ai-awareness-day' ) . '</a>' );
	foreach ( array( 'aiad_hero', 'aiad_campaign', 'aiad_contact', 'aiad_front_page_layout' ) as $section_id ) {
		$section = $wp_customize->get_section( $section_id );
		if ( $section ) {
			$section->description = '<p>' . $note . '</p>' . $section->description;
		}
	}
}
add_action( 'customize_register', 'aiad_block_homepage_customizer', 1001 );

/**
 * Appearance → Edit Homepage: while the block homepage is on, say that the homepage's wording is edited in its blocks.
 */
function aiad_block_homepage_edit_homepage_notice(): void {
	$screen = get_current_screen();
	$page   = aiad_block_homepage_page();
	if ( ! $page || ! $screen || 'appearance_page_aiad-edit-homepage' !== $screen->id ) {
		return;
	}
	printf(
		'<div class="notice notice-info"><p>%s <a href="%s">%s</a></p></div>',
		esc_html__( 'The homepage is a block page. Section wording set in its blocks takes the place of these fields; empty block fields still use them.', 'ai-awareness-day' ),
		esc_url( get_edit_post_link( $page->ID ) ),
		esc_html__( 'Edit the homepage', 'ai-awareness-day' )
	);
}
add_action( 'admin_notices', 'aiad_block_homepage_edit_homepage_notice' );

/**
 * Sections that can be rebuilt from core blocks, so they are edited on the page: section slug => pattern
 * (patterns/homepage-{slug}.php). The section block's sidebar offers to swap itself for the pattern.
 *
 * @return array<string, string>
 */
function aiad_homepage_section_patterns(): array {
	return array(
		'campaign'           => 'aiad/homepage-campaign',
		'principles'         => 'aiad/homepage-principles',
		'aim'                => 'aiad/homepage-aim',
		'free_resources'     => 'aiad/homepage-free-resources',
		'featured_resources' => 'aiad/homepage-featured-resources',
		'contact'            => 'aiad/homepage-contact',
	);
}

/**
 * Where a section block's sidebar wording goes when it swaps itself for its pattern: section slug => [ theme mod =>
 * target ]. A target is either a core block by class ( 'class', and 'nth' for a later block with the same class ),
 * whose content the value replaces ( 'html' when the value may hold HTML ), or a block attribute ( 'block', 'attr' ).
 * The principle cards take theirs by strand (assets/js/homepage-section-blocks.js).
 *
 * @return array<string, array<string, array<string, mixed>>>
 */
function aiad_homepage_pattern_wording_targets(): array {
	return array(
		'campaign'           => array(
			'aiad_campaign_title'              => array( 'class' => 'section-title' ),
			'aiad_campaign_text'               => array( 'class' => 'section-desc', 'html' => true ),
			'aiad_campaign_text_2'             => array( 'class' => 'section-desc', 'html' => true, 'nth' => 1 ),
			'aiad_campaign_linkedin_embed_src' => array( 'block' => 'aiad/campaign-embed', 'attr' => 'url' ),
		),
		'free_resources'     => array(
			'aiad_free_resources_title' => array( 'class' => 'section-title' ),
			'aiad_free_resources_desc'  => array( 'class' => 'section-desc' ),
		),
		'featured_resources' => array(
			'aiad_handpicked_resources_title' => array( 'class' => 'section-title' ),
			'aiad_handpicked_resources_desc'  => array( 'class' => 'section-desc' ),
			'aiad_linkedin_post_url'          => array( 'block' => 'aiad/linkedin-card', 'attr' => 'url' ),
		),
		'contact'            => array(
			'aiad_contact_title' => array( 'class' => 'section-title' ),
			'aiad_contact_desc'  => array( 'class' => 'section-desc', 'html' => true ),
		),
	);
}

/**
 * The pattern category the homepage section patterns use.
 */
function aiad_register_homepage_pattern_category(): void {
	register_block_pattern_category( 'aiad-homepage', array( 'label' => __( 'Homepage sections', 'ai-awareness-day' ) ) );
}
add_action( 'init', 'aiad_register_homepage_pattern_category' );

/**
 * Give the editor each section block's pattern, so the block can swap itself for editable core blocks.
 */
function aiad_homepage_section_pattern_data(): void {
	$registry = WP_Block_Patterns_Registry::get_instance();
	$data     = array();
	foreach ( aiad_homepage_section_patterns() as $slug => $pattern ) {
		$registered = $registry->get_registered( $pattern );
		if ( $registered ) {
			$data[ aiad_homepage_section_block_name( $slug ) ] = $registered['content'];
		}
	}
	$targets = array();
	foreach ( aiad_homepage_pattern_wording_targets() as $slug => $fields ) {
		$targets[ aiad_homepage_section_block_name( $slug ) ] = $fields;
	}
	wp_add_inline_script( 'aiad-homepage-section-blocks', 'window.aiadHomepagePatterns = ' . wp_json_encode( $data ) . '; window.aiadHomepagePatternTargets = ' . wp_json_encode( $targets ) . ';', 'before' );
}
add_action( 'enqueue_block_editor_assets', 'aiad_homepage_section_pattern_data' );

/**
 * The Aim section in core blocks: add the "Show more" button the section template prints after the aims list.
 * It is not a block because it only works with the list's script (initAimListExpand in assets/js/main.js).
 *
 * @param string $block_content The list's HTML.
 */
function aiad_homepage_aims_expand_button( string $block_content ): string {
	if ( ! str_contains( $block_content, 'id="aims-list"' ) || substr_count( $block_content, '<li' ) <= 3 ) {
		return $block_content;
	}
	ob_start();
	?>
<div class="aims-expand-wrap">
	<button
		type="button"
		class="aims-expand"
		id="aim-expand"
		aria-expanded="false"
		aria-controls="aims-list"
		data-label-more="<?php echo esc_attr__( 'Show more', 'ai-awareness-day' ); ?>"
		data-label-less="<?php echo esc_attr__( 'Show less', 'ai-awareness-day' ); ?>"
	>
		<?php esc_html_e( 'Show more', 'ai-awareness-day' ); ?>
	</button>
</div>
	<?php
	return $block_content . ob_get_clean();
}
add_filter( 'render_block_core/list', 'aiad_homepage_aims_expand_button' );

/**
 * The blocks the section patterns use where core blocks cannot keep the design (blocks/*): the principles grid and
 * its cards, whose whole card is a link, and the Get Involved form.
 */
function aiad_register_homepage_pattern_blocks(): void {
	register_block_type( AIAD_DIR . '/blocks/principles-grid' );
	register_block_type( AIAD_DIR . '/blocks/principle-card' );
	register_block_type( AIAD_DIR . '/blocks/contact-form' );
}
add_action( 'init', 'aiad_register_homepage_pattern_blocks' );

/**
 * Give the principle card's editor script each strand's icon, name and the site's wording (shown while a card's
 * text is empty).
 */
function aiad_principle_card_editor_data(): void {
	$names = array(
		'safe'        => __( 'Safe', 'ai-awareness-day' ),
		'smart'       => __( 'Smart', 'ai-awareness-day' ),
		'creative'    => __( 'Creative', 'ai-awareness-day' ),
		'responsible' => __( 'Responsible', 'ai-awareness-day' ),
		'future'      => __( 'Future', 'ai-awareness-day' ),
		'literacy'    => __( 'Your AI. Your choices. (literacy logo)', 'ai-awareness-day' ),
	);
	$data = array( 'icons' => array(), 'strands' => array() );
	foreach ( array_keys( aiad_principle_cards() ) as $strand ) {
		$wording                     = aiad_principle_card_wording( $strand );
		$data['strands'][ $strand ] = array( 'label' => $names[ $strand ], 'title' => $wording[0], 'text' => $wording[1] );
		$data['icons'][ $strand ]   = 'literacy' === $strand ? aiad_get_logo_image_url( aiad_get_literacy_logo_attachment_id(), 'medium' ) : aiad_strand_icon_uri( $strand );
	}
	wp_add_inline_script( generate_block_asset_handle( 'aiad/principle-card', 'editorScript' ), 'window.aiadPrincipleCards = ' . wp_json_encode( $data ) . ';', 'before' );
}
add_action( 'enqueue_block_editor_assets', 'aiad_principle_card_editor_data' );

/**
 * What the hero's on-canvas editor shows that is worked out when the page renders (assets/js/homepage-hero-edit.js):
 * the dates, the countdown, whether the portal links show, and the strand names. The previous hero keeps its preview.
 */
function aiad_homepage_hero_editor_data(): void {
	$portal_live = function_exists( 'aiad_portal_is_live' ) ? aiad_portal_is_live() : true;
	$dates       = function_exists( 'aiad_national_conversation_dates' ) ? aiad_national_conversation_dates() : null;
	$countdown   = aiad_national_conversation_countdown();
	$data        = array(
		'previous'  => aiad_homepage_hero_is_previous(),
		'eventDate' => $dates ? wp_date( 'l jS F Y', $dates['event']->getTimestamp() ) : '',
		/* translators: %s: month and year the conversation opens, e.g. January 2027 */
		'starts'    => $dates ? ( $portal_live ? __( 'Now open', 'ai-awareness-day' ) : sprintf( __( 'Starting %s', 'ai-awareness-day' ), wp_date( 'F Y', $dates['opens']->getTimestamp() ) ) ) : '',
		'portal'    => $portal_live ? array( __( 'Nominate a school you work with', 'ai-awareness-day' ), __( 'Already registered? Sign in', 'ai-awareness-day' ) ) : array(),
		'countdown' => $countdown ? array( 'label' => $countdown['label'], 'days' => max( 0, (int) floor( ( $countdown['ts_ms'] / 1000 - time() ) / DAY_IN_SECONDS ) ) ) : null,
		'units'     => array( __( 'Days', 'ai-awareness-day' ), __( 'Hours', 'ai-awareness-day' ), __( 'Minutes', 'ai-awareness-day' ), __( 'Seconds', 'ai-awareness-day' ) ),
		'strands'   => array_map( static fn( array $strand ): string => $strand['name'], aiad_hero27_strand_questions() ),
		'eyebrow'   => __( 'AI Awareness Day', 'ai-awareness-day' ),
	);
	wp_add_inline_script( 'aiad-homepage-hero-edit', 'window.aiadHeroEditor = ' . wp_json_encode( $data ) . ';', 'before' );
}
add_action( 'enqueue_block_editor_assets', 'aiad_homepage_hero_editor_data' );
