<?php
/**
 * One-off resource seeding (runs on init; guarded by options).
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Seed tutor-time resource: BBC Ideas — How AI actually works (YouTube).
 * Runs once; creates a published resource with Activity Schema–compliant meta.
 */
function aiad_seed_bbc_how_ai_works_tutor_resource(): void {
    if ( get_option( 'aiad_bbc_how_ai_works_resource_seeded' ) === 'yes' ) {
        return;
    }

    $title = __( 'How AI actually works (BBC Ideas)', 'ai-awareness-day' );
    if ( aiad_get_post_by_title( $title, 'resource' ) ) {
        update_option( 'aiad_bbc_how_ai_works_resource_seeded', 'yes' );
        return;
    }

    $video_url = 'https://www.youtube.com/watch?v=E4bvQZRC6Bo';

    $post_id = wp_insert_post(
        array(
            'post_type'    => 'resource',
            'post_title'   => $title,
            'post_name'    => 'how-ai-actually-works-bbc-ideas',
            'post_excerpt' => __( 'A 15-minute tutor-time video activity: demystify how machine learning works and why “thinking” is a misleading metaphor.', 'ai-awareness-day' ),
            'post_content' => '<p>' . esc_html__( 'Use this BBC Ideas film in tutor time to build a shared, accurate mental model of machine learning: data, patterns, and prediction — without treating the system as a conscious “mind”.', 'ai-awareness-day' ) . '</p>',
            'post_status'  => 'publish',
            'post_author'  => 1,
        ),
        true
    );

    if ( ! $post_id || is_wp_error( $post_id ) ) {
        return;
    }

    wp_set_object_terms( $post_id, array( 'smart' ), 'resource_principle' );
    wp_set_object_terms( $post_id, array( '15-20-min-tutor-time' ), 'resource_duration' );
    wp_set_object_terms( $post_id, array( 'video' ), 'activity_type' );

    update_post_meta( $post_id, '_aiad_subtitle', __( 'Watch, pause, and discuss: what AI is really doing when it “guesses” well.', 'ai-awareness-day' ) );
    update_post_meta( $post_id, '_aiad_level', 'intermediate' );
    update_post_meta( $post_id, '_aiad_status', 'published' );
    update_post_meta( $post_id, '_aiad_key_stage', array( 'ks3', 'ks4', 'ks5' ) );
    update_post_meta( $post_id, '_aiad_preview_video_url', $video_url );

    update_post_meta(
        $post_id,
        '_aiad_preparation',
        array(
            __( 'Test playback and sound in the room; open the resource page or have the YouTube link ready.', 'ai-awareness-day' ),
            __( 'Optional: board space for two columns — “Helpful metaphor” vs “Misleading if taken literally”. The film uses a chatbot as a spaceship navigating a galaxy of information (≈0:46): a strong fit for the first column — it moves through existing data, rather than inventing the galaxy from nothing.', 'ai-awareness-day' ),
        )
    );

    update_post_meta(
        $post_id,
        '_aiad_learning_objectives',
        array(
            array( 'objective' => __( 'Understand that many modern AI systems learn statistical patterns from data rather than following a fixed, hand-written rule for every situation.', 'ai-awareness-day' ) ),
            array( 'objective' => __( 'Recognise why casual language like “the AI thinks” can mislead people about what is happening under the hood.', 'ai-awareness-day' ) ),
            array( 'objective' => __( 'Recognise that user tone and wording steer outputs toward different regions of trained data — without implying the model has feelings or a fixed personality.', 'ai-awareness-day' ) ),
            array( 'objective' => __( 'Apply one or two critical questions when encountering AI-generated text, images, or recommendations in daily life.', 'ai-awareness-day' ) ),
        )
    );

    update_post_meta(
        $post_id,
        '_aiad_instructions',
        array(
            array(
                'step'     => 1,
                'action'   => __( 'Frame the session: we are building a clearer picture of machine learning — not debating whether AI is “alive”.', 'ai-awareness-day' ),
                'duration' => __( '2 min', 'ai-awareness-day' ),
            ),
            array(
                'step'           => 2,
                'action'         => __( 'Play the film from the start. Pause after the early explanation of data and pattern-finding. Ask: “What is the system actually optimising for?”', 'ai-awareness-day' ),
                'duration'       => __( '5 min', 'ai-awareness-day' ),
                'student_action' => __( 'Turn to a partner: one sentence — “What surprised you?”', 'ai-awareness-day' ),
            ),
            array(
                'step'       => 3,
                'action'     => __( 'Resume and watch (including ≈0:46 and the tone section ≈02:08). Draw out the spaceship-in-a-galaxy metaphor: helpful because the system navigates existing information; misleading if we imagine it “creates” the territory. Invite students to notice looser metaphors too (e.g. “learning”, “understanding”) and what they mean in software.', 'ai-awareness-day' ),
                'duration'   => __( '5 min', 'ai-awareness-day' ),
                'teacher_tip' => __( 'Quick prompt (~02:08): “If the model has no personality, why can being polite still change the output?” Polite phrasing steers predictions toward regions of data the model saw during training — not because it has manners.', 'ai-awareness-day' ),
            ),
            array(
                'step'        => 4,
                'action'      => __( 'Plenary: agree on one takeaway (e.g. “Pattern from data, not magic”) and one habit (e.g. “Check the source and purpose of the output”). If time, ask who should decide what counts as “safe” when developers add guardrails (~03:52) — companies, regulators, users?', 'ai-awareness-day' ),
                'duration'    => __( '3 min', 'ai-awareness-day' ),
                'teacher_tip' => __( 'Sycophancy (~02:35): models can be tuned to agree or flatter. Ask how you might invite a more honest counter-view (the film suggests prompts such as “play devil’s advocate”). Link to headlines extension: anthropomorphism vs steering.', 'ai-awareness-day' ),
            ),
        )
    );

    update_post_meta(
        $post_id,
        '_aiad_key_definitions',
        array(
            array(
                'term'       => __( 'Machine learning', 'ai-awareness-day' ),
                'definition' => __( 'A family of methods where a model’s behaviour is shaped by examples (data) rather than only by fixed rules written line-by-line.', 'ai-awareness-day' ),
            ),
            array(
                'term'       => __( 'Training data', 'ai-awareness-day' ),
                'definition' => __( 'The examples used to build or tune a model. Quality and bias in this data strongly affect what the system can do.', 'ai-awareness-day' ),
            ),
            array(
                'term'       => __( 'Pattern', 'ai-awareness-day' ),
                'definition' => __( 'Regularities in data that a model can exploit to make predictions or generate plausible outputs.', 'ai-awareness-day' ),
            ),
            array(
                'term'       => __( 'Prediction', 'ai-awareness-day' ),
                'definition' => __( 'An output scored or chosen from learned associations; it can be impressively useful and still be wrong or unfair in edge cases.', 'ai-awareness-day' ),
            ),
            array(
                'term'       => __( 'Guardrails', 'ai-awareness-day' ),
                'definition' => __( 'Human-imposed rules or filters on what a model is allowed to say or do. They raise ethical questions: who defines “safe”, and whose values get baked in?', 'ai-awareness-day' ),
            ),
            array(
                'term'       => __( 'Sycophancy', 'ai-awareness-day' ),
                'definition' => __( 'When a system over-agrees or flatters to please the user. One response is to prompt for an opposing view — e.g. ask it to “play devil’s advocate”.', 'ai-awareness-day' ),
            ),
        )
    );

    update_post_meta(
        $post_id,
        '_aiad_discussion_question',
        __( 'When is it harmless to say an AI “understands” something — and when does that wording cause real mistakes? If the model has no personality, why can your tone in a prompt still change the answer?', 'ai-awareness-day' )
    );

    update_post_meta(
        $post_id,
        '_aiad_teacher_notes',
        __( "Film beats worth naming: spaceship / galaxy (≈0:46), tone steering output (≈02:08), sycophancy and devil’s advocate (≈02:35), guardrails and who decides safety (≈03:52).\n\nFurther prompts: What would you want to know about the data used? Who benefits if you trust the output without checking?\n\nWhat to verify: students can separate metaphor from mechanism — e.g. the system matches patterns from training, it does not “know” facts the way a person can after lived experience.\n\nIf time allows, connect to school context: chatbots, recommendation feeds, and image tools — same core ideas, different interfaces.", 'ai-awareness-day' )
    );

    update_post_meta(
        $post_id,
        '_aiad_differentiation',
        array(
            'support' => __( 'Offer sentence stems: “The data shapes…”, “The output is plausible when…”, “A risk of trusting it is…”.', 'ai-awareness-day' ),
            'stretch' => __( 'Sycophancy (~02:35): If the model is trying to please the user, how can you invite an honest alternative viewpoint? (Try “play devil’s advocate” or ask for limitations.) When might uncritical agreement hide errors or bias?', 'ai-awareness-day' ),
            'send'    => __( 'Prefer written pair/trio options; allow students to respond with a labelled diagram (data → model → output) instead of spoken answers.', 'ai-awareness-day' ),
        )
    );

    update_post_meta(
        $post_id,
        '_aiad_extensions',
        array(
            array(
                'activity' => __( 'Compare two headlines about the same AI story — identify where language implies human-like agency.', 'ai-awareness-day' ),
                'type'     => 'cross_curricular',
            ),
            array(
                'activity' => __( 'Debate briefly: who should decide what is “safe” for an AI to say — the company, regulators, teachers, or users? Tie to guardrails (~03:52).', 'ai-awareness-day' ),
                'type'     => 'next_lesson',
            ),
        )
    );

    update_post_meta(
        $post_id,
        '_aiad_resources',
        array(
            array(
                'name' => __( 'How AI actually works — BBC Ideas (YouTube)', 'ai-awareness-day' ),
                'type' => 'video',
                'url'  => $video_url,
            ),
        )
    );

    update_option( 'aiad_bbc_how_ai_works_resource_seeded', 'yes' );
}
add_action( 'init', 'aiad_seed_bbc_how_ai_works_tutor_resource', 27 );

/**
 * Seed Cyber Skills Live partner resources.
 * Adds new external resources once on existing sites without duplicating posts.
 */
function aiad_seed_cyberskillslive_partner_resources(): void {
    if ( get_option( 'aiad_cyberskillslive_partner_resources_seeded' ) === 'yes' ) {
        return;
    }

    $items = array(
        array(
            'title'      => 'Defend the Rhino with AI',
            'excerpt'    => __( 'An educational game where learners use data and machine learning to help rescue rhinos from poachers.', 'ai-awareness-day' ),
            'url'        => 'https://cyberskillslive.com/activity/defend-the-rhino/',
            'org'        => 'Cyber Skills Live',
            'theme'      => 'responsible',
            'duration'   => '30-45-min-after-school',
            'activities' => array( 'game' ),
        ),
        array(
            'title'      => 'The Unbelievably Creative AI Show',
            'excerpt'    => __( 'A one-hour live stage show that gets audiences thinking critically and creatively about AI, art, and human imagination.', 'ai-awareness-day' ),
            'url'        => 'https://cyberskillslive.com/unbelievably-creative/',
            'org'        => 'Cyber Skills Live',
            'theme'      => 'creative',
            'duration'   => '30-45-min-after-school',
            'activities' => array( 'presentation' ),
        ),
    );

    foreach ( $items as $item ) {
        $existing = aiad_get_post_by_title( $item['title'], 'featured_resource' );
        if ( $existing ) {
            continue;
        }

        $post_id = wp_insert_post(
            array(
                'post_type'    => 'featured_resource',
                'post_title'   => $item['title'],
                'post_excerpt' => $item['excerpt'],
                'post_status'  => 'publish',
                'post_author'  => 1,
            ),
            true
        );

        if ( ! $post_id || is_wp_error( $post_id ) ) {
            continue;
        }

        update_post_meta( $post_id, '_featured_resource_url', $item['url'] );
        update_post_meta( $post_id, '_featured_resource_org_name', $item['org'] );
        wp_set_object_terms( $post_id, array( $item['theme'] ), 'resource_principle' );
        wp_set_object_terms( $post_id, array( $item['duration'] ), 'resource_duration' );
        wp_set_object_terms( $post_id, $item['activities'], 'activity_type' );
    }

    update_option( 'aiad_cyberskillslive_partner_resources_seeded', 'yes' );
}
add_action( 'init', 'aiad_seed_cyberskillslive_partner_resources', 28 );

/**
 * Debate packs for the resources that existed when "Set up the debate" was
 * added: a motion and three points each way for each National Conversation
 * age pathway. An empty motion leaves the debate network's motion bank item
 * for the theme in place (aiad_resource_debate()), used where it already fits
 * the topic. Runs once, and never overwrites a pack an editor has written.
 */
function aiad_seed_resource_debate_packs(): void {
    if ( get_option( 'aiad_debate_packs_seeded_v1' ) === 'yes' ) {
        return;
    }

    $packs = array(
        // Responsible. The bank's Primary and Post-16 motions are this topic.
        'how-ai-uses-our-drinking-water-bbc' => array(
            'primary_for'       => "If we knew the cost, we could choose not to ask AI silly questions.\nFood labels tell us what is inside, so AI apps should tell us what they use.\nIt could help save water for the people and nature living near data centres.",
            'primary_against'   => "The numbers might be hard to understand and just worry people.\nOne question uses only a tiny bit, so knowing might not change anything.\nIt is the companies' job to fix the problem, not children's.",
            'secondary_motion'  => 'Should people use AI less because of its water and energy cost, or is it up to the companies to fix the problem?',
            'secondary_prompt'  => 'Challenge card: What if using AI for a task saves more energy than doing it another way?',
            'secondary_for'     => "Millions of unnecessary prompts add up to real water used by data centres.\nWaiting for companies to act could take years while local water stress gets worse.\nThinking before we prompt builds better habits and better questions.",
            'secondary_against' => "Most of the cost comes from training models and running data centres, which users cannot control.\nBlaming individuals lets companies avoid their responsibility.\nAI can also save resources, for example by managing energy grids and finding leaks.",
            'post16_for'        => "Without published figures, users, regulators and local communities cannot hold companies to account.\nLabels change behaviour: energy ratings pushed appliance makers towards efficiency.\nCommunities near data centres, such as the Thames Valley, deserve to know how much local water is used.",
            'post16_against'    => "A figure for every request is hard to calculate honestly and gives false precision.\nAnnual reporting for each data centre or company would be more useful and more reliable.\nA focus on single requests distracts from the larger cost of training models and building hardware.",
        ),
        // Safe. Kept to friendship for Primary.
        'ai-relationships-easier-than-the-real-thing' => array(
            'primary_motion'    => 'Can an AI chatbot be a real friend?',
            'primary_prompt'    => 'Sentence starter: I think an AI chatbot can / can\'t be a real friend because ___.',
            'primary_for'       => "It is always there to talk to when you feel lonely.\nIt is kind and never laughs at you.\nIt can help you practise what to say to people.",
            'primary_against'   => "It does not really care about you; it puts words together.\nIt cannot play with you, help you when you fall over or share things with you.\nA real friend sometimes disagrees with you, and that helps you grow.",
            'secondary_motion'  => 'Is it a problem if young people turn to AI chatbots instead of friends when they feel lonely?',
            'secondary_prompt'  => 'Challenge card: What if the chatbot helped someone feel brave enough to talk to a real person?',
            'secondary_for'     => "Chatbots are designed to keep you talking, which can crowd out real relationships.\nA chatbot that always agrees never teaches you to handle conflict or compromise.\nThe personal things you tell a chatbot are stored by a company.",
            'secondary_against' => "For someone isolated or anxious, some support is better than none.\nPractising conversations with AI can build confidence for real ones.\nJudging young people for using chatbots pushes them to use them in secret.",
            'post16_motion'     => 'This house believes AI companion apps should not be available to under-18s.',
            'post16_prompt'     => 'The tension: comfort vs dependency. Research: how does the Online Safety Act apply to AI chatbots that talk with young people?',
            'post16_for'        => "Companion apps are built for engagement, and young people are especially open to emotional dependency.\nAge checks already apply to other products that can cause harm; emotional manipulation is a comparable risk.\nUnder-18s are still developing the social skills these apps let them avoid.",
            'post16_against'    => "A ban is hard to enforce and pushes young people towards less safe, unregulated apps.\nSafety-by-design rules, such as time limits and signposting to help, protect young people without removing support.\nSome young people, including some neurodivergent students, find low-pressure conversation genuinely helpful.",
        ),
        // Smart.
        'how-ai-actually-works-bbc-ideas' => array(
            'primary_motion'    => 'Does AI really know things, or is it just very good at guessing?',
            'primary_prompt'    => 'Sentence starter: I think AI knows / guesses because ___.',
            'primary_for'       => "It answers lots of questions correctly, like a very clever person.\nIt has learned from more books and websites than anyone could ever read.\nIt can explain the same thing in different ways, like a teacher.",
            'primary_against'   => "It picks the words that usually come next, without checking they are true.\nIt can say wrong things in a very confident voice.\nIt has never seen, touched or done any of the things it talks about.",
            'secondary_motion'  => 'Should we stop saying that AI "thinks" or "understands"?',
            'secondary_prompt'  => 'Challenge card: What if "it thinks" is just a quick way of speaking, like saying a phone "wakes up"?',
            'secondary_for'     => "These words make people trust answers more than they should.\nCalling it thinking hides that answers come from patterns in training data.\nClearer words help us spot when AI is likely to be wrong.",
            'secondary_against' => "Everyday metaphors are normal, and most people know it is a machine.\nTechnical language would put many people off learning about AI at all.\nEven experts disagree about what understanding means, so dropping the word settles nothing.",
            'post16_motion'     => 'This house believes describing AI as "understanding" does more harm than good.',
            'post16_prompt'     => 'The tension: accessibility vs accuracy. Research: how do AI researchers disagree about whether large language models understand language?',
            'post16_for'        => "Human-like language inflates trust and leads people to rely on outputs they have not checked.\nIt shifts responsibility: \"the AI decided\" hides the people who designed and deployed it.\nSycophancy shows these systems are tuned for approval, not truth, and \"understanding\" masks that.",
            'post16_against'    => "Metaphor is how people learn any technical subject; strictly precise language would exclude most of the public.\nModels do build internal representations that are reasonable to call understanding in a limited sense.\nThe real harm comes from design and marketing choices, not from the word itself.",
        ),
    );

    aiad_seed_debate_packs_by_slug( $packs );
    update_option( 'aiad_debate_packs_seeded_v1', 'yes' );
}
add_action( 'init', 'aiad_seed_resource_debate_packs', 29 );

/**
 * Write debate packs onto resources found by slug. A resource that is missing
 * is skipped, and a pack an editor has already written is never overwritten.
 *
 * @param array<string, array<string, string>> $packs Slug => pack fields.
 */
function aiad_seed_debate_packs_by_slug( array $packs ): void {
    foreach ( $packs as $slug => $pack ) {
        $post = get_page_by_path( $slug, OBJECT, 'resource' );
        if ( ! $post ) {
            continue;
        }
        $existing = get_post_meta( $post->ID, '_aiad_debate_pack', true );
        if ( is_array( $existing ) && array_filter( $existing ) ) {
            continue;
        }
        $full = array();
        foreach ( array( 'primary', 'secondary', 'post16' ) as $age ) {
            foreach ( array( 'motion', 'prompt', 'for', 'against' ) as $part ) {
                $full[ $age . '_' . $part ] = $pack[ $age . '_' . $part ] ?? '';
            }
        }
        update_post_meta( $post->ID, '_aiad_debate_pack', $full );
    }
}


/**
 * The lesson slides and their teacher steps, kept in step with SlideForge.
 *
 * The six lessons with slides (five starters and the Smart assembly) are built
 * in SlideForge (AiAd27-Classic/ in that repository), which exports a PDF of
 * each and assets/lessons/2027/lessons.json: the Instructions and Preparation
 * for each lesson, with every step's "Slide n" numbered against the PDF's
 * pages. Both are written from one source, so the steps on the page and the
 * slides on the board cannot drift apart again.
 *
 * The file also carries each lesson's debate pack, which its slides use too.
 *
 * Applied when the file changes, not once: a new export deployed with the
 * theme updates the lessons on the next request. That is the point of a single
 * source, and it means a change made only in the editor to these fields is
 * replaced by the next export — make it in SlideForge instead. The PDF also
 * replaces each lesson's PowerPoint download.
 */
function aiad_sync_lesson_decks(): void {
    $file = get_template_directory() . '/assets/lessons/2027/lessons.json';
    if ( ! is_readable( $file ) ) {
        return;
    }
    $hash = md5_file( $file );
    if ( get_option( 'aiad_lesson_decks_synced' ) === $hash ) {
        return;
    }
    $lessons = json_decode( (string) file_get_contents( $file ), true );
    if ( ! is_array( $lessons ) ) {
        return;
    }

    $base = get_template_directory_uri() . '/assets/lessons/2027/';
    foreach ( $lessons as $lesson ) {
        $post = get_page_by_path( (string) ( $lesson['wp'] ?? '' ), OBJECT, 'resource' );
        if ( ! $post || empty( $lesson['instructions'] ) ) {
            continue;
        }
        $steps = array();
        foreach ( $lesson['instructions'] as $i => $step ) {
            $steps[] = array(
                'step'           => $i + 1,
                'action'         => sanitize_textarea_field( (string) ( $step['action'] ?? '' ) ),
                'duration'       => sanitize_text_field( (string) ( $step['duration'] ?? '' ) ),
                'resource_ref'   => sanitize_text_field( (string) ( $step['resource_ref'] ?? '' ) ),
                'student_action' => sanitize_text_field( (string) ( $step['student_action'] ?? '' ) ),
                'teacher_tip'    => sanitize_textarea_field( (string) ( $step['teacher_tip'] ?? '' ) ),
                'optional'       => ! empty( $step['optional'] ),
            );
        }
        update_post_meta( $post->ID, '_aiad_instructions', $steps );
        if ( ! empty( $lesson['preparation'] ) && is_array( $lesson['preparation'] ) ) {
            update_post_meta( $post->ID, '_aiad_preparation', array_map( 'sanitize_text_field', $lesson['preparation'] ) );
        }
        /* The debate on the slides and in "Set up the debate" on the page,
           from one source (AiAd27-Classic/debates.js in SlideForge). */
        if ( ! empty( $lesson['debate_pack'] ) && is_array( $lesson['debate_pack'] ) ) {
            $pack = array();
            foreach ( array( 'primary', 'secondary', 'post16' ) as $age ) {
                foreach ( array( 'motion', 'prompt', 'for', 'against' ) as $part ) {
                    $pack[ $age . '_' . $part ] = sanitize_textarea_field( (string) ( $lesson['debate_pack'][ $age . '_' . $part ] ?? '' ) );
                }
            }
            update_post_meta( $post->ID, '_aiad_debate_pack', $pack );
        }
        if ( ! empty( $lesson['pdf'] ) ) {
            update_post_meta( $post->ID, '_aiad_download_url', esc_url_raw( $base . rawurlencode( (string) $lesson['pdf'] ) ) );
        }
    }

    update_option( 'aiad_lesson_decks_synced', $hash );
}
add_action( 'init', 'aiad_sync_lesson_decks', 31 );
