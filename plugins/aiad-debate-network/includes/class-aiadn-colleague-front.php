<?php
/**
 * A colleague whose email is not on the school's domain asks to join; the school's lead approves.
 *
 *   /conversation/colleague/            ask to join (school code, name, email)
 *   /conversation/colleague/?t=TOKEN    the lead's page: approve or decline (looking changes nothing)
 *
 * Colleagues on the lead's own (non-free) email domain still join straight away at the front door. This is
 * for everyone else: a teacher with a personal or different address, or a supply teacher.
 *
 * The person asking always gets the same answer, whether or not the school code matches, so the page
 * cannot be used to find out which schools exist. They can only sign in once the lead has approved and they
 * have proved the address is theirs with the emailed code, like everyone else.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Colleague_Front {

	const TTL = 7 * DAY_IN_SECONDS;

	private static function esc( $v ): string {
		return esc_html( (string) $v );
	}

	public static function view_colleague(): string {
		AIADN_Front::set_title( 'Join your school' );

		if ( AIADN_Front::is_post() ) {
			$action = AIADN_Front::post( 'aiadn_action' );
			if ( 'colleague_request' === $action ) {
				return self::handle_request();
			}
			if ( 'colleague_decide' === $action ) {
				return self::handle_decision();
			}
		}
		if ( '' !== AIADN_Front::get( 't' ) ) {
			return self::render_decision();
		}
		return self::render_request( '', array( 'school_code' => AIADN_Front::get( 'c' ) ) );
	}

	/* ------------------------------------------------------------------ */
	/* The colleague asks                                                  */
	/* ------------------------------------------------------------------ */

	private static function render_request( string $error, array $v = array() ): string {
		$val = static fn( string $k ): string => esc_attr( (string) ( $v[ $k ] ?? '' ) );
		$h   = '<h1>Ask to join your school</h1>';
		$h  .= '<p class="aiadn__small"><a href="' . esc_url( AIADN_Front::url( 'join' ) ) . '">&larr; The front door</a></p>';
		$h  .= '<p>If your school email address is on the same domain as your school lead&rsquo;s, you can go straight in from <a href="' . esc_url( AIADN_Front::url( 'join' ) ) . '">the front door</a>. If it is not, or you use a different address, ask here and your school lead will be emailed to approve you.</p>';
		$h  .= AIADN_Front::notice( $error, 'error' );
		$h  .= '<form method="post" action="' . esc_url( AIADN_Front::url( 'colleague' ) ) . '" class="aiadn__form"><input type="hidden" name="aiadn_action" value="colleague_request">' . AIADN_Front::honeypot();
		$h  .= '<label for="c-code">School code</label><input id="c-code" name="school_code" type="text" value="' . $val( 'school_code' ) . '" autocomplete="off" required>';
		$h  .= '<label for="c-name">Your name</label><input id="c-name" name="name" type="text" value="' . $val( 'name' ) . '" autocomplete="name" required>';
		$h  .= '<label for="c-email">Your email address</label><input id="c-email" name="email" type="email" value="' . $val( 'email' ) . '" autocomplete="email" required>';
		$h  .= '<button class="aiadn__button" type="submit">Ask my school lead to approve me</button></form>';
		return $h;
	}

	private static function handle_request(): string {
		if ( AIADN_Front::honeypot_tripped() ) {
			return self::thanks();
		}
		if ( ! AIADN_Util::allow( 'colleague|' . AIADN_Util::client_ip(), 10, HOUR_IN_SECONDS ) ) {
			return '<h1>Please wait</h1><p>' . esc_html( AIADN_Front::message( 'slow' ) ) . '</p>';
		}
		$code  = AIADN_Util::normalise_school_code( AIADN_Front::post( 'school_code' ) );
		$name  = AIADN_Front::post( 'name' );
		$email = AIADN_Util::normalise_email( AIADN_Front::post( 'email' ) );
		$v     = array( 'school_code' => AIADN_Front::post( 'school_code' ), 'name' => $name, 'email' => $email );
		if ( '' === $name || ! is_email( $email ) || '' === $code ) {
			return self::render_request( 'Enter your school code, your name and a valid email address.', $v );
		}
		$name = mb_substr( $name, 0, 120 );

		$school = AIADN_Schools::get_by_code( $code );
		if ( $school && 'approved' === $school['status'] && AIADN_Util::allow( 'colleague-school|' . $school['id'], 10, DAY_IN_SECONDS ) ) {
			$lead     = AIADN_Schools::lead( (int) $school['id'] );
			$existing = AIADN_Schools::find_member( (int) $school['id'], $email );
			if ( $lead && ! $existing ) {
				$member_id = AIADN_Schools::add_member( (int) $school['id'], $email, $name, '', 'pending', false );
				self::ask_lead( $school, $lead, $member_id, $name, $email );
			} elseif ( $lead && $existing && 'pending' === $existing['role'] && AIADN_Util::allow( 'colleague-again|' . $existing['id'], 2, DAY_IN_SECONDS ) ) {
				self::ask_lead( $school, $lead, (int) $existing['id'], $existing['name'] ?: $name, $email );
			}
		}
		return self::thanks();
	}

	private static function ask_lead( array $school, array $lead, int $member_id, string $name, string $email ): void {
		$token = AIADN_Auth::issue_token( (int) $school['id'], 'colleague', self::TTL, $member_id );
		$body  = "{$name} ({$email}) has asked to join {$school['name']} on the National AI Conversation, as a teacher.\n\n";
		$body .= "If you know them, please approve them. They will then be able to sign in with a code sent to their email, start debates and run the class PIN for your school.\n\n";
		$body .= "If you do not know them, ignore this email or choose to decline. Nothing happens until you decide.\n\n";
		$body .= AIADN_Front::url( 'colleague', array( 't' => $token ) ) . "\n\nThis link works for 7 days. You can also decide from your school page.";
		AIADN_Mailer::send_notice( $lead['email'], 'Please approve a colleague: ' . $name, $body );
	}

	private static function thanks(): string {
		$h  = '<h1>Thank you</h1>';
		$h .= '<p>If that school code matches an approved school, we have asked its lead teacher to approve you. When they do, you will get an email.</p>';
		$h .= '<p>Then go to <a href="' . esc_url( AIADN_Front::url( 'join' ) ) . '">the front door</a>, choose Teacher, and sign in with your email address and the school code.</p>';
		return $h;
	}

	/* ------------------------------------------------------------------ */
	/* The lead decides                                                    */
	/* ------------------------------------------------------------------ */

	/** The pending colleague a token is for, or null. */
	private static function pending_for( ?array $token ): ?array {
		if ( ! $token ) {
			return null;
		}
		$member = AIADN_Schools::get_member( (int) $token['ref_id'] );
		return ( $member && (int) $member['school_id'] === (int) $token['school_id'] && 'pending' === $member['role'] ) ? $member : null;
	}

	private static function render_decision(): string {
		$token  = AIADN_Auth::find_token( AIADN_Front::get( 't' ), 'colleague' );
		$member = self::pending_for( $token );
		$school = $token ? AIADN_Schools::get( (int) $token['school_id'] ) : null;
		if ( ! $member || ! $school ) {
			return '<h1>This link is no longer valid</h1><p>It may have expired, or the request may already have been decided. You can also decide from your school page.</p>';
		}
		// Looking is free. Only a button changes anything.
		$h  = '<h1>Approve ' . self::esc( $member['name'] ) . '?</h1>';
		$h .= '<p><strong>' . self::esc( $member['name'] ) . '</strong> (' . self::esc( $member['email'] ) . ') has asked to join <strong>' . self::esc( $school['name'] ) . '</strong> as a teacher.</p>';
		$h .= '<p>Approving lets them sign in with a code sent to that address, start debates and run the class PIN for your school.</p>';
		$h .= '<form method="post" action="' . esc_url( AIADN_Front::url( 'colleague' ) ) . '" class="aiadn__form aiadn__form--row"><input type="hidden" name="aiadn_action" value="colleague_decide"><input type="hidden" name="t" value="' . esc_attr( AIADN_Front::get( 't' ) ) . '">';
		$h .= '<button type="submit" name="decision" value="approve" class="aiadn__button">Approve</button> <button type="submit" name="decision" value="decline" class="aiadn__button aiadn__button--quiet">I do not know them</button></form>';
		return $h;
	}

	private static function handle_decision(): string {
		if ( ! AIADN_Util::allow( 'colleague-decide|' . AIADN_Util::client_ip(), 30, 600 ) ) {
			return '<h1>Please wait</h1><p>' . esc_html( AIADN_Front::message( 'slow' ) ) . '</p>';
		}
		$gone   = '<h1>This link is no longer valid</h1><p>It may have expired, or the request may already have been decided.</p>';
		$token  = AIADN_Auth::find_token( AIADN_Front::post( 't' ), 'colleague' );
		$member = self::pending_for( $token );
		if ( ! $member || ! AIADN_Auth::consume_token( (int) $token['id'] ) ) {
			return $gone;
		}
		$school = AIADN_Schools::get( (int) $token['school_id'] );
		if ( ! $school ) {
			return $gone;
		}
		$approve = 'approve' === AIADN_Front::post( 'decision' );
		self::decide( $school, $member, $approve );
		return $approve
			? '<h1>Approved</h1><p>' . self::esc( $member['name'] ) . ' can now sign in. We have told them.</p>'
			: '<h1>Thank you</h1><p>We have not added ' . self::esc( $member['name'] ) . '.</p>';
	}

	/** Approve or decline a pending colleague, and tell them. */
	public static function decide( array $school, array $member, bool $approve ): void {
		if ( 'pending' !== $member['role'] ) {
			return;
		}
		if ( $approve ) {
			AIADN_Schools::set_member_role( (int) $member['id'], 'teacher' );
			AIADN_Mailer::send_notice( $member['email'], 'You can now join ' . $school['name'], "Your school lead has approved you.\n\nTo sign in, go to the front door, choose Teacher, and enter the school code and this email address. We will email you a code.\n\n" . AIADN_Front::url( 'join', array( 'c' => $school['code'] ) ) );
		} else {
			AIADN_Schools::delete_member( (int) $member['id'] );
			AIADN_Mailer::send_notice( $member['email'], 'Your request to join ' . $school['name'], "Your school lead has not approved your request to join {$school['name']} on the National AI Conversation.\n\nIf you think that is a mistake, please speak to them directly." );
		}
	}

	/* ------------------------------------------------------------------ */
	/* On the lead's dashboard                                             */
	/* ------------------------------------------------------------------ */

	/** Colleagues waiting for approval. Only the lead sees this. */
	public static function pending_panel( int $school_id ): string {
		$pending = array_filter( AIADN_Schools::members( $school_id ), static fn( $m ) => 'pending' === $m['role'] );
		if ( ! $pending ) {
			return '';
		}
		$h = '<div class="aiadn__panel"><h2>Colleagues waiting for you</h2><p class="aiadn__small">They asked to join your school with an email address that is not on your domain. Only approve people you know.</p>';
		foreach ( $pending as $m ) {
			$h .= '<form method="post" action="' . esc_url( AIADN_Front::url( 'school' ) ) . '" class="aiadn__form aiadn__form--row">' . AIADN_Front::csrf_field() . '<input type="hidden" name="aiadn_action" value="colleague_decide"><input type="hidden" name="member_id" value="' . (int) $m['id'] . '">';
			$h .= '<span><strong>' . self::esc( $m['name'] ?: 'Colleague' ) . '</strong> ' . self::esc( $m['email'] ) . '</span> <button type="submit" name="decision" value="approve" class="aiadn__button">Approve</button> <button type="submit" name="decision" value="decline" class="aiadn__button aiadn__button--quiet">Decline</button></form>';
		}
		return $h . '</div>';
	}

	/** The lead pressed Approve or Decline on their dashboard. */
	public static function decide_from_dashboard( array $school, int $member_id, bool $approve ): void {
		$member = AIADN_Schools::get_member( $member_id );
		if ( $member && (int) $member['school_id'] === (int) $school['id'] ) {
			self::decide( $school, $member, $approve );
		}
	}
}
