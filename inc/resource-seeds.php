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
