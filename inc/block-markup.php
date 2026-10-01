<?php
/**
 * Block markup for the theme's page patterns (patterns/national-conversation.php, patterns/walkthrough.php): each
 * function returns a block exactly as the editor saves it, so the patterns build whole pages from the words of their
 * PHP templates and the editor opens them without changes.
 *
 * Text passed in is HTML, already escaped (aiad_block_text() escapes plain text); attributes are escaped here.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plain text as block content: escaped, with apostrophes as the editor saves them.
 *
 * @param string $text Plain text.
 */
function aiad_block_text( string $text ): string {
	return str_replace( '&#039;', "'", esc_html( $text ) );
}

/**
 * A block's attributes as its comment carries them, leaving out empty ones: ' {...}' or ''.
 *
 * @param array $attributes Attributes.
 */
function aiad_block_attrs( array $attributes ): string {
	$attributes = array_filter( $attributes, static fn( $value ): bool => null !== $value && '' !== $value );
	return $attributes ? ' ' . serialize_block_attributes( $attributes ) : '';
}

/**
 * Blocks in a row, as the editor separates them.
 *
 * @param array $blocks Block markup (empty entries are left out).
 */
function aiad_blocks( array $blocks ): string {
	return implode( "\n\n", array_filter( $blocks ) );
}

/**
 * A paragraph.
 *
 * @param string $html  Content.
 * @param string $class Class.
 * @param string $id    Anchor.
 */
function aiad_block_paragraph( string $html, string $class = '', string $id = '' ): string {
	$open = '<p' . ( '' !== $class ? ' class="' . esc_attr( $class ) . '"' : '' ) . ( '' !== $id ? ' id="' . esc_attr( $id ) . '"' : '' ) . '>';
	return '<!-- wp:paragraph' . aiad_block_attrs( array( 'className' => $class, 'anchor' => $id ) ) . " -->\n" . $open . $html . "</p>\n<!-- /wp:paragraph -->";
}

/**
 * A heading.
 *
 * @param int    $level 1 to 6.
 * @param string $html  Content.
 * @param string $class Class.
 * @param string $id    Anchor.
 */
function aiad_block_heading( int $level, string $html, string $class = '', string $id = '' ): string {
	$json = aiad_block_attrs( array( 'level' => 2 === $level ? null : $level, 'className' => $class, 'anchor' => $id ) );
	$tag  = 'h' . $level;
	return '<!-- wp:heading' . $json . " -->\n<" . $tag . ( '' !== $id ? ' id="' . esc_attr( $id ) . '"' : '' ) . ' class="wp-block-heading' . ( '' !== $class ? ' ' . esc_attr( $class ) : '' ) . '">' . $html . '</' . $tag . ">\n<!-- /wp:heading -->";
}

/**
 * A group.
 *
 * @param array  $inner Inner blocks.
 * @param string $class Class.
 * @param string $tag   Element: div, section, main, aside...
 * @param string $id    Anchor.
 * @param string $name  Name in List View.
 */
function aiad_block_group( array $inner, string $class, string $tag = 'div', string $id = '', string $name = '' ): string {
	$json = aiad_block_attrs(
		array(
			'tagName'   => 'div' === $tag ? null : $tag,
			'metadata'  => '' !== $name ? array( 'name' => $name ) : null,
			'className' => $class,
			'layout'    => array( 'type' => 'default' ),
			'anchor'    => $id,
		)
	);
	return '<!-- wp:group' . $json . " -->\n<" . $tag . ( '' !== $id ? ' id="' . esc_attr( $id ) . '"' : '' ) . ' class="wp-block-group' . ( '' !== $class ? ' ' . esc_attr( $class ) : '' ) . '">' . aiad_blocks( $inner ) . '</' . $tag . ">\n<!-- /wp:group -->";
}

/**
 * A Styled list (blocks/list).
 *
 * @param array  $inner Items.
 * @param string $class Class.
 * @param string $tag   ul, ol, dl, p, div or nav.
 * @param array  $aria  aria-label and/or aria-labelledby.
 */
function aiad_block_styled_list( array $inner, string $class, string $tag = 'ul', array $aria = array() ): string {
	$html = '';
	foreach ( $aria as $name => $value ) {
		$html .= ' ' . $name . '="' . esc_attr( $value ) . '"';
	}
	return '<!-- wp:aiad/list' . aiad_block_attrs( array( 'tagName' => 'ul' === $tag ? null : $tag, 'className' => $class ) ) . " -->\n<" . $tag . $html . ' class="' . esc_attr( $class ) . '">' . aiad_blocks( $inner ) . '</' . $tag . ">\n<!-- /wp:aiad/list -->";
}

/**
 * A Styled list item (blocks/item).
 *
 * @param array  $inner Inner blocks.
 * @param string $class Class.
 * @param string $tag   li or div.
 */
function aiad_block_styled_item( array $inner, string $class = '', string $tag = 'li' ): string {
	return '<!-- wp:aiad/item' . aiad_block_attrs( array( 'tagName' => 'li' === $tag ? null : $tag, 'className' => $class ) ) . " -->\n<" . $tag . ( '' !== $class ? ' class="' . esc_attr( $class ) . '"' : '' ) . '>' . aiad_blocks( $inner ) . '</' . $tag . ">\n<!-- /wp:aiad/item -->";
}

/**
 * A Styled text (blocks/text).
 *
 * @param string $html Content.
 * @param string $tag  dt, dd, li or span.
 */
function aiad_block_styled_text( string $html, string $tag = 'span' ): string {
	return '<!-- wp:aiad/text' . aiad_block_attrs( array( 'tagName' => 'span' === $tag ? null : $tag ) ) . " -->\n<" . $tag . '>' . $html . '</' . $tag . ">\n<!-- /wp:aiad/text -->";
}

/**
 * A core list of plain items.
 *
 * @param array $items   Items' HTML.
 * @param bool  $ordered Numbered.
 */
function aiad_block_list( array $items, bool $ordered = false ): string {
	$tag   = $ordered ? 'ol' : 'ul';
	$lines = array_map( static fn( string $html ): string => "<!-- wp:list-item -->\n<li>" . $html . "</li>\n<!-- /wp:list-item -->", $items );
	return '<!-- wp:list' . aiad_block_attrs( array( 'ordered' => $ordered ? true : null ) ) . " -->\n<" . $tag . ' class="wp-block-list">' . implode( "\n\n", $lines ) . '</' . $tag . ">\n<!-- /wp:list -->";
}

/**
 * An image block with a caption.
 *
 * @param string $src     URL.
 * @param string $alt     Alternative text.
 * @param string $caption Caption (plain text).
 * @param string $class   Class.
 */
function aiad_block_image( string $src, string $alt, string $caption, string $class ): string {
	return '<!-- wp:image' . aiad_block_attrs( array( 'className' => $class ) ) . " -->\n" . '<figure class="wp-block-image ' . esc_attr( $class ) . '"><img src="' . esc_url( $src ) . '" alt="' . esc_attr( $alt ) . '"/><figcaption class="wp-element-caption">' . esc_html( $caption ) . "</figcaption></figure>\n<!-- /wp:image -->";
}

/**
 * A quote of one paragraph.
 *
 * @param string $html  Content.
 * @param string $class Class.
 */
function aiad_block_quote( string $html, string $class ): string {
	return '<!-- wp:quote' . aiad_block_attrs( array( 'className' => $class ) ) . " -->\n" . '<blockquote class="wp-block-quote ' . esc_attr( $class ) . '">' . aiad_block_paragraph( $html ) . "</blockquote>\n<!-- /wp:quote -->";
}

/**
 * A details block: a question and a one-paragraph answer.
 *
 * @param string $summary Summary.
 * @param string $html    Answer.
 * @param string $class   Class.
 */
function aiad_block_details( string $summary, string $html, string $class ): string {
	return '<!-- wp:details' . aiad_block_attrs( array( 'className' => $class ) ) . " -->\n" . '<details class="wp-block-details ' . esc_attr( $class ) . '"><summary>' . $summary . '</summary>' . aiad_block_paragraph( $html ) . "</details>\n<!-- /wp:details -->";
}
