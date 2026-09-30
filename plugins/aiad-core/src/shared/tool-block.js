import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Register one of the interactive tools as a dynamic block.
 *
 * render.php builds the front end from the tool's shortcode function, so there is nothing to save. The editor shows
 * the same server-rendered markup; the tool's own script only runs on the front end.
 *
 * @param {Object} metadata The block's block.json.
 * @param {Array}  controls Sidebar settings: { key, label, type: 'toggle' | 'text' | 'select', options, help }.
 */
export function registerToolBlock( metadata, controls = [] ) {
	function ToolEdit( { attributes, setAttributes } ) {
		const blockProps = useBlockProps();
		const set = ( key ) => ( value ) => setAttributes( { [ key ]: value } );

		return (
			<div { ...blockProps }>
				{ controls.length > 0 && (
					<InspectorControls>
						<PanelBody title={ metadata.title }>
							{ controls.map(
								( { key, label, type, options, help } ) => {
									if ( 'toggle' === type ) {
										return (
											<ToggleControl
												key={ key }
												label={ label }
												help={ help }
												checked={ !! attributes[ key ] }
												onChange={ set( key ) }
												__nextHasNoMarginBottom
											/>
										);
									}
									if ( 'select' === type ) {
										return (
											<SelectControl
												key={ key }
												label={ label }
												help={ help }
												value={ attributes[ key ] }
												options={ options }
												onChange={ set( key ) }
												__nextHasNoMarginBottom
												__next40pxDefaultSize
											/>
										);
									}
									return (
										<TextControl
											key={ key }
											label={ label }
											help={ help }
											value={ attributes[ key ] || '' }
											onChange={ set( key ) }
											__nextHasNoMarginBottom
											__next40pxDefaultSize
										/>
									);
								}
							) }
						</PanelBody>
					</InspectorControls>
				) }
				<ServerSideRender
					block={ metadata.name }
					attributes={ attributes }
				/>
			</div>
		);
	}

	registerBlockType( metadata.name, {
		edit: ToolEdit,
		save: () => null,
	} );
}
