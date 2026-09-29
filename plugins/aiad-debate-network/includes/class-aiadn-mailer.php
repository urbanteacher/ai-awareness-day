<?php
/**
 * Emails. Plain text, short, from a recognisable sender.
 *
 * Locally, set AIADN_SMTP_HOST (docker-compose does) and every email goes to Mailpit.
 * In production, use a transactional email service through an SMTP plugin and
 * authenticate the sending domain (SPF, DKIM, DMARC).
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
		if ( defined( 'AIADN_MAIL_FROM' ) && is_email( AIADN_MAIL_FROM ) ) {
			return AIADN_MAIL_FROM;
		}
		$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
		$host = preg_replace( '/^www\./', '', $host );
		if ( false === strpos( $host, '.' ) ) {
			$host = 'aiawarenessday.co.uk';
		}
		return 'no-reply@' . $host;
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
		if ( ! $host ) {
			return;
		}
		$phpmailer->isSMTP();
		$phpmailer->Host        = $host;
		$phpmailer->Port        = (int) ( getenv( 'AIADN_SMTP_PORT' ) ?: 1025 );
		$phpmailer->SMTPAuth    = false;
		$phpmailer->SMTPAutoTLS = false;
	}

	public static function from_name( string $name ): string {
		return 'AI Awareness Day';
	}

	private static function send( string $to, string $subject, string $body ): bool {
		$footer = "\n\n--\nAI Awareness Day 2027: National AI Conversation\nhttps://aiawarenessday.co.uk/";
		return (bool) wp_mail( $to, $subject, $body . $footer );
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
