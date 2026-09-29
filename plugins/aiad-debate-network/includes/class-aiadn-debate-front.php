<?php
/**
 * Debate pages:
 *   /conversation/invite/  what School B sees when invited (screen 15)
 *   /conversation/debate/  one debate: tracker, next step, fixture, safeguarding pack (screens 8, 14, 16-20)
 *   /conversation/judge/   the judge: signed-in list or a shortcut link (screens 21b, 22)
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Debate_Front {

	const FLASH = array(
		'invited'   => 'Invitation sent.',
		'released'  => 'The place is open again. You can invite another school.',
		'proposed'  => 'Sent. We have emailed the other school to review it.',
		'agreed'    => 'The fixture is agreed. We have invited the judge.',
		'suggested' => 'We have sent your suggested date back to the other school.',
		'declined'  => 'You have left this debate. The other school has been told.',
		'cancelled' => 'This debate has been cancelled and the other school has been told.',
		'saved'     => 'Saved.',
		'resent'    => 'We have sent the judge the invitation again.',
		'changed'   => 'The judge has been changed and invited.',
		'rated'     => 'Thank you for the feedback.',
		'linked'    => 'The meeting link is updated. The other school and the judge have been told.',
		'joined'    => 'You have accepted. Once your headteacher has approved the school, everything is confirmed.',
		'slow'      => 'Too many requests. Please wait a while and try again.',
		'published' => 'Your request is on Find a Debate. Other schools can now ask to debate you, and you choose.',
		'unpublished' => 'Your request is off Find a Debate.',
		'request_accepted' => 'You have accepted. It is a match, and we have told the other school.',
		'request_declined' => 'You have declined. We have told them politely.',
	);

	const CHECKLIST = array(
		'supervision' => 'Supervision: staff with students at all times',
		'travel'      => 'Travel and educational visit forms (if going away)',
		'permissions' => 'Parental permissions (photos, travel)',
		'risk'        => 'Risk assessment completed',
		'visitor'     => 'Visitor procedure for the judge',
		'audience'    => 'Audience and behaviour plan',
		'online'         => 'Online: we are using a platform our school approves',
		'online_control' => 'Online: we are hosting, so we create the meeting and control who joins (waiting room on, judge joins as a guest)',
		'online_record'  => 'Online: nobody records the debate',
	);

	/** Items that only apply to one format. Shown once the format is known. */
	const ONLINE_ONLY    = array( 'online', 'online_control', 'online_record' );
	const IN_PERSON_ONLY = array( 'travel', 'visitor' );

	/* ------------------------------------------------------------------ */
	/* Helpers                                                             */
	/* ------------------------------------------------------------------ */

	private static function esc( $v ): string {
		return esc_html( (string) $v );
	}

	private static function flash( string $key ): string {
		return isset( self::FLASH[ $key ] ) ? AIADN_Front::notice( self::FLASH[ $key ], 'info' ) : '';
	}

	private static function back( array $debate, string $flash_key = '' ): void {
		$url = AIADN_Debates::url( $debate );
		if ( '' !== $flash_key ) {
			$url = add_query_arg( 'msg', $flash_key, $url );
		}
		wp_safe_redirect( $url );
		exit;
	}

	private static function not_found(): string {
		AIADN_Front::set_title( 'Not found' );
		return '<h1>We could not find that debate</h1><p>Check the link, or <a href="' . esc_url( AIADN_Front::url( 'school' ) ) . '">go to your school page</a>.</p>';
	}

	private static function school_name( $id ): string {
		$school = $id ? AIADN_Schools::get( (int) $id ) : null;
		return $school ? $school['name'] : 'Not yet chosen';
	}

	/* ------------------------------------------------------------------ */
	/* /conversation/invite/                                               */
	/* ------------------------------------------------------------------ */

	public static function view_invite(): string {
		AIADN_Front::set_title( 'You are invited to a debate' );
		$raw   = AIADN_Front::get( 't' ) ?: AIADN_Front::post( 't' );
		$token = AIADN_Auth::find_token( $raw, 'debate_invite' );
		$debate = $token ? AIADN_Debates::get( (int) $token['ref_id'] ) : null;
		if ( ! $debate ) {
			return '<h1>This invitation is no longer valid</h1><p>It may have expired, or the other school may have sent a newer one. Ask them to send it again.</p>';
		}
		if ( 'awaiting_opponent' !== $debate['status'] ) {
			return '<h1>This invitation has already been taken</h1><p>Another school has accepted it. If that was not you, ask the inviting school to send a new invitation.</p>';
		}
		$host    = AIADN_Schools::get( (int) $debate['school_a_id'] );
		$session = AIADN_Auth::current();
		$mine    = $session && in_array( $session['role'], array( 'lead', 'teacher' ), true ) && 'approved' === $session['school']['status'] && (int) $session['school_id'] !== (int) $debate['school_a_id'];

		if ( AIADN_Front::is_post() && 'accept_invite' === AIADN_Front::post( 'aiadn_action' ) ) {
			if ( $mine && AIADN_Auth::csrf_ok( AIADN_Front::post( 'csrf' ) ) ) {
				if ( AIADN_Debates::claim_slot( (int) $debate['id'], (int) $session['school_id'], (int) $session['member_id'], 'matched' ) ) {
					AIADN_Debates::complete_match( AIADN_Debates::get( (int) $debate['id'] ) );
				}
				wp_safe_redirect( AIADN_Debates::url( $debate ) );
				exit;
			}
			AIADN_Front::redirect( 'invite', array( 't' => $raw ) );
		}

		$h  = '<h1>' . self::esc( $host['name'] ) . ' has invited you to a debate</h1>';
		$h .= '<p>Debate ID: <strong>' . self::esc( $debate['code'] ) . '</strong></p>';
		$h .= '<p>Two schools argue an AI question, with an independent judge. Your school stays responsible for its own pupils, supervision and permissions.</p>';
		if ( $mine ) {
			$h .= '<div class="aiadn__panel"><h2>Accept as ' . self::esc( $session['school']['name'] ) . '</h2>';
			$h .= '<form method="post" action="' . esc_url( AIADN_Front::url( 'invite' ) ) . '">' . AIADN_Front::csrf_field() . '<input type="hidden" name="aiadn_action" value="accept_invite"><input type="hidden" name="t" value="' . esc_attr( $raw ) . '"><button class="aiadn__button" type="submit">Accept this debate</button></form></div>';
		} else {
			$h .= '<div class="aiadn__panel"><h2>Register your school to accept</h2><p>You will need your headteacher or another senior leader to approve your school. It takes a few minutes.</p>';
			$h .= '<a class="aiadn__button" href="' . esc_url( AIADN_Front::url( 'register', array( 'inv' => $raw ) ) ) . '">Register and accept</a></div>';
			$h .= '<p class="aiadn__small">Already registered? <a href="' . esc_url( AIADN_Front::url( 'join', array( 'inv' => $raw ) ) ) . '">Sign in with your school code</a>.</p>';
		}
		return $h;
	}

	/* ------------------------------------------------------------------ */
	/* /conversation/debate/                                               */
	/* ------------------------------------------------------------------ */

	public static function view_debate(): string {
		AIADN_Front::set_title( 'Your debate' );
		$session = AIADN_Auth::current();
		if ( ! $session || ! in_array( $session['role'], array( 'lead', 'teacher', 'slt' ), true ) ) {
			AIADN_Front::redirect( 'join', array( 'msg' => 'signin' ) );
		}
		$code   = AIADN_Util::normalise_debate_code( AIADN_Front::get( 'd' ) ?: AIADN_Front::post( 'd' ) );
		$debate = $code ? AIADN_Debates::get_by_code( $code ) : null;
		if ( ! $debate || ! AIADN_Debates::involves( $debate, (int) $session['school_id'] ) ) {
			return self::not_found();
		}

		if ( ! empty( $debate['theme'] ) ) {
			AIADN_Front::set_strand( $debate['theme'] );
		}
		$state = array( 'errors' => array(), 'values' => array(), 'link' => '' );
		if ( AIADN_Front::is_post() ) {
			if ( ! AIADN_Auth::csrf_ok( AIADN_Front::post( 'csrf' ) ) || ! in_array( $session['role'], array( 'lead', 'teacher' ), true ) ) {
				self::back( $debate );
			}
			$state  = self::handle_action( $debate, $session );
			$debate = AIADN_Debates::get( (int) $debate['id'] );
		}
		return self::render_debate( $debate, $session, $state );
	}

	/**
	 * Run one action. Redirects on success; returns errors/values/link when the page should re-render.
	 *
	 * @return array{errors:array,values:array,link:string}
	 */
	private static function handle_action( array $debate, array $session ): array {
		$school_id = (int) $session['school_id'];
		$side      = AIADN_Debates::side( $debate, $school_id );
		$status    = $debate['status'];
		$state     = array( 'errors' => array(), 'values' => array(), 'link' => '' );
		$action    = AIADN_Front::post( 'aiadn_action' );

		switch ( $action ) {
			case 'send_invite':
				if ( 'a' === $side && 'awaiting_opponent' === $status ) {
					$email = AIADN_Util::normalise_email( AIADN_Front::post( 'invite_email' ) );
					if ( ! is_email( $email ) ) {
						$state['errors']['invite_email'] = 'Enter the other teacher\'s email address.';
						$state['values']                 = array( 'invite_name' => AIADN_Front::post( 'invite_name' ), 'invite_email' => $email );
						return $state;
					}
					if ( ! AIADN_Util::allow( 'invite|' . $school_id, 10, HOUR_IN_SECONDS ) ) {
						self::back( $debate, 'slow' );
					}
					AIADN_Debates::send_invite( $debate, AIADN_Front::post( 'invite_name' ), $email );
					self::back( $debate, 'invited' );
				}
				break;

			case 'new_link':
				if ( 'a' === $side && 'awaiting_opponent' === $status ) {
					$state['link'] = AIADN_Debates::new_invite_link( $debate );
					return $state;
				}
				break;

			case 'publish_request':
				if ( 'a' === $side && 'awaiting_opponent' === $status ) {
					$f   = array(
						'age_group' => AIADN_Front::post( 'age_group' ),
						'theme'     => AIADN_Front::post( 'theme' ),
						'dates'     => AIADN_Front::post( 'req_dates' ),
						'format'    => AIADN_Front::post( 'req_format' ),
						'host'      => AIADN_Front::post( 'req_host' ),
						'travel'    => AIADN_Front::post( 'req_travel' ),
					);
					$err = AIADN_Find::publish( $debate, $f );
					if ( '' !== $err ) {
						$state['errors']['find'] = $err;
						$state['values']         = array_merge( $f, array( 'req_dates' => $f['dates'], 'req_format' => $f['format'], 'req_host' => $f['host'], 'req_travel' => $f['travel'] ) );
						return $state;
					}
					self::back( $debate, 'published' );
				}
				break;

			case 'unpublish_request':
				if ( 'a' === $side && 'awaiting_opponent' === $status ) {
					AIADN_Find::unpublish( $debate );
					self::back( $debate, 'unpublished' );
				}
				break;

			case 'decide_request':
				if ( 'a' === $side && 'awaiting_opponent' === $status ) {
					$req = AIADN_Find::get( (int) AIADN_Front::post( 'request_id' ) );
					if ( $req && (int) $req['debate_id'] === (int) $debate['id'] ) {
						$accept = 'accept' === AIADN_Front::post( 'decision' );
						if ( AIADN_Find::decide( $req, $debate, $accept ) ) {
							self::back( $debate, $accept ? 'request_accepted' : 'request_declined' );
						}
					}
				}
				break;

			case 'release':
				if ( 'a' === $side && 'awaiting_b_approval' === $status ) {
					AIADN_Debates::reopen( $debate, $school_id, 'released' );
					self::back( $debate, 'released' );
				}
				break;

			case 'propose':
				if ( 'a' === $side && 'matched' === $status ) {
					list( $errors, $values, $fx, $judge ) = self::parse_fixture();
					if ( $errors ) {
						$state['errors'] = $errors;
						$state['values'] = $values;
						return $state;
					}
					AIADN_Debates::propose( $debate, $fx, $judge );
					self::back( $debate, 'proposed' );
				}
				break;

			case 'accept_fixture':
				if ( 'proposed' === $status && (int) $debate['proposed_by'] !== $school_id ) {
					AIADN_Debates::agree( $debate, $school_id );
					self::back( $debate, 'agreed' );
				}
				break;

			case 'suggest_date':
				if ( 'proposed' === $status && (int) $debate['proposed_by'] !== $school_id ) {
					$utc = AIADN_Util::local_to_utc( AIADN_Front::post( 'starts_at' ) );
					if ( ! $utc || strtotime( $utc . ' UTC' ) < time() + HOUR_IN_SECONDS ) {
						$state['errors']['suggest'] = 'Choose a date and time in the future.';
						return $state;
					}
					AIADN_Debates::suggest_date( $debate, $school_id, $utc );
					self::back( $debate, 'suggested' );
				}
				break;

			case 'decline':
				if ( 'b' === $side && in_array( $status, array( 'matched', 'proposed' ), true ) ) {
					AIADN_Debates::notify_other( $debate, $school_id, 'The other school has left your debate', "The other school can no longer take part, so the place is open again. You can invite another school from the debate page:\n\n" . AIADN_Debates::url( $debate ) );
					AIADN_Debates::reopen( $debate, $school_id, 'declined' );
					wp_safe_redirect( add_query_arg( 'msg', 'declined', AIADN_Front::url( 'school' ) ) );
					exit;
				}
				break;

			case 'cancel':
				if ( in_array( $status, array( 'matched', 'proposed', 'agreed', 'ready' ), true ) ) {
					AIADN_Debates::cancel( $debate, $school_id );
					self::back( $debate, 'cancelled' );
				}
				break;

			case 'set_link':
				if ( 'a' === $side && 'online' === $debate['format'] && in_array( $status, array( 'proposed', 'agreed', 'ready' ), true ) ) {
					list( $url, $link_error ) = self::link_from_request( (string) $debate['starts_at'] );
					if ( '' !== $link_error || '' === $url ) {
						$state['errors']['set_link'] = '' !== $link_error ? $link_error : 'Add the new link, or upload the calendar file from your meeting.';
						return $state;
					}
					if ( $url !== $debate['meeting_url'] ) {
						AIADN_Debates::set_meeting_link( $debate, $url, $school_id );
					}
					self::back( $debate, 'linked' );
				}
				break;

			case 'rate':
				if ( 'completed' === $status ) {
					AIADN_Results::save_rating( (int) $debate['id'], $school_id, 'teacher', (int) AIADN_Front::post( 'rating' ) );
					self::back( $debate, 'rated' );
				}
				break;

			case 'checklist':
				if ( in_array( $status, array( 'matched', 'proposed', 'agreed', 'ready' ), true ) ) {
					$ticks = array_diff_key( AIADN_Debates::checklist( $debate, $side ), self::CHECKLIST ); // Keep the prep ticks.
					foreach ( array_keys( self::CHECKLIST ) as $key ) {
						$ticks[ $key ] = '' !== AIADN_Front::post( 'tick_' . $key );
					}
					AIADN_Debates::save_checklist( $debate, $side, $ticks );
					self::back( $debate, 'saved' );
				}
				break;

			case 'prep_checklist':
				if ( in_array( $status, array( 'agreed', 'ready' ), true ) && '' !== $side && in_array( $session['role'], array( 'lead', 'teacher' ), true ) ) {
					$ticks = array_diff_key( AIADN_Debates::checklist( $debate, $side ), AIADN_Format::CHECKLIST ); // Keep the safeguarding ticks.
					foreach ( array_keys( AIADN_Format::checklist_for( (string) $debate['format'] ) ) as $key ) {
						$ticks[ $key ] = '' !== AIADN_Front::post( 'tick_' . $key );
					}
					AIADN_Debates::save_checklist( $debate, $side, $ticks );
					AIADN_Front::redirect( 'prep', array( 'd' => $debate['code'], 'msg' => 'saved' ) );
				}
				break;

			case 'resend_judge':
				$judge = (int) $debate['judge_id'] ? AIADN_Debates::get_judge( (int) $debate['judge_id'] ) : null;
				if ( in_array( $status, array( 'agreed', 'ready' ), true ) && $judge && 'invited' === $judge['status'] ) {
					if ( ! AIADN_Util::allow( 'rejudge|' . $debate['id'], 3, HOUR_IN_SECONDS ) ) {
						self::back( $debate, 'slow' );
					}
					AIADN_Debates::invite_judge( $debate, $judge );
					self::back( $debate, 'resent' );
				}
				break;

			case 'change_judge':
				if ( in_array( $status, array( 'agreed', 'ready' ), true ) ) {
					list( $errors, $values, $judge ) = self::parse_judge( 'cj_' );
					if ( $errors ) {
						$state['errors'] = $errors;
						$state['values'] = $values;
						return $state;
					}
					AIADN_Debates::change_judge( $debate, $judge, $school_id );
					self::back( $debate, 'changed' );
				}
				break;
		}
		self::back( $debate );
		return $state;
	}

	/**
	 * The meeting link, from an uploaded calendar file (preferred) or the typed link.
	 * Returns array( link, error ). link is '' with no error when neither was given.
	 * The file is read and thrown away: only the join link is kept.
	 *
	 * @param string $expected_utc The debate's start (UTC), to check it is the right meeting; '' to skip.
	 * @return array{0:string,1:string}
	 */
	private static function link_from_request( string $expected_utc ): array {
		$file = isset( $_FILES['meeting_ics'] ) && is_array( $_FILES['meeting_ics'] ) ? $_FILES['meeting_ics'] : null; // phpcs:ignore WordPress.Security.NonceVerification
		if ( $file && UPLOAD_ERR_NO_FILE !== (int) ( $file['error'] ?? UPLOAD_ERR_NO_FILE ) ) {
			if ( UPLOAD_ERR_OK !== (int) $file['error'] || ! is_uploaded_file( (string) $file['tmp_name'] ) ) {
				return array( '', 'The file could not be uploaded. Try again, or paste the link instead.' );
			}
			if ( (int) $file['size'] > AIADN_Meeting_Ics::MAX_BYTES ) {
				return array( '', 'That file is too big to be a calendar invite. Upload the .ics file from your meeting, or paste the link instead.' );
			}
			$read = AIADN_Meeting_Ics::extract( (string) file_get_contents( (string) $file['tmp_name'] ) );
			if ( '' !== $read['error'] ) {
				return array( '', $read['error'] );
			}
			if ( '' !== $expected_utc && null !== $read['start'] && abs( $read['start'] - strtotime( $expected_utc . ' UTC' ) ) > AIADN_Meeting_Ics::TOLERANCE ) {
				return array( '', 'That calendar file is for ' . AIADN_Util::show( gmdate( 'Y-m-d H:i:s', $read['start'] ) ) . ', but this debate is on ' . AIADN_Util::show( $expected_utc ) . '. Check it is the right meeting.' );
			}
			return array( $read['url'], '' );
		}
		$typed = trim( AIADN_Front::post( 'meeting_url' ) );
		if ( '' === $typed ) {
			return array( '', '' );
		}
		$url = AIADN_Debates::clean_meeting_url( $typed );
		return '' === $url ? array( '', 'The link must start with https:// and be a web address, such as a Teams, Meet or Zoom link.' ) : array( $url, '' );
	}

	/**
	 * The panel for the day itself. Online: the join button, so nobody hunts through email for the link.
	 * In person: when, where and who is judging, with the same weight, so the day looks the same either way.
	 */
	public static function join_panel( array $debate, string $viewer = 'school', string $calendar_url = '', string $paper_url = '', string $prep_url = '' ): string {
		if ( ! AIADN_Debates::is_today( $debate ) ) {
			return '';
		}
		$a = AIADN_Schools::get( (int) $debate['school_a_id'] );
		$b = AIADN_Schools::get( (int) $debate['school_b_id'] );
		if ( 'in_person' === $debate['format'] ) {
			$judge = (int) $debate['judge_id'] ? AIADN_Debates::get_judge( (int) $debate['judge_id'] ) : null;
			$dir   = AIADN_Debates::directions_url( $debate );
			$h     = '<div class="aiadn__panel aiadn__panel--ink aiadn__join aiadn__today"><h2>Today</h2><p><strong>' . esc_html( ( $a['name'] ?? '' ) . ' v ' . ( $b['name'] ?? '' ) ) . '</strong><br>' . esc_html( AIADN_Util::show( (string) $debate['starts_at'], 'D j M, H:i' ) ) . '</p><dl class="aiadn__details"><dt>Where</dt><dd>' . esc_html( AIADN_Debates::place( $debate ) ) . ( $dir ? ' <a href="' . esc_url( $dir ) . '" target="_blank" rel="noopener noreferrer">Get directions</a>' : '' ) . '</dd>';
			if ( $judge ) {
				$h .= '<dt>Judge</dt><dd>' . esc_html( $judge['name'] . ( $judge['organisation'] ? ', ' . $judge['organisation'] : '' ) ) . '</dd>';
			}
			$note = 'judge' === $viewer
				? 'Please arrive at reception and follow the host school\'s visitor procedure.'
				: 'The judge is a visitor: please welcome them at reception and follow your school\'s visitor procedure.';
			$print = ( 'school' === $viewer && '' !== $paper_url ) ? '<p class="aiadn__small">Have a printed scorecard ready for the judge, if they would rather score on paper.</p><p><a class="aiadn__button aiadn__button--quiet" href="' . esc_url( $paper_url ) . '" target="_blank" rel="noopener">Print a scorecard for the judge</a></p>' : '';
			return $h . '</dl><p class="aiadn__small">' . esc_html( $note ) . '</p>' . self::prep_button( $prep_url ) . $print . self::calendar_button( $calendar_url ) . '</div>';
		}
		if ( ! AIADN_Debates::is_live( $debate ) ) {
			return '';
		}
		return '<div class="aiadn__panel aiadn__panel--ink aiadn__join"><h2>Join the meeting</h2><p><strong>' . esc_html( ( $a['name'] ?? '' ) . ' v ' . ( $b['name'] ?? '' ) ) . '</strong><br>' . esc_html( AIADN_Util::show( (string) $debate['starts_at'], 'D j M, H:i' ) ) . '. Hosted by ' . esc_html( $a['name'] ?? '' ) . ', who admit everyone from the waiting room.</p><p><a class="aiadn__button" href="' . esc_url( $debate['meeting_url'] ) . '" target="_blank" rel="noopener noreferrer">Join the meeting</a></p>' . self::prep_button( $prep_url ) . self::calendar_button( $calendar_url ) . '</div>';
	}

	/** The link to the running order for the day. */
	public static function prep_button( string $url ): string {
		return '' === $url ? '' : '<p><a class="aiadn__button aiadn__button--quiet" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">Running order for the day</a></p>';
	}

	/** The "Add to my calendar" download, for an agreed fixture. */
	public static function calendar_button( string $url ): string {
		return '' === $url ? '' : '<p><a class="aiadn__button aiadn__button--quiet" href="' . esc_url( $url ) . '">Add to my calendar</a></p>';
	}

	/**
	 * /conversation/calendar/: the debate's calendar file as a download.
	 * For a teacher or headteacher at either school, or for the judge (by shortcut link or after signing in).
	 * Only for a fixture that has been agreed and is still to come or on now.
	 */
	public static function view_calendar(): string {
		AIADN_Front::set_title( 'Calendar' );
		$none    = '<h1>We could not find that</h1><p>The calendar file is only available for an agreed debate, to the schools and the judge.</p>';
		$raw     = AIADN_Front::get( 't' );
		$session = AIADN_Auth::current();
		$debate  = null;

		if ( '' !== $raw ) {
			$tok   = preg_match( '/^[a-f0-9]{40}$/', $raw ) ? AIADN_Auth::find_token( $raw, 'judge' ) : null;
			$judge = $tok ? AIADN_Debates::get_judge( (int) $tok['ref_id'] ) : null;
			$debate = ( $judge && 'accepted' === $judge['status'] ) ? AIADN_Debates::get( (int) $judge['debate_id'] ) : null;
		} else {
			$code   = AIADN_Util::normalise_debate_code( AIADN_Front::get( 'd' ) );
			$debate = $code ? AIADN_Debates::get_by_code( $code ) : null;
			$ok     = false;
			if ( $debate && $session ) {
				if ( in_array( $session['role'], array( 'lead', 'teacher', 'slt' ), true ) ) {
					$ok = AIADN_Debates::involves( $debate, (int) $session['school_id'] );
				} elseif ( 'judge' === $session['role'] ) {
					foreach ( AIADN_Debates::judges_for_school_email( (int) $session['school_id'], (string) $session['member']['email'] ) as $row ) {
						$ok = $ok || ( (int) $row['debate_id'] === (int) $debate['id'] && 'accepted' === $row['status'] );
					}
				}
			}
			if ( ! $ok ) {
				$debate = null;
			}
		}
		if ( ! $debate || ! in_array( $debate['status'], array( 'agreed', 'ready' ), true ) || ! $debate['starts_at'] ) {
			return $none;
		}
		AIADN_Calendar::download( $debate );
		return ''; // download() exits.
	}

	/* ---- form parsing ------------------------------------------------ */

	/** @return array{0:array,1:array,2:array} errors, values, judge */
	private static function parse_judge( string $prefix ): array {
		$values = array(
			'name'         => AIADN_Front::post( $prefix . 'name' ),
			'email'        => AIADN_Util::normalise_email( AIADN_Front::post( $prefix . 'email' ) ),
			'organisation' => AIADN_Front::post( $prefix . 'organisation' ),
			'judge_type'   => AIADN_Front::post( $prefix . 'judge_type' ),
			'ack'          => '' !== AIADN_Front::post( $prefix . 'ack' ),
		);
		$errors = array();
		if ( strlen( $values['name'] ) < 2 ) {
			$errors[ $prefix . 'name' ] = "Enter the judge's name.";
		}
		if ( ! is_email( $values['email'] ) ) {
			$errors[ $prefix . 'email' ] = "Enter the judge's email address.";
		}
		if ( ! isset( AIADN_Motions::JUDGE_TYPES[ $values['judge_type'] ] ) ) {
			$errors[ $prefix . 'judge_type' ] = 'Choose a judge type.';
		}
		if ( ! $values['ack'] ) {
			$errors[ $prefix . 'ack' ] = "Please confirm your school's safeguarding and visitor policy applies.";
		}
		return array( $errors, $values, array( 'name' => $values['name'], 'email' => $values['email'], 'organisation' => $values['organisation'], 'judge_type' => $values['judge_type'] ) );
	}

	/** @return array{0:array,1:array,2:array,3:array} errors, values, fixture, judge */
	private static function parse_fixture(): array {
		$values = array(
			'starts_at'  => AIADN_Front::post( 'starts_at' ),
			'age_group'  => AIADN_Front::post( 'age_group' ),
			'theme'      => AIADN_Front::post( 'theme' ),
			'motion_key' => AIADN_Front::post( 'motion_key' ),
			'a_side'     => AIADN_Front::post( 'a_side' ),
			'format'     => AIADN_Front::post( 'format' ),
			'venue'      => AIADN_Front::post( 'venue' ),
			'venue_address' => AIADN_Front::post( 'venue_address' ),
			'meeting_url' => AIADN_Front::post( 'meeting_url' ),
			'calendar'    => '' !== AIADN_Front::post( 'calendar' ),
		);
		$errors = array();
		$utc    = AIADN_Util::local_to_utc( $values['starts_at'] );
		if ( ! $utc || strtotime( $utc . ' UTC' ) < time() + HOUR_IN_SECONDS ) {
			$errors['starts_at'] = 'Choose a date and time in the future.';
		} elseif ( strtotime( $utc . ' UTC' ) > time() + YEAR_IN_SECONDS ) {
			$errors['starts_at'] = 'Choose a date within the next year.';
		}
		if ( ! isset( AIADN_Motions::AGES[ $values['age_group'] ] ) ) {
			$errors['age_group'] = 'Choose an age group.';
		}
		if ( ! isset( AIADN_Motions::THEMES[ $values['theme'] ] ) ) {
			$errors['theme'] = 'Choose a theme.';
		}
		$motion = ( ! isset( $errors['age_group'] ) && ! isset( $errors['theme'] ) ) ? AIADN_Motions::find( $values['motion_key'], $values['theme'], $values['age_group'] ) : null;
		if ( ! $motion ) {
			$errors['motion_key'] = 'Choose a motion for that theme and age group.';
		}
		if ( ! in_array( $values['a_side'], array( 'for', 'against' ), true ) ) {
			$errors['a_side'] = 'Choose which side you argue.';
		}
		if ( ! isset( AIADN_Motions::FORMATS[ $values['format'] ] ) ) {
			$errors['format'] = 'Choose online or in person.';
		} elseif ( 'in_person' === $values['format'] ) {
			if ( strlen( $values['venue'] ) < 3 ) {
				$errors['venue'] = 'Say where it will take place.';
			}
			// The address goes into the calendar entry and the judge's directions, so a visitor can find the school.
			if ( strlen( $values['venue_address'] ) < 10 ) {
				$errors['venue_address'] = 'Add the full address with the postcode, so the judge can find you and the calendar entry has it.';
			}
		} elseif ( 'online' === $values['format'] ) {
			// The host school (School A) creates the meeting, so a link is needed to agree an online debate.
			list( $meeting, $link_error ) = self::link_from_request( (string) $utc );
			if ( '' !== $link_error ) {
				$errors['meeting_url'] = $link_error;
			} elseif ( '' === $meeting ) {
				$errors['meeting_url'] = 'Add the meeting link, or upload the calendar file from your meeting. As the host, you create the meeting.';
			}
		}
		list( $judge_errors, $judge_values, $judge ) = self::parse_judge( 'j_' );
		$errors = array_merge( $errors, $judge_errors );
		$values = array_merge( $values, array( 'j_name' => $judge_values['name'], 'j_email' => $judge_values['email'], 'j_organisation' => $judge_values['organisation'], 'j_judge_type' => $judge_values['judge_type'], 'j_ack' => $judge_values['ack'] ) );

		$fx = array(
			'age_group'   => $values['age_group'],
			'theme'       => $values['theme'],
			'motion_key'  => $values['motion_key'],
			'motion_text' => $motion ? $motion['text'] : '',
			'a_side'      => $values['a_side'],
			'format'      => $values['format'],
			// Only the field that belongs to the chosen format is kept.
			'venue'       => 'in_person' === $values['format'] ? $values['venue'] : '',
			'venue_address' => 'in_person' === $values['format'] ? $values['venue_address'] : '',
			'meeting_url' => ( 'online' === $values['format'] && isset( $meeting ) ) ? $meeting : '',
			'send_calendar' => $values['calendar'] ? 1 : 0,
			'starts_at'   => $utc,
		);
		return array( $errors, $values, $fx, $judge );
	}

	/* ---- rendering --------------------------------------------------- */

	private static function err( array $errors, string $key ): string {
		return isset( $errors[ $key ] ) ? '<p class="aiadn__error" role="alert">' . esc_html( $errors[ $key ] ) . '</p>' : '';
	}

	public static function form_open( array $debate, string $action ): string {
		return '<form method="post" action="' . esc_url( AIADN_Debates::url( $debate ) ) . '" class="aiadn__form" enctype="multipart/form-data">' . AIADN_Front::csrf_field() . '<input type="hidden" name="d" value="' . esc_attr( $debate['code'] ) . '"><input type="hidden" name="aiadn_action" value="' . esc_attr( $action ) . '">';
	}

	/** Where the debate is, with an online meeting link as a real link (opens in a new tab, no referrer). */
	public static function where_html( array $debate ): string {
		if ( 'online' === $debate['format'] && $debate['meeting_url'] ) {
			return 'Online, hosted by ' . esc_html( self::school_name( $debate['school_a_id'] ) ) . ': <a href="' . esc_url( $debate['meeting_url'] ) . '" target="_blank" rel="noopener noreferrer">Meeting link</a>';
		}
		return esc_html( AIADN_Debates::where( $debate ) );
	}

	private static function render_tracker( array $debate, int $school_id = 0 ): string {
		$marks = array( 'done' => array( '&#10003;', 'Done' ), 'now' => array( '&#9679;', 'Now' ), 'todo' => array( '&#9675;', 'To do' ), 'issue' => array( '!', 'Issue' ) );
		$h     = '<ol class="aiadn__tracker">';
		foreach ( AIADN_Debates::tracker( $debate, $school_id ) as $stage ) {
			$m  = $marks[ $stage['state'] ];
			$h .= '<li class="aiadn__tracker--' . esc_attr( $stage['state'] ) . '"><span class="aiadn__mark" aria-hidden="true">' . $m[0] . '</span><span class="aiadn__sr">' . $m[1] . ': </span>' . esc_html( $stage['label'] ) . ( '' !== $stage['detail'] ? ' <span class="aiadn__meta">' . esc_html( $stage['detail'] ) . '</span>' : '' ) . '</li>';
		}
		return $h . '</ol>';
	}

	private static function render_details( array $debate, int $school_id ): string {
		$side = AIADN_Debates::side( $debate, $school_id );
		$rows = array( 'Against' => self::school_name( AIADN_Debates::other_school_id( $debate, $school_id ) ) );
		if ( $debate['starts_at'] ) {
			$rows['When'] = AIADN_Util::show( $debate['starts_at'] );
			$rows['Where'] = AIADN_Debates::where( $debate );
			$rows['Theme'] = AIADN_Motions::THEMES[ $debate['theme'] ] ?? '';
			$rows['Motion'] = '"' . $debate['motion_text'] . '"';
			$a_for = 'for' === $debate['a_side'];
			$rows['You argue'] = strtoupper( ( 'a' === $side ? $a_for : ! $a_for ) ? 'for' : 'against' );
			$judge = (int) $debate['judge_id'] ? AIADN_Debates::get_judge( (int) $debate['judge_id'] ) : null;
			if ( $judge ) {
				$rows['Judge'] = $judge['name'] . ( $judge['organisation'] ? ', ' . $judge['organisation'] : '' );
			}
		}
		$h = '<dl class="aiadn__details">';
		foreach ( $rows as $label => $value ) {
			$h .= '<dt>' . esc_html( $label ) . '</dt><dd>' . ( 'Where' === $label ? self::where_html( $debate ) : esc_html( $value ) ) . '</dd>';
		}
		return $h . '</dl>';
	}

	private static function render_debate( array $debate, array $session, array $state ): string {
		$school_id = (int) $session['school_id'];
		$side      = AIADN_Debates::side( $debate, $school_id );
		$status    = $debate['status'];
		$can_act   = in_array( $session['role'], array( 'lead', 'teacher' ), true );
		$errors    = $state['errors'];
		$v         = $state['values'];

		$h  = '<p class="aiadn__small"><a href="' . esc_url( AIADN_Front::url( 'school' ) ) . '">&larr; Your school</a></p>';
		$h .= '<h1>Debate ' . self::esc( $debate['code'] ) . '</h1>';
		$h .= self::flash( AIADN_Front::get( 'msg' ) );
		$h .= self::render_tracker( $debate, $school_id );

		if ( 'expired' === $status ) {
			return $h . '<div class="aiadn__panel"><h2>Closed</h2><p>Nothing happened for 14 days, so this debate closed. You can start a new debate from <a href="' . esc_url( AIADN_Front::url( 'school' ) ) . '">your school page</a>, and invite the same school or a different one.</p></div>' . self::render_details( $debate, $school_id );
		}
		if ( 'cancelled' === $status ) {
			return $h . '<div class="aiadn__panel"><h2>Cancelled</h2><p>This debate was cancelled.</p></div>' . ( 'cancelled' === $status ? self::render_details( $debate, $school_id ) : '' );
		}

		$h .= self::join_panel( $debate, 'school', AIADN_Front::url( 'calendar', array( 'd' => $debate['code'] ) ), AIADN_Front::url( 'paper', array( 'd' => $debate['code'] ) ), in_array( $status, array( 'agreed', 'ready' ), true ) ? AIADN_Front::url( 'prep', array( 'd' => $debate['code'] ) ) : '' );

		// ---- a result is in (or the debate is void) ----
		if ( in_array( $status, array( 'completed', 'void' ), true ) ) {
			$h .= AIADN_Result_Front::render_result_panel( $debate, $session );
			$h .= '<div class="aiadn__panel"><h2>The fixture</h2>' . self::render_details( $debate, $school_id ) . '</div>';
			$h .= AIADN_Result_Front::render_result_actions( $debate, $session, self::form_open( $debate, 'rate' ), $can_act );
			return $h;
		}

		// ---- the one next step ----
		$h .= '<div class="aiadn__panel"><h2>Next step</h2>';
		if ( 'awaiting_opponent' === $status ) {
			if ( 'a' === $side && $can_act ) {
				if ( $state['link'] ) {
					$h .= '<p><strong>Share this link.</strong> It replaces any earlier link.</p><p class="aiadn__link-box"><input type="text" readonly value="' . esc_attr( $state['link'] ) . '" onclick="this.select()" aria-label="Invitation link"></p>';
				}
				$h .= '<h3>Option 1: email a teacher</h3>' . self::form_open( $debate, 'send_invite' );
				$h .= '<label for="inv-name">Their name <span class="aiadn__opt">(optional)</span></label><input id="inv-name" name="invite_name" type="text" value="' . esc_attr( $v['invite_name'] ?? '' ) . '">';
				$h .= '<label for="inv-email">Their email</label><input id="inv-email" name="invite_email" type="email" value="' . esc_attr( $v['invite_email'] ?? '' ) . '" required>' . self::err( $errors, 'invite_email' );
				$h .= '<button class="aiadn__button" type="submit">Send invitation</button></form>';
				$h .= '<h3>Option 2: share a link</h3><p class="aiadn__small">Anyone with the link can start to accept, but their school still needs headteacher approval.</p>' . self::form_open( $debate, 'new_link' ) . '<button class="aiadn__button aiadn__button--quiet" type="submit">Get a link to share</button></form>';
				$h .= self::render_find_panel( $debate, $session, $errors, $v );
			} else {
				$h .= '<p>Waiting for the other school to be invited.</p>';
			}
		} elseif ( 'awaiting_b_approval' === $status ) {
			$h .= '<p><strong>' . self::esc( self::school_name( $debate['school_b_id'] ) ) . '</strong> has accepted. The match is confirmed as soon as their headteacher approves the school.</p>';
			if ( 'a' === $side && $can_act ) {
				$h .= '<p class="aiadn__small">Taking too long?</p>' . self::form_open( $debate, 'release' ) . '<button class="aiadn__button aiadn__button--quiet" type="submit">Invite someone else</button></form>';
			}
		} elseif ( 'matched' === $status ) {
			if ( 'a' === $side && $can_act ) {
				$h .= self::render_propose_form( $debate, $session, $errors, $v );
			} else {
				$h .= '<p>It is a match. Waiting for <strong>' . self::esc( self::school_name( $debate['school_a_id'] ) ) . '</strong> to propose the date, theme, motion and judge.</p>';
			}
		} elseif ( 'proposed' === $status ) {
			if ( (int) $debate['proposed_by'] !== $school_id && $can_act ) {
				$h .= '<p>Please review the fixture below.</p>';
				$h .= '<div class="aiadn__actions">' . self::form_open( $debate, 'accept_fixture' ) . '<button class="aiadn__button" type="submit">Accept</button></form></div>';
				$h .= '<h3>Suggest another date</h3>' . self::form_open( $debate, 'suggest_date' ) . '<label for="sd">New date and time</label><input id="sd" name="starts_at" type="datetime-local" min="' . esc_attr( wp_date( 'Y-m-d\TH:i', time() + HOUR_IN_SECONDS ) ) . '" value="' . esc_attr( $debate['starts_at'] ? AIADN_Util::to_input( $debate['starts_at'] ) : '' ) . '" required>' . self::err( $errors, 'suggest' ) . '<button class="aiadn__button aiadn__button--quiet" type="submit">Suggest this date instead</button></form>';
				if ( 'b' === $side ) {
					$h .= '<h3>Not for you?</h3>' . self::form_open( $debate, 'decline' ) . '<button class="aiadn__button aiadn__button--quiet" type="submit">Decline this debate</button></form>';
				}
			} else {
				$h .= '<p>Waiting for <strong>' . self::esc( self::school_name( AIADN_Debates::other_school_id( $debate, $school_id ) ) ) . '</strong> to review the fixture.</p>';
			}
		} else { // agreed / ready.
			$judge = (int) $debate['judge_id'] ? AIADN_Debates::get_judge( (int) $debate['judge_id'] ) : null;
			if ( 'ready' === $status ) {
				$h .= '<p><strong>Everything is set.</strong> The judge has accepted. After the debate the judge submits the scores, and the result appears here.</p>';
			} elseif ( $judge && 'declined' === $judge['status'] ) {
				$h .= '<p><strong>' . self::esc( $judge['name'] ) . " can't make it.</strong> Please choose another judge.</p>";
			} else {
				$h .= '<p>The fixture is agreed. Waiting for <strong>' . self::esc( $judge ? $judge['name'] : 'the judge' ) . '</strong> to accept.</p>';
				if ( $judge && 'invited' === $judge['status'] && $can_act ) {
					$h .= self::form_open( $debate, 'resend_judge' ) . '<button class="aiadn__button aiadn__button--quiet" type="submit">Send the invitation again</button></form>';
				}
			}
			if ( $can_act && ( ! $judge || 'accepted' !== $judge['status'] || 'ready' === $status ) ) {
				$open = $judge && 'declined' === $judge['status'] || array_intersect_key( $errors, array_flip( array( 'cj_name', 'cj_email', 'cj_judge_type', 'cj_ack' ) ) );
				$h   .= '<details class="aiadn__details-box"' . ( $open ? ' open' : '' ) . '><summary>Change the judge</summary>' . self::render_judge_fields( $debate, 'cj_', $errors, $v, 'change_judge', 'Invite a different judge' ) . '</details>';
			}
		}
		$h .= '</div>';

		// ---- the host can change an online link ----
		if ( 'online' === $debate['format'] && 'a' === $side && $can_act && in_array( $status, array( 'proposed', 'agreed', 'ready' ), true ) ) {
			$h .= '<div class="aiadn__panel"><details class="aiadn__details-box"' . ( isset( $errors['set_link'] ) ? ' open' : '' ) . '><summary>Change the meeting link</summary>' . self::form_open( $debate, 'set_link' ) . '<label for="sl-ics">Upload the new calendar file (.ics)</label><input id="sl-ics" name="meeting_ics" type="file" accept=".ics,text/calendar"><label for="sl-url">Or paste the new link</label><input id="sl-url" name="meeting_url" type="url" inputmode="url" value="' . esc_attr( $debate['meeting_url'] ) . '">' . self::err( $errors, 'set_link' ) . '<button class="aiadn__button aiadn__button--quiet" type="submit">Update the link</button></form><p class="aiadn__small">The other school and the judge are emailed the new link.</p></details></div>';
		}

		// ---- the fixture ----
		if ( $debate['starts_at'] ) {
			$cal = in_array( $status, array( 'agreed', 'ready' ), true ) ? self::calendar_button( AIADN_Front::url( 'calendar', array( 'd' => $debate['code'] ) ) ) : '';
			$h  .= '<div class="aiadn__panel"><h2>The fixture</h2>' . self::render_details( $debate, $school_id ) . $cal . '</div>';
			if ( in_array( $status, array( 'agreed', 'ready' ), true ) ) {
				$h .= '<div class="aiadn__panel"><h2>Prepare for the day</h2><p>We recommend ' . (int) AIADN_Format::TOTAL_MINUTES . ' minutes, and you can adapt it to suit your school. The prep pack has a checklist, a suggested running order, the survey questions to ask your students, how to split the research, and who does what.</p><p><a class="aiadn__button" href="' . esc_url( AIADN_Front::url( 'prep', array( 'd' => $debate['code'] ) ) ) . '">Open the prep pack</a></p></div>';
				// The two ways a judge can score, and what the school should do about paper (brief, section 9).
				$h .= '<div class="aiadn__panel"><h2>Scorecard for the judge</h2><p>The judge can score on their phone or on paper. <strong>If they would rather use paper, please print a scorecard in advance</strong> and give it to them on the day. After the debate they scan the code on it and enter the final scores.</p><p><a class="aiadn__button aiadn__button--quiet" href="' . esc_url( AIADN_Front::url( 'paper', array( 'd' => $debate['code'] ) ) ) . '" target="_blank" rel="noopener">Print a scorecard for the judge</a></p></div>';
			}
		} elseif ( $debate['school_b_id'] ) {
			$h .= '<div class="aiadn__panel"><h2>The fixture</h2>' . self::render_details( $debate, $school_id ) . '</div>';
		}

		// ---- safeguarding pack ----
		if ( in_array( $status, array( 'matched', 'proposed', 'agreed', 'ready' ), true ) ) {
			$h .= self::render_pack( $debate, $side, $can_act );
		}

		if ( $can_act && in_array( $status, array( 'matched', 'proposed', 'agreed', 'ready' ), true ) ) {
			$h .= '<div class="aiadn__panel"><h2>Cancel this debate</h2><p class="aiadn__small">The other school and the judge will be told.</p>' . self::form_open( $debate, 'cancel' ) . '<button class="aiadn__button aiadn__button--quiet" type="submit" onclick="return confirm(\'Cancel this debate?\');">Cancel debate</button></form></div>';
		}
		return $h;
	}

	/** Option 3: put the debate on Find a Debate, and answer the schools that ask. */
	private static function render_find_panel( array $debate, array $session, array $errors, array $v ): string {
		$h = '<h3>Option 3: Find a Debate</h3>';
		if ( (int) $debate['open_request'] ) {
			$h .= '<p>Your request is on Find a Debate: <strong>' . esc_html( AIADN_Motions::AGES[ $debate['age_group'] ] ?? '' ) . '</strong>, <strong>' . esc_html( AIADN_Motions::THEMES[ $debate['theme'] ] ?? '' ) . '</strong>. ' . esc_html( $debate['req_dates'] ) . '</p>';
			$pending = AIADN_Find::requests_for_debate( (int) $debate['id'] );
			if ( $pending ) {
				$h .= '<p><strong>' . count( $pending ) . ' school' . ( 1 === count( $pending ) ? '' : 's' ) . ' would like to debate you.</strong> You choose. Accepting one tells the others it is taken.</p>';
				foreach ( $pending as $r ) {
					$school = AIADN_Schools::get( (int) $r['school_id'] );
					if ( ! $school ) {
						continue;
					}
					$h .= self::form_open( $debate, 'decide_request' ) . '<input type="hidden" name="request_id" value="' . (int) $r['id'] . '"><span><strong>' . self::esc( $school['name'] ) . '</strong>, ' . self::esc( AIADN_Regions::for_postcode( (string) $school['postcode'] ) ) . ', ' . self::esc( implode( ' and ', array_map( static fn( $k ) => AIADN_Motions::AGES[ $k ] ?? '', array_filter( explode( ',', (string) $school['age_phases'] ) ) ) ) ) . '</span> <button class="aiadn__button" type="submit" name="decision" value="accept">Accept</button> <button class="aiadn__button aiadn__button--quiet" type="submit" name="decision" value="decline">Decline</button></form>';
				}
			} else {
				$h .= '<p class="aiadn__small">No school has asked yet. We will email you when one does.</p>';
			}
			return $h . self::form_open( $debate, 'unpublish_request' ) . '<button class="aiadn__button aiadn__button--quiet" type="submit">Take it off Find a Debate</button></form>';
		}
		$phases  = array_filter( explode( ',', (string) $session['school']['age_phases'] ) );
		$val     = static fn( string $k, string $d = '' ): string => (string) ( $v[ $k ] ?? $d );
		$h      .= '<p class="aiadn__small">Show other schools you are looking for an opponent. They see your school name and area, and what you are looking for. They never see anyone&rsquo;s name or email. You choose who you debate.</p>' . self::form_open( $debate, 'publish_request' );
		$h      .= '<fieldset class="aiadn__roles"><legend>Age group</legend>';
		foreach ( AIADN_Motions::AGES as $key => $label ) {
			$h .= '<label class="aiadn__radio"><input type="radio" name="age_group" value="' . esc_attr( $key ) . '"' . checked( $val( 'age_group', $debate['age_group'] ?: ( $phases ? reset( $phases ) : 'primary' ) ), $key, false ) . '> ' . esc_html( $label ) . '</label>';
		}
		$h .= '</fieldset><label for="f-theme">Theme</label><select id="f-theme" name="theme"><option value="">Choose...</option>';
		foreach ( AIADN_Motions::THEMES as $key => $label ) {
			$h .= '<option value="' . esc_attr( $key ) . '"' . selected( $val( 'theme', (string) $debate['theme'] ), $key, false ) . '>' . esc_html( $label ) . '</option>';
		}
		$h .= '</select><label for="f-dates">When suits <span class="aiadn__opt">(for example: any Tuesday in February)</span></label><input id="f-dates" name="req_dates" type="text" maxlength="200" value="' . esc_attr( $val( 'req_dates' ) ) . '">';
		foreach ( array( 'req_format' => array( 'Online or in person', AIADN_Find::FORMATS ), 'req_host' => array( 'Hosting', AIADN_Find::HOST ), 'req_travel' => array( 'Travel', AIADN_Find::TRAVEL ) ) as $name => $set ) {
			$h .= '<label for="f-' . esc_attr( $name ) . '">' . esc_html( $set[0] ) . '</label><select id="f-' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '">';
			foreach ( $set[1] as $key => $label ) {
				$h .= '<option value="' . esc_attr( $key ) . '"' . selected( $val( $name, 'either' === $key || 'region' === $key ? $key : '' ), $key, false ) . '>' . esc_html( $label ) . '</option>';
			}
			$h .= '</select>';
		}
		return $h . self::err( $errors, 'find' ) . '<button class="aiadn__button aiadn__button--quiet" type="submit">Put it on Find a Debate</button></form>';
	}

	private static function render_pack( array $debate, string $side, bool $can_act ): string {
		$ticks = AIADN_Debates::checklist( $debate, $side );
		$h     = '<div class="aiadn__panel"><h2>Debate &amp; Safeguarding Pack</h2>';
		$h    .= '<p>Your school\'s own policies apply. Use this list to check you are ready. The ticks are for your own records: nobody else sees them and they do not block anything.</p>';
		if ( $can_act ) {
			$h .= self::form_open( $debate, 'checklist' );
		}
		foreach ( self::CHECKLIST as $key => $label ) {
			if ( 'online' === $debate['format'] && in_array( $key, self::IN_PERSON_ONLY, true ) ) {
				continue;
			}
			if ( 'in_person' === $debate['format'] && in_array( $key, self::ONLINE_ONLY, true ) ) {
				continue;
			}
			$h .= '<label class="aiadn__radio"><input type="checkbox" name="tick_' . esc_attr( $key ) . '" value="1"' . ( ! empty( $ticks[ $key ] ) ? ' checked' : '' ) . ( $can_act ? '' : ' disabled' ) . '> ' . esc_html( $label ) . '</label>';
		}
		if ( $can_act ) {
			$h .= '<button class="aiadn__button aiadn__button--quiet" type="submit">Save my ticks</button></form>';
		}
		$h .= '<p><em>Challenge the argument. Respect the person.</em> The National Debate Code of Conduct applies to pupils, teachers, judges, visitors and audiences. Report anything concerning to your own safeguarding lead first.</p></div>';
		return $h;
	}

	private static function render_judge_fields( array $debate, string $prefix, array $errors, array $v, string $action, string $button ): string {
		$name = $prefix . 'name';
		$h    = '';
		if ( $action ) {
			$h .= self::form_open( $debate, $action );
		}
		$h   .= '<label for="' . $prefix . 'n">Judge\'s name</label><input id="' . $prefix . 'n" name="' . $name . '" type="text" value="' . esc_attr( (string) ( $v[ $name ] ?? $v['name'] ?? '' ) ) . '" required>' . self::err( $errors, $name );
		$h   .= '<label for="' . $prefix . 'e">Judge\'s email</label><input id="' . $prefix . 'e" name="' . $prefix . 'email" type="email" value="' . esc_attr( (string) ( $v[ $prefix . 'email' ] ?? $v['email'] ?? '' ) ) . '" required>' . self::err( $errors, $prefix . 'email' );
		$h   .= '<label for="' . $prefix . 'o">Organisation <span class="aiadn__opt">(optional)</span></label><input id="' . $prefix . 'o" name="' . $prefix . 'organisation" type="text" value="' . esc_attr( (string) ( $v[ $prefix . 'organisation' ] ?? $v['organisation'] ?? '' ) ) . '">';
		$h   .= '<label for="' . $prefix . 't">Judge type</label><select id="' . $prefix . 't" name="' . $prefix . 'judge_type"><option value="">Choose...</option>';
		$cur  = (string) ( $v[ $prefix . 'judge_type' ] ?? $v['judge_type'] ?? '' );
		foreach ( AIADN_Motions::JUDGE_TYPES as $key => $label ) {
			$h .= '<option value="' . esc_attr( $key ) . '"' . selected( $cur, $key, false ) . '>' . esc_html( $label ) . '</option>';
		}
		$h   .= '</select>' . self::err( $errors, $prefix . 'judge_type' );
		$ack  = ! empty( $v[ $prefix . 'ack' ] ) || ! empty( $v['ack'] );
		$h   .= '<label class="aiadn__radio"><input type="checkbox" name="' . $prefix . 'ack" value="1"' . ( $ack ? ' checked' : '' ) . '> Our school\'s safeguarding and visitor policy applies to the judge.</label>' . self::err( $errors, $prefix . 'ack' );
		if ( $action ) {
			$h .= '<button class="aiadn__button aiadn__button--quiet" type="submit">' . esc_html( $button ) . '</button></form>';
		}
		return $h;
	}

	private static function render_propose_form( array $debate, array $session, array $errors, array $v ): string {
		$phases  = array_filter( explode( ',', (string) $session['school']['age_phases'] ) );
		$default = $v['age_group'] ?? ( $debate['age_group'] ?: ( $phases ? reset( $phases ) : 'primary' ) );
		$h       = '<p>It is a match with <strong>' . self::esc( self::school_name( $debate['school_b_id'] ) ) . '</strong>. Propose the debate.</p>';
		$h      .= self::form_open( $debate, 'propose' );
		$h      .= '<label for="p-when">Date and time</label><input id="p-when" name="starts_at" type="datetime-local" min="' . esc_attr( wp_date( 'Y-m-d\TH:i', time() + HOUR_IN_SECONDS ) ) . '" value="' . esc_attr( (string) ( $v['starts_at'] ?? '' ) ) . '" required>' . self::err( $errors, 'starts_at' );
		$h      .= '<fieldset class="aiadn__roles"><legend>Age group</legend>';
		foreach ( AIADN_Motions::AGES as $key => $label ) {
			$h .= '<label class="aiadn__radio"><input type="radio" name="age_group" value="' . esc_attr( $key ) . '"' . checked( $default, $key, false ) . '> ' . esc_html( $label ) . '</label>';
		}
		$h .= '</fieldset>' . self::err( $errors, 'age_group' );
		$h .= '<p class="aiadn__small">We recommend ' . (int) AIADN_Format::TOTAL_MINUTES . ' minutes, and you can adapt it to suit your school. Once the debate is agreed, both schools get a prep pack with a suggested running order.</p>';
		$h .= '<label for="p-theme">Theme</label><select id="p-theme" name="theme"><option value="">Choose...</option>';
		foreach ( AIADN_Motions::THEMES as $key => $label ) {
			$h .= '<option value="' . esc_attr( $key ) . '"' . selected( (string) ( $v['theme'] ?? $debate['theme'] ), $key, false ) . '>' . esc_html( $label ) . '</option>';
		}
		$h .= '</select>' . self::err( $errors, 'theme' );
		$h .= '<label for="p-motion">Motion</label><select id="p-motion" name="motion_key"><option value="">Choose a theme and age group first</option>';
		foreach ( AIADN_Motions::all() as $key => $m ) {
			$h .= '<option value="' . esc_attr( $key ) . '" data-theme="' . esc_attr( $m['theme'] ) . '" data-age="' . esc_attr( $m['age'] ) . '"' . selected( (string) ( $v['motion_key'] ?? '' ), $key, false ) . '>' . esc_html( $m['text'] ) . '</option>';
		}
		$h .= '</select>' . self::err( $errors, 'motion_key' );
		$h .= '<fieldset class="aiadn__roles"><legend>We argue</legend>';
		foreach ( array( 'for' => 'For the motion', 'against' => 'Against the motion' ) as $key => $label ) {
			$h .= '<label class="aiadn__radio"><input type="radio" name="a_side" value="' . esc_attr( $key ) . '"' . checked( (string) ( $v['a_side'] ?? '' ), $key, false ) . '> ' . esc_html( $label ) . '</label>';
		}
		$h .= '</fieldset>' . self::err( $errors, 'a_side' );
		$h .= '<fieldset class="aiadn__roles"><legend>Format</legend>';
		foreach ( AIADN_Motions::FORMATS as $key => $label ) {
			$h .= '<label class="aiadn__radio"><input type="radio" name="format" value="' . esc_attr( $key ) . '"' . checked( (string) ( $v['format'] ?? 'in_person' ), $key, false ) . '> ' . esc_html( $label ) . '</label>';
		}
		$h .= '</fieldset>' . self::err( $errors, 'format' );
		$h .= '<div data-for="in_person"><label for="p-venue">Venue</label><input id="p-venue" name="venue" type="text" value="' . esc_attr( (string) ( $v['venue'] ?? '' ) ) . '" placeholder="e.g. the school hall">' . self::err( $errors, 'venue' );
		$h .= '<label for="p-addr">Address <span class="aiadn__opt">(street, town and postcode)</span></label><input id="p-addr" name="venue_address" type="text" value="' . esc_attr( (string) ( $v['venue_address'] ?? '' ) ) . '" autocomplete="street-address" placeholder="e.g. 12 High Street, Leeds, LS6 2AB">' . self::err( $errors, 'venue_address' );
		$h .= '<p class="aiadn__hint">This goes in the calendar entry and gives the judge directions, so please give the school\'s full address, not just the room.</p></div>';
		$h .= '<div data-for="online"><label for="p-ics">Upload the calendar file from your meeting (.ics)</label><input id="p-ics" name="meeting_ics" type="file" accept=".ics,text/calendar">';
		$h .= '<p class="aiadn__hint">Create the meeting in Teams, Meet or Zoom, download its calendar file (.ics) and upload it here. We take the join link from it, so on the day everyone joins from this site and nobody has to search their email. We do not keep the file.</p>';
		$h .= '<label for="p-link">Or paste the meeting link</label><input id="p-link" name="meeting_url" type="url" inputmode="url" value="' . esc_attr( (string) ( $v['meeting_url'] ?? '' ) ) . '" placeholder="https://teams.microsoft.com/...">' . self::err( $errors, 'meeting_url' );
		$h .= '<p class="aiadn__hint">As the host, you create the meeting on the platform your school approves. Turn the waiting room on, admit the other school and the judge yourself, and do not record.</p></div>';
		// In person there is no meeting to send invites from, so we make the calendar file: on to begin with.
		// Online, the host has usually invited people from Teams already: off, and switched by the format choice.
		$cal_on = $v ? ! empty( $v['calendar'] ) : true;
		$h .= '<label class="aiadn__radio aiadn__calendar"><input type="checkbox" name="calendar" value="1"' . ( $cal_on ? ' checked' : '' ) . '> Email calendar invites (.ics) so it is booked in everyone\'s diary</label>';
		$h .= '<p class="aiadn__hint">We make the calendar file, so nobody has to. Once both schools agree, we email an invite to you, the other school and both schools\' headteachers or senior leaders, and to the judge when they accept. If the debate changes it updates, and if it is cancelled it is marked cancelled. For an online debate, leave this off if you have already invited people from Teams, so nobody gets two.</p>';
		$h .= '<h3>The judge</h3><p class="aiadn__small">Someone independent of both schools. We invite them once both schools agree the fixture.</p>';
		$h .= self::render_judge_fields( $debate, 'j_', $errors, $v, '', '' );
		$h .= '<button class="aiadn__button" type="submit">Send to ' . self::esc( self::school_name( $debate['school_b_id'] ) ) . '</button></form>';
		$h .= '<script>(function(){var f=document.querySelector("form input[value=propose]");if(f){f=f.form;var cal=f.querySelector("input[name=calendar]"),touched=false;if(cal){cal.addEventListener("change",function(){touched=true;});}var s=function(){var r=f.querySelector("input[name=format]:checked");var v=r?r.value:"in_person";f.querySelectorAll("[data-for]").forEach(function(d){d.style.display=d.getAttribute("data-for")===v?"":"none";});};f.querySelectorAll("input[name=format]").forEach(function(r){r.addEventListener("change",function(){s();if(cal&&!touched){cal.checked=(r.value==="in_person");}});});s();}})();</script>';
		$h .= '<script>(function(){var t=document.getElementById("p-theme"),m=document.getElementById("p-motion");if(!t||!m)return;function f(){var a=document.querySelector("input[name=age_group]:checked");var age=a?a.value:"",th=t.value;var any=false;for(var i=1;i<m.options.length;i++){var o=m.options[i];var ok=o.dataset.theme===th&&o.dataset.age===age;o.hidden=!ok;o.disabled=!ok;if(ok)any=true;if(!ok&&o.selected)m.selectedIndex=0;}m.options[0].text=any?"Choose a motion...":"Choose a theme and age group first";}t.addEventListener("change",f);document.querySelectorAll("input[name=age_group]").forEach(function(r){r.addEventListener("change",f);});f();})();</script>';
		return $h;
	}

	/* ------------------------------------------------------------------ */
	/* /conversation/judge/                                                */
	/* ------------------------------------------------------------------ */

	public static function view_judge(): string {
		AIADN_Front::set_title( 'Judge' );
		$raw     = AIADN_Front::get( 't' ) ?: AIADN_Front::post( 't' );
		$session = AIADN_Auth::current();
		$mode    = '';
		$judge   = null;

		if ( '' !== $raw ) {
			$token = preg_match( '/^[a-f0-9]{40}$/', $raw ) ? AIADN_Auth::find_token( $raw, 'judge' ) : null;
			if ( $token ) {
				$judge = AIADN_Debates::get_judge( (int) $token['ref_id'] );
				$mode  = 'token';
			}
			if ( ! $judge ) {
				return '<h1>This link is no longer valid</h1><p>It may have expired, or the teachers may have chosen a different judge. Ask them to send it again.</p>';
			}
		} elseif ( $session && 'judge' === $session['role'] ) {
			$mode = 'session';
		} else {
			AIADN_Front::redirect( 'join', array( 'msg' => 'signin' ) );
		}

		if ( AIADN_Front::is_post() && 'judge_respond' === AIADN_Front::post( 'aiadn_action' ) ) {
			self::handle_judge_response( $mode, $judge, $session );
		}

		if ( 'token' === $mode ) {
			$first = AIADN_Debates::get( (int) $judge['debate_id'] );
			if ( $first && ! empty( $first['theme'] ) ) {
				AIADN_Front::set_strand( $first['theme'] );
			}
			$rows = array( $judge );
		} else {
			$rows = AIADN_Debates::judges_for_school_email( (int) $session['school_id'], (string) $session['member']['email'] );
		}

		$h  = '<h1>Judging</h1>';
		$flash = array(
			'accepted' => array( 'Thank you. You are down to judge this debate.', 'info' ),
			'declined' => array( 'Thank you for letting us know. The teachers have been told.', 'info' ),
			'ack'      => array( "Please tick the box to confirm you'll follow the host school's rules.", 'error' ),
		);
		$msg = AIADN_Front::get( 'msg' );
		if ( isset( $flash[ $msg ] ) ) {
			$h .= AIADN_Front::notice( $flash[ $msg ][0], $flash[ $msg ][1] );
		}
		if ( ! $rows ) {
			return $h . '<p>You are not down to judge any debates here.</p>';
		}
		foreach ( $rows as $row ) {
			$debate = AIADN_Debates::get( (int) $row['debate_id'] );
			if ( ! $debate ) {
				continue;
			}
			$h .= self::render_judge_card( $row, $debate, $mode, $raw );
		}
		if ( 'session' === $mode ) {
			$h .= '<form method="post" action="' . esc_url( AIADN_Front::url( 'school' ) ) . '" class="aiadn__signout">' . AIADN_Front::csrf_field() . '<input type="hidden" name="aiadn_action" value="logout"><button class="aiadn__link" type="submit">Sign out</button></form>';
		}
		return $h;
	}

	private static function render_judge_card( array $judge, array $debate, string $mode, string $raw ): string {
		$a = AIADN_Schools::get( (int) $debate['school_a_id'] );
		$b = $debate['school_b_id'] ? AIADN_Schools::get( (int) $debate['school_b_id'] ) : null;
		$h = '<div class="aiadn__panel"><h2>' . self::esc( $a['name'] ) . ' v ' . self::esc( $b ? $b['name'] : '' ) . '</h2><dl class="aiadn__details">';
		$h .= '<dt>When</dt><dd>' . self::esc( AIADN_Util::show( (string) $debate['starts_at'] ) ) . '</dd>';
		$h .= '<dt>Where</dt><dd>' . self::where_html( $debate ) . '</dd>';
		$h .= '<dt>Theme</dt><dd>' . self::esc( AIADN_Motions::THEMES[ $debate['theme'] ] ?? '' ) . '</dd>';
		$h .= '<dt>Motion</dt><dd>&ldquo;' . self::esc( $debate['motion_text'] ) . '&rdquo;</dd></dl>';

		if ( 'cancelled' === $debate['status'] ) {
			return $h . '<p><strong>This debate has been cancelled.</strong> There is nothing more to do.</p></div>';
		}
		if ( 'accepted' === $judge['status'] ) {
			$score_url = 'token' === $mode ? AIADN_Front::url( 'score', array( 't' => $raw ) ) : AIADN_Front::url( 'score', array( 'd' => $debate['code'] ) );
			$cal_url   = 'token' === $mode ? AIADN_Front::url( 'calendar', array( 't' => $raw ) ) : AIADN_Front::url( 'calendar', array( 'd' => $debate['code'] ) );
			$prep_url  = 'token' === $mode ? AIADN_Front::url( 'prep', array( 't' => $raw ) ) : AIADN_Front::url( 'prep', array( 'd' => $debate['code'] ) );
			$live      = in_array( $debate['status'], array( 'agreed', 'ready' ), true );
			$h        .= self::join_panel( $debate, 'judge', $live ? $cal_url : '', '', $live ? $prep_url : '' );
			if ( ! AIADN_Debates::is_today( $debate ) && $live ) {
				$h .= self::prep_button( $prep_url ) . self::calendar_button( $cal_url );
			}
			if ( in_array( $debate['status'], array( 'completed', 'void' ), true ) ) {
				return $h . '<p><strong>Your result has been submitted.</strong> Thank you.</p><a class="aiadn__button aiadn__button--quiet" href="' . esc_url( $score_url ) . '">See what you submitted</a></div>';
			}
			// Two ways to score, as in the brief: on the phone, or on paper and then enter the final scores.
			$paper_url = 'token' === $mode ? AIADN_Front::url( 'paper', array( 't' => $raw ) ) : AIADN_Front::url( 'paper', array( 'd' => $debate['code'] ) );
			$phone     = AIADN_Scorecards::is_open( $debate )
				? '<p><strong>On your phone.</strong> Scoring is open.</p><p><a class="aiadn__button" href="' . esc_url( $score_url ) . '">Open the scorecard</a></p>'
				: '<p><strong>On your phone.</strong> Scoring opens ' . self::esc( AIADN_Util::show( gmdate( 'Y-m-d H:i:s', AIADN_Scorecards::opens_at( $debate ) ) ) ) . ', three hours before the debate. Come back to this page then.</p>';
			$paper     = '<p><strong>On paper.</strong> Your host school may give you a printed scorecard. If not, you can print one here. After the debate, scan the code on it and enter the final scores. The online entry is the official result.</p><p><a class="aiadn__button aiadn__button--quiet" href="' . esc_url( $paper_url ) . '" target="_blank" rel="noopener">Print a paper scorecard</a></p>';
			return $h . '<p><strong>You have accepted.</strong> There are two ways to score.</p>' . $phone . $paper . '</div>';
		}
		if ( 'declined' === $judge['status'] ) {
			return $h . '<p>You have told us you can\'t make it. Thank you.</p></div>';
		}
		if ( 'invited' !== $judge['status'] ) {
			return $h . '<p>This invitation is no longer active.</p></div>';
		}

		$h .= '<form method="post" action="' . esc_url( AIADN_Front::url( 'judge' ) ) . '" class="aiadn__form">';
		$h .= 'token' === $mode ? '<input type="hidden" name="t" value="' . esc_attr( $raw ) . '">' : AIADN_Front::csrf_field();
		$h .= '<input type="hidden" name="aiadn_action" value="judge_respond"><input type="hidden" name="judge_id" value="' . (int) $judge['id'] . '">';
		$h .= '<label class="aiadn__radio"><input type="checkbox" name="ack" value="1"> I will follow the host school\'s visitor and safeguarding rules.</label>';
		$h .= '<label class="aiadn__radio"><input type="checkbox" name="name_public" value="1"> Show my name on the public result.</label>';
		$h .= '<fieldset class="aiadn__roles"><legend>Did an organisation introduce you to judging? <span class="aiadn__opt">(optional)</span></legend><label for="jr-org">Organisation</label><input id="jr-org" name="ref_org" type="text" maxlength="200"><label for="jr-email">Email of the person there</label><input id="jr-email" name="ref_email" type="email"><label class="aiadn__radio"><input type="checkbox" name="share_referral" value="1"> Tell them I am judging, and when I have judged. They will see my name.</label><p class="aiadn__small">You are responsible for having their permission to give us their email address. They see your name in those emails and only totals on their dashboard.</p></fieldset>';
		$h .= '<button class="aiadn__button" type="submit" name="decision" value="accept">I can judge</button> <button class="aiadn__button aiadn__button--quiet" type="submit" name="decision" value="decline">I can\'t make it</button></form></div>';
		return $h;
	}

	private static function handle_judge_response( string $mode, ?array $judge, ?array $session ): void {
		if ( ! AIADN_Util::allow( 'judge|' . AIADN_Util::client_ip(), 30, 600 ) ) {
			AIADN_Front::redirect( 'judge', array( 'msg' => 'slow' ) );
		}
		$target = AIADN_Debates::get_judge( (int) AIADN_Front::post( 'judge_id' ) );
		$ok     = false;
		if ( $target && 'token' === $mode && $judge && (int) $judge['id'] === (int) $target['id'] ) {
			$ok = true;
		} elseif ( $target && 'session' === $mode && $session && AIADN_Auth::csrf_ok( AIADN_Front::post( 'csrf' ) ) && strtolower( $target['email'] ) === strtolower( (string) $session['member']['email'] ) && AIADN_Debates::judge_involves_school( $target, (int) $session['school_id'] ) ) {
			$ok = true;
		}
		$back = 'token' === $mode ? array( 't' => AIADN_Front::post( 't' ) ) : array();
		if ( ! $ok || 'invited' !== $target['status'] ) {
			AIADN_Front::redirect( 'judge', $back );
		}
		$debate = AIADN_Debates::get( (int) $target['debate_id'] );
		if ( ! $debate || 'cancelled' === $debate['status'] ) {
			AIADN_Front::redirect( 'judge', $back );
		}
		$accept = 'accept' === AIADN_Front::post( 'decision' );
		if ( $accept && '' === AIADN_Front::post( 'ack' ) ) {
			AIADN_Front::redirect( 'judge', array_merge( $back, array( 'msg' => 'ack' ) ) );
		}
		AIADN_Debates::judge_respond( $debate, $target, $accept, '' !== AIADN_Front::post( 'name_public' ) );
		if ( $accept ) {
			$rid = AIADN_Referrals::name_referrer( 'judge', (int) $target['id'], AIADN_Front::post( 'ref_org' ), AIADN_Front::post( 'ref_email' ) );
			if ( $rid ) {
				AIADN_Referrals::decide( AIADN_Referrals::get( $rid ), '' !== AIADN_Front::post( 'share_referral' ), strtolower( $target['email'] ) );
			}
		}
		AIADN_Front::redirect( 'judge', array_merge( $back, array( 'msg' => $accept ? 'accepted' : 'declined' ) ) );
	}
}
