<?php
/**
 * National Survey 2026 — shortcode, AJAX handler, and admin CSV export.
 *
 * Shortcode: [aiad_national_survey]
 * Responses are stored as 'survey_response' custom posts with _survey_* meta.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Post type
// ---------------------------------------------------------------------------

function aiad_register_survey_response_post_type(): void {
	register_post_type(
		'survey_response',
		array(
			'labels'              => array(
				'name'          => __( 'Survey Responses', 'ai-awareness-day' ),
				'singular_name' => __( 'Survey Response', 'ai-awareness-day' ),
				'all_items'     => __( 'All Survey Responses', 'ai-awareness-day' ),
				'search_items'  => __( 'Search Responses', 'ai-awareness-day' ),
				'edit_item'     => __( 'View Response', 'ai-awareness-day' ),
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'menu_icon'           => 'dashicons-chart-bar',
			'supports'            => array( 'title' ),
			'capability_type'     => 'post',
			'capabilities'        => array( 'create_posts' => false ),
			'map_meta_cap'        => true,
		)
	);
}
add_action( 'init', 'aiad_register_survey_response_post_type' );

// ---------------------------------------------------------------------------
// Assets
// ---------------------------------------------------------------------------

function aiad_register_national_survey_assets(): void {
	$css_path = AIAD_DIR . '/assets/css/components/national-survey.css';
	$js_path  = AIAD_DIR . '/assets/js/national-survey.js';

	wp_register_style(
		'aiad-national-survey',
		AIAD_URI . '/assets/css/components/national-survey.css',
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : AIAD_VERSION
	);

	wp_register_script(
		'aiad-national-survey',
		AIAD_URI . '/assets/js/national-survey.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : AIAD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'aiad_register_national_survey_assets', 5 );

function aiad_enqueue_national_survey_assets(): void {
	static $enqueued = false;
	if ( $enqueued ) {
		return;
	}
	$enqueued = true;
	wp_enqueue_style( 'aiad-national-survey' );
	wp_enqueue_script( 'aiad-national-survey' );
	wp_localize_script(
		'aiad-national-survey',
		'aiadSurvey',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'aiad_survey_nonce' ),
		)
	);
}

// ---------------------------------------------------------------------------
// Shortcode
// ---------------------------------------------------------------------------

/**
 * Shortcode: [aiad_national_survey]
 *
 * Steps:
 *   1  – School profile (everyone)
 *   2  – Participation gate (everyone)
 *   3p – Resource Friction / Part 2 (participants)
 *   3n – Non-participant block A (non-participants)
 *   4p – Learning Efficacy / Part 3 (participants)
 *   4n – Non-participant block B (non-participants)
 *   5p – Strategic Roadmap / Part 4 (participants only)
 *   6  – Stay in touch (everyone)
 *
 * @param array<string, string>|string $atts Unused.
 */
function aiad_national_survey_shortcode( $atts = array() ): string {
	aiad_enqueue_national_survey_assets();

	ob_start();
	?>
	<div class="aiad-survey" id="aiad-national-survey" role="main" aria-label="<?php esc_attr_e( 'AI Awareness Day National Survey', 'ai-awareness-day' ); ?>">

		<!-- Progress bar -->
		<div class="aiad-survey__progress" aria-hidden="true">
			<div class="aiad-survey__progress-bar" id="aiad-survey-progress-bar" style="width:0%"></div>
		</div>
		<p class="aiad-survey__progress-label" id="aiad-survey-progress-label"></p>

		<form class="aiad-survey__form" id="aiad-survey-form" novalidate>

			<!-- ── STEP: profile ── Part 1: School Profile & Contextual Baselines -->
			<fieldset class="aiad-survey__step" data-step-id="profile">
				<legend class="aiad-survey__step-title">
					<?php esc_html_e( 'Part 1 — School profile', 'ai-awareness-day' ); ?>
				</legend>

				<!-- Q1: Role -->
				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-role">
						1. <?php esc_html_e( 'What is your role within your institution?', 'ai-awareness-day' ); ?>
						<span class="aiad-survey__required" aria-hidden="true">*</span>
					</label>
					<div class="aiad-survey__radio-group">
						<?php
						$roles = array(
							'teacher_primary'   => __( 'Classroom Teacher (Primary)', 'ai-awareness-day' ),
							'teacher_secondary' => __( 'Classroom Teacher (Secondary / FE)', 'ai-awareness-day' ),
							'computing_lead'    => __( 'Computing Subject Lead / Head of Department', 'ai-awareness-day' ),
							'slt_mat'           => __( 'Senior Leadership Team (SLT) / MAT Digital Strategy Lead', 'ai-awareness-day' ),
							'alt_provision'     => __( 'Alternative Provision / SEN Support Specialist', 'ai-awareness-day' ),
						);
						foreach ( $roles as $val => $label ) :
							?>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="role" value="<?php echo esc_attr( $val ); ?>" class="aiad-survey__radio" required />
								<?php echo esc_html( $label ); ?>
							</label>
						<?php endforeach; ?>
					</div>
				</div>

				<!-- Q2: MAT / LA -->
				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-mat">
						2. <?php esc_html_e( 'Which Multi-Academy Trust (MAT) or Local Authority is your school associated with?', 'ai-awareness-day' ); ?>
					</label>
					<input class="aiad-survey__input" type="text" id="survey-mat" name="mat_la"
						placeholder="<?php esc_attr_e( 'e.g. Oasis Community Learning, Essex LA…', 'ai-awareness-day' ); ?>" maxlength="200" />
				</div>
			</fieldset>

			<!-- ── STEP: gate ── Participation gate -->
			<fieldset class="aiad-survey__step" data-step-id="gate">
				<legend class="aiad-survey__step-title">
					<?php esc_html_e( 'Did your school take part?', 'ai-awareness-day' ); ?>
				</legend>

				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						<?php esc_html_e( 'Did your school or classroom participate in National AI Awareness Day on 4th June 2026?', 'ai-awareness-day' ); ?>
						<span class="aiad-survey__required" aria-hidden="true">*</span>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="participated" value="yes" class="aiad-survey__radio" id="survey-participated-yes" required />
							<?php esc_html_e( 'Yes — we took part', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="participated" value="no" class="aiad-survey__radio" id="survey-participated-no" />
							<?php esc_html_e( 'No — we did not participate', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>

				<!-- Q3: Participation scale — only shown/required if "yes" (JS toggles visibility) -->
				<div class="aiad-survey__field" id="survey-participation-scale-wrap">
					<p class="aiad-survey__label">
						3. <?php esc_html_e( 'What was the approximate level of student participation in your school on 4th June?', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="participation_scale" value="single_classroom" class="aiad-survey__radio" />
							<?php esc_html_e( 'Single classroom focus (under 30 pupils)', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="participation_scale" value="year_group" class="aiad-survey__radio" />
							<?php esc_html_e( 'Year-group cohort intervention (30–120 pupils)', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="participation_scale" value="whole_school" class="aiad-survey__radio" />
							<?php esc_html_e( 'Whole-school deployment / Dedicated Assembly (120+ pupils)', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>
			</fieldset>

			<!-- ── STEP: resource-friction ── Part 2: Resource Friction (participants only) -->
			<fieldset class="aiad-survey__step" data-step-id="resource-friction" data-path="participant">
				<legend class="aiad-survey__step-title">
					<?php esc_html_e( 'Part 2 — Resource friction &amp; classroom application', 'ai-awareness-day' ); ?>
				</legend>

				<p class="aiad-survey__section-note">
					<?php esc_html_e( 'This section targets classroom execution reality — workload preservation and implementation speed.', 'ai-awareness-day' ); ?>
				</p>

				<!-- Q4: Prep time -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						4. <?php esc_html_e( 'The "Just One Activity" delivery model is designed to minimise prep time. How long did it take you to get your campaign resources ready to teach?', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="prep_time" value="zero" class="aiad-survey__radio" />
							<?php esc_html_e( 'Zero prep — we picked it up and streamed / ran it instantly.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="prep_time" value="under_15" class="aiad-survey__radio" />
							<?php esc_html_e( 'Less than 15 minutes of minor review.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="prep_time" value="15_to_45" class="aiad-survey__radio" />
							<?php esc_html_e( '15 to 45 minutes (required tweaking for our class level).', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="prep_time" value="over_45" class="aiad-survey__radio" />
							<?php esc_html_e( 'Over 45 minutes (too complex / required too much background reading).', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>

				<!-- Q5: Likert scales -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						5. <?php esc_html_e( 'Rate the accessibility of the core classroom resource assets (1 = Strongly Disagree, 5 = Strongly Agree):', 'ai-awareness-day' ); ?>
					</p>

					<?php
					$likerts = array(
						'rating_plug_and_play' => __(
							'"Plug and Play" Usability: Non-technical teachers on my staff could deploy the lesson slides confidently without prior AI training.',
							'ai-awareness-day'
						),
						'rating_student_access' => __(
							'Student Accessibility: The vocabulary and concepts matched my students\' age group and cognitive load.',
							'ai-awareness-day'
						),
						'rating_tech_delivery' => __(
							'Technical Delivery: The resources (live streams, slide decks, video links) loaded flawlessly on our school\'s network/firewall infrastructure.',
							'ai-awareness-day'
						),
					);
					foreach ( $likerts as $name => $label ) :
						?>
						<div class="aiad-survey__field aiad-survey__field--rating">
							<p class="aiad-survey__label aiad-survey__label--rating"><?php echo esc_html( $label ); ?></p>
							<div class="aiad-survey__stars" role="radiogroup" aria-label="<?php echo esc_attr( $label ); ?>">
								<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
									<label class="aiad-survey__star-label">
										<input type="radio" name="<?php echo esc_attr( $name ); ?>"
											value="<?php echo esc_attr( $i ); ?>"
											class="aiad-survey__star-input" />
										<span class="aiad-survey__star" aria-hidden="true">★</span>
										<span class="screen-reader-text"><?php echo esc_html( $i ); ?></span>
									</label>
								<?php endfor; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</fieldset>

			<!-- ── STEP: learning-efficacy ── Part 3 (participants only) -->
			<fieldset class="aiad-survey__step" data-step-id="learning-efficacy" data-path="participant">
				<legend class="aiad-survey__step-title">
					<?php esc_html_e( 'Part 3 — Learning efficacy', 'ai-awareness-day' ); ?>
				</legend>

				<p class="aiad-survey__section-note">
					<?php esc_html_e( '"Know It, Question It, Use It Wisely" — how well did the campaign hit its core pedagogical aims?', 'ai-awareness-day' ); ?>
				</p>

				<!-- Q6: Critical thinking shift -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						6. <?php esc_html_e( 'Following the campaign day activities, have you observed a shift in your students\' critical thinking around technology?', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="critical_thinking_shift" value="significant" class="aiad-survey__radio" />
							<?php esc_html_e( 'Significant change: Students are actively questioning chatbot outputs and checking for data bias/hallucinations.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="critical_thinking_shift" value="moderate" class="aiad-survey__radio" />
							<?php esc_html_e( 'Moderate change: Students are aware that AI is hiding in daily algorithms, but still treat outputs as objective truth.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="critical_thinking_shift" value="none" class="aiad-survey__radio" />
							<?php esc_html_e( 'No change: Students continue to use AI tools as pass-through response generators without critical auditing.', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>

				<!-- Q7: Staff room / parental network impact -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						7. <?php esc_html_e( 'How has the campaign impacted your staff room or parental network? (Select all that apply)', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__checkgroup aiad-survey__checkgroup--single">
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="staffroom_impact[]" value="admin_workload" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'It opened up an active conversation among non-technical teachers about using AI to reduce administrative/marking workloads.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="staffroom_impact[]" value="data_privacy" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'It helped us map out clear, safe boundaries for students regarding data privacy and cloud LLM usage.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="staffroom_impact[]" value="parents_engaged" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'It engaged parents through home-learning links, making AI discussions less intimidating at home.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="staffroom_impact[]" value="no_change" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'It has not yet altered wider staff or parental engagement trends.', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>
			</fieldset>

			<!-- ── STEP: strategic-roadmap ── Part 4 (participants only) -->
			<fieldset class="aiad-survey__step" data-step-id="strategic-roadmap" data-path="participant">
				<legend class="aiad-survey__step-title">
					<?php esc_html_e( 'Part 4 — Strategic roadmap for 2027', 'ai-awareness-day' ); ?>
				</legend>

				<!-- Q8: Bottleneck -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						8. <?php esc_html_e( 'What was the single biggest bottleneck you faced when executing the day?', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="bottleneck" value="time" class="aiad-survey__radio" />
							<?php esc_html_e( 'Time constraints: The school timetable/curriculum map is too packed to pause for a themed drop-down day.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="bottleneck" value="tech_barriers" class="aiad-survey__radio" />
							<?php esc_html_e( 'Tech barriers: School firewall restrictions blocked outbound links, video elements, or interactive platform modules.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="bottleneck" value="staff_hesitation" class="aiad-survey__radio" />
							<?php esc_html_e( 'Staff hesitation: Non-specialist teachers felt anxious or lacked the confidence to answer student questions about machine learning.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="bottleneck" value="physical_assets" class="aiad-survey__radio" />
							<?php esc_html_e( 'Lack of physical assets: We wanted ready-made physical display boards, sticker packs, or printed student toolkits.', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>

				<!-- Q9: Support modules for 2027 -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						9. <?php esc_html_e( 'What high-priority support modules should National AI Awareness Day add for 2027? (tick all that apply)', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__checkgroup aiad-survey__checkgroup--single">
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="support_modules[]" value="display_kits" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'Pre-packaged Display Board Kits: Digital and printable templates to instantly create interactive AI literacy spaces in school corridors.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="support_modules[]" value="cross_curricular" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'Cross-Curricular Schemes of Work: Explicit mapping showing how to weave the "Question It" principle into English, History, Art, and Science.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="support_modules[]" value="cpd_pathways" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'Structured Faculty CPD Pathways: Certified, 30-minute bite-sized training webinars for staff rooms ahead of the launch day.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="support_modules[]" value="pta_packs" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'Parent-Teacher Association (PTA) Interactive Packs: Step-by-step evening workshop templates to deliver direct to local families.', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>

				<!-- Q10: Open qualitative feedback -->
				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-open-feedback">
						10. <?php esc_html_e( 'Please share any open feedback from your classroom. What worked best, and what is one thing we must fix for 2027?', 'ai-awareness-day' ); ?>
					</label>
					<textarea class="aiad-survey__textarea" id="survey-open-feedback" name="open_feedback"
						rows="5" maxlength="2000"
						placeholder="<?php esc_attr_e( 'Share a specific classroom anecdote, a highlight, or a concrete fix…', 'ai-awareness-day' ); ?>"></textarea>
				</div>
			</fieldset>

			<!-- ── STEP: non-participant-a ── Part 1B block A (non-participants only) -->
			<fieldset class="aiad-survey__step" data-step-id="non-participant-a" data-path="non-participant">
				<legend class="aiad-survey__step-title">
					<?php esc_html_e( 'Part 1B — Understanding the barriers', 'ai-awareness-day' ); ?>
				</legend>

				<p class="aiad-survey__section-note">
					<?php esc_html_e( 'Understanding why schools did not take part is just as important as hearing from those who did. Your answers directly shape the 2027 campaign.', 'ai-awareness-day' ); ?>
				</p>

				<!-- Q1B-1: Primary reason for non-participation -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						1. <?php esc_html_e( 'What was the primary reason your school or classroom did not participate in National AI Awareness Day this year?', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="non_part_reason" value="timetable" class="aiad-survey__radio" />
							<?php esc_html_e( 'Curriculum/Timetable Pressure: We were locked into rigid end-of-term exam prep, mock assessments, or fixed assessment windows.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="non_part_reason" value="late_discovery" class="aiad-survey__radio" />
							<?php esc_html_e( 'Late Discovery: We only heard about the campaign a few days before 4th June and didn\'t have enough notice to get SLT sign-off.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="non_part_reason" value="staff_confidence" class="aiad-survey__radio" />
							<?php esc_html_e( 'Lack of Staff Confidence: Non-specialist staff felt anxious about answering student questions on machine learning and LLM ethics.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="non_part_reason" value="firewall" class="aiad-survey__radio" />
							<?php esc_html_e( 'Tech/Infrastructure Restrictions: Our school firewall completely blocks generative AI sandboxes (like ChatGPT/Claude), so we assumed the day wasn\'t viable.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="non_part_reason" value="banned_policy" class="aiad-survey__radio" />
							<?php esc_html_e( 'Banned Tool Policy: Our current school or MAT policy enforces an outright ban on AI discussion or interaction.', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>

				<!-- Q1B-2: Staff room attitude -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						2. <?php esc_html_e( 'Which of the following statements best describes the attitude toward AI tools within your school\'s current staff room?', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="staffroom_attitude" value="anxiety" class="aiad-survey__radio" />
							<?php esc_html_e( 'Anxiety/Resistance: Staff view AI primarily as a plagiarism hazard or a threat to traditional student writing development.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="staffroom_attitude" value="overwhelmed" class="aiad-survey__radio" />
							<?php esc_html_e( 'Overwhelmed: Teachers are interested in workload reduction but simply do not have the headspace or time to learn a new system.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="staffroom_attitude" value="isolated" class="aiad-survey__radio" />
							<?php esc_html_e( 'Interested but Isolated: Individual teachers are experimenting with AI prompts, but there is no joined-up, school-wide strategy.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="staffroom_attitude" value="apathy" class="aiad-survey__radio" />
							<?php esc_html_e( 'Apathy: Staff do not see AI as a high-priority issue for our specific demographic or age group right now.', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>
			</fieldset>

			<!-- ── STEP: non-participant-b ── Part 1B block B (non-participants only) -->
			<fieldset class="aiad-survey__step" data-step-id="non-participant-b" data-path="non-participant">
				<legend class="aiad-survey__step-title">
					<?php esc_html_e( 'Part 1B — Looking ahead to 2027', 'ai-awareness-day' ); ?>
				</legend>

				<!-- Q1B-3: 5-minute pledge -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						3. <?php esc_html_e( 'The campaign uses a "Just One Activity" pledge model requiring only 5 minutes of classroom time. Would this low-barrier structure make you consider participating in 2027?', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="five_min_pledge" value="yes" class="aiad-survey__radio" />
							<?php esc_html_e( 'Yes — a 5-minute tutor group starter or assembly slot is highly realistic to fit into our timetable.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="five_min_pledge" value="no" class="aiad-survey__radio" />
							<?php esc_html_e( 'No — even a 5-minute activity requires internal coordination and policy checks we cannot accommodate without formal notice.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="five_min_pledge" value="unsure" class="aiad-survey__radio" />
							<?php esc_html_e( 'Unsure — it depends entirely on whether the resource provides an explicit "plug-and-play" script for non-technical cover teachers.', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>

				<!-- Q1B-4: What would help onboarding -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						4. <?php esc_html_e( 'What could the central campaign team provide over the next 12 months to make onboarding seamless for your school in 2027? (tick all that apply)', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__checkgroup aiad-survey__checkgroup--single">
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="onboarding_needs[]" value="slt_toolkit" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'SLT Pledging Toolkits: Ready-made email templates and "business case" slide decks to secure formal sign-off from headteachers and governors.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="onboarding_needs[]" value="offline_packs" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'Off-line / "AI Unplugged" Resource Packs: Printed card games, sorting activities, and physical worksheets that teach AI logic without any devices or internet.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="onboarding_needs[]" value="prerecorded_assembly" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'Pre-recorded video assemblies: A completely hands-off, high-quality video file we can simply press play on, requiring zero teacher talking time.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="onboarding_needs[]" value="early_term_mapping" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'Early Term Mapping: Releasing the 2027 date and curriculum alignments 6 months in advance so we can bake it directly into our autumn calendar planning.', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>
			</fieldset>

			<!-- ── STEP: contact ── Stay in touch (everyone) -->
			<fieldset class="aiad-survey__step" data-step-id="contact">
				<legend class="aiad-survey__step-title">
					<?php esc_html_e( 'Stay in touch (optional)', 'ai-awareness-day' ); ?>
				</legend>

				<p class="aiad-survey__helper">
					<?php esc_html_e( 'Leave your details if you\'d like early notice of 2027, or if we may quote your feedback (anonymised) in our national report or AAAI submission.', 'ai-awareness-day' ); ?>
				</p>

				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-name">
						<?php esc_html_e( 'Your name', 'ai-awareness-day' ); ?>
					</label>
					<input class="aiad-survey__input" type="text" id="survey-name" name="contact_name"
						placeholder="<?php esc_attr_e( 'Optional', 'ai-awareness-day' ); ?>" maxlength="150" autocomplete="name" />
				</div>

				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-email">
						<?php esc_html_e( 'Email address', 'ai-awareness-day' ); ?>
					</label>
					<input class="aiad-survey__input" type="email" id="survey-email" name="contact_email"
						placeholder="<?php esc_attr_e( 'Optional', 'ai-awareness-day' ); ?>" maxlength="200" autocomplete="email" />
				</div>

				<div class="aiad-survey__field">
					<label class="aiad-survey__check-label">
						<input type="checkbox" name="permission_quote" value="1" class="aiad-survey__checkbox" />
						<?php esc_html_e( 'You may include an anonymised version of my feedback in public reports or research communications.', 'ai-awareness-day' ); ?>
					</label>
				</div>

				<!-- Honeypot -->
				<div class="aiad-survey__honeypot" aria-hidden="true">
					<input type="text" name="aiad_website" tabindex="-1" autocomplete="off" />
				</div>
			</fieldset>

			<!-- Navigation -->
			<div class="aiad-survey__nav">
				<button type="button" class="aiad-survey__btn aiad-survey__btn--back" id="aiad-survey-back" hidden>
					<?php esc_html_e( '← Back', 'ai-awareness-day' ); ?>
				</button>
				<button type="button" class="aiad-survey__btn aiad-survey__btn--next" id="aiad-survey-next">
					<?php esc_html_e( 'Next →', 'ai-awareness-day' ); ?>
				</button>
				<button type="submit" class="aiad-survey__btn aiad-survey__btn--submit" id="aiad-survey-submit" hidden>
					<?php esc_html_e( 'Submit survey', 'ai-awareness-day' ); ?>
				</button>
			</div>

			<div class="aiad-survey__error" id="aiad-survey-error" role="alert" hidden></div>

		</form>

		<!-- Success message -->
		<div class="aiad-survey__success" id="aiad-survey-success" hidden>
			<div class="aiad-survey__success-icon" aria-hidden="true">✓</div>
			<h2 class="aiad-survey__success-title">
				<?php esc_html_e( 'Thank you!', 'ai-awareness-day' ); ?>
			</h2>
			<p class="aiad-survey__success-body">
				<?php esc_html_e( 'Your response has been recorded and will help shape AI Awareness Day 2027.', 'ai-awareness-day' ); ?>
			</p>
		</div>

	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'aiad_national_survey', 'aiad_national_survey_shortcode' );

// ---------------------------------------------------------------------------
// AJAX handler
// ---------------------------------------------------------------------------

function aiad_handle_survey_submission(): void {
	check_ajax_referer( 'aiad_survey_nonce', 'nonce' );

	// Honeypot
	$honeypot = isset( $_POST['aiad_website'] ) ? sanitize_text_field( wp_unslash( $_POST['aiad_website'] ) ) : '';
	if ( $honeypot !== '' ) {
		wp_send_json_error( array( 'message' => __( 'Invalid submission.', 'ai-awareness-day' ) ) );
	}

	// Rate limit: 2 per fingerprint per hour
	$fingerprint = md5(
		( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' )
		. '|'
		. ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '' )
	);
	$rate_key = 'aiad_survey_rate_' . $fingerprint;
	$count    = (int) get_transient( $rate_key );
	if ( $count >= 2 ) {
		wp_send_json_error( array( 'message' => __( 'You have already submitted a response recently. Thank you!', 'ai-awareness-day' ) ) );
	}

	// Sanitise scalar fields
	$role                   = sanitize_text_field( wp_unslash( $_POST['role'] ?? '' ) );
	$mat_la                 = sanitize_text_field( wp_unslash( $_POST['mat_la'] ?? '' ) );
	$participated           = sanitize_text_field( wp_unslash( $_POST['participated'] ?? '' ) );
	$participation_scale    = sanitize_text_field( wp_unslash( $_POST['participation_scale'] ?? '' ) );
	$prep_time              = sanitize_text_field( wp_unslash( $_POST['prep_time'] ?? '' ) );
	$critical_thinking      = sanitize_text_field( wp_unslash( $_POST['critical_thinking_shift'] ?? '' ) );
	$bottleneck             = sanitize_text_field( wp_unslash( $_POST['bottleneck'] ?? '' ) );
	$open_feedback          = sanitize_textarea_field( wp_unslash( $_POST['open_feedback'] ?? '' ) );
	$non_part_reason        = sanitize_text_field( wp_unslash( $_POST['non_part_reason'] ?? '' ) );
	$staffroom_attitude     = sanitize_text_field( wp_unslash( $_POST['staffroom_attitude'] ?? '' ) );
	$five_min_pledge        = sanitize_text_field( wp_unslash( $_POST['five_min_pledge'] ?? '' ) );
	$contact_name           = sanitize_text_field( wp_unslash( $_POST['contact_name'] ?? '' ) );
	$contact_email          = sanitize_email( wp_unslash( $_POST['contact_email'] ?? '' ) );
	$perm_quote             = isset( $_POST['permission_quote'] ) ? '1' : '0';

	// Validate required field
	if ( empty( $role ) ) {
		wp_send_json_error( array( 'message' => __( 'Please tell us your role before submitting.', 'ai-awareness-day' ) ) );
	}

	// Sanitise array fields — participant path
	$allowed_staffroom = array( 'admin_workload', 'data_privacy', 'parents_engaged', 'no_change' );
	$raw_staffroom = isset( $_POST['staffroom_impact'] ) && is_array( $_POST['staffroom_impact'] )
		? array_map( 'sanitize_text_field', wp_unslash( $_POST['staffroom_impact'] ) )
		: array();
	$staffroom_impact = array_values( array_intersect( $raw_staffroom, $allowed_staffroom ) );

	$allowed_support = array( 'display_kits', 'cross_curricular', 'cpd_pathways', 'pta_packs' );
	$raw_support = isset( $_POST['support_modules'] ) && is_array( $_POST['support_modules'] )
		? array_map( 'sanitize_text_field', wp_unslash( $_POST['support_modules'] ) )
		: array();
	$support_modules = array_values( array_intersect( $raw_support, $allowed_support ) );

	// Non-participant path
	$allowed_onboarding = array( 'slt_toolkit', 'offline_packs', 'prerecorded_assembly', 'early_term_mapping' );
	$raw_onboarding = isset( $_POST['onboarding_needs'] ) && is_array( $_POST['onboarding_needs'] )
		? array_map( 'sanitize_text_field', wp_unslash( $_POST['onboarding_needs'] ) )
		: array();
	$onboarding_needs = array_values( array_intersect( $raw_onboarding, $allowed_onboarding ) );

	// Sanitise Likert ratings (1–5)
	$likert_fields = array( 'rating_plug_and_play', 'rating_student_access', 'rating_tech_delivery' );
	$ratings = array();
	foreach ( $likert_fields as $field ) {
		$val = (int) ( $_POST[ $field ] ?? 0 );
		$ratings[ $field ] = ( $val >= 1 && $val <= 5 ) ? $val : 0;
	}

	// Build a title for the admin list
	$title = sprintf(
		'%s — %s — %s',
		$role ?: 'unknown',
		$participated === 'yes' ? 'participant' : 'non-participant',
		gmdate( 'Y-m-d H:i' )
	);

	$post_id = wp_insert_post( array(
		'post_type'   => 'survey_response',
		'post_status' => 'publish',
		'post_title'  => $title,
	) );

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		wp_send_json_error( array( 'message' => __( 'Sorry, your response could not be saved. Please try again.', 'ai-awareness-day' ) ) );
	}

	$meta = array(
		// Part 1 — School profile
		'_survey_role'                  => $role,
		'_survey_mat_la'                => $mat_la,
		// Gate
		'_survey_participated'          => $participated,
		'_survey_participation_scale'   => $participation_scale,
		// Part 2 — Resource friction (participants)
		'_survey_prep_time'             => $prep_time,
		'_survey_rating_plug_and_play'  => $ratings['rating_plug_and_play'],
		'_survey_rating_student_access' => $ratings['rating_student_access'],
		'_survey_rating_tech_delivery'  => $ratings['rating_tech_delivery'],
		// Part 3 — Learning efficacy (participants)
		'_survey_critical_thinking'     => $critical_thinking,
		'_survey_staffroom_impact'      => wp_json_encode( $staffroom_impact ),
		// Part 4 — Strategic roadmap (participants)
		'_survey_bottleneck'            => $bottleneck,
		'_survey_support_modules'       => wp_json_encode( $support_modules ),
		'_survey_open_feedback'         => $open_feedback,
		// Part 1B — Non-participant track
		'_survey_non_part_reason'       => $non_part_reason,
		'_survey_staffroom_attitude'    => $staffroom_attitude,
		'_survey_five_min_pledge'       => $five_min_pledge,
		'_survey_onboarding_needs'      => wp_json_encode( $onboarding_needs ),
		// Contact
		'_survey_contact_name'          => $contact_name,
		'_survey_contact_email'         => $contact_email,
		'_survey_permission_quote'      => $perm_quote,
		'_survey_year'                  => '2026',
	);

	foreach ( $meta as $key => $value ) {
		update_post_meta( $post_id, $key, $value );
	}

	// Bump rate limit transient
	set_transient( $rate_key, $count + 1, HOUR_IN_SECONDS );

	wp_send_json_success( array( 'message' => __( 'Thank you for your response!', 'ai-awareness-day' ) ) );
}
add_action( 'wp_ajax_aiad_survey_submit', 'aiad_handle_survey_submission' );
add_action( 'wp_ajax_nopriv_aiad_survey_submit', 'aiad_handle_survey_submission' );

// ---------------------------------------------------------------------------
// Admin: meta box display
// ---------------------------------------------------------------------------

function aiad_survey_add_meta_box(): void {
	add_meta_box(
		'aiad-survey-response',
		__( 'Survey Response', 'ai-awareness-day' ),
		'aiad_survey_meta_box_render',
		'survey_response',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'aiad_survey_add_meta_box' );

function aiad_survey_meta_box_render( WP_Post $post ): void {
	$fields = array(
		'_survey_role'                  => __( 'Role', 'ai-awareness-day' ),
		'_survey_mat_la'                => __( 'MAT / Local Authority', 'ai-awareness-day' ),
		'_survey_participated'          => __( 'Participated on 4th June', 'ai-awareness-day' ),
		'_survey_participation_scale'   => __( 'Participation scale', 'ai-awareness-day' ),
		'_survey_prep_time'             => __( 'Prep time (Q4)', 'ai-awareness-day' ),
		'_survey_rating_plug_and_play'  => __( 'Rating: plug & play usability', 'ai-awareness-day' ),
		'_survey_rating_student_access' => __( 'Rating: student accessibility', 'ai-awareness-day' ),
		'_survey_rating_tech_delivery'  => __( 'Rating: technical delivery', 'ai-awareness-day' ),
		'_survey_critical_thinking'     => __( 'Critical thinking shift (Q6)', 'ai-awareness-day' ),
		'_survey_staffroom_impact'      => __( 'Staff room / parental impact (Q7)', 'ai-awareness-day' ),
		'_survey_bottleneck'            => __( 'Biggest bottleneck (Q8)', 'ai-awareness-day' ),
		'_survey_support_modules'       => __( 'Support modules for 2027 (Q9)', 'ai-awareness-day' ),
		'_survey_open_feedback'         => __( 'Open feedback (Q10)', 'ai-awareness-day' ),
		'_survey_non_part_reason'       => __( 'Non-participant: primary reason', 'ai-awareness-day' ),
		'_survey_staffroom_attitude'    => __( 'Non-participant: staff room attitude', 'ai-awareness-day' ),
		'_survey_five_min_pledge'       => __( 'Non-participant: 5-min pledge', 'ai-awareness-day' ),
		'_survey_onboarding_needs'      => __( 'Non-participant: onboarding needs', 'ai-awareness-day' ),
		'_survey_contact_name'          => __( 'Contact name', 'ai-awareness-day' ),
		'_survey_contact_email'         => __( 'Contact email', 'ai-awareness-day' ),
		'_survey_permission_quote'      => __( 'Quoting permission', 'ai-awareness-day' ),
	);

	echo '<table class="form-table"><tbody>';
	foreach ( $fields as $key => $label ) {
		$value = get_post_meta( $post->ID, $key, true );
		if ( $value === '' || $value === false ) {
			continue;
		}
		// Pretty-print JSON arrays
		if ( str_starts_with( (string) $value, '[' ) ) {
			$decoded = json_decode( (string) $value, true );
			$value   = is_array( $decoded ) ? implode( ', ', $decoded ) : $value;
		}
		echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>' . nl2br( esc_html( (string) $value ) ) . '</td></tr>';
	}
	echo '</tbody></table>';
}

// ---------------------------------------------------------------------------
// Admin: CSV export
// ---------------------------------------------------------------------------

function aiad_survey_admin_notices(): void {
	$screen = get_current_screen();
	if ( ! $screen || $screen->post_type !== 'survey_response' || $screen->base !== 'edit' ) {
		return;
	}

	// Success / error feedback after timeline action
	$timeline_result = isset( $_GET['aiad_timeline'] ) ? sanitize_key( $_GET['aiad_timeline'] ) : '';
	if ( $timeline_result === 'created' ) {
		$timeline_id  = isset( $_GET['tid'] ) ? (int) $_GET['tid'] : 0;
		$edit_url     = $timeline_id ? get_edit_post_link( $timeline_id ) : '';
		echo '<div class="notice notice-success is-dismissible"><p>';
		esc_html_e( 'Timeline entry created successfully.', 'ai-awareness-day' );
		if ( $edit_url ) {
			echo ' <a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit entry →', 'ai-awareness-day' ) . '</a>';
		}
		echo '</p></div>';
	} elseif ( $timeline_result === 'exists' ) {
		$timeline_id = isset( $_GET['tid'] ) ? (int) $_GET['tid'] : 0;
		$edit_url    = $timeline_id ? get_edit_post_link( $timeline_id ) : '';
		echo '<div class="notice notice-warning is-dismissible"><p>';
		esc_html_e( 'A survey timeline entry already exists.', 'ai-awareness-day' );
		if ( $edit_url ) {
			echo ' <a href="' . esc_url( $edit_url ) . '">' . esc_html__( 'Edit entry →', 'ai-awareness-day' ) . '</a>';
		}
		echo '</p></div>';
	} elseif ( $timeline_result === 'error' ) {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Could not create the timeline entry. Please try again.', 'ai-awareness-day' ) . '</p></div>';
	}

	$survey_exists = aiad_survey_timeline_entry_exists();
	?>
	<div class="aiad-survey-export-bar" style="margin:16px 0 0; display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
		<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'aiad_export_survey_csv', '_wpnonce' => wp_create_nonce( 'aiad_export_survey_csv' ) ), admin_url( 'admin-post.php' ) ) ); ?>"
			class="button button-primary">
			<?php esc_html_e( 'Export all responses to CSV', 'ai-awareness-day' ); ?>
		</a>
		<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'aiad_survey_add_to_timeline', '_wpnonce' => wp_create_nonce( 'aiad_survey_add_to_timeline' ) ), admin_url( 'admin-post.php' ) ) ); ?>"
			class="button<?php echo $survey_exists ? ' disabled' : ''; ?>"
			<?php echo $survey_exists ? 'aria-disabled="true"' : ''; ?>>
			<?php echo $survey_exists ? esc_html__( 'Survey entry already exists', 'ai-awareness-day' ) : esc_html__( 'Add survey to Timeline', 'ai-awareness-day' ); ?>
		</a>
	</div>
	<?php
}
add_action( 'admin_notices', 'aiad_survey_admin_notices' );

/**
 * Check whether a timeline entry for the national survey already exists.
 */
function aiad_survey_timeline_entry_exists(): bool {
	$existing = get_posts( array(
		'post_type'      => 'timeline',
		'post_status'    => array( 'publish', 'draft', 'pending', 'future' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'   => '_aiad_timeline_auto_type',
				'value' => 'national_survey_2026',
			),
		),
	) );
	return ! empty( $existing );
}

/**
 * Get the existing survey timeline entry ID (0 if none).
 */
function aiad_survey_get_timeline_entry_id(): int {
	$existing = get_posts( array(
		'post_type'      => 'timeline',
		'post_status'    => array( 'publish', 'draft', 'pending', 'future' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'   => '_aiad_timeline_auto_type',
				'value' => 'national_survey_2026',
			),
		),
	) );
	return ! empty( $existing ) ? (int) $existing[0] : 0;
}

/**
 * Admin-post handler: create the survey timeline entry.
 */
function aiad_survey_add_to_timeline_handler(): void {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Permission denied.', 'ai-awareness-day' ) );
	}
	check_admin_referer( 'aiad_survey_add_to_timeline' );

	$redirect = admin_url( 'edit.php?post_type=survey_response' );

	// Return early if entry already exists
	$existing_id = aiad_survey_get_timeline_entry_id();
	if ( $existing_id > 0 ) {
		wp_safe_redirect( add_query_arg( array( 'aiad_timeline' => 'exists', 'tid' => $existing_id ), $redirect ) );
		exit;
	}

	// Try to find the survey page automatically (page using the shortcode)
	$survey_page = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		's'              => 'aiad_national_survey',
	) );
	$survey_url = ! empty( $survey_page ) ? get_permalink( $survey_page[0] ) : home_url( '/survey/' );

	// Create the timeline entry via the shared helper
	$post_id = aiad_create_timeline_entry( array(
		'title'      => __( '📋 National Survey — Share your AI Awareness Day experience', 'ai-awareness-day' ),
		'content'    => '<p>' . esc_html__( 'We want to hear from you! Tell us how AI Awareness Day 2026 went for your school or organisation, and share your ideas for 2027. The survey takes around 3 minutes.', 'ai-awareness-day' ) . '</p>',
		'auto_type'  => 'national_survey_2026',
		'icon'       => 'announcement',
		'related_id' => 0,
		'link_url'   => (string) $survey_url,
		'link_label' => __( 'Take the survey →', 'ai-awareness-day' ),
		'post_name'  => 'national-survey-2026',
	) );

	if ( ! $post_id ) {
		wp_safe_redirect( add_query_arg( 'aiad_timeline', 'error', $redirect ) );
		exit;
	}

	// Pin it so it appears prominently
	update_post_meta( (int) $post_id, '_aiad_timeline_pinned', true );

	wp_safe_redirect( add_query_arg( array( 'aiad_timeline' => 'created', 'tid' => $post_id ), $redirect ) );
	exit;
}
add_action( 'admin_post_aiad_survey_add_to_timeline', 'aiad_survey_add_to_timeline_handler' );

// ---------------------------------------------------------------------------
// "National Moment" timeline entry
// ---------------------------------------------------------------------------

function aiad_national_moment_timeline_entry_exists(): bool {
	$existing = get_posts( array(
		'post_type'      => 'timeline',
		'post_status'    => array( 'publish', 'draft', 'pending', 'future' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'   => '_aiad_timeline_auto_type',
				'value' => 'national_moment_2026',
			),
		),
	) );
	return ! empty( $existing );
}

function aiad_national_moment_get_timeline_entry_id(): int {
	$existing = get_posts( array(
		'post_type'      => 'timeline',
		'post_status'    => array( 'publish', 'draft', 'pending', 'future' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'   => '_aiad_timeline_auto_type',
				'value' => 'national_moment_2026',
			),
		),
	) );
	return ! empty( $existing ) ? (int) $existing[0] : 0;
}

/**
 * Seed the "national moment" timeline entry once on init if it doesn't exist yet.
 */
function aiad_national_moment_seed_timeline_entry(): void {
	if ( get_option( 'aiad_national_moment_2026_seeded' ) ) {
		return;
	}
	if ( aiad_national_moment_timeline_entry_exists() ) {
		update_option( 'aiad_national_moment_2026_seeded', true );
		return;
	}

	$content = '<p>Yesterday, we did something historic.</p>'
		. '<p>AI Awareness Day 2026 became a genuine national moment — and it exceeded every expectation we had. Multi-academy trusts, schools, charities, EdTech companies, universities, and grassroots organisations across the UK came together to bring artificial intelligence education into classrooms, assemblies, and homes on the same day.</p>'
		. '<h3>The numbers</h3>'
		. '<ul>'
		. '<li><strong>750,000+ students</strong> engaged with AI Awareness Day activities — with a path to <strong>1 million</strong> in the coming months</li>'
		. '<li><strong>1 million+ reach</strong> through MATs, charities, and educational providers</li>'
		. '<li><strong>Hundreds of thousands</strong> of social media engagements across LinkedIn, X, Facebook, and Instagram</li>'
		. '</ul>'
		. '<p>This was not a single school, a single organisation, or a single moment. It was a movement.</p>'
		. '<h3>What comes next</h3>'
		. '<p>AI Awareness Day was the launchpad, not the finish line. Browse the full resource library, watch YouTube Shorts featuring AI education leaders, and attend partner events throughout June 2026. Our Mini Masterclass series is growing — submit your tips and insights to be featured.</p>'
		. '<p><em>Know it. Question it. Use it wisely.</em></p>';

	$post_id = aiad_create_timeline_entry( array(
		'title'      => 'The Day After. And What a Day It Was. 🎉',
		'content'    => $content,
		'auto_type'  => 'national_moment_2026',
		'icon'       => 'milestone',
		'related_id' => 0,
		'link_url'   => 'https://aiawarenessday.co.uk/timeline/the-day-after-and-what-a-day-it-was-%f0%9f%8e%89/',
		'link_label' => 'Read the full story →',
		'post_name'  => 'the-day-after-and-what-a-day-it-was',
	) );

	if ( $post_id ) {
		update_post_meta( (int) $post_id, '_aiad_timeline_pinned', true );
		update_option( 'aiad_national_moment_2026_seeded', true );
	}
}
add_action( 'init', 'aiad_national_moment_seed_timeline_entry', 40 );

function aiad_export_survey_csv(): void {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Permission denied.', 'ai-awareness-day' ) );
	}
	check_admin_referer( 'aiad_export_survey_csv' );

	$posts = get_posts( array(
		'post_type'      => 'survey_response',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'ASC',
	) );

	$columns = array(
		'ID', 'Date', 'Role', 'MAT / LA', 'Participated', 'Participation scale',
		'Prep time', 'Rating: plug & play', 'Rating: student access', 'Rating: tech delivery',
		'Critical thinking shift', 'Staff room impact', 'Bottleneck', 'Support modules 2027',
		'Open feedback',
		'Non-part: reason', 'Non-part: staff room attitude', 'Non-part: 5-min pledge', 'Non-part: onboarding needs',
		'Contact name', 'Contact email', 'Quoting permission',
	);

	$meta_keys = array(
		'_survey_role', '_survey_mat_la', '_survey_participated', '_survey_participation_scale',
		'_survey_prep_time',
		'_survey_rating_plug_and_play', '_survey_rating_student_access', '_survey_rating_tech_delivery',
		'_survey_critical_thinking', '_survey_staffroom_impact', '_survey_bottleneck',
		'_survey_support_modules', '_survey_open_feedback',
		'_survey_non_part_reason', '_survey_staffroom_attitude', '_survey_five_min_pledge', '_survey_onboarding_needs',
		'_survey_contact_name', '_survey_contact_email', '_survey_permission_quote',
	);

	header( 'Content-Type: text/csv; charset=UTF-8' );
	header( 'Content-Disposition: attachment; filename="aiad-survey-responses-' . gmdate( 'Y-m-d' ) . '.csv"' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );

	$out = fopen( 'php://output', 'w' );
	fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ); // UTF-8 BOM for Excel
	fputcsv( $out, $columns );

	foreach ( $posts as $post ) {
		$row = array( $post->ID, $post->post_date );
		foreach ( $meta_keys as $key ) {
			$value = get_post_meta( $post->ID, $key, true );
			if ( str_starts_with( (string) $value, '[' ) ) {
				$decoded = json_decode( (string) $value, true );
				$value   = is_array( $decoded ) ? implode( '; ', $decoded ) : $value;
			}
			$row[] = (string) $value;
		}
		fputcsv( $out, $row );
	}

	fclose( $out );
	exit;
}
add_action( 'admin_post_aiad_export_survey_csv', 'aiad_export_survey_csv' );
