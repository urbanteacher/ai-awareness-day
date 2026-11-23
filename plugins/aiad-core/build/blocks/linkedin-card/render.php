<?php
/**
 * LinkedIn card block: the card the homepage's featured resources section prints after it
 * (template-parts/components/linkedin-card.php).
 *
 * @package AI_Awareness_Day
 *
 * @var array $attributes Block attributes.
 */

// This block prints the AI Awareness Day theme's markup with the theme's own functions; under another theme it
// prints nothing rather than stopping the page with a fatal error.
foreach ( array( 'aiad_get_text_alignment_class' ) as $aiad_theme_fn ) {
	if ( ! function_exists( $aiad_theme_fn ) ) {
		return;
	}
}

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

get_template_part(
	'template-parts/components/linkedin-card',
	null,
	array(
		'url'                  => (string) ( $attributes['url'] ?? '' ),
		'text_alignment_class' => aiad_get_text_alignment_class(),
	)
);
