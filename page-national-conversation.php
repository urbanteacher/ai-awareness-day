<?php
/**
 * Template for /national-conversation/: what the National AI Conversation is.
 *
 * There is deliberately no "Become a partner" or "Volunteer to judge" button. Partners could be read as selling into
 * schools, and an open call for judges is a safeguarding risk. Partners come in through the schools that name them,
 * and judges only when a teacher invites them.
 *
 * The story follows the board brief: why it matters, what it covers, how a debate runs, how schools stay in charge,
 * what students say and where it goes, recognition, the network, who it is for, and when it happens.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dates      = aiad_national_conversation_dates();
$opens      = $dates['opens'];
$event      = $dates['event'];
$is_open    = time() >= $opens->getTimestamp();
$opens_long = wp_date( 'j F Y', $opens->getTimestamp() );
$event_long = wp_date( 'l j F Y', $event->getTimestamp() );
$event_year = wp_date( 'Y', $event->getTimestamp() );

// Until the conversation opens the portal is not offered: the page says when registration opens instead.
$portal_live  = aiad_portal_is_live();
$register_url = $portal_live ? ( aiad_conversation_url( 'register' ) ?: home_url( '/#contact' ) ) : '';
$sign_in_url  = $portal_live ? aiad_conversation_url( 'join' ) : '';
$nominate_url = $portal_live ? aiad_conversation_url( 'nominate' ) : '';
$privacy_url  = aiad_conversation_url( 'privacy' );
$readiness    = function_exists( 'aiad_get_benchmark_start_url' ) ? aiad_get_benchmark_start_url() : '';
$retention    = class_exists( 'AIADN_Privacy' ) ? wp_date( 'j F Y', strtotime( AIADN_Privacy::retention_date() ) ) : '31 August 2027';

// Brief section 22: the young person's journey, and what happens to what they say.
$journey = array( __( 'Learn', 'ai-awareness-day' ), __( 'Think', 'ai-awareness-day' ), __( 'Discuss', 'ai-awareness-day' ), __( 'Debate', 'ai-awareness-day' ), __( 'Reconsider', 'ai-awareness-day' ) );
$insight = array( __( 'Listen', 'ai-awareness-day' ), __( 'Measure', 'ai-awareness-day' ), __( 'Understand', 'ai-awareness-day' ), __( 'Report', 'ai-awareness-day' ), __( 'Feed back', 'ai-awareness-day' ) );

// Brief section 1: what taking part develops.
$skills = array( __( 'Critical thinking', 'ai-awareness-day' ), __( 'Oracy', 'ai-awareness-day' ), __( 'Weighing up evidence', 'ai-awareness-day' ), __( 'Respectful disagreement', 'ai-awareness-day' ), __( 'Independent judgement', 'ai-awareness-day' ) );

$themes = array(
	'safe'        => array( __( 'Safe', 'ai-awareness-day' ), __( 'Privacy, security, trust, memory, bias and protection.', 'ai-awareness-day' ) ),
	'smart'       => array( __( 'Smart', 'ai-awareness-day' ), __( 'Learning, reasoning, verification, critical thinking and dependence.', 'ai-awareness-day' ) ),
	'creative'    => array( __( 'Creative', 'ai-awareness-day' ), __( 'Creativity, authorship, originality, ownership and imagination.', 'ai-awareness-day' ) ),
	'responsible' => array( __( 'Responsible', 'ai-awareness-day' ), __( 'Accountability, fairness, sustainability, permission and consequences.', 'ai-awareness-day' ) ),
	'future'      => array( __( 'Future', 'ai-awareness-day' ), __( 'Jobs, skills, opportunity, human agency, society and young people\'s role in decisions.', 'ai-awareness-day' ) ),
);

$ways = array(
	array( __( 'Classroom Conversation', 'ai-awareness-day' ), __( 'The lowest barrier in. Five-minute starters, questions and free resources to get a class talking about AI.', 'ai-awareness-day' ) ),
	array( __( 'School Debate', 'ai-awareness-day' ), __( 'Class against class, house against house or year against year. Build confidence inside your own school first.', 'ai-awareness-day' ) ),
	array( __( 'National Debate Network', 'ai-awareness-day' ), __( 'Connect with another school for a structured, judged inter-school debate, in person or online.', 'ai-awareness-day' ) ),
);

// Brief sections 4 and 24.4: one item per theme and age group, each pitched differently.
$ages = array(
	array( __( 'Primary', 'ai-awareness-day' ), __( 'Mainly Years 5 and 6', 'ai-awareness-day' ), __( 'A scenario with clear options and a sentence starter.', 'ai-awareness-day' ) ),
	array( __( 'Secondary', 'ai-awareness-day' ), __( 'Years 7 to 11', 'ai-awareness-day' ), __( 'An open question, with a “What if…?” challenge card.', 'ai-awareness-day' ) ),
	array( __( 'Post-16 and FE', 'ai-awareness-day' ), __( 'Sixth form and college', 'ai-awareness-day' ), __( 'A formal motion, with its central tension and a research prompt.', 'ai-awareness-day' ) ),
);

// Brief section 24.5: the recommended 30-minute running order.
$running = array(
	array( __( '10 min', 'ai-awareness-day' ), __( 'Setting the scene', 'ai-awareness-day' ), __( 'A welcome, an opening vote from the room, and what the survey found.', 'ai-awareness-day' ) ),
	array( __( '20 min', 'ai-awareness-day' ), __( 'The debate', 'ai-awareness-day' ), __( 'Opening cases, time to prepare rebuttals, rebuttal, questions from the floor, closing speeches and a final vote.', 'ai-awareness-day' ) ),
);

$steps = array(
	array( __( 'Register your school', 'ai-awareness-day' ), __( 'A teacher gives their name, role and school email, and names a headteacher. The headteacher approves with one click. Nobody gets a password.', 'ai-awareness-day' ) ),
	array( __( 'Get a Debate ID', 'ai-awareness-day' ), __( 'Every debate has its own reference, for example AID-7K42P, that both schools and the judge use.', 'ai-awareness-day' ) ),
	array( __( 'Invite another school', 'ai-awareness-day' ), __( 'Send an invitation by email or share a link. No opponent yet? Join the conversation shows schools in your region, or online, looking for a debate. Each school confirms its own place: one school never registers another.', 'ai-awareness-day' ) ),
	array( __( 'Agree the fixture', 'ai-awareness-day' ), __( 'Choose the date, format, theme, motion and a judge. Once two schools are matched, both teachers receive the Debate & Safeguarding Pack automatically.', 'ai-awareness-day' ) ),
	array( __( 'Hold the debate', 'ai-awareness-day' ), __( 'The judge scores on a phone or on a printed scorecard, because not every venue has reliable Wi-Fi. The judge\'s submission is the official result.', 'ai-awareness-day' ) ),
	array( __( 'Be recognised', 'ai-awareness-day' ), __( 'Taking part is recorded straight away. Two debates against two different schools earns the National AI Debate School certificate.', 'ai-awareness-day' ) ),
);

// Brief section 22: Classroom, then school, then school to school, then trust, region and national.
$network = array( __( 'Classroom', 'ai-awareness-day' ), __( 'School', 'ai-awareness-day' ), __( 'School to school', 'ai-awareness-day' ), __( 'Trust', 'ai-awareness-day' ), __( 'Region', 'ai-awareness-day' ), __( 'National', 'ai-awareness-day' ) );

// Brief section 15. The professionals' line no longer asks for volunteer judges (decision 23.3), and the line for
// organisations asks nothing of them directly: they come in through their schools (decision 24.9).
$audiences = array(
	array( __( 'Young people', 'ai-awareness-day' ), __( 'AI is changing your future. You should have a say in it. Question it. Challenge it. Debate it.', 'ai-awareness-day' ), '' ),
	array( __( 'Teachers', 'ai-awareness-day' ), __( 'Start a meaningful conversation about AI without adding hours of administration.', 'ai-awareness-day' ), '' ),
	array( __( 'Headteachers', 'ai-awareness-day' ), __( 'Give your students a voice in one of the biggest conversations shaping their future.', 'ai-awareness-day' ), '' ),
	array( __( 'Trust leaders', 'ai-awareness-day' ), __( 'Turn individual school conversations into one powerful network-wide conversation about AI.', 'ai-awareness-day' ), '' ),
	array( __( 'Parents and carers', 'ai-awareness-day' ), __( 'Young people need opportunities to think critically about AI, not simply use it.', 'ai-awareness-day' ), '' ),
	array( __( 'Organisations that work with schools', 'ai-awareness-day' ), __( 'Take learning beyond the resource: help schools apply what they have learned, challenge one another and contribute nationally.', 'ai-awareness-day' ), '' ),
	array( __( 'Employers and technology organisations', 'ai-awareness-day' ), __( 'Don\'t just tell young people about the future of AI. Listen to them.', 'ai-awareness-day' ), '' ),
	array( __( 'Professionals and community leaders', 'ai-awareness-day' ), __( 'Give young people your attention, not a lecture.', 'ai-awareness-day' ), __( 'Judges are invited by a teacher at the host school, never through an open call.', 'ai-awareness-day' ) ),
	array( __( 'Policymakers and researchers', 'ai-awareness-day' ), __( 'Listen to what young people are actually saying about AI, through structured insight that is properly governed.', 'ai-awareness-day' ), '' ),
);

$timeline = array(
	array( __( 'Now to December 2026', 'ai-awareness-day' ), __( 'Getting ready. The platform is tested with a first group of schools while resources and safeguarding guidance are written.', 'ai-awareness-day' ) ),
	array( $opens_long, __( 'The National Conversation opens. Schools can begin classroom conversations, Student Voice activities and debates.', 'ai-awareness-day' ) ),
	array( __( 'January to March', 'ai-awareness-day' ), __( 'School debates, inter-school debates, trust activity and Student Voice.', 'ai-awareness-day' ) ),
	array( __( 'April', 'ai-awareness-day' ), __( 'Final debates, local and regional events, and the first national findings taking shape.', 'ai-awareness-day' ) ),
	array( $event_long, __( 'AI Awareness Day: Humans in the Loop. The national culmination, with the most engaged schools, the national findings and a flagship debate.', 'ai-awareness-day' ) ),
);

$faqs = array(
	array( __( 'Do students need an account?', 'ai-awareness-day' ), __( 'No. Students scan a QR code and enter a class PIN. They give no name and create no account.', 'ai-awareness-day' ) ),
	array( __( 'Do teachers need a password?', 'ai-awareness-day' ), __( 'No. Teachers, headteachers and judges sign in with their school code and their own email address, and we email a six-digit code.', 'ai-awareness-day' ) ),
	array( __( 'Can we take part without another school to debate?', 'ai-awareness-day' ), __( 'Yes. Classroom Conversations and School Debates need only your own school, and Join the conversation helps you find a school for an inter-school debate.', 'ai-awareness-day' ) ),
	array( __( 'Who is responsible for safeguarding?', 'ai-awareness-day' ), __( 'Each school, under its own policies. The platform connects schools and gives every teacher a checklist; it does not replace a school\'s own procedures.', 'ai-awareness-day' ) ),
	array(
		__( 'What happens to our details?', 'ai-awareness-day' ),
		sprintf(
			/* translators: %s: deletion date */
			__( 'Names and email addresses of teachers, headteachers and judges are deleted after the campaign ends on %s, or sooner if you ask. Student answers are anonymous. Totals and results stay, without anyone\'s contact details.', 'ai-awareness-day' ),
			$retention
		),
	),
);

get_header();
?>

<main id="main" role="main" class="ncp">

	<section class="ncp-hero" aria-labelledby="ncp-title">
		<div class="container">
			<p class="ncp-eyebrow"><?php esc_html_e( 'AI Awareness Day', 'ai-awareness-day' ); ?> <?php echo esc_html( $event_year ); ?> <span aria-hidden="true">&middot;</span> <?php esc_html_e( 'Humans in the Loop', 'ai-awareness-day' ); ?></p>
			<h1 class="ncp-title" id="ncp-title"><?php esc_html_e( 'The National AI Conversation', 'ai-awareness-day' ); ?></h1>
			<p class="ncp-lead"><?php esc_html_e( 'A national conversation about AI, led by young people. Question it. Challenge it. Debate it.', 'ai-awareness-day' ); ?></p>

			<dl class="ncp-dates">
				<div>
					<dt><?php echo $is_open ? esc_html__( 'Opened', 'ai-awareness-day' ) : esc_html__( 'Opens', 'ai-awareness-day' ); ?></dt>
					<dd><?php echo esc_html( $opens_long ); ?></dd>
				</div>
				<div>
					<dt><?php esc_html_e( 'AI Awareness Day', 'ai-awareness-day' ); ?></dt>
					<dd><?php echo esc_html( $event_long ); ?></dd>
				</div>
			</dl>

			<p class="ncp-actions">
				<?php if ( $register_url ) : ?>
					<a class="ncp-btn ncp-btn--primary" href="<?php echo esc_url( $register_url ); ?>"><?php esc_html_e( 'Join the National Conversation', 'ai-awareness-day' ); ?></a>
				<?php else : ?>
					<span class="ncp-btn ncp-btn--soon"><?php printf( /* translators: %s: opening date */ esc_html__( 'Schools can register from %s', 'ai-awareness-day' ), esc_html( $opens_long ) ); ?></span>
				<?php endif; ?>
				<?php if ( $readiness ) : ?>
					<a class="ncp-btn ncp-btn--ghost" href="<?php echo esc_url( $readiness ); ?>"><?php esc_html_e( 'Check your AI readiness', 'ai-awareness-day' ); ?></a>
				<?php endif; ?>
			</p>
			<?php if ( $nominate_url || $sign_in_url ) : ?>
			<p class="ncp-more">
				<?php if ( $nominate_url ) : ?><a href="<?php echo esc_url( $nominate_url ); ?>"><?php esc_html_e( 'Nominate a school you work with', 'ai-awareness-day' ); ?></a><?php endif; ?>
				<?php if ( $sign_in_url ) : ?><a href="<?php echo esc_url( $sign_in_url ); ?>"><?php esc_html_e( 'Already registered? Sign in', 'ai-awareness-day' ); ?></a><?php endif; ?>
			</p>
			<?php endif; ?>
		</div>
	</section>

	<section class="ncp-section" aria-labelledby="ncp-what">
		<div class="container ncp-narrow">
			<h2 id="ncp-what"><?php esc_html_e( 'Bigger than a single awareness day', 'ai-awareness-day' ); ?></h2>
			<p class="ncp-big">
				<?php
				printf(
					/* translators: 1: opening date, 2: AI Awareness Day date */
					esc_html__( 'From %1$s, schools can question, discuss and debate the role AI should play in their lives and futures, building up to AI Awareness Day on %2$s.', 'ai-awareness-day' ),
					esc_html( $opens_long ),
					esc_html( $event_long )
				);
				?>
			</p>
			<p><?php esc_html_e( 'Young people will not simply be taught about AI. They will listen to different perspectives and form their own judgements.', 'ai-awareness-day' ); ?></p>
			<p class="ncp-label" id="ncp-journey"><?php esc_html_e( 'Every student\'s journey', 'ai-awareness-day' ); ?></p>
			<ol class="ncp-arc" aria-labelledby="ncp-journey">
				<?php foreach ( $journey as $stage ) : ?>
					<li><?php echo esc_html( $stage ); ?></li>
				<?php endforeach; ?>
			</ol>
			<p class="ncp-chips">
				<strong><?php esc_html_e( 'The skills it builds:', 'ai-awareness-day' ); ?></strong>
				<?php foreach ( $skills as $skill ) : ?>
					<span><?php echo esc_html( $skill ); ?></span>
				<?php endforeach; ?>
			</p>
			<p><?php esc_html_e( 'It is designed to be easy to join. Start with five minutes in a lesson, or connect with another school for a structured debate. Nobody needs an account or a password.', 'ai-awareness-day' ); ?> <strong><?php esc_html_e( 'Maximum participation. Minimum administration.', 'ai-awareness-day' ); ?></strong></p>
			<blockquote class="ncp-principle">
				<p><?php esc_html_e( 'The technology connects the conversation. Young people do the thinking.', 'ai-awareness-day' ); ?></p>
			</blockquote>
		</div>
	</section>

	<section class="ncp-section ncp-section--card" aria-labelledby="ncp-themes">
		<div class="container">
			<h2 id="ncp-themes"><?php esc_html_e( 'Five themes, one conversation', 'ai-awareness-day' ); ?></h2>
			<ul class="ncp-themes">
				<?php foreach ( $themes as $slug => $theme ) : ?>
					<li class="ncp-theme ncp-theme--<?php echo esc_attr( $slug ); ?>">
						<span class="ncp-theme__icon" aria-hidden="true"><?php echo aiad_strand_icon_svg( $slug, 32 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<h3><?php echo esc_html( $theme[0] ); ?></h3>
						<p><?php echo esc_html( $theme[1] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<section class="ncp-section" aria-labelledby="ncp-ways">
		<div class="container">
			<h2 id="ncp-ways"><?php esc_html_e( 'Three ways to take part', 'ai-awareness-day' ); ?></h2>
			<ol class="ncp-ways">
				<?php foreach ( $ways as $way ) : ?>
					<li>
						<h3><?php echo esc_html( $way[0] ); ?></h3>
						<p><?php echo esc_html( $way[1] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
			<h3 class="ncp-subhead"><?php esc_html_e( 'Three age pathways, the same five themes', 'ai-awareness-day' ); ?></h3>
			<ul class="ncp-ages">
				<?php foreach ( $ages as $age ) : ?>
					<li>
						<h4><?php echo esc_html( $age[0] ); ?></h4>
						<p class="ncp-ages__years"><?php echo esc_html( $age[1] ); ?></p>
						<p><?php echo esc_html( $age[2] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
			<p><?php esc_html_e( 'Questions, motions, resources and judging are all pitched for the age.', 'ai-awareness-day' ); ?></p>
		</div>
	</section>

	<section class="ncp-section ncp-section--card" aria-labelledby="ncp-inside">
		<div class="container">
			<h2 id="ncp-inside"><?php esc_html_e( 'Inside a debate', 'ai-awareness-day' ); ?></h2>
			<p class="ncp-intro"><?php esc_html_e( 'About 30 minutes, and the whole class takes part, not just the speakers.', 'ai-awareness-day' ); ?></p>
			<ol class="ncp-running">
				<?php foreach ( $running as $part ) : ?>
					<li>
						<p class="ncp-running__time"><?php echo esc_html( $part[0] ); ?></p>
						<h3><?php echo esc_html( $part[1] ); ?></h3>
						<p><?php echo esc_html( $part[2] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
			<div class="ncp-inside">
				<div>
					<h3><?php esc_html_e( 'A role for everyone', 'ai-awareness-day' ); ?></h3>
					<p><?php esc_html_e( 'Each school gets a prep pack: a checklist, a short survey to run two to three weeks before with the same questions in both schools, research in three groups, and roles for a class of about 30.', 'ai-awareness-day' ); ?></p>
				</div>
				<div>
					<h3><?php esc_html_e( 'Judged on four things', 'ai-awareness-day' ); ?></h3>
					<p class="ncp-chips">
						<span><?php esc_html_e( 'Argument', 'ai-awareness-day' ); ?></span>
						<span><?php esc_html_e( 'Evidence', 'ai-awareness-day' ); ?></span>
						<span><?php esc_html_e( 'Rebuttal', 'ai-awareness-day' ); ?></span>
						<span><?php esc_html_e( 'Delivery', 'ai-awareness-day' ); ?></span>
					</p>
					<p><?php esc_html_e( 'Each is scored from 1 to 5. The criteria are the same at every age, in simpler words for primary.', 'ai-awareness-day' ); ?></p>
				</div>
			</div>
			<blockquote class="ncp-principle">
				<p><?php esc_html_e( 'Several sources for every fact. Don\'t rely on AI-generated content: it can be wrong.', 'ai-awareness-day' ); ?></p>
			</blockquote>
			<p class="ncp-note"><?php esc_html_e( 'The running order is a recommendation, not a rule, so schools can fit it to their setting and their students.', 'ai-awareness-day' ); ?></p>
		</div>
	</section>

	<section class="ncp-section" aria-labelledby="ncp-how">
		<div class="container">
			<h2 id="ncp-how"><?php esc_html_e( 'How an inter-school debate works', 'ai-awareness-day' ); ?></h2>
			<p class="ncp-motto"><?php esc_html_e( 'Identify your school once. We take care of the rest.', 'ai-awareness-day' ); ?></p>
			<p class="ncp-intro"><?php esc_html_e( 'It should feel more like arranging a school sports fixture than registering for another complicated education platform. It is not a learning management system, a pupil social network or a heavyweight competition platform.', 'ai-awareness-day' ); ?></p>
			<ol class="ncp-steps">
				<?php foreach ( $steps as $step ) : ?>
					<li>
						<h3><?php echo esc_html( $step[0] ); ?></h3>
						<p><?php echo str_replace( 'AID-7K42P', '<span class="ncp-nowrap">AID-7K42P</span>', esc_html( $step[1] ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped, then the example ID is wrapped. ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
			<p class="ncp-tally"><?php esc_html_e( 'One Debate ID. Two school leads. One judge. One result.', 'ai-awareness-day' ); ?></p>
		</div>
	</section>

	<section class="ncp-section ncp-section--card" aria-labelledby="ncp-safe">
		<div class="container ncp-split">
			<div>
				<h2 id="ncp-safe"><?php esc_html_e( 'Schools stay in charge', 'ai-awareness-day' ); ?></h2>
				<p><?php esc_html_e( 'The platform connects schools. Each school remains responsible for its own safeguarding, supervision, travel, parental permissions, risk assessments and visitor arrangements.', 'ai-awareness-day' ); ?></p>
				<p><?php esc_html_e( 'When two schools are matched, both teachers automatically receive our Debate & Safeguarding Pack to help them prepare.', 'ai-awareness-day' ); ?></p>
				<p><?php esc_html_e( 'Every debate has an independent judge, such as a professional, technologist, academic or community leader. Judges are invited by the host teacher, never through an open call, and the host school\'s own visitor procedures apply.', 'ai-awareness-day' ); ?></p>
			</div>
			<aside class="ncp-conduct" aria-labelledby="ncp-conduct">
				<h3 id="ncp-conduct"><?php esc_html_e( 'National Debate Code of Conduct', 'ai-awareness-day' ); ?></h3>
				<p class="ncp-conduct__line"><?php esc_html_e( 'Challenge the argument. Respect the person.', 'ai-awareness-day' ); ?></p>
				<p><?php esc_html_e( 'It applies to students, teachers, judges, visitors and audiences. Strong disagreement is encouraged; personal attacks, discriminatory language, intimidation, deliberate disruption and abusive audience behaviour are not.', 'ai-awareness-day' ); ?></p>
			</aside>
		</div>
	</section>

	<section class="ncp-section" aria-labelledby="ncp-voice">
		<div class="container ncp-narrow">
			<h2 id="ncp-voice"><?php esc_html_e( 'Students have their say, and schools get something back', 'ai-awareness-day' ); ?></h2>
			<blockquote class="ncp-principle ncp-principle--lead">
				<p><?php esc_html_e( 'A survey captures a position. A debate captures a position changing.', 'ai-awareness-day' ); ?></p>
			</blockquote>
			<p><?php esc_html_e( 'When a school joins, its teachers are invited to share a short Student Voice activity, “Get your students thinking”. It is preparation for the debate, not a research form. Students scan a QR code, type the class PIN and answer a few questions about Safe, Smart, Creative, Responsible and Future. There are no accounts and no names.', 'ai-awareness-day' ); ?></p>
			<p><?php esc_html_e( 'In return the school receives a School AI Snapshot: what its own students think about AI, shown once ten students have answered. Schools can use it for curriculum planning, their AI strategy, staff development, governors and student voice work.', 'ai-awareness-day' ); ?></p>
			<p><?php esc_html_e( 'After a debate the judge also records how the audience voted before and after. That lets us ask a real question: does hearing different arguments change how young people think about AI? Any wider research is designed with specialist review of consent, privacy and safeguarding first.', 'ai-awareness-day' ); ?></p>
			<h3 class="ncp-subhead" id="ncp-insight"><?php esc_html_e( 'Where their voices go', 'ai-awareness-day' ); ?></h3>
			<ol class="ncp-arc ncp-arc--small" aria-labelledby="ncp-insight">
				<?php foreach ( $insight as $stage ) : ?>
					<li><?php echo esc_html( $stage ); ?></li>
				<?php endforeach; ?>
			</ol>
			<p><?php esc_html_e( 'What young people say is fed back into education, industry and public discussion. The conversation is built to run every year, so over time it can show how young people\'s relationship with AI is changing. Findings describe the schools that took part; they never claim to speak for every young person in the country.', 'ai-awareness-day' ); ?></p>
		</div>
	</section>

	<section class="ncp-section ncp-section--card" aria-labelledby="ncp-recognition">
		<div class="container">
			<h2 id="ncp-recognition"><?php esc_html_e( 'Recognising taking part, not only winning', 'ai-awareness-day' ); ?></h2>
			<div class="ncp-recognition">
				<div>
					<p class="ncp-recognition__count">1</p>
					<p><?php esc_html_e( 'completed inter-school debate', 'ai-awareness-day' ); ?></p>
					<p class="ncp-recognition__result"><?php esc_html_e( 'Participation recorded', 'ai-awareness-day' ); ?></p>
				</div>
				<div>
					<p class="ncp-recognition__count">2</p>
					<p><?php esc_html_e( 'completed debates against two different schools', 'ai-awareness-day' ); ?></p>
					<p class="ncp-recognition__result"><?php esc_html_e( 'National AI Debate School certificate', 'ai-awareness-day' ); ?></p>
				</div>
			</div>
			<p><?php esc_html_e( 'The programme rewards conversation, connection and breadth: the schools you meet, the students involved and the themes explored. Trusts and regions can run competitive events of their own, but there is no national league table of winners.', 'ai-awareness-day' ); ?></p>
			<p><?php esc_html_e( 'Results are public: both schools, the theme, the motion, the date, the result and, if they agree, the judge\'s name. Teachers\' names and anything about students are never published.', 'ai-awareness-day' ); ?></p>
		</div>
	</section>

	<section class="ncp-section" aria-labelledby="ncp-networks">
		<div class="container ncp-narrow">
			<h2 id="ncp-networks"><?php esc_html_e( 'Whole trusts and communities can join in', 'ai-awareness-day' ); ?></h2>
			<p class="ncp-chain" aria-label="<?php esc_attr_e( 'Classroom, then school, then school to school, then trust, then region, then national', 'ai-awareness-day' ); ?>">
				<?php foreach ( $network as $level ) : ?>
					<span><?php echo esc_html( $level ); ?></span>
				<?php endforeach; ?>
			</p>
			<p><?php esc_html_e( 'A multi-academy trust can hold debates between its own academies before connecting with schools elsewhere. An organisation that already works with schools can encourage them to take part.', 'ai-awareness-day' ); ?></p>
			<p><?php esc_html_e( 'Whoever brings a school along, the school always confirms its own place, and its headteacher decides whether the organisation that introduced it is told.', 'ai-awareness-day' ); ?></p>
			<?php if ( $nominate_url ) : ?>
				<p><a class="ncp-link" href="<?php echo esc_url( $nominate_url ); ?>"><?php esc_html_e( 'Work with a school? Nominate it', 'ai-awareness-day' ); ?></a></p>
			<?php endif; ?>
		</div>
	</section>

	<section class="ncp-section ncp-section--card" aria-labelledby="ncp-who">
		<div class="container">
			<h2 id="ncp-who"><?php esc_html_e( 'Everyone has a part to play', 'ai-awareness-day' ); ?></h2>
			<ul class="ncp-who">
				<?php foreach ( $audiences as $audience ) : ?>
					<li>
						<h3><?php echo esc_html( $audience[0] ); ?></h3>
						<p class="ncp-who__line"><?php echo esc_html( $audience[1] ); ?></p>
						<?php if ( $audience[2] ) : ?>
							<p class="ncp-who__note"><?php echo esc_html( $audience[2] ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<section class="ncp-section" aria-labelledby="ncp-timeline">
		<div class="container ncp-narrow">
			<h2 id="ncp-timeline"><?php esc_html_e( 'The timeline', 'ai-awareness-day' ); ?></h2>
			<ol class="ncp-timeline">
				<?php foreach ( $timeline as $item ) : ?>
					<li>
						<h3><?php echo esc_html( $item[0] ); ?></h3>
						<p><?php echo esc_html( $item[1] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>

	<section class="ncp-section ncp-section--card" aria-labelledby="ncp-faq">
		<div class="container ncp-narrow">
			<h2 id="ncp-faq"><?php esc_html_e( 'Questions schools ask', 'ai-awareness-day' ); ?></h2>
			<?php foreach ( $faqs as $faq ) : ?>
				<details class="ncp-faq">
					<summary><?php echo esc_html( $faq[0] ); ?></summary>
					<p><?php echo esc_html( $faq[1] ); ?></p>
				</details>
			<?php endforeach; ?>
			<?php if ( $privacy_url ) : ?>
				<p class="ncp-small"><a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Delete my details', 'ai-awareness-day' ); ?></a></p>
			<?php endif; ?>
		</div>
	</section>

	<section class="ncp-cta" aria-labelledby="ncp-cta">
		<div class="container">
			<h2 id="ncp-cta"><?php esc_html_e( 'Ready to start the conversation?', 'ai-awareness-day' ); ?></h2>
			<?php if ( $register_url ) : ?>
				<p><?php esc_html_e( 'Register your school in a few minutes. Your headteacher approves with one click.', 'ai-awareness-day' ); ?></p>
				<p class="ncp-actions">
					<a class="ncp-btn ncp-btn--primary" href="<?php echo esc_url( $register_url ); ?>"><?php esc_html_e( 'Join the National Conversation', 'ai-awareness-day' ); ?></a>
				</p>
				<p class="ncp-more">
					<?php if ( $nominate_url ) : ?><a href="<?php echo esc_url( $nominate_url ); ?>"><?php esc_html_e( 'Nominate a school you work with', 'ai-awareness-day' ); ?></a><?php endif; ?>
					<?php if ( $sign_in_url ) : ?><a href="<?php echo esc_url( $sign_in_url ); ?>"><?php esc_html_e( 'Already registered? Sign in', 'ai-awareness-day' ); ?></a><?php endif; ?>
				</p>
			<?php else : ?>
				<p><?php printf( /* translators: %s: opening date */ esc_html__( 'Schools can register from %s. Until then, see how ready your school is for AI.', 'ai-awareness-day' ), esc_html( $opens_long ) ); ?></p>
				<?php if ( $readiness ) : ?>
					<p class="ncp-actions">
						<a class="ncp-btn ncp-btn--primary" href="<?php echo esc_url( $readiness ); ?>"><?php esc_html_e( 'Check your AI readiness', 'ai-awareness-day' ); ?></a>
					</p>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</section>

</main>

<?php get_footer(); ?>
