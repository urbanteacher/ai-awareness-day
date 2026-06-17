<?php
/**
 * Benchmark certificate copy — role-tailored headline and body text.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Certificate preview and allocation copy per stakeholder role.
 */
class AIRB_Certificate_Copy {

	/**
	 * Normalise role slugs used in dashboards vs stored submissions.
	 */
	public static function normalize_role( string $role ): string {
		$role = sanitize_key( $role );
		if ( 'support_staff' === $role ) {
			return 'support';
		}
		return $role;
	}

	/**
	 * Copy for one role.
	 *
	 * @return array<string, string>
	 */
	public static function for_role( string $role ): array {
		$role  = self::normalize_role( $role );
		$all   = self::definitions();
		$copy  = $all[ $role ] ?? $all['teacher'];
		$headline = self::headline_lines();

		return array(
			'headline_primary'      => $headline['headline_primary'],
			'headline_secondary'    => $headline['headline_secondary'],
			'body'                  => $copy['body'],
			'name_placeholder'      => $copy['name_placeholder'],
			'evidence_action_label' => $copy['evidence_action_label'],
			'evidence_change_label' => $copy['evidence_change_label'],
			'evidence_link_label'   => $copy['evidence_link_label'],
			'evidence_action_placeholder' => $copy['evidence_action_placeholder'],
			'evidence_change_placeholder' => $copy['evidence_change_placeholder'],
			'evidence_link_placeholder'   => $copy['evidence_link_placeholder'],
		);
	}

	/**
	 * All role copy for front-end localisation.
	 *
	 * @return array<string, array{headline_primary: string, body: string, name_placeholder: string}>
	 */
	public static function all_for_front(): array {
		$headline = self::headline_lines();
		$out      = array();

		foreach ( self::definitions() as $role => $copy ) {
			$out[ $role ] = array(
				'headline_primary'            => $headline['headline_primary'],
				'headline_secondary'          => $headline['headline_secondary'],
				'body'                        => $copy['body'],
				'name_placeholder'            => $copy['name_placeholder'],
				'evidence_action_label'       => $copy['evidence_action_label'],
				'evidence_change_label'       => $copy['evidence_change_label'],
				'evidence_link_label'         => $copy['evidence_link_label'],
				'evidence_action_placeholder' => $copy['evidence_action_placeholder'],
				'evidence_change_placeholder' => $copy['evidence_change_placeholder'],
				'evidence_link_placeholder'   => $copy['evidence_link_placeholder'],
			);
		}

		if ( isset( $out['support'] ) ) {
			$out['support_staff'] = $out['support'];
		}

		return $out;
	}

	/**
	 * Certificate title split across two lines for compact preview layouts.
	 *
	 * @return array{headline_primary: string, headline_secondary: string}
	 */
	private static function headline_lines(): array {
		return array(
			'headline_primary'   => __( 'AI Risk & Readiness Benchmark™', 'ai-risk-benchmark' ),
			'headline_secondary' => __( 'Certificate', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * Role-specific certificate body lines (after participant name).
	 *
	 * @return array<string, array{body: string, name_placeholder: string}>
	 */
	private static function definitions(): array {
		return array(
			'teacher' => array(
				'body'                        => __( 'has completed the AI Risk & Readiness Benchmark™ and submitted evidence of a responsible classroom AI action linked to AI Awareness Day.', 'ai-risk-benchmark' ),
				'name_placeholder'            => __( 'Example: Alex Teacher', 'ai-risk-benchmark' ),
				'evidence_action_label'       => __( 'What did you do with learners?', 'ai-risk-benchmark' ),
				'evidence_change_label'       => __( 'What changed in your classroom practice, lesson, or checking habit?', 'ai-risk-benchmark' ),
				'evidence_link_label'         => __( 'Optional evidence link (lesson slide, pupil activity, CPD note, etc.)', 'ai-risk-benchmark' ),
				'evidence_action_placeholder' => __( 'Example: I modelled checking an AI summary with my Year 9 class before they used it in a research task.', 'ai-risk-benchmark' ),
				'evidence_change_placeholder' => __( 'Example: Pupils now explain the source they would verify before trusting an AI answer.', 'ai-risk-benchmark' ),
				'evidence_link_placeholder'   => __( 'https://…', 'ai-risk-benchmark' ),
			),
			'student' => array(
				'body'                        => __( 'has completed the AI Risk & Readiness Benchmark™ and submitted evidence of a responsible learning action linked to AI Awareness Day.', 'ai-risk-benchmark' ),
				'name_placeholder'            => __( 'Example: Alex Student', 'ai-risk-benchmark' ),
				'evidence_action_label'       => __( 'What did you do in your learning?', 'ai-risk-benchmark' ),
				'evidence_change_label'       => __( 'What changed in how you study, verify answers, or use AI for school work?', 'ai-risk-benchmark' ),
				'evidence_link_label'         => __( 'Optional evidence link (study notes, draft work, reflection, etc.)', 'ai-risk-benchmark' ),
				'evidence_action_placeholder' => __( 'Example: I tried the task myself first, then used AI only to check one step and wrote what I changed.', 'ai-risk-benchmark' ),
				'evidence_change_placeholder' => __( 'Example: I now check AI answers against my textbook before handing work in.', 'ai-risk-benchmark' ),
				'evidence_link_placeholder'   => __( 'https://…', 'ai-risk-benchmark' ),
			),
			'parent'  => array(
				'body'                        => __( 'has completed the AI Risk & Readiness Benchmark™ and submitted evidence of a responsible home support action linked to AI Awareness Day.', 'ai-risk-benchmark' ),
				'name_placeholder'            => __( 'Example: Alex Parent', 'ai-risk-benchmark' ),
				'evidence_action_label'       => __( 'What did you do to support your child\'s AI use at home?', 'ai-risk-benchmark' ),
				'evidence_change_label'       => __( 'What changed in your conversations, routines, or understanding at home?', 'ai-risk-benchmark' ),
				'evidence_link_label'         => __( 'Optional evidence link (conversation guide, homework note, family agreement, etc.)', 'ai-risk-benchmark' ),
				'evidence_action_placeholder' => __( 'Example: I asked my child to explain their homework answer in their own words before they used AI again.', 'ai-risk-benchmark' ),
				'evidence_change_placeholder' => __( 'Example: We now agree when AI is allowed for homework and when they must try first.', 'ai-risk-benchmark' ),
				'evidence_link_placeholder'   => __( 'https://…', 'ai-risk-benchmark' ),
			),
			'leader'  => array(
				'body'                        => __( 'has completed the AI Risk & Readiness Benchmark™ and submitted evidence of a responsible AI governance action linked to AI Awareness Day.', 'ai-risk-benchmark' ),
				'name_placeholder'            => __( 'Example: Alex Leader', 'ai-risk-benchmark' ),
				'evidence_action_label'       => __( 'What leadership or governance action did you take?', 'ai-risk-benchmark' ),
				'evidence_change_label'       => __( 'What changed in policy, staff practice, safeguarding, or oversight?', 'ai-risk-benchmark' ),
				'evidence_link_label'         => __( 'Optional evidence link (policy note, SLT paper, staff briefing, action plan, etc.)', 'ai-risk-benchmark' ),
				'evidence_action_placeholder' => __( 'Example: I led a staff briefing on verifying AI outputs before use with pupils.', 'ai-risk-benchmark' ),
				'evidence_change_placeholder' => __( 'Example: Faculty leads now record one AI oversight check in their meeting notes.', 'ai-risk-benchmark' ),
				'evidence_link_placeholder'   => __( 'https://…', 'ai-risk-benchmark' ),
			),
			'support' => array(
				'body'                        => __( 'has completed the AI Risk & Readiness Benchmark™ and submitted evidence of a responsible operational AI action linked to AI Awareness Day.', 'ai-risk-benchmark' ),
				'name_placeholder'            => __( 'Example: Alex Support Staff', 'ai-risk-benchmark' ),
				'evidence_action_label'       => __( 'What operational action did you take?', 'ai-risk-benchmark' ),
				'evidence_change_label'       => __( 'What changed in office workflow, data handling, or staff guidance?', 'ai-risk-benchmark' ),
				'evidence_link_label'         => __( 'Optional evidence link (checklist, process note, data guidance, CPD record, etc.)', 'ai-risk-benchmark' ),
				'evidence_action_placeholder' => __( 'Example: I updated our office AI checklist so pupil data is never pasted into public tools.', 'ai-risk-benchmark' ),
				'evidence_change_placeholder' => __( 'Example: Colleagues now ask whether AI use is approved before drafting parent letters.', 'ai-risk-benchmark' ),
				'evidence_link_placeholder'   => __( 'https://…', 'ai-risk-benchmark' ),
			),
			'public'  => array(
				'body'                        => __( 'has completed the AI Risk & Readiness Benchmark™ and submitted evidence of a responsible personal AI action linked to AI Awareness Day.', 'ai-risk-benchmark' ),
				'name_placeholder'            => __( 'Example: Alex Participant', 'ai-risk-benchmark' ),
				'evidence_action_label'       => __( 'What personal AI action did you take?', 'ai-risk-benchmark' ),
				'evidence_change_label'       => __( 'What changed in how you verify, protect privacy, or use AI safely?', 'ai-risk-benchmark' ),
				'evidence_link_label'         => __( 'Optional evidence link (notes, screenshot, privacy checklist, etc.)', 'ai-risk-benchmark' ),
				'evidence_action_placeholder' => __( 'Example: I removed personal details from a prompt and checked the answer against a trusted source.', 'ai-risk-benchmark' ),
				'evidence_change_placeholder' => __( 'Example: I now verify AI answers before sharing them with family or colleagues.', 'ai-risk-benchmark' ),
				'evidence_link_placeholder'   => __( 'https://…', 'ai-risk-benchmark' ),
			),
		);
	}
}
