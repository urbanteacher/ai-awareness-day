<?php
/**
 * /conversation/nominate/  Nominate a school you work with. See AIADN_Nominations for how it is kept safe.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Nominate_Front {

	private static function esc( $v ): string {
		return esc_html( (string) $v );
	}

	public static function view_nominate(): string {
		AIADN_Front::set_title( 'Nominate a school' );

		if ( '' !== AIADN_Front::get( 'stop' ) || 'nominate_stop' === AIADN_Front::post( 'aiadn_action' ) ) {
			return self::stop_page();
		}
		if ( AIADN_Front::is_post() ) {
			if ( ! AIADN_Util::allow( 'nominate|' . AIADN_Util::client_ip(), 10, HOUR_IN_SECONDS ) ) {
				return '<h1>Please wait</h1><p>' . esc_html( AIADN_Front::message( 'slow' ) ) . '</p>';
			}
			$action = AIADN_Front::post( 'aiadn_action' );
			if ( 'nominate' === $action ) {
				return self::handle_form();
			}
			if ( 'nominate_confirm' === $action ) {
				return self::handle_confirm();
			}
		}
		if ( 'code' === AIADN_Front::get( 'step' ) ) {
			return self::code_form( AIADN_Front::get( 'ref' ), '' );
		}
		return self::form( '', array() );
	}

	private static function form( string $error, array $v ): string {
		$val = static fn( string $k ): string => esc_attr( (string) ( $v[ $k ] ?? '' ) );
		$h   = '<h1>Nominate a school</h1>';
		$h  .= '<p>Do you work with a school that would like to take part in the National AI Conversation? Tell us who to invite. We will send the school one short email, and it decides for itself whether to register.</p>';
		$h  .= '<div class="aiadn__panel"><h2>How this works</h2><ul class="aiadn__list"><li>We first check that your own email address is yours, with a code.</li><li>The school gets one email that says who nominated it. We do not send your message, so you cannot add a link or a pitch.</li><li>We never email the same school twice, and never again once it asks us not to.</li><li>Nominating gives you no access to anything about the school. Its headteacher approves it, and can choose whether you are told when it takes part.</li></ul></div>';
		$h  .= AIADN_Front::notice( $error, 'error' );
		$h  .= '<form method="post" action="' . esc_url( AIADN_Front::url( 'nominate' ) ) . '" class="aiadn__form"><input type="hidden" name="aiadn_action" value="nominate">' . AIADN_Front::honeypot();
		$h  .= '<label for="n-name">Your name</label><input id="n-name" name="name" type="text" value="' . $val( 'name' ) . '" autocomplete="name" required>';
		$h  .= '<label for="n-email">Your email address</label><input id="n-email" name="email" type="email" value="' . $val( 'email' ) . '" autocomplete="email" required>';
		$h  .= '<label for="n-org">Your organisation <span class="aiadn__opt">(optional)</span></label><input id="n-org" name="org" type="text" value="' . $val( 'org' ) . '">';
		$h  .= '<label for="n-school">The school\'s name</label><input id="n-school" name="school" type="text" value="' . $val( 'school' ) . '" required>';
		$h  .= '<label for="n-semail">An email address at the school <span class="aiadn__opt">(the headteacher or the school office)</span></label><input id="n-semail" name="school_email" type="email" value="' . $val( 'school_email' ) . '" required>';
		$h  .= '<p class="aiadn__small">You are responsible for having good reason to give us this address. It must be on the school\'s own domain, not a personal mailbox. We delete it as soon as the invitation is sent.</p>';
		return $h . '<button class="aiadn__button" type="submit">Email me a code</button></form>';
	}

	private static function handle_form(): string {
		$v = array( 'name' => AIADN_Front::post( 'name' ), 'email' => AIADN_Front::post( 'email' ), 'org' => AIADN_Front::post( 'org' ), 'school' => AIADN_Front::post( 'school' ), 'school_email' => AIADN_Front::post( 'school_email' ) );
		if ( AIADN_Front::honeypot_tripped() ) {
			return self::form( '', array() );
		}
		$id = AIADN_Nominations::create( $v['name'], $v['email'], $v['org'], $v['school'], $v['school_email'] );
		if ( is_string( $id ) ) {
			return self::form( $id, $v );
		}
		$ref = AIADN_Nominations::send_code( $id );
		AIADN_Front::redirect( 'nominate', array( 'step' => 'code', 'ref' => $ref ) );
		return '';
	}

	private static function code_form( string $ref, string $error ): string {
		$ref = preg_replace( '/[^a-f0-9]/', '', strtolower( $ref ) );
		$h   = '<h1>Check your email</h1><p>We have sent a 6-digit code to the address you gave. Enter it to confirm the address is yours and we will invite the school.</p>';
		$h  .= AIADN_Front::notice( $error, 'error' );
		$h  .= '<form method="post" action="' . esc_url( AIADN_Front::url( 'nominate' ) ) . '" class="aiadn__form"><input type="hidden" name="aiadn_action" value="nominate_confirm"><input type="hidden" name="ref" value="' . esc_attr( $ref ) . '">';
		$h  .= '<label for="n-code">Code</label><input id="n-code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required>';
		return $h . '<button class="aiadn__button" type="submit">Confirm and invite the school</button></form>';
	}

	private static function handle_confirm(): string {
		if ( ! AIADN_Nominations::confirm( AIADN_Front::post( 'ref' ), AIADN_Front::post( 'code' ) ) ) {
			return self::code_form( AIADN_Front::post( 'ref' ), "We couldn't match those details." );
		}
		return '<h1>Thank you</h1><p>If the school has not asked us to leave it alone, we have emailed it one invitation. It is up to the school whether to register, and we will not chase it.</p>';
	}

	/** "Do not contact me": looking changes nothing; the button does. */
	private static function stop_page(): string {
		$raw = AIADN_Front::get( 'stop' ) ?: AIADN_Front::post( 'stop' );
		$tok = AIADN_Auth::find_token( $raw, 'nomstop' );
		$n   = $tok ? AIADN_Nominations::get( (int) $tok['ref_id'] ) : null;
		if ( ! $n ) {
			return '<h1>This link is no longer valid</h1><p>If you still get emails from us about a nomination, reply to one and we will stop them.</p>';
		}
		if ( AIADN_Front::is_post() && 'nominate_stop' === AIADN_Front::post( 'aiadn_action' ) ) {
			AIADN_Nominations::opt_out( (int) $n['id'] );
			return '<h1>Done</h1><p>We will not contact this address about the National AI Conversation again, and we have deleted it.</p>';
		}
		return '<h1>Do not contact me</h1><p>Press the button and we will never email this address about a nomination again.</p><form method="post" action="' . esc_url( AIADN_Front::url( 'nominate' ) ) . '" class="aiadn__form"><input type="hidden" name="aiadn_action" value="nominate_stop"><input type="hidden" name="stop" value="' . esc_attr( $raw ) . '"><button class="aiadn__button" type="submit">Do not contact me about this</button></form>';
	}
}
