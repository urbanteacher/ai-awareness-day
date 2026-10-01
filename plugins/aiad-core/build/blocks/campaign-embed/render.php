<?php
/**
 * Campaign video block: the embed the homepage's campaign section prints (template-parts/components/campaign-embed.php).
 * Without an address it prints nothing, and the section lays its text out full width.
 *
 * @var array $attributes Block attributes.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

get_template_part( 'template-parts/components/campaign-embed', null, array( 'src' => (string) ( $attributes['url'] ?? '' ) ) );
