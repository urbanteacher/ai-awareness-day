<?php
/**
 * Schools AI Risk Academy markup — loaded by [aiad_risk_academy].
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$show = isset( $aiad_risk_academy_show ) && is_array( $aiad_risk_academy_show )
	? $aiad_risk_academy_show
	: array(
		'hero'         => true,
		'methodology'  => true,
		'meter'        => true,
		'curriculum'   => true,
		'contributors' => true,
		'resources'    => true,
		'sources'      => true,
		'enrol'        => true,
	);
?>
<div class="sara" data-aiad-risk-academy aria-label="<?php esc_attr_e( 'Schools AI Risk Academy', 'aiad-core' ); ?>">

	<header class="sara-header">
		<div class="sara-wrap sara-header__inner">
			<a class="sara-logo" href="#sara-top"><?php esc_html_e( 'Schools AI Risk Academy', 'aiad-core' ); ?></a>
			<button type="button" class="sara-nav-toggle" data-sara-nav-toggle aria-expanded="false" aria-controls="sara-nav"><?php esc_html_e( 'Menu', 'aiad-core' ); ?></button>
			<ul class="sara-nav-links" id="sara-nav" data-sara-nav-links>
				<?php if ( ! empty( $show['methodology'] ) ) : ?><li><a href="#sara-method"><?php esc_html_e( 'How it works', 'aiad-core' ); ?></a></li><?php endif; ?>
				<?php if ( ! empty( $show['meter'] ) ) : ?><li><a href="#sara-meter"><?php esc_html_e( 'Risk Meter', 'aiad-core' ); ?></a></li><?php endif; ?>
				<?php if ( ! empty( $show['curriculum'] ) ) : ?><li><a href="#sara-curriculum"><?php esc_html_e( 'Curriculum', 'aiad-core' ); ?></a></li><?php endif; ?>
				<?php if ( ! empty( $show['sources'] ) ) : ?><li><a href="#sara-sources"><?php esc_html_e( 'Sources', 'aiad-core' ); ?></a></li><?php endif; ?>
			</ul>
		</div>
	</header>

	<?php if ( ! empty( $show['hero'] ) ) : ?>
	<section class="sara-hero" id="sara-top" data-sara-reveal>
		<div class="sara-wrap">
			<p class="sara-eyebrow"><?php esc_html_e( 'Free · England · DfE-aligned', 'aiad-core' ); ?></p>
			<h1><?php esc_html_e( 'Govern AI safely — and measure your school\'s exposure', 'aiad-core' ); ?></h1>
			<p class="sara-lead"><?php esc_html_e( 'Before adopting AI, a school should be satisfied that the benefits clearly outweigh the risks. This mini-app scores the situation around AI use — not the tool itself — with an interactive Risk Meter and six teachable modules mapped to UK guidance.', 'aiad-core' ); ?></p>
			<div class="sara-badges">
				<span class="sara-badge"><?php esc_html_e( 'England / DfE', 'aiad-core' ); ?></span>
				<span class="sara-badge"><?php esc_html_e( 'KCSIE · ICO · Ofsted · JCQ', 'aiad-core' ); ?></span>
				<span class="sara-badge"><?php esc_html_e( 'Free', 'aiad-core' ); ?></span>
				<span class="sara-badge"><?php esc_html_e( 'Nothing stored', 'aiad-core' ); ?></span>
			</div>
			<div class="sara-hero__actions">
				<?php if ( ! empty( $show['meter'] ) ) : ?>
					<a class="sara-btn sara-btn--primary" href="#sara-meter"><?php esc_html_e( 'Open Risk Meter', 'aiad-core' ); ?></a>
				<?php endif; ?>
				<?php if ( ! empty( $show['curriculum'] ) ) : ?>
					<a class="sara-btn sara-btn--ghost" href="#sara-curriculum"><?php esc_html_e( 'Browse curriculum', 'aiad-core' ); ?></a>
				<?php endif; ?>
			</div>
			<div class="sara-chips">
				<a class="sara-chip" href="#sara-sources"><?php esc_html_e( 'DfE Generative AI', 'aiad-core' ); ?></a>
				<a class="sara-chip" href="#sara-sources"><?php esc_html_e( 'KCSIE', 'aiad-core' ); ?></a>
				<a class="sara-chip" href="#sara-sources"><?php esc_html_e( 'ICO / UK GDPR', 'aiad-core' ); ?></a>
				<a class="sara-chip" href="#sara-sources"><?php esc_html_e( 'JCQ assessments', 'aiad-core' ); ?></a>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( ! empty( $show['methodology'] ) ) : ?>
	<section class="sara-section" id="sara-method" data-sara-reveal>
		<div class="sara-wrap">
			<h2><?php esc_html_e( 'How it works', 'aiad-core' ); ?></h2>
			<p class="sara-muted"><?php esc_html_e( 'Risk is a property of a task or habit — how much unmanaged AI use a situation allows, and how much is at stake when that use happens.', 'aiad-core' ); ?></p>

			<div class="sara-grid-2">
				<div class="sara-panel">
					<h3><?php esc_html_e( 'Activity exposure', 'aiad-core' ); ?></h3>
					<p class="sara-muted"><?php esc_html_e( 'For staff: how much room a task\'s design leaves for unmanaged AI use (supervision, device access, stakes, intent).', 'aiad-core' ); ?></p>
				</div>
				<div class="sara-panel">
					<h3><?php esc_html_e( 'Student reliance', 'aiad-core' ); ?></h3>
					<p class="sara-muted"><?php esc_html_e( 'For students: how much of their own thinking they hand to AI — supportive self-check, not punitive monitoring.', 'aiad-core' ); ?></p>
				</div>
			</div>

			<h3><?php esc_html_e( 'Four scoring factors (activity exposure)', 'aiad-core' ); ?></h3>
			<p class="sara-muted"><?php esc_html_e( 'Supervision · Device & AI access · Stakes · Intent — weighted to a 0–100 score, then banded as Keep / Decide / Redesign.', 'aiad-core' ); ?></p>

			<h3><?php esc_html_e( 'The four human-factor traps', 'aiad-core' ); ?></h3>
			<ul class="sara-muted">
				<li><strong><?php esc_html_e( 'Over-reliance', 'aiad-core' ); ?></strong> — <?php esc_html_e( 'assuming the AI is right and stopping checks.', 'aiad-core' ); ?></li>
				<li><strong><?php esc_html_e( 'Time pressure', 'aiad-core' ); ?></strong> — <?php esc_html_e( 'accepting the first AI draft under workload.', 'aiad-core' ); ?></li>
				<li><strong><?php esc_html_e( 'The fluency trap', 'aiad-core' ); ?></strong> — <?php esc_html_e( 'polished answers that hide missing understanding.', 'aiad-core' ); ?></li>
				<li><strong><?php esc_html_e( 'Responsibility drift', 'aiad-core' ); ?></strong> — <?php esc_html_e( 'leaning on AI so the decision does not feel yours.', 'aiad-core' ); ?></li>
			</ul>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( ! empty( $show['meter'] ) ) : ?>
	<section class="sara-section" id="sara-meter" data-sara-reveal>
		<div class="sara-wrap">
			<h2><?php esc_html_e( 'AI Risk Meter', 'aiad-core' ); ?></h2>
			<p class="sara-muted"><?php esc_html_e( 'Four tabbed tools — all in your browser, nothing sent or stored.', 'aiad-core' ); ?></p>

			<div class="sara-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Risk Meter tools', 'aiad-core' ); ?>">
				<button type="button" class="sara-tab is-active" role="tab" data-sara-tab="exposure" aria-selected="true"><?php esc_html_e( 'Assess an activity', 'aiad-core' ); ?></button>
				<button type="button" class="sara-tab" role="tab" data-sara-tab="reliance" aria-selected="false"><?php esc_html_e( 'Reliance self-check', 'aiad-core' ); ?></button>
				<button type="button" class="sara-tab" role="tab" data-sara-tab="class" aria-selected="false"><?php esc_html_e( 'Class & school picture', 'aiad-core' ); ?></button>
				<button type="button" class="sara-tab" role="tab" data-sara-tab="verify" aria-selected="false"><?php esc_html_e( 'Verify before you trust', 'aiad-core' ); ?></button>
			</div>

			<!-- Activity exposure -->
			<div class="sara-tab-panel is-active" data-sara-panel="exposure" role="tabpanel">
				<form data-sara-exposure-form novalidate>
					<div class="sara-field">
						<span class="sara-label">1. <?php esc_html_e( 'Supervision', 'aiad-core' ); ?></span>
						<div class="sara-radio-group">
							<label class="sara-option"><input type="radio" name="supervision" value="0" /> <?php esc_html_e( 'In class, supervised', 'aiad-core' ); ?></label>
							<label class="sara-option"><input type="radio" name="supervision" value="1" /> <?php esc_html_e( 'Light supervision', 'aiad-core' ); ?></label>
							<label class="sara-option"><input type="radio" name="supervision" value="3" /> <?php esc_html_e( 'Home / unsupervised', 'aiad-core' ); ?></label>
						</div>
					</div>
					<div class="sara-field">
						<span class="sara-label">2. <?php esc_html_e( 'Device & AI access', 'aiad-core' ); ?></span>
						<div class="sara-radio-group">
							<label class="sara-option"><input type="radio" name="access" value="0" /> <?php esc_html_e( 'No devices / paper', 'aiad-core' ); ?></label>
							<label class="sara-option"><input type="radio" name="access" value="1" /> <?php esc_html_e( 'Devices, AI filtered', 'aiad-core' ); ?></label>
							<label class="sara-option"><input type="radio" name="access" value="3" /> <?php esc_html_e( 'Open AI access', 'aiad-core' ); ?></label>
						</div>
					</div>
					<div class="sara-field">
						<span class="sara-label">3. <?php esc_html_e( 'Stakes', 'aiad-core' ); ?></span>
						<div class="sara-radio-group">
							<label class="sara-option"><input type="radio" name="stakes" value="0" /> <?php esc_html_e( 'Formative, not graded', 'aiad-core' ); ?></label>
							<label class="sara-option"><input type="radio" name="stakes" value="1" /> <?php esc_html_e( 'Graded classwork', 'aiad-core' ); ?></label>
							<label class="sara-option"><input type="radio" name="stakes" value="3" /> <?php esc_html_e( 'Counts to a qualification', 'aiad-core' ); ?></label>
						</div>
					</div>
					<div class="sara-field">
						<span class="sara-label">4. <?php esc_html_e( 'Intent', 'aiad-core' ); ?></span>
						<div class="sara-radio-group">
							<label class="sara-option"><input type="radio" name="intent" value="0" /> <?php esc_html_e( 'AI not needed / not possible', 'aiad-core' ); ?></label>
							<label class="sara-option"><input type="radio" name="intent" value="1" /> <?php esc_html_e( 'Permitted, taught rules', 'aiad-core' ); ?></label>
							<label class="sara-option"><input type="radio" name="intent" value="2" /> <?php esc_html_e( 'No guidance given', 'aiad-core' ); ?></label>
						</div>
					</div>
					<button type="button" class="sara-btn sara-btn--primary" data-sara-exposure-score><?php esc_html_e( 'Score this activity', 'aiad-core' ); ?></button>
					<div data-sara-exposure-result hidden></div>
					<div class="sara-field" style="margin-top:1.25rem">
						<label class="sara-label" for="sara-exp-name"><?php esc_html_e( 'Name this activity (optional)', 'aiad-core' ); ?></label>
						<input class="sara-input" type="text" id="sara-exp-name" data-sara-exposure-name placeholder="<?php esc_attr_e( 'e.g. Year 10 essay — take home', 'aiad-core' ); ?>" />
					</div>
					<button type="button" class="sara-btn sara-btn--ghost" data-sara-exposure-add><?php esc_html_e( 'Add to class picture →', 'aiad-core' ); ?></button>
				</form>
			</div>

			<!-- Student reliance -->
			<div class="sara-tab-panel" data-sara-panel="reliance" role="tabpanel" hidden>
				<form data-sara-reliance-form novalidate>
					<p class="sara-muted"><?php esc_html_e( 'How often is each statement true for you? (Never · Sometimes · Often · Almost always)', 'aiad-core' ); ?></p>
					<?php
					$reliance_items = array(
						array( 'key' => 'own_work', 'reverse' => false, 'text' => __( 'I let AI write work I hand in as my own.', 'aiad-core' ) ),
						array( 'key' => 'think_first', 'reverse' => false, 'text' => __( 'I ask AI to think before I attempt the task myself.', 'aiad-core' ) ),
						array( 'key' => 'copy_answers', 'reverse' => false, 'text' => __( 'I copy AI answers without checking them.', 'aiad-core' ) ),
						array( 'key' => 'check_facts', 'reverse' => true, 'text' => __( 'I use AI to check facts after doing the work myself.', 'aiad-core' ) ),
						array( 'key' => 'explain_redo', 'reverse' => true, 'text' => __( 'I use AI to explain something, then redo it in my own words.', 'aiad-core' ) ),
						array( 'key' => 'without_ai', 'reverse' => true, 'text' => __( 'I could complete the work without AI if I had to.', 'aiad-core' ) ),
					);
					$freq = array(
						''  => __( 'Choose…', 'aiad-core' ),
						'0' => __( 'Never', 'aiad-core' ),
						'1' => __( 'Sometimes', 'aiad-core' ),
						'2' => __( 'Often', 'aiad-core' ),
						'3' => __( 'Almost always', 'aiad-core' ),
					);
					foreach ( $reliance_items as $i => $item ) :
						$id = 'sara-rel-' . $item['key'];
						?>
					<div class="sara-field">
						<label class="sara-label" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( ( $i + 1 ) . '. ' . $item['text'] ); ?></label>
						<select class="sara-select" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $item['key'] ); ?>" <?php echo $item['reverse'] ? 'data-reverse="1"' : ''; ?>>
							<?php foreach ( $freq as $val => $label ) : ?>
								<option value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<?php endforeach; ?>
					<button type="button" class="sara-btn sara-btn--primary" data-sara-reliance-score><?php esc_html_e( 'See my pattern', 'aiad-core' ); ?></button>
					<div data-sara-reliance-result hidden></div>
				</form>
			</div>

			<!-- Class picture -->
			<div class="sara-tab-panel" data-sara-panel="class" role="tabpanel" hidden>
				<div class="sara-gauge-wrap">
					<svg class="sara-gauge" viewBox="0 0 200 110" aria-hidden="true">
						<path d="M20 100 A80 80 0 0 1 180 100" fill="none" stroke="#333" stroke-width="12" stroke-linecap="round"/>
						<path d="M20 100 A80 80 0 0 1 70 35" fill="none" stroke="#5fa463" stroke-width="12"/>
						<path d="M70 35 A80 80 0 0 1 130 35" fill="none" stroke="#e0a52e" stroke-width="12"/>
						<path d="M130 35 A80 80 0 0 1 180 100" fill="none" stroke="#cc5c4d" stroke-width="12"/>
						<line class="sara-gauge__needle" data-sara-gauge-needle x1="100" y1="100" x2="100" y2="30" stroke="#f2efe4" stroke-width="3" stroke-linecap="round" style="transform-origin:100px 100px;transform:rotate(-90deg)"/>
						<circle cx="100" cy="100" r="6" fill="#e0a52e"/>
					</svg>
					<p class="sara-gauge__label" data-sara-gauge-label><?php esc_html_e( 'Add assessed activities to see your school picture', 'aiad-core' ); ?></p>
				</div>
				<p><strong><?php esc_html_e( 'Average exposure:', 'aiad-core' ); ?></strong> <span data-sara-class-avg>—</span></p>
				<p data-sara-class-empty class="sara-muted"><?php esc_html_e( 'Score activities in the first tab, then add them here to build a class or school roll-up.', 'aiad-core' ); ?></p>
				<ul class="sara-class-list" data-sara-class-list></ul>
				<div class="sara-warn" data-sara-class-warn hidden>
					<?php esc_html_e( 'High average exposure — this is the redesign signal. Consider supervision, filtering, or in-person delivery for high-stakes tasks.', 'aiad-core' ); ?>
				</div>
			</div>

			<!-- Verify -->
			<div class="sara-tab-panel" data-sara-panel="verify" role="tabpanel" hidden>
				<p class="sara-muted"><?php esc_html_e( 'A four-step habit — not scored. Tap each step when you have done it.', 'aiad-core' ); ?></p>
				<ul class="sara-verify-steps" data-sara-verify-steps>
					<li class="sara-verify-step" data-sara-verify-step>
						<strong><?php esc_html_e( '1. Form your own view first', 'aiad-core' ); ?></strong>
						<?php esc_html_e( 'Attempt, draft, or mark blind before opening the AI. Guards against over-reliance.', 'aiad-core' ); ?>
					</li>
					<li class="sara-verify-step" data-sara-verify-step>
						<strong><?php esc_html_e( '2. Compare, don\'t defer', 'aiad-core' ); ?></strong>
						<?php esc_html_e( 'When the AI differs, find the specific reason — don\'t assume either side is right. Guards against the fluency trap.', 'aiad-core' ); ?>
					</li>
					<li class="sara-verify-step" data-sara-verify-step>
						<strong><?php esc_html_e( '3. Check the blind spots', 'aiad-core' ); ?></strong>
						<?php esc_html_e( 'Name what the AI cannot see: effort, working, context. Guards against fluency & time pressure.', 'aiad-core' ); ?>
					</li>
					<li class="sara-verify-step" data-sara-verify-step>
						<strong><?php esc_html_e( '4. Own the call', 'aiad-core' ); ?></strong>
						<?php esc_html_e( 'The human signs off and is accountable. Guards against responsibility drift.', 'aiad-core' ); ?>
					</li>
				</ul>
				<p class="sara-panel" data-sara-verify-done hidden><?php esc_html_e( 'Routine complete — you have verified before trusting.', 'aiad-core' ); ?></p>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( ! empty( $show['curriculum'] ) ) : ?>
	<section class="sara-section" id="sara-curriculum" data-sara-reveal>
		<div class="sara-wrap">
			<h2><?php esc_html_e( 'Curriculum', 'aiad-core' ); ?></h2>
			<p class="sara-muted"><?php esc_html_e( 'Six modules turning UK guidance into something teachable.', 'aiad-core' ); ?></p>
			<?php
			$modules = array(
				array(
					'code' => 'M-01',
					'title' => __( 'Foundations & the UK policy landscape', 'aiad-core' ),
					'lessons' => array(
						__( 'Generative vs agentic AI in plain terms', 'aiad-core' ),
						__( 'DfE position and the AI Opportunities Action Plan', 'aiad-core' ),
						__( 'Why teacher judgement stays central', 'aiad-core' ),
						__( 'What "applies to England" means', 'aiad-core' ),
					),
				),
				array(
					'code' => 'M-02',
					'title' => __( 'Safety first: assess before you adopt', 'aiad-core' ),
					'lessons' => array(
						__( 'Running a use-case risk assessment', 'aiad-core' ),
						__( 'Staff-facing vs pupil-facing AI; under-18s', 'aiad-core' ),
						__( 'Age restrictions, supervision, filtering & monitoring', 'aiad-core' ),
						__( 'KCSIE, safeguarding and AI-enabled risks', 'aiad-core' ),
					),
				),
				array(
					'code' => 'M-03',
					'title' => __( 'Data protection & privacy', 'aiad-core' ),
					'lessons' => array(
						__( 'Keeping personal data out of AI tools', 'aiad-core' ),
						__( 'When a DPIA is required', 'aiad-core' ),
						__( 'Transparency with pupils, parents and guardians', 'aiad-core' ),
						__( 'Automated decisions about children (ICO)', 'aiad-core' ),
					),
				),
				array(
					'code' => 'M-04',
					'title' => __( 'Intellectual property & copyright', 'aiad-core' ),
					'lessons' => array(
						__( 'Pupils\' work as copyright; training on student data', 'aiad-core' ),
						__( 'Permission from a minor\'s parent or guardian', 'aiad-core' ),
						__( 'Verifying output for accuracy and bias', 'aiad-core' ),
					),
				),
				array(
					'code' => 'M-05',
					'title' => __( 'Academic integrity & assessment', 'aiad-core' ),
					'lessons' => array(
						__( 'What counts as AI malpractice (JCQ)', 'aiad-core' ),
						__( 'Reviewing homework and unsupervised study', 'aiad-core' ),
						__( 'Using the Risk Meter to redesign exposed assessments', 'aiad-core' ),
					),
				),
				array(
					'code' => 'M-06',
					'title' => __( 'Governance, oversight & your AI policy', 'aiad-core' ),
					'lessons' => array(
						__( 'Roles for SLT, governors, DSL and DPO', 'aiad-core' ),
						__( 'Writing and reviewing a whole-school AI use policy', 'aiad-core' ),
						__( 'How Ofsted looks at your AI decisions', 'aiad-core' ),
						__( 'Embedding verify-before-you-trust in daily practice', 'aiad-core' ),
					),
				),
			);
			foreach ( $modules as $mod ) :
				?>
			<div class="sara-accordion" data-sara-accordion>
				<button type="button" class="sara-acc-trigger" data-sara-acc-trigger aria-expanded="false">
					<span><span class="sara-acc-code"><?php echo esc_html( $mod['code'] ); ?></span><?php echo esc_html( $mod['title'] ); ?></span>
					<span aria-hidden="true">+</span>
				</button>
				<div class="sara-acc-body" data-sara-acc-body hidden>
					<ul>
						<?php foreach ( $mod['lessons'] as $lesson ) : ?>
							<li><?php echo esc_html( $lesson ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( ! empty( $show['contributors'] ) ) : ?>
	<section class="sara-section" data-sara-reveal>
		<div class="sara-wrap">
			<h2><?php esc_html_e( 'Who built it', 'aiad-core' ); ?></h2>
			<p class="sara-muted"><?php esc_html_e( 'Built by risk and governance practitioners, mapped to DfE, Ofsted, ICO and JCQ guidance. This supports — not replaces — your DSL, DPO and senior leaders. An education or safeguarding specialist would strengthen it for a live school audience.', 'aiad-core' ); ?></p>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( ! empty( $show['resources'] ) ) : ?>
	<section class="sara-section" data-sara-reveal>
		<div class="sara-wrap">
			<h2><?php esc_html_e( 'Templates & live classes', 'aiad-core' ); ?></h2>
			<p class="sara-muted"><?php esc_html_e( 'Referenced resources include a school AI-use policy template, DPIA checklist, activity exposure audit sheet, and letter to parents — plus free live classes and office hours (coming soon).', 'aiad-core' ); ?></p>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( ! empty( $show['sources'] ) ) : ?>
	<section class="sara-section sara-section--paper" id="sara-sources" data-sara-reveal>
		<div class="sara-wrap">
			<h2><?php esc_html_e( 'Alignment & sources', 'aiad-core' ); ?></h2>
			<p class="sara-muted"><?php esc_html_e( 'The app links to published UK guidance. Those sources are the authority — this is a way in, not a substitute. Educational resource, not legal advice. England framework; Scotland, Wales and NI should follow their own national guidance. Guidance current as of June 2027.', 'aiad-core' ); ?></p>
			<ul class="sara-sources-list">
				<li><a href="https://www.gov.uk/government/publications/generative-artificial-intelligence-in-education" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'DfE — Generative AI in education', 'aiad-core' ); ?></a></li>
				<li><a href="https://www.gov.uk/government/collections/using-ai-in-education-settings-support-materials" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'DfE — Using AI in education settings', 'aiad-core' ); ?></a></li>
				<li><a href="https://www.gov.uk/government/publications/generative-ai-product-safety-standards" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'DfE — Generative AI: product safety standards', 'aiad-core' ); ?></a></li>
				<li><a href="https://www.gov.uk/government/publications/keeping-children-safe-in-education--2" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Keeping Children Safe in Education (KCSIE)', 'aiad-core' ); ?></a></li>
				<li><a href="https://ico.org.uk/for-organisations/uk-gdpr-guidance-and-resources/artificial-intelligence/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'ICO — AI & data protection (UK GDPR)', 'aiad-core' ); ?></a></li>
				<li><a href="https://www.jcq.org.uk/exams-office/malpractice/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'JCQ — AI use in assessments', 'aiad-core' ); ?></a></li>
				<li><a href="https://www.gov.uk/government/publications/ofsteds-approach-to-ai" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Ofsted\'s approach to AI', 'aiad-core' ); ?></a></li>
				<li><a href="https://www.gov.uk/government/publications/regulating-artificial-intelligence-in-qualifications" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Ofqual — regulating AI in qualifications', 'aiad-core' ); ?></a></li>
			</ul>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( ! empty( $show['enrol'] ) ) : ?>
	<section class="sara-section" id="sara-enrol" data-sara-reveal>
		<div class="sara-wrap">
			<h2><?php esc_html_e( 'Free enrolment', 'aiad-core' ); ?></h2>
			<p class="sara-muted"><?php esc_html_e( 'Register interest for live classes and office hours. Demo build: confirmation only, not sent to a server.', 'aiad-core' ); ?></p>
			<form data-sara-enrol class="sara-panel" style="max-width:420px">
				<label class="sara-label" for="sara-enrol-email"><?php esc_html_e( 'Work email', 'aiad-core' ); ?></label>
				<input class="sara-input" type="email" id="sara-enrol-email" name="email" required autocomplete="email" />
				<button type="submit" class="sara-btn sara-btn--primary" style="margin-top:0.75rem"><?php esc_html_e( 'Register interest', 'aiad-core' ); ?></button>
				<p data-sara-enrol-msg hidden class="sara-muted" style="margin-top:0.75rem"></p>
			</form>
		</div>
	</section>
	<?php endif; ?>

	<footer class="sara-footer">
		<div class="sara-wrap">
			<p class="sara-disclaimer"><?php esc_html_e( 'Not legal advice. Decisions for your setting should be made with your DPO, DSL and senior leadership. Review AI policies at least annually.', 'aiad-core' ); ?></p>
			<p class="sara-credit">
				<?php esc_html_e( 'Produced by', 'aiad-core' ); ?>
				<strong><?php esc_html_e( 'AI Awareness Day', 'aiad-core' ); ?></strong>
				&middot;
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'aiawarenessday.co.uk' ); ?></a>
			</p>
		</div>
	</footer>

</div>
