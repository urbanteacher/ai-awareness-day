<?php
/**
 * The numbers behind the programme team's dashboard, the trust view and the partner report.
 *
 * Everything here is aggregate: counts of schools, debates, students and answers. There are no contact
 * details, no student data and no scores by student. The figures are worked out in PHP from a handful
 * of queries, so the rules (what counts as a debate, what counts as reached) are written once, here.
 *
 * A debate COUNTS when its result is in and no issue is open on it (the same rule as AIADN_Results).
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AIADN_Stats {

	/** A debate still waiting on somebody for this many days is listed as stuck. */
	const STUCK_DAYS = 7;

	/** A school waiting this long for its senior leader, or never verifying its email, is a data-quality note. */
	const STALE_DAYS = 7;

	/** A result overdue by this many days after the debate is listed as stuck. */
	const RESULT_OVERDUE_DAYS = 2;

	const DONE = array( 'completed', 'void', 'cancelled', 'expired' );

	const STATUS_LABELS = array(
		'awaiting_opponent'    => 'Waiting for an opponent',
		'awaiting_b_approval'  => "Waiting for the other school's headteacher",
		'matched'              => 'Waiting for the host to propose a fixture',
		'proposed'             => 'Waiting for the fixture to be agreed',
		'agreed'               => 'Waiting for the judge to accept',
		'ready'                => 'Ready',
		'completed'            => 'Completed',
		'void'                 => 'Void',
		'cancelled'            => 'Cancelled',
		'expired'              => 'Expired',
	);

	/** @var array<string,mixed>|null */
	private static $data = null;

	public static function reset(): void {
		self::$data = null;
	}

	/* ------------------------------------------------------------------ */
	/* Loading                                                             */
	/* ------------------------------------------------------------------ */

	private static function keyed( string $sql, string $key ): array {
		global $wpdb;
		$out = array();
		foreach ( (array) $wpdb->get_results( $sql, ARRAY_A ) as $row ) { // phpcs:ignore WordPress.DB
			$out[ $row[ $key ] ] = $row;
		}
		return $out;
	}

	/** @return array<string,mixed> */
	private static function load(): array {
		if ( null !== self::$data ) {
			return self::$data;
		}
		global $wpdb;
		$t = static fn( string $n ): string => AIADN_Database::table( $n );

		$schools = self::keyed( 'SELECT id, code, name, name_key, postcode, age_phases, mat_name, partner_ref, status, created_at, slt_approved_at FROM ' . $t( 'schools' ), 'id' );
		$debates = self::keyed( 'SELECT id, code, school_a_id, school_b_id, status, age_group, theme, format, stage_at, starts_at, reminders_sent, created_at FROM ' . $t( 'debates' ), 'id' );
		$cards   = array();
		foreach ( (array) $wpdb->get_results( "SELECT debate_id, students FROM {$t( 'scorecards' )} WHERE status = 'submitted'", ARRAY_A ) as $r ) { // phpcs:ignore WordPress.DB
			$cards[ (int) $r['debate_id'] ] = (int) $r['students'];
		}
		$open = array();
		foreach ( (array) $wpdb->get_col( "SELECT DISTINCT debate_id FROM {$t( 'issues' )} WHERE status = 'reported'" ) as $id ) { // phpcs:ignore WordPress.DB
			$open[ (int) $id ] = true;
		}
		$certs = array();
		foreach ( (array) $wpdb->get_col( "SELECT school_id FROM {$t( 'certificates' )} WHERE status = 'issued'" ) as $id ) { // phpcs:ignore WordPress.DB
			$certs[ (int) $id ] = true;
		}
		$voice = array();
		foreach ( (array) $wpdb->get_results( "SELECT school_id, COUNT(*) AS n FROM {$t( 'voice' )} GROUP BY school_id", ARRAY_A ) as $r ) { // phpcs:ignore WordPress.DB
			$voice[ (int) $r['school_id'] ] = (int) $r['n'];
		}
		$judges = (array) $wpdb->get_results( "SELECT id, debate_id, email, judge_type, status, partner_ref FROM {$t( 'judges' )}", ARRAY_A ); // phpcs:ignore WordPress.DB
		$issues = (array) $wpdb->get_results( "SELECT id, debate_id, category, status, created_at, resolved_at FROM {$t( 'issues' )}", ARRAY_A ); // phpcs:ignore WordPress.DB
		$sums = '';
		foreach ( array_keys( AIADN_Voice::QUESTIONS ) as $k ) {
			$sums .= ", SUM(q_{$k} = 1) AS {$k}_1, SUM(q_{$k} = 2) AS {$k}_2, SUM(q_{$k} = 3) AS {$k}_3";
		}
		// Answers given after a debate are left out, so what a debate did to opinion does not colour the picture.
		$voice_rows = (array) $wpdb->get_results( "SELECT school_id, year_group, COUNT(*) AS n{$sums} FROM {$t( 'voice' )} WHERE phase <> 'after' GROUP BY school_id, year_group", ARRAY_A ); // phpcs:ignore WordPress.DB
		$rates  = (array) $wpdb->get_results( "SELECT who, COUNT(*) AS n, AVG(rating) AS avg FROM {$t( 'ratings' )} GROUP BY who", ARRAY_A ); // phpcs:ignore WordPress.DB

		foreach ( $debates as $id => $d ) {
			$debates[ $id ]['counts'] = 'completed' === $d['status'] && ! isset( $open[ (int) $id ] );
			$debates[ $id ]['held']   = 'completed' === $d['status'] && isset( $open[ (int) $id ] );
		}
		foreach ( $schools as $id => $s ) {
			$schools[ $id ]['region']  = AIADN_Regions::for_postcode( (string) $s['postcode'] );
			$schools[ $id ]['mat_key'] = self::org_key( (string) $s['mat_name'] );
			$schools[ $id ]['par_key'] = self::org_key( (string) $s['partner_ref'] );
		}

		self::$data = compact( 'schools', 'debates', 'cards', 'certs', 'voice', 'voice_rows', 'judges', 'issues', 'rates' );
		return self::$data;
	}

	/**
	 * A comparison key for a trust or partner name, so "Apps for Good", "apps for good" and "AppsForGood"
	 * are counted as one. Empty for a blank name.
	 */
	public static function org_key( string $name ): string {
		$name = trim( $name );
		if ( '' === $name ) {
			return '';
		}
		$plain = strtolower( function_exists( 'remove_accents' ) ? remove_accents( $name ) : $name );
		$key   = preg_replace( '/[^a-z0-9]+/', '', preg_replace( '/\b(multi[- ]?academy|academy|trust|mat|the|ltd|limited|cic)\b/', ' ', $plain ) );
		return '' !== $key ? $key : preg_replace( '/[^a-z0-9]+/', '', $plain );
	}

	private static function days_since( ?string $utc ): float {
		return $utc ? max( 0, ( time() - strtotime( $utc . ' UTC' ) ) / DAY_IN_SECONDS ) : 0.0;
	}

	/* ------------------------------------------------------------------ */
	/* Scopes: which schools a figure is about                             */
	/* ------------------------------------------------------------------ */

	/**
	 * The schools in a group: every school for the whole programme, or a region, trust or partner.
	 *
	 * @param string $kind '' (everyone), 'region', 'mat' or 'partner'
	 * @return array<int,true>|null a set of school ids, or null for everyone
	 */
	public static function school_ids( string $kind, string $key ): ?array {
		if ( '' === $kind ) {
			return null;
		}
		$field = array( 'region' => 'region', 'mat' => 'mat_key', 'partner' => 'par_key' )[ $kind ] ?? null;
		$set   = array();
		if ( $field ) {
			foreach ( self::load()['schools'] as $id => $s ) {
				if ( '' !== $key && (string) $s[ $field ] === $key ) {
					$set[ (int) $id ] = true;
				}
			}
		}
		return $set;
	}

	/* ------------------------------------------------------------------ */
	/* The figures                                                         */
	/* ------------------------------------------------------------------ */

	/**
	 * Participation figures for a set of schools (or for everyone when $ids is null).
	 *
	 * @param array<int,true>|null $ids
	 * @return array<string,mixed>
	 */
	public static function figures( ?array $ids = null ): array {
		$d  = self::load();
		$in = static fn( $sid ): bool => null === $ids || isset( $ids[ (int) $sid ] );

		$schools = array_filter( $d['schools'], static fn( $s ) => $in( $s['id'] ) );
		$debates = array_filter( $d['debates'], static fn( $x ) => $in( $x['school_a_id'] ) || ( $x['school_b_id'] && $in( $x['school_b_id'] ) ) );

		// The journey, school by school.
		$started = $matched = $agreed = $done = array();
		foreach ( $debates as $x ) {
			foreach ( array( (int) $x['school_a_id'], (int) $x['school_b_id'] ) as $sid ) {
				if ( ! $sid || ! isset( $schools[ $sid ] ) ) {
					continue;
				}
				$started[ $sid ] = true;
				if ( in_array( $x['status'], array( 'matched', 'proposed', 'agreed', 'ready', 'completed', 'void' ), true ) ) {
					$matched[ $sid ] = true;
				}
				if ( in_array( $x['status'], array( 'agreed', 'ready', 'completed' ), true ) ) {
					$agreed[ $sid ] = true;
				}
				if ( $x['counts'] ) {
					$done[ $sid ] = true;
				}
			}
		}
		$approved  = array_filter( $schools, static fn( $s ) => 'approved' === $s['status'] );
		$certified = array_filter( $schools, static fn( $s ) => isset( $d['certs'][ (int) $s['id'] ] ) );
		$funnel    = array(
			array( 'Started registering', count( $schools ) ),
			array( 'Verified their email', count( array_filter( $schools, static fn( $s ) => '' !== (string) $s['code'] ) ) ),
			array( 'Headteacher approved', count( $approved ) ),
			array( 'Started a debate', count( $started ) ),
			array( 'Matched with an opponent', count( $matched ) ),
			array( 'Fixture agreed', count( $agreed ) ),
			array( 'Completed a debate', count( $done ) ),
			array( 'Earned the certificate', count( $certified ) ),
		);

		$by_status = array();
		$themes    = array_fill_keys( array_keys( AIADN_Motions::THEMES ), 0 );
		$ages      = array_fill_keys( array_keys( AIADN_Motions::AGES ), 0 );
		$formats   = array_fill_keys( array_keys( AIADN_Motions::FORMATS ), 0 );
		$students  = 0;
		$pairs     = array();
		$counting  = 0;
		$held      = 0;
		foreach ( $debates as $id => $x ) {
			$by_status[ $x['status'] ] = ( $by_status[ $x['status'] ] ?? 0 ) + 1;
			if ( $x['held'] ) {
				++$held;
			}
			if ( ! $x['counts'] ) {
				continue;
			}
			++$counting;
			if ( isset( $themes[ $x['theme'] ] ) ) {
				++$themes[ $x['theme'] ];
			}
			if ( isset( $ages[ $x['age_group'] ] ) ) {
				++$ages[ $x['age_group'] ];
			}
			if ( isset( $formats[ $x['format'] ] ) ) {
				++$formats[ $x['format'] ];
			}
			$students += (int) ( $d['cards'][ (int) $id ] ?? 0 );
			$a = (int) $x['school_a_id'];
			$b = (int) $x['school_b_id'];
			if ( $a && $b ) {
				$pairs[ min( $a, $b ) . '-' . max( $a, $b ) ] = array( $a, $b );
			}
		}
		$cross_mat = $cross_region = 0;
		foreach ( $pairs as $p ) {
			$sa = $d['schools'][ $p[0] ] ?? null;
			$sb = $d['schools'][ $p[1] ] ?? null;
			if ( ! $sa || ! $sb ) {
				continue;
			}
			if ( '' !== $sa['mat_key'] && '' !== $sb['mat_key'] && $sa['mat_key'] !== $sb['mat_key'] ) {
				++$cross_mat;
			}
			if ( AIADN_Regions::UNKNOWN !== $sa['region'] && AIADN_Regions::UNKNOWN !== $sb['region'] && $sa['region'] !== $sb['region'] ) {
				++$cross_region;
			}
		}

		$responses = 0;
		$unlocked  = 0;
		foreach ( $schools as $sid => $s ) {
			$n          = (int) ( $d['voice'][ (int) $sid ] ?? 0 );
			$responses += $n;
			if ( $n >= AIADN_Voice::MIN_RESPONSES ) {
				++$unlocked;
			}
		}

		$j = array( 'invited' => 0, 'accepted' => 0, 'declined' => 0, 'people' => 0, 'repeat' => 0, 'types' => array_fill_keys( array_keys( AIADN_Motions::JUDGE_TYPES ), 0 ) );
		$accepted_by = array();
		foreach ( $d['judges'] as $row ) {
			if ( ! isset( $debates[ (int) $row['debate_id'] ] ) ) {
				continue;
			}
			if ( 'accepted' === $row['status'] ) {
				++$j['accepted'];
				$accepted_by[ strtolower( $row['email'] ) ] = ( $accepted_by[ strtolower( $row['email'] ) ] ?? 0 ) + 1;
				if ( isset( $j['types'][ $row['judge_type'] ] ) ) {
					++$j['types'][ $row['judge_type'] ];
				}
			} elseif ( 'declined' === $row['status'] ) {
				++$j['declined'];
			} else {
				++$j['invited'];
			}
		}
		$j['people'] = count( $accepted_by );
		$j['repeat'] = count( array_filter( $accepted_by, static fn( $n ) => $n >= 2 ) );

		return array(
			'schools'      => count( $schools ),
			'approved'     => count( $approved ),
			'funnel'       => $funnel,
			'debates'      => count( $debates ),
			'by_status'    => $by_status,
			'counting'     => $counting,
			'held'         => $held,
			'themes'       => $themes,
			'themes_used'  => count( array_filter( $themes ) ),
			'ages'         => $ages,
			'formats'      => $formats,
			'students'     => $students,
			'connections'  => count( $pairs ),
			'cross_mat'    => $cross_mat,
			'cross_region' => $cross_region,
			'voice'        => $responses,
			'voice_schools' => $unlocked,
			'judges'       => $j,
		);
	}

	/**
	 * What students said, by age pathway, for a set of schools (or everyone).
	 *
	 * An age group is only shown once at least AIADN_Voice::MIN_RESPONSES students have answered, and, for
	 * a report that leaves the programme team, once they come from at least $min_schools schools, so no
	 * single school's results can be read off a partner's report. A question is shown once that many
	 * students have answered it. Percentages are of those who answered that question.
	 *
	 * @param array<int,true>|null $ids
	 * @return array<string,array{label:string,responses:int,schools:int,shown:bool,questions:array<string,?array{agree:int,unsure:int,disagree:int}>}>
	 */
	public static function voice_by_pathway( ?array $ids = null, int $min_schools = 1 ): array {
		$d    = self::load();
		$out  = array();
		$keys = array_keys( AIADN_Voice::QUESTIONS );
		foreach ( array( 'primary' => AIADN_Motions::AGES['primary'], 'secondary' => AIADN_Motions::AGES['secondary'], 'post16' => AIADN_Motions::AGES['post16'], 'unstated' => 'Did not say their year' ) as $p => $label ) {
			$out[ $p ] = array( 'label' => $label, 'responses' => 0, 'school_set' => array(), 'sums' => array_fill_keys( $keys, array( 1 => 0, 2 => 0, 3 => 0 ) ) );
		}
		foreach ( $d['voice_rows'] as $row ) {
			$sid = (int) $row['school_id'];
			if ( null !== $ids && ! isset( $ids[ $sid ] ) ) {
				continue;
			}
			$p                          = AIADN_Voice::pathway( (string) $row['year_group'] ) ?: 'unstated';
			$out[ $p ]['responses']    += (int) $row['n'];
			$out[ $p ]['school_set'][ $sid ] = true;
			foreach ( $keys as $k ) {
				foreach ( array( 1, 2, 3 ) as $v ) {
					$out[ $p ]['sums'][ $k ][ $v ] += (int) $row[ "{$k}_{$v}" ];
				}
			}
		}
		foreach ( $out as $p => $o ) {
			$schools = count( $o['school_set'] );
			$shown   = 'unstated' !== $p && $o['responses'] >= AIADN_Voice::MIN_RESPONSES && $schools >= $min_schools;
			$qs      = array();
			foreach ( $keys as $k ) {
				$sum      = $o['sums'][ $k ];
				$answered = $sum[1] + $sum[2] + $sum[3];
				$qs[ $k ] = ( $shown && $answered >= AIADN_Voice::MIN_RESPONSES )
					? array( 'agree' => (int) round( 100 * $sum[1] / $answered ), 'unsure' => (int) round( 100 * $sum[2] / $answered ), 'disagree' => (int) round( 100 * $sum[3] / $answered ) )
					: null;
			}
			$out[ $p ] = array( 'label' => $o['label'], 'responses' => $o['responses'], 'schools' => $schools, 'shown' => $shown, 'questions' => $qs );
		}
		return $out;
	}

	/**
	 * A table of groups: regions, trusts or partners, each with a few headline numbers.
	 *
	 * @param string $kind 'region', 'mat' or 'partner'
	 * @return array<int,array<string,mixed>> sorted by schools, largest first
	 */
	public static function groups( string $kind ): array {
		$d     = self::load();
		$field = array( 'region' => 'region', 'mat' => 'mat_key', 'partner' => 'par_key' )[ $kind ];
		$raw   = array( 'region' => 'region', 'mat' => 'mat_name', 'partner' => 'partner_ref' )[ $kind ];

		$rows   = array();
		$member = array();
		foreach ( $d['schools'] as $sid => $s ) {
			$key = (string) $s[ $field ];
			if ( '' === $key ) {
				continue;
			}
			$member[ (int) $sid ] = $key;
			if ( ! isset( $rows[ $key ] ) ) {
				$rows[ $key ] = array( 'key' => $key, 'label' => '', 'spellings' => array(), 'schools' => 0, 'approved' => 0, 'active' => 0, 'debates' => 0, 'students' => 0, 'voice' => 0 );
			}
			++$rows[ $key ]['schools'];
			$rows[ $key ]['approved'] += 'approved' === $s['status'] ? 1 : 0;
			$rows[ $key ]['voice']    += (int) ( $d['voice'][ (int) $sid ] ?? 0 );
			$spelling = trim( (string) $s[ $raw ] );
			$rows[ $key ]['spellings'][ $spelling ] = ( $rows[ $key ]['spellings'][ $spelling ] ?? 0 ) + 1;
		}
		$active = array();
		foreach ( $d['debates'] as $id => $x ) {
			$keys = array();
			foreach ( array( (int) $x['school_a_id'], (int) $x['school_b_id'] ) as $sid ) {
				if ( $sid && isset( $member[ $sid ] ) ) {
					$keys[ $member[ $sid ] ] = true;
					$active[ $sid ]          = true;
				}
			}
			if ( $x['counts'] ) {
				foreach ( array_keys( $keys ) as $key ) {
					++$rows[ $key ]['debates'];
					$rows[ $key ]['students'] += (int) ( $d['cards'][ (int) $id ] ?? 0 );
				}
			}
		}
		foreach ( array_keys( $active ) as $sid ) {
			++$rows[ $member[ $sid ] ]['active'];
		}
		foreach ( $rows as $key => $row ) {
			arsort( $row['spellings'] );
			$rows[ $key ]['label']     = (string) key( $row['spellings'] );
			$rows[ $key ]['variants']  = count( $row['spellings'] );
		}
		usort( $rows, static fn( $a, $b ) => $b['schools'] <=> $a['schools'] ?: strcmp( $a['label'], $b['label'] ) );
		return $rows;
	}

	/** The name to show for a group key, or the key itself if it is unknown. */
	public static function group_label( string $kind, string $key ): string {
		foreach ( self::groups( $kind ) as $row ) {
			if ( $row['key'] === $key ) {
				return $row['label'];
			}
		}
		return $key;
	}

	/**
	 * The schools in a trust, for the programme team's trust view. Names and codes only.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function schools_in( string $kind, string $key ): array {
		$d   = self::load();
		$ids = self::school_ids( $kind, $key ) ?? array();
		$out = array();
		foreach ( array_keys( $ids ) as $sid ) {
			$s        = $d['schools'][ $sid ];
			$debates  = 0;
			$students = 0;
			foreach ( $d['debates'] as $id => $x ) {
				if ( $x['counts'] && ( (int) $x['school_a_id'] === $sid || (int) $x['school_b_id'] === $sid ) ) {
					++$debates;
					$students += (int) ( $d['cards'][ (int) $id ] ?? 0 );
				}
			}
			$out[] = array( 'name' => $s['name'], 'code' => (string) $s['code'], 'status' => $s['status'], 'region' => $s['region'], 'age_phases' => $s['age_phases'], 'debates' => $debates, 'students' => $students, 'voice' => (int) ( $d['voice'][ $sid ] ?? 0 ) );
		}
		usort( $out, static fn( $a, $b ) => $b['debates'] <=> $a['debates'] ?: strcmp( $a['name'], $b['name'] ) );
		return $out;
	}

	/**
	 * Judges a partner has brought in: accepted judges who named the partner.
	 */
	public static function judges_from_partner( string $key ): int {
		$people = array();
		foreach ( self::load()['judges'] as $row ) {
			if ( 'accepted' === $row['status'] && '' !== $key && self::org_key( (string) $row['partner_ref'] ) === $key ) {
				$people[ strtolower( $row['email'] ) ] = true;
			}
		}
		return count( $people );
	}

	/* ------------------------------------------------------------------ */
	/* What needs attention                                                */
	/* ------------------------------------------------------------------ */

	/**
	 * Debates that are waiting on somebody, oldest first.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function stuck(): array {
		$d   = self::load();
		$out = array();
		foreach ( $d['debates'] as $x ) {
			if ( in_array( $x['status'], self::DONE, true ) ) {
				continue;
			}
			if ( 'ready' === $x['status'] ) {
				// Ready is fine until the day has passed with no result.
				$late = self::days_since( $x['starts_at'] );
				if ( ! $x['starts_at'] || strtotime( $x['starts_at'] . ' UTC' ) > time() || $late < self::RESULT_OVERDUE_DAYS ) {
					continue;
				}
				$days = $late;
				$what = 'Result overdue';
			} else {
				$days = self::days_since( $x['stage_at'] );
				if ( $days < self::STUCK_DAYS ) {
					continue;
				}
				$what = self::STATUS_LABELS[ $x['status'] ] ?? $x['status'];
			}
			$a     = $d['schools'][ (int) $x['school_a_id'] ] ?? null;
			$b     = $x['school_b_id'] ? ( $d['schools'][ (int) $x['school_b_id'] ] ?? null ) : null;
			$out[] = array( 'code' => $x['code'], 'what' => $what, 'days' => (int) floor( $days ), 'schools' => ( $a['name'] ?? '' ) . ( $b ? ' v ' . $b['name'] : '' ), 'reminders' => (int) $x['reminders_sent'] );
		}
		usort( $out, static fn( $p, $q ) => $q['days'] <=> $p['days'] );
		return $out;
	}

	/** Debates still in progress, whether or not they are stuck. */
	public static function in_progress(): int {
		return count( array_filter( self::load()['debates'], static fn( $x ) => ! in_array( $x['status'], self::DONE, true ) ) );
	}

	/**
	 * Incident workflow: counts, and how long the open ones have waited. No details of what happened.
	 *
	 * @return array<string,mixed>
	 */
	public static function incidents(): array {
		$d       = self::load();
		$open    = $resolved = 0;
		$oldest  = 0;
		$by_cat  = array();
		foreach ( $d['issues'] as $i ) {
			$by_cat[ $i['category'] ] = ( $by_cat[ $i['category'] ] ?? 0 ) + 1;
			if ( 'reported' === $i['status'] ) {
				++$open;
				$oldest = max( $oldest, (int) floor( self::days_since( $i['created_at'] ) ) );
			} else {
				++$resolved;
			}
		}
		arsort( $by_cat );
		return array( 'open' => $open, 'resolved' => $resolved, 'oldest_open_days' => $oldest, 'categories' => $by_cat );
	}

	/**
	 * Things to tidy: records that will skew the figures if left.
	 *
	 * @return array<string,mixed>
	 */
	public static function data_quality(): array {
		$d       = self::load();
		$waiting = $unverified = $no_postcode = $inactive = array();
		$names   = array();
		$has_debate = array();
		foreach ( $d['debates'] as $x ) {
			$has_debate[ (int) $x['school_a_id'] ] = true;
			if ( $x['school_b_id'] ) {
				$has_debate[ (int) $x['school_b_id'] ] = true;
			}
		}
		foreach ( $d['schools'] as $sid => $s ) {
			if ( 'rejected' !== $s['status'] && 'pending_email' !== $s['status'] ) {
				$names[ $s['name_key'] ][] = $s['name'];
			}
			if ( 'pending_slt' === $s['status'] && self::days_since( $s['created_at'] ) >= self::STALE_DAYS ) {
				$waiting[] = array( 'name' => $s['name'], 'days' => (int) floor( self::days_since( $s['created_at'] ) ) );
			}
			if ( 'pending_email' === $s['status'] && self::days_since( $s['created_at'] ) >= self::STALE_DAYS ) {
				$unverified[] = array( 'name' => $s['name'], 'days' => (int) floor( self::days_since( $s['created_at'] ) ) );
			}
			if ( 'rejected' !== $s['status'] && AIADN_Regions::UNKNOWN === $s['region'] ) {
				$no_postcode[] = $s['name'];
			}
			if ( 'approved' === $s['status'] && ! isset( $has_debate[ (int) $sid ] ) && self::days_since( $s['slt_approved_at'] ?: $s['created_at'] ) >= 30 ) {
				$inactive[] = array( 'name' => $s['name'], 'days' => (int) floor( self::days_since( $s['slt_approved_at'] ?: $s['created_at'] ) ) );
			}
		}
		$dupes = array();
		foreach ( $names as $key => $list ) {
			if ( count( $list ) > 1 ) {
				$dupes[] = array( 'name' => $list[0], 'copies' => count( $list ) );
			}
		}
		$variants = array();
		foreach ( array( 'mat' => 'Trust', 'partner' => 'Partner' ) as $kind => $label ) {
			foreach ( self::groups( $kind ) as $g ) {
				if ( $g['variants'] > 1 ) {
					$variants[] = array( 'kind' => $label, 'names' => implode( ' / ', array_keys( $g['spellings'] ) ) );
				}
			}
		}
		$sort = static function ( array $rows ): array {
			usort( $rows, static fn( $a, $b ) => ( $b['days'] ?? 0 ) <=> ( $a['days'] ?? 0 ) );
			return $rows;
		};
		return array(
			'awaiting_slt'  => $sort( $waiting ),
			'unverified'    => $sort( $unverified ),
			'duplicates'    => $dupes,
			'no_region'     => $no_postcode,
			'inactive'      => $sort( $inactive ),
			'variants'      => $variants,
		);
	}

	/** How easy teachers and judges found it. Counts and averages only. */
	public static function ratings(): array {
		$out = array( 'teacher' => array( 'n' => 0, 'avg' => 0.0 ), 'judge' => array( 'n' => 0, 'avg' => 0.0 ) );
		foreach ( self::load()['rates'] as $r ) {
			if ( isset( $out[ $r['who'] ] ) ) {
				$out[ $r['who'] ] = array( 'n' => (int) $r['n'], 'avg' => round( (float) $r['avg'], 1 ) );
			}
		}
		return $out;
	}
}
