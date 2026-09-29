<?php
/**
 * Public pages and their form handling.
 *
 * Routes (rewrite rules, no WordPress pages needed):
 *   /conversation/join/      front door (screen 0), code entry (screen 3), student landing
 *   /conversation/register/  register a school (screen 1)
 *   /conversation/approve/   SLT approval (screen 4)
 *   /conversation/school/    school dashboard (screens 6 and 7)
 *
 * Forms post back to the same URL. Pages are never cached and never indexed.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Front {

	const VIEWS = array( 'join', 'register', 'approve', 'school', 'invite', 'debate', 'judge', 'score', 'results', 'issue', 'certificate', 'check', 'debates', 'survey', 'voice', 'board', 'calendar', 'paper', 'prep', 'programme', 'snapshot', 'colleague' );

	/** @var string */
	private static $title = 'National AI Conversation';

	/** @var string Strand colour for the page: safe, smart, creative, responsible or future. */
	private static $strand = 'safe';

	/** Each page takes a strand colour, like the homepage sections. Debate pages override it with their theme. */
	const VIEW_STRANDS = array(
		'join'     => 'safe',
		'register' => 'creative',
		'approve'  => 'responsible',
		'school'   => 'safe',
		'invite'   => 'smart',
		'debate'   => 'smart',
		'judge'    => 'smart',
		'score'    => 'smart',
		'results'  => 'future',
		'issue'    => 'smart',
		'certificate' => 'responsible',
		'check'    => 'responsible',
		'debates'  => 'future',
		'survey'   => 'safe',
		'voice'    => 'safe',
		'board'    => 'safe',
		'calendar' => 'smart',
		'paper'    => 'smart',
		'prep'     => 'smart',
		'programme' => 'future',
		'snapshot' => 'safe',
		'colleague' => 'creative',
	);

	public static function register(): void {
		add_action( 'init', array( __CLASS__, 'add_rewrites' ), 5 );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'dispatch' ), 1 );
		add_action( 'init', array( __CLASS__, 'maybe_flush_rewrites' ), 99 );
	}

	/** Flush once per plugin version, so a deploy never needs a manual permalink reset. */
	public static function maybe_flush_rewrites(): void {
		if ( AIADN_VERSION === get_option( 'aiadn_rewrite_version' ) ) {
			return;
		}
		flush_rewrite_rules( false );
		update_option( 'aiadn_rewrite_version', AIADN_VERSION, false );
	}

	public static function add_rewrites(): void {
		add_rewrite_rule( '^conversation/(' . implode( '|', self::VIEWS ) . ')/?$', 'index.php?aiadn_view=$matches[1]', 'top' );
	}

	public static function query_vars( array $vars ): array {
		$vars[] = 'aiadn_view';
		return $vars;
	}

	/** The AiAd27 lockup from the theme's asset pack, for pages people print (the site header does not print). */
	public static function logo_html( string $class = '' ): string {
		return '<img class="aiadn__logo ' . esc_attr( $class ) . '" src="' . esc_url( get_theme_file_uri( 'assets/brand/aiad27/aiad27-lockup.svg' ) ) . '" alt="AI Awareness Day 2027, Keep Humans in the Loop" width="240" height="45">';
	}

	public static function set_title( string $title ): void {
		self::$title = $title;
	}

	public static function set_strand( string $strand ): void {
		if ( isset( AIADN_Motions::THEMES[ $strand ] ) ) {
			self::$strand = $strand;
		}
	}

	public static function url( string $view, array $args = array() ): string {
		return add_query_arg( $args, home_url( '/conversation/' . $view . '/' ) );
	}

	/* ------------------------------------------------------------------ */
	/* Routing                                                             */
	/* ------------------------------------------------------------------ */

	public static function dispatch(): void {
		$view = (string) get_query_var( 'aiadn_view' );
		if ( '' === $view || ! in_array( $view, self::VIEWS, true ) ) {
			return;
		}

		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		nocache_headers();
		header( 'X-Robots-Tag: noindex, nofollow' );
		status_header( 200 );

		if ( ! AIADN_Database::tables_exist() ) {
			AIADN_Database::create_tables();
		}

		self::$strand = self::VIEW_STRANDS[ $view ];
		$handler      = array( __CLASS__, 'view_' . $view );
		if ( ! method_exists( __CLASS__, 'view_' . $view ) ) {
			$handler = array( 'AIADN_Voice_Front', 'view_' . $view );
			foreach ( array( 'AIADN_Result_Front', 'AIADN_Debate_Front', 'AIADN_Programme_Front', 'AIADN_Snapshot_Front', 'AIADN_Colleague_Front' ) as $class ) {
				if ( method_exists( $class, 'view_' . $view ) ) {
					$handler = array( $class, 'view_' . $view );
				}
			}
		}
		$html         = call_user_func( $handler );
		self::output( $html );
	}

	private static function output( string $inner ): void {
		add_filter( 'pre_get_document_title', static fn() => self::$title . ' | AI Awareness Day' );
		add_filter( 'wp_robots', static fn( $r ) => array( 'noindex' => true, 'nofollow' => true ) );
		wp_enqueue_style( 'aiadn', AIADN_PLUGIN_URL . 'public/aiadn.css', array(), AIADN_VERSION . '.' . (int) @filemtime( AIADN_PLUGIN_DIR . 'public/aiadn.css' ) ); // The file's date is part of the version, so a changed stylesheet is never served stale.
		get_header();

		// The page's own <h1> (and an optional "back" link above it) moves into the coloured band.
		$lead  = '';
		$title = esc_html( self::$title );
		$rest  = $inner;
		if ( preg_match( '#^(<p class="aiadn__small">.*?</p>)?<h1>(.*?)</h1>(.*)$#s', $inner, $m ) ) {
			$lead  = $m[1];
			$title = $m[2];
			$rest  = $m[3];
		}

		echo '<main id="main" class="aiadn aiadn--' . esc_attr( self::$strand ) . '">';
		echo '<header class="aiadn__band"><div class="aiadn__wrap">';
		echo $lead; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts.
		echo '<p class="aiadn__eyebrow">National AI Conversation</p>';
		echo '<h1>' . $title . '</h1>'; // phpcs:ignore WordPress.Security.EscapeOutput -- already escaped where the view built it.
		echo '</div></header>';
		echo '<div class="aiadn__body"><div class="aiadn__wrap">';
		echo $rest; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts below.
		echo '</div></div></main>';
		get_footer();
		exit;
	}

	public static function redirect( string $view, array $args = array() ): void {
		wp_safe_redirect( self::url( $view, $args ) );
		exit;
	}

	/* ------------------------------------------------------------------ */
	/* Small helpers                                                       */
	/* ------------------------------------------------------------------ */

	public static function is_post(): bool {
		return isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'];
	}

	public static function post( string $key ): string {
		return isset( $_POST[ $key ] ) && is_string( $_POST[ $key ] ) ? trim( sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	}

	public static function get( string $key ): string {
		return isset( $_GET[ $key ] ) && is_string( $_GET[ $key ] ) ? trim( sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	}

	public static function message( string $key ): string {
		$messages = array(
			'nomatch'   => "We couldn't match those details.",
			'slow'      => 'Too many attempts. Please wait a few minutes and try again.',
			'wrongcode' => "That code didn't work. Check it and try again, or start again.",
			'signin'    => 'Please sign in with your school code.',
			'signedout' => 'You are signed out.',
			'pin'       => 'A new class PIN has started. The old one no longer works.',
			'colleague' => 'Done. We have told your colleague.',
			'resent'    => 'We have sent the approval email again.',
			'invalid'   => 'That link is no longer valid.',
			'declined'  => 'You have left this debate. The other school has been told.',
		);
		return $messages[ $key ] ?? '';
	}

	public static function notice( string $text, string $type = 'info' ): string {
		return '' === $text ? '' : '<p class="aiadn__notice aiadn__notice--' . esc_attr( $type ) . '" role="status">' . esc_html( $text ) . '</p>';
	}

	public static function local_time( string $utc ): string {
		return wp_date( 'H:i', strtotime( $utc . ' UTC' ) );
	}

	public static function honeypot(): string {
		return '<div class="aiadn__hp" aria-hidden="true"><label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>';
	}

	public static function honeypot_tripped(): bool {
		return '' !== self::post( 'website' );
	}

	public static function csrf_field(): string {
		return '<input type="hidden" name="csrf" value="' . esc_attr( AIADN_Auth::csrf() ) . '">';
	}

	/** Remember a masked address for the "check your email" screen, keyed by ref, for real and decoy refs alike. */
	private static function remember_mask( string $ref, string $email ): void {
		set_transient( 'aiadn_mask_' . $ref, AIADN_Util::mask_email( $email ), AIADN_Auth::CODE_TTL );
	}

	/** Send a code to the address, or pretend to. Either way the caller gets a ref and the screen looks identical. */
	private static function send_or_decoy( ?array $school, string $email, string $purpose, string $role_hint, bool $eligible ): string {
		$send = $eligible && $school && AIADN_Util::allow( 'send|' . $email, 5, HOUR_IN_SECONDS );
		if ( $send ) {
			list( $ref, $code ) = AIADN_Auth::issue_code( (int) $school['id'], $email, $purpose, $role_hint );
			if ( ! AIADN_Mailer::send_code( $email, $code ) ) {
				error_log( 'AIADN: could not send sign-in code email.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			}
		} else {
			$ref = AIADN_Auth::decoy_ref();
		}
		self::remember_mask( $ref, $email );
		return $ref;
	}

	/* ------------------------------------------------------------------ */
	/* /conversation/join/  front door                                     */
	/* ------------------------------------------------------------------ */

	private static function view_join(): string {
		self::$title = 'Enter your school code';

		if ( self::is_post() ) {
			$action = self::post( 'aiadn_action' );
			if ( 'front_door' === $action ) {
				self::handle_front_door();
			} elseif ( 'verify' === $action ) {
				self::handle_verify( 'join' );
			}
		}

		$step = self::get( 'step' );
		if ( 'code' === $step ) {
			return self::render_code_form( 'join', self::get( 'ref' ), self::message( self::get( 'msg' ) ) );
		}
		if ( 'student' === $step ) {
			return self::render_student_landing();
		}
		return self::render_front_door( self::message( self::get( 'msg' ) ), AIADN_Util::normalise_school_code( self::get( 'c' ) ) );
	}

	/** An invitation token that is being carried through sign-in, or ''. */
	public static function carried_invite(): string {
		$inv = self::post( 'inv' ) ?: self::get( 'inv' );
		return preg_match( '/^[a-f0-9]{40}$/', $inv ) ? $inv : '';
	}

	private static function render_front_door( string $error, string $prefill_code ): string {
		$h  = '<h1>Enter your school code</h1>';
		$h .= self::notice( $error, 'error' );
		if ( self::carried_invite() ) {
			$h .= '<p>Sign in with your school code to accept the debate invitation.</p>';
		}
		$h .= '<form method="post" action="' . esc_url( self::url( 'join' ) ) . '" class="aiadn__form" novalidate>';
		$h .= '<input type="hidden" name="aiadn_action" value="front_door">';
		if ( self::carried_invite() ) {
			$h .= '<input type="hidden" name="inv" value="' . esc_attr( self::carried_invite() ) . '">';
		}
		$h .= self::honeypot();
		$h .= '<label for="aiadn-code">School code</label>';
		$h .= '<input id="aiadn-code" name="school_code" type="text" value="' . esc_attr( $prefill_code ) . '" placeholder="SCH-3F9A2" autocapitalize="characters" autocomplete="off" maxlength="12" required class="aiadn__code">';
		$h .= '<fieldset class="aiadn__roles"><legend>I am a...</legend>';
		foreach ( array( 'student' => 'Student', 'teacher' => 'Teacher', 'judge' => 'Judge', 'slt' => 'Headteacher / SLT' ) as $value => $label ) {
			$h .= '<label class="aiadn__radio"><input type="radio" name="role" value="' . esc_attr( $value ) . '"' . ( 'teacher' === $value ? ' checked' : '' ) . '> ' . esc_html( $label ) . '</label>';
		}
		$h .= '</fieldset>';
		$h .= '<div class="aiadn__field" data-for="email"><label for="aiadn-email">Your school email</label>';
		$h .= '<input id="aiadn-email" name="email" type="email" autocomplete="email" placeholder="name@school.org.uk">';
		$h .= '<p class="aiadn__hint">We will email you a 6-digit code.</p></div>';
		$h .= '<div class="aiadn__field" data-for="pin"><label for="aiadn-pin">Class PIN (students)</label>';
		$h .= '<input id="aiadn-pin" name="pin" type="text" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" autocomplete="off" class="aiadn__pin">';
		$h .= '<p class="aiadn__hint">Your teacher will show the PIN on the board.</p></div>';
		$h .= '<button type="submit" class="aiadn__button">Continue</button>';
		$h .= '</form>';
		$h .= '<p class="aiadn__small">New school? <a href="' . esc_url( self::url( 'register' ) ) . '">Register here</a>. Lost your code? Ask your teacher or headteacher.</p><p class="aiadn__small">A colleague without your school\'s email address? <a href="' . esc_url( self::url( 'colleague' ) ) . '">Ask to join</a>.</p>';
		$h .= '<script>(function(){var f=document.querySelector(".aiadn__form");if(!f)return;function s(){var r=f.querySelector("input[name=role]:checked");var st=r&&r.value==="student";f.querySelector("[data-for=pin]").style.display=st?"":"none";f.querySelector("[data-for=email]").style.display=st?"none":"";}f.addEventListener("change",s);s();})();</script>';
		return $h;
	}

	private static function handle_front_door(): void {
		$ip = AIADN_Util::client_ip();
		// A whole class shares one school network address, so this is generous. The real protections are
		// per person (a code goes to an email at most 5 times an hour) and per PIN (wrong guesses, below).
		if ( ! AIADN_Util::allow( 'fd|' . $ip, 300, 600 ) ) {
			self::redirect( 'join', array( 'msg' => 'slow' ) );
		}
		if ( self::honeypot_tripped() ) {
			self::redirect( 'join', array( 'msg' => 'nomatch' ) );
		}

		$code   = AIADN_Util::normalise_school_code( self::post( 'school_code' ) );
		$role   = self::post( 'role' );
		$school = $code ? AIADN_Schools::get_by_code( $code ) : null;

		if ( 'student' === $role ) {
			// Guessing PINs is the only real attack here, so only WRONG guesses count: 15 per 10 minutes from one
			// network, and 60 an hour against one PIN from anywhere. Starting a new PIN starts the count again.
			// Counting every sign-in would lock a whole class of 30 out of its own lesson.
			$running   = ( $school && 'approved' === $school['status'] ) ? AIADN_Auth::current_pin( (int) $school['id'] ) : null;
			$bucket_ip = 'pinfail|ip|' . $code . '|' . $ip;
			$bucket_pin = 'pinfail|' . ( $running ? (int) $running['id'] : 0 ) . '|' . $code;
			if ( AIADN_Util::exhausted( $bucket_ip, 15 ) || AIADN_Util::exhausted( $bucket_pin, 60 ) ) {
				self::redirect( 'join', array( 'msg' => 'slow' ) );
			}
			$pin_row = ( $school && 'approved' === $school['status'] ) ? AIADN_Auth::pin_row_matching( (int) $school['id'], self::post( 'pin' ) ) : null;
			if ( $pin_row ) {
				// For students the session's member id is the PIN they used, so their answers can be filed under it.
				AIADN_Auth::start_session( array( 'member_id' => (int) $pin_row['id'], 'school_id' => (int) $school['id'], 'role' => 'student' ), AIADN_Auth::STUDENT_TTL );
				self::redirect( 'survey' );
			}
			AIADN_Util::record( $bucket_ip, 600 );
			AIADN_Util::record( $bucket_pin, 3600 );
			self::redirect( 'join', array( 'msg' => 'nomatch' ) );
		}

		if ( ! in_array( $role, array( 'teacher', 'judge', 'slt' ), true ) ) {
			self::redirect( 'join', array( 'msg' => 'nomatch' ) );
		}

		$email = AIADN_Util::normalise_email( self::post( 'email' ) );
		if ( ! is_email( $email ) ) {
			self::redirect( 'join', array( 'msg' => 'nomatch' ) );
		}

		$eligible = false;
		if ( $school ) {
			list( $member, $by_domain ) = AIADN_Schools::eligibility( $school, $email, $role );
			$eligible                   = (bool) ( $member || $by_domain );
		}
		$ref  = self::send_or_decoy( $school, $email, 'signin', $role, $eligible );
		$args = array( 'step' => 'code', 'ref' => $ref );
		if ( self::carried_invite() ) {
			$args['inv'] = self::carried_invite();
		}
		self::redirect( 'join', $args );
	}

	private static function render_code_form( string $view, string $ref, string $error ): string {
		$ref  = preg_replace( '/[^a-f0-9]/', '', strtolower( $ref ) );
		$mask = get_transient( 'aiadn_mask_' . $ref );
		$h    = '<h1>Check your email</h1>';
		$h   .= '<p>If those details match, we have sent a 6-digit code' . ( $mask ? ' to <strong>' . esc_html( $mask ) . '</strong>' : '' ) . '.</p>';
		$h   .= self::notice( $error, 'error' );
		$h   .= '<form method="post" action="' . esc_url( self::url( $view ) ) . '" class="aiadn__form">';
		$h   .= '<input type="hidden" name="aiadn_action" value="verify"><input type="hidden" name="ref" value="' . esc_attr( $ref ) . '">';
		if ( self::carried_invite() ) {
			$h .= '<input type="hidden" name="inv" value="' . esc_attr( self::carried_invite() ) . '">';
		}
		$h   .= '<label for="aiadn-digits">6-digit code</label>';
		$h   .= '<input id="aiadn-digits" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required class="aiadn__code" autofocus>';
		$h   .= '<button type="submit" class="aiadn__button">Continue</button></form>';
		$h   .= '<p class="aiadn__small">The code works once and expires in 15 minutes. <a href="' . esc_url( self::url( 'join' ) ) . '">Start again</a>.</p>';
		return $h;
	}

	private static function handle_verify( string $back_view ): void {
		if ( ! AIADN_Util::allow( 'vf|' . AIADN_Util::client_ip(), 30, 600 ) ) {
			self::redirect( $back_view, array( 'step' => 'code', 'ref' => self::post( 'ref' ), 'msg' => 'slow' ) );
		}
		$row = AIADN_Auth::verify_code( self::post( 'ref' ), self::post( 'code' ) );
		if ( ! $row ) {
			self::redirect( $back_view, array( 'step' => 'code', 'ref' => self::post( 'ref' ), 'msg' => 'wrongcode' ) );
		}

		$school = AIADN_Schools::get( (int) $row['school_id'] );
		if ( ! $school ) {
			self::redirect( 'join', array( 'msg' => 'nomatch' ) );
		}
		$email  = (string) $row['email'];
		$member = AIADN_Schools::find_member( (int) $school['id'], $email );

		if ( 'register' === $row['purpose'] ) {
			if ( ! $member ) {
				self::redirect( 'join', array( 'msg' => 'nomatch' ) );
			}
			AIADN_Schools::mark_member_verified( (int) $member['id'] );
			if ( 'pending_email' === $school['status'] ) {
				AIADN_Schools::assign_code( (int) $school['id'] );
				AIADN_Schools::set_status( (int) $school['id'], 'pending_slt' );
				self::send_slt_email( AIADN_Schools::get( (int) $school['id'] ), $member );
			}
			AIADN_Auth::start_session( array( 'member_id' => (int) $member['id'], 'school_id' => (int) $school['id'], 'role' => (string) $member['role'] ), AIADN_Auth::SESSION_TTL );
			self::redirect( 'school', array( 'welcome' => 1 ) );
		}

		// A judge signs in as that judge, not as a school member.
		if ( 'judge' === $row['role_hint'] ) {
			$judge = AIADN_Debates::judge_for_school_email( (int) $school['id'], $email );
			if ( ! $judge ) {
				self::redirect( 'join', array( 'msg' => 'nomatch' ) );
			}
			AIADN_Auth::start_session( array( 'member_id' => (int) $judge['id'], 'school_id' => (int) $school['id'], 'role' => 'judge' ), AIADN_Auth::JUDGE_TTL );
			self::redirect( 'judge' );
		}

		// Sign in. A colleague on the same email domain joins here (decision applied: same domain joins straight away).
		if ( ! $member && 'teacher' === $row['role_hint'] ) {
			list( , $by_domain ) = AIADN_Schools::eligibility( $school, $email, 'teacher' );
			if ( $by_domain ) {
				$new_id = AIADN_Schools::add_member( (int) $school['id'], $email, '', '', 'teacher', true );
				$member = AIADN_Schools::get_member( $new_id );
				$lead   = AIADN_Schools::lead( (int) $school['id'] );
				if ( $lead ) {
					AIADN_Mailer::send_colleague_joined( $lead['email'], $email, $school['name'] );
				}
			}
		}
		if ( ! $member ) {
			self::redirect( 'join', array( 'msg' => 'nomatch' ) );
		}
		AIADN_Schools::mark_member_verified( (int) $member['id'] );
		AIADN_Auth::start_session( array( 'member_id' => (int) $member['id'], 'school_id' => (int) $school['id'], 'role' => (string) $member['role'] ), AIADN_Auth::SESSION_TTL );
		if ( self::carried_invite() ) {
			self::redirect( 'invite', array( 't' => self::carried_invite() ) );
		}
		self::redirect( 'school' );
	}

	private static function render_student_landing(): string {
		self::redirect( 'survey' );
		$session = AIADN_Auth::current();
		if ( ! $session || 'student' !== $session['role'] ) {
			self::redirect( 'join', array( 'msg' => 'nomatch' ) );
		}
		$h  = '<h1>You are in</h1>';
		$h .= '<p>You have joined the conversation for <strong>' . esc_html( $session['school']['name'] ) . '</strong>.</p>';
		$h .= '<p>The Student Voice survey starts here in the next build.</p>';
		return $h;
	}

	/* ------------------------------------------------------------------ */
	/* /conversation/register/                                             */
	/* ------------------------------------------------------------------ */

	private static function view_register(): string {
		self::$title = 'Register your school';
		$values      = array( 'inv' => self::carried_invite() );
		$errors      = array();

		if ( self::is_post() ) {
			$action = self::post( 'aiadn_action' );
			if ( 'verify' === $action ) {
				self::handle_verify( 'register' );
			} elseif ( 'register' === $action ) {
				list( $values, $errors ) = self::handle_register();
			}
		}

		if ( 'code' === self::get( 'step' ) ) {
			return self::render_code_form( 'register', self::get( 'ref' ), self::message( self::get( 'msg' ) ) );
		}
		return self::render_register_form( $values, $errors );
	}

	/** @return array{0:array,1:array} values and errors when validation fails; redirects on success. */
	private static function handle_register(): array {
		$values = array(
			'name'         => self::post( 'school_name' ),
			'postcode'     => strtoupper( self::post( 'postcode' ) ),
			'mat_name'     => self::post( 'mat_name' ),
			'teacher_name' => self::post( 'teacher_name' ),
			'job_title'    => self::post( 'job_title' ),
			'email'        => AIADN_Util::normalise_email( self::post( 'email' ) ),
			'slt_email'    => AIADN_Util::normalise_email( self::post( 'slt_email' ) ),
			'partner_ref'  => self::post( 'partner_ref' ),
			'age_phases'   => array(),
			'agree'        => '' !== self::post( 'agree' ),
			'inv'          => self::carried_invite(),
		);
		$posted_phases = isset( $_POST['age_phases'] ) && is_array( $_POST['age_phases'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['age_phases'] ) ) : array(); // phpcs:ignore WordPress.Security
		$values['age_phases'] = array_values( array_intersect( array( 'primary', 'secondary', 'post16' ), $posted_phases ) );

		// Generous, because a trust may register many schools from one office. The hidden field does the real work.
		if ( ! AIADN_Util::allow( 'reg|' . AIADN_Util::client_ip(), 60, HOUR_IN_SECONDS ) ) {
			return array( $values, array( 'form' => 'Too many registrations from this connection. Please try again in a while.' ) );
		}
		if ( self::honeypot_tripped() ) {
			return array( $values, array( 'form' => 'Something went wrong. Please try again.' ) );
		}

		$errors = array();
		if ( strlen( $values['name'] ) < 3 ) {
			$errors['school_name'] = 'Enter your school name.';
		}
		if ( ! preg_match( '/^[A-Za-z0-9 ]{3,10}$/', $values['postcode'] ) ) {
			$errors['postcode'] = 'Enter your school postcode.';
		}
		if ( ! $values['age_phases'] ) {
			$errors['age_phases'] = 'Choose at least one age phase.';
		}
		if ( strlen( $values['teacher_name'] ) < 2 ) {
			$errors['teacher_name'] = 'Enter your name.';
		}
		if ( ! is_email( $values['email'] ) ) {
			$errors['email'] = 'Enter your school email address.';
		}
		if ( ! is_email( $values['slt_email'] ) ) {
			$errors['slt_email'] = 'Enter a headteacher or senior leader email address.';
		} elseif ( $values['slt_email'] === $values['email'] ) {
			$errors['slt_email'] = 'This needs to be someone else, so they can approve your school.';
		}
		if ( ! $values['agree'] ) {
			$errors['agree'] = 'Please agree to the Code of Conduct.';
		}
		if ( $errors ) {
			return array( $values, $errors );
		}

		$invite_debate = null;
		if ( '' !== $values['inv'] ) {
			$tok           = AIADN_Auth::find_token( $values['inv'], 'debate_invite' );
			$invite_debate = $tok ? AIADN_Debates::get( (int) $tok['ref_id'] ) : null;
			if ( ! $invite_debate || 'awaiting_opponent' !== $invite_debate['status'] ) {
				return array( $values, array( 'form' => 'That invitation has expired or has already been taken. Ask the other school to send a new one.' ) );
			}
		}

		$result = AIADN_Schools::register(
			array(
				'name'         => $values['name'],
				'postcode'     => $values['postcode'],
				'age_phases'   => implode( ',', $values['age_phases'] ),
				'mat_name'     => $values['mat_name'],
				'partner_ref'  => $values['partner_ref'],
				'slt_email'    => $values['slt_email'],
				'teacher_name' => $values['teacher_name'],
				'job_title'    => $values['job_title'],
				'email'        => $values['email'],
			)
		);
		if ( is_wp_error( $result ) ) {
			$msg = 'exists' === $result->get_error_code()
				? 'This school is already registered. Use the front door with your school code, or ask a colleague to share it.'
				: 'We could not save your registration. Please try again.';
			return array( $values, array( 'form' => $msg ) );
		}

		if ( $invite_debate ) {
			$lead_row = AIADN_Schools::lead( (int) $result );
			AIADN_Debates::claim_slot( (int) $invite_debate['id'], (int) $result, $lead_row ? (int) $lead_row['id'] : 0, 'awaiting_b_approval' );
		}

		list( $ref, $code ) = AIADN_Auth::issue_code( (int) $result, $values['email'], 'register', 'lead' );
		AIADN_Mailer::send_code( $values['email'], $code );
		self::remember_mask( $ref, $values['email'] );
		self::redirect( 'register', array( 'step' => 'code', 'ref' => $ref ) );
		return array( $values, array() ); // Unreachable; redirect exits.
	}

	private static function field_error( array $errors, string $key ): string {
		return isset( $errors[ $key ] ) ? '<p class="aiadn__error" role="alert">' . esc_html( $errors[ $key ] ) . '</p>' : '';
	}

	private static function render_register_form( array $v, array $errors ): string {
		$val = static fn( string $k ) => esc_attr( (string) ( $v[ $k ] ?? '' ) );
		$h   = '<h1>Register your school</h1>';
		$h  .= '<p>Register once. Colleagues, students and judges then use your school code.</p>';
		if ( ! empty( $v['inv'] ) ) {
			$h .= '<p class="aiadn__notice aiadn__notice--info">You are registering to accept a debate invitation. Once your headteacher approves your school, the debate is confirmed.</p>';
		}
		if ( isset( $errors['form'] ) ) {
			$h .= self::notice( $errors['form'], 'error' );
		}
		$h .= '<form method="post" action="' . esc_url( self::url( 'register' ) ) . '" class="aiadn__form" novalidate>';
		$h .= '<input type="hidden" name="aiadn_action" value="register">' . self::honeypot();
		if ( ! empty( $v['inv'] ) ) {
			$h .= '<input type="hidden" name="inv" value="' . esc_attr( $v['inv'] ) . '">';
		}

		$h .= '<label for="r-school">School name</label><input id="r-school" name="school_name" type="text" value="' . $val( 'name' ) . '" required>' . self::field_error( $errors, 'school_name' );
		$h .= '<label for="r-pc">School postcode</label><input id="r-pc" name="postcode" type="text" value="' . $val( 'postcode' ) . '" maxlength="10" autocomplete="postal-code" required>' . self::field_error( $errors, 'postcode' );

		$h .= '<fieldset class="aiadn__roles"><legend>Age phase</legend>';
		foreach ( array( 'primary' => 'Primary', 'secondary' => 'Secondary', 'post16' => 'Post-16' ) as $key => $label ) {
			$checked = in_array( $key, (array) ( $v['age_phases'] ?? array() ), true ) ? ' checked' : '';
			$h      .= '<label class="aiadn__radio"><input type="checkbox" name="age_phases[]" value="' . esc_attr( $key ) . '"' . $checked . '> ' . esc_html( $label ) . '</label>';
		}
		$h .= '</fieldset>' . self::field_error( $errors, 'age_phases' );

		$h .= '<label for="r-mat">Multi-academy trust <span class="aiadn__opt">(optional)</span></label><input id="r-mat" name="mat_name" type="text" value="' . $val( 'mat_name' ) . '">';
		$h .= '<label for="r-name">Your name</label><input id="r-name" name="teacher_name" type="text" value="' . $val( 'teacher_name' ) . '" autocomplete="name" required>' . self::field_error( $errors, 'teacher_name' );
		$h .= '<label for="r-role">Your role <span class="aiadn__opt">(optional)</span></label><input id="r-role" name="job_title" type="text" value="' . $val( 'job_title' ) . '" placeholder="Class teacher">';
		$h .= '<label for="r-email">School email</label><input id="r-email" name="email" type="email" value="' . $val( 'email' ) . '" autocomplete="email" required>' . self::field_error( $errors, 'email' );
		$h .= '<label for="r-slt">Headteacher or SLT email <span class="aiadn__opt">(to approve your school)</span></label><input id="r-slt" name="slt_email" type="email" value="' . $val( 'slt_email' ) . '" required>' . self::field_error( $errors, 'slt_email' );
		$h .= '<label for="r-partner">Did a partner introduce you? <span class="aiadn__opt">(optional)</span></label><input id="r-partner" name="partner_ref" type="text" value="' . $val( 'partner_ref' ) . '" placeholder="e.g. Apps for Good">';
		$h .= '<p class="aiadn__small">When a debate is finished, the school names, theme, motion and winner are shown on a public results page. Teacher and student details never are, and the school code is never shown.</p>';
		$h .= '<label class="aiadn__radio"><input type="checkbox" name="agree" value="1"' . ( ! empty( $v['agree'] ) ? ' checked' : '' ) . '> I agree to the Code of Conduct: challenge the argument, respect the person.</label>' . self::field_error( $errors, 'agree' );
		$h .= '<button type="submit" class="aiadn__button">Send me a code</button></form>';
		$h .= '<p class="aiadn__small">Already registered? <a href="' . esc_url( self::url( 'join' ) ) . '">Use the front door</a>.</p>';
		return $h;
	}

	public static function send_slt_email( array $school, array $lead ): void {
		$token = AIADN_Auth::issue_token( (int) $school['id'], 'slt_approve', AIADN_Auth::SLT_TOKEN_TTL );
		$url   = self::url( 'approve', array( 't' => $token ) );
		AIADN_Mailer::send_slt_approval( $school['slt_email'], $lead['name'], $lead['job_title'], $school['name'], $url );
	}

	/* ------------------------------------------------------------------ */
	/* /conversation/approve/  SLT                                         */
	/* ------------------------------------------------------------------ */

	private static function view_invite(): string {
		return AIADN_Debate_Front::view_invite();
	}

	private static function view_debate(): string {
		return AIADN_Debate_Front::view_debate();
	}

	private static function view_judge(): string {
		return AIADN_Debate_Front::view_judge();
	}

	private static function view_approve(): string {
		self::$title = 'Approve a school';

		if ( self::is_post() && 'slt_decide' === self::post( 'aiadn_action' ) ) {
			return self::handle_slt_decision();
		}

		$token = AIADN_Auth::find_token( self::get( 't' ), 'slt_approve' );
		if ( ! $token ) {
			return '<h1>This link is no longer valid</h1><p>It may have expired or already been used. Ask the teacher to send it again from their school page.</p>';
		}
		$school = AIADN_Schools::get( (int) $token['school_id'] );
		$lead   = $school ? AIADN_Schools::lead( (int) $school['id'] ) : null;
		if ( ! $school || ! $lead ) {
			return '<h1>This link is no longer valid</h1>';
		}

		// Looking is free. Only the button changes anything.
		$who = $lead['name'] . ( '' !== $lead['job_title'] ? ', ' . $lead['job_title'] : '' );
		$h   = '<h1>Approve ' . esc_html( $school['name'] ) . '?</h1>';
		$h  .= '<p>Teacher: <strong>' . esc_html( $who ) . '</strong></p>';
		$h  .= '<p>Approving lets this school invite others to take part and run debates. Students, colleagues and judges still need a class PIN or their own emailed code.</p>';
		$h  .= '<form method="post" action="' . esc_url( self::url( 'approve' ) ) . '" class="aiadn__form aiadn__form--row">';
		$h  .= '<input type="hidden" name="aiadn_action" value="slt_decide"><input type="hidden" name="t" value="' . esc_attr( self::get( 't' ) ) . '">';
		$h  .= '<button type="submit" name="decision" value="approve" class="aiadn__button">Approve</button> ';
		$h  .= '<button type="submit" name="decision" value="reject" class="aiadn__button aiadn__button--quiet">This isn\'t right</button></form>';
		return $h;
	}

	private static function handle_slt_decision(): string {
		if ( ! AIADN_Util::allow( 'slt|' . AIADN_Util::client_ip(), 30, 600 ) ) {
			return '<h1>Please wait</h1><p>' . esc_html( self::message( 'slow' ) ) . '</p>';
		}
		$token = AIADN_Auth::find_token( self::post( 't' ), 'slt_approve' );
		$done  = '<h1>This link is no longer valid</h1><p>It may have expired or already been used.</p>';
		if ( ! $token || ! AIADN_Auth::consume_token( (int) $token['id'] ) ) {
			return $done;
		}
		$school = AIADN_Schools::get( (int) $token['school_id'] );
		if ( ! $school ) {
			return $done;
		}

		if ( 'reject' === self::post( 'decision' ) ) {
			AIADN_Schools::set_status( (int) $school['id'], 'rejected' );
			return '<h1>Thank you</h1><p>We will not go ahead with this registration.</p>';
		}

		AIADN_Schools::set_status( (int) $school['id'], 'approved', array( 'slt_approved_at' => AIADN_Util::now() ) );
		if ( ! AIADN_Schools::find_member( (int) $school['id'], $school['slt_email'] ) ) {
			AIADN_Schools::add_member( (int) $school['id'], $school['slt_email'], 'Senior leader', '', 'slt', true );
		}
		AIADN_Debates::on_school_approved( (int) $school['id'] );
		$lead = AIADN_Schools::lead( (int) $school['id'] );
		if ( $lead ) {
			AIADN_Mailer::send_school_approved( $lead['email'], $school['name'], (string) $school['code'], self::url( 'join', array( 'c' => $school['code'] ) ) );
		}

		$h  = '<h1>Approved</h1>';
		$h .= '<p>' . esc_html( $school['name'] ) . ' can now take part.</p>';
		$h .= '<p>Your school code is</p><p class="aiadn__bigcode">' . esc_html( (string) $school['code'] ) . '</p>';
		$h .= '<p>To see your school, go to <a href="' . esc_url( self::url( 'join' ) ) . '">the front door</a>, choose Headteacher / SLT, and enter the code and this email address.</p>';
		return $h;
	}

	/* ------------------------------------------------------------------ */
	/* /conversation/school/  dashboard                                    */
	/* ------------------------------------------------------------------ */

	private static function view_school(): string {
		self::$title = 'Your school';
		$session     = AIADN_Auth::current();
		if ( ! $session ) {
			self::redirect( 'join', array( 'msg' => 'signin' ) );
		}
		if ( 'student' === $session['role'] ) {
			self::redirect( 'survey' );
		}
		if ( 'judge' === $session['role'] ) {
			self::redirect( 'judge' );
		}

		if ( self::is_post() ) {
			if ( ! AIADN_Auth::csrf_ok( self::post( 'csrf' ) ) ) {
				self::redirect( 'school' );
			}
			$action = self::post( 'aiadn_action' );
			$school = $session['school'];
			if ( 'new_debate' === $action && 'approved' === $school['status'] && in_array( $session['role'], array( 'lead', 'teacher' ), true ) ) {
				if ( AIADN_Util::allow( 'newdebate|' . $school['id'], 20, HOUR_IN_SECONDS ) ) {
					$debate = AIADN_Debates::create( (int) $school['id'], (int) $session['member_id'] );
					if ( $debate ) {
						wp_safe_redirect( AIADN_Debates::url( $debate ) );
						exit;
					}
				}
				self::redirect( 'school', array( 'msg' => 'slow' ) );
			}
			if ( 'logout' === $action ) {
				AIADN_Auth::end_session();
				self::redirect( 'join', array( 'msg' => 'signedout' ) );
			}
			if ( 'new_pin' === $action && 'approved' === $school['status'] && in_array( $session['role'], array( 'lead', 'teacher' ), true ) ) {
				$purpose   = self::post( 'pin_purpose' );
				$debate_id = 0;
				if ( in_array( $purpose, array( 'before', 'after' ), true ) ) {
					$chosen = AIADN_Debates::get_by_code( AIADN_Util::normalise_debate_code( self::post( 'pin_debate' ) ) );
					if ( $chosen && AIADN_Debates::involves( $chosen, (int) $school['id'] ) && in_array( $chosen['status'], array( 'agreed', 'ready', 'completed' ), true ) ) {
						$debate_id = (int) $chosen['id'];
					} else {
						$purpose = 'general'; // No valid debate chosen: an ordinary survey.
					}
				} else {
					$purpose = 'general';
				}
				AIADN_Auth::new_pin( (int) $school['id'], (int) $session['member_id'], $purpose, $debate_id );
				self::redirect( 'school', array( 'msg' => 'pin' ) );
			}
			if ( 'colleague_decide' === $action && 'approved' === $school['status'] && 'lead' === $session['role'] ) {
				AIADN_Colleague_Front::decide_from_dashboard( $school, (int) self::post( 'member_id' ), 'approve' === self::post( 'decision' ) );
				self::redirect( 'school', array( 'msg' => 'colleague' ) );
			}
			if ( 'resend_slt' === $action && 'pending_slt' === $school['status'] && 'lead' === $session['role'] ) {
				if ( AIADN_Util::allow( 'resend|' . $school['id'], 3, HOUR_IN_SECONDS ) ) {
					self::send_slt_email( $school, $session['member'] );
				}
				self::redirect( 'school', array( 'msg' => 'resent' ) );
			}
			self::redirect( 'school' );
		}

		return self::render_dashboard( $session );
	}

	private static function render_dashboard( array $session ): string {
		$school = $session['school'];
		$member = $session['member'];
		$role   = $session['role'];
		$status = $school['status'];

		$h  = '<h1>' . esc_html( $school['name'] ) . '</h1>';
		$h .= '<p class="aiadn__meta">Signed in as ' . esc_html( '' !== $member['name'] ? $member['name'] : $member['email'] ) . ' (' . esc_html( 'slt' === $role ? 'senior leader' : $role ) . ')</p>';
		$h .= self::notice( self::message( self::get( 'msg' ) ), 'info' );

		if ( self::get( 'welcome' ) && 'pending_slt' === $status ) {
			$h .= '<div class="aiadn__panel aiadn__panel--ink"><h2>You are verified</h2>';
			$h .= '<p>Your school code is</p><p class="aiadn__bigcode">' . esc_html( (string) $school['code'] ) . '</p>';
			$h .= '<p>Everyone at your school uses this: students, colleagues, your headteacher and judges. It is a reference, not a password. Each person also needs a class PIN or an emailed code.</p></div>';
		}

		if ( 'pending_slt' === $status ) {
			$h .= '<div class="aiadn__panel"><h2>Waiting for SLT approval</h2>';
			$h .= '<p>We have emailed <strong>' . esc_html( $school['slt_email'] ) . '</strong>. Once they approve, you can run the class PIN and invite another school.</p>';
			if ( 'lead' === $role ) {
				$h .= '<form method="post" action="' . esc_url( self::url( 'school' ) ) . '">' . self::csrf_field() . '<input type="hidden" name="aiadn_action" value="resend_slt"><button class="aiadn__button aiadn__button--quiet" type="submit">Send the approval email again</button></form>';
			}
			$h .= '</div>';
		} elseif ( 'approved' === $status ) {
			// Any online debate that is on now: the join button, right at the top.
			foreach ( AIADN_Debates::for_school( (int) $school['id'] ) as $d ) {
				$h .= AIADN_Debate_Front::join_panel( $d, 'school', self::url( 'calendar', array( 'd' => $d['code'] ) ), self::url( 'paper', array( 'd' => $d['code'] ) ), self::url( 'prep', array( 'd' => $d['code'] ) ) );
			}
			$h .= '<div class="aiadn__panel aiadn__panel--ink"><h2>School code</h2><p class="aiadn__bigcode">' . esc_html( (string) $school['code'] ) . '</p>';
			$h .= '<p>Share it with students, colleagues, your headteacher and judges. Each also needs a class PIN or their own email code.</p>';
			$h .= '<p class="aiadn__small">Front door: <a href="' . esc_url( self::url( 'join', array( 'c' => $school['code'] ) ) ) . '">' . esc_html( self::url( 'join', array( 'c' => $school['code'] ) ) ) . '</a></p></div>';

			if ( in_array( $role, array( 'lead', 'teacher' ), true ) ) {
				$pin = AIADN_Auth::current_pin( (int) $school['id'] );
				$h  .= '<div class="aiadn__panel aiadn__panel--ink"><h2>Class PIN for students</h2>';
				if ( $pin ) {
					$h .= '<p class="aiadn__bigcode aiadn__bigcode--pin">' . esc_html( $pin['pin'] ) . '</p><p>Works until about ' . esc_html( self::local_time( $pin['expires_at'] ) ) . '. Starting a new PIN stops this one.</p>';
					if ( 'general' !== $pin['purpose'] ) {
						$linked = AIADN_Debates::get( (int) $pin['debate_id'] );
						$h     .= '<p class="aiadn__small">For the survey ' . esc_html( strtolower( AIADN_Voice::PHASES[ $pin['purpose'] ] ) ) . ( $linked ? ' ' . esc_html( $linked['code'] ) : '' ) . '.</p>';
					}
					$h .= '<p><a class="aiadn__button" href="' . esc_url( self::url( 'board' ) ) . '">Show on the whiteboard</a></p>';
				} else {
					$h .= '<p>No PIN is running. Start one when the lesson begins and show it on the board.</p>';
				}
				$h .= '<form method="post" action="' . esc_url( self::url( 'school' ) ) . '" class="aiadn__form">' . self::csrf_field() . '<input type="hidden" name="aiadn_action" value="new_pin">';
				$h .= '<label for="pin-purpose">What is this PIN for?</label><select id="pin-purpose" name="pin_purpose">';
				foreach ( AIADN_Voice::PHASES as $key => $label ) {
					$h .= '<option value="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</option>';
				}
				$h .= '</select>';
				$pickable = array_filter( AIADN_Debates::for_school( (int) $school['id'] ), static fn( $d ) => in_array( $d['status'], array( 'agreed', 'ready', 'completed' ), true ) );
				if ( $pickable ) {
					$h .= '<label for="pin-debate">Which debate? <span class="aiadn__opt">(for before or after)</span></label><select id="pin-debate" name="pin_debate"><option value="">Choose...</option>';
					foreach ( $pickable as $d ) {
						$h .= '<option value="' . esc_attr( $d['code'] ) . '">' . esc_html( $d['code'] . ' v ' . ( AIADN_Schools::get( AIADN_Debates::other_school_id( $d, (int) $school['id'] ) )['name'] ?? '' ) ) . '</option>';
					}
					$h .= '</select>';
				}
				$h .= '<button class="aiadn__button' . ( $pin ? ' aiadn__button--quiet' : '' ) . '" type="submit">' . ( $pin ? 'Start a new PIN' : 'Start a class PIN' ) . '</button></form>';
				$count = AIADN_Voice::count( (int) $school['id'] );
				$h    .= '<p class="aiadn__small"><a href="' . esc_url( self::url( 'voice' ) ) . '">Student Voice results</a>: ' . (int) $count . ' response' . ( 1 === $count ? '' : 's' ) . ( $count < AIADN_Voice::MIN_RESPONSES ? ', results unlock at ' . (int) AIADN_Voice::MIN_RESPONSES : '' ) . '.</p></div>';
			}

			$h .= self::render_debates_panel( $session );
			if ( AIADN_Debates::for_school( (int) $school['id'] ) ) {
				$h .= '<p><a class="aiadn__button aiadn__button--quiet" href="' . esc_url( self::url( 'results' ) ) . '">Results and certificate</a></p>';
			}
			$h .= '<p><a class="aiadn__button aiadn__button--quiet" href="' . esc_url( self::url( 'snapshot' ) ) . '">School AI Snapshot</a></p>';

			if ( 'lead' === $role ) {
				$h .= AIADN_Colleague_Front::pending_panel( (int) $school['id'] );
				$h .= '<div class="aiadn__panel"><h2>Team</h2><table class="aiadn__table"><thead><tr><th scope="col">Name</th><th scope="col">Email</th><th scope="col">Role</th></tr></thead><tbody>';
				foreach ( AIADN_Schools::members( (int) $school['id'] ) as $m ) {
					$h .= '<tr><td>' . esc_html( $m['name'] ?: 'Colleague' ) . '</td><td>' . esc_html( $m['email'] ) . '</td><td>' . esc_html( 'slt' === $m['role'] ? 'SLT' : ( 'pending' === $m['role'] ? 'Waiting for approval' : $m['role'] ) ) . '</td></tr>';
				}
				$h .= '</tbody></table></div>';
			}
		} else {
			$h .= '<div class="aiadn__panel"><p>This registration is not active.</p></div>';
		}

		$h .= '<form method="post" action="' . esc_url( self::url( 'school' ) ) . '" class="aiadn__signout">' . self::csrf_field() . '<input type="hidden" name="aiadn_action" value="logout"><button class="aiadn__link" type="submit">Sign out</button></form>';
		return $h;
	}

	private static function render_debates_panel( array $session ): string {
		$school = $session['school'];
		$h      = '<div class="aiadn__panel"><h2>Debates</h2>';
		$debates = AIADN_Debates::for_school( (int) $school['id'] );
		if ( ! $debates ) {
			$h .= '<p>No debates yet. Start one, then invite another school.</p>';
		} else {
			$h .= '<table class="aiadn__table"><thead><tr><th scope="col">Debate</th><th scope="col">Against</th><th scope="col">Where it is</th></tr></thead><tbody>';
			foreach ( $debates as $d ) {
				$other_id = AIADN_Debates::other_school_id( $d, (int) $school['id'] );
				$other    = $other_id ? AIADN_Schools::get( $other_id ) : null;
				$h       .= '<tr><td><a href="' . esc_url( AIADN_Debates::url( $d ) ) . '">' . esc_html( $d['code'] ) . '</a></td><td>' . esc_html( $other ? $other['name'] : 'Not yet chosen' ) . '</td><td>' . esc_html( AIADN_Debates::STATUS_LABELS[ $d['status'] ] ?? $d['status'] ) . '</td></tr>';
			}
			$h .= '</tbody></table>';
		}
		if ( in_array( $session['role'], array( 'lead', 'teacher' ), true ) ) {
			$h .= '<form method="post" action="' . esc_url( self::url( 'school' ) ) . '">' . self::csrf_field() . '<input type="hidden" name="aiadn_action" value="new_debate"><button class="aiadn__button" type="submit">Start a new debate</button></form>';
		}
		return $h . '</div>';
	}
}
