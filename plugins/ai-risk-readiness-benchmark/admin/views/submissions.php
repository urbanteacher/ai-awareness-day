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
 * @var bool                      $has_filters
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$paged = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$deleted_count = max( 0, (int) ( $_GET['deleted'] ?? 0 ) );
$delete_error  = ! empty( $_GET['delete_error'] );

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

	<?php if ( $deleted_count > 0 ) : ?>
		<div class="notice notice-success is-dismissible"><p>
			<?php
			printf(
				esc_html( _n( '%d submission deleted.', '%d submissions deleted.', $deleted_count, 'ai-risk-benchmark' ) ),
				$deleted_count
			);
			?>
		</p></div>
	<?php elseif ( $delete_error ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'No submissions were deleted. Check your selection or confirmation text and try again.', 'ai-risk-benchmark' ); ?></p></div>
	<?php endif; ?>

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
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;" onsubmit="return confirm('<?php echo esc_js( __( 'Delete this submission permanently?', 'ai-risk-benchmark' ) ); ?>');">
					<?php wp_nonce_field( 'airb_delete_submissions' ); ?>
					<input type="hidden" name="action" value="airb_delete_submissions" />
					<input type="hidden" name="airb_delete_action" value="single" />
					<input type="hidden" name="submission_id" value="<?php echo esc_attr( (string) $submission_detail->id ); ?>" />
					<?php submit_button( __( 'Delete submission', 'ai-risk-benchmark' ), 'delete', 'submit', false ); ?>
				</form>
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

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="airb-submissions-form">
		<?php wp_nonce_field( 'airb_delete_submissions' ); ?>
		<input type="hidden" name="action" value="airb_delete_submissions" />
		<input type="hidden" name="airb_delete_action" value="bulk" id="airb-delete-action" />
		<input type="hidden" name="role" value="<?php echo esc_attr( $filters['role'] ); ?>" />
		<input type="hidden" name="risk_level" value="<?php echo esc_attr( $filters['risk_level'] ); ?>" />
		<input type="hidden" name="school" value="<?php echo esc_attr( $filters['school'] ); ?>" />
		<input type="hidden" name="date_from" value="<?php echo esc_attr( $filters['date_from'] ); ?>" />
		<input type="hidden" name="date_to" value="<?php echo esc_attr( $filters['date_to'] ); ?>" />
		<input type="hidden" name="paged" value="<?php echo esc_attr( (string) $paged ); ?>" />

		<div class="tablenav top">
			<div class="alignleft actions bulkactions">
				<label for="airb-bulk-action" class="screen-reader-text"><?php esc_html_e( 'Bulk action', 'ai-risk-benchmark' ); ?></label>
				<select name="bulk_action" id="airb-bulk-action">
					<option value=""><?php esc_html_e( 'Bulk actions', 'ai-risk-benchmark' ); ?></option>
					<option value="delete"><?php esc_html_e( 'Delete', 'ai-risk-benchmark' ); ?></option>
				</select>
				<?php submit_button( __( 'Apply', 'ai-risk-benchmark' ), 'secondary', 'airb_bulk_apply', false ); ?>
			</div>
			<?php if ( $has_filters && $total > 0 ) : ?>
				<div class="alignleft actions" style="margin-left:8px;">
					<button type="submit" class="button button-secondary" onclick="document.getElementById('airb-delete-action').value='filtered'; return confirm('<?php echo esc_js( sprintf( __( 'Delete all %d submission(s) matching the current filters?', 'ai-risk-benchmark' ), (int) $total ) ); ?>');">
						<?php
						printf(
							esc_html__( 'Delete all matching (%d)', 'ai-risk-benchmark' ),
							(int) $total
						);
						?>
					</button>
				</div>
			<?php endif; ?>
		</div>
	</form>

	<table class="widefat striped">
		<thead>
			<tr>
				<td class="manage-column column-cb check-column">
					<input type="checkbox" id="airb-select-all" form="airb-submissions-form" />
				</td>
				<th><?php esc_html_e( 'ID', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Date', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Role', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'School', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Risk', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Alignment', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Oversight', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'ai-risk-benchmark' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $rows ) ) : ?>
				<tr><td colspan="9"><?php esc_html_e( 'No submissions yet.', 'ai-risk-benchmark' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<th scope="row" class="check-column">
							<input type="checkbox" form="airb-submissions-form" name="submission_ids[]" value="<?php echo esc_attr( (string) $row->id ); ?>" />
						</th>
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
						<td>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline;" onsubmit="return confirm('<?php echo esc_js( __( 'Delete this submission?', 'ai-risk-benchmark' ) ); ?>');">
								<?php wp_nonce_field( 'airb_delete_submissions' ); ?>
								<input type="hidden" name="action" value="airb_delete_submissions" />
								<input type="hidden" name="airb_delete_action" value="single" />
								<input type="hidden" name="submission_id" value="<?php echo esc_attr( (string) $row->id ); ?>" />
								<input type="hidden" name="role" value="<?php echo esc_attr( $filters['role'] ); ?>" />
								<input type="hidden" name="risk_level" value="<?php echo esc_attr( $filters['risk_level'] ); ?>" />
								<input type="hidden" name="school" value="<?php echo esc_attr( $filters['school'] ); ?>" />
								<input type="hidden" name="date_from" value="<?php echo esc_attr( $filters['date_from'] ); ?>" />
								<input type="hidden" name="date_to" value="<?php echo esc_attr( $filters['date_to'] ); ?>" />
								<input type="hidden" name="paged" value="<?php echo esc_attr( (string) $paged ); ?>" />
								<button type="submit" class="button-link-delete"><?php esc_html_e( 'Delete', 'ai-risk-benchmark' ); ?></button>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>

	<?php if ( ( $stats['total'] ?? 0 ) > 0 ) : ?>
		<div class="card" style="max-width:640px;padding:1rem 1.25rem;margin:1.5rem 0;border-left:4px solid #d63638;">
			<h2 style="margin-top:0;"><?php esc_html_e( 'Delete all submissions', 'ai-risk-benchmark' ); ?></h2>
			<p><?php esc_html_e( 'Permanently remove every benchmark submission. Linked leads and funnel events are kept, but their submission link is cleared.', 'ai-risk-benchmark' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'This cannot be undone. Delete every submission?', 'ai-risk-benchmark' ) ); ?>');">
				<?php wp_nonce_field( 'airb_delete_submissions' ); ?>
				<input type="hidden" name="action" value="airb_delete_submissions" />
				<input type="hidden" name="airb_delete_action" value="all" />
				<p>
					<label for="airb-delete-all-confirm">
						<?php esc_html_e( 'Type DELETE ALL to confirm:', 'ai-risk-benchmark' ); ?>
					</label><br />
					<input type="text" id="airb-delete-all-confirm" name="delete_all_confirm" class="regular-text" autocomplete="off" />
				</p>
				<?php submit_button( __( 'Delete all submissions', 'ai-risk-benchmark' ), 'delete', 'submit', false ); ?>
			</form>
		</div>
	<?php endif; ?>

	<script>
	(function () {
		var selectAll = document.getElementById('airb-select-all');
		var form = document.getElementById('airb-submissions-form');
		if (!selectAll || !form) {
			return;
		}
		selectAll.addEventListener('change', function () {
			document.querySelectorAll('input[name="submission_ids[]"][form="airb-submissions-form"]').forEach(function (box) {
				box.checked = selectAll.checked;
			});
		});
		var bulkApply = form.querySelector('#airb_bulk_apply');
		if (bulkApply) {
			bulkApply.addEventListener('click', function (event) {
				var action = document.getElementById('airb-bulk-action');
				if (!action || action.value !== 'delete') {
					return;
				}
				var checked = document.querySelectorAll('input[name="submission_ids[]"][form="airb-submissions-form"]:checked');
				if (!checked.length) {
					event.preventDefault();
					window.alert('<?php echo esc_js( __( 'Select at least one submission to delete.', 'ai-risk-benchmark' ) ); ?>');
					return;
				}
				if (!window.confirm('<?php echo esc_js( __( 'Delete the selected submissions?', 'ai-risk-benchmark' ) ); ?>')) {
					event.preventDefault();
				}
			});
		}
	})();
	</script>

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
