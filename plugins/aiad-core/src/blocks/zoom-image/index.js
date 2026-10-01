/**
 * Image that opens full size (block.json): a link to the image, around the image and a label, saved as plain HTML
 * with its class (the walkthrough's .wt-shot__frame). Replace the image from the toolbar; its size and alternative
 * text come with it, and the alternative text can be changed in the sidebar.
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

	function image( a ) {
		return el( 'img', {
			src: a.url,
			width: a.width,
			height: a.height,
			alt: a.alt,
			loading: 'lazy',
			decoding: 'async',
		} );
	}

	blocks.registerBlockType( 'aiad/zoom-image', {
		edit( props ) {
			const a = props.attributes;
			return el(
				element.Fragment,
				null,
				el(
					blockEditor.BlockControls,
					{ group: 'other' },
					el( blockEditor.MediaReplaceFlow, {
						mediaURL: a.url,
						allowedTypes: [ 'image' ],
						accept: 'image/*',
						onSelect( media ) {
							props.setAttributes( {
								url: media.url,
								width: media.width,
								height: media.height,
								alt: media.alt || a.alt,
							} );
						},
						onSelectURL( url ) {
							props.setAttributes( {
								url,
								width: undefined,
								height: undefined,
							} );
						},
					} )
				),
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'Image', 'ai-awareness-day' ) },
						el( components.TextareaControl, {
							label: __( 'Alternative text', 'ai-awareness-day' ),
							help: __(
								'What the image shows, for people who cannot see it.',
								'ai-awareness-day'
							),
							value: a.alt,
							onChange( value ) {
								props.setAttributes( { alt: value } );
							},
							__nextHasNoMarginBottom: true,
						} )
					)
				),
				el(
					'a',
					blockEditor.useBlockProps( {
						href: a.url,
						onClick( event ) {
							event.preventDefault(); // In the editor, a click selects the block.
						},
					} ),
					a.url
						? image( a )
						: el(
								'span',
								null,
								__(
									'Choose an image from the toolbar.',
									'ai-awareness-day'
								)
							),
					el( blockEditor.RichText, {
						tagName: 'span',
						className: 'wt-shot__zoom',
						value: a.label,
						allowedFormats: [],
						placeholder: __( 'Label', 'ai-awareness-day' ),
						onChange( value ) {
							props.setAttributes( { label: value } );
						},
					} )
				)
			);
		},
		save( props ) {
			const a = props.attributes;
			return el(
				'a',
				blockEditor.useBlockProps.save( {
					href: a.url,
					target: '_blank',
					rel: 'noopener',
				} ),
				image( a ),
				el( blockEditor.RichText.Content, {
					tagName: 'span',
					className: 'wt-shot__zoom',
					value: a.label,
				} )
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
