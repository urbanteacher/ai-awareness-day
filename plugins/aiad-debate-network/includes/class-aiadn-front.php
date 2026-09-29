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

	const VIEWS = array( 'join', 'register', 'approve', 'school' );

	/** @var string */
	private static $title = 'National AI Conversation';

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

		$html = call_user_func( array( __CLASS__, 'view_' . $view ) );
		self::output( $html );
	}

	private static function output( string $inner ): void {
		add_filter( 'pre_get_document_title', static fn() => self::$title . ' | AI Awareness Day' );
		add_filter( 'wp_robots', static fn( $r ) => array( 'noindex' => true, 'nofollow' => true ) );
		wp_enqueue_style( 'aiadn', AIADN_PLUGIN_URL . 'public/aiadn.css', array(), AIADN_VERSION );
		get_header();
		echo '<main id="main" class="aiadn"><div class="aiadn__card">';
		echo '<p class="aiadn__eyebrow">National AI Conversation</p>';
		echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts below.
		echo '</div></main>';
		get_footer();
		exit;
	}

	private static function redirect( string $view, array $args = array() ): void {
		wp_safe_redirect( self::url( $view, $args ) );
		exit;
	}

	/* ------------------------------------------------------------------ */
	/* Small helpers                                                       */
	/* ------------------------------------------------------------------ */

	private static function is_post(): bool {
		return isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'];
	}

	private static function post( string $key ): string {
		return isset( $_POST[ $key ] ) && is_string( $_POST[ $key ] ) ? trim( sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	}

	private static function get( string $key ): string {
		return isset( $_GET[ $key ] ) && is_string( $_GET[ $key ] ) ? trim( sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	}

	private static function message( string $key ): string {
		$messages = array(
			'nomatch'   => "We couldn't match those details.",
			'slow'      => 'Too many attempts. Please wait a few minutes and try again.',
			'wrongcode' => "That code didn't work. Check it and try again, or start again.",
			'signin'    => 'Please sign in with your school code.',
			'signedout' => 'You are signed out.',
			'pin'       => 'A new class PIN has started. The old one no longer works.',
			'resent'    => 'We have sent the approval email again.',
		);
		return $messages[ $key ] ?? '';
	}

	private static function notice( string $text, string $type = 'info' ): string {
		return '' === $text ? '' : '<p class="aiadn__notice aiadn__notice--' . esc_attr( $type ) . '" role="status">' . esc_html( $text ) . '</p>';
	}

	private static function local_time( string $utc ): string {
		return wp_date( 'H:i', strtotime( $utc . ' UTC' ) );
	}

	private static function honeypot(): string {
		return '<div class="aiadn__hp" aria-hidden="true"><label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>';
	}

	private static function honeypot_tripped(): bool {
		return '' !== self::post( 'website' );
	}

	private static function csrf_field(): string {
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

	private static function render_front_door( string $error, string $prefill_code ): string {
		$h  = '<h1>Enter your school code</h1>';
		$h .= self::notice( $error, 'error' );
		$h .= '<form method="post" action="' . esc_url( self::url( 'join' ) ) . '" class="aiadn__form" novalidate>';
		$h .= '<input type="hidden" name="aiadn_action" value="front_door">';
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
		$h .= '<p class="aiadn__small">New school? <a href="' . esc_url( self::url( 'register' ) ) . '">Register here</a>. Lost your code? Ask your teacher or headteacher.</p>';
		$h .= '<script>(function(){var f=document.querySelector(".aiadn__form");if(!f)return;function s(){var r=f.querySelector("input[name=role]:checked");var st=r&&r.value==="student";f.querySelector("[data-for=pin]").style.display=st?"":"none";f.querySelector("[data-for=email]").style.display=st?"none":"";}f.addEventListener("change",s);s();})();</script>';
		return $h;
	}

	private static function handle_front_door(): void {
		$ip = AIADN_Util::client_ip();
		if ( ! AIADN_Util::allow( 'fd|' . $ip, 30, 600 ) ) {
			self::redirect( 'join', array( 'msg' => 'slow' ) );
		}
		if ( self::honeypot_tripped() ) {
			self::redirect( 'join', array( 'msg' => 'nomatch' ) );
		}

		$code   = AIADN_Util::normalise_school_code( self::post( 'school_code' ) );
		$role   = self::post( 'role' );
		$school = $code ? AIADN_Schools::get_by_code( $code ) : null;

		if ( 'student' === $role ) {
			// Guessing PINs is the only real attack here: 8 tries per school code per 10 minutes.
			if ( ! AIADN_Util::allow( 'pin|' . $code . '|' . $ip, 8, 600 ) ) {
				self::redirect( 'join', array( 'msg' => 'slow' ) );
			}
			if ( $school && 'approved' === $school['status'] && AIADN_Auth::pin_matches( (int) $school['id'], self::post( 'pin' ) ) ) {
				AIADN_Auth::start_session( array( 'member_id' => 0, 'school_id' => (int) $school['id'], 'role' => 'student' ), AIADN_Auth::STUDENT_TTL );
				self::redirect( 'join', array( 'step' => 'student' ) );
			}
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
		$ref = self::send_or_decoy( $school, $email, 'signin', $role, $eligible );
		self::redirect( 'join', array( 'step' => 'code', 'ref' => $ref ) );
	}

	private static function render_code_form( string $view, string $ref, string $error ): string {
		$ref  = preg_replace( '/[^a-f0-9]/', '', strtolower( $ref ) );
		$mask = get_transient( 'aiadn_mask_' . $ref );
		$h    = '<h1>Check your email</h1>';
		$h   .= '<p>If those details match, we have sent a 6-digit code' . ( $mask ? ' to <strong>' . esc_html( $mask ) . '</strong>' : '' ) . '.</p>';
		$h   .= self::notice( $error, 'error' );
		$h   .= '<form method="post" action="' . esc_url( self::url( $view ) ) . '" class="aiadn__form">';
		$h   .= '<input type="hidden" name="aiadn_action" value="verify"><input type="hidden" name="ref" value="' . esc_attr( $ref ) . '">';
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
		self::redirect( 'school' );
	}

	private static function render_student_landing(): string {
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
		$values      = array();
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
		if ( isset( $errors['form'] ) ) {
			$h .= self::notice( $errors['form'], 'error' );
		}
		$h .= '<form method="post" action="' . esc_url( self::url( 'register' ) ) . '" class="aiadn__form" novalidate>';
		$h .= '<input type="hidden" name="aiadn_action" value="register">' . self::honeypot();

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
		$h .= '<label class="aiadn__radio"><input type="checkbox" name="agree" value="1"' . ( ! empty( $v['agree'] ) ? ' checked' : '' ) . '> I agree to the Code of Conduct: challenge the argument, respect the person.</label>' . self::field_error( $errors, 'agree' );
		$h .= '<button type="submit" class="aiadn__button">Send me a code</button></form>';
		$h .= '<p class="aiadn__small">Already registered? <a href="' . esc_url( self::url( 'join' ) ) . '">Use the front door</a>.</p>';
		return $h;
	}

	private static function send_slt_email( array $school, array $lead ): void {
		$token = AIADN_Auth::issue_token( (int) $school['id'], 'slt_approve', AIADN_Auth::SLT_TOKEN_TTL );
		$url   = self::url( 'approve', array( 't' => $token ) );
		AIADN_Mailer::send_slt_approval( $school['slt_email'], $lead['name'], $lead['job_title'], $school['name'], $url );
	}

	/* ------------------------------------------------------------------ */
	/* /conversation/approve/  SLT                                         */
	/* ------------------------------------------------------------------ */

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
			self::redirect( 'join', array( 'step' => 'student' ) );
		}

		if ( self::is_post() ) {
			if ( ! AIADN_Auth::csrf_ok( self::post( 'csrf' ) ) ) {
				self::redirect( 'school' );
			}
			$action = self::post( 'aiadn_action' );
			$school = $session['school'];
			if ( 'logout' === $action ) {
				AIADN_Auth::end_session();
				self::redirect( 'join', array( 'msg' => 'signedout' ) );
			}
			if ( 'new_pin' === $action && 'approved' === $school['status'] && in_array( $session['role'], array( 'lead', 'teacher' ), true ) ) {
				AIADN_Auth::new_pin( (int) $school['id'], (int) $session['member_id'] );
				self::redirect( 'school', array( 'msg' => 'pin' ) );
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
			$h .= '<div class="aiadn__panel"><h2>You are verified</h2>';
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
			$h .= '<div class="aiadn__panel"><h2>School code</h2><p class="aiadn__bigcode">' . esc_html( (string) $school['code'] ) . '</p>';
			$h .= '<p>Share it with students, colleagues, your headteacher and judges. Each also needs a class PIN or their own email code.</p>';
			$h .= '<p class="aiadn__small">Front door: <a href="' . esc_url( self::url( 'join', array( 'c' => $school['code'] ) ) ) . '">' . esc_html( self::url( 'join', array( 'c' => $school['code'] ) ) ) . '</a></p></div>';

			if ( in_array( $role, array( 'lead', 'teacher' ), true ) ) {
				$pin = AIADN_Auth::current_pin( (int) $school['id'] );
				$h  .= '<div class="aiadn__panel"><h2>Class PIN for students</h2>';
				if ( $pin ) {
					$h .= '<p class="aiadn__bigcode aiadn__bigcode--pin">' . esc_html( $pin['pin'] ) . '</p><p>Works until about ' . esc_html( self::local_time( $pin['expires_at'] ) ) . '. Starting a new PIN stops this one.</p>';
				} else {
					$h .= '<p>No PIN is running. Start one when the lesson begins and show it on the board.</p>';
				}
				$h .= '<form method="post" action="' . esc_url( self::url( 'school' ) ) . '">' . self::csrf_field() . '<input type="hidden" name="aiadn_action" value="new_pin"><button class="aiadn__button" type="submit">' . ( $pin ? 'Start a new PIN' : 'Start a class PIN' ) . '</button></form></div>';
			}

			if ( 'lead' === $role ) {
				$h .= '<div class="aiadn__panel"><h2>Team</h2><table class="aiadn__table"><thead><tr><th scope="col">Name</th><th scope="col">Email</th><th scope="col">Role</th></tr></thead><tbody>';
				foreach ( AIADN_Schools::members( (int) $school['id'] ) as $m ) {
					$h .= '<tr><td>' . esc_html( $m['name'] ?: 'Colleague' ) . '</td><td>' . esc_html( $m['email'] ) . '</td><td>' . esc_html( 'slt' === $m['role'] ? 'SLT' : $m['role'] ) . '</td></tr>';
				}
				$h .= '</tbody></table></div>';
			}
			$h .= '<p class="aiadn__small">Inviting another school and setting up a debate arrive in the next build.</p>';
		} else {
			$h .= '<div class="aiadn__panel"><p>This registration is not active.</p></div>';
		}

		$h .= '<form method="post" action="' . esc_url( self::url( 'school' ) ) . '" class="aiadn__signout">' . self::csrf_field() . '<input type="hidden" name="aiadn_action" value="logout"><button class="aiadn__link" type="submit">Sign out</button></form>';
		return $h;
	}
}
