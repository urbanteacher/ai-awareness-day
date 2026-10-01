/**
 * Strand icon (block.json, render.php): the strand's mark, shown in the editor as the page shows it.
 */
// Side-effect imports so the build lists these packages as the script's dependencies; the code uses window.wp.
import '@wordpress/block-editor';
import '@wordpress/blocks';
import '@wordpress/components';
import '@wordpress/element';
import '@wordpress/i18n';
import '@wordpress/server-side-render';

( function (
	blocks,
	element,
	blockEditor,
	components,
	i18n,
	serverSideRender
) {
	const el = element.createElement;
	const __ = i18n.__;

	blocks.registerBlockType( 'aiad/strand-icon', {
		edit( props ) {
			return el(
				element.Fragment,
				null,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'Strand', 'ai-awareness-day' ) },
						el( components.SelectControl, {
							label: __( 'Strand', 'ai-awareness-day' ),
							value: props.attributes.strand,
							options: [
								'safe',
								'smart',
								'creative',
								'responsible',
								'future',
							].map( function ( slug ) {
								return {
									label:
										slug.charAt( 0 ).toUpperCase() +
										slug.slice( 1 ),
									value: slug,
								};
							} ),
							onChange( value ) {
								props.setAttributes( { strand: value } );
							},
							__nextHasNoMarginBottom: true,
						} )
					)
				),
				el(
					'div',
					blockEditor.useBlockProps( {
						style: { display: 'contents' },
					} ),
					el( serverSideRender, {
						block: 'aiad/strand-icon',
						attributes: props.attributes,
					} )
				)
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
	window.wp.components,
	window.wp.i18n,
	window.wp.serverSideRender
);
