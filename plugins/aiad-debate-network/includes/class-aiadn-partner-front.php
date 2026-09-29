<?php
/**
 * The page an introducing organisation sees, by link from an email. No account and no password.
 *
 *   /conversation/partner/?t=TOKEN   its dashboard: totals for the schools and judges it introduced
 *   /conversation/partner/?r=TOKEN   stop these emails, or say "this wasn't us"
 *   /conversation/partner/           ask for a new dashboard link, by email
 *
 * It only shows what a school's headteacher, or a judge, agreed to share, and only totals.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Partner_Front {

	private static function esc( $v ): string {
		return esc_html( (string) $v );
	}

	public static function view_partner(): string {
		AIADN_Front::set_title( 'Your dashboard' );

		if ( AIADN_Front::is_post() ) {
			if ( ! AIADN_Util::allow( 'partner|' . AIADN_Util::client_ip(), 30, 600 ) ) {
				return '<h1>Please wait</h1><p>' . esc_html( AIADN_Front::message( 'slow' ) ) . '</p>';
			}
			$action = AIADN_Front::post( 'aiadn_action' );
			if ( 'partner_link' === $action ) {
				return self::handle_link_request();
			}
			if ( in_array( $action, array( 'referral_stop', 'referral_disown' ), true ) ) {
				return self::handle_manage( $action );
			}
		}
		if ( '' !== AIADN_Front::get( 't' ) ) {
			return self::dashboard();
		}
		if ( '' !== AIADN_Front::get( 'r' ) ) {
			return self::manage_page();
		}
		return self::link_form();
	}

	/* ------------------------------------------------------------------ */
	/* The dashboard                                                       */
	/* ------------------------------------------------------------------ */

	private static function dashboard(): string {
		$token   = AIADN_Auth::find_token( AIADN_Front::get( 't' ), 'partner' );
		$partner = $token ? AIADN_Referrals::partner( (int) $token['ref_id'] ) : null;
		if ( ! $partner ) {
			return '<h1>This link is no longer valid</h1><p>Dashboard links work for 30 days. <a href="' . esc_url( AIADN_Front::url( 'partner' ) ) . '">Ask for a new one</a> and we will email it to you.</p>';
		}
		AIADN_Front::set_title( $partner['name'] );
		$h  = '<h1>' . self::esc( $partner['name'] ) . '</h1>';
		$h .= '<p class="aiadn__small aiadn__noprint">This link works for 30 days. <a href="' . esc_url( AIADN_Front::url( 'partner' ) ) . '">Ask for a new one</a> by email at any time.</p>';
		return $h . AIADN_Programme_Front::partner_body( (string) $partner['id'], $partner['name'] );
	}

	/* ------------------------------------------------------------------ */
	/* Stop the emails, or "this wasn't us"                                */
	/* ------------------------------------------------------------------ */

	private static function referral_from( string $raw ): ?array {
		$token = AIADN_Auth::find_token( $raw, 'referral' );
		return $token ? AIADN_Referrals::get( (int) $token['ref_id'] ) : null;
	}

	private static function manage_page( string $message = '' ): string {
		$ref = self::referral_from( AIADN_Front::get( 'r' ) );
		if ( ! $ref ) {
			return '<h1>This link is no longer valid</h1><p>Email us and we will sort it out.</p>';
		}
		$who = 'judge' === $ref['subject_type'] ? 'A judge' : ( AIADN_Schools::get( (int) $ref['subject_id'] )['name'] ?? 'A school' );
		$h   = '<h1>Your emails from the National AI Conversation</h1>' . AIADN_Front::notice( $message, 'info' );
		$h  .= '<p><strong>' . self::esc( $who ) . '</strong> named <strong>' . self::esc( $ref['org_name'] ) . '</strong> (' . self::esc( $ref['referrer_email'] ) . ') as having introduced them.</p>';
		$form = static fn( string $action, string $label, string $note, bool $quiet ): string => '<form method="post" action="' . esc_url( AIADN_Front::url( 'partner' ) ) . '" class="aiadn__form"><input type="hidden" name="aiadn_action" value="' . esc_attr( $action ) . '"><input type="hidden" name="r" value="' . esc_attr( AIADN_Front::get( 'r' ) ) . '"><p class="aiadn__small">' . esc_html( $note ) . '</p><button class="aiadn__button' . ( $quiet ? ' aiadn__button--quiet' : '' ) . '" type="submit">' . esc_html( $label ) . '</button></form>';
		if ( 'shared' === $ref['status'] && ! AIADN_Referrals::is_stopped( $ref['referrer_email'] ) ) {
			$h .= $form( 'referral_stop', 'Stop these emails', 'You will get no more updates by email. Your dashboard link keeps working until it expires.', true );
		} elseif ( 'shared' === $ref['status'] ) {
			$h .= '<p>These emails have been stopped.</p>';
		}
		if ( 'shared' === $ref['status'] ) {
			$h .= $form( 'referral_disown', 'This was not us', 'The school (or judge) will be taken out of your numbers and we will tell the programme team. Use this if you did not introduce them.', true );
		} elseif ( 'disowned' === $ref['status'] ) {
			$h .= '<p>You told us this was not you. It has been removed from your numbers.</p>';
		}
		return $h;
	}

	private static function handle_manage( string $action ): string {
		$ref = self::referral_from( AIADN_Front::post( 'r' ) );
		if ( ! $ref ) {
			return '<h1>This link is no longer valid</h1>';
		}
		if ( 'referral_disown' === $action ) {
			AIADN_Referrals::disown( $ref );
			return '<h1>Thank you</h1><p>We have taken them out of your numbers and told the programme team. You will get no more emails about them.</p>';
		}
		AIADN_Referrals::stop_emails( $ref['referrer_email'] );
		return '<h1>Done</h1><p>You will get no more update emails at ' . self::esc( $ref['referrer_email'] ) . '. Your dashboard link keeps working until it expires.</p>';
	}

	/* ------------------------------------------------------------------ */
	/* A new link                                                          */
	/* ------------------------------------------------------------------ */

	private static function link_form(): string {
		$h  = '<h1>Your dashboard</h1>';
		$h .= '<p>If a school or judge told us you introduced them, and their headteacher (or the judge) agreed we could tell you, we can email you a link to your dashboard.</p>';
		$h .= '<form method="post" action="' . esc_url( AIADN_Front::url( 'partner' ) ) . '" class="aiadn__form"><input type="hidden" name="aiadn_action" value="partner_link">' . AIADN_Front::honeypot();
		$h .= '<label for="pl-email">Your email address</label><input id="pl-email" name="email" type="email" autocomplete="email" required>';
		$h .= '<button class="aiadn__button" type="submit">Email me a link</button></form>';
		return $h;
	}

	private static function handle_link_request(): string {
		$email = AIADN_Util::normalise_email( AIADN_Front::post( 'email' ) );
		if ( ! AIADN_Front::honeypot_tripped() && is_email( $email ) && AIADN_Util::allow( 'partner-link|' . $email, 3, DAY_IN_SECONDS ) ) {
			AIADN_Referrals::send_dashboard_link( $email );
		}
		return '<h1>Check your email</h1><p>If that address has been named by a school or judge that agreed to tell you, we have sent a link. It can take a minute to arrive.</p>';
	}
}
