/**
 * Partner logo strip (block.json, render.php): a preview in the editor. The logos are edited under Partners.
 */
( function ( blocks, element, blockEditor, ServerSideRender ) {
	var el = element.createElement;

	function empty() {
		return el( 'p', { className: 'section-desc' }, 'The partner logo strip shows partners that have a featured image. None do yet, so the strip is hidden on the site.' );
	}

	blocks.registerBlockType( 'aiad/partner-marquee', {
		edit: function () {
			return el(
				'div',
				blockEditor.useBlockProps(),
				el( ServerSideRender, { block: 'aiad/partner-marquee', EmptyResponsePlaceholder: empty } )
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.serverSideRender );
