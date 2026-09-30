import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';

// Dynamic block: render.php builds the front end, so there is nothing to save.
registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
} );
