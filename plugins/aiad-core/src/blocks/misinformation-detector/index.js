import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { registerToolBlock } from '../../shared/tool-block';

registerToolBlock( metadata, [
	{
		key: 'hideIntro',
		label: __( 'Introduction', 'aiad-core' ),
		type: 'select',
		options: [
			{
				value: 'auto',
				label: __( 'Automatic (hidden on single pages)', 'aiad-core' ),
			},
			{ value: '0', label: __( 'Show', 'aiad-core' ) },
			{ value: '1', label: __( 'Hide', 'aiad-core' ) },
		],
	},
] );
