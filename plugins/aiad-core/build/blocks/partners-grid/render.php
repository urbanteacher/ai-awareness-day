<?php
/**
 * Partner cards block: the cards and button the homepage's campaign section prints
 * (template-parts/components/partners-grid.php). The reach group around it gets the counts main.js reads
 * (aiad_campaign_section_attributes()).
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

get_template_part( 'template-parts/components/partners-grid', null, aiad_campaign_partners() );
