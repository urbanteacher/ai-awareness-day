/**
 * National Conversation buttons (block.json, render.php): shown in the editor as the page shows them today.
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

	blocks.registerBlockType( 'aiad/nc-actions', {
		edit( props ) {
			return el(
				element.Fragment,
				null,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'Buttons', 'ai-awareness-day' ) },
						el( components.SelectControl, {
							label: __( 'Which buttons', 'ai-awareness-day' ),
							value: props.attributes.variant,
							options: [
								{
									label: __(
										'Top of the page',
										'ai-awareness-day'
									),
									value: 'hero',
								},
								{
									label: __(
										'Closing section',
										'ai-awareness-day'
									),
									value: 'cta',
								},
								{
									label: __(
										'Nominate a school link',
										'ai-awareness-day'
									),
									value: 'nominate',
								},
							],
							onChange( value ) {
								props.setAttributes( { variant: value } );
							},
							help: __(
								'They change on the day the conversation opens, so they are not edited here.',
								'ai-awareness-day'
							),
							__nextHasNoMarginBottom: true,
						} )
					)
				),
				el(
					'div',
					blockEditor.useBlockProps(),
					el( serverSideRender, {
						block: 'aiad/nc-actions',
						attributes: props.attributes,
						EmptyResponsePlaceholder() {
							return el(
								'p',
								{ className: 'ncp-note' },
								__(
									'Nominate a school link: shown here once the conversation opens.',
									'ai-awareness-day'
								)
							);
						},
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
