import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { registerToolBlock } from '../../shared/tool-block';

registerToolBlock( metadata, [
	{
		key: 'hideIntro',
		label: __( 'Hide the introduction', 'aiad-core' ),
		type: 'toggle',
	},
	{
		key: 'exploreUrl',
		label: __( '"Explore more" link', 'aiad-core' ),
		type: 'text',
		help: __( 'Leave empty for the default.', 'aiad-core' ),
	},
] );
