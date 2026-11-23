<?php
/**
 * National Survey: Admin: the survey analytics page.
 *
 * Part of the survey module (modules/survey.php).
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Admin: analytics page
// ---------------------------------------------------------------------------

function aiad_survey_register_analytics_page(): void {
	add_submenu_page(
		'edit.php?post_type=survey_response',
		__( 'Survey Analytics', 'aiad-core' ),
		__( '📊 Analytics', 'aiad-core' ),
		'edit_posts',
		'aiad-survey-analytics',
		'aiad_survey_analytics_page'
	);
}
add_action( 'admin_menu', 'aiad_survey_register_analytics_page' );

function aiad_survey_analytics_page(): void {
	global $wpdb;

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Permission denied.', 'aiad-core' ) );
	}

	// Fetch all meta in one query
	$post_ids = $wpdb->get_col(
		"SELECT ID FROM {$wpdb->posts} WHERE post_type = 'survey_response' AND post_status = 'publish' ORDER BY post_date ASC"
	);
	$total = count( $post_ids );

	if ( ! $total ) {
		echo '<div class="wrap"><h1>' . esc_html__( 'Survey Analytics', 'aiad-core' ) . '</h1>';
		echo '<p>' . esc_html__( 'No responses yet.', 'aiad-core' ) . '</p></div>';
		return;
	}

	// Build aggregated data
	$roles       = array();
	$school_types = array();
	$year_groups_tally = array();
	$participated_yes = 0;
	$participated_no  = 0;
	$display_board = array();
	$ai_policy     = array();
	$curriculum_embedded = array();
	$scales      = array();
	$hopes       = array();
	$lasting     = array();
	$prep        = array();
	$materials   = array();
	$useful_formats = array();
	$best_formats   = array();
	$bottlenecks = array();
	$support     = array();
	$non_reasons = array();
	$staffroom_attitudes = array();
	$comms       = array();
	$ratings_sum = array_fill_keys( array(
		'rating_student_empowerment', 'rating_critical_skepticism', 'rating_inclusivity',
		'rating_plug_and_play', 'rating_student_access', 'rating_tech_delivery',
	), array( 'sum' => 0, 'count' => 0 ) );
	$recent_feedback = array();

	foreach ( $post_ids as $pid ) {
		$meta = get_post_meta( (int) $pid );
		$get  = function( string $key ) use ( $meta ): string {
			return isset( $meta[ $key ][0] ) ? (string) $meta[ $key ][0] : '';
		};

		// Roles
		$role = $get( '_survey_role' );
		if ( $role ) $roles[ $role ] = ( $roles[ $role ] ?? 0 ) + 1;

		// School types
		$st = $get( '_survey_school_type' );
		if ( $st ) $school_types[ $st ] = ( $school_types[ $st ] ?? 0 ) + 1;

		// Year groups
		$yg_raw = $get( '_survey_year_groups' );
		$yg_arr = $yg_raw ? json_decode( $yg_raw, true ) : array();
		if ( is_array( $yg_arr ) ) {
			foreach ( $yg_arr as $yg ) {
				$year_groups_tally[ $yg ] = ( $year_groups_tally[ $yg ] ?? 0 ) + 1;
			}
		}

		// Participation
		$part = $get( '_survey_participated' );
		if ( $part === 'yes' ) $participated_yes++;
		elseif ( $part === 'no' ) $participated_no++;

		$db = $get( '_survey_display_board' );
		if ( $db ) $display_board[ $db ] = ( $display_board[ $db ] ?? 0 ) + 1;

		$ap = $get( '_survey_ai_policy' );
		if ( $ap ) $ai_policy[ $ap ] = ( $ai_policy[ $ap ] ?? 0 ) + 1;

		$ce = $get( '_survey_curriculum_embedded' );
		if ( $ce ) $curriculum_embedded[ $ce ] = ( $curriculum_embedded[ $ce ] ?? 0 ) + 1;

		$scale = $get( '_survey_participation_scale' );
		if ( $scale ) $scales[ $scale ] = ( $scales[ $scale ] ?? 0 ) + 1;

		$hope = $get( '_survey_primary_hope' );
		if ( $hope ) $hopes[ $hope ] = ( $hopes[ $hope ] ?? 0 ) + 1;

		$le = $get( '_survey_lasting_effect' );
		if ( $le ) $lasting[ $le ] = ( $lasting[ $le ] ?? 0 ) + 1;

		$pt = $get( '_survey_prep_time' );
		if ( $pt ) $prep[ $pt ] = ( $prep[ $pt ] ?? 0 ) + 1;

		$mq = $get( '_survey_materials_quality' );
		if ( $mq ) $materials[ $mq ] = ( $materials[ $mq ] ?? 0 ) + 1;

		$uf_raw = $get( '_survey_useful_formats' );
		$uf_arr = $uf_raw ? json_decode( $uf_raw, true ) : array();
		if ( is_array( $uf_arr ) ) {
			foreach ( $uf_arr as $uf ) {
				$useful_formats[ $uf ] = ( $useful_formats[ $uf ] ?? 0 ) + 1;
			}
		}

		$bf = $get( '_survey_best_format' );
		if ( $bf ) $best_formats[ $bf ] = ( $best_formats[ $bf ] ?? 0 ) + 1;

		$bn = $get( '_survey_bottleneck' );
		if ( $bn ) $bottlenecks[ $bn ] = ( $bottlenecks[ $bn ] ?? 0 ) + 1;

		$sm_raw = $get( '_survey_support_modules' );
		$sm_arr = $sm_raw ? json_decode( $sm_raw, true ) : array();
		if ( is_array( $sm_arr ) ) {
			foreach ( $sm_arr as $sm ) {
				$support[ $sm ] = ( $support[ $sm ] ?? 0 ) + 1;
			}
		}

		$nr = $get( '_survey_non_part_reason' );
		if ( $nr ) $non_reasons[ $nr ] = ( $non_reasons[ $nr ] ?? 0 ) + 1;

		$sa = $get( '_survey_staffroom_attitude' );
		if ( $sa ) $staffroom_attitudes[ $sa ] = ( $staffroom_attitudes[ $sa ] ?? 0 ) + 1;

		$comms_raw = $get( '_survey_comms_preference' );
		$comms_arr = $comms_raw ? json_decode( $comms_raw, true ) : array();
		if ( is_array( $comms_arr ) ) {
			foreach ( $comms_arr as $c ) {
				$comms[ $c ] = ( $comms[ $c ] ?? 0 ) + 1;
			}
		}

		// Ratings
		foreach ( array_keys( $ratings_sum ) as $rk ) {
			$rv = (int) $get( '_survey_' . $rk );
			if ( $rv >= 1 && $rv <= 5 ) {
				$ratings_sum[ $rk ]['sum']   += $rv;
				$ratings_sum[ $rk ]['count'] += 1;
			}
		}

		// Recent feedback (last 5)
		$fb = $get( '_survey_open_feedback' );
		if ( $fb && count( $recent_feedback ) < 5 ) {
			$recent_feedback[] = array(
				'text'   => $fb,
				'school' => $get( '_survey_school_name' ),
				'role'   => $get( '_survey_role' ),
			);
		}
	}

	arsort( $roles );
	arsort( $bottlenecks );
	arsort( $support );

	// Helper: render a horizontal bar chart
	$bar = function( array $data, array $labels, int $total_n ) use ( $total ): string {
		if ( ! $data ) return '<p style="color:#888">No data yet.</p>';
		$out = '<div style="display:grid;gap:6px;margin-top:8px;">';
		foreach ( $data as $key => $count ) {
			$label = $labels[ $key ] ?? $key;
			$pct   = $total_n > 0 ? round( ( $count / $total_n ) * 100 ) : 0;
			$out  .= '<div>';
			$out  .= '<div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:2px;"><span>' . esc_html( $label ) . '</span><span style="color:#888;">' . esc_html( $count ) . ' (' . esc_html( $pct ) . '%)</span></div>';
			$out  .= '<div style="background:#e5e7eb;border-radius:4px;height:14px;overflow:hidden;"><div style="background:#0070c0;height:100%;width:' . esc_attr( $pct ) . '%;border-radius:4px;transition:width .4s;"></div></div>';
			$out  .= '</div>';
		}
		return $out . '</div>';
	};

	$role_labels = array(
		'teacher_primary'   => 'Classroom Teacher (Primary)',
		'teacher_secondary' => 'Classroom Teacher (Secondary)',
		'computing_lead'    => 'Computing Lead / HoD',
		'headteacher'       => 'Headteacher / School Leader',
		'slt_mat'           => 'SLT / Digital Lead',
		'alt_provision'     => 'Alt Provision / SEN',
	);

	$school_type_labels = array(
		'primary'    => 'Primary',
		'secondary'  => 'Secondary',
		'all_through' => 'All-through',
		'special'    => 'Special / Alt Provision',
		'fe_college' => 'FE College / 6th Form',
		'higher_education' => 'Higher Education',
		'mat_trust'  => 'MAT / Trust',
	);

	$yg_labels = array(
		'eyfs' => 'EYFS', 'ks1' => 'KS1', 'ks2' => 'KS2',
		'ks3' => 'KS3', 'ks4' => 'KS4', 'ks5' => 'KS5 / 6th Form', 'staff' => 'Staff CPD',
	);

	$rating_labels = array(
		'rating_student_empowerment' => 'Student Empowerment',
		'rating_critical_skepticism' => 'Critical Scepticism',
		'rating_inclusivity'         => 'Inclusivity & Access',
		'rating_plug_and_play'       => 'Plug & Play Usability',
		'rating_student_access'      => 'Student Accessibility',
		'rating_tech_delivery'       => 'Technical Delivery',
	);

	$hope_labels = array(
		'demystify'        => 'Demystify AI / reduce anxiety',
		'confidence_skills' => 'Confidence & practical skills',
		'digital_resilience' => 'Digital resilience / fact-checking',
		'career_interest'  => 'Career interest / CS spark',
	);

	$bottleneck_labels = array(
		'time'           => 'Time constraints',
		'tech_barriers'  => 'Tech / firewall barriers',
		'staff_hesitation' => 'Staff hesitation',
		'physical_assets' => 'Lack of physical assets',
	);

	$support_labels = array(
		'display_kits'    => 'Display Board Kits',
		'cross_curricular' => 'Cross-Curricular Schemes of Work',
		'cpd_pathways'    => 'Staff CPD Pathways',
		'pta_packs'       => 'PTA Interactive Packs',
	);

	$maturity_labels = array(
		'yes'            => 'Yes',
		'in_development' => 'In development',
		'no'             => 'No',
	);

	$materials_labels = array(
		'ideal'      => 'Ideal — exceeded needs',
		'adequate'   => 'Adequate',
		'basic'      => 'Basic — had to supplement',
		'inadequate' => 'Inadequate',
	);

	$useful_format_labels = array(
		'starter_5'   => '5-min lesson starter',
		'tutor_15'    => '15-min tutor session',
		'assembly_20' => '20-min assembly slides',
		'none'        => 'None fitted timetable',
	);

	$best_format_labels = array(
		'video'                => 'Video (ready to play)',
		'slides'               => 'Presentation slides',
		'teacher_instructions' => 'Teacher breakdown / instructions',
		'mix'                  => 'A mix',
	);

	$comms_labels = array(
		'website_timeline' => 'Website timeline',
		'linkedin'         => 'LinkedIn',
		'newsletter'       => 'Email newsletter',
	);

	// Card CSS helper
	$card = '<div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:20px 24px;margin-bottom:20px;">';
	?>
	<div class="wrap" style="max-width:1100px;">
		<h1 style="margin-bottom:6px;">📊 <?php esc_html_e( 'National Survey 2026 — Analytics', 'aiad-core' ); ?></h1>
		<p style="color:#6b7280;margin-bottom:24px;">
			<?php echo esc_html( sprintf( __( '%d total responses', 'aiad-core' ), $total ) ); ?>
			&nbsp;·&nbsp;
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=survey_response' ) ); ?>"><?php esc_html_e( 'View all responses', 'aiad-core' ); ?></a>
			&nbsp;·&nbsp;
			<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'aiad_export_survey_csv', '_wpnonce' => wp_create_nonce( 'aiad_export_survey_csv' ) ), admin_url( 'admin-post.php' ) ) ); ?>"><?php esc_html_e( 'Export CSV', 'aiad-core' ); ?></a>
		</p>

		<!-- Top stats -->
		<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px;margin-bottom:24px;">
			<?php
			$stats = array(
				array( 'label' => __( 'Total responses', 'aiad-core' ), 'value' => $total, 'color' => '#0070c0' ),
				array( 'label' => __( 'Participants', 'aiad-core' ), 'value' => $participated_yes, 'color' => '#059669' ),
				array( 'label' => __( 'Non-participants', 'aiad-core' ), 'value' => $participated_no, 'color' => '#dc2626' ),
				array( 'label' => __( 'Schools named', 'aiad-core' ), 'value' => count( array_filter( array_map( fn( $pid ) => get_post_meta( (int) $pid, '_survey_school_name', true ), $post_ids ) ) ), 'color' => '#6441B8' ),
			);
			foreach ( $stats as $s ) :
				?>
				<div style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px 20px;text-align:center;">
					<div style="font-size:2rem;font-weight:700;color:<?php echo esc_attr( $s['color'] ); ?>;"><?php echo esc_html( $s['value'] ); ?></div>
					<div style="font-size:12px;color:#6b7280;margin-top:4px;"><?php echo esc_html( $s['label'] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>

		<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

		<!-- Roles -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Respondents by role', 'aiad-core' ); ?>
			</h3>
			<?php echo $bar( $roles, $role_labels, $total ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- School types -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'School type breakdown', 'aiad-core' ); ?>
			</h3>
			<?php echo $bar( $school_types, $school_type_labels, $total ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- Year groups -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Year groups engaged', 'aiad-core' ); ?>
			</h3>
			<?php
			arsort( $year_groups_tally );
			echo $bar( $year_groups_tally, $yg_labels, $total ); // phpcs:ignore WordPress.Security.EscapeOutput
			?>
		</div>

		<!-- AI in curriculum -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'AI activities embedded in curriculum', 'aiad-core' ); ?>
			</h3>
			<?php echo $bar( $curriculum_embedded, $maturity_labels, $total ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- AI Awareness display board -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Has an AI Awareness display board', 'aiad-core' ); ?>
			</h3>
			<?php echo $bar( $display_board, $maturity_labels, $total ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- AI Awareness policy -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Has an AI Awareness / AI-use policy', 'aiad-core' ); ?>
			</h3>
			<?php echo $bar( $ai_policy, $maturity_labels, $total ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- Participation scale -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Participation scale (participants)', 'aiad-core' ); ?>
			</h3>
			<?php
			echo $bar( $scales, array( // phpcs:ignore WordPress.Security.EscapeOutput
				'single_classroom' => 'Single classroom (< 30)',
				'year_group'       => 'Year-group cohort (30–120)',
				'whole_school'     => 'Whole school (120+)',
			), $participated_yes );
			?>
		</div>

		<!-- Primary hopes -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Primary hope (Q1)', 'aiad-core' ); ?>
			</h3>
			<?php echo $bar( $hopes, $hope_labels, $participated_yes ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- Lasting effect -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Lasting classroom effect (Q3)', 'aiad-core' ); ?>
			</h3>
			<?php
			echo $bar( $lasting, array( // phpcs:ignore WordPress.Security.EscapeOutput
				'significant' => 'Significant — changed dynamic',
				'moderate'    => 'Moderate awareness raised',
				'none'        => 'No measurable effect',
			), $participated_yes );
			?>
		</div>

		<!-- Bottlenecks -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Biggest bottleneck (Q8)', 'aiad-core' ); ?>
			</h3>
			<?php echo $bar( $bottlenecks, $bottleneck_labels, $participated_yes ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- Non-participant reasons -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Non-participant: barriers (Q1B)', 'aiad-core' ); ?>
			</h3>
			<?php
			echo $bar( $non_reasons, array( // phpcs:ignore WordPress.Security.EscapeOutput
				'timetable'       => 'Curriculum/timetable pressure',
				'late_discovery'  => 'Late discovery',
				'staff_confidence' => 'Lack of staff confidence',
				'firewall'        => 'Tech / firewall restrictions',
				'banned_policy'   => 'AI ban policy',
			), $participated_no );
			?>
		</div>

		<!-- Support modules 2027 -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Top 2027 support modules requested', 'aiad-core' ); ?>
			</h3>
			<?php echo $bar( $support, $support_labels, $participated_yes ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- Materials adequacy -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Did the materials meet needs? (Q6)', 'aiad-core' ); ?>
			</h3>
			<?php echo $bar( $materials, $materials_labels, $participated_yes ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- Useful session formats -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Useful session formats (Q7)', 'aiad-core' ); ?>
			</h3>
			<?php echo $bar( $useful_formats, $useful_format_labels, $participated_yes ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- Best content format -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Best content format (Q8)', 'aiad-core' ); ?>
			</h3>
			<?php echo $bar( $best_formats, $best_format_labels, $participated_yes ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<!-- Preferred communication channels -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 12px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Preferred update channels', 'aiad-core' ); ?>
			</h3>
			<?php echo $bar( $comms, $comms_labels, $total ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		</div><!-- /grid -->

		<!-- Star ratings -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 16px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Average Likert ratings (participants, out of 5)', 'aiad-core' ); ?>
			</h3>
			<div style="display:grid;gap:10px;">
			<?php foreach ( $ratings_sum as $rk => $rv ) : ?>
				<?php
				$avg = $rv['count'] > 0 ? round( $rv['sum'] / $rv['count'], 2 ) : 0;
				$pct = $avg > 0 ? round( ( $avg / 5 ) * 100 ) : 0;
				$label = $rating_labels[ $rk ] ?? $rk;
				?>
				<div>
					<div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:3px;">
						<span><?php echo esc_html( $label ); ?></span>
						<span style="font-weight:600;color:<?php echo $avg >= 4 ? '#059669' : ( $avg >= 3 ? '#d97706' : '#dc2626' ); ?>;">
							<?php echo $rv['count'] > 0 ? esc_html( $avg . ' / 5' ) : esc_html__( 'No data', 'aiad-core' ); ?>
						</span>
					</div>
					<div style="background:#e5e7eb;border-radius:4px;height:16px;overflow:hidden;">
						<div style="background:<?php echo $avg >= 4 ? '#059669' : ( $avg >= 3 ? '#d97706' : '#dc2626' ); ?>;height:100%;width:<?php echo esc_attr( $pct ); ?>%;border-radius:4px;"></div>
					</div>
					<div style="font-size:11px;color:#9ca3af;margin-top:2px;"><?php echo esc_html( $rv['count'] ); ?> <?php esc_html_e( 'responses', 'aiad-core' ); ?></div>
				</div>
			<?php endforeach; ?>
			</div>
		</div>

		<?php if ( $recent_feedback ) : ?>
		<!-- Recent open feedback -->
		<?php echo $card; ?>
			<h3 style="margin:0 0 16px;font-size:14px;text-transform:uppercase;letter-spacing:.04em;color:#6b7280;">
				<?php esc_html_e( 'Recent open feedback', 'aiad-core' ); ?>
			</h3>
			<div style="display:grid;gap:12px;">
			<?php foreach ( $recent_feedback as $fb ) : ?>
				<div style="background:#f9fafb;border-left:3px solid #0070c0;padding:10px 14px;border-radius:0 6px 6px 0;">
					<p style="margin:0 0 6px;font-style:italic;">"<?php echo esc_html( mb_substr( $fb['text'], 0, 300 ) . ( mb_strlen( $fb['text'] ) > 300 ? '…' : '' ) ); ?>"</p>
					<p style="margin:0;font-size:11px;color:#6b7280;">
						<?php
						$by = array_filter( array( $fb['school'], $fb['role'] ) );
						echo esc_html( $by ? '— ' . implode( ' · ', $by ) : '— Anonymous' );
						?>
					</p>
				</div>
			<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

	</div>
	<?php
}
