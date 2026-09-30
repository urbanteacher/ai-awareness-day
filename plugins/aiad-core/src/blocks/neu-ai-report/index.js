import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { registerToolBlock } from '../../shared/tool-block';

registerToolBlock( metadata, [
	{
		key: 'headline',
		label: __( 'Headline', 'aiad-core' ),
		type: 'select',
		options: [
			{
				value: 'auto',
				label: __(
					'Automatic (shown except on single pages)',
					'aiad-core'
				),
			},
			{ value: '1', label: __( 'Show', 'aiad-core' ) },
			{ value: '0', label: __( 'Hide', 'aiad-core' ) },
		],
	},
] );
