<?php
/**
 * The programme team's pages, for people signed in to WordPress with the right capability:
 *
 *   /conversation/programme/                     the whole programme: journey, what is stuck, participation
 *   /conversation/programme/?mat=KEY             one trust: its schools and their participation
 *   /conversation/programme/?partner=KEY         one partner: aggregate impact, printable, no school details
 *   /conversation/programme/?export=national     CSV of the headline figures (also mat= and partner=)
 *
 * Schools, judges and partners never see these pages. Partners get a report from the team (section 23.10),
 * built from counts only.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Programme_Front {

	/** @var array<string,mixed>|null Result of a retention preview or run, for the page. */
	private static $retention = null;

	/** @var string|null Result of the last test email, for the page. */
	private static $test_result = null;

	/** A partner sees an age group's Student Voice only when it comes from at least this many schools. */
	const PARTNER_MIN_SCHOOLS = 3;

	/** WordPress capability needed. The programme team already use WordPress admin (section 23.1). */
	private static function capability(): string {
		return (string) apply_filters( 'aiadn_programme_capability', 'manage_options' );
	}

	private static function esc( $v ): string {
		return esc_html( (string) $v );
	}

	private static function pct( int $part, int $whole ): string {
		return $whole > 0 ? (string) round( 100 * $part / $whole ) . '%' : '0%';
	}

	/** Signed in with the right capability, or a sign-in redirect, or a plain refusal. */
	private static function gate(): ?string {
		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( wp_login_url( AIADN_Front::url( 'programme', array_filter( array(
				'mat'     => AIADN_Front::get( 'mat' ),
				'partner' => AIADN_Front::get( 'partner' ),
			) ) ) ) );
			exit;
		}
		if ( ! current_user_can( self::capability() ) ) {
			status_header( 403 );
			return '<h1>Programme team only</h1><p>This page is for the programme team. If you should be able to see it, ask the person who manages the site to give your WordPress account access.</p>';
		}
		return null;
	}

	/* ------------------------------------------------------------------ */
	/* Building blocks                                                     */
	/* ------------------------------------------------------------------ */

	private static function tiles( array $pairs ): string {
		$h = '<div class="aiadn__stats">';
		foreach ( $pairs as $label => $n ) {
			$h .= '<div class="aiadn__stat"><strong>' . self::esc( $n ) . '</strong><span>' . esc_html( $label ) . '</span></div>';
		}
		return $h . '</div>';
	}

	/**
	 * @param array<int,string>              $heads
	 * @param array<int,array<int,string>>   $rows  cells are HTML already escaped by the caller
	 */
	private static function table( array $heads, array $rows, string $empty = 'Nothing to show.' ): string {
		if ( ! $rows ) {
			return '<p class="aiadn__small">' . esc_html( $empty ) . '</p>';
		}
		$h = '<table class="aiadn__table"><thead><tr>';
		foreach ( $heads as $head ) {
			$h .= '<th scope="col">' . esc_html( $head ) . '</th>';
		}
		$h .= '</tr></thead><tbody>';
		foreach ( $rows as $row ) {
			$h .= '<tr>' . implode( '', array_map( static fn( $c ) => '<td>' . $c . '</td>', $row ) ) . '</tr>';
		}
		return $h . '</tbody></table>';
	}

	private static function bars( array $funnel ): string {
		$top = max( 1, (int) ( $funnel[0][1] ?? 1 ) );
		$h   = '';
		foreach ( $funnel as $step ) {
			list( $label, $n ) = $step;
			$h .= '<div class="aiadn__barrow aiadn__barrow--wide"><span>' . esc_html( $label ) . '</span><span class="aiadn__bar" aria-hidden="true"><span style="width:' . (int) round( 100 * $n / $top ) . '%"></span></span><strong>' . (int) $n . ' <span class="aiadn__meta">' . esc_html( self::pct( (int) $n, $top ) ) . '</span></strong></div>';
		}
		return $h;
	}

	private static function count_table( array $counts, array $labels ): string {
		$rows = array();
		foreach ( $counts as $key => $n ) {
			$rows[] = array( esc_html( $labels[ $key ] ?? (string) $key ), (string) (int) $n );
		}
		return self::table( array( '', 'Completed debates' ), $rows );
	}

	/**
	 * What students said, for each age group. Uses the Student Voice statements.
	 *
	 * @param array<string,array<string,mixed>> $vb from AIADN_Stats::voice_by_pathway()
	 */
	private static function voice_panel( array $vb, int $min_schools ): string {
		$h = '<p class="aiadn__small">Anonymous answers from students, by the year group each student chose. Percentages are of the students who answered each statement. An age group appears once at least ' . (int) AIADN_Voice::MIN_RESPONSES . ' students have answered' . ( $min_schools > 1 ? ', from at least ' . (int) $min_schools . ' schools' : '' ) . '. Answers given after a debate are left out.</p>';
		foreach ( $vb as $key => $g ) {
			if ( 'unstated' === $key ) {
				if ( $g['responses'] > 0 ) {
					$h .= '<p class="aiadn__small">' . (int) $g['responses'] . ' students chose &ldquo;prefer not to say&rdquo; for their year. They are counted in the totals but not in an age group.</p>';
				}
				continue;
			}
			$h .= '<h3>' . esc_html( $g['label'] ) . ' <span class="aiadn__meta">' . (int) $g['responses'] . ' answer' . ( 1 === $g['responses'] ? '' : 's' ) . ' from ' . (int) $g['schools'] . ' school' . ( 1 === $g['schools'] ? '' : 's' ) . '</span></h3>';
			if ( ! $g['shown'] ) {
				$h .= '<p class="aiadn__small">Not enough answers yet to show this age group.</p>';
				continue;
			}
			foreach ( AIADN_Voice::QUESTIONS as $qk => $q ) {
				$r = $g['questions'][ $qk ] ?? null;
				if ( ! $r ) {
					continue;
				}
				$h .= '<div class="aiadn__vq aiadn__q--' . esc_attr( $q['theme'] ) . '">' . AIADN_Voice_Front::tile( $q['theme'] ) . '<div><p><strong>' . esc_html( $q['label'] ) . '</strong> &ldquo;' . esc_html( $q['text'] ) . '&rdquo;</p>' . AIADN_Voice_Front::stack( $r ) . '<p class="aiadn__small">Agree ' . (int) $r['agree'] . '% &middot; Not sure ' . (int) $r['unsure'] . '% &middot; Disagree ' . (int) $r['disagree'] . '%</p></div></div>';
			}
		}
		return $h;
	}

	/* ------------------------------------------------------------------ */
	/* /conversation/programme/                                            */
	/* ------------------------------------------------------------------ */

	public static function view_programme(): string {
		AIADN_Front::set_title( 'Programme team' );
		$refused = self::gate();
		if ( null !== $refused ) {
			return $refused;
		}
		AIADN_Stats::reset();

		$sent_test = null;
		if ( AIADN_Front::is_post() && 'send_test_email' === AIADN_Front::post( 'aiadn_action' ) && check_admin_referer( 'aiadn_test_email' ) ) {
			$me        = wp_get_current_user();
			$sent_test = AIADN_Mailer::send_test( $me->user_email ) ? 'sent to ' . $me->user_email : 'failed';
		}
		self::$test_result = $sent_test;
		if ( AIADN_Front::is_post() && in_array( AIADN_Front::post( 'aiadn_action' ), array( 'retention_preview', 'retention_run' ), true ) && check_admin_referer( 'aiadn_retention' ) ) {
			$do = AIADN_Front::post( 'aiadn_action' );
			if ( 'retention_preview' === $do ) {
				self::$retention = array( 'kind' => 'preview', 'counts' => AIADN_Privacy::end_of_campaign( null, true ) );
			} elseif ( 'retention_run' === $do ) {
				self::$retention = 'DELETE' === AIADN_Front::post( 'confirm' )
					? array( 'kind' => 'done', 'counts' => AIADN_Privacy::end_of_campaign( null, false ) )
					: array( 'kind' => 'refused', 'counts' => array() );
			}
		}

		$export = AIADN_Front::get( 'export' );
		if ( '' !== $export ) {
			self::export( $export );
		}
		if ( '' !== AIADN_Front::get( 'mat' ) ) {
			return self::view_mat( AIADN_Front::get( 'mat' ) );
		}
		if ( '' !== AIADN_Front::get( 'partner' ) ) {
			return self::partner_page( AIADN_Front::get( 'partner' ) );
		}
		return self::view_national();
	}

	private static function view_national(): string {
		$f = AIADN_Stats::figures( null );
		$j = $f['judges'];

		$h  = '<h1>Programme team</h1>';
		$h .= '<p class="aiadn__small aiadn__noprint">Everything here is a count. There are no contact details and nothing about individual students. <a href="' . esc_url( AIADN_Front::url( 'programme', array( 'export' => 'national' ) ) ) . '">Download the figures as CSV</a></p>';
		$h .= self::tiles( array(
			'Schools approved'  => $f['approved'],
			'Debates completed' => $f['counting'],
			'Students reached'  => $f['students'],
			'School connections' => $f['connections'],
			'Student Voice answers' => $f['voice'],
			'Judges'            => $j['people'],
		) );
		$h .= '<p class="aiadn__small aiadn__noprint"><button class="aiadn__link" type="button" onclick="document.querySelectorAll(\'.aiadn__fold\').forEach(function(d){d.open=true})">Open all</button> &middot; <button class="aiadn__link" type="button" onclick="document.querySelectorAll(\'.aiadn__fold\').forEach(function(d){d.open=false})">Close all</button></p>';

		// The journey.
		$h .= AIADN_Result_Front::fold( 'The journey', '<p class="aiadn__small">How many schools reach each step. The percentage is of the schools that started registering.</p>' . self::bars( $f['funnel'] ), true );

		// What is stuck.
		$stuck = AIADN_Stats::stuck();
		$rows  = array();
		foreach ( array_slice( $stuck, 0, 20 ) as $s ) {
			$rows[] = array( self::esc( $s['code'] ), self::esc( $s['schools'] ), self::esc( $s['what'] ), (string) (int) $s['days'] . ' days', (string) (int) $s['reminders'] );
		}
		$body  = '<p>' . (int) AIADN_Stats::in_progress() . ' debates are in progress. ' . count( $stuck ) . ' have waited ' . (int) AIADN_Stats::STUCK_DAYS . ' days or more, or a result is overdue.</p>';
		$body .= self::table( array( 'Debate', 'Schools', 'Waiting for', 'For', 'Reminders sent' ), $rows, 'No debate is stuck.' );
		$h    .= AIADN_Result_Front::fold( 'Stuck fixtures (' . count( $stuck ) . ')', $body, true );

		// Incidents.
		$inc  = AIADN_Stats::incidents();
		$body = self::tiles( array( 'Open' => $inc['open'], 'Resolved' => $inc['resolved'], 'Oldest open (days)' => $inc['oldest_open_days'] ) );
		$body .= '<p class="aiadn__small">Held results: ' . (int) $f['held'] . '. The team sees counts only; schools hold the details.</p>';
		$rows = array();
		foreach ( $inc['categories'] as $cat => $n ) {
			$rows[] = array( self::esc( ucfirst( str_replace( '_', ' ', (string) $cat ) ) ), (string) (int) $n );
		}
		$body .= self::table( array( 'Category', 'Reports' ), $rows, 'No incidents reported.' );
		$h    .= AIADN_Result_Front::fold( 'Incidents (' . (int) $inc['open'] . ' open)', $body, $inc['open'] > 0 );

		// Data quality.
		$dq    = AIADN_Stats::data_quality();
		$total = count( $dq['awaiting_slt'] ) + count( $dq['unverified'] ) + count( $dq['duplicates'] ) + count( $dq['no_region'] ) + count( $dq['inactive'] ) + count( $dq['variants'] );
		$body  = '<p class="aiadn__small">Records to tidy, so the figures stay honest.</p>';
		$list  = static function ( string $title, array $items, callable $line ): string {
			if ( ! $items ) {
				return '';
			}
			$out = '<h3>' . esc_html( $title ) . ' (' . count( $items ) . ')</h3><ul class="aiadn__list">';
			foreach ( array_slice( $items, 0, 10 ) as $i ) {
				$out .= '<li>' . $line( $i ) . '</li>';
			}
			return $out . ( count( $items ) > 10 ? '<li class="aiadn__meta">and ' . ( count( $items ) - 10 ) . ' more</li>' : '' ) . '</ul>';
		};
		$body .= $list( 'Waiting for a headteacher to approve', $dq['awaiting_slt'], static fn( $i ) => esc_html( $i['name'] ) . ' <span class="aiadn__meta">' . (int) $i['days'] . ' days</span>' );
		$body .= $list( 'Registered but never verified their email', $dq['unverified'], static fn( $i ) => esc_html( $i['name'] ) . ' <span class="aiadn__meta">' . (int) $i['days'] . ' days</span>' );
		$body .= $list( 'Possible duplicate schools (same name)', $dq['duplicates'], static fn( $i ) => esc_html( $i['name'] ) . ' <span class="aiadn__meta">' . (int) $i['copies'] . ' records</span>' );
		$body .= $list( 'Approved, no debate started after 30 days', $dq['inactive'], static fn( $i ) => esc_html( $i['name'] ) . ' <span class="aiadn__meta">' . (int) $i['days'] . ' days</span>' );
		$body .= $list( 'Trusts or partners spelt more than one way', $dq['variants'], static fn( $i ) => esc_html( $i['kind'] ) . ': ' . esc_html( $i['names'] ) );
		$body .= $list( 'No region (postcode missing or not recognised)', $dq['no_region'], static fn( $i ) => esc_html( $i ) );
		if ( 0 === $total ) {
			$body .= '<p>Nothing to tidy.</p>';
		}
		$h .= AIADN_Result_Front::fold( 'Data quality (' . $total . ')', $body, false );

		// Participation.
		$body  = '<p>Debates completed with no open issue.</p>';
		$body .= '<h3>By age group</h3>' . self::count_table( $f['ages'], AIADN_Motions::AGES );
		$body .= '<h3>By theme</h3>' . self::count_table( $f['themes'], AIADN_Motions::THEMES );
		$body .= '<h3>By format</h3>' . self::count_table( $f['formats'], AIADN_Motions::FORMATS );
		$body .= '<h3>Connections</h3>' . self::tiles( array( 'Different pairs of schools' => $f['connections'], 'Across trusts' => $f['cross_mat'], 'Across regions' => $f['cross_region'], 'Themes explored' => $f['themes_used'] ) );
		$h    .= AIADN_Result_Front::fold( 'Participation', $body, false );

		// Regions, trusts, partners.
		$rows = array();
		foreach ( AIADN_Stats::groups( 'region' ) as $g ) {
			$rows[] = array( self::esc( $g['label'] ), (string) $g['schools'], (string) $g['approved'], (string) $g['active'], (string) $g['debates'], (string) $g['students'] );
		}
		$h .= AIADN_Result_Front::fold( 'Regions', '<p class="aiadn__small">From the first letters of each school&rsquo;s postcode. A few postcode areas straddle a boundary, so treat the split as approximate.</p>' . self::table( array( 'Region', 'Schools', 'Approved', 'Started a debate', 'Debates', 'Students' ), $rows, 'No schools yet.' ), false );

		$rows = array();
		foreach ( AIADN_Stats::groups( 'mat' ) as $g ) {
			$rows[] = array( '<a href="' . esc_url( AIADN_Front::url( 'programme', array( 'mat' => $g['key'] ) ) ) . '">' . self::esc( $g['label'] ) . '</a>', (string) $g['schools'], (string) $g['approved'], (string) $g['active'], (string) $g['debates'], (string) $g['students'] );
		}
		$h .= AIADN_Result_Front::fold( 'Trusts', self::table( array( 'Trust', 'Schools', 'Approved', 'Started a debate', 'Debates', 'Students' ), $rows, 'No school has named a trust yet.' ), false );

		$rows = array();
		foreach ( AIADN_Stats::groups( 'partner' ) as $g ) {
			$rows[] = array( '<a href="' . esc_url( AIADN_Front::url( 'programme', array( 'partner' => $g['key'] ) ) ) . '">' . self::esc( $g['label'] ) . '</a>', (string) $g['schools'], (string) $g['approved'], (string) $g['active'], (string) $g['debates'], (string) AIADN_Stats::judges_from_partner( $g['key'] ) );
		}
		$h .= AIADN_Result_Front::fold( 'Partners', '<p class="aiadn__small">Organisations named at sign-up whose school (or judge) agreed to tell them. Open one for its impact report.</p>' . self::table( array( 'Partner', 'Schools', 'Approved', 'Started a debate', 'Debates', 'Judges' ), $rows, 'No headteacher or judge has agreed to tell an organisation yet.' ), false );

		// Find a Debate.
		$fs   = AIADN_Find::summary();
		$body = self::tiles( array( 'Open requests now' => $fs['open'], 'Asks waiting' => $fs['pending'], 'Accepted' => $fs['accepted'], 'Declined' => $fs['declined'], 'Withdrawn' => $fs['withdrawn'] ) );
		$body .= '<p class="aiadn__small">Schools without an opponent publish a request; another school asks and the host chooses. Counts only.</p>';
		$h    .= AIADN_Result_Front::fold( 'Join the conversation', $body, false );

		// Data retention.
		$body  = '<p>Teacher, headteacher and judge contact details, and the emails of the people who introduced them, are deleted automatically the day after <strong>' . self::esc( wp_date( 'j F Y', strtotime( AIADN_Privacy::retention_date() ) ) ) . '</strong>. The team is warned 14 days before. Anonymous totals, scores, results and Student Voice answers are kept.</p>';
		$done  = get_option( 'aiadn_retention_done_' . AIADN_Privacy::retention_date() );
		$body .= '<p>' . ( $done ? 'The end-of-campaign deletion has run (' . self::esc( AIADN_Util::show( (string) $done ) ) . ').' : 'The end-of-campaign deletion has not run yet.' ) . '</p>';
		if ( self::$retention ) {
			if ( 'refused' === self::$retention['kind'] ) {
				$body .= AIADN_Front::notice( 'Nothing was deleted. Type DELETE in capitals to confirm.', 'error' );
			} else {
				$rows = array();
				foreach ( self::$retention['counts'] as $label => $n ) {
					$rows[] = array( self::esc( $label ), (string) (int) $n );
				}
				$body .= '<h3>' . ( 'preview' === self::$retention['kind'] ? 'This is what would be deleted' : 'This has been deleted' ) . '</h3>' . self::table( array( 'What', 'Rows' ), $rows );
			}
		}
		$nonce = wp_nonce_field( 'aiadn_retention', '_wpnonce', true, false );
		$body .= '<form method="post" action="' . esc_url( AIADN_Front::url( 'programme' ) ) . '" class="aiadn__form">' . $nonce . '<input type="hidden" name="aiadn_action" value="retention_preview"><button class="aiadn__button aiadn__button--quiet" type="submit">Preview what would be deleted</button></form>';
		$body .= '<form method="post" action="' . esc_url( AIADN_Front::url( 'programme' ) ) . '" class="aiadn__form">' . $nonce . '<input type="hidden" name="aiadn_action" value="retention_run"><label for="rt-confirm">To delete everyone&rsquo;s contact details now, type DELETE</label><input id="rt-confirm" name="confirm" type="text" autocomplete="off"><button class="aiadn__button aiadn__button--quiet" type="submit">Delete now</button></form>';
		$body .= '<p class="aiadn__small">Anyone can also ask for their own details to go sooner, from the &ldquo;Delete my details&rdquo; page. Download any figures you want to keep as CSV first.</p>';
		$h    .= AIADN_Result_Front::fold( 'Data retention', $body, false );

		// Email check.
		$route = AIADN_Mailer::route();
		$body  = '<p>How this platform\'s emails go out (sign-in codes, approvals, invitations, updates).</p><dl class="aiadn__details"><dt>Route</dt><dd>' . self::esc( $route['kind'] ) . ( '' !== $route['detail'] ? ' (' . self::esc( $route['detail'] ) . ')' : '' ) . '</dd><dt>Sent from</dt><dd>' . self::esc( $route['from'] ) . '</dd><dt>Team address</dt><dd>' . self::esc( AIADN_Util::team_email() ) . '</dd></dl>';
		if ( null !== self::$test_result ) {
			$body .= AIADN_Front::notice( 'Test email ' . self::$test_result . '.', 'sent' === substr( self::$test_result, 0, 4 ) ? 'info' : 'error' );
		}
		$body .= '<form method="post" action="' . esc_url( AIADN_Front::url( 'programme' ) ) . '" class="aiadn__form">' . wp_nonce_field( 'aiadn_test_email', '_wpnonce', true, false ) . '<input type="hidden" name="aiadn_action" value="send_test_email"><button class="aiadn__button aiadn__button--quiet" type="submit">Send a test email to me</button></form>';
		$h    .= AIADN_Result_Front::fold( 'Email check', $body, false );

		// Referrals.
		$rs   = AIADN_Stats::referrals_summary();
		$body = self::tiles( array( 'Waiting for the headteacher or judge' => $rs['counts']['named'], 'Agreed to tell them' => $rs['counts']['shared'], 'Chose not to tell them' => $rs['counts']['not_shared'], 'Organisation said not us' => $rs['counts']['disowned'] ) );
		$body .= '<p class="aiadn__small">A school or judge names who introduced them. Nobody is emailed until the headteacher or judge agrees, and the organisation can say it was not them.</p>';
		$rows = array();
		foreach ( $rs['disowned'] as $r ) {
			$rows[] = array( self::esc( $r['who'] ), self::esc( $r['org'] ) );
		}
		$body .= self::table( array( 'Named', 'As introduced by' ), $rows, 'No referral has been disowned.' );
		$nm    = AIADN_Nominations::summary();
		$body .= '<h3>Nominations</h3>' . self::tiles( array( 'Waiting for the nominator’s code' => $nm['unverified'], 'Invitations sent' => $nm['sent'], 'Schools registered' => $nm['registered'], 'Asked not to be contacted' => $nm['opted_out'] ) ) . '<p class="aiadn__small">Someone who works with a school nominates it; the school gets one email and decides for itself. Counts only.</p>';
		$h    .= AIADN_Result_Front::fold( 'Referrals', $body, $rs['counts']['disowned'] > 0 );

		// Judges.
		$body  = self::tiles( array( 'Accepted' => $j['accepted'], 'Different people' => $j['people'], 'Judged more than once' => $j['repeat'], 'Declined' => $j['declined'], 'Waiting to answer' => $j['invited'] ) );
		$rows  = array();
		foreach ( $j['types'] as $type => $n ) {
			$rows[] = array( self::esc( AIADN_Motions::JUDGE_TYPES[ $type ] ), (string) (int) $n );
		}
		$body .= self::table( array( 'Type of judge', 'Debates judged' ), $rows );
		$h    .= AIADN_Result_Front::fold( 'Judges', $body, false );

		// Student Voice.
		$h .= AIADN_Result_Front::fold( 'Student Voice', self::tiles( array( 'Answers' => $f['voice'], 'Schools with results unlocked' => $f['voice_schools'] ) ) . self::voice_panel( AIADN_Stats::voice_by_pathway( null, 1 ), 1 ) . '<p class="aiadn__small">A school&rsquo;s own results unlock at ' . (int) AIADN_Voice::MIN_RESPONSES . ' answers. These are the schools that took part, not a national sample.</p>', false );

		$r     = AIADN_Stats::ratings();
		$body  = '<p>How easy people found it, out of 5.</p>' . self::tiles( array( 'Teachers (' . $r['teacher']['n'] . ' ratings)' => $r['teacher']['n'] ? $r['teacher']['avg'] : '-', 'Judges (' . $r['judge']['n'] . ' ratings)' => $r['judge']['n'] ? $r['judge']['avg'] : '-' ) );
		$h    .= AIADN_Result_Front::fold( 'Experience', $body, false );

		$h .= '<p class="aiadn__small">The figures describe the schools taking part. They are not representative of schools nationally.</p>';
		$h .= '<script>(function(){var s=[];window.addEventListener("beforeprint",function(){s=[];document.querySelectorAll(".aiadn__fold").forEach(function(d){s.push(d.open);d.open=true})});window.addEventListener("afterprint",function(){document.querySelectorAll(".aiadn__fold").forEach(function(d,i){d.open=!!s[i]})})})();</script>';
		return $h;
	}

	/* ------------------------------------------------------------------ */
	/* One trust                                                           */
	/* ------------------------------------------------------------------ */

	private static function view_mat( string $key ): string {
		$ids = AIADN_Stats::school_ids( 'mat', $key );
		if ( ! $ids ) {
			return '<h1>Trust not found</h1><p><a href="' . esc_url( AIADN_Front::url( 'programme' ) ) . '">&larr; Programme team</a></p>';
		}
		$label = AIADN_Stats::group_label( 'mat', $key );
		$f     = AIADN_Stats::figures( $ids );

		$h  = '<h1>' . self::esc( $label ) . '</h1>';
		$h .= '<p class="aiadn__small aiadn__noprint"><a href="' . esc_url( AIADN_Front::url( 'programme' ) ) . '">&larr; Programme team</a> &middot; <a href="' . esc_url( AIADN_Front::url( 'programme', array( 'export' => 'mat', 'mat' => $key ) ) ) . '">Download as CSV</a></p>';
		$h .= self::tiles( array( 'Schools' => $f['schools'], 'Approved' => $f['approved'], 'Debates completed' => $f['counting'], 'Students reached' => $f['students'], 'Themes explored' => $f['themes_used'], 'Student Voice answers' => $f['voice'] ) );

		$rows = array();
		foreach ( AIADN_Stats::schools_in( 'mat', $key ) as $s ) {
			$rows[] = array( self::esc( $s['name'] ), self::esc( $s['code'] ), self::esc( $s['region'] ), self::esc( 'approved' === $s['status'] ? 'Approved' : 'Waiting' ), (string) $s['debates'], (string) $s['students'], (string) $s['voice'] );
		}
		$h .= '<div class="aiadn__panel"><h2>Participating schools</h2>' . self::table( array( 'School', 'Code', 'Region', 'Status', 'Debates', 'Students', 'Voice answers' ), $rows ) . '</div>';
		$h .= '<div class="aiadn__panel"><h2>Where the conversation went</h2>' . self::tiles( array( 'Connections' => $f['connections'], 'Across trusts' => $f['cross_mat'], 'Across regions' => $f['cross_region'] ) ) . '<h3>By theme</h3>' . self::count_table( $f['themes'], AIADN_Motions::THEMES ) . '<h3>By age group</h3>' . self::count_table( $f['ages'], AIADN_Motions::AGES ) . '</div>';
		$h .= '<div class="aiadn__panel"><h2>What students said</h2>' . self::voice_panel( AIADN_Stats::voice_by_pathway( $ids, 1 ), 1 ) . '</div>';
		$h .= '<p class="aiadn__small">The trust has no sign-in of its own. If a trust wants this, the programme team can print it or send the CSV.</p>';
		return $h;
	}

	/* ------------------------------------------------------------------ */
	/* One partner: aggregate only                                         */
	/* ------------------------------------------------------------------ */

	private static function partner_page( string $key ): string {
		$ids = AIADN_Stats::school_ids( 'partner', $key );
		if ( ! $ids && 0 === AIADN_Stats::judges_from_partner( $key ) ) {
			return '<h1>Partner not found</h1><p><a href="' . esc_url( AIADN_Front::url( 'programme' ) ) . '">&larr; Programme team</a></p>';
		}
		$label = AIADN_Stats::group_label( 'partner', $key );
		$h     = '<h1>Impact report: ' . self::esc( $label ) . '</h1>';
		$h    .= '<p class="aiadn__small aiadn__noprint"><a href="' . esc_url( AIADN_Front::url( 'programme' ) ) . '">&larr; Programme team</a> &middot; <a href="' . esc_url( AIADN_Front::url( 'programme', array( 'export' => 'partner', 'partner' => $key ) ) ) . '">Download as CSV</a></p>';
		return $h . self::partner_body( $key, $label ) . '<p class="aiadn__small aiadn__noprint">The organisation has its own live version of this, by email link, once a headteacher or judge has agreed to tell it.</p>';
	}

	/**
	 * The figures for one organisation: what the programme team prints, and what the organisation sees on its
	 * own dashboard. Counts only. No school, teacher, judge or student is named.
	 */
	public static function partner_body( string $key, string $label ): string {
		$ids = AIADN_Stats::school_ids( 'partner', $key ) ?? array();
		$f   = AIADN_Stats::figures( $ids );
		$judges  = AIADN_Stats::judges_from_partner( $key );
		$started = (int) $f['funnel'][3][1];

		$h  = '<div class="aiadn__partner-logo">' . AIADN_Front::logo_html() . '</div><h2 class="aiadn__printonly">Impact report: ' . self::esc( $label ) . '</h2>';
		$h .= '<p>National AI Conversation, AI Awareness Day 2027. Prepared ' . self::esc( wp_date( 'j F Y' ) ) . '.</p>';
		$h .= self::tiles( array(
			'Schools activated'  => $f['approved'],
			'Started a debate'   => $started,
			'Debates generated'  => $f['debates'],
			'Debates completed'  => $f['counting'],
			'Judges contributed' => $judges,
			'Students reached'   => $f['students'],
		) );
		$h .= '<div class="aiadn__panel"><h2>What that means</h2><ul class="aiadn__list">';
		$h .= '<li><strong>' . (int) $f['approved'] . '</strong> schools you introduced are taking part. Their headteachers agreed we could tell you.</li>';
		$h .= '<li>Those schools took part in <strong>' . (int) $f['debates'] . '</strong> debates, and <strong>' . (int) $f['counting'] . '</strong> have been judged and counted, reaching <strong>' . (int) $f['students'] . '</strong> students.</li>';
		$h .= '<li>Their students gave <strong>' . (int) $f['voice'] . '</strong> anonymous Student Voice answers.</li>';
		$h .= '<li>Debates covered <strong>' . (int) $f['themes_used'] . '</strong> of the five themes, against <strong>' . (int) $f['connections'] . '</strong> different schools.</li>';
		$h .= '<li><strong>' . (int) $judges . '</strong> judges you introduced have agreed to judge.</li>';
		$h .= '</ul></div>';
		$h .= '<div class="aiadn__panel"><h2>By theme</h2>' . self::count_table( $f['themes'], AIADN_Motions::THEMES ) . '<h2>By age group</h2>' . self::count_table( $f['ages'], AIADN_Motions::AGES ) . '</div>';
		$h .= '<div class="aiadn__panel"><h2>What students said, by age group</h2>' . self::voice_panel( AIADN_Stats::voice_by_pathway( $ids, self::PARTNER_MIN_SCHOOLS ), self::PARTNER_MIN_SCHOOLS ) . '</div>';
		$h .= '<p class="aiadn__small">Counts only. No school, teacher, judge or student is named. The figures describe the schools that took part and are not representative of schools nationally. Each school, judge and partner is responsible for their own part in the conversation, and you are responsible for how you use these figures.</p>';
		$h .= '<p class="aiadn__noprint"><button class="aiadn__button aiadn__button--quiet" type="button" onclick="window.print()">Print or save as PDF</button></p>';
		return $h;
	}

	/* ------------------------------------------------------------------ */
	/* CSV                                                                 */
	/* ------------------------------------------------------------------ */

	/** A cell that cannot be run as a spreadsheet formula. */
	private static function cell( $v ): string {
		$v = (string) $v;
		return preg_match( '/^[=+\-@\t\r]/', $v ) ? "'" . $v : $v;
	}

	/** Student Voice by age group, as CSV rows. Only what the report would show. */
	private static function voice_rows( array $vb ): array {
		$rows = array();
		foreach ( $vb as $key => $g ) {
			if ( 'unstated' === $key ) {
				continue;
			}
			$rows[] = array( 'Student Voice answers: ' . $g['label'], (string) $g['responses'] );
			$rows[] = array( 'Student Voice schools: ' . $g['label'], (string) $g['schools'] );
			foreach ( AIADN_Voice::QUESTIONS as $qk => $q ) {
				$r = $g['questions'][ $qk ] ?? null;
				if ( $r ) {
					$rows[] = array( 'Student Voice: ' . $g['label'] . ': ' . $q['label'] . ': agree %', (string) $r['agree'] );
					$rows[] = array( 'Student Voice: ' . $g['label'] . ': ' . $q['label'] . ': not sure %', (string) $r['unsure'] );
					$rows[] = array( 'Student Voice: ' . $g['label'] . ': ' . $q['label'] . ': disagree %', (string) $r['disagree'] );
				}
			}
		}
		return $rows;
	}

	private static function figure_rows( array $f ): array {
		$rows = array( array( 'Measure', 'Value' ) );
		$add  = static function ( string $label, $n ) use ( &$rows ): void {
			$rows[] = array( $label, (string) $n );
		};
		$add( 'Schools registered', $f['schools'] );
		$add( 'Schools approved', $f['approved'] );
		foreach ( $f['funnel'] as $step ) {
			$add( 'Journey: ' . $step[0], $step[1] );
		}
		$add( 'Debates started', $f['debates'] );
		$add( 'Debates completed', $f['counting'] );
		$add( 'Results held by an issue', $f['held'] );
		$add( 'Students reached', $f['students'] );
		$add( 'Different pairs of schools', $f['connections'] );
		$add( 'Pairs across trusts', $f['cross_mat'] );
		$add( 'Pairs across regions', $f['cross_region'] );
		$add( 'Themes explored', $f['themes_used'] );
		foreach ( $f['themes'] as $k => $n ) {
			$add( 'Theme: ' . AIADN_Motions::THEMES[ $k ], $n );
		}
		foreach ( $f['ages'] as $k => $n ) {
			$add( 'Age group: ' . AIADN_Motions::AGES[ $k ], $n );
		}
		foreach ( $f['formats'] as $k => $n ) {
			$add( 'Format: ' . AIADN_Motions::FORMATS[ $k ], $n );
		}
		$add( 'Student Voice answers', $f['voice'] );
		$add( 'Judges (different people)', $f['judges']['people'] );
		return $rows;
	}

	private static function export( string $what ): void {
		$rows = array();
		if ( 'mat' === $what || 'partner' === $what ) {
			$key = AIADN_Front::get( $what );
			$ids = AIADN_Stats::school_ids( $what, $key );
			if ( ! $ids ) {
				wp_die( 'Not found.', '', array( 'response' => 404 ) );
			}
			$rows   = self::figure_rows( AIADN_Stats::figures( $ids ) );
			$rows   = array_merge( $rows, self::voice_rows( AIADN_Stats::voice_by_pathway( $ids, 'partner' === $what ? self::PARTNER_MIN_SCHOOLS : 1 ) ) );
			$rows[] = array( 'Group', AIADN_Stats::group_label( $what, $key ) );
			if ( 'partner' === $what ) {
				$rows[] = array( 'Judges contributed', (string) AIADN_Stats::judges_from_partner( $key ) );
			}
		} else {
			$rows = array_merge( self::figure_rows( AIADN_Stats::figures( null ) ), self::voice_rows( AIADN_Stats::voice_by_pathway( null, 1 ) ) );
			foreach ( array( 'region' => 'Region', 'mat' => 'Trust', 'partner' => 'Partner' ) as $kind => $label ) {
				$rows[] = array();
				$rows[] = array( $label, 'Schools', 'Approved', 'Started a debate', 'Debates', 'Students' );
				foreach ( AIADN_Stats::groups( $kind ) as $g ) {
					$rows[] = array( $g['label'], (string) $g['schools'], (string) $g['approved'], (string) $g['active'], (string) $g['debates'], (string) $g['students'] );
				}
			}
		}
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="aiad-' . sanitize_file_name( $what ) . '-' . gmdate( 'Y-m-d' ) . '.csv"' );
		header( 'X-Content-Type-Options: nosniff' );
		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" );
		foreach ( $rows as $row ) {
			fputcsv( $out, array_map( array( __CLASS__, 'cell' ), $row ) );
		}
		fclose( $out );
		exit;
	}
}
