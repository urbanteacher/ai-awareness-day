<?php
/**
 * Thank-you letter copy for participants (admin PDF generator).
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Letter body paragraphs (name is shown in the salutation only).
 *
 * @return array{title:string,paragraphs:array<int,string>,signoff:array<int,string>}
 */
function aiad_get_thank_you_letter_copy(): array {
	return array(
		'title'      => __( 'AI Awareness Day 2026', 'ai-awareness-day' ),
		'paragraphs' => array(
			__(
				'Thank you for taking part in AI Awareness Day 2026.',
				'ai-awareness-day'
			),
			__(
				'We are deeply grateful for your participation and for the support and backing you have given our campaign. Your involvement strengthens our nationwide work to explore artificial intelligence in education and helps build a future where every learner and educator feels confident with AI.',
				'ai-awareness-day'
			),
			__(
				'Thank you again for standing with AI Awareness Day.',
				'ai-awareness-day'
			),
		),
		'signoff'    => array(
			__( 'With warm thanks,', 'ai-awareness-day' ),
			__( 'The AI Awareness Day team', 'ai-awareness-day' ),
		),
	);
}

/**
 * REST-friendly thank-you letter copy.
 *
 * @return array<string, mixed>
 */
function aiad_thank_you_letter_copy_for_rest(): array {
	$copy               = aiad_get_thank_you_letter_copy();
	$copy['paragraphs'] = array_values( $copy['paragraphs'] );
	$copy['signoff']    = array_values( $copy['signoff'] );
	return $copy;
}
