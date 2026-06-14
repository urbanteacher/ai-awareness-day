<?php
/**
 * Admin leads list.
 *
 * @package AIRB
 *
 * @var array<string, mixed>        $filters
 * @var array<int, object>          $rows
 * @var int                         $total
 * @var array<string, string>       $roles
 * @var array<string, int>          $status_counts
 * @var array<string, string>       $statuses
 * @var object|null                 $detail
 * @var object|null                 $submission
 * @var array<int, object>          $related_leads
 * @var array<string, int>          $source_counts
 * @var array<string, int>          $role_counts
 * @var array<string, int>          $interest_counts
 * @var array<int, array<string,mixed>> $top_hub_pages
 * @var array<int, array<string,mixed>> $school_leads
 * @var bool                        $school_view
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

$total_leads = array_sum( $status_counts );
?>
<div class="wrap">
	<h1><?php esc_html_e( 'AI Risk Benchmark — Leads', 'ai-risk-benchmark' ); ?></h1>
	<p><?php esc_html_e( 'Commercial follow-up from benchmark results and hub pages. Submissions = intelligence · Events = behaviour · Leads = follow-up.', 'ai-risk-benchmark' ); ?></p>

	<?php if ( ! empty( $_GET['updated'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Lead updated.', 'ai-risk-benchmark' ); ?></p></div>
	<?php endif; ?>

	<?php if ( ! $detail && ! $school_view ) : ?>
		<h2><?php esc_html_e( 'Lead source summary', 'ai-risk-benchmark' ); ?></h2>
		<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin:1rem 0;">
			<div class="card" style="padding:0.75rem 1rem;">
				<strong style="font-size:1.5rem;"><?php echo esc_html( (string) $total_leads ); ?></strong>
				<span><?php esc_html_e( 'Total leads', 'ai-risk-benchmark' ); ?></span>
			</div>
			<div class="card" style="padding:0.75rem 1rem;">
				<strong style="font-size:1.5rem;"><?php echo esc_html( (string) ( $source_counts['results'] ?? 0 ) ); ?></strong>
				<span><?php esc_html_e( 'From results page', 'ai-risk-benchmark' ); ?></span>
			</div>
			<div class="card" style="padding:0.75rem 1rem;">
				<strong style="font-size:1.5rem;"><?php echo esc_html( (string) ( $source_counts['hub'] ?? 0 ) ); ?></strong>
				<span><?php esc_html_e( 'From hub pages', 'ai-risk-benchmark' ); ?></span>
			</div>
		</div>

		<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1rem;margin:1rem 0 1.5rem;">
			<div class="card" style="padding:0.75rem 1rem;">
				<h3 style="margin-top:0;"><?php esc_html_e( 'By role', 'ai-risk-benchmark' ); ?></h3>
				<ul style="margin:0;padding-left:1.2rem;">
					<?php if ( empty( $role_counts ) ) : ?>
						<li><?php esc_html_e( 'No data yet.', 'ai-risk-benchmark' ); ?></li>
					<?php else : ?>
						<?php foreach ( $role_counts as $slug => $count ) : ?>
							<li><?php echo esc_html( ( $roles[ $slug ] ?? $slug ) . ' — ' . (int) $count ); ?></li>
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>
			</div>
			<div class="card" style="padding:0.75rem 1rem;">
				<h3 style="margin-top:0;"><?php esc_html_e( 'Top interests', 'ai-risk-benchmark' ); ?></h3>
				<ul style="margin:0;padding-left:1.2rem;">
					<?php if ( empty( $interest_counts ) ) : ?>
						<li><?php esc_html_e( 'No data yet.', 'ai-risk-benchmark' ); ?></li>
					<?php else : ?>
						<?php foreach ( array_slice( $interest_counts, 0, 8, true ) as $slug => $count ) : ?>
							<li><?php echo esc_html( str_replace( '_', ' ', $slug ) . ' — ' . (int) $count ); ?></li>
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>
			</div>
			<div class="card" style="padding:0.75rem 1rem;">
				<h3 style="margin-top:0;"><?php esc_html_e( 'Top hub pages', 'ai-risk-benchmark' ); ?></h3>
				<ul style="margin:0;padding-left:1.2rem;">
					<?php if ( empty( $top_hub_pages ) ) : ?>
						<li><?php esc_html_e( 'No hub leads yet.', 'ai-risk-benchmark' ); ?></li>
					<?php else : ?>
						<?php foreach ( $top_hub_pages as $hub_row ) : ?>
							<li>
								<?php echo esc_html( ( $hub_row['title'] ?: $hub_row['slug'] ) . ' — ' . (int) $hub_row['count'] ); ?>
							</li>
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>
			</div>
		</div>

		<h2><?php esc_html_e( 'Schools with leads', 'ai-risk-benchmark' ); ?></h2>
		<table class="widefat striped" style="max-width:640px;margin-bottom:1.5rem;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'School', 'ai-risk-benchmark' ); ?></th>
					<th><?php esc_html_e( 'Leads', 'ai-risk-benchmark' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $school_leads ) ) : ?>
					<tr><td colspan="3"><?php esc_html_e( 'No school-tagged leads yet.', 'ai-risk-benchmark' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $school_leads as $school_row ) : ?>
						<?php
						$school_url = add_query_arg(
							array(
								'page'   => 'airb-leads',
								'school' => $school_row['name'],
							),
							admin_url( 'admin.php' )
						);
						$dash_url = add_query_arg(
							array(
								'page'   => 'airb-school-dashboard',
								'school' => $school_row['name'],
							),
							admin_url( 'admin.php' )
						);
						?>
						<tr>
							<td><?php echo esc_html( $school_row['name'] ); ?></td>
							<td><?php echo esc_html( (string) $school_row['count'] ); ?></td>
							<td>
								<a href="<?php echo esc_url( $school_url ); ?>"><?php esc_html_e( 'View leads', 'ai-risk-benchmark' ); ?></a>
								·
								<a href="<?php echo esc_url( $dash_url ); ?>"><?php esc_html_e( 'School dashboard', 'ai-risk-benchmark' ); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<?php if ( ! empty( $filters['submission_id'] ) && ! $detail ) : ?>
		<div class="notice notice-info" style="margin:1rem 0;">
			<p>
				<?php
				printf(
					/* translators: %d: submission ID */
					esc_html__( 'Showing leads linked to submission #%d.', 'ai-risk-benchmark' ),
					(int) $filters['submission_id']
				);
				?>
				<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'airb-benchmark', 'submission_id' => (int) $filters['submission_id'] ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'View submission', 'ai-risk-benchmark' ); ?></a>
				·
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=airb-leads' ) ); ?>"><?php esc_html_e( 'Clear filter', 'ai-risk-benchmark' ); ?></a>
			</p>
		</div>
	<?php endif; ?>

	<?php if ( $school_view && ! $detail ) : ?>
		<div class="notice notice-info" style="margin:1rem 0;">
			<p>
				<?php
				printf(
					/* translators: %s: school name */
					esc_html__( 'Showing leads for: %s', 'ai-risk-benchmark' ),
					esc_html( $filters['school'] )
				);
				?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=airb-leads' ) ); ?>"><?php esc_html_e( 'Clear school filter', 'ai-risk-benchmark' ); ?></a>
				·
				<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'airb-school-dashboard', 'school' => $filters['school'] ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Open school dashboard', 'ai-risk-benchmark' ); ?></a>
			</p>
		</div>
	<?php endif; ?>

	<div class="airb-admin-stats" style="display:flex;flex-wrap:wrap;gap:0.75rem;margin:1rem 0;">
		<?php foreach ( $statuses as $slug => $label ) : ?>
			<?php
			$count = (int) ( $status_counts[ $slug ] ?? 0 );
			$url   = add_query_arg(
				array(
					'page'   => 'airb-leads',
					'status' => $slug,
					'school' => $filters['school'],
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
				<?php wp_nonce_field( 'airb_update_lead' ); ?>
				<input type="hidden" name="action" value="airb_update_lead" />
				<input type="hidden" name="lead_id" value="<?php echo esc_attr( (string) $detail->id ); ?>" />
				<p>
					<label for="airb-lead-status"><strong><?php esc_html_e( 'Status', 'ai-risk-benchmark' ); ?></strong></label><br />
					<select name="status" id="airb-lead-status">
						<?php foreach ( $statuses as $slug => $label ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $detail->status, $slug ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<p>
					<label for="airb-lead-notes"><strong><?php esc_html_e( 'Internal notes', 'ai-risk-benchmark' ); ?></strong></label><br />
					<textarea name="notes" id="airb-lead-notes" rows="4" class="large-text" placeholder="<?php esc_attr_e( 'Follow-up notes, call summary, next action…', 'ai-risk-benchmark' ); ?>"><?php echo esc_textarea( (string) ( $detail->notes ?? '' ) ); ?></textarea>
				</p>
				<?php submit_button( __( 'Save lead', 'ai-risk-benchmark' ), 'primary', 'submit', false ); ?>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=airb-leads' ) ); ?>"><?php esc_html_e( 'Back to list', 'ai-risk-benchmark' ); ?></a>
			</form>

			<?php if ( $submission instanceof stdClass ) : ?>
				<?php
				$submission_url = add_query_arg(
					array(
						'page'           => 'airb-benchmark',
						'submission_id'  => (int) $submission->id,
					),
					admin_url( 'admin.php' )
				);
				?>
				<div class="card" style="padding:0.75rem 1rem;margin:0 0 1rem;background:#f6f7f7;">
					<h3 style="margin-top:0;"><?php esc_html_e( 'Linked benchmark submission', 'ai-risk-benchmark' ); ?></h3>
					<p>
						<a href="<?php echo esc_url( $submission_url ); ?>"><strong><?php printf( esc_html__( 'Submission #%d', 'ai-risk-benchmark' ), (int) $submission->id ); ?></strong></a>
						· <?php echo esc_html( (string) $submission->created_at ); ?>
						· <?php echo esc_html( $roles[ $submission->role ] ?? $submission->role ); ?>
						· <?php echo esc_html( (string) $submission->alignment_score ); ?>/100
						· <?php echo esc_html( ucfirst( (string) $submission->risk_level ) ); ?>
					</p>
					<p class="description">
						<?php esc_html_e( 'School:', 'ai-risk-benchmark' ); ?> <?php echo esc_html( (string) $submission->school_name ?: '—' ); ?>
						· <?php esc_html_e( 'Email:', 'ai-risk-benchmark' ); ?> <?php echo esc_html( (string) $submission->email ?: '—' ); ?>
						· <?php esc_html_e( 'Consent:', 'ai-risk-benchmark' ); ?> <?php echo (int) $submission->consent ? esc_html__( 'Yes', 'ai-risk-benchmark' ) : esc_html__( 'No', 'ai-risk-benchmark' ); ?>
					</p>
				</div>
			<?php endif; ?>

			<table class="widefat striped" style="margin-top:0.5rem;">
				<tbody>
					<tr><th style="width:180px;"><?php esc_html_e( 'Date', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( (string) $detail->created_at ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Source', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( 'hub' === $detail->source ? __( 'Hub page', 'ai-risk-benchmark' ) : __( 'Results', 'ai-risk-benchmark' ) ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Role', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( $roles[ $detail->role ] ?? $detail->role ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Name', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( (string) $detail->name ?: '—' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Email', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( (string) $detail->email ?: '—' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'School', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( (string) $detail->school ?: '—' ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Child\'s school', 'ai-risk-benchmark' ); ?></th><td><?php echo esc_html( (string) $detail->child_school ?: '—' ); ?></td></tr>
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

			<?php if ( count( $related_leads ) > 1 ) : ?>
				<h3><?php esc_html_e( 'Other leads from same submission', 'ai-risk-benchmark' ); ?></h3>
				<ul>
					<?php foreach ( $related_leads as $rel ) : ?>
						<?php if ( (int) $rel->id === (int) $detail->id ) { continue; } ?>
						<li>
							<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'airb-leads', 'lead_id' => (int) $rel->id ), admin_url( 'admin.php' ) ) ); ?>">
								<?php printf( esc_html__( 'Lead #%d — %s', 'ai-risk-benchmark' ), (int) $rel->id, esc_html( (string) $rel->created_at ) ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
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
