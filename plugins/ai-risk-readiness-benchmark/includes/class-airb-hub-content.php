<?php
/**
 * Hub page intervention frameworks — DfE-aligned structured content.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds Gutenberg content for Level 2 improvement pages.
 */
class AIRB_Hub_Content {

	/** Marker in legacy placeholder pages — used when upgrading existing installs. */
	public const LEGACY_PLACEHOLDER_MARKER = 'Content on this page can be expanded in the WordPress editor';

	/** Content version marker — used by hub page patch upgrades. */
	public const CONTENT_VERSION_MARKER = 'airb-intervention-v19';

	/**
	 * Build page content for a hub slug.
	 *
	 * @param string $slug          Page slug.
	 * @param string $excerpt       Page excerpt fallback.
	 * @param string $benchmark_cta Benchmark CTA label.
	 */
	public static function for_slug( string $slug, string $excerpt, string $benchmark_cta ): string {
		$frameworks = self::frameworks();
		$def        = $frameworks[ $slug ] ?? self::generic_framework( $slug, $excerpt );
		return self::render( $def, $benchmark_cta, $slug );
	}

	/**
	 * Slugs that use the full intervention template.
	 *
	 * @return array<int, string>
	 */
	public static function intervention_slugs(): array {
		return array_keys( self::frameworks() );
	}

	/**
	 * Campaign contact email (Customizer `aiad_contact_email`, else campaign inbox).
	 */
	private static function contact_email(): string {
		$email = '';
		if ( function_exists( 'get_theme_mod' ) ) {
			$email = sanitize_email( (string) get_theme_mod( 'aiad_contact_email', '' ) );
		}
		return $email ? $email : 'info@aiawarenessday.co.uk';
	}

	/**
	 * Build a "contact us for guidance and support" mailto for a development area.
	 *
	 * @param string $area Development / support area label.
	 */
	private static function area_contact_link( string $area ): string {
		unset( $area );
		return '#airb-hub-interest';
	}

	/**
	 * HTML attributes for hub interest anchors (prefill via data attribute — hash query breaks native scroll).
	 *
	 * @param string $prefill Interest checkbox slug.
	 */
	private static function hub_interest_link_attrs( string $prefill = '' ): string {
		$prefill = sanitize_key( $prefill );
		if ( '' === $prefill ) {
			return '';
		}
		return ' data-airb-hub-prefill="' . esc_attr( $prefill ) . '"';
	}

	/**
	 * Build an opening anchor tag for the on-page hub interest form.
	 *
	 * @param string $prefill Interest checkbox slug.
	 */
	private static function hub_interest_link_open( string $prefill = '' ): string {
		return '<a href="#airb-hub-interest"' . self::hub_interest_link_attrs( $prefill ) . '>';
	}

	/**
	 * "Wider Support & Development" funnel block — appears on every hub page
	 * unless the framework sets `'support' => false`. Restores per-area
	 * contact ("email") doorways for guidance and support.
	 *
	 * @param array<string, mixed> $def Framework definition.
	 * @return array<int, string>
	 */
	private static function support_blocks( array $def ): array {
		$support = $def['support'] ?? array();
		if ( false === $support ) {
			return array();
		}
		$support = (array) $support;

		$heading = (string) ( $support['heading'] ?? __( 'Wider Support & Development', 'ai-risk-benchmark' ) );
		$intro   = (string) ( $support['intro'] ?? __( 'For more information, guidance and support — including how AI Awareness Day can help your school — contact the AI Awareness team.', 'ai-risk-benchmark' ) );

		$blocks   = array();
		$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $heading ) . '</h2><!-- /wp:heading -->';
		if ( $intro ) {
			$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( $intro ) . '</p><!-- /wp:paragraph -->';
		}

		$items = (array) ( $support['items'] ?? array() );
		if ( $items ) {
			$cta_label = (string) ( $support['item_cta'] ?? __( 'Get further support', 'ai-risk-benchmark' ) );
			foreach ( $items as $item ) {
				$item  = (array) $item;
				$label = (string) ( $item['label'] ?? '' );
				if ( '' === $label ) {
					continue;
				}
				$prefill  = sanitize_key( (string) ( $item['interest'] ?? '' ) );
				$blocks[] = '<!-- wp:paragraph --><p><strong>' . esc_html( $label ) . '</strong> — ' . self::hub_interest_link_open( $prefill ) . esc_html( $cta_label ) . '</a></p><!-- /wp:paragraph -->';
			}
		} else {
			$cta_label = (string) ( $support['cta'] ?? __( 'Need further support?', 'ai-risk-benchmark' ) );
			$blocks[]  = '<!-- wp:paragraph --><p><a href="#airb-hub-interest">' . esc_html( $cta_label ) . '</a></p><!-- /wp:paragraph -->';
		}

		return $blocks;
	}

	/**
	 * Standard UK guidance alignment note (not a compliance claim).
	 */
	private static function uk_guidance_note(): string {
		return __( 'This intervention framework references recognised UK education guidance — including Department for Education (DfE) generative AI guidance for schools, Information Commissioner\'s Office (ICO) expectations on pupil data, Keeping Children Safe in Education (KCSIE), Joint Council for Qualifications (JCQ) and Ofqual assessment integrity guidance, and Ofsted expectations for safeguarding and curriculum quality. It supports school self-assessment; it is not legal advice and does not imply official endorsement or compliance certification.', 'ai-risk-benchmark' );
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	public static function frameworks(): array {
		return array(
			'teacher-ai-verification-framework' => array(
				'headline'            => __( 'Teacher AI Verification Framework™', 'ai-risk-benchmark' ),
				'subtitle'            => __( 'Verify Before You Trust™', 'ai-risk-benchmark' ),
				'why_heading'         => __( 'Why This Matters', 'ai-risk-benchmark' ),
				'why_paragraphs'      => array(
					__( 'Artificial intelligence can save teachers significant time, improve productivity and provide useful starting points for planning, assessment and communication. However, AI systems can also generate inaccurate information, misleading advice and inappropriate content.', 'ai-risk-benchmark' ),
					__( 'Teachers remain professionally accountable for any material used with pupils, parents and colleagues. AI can assist your professional judgement, but it cannot replace it.', 'ai-risk-benchmark' ),
					__( 'The purpose of this framework is to help teachers maintain strong human oversight when using AI tools.', 'ai-risk-benchmark' ),
				),
				'preface_sections'    => array(
					array(
						'heading' => __( 'What Good AI Use Looks Like', 'ai-risk-benchmark' ),
						'intro'   => __( 'Responsible AI use means:', 'ai-risk-benchmark' ),
						'items'   => array(
							__( 'Using AI to support thinking, not replace it.', 'ai-risk-benchmark' ),
							__( 'Verifying important information before use.', 'ai-risk-benchmark' ),
							__( 'Adapting AI-generated content for your pupils.', 'ai-risk-benchmark' ),
							__( 'Protecting personal and sensitive information.', 'ai-risk-benchmark' ),
							__( 'Remaining accountable for professional decisions.', 'ai-risk-benchmark' ),
						),
					),
				),
				'framework_heading'   => __( 'The Verify Before You Trust™ Framework', 'ai-risk-benchmark' ),
				'steps'               => array(
					array(
						'title' => __( 'Step 1: Form Your Own View First', 'ai-risk-benchmark' ),
						'body'  => __( 'Before opening an AI tool:', 'ai-risk-benchmark' ),
						'items' => array(
							__( 'Clarify your objective.', 'ai-risk-benchmark' ),
							__( 'Identify what success looks like.', 'ai-risk-benchmark' ),
							__( 'Consider what you already know.', 'ai-risk-benchmark' ),
						),
						'quote' => __( 'What would I do if AI was unavailable?', 'ai-risk-benchmark' ),
						'after' => __( 'This helps maintain professional judgement and reduces over-reliance.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Step 2: Compare, Don\'t Defer', 'ai-risk-benchmark' ),
						'body'  => __( 'Treat AI as a colleague offering suggestions, not an expert making decisions. Review against your own knowledge and experience:', 'ai-risk-benchmark' ),
						'items' => array(
							__( 'Lesson plans', 'ai-risk-benchmark' ),
							__( 'Assessment questions', 'ai-risk-benchmark' ),
							__( 'Parent communications', 'ai-risk-benchmark' ),
							__( 'Behaviour strategies', 'ai-risk-benchmark' ),
							__( 'Classroom activities', 'ai-risk-benchmark' ),
						),
					),
					array(
						'title'       => __( 'Step 3: Check the Blind Spots', 'ai-risk-benchmark' ),
						'body'        => __( 'Review every AI output for:', 'ai-risk-benchmark' ),
						'subsections' => array(
							array(
								'title' => __( 'Accuracy', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Are facts correct?', 'ai-risk-benchmark' ),
									__( 'Are examples accurate?', 'ai-risk-benchmark' ),
									__( 'Are statistics current?', 'ai-risk-benchmark' ),
								),
							),
							array(
								'title' => __( 'Age Appropriateness', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Is the language suitable?', 'ai-risk-benchmark' ),
									__( 'Does it match the developmental stage of pupils?', 'ai-risk-benchmark' ),
								),
							),
							array(
								'title' => __( 'Bias', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Does the content represent groups fairly?', 'ai-risk-benchmark' ),
									__( 'Are alternative perspectives considered?', 'ai-risk-benchmark' ),
								),
							),
							array(
								'title' => __( 'Safeguarding', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Could this content create safeguarding concerns?', 'ai-risk-benchmark' ),
									__( 'Does it deal appropriately with sensitive topics?', 'ai-risk-benchmark' ),
								),
							),
						),
					),
					array(
						'title' => __( 'Step 4: Own The Decision', 'ai-risk-benchmark' ),
						'quote' => __( 'Would I confidently explain and justify this content to a parent, colleague or school leader?', 'ai-risk-benchmark' ),
						'after' => __( 'If the answer is no, revise or discard it.', 'ai-risk-benchmark' ),
					),
				),
				'post_sections'       => array(
					array(
						'heading' => __( 'Common AI Mistakes', 'ai-risk-benchmark' ),
						'blocks'  => array(
							array(
								'title' => __( 'Hallucinations', 'ai-risk-benchmark' ),
								'intro' => __( 'AI can invent:', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Sources', 'ai-risk-benchmark' ),
									__( 'Quotes', 'ai-risk-benchmark' ),
									__( 'Statistics', 'ai-risk-benchmark' ),
									__( 'Research findings', 'ai-risk-benchmark' ),
								),
							),
							array(
								'title' => __( 'False Confidence', 'ai-risk-benchmark' ),
								'body'  => __( 'AI often sounds certain when it is wrong.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Outdated Information', 'ai-risk-benchmark' ),
								'body'  => __( 'Information may not reflect current guidance or curriculum changes.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Generic Responses', 'ai-risk-benchmark' ),
								'body'  => __( 'Outputs often require adaptation to suit your pupils and context.', 'ai-risk-benchmark' ),
							),
						),
					),
					array(
						'heading' => __( 'Data Protection Reminder', 'ai-risk-benchmark' ),
						'intro'   => __( 'Never enter the following into public AI tools unless approved by your school and compliant with data protection requirements (ICO):', 'ai-risk-benchmark' ),
						'items'   => array(
							__( 'Pupil names', 'ai-risk-benchmark' ),
							__( 'SEND information', 'ai-risk-benchmark' ),
							__( 'Safeguarding concerns', 'ai-risk-benchmark' ),
							__( 'Medical information', 'ai-risk-benchmark' ),
							__( 'Behaviour records', 'ai-risk-benchmark' ),
						),
					),
				),
				'checklist'           => array(
					'heading' => __( 'Quick Verification Checklist', 'ai-risk-benchmark' ),
					'intro'   => __( 'Before using AI-generated content:', 'ai-risk-benchmark' ),
					'items'   => array(
						__( 'I understand the purpose.', 'ai-risk-benchmark' ),
						__( 'I have reviewed the content.', 'ai-risk-benchmark' ),
						__( 'I have checked accuracy.', 'ai-risk-benchmark' ),
						__( 'I have considered safeguarding implications.', 'ai-risk-benchmark' ),
						__( 'I have adapted it for my pupils.', 'ai-risk-benchmark' ),
						__( 'I am willing to take responsibility for the final version.', 'ai-risk-benchmark' ),
					),
				),
				'download'            => array(
					'label' => __( 'Teacher Verification Checklist (PDF — add download in editor)', 'ai-risk-benchmark' ),
					'note'  => __( 'Printable version of the quick verification checklist above.', 'ai-risk-benchmark' ),
				),
				'training'            => array(
					'heading' => __( 'Training Available', 'ai-risk-benchmark' ),
					'label'   => __( 'AI Awareness Day Teacher Session', 'ai-risk-benchmark' ),
					'path'    => 'ai-awareness-day',
					'topics'  => array(
						__( 'Human oversight', 'ai-risk-benchmark' ),
						__( 'AI literacy', 'ai-risk-benchmark' ),
						__( 'Data protection', 'ai-risk-benchmark' ),
						__( 'Safeguarding', 'ai-risk-benchmark' ),
						__( 'Responsible classroom use', 'ai-risk-benchmark' ),
					),
				),
				'benchmark'           => array(
					'label' => __( 'Retake the benchmark after applying these principles', 'ai-risk-benchmark' ),
					'ref'   => 'human_oversight',
				),
				'benchmark_improves'  => array(
					__( 'Human Oversight', 'ai-risk-benchmark' ),
					__( 'AI Literacy', 'ai-risk-benchmark' ),
					__( 'Safe Adoption', 'ai-risk-benchmark' ),
					__( 'Privacy Awareness', 'ai-risk-benchmark' ),
				),
			),
			'think-first-prompt-second'         => array(
				'headline'            => __( 'Think First, Prompt Second™', 'ai-risk-benchmark' ),
				'subtitle'            => __( 'A Student Framework for Using AI Responsibly', 'ai-risk-benchmark' ),
				'why_heading'         => __( 'Why This Matters', 'ai-risk-benchmark' ),
				'why_paragraphs'      => array(
					__( 'AI can be a powerful learning tool. It can explain concepts, generate examples and help you revise.', 'ai-risk-benchmark' ),
					__( 'However, if AI does all the thinking, you may learn less, remember less and become dependent on it.', 'ai-risk-benchmark' ),
					__( 'The goal is not to avoid AI. The goal is to use AI in a way that helps you learn.', 'ai-risk-benchmark' ),
				),
				'framework_heading'   => __( 'The Four-Step Framework', 'ai-risk-benchmark' ),
				'steps'               => array(
					array(
						'title' => __( 'Step 1: Think First', 'ai-risk-benchmark' ),
						'body'  => __( 'Before using AI:', 'ai-risk-benchmark' ),
						'items' => array(
							__( 'Read the question carefully.', 'ai-risk-benchmark' ),
							__( 'Make your own attempt.', 'ai-risk-benchmark' ),
							__( 'Write down your ideas.', 'ai-risk-benchmark' ),
						),
						'after' => __( 'Learning happens when your brain works through a problem. Struggle is part of learning.', 'ai-risk-benchmark' ),
					),
					array(
						'title'       => __( 'Step 2: Prompt With Purpose', 'ai-risk-benchmark' ),
						'body'        => __( 'Ask AI to help you understand.', 'ai-risk-benchmark' ),
						'subsections' => array(
							array(
								'title' => __( 'Good examples', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Explain this concept.', 'ai-risk-benchmark' ),
									__( 'Give me an example.', 'ai-risk-benchmark' ),
									__( 'Test my understanding.', 'ai-risk-benchmark' ),
									__( 'Help me improve my answer.', 'ai-risk-benchmark' ),
								),
							),
							array(
								'title' => __( 'Less helpful examples', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Write my essay.', 'ai-risk-benchmark' ),
									__( 'Complete my homework.', 'ai-risk-benchmark' ),
									__( 'Give me the final answer.', 'ai-risk-benchmark' ),
								),
							),
						),
					),
					array(
						'title' => __( 'Step 3: Check and Rewrite', 'ai-risk-benchmark' ),
						'body'  => __( 'Never copy and paste AI responses. Instead:', 'ai-risk-benchmark' ),
						'items' => array(
							__( 'Check the answer.', 'ai-risk-benchmark' ),
							__( 'Compare it with class notes.', 'ai-risk-benchmark' ),
							__( 'Rewrite it in your own words.', 'ai-risk-benchmark' ),
							__( 'Explain it to someone else.', 'ai-risk-benchmark' ),
						),
						'after' => __( 'If you cannot explain it, you may not fully understand it.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Step 4: Be Honest', 'ai-risk-benchmark' ),
						'body'  => __( 'Different schools have different rules. Always follow your school\'s expectations. AI should support learning, not replace it.', 'ai-risk-benchmark' ),
					),
				),
				'post_sections'       => array(
					array(
						'heading' => __( 'Warning Signs of AI Dependency', 'ai-risk-benchmark' ),
						'intro'   => __( 'You may be becoming too reliant on AI if:', 'ai-risk-benchmark' ),
						'items'   => array(
							__( 'You use AI before attempting work.', 'ai-risk-benchmark' ),
							__( 'You copy answers without checking.', 'ai-risk-benchmark' ),
							__( 'You cannot explain the work yourself.', 'ai-risk-benchmark' ),
							__( 'You feel unable to complete tasks without AI.', 'ai-risk-benchmark' ),
						),
					),
					array(
						'heading' => __( 'Protect Your Privacy', 'ai-risk-benchmark' ),
						'intro'   => __( 'Never share:', 'ai-risk-benchmark' ),
						'items'   => array(
							__( 'Your full name', 'ai-risk-benchmark' ),
							__( 'School details', 'ai-risk-benchmark' ),
							__( 'Home address', 'ai-risk-benchmark' ),
							__( 'Passwords', 'ai-risk-benchmark' ),
							__( 'Photos', 'ai-risk-benchmark' ),
							__( 'Personal information', 'ai-risk-benchmark' ),
						),
						'body'    => __( 'AI tools do not need this information to help you learn.', 'ai-risk-benchmark' ),
					),
				),
				'challenge'           => array(
					'heading' => __( 'Student Challenge', 'ai-risk-benchmark' ),
					'intro'   => __( 'Try this:', 'ai-risk-benchmark' ),
					'steps'   => array(
						array(
							'title' => __( 'Task One', 'ai-risk-benchmark' ),
							'body'  => __( 'Complete a piece of work without AI.', 'ai-risk-benchmark' ),
						),
						array(
							'title' => __( 'Task Two', 'ai-risk-benchmark' ),
							'body'  => __( 'Use AI after your first attempt.', 'ai-risk-benchmark' ),
						),
						array(
							'title' => __( 'Compare', 'ai-risk-benchmark' ),
							'items' => array(
								__( 'What did AI improve?', 'ai-risk-benchmark' ),
								__( 'What did AI get wrong?', 'ai-risk-benchmark' ),
								__( 'What did you learn?', 'ai-risk-benchmark' ),
							),
						),
					),
				),
				'download'            => array(
					'label' => __( 'Think First checklist (PDF — add download in editor)', 'ai-risk-benchmark' ),
					'note'  => __( 'Wallet-size reminder for planners and homework diaries.', 'ai-risk-benchmark' ),
				),
				'training'            => array(
					'label' => __( 'Student AI Study Skills', 'ai-risk-benchmark' ),
					'path'  => 'student-ai-study-skills',
				),
				'remember'            => array(
					'heading' => __( 'Remember', 'ai-risk-benchmark' ),
					'body'    => __( 'Think First. Prompt Second.', 'ai-risk-benchmark' ),
				),
				'benchmark'           => array(
					'label' => __( 'Retake the benchmark after applying these principles', 'ai-risk-benchmark' ),
					'ref'   => 'ai_dependency',
				),
				'benchmark_improves'  => array(
					__( 'Independent Thinking', 'ai-risk-benchmark' ),
					__( 'Verification Skills', 'ai-risk-benchmark' ),
					__( 'AI Literacy', 'ai-risk-benchmark' ),
					__( 'Responsible AI Use', 'ai-risk-benchmark' ),
				),
			),
			'parent-ai-safety'                  => array(
				'headline'            => __( 'Parent AI Safety Guide', 'ai-risk-benchmark' ),
				'subtitle'            => __( 'Helping Your Child Use AI Safely and Responsibly', 'ai-risk-benchmark' ),
				'why_heading'         => __( 'Why This Matters', 'ai-risk-benchmark' ),
				'why_paragraphs'      => array(
					__( 'Many children now use AI tools at home for:', 'ai-risk-benchmark' ),
				),
				'why_list'            => array(
					__( 'Homework', 'ai-risk-benchmark' ),
					__( 'Revision', 'ai-risk-benchmark' ),
					__( 'Research', 'ai-risk-benchmark' ),
					__( 'Creativity', 'ai-risk-benchmark' ),
					__( 'Entertainment', 'ai-risk-benchmark' ),
				),
				'why_after'           => array(
					__( 'AI can provide benefits, but children also need support to use it safely and responsibly.', 'ai-risk-benchmark' ),
					__( 'Parents play a vital role in helping children develop healthy habits.', 'ai-risk-benchmark' ),
				),
				'framework_heading'   => __( 'What Parents Should Know', 'ai-risk-benchmark' ),
				'steps'               => array(
					array(
						'title' => __( 'AI Can Be Wrong', 'ai-risk-benchmark' ),
						'body'  => __( 'AI may provide:', 'ai-risk-benchmark' ),
						'items' => array(
							__( 'Incorrect facts', 'ai-risk-benchmark' ),
							__( 'Outdated information', 'ai-risk-benchmark' ),
							__( 'Biased responses', 'ai-risk-benchmark' ),
							__( 'Confident but inaccurate answers', 'ai-risk-benchmark' ),
						),
						'after' => __( 'Encourage children to check information using trusted sources.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'AI Is Not A Search Engine', 'ai-risk-benchmark' ),
						'body'  => __( 'AI generates responses. It does not always provide verified facts. Children should learn to question and verify answers.', 'ai-risk-benchmark' ),
					),
					array(
						'title'       => __( 'AI Should Support Learning', 'ai-risk-benchmark' ),
						'body'        => __( 'AI works best when it:', 'ai-risk-benchmark' ),
						'items'       => array(
							__( 'Explains concepts', 'ai-risk-benchmark' ),
							__( 'Provides examples', 'ai-risk-benchmark' ),
							__( 'Tests understanding', 'ai-risk-benchmark' ),
							__( 'Supports revision', 'ai-risk-benchmark' ),
						),
						'subsections' => array(
							array(
								'title' => __( 'AI should not replace', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Thinking', 'ai-risk-benchmark' ),
									__( 'Problem solving', 'ai-risk-benchmark' ),
									__( 'Independent effort', 'ai-risk-benchmark' ),
								),
							),
						),
					),
				),
				'post_sections'       => array(
					array(
						'heading' => __( 'Family AI Rules', 'ai-risk-benchmark' ),
						'intro'   => __( 'Consider agreeing simple rules:', 'ai-risk-benchmark' ),
						'blocks'  => array(
							array(
								'title' => __( 'Rule 1', 'ai-risk-benchmark' ),
								'body'  => __( 'Attempt work before using AI.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Rule 2', 'ai-risk-benchmark' ),
								'body'  => __( 'Check AI answers.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Rule 3', 'ai-risk-benchmark' ),
								'body'  => __( 'Never submit AI work as your own.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Rule 4', 'ai-risk-benchmark' ),
								'body'  => __( 'Protect personal information.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Rule 5', 'ai-risk-benchmark' ),
								'body'  => __( 'Talk about anything that feels concerning.', 'ai-risk-benchmark' ),
							),
						),
					),
					array(
						'heading' => __( 'Protecting Privacy', 'ai-risk-benchmark' ),
						'intro'   => __( 'Children should never share:', 'ai-risk-benchmark' ),
						'items'   => array(
							__( 'Full names', 'ai-risk-benchmark' ),
							__( 'Addresses', 'ai-risk-benchmark' ),
							__( 'School names', 'ai-risk-benchmark' ),
							__( 'Phone numbers', 'ai-risk-benchmark' ),
							__( 'Photos', 'ai-risk-benchmark' ),
							__( 'Personal information', 'ai-risk-benchmark' ),
						),
						'after'   => __( 'Public AI tools do not need these details.', 'ai-risk-benchmark' ),
					),
					array(
						'heading' => __( 'Talking About AI At Home', 'ai-risk-benchmark' ),
						'intro'   => __( 'Questions you could ask:', 'ai-risk-benchmark' ),
						'items'   => array(
							__( 'Which AI tools do you use?', 'ai-risk-benchmark' ),
							__( 'What do you use them for?', 'ai-risk-benchmark' ),
							__( 'How do you know if the answer is correct?', 'ai-risk-benchmark' ),
							__( 'Have you ever found AI to be wrong?', 'ai-risk-benchmark' ),
							__( 'What information should never be shared?', 'ai-risk-benchmark' ),
						),
					),
					array(
						'heading' => __( 'Homework and AI', 'ai-risk-benchmark' ),
						'body'    => __( 'A useful question is:', 'ai-risk-benchmark' ),
						'quote'   => __( 'Did AI help you think, or did it do the thinking for you?', 'ai-risk-benchmark' ),
						'after'   => __( 'The aim is to support learning, not replace it.', 'ai-risk-benchmark' ),
					),
					array(
						'heading' => __( 'When To Contact School', 'ai-risk-benchmark' ),
						'intro'   => __( 'Speak to your child\'s school if you have concerns about:', 'ai-risk-benchmark' ),
						'items'   => array(
							__( 'AI-generated bullying', 'ai-risk-benchmark' ),
							__( 'Deepfakes', 'ai-risk-benchmark' ),
							__( 'Inappropriate content', 'ai-risk-benchmark' ),
							__( 'Academic honesty', 'ai-risk-benchmark' ),
							__( 'Privacy and safety', 'ai-risk-benchmark' ),
						),
					),
				),
				'download'            => array(
					'label' => __( 'Parent AI Safety one-pager (PDF — add download in editor)', 'ai-risk-benchmark' ),
					'note'  => __( 'Print for parents\' evenings and home-school communication.', 'ai-risk-benchmark' ),
				),
				'training'            => array(
					'heading' => __( 'Training Available', 'ai-risk-benchmark' ),
					'label'   => __( 'AI Awareness Day — parent session', 'ai-risk-benchmark' ),
					'path'    => 'ai-awareness-day',
				),
				'benchmark'           => array(
					'label' => __( 'Retake the parent benchmark after applying these principles', 'ai-risk-benchmark' ),
					'ref'   => 'privacy',
				),
				'benchmark_supports'  => __( 'This guide supports:', 'ai-risk-benchmark' ),
				'benchmark_improves'  => array(
					__( 'Parent AI Awareness', 'ai-risk-benchmark' ),
					__( 'Home AI Safety', 'ai-risk-benchmark' ),
					__( 'Child Privacy Awareness', 'ai-risk-benchmark' ),
					__( 'Parent Confidence', 'ai-risk-benchmark' ),
				),
				'closing'             => array(
					__( 'The best protection is regular conversation.', 'ai-risk-benchmark' ),
					__( 'Children do not need parents to be AI experts.', 'ai-risk-benchmark' ),
					__( 'They need interested, informed and supportive adults.', 'ai-risk-benchmark' ),
				),
			),
			'parent-deepfake-awareness'         => array(
				'headline'            => __( 'Parent Deepfake Awareness', 'ai-risk-benchmark' ),
				'intro'               => __( 'A safeguarding intervention aligned to KCSIE — help families recognise AI-generated harm and respond appropriately.', 'ai-risk-benchmark' ),
				'purpose'             => __( 'Support safeguarding.', 'ai-risk-benchmark' ),
				'why_heading'         => __( 'Why this matters', 'ai-risk-benchmark' ),
				'why_body'            => __( 'Deepfakes and AI-generated imagery are a growing safeguarding concern. Schools and parents must recognise risks early and know how to respond — consistent with KCSIE and Ofsted safeguarding expectations.', 'ai-risk-benchmark' ),
				'framework_heading'   => __( 'What is a deepfake?', 'ai-risk-benchmark' ),
				'steps'               => array(
					array(
						'title' => __( 'Definition', 'ai-risk-benchmark' ),
						'body'  => __( 'AI-generated content designed to imitate real people — voice, face or events. It can look convincing and spread quickly.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Risks', 'ai-risk-benchmark' ),
						'body'  => '',
						'items' => array(
							__( 'Bullying and harassment', 'ai-risk-benchmark' ),
							__( 'Impersonation', 'ai-risk-benchmark' ),
							__( 'Reputation damage', 'ai-risk-benchmark' ),
							__( 'Fake evidence', 'ai-risk-benchmark' ),
							__( 'Online coercion', 'ai-risk-benchmark' ),
						),
					),
					array(
						'title' => __( 'What to do', 'ai-risk-benchmark' ),
						'body'  => '',
						'items' => array(
							__( 'Save evidence (screenshots, URLs, dates)', 'ai-risk-benchmark' ),
							__( 'Report concerns to the platform', 'ai-risk-benchmark' ),
							__( 'Contact school if pupils are involved — follow KCSIE escalation routes', 'ai-risk-benchmark' ),
						),
					),
				),
				'download'            => array(
					'label' => __( 'Deepfake response guide for parents (PDF — add download in editor)', 'ai-risk-benchmark' ),
					'note'  => __( 'Include school safeguarding contact details when publishing.', 'ai-risk-benchmark' ),
				),
				'training'            => array(
					'label' => __( 'AI Awareness Day — safeguarding briefing', 'ai-risk-benchmark' ),
					'path'  => 'ai-awareness-day',
				),
				'benchmark'           => array(
					'label' => __( 'Retake parent benchmark — Safeguarding domain', 'ai-risk-benchmark' ),
					'ref'   => 'safeguarding',
				),
				'benchmark_improves'  => array(
					__( 'Safeguarding', 'ai-risk-benchmark' ),
					__( 'Home AI Awareness', 'ai-risk-benchmark' ),
				),
			),
			'school-ai-governance'              => array(
				'headline'            => __( 'School AI Governance Framework', 'ai-risk-benchmark' ),
				'intro'               => __( 'A leadership intervention to improve Governance Maturity — aligned to DfE generative AI guidance, ICO accountability and governor oversight expectations.', 'ai-risk-benchmark' ),
				'purpose'             => __( 'Improve Governance Maturity.', 'ai-risk-benchmark' ),
				'why_heading'         => __( 'Why this matters', 'ai-risk-benchmark' ),
				'why_body'            => __( 'Governors and trust boards expect evidence of responsible AI adoption — policy, ownership, training and review — not ad hoc tool use. Ofsted may ask how safeguarding and curriculum quality are protected as AI use grows.', 'ai-risk-benchmark' ),
				'framework_heading'   => __( 'Governance checklist', 'ai-risk-benchmark' ),
				'steps'               => array(
					array(
						'title' => __( 'Leadership', 'ai-risk-benchmark' ),
						'body'  => '',
						'items' => array(
							__( 'Named AI Lead', 'ai-risk-benchmark' ),
							__( 'Annual review cycle', 'ai-risk-benchmark' ),
							__( 'Governor awareness', 'ai-risk-benchmark' ),
						),
					),
					array(
						'title' => __( 'Policy', 'ai-risk-benchmark' ),
						'body'  => '',
						'items' => array(
							__( 'Approved tools list', 'ai-risk-benchmark' ),
							__( 'Prohibited use cases', 'ai-risk-benchmark' ),
							__( 'Assessment expectations (JCQ / Ofqual)', 'ai-risk-benchmark' ),
						),
					),
					array(
						'title' => __( 'Staff', 'ai-risk-benchmark' ),
						'body'  => '',
						'items' => array(
							__( 'Annual training', 'ai-risk-benchmark' ),
							__( 'Incident reporting', 'ai-risk-benchmark' ),
							__( 'Verification expectations', 'ai-risk-benchmark' ),
						),
					),
					array(
						'title' => __( 'Pupils', 'ai-risk-benchmark' ),
						'body'  => '',
						'items' => array(
							__( 'AI literacy', 'ai-risk-benchmark' ),
							__( 'Responsible use guidance', 'ai-risk-benchmark' ),
							__( 'Safeguarding awareness (KCSIE)', 'ai-risk-benchmark' ),
						),
					),
				),
				'download'            => array(
					'label' => __( 'School AI Governance checklist (PDF — add download in editor)', 'ai-risk-benchmark' ),
					'note'  => __( 'Use with governor meetings and trust board papers.', 'ai-risk-benchmark' ),
				),
				'training'            => array(
					'label' => __( 'Book a free AI Readiness Review', 'ai-risk-benchmark' ),
					'path'  => 'contact',
				),
				'benchmark'           => array(
					'label' => __( 'Retake leader benchmark — Governance domain', 'ai-risk-benchmark' ),
					'ref'   => 'governance',
				),
				'benchmark_improves'  => array(
					__( 'Governance Maturity', 'ai-risk-benchmark' ),
					__( 'Safeguarding Readiness', 'ai-risk-benchmark' ),
					__( 'DfE Alignment Score', 'ai-risk-benchmark' ),
				),
			),
			'ai-policy-generator'             => array(
				'headline'            => __( 'School AI Policy: DfE Templates & Support', 'ai-risk-benchmark' ),
				'subtitle'            => __( 'Adapt the Official DfE AI Policy Template to Your School', 'ai-risk-benchmark' ),
				'why_heading'         => __( 'Why This Matters', 'ai-risk-benchmark' ),
				'why_paragraphs'      => array(
					__( 'Many schools understand that AI is becoming part of education but struggle to develop clear expectations for staff, pupils and parents.', 'ai-risk-benchmark' ),
					__( 'An AI policy helps create consistency, reduce risk and communicate expectations.', 'ai-risk-benchmark' ),
					__( 'There is no single policy that works for every school.', 'ai-risk-benchmark' ),
					__( 'A primary school will have different requirements from a secondary school. A MAT may need a different approach from a standalone school.', 'ai-risk-benchmark' ),
					__( 'The Department for Education publishes a free AI policy template that schools can adapt — there is no need to start from scratch. This page shows you how to tailor it to your context, and the AI Awareness Day team can support you through it.', 'ai-risk-benchmark' ),
				),
				'post_sections'       => array(
					array(
						'heading' => __( 'Before You Adapt Your Policy', 'ai-risk-benchmark' ),
						'intro'   => __( 'Consider:', 'ai-risk-benchmark' ),
						'blocks'  => array(
							array(
								'title' => __( 'School Phase', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Primary', 'ai-risk-benchmark' ),
									__( 'Secondary', 'ai-risk-benchmark' ),
									__( 'All-through', 'ai-risk-benchmark' ),
									__( 'Special School', 'ai-risk-benchmark' ),
								),
							),
							array(
								'title' => __( 'Structure', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Standalone School', 'ai-risk-benchmark' ),
									__( 'Academy', 'ai-risk-benchmark' ),
									__( 'Multi-Academy Trust', 'ai-risk-benchmark' ),
								),
							),
							array(
								'title' => __( 'Staff AI Use', 'ai-risk-benchmark' ),
								'body'  => __( 'Will staff be permitted to use AI for:', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Lesson planning', 'ai-risk-benchmark' ),
									__( 'Resource creation', 'ai-risk-benchmark' ),
									__( 'Administrative tasks', 'ai-risk-benchmark' ),
									__( 'Parent communication drafting', 'ai-risk-benchmark' ),
								),
							),
							array(
								'title' => __( 'Student AI Use', 'ai-risk-benchmark' ),
								'body'  => __( 'Will students be permitted to:', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Use AI for homework?', 'ai-risk-benchmark' ),
									__( 'Use AI for revision?', 'ai-risk-benchmark' ),
									__( 'Use AI for coursework support?', 'ai-risk-benchmark' ),
								),
							),
							array(
								'title' => __( 'Assessment Expectations', 'ai-risk-benchmark' ),
								'body'  => __( 'How will AI use be managed during:', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Homework', 'ai-risk-benchmark' ),
									__( 'Coursework', 'ai-risk-benchmark' ),
									__( 'Internal assessments', 'ai-risk-benchmark' ),
									__( 'Examinations', 'ai-risk-benchmark' ),
								),
							),
						),
					),
					array(
						'heading' => __( 'Recommended Policy Sections', 'ai-risk-benchmark' ),
						'blocks'  => array(
							array(
								'title' => __( 'Purpose', 'ai-risk-benchmark' ),
								'body'  => __( 'Why the school is using AI.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Scope', 'ai-risk-benchmark' ),
								'body'  => __( 'Who the policy applies to.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Staff Use', 'ai-risk-benchmark' ),
								'body'  => __( 'Approved and prohibited use cases.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Student Use', 'ai-risk-benchmark' ),
								'body'  => __( 'Expectations and responsibilities.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Data Protection', 'ai-risk-benchmark' ),
								'body'  => __( 'Requirements around personal information.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Safeguarding', 'ai-risk-benchmark' ),
								'body'  => __( 'AI-related safeguarding considerations.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Assessment Integrity', 'ai-risk-benchmark' ),
								'body'  => __( 'Appropriate and inappropriate use.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Governance', 'ai-risk-benchmark' ),
								'body'  => __( 'Leadership oversight and review arrangements.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Training', 'ai-risk-benchmark' ),
								'body'  => __( 'Staff development expectations.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Review Cycle', 'ai-risk-benchmark' ),
								'body'  => __( 'Annual review recommended.', 'ai-risk-benchmark' ),
							),
						),
					),
					array(
						'heading' => __( 'Questions Before Approval', 'ai-risk-benchmark' ),
						'intro'   => __( 'Can every member of staff explain:', 'ai-risk-benchmark' ),
						'items'   => array(
							__( 'What AI is?', 'ai-risk-benchmark' ),
							__( 'What AI is not?', 'ai-risk-benchmark' ),
							__( 'What information should never be entered into AI tools?', 'ai-risk-benchmark' ),
							__( 'What human oversight means?', 'ai-risk-benchmark' ),
						),
						'after'   => __( 'If not, training should accompany policy implementation.', 'ai-risk-benchmark' ),
					),
					array(
						'heading' => __( 'Recommended Next Steps', 'ai-risk-benchmark' ),
						'blocks'  => array(
							array(
								'body' => __( 'Download the official DfE AI policy template and adapt it using the sections above.', 'ai-risk-benchmark' ),
								'link' => array(
									'label' => __( 'DfE — Using AI in education settings (policy guidance & template)', 'ai-risk-benchmark' ),
									'url'   => AIRB_Defaults::dfe_url_using_ai(),
								),
							),
							array(
								'body'  => __( 'Review your adapted policy with:', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Senior Leadership Team', 'ai-risk-benchmark' ),
									__( 'DSL', 'ai-risk-benchmark' ),
									__( 'DPO', 'ai-risk-benchmark' ),
									__( 'Governors or Trust Leaders', 'ai-risk-benchmark' ),
								),
							),
							array(
								'body' => __( 'Need help tailoring it to your school? Contact the AI Awareness Day team for guidance and support.', 'ai-risk-benchmark' ),
							),
						),
					),
				),
				'benchmark'           => array(
					'label' => __( 'Retake the leader benchmark after implementing your policy', 'ai-risk-benchmark' ),
					'ref'   => 'governance',
				),
				'benchmark_supports'  => __( 'Supports:', 'ai-risk-benchmark' ),
				'benchmark_improves'  => array(
					__( 'Governance', 'ai-risk-benchmark' ),
					__( 'Safe Adoption', 'ai-risk-benchmark' ),
					__( 'Staff Readiness', 'ai-risk-benchmark' ),
					__( 'Privacy & Data Protection', 'ai-risk-benchmark' ),
				),
				'closing'             => array(
					__( 'Good governance begins with clear expectations.', 'ai-risk-benchmark' ),
				),
			),
			'ai-risk-register'                => array(
				'headline'            => __( 'AI Risk Register', 'ai-risk-benchmark' ),
				'subtitle'            => __( 'Identifying and Managing AI-Related Risks', 'ai-risk-benchmark' ),
				'why_heading'         => __( 'Why This Matters', 'ai-risk-benchmark' ),
				'why_paragraphs'      => array(
					__( 'Every school manages risk.', 'ai-risk-benchmark' ),
					__( 'AI introduces new opportunities, but also new forms of exposure.', 'ai-risk-benchmark' ),
					__( 'An AI Risk Register helps schools:', 'ai-risk-benchmark' ),
				),
				'why_list'            => array(
					__( 'Identify risks', 'ai-risk-benchmark' ),
					__( 'Assign ownership', 'ai-risk-benchmark' ),
					__( 'Track controls', 'ai-risk-benchmark' ),
					__( 'Monitor improvement', 'ai-risk-benchmark' ),
				),
				'why_after'           => array(
					__( 'This register should be reviewed regularly alongside safeguarding, data protection and strategic risk reviews.', 'ai-risk-benchmark' ),
				),
				'risk_categories'     => array(
					array(
						'heading' => __( 'Risk Category 1: Data Protection', 'ai-risk-benchmark' ),
						'fields'  => array(
							array(
								'label' => __( 'Example Risk', 'ai-risk-benchmark' ),
								'body'  => __( 'Staff enter identifiable pupil information into public AI tools.', 'ai-risk-benchmark' ),
							),
							array(
								'label' => __( 'Potential Impact', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Data breach', 'ai-risk-benchmark' ),
									__( 'Loss of trust', 'ai-risk-benchmark' ),
									__( 'Regulatory concerns', 'ai-risk-benchmark' ),
								),
							),
							array(
								'label' => __( 'Example Controls', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Staff training', 'ai-risk-benchmark' ),
									__( 'Approved tools list', 'ai-risk-benchmark' ),
									__( 'Privacy guidance', 'ai-risk-benchmark' ),
								),
							),
							array(
								'label' => __( 'Suggested Owner', 'ai-risk-benchmark' ),
								'body'  => __( 'DPO / AI Lead', 'ai-risk-benchmark' ),
							),
						),
					),
					array(
						'heading' => __( 'Risk Category 2: Safeguarding', 'ai-risk-benchmark' ),
						'fields'  => array(
							array(
								'label' => __( 'Example Risk', 'ai-risk-benchmark' ),
								'body'  => __( 'AI-generated content contributes to bullying, impersonation or deepfake incidents.', 'ai-risk-benchmark' ),
							),
							array(
								'label' => __( 'Potential Impact', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Pupil harm', 'ai-risk-benchmark' ),
									__( 'Safeguarding concerns', 'ai-risk-benchmark' ),
								),
							),
							array(
								'label' => __( 'Example Controls', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Online safety education', 'ai-risk-benchmark' ),
									__( 'Staff awareness', 'ai-risk-benchmark' ),
									__( 'Incident reporting procedures', 'ai-risk-benchmark' ),
								),
							),
							array(
								'label' => __( 'Suggested Owner', 'ai-risk-benchmark' ),
								'body'  => __( 'DSL', 'ai-risk-benchmark' ),
							),
						),
					),
					array(
						'heading' => __( 'Risk Category 3: Assessment Integrity', 'ai-risk-benchmark' ),
						'fields'  => array(
							array(
								'label' => __( 'Example Risk', 'ai-risk-benchmark' ),
								'body'  => __( 'Students submit AI-generated work without disclosure.', 'ai-risk-benchmark' ),
							),
							array(
								'label' => __( 'Potential Impact', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Reduced learning', 'ai-risk-benchmark' ),
									__( 'Academic dishonesty', 'ai-risk-benchmark' ),
								),
							),
							array(
								'label' => __( 'Example Controls', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Assessment review', 'ai-risk-benchmark' ),
									__( 'Student guidance', 'ai-risk-benchmark' ),
									__( 'Teacher verification processes', 'ai-risk-benchmark' ),
								),
							),
							array(
								'label' => __( 'Suggested Owner', 'ai-risk-benchmark' ),
								'body'  => __( 'Assessment Lead', 'ai-risk-benchmark' ),
							),
						),
					),
					array(
						'heading' => __( 'Risk Category 4: Staff Over-Reliance', 'ai-risk-benchmark' ),
						'fields'  => array(
							array(
								'label' => __( 'Example Risk', 'ai-risk-benchmark' ),
								'body'  => __( 'Staff accept AI-generated outputs without sufficient review.', 'ai-risk-benchmark' ),
							),
							array(
								'label' => __( 'Potential Impact', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Inaccurate teaching materials', 'ai-risk-benchmark' ),
									__( 'Reduced professional judgement', 'ai-risk-benchmark' ),
								),
							),
							array(
								'label' => __( 'Example Controls', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Verification framework', 'ai-risk-benchmark' ),
									__( 'Human oversight expectations', 'ai-risk-benchmark' ),
									__( 'Professional development', 'ai-risk-benchmark' ),
								),
							),
							array(
								'label' => __( 'Suggested Owner', 'ai-risk-benchmark' ),
								'body'  => __( 'AI Lead', 'ai-risk-benchmark' ),
							),
						),
					),
					array(
						'heading' => __( 'Risk Category 5: Governance', 'ai-risk-benchmark' ),
						'fields'  => array(
							array(
								'label' => __( 'Example Risk', 'ai-risk-benchmark' ),
								'body'  => __( 'AI use expands without oversight or policy review.', 'ai-risk-benchmark' ),
							),
							array(
								'label' => __( 'Potential Impact', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Inconsistent practice', 'ai-risk-benchmark' ),
									__( 'Increased exposure', 'ai-risk-benchmark' ),
								),
							),
							array(
								'label' => __( 'Example Controls', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'AI policy', 'ai-risk-benchmark' ),
									__( 'Annual review', 'ai-risk-benchmark' ),
									__( 'Governor reporting', 'ai-risk-benchmark' ),
								),
							),
							array(
								'label' => __( 'Suggested Owner', 'ai-risk-benchmark' ),
								'body'  => __( 'Headteacher / Trust Lead', 'ai-risk-benchmark' ),
							),
						),
					),
				),
				'risk_matrix'         => array(
					'heading' => __( 'Risk Matrix', 'ai-risk-benchmark' ),
					'headers' => array(
						__( 'Risk', 'ai-risk-benchmark' ),
						__( 'Likelihood', 'ai-risk-benchmark' ),
						__( 'Impact', 'ai-risk-benchmark' ),
						__( 'Owner', 'ai-risk-benchmark' ),
						__( 'Review Date', 'ai-risk-benchmark' ),
					),
					'rows'    => array(
						array(
							__( 'Data Protection', 'ai-risk-benchmark' ),
							__( 'Medium', 'ai-risk-benchmark' ),
							__( 'High', 'ai-risk-benchmark' ),
							__( 'DPO', 'ai-risk-benchmark' ),
							__( 'Annual', 'ai-risk-benchmark' ),
						),
						array(
							__( 'Deepfakes', 'ai-risk-benchmark' ),
							__( 'Medium', 'ai-risk-benchmark' ),
							__( 'Medium', 'ai-risk-benchmark' ),
							__( 'DSL', 'ai-risk-benchmark' ),
							__( 'Annual', 'ai-risk-benchmark' ),
						),
						array(
							__( 'Assessment Integrity', 'ai-risk-benchmark' ),
							__( 'High', 'ai-risk-benchmark' ),
							__( 'High', 'ai-risk-benchmark' ),
							__( 'Assessment Lead', 'ai-risk-benchmark' ),
							__( 'Annual', 'ai-risk-benchmark' ),
						),
					),
				),
				'post_sections'       => array(
					array(
						'heading' => __( 'Review Questions', 'ai-risk-benchmark' ),
						'items'   => array(
							__( 'Has the risk changed?', 'ai-risk-benchmark' ),
							__( 'Are controls effective?', 'ai-risk-benchmark' ),
							__( 'Has training been completed?', 'ai-risk-benchmark' ),
							__( 'Are new risks emerging?', 'ai-risk-benchmark' ),
						),
					),
				),
				'benchmark'           => array(
					'label' => __( 'Retake the leader benchmark after updating your risk register', 'ai-risk-benchmark' ),
					'ref'   => 'governance',
				),
				'benchmark_supports'  => __( 'Supports:', 'ai-risk-benchmark' ),
				'benchmark_improves'  => array(
					__( 'Governance', 'ai-risk-benchmark' ),
					__( 'Privacy', 'ai-risk-benchmark' ),
					__( 'Safeguarding', 'ai-risk-benchmark' ),
					__( 'Assessment Integrity', 'ai-risk-benchmark' ),
				),
				'closing'             => array(
					__( 'Effective risk management is continuous, not one-off.', 'ai-risk-benchmark' ),
				),
			),
			'dfe-ai-compliance-checklist'       => array(
				'headline'            => __( 'DfE AI Compliance Checklist', 'ai-risk-benchmark' ),
				'subtitle'            => __( 'A Practical Review Framework For School Leaders', 'ai-risk-benchmark' ),
				'why_heading'         => __( 'Important', 'ai-risk-benchmark' ),
				'why_paragraphs'      => array(
					__( 'This checklist is designed to support reflection and planning.', 'ai-risk-benchmark' ),
					__( 'It is not a compliance certification and does not replace legal, safeguarding or data protection advice.', 'ai-risk-benchmark' ),
				),
				'checklist_sections'  => array(
					array(
						'heading'     => __( 'Governance', 'ai-risk-benchmark' ),
						'subsections' => array(
							array(
								'title' => __( 'Leadership', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Named AI Lead identified', 'ai-risk-benchmark' ),
									__( 'Senior leadership oversight established', 'ai-risk-benchmark' ),
									__( 'Governors informed of AI approach', 'ai-risk-benchmark' ),
								),
							),
							array(
								'title' => __( 'Policy', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'AI policy in place', 'ai-risk-benchmark' ),
									__( 'Policy reviewed annually', 'ai-risk-benchmark' ),
									__( 'Staff expectations documented', 'ai-risk-benchmark' ),
									__( 'Student expectations documented', 'ai-risk-benchmark' ),
								),
							),
						),
					),
					array(
						'heading'     => __( 'Safe Adoption', 'ai-risk-benchmark' ),
						'subsections' => array(
							array(
								'title' => __( 'Tool Approval', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Approved AI tools identified', 'ai-risk-benchmark' ),
									__( 'Risk assessment completed before adoption', 'ai-risk-benchmark' ),
									__( 'Staff know which tools are permitted', 'ai-risk-benchmark' ),
								),
							),
							array(
								'title' => __( 'Human Oversight', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Verification expectations documented', 'ai-risk-benchmark' ),
									__( 'Professional accountability emphasised', 'ai-risk-benchmark' ),
									__( 'Staff understand limitations of AI outputs', 'ai-risk-benchmark' ),
								),
							),
						),
					),
					array(
						'heading'     => __( 'Data Protection', 'ai-risk-benchmark' ),
						'subsections' => array(
							array(
								'title' => __( 'Privacy', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Staff understand personal data risks', 'ai-risk-benchmark' ),
									__( 'Guidance issued on pupil information', 'ai-risk-benchmark' ),
									__( 'Approved tools reviewed for privacy implications', 'ai-risk-benchmark' ),
								),
							),
							array(
								'title' => __( 'Review', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Data protection implications considered', 'ai-risk-benchmark' ),
									__( 'Appropriate assessments completed where required', 'ai-risk-benchmark' ),
								),
							),
						),
					),
					array(
						'heading'     => __( 'Safeguarding', 'ai-risk-benchmark' ),
						'subsections' => array(
							array(
								'title' => __( 'Online Safety', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'AI risks included in safeguarding reviews', 'ai-risk-benchmark' ),
									__( 'Deepfake risks discussed', 'ai-risk-benchmark' ),
									__( 'Reporting routes understood', 'ai-risk-benchmark' ),
								),
							),
							array(
								'title' => __( 'Staff Awareness', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Staff trained on emerging AI risks', 'ai-risk-benchmark' ),
									__( 'Pupils receive age-appropriate guidance', 'ai-risk-benchmark' ),
								),
							),
						),
					),
					array(
						'heading'     => __( 'Assessment Integrity', 'ai-risk-benchmark' ),
						'subsections' => array(
							array(
								'title' => __( 'Staff', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Relevant staff understand assessment expectations', 'ai-risk-benchmark' ),
									__( 'Internal guidance provided', 'ai-risk-benchmark' ),
								),
							),
							array(
								'title' => __( 'Pupils', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Expectations communicated clearly', 'ai-risk-benchmark' ),
									__( 'Academic honesty discussed', 'ai-risk-benchmark' ),
								),
							),
						),
					),
					array(
						'heading'     => __( 'Workforce Readiness', 'ai-risk-benchmark' ),
						'subsections' => array(
							array(
								'title' => __( 'Training', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Staff training completed', 'ai-risk-benchmark' ),
									__( 'New staff receive guidance', 'ai-risk-benchmark' ),
									__( 'Updates provided annually', 'ai-risk-benchmark' ),
								),
							),
							array(
								'title' => __( 'Support', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Staff know where to seek help', 'ai-risk-benchmark' ),
									__( 'Resources available', 'ai-risk-benchmark' ),
								),
							),
						),
					),
					array(
						'heading'     => __( 'School Culture', 'ai-risk-benchmark' ),
						'subsections' => array(
							array(
								'title' => __( 'Responsible Use', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'AI supports learning and teaching', 'ai-risk-benchmark' ),
									__( 'Human judgement remains central', 'ai-risk-benchmark' ),
									__( 'Continuous improvement encouraged', 'ai-risk-benchmark' ),
								),
							),
						),
					),
				),
				'post_sections'       => array(
					array(
						'heading' => __( 'Traffic Light Summary', 'ai-risk-benchmark' ),
						'blocks'  => array(
							array(
								'title' => __( 'Green', 'ai-risk-benchmark' ),
								'body'  => __( 'Most controls established and operating.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Amber', 'ai-risk-benchmark' ),
								'body'  => __( 'Some controls established but gaps remain.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Red', 'ai-risk-benchmark' ),
								'body'  => __( 'Significant work required before AI adoption can be considered mature.', 'ai-risk-benchmark' ),
							),
						),
					),
					array(
						'heading' => __( 'Recommended Next Steps', 'ai-risk-benchmark' ),
						'blocks'  => array(
							array(
								'body' => __( 'If Governance scores are low:', 'ai-risk-benchmark' ),
								'link' => array(
									'label' => __( 'School AI Policy: DfE templates & support', 'ai-risk-benchmark' ),
									'path'  => 'ai-policy-generator',
								),
							),
							array(
								'body' => __( 'If Human Oversight scores are low:', 'ai-risk-benchmark' ),
								'link' => array(
									'label' => __( 'Teacher AI Verification Framework', 'ai-risk-benchmark' ),
									'path'  => 'teacher-ai-verification-framework',
								),
							),
							array(
								'body' => __( 'If Safeguarding scores are low:', 'ai-risk-benchmark' ),
								'link' => array(
									'label' => __( 'Parent Deepfake Awareness', 'ai-risk-benchmark' ),
									'path'  => 'parent-deepfake-awareness',
								),
							),
							array(
								'body' => __( 'If Readiness scores are low:', 'ai-risk-benchmark' ),
								'link' => array(
									'label' => __( 'AI Awareness Day', 'ai-risk-benchmark' ),
									'path'  => 'ai-awareness-day',
								),
							),
						),
					),
				),
				'download'            => array(
					'label' => __( 'DfE AI Compliance Checklist (PDF — add download in editor)', 'ai-risk-benchmark' ),
					'note'  => __( 'RAG scoring template for SLT and governors.', 'ai-risk-benchmark' ),
				),
				'benchmark'           => array(
					'label' => __( 'Retake the leader benchmark after completing this review', 'ai-risk-benchmark' ),
					'ref'   => 'governance',
				),
				'benchmark_supports'  => __( 'This checklist supports:', 'ai-risk-benchmark' ),
				'benchmark_improves'  => array(
					__( 'Governance', 'ai-risk-benchmark' ),
					__( 'Safe Adoption', 'ai-risk-benchmark' ),
					__( 'Workforce Readiness', 'ai-risk-benchmark' ),
					__( 'Assessment Integrity', 'ai-risk-benchmark' ),
					__( 'Safeguarding', 'ai-risk-benchmark' ),
				),
				'closing'             => array(
					__( 'Review annually and after significant changes to AI use within the school.', 'ai-risk-benchmark' ),
				),
			),
			'ai-champion-programme'           => array(
				'headline'            => __( 'AI Champion Programme', 'ai-risk-benchmark' ),
				'subtitle'            => __( 'Building Internal AI Leadership', 'ai-risk-benchmark' ),
				'why_heading'         => __( 'Why This Matters', 'ai-risk-benchmark' ),
				'why_paragraphs'      => array(
					__( 'Most schools do not need AI experts.', 'ai-risk-benchmark' ),
					__( 'They need trusted colleagues who can:', 'ai-risk-benchmark' ),
				),
				'why_list'            => array(
					__( 'Model good practice', 'ai-risk-benchmark' ),
					__( 'Support staff', 'ai-risk-benchmark' ),
					__( 'Share learning', 'ai-risk-benchmark' ),
					__( 'Promote responsible AI use', 'ai-risk-benchmark' ),
				),
				'why_after'           => array(
					__( 'The AI Champion Programme helps schools build capability from within.', 'ai-risk-benchmark' ),
				),
				'risk_categories'     => array(
					array(
						'heading' => __( 'Level 1: AI Aware', 'ai-risk-benchmark' ),
						'fields'  => array(
							array(
								'label' => __( 'Focus', 'ai-risk-benchmark' ),
								'body'  => __( 'Understanding fundamentals.', 'ai-risk-benchmark' ),
							),
							array(
								'label' => __( 'Expectations', 'ai-risk-benchmark' ),
								'body'  => __( 'Can explain:', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'What AI is', 'ai-risk-benchmark' ),
									__( 'Common benefits', 'ai-risk-benchmark' ),
									__( 'Common risks', 'ai-risk-benchmark' ),
								),
							),
							array(
								'label' => __( 'Evidence', 'ai-risk-benchmark' ),
								'body'  => __( 'Completion of benchmark and awareness training.', 'ai-risk-benchmark' ),
							),
						),
					),
					array(
						'heading' => __( 'Level 2: Verification Ready', 'ai-risk-benchmark' ),
						'fields'  => array(
							array(
								'label' => __( 'Focus', 'ai-risk-benchmark' ),
								'body'  => __( 'Human oversight.', 'ai-risk-benchmark' ),
							),
							array(
								'label' => __( 'Expectations', 'ai-risk-benchmark' ),
								'body'  => __( 'Uses:', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Verify Before You Trust™', 'ai-risk-benchmark' ),
									__( 'Fact-checking techniques', 'ai-risk-benchmark' ),
									__( 'Professional review processes', 'ai-risk-benchmark' ),
								),
							),
							array(
								'label' => __( 'Evidence', 'ai-risk-benchmark' ),
								'body'  => __( 'Strong Human Oversight score.', 'ai-risk-benchmark' ),
							),
						),
					),
					array(
						'heading' => __( 'Level 3: Responsible AI Practitioner', 'ai-risk-benchmark' ),
						'fields'  => array(
							array(
								'label' => __( 'Focus', 'ai-risk-benchmark' ),
								'body'  => __( 'Classroom implementation.', 'ai-risk-benchmark' ),
							),
							array(
								'label' => __( 'Expectations', 'ai-risk-benchmark' ),
								'body'  => __( 'Uses AI responsibly and models good practice. Supports colleagues where appropriate.', 'ai-risk-benchmark' ),
							),
							array(
								'label' => __( 'Evidence', 'ai-risk-benchmark' ),
								'body'  => __( 'Benchmark improvement over time.', 'ai-risk-benchmark' ),
							),
						),
					),
					array(
						'heading' => __( 'Level 4: AI Champion', 'ai-risk-benchmark' ),
						'fields'  => array(
							array(
								'label' => __( 'Focus', 'ai-risk-benchmark' ),
								'body'  => __( 'Whole-school influence.', 'ai-risk-benchmark' ),
							),
							array(
								'label' => __( 'Expectations', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Shares practice', 'ai-risk-benchmark' ),
									__( 'Supports training', 'ai-risk-benchmark' ),
									__( 'Contributes to policy discussions', 'ai-risk-benchmark' ),
								),
							),
							array(
								'label' => __( 'Evidence', 'ai-risk-benchmark' ),
								'body'  => __( 'Participation in school AI initiatives.', 'ai-risk-benchmark' ),
							),
						),
					),
					array(
						'heading' => __( 'Level 5: School AI Lead', 'ai-risk-benchmark' ),
						'fields'  => array(
							array(
								'label' => __( 'Focus', 'ai-risk-benchmark' ),
								'body'  => __( 'Strategic leadership.', 'ai-risk-benchmark' ),
							),
							array(
								'label' => __( 'Expectations', 'ai-risk-benchmark' ),
								'body'  => __( 'Supports:', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Governance', 'ai-risk-benchmark' ),
									__( 'Policy review', 'ai-risk-benchmark' ),
									__( 'Staff development', 'ai-risk-benchmark' ),
									__( 'Benchmark analysis', 'ai-risk-benchmark' ),
								),
							),
							array(
								'label' => __( 'Evidence', 'ai-risk-benchmark' ),
								'body'  => __( 'Leadership endorsement.', 'ai-risk-benchmark' ),
							),
						),
					),
				),
				'post_sections'       => array(
					array(
						'heading' => __( 'Champion Responsibilities', 'ai-risk-benchmark' ),
						'intro'   => __( 'Champions should:', 'ai-risk-benchmark' ),
						'items'   => array(
							__( 'Promote responsible AI use', 'ai-risk-benchmark' ),
							__( 'Encourage verification', 'ai-risk-benchmark' ),
							__( 'Share resources', 'ai-risk-benchmark' ),
							__( 'Support colleagues', 'ai-risk-benchmark' ),
							__( 'Model professional judgement', 'ai-risk-benchmark' ),
						),
						'after'   => __( 'They should not be expected to become technical experts.', 'ai-risk-benchmark' ),
					),
					array(
						'heading' => __( 'Benefits', 'ai-risk-benchmark' ),
						'blocks'  => array(
							array(
								'title' => __( 'For Staff', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Professional growth', 'ai-risk-benchmark' ),
									__( 'Leadership opportunities', 'ai-risk-benchmark' ),
								),
							),
							array(
								'title' => __( 'For Schools', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Internal expertise', 'ai-risk-benchmark' ),
									__( 'Reduced dependency on external support', 'ai-risk-benchmark' ),
									__( 'Improved consistency', 'ai-risk-benchmark' ),
								),
							),
						),
					),
				),
				'benchmark'           => array(
					'label' => __( 'Retake the teacher benchmark to track your progression', 'ai-risk-benchmark' ),
					'ref'   => 'human_oversight',
				),
				'benchmark_supports'  => __( 'Supports:', 'ai-risk-benchmark' ),
				'benchmark_improves'  => array(
					__( 'Human Oversight', 'ai-risk-benchmark' ),
					__( 'AI Literacy', 'ai-risk-benchmark' ),
					__( 'Staff Readiness', 'ai-risk-benchmark' ),
					__( 'Governance', 'ai-risk-benchmark' ),
				),
				'closing'             => array(
					__( 'The goal is not to create AI experts.', 'ai-risk-benchmark' ),
					__( 'The goal is to create confident, informed and responsible practitioners.', 'ai-risk-benchmark' ),
				),
			),
			'annual-benchmark-review'         => array(
				'headline'            => __( 'Annual AI Benchmark Review', 'ai-risk-benchmark' ),
				'intro'               => __( 'Measure improvement across your school community — compare scores year-on-year and evidence progress to governors.', 'ai-risk-benchmark' ),
				'purpose'             => __( 'Measure improvement.', 'ai-risk-benchmark' ),
				'why_heading'         => __( 'Why this matters', 'ai-risk-benchmark' ),
				'why_body'            => __( 'AI tools and behaviours change faster than policy cycles. An annual benchmark gives a defensible baseline — and proof that interventions worked. Aligns with DfE expectation that schools review policies and practice regularly.', 'ai-risk-benchmark' ),
				'framework_heading'   => __( 'Annual review cycle', 'ai-risk-benchmark' ),
				'steps'               => array(
					array(
						'title' => __( 'Compare', 'ai-risk-benchmark' ),
						'body'  => '',
						'items' => array(
							__( 'Teacher scores', 'ai-risk-benchmark' ),
							__( 'Student scores', 'ai-risk-benchmark' ),
							__( 'Parent scores', 'ai-risk-benchmark' ),
							__( 'Leadership scores', 'ai-risk-benchmark' ),
						),
					),
					array(
						'title' => __( 'Review', 'ai-risk-benchmark' ),
						'body'  => '',
						'items' => array(
							__( 'New DfE guidance', 'ai-risk-benchmark' ),
							__( 'New risks (e.g. deepfakes, new tools)', 'ai-risk-benchmark' ),
							__( 'Policy and register updates', 'ai-risk-benchmark' ),
						),
					),
					array(
						'title' => __( 'Report', 'ai-risk-benchmark' ),
						'body'  => __( 'Governor-ready summary: readiness up, dependency risk down, weak domains closed, interventions completed.', 'ai-risk-benchmark' ),
					),
				),
				'download'            => array(
					'label' => __( 'Governor report template (PDF — add download in editor)', 'ai-risk-benchmark' ),
					'note'  => __( 'One page: scores by role, year-on-year delta, actions taken, next-year priorities.', 'ai-risk-benchmark' ),
				),
				'training'            => array(
					'label' => __( 'AI Awareness Day — whole-school programme', 'ai-risk-benchmark' ),
					'path'  => 'ai-awareness-day',
				),
				'benchmark'           => array(
					'label' => __( 'Start whole-school benchmark cycle', 'ai-risk-benchmark' ),
					'ref'   => 'governance',
				),
				'benchmark_improves'  => array(
					__( 'Whole-school DfE Alignment', 'ai-risk-benchmark' ),
					__( 'Governance Maturity', 'ai-risk-benchmark' ),
				),
			),
			'national-benchmark-report'       => array(
				'headline'            => __( 'UK School AI Benchmark Report', 'ai-risk-benchmark' ),
				'subtitle'            => __( 'Understanding AI Adoption Across Schools', 'ai-risk-benchmark' ),
				'why_heading'         => __( 'About This Report', 'ai-risk-benchmark' ),
				'why_paragraphs'      => array(
					__( 'The UK School AI Benchmark Report brings together anonymised benchmark data from participating schools.', 'ai-risk-benchmark' ),
					__( 'The purpose is to help schools understand:', 'ai-risk-benchmark' ),
				),
				'why_list'            => array(
					__( 'National trends', 'ai-risk-benchmark' ),
					__( 'Emerging risks', 'ai-risk-benchmark' ),
					__( 'Areas of strength', 'ai-risk-benchmark' ),
					__( 'Areas requiring attention', 'ai-risk-benchmark' ),
				),
				'why_after'           => array(
					__( 'No individual school is identified.', 'ai-risk-benchmark' ),
					__( 'The report is intended to support reflection and improvement.', 'ai-risk-benchmark' ),
				),
				'post_sections'       => array(
					array(
						'heading' => __( 'Executive Summary', 'ai-risk-benchmark' ),
						'blocks'  => array(
							array(
								'title' => __( 'Key Findings', 'ai-risk-benchmark' ),
								'body'  => __( 'Based on benchmark responses from:', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Teachers', 'ai-risk-benchmark' ),
									__( 'Students', 'ai-risk-benchmark' ),
									__( 'Parents', 'ai-risk-benchmark' ),
									__( 'School Leaders', 'ai-risk-benchmark' ),
								),
								'after' => __( 'Across participating schools.', 'ai-risk-benchmark' ),
							),
						),
					),
					array(
						'heading' => __( 'National Trends', 'ai-risk-benchmark' ),
						'blocks'  => array(
							array(
								'title' => __( 'Teacher Human Oversight', 'ai-risk-benchmark' ),
								'body'  => __( 'Average score: XX%', 'ai-risk-benchmark' ),
								'after' => __( 'Key finding: Teachers generally understand AI benefits but verification behaviours remain inconsistent.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Student AI Dependency', 'ai-risk-benchmark' ),
								'body'  => __( 'Average score: XX%', 'ai-risk-benchmark' ),
								'after' => __( 'Key finding: Many students report using AI before attempting work independently.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Parent AI Awareness', 'ai-risk-benchmark' ),
								'body'  => __( 'Average score: XX%', 'ai-risk-benchmark' ),
								'after' => __( 'Key finding: Parents often understand online safety but feel less confident discussing AI specifically.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Leadership Governance', 'ai-risk-benchmark' ),
								'body'  => __( 'Average score: XX%', 'ai-risk-benchmark' ),
								'after' => __( 'Key finding: Many schools are developing policies but governance maturity varies significantly.', 'ai-risk-benchmark' ),
							),
						),
					),
					array(
						'heading' => __( 'Top Risks Identified', 'ai-risk-benchmark' ),
						'blocks'  => array(
							array(
								'title' => __( '1. AI Dependency', 'ai-risk-benchmark' ),
								'body'  => __( 'Over-reliance on AI without sufficient independent thinking.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( '2. Human Oversight', 'ai-risk-benchmark' ),
								'body'  => __( 'Insufficient verification of AI-generated outputs.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( '3. Privacy', 'ai-risk-benchmark' ),
								'body'  => __( 'Uncertainty about what information can safely be entered into AI systems.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( '4. Deepfake Awareness', 'ai-risk-benchmark' ),
								'body'  => __( 'Limited understanding of AI-generated manipulation.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( '5. Assessment Integrity', 'ai-risk-benchmark' ),
								'body'  => __( 'Growing concerns around responsible use in learning and assessment.', 'ai-risk-benchmark' ),
							),
						),
					),
					array(
						'heading' => __( 'Benchmark Comparisons', 'ai-risk-benchmark' ),
						'blocks'  => array(
							array(
								'title' => __( 'Primary Schools', 'ai-risk-benchmark' ),
								'body'  => __( 'Average readiness: XX%', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Secondary Schools', 'ai-risk-benchmark' ),
								'body'  => __( 'Average readiness: XX%', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Standalone Schools', 'ai-risk-benchmark' ),
								'body'  => __( 'Average readiness: XX%', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Multi-Academy Trusts', 'ai-risk-benchmark' ),
								'body'  => __( 'Average readiness: XX%', 'ai-risk-benchmark' ),
							),
						),
					),
					array(
						'heading' => __( 'What High-Performing Schools Do Differently', 'ai-risk-benchmark' ),
						'intro'   => __( 'Schools with stronger benchmark scores typically:', 'ai-risk-benchmark' ),
						'items'   => array(
							__( 'Have clear policies', 'ai-risk-benchmark' ),
							__( 'Provide staff training', 'ai-risk-benchmark' ),
							__( 'Discuss AI openly', 'ai-risk-benchmark' ),
							__( 'Promote verification', 'ai-risk-benchmark' ),
							__( 'Review AI annually', 'ai-risk-benchmark' ),
						),
					),
					array(
						'heading' => __( 'Emerging Themes', 'ai-risk-benchmark' ),
						'blocks'  => array(
							array(
								'title' => __( 'Human Behaviour Matters More Than Technology', 'ai-risk-benchmark' ),
								'body'  => __( 'The strongest predictor of readiness is not access to tools.', 'ai-risk-benchmark' ),
							),
							array(
								'body' => __( 'It is behaviour.', 'ai-risk-benchmark' ),
							),
							array(
								'body'  => __( 'Schools that focus on:', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Human oversight', 'ai-risk-benchmark' ),
									__( 'Verification', 'ai-risk-benchmark' ),
									__( 'Critical thinking', 'ai-risk-benchmark' ),
								),
							),
							array(
								'body' => __( 'perform better than schools focused solely on technology adoption.', 'ai-risk-benchmark' ),
							),
						),
					),
					array(
						'heading' => __( 'Recommendations', 'ai-risk-benchmark' ),
						'blocks'  => array(
							array(
								'title' => __( 'Leaders', 'ai-risk-benchmark' ),
								'body'  => __( 'Review governance annually.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Teachers', 'ai-risk-benchmark' ),
								'body'  => __( 'Use Verify Before You Trust™.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Students', 'ai-risk-benchmark' ),
								'body'  => __( 'Use Think First, Prompt Second™.', 'ai-risk-benchmark' ),
							),
							array(
								'title' => __( 'Parents', 'ai-risk-benchmark' ),
								'body'  => __( 'Discuss AI regularly at home.', 'ai-risk-benchmark' ),
							),
						),
					),
					array(
						'heading' => __( 'Looking Ahead', 'ai-risk-benchmark' ),
						'blocks'  => array(
							array(
								'body' => __( 'AI will continue to evolve.', 'ai-risk-benchmark' ),
							),
							array(
								'body' => __( 'The challenge for schools is not whether AI will be used.', 'ai-risk-benchmark' ),
							),
							array(
								'body' => __( 'The challenge is ensuring it is used responsibly.', 'ai-risk-benchmark' ),
							),
						),
					),
				),
				'benchmark_heading'   => __( 'Contribute To The Next Report', 'ai-risk-benchmark' ),
				'benchmark_intro'     => __( 'Complete the AI Risk & Readiness Benchmark™ and help build a clearer picture of AI adoption across UK schools.', 'ai-risk-benchmark' ),
				'benchmark'           => array(
					'label' => __( 'Take the benchmark — contribute to national data', 'ai-risk-benchmark' ),
				),
				'closing'             => array(
					__( 'Together we can move from assumptions to evidence.', 'ai-risk-benchmark' ),
				),
			),
			'ai-awareness-day'                => array(
				'headline'            => __( 'AI Awareness Day Framework', 'ai-risk-benchmark' ),
				'guidance_alignment'  => false,
				'post_sections'       => array(
					array(
						'heading' => __( 'Audience', 'ai-risk-benchmark' ),
						'items'   => array(
							__( 'School Leaders', 'ai-risk-benchmark' ),
							__( 'Trust Leaders', 'ai-risk-benchmark' ),
							__( 'Governors', 'ai-risk-benchmark' ),
						),
					),
					array(
						'heading' => __( 'Structure', 'ai-risk-benchmark' ),
						'blocks'  => array(
							array(
								'title'       => __( 'Morning', 'ai-risk-benchmark' ),
								'subsections' => array(
									array(
										'title' => __( 'Leadership Session', 'ai-risk-benchmark' ),
										'items' => array(
											__( 'Governance', 'ai-risk-benchmark' ),
											__( 'DfE Guidance', 'ai-risk-benchmark' ),
											__( 'Risk Management', 'ai-risk-benchmark' ),
											__( 'Policy', 'ai-risk-benchmark' ),
										),
									),
								),
							),
							array(
								'title' => __( 'Teacher Session', 'ai-risk-benchmark' ),
								'items' => array(
									__( 'Human Oversight', 'ai-risk-benchmark' ),
									__( 'Verification', 'ai-risk-benchmark' ),
									__( 'Privacy', 'ai-risk-benchmark' ),
									__( 'Assessment Integrity', 'ai-risk-benchmark' ),
								),
							),
							array(
								'title'       => __( 'Afternoon', 'ai-risk-benchmark' ),
								'subsections' => array(
									array(
										'title' => __( 'Student Session', 'ai-risk-benchmark' ),
										'items' => array(
											__( 'AI Literacy', 'ai-risk-benchmark' ),
											__( 'Critical Thinking', 'ai-risk-benchmark' ),
											__( 'Deepfakes', 'ai-risk-benchmark' ),
											__( 'Responsible Use', 'ai-risk-benchmark' ),
										),
									),
								),
							),
							array(
								'title'       => __( 'Evening', 'ai-risk-benchmark' ),
								'subsections' => array(
									array(
										'title' => __( 'Parent Session', 'ai-risk-benchmark' ),
										'items' => array(
											__( 'AI at Home', 'ai-risk-benchmark' ),
											__( 'Privacy', 'ai-risk-benchmark' ),
											__( 'Homework', 'ai-risk-benchmark' ),
											__( 'Online Safety', 'ai-risk-benchmark' ),
										),
									),
								),
							),
						),
					),
					array(
						'heading' => __( 'Outcomes', 'ai-risk-benchmark' ),
						'intro'   => __( 'Schools receive:', 'ai-risk-benchmark' ),
						'items'   => array(
							__( 'Whole-school benchmark', 'ai-risk-benchmark' ),
							__( 'Leadership report', 'ai-risk-benchmark' ),
							__( 'AI readiness action plan', 'ai-risk-benchmark' ),
							__( 'Governance recommendations', 'ai-risk-benchmark' ),
							__( 'Annual review framework', 'ai-risk-benchmark' ),
						),
					),
					array(
						'heading' => __( 'Positioning', 'ai-risk-benchmark' ),
						'blocks'  => array(
							array(
								'body' => __( 'AI Awareness Day is not about teaching people how to use AI.', 'ai-risk-benchmark' ),
							),
							array(
								'body' => __( 'It is about helping schools understand how to use AI safely, responsibly and effectively.', 'ai-risk-benchmark' ),
							),
						),
					),
				),
				'training'            => array(
					'heading' => __( 'Book AI Awareness Day', 'ai-risk-benchmark' ),
					'label'   => __( 'Contact us to plan your whole-school programme', 'ai-risk-benchmark' ),
					'path'    => 'contact',
				),
				'benchmark_heading'   => __( 'Start With The Benchmark', 'ai-risk-benchmark' ),
				'benchmark_intro'     => __( 'AI Awareness Day works best when built on your school\'s benchmark data — leadership reports, readiness scores and governance priorities.', 'ai-risk-benchmark' ),
				'benchmark'           => array(
					'label' => __( 'Take the whole-school AI Risk & Readiness Benchmark™', 'ai-risk-benchmark' ),
				),
				'support'             => array(
					'heading' => __( 'Wider Support & Development', 'ai-risk-benchmark' ),
					'intro'   => __( 'Each area below can be delivered as a standalone intervention shaped by your benchmark results. For more information, guidance and support, contact the AI Awareness team.', 'ai-risk-benchmark' ),
					'items'   => array(
						array( 'label' => __( 'Governance Review', 'ai-risk-benchmark' ) ),
						array( 'label' => __( 'AI Policy Development', 'ai-risk-benchmark' ) ),
						array( 'label' => __( 'Staff Training', 'ai-risk-benchmark' ) ),
						array( 'label' => __( 'Parent Session', 'ai-risk-benchmark' ) ),
						array( 'label' => __( 'Student Session', 'ai-risk-benchmark' ) ),
						array( 'label' => __( 'AI Awareness Day', 'ai-risk-benchmark' ) ),
					),
				),
			),
			'school-ai-maturity'              => array(
				'headline'            => __( 'School AI Maturity Framework', 'ai-risk-benchmark' ),
				'intro'               => __( 'Translate your DfE Alignment Score into a maturity band — and a prioritised set of interventions for governors and SLT.', 'ai-risk-benchmark' ),
				'purpose'             => __( 'Turn benchmark scores into a leadership narrative.', 'ai-risk-benchmark' ),
				'why_heading'         => __( 'Why this matters', 'ai-risk-benchmark' ),
				'why_body'            => __( 'Governors, trusts and Ofsted ask for evidence of responsible AI adoption — not a single percentage in isolation. Maturity bands link benchmark data to action.', 'ai-risk-benchmark' ),
				'framework_heading'   => __( 'Maturity bands', 'ai-risk-benchmark' ),
				'steps'               => array(
					array(
						'title' => __( '0–25 — Emerging', 'ai-risk-benchmark' ),
						'body'  => __( 'Ad hoc AI use; limited governance. Priorities: adapt the DfE AI policy template, DfE Compliance Checklist, leader benchmark.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( '26–50 — Developing', 'ai-risk-benchmark' ),
						'body'  => __( 'Policy in progress; uneven practice. Priorities: School AI Governance Framework, AI Risk Register, staff verification CPD.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( '51–75 — Established', 'ai-risk-benchmark' ),
						'body'  => __( 'Documented controls; improving awareness. Priorities: Annual Benchmark Review, parent sessions, safeguarding refresh.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( '76–100 — Leading', 'ai-risk-benchmark' ),
						'body'  => __( 'Evidence-led culture; annual review cycle. Priorities: national benchmark data, AI Champion Programme, MAT roll-up.', 'ai-risk-benchmark' ),
					),
				),
				'download'            => array(
					'label' => __( 'Governor maturity report template (PDF — add download in editor)', 'ai-risk-benchmark' ),
					'note'  => __( 'One page: maturity band, scores by role, interventions completed, next-year priorities.', 'ai-risk-benchmark' ),
				),
				'training'            => array(
					'label' => __( 'Book a free AI Readiness Review', 'ai-risk-benchmark' ),
					'path'  => 'contact',
				),
				'benchmark'           => array(
					'label' => __( 'Retake leader benchmark', 'ai-risk-benchmark' ),
					'ref'   => 'governance',
				),
				'benchmark_improves'  => array(
					__( 'DfE Alignment Score', 'ai-risk-benchmark' ),
					__( 'Governance Maturity', 'ai-risk-benchmark' ),
				),
			),
			'teacher-ai-lesson-planning-checklist' => array(
				'headline'           => __( 'AI Lesson Planning Checklist', 'ai-risk-benchmark' ),
				'subtitle'           => __( 'Plan With AI Without Losing Professional Judgement', 'ai-risk-benchmark' ),
				'why_heading'        => __( 'Why This Matters', 'ai-risk-benchmark' ),
				'why_paragraphs'     => array(
					__( 'AI can produce a lesson plan in seconds — but a plan that looks polished is not the same as a plan that is accurate, age-appropriate and right for your class.', 'ai-risk-benchmark' ),
					__( 'You remain professionally accountable for everything you teach. This checklist helps you use AI to plan faster while keeping your own judgement at the centre.', 'ai-risk-benchmark' ),
				),
				'framework_heading'  => __( 'Plan in Four Moves', 'ai-risk-benchmark' ),
				'steps'              => array(
					array(
						'title' => __( 'Move 1: Define the Learning First', 'ai-risk-benchmark' ),
						'body'  => __( 'Before prompting, decide:', 'ai-risk-benchmark' ),
						'items' => array(
							__( 'The intended learning outcome', 'ai-risk-benchmark' ),
							__( 'Prior knowledge pupils bring', 'ai-risk-benchmark' ),
							__( 'Common misconceptions to address', 'ai-risk-benchmark' ),
							__( 'How you will check understanding', 'ai-risk-benchmark' ),
						),
						'after' => __( 'AI cannot know your class. You do.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Move 2: Prompt With Context', 'ai-risk-benchmark' ),
						'body'  => __( 'Give the tool what it needs — year group, ability range, time available, curriculum reference — without entering pupil names or personal data.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Move 3: Adapt, Don\'t Adopt', 'ai-risk-benchmark' ),
						'body'  => __( 'Treat the output as a first draft. Adjust examples, language and pace for your pupils, and check it against your scheme of work.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Move 4: Verify Before You Teach', 'ai-risk-benchmark' ),
						'body'  => __( 'Check facts, dates, sources and worked examples. AI can be confidently wrong.', 'ai-risk-benchmark' ),
					),
				),
				'checklist'          => array(
					'heading' => __( 'Before You Use an AI-Generated Plan', 'ai-risk-benchmark' ),
					'intro'   => __( 'Confirm each point:', 'ai-risk-benchmark' ),
					'items'   => array(
						__( 'The learning outcome is clear and correct', 'ai-risk-benchmark' ),
						__( 'Content is accurate and current', 'ai-risk-benchmark' ),
						__( 'Language and examples suit my pupils', 'ai-risk-benchmark' ),
						__( 'Misconceptions and SEND needs are considered', 'ai-risk-benchmark' ),
						__( 'No pupil or personal data was entered into the tool', 'ai-risk-benchmark' ),
						__( 'I can confidently justify this plan to a colleague or parent', 'ai-risk-benchmark' ),
					),
				),
				'download'           => array(
					'label' => __( 'AI Lesson Planning Checklist (PDF — add download in editor)', 'ai-risk-benchmark' ),
					'note'  => __( 'Printable one-pager for planning files and department meetings.', 'ai-risk-benchmark' ),
				),
				'training'           => array(
					'label' => __( 'AI Awareness Day Teacher Session', 'ai-risk-benchmark' ),
					'path'  => 'ai-awareness-day',
				),
				'benchmark'          => array(
					'label' => __( 'Retake the teacher benchmark after applying this checklist', 'ai-risk-benchmark' ),
					'ref'   => 'human_oversight',
				),
				'benchmark_improves' => array(
					__( 'Human Oversight', 'ai-risk-benchmark' ),
					__( 'AI Literacy', 'ai-risk-benchmark' ),
					__( 'Safe Adoption', 'ai-risk-benchmark' ),
				),
				'closing'            => array(
					__( 'Use AI to save time on the first draft — never on the professional judgement.', 'ai-risk-benchmark' ),
				),
			),
			'teacher-ai-privacy-guide'          => array(
				'headline'           => __( 'Teacher AI Privacy Guide', 'ai-risk-benchmark' ),
				'subtitle'           => __( 'Protecting Pupil Data When Using AI Tools', 'ai-risk-benchmark' ),
				'why_heading'        => __( 'Why This Matters', 'ai-risk-benchmark' ),
				'why_paragraphs'     => array(
					__( 'Most public AI tools store and may reuse what you type. Entering identifiable pupil information can breach data protection law and create safeguarding risk.', 'ai-risk-benchmark' ),
					__( 'This guide aligns to Information Commissioner\'s Office (ICO) expectations and your school\'s data protection responsibilities.', 'ai-risk-benchmark' ),
				),
				'framework_heading'  => __( 'The Golden Rules', 'ai-risk-benchmark' ),
				'steps'              => array(
					array(
						'title' => __( 'Rule 1: Never Enter Personal Data', 'ai-risk-benchmark' ),
						'body'  => __( 'Keep the following out of public AI tools:', 'ai-risk-benchmark' ),
						'items' => array(
							__( 'Pupil names or initials that identify a child', 'ai-risk-benchmark' ),
							__( 'SEND, medical or safeguarding information', 'ai-risk-benchmark' ),
							__( 'Behaviour records and assessment data', 'ai-risk-benchmark' ),
							__( 'Photographs or contact details', 'ai-risk-benchmark' ),
						),
					),
					array(
						'title' => __( 'Rule 2: Anonymise', 'ai-risk-benchmark' ),
						'body'  => __( 'If you need help with a real scenario, remove identifying detail. Use "a Year 8 pupil" rather than a name, and strip anything that could identify the child in combination.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Rule 3: Use Approved Tools', 'ai-risk-benchmark' ),
						'body'  => __( 'Check your school\'s approved tools list before adopting anything new. If in doubt, ask your DPO or AI Lead.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Rule 4: Assume It Is Not Private', 'ai-risk-benchmark' ),
						'body'  => __( 'Treat anything typed into a public AI tool as potentially visible to others. If you would not put it on a postcard, do not put it in the prompt.', 'ai-risk-benchmark' ),
					),
				),
				'post_sections'      => array(
					array(
						'heading' => __( 'When Something Goes Wrong', 'ai-risk-benchmark' ),
						'intro'   => __( 'If personal data is entered into an AI tool by mistake:', 'ai-risk-benchmark' ),
						'items'   => array(
							__( 'Report it to your Data Protection Officer immediately', 'ai-risk-benchmark' ),
							__( 'Follow your school\'s data breach procedure', 'ai-risk-benchmark' ),
							__( 'Do not attempt to hide or delay reporting', 'ai-risk-benchmark' ),
						),
					),
				),
				'checklist'          => array(
					'heading' => __( 'Quick Privacy Check', 'ai-risk-benchmark' ),
					'intro'   => __( 'Before you press enter:', 'ai-risk-benchmark' ),
					'items'   => array(
						__( 'No child can be identified from this prompt', 'ai-risk-benchmark' ),
						__( 'No SEND, medical or safeguarding detail is included', 'ai-risk-benchmark' ),
						__( 'The tool is on our approved list', 'ai-risk-benchmark' ),
						__( 'I know who our DPO is if something goes wrong', 'ai-risk-benchmark' ),
					),
				),
				'download'           => array(
					'label' => __( 'Pupil Data Reminder Card (PDF — add download in editor)', 'ai-risk-benchmark' ),
					'note'  => __( 'Wallet-size reminder of what never to enter into AI tools.', 'ai-risk-benchmark' ),
				),
				'training'           => array(
					'label' => __( 'AI Awareness Day Teacher Session', 'ai-risk-benchmark' ),
					'path'  => 'ai-awareness-day',
				),
				'benchmark'          => array(
					'label' => __( 'Retake the teacher benchmark after applying these rules', 'ai-risk-benchmark' ),
					'ref'   => 'privacy',
				),
				'benchmark_improves' => array(
					__( 'Privacy & Data Protection', 'ai-risk-benchmark' ),
					__( 'Safeguarding', 'ai-risk-benchmark' ),
					__( 'Safe Adoption', 'ai-risk-benchmark' ),
				),
				'closing'            => array(
					__( 'Protecting pupil data is part of safeguarding — not a separate task.', 'ai-risk-benchmark' ),
				),
			),
			'teacher-ai-assessment-guide'       => array(
				'headline'           => __( 'Teacher AI Assessment Guide', 'ai-risk-benchmark' ),
				'subtitle'           => __( 'Protecting Assessment Integrity in the Age of AI', 'ai-risk-benchmark' ),
				'why_heading'        => __( 'Why This Matters', 'ai-risk-benchmark' ),
				'why_paragraphs'     => array(
					__( 'AI makes it easy for pupils to produce work that is not their own — and easy for staff to over-rely on AI feedback. Both undermine the integrity of assessment.', 'ai-risk-benchmark' ),
					__( 'This guide reflects Joint Council for Qualifications (JCQ) and Ofqual expectations on assessment and academic integrity.', 'ai-risk-benchmark' ),
				),
				'framework_heading'  => __( 'Protecting Integrity', 'ai-risk-benchmark' ),
				'steps'              => array(
					array(
						'title' => __( 'Be Clear About Expectations', 'ai-risk-benchmark' ),
						'body'  => __( 'Tell pupils when AI is permitted, when it is not, and how use must be acknowledged. Ambiguity drives misuse.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Design Assessment That Shows Thinking', 'ai-risk-benchmark' ),
						'body'  => __( 'Favour tasks that reveal the process:', 'ai-risk-benchmark' ),
						'items' => array(
							__( 'Drafts, plans and annotated working', 'ai-risk-benchmark' ),
							__( 'In-class and verbal components', 'ai-risk-benchmark' ),
							__( 'Personal reflection and local context', 'ai-risk-benchmark' ),
							__( 'Follow-up questions that test understanding', 'ai-risk-benchmark' ),
						),
					),
					array(
						'title' => __( 'Use AI Feedback Carefully', 'ai-risk-benchmark' ),
						'body'  => __( 'AI can speed up formative feedback, but you remain responsible for accuracy and fairness. Never enter identifiable pupil work into public tools, and always review AI feedback before it reaches a pupil.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Respond Proportionately', 'ai-risk-benchmark' ),
						'body'  => __( 'Where misuse is suspected, focus on a conversation and learning first. Follow your school and JCQ procedures for formal assessments.', 'ai-risk-benchmark' ),
					),
				),
				'checklist'          => array(
					'heading' => __( 'Assessment Integrity Checklist', 'ai-risk-benchmark' ),
					'intro'   => __( 'For each assessment:', 'ai-risk-benchmark' ),
					'items'   => array(
						__( 'Pupils know whether AI is allowed and how to acknowledge it', 'ai-risk-benchmark' ),
						__( 'The task makes pupil thinking visible', 'ai-risk-benchmark' ),
						__( 'Any AI feedback I use is reviewed for accuracy', 'ai-risk-benchmark' ),
						__( 'No identifiable pupil work was entered into a public tool', 'ai-risk-benchmark' ),
						__( 'I know the JCQ / school process if misuse is suspected', 'ai-risk-benchmark' ),
					),
				),
				'download'           => array(
					'label' => __( 'Assessment Integrity Checklist (PDF — add download in editor)', 'ai-risk-benchmark' ),
					'note'  => __( 'Use with department and moderation meetings.', 'ai-risk-benchmark' ),
				),
				'training'           => array(
					'label' => __( 'AI Awareness Day Teacher Session', 'ai-risk-benchmark' ),
					'path'  => 'ai-awareness-day',
				),
				'benchmark'          => array(
					'label' => __( 'Retake the teacher benchmark after reviewing your assessment practice', 'ai-risk-benchmark' ),
					'ref'   => 'assessment_integrity',
				),
				'benchmark_improves' => array(
					__( 'Assessment Integrity', 'ai-risk-benchmark' ),
					__( 'AI Literacy', 'ai-risk-benchmark' ),
					__( 'Human Oversight', 'ai-risk-benchmark' ),
				),
				'closing'            => array(
					__( 'The goal is not to catch pupils out — it is to keep learning honest.', 'ai-risk-benchmark' ),
				),
			),
			'student-ai-study-skills'           => array(
				'headline'           => __( 'Student AI Study Skills', 'ai-risk-benchmark' ),
				'subtitle'           => __( 'Use AI to Learn More, Not Less', 'ai-risk-benchmark' ),
				'why_heading'        => __( 'Why This Matters', 'ai-risk-benchmark' ),
				'why_paragraphs'     => array(
					__( 'AI can be a brilliant study partner — or it can do your thinking for you so you learn nothing. The difference is how you use it.', 'ai-risk-benchmark' ),
					__( 'These study skills help you use AI to understand more, remember more and stay in control of your own learning.', 'ai-risk-benchmark' ),
				),
				'framework_heading'  => __( 'Smart Ways to Study With AI', 'ai-risk-benchmark' ),
				'steps'              => array(
					array(
						'title' => __( 'Try First, Then Check', 'ai-risk-benchmark' ),
						'body'  => __( 'Attempt the work yourself before asking AI. Then use it to check, compare and improve — not to start.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Ask It to Teach, Not to Tell', 'ai-risk-benchmark' ),
						'body'  => __( 'Powerful study prompts:', 'ai-risk-benchmark' ),
						'items' => array(
							__( 'Explain this like I am 12.', 'ai-risk-benchmark' ),
							__( 'Give me a worked example, then a question to try.', 'ai-risk-benchmark' ),
							__( 'Quiz me on this topic.', 'ai-risk-benchmark' ),
							__( 'Tell me what I got wrong and why.', 'ai-risk-benchmark' ),
						),
					),
					array(
						'title' => __( 'Test Yourself Without It', 'ai-risk-benchmark' ),
						'body'  => __( 'Close the AI and explain the idea out loud or on paper. If you can teach it, you have learned it.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Stay Honest', 'ai-risk-benchmark' ),
						'body'  => __( 'Follow your school\'s rules. Never submit AI writing as your own work.', 'ai-risk-benchmark' ),
					),
				),
				'challenge'          => array(
					'heading' => __( 'Revision Challenge', 'ai-risk-benchmark' ),
					'intro'   => __( 'For your next topic:', 'ai-risk-benchmark' ),
					'steps'   => array(
						array(
							'title' => __( 'Step 1', 'ai-risk-benchmark' ),
							'body'  => __( 'Ask AI to quiz you with ten questions.', 'ai-risk-benchmark' ),
						),
						array(
							'title' => __( 'Step 2', 'ai-risk-benchmark' ),
							'body'  => __( 'Answer them without looking anything up.', 'ai-risk-benchmark' ),
						),
						array(
							'title' => __( 'Step 3', 'ai-risk-benchmark' ),
							'body'  => __( 'Ask it to explain only the ones you got wrong, then try again tomorrow.', 'ai-risk-benchmark' ),
						),
					),
				),
				'download'           => array(
					'label' => __( 'Study With AI checklist (PDF — add download in editor)', 'ai-risk-benchmark' ),
					'note'  => __( 'Reminder card for planners and revision folders.', 'ai-risk-benchmark' ),
				),
				'benchmark'          => array(
					'label' => __( 'Retake the student benchmark after trying these skills', 'ai-risk-benchmark' ),
					'ref'   => 'ai_dependency',
				),
				'benchmark_improves' => array(
					__( 'Independent Thinking', 'ai-risk-benchmark' ),
					__( 'Responsible AI Use', 'ai-risk-benchmark' ),
					__( 'AI Literacy', 'ai-risk-benchmark' ),
				),
				'remember'           => array(
					'heading' => __( 'Remember', 'ai-risk-benchmark' ),
					'body'    => __( 'AI is your coach, not your ghostwriter.', 'ai-risk-benchmark' ),
				),
			),
			'student-ai-privacy-guide'          => array(
				'headline'           => __( 'Student AI Privacy Guide', 'ai-risk-benchmark' ),
				'subtitle'           => __( 'Stay Safe and Private When Using AI', 'ai-risk-benchmark' ),
				'why_heading'        => __( 'Why This Matters', 'ai-risk-benchmark' ),
				'why_paragraphs'     => array(
					__( 'AI tools often save what you type. Once you share something, you may not be able to take it back.', 'ai-risk-benchmark' ),
					__( 'Protecting your privacy keeps you safe online — and AI does not need your personal information to help you learn.', 'ai-risk-benchmark' ),
				),
				'framework_heading'  => __( 'Keep These Private', 'ai-risk-benchmark' ),
				'steps'              => array(
					array(
						'title' => __( 'Never Share', 'ai-risk-benchmark' ),
						'body'  => __( 'Keep these to yourself:', 'ai-risk-benchmark' ),
						'items' => array(
							__( 'Your full name', 'ai-risk-benchmark' ),
							__( 'Your school, address or phone number', 'ai-risk-benchmark' ),
							__( 'Passwords or logins', 'ai-risk-benchmark' ),
							__( 'Photos of yourself or others', 'ai-risk-benchmark' ),
							__( 'Anything about friends or family', 'ai-risk-benchmark' ),
						),
					),
					array(
						'title' => __( 'Think Before You Type', 'ai-risk-benchmark' ),
						'body'  => __( 'Ask yourself: would I be happy for a stranger to read this? If not, do not type it.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Tell a Trusted Adult', 'ai-risk-benchmark' ),
						'body'  => __( 'If an AI tool asks for personal information, says something that worries you, or shows something inappropriate, tell a parent, carer or teacher.', 'ai-risk-benchmark' ),
					),
				),
				'checklist'          => array(
					'heading' => __( 'Before You Use an AI Tool', 'ai-risk-benchmark' ),
					'intro'   => __( 'Check:', 'ai-risk-benchmark' ),
					'items'   => array(
						__( 'I have not shared my name or where I live', 'ai-risk-benchmark' ),
						__( 'I have not shared photos or passwords', 'ai-risk-benchmark' ),
						__( 'I would be okay if a stranger read this', 'ai-risk-benchmark' ),
						__( 'I know which adult to tell if something feels wrong', 'ai-risk-benchmark' ),
					),
				),
				'benchmark'          => array(
					'label' => __( 'Retake the student benchmark after reading this guide', 'ai-risk-benchmark' ),
					'ref'   => 'privacy',
				),
				'benchmark_improves' => array(
					__( 'Privacy Awareness', 'ai-risk-benchmark' ),
					__( 'Online Safety', 'ai-risk-benchmark' ),
					__( 'Responsible AI Use', 'ai-risk-benchmark' ),
				),
				'remember'           => array(
					'heading' => __( 'Remember', 'ai-risk-benchmark' ),
					'body'    => __( 'AI does not need to know who you are to help you.', 'ai-risk-benchmark' ),
				),
			),
			'how-to-check-ai-answers'           => array(
				'headline'           => __( 'How To Check AI Answers', 'ai-risk-benchmark' ),
				'subtitle'           => __( 'Spot Mistakes Before You Trust Them', 'ai-risk-benchmark' ),
				'why_heading'        => __( 'Why This Matters', 'ai-risk-benchmark' ),
				'why_paragraphs'     => array(
					__( 'AI sounds confident even when it is wrong. It can invent facts, quotes and sources — this is called a hallucination.', 'ai-risk-benchmark' ),
					__( 'Learning to check answers is the most important AI skill you can have. It keeps your work accurate and your thinking sharp.', 'ai-risk-benchmark' ),
				),
				'framework_heading'  => __( 'The Four Checks', 'ai-risk-benchmark' ),
				'steps'              => array(
					array(
						'title' => __( 'Check 1: Does It Make Sense?', 'ai-risk-benchmark' ),
						'body'  => __( 'Read it slowly. Does it match what you already know? If something feels off, dig deeper.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Check 2: Compare a Second Source', 'ai-risk-benchmark' ),
						'body'  => __( 'Look it up in your textbook, class notes or a trusted website. Never rely on AI alone for facts.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Check 3: Ask for the Source', 'ai-risk-benchmark' ),
						'body'  => __( 'Ask the AI where its information comes from — then check the source is real. AI sometimes invents references.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Check 4: Watch for Red Flags', 'ai-risk-benchmark' ),
						'body'  => __( 'Be extra careful with:', 'ai-risk-benchmark' ),
						'items' => array(
							__( 'Exact statistics and dates', 'ai-risk-benchmark' ),
							__( 'Quotes and named studies', 'ai-risk-benchmark' ),
							__( 'Very recent events', 'ai-risk-benchmark' ),
							__( 'Anything that sounds too neat', 'ai-risk-benchmark' ),
						),
					),
				),
				'challenge'          => array(
					'heading' => __( 'Fact-Check Challenge', 'ai-risk-benchmark' ),
					'intro'   => __( 'Try this:', 'ai-risk-benchmark' ),
					'steps'   => array(
						array(
							'title' => __( 'Step 1', 'ai-risk-benchmark' ),
							'body'  => __( 'Ask AI a question about a topic you know well.', 'ai-risk-benchmark' ),
						),
						array(
							'title' => __( 'Step 2', 'ai-risk-benchmark' ),
							'body'  => __( 'Find one thing it got wrong or left out.', 'ai-risk-benchmark' ),
						),
						array(
							'title' => __( 'Step 3', 'ai-risk-benchmark' ),
							'body'  => __( 'Correct it in your own words using a trusted source.', 'ai-risk-benchmark' ),
						),
					),
				),
				'benchmark'          => array(
					'label' => __( 'Retake the student benchmark after practising these checks', 'ai-risk-benchmark' ),
					'ref'   => 'verify',
				),
				'benchmark_improves' => array(
					__( 'Verification Skills', 'ai-risk-benchmark' ),
					__( 'Critical Thinking', 'ai-risk-benchmark' ),
					__( 'AI Literacy', 'ai-risk-benchmark' ),
				),
				'remember'           => array(
					'heading' => __( 'Remember', 'ai-risk-benchmark' ),
					'body'    => __( 'Confident does not mean correct. Always check.', 'ai-risk-benchmark' ),
				),
			),
			'parent-ai-homework-guide'          => array(
				'headline'           => __( 'Parent AI Homework Guide', 'ai-risk-benchmark' ),
				'subtitle'           => __( 'Helping With Homework in the Age of AI', 'ai-risk-benchmark' ),
				'why_heading'        => __( 'Why This Matters', 'ai-risk-benchmark' ),
				'why_paragraphs'     => array(
					__( 'Children now have instant access to AI that can answer almost any homework question. Used well, it supports learning. Used badly, it replaces the thinking homework is meant to build.', 'ai-risk-benchmark' ),
					__( 'You do not need to be an AI expert. A few simple habits at home make all the difference.', 'ai-risk-benchmark' ),
				),
				'framework_heading'  => __( 'Helping the Right Way', 'ai-risk-benchmark' ),
				'steps'              => array(
					array(
						'title' => __( 'Attempt First', 'ai-risk-benchmark' ),
						'body'  => __( 'Encourage your child to try the work themselves before opening any AI tool. The struggle is where the learning happens.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Use AI to Understand, Not to Finish', 'ai-risk-benchmark' ),
						'body'  => __( 'It is fine to ask AI to explain a tricky idea. It is not fine to have AI write the answer to hand in.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Ask the Magic Question', 'ai-risk-benchmark' ),
						'body'  => __( 'A simple check at the end of homework:', 'ai-risk-benchmark' ),
						'quote' => __( 'Did AI help you think, or did it do the thinking for you?', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Check the School\'s Rules', 'ai-risk-benchmark' ),
						'body'  => __( 'Different schools allow different things. If you are unsure when AI is permitted, ask your child\'s teacher.', 'ai-risk-benchmark' ),
					),
				),
				'post_sections'      => array(
					array(
						'heading' => __( 'Signs AI Is Doing Too Much', 'ai-risk-benchmark' ),
						'intro'   => __( 'Gently check in if your child:', 'ai-risk-benchmark' ),
						'items'   => array(
							__( 'Finishes quickly but cannot explain the work', 'ai-risk-benchmark' ),
							__( 'Reaches for AI before reading the question', 'ai-risk-benchmark' ),
							__( 'Hands in work that does not sound like them', 'ai-risk-benchmark' ),
						),
					),
				),
				'download'           => array(
					'label' => __( 'Homework and AI one-pager (PDF — add download in editor)', 'ai-risk-benchmark' ),
					'note'  => __( 'Print for the fridge or homework area.', 'ai-risk-benchmark' ),
				),
				'training'           => array(
					'label' => __( 'AI Awareness Day — parent session', 'ai-risk-benchmark' ),
					'path'  => 'ai-awareness-day',
				),
				'benchmark'          => array(
					'label' => __( 'Retake the parent benchmark after trying these habits', 'ai-risk-benchmark' ),
					'ref'   => 'homework',
				),
				'benchmark_improves' => array(
					__( 'Parent AI Awareness', 'ai-risk-benchmark' ),
					__( 'Home AI Safety', 'ai-risk-benchmark' ),
					__( 'Parent Confidence', 'ai-risk-benchmark' ),
				),
				'closing'            => array(
					__( 'Your interest matters more than your expertise.', 'ai-risk-benchmark' ),
				),
			),
			'talking-to-children-about-ai'      => array(
				'headline'           => __( 'Talking To Children About AI', 'ai-risk-benchmark' ),
				'subtitle'           => __( 'Simple Conversations That Build Safe Habits', 'ai-risk-benchmark' ),
				'why_heading'        => __( 'Why This Matters', 'ai-risk-benchmark' ),
				'why_paragraphs'     => array(
					__( 'The best protection is not blocking AI — it is regular, open conversation. Children who talk about AI at home make safer, smarter choices with it.', 'ai-risk-benchmark' ),
					__( 'You do not need technical knowledge. You need curiosity and a few good questions.', 'ai-risk-benchmark' ),
				),
				'framework_heading'  => __( 'How to Start the Conversation', 'ai-risk-benchmark' ),
				'steps'              => array(
					array(
						'title' => __( 'Be Curious, Not Worried', 'ai-risk-benchmark' ),
						'body'  => __( 'Ask your child to show you the AI tools they use. Let them teach you — it keeps the conversation open and honest.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Ask Open Questions', 'ai-risk-benchmark' ),
						'body'  => __( 'Good conversation starters:', 'ai-risk-benchmark' ),
						'items' => array(
							__( 'Which AI tools do you and your friends use?', 'ai-risk-benchmark' ),
							__( 'How do you know if what it says is true?', 'ai-risk-benchmark' ),
							__( 'Has AI ever got something wrong?', 'ai-risk-benchmark' ),
							__( 'What would you never share with it?', 'ai-risk-benchmark' ),
						),
					),
					array(
						'title' => __( 'Talk About Feelings, Too', 'ai-risk-benchmark' ),
						'body'  => __( 'Ask whether anything they have seen online or through AI has ever made them uncomfortable, and remind them they can always come to you.', 'ai-risk-benchmark' ),
					),
					array(
						'title' => __( 'Keep It Going', 'ai-risk-benchmark' ),
						'body'  => __( 'One chat is not enough. Little and often works best — in the car, over dinner, whenever it comes up naturally.', 'ai-risk-benchmark' ),
					),
				),
				'post_sections'      => array(
					array(
						'heading' => __( 'When to Contact School', 'ai-risk-benchmark' ),
						'intro'   => __( 'Speak to your child\'s school if you have concerns about:', 'ai-risk-benchmark' ),
						'items'   => array(
							__( 'AI-generated bullying or deepfakes', 'ai-risk-benchmark' ),
							__( 'Inappropriate content', 'ai-risk-benchmark' ),
							__( 'Academic honesty', 'ai-risk-benchmark' ),
							__( 'Privacy or safety', 'ai-risk-benchmark' ),
						),
					),
				),
				'download'           => array(
					'label' => __( 'Conversation starters card (PDF — add download in editor)', 'ai-risk-benchmark' ),
					'note'  => __( 'Keep a few questions handy for everyday moments.', 'ai-risk-benchmark' ),
				),
				'training'           => array(
					'label' => __( 'AI Awareness Day — parent session', 'ai-risk-benchmark' ),
					'path'  => 'ai-awareness-day',
				),
				'benchmark'          => array(
					'label' => __( 'Retake the parent benchmark after starting these conversations', 'ai-risk-benchmark' ),
					'ref'   => 'parent_awareness',
				),
				'benchmark_improves' => array(
					__( 'Parent AI Awareness', 'ai-risk-benchmark' ),
					__( 'Child Privacy Awareness', 'ai-risk-benchmark' ),
					__( 'Parent Confidence', 'ai-risk-benchmark' ),
				),
				'closing'            => array(
					__( 'Children do not need experts. They need interested, supportive adults.', 'ai-risk-benchmark' ),
				),
			),
		);
	}

	/**
	 * Generic intervention scaffold for pages not yet fully written.
	 *
	 * @return array<string, mixed>
	 */
	private static function generic_framework( string $slug, string $excerpt ): array {
		return array(
			'headline'    => '',
			'intro'       => $excerpt,
			'purpose'     => __( 'Strengthen a weak benchmark domain.', 'ai-risk-benchmark' ),
			'why_heading' => __( 'Why this matters', 'ai-risk-benchmark' ),
			'why_body'    => __( 'Your benchmark identified this as an area to strengthen. Complete this intervention, then retake the audit to measure improvement.', 'ai-risk-benchmark' ),
			'steps'       => array(
				array(
					'title' => __( 'What should I do?', 'ai-risk-benchmark' ),
					'body'  => __( 'Add step-by-step actions aligned to DfE, ICO, KCSIE, JCQ, Ofqual and your school policy in the WordPress editor.', 'ai-risk-benchmark' ),
				),
			),
			'download'    => array(
				'label' => __( 'Download — add checklist or template in editor', 'ai-risk-benchmark' ),
				'note'  => '',
			),
			'training'    => array(
				'label' => __( 'Training — AI Awareness Day session', 'ai-risk-benchmark' ),
				'path'  => 'ai-awareness-day',
			),
			'benchmark'   => array(
				'label' => __( 'Retake the benchmark after completing this framework', 'ai-risk-benchmark' ),
				'ref'   => $slug,
			),
		);
	}

	/**
	 * @param array<string, mixed> $def Framework definition.
	 */
	private static function render( array $def, string $benchmark_cta, string $page_slug = '' ): string {
		$blocks = array();

		$blocks[] = '<!-- ' . esc_html( self::CONTENT_VERSION_MARKER ) . ' -->';

		if ( ! empty( $def['subtitle'] ) ) {
			$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( (string) $def['subtitle'] ) . '</h2><!-- /wp:heading -->';
		}

		$intro = (string) ( $def['intro'] ?? '' );
		if ( $intro ) {
			$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( $intro ) . '</p><!-- /wp:paragraph -->';
		}

		if ( ! empty( $def['purpose'] ) ) {
			$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'Purpose', 'ai-risk-benchmark' ) . '</h2><!-- /wp:heading -->';
			$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( (string) $def['purpose'] ) . '</p><!-- /wp:paragraph -->';
		}

		if ( ! isset( $def['guidance_alignment'] ) || $def['guidance_alignment'] ) {
			$blocks[] = '<!-- wp:paragraph --><p><em>' . esc_html( self::uk_guidance_note() ) . '</em></p><!-- /wp:paragraph -->';
		}

		$why_h            = (string) ( $def['why_heading'] ?? __( 'Why this matters', 'ai-risk-benchmark' ) );
		$why_paragraphs   = (array) ( $def['why_paragraphs'] ?? array() );
		$why_list         = (array) ( $def['why_list'] ?? array() );
		$why_after        = (array) ( $def['why_after'] ?? array() );
		$why_body         = (string) ( $def['why_body'] ?? '' );
		$has_why_section  = $why_paragraphs || $why_body || $why_list || $why_after;

		if ( $has_why_section ) {
			$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $why_h ) . '</h2><!-- /wp:heading -->';

			if ( $why_paragraphs ) {
				foreach ( $why_paragraphs as $paragraph ) {
					if ( $paragraph ) {
						$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( (string) $paragraph ) . '</p><!-- /wp:paragraph -->';
					}
				}
			} elseif ( $why_body ) {
				$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( $why_body ) . '</p><!-- /wp:paragraph -->';
			}

			if ( $why_list ) {
				$blocks[] = self::list_block( $why_list );
			}

			foreach ( $why_after as $paragraph ) {
				if ( $paragraph ) {
					$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( (string) $paragraph ) . '</p><!-- /wp:paragraph -->';
				}
			}
		}

		foreach ( (array) ( $def['preface_sections'] ?? array() ) as $section ) {
			$blocks = array_merge( $blocks, self::content_section_blocks( (array) $section ) );
		}

		$checklist_sections = (array) ( $def['checklist_sections'] ?? array() );
		if ( $checklist_sections ) {
			$blocks[] = self::interactive_checklist_sections_html( $checklist_sections );
		}

		foreach ( (array) ( $def['risk_categories'] ?? array() ) as $category ) {
			if ( ! is_array( $category ) ) {
				continue;
			}
			if ( ! empty( $category['heading'] ) ) {
				$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( (string) $category['heading'] ) . '</h2><!-- /wp:heading -->';
			}
			foreach ( (array) ( $category['fields'] ?? array() ) as $field ) {
				$field = (array) $field;
				if ( ! empty( $field['label'] ) ) {
					$blocks[] = '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html( (string) $field['label'] ) . '</h3><!-- /wp:heading -->';
				}
				if ( ! empty( $field['body'] ) ) {
					$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( (string) $field['body'] ) . '</p><!-- /wp:paragraph -->';
				}
				if ( ! empty( $field['items'] ) ) {
					$blocks[] = self::list_block( (array) $field['items'] );
				}
			}
		}

		$risk_matrix = (array) ( $def['risk_matrix'] ?? array() );
		if ( ! empty( $risk_matrix['heading'] ) ) {
			$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( (string) $risk_matrix['heading'] ) . '</h2><!-- /wp:heading -->';
		}
		if ( ! empty( $risk_matrix['headers'] ) && ! empty( $risk_matrix['rows'] ) ) {
			$blocks[] = self::table_block( (array) $risk_matrix['headers'], (array) $risk_matrix['rows'] );
		}

		$steps = (array) ( $def['steps'] ?? array() );
		if ( $steps ) {
			$framework_h = (string) ( $def['framework_heading'] ?? __( 'Framework', 'ai-risk-benchmark' ) );
			$blocks[]    = '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $framework_h ) . '</h2><!-- /wp:heading -->';
			foreach ( $steps as $step ) {
				$blocks = array_merge( $blocks, self::step_blocks( (array) $step ) );
			}
		}

		foreach ( (array) ( $def['post_sections'] ?? array() ) as $section ) {
			if ( ! is_array( $section ) ) {
				continue;
			}
			if ( ! empty( $section['heading'] ) ) {
				$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( (string) $section['heading'] ) . '</h2><!-- /wp:heading -->';
			}
			if ( ! empty( $section['intro'] ) ) {
				$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( (string) $section['intro'] ) . '</p><!-- /wp:paragraph -->';
			}
			if ( ! empty( $section['body'] ) ) {
				$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( (string) $section['body'] ) . '</p><!-- /wp:paragraph -->';
			}
			if ( ! empty( $section['quote'] ) ) {
				$blocks[] = '<!-- wp:quote --><blockquote class="wp-block-quote"><p>' . esc_html( (string) $section['quote'] ) . '</p></blockquote><!-- /wp:quote -->';
			}
			if ( ! empty( $section['after'] ) ) {
				$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( (string) $section['after'] ) . '</p><!-- /wp:paragraph -->';
			}
			if ( ! empty( $section['items'] ) ) {
				$blocks[] = self::list_block( (array) $section['items'] );
			}
			foreach ( (array) ( $section['blocks'] ?? array() ) as $block ) {
				$blocks = array_merge( $blocks, self::step_blocks( (array) $block ) );
			}
		}

		$checklist = (array) ( $def['checklist'] ?? array() );
		if ( ! empty( $checklist['heading'] ) ) {
			$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( (string) $checklist['heading'] ) . '</h2><!-- /wp:heading -->';
			if ( ! empty( $checklist['intro'] ) ) {
				$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( (string) $checklist['intro'] ) . '</p><!-- /wp:paragraph -->';
			}
			if ( ! empty( $checklist['items'] ) ) {
				$blocks[] = self::interactive_checklist_html(
					array( array( 'items' => (array) $checklist['items'] ) ),
					false
				);
			}
		}

		$challenge = (array) ( $def['challenge'] ?? array() );
		if ( ! empty( $challenge['heading'] ) ) {
			$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( (string) $challenge['heading'] ) . '</h2><!-- /wp:heading -->';
			if ( ! empty( $challenge['intro'] ) ) {
				$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( (string) $challenge['intro'] ) . '</p><!-- /wp:paragraph -->';
			}
			if ( ! empty( $challenge['body'] ) ) {
				$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( (string) $challenge['body'] ) . '</p><!-- /wp:paragraph -->';
			}
			foreach ( (array) ( $challenge['steps'] ?? array() ) as $step ) {
				$blocks = array_merge( $blocks, self::step_blocks( (array) $step ) );
			}
		}

		$discussion = (array) ( $def['discussion'] ?? array() );
		if ( ! empty( $discussion['heading'] ) ) {
			$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( (string) $discussion['heading'] ) . '</h2><!-- /wp:heading -->';
			if ( ! empty( $discussion['intro'] ) ) {
				$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( (string) $discussion['intro'] ) . '</p><!-- /wp:paragraph -->';
			}
			if ( ! empty( $discussion['items'] ) ) {
				$blocks[] = self::list_block( (array) $discussion['items'] );
			}
		}

		$download = (array) ( $def['download'] ?? array() );
		if ( ! empty( $download['label'] ) ) {
			$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'What can I download?', 'ai-risk-benchmark' ) . '</h2><!-- /wp:heading -->';
			$blocks[] = '<!-- wp:paragraph --><p><strong>' . esc_html( (string) $download['label'] ) . '</strong></p><!-- /wp:paragraph -->';
			if ( ! empty( $download['note'] ) ) {
				$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( (string) $download['note'] ) . '</p><!-- /wp:paragraph -->';
			}
		}

		$training = (array) ( $def['training'] ?? array() );
		if ( ! empty( $training['label'] ) || ! empty( $training['heading'] ) ) {
			$train_h = (string) ( $training['heading'] ?? __( 'What training exists?', 'ai-risk-benchmark' ) );
			$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $train_h ) . '</h2><!-- /wp:heading -->';
			if ( ! empty( $training['label'] ) ) {
				$path    = (string) ( $training['path'] ?? '' );
				$prefill = '';
				if ( 'contact' === $path ) {
					$prefill = sanitize_key( (string) ( $training['interest'] ?? $training['prefill'] ?? '' ) );
					if ( ! $prefill && $page_slug && class_exists( 'AIRB_Hub_Interest' ) ) {
						$slug_map = AIRB_Hub_Interest::slug_interest_map();
						$prefill  = sanitize_key( (string) ( $slug_map[ $page_slug ] ?? 'further_information' ) );
					}
					$blocks[] = '<!-- wp:paragraph --><p><strong>' . self::hub_interest_link_open( $prefill ) . esc_html( (string) $training['label'] ) . '</a></strong></p><!-- /wp:paragraph -->';
				} elseif ( $path ) {
					$url = AIRB_Defaults::hub_page_url( $path );
					$blocks[] = '<!-- wp:paragraph --><p><strong><a href="' . esc_url( $url ) . '">' . esc_html( (string) $training['label'] ) . '</a></strong></p><!-- /wp:paragraph -->';
				} else {
					$blocks[] = '<!-- wp:paragraph --><p><strong>' . esc_html( (string) $training['label'] ) . '</strong></p><!-- /wp:paragraph -->';
				}
			}
			if ( ! empty( $training['topics'] ) ) {
				$blocks[] = '<!-- wp:paragraph --><p>' . esc_html__( 'Topics include:', 'ai-risk-benchmark' ) . '</p><!-- /wp:paragraph -->';
				$blocks[] = self::list_block( (array) $training['topics'] );
			}
		}

		if ( ! empty( $def['tool_note'] ) ) {
			$blocks[] = '<!-- wp:paragraph --><p><em>' . esc_html( (string) $def['tool_note'] ) . '</em></p><!-- /wp:paragraph -->';
		}

		$remember = (array) ( $def['remember'] ?? array() );

		$benchmark = (array) ( $def['benchmark'] ?? array() );
		$benchmark_heading = (string) ( $def['benchmark_heading'] ?? '' );
		$benchmark_intro   = (string) ( $def['benchmark_intro'] ?? '' );
		if ( ! empty( $benchmark['label'] ) || $benchmark_heading || $benchmark_intro ) {
			$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html( $benchmark_heading ? $benchmark_heading : __( 'Improve Your Benchmark Score', 'ai-risk-benchmark' ) ) . '</h2><!-- /wp:heading -->';
			if ( $benchmark_intro ) {
				$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( $benchmark_intro ) . '</p><!-- /wp:paragraph -->';
			}
			$improves = (array) ( $def['benchmark_improves'] ?? array() );
			if ( $improves ) {
				$supports = (string) ( $def['benchmark_supports'] ?? __( 'This framework supports improvement in:', 'ai-risk-benchmark' ) );
				$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( $supports ) . '</p><!-- /wp:paragraph -->';
				$blocks[] = self::list_block( $improves );
			}
			foreach ( (array) ( $def['closing'] ?? array() ) as $paragraph ) {
				if ( $paragraph ) {
					$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( (string) $paragraph ) . '</p><!-- /wp:paragraph -->';
				}
			}
			if ( ! empty( $benchmark['label'] ) ) {
				$ref = sanitize_key( (string) ( $benchmark['ref'] ?? '' ) );
				$url = AIRB_Defaults::benchmark_page_url();
				if ( $ref ) {
					$url = add_query_arg( array( 'airb_ref' => $ref ), $url );
				}
				$blocks[] = '<!-- wp:paragraph --><p><a href="' . esc_url( $url ) . '">' . esc_html( (string) $benchmark['label'] ) . '</a></p><!-- /wp:paragraph -->';
			}
		}

		if ( ! empty( $remember['heading'] ) || ! empty( $remember['body'] ) ) {
			if ( ! empty( $remember['heading'] ) ) {
				$blocks[] = '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html( (string) $remember['heading'] ) . '</h3><!-- /wp:heading -->';
			}
			if ( ! empty( $remember['body'] ) ) {
				$blocks[] = '<!-- wp:paragraph --><p><strong>' . esc_html( (string) $remember['body'] ) . '</strong></p><!-- /wp:paragraph -->';
			}
		}

		$blocks = array_merge( $blocks, self::support_blocks( $def ) );

		$blocks[] = '<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->';
		$blocks[] = '<!-- wp:paragraph --><p><a href="' . esc_url( AIRB_Defaults::benchmark_page_url() ) . '">' . esc_html( $benchmark_cta ) . '</a></p><!-- /wp:paragraph -->';

		return implode( "\n\n", $blocks );
	}

	/**
	 * @param array<int, string> $items List items.
	 */
	private static function list_block( array $items ): string {
		$items = array_values( array_filter( array_map( 'strval', $items ) ) );
		if ( ! $items ) {
			return '';
		}
		$html = '<!-- wp:list --><ul class="wp-block-list">';
		foreach ( $items as $item ) {
			$html .= '<li>' . esc_html( $item ) . '</li>';
		}
		$html .= '</ul><!-- /wp:list -->';
		return $html;
	}

	/**
	 * @param array<int, string>   $headers Table header cells.
	 * @param array<int, string[]> $rows    Table body rows.
	 */
	private static function table_block( array $headers, array $rows ): string {
		$headers = array_values( array_filter( array_map( 'strval', $headers ) ) );
		if ( ! $headers || ! $rows ) {
			return '';
		}

		$html = '<!-- wp:table --><figure class="wp-block-table"><table><thead><tr>';
		foreach ( $headers as $header ) {
			$html .= '<th>' . esc_html( $header ) . '</th>';
		}
		$html .= '</tr></thead><tbody>';
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$html .= '<tr>';
			foreach ( $row as $cell ) {
				$html .= '<td>' . esc_html( (string) $cell ) . '</td>';
			}
			$html .= '</tr>';
		}
		$html .= '</tbody></table></figure><!-- /wp:table -->';
		return $html;
	}

	/**
	 * @param array<int, string> $items Checklist items.
	 */
	private static function checklist_block( array $items ): string {
		$items = array_values( array_filter( array_map( 'strval', $items ) ) );
		if ( ! $items ) {
			return '';
		}
		$html = '<!-- wp:list --><ul class="wp-block-list">';
		foreach ( $items as $item ) {
			$html .= '<li>☐ ' . esc_html( $item ) . '</li>';
		}
		$html .= '</ul><!-- /wp:list -->';
		return $html;
	}

	/**
	 * Public accessor for the campaign contact email (used when enqueuing assets).
	 */
	public static function campaign_contact_email(): string {
		return self::contact_email();
	}

	/**
	 * Build the multi-section interactive checklist (e.g. DfE compliance review).
	 * Headings stay as real h2/h3 for SEO; the whole review shares one progress
	 * bar + RAG status so leaders see a single completion picture.
	 *
	 * @param array<int, array<string, mixed>> $sections Section definitions.
	 */
	private static function interactive_checklist_sections_html( array $sections ): string {
		$groups = array();
		foreach ( $sections as $section ) {
			$section = (array) $section;
			$heading = (string) ( $section['heading'] ?? '' );
			$subs    = (array) ( $section['subsections'] ?? array() );
			$first   = true;
			foreach ( $subs as $sub ) {
				$sub   = (array) $sub;
				$items = array_values( array_filter( array_map( 'strval', (array) ( $sub['items'] ?? array() ) ) ) );
				if ( ! $items ) {
					continue;
				}
				$groups[] = array(
					'section'    => $first ? $heading : '',
					'title'      => (string) ( $sub['title'] ?? '' ),
					'items'      => $items,
				);
				$first = false;
			}
		}

		return self::interactive_checklist_html( $groups, true );
	}

	/**
	 * Render a progressive-enhancement checklist. Stored markup is KSES-safe
	 * (no inputs/data-* attributes); airb-checklist.js turns each item into a
	 * tickable, saved checkbox with progress, optional RAG status, and
	 * "email to my SLT / the AI Awareness team" actions.
	 *
	 * @param array<int, array<string, mixed>> $groups  Groups of items.
	 * @param bool                             $show_rag Show RAG status badge.
	 */
	private static function interactive_checklist_html( array $groups, bool $show_rag ): string {
		$rows = '';
		foreach ( $groups as $group ) {
			$group   = (array) $group;
			$section = (string) ( $group['section'] ?? '' );
			$title   = (string) ( $group['title'] ?? '' );
			$items   = array_values( array_filter( array_map( 'strval', (array) ( $group['items'] ?? array() ) ) ) );
			if ( ! $items ) {
				continue;
			}
			if ( '' !== $section ) {
				$rows .= '<h2 class="wp-block-heading airb-checklist__section">' . esc_html( $section ) . '</h2>';
			}
			if ( '' !== $title ) {
				$rows .= '<h3 class="wp-block-heading airb-checklist__group">' . esc_html( $title ) . '</h3>';
			}
			$rows .= '<ul class="airb-checklist__list">';
			foreach ( $items as $item ) {
				$rows .= '<li class="airb-checklist__item">' . esc_html( $item ) . '</li>';
			}
			$rows .= '</ul>';
		}

		if ( '' === $rows ) {
			return '';
		}

		$class = 'airb-checklist' . ( $show_rag ? ' airb-checklist--rag' : '' );

		return '<!-- wp:html --><div class="' . esc_attr( $class ) . '">' . $rows . '</div><!-- /wp:html -->';
	}

	/**
	 * @param array<string, mixed> $section Section definition.
	 * @param int                    $heading_level Heading level for title.
	 * @return array<int, string>
	 */
	private static function content_section_blocks( array $section, int $heading_level = 2 ): array {
		$blocks = array();
		$title  = (string) ( $section['heading'] ?? $section['title'] ?? '' );
		if ( $title ) {
			$attr = 2 === $heading_level ? '' : ' {"level":' . (int) $heading_level . '}';
			$tag  = 2 === $heading_level ? 'h2' : 'h' . (int) $heading_level;
			$blocks[] = '<!-- wp:heading' . $attr . ' --><' . $tag . ' class="wp-block-heading">' . esc_html( $title ) . '</' . $tag . '><!-- /wp:heading -->';
		}
		if ( ! empty( $section['intro'] ) ) {
			$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( (string) $section['intro'] ) . '</p><!-- /wp:paragraph -->';
		}
		if ( ! empty( $section['body'] ) ) {
			$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( (string) $section['body'] ) . '</p><!-- /wp:paragraph -->';
		}
		if ( ! empty( $section['items'] ) ) {
			$blocks[] = self::list_block( (array) $section['items'] );
		}
		return $blocks;
	}

	/**
	 * @param array<string, mixed> $step Step definition.
	 * @return array<int, string>
	 */
	private static function step_blocks( array $step ): array {
		$blocks = array();
		$title  = (string) ( $step['title'] ?? '' );
		if ( $title ) {
			$blocks[] = '<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html( $title ) . '</h3><!-- /wp:heading -->';
		}
		if ( ! empty( $step['body'] ) ) {
			$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( (string) $step['body'] ) . '</p><!-- /wp:paragraph -->';
		}
		if ( ! empty( $step['items'] ) ) {
			$blocks[] = self::list_block( (array) $step['items'] );
		}
		if ( ! empty( $step['quote'] ) ) {
			$blocks[] = '<!-- wp:quote --><blockquote class="wp-block-quote"><p>' . esc_html( (string) $step['quote'] ) . '</p></blockquote><!-- /wp:quote -->';
		}
		if ( ! empty( $step['after'] ) ) {
			$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( (string) $step['after'] ) . '</p><!-- /wp:paragraph -->';
		}
		if ( ! empty( $step['link']['label'] ) ) {
			$path     = (string) ( $step['link']['path'] ?? '' );
			$external = (string) ( $step['link']['url'] ?? '' );
			$label    = (string) $step['link']['label'];
			if ( '' !== $external ) {
				$blocks[] = '<!-- wp:paragraph --><p>→ <a href="' . esc_url( $external ) . '" target="_blank" rel="noopener">' . esc_html( $label ) . '</a></p><!-- /wp:paragraph -->';
			} elseif ( '' !== $path ) {
				$blocks[] = '<!-- wp:paragraph --><p>→ <a href="' . esc_url( AIRB_Defaults::hub_page_url( $path ) ) . '">' . esc_html( $label ) . '</a></p><!-- /wp:paragraph -->';
			} else {
				$blocks[] = '<!-- wp:paragraph --><p>→ ' . esc_html( $label ) . '</p><!-- /wp:paragraph -->';
			}
		}
		foreach ( (array) ( $step['subsections'] ?? array() ) as $sub ) {
			$sub = (array) $sub;
			if ( ! empty( $sub['title'] ) ) {
				$blocks[] = '<!-- wp:heading {"level":4} --><h4 class="wp-block-heading">' . esc_html( (string) $sub['title'] ) . '</h4><!-- /wp:heading -->';
			}
			if ( ! empty( $sub['items'] ) ) {
				$blocks[] = self::list_block( (array) $sub['items'] );
			}
		}
		return $blocks;
	}
}
