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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


$aiad_count = count( aiad_literacy_elements() );

$aiad_button = static fn( string $url, string $label, string $kind ): string => '<a class="alpt-btn alpt-btn--' . $kind . '" href="' . $url . '">' . aiad_block_text( $label ) . '</a>';

$aiad_out = array();

// Introduction.
$aiad_out[] = aiad_block_group(
	array(
		aiad_block_group(
			array(
				aiad_block_paragraph( aiad_block_text( __( 'AI literacy for teachers and students · Keep Humans in the Loop', 'ai-awareness-day' ) ), 'alpt-eyebrow' ),
				aiad_block_heading( 1, aiad_block_text( __( 'The AI Literacy Periodic Table', 'ai-awareness-day' ) ), 'alpt-title', 'alpt-title' ),
				aiad_block_paragraph( aiad_block_text( __( 'AI literacy means more than knowing the words. This table helps students and staff understand AI, question it, and decide when a human must stay in charge.', 'ai-awareness-day' ) ), 'alpt-lead' ),
				// This week's element: beside the title on wide screens, just above the table on phones.
				'<!-- wp:aiad/literacy-week /-->',
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

$aiad_t    = static fn( string $text ): string => aiad_block_text( $text );
$aiad_link = static fn( string $url, string $text ): string => '<a href="' . esc_url( $url ) . '">' . aiad_block_text( $text ) . '</a>';
$aiad_b    = static fn( string $text ): string => '<strong>' . aiad_block_text( $text ) . '</strong>';

// Ready-made sessions: a starter, a lesson, a staff INSET and a page for parents, as details blocks with timed steps,
// edited on the page. Each moves from knowing the word, to questioning it, to deciding where a human stays in the loop.
$aiad_session = static function ( string $title, string $meta, array $steps, string $after = '' ): string {
	$inner = aiad_block_paragraph( $meta, 'alpt-session__meta' ) . aiad_block_list( $steps, true ) . ( '' !== $after ? aiad_block_paragraph( $after, 'alpt-session__after' ) : '' );
	return '<!-- wp:details' . aiad_block_attrs( array( 'className' => 'alpt-faq__item alpt-session' ) ) . " -->\n" . '<details class="wp-block-details alpt-faq__item alpt-session"><summary>' . aiad_block_text( $title ) . '</summary>' . $inner . "</details>\n<!-- /wp:details -->";
};
$aiad_st = static fn( string $time, string $text ): string => '<strong>' . aiad_block_text( $time ) . '</strong> ' . aiad_block_text( $text );

$aiad_parent_syms = array( 'Cb', 'Ll', 'Ha', 'Dk', 'Sp', 'Rc', 'Pv', 'Ag', 'Vc', 'Hi' );
$aiad_parent_els  = array_column( aiad_literacy_elements(), null, 'sym' );
$aiad_parent_list = array();
foreach ( $aiad_parent_syms as $aiad_sym ) {
	if ( isset( $aiad_parent_els[ $aiad_sym ] ) ) {
		$aiad_parent_list[] = '<strong>' . aiad_block_text( $aiad_parent_els[ $aiad_sym ]['name'] ) . ':</strong> ' . aiad_block_text( $aiad_parent_els[ $aiad_sym ]['def'] );
	}
}

$aiad_sessions = array(
	$aiad_session(
		__( 'A 10-minute starter: guess the word', 'ai-awareness-day' ),
		aiad_block_text( __( 'For any class, from Key Stage 2 up. You need this page on the board. By the end, students can explain one AI term in their own words.', 'ai-awareness-day' ) ),
		array(
			$aiad_st( __( '2 minutes.', 'ai-awareness-day' ), __( 'Choose the element of the week, or any element without a safeguarding note, and press Show to class. The definition starts hidden. Ask: what do you think it means?', 'ai-awareness-day' ) ),
			$aiad_st( __( '3 minutes.', 'ai-awareness-day' ), __( 'Pairs agree on a guess. Take three answers.', 'ai-awareness-day' ) ),
			$aiad_st( __( '2 minutes.', 'ai-awareness-day' ), __( 'Show the definition. Whose guess was closest, and what did everyone miss?', 'ai-awareness-day' ) ),
			$aiad_st( __( '3 minutes.', 'ai-awareness-day' ), __( 'Read the Talk about it question together and take two or three answers.', 'ai-awareness-day' ) ),
		)
	),
	$aiad_session(
		__( 'A 50-minute lesson: know it, question it, act on it', 'ai-awareness-day' ),
		aiad_block_text( __( 'For Key Stages 3 and 4, in Computing, PSHE/RSHE or Citizenship. By the end, students can explain key AI terms in plain English, name an issue a technology raises, and say where a human must stay in charge. You need this page on the board, and either one device per group or each group\'s family printed as cards (choose the family, then press Print these as cards).', 'ai-awareness-day' ) ),
		array(
			$aiad_st( __( '5 minutes. Starter:', 'ai-awareness-day' ), __( 'run a quick version of the guessing game above (steps 1 to 3) with Hallucination.', 'ai-awareness-day' ) ),
			$aiad_st( __( '10 minutes. Know it:', 'ai-awareness-day' ), __( 'split the class into seven groups, one for each family (in a large class, two groups can share a family). Each group chooses the three elements a Year 7 most needs to know and rewrites each definition in its own words.', 'ai-awareness-day' ) ),
			$aiad_st( __( '15 minutes. Question it:', 'ai-awareness-day' ), __( 'each group opens Classroom notes for one of its elements and picks one issue it raises. If it has none, press Connections and choose a linked element that does. They prepare a one-minute answer: who could this help, and who could it harm?', 'ai-awareness-day' ) ),
			$aiad_st( __( '10 minutes. Act on it:', 'ai-awareness-day' ), __( 'each group opens one Secondary compound (below the table) that uses an element from its family and reads who stays in the loop. Then it writes one rule for using AI in school.', 'ai-awareness-day' ) ),
			$aiad_st( __( '10 minutes. Plenary:', 'ai-awareness-day' ), __( 'groups share their rules and the class votes on its top three. Exit ticket: each student writes one term from today in their own words, and one place where a human must stay in charge.', 'ai-awareness-day' ) ),
		),
		aiad_block_text( __( 'Some elements, such as Deepfake and Jailbreaking, can lead to conversations about safeguarding. Follow your school\'s procedures.', 'ai-awareness-day' ) )
	),
	$aiad_session(
		__( 'A 30-minute staff INSET: the AI words every teacher meets', 'ai-awareness-day' ),
		aiad_block_text( __( 'For a staff meeting or training day. By the end, staff share the same AI vocabulary and have a plan for using the table this term.', 'ai-awareness-day' ) ),
		array(
			$aiad_st( __( '5 minutes.', 'ai-awareness-day' ), __( 'Show to class with the definition hidden, for three words staff hear most: Large Language Model, Hallucination and Shadow AI. Staff guess first, just as students would.', 'ai-awareness-day' ) ),
			$aiad_st( __( '10 minutes.', 'ai-awareness-day' ), __( 'If your school has taken the AI Risk Readiness Benchmark, pick the area that scored lowest and open five elements from the family closest to it: each element names the part of the benchmark it belongs to. If not, start with Privacy and Shadow AI.', 'ai-awareness-day' ) ),
			$aiad_st( __( '10 minutes.', 'ai-awareness-day' ), __( 'Departments choose one theme, filter the table by it, and pick an element of the week for each week of the half term. Press Print these as cards to print the whole theme, then keep the cards you chose.', 'ai-awareness-day' ) ),
			$aiad_st( __( '5 minutes.', 'ai-awareness-day' ), __( 'Agree which elements need care in class, such as Deepfake and Jailbreaking, and remind staff who to report concerns to. Agree one rule too: no pupil\'s personal data goes into an AI tool the school has not approved.', 'ai-awareness-day' ) ),
		),
		$aiad_link( aiad_literacy_benchmark_url(), __( 'Take the AI Risk Readiness Benchmark', 'ai-awareness-day' ) )
	),
	$aiad_session(
		__( 'For parents and carers: ten AI words your child is hearing', 'ai-awareness-day' ),
		aiad_block_text( __( 'Share this list, or the link to this page. Ask your child which words they have heard, and what they think each one means. You don\'t need to be an expert: asking is the best start.', 'ai-awareness-day' ) ),
		$aiad_parent_list,
		aiad_block_text( __( 'Worried about something your child has seen online? Talk to their school, or call the NSPCC helpline on 0808 800 5000. Children can talk to Childline on 0800 1111.', 'ai-awareness-day' ) )
	),
);

$aiad_out[] = aiad_block_group(
	array(
		aiad_block_group(
			array(
				aiad_block_heading( 2, aiad_block_text( __( 'Ready-made sessions', 'ai-awareness-day' ) ), 'alpt-section-title', 'alpt-sessions' ),
				aiad_block_paragraph( aiad_block_text( __( 'Four sessions you can run straight from this page, with timings: a starter, a full lesson, a staff INSET and a page for parents.', 'ai-awareness-day' ) ), 'alpt-section-lead' ),
				aiad_block_group( $aiad_sessions, 'alpt-faq' ),
			),
			'container'
		),
	),
	'alpt-sessions-wrap',
	'section',
	'',
	__( 'Ready-made sessions', 'ai-awareness-day' )
);

// Help with a display board: questions teachers ask, as details blocks, edited on the page.

$aiad_faq = array(
	array(
		__( 'How do I print the table for a display board?', 'ai-awareness-day' ),
		$aiad_t( __( 'Press', 'ai-awareness-day' ) ) . ' <a href="#alpt-print">' . $aiad_t( __( 'Print the poster', 'ai-awareness-day' ) ) . '</a> ' . $aiad_t( __( 'under the filters, just above the table. The table prints on one landscape page, with a title and a colour key. It is best on A3 for a wall, where every name is easy to read; on A4 it suits a desk or a handout. To print element cards to cut out, twelve to an A4 page, choose a family or theme (or search for a word) and press Print these as cards under the filters. Choose Technical terms, AIAD terms or Year above the table first: the poster prints in whichever view is showing.', 'ai-awareness-day' ) ),
	),
	array(
		__( 'Technical terms, AIAD terms or Year: which should I put up?', 'ai-awareness-day' ),
		$aiad_t( __( 'Technical terms colours the table by family, showing what each technology is, from foundations to infrastructure. AIAD terms colours the same table by the five campaign themes, Safe, Smart, Creative, Responsible and Future, so students can see how each theme runs through the technology. Year colours it by when each word came into use, from the 1800s to now. Many schools print two side by side.', 'ai-awareness-day' ) ),
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
		$aiad_t( __( 'Any: pitch each element and question to your class. In the compound challenge, Primary compounds suit Key Stage 2, Secondary ones Key Stages 3 and 4, and Further education ones post-16 and staff. It works in tutor time, PSHE, computing, English or any lesson where AI comes up.', 'ai-awareness-day' ) ),
	),
	array(
		__( 'Where does it fit in the curriculum, and where should each key stage start?', 'ai-awareness-day' ),
		$aiad_t( __( 'It supports Computing (how computer systems and data work, and using technology safely, respectfully and responsibly), RSHE (online relationships, internet safety and harms, and media literacy) and Citizenship (debate, rights and how laws are made). The Safe and Responsible themes also support your school\'s online safety work. It maps to UNESCO\'s AI competency framework for students (a human-centred mindset, the ethics of AI, AI techniques and applications, and AI system design) and supports the DfE\'s guidance on generative AI in education.', 'ai-awareness-day' ) ) . ' '
		. $aiad_b( __( 'Key Stage 2:', 'ai-awareness-day' ) ) . ' ' . $aiad_t( __( 'start with Foundations and the Safe theme, and the Primary compounds in the compound challenge.', 'ai-awareness-day' ) ) . ' '
		. $aiad_b( __( 'Key Stage 3:', 'ai-awareness-day' ) ) . ' ' . $aiad_t( __( 'Generative AI and AI agents, with the 50-minute lesson.', 'ai-awareness-day' ) ) . ' '
		. $aiad_b( __( 'Key Stage 4 and post-16:', 'ai-awareness-day' ) ) . ' ' . $aiad_t( __( 'Governance & Ethics, the Year view and the Further education compounds.', 'ai-awareness-day' ) ) . ' '
		. $aiad_b( __( 'Staff:', 'ai-awareness-day' ) ) . ' ' . $aiad_t( __( 'the 30-minute INSET in Ready-made sessions.', 'ai-awareness-day' ) ),
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

// Closing call to action: the day itself, where the page's aim leads.
$aiad_out[] = aiad_block_group(
	array(
		aiad_block_group(
			array(
				aiad_block_paragraph( aiad_block_text( __( 'Thursday 29 April 2027', 'ai-awareness-day' ) ), 'alpt-sticker' ),
				aiad_block_heading( 2, aiad_block_text( __( 'Ready for AI Awareness Day?', 'ai-awareness-day' ) ), 'alpt-close__title', 'alpt-close' ),
				aiad_block_paragraph( aiad_block_text( __( 'On Thursday 29 April 2027, put the poster up, run the 50-minute lesson, and turn a Talk about it question into a debate for the National AI Conversation. Know the word, question it, and keep humans in the loop.', 'ai-awareness-day' ) ), 'alpt-close__text' ),
				aiad_block_paragraph( $aiad_button( esc_url( home_url( '/#contact' ) ), __( 'Get involved', 'ai-awareness-day' ), 'ink' ) . $aiad_button( esc_url( home_url( '/national-conversation/' ) ), __( 'Join the National AI Conversation', 'ai-awareness-day' ), 'ghost' ) . $aiad_button( esc_url( aiad_literacy_benchmark_url() ), __( 'Take the benchmark', 'ai-awareness-day' ), 'ghost' ), 'alpt-actions' ),
			),
			'container'
		),
	),
	'alpt-close',
	'section',
	'',
	__( 'AI Awareness Day', 'ai-awareness-day' )
);

// The page's main element is its outermost block, so the editor shows the content inside .alpt-page as the site does.
echo aiad_block_group( $aiad_out, 'alpt-page', 'main', 'main', __( 'AI Literacy Periodic Table', 'ai-awareness-day' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block markup; every value is escaped above.
