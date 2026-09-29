<?php
/**
 * The go-live check: whether this site is set up to run the National AI Conversation for real.
 *
 * Shown to the programme team. Each line is ok, warn (works, but look at it) or fail (will break something).
 * It never shows a password or a key.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AIADN_Golive {

	/** @return array<int,array{label:string,status:string,detail:string}> */
	public static function checks(): array {
		$out = array();
		$add = static function ( string $label, string $status, string $detail ) use ( &$out ): void {
			$out[] = array( 'label' => $label, 'status' => $status, 'detail' => $detail );
		};

		// Email.
		$route = AIADN_Mailer::route();
		if ( 'SMTP from wp-config.php' === $route['kind'] ) {
			$add( 'Email service', 'ok', 'Emails go out through ' . $route['detail'] . '.' );
		} elseif ( 'local test inbox' === $route['kind'] ) {
			$add( 'Email service', 'warn', 'Emails go to a local test inbox, not to people. Fine on a development copy, wrong on the live site.' );
		} else {
			$add( 'Email service', 'warn', 'Using the site\'s default mail. Add the Brevo login to wp-config.php (AIADN_SMTP_HOST, _PORT, _USER, _PASS), then use the test email below.' );
		}
		$from_ok = defined( 'AIADN_MAIL_FROM' ) && is_email( AIADN_MAIL_FROM );
		$add( 'Sender address', $from_ok ? 'ok' : 'warn', $from_ok ? 'Emails come from ' . AIADN_MAIL_FROM . '.' : 'AIADN_MAIL_FROM is not set, so emails come from ' . $route['from'] . '. Set it to the address verified in Brevo.' );
		$team_ok = defined( 'AIADN_TEAM_EMAIL' ) && is_email( AIADN_TEAM_EMAIL );
		$add( 'Programme team address', $team_ok ? 'ok' : 'warn', $team_ok ? 'Incident copies and deletion notices go to ' . AIADN_TEAM_EMAIL . '.' : 'AIADN_TEAM_EMAIL is not set, so they go to the site admin address (' . AIADN_Util::team_email() . ').' );

		// SPF and DMARC for the sending domain, where this server can look them up.
		$domain = substr( strrchr( $route['from'], '@' ), 1 );
		if ( $domain && function_exists( 'dns_get_record' ) ) {
			$txt   = @dns_get_record( $domain, DNS_TXT ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			$dmarc = @dns_get_record( '_dmarc.' . $domain, DNS_TXT ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			if ( false === $txt || array() === $txt && false === $dmarc ) {
				$add( 'SPF and DMARC', 'warn', 'This server could not look up ' . $domain . '. Check SPF, DKIM and DMARC in Brevo instead.' );
			} else {
				$has = static fn( $records, $needle ) => (bool) array_filter( (array) $records, static fn( $r ) => isset( $r['txt'] ) && 0 === stripos( $r['txt'], $needle ) );
				$spf = $has( $txt, 'v=spf1' );
				$dm  = $has( $dmarc, 'v=DMARC1' );
				$add( 'SPF and DMARC', ( $spf && $dm ) ? 'ok' : 'warn', $domain . ': SPF ' . ( $spf ? 'found' : 'not found' ) . ', DMARC ' . ( $dm ? 'found' : 'not found' ) . '. DKIM cannot be checked from here: confirm it in Brevo.' );
			}
		}

		// Time and scheduled tasks.
		$tz = wp_timezone_string();
		$add( 'Time zone', 'Europe/London' === $tz ? 'ok' : 'warn', 'WordPress is set to ' . ( $tz ?: 'UTC' ) . '. Debate times are shown in this zone; it should be Europe/London.' );
		$next_rem = wp_next_scheduled( AIADN_Reminders::HOOK );
		$next_ret = wp_next_scheduled( AIADN_Privacy::HOOK );
		$add( 'Scheduled tasks', ( $next_rem && $next_ret ) ? 'ok' : 'fail', ( $next_rem && $next_ret ) ? 'Reminders and the end-of-campaign deletion are both scheduled.' : 'A scheduled task is missing. Deactivate and reactivate the plugin.' );
		$last = (int) get_option( 'aiadn_cron_last_run', 0 );
		if ( $last && time() - $last < 3 * HOUR_IN_SECONDS ) {
			$add( 'Scheduled tasks are running', 'ok', 'The reminders last ran ' . human_time_diff( $last ) . ' ago.' );
		} else {
			$add( 'Scheduled tasks are running', 'warn', ( $last ? 'The reminders last ran ' . human_time_diff( $last ) . ' ago.' : 'The reminders have not run yet.' ) . ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ? ' WP-Cron is switched off here, so the server must call wp-cron.php every few minutes.' : ' WordPress only runs them when someone visits, so have the server call wp-cron.php every few minutes.' ) );
		}

		// Address and security.
		$https = 0 === strpos( home_url(), 'https://' );
		$add( 'HTTPS', $https ? 'ok' : 'fail', $https ? 'The site address is https.' : 'The site address is not https. Sign-in cookies and emailed links should only travel over https.' );
		$add( 'Permalinks', get_option( 'permalink_structure' ) ? 'ok' : 'fail', get_option( 'permalink_structure' ) ? 'Pretty permalinks are on, which the /conversation/ pages need.' : 'Permalinks are plain. Choose any other structure in Settings.' );
		$rules = (array) get_option( 'rewrite_rules', array() );
		$route_ok = (bool) array_filter( array_keys( $rules ), static fn( $k ) => 0 === strpos( (string) $k, '^conversation/(' ) );
		$add( 'The /conversation/ pages', $route_ok ? 'ok' : 'fail', $route_ok ? 'Their addresses are registered.' : 'Their addresses are not registered. Save Settings, Permalinks once.' );
		$debug = defined( 'WP_DEBUG' ) && WP_DEBUG && 'production' === wp_get_environment_type();
		$add( 'Debug mode', $debug ? 'warn' : 'ok', $debug ? 'WP_DEBUG is on in production. Turn it off.' : 'Off, or not a production site.' );

		// The plugin.
		$tables = AIADN_Database::tables_exist();
		$dbver  = (int) get_option( AIADN_Database::OPTION, 0 );
		$add( 'Database', ( $tables && $dbver >= AIADN_Database::DB_VERSION ) ? 'ok' : 'fail', 'Version ' . $dbver . ' of ' . AIADN_Database::DB_VERSION . ( $tables ? '.' : ', and the tables are missing.' ) );
		$add( 'Plugin version', 'ok', AIADN_VERSION . ' (loaded from ' . ( 0 === strpos( wp_normalize_path( AIADN_PLUGIN_DIR ), wp_normalize_path( WP_PLUGIN_DIR ) ) ? 'wp-content/plugins' : 'the theme' ) . ').' );

		// Dates.
		$ret = AIADN_Privacy::retention_date();
		$add( 'Deletion date', ( wp_date( 'Y-m-d' ) <= $ret ) ? 'ok' : 'warn', 'Personal details are deleted the day after ' . wp_date( 'j F Y', strtotime( $ret ) ) . '.' );
		$cd = function_exists( 'aiad_national_conversation_countdown' ) ? aiad_national_conversation_countdown() : null;
		$add( 'Homepage countdown', $cd ? 'ok' : 'warn', $cd ? $cd['label'] . ' ' . wp_date( 'j F Y', (int) ( $cd['ts_ms'] / 1000 ) ) . '. Check that this is the date you mean.' : 'The countdown is not showing: both of its dates have passed.' );

		return $out;
	}

	/** Worst status first, for the section heading. */
	public static function worst( array $checks ): string {
		$order = array( 'ok' => 0, 'warn' => 1, 'fail' => 2 );
		$w     = 'ok';
		foreach ( $checks as $c ) {
			if ( $order[ $c['status'] ] > $order[ $w ] ) {
				$w = $c['status'];
			}
		}
		return $w;
	}
}
