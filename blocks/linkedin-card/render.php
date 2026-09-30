<?php
/**
 * LinkedIn card block: the card the homepage's featured resources section prints after it
 * (template-parts/components/linkedin-card.php).
 *
 * @package AI_Awareness_Day
 *
 * @var array $attributes Block attributes.
 */

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
