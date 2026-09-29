<?php
/**
 * /conversation/find/  Find a Debate: open requests from other schools, and the asks this school has made.
 *
 * For signed-in teachers of approved schools only. Schools see each other's name and area, and what the
 * request is for. Never a teacher's name or email, and nothing about students.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Find_Front {

	private static function esc( $v ): string {
		return esc_html( (string) $v );
	}

	public static function view_find(): string {
		AIADN_Front::set_title( 'Join the conversation' );
		$session = AIADN_Auth::current();
		if ( ! $session || ! in_array( $session['role'], array( 'lead', 'teacher' ), true ) || 'approved' !== $session['school']['status'] ) {
			AIADN_Front::redirect( 'join', array( 'msg' => 'signin' ) );
		}
		$school_id = (int) $session['school_id'];
		$error     = '';

		if ( AIADN_Front::is_post() ) {
			if ( ! AIADN_Auth::csrf_ok( AIADN_Front::post( 'csrf' ) ) ) {
				AIADN_Front::redirect( 'find' );
			}
			$action = AIADN_Front::post( 'aiadn_action' );
			if ( 'find_ask' === $action ) {
				$debate = AIADN_Debates::get_by_code( AIADN_Util::normalise_debate_code( AIADN_Front::post( 'd' ) ) );
				$error  = $debate ? AIADN_Find::ask( $debate, $school_id, (int) $session['member_id'] ) : 'That request is no longer available.';
				if ( '' === $error ) {
					AIADN_Front::redirect( 'find', array( 'msg' => 'asked' ) );
				}
			} elseif ( 'find_withdraw' === $action ) {
				$req = AIADN_Find::get( (int) AIADN_Front::post( 'request_id' ) );
				if ( $req ) {
					AIADN_Find::withdraw( $req, $school_id );
				}
				AIADN_Front::redirect( 'find', array( 'msg' => 'withdrawn' ) );
			}
		}

		$filters = array( 'age' => AIADN_Front::get( 'age' ), 'theme' => AIADN_Front::get( 'theme' ), 'format' => AIADN_Front::get( 'format' ), 'region' => AIADN_Front::get( 'region' ) );
		$home    = AIADN_Regions::for_postcode( (string) $session['school']['postcode'] );
		$notes   = array( 'asked' => 'We have told them. You will get an email when they decide.', 'withdrawn' => 'Withdrawn.' );

		$h  = '<h1>Join the conversation</h1>';
		$h .= '<p class="aiadn__small"><a href="' . esc_url( AIADN_Front::url( 'school' ) ) . '">&larr; Your school</a></p>';
		$h .= AIADN_Front::notice( $notes[ AIADN_Front::get( 'msg' ) ] ?? '', 'info' ) . AIADN_Front::notice( $error, 'error' );
		$h .= '<p>Schools looking for an opponent. Ask to debate one and they choose. They see your school name and region, and nothing else about you.</p>';

		// One switch: my region, or everywhere. The region is worked out from each school's registered postcode.
		$all = 'all' === AIADN_Front::get( 'where' );
		$h  .= '<p>';
		$h  .= $all ? '<a class="aiadn__button aiadn__button--quiet" href="' . esc_url( AIADN_Front::url( 'find' ) ) . '">In my region: ' . self::esc( $home ) . '</a> <strong>Everywhere</strong>' : '<strong>In my region: ' . self::esc( $home ) . '</strong> <a class="aiadn__button aiadn__button--quiet" href="' . esc_url( AIADN_Front::url( 'find', array( 'where' => 'all' ) ) ) . '">Everywhere</a>';
		$h  .= '</p>';

		$board = AIADN_Find::board( $school_id, array_merge( $filters, array( 'all' => $all ? '1' : '' ) ) );
		if ( ! $board ) {
			$h .= '<div class="aiadn__panel"><p>No open requests in your region yet. Try Everywhere, or put your own request up from a debate that is waiting for an opponent.</p></div>';
		}
		foreach ( $board as $card ) {
			$d  = $card['debate'];
			$h .= '<div class="aiadn__panel aiadn__q--' . esc_attr( $d['theme'] ) . '"><h2>' . self::esc( $card['school']['name'] ) . '</h2>' . ( $card['near'] ? '<p class="aiadn__eyebrow">In your region</p>' : '' ) . '<dl class="aiadn__details">';
			$h .= '<dt>Region</dt><dd>' . self::esc( $card['region'] ) . '</dd>';
			$h .= '<dt>Age group</dt><dd>' . self::esc( AIADN_Motions::AGES[ $d['age_group'] ] ?? '' ) . '</dd>';
			$h .= '<dt>Theme</dt><dd>' . self::esc( AIADN_Motions::THEMES[ $d['theme'] ] ?? '' ) . '</dd>';
			$h .= '<dt>When suits</dt><dd>' . self::esc( $d['req_dates'] ) . '</dd>';
			$h .= '<dt>Format</dt><dd>' . self::esc( AIADN_Find::FORMATS[ $d['req_format'] ] ?? '' ) . '</dd>';
			$h .= '<dt>Hosting</dt><dd>' . self::esc( AIADN_Find::HOST[ $d['req_host'] ] ?? '' ) . '</dd>';
			$h .= '<dt>Open to</dt><dd>' . self::esc( AIADN_Find::TRAVEL[ $d['req_travel'] ] ?? AIADN_Find::TRAVEL['region'] ) . '</dd></dl>';
			if ( '' !== $card['asked'] ) {
				$h .= '<p><strong>' . self::esc( 'pending' === $card['asked'] ? 'You have asked. Waiting for their answer.' : ( 'declined' === $card['asked'] ? 'They chose not to go ahead this time.' : 'You asked about this one.' ) ) . '</strong></p>';
			} else {
				$h .= '<form method="post" action="' . esc_url( AIADN_Front::url( 'find' ) ) . '">' . AIADN_Front::csrf_field() . '<input type="hidden" name="aiadn_action" value="find_ask"><input type="hidden" name="d" value="' . esc_attr( $d['code'] ) . '"><button class="aiadn__button" type="submit">Ask to debate ' . self::esc( $card['school']['name'] ) . '</button></form>';
			}
			$h .= '</div>';
		}

		// This school's own asks that are still waiting.
		$mine = AIADN_Find::pending_for_school( $school_id );
		if ( $mine ) {
			$h .= '<div class="aiadn__panel"><h2>Waiting for an answer</h2>';
			foreach ( $mine as $r ) {
				$d    = AIADN_Debates::get( (int) $r['debate_id'] );
				$host = $d ? AIADN_Schools::get( (int) $d['school_a_id'] ) : null;
				$h   .= '<form method="post" action="' . esc_url( AIADN_Front::url( 'find' ) ) . '" class="aiadn__form aiadn__form--row">' . AIADN_Front::csrf_field() . '<input type="hidden" name="aiadn_action" value="find_withdraw"><input type="hidden" name="request_id" value="' . (int) $r['id'] . '"><span>' . self::esc( $host['name'] ?? 'A school' ) . '</span> <button class="aiadn__button aiadn__button--quiet" type="submit">Withdraw</button></form>';
			}
			$h .= '</div>';
		}
		return $h;
	}
}
