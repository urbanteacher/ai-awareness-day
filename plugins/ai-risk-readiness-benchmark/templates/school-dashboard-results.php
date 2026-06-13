<?php
/**
 * School dashboard results partial (PHP + JS).
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $rollup ) || ! is_array( $rollup ) ) {
	return;
}
?>
<div class="airb__school-results" data-airb-school-results>
	<h3 class="airb__panel-title"><?php echo esc_html( (string) $rollup['school_name'] ); ?></h3>
	<p class="airb__muted">
		<?php
		printf(
			esc_html__( '%1$d of %2$d stakeholder groups complete', 'ai-risk-benchmark' ),
			(int) $rollup['roles_complete'],
			(int) $rollup['roles_total']
		);
		?>
	</p>

	<div class="airb__role-bars">
		<?php foreach ( (array) $rollup['roles'] as $slug => $data ) : ?>
			<div class="airb__role-bar <?php echo null === $data['readiness'] ? 'is-missing' : ''; ?>">
				<span class="airb__role-bar-label"><?php echo esc_html( (string) ( $data['label'] ?? $slug ) ); ?></span>
				<?php if ( null !== $data['readiness'] ) : ?>
					<div class="airb__bar-track"><div class="airb__bar-fill" style="width:<?php echo esc_attr( (string) $data['readiness'] ); ?>%"></div></div>
					<span class="airb__role-bar-val"><?php echo esc_html( (string) $data['readiness'] ); ?>%</span>
				<?php else : ?>
					<span class="airb__role-bar-val airb__muted"><?php esc_html_e( 'Awaiting audit', 'ai-risk-benchmark' ); ?></span>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="airb__cards">
		<div class="airb__card airb__card--<?php echo esc_attr( (string) $rollup['overall_risk_level'] ); ?>">
			<span class="airb__card-title"><?php esc_html_e( 'Overall DfE AI Alignment Score', 'ai-risk-benchmark' ); ?></span>
			<strong class="airb__card-value"><?php echo esc_html( (string) $rollup['overall_alignment'] ); ?>%</strong>
		</div>
		<div class="airb__card airb__card--<?php echo esc_attr( (string) $rollup['overall_risk_level'] ); ?>">
			<span class="airb__card-title"><?php esc_html_e( 'Risk level', 'ai-risk-benchmark' ); ?></span>
			<strong class="airb__card-value"><?php echo esc_html( (string) $rollup['overall_risk_label'] ); ?></strong>
		</div>
	</div>

	<?php if ( ! empty( $rollup['exposure_breakdown'] ) ) : ?>
		<h4><?php esc_html_e( 'Exposure breakdown', 'ai-risk-benchmark' ); ?></h4>
		<table class="airb__exposure-table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Risk area', 'ai-risk-benchmark' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Level', 'ai-risk-benchmark' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( (array) $rollup['exposure_breakdown'] as $row ) : ?>
					<tr>
						<td><?php echo esc_html( (string) ( $row['label'] ?? '' ) ); ?></td>
						<td><span class="airb__exposure-pill airb__exposure-pill--<?php echo esc_attr( strtolower( (string) ( $row['band_label'] ?? 'low' ) ) ); ?>"><?php echo esc_html( (string) ( $row['band_label'] ?? '' ) ); ?></span></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<?php if ( ! empty( $rollup['key_exposure_areas'] ) ) : ?>
		<h4><?php esc_html_e( 'Key exposure areas', 'ai-risk-benchmark' ); ?></h4>
		<?php echo AIRB_Scoring::risk_cells_html( (array) $rollup['key_exposure_areas'], 'stat' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endif; ?>
</div>
