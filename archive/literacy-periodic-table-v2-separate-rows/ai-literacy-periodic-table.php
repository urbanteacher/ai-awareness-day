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
 * The introduction and the classroom ideas are core blocks, edited on the page. The table, with every definition
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
				aiad_block_paragraph( aiad_block_text( sprintf( __( '%d terms you will hear in conversations about AI, sorted into nine families: from the foundations everything is built on, to generative, predictive, agentic and automation AI, the governance and infrastructure that hold it all together, the latest buzzwords and trends, and how AI models are reverse engineered.', 'ai-awareness-day' ), $aiad_count ) ), 'alpt-lead' ),
				aiad_block_paragraph( aiad_block_text( __( 'Choose any element to read what it means in plain English. Pick a family to see it on its own, or search for a word.', 'ai-awareness-day' ) ), 'alpt-intro' ),
				aiad_block_paragraph( $aiad_button( '#alpt-print', __( 'Print the poster', 'ai-awareness-day' ), 'ink' ) . $aiad_button( '#alpt-classroom', __( 'Ways to use it in class', 'ai-awareness-day' ), 'ghost' ), 'alpt-actions' ),
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

// Ways to use it in class.
$aiad_ideas = array(
	array( __( 'Element of the day', 'ai-awareness-day' ), __( 'Press Random element at the start of a lesson. Read the definition together, then ask: where have we seen this, and why does it matter?', 'ai-awareness-day' ) ),
	array( __( 'Build a compound', 'ai-awareness-day' ), __( 'Give pairs a news story about AI. Which two or three elements explain it? A fake video of a head teacher might be Deepfake + Generative AI + Guardrails.', 'ai-awareness-day' ) ),
	array( __( 'Family experts', 'ai-awareness-day' ), __( 'Give each group one family. They teach the rest of the class its three most important elements, and why they chose them.', 'ai-awareness-day' ) ),
	array( __( 'What\'s missing?', 'ai-awareness-day' ), /* translators: %d: the next element's number */
		sprintf( __( 'Ask students to design element %d: its symbol, its family and a definition. Which family needs it most?', 'ai-awareness-day' ), $aiad_count + 1 ) ),
	array( __( 'Before and after', 'ai-awareness-day' ), __( 'Students mark the elements they could explain to a friend. Come back at the end of term and see how many more they can explain.', 'ai-awareness-day' ) ),
	array( __( 'Debate starter', 'ai-awareness-day' ), __( 'Many elements make a motion for a class debate. Bias, Privacy or Copyright & IP: should the rules on AI be tighter?', 'ai-awareness-day' ) ),
);

$aiad_out[] = aiad_block_group(
	array(
		aiad_block_group(
			array(
				aiad_block_heading( 2, aiad_block_text( __( 'Ways to use it in class', 'ai-awareness-day' ) ), 'alpt-section-title', 'alpt-classroom' ),
				aiad_block_paragraph( aiad_block_text( __( 'Quick activities for tutor time, PSHE, computing, English or any lesson where AI comes up. Pitch each element to your class.', 'ai-awareness-day' ) ), 'alpt-section-lead' ),
				aiad_block_group(
					array_map(
						static fn( array $idea ): string => aiad_block_group(
							array(
								aiad_block_heading( 3, aiad_block_text( $idea[0] ) ),
								aiad_block_paragraph( aiad_block_text( $idea[1] ) ),
							),
							'alpt-idea'
						),
						$aiad_ideas
					),
					'alpt-ideas'
				),
				aiad_block_group(
					array(
						aiad_block_group(
							array(
								aiad_block_heading( 3, aiad_block_text( __( 'For teachers', 'ai-awareness-day' ) ) ),
								aiad_block_paragraph( aiad_block_text( __( 'Print the poster for your classroom wall: it fits A3 or A4 landscape. Some elements, such as Deepfake, can lead to conversations about safeguarding, so follow your school\'s procedures and make sure students know who to talk to.', 'ai-awareness-day' ) ) ),
							),
							'alpt-audience'
						),
						aiad_block_group(
							array(
								aiad_block_heading( 3, aiad_block_text( __( 'For students', 'ai-awareness-day' ) ) ),
								/* translators: %d: number of elements */
								aiad_block_paragraph( aiad_block_text( sprintf( __( 'You don\'t need to learn all %d. Start with the family you use most, look up the terms you hear about the AI tools you already use, and remember: you are the human in the loop.', 'ai-awareness-day' ), $aiad_count ) ) ),
							),
							'alpt-audience'
						),
					),
					'alpt-audiences'
				),
			),
			'container'
		),
	),
	'alpt-classroom',
	'section',
	'',
	__( 'Ways to use it in class', 'ai-awareness-day' )
);

// The page's main element is its outermost block, so the editor shows the content inside .alpt-page as the site does.
echo aiad_block_group( $aiad_out, 'alpt-page', 'main', 'main', __( 'AI Literacy Periodic Table', 'ai-awareness-day' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block markup; every value is escaped above.
