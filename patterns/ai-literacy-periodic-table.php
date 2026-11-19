<?php
/**
 * Title: AI Literacy Periodic Table page
 * Slug: aiad/ai-literacy-periodic-table
 * Categories: aiad-pages
 * Post Types: page
 * Block Types: core/post-content
 * Keywords: periodic table, ai literacy, glossary, teachers, students, classroom
 * Description: The AI Literacy Periodic Table page: an introduction, the interactive table and ideas for using it in class.
 *
 * The introduction and the display board help (questions and answers) are core blocks, edited on the page. The table, with every definition
 * listed below it, is one block (aiad/literacy-table) drawn from aiad_literacy_elements() in inc/literacy-table.php.
 * Everything sits in one main group (main#main.alpt-page), so the editor shows it as the site does.
 *
 * @package AI_Awareness_Day
 */

$aiad_count = count( aiad_literacy_elements() );

$aiad_button = static fn( string $url, string $label, string $kind ): string => '<a class="alpt-btn alpt-btn--' . $kind . '" href="' . $url . '">' . aiad_block_text( $label ) . '</a>';

$aiad_out = array();

// Introduction.
$aiad_out[] = aiad_block_group(
	array(
		aiad_block_group(
			array(
				aiad_block_paragraph( aiad_block_text( __( 'AI literacy for teachers and students', 'ai-awareness-day' ) ), 'alpt-eyebrow' ),
				aiad_block_heading( 1, aiad_block_text( __( 'The AI Literacy Periodic Table', 'ai-awareness-day' ) ), 'alpt-title', 'alpt-title' ),
				/* translators: %d: number of elements */
				aiad_block_paragraph( aiad_block_text( sprintf( __( '%d terms you will hear in conversations about AI, sorted into seven families: from the foundations everything is built on, to generative, predictive, agentic and automation AI, and the governance and infrastructure that hold it all together. The latest buzzwords are in there too, from AI slop to jailbreaking.', 'ai-awareness-day' ), $aiad_count ) ), 'alpt-lead' ),
				aiad_block_paragraph( aiad_block_text( __( 'Choose any element to read what it means in plain English. Pick a family to see it on its own, or search for a word.', 'ai-awareness-day' ) ), 'alpt-intro' ),
				aiad_block_paragraph( $aiad_button( '#alpt-explore', __( 'Explore the table', 'ai-awareness-day' ), 'ink' ) . $aiad_button( '#alpt-print', __( 'Print the poster', 'ai-awareness-day' ), 'ghost' ) . $aiad_button( '#alpt-challenge-title', __( 'Take the compound challenge', 'ai-awareness-day' ), 'ghost' ) . $aiad_button( '#alpt-classroom', __( 'Display board help', 'ai-awareness-day' ), 'ghost' ), 'alpt-actions' ),
			),
			'container'
		),
	),
	'alpt-hero',
	'section',
	'',
	__( 'Introduction', 'ai-awareness-day' )
);

// The table.
$aiad_out[] = aiad_block_group(
	array( "<!-- wp:aiad/literacy-table /-->" ),
	'alpt-table-wrap',
	'section',
	'',
	__( 'The table', 'ai-awareness-day' )
);

// Help with a display board: questions teachers ask, as details blocks, edited on the page.
$aiad_t    = static fn( string $text ): string => aiad_block_text( $text );
$aiad_link = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '">' . aiad_block_text( $text ) . '</a>';
$aiad_b    = static fn( string $text ): string => '<strong>' . aiad_block_text( $text ) . '</strong>';

$aiad_faq = array(
	array(
		__( 'How do I print the table for a display board?', 'ai-awareness-day' ),
		$aiad_t( __( 'Press Print the poster at the top of this page. The table prints on one A3 or A4 landscape page, with a title and a colour key. Choose Families or AIAD themes above the table first: the poster prints in whichever view is showing.', 'ai-awareness-day' ) ),
	),
	array(
		__( 'Families or AIAD themes: which should I put up?', 'ai-awareness-day' ),
		$aiad_t( __( 'Families shows what each technology is, from foundations to infrastructure. AIAD themes colours the same table by the five campaign themes, Safe, Smart, Creative, Responsible and Future, so students can see how each theme runs through the technology. Many schools print both and put them side by side.', 'ai-awareness-day' ) ),
	),
	array(
		__( 'What else can go on the board?', 'ai-awareness-day' ),
		$aiad_t( __( 'An element of the week with its definition and its Talk about it question; the issues it raises, from the list below the table; a compound of the week from the compound challenge; and your students\' ideas for new elements.', 'ai-awareness-day' ) ) . ' ' . $aiad_link( home_url( '/#display-board' ), __( 'Our display board blueprint', 'ai-awareness-day' ) ) . ' ' . $aiad_t( __( 'shows a layout built around the five themes.', 'ai-awareness-day' ) ),
	),
	array(
		__( 'What quick activities can I run in front of it?', 'ai-awareness-day' ),
		$aiad_b( __( 'Element of the day:', 'ai-awareness-day' ) ) . ' ' . $aiad_t( __( 'pick an element, read it together, then press Connections and talk about how it links to the elements that light up.', 'ai-awareness-day' ) ) . ' '
		. $aiad_b( __( 'Build a compound:', 'ai-awareness-day' ) ) . ' ' . $aiad_t( __( 'race in pairs in the compound challenge, then ask which elements explain a news story about AI.', 'ai-awareness-day' ) ) . ' '
		. $aiad_b( __( 'Family experts:', 'ai-awareness-day' ) ) . ' ' . $aiad_t( __( 'each group teaches the class the three most important elements in one family.', 'ai-awareness-day' ) ) . ' '
		. $aiad_b( __( 'What\'s missing?', 'ai-awareness-day' ) ) . ' ' . $aiad_t( __( 'students design a new element and send the best one to us.', 'ai-awareness-day' ) ) . ' '
		. $aiad_b( __( 'Debate starter:', 'ai-awareness-day' ) ) . ' ' . $aiad_t( __( 'every compound and issue comes with a question to debate.', 'ai-awareness-day' ) ),
	),
	array(
		__( 'Which age groups is it for?', 'ai-awareness-day' ),
		$aiad_t( __( 'Any: pitch each element and question to your class. In the compound challenge, Beginner compounds suit younger students, and Advanced ones suit older students and staff. It works in tutor time, PSHE, computing, English or any lesson where AI comes up.', 'ai-awareness-day' ) ),
	),
	array(
		__( 'Are there safeguarding issues to be aware of?', 'ai-awareness-day' ),
		$aiad_t( __( 'Yes. Some elements and issues, such as Deepfake, Image-Based Abuse, AI Companion and Jailbreaking, can lead to conversations about safeguarding. Follow your school\'s procedures, and make sure students know who to talk to.', 'ai-awareness-day' ) ),
	),
	array(
		__( 'How should students use it?', 'ai-awareness-day' ),
		/* translators: %d: number of elements */
		$aiad_t( sprintf( __( 'They don\'t need to learn all %d. Start with the family you use most, look up the terms you hear about the AI tools you already use, and remember: you are the human in the loop.', 'ai-awareness-day' ), $aiad_count ) ),
	),
	array(
		__( 'Where can I find lessons to go with it?', 'ai-awareness-day' ),
		$aiad_t( __( 'Many elements and issues have Learn more links to AI Awareness Day lessons, articles and tools, and to resources from our partners. You\'ll find them in the panel when you choose an element, and in the lists below the table.', 'ai-awareness-day' ) ),
	),
	array(
		__( 'How does it link to the AI Risk Readiness Benchmark?', 'ai-awareness-day' ),
		$aiad_t( __( 'Each element names the part of the benchmark it relates to, such as Privacy & Data Protection or Safeguarding.', 'ai-awareness-day' ) ) . ' ' . $aiad_link( aiad_literacy_benchmark_url(), __( 'Take the benchmark', 'ai-awareness-day' ) ) . ' ' . $aiad_t( __( 'to see how ready you or your school are.', 'ai-awareness-day' ) ),
	),
	array(
		__( 'Can students suggest new elements?', 'ai-awareness-day' ),
		$aiad_t( __( 'Yes: use Suggest a new element, below the table. The team reviews every suggestion and adds the best ones.', 'ai-awareness-day' ) ),
	),
);

$aiad_out[] = aiad_block_group(
	array(
		aiad_block_group(
			array(
				aiad_block_heading( 2, aiad_block_text( __( 'Need help with your AI literacy display board?', 'ai-awareness-day' ) ), 'alpt-section-title', 'alpt-classroom' ),
				aiad_block_paragraph( aiad_block_text( __( 'Answers to the questions teachers ask most, from printing the poster to running activities in front of it.', 'ai-awareness-day' ) ), 'alpt-section-lead' ),
				aiad_block_group(
					array_map( static fn( array $q ): string => aiad_block_details( aiad_block_text( $q[0] ), $q[1], 'alpt-faq__item' ), $aiad_faq ),
					'alpt-faq'
				),
			),
			'container'
		),
	),
	'alpt-classroom',
	'section',
	'',
	__( 'Display board help', 'ai-awareness-day' )
);

// The page's main element is its outermost block, so the editor shows the content inside .alpt-page as the site does.
echo aiad_block_group( $aiad_out, 'alpt-page', 'main', 'main', __( 'AI Literacy Periodic Table', 'ai-awareness-day' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block markup; every value is escaped above.
