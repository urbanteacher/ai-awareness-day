<?php
/**
 * Slice 3 pages:
 *   /conversation/score/        the judge's scorecard, phone-first (screens 23, 25)
 *   /conversation/results/      a school's results (screen 9)
 *   /conversation/issue/        report and resolve an issue (screens 28, 29)
 *   /conversation/certificate/  the school's certificate (screen 31)
 *   /conversation/check/        anyone can check a certificate (screen 32)
 *   /conversation/debates/      public results (screen 30)
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Result_Front {

	/* ------------------------------------------------------------------ */
	/* Helpers                                                             */
	/* ------------------------------------------------------------------ */

	private static function esc( $v ): string {
		return esc_html( (string) $v );
	}

	private static function school_name( $id ): string {
		$school = $id ? AIADN_Schools::get( (int) $id ) : null;
		return $school ? $school['name'] : '';
	}

	private static function theme_strand( array $debate ): void {
		if ( ! empty( $debate['theme'] ) ) {
			AIADN_Front::set_strand( $debate['theme'] );
		}
	}

	private static function err( array $errors, string $key ): string {
		return isset( $errors[ $key ] ) ? '<p class="aiadn__error" role="alert">' . esc_html( $errors[ $key ] ) . '</p>' : '';
	}

	/** A signed-in teacher or senior leader, or a redirect to the front door. */
	private static function require_school_session(): array {
		$session = AIADN_Auth::current();
		if ( ! $session || ! in_array( $session['role'], array( 'lead', 'teacher', 'slt' ), true ) ) {
			AIADN_Front::redirect( 'join', array( 'msg' => 'signin' ) );
		}
		return $session;
	}

	private static function debate_for_session( array $session, string $code_raw ): ?array {
		$code   = AIADN_Util::normalise_debate_code( $code_raw );
		$debate = $code ? AIADN_Debates::get_by_code( $code ) : null;
		return ( $debate && AIADN_Debates::involves( $debate, (int) $session['school_id'] ) ) ? $debate : null;
	}

	/**
	 * Who is the judge here: a signed-in judge or a shortcut-link judge, on an accepted debate.
	 *
	 * @return array{0:?array,1:?array,2:string,3:string,4:string} judge, debate, mode, raw token, error page
	 */
	private static function judge_context(): array {
		$raw     = AIADN_Front::get( 't' ) ?: AIADN_Front::post( 't' );
		$session = AIADN_Auth::current();
		$judge   = null;
		$mode    = '';
		$bad     = '<h1>This link is no longer valid</h1><p>It may have expired, or the teachers may have chosen a different judge.</p>';

		if ( '' !== $raw ) {
			$tok   = preg_match( '/^[a-f0-9]{40}$/', $raw ) ? AIADN_Auth::find_token( $raw, 'judge' ) : null;
			$judge = $tok ? AIADN_Debates::get_judge( (int) $tok['ref_id'] ) : null;
			$mode  = 'token';
		} elseif ( $session && 'judge' === $session['role'] ) {
			$code = AIADN_Util::normalise_debate_code( AIADN_Front::get( 'd' ) ?: AIADN_Front::post( 'd' ) );
			foreach ( AIADN_Debates::judges_for_school_email( (int) $session['school_id'], (string) $session['member']['email'] ) as $row ) {
				$d = AIADN_Debates::get( (int) $row['debate_id'] );
				if ( $d && $d['code'] === $code ) {
					$judge = $row;
				}
			}
			$mode = 'session';
		} else {
			AIADN_Front::redirect( 'join', array( 'msg' => 'signin' ) );
		}

		$debate = $judge ? AIADN_Debates::get( (int) $judge['debate_id'] ) : null;
		if ( ! $judge || ! $debate ) {
			return array( null, null, $mode, $raw, $bad );
		}
		if ( 'accepted' !== $judge['status'] ) {
			return array( null, null, $mode, $raw, '<h1>Not available</h1><p>Please accept the invitation first.</p>' );
		}
		if ( 'cancelled' === $debate['status'] ) {
			return array( null, null, $mode, $raw, '<h1>This debate has been cancelled</h1><p>There is nothing more to do.</p>' );
		}
		return array( $judge, $debate, $mode, $raw, '' );
	}

	private static function judge_authorised( string $mode, ?array $session ): bool {
		return 'token' === $mode || ( $session && AIADN_Auth::csrf_ok( AIADN_Front::post( 'csrf' ) ) );
	}

	private static function score_url( array $debate, string $mode, string $raw, array $args = array() ): string {
		return AIADN_Front::url( 'score', array_merge( 'token' === $mode ? array( 't' => $raw ) : array( 'd' => $debate['code'] ), $args ) );
	}

	/* ------------------------------------------------------------------ */
	/* /conversation/score/                                                */
	/* ------------------------------------------------------------------ */

	public static function view_score(): string {
		AIADN_Front::set_title( 'Scorecard' );
		list( $judge, $debate, $mode, $raw, $error ) = self::judge_context();
		if ( $error ) {
			return $error;
		}
		self::theme_strand( $debate );
		$session = AIADN_Auth::current();
		$card    = AIADN_Scorecards::get_for_debate( (int) $debate['id'] );
		$errors  = array();
		$values  = $card ? array_intersect_key( $card, AIADN_Scorecards::blank() ) : AIADN_Scorecards::blank();

		if ( AIADN_Front::is_post() ) {
			if ( ! self::judge_authorised( $mode, $session ) ) {
				AIADN_Front::redirect( 'judge' );
			}
			$action = AIADN_Front::post( 'aiadn_action' );
			if ( 'save_scores' === $action && AIADN_Scorecards::is_open( $debate ) ) {
				AIADN_Scorecards::save_draft( $debate, $judge, AIADN_Scorecards::clean( self::posted_scores() ) );
				if ( isset( $_SERVER['HTTP_X_AIADN_AUTOSAVE'] ) ) {
					wp_send_json( array( 'saved' => true ) );
				}
				wp_safe_redirect( self::score_url( $debate, $mode, $raw, array( 'msg' => 'saved' ) ) );
				exit;
			}
			if ( 'submit_result' === $action && AIADN_Scorecards::is_open( $debate ) ) {
				$values = AIADN_Scorecards::clean( self::posted_scores() );
				$errors = AIADN_Scorecards::submit( $debate, $judge, $values );
				if ( ! $errors ) {
					wp_safe_redirect( self::score_url( $debate, $mode, $raw, array( 'msg' => 'submitted' ) ) );
					exit;
				}
			}
			if ( 'rate' === $action && $card && 'submitted' === $card['status'] ) {
				AIADN_Results::save_rating( (int) $debate['id'], 0, 'judge', (int) AIADN_Front::post( 'rating' ) );
				wp_safe_redirect( self::score_url( $debate, $mode, $raw, array( 'msg' => 'rated' ) ) );
				exit;
			}
			$debate = AIADN_Debates::get( (int) $debate['id'] );
			$card   = AIADN_Scorecards::get_for_debate( (int) $debate['id'] );
		}

		return self::render_score( $judge, $debate, $card, $mode, $raw, $values, $errors );
	}

	/** @return array<string,string> */
	private static function posted_scores(): array {
		$out = array();
		foreach ( array_keys( AIADN_Scorecards::blank() ) as $key ) {
			$out[ $key ] = isset( $_POST[ $key ] ) && is_string( $_POST[ $key ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		}
		return $out;
	}

	private static function render_score( array $judge, array $debate, ?array $card, string $mode, string $raw, array $v, array $errors ): string {
		$a  = self::school_name( $debate['school_a_id'] );
		$b  = self::school_name( $debate['school_b_id'] );
		$h  = '<h1>Scorecard</h1>';
		$flash = array( 'saved' => 'Draft saved.', 'submitted' => 'Result submitted. Thank you.', 'rated' => 'Thank you for the feedback.' );
		$msg   = AIADN_Front::get( 'msg' );
		if ( isset( $flash[ $msg ] ) ) {
			$h .= AIADN_Front::notice( $flash[ $msg ], 'info' );
		}
		$h .= '<p><strong>' . self::esc( $a ) . ' v ' . self::esc( $b ) . '</strong><br>' . self::esc( AIADN_Util::show( (string) $debate['starts_at'] ) ) . '<br>&ldquo;' . self::esc( $debate['motion_text'] ) . '&rdquo;</p>';
		$back = 'token' === $mode ? AIADN_Front::url( 'judge', array( 't' => $raw ) ) : AIADN_Front::url( 'judge' );
		$h   .= AIADN_Debate_Front::join_panel( $debate, 'judge', ( 'token' === $mode ? AIADN_Front::url( 'calendar', array( 't' => $raw ) ) : AIADN_Front::url( 'calendar', array( 'd' => $debate['code'] ) ) ) );

		// ---- already submitted ----
		if ( $card && 'submitted' === $card['status'] ) {
			$winner = 'a' === $card['winner'] ? $a : $b;
			$h .= '<div class="aiadn__panel"><h2>Result submitted</h2><p class="aiadn__bigcode">' . (int) $card['a_total'] . ' - ' . (int) $card['b_total'] . '</p><p>Winner: <strong>' . self::esc( $winner ) . '</strong></p>';
			$h .= '<p>Both schools can see it now. It is final unless a school reports a problem.</p></div>';
			$h .= '<p>Made a mistake? <a href="' . esc_url( 'token' === $mode ? AIADN_Front::url( 'issue', array( 't' => $raw ) ) : AIADN_Front::url( 'issue', array( 'd' => $debate['code'] ) ) ) . '">Tell the schools</a>.</p>';
			if ( ! AIADN_Results::has_rated( (int) $debate['id'], 0, 'judge' ) ) {
				$h .= '<div class="aiadn__panel"><h2>How was judging?</h2><form method="post" action="' . esc_url( self::score_url( $debate, $mode, $raw ) ) . '" class="aiadn__form aiadn__form--row">' . self::judge_hidden( $mode, $raw, $debate ) . '<input type="hidden" name="aiadn_action" value="rate">';
				foreach ( array( 1 => 'Difficult', 3 => 'OK', 5 => 'Good' ) as $n => $label ) {
					$h .= '<button class="aiadn__button aiadn__button--quiet" type="submit" name="rating" value="' . (int) $n . '">' . esc_html( $label ) . '</button>';
				}
				$h .= '</form></div>';
			}
			return $h . '<p class="aiadn__small"><a href="' . esc_url( $back ) . '">&larr; Back to judging</a></p>';
		}

		// ---- not open yet ----
		if ( ! AIADN_Scorecards::is_open( $debate ) ) {
			return $h . '<div class="aiadn__panel"><h2>Scoring opens soon</h2><p>You can start scoring from <strong>' . self::esc( AIADN_Util::show( gmdate( 'Y-m-d H:i:s', AIADN_Scorecards::opens_at( $debate ) ) ) ) . '</strong>, three hours before the debate.</p></div><p class="aiadn__small"><a href="' . esc_url( $back ) . '">&larr; Back to judging</a></p>';
		}

		// ---- the form ----
		$val = static fn( string $k ) => (int) ( $v[ $k ] ?? 0 ) > 0 ? (int) $v[ $k ] : '';
		$h  .= AIADN_Front::notice( isset( $errors['form'] ) ? $errors['form'] : ( $errors ? 'Please fill in the highlighted parts.' : '' ), 'error' );
		$h  .= '<form method="post" action="' . esc_url( self::score_url( $debate, $mode, $raw ) ) . '" class="aiadn__form aiadn__score" id="aiadn-score">' . self::judge_hidden( $mode, $raw, $debate );

		$h .= '<label for="sc-students">Students taking part (both schools)</label><input id="sc-students" name="students" type="number" inputmode="numeric" min="0" max="500" value="' . esc_attr( (string) $val( 'students' ) ) . '">' . self::err( $errors, 'students' );

		$h .= '<fieldset class="aiadn__roles"><legend>Room vote before the debate</legend>' . self::vote_inputs( 'vb', $v ) . '</fieldset>';

		$h .= '<table class="aiadn__table aiadn__scoretable"><caption class="aiadn__sr">Scores from 1 to 5</caption><thead><tr><th scope="col">Criterion</th><th scope="col">' . self::esc( $a ) . '</th><th scope="col">' . self::esc( $b ) . '</th></tr></thead><tbody>';
		foreach ( AIADN_Scorecards::CRITERIA as $key => $label ) {
			$h .= '<tr><th scope="row">' . esc_html( $label ) . '</th>';
			foreach ( array( 'a', 'b' ) as $side ) {
				$name = $side . '_' . $key;
				$h   .= '<td><label class="aiadn__sr" for="sc-' . $name . '">' . esc_html( $label ) . ', ' . self::esc( 'a' === $side ? $a : $b ) . '</label><select id="sc-' . $name . '" name="' . $name . '" data-side="' . $side . '"><option value="">-</option>';
				for ( $n = 1; $n <= 5; $n++ ) {
					$h .= '<option value="' . $n . '"' . selected( (int) ( $v[ $name ] ?? 0 ), $n, false ) . '>' . $n . '</option>';
				}
				$h .= '</select>' . self::err( $errors, $name ) . '</td>';
			}
			$h .= '</tr>';
		}
		$h .= '<tr><th scope="row">Total</th><td data-total="a">0</td><td data-total="b">0</td></tr></tbody></table>';

		$h .= '<fieldset class="aiadn__roles"><legend>Winner</legend>';
		foreach ( array( 'a' => $a, 'b' => $b ) as $side => $name ) {
			$h .= '<label class="aiadn__radio"><input type="radio" name="winner" value="' . $side . '"' . checked( (string) ( $v['winner'] ?? '' ), $side, false ) . '> ' . self::esc( $name ) . '</label>';
		}
		$h .= '</fieldset>' . self::err( $errors, 'winner' );

		$h .= '<label for="sc-ca">Comment for ' . self::esc( $a ) . '</label><textarea id="sc-ca" name="comment_a" rows="3" maxlength="1000">' . esc_textarea( (string) ( $v['comment_a'] ?? '' ) ) . '</textarea>';
		$h .= '<label for="sc-cb">Comment for ' . self::esc( $b ) . '</label><textarea id="sc-cb" name="comment_b" rows="3" maxlength="1000">' . esc_textarea( (string) ( $v['comment_b'] ?? '' ) ) . '</textarea>';
		$h .= '<fieldset class="aiadn__roles"><legend>Room vote after the debate</legend>' . self::vote_inputs( 'va', $v ) . '</fieldset>';

		$h .= '<p class="aiadn__small" id="aiadn-saved" aria-live="polite">' . ( $card ? 'Draft saved earlier.' : 'Nothing saved yet.' ) . '</p>';
		$h .= '<button class="aiadn__button aiadn__button--quiet" type="submit" name="aiadn_action" value="save_scores">Save draft</button> <button class="aiadn__button" type="submit" name="aiadn_action" value="submit_result">Submit result</button></form>';
		$h .= '<p class="aiadn__small"><a href="' . esc_url( $back ) . '">&larr; Back to judging</a></p>';
		$h .= self::score_script();
		return $h;
	}

	private static function judge_hidden( string $mode, string $raw, array $debate ): string {
		return 'token' === $mode
			? '<input type="hidden" name="t" value="' . esc_attr( $raw ) . '">'
			: AIADN_Front::csrf_field() . '<input type="hidden" name="d" value="' . esc_attr( $debate['code'] ) . '">';
	}

	private static function vote_inputs( string $prefix, array $v ): string {
		$h = '<div class="aiadn__votes">';
		foreach ( array( 'agree' => 'Agree', 'disagree' => 'Disagree', 'unsure' => 'Unsure' ) as $k => $label ) {
			$h .= '<label>' . esc_html( $label ) . '<input name="' . $prefix . '_' . $k . '" type="number" inputmode="numeric" min="0" max="500" value="' . esc_attr( (string) ( (int) ( $v[ $prefix . '_' . $k ] ?? 0 ) ) ) . '"></label>';
		}
		return $h . '</div>';
	}

	/** Adds totals and saves as the judge types. The form still works without it. */
	private static function score_script(): string {
		return '<script>(function(){var f=document.getElementById("aiadn-score");if(!f)return;var note=document.getElementById("aiadn-saved");function totals(){["a","b"].forEach(function(s){var t=0;f.querySelectorAll("select[data-side="+s+"]").forEach(function(x){t+=parseInt(x.value||"0",10)||0;});var c=f.querySelector("[data-total="+s+"]");if(c)c.textContent=t;});}var timer=null;function save(){var fd=new FormData(f);fd.set("aiadn_action","save_scores");note.textContent="Saving...";fetch(f.action,{method:"POST",body:fd,credentials:"same-origin",headers:{"X-AIADN-Autosave":"1"}}).then(function(r){return r.json();}).then(function(j){note.textContent=j&&j.saved?"Saved just now.":"Could not save. Use Save draft.";}).catch(function(){note.textContent="Could not save. Use Save draft.";});}f.addEventListener("submit",function(e){var b=e.submitter;if(!b||b.value!=="submit_result")return;var ta=parseInt(f.querySelector("[data-total=a]").textContent,10)||0,tb=parseInt(f.querySelector("[data-total=b]").textContent,10)||0;var w=f.querySelector("input[name=winner]:checked");var lead=ta>tb?"a":(tb>ta?"b":"");var msg="Submit the result? It is final unless a school reports a problem.";if(w&&lead&&w.value!==lead){msg+="\n\nYou chose the school with the lower total as the winner.";}if(!confirm(msg)){e.preventDefault();}});f.addEventListener("input",function(){totals();clearTimeout(timer);timer=setTimeout(save,1500);});f.addEventListener("change",function(){totals();clearTimeout(timer);timer=setTimeout(save,800);});totals();})();</script>';
	}

	/* ------------------------------------------------------------------ */
	/* The result panel on a debate page (teachers)                        */
	/* ------------------------------------------------------------------ */

	public static function render_result_panel( array $debate, array $session ): string {
		$school_id = (int) $session['school_id'];
		$side      = AIADN_Debates::side( $debate, $school_id );
		$card      = AIADN_Scorecards::get_for_debate( (int) $debate['id'] );
		$a         = self::school_name( $debate['school_a_id'] );
		$b         = self::school_name( $debate['school_b_id'] );

		if ( 'void' === $debate['status'] ) {
			return '<div class="aiadn__panel"><h2>Void</h2><p>This debate has been voided. It does not count towards results or certificates.</p></div>';
		}
		if ( ! $card || 'submitted' !== $card['status'] ) {
			return '';
		}

		$held = AIADN_Issues::is_held( (int) $debate['id'] );
		$h    = '';
		if ( $held ) {
			$h .= '<p class="aiadn__notice aiadn__notice--error">Result on hold. An issue is open, so this result does not count until it is resolved. <a href="' . esc_url( AIADN_Front::url( 'issue', array( 'd' => $debate['code'] ) ) ) . '">See the issue</a>.</p>';
		}
		$winner = 'a' === $card['winner'] ? $a : $b;
		$h     .= '<div class="aiadn__panel"><h2>Result</h2><p class="aiadn__bigcode">' . (int) $card['a_total'] . ' - ' . (int) $card['b_total'] . '</p><p>Winner: <strong>' . self::esc( $winner ) . '</strong>. Judge: ' . self::esc( self::judge_name( $debate ) ) . '.</p>';
		$h     .= '<table class="aiadn__table"><thead><tr><th scope="col">Criterion</th><th scope="col">' . self::esc( $a ) . '</th><th scope="col">' . self::esc( $b ) . '</th></tr></thead><tbody>';
		foreach ( AIADN_Scorecards::CRITERIA as $key => $label ) {
			$h .= '<tr><th scope="row">' . esc_html( $label ) . '</th><td>' . (int) $card[ 'a_' . $key ] . '</td><td>' . (int) $card[ 'b_' . $key ] . '</td></tr>';
		}
		$h .= '</tbody></table>';
		$comment = (string) ( 'a' === $side ? $card['comment_a'] : $card['comment_b'] );
		if ( '' !== trim( $comment ) ) {
			$h .= '<h3>The judge\'s comment for your school</h3><p>&ldquo;' . nl2br( esc_html( $comment ) ) . '&rdquo;</p>';
		}
		if ( $card['vb_agree'] + $card['vb_disagree'] + $card['vb_unsure'] + $card['va_agree'] + $card['va_disagree'] + $card['va_unsure'] > 0 ) {
			$h .= '<h3>Room vote</h3><p>Before: agree ' . (int) $card['vb_agree'] . ', disagree ' . (int) $card['vb_disagree'] . ', unsure ' . (int) $card['vb_unsure'] . '.<br>After: agree ' . (int) $card['va_agree'] . ', disagree ' . (int) $card['va_disagree'] . ', unsure ' . (int) $card['va_unsure'] . '.</p>';
		}
		$prog = AIADN_Certificates::progress( $school_id );
		$h   .= '<h3>Certificate progress</h3><p>' . (int) $prog['have'] . ' of ' . (int) $prog['need'] . ' debates against different schools. <a href="' . esc_url( AIADN_Front::url( 'certificate' ) ) . '">See your certificate</a>.</p></div>';
		return $h;
	}

	public static function judge_name( array $debate ): string {
		$judge = (int) $debate['judge_id'] ? AIADN_Debates::get_judge( (int) $debate['judge_id'] ) : null;
		return $judge ? $judge['name'] : 'the judge';
	}

	/** Feedback form and the report-an-issue link, shown under a result. */
	public static function render_result_actions( array $debate, array $session, string $form_open, bool $can_act ): string {
		$school_id = (int) $session['school_id'];
		$h         = '';
		if ( $can_act && ! AIADN_Results::has_rated( (int) $debate['id'], $school_id, 'teacher' ) && AIADN_Scorecards::get_for_debate( (int) $debate['id'] ) ) {
			$h .= '<div class="aiadn__panel"><h2>Quick feedback</h2><p>How easy was this debate to organise?</p>' . $form_open;
			for ( $n = 1; $n <= 5; $n++ ) {
				$h .= '<button class="aiadn__button aiadn__button--quiet" type="submit" name="rating" value="' . $n . '">' . $n . '</button> ';
			}
			$h .= '</form><p class="aiadn__small">1 is hard, 5 is easy.</p></div>';
		}
		$h .= '<p>Something wrong? <a href="' . esc_url( AIADN_Front::url( 'issue', array( 'd' => $debate['code'] ) ) ) . '">Report an issue</a>. <a href="' . esc_url( AIADN_Front::url( 'results' ) ) . '">All your results</a>.</p>';
		return $h;
	}

	/* ------------------------------------------------------------------ */
	/* /conversation/paper/  the printed scorecard                         */
	/* ------------------------------------------------------------------ */

	/**
	 * The agreed debate a printable page is for, and who is looking: the schools (teachers and senior leaders)
	 * or the judge, by shortcut link or after signing in. Null when there is nothing to show them.
	 *
	 * @return array{0:array,1:string,2:string}|null the debate, the viewer ('school' or 'judge') and, for a school, its side
	 */
	private static function printable_debate(): ?array {
		$raw     = AIADN_Front::get( 't' );
		$session = AIADN_Auth::current();
		$debate  = null;
		$viewer  = 'school';
		$side    = '';

		if ( '' !== $raw ) {
			$tok    = preg_match( '/^[a-f0-9]{40}$/', $raw ) ? AIADN_Auth::find_token( $raw, 'judge' ) : null;
			$judge  = $tok ? AIADN_Debates::get_judge( (int) $tok['ref_id'] ) : null;
			$debate = ( $judge && 'accepted' === $judge['status'] ) ? AIADN_Debates::get( (int) $judge['debate_id'] ) : null;
			$viewer = 'judge';
		} elseif ( ! $session ) {
			// A teacher opening the link from an email, before signing in, is sent to sign in rather than told it is not there.
			AIADN_Front::redirect( 'join', array( 'msg' => 'signin' ) );
		} else {
			$code   = AIADN_Util::normalise_debate_code( AIADN_Front::get( 'd' ) );
			$debate = $code ? AIADN_Debates::get_by_code( $code ) : null;
			$ok     = false;
			if ( $debate ) {
				if ( in_array( $session['role'], array( 'lead', 'teacher', 'slt' ), true ) ) {
					$ok   = AIADN_Debates::involves( $debate, (int) $session['school_id'] );
					$side = AIADN_Debates::side( $debate, (int) $session['school_id'] );
				} elseif ( 'judge' === $session['role'] ) {
					$viewer = 'judge';
					foreach ( AIADN_Debates::judges_for_school_email( (int) $session['school_id'], (string) $session['member']['email'] ) as $row ) {
						$ok = $ok || ( (int) $row['debate_id'] === (int) $debate['id'] && 'accepted' === $row['status'] );
					}
				}
			}
			$debate = $ok ? $debate : null;
		}
		if ( ! $debate || ! in_array( $debate['status'], array( 'agreed', 'ready' ), true ) || ! $debate['starts_at'] ) {
			return null;
		}
		return array( $debate, $viewer, $side );
	}

	/**
	 * A printable A4 scorecard for a judge who prefers paper (brief, section 9). The school prints it in
	 * advance and hands it over, or the judge prints their own. After the debate the judge scans the QR
	 * code and enters the final scores online: that entry is the official result.
	 */
	public static function view_paper(): string {
		AIADN_Front::set_title( 'Paper scorecard' );
		$none  = '<h1>We could not find that</h1><p>A paper scorecard is available for an agreed debate, to the schools and the judge.</p>';
		$found = self::printable_debate();
		if ( ! $found ) {
			return $none;
		}
		$debate = $found[0];
		self::theme_strand( $debate );

		$a      = self::school_name( $debate['school_a_id'] );
		$b      = self::school_name( $debate['school_b_id'] );
		$judge  = (int) $debate['judge_id'] ? AIADN_Debates::get_judge( (int) $debate['judge_id'] ) : null;
		$a_for  = 'for' === $debate['a_side'];
		$school = AIADN_Schools::get( (int) $debate['school_a_id'] );
		$join   = AIADN_Front::url( 'join', array( 'c' => $school['code'] ) );

		$blank = '<span class="aiadn__blank"></span>';
		$votes = static fn( string $label ) => '<p class="aiadn__paper-votes"><strong>' . esc_html( $label ) . '</strong> &nbsp; Agree ' . $blank . ' &nbsp; Disagree ' . $blank . ' &nbsp; Unsure ' . $blank . '</p>';

		$h  = '<h1>Paper scorecard</h1>';
		$h .= '<div class="aiadn__noprint"><p>The judge can score on their phone or on paper. <strong>Print this in advance</strong> and give it to the judge on the day. After the debate the judge scans the code at the bottom and enters the final scores online.</p>';
		$h .= '<p><button class="aiadn__button" type="button" onclick="window.print()">Print the scorecard</button></p></div>';

		$h .= '<div class="aiadn__paper">';
		$h .= '<div class="aiadn__paper-logo">' . AIADN_Front::logo_html() . '</div>';
		$h .= '<h2>Judge&rsquo;s scorecard</h2>';
		$h .= '<p class="aiadn__paper-head"><strong>Debate ' . esc_html( $debate['code'] ) . '</strong><br>' . esc_html( AIADN_Util::show( (string) $debate['starts_at'] ) ) . '<br>' . esc_html( AIADN_Debates::where( $debate ) ) . '</p>';
		$h .= '<p><strong>Motion:</strong> &ldquo;' . esc_html( $debate['motion_text'] ) . '&rdquo;<br><strong>Theme:</strong> ' . esc_html( AIADN_Motions::THEMES[ $debate['theme'] ] ?? '' ) . ( $judge ? '<br><strong>Judge:</strong> ' . esc_html( $judge['name'] ) : '' ) . '</p>';
		$h .= '<p class="aiadn__paper-order"><strong>Suggested running order (' . esc_html( (string) AIADN_Format::TOTAL_MINUTES ) . ' minutes):</strong> ' . esc_html( AIADN_Format::one_line( (string) $debate['age_group'], (string) $debate['starts_at'] ) ) . '</p>';
		$h .= '<p><strong>Students taking part (both schools):</strong> ' . $blank . '</p>';
		$h .= $votes( 'Room vote before the debate' );
		$h .= '<table class="aiadn__paper-table"><thead><tr><th scope="col">Score 1 (weak) to 5 (excellent)</th><th scope="col">' . esc_html( $a ) . '<br><span>' . ( $a_for ? 'FOR' : 'AGAINST' ) . '</span></th><th scope="col">' . esc_html( $b ) . '<br><span>' . ( $a_for ? 'AGAINST' : 'FOR' ) . '</span></th></tr></thead><tbody>';
		foreach ( AIADN_Scorecards::CRITERIA as $label ) {
			$h .= '<tr><th scope="row">' . esc_html( $label ) . '</th><td></td><td></td></tr>';
		}
		$h .= '<tr><th scope="row">Total</th><td></td><td></td></tr></tbody></table>';
		$h .= '<p class="aiadn__paper-winner"><strong>Winner:</strong> &nbsp; <span class="aiadn__box"></span> ' . esc_html( $a ) . ' &nbsp;&nbsp; <span class="aiadn__box"></span> ' . esc_html( $b ) . '</p>';
		$h .= '<p><strong>Comment for ' . esc_html( $a ) . '</strong></p><div class="aiadn__lines"></div>';
		$h .= '<p><strong>Comment for ' . esc_html( $b ) . '</strong></p><div class="aiadn__lines"></div>';
		$h .= $votes( 'Room vote after the debate' );
		$h .= '<div class="aiadn__paper-qr"><div class="aiadn__paper-qrimg">' . AIADN_QR::svg( $join, 'QR code that opens the sign-in page' ) . '</div><p>After the debate, scan this code. It opens the sign-in page with the school code filled in. Choose <strong>Judge</strong>, enter your email, then the code we email you, then enter these scores. <strong>The online entry is the official result.</strong></p></div>';
		$h .= '</div>';
		return $h;
	}

	/* ------------------------------------------------------------------ */
	/* /conversation/prep/  (the prep pack and running order)              */
	/* ------------------------------------------------------------------ */

	/**
	 * The prep pack: a checklist, how students take part, the weeks before and a suggested running order.
	 * The judge sees the running order without the preparation. Every section folds away, to save scrolling.
	 */
	public static function view_prep(): string {
		AIADN_Front::set_title( 'Prep pack' );
		$none  = '<h1>We could not find that</h1><p>The prep pack is available for an agreed debate, to the schools and the judge.</p>';
		$found = self::printable_debate();
		if ( ! $found ) {
			return $none;
		}
		list( $debate, $viewer, $side ) = $found;
		self::theme_strand( $debate );

		$judge_view = 'judge' === $viewer;
		$session    = AIADN_Auth::current();
		$can_act    = ! $judge_view && $session && in_array( $session['role'], array( 'lead', 'teacher' ), true );
		$age        = AIADN_Format::age( (string) $debate['age_group'] );
		$a          = self::school_name( $debate['school_a_id'] );
		$b          = self::school_name( $debate['school_b_id'] );
		$a_for      = 'for' === $debate['a_side'];
		$motion     = AIADN_Motions::all()[ $debate['motion_key'] ] ?? array();
		if ( ( $motion['text'] ?? '' ) !== $debate['motion_text'] ) {
			$motion = array(); // A debate made before the motion bank changed: its key now means something else.
		}
		$raw = AIADN_Front::get( 't' );

		if ( $judge_view ) {
			AIADN_Front::set_title( 'Running order' );
			$back = AIADN_Front::url( 'judge', '' !== $raw ? array( 't' => $raw ) : array() );
		} else {
			$back = AIADN_Debates::url( $debate );
		}

		$h  = '<h1>' . ( $judge_view ? 'Running order' : 'Prep pack' ) . '</h1>';
		$h .= '<p class="aiadn__small aiadn__noprint"><a href="' . esc_url( $back ) . '">&larr; Back</a></p>';
		$h .= 'saved' === AIADN_Front::get( 'msg' ) ? AIADN_Front::notice( 'Saved.', 'info' ) : '';
		$h .= '<h2 class="aiadn__printonly">' . ( $judge_view ? 'Running order' : 'Prep pack' ) . '</h2>';
		$h .= '<div class="aiadn__panel"><dl class="aiadn__details">';
		$h .= '<dt>Debate</dt><dd>' . esc_html( $debate['code'] ) . '</dd>';
		$h .= '<dt>Motion</dt><dd>&ldquo;' . esc_html( $debate['motion_text'] ) . '&rdquo;</dd>';
		$h .= '<dt>Theme</dt><dd>' . esc_html( AIADN_Motions::THEMES[ $debate['theme'] ] ?? '' ) . '</dd>';
		$h .= '<dt>Age group</dt><dd>' . esc_html( AIADN_Motions::AGES[ $age ] ) . '</dd>';
		$h .= '<dt>When</dt><dd>' . esc_html( AIADN_Util::show( (string) $debate['starts_at'] ) ) . '</dd>';
		$h .= '<dt>For (Proposition)</dt><dd>' . esc_html( $a_for ? $a : $b ) . '</dd>';
		$h .= '<dt>Against (Opposition)</dt><dd>' . esc_html( $a_for ? $b : $a ) . '</dd>';
		if ( ! $judge_view && '' !== $side ) {
			$you_for = 'a' === $side ? $a_for : ! $a_for;
			$h      .= '<dt>Your school argues</dt><dd><strong>' . ( $you_for ? 'FOR (Proposition)' : 'AGAINST (Opposition)' ) . '</strong></dd>';
		}
		$h .= '</dl></div>';

		$h .= '<p class="aiadn__small aiadn__noprint">Each section folds away. <button class="aiadn__link" type="button" onclick="document.querySelectorAll(\'.aiadn__fold\').forEach(function(d){d.open=true})">Open all</button> &middot; <button class="aiadn__link" type="button" onclick="document.querySelectorAll(\'.aiadn__fold\').forEach(function(d){d.open=false})">Close all</button></p>';

		if ( ! $judge_view ) {
			$h .= self::fold( 'Your checklist', self::prep_checklist( $debate, $side, $can_act ), true );
			$h .= self::fold( 'How your students take part', self::prep_students( $age, $motion ), false );
			$h .= self::fold( 'Two to three weeks before', self::prep_before( $debate, $age ), false );
		}

		$h .= self::fold( 'On the day: ' . (int) AIADN_Format::TOTAL_MINUTES . ' minutes (recommended)', self::prep_run( $debate, $age, $judge_view ), $judge_view );

		if ( ! $judge_view ) {
			$who  = '<p>For a class of about 30. Each school names six roles: two survey presenters, an opening speaker, a rebuttal speaker, a closing speaker and a lead note-taker. Everyone else is a note-taker or a questioner, so the whole room has a job.</p><dl class="aiadn__details">';
			$who .= '<dt>The chair</dt><dd>Introduces the motion and rules, keeps time with a timer everyone can see, takes the floor questions and announces the vote. Agree between you who this is.</dd>';
			foreach ( AIADN_Format::ROLES as $role => $job ) {
				$who .= '<dt>' . esc_html( $role ) . '</dt><dd>' . esc_html( $job ) . '</dd>';
			}
			$who .= '</dl><p class="aiadn__small">Thirty minutes leaves little slack, so a timer helps. If you can manage 35 minutes, give the extra five to floor questions: it is the part audiences most often find too short.</p>';
			$h   .= self::fold( 'Who does what', $who, false );
		}
		$h .= '<p class="aiadn__small">The format is a draft and will be reviewed by debate educators before national use.</p>';
		$h .= '<p class="aiadn__noprint"><button class="aiadn__button aiadn__button--quiet" type="button" onclick="window.print()">Print this page</button></p>';
		// Folded sections would print folded: open them all for printing, then put them back.
		$h .= '<script>(function(){var s=[];window.addEventListener("beforeprint",function(){s=[];document.querySelectorAll(".aiadn__fold").forEach(function(d){s.push(d.open);d.open=true})});window.addEventListener("afterprint",function(){document.querySelectorAll(".aiadn__fold").forEach(function(d,i){d.open=!!s[i]})})})();</script>';
		return $h;
	}

	/** A section that folds away. Its heading is the summary. */
	public static function fold( string $title, string $body, bool $open ): string {
		return '<details class="aiadn__panel aiadn__fold"' . ( $open ? ' open' : '' ) . '><summary><h2>' . esc_html( $title ) . '</h2></summary>' . $body . '</details>';
	}

	/** The school's own to-do list, saved as they tick. */
	private static function prep_checklist( array $debate, string $side, bool $can_act ): string {
		$ticks = '' !== $side ? AIADN_Debates::checklist( $debate, $side ) : array();
		$items = AIADN_Format::checklist_for( (string) $debate['format'] );
		$h     = '<p class="aiadn__small">For your own records: nobody else sees your ticks and they do not block anything.</p>';
		if ( $can_act ) {
			$h .= AIADN_Debate_Front::form_open( $debate, 'prep_checklist' );
		}
		foreach ( array( 'before' => 'In the weeks before', 'day' => 'On the day' ) as $when => $heading ) {
			$h .= '<h3>' . esc_html( $heading ) . '</h3>';
			foreach ( $items as $key => $item ) {
				if ( $when !== $item['when'] ) {
					continue;
				}
				$h .= '<label class="aiadn__radio"><input type="checkbox" name="tick_' . esc_attr( $key ) . '" value="1"' . ( ! empty( $ticks[ $key ] ) ? ' checked' : '' ) . ( $can_act ? '' : ' disabled' ) . '> ' . esc_html( $item['text'] ) . '</label>';
			}
		}
		if ( $can_act ) {
			$h .= '<button class="aiadn__button aiadn__button--quiet" type="submit">Save my ticks</button></form>';
		}
		return $h;
	}

	/** How students take part at this age, and the prompt that goes with the motion. */
	private static function prep_students( string $age, array $motion ): string {
		$h = '';
		if ( 'primary' === $age ) {
			$h .= '<p>Pupils move to a corner of the room, or vote with a show of hands, then tell a talk partner why they chose it. Keep the sentence starter on display and prompt as needed.</p>';
			if ( ! empty( $motion['starter'] ) ) {
				$h .= '<p><strong>Sentence starter:</strong> &ldquo;' . esc_html( $motion['starter'] ) . '&rdquo;</p>';
			}
		} elseif ( 'secondary' === $age ) {
			$h .= '<p>Small groups discuss the question, using talking roles: Builder, Challenger and Summariser. After the first round, give each group the challenge card so it tests its own view. Teachers can shorten the discussion for Key Stage 3.</p>';
			if ( ! empty( $motion['challenge'] ) ) {
				$h .= '<p><strong>Challenge card:</strong> &ldquo;' . esc_html( $motion['challenge'] ) . '&rdquo;</p>';
			}
		} else {
			$h .= '<p>Students prepare with the central tension and the research prompt, then debate using the running order below.</p>';
			if ( ! empty( $motion['tension'] ) ) {
				$h .= '<p><strong>Central tension:</strong> ' . esc_html( $motion['tension'] ) . '</p>';
			}
			if ( ! empty( $motion['research'] ) ) {
				$h .= '<p><strong>Research prompt:</strong> ' . esc_html( $motion['research'] ) . '</p>';
			}
		}
		return $h;
	}

	/** The survey and the research, for the weeks before. */
	private static function prep_before( array $debate, string $age ): string {
		$before = AIADN_Format::before( $age );

		$h  = '<h3>Survey your own students</h3><p>' . esc_html( $before['survey'] ) . ' Use the same questions as the other school, so the results can be compared on the day.</p><ol class="aiadn__list">';
		foreach ( AIADN_Format::survey_questions( $debate ) as $q ) {
			$h .= '<li>' . esc_html( $q['text'] ) . ' <span class="aiadn__meta">(' . esc_html( $q['answers'] ) . ')</span></li>';
		}
		$h .= '</ol><p><strong>Another way for students to complete it.</strong> They can answer on their own device instead. Show the whiteboard page, and they scan the code and type the class PIN. It is anonymous, and covers the theme statement' . ( count( AIADN_Format::survey_questions( $debate ) ) > 3 ? 's' : '' ) . ' above. The motion question and the reason still need a class vote or a form. <a href="' . esc_url( AIADN_Front::url( 'board' ) ) . '">Open the whiteboard page</a>.</p>';
		$h .= '<p class="aiadn__small">Keep it to a class or year group and do not record names. On the day, share a simple chart and one key finding.</p>';

		$h .= '<h3>Research</h3><p>Split the team into three research groups. ' . esc_html( $before['research'] ) . '</p><ul class="aiadn__list">';
		foreach ( AIADN_Format::RESEARCH_GROUPS as $group => $ask ) {
			$h .= '<li><strong>' . esc_html( $group ) . '.</strong> ' . esc_html( $ask ) . '</li>';
		}
		$h .= '</ul><h3>Check your sources</h3><ul class="aiadn__list">';
		$h .= '<li><strong>Use more than one source</strong> for every fact you plan to use, from different places.</li>';
		$h .= '<li><strong>Do not rely on AI-generated content.</strong> It can be wrong and still sound certain. If AI suggests a fact, find it somewhere else before you use it.</li>';
		$h .= '<li><strong>Write down where each fact came from</strong>, so students can answer &ldquo;how do you know?&rdquo;</li></ul>';
		$h .= '<p class="aiadn__small">Ready-made fact cards and sources are still being prepared. Until then, choose your own and check them as above.</p>';
		return $h;
	}

	/** The running order as two tables, start-up then debate. Recommended, not compulsory. */
	private static function prep_run( array $debate, string $age, bool $judge_view ): string {
		$stages = AIADN_Format::run( $age );
		$h      = '<p>' . ( $judge_view
			? 'This is the schools&rsquo; suggested running order. They may adapt it to suit their setting and their students.'
			: 'This is a recommended running order, <strong>not a requirement</strong>. Change the stages or the timings to suit your school and what your students can do.' ) . '</p>';
		$h     .= '<p class="aiadn__small">' . esc_html( AIADN_Motions::AGES[ $age ] ) . ' timings. The first column starts from the time of the debate.</p>';
		foreach ( array( 'start' => 'Start-up', 'debate' => 'Debate' ) as $part => $heading ) {
			$rows = array_filter( $stages, static fn( $s ) => $part === $s['part'] );
			if ( ! $rows ) {
				continue;
			}
			$first = reset( $rows );
			$last  = end( $rows );
			$h    .= '<h3>' . esc_html( $heading ) . ' <span class="aiadn__meta">' . esc_html( AIADN_Format::clock( $first['start'] ) . ' to ' . AIADN_Format::clock( $last['end'] ) ) . '</span></h3>';
			$h    .= '<table class="aiadn__table aiadn__run"><thead><tr><th scope="col">Time</th><th scope="col">Stage</th><th scope="col">What happens</th><th scope="col">Length</th></tr></thead><tbody>';
			foreach ( $rows as $s ) {
				$h .= '<tr><td>' . esc_html( AIADN_Format::clock_time( (string) $debate['starts_at'], $s['start'] ) ) . ' <span class="aiadn__meta">(' . esc_html( AIADN_Format::clock( $s['start'] ) ) . ')</span></td><th scope="row">' . esc_html( $s['label'] ) . '</th><td>' . esc_html( $s['what'] ) . '</td><td>' . AIADN_Format::minutes_label( $s['minutes'], $s['each'] ) . '</td></tr>';
			}
			$h .= '</tbody></table>';
		}
		return $h;
	}

	/* ------------------------------------------------------------------ */
	/* /conversation/results/                                              */
	/* ------------------------------------------------------------------ */

	public static function view_results(): string {
		AIADN_Front::set_title( 'Your results' );
		$session = self::require_school_session();
		$school  = $session['school'];
		$sum     = AIADN_Results::summary( (int) $school['id'] );

		$h  = '<h1>Your results</h1>';
		$h .= '<p class="aiadn__small"><a href="' . esc_url( AIADN_Front::url( 'school' ) ) . '">&larr; Your school</a></p>';
		$h .= '<div class="aiadn__stats">';
		foreach ( array( 'Debates' => $sum['count'], 'Won' => $sum['won'], 'Students' => $sum['students'], 'Schools' => $sum['schools'], 'Themes' => $sum['themes'] ) as $label => $n ) {
			$h .= '<div class="aiadn__stat"><strong>' . (int) $n . '</strong><span>' . esc_html( $label ) . '</span></div>';
		}
		$h .= '</div>';

		if ( ! $sum['rows'] ) {
			return $h . '<div class="aiadn__panel"><p>No results yet. Once a judge submits a scorecard, it appears here.</p></div>';
		}

		$h .= '<div class="aiadn__panel"><h2>Debates</h2><table class="aiadn__table"><thead><tr><th scope="col">Date</th><th scope="col">Against</th><th scope="col">Theme</th><th scope="col">Score</th><th scope="col">Result</th></tr></thead><tbody>';
		foreach ( $sum['rows'] as $r ) {
			$d    = $r['debate'];
			$card = $r['card'];
			$us   = 'a' === $r['side'] ? $card['a_total'] : $card['b_total'];
			$them = 'a' === $r['side'] ? $card['b_total'] : $card['a_total'];
			$h   .= '<tr><td>' . self::esc( AIADN_Util::show( (string) $d['starts_at'], 'j M Y' ) ) . '</td><td>' . self::esc( $r['opponent'] ) . '</td><td>' . self::esc( AIADN_Motions::THEMES[ $d['theme'] ] ?? '' ) . '</td><td>' . (int) $us . '-' . (int) $them . '</td><td><a href="' . esc_url( AIADN_Debates::url( $d ) ) . '">' . ( $r['won'] ? 'Won' : 'Lost' ) . '</a></td></tr>';
		}
		$h .= '</tbody></table></div>';

		// Our scores against the average for the same age group.
		$by_age = array();
		foreach ( $sum['rows'] as $r ) {
			$by_age[ $r['debate']['age_group'] ][] = $r;
		}
		$h .= '<div class="aiadn__panel"><h2>Our scores, out of 5</h2>';
		foreach ( $by_age as $age => $rows ) {
			$avg = AIADN_Results::averages( $age );
			$h  .= '<h3>' . self::esc( AIADN_Motions::AGES[ $age ] ?? $age ) . '</h3>';
			foreach ( AIADN_Scorecards::CRITERIA as $key => $label ) {
				$total = 0;
				foreach ( $rows as $r ) {
					$total += (int) $r['card'][ $r['side'] . '_' . $key ];
				}
				$ours = round( $total / count( $rows ), 1 );
				$h   .= '<div class="aiadn__barrow"><span>' . esc_html( $label ) . '</span><span class="aiadn__bar" aria-hidden="true"><span style="width:' . (int) round( $ours / 5 * 100 ) . '%"></span></span><span>' . esc_html( number_format( $ours, 1 ) ) . ( $avg ? ' <span class="aiadn__meta">average ' . esc_html( number_format( $avg[ $key ], 1 ) ) . '</span>' : '' ) . '</span></div>';
			}
			if ( ! $avg ) {
				$h .= '<p class="aiadn__small">Averages appear once at least ' . (int) AIADN_Results::MIN_SAMPLE . ' debates in this age group have been scored.</p>';
			}
		}
		$h .= '</div>';

		$h .= '<div class="aiadn__panel"><h2>Room vote movement</h2><ul class="aiadn__list">';
		foreach ( $sum['rows'] as $r ) {
			$card = $r['card'];
			if ( $card['vb_agree'] + $card['va_agree'] + $card['vb_disagree'] + $card['va_disagree'] > 0 ) {
				$change = (int) $card['va_agree'] - (int) $card['vb_agree'];
				$h     .= '<li>' . self::esc( AIADN_Util::show( (string) $r['debate']['starts_at'], 'j M' ) ) . ': agree ' . (int) $card['vb_agree'] . ' &rarr; ' . (int) $card['va_agree'] . ' (' . ( $change >= 0 ? '+' : '' ) . $change . ')</li>';
			}
		}
		$h .= '</ul></div>';

		$prog = AIADN_Certificates::progress( (int) $school['id'] );
		$h   .= '<div class="aiadn__panel"><h2>Certificate</h2><p>' . (int) $prog['have'] . ' of ' . (int) $prog['need'] . ' debates against different schools.</p><a class="aiadn__button aiadn__button--quiet" href="' . esc_url( AIADN_Front::url( 'certificate' ) ) . '">Certificate</a></div>';
		return $h;
	}

	/* ------------------------------------------------------------------ */
	/* /conversation/issue/                                                */
	/* ------------------------------------------------------------------ */

	public static function view_issue(): string {
		AIADN_Front::set_title( 'Report an issue' );
		$actor  = null; // array( school_id, role, email, debate, judge_mode, raw ).
		$raw_t  = AIADN_Front::get( 't' ) ?: AIADN_Front::post( 't' );
		$sess   = AIADN_Auth::current();

		if ( '' !== $raw_t || ( $sess && 'judge' === $sess['role'] ) ) {
			list( $judge, $debate, $mode, $raw, $error ) = self::judge_context();
			if ( $error ) {
				return $error;
			}
			$actor = array( 'school_id' => 0, 'role' => 'judge', 'email' => $judge['email'], 'debate' => $debate, 'mode' => $mode, 'raw' => $raw );
		} else {
			$session = self::require_school_session();
			$debate  = self::debate_for_session( $session, AIADN_Front::get( 'd' ) ?: AIADN_Front::post( 'd' ) );
			if ( ! $debate ) {
				return '<h1>We could not find that debate</h1><p><a href="' . esc_url( AIADN_Front::url( 'school' ) ) . '">Go to your school page</a>.</p>';
			}
			$actor = array( 'school_id' => (int) $session['school_id'], 'role' => 'slt' === $session['role'] ? 'slt' : 'teacher', 'email' => (string) $session['member']['email'], 'debate' => $debate, 'mode' => 'session', 'raw' => '', 'session_role' => $session['role'] );
		}
		$debate = $actor['debate'];
		self::theme_strand( $debate );
		if ( ! $debate['school_b_id'] ) {
			return '<h1>Nothing to report yet</h1><p>This debate has not been matched.</p>';
		}

		$errors = array();
		$values = array();
		if ( AIADN_Front::is_post() ) {
			$ok = 'judge' === $actor['role'] ? self::judge_authorised( $actor['mode'], $sess ) : AIADN_Auth::csrf_ok( AIADN_Front::post( 'csrf' ) );
			if ( $ok ) {
				list( $errors, $values ) = self::handle_issue_action( $actor, $debate );
				$debate = AIADN_Debates::get( (int) $debate['id'] );
			}
		}
		return self::render_issue( $actor, $debate, $errors, $values );
	}

	private static function issue_url( array $actor, array $debate, array $args = array() ): string {
		return AIADN_Front::url( 'issue', array_merge( 'token' === $actor['mode'] ? array( 't' => $actor['raw'] ) : array( 'd' => $debate['code'] ), $args ) );
	}

	/** @return array{0:array,1:array} errors, values; redirects on success */
	private static function handle_issue_action( array $actor, array $debate ): array {
		$action = AIADN_Front::post( 'aiadn_action' );
		$school = (int) $actor['school_id'];

		if ( 'report_issue' === $action ) {
			$category = AIADN_Front::post( 'category' );
			$details  = sanitize_textarea_field( wp_unslash( (string) ( $_POST['details'] ?? '' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$values   = array( 'category' => $category, 'details' => $details );
			if ( ! isset( AIADN_Issues::CATEGORIES[ $category ] ) ) {
				return array( array( 'category' => 'Choose what happened.' ), $values );
			}
			if ( ! AIADN_Util::allow( 'issue|' . $debate['id'], 10, HOUR_IN_SECONDS ) ) {
				return array( array( 'form' => 'Too many reports. Please wait a while.' ), $values );
			}
			AIADN_Issues::create( $debate, $school, $actor['role'], $actor['email'], $category, $details );
			wp_safe_redirect( self::issue_url( $actor, $debate, array( 'msg' => 'reported' ) ) );
			exit;
		}

		$issue = AIADN_Issues::get( (int) AIADN_Front::post( 'issue_id' ) );
		if ( ! $issue || (int) $issue['debate_id'] !== (int) $debate['id'] ) {
			return array( array(), array() );
		}
		$is_slt = isset( $actor['session_role'] ) && 'slt' === $actor['session_role'];
		$is_lead = isset( $actor['session_role'] ) && in_array( $actor['session_role'], array( 'lead', 'slt' ), true );

		if ( 'resolve_issue' === $action && $is_slt ) {
			if ( AIADN_Issues::resolve( $issue, $debate, $school, AIADN_Front::post( 'resolution' ) ) ) {
				wp_safe_redirect( self::issue_url( $actor, $debate, array( 'msg' => 'resolved' ) ) );
				exit;
			}
		}
		if ( 'reopen_issue' === $action && $is_lead ) {
			if ( AIADN_Issues::reopen( $issue, $debate, $school ) ) {
				wp_safe_redirect( self::issue_url( $actor, $debate, array( 'msg' => 'reopened' ) ) );
				exit;
			}
		}
		return array( array(), array() );
	}

	private static function render_issue( array $actor, array $debate, array $errors, array $v ): string {
		$flash = array( 'reported' => 'Issue logged. Both schools have been told.', 'resolved' => 'The issue is closed.', 'reopened' => 'The issue has been re-opened.' );
		$msg   = AIADN_Front::get( 'msg' );
		$a     = self::school_name( $debate['school_a_id'] );
		$b     = self::school_name( $debate['school_b_id'] );
		$h     = '<p class="aiadn__small"><a href="' . esc_url( 'judge' === $actor['role'] ? AIADN_Front::url( 'judge' ) : AIADN_Debates::url( $debate ) ) . '">&larr; Back</a></p>';
		$h    .= '<h1>Issues on ' . self::esc( $debate['code'] ) . '</h1>';
		$h    .= isset( $flash[ $msg ] ) ? AIADN_Front::notice( $flash[ $msg ], 'info' ) : '';
		$h    .= '<p>' . self::esc( $a ) . ' v ' . self::esc( $b ) . '</p>';
		$held  = AIADN_Issues::is_held( (int) $debate['id'] );
		if ( $held ) {
			$h .= '<p class="aiadn__notice aiadn__notice--error">Result on hold. It will not count towards certificates, or appear publicly, until the issue is closed.</p>';
		}

		$is_slt = isset( $actor['session_role'] ) && 'slt' === $actor['session_role'];
		$is_lead = isset( $actor['session_role'] ) && in_array( $actor['session_role'], array( 'lead', 'slt' ), true );
		foreach ( AIADN_Issues::for_debate( (int) $debate['id'] ) as $issue ) {
			$who = 'judge' === $issue['reporter_role'] ? 'the judge' : self::school_name( $issue['reporter_school_id'] );
			$h  .= '<div class="aiadn__panel"><h2>' . self::esc( AIADN_Issues::code( $issue ) ) . '</h2><dl class="aiadn__details"><dt>What</dt><dd>' . esc_html( AIADN_Issues::CATEGORIES[ $issue['category'] ] ?? $issue['category'] ) . '</dd><dt>Reported</dt><dd>' . self::esc( AIADN_Util::show( $issue['created_at'], 'j M, H:i' ) ) . ' by ' . self::esc( $who ) . '</dd>';
			if ( '' !== trim( (string) $issue['details'] ) ) {
				$h .= '<dt>Details</dt><dd>' . nl2br( esc_html( $issue['details'] ) ) . '</dd>';
			}
			$h .= '<dt>Status</dt><dd>' . ( 'reported' === $issue['status'] ? 'Schools resolving' : 'Closed: ' . esc_html( AIADN_Issues::RESOLUTIONS[ $issue['resolution'] ] ?? '' ) ) . '</dd></dl>';
			if ( 'safeguarding' === $issue['category'] ) {
				$h .= '<p class="aiadn__small">Safeguarding concerns are handled under each school\'s own procedure. This record does not replace telling your Designated Safeguarding Lead.</p>';
			}
			if ( 'reported' === $issue['status'] && $is_slt ) {
				$h .= '<h3>Close this issue</h3><form method="post" action="' . esc_url( self::issue_url( $actor, $debate ) ) . '" class="aiadn__form">' . AIADN_Front::csrf_field() . '<input type="hidden" name="issue_id" value="' . (int) $issue['id'] . '"><input type="hidden" name="aiadn_action" value="resolve_issue">';
				foreach ( AIADN_Issues::RESOLUTIONS as $key => $label ) {
					$h .= '<label class="aiadn__radio"><input type="radio" name="resolution" value="' . esc_attr( $key ) . '" required> ' . esc_html( $label ) . '</label>';
				}
				$h .= '<button class="aiadn__button" type="submit">Mark as resolved</button></form><p class="aiadn__small">A senior leader at either school can close an issue. The other school is told and can re-open it for a week if the result stands.</p>';
			} elseif ( 'reported' === $issue['status'] ) {
				$h .= '<p class="aiadn__small">A senior leader at either school closes the issue. You can ask the programme team for help: ' . esc_html( AIADN_Util::team_email() ) . '</p>';
			}
			if ( $is_lead && AIADN_Issues::can_reopen( $issue, (int) $actor['school_id'] ) ) {
				$h .= '<form method="post" action="' . esc_url( self::issue_url( $actor, $debate ) ) . '" class="aiadn__form">' . AIADN_Front::csrf_field() . '<input type="hidden" name="issue_id" value="' . (int) $issue['id'] . '"><input type="hidden" name="aiadn_action" value="reopen_issue"><button class="aiadn__button aiadn__button--quiet" type="submit">Re-open this issue</button></form>';
			}
			$h .= '</div>';
		}

		// ---- report a new one ----
		$h .= '<div class="aiadn__panel"><h2>Report an issue</h2>';
		$h .= isset( $errors['form'] ) ? AIADN_Front::notice( $errors['form'], 'error' ) : '';
		$h .= '<form method="post" action="' . esc_url( self::issue_url( $actor, $debate ) ) . '" class="aiadn__form">';
		$h .= 'token' === $actor['mode'] ? '<input type="hidden" name="t" value="' . esc_attr( $actor['raw'] ) . '">' : AIADN_Front::csrf_field();
		$h .= '<input type="hidden" name="aiadn_action" value="report_issue"><fieldset class="aiadn__roles"><legend>What happened?</legend>';
		foreach ( AIADN_Issues::CATEGORIES as $key => $label ) {
			$h .= '<label class="aiadn__radio"><input type="radio" name="category" value="' . esc_attr( $key ) . '"' . checked( (string) ( $v['category'] ?? '' ), $key, false ) . '> ' . esc_html( $label ) . '</label>';
		}
		$h .= '</fieldset>' . self::err( $errors, 'category' );
		$h .= '<label for="is-details">Details <span class="aiadn__opt">(optional)</span></label><textarea id="is-details" name="details" rows="4" maxlength="2000">' . esc_textarea( (string) ( $v['details'] ?? '' ) ) . '</textarea>';
		$h .= '<p class="aiadn__small"><strong>Safeguarding concern?</strong> Follow your own school\'s procedure and tell your Designated Safeguarding Lead first. This form does not replace that.</p>';
		$h .= '<p class="aiadn__small">This goes to both schools\' senior leaders and the programme team. It is private, and the result is held while the issue is open.</p>';
		$h .= '<button class="aiadn__button" type="submit">Send report</button></form></div>';
		return $h;
	}

	/* ------------------------------------------------------------------ */
	/* /conversation/certificate/  and  /conversation/check/               */
	/* ------------------------------------------------------------------ */

	public static function view_certificate(): string {
		AIADN_Front::set_title( 'Certificate' );
		$session = self::require_school_session();
		$school  = $session['school'];
		$cert    = AIADN_Certificates::get_for_school( (int) $school['id'] );
		$prog    = AIADN_Certificates::progress( (int) $school['id'] );

		$h = '<h1>Certificate</h1><p class="aiadn__small aiadn__noprint"><a href="' . esc_url( AIADN_Front::url( 'results' ) ) . '">&larr; Your results</a></p>';
		if ( ! $cert ) {
			return $h . '<div class="aiadn__panel"><h2>Not yet</h2><p><strong>' . (int) $prog['have'] . ' of ' . (int) $prog['need'] . '</strong> debates against different schools. The certificate unlocks automatically when you have debated two different schools. A second debate against the same school counts as participation, but not towards the certificate.</p></div>';
		}
		if ( 'withdrawn' === $cert['status'] ) {
			return $h . '<div class="aiadn__panel"><h2>Withdrawn</h2><p>This certificate was withdrawn on ' . self::esc( AIADN_Util::show( (string) $cert['withdrawn_at'], 'j M Y' ) ) . ': ' . self::esc( $cert['withdrawn_reason'] ) . '. It comes back when you have completed two debates against different schools.</p></div>';
		}
		$snap  = json_decode( (string) $cert['snapshot'], true );
		$check = AIADN_Front::url( 'check', array( 'ref' => $cert['reference'] ) );
		$h    .= '<div class="aiadn__cert"><div class="aiadn__cert-logo">' . AIADN_Front::logo_html() . '</div><h2>National AI Debate School</h2><p>awarded to</p><p class="aiadn__cert-school">' . self::esc( $school['name'] ) . '</p><p>for debating with two different schools:</p><ul class="aiadn__list">';
		foreach ( (array) $snap as $row ) {
			$h .= '<li>' . self::esc( $row['opponent'] ?? '' ) . ', ' . self::esc( AIADN_Util::show( (string) ( $row['date'] ?? '' ), 'j M Y' ) ) . ', ' . self::esc( AIADN_Motions::THEMES[ $row['theme'] ?? '' ] ?? '' ) . '</li>';
		}
		$h .= '</ul><p class="aiadn__cert-strands">SAFE &middot; SMART &middot; CREATIVE &middot; RESPONSIBLE &middot; FUTURE</p><div class="aiadn__cert-qr">' . AIADN_QR::svg( $check, 'QR code that checks this certificate' ) . '</div><p class="aiadn__cert-ref">Reference: <strong>' . self::esc( $cert['reference'] ) . '</strong><br>Scan the code, or check it at ' . self::esc( $check ) . '</p></div>';
		$h .= '<button class="aiadn__button aiadn__noprint" type="button" onclick="window.print()">Print certificate</button>';
		return $h;
	}

	public static function view_check(): string {
		AIADN_Front::set_title( 'Certificate check' );
		$h   = '<h1>Certificate check</h1>';
		$raw = AIADN_Front::get( 'ref' );
		$h  .= '<form method="get" action="' . esc_url( AIADN_Front::url( 'check' ) ) . '" class="aiadn__form"><label for="ck-ref">Certificate reference</label><input id="ck-ref" name="ref" type="text" value="' . esc_attr( $raw ) . '" placeholder="AIAD-DS-XXXXX" autocomplete="off" class="aiadn__code"><button class="aiadn__button" type="submit">Check</button></form>';
		if ( '' === $raw ) {
			return $h;
		}
		if ( ! AIADN_Util::allow( 'check|' . AIADN_Util::client_ip(), 60, 600 ) ) {
			return $h . AIADN_Front::notice( AIADN_Front::message( 'slow' ), 'error' );
		}
		$ref  = AIADN_Certificates::normalise_reference( $raw );
		$cert = $ref ? AIADN_Certificates::get_by_reference( $ref ) : null;
		if ( ! $cert ) {
			return $h . '<div class="aiadn__panel"><h2>Not found</h2><p>We could not find a certificate with that reference.</p></div>';
		}
		$school = self::school_name( $cert['school_id'] );
		if ( 'issued' === $cert['status'] ) {
			return $h . '<div class="aiadn__panel"><h2>Valid</h2><p><strong>National AI Debate School</strong><br>' . self::esc( $school ) . '<br>Issued ' . self::esc( AIADN_Util::show( $cert['issued_at'], 'j M Y' ) ) . '</p></div>';
		}
		return $h . '<div class="aiadn__panel"><h2>Withdrawn</h2><p>This certificate for ' . self::esc( $school ) . ' was withdrawn on ' . self::esc( AIADN_Util::show( (string) $cert['withdrawn_at'], 'j M Y' ) ) . ': ' . self::esc( $cert['withdrawn_reason'] ) . '.</p></div>';
	}

	/* ------------------------------------------------------------------ */
	/* /conversation/debates/  public results                              */
	/* ------------------------------------------------------------------ */

	public static function view_debates(): string {
		AIADN_Front::set_title( 'Debate results' );
		$theme = AIADN_Front::get( 'theme' );
		$age   = AIADN_Front::get( 'age' );
		$rows  = AIADN_Results::public_list( array( 'theme' => $theme, 'age' => $age ) );

		$h  = '<h1>Debate results</h1><p>Schools across the country debating AI, each with an independent judge. No teacher or student details are ever shown.</p>';
		$h .= '<form method="get" action="' . esc_url( AIADN_Front::url( 'debates' ) ) . '" class="aiadn__form aiadn__filters"><div><label for="f-theme">Theme</label><select id="f-theme" name="theme"><option value="">All themes</option>';
		foreach ( AIADN_Motions::THEMES as $key => $label ) {
			$h .= '<option value="' . esc_attr( $key ) . '"' . selected( $theme, $key, false ) . '>' . esc_html( $label ) . '</option>';
		}
		$h .= '</select></div><div><label for="f-age">Age group</label><select id="f-age" name="age"><option value="">All ages</option>';
		foreach ( AIADN_Motions::AGES as $key => $label ) {
			$h .= '<option value="' . esc_attr( $key ) . '"' . selected( $age, $key, false ) . '>' . esc_html( $label ) . '</option>';
		}
		$h .= '</select></div><button class="aiadn__button aiadn__button--quiet" type="submit">Filter</button></form>';
		if ( ! $rows ) {
			return $h . '<p>No results to show yet.</p>';
		}
		$h .= '<table class="aiadn__table aiadn__public"><caption class="aiadn__sr">Debate results</caption><thead><tr><th scope="col">Date</th><th scope="col">Schools</th><th scope="col">Theme</th><th scope="col">Motion</th><th scope="col">Winner</th><th scope="col">Judge</th></tr></thead><tbody>';
		foreach ( $rows as $r ) {
			$d  = $r['debate'];
			$h .= '<tr><th scope="row">' . self::esc( AIADN_Util::show( (string) $d['starts_at'], 'j M Y' ) ) . '</th><td>' . self::esc( $r['a'] ) . ' v ' . self::esc( $r['b'] ) . '</td><td>' . self::esc( AIADN_Motions::THEMES[ $d['theme'] ] ?? '' ) . '</td><td>' . self::esc( $d['motion_text'] ) . '</td><td>' . self::esc( $r['winner'] ) . '</td><td>' . self::esc( $r['judge'] ) . '</td></tr>';
		}
		return $h . '</tbody></table><p class="aiadn__small">Judges are named only if they agreed. Results under review are not shown.</p>';
	}
}
