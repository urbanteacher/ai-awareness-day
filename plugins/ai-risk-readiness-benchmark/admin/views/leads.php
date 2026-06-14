<?php
/**
 * Admin leads list.
 *
 * @package AIRB
 *
 * @var array<string, mixed>   $filters
 * @var array<int, object>     $rows
 * @var int                    $total
 * @var array<string, string>  $roles
 * @var array<string, int>     $status_counts
 * @var array<string, string>  $statuses
 * @var object|null            $detail
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$detail_id = (int) ( $_GET['lead_id'] ?? 0 );
$export_url = wp_nonce_url(
	add_query_arg(
		array(
			'airb_export' => 'leads_csv',
			'status'      => $filters['status'],
			'source'      => $filters['source'],
			'role'        => $filters['role'],
			'school'      => $filters['school'],
			'date_from'   => $filters['date_from'],
			'date_to'     => $filters['date_to'],
		),
		admin_url( 'admin.php' )
	),
	'airb_export_leads_csv'
);
?>
<div class="wrap">
	<h1><?php esc_html_e( 'AI Risk Benchmark — Leads', 'ai-risk-benchmark' ); ?></h1>
	<p><?php esc_html_e( 'Interest and support requests from benchmark results and hub resource pages. Full form payloads are stored here — not just email.', 'ai-risk-benchmark' ); ?></p>

	<div class="airb-admin-stats" style="display:flex;flex-wrap:wrap;gap:0.75rem;margin:1rem 0;">
		<?php foreach ( $statuses as $slug => $label ) : ?>
			<?php
			$count = (int) ( $status_counts[ $slug ] ?? 0 );
			$url   = add_query_arg(
				array(
					'page'   => 'airb-leads',
					'status' => $slug,
				),
				admin_url( 'admin.php' )
			);
			?>
			<a href="<?php echo esc_url( $url ); ?>" class="card" style="padding:0.6rem 0.9rem;text-decoration:none;<?php echo $filters['status'] === $slug ? 'border:2px solid #2271b1;' : ''; ?>">
				<strong style="font-size:1.25rem;display:block;"><?php echo esc_html( (string) $count ); ?></strong>
				<span><?php echo esc_html( $label ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>

	<?php if ( $detail instanceof stdClass ) : ?>
		<div class="card" style="max-width:960px;padding:1rem 1.25rem;margin:1rem 0;">
			<h2 style="margin-top:0;"><?php printf( esc_html__( 'Lead #%d', 'ai-risk-benchmark' ), (int) $detail->id ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-bottom:1rem;">
				<?php wp_nonce_field( 'airb_update_lead_status' ); ?>
				<input type="hidden" name="action" value="airb_update_lead_status" />
				<input type="hidden" name="lead_id" value="<?php echo esc_attr( (string) $detail->id ); ?>" />
				<label for="airb-lead-status"><strong><?php esc_html_e( 'Status', 'ai-risk-benchmark' ); ?></strong></label>
				<select name="status" id="airb-lead-status">
					<?php foreach ( $statuses as $slug => $label ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $detail->status, $slug ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php submit_button( __( 'Update status', 'ai-risk-benchmark' ), 'primary', 'submit', false ); ?>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=airb-leads' ) ); ?>"><?php esc_html_e( 'Back to list', 'ai-risk-benchmark' ); ?></a>
			</form>

			<table class="widefat striped" style="margin-top:0.5rem;">
				<tbody>
					<tr><th style="width:180px;"><?php esc_html_e( 'Date', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( (string) $detail->created_at ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Source', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( 'hub' === $detail->source ? __( 'Hub page', 'ai-risk-benchmark' ) : __( 'Results', 'ai-risk-benchmark' ) ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Role', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( $roles[ $detail->role ] ?? $detail->role ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Name', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( (string) $detail->name ?: '—' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Email', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( (string) $detail->email ?: '—' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'School', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( (string) $detail->school ?: '—' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Child\'s school', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( (string) $detail->child_school ?: '—' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Submission ID', 'ai-risk-benchmark' ); ?></th><td><?php echo (int) $detail->submission_id ? esc_html( (string) $detail->submission_id ) : '—'; ?></td></tr>
					<tr><th><?php esc_html_e( 'Readiness', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( (string) $detail->alignment_score ); ?>/100 <?php echo esc_html( (string) $detail->readiness_level_label ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Risk', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( ucfirst( (string) $detail->risk_level ) ); ?> <?php echo esc_html( (string) $detail->risk_level_label ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Interests', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( implode( ', ', AIRB_Leads::decode_list( (string) $detail->interests ) ) ?: '—' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Weak domains', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( implode( ', ', AIRB_Leads::decode_list( (string) $detail->weak_domains ) ) ?: '—' ); ?></td></tr>
					<?php if ( 'hub' === $detail->source ) : ?>
						<tr><th><?php esc_html_e( 'Hub page', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( (string) $detail->hub_title ?: $detail->hub_page ); ?></td></tr>
						<tr><th><?php esc_html_e( 'Benchmark ref', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( (string) $detail->hub_ref ?: '—' ); ?></td></tr>
						<tr><th><?php esc_html_e( 'Page URL', 'ai-risk-benchmark' ); ?></th><td><a href="<?php echo esc_url( (string) $detail->hub_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( (string) $detail->hub_url ); ?></a></td></tr>
						<?php if ( (int) $detail->checklist_total > 0 ) : ?>
							<tr><th><?php esc_html_e( 'Checklist', 'ai-risk-benchmark' ); ?></th><td><?php printf( esc_html__( '%1$d / %2$d complete', 'ai-risk-benchmark' ), (int) $detail->checklist_done, (int) $detail->checklist_total ); ?></td></tr>
						<?php endif; ?>
					<?php endif; ?>
					<tr><th><?php esc_html_e( 'Message', 'ai-risk-benchmark' ); ?></th><td><?php echo nl2br( esc_html( (string) $detail->message ?: '—' ) ); ?></td></tr>
				</tbody>
			</table>
		</div>
	<?php endif; ?>

	<form method="get" class="airb-admin-filters">
		<input type="hidden" name="page" value="airb-leads" />
		<select name="status">
			<option value=""><?php esc_html_e( 'All statuses', 'ai-risk-benchmark' ); ?></option>
			<?php foreach ( $statuses as $slug => $label ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $filters['status'], $slug ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<select name="source">
			<option value=""><?php esc_html_e( 'All sources', 'ai-risk-benchmark' ); ?></option>
			<option value="results" <?php selected( $filters['source'], 'results' ); ?>><?php esc_html_e( 'Results', 'ai-risk-benchmark' ); ?></option>
			<option value="hub" <?php selected( $filters['source'], 'hub' ); ?>><?php esc_html_e( 'Hub page', 'ai-risk-benchmark' ); ?></option>
		</select>
		<select name="role">
			<option value=""><?php esc_html_e( 'All roles', 'ai-risk-benchmark' ); ?></option>
			<?php foreach ( $roles as $slug => $label ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $filters['role'], $slug ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<input type="text" name="school" placeholder="<?php esc_attr_e( 'School name', 'ai-risk-benchmark' ); ?>" value="<?php echo esc_attr( $filters['school'] ); ?>" />
		<input type="date" name="date_from" value="<?php echo esc_attr( $filters['date_from'] ); ?>" />
		<input type="date" name="date_to" value="<?php echo esc_attr( $filters['date_to'] ); ?>" />
		<?php submit_button( __( 'Filter', 'ai-risk-benchmark' ), 'secondary', '', false ); ?>
		<a class="button" href="<?php echo esc_url( $export_url ); ?>"><?php esc_html_e( 'Export CSV', 'ai-risk-benchmark' ); ?></a>
	</form>

	<p><?php printf( esc_html__( '%d lead(s) matching filters', 'ai-risk-benchmark' ), (int) $total ); ?></p>

	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'ID', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Date', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Status', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Source', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Role', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Contact', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'School', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Interests', 'ai-risk-benchmark' ); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $rows ) ) : ?>
				<tr><td colspan="9"><?php esc_html_e( 'No leads yet.', 'ai-risk-benchmark' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $rows as $row ) : ?>
					<?php
					$interests = AIRB_Leads::decode_list( (string) $row->interests );
					$view_url  = add_query_arg(
						array(
							'page'    => 'airb-leads',
							'lead_id' => (int) $row->id,
						),
						admin_url( 'admin.php' )
					);
					?>
					<tr>
						<td><?php echo esc_html( (string) $row->id ); ?></td>
						<td><?php echo esc_html( (string) $row->created_at ); ?></td>
						<td><?php echo esc_html( $statuses[ $row->status ] ?? $row->status ); ?></td>
						<td><?php echo esc_html( 'hub' === $row->source ? __( 'Hub', 'ai-risk-benchmark' ) : __( 'Results', 'ai-risk-benchmark' ) ); ?></td>
						<td><?php echo esc_html( $roles[ $row->role ] ?? $row->role ); ?></td>
						<td>
							<?php if ( $row->name ) : ?>
								<strong><?php echo esc_html( (string) $row->name ); ?></strong><br />
							<?php endif; ?>
							<?php echo esc_html( (string) $row->email ?: '—' ); ?>
						</td>
						<td><?php echo esc_html( (string) $row->school ?: ( $row->child_school ?: '—' ) ); ?></td>
						<td><?php echo esc_html( implode( ', ', array_slice( $interests, 0, 2 ) ) . ( count( $interests ) > 2 ? '…' : '' ) ); ?></td>
						<td><a href="<?php echo esc_url( $view_url ); ?>"><?php esc_html_e( 'View', 'ai-risk-benchmark' ); ?></a></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>

	<?php
	$per_page    = (int) ( $filters['limit'] ?? 50 );
	$paged       = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
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
