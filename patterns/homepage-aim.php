<?php
/**
 * Title: Homepage: Aim
 * Slug: aiad/homepage-aim
 * Categories: aiad-homepage
 * Keywords: homepage, aim, section
 * Description: The Aim section in core blocks, so its heading and aims are edited on the page. Replaces the Aim section block (template-parts/front-page/section-aim.php).
 *
 * The aims fade in together (core list items take no class, so they cannot stagger as the template's do). The "Show more" button for small screens is added when the list renders (aiad_homepage_aims_expand_button()).
 *
 * @package AI_Awareness_Day
 */

$aiad_aims = array(
	__( 'Help young people keep humans in the loop — knowing when to trust AI, when to check it, and when to decide without it.', 'ai-awareness-day' ),
	__( 'Give classrooms a shared language for five conversations: Safe, Smart, Creative, Responsible and Future.', 'ai-awareness-day' ),
	__( 'Build the habit of questioning what AI knows about you, what it decides for you, and what it makes in your name.', 'ai-awareness-day' ),
	__( 'Strengthen digital resilience so students can navigate an AI-shaped world with judgement, not fear.', 'ai-awareness-day' ),
	__( 'Inspire creative and responsible use of AI across the curriculum — with authorship and attribution kept honest.', 'ai-awareness-day' ),
	__( 'Grow a national conversation about the AI already in young people’s lives: Your AI. Your choices.', 'ai-awareness-day' ),
);
?>
<!-- wp:group {"tagName":"section","metadata":{"name":"Aim","patternName":"aiad/homepage-aim"},"className":"section section--green","layout":{"type":"default"},"anchor":"aim"} -->
<section id="aim" class="wp-block-group section section--green"><!-- wp:group {"className":"container","layout":{"type":"default"}} -->
<div class="wp-block-group container"><!-- wp:group {"className":"fade-up","layout":{"type":"default"}} -->
<div class="wp-block-group fade-up"><!-- wp:paragraph {"className":"section-label"} -->
<p class="section-label"><?php esc_html_e( 'Aim', 'ai-awareness-day' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"section-title"} -->
<h2 class="wp-block-heading section-title"><?php esc_html_e( 'Keep humans in the loop', 'ai-awareness-day' ); ?></h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:list {"ordered":true,"className":"aims-list fade-up","anchor":"aims-list"} -->
<ol id="aims-list" class="wp-block-list aims-list fade-up"><?php
echo implode(
	"\n\n",
	array_map(
		static function ( string $aim ): string {
			return "<!-- wp:list-item -->\n<li>" . esc_html( $aim ) . "</li>\n<!-- /wp:list-item -->";
		},
		$aiad_aims
	)
);
?></ol>
<!-- /wp:list --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
