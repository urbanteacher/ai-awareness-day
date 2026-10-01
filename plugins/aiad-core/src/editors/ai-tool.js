/**
 * AI tools: the Tool details panel.
 *
 * AI tools had no editor support, so WordPress opened them on the classic edit
 * screen, a page of its own with a "Tool Details" meta box and none of the block
 * editor. They now open in the block editor like everything else. This is the old
 * box (modules/ai-tools.php) with the same three fields. The category is core's
 * own panel. A tool also has a body now, which is empty and which the front end
 * does not read.
 */
import { __ } from '@wordpress/i18n';
import { TextControl, TextareaControl } from '@wordpress/components';

import { registerRecordDetails } from '../shared/record-editor';

function ToolFields( { set, text } ) {
	return (
		<>
			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				type="url"
				label={ __( 'Website URL', 'aiad-core' ) }
				placeholder="https://example.com"
				value={ text( '_aiad_tool_url' ) }
				onChange={ set( '_aiad_tool_url' ) }
			/>

			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ __( 'Use case', 'aiad-core' ) }
				help={ __(
					'Short description shown on the card.',
					'aiad-core'
				) }
				placeholder={ __(
					'e.g. Lesson planning, differentiation, feedback',
					'aiad-core'
				) }
				value={ text( '_aiad_tool_use_case' ) }
				onChange={ set( '_aiad_tool_use_case' ) }
			/>

			<TextareaControl
				__nextHasNoMarginBottom
				label={ __( 'Key features', 'aiad-core' ) }
				help={ __(
					'One feature per line. The first 3 are shown on cards; the rest appear as "+N more".',
					'aiad-core'
				) }
				placeholder={ __( 'One feature per line', 'aiad-core' ) }
				rows={ 6 }
				value={ text( '_aiad_tool_features' ) }
				onChange={ set( '_aiad_tool_features' ) }
			/>
		</>
	);
}

registerRecordDetails( {
	postType: 'ai_tool',
	name: 'aiad-tool-details',
	title: __( 'Tool details', 'aiad-core' ),
	Fields: ToolFields,
} );
