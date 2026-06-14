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
	 * Frequency scale for risk-increasing behaviours (higher frequency = higher risk).
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private static function freq_risk(): array {
		return array(
			array( 'value' => 'rarely', 'label' => __( 'Rarely or never', 'ai-risk-benchmark' ), 'score' => 0 ),
			array( 'value' => 'sometimes', 'label' => __( 'Sometimes', 'ai-risk-benchmark' ), 'score' => 1 ),
			array( 'value' => 'often', 'label' => __( 'Often', 'ai-risk-benchmark' ), 'score' => 2 ),
			array( 'value' => 'always', 'label' => __( 'Always', 'ai-risk-benchmark' ), 'score' => 3 ),
		);
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	private static function q( string $id, string $role, string $domain, string $section, string $text, array $options = array(), string $type = 'radio', array $extra = array() ): array {
		return array_merge(
			array(
				'id'      => $id,
				'role'    => $role,
				'domain'  => $domain,
				'section' => $section,
				'type'    => $type,
				'text'    => $text,
				'options' => $options,
			),
			$extra
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
				__( 'How confident are you that you know whether your child uses AI tools?', 'ai-risk-benchmark' ),
				__( 'How sure are you about whether your child uses AI at home?', 'ai-risk-benchmark' ),
			),
			'p_know_tools'  => array(
				__( 'Could you name the AI tools your child uses most often?', 'ai-risk-benchmark' ),
				__( 'How clearly do you know which AI apps your child has access to?', 'ai-risk-benchmark' ),
			),
			'p_child_unknown_use' => array(
				__( 'How often does your child use AI without you knowing exactly what they used it for?', 'ai-risk-benchmark' ),
				__( 'How often does your child use AI in ways you cannot clearly account for?', 'ai-risk-benchmark' ),
			),
			'p_no_share'    => array(
				__( 'Do you know what personal details your child should never enter into AI tools?', 'ai-risk-benchmark' ),
				__( 'Are you clear on which information is unsafe to share with public AI apps?', 'ai-risk-benchmark' ),
			),
			'p_harm_response' => array(
				__( 'If your child received an AI-generated image, voice message or fake account pretending to be someone they know, would they know what to do?', 'ai-risk-benchmark' ),
				__( 'If your child saw a fake AI image or voice message from someone they know, would they know how to respond?', 'ai-risk-benchmark' ),
			),
			'p_home_ai_culture' => array(
				__( 'Which statement best describes AI use in your home?', 'ai-risk-benchmark' ),
				__( 'Which best describes how your family talks about AI?', 'ai-risk-benchmark' ),
			),
			'p_explain_own_words' => array(
				__( 'When your child uses AI for homework, how often do you ask them to explain the answer in their own words?', 'ai-risk-benchmark' ),
				__( 'After your child uses AI for homework, how often do you ask them to explain their answer without the tool?', 'ai-risk-benchmark' ),
			),
			'p_check_suspicion' => array(
				__( 'If you suspected your child had used AI for homework, how would you check?', 'ai-risk-benchmark' ),
				__( 'If you thought homework might be AI-generated, what would you do first?', 'ai-risk-benchmark' ),
			),
			'p_hw_first_response' => array(
				__( 'If your child is struggling with homework, what is your first response?', 'ai-risk-benchmark' ),
				__( 'When homework is difficult, what do you usually do first?', 'ai-risk-benchmark' ),
			),
			'p_parent_ai_hw' => array(
				__( 'How often do you personally use AI to help your child with schoolwork?', 'ai-risk-benchmark' ),
				__( 'How often do you use tools like ChatGPT to help with homework or revision at home?', 'ai-risk-benchmark' ),
			),
			'p_parent_ai_comms' => array(
				__( 'How often do you use AI to draft emails or messages to school about your child?', 'ai-risk-benchmark' ),
				__( 'How often do you use AI when writing to teachers or school staff?', 'ai-risk-benchmark' ),
			),
			'p_school_expectations' => array(
				__( 'Do you know your school\'s expectations for AI use in homework?', 'ai-risk-benchmark' ),
				__( 'Are you clear on your school\'s rules for AI and homework?', 'ai-risk-benchmark' ),
			),
			'p_school_discuss' => array(
				__( 'How confident would you be discussing your child\'s AI use with their school if you had concerns?', 'ai-risk-benchmark' ),
				__( 'If you were worried about your child\'s AI use, how easy would it be to talk to school?', 'ai-risk-benchmark' ),
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
			't_trust_judgement' => array(
				__( 'When AI and your professional judgement disagree, which do you typically trust?', 'ai-risk-benchmark' ),
				__( 'If AI output conflicts with your professional judgement, which do you usually follow?', 'ai-risk-benchmark' ),
			),
			't_redesign_beyond_ai' => array(
				__( 'How often do you redesign tasks so pupils must show thinking beyond what AI can easily produce?', 'ai-risk-benchmark' ),
				__( 'How often do you adapt tasks to require student thinking that AI cannot simply generate?', 'ai-risk-benchmark' ),
			),
			't_spot_subtle' => array(
				__( 'When AI gives a plausible-sounding answer, how confident are you that you could spot a subtle error?', 'ai-risk-benchmark' ),
				__( 'How confident are you spotting a subtle mistake in an AI answer that sounds correct?', 'ai-risk-benchmark' ),
			),
			't_school_policy' => array(
				__( 'Do you know which AI tools your school has approved for staff and pupils?', 'ai-risk-benchmark' ),
				__( 'Are you clear on which AI tools your school allows staff and pupils to use?', 'ai-risk-benchmark' ),
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
				__( 'How tempting would it be to hand in AI-generated work without changing it?', 'ai-risk-benchmark' ),
				__( 'How tempting is it to submit AI answers without making them your own?', 'ai-risk-benchmark' ),
			),
			's_report_ai_harm' => array(
				__( 'If someone used AI to create a fake image, voice note or message about another student, would you know how to report it?', 'ai-risk-benchmark' ),
				__( 'Would you know how to report AI-generated fake content that harms another student?', 'ai-risk-benchmark' ),
			),
			's_verify'      => array(
				__( 'Do you check AI answers before submitting work?', 'ai-risk-benchmark' ),
				__( 'Do you review what AI gives you before you hand it in?', 'ai-risk-benchmark' ),
			),
			's_explain_own_words' => array(
				__( 'Could you explain an AI-generated answer in your own words?', 'ai-risk-benchmark' ),
				__( 'If asked, could you explain an AI answer using your own words?', 'ai-risk-benchmark' ),
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
				__( 'Does your school have a student AI-use policy?', 'ai-risk-benchmark' ),
				__( 'Is there a published policy on how pupils may use AI for learning and homework?', 'ai-risk-benchmark' ),
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
				__( 'Before pupils use AI tools, has your school checked privacy and data-protection risks?', 'ai-risk-benchmark' ),
				__( 'Has your school reviewed privacy risks before pupils use AI tools?', 'ai-risk-benchmark' ),
			),
			'l_approved_tools' => array(
				__( 'Is there a clear list of AI tools staff and pupils may use?', 'ai-risk-benchmark' ),
				__( 'Are approved AI tools communicated to staff and students?', 'ai-risk-benchmark' ),
			),
			'l_staff_training' => array(
				__( 'What proportion of staff have received AI training in the last 12 months?', 'ai-risk-benchmark' ),
				__( 'What share of staff have had AI risk and verification training in the past year?', 'ai-risk-benchmark' ),
			),
			'l_incidents'   => array(
				__( 'Are AI-related incidents logged and reviewed by leadership?', 'ai-risk-benchmark' ),
				__( 'Does the school track and learn from AI-related incidents?', 'ai-risk-benchmark' ),
			),
			'l_incident_escalation' => array(
				__( 'If an AI-related safeguarding or data incident happened tomorrow, would staff know how to escalate it?', 'ai-risk-benchmark' ),
				__( 'Would staff know the escalation route for an AI-related safeguarding or data breach incident?', 'ai-risk-benchmark' ),
			),
			'l_assessment_review' => array(
				__( 'Are assessments checked for vulnerability to AI-assisted cheating?', 'ai-risk-benchmark' ),
				__( 'Do you review how AI could affect the integrity of assessments?', 'ai-risk-benchmark' ),
			),
			'l_jcq'         => array(
				__( 'For pupils taking formal qualifications (e.g. GCSE or A Level), do staff understand the rules on AI use in exams and coursework?', 'ai-risk-benchmark' ),
				__( 'Do staff who need to know understand your school\'s rules on AI use in formal exams and assessed work?', 'ai-risk-benchmark' ),
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
		$fr = self::freq_risk();
		return self::attach_text_variants(
			array_merge(
			// —— Teacher ——
			array(
				self::q( 't_modify_pct', 'teacher', 'human_oversight', __( 'Human Oversight', 'ai-risk-benchmark' ), __( 'What percentage of AI-generated content do you modify before using it?', 'ai-risk-benchmark' ), array(), 'slider' ),
				self::q( 't_verify', 'teacher', 'human_oversight', __( 'Human Oversight', 'ai-risk-benchmark' ), __( 'Do you verify AI outputs before using them with pupils or colleagues?', 'ai-risk-benchmark' ), $f ),
				self::q( 't_cross_ref', 'teacher', 'human_oversight', __( 'Human Oversight', 'ai-risk-benchmark' ), __( 'How often do you cross-reference AI outputs with other sources?', 'ai-risk-benchmark' ), $f ),
				self::q( 't_challenge', 'teacher', 'human_oversight', __( 'Human Oversight', 'ai-risk-benchmark' ), __( 'Do you challenge AI recommendations when they seem incorrect?', 'ai-risk-benchmark' ), $f ),
				self::q( 't_trust_judgement', 'teacher', 'human_oversight', __( 'Human Oversight', 'ai-risk-benchmark' ), __( 'When AI and your professional judgement disagree, which do you typically trust?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'judgement', 'label' => __( 'Professional judgement', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'usually_judgement', 'label' => __( 'Usually professional judgement', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'usually_ai', 'label' => __( 'Usually AI', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'ai', 'label' => __( 'AI', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 't_spot_subtle', 'teacher', 'human_oversight', __( 'Human Oversight', 'ai-risk-benchmark' ), __( 'When AI gives an answer that sounds plausible, how confident are you that you could spot a subtle error?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'very', 'label' => __( 'Very confident', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'fairly', 'label' => __( 'Fairly confident', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'not_always', 'label' => __( 'Not always', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'not', 'label' => __( 'Not confident', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 't_ai_before_task', 'teacher', 'ai_dependency', __( 'Dependency', 'ai-risk-benchmark' ), __( 'How often do you use AI before attempting the task yourself?', 'ai-risk-benchmark' ), $fr ),
				self::q( 't_without_ai', 'teacher', 'ai_dependency', __( 'Dependency', 'ai-risk-benchmark' ), __( 'Could you teach effectively without AI for one week?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes_easily', 'label' => __( 'Yes, easily', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'yes_some', 'label' => __( 'Yes, with effort', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'difficult', 'label' => __( 'Difficult', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'Not realistically', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 't_feedback_ai', 'teacher', 'ai_dependency', __( 'Dependency', 'ai-risk-benchmark' ), __( 'How often do you use AI to write pupil feedback?', 'ai-risk-benchmark' ), $fr ),
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
				self::q( 't_school_policy', 'teacher', 'safe_adoption', __( 'School guidance', 'ai-risk-benchmark' ), __( 'Do you know which AI tools your school has approved for staff and pupil use?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes_list', 'label' => __( 'Yes — I know the approved list', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'some', 'label' => __( 'I know some are approved, but not the full list', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'unsure', 'label' => __( 'Not sure', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No — I don\'t know of an approved list', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 't_redesign_beyond_ai', 'teacher', 'assessment_integrity', __( 'Assessment design', 'ai-risk-benchmark' ), __( 'How often do you redesign tasks specifically to require student thinking beyond what AI can easily produce?', 'ai-risk-benchmark' ), $f ),
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
				self::q( 's_submitted_ai', 'student', 'assessment_integrity', __( 'Assessment integrity', 'ai-risk-benchmark' ), __( 'How tempting would it be to submit AI-generated work without changing it?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'not_at_all', 'label' => __( 'Not at all', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'occasionally', 'label' => __( 'Occasionally', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'quite', 'label' => __( 'Quite tempting', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'very', 'label' => __( 'Very tempting', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 's_verify', 'student', 'human_oversight', __( 'Verification', 'ai-risk-benchmark' ), __( 'Do you check AI answers before handing work in?', 'ai-risk-benchmark' ), $f ),
				self::q( 's_explain_own_words', 'student', 'human_oversight', __( 'Verification', 'ai-risk-benchmark' ), __( 'Could you explain an AI-generated answer in your own words?', 'ai-risk-benchmark' ), $f ),
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
				self::q( 's_report_ai_harm', 'student', 'safeguarding', __( 'Safeguarding', 'ai-risk-benchmark' ), __( 'If someone used AI to create a fake image, voice note or message of another student, would you know how to report it?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'probably', 'label' => __( 'Probably', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'unsure', 'label' => __( 'Unsure', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
			),
			// —— Parent ——
			array(
				self::q( 'p_child_uses', 'parent', 'ai_literacy', __( 'Awareness', 'ai-risk-benchmark' ), __( 'How confident are you that you know whether your child uses AI tools?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'very', 'label' => __( 'Very confident — I know whether they do', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'fairly', 'label' => __( 'Fairly confident', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'unsure', 'label' => __( 'Not confident / unsure', 'ai-risk-benchmark' ), 'score' => 3 ),
					array( 'value' => 'no_use', 'label' => __( 'Confident they do not use AI', 'ai-risk-benchmark' ), 'score' => 0 ),
				) ),
				self::q( 'p_know_tools', 'parent', 'ai_literacy', __( 'Awareness', 'ai-risk-benchmark' ), __( 'Do you know which AI tools your child uses?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes, clearly', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'some', 'label' => __( 'Some of them', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'vague', 'label' => __( 'Vaguely', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'p_child_unknown_use', 'parent', 'ai_literacy', __( 'Awareness', 'ai-risk-benchmark' ), __( 'How often does your child use AI without you knowing exactly what they used it for?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'never', 'label' => __( 'Never', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'occasionally', 'label' => __( 'Occasionally', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'frequently', 'label' => __( 'Frequently', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'dont_know', 'label' => __( 'I don\'t know', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'p_no_share', 'parent', 'privacy', __( 'Home AI safety', 'ai-risk-benchmark' ), __( 'Do you know what information children should not share with AI?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'mostly', 'label' => __( 'Mostly', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'unsure', 'label' => __( 'Unsure', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'p_harm_response', 'parent', 'safeguarding', __( 'Home AI safety', 'ai-risk-benchmark' ), __( 'If your child received an AI-generated image, voice message or fake account pretending to be someone they know, would they know what to do?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'definitely', 'label' => __( 'Definitely', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'probably', 'label' => __( 'Probably', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'unsure', 'label' => __( 'Unsure', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'unlikely', 'label' => __( 'Unlikely', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'p_home_ai_culture', 'parent', 'safe_adoption', __( 'Home AI safety', 'ai-risk-benchmark' ), __( 'Which statement best describes AI use in your home?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'discuss_regularly', 'label' => __( 'We regularly discuss AI use', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'discuss_occasionally', 'label' => __( 'We occasionally discuss it', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'rarely_discuss', 'label' => __( 'AI use happens but we rarely discuss it', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'dont_know', 'label' => __( 'I don\'t know how AI is being used', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'p_explain_own_words', 'parent', 'human_oversight', __( 'Homework oversight', 'ai-risk-benchmark' ), __( 'When your child uses AI for homework, how often do you ask them to explain the answer in their own words?', 'ai-risk-benchmark' ), self::freq() ),
				self::q( 'p_check_suspicion', 'parent', 'assessment_integrity', __( 'Homework oversight', 'ai-risk-benchmark' ), __( 'If you suspected your child had used AI for homework, how would you check?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'ask_explain', 'label' => __( 'Ask them to explain their work', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'compare_drafts', 'label' => __( 'Compare drafts or working', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'ai_detector', 'label' => __( 'Use an AI detector', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'wouldnt_know', 'label' => __( 'I wouldn\'t know how', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'p_hw_first_response', 'parent', 'safe_adoption', __( 'Homework oversight', 'ai-risk-benchmark' ), __( 'If your child is struggling with homework, what is your first response?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'work_together', 'label' => __( 'Work through it together', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'contact_school', 'label' => __( 'Contact school or teacher', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'online_resources', 'label' => __( 'Use online resources', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'use_ai', 'label' => __( 'Use AI for help', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'p_parent_ai_hw', 'parent', 'ai_dependency', __( 'Your AI use', 'ai-risk-benchmark' ), __( 'How often do you personally use AI to help your child with schoolwork?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'never', 'label' => __( 'Never', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'occasionally', 'label' => __( 'Occasionally', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'weekly', 'label' => __( 'Weekly', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'most_tasks', 'label' => __( 'Most homework tasks', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'p_parent_ai_comms', 'parent', 'ai_dependency', __( 'Your AI use', 'ai-risk-benchmark' ), __( 'How often do you use AI to draft emails or messages to school about your child?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'never', 'label' => __( 'Never', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'occasionally', 'label' => __( 'Occasionally', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'often', 'label' => __( 'Often', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'usually', 'label' => __( 'Usually', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'p_school_expectations', 'parent', 'governance', __( 'School partnership', 'ai-risk-benchmark' ), __( 'Do you know your school\'s expectations for AI use in homework?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes_clear', 'label' => __( 'Yes, clearly', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'somewhat', 'label' => __( 'Somewhat', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'heard', 'label' => __( 'I\'ve heard something', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'p_school_discuss', 'parent', 'governance', __( 'School partnership', 'ai-risk-benchmark' ), __( 'How confident would you be discussing your child\'s AI use with their school if you had concerns?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'very', 'label' => __( 'Very confident', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'fairly', 'label' => __( 'Fairly confident', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'unsure', 'label' => __( 'Unsure', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'not', 'label' => __( 'Not confident', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
			),
			// —— School leader ——
			array(
				self::q( 'l_policy', 'leader', 'governance', __( 'Governance', 'ai-risk-benchmark' ), __( 'Does your school have a student AI-use policy?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'embedded', 'label' => __( 'Published and embedded', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'published', 'label' => __( 'Published', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'draft', 'label' => __( 'Draft', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No policy', 'ai-risk-benchmark' ), 'score' => 3 ),
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
				self::q( 'l_dp_review', 'leader', 'privacy', __( 'Data Protection', 'ai-risk-benchmark' ), __( 'Before pupils use AI tools, has your school checked privacy and data-protection risks?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes, where needed', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'started', 'label' => __( 'Started', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'planned', 'label' => __( 'Planned', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'Not yet', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'l_approved_tools', 'leader', 'privacy', __( 'Data Protection', 'ai-risk-benchmark' ), __( 'Are approved AI tools listed for staff and students?', 'ai-risk-benchmark' ), $f ),
				self::q( 'l_staff_training', 'leader', 'human_oversight', __( 'Workforce Readiness', 'ai-risk-benchmark' ), __( 'What proportion of staff have received AI training in the last 12 months?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'over_75', 'label' => __( 'Over 75%', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'fifty_75', 'label' => __( '50–75%', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'under_50', 'label' => __( 'Under 50%', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'none', 'label' => __( 'None', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'l_incidents', 'leader', 'governance', __( 'Workforce Readiness', 'ai-risk-benchmark' ), __( 'Are AI-related incidents tracked and reviewed?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes, systematically', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'informal', 'label' => __( 'Informally', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'planned', 'label' => __( 'Planned', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'l_incident_escalation', 'leader', 'governance', __( 'Governance', 'ai-risk-benchmark' ), __( 'If an AI-related safeguarding or data incident occurred tomorrow, would staff know how to escalate it?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'mostly', 'label' => __( 'Mostly', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'unsure', 'label' => __( 'Unsure', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'l_assessment_review', 'leader', 'assessment_integrity', __( 'Assessment', 'ai-risk-benchmark' ), __( 'Are assessments reviewed for AI exposure?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes, systematically', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'some', 'label' => __( 'Some departments', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'ad_hoc', 'label' => __( 'Ad hoc', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q(
					'l_jcq',
					'leader',
					'assessment_integrity',
					__( 'Assessment', 'ai-risk-benchmark' ),
					__( 'For pupils taking formal qualifications (e.g. GCSE or A Level), do staff who need to know understand your school\'s rules on AI use in exams and coursework?', 'ai-risk-benchmark' ),
					array(
						array( 'value' => 'yes', 'label' => __( 'Yes, widely understood', 'ai-risk-benchmark' ), 'score' => 0 ),
						array( 'value' => 'some', 'label' => __( 'In some teams', 'ai-risk-benchmark' ), 'score' => 1 ),
						array( 'value' => 'planned', 'label' => __( 'Being rolled out', 'ai-risk-benchmark' ), 'score' => 2 ),
						array( 'value' => 'no', 'label' => __( 'Not yet', 'ai-risk-benchmark' ), 'score' => 3 ),
					),
					'radio',
					array(
						'show_for_phases' => array( 'secondary', 'all_through' ),
					)
				),
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
			),
			// —— Education support staff ——
			array(
				self::q( 'ss_recognise_inaccuracy', 'support_staff', 'ai_literacy', __( 'AI literacy', 'ai-risk-benchmark' ), __( 'How confident are you in recognising when AI may provide inaccurate information?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'very', 'label' => __( 'Very confident', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'mostly', 'label' => __( 'Mostly confident', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'unsure', 'label' => __( 'Unsure', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'not', 'label' => __( 'Not confident', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'ss_ai_understanding', 'support_staff', 'ai_literacy', __( 'AI literacy', 'ai-risk-benchmark' ), __( 'Which statement best reflects your understanding of AI?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'check', 'label' => __( 'AI can make mistakes and should be checked', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'usually', 'label' => __( 'AI is usually accurate', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'mostly', 'label' => __( 'AI is accurate most of the time', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'unsure', 'label' => __( 'I am not sure', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'ss_review_comms', 'support_staff', 'human_oversight', __( 'Human oversight', 'ai-risk-benchmark' ), __( 'How often do you review and edit AI-generated emails, letters or reports before using them?', 'ai-risk-benchmark' ), $f ),
				self::q( 'ss_verify_before_act', 'support_staff', 'human_oversight', __( 'Human oversight', 'ai-risk-benchmark' ), __( 'If AI produced information that sounded correct, how likely would you be to verify it before acting on it?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'always', 'label' => __( 'Always verify', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'usually', 'label' => __( 'Usually verify', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'sometimes', 'label' => __( 'Sometimes verify', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'rarely', 'label' => __( 'Rarely verify', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'ss_spot_subtle_error', 'support_staff', 'human_oversight', __( 'Human oversight', 'ai-risk-benchmark' ), __( 'How confident are you that you could spot a subtle error in AI-generated content?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'very', 'label' => __( 'Very confident', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'fairly', 'label' => __( 'Fairly confident', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'not_always', 'label' => __( 'Not always', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'not', 'label' => __( 'Not confident', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'ss_draft_comms', 'support_staff', 'ai_dependency', __( 'Operational dependency', 'ai-risk-benchmark' ), __( 'How often do you use AI to draft emails, letters or communications?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'rarely', 'label' => __( 'Rarely', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'sometimes', 'label' => __( 'Sometimes', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'often', 'label' => __( 'Often', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'daily', 'label' => __( 'Daily', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'ss_without_ai', 'support_staff', 'ai_dependency', __( 'Operational dependency', 'ai-risk-benchmark' ), __( 'Could you complete your role effectively without AI for one week?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'easily', 'label' => __( 'Easily', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'mostly', 'label' => __( 'Mostly', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'difficulty', 'label' => __( 'With difficulty', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'ss_task_approach', 'support_staff', 'ai_dependency', __( 'Operational dependency', 'ai-risk-benchmark' ), __( 'When completing a task, which best describes your approach?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'self_first', 'label' => __( 'I attempt it myself before using AI', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'sometimes_early', 'label' => __( 'I sometimes use AI early', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'often_start', 'label' => __( 'I often start with AI', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'almost_always', 'label' => __( 'I almost always start with AI', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'ss_entered_personal', 'support_staff', 'privacy', __( 'Data protection', 'ai-risk-benchmark' ), __( 'Have you ever entered personal information into an AI tool?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'never', 'label' => __( 'Never', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'occasionally', 'label' => __( 'Occasionally', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'unsure', 'label' => __( 'Unsure', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'frequently', 'label' => __( 'Frequently', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'ss_never_enter', 'support_staff', 'privacy', __( 'Data protection', 'ai-risk-benchmark' ), __( 'Which of the following should never be entered into a public AI tool without approval?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'pupil', 'label' => __( 'Pupil information', 'ai-risk-benchmark' ), 'score' => 3 ),
					array( 'value' => 'hr', 'label' => __( 'HR records', 'ai-risk-benchmark' ), 'score' => 3 ),
					array( 'value' => 'safeguarding', 'label' => __( 'Safeguarding concerns', 'ai-risk-benchmark' ), 'score' => 3 ),
					array( 'value' => 'all', 'label' => __( 'All of the above', 'ai-risk-benchmark' ), 'score' => 0 ),
				) ),
				self::q( 'ss_data_rules', 'support_staff', 'privacy', __( 'Data protection', 'ai-risk-benchmark' ), __( 'How confident are you in understanding your organisation\'s rules for using data in AI tools?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'very', 'label' => __( 'Very confident', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'mostly', 'label' => __( 'Mostly confident', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'unsure', 'label' => __( 'Unsure', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'not', 'label' => __( 'Not confident', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'ss_approved_tools', 'support_staff', 'safe_adoption', __( 'Safe adoption', 'ai-risk-benchmark' ), __( 'Do you know which AI tools are approved for use in your school or trust?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes_follow', 'label' => __( 'Yes and I follow the guidance', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'know_some', 'label' => __( 'I know some approved tools', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'unsure', 'label' => __( 'I am unsure', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
				self::q( 'ss_check_approval', 'support_staff', 'safe_adoption', __( 'Safe adoption', 'ai-risk-benchmark' ), __( 'Before using a new AI tool, how likely are you to check whether it has been approved?', 'ai-risk-benchmark' ), $f ),
				self::q( 'ss_report_issue', 'support_staff', 'safe_adoption', __( 'Safe adoption', 'ai-risk-benchmark' ), __( 'If an AI-related data protection or safeguarding issue occurred, would you know how to report it?', 'ai-risk-benchmark' ), array(
					array( 'value' => 'yes', 'label' => __( 'Yes', 'ai-risk-benchmark' ), 'score' => 0 ),
					array( 'value' => 'probably', 'label' => __( 'Probably', 'ai-risk-benchmark' ), 'score' => 1 ),
					array( 'value' => 'unsure', 'label' => __( 'Unsure', 'ai-risk-benchmark' ), 'score' => 2 ),
					array( 'value' => 'no', 'label' => __( 'No', 'ai-risk-benchmark' ), 'score' => 3 ),
				) ),
			)
		)
		);
	}
}
