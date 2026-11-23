/**
 * Styled list (block.json): a ul, ol or dl (or a p or div of tags, or a nav around a list) whose items are Styled list items or Styled text.
 * Saved as plain HTML with its class, so the page's styles apply in the editor and on the site alike.
 */
// Side-effect imports so the build lists these packages as the script's dependencies; the code uses window.wp.
import '@wordpress/block-editor';
import '@wordpress/blocks';
import '@wordpress/components';
import '@wordpress/element';
import '@wordpress/i18n';

( function ( blocks, element, blockEditor, components, i18n ) {
	const el = element.createElement;
	const __ = i18n.__;

	function aria( attributes ) {
		return {
			'aria-label': attributes.ariaLabel || undefined,
			'aria-labelledby': attributes.ariaLabelledby || undefined,
		};
	}

	blocks.registerBlockType( 'aiad/list', {
		edit( props ) {
			const a = props.attributes;
			const inner = blockEditor.useInnerBlocksProps(
				blockEditor.useBlockProps( aria( a ) ),
				{
					defaultBlock: { name: 'aiad/item' },
					directInsert: true,
				}
			);
			return el(
				element.Fragment,
				null,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'List', 'aiad-core' ) },
						el( components.SelectControl, {
							label: __( 'HTML element', 'aiad-core' ),
							value: a.tagName,
							options: [
								{
									label: __(
										'Bulleted list (ul)',
										'aiad-core'
									),
									value: 'ul',
								},
								{
									label: __(
										'Numbered list (ol)',
										'aiad-core'
									),
									value: 'ol',
								},
								{
									label: __(
										'Terms and descriptions (dl)',
										'aiad-core'
									),
									value: 'dl',
								},
								{
									label: __(
										'Paragraph of tags (p)',
										'aiad-core'
									),
									value: 'p',
								},
								{
									label: __(
										'Box (div)',
										'aiad-core'
									),
									value: 'div',
								},
								{
									label: __(
										'Navigation (nav)',
										'aiad-core'
									),
									value: 'nav',
								},
							],
							onChange( value ) {
								props.setAttributes( { tagName: value } );
							},
							__nextHasNoMarginBottom: true,
						} ),
						el( components.TextControl, {
							label: __(
								'Name for screen readers (aria-label)',
								'aiad-core'
							),
							value: a.ariaLabel || '',
							onChange( value ) {
								props.setAttributes( {
									ariaLabel: value || undefined,
								} );
							},
							__nextHasNoMarginBottom: true,
						} ),
						el( components.TextControl, {
							label: __(
								'Named by the element with this ID (aria-labelledby)',
								'aiad-core'
							),
							value: a.ariaLabelledby || '',
							onChange( value ) {
								props.setAttributes( {
									ariaLabelledby: value || undefined,
								} );
							},
							__nextHasNoMarginBottom: true,
						} )
					)
				),
				el( a.tagName, inner )
			);
		},
		save( props ) {
			const a = props.attributes;
			return el(
				a.tagName,
				blockEditor.useInnerBlocksProps.save(
					blockEditor.useBlockProps.save( aria( a ) )
				)
			);
		},
	} );
} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n
);
