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

$alignment_label = (string) ( $rollup['alignment_score_label'] ?? AIRB_Scoring::alignment_score_label() );
?>
<div class="airb__school-results" data-airb-school-results>
	<h3 class="airb__panel-title"><?php esc_html_e( 'Whole-School AI Readiness Snapshot', 'ai-risk-benchmark' ); ?></h3>
	<p class="airb__muted airb__school-snapshot-sub">
		<?php echo esc_html( (string) $rollup['school_name'] ); ?>
		&middot;
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
					<span class="airb__role-bar-val">
						<?php echo esc_html( (string) $data['readiness'] ); ?>%
						<?php if ( ! empty( $data['readiness_band_label'] ) ) : ?>
							<span class="airb__role-bar-band"><?php echo esc_html( (string) $data['readiness_band_label'] ); ?></span>
						<?php endif; ?>
					</span>
				<?php else : ?>
					<span class="airb__role-bar-val airb__muted"><?php esc_html_e( 'Awaiting audit', 'ai-risk-benchmark' ); ?></span>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="airb__cards">
		<div class="airb__card airb__card--<?php echo esc_attr( (string) $rollup['overall_risk_level'] ); ?>">
			<span class="airb__card-title"><?php echo esc_html( $alignment_label ); ?></span>
			<strong class="airb__card-value"><?php echo esc_html( (string) $rollup['overall_alignment'] ); ?>%</strong>
			<?php if ( ! empty( $rollup['overall_readiness_band'] ) ) : ?>
				<span class="airb__card-band"><?php echo esc_html( (string) $rollup['overall_readiness_band'] ); ?></span>
			<?php endif; ?>
		</div>
		<div class="airb__card airb__card--<?php echo esc_attr( (string) $rollup['overall_risk_level'] ); ?>">
			<span class="airb__card-title"><?php esc_html_e( 'Risk level', 'ai-risk-benchmark' ); ?></span>
			<strong class="airb__card-value"><?php echo esc_html( (string) $rollup['overall_risk_label'] ); ?></strong>
		</div>
	</div>

	<?php if ( ! empty( $rollup['alignment_disclaimer'] ) ) : ?>
		<p class="airb__muted airb__alignment-disclaimer"><?php echo esc_html( (string) $rollup['alignment_disclaimer'] ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $rollup['key_exposure_areas'] ) ) : ?>
		<h4><?php esc_html_e( 'Highest risk areas', 'ai-risk-benchmark' ); ?></h4>
		<?php echo AIRB_Scoring::risk_cells_html( (array) $rollup['key_exposure_areas'], 'stat' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endif; ?>

	<?php if ( ! empty( $rollup['recommended_priorities'] ) ) : ?>
		<h4><?php esc_html_e( 'Recommended school priorities', 'ai-risk-benchmark' ); ?></h4>
		<ol class="airb__school-priorities">
			<?php foreach ( (array) $rollup['recommended_priorities'] as $priority ) : ?>
				<li><?php echo esc_html( (string) $priority ); ?></li>
			<?php endforeach; ?>
		</ol>
	<?php endif; ?>

	<?php if ( ! empty( $rollup['exposure_breakdown'] ) ) : ?>
		<details class="airb__school-exposure-details">
			<summary><?php esc_html_e( 'Full exposure breakdown', 'ai-risk-benchmark' ); ?></summary>
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
		</details>
	<?php endif; ?>
</div>
