<?php
/**
 * Admin submissions table.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$export_url = wp_nonce_url(
	add_query_arg(
		array(
			'airb_export' => 'csv',
			'role'        => $filters['role'],
			'risk_level'  => $filters['risk_level'],
			'school'      => $filters['school'],
			'date_from'   => $filters['date_from'],
			'date_to'     => $filters['date_to'],
		),
		admin_url( 'admin.php' )
	),
	'airb_export_csv'
);
?>
<div class="wrap">
	<h1><?php esc_html_e( 'AI Risk Benchmark — Submissions', 'ai-risk-benchmark' ); ?></h1>
	<p><?php esc_html_e( 'GDPR-friendly: only submissions where consent was given are stored. No student personal data is collected.', 'ai-risk-benchmark' ); ?></p>

	<form method="get" class="airb-admin-filters">
		<input type="hidden" name="page" value="airb-benchmark" />
		<select name="role">
			<option value=""><?php esc_html_e( 'All roles', 'ai-risk-benchmark' ); ?></option>
			<?php foreach ( $roles as $slug => $label ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $filters['role'], $slug ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<select name="risk_level">
			<option value=""><?php esc_html_e( 'All risk levels', 'ai-risk-benchmark' ); ?></option>
			<?php foreach ( array( 'low', 'moderate', 'high', 'critical' ) as $band ) : ?>
				<option value="<?php echo esc_attr( $band ); ?>" <?php selected( $filters['risk_level'], $band ); ?>><?php echo esc_html( ucfirst( $band ) ); ?></option>
			<?php endforeach; ?>
		</select>
		<input type="text" name="school" placeholder="<?php esc_attr_e( 'School name', 'ai-risk-benchmark' ); ?>" value="<?php echo esc_attr( $filters['school'] ); ?>" />
		<input type="date" name="date_from" value="<?php echo esc_attr( $filters['date_from'] ); ?>" />
		<input type="date" name="date_to" value="<?php echo esc_attr( $filters['date_to'] ); ?>" />
		<?php submit_button( __( 'Filter', 'ai-risk-benchmark' ), 'secondary', '', false ); ?>
		<a class="button" href="<?php echo esc_url( $export_url ); ?>"><?php esc_html_e( 'Export CSV', 'ai-risk-benchmark' ); ?></a>
	</form>

	<p><?php printf( esc_html__( '%d submission(s)', 'ai-risk-benchmark' ), (int) $total ); ?></p>

	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'ID', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Date', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Role', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'School', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Risk', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Alignment', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Oversight', 'ai-risk-benchmark' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $rows ) ) : ?>
				<tr><td colspan="7"><?php esc_html_e( 'No submissions yet.', 'ai-risk-benchmark' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<td><?php echo esc_html( (string) $row->id ); ?></td>
						<td><?php echo esc_html( (string) $row->created_at ); ?></td>
						<td><?php echo esc_html( $roles[ $row->role ] ?? $row->role ); ?></td>
						<td><?php echo esc_html( (string) $row->school_name ); ?></td>
						<td><?php echo esc_html( ucfirst( (string) $row->risk_level ) ); ?></td>
						<td><?php echo esc_html( (string) $row->alignment_score ); ?>/100</td>
						<td><?php echo esc_html( (string) $row->human_oversight_label ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>

	<?php
	$total_pages = (int) ceil( $total / $per_page );
	if ( $total_pages > 1 ) :
		$pagination = paginate_links(
			array(
				'base'      => add_query_arg( 'paged', '%#%' ),
				'format'    => '',
				'prev_text' => __( '&laquo;', 'ai-risk-benchmark' ),
				'next_text' => __( '&raquo;', 'ai-risk-benchmark' ),
				'total'     => $total_pages,
				'current'   => $paged,
			)
		);
		?>
		<?php if ( $pagination ) : ?>
			<div class="tablenav">
				<div class="tablenav-pages">
					<?php echo wp_kses_post( $pagination ); ?>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
