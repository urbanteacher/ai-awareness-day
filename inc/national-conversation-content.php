<?php
/**
 * The words of the National Conversation page: patterns/national-conversation.php turns them into the blocks of the
 * editable page. inc/resource-lesson.php reads the age pathways' 'take' and 'day' lines too.
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
	// Aims, objectives and ambitions (brief sections 1, 12 and 13). The objectives name what the platform counts;
	// no numeric targets have been set.
	$aims = array(
		__( 'Build the skills to weigh evidence, argue a case and disagree respectfully.', 'ai-awareness-day' ),
		__( 'Connect schools with others beyond their own trust or region.', 'ai-awareness-day' ),
		__( 'Bring professionals and community leaders into debates as independent judges.', 'ai-awareness-day' ),
		__( 'Record what young people taking part think about AI, and report it.', 'ai-awareness-day' ),
	);
	$objectives = array(
		__( 'Debates on all five themes at primary, secondary and post-16.', 'ai-awareness-day' ),
		__( 'Schools debating schools beyond their own trust or region.', 'ai-awareness-day' ),
		__( 'Student Voice results for each school once 10 of its students have answered.', 'ai-awareness-day' ),
		sprintf( /* translators: %s: AI Awareness Day date */ __( 'National findings on %s, describing only the students who took part.', 'ai-awareness-day' ), $event_long ),
	);
	$ambitions = array(
		__( 'Run every year, and track how young people\'s views on AI change.', 'ai-awareness-day' ),
		__( 'Make young people\'s views on AI heard by schools, employers and policymakers.', 'ai-awareness-day' ),
	);

	// The young person's journey (adapted from brief section 22).
	$journey = array( __( 'Learn', 'ai-awareness-day' ), __( 'Think', 'ai-awareness-day' ), __( 'Discuss', 'ai-awareness-day' ), __( 'Rebut', 'ai-awareness-day' ), __( 'Reflect', 'ai-awareness-day' ) );

	// What taking part develops (brief section 1).
	$skills = array(
		array( __( 'Oracy & Communication', 'ai-awareness-day' ), __( 'Speaking fluently and confidently about complex ideas, including to an audience.', 'ai-awareness-day' ) ),
		array( __( 'Critical Thinking', 'ai-awareness-day' ), __( 'Evaluating arguments logically and spotting cognitive biases.', 'ai-awareness-day' ) ),
		array( __( 'Information Literacy', 'ai-awareness-day' ), __( 'Examining evidence and verifying facts.', 'ai-awareness-day' ) ),
		array( __( 'Active Listening & Empathy', 'ai-awareness-day' ), __( 'Hearing other people out and understanding their point of view.', 'ai-awareness-day' ) ),
		array( __( 'Independent Judgement', 'ai-awareness-day' ), __( 'Forming views backed by reasons, not peer pressure.', 'ai-awareness-day' ) ),
		array( __( 'Civil Discourse', 'ai-awareness-day' ), __( 'Disagreeing respectfully and defusing tension by adapting tone and voice.', 'ai-awareness-day' ) ),
	);

	$ways = array(
		array( __( 'Classroom Conversation', 'ai-awareness-day' ), __( 'Five-minute starters, questions and free resources to get a class talking about AI.', 'ai-awareness-day' ) ),
		array( __( 'School Debate', 'ai-awareness-day' ), __( 'Class against class, house against house or year against year. Build confidence inside your own school first.', 'ai-awareness-day' ) ),
		array( __( 'National Debate Network', 'ai-awareness-day' ), __( 'Debate another school, in person or online, with an independent judge.', 'ai-awareness-day' ) ),
	);

	$themes = array(
		'safe'        => array( __( 'Safe', 'ai-awareness-day' ), __( 'Privacy, security, trust, bias and AI memory.', 'ai-awareness-day' ) ),
		'smart'       => array( __( 'Smart', 'ai-awareness-day' ), __( 'Learning, critical thinking, checking facts and relying on AI.', 'ai-awareness-day' ) ),
		'creative'    => array( __( 'Creative', 'ai-awareness-day' ), __( 'Imagination, originality, authorship and ownership.', 'ai-awareness-day' ) ),
		'responsible' => array( __( 'Responsible', 'ai-awareness-day' ), __( 'Accountability, fairness, sustainability, consequences and permission to use people\'s work.', 'ai-awareness-day' ) ),
		'future'      => array( __( 'Future', 'ai-awareness-day' ), __( 'Jobs, skills, opportunities and young people\'s say in decisions about AI.', 'ai-awareness-day' ) ),
	);

	// Brief sections 4 and 24.5: one Safe idea as each age meets it. The page shows these rows; resource pages also
	// read 'take' and 'day'.
	$age_rows = array(
		'question' => __( 'The question', 'ai-awareness-day' ),
		'take'     => __( 'How students take part', 'ai-awareness-day' ),
	);
	$ages = array(
		array(
			'name'     => __( 'Primary', 'ai-awareness-day' ),
			'years'    => __( 'Mainly Years 5 and 6', 'ai-awareness-day' ),
			'question' => __( '“Your AI helper can remember things about you. Should it remember everything, some things or nothing?”', 'ai-awareness-day' ),
			'take'     => __( 'Pupils move to a corner of the room or vote with a show of hands, then tell a talk partner why.', 'ai-awareness-day' ),
			'day'      => __( 'Shorter speeches, with more time for questions from the floor and the final vote.', 'ai-awareness-day' ),
		),
		array(
			'name'     => __( 'Secondary', 'ai-awareness-day' ),
			'years'    => __( 'Years 7 to 11', 'ai-awareness-day' ),
			'question' => __( '“An AI assistant remembers your conversations to give you more personal support. How much should it remember, and who should decide?”', 'ai-awareness-day' ),
			'take'     => __( 'Small groups take talking roles, then test their view with a “What if…?” challenge card.', 'ai-awareness-day' ),
			'day'      => __( 'Three-minute opening cases, two-minute rebuttals, and questions from the floor.', 'ai-awareness-day' ),
		),
		array(
			'name'     => __( 'Post-16 and FE', 'ai-awareness-day' ),
			'years'    => __( 'Sixth form and college', 'ai-awareness-day' ),
			'question' => __( '“This house believes users, not companies, should control what AI remembers about them.”', 'ai-awareness-day' ),
			'take'     => __( 'A formal debate, with points of information offered during the opening speeches.', 'ai-awareness-day' ),
			'day'      => __( 'Longer opening cases, and a show of hands or a digital vote at the end.', 'ai-awareness-day' ),
		),
	);

	// Brief section 24.5: the recommended 30-minute running order.
	$running = array(
		array( __( '10 min', 'ai-awareness-day' ), __( 'Setting the scene', 'ai-awareness-day' ), __( 'A welcome, an opening vote from the room, and what the survey found.', 'ai-awareness-day' ) ),
		array( __( '20 min', 'ai-awareness-day' ), __( 'The debate', 'ai-awareness-day' ), __( 'Opening cases, rebuttals, questions from the floor, closing speeches and a final vote.', 'ai-awareness-day' ) ),
	);

	$steps = array(
		array( __( 'Register your school', 'ai-awareness-day' ), __( 'Name the headteacher or senior leader who will approve your school. Nobody gets a password.', 'ai-awareness-day' ) ),
		array( __( 'Get a Debate ID', 'ai-awareness-day' ), __( 'Every debate has its own reference, for example AID-7K42P, that both schools and the judge use.', 'ai-awareness-day' ) ),
		array( __( 'Invite another school', 'ai-awareness-day' ), __( 'Invite a school you know by email or link, or find one on Join the conversation.', 'ai-awareness-day' ) ),
		array( __( 'Agree the fixture', 'ai-awareness-day' ), __( 'Choose the date, theme, motion and judge, and whether it is online or in person.', 'ai-awareness-day' ) ),
		array( __( 'Hold the debate', 'ai-awareness-day' ), __( 'The judge scores on a phone, or on paper and then online. Their online entry is the official result.', 'ai-awareness-day' ) ),
		array( __( 'Be recognised', 'ai-awareness-day' ), __( 'Two completed debates against two different schools earn the National AI Debate School certificate.', 'ai-awareness-day' ) ),
	);

	// Brief section 22: classroom, then school, then school to school, then MAT, region and national.
	$network = array( __( 'Classroom', 'ai-awareness-day' ), __( 'School', 'ai-awareness-day' ), __( 'School to school', 'ai-awareness-day' ), __( 'MAT', 'ai-awareness-day' ), __( 'Region', 'ai-awareness-day' ), __( 'National', 'ai-awareness-day' ) );

	// Brief sections 11 and 22, with decisions 23.3, 24.9 and 24.11: organisations encourage schools, never enrol them.
	$partner_points = array(
		array( __( 'Encourage your schools', 'ai-awareness-day' ), __( 'Tell the schools you work with. Each registers itself and is approved by its own headteacher. You never sign a school up.', 'ai-awareness-day' ) ),
		array( __( 'Be named, with permission', 'ai-awareness-day' ), __( 'A school can name you when it registers. You are told only if its headteacher agrees. Or nominate a school you work with, and we send it one invitation.', 'ai-awareness-day' ) ),
		array( __( 'Lend your expertise', 'ai-awareness-day' ), __( 'Your people can judge a debate if a teacher at one of the schools invites them.', 'ai-awareness-day' ) ),
		array( __( 'See the difference, not the data', 'ai-awareness-day' ), __( 'If a headteacher agrees, we email you a dashboard link. It shows totals only: schools, debates, judges and students reached. Student Voice by age group appears once 10 students from three schools have answered.', 'ai-awareness-day' ) ),
	);

	$timeline = array(
		array( __( 'November and December 2026', 'ai-awareness-day' ), __( 'A first group of schools tries the platform, and we make changes before the conversation opens.', 'ai-awareness-day' ) ),
		array( $opens_long, __( 'The National Conversation opens. Schools can start classroom conversations, the Student Voice survey and debates.', 'ai-awareness-day' ) ),
		array( __( 'January to April 2027', 'ai-awareness-day' ), __( 'Debates within and between schools. MATs and regions can run their own events.', 'ai-awareness-day' ) ),
		array( $event_long, __( 'AI Awareness Day: Humans in the Loop. We share the national findings and hold a flagship debate.', 'ai-awareness-day' ) ),
	);

	$faqs = array(
		array( __( 'How much time does it take?', 'ai-awareness-day' ), __( 'A classroom starter takes five minutes. A debate takes about 30 minutes, plus a short survey two to three weeks before.', 'ai-awareness-day' ) ),
		array( __( 'Does anyone need an account or a password?', 'ai-awareness-day' ), __( 'No. Students scan a QR code or type the school code, then type the class PIN, and give no name. Adults sign in with the school code, their own email address and a six-digit code we send them.', 'ai-awareness-day' ) ),
		array( __( 'Can more than one teacher at our school take part?', 'ai-awareness-day' ), __( 'Yes. Colleagues with the school\'s email address join straight away, and the lead teacher approves anyone else. Every debate counts towards one school total and one certificate.', 'ai-awareness-day' ) ),
		array( __( 'Can we debate online?', 'ai-awareness-day' ), __( 'Yes. The host school uses its own video platform and adds the meeting link to the debate page. The safeguarding checklist reminds both schools that the host controls who joins and that nobody records the debate.', 'ai-awareness-day' ) ),
		array( __( 'What does our school get?', 'ai-awareness-day' ), __( 'Two debates against two different schools earn the National AI Debate School certificate. Once 10 of your students have answered the Student Voice survey, you also get a one-page School AI Snapshot of their views.', 'ai-awareness-day' ) ),
		array(
			__( 'What happens to our details?', 'ai-awareness-day' ),
			sprintf(
				/* translators: %s: deletion date */
				__( 'We delete the names and email addresses of teachers, headteachers and judges after the campaign ends on %s, or sooner if you ask. A judge who agreed to be named stays named on their results. Student answers are anonymous. Results and totals stay.', 'ai-awareness-day' ),
				$retention
			),
		),
	);

	return compact( 'aims', 'objectives', 'ambitions', 'journey', 'skills', 'ways', 'themes', 'age_rows', 'ages', 'running', 'steps', 'network', 'partner_points', 'timeline', 'faqs' );
}
