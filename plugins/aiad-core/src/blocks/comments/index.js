/**
 * Comments (block.json, render.php): they depend on the post, so the editor shows a note.
 */
// Side-effect imports so the build lists these packages as the script's dependencies; the code uses window.wp.
import '@wordpress/block-editor';
import '@wordpress/blocks';
import '@wordpress/element';

( function ( blocks, element, blockEditor ) {
	const el = element.createElement;

	blocks.registerBlockType( 'aiad/comments', {
		edit() {
			return el(
				'p',
				blockEditor.useBlockProps(),
				'Comments: the post’s comments and comment form show here on the site, when comments are open.'
			);
		},
		save() {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor );
