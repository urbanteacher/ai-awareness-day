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

/** Survey schema version — v1.0 = launch; v1.1 = reach/impact questions (June 2026). */
define( 'AIAD_SURVEY_VERSION', '1.1' );

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
 * Steps (grouped multi-step UX — one theme per screen):
 *   1 – School profile (everyone)
 *   2 – School AI readiness (everyone)
 *   3p – Hopes & student empowerment (participants)
 *   3n – Understanding barriers (non-participants)
 *   4p – Resources, impact & 2027 planning (participants)
 *   5  – Reach & impact (everyone; path-specific fields)
 *   6  – Stay in touch (everyone)
 *
 * @param array<string, string>|string $atts Unused.
 */
function aiad_national_survey_shortcode( $atts = array() ): string {
	aiad_enqueue_national_survey_assets();

	ob_start();
	?>
	<div class="aiad-survey" id="aiad-national-survey" role="main" aria-label="<?php esc_attr_e( 'AI Awareness Day National Survey', 'ai-awareness-day' ); ?>">

		<!-- Progress: segmented stepper (built by JS, one segment per step in the active path) -->
		<div class="aiad-survey__progress" id="aiad-survey-progress">
			<div class="aiad-survey__stepper" id="aiad-survey-stepper" role="list" aria-hidden="true"></div>
			<p class="aiad-survey__progress-label" id="aiad-survey-progress-label" aria-live="polite"></p>
		</div>

		<form class="aiad-survey__form" id="aiad-survey-form" novalidate>

			<!-- ── STEP: profile ── Part 1: School Profile & Contextual Baselines -->
			<fieldset class="aiad-survey__step" data-step-id="profile" aria-labelledby="aiad-survey-step-profile-title">
				<h2 class="aiad-survey__step-title" id="aiad-survey-step-profile-title">
					<?php esc_html_e( 'Part 1 — School profile', 'ai-awareness-day' ); ?>
				</h2>

				<?php
				$year_groups = array(
					'eyfs'  => __( 'EYFS', 'ai-awareness-day' ),
					'ks1'   => __( 'KS1 (Y1–Y2)', 'ai-awareness-day' ),
					'ks2'   => __( 'KS2 (Y3–Y6)', 'ai-awareness-day' ),
					'ks3'   => __( 'KS3 (Y7–Y9)', 'ai-awareness-day' ),
					'ks4'   => __( 'KS4 (Y10–Y11)', 'ai-awareness-day' ),
					'ks5'   => __( 'KS5 / 6th Form', 'ai-awareness-day' ),
					'staff' => __( 'Staff CPD only', 'ai-awareness-day' ),
				);
				?>

				<!-- Q1: School type -->
				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-school-type">
						1. <?php esc_html_e( 'School type', 'ai-awareness-day' ); ?>
					</label>
					<select class="aiad-survey__select" id="survey-school-type" name="school_type">
						<option value=""><?php esc_html_e( '— Select —', 'ai-awareness-day' ); ?></option>
						<option value="primary"><?php esc_html_e( 'Primary', 'ai-awareness-day' ); ?></option>
						<option value="secondary"><?php esc_html_e( 'Secondary', 'ai-awareness-day' ); ?></option>
						<option value="all_through"><?php esc_html_e( 'All-through (4–18)', 'ai-awareness-day' ); ?></option>
						<option value="special"><?php esc_html_e( 'Special / Alternative Provision', 'ai-awareness-day' ); ?></option>
						<option value="fe_college"><?php esc_html_e( 'FE College / Sixth Form', 'ai-awareness-day' ); ?></option>
						<option value="higher_education"><?php esc_html_e( 'Higher Education', 'ai-awareness-day' ); ?></option>
						<option value="mat_trust"><?php esc_html_e( 'MAT / Trust (multiple schools)', 'ai-awareness-day' ); ?></option>
					</select>
				</div>

				<!-- Q2: Year groups -->
				<div class="aiad-survey__field" id="survey-year-groups-wrap">
					<p class="aiad-survey__label">
						2. <?php esc_html_e( 'Which year groups do you work with? (tick all that apply)', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__checkgroup">
						<?php foreach ( $year_groups as $val => $label ) : ?>
							<label class="aiad-survey__check-label">
								<input type="checkbox" name="year_groups[]" value="<?php echo esc_attr( $val ); ?>" class="aiad-survey__checkbox" />
								<?php echo esc_html( $label ); ?>
							</label>
						<?php endforeach; ?>
					</div>
				</div>

				<!-- Q3: Role -->
				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-role">
						3. <?php esc_html_e( 'What is your role within your institution?', 'ai-awareness-day' ); ?>
						<span class="aiad-survey__required" aria-hidden="true">*</span>
					</label>
					<input class="aiad-survey__input" type="text" id="survey-role" name="role" required
						placeholder="<?php esc_attr_e( 'e.g. Head teacher, Deputy, Computing lead', 'ai-awareness-day' ); ?>"
						maxlength="200" autocomplete="organization-title" />
				</div>

				<!-- Q4: Participation gate -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						4. <?php esc_html_e( 'Did your school or classroom participate in National AI Awareness Day on 4th June 2026?', 'ai-awareness-day' ); ?>
						<span class="aiad-survey__required" aria-hidden="true">*</span>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="participated" value="yes" class="aiad-survey__radio" id="survey-participated-yes" required />
							<?php esc_html_e( 'Yes — we took part', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="participated" value="no" class="aiad-survey__radio" id="survey-participated-no" required />
							<?php esc_html_e( 'No — we did not participate', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>

				<!-- Q5: Participation scale — only shown if "yes" (JS toggles visibility) -->
				<div class="aiad-survey__field" id="survey-participation-scale-wrap">
					<p class="aiad-survey__label">
						5. <?php esc_html_e( 'What was the approximate level of student participation in your school on 4th June?', 'ai-awareness-day' ); ?>
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

			<!-- ── STEP: school-readiness ── Display board & policy (everyone, separate from participation gate) -->
			<fieldset class="aiad-survey__step" data-step-id="school-readiness" aria-labelledby="aiad-survey-step-school-readiness-title">
				<h2 class="aiad-survey__step-title" id="aiad-survey-step-school-readiness-title">
					<?php esc_html_e( 'School AI readiness', 'ai-awareness-day' ); ?>
				</h2>

				<p class="aiad-survey__helper">
					<?php esc_html_e( 'A quick snapshot of how AI Awareness is taking shape in your school — from the curriculum to corridors and classrooms.', 'ai-awareness-day' ); ?>
				</p>

				<!-- Q1: AI in curriculum -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						1. <?php esc_html_e( 'Have you embedded AI activities into your curriculum?', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="curriculum_embedded" value="yes" class="aiad-survey__radio" />
							<?php esc_html_e( 'Yes — AI activities are part of our curriculum.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="curriculum_embedded" value="in_development" class="aiad-survey__radio" />
							<?php esc_html_e( 'In development — we are starting to embed AI activities.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="curriculum_embedded" value="no" class="aiad-survey__radio" />
							<?php esc_html_e( 'Not yet — we have not embedded AI activities.', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>

				<!-- Q2: Display board -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						2. <?php esc_html_e( 'As you may have seen through AI Awareness Day, does your school have or are you considering an AI Awareness display board to raise literacy within corridors or classrooms?', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="display_board" value="yes" class="aiad-survey__radio" />
							<?php esc_html_e( 'Yes — we have one on display.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="display_board" value="in_development" class="aiad-survey__radio" />
							<?php esc_html_e( 'In development — we are putting one together.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="display_board" value="no" class="aiad-survey__radio" />
							<?php esc_html_e( 'No — we do not have one.', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>

				<!-- Q3: AI policy -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						3. <?php esc_html_e( 'Does your school have an AI Awareness / AI-use policy?', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="ai_policy" value="yes" class="aiad-survey__radio" />
							<?php esc_html_e( 'Yes — we have a policy in place.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="ai_policy" value="in_development" class="aiad-survey__radio" />
							<?php esc_html_e( 'In development — a policy is being written.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="ai_policy" value="no" class="aiad-survey__radio" />
							<?php esc_html_e( 'No — we do not have one yet.', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>
			</fieldset>

			<!-- ── STEP: hopes ── Part 1: Hopes, Attitudinal Shifts & Student Empowerment (participants only) -->
			<fieldset class="aiad-survey__step" data-step-id="hopes" data-path="participant" aria-labelledby="aiad-survey-step-hopes-title">
				<h2 class="aiad-survey__step-title" id="aiad-survey-step-hopes-title">
					<?php esc_html_e( 'Part 1 — Hopes, attitudinal shifts &amp; student empowerment', 'ai-awareness-day' ); ?>
				</h2>

				<p class="aiad-survey__section-note">
					<?php esc_html_e( 'This section tracks whether the campaign succeeded in changing students\' mindsets from passive technology consumers to empowered, confident digital architects.', 'ai-awareness-day' ); ?>
				</p>

				<!-- Q1: Primary hope -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						1. <?php esc_html_e( 'What was your primary hope for your students when signing up for AI Awareness Day?', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="primary_hope" value="demystify" class="aiad-survey__radio" />
							<?php esc_html_e( 'To lower their anxiety and demystify how artificial intelligence works.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="primary_hope" value="confidence_skills" class="aiad-survey__radio" />
							<?php esc_html_e( 'To give them the confidence and practical skills to use AI tools for creative problem solving.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="primary_hope" value="digital_resilience" class="aiad-survey__radio" />
							<?php esc_html_e( 'To build their digital resilience and teach them to critically check for fake or biased information.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="primary_hope" value="career_interest" class="aiad-survey__radio" />
							<?php esc_html_e( 'To spark their interest in computer science, machine learning, and future tech careers.', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>

				<!-- Q2: Immediate effect Likert scales -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						2. <?php esc_html_e( 'Rate the immediate effect of the campaign day on your students\' confidence and learning habits (1 = Strongly Disagree, 5 = Strongly Agree):', 'ai-awareness-day' ); ?>
					</p>

					<?php
					$empowerment_likerts = array(
						'rating_student_empowerment' => __(
							'Student Empowerment: Students showed greater confidence using AI tools as an active brainstorming partner rather than just a quick cheating shortcut.',
							'ai-awareness-day'
						),
						'rating_critical_skepticism' => __(
							'Critical Scepticism: The activities successfully gave students a healthy scepticism, helping them spot hallucinations and biased data trends.',
							'ai-awareness-day'
						),
						'rating_inclusivity' => __(
							'Inclusivity & Access: The jargon-free, interactive format gave non-technical and less confident students an equal voice in class discussions.',
							'ai-awareness-day'
						),
					);
					foreach ( $empowerment_likerts as $name => $label ) :
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

				<!-- Q3: Lasting classroom effect -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						3. <?php esc_html_e( 'Did the campaign leave a lasting positive effect on your classroom environment?', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="lasting_effect" value="significant" class="aiad-survey__radio" />
							<?php esc_html_e( 'Yes — it completely changed the dynamic. Students are now routinely questioning and checking text/image generator outputs.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="lasting_effect" value="moderate" class="aiad-survey__radio" />
							<?php esc_html_e( 'Moderate effect — it raised general awareness about data privacy, but students still treat algorithmic outputs as mostly correct.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="lasting_effect" value="none" class="aiad-survey__radio" />
							<?php esc_html_e( 'No measurable effect — students enjoyed the drop-down activity but quickly went back to using AI as an unchecked pass-through answer generator.', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>
			</fieldset>

			<!-- ── STEP: participant-feedback ── Resources, impact & 2027 (participants only) -->
			<fieldset class="aiad-survey__step" data-step-id="participant-feedback" data-path="participant" aria-labelledby="aiad-survey-step-participant-feedback-title">
				<h2 class="aiad-survey__step-title" id="aiad-survey-step-participant-feedback-title">
					<?php esc_html_e( 'Your experience &amp; planning for 2027', 'ai-awareness-day' ); ?>
				</h2>

				<p class="aiad-survey__helper">
					<?php esc_html_e( 'Tell us how the resources worked in practice, what changed in your school, and what would help next time.', 'ai-awareness-day' ); ?>
				</p>

				<h3 class="aiad-survey__subsection-title">
					<?php esc_html_e( 'Resources &amp; classroom delivery', 'ai-awareness-day' ); ?>
				</h3>

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

				<!-- Q6: Materials adequacy -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						6. <?php esc_html_e( 'Overall, how well did the AI Awareness Day teaching materials meet your needs?', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="materials_quality" value="ideal" class="aiad-survey__radio" />
							<?php esc_html_e( 'Ideal — comprehensive and ready to teach, exceeded what we needed.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="materials_quality" value="adequate" class="aiad-survey__radio" />
							<?php esc_html_e( 'Adequate — enough to deliver a solid session without much extra work.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="materials_quality" value="basic" class="aiad-survey__radio" />
							<?php esc_html_e( 'Basic — usable, but we had to supplement them with our own resources.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="materials_quality" value="inadequate" class="aiad-survey__radio" />
							<?php esc_html_e( 'Inadequate — not enough to teach from confidently.', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>

				<!-- Q7: Which session formats proved useful -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						7. <?php esc_html_e( 'Which session formats proved useful in your setting? (tick all that worked)', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__checkgroup aiad-survey__checkgroup--single">
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="useful_formats[]" value="starter_5" class="aiad-survey__checkbox" />
							<?php esc_html_e( '5-minute lesson starter', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="useful_formats[]" value="tutor_15" class="aiad-survey__checkbox" />
							<?php esc_html_e( '15-minute tutor-group session', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="useful_formats[]" value="assembly_20" class="aiad-survey__checkbox" />
							<?php esc_html_e( '20-minute assembly slides', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="useful_formats[]" value="none" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'None of these lengths fitted our timetable', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>

				<!-- Q8: Best content format -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						8. <?php esc_html_e( 'Which content format works best for your classroom?', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="best_format" value="video" class="aiad-survey__radio" />
							<?php esc_html_e( 'Video — ready to play, minimal teacher input.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="best_format" value="slides" class="aiad-survey__radio" />
							<?php esc_html_e( 'Presentation slides we can deliver ourselves.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="best_format" value="teacher_instructions" class="aiad-survey__radio" />
							<?php esc_html_e( 'Teacher breakdown — step-by-step instructions and talking points.', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="best_format" value="mix" class="aiad-survey__radio" />
							<?php esc_html_e( 'A mix — no single format stood out.', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>

				<h3 class="aiad-survey__subsection-title">
					<?php esc_html_e( 'Staff room &amp; wider impact', 'ai-awareness-day' ); ?>
				</h3>

				<!-- Staff room / parental network impact -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						9. <?php esc_html_e( 'How has the campaign impacted your staff room or parental network? (Select all that apply)', 'ai-awareness-day' ); ?>
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

				<h3 class="aiad-survey__subsection-title">
					<?php esc_html_e( 'Planning for 2027', 'ai-awareness-day' ); ?>
				</h3>

				<!-- Bottleneck -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						10. <?php esc_html_e( 'What was the single biggest bottleneck you faced when executing the day?', 'ai-awareness-day' ); ?>
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
						11. <?php esc_html_e( 'What high-priority support modules should National AI Awareness Day add for 2027? (tick all that apply)', 'ai-awareness-day' ); ?>
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
						12. <?php esc_html_e( 'Please share any open feedback from your classroom. What worked best, and what is one thing we must fix for 2027?', 'ai-awareness-day' ); ?>
					</label>
					<textarea class="aiad-survey__textarea" id="survey-open-feedback" name="open_feedback"
						rows="5" maxlength="2000"
						placeholder="<?php esc_attr_e( 'Share a specific classroom anecdote, a highlight, or a concrete fix…', 'ai-awareness-day' ); ?>"></textarea>
				</div>
			</fieldset>

			<!-- ── STEP: non-participant-a ── Part 1B block A (non-participants only) -->
			<fieldset class="aiad-survey__step" data-step-id="non-participant-a" data-path="non-participant" aria-labelledby="aiad-survey-step-non-participant-a-title">
				<h2 class="aiad-survey__step-title" id="aiad-survey-step-non-participant-a-title">
					<?php esc_html_e( 'Part 1B — Understanding the barriers', 'ai-awareness-day' ); ?>
				</h2>

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

			<!-- ── STEP: reach-impact ── Reach, confidence & next steps (everyone; path-specific fields) -->
			<fieldset class="aiad-survey__step" data-step-id="reach-impact" aria-labelledby="aiad-survey-step-reach-impact-title">
				<h2 class="aiad-survey__step-title" id="aiad-survey-step-reach-impact-title">
					<?php esc_html_e( 'Reach &amp; impact', 'ai-awareness-day' ); ?>
				</h2>

				<p class="aiad-survey__helper">
					<?php esc_html_e( 'A few quick questions to help us measure reach and plan for 2027.', 'ai-awareness-day' ); ?>
				</p>

				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						1. <?php esc_html_e( 'How did you hear about AI Awareness Day?', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="hear_about" value="social" class="aiad-survey__radio" />
							<?php esc_html_e( 'Social media', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="hear_about" value="email" class="aiad-survey__radio" />
							<?php esc_html_e( 'Email or newsletter', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="hear_about" value="colleague" class="aiad-survey__radio" />
							<?php esc_html_e( 'Colleague or word of mouth', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="hear_about" value="mat_trust" class="aiad-survey__radio" />
							<?php esc_html_e( 'MAT / trust communication', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="hear_about" value="dfe_org" class="aiad-survey__radio" />
							<?php esc_html_e( 'DfE or other organisation', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="hear_about" value="search" class="aiad-survey__radio" />
							<?php esc_html_e( 'Web search', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="hear_about" value="other" class="aiad-survey__radio" />
							<?php esc_html_e( 'Other', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>

				<div class="aiad-survey__path-participant" hidden>
					<div class="aiad-survey__field">
						<p class="aiad-survey__label">
							2. <?php esc_html_e( 'Before AI Awareness Day, how confident did you feel discussing AI in education?', 'ai-awareness-day' ); ?>
						</p>
						<div class="aiad-survey__radio-group">
							<?php
							$confidence_options = array(
								'very'     => __( 'Very confident', 'ai-awareness-day' ),
								'fairly'   => __( 'Fairly confident', 'ai-awareness-day' ),
								'not_very' => __( 'Not very confident', 'ai-awareness-day' ),
								'not'      => __( 'Not confident', 'ai-awareness-day' ),
							);
							foreach ( $confidence_options as $val => $label ) :
								?>
								<label class="aiad-survey__radio-label">
									<input type="radio" name="confidence_before" value="<?php echo esc_attr( $val ); ?>" class="aiad-survey__radio" />
									<?php echo esc_html( $label ); ?>
								</label>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="aiad-survey__field">
						<p class="aiad-survey__label">
							3. <?php esc_html_e( 'After taking part, how confident do you feel discussing AI in education?', 'ai-awareness-day' ); ?>
						</p>
						<div class="aiad-survey__radio-group">
							<?php foreach ( $confidence_options as $val => $label ) : ?>
								<label class="aiad-survey__radio-label">
									<input type="radio" name="confidence_after" value="<?php echo esc_attr( $val ); ?>" class="aiad-survey__radio" />
									<?php echo esc_html( $label ); ?>
								</label>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="aiad-survey__field">
						<p class="aiad-survey__label">
							4. <?php esc_html_e( 'As a result of AI Awareness Day, what is the one action you intend to take next?', 'ai-awareness-day' ); ?>
						</p>
						<div class="aiad-survey__radio-group">
							<label class="aiad-survey__radio-label">
								<input type="radio" name="intended_action" value="learn_more" class="aiad-survey__radio" />
								<?php esc_html_e( 'Learn more about AI', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="intended_action" value="review_policy" class="aiad-survey__radio" />
								<?php esc_html_e( 'Review school policy', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="intended_action" value="discuss_students" class="aiad-survey__radio" />
								<?php esc_html_e( 'Discuss AI with students', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="intended_action" value="discuss_child" class="aiad-survey__radio" />
								<?php esc_html_e( 'Discuss AI with my child', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="intended_action" value="safeguarding" class="aiad-survey__radio" />
								<?php esc_html_e( 'Improve safeguarding processes', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="intended_action" value="assessment" class="aiad-survey__radio" />
								<?php esc_html_e( 'Review assessment approaches', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="intended_action" value="explore_tools" class="aiad-survey__radio" />
								<?php esc_html_e( 'Explore AI tools', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="intended_action" value="no_action" class="aiad-survey__radio" />
								<?php esc_html_e( 'No immediate action', 'ai-awareness-day' ); ?>
							</label>
						</div>
					</div>
				</div>

				<div class="aiad-survey__path-non-participant" hidden>
					<div class="aiad-survey__field">
						<p class="aiad-survey__label">
							2. <?php esc_html_e( 'Had you heard of AI Awareness Day before today?', 'ai-awareness-day' ); ?>
						</p>
						<div class="aiad-survey__radio-group">
							<label class="aiad-survey__radio-label">
								<input type="radio" name="heard_before" value="yes" class="aiad-survey__radio" />
								<?php esc_html_e( 'Yes', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="heard_before" value="no" class="aiad-survey__radio" />
								<?php esc_html_e( 'No', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="heard_before" value="not_sure" class="aiad-survey__radio" />
								<?php esc_html_e( 'Not sure', 'ai-awareness-day' ); ?>
							</label>
						</div>
					</div>

					<div class="aiad-survey__field">
						<p class="aiad-survey__label">
							3. <?php esc_html_e( 'What concerns you most about AI in education?', 'ai-awareness-day' ); ?>
						</p>
						<div class="aiad-survey__radio-group">
							<label class="aiad-survey__radio-label">
								<input type="radio" name="top_concern" value="misinformation" class="aiad-survey__radio" />
								<?php esc_html_e( 'Misinformation and unreliable outputs', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="top_concern" value="privacy" class="aiad-survey__radio" />
								<?php esc_html_e( 'Privacy and data protection', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="top_concern" value="safeguarding" class="aiad-survey__radio" />
								<?php esc_html_e( 'Safeguarding and online harm', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="top_concern" value="deepfakes" class="aiad-survey__radio" />
								<?php esc_html_e( 'Deepfakes and impersonation', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="top_concern" value="integrity" class="aiad-survey__radio" />
								<?php esc_html_e( 'Academic integrity and cheating', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="top_concern" value="bias" class="aiad-survey__radio" />
								<?php esc_html_e( 'Bias and fairness', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="top_concern" value="over_reliance" class="aiad-survey__radio" />
								<?php esc_html_e( 'Over-reliance on AI', 'ai-awareness-day' ); ?>
							</label>
						</div>
					</div>

					<div class="aiad-survey__field">
						<p class="aiad-survey__label">
							4. <?php esc_html_e( 'What support would help your school take part next year? (tick all that apply)', 'ai-awareness-day' ); ?>
						</p>
						<div class="aiad-survey__checkgroup aiad-survey__checkgroup--single">
							<label class="aiad-survey__check-label">
								<input type="checkbox" name="support_needed[]" value="training" class="aiad-survey__checkbox" />
								<?php esc_html_e( 'Staff training / CPD', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__check-label">
								<input type="checkbox" name="support_needed[]" value="policy" class="aiad-survey__checkbox" />
								<?php esc_html_e( 'Policy templates and governance guidance', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__check-label">
								<input type="checkbox" name="support_needed[]" value="parent_resources" class="aiad-survey__checkbox" />
								<?php esc_html_e( 'Parent guides and family resources', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__check-label">
								<input type="checkbox" name="support_needed[]" value="student_resources" class="aiad-survey__checkbox" />
								<?php esc_html_e( 'Student classroom resources', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__check-label">
								<input type="checkbox" name="support_needed[]" value="safeguarding" class="aiad-survey__checkbox" />
								<?php esc_html_e( 'Safeguarding guidance', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__check-label">
								<input type="checkbox" name="support_needed[]" value="case_studies" class="aiad-survey__checkbox" />
								<?php esc_html_e( 'Case studies from other schools', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__check-label">
								<input type="checkbox" name="support_needed[]" value="webinars" class="aiad-survey__checkbox" />
								<?php esc_html_e( 'Webinars or live briefings', 'ai-awareness-day' ); ?>
							</label>
						</div>
					</div>

					<div class="aiad-survey__field">
						<p class="aiad-survey__label">
							5. <?php esc_html_e( 'Would you participate in AI Awareness Day next year?', 'ai-awareness-day' ); ?>
						</p>
						<div class="aiad-survey__radio-group">
							<label class="aiad-survey__radio-label">
								<input type="radio" name="participate_next_year" value="definitely" class="aiad-survey__radio" />
								<?php esc_html_e( 'Definitely', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="participate_next_year" value="probably" class="aiad-survey__radio" />
								<?php esc_html_e( 'Probably', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="participate_next_year" value="maybe" class="aiad-survey__radio" />
								<?php esc_html_e( 'Maybe', 'ai-awareness-day' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="participate_next_year" value="unlikely" class="aiad-survey__radio" />
								<?php esc_html_e( 'Unlikely', 'ai-awareness-day' ); ?>
							</label>
						</div>
					</div>
				</div>

				<div class="aiad-survey__field">
					<p class="aiad-survey__label aiad-survey__label--recommend">
						<span class="aiad-survey__recommend-label-participant" hidden>
							5. <?php esc_html_e( 'How likely are you to recommend AI Awareness Day to another school or colleague?', 'ai-awareness-day' ); ?>
						</span>
						<span class="aiad-survey__recommend-label-non-participant" hidden>
							6. <?php esc_html_e( 'How likely are you to recommend AI Awareness Day to another school or colleague?', 'ai-awareness-day' ); ?>
						</span>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="recommend" value="definitely" class="aiad-survey__radio" />
							<?php esc_html_e( 'Definitely', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="recommend" value="probably" class="aiad-survey__radio" />
							<?php esc_html_e( 'Probably', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="recommend" value="maybe" class="aiad-survey__radio" />
							<?php esc_html_e( 'Maybe', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="recommend" value="unlikely" class="aiad-survey__radio" />
							<?php esc_html_e( 'Unlikely', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>

				<input type="hidden" name="survey_version" value="<?php echo esc_attr( AIAD_SURVEY_VERSION ); ?>" />
			</fieldset>

			<!-- ── STEP: contact ── School details & stay in touch (everyone) -->
			<fieldset class="aiad-survey__step" data-step-id="contact" aria-labelledby="aiad-survey-step-contact-title">
				<h2 class="aiad-survey__step-title" id="aiad-survey-step-contact-title">
					<?php esc_html_e( 'Your school &amp; stay in touch', 'ai-awareness-day' ); ?>
				</h2>

				<p class="aiad-survey__helper">
					<?php esc_html_e( 'This survey is anonymous unless you choose to share your school name or email below. All fields on this step are optional — add contact details only if you would like 2027 early access or are happy for an anonymised quote to appear in our national report.', 'ai-awareness-day' ); ?>
				</p>

				<!-- School name -->
				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-school-name">
						<?php esc_html_e( 'School name (optional)', 'ai-awareness-day' ); ?>
					</label>
					<input class="aiad-survey__input" type="text" id="survey-school-name" name="school_name"
						placeholder="<?php esc_attr_e( 'e.g. Westfield Academy…', 'ai-awareness-day' ); ?>" maxlength="200" autocomplete="organization" />
				</div>

				<!-- Email -->
				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-email">
						<?php esc_html_e( 'Email address (optional)', 'ai-awareness-day' ); ?>
					</label>
					<input class="aiad-survey__input" type="email" id="survey-email" name="contact_email"
						placeholder="<?php esc_attr_e( 'Optional — for 2027 early access', 'ai-awareness-day' ); ?>" maxlength="200" autocomplete="email" />
				</div>

				<!-- Preferred communication channels -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						<?php esc_html_e( 'How would you prefer to receive regular updates? (tick all that apply)', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__checkgroup aiad-survey__checkgroup--single">
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="comms_preference[]" value="website_timeline" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'Regular updates on the website via the timeline', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="comms_preference[]" value="linkedin" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'LinkedIn', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="comms_preference[]" value="newsletter" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'Email newsletter', 'ai-awareness-day' ); ?>
						</label>
					</div>
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
				<button type="button" class="aiad-survey__btn aiad-survey__btn--submit" id="aiad-survey-submit" hidden>
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

		<p class="aiad-survey__credit">
			<?php esc_html_e( 'Produced by', 'ai-awareness-day' ); ?>
			<strong><?php esc_html_e( 'AI Awareness Day', 'ai-awareness-day' ); ?></strong>
			·
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'aiawarenessday.com' ); ?></a>
		</p>

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
	$display_board          = sanitize_text_field( wp_unslash( $_POST['display_board'] ?? '' ) );
	$ai_policy              = sanitize_text_field( wp_unslash( $_POST['ai_policy'] ?? '' ) );
	$curriculum_embedded    = sanitize_text_field( wp_unslash( $_POST['curriculum_embedded'] ?? '' ) );
	$participated           = sanitize_text_field( wp_unslash( $_POST['participated'] ?? '' ) );
	$participation_scale    = sanitize_text_field( wp_unslash( $_POST['participation_scale'] ?? '' ) );
	$primary_hope           = sanitize_text_field( wp_unslash( $_POST['primary_hope'] ?? '' ) );
	$lasting_effect         = sanitize_text_field( wp_unslash( $_POST['lasting_effect'] ?? '' ) );
	$prep_time              = sanitize_text_field( wp_unslash( $_POST['prep_time'] ?? '' ) );
	$materials_quality      = sanitize_text_field( wp_unslash( $_POST['materials_quality'] ?? '' ) );
	$best_format            = sanitize_text_field( wp_unslash( $_POST['best_format'] ?? '' ) );
	$bottleneck             = sanitize_text_field( wp_unslash( $_POST['bottleneck'] ?? '' ) );
	$open_feedback          = sanitize_textarea_field( wp_unslash( $_POST['open_feedback'] ?? '' ) );
	$non_part_reason        = sanitize_text_field( wp_unslash( $_POST['non_part_reason'] ?? '' ) );
	$staffroom_attitude     = sanitize_text_field( wp_unslash( $_POST['staffroom_attitude'] ?? '' ) );
	$school_name            = sanitize_text_field( wp_unslash( $_POST['school_name'] ?? '' ) );
	$school_type            = sanitize_text_field( wp_unslash( $_POST['school_type'] ?? '' ) );
	$contact_email          = sanitize_email( wp_unslash( $_POST['contact_email'] ?? '' ) );
	$perm_quote             = isset( $_POST['permission_quote'] ) ? '1' : '0';
	$survey_version         = sanitize_text_field( wp_unslash( $_POST['survey_version'] ?? AIAD_SURVEY_VERSION ) );
	$hear_about             = sanitize_text_field( wp_unslash( $_POST['hear_about'] ?? '' ) );
	$confidence_before      = sanitize_text_field( wp_unslash( $_POST['confidence_before'] ?? '' ) );
	$confidence_after       = sanitize_text_field( wp_unslash( $_POST['confidence_after'] ?? '' ) );
	$intended_action        = sanitize_text_field( wp_unslash( $_POST['intended_action'] ?? '' ) );
	$heard_before           = sanitize_text_field( wp_unslash( $_POST['heard_before'] ?? '' ) );
	$top_concern            = sanitize_text_field( wp_unslash( $_POST['top_concern'] ?? '' ) );
	$participate_next_year  = sanitize_text_field( wp_unslash( $_POST['participate_next_year'] ?? '' ) );
	$recommend              = sanitize_text_field( wp_unslash( $_POST['recommend'] ?? '' ) );

	$allowed_year_groups = array( 'eyfs', 'ks1', 'ks2', 'ks3', 'ks4', 'ks5', 'staff' );
	$raw_year_groups = isset( $_POST['year_groups'] ) && is_array( $_POST['year_groups'] )
		? array_map( 'sanitize_text_field', wp_unslash( $_POST['year_groups'] ) )
		: array();
	$year_groups = array_values( array_intersect( $raw_year_groups, $allowed_year_groups ) );

	$allowed_school_types = array( 'primary', 'secondary', 'all_through', 'special', 'fe_college', 'higher_education', 'mat_trust' );
	if ( ! in_array( $school_type, $allowed_school_types, true ) ) {
		$school_type = '';
	}

	// Validate required field
	if ( empty( $role ) ) {
		wp_send_json_error( array( 'message' => __( 'Please tell us your role before submitting.', 'ai-awareness-day' ) ) );
	}

	// School maturity questions (everyone) — validate against allowed values.
	$allowed_maturity = array( 'yes', 'in_development', 'no' );
	if ( ! in_array( $display_board, $allowed_maturity, true ) ) {
		$display_board = '';
	}
	if ( ! in_array( $ai_policy, $allowed_maturity, true ) ) {
		$ai_policy = '';
	}
	if ( ! in_array( $curriculum_embedded, $allowed_maturity, true ) ) {
		$curriculum_embedded = '';
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

	// Materials feedback (participants) — validate against allowed values.
	$allowed_materials_quality = array( 'ideal', 'adequate', 'basic', 'inadequate' );
	if ( ! in_array( $materials_quality, $allowed_materials_quality, true ) ) {
		$materials_quality = '';
	}
	$allowed_best_format = array( 'video', 'slides', 'teacher_instructions', 'mix' );
	if ( ! in_array( $best_format, $allowed_best_format, true ) ) {
		$best_format = '';
	}
	$allowed_useful_formats = array( 'starter_5', 'tutor_15', 'assembly_20', 'none' );
	$raw_useful = isset( $_POST['useful_formats'] ) && is_array( $_POST['useful_formats'] )
		? array_map( 'sanitize_text_field', wp_unslash( $_POST['useful_formats'] ) )
		: array();
	$useful_formats = array_values( array_intersect( $raw_useful, $allowed_useful_formats ) );

	// Non-participant path
	// Communication preferences (everyone)
	$allowed_comms = array( 'website_timeline', 'linkedin', 'newsletter' );
	$raw_comms = isset( $_POST['comms_preference'] ) && is_array( $_POST['comms_preference'] )
		? array_map( 'sanitize_text_field', wp_unslash( $_POST['comms_preference'] ) )
		: array();
	$comms_preference = array_values( array_intersect( $raw_comms, $allowed_comms ) );

	$allowed_hear_about = array( 'social', 'email', 'colleague', 'mat_trust', 'dfe_org', 'search', 'other' );
	if ( ! in_array( $hear_about, $allowed_hear_about, true ) ) {
		$hear_about = '';
	}

	$allowed_confidence = array( 'very', 'fairly', 'not_very', 'not' );
	if ( ! in_array( $confidence_before, $allowed_confidence, true ) ) {
		$confidence_before = '';
	}
	if ( ! in_array( $confidence_after, $allowed_confidence, true ) ) {
		$confidence_after = '';
	}

	$allowed_intended_action = array(
		'learn_more', 'review_policy', 'discuss_students', 'discuss_child',
		'safeguarding', 'assessment', 'explore_tools', 'no_action',
	);
	if ( ! in_array( $intended_action, $allowed_intended_action, true ) ) {
		$intended_action = '';
	}

	$allowed_heard_before = array( 'yes', 'no', 'not_sure' );
	if ( ! in_array( $heard_before, $allowed_heard_before, true ) ) {
		$heard_before = '';
	}

	$allowed_top_concern = array(
		'misinformation', 'privacy', 'safeguarding', 'deepfakes', 'integrity', 'bias', 'over_reliance',
	);
	if ( ! in_array( $top_concern, $allowed_top_concern, true ) ) {
		$top_concern = '';
	}

	$allowed_likelihood = array( 'definitely', 'probably', 'maybe', 'unlikely' );
	if ( ! in_array( $participate_next_year, $allowed_likelihood, true ) ) {
		$participate_next_year = '';
	}
	if ( ! in_array( $recommend, $allowed_likelihood, true ) ) {
		$recommend = '';
	}

	$allowed_support_needed = array(
		'training', 'policy', 'parent_resources', 'student_resources', 'safeguarding', 'case_studies', 'webinars',
	);
	$raw_support_needed = isset( $_POST['support_needed'] ) && is_array( $_POST['support_needed'] )
		? array_map( 'sanitize_text_field', wp_unslash( $_POST['support_needed'] ) )
		: array();
	$support_needed = array_values( array_intersect( $raw_support_needed, $allowed_support_needed ) );

	// Sanitise Likert ratings (1–5)
	$likert_fields = array(
		'rating_student_empowerment',
		'rating_critical_skepticism',
		'rating_inclusivity',
		'rating_plug_and_play',
		'rating_student_access',
		'rating_tech_delivery',
	);
	$ratings = array();
	foreach ( $likert_fields as $field ) {
		$val = (int) ( $_POST[ $field ] ?? 0 );
		$ratings[ $field ] = ( $val >= 1 && $val <= 5 ) ? $val : 0;
	}

	// Build a title for the admin list
	$title = sprintf(
		'%s — %s — %s — %s',
		$school_name ?: 'Anonymous',
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
		'_survey_curriculum_embedded'   => $curriculum_embedded,
		'_survey_display_board'         => $display_board,
		'_survey_ai_policy'             => $ai_policy,
		// Gate
		'_survey_participated'          => $participated,
		'_survey_participation_scale'   => $participation_scale,
		// Part 1 — Hopes & attitudinal shifts (participants)
		'_survey_primary_hope'                  => $primary_hope,
		'_survey_rating_student_empowerment'    => $ratings['rating_student_empowerment'],
		'_survey_rating_critical_skepticism'    => $ratings['rating_critical_skepticism'],
		'_survey_rating_inclusivity'            => $ratings['rating_inclusivity'],
		'_survey_lasting_effect'                => $lasting_effect,
		// Part 2 — Resource friction (participants)
		'_survey_prep_time'                     => $prep_time,
		'_survey_rating_plug_and_play'          => $ratings['rating_plug_and_play'],
		'_survey_rating_student_access'         => $ratings['rating_student_access'],
		'_survey_rating_tech_delivery'          => $ratings['rating_tech_delivery'],
		'_survey_materials_quality'             => $materials_quality,
		'_survey_useful_formats'                => wp_json_encode( $useful_formats ),
		'_survey_best_format'                   => $best_format,
		// Part 3 — Learning efficacy (participants)
		'_survey_staffroom_impact'              => wp_json_encode( $staffroom_impact ),
		// Part 4 — Strategic roadmap (participants)
		'_survey_bottleneck'            => $bottleneck,
		'_survey_support_modules'       => wp_json_encode( $support_modules ),
		'_survey_open_feedback'         => $open_feedback,
		// Part 1B — Non-participant track
		'_survey_non_part_reason'       => $non_part_reason,
		'_survey_staffroom_attitude'    => $staffroom_attitude,
		// Contact / school details
		'_survey_school_name'           => $school_name,
		'_survey_school_type'           => $school_type,
		'_survey_year_groups'           => wp_json_encode( $year_groups ),
		'_survey_contact_email'         => $contact_email,
		'_survey_comms_preference'      => wp_json_encode( $comms_preference ),
		'_survey_permission_quote'      => $perm_quote,
		'_survey_year'                  => '2026',
		'_survey_version'               => $survey_version ?: '1.1',
		// Reach & impact (v1.1)
		'_survey_hear_about'            => $hear_about,
		'_survey_confidence_before'     => $confidence_before,
		'_survey_confidence_after'      => $confidence_after,
		'_survey_intended_action'       => $intended_action,
		'_survey_heard_before'          => $heard_before,
		'_survey_top_concern'           => $top_concern,
		'_survey_support_needed'        => wp_json_encode( $support_needed ),
		'_survey_participate_next_year' => $participate_next_year,
		'_survey_recommend'             => $recommend,
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
		'_survey_curriculum_embedded'   => __( 'AI activities in curriculum', 'ai-awareness-day' ),
		'_survey_display_board'         => __( 'AI Awareness display board', 'ai-awareness-day' ),
		'_survey_ai_policy'             => __( 'AI Awareness / AI-use policy', 'ai-awareness-day' ),
		'_survey_participated'          => __( 'Participated on 4th June', 'ai-awareness-day' ),
		'_survey_participation_scale'           => __( 'Participation scale', 'ai-awareness-day' ),
		'_survey_primary_hope'                  => __( 'Primary hope (Q1)', 'ai-awareness-day' ),
		'_survey_rating_student_empowerment'    => __( 'Rating: student empowerment (Q2)', 'ai-awareness-day' ),
		'_survey_rating_critical_skepticism'    => __( 'Rating: critical scepticism (Q2)', 'ai-awareness-day' ),
		'_survey_rating_inclusivity'            => __( 'Rating: inclusivity & access (Q2)', 'ai-awareness-day' ),
		'_survey_lasting_effect'                => __( 'Lasting classroom effect (Q3)', 'ai-awareness-day' ),
		'_survey_prep_time'                     => __( 'Prep time (Q4)', 'ai-awareness-day' ),
		'_survey_rating_plug_and_play'          => __( 'Rating: plug & play usability (Q5)', 'ai-awareness-day' ),
		'_survey_rating_student_access'         => __( 'Rating: student accessibility (Q5)', 'ai-awareness-day' ),
		'_survey_rating_tech_delivery'          => __( 'Rating: technical delivery (Q5)', 'ai-awareness-day' ),
		'_survey_materials_quality'             => __( 'Materials adequacy (Q6)', 'ai-awareness-day' ),
		'_survey_useful_formats'                => __( 'Useful session formats (Q7)', 'ai-awareness-day' ),
		'_survey_best_format'                   => __( 'Best content format (Q8)', 'ai-awareness-day' ),
		'_survey_staffroom_impact'              => __( 'Staff room / parental impact (Q9)', 'ai-awareness-day' ),
		'_survey_bottleneck'            => __( 'Biggest bottleneck (Q10)', 'ai-awareness-day' ),
		'_survey_support_modules'       => __( 'Support modules for 2027 (Q11)', 'ai-awareness-day' ),
		'_survey_open_feedback'         => __( 'Open feedback (Q12)', 'ai-awareness-day' ),
		'_survey_non_part_reason'       => __( 'Non-participant: primary reason', 'ai-awareness-day' ),
		'_survey_staffroom_attitude'    => __( 'Non-participant: staff room attitude', 'ai-awareness-day' ),
		'_survey_version'               => __( 'Survey version', 'ai-awareness-day' ),
		'_survey_hear_about'            => __( 'How heard about AAD', 'ai-awareness-day' ),
		'_survey_confidence_before'     => __( 'Confidence before (participant)', 'ai-awareness-day' ),
		'_survey_confidence_after'      => __( 'Confidence after (participant)', 'ai-awareness-day' ),
		'_survey_intended_action'       => __( 'Intended next action (participant)', 'ai-awareness-day' ),
		'_survey_heard_before'          => __( 'Heard before today (non-participant)', 'ai-awareness-day' ),
		'_survey_top_concern'           => __( 'Top AI concern (non-participant)', 'ai-awareness-day' ),
		'_survey_support_needed'        => __( 'Support needed for 2027 (non-participant)', 'ai-awareness-day' ),
		'_survey_participate_next_year' => __( 'Participate next year (non-participant)', 'ai-awareness-day' ),
		'_survey_recommend'             => __( 'Recommend to colleague', 'ai-awareness-day' ),
		'_survey_school_name'           => __( 'School name', 'ai-awareness-day' ),
		'_survey_school_type'           => __( 'School type', 'ai-awareness-day' ),
		'_survey_year_groups'           => __( 'Year groups engaged', 'ai-awareness-day' ),
		'_survey_contact_email'         => __( 'Contact email', 'ai-awareness-day' ),
		'_survey_comms_preference'      => __( 'Preferred update channels', 'ai-awareness-day' ),
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
		'ID', 'Date', 'Role', 'AI in curriculum', 'AI display board', 'AI policy', 'Participated', 'Participation scale',
		// Part 1 — Hopes
		'Primary hope (Q1)',
		'Rating: student empowerment (Q2)', 'Rating: critical scepticism (Q2)', 'Rating: inclusivity (Q2)',
		'Lasting classroom effect (Q3)',
		// Part 2 — Resource friction
		'Prep time (Q4)', 'Rating: plug & play (Q5)', 'Rating: student access (Q5)', 'Rating: tech delivery (Q5)',
		'Materials adequacy (Q6)', 'Useful formats (Q7)', 'Best content format (Q8)',
		// Part 3 — Learning efficacy
		'Staff room impact (Q9)',
		// Part 4 — Strategic roadmap
		'Bottleneck (Q10)', 'Support modules 2027 (Q11)', 'Open feedback (Q12)',
		// Non-participant track
		'Non-part: reason', 'Non-part: staff room attitude',
		// Reach & impact (v1.1)
		'Survey version', 'How heard about AAD', 'Confidence before', 'Confidence after', 'Intended next action',
		'Heard before today', 'Top AI concern', 'Support needed 2027', 'Participate next year', 'Recommend',
		// Contact / school details
		'School name', 'School type', 'Year groups', 'Contact email', 'Preferred update channels', 'Quoting permission',
	);

	$meta_keys = array(
		'_survey_role', '_survey_curriculum_embedded', '_survey_display_board', '_survey_ai_policy', '_survey_participated', '_survey_participation_scale',
		'_survey_primary_hope',
		'_survey_rating_student_empowerment', '_survey_rating_critical_skepticism', '_survey_rating_inclusivity',
		'_survey_lasting_effect',
		'_survey_prep_time',
		'_survey_rating_plug_and_play', '_survey_rating_student_access', '_survey_rating_tech_delivery',
		'_survey_materials_quality', '_survey_useful_formats', '_survey_best_format',
		'_survey_staffroom_impact', '_survey_bottleneck',
		'_survey_support_modules', '_survey_open_feedback',
		'_survey_non_part_reason', '_survey_staffroom_attitude',
		'_survey_version', '_survey_hear_about', '_survey_confidence_before', '_survey_confidence_after', '_survey_intended_action',
		'_survey_heard_before', '_survey_top_concern', '_survey_support_needed', '_survey_participate_next_year', '_survey_recommend',
		'_survey_school_name', '_survey_school_type', '_survey_year_groups', '_survey_contact_email', '_survey_comms_preference', '_survey_permission_quote',
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

// ---------------------------------------------------------------------------
// Admin: custom list table columns
// ---------------------------------------------------------------------------

function aiad_survey_response_columns( array $cols ): array {
	return array(
		'cb'            => $cols['cb'] ?? '',
		'title'         => __( 'Response', 'ai-awareness-day' ),
		'school'        => __( 'School', 'ai-awareness-day' ),
		'role'          => __( 'Role', 'ai-awareness-day' ),
		'year_groups'   => __( 'Year groups', 'ai-awareness-day' ),
		'participated'  => __( 'Participated?', 'ai-awareness-day' ),
		'avg_rating'    => __( 'Avg rating', 'ai-awareness-day' ),
		'date'          => __( 'Date', 'ai-awareness-day' ),
	);
}
add_filter( 'manage_survey_response_posts_columns', 'aiad_survey_response_columns' );

function aiad_survey_response_column_content( string $col, int $post_id ): void {
	switch ( $col ) {
		case 'school':
			$name = (string) get_post_meta( $post_id, '_survey_school_name', true );
			$type = (string) get_post_meta( $post_id, '_survey_school_type', true );
			echo esc_html( $name ?: '—' );
			if ( $type ) {
				echo ' <span style="color:#888;font-size:11px;">(' . esc_html( $type ) . ')</span>';
			}
			break;
		case 'role':
			$map = array(
				'teacher_primary'   => 'Teacher (Primary)',
				'teacher_secondary' => 'Teacher (Secondary)',
				'computing_lead'    => 'Computing Lead',
				'headteacher'       => 'Headteacher / Leader',
				'slt_mat'           => 'SLT / Digital Lead',
				'alt_provision'     => 'Alt Provision',
			);
			$val = (string) get_post_meta( $post_id, '_survey_role', true );
			echo esc_html( $map[ $val ] ?? $val ?: '—' );
			break;
		case 'year_groups':
			$raw = (string) get_post_meta( $post_id, '_survey_year_groups', true );
			$arr = $raw ? json_decode( $raw, true ) : array();
			echo esc_html( is_array( $arr ) && $arr ? implode( ', ', array_map( 'strtoupper', $arr ) ) : '—' );
			break;
		case 'participated':
			$val = (string) get_post_meta( $post_id, '_survey_participated', true );
			if ( $val === 'yes' ) {
				$scale = (string) get_post_meta( $post_id, '_survey_participation_scale', true );
				$badge = '<span style="background:#dcfce7;color:#166534;padding:2px 7px;border-radius:99px;font-size:11px;font-weight:600;">Yes</span>';
				echo wp_kses_post( $badge );
				if ( $scale ) {
					echo ' <span style="color:#888;font-size:11px;">' . esc_html( $scale ) . '</span>';
				}
			} elseif ( $val === 'no' ) {
				echo '<span style="background:#fee2e2;color:#991b1b;padding:2px 7px;border-radius:99px;font-size:11px;font-weight:600;">No</span>';
			} else {
				echo '—';
			}
			break;
		case 'avg_rating':
			$fields = array(
				'_survey_rating_student_empowerment',
				'_survey_rating_critical_skepticism',
				'_survey_rating_inclusivity',
				'_survey_rating_plug_and_play',
				'_survey_rating_student_access',
				'_survey_rating_tech_delivery',
			);
			$vals = array_filter( array_map( function( $k ) use ( $post_id ) {
				$v = (int) get_post_meta( $post_id, $k, true );
				return $v > 0 ? $v : null;
			}, $fields ) );
			if ( $vals ) {
				$avg = round( array_sum( $vals ) / count( $vals ), 1 );
				$stars = str_repeat( '★', (int) round( $avg ) ) . str_repeat( '☆', 5 - (int) round( $avg ) );
				echo '<span title="' . esc_attr( $avg . '/5' ) . '" style="color:#f59e0b;">' . esc_html( $stars ) . '</span> <span style="font-size:11px;color:#888;">' . esc_html( $avg ) . '</span>';
			} else {
				echo '<span style="color:#888;">—</span>';
			}
			break;
	}
}
add_action( 'manage_survey_response_posts_custom_column', 'aiad_survey_response_column_content', 10, 2 );

function aiad_survey_response_sortable_columns( array $cols ): array {
	$cols['school']       = 'school';
	$cols['participated'] = 'participated';
	return $cols;
}
add_filter( 'manage_edit-survey_response_sortable_columns', 'aiad_survey_response_sortable_columns' );

// ---------------------------------------------------------------------------
// Admin: analytics page
// ---------------------------------------------------------------------------

function aiad_survey_register_analytics_page(): void {
	add_submenu_page(
		'edit.php?post_type=survey_response',
		__( 'Survey Analytics', 'ai-awareness-day' ),
		__( '📊 Analytics', 'ai-awareness-day' ),
		'edit_posts',
		'aiad-survey-analytics',
		'aiad_survey_analytics_page'
	);
}
add_action( 'admin_menu', 'aiad_survey_register_analytics_page' );

function aiad_survey_analytics_page(): void {
	global $wpdb;

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Permission denied.', 'ai-awareness-day' ) );
	}

	// Fetch all meta in one query
	$post_ids = $wpdb->get_col(
		"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'survey_response' AND post_status = 'publish' ORDER BY post_date ASC"
	);
	$total = count( $post_ids );

	if ( ! $total ) {
		echo '<div class="wrap"><h1>' . esc_html__( 'Survey Analytics', 'ai-awareness-day' ) . '</h1>';
		echo '<p>' . esc_html__( 'No responses yet.', 'ai-awareness-day' ) . '</p></div>';
		return;
	}

	// Build aggregated data
	$roles       = array();
	$school_types = array();
	$year_groups_tally = array();
	$participated_yes = 0;
	$participated_no  = 0;
	$display_board = array();
	$ai_policy     = array();
	$curriculum_embedded = array();
	$scales      = array();
	$hopes       = array();
	$lasting     = array();
	$prep        = array();
	$materials   = array();
	$useful_formats = array();
	$best_formats   = array();
	$bottlenecks = array();
	$support     = array();
	$non_reasons = array();
	$staffroom_attitudes = array();
	$comms       = array();
	$ratings_sum = array_fill_keys( array(
		'rating_student_empowerment', 'rating_critical_skepticism', 'rating_inclusivity',
		'rating_plug_and_play', 'rating_student_access', 'rating_tech_delivery',
	), array( 'sum' => 0, 'count' => 0 ) );
	$recent_feedback = array();

	foreach ( $post_ids as $pid ) {
		$meta = get_post_meta( (int) $pid );
		$get  = function( string $key ) use ( $meta ): string {
			return isset( $meta[ $key ][0] ) ? (string) $meta[ $key ][0] : '';
		};

		// Roles
		$role = $get( '_survey_role' );
		if ( $role ) $roles[ $role ] = ( $roles[ $role ] ?? 0 ) + 1;

		// School types
		$st = $get( '_survey_school_type' );
		if ( $st ) $school_types[ $st ] = ( $school_types[ $st ] ?? 0 ) + 1;

		// Year groups
		$yg_raw = $get( '_survey_year_groups' );
		$yg_arr = $yg_raw ? json_decode( $yg_raw, true ) : array();
		if ( is_array( $yg_arr ) ) {
			foreach ( $yg_arr as $yg ) {
				$year_groups_tally[ $yg ] = ( $year_groups_tally[ $yg ] ?? 0 ) + 1;
			}
		}

		// Participation
		$part = $get( '_survey_participated' );
		if ( $part === 'yes' ) $participated_yes++;
		elseif ( $part === 'no' ) $participated_no++;

		$db = $get( '_survey_display_board' );
		if ( $db ) $display_board[ $db ] = ( $display_board[ $db ] ?? 0 ) + 1;

		$ap = $get( '_survey_ai_policy' );
		if ( $ap ) $ai_policy[ $ap ] = ( $ai_policy[ $ap ] ?? 0 ) + 1;

		$ce = $get( '_survey_curriculum_embedded' );
		if ( $ce ) $curriculum_embedded[ $ce ] = ( $curriculum_embedded[ $ce ] ?? 0 ) + 1;

		$scale = $get( '_survey_participation_scale' );
		if ( $scale ) $scales[ $scale ] = ( $scales[ $scale ] ?? 0 ) + 1;

		$hope = $get( '_survey_primary_hope' );
		if ( $hope ) $hopes[ $hope ] = ( $hopes[ $hope ] ?? 0 ) + 1;

		$le = $get( '_survey_lasting_effect' );
		if ( $le ) $lasting[ $le ] = ( $lasting[ $le ] ?? 0 ) + 1;

		$pt = $get( '_survey_prep_time' );
		if ( $pt ) $prep[ $pt ] = ( $prep[ $pt ] ?? 0 ) + 1;

		$mq = $get( '_survey_materials_quality' );
		if ( $mq ) $materials[ $mq ] = ( $materials[ $mq ] ?? 0 ) + 1;

		$uf_raw = $get( '_survey_useful_formats' );
		$uf_arr = $uf_raw ? json_decode( $uf_raw, true ) : array();
		if ( is_array( $uf_arr ) ) {
			foreach ( $uf_arr as $uf ) {
				$useful_formats[ $uf ] = ( $useful_formats[ $uf ] ?? 0 ) + 1;
			}
		}

		$bf = $get( '_survey_best_format' );
		if ( $bf ) $best_formats[ $bf ] = ( $best_formats[ $bf ] ?? 0 ) + 1;

		$bn = $get( '_survey_bottleneck' );
		if ( $bn ) $bottlenecks[ $bn ] = ( $bottlenecks[ $bn ] ?? 0 ) + 1;

		$sm_raw = $get( '_survey_support_modules' );
		$sm_arr = $sm_raw ? json_decode( $sm_raw, true ) : array();
		if ( is_array( $sm_arr ) ) {
			foreach ( $sm_arr as $sm ) {
				$support[ $sm ] = ( $support[ $sm ] ?? 0 ) + 1;
			}
		}

		$nr = $get( '_survey_non_part_reason' );
		if ( $nr ) $non_reasons[ $nr ] = ( $non_reasons[ $nr ] ?? 0 ) + 1;

		$sa = $get( '_survey_staffroom_attitude' );
		if ( $sa ) $staffroom_attitudes[ $sa ] = ( $staffroom_attitudes[ $sa ] ?? 0 ) + 1;

		$comms_raw = $get( '_survey_comms_preference' );
		$comms_arr = $comms_raw ? json_decode( $comms_raw, true ) : array();
		if ( is_array( $comms_arr ) ) {
			foreach ( $comms_arr as $c ) {
				$comms[ $c ] = ( $comms[ $c ] ?? 0 ) + 1;
			}
		}

		// Ratings
		foreach ( array_keys( $ratings_sum ) as $rk ) {
			$rv = (int) $get( '_survey_' . $rk );
			if ( $rv >= 1 && $rv <= 5 ) {
				$ratings_sum[ $rk ]['sum']   += $rv;
				$ratings_sum[ $rk ]['count'] += 1;
			}
		}

		// Recent feedback (last 5)
		$fb = $get( '_survey_open_feedback' );
		if ( $fb && count( $recent_feedback ) < 5 ) {
			$recent_feedback[] = array(
				'text'   => $fb,
				'school' => $get( '_survey_school_name' ),
				'role'   => $get( '_survey_role' ),
			);
		}
	}

	arsort( $roles );
	arsort( $bottlenecks );
	arsort( $support );

	// Helper: render a horizontal bar chart
	$bar = function( array $data, array $labels, int $total_n ) use ( $total ): string {
		if ( ! $data ) return '<p style="color:#888">No data yet.</p>';
		$out = '<div style="display:grid;gap:6px;margin-top:8px;">';
		foreach ( $data as $key => $count ) {
			$label = $labels[ $key ] ?? $key;
			$pct   = $total_n > 0 ? round( ( $count / $total_n ) * 100 ) : 0;
			$out  .= '<div>';
			$out  .= '<div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:2px;"><span>' . esc_html( $label ) . '</span><span style="color:#888;">' . esc_html( $count ) . ' (' . esc_html( $pct ) . '%)</span></div>';
			$out  .= '<div style="background:#e5e7eb;border-radius:4px;height:14px;overflow:hidden;"><div style="background:#0070c0;height:100%;width:' . esc_attr( $pct ) . '%;border-radius:4px;transition:width .4s;"></div></div>';
			$out  .= '</div>';
		}
		return $out . '</div>';
	};

	$role_labels = array(
		'teacher_primary'   => 'Classroom Teacher (Primary)',
		'teacher_secondary' => 'Classroom Teacher (Secondary)',
		'computing_lead'    => 'Computing Lead / HoD',
		'headteacher'       => 'Headteacher / School Leader',
		'slt_mat'           => 'SLT / Digital Lead',
		'alt_provision'     => 'Alt Provision / SEN',
	);

	$school_type_labels = array(
		'primary'    => 'Primary',
		'secondary'  => 'Secondary',
		'all_through' => 'All-through',
		'special'    => 'Special / Alt Provision',
		'fe_college' => 'FE College / 6th Form',
		'higher_education' => 'Higher Education',
		'mat_trust'  => 'MAT / Trust',
	);

	$yg_labels = array(
		'eyfs' => 'EYFS', 'ks1' => 'KS1', 'ks2' => 'KS2',
		'ks3' => 'KS3', 'ks4' => 'KS4', 'ks5' => 'KS5 / 6th Form', 'staff' => 'Staff CPD',
	);

	$rating_labels = array(
		'rating_student_empowerment' => 'Student Empowerment',
		'rating_critical_skepticism' => 'Critical Scepticism',
		'rating_inclusivity'         => 'Inclusivity & Access',
		'rating_plug_and_play'       => 'Plug & Play Usability',
		'rating_student_access'      => 'Student Accessibility',
		'rating_tech_delivery'       => 'Technical Delivery',
	);

	$hope_labels = array(
		'demystify'        => 'Demystify AI / reduce anxiety',
		'confidence_skills' => 'Confidence & practical skills',
		'digital_resilience' => 'Digital resilience / fact-checking',
		'career_interest'  => 'Career interest / CS spark',
	);

	$bottleneck_labels = array(
		'time'           => 'Time constraints',
		'tech_barriers'  => 'Tech / firewall barriers',
		'staff_hesitation' => 'Staff hesitation',
		'physical_assets' => 'Lack of physical assets',
	);

	$support_labels = array(
		'display_kits'    => 'Display Board Kits',
		'cross_curricular' => 'Cross-Curricular Schemes of Work',
		'cpd_pathways'    => 'Staff CPD Pathways',
		'pta_packs'       => 'PTA Interactive Packs',
	);

	$maturity_labels = array(
		'yes'            => 'Yes',
		'in_development' => 'In development',
		'no'             => 'No',
	);

	$materials_labels = array(
		'ideal'      => 'Ideal — exceeded needs',
		'adequate'   => 'Adequate',
		'basic'      => 'Basic — had to supplement',
		'inadequate' => 'Inadequate',
	);

	$useful_format_labels = array(
		'starter_5'   => '5-min lesson starter',
		'tutor_15'    => '15-min tutor session',
		'assembly_20' => '20-min assembly slides',
		'none'        => 'None fitted timetable',
	);

	$best_format_labels = array(
		'video'                => 'Video (ready to play)',
		'slides'               => 'Presentation slides',
		'teacher_instructions' => 'Teacher breakdown / instructions',
		'mix'                  => 'A mix',
	);

	$comms_labels = array(
		'website_timeline' => 'Website timeline',
		'linkedin'         => 'LinkedIn',
		'newsletter'       => 'Email newsletter',
	);

	// Card CSS helper
	$card = '<div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:20px 24px;margin-bottom:20px;">';
	?>
	<div class="wrap" style="max-width:1100px;">
		<h1 style="margin-bottom:6px;">📊 <?php esc_html_e( 'National Survey 2026 — Analytics', 'ai-awareness-day' ); ?></h1>
		<p style="color:#6b7280;margin-bottom:24px;">
			<?php echo esc_html( sprintf( __( '%d total responses', 'ai-awareness-day' ), $total ) ); ?>
			&nbsp;·&nbsp;
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=survey_response' ) ); ?>"><?php esc_html_e( 'View all responses', 'ai-awareness-day' ); ?></a>
			&nbsp;·&nbsp;
			<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'aiad_export_survey_csv', '_wpnonce' => wp_create_nonce( 'aiad_export_survey_csv' ) ), admin_url( 'admin-post.php' ) ) ); ?>"><?php esc_html_e( 'Export CSV', 'ai-awareness-day' ); ?></a>
		</p>

		<!-- Top stats -->
		<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;margin-bottom:24px;">
			<?php
			$stats = array(
				array( 'label' => __( 'Total responses', 'ai-awareness-day' ), 'value' => $total, 'color' => '#0070c0' ),
				array( 'label' => __( 'Participants', 'ai-awareness-day' ), 'value' => $participated_yes, 'color' => '#059669' ),
				array( 'label' => __( 'Non-participants', 'ai-awareness-day' ), 'value' => $participated_no, 'color' => '#dc2626' ),
				array( 'label' => __( 'Schools named', 'ai-awareness-day' ), 'value' => count( array_filter( array_map( fn( $pid ) => get_post_meta( (int) $pid, '_survey_school_name', true ), $post_ids ) ) ), 'color' => '#7c3aed' ),
			);
			foreach ( $stats as $s ) :
				?>
				<div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px 20px;text-align:center;">
					<div style="font-size:2rem;font-weight:700;color:<?php echo esc_attr( $s['color'] ); ?>;"><?php echo esc_html( $s['value'] ); ?></div>
					<div style="font-size:12px;color:#6b7280;margin-top:4px;"><?php echo esc_html( $s['label'] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>

		<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

		<!-- Roles -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Respondents by role', 'ai-awareness-day' ); ?>
			</h3>
			<?php echo $bar( $roles, $role_labels, $total ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- School types -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'School type breakdown', 'ai-awareness-day' ); ?>
			</h3>
			<?php echo $bar( $school_types, $school_type_labels, $total ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- Year groups -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Year groups engaged', 'ai-awareness-day' ); ?>
			</h3>
			<?php
			arsort( $year_groups_tally );
			echo $bar( $year_groups_tally, $yg_labels, $total ); // phpcs:ignore WordPress.Security.EscapeOutput
			?>
		</div>

		<!-- AI in curriculum -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'AI activities embedded in curriculum', 'ai-awareness-day' ); ?>
			</h3>
			<?php echo $bar( $curriculum_embedded, $maturity_labels, $total ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- AI Awareness display board -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Has an AI Awareness display board', 'ai-awareness-day' ); ?>
			</h3>
			<?php echo $bar( $display_board, $maturity_labels, $total ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- AI Awareness policy -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Has an AI Awareness / AI-use policy', 'ai-awareness-day' ); ?>
			</h3>
			<?php echo $bar( $ai_policy, $maturity_labels, $total ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- Participation scale -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Participation scale (participants)', 'ai-awareness-day' ); ?>
			</h3>
			<?php
			echo $bar( $scales, array( // phpcs:ignore WordPress.Security.EscapeOutput
				'single_classroom' => 'Single classroom (< 30)',
				'year_group'       => 'Year-group cohort (30–120)',
				'whole_school'     => 'Whole school (120+)',
			), $participated_yes );
			?>
		</div>

		<!-- Primary hopes -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Primary hope (Q1)', 'ai-awareness-day' ); ?>
			</h3>
			<?php echo $bar( $hopes, $hope_labels, $participated_yes ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- Lasting effect -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Lasting classroom effect (Q3)', 'ai-awareness-day' ); ?>
			</h3>
			<?php
			echo $bar( $lasting, array( // phpcs:ignore WordPress.Security.EscapeOutput
				'significant' => 'Significant — changed dynamic',
				'moderate'    => 'Moderate awareness raised',
				'none'        => 'No measurable effect',
			), $participated_yes );
			?>
		</div>

		<!-- Bottlenecks -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Biggest bottleneck (Q8)', 'ai-awareness-day' ); ?>
			</h3>
			<?php echo $bar( $bottlenecks, $bottleneck_labels, $participated_yes ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- Non-participant reasons -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Non-participant: barriers (Q1B)', 'ai-awareness-day' ); ?>
			</h3>
			<?php
			echo $bar( $non_reasons, array( // phpcs:ignore WordPress.Security.EscapeOutput
				'timetable'       => 'Curriculum/timetable pressure',
				'late_discovery'  => 'Late discovery',
				'staff_confidence' => 'Lack of staff confidence',
				'firewall'        => 'Tech / firewall restrictions',
				'banned_policy'   => 'AI ban policy',
			), $participated_no );
			?>
		</div>

		<!-- Support modules 2027 -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Top 2027 support modules requested', 'ai-awareness-day' ); ?>
			</h3>
			<?php echo $bar( $support, $support_labels, $participated_yes ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- Materials adequacy -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Did the materials meet needs? (Q6)', 'ai-awareness-day' ); ?>
			</h3>
			<?php echo $bar( $materials, $materials_labels, $participated_yes ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- Useful session formats -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Useful session formats (Q7)', 'ai-awareness-day' ); ?>
			</h3>
			<?php echo $bar( $useful_formats, $useful_format_labels, $participated_yes ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- Best content format -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Best content format (Q8)', 'ai-awareness-day' ); ?>
			</h3>
			<?php echo $bar( $best_formats, $best_format_labels, $participated_yes ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- Preferred communication channels -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Preferred update channels', 'ai-awareness-day' ); ?>
			</h3>
			<?php echo $bar( $comms, $comms_labels, $total ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		</div><!-- /grid -->

		<!-- Star ratings -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 16px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Average Likert ratings (participants, out of 5)', 'ai-awareness-day' ); ?>
			</h3>
			<div style="display:grid;gap:10px;">
			<?php foreach ( $ratings_sum as $rk => $rv ) : ?>
				<?php
				$avg = $rv['count'] > 0 ? round( $rv['sum'] / $rv['count'], 2 ) : 0;
				$pct = $avg > 0 ? round( ( $avg / 5 ) * 100 ) : 0;
				$label = $rating_labels[ $rk ] ?? $rk;
				?>
				<div>
					<div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:3px;">
						<span><?php echo esc_html( $label ); ?></span>
						<span style="font-weight:600;color:<?php echo $avg >= 4 ? '#059669' : ( $avg >= 3 ? '#d97706' : '#dc2626' ); ?>;">
							<?php echo $rv['count'] > 0 ? esc_html( $avg . ' / 5' ) : esc_html__( 'No data', 'ai-awareness-day' ); ?>
						</span>
					</div>
					<div style="background:#e5e7eb;border-radius:4px;height:16px;overflow:hidden;">
						<div style="background:<?php echo $avg >= 4 ? '#059669' : ( $avg >= 3 ? '#d97706' : '#dc2626' ); ?>;height:100%;width:<?php echo esc_attr( $pct ); ?>%;border-radius:4px;"></div>
					</div>
					<div style="font-size:11px;color:#9ca3af;margin-top:2px;"><?php echo esc_html( $rv['count'] ); ?> <?php esc_html_e( 'responses', 'ai-awareness-day' ); ?></div>
				</div>
			<?php endforeach; ?>
			</div>
		</div>

		<?php if ( $recent_feedback ) : ?>
		<!-- Recent open feedback -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 16px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Recent open feedback', 'ai-awareness-day' ); ?>
			</h3>
			<div style="display:grid;gap:12px;">
			<?php foreach ( $recent_feedback as $fb ) : ?>
				<div style="background:#f9fafb;border-left:3px solid #0070c0;padding:10px 14px;border-radius:0 6px 6px 0;">
					<p style="margin:0 0 6px;font-style:italic;">"<?php echo esc_html( mb_substr( $fb['text'], 0, 300 ) . ( mb_strlen( $fb['text'] ) > 300 ? '…' : '' ) ); ?>"</p>
					<p style="margin:0;font-size:11px;color:#6b7280;">
						<?php
						$by = array_filter( array( $fb['school'], $fb['role'] ) );
						echo esc_html( $by ? '— ' . implode( ' · ', $by ) : '— Anonymous' );
						?>
					</p>
				</div>
			<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

	</div>
	<?php
}

// ---------------------------------------------------------------------------
// Auto-create the survey page if it doesn't exist
// ---------------------------------------------------------------------------

function aiad_maybe_create_survey_page(): void {
	if ( get_option( 'aiad_survey_page_created' ) ) {
		return;
	}

	// Check if a page with this slug already exists
	$existing = get_page_by_path( 'national-survey-2026' );
	if ( $existing ) {
		update_option( 'aiad_survey_page_created', $existing->ID );
		return;
	}

	$page_id = wp_insert_post( array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => __( 'National Survey 2026 — AI Awareness Day', 'ai-awareness-day' ),
		'post_name'    => 'national-survey-2026',
		'post_content' => '<!-- wp:shortcode -->[aiad_national_survey]<!-- /wp:shortcode -->',
	) );

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_option( 'aiad_survey_page_created', $page_id );
	}
}
add_action( 'init', 'aiad_maybe_create_survey_page', 50 );
