/**
 * Principles grid (block.json, render.php): holds the principle cards. The cards sit directly in the grid in the
 * editor too, so the theme's grid and phone carousel styles apply there as on the site.
 */
// Side-effect imports so the build lists these packages as the script's dependencies; the code uses window.wp.
import '@wordpress/block-editor';
import '@wordpress/blocks';
import '@wordpress/element';

( function ( blocks, element, blockEditor ) {
	const el = element.createElement;

	blocks.registerBlockType( 'aiad/principles-grid', {
		edit() {
			const blockProps = blockEditor.useBlockProps( {
				className: 'principles-grid',
			} );
			return el(
				'div',
				blockEditor.useInnerBlocksProps( blockProps, {
					allowedBlocks: [ 'aiad/principle-card' ],
					orientation: 'horizontal',
					renderAppender: false,
				} )
			);
		},
		save() {
			return el( blockEditor.InnerBlocks.Content );
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor );
