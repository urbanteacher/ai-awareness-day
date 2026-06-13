<?php
/**
 * Full DfE-aligned question bank — grouped by audit section per role.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Framework questions factory.
 */
class AIRB_Questions {

	/**
	 * Standard frequency options.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private static function freq(): array {
		return array(
			array( 'value' => 'always', 'label' => __( 'Always', 'ai-risk-benchmark' ), 'score' => 0 ),
			array( 'value' => 'often', 'label' => __( 'Often', 'ai-risk-benchmark' ), 'score' => 1 ),
			array( 'value' => 'sometimes', 'label' => __( 'Sometimes', 'ai-risk-benchmark' ), 'score' => 2 ),
			array( 'value' => 'rarely', 'label' => __( 'Rarely or never', 'ai-risk-benchmark' ), 'score' => 3 ),
		);
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	private static function q( string $id, string $role, string $domain, string $section, string $text, array $options = array(), string $type = 'radio' ): array {
		return array(
			'id'      => $id,
			'role'    => $role,
			'domain'  => $domain,
			'section' => $section,
			'type'    => $type,
			'text'    => $text,
			'options' => $options,
		);
	}

	/**
	 * Complete question set.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function all(): array {
		$f = self::freq();
		return array_merge(
			// —— Teacher ——
			array(
				self::q( 't_modify_pct', 'teacher', 'human_oversight', __( 'Human Oversight', 'ai-risk-benchmark' ), __( 'What percentage of AI-generated content do you modify before using it?', 'ai-risk-benchmark' ), array(), 'slider' ),
				self::q( 't_verify', 'teacher', 'human_oversight', __( 'Human Oversight', 'ai-risk-benchmark' ), __( 'Do you verify AI outputs before using them with pupils or colleagues?', 'ai-risk-benchmark' ), $f ),
				self::q( 't_cross_ref', 'teacher', 'human_oversight', __( 'Human Oversight', 'ai-risk-benchmark' ), __( 'How often do you cross-reference AI outputs with other sources?', 'ai-risk-benchmark' ), $f ),
				self::q( 't_challenge', 'teacher', 'human_oversight', __( 'Human Oversight', 'ai-risk-benchmark' ), __( 'Do you challenge AI recommendations when they seem incorrect?', 'ai-risk-benchmark' ), $f ),
				self::q( 't_ai_before_task', 'teacher', 'ai_dependency', __( 'Dependency', 'ai-risk-benchmark' ), __( 'How often do you use AI before attempting the task yourself?', 'ai-risk-benchmark' ), $f ),
				self::q( 't_without_ai', 'teacher', 'ai_dependency', __( 'Dependency', 'ai-risk-benchmark' ), __( 'Could you teach effectively without AI for one week?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes_easily', 'label' => __( 'Yes, easily', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'yes_some', 'label' => __( 'Yes, with effort', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'difficult', 'label' => __( 'Difficult', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'Not realistically', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 't_feedback_ai', 'teacher', 'ai_dependency', __( 'Dependency', 'ai-risk-benchmark' ), __( 'How often do you use AI to write pupil feedback?', 'ai-risk-benchmark' ), $f ),
				self::q( 't_pupil_data', 'teacher', 'privacy', __( 'Privacy', 'ai-risk-benchmark' ), __( 'Have you entered student information into AI tools?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'never', 'label' => __( 'Never', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'unsure', 'label' => __( 'Not sure / might have', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'yes_anon', 'label' => __( 'Yes, but anonymised only', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'yes', 'label' => __( 'Yes, identifiable data', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 't_send_data', 'teacher', 'privacy', __( 'Privacy', 'ai-risk-benchmark' ), __( 'Have you entered SEND or sensitive pupil information into AI?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'never', 'label' => __( 'Never', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'unsure', 'label' => __( 'Not sure', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'yes', 'label' => __( 'Yes', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 't_data_risks', 'teacher', 'privacy', __( 'Privacy', 'ai-risk-benchmark' ), __( 'Do you understand personal data risks when using AI?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes, clearly', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'mostly', 'label' => __( 'Mostly', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'basic', 'label' => __( 'Basic awareness', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'Limited', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 't_hallucinations', 'teacher', 'ai_literacy', __( 'Confidence & Competence', 'ai-risk-benchmark' ), __( 'Do you understand AI hallucinations and limitations?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'confident', 'label' => __( 'Yes, and I teach pupils about them', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'aware', 'label' => __( 'Yes, generally aware', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'basic', 'label' => __( 'Basic awareness only', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'limited', 'label' => __( 'Limited understanding', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 't_when_not', 'teacher', 'ai_literacy', __( 'Confidence & Competence', 'ai-risk-benchmark' ), __( 'Do you know when AI should not be used?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes, confident', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'mostly', 'label' => __( 'Mostly', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'unsure', 'label' => __( 'Sometimes unsure', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 't_safe_adoption', 'teacher', 'safe_adoption', __( 'Confidence & Competence', 'ai-risk-benchmark' ), __( 'Before using a new AI tool with pupils, do you assess benefits vs risks?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'always', 'label' => __( 'Always, with a clear decision', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'usually', 'label' => __( 'Usually', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'sometimes', 'label' => __( 'Sometimes', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No formal check', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
			),
			// —— Student ——
			array(
				self::q( 's_attempt_first', 'student', 'ai_dependency', __( 'Dependency', 'ai-risk-benchmark' ), __( 'Do you attempt work before using AI?', 'ai-risk-benchmark' ), $f ),
				self::q( 's_without_ai', 'student', 'ai_dependency', __( 'Dependency', 'ai-risk-benchmark' ), __( 'Could you complete the assignment without AI?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes, confidently', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'mostly', 'label' => __( 'Mostly', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'struggle', 'label' => __( 'I would struggle', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 's_submitted_ai', 'student', 'assessment_integrity', __( 'Dependency', 'ai-risk-benchmark' ), __( 'Have you submitted AI-generated work as your own?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'never', 'label' => __( 'Never', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'once', 'label' => __( 'Once or twice', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'sometimes', 'label' => __( 'Sometimes', 'ai-risk-benchmark' ), 'score' => 3 ),
					array( 'value' => 'often', 'label' => __( 'Often', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 's_verify', 'student', 'human_oversight', __( 'Verification', 'ai-risk-benchmark' ), __( 'Do you check AI answers before handing work in?', 'ai-risk-benchmark' ), $f ),
				self::q( 's_textbooks', 'student', 'human_oversight', __( 'Verification', 'ai-risk-benchmark' ), __( 'Do you compare AI answers with textbooks or other sources?', 'ai-risk-benchmark' ), $f ),
				self::q( 's_spot_mistakes', 'student', 'human_oversight', __( 'Verification', 'ai-risk-benchmark' ), __( 'Have you identified mistakes in AI answers?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes_often', 'label' => __( 'Yes, often', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'sometimes', 'label' => __( 'Sometimes', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'rarely', 'label' => __( 'Rarely', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'never', 'label' => __( 'Never / not sure', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 's_how_ai_works', 'student', 'ai_literacy', __( 'Critical Thinking', 'ai-risk-benchmark' ), __( 'Do you understand how AI works and its limitations?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes, clearly', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'mostly', 'label' => __( 'Mostly', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'basic', 'label' => __( 'A little', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'Not really', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 's_wrong', 'student', 'ai_literacy', __( 'Critical Thinking', 'ai-risk-benchmark' ), __( 'Do you know when AI is wrong?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes, and I check outputs', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'mostly', 'label' => __( 'Mostly', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'basic', 'label' => __( 'A little', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'Not really', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 's_personal_info', 'student', 'privacy', __( 'Safety', 'ai-risk-benchmark' ), __( 'Have you shared personal information with AI tools?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'never', 'label' => __( 'Never', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'unsure', 'label' => __( 'Not sure', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'once', 'label' => __( 'Once or twice', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'yes', 'label' => __( 'Yes, regularly', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 's_privacy_risks', 'student', 'privacy', __( 'Safety', 'ai-risk-benchmark' ), __( 'Do you understand privacy risks when using AI?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'mostly', 'label' => __( 'Mostly', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'unsure', 'label' => __( 'Unsure', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
			),
			// —— Parent ——
			array(
				self::q( 'p_child_uses', 'parent', 'ai_literacy', __( 'Awareness', 'ai-risk-benchmark' ), __( 'Does your child use AI tools?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes, I know they do', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'think', 'label' => __( 'I think so', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'unsure', 'label' => __( 'Not sure', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No / unlikely', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'p_know_tools', 'parent', 'ai_literacy', __( 'Awareness', 'ai-risk-benchmark' ), __( 'Do you know which AI tools your child uses?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes, clearly', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'some', 'label' => __( 'Some of them', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'vague', 'label' => __( 'Vaguely', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'p_discuss_use', 'parent', 'safe_adoption', __( 'Oversight', 'ai-risk-benchmark' ), __( 'Have you discussed AI use with your child?', 'ai-risk-benchmark' ), $f ),
				self::q( 'p_cheating', 'parent', 'assessment_integrity', __( 'Oversight', 'ai-risk-benchmark' ), __( 'Do you understand AI-related cheating and homework integrity?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes, clearly', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'mostly', 'label' => __( 'Mostly', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'basic', 'label' => __( 'Basic awareness', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'Limited', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'p_spot_ai_hw', 'parent', 'assessment_integrity', __( 'Oversight', 'ai-risk-benchmark' ), __( 'Could you recognise AI-generated homework?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'confident', 'label' => __( 'Yes, confident', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'maybe', 'label' => __( 'Maybe sometimes', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'unsure', 'label' => __( 'Unsure', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'p_no_share', 'parent', 'privacy', __( 'Safety', 'ai-risk-benchmark' ), __( 'Do you know what information children should not share with AI?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'mostly', 'label' => __( 'Mostly', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'unsure', 'label' => __( 'Unsure', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'p_deepfakes', 'parent', 'safeguarding', __( 'Safety', 'ai-risk-benchmark' ), __( 'Have you discussed deepfake and online safety risks?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes, and we discuss them', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'aware', 'label' => __( 'Generally aware', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'basic', 'label' => __( 'Basic awareness', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'Limited', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'p_equipped', 'parent', 'ai_literacy', __( 'Confidence', 'ai-risk-benchmark' ), __( 'Do you feel equipped to guide your child on AI use?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'mostly', 'label' => __( 'Mostly', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'learning', 'label' => __( 'Still learning', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'Not yet', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
			),
			// —— School leader ——
			array(
				self::q( 'l_policy', 'leader', 'governance', __( 'Governance', 'ai-risk-benchmark' ), __( 'Is there a published AI policy?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'published', 'label' => __( 'Published & reviewed', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'draft', 'label' => __( 'In draft', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'informal', 'label' => __( 'Informal only', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'l_ai_lead', 'leader', 'governance', __( 'Governance', 'ai-risk-benchmark' ), __( 'Is there a named AI lead or owner?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'shared', 'label' => __( 'Shared across roles', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'planned', 'label' => __( 'Planned', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'l_annual_review', 'leader', 'governance', __( 'Governance', 'ai-risk-benchmark' ), __( 'Is AI use reviewed annually by leadership?', 'ai-risk-benchmark' ), $f ),
				self::q( 'l_safeguarding', 'leader', 'safeguarding', __( 'Safeguarding', 'ai-risk-benchmark' ), __( 'Are AI risks included in safeguarding procedures?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes, explicitly', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'partial', 'label' => __( 'Partially', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'review', 'label' => __( 'Under review', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'l_deepfakes', 'leader', 'safeguarding', __( 'Safeguarding', 'ai-risk-benchmark' ), __( 'Are deepfakes and AI-enabled harms covered in safeguarding procedures?', 'ai-risk-benchmark' ), $f ),
				self::q( 'l_dp_review', 'leader', 'privacy', __( 'Data Protection', 'ai-risk-benchmark' ), __( 'Has an AI-related DPIA been completed for pupil-facing AI tools?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes, with DPIAs where needed', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'started', 'label' => __( 'Started', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'planned', 'label' => __( 'Planned', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'Not yet', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'l_approved_tools', 'leader', 'privacy', __( 'Data Protection', 'ai-risk-benchmark' ), __( 'Are approved AI tools listed for staff and students?', 'ai-risk-benchmark' ), $f ),
				self::q( 'l_staff_training', 'leader', 'human_oversight', __( 'Workforce Readiness', 'ai-risk-benchmark' ), __( 'Have staff been trained on AI risks and verification?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'regular', 'label' => __( 'Yes, regular CPD', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'some', 'label' => __( 'Some staff / one-off', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'planned', 'label' => __( 'Planned', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'l_incidents', 'leader', 'governance', __( 'Workforce Readiness', 'ai-risk-benchmark' ), __( 'Are AI-related incidents tracked and reviewed?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes, systematically', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'informal', 'label' => __( 'Informally', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'planned', 'label' => __( 'Planned', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'l_assessment_review', 'leader', 'assessment_integrity', __( 'Assessment', 'ai-risk-benchmark' ), __( 'Are assessments reviewed for AI exposure?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes, systematically', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'some', 'label' => __( 'Some departments', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'ad_hoc', 'label' => __( 'Ad hoc', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'l_jcq', 'leader', 'assessment_integrity', __( 'Assessment', 'ai-risk-benchmark' ), __( 'Is JCQ guidance on AI understood across the school?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes, widely understood', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'some', 'label' => __( 'In some teams', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'planned', 'label' => __( 'Being rolled out', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'Not yet', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'l_safe_adoption', 'leader', 'safe_adoption', __( 'Governance', 'ai-risk-benchmark' ), __( 'Are new AI tools assessed before adoption (benefits vs risks)?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes, formal process', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'sometimes', 'label' => __( 'Sometimes', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'informal', 'label' => __( 'Informal only', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'l_literacy', 'leader', 'ai_literacy', __( 'Workforce Readiness', 'ai-risk-benchmark' ), __( 'Is AI literacy included in your curriculum or tutor programme?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'embedded', 'label' => __( 'Yes, embedded', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'pilot', 'label' => __( 'Pilot / partial', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'planned', 'label' => __( 'Planned', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'Not yet', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
			)
		);
	}
}
