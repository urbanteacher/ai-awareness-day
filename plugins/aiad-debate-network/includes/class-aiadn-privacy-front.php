<?php
/**
 * /conversation/privacy/  "Delete my details": anyone we hold an email address for can ask, and proves the address is
 * theirs with a code we email. The answer is always the same whether or not we hold anything, so the page cannot be
 * used to find out who has taken part.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Privacy_Front {

	private static function esc( $v ): string {
		return esc_html( (string) $v );
	}

	public static function view_privacy(): string {
		AIADN_Front::set_title( 'Delete my details' );
		if ( AIADN_Front::is_post() ) {
			if ( ! AIADN_Util::allow( 'privacy|' . AIADN_Util::client_ip(), 20, HOUR_IN_SECONDS ) ) {
				return '<h1>Please wait</h1><p>' . esc_html( AIADN_Front::message( 'slow' ) ) . '</p>';
			}
			$action = AIADN_Front::post( 'aiadn_action' );
			if ( 'erase_request' === $action ) {
				return self::handle_request();
			}
			if ( 'erase_confirm' === $action ) {
				return self::handle_confirm();
			}
		}
		if ( 'code' === AIADN_Front::get( 'step' ) ) {
			return self::code_form( AIADN_Front::get( 'ref' ), '' );
		}
		return self::request_form();
	}

	private static function request_form(): string {
		$h  = '<h1>Delete my details</h1>';
		$h .= '<p>We keep the names and email addresses of teachers, headteachers, judges and the people who introduce them only while they are needed, and delete them all after the campaign ends on ' . self::esc( wp_date( 'j F Y', strtotime( AIADN_Privacy::retention_date() ) ) ) . '. You can ask for yours to go sooner.</p>';
		$h .= '<div class="aiadn__panel"><h2>What happens</h2><ul class="aiadn__list"><li>Your name, email address and job title are deleted wherever we hold them.</li><li>Debates, scores and results stay, without your name. If you were a judge, your name no longer appears on a result.</li><li>If you reported an incident, that record stays as a safeguarding record, without your email address.</li><li>If you are your school&rsquo;s lead teacher, the school will have no lead to sign in until it has another, so please tell a colleague first.</li></ul></div>';
		$h .= '<form method="post" action="' . esc_url( AIADN_Front::url( 'privacy' ) ) . '" class="aiadn__form"><input type="hidden" name="aiadn_action" value="erase_request">' . AIADN_Front::honeypot();
		$h .= '<label for="pv-email">The email address we hold</label><input id="pv-email" name="email" type="email" autocomplete="email" required>';
		$h .= '<button class="aiadn__button" type="submit">Email me a code</button></form>';
		return $h;
	}

	private static function handle_request(): string {
		$email = AIADN_Util::normalise_email( AIADN_Front::post( 'email' ) );
		$ref   = AIADN_Auth::decoy_ref();
		if ( ! AIADN_Front::honeypot_tripped() && is_email( $email ) && AIADN_Util::allow( 'privacy-send|' . $email, 5, HOUR_IN_SECONDS ) && AIADN_Privacy::holds( $email ) ) {
			list( $ref, $code ) = AIADN_Auth::issue_code( 0, $email, 'erase', '' );
			AIADN_Mailer::send_code( $email, $code );
		}
		AIADN_Front::redirect( 'privacy', array( 'step' => 'code', 'ref' => $ref ) );
		return '';
	}

	private static function code_form( string $ref, string $error ): string {
		$ref = preg_replace( '/[^a-f0-9]/', '', strtolower( $ref ) );
		$h   = '<h1>Check your email</h1><p>If we hold details under that address, we have sent a 6-digit code. Enter it to confirm the address is yours and delete your details.</p>';
		$h  .= AIADN_Front::notice( $error, 'error' );
		$h  .= '<form method="post" action="' . esc_url( AIADN_Front::url( 'privacy' ) ) . '" class="aiadn__form"><input type="hidden" name="aiadn_action" value="erase_confirm"><input type="hidden" name="ref" value="' . esc_attr( $ref ) . '">';
		$h  .= '<label for="pv-code">Code</label><input id="pv-code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required>';
		$h  .= '<button class="aiadn__button" type="submit">Delete my details</button></form>';
		return $h;
	}

	private static function handle_confirm(): string {
		$row = AIADN_Auth::verify_code( AIADN_Front::post( 'ref' ), AIADN_Front::post( 'code' ) );
		if ( ! $row || 'erase' !== $row['purpose'] ) {
			return self::code_form( AIADN_Front::post( 'ref' ), "We couldn't match those details." );
		}
		AIADN_Privacy::erase_email( (string) $row['email'] );
		return '<h1>Done</h1><p>Your details have been deleted. Anything you signed up for or judged is now shown without your name, and you will get no more emails from us about it.</p>';
	}
}
