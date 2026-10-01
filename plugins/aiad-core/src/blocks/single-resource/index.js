/**
 * Lesson page (block.json, render.php): a placeholder in the editor. The page is printed from the post or archive being
 * shown, which the editor does not have, so there is no preview.
 */
// Side-effect imports so the build lists these packages as the script's dependencies; the code uses window.wp.
import '@wordpress/block-editor';
import '@wordpress/blocks';
import '@wordpress/element';

( function ( blocks, element, blockEditor ) {
	const el = element.createElement;

	blocks.registerBlockType( 'aiad/single-resource', {
		edit() {
			return el(
				'div',
				blockEditor.useBlockProps( {
					style: { padding: '2rem', border: '1px dashed #999' },
				} ),
				el( 'strong', null, 'Lesson page' ),
				el(
					'p',
					{ style: { margin: '0.5rem 0 0' } },
					'The lesson page: what it is, what to get ready, the timed steps with the video beside them, the question for the board and the reference material. Printed from the resource being shown.'
				)
			);
		},
		save() {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor );
