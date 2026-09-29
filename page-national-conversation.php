<?php
/**
 * Template for /national-conversation/: what the National AI Conversation is.
 *
 * There is deliberately no "Become a partner" or "Volunteer to judge" button. Partners could be read as selling into
 * schools, and an open call for judges is a safeguarding risk. Partners come in through the schools that name them,
 * and judges only when a teacher invites them. The one invitation to organisations is to support the programme
 * itself (the platform, guest speakers and hosting for schools serving disadvantaged communities), and it goes to the
 * programme team through the contact form, not to any school.
 *
 * The story follows the board brief: why it matters, who does what, what it covers, how each age debates, how a
 * debate runs, how schools stay in charge, recognition, the network, partners, who it is for, and when it happens.
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
$readiness    = function_exists( 'aiad_get_benchmark_start_url' ) ? aiad_get_benchmark_start_url() : '';
$retention    = class_exists( 'AIADN_Privacy' ) ? wp_date( 'j F Y', strtotime( AIADN_Privacy::retention_date() ) ) : '31 August 2027';

// The young person's journey (adapted from brief section 22).
$journey = array( __( 'Learn', 'ai-awareness-day' ), __( 'Think', 'ai-awareness-day' ), __( 'Discuss', 'ai-awareness-day' ), __( 'Rebut', 'ai-awareness-day' ), __( 'Reflect', 'ai-awareness-day' ) );

// What taking part develops (brief section 1, expanded).
$skills = array(
	array( __( 'Oracy & Communication', 'ai-awareness-day' ), __( 'Developing fluent spoken language, confidence and the ability to articulate complex ideas.', 'ai-awareness-day' ) ),
	array( __( 'Public Speaking', 'ai-awareness-day' ), __( 'Being articulate and exploring technical concepts in front of an audience.', 'ai-awareness-day' ) ),
	array( __( 'Critical Thinking', 'ai-awareness-day' ), __( 'Evaluating arguments logically and spotting cognitive biases.', 'ai-awareness-day' ) ),
	array( __( 'Information Literacy', 'ai-awareness-day' ), __( 'Critically examining evidence, identifying bias and verifying facts.', 'ai-awareness-day' ) ),
	array( __( 'Conflict Resolution', 'ai-awareness-day' ), __( 'Learning how to resolve conflict in pressured situations.', 'ai-awareness-day' ) ),
	array( __( 'Active Listening & Empathy', 'ai-awareness-day' ), __( 'Engaging deeply with other people\'s viewpoints and responding respectfully.', 'ai-awareness-day' ) ),
	array( __( 'Independent Judgement', 'ai-awareness-day' ), __( 'Forming personal convictions backed by reasoning rather than peer pressure.', 'ai-awareness-day' ) ),
	array( __( 'Civil Discourse', 'ai-awareness-day' ), __( 'Learning to defuse playground or real-world tension by adapting tone and voice to solve problems together.', 'ai-awareness-day' ) ),
);

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

// Who does what: one route in for each kind of person, in the order the platform walks them through it.
$paths = array(
	array(
		'id'    => 'teachers',
		'who'   => __( 'Teachers', 'ai-awareness-day' ),
		'tag'   => __( 'Lead the conversation in your school', 'ai-awareness-day' ),
		'steps' => array(
			__( 'Register your school and name the headteacher or senior leader who will approve it.', 'ai-awareness-day' ),
			__( 'Once they approve, put the class PIN on the whiteboard and start a debate.', 'ai-awareness-day' ),
			__( 'Invite a school you know, or find one on Join the conversation.', 'ai-awareness-day' ),
			__( 'Use the prep pack, hold the debate, and collect the result and certificate.', 'ai-awareness-day' ),
		),
		'note'  => __( 'Colleagues with your school email address join straight away. Anyone else is approved by the lead teacher.', 'ai-awareness-day' ),
	),
	array(
		'id'    => 'headteachers',
		'who'   => __( 'Headteachers and senior leaders', 'ai-awareness-day' ),
		'tag'   => __( 'One click to say yes', 'ai-awareness-day' ),
		'steps' => array(
			__( 'You get one email when a teacher registers your school.', 'ai-awareness-day' ),
			__( 'Approve with one click. Nothing goes live until you do.', 'ai-awareness-day' ),
			__( 'Decide whether the organisation that introduced your school is told.', 'ai-awareness-day' ),
			__( 'Sign in any time with the school code and your own email.', 'ai-awareness-day' ),
		),
		'note'  => '',
	),
	array(
		'id'    => 'students',
		'who'   => __( 'Students', 'ai-awareness-day' ),
		'tag'   => __( 'No names, no accounts', 'ai-awareness-day' ),
		'steps' => array(
			__( 'Scan the QR code on the whiteboard, or type the school code and the class PIN.', 'ai-awareness-day' ),
			__( 'Answer a few anonymous questions about AI before the debate.', 'ai-awareness-day' ),
			__( 'Take a role: speaker, note-taker, questioner or survey presenter.', 'ai-awareness-day' ),
			__( 'Vote before and after, and see whether the room changed its mind.', 'ai-awareness-day' ),
		),
		'note'  => '',
	),
	array(
		'id'    => 'judges',
		'who'   => __( 'Judges', 'ai-awareness-day' ),
		'tag'   => __( 'Invited by a teacher', 'ai-awareness-day' ),
		'steps' => array(
			__( 'A teacher at the host school invites you by email. There is no open call.', 'ai-awareness-day' ),
			__( 'Agree to follow the host school\'s safeguarding and visitor rules.', 'ai-awareness-day' ),
			__( 'Score on your phone or on a printed scorecard. Your submission is the result.', 'ai-awareness-day' ),
			__( 'Choose whether your name appears on the public result.', 'ai-awareness-day' ),
		),
		'note'  => '',
	),
	array(
		'id'    => 'organisations',
		'who'   => __( 'Organisations that support schools', 'ai-awareness-day' ),
		'tag'   => __( 'Encourage your schools, don\'t enrol them', 'ai-awareness-day' ),
		'steps' => array(
			__( 'Tell the schools you work with. Each registers and confirms its own place.', 'ai-awareness-day' ),
			__( 'A school can name you when it registers, or you can nominate a school.', 'ai-awareness-day' ),
			__( 'If its headteacher agrees, you get a dashboard of totals by emailed link.', 'ai-awareness-day' ),
		),
		'note'  => '',
	),
	array(
		'id'    => 'mats',
		'who'   => __( 'MATs (multi-academy trusts)', 'ai-awareness-day' ),
		'tag'   => __( 'From academy to academy', 'ai-awareness-day' ),
		'steps' => array(
			__( 'Each academy registers and is approved by its own headteacher.', 'ai-awareness-day' ),
			__( 'Hold debates between your own academies, then connect beyond the MAT.', 'ai-awareness-day' ),
			__( 'Ask the programme team for a report of your academies taking part.', 'ai-awareness-day' ),
		),
		'note'  => '',
	),
);

// Where each pathway reads on.
$path_links = array(
	'teachers'      => array( '#ncp-how', __( 'How an inter-school debate works', 'ai-awareness-day' ) ),
	'headteachers'  => array( '#ncp-approval', __( 'Why approval comes first', 'ai-awareness-day' ) ),
	'students'      => array( '#ncp-inside', __( 'Inside a debate', 'ai-awareness-day' ) ),
	'judges'        => array( '#ncp-safe', __( 'Judges and safeguarding', 'ai-awareness-day' ) ),
	'organisations' => array( '#ncp-partners', __( 'How organisations take part', 'ai-awareness-day' ) ),
	'mats'          => array( '#ncp-networks', __( 'MATs and communities', 'ai-awareness-day' ) ),
);

// Brief sections 4, 24.4 and 24.5, and the plugin's motion bank and prep pack: one idea, Safe and what AI should
// remember about you, as each age pathway meets it. The wording is the platform's own.
$age_rows = array(
	'question' => __( 'The question', 'ai-awareness-day' ),
	'support'  => __( 'Help to get going', 'ai-awareness-day' ),
	'take'     => __( 'How students take part', 'ai-awareness-day' ),
	'survey'   => __( 'The survey beforehand', 'ai-awareness-day' ),
	'research' => __( 'Research', 'ai-awareness-day' ),
	'day'      => __( 'On the day', 'ai-awareness-day' ),
);
$ages = array(
	array(
		'name'     => __( 'Primary', 'ai-awareness-day' ),
		'years'    => __( 'Mainly Years 5 and 6', 'ai-awareness-day' ),
		'question' => __( '“Your AI helper can remember things about you. Should it remember everything, some things or nothing?”', 'ai-awareness-day' ),
		'support'  => __( 'A sentence starter on display: “I think it should remember ___ because ___.”', 'ai-awareness-day' ),
		'take'     => __( 'Pupils move to a corner of the room or vote with a show of hands, then tell a talk partner why.', 'ai-awareness-day' ),
		'survey'   => __( 'A class vote or tally chart, with the teacher helping.', 'ai-awareness-day' ),
		'research' => __( 'Short fact cards, one fact to a card.', 'ai-awareness-day' ),
		'day'      => __( 'Shorter speeches, with more time for questions from the floor and the final vote.', 'ai-awareness-day' ),
	),
	array(
		'name'     => __( 'Secondary', 'ai-awareness-day' ),
		'years'    => __( 'Years 7 to 11', 'ai-awareness-day' ),
		'question' => __( '“An AI assistant remembers your conversations to give you more personal support. How much should it remember, and who should decide?”', 'ai-awareness-day' ),
		'support'  => __( 'A “What if…?” challenge card: “What if remembering more helped it notice when you were struggling?”', 'ai-awareness-day' ),
		'take'     => __( 'Small groups with talking roles, Builder, Challenger and Summariser, then the challenge card tests their view. Shorter for Key Stage 3.', 'ai-awareness-day' ),
		'survey'   => __( 'A short online form sent to a year group.', 'ai-awareness-day' ),
		'research' => __( 'Two or three sources they are given, plus one they find for themselves.', 'ai-awareness-day' ),
		'day'      => __( 'Three-minute opening cases, two-minute rebuttals, and questions from the floor.', 'ai-awareness-day' ),
	),
	array(
		'name'     => __( 'Post-16 and FE', 'ai-awareness-day' ),
		'years'    => __( 'Sixth form and college', 'ai-awareness-day' ),
		'question' => __( '“This house believes users, not companies, should control what AI remembers about them.”', 'ai-awareness-day' ),
		'support'  => __( 'The central tension, personalisation against privacy, and a research prompt: how does the ICO\'s Children\'s Code treat data about young people?', 'ai-awareness-day' ),
		'take'     => __( 'A formal debate, with points of information offered during the opening speeches.', 'ai-awareness-day' ),
		'survey'   => __( 'Students write their own questions and note the limits: sample size and leading wording.', 'ai-awareness-day' ),
		'research' => __( 'Independent research, with a note of how reliable each source is.', 'ai-awareness-day' ),
		'day'      => __( 'Longer opening cases, and a show of hands or a digital vote at the end.', 'ai-awareness-day' ),
	),
);

// Brief sections 11, 15 and 22, with decisions 23.3, 24.9 and 24.11: how organisations take part without
// signing schools up, and what they see.
$partner_points = array(
	array( __( 'Encourage your schools', 'ai-awareness-day' ), __( 'Tell the schools you work with. Each registers itself and is approved by its own headteacher: one school never signs up another, and neither do you.', 'ai-awareness-day' ) ),
	array( __( 'Be named, with permission', 'ai-awareness-day' ), __( 'A school can name your organisation, and a person there, when it registers, and its headteacher decides whether you are told. Or nominate a school you work with, and we send it one invitation.', 'ai-awareness-day' ) ),
	array( __( 'Lend your expertise', 'ai-awareness-day' ), __( 'Your people can judge a debate when a teacher at the host school invites them. There is no open call for judges.', 'ai-awareness-day' ) ),
	array( __( 'See the difference, not the data', 'ai-awareness-day' ), __( 'If a headteacher agrees, you get a dashboard by emailed link, with no account: schools, debates, judges and students reached, and Student Voice by age group once three schools have answered. No school, teacher or student is named.', 'ai-awareness-day' ) ),
);

// Brief section 24.5: the recommended 30-minute running order.
$running = array(
	array( __( '10 min', 'ai-awareness-day' ), __( 'Setting the scene', 'ai-awareness-day' ), __( 'A welcome, an opening vote from the room, and what the survey found.', 'ai-awareness-day' ) ),
	array( __( '20 min', 'ai-awareness-day' ), __( 'The debate', 'ai-awareness-day' ), __( 'Opening cases, time to prepare rebuttals, rebuttal, questions from the floor, closing speeches and a final vote.', 'ai-awareness-day' ) ),
);

$steps = array(
	array( __( 'Register your school', 'ai-awareness-day' ), __( 'A teacher gives their name, role and school email, and names the headteacher or senior leader who approves the school with one click. Nobody gets a password.', 'ai-awareness-day' ) ),
	array( __( 'Get a Debate ID', 'ai-awareness-day' ), __( 'Every debate has its own reference, for example AID-7K42P, that both schools and the judge use.', 'ai-awareness-day' ) ),
	array( __( 'Invite another school', 'ai-awareness-day' ), __( 'Send an invitation by email or share a link. No opponent yet? Join the conversation shows schools in your region, or online, looking for a debate. The school you invite registers and is approved by its own headteacher, and the match is made once both schools are approved.', 'ai-awareness-day' ) ),
	array( __( 'Agree the fixture', 'ai-awareness-day' ), __( 'Choose the date, online or in person, the theme, motion and a judge. Both teachers receive the Debate & Safeguarding Pack automatically, and calendar entries carry the directions or the meeting link.', 'ai-awareness-day' ) ),
	array( __( 'Hold the debate', 'ai-awareness-day' ), __( 'The judge scores on a phone or on a printed scorecard, because not every venue has reliable Wi-Fi. The judge\'s submission is the official result.', 'ai-awareness-day' ) ),
	array( __( 'Be recognised', 'ai-awareness-day' ), __( 'Taking part is recorded straight away. Two debates against two different schools earns the National AI Debate School certificate.', 'ai-awareness-day' ) ),
);

// Brief section 22: Classroom, then school, then school to school, then MAT, region and national.
$network = array( __( 'Classroom', 'ai-awareness-day' ), __( 'School', 'ai-awareness-day' ), __( 'School to school', 'ai-awareness-day' ), __( 'MAT', 'ai-awareness-day' ), __( 'Region', 'ai-awareness-day' ), __( 'National', 'ai-awareness-day' ) );

// Brief section 15. The professionals' line no longer asks for volunteer judges (decision 23.3), and the line for
// organisations asks nothing of them directly: they come in through their schools (decision 24.9).
$audiences = array(
	array( __( 'Young people', 'ai-awareness-day' ), __( 'AI is changing your future. You should have a say in it. Question it. Challenge it. Debate it.', 'ai-awareness-day' ), '' ),
	array( __( 'Teachers', 'ai-awareness-day' ), __( 'Start a meaningful conversation about AI without adding hours of administration.', 'ai-awareness-day' ), '' ),
	array( __( 'Headteachers', 'ai-awareness-day' ), __( 'Give your students a voice in one of the biggest conversations shaping their future.', 'ai-awareness-day' ), '' ),
	array( __( 'MAT leaders', 'ai-awareness-day' ), __( 'Turn individual school conversations into one powerful network-wide conversation about AI.', 'ai-awareness-day' ), '' ),
	array( __( 'Parents and carers', 'ai-awareness-day' ), __( 'Young people need opportunities to think critically about AI, not simply use it.', 'ai-awareness-day' ), '' ),
	array( __( 'Organisations that work with schools', 'ai-awareness-day' ), __( 'Take learning beyond the resource: help schools apply what they have learned, challenge one another and contribute nationally.', 'ai-awareness-day' ), '' ),
	array( __( 'Employers and technology organisations', 'ai-awareness-day' ), __( 'Don\'t just tell young people about the future of AI. Listen to them.', 'ai-awareness-day' ), '' ),
	array( __( 'Professionals and community leaders', 'ai-awareness-day' ), __( 'Give young people your attention, not a lecture.', 'ai-awareness-day' ), __( 'Judges are invited by a teacher at the host school, never through an open call.', 'ai-awareness-day' ) ),
	array( __( 'Policymakers and researchers', 'ai-awareness-day' ), __( 'Listen to what young people are actually saying about AI, through structured insight that is properly governed.', 'ai-awareness-day' ), '' ),
);

$timeline = array(
	array( __( 'Now to December 2026', 'ai-awareness-day' ), __( 'Getting ready. The platform is tested with a first group of schools while resources and safeguarding guidance are written.', 'ai-awareness-day' ) ),
	array( $opens_long, __( 'The National Conversation opens. Schools can begin classroom conversations, Student Voice activities and debates.', 'ai-awareness-day' ) ),
	array( __( 'January to March', 'ai-awareness-day' ), __( 'School debates, inter-school debates, MAT activity and Student Voice.', 'ai-awareness-day' ) ),
	array( __( 'April', 'ai-awareness-day' ), __( 'Final debates, local and regional events, and the first national findings taking shape.', 'ai-awareness-day' ) ),
	array( $event_long, __( 'AI Awareness Day: Humans in the Loop. The national culmination, with the most engaged schools, the national findings and a flagship debate.', 'ai-awareness-day' ) ),
);

$faqs = array(
	array( __( 'Do students need an account?', 'ai-awareness-day' ), __( 'No. Students scan a QR code and enter a class PIN. They give no name and create no account.', 'ai-awareness-day' ) ),
	array( __( 'Do we need our headteacher\'s approval?', 'ai-awareness-day' ), __( 'Yes. Every school is approved by its headteacher or a senior leader before it can run a class PIN, start or join a debate, or use Join the conversation. It takes one click from one email, and the lead teacher can send the email again if it is missed.', 'ai-awareness-day' ) ),
	array( __( 'Can more than one teacher at our school take part?', 'ai-awareness-day' ), __( 'Yes. Colleagues with the school\'s email address join straight away; anyone else is approved by the lead teacher. Every debate counts towards one school total and one certificate.', 'ai-awareness-day' ) ),
	array( __( 'Do teachers need a password?', 'ai-awareness-day' ), __( 'No. Teachers, headteachers and judges sign in with their school code and their own email address, and we email a six-digit code.', 'ai-awareness-day' ) ),
	array( __( 'Can we take part without another school to debate?', 'ai-awareness-day' ), __( 'Yes. Classroom Conversations and School Debates need only your own school, and Join the conversation helps you find a school for an inter-school debate.', 'ai-awareness-day' ) ),
	array( __( 'Can we debate online?', 'ai-awareness-day' ), __( 'Yes. The host school uses its own video platform and adds the meeting link, so it is on the debate page on the day. The safeguarding pack asks both schools to confirm that the host controls who joins and that nobody records the debate.', 'ai-awareness-day' ) ),
	array( __( 'Who is responsible for safeguarding?', 'ai-awareness-day' ), __( 'Each school, under its own policies. The platform connects schools and gives every teacher a checklist; it does not replace a school\'s own procedures.', 'ai-awareness-day' ) ),
	array(
		__( 'What happens to our details?', 'ai-awareness-day' ),
		sprintf(
			/* translators: %s: deletion date */
			__( 'Names and email addresses of teachers, headteachers and judges are deleted after the campaign ends on %s, or sooner if you ask us. Student answers are anonymous. Totals and results stay, without anyone\'s contact details.', 'ai-awareness-day' ),
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
		<div class="container ncp-lede">
			<figure class="ncp-photo ncp-photo--tall">
				<img src="<?php echo esc_url( AIAD_URI . '/assets/images/national-conversation/classroom-hand-up.jpg' ); ?>" width="900" height="1359" alt="<?php esc_attr_e( 'A pupil presents her work to the class while a classmate raises a hand to ask a question', 'ai-awareness-day' ); ?>" loading="lazy" decoding="async">
				<figcaption><?php esc_html_e( 'Photo: Taylor Flowe on Unsplash', 'ai-awareness-day' ); ?></figcaption>
			</figure>
			<div class="ncp-lede__text">
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
			<h3 class="ncp-subhead" id="ncp-skills"><?php esc_html_e( 'The skills we want young people to build in the age of AI', 'ai-awareness-day' ); ?></h3>
			<dl class="ncp-skills">
				<?php foreach ( $skills as $skill ) : ?>
					<div>
						<dt><?php echo esc_html( $skill[0] ); ?></dt>
						<dd><?php echo esc_html( $skill[1] ); ?></dd>
					</div>
				<?php endforeach; ?>
			</dl>
			<p><?php esc_html_e( 'It is designed to be easy to join. Start with five minutes in a lesson, or connect with another school for a structured debate. Nobody needs an account or a password.', 'ai-awareness-day' ); ?> <strong><?php esc_html_e( 'Maximum participation. Minimum administration.', 'ai-awareness-day' ); ?></strong></p>
			<blockquote class="ncp-principle">
				<p><?php esc_html_e( 'The technology connects the conversation. Young people do the thinking.', 'ai-awareness-day' ); ?></p>
			</blockquote>
			</div>
		</div>
	</section>

	<section class="ncp-section ncp-section--card" aria-labelledby="ncp-paths">
		<div class="container">
			<h2 id="ncp-paths"><?php esc_html_e( 'Find your pathway', 'ai-awareness-day' ); ?></h2>
			<p class="ncp-intro"><?php esc_html_e( 'Every school gets one school code. Students add a class PIN; adults add their own email address and a six-digit code we send them. Nobody has a password.', 'ai-awareness-day' ); ?></p>
			<ul class="ncp-paths">
				<?php foreach ( $paths as $path ) : ?>
					<li>
						<h3><?php echo esc_html( $path['who'] ); ?></h3>
						<p class="ncp-paths__tag"><?php echo esc_html( $path['tag'] ); ?></p>
						<ol>
							<?php foreach ( $path['steps'] as $path_step ) : ?>
								<li><?php echo esc_html( $path_step ); ?></li>
							<?php endforeach; ?>
						</ol>
						<?php if ( $path['note'] ) : ?>
							<p class="ncp-paths__note"><?php echo esc_html( $path['note'] ); ?></p>
						<?php endif; ?>
						<?php if ( isset( $path_links[ $path['id'] ] ) ) : ?>
							<p class="ncp-paths__more"><a href="<?php echo esc_attr( $path_links[ $path['id'] ][0] ); ?>"><?php echo esc_html( $path_links[ $path['id'] ][1] ); ?></a></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<section class="ncp-section" aria-labelledby="ncp-themes">
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

	<section class="ncp-section ncp-section--card" aria-labelledby="ncp-ways">
		<div class="container">
			<h2 id="ncp-ways"><?php esc_html_e( 'Three ways to take part', 'ai-awareness-day' ); ?></h2>
			<figure class="ncp-photo ncp-photo--wide ncp-photo--classroom">
				<img src="<?php echo esc_url( AIAD_URI . '/assets/images/national-conversation/classroom-listening.jpg' ); ?>" width="1400" height="927" alt="<?php esc_attr_e( 'Pupils at their desks, seen from the back of the classroom, listening to their teacher', 'ai-awareness-day' ); ?>" loading="lazy" decoding="async">
				<figcaption><?php esc_html_e( 'Photo: Taylor Flowe on Unsplash', 'ai-awareness-day' ); ?></figcaption>
			</figure>
			<ol class="ncp-ways">
				<?php foreach ( $ways as $way ) : ?>
					<li>
						<h3><?php echo esc_html( $way[0] ); ?></h3>
						<p><?php echo esc_html( $way[1] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
			<p class="ncp-note"><?php esc_html_e( 'Each works at primary, secondary and post-16, with questions pitched for the age.', 'ai-awareness-day' ); ?> <a class="ncp-link" href="#ncp-ages"><?php esc_html_e( 'See how each age debates', 'ai-awareness-day' ); ?></a></p>
		</div>
	</section>

	<section class="ncp-section" aria-labelledby="ncp-ages">
		<div class="container">
			<h2 id="ncp-ages"><?php esc_html_e( 'One conversation, pitched for every age', 'ai-awareness-day' ); ?></h2>
			<p class="ncp-intro"><?php esc_html_e( 'Primary, secondary and post-16 students debate the same five themes. What changes is how the question is asked, how they prepare and how the debate runs. Here is one idea from the Safe theme, what AI should remember about you, as each age meets it.', 'ai-awareness-day' ); ?></p>
			<ul class="ncp-agecards">
				<?php foreach ( $ages as $age ) : ?>
					<li>
						<h3><?php echo esc_html( $age['name'] ); ?></h3>
						<p class="ncp-agecards__years"><?php echo esc_html( $age['years'] ); ?></p>
						<?php foreach ( $age_rows as $row_key => $row_label ) : ?>
							<div class="ncp-agecards__row ncp-agecards__row--<?php echo esc_attr( $row_key ); ?>">
								<h4><?php echo esc_html( $row_label ); ?></h4>
								<p><?php echo esc_html( $age[ $row_key ] ); ?></p>
							</div>
						<?php endforeach; ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<p><?php esc_html_e( 'Every age has the same 30 minutes and is judged on the same four things, in simpler words for primary, and every theme has a question written for each age. The questions, timings and wording are being reviewed by debate educators before the conversation opens.', 'ai-awareness-day' ); ?></p>
		</div>
	</section>

	<section class="ncp-section ncp-section--card" aria-labelledby="ncp-inside">
		<div class="container">
			<h2 id="ncp-inside"><?php esc_html_e( 'Inside a debate', 'ai-awareness-day' ); ?></h2>
			<p class="ncp-intro"><?php esc_html_e( 'About 30 minutes, and the whole class takes part, not just the speakers.', 'ai-awareness-day' ); ?></p>
			<figure class="ncp-photo ncp-photo--wide ncp-photo--writing">
				<img src="<?php echo esc_url( AIAD_URI . '/assets/images/national-conversation/student-writing.jpg' ); ?>" width="1400" height="933" alt="<?php esc_attr_e( 'A student in a hall of red seats writes notes on a sheet of paper', 'ai-awareness-day' ); ?>" loading="lazy" decoding="async">
				<figcaption><?php esc_html_e( 'Photo: Kate Tweedy on Unsplash', 'ai-awareness-day' ); ?></figcaption>
			</figure>
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
			<div class="ncp-callout" id="ncp-approval">
				<h3><?php esc_html_e( 'Headteacher approval comes first', 'ai-awareness-day' ); ?></h3>
				<p><?php esc_html_e( 'Every school taking part, including the one you invite, is approved by its headteacher or a senior leader before anything goes live. They get one email and approve with one click. Until then a school cannot run a class PIN for students, start or join a debate, or see Join the conversation. If the email is missed, the lead teacher can send it again.', 'ai-awareness-day' ); ?></p>
			</div>
			<ol class="ncp-steps">
				<?php foreach ( $steps as $step ) : ?>
					<li>
						<h3><?php echo esc_html( $step[0] ); ?></h3>
						<p><?php echo str_replace( 'AID-7K42P', '<span class="ncp-nowrap">AID-7K42P</span>', esc_html( $step[1] ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped, then the example ID is wrapped. ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
			<p class="ncp-tally"><?php esc_html_e( 'One Debate ID. Two school leads. One judge. One result.', 'ai-awareness-day' ); ?></p>
			<p class="ncp-note"><?php esc_html_e( 'If a step stalls, we send reminders after 3 and 7 days. A debate with no action for 14 days expires, and the school can invite another.', 'ai-awareness-day' ); ?></p>
		</div>
	</section>

	<section class="ncp-section ncp-section--card" aria-labelledby="ncp-safe">
		<div class="container ncp-split">
			<div>
				<h2 id="ncp-safe"><?php esc_html_e( 'Schools stay in charge', 'ai-awareness-day' ); ?></h2>
				<p><?php esc_html_e( 'The platform connects schools. Each school remains responsible for its own safeguarding, supervision, travel, parental permissions, risk assessments and visitor arrangements.', 'ai-awareness-day' ); ?></p>
				<p><?php esc_html_e( 'When two schools are matched, both teachers automatically receive our Debate & Safeguarding Pack to help them prepare.', 'ai-awareness-day' ); ?></p>
				<p><?php esc_html_e( 'Every debate has an independent judge, such as a professional, technologist, academic or community leader. Judges are invited by the host teacher, never through an open call, and the host school\'s own visitor procedures apply.', 'ai-awareness-day' ); ?></p>
				<p><?php esc_html_e( 'If something goes wrong, either school can report it: a cancellation, a conduct or safeguarding concern, or a result that does not reflect what happened. Both schools\' senior leaders are told, the result is held, and the report stays private. Schools resolve it under their own procedures.', 'ai-awareness-day' ); ?></p>
			</div>
			<aside class="ncp-conduct" aria-labelledby="ncp-conduct">
				<h3 id="ncp-conduct"><?php esc_html_e( 'National Debate Code of Conduct', 'ai-awareness-day' ); ?></h3>
				<p class="ncp-conduct__line"><?php esc_html_e( 'Challenge the argument. Respect the person.', 'ai-awareness-day' ); ?></p>
				<p><?php esc_html_e( 'It applies to students, teachers, judges, visitors and audiences. Strong disagreement is encouraged; personal attacks, discriminatory language, intimidation, deliberate disruption and abusive audience behaviour are not.', 'ai-awareness-day' ); ?></p>
			</aside>
		</div>
	</section>

	<section class="ncp-section" aria-labelledby="ncp-recognition">
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
			<p><?php esc_html_e( 'The programme rewards conversation, connection and breadth: the schools you meet, the students involved and the themes explored. MATs and regions can run competitive events of their own, but there is no national league table of winners.', 'ai-awareness-day' ); ?></p>
			<p><?php esc_html_e( 'Results are public: both schools, the theme, the motion, the date, the result and, if they agree, the judge\'s name. Teachers\' names and anything about students are never published. Every certificate carries a reference that anyone can check.', 'ai-awareness-day' ); ?></p>
		</div>
	</section>

	<section class="ncp-section ncp-section--card" aria-labelledby="ncp-networks">
		<div class="container ncp-narrow">
			<h2 id="ncp-networks"><?php esc_html_e( 'Whole MATs and communities can join in', 'ai-awareness-day' ); ?></h2>
			<p class="ncp-chain" aria-label="<?php esc_attr_e( 'Classroom, then school, then school to school, then MAT, then region, then national', 'ai-awareness-day' ); ?>">
				<?php foreach ( $network as $level ) : ?>
					<span><?php echo esc_html( $level ); ?></span>
				<?php endforeach; ?>
			</p>
			<p><?php esc_html_e( 'A multi-academy trust (MAT) can hold debates between its own academies before connecting with schools elsewhere. Each academy registers and is approved by its own headteacher, and the programme team can share a report of the MAT\'s academies taking part.', 'ai-awareness-day' ); ?></p>
		</div>
	</section>

	<section class="ncp-section" aria-labelledby="ncp-partners">
		<div class="container">
			<h2 id="ncp-partners"><?php esc_html_e( 'For organisations that bring AI into schools', 'ai-awareness-day' ); ?></h2>
			<p class="ncp-motto"><?php esc_html_e( 'Take learning beyond the resource.', 'ai-awareness-day' ); ?></p>
			<p class="ncp-intro"><?php esc_html_e( 'If your organisation supports schools with AI lessons, resources or programmes, this is the natural next step: students put what they have learned to work, testing their thinking against peers from another school, in front of an audience and an independent judge.', 'ai-awareness-day' ); ?></p>
			<ul class="ncp-partner">
				<?php foreach ( $partner_points as $point ) : ?>
					<li>
						<h3><?php echo esc_html( $point[0] ); ?></h3>
						<p><?php echo esc_html( $point[1] ); ?></p>
					</li>
				<?php endforeach; ?>
			</ul>
			<p><?php esc_html_e( 'Whoever brings a school along, the school always confirms its own place. Every email to an organisation lets it stop the emails, or say “this wasn\'t us”.', 'ai-awareness-day' ); ?></p>
			<?php if ( $nominate_url ) : ?>
				<p><a class="ncp-link" href="<?php echo esc_url( $nominate_url ); ?>"><?php esc_html_e( 'Work with a school? Nominate it', 'ai-awareness-day' ); ?></a></p>
			<?php endif; ?>
			<div class="ncp-callout ncp-callout--invite">
				<h3><?php esc_html_e( 'Interested in supporting the national debate?', 'ai-awareness-day' ); ?></h3>
				<p><?php esc_html_e( 'Don\'t hesitate to reach out to us. A few partnership places remain for organisations that want to help with:', 'ai-awareness-day' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Powering the platform that hosts the debates', 'ai-awareness-day' ); ?></li>
					<li><?php esc_html_e( 'Keynote talks from technology companies at debates and events', 'ai-awareness-day' ); ?></li>
					<li><?php esc_html_e( 'Guest speakers for schools serving disadvantaged communities', 'ai-awareness-day' ); ?></li>
					<li><?php esc_html_e( 'Hosting debates and events for those schools', 'ai-awareness-day' ); ?></li>
				</ul>
				<p class="ncp-callout__small"><?php esc_html_e( 'Partners support the programme, not individual schools. They never see schools\', staff or students\' details, and guest speakers visit under each school\'s own visitor procedures.', 'ai-awareness-day' ); ?></p>
				<p class="ncp-actions"><a class="ncp-btn ncp-btn--primary" href="<?php echo esc_url( home_url( '/#contact' ) ); ?>"><?php esc_html_e( 'Get in touch', 'ai-awareness-day' ); ?></a></p>
			</div>
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
