<?php
/**
 * Front-end school dashboard shortcode template.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rollup = $airb_school_rollup ?? null;
$school = $airb_school_name ?? '';
?>
<div class="airb airb--dashboard" id="airb-school-dashboard" data-airb-school-dashboard>
	<div class="airb__intro">
		<h2 class="airb__title"><?php esc_html_e( 'Whole-School AI Readiness Snapshot', 'ai-risk-benchmark' ); ?></h2>
		<p class="airb__lead"><?php esc_html_e( 'See readiness by role — teachers, students, parents and leaders — once each group has completed the benchmark.', 'ai-risk-benchmark' ); ?></p>
	</div>

	<form class="airb__panel airb__school-lookup" id="airb-school-lookup-form">
		<label class="airb__label" for="airb-school-lookup-input"><?php esc_html_e( 'School name', 'ai-risk-benchmark' ); ?></label>
		<input class="airb__input" type="text" id="airb-school-lookup-input" name="school_name" value="<?php echo esc_attr( $school ); ?>" required />
		<button type="submit" class="airb__btn airb__btn--primary"><?php esc_html_e( 'Load school picture', 'ai-risk-benchmark' ); ?></button>
	</form>

	<div class="airb__error" id="airb-school-error" role="alert" hidden></div>

	<div id="airb-school-results" <?php echo $rollup ? '' : 'hidden'; ?>>
		<?php if ( $rollup ) : ?>
			<?php include AIRB_PLUGIN_DIR . 'templates/school-dashboard-results.php'; ?>
		<?php endif; ?>
	</div>
</div>

<script>
(function(){
	var form = document.getElementById('airb-school-lookup-form');
	if (!form) return;
	var results = document.getElementById('airb-school-results');
	var err = document.getElementById('airb-school-error');
	function fail(message) {
		err.textContent = message;
		err.hidden = false;
		results.hidden = true;
	}
	form.addEventListener('submit', function(e){
		e.preventDefault();
		err.hidden = true;
		var name = document.getElementById('airb-school-lookup-input').value.trim();
		if (!name) return;
		// airbBenchmark is printed with the footer scripts, after this tag, so it is read when the form is sent.
		var cfg = window.airbBenchmark;
		if (!cfg || !cfg.rest) {
			fail('Something went wrong. Please try again.');
			return;
		}
		var body = new FormData();
		body.append('school_name', name);
		fetch(cfg.rest + 'school_dashboard', { method:'POST', body: body, credentials:'same-origin' })
			.then(function(r){ return r.json(); })
			.then(function(json){
				if (!json.success) {
					fail((json.data && json.data.message) ? json.data.message : 'Not found');
					return;
				}
				window.airbRenderSchoolDashboard && window.airbRenderSchoolDashboard(json.data.rollup, results);
				results.hidden = false;
			})
			.catch(function(){
				fail((cfg.i18n && cfg.i18n.error) || 'Something went wrong. Please try again.');
			});
	});
})();
</script>
