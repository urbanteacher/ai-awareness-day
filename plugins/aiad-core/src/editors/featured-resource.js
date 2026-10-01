/**
 * Featured resources: not converted to a Details panel yet; their meta box
 * ("External resource link, theme & attribution") still holds the theme and the
 * session length. Turning on REST for those two taxonomies (for lessons) gave
 * these screens core's panels for the same fields as well, two controls for one
 * value, so core's are hidden until the type is converted in its turn
 * (docs/WP71-STANDARDISATION.md). Formats has always had its core panel.
 */
import { hideCorePanels } from '../shared/record-editor';

hideCorePanels( {
	postType: 'featured_resource',
	name: 'aiad-featured-resource-panels',
	panels: [
		'taxonomy-panel-resource_principle',
		'taxonomy-panel-resource_duration',
	],
} );
