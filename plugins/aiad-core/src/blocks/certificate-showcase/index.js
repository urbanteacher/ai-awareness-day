import { __ } from '@wordpress/i18n';
import metadata from './block.json';
import { registerToolBlock } from '../../shared/tool-block';

const DEFAULT_HELP = __( 'Leave empty for the default wording.', 'aiad-core' );

registerToolBlock( metadata, [
	{
		key: 'eyebrow',
		label: __( 'Label', 'aiad-core' ),
		type: 'text',
		help: DEFAULT_HELP,
	},
	{
		key: 'title',
		label: __( 'Heading', 'aiad-core' ),
		type: 'text',
		help: DEFAULT_HELP,
	},
	{
		key: 'lead',
		label: __( 'Intro', 'aiad-core' ),
		type: 'text',
		help: DEFAULT_HELP,
	},
	{
		key: 'ctaText',
		label: __( 'Button text', 'aiad-core' ),
		type: 'text',
		help: DEFAULT_HELP,
	},
	{
		key: 'ctaUrl',
		label: __( 'Button link', 'aiad-core' ),
		type: 'text',
		help: __( 'Leave empty for the benchmark start page.', 'aiad-core' ),
	},
	{
		key: 'certEyebrow',
		label: __( 'Certificate label', 'aiad-core' ),
		type: 'text',
		help: DEFAULT_HELP,
	},
	{
		key: 'certTitle',
		label: __( 'Certificate heading', 'aiad-core' ),
		type: 'text',
		help: DEFAULT_HELP,
	},
	{
		key: 'certLead',
		label: __( 'Certificate intro', 'aiad-core' ),
		type: 'text',
		help: DEFAULT_HELP,
	},
	{
		key: 'strand',
		label: __( 'Example certificate strand', 'aiad-core' ),
		type: 'select',
		options: [
			{ value: 'safe', label: __( 'Safe', 'aiad-core' ) },
			{ value: 'smart', label: __( 'Smart', 'aiad-core' ) },
			{ value: 'creative', label: __( 'Creative', 'aiad-core' ) },
			{ value: 'responsible', label: __( 'Responsible', 'aiad-core' ) },
			{ value: 'future', label: __( 'Future', 'aiad-core' ) },
		],
	},
	{
		key: 'tone',
		label: __( 'Background', 'aiad-core' ),
		type: 'select',
		options: [
			{ value: '', label: __( 'Default (ink)', 'aiad-core' ) },
			{ value: 'ink', label: __( 'Ink', 'aiad-core' ) },
			{ value: 'cream', label: __( 'Cream', 'aiad-core' ) },
		],
	},
	{
		key: 'sectionId',
		label: __( 'Section ID', 'aiad-core' ),
		type: 'text',
		help: __(
			'Anchor for links to this section. Default: benchmark-audit.',
			'aiad-core'
		),
	},
] );
