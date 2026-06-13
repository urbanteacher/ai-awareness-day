<?php
/**
 * Printable report template.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$roles    = AIRB_Defaults::roles();
$role_lbl = $roles[ $airb_report_role ] ?? $airb_report_role;
$results  = $airb_report_results;
$config   = $airb_report_config;
?>
<div class="airb-report">
	<h1><?php esc_html_e( 'AI Risk & Readiness Benchmark Report', 'ai-risk-benchmark' ); ?></h1>
	<p><strong><?php esc_html_e( 'Role:', 'ai-risk-benchmark' ); ?></strong> <?php echo esc_html( $role_lbl ); ?></p>
	<p><strong><?php esc_html_e( 'Date:', 'ai-risk-benchmark' ); ?></strong> <?php echo esc_html( gmdate( 'j F Y' ) ); ?></p>
	<p class="airb-report__disclaimer"><?php echo esc_html( (string) ( $config['disclaimer'] ?? '' ) ); ?></p>

	<h2><?php esc_html_e( 'Summary', 'ai-risk-benchmark' ); ?></h2>
	<p><strong><?php esc_html_e( 'Overall risk level:', 'ai-risk-benchmark' ); ?></strong> <?php echo esc_html( (string) ( $results['risk_level_label'] ?? '' ) ); ?></p>

	<?php if ( ! empty( $results['role_result_cards'] ) ) : ?>
		<h3><?php esc_html_e( 'Role-specific scores', 'ai-risk-benchmark' ); ?></h3>
		<ul>
			<?php foreach ( (array) $results['role_result_cards'] as $card ) : ?>
				<li><?php echo esc_html( (string) ( $card['label'] ?? '' ) ); ?>: <strong><?php echo esc_html( (string) ( $card['value'] ?? '' ) ); ?></strong></li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<ul>
			<li><?php esc_html_e( 'DfE AI Alignment Score:', 'ai-risk-benchmark' ); ?> <strong><?php echo esc_html( (string) ( $results['alignment_score'] ?? 0 ) ); ?>/100</strong></li>
			<li><?php esc_html_e( 'AI Dependency Index:', 'ai-risk-benchmark' ); ?> <strong><?php echo esc_html( (string) ( $results['dependency_index'] ?? 0 ) ); ?>%</strong></li>
			<li><?php esc_html_e( 'Human Oversight:', 'ai-risk-benchmark' ); ?> <strong><?php echo esc_html( (string) ( $results['human_oversight_label'] ?? '' ) ); ?></strong></li>
		</ul>
	<?php endif; ?>

	<?php if ( ! empty( $results['key_exposure_areas'] ) ) : ?>
		<h3><?php esc_html_e( 'Key exposure areas', 'ai-risk-benchmark' ); ?></h3>
		<ul>
			<?php foreach ( (array) $results['key_exposure_areas'] as $area ) : ?>
				<li><?php echo esc_html( (string) ( $area['label'] ?? '' ) ); ?> — <?php echo esc_html( (string) ( $area['risk'] ?? 0 ) ); ?>%</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if ( ! empty( $results['next_steps'] ) ) : ?>
		<h2><?php esc_html_e( 'Recommended for you', 'ai-risk-benchmark' ); ?></h2>
		<?php foreach ( (array) $results['next_steps'] as $step ) : ?>
			<h3><?php echo esc_html( (string) ( $step['title'] ?? '' ) ); ?>
				<?php if ( ! empty( $step['type_label'] ) ) : ?>
					<small>(<?php echo esc_html( (string) $step['type_label'] ); ?>)</small>
				<?php endif; ?>
			</h3>
			<p><?php echo esc_html( (string) ( $step['body'] ?? '' ) ); ?></p>
			<?php if ( ! empty( $step['cta_url'] ) ) : ?>
				<p><a href="<?php echo esc_url( (string) $step['cta_url'] ); ?>"><?php echo esc_html( (string) ( $step['cta_text'] ?? $step['title'] ?? '' ) ); ?></a></p>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php if ( ! empty( $results['gateway']['cards'] ) ) : ?>
		<h2><?php echo esc_html( (string) ( $results['gateway']['headline'] ?? __( 'Your audit is the starting point', 'ai-risk-benchmark' ) ) ); ?></h2>
		<?php if ( ! empty( $results['gateway']['intro'] ) ) : ?>
			<p><?php echo esc_html( (string) $results['gateway']['intro'] ); ?></p>
		<?php endif; ?>
		<ul>
			<?php foreach ( (array) $results['gateway']['cards'] as $card ) : ?>
				<li><strong><?php echo esc_html( (string) ( $card['title'] ?? '' ) ); ?></strong> — <?php echo esc_html( (string) ( $card['body'] ?? '' ) ); ?>
					<?php if ( ! empty( $card['cta_url'] ) ) : ?>
						(<a href="<?php echo esc_url( (string) $card['cta_url'] ); ?>"><?php echo esc_html( (string) ( $card['cta_text'] ?? '' ) ); ?></a>)
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<h2><?php esc_html_e( 'Domain scores', 'ai-risk-benchmark' ); ?></h2>
	<table border="1" cellpadding="8" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Domain', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Risk %', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Readiness %', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Band', 'ai-risk-benchmark' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( (array) ( $results['domain_scores'] ?? array() ) as $dom ) : ?>
				<?php if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) { continue; } ?>
				<tr>
					<td><?php echo esc_html( (string) ( $dom['label'] ?? '' ) ); ?></td>
					<td><?php echo esc_html( (string) ( $dom['risk_percentage'] ?? 0 ) ); ?></td>
					<td><?php echo esc_html( (string) ( $dom['readiness_percentage'] ?? 0 ) ); ?></td>
					<td><?php echo esc_html( (string) ( $dom['band_label'] ?? '' ) ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php if ( ! empty( $results['recommendations'] ) ) : ?>
		<h2><?php esc_html_e( 'Recommendations', 'ai-risk-benchmark' ); ?></h2>
		<?php foreach ( (array) $results['recommendations'] as $rec ) : ?>
			<h3><?php echo esc_html( (string) ( $rec['title'] ?? '' ) ); ?></h3>
			<p><?php echo esc_html( (string) ( $rec['body'] ?? '' ) ); ?></p>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
