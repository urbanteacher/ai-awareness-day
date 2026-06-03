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
				'Thank you for taking part in AI Awareness Day 2026. We are deeply grateful for your participation, and for the support and backing you have given our campaign.',
				'ai-awareness-day'
			),
			__(
				'The idea behind the day has always been simple: to build AI literacy across UK schools by asking every school to commit to just one activity — one conversation, one lesson, one assembly. This year, we rallied around a single, powerful message: Know it. Question it. Use it wisely.',
				'ai-awareness-day'
			),
			__(
				'Your involvement strengthens our nationwide work to explore artificial intelligence in education and helps build a future where every learner and educator feels confident with AI. Because of your support, that message reached classrooms across the country — and together, we are helping a generation of young people grow up curious, confident and thoughtful about the technology shaping their world.',
				'ai-awareness-day'
			),
			__(
				'We would love to see how you took part. Please do share any photos from your activities, or tag us in your posts using #AiAwarenessDay26 — we would be delighted to celebrate your work alongside others across the UK.',
				'ai-awareness-day'
			),
		),
		'signoff'    => array(
			__( 'With heartfelt thanks and humility,', 'ai-awareness-day' ),
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
