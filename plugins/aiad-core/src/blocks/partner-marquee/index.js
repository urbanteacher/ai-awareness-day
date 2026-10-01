/**
 * Partner logo strip (block.json, render.php): a preview in the editor. The logos are edited under Partners.
 */
// Side-effect imports so the build lists these packages as the script's dependencies; the code uses window.wp.
import '@wordpress/block-editor';
import '@wordpress/blocks';
import '@wordpress/element';
import '@wordpress/server-side-render';

( function ( blocks, element, blockEditor, ServerSideRender ) {
	const el = element.createElement;

	function empty() {
		return el(
			'p',
			{ className: 'section-desc' },
			'The partner logo strip shows partners that have a featured image. None do yet, so the strip is hidden on the site.'
		);
	}

	blocks.registerBlockType( 'aiad/partner-marquee', {
		edit() {
			return el(
				'div',
				blockEditor.useBlockProps(),
				el( ServerSideRender, {
					block: 'aiad/partner-marquee',
					EmptyResponsePlaceholder: empty,
				} )
			);
		},
		save() {
			return null;
		},
	} );
} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.serverSideRender
);
