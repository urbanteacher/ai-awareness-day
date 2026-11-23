<?php
/**
 * Partner cards block: the cards and button the homepage's campaign section prints
 * (template-parts/components/partners-grid.php). The reach group around it gets the counts main.js reads
 * (aiad_campaign_section_attributes()).
 *
 * @package AI_Awareness_Day
 */

// This block prints the AI Awareness Day theme's markup with the theme's own functions; under another theme it
// prints nothing rather than stopping the page with a fatal error.
foreach ( array( 'aiad_campaign_partners', 'aiad_campaign_section_attributes' ) as $aiad_theme_fn ) {
	if ( ! function_exists( $aiad_theme_fn ) ) {
		return;
	}
}

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

get_template_part( 'template-parts/components/partners-grid', null, aiad_campaign_partners() );
