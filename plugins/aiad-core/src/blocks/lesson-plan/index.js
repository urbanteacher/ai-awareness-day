/**
 * Lesson plan: a lesson's content, edited on the canvas (edit.js), and its
 * Lesson details panel in the sidebar (details.js). The block saves nothing:
 * the lesson's meta is what the lesson page draws. See
 * modules/post-types/resource-editor.php.
 */
import { registerBlockType } from '@wordpress/blocks';

import './editor.scss';
import metadata from './block.json';
import Edit from './edit';
import './details';

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
} );
