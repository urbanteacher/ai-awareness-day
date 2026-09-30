import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { registerToolBlock } from '../../shared/tool-block';

registerToolBlock( metadata, [
	{
		key: 'hideIntro',
		label: __( 'Hide the introduction', 'aiad-core' ),
		type: 'toggle',
	},
	{ key: 'quiz', label: __( 'Show the quiz', 'aiad-core' ), type: 'toggle' },
] );
