import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

const SETTINGS = [
	{
		key: 'questions',
		label: __( 'Questions', 'aiad-core' ),
		min: 5,
		max: 30,
	},
	{
		key: 'seconds',
		label: __( 'Seconds per question', 'aiad-core' ),
		min: 5,
		max: 60,
	},
	{ key: 'bonus', label: __( 'Speed bonus', 'aiad-core' ), min: 0, max: 50 },
	{
		key: 'points',
		label: __( 'Points per answer', 'aiad-core' ),
		min: 10,
		max: 500,
		step: 10,
	},
];

export default function Edit( { attributes, setAttributes } ) {
	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title={ __( 'Quiz settings', 'aiad-core' ) }>
					{ SETTINGS.map( ( { key, label, min, max, step = 1 } ) => (
						<RangeControl
							key={ key }
							label={ label }
							value={ attributes[ key ] }
							onChange={ ( value ) =>
								setAttributes( { [ key ]: value } )
							}
							min={ min }
							max={ max }
							step={ step }
							__nextHasNoMarginBottom
							__next40pxDefaultSize
						/>
					) ) }
				</PanelBody>
			</InspectorControls>
			{ /* The quiz's own JS only runs on the front end, so the editor shows its start card. */ }
			<ServerSideRender
				block="aiad/speed-quiz"
				attributes={ attributes }
			/>
		</div>
	);
}
