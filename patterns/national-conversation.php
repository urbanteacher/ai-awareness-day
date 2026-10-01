<?php
/**
 * Title: National Conversation page
 * Slug: aiad/national-conversation
 * Categories: aiad-pages
 * Post Types: page
 * Block Types: core/post-content
 * Keywords: national conversation, debate, schools, landing page
 * Description: The National AI Conversation landing page in blocks, so all of its text is edited on the page. Starts with the wording of page-national-conversation.php.
 *
 * The words come from aiad_national_conversation_content(), the same lists page-national-conversation.php prints.
 * Everything sits in one main group (main#main.ncp, as on the PHP page; templates/page-national-conversation.html
 * has only the header, the post content and the footer). Text is core blocks; lists whose items hold more than text are Styled list, Styled list item and Styled text blocks
 * (blocks/list, item, text), saved as plain HTML with the page's classes, so national-conversation.css styles them
 * as it styles the PHP page. What changes on its own is filled in when the page renders
 * (aiad_national_conversation_placeholders()): {opens}, {event}, {event_year}, {opens_label} and {retention}. The
 * buttons, which change on the day the conversation opens, are a block of their own (blocks/nc-actions), and so is
 * each strand's mark (blocks/strand-icon).
 *
 * @package AI_Awareness_Day
 */

[
	'journey'        => $aiad_journey,
	'skills'         => $aiad_skills,
	'themes'         => $aiad_themes,
	'ways'           => $aiad_ways,
	'paths'          => $aiad_paths,
	'path_links'     => $aiad_path_links,
	'age_rows'       => $aiad_age_rows,
	'ages'           => $aiad_ages,
	'partner_points' => $aiad_partner_points,
	'running'        => $aiad_running,
	'steps'          => $aiad_steps,
	'network'        => $aiad_network,
	'audiences'      => $aiad_audiences,
	'timeline'       => $aiad_timeline,
	'faqs'           => $aiad_faqs,
] = aiad_national_conversation_content( '{opens}', '{event}', '{retention}' );

// Block markup: inc/block-markup.php. The variables are prefixed because WordPress includes pattern files inside
// its own functions.
$aiad_image = static fn( string $file, string $alt, string $caption, string $class ): string => aiad_block_image( AIAD_URI . '/assets/images/national-conversation/' . $file, $alt, $caption, $class );

$aiad_section = static fn( array $inner, string $name, bool $card = false, string $container = 'container' ): string => aiad_block_group( array( aiad_block_group( $inner, $container ) ), 'ncp-section' . ( $card ? ' ncp-section--card' : '' ), 'section', '', $name );

$aiad_photo_credit = static fn( string $who ): string => sprintf( /* translators: %s: photographer */ __( 'Photo: %s on Unsplash', 'ai-awareness-day' ), $who );

/* ------------------------------------------------------------------ The page */

$aiad_out = array();

// Hero.
$aiad_out[] = aiad_block_group(
	array(
		aiad_block_group(
			array(
				aiad_block_paragraph( aiad_block_text( __( 'AI Awareness Day', 'ai-awareness-day' ) ) . ' {event_year} <span aria-hidden="true">·</span> ' . aiad_block_text( __( 'Humans in the Loop', 'ai-awareness-day' ) ), 'ncp-eyebrow' ),
				aiad_block_heading( 1, aiad_block_text( __( 'The National AI Conversation', 'ai-awareness-day' ) ), 'ncp-title', 'ncp-title' ),
				aiad_block_paragraph( aiad_block_text( __( 'A national conversation about AI, led by young people. Question it. Challenge it. Debate it.', 'ai-awareness-day' ) ), 'ncp-lead' ),
				aiad_block_styled_list(
					array(
						aiad_block_styled_item( array( aiad_block_styled_text( '{opens_label}', 'dt' ), aiad_block_styled_text( '{opens}', 'dd' ) ), '', 'div' ),
						aiad_block_styled_item( array( aiad_block_styled_text( aiad_block_text( __( 'AI Awareness Day', 'ai-awareness-day' ) ), 'dt' ), aiad_block_styled_text( '{event}', 'dd' ) ), '', 'div' ),
					),
					'ncp-dates',
					'dl'
				),
				'<!-- wp:aiad/nc-actions /-->',
			),
			'container'
		),
	),
	'ncp-hero',
	'section',
	'',
	__( 'Hero', 'ai-awareness-day' )
);

// What it is.
$aiad_out[] = aiad_block_group(
	array(
		aiad_block_group(
			array(
				$aiad_image( 'classroom-hand-up.jpg', __( 'A pupil presents her work to the class while a classmate raises a hand to ask a question', 'ai-awareness-day' ), $aiad_photo_credit( 'Taylor Flowe' ), 'ncp-photo ncp-photo--tall' ),
				aiad_block_group(
					array(
						aiad_block_heading( 2, aiad_block_text( __( 'Bigger than a single awareness day', 'ai-awareness-day' ) ), '', 'ncp-what' ),
						aiad_block_paragraph( aiad_block_text( sprintf( /* translators: 1: opening date, 2: AI Awareness Day date */ __( 'From %1$s, schools can question, discuss and debate the role AI should play in their lives and futures, building up to AI Awareness Day on %2$s.', 'ai-awareness-day' ), '{opens}', '{event}' ) ), 'ncp-big' ),
						aiad_block_paragraph( aiad_block_text( __( 'Young people will not simply be taught about AI. They will listen to different perspectives and form their own judgements.', 'ai-awareness-day' ) ) ),
						aiad_block_paragraph( aiad_block_text( __( 'Every student\'s journey', 'ai-awareness-day' ) ), 'ncp-label', 'ncp-journey' ),
						aiad_block_styled_list( array_map( static fn( string $stage ): string => aiad_block_styled_text( aiad_block_text( $stage ), 'li' ), $aiad_journey ), 'ncp-arc', 'ol', array( 'aria-labelledby' => 'ncp-journey' ) ),
						aiad_block_heading( 3, aiad_block_text( __( 'The skills we want young people to build in the age of AI', 'ai-awareness-day' ) ), 'ncp-subhead', 'ncp-skills' ),
						aiad_block_styled_list( array_map( static fn( array $skill ): string => aiad_block_styled_item( array( aiad_block_styled_text( aiad_block_text( $skill[0] ), 'dt' ), aiad_block_styled_text( aiad_block_text( $skill[1] ), 'dd' ) ), '', 'div' ), $aiad_skills ), 'ncp-skills', 'dl' ),
						aiad_block_paragraph( aiad_block_text( __( 'It is designed to be easy to join. Start with five minutes in a lesson, or connect with another school for a structured debate. Nobody needs an account or a password.', 'ai-awareness-day' ) ) . ' <strong>' . aiad_block_text( __( 'Maximum participation. Minimum administration.', 'ai-awareness-day' ) ) . '</strong>' ),
						aiad_block_quote( aiad_block_text( __( 'The technology connects the conversation. Young people do the thinking.', 'ai-awareness-day' ) ), 'ncp-principle' ),
					),
					'ncp-lede__text'
				),
			),
			'container ncp-lede'
		),
	),
	'ncp-section',
	'section',
	'',
	__( 'What it is', 'ai-awareness-day' )
);

// Find your pathway.
$aiad_path_items = array();
foreach ( $aiad_paths as $aiad_path ) {
	$aiad_link         = $aiad_path_links[ $aiad_path['id'] ] ?? null;
	$aiad_path_items[] = aiad_block_styled_item(
		array(
			aiad_block_heading( 3, aiad_block_text( $aiad_path['who'] ) ),
			aiad_block_paragraph( aiad_block_text( $aiad_path['tag'] ), 'ncp-paths__tag' ),
			aiad_block_list( array_map( 'aiad_block_text', $aiad_path['steps'] ), true ),
			$aiad_path['note'] ? aiad_block_paragraph( aiad_block_text( $aiad_path['note'] ), 'ncp-paths__note' ) : '',
			$aiad_link ? aiad_block_paragraph( '<a href="' . esc_attr( $aiad_link[0] ) . '">' . aiad_block_text( $aiad_link[1] ) . '</a>', 'ncp-paths__more' ) : '',
		)
	);
}
$aiad_out[] = $aiad_section(
	array(
		aiad_block_heading( 2, aiad_block_text( __( 'Find your pathway', 'ai-awareness-day' ) ), '', 'ncp-paths' ),
		aiad_block_paragraph( aiad_block_text( __( 'Every school gets one school code. Students add a class PIN; adults add their own email address and a six-digit code we send them. Nobody has a password.', 'ai-awareness-day' ) ), 'ncp-intro' ),
		aiad_block_styled_list( $aiad_path_items, 'ncp-paths' ),
	),
	__( 'Find your pathway', 'ai-awareness-day' ),
	true
);

// Five themes.
$aiad_theme_items = array();
foreach ( $aiad_themes as $aiad_slug => $aiad_theme ) {
	$aiad_theme_items[] = aiad_block_styled_item(
		array(
			'<!-- wp:aiad/strand-icon' . aiad_block_attrs( array( 'strand' => 'safe' === $aiad_slug ? null : $aiad_slug ) ) . ' /-->',
			aiad_block_heading( 3, aiad_block_text( $aiad_theme[0] ) ),
			aiad_block_paragraph( aiad_block_text( $aiad_theme[1] ) ),
		),
		'ncp-theme ncp-theme--' . $aiad_slug
	);
}
$aiad_out[] = $aiad_section(
	array(
		aiad_block_heading( 2, aiad_block_text( __( 'Five themes, one conversation', 'ai-awareness-day' ) ), '', 'ncp-themes' ),
		aiad_block_styled_list( $aiad_theme_items, 'ncp-themes' ),
	),
	__( 'Five themes', 'ai-awareness-day' )
);

// Three ways to take part.
$aiad_out[] = $aiad_section(
	array(
		aiad_block_heading( 2, aiad_block_text( __( 'Three ways to take part', 'ai-awareness-day' ) ), '', 'ncp-ways' ),
		$aiad_image( 'classroom-listening.jpg', __( 'Pupils at their desks, seen from the back of the classroom, listening to their teacher', 'ai-awareness-day' ), $aiad_photo_credit( 'Taylor Flowe' ), 'ncp-photo ncp-photo--wide ncp-photo--classroom' ),
		aiad_block_styled_list( array_map( static fn( array $way ): string => aiad_block_styled_item( array( aiad_block_heading( 3, aiad_block_text( $way[0] ) ), aiad_block_paragraph( aiad_block_text( $way[1] ) ) ) ), $aiad_ways ), 'ncp-ways', 'ol' ),
		aiad_block_paragraph( aiad_block_text( __( 'Each works at primary, secondary and post-16, with questions pitched for the age.', 'ai-awareness-day' ) ) . ' <a class="ncp-link" href="#ncp-ages">' . aiad_block_text( __( 'See how each age debates', 'ai-awareness-day' ) ) . '</a>', 'ncp-note' ),
	),
	__( 'Three ways to take part', 'ai-awareness-day' ),
	true
);

// Every age.
$aiad_age_items = array();
foreach ( $aiad_ages as $aiad_age ) {
	$aiad_rows = array(
		aiad_block_heading( 3, aiad_block_text( $aiad_age['name'] ) ),
		aiad_block_paragraph( aiad_block_text( $aiad_age['years'] ), 'ncp-agecards__years' ),
	);
	foreach ( $aiad_age_rows as $aiad_row_key => $aiad_row_label ) {
		$aiad_rows[] = aiad_block_group( array( aiad_block_heading( 4, aiad_block_text( $aiad_row_label ) ), aiad_block_paragraph( aiad_block_text( $aiad_age[ $aiad_row_key ] ) ) ), 'ncp-agecards__row ncp-agecards__row--' . $aiad_row_key );
	}
	$aiad_age_items[] = aiad_block_styled_item( $aiad_rows );
}
$aiad_out[] = $aiad_section(
	array(
		aiad_block_heading( 2, aiad_block_text( __( 'One conversation, pitched for every age', 'ai-awareness-day' ) ), '', 'ncp-ages' ),
		aiad_block_paragraph( aiad_block_text( __( 'Primary, secondary and post-16 students debate the same five themes. What changes is how the question is asked, how they prepare and how the debate runs. Here is one idea from the Safe theme, what AI should remember about you, as each age meets it.', 'ai-awareness-day' ) ), 'ncp-intro' ),
		aiad_block_styled_list( $aiad_age_items, 'ncp-agecards' ),
		aiad_block_paragraph( aiad_block_text( __( 'Every age has the same 30 minutes and is judged on the same four things, in simpler words for primary, and every theme has a question written for each age. The questions, timings and wording are being reviewed by debate educators before the conversation opens.', 'ai-awareness-day' ) ) ),
	),
	__( 'Every age', 'ai-awareness-day' )
);

// Inside a debate.
$aiad_out[] = $aiad_section(
	array(
		aiad_block_heading( 2, aiad_block_text( __( 'Inside a debate', 'ai-awareness-day' ) ), '', 'ncp-inside' ),
		aiad_block_paragraph( aiad_block_text( __( 'About 30 minutes, and the whole class takes part, not just the speakers.', 'ai-awareness-day' ) ), 'ncp-intro' ),
		$aiad_image( 'student-writing.jpg', __( 'A student in a hall of red seats writes notes on a sheet of paper', 'ai-awareness-day' ), $aiad_photo_credit( 'Kate Tweedy' ), 'ncp-photo ncp-photo--wide ncp-photo--writing' ),
		aiad_block_styled_list( array_map( static fn( array $part ): string => aiad_block_styled_item( array( aiad_block_paragraph( aiad_block_text( $part[0] ), 'ncp-running__time' ), aiad_block_heading( 3, aiad_block_text( $part[1] ) ), aiad_block_paragraph( aiad_block_text( $part[2] ) ) ) ), $aiad_running ), 'ncp-running', 'ol' ),
		aiad_block_group(
			array(
				aiad_block_group(
					array(
						aiad_block_heading( 3, aiad_block_text( __( 'A role for everyone', 'ai-awareness-day' ) ) ),
						aiad_block_paragraph( aiad_block_text( __( 'Each school gets a prep pack: a checklist, a short survey to run two to three weeks before with the same questions in both schools, research in three groups, and roles for a class of about 30.', 'ai-awareness-day' ) ) ),
					),
					''
				),
				aiad_block_group(
					array(
						aiad_block_heading( 3, aiad_block_text( __( 'Judged on four things', 'ai-awareness-day' ) ) ),
						aiad_block_styled_list( array_map( static fn( string $chip ): string => aiad_block_styled_text( aiad_block_text( $chip ) ), array( __( 'Argument', 'ai-awareness-day' ), __( 'Evidence', 'ai-awareness-day' ), __( 'Rebuttal', 'ai-awareness-day' ), __( 'Delivery', 'ai-awareness-day' ) ) ), 'ncp-chips', 'p' ),
						aiad_block_paragraph( aiad_block_text( __( 'Each is scored from 1 to 5. The criteria are the same at every age, in simpler words for primary.', 'ai-awareness-day' ) ) ),
					),
					''
				),
			),
			'ncp-inside'
		),
		aiad_block_quote( aiad_block_text( __( 'Several sources for every fact. Don\'t rely on AI-generated content: it can be wrong.', 'ai-awareness-day' ) ), 'ncp-principle' ),
		aiad_block_paragraph( aiad_block_text( __( 'The running order is a recommendation, not a rule, so schools can fit it to their setting and their students.', 'ai-awareness-day' ) ), 'ncp-note' ),
	),
	__( 'Inside a debate', 'ai-awareness-day' ),
	true
);

// How an inter-school debate works.
$aiad_out[] = $aiad_section(
	array(
		aiad_block_heading( 2, aiad_block_text( __( 'How an inter-school debate works', 'ai-awareness-day' ) ), '', 'ncp-how' ),
		aiad_block_paragraph( aiad_block_text( __( 'Identify your school once. We take care of the rest.', 'ai-awareness-day' ) ), 'ncp-motto' ),
		aiad_block_paragraph( aiad_block_text( __( 'It should feel more like arranging a school sports fixture than registering for another complicated education platform. It is not a learning management system, a pupil social network or a heavyweight competition platform.', 'ai-awareness-day' ) ), 'ncp-intro' ),
		aiad_block_group(
			array(
				aiad_block_heading( 3, aiad_block_text( __( 'Headteacher approval comes first', 'ai-awareness-day' ) ) ),
				aiad_block_paragraph( aiad_block_text( __( 'Every school taking part, including the one you invite, is approved by its headteacher or a senior leader before anything goes live. They get one email and approve with one click. Until then a school cannot run a class PIN for students, start or join a debate, or see Join the conversation. If the email is missed, the lead teacher can send it again.', 'ai-awareness-day' ) ) ),
			),
			'ncp-callout',
			'div',
			'ncp-approval'
		),
		aiad_block_styled_list( array_map( static fn( array $step ): string => aiad_block_styled_item( array( aiad_block_heading( 3, aiad_block_text( $step[0] ) ), aiad_block_paragraph( str_replace( 'AID-7K42P', '<span class="ncp-nowrap">AID-7K42P</span>', aiad_block_text( $step[1] ) ) ) ) ), $aiad_steps ), 'ncp-steps', 'ol' ),
		aiad_block_paragraph( aiad_block_text( __( 'One Debate ID. Two school leads. One judge. One result.', 'ai-awareness-day' ) ), 'ncp-tally' ),
		aiad_block_paragraph( aiad_block_text( __( 'If a step stalls, we send reminders after 3 and 7 days. A debate with no action for 14 days expires, and the school can invite another.', 'ai-awareness-day' ) ), 'ncp-note' ),
	),
	__( 'How a debate works', 'ai-awareness-day' )
);

// Schools stay in charge.
$aiad_out[] = $aiad_section(
	array(
		aiad_block_group(
			array(
				aiad_block_heading( 2, aiad_block_text( __( 'Schools stay in charge', 'ai-awareness-day' ) ), '', 'ncp-safe' ),
				aiad_block_paragraph( aiad_block_text( __( 'The platform connects schools. Each school remains responsible for its own safeguarding, supervision, travel, parental permissions, risk assessments and visitor arrangements.', 'ai-awareness-day' ) ) ),
				aiad_block_paragraph( aiad_block_text( __( 'When two schools are matched, both teachers automatically receive our Debate & Safeguarding Pack to help them prepare.', 'ai-awareness-day' ) ) ),
				aiad_block_paragraph( aiad_block_text( __( 'Every debate has an independent judge, such as a professional, technologist, academic or community leader. Judges are invited by the host teacher, never through an open call, and the host school\'s own visitor procedures apply.', 'ai-awareness-day' ) ) ),
				aiad_block_paragraph( aiad_block_text( __( 'If something goes wrong, either school can report it: a cancellation, a conduct or safeguarding concern, or a result that does not reflect what happened. Both schools\' senior leaders are told, the result is held, and the report stays private. Schools resolve it under their own procedures.', 'ai-awareness-day' ) ) ),
			),
			''
		),
		aiad_block_group(
			array(
				aiad_block_heading( 3, aiad_block_text( __( 'National Debate Code of Conduct', 'ai-awareness-day' ) ), '', 'ncp-conduct' ),
				aiad_block_paragraph( aiad_block_text( __( 'Challenge the argument. Respect the person.', 'ai-awareness-day' ) ), 'ncp-conduct__line' ),
				aiad_block_paragraph( aiad_block_text( __( 'It applies to students, teachers, judges, visitors and audiences. Strong disagreement is encouraged; personal attacks, discriminatory language, intimidation, deliberate disruption and abusive audience behaviour are not.', 'ai-awareness-day' ) ) ),
			),
			'ncp-conduct',
			'aside'
		),
	),
	__( 'Schools stay in charge', 'ai-awareness-day' ),
	true,
	'container ncp-split'
);

// Recognition.
$aiad_counts = array(
	array( '1', __( 'completed inter-school debate', 'ai-awareness-day' ), __( 'Participation recorded', 'ai-awareness-day' ) ),
	array( '2', __( 'completed debates against two different schools', 'ai-awareness-day' ), __( 'National AI Debate School certificate', 'ai-awareness-day' ) ),
);
$aiad_out[] = $aiad_section(
	array(
		aiad_block_heading( 2, aiad_block_text( __( 'Recognising taking part, not only winning', 'ai-awareness-day' ) ), '', 'ncp-recognition' ),
		aiad_block_group( array_map( static fn( array $count ): string => aiad_block_group( array( aiad_block_paragraph( $count[0], 'ncp-recognition__count' ), aiad_block_paragraph( aiad_block_text( $count[1] ) ), aiad_block_paragraph( aiad_block_text( $count[2] ), 'ncp-recognition__result' ) ), '' ), $aiad_counts ), 'ncp-recognition' ),
		aiad_block_paragraph( aiad_block_text( __( 'The programme rewards conversation, connection and breadth: the schools you meet, the students involved and the themes explored. MATs and regions can run competitive events of their own, but there is no national league table of winners.', 'ai-awareness-day' ) ) ),
		aiad_block_paragraph( aiad_block_text( __( 'Results are public: both schools, the theme, the motion, the date, the result and, if they agree, the judge\'s name. Teachers\' names and anything about students are never published. Every certificate carries a reference that anyone can check.', 'ai-awareness-day' ) ) ),
	),
	__( 'Recognition', 'ai-awareness-day' )
);

// MATs and communities.
$aiad_out[] = $aiad_section(
	array(
		aiad_block_heading( 2, aiad_block_text( __( 'Whole MATs and communities can join in', 'ai-awareness-day' ) ), '', 'ncp-networks' ),
		aiad_block_styled_list( array_map( static fn( string $level ): string => aiad_block_styled_text( aiad_block_text( $level ) ), $aiad_network ), 'ncp-chain', 'p', array( 'aria-label' => __( 'Classroom, then school, then school to school, then MAT, then region, then national', 'ai-awareness-day' ) ) ),
		aiad_block_paragraph( aiad_block_text( __( 'A multi-academy trust (MAT) can hold debates between its own academies before connecting with schools elsewhere. Each academy registers and is approved by its own headteacher, and the programme team can share a report of the MAT\'s academies taking part.', 'ai-awareness-day' ) ) ),
	),
	__( 'MATs and communities', 'ai-awareness-day' ),
	true,
	'container ncp-narrow'
);

// Organisations.
$aiad_out[] = $aiad_section(
	array(
		aiad_block_heading( 2, aiad_block_text( __( 'For organisations that bring AI into schools', 'ai-awareness-day' ) ), '', 'ncp-partners' ),
		aiad_block_paragraph( aiad_block_text( __( 'Take learning beyond the resource.', 'ai-awareness-day' ) ), 'ncp-motto' ),
		aiad_block_paragraph( aiad_block_text( __( 'If your organisation supports schools with AI lessons, resources or programmes, this is the natural next step: students put what they have learned to work, testing their thinking against peers from another school, in front of an audience and an independent judge.', 'ai-awareness-day' ) ), 'ncp-intro' ),
		aiad_block_styled_list( array_map( static fn( array $point ): string => aiad_block_styled_item( array( aiad_block_heading( 3, aiad_block_text( $point[0] ) ), aiad_block_paragraph( aiad_block_text( $point[1] ) ) ) ), $aiad_partner_points ), 'ncp-partner' ),
		aiad_block_paragraph( aiad_block_text( __( 'Whoever brings a school along, the school always confirms its own place. Every email to an organisation lets it stop the emails, or say “this wasn\'t us”.', 'ai-awareness-day' ) ) ),
		'<!-- wp:aiad/nc-actions {"variant":"nominate"} /-->',
		aiad_block_group(
			array(
				aiad_block_heading( 3, aiad_block_text( __( 'Interested in supporting the national debate?', 'ai-awareness-day' ) ) ),
				aiad_block_paragraph( aiad_block_text( __( 'Don\'t hesitate to reach out to us. A few partnership places remain for organisations that want to help with:', 'ai-awareness-day' ) ) ),
				aiad_block_list(
					array(
						aiad_block_text( __( 'Powering the platform that hosts the debates', 'ai-awareness-day' ) ),
						aiad_block_text( __( 'Keynote talks from technology companies at debates and events', 'ai-awareness-day' ) ),
						aiad_block_text( __( 'Guest speakers for schools serving disadvantaged communities', 'ai-awareness-day' ) ),
						aiad_block_text( __( 'Hosting debates and events for those schools', 'ai-awareness-day' ) ),
					)
				),
				aiad_block_paragraph( aiad_block_text( __( 'Partners support the programme, not individual schools. They never see schools\', staff or students\' details, and guest speakers visit under each school\'s own visitor procedures.', 'ai-awareness-day' ) ), 'ncp-callout__small' ),
				aiad_block_paragraph( '<a class="ncp-btn ncp-btn--primary" href="' . esc_url( home_url( '/#contact' ) ) . '">' . aiad_block_text( __( 'Get in touch', 'ai-awareness-day' ) ) . '</a>', 'ncp-actions' ),
			),
			'ncp-callout ncp-callout--invite'
		),
	),
	__( 'For organisations', 'ai-awareness-day' )
);

// Everyone has a part to play.
$aiad_out[] = $aiad_section(
	array(
		aiad_block_heading( 2, aiad_block_text( __( 'Everyone has a part to play', 'ai-awareness-day' ) ), '', 'ncp-who' ),
		aiad_block_styled_list( array_map( static fn( array $audience ): string => aiad_block_styled_item( array( aiad_block_heading( 3, aiad_block_text( $audience[0] ) ), aiad_block_paragraph( aiad_block_text( $audience[1] ), 'ncp-who__line' ), $audience[2] ? aiad_block_paragraph( aiad_block_text( $audience[2] ), 'ncp-who__note' ) : '' ) ), $aiad_audiences ), 'ncp-who' ),
	),
	__( 'Everyone has a part', 'ai-awareness-day' ),
	true
);

// Timeline.
$aiad_out[] = $aiad_section(
	array(
		aiad_block_heading( 2, aiad_block_text( __( 'The timeline', 'ai-awareness-day' ) ), '', 'ncp-timeline' ),
		aiad_block_styled_list( array_map( static fn( array $stage ): string => aiad_block_styled_item( array( aiad_block_heading( 3, aiad_block_text( $stage[0] ) ), aiad_block_paragraph( aiad_block_text( $stage[1] ) ) ) ), $aiad_timeline ), 'ncp-timeline', 'ol' ),
	),
	__( 'Timeline', 'ai-awareness-day' ),
	false,
	'container ncp-narrow'
);

// Questions.
$aiad_out[] = $aiad_section(
	array_merge(
		array( aiad_block_heading( 2, aiad_block_text( __( 'Questions schools ask', 'ai-awareness-day' ) ), '', 'ncp-faq' ) ),
		array_map( static fn( array $faq ): string => aiad_block_details( aiad_block_text( $faq[0] ), aiad_block_text( $faq[1] ), 'ncp-faq' ), $aiad_faqs )
	),
	__( 'Questions', 'ai-awareness-day' ),
	true,
	'container ncp-narrow'
);

// Closing call to action.
$aiad_out[] = aiad_block_group(
	array(
		aiad_block_group(
			array(
				aiad_block_heading( 2, aiad_block_text( __( 'Ready to start the conversation?', 'ai-awareness-day' ) ), '', 'ncp-cta' ),
				'<!-- wp:aiad/nc-actions {"variant":"cta"} /-->',
			),
			'container'
		),
	),
	'ncp-cta',
	'section',
	'',
	__( 'Start the conversation', 'ai-awareness-day' )
);

// The page's main element is its outermost block, so the editor shows the content inside .ncp as the site does.
echo aiad_block_group( $aiad_out, 'ncp', 'main', 'main', __( 'National Conversation', 'ai-awareness-day' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block markup; every value is escaped above.
