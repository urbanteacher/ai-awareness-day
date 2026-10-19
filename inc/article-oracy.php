<?php
/**
 * One-time: publish the oracy article, "What Does the New Government Announcement Mean for AI Awareness Day?", as a
 * campaign update (the timeline post type, shown at /timeline/). Runs once on the first request after a deploy; the
 * article can then be edited, retitled or trashed in the editor without this putting it back.
 *
 * Written as block markup, with the filters that strip markup from users who cannot post unfiltered HTML switched
 * off while it saves, so the blocks are stored as written.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The article's blocks.
 *
 * @return string Block markup.
 */
function aiad_article_oracy_content(): string {
	$p = static function ( string $text ): string {
		return "<!-- wp:paragraph -->\n<p>" . $text . "</p>\n<!-- /wp:paragraph -->\n\n";
	};
	$h = static function ( string $text ): string {
		return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . $text . "</h2>\n<!-- /wp:heading -->\n\n";
	};

	return $p( 'What happens to the value of writing when a machine can do it for you in seconds?' )
		. $p( 'That question sits quietly behind one of the most significant curriculum changes in a decade. Education Secretary Lucy Powell has announced that oracy (public speaking, structured debate and attentive listening) will be given equal status with reading and writing in England’s national curriculum, as a brand-new strand of English, assessed up to and including GCSE.' )
		. $p( 'On the surface, it is a policy about speaking skills. Look closer, and it reads like a bet on what will still make us valuable in an age of artificial intelligence. So what does it mean for AI Awareness Day on Thursday 29 April 2027, a campaign built around one idea: Keep Humans in the Loop?' )
		. $p( 'For years, the ed-tech conversation has centred on digital literacy. The prevailing wisdom said that to prepare children for a software-driven economy, we needed to teach them to code, or at least to write a precise AI prompt.' )
		. $p( 'The renewed focus on oracy points somewhere else. When generative AI can produce a competent essay, debug code in seconds or draft a legal summary, the value of routine written output falls sharply. As exam boards wrestle with AI-generated coursework, the traditional essay is under real pressure.' )
		. $p( 'The premium shifts from the text itself to the person who can explain and defend it.' )
		. $h( 'The human advantage' )
		. $p( 'By building spoken language into the curriculum from primary school to GCSE, the government is investing in the skills that are hardest to automate. In an AI-saturated job market, the capabilities that stand out are likely to be the distinctly human ones: persuasion, negotiation and reasoning out loud with other people.' )
		. $p( 'This gives new weight to AI Awareness Day’s 2027 theme, Keep Humans in the Loop. The campaign asks young people to question, discuss and debate the role AI should play in their lives and futures. In the past, busy schools might have treated an initiative like this as a worthwhile but optional computing assembly.' )
		. $p( 'Now there is a natural fit. The revised curriculum is due to be finalised in spring 2027 and taught from September 2028, so schools have a window to start building oracy skills before they become a formal requirement. AI is a ready-made subject for that practice: current, contested and directly relevant to students’ lives. The National AI Conversation, starting in January 2027, offers exactly this kind of structured speaking and listening, and it is far more than an exercise in tech ethics.' )
		. $h( 'Levelling the playing field' )
		. $p( 'There is also an equity argument. Confident public speaking has long been a marker of privilege, often cultivated in fee-paying schools that train pupils to hold a room. As more technical tasks are automated, leaving that advantage in place would put state-educated children further behind. Making oracy part of every child’s education is a step towards fairness in an AI-shaped economy.' )
		. $h( 'Not a silver bullet' )
		. $p( 'None of this will happen automatically. Critics, including former Ofsted chief Amanda Spielman, have warned that assessing speaking could fuel grade inflation, and headteachers point out that more time for oracy has to come from somewhere in an already crowded timetable. Others argue that confident speech is only valuable when it rests on solid knowledge. These are fair challenges. They are also a reminder that oracy and critical thinking about AI work best together: students need something worth saying, and the skills to say it well.' )
		. $h( 'The oldest medium for the newest challenge' )
		. $p( 'We are entering a curious moment, where one of the best ways to prepare young people for a high-tech future is to strengthen the oldest medium we have: the spoken word. The future may belong less to those who type fastest and more to those who can speak with clarity, empathy and conviction.' )
		. $p( 'For schools taking part in AI Awareness Day, the message is simple. To help children thrive alongside AI, we don’t need to teach them to think like computers. We need to help them talk, listen and reason like humans.' );
}

/**
 * Publish the article once, with its excerpt and topics (Policy & regulation first, then Teaching & learning).
 */
function aiad_migrate_article_oracy(): void {
	if ( get_option( 'aiad_article_oracy_added' ) === '1' || ! post_type_exists( 'timeline' ) || ! taxonomy_exists( 'timeline_category' ) ) {
		return;
	}
	$slug = 'oracy-and-ai-awareness-day';
	if ( get_page_by_path( $slug, OBJECT, 'timeline' ) ) {
		update_option( 'aiad_article_oracy_added', '1' );
		return;
	}

	kses_remove_filters();
	$id = wp_insert_post(
		array(
			'post_type'    => 'timeline',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => 'What Does the New Government Announcement Mean for AI Awareness Day?',
			'post_excerpt' => 'Speaking skills are set to sit alongside reading and writing in England’s curriculum. So what does the government’s announcement mean for AI Awareness Day 2027?',
			'post_content' => aiad_article_oracy_content(),
		)
	);
	kses_init_filters();
	if ( ! $id || is_wp_error( $id ) ) {
		return;
	}

	update_post_meta( $id, '_aiad_timeline_source', 'manual' );
	update_post_meta( $id, '_aiad_timeline_icon', 'announcement' );

	$terms = array();
	foreach ( array( 'policy-regulation', 'teaching-learning' ) as $topic ) {
		$term = get_term_by( 'slug', $topic, 'timeline_category' );
		if ( $term ) {
			$terms[] = (int) $term->term_id;
		}
	}
	if ( $terms ) {
		wp_set_object_terms( $id, $terms, 'timeline_category', false );
	}

	update_option( 'aiad_article_oracy_added', '1' );
}
add_action( 'init', 'aiad_migrate_article_oracy', 47 );
