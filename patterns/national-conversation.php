<?php
/**
 * Title: National Conversation page
 * Slug: aiad/national-conversation
 * Categories: aiad-pages
 * Post Types: page
 * Block Types: core/post-content
 * Keywords: national conversation, debate, schools, landing page
 * Description: The National AI Conversation landing page in blocks, so all of its text is edited on the page.
 *
 * The lists come from aiad_national_conversation_content() (inc/national-conversation-content.php).
 * Everything sits in one main group (main#main.ncp, as on the PHP page; templates/page-national-conversation.html
 * has only the header, the post content and the footer). Text is core blocks; lists whose items hold more than text are Styled list, Styled list item and Styled text blocks
 * (aiad-core's list, list-item and text blocks), saved as plain HTML with the page's classes, so national-conversation.css styles them
 * as it styles the PHP page. What changes on its own is filled in when the page renders
 * (aiad_national_conversation_placeholders()): {opens}, {event}, {event_year}, {opens_label} and {retention}. The
 * buttons, which change on the day the conversation opens, are a block of their own (aiad/nc-actions), and so is
 * each strand's mark (aiad/strand-icon).
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


[
	'aims'           => $aiad_aims,
	'objectives'     => $aiad_objectives,
	'ambitions'      => $aiad_ambitions,
	'journey'        => $aiad_journey,
	'skills'         => $aiad_skills,
	'ways'           => $aiad_ways,
	'themes'         => $aiad_themes,
	'age_rows'       => $aiad_age_rows,
	'ages'           => $aiad_ages,
	'running'        => $aiad_running,
	'steps'          => $aiad_steps,
	'network'        => $aiad_network,
	'partner_points' => $aiad_partner_points,
	'timeline'       => $aiad_timeline,
	'faqs'           => $aiad_faqs,
] = aiad_national_conversation_content( '{opens}', '{event}', '{retention}' );

// Block markup: inc/block-markup.php. The variables are prefixed because WordPress includes pattern files inside
// its own functions.
$aiad_image = static fn( string $file, string $alt, string $caption, string $class ): string => aiad_block_image( AIAD_URI . '/assets/images/national-conversation/' . $file, $alt, $caption, $class );

$aiad_section = static fn( array $inner, string $name, bool $card = false, string $container = 'container' ): string => aiad_block_group( array( aiad_block_group( $inner, $container ) ), 'ncp-section' . ( $card ? ' ncp-section--card' : '' ), 'section', '', $name );

$aiad_photo_credit = static fn( string $who ): string => sprintf( /* translators: %s: photographer */ __( 'Photo: %s on Unsplash', 'ai-awareness-day' ), $who );

$aiad_cards = static fn( array $items, string $class, string $tag = 'ul' ): string => aiad_block_styled_list( array_map( static fn( array $item ): string => aiad_block_styled_item( array( aiad_block_heading( 3, aiad_block_text( $item[0] ) ), aiad_block_paragraph( aiad_block_text( $item[1] ) ) ) ), $items ), $class, $tag );

/* ------------------------------------------------------------------ The page */

$aiad_out = array();

// Hero.
$aiad_out[] = aiad_block_group(
	array(
		aiad_block_group(
			array(
				aiad_block_paragraph( aiad_block_text( __( 'AI Awareness Day', 'ai-awareness-day' ) ) . ' {event_year} <span aria-hidden="true">·</span> ' . aiad_block_text( __( 'Humans in the Loop', 'ai-awareness-day' ) ), 'ncp-eyebrow' ),
				aiad_block_heading( 1, aiad_block_text( __( 'The National AI Conversation', 'ai-awareness-day' ) ), 'ncp-title', 'ncp-title' ),
				aiad_block_paragraph( aiad_block_text( __( 'Students question and debate AI, from five minutes in class to judged debates between schools. For primary, secondary and post-16.', 'ai-awareness-day' ) ), 'ncp-lead' ),
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
		aiad_block_heading( 2, aiad_block_text( __( 'How each age debates', 'ai-awareness-day' ) ), '', 'ncp-ages' ),
		aiad_block_paragraph( aiad_block_text( __( 'Primary, secondary and post-16 students debate the same five themes, each with a question written for their age. These examples are from the Safe theme.', 'ai-awareness-day' ) ), 'ncp-intro' ),
		aiad_block_styled_list( $aiad_age_items, 'ncp-agecards' ),
		aiad_block_paragraph( aiad_block_text( __( 'Debate educators will review the questions and timings before the conversation opens.', 'ai-awareness-day' ) ), 'ncp-note' ),
	),
	__( 'Every age', 'ai-awareness-day' )
);

// Three ways to take part.
$aiad_out[] = $aiad_section(
	array(
		aiad_block_heading( 2, aiad_block_text( __( 'Three ways to take part', 'ai-awareness-day' ) ), '', 'ncp-ways' ),
		$aiad_image( 'classroom-listening.jpg', __( 'Pupils at their desks, seen from the back of the classroom, listening to their teacher', 'ai-awareness-day' ), $aiad_photo_credit( 'Taylor Flowe' ), 'ncp-photo ncp-photo--wide ncp-photo--classroom' ),
		$aiad_cards( $aiad_ways, 'ncp-ways', 'ol' ),
		aiad_block_paragraph( aiad_block_text( __( 'All three work at primary, secondary and post-16.', 'ai-awareness-day' ) ), 'ncp-note' ),
	),
	__( 'Three ways to take part', 'ai-awareness-day' ),
	true
);

// Timeline.
$aiad_out[] = $aiad_section(
	array(
		aiad_block_heading( 2, aiad_block_text( __( 'The timeline', 'ai-awareness-day' ) ), '', 'ncp-timeline' ),
		$aiad_cards( $aiad_timeline, 'ncp-timeline', 'ol' ),
	),
	__( 'Timeline', 'ai-awareness-day' ),
	false,
	'container ncp-narrow'
);

// Inside a debate.
$aiad_out[] = $aiad_section(
	array(
		aiad_block_heading( 2, aiad_block_text( __( 'Inside a debate', 'ai-awareness-day' ) ), '', 'ncp-inside' ),
		aiad_block_paragraph( aiad_block_text( __( 'About 30 minutes. Schools can adapt the timings to suit their students.', 'ai-awareness-day' ) ), 'ncp-intro' ),
		$aiad_image( 'student-writing.jpg', __( 'A student writing notes in a hall of red seats', 'ai-awareness-day' ), $aiad_photo_credit( 'Kate Tweedy' ), 'ncp-photo ncp-photo--wide ncp-photo--writing' ),
		aiad_block_styled_list( array_map( static fn( array $part ): string => aiad_block_styled_item( array( aiad_block_paragraph( aiad_block_text( $part[0] ), 'ncp-running__time' ), aiad_block_heading( 3, aiad_block_text( $part[1] ) ), aiad_block_paragraph( aiad_block_text( $part[2] ) ) ) ), $aiad_running ), 'ncp-running', 'ol' ),
		aiad_block_group(
			array(
				aiad_block_group(
					array(
						aiad_block_heading( 3, aiad_block_text( __( 'A role for everyone', 'ai-awareness-day' ) ) ),
						aiad_block_paragraph( aiad_block_text( __( 'The whole class takes part as speakers, note-takers, questioners or survey presenters. Each school\'s prep pack has a checklist, the survey questions and the research.', 'ai-awareness-day' ) ) ),
					),
					''
				),
				aiad_block_group(
					array(
						aiad_block_heading( 3, aiad_block_text( __( 'Judged on four things', 'ai-awareness-day' ) ) ),
						aiad_block_styled_list( array_map( static fn( string $chip ): string => aiad_block_styled_text( aiad_block_text( $chip ) ), array( __( 'Argument', 'ai-awareness-day' ), __( 'Evidence', 'ai-awareness-day' ), __( 'Rebuttal', 'ai-awareness-day' ), __( 'Delivery', 'ai-awareness-day' ) ) ), 'ncp-chips', 'p' ),
						aiad_block_paragraph( aiad_block_text( __( 'The judge scores both sides from 1 to 5 on each, at every age.', 'ai-awareness-day' ) ) ),
					),
					''
				),
			),
			'ncp-inside'
		),
		aiad_block_quote( aiad_block_text( __( 'Several sources for every fact. Don\'t rely on AI-generated content alone: it can be wrong.', 'ai-awareness-day' ) ), 'ncp-principle' ),
	),
	__( 'Inside a debate', 'ai-awareness-day' ),
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
			// "Show resources": a pop-up of this theme's resources from the library (inc/national-conversation-page.php).
			'<!-- wp:aiad/nc-theme-resources' . aiad_block_attrs( array( 'strand' => $aiad_slug ) ) . ' /-->',
		),
		'ncp-theme ncp-theme--' . $aiad_slug
	);
}
$aiad_out[] = $aiad_section(
	array(
		aiad_block_heading( 2, aiad_block_text( __( 'Five themes to debate', 'ai-awareness-day' ) ), '', 'ncp-themes' ),
		aiad_block_paragraph( aiad_block_text( __( 'Choose a theme and press Show resources to see free lessons and activities for it.', 'ai-awareness-day' ) ), 'ncp-intro' ),
		aiad_block_styled_list( $aiad_theme_items, 'ncp-themes' ),
	),
	__( 'Five themes', 'ai-awareness-day' )
);

// How an inter-school debate works.
$aiad_out[] = $aiad_section(
	array(
		aiad_block_heading( 2, aiad_block_text( __( 'How an inter-school debate works', 'ai-awareness-day' ) ), '', 'ncp-how' ),
		aiad_block_paragraph( aiad_block_text( __( 'It should feel like arranging a sports fixture, not joining another complicated platform.', 'ai-awareness-day' ) ), 'ncp-intro' ),
		aiad_block_group(
			array(
				aiad_block_heading( 3, aiad_block_text( __( 'Headteacher approval comes first', 'ai-awareness-day' ) ) ),
				aiad_block_paragraph( aiad_block_text( __( 'Before anything goes live, every school, including the one you invite, is approved by a headteacher or senior leader. It takes one click from one email, and the lead teacher can send it again.', 'ai-awareness-day' ) ) ),
			),
			'ncp-callout',
			'div',
			'ncp-approval'
		),
		aiad_block_styled_list( array_map( static fn( array $step ): string => aiad_block_styled_item( array( aiad_block_heading( 3, aiad_block_text( $step[0] ) ), aiad_block_paragraph( str_replace( 'AID-7K42P', '<span class="ncp-nowrap">AID-7K42P</span>', aiad_block_text( $step[1] ) ) ) ) ), $aiad_steps ), 'ncp-steps', 'ol' ),
		aiad_block_paragraph( aiad_block_text( __( 'If a step stalls, we send reminders. A debate with no action for 14 days expires, and you can invite another school.', 'ai-awareness-day' ) ), 'ncp-note' ),
	),
	__( 'How a debate works', 'ai-awareness-day' ),
	true
);

// Safeguarding.
$aiad_out[] = $aiad_section(
	array(
		aiad_block_group(
			array(
				aiad_block_heading( 2, aiad_block_text( __( 'Safeguarding: schools stay in charge', 'ai-awareness-day' ) ), '', 'ncp-safe' ),
				aiad_block_paragraph( aiad_block_text( __( 'The platform connects schools. Each school remains responsible for its own safeguarding, supervision, travel, parental permissions, risk assessments and visitor arrangements.', 'ai-awareness-day' ) ) ),
				aiad_block_paragraph( aiad_block_text( __( 'When two schools are matched, both teachers are emailed a link to our Debate & Safeguarding Pack.', 'ai-awareness-day' ) ) ),
				aiad_block_paragraph( aiad_block_text( __( 'Every debate needs an independent judge. The teacher who sets up the debate names them: there is no open call. The host school\'s visitor procedures apply.', 'ai-awareness-day' ) ) ),
				aiad_block_paragraph( aiad_block_text( __( 'Either school can report a problem: a cancellation, a conduct or safeguarding concern, or a wrong result. The result is held, both schools\' senior leaders are told and the report stays private. Schools resolve it under their own procedures.', 'ai-awareness-day' ) ) ),
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
	__( 'Safeguarding', 'ai-awareness-day' ),
	false,
	'container ncp-split'
);

// In the news: the oracy announcement (a campaign update, inc/article-oracy.php), shown as the news feed's card.
$aiad_out[] = $aiad_section(
	array(
		aiad_block_group(
			array(
				aiad_block_group(
					array(
						aiad_block_paragraph( aiad_block_text( __( 'In the news', 'ai-awareness-day' ) ), 'ncp-news__eyebrow' ),
						aiad_block_heading( 2, aiad_block_text( __( 'Why now: speaking joins reading and writing', 'ai-awareness-day' ) ), '', 'ncp-news' ),
						aiad_block_paragraph( aiad_block_text( __( 'The government has announced that oracy, the skills of speaking, debating and listening, will sit alongside reading and writing in England\'s curriculum. The National AI Conversation gives students a real question to practise those skills on.', 'ai-awareness-day' ) ) ),
					),
					'ncp-news__text'
				),
				'<!-- wp:aiad/nc-news {"slug":"oracy-and-ai-awareness-day"} /-->',
			),
			'ncp-news__grid'
		),
	),
	__( 'In the news', 'ai-awareness-day' ),
	true
);

// Aims, objectives and ambitions.
$aiad_goal = static fn( string $title, array $items, bool $ordered = false ): string => aiad_block_styled_item( array( aiad_block_heading( 3, aiad_block_text( $title ) ), aiad_block_list( array_map( 'aiad_block_text', $items ), $ordered ) ) );
$aiad_out[] = $aiad_section(
	array(
		aiad_block_heading( 2, aiad_block_text( __( 'What we want to achieve', 'ai-awareness-day' ) ), '', 'ncp-what' ),
		aiad_block_paragraph( aiad_block_text( __( 'Young people forming their own judgements about the role AI should play in their lives.', 'ai-awareness-day' ) ), 'ncp-big' ),
		aiad_block_styled_list(
			array(
				$aiad_goal( __( 'Aims', 'ai-awareness-day' ), $aiad_aims, true ),
				$aiad_goal( __( 'Objectives for {event_year}', 'ai-awareness-day' ), $aiad_objectives ),
				$aiad_goal( __( 'Ambitions beyond {event_year}', 'ai-awareness-day' ), $aiad_ambitions ),
			),
			'ncp-aims'
		),
		aiad_block_paragraph( aiad_block_text( __( 'We count schools taking part, debates completed, students involved, themes explored and links across trusts and regions.', 'ai-awareness-day' ) ), 'ncp-note' ),
	),
	__( 'Aims, objectives and ambitions', 'ai-awareness-day' )
);

// The skills.
$aiad_out[] = aiad_block_group(
	array(
		aiad_block_group(
			array(
				$aiad_image( 'classroom-hand-up.jpg', __( 'A pupil presents her work to the class while a classmate raises a hand to ask a question', 'ai-awareness-day' ), $aiad_photo_credit( 'Taylor Flowe' ), 'ncp-photo ncp-photo--tall' ),
				aiad_block_group(
					array(
						aiad_block_heading( 2, aiad_block_text( __( 'The skills students build', 'ai-awareness-day' ) ), '', 'ncp-skills' ),
						aiad_block_paragraph( aiad_block_text( __( 'Every student\'s journey', 'ai-awareness-day' ) ), 'ncp-label', 'ncp-journey' ),
						aiad_block_styled_list( array_map( static fn( string $stage ): string => aiad_block_styled_text( aiad_block_text( $stage ), 'li' ), $aiad_journey ), 'ncp-arc', 'ol', array( 'aria-labelledby' => 'ncp-journey' ) ),
						aiad_block_styled_list( array_map( static fn( array $skill ): string => aiad_block_styled_item( array( aiad_block_styled_text( aiad_block_text( $skill[0] ), 'dt' ), aiad_block_styled_text( aiad_block_text( $skill[1] ), 'dd' ) ), '', 'div' ), $aiad_skills ), 'ncp-skills', 'dl' ),
						aiad_block_quote( aiad_block_text( __( 'The technology connects the conversation. Young people do the thinking.', 'ai-awareness-day' ) ), 'ncp-principle' ),
					),
					'ncp-lede__text'
				),
			),
			'container ncp-lede'
		),
	),
	'ncp-section ncp-section--card',
	'section',
	'',
	__( 'The skills', 'ai-awareness-day' )
);

// Recognition.
$aiad_out[] = $aiad_section(
	array(
		aiad_block_heading( 2, aiad_block_text( __( 'Recognising taking part, not only winning', 'ai-awareness-day' ) ), '', 'ncp-recognition' ),
		aiad_block_paragraph( aiad_block_text( __( 'Your school\'s record shows the schools you meet, the students involved and the themes explored. MATs and regions can run their own competitions, but there is no national league table.', 'ai-awareness-day' ) ) ),
		aiad_block_paragraph( aiad_block_text( __( 'Results are public: both schools, the theme, motion, date and result. The judge\'s name appears only if they agree. Teachers\' names and anything about students are never published. Every certificate carries a reference anyone can check.', 'ai-awareness-day' ) ) ),
	),
	__( 'Recognition', 'ai-awareness-day' ),
	false,
	'container ncp-narrow'
);

// MATs.
$aiad_out[] = $aiad_section(
	array(
		aiad_block_heading( 2, aiad_block_text( __( 'For multi-academy trusts', 'ai-awareness-day' ) ), '', 'ncp-networks' ),
		aiad_block_styled_list( array_map( static fn( string $level ): string => aiad_block_styled_text( aiad_block_text( $level ) ), $aiad_network ), 'ncp-chain', 'p', array( 'aria-label' => __( 'Classroom, then school, then school to school, then MAT, then region, then national', 'ai-awareness-day' ) ) ),
		aiad_block_paragraph( aiad_block_text( __( 'A multi-academy trust (MAT) can hold debates between its own academies before connecting with schools elsewhere. Each academy registers and is approved by its own headteacher. Ask the programme team for a report of your academies taking part.', 'ai-awareness-day' ) ) ),
	),
	__( 'MATs', 'ai-awareness-day' ),
	true,
	'container ncp-narrow'
);

// Organisations.
$aiad_out[] = $aiad_section(
	array(
		aiad_block_heading( 2, aiad_block_text( __( 'For organisations that bring AI into schools', 'ai-awareness-day' ) ), '', 'ncp-partners' ),
		aiad_block_paragraph( aiad_block_text( __( 'Students put your AI lessons, resources or programmes to work. They test their thinking against another school, in front of an audience and a judge.', 'ai-awareness-day' ) ), 'ncp-intro' ),
		$aiad_cards( $aiad_partner_points, 'ncp-partner' ),
		aiad_block_paragraph( aiad_block_text( __( 'Every update we email lets you stop the emails or say “this wasn\'t us”.', 'ai-awareness-day' ) ) ),
		'<!-- wp:aiad/nc-actions {"variant":"nominate"} /-->',
		aiad_block_group(
			array(
				aiad_block_heading( 3, aiad_block_text( __( 'Support the national debate', 'ai-awareness-day' ) ) ),
				aiad_block_paragraph( aiad_block_text( __( 'Partners can help with:', 'ai-awareness-day' ) ) ),
				aiad_block_list(
					array(
						aiad_block_text( __( 'Powering the platform that hosts the debates', 'ai-awareness-day' ) ),
						aiad_block_text( __( 'Keynote talks from technology companies at debates and events', 'ai-awareness-day' ) ),
						aiad_block_text( __( 'Guest speakers for schools serving disadvantaged communities', 'ai-awareness-day' ) ),
						aiad_block_text( __( 'Hosting debates and events for those schools', 'ai-awareness-day' ) ),
					)
				),
				aiad_block_paragraph( aiad_block_text( __( 'Partners support the programme, not individual schools. They never see details of schools, staff or students. Guest speakers follow each school\'s own visitor procedures.', 'ai-awareness-day' ) ), 'ncp-callout__small' ),
				aiad_block_paragraph( '<a class="ncp-btn ncp-btn--primary" href="' . esc_url( home_url( '/#contact' ) ) . '">' . aiad_block_text( __( 'Get in touch', 'ai-awareness-day' ) ) . '</a>', 'ncp-actions' ),
			),
			'ncp-callout ncp-callout--invite'
		),
	),
	__( 'For organisations', 'ai-awareness-day' )
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
				aiad_block_heading( 2, aiad_block_text( __( 'Start the conversation in your school', 'ai-awareness-day' ) ), '', 'ncp-cta' ),
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
