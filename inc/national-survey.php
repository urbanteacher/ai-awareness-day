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
		<p class="aiad-survey__progress-label" id="aiad-survey-progress-label">
			<?php esc_html_e( 'Step 1 of 6', 'ai-awareness-day' ); ?>
		</p>

		<form class="aiad-survey__form" id="aiad-survey-form" novalidate>

			<!-- Step 1: About you -->
			<fieldset class="aiad-survey__step aiad-survey__step--active" data-step="1">
				<legend class="aiad-survey__step-title">
					<?php esc_html_e( 'About you', 'ai-awareness-day' ); ?>
				</legend>

				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-role">
						<?php esc_html_e( 'I am a…', 'ai-awareness-day' ); ?>
						<span class="aiad-survey__required" aria-hidden="true">*</span>
					</label>
					<select class="aiad-survey__select" id="survey-role" name="role" required>
						<option value=""><?php esc_html_e( 'Select your role', 'ai-awareness-day' ); ?></option>
						<option value="teacher"><?php esc_html_e( 'Teacher', 'ai-awareness-day' ); ?></option>
						<option value="school_leader"><?php esc_html_e( 'School Leader / Senior Leader', 'ai-awareness-day' ); ?></option>
						<option value="student"><?php esc_html_e( 'Student', 'ai-awareness-day' ); ?></option>
						<option value="parent"><?php esc_html_e( 'Parent / Carer', 'ai-awareness-day' ); ?></option>
						<option value="librarian"><?php esc_html_e( 'Librarian', 'ai-awareness-day' ); ?></option>
						<option value="organisation"><?php esc_html_e( 'Organisation / Partner', 'ai-awareness-day' ); ?></option>
						<option value="other"><?php esc_html_e( 'Other', 'ai-awareness-day' ); ?></option>
					</select>
				</div>

				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-school">
						<?php esc_html_e( 'School or organisation name', 'ai-awareness-day' ); ?>
					</label>
					<input class="aiad-survey__input" type="text" id="survey-school" name="school_name"
						placeholder="<?php esc_attr_e( 'Optional', 'ai-awareness-day' ); ?>" maxlength="200" />
				</div>

				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-region">
						<?php esc_html_e( 'Region', 'ai-awareness-day' ); ?>
					</label>
					<select class="aiad-survey__select" id="survey-region" name="region">
						<option value=""><?php esc_html_e( 'Select your region', 'ai-awareness-day' ); ?></option>
						<option value="east_midlands"><?php esc_html_e( 'East Midlands', 'ai-awareness-day' ); ?></option>
						<option value="east_of_england"><?php esc_html_e( 'East of England', 'ai-awareness-day' ); ?></option>
						<option value="london"><?php esc_html_e( 'London', 'ai-awareness-day' ); ?></option>
						<option value="north_east"><?php esc_html_e( 'North East', 'ai-awareness-day' ); ?></option>
						<option value="north_west"><?php esc_html_e( 'North West', 'ai-awareness-day' ); ?></option>
						<option value="northern_ireland"><?php esc_html_e( 'Northern Ireland', 'ai-awareness-day' ); ?></option>
						<option value="scotland"><?php esc_html_e( 'Scotland', 'ai-awareness-day' ); ?></option>
						<option value="south_east"><?php esc_html_e( 'South East', 'ai-awareness-day' ); ?></option>
						<option value="south_west"><?php esc_html_e( 'South West', 'ai-awareness-day' ); ?></option>
						<option value="wales"><?php esc_html_e( 'Wales', 'ai-awareness-day' ); ?></option>
						<option value="west_midlands"><?php esc_html_e( 'West Midlands', 'ai-awareness-day' ); ?></option>
						<option value="yorkshire"><?php esc_html_e( 'Yorkshire and the Humber', 'ai-awareness-day' ); ?></option>
						<option value="prefer_not_to_say"><?php esc_html_e( 'Prefer not to say', 'ai-awareness-day' ); ?></option>
					</select>
				</div>
			</fieldset>

			<!-- Step 2: Participation -->
			<fieldset class="aiad-survey__step" data-step="2">
				<legend class="aiad-survey__step-title">
					<?php esc_html_e( 'Your participation', 'ai-awareness-day' ); ?>
				</legend>

				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-heard">
						<?php esc_html_e( 'How did you hear about AI Awareness Day?', 'ai-awareness-day' ); ?>
					</label>
					<select class="aiad-survey__select" id="survey-heard" name="heard_via">
						<option value=""><?php esc_html_e( 'Select one', 'ai-awareness-day' ); ?></option>
						<option value="social_media"><?php esc_html_e( 'Social media', 'ai-awareness-day' ); ?></option>
						<option value="word_of_mouth"><?php esc_html_e( 'Word of mouth / colleague', 'ai-awareness-day' ); ?></option>
						<option value="email_newsletter"><?php esc_html_e( 'Email / newsletter', 'ai-awareness-day' ); ?></option>
						<option value="school_network"><?php esc_html_e( 'School / MAT network', 'ai-awareness-day' ); ?></option>
						<option value="search_engine"><?php esc_html_e( 'Search engine', 'ai-awareness-day' ); ?></option>
						<option value="press_media"><?php esc_html_e( 'Press / media', 'ai-awareness-day' ); ?></option>
						<option value="partner_organisation"><?php esc_html_e( 'Partner organisation', 'ai-awareness-day' ); ?></option>
						<option value="returning"><?php esc_html_e( 'I took part last year', 'ai-awareness-day' ); ?></option>
						<option value="other"><?php esc_html_e( 'Other', 'ai-awareness-day' ); ?></option>
					</select>
				</div>

				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						<?php esc_html_e( 'Which activities or resources did you use? (tick all that apply)', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__checkgroup">
						<?php
						$activities = array(
							'assembly'          => __( 'Assembly / presentation', 'ai-awareness-day' ),
							'live_session'      => __( 'Live session / webinar', 'ai-awareness-day' ),
							'classroom_activity'=> __( 'Classroom activity', 'ai-awareness-day' ),
							'display_board'     => __( 'Display board / poster', 'ai-awareness-day' ),
							'speed_quiz'        => __( 'AI Speed Quiz', 'ai-awareness-day' ),
							'llm_explainer'     => __( 'LLM Explainer', 'ai-awareness-day' ),
							'misinformation'    => __( 'Misinformation Detector', 'ai-awareness-day' ),
							'curriculum_quiz'   => __( 'Curriculum Quiz', 'ai-awareness-day' ),
							'certificate'       => __( 'Certificate of Participation', 'ai-awareness-day' ),
							'resources_pack'    => __( 'Resources / assets pack', 'ai-awareness-day' ),
							'other_activity'    => __( 'Other', 'ai-awareness-day' ),
						);
						foreach ( $activities as $val => $label ) :
							?>
							<label class="aiad-survey__check-label">
								<input type="checkbox" name="activities[]" value="<?php echo esc_attr( $val ); ?>" class="aiad-survey__checkbox" />
								<?php echo esc_html( $label ); ?>
							</label>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-participants">
						<?php esc_html_e( 'Approximately how many students or participants were involved?', 'ai-awareness-day' ); ?>
					</label>
					<select class="aiad-survey__select" id="survey-participants" name="participants">
						<option value=""><?php esc_html_e( 'Select a range', 'ai-awareness-day' ); ?></option>
						<option value="1_10"><?php esc_html_e( '1–10', 'ai-awareness-day' ); ?></option>
						<option value="11_30"><?php esc_html_e( '11–30', 'ai-awareness-day' ); ?></option>
						<option value="31_60"><?php esc_html_e( '31–60', 'ai-awareness-day' ); ?></option>
						<option value="61_150"><?php esc_html_e( '61–150', 'ai-awareness-day' ); ?></option>
						<option value="151_500"><?php esc_html_e( '151–500', 'ai-awareness-day' ); ?></option>
						<option value="500_plus"><?php esc_html_e( '500+', 'ai-awareness-day' ); ?></option>
						<option value="not_applicable"><?php esc_html_e( 'Not applicable', 'ai-awareness-day' ); ?></option>
					</select>
				</div>
			</fieldset>

			<!-- Step 3: Impact ratings -->
			<fieldset class="aiad-survey__step" data-step="3">
				<legend class="aiad-survey__step-title">
					<?php esc_html_e( 'Impact and experience', 'ai-awareness-day' ); ?>
				</legend>

				<p class="aiad-survey__helper">
					<?php esc_html_e( 'Rate each statement from 1 (strongly disagree) to 5 (strongly agree).', 'ai-awareness-day' ); ?>
				</p>

				<?php
				$ratings = array(
					'rating_overall'    => __( 'Overall, AI Awareness Day was worthwhile', 'ai-awareness-day' ),
					'rating_resources'  => __( 'The resources and activities were high quality', 'ai-awareness-day' ),
					'rating_accessible' => __( 'The activities were easy to use without prior AI knowledge', 'ai-awareness-day' ),
					'rating_confidence' => __( 'Taking part increased confidence in discussing AI', 'ai-awareness-day' ),
					'rating_age_range'  => __( 'The content was appropriate for the age range I work with', 'ai-awareness-day' ),
				);
				foreach ( $ratings as $name => $label ) :
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
			</fieldset>

			<!-- Step 4: Open feedback -->
			<fieldset class="aiad-survey__step" data-step="4">
				<legend class="aiad-survey__step-title">
					<?php esc_html_e( 'Your feedback', 'ai-awareness-day' ); ?>
				</legend>

				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-highlight">
						<?php esc_html_e( 'What was the most valuable part of AI Awareness Day for you or your students?', 'ai-awareness-day' ); ?>
					</label>
					<textarea class="aiad-survey__textarea" id="survey-highlight" name="highlight"
						rows="4" maxlength="1000"
						placeholder="<?php esc_attr_e( 'Share a highlight or memorable moment…', 'ai-awareness-day' ); ?>"></textarea>
				</div>

				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-improve">
						<?php esc_html_e( 'What could have been better?', 'ai-awareness-day' ); ?>
					</label>
					<textarea class="aiad-survey__textarea" id="survey-improve" name="improve"
						rows="4" maxlength="1000"
						placeholder="<?php esc_attr_e( 'Anything that didn't work well, was missing, or could be clearer…', 'ai-awareness-day' ); ?>"></textarea>
				</div>
			</fieldset>

			<!-- Step 5: Ideas for 2027 -->
			<fieldset class="aiad-survey__step" data-step="5">
				<legend class="aiad-survey__step-title">
					<?php esc_html_e( 'Looking ahead to 2027', 'ai-awareness-day' ); ?>
				</legend>

				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						<?php esc_html_e( 'What themes or topics would you like to see featured in 2027? (tick all that apply)', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__checkgroup">
						<?php
						$themes = array(
							'ai_ethics'       => __( 'AI ethics and bias', 'ai-awareness-day' ),
							'ai_creativity'   => __( 'AI and creativity (art, music, writing)', 'ai-awareness-day' ),
							'ai_safety'       => __( 'AI safety and regulation', 'ai-awareness-day' ),
							'ai_jobs'         => __( 'AI and future careers / jobs', 'ai-awareness-day' ),
							'ai_environment'  => __( 'AI and the environment', 'ai-awareness-day' ),
							'ai_health'       => __( 'AI in healthcare', 'ai-awareness-day' ),
							'ai_misinformation'=> __( 'Deepfakes and misinformation', 'ai-awareness-day' ),
							'ai_coding'       => __( 'AI and coding / computer science', 'ai-awareness-day' ),
							'ai_primary'      => __( 'More content for primary / KS1–KS2', 'ai-awareness-day' ),
							'ai_sixth_form'   => __( 'More content for sixth form / post-16', 'ai-awareness-day' ),
							'theme_other'     => __( 'Other (please tell us below)', 'ai-awareness-day' ),
						);
						foreach ( $themes as $val => $label ) :
							?>
							<label class="aiad-survey__check-label">
								<input type="checkbox" name="themes_2027[]" value="<?php echo esc_attr( $val ); ?>" class="aiad-survey__checkbox" />
								<?php echo esc_html( $label ); ?>
							</label>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="aiad-survey__field">
					<label class="aiad-survey__label" for="survey-suggestions">
						<?php esc_html_e( 'Any other ideas or suggestions for 2027?', 'ai-awareness-day' ); ?>
					</label>
					<textarea class="aiad-survey__textarea" id="survey-suggestions" name="suggestions_2027"
						rows="4" maxlength="1000"
						placeholder="<?php esc_attr_e( 'New activities, formats, partnerships, timing…', 'ai-awareness-day' ); ?>"></textarea>
				</div>

				<div class="aiad-survey__field">
					<p class="aiad-survey__label">
						<?php esc_html_e( 'Would you take part in AI Awareness Day again?', 'ai-awareness-day' ); ?>
					</p>
					<div class="aiad-survey__radio-group">
						<label class="aiad-survey__radio-label">
							<input type="radio" name="return_2027" value="yes" class="aiad-survey__radio" />
							<?php esc_html_e( 'Yes, definitely', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="return_2027" value="probably" class="aiad-survey__radio" />
							<?php esc_html_e( 'Probably', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="return_2027" value="unsure" class="aiad-survey__radio" />
							<?php esc_html_e( 'Not sure yet', 'ai-awareness-day' ); ?>
						</label>
						<label class="aiad-survey__radio-label">
							<input type="radio" name="return_2027" value="no" class="aiad-survey__radio" />
							<?php esc_html_e( 'Probably not', 'ai-awareness-day' ); ?>
						</label>
					</div>
				</div>
			</fieldset>

			<!-- Step 6: Optional contact details -->
			<fieldset class="aiad-survey__step" data-step="6">
				<legend class="aiad-survey__step-title">
					<?php esc_html_e( 'Stay in touch (optional)', 'ai-awareness-day' ); ?>
				</legend>

				<p class="aiad-survey__helper">
					<?php esc_html_e( 'Leave your details if you\'d like to be notified about 2027, or if we may quote your feedback (with permission) in our national report.', 'ai-awareness-day' ); ?>
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
						<?php esc_html_e( 'You may include an anonymised version of my feedback in public reports or communications.', 'ai-awareness-day' ); ?>
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
	$role           = sanitize_text_field( wp_unslash( $_POST['role'] ?? '' ) );
	$school_name    = sanitize_text_field( wp_unslash( $_POST['school_name'] ?? '' ) );
	$region         = sanitize_text_field( wp_unslash( $_POST['region'] ?? '' ) );
	$heard_via      = sanitize_text_field( wp_unslash( $_POST['heard_via'] ?? '' ) );
	$participants   = sanitize_text_field( wp_unslash( $_POST['participants'] ?? '' ) );
	$highlight      = sanitize_textarea_field( wp_unslash( $_POST['highlight'] ?? '' ) );
	$improve        = sanitize_textarea_field( wp_unslash( $_POST['improve'] ?? '' ) );
	$suggestions    = sanitize_textarea_field( wp_unslash( $_POST['suggestions_2027'] ?? '' ) );
	$return_2027    = sanitize_text_field( wp_unslash( $_POST['return_2027'] ?? '' ) );
	$contact_name   = sanitize_text_field( wp_unslash( $_POST['contact_name'] ?? '' ) );
	$contact_email  = sanitize_email( wp_unslash( $_POST['contact_email'] ?? '' ) );
	$perm_quote     = isset( $_POST['permission_quote'] ) ? '1' : '0';

	// Validate required field
	if ( empty( $role ) ) {
		wp_send_json_error( array( 'message' => __( 'Please tell us your role before submitting.', 'ai-awareness-day' ) ) );
	}

	// Sanitise array fields
	$allowed_activities = array(
		'assembly', 'live_session', 'classroom_activity', 'display_board',
		'speed_quiz', 'llm_explainer', 'misinformation', 'curriculum_quiz',
		'certificate', 'resources_pack', 'other_activity',
	);
	$raw_activities = isset( $_POST['activities'] ) && is_array( $_POST['activities'] )
		? array_map( 'sanitize_text_field', wp_unslash( $_POST['activities'] ) )
		: array();
	$activities = array_values( array_intersect( $raw_activities, $allowed_activities ) );

	$allowed_themes = array(
		'ai_ethics', 'ai_creativity', 'ai_safety', 'ai_jobs', 'ai_environment',
		'ai_health', 'ai_misinformation', 'ai_coding', 'ai_primary', 'ai_sixth_form', 'theme_other',
	);
	$raw_themes = isset( $_POST['themes_2027'] ) && is_array( $_POST['themes_2027'] )
		? array_map( 'sanitize_text_field', wp_unslash( $_POST['themes_2027'] ) )
		: array();
	$themes = array_values( array_intersect( $raw_themes, $allowed_themes ) );

	// Sanitise star ratings (1–5)
	$rating_fields = array( 'rating_overall', 'rating_resources', 'rating_accessible', 'rating_confidence', 'rating_age_range' );
	$ratings = array();
	foreach ( $rating_fields as $field ) {
		$val = (int) ( $_POST[ $field ] ?? 0 );
		$ratings[ $field ] = ( $val >= 1 && $val <= 5 ) ? $val : 0;
	}

	// Build a title for the admin list
	$title = sprintf(
		'%s — %s — %s',
		$role ?: 'unknown',
		$region ?: 'no region',
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
		'_survey_role'           => $role,
		'_survey_school_name'    => $school_name,
		'_survey_region'         => $region,
		'_survey_heard_via'      => $heard_via,
		'_survey_activities'     => wp_json_encode( $activities ),
		'_survey_participants'   => $participants,
		'_survey_rating_overall' => $ratings['rating_overall'],
		'_survey_rating_resources'   => $ratings['rating_resources'],
		'_survey_rating_accessible'  => $ratings['rating_accessible'],
		'_survey_rating_confidence'  => $ratings['rating_confidence'],
		'_survey_rating_age_range'   => $ratings['rating_age_range'],
		'_survey_highlight'      => $highlight,
		'_survey_improve'        => $improve,
		'_survey_themes_2027'    => wp_json_encode( $themes ),
		'_survey_suggestions'    => $suggestions,
		'_survey_return_2027'    => $return_2027,
		'_survey_contact_name'   => $contact_name,
		'_survey_contact_email'  => $contact_email,
		'_survey_permission_quote' => $perm_quote,
		'_survey_year'           => '2026',
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
		'_survey_role'               => __( 'Role', 'ai-awareness-day' ),
		'_survey_school_name'        => __( 'School / Organisation', 'ai-awareness-day' ),
		'_survey_region'             => __( 'Region', 'ai-awareness-day' ),
		'_survey_heard_via'          => __( 'Heard via', 'ai-awareness-day' ),
		'_survey_activities'         => __( 'Activities used', 'ai-awareness-day' ),
		'_survey_participants'       => __( 'Participants', 'ai-awareness-day' ),
		'_survey_rating_overall'     => __( 'Rating: overall', 'ai-awareness-day' ),
		'_survey_rating_resources'   => __( 'Rating: resources', 'ai-awareness-day' ),
		'_survey_rating_accessible'  => __( 'Rating: accessibility', 'ai-awareness-day' ),
		'_survey_rating_confidence'  => __( 'Rating: confidence', 'ai-awareness-day' ),
		'_survey_rating_age_range'   => __( 'Rating: age range', 'ai-awareness-day' ),
		'_survey_highlight'          => __( 'Most valuable part', 'ai-awareness-day' ),
		'_survey_improve'            => __( 'Could be better', 'ai-awareness-day' ),
		'_survey_themes_2027'        => __( 'Themes for 2027', 'ai-awareness-day' ),
		'_survey_suggestions'        => __( 'Suggestions for 2027', 'ai-awareness-day' ),
		'_survey_return_2027'        => __( 'Would return in 2027', 'ai-awareness-day' ),
		'_survey_contact_name'       => __( 'Contact name', 'ai-awareness-day' ),
		'_survey_contact_email'      => __( 'Contact email', 'ai-awareness-day' ),
		'_survey_permission_quote'   => __( 'Quoting permission', 'ai-awareness-day' ),
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
		'ID', 'Date', 'Role', 'School / Organisation', 'Region', 'Heard via',
		'Activities used', 'Participants', 'Rating: overall', 'Rating: resources',
		'Rating: accessibility', 'Rating: confidence', 'Rating: age range',
		'Most valuable part', 'Could be better', 'Themes for 2027',
		'Suggestions for 2027', 'Would return 2027', 'Contact name',
		'Contact email', 'Quoting permission',
	);

	$meta_keys = array(
		'_survey_role', '_survey_school_name', '_survey_region', '_survey_heard_via',
		'_survey_activities', '_survey_participants',
		'_survey_rating_overall', '_survey_rating_resources',
		'_survey_rating_accessible', '_survey_rating_confidence', '_survey_rating_age_range',
		'_survey_highlight', '_survey_improve', '_survey_themes_2027',
		'_survey_suggestions', '_survey_return_2027',
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
