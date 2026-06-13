<?php
/**
 * Admin settings — editable questions & recommendations.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'AI Risk Benchmark — Settings', 'ai-risk-benchmark' ); ?></h1>

	<?php if ( ! empty( $saved ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'ai-risk-benchmark' ); ?></p></div>
	<?php endif; ?>

	<p><?php esc_html_e( 'Shortcodes:', 'ai-risk-benchmark' ); ?> <code>[ai_risk_benchmark]</code> · <code>[ai_risk_school_dashboard]</code></p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'airb_save_settings' ); ?>
		<input type="hidden" name="action" value="airb_save_settings" />

		<h2><?php esc_html_e( 'General copy', 'ai-risk-benchmark' ); ?></h2>
		<table class="form-table">
			<tr>
				<th><label for="airb-positioning-headline"><?php esc_html_e( 'Positioning headline', 'ai-risk-benchmark' ); ?></label></th>
				<td><input type="text" class="large-text" id="airb-positioning-headline" name="positioning_headline" value="<?php echo esc_attr( (string) ( $config['positioning']['headline'] ?? '' ) ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="airb-positioning-problem"><?php esc_html_e( 'Problem statement', 'ai-risk-benchmark' ); ?></label></th>
				<td><textarea class="large-text" rows="3" id="airb-positioning-problem" name="positioning_problem"><?php echo esc_textarea( (string) ( $config['positioning']['problem'] ?? '' ) ); ?></textarea></td>
			</tr>
			<tr>
				<th><label for="airb-positioning-solution"><?php esc_html_e( 'Solution statement', 'ai-risk-benchmark' ); ?></label></th>
				<td><textarea class="large-text" rows="2" id="airb-positioning-solution" name="positioning_solution"><?php echo esc_textarea( (string) ( $config['positioning']['solution'] ?? '' ) ); ?></textarea></td>
			</tr>
			<tr>
				<th><label for="airb-intro"><?php esc_html_e( 'Introduction', 'ai-risk-benchmark' ); ?></label></th>
				<td><textarea class="large-text" rows="3" id="airb-intro" name="intro"><?php echo esc_textarea( (string) ( $config['intro'] ?? '' ) ); ?></textarea></td>
			</tr>
			<tr>
				<th><label for="airb-disclaimer"><?php esc_html_e( 'Disclaimer', 'ai-risk-benchmark' ); ?></label></th>
				<td><textarea class="large-text" rows="4" id="airb-disclaimer" name="disclaimer"><?php echo esc_textarea( (string) ( $config['disclaimer'] ?? '' ) ); ?></textarea></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Consultation CTA', 'ai-risk-benchmark' ); ?></th>
				<td>
					<input type="text" class="regular-text" name="cta_title" value="<?php echo esc_attr( (string) ( $config['consultation_cta']['title'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Title', 'ai-risk-benchmark' ); ?>" /><br>
					<input type="url" class="regular-text" name="cta_url" value="<?php echo esc_attr( (string) ( $config['consultation_cta']['url'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'URL', 'ai-risk-benchmark' ); ?>" /><br>
					<input type="text" class="regular-text" name="cta_text" value="<?php echo esc_attr( (string) ( $config['consultation_cta']['text'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Link text', 'ai-risk-benchmark' ); ?>" />
				</td>
			</tr>
		</table>

		<?php $gateway = (array) ( $config['gateway'] ?? array() ); ?>
		<h2><?php esc_html_e( 'Audit gateway (teachers & leaders)', 'ai-risk-benchmark' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Shown after results — frames the audit as a doorway into tracking progress, CPD and consultation.', 'ai-risk-benchmark' ); ?></p>
		<table class="form-table">
			<tr>
				<th><label for="gateway-headline"><?php esc_html_e( 'Gateway headline', 'ai-risk-benchmark' ); ?></label></th>
				<td><input type="text" class="large-text" id="gateway-headline" name="gateway_headline" value="<?php echo esc_attr( (string) ( $gateway['headline'] ?? '' ) ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="gateway-intro"><?php esc_html_e( 'Gateway intro', 'ai-risk-benchmark' ); ?></label></th>
				<td><textarea class="large-text" rows="2" id="gateway-intro" name="gateway_intro"><?php echo esc_textarea( (string) ( $gateway['intro'] ?? '' ) ); ?></textarea></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Track progress card', 'ai-risk-benchmark' ); ?></th>
				<td>
					<?php $track = (array) ( $gateway['track_progress'] ?? array() ); ?>
					<input type="text" class="large-text" name="gateway_track_title" value="<?php echo esc_attr( (string) ( $track['title'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Title', 'ai-risk-benchmark' ); ?>" /><br>
					<textarea class="large-text" rows="2" name="gateway_track_body"><?php echo esc_textarea( (string) ( $track['body'] ?? '' ) ); ?></textarea><br>
					<input type="text" class="regular-text" name="gateway_track_cta" value="<?php echo esc_attr( (string) ( $track['cta_text'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Button text', 'ai-risk-benchmark' ); ?>" />
					<input type="url" class="regular-text" name="gateway_track_url" value="<?php echo esc_attr( (string) ( $track['cta_url'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'School dashboard page URL (optional)', 'ai-risk-benchmark' ); ?>" />
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Book CPD card', 'ai-risk-benchmark' ); ?></th>
				<td>
					<?php $cpd = (array) ( $gateway['book_cpd'] ?? array() ); ?>
					<input type="text" class="large-text" name="gateway_cpd_title" value="<?php echo esc_attr( (string) ( $cpd['title'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Title', 'ai-risk-benchmark' ); ?>" /><br>
					<textarea class="large-text" rows="2" name="gateway_cpd_body"><?php echo esc_textarea( (string) ( $cpd['body'] ?? '' ) ); ?></textarea><br>
					<input type="text" class="regular-text" name="gateway_cpd_cta" value="<?php echo esc_attr( (string) ( $cpd['cta_text'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Button text', 'ai-risk-benchmark' ); ?>" />
					<input type="url" class="regular-text" name="gateway_cpd_url" value="<?php echo esc_attr( (string) ( $cpd['cta_url'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'URL', 'ai-risk-benchmark' ); ?>" />
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Consultation card', 'ai-risk-benchmark' ); ?></th>
				<td>
					<?php $consult = (array) ( $gateway['book_consultation'] ?? array() ); ?>
					<input type="text" class="large-text" name="gateway_consult_title" value="<?php echo esc_attr( (string) ( $consult['title'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Title', 'ai-risk-benchmark' ); ?>" /><br>
					<textarea class="large-text" rows="2" name="gateway_consult_body"><?php echo esc_textarea( (string) ( $consult['body'] ?? '' ) ); ?></textarea><br>
					<input type="text" class="regular-text" name="gateway_consult_cta" value="<?php echo esc_attr( (string) ( $consult['cta_text'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Button text', 'ai-risk-benchmark' ); ?>" />
					<input type="url" class="regular-text" name="gateway_consult_url" value="<?php echo esc_attr( (string) ( $consult['cta_url'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'URL', 'ai-risk-benchmark' ); ?>" />
				</td>
			</tr>
		</table>

		<h2><?php esc_html_e( 'Questions', 'ai-risk-benchmark' ); ?></h2>
		<p class="description"><?php esc_html_e( 'For radio/select questions, enter options one per line as: value|label|score (0=low risk, 3=high risk). Leave options empty for slider questions.', 'ai-risk-benchmark' ); ?></p>

		<?php foreach ( (array) ( $config['questions'] ?? array() ) as $i => $q ) : ?>
			<div style="border:1px solid #ccd0d4;padding:12px;margin-bottom:12px;background:#fff;">
				<input type="hidden" name="q_id[]" value="<?php echo esc_attr( (string) ( $q['id'] ?? '' ) ); ?>" />
				<p>
					<strong><?php echo esc_html( sprintf( 'Q%s — %s', (string) ( $i + 1 ), (string) ( $q['id'] ?? '' ) ) ); ?></strong>
				</p>
				<p>
					<select name="q_role[]">
						<?php foreach ( $roles as $slug => $label ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $q['role'] ?? '', $slug ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<select name="q_domain[]">
						<?php foreach ( $domains as $slug => $label ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $q['domain'] ?? '', $slug ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<select name="q_type[]">
						<option value="radio" <?php selected( $q['type'] ?? '', 'radio' ); ?>><?php esc_html_e( 'Radio', 'ai-risk-benchmark' ); ?></option>
						<option value="select" <?php selected( $q['type'] ?? '', 'select' ); ?>><?php esc_html_e( 'Select', 'ai-risk-benchmark' ); ?></option>
						<option value="slider" <?php selected( $q['type'] ?? '', 'slider' ); ?>><?php esc_html_e( 'Slider', 'ai-risk-benchmark' ); ?></option>
					</select>
				</p>
				<p><textarea class="large-text" rows="2" name="q_text[]"><?php echo esc_textarea( (string) ( $q['text'] ?? '' ) ); ?></textarea></p>
				<p><textarea class="large-text code" rows="4" name="q_options[]" placeholder="value|label|score"><?php echo esc_textarea( AIRB_Admin::options_to_lines( (array) ( $q['options'] ?? array() ) ) ); ?></textarea></p>
			</div>
		<?php endforeach; ?>

		<h2><?php esc_html_e( 'Recommendations', 'ai-risk-benchmark' ); ?></h2>
		<?php foreach ( (array) ( $config['recommendations'] ?? array() ) as $rec ) : ?>
			<div style="border:1px solid #ccd0d4;padding:12px;margin-bottom:12px;background:#fff;">
				<p>
					<select name="r_domain[]">
						<?php foreach ( $domains as $slug => $label ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $rec['domain'] ?? '', $slug ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<select name="r_min_band[]">
						<?php foreach ( array( 'moderate', 'high', 'critical' ) as $band ) : ?>
							<option value="<?php echo esc_attr( $band ); ?>" <?php selected( $rec['min_band'] ?? '', $band ); ?>><?php echo esc_html( ucfirst( $band ) ); ?>+</option>
						<?php endforeach; ?>
					</select>
				</p>
				<p><input type="text" class="large-text" name="r_title[]" value="<?php echo esc_attr( (string) ( $rec['title'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Title', 'ai-risk-benchmark' ); ?>" /></p>
				<p><textarea class="large-text" rows="2" name="r_body[]"><?php echo esc_textarea( (string) ( $rec['body'] ?? '' ) ); ?></textarea></p>
				<p>
					<input type="text" name="r_cta_text[]" value="<?php echo esc_attr( (string) ( $rec['cta_text'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'CTA text', 'ai-risk-benchmark' ); ?>" />
					<input type="url" class="regular-text" name="r_cta_url[]" value="<?php echo esc_attr( (string) ( $rec['cta_url'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'CTA URL', 'ai-risk-benchmark' ); ?>" />
				</p>
			</div>
		<?php endforeach; ?>

		<?php submit_button( __( 'Save settings', 'ai-risk-benchmark' ) ); ?>
	</form>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Reset all questions and recommendations to defaults?', 'ai-risk-benchmark' ) ); ?>');">
		<?php wp_nonce_field( 'airb_reset_defaults' ); ?>
		<input type="hidden" name="action" value="airb_reset_defaults" />
		<?php submit_button( __( 'Reset to defaults', 'ai-risk-benchmark' ), 'delete' ); ?>
	</form>
</div>
