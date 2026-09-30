<?php
/**
 * Timed AI speed quiz for teachers — shortcode, leaderboard (local), timeline seed.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical slug for the speed quiz timeline entry.
 */
function aiad_speed_quiz_post_slug(): string {
	return 'ai-speed-quiz-for-teachers';
}

/**
 * Whether post content includes the speed quiz shortcode.
 */
function aiad_post_content_has_speed_quiz_shortcode( WP_Post $post ): bool {
	if ( has_shortcode( $post->post_content, 'aiad_speed_quiz' ) ) {
		return true;
	}
	return false !== strpos( $post->post_content, '[aiad_speed_quiz' );
}

/**
 * Whether the current singular view should load speed quiz assets.
 */
function aiad_content_has_speed_quiz_shortcode(): bool {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	return aiad_post_content_has_speed_quiz_shortcode( $post );
}

/**
 * Register speed quiz CSS/JS.
 */
function aiad_register_speed_quiz_assets(): void {
	$css_path = AIAD_DIR . '/assets/css/components/ai-speed-quiz.css';
	$js_path  = AIAD_DIR . '/assets/js/ai-speed-quiz.js';

	wp_register_style(
		'aiad-speed-quiz',
		AIAD_URI . '/assets/css/components/ai-speed-quiz.css',
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : AIAD_VERSION
	);

	wp_register_script(
		'aiad-speed-quiz',
		AIAD_URI . '/assets/js/ai-speed-quiz.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : AIAD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'aiad_register_speed_quiz_assets', 5 );

/**
 * Enqueue speed quiz assets.
 */
function aiad_enqueue_speed_quiz_assets(): void {
	static $enqueued = false;
	if ( $enqueued ) {
		return;
	}
	$enqueued = true;
	wp_enqueue_style( 'aiad-speed-quiz' );
	wp_enqueue_script( 'aiad-speed-quiz' );
}

/**
 * Shortcode: [aiad_speed_quiz]
 *
 * @param array<string, string>|string $atts Attributes.
 */
function aiad_speed_quiz_shortcode( $atts = array() ): string {
	$GLOBALS['aiad_speed_quiz_shortcode_rendered'] = true;

	$atts = shortcode_atts(
		array(
			'questions' => '10',
			'seconds'   => '15',
			'bonus'     => '10',
			'points'    => '100',
		),
		is_array( $atts ) ? $atts : array(),
		'aiad_speed_quiz'
	);

	aiad_enqueue_speed_quiz_assets();

	$host = wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'aiawarenessday.com';

	ob_start();
	?>
	<div
		class="aiad-speed-quiz"
		data-aiad-speed-quiz
		data-questions="<?php echo esc_attr( $atts['questions'] ); ?>"
		data-seconds="<?php echo esc_attr( $atts['seconds'] ); ?>"
		data-bonus="<?php echo esc_attr( $atts['bonus'] ); ?>"
		data-points="<?php echo esc_attr( $atts['points'] ); ?>"
		aria-label="<?php esc_attr_e( 'AI speed quiz for teachers', 'ai-awareness-day' ); ?>"
	>
		<div class="sq-card" data-sq-start>
			<p class="sq-badge"><?php esc_html_e( 'Timed challenge', 'ai-awareness-day' ); ?></p>
			<h2 class="sq-title"><?php esc_html_e( 'The AI speed quiz for teachers', 'ai-awareness-day' ); ?></h2>
			<p class="sq-lead">
				<?php
				printf(
					/* translators: 1: question count, 2: seconds per question */
					esc_html__( '%1$d questions. %2$d seconds each. How well do you really know AI? Your score, time and rank go on the leaderboard (saved on this device).', 'ai-awareness-day' ),
					(int) $atts['questions'],
					(int) $atts['seconds']
				);
				?>
			</p>
			<div class="sq-stats" aria-hidden="true">
				<div class="sq-stat">
					<span class="sq-stat__val"><?php echo esc_html( $atts['questions'] ); ?></span>
					<span class="sq-stat__lbl"><?php esc_html_e( 'questions', 'ai-awareness-day' ); ?></span>
				</div>
				<div class="sq-stat">
					<span class="sq-stat__val"><?php echo esc_html( $atts['seconds'] ); ?>s</span>
					<span class="sq-stat__lbl"><?php esc_html_e( 'per question', 'ai-awareness-day' ); ?></span>
				</div>
				<div class="sq-stat">
					<span class="sq-stat__val">+<?php echo esc_html( $atts['bonus'] ); ?></span>
					<span class="sq-stat__lbl"><?php esc_html_e( 'speed bonus', 'ai-awareness-day' ); ?></span>
				</div>
				<div class="sq-stat">
					<span class="sq-stat__val">🏆</span>
					<span class="sq-stat__lbl"><?php esc_html_e( 'leaderboard', 'ai-awareness-day' ); ?></span>
				</div>
			</div>
			<div class="sq-start-row">
				<label class="screen-reader-text" for="aiad-sq-name"><?php esc_html_e( 'Your name', 'ai-awareness-day' ); ?></label>
				<input
					type="text"
					id="aiad-sq-name"
					class="sq-input"
					data-sq-name
					maxlength="40"
					autocomplete="nickname"
					placeholder="<?php esc_attr_e( 'Your name (for the leaderboard)', 'ai-awareness-day' ); ?>"
				/>
				<button type="button" class="sq-btn sq-btn--primary" data-sq-start-btn>
					<?php esc_html_e( 'Start', 'ai-awareness-day' ); ?>
				</button>
			</div>
		</div>

		<div class="sq-card" data-sq-play hidden>
			<div class="sq-play-top">
				<span class="sq-meta"><?php esc_html_e( 'Score', 'ai-awareness-day' ); ?>: <strong data-sq-score-live>0</strong></span>
				<span class="sq-meta"><?php esc_html_e( 'Question', 'ai-awareness-day' ); ?>: <strong data-sq-q-live>1/10</strong></span>
			</div>
			<div class="sq-timer-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100">
				<div class="sq-timer-fill" data-sq-timer-fill style="width:100%"></div>
			</div>
			<p class="sq-q-num" data-sq-q-num></p>
			<p class="sq-question" data-sq-question></p>
			<div class="sq-options" data-sq-options></div>
			<p class="sq-flash" data-sq-flash aria-live="polite"></p>
		</div>

		<div class="sq-card" data-sq-results hidden>
			<div data-sq-results-body></div>
			<div class="sq-results-actions">
				<button type="button" class="sq-btn sq-btn--primary" data-sq-retry><?php esc_html_e( 'Play again', 'ai-awareness-day' ); ?></button>
				<button type="button" class="sq-btn" data-sq-copy><?php esc_html_e( 'Copy result', 'ai-awareness-day' ); ?></button>
			</div>
			<div class="sq-leaderboard">
				<h4><?php esc_html_e( 'Leaderboard (this browser)', 'ai-awareness-day' ); ?></h4>
				<ul class="sq-lb-list" data-sq-lb-list></ul>
			</div>
		</div>

		<p class="sq-credit">
			<?php esc_html_e( 'Produced by', 'ai-awareness-day' ); ?>
			<strong><?php esc_html_e( 'AI Awareness Day', 'ai-awareness-day' ); ?></strong>
			&middot;
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( (string) $host ); ?></a>
		</p>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'aiad_speed_quiz', 'aiad_speed_quiz_shortcode' );

/**
 * Load assets in head when shortcode is in post content.
 */
function aiad_maybe_enqueue_speed_quiz_in_head(): void {
	if ( aiad_content_has_speed_quiz_shortcode() ) {
		aiad_enqueue_speed_quiz_assets();
	}
}
add_action( 'wp_enqueue_scripts', 'aiad_maybe_enqueue_speed_quiz_in_head', 20 );

/**
 * Footer fallback when shortcode renders late.
 */
function aiad_speed_quiz_footer_assets(): void {
	if ( ! empty( $GLOBALS['aiad_speed_quiz_shortcode_rendered'] ) ) {
		aiad_enqueue_speed_quiz_assets();
	}
}
add_action( 'wp_footer', 'aiad_speed_quiz_footer_assets', 1 );

/**
 * Gutenberg body for speed quiz timeline entry.
 */
function aiad_get_speed_quiz_timeline_content(): string {
	$llm_url = esc_url( home_url( '/timeline/' . aiad_llm_explainer_post_slug() . '/' ) );
	$buzz_url = esc_url( home_url( '/timeline/' . aiad_buzzwords_post_slug() . '/' ) );

	return '<!-- wp:paragraph -->
<p>How fast can you separate AI fact from fiction? This <strong>timed speed quiz</strong> gives you ten questions, fifteen seconds each, and a live score with speed bonuses. Beat your colleagues on the leaderboard — or just challenge yourself between lessons.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Pair it with our <a href="' . $llm_url . '">How LLMs work</a> explainer or the <a href="' . $buzz_url . '">15 buzzwords glossary</a> if you want a refresher before you start.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[aiad_speed_quiz]
<!-- /wp:shortcode -->

<!-- wp:paragraph -->
<p><strong>Staff-room idea:</strong> run it on the projector, one volunteer per question, or set a department challenge during a 10-minute CPD slot. Scores are stored in the browser on each device — perfect for friendly competition, not formal assessment.</p>
<!-- /wp:paragraph -->';
}

/**
 * Timeline meta for speed quiz entry.
 */
function aiad_set_speed_quiz_timeline_meta( int $post_id ): void {
	update_post_meta( $post_id, '_aiad_timeline_source', 'manual' );
	update_post_meta( $post_id, '_aiad_timeline_icon', 'announcement' );
	update_post_meta( $post_id, '_aiad_timeline_auto_type', '' );
	update_post_meta( $post_id, '_aiad_timeline_related_id', 0 );
}

/**
 * Create speed quiz timeline entry.
 *
 * @return int Post ID or 0.
 */
function aiad_create_speed_quiz_timeline_entry(): int {
	$slug  = aiad_speed_quiz_post_slug();
	$title = __( 'The AI Speed Quiz for Teachers', 'ai-awareness-day' );

	$existing = get_page_by_path( $slug, OBJECT, 'timeline' );
	if ( $existing instanceof WP_Post ) {
		return (int) $existing->ID;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'timeline',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_excerpt' => __( '10 AI questions, 15 seconds each — score points, earn speed bonuses, and climb the leaderboard.', 'ai-awareness-day' ),
			'post_content' => aiad_get_speed_quiz_timeline_content(),
			'post_status'  => 'draft',
			'post_author'  => 1,
		),
		true
	);

	if ( ! $post_id || is_wp_error( $post_id ) ) {
		return 0;
	}

	aiad_set_speed_quiz_timeline_meta( (int) $post_id );
	return (int) $post_id;
}

/**
 * One-time seed.
 */
function aiad_seed_speed_quiz_timeline_entry(): void {
	if ( get_option( 'aiad_speed_quiz_timeline_seeded' ) === 'yes' ) {
		return;
	}

	$slug = aiad_speed_quiz_post_slug();

	if ( get_page_by_path( $slug, OBJECT, 'timeline' ) ) {
		update_option( 'aiad_speed_quiz_timeline_seeded', 'yes' );
		return;
	}

	if ( aiad_create_speed_quiz_timeline_entry() ) {
		update_option( 'aiad_speed_quiz_timeline_seeded', 'yes' );
	}
}
add_action( 'init', 'aiad_seed_speed_quiz_timeline_entry', 32 );
