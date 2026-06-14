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
	 * Alternate phrasing for repeat audits — same question id and scoring.
	 *
	 * @return array<string, array<int, string>>
	 */
	private static function text_variants(): array {
		return array(
			// Parent
			'p_child_uses'  => array(
				__( 'Are AI apps or chatbots part of your child\'s homework or free time?', 'ai-risk-benchmark' ),
				__( 'Has your child used tools like ChatGPT or similar at home?', 'ai-risk-benchmark' ),
			),
			'p_know_tools'  => array(
				__( 'Could you name the AI tools your child uses most often?', 'ai-risk-benchmark' ),
				__( 'How clearly do you know which AI apps your child has access to?', 'ai-risk-benchmark' ),
			),
			'p_discuss_use' => array(
				__( 'How often do you talk with your child about using AI responsibly?', 'ai-risk-benchmark' ),
				__( 'Have you had a conversation about when AI is — and is not — appropriate?', 'ai-risk-benchmark' ),
			),
			'p_cheating'    => array(
				__( 'Do you know how AI can affect homework honesty and academic integrity?', 'ai-risk-benchmark' ),
				__( 'Are you confident about where AI help crosses the line into cheating?', 'ai-risk-benchmark' ),
			),
			'p_spot_ai_hw'  => array(
				__( 'Would you notice if homework looked AI-generated rather than your child\'s own work?', 'ai-risk-benchmark' ),
				__( 'How confident are you spotting work that may have come from an AI tool?', 'ai-risk-benchmark' ),
			),
			'p_no_share'    => array(
				__( 'Do you know what personal details your child should never enter into AI tools?', 'ai-risk-benchmark' ),
				__( 'Are you clear on which information is unsafe to share with public AI apps?', 'ai-risk-benchmark' ),
			),
			'p_deepfakes'   => array(
				__( 'Have you talked with your child about deepfakes, scams and AI-enabled online harm?', 'ai-risk-benchmark' ),
				__( 'Does your family discuss fake images, voice clones and other AI safety risks?', 'ai-risk-benchmark' ),
			),
			'p_equipped'    => array(
				__( 'Do you feel confident supporting your child to use AI safely at home?', 'ai-risk-benchmark' ),
				__( 'How prepared do you feel to advise your child on responsible AI use?', 'ai-risk-benchmark' ),
			),
			// Teacher
			't_verify'      => array(
				__( 'Before you use AI output with pupils, do you check it is accurate and appropriate?', 'ai-risk-benchmark' ),
				__( 'Do you review AI-generated content before sharing it in class?', 'ai-risk-benchmark' ),
			),
			't_cross_ref'   => array(
				__( 'How often do you check AI answers against trusted sources or your own knowledge?', 'ai-risk-benchmark' ),
				__( 'Do you compare AI suggestions with textbooks, schemes of work or colleagues?', 'ai-risk-benchmark' ),
			),
			't_ai_before_task' => array(
				__( 'How often do you reach for AI before trying the task yourself?', 'ai-risk-benchmark' ),
				__( 'Do you typically use AI first, or only after your own initial attempt?', 'ai-risk-benchmark' ),
			),
			't_pupil_data'  => array(
				__( 'Have you ever typed pupil names, marks or other student information into a public AI tool?', 'ai-risk-benchmark' ),
				__( 'Have identifiable pupil details ever been entered into AI tools you use?', 'ai-risk-benchmark' ),
			),
			't_hallucinations' => array(
				__( 'Do you understand that AI can invent facts and how to explain that to pupils?', 'ai-risk-benchmark' ),
				__( 'Are you confident teaching pupils that AI outputs can be wrong or made up?', 'ai-risk-benchmark' ),
			),
			't_when_not'    => array(
				__( 'Can you identify tasks where AI should not be used in your classroom?', 'ai-risk-benchmark' ),
				__( 'Are you clear on situations where using AI would be inappropriate for pupils?', 'ai-risk-benchmark' ),
			),
			't_feedback_ai' => array(
				__( 'How often do you draft pupil feedback using AI before personalising it?', 'ai-risk-benchmark' ),
				__( 'Do you rely on AI to write or rewrite comments on pupils\' work?', 'ai-risk-benchmark' ),
			),
			't_modify_pct'  => array(
				__( 'Roughly how much AI-generated material do you edit before using it with pupils?', 'ai-risk-benchmark' ),
				__( 'What share of AI output do you change before it reaches pupils or colleagues?', 'ai-risk-benchmark' ),
			),
			't_challenge'   => array(
				__( 'When AI advice looks wrong, do you push back and correct it?', 'ai-risk-benchmark' ),
				__( 'Do you question AI recommendations that do not seem right?', 'ai-risk-benchmark' ),
			),
			't_without_ai'  => array(
				__( 'Could you manage a normal week of teaching without leaning on AI tools?', 'ai-risk-benchmark' ),
				__( 'If AI were unavailable for a week, could you still teach effectively?', 'ai-risk-benchmark' ),
			),
			't_send_data'   => array(
				__( 'Have SEND or other sensitive pupil details ever been entered into an AI tool?', 'ai-risk-benchmark' ),
				__( 'Has confidential pupil information about SEND or vulnerability been shared with AI?', 'ai-risk-benchmark' ),
			),
			't_data_risks'  => array(
				__( 'How well do you understand data-protection risks when using AI at school?', 'ai-risk-benchmark' ),
				__( 'Are you clear on personal-data risks when using AI tools professionally?', 'ai-risk-benchmark' ),
			),
			't_safe_adoption' => array(
				__( 'Before adopting a new AI tool with pupils, do you weigh benefits against risks?', 'ai-risk-benchmark' ),
				__( 'Do you assess pros and cons before introducing a new AI tool to pupils?', 'ai-risk-benchmark' ),
			),
			// Student
			's_attempt_first' => array(
				__( 'Do you try the work yourself before asking AI for help?', 'ai-risk-benchmark' ),
				__( 'Do you make an attempt on your own before turning to AI?', 'ai-risk-benchmark' ),
			),
			's_without_ai'  => array(
				__( 'Could you finish this kind of assignment without using AI?', 'ai-risk-benchmark' ),
				__( 'If AI were not available, could you still complete the work?', 'ai-risk-benchmark' ),
			),
			's_submitted_ai' => array(
				__( 'Have you ever handed in AI-written work as if it were your own?', 'ai-risk-benchmark' ),
				__( 'Have you submitted AI-generated answers without saying so?', 'ai-risk-benchmark' ),
			),
			's_verify'      => array(
				__( 'Do you check AI answers before submitting work?', 'ai-risk-benchmark' ),
				__( 'Do you review what AI gives you before you hand it in?', 'ai-risk-benchmark' ),
			),
			's_textbooks'   => array(
				__( 'Do you double-check AI answers against books, notes or other sources?', 'ai-risk-benchmark' ),
				__( 'Do you compare AI answers with textbooks or class materials?', 'ai-risk-benchmark' ),
			),
			's_spot_mistakes' => array(
				__( 'Have you noticed when AI gives wrong or misleading answers?', 'ai-risk-benchmark' ),
				__( 'Can you spot errors or mistakes in AI responses?', 'ai-risk-benchmark' ),
			),
			's_how_ai_works' => array(
				__( 'Do you understand what AI can and cannot do reliably?', 'ai-risk-benchmark' ),
				__( 'Do you know the main limits of how AI tools work?', 'ai-risk-benchmark' ),
			),
			's_wrong'       => array(
				__( 'Do you know AI can be wrong — and do you act on that?', 'ai-risk-benchmark' ),
				__( 'When AI might be incorrect, do you verify before trusting it?', 'ai-risk-benchmark' ),
			),
			's_personal_info' => array(
				__( 'Have you shared private details (name, school, photos) with AI tools?', 'ai-risk-benchmark' ),
				__( 'Have you ever typed personal information into a public AI app?', 'ai-risk-benchmark' ),
			),
			's_privacy_risks' => array(
				__( 'Do you understand why sharing personal data with AI can be risky?', 'ai-risk-benchmark' ),
				__( 'Are you aware of privacy risks when using AI tools?', 'ai-risk-benchmark' ),
			),
			// Leader
			'l_policy'      => array(
				__( 'Does your school have a clear, published policy on AI use?', 'ai-risk-benchmark' ),
				__( 'Is AI use covered in a formal policy that staff can access?', 'ai-risk-benchmark' ),
			),
			'l_ai_lead'     => array(
				__( 'Is someone clearly responsible for AI oversight in your school?', 'ai-risk-benchmark' ),
				__( 'Has the school appointed a lead or owner for AI governance?', 'ai-risk-benchmark' ),
			),
			'l_annual_review' => array(
				__( 'Does leadership review AI use and risks on a regular basis?', 'ai-risk-benchmark' ),
				__( 'Is AI governance reviewed by senior leaders at least annually?', 'ai-risk-benchmark' ),
			),
			'l_safeguarding' => array(
				__( 'Are AI-related safeguarding risks reflected in school procedures?', 'ai-risk-benchmark' ),
				__( 'Do safeguarding policies explicitly cover AI-related harm?', 'ai-risk-benchmark' ),
			),
			'l_deepfakes'   => array(
				__( 'Do safeguarding procedures cover deepfakes and AI-enabled abuse?', 'ai-risk-benchmark' ),
				__( 'Are deepfake and AI manipulation risks addressed in safeguarding?', 'ai-risk-benchmark' ),
			),
			'l_dp_review'   => array(
				__( 'Have you completed privacy impact assessments for pupil-facing AI tools?', 'ai-risk-benchmark' ),
				__( 'Are DPIAs in place where pupils use AI systems?', 'ai-risk-benchmark' ),
			),
			'l_approved_tools' => array(
				__( 'Is there a clear list of AI tools staff and pupils may use?', 'ai-risk-benchmark' ),
				__( 'Are approved AI tools communicated to staff and students?', 'ai-risk-benchmark' ),
			),
			'l_staff_training' => array(
				__( 'Have staff received training on AI risks and how to verify outputs?', 'ai-risk-benchmark' ),
				__( 'Is AI risk awareness part of staff CPD in your school?', 'ai-risk-benchmark' ),
			),
			'l_incidents'   => array(
				__( 'Are AI-related incidents logged and reviewed by leadership?', 'ai-risk-benchmark' ),
				__( 'Does the school track and learn from AI-related incidents?', 'ai-risk-benchmark' ),
			),
			'l_assessment_review' => array(
				__( 'Are assessments checked for vulnerability to AI-assisted cheating?', 'ai-risk-benchmark' ),
				__( 'Do you review how AI could affect the integrity of assessments?', 'ai-risk-benchmark' ),
			),
			'l_jcq'         => array(
				__( 'Is JCQ guidance on AI understood by staff who need it?', 'ai-risk-benchmark' ),
				__( 'Do relevant teams understand JCQ rules on AI in assessments?', 'ai-risk-benchmark' ),
			),
			'l_safe_adoption' => array(
				__( 'Are new AI tools formally assessed before the school adopts them?', 'ai-risk-benchmark' ),
				__( 'Is there a structured check before rolling out new AI tools?', 'ai-risk-benchmark' ),
			),
			'l_literacy'    => array(
				__( 'Is AI literacy taught or discussed through curriculum or tutor time?', 'ai-risk-benchmark' ),
				__( 'Do pupils learn about responsible AI use as part of school provision?', 'ai-risk-benchmark' ),
			),
		);
	}

	/**
	 * Attach alternate phrasing to questions that define variants.
	 *
	 * @param array<int, array<string, mixed>> $questions Question rows.
	 * @return array<int, array<string, mixed>>
	 */
	private static function attach_text_variants( array $questions ): array {
		$variants = self::text_variants();
		foreach ( $questions as &$question ) {
			$id = (string) ( $question['id'] ?? '' );
			if ( $id && isset( $variants[ $id ] ) ) {
				$question['text_variants'] = $variants[ $id ];
			}
		}
		unset( $question );
		return $questions;
	}

	/**
	 * Complete question set.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function all(): array {
		$f = self::freq();
		return self::attach_text_variants(
			array_merge(
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
		)
		);
	}
}
