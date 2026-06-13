<?php
/**
 * Admin school dashboard view.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$roles = AIRB_Defaults::roles();
?>
<div class="wrap">
	<h1><?php esc_html_e( 'School-wide dashboard', 'ai-risk-benchmark' ); ?></h1>
	<p><?php esc_html_e( 'Aggregates consented benchmark submissions by school and stakeholder role.', 'ai-risk-benchmark' ); ?></p>

	<form method="get" class="airb-admin-filters">
		<input type="hidden" name="page" value="airb-school-dashboard" />
		<select name="school">
			<option value=""><?php esc_html_e( 'Select a school…', 'ai-risk-benchmark' ); ?></option>
			<?php foreach ( $schools as $s ) : ?>
				<option value="<?php echo esc_attr( (string) $s['school_name'] ); ?>" <?php selected( $school, (string) $s['school_name'] ); ?>>
					<?php echo esc_html( (string) $s['school_name'] ); ?>
					(<?php echo esc_html( (string) $s['submission_count'] ); ?>)
				</option>
			<?php endforeach; ?>
		</select>
		<?php submit_button( __( 'View roll-up', 'ai-risk-benchmark' ), 'secondary', '', false ); ?>
	</form>

	<?php if ( $rollup ) : ?>
		<h2><?php echo esc_html( $rollup['school_name'] ); ?></h2>
		<p>
			<?php
			printf(
				esc_html__( '%1$d of %2$d stakeholder groups · %3$d total submissions', 'ai-risk-benchmark' ),
				(int) $rollup['roles_complete'],
				(int) $rollup['roles_total'],
				(int) $rollup['total_submissions']
			);
			?>
		</p>

		<table class="widefat striped" style="max-width:640px">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Group', 'ai-risk-benchmark' ); ?></th>
					<th><?php esc_html_e( 'Readiness', 'ai-risk-benchmark' ); ?></th>
					<th><?php esc_html_e( 'Submissions', 'ai-risk-benchmark' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rollup['roles'] as $slug => $data ) : ?>
					<tr>
						<td><?php echo esc_html( (string) ( $data['label'] ?? $roles[ $slug ] ?? $slug ) ); ?></td>
						<td>
							<?php if ( null === $data['readiness'] ) : ?>
								<em><?php esc_html_e( 'No data yet', 'ai-risk-benchmark' ); ?></em>
							<?php else : ?>
								<strong><?php echo esc_html( (string) $data['readiness'] ); ?>%</strong>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( (string) ( $data['submissions'] ?? 0 ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<h3><?php esc_html_e( 'Overall DfE AI Alignment Score', 'ai-risk-benchmark' ); ?></h3>
		<p><strong style="font-size:1.5rem"><?php echo esc_html( (string) $rollup['overall_alignment'] ); ?>%</strong>
			— <?php echo esc_html( (string) $rollup['overall_risk_label'] ); ?></p>

		<?php if ( ! empty( $rollup['key_exposure_areas'] ) ) : ?>
			<h3><?php esc_html_e( 'Key exposure areas', 'ai-risk-benchmark' ); ?></h3>
			<ul>
				<?php foreach ( $rollup['key_exposure_areas'] as $area ) : ?>
					<li><?php echo esc_html( (string) $area['label'] ); ?> — <?php echo esc_html( (string) $area['risk'] ); ?>% risk</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<p class="description"><?php esc_html_e( 'Front-end shortcode:', 'ai-risk-benchmark' ); ?> <code>[ai_risk_school_dashboard school="<?php echo esc_attr( $rollup['school_name'] ); ?>"]</code></p>
	<?php elseif ( $school ) : ?>
		<p><em><?php esc_html_e( 'No consented submissions found for this school.', 'ai-risk-benchmark' ); ?></em></p>
	<?php endif; ?>
</div>
