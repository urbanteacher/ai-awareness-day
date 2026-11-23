<?php
/**
 * The AI Literacy Periodic Table page's presentation in the theme: its stylesheet even without the table block, the
 * "scripted" class before first paint, the document title, a body class, and the homepage promo card.
 *
 * The table itself, its data, pictures, script, stylesheet and admin screen are in the AI Awareness Day Core plugin
 * (plugins/aiad-core/modules/literacy-table/), which the theme always loads.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// The page's own stylesheet, for its introduction and classroom ideas, even if the table's block is taken out.
add_action(
	'wp_enqueue_scripts',
	static function (): void {
		if ( ! is_admin() && ( function_exists( 'aiad_is_literacy_table_page' ) && aiad_is_literacy_table_page() ) ) {
			wp_enqueue_style( 'aiad-literacy-table' );
		}
	},
	15
);

// Mark the page as scripted before it first paints, so the search, filters and
// panel buttons are laid out from the start instead of pushing the table down
// when the deferred script runs. Without JavaScript the CSS keeps them hidden.
add_action(
	'wp_head',
	static function (): void {
		if ( ( function_exists( 'aiad_is_literacy_table_page' ) && aiad_is_literacy_table_page() ) ) {
			wp_print_inline_script_tag( "document.documentElement.classList.add('alpt-js');" );
		}
	},
	1
);

add_filter(
	'pre_get_document_title',
	static function ( string $title ): string {
		return ( function_exists( 'aiad_is_literacy_table_page' ) && aiad_is_literacy_table_page() ) ? __( 'The AI Literacy Periodic Table for teachers and students | AI Awareness Day', 'ai-awareness-day' ) : $title;
	},
	20
);

add_filter(
	'body_class',
	static function ( array $classes ): array {
		if ( ( function_exists( 'aiad_is_literacy_table_page' ) && aiad_is_literacy_table_page() ) ) {
			$classes[] = 'literacy-table-page';
		}
		return $classes;
	}
);

/*
 * The homepage promo (template-parts/front-page/part-literacy-table-promo.php) goes at the end of the Handpicked
 * resources section, under its "View all" link. That section is an editable group on the Home page, so the card is
 * added inside it as the page is shown (before the container's closing tag) rather than written into the page.
 */
add_filter(
	'render_block_core/group',
	static function ( string $html, array $block ): string {
		if ( 'partner-resources' !== ( $block['attrs']['anchor'] ?? '' ) || ! is_front_page() ) {
			return $html;
		}
		ob_start();
		get_template_part( 'template-parts/front-page/part', 'literacy-table-promo' );
		$promo = (string) ob_get_clean();
		$end   = strrpos( $html, '</div>' );
		return false === $end || '' === $promo ? $html : substr( $html, 0, $end ) . $promo . substr( $html, $end );
	},
	10,
	2
);
