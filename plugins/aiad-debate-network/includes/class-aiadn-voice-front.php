<?php
/**
 * Student Voice pages:
 *   /conversation/survey/  the student survey, after school code + class PIN (screen 26)
 *   /conversation/voice/   a school's results (screen 11)
 *   /conversation/board/   the whiteboard display: QR code, school code and class PIN (screens 4 and 7)
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Voice_Front {

	private static function esc( $v ): string {
		return esc_html( (string) $v );
	}

	/** The theme's own strand graphic (dark chamfered tile, coloured icon). Decorative: the label sits beside it. */
	public static function tile( string $theme ): string {
		return '<img class="aiadn__tile" src="' . esc_url( get_theme_file_uri( 'assets/brand/aiad27/poster-' . $theme . '.svg' ) ) . '" alt="" width="64" height="65" loading="lazy">';
	}

	/* ------------------------------------------------------------------ */
	/* /conversation/survey/                                               */
	/* ------------------------------------------------------------------ */

	public static function view_survey(): string {
		AIADN_Front::set_title( 'Student Voice' );
		$session = AIADN_Auth::current();
		if ( ! $session || 'student' !== $session['role'] ) {
			if ( '' !== AIADN_Front::get( 'done' ) ) {
				return '<h1>Thank you</h1><p>Your answers have been saved. They are anonymous: nobody can tell which answers are yours.</p><p>You can close this page now.</p>';
			}
			AIADN_Front::redirect( 'join', array( 'msg' => 'signin' ) );
		}
		$pin = AIADN_Auth::get_pin( (int) $session['member_id'] );
		if ( ! $pin ) {
			AIADN_Front::redirect( 'join', array( 'msg' => 'signin' ) );
		}

		$error  = '';
		$values = array( 'year_group' => '', 'answers' => array() );
		if ( AIADN_Front::is_post() && 'submit_voice' === AIADN_Front::post( 'aiadn_action' ) ) {
			if ( ! AIADN_Auth::csrf_ok( AIADN_Front::post( 'csrf' ) ) ) {
				AIADN_Front::redirect( 'survey' );
			}
			$values['year_group'] = AIADN_Front::post( 'year_group' );
			foreach ( array_keys( AIADN_Voice::QUESTIONS ) as $key ) {
				$values['answers'][ $key ] = (int) AIADN_Front::post( 'q_' . $key );
			}
			$error = AIADN_Voice::submit( $pin, $values['year_group'], $values['answers'] );
			if ( '' === $error ) {
				// One answer per sign-in. Another student uses the PIN again.
				AIADN_Auth::end_session();
				AIADN_Front::redirect( 'survey', array( 'done' => 1 ) );
			}
		}

		$h  = '<h1>Student Voice</h1>';
		$h .= '<p>You have joined the conversation for <strong>' . self::esc( $session['school']['name'] ) . '</strong>. Six quick questions. No names, no accounts, and your answers are anonymous.</p>';
		$h .= AIADN_Front::notice( $error, 'error' );
		$h .= '<form method="post" action="' . esc_url( AIADN_Front::url( 'survey' ) ) . '" class="aiadn__form">' . AIADN_Front::csrf_field() . '<input type="hidden" name="aiadn_action" value="submit_voice">';

		$h .= '<fieldset class="aiadn__roles"><legend>Which year are you in?</legend>';
		foreach ( AIADN_Voice::YEAR_GROUPS as $key => $label ) {
			$h .= '<label class="aiadn__radio"><input type="radio" name="year_group" value="' . esc_attr( $key ) . '"' . checked( $values['year_group'], $key, false ) . '> ' . esc_html( $label ) . '</label>';
		}
		$h .= '</fieldset>';

		$n     = 0;
		$total = count( AIADN_Voice::QUESTIONS );
		foreach ( AIADN_Voice::QUESTIONS as $key => $q ) {
			++$n;
			$h .= '<div class="aiadn__q aiadn__q--' . esc_attr( $q['theme'] ) . '">' . self::tile( $q['theme'] ) . '<fieldset class="aiadn__roles aiadn__question"><legend><span class="aiadn__meta">' . esc_html( $q['label'] ) . ' &middot; ' . $n . ' of ' . (int) $total . '</span><br>&ldquo;' . esc_html( $q['text'] ) . '&rdquo;</legend>';
			foreach ( AIADN_Voice::ANSWERS as $value => $label ) {
				$h .= '<label class="aiadn__radio"><input type="radio" name="q_' . esc_attr( $key ) . '" value="' . (int) $value . '"' . checked( (int) ( $values['answers'][ $key ] ?? 0 ), $value, false ) . '> ' . esc_html( $label ) . '</label>';
			}
			$h .= '</fieldset></div>';
		}
		return $h . '<button class="aiadn__button" type="submit">Send my answers</button></form>';
	}

	/* ------------------------------------------------------------------ */
	/* /conversation/voice/  (teachers and senior leaders)                 */
	/* ------------------------------------------------------------------ */

	public static function view_voice(): string {
		AIADN_Front::set_title( 'Student Voice results' );
		$session = AIADN_Auth::current();
		if ( ! $session || ! in_array( $session['role'], array( 'lead', 'teacher', 'slt' ), true ) ) {
			AIADN_Front::redirect( 'join', array( 'msg' => 'signin' ) );
		}
		$school_id = (int) $session['school_id'];
		$n         = AIADN_Voice::count( $school_id );
		$results   = AIADN_Voice::results( $school_id );

		$h  = '<h1>Student Voice</h1><p class="aiadn__small"><a href="' . esc_url( AIADN_Front::url( 'school' ) ) . '">&larr; Your school</a></p>';
		$h .= '<div class="aiadn__stats"><div class="aiadn__stat"><strong>' . (int) $n . '</strong><span>Responses</span></div></div>';

		if ( ! $results ) {
			$h .= '<div class="aiadn__panel"><h2>Results unlock at ' . (int) AIADN_Voice::MIN_RESPONSES . '</h2><p>' . (int) $n . ' of ' . (int) AIADN_Voice::MIN_RESPONSES . ' students have answered so far. Results stay hidden until there are enough that no individual can be picked out.</p><a class="aiadn__button" href="' . esc_url( AIADN_Front::url( 'board' ) ) . '">Show the code and PIN on the whiteboard</a></div>';
			return $h;
		}

		$h .= '<div class="aiadn__panel"><h2>What your students think</h2>';
		foreach ( AIADN_Voice::QUESTIONS as $key => $q ) {
			$r = $results[ $key ] ?? null;
			if ( ! $r ) {
				$h .= '<div class="aiadn__vq aiadn__q--' . esc_attr( $q['theme'] ) . '">' . self::tile( $q['theme'] ) . '<div><p><strong>' . esc_html( $q['label'] ) . '</strong> &ldquo;' . esc_html( $q['text'] ) . '&rdquo;</p><p class="aiadn__small">Not enough answers to this question yet.</p></div></div>';
				continue;
			}
			$h .= '<div class="aiadn__vq aiadn__q--' . esc_attr( $q['theme'] ) . '">' . self::tile( $q['theme'] ) . '<div><p><strong>' . esc_html( $q['label'] ) . '</strong> &ldquo;' . esc_html( $q['text'] ) . '&rdquo;</p>' . self::stack( $r ) . '<p class="aiadn__small">Agree ' . (int) $r['agree'] . '% &middot; Not sure ' . (int) $r['unsure'] . '% &middot; Disagree ' . (int) $r['disagree'] . '%</p></div></div>';
		}
		$split = AIADN_Voice::biggest_split( $results );
		if ( $split ) {
			$h .= '<p><strong>Biggest split:</strong> ' . esc_html( AIADN_Voice::QUESTIONS[ $split ]['label'] ) . '. Students disagree with each other most here, which makes it a good topic for a debate.</p>';
		}
		$h .= '</div>';

		$movement = AIADN_Voice::movement( $school_id );
		if ( $movement ) {
			$h .= '<div class="aiadn__panel"><h2>Before and after a debate</h2>';
			foreach ( $movement as $m ) {
				$d  = $m['debate'];
				$h .= '<h3>' . self::esc( $d['code'] ) . ', ' . self::esc( AIADN_Util::show( (string) $d['starts_at'], 'j M Y' ) ) . '</h3><ul class="aiadn__list">';
				foreach ( AIADN_Voice::QUESTIONS as $key => $q ) {
					if ( empty( $m['before'][ $key ] ) || empty( $m['after'][ $key ] ) ) {
						continue;
					}
					$change = $m['after'][ $key ]['agree'] - $m['before'][ $key ]['agree'];
					$h     .= '<li>' . esc_html( $q['label'] ) . ': agree ' . (int) $m['before'][ $key ]['agree'] . '% &rarr; ' . (int) $m['after'][ $key ]['agree'] . '% (' . ( $change >= 0 ? '+' : '' ) . (int) $change . ')</li>';
				}
				$h .= '</ul>';
			}
			$h .= '</div>';
		}
		return $h . '<p class="aiadn__small">Every answer is anonymous. The statements are drafts and will be reviewed before national use.</p>';
	}

	/** A stacked bar for agree, not sure and disagree. The percentages are also written out below it. */
	public static function stack( array $r ): string {
		return '<span class="aiadn__stack" aria-hidden="true"><span class="aiadn__stack--a" style="width:' . (int) $r['agree'] . '%"></span><span class="aiadn__stack--u" style="width:' . (int) $r['unsure'] . '%"></span><span class="aiadn__stack--d" style="width:' . (int) $r['disagree'] . '%"></span></span>';
	}

	/* ------------------------------------------------------------------ */
	/* /conversation/board/  (the whiteboard)                              */
	/* ------------------------------------------------------------------ */

	public static function view_board(): string {
		AIADN_Front::set_title( 'On the whiteboard' );
		$session = AIADN_Auth::current();
		if ( ! $session || ! in_array( $session['role'], array( 'lead', 'teacher' ), true ) || 'approved' !== $session['school']['status'] ) {
			AIADN_Front::redirect( 'join', array( 'msg' => 'signin' ) );
		}
		$school = $session['school'];
		$pin    = AIADN_Auth::current_pin( (int) $school['id'] );
		$url    = AIADN_Front::url( 'join', array( 'c' => $school['code'] ) );

		$h  = '<h1>Join the conversation</h1>';
		$h .= '<p class="aiadn__small aiadn__noprint"><a href="' . esc_url( AIADN_Front::url( 'school' ) ) . '">&larr; Your school</a></p>';
		$h .= '<div class="aiadn__board-logo">' . AIADN_Front::logo_html() . '</div>';
		$h .= '<div class="aiadn__board"><div class="aiadn__board-qr">' . AIADN_QR::svg( $url, 'QR code that opens the front door with your school code filled in' ) . '</div><div class="aiadn__board-text">';
		$h .= '<p class="aiadn__eyebrow">1. Scan the code</p><p class="aiadn__small">It opens the page with the school code filled in.</p>';
		$h .= '<p class="aiadn__eyebrow">School code</p><p class="aiadn__bigcode">' . self::esc( $school['code'] ) . '</p>';
		$h .= '<p class="aiadn__eyebrow">2. Type the class PIN</p>';
		if ( $pin ) {
			$h .= '<p class="aiadn__bigcode aiadn__bigcode--pin">' . self::esc( $pin['pin'] ) . '</p><p class="aiadn__small">Works until about ' . self::esc( AIADN_Util::show( $pin['expires_at'], 'H:i' ) ) . '.</p>';
		} else {
			$h .= '<p>No PIN is running. <a href="' . esc_url( AIADN_Front::url( 'school' ) ) . '">Start one on your school page</a>.</p>';
		}
		$h .= '</div></div><p class="aiadn__small aiadn__noprint">No names, no accounts. Students who scan the code and type the PIN answer five anonymous questions.</p>';
		$h .= '<button class="aiadn__button aiadn__button--quiet aiadn__noprint" type="button" onclick="window.print()">Print this page</button>';
		return $h;
	}
}
