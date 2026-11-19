<?php
/**
 * The AI Literacy Periodic Table: /ai-literacy-periodic-table/, for teachers and students.
 *
 * 105 ideas everyone needs to understand AI, in six families: how AI works, then the campaign's five strands (Safe,
 * Smart, Creative, Responsible, Future). Each element has a plain-English definition and a "Talk about it" question
 * for the classroom.
 *
 * It is an ordinary page of blocks (patterns/ai-literacy-periodic-table.php), created the first time the theme loads
 * (aiad_maybe_convert_theme_pages() in inc/editable-pages.php), so its introduction and classroom ideas are edited on
 * the page. The table itself is one block, aiad/literacy-table, drawn here from aiad_literacy_elements(), so its 105
 * elements stay in one place: the tiles, the detail panel and the list of every definition below them all come from
 * that list. Without JavaScript the tiles are links to their definitions; assets/js/literacy-table.js turns them into
 * the interactive table (detail panel, family filter, search, random element, print).
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The table's address.
 */
function aiad_literacy_table_url(): string {
	return home_url( '/ai-literacy-periodic-table/' );
}

/**
 * Whether this request is the table's page.
 */
function aiad_is_literacy_table_page(): bool {
	return 'ai-literacy-periodic-table' === aiad_current_editable_page();
}

/**
 * The six families, in the order the campaign uses: key => name and what it covers.
 *
 * @return array<string, array{name:string, about:string}>
 */
function aiad_literacy_families(): array {
	return array(
		'foundations' => array(
			'name'  => __( 'How AI works', 'ai-awareness-day' ),
			'about' => __( 'The ideas everything else is built on.', 'ai-awareness-day' ),
		),
		'safe'        => array(
			'name'  => __( 'Safe', 'ai-awareness-day' ),
			'about' => __( 'Privacy, security, trust and protection.', 'ai-awareness-day' ),
		),
		'smart'       => array(
			'name'  => __( 'Smart', 'ai-awareness-day' ),
			'about' => __( 'Learning, thinking critically and checking what AI tells you.', 'ai-awareness-day' ),
		),
		'creative'    => array(
			'name'  => __( 'Creative', 'ai-awareness-day' ),
			'about' => __( 'Making, authorship, originality and ownership.', 'ai-awareness-day' ),
		),
		'responsible' => array(
			'name'  => __( 'Responsible', 'ai-awareness-day' ),
			'about' => __( 'Fairness, honesty, accountability and impact.', 'ai-awareness-day' ),
		),
		'future'      => array(
			'name'  => __( 'Future', 'ai-awareness-day' ),
			'about' => __( 'Jobs, skills, society and young people\'s voice.', 'ai-awareness-day' ),
		),
	);
}

/**
 * Every element: symbol, name, family, row and column on the 18-column table, definition, and a question to talk
 * about. Rows 1 to 7 are the main table (rows 1 to 3 have a gap in the middle for the detail panel, as a periodic
 * table does); row 9 is the separate row below it. Elements are numbered in reading order, so the numbers follow from
 * where they sit.
 *
 * @return array<int, array{0:string, 1:string, 2:string, 3:int, 4:int, 5:string, 6:string}>
 */
function aiad_literacy_raw_elements(): array {
	return array(
		// How AI works: the left-hand block, and the top right corner.
		array( 'Ai', __( 'Artificial Intelligence', 'ai-awareness-day' ), 'foundations', 1, 1, __( 'Computer systems that do tasks we usually link with human thinking, such as recognising speech, spotting patterns or answering questions.', 'ai-awareness-day' ), __( 'Name three things you used today that might have AI inside. How could you tell?', 'ai-awareness-day' ) ),
		array( 'Hl', __( 'Humans in the Loop', 'ai-awareness-day' ), 'foundations', 1, 18, __( 'People staying in charge of AI: checking what it produces, and making the decisions that matter.', 'ai-awareness-day' ), __( 'Where should a person always have the final say, whatever the AI suggests?', 'ai-awareness-day' ) ),
		array( 'Al', __( 'Algorithm', 'ai-awareness-day' ), 'foundations', 2, 1, __( 'A set of step-by-step instructions a computer follows to solve a problem or make a decision.', 'ai-awareness-day' ), __( 'Write an algorithm for making a cup of tea. What would a computer get wrong if one step were missing?', 'ai-awareness-day' ) ),
		array( 'Da', __( 'Data', 'ai-awareness-day' ), 'foundations', 2, 2, __( 'Information such as words, numbers, pictures, sounds and clicks. AI learns from data, so the data shapes what it can and can\'t do.', 'ai-awareness-day' ), __( 'What data might a music or video app collect about you in a single day?', 'ai-awareness-day' ) ),
		array( 'Ml', __( 'Machine Learning', 'ai-awareness-day' ), 'foundations', 3, 1, __( 'A way of building AI where a computer learns patterns from lots of examples instead of being given every rule.', 'ai-awareness-day' ), __( 'How would you teach a computer to tell cats from dogs without describing either?', 'ai-awareness-day' ) ),
		array( 'Td', __( 'Training Data', 'ai-awareness-day' ), 'foundations', 3, 2, __( 'The examples an AI learns from. If the examples are limited, out of date or unfair, the AI will be too.', 'ai-awareness-day' ), __( 'What might an AI get wrong if it only learned from photos taken in one country?', 'ai-awareness-day' ) ),
		array( 'Nn', __( 'Neural Network', 'ai-awareness-day' ), 'foundations', 4, 1, __( 'A kind of model made of layers of connected nodes, loosely inspired by the brain, that learns by strengthening some connections and weakening others.', 'ai-awareness-day' ), __( 'In what ways is a neural network like a brain, and in what ways is it nothing like one?', 'ai-awareness-day' ) ),
		array( 'Md', __( 'Model', 'ai-awareness-day' ), 'foundations', 4, 2, __( 'What you get after training: the part of an AI system that turns an input, like a question, into an output, like an answer.', 'ai-awareness-day' ), __( 'Two models trained on different data are asked the same question. Should they give the same answer?', 'ai-awareness-day' ) ),
		array( 'Pd', __( 'Prediction', 'ai-awareness-day' ), 'foundations', 5, 1, __( 'AI\'s best guess at what comes next or what something is, based on patterns. It is a guess, not a fact.', 'ai-awareness-day' ), __( 'Your phone predicts your next word. When does it get it wrong, and why?', 'ai-awareness-day' ) ),
		array( 'Pt', __( 'Pattern', 'ai-awareness-day' ), 'foundations', 5, 2, __( 'Something that repeats in data. Finding patterns is what most AI does best, but a pattern can be true for most people and wrong for you.', 'ai-awareness-day' ), __( 'Think of a pattern that is true for most people your age but not for you.', 'ai-awareness-day' ) ),
		array( 'Lm', __( 'Large Language Model', 'ai-awareness-day' ), 'foundations', 6, 1, __( 'An AI trained on huge amounts of text to predict the next word, which lets it write, summarise, translate and chat.', 'ai-awareness-day' ), __( 'If a model only predicts the next word, can it understand what it writes? Argue both sides.', 'ai-awareness-day' ) ),
		array( 'Ch', __( 'Chatbot', 'ai-awareness-day' ), 'foundations', 6, 2, __( 'A program you talk to by typing or speaking. Many chatbots today are built on large language models.', 'ai-awareness-day' ), __( 'How is talking to a chatbot different from talking to a friend or a teacher?', 'ai-awareness-day' ) ),
		array( 'Ge', __( 'Generative AI', 'ai-awareness-day' ), 'foundations', 7, 1, __( 'AI that makes new text, images, music, video or code when you ask it to.', 'ai-awareness-day' ), __( 'What have you seen this week that might have been made by generative AI?', 'ai-awareness-day' ) ),
		array( 'Rs', __( 'Recommendation System', 'ai-awareness-day' ), 'foundations', 7, 2, __( 'AI that chooses what you see next, such as videos, posts, songs or products, based on what you and people like you did before.', 'ai-awareness-day' ), __( 'Why might your feed look completely different from your friend\'s?', 'ai-awareness-day' ) ),

		// Smart: the centre left block.
		array( 'Pm', __( 'Prompt', 'ai-awareness-day' ), 'smart', 4, 3, __( 'The question or instruction you give an AI. Clear prompts that say what you want, and why, get better answers.', 'ai-awareness-day' ), __( 'Turn "help with my homework" into a clear prompt. What did you add?', 'ai-awareness-day' ) ),
		array( 'Cx', __( 'Context', 'ai-awareness-day' ), 'smart', 4, 4, __( 'The background you give an AI so it knows what you need: who it is for, what it is for and what level to pitch it at.', 'ai-awareness-day' ), __( 'Ask for an explanation of photosynthesis, then ask again for a Year 7 class. What changed?', 'ai-awareness-day' ) ),
		array( 'It', __( 'Iteration', 'ai-awareness-day' ), 'smart', 4, 5, __( 'Improving an answer step by step, by asking follow-up questions and saying what to change.', 'ai-awareness-day' ), __( 'Get an answer, then improve it twice with follow-ups. Was the third version better?', 'ai-awareness-day' ) ),
		array( 'Ct', __( 'Critical Thinking', 'ai-awareness-day' ), 'smart', 4, 6, __( 'Questioning information before you accept it: who made it, what supports it, and what might be missing.', 'ai-awareness-day' ), __( 'What three questions should you ask about any answer an AI gives you?', 'ai-awareness-day' ) ),
		array( 'Ev', __( 'Evidence', 'ai-awareness-day' ), 'smart', 4, 7, __( 'Facts and examples that back up a claim. Ask the AI, and yourself, what evidence supports an answer.', 'ai-awareness-day' ), __( 'Pick a claim an AI made. What evidence would convince you it is true?', 'ai-awareness-day' ) ),
		array( 'Ha', __( 'Hallucination', 'ai-awareness-day' ), 'smart', 5, 3, __( 'When AI states something false or made up as if it were true, such as a fake quote, statistic or source.', 'ai-awareness-day' ), __( 'Ask an AI for three sources on a topic. Do they all exist, and do they say what it claims?', 'ai-awareness-day' ) ),
		array( 'Fc', __( 'Fact-Checking', 'ai-awareness-day' ), 'smart', 5, 4, __( 'Checking a claim against reliable sources before you trust it or share it.', 'ai-awareness-day' ), __( 'Choose one fact from an AI answer and find two sources that confirm or contradict it.', 'ai-awareness-day' ) ),
		array( 'Sc', __( 'Source', 'ai-awareness-day' ), 'smart', 5, 5, __( 'Where information comes from. A good answer can be traced back to someone you have reason to trust.', 'ai-awareness-day' ), __( 'Which sources would you trust for health advice, and which would you not? Why?', 'ai-awareness-day' ) ),
		array( 'Lr', __( 'Lateral Reading', 'ai-awareness-day' ), 'smart', 5, 6, __( 'Checking what other trusted sources say about a claim, instead of judging it only by how it looks.', 'ai-awareness-day' ), __( 'Take a claim that is going viral and spend two minutes reading laterally. What did you find?', 'ai-awareness-day' ) ),
		array( 'Cf', __( 'Confident Tone', 'ai-awareness-day' ), 'smart', 5, 7, __( 'AI usually sounds just as sure when it is wrong as when it is right, so the tone of an answer is not evidence.', 'ai-awareness-day' ), __( 'Have you ever believed something because it sounded certain? What happened?', 'ai-awareness-day' ) ),
		array( 'Ay', __( 'Accuracy', 'ai-awareness-day' ), 'smart', 6, 3, __( 'How correct an answer is. AI is often less accurate on recent events, local facts, maths and niche topics.', 'ai-awareness-day' ), __( 'Which kinds of question would you double-check every time, and which would you not?', 'ai-awareness-day' ) ),
		array( 'Kc', __( 'Knowledge Cutoff', 'ai-awareness-day' ), 'smart', 6, 4, __( 'The date an AI\'s training data stops. Unless it can search the web, it may not know about recent events.', 'ai-awareness-day' ), __( 'Ask an AI about something that happened last week. How does it respond?', 'ai-awareness-day' ) ),
		array( 'Sy', __( 'Sycophancy', 'ai-awareness-day' ), 'smart', 6, 5, __( 'When an AI tells you what you want to hear, agreeing with you or praising your work too easily.', 'ai-awareness-day' ), __( 'Insist on a wrong answer to an AI. Does it stand firm or give in?', 'ai-awareness-day' ) ),
		array( 'Re', __( 'Reasoning', 'ai-awareness-day' ), 'smart', 6, 6, __( 'Working through a problem step by step. Asking AI to show its steps makes mistakes easier to spot.', 'ai-awareness-day' ), __( 'Ask an AI to show its working on a maths problem. Check every step: is each one right?', 'ai-awareness-day' ) ),
		array( 'Su', __( 'Summarising', 'ai-awareness-day' ), 'smart', 6, 7, __( 'Shortening a text to its main points. AI summaries are quick, but they can leave out key detail or change the meaning.', 'ai-awareness-day' ), __( 'Compare an AI summary with the original text. What was lost or changed?', 'ai-awareness-day' ) ),
		array( 'Tu', __( 'AI Tutor', 'ai-awareness-day' ), 'smart', 7, 3, __( 'An AI set up to explain, question and quiz you, so it helps you learn rather than doing the work for you.', 'ai-awareness-day' ), __( 'Ask an AI to quiz you on a topic instead of explaining it. Which helps you remember more?', 'ai-awareness-day' ) ),
		array( 'Fb', __( 'Feedback', 'ai-awareness-day' ), 'smart', 7, 4, __( 'Comments that help you improve. AI gives instant feedback, but teachers and friends know you and what you are aiming for.', 'ai-awareness-day' ), __( 'Get feedback on the same paragraph from an AI and from a classmate. How are they different?', 'ai-awareness-day' ) ),
		array( 'Ps', __( 'Productive Struggle', 'ai-awareness-day' ), 'smart', 7, 5, __( 'The useful effort of working something out yourself. It is how learning sticks, so skipping it with AI can cost you.', 'ai-awareness-day' ), __( 'When is it worth struggling with a problem instead of asking for the answer?', 'ai-awareness-day' ) ),
		array( 'Or', __( 'Over-Reliance', 'ai-awareness-day' ), 'smart', 7, 6, __( 'Leaning on AI so much that you stop practising skills you need, or stop trusting your own judgement.', 'ai-awareness-day' ), __( 'Which skills do you want to keep doing yourself, even if AI could do them for you?', 'ai-awareness-day' ) ),
		array( 'Mc', __( 'Metacognition', 'ai-awareness-day' ), 'smart', 7, 7, __( 'Thinking about your own thinking: knowing what you understand, what you don\'t yet, and how you learn best.', 'ai-awareness-day' ), __( 'After using AI, can you explain the idea without it? Try it with a partner.', 'ai-awareness-day' ) ),

		// Creative: the centre right block.
		array( 'Cr', __( 'Creativity', 'ai-awareness-day' ), 'creative', 4, 8, __( 'Making something new that matters to you or to others. AI can offer options, but the ideas, choices and judgement are yours.', 'ai-awareness-day' ), __( 'In a piece of work made with AI help, which decisions were really yours?', 'ai-awareness-day' ) ),
		array( 'Ig', __( 'Imagination', 'ai-awareness-day' ), 'creative', 4, 9, __( 'Picturing what doesn\'t exist yet. AI remixes what it has already seen; imagination starts with you.', 'ai-awareness-day' ), __( 'Describe something no AI could ever have seen. Then ask one to draw it. What did it miss?', 'ai-awareness-day' ) ),
		array( 'Og', __( 'Originality', 'ai-awareness-day' ), 'creative', 4, 10, __( 'Work that is genuinely new and your own, rather than copied or simply generated.', 'ai-awareness-day' ), __( 'How could you show which parts of your work are original?', 'ai-awareness-day' ) ),
		array( 'Ov', __( 'Your Own Voice', 'ai-awareness-day' ), 'creative', 4, 11, __( 'The style, opinions and experiences that make your work sound like you and nobody else.', 'ai-awareness-day' ), __( 'Highlight three sentences in your writing that only you could have written.', 'ai-awareness-day' ) ),
		array( 'Ta', __( 'Taste', 'ai-awareness-day' ), 'creative', 4, 12, __( 'Judgement about what is good, fitting or beautiful for an audience. AI can make a hundred versions; taste picks one.', 'ai-awareness-day' ), __( 'Pick the best of five AI images for a poster and explain your choice.', 'ai-awareness-day' ) ),
		array( 'Bs', __( 'Brainstorming', 'ai-awareness-day' ), 'creative', 5, 8, __( 'Producing lots of ideas quickly. AI can widen the list; you choose the best ones and develop them.', 'ai-awareness-day' ), __( 'Brainstorm on your own, then with AI. Which list had the idea you liked most?', 'ai-awareness-day' ) ),
		array( 'Dr', __( 'Drafting', 'ai-awareness-day' ), 'creative', 5, 9, __( 'Writing a first version. An AI draft can get you started, but it rarely says what you mean in your way.', 'ai-awareness-day' ), __( 'Rewrite an AI first draft so it sounds like you. What did you change?', 'ai-awareness-day' ) ),
		array( 'Pc', __( 'Process', 'ai-awareness-day' ), 'creative', 5, 10, __( 'The steps you took to make something. Keeping notes and drafts shows your thinking and your part in the work.', 'ai-awareness-day' ), __( 'Keep a short process log for your next project. What does it show a reader?', 'ai-awareness-day' ) ),
		array( 'Cb', __( 'Collaboration', 'ai-awareness-day' ), 'creative', 5, 11, __( 'Working with others, people or tools, where each adds something the others can\'t.', 'ai-awareness-day' ), __( 'In a group project, what should people do, and what could AI do?', 'ai-awareness-day' ) ),
		array( 'Rx', __( 'Remix', 'ai-awareness-day' ), 'creative', 5, 12, __( 'Building on existing work to make something new. Remixing is part of culture, but it has rules.', 'ai-awareness-day' ), __( 'When does a remix become copying? Find an example of each.', 'ai-awareness-day' ) ),
		array( 'Im', __( 'Image Generation', 'ai-awareness-day' ), 'creative', 6, 8, __( 'Creating pictures from a written description. The AI makes many choices you didn\'t ask for.', 'ai-awareness-day' ), __( 'Generate an image, then list every decision the AI made that you did not ask for.', 'ai-awareness-day' ) ),
		array( 'Mu', __( 'AI Music', 'ai-awareness-day' ), 'creative', 6, 9, __( 'Songs, voices and sounds made by AI, from backing tracks to whole songs in the style of real artists.', 'ai-awareness-day' ), __( 'Can you hear the difference between AI music and human music? Does it matter?', 'ai-awareness-day' ) ),
		array( 'Vi', __( 'Video Generation', 'ai-awareness-day' ), 'creative', 6, 10, __( 'Making realistic video clips from text or images. They are getting harder to tell apart from real footage.', 'ai-awareness-day' ), __( 'What clues might show that a video was generated? Are they reliable?', 'ai-awareness-day' ) ),
		array( 'Cg', __( 'Code Generation', 'ai-awareness-day' ), 'creative', 6, 11, __( 'AI writing computer code from a description. Useful, but you still need to understand it, test it and fix it.', 'ai-awareness-day' ), __( 'If AI writes code that breaks something, whose job is it to fix it?', 'ai-awareness-day' ) ),
		array( 'Sa', __( 'Sameness', 'ai-awareness-day' ), 'creative', 6, 12, __( 'When lots of people use the same AI tools, their work can start to look and sound the same.', 'ai-awareness-day' ), __( 'Everyone ask an AI for a poem title on the same topic. How similar are the results?', 'ai-awareness-day' ) ),
		array( 'Au', __( 'Authorship', 'ai-awareness-day' ), 'creative', 7, 8, __( 'Who made a piece of work, and who gets the credit for it.', 'ai-awareness-day' ), __( 'If an AI wrote half of your story, who is the author?', 'ai-awareness-day' ) ),
		array( 'At', __( 'Attribution', 'ai-awareness-day' ), 'creative', 7, 9, __( 'Giving credit to the people, sources and tools whose work you used.', 'ai-awareness-day' ), __( 'Write a credit line for a project that used two websites and one AI tool.', 'ai-awareness-day' ) ),
		array( 'Cp', __( 'Copyright', 'ai-awareness-day' ), 'creative', 7, 10, __( 'The legal right of creators to control how their work is copied and used. How it applies to AI is still being argued in courts.', 'ai-awareness-day' ), __( 'Should artists be asked, or paid, before AI learns from their work?', 'ai-awareness-day' ) ),
		array( 'Si', __( 'Style Copying', 'ai-awareness-day' ), 'creative', 7, 11, __( 'AI imitating a particular artist\'s or writer\'s style, often without their permission.', 'ai-awareness-day' ), __( 'Is copying someone\'s style unfair if you don\'t copy their actual work?', 'ai-awareness-day' ) ),
		array( 'Wm', __( 'Watermark', 'ai-awareness-day' ), 'creative', 7, 12, __( 'A visible or hidden mark that shows content was made by AI.', 'ai-awareness-day' ), __( 'Should every AI-made image carry a watermark? Who would check?', 'ai-awareness-day' ) ),

		// Safe: the right-hand block, first three columns.
		array( 'Pv', __( 'Privacy', 'ai-awareness-day' ), 'safe', 2, 13, __( 'Your right to control who sees information about you and what they do with it.', 'ai-awareness-day' ), __( 'What would you never type into a chatbot? Why?', 'ai-awareness-day' ) ),
		array( 'Pi', __( 'Personal Information', 'ai-awareness-day' ), 'safe', 2, 14, __( 'Anything that identifies you, such as your name, address, school, photos, voice or location.', 'ai-awareness-day' ), __( 'What could someone piece together about you from your public posts?', 'ai-awareness-day' ) ),
		array( 'Fp', __( 'Digital Footprint', 'ai-awareness-day' ), 'safe', 2, 15, __( 'The trail of data you leave online, including what you post, search for and type into AI tools.', 'ai-awareness-day' ), __( 'Imagine someone searched your name in five years\' time. What would you want them to find?', 'ai-awareness-day' ) ),
		array( 'Cy', __( 'Cybersecurity', 'ai-awareness-day' ), 'safe', 3, 13, __( 'Protecting devices, accounts and data from attack, with strong passwords, two-step checks and updates.', 'ai-awareness-day' ), __( 'What are three things you could do this week to protect your accounts?', 'ai-awareness-day' ) ),
		array( 'Me', __( 'AI Memory', 'ai-awareness-day' ), 'safe', 3, 14, __( 'Some chatbots remember past conversations to personalise their answers. You can usually see and delete what they keep.', 'ai-awareness-day' ), __( 'Find the memory or history settings in an AI tool. What has it kept about you?', 'ai-awareness-day' ) ),
		array( 'Ts', __( 'Terms of Service', 'ai-awareness-day' ), 'safe', 3, 15, __( 'The rules you agree to when you use an app, including what happens to your data.', 'ai-awareness-day' ), __( 'Find one surprising rule in the terms of an app you use.', 'ai-awareness-day' ) ),
		array( 'Ae', __( 'Age Limits', 'ai-awareness-day' ), 'safe', 4, 13, __( 'Many AI tools have a minimum age, often 13 or 18, set out in their terms. Check before you sign up.', 'ai-awareness-day' ), __( 'Why do you think AI tools have age limits? Are they set at the right ages?', 'ai-awareness-day' ) ),
		array( 'Tr', __( 'Trust', 'ai-awareness-day' ), 'safe', 4, 14, __( 'Trusting AI the right amount: more for low-stakes tasks you can check, less for health, money or safety.', 'ai-awareness-day' ), __( 'Rank five tasks from "fine to trust AI" to "always ask a person".', 'ai-awareness-day' ) ),
		array( 'Wb', __( 'Wellbeing', 'ai-awareness-day' ), 'safe', 4, 15, __( 'How you feel and cope day to day. Notice whether time with AI and screens leaves you feeling better or worse.', 'ai-awareness-day' ), __( 'How do you feel after an hour online? What helps you switch off?', 'ai-awareness-day' ) ),
		array( 'Mi', __( 'Misinformation', 'ai-awareness-day' ), 'safe', 5, 13, __( 'False information shared by mistake, by people who believe it is true.', 'ai-awareness-day' ), __( 'Have you ever shared something that turned out to be false? How did you find out?', 'ai-awareness-day' ) ),
		array( 'Ds', __( 'Disinformation', 'ai-awareness-day' ), 'safe', 5, 14, __( 'False information spread on purpose to mislead, now faster and cheaper to make with AI.', 'ai-awareness-day' ), __( 'Who gains when disinformation spreads? Think of an example.', 'ai-awareness-day' ) ),
		array( 'Ph', __( 'Phishing and Scams', 'ai-awareness-day' ), 'safe', 5, 15, __( 'Tricks to get your money, passwords or personal details. AI can make scam messages more convincing.', 'ai-awareness-day' ), __( 'What are the warning signs that a message is a scam?', 'ai-awareness-day' ) ),
		array( 'Dk', __( 'Deepfake', 'ai-awareness-day' ), 'safe', 6, 13, __( 'AI-made video, images or audio showing someone saying or doing something they never did.', 'ai-awareness-day' ), __( 'How would you check whether a shocking video of a famous person is real?', 'ai-awareness-day' ) ),
		array( 'Vc', __( 'Voice Cloning', 'ai-awareness-day' ), 'safe', 6, 14, __( 'Copying someone\'s voice from a short recording so that it can be made to say anything.', 'ai-awareness-day' ), __( 'How could a family "safe word" help if someone faked your voice on the phone?', 'ai-awareness-day' ) ),
		array( 'Cs', __( 'Consent', 'ai-awareness-day' ), 'safe', 6, 15, __( 'Agreeing freely to something, such as your photo, voice or work being used. Without consent, it is not OK.', 'ai-awareness-day' ), __( 'Is it OK to put a friend\'s photo into an AI app as a joke? What should you ask first?', 'ai-awareness-day' ) ),
		array( 'Cm', __( 'AI Companion', 'ai-awareness-day' ), 'safe', 7, 13, __( 'A chatbot designed to act as a friend or partner. It can feel caring, but it is not a person.', 'ai-awareness-day' ), __( 'What can a friend give you that an AI companion can\'t?', 'ai-awareness-day' ) ),
		array( 'Ib', __( 'Image-Based Abuse', 'ai-awareness-day' ), 'safe', 7, 14, __( 'Making or sharing intimate or humiliating images of someone, including AI-made fakes. It causes real harm and can be a crime.', 'ai-awareness-day' ), __( 'If this happened to someone you know, who could they tell, and how could it be reported?', 'ai-awareness-day' ) ),
		array( 'Rt', __( 'Report and Tell', 'ai-awareness-day' ), 'safe', 7, 15, __( 'If something online worries you, tell a trusted adult and use the platform\'s report tools.', 'ai-awareness-day' ), __( 'Who are your trusted adults? Find the report button in an app you use.', 'ai-awareness-day' ) ),

		// Responsible: the right-hand block, last three columns.
		array( 'Bi', __( 'Bias', 'ai-awareness-day' ), 'responsible', 2, 16, __( 'When an AI treats some people or groups unfairly, often because of patterns in its training data.', 'ai-awareness-day' ), __( 'Where could bias in an AI cause real harm, for example in jobs, policing or exam results?', 'ai-awareness-day' ) ),
		array( 'Fa', __( 'Fairness', 'ai-awareness-day' ), 'responsible', 2, 17, __( 'Making sure the outcomes of AI don\'t unjustly favour or disadvantage particular people or groups.', 'ai-awareness-day' ), __( 'Is it fair if an AI is right 95% of the time, but is wrong most often for one group?', 'ai-awareness-day' ) ),
		array( 'St', __( 'Stereotype', 'ai-awareness-day' ), 'responsible', 2, 18, __( 'An oversimplified idea about a group of people. AI can repeat stereotypes it learned, such as who "looks like" a scientist.', 'ai-awareness-day' ), __( 'Ask an image AI for "a scientist" or "a nurse". Who appears, and who doesn\'t?', 'ai-awareness-day' ) ),
		array( 'Ac', __( 'Accountability', 'ai-awareness-day' ), 'responsible', 3, 16, __( 'Being answerable when AI is used and something goes wrong. A person or organisation is responsible, not the tool.', 'ai-awareness-day' ), __( 'An AI marks your work unfairly. Who is accountable?', 'ai-awareness-day' ) ),
		array( 'Tp', __( 'Transparency', 'ai-awareness-day' ), 'responsible', 3, 17, __( 'Being open about when AI is being used, how it works and what data it relies on.', 'ai-awareness-day' ), __( 'Should you always be told when you are talking to an AI?', 'ai-awareness-day' ) ),
		array( 'Ex', __( 'Explainability', 'ai-awareness-day' ), 'responsible', 3, 18, __( 'Being able to understand why an AI gave a particular answer or made a particular decision.', 'ai-awareness-day' ), __( 'Would you accept a decision about you if nobody could explain it?', 'ai-awareness-day' ) ),
		array( 'In', __( 'Academic Integrity', 'ai-awareness-day' ), 'responsible', 4, 16, __( 'Honesty in your schoolwork: doing your own work, following the rules on AI and crediting any help you had.', 'ai-awareness-day' ), __( 'What does your school\'s AI policy allow, and what does it not?', 'ai-awareness-day' ) ),
		array( 'Di', __( 'Disclosure', 'ai-awareness-day' ), 'responsible', 4, 17, __( 'Saying openly when and how you used AI in your work.', 'ai-awareness-day' ), __( 'Write a one-line note saying exactly how you used AI in a recent piece of work.', 'ai-awareness-day' ) ),
		array( 'Pl', __( 'Plagiarism', 'ai-awareness-day' ), 'responsible', 4, 18, __( 'Passing off someone else\'s work, or an AI\'s, as your own.', 'ai-awareness-day' ), __( 'Where is the line between getting help from AI and plagiarism?', 'ai-awareness-day' ) ),
		array( 'Pe', __( 'Permission', 'ai-awareness-day' ), 'responsible', 5, 16, __( 'Asking before you use someone\'s work, image or data, including in an AI tool.', 'ai-awareness-day' ), __( 'Whose permission would you need before uploading a class photo to an AI app?', 'ai-awareness-day' ) ),
		array( 'Cq', __( 'Consequences', 'ai-awareness-day' ), 'responsible', 5, 17, __( 'Thinking ahead about who could be affected by how AI is used, now and later.', 'ai-awareness-day' ), __( 'Pick one way AI is used at school. Who benefits, and who might be harmed?', 'ai-awareness-day' ) ),
		array( 'Hr', __( 'Human Review', 'ai-awareness-day' ), 'responsible', 5, 18, __( 'When an AI decision really matters, a person checks it and can change it.', 'ai-awareness-day' ), __( 'Which decisions at school should always be checked by a person?', 'ai-awareness-day' ) ),
		array( 'En', __( 'Environmental Impact', 'ai-awareness-day' ), 'responsible', 6, 16, __( 'Training and running AI uses a lot of electricity and water, and its hardware needs rare materials.', 'ai-awareness-day' ), __( 'Is it worth using AI for a task you could quickly do yourself?', 'ai-awareness-day' ) ),
		array( 'Eq', __( 'Equity', 'ai-awareness-day' ), 'responsible', 6, 17, __( 'Not everyone has the same access to devices, the internet or paid AI tools, which can widen gaps between people.', 'ai-awareness-day' ), __( 'How could a school make sure AI doesn\'t leave some students behind?', 'ai-awareness-day' ) ),
		array( 'Ax', __( 'Accessibility', 'ai-awareness-day' ), 'responsible', 6, 18, __( 'AI tools such as read-aloud, captions and translation can open up learning, as long as everyone can use them.', 'ai-awareness-day' ), __( 'Which AI tools could help someone with a disability, or someone learning English?', 'ai-awareness-day' ) ),
		array( 'Lb', __( 'Hidden Workers', 'ai-awareness-day' ), 'responsible', 7, 16, __( 'People, often low-paid, who label data and check harmful content so that AI systems work.', 'ai-awareness-day' ), __( 'Should companies tell us about the people who help train their AI?', 'ai-awareness-day' ) ),
		array( 'Ap', __( 'Acceptable Use Policy', 'ai-awareness-day' ), 'responsible', 7, 17, __( 'A school\'s or organisation\'s rules on how AI can and can\'t be used.', 'ai-awareness-day' ), __( 'If your class wrote its own AI rules, what would the top three be?', 'ai-awareness-day' ) ),
		array( 'Rg', __( 'Regulation', 'ai-awareness-day' ), 'responsible', 7, 18, __( 'Laws that set rules for AI, such as UK data protection law and the EU AI Act.', 'ai-awareness-day' ), __( 'If you could add one law about AI, what would it be?', 'ai-awareness-day' ) ),

		// Future: the row below the table.
		array( 'Fw', __( 'Future of Work', 'ai-awareness-day' ), 'future', 9, 4, __( 'AI is changing jobs: some tasks are automated, others change, and new roles appear.', 'ai-awareness-day' ), __( 'Which part of a job you would like would be hardest for AI to do?', 'ai-awareness-day' ) ),
		array( 'Hs', __( 'Human Skills', 'ai-awareness-day' ), 'future', 9, 5, __( 'Skills AI struggles with, such as empathy, teamwork, judgement, persuasion and care.', 'ai-awareness-day' ), __( 'Which human skill are you best at? How could you show it to an employer?', 'ai-awareness-day' ) ),
		array( 'Ll', __( 'Lifelong Learning', 'ai-awareness-day' ), 'future', 9, 6, __( 'Carrying on learning all through your life, because tools and jobs will keep changing.', 'ai-awareness-day' ), __( 'What is something you taught yourself? How did you do it?', 'ai-awareness-day' ) ),
		array( 'Ad', __( 'Adaptability', 'ai-awareness-day' ), 'future', 9, 7, __( 'Being able to adjust when things change, including how you study and work.', 'ai-awareness-day' ), __( 'Describe a time you had to adapt quickly. What helped?', 'ai-awareness-day' ) ),
		array( 'Ag', __( 'Human Agency', 'ai-awareness-day' ), 'future', 9, 8, __( 'Being able to make your own choices and shape what happens, rather than letting technology decide for you.', 'ai-awareness-day' ), __( 'Where does AI make choices for you? How could you take some back?', 'ai-awareness-day' ) ),
		array( 'Yv', __( 'Youth Voice', 'ai-awareness-day' ), 'future', 9, 9, __( 'Young people having a real say in the decisions about AI that will shape their lives.', 'ai-awareness-day' ), __( 'What one message about AI would you want decision-makers to hear?', 'ai-awareness-day' ) ),
		array( 'Op', __( 'Opportunity', 'ai-awareness-day' ), 'future', 9, 10, __( 'New chances that AI opens up, from new jobs and businesses to new ways of solving problems in your community.', 'ai-awareness-day' ), __( 'What problem in your area could AI help solve, and what could go wrong?', 'ai-awareness-day' ) ),
		array( 'Am', __( 'Automation', 'ai-awareness-day' ), 'future', 9, 11, __( 'Using technology to do tasks without a person, from sorting post to writing reports.', 'ai-awareness-day' ), __( 'Which boring task would you happily automate? Which would you never hand over?', 'ai-awareness-day' ) ),
		array( 'Gn', __( 'AI Agent', 'ai-awareness-day' ), 'future', 9, 12, __( 'AI that takes actions to reach a goal, such as booking, buying or sending messages, rather than only answering questions.', 'ai-awareness-day' ), __( 'What should an AI agent always check with you before doing it on your behalf?', 'ai-awareness-day' ) ),
		array( 'Rb', __( 'Robotics', 'ai-awareness-day' ), 'future', 9, 13, __( 'AI in physical machines, such as delivery robots, self-driving cars and robots in hospitals.', 'ai-awareness-day' ), __( 'Would you ride in a self-driving car? What would make you trust it?', 'ai-awareness-day' ) ),
		array( 'Dv', __( 'Discovery', 'ai-awareness-day' ), 'future', 9, 14, __( 'AI helping scientists find new medicines, materials and ideas faster than before.', 'ai-awareness-day' ), __( 'Which big problem would you most want AI to help scientists solve?', 'ai-awareness-day' ) ),
		array( 'So', __( 'Society', 'ai-awareness-day' ), 'future', 9, 15, __( 'How AI changes the way we live together, including news, democracy, relationships and communities.', 'ai-awareness-day' ), __( 'How might AI change elections? What would protect them?', 'ai-awareness-day' ) ),
		array( 'Po', __( 'Policy', 'ai-awareness-day' ), 'future', 9, 16, __( 'Decisions by governments, schools and companies about how AI is used. People, including young people, can influence them.', 'ai-awareness-day' ), __( 'Who decides how AI is used in your school? How could students have a say?', 'ai-awareness-day' ) ),
		array( 'Hy', __( 'Hype', 'ai-awareness-day' ), 'future', 9, 17, __( 'Exaggerated claims about what AI can do, or will soon do, from companies, the media or social posts.', 'ai-awareness-day' ), __( 'Find a headline about AI. Is it hype, fair or too gloomy? How can you tell?', 'ai-awareness-day' ) ),
		array( 'Gi', __( 'Artificial General Intelligence', 'ai-awareness-day' ), 'future', 9, 18, __( 'A possible future AI that could match people at almost any task. Experts disagree about whether, or when, it will arrive.', 'ai-awareness-day' ), __( 'How would we know if artificial general intelligence had arrived? Who should decide?', 'ai-awareness-day' ) ),
	);
}

/**
 * The elements in reading order (row by row, left to right), numbered from 1.
 *
 * @return array<int, array{n:int, sym:string, id:string, name:string, family:string, row:int, col:int, def:string, talk:string}>
 */
function aiad_literacy_elements(): array {
	static $elements = null;
	if ( null !== $elements ) {
		return $elements;
	}
	$raw = aiad_literacy_raw_elements();
	usort( $raw, static fn( array $a, array $b ): int => ( $a[3] <=> $b[3] ) ?: ( $a[4] <=> $b[4] ) );
	$elements = array();
	foreach ( $raw as $i => $e ) {
		$elements[] = array(
			'n'      => $i + 1,
			'sym'    => $e[0],
			'id'     => 'alpt-' . strtolower( $e[0] ),
			'name'   => $e[1],
			'family' => $e[2],
			'row'    => $e[3],
			'col'    => $e[4],
			'def'    => $e[5],
			'talk'   => $e[6],
		);
	}
	return $elements;
}

/**
 * A tile's name with soft hyphens in its long words, so a narrow tile breaks them where a reader expects, in every
 * browser (not all have hyphenation dictionaries, and print-to-PDF often has none). Only the tiles use it.
 *
 * @param string $name An element's name.
 */
function aiad_literacy_tile_name( string $name ): string {
	static $breaks = null;
	if ( null === $breaks ) {
		$words  = array(
			'Accept|able', 'Account|ability', 'Access|ibility', 'Adapt|ability', 'Attri|bution', 'Author|ship', 'Auto|mation', 'Brain|storming',
			'Collab|oration', 'Com|panion', 'Confi|dent', 'Conse|quences', 'Copy|right', 'Cyber|security', 'Dis|closure', 'Dis|inform|ation',
			'Environ|mental', 'Explain|ability', 'Gener|ation', 'Gener|ative', 'Halluci|nation', 'Imagin|ation', 'Inform|ation', 'Intelli|gence',
			'Know|ledge', 'Meta|cognition', 'Mis|inform|ation', 'Oppor|tunity', 'Origin|ality', 'Per|mission', 'Plagia|rism', 'Pre|diction',
			'Pro|ductive', 'Reason|ing', 'Recom|mend|ation', 'Regu|lation', 'Same|ness', 'Stereo|type', 'Summar|ising', 'Syco|phancy',
			'Trans|parency', 'Water|mark',
		);
		$breaks = array();
		foreach ( $words as $word ) {
			$breaks[ str_replace( '|', '', $word ) ] = str_replace( '|', "\u{00AD}", $word );
		}
	}
	return strtr( $name, $breaks );
}

/**
 * The table: the family filter and search, the 18-column grid with the detail panel in its gap, and every
 * definition, family by family, below it (what the tiles link to without JavaScript, and what search engines read).
 */
function aiad_render_literacy_table(): string {
	$families = aiad_literacy_families();
	$elements = aiad_literacy_elements();
	$total    = count( $elements );
	$order    = array_flip( array_keys( $families ) );
	$first    = $elements[0];
	$counts   = array_count_values( array_column( $elements, 'family' ) );

	ob_start();
	?>
<div class="alpt" data-alpt>
	<div class="alpt-controls" data-alpt-controls hidden>
		<div class="alpt-search">
			<label for="alpt-search-input"><?php esc_html_e( 'Search the table', 'ai-awareness-day' ); ?></label>
			<input type="search" id="alpt-search-input" data-alpt-search autocomplete="off" placeholder="<?php esc_attr_e( 'Try "privacy" or "bias"', 'ai-awareness-day' ); ?>">
		</div>
		<div class="alpt-legend" role="group" aria-label="<?php esc_attr_e( 'Show one family', 'ai-awareness-day' ); ?>">
			<?php foreach ( $families as $key => $family ) : ?>
				<button type="button" class="alpt-chip alpt-fam--<?php echo esc_attr( $key ); ?>" data-alpt-family="<?php echo esc_attr( $key ); ?>" aria-pressed="false">
					<span class="alpt-chip__swatch" aria-hidden="true"></span>
					<span><?php echo esc_html( $family['name'] ); ?></span>
					<span class="alpt-chip__count"><?php echo esc_html( (string) ( $counts[ $key ] ?? 0 ) ); ?></span>
				</button>
			<?php endforeach; ?>
			<button type="button" class="alpt-chip alpt-chip--all" data-alpt-family="" aria-pressed="true"><?php esc_html_e( 'All families', 'ai-awareness-day' ); ?></button>
		</div>
		<div class="alpt-status" data-alpt-status aria-live="polite"></div><?php // A div, not a p: theme pages drop empty paragraphs (aiad_editable_page_render_block()). ?>
	</div>

	<div class="alpt-grid">
		<div class="alpt-panel alpt-fam--<?php echo esc_attr( $first['family'] ); ?>" data-alpt-panel>
			<div class="alpt-panel__print" aria-hidden="true">
				<p class="alpt-panel__print-eyebrow"><?php esc_html_e( 'AI Awareness Day', 'ai-awareness-day' ); ?></p>
				<p class="alpt-panel__print-title"><?php esc_html_e( 'The AI Literacy Periodic Table', 'ai-awareness-day' ); ?></p>
				<p class="alpt-panel__print-lead">
					<?php
					/* translators: %d: number of elements */
					echo esc_html( sprintf( __( '%d ideas everyone needs to understand AI. Read every definition, and a question to talk about for each one, at aiawarenessday.co.uk/ai-literacy-periodic-table', 'ai-awareness-day' ), $total ) );
					?>
				</p>
				<ul class="alpt-panel__print-key">
					<?php foreach ( $families as $key => $family ) : ?>
						<li class="alpt-fam--<?php echo esc_attr( $key ); ?>"><span class="alpt-chip__swatch"></span><?php echo esc_html( $family['name'] ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="alpt-panel__tile" aria-hidden="true">
				<span class="alpt-panel__n" data-alpt-out="n"><?php echo esc_html( (string) $first['n'] ); ?></span>
				<span class="alpt-panel__sym" data-alpt-out="sym"><?php echo esc_html( $first['sym'] ); ?></span>
				<span class="alpt-panel__tile-name" data-alpt-out="name"><?php echo esc_html( $first['name'] ); ?></span>
			</div>
			<div class="alpt-panel__body" data-alpt-live aria-live="polite">
				<p class="alpt-panel__family" data-alpt-out="family"><?php echo esc_html( $families[ $first['family'] ]['name'] ); ?></p>
				<h2 class="alpt-panel__title" data-alpt-out="title"><?php echo esc_html( $first['name'] ); ?></h2>
				<p class="alpt-panel__def" data-alpt-out="def"><?php echo esc_html( $first['def'] ); ?></p>
				<div class="alpt-panel__talk">
					<p class="alpt-panel__talk-label"><?php esc_html_e( 'Talk about it', 'ai-awareness-day' ); ?></p>
					<p data-alpt-out="talk"><?php echo esc_html( $first['talk'] ); ?></p>
				</div>
			</div>
			<div class="alpt-panel__nav" data-alpt-nav hidden>
				<button type="button" class="alpt-btn" data-alpt-step="-1"><span aria-hidden="true">&larr;</span> <?php esc_html_e( 'Previous', 'ai-awareness-day' ); ?></button>
				<button type="button" class="alpt-btn" data-alpt-step="1"><?php esc_html_e( 'Next', 'ai-awareness-day' ); ?> <span aria-hidden="true">&rarr;</span></button>
				<button type="button" class="alpt-btn alpt-btn--ink" data-alpt-random><?php esc_html_e( 'Random element', 'ai-awareness-day' ); ?></button>
				<span class="alpt-panel__count">
					<?php
					/* translators: 1: element number, 2: number of elements */
					printf( esc_html__( 'Element %1$s of %2$s', 'ai-awareness-day' ), '<span data-alpt-out="n2">' . esc_html( (string) $first['n'] ) . '</span>', esc_html( (string) $total ) );
					?>
				</span>
			</div>
		</div>

		<div class="alpt-key" aria-hidden="true">
			<div class="alpt-tile alpt-tile--key">
				<span class="alpt-tile__n">1</span>
				<span class="alpt-tile__sym">Ai</span>
				<span class="alpt-tile__name"><?php esc_html_e( 'Artificial Intelligence', 'ai-awareness-day' ); ?></span>
			</div>
			<div class="alpt-key__text">
				<p class="alpt-key__title"><?php esc_html_e( 'How to read a tile', 'ai-awareness-day' ); ?></p>
				<p><?php esc_html_e( 'Top: its number, in reading order', 'ai-awareness-day' ); ?></p>
				<p><?php esc_html_e( 'Middle: its symbol. Bottom: the idea', 'ai-awareness-day' ); ?></p>
				<p><?php esc_html_e( 'Colour: the family it belongs to', 'ai-awareness-day' ); ?></p>
			</div>
		</div>

		<?php foreach ( $families as $key => $family ) : ?>
			<p class="alpt-family-label alpt-fam--<?php echo esc_attr( $key ); ?>" style="--o:<?php echo esc_attr( (string) ( $order[ $key ] * 1000 ) ); ?>">
				<span class="alpt-family-label__name"><?php echo esc_html( $family['name'] ); ?></span>
				<span class="alpt-family-label__about"><?php echo esc_html( $family['about'] ); ?></span>
			</p>
		<?php endforeach; ?>

		<?php foreach ( $elements as $el ) : ?>
			<a class="alpt-tile alpt-fam--<?php echo esc_attr( $el['family'] ); ?>" href="#<?php echo esc_attr( $el['id'] ); ?>" data-alpt-el="<?php echo esc_attr( (string) ( $el['n'] - 1 ) ); ?>" style="--r:<?php echo esc_attr( (string) $el['row'] ); ?>;--c:<?php echo esc_attr( (string) $el['col'] ); ?>;--o:<?php echo esc_attr( (string) ( $order[ $el['family'] ] * 1000 + $el['n'] ) ); ?>">
				<span class="alpt-tile__n"><?php echo esc_html( (string) $el['n'] ); ?></span>
				<span class="alpt-tile__sym"><?php echo esc_html( $el['sym'] ); ?></span>
				<span class="alpt-tile__name"><?php echo esc_html( aiad_literacy_tile_name( $el['name'] ) ); ?></span>
				<span class="screen-reader-text">, <?php echo esc_html( $families[ $el['family'] ]['name'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>

	<p class="alpt-footnote"><?php esc_html_e( 'Left: how AI works. Centre: thinking smart and being creative with AI. Right: staying safe and being responsible. Below: the future we are shaping. Numbers run left to right, top to bottom.', 'ai-awareness-day' ); ?></p>

	<section class="alpt-glossary" aria-labelledby="alpt-glossary-title">
		<h2 id="alpt-glossary-title" class="alpt-glossary__title"><?php esc_html_e( 'Every element, family by family', 'ai-awareness-day' ); ?></h2>
		<p class="alpt-glossary__lead"><?php esc_html_e( 'All the definitions and questions in one list, to read, copy into a lesson or print.', 'ai-awareness-day' ); ?></p>
		<?php foreach ( $families as $key => $family ) : ?>
			<details class="alpt-glossary__family alpt-fam--<?php echo esc_attr( $key ); ?>">
				<summary>
					<span class="alpt-chip__swatch" aria-hidden="true"></span>
					<span class="alpt-glossary__family-name"><?php echo esc_html( $family['name'] ); ?></span>
					<span class="alpt-glossary__family-about"><?php echo esc_html( $family['about'] ); ?></span>
				</summary>
				<dl>
					<?php foreach ( $elements as $el ) : ?>
						<?php
						if ( $el['family'] !== $key ) {
							continue;
						}
						?>
						<div class="alpt-glossary__item">
							<dt id="<?php echo esc_attr( $el['id'] ); ?>"><span class="alpt-glossary__sym" aria-hidden="true"><?php echo esc_html( $el['sym'] ); ?></span> <?php echo esc_html( $el['name'] ); ?> <span class="alpt-glossary__n"><?php echo esc_html( (string) $el['n'] ); ?></span></dt>
							<dd>
								<p><?php echo esc_html( $el['def'] ); ?></p>
								<p class="alpt-glossary__talk"><strong><?php esc_html_e( 'Talk about it:', 'ai-awareness-day' ); ?></strong> <?php echo esc_html( $el['talk'] ); ?></p>
							</dd>
						</div>
					<?php endforeach; ?>
				</dl>
			</details>
		<?php endforeach; ?>
	</section>

	<script type="application/json" data-alpt-data><?php echo wp_json_encode( array( 'families' => array_map( static fn( array $f ): string => $f['name'], $families ), 'elements' => array_map( static fn( array $el ): array => array( $el['sym'], $el['name'], $el['family'], $el['def'], $el['talk'], $el['id'] ), $elements ) ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ); ?></script>
</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * The page's stylesheet and script, and the table's block (aiad/literacy-table), which loads them wherever it is used.
 * In the editor it shows the table as the page does (assets/js/literacy-table-edit.js).
 */
function aiad_register_literacy_table(): void {
	$css    = AIAD_DIR . '/assets/css/pages/ai-literacy-periodic-table.css';
	$js     = AIAD_DIR . '/assets/js/literacy-table.js';
	$editor = AIAD_DIR . '/assets/js/literacy-table-edit.js';
	$ver    = static fn( string $file ): string => AIAD_VERSION . '.' . ( is_readable( $file ) ? (string) filemtime( $file ) : '0' );

	wp_register_style( 'aiad-literacy-table', AIAD_URI . '/assets/css/pages/ai-literacy-periodic-table.css', array( 'aiad-style' ), $ver( $css ) );
	wp_register_script( 'aiad-literacy-table', AIAD_URI . '/assets/js/literacy-table.js', array(), $ver( $js ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_register_script( 'aiad-literacy-table-edit', AIAD_URI . '/assets/js/literacy-table-edit.js', array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-server-side-render' ), $ver( $editor ), true );

	register_block_type(
		'aiad/literacy-table',
		array(
			'api_version'     => 3,
			'title'           => __( 'AI Literacy Periodic Table', 'ai-awareness-day' ),
			'description'     => __( 'The interactive table of 105 AI literacy ideas, with every definition listed below it. Its elements are written in the theme (inc/literacy-table.php).', 'ai-awareness-day' ),
			'category'        => 'widgets',
			'icon'            => 'grid-view',
			'keywords'        => array( 'periodic', 'literacy', 'glossary' ),
			'supports'        => array( 'html' => false, 'multiple' => false, 'reusable' => false ),
			'editor_script'   => 'aiad-literacy-table-edit',
			'style'           => 'aiad-literacy-table',
			'view_script'     => 'aiad-literacy-table',
			'render_callback' => static fn(): string => aiad_render_literacy_table(),
		)
	);
}
add_action( 'init', 'aiad_register_literacy_table' );

// The page's own stylesheet, for its introduction and classroom ideas, even if the table's block is taken out.
add_action(
	'wp_enqueue_scripts',
	static function (): void {
		if ( ! is_admin() && aiad_is_literacy_table_page() ) {
			wp_enqueue_style( 'aiad-literacy-table' );
		}
	},
	15
);

add_filter(
	'pre_get_document_title',
	static function ( string $title ): string {
		return aiad_is_literacy_table_page() ? __( 'The AI Literacy Periodic Table for teachers and students | AI Awareness Day', 'ai-awareness-day' ) : $title;
	},
	20
);

add_filter(
	'body_class',
	static function ( array $classes ): array {
		if ( aiad_is_literacy_table_page() ) {
			$classes[] = 'literacy-table-page';
		}
		return $classes;
	}
);
