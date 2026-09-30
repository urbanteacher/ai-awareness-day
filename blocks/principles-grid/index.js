/**
 * Principles grid (block.json, render.php): holds the principle cards. The cards sit directly in the grid in the
 * editor too, so the theme's grid and phone carousel styles apply there as on the site.
 */
( function ( blocks, element, blockEditor ) {
	var el = element.createElement;

	blocks.registerBlockType( 'aiad/principles-grid', {
		edit: function () {
			var blockProps = blockEditor.useBlockProps( { className: 'principles-grid' } );
			return el(
				'div',
				blockEditor.useInnerBlocksProps( blockProps, {
					allowedBlocks: [ 'aiad/principle-card' ],
					orientation: 'horizontal',
					renderAppender: false,
				} )
			);
		},
		save: function () {
			return el( blockEditor.InnerBlocks.Content );
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor );
