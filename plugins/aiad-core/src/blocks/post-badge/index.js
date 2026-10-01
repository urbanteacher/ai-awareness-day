/**
 * Featured badge (block.json, render.php): it depends on the post it is shown with, so the editor shows a note.
 */
// Side-effect imports so the build lists these packages as the script's dependencies; the code uses window.wp.
import '@wordpress/block-editor';
import '@wordpress/blocks';
import '@wordpress/element';

( function ( blocks, element, blockEditor ) {
	const el = element.createElement;

	blocks.registerBlockType( 'aiad/post-badge', {
		edit() {
			return el(
				'p',
				blockEditor.useBlockProps(),
				'Featured badge: shows “Featured” here on sticky posts.'
			);
		},
		save() {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor );
