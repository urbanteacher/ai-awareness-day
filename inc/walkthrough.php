<?php
/**
 * /walkthrough/ : a public tour of the National AI Conversation platform, for showing clients.
 *
 * It works anywhere, on any device, because nothing on it needs signing in: the signed-in screens are screenshots of
 * the local demo (assets/images/walkthrough/), and only the public pages link to the live site. The interactive demo
 * itself (demo-walkthrough.html and demo-start.php, which signs the browser in as the demo administrator) stays on the
 * presenter's laptop and out of git; on the local copy the tour links to it.
 *
 * The address is matched directly rather than through a rewrite rule, so it works straight after a deploy without
 * waiting for the stored rules to be flushed. Pages → Theme pages (inc/editable-pages.php) can turn it into an
 * editable page in blocks at the same address (patterns/walkthrough.php, templates/page-walkthrough.html), which
 * the address then shows.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether this request is the walkthrough.
 */
function aiad_is_walkthrough(): bool {
	static $is = null;
	if ( null === $is ) {
		$path = strtolower( trim( (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ), '/' ) );
		$home = strtolower( trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' ) );
		if ( '' !== $home && 0 === strpos( $path, $home . '/' ) ) {
			$path = substr( $path, strlen( $home ) + 1 );
		}
		$is = ( 'walkthrough' === $path );
	}
	return $is;
}

/**
 * The editable page's placeholder (inc/editable-pages.php): the interactive demo's address, which is empty away from
 * the presenter's laptop, so the link to it is left out.
 *
 * @return array<string, string>
 */
function aiad_walkthrough_placeholders(): array {
	return array( '{demo_url}' => esc_url( aiad_walkthrough_demo_url() ) );
}

/**
 * The interactive demo on the presenter's laptop, or '' anywhere else.
 */
function aiad_walkthrough_demo_url(): string {
	$host = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
	if ( in_array( $host, array( 'localhost', '127.0.0.1' ), true ) && is_readable( get_template_directory() . '/demo-walkthrough.html' ) ) {
		return AIAD_URI . '/demo-walkthrough.html';
	}
	return '';
}

/** No post sits behind the address, so say plainly that it is a real page. */
add_action(
	'template_redirect',
	static function (): void {
		if ( ! aiad_is_walkthrough() ) {
			return;
		}
		global $wp_query;
		$wp_query->is_404  = false;
		$wp_query->is_home = false;
		status_header( 200 );
	},
	1 // Before WordPress guesses at a similar page for an address it does not know.
);

add_filter(
	'template_include',
	static function ( string $template ): string {
		if ( ! aiad_is_walkthrough() || aiad_editable_page_post( 'walkthrough' ) ) {
			return $template; // The editable page has its own template (templates/page-walkthrough.html).
		}
		$custom = get_template_directory() . '/page-walkthrough.php';
		return is_readable( $custom ) ? $custom : $template;
	},
	20
);

add_filter(
	'pre_get_document_title',
	static function ( string $title ): string {
		return aiad_is_walkthrough() ? __( 'Platform walkthrough | AI Awareness Day', 'ai-awareness-day' ) : $title;
	},
	20
);

// Public, but shared by link: every screen shows demo data, so it is kept out of search results.
add_filter(
	'wp_robots',
	static function ( array $robots ): array {
		if ( aiad_is_walkthrough() ) {
			$robots['noindex'] = true;
		}
		return $robots;
	}
);

add_filter(
	'body_class',
	static function ( array $classes ): array {
		if ( aiad_is_walkthrough() ) {
			$classes[] = 'walkthrough-page';
		}
		return $classes;
	}
);

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		if ( is_admin() || ! aiad_is_walkthrough() ) {
			return;
		}
		$css = AIAD_DIR . '/assets/css/pages/walkthrough.css';
		if ( is_readable( $css ) ) {
			wp_enqueue_style( 'aiad-walkthrough', AIAD_URI . '/assets/css/pages/walkthrough.css', array( 'aiad-style' ), AIAD_VERSION . '.' . (string) filemtime( $css ) );
		}
	},
	15
);

/**
 * The walkthrough's parts and screens, in order: page-walkthrough.php prints them, and patterns/walkthrough.php turns
 * them into the blocks of the editable page. Each screen: image, width, height, title, what it shows, and a live page
 * to open where there is one.
 *
 * @param string $aiad_nc_url The National Conversation page's address.
 * @return array<int, array<string, mixed>>
 */
function aiad_walkthrough_parts( string $aiad_nc_url ): array {
	return array(
		array(
			'id'    => 'front-door',
			'title' => __( 'The public front door', 'ai-awareness-day' ),
			'intro' => __( 'What anyone sees, and how a school joins.', 'ai-awareness-day' ),
			'shots' => array(
				array( '01-national-conversation', 1280, 900, __( 'What is the National AI Conversation?', 'ai-awareness-day' ), __( 'The landing page: the idea, the five themes, how each age debates, how a debate works, safeguarding and the timeline.', 'ai-awareness-day' ), $aiad_nc_url ),
				array( '02-homepage', 1280, 900, __( 'The homepage', 'ai-awareness-day' ), __( 'The way in: one debate motion at a time in the strand box, and the countdown to AI Awareness Day.', 'ai-awareness-day' ), home_url( '/' ) ),
				array( '03-register', 1280, 1150, __( 'Register your school', 'ai-awareness-day' ), __( 'One short form. It names the headteacher who must approve the school, and who introduced it. Nobody gets a password.', 'ai-awareness-day' ), '' ),
				array( '04-nominate', 1280, 950, __( 'Nominate a school', 'ai-awareness-day' ), __( 'Someone who works with a school can invite it. They prove their own email first, and the school is emailed once.', 'ai-awareness-day' ), '' ),
				array( '05-headteacher-approval', 1280, 900, __( 'The headteacher approves', 'ai-awareness-day' ), __( 'One click from one email. Nothing goes live for the school until this happens.', 'ai-awareness-day' ), '' ),
			),
		),
		array(
			'id'    => 'school',
			'title' => __( 'A school\'s day', 'ai-awareness-day' ),
			'intro' => __( 'Willowbrook Primary School, as its lead teacher sees it.', 'ai-awareness-day' ),
			'shots' => array(
				array( '06-school-dashboard', 1280, 1300, __( 'School dashboard', 'ai-awareness-day' ), __( 'The school code, the class PIN, every debate and the team, in one place.', 'ai-awareness-day' ), '' ),
				array( '07-whiteboard', 1280, 900, __( 'On the whiteboard', 'ai-awareness-day' ), __( 'What the class sees: scan the code, type the PIN, and answer a few anonymous questions. No names, no accounts.', 'ai-awareness-day' ), '' ),
				array( '08-join-the-conversation', 1280, 900, __( 'Join the conversation', 'ai-awareness-day' ), __( 'Schools looking for an opponent, in their own region first. The host school chooses who to accept.', 'ai-awareness-day' ), '' ),
				array( '09-debate-waiting', 1280, 1150, __( 'A debate waiting for an opponent', 'ai-awareness-day' ), __( 'Every step on one tracker, so both teachers always know what is next. Invite by email, share a link, or put it on the board.', 'ai-awareness-day' ), '' ),
				array( '10-debate-ready', 1280, 1300, __( 'A debate that is ready', 'ai-awareness-day' ), __( 'The fixture, the judge, the safeguarding pack, a calendar entry and directions.', 'ai-awareness-day' ), '' ),
				array( '11-prep-pack', 1280, 1300, __( 'The prep pack', 'ai-awareness-day' ), __( 'A 30-minute running order for the age group, a checklist, the survey to run beforehand, research and roles for the whole class.', 'ai-awareness-day' ), '' ),
				array( '12-paper-scorecard', 1280, 1250, __( 'A paper scorecard', 'ai-awareness-day' ), __( 'For a judge who prefers paper. The code on it leads to the official online entry.', 'ai-awareness-day' ), '' ),
				array( '13-debate-result', 1280, 1150, __( 'The result', 'ai-awareness-day' ), __( 'Scores on four criteria, the judge\'s comment for the school, and how the room vote moved.', 'ai-awareness-day' ), '' ),
				array( '14-results', 1280, 1150, __( 'Results', 'ai-awareness-day' ), __( 'Every debate, the school\'s scores against the average for its age group, and progress to the certificate.', 'ai-awareness-day' ), '' ),
				array( '15-certificate', 1280, 1050, __( 'The certificate', 'ai-awareness-day' ), __( 'Two debates against two different schools earns it. Printable, with a reference anyone can check.', 'ai-awareness-day' ), '' ),
				array( '16-student-voice', 1280, 1150, __( 'Student Voice', 'ai-awareness-day' ), __( 'What the school\'s own students think, anonymous and hidden until at least ten have answered.', 'ai-awareness-day' ), '' ),
				array( '17-school-ai-snapshot', 1280, 1300, __( 'The School AI Snapshot', 'ai-awareness-day' ), __( 'One printable page to take to governors and staff.', 'ai-awareness-day' ), '' ),
			),
		),
		array(
			'id'    => 'judge',
			'title' => __( 'The judge', 'ai-awareness-day' ),
			'intro' => __( 'No account: the judge is invited by the host teacher and uses the link in the invitation.', 'ai-awareness-day' ),
			'shots' => array(
				array( '18-judge-page', 1280, 1150, __( 'The judge\'s page', 'ai-awareness-day' ), __( 'The fixture, where to go, the running order and two ways to score.', 'ai-awareness-day' ), '' ),
				array( '19-judge-scorecard-phone', 780, 2800, __( 'Scoring on a phone', 'ai-awareness-day' ), __( 'Four criteria, a comment for each school, and the room vote before and after. It saves as the judge types.', 'ai-awareness-day' ), '' ),
			),
		),
		array(
			'id'    => 'everyone',
			'title' => __( 'Everyone else', 'ai-awareness-day' ),
			'intro' => __( 'What the public, and the organisations that introduce schools, can see.', 'ai-awareness-day' ),
			'shots' => array(
				array( '20-public-results', 1280, 972, __( 'Public results', 'ai-awareness-day' ), __( 'Both schools, the theme, the motion and the winner, and the judge\'s name only if they agreed. Never anything about students.', 'ai-awareness-day' ), '' ),
				array( '21-certificate-check', 1280, 850, __( 'Check a certificate', 'ai-awareness-day' ), __( 'Anyone can check a certificate is genuine from its reference.', 'ai-awareness-day' ), '' ),
				array( '22-introducer-dashboard', 1280, 1150, __( 'An introducer\'s dashboard', 'ai-awareness-day' ), __( 'For an organisation that introduced a school: an emailed link, no account, totals only, and only if the school\'s headteacher agreed.', 'ai-awareness-day' ), '' ),
			),
		),
		array(
			'id'    => 'programme',
			'title' => __( 'The programme team', 'ai-awareness-day' ),
			'intro' => __( 'How AI Awareness Day runs the conversation nationally.', 'ai-awareness-day' ),
			'shots' => array(
				array( '23-programme-team', 1280, 975, __( 'The programme dashboard', 'ai-awareness-day' ), __( 'The journey from sign-up to certificate, stuck fixtures, regions, MATs, partners, judges and Student Voice. Every figure is a count; the ones shown are illustrative.', 'ai-awareness-day' ), '' ),
			),
		),
	);
}
