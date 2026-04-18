<?php
/**
 * AI Tools: CPT, taxonomy, meta, admin UI, and rendering helpers.
 *
 * CPT slug:      ai_tool
 * Archive slug:  ai-tools
 * Taxonomy:      tool_category
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ──────────────────────────────────────────────
   1. CPT Registration
   ────────────────────────────────────────────── */

function aiad_register_ai_tool_post_type(): void {
	register_post_type( 'ai_tool', array(
		'labels' => array(
			'name'               => __( 'AI Tools', 'ai-awareness-day' ),
			'singular_name'      => __( 'AI Tool', 'ai-awareness-day' ),
			'add_new'            => __( 'Add Tool', 'ai-awareness-day' ),
			'add_new_item'       => __( 'Add New AI Tool', 'ai-awareness-day' ),
			'edit_item'          => __( 'Edit AI Tool', 'ai-awareness-day' ),
			'view_item'          => __( 'View AI Tool', 'ai-awareness-day' ),
			'all_items'          => __( 'All AI Tools', 'ai-awareness-day' ),
			'search_items'       => __( 'Search AI Tools', 'ai-awareness-day' ),
		),
		'public'             => true,
		'publicly_queryable' => true,
		'has_archive'        => 'ai-tools',
		'rewrite'            => array( 'slug' => 'ai-tools' ),
		'show_ui'            => true,
		'show_in_menu'       => true,
		'menu_icon'          => 'dashicons-laptop',
		'show_in_rest'       => true,
		'supports'           => array( 'title', 'excerpt', 'thumbnail' ),
	) );
}
add_action( 'init', 'aiad_register_ai_tool_post_type', 10 );

/* ──────────────────────────────────────────────
   2. Taxonomy: tool_category
   ────────────────────────────────────────────── */

function aiad_register_tool_category_taxonomy(): void {
	register_taxonomy( 'tool_category', 'ai_tool', array(
		'labels' => array(
			'name'          => __( 'Tool Categories', 'ai-awareness-day' ),
			'singular_name' => __( 'Tool Category', 'ai-awareness-day' ),
			'add_new_item'  => __( 'Add New Category', 'ai-awareness-day' ),
			'edit_item'     => __( 'Edit Category', 'ai-awareness-day' ),
		),
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'tool-category' ),
	) );
}
add_action( 'init', 'aiad_register_tool_category_taxonomy', 10 );

/**
 * Pre-seed default tool categories and all 18 tools on first load.
 * Runs once; uses an option flag to prevent re-seeding.
 */
function aiad_seed_tools(): void {
	if ( get_option( 'aiad_tools_seeded' ) ) {
		return;
	}

	// ── Categories ──────────────────────────────────────
	$categories = array(
		'content-creation'         => 'Content Creation',
		'visual-creative'          => 'Visual & Creative',
		'learning-platforms'       => 'Learning Platforms',
		'subject-specific'         => 'Subject-Specific',
		'interactive-demos'        => 'Interactive Demos',
		'professional-development' => 'Professional Development',
	);

	$term_ids = array();
	foreach ( $categories as $slug => $name ) {
		$existing = term_exists( $slug, 'tool_category' );
		if ( $existing ) {
			$term_ids[ $slug ] = is_array( $existing ) ? (int) $existing['term_id'] : (int) $existing;
		} else {
			$inserted = wp_insert_term( $name, 'tool_category', array( 'slug' => $slug ) );
			if ( ! is_wp_error( $inserted ) ) {
				$term_ids[ $slug ] = (int) $inserted['term_id'];
			}
		}
	}

	// ── Tools data ──────────────────────────────────────
	$tools = array(

		// Content Creation
		array(
			'title'    => 'Claude.ai',
			'category' => 'content-creation',
			'use_case' => 'Lesson planning, differentiation, feedback',
			'url'      => 'https://claude.ai',
			'features' => "Lesson planning assistance\nDifferentiation strategies\nStudent feedback generation\nCurriculum support\nDetailed explanations",
		),
		array(
			'title'    => 'ChatGPT',
			'category' => 'content-creation',
			'use_case' => 'Brainstorming, rubrics, simplifying texts',
			'url'      => 'https://chat.openai.com',
			'features' => "Brainstorming sessions\nRubric creation\nText simplification\nQuestion generation\nLesson idea support",
		),
		array(
			'title'    => 'Perplexity AI',
			'category' => 'content-creation',
			'use_case' => 'Research with citations, fact-checking',
			'url'      => 'https://www.perplexity.ai',
			'features' => "Research with citations\nFact-checking capabilities\nSource verification\nReal-time web search\nAcademic research support",
		),

		// Visual & Creative
		array(
			'title'    => 'Canva Education',
			'category' => 'visual-creative',
			'use_case' => 'AI-powered design for presentations',
			'url'      => 'https://www.canva.com/education/',
			'features' => "AI-powered design suggestions\nEducational templates\nPresentation creation\nClassroom materials\nCollaborative editing",
		),
		array(
			'title'    => 'Bing Create',
			'category' => 'visual-creative',
			'use_case' => 'Generate custom images for lessons',
			'url'      => 'https://www.bing.com/create',
			'features' => "AI image generation\nCustom lesson visuals\nEducational illustrations\nFree to use\nMicrosoft integration",
		),
		array(
			'title'    => 'Adobe Express',
			'category' => 'visual-creative',
			'use_case' => 'Quick graphics and animations',
			'url'      => 'https://www.adobe.com/express/',
			'features' => "Quick graphics creation\nAnimation tools\nTemplates library\nFree educator plan\nSocial media assets",
		),

		// Learning Platforms
		array(
			'title'    => 'Scratch + AI',
			'category' => 'learning-platforms',
			'use_case' => 'Block-based AI programming',
			'url'      => 'https://scratch.mit.edu',
			'features' => "Block-based programming\nAI integration\nStudent-friendly interface\nFree projects library\nClassroom sharing",
		),
		array(
			'title'    => 'Teachable Machine',
			'category' => 'learning-platforms',
			'use_case' => 'Train AI models without coding',
			'url'      => 'https://teachablemachine.withgoogle.com',
			'features' => "No coding required\nImage classification\nAudio recognition\nPose detection\nExportable models",
		),
		array(
			'title'    => 'Machine Learning for Kids',
			'category' => 'learning-platforms',
			'use_case' => 'Hands-on ML projects',
			'url'      => 'https://machinelearningforkids.co.uk',
			'features' => "Age-appropriate projects\nScratch integration\nHands-on activities\nFree classroom accounts\nReady-made worksheets",
		),

		// Subject-Specific
		array(
			'title'    => 'Wolfram Alpha',
			'category' => 'subject-specific',
			'use_case' => 'Mathematics and science calculations',
			'url'      => 'https://www.wolframalpha.com',
			'features' => "Mathematical calculations\nScience problem solving\nStep-by-step solutions\nGraphing tools\nData analysis",
		),
		array(
			'title'    => 'Grammarly',
			'category' => 'subject-specific',
			'use_case' => 'Writing assistance and feedback',
			'url'      => 'https://www.grammarly.com',
			'features' => "Writing assistance\nGrammar checking\nStyle suggestions\nTone detection\nPlagiarism detection",
		),
		array(
			'title'    => 'Khan Academy',
			'category' => 'subject-specific',
			'use_case' => 'AI-powered personalized learning',
			'url'      => 'https://www.khanacademy.org',
			'features' => "Personalized learning paths\nAI-powered recommendations\nAdaptive practice\nProgress tracking\nFree for all students",
		),

		// Interactive Demos
		array(
			'title'    => 'Quick Draw',
			'category' => 'interactive-demos',
			'use_case' => 'AI learns to recognize drawings',
			'url'      => 'https://quickdraw.withgoogle.com',
			'features' => "Drawing recognition\nReal-time AI learning\nEducational games\nNo account needed\nPattern recognition demo",
		),
		array(
			'title'    => 'Semantris',
			'category' => 'interactive-demos',
			'use_case' => 'Word association AI game',
			'url'      => 'https://research.google.com/semantris/',
			'features' => "Word association game\nAI-powered gameplay\nEducational fun\nLanguage understanding\nNo account needed",
		),
		array(
			'title'    => 'AutoDraw',
			'category' => 'interactive-demos',
			'use_case' => 'AI-assisted drawing tool',
			'url'      => 'https://www.autodraw.com',
			'features' => "AI-assisted drawing\nShape recognition\nCreative assistance\nFree to use\nNo account needed",
		),

		// Professional Development
		array(
			'title'    => 'Elements of AI',
			'category' => 'professional-development',
			'use_case' => 'Free AI fundamentals course',
			'url'      => 'https://www.elementsofai.com',
			'features' => "Free AI fundamentals course\nSelf-paced learning\nCertificate of completion\nNo technical background needed\nAvailable in many languages",
		),
		array(
			'title'    => 'AI4K12',
			'category' => 'professional-development',
			'use_case' => 'AI curriculum guidelines',
			'url'      => 'https://ai4k12.org',
			'features' => "AI curriculum guidelines\nGrade-level standards\nLearning progressions\nFree lesson plans\nTeacher resources",
		),
		array(
			'title'    => 'Google AI Education',
			'category' => 'professional-development',
			'use_case' => 'Ready-to-use lesson plans',
			'url'      => 'https://edu.google.com/intl/ALL_uk/for-educators/program-summaries/ai/',
			'features' => "Ready-to-use lesson plans\nTeacher training materials\nClassroom activities\nFree resources\nCross-curricular support",
		),
	);

	// ── Insert posts ─────────────────────────────────────
	foreach ( $tools as $i => $tool ) {
		$post_id = wp_insert_post( array(
			'post_title'  => $tool['title'],
			'post_status' => 'publish',
			'post_type'   => 'ai_tool',
			'menu_order'  => $i,
		) );

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		update_post_meta( $post_id, '_aiad_tool_url', esc_url_raw( $tool['url'] ) );
		update_post_meta( $post_id, '_aiad_tool_status', 'available' );
		update_post_meta( $post_id, '_aiad_tool_use_case', $tool['use_case'] );
		update_post_meta( $post_id, '_aiad_tool_features', $tool['features'] );

		$cat_slug = $tool['category'];
		if ( isset( $term_ids[ $cat_slug ] ) ) {
			wp_set_object_terms( $post_id, array( $term_ids[ $cat_slug ] ), 'tool_category' );
		}
	}

	update_option( 'aiad_tools_seeded', true );
}
add_action( 'init', 'aiad_seed_tools', 25 );

/**
 * Seed additional tools (v2) — new categories + 19 more tools.
 * Runs once; guarded by aiad_tools_seeded_v2.
 */
function aiad_seed_tools_v2(): void {
	if ( get_option( 'aiad_tools_seeded_v2' ) ) {
		return;
	}

	// ── New categories ───────────────────────────────────
	$new_categories = array(
		'productivity-organization' => 'Productivity & Organisation',
		'audio-video'               => 'Audio & Video',
		'image-design'              => 'Image & Design',
		'writing-communication'     => 'Writing & Communication',
		'research-reading'          => 'Research & Reading',
	);

	$term_ids = array();
	foreach ( $new_categories as $slug => $name ) {
		$existing = term_exists( $slug, 'tool_category' );
		if ( $existing ) {
			$term_ids[ $slug ] = is_array( $existing ) ? (int) $existing['term_id'] : (int) $existing;
		} else {
			$inserted = wp_insert_term( $name, 'tool_category', array( 'slug' => $slug ) );
			if ( ! is_wp_error( $inserted ) ) {
				$term_ids[ $slug ] = (int) $inserted['term_id'];
			}
		}
	}

	// Also resolve existing content-creation term for the extra 3 tools
	$existing_cc = term_exists( 'content-creation', 'tool_category' );
	if ( $existing_cc ) {
		$term_ids['content-creation'] = is_array( $existing_cc ) ? (int) $existing_cc['term_id'] : (int) $existing_cc;
	}

	// ── New tools ────────────────────────────────────────
	$tools = array(

		// Content Creation (3 more)
		array(
			'title'    => 'NotebookLM',
			'category' => 'content-creation',
			'use_case' => 'AI research notebook for lesson prep and notes',
			'url'      => 'https://notebooklm.google.com',
			'features' => "Upload documents and ask questions\nAI-generated summaries\nPodcast-style audio overviews\nSource-grounded answers\nFree with Google account",
		),
		array(
			'title'    => 'Goblin Tools',
			'category' => 'content-creation',
			'use_case' => 'Simple AI tools for breaking down tasks',
			'url'      => 'https://goblin.tools',
			'features' => "Task breakdown for neurodivergent users\nMagic To Do list\nJudgement Remover for tone\nFormaliser for writing\nFree to use",
		),
		array(
			'title'    => 'Gemini',
			'category' => 'content-creation',
			'use_case' => 'Google AI for lesson planning and content',
			'url'      => 'https://gemini.google.com',
			'features' => "Lesson planning assistance\nDocument and image understanding\nGoogle Workspace integration\nReal-time information access\nFree with Google account",
		),

		// Productivity & Organisation
		array(
			'title'    => 'Otter.ai',
			'category' => 'productivity-organization',
			'use_case' => 'AI meeting and lecture transcription',
			'url'      => 'https://otter.ai',
			'features' => "Real-time transcription\nMeeting summaries\nAction item detection\nSpeaker identification\nFree plan available",
		),
		array(
			'title'    => 'Fireflies.ai',
			'category' => 'productivity-organization',
			'use_case' => 'Auto-record and transcribe meetings',
			'url'      => 'https://fireflies.ai',
			'features' => "Automatic meeting recording\nAI-generated notes\nSearchable transcripts\nIntegrates with Zoom, Teams, Meet\nFree plan available",
		),
		array(
			'title'    => 'Notion AI',
			'category' => 'productivity-organization',
			'use_case' => 'AI-powered workspace for planning and writing',
			'url'      => 'https://www.notion.so',
			'features' => "AI writing and editing assistant\nAutomatic summaries\nAction item extraction\nFree-form note-taking\nTeam collaboration",
		),

		// Audio & Video
		array(
			'title'    => 'Descript',
			'category' => 'audio-video',
			'use_case' => 'Edit audio and video by editing text',
			'url'      => 'https://www.descript.com',
			'features' => "Text-based audio/video editing\nAI filler-word removal\nOverdub voice cloning\nScreen recording\nFree plan available",
		),
		array(
			'title'    => 'ElevenLabs',
			'category' => 'audio-video',
			'use_case' => 'Realistic AI text-to-speech voices',
			'url'      => 'https://elevenlabs.io',
			'features' => "High-quality voice synthesis\nMultiple languages and accents\nCustom voice cloning\nAudiobook generation\nFree plan available",
		),
		array(
			'title'    => 'CapCut',
			'category' => 'audio-video',
			'use_case' => 'AI video editing for classroom content',
			'url'      => 'https://www.capcut.com',
			'features' => "Auto captions and subtitles\nAI background removal\nTemplate-based editing\nText-to-video\nFree to use",
		),
		array(
			'title'    => 'Whisper',
			'category' => 'audio-video',
			'use_case' => 'Open-source speech recognition and transcription',
			'url'      => 'https://openai.com/research/whisper',
			'features' => "Multilingual transcription\nHigh accuracy speech recognition\nOpen source and free\nNoise-robust\nDeveloper-friendly API",
		),

		// Image & Design
		array(
			'title'    => 'Remove.bg',
			'category' => 'image-design',
			'use_case' => 'Instantly remove image backgrounds with AI',
			'url'      => 'https://www.remove.bg',
			'features' => "One-click background removal\nHigh-resolution downloads\nBulk processing\nAPI access\nFree preview downloads",
		),
		array(
			'title'    => 'Cleanup.pictures',
			'category' => 'image-design',
			'use_case' => 'Remove unwanted objects from photos',
			'url'      => 'https://cleanup.pictures',
			'features' => "AI object removal\nBrush to select area\nNo signup required\nFree to use\nHigh quality inpainting",
		),
		array(
			'title'    => 'DALL-E',
			'category' => 'image-design',
			'use_case' => 'Generate images from text descriptions',
			'url'      => 'https://openai.com/dall-e-3',
			'features' => "Text-to-image generation\nImage editing and variations\nHigh-resolution outputs\nAvailable in ChatGPT\nCreative classroom visuals",
		),

		// Writing & Communication
		array(
			'title'    => 'DeepL',
			'category' => 'writing-communication',
			'use_case' => 'AI-powered language translation',
			'url'      => 'https://www.deepl.com',
			'features' => "High-accuracy translations\n30+ languages supported\nDocument translation\nTone and formality control\nFree plan available",
		),
		array(
			'title'    => 'Hemingway Editor',
			'category' => 'writing-communication',
			'use_case' => 'Simplify and clarify written content',
			'url'      => 'https://hemingwayapp.com',
			'features' => "Readability grade scoring\nHighlights complex sentences\nPassive voice detection\nAdverb usage feedback\nFree online version",
		),
		array(
			'title'    => 'Suno',
			'category' => 'writing-communication',
			'use_case' => 'Generate original songs and music with AI',
			'url'      => 'https://suno.com',
			'features' => "Text-to-song generation\nCustom lyrics and style\nVarious music genres\nFree plan available\nEngaging classroom activity",
		),

		// Research & Reading
		array(
			'title'    => 'Elicit',
			'category' => 'research-reading',
			'use_case' => 'AI research assistant for academic papers',
			'url'      => 'https://elicit.com',
			'features' => "Summarise academic papers\nLiterature review automation\nExtract key data from studies\nFree plan available\nCitation-backed answers",
		),
		array(
			'title'    => 'SciSpace',
			'category' => 'research-reading',
			'use_case' => 'Understand and explain research papers',
			'url'      => 'https://typeset.io',
			'features' => "AI paper explanations\nAsk questions about any PDF\nLiterature search\nSimplified summaries\nFree plan available",
		),
	);

	// ── Insert posts ─────────────────────────────────────
	// Start menu_order after existing 18 tools
	$offset = 18;
	foreach ( $tools as $i => $tool ) {
		$post_id = wp_insert_post( array(
			'post_title'  => $tool['title'],
			'post_status' => 'publish',
			'post_type'   => 'ai_tool',
			'menu_order'  => $offset + $i,
		) );

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		update_post_meta( $post_id, '_aiad_tool_url', esc_url_raw( $tool['url'] ) );
		update_post_meta( $post_id, '_aiad_tool_status', 'available' );
		update_post_meta( $post_id, '_aiad_tool_use_case', $tool['use_case'] );
		update_post_meta( $post_id, '_aiad_tool_features', $tool['features'] );

		$cat_slug = $tool['category'];
		if ( isset( $term_ids[ $cat_slug ] ) ) {
			wp_set_object_terms( $post_id, array( $term_ids[ $cat_slug ] ), 'tool_category' );
		}
	}

	update_option( 'aiad_tools_seeded_v2', true );
}
add_action( 'init', 'aiad_seed_tools_v2', 26 );

/* ──────────────────────────────────────────────
   3. Meta Fields
   ────────────────────────────────────────────── */

function aiad_register_tool_meta(): void {
	$fields = array(
		'_aiad_tool_url'      => 'string',
		'_aiad_tool_status'   => 'string',
		'_aiad_tool_use_case' => 'string',
		'_aiad_tool_features' => 'string',
	);

	foreach ( $fields as $key => $type ) {
		register_post_meta( 'ai_tool', $key, array(
			'type'          => $type,
			'single'        => true,
			'default'       => '',
			'show_in_rest'  => true,
			'auth_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		) );
	}
}
add_action( 'init', 'aiad_register_tool_meta', 15 );

/* ──────────────────────────────────────────────
   4. Admin Meta Box
   ────────────────────────────────────────────── */

function aiad_add_tool_meta_box(): void {
	add_meta_box(
		'aiad_tool_details',
		__( 'Tool Details', 'ai-awareness-day' ),
		'aiad_render_tool_meta_box',
		'ai_tool',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'aiad_add_tool_meta_box' );

function aiad_render_tool_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'aiad_tool_meta_save', 'aiad_tool_meta_nonce' );

	$url      = get_post_meta( $post->ID, '_aiad_tool_url', true );
	$use_case = get_post_meta( $post->ID, '_aiad_tool_use_case', true );
	$features = get_post_meta( $post->ID, '_aiad_tool_features', true );
	?>
	<table class="form-table" style="margin-top:0">
		<tr>
			<th><label for="aiad_tool_url"><?php esc_html_e( 'Website URL', 'ai-awareness-day' ); ?></label></th>
			<td><input type="url" id="aiad_tool_url" name="aiad_tool_url" value="<?php echo esc_attr( $url ); ?>" class="large-text" placeholder="https://example.com" /></td>
		</tr>
		<tr>
			<th><label for="aiad_tool_use_case"><?php esc_html_e( 'Use Case', 'ai-awareness-day' ); ?></label></th>
			<td>
				<input type="text" id="aiad_tool_use_case" name="aiad_tool_use_case" value="<?php echo esc_attr( $use_case ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'e.g. Lesson planning, differentiation, feedback', 'ai-awareness-day' ); ?>" />
				<p class="description"><?php esc_html_e( 'Short description shown on the card.', 'ai-awareness-day' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="aiad_tool_features"><?php esc_html_e( 'Key Features', 'ai-awareness-day' ); ?></label></th>
			<td>
				<textarea id="aiad_tool_features" name="aiad_tool_features" class="large-text" rows="5" placeholder="<?php esc_attr_e( 'One feature per line', 'ai-awareness-day' ); ?>"><?php echo esc_textarea( $features ); ?></textarea>
				<p class="description"><?php esc_html_e( 'One feature per line. First 3 are shown on cards; the rest appear as "+N more".', 'ai-awareness-day' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

function aiad_save_tool_meta( int $post_id ): void {
	if (
		! isset( $_POST['aiad_tool_meta_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aiad_tool_meta_nonce'] ) ), 'aiad_tool_meta_save' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}

	if ( isset( $_POST['aiad_tool_url'] ) ) {
		update_post_meta( $post_id, '_aiad_tool_url', esc_url_raw( wp_unslash( $_POST['aiad_tool_url'] ) ) );
	}
	if ( isset( $_POST['aiad_tool_use_case'] ) ) {
		update_post_meta( $post_id, '_aiad_tool_use_case', sanitize_text_field( wp_unslash( $_POST['aiad_tool_use_case'] ) ) );
	}
	if ( isset( $_POST['aiad_tool_features'] ) ) {
		update_post_meta( $post_id, '_aiad_tool_features', sanitize_textarea_field( wp_unslash( $_POST['aiad_tool_features'] ) ) );
	}
}
add_action( 'save_post_ai_tool', 'aiad_save_tool_meta' );

/* ──────────────────────────────────────────────
   5. Rendering Helper
   ────────────────────────────────────────────── */

/**
 * Render a single tool card.
 *
 * @param WP_Post $tool The ai_tool post object.
 * @return string HTML markup.
 */
function aiad_render_tool_card( WP_Post $tool ): string {
	$url      = get_post_meta( $tool->ID, '_aiad_tool_url', true );
	$use_case = get_post_meta( $tool->ID, '_aiad_tool_use_case', true );
	$features_raw = get_post_meta( $tool->ID, '_aiad_tool_features', true );

	$features     = $features_raw ? array_filter( array_map( 'trim', explode( "\n", $features_raw ) ) ) : array();
	$features     = array_values( $features );
	$shown        = array_slice( $features, 0, 3 );

	$terms        = get_the_terms( $tool->ID, 'tool_category' );
	$category     = ( $terms && ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms[0]->name : '';
	$cat_slug     = ( $terms && ! is_wp_error( $terms ) && ! empty( $terms ) ) ? (string) $terms[0]->slug : '';

	$card_classes = array( 'tool-card' );
	if ( $cat_slug !== '' ) {
		$card_classes[] = 'tool-card--cat-' . sanitize_html_class( $cat_slug );
	}

	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', $card_classes ) ); ?>">
		<?php if ( $category ) : ?>
		<div class="tool-card__top">
			<span class="tool-card__category"><?php echo esc_html( $category ); ?></span>
		</div>
		<?php endif; ?>

		<h3 class="tool-card__title"><?php echo esc_html( get_the_title( $tool ) ); ?></h3>

		<?php if ( $use_case ) : ?>
			<p class="tool-card__use-case"><?php echo esc_html( $use_case ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $shown ) ) : ?>
			<ul class="tool-card__features">
				<?php foreach ( $shown as $feature ) : ?>
					<li><?php echo esc_html( $feature ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( $url ) : ?>
			<a href="<?php echo esc_url( $url ); ?>" class="tool-card__link" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Visit Website', 'ai-awareness-day' ); ?>
				<span aria-hidden="true">→</span>
			</a>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}
