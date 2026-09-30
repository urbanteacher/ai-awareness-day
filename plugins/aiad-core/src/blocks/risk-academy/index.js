import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { registerToolBlock } from '../../shared/tool-block';

registerToolBlock( metadata, [
	{ key: 'hero', label: __( 'Hero', 'aiad-core' ), type: 'toggle' },
	{
		key: 'methodology',
		label: __( 'Methodology', 'aiad-core' ),
		type: 'toggle',
	},
	{ key: 'meter', label: __( 'Risk meter', 'aiad-core' ), type: 'toggle' },
	{
		key: 'curriculum',
		label: __( 'Curriculum', 'aiad-core' ),
		type: 'toggle',
	},
	{
		key: 'contributors',
		label: __( 'Contributors', 'aiad-core' ),
		type: 'toggle',
	},
	{ key: 'resources', label: __( 'Resources', 'aiad-core' ), type: 'toggle' },
	{ key: 'sources', label: __( 'Sources', 'aiad-core' ), type: 'toggle' },
	{ key: 'enrol', label: __( 'Enrol', 'aiad-core' ), type: 'toggle' },
] );
