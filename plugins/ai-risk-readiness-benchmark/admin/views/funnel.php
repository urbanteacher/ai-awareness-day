<?php
/**
 * Admin funnel metrics and event log.
 *
 * @package AIRB
 *
 * @var array<string, int>        $event_counts
 * @var array<string, int>        $role_counts
 * @var array<string, int>        $weak_domains
 * @var array<int, object>        $events
 * @var int                       $total_events
 * @var int                       $total_subs
 * @var array<string, string>     $event_labels
 * @var array<string, string>     $roles
 * @var array<string, string>     $domains
 * @var array<string, mixed>      $filters
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$total_pages = max( 1, (int) ceil( $total_events / max( 1, (int) $filters['limit'] ) ) );
?>
<div class="wrap">
	<h1><?php esc_html_e( 'AI Risk Benchmark — Funnel & events', 'ai-risk-benchmark' ); ?></h1>
	<p><?php esc_html_e( 'Track benchmark completions, post-results engagement, and weak domains across all submissions.', 'ai-risk-benchmark' ); ?></p>

	<div class="airb-funnel-summary" style="display:flex;flex-wrap:wrap;gap:1rem;margin:1.25rem 0;">
		<div class="card" style="min-width:160px;padding:1rem 1.25rem;">
			<strong style="display:block;font-size:1.75rem;line-height:1.2;"><?php echo esc_html( (string) $total_subs ); ?></strong>
			<span><?php esc_html_e( 'Stored submissions', 'ai-risk-benchmark' ); ?></span>
		</div>
		<div class="card" style="min-width:160px;padding:1rem 1.25rem;">
			<strong style="display:block;font-size:1.75rem;line-height:1.2;"><?php echo esc_html( (string) $total_events ); ?></strong>
			<span><?php esc_html_e( 'Journey events', 'ai-risk-benchmark' ); ?></span>
		</div>
	</div>

	<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;margin-bottom:2rem;">
		<div class="card" style="padding:1rem 1.25rem;">
			<h2 style="margin-top:0;font-size:1.1rem;"><?php esc_html_e( 'Submissions by role', 'ai-risk-benchmark' ); ?></h2>
			<?php if ( $role_counts ) : ?>
				<table class="widefat striped">
					<thead><tr><th><?php esc_html_e( 'Role', 'ai-risk-benchmark' ); ?></th><th><?php esc_html_e( 'Count', 'ai-risk-benchmark' ); ?></th></tr></thead>
					<tbody>
					<?php foreach ( $role_counts as $slug => $count ) : ?>
						<tr>
							<td><?php echo esc_html( $roles[ $slug ] ?? $slug ); ?></td>
							<td><?php echo esc_html( (string) $count ); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><em><?php esc_html_e( 'No submissions yet.', 'ai-risk-benchmark' ); ?></em></p>
			<?php endif; ?>
		</div>

		<div class="card" style="padding:1rem 1.25rem;">
			<h2 style="margin-top:0;font-size:1.1rem;"><?php esc_html_e( 'Event counts', 'ai-risk-benchmark' ); ?></h2>
			<?php if ( $event_counts ) : ?>
				<table class="widefat striped">
					<thead><tr><th><?php esc_html_e( 'Event', 'ai-risk-benchmark' ); ?></th><th><?php esc_html_e( 'Count', 'ai-risk-benchmark' ); ?></th></tr></thead>
					<tbody>
					<?php foreach ( $event_counts as $type => $count ) : ?>
						<tr>
							<td><?php echo esc_html( $event_labels[ $type ] ?? $type ); ?></td>
							<td><?php echo esc_html( (string) $count ); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><em><?php esc_html_e( 'No events recorded yet.', 'ai-risk-benchmark' ); ?></em></p>
			<?php endif; ?>
		</div>

		<div class="card" style="padding:1rem 1.25rem;">
			<h2 style="margin-top:0;font-size:1.1rem;"><?php esc_html_e( 'Weak domains (<70% readiness)', 'ai-risk-benchmark' ); ?></h2>
			<?php if ( $weak_domains ) : ?>
				<table class="widefat striped">
					<thead><tr><th><?php esc_html_e( 'Domain', 'ai-risk-benchmark' ); ?></th><th><?php esc_html_e( 'Occurrences', 'ai-risk-benchmark' ); ?></th></tr></thead>
					<tbody>
					<?php foreach ( $weak_domains as $slug => $count ) : ?>
						<tr>
							<td><?php echo esc_html( $domains[ $slug ] ?? $slug ); ?></td>
							<td><?php echo esc_html( (string) $count ); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><em><?php esc_html_e( 'No weak-domain data yet.', 'ai-risk-benchmark' ); ?></em></p>
			<?php endif; ?>
		</div>
	</div>

	<h2><?php esc_html_e( 'Recent events', 'ai-risk-benchmark' ); ?></h2>

	<form method="get" class="airb-admin-filters" style="margin-bottom:1rem;">
		<input type="hidden" name="page" value="airb-funnel" />
		<select name="event_type">
			<option value=""><?php esc_html_e( 'All event types', 'ai-risk-benchmark' ); ?></option>
			<?php foreach ( $event_labels as $slug => $label ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $filters['event_type'], $slug ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<select name="role">
			<option value=""><?php esc_html_e( 'All roles', 'ai-risk-benchmark' ); ?></option>
			<?php foreach ( $roles as $slug => $label ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $filters['role'], $slug ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<input type="date" name="date_from" value="<?php echo esc_attr( (string) $filters['date_from'] ); ?>" />
		<input type="date" name="date_to" value="<?php echo esc_attr( (string) $filters['date_to'] ); ?>" />
		<button type="submit" class="button"><?php esc_html_e( 'Filter', 'ai-risk-benchmark' ); ?></button>
	</form>

	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'When', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Event', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Role', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Session', 'ai-risk-benchmark' ); ?></th>
				<th><?php esc_html_e( 'Details', 'ai-risk-benchmark' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php if ( $events ) : ?>
			<?php foreach ( $events as $event ) : ?>
				<?php
				$meta = json_decode( (string) ( $event->metadata ?? '{}' ), true );
				if ( ! is_array( $meta ) ) {
					$meta = array();
				}
				$meta_preview = wp_json_encode( $meta );
				if ( is_string( $meta_preview ) && strlen( $meta_preview ) > 120 ) {
					$meta_preview = substr( $meta_preview, 0, 117 ) . '…';
				}
				?>
				<tr>
					<td><?php echo esc_html( (string) $event->created_at ); ?></td>
					<td><?php echo esc_html( $event_labels[ (string) $event->event_type ] ?? (string) $event->event_type ); ?></td>
					<td><?php echo esc_html( $roles[ (string) $event->role ] ?? (string) $event->role ); ?></td>
					<td><code><?php echo esc_html( substr( (string) $event->session_id, 0, 12 ) ); ?>…</code></td>
					<td><code><?php echo esc_html( (string) $meta_preview ); ?></code></td>
				</tr>
			<?php endforeach; ?>
		<?php else : ?>
			<tr><td colspan="5"><em><?php esc_html_e( 'No events match these filters.', 'ai-risk-benchmark' ); ?></em></td></tr>
		<?php endif; ?>
		</tbody>
	</table>

	<?php if ( $total_pages > 1 ) : ?>
		<div class="tablenav bottom">
			<div class="tablenav-pages">
				<?php
				echo wp_kses_post(
					paginate_links(
						array(
							'base'    => add_query_arg( 'paged', '%#%' ),
							'format'  => '',
							'current' => max( 1, (int) ( $_GET['paged'] ?? 1 ) ),
							'total'   => $total_pages,
						)
					)
				);
				?>
			</div>
		</div>
	<?php endif; ?>
</div>
