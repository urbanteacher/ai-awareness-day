<?php
/**
 * Template for /walkthrough/: a public tour of the National AI Conversation platform (see inc/walkthrough.php).
 *
 * Signed-in screens are screenshots of the local demo, so the tour works for anyone, on any device, without an
 * account; the two public pages link to the live site. Every screen shows made-up demo data.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$aiad_demo_url = function_exists( 'aiad_walkthrough_demo_url' ) ? aiad_walkthrough_demo_url() : '';
$aiad_nc_url   = function_exists( 'aiad_national_conversation_page_url' ) ? aiad_national_conversation_page_url() : home_url( '/national-conversation/' );

// Each screen: image, width, height, title, what it shows, and a live page to open where there is one.
$aiad_parts = array(
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

get_header();
?>

<main id="main" role="main" class="wt">

	<section class="wt-hero" aria-labelledby="wt-title">
		<div class="container">
			<p class="wt-eyebrow"><?php esc_html_e( 'National AI Conversation 2027', 'ai-awareness-day' ); ?></p>
			<h1 class="wt-title" id="wt-title"><?php esc_html_e( 'Platform walkthrough', 'ai-awareness-day' ); ?></h1>
			<p class="wt-lead"><?php esc_html_e( 'A tour of the platform schools will use from 1 January 2027, from signing up to the programme team\'s dashboard.', 'ai-awareness-day' ); ?></p>
			<p class="wt-demo-note"><?php esc_html_e( 'Every screen shows made-up demo data: Willowbrook Primary School and the schools it debates.', 'ai-awareness-day' ); ?></p>
			<p class="wt-actions">
				<a class="wt-btn wt-btn--primary" href="<?php echo esc_url( $aiad_nc_url ); ?>"><?php esc_html_e( 'What is the National AI Conversation?', 'ai-awareness-day' ); ?></a>
				<?php if ( $aiad_demo_url ) : ?>
					<a class="wt-btn wt-btn--ghost" href="<?php echo esc_url( $aiad_demo_url ); ?>"><?php esc_html_e( 'Open the interactive demo', 'ai-awareness-day' ); ?></a>
				<?php endif; ?>
			</p>
			<nav class="wt-parts" aria-label="<?php esc_attr_e( 'Parts of the walkthrough', 'ai-awareness-day' ); ?>">
				<ol>
					<?php foreach ( $aiad_parts as $aiad_i => $aiad_part ) : ?>
						<li><a href="#wt-<?php echo esc_attr( $aiad_part['id'] ); ?>"><?php echo esc_html( ( $aiad_i + 1 ) . '. ' . $aiad_part['title'] ); ?></a></li>
					<?php endforeach; ?>
				</ol>
			</nav>
		</div>
	</section>

	<?php
	$aiad_n = 0;
	foreach ( $aiad_parts as $aiad_i => $aiad_part ) :
		?>
	<section class="wt-part<?php echo 1 === $aiad_i % 2 ? ' wt-part--card' : ''; ?>" id="wt-<?php echo esc_attr( $aiad_part['id'] ); ?>" aria-labelledby="wt-<?php echo esc_attr( $aiad_part['id'] ); ?>-title">
		<div class="container">
			<p class="wt-part__number"><?php echo esc_html( sprintf( /* translators: %d: part number */ __( 'Part %d', 'ai-awareness-day' ), $aiad_i + 1 ) ); ?></p>
			<h2 id="wt-<?php echo esc_attr( $aiad_part['id'] ); ?>-title"><?php echo esc_html( $aiad_part['title'] ); ?></h2>
			<p class="wt-part__intro"><?php echo esc_html( $aiad_part['intro'] ); ?></p>
			<ol class="wt-shots">
				<?php
				foreach ( $aiad_part['shots'] as $aiad_shot ) :
					++$aiad_n;
					list( $aiad_file, $aiad_w, $aiad_h, $aiad_title, $aiad_desc, $aiad_live ) = $aiad_shot;
					$aiad_src   = AIAD_URI . '/assets/images/walkthrough/' . $aiad_file . '.jpg';
					$aiad_phone = $aiad_w < $aiad_h * 0.5;
					?>
					<li class="wt-shot<?php echo $aiad_phone ? ' wt-shot--phone' : ''; ?>">
						<a class="wt-shot__frame" href="<?php echo esc_url( $aiad_src ); ?>" target="_blank" rel="noopener">
							<img src="<?php echo esc_url( $aiad_src ); ?>" width="<?php echo (int) $aiad_w; ?>" height="<?php echo (int) $aiad_h; ?>" alt="<?php echo esc_attr( sprintf( /* translators: %s: screen name */ __( 'Screenshot: %s', 'ai-awareness-day' ), $aiad_title ) ); ?>" loading="lazy" decoding="async">
							<span class="wt-shot__zoom"><?php esc_html_e( 'View full screen', 'ai-awareness-day' ); ?></span>
						</a>
						<div class="wt-shot__body">
							<p class="wt-shot__step"><?php echo esc_html( sprintf( /* translators: %d: screen number */ __( 'Screen %d', 'ai-awareness-day' ), $aiad_n ) ); ?></p>
							<h3><?php echo esc_html( $aiad_title ); ?></h3>
							<p><?php echo esc_html( $aiad_desc ); ?></p>
							<?php if ( $aiad_live ) : ?>
								<p class="wt-shot__live"><a href="<?php echo esc_url( $aiad_live ); ?>"><?php esc_html_e( 'Open the live page', 'ai-awareness-day' ); ?></a></p>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>
	<?php endforeach; ?>

	<section class="wt-cta" aria-labelledby="wt-cta">
		<div class="container">
			<h2 id="wt-cta"><?php esc_html_e( 'Schools can register from 1 January 2027', 'ai-awareness-day' ); ?></h2>
			<p><?php esc_html_e( 'Find out more about the National AI Conversation, or get in touch.', 'ai-awareness-day' ); ?></p>
			<p class="wt-actions">
				<a class="wt-btn wt-btn--primary" href="<?php echo esc_url( $aiad_nc_url ); ?>"><?php esc_html_e( 'The National AI Conversation', 'ai-awareness-day' ); ?></a>
				<a class="wt-btn wt-btn--ghost" href="<?php echo esc_url( home_url( '/#contact' ) ); ?>"><?php esc_html_e( 'Get in touch', 'ai-awareness-day' ); ?></a>
			</p>
		</div>
	</section>

</main>

<?php get_footer(); ?>
