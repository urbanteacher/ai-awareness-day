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
 * Debate packs for the six lesson starters and the assembly published before
 * the debate section existed. Where two lessons share a topic (pattern
 * prediction; AI's environmental cost) each takes a different motion, so a
 * class that does both does not debate the same thing twice. The motion bank
 * item is kept, by leaving the motion empty, only where it is a clear for or
 * against; most Primary bank items are open questions.
 */
function aiad_seed_resource_debate_packs_v2(): void {
    if ( get_option( 'aiad_debate_packs_seeded_v2' ) === 'yes' ) {
        return;
    }

    $packs = array(
        // Smart, assembly. Angle: checking what AI tells you.
        'ai-is-already-here' => array(
            'primary_motion'    => 'Should you check everything AI tells you?',
            'primary_prompt'    => 'Sentence starter: I think you should / don\'t need to check because ___.',
            'primary_for'       => "AI can make things up and still sound very sure.\nChecking with a book or a trusted adult helps you find the truth.\nIf you share something wrong, other people might believe it too.",
            'primary_against'   => "Checking everything would take a very long time.\nFor fun things, like a silly story, it does not matter if it is wrong.\nAI gets lots of simple things right.",
            'secondary_motion'  => 'AI chatbots should have to warn you every time an answer might be made up.',
            'secondary_prompt'  => 'Challenge card: What if the warnings appeared so often that everyone started ignoring them?',
            'secondary_for'     => "Hallucinations happen regularly, and fluent answers make them hard to spot.\nA warning reminds people to verify before they trust or share.\nCompanies already warn about other risks, such as age limits and gambling.",
            'secondary_against' => "The system often cannot tell when it is wrong, so the warnings would be guesses too.\nConstant warnings cause warning fatigue, and people stop reading them.\nLearning to verify is a skill we need anyway, warning or not.",
            // Post-16 keeps the bank motion: guide students rather than give answers.
            'post16_for'        => "Learning comes from working things out; ready-made answers skip the thinking that builds understanding.\nGuiding tools reduce the risk of students copying a confident hallucination into their work.\nSchools have a duty to teach verification, and answer machines undermine it.",
            'post16_against'    => "Students will use answer tools at home anyway, so schools should teach them to use them well.\nSometimes a clear worked answer is the fastest way to learn, as with a model essay.\nA rule for every tool is hard to enforce and may hold back students who use AI responsibly.",
        ),
        // Creative.
        'ai-as-your-creative-partner' => array(
            'primary_motion'    => 'If AI helps you make a picture, is it still your picture?',
            'primary_prompt'    => 'Sentence starter: I think it is / isn\'t still mine because ___.',
            'primary_for'       => "You had the idea and told the AI what to make.\nYou chose which picture to keep and what to change.\nArtists have always used tools, like paintbrushes and cameras.",
            'primary_against'   => "The AI did the drawing, not you.\nThe AI learned from other people's pictures.\nIf you only typed a few words, you did not do much of the work.",
            'secondary_motion'  => 'Using AI makes people more creative, not less.',
            'secondary_prompt'  => 'Challenge card: What if AI made it so easy that nobody bothered to learn to draw, write or play an instrument?',
            'secondary_for'     => "AI can get you past a blank page and spark ideas you would not have had.\nPeople without expensive training or equipment can now make music, films and art.\nThe best results come from human ideas plus AI help, with the human still in charge.",
            'secondary_against' => "AI recombines existing patterns, so its suggestions push everyone towards similar work.\nSkills such as drawing and writing come from practice that AI lets people skip.\nWhen AI does the hard part, it is harder to put your own voice and experience into the work.",
            // Post-16 keeps the bank motion: AI work and creative prizes.
            'post16_for'        => "Prizes reward human skill, effort and vision, and a prompt is not the same achievement.\nAI models are trained on artists' work, often without permission or payment.\nIf AI work can win, human artists lose the opportunities that build careers.",
            'post16_against'    => "Every new tool, from photography to digital art, was once called cheating.\nWhere a person directs, selects and edits, the creative choices are still theirs.\nA ban is unworkable, because almost all digital work now involves some AI assistance.",
        ),
        // Smart. Angle: whether understanding matters, and AI advice.
        'how-does-ai-actually-think' => array(
            'primary_motion'    => 'Would you trust an AI helper more than a book?',
            'primary_prompt'    => 'Sentence starter: I would trust ___ more because ___.',
            'primary_for'       => "AI answers quickly, and you can ask it anything.\nIt can explain things in a way that suits you.\nBooks can be old and out of date.",
            'primary_against'   => "AI can make up answers that sound true.\nBooks are checked by people before they are printed.\nAI does not really understand what it is saying.",
            'secondary_motion'  => 'It does not matter whether AI understands, as long as its answers are useful.',
            'secondary_prompt'  => 'Challenge card: What if someone followed an AI\'s health advice and it was wrong?',
            'secondary_for'     => "We use calculators and maps without them understanding anything.\nWhat matters is whether an answer is correct, and we can check that.\nAI already helps people learn, write and solve problems every day.",
            'secondary_against' => "Without understanding, AI cannot tell when its own answer is false.\nHallucinations in health, legal or safety advice can cause real harm.\nPeople trust AI more when they think it understands, so the difference changes how we use it.",
            'post16_motion'     => 'This house would ban AI chatbots from giving medical or legal advice.',
            'post16_prompt'     => 'The tension: access vs accuracy. Research: what do UK health and legal regulators say about AI tools giving advice to the public?',
            'post16_for'        => "Pattern prediction is not professional judgement, and confident errors here can do serious harm.\nDoctors and lawyers are accountable and regulated; a chatbot is neither.\nVulnerable people are the most likely to rely on free advice without checking it.",
            'post16_against'    => "Many people cannot afford or quickly reach a doctor or lawyer, and general information helps them.\nA ban would push people towards worse sources, such as anonymous forums.\nClear signposting and safety rules would reduce harm without removing a useful service.",
        ),
        // Responsible. Angle: who is responsible, and whether it is worth it.
        'the-hidden-costs-of-ai' => array(
            'primary_motion'    => 'Should we use AI less to help look after the planet?',
            'primary_prompt'    => 'Sentence starter: I think we should / shouldn\'t use AI less because ___.',
            'primary_for'       => "AI uses electricity and water every time we use it.\nSmall changes by lots of people can add up.\nWe can still do many things without AI, like thinking for ourselves.",
            'primary_against'   => "AI can help the planet too, like spotting leaks or saving energy.\nThe big companies use the most, so they should change first.\nAI helps people learn and do useful things.",
            'secondary_motion'  => 'Tech companies, not users, should be responsible for cutting AI\'s environmental impact.',
            'secondary_prompt'  => 'Challenge card: What if companies only change when their customers demand it?',
            'secondary_for'     => "Companies design the models and run the data centres, so they control most of the impact.\nUsers cannot see how much energy or water a request uses, so they cannot make informed choices.\nCompanies have the money and expertise to switch to renewable energy and better cooling.",
            'secondary_against' => "Companies respond to demand, so users' choices shape what they build.\nGovernments set the rules on energy and water, so responsibility is shared.\nEvery user can choose when AI is really needed.",
            'post16_motion'     => 'This house believes the benefits of AI are worth its environmental cost.',
            'post16_prompt'     => 'The tension: progress vs sustainability. Research: how is AI being used to cut emissions in energy, transport or farming?',
            'post16_for'        => "AI is helping to design better batteries, manage power grids and model the climate.\nEfficiency improves quickly: newer models and chips do more with less energy.\nThe cost is real but small compared with sectors such as transport and heating.",
            'post16_against'    => "Data centre demand for energy and water is growing fast and competing with local communities.\nMuch AI use is trivial, so the cost is not being spent on climate solutions.\nWithout transparent reporting, claims that the benefits outweigh the costs cannot be checked.",
        ),
        // Safe, sensitive. Primary stays with labelling; no intimate-image content below Post-16
        // beyond the lesson's own point that it is already a crime.
        'whos-really-behind-the-screen' => array(
            'primary_motion'    => 'Should apps have to put a label on pictures and videos made by AI?',
            'primary_prompt'    => 'Sentence starter: I think they should / shouldn\'t because ___.',
            'primary_for'       => "It would help us tell what is real and what is made up.\nPeople would be less likely to be tricked.\nIt is fair to know how something was made.",
            'primary_against'   => "People who want to trick others could remove the label.\nSome AI pictures are just for fun and do no harm.\nWe should learn to check things ourselves anyway.",
            'secondary_motion'  => 'Making a deepfake of someone without their permission should be against the law, even as a joke.',
            'secondary_prompt'  => 'Challenge card: What if the deepfake was of a famous politician and made a serious point?',
            'secondary_for'     => "A joke deepfake can still humiliate someone and spread far beyond the people it was meant for.\nOnce shared, it is almost impossible to delete, and the harm can last.\nA clear law would show that a person's face and voice belong to them.",
            'secondary_against' => "Satire of public figures is an important part of free speech.\nThe most harmful uses, such as intimate images and fraud, are already crimes.\nEducation and fast reporting tools may protect people better than a law that is hard to enforce.",
            'post16_motion'     => 'This house would make social media platforms legally responsible for deepfakes shared on their sites.',
            'post16_prompt'     => 'The tension: free expression vs protection. Research: what does the Online Safety Act require platforms to do about illegal content?',
            'post16_for'        => "Platforms profit from content spreading, so they should carry responsibility for the harm it causes.\nOnly platforms can act at the speed and scale needed to stop a deepfake spreading.\nLegal duties give victims a route to justice that individual reporting does not.",
            'post16_against'    => "Detection is imperfect, so platforms would over-remove legitimate content, including satire.\nResponsibility should rest with the person who makes and shares a deepfake.\nHeavy liability favours big companies that can afford moderation and squeezes out smaller platforms.",
        ),
        // Future.
        'your-ai-ready-future' => array(
            'primary_motion'    => 'Should AI and robots do the boring jobs so people don\'t have to?',
            'primary_prompt'    => 'Sentence starter: I think they should / shouldn\'t because ___.',
            'primary_for'       => "People would have more time for fun, creative and caring jobs.\nRobots do not get tired or bored.\nSome boring jobs are also dangerous, so it would keep people safe.",
            'primary_against'   => "Some people like those jobs and need them to earn money.\nA job that seems boring to you might matter a lot to someone else.\nIf AI does everything, people might forget how to do important things.",
            'secondary_motion'  => 'AI will create more good jobs than it takes away.',
            'secondary_prompt'  => 'Challenge card: What if the new jobs need skills that the people who lost their jobs do not have?',
            'secondary_for'     => "The World Economic Forum expects about 170 million new roles by 2030, against 92 million displaced.\nMost jobs will be transformed rather than disappear, with AI taking over routine tasks.\nNew careers are already appearing, from AI ethics to data science.",
            'secondary_against' => "New jobs may appear in different places from the jobs that are lost.\nNot every new role is a good one; some will be low-paid work checking AI output.\nThe speed of change could leave many people behind before they can retrain.",
            'post16_motion'     => 'This house believes schools should prioritise human skills over technical AI skills.',
            'post16_prompt'     => 'The tension: employability vs adaptability. Research: which skills do employers say they will need most by 2030?',
            'post16_for'        => "Empathy, judgement and creativity are the skills AI cannot replicate, so they keep their value.\nTechnical tools change every year, but human skills last a whole career.\nEmployers consistently rank skills such as communication and problem-solving among the most important.",
            'post16_against'    => "Without technical AI literacy, young people cannot shape or question the tools they use.\nMany of the best-paid new roles need data and AI skills, and school is where access is fairest.\nIt is a false choice: working well alongside AI is itself a human skill.",
        ),
    );

    aiad_seed_debate_packs_by_slug( $packs );
    update_option( 'aiad_debate_packs_seeded_v2', 'yes' );
}
add_action( 'init', 'aiad_seed_resource_debate_packs_v2', 30 );

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
            );
        }
        update_post_meta( $post->ID, '_aiad_instructions', $steps );
        if ( ! empty( $lesson['preparation'] ) && is_array( $lesson['preparation'] ) ) {
            update_post_meta( $post->ID, '_aiad_preparation', array_map( 'sanitize_text_field', $lesson['preparation'] ) );
        }
        if ( ! empty( $lesson['pdf'] ) ) {
            update_post_meta( $post->ID, '_aiad_download_url', esc_url_raw( $base . rawurlencode( (string) $lesson['pdf'] ) ) );
        }
    }

    update_option( 'aiad_lesson_decks_synced', $hash );
}
add_action( 'init', 'aiad_sync_lesson_decks', 31 );
