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
		$h    .= '<div class="aiadn__cert"><p class="aiadn__cert-top">AI Awareness Day 2027</p><h2>National AI Debate School</h2><p>awarded to</p><p class="aiadn__cert-school">' . self::esc( $school['name'] ) . '</p><p>for debating with two different schools:</p><ul class="aiadn__list">';
		foreach ( (array) $snap as $row ) {
			$h .= '<li>' . self::esc( $row['opponent'] ?? '' ) . ', ' . self::esc( AIADN_Util::show( (string) ( $row['date'] ?? '' ), 'j M Y' ) ) . ', ' . self::esc( AIADN_Motions::THEMES[ $row['theme'] ?? '' ] ?? '' ) . '</li>';
		}
		$h .= '</ul><p class="aiadn__cert-strands">SAFE &middot; SMART &middot; CREATIVE &middot; RESPONSIBLE &middot; FUTURE</p><p class="aiadn__cert-ref">Reference: <strong>' . self::esc( $cert['reference'] ) . '</strong><br>Check it at ' . self::esc( $check ) . '</p></div>';
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
