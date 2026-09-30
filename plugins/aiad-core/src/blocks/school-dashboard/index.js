import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { registerToolBlock } from '../../shared/tool-block';

registerToolBlock( metadata, [
	{
		key: 'school',
		label: __( 'School', 'aiad-core' ),
		type: 'text',
		help: __(
			'Leave empty to use the ?school= address parameter, as the shortcode does.',
			'aiad-core'
		),
	},
] );
