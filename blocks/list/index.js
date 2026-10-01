/**
 * Styled list (block.json): a ul, ol or dl (or a p or div of tags, or a nav around a list) whose items are Styled list items or Styled text.
 * Saved as plain HTML with its class, so the page's styles apply in the editor and on the site alike.
 */
( function ( blocks, element, blockEditor, components, i18n ) {
	var el = element.createElement;
	var __ = i18n.__;

	function aria( attributes ) {
		return {
			'aria-label': attributes.ariaLabel || undefined,
			'aria-labelledby': attributes.ariaLabelledby || undefined,
		};
	}

	blocks.registerBlockType( 'aiad/list', {
		edit: function ( props ) {
			var a = props.attributes;
			var inner = blockEditor.useInnerBlocksProps( blockEditor.useBlockProps( aria( a ) ), {
				defaultBlock: { name: 'aiad/item' },
				directInsert: true,
			} );
			return el(
				element.Fragment,
				null,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'List', 'ai-awareness-day' ) },
						el( components.SelectControl, {
							label: __( 'HTML element', 'ai-awareness-day' ),
							value: a.tagName,
							options: [
								{ label: __( 'Bulleted list (ul)', 'ai-awareness-day' ), value: 'ul' },
								{ label: __( 'Numbered list (ol)', 'ai-awareness-day' ), value: 'ol' },
								{ label: __( 'Terms and descriptions (dl)', 'ai-awareness-day' ), value: 'dl' },
								{ label: __( 'Paragraph of tags (p)', 'ai-awareness-day' ), value: 'p' },
								{ label: __( 'Box (div)', 'ai-awareness-day' ), value: 'div' },
								{ label: __( 'Navigation (nav)', 'ai-awareness-day' ), value: 'nav' },
							],
							onChange: function ( value ) {
								props.setAttributes( { tagName: value } );
							},
							__nextHasNoMarginBottom: true,
						} ),
						el( components.TextControl, {
							label: __( 'Name for screen readers (aria-label)', 'ai-awareness-day' ),
							value: a.ariaLabel || '',
							onChange: function ( value ) {
								props.setAttributes( { ariaLabel: value || undefined } );
							},
							__nextHasNoMarginBottom: true,
						} ),
						el( components.TextControl, {
							label: __( 'Named by the element with this ID (aria-labelledby)', 'ai-awareness-day' ),
							value: a.ariaLabelledby || '',
							onChange: function ( value ) {
								props.setAttributes( { ariaLabelledby: value || undefined } );
							},
							__nextHasNoMarginBottom: true,
						} )
					)
				),
				el( a.tagName, inner )
			);
		},
		save: function ( props ) {
			var a = props.attributes;
			return el( a.tagName, blockEditor.useInnerBlocksProps.save( blockEditor.useBlockProps.save( aria( a ) ) ) );
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n );
