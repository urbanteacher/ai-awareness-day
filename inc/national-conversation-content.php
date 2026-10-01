<?php
/**
 * The words of the National Conversation page: page-national-conversation.php prints them, and
 * patterns/national-conversation.php turns them into the blocks of the editable page.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The page's lists, in the order the page shows them.
 *
 * @param string $opens_long The day the conversation opens, as the page writes it.
 * @param string $event_long AI Awareness Day, as the page writes it.
 * @param string $retention  The day contact details are deleted.
 * @return array<string, array>
 */
function aiad_national_conversation_content( string $opens_long, string $event_long, string $retention ): array {
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

	return compact( 'journey', 'skills', 'themes', 'ways', 'paths', 'path_links', 'age_rows', 'ages', 'partner_points', 'running', 'steps', 'network', 'audiences', 'timeline', 'faqs' );
}
