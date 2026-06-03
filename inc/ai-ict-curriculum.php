<?php
/**
 * KS2–KS3 ICT & Computing Curriculum Experience for teachers.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether post content includes the shortcode.
 */
function aiad_post_content_has_ict_curriculum_shortcode( WP_Post $post ): bool {
	if ( has_shortcode( $post->post_content, 'aiad_ict_curriculum' ) ) {
		return true;
	}
	return false !== strpos( $post->post_content, '[aiad_ict_curriculum' );
}

/**
 * Whether the current singular view should load assets.
 */
function aiad_content_has_ict_curriculum_shortcode(): bool {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	return aiad_post_content_has_ict_curriculum_shortcode( $post );
}

/**
 * Register CPT assets.
 */
function aiad_register_ict_curriculum_assets(): void {
	$css_path = AIAD_DIR . '/assets/css/components/ai-ict-curriculum.css';
	$js_path  = AIAD_DIR . '/assets/js/ai-ict-curriculum.js';

	wp_register_style(
		'aiad-ict-curriculum',
		AIAD_URI . '/assets/css/components/ai-ict-curriculum.css',
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : AIAD_VERSION
	);

	wp_register_script(
		'aiad-ict-curriculum',
		AIAD_URI . '/assets/js/ai-ict-curriculum.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : AIAD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'aiad_register_ict_curriculum_assets', 5 );

/**
 * Enqueue assets when shortcode is rendered.
 */
function aiad_enqueue_ict_curriculum_assets(): void {
	static $enqueued = false;
	if ( $enqueued ) {
		return;
	}
	$enqueued = true;
	wp_enqueue_style( 'aiad-ict-curriculum' );
	wp_enqueue_script( 'aiad-ict-curriculum' );
}

/**
 * Shortcode: [aiad_ict_curriculum]
 */
function aiad_ict_curriculum_shortcode(): string {
	$GLOBALS['aiad_ict_curriculum_shortcode_rendered'] = true;
	aiad_enqueue_ict_curriculum_assets();

	$host = wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'aiawarenessday.com';

	ob_start();
	?>
	<div
		class="aiad-ict-curriculum-widget"
		data-aiad-ict-curriculum
		aria-label="<?php esc_attr_e( 'Interactive KS2 and KS3 Computing and ICT assessment playground', 'ai-awareness-day' ); ?>"
	>
		<!-- Widget Hub Header -->
		<div class="ict-widget__header">
			<div class="ict-widget__badge"><?php esc_html_e( 'Interactive CPD Tool', 'ai-awareness-day' ); ?></div>
			<h2 class="ict-widget__title"><?php esc_html_e( 'Experience the National Curriculum', 'ai-awareness-day' ); ?></h2>
			<p class="ict-widget__lead">
				<?php esc_html_e( 'Select a Key Stage below to experience the coding, logic, and data puzzles pupils face, and explore the educational concepts behind them.', 'ai-awareness-day' ); ?>
			</p>
			
			<div class="ict-widget__tabs" role="tablist">
				<button class="ict-widget__tab-btn is-active" id="tab-ks2" role="tab" aria-selected="true" aria-controls="panel-ks2" type="button">
					<span class="tab-label-main"><?php esc_html_e( 'Key Stage 2', 'ai-awareness-day' ); ?></span>
					<span class="tab-label-sub"><?php esc_html_e( 'Block-based logic (Ages 7-11)', 'ai-awareness-day' ); ?></span>
				</button>
				<button class="ict-widget__tab-btn" id="tab-ks3" role="tab" aria-selected="false" aria-controls="panel-ks3" type="button" tabindex="-1">
					<span class="tab-label-main"><?php esc_html_e( 'Key Stage 3', 'ai-awareness-day' ); ?></span>
					<span class="tab-label-sub"><?php esc_html_e( 'Data & Text-based coding (Ages 11-14)', 'ai-awareness-day' ); ?></span>
				</button>
			</div>
		</div>

		<!-- Panel: Key Stage 2 -->
		<div class="ict-widget__panel is-active" id="panel-ks2" role="tabpanel" aria-labelledby="tab-ks2">
			<div class="ict-widget__panel-intro">
				<h3><?php esc_html_e( 'Key Stage 2: Sequencing, Selection & Iteration', 'ai-awareness-day' ); ?></h3>
				<p>
					<?php esc_html_e( 'Primary computing focuses on logical reasoning, algorithms, and block-based coding (usually Scratch). A crucial milestone is moving from single commands (sequencing) to repeating instructions (iteration/loops) to draw shapes or control objects.', 'ai-awareness-day' ); ?>
				</p>
			</div>

			<!-- Scratch Logic Playground -->
			<div class="scratch-playground" data-scratch-playground>
				<div class="playground-layout">
					<!-- Visual Code Editor -->
					<div class="scratch-editor">
						<div class="editor-header">
							<span class="dot-btn red"></span>
							<span class="dot-btn yellow"></span>
							<span class="dot-btn green"></span>
							<span class="editor-title"><?php esc_html_e( 'Workspace — Scratch Logic', 'ai-awareness-day' ); ?></span>
						</div>
						
						<div class="scratch-blocks-list">
							<!-- Block: When Clicked -->
							<div class="scratch-block scratch-block--event">
								<span class="block-flag-icon">🏁</span> <?php esc_html_e( 'when green flag clicked', 'ai-awareness-day' ); ?>
							</div>

							<!-- Block: Pen down -->
							<div class="scratch-block scratch-block--pen">
								🖋️ <?php esc_html_e( 'pen down', 'ai-awareness-day' ); ?>
							</div>

							<!-- Block: Repeat Loop -->
							<div class="scratch-block scratch-block--control block-has-children">
								<div class="block-top-row">
									🔄 <?php esc_html_e( 'repeat', 'ai-awareness-day' ); ?>
									<select class="scratch-select select-repeat" data-scratch-input="repeat" aria-label="<?php esc_attr_e( 'Loop repeat count', 'ai-awareness-day' ); ?>">
										<option value="3">3</option>
										<option value="4" selected>4</option>
										<option value="5">5</option>
										<option value="8">8</option>
									</select>
									<?php esc_html_e( 'times', 'ai-awareness-day' ); ?>
								</div>
								
								<div class="block-nested-contents">
									<div class="scratch-block scratch-block--motion">
										➡️ <?php esc_html_e( 'move', 'ai-awareness-day' ); ?>
										<span class="block-input-value">100</span>
										<?php esc_html_e( 'steps', 'ai-awareness-day' ); ?>
									</div>
									<div class="scratch-block scratch-block--motion">
										↪️ <?php esc_html_e( 'turn right', 'ai-awareness-day' ); ?>
										<select class="scratch-select select-turn" data-scratch-input="turn" aria-label="<?php esc_attr_e( 'Degrees of turn', 'ai-awareness-day' ); ?>">
											<option value="45">45°</option>
											<option value="90">90°</option>
											<option value="120">120°</option>
											<option value="180">180°</option>
											<option value="360">360°</option>
										</select>
									</div>
								</div>
								
								<div class="block-bottom-row"></div>
							</div>

							<!-- Block: Pen up -->
							<div class="scratch-block scratch-block--pen">
								🖋️ <?php esc_html_e( 'pen up', 'ai-awareness-day' ); ?>
							</div>
						</div>

						<div class="editor-actions">
							<button class="playground-btn playground-btn--run" data-action="run-scratch" type="button">
								<span class="btn-icon">▶</span> <?php esc_html_e( 'Run Code', 'ai-awareness-day' ); ?>
							</button>
							<button class="playground-btn playground-btn--reset" data-action="reset-scratch" type="button">
								<?php esc_html_e( 'Reset Grid', 'ai-awareness-day' ); ?>
							</button>
						</div>
					</div>

					<!-- Grid Simulation Panel -->
					<div class="grid-simulator">
						<div class="simulator-header">
							<span class="sim-label"><?php esc_html_e( 'Stage Preview', 'ai-awareness-day' ); ?></span>
							<span class="sim-coordinates" data-scratch-coords>x: 0, y: 0</span>
						</div>
						<div class="grid-canvas-wrap">
							<div class="grid-lines-bg"></div>
							<!-- Spaceship Sprite -->
							<div class="grid-sprite" data-scratch-sprite>🚀</div>
							<!-- Space Gem Target -->
							<div class="grid-gem" data-scratch-gem>💎</div>
							<!-- Draw Trace Path SVG -->
							<svg class="grid-svg-trace" data-scratch-svg></svg>
						</div>
						<div class="simulator-instruction">
							<span class="instruction-icon">💡</span>
							<p><?php esc_html_e( 'Challenge: Set the repeat loops and turn degrees to guide the rocket on a perfect square path and collect the space gem!', 'ai-awareness-day' ); ?></p>
						</div>
					</div>
				</div>

				<!-- Educational Feedback Sheet -->
				<div class="ict-feedback-card hidden" data-scratch-feedback>
					<div class="feedback-status success">
						<span class="status-icon">🏆</span>
						<div>
							<h4><?php esc_html_e( 'Success! You completed the KS2 coding challenge.', 'ai-awareness-day' ); ?></h4>
							<p><?php esc_html_e( 'The rocket successfully traced a perfect 4-sided square (repeat 4 times, turning 90 degrees each time).', 'ai-awareness-day' ); ?></p>
						</div>
					</div>
					<div class="feedback-status error hidden">
						<span class="status-icon">⚠️</span>
						<div>
							<h4><?php esc_html_e( 'Incorrect Path', 'ai-awareness-day' ); ?></h4>
							<p class="error-msg-text"><?php esc_html_e( 'The rocket did not make a square. Review your instructions and try again!', 'ai-awareness-day' ); ?></p>
						</div>
					</div>

					<div class="feedback-curriculum-tabs">
						<button class="feedback-tab-btn is-active" data-feedback-tab="insight" type="button"><?php esc_html_e( 'Curriculum Insight', 'ai-awareness-day' ); ?></button>
						<button class="feedback-tab-btn" data-feedback-tab="classroom" type="button"><?php esc_html_e( 'Classroom Pedagogy', 'ai-awareness-day' ); ?></button>
					</div>

					<div class="feedback-tab-content" data-feedback-panel="insight">
						<p><strong><?php esc_html_e( 'The Mathematics of Repeating Paths:', 'ai-awareness-day' ); ?></strong></p>
						<p>
							<?php esc_html_e( 'At KS2, pupils are taught to think computationally using simple loops. A very common misconception pupils make is that to draw a shape, you rotate by the interior angle rather than the exterior turn. For example, to draw an equilateral triangle, pupils often choose a 60° turn. However, the computer turns the sprite *exteriorly*, meaning they must turn 120° (calculated by dividing 360° by the 3 sides).', 'ai-awareness-day' ); ?>
						</p>
						<blockquote>
							<strong><?php esc_html_e( 'Curriculum Link:', 'ai-awareness-day' ); ?></strong>
							<?php esc_html_e( 'UK National Curriculum Computing KS2 states that pupils must: "design, write and debug programs that accomplish specific goals, including controlling or simulating physical systems; solve problems by decomposing them into smaller parts."', 'ai-awareness-day' ); ?>
						</blockquote>
					</div>

					<div class="feedback-tab-content hidden" data-feedback-panel="classroom">
						<p><strong><?php esc_html_e( 'Debugging in the Classroom:', 'ai-awareness-day' ); ?></strong></p>
						<p>
							<?php esc_html_e( 'When students encounter logic bugs (e.g. their shape is open or asymmetrical), teachers are trained to use "unplugged" roleplay. Asking students to stand up, act as the sprite, take 10 steps, and turn is a powerful way to bridge the abstract logic of code with spatial reasoning. This aligns with the NCCE (National Centre for Computing Education) model of Semantic Waves — grounding abstract code in concrete experiences.', 'ai-awareness-day' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>

		<!-- Panel: Key Stage 3 -->
		<div class="ict-widget__panel" id="panel-ks3" role="tabpanel" aria-labelledby="tab-ks3" hidden>
			<div class="ict-widget__panel-intro">
				<h3><?php esc_html_e( 'Key Stage 3: Data Representation & Text-Based Code', 'ai-awareness-day' ); ?></h3>
				<p>
					<?php esc_html_e( 'In secondary school, the curriculum makes two massive leaps: understanding how digital computers store and represent data (binary representation), and transitioning from block-based visuals to text-based coding (primarily Python).', 'ai-awareness-day' ); ?>
				</p>
			</div>

			<div class="ks3-playground" data-ks3-playground>
				<!-- Part A: Binary Register Lock -->
				<div class="binary-lock-section" data-binary-lock>
					<div class="binary-lock__header">
						<div class="lock-icon">🔒</div>
						<div>
							<h4><?php esc_html_e( 'Part A: The Binary Register Decoder', 'ai-awareness-day' ); ?></h4>
							<p><?php esc_html_e( 'Before accessing the Python engine, you must unlock the terminal by setting the 4-bit binary register to represent the decimal number 11.', 'ai-awareness-day' ); ?></p>
						</div>
					</div>

					<div class="binary-register">
						<!-- Bit 8 -->
						<div class="bit-card">
							<span class="bit-value-label"><?php esc_html_e( 'Place Value: 8', 'ai-awareness-day' ); ?></span>
							<button class="bit-toggle-btn" data-bit-index="3" data-bit-value="8" type="button">0</button>
							<span class="bit-status-label"><?php esc_html_e( 'OFF', 'ai-awareness-day' ); ?></span>
						</div>
						<!-- Bit 4 -->
						<div class="bit-card">
							<span class="bit-value-label"><?php esc_html_e( 'Place Value: 4', 'ai-awareness-day' ); ?></span>
							<button class="bit-toggle-btn" data-bit-index="2" data-bit-value="4" type="button">0</button>
							<span class="bit-status-label"><?php esc_html_e( 'OFF', 'ai-awareness-day' ); ?></span>
						</div>
						<!-- Bit 2 -->
						<div class="bit-card">
							<span class="bit-value-label"><?php esc_html_e( 'Place Value: 2', 'ai-awareness-day' ); ?></span>
							<button class="bit-toggle-btn" data-bit-index="1" data-bit-value="2" type="button">0</button>
							<span class="bit-status-label"><?php esc_html_e( 'OFF', 'ai-awareness-day' ); ?></span>
						</div>
						<!-- Bit 1 -->
						<div class="bit-card">
							<span class="bit-value-label"><?php esc_html_e( 'Place Value: 1', 'ai-awareness-day' ); ?></span>
							<button class="bit-toggle-btn" data-bit-index="0" data-bit-value="1" type="button">0</button>
							<span class="bit-status-label"><?php esc_html_e( 'OFF', 'ai-awareness-day' ); ?></span>
						</div>
					</div>

					<div class="binary-display-bar">
						<div class="bar-formula">
							<span class="formula-term" id="term-8">0 × 8</span> + 
							<span class="formula-term" id="term-4">0 × 4</span> + 
							<span class="formula-term" id="term-2">0 × 2</span> + 
							<span class="formula-term" id="term-1">0 × 1</span>
						</div>
						<div class="bar-total">
							<?php esc_html_e( 'Current Total:', 'ai-awareness-day' ); ?> <span class="total-number" data-binary-total>0</span>
						</div>
					</div>
					
					<div class="binary-feedback hidden" data-binary-feedback></div>
				</div>

				<!-- Part B: Python Mock Terminal (Locked by default) -->
				<div class="python-section is-locked" data-python-section>
					<div class="python-lock-overlay" data-python-overlay>
						<span class="overlay-lock-icon">🔒</span>
						<p><?php esc_html_e( 'Solve Part A to unlock the Python logic engine.', 'ai-awareness-day' ); ?></p>
					</div>

					<div class="python-terminal-wrap">
						<div class="terminal-header">
							<div class="terminal-dots">
								<span class="t-dot red"></span>
								<span class="t-dot yellow"></span>
								<span class="t-dot green"></span>
							</div>
							<span class="terminal-title">python_motor_controller.py</span>
						</div>
						
						<!-- Python Code -->
						<div class="terminal-editor">
							<pre class="python-code-pre"><code><span class="py-line" data-line="1"><span class="py-comment"># Variable initialization</span></span>
<span class="py-line" data-line="2"><span class="py-var">temperature</span> = <span class="py-val">22</span></span>
<span class="py-line" data-line="3"><span class="py-var">is_raining</span> = <span class="py-val">True</span></span>
<span class="py-line" data-line="4"><span class="py-var">motor_speed</span> = <span class="py-val">0</span></span>
<span class="py-line" data-line="5"></span>
<span class="py-line" data-line="6"><span class="py-keyword">if</span> <span class="py-var">temperature</span> &gt; <span class="py-val">20</span> <span class="py-keyword">and not</span> <span class="py-var">is_raining</span>:</span>
<span class="py-line" data-line="7">    <span class="py-var">motor_speed</span> = <span class="py-val">100</span></span>
<span class="py-line" data-line="8"><span class="py-keyword">elif</span> <span class="py-var">temperature</span> &gt; <span class="py-val">20</span> <span class="py-keyword">and</span> <span class="py-var">is_raining</span>:</span>
<span class="py-line" data-line="9">    <span class="py-var">motor_speed</span> = <span class="py-val">50</span></span>
<span class="py-line" data-line="10"><span class="py-keyword">else</span>:</span>
<span class="py-line" data-line="11">    <span class="py-var">motor_speed</span> = <span class="py-val">10</span></span>
<span class="py-line" data-line="12"></span>
<span class="py-line" data-line="13"><span class="py-keyword">print</span>(<span class="py-var">motor_speed</span>)</span></code></pre>
						</div>

						<div class="terminal-output-bar" data-terminal-output>
							<span class="prompt">>>></span> <span class="output-text blink-cursor"><?php esc_html_e( 'Awaiting trace answer...', 'ai-awareness-day' ); ?></span>
						</div>
					</div>

					<!-- Multiple Choice Panel -->
					<div class="python-question-panel">
						<h4 class="question-title">💡 <?php esc_html_e( 'Trace the code logic. What will the terminal output be when this Python script is executed?', 'ai-awareness-day' ); ?></h4>
						<div class="python-options">
							<button class="python-opt-btn" data-opt-value="0" type="button">0</button>
							<button class="python-opt-btn" data-opt-value="10" type="button">10</button>
							<button class="python-opt-btn" data-opt-value="50" type="button">50</button>
							<button class="python-opt-btn" data-opt-value="100" type="button">100</button>
						</div>
					</div>
				</div>

				<!-- Educational Feedback Sheet -->
				<div class="ict-feedback-card hidden" data-ks3-feedback>
					<div class="feedback-status success">
						<span class="status-icon">🏆</span>
						<div>
							<h4><?php esc_html_e( 'Excellent! You fully decoded the KS3 experience.', 'ai-awareness-day' ); ?></h4>
							<p><?php esc_html_e( 'Binary 1011 matches decimal 11. And the Python tracing correctly evaluates to 50.', 'ai-awareness-day' ); ?></p>
						</div>
					</div>
					<div class="feedback-status error hidden">
						<span class="status-icon">⚠️</span>
						<div>
							<h4><?php esc_html_e( 'Trace Incorrect', 'ai-awareness-day' ); ?></h4>
							<p class="ks3-error-msg-text"><?php esc_html_e( 'That is not the correct output. Carefully trace line by line!', 'ai-awareness-day' ); ?></p>
						</div>
					</div>

					<div class="feedback-curriculum-tabs">
						<button class="feedback-tab-btn is-active" data-feedback-tab="ks3-insight" type="button"><?php esc_html_e( 'Curriculum Insight', 'ai-awareness-day' ); ?></button>
						<button class="feedback-tab-btn" data-feedback-tab="ks3-classroom" type="button"><?php esc_html_e( 'Classroom Pedagogy', 'ai-awareness-day' ); ?></button>
					</div>

					<div class="feedback-tab-content" data-feedback-panel="ks3-insight">
						<p><strong><?php esc_html_e( 'Transitioning from blocks to text-based code:', 'ai-awareness-day' ); ?></strong></p>
						<p>
							<?php esc_html_e( 'At KS3, binary registers demonstrate how numeric data, text, and imagery are represented inside physical silicon gates using bits (Binary Digits). Transitioning to Python adds syntax rules, where boolean operations (such as `and` or `not`) require logical precision. In the code above, the first `if` statement evaluates to `False` because `not is_raining` is `False`. The logic then cascades to the `elif` branch, which evaluates to `True`, assigning `50` to `motor_speed`.', 'ai-awareness-day' ); ?>
						</p>
						<blockquote>
							<strong><?php esc_html_e( 'Curriculum Link:', 'ai-awareness-day' ); ?></strong>
							<?php esc_html_e( 'UK National Curriculum Computing KS3 states that pupils must: "use two or more programming languages, at least one of which is text-based, to solve a variety of computational problems... understand how data of various types can be represented and manipulated digitally, in the form of binary digits."', 'ai-awareness-day' ); ?>
						</blockquote>
					</div>

					<div class="feedback-tab-content hidden" data-feedback-panel="ks3-classroom">
						<p><strong><?php esc_html_e( 'Teaching Trace Tables & Code Comprehension:', 'ai-awareness-day' ); ?></strong></p>
						<p>
							<?php esc_html_e( 'In secondary classrooms, teachers use "trace tables" to help students track variable states line by line. This is a foundational technique to combat the common mistake of students reading code like a book (skimming) rather than executing it sequentially like a machine. Tracing variables encourages computational literacy and ensures pupils understand how programs store states before they write long programs of their own.', 'ai-awareness-day' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>

		<!-- Widget Footer Credits -->
		<p class="ict-widget__credit">
			<?php esc_html_e( 'Produced by', 'ai-awareness-day' ); ?>
			<strong><?php esc_html_e( 'AI Awareness Day', 'ai-awareness-day' ); ?></strong>
			&middot;
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( (string) $host ); ?></a>
		</p>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'aiad_ict_curriculum', 'aiad_ict_curriculum_shortcode' );

/**
 * Load assets in head when shortcode is in post content.
 */
function aiad_maybe_enqueue_ict_curriculum_in_head(): void {
	if ( aiad_content_has_ict_curriculum_shortcode() ) {
		aiad_enqueue_ict_curriculum_assets();
	}
}
add_action( 'wp_enqueue_scripts', 'aiad_maybe_enqueue_ict_curriculum_in_head', 20 );

/**
 * Footer fallback when shortcode renders late.
 */
function aiad_ict_curriculum_footer_assets(): void {
	if ( ! empty( $GLOBALS['aiad_ict_curriculum_shortcode_rendered'] ) ) {
		aiad_enqueue_ict_curriculum_assets();
	}
}
add_action( 'wp_footer', 'aiad_ict_curriculum_footer_assets', 1 );
