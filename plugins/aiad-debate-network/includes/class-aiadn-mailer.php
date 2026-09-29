<?php
/**
 * Emails. Plain text, short, from a recognisable sender.
 *
 * Locally, AIADN_SMTP_HOST is set in the environment (docker-compose does) and every email goes to Mailpit.
 *
 * On the live site, put the email service's SMTP login in wp-config.php and this plugin's emails go through it
 * (the campaign uses Brevo, from info@aiawarenessday.co.uk):
 *
 *     define( 'AIADN_SMTP_HOST', 'smtp-relay.brevo.com' );
 *     define( 'AIADN_SMTP_PORT', 587 );
 *     define( 'AIADN_SMTP_USER', '...' );
 *     define( 'AIADN_SMTP_PASS', '...' );
 *     define( 'AIADN_MAIL_FROM', 'info@aiawarenessday.co.uk' );
 *     define( 'AIADN_TEAM_EMAIL', '...' );
 *
 * Only this plugin's own emails use these settings and its sender. The site's other emails (the contact form,
 * the benchmark certificates) keep whatever set-up they already have. The sending domain needs SPF, DKIM and
 * DMARC in the email service.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Mailer {

	public static function register(): void {
		add_action( 'phpmailer_init', array( __CLASS__, 'maybe_use_local_smtp' ) );
		add_filter( 'wp_mail_from_name', array( __CLASS__, 'from_name' ) );
		add_filter( 'wp_mail_from', array( __CLASS__, 'from_address' ) );
	}

	/**
	 * A valid, recognisable sender. WordPress's default (wordpress@localhost) is not a valid address
	 * and is rejected. In production, set AIADN_MAIL_FROM in wp-config.php to an address on the domain
	 * that carries the SPF/DKIM/DMARC records.
	 */
	public static function from_address( string $email ): string {
		if ( ! self::$sending ) {
			return $email; // Not one of ours: leave the site's other emails (contact form, certificates) as they are.
		}
		if ( defined( 'AIADN_MAIL_FROM' ) && is_email( AIADN_MAIL_FROM ) ) {
			return AIADN_MAIL_FROM;
		}
		$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
		$host = preg_replace( '/^www\./', '', $host );
		if ( false === strpos( $host, '.' ) ) {
			$host = 'aiawarenessday.co.uk';
		}
		return 'info@' . $host;
	}

	/** @var array{content:string,name:string}|null A calendar file to attach to the email being sent. */
	private static $ics = null;

	/** An email with a calendar invite (.ics) attached, which calendars open as an event. */
	public static function send_calendar( string $to, string $subject, string $body, string $ics, string $filename ): bool {
		self::$ics = array( 'content' => $ics, 'name' => $filename );
		$ok        = self::send( $to, $subject, $body );
		self::$ics = null;
		return $ok;
	}

	public static function maybe_use_local_smtp( $phpmailer ): void {
		if ( self::$ics ) {
			$phpmailer->addStringAttachment( self::$ics['content'], self::$ics['name'], 'base64', 'text/calendar; charset=utf-8; method=PUBLISH' );
		}
		$host = getenv( 'AIADN_SMTP_HOST' );
		if ( $host ) {
			// A local test inbox: no login, no encryption.
			$phpmailer->isSMTP();
			$phpmailer->Host        = $host;
			$phpmailer->Port        = (int) ( getenv( 'AIADN_SMTP_PORT' ) ?: 1025 );
			$phpmailer->SMTPAuth    = false;
			$phpmailer->SMTPAutoTLS = false;
			return;
		}
		if ( self::$sending ) {
			self::configure_smtp( $phpmailer );
		}
	}

	/** The email service's SMTP login from wp-config.php, for this plugin's own emails. Does nothing if it is not set. */
	public static function configure_smtp( $phpmailer ): void {
		if ( ! defined( 'AIADN_SMTP_HOST' ) || '' === (string) AIADN_SMTP_HOST ) {
			return;
		}
		$phpmailer->isSMTP();
		$phpmailer->Host       = (string) AIADN_SMTP_HOST;
		$phpmailer->Port       = defined( 'AIADN_SMTP_PORT' ) ? (int) AIADN_SMTP_PORT : 587;
		$phpmailer->SMTPSecure = defined( 'AIADN_SMTP_SECURE' ) ? (string) AIADN_SMTP_SECURE : ( 465 === $phpmailer->Port ? 'ssl' : 'tls' );
		$phpmailer->SMTPAuth   = defined( 'AIADN_SMTP_USER' ) && '' !== (string) AIADN_SMTP_USER;
		if ( $phpmailer->SMTPAuth ) {
			$phpmailer->Username = (string) AIADN_SMTP_USER;
			$phpmailer->Password = defined( 'AIADN_SMTP_PASS' ) ? (string) AIADN_SMTP_PASS : '';
		}
	}

	/**
	 * How this plugin's emails will go out, for the programme team's check. Never includes a login.
	 *
	 * @return array{kind:string,detail:string,from:string}
	 */
	public static function route(): array {
		self::$sending = true;
		$from = self::from_address( '' );
		self::$sending = false;
		if ( getenv( 'AIADN_SMTP_HOST' ) ) {
			return array( 'kind' => 'local test inbox', 'detail' => (string) getenv( 'AIADN_SMTP_HOST' ), 'from' => $from );
		}
		if ( defined( 'AIADN_SMTP_HOST' ) && '' !== (string) AIADN_SMTP_HOST ) {
			return array( 'kind' => 'SMTP from wp-config.php', 'detail' => (string) AIADN_SMTP_HOST, 'from' => $from );
		}
		return array( 'kind' => 'the site\'s default mail (usually SMTP plugin or the server)', 'detail' => '', 'from' => $from );
	}

	/** A test email to one address, so the programme team can check the set-up on the live site. */
	public static function send_test( string $to ): bool {
		return self::send( $to, 'Test email from the National AI Conversation', "This is a test. If it reached you, the platform's emails are going out.\n\nSent " . wp_date( 'j M Y, H:i' ) . '.' );
	}

	public static function from_name( string $name ): string {
		return self::$sending ? 'AI Awareness Day' : $name;
	}

	/** True only while one of this plugin's own emails is being sent. */
	private static $sending = false;

	private static function send( string $to, string $subject, string $body ): bool {
		$footer = "\n\n--\nAI Awareness Day 2027: National AI Conversation\nhttps://aiawarenessday.co.uk/";
		self::$sending = true;
		$ok            = (bool) wp_mail( $to, $subject, $body . $footer );
		self::$sending = false;
		return $ok;
	}

	public static function send_code( string $to, string $code ): bool {
		$body  = "Your AI Awareness Day code is:\n\n    {$code}\n\n";
		$body .= "Enter it on the page you were just on. It works once and expires in 15 minutes.\n\n";
		$body .= "If you did not ask for this, ignore this email. Nothing happens unless the code is entered.";
		return self::send( $to, 'Your AI Awareness Day code: ' . $code, $body );
	}

	public static function send_slt_approval( string $to, string $teacher_name, string $job_title, string $school_name, string $url ): bool {
		$who   = '' !== $job_title ? "{$teacher_name} ({$job_title})" : $teacher_name;
		$body  = "{$who} wants to register {$school_name} for the National AI Conversation.\n\n";
		$body .= "As a headteacher or senior leader, please confirm this is right:\n\n    {$url}\n\n";
		$body .= "The link opens a page with an Approve button. Nothing changes until you press it.\n\n";
		$body .= "If you do not recognise this, you can ignore the email or press \"This isn't right\" on that page.";
		return self::send( $to, 'Please confirm ' . $school_name, $body );
	}

	public static function send_school_approved( string $to, string $school_name, string $school_code, string $join_url ): bool {
		$body  = "{$school_name} has been approved by senior leadership.\n\n";
		$body .= "Your school code is:  {$school_code}\n\n";
		$body .= "Students, colleagues, your headteacher and judges all start here:\n\n    {$join_url}\n\n";
		$body .= "The code shows which school. Each person also needs a class PIN or their own emailed code, so it is safe to display on a whiteboard.";
		return self::send( $to, $school_name . ' is approved', $body );
	}

	/** The headteacher's copy: the code, and how to come back. They are signed in on the device they approved from. */
	public static function send_school_approved_slt( string $to, string $school_name, string $school_code, string $join_url ): bool {
		$body  = "You have approved {$school_name}. Thank you.\n\n";
		$body .= "Your school code is:  {$school_code}\n\n";
		$body .= "To see your school another time, go to:\n\n    {$join_url}\n\nChoose Headteacher / SLT, and enter the code and this email address. We will email you a six-digit code to finish signing in.\n\n";
		$body .= "The school code is not a password. It shows which school, and everyone signing in also needs their own email address and a code we send them, so it is fine to show on a whiteboard.";
		return self::send( $to, $school_name . ': your school code', $body );
	}

	public static function send_colleague_joined( string $to, string $colleague, string $school_name ): bool {
		$body = "{$colleague} has joined {$school_name} using your school's email domain.\n\nIf you do not know them, reply to this email and we will remove them.";
		return self::send( $to, $colleague . ' joined ' . $school_name, $body );
	}

	public static function send_debate_invite( string $to, string $name, string $school_a_name, string $debate_code, string $url ): bool {
		$hello = '' !== $name ? "Hello {$name},\n\n" : '';
		$body  = $hello . "{$school_a_name} has invited your school to a debate about AI, as part of the National AI Conversation.\n\n";
		$body .= "Debate ID: {$debate_code}\n\n";
		$body .= "To accept, or to see who is asking first, open:\n\n    {$url}\n\n";
		$body .= "Your school will need headteacher or SLT approval before the debate can go ahead. If you do not know {$school_a_name}, ignore this email.";
		return self::send( $to, $school_a_name . ' has invited you to a debate', $body );
	}

	public static function send_match( string $to, string $other_school, string $debate_code, string $url, bool $proposes ): bool {
		$body  = "It's a match! Your school will debate {$other_school}.\n\nDebate ID: {$debate_code}\n\n";
		$body .= "Your Debate & Safeguarding Pack is on the debate page. Each school stays responsible for its own pupils, supervision, visitors and permissions, so please read it:\n\n    {$url}\n\n";
		$body .= $proposes
			? 'Next: you propose the date, theme, motion and judge on that page.'
			: 'Next: the other school proposes the date, theme, motion and judge. We will email you when they do.';
		return self::send( $to, "It's a match: " . $other_school, $body );
	}

	/** A plain notice for the smaller moments: proposed, agreed, cancelled, judge replied. */
	public static function send_notice( string $to, string $subject, string $body ): bool {
		return self::send( $to, $subject, $body );
	}

	public static function send_judge_invitation( string $to, string $judge_name, string $summary, string $school_code, string $join_url, string $shortcut_url ): bool {
		$body  = "Hello {$judge_name},\n\nThank you for offering to judge a school debate about AI.\n\n{$summary}\n\n";
		$body .= "To accept and, on the day, to score:\n\n";
		$body .= "  1. Go to {$join_url}\n  2. Enter the school code  {$school_code}\n  3. Choose Judge and enter this email address (we will send you a 6-digit code)\n\n";
		$body .= "Or use this shortcut to accept or decline:\n\n    {$shortcut_url}\n\n";
		$body .= "On the day you can score on your phone, or on paper: the host school may hand you a printed scorecard, or you can print one from your judge page and enter the final scores online afterwards.\n\n";
		$body .= 'The host school stays responsible for safeguarding and visitors, and will tell you how to arrive.';
		return self::send( $to, 'Will you judge a debate on ' . self::date_from_summary( $summary ) . '?', $body );
	}

	private static function date_from_summary( string $summary ): string {
		return preg_match( '/\b\d{1,2} [A-Z][a-z]{2} \d{4}\b/', $summary, $m ) ? preg_replace( '/ \d{4}$/', '', $m[0] ) : 'the day';
	}
}
