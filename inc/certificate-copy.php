<?php
/**
 * Certificate wording by signup role (teacher, school leader, organisation, parent).
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether an organisation type is treated as industry / tech partner wording.
 *
 * @param string $org_type Organisation type slug from signup.
 */
function aiad_certificate_is_tech_organisation( string $org_type ): bool {
	return in_array( $org_type, array( 'company', 'education_provider' ), true );
}

/**
 * Whole-school certificate body (school name only — no individual line).
 *
 * @param string $involved_as teacher|school_leader|organisation|parent|''.
 * @param string $org_type    Organisation type slug when involved_as is organisation.
 */
function aiad_get_certificate_school_body( string $involved_as, string $org_type = '' ): string {
	$default = __(
		'has actively contributed to AI Awareness Day 2026 through participation in our nationwide exploration of artificial intelligence in education, helping build a future where every learner and educator feels confident with AI.',
		'ai-awareness-day'
	);

	switch ( $involved_as ) {
		case 'teacher':
		case 'school_leader':
			return __(
				'has actively contributed to AI Awareness Day 2026 through the school\'s participation in our nationwide exploration of artificial intelligence in education, helping build a future where every learner and educator feels confident with AI.',
				'ai-awareness-day'
			);

		case 'organisation':
			if ( aiad_certificate_is_tech_organisation( $org_type ) ) {
				return __(
					'has actively contributed to AI Awareness Day 2026 through the organisation\'s participation in our nationwide exploration of artificial intelligence in education, helping build a future where every learner and educator feels confident with AI.',
					'ai-awareness-day'
				);
			}

			return __(
				'has actively contributed to AI Awareness Day 2026 through the organisation\'s support of our nationwide exploration of artificial intelligence in education, helping build a future where every learner and educator feels confident with AI.',
				'ai-awareness-day'
			);

		case 'parent':
			return __(
				'has actively contributed to AI Awareness Day 2026 as part of our nationwide exploration of artificial intelligence in education, supporting confident and responsible AI use for learners and families.',
				'ai-awareness-day'
			);

		default:
			return $default;
	}
}

/**
 * Certificate copy blocks for a participant role.
 *
 * @param string $involved_as teacher|school_leader|organisation|parent|''.
 * @param string $org_type    Organisation type slug when involved_as is organisation.
 * @return array{headline_primary:string,eyebrow:string,affiliation_prefix:string,body:string,school_body:string}
 */
function aiad_get_certificate_copy( string $involved_as, string $org_type = '' ): array {
	$default_body = __(
		'has actively contributed to AI Awareness Day 2026 through their engagement in our nationwide exploration of artificial intelligence in education, helping build a future where every learner and educator feels confident with AI.',
		'ai-awareness-day'
	);

	$base = array(
		'headline_primary'   => __( 'AI Awareness Day 2026', 'ai-awareness-day' ),
		'eyebrow'            => __( 'Certificate of participation', 'ai-awareness-day' ),
		'affiliation_prefix' => __( 'from', 'ai-awareness-day' ),
		'body'               => $default_body,
	);

	switch ( $involved_as ) {
		case 'teacher':
			$copy = array_merge(
				$base,
				array(
					'body' => __(
						'has actively contributed to AI Awareness Day 2026 through their work with learners and participation in our nationwide exploration of artificial intelligence in education, helping build a future where every learner and educator feels confident with AI.',
						'ai-awareness-day'
					),
				)
			);
			break;

		case 'school_leader':
			$copy = array_merge(
				$base,
				array(
					'body' => __(
						'has actively contributed to AI Awareness Day 2026 through their leadership and their school\'s participation in our nationwide exploration of artificial intelligence in education, helping build a future where every learner and educator feels confident with AI.',
						'ai-awareness-day'
					),
				)
			);
			break;

		case 'organisation':
			if ( aiad_certificate_is_tech_organisation( $org_type ) ) {
				$copy = array_merge(
					$base,
					array(
						'body' => __(
							'has actively contributed to AI Awareness Day 2026 through their organisation\'s engagement in our nationwide exploration of artificial intelligence in education, helping build a future where every learner and educator feels confident with AI.',
							'ai-awareness-day'
						),
					)
				);
				break;
			}

			$copy = array_merge(
				$base,
				array(
					'body' => __(
						'has actively contributed to AI Awareness Day 2026 through their organisation\'s support of our nationwide exploration of artificial intelligence in education, helping build a future where every learner and educator feels confident with AI.',
						'ai-awareness-day'
					),
				)
			);
			break;

		case 'parent':
			$copy = array_merge(
				$base,
				array(
					'affiliation_prefix' => __( 'in support of', 'ai-awareness-day' ),
					'body'               => __(
						'has actively contributed to AI Awareness Day 2026 as a parent or carer supporting their child\'s learning, as part of our nationwide exploration of artificial intelligence in education.',
						'ai-awareness-day'
					),
				)
			);
			break;

		default:
			$copy = $base;
			break;
	}

	if ( ! isset( $copy ) ) {
		$copy = $base;
	}

	$copy['school_body'] = aiad_get_certificate_school_body( $involved_as, $org_type );

	return $copy;
}

/**
 * REST-safe certificate copy.
 *
 * @param string $involved_as Participant role slug.
 * @param string $org_type    Organisation type slug.
 * @return array<string, mixed>
 */
function aiad_certificate_copy_for_rest( string $involved_as, string $org_type = '' ): array {
	$copy                      = aiad_get_certificate_copy( $involved_as, $org_type );
	$copy['paragraphs']        = array( $copy['body'] );
	$copy['affiliationPrefix'] = $copy['affiliation_prefix'];
	$copy['headlinePrimary']   = $copy['headline_primary'];
	$copy['headline']          = $copy['headline_primary'];
	$copy['schoolBody']        = $copy['school_body'];
	return $copy;
}
