<?php
/**
 * AI Tools: the directory row renderer used by the AI Tools archive and the homepage tools section.
 *
 * The ai_tool post type, tool_category taxonomy, seeds, meta and admin UI live in the aiad-core plugin
 * (plugins/aiad-core/modules/ai-tools.php).
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ──────────────────────────────────────────────
   5. Rendering Helper
   ────────────────────────────────────────────── */

/**
 * Render one tool as a directory row: <li> for a .tool-rows list.
 *
 * The old card was a black chamfered panel per tool, and it coloured each
 * category with a strand's bright ("Visual & Creative" in Smart orange, three
 * unrelated categories in Safe cyan). Colour on this site means strand, so that
 * told teachers the wrong thing. Rows use no strand colour at all. The tool's
 * initial sits in the style guide's letter tile, its one-cut chamfer.
 *
 * The whole row is one link to the tool's site. data-tool-id is what
 * assets/js/engagement-tracking.js counts clicks by.
 *
 * @param WP_Post $tool The ai_tool post object.
 * @param array   $opts {
 *     @type bool $show_category Print the category label. Off inside the archive's
 *                               category groups, whose heading already says it.
 *     @type bool $show_features Print the features, joined on one line.
 * }
 * @return string HTML markup.
 */
function aiad_render_tool_row( WP_Post $tool, array $opts = array() ): string {
	$opts = wp_parse_args( $opts, array( 'show_category' => true, 'show_features' => false ) );

	$url          = get_post_meta( $tool->ID, '_aiad_tool_url', true );
	$use_case     = get_post_meta( $tool->ID, '_aiad_tool_use_case', true );
	$features_raw = get_post_meta( $tool->ID, '_aiad_tool_features', true );
	$features     = $features_raw ? array_values( array_filter( array_map( 'trim', explode( "\n", $features_raw ) ) ) ) : array();

	$terms    = get_the_terms( $tool->ID, 'tool_category' );
	$category = ( $terms && ! is_wp_error( $terms ) ) ? html_entity_decode( $terms[0]->name, ENT_QUOTES, 'UTF-8' ) : '';

	$title   = html_entity_decode( get_the_title( $tool ), ENT_QUOTES, 'UTF-8' );
	$initial = preg_match( '/[\p{L}\p{N}]/u', $title, $m ) ? mb_strtoupper( $m[0] ) : '·';

	$tag   = $url ? 'a' : 'div';
	$attrs = $url
		? sprintf( ' href="%s" data-tool-id="%d" target="_blank" rel="noopener noreferrer"', esc_url( $url ), (int) $tool->ID )
		: '';

	ob_start();
	?>
	<li class="tool-row">
		<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- a or div. ?> class="tool-row__inner"<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>>
			<span class="tool-row__tile" aria-hidden="true"><?php echo esc_html( $initial ); ?></span>
			<span class="tool-row__body">
				<?php if ( $opts['show_category'] && $category ) : ?>
					<span class="tool-row__cat"><?php echo esc_html( $category ); ?></span>
				<?php endif; ?>
				<span class="tool-row__name"><?php echo esc_html( $title ); ?></span>
				<?php if ( $use_case ) : ?>
					<span class="tool-row__use"><?php echo esc_html( $use_case ); ?></span>
				<?php endif; ?>
				<?php if ( $opts['show_features'] && $features ) : ?>
					<span class="tool-row__features"><?php echo esc_html( implode( ' · ', array_slice( $features, 0, 3 ) ) ); ?></span>
				<?php endif; ?>
			</span>
			<?php if ( $url ) : ?>
				<span class="tool-row__go" aria-hidden="true">&#8599;</span>
				<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'ai-awareness-day' ); ?></span>
			<?php endif; ?>
		</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	</li>
	<?php
	return ob_get_clean();
}
