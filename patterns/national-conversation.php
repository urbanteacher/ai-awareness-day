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

// Block markup. Text passed in is HTML, already escaped; $aiad_e() escapes plain text. The variables are prefixed
// because WordPress includes pattern files inside its own functions.
$aiad_e = static fn( string $text ): string => str_replace( '&#039;', "'", esc_html( $text ) ); // Apostrophes as the editor saves them.

$aiad_attrs = static function ( array $attributes ): string {
	$attributes = array_filter( $attributes, static fn( $value ): bool => null !== $value && '' !== $value );
	return $attributes ? ' ' . serialize_block_attributes( $attributes ) : '';
};

$aiad_blocks = static fn( array $inner ): string => implode( "\n\n", array_filter( $inner ) );

$aiad_p = static function ( string $html, string $class = '', string $id = '' ) use ( $aiad_attrs ): string {
	$open = '<p' . ( '' !== $class ? ' class="' . esc_attr( $class ) . '"' : '' ) . ( '' !== $id ? ' id="' . esc_attr( $id ) . '"' : '' ) . '>';
	return '<!-- wp:paragraph' . $aiad_attrs( array( 'className' => $class, 'anchor' => $id ) ) . " -->\n" . $open . $html . "</p>\n<!-- /wp:paragraph -->";
};

$aiad_h = static function ( int $level, string $html, string $class = '', string $id = '' ) use ( $aiad_attrs ): string {
	$json = $aiad_attrs( array( 'level' => 2 === $level ? null : $level, 'className' => $class, 'anchor' => $id ) );
	$tag  = 'h' . $level;
	return '<!-- wp:heading' . $json . " -->\n<" . $tag . ( '' !== $id ? ' id="' . esc_attr( $id ) . '"' : '' ) . ' class="wp-block-heading' . ( '' !== $class ? ' ' . esc_attr( $class ) : '' ) . '">' . $html . '</' . $tag . ">\n<!-- /wp:heading -->";
};

$aiad_group = static function ( array $inner, string $class, string $tag = 'div', string $id = '', string $name = '' ) use ( $aiad_attrs, $aiad_blocks ): string {
	$json = $aiad_attrs(
		array(
			'tagName'   => 'div' === $tag ? null : $tag,
			'metadata'  => '' !== $name ? array( 'name' => $name ) : null,
			'className' => $class,
			'layout'    => array( 'type' => 'default' ),
			'anchor'    => $id,
		)
	);
	return '<!-- wp:group' . $json . " -->\n<" . $tag . ( '' !== $id ? ' id="' . esc_attr( $id ) . '"' : '' ) . ' class="wp-block-group' . ( '' !== $class ? ' ' . esc_attr( $class ) : '' ) . '">' . $aiad_blocks( $inner ) . '</' . $tag . ">\n<!-- /wp:group -->";
};

// Styled list (blocks/list): $extra holds the aria-label or aria-labelledby.
$aiad_list = static function ( array $inner, string $class, string $tag = 'ul', array $extra = array() ) use ( $aiad_attrs, $aiad_blocks ): string {
	$html = '';
	foreach ( $extra as $name => $value ) {
		$html .= ' ' . $name . '="' . esc_attr( $value ) . '"';
	}
	return '<!-- wp:aiad/list' . $aiad_attrs( array( 'tagName' => 'ul' === $tag ? null : $tag, 'className' => $class ) ) . " -->\n<" . $tag . $html . ' class="' . esc_attr( $class ) . '">' . $aiad_blocks( $inner ) . '</' . $tag . ">\n<!-- /wp:aiad/list -->";
};

$aiad_item = static function ( array $inner, string $class = '', string $tag = 'li' ) use ( $aiad_attrs, $aiad_blocks ): string {
	return '<!-- wp:aiad/item' . $aiad_attrs( array( 'tagName' => 'li' === $tag ? null : $tag, 'className' => $class ) ) . " -->\n<" . $tag . ( '' !== $class ? ' class="' . esc_attr( $class ) . '"' : '' ) . '>' . $aiad_blocks( $inner ) . '</' . $tag . ">\n<!-- /wp:aiad/item -->";
};

$aiad_text = static function ( string $html, string $tag = 'span' ) use ( $aiad_attrs ): string {
	return '<!-- wp:aiad/text' . $aiad_attrs( array( 'tagName' => 'span' === $tag ? null : $tag ) ) . " -->\n<" . $tag . '>' . $html . '</' . $tag . ">\n<!-- /wp:aiad/text -->";
};

// A core list of plain items.
$aiad_core_list = static function ( array $items, bool $ordered = false ) use ( $aiad_attrs ): string {
	$tag   = $ordered ? 'ol' : 'ul';
	$lines = array_map( static fn( string $html ): string => "<!-- wp:list-item -->\n<li>" . $html . "</li>\n<!-- /wp:list-item -->", $items );
	return '<!-- wp:list' . $aiad_attrs( array( 'ordered' => $ordered ? true : null ) ) . " -->\n<" . $tag . ' class="wp-block-list">' . implode( "\n\n", $lines ) . '</' . $tag . ">\n<!-- /wp:list -->";
};

$aiad_image = static function ( string $file, string $alt, string $caption, string $class ) use ( $aiad_attrs ): string {
	$src = AIAD_URI . '/assets/images/national-conversation/' . $file;
	return '<!-- wp:image' . $aiad_attrs( array( 'className' => $class ) ) . " -->\n" . '<figure class="wp-block-image ' . esc_attr( $class ) . '"><img src="' . esc_url( $src ) . '" alt="' . esc_attr( $alt ) . '"/><figcaption class="wp-element-caption">' . esc_html( $caption ) . "</figcaption></figure>\n<!-- /wp:image -->";
};

$aiad_quote = static function ( string $html, string $class ) use ( $aiad_attrs, $aiad_p ): string {
	return '<!-- wp:quote' . $aiad_attrs( array( 'className' => $class ) ) . " -->\n" . '<blockquote class="wp-block-quote ' . esc_attr( $class ) . '">' . $aiad_p( $html ) . "</blockquote>\n<!-- /wp:quote -->";
};

$aiad_details = static function ( string $summary, string $html, string $class ) use ( $aiad_attrs, $aiad_p ): string {
	return '<!-- wp:details' . $aiad_attrs( array( 'className' => $class ) ) . " -->\n" . '<details class="wp-block-details ' . esc_attr( $class ) . '"><summary>' . $summary . '</summary>' . $aiad_p( $html ) . "</details>\n<!-- /wp:details -->";
};

$aiad_section = static fn( array $inner, string $name, bool $card = false, string $container = 'container' ): string => $aiad_group( array( $aiad_group( $inner, $container ) ), 'ncp-section' . ( $card ? ' ncp-section--card' : '' ), 'section', '', $name );

$aiad_photo_credit = static fn( string $who ): string => sprintf( /* translators: %s: photographer */ __( 'Photo: %s on Unsplash', 'ai-awareness-day' ), $who );

/* ------------------------------------------------------------------ The page */

$aiad_out = array();

// Hero.
$aiad_out[] = $aiad_group(
	array(
		$aiad_group(
			array(
				$aiad_p( $aiad_e( __( 'AI Awareness Day', 'ai-awareness-day' ) ) . ' {event_year} <span aria-hidden="true">·</span> ' . $aiad_e( __( 'Humans in the Loop', 'ai-awareness-day' ) ), 'ncp-eyebrow' ),
				$aiad_h( 1, $aiad_e( __( 'The National AI Conversation', 'ai-awareness-day' ) ), 'ncp-title', 'ncp-title' ),
				$aiad_p( $aiad_e( __( 'A national conversation about AI, led by young people. Question it. Challenge it. Debate it.', 'ai-awareness-day' ) ), 'ncp-lead' ),
				$aiad_list(
					array(
						$aiad_item( array( $aiad_text( '{opens_label}', 'dt' ), $aiad_text( '{opens}', 'dd' ) ), '', 'div' ),
						$aiad_item( array( $aiad_text( $aiad_e( __( 'AI Awareness Day', 'ai-awareness-day' ) ), 'dt' ), $aiad_text( '{event}', 'dd' ) ), '', 'div' ),
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
$aiad_out[] = $aiad_group(
	array(
		$aiad_group(
			array(
				$aiad_image( 'classroom-hand-up.jpg', __( 'A pupil presents her work to the class while a classmate raises a hand to ask a question', 'ai-awareness-day' ), $aiad_photo_credit( 'Taylor Flowe' ), 'ncp-photo ncp-photo--tall' ),
				$aiad_group(
					array(
						$aiad_h( 2, $aiad_e( __( 'Bigger than a single awareness day', 'ai-awareness-day' ) ), '', 'ncp-what' ),
						$aiad_p( $aiad_e( sprintf( /* translators: 1: opening date, 2: AI Awareness Day date */ __( 'From %1$s, schools can question, discuss and debate the role AI should play in their lives and futures, building up to AI Awareness Day on %2$s.', 'ai-awareness-day' ), '{opens}', '{event}' ) ), 'ncp-big' ),
						$aiad_p( $aiad_e( __( 'Young people will not simply be taught about AI. They will listen to different perspectives and form their own judgements.', 'ai-awareness-day' ) ) ),
						$aiad_p( $aiad_e( __( 'Every student\'s journey', 'ai-awareness-day' ) ), 'ncp-label', 'ncp-journey' ),
						$aiad_list( array_map( static fn( string $stage ): string => $aiad_text( $aiad_e( $stage ), 'li' ), $aiad_journey ), 'ncp-arc', 'ol', array( 'aria-labelledby' => 'ncp-journey' ) ),
						$aiad_h( 3, $aiad_e( __( 'The skills we want young people to build in the age of AI', 'ai-awareness-day' ) ), 'ncp-subhead', 'ncp-skills' ),
						$aiad_list( array_map( static fn( array $skill ): string => $aiad_item( array( $aiad_text( $aiad_e( $skill[0] ), 'dt' ), $aiad_text( $aiad_e( $skill[1] ), 'dd' ) ), '', 'div' ), $aiad_skills ), 'ncp-skills', 'dl' ),
						$aiad_p( $aiad_e( __( 'It is designed to be easy to join. Start with five minutes in a lesson, or connect with another school for a structured debate. Nobody needs an account or a password.', 'ai-awareness-day' ) ) . ' <strong>' . $aiad_e( __( 'Maximum participation. Minimum administration.', 'ai-awareness-day' ) ) . '</strong>' ),
						$aiad_quote( $aiad_e( __( 'The technology connects the conversation. Young people do the thinking.', 'ai-awareness-day' ) ), 'ncp-principle' ),
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
	$aiad_path_items[] = $aiad_item(
		array(
			$aiad_h( 3, $aiad_e( $aiad_path['who'] ) ),
			$aiad_p( $aiad_e( $aiad_path['tag'] ), 'ncp-paths__tag' ),
			$aiad_core_list( array_map( $aiad_e, $aiad_path['steps'] ), true ),
			$aiad_path['note'] ? $aiad_p( $aiad_e( $aiad_path['note'] ), 'ncp-paths__note' ) : '',
			$aiad_link ? $aiad_p( '<a href="' . esc_attr( $aiad_link[0] ) . '">' . $aiad_e( $aiad_link[1] ) . '</a>', 'ncp-paths__more' ) : '',
		)
	);
}
$aiad_out[] = $aiad_section(
	array(
		$aiad_h( 2, $aiad_e( __( 'Find your pathway', 'ai-awareness-day' ) ), '', 'ncp-paths' ),
		$aiad_p( $aiad_e( __( 'Every school gets one school code. Students add a class PIN; adults add their own email address and a six-digit code we send them. Nobody has a password.', 'ai-awareness-day' ) ), 'ncp-intro' ),
		$aiad_list( $aiad_path_items, 'ncp-paths' ),
	),
	__( 'Find your pathway', 'ai-awareness-day' ),
	true
);

// Five themes.
$aiad_theme_items = array();
foreach ( $aiad_themes as $aiad_slug => $aiad_theme ) {
	$aiad_theme_items[] = $aiad_item(
		array(
			'<!-- wp:aiad/strand-icon' . $aiad_attrs( array( 'strand' => 'safe' === $aiad_slug ? null : $aiad_slug ) ) . ' /-->',
			$aiad_h( 3, $aiad_e( $aiad_theme[0] ) ),
			$aiad_p( $aiad_e( $aiad_theme[1] ) ),
		),
		'ncp-theme ncp-theme--' . $aiad_slug
	);
}
$aiad_out[] = $aiad_section(
	array(
		$aiad_h( 2, $aiad_e( __( 'Five themes, one conversation', 'ai-awareness-day' ) ), '', 'ncp-themes' ),
		$aiad_list( $aiad_theme_items, 'ncp-themes' ),
	),
	__( 'Five themes', 'ai-awareness-day' )
);

// Three ways to take part.
$aiad_out[] = $aiad_section(
	array(
		$aiad_h( 2, $aiad_e( __( 'Three ways to take part', 'ai-awareness-day' ) ), '', 'ncp-ways' ),
		$aiad_image( 'classroom-listening.jpg', __( 'Pupils at their desks, seen from the back of the classroom, listening to their teacher', 'ai-awareness-day' ), $aiad_photo_credit( 'Taylor Flowe' ), 'ncp-photo ncp-photo--wide ncp-photo--classroom' ),
		$aiad_list( array_map( static fn( array $way ): string => $aiad_item( array( $aiad_h( 3, $aiad_e( $way[0] ) ), $aiad_p( $aiad_e( $way[1] ) ) ) ), $aiad_ways ), 'ncp-ways', 'ol' ),
		$aiad_p( $aiad_e( __( 'Each works at primary, secondary and post-16, with questions pitched for the age.', 'ai-awareness-day' ) ) . ' <a class="ncp-link" href="#ncp-ages">' . $aiad_e( __( 'See how each age debates', 'ai-awareness-day' ) ) . '</a>', 'ncp-note' ),
	),
	__( 'Three ways to take part', 'ai-awareness-day' ),
	true
);

// Every age.
$aiad_age_items = array();
foreach ( $aiad_ages as $aiad_age ) {
	$aiad_rows = array(
		$aiad_h( 3, $aiad_e( $aiad_age['name'] ) ),
		$aiad_p( $aiad_e( $aiad_age['years'] ), 'ncp-agecards__years' ),
	);
	foreach ( $aiad_age_rows as $aiad_row_key => $aiad_row_label ) {
		$aiad_rows[] = $aiad_group( array( $aiad_h( 4, $aiad_e( $aiad_row_label ) ), $aiad_p( $aiad_e( $aiad_age[ $aiad_row_key ] ) ) ), 'ncp-agecards__row ncp-agecards__row--' . $aiad_row_key );
	}
	$aiad_age_items[] = $aiad_item( $aiad_rows );
}
$aiad_out[] = $aiad_section(
	array(
		$aiad_h( 2, $aiad_e( __( 'One conversation, pitched for every age', 'ai-awareness-day' ) ), '', 'ncp-ages' ),
		$aiad_p( $aiad_e( __( 'Primary, secondary and post-16 students debate the same five themes. What changes is how the question is asked, how they prepare and how the debate runs. Here is one idea from the Safe theme, what AI should remember about you, as each age meets it.', 'ai-awareness-day' ) ), 'ncp-intro' ),
		$aiad_list( $aiad_age_items, 'ncp-agecards' ),
		$aiad_p( $aiad_e( __( 'Every age has the same 30 minutes and is judged on the same four things, in simpler words for primary, and every theme has a question written for each age. The questions, timings and wording are being reviewed by debate educators before the conversation opens.', 'ai-awareness-day' ) ) ),
	),
	__( 'Every age', 'ai-awareness-day' )
);

// Inside a debate.
$aiad_out[] = $aiad_section(
	array(
		$aiad_h( 2, $aiad_e( __( 'Inside a debate', 'ai-awareness-day' ) ), '', 'ncp-inside' ),
		$aiad_p( $aiad_e( __( 'About 30 minutes, and the whole class takes part, not just the speakers.', 'ai-awareness-day' ) ), 'ncp-intro' ),
		$aiad_image( 'student-writing.jpg', __( 'A student in a hall of red seats writes notes on a sheet of paper', 'ai-awareness-day' ), $aiad_photo_credit( 'Kate Tweedy' ), 'ncp-photo ncp-photo--wide ncp-photo--writing' ),
		$aiad_list( array_map( static fn( array $part ): string => $aiad_item( array( $aiad_p( $aiad_e( $part[0] ), 'ncp-running__time' ), $aiad_h( 3, $aiad_e( $part[1] ) ), $aiad_p( $aiad_e( $part[2] ) ) ) ), $aiad_running ), 'ncp-running', 'ol' ),
		$aiad_group(
			array(
				$aiad_group(
					array(
						$aiad_h( 3, $aiad_e( __( 'A role for everyone', 'ai-awareness-day' ) ) ),
						$aiad_p( $aiad_e( __( 'Each school gets a prep pack: a checklist, a short survey to run two to three weeks before with the same questions in both schools, research in three groups, and roles for a class of about 30.', 'ai-awareness-day' ) ) ),
					),
					''
				),
				$aiad_group(
					array(
						$aiad_h( 3, $aiad_e( __( 'Judged on four things', 'ai-awareness-day' ) ) ),
						$aiad_list( array_map( static fn( string $chip ): string => $aiad_text( $aiad_e( $chip ) ), array( __( 'Argument', 'ai-awareness-day' ), __( 'Evidence', 'ai-awareness-day' ), __( 'Rebuttal', 'ai-awareness-day' ), __( 'Delivery', 'ai-awareness-day' ) ) ), 'ncp-chips', 'p' ),
						$aiad_p( $aiad_e( __( 'Each is scored from 1 to 5. The criteria are the same at every age, in simpler words for primary.', 'ai-awareness-day' ) ) ),
					),
					''
				),
			),
			'ncp-inside'
		),
		$aiad_quote( $aiad_e( __( 'Several sources for every fact. Don\'t rely on AI-generated content: it can be wrong.', 'ai-awareness-day' ) ), 'ncp-principle' ),
		$aiad_p( $aiad_e( __( 'The running order is a recommendation, not a rule, so schools can fit it to their setting and their students.', 'ai-awareness-day' ) ), 'ncp-note' ),
	),
	__( 'Inside a debate', 'ai-awareness-day' ),
	true
);

// How an inter-school debate works.
$aiad_out[] = $aiad_section(
	array(
		$aiad_h( 2, $aiad_e( __( 'How an inter-school debate works', 'ai-awareness-day' ) ), '', 'ncp-how' ),
		$aiad_p( $aiad_e( __( 'Identify your school once. We take care of the rest.', 'ai-awareness-day' ) ), 'ncp-motto' ),
		$aiad_p( $aiad_e( __( 'It should feel more like arranging a school sports fixture than registering for another complicated education platform. It is not a learning management system, a pupil social network or a heavyweight competition platform.', 'ai-awareness-day' ) ), 'ncp-intro' ),
		$aiad_group(
			array(
				$aiad_h( 3, $aiad_e( __( 'Headteacher approval comes first', 'ai-awareness-day' ) ) ),
				$aiad_p( $aiad_e( __( 'Every school taking part, including the one you invite, is approved by its headteacher or a senior leader before anything goes live. They get one email and approve with one click. Until then a school cannot run a class PIN for students, start or join a debate, or see Join the conversation. If the email is missed, the lead teacher can send it again.', 'ai-awareness-day' ) ) ),
			),
			'ncp-callout',
			'div',
			'ncp-approval'
		),
		$aiad_list( array_map( static fn( array $step ): string => $aiad_item( array( $aiad_h( 3, $aiad_e( $step[0] ) ), $aiad_p( str_replace( 'AID-7K42P', '<span class="ncp-nowrap">AID-7K42P</span>', $aiad_e( $step[1] ) ) ) ) ), $aiad_steps ), 'ncp-steps', 'ol' ),
		$aiad_p( $aiad_e( __( 'One Debate ID. Two school leads. One judge. One result.', 'ai-awareness-day' ) ), 'ncp-tally' ),
		$aiad_p( $aiad_e( __( 'If a step stalls, we send reminders after 3 and 7 days. A debate with no action for 14 days expires, and the school can invite another.', 'ai-awareness-day' ) ), 'ncp-note' ),
	),
	__( 'How a debate works', 'ai-awareness-day' )
);

// Schools stay in charge.
$aiad_out[] = $aiad_section(
	array(
		$aiad_group(
			array(
				$aiad_h( 2, $aiad_e( __( 'Schools stay in charge', 'ai-awareness-day' ) ), '', 'ncp-safe' ),
				$aiad_p( $aiad_e( __( 'The platform connects schools. Each school remains responsible for its own safeguarding, supervision, travel, parental permissions, risk assessments and visitor arrangements.', 'ai-awareness-day' ) ) ),
				$aiad_p( $aiad_e( __( 'When two schools are matched, both teachers automatically receive our Debate & Safeguarding Pack to help them prepare.', 'ai-awareness-day' ) ) ),
				$aiad_p( $aiad_e( __( 'Every debate has an independent judge, such as a professional, technologist, academic or community leader. Judges are invited by the host teacher, never through an open call, and the host school\'s own visitor procedures apply.', 'ai-awareness-day' ) ) ),
				$aiad_p( $aiad_e( __( 'If something goes wrong, either school can report it: a cancellation, a conduct or safeguarding concern, or a result that does not reflect what happened. Both schools\' senior leaders are told, the result is held, and the report stays private. Schools resolve it under their own procedures.', 'ai-awareness-day' ) ) ),
			),
			''
		),
		$aiad_group(
			array(
				$aiad_h( 3, $aiad_e( __( 'National Debate Code of Conduct', 'ai-awareness-day' ) ), '', 'ncp-conduct' ),
				$aiad_p( $aiad_e( __( 'Challenge the argument. Respect the person.', 'ai-awareness-day' ) ), 'ncp-conduct__line' ),
				$aiad_p( $aiad_e( __( 'It applies to students, teachers, judges, visitors and audiences. Strong disagreement is encouraged; personal attacks, discriminatory language, intimidation, deliberate disruption and abusive audience behaviour are not.', 'ai-awareness-day' ) ) ),
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
		$aiad_h( 2, $aiad_e( __( 'Recognising taking part, not only winning', 'ai-awareness-day' ) ), '', 'ncp-recognition' ),
		$aiad_group( array_map( static fn( array $count ): string => $aiad_group( array( $aiad_p( $count[0], 'ncp-recognition__count' ), $aiad_p( $aiad_e( $count[1] ) ), $aiad_p( $aiad_e( $count[2] ), 'ncp-recognition__result' ) ), '' ), $aiad_counts ), 'ncp-recognition' ),
		$aiad_p( $aiad_e( __( 'The programme rewards conversation, connection and breadth: the schools you meet, the students involved and the themes explored. MATs and regions can run competitive events of their own, but there is no national league table of winners.', 'ai-awareness-day' ) ) ),
		$aiad_p( $aiad_e( __( 'Results are public: both schools, the theme, the motion, the date, the result and, if they agree, the judge\'s name. Teachers\' names and anything about students are never published. Every certificate carries a reference that anyone can check.', 'ai-awareness-day' ) ) ),
	),
	__( 'Recognition', 'ai-awareness-day' )
);

// MATs and communities.
$aiad_out[] = $aiad_section(
	array(
		$aiad_h( 2, $aiad_e( __( 'Whole MATs and communities can join in', 'ai-awareness-day' ) ), '', 'ncp-networks' ),
		$aiad_list( array_map( static fn( string $level ): string => $aiad_text( $aiad_e( $level ) ), $aiad_network ), 'ncp-chain', 'p', array( 'aria-label' => __( 'Classroom, then school, then school to school, then MAT, then region, then national', 'ai-awareness-day' ) ) ),
		$aiad_p( $aiad_e( __( 'A multi-academy trust (MAT) can hold debates between its own academies before connecting with schools elsewhere. Each academy registers and is approved by its own headteacher, and the programme team can share a report of the MAT\'s academies taking part.', 'ai-awareness-day' ) ) ),
	),
	__( 'MATs and communities', 'ai-awareness-day' ),
	true,
	'container ncp-narrow'
);

// Organisations.
$aiad_out[] = $aiad_section(
	array(
		$aiad_h( 2, $aiad_e( __( 'For organisations that bring AI into schools', 'ai-awareness-day' ) ), '', 'ncp-partners' ),
		$aiad_p( $aiad_e( __( 'Take learning beyond the resource.', 'ai-awareness-day' ) ), 'ncp-motto' ),
		$aiad_p( $aiad_e( __( 'If your organisation supports schools with AI lessons, resources or programmes, this is the natural next step: students put what they have learned to work, testing their thinking against peers from another school, in front of an audience and an independent judge.', 'ai-awareness-day' ) ), 'ncp-intro' ),
		$aiad_list( array_map( static fn( array $point ): string => $aiad_item( array( $aiad_h( 3, $aiad_e( $point[0] ) ), $aiad_p( $aiad_e( $point[1] ) ) ) ), $aiad_partner_points ), 'ncp-partner' ),
		$aiad_p( $aiad_e( __( 'Whoever brings a school along, the school always confirms its own place. Every email to an organisation lets it stop the emails, or say “this wasn\'t us”.', 'ai-awareness-day' ) ) ),
		'<!-- wp:aiad/nc-actions {"variant":"nominate"} /-->',
		$aiad_group(
			array(
				$aiad_h( 3, $aiad_e( __( 'Interested in supporting the national debate?', 'ai-awareness-day' ) ) ),
				$aiad_p( $aiad_e( __( 'Don\'t hesitate to reach out to us. A few partnership places remain for organisations that want to help with:', 'ai-awareness-day' ) ) ),
				$aiad_core_list(
					array(
						$aiad_e( __( 'Powering the platform that hosts the debates', 'ai-awareness-day' ) ),
						$aiad_e( __( 'Keynote talks from technology companies at debates and events', 'ai-awareness-day' ) ),
						$aiad_e( __( 'Guest speakers for schools serving disadvantaged communities', 'ai-awareness-day' ) ),
						$aiad_e( __( 'Hosting debates and events for those schools', 'ai-awareness-day' ) ),
					)
				),
				$aiad_p( $aiad_e( __( 'Partners support the programme, not individual schools. They never see schools\', staff or students\' details, and guest speakers visit under each school\'s own visitor procedures.', 'ai-awareness-day' ) ), 'ncp-callout__small' ),
				$aiad_p( '<a class="ncp-btn ncp-btn--primary" href="' . esc_url( home_url( '/#contact' ) ) . '">' . $aiad_e( __( 'Get in touch', 'ai-awareness-day' ) ) . '</a>', 'ncp-actions' ),
			),
			'ncp-callout ncp-callout--invite'
		),
	),
	__( 'For organisations', 'ai-awareness-day' )
);

// Everyone has a part to play.
$aiad_out[] = $aiad_section(
	array(
		$aiad_h( 2, $aiad_e( __( 'Everyone has a part to play', 'ai-awareness-day' ) ), '', 'ncp-who' ),
		$aiad_list( array_map( static fn( array $audience ): string => $aiad_item( array( $aiad_h( 3, $aiad_e( $audience[0] ) ), $aiad_p( $aiad_e( $audience[1] ), 'ncp-who__line' ), $audience[2] ? $aiad_p( $aiad_e( $audience[2] ), 'ncp-who__note' ) : '' ) ), $aiad_audiences ), 'ncp-who' ),
	),
	__( 'Everyone has a part', 'ai-awareness-day' ),
	true
);

// Timeline.
$aiad_out[] = $aiad_section(
	array(
		$aiad_h( 2, $aiad_e( __( 'The timeline', 'ai-awareness-day' ) ), '', 'ncp-timeline' ),
		$aiad_list( array_map( static fn( array $stage ): string => $aiad_item( array( $aiad_h( 3, $aiad_e( $stage[0] ) ), $aiad_p( $aiad_e( $stage[1] ) ) ) ), $aiad_timeline ), 'ncp-timeline', 'ol' ),
	),
	__( 'Timeline', 'ai-awareness-day' ),
	false,
	'container ncp-narrow'
);

// Questions.
$aiad_out[] = $aiad_section(
	array_merge(
		array( $aiad_h( 2, $aiad_e( __( 'Questions schools ask', 'ai-awareness-day' ) ), '', 'ncp-faq' ) ),
		array_map( static fn( array $faq ): string => $aiad_details( $aiad_e( $faq[0] ), $aiad_e( $faq[1] ), 'ncp-faq' ), $aiad_faqs )
	),
	__( 'Questions', 'ai-awareness-day' ),
	true,
	'container ncp-narrow'
);

// Closing call to action.
$aiad_out[] = $aiad_group(
	array(
		$aiad_group(
			array(
				$aiad_h( 2, $aiad_e( __( 'Ready to start the conversation?', 'ai-awareness-day' ) ), '', 'ncp-cta' ),
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
echo $aiad_group( $aiad_out, 'ncp', 'main', 'main', __( 'National Conversation', 'ai-awareness-day' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block markup; every value is escaped above.
