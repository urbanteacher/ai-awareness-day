<?php
/**
 * Admin submissions table.
 *
 * @package AIRB
 *
 * @var array<string, mixed>      $filters
 * @var array<int, object>        $rows
 * @var int                       $total
 * @var array<string, string>     $roles
 * @var array<string, int>        $stats
 * @var object|null               $submission_detail
 * @var array<int, object>        $submission_leads
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
	<p><?php esc_html_e( 'Every completed benchmark is stored with scores and role. School name and email are saved when provided.', 'ai-risk-benchmark' ); ?></p>

	<?php if ( $submission_detail instanceof stdClass ) : ?>
		<div class="card" style="max-width:960px;padding:1rem 1.25rem;margin:1rem 0;">
			<h2 style="margin-top:0;"><?php printf( esc_html__( 'Submission #%d', 'ai-risk-benchmark' ), (int) $submission_detail->id ); ?></h2>
			<p>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=airb-benchmark' ) ); ?>"><?php esc_html_e( 'Back to all submissions', 'ai-risk-benchmark' ); ?></a>
				<?php if ( ! empty( $submission_leads ) ) : ?>
					<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => 'airb-leads', 'submission_id' => (int) $submission_detail->id ), admin_url( 'admin.php' ) ) ); ?>">
						<?php printf( esc_html__( 'View %d linked lead(s)', 'ai-risk-benchmark' ), count( $submission_leads ) ); ?>
					</a>
				<?php endif; ?>
			</p>
			<table class="widefat striped">
				<tbody>
					<tr><th style="width:180px;"><?php esc_html_e( 'Date', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( (string) $submission_detail->created_at ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Role', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( $roles[ $submission_detail->role ] ?? $submission_detail->role ); ?></td></tr>
					<tr><th><?php esc_html_e( 'School', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( (string) $submission_detail->school_name ?: '—' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Email', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( (string) $submission_detail->email ?: '—' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Alignment', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( (string) $submission_detail->alignment_score ); ?>/100</td></tr>
					<tr><th><?php esc_html_e( 'Risk', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( ucfirst( (string) $submission_detail->risk_level ) ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Dependency', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( (string) $submission_detail->dependency_index ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Human oversight', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( (string) $submission_detail->human_oversight_label ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Benchmark consent', 'ai-risk-benchmark' ); ?></th><td><?php echo (int) $submission_detail->consent ? esc_html__( 'Yes', 'ai-risk-benchmark' ) : esc_html__( 'No', 'ai-risk-benchmark' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Contact opt-in', 'ai-risk-benchmark' ); ?></th><td><?php echo (int) $submission_detail->contact_opt_in ? esc_html__( 'Yes', 'ai-risk-benchmark' ) : esc_html__( 'No', 'ai-risk-benchmark' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Session ID', 'ai-risk-benchmark' ); ?></th><td><code><?php echo esc_html( (string) $submission_detail->session_id ); ?></code></td></tr>
				</tbody>
			</table>
			<?php if ( ! empty( $submission_leads ) ) : ?>
				<h3><?php esc_html_e( 'Linked leads', 'ai-risk-benchmark' ); ?></h3>
				<ul>
					<?php foreach ( $submission_leads as $lead_row ) : ?>
						<li>
							<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'airb-leads', 'lead_id' => (int) $lead_row->id ), admin_url( 'admin.php' ) ) ); ?>">
								<?php
								printf(
									esc_html__( 'Lead #%1$d — %2$s — %3$s', 'ai-risk-benchmark' ),
									(int) $lead_row->id,
									esc_html( (string) $lead_row->created_at ),
									esc_html( (string) $lead_row->email )
								);
								?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="airb-admin-stats" style="display:flex;flex-wrap:wrap;gap:1rem;margin:1rem 0;">
		<div class="card" style="padding:0.75rem 1rem;">
			<strong style="font-size:1.4rem;"><?php echo esc_html( (string) ( $stats['total'] ?? 0 ) ); ?></strong>
			<span><?php esc_html_e( 'Total completions', 'ai-risk-benchmark' ); ?></span>
		</div>
		<div class="card" style="padding:0.75rem 1rem;">
			<strong style="font-size:1.4rem;"><?php echo esc_html( (string) ( $stats['with_school'] ?? 0 ) ); ?></strong>
			<span><?php esc_html_e( 'With school name', 'ai-risk-benchmark' ); ?></span>
		</div>
		<div class="card" style="padding:0.75rem 1rem;">
			<strong style="font-size:1.4rem;"><?php echo esc_html( (string) ( $stats['benchmark_consent'] ?? 0 ) ); ?></strong>
			<span><?php esc_html_e( 'Benchmark consent', 'ai-risk-benchmark' ); ?></span>
		</div>
		<div class="card" style="padding:0.75rem 1rem;">
			<strong style="font-size:1.4rem;"><?php echo esc_html( (string) ( $stats['contact_opt_ins'] ?? 0 ) ); ?></strong>
			<span><?php esc_html_e( 'Contact opt-ins', 'ai-risk-benchmark' ); ?></span>
		</div>
	</div>

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
		<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=airb-funnel' ) ); ?>"><?php esc_html_e( 'Funnel & events', 'ai-risk-benchmark' ); ?></a>
		<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=airb-leads' ) ); ?>"><?php esc_html_e( 'Leads', 'ai-risk-benchmark' ); ?></a>
	</form>

	<p><?php printf( esc_html__( '%d submission(s) matching filters', 'ai-risk-benchmark' ), (int) $total ); ?></p>

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
						<td>
							<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'airb-benchmark', 'submission_id' => (int) $row->id ), admin_url( 'admin.php' ) ) ); ?>">
								<?php echo esc_html( (string) $row->id ); ?>
							</a>
						</td>
						<td><?php echo esc_html( (string) $row->created_at ); ?></td>
						<td><?php echo esc_html( $roles[ $row->role ] ?? $row->role ); ?></td>
						<td><?php echo esc_html( (string) $row->school_name ?: '—' ); ?></td>
						<td><?php echo esc_html( ucfirst( (string) $row->risk_level ) ); ?></td>
						<td><?php echo esc_html( (string) $row->alignment_score ); ?>/100</td>
						<td><?php echo esc_html( (string) $row->human_oversight_label ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>

	<?php
	$per_page    = (int) ( $filters['limit'] ?? 50 );
	$total_pages = (int) ceil( $total / max( 1, $per_page ) );
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
