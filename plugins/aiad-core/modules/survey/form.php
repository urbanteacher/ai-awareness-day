<?php
/**
 * National Survey: The survey form, printed by the aiad/national-survey block (aiad_national_survey_render()).
 *
 * Part of the survey module (modules/survey.php).
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
function aiad_national_survey_render( $atts = array() ): string {
	aiad_enqueue_national_survey_assets();

	ob_start();
	?>
	<div class="aiad-survey" id="aiad-national-survey" role="region" aria-label="<?php esc_attr_e( 'AI Awareness Day National Survey', 'aiad-core' ); ?>">

		<!-- Progress: segmented stepper (built by JS, one segment per step in the active path) -->
		<div class="aiad-survey__progress" id="aiad-survey-progress">
			<div class="aiad-survey__stepper" id="aiad-survey-stepper" role="list" aria-hidden="true"></div>
			<p class="aiad-survey__progress-label" id="aiad-survey-progress-label" aria-live="polite"></p>
		</div>

		<form class="aiad-survey__form" id="aiad-survey-form" novalidate>

			<!-- ── STEP: profile ── Part 1: School Profile & Contextual Baselines -->
			<fieldset class="aiad-survey__step" data-step-id="profile" aria-labelledby="aiad-survey-step-profile-title">
				<h2 class="aiad-survey__step-title" id="aiad-survey-step-profile-title">
					<?php esc_html_e( 'Part 1 — School profile', 'aiad-core' ); ?>
				</h2>

				<?php
				$year_groups = array(
					'eyfs'  => __( 'EYFS', 'aiad-core' ),
					'ks1'   => __( 'KS1 (Y1–Y2)', 'aiad-core' ),
					'ks2'   => __( 'KS2 (Y3–Y6)', 'aiad-core' ),
					'ks3'   => __( 'KS3 (Y7–Y9)', 'aiad-core' ),
					'ks4'   => __( 'KS4 (Y10–Y11)', 'aiad-core' ),
					'ks5'   => __( 'KS5 / 6th Form', 'aiad-core' ),
					'staff' => __( 'Staff CPD only', 'aiad-core' ),
				);
				?>

				<!-- Q1: School type -->
				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-school-type">
						1. <?php esc_html_e( 'School type', 'aiad-core' ); ?>
					</label>
					<select class="aiad-survey__select" id="survey-school-type" name="school_type">
						<option value=""><?php esc_html_e( '— Select —', 'aiad-core' ); ?></option>
						<option value="primary"><?php esc_html_e( 'Primary', 'aiad-core' ); ?></option>
						<option value="secondary"><?php esc_html_e( 'Secondary', 'aiad-core' ); ?></option>
						<option value="all_through"><?php esc_html_e( 'All-through (4–18)', 'aiad-core' ); ?></option>
						<option value="special"><?php esc_html_e( 'Special / Alternative Provision', 'aiad-core' ); ?></option>
						<option value="fe_college"><?php esc_html_e( 'FE College / Sixth Form', 'aiad-core' ); ?></option>
						<option value="higher_education"><?php esc_html_e( 'Higher Education', 'aiad-core' ); ?></option>
						<option value="mat_trust"><?php esc_html_e( 'MAT / Trust (multiple schools)', 'aiad-core' ); ?></option>
					</select>
				</div>

				<!-- Q2a: Year groups (schools) -->
				<div class="aiad-survey__field" id="survey-year-groups-wrap">
					<p class="aiad-survey__label">
						2. <?php esc_html_e( 'Which year groups do you work with? (tick all that apply)', 'aiad-core' ); ?>
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

				<!-- Q2b: HE levels (only shown when Higher Education selected) -->
				<div class="aiad-survey__field" id="survey-he-levels-wrap" style="display:none;">
					<p class="aiad-survey__label">
						2. <?php esc_html_e( 'Which levels do you work with? (tick all that apply)', 'aiad-core' ); ?>
					</p>
					<div class="aiad-survey__checkgroup">
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="year_groups[]" value="undergraduate" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'Undergraduate (Years 1–4)', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="year_groups[]" value="postgraduate" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'Postgraduate (Master\'s / PhD)', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="year_groups[]" value="staff" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'Staff CPD only', 'aiad-core' ); ?>
						</label>
					</div>
				</div>

				<!-- Q3: Role -->
				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-role">
						3. <?php esc_html_e( 'What is your role within your institution?', 'aiad-core' ); ?>
						<span class="aiad-survey__required" aria-hidden="true">*</span>
					</label>
					<input class="aiad-survey__input" type="text" id="survey-role" name="role" required
						placeholder="<?php esc_attr_e( 'e.g. Head teacher, Deputy, Computing lead', 'aiad-core' ); ?>"
						maxlength="200" autocomplete="organization-title" />
				</div>

				<!-- Q4: Participation gate -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						4. <?php esc_html_e( 'Did your school or classroom participate in National AI Awareness Day (2026)?', 'aiad-core' ); ?>
						<span class="aiad-survey__required" aria-hidden="true">*</span>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="participated" value="yes" class="aiad-survey__radio" id="survey-participated-yes" required />
							<?php esc_html_e( 'Yes — we took part', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="participated" value="no" class="aiad-survey__radio" id="survey-participated-no" required />
							<?php esc_html_e( 'No — we did not participate', 'aiad-core' ); ?>
						</label>
					</div>
				</div>

				<!-- Q5: Participation scale — only shown if "yes" (JS toggles visibility) -->
				<div class="aiad-survey__field" id="survey-participation-scale-wrap">
					<p class="aiad-survey__label">
						5. <?php esc_html_e( 'What was the approximate level of student participation in your school on 4th June?', 'aiad-core' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="participation_scale" value="single_classroom" class="aiad-survey__radio" />
							<?php esc_html_e( 'Single classroom focus (under 30 pupils)', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="participation_scale" value="year_group" class="aiad-survey__radio" />
							<?php esc_html_e( 'Year-group cohort intervention (30–120 pupils)', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="participation_scale" value="whole_school" class="aiad-survey__radio" />
							<?php esc_html_e( 'Whole-school deployment / Dedicated Assembly (120+ pupils)', 'aiad-core' ); ?>
						</label>
					</div>
				</div>
			</fieldset>

			<!-- ── STEP: school-readiness ── Display board & policy (everyone, separate from participation gate) -->
			<fieldset class="aiad-survey__step" data-step-id="school-readiness" aria-labelledby="aiad-survey-step-school-readiness-title">
				<h2 class="aiad-survey__step-title" id="aiad-survey-step-school-readiness-title">
					<?php esc_html_e( 'School AI readiness', 'aiad-core' ); ?>
				</h2>

				<p class="aiad-survey__helper">
					<?php esc_html_e( 'A quick snapshot of how AI Awareness is taking shape in your school — from the curriculum to corridors and classrooms.', 'aiad-core' ); ?>
				</p>

				<!-- Q1: AI in curriculum -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						1. <?php esc_html_e( 'Have you embedded AI activities into your curriculum?', 'aiad-core' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="curriculum_embedded" value="yes" class="aiad-survey__radio" />
							<?php esc_html_e( 'Yes — AI activities are part of our curriculum.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="curriculum_embedded" value="in_development" class="aiad-survey__radio" />
							<?php esc_html_e( 'In development — we are starting to embed AI activities.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="curriculum_embedded" value="no" class="aiad-survey__radio" />
							<?php esc_html_e( 'Not yet — we have not embedded AI activities.', 'aiad-core' ); ?>
						</label>
					</div>
				</div>

				<!-- Q2: Display board -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						2. <?php esc_html_e( 'As you may have seen through AI Awareness Day, does your school have or are you considering an AI Awareness display board to raise literacy within corridors or classrooms?', 'aiad-core' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="display_board" value="yes" class="aiad-survey__radio" />
							<?php esc_html_e( 'Yes — we have one on display.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="display_board" value="in_development" class="aiad-survey__radio" />
							<?php esc_html_e( 'In development — we are putting one together.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="display_board" value="no" class="aiad-survey__radio" />
							<?php esc_html_e( 'No — we do not have one.', 'aiad-core' ); ?>
						</label>
					</div>
				</div>

				<!-- Q3: AI policy -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						3. <?php esc_html_e( 'Does your school have an AI Awareness / AI-use policy?', 'aiad-core' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="ai_policy" value="yes" class="aiad-survey__radio" />
							<?php esc_html_e( 'Yes — we have a policy in place.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="ai_policy" value="in_development" class="aiad-survey__radio" />
							<?php esc_html_e( 'In development — a policy is being written.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="ai_policy" value="no" class="aiad-survey__radio" />
							<?php esc_html_e( 'No — we do not have one yet.', 'aiad-core' ); ?>
						</label>
					</div>
				</div>
			</fieldset>

			<!-- ── STEP: hopes ── Part 1: Hopes, Attitudinal Shifts & Student Empowerment (participants only) -->
			<fieldset class="aiad-survey__step" data-step-id="hopes" data-path="participant" aria-labelledby="aiad-survey-step-hopes-title">
				<h2 class="aiad-survey__step-title" id="aiad-survey-step-hopes-title">
					<?php esc_html_e( 'Part 1 — Hopes, attitudinal shifts &amp; student empowerment', 'aiad-core' ); ?>
				</h2>

				<p class="aiad-survey__section-note">
					<?php esc_html_e( 'This section tracks whether the campaign succeeded in changing students\' mindsets from passive technology consumers to empowered, confident digital architects.', 'aiad-core' ); ?>
				</p>

				<!-- Q1: Primary hope -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						1. <?php esc_html_e( 'What was your primary hope for your students when signing up for AI Awareness Day?', 'aiad-core' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="primary_hope" value="demystify" class="aiad-survey__radio" />
							<?php esc_html_e( 'To lower their anxiety and demystify how artificial intelligence works.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="primary_hope" value="confidence_skills" class="aiad-survey__radio" />
							<?php esc_html_e( 'To give them the confidence and practical skills to use AI tools for creative problem solving.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="primary_hope" value="digital_resilience" class="aiad-survey__radio" />
							<?php esc_html_e( 'To build their digital resilience and teach them to critically check for fake or biased information.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="primary_hope" value="career_interest" class="aiad-survey__radio" />
							<?php esc_html_e( 'To spark their interest in computer science, machine learning, and future tech careers.', 'aiad-core' ); ?>
						</label>
					</div>
				</div>

				<!-- Q2: Immediate effect Likert scales -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						2. <?php esc_html_e( 'Rate the immediate effect of the campaign day on your students\' confidence and learning habits (1 = Strongly Disagree, 5 = Strongly Agree):', 'aiad-core' ); ?>
					</p>

					<?php
					$empowerment_likerts = array(
						'rating_student_empowerment' => __(
							'Student Empowerment: Students showed greater confidence using AI tools as an active brainstorming partner rather than just a quick cheating shortcut.',
							'aiad-core'
						),
						'rating_critical_skepticism' => __(
							'Critical Scepticism: The activities successfully gave students a healthy scepticism, helping them spot hallucinations and biased data trends.',
							'aiad-core'
						),
						'rating_inclusivity' => __(
							'Inclusivity & Access: The jargon-free, interactive format gave non-technical and less confident students an equal voice in class discussions.',
							'aiad-core'
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
						3. <?php esc_html_e( 'Did the campaign leave a lasting positive effect on your classroom environment?', 'aiad-core' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="lasting_effect" value="significant" class="aiad-survey__radio" />
							<?php esc_html_e( 'Yes — it completely changed the dynamic. Students are now routinely questioning and checking text/image generator outputs.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="lasting_effect" value="moderate" class="aiad-survey__radio" />
							<?php esc_html_e( 'Moderate effect — it raised general awareness about data privacy, but students still treat algorithmic outputs as mostly correct.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="lasting_effect" value="none" class="aiad-survey__radio" />
							<?php esc_html_e( 'No measurable effect — students enjoyed the drop-down activity but quickly went back to using AI as an unchecked pass-through answer generator.', 'aiad-core' ); ?>
						</label>
					</div>
				</div>
			</fieldset>

			<!-- ── STEP: participant-feedback ── Resources, impact & 2027 (participants only) -->
			<fieldset class="aiad-survey__step" data-step-id="participant-feedback" data-path="participant" aria-labelledby="aiad-survey-step-participant-feedback-title">
				<h2 class="aiad-survey__step-title" id="aiad-survey-step-participant-feedback-title">
					<?php esc_html_e( 'Your experience &amp; planning for 2027', 'aiad-core' ); ?>
				</h2>

				<p class="aiad-survey__helper">
					<?php esc_html_e( 'Tell us how the resources worked in practice, what changed in your school, and what would help next time.', 'aiad-core' ); ?>
				</p>

				<h3 class="aiad-survey__subsection-title">
					<?php esc_html_e( 'Resources &amp; classroom delivery', 'aiad-core' ); ?>
				</h3>

				<!-- Q4: Prep time -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						4. <?php esc_html_e( 'The "Just One Activity" delivery model is designed to minimise prep time. How long did it take you to get your campaign resources ready to teach?', 'aiad-core' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="prep_time" value="zero" class="aiad-survey__radio" />
							<?php esc_html_e( 'Zero prep — we picked it up and streamed / ran it instantly.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="prep_time" value="under_15" class="aiad-survey__radio" />
							<?php esc_html_e( 'Less than 15 minutes of minor review.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="prep_time" value="15_to_45" class="aiad-survey__radio" />
							<?php esc_html_e( '15 to 45 minutes (required tweaking for our class level).', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="prep_time" value="over_45" class="aiad-survey__radio" />
							<?php esc_html_e( 'Over 45 minutes (too complex / required too much background reading).', 'aiad-core' ); ?>
						</label>
					</div>
				</div>

				<!-- Q5: Likert scales -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						5. <?php esc_html_e( 'Rate the accessibility of the core classroom resource assets (1 = Strongly Disagree, 5 = Strongly Agree):', 'aiad-core' ); ?>
					</p>

					<?php
					$likerts = array(
						'rating_plug_and_play' => __(
							'"Plug and Play" Usability: Non-technical teachers on my staff could deploy the lesson slides confidently without prior AI training.',
							'aiad-core'
						),
						'rating_student_access' => __(
							'Student Accessibility: The vocabulary and concepts matched my students\' age group and cognitive load.',
							'aiad-core'
						),
						'rating_tech_delivery' => __(
							'Technical Delivery: The resources (live streams, slide decks, video links) loaded flawlessly on our school\'s network/firewall infrastructure.',
							'aiad-core'
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
						6. <?php esc_html_e( 'Overall, how well did the AI Awareness Day teaching materials meet your needs?', 'aiad-core' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="materials_quality" value="ideal" class="aiad-survey__radio" />
							<?php esc_html_e( 'Ideal — comprehensive and ready to teach, exceeded what we needed.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="materials_quality" value="adequate" class="aiad-survey__radio" />
							<?php esc_html_e( 'Adequate — enough to deliver a solid session without much extra work.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="materials_quality" value="basic" class="aiad-survey__radio" />
							<?php esc_html_e( 'Basic — usable, but we had to supplement them with our own resources.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="materials_quality" value="inadequate" class="aiad-survey__radio" />
							<?php esc_html_e( 'Inadequate — not enough to teach from confidently.', 'aiad-core' ); ?>
						</label>
					</div>
				</div>

				<!-- Q7: Which session formats proved useful -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						7. <?php esc_html_e( 'Which session formats proved useful in your setting? (tick all that worked)', 'aiad-core' ); ?>
					</p>
					<div class="aiad-survey__checkgroup aiad-survey__checkgroup--single">
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="useful_formats[]" value="starter_5" class="aiad-survey__checkbox" />
							<?php esc_html_e( '5-minute lesson starter', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="useful_formats[]" value="tutor_15" class="aiad-survey__checkbox" />
							<?php esc_html_e( '15-minute tutor-group session', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="useful_formats[]" value="assembly_20" class="aiad-survey__checkbox" />
							<?php esc_html_e( '20-minute assembly slides', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="useful_formats[]" value="none" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'None of these lengths fitted our timetable', 'aiad-core' ); ?>
						</label>
					</div>
				</div>

				<!-- Q8: Best content format -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						8. <?php esc_html_e( 'Which content format works best for your classroom?', 'aiad-core' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="best_format" value="video" class="aiad-survey__radio" />
							<?php esc_html_e( 'Video — ready to play, minimal teacher input.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="best_format" value="slides" class="aiad-survey__radio" />
							<?php esc_html_e( 'Presentation slides we can deliver ourselves.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="best_format" value="teacher_instructions" class="aiad-survey__radio" />
							<?php esc_html_e( 'Teacher breakdown — step-by-step instructions and talking points.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="best_format" value="mix" class="aiad-survey__radio" />
							<?php esc_html_e( 'A mix — no single format stood out.', 'aiad-core' ); ?>
						</label>
					</div>
				</div>

				<h3 class="aiad-survey__subsection-title">
					<?php esc_html_e( 'Staff room &amp; wider impact', 'aiad-core' ); ?>
				</h3>

				<!-- Staff room / parental network impact -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						9. <?php esc_html_e( 'How has the campaign impacted your staff room or parental network? (Select all that apply)', 'aiad-core' ); ?>
					</p>
					<div class="aiad-survey__checkgroup aiad-survey__checkgroup--single">
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="staffroom_impact[]" value="admin_workload" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'It opened up an active conversation among non-technical teachers about using AI to reduce administrative/marking workloads.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="staffroom_impact[]" value="data_privacy" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'It helped us map out clear, safe boundaries for students regarding data privacy and cloud LLM usage.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="staffroom_impact[]" value="parents_engaged" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'It engaged parents through home-learning links, making AI discussions less intimidating at home.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="staffroom_impact[]" value="no_change" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'It has not yet altered wider staff or parental engagement trends.', 'aiad-core' ); ?>
						</label>
					</div>
				</div>

				<h3 class="aiad-survey__subsection-title">
					<?php esc_html_e( 'Planning for 2027', 'aiad-core' ); ?>
				</h3>

				<!-- Bottleneck -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						10. <?php esc_html_e( 'What was the single biggest bottleneck you faced when executing the day?', 'aiad-core' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="bottleneck" value="time" class="aiad-survey__radio" />
							<?php esc_html_e( 'Time constraints: The school timetable/curriculum map is too packed to pause for a themed drop-down day.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="bottleneck" value="tech_barriers" class="aiad-survey__radio" />
							<?php esc_html_e( 'Tech barriers: School firewall restrictions blocked outbound links, video elements, or interactive platform modules.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="bottleneck" value="staff_hesitation" class="aiad-survey__radio" />
							<?php esc_html_e( 'Staff hesitation: Non-specialist teachers felt anxious or lacked the confidence to answer student questions about machine learning.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="bottleneck" value="physical_assets" class="aiad-survey__radio" />
							<?php esc_html_e( 'Lack of physical assets: We wanted ready-made physical display boards, sticker packs, or printed student toolkits.', 'aiad-core' ); ?>
						</label>
					</div>
				</div>

				<!-- Q9: Support modules for 2027 -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						11. <?php esc_html_e( 'What high-priority support modules should National AI Awareness Day add for 2027? (tick all that apply)', 'aiad-core' ); ?>
					</p>
					<div class="aiad-survey__checkgroup aiad-survey__checkgroup--single">
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="support_modules[]" value="display_kits" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'Pre-packaged Display Board Kits: Digital and printable templates to instantly create interactive AI literacy spaces in school corridors.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="support_modules[]" value="cross_curricular" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'Cross-Curricular Schemes of Work: Explicit mapping showing how to weave the "Question It" principle into English, History, Art, and Science.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="support_modules[]" value="cpd_pathways" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'Structured Faculty CPD Pathways: Certified, 30-minute bite-sized training webinars for staff rooms ahead of the launch day.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="support_modules[]" value="pta_packs" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'Parent-Teacher Association (PTA) Interactive Packs: Step-by-step evening workshop templates to deliver direct to local families.', 'aiad-core' ); ?>
						</label>
					</div>
				</div>

				<!-- Q10: Open qualitative feedback -->
				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-open-feedback">
						12. <?php esc_html_e( 'Please share any open feedback from your classroom. What worked best, and what is one thing we must fix for 2027?', 'aiad-core' ); ?>
					</label>
					<textarea class="aiad-survey__textarea" id="survey-open-feedback" name="open_feedback"
						rows="5" maxlength="2000"
						placeholder="<?php esc_attr_e( 'Share a specific classroom anecdote, a highlight, or a concrete fix…', 'aiad-core' ); ?>"></textarea>
				</div>
			</fieldset>

			<!-- ── STEP: non-participant-a ── Part 1B block A (non-participants only) -->
			<fieldset class="aiad-survey__step" data-step-id="non-participant-a" data-path="non-participant" aria-labelledby="aiad-survey-step-non-participant-a-title">
				<h2 class="aiad-survey__step-title" id="aiad-survey-step-non-participant-a-title">
					<?php esc_html_e( 'Part 1B — Understanding the barriers', 'aiad-core' ); ?>
				</h2>

				<p class="aiad-survey__section-note">
					<?php esc_html_e( 'Understanding why schools did not take part is just as important as hearing from those who did. Your answers directly shape the 2027 campaign.', 'aiad-core' ); ?>
				</p>

				<!-- Q1B-1: Primary reason for non-participation -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						1. <?php esc_html_e( 'What was the primary reason your school or classroom did not participate in National AI Awareness Day this year?', 'aiad-core' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="non_part_reason" value="timetable" class="aiad-survey__radio" />
							<?php esc_html_e( 'Curriculum/Timetable Pressure: We were locked into rigid end-of-term exam prep, mock assessments, or fixed assessment windows.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="non_part_reason" value="late_discovery" class="aiad-survey__radio" />
							<?php esc_html_e( 'Late Discovery: We only heard about the campaign a few days before 4th June and didn\'t have enough notice to get SLT sign-off.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="non_part_reason" value="staff_confidence" class="aiad-survey__radio" />
							<?php esc_html_e( 'Lack of Staff Confidence: Non-specialist staff felt anxious about answering student questions on machine learning and LLM ethics.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="non_part_reason" value="firewall" class="aiad-survey__radio" />
							<?php esc_html_e( 'Tech/Infrastructure Restrictions: Our school firewall completely blocks generative AI sandboxes (like ChatGPT/Claude), so we assumed the day wasn\'t viable.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="non_part_reason" value="banned_policy" class="aiad-survey__radio" />
							<?php esc_html_e( 'Banned Tool Policy: Our current school or MAT policy enforces an outright ban on AI discussion or interaction.', 'aiad-core' ); ?>
						</label>
					</div>
				</div>

				<!-- Q1B-2: Staff room attitude -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						2. <?php esc_html_e( 'Which of the following statements best describes the attitude toward AI tools within your school\'s current staff room?', 'aiad-core' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="staffroom_attitude" value="anxiety" class="aiad-survey__radio" />
							<?php esc_html_e( 'Anxiety/Resistance: Staff view AI primarily as a plagiarism hazard or a threat to traditional student writing development.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="staffroom_attitude" value="overwhelmed" class="aiad-survey__radio" />
							<?php esc_html_e( 'Overwhelmed: Teachers are interested in workload reduction but simply do not have the headspace or time to learn a new system.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="staffroom_attitude" value="isolated" class="aiad-survey__radio" />
							<?php esc_html_e( 'Interested but Isolated: Individual teachers are experimenting with AI prompts, but there is no joined-up, school-wide strategy.', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="staffroom_attitude" value="apathy" class="aiad-survey__radio" />
							<?php esc_html_e( 'Apathy: Staff do not see AI as a high-priority issue for our specific demographic or age group right now.', 'aiad-core' ); ?>
						</label>
					</div>
				</div>
			</fieldset>

			<!-- ── STEP: reach-impact ── Reach, confidence & next steps (everyone; path-specific fields) -->
			<fieldset class="aiad-survey__step" data-step-id="reach-impact" aria-labelledby="aiad-survey-step-reach-impact-title">
				<h2 class="aiad-survey__step-title" id="aiad-survey-step-reach-impact-title">
					<?php esc_html_e( 'Reach &amp; impact', 'aiad-core' ); ?>
				</h2>

				<p class="aiad-survey__helper">
					<?php esc_html_e( 'A few quick questions to help us measure reach and plan for 2027.', 'aiad-core' ); ?>
				</p>

				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						1. <?php esc_html_e( 'How did you hear about AI Awareness Day?', 'aiad-core' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="hear_about" value="social" class="aiad-survey__radio" />
							<?php esc_html_e( 'Social media', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="hear_about" value="email" class="aiad-survey__radio" />
							<?php esc_html_e( 'Email or newsletter', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="hear_about" value="colleague" class="aiad-survey__radio" />
							<?php esc_html_e( 'Colleague or word of mouth', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="hear_about" value="mat_trust" class="aiad-survey__radio" />
							<?php esc_html_e( 'MAT / trust communication', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="hear_about" value="dfe_org" class="aiad-survey__radio" />
							<?php esc_html_e( 'DfE or other organisation', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="hear_about" value="search" class="aiad-survey__radio" />
							<?php esc_html_e( 'Web search', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="hear_about" value="other" class="aiad-survey__radio" />
							<?php esc_html_e( 'Other', 'aiad-core' ); ?>
						</label>
					</div>
				</div>

				<div class="aiad-survey__path-participant" hidden>
					<div class="aiad-survey__field">
						<p class="aiad-survey__label">
							2. <?php esc_html_e( 'Before AI Awareness Day, how confident did you feel discussing AI in education?', 'aiad-core' ); ?>
						</p>
						<div class="aiad-survey__radio-group">
							<?php
							$confidence_options = array(
								'very'     => __( 'Very confident', 'aiad-core' ),
								'fairly'   => __( 'Fairly confident', 'aiad-core' ),
								'not_very' => __( 'Not very confident', 'aiad-core' ),
								'not'      => __( 'Not confident', 'aiad-core' ),
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
							3. <?php esc_html_e( 'After taking part, how confident do you feel discussing AI in education?', 'aiad-core' ); ?>
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
							4. <?php esc_html_e( 'As a result of AI Awareness Day, what is the one action you intend to take next?', 'aiad-core' ); ?>
						</p>
						<div class="aiad-survey__radio-group">
							<label class="aiad-survey__radio-label">
								<input type="radio" name="intended_action" value="learn_more" class="aiad-survey__radio" />
								<?php esc_html_e( 'Learn more about AI', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="intended_action" value="review_policy" class="aiad-survey__radio" />
								<?php esc_html_e( 'Review school policy', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="intended_action" value="discuss_students" class="aiad-survey__radio" />
								<?php esc_html_e( 'Discuss AI with students', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="intended_action" value="discuss_child" class="aiad-survey__radio" />
								<?php esc_html_e( 'Discuss AI with my child', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="intended_action" value="safeguarding" class="aiad-survey__radio" />
								<?php esc_html_e( 'Improve safeguarding processes', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="intended_action" value="assessment" class="aiad-survey__radio" />
								<?php esc_html_e( 'Review assessment approaches', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="intended_action" value="explore_tools" class="aiad-survey__radio" />
								<?php esc_html_e( 'Explore AI tools', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="intended_action" value="no_action" class="aiad-survey__radio" />
								<?php esc_html_e( 'No immediate action', 'aiad-core' ); ?>
							</label>
						</div>
					</div>
				</div>

				<div class="aiad-survey__path-non-participant" hidden>
					<div class="aiad-survey__field">
						<p class="aiad-survey__label">
							2. <?php esc_html_e( 'Had you heard of AI Awareness Day before today?', 'aiad-core' ); ?>
						</p>
						<div class="aiad-survey__radio-group">
							<label class="aiad-survey__radio-label">
								<input type="radio" name="heard_before" value="yes" class="aiad-survey__radio" />
								<?php esc_html_e( 'Yes', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="heard_before" value="no" class="aiad-survey__radio" />
								<?php esc_html_e( 'No', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="heard_before" value="not_sure" class="aiad-survey__radio" />
								<?php esc_html_e( 'Not sure', 'aiad-core' ); ?>
							</label>
						</div>
					</div>

					<div class="aiad-survey__field">
						<p class="aiad-survey__label">
							3. <?php esc_html_e( 'What concerns you most about AI in education?', 'aiad-core' ); ?>
						</p>
						<div class="aiad-survey__radio-group">
							<label class="aiad-survey__radio-label">
								<input type="radio" name="top_concern" value="misinformation" class="aiad-survey__radio" />
								<?php esc_html_e( 'Misinformation and unreliable outputs', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="top_concern" value="privacy" class="aiad-survey__radio" />
								<?php esc_html_e( 'Privacy and data protection', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="top_concern" value="safeguarding" class="aiad-survey__radio" />
								<?php esc_html_e( 'Safeguarding and online harm', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="top_concern" value="deepfakes" class="aiad-survey__radio" />
								<?php esc_html_e( 'Deepfakes and impersonation', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="top_concern" value="integrity" class="aiad-survey__radio" />
								<?php esc_html_e( 'Academic integrity and cheating', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="top_concern" value="bias" class="aiad-survey__radio" />
								<?php esc_html_e( 'Bias and fairness', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="top_concern" value="over_reliance" class="aiad-survey__radio" />
								<?php esc_html_e( 'Over-reliance on AI', 'aiad-core' ); ?>
							</label>
						</div>
					</div>

					<div class="aiad-survey__field">
						<p class="aiad-survey__label">
							4. <?php esc_html_e( 'What support would help your school take part next year? (tick all that apply)', 'aiad-core' ); ?>
						</p>
						<div class="aiad-survey__checkgroup aiad-survey__checkgroup--single">
							<label class="aiad-survey__check-label">
								<input type="checkbox" name="support_needed[]" value="training" class="aiad-survey__checkbox" />
								<?php esc_html_e( 'Staff training / CPD', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__check-label">
								<input type="checkbox" name="support_needed[]" value="policy" class="aiad-survey__checkbox" />
								<?php esc_html_e( 'Policy templates and governance guidance', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__check-label">
								<input type="checkbox" name="support_needed[]" value="parent_resources" class="aiad-survey__checkbox" />
								<?php esc_html_e( 'Parent guides and family resources', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__check-label">
								<input type="checkbox" name="support_needed[]" value="student_resources" class="aiad-survey__checkbox" />
								<?php esc_html_e( 'Student classroom resources', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__check-label">
								<input type="checkbox" name="support_needed[]" value="safeguarding" class="aiad-survey__checkbox" />
								<?php esc_html_e( 'Safeguarding guidance', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__check-label">
								<input type="checkbox" name="support_needed[]" value="case_studies" class="aiad-survey__checkbox" />
								<?php esc_html_e( 'Case studies from other schools', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__check-label">
								<input type="checkbox" name="support_needed[]" value="webinars" class="aiad-survey__checkbox" />
								<?php esc_html_e( 'Webinars or live briefings', 'aiad-core' ); ?>
							</label>
						</div>
					</div>

					<div class="aiad-survey__field">
						<p class="aiad-survey__label">
							5. <?php esc_html_e( 'Would you participate in AI Awareness Day next year?', 'aiad-core' ); ?>
						</p>
						<div class="aiad-survey__radio-group">
							<label class="aiad-survey__radio-label">
								<input type="radio" name="participate_next_year" value="definitely" class="aiad-survey__radio" />
								<?php esc_html_e( 'Definitely', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="participate_next_year" value="probably" class="aiad-survey__radio" />
								<?php esc_html_e( 'Probably', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="participate_next_year" value="maybe" class="aiad-survey__radio" />
								<?php esc_html_e( 'Maybe', 'aiad-core' ); ?>
							</label>
							<label class="aiad-survey__radio-label">
								<input type="radio" name="participate_next_year" value="unlikely" class="aiad-survey__radio" />
								<?php esc_html_e( 'Unlikely', 'aiad-core' ); ?>
							</label>
						</div>
					</div>
				</div>

				<div class="aiad-survey__field">
					<p class="aiad-survey__label aiad-survey__label--recommend">
						<span class="aiad-survey__recommend-label-participant" hidden>
							5. <?php esc_html_e( 'How likely are you to recommend AI Awareness Day to another school or colleague?', 'aiad-core' ); ?>
						</span>
						<span class="aiad-survey__recommend-label-non-participant" hidden>
							6. <?php esc_html_e( 'How likely are you to recommend AI Awareness Day to another school or colleague?', 'aiad-core' ); ?>
						</span>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="recommend" value="definitely" class="aiad-survey__radio" />
							<?php esc_html_e( 'Definitely', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="recommend" value="probably" class="aiad-survey__radio" />
							<?php esc_html_e( 'Probably', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="recommend" value="maybe" class="aiad-survey__radio" />
							<?php esc_html_e( 'Maybe', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="recommend" value="unlikely" class="aiad-survey__radio" />
							<?php esc_html_e( 'Unlikely', 'aiad-core' ); ?>
						</label>
					</div>
				</div>

				<input type="hidden" name="survey_version" value="<?php echo esc_attr( AIAD_SURVEY_VERSION ); ?>" />
			</fieldset>

			<!-- ── STEP: contact ── School details & stay in touch (everyone) -->
			<fieldset class="aiad-survey__step" data-step-id="contact" aria-labelledby="aiad-survey-step-contact-title">
				<h2 class="aiad-survey__step-title" id="aiad-survey-step-contact-title">
					<?php esc_html_e( 'Your school &amp; stay in touch', 'aiad-core' ); ?>
				</h2>

				<p class="aiad-survey__helper">
					<?php esc_html_e( 'This survey is anonymous unless you choose to share your school name or email below. All fields on this step are optional — add contact details only if you would like 2027 early access or are happy for an anonymised quote to appear in our national report.', 'aiad-core' ); ?>
				</p>

				<!-- School name -->
				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-school-name">
						<?php esc_html_e( 'School name (optional)', 'aiad-core' ); ?>
					</label>
					<input class="aiad-survey__input" type="text" id="survey-school-name" name="school_name"
						placeholder="<?php esc_attr_e( 'e.g. Westfield Academy…', 'aiad-core' ); ?>" maxlength="200" autocomplete="organization" />
				</div>

				<!-- Email -->
				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-email">
						<?php esc_html_e( 'Email address (optional)', 'aiad-core' ); ?>
					</label>
					<input class="aiad-survey__input" type="email" id="survey-email" name="contact_email"
						placeholder="<?php esc_attr_e( 'Optional — for 2027 early access', 'aiad-core' ); ?>" maxlength="200" autocomplete="email" />
				</div>

				<!-- Preferred communication channels -->
				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						<?php esc_html_e( 'How would you prefer to receive regular updates? (tick all that apply)', 'aiad-core' ); ?>
					</p>
					<div class="aiad-survey__checkgroup aiad-survey__checkgroup--single">
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="comms_preference[]" value="website_timeline" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'Regular updates on the website via the timeline', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="comms_preference[]" value="linkedin" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'LinkedIn', 'aiad-core' ); ?>
						</label>
						<label class="aiad-survey__check-label">
							<input type="checkbox" name="comms_preference[]" value="newsletter" class="aiad-survey__checkbox" />
							<?php esc_html_e( 'Email newsletter', 'aiad-core' ); ?>
						</label>
					</div>
				</div>

				<div class="aiad-survey__field">
					<label class="aiad-survey__check-label">
						<input type="checkbox" name="permission_quote" value="1" class="aiad-survey__checkbox" />
						<?php esc_html_e( 'You may include an anonymised version of my feedback in public reports or research communications.', 'aiad-core' ); ?>
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
					<?php esc_html_e( '← Back', 'aiad-core' ); ?>
				</button>
				<button type="button" class="aiad-survey__btn aiad-survey__btn--next" id="aiad-survey-next">
					<?php esc_html_e( 'Next →', 'aiad-core' ); ?>
				</button>
				<button type="button" class="aiad-survey__btn aiad-survey__btn--submit" id="aiad-survey-submit" hidden>
					<?php esc_html_e( 'Submit survey', 'aiad-core' ); ?>
				</button>
			</div>

			<div class="aiad-survey__error" id="aiad-survey-error" role="alert" hidden></div>

		</form>

		<!-- Success message -->
		<div class="aiad-survey__success" id="aiad-survey-success" hidden>
			<div class="aiad-survey__success-icon" aria-hidden="true">✓</div>
			<h2 class="aiad-survey__success-title">
				<?php esc_html_e( 'Thank you!', 'aiad-core' ); ?>
			</h2>
			<p class="aiad-survey__success-body">
				<?php esc_html_e( 'Your response has been recorded and will help shape AI Awareness Day 2027.', 'aiad-core' ); ?>
			</p>
		</div>

		<p class="aiad-survey__credit">
			<?php esc_html_e( 'Produced by', 'aiad-core' ); ?>
			<strong><?php esc_html_e( 'AI Awareness Day', 'aiad-core' ); ?></strong>
			·
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'aiawarenessday.com' ); ?></a>
		</p>

	</div>
	<?php
	return ob_get_clean();
}
