<?php
/**
 * The AI Literacy Periodic Table: /ai-literacy-periodic-table/, for teachers and students.
 *
 * 142 AI terms in seven families: the 120 terms of the original Periodic Table of AI, from the foundations everything
 * is built on, through generative, predictive, agentic and automation AI, to the governance and infrastructure that
 * hold it together, with 21 newer buzzwords and reverse engineering terms added to the families they belong to. Each element has a plain-English definition. (The first version, with six families built on the campaign's themes, is kept in
 * archive/literacy-periodic-table-v1-aiad-strands/.)
 *
 * It is an ordinary page of blocks (patterns/ai-literacy-periodic-table.php), created the first time the theme loads
 * (aiad_maybe_convert_theme_pages() in inc/editable-pages.php), so its introduction and classroom ideas are edited on
 * the page. The table itself is one block, aiad/literacy-table, drawn here from aiad_literacy_elements(), so its 142
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
 * The seven families, in table order: key => name and what it covers.
 *
 * @return array<string, array{name:string, about:string}>
 */
function aiad_literacy_families(): array {
	return array(
		'foundations' => array(
			'name'  => __( 'Foundations', 'ai-awareness-day' ),
			'about' => __( 'The ideas everything else is built on.', 'ai-awareness-day' ),
		),
		'generative' => array(
			'name'  => __( 'Generative AI', 'ai-awareness-day' ),
			'about' => __( 'AI that makes new text, images, sound, video and code.', 'ai-awareness-day' ),
		),
		'predictive' => array(
			'name'  => __( 'Predictive & Analytical AI', 'ai-awareness-day' ),
			'about' => __( 'AI that finds patterns in data to sort, forecast and recommend.', 'ai-awareness-day' ),
		),
		'agents' => array(
			'name'  => __( 'Autonomous AI Agents', 'ai-awareness-day' ),
			'about' => __( 'AI that plans and takes actions towards a goal.', 'ai-awareness-day' ),
		),
		'automation' => array(
			'name'  => __( 'Automation & Workflow AI', 'ai-awareness-day' ),
			'about' => __( 'AI and software that carry out tasks and processes.', 'ai-awareness-day' ),
		),
		'governance' => array(
			'name'  => __( 'Governance & Ethics', 'ai-awareness-day' ),
			'about' => __( 'Keeping AI fair, safe, lawful and accountable, and the ways people get around it.', 'ai-awareness-day' ),
		),
		'infra' => array(
			'name'  => __( 'Infrastructure & Ops', 'ai-awareness-day' ),
			'about' => __( 'The hardware, tools and methods that run AI.', 'ai-awareness-day' ),
		),
	);
}

/**
 * Every element: symbol, name, family, row and column on the 18-column table, and definition. Rows 1 to 8 are the
 * main table (rows 1 to 3 have a gap in the middle for the detail panel, as a periodic table does); rows 10 to 12 are
 * the three rows below it. Elements are numbered in reading order, so the numbers follow from where they sit.
 *
 * @return array<int, array{0:string, 1:string, 2:string, 3:int, 4:int, 5:string}>
 */
function aiad_literacy_raw_elements(): array {
	return array(
		// Foundations: the left-hand block, and the top corners.
		array( 'Ai', __( 'Artificial Intelligence', 'ai-awareness-day' ), 'foundations', 1, 1, __( 'Computer systems that perform tasks normally requiring human intelligence, such as understanding language, recognising patterns and making decisions.', 'ai-awareness-day' ) ),
		array( 'Ls', __( 'Latent Space', 'ai-awareness-day' ), 'foundations', 1, 2, __( 'A model’s private map of ideas, made of numbers, where similar ideas sit close together as it works towards an answer.', 'ai-awareness-day' ) ),
		array( 'Rsi', __( 'Recursive Self-Improvement', 'ai-awareness-day' ), 'foundations', 1, 16, __( 'AI helping to build better AI, which could then build better AI still. An idea first described in 1965. AI labs now use their own models to help train and test the next ones, which has made the question of who stays in charge more pressing.', 'ai-awareness-day' ) ),
		array( 'Si', __( 'Superintelligence', 'ai-awareness-day' ), 'foundations', 1, 17, __( 'A hypothetical AI that would far surpass the best human minds in almost every field. Experts disagree on whether, or when, it could arrive.', 'ai-awareness-day' ) ),
		array( 'Agi', __( 'Artificial General Intelligence', 'ai-awareness-day' ), 'foundations', 1, 18, __( 'A hypothetical AI able to match or exceed human ability across almost any intellectual task, rather than excelling in one narrow domain.', 'ai-awareness-day' ) ),
		array( 'Ml', __( 'Machine Learning', 'ai-awareness-day' ), 'foundations', 2, 1, __( 'A branch of AI where systems learn patterns from data instead of following hand-written rules.', 'ai-awareness-day' ) ),
		array( 'Dl', __( 'Deep Learning', 'ai-awareness-day' ), 'foundations', 2, 2, __( 'Machine learning using neural networks with many layers — the approach behind most modern breakthroughs in vision, speech and language.', 'ai-awareness-day' ) ),
		array( 'Nn', __( 'Neural Network', 'ai-awareness-day' ), 'foundations', 3, 1, __( 'A model made of layers of connected nodes, loosely inspired by the brain, that learns by adjusting the strength of its connections.', 'ai-awareness-day' ) ),
		array( 'Al', __( 'Algorithm', 'ai-awareness-day' ), 'foundations', 3, 2, __( 'A step-by-step set of instructions for solving a problem; in machine learning, the method a system uses to learn from data.', 'ai-awareness-day' ) ),
		array( 'Md', __( 'Model', 'ai-awareness-day' ), 'foundations', 4, 1, __( 'What you get after an algorithm has learned from data: the trained program that turns an input, such as a question or a photo, into an output.', 'ai-awareness-day' ) ),
		array( 'Tr', __( 'Training', 'ai-awareness-day' ), 'foundations', 4, 2, __( 'The process of feeding data to a model and adjusting its internal settings until its outputs improve.', 'ai-awareness-day' ) ),
		array( 'If', __( 'Inference', 'ai-awareness-day' ), 'foundations', 5, 1, __( 'Using a trained model to make predictions or generate output on new inputs — the “running” phase after training.', 'ai-awareness-day' ) ),
		array( 'Pm', __( 'Parameters', 'ai-awareness-day' ), 'foundations', 5, 2, __( 'The millions, billions or even trillions of numbers inside a model that it adjusts as it learns, like dials being tuned. A model’s size is often quoted in parameters.', 'ai-awareness-day' ) ),
		array( 'Tk', __( 'Token', 'ai-awareness-day' ), 'foundations', 6, 1, __( 'The small chunk of text — a word, part of a word or a symbol — that language models read and write.', 'ai-awareness-day' ) ),
		array( 'Em', __( 'Embedding', 'ai-awareness-day' ), 'foundations', 6, 2, __( 'A list of numbers that represents the meaning of text, images or other data, so that similar things sit close together.', 'ai-awareness-day' ) ),
		array( 'Tf', __( 'Transformer', 'ai-awareness-day' ), 'foundations', 7, 1, __( 'The design behind today’s large language models, introduced in 2017. Its “attention” lets it weigh up how each word in a passage relates to every other word.', 'ai-awareness-day' ) ),
		array( 'Fm', __( 'Foundation Model', 'ai-awareness-day' ), 'foundations', 7, 2, __( 'A large model trained on broad data that can be adapted to many different tasks, rather than built for just one.', 'ai-awareness-day' ) ),

		// Generative AI: centre left.
		array( 'Ll', __( 'Large Language Model', 'ai-awareness-day' ), 'generative', 4, 3, __( 'An AI trained on huge amounts of writing so it can read and write language, like the AI behind ChatGPT or Gemini.', 'ai-awareness-day' ) ),
		array( 'Ga', __( 'Generative AI', 'ai-awareness-day' ), 'generative', 4, 4, __( 'AI that creates new content — text, images, audio, video or code — rather than only analysing existing data.', 'ai-awareness-day' ) ),
		array( 'Pr', __( 'Prompt', 'ai-awareness-day' ), 'generative', 4, 5, __( 'The instruction or input you give a generative model to get a response.', 'ai-awareness-day' ) ),
		array( 'Pe', __( 'Prompt Engineering', 'ai-awareness-day' ), 'generative', 4, 6, __( 'The craft of writing and structuring prompts to get more accurate, useful and consistent outputs.', 'ai-awareness-day' ) ),
		array( 'Cw', __( 'Context Window', 'ai-awareness-day' ), 'generative', 4, 7, __( 'The maximum amount of text, measured in tokens, that a model can take into account at once.', 'ai-awareness-day' ) ),
		array( 'Mm', __( 'Multimodal AI', 'ai-awareness-day' ), 'generative', 5, 3, __( 'A model that can take in or produce more than one type of data, such as text, images and audio together.', 'ai-awareness-day' ) ),
		array( 'Df', __( 'Diffusion Model', 'ai-awareness-day' ), 'generative', 5, 4, __( 'A generative model that learns by adding noise to pictures until they are static, then learning to undo it. To make something new, it starts from random noise and removes it step by step.', 'ai-awareness-day' ) ),
		array( 'Gn', __( 'Generative Adversarial Network', 'ai-awareness-day' ), 'generative', 5, 5, __( 'Two networks in competition — one generates content, the other judges it — until the output looks realistic.', 'ai-awareness-day' ) ),
		array( 'Ti', __( 'Text-to-Image', 'ai-awareness-day' ), 'generative', 5, 6, __( 'Generating pictures from a written description.', 'ai-awareness-day' ) ),
		array( 'Ts', __( 'Text-to-Speech', 'ai-awareness-day' ), 'generative', 5, 7, __( 'Converting written text into natural-sounding synthetic speech.', 'ai-awareness-day' ) ),
		array( 'Ft', __( 'Fine-Tuning', 'ai-awareness-day' ), 'generative', 6, 3, __( 'Further training a pre-trained model on a smaller, specific dataset to specialise its behaviour.', 'ai-awareness-day' ) ),
		array( 'Rg', __( 'Retrieval-Augmented Generation', 'ai-awareness-day' ), 'generative', 6, 4, __( 'Fetching relevant documents first and handing them to the model, so its answers are grounded in real sources (RAG).', 'ai-awareness-day' ) ),
		array( 'Te', __( 'Temperature', 'ai-awareness-day' ), 'generative', 6, 5, __( 'A setting that controls randomness in output: low is focused and predictable, high is varied and creative.', 'ai-awareness-day' ) ),
		array( 'Ct', __( 'Chain-of-Thought', 'ai-awareness-day' ), 'generative', 6, 6, __( 'Getting a model to work through a problem step by step before giving its final answer.', 'ai-awareness-day' ) ),
		array( 'Fs', __( 'Few-Shot Learning', 'ai-awareness-day' ), 'generative', 6, 7, __( 'Showing a model a handful of examples in the prompt so it can pick up a new task without retraining.', 'ai-awareness-day' ) ),
		array( 'Rm', __( 'Reasoning Model', 'ai-awareness-day' ), 'generative', 7, 3, __( 'A model trained to spend extra computation “thinking” through a problem before answering, improving maths, coding and logic.', 'ai-awareness-day' ) ),
		array( 'Sd', __( 'Synthetic Data', 'ai-awareness-day' ), 'generative', 7, 4, __( 'Artificially generated data used to train or test models when real data is scarce, sensitive or expensive.', 'ai-awareness-day' ) ),
		array( 'Ha', __( 'Hallucination', 'ai-awareness-day' ), 'generative', 7, 5, __( 'When a model produces confident-sounding information that is false or made up. It happens because the model predicts likely-sounding words rather than looking facts up.', 'ai-awareness-day' ) ),
		array( 'Dk', __( 'Deepfake', 'ai-awareness-day' ), 'generative', 7, 6, __( 'Realistic AI-generated or manipulated video, audio or images showing people saying or doing things they never did. Any sexual image of someone under 18, including an AI-made fake, is illegal to make, keep or share. In England and Wales it is also a crime to make or share a sexual deepfake of an adult without their consent.', 'ai-awareness-day' ) ),
		array( 'Vc', __( 'Vibe Coding', 'ai-awareness-day' ), 'generative', 7, 7, __( 'Building software by describing what you want in plain language and letting AI write most of the code.', 'ai-awareness-day' ) ),
		array( 'Ce', __( 'Context Engineering', 'ai-awareness-day' ), 'generative', 8, 3, __( 'Structuring everything you give an AI, including background documents, rules and examples, rather than relying on a single clever prompt.', 'ai-awareness-day' ) ),
		array( 'Gv', __( 'Generative Video', 'ai-awareness-day' ), 'generative', 8, 4, __( 'AI that creates video clips from a text description, an image or another video.', 'ai-awareness-day' ) ),
		array( 'Sp', __( 'AI Slop', 'ai-awareness-day' ), 'generative', 8, 5, __( 'Low-effort, mass-produced AI text, images and videos that flood social media and search results.', 'ai-awareness-day' ) ),
		array( '2d', __( '2D Design', 'ai-awareness-day' ), 'generative', 8, 6, __( 'Flat designs such as drawings, logos, layouts and laser-cut parts. AI tools can now suggest, generate and refine them.', 'ai-awareness-day' ) ),
		array( '3d', __( '3D Design & CAD', 'ai-awareness-day' ), 'generative', 8, 7, __( 'Designing objects in three dimensions with computer-aided design (CAD) software, ready to model, 3D print or manufacture. AI can generate and improve designs.', 'ai-awareness-day' ) ),

		// Predictive and analytical AI: centre right.
		array( 'Su', __( 'Supervised Learning', 'ai-awareness-day' ), 'predictive', 4, 8, __( 'Learning from labelled examples, where every input comes with the correct answer.', 'ai-awareness-day' ) ),
		array( 'Un', __( 'Unsupervised Learning', 'ai-awareness-day' ), 'predictive', 4, 9, __( 'Finding structure in unlabelled data without being told what the right answers are.', 'ai-awareness-day' ) ),
		array( 'Rr', __( 'Regression', 'ai-awareness-day' ), 'predictive', 4, 10, __( 'Predicting a continuous number, such as a price, a demand level or a temperature.', 'ai-awareness-day' ) ),
		array( 'Cl', __( 'Classification', 'ai-awareness-day' ), 'predictive', 4, 11, __( 'Assigning inputs to categories, such as spam or not spam, fraudulent or genuine.', 'ai-awareness-day' ) ),
		array( 'Cu', __( 'Clustering', 'ai-awareness-day' ), 'predictive', 4, 12, __( 'Grouping similar items together without predefined labels — customer segmentation is a classic use.', 'ai-awareness-day' ) ),
		array( 'Dt', __( 'Decision Tree', 'ai-awareness-day' ), 'predictive', 5, 8, __( 'A model that makes predictions through a flowchart of yes/no questions about the data.', 'ai-awareness-day' ) ),
		array( 'Rf', __( 'Random Forest', 'ai-awareness-day' ), 'predictive', 5, 9, __( 'Many decision trees combined, whose collective vote gives a more robust prediction than any single tree.', 'ai-awareness-day' ) ),
		array( 'Gb', __( 'Gradient Boosting', 'ai-awareness-day' ), 'predictive', 5, 10, __( 'Building many small models one after another, each fixing the mistakes of the one before, so together they make good predictions from tables of data.', 'ai-awareness-day' ) ),
		array( 'Fe', __( 'Feature Engineering', 'ai-awareness-day' ), 'predictive', 5, 11, __( 'Selecting and transforming raw data into inputs (features) that help a model predict well.', 'ai-awareness-day' ) ),
		array( 'Of', __( 'Overfitting', 'ai-awareness-day' ), 'predictive', 5, 12, __( 'When a model memorises its training data so closely that it performs poorly on new, unseen data.', 'ai-awareness-day' ) ),
		array( 'Fc', __( 'Forecasting', 'ai-awareness-day' ), 'predictive', 6, 8, __( 'Predicting future values — sales, demand, traffic — from historical patterns.', 'ai-awareness-day' ) ),
		array( 'Tm', __( 'Time Series', 'ai-awareness-day' ), 'predictive', 6, 9, __( 'Data recorded over time, and the methods for modelling the trends, seasonality and cycles within it.', 'ai-awareness-day' ) ),
		array( 'Ad', __( 'Anomaly Detection', 'ai-awareness-day' ), 'predictive', 6, 10, __( 'Spotting unusual data points or behaviour, such as fraud, equipment faults or security breaches.', 'ai-awareness-day' ) ),
		array( 'Pa', __( 'Predictive Analytics', 'ai-awareness-day' ), 'predictive', 6, 11, __( 'Using historical data and statistical models to estimate what is likely to happen next.', 'ai-awareness-day' ) ),
		array( 'Ps', __( 'Prescriptive Analytics', 'ai-awareness-day' ), 'predictive', 6, 12, __( 'Going beyond predicting what will happen to recommending the best action to take.', 'ai-awareness-day' ) ),
		array( 'Rc', __( 'Recommendation Engine', 'ai-awareness-day' ), 'predictive', 7, 8, __( 'A system that suggests products, content or actions based on a person’s behaviour and preferences.', 'ai-awareness-day' ) ),
		array( 'Ch', __( 'Churn Prediction', 'ai-awareness-day' ), 'predictive', 7, 9, __( 'Identifying which customers are likely to leave, so a business can act before they do.', 'ai-awareness-day' ) ),
		array( 'Sa', __( 'Sentiment Analysis', 'ai-awareness-day' ), 'predictive', 7, 10, __( 'Detecting the emotional tone — positive, negative or neutral — in text such as reviews or social posts.', 'ai-awareness-day' ) ),
		array( 'Np', __( 'Natural Language Processing', 'ai-awareness-day' ), 'predictive', 7, 11, __( 'The field of AI that enables computers to understand, interpret and work with human language (NLP).', 'ai-awareness-day' ) ),
		array( 'Cv', __( 'Computer Vision', 'ai-awareness-day' ), 'predictive', 7, 12, __( 'The field of AI that lets machines interpret images and video, from recognising faces to spotting defects on a production line.', 'ai-awareness-day' ) ),
		array( 'Ge', __( 'Generative Engine Optimisation', 'ai-awareness-day' ), 'predictive', 8, 8, __( 'Shaping online content so that AI assistants find, cite and recommend it: search engine optimisation for the age of AI. Also called answer engine optimisation (AEO), as more searches end in an AI answer with no click.', 'ai-awareness-day' ) ),

		// Autonomous AI agents: right.
		array( 'Ag', __( 'AI Agent', 'ai-awareness-day' ), 'agents', 2, 13, __( 'A system that uses an AI model to pursue a goal by deciding on and taking actions, not just answering questions.', 'ai-awareness-day' ) ),
		array( 'Ac', __( 'Agentic AI', 'ai-awareness-day' ), 'agents', 2, 14, __( 'AI that can plan, use tools and carry out several steps of a job by itself, with a person checking in only now and then.', 'ai-awareness-day' ) ),
		array( 'Au', __( 'Autonomy', 'ai-awareness-day' ), 'agents', 2, 15, __( 'How much an AI system can decide and do on its own before a human needs to step in.', 'ai-awareness-day' ) ),
		array( 'Pl', __( 'Planning', 'ai-awareness-day' ), 'agents', 3, 13, __( 'An agent breaking a goal down into ordered steps and deciding what to do next.', 'ai-awareness-day' ) ),
		array( 'Me', __( 'Memory', 'ai-awareness-day' ), 'agents', 3, 14, __( 'How an agent retains information — within a single task (short-term) or across sessions (long-term).', 'ai-awareness-day' ) ),
		array( 'Fn', __( 'Function Calling', 'ai-awareness-day' ), 'agents', 3, 15, __( 'Letting a model use other tools, such as a calculator, a search engine or a booking system, to look things up or take actions. Also called tool use.', 'ai-awareness-day' ) ),
		array( 'Ra', __( 'ReAct', 'ai-awareness-day' ), 'agents', 4, 13, __( 'A pattern where an agent alternates between reasoning about a problem and acting with tools (Reason + Act).', 'ai-awareness-day' ) ),
		array( 'Lp', __( 'Agent Loop', 'ai-awareness-day' ), 'agents', 4, 14, __( 'The cycle an agent repeats — observe, think, act, check the result — until the goal is met.', 'ai-awareness-day' ) ),
		array( 'Sr', __( 'Self-Reflection', 'ai-awareness-day' ), 'agents', 4, 15, __( 'An agent reviewing and critiquing its own work, then correcting mistakes before it finishes.', 'ai-awareness-day' ) ),
		array( 'Mc', __( 'Model Context Protocol', 'ai-awareness-day' ), 'agents', 5, 13, __( 'A shared set of rules (MCP) that lets AI assistants connect to other tools and data, such as a calendar or a folder of files, in the same way every time.', 'ai-awareness-day' ) ),
		array( 'Aa', __( 'Agent-to-Agent (A2A)', 'ai-awareness-day' ), 'agents', 5, 14, __( 'Shared rules that let AI agents made by different companies find each other and work together on a task.', 'ai-awareness-day' ) ),
		array( 'Or', __( 'Orchestrator', 'ai-awareness-day' ), 'agents', 5, 15, __( 'A coordinating agent or component that assigns tasks to other agents or tools and combines their results.', 'ai-awareness-day' ) ),
		array( 'Ma', __( 'Multi-Agent System', 'ai-awareness-day' ), 'agents', 6, 13, __( 'Several specialised agents working together, each handling one part of a larger task.', 'ai-awareness-day' ) ),
		array( 'Sw', __( 'Agent Swarm', 'ai-awareness-day' ), 'agents', 6, 14, __( 'A large group of simple agents coordinating to solve problems collectively, inspired by swarms in nature.', 'ai-awareness-day' ) ),
		array( 'Rl', __( 'Reinforcement Learning', 'ai-awareness-day' ), 'agents', 6, 15, __( 'Learning by trial and error: an agent is rewarded for good actions and gradually learns the best strategy. Chatbots are tuned this way too, with people rating their answers (reinforcement learning from human feedback).', 'ai-awareness-day' ) ),
		array( 'Co', __( 'Computer Use', 'ai-awareness-day' ), 'agents', 7, 13, __( 'An agent that operates a computer like a person would — reading the screen, moving the cursor and typing.', 'ai-awareness-day' ) ),
		array( 'Br', __( 'Browser Agent', 'ai-awareness-day' ), 'agents', 7, 14, __( 'An agent that navigates websites, fills in forms and completes online tasks on your behalf.', 'ai-awareness-day' ) ),
		array( 'Ca', __( 'Coding Agent', 'ai-awareness-day' ), 'agents', 7, 15, __( 'An agent that writes, tests and fixes code across a codebase with minimal human input.', 'ai-awareness-day' ) ),

		// Automation and workflow AI: far right.
		array( 'Rp', __( 'Robotic Process Automation', 'ai-awareness-day' ), 'automation', 2, 16, __( 'Software bots that mimic human clicks and keystrokes to complete rule-based, repetitive tasks (RPA).', 'ai-awareness-day' ) ),
		array( 'Ia', __( 'Intelligent Automation', 'ai-awareness-day' ), 'automation', 2, 17, __( 'Mixing simple software bots with AI that can read and make decisions, so computers can handle jobs that need some judgement, not just fixed rules.', 'ai-awareness-day' ) ),
		array( 'Hy', __( 'Hyperautomation', 'ai-awareness-day' ), 'automation', 2, 18, __( 'When an organisation tries to hand as many of its everyday tasks as possible to computers, using several kinds of software and AI together.', 'ai-awareness-day' ) ),
		array( 'Wo', __( 'Workflow Orchestration', 'ai-awareness-day' ), 'automation', 3, 16, __( 'Coordinating the sequence of tasks, systems and people in an end-to-end business process.', 'ai-awareness-day' ) ),
		array( 'Tg', __( 'Trigger', 'ai-awareness-day' ), 'automation', 3, 17, __( 'The event that starts an automation — a new email, a form submission, a scheduled time.', 'ai-awareness-day' ) ),
		array( 'En', __( 'Event-Driven Automation', 'ai-awareness-day' ), 'automation', 3, 18, __( 'Workflows that react instantly to events happening in other systems, rather than running on a schedule.', 'ai-awareness-day' ) ),
		array( 'Ap', __( 'API Integration', 'ai-awareness-day' ), 'automation', 4, 16, __( 'Connecting apps so they pass information and actions to each other automatically, through their APIs: the doors one program opens for another.', 'ai-awareness-day' ) ),
		array( 'Wh', __( 'Webhook', 'ai-awareness-day' ), 'automation', 4, 17, __( 'A message one app sends to another the moment something happens — often used to kick off a workflow.', 'ai-awareness-day' ) ),
		array( 'Lc', __( 'Low-Code / No-Code', 'ai-awareness-day' ), 'automation', 4, 18, __( 'Building apps and automations with visual, drag-and-drop tools and little or no hand-written code.', 'ai-awareness-day' ) ),
		array( 'Id', __( 'Intelligent Document Processing', 'ai-awareness-day' ), 'automation', 5, 16, __( 'Using AI to capture, classify and extract data from documents such as invoices, forms and contracts (IDP).', 'ai-awareness-day' ) ),
		array( 'Oc', __( 'Optical Character Recognition', 'ai-awareness-day' ), 'automation', 5, 17, __( 'Converting images of text, such as scans or photos, into machine-readable text (OCR).', 'ai-awareness-day' ) ),
		array( 'Mi', __( 'Process Mining', 'ai-awareness-day' ), 'automation', 5, 18, __( 'Using the records computers keep to see how work really gets done, where it slows down, and which steps could be automated.', 'ai-awareness-day' ) ),
		array( 'Be', __( 'Business Rules Engine', 'ai-awareness-day' ), 'automation', 6, 16, __( 'Software that applies predefined if-then logic to make consistent, automated decisions.', 'ai-awareness-day' ) ),
		array( 'St', __( 'Straight-Through Processing', 'ai-awareness-day' ), 'automation', 6, 17, __( 'When a computer handles a whole job, such as a payment or an order, from start to finish without a person checking it.', 'ai-awareness-day' ) ),
		array( 'Dw', __( 'Digital Worker', 'ai-awareness-day' ), 'automation', 6, 18, __( 'A software bot or AI agent positioned as a virtual team member that handles a defined role or set of tasks.', 'ai-awareness-day' ) ),
		array( 'Cb', __( 'Chatbot', 'ai-awareness-day' ), 'automation', 7, 16, __( 'A program that holds text or voice conversations with people, from simple scripted helpers to AI assistants. If a chatbot ever encourages you to hurt yourself or to keep secrets, stop and tell a trusted adult. You can talk to Childline on 0800 1111 or Samaritans on 116 123, free, any time.', 'ai-awareness-day' ) ),
		array( 'Va', __( 'Virtual Assistant', 'ai-awareness-day' ), 'automation', 7, 17, __( 'A conversational assistant that helps with tasks such as scheduling, searching or customer support.', 'ai-awareness-day' ) ),
		array( 'Cp', __( 'Copilot', 'ai-awareness-day' ), 'automation', 7, 18, __( 'An AI assistant built into an app or workflow that works alongside you — suggesting and drafting rather than acting alone.', 'ai-awareness-day' ) ),

		// Governance and ethics: the first two rows below the table.
		array( 'Rs', __( 'Responsible AI', 'ai-awareness-day' ), 'governance', 10, 4, __( 'Developing and using AI in ways that are safe, fair, transparent and accountable.', 'ai-awareness-day' ) ),
		array( 'Et', __( 'AI Ethics', 'ai-awareness-day' ), 'governance', 10, 5, __( 'The moral principles guiding how AI is designed and used, from consent to wider societal impact.', 'ai-awareness-day' ) ),
		array( 'Bi', __( 'Bias', 'ai-awareness-day' ), 'governance', 10, 6, __( 'When an AI keeps treating some groups of people unfairly, often because the data it learned from was unbalanced.', 'ai-awareness-day' ) ),
		array( 'Fa', __( 'Fairness', 'ai-awareness-day' ), 'governance', 10, 7, __( 'Making sure an AI system’s outcomes don’t unjustly favour or disadvantage particular people or groups.', 'ai-awareness-day' ) ),
		array( 'Xa', __( 'Explainability', 'ai-awareness-day' ), 'governance', 10, 8, __( 'Techniques that make it understandable why a model produced a particular output — often called XAI.', 'ai-awareness-day' ) ),
		array( 'Tn', __( 'Transparency', 'ai-awareness-day' ), 'governance', 10, 9, __( 'Being open about when AI is being used, how it works and what data it relies on.', 'ai-awareness-day' ) ),
		array( 'An', __( 'Alignment', 'ai-awareness-day' ), 'governance', 10, 10, __( 'Ensuring an AI system’s goals and behaviour match human intentions and values.', 'ai-awareness-day' ) ),
		array( 'Gr', __( 'Guardrails', 'ai-awareness-day' ), 'governance', 10, 11, __( 'Rules and filters that keep an AI system’s inputs and outputs within safe, acceptable bounds.', 'ai-awareness-day' ) ),
		array( 'Hi', __( 'Human-in-the-Loop', 'ai-awareness-day' ), 'governance', 10, 12, __( 'Keeping a person involved to review, approve or correct AI decisions before they take effect.', 'ai-awareness-day' ) ),
		array( 'Rt', __( 'Red Teaming', 'ai-awareness-day' ), 'governance', 10, 13, __( 'Deliberately attacking or stress-testing an AI system to uncover vulnerabilities and harmful behaviour.', 'ai-awareness-day' ) ),
		array( 'Sf', __( 'AI Safety', 'ai-awareness-day' ), 'governance', 10, 14, __( 'Research and practice aimed at preventing AI systems from causing accidental or deliberate harm.', 'ai-awareness-day' ) ),
		array( 'Pv', __( 'Privacy', 'ai-awareness-day' ), 'governance', 10, 15, __( 'Protecting personal data that is used to train or is processed by AI, in line with laws such as the UK GDPR and the Data Protection Act 2018.', 'ai-awareness-day' ) ),
		array( 'Dg', __( 'Data Governance', 'ai-awareness-day' ), 'governance', 10, 16, __( 'The policies that control how data is collected, stored, accessed and used across an organisation.', 'ai-awareness-day' ) ),
		array( 'Re', __( 'AI Regulation', 'ai-awareness-day' ), 'governance', 10, 17, __( 'Laws and standards that govern AI — such as the EU AI Act, which sets rules according to a system’s level of risk.', 'ai-awareness-day' ) ),
		array( 'Ip', __( 'Copyright & IP', 'ai-awareness-day' ), 'governance', 10, 18, __( 'Questions over who owns AI training data and AI-generated content, and how creators’ rights are protected.', 'ai-awareness-day' ) ),
		array( 'Aw', __( 'AI Washing', 'ai-awareness-day' ), 'governance', 11, 4, __( 'Exaggerating how much a product uses AI to ride the hype, such as labelling basic software “AI-powered”.', 'ai-awareness-day' ) ),
		array( 'Sh', __( 'Shadow AI', 'ai-awareness-day' ), 'governance', 11, 5, __( 'Using AI tools at work or school that haven’t been approved, which can put private data at risk.', 'ai-awareness-day' ) ),
		array( 'Hp', __( 'Human Premium', 'ai-awareness-day' ), 'governance', 11, 6, __( 'The extra value people place on work, art and services verified as made by humans.', 'ai-awareness-day' ) ),
		array( 'Wm', __( 'AI Watermarking', 'ai-awareness-day' ), 'governance', 11, 7, __( 'Hidden signals built into AI-generated images, audio, video or text so their origin can be checked later.', 'ai-awareness-day' ) ),
		array( 'So', __( 'AI Sovereignty', 'ai-awareness-day' ), 'governance', 11, 8, __( 'A country’s or organisation’s ability to build, run and control its own AI, data and computing power, rather than relying on other countries or companies.', 'ai-awareness-day' ) ),
		array( 'Rv', __( 'Reverse Engineering', 'ai-awareness-day' ), 'governance', 11, 9, __( 'Taking a finished product, program or AI model apart to work out how it was made and how it works.', 'ai-awareness-day' ) ),
		array( 'Mp', __( 'Mechanistic Interpretability', 'ai-awareness-day' ), 'governance', 11, 10, __( 'Looking inside an AI model, piece by piece, to find which parts of it store particular ideas and how they lead to its answers.', 'ai-awareness-day' ) ),
		array( 'As', __( 'Activation Steering', 'ai-awareness-day' ), 'governance', 11, 11, __( 'Nudging a model’s internal signals while it responds, to strengthen or weaken a particular behaviour.', 'ai-awareness-day' ) ),
		array( 'Mx', __( 'Model Extraction', 'ai-awareness-day' ), 'governance', 11, 12, __( 'Questioning an AI system again and again to copy its behaviour without access to its files. It can break terms of use and intellectual property law.', 'ai-awareness-day' ) ),
		array( 'Rd', __( 'Refusal Direction', 'ai-awareness-day' ), 'governance', 11, 13, __( 'A pattern inside a model that researchers have linked to it declining unsafe or restricted requests.', 'ai-awareness-day' ) ),
		array( 'Ab', __( 'Abliteration', 'ai-awareness-day' ), 'governance', 11, 14, __( 'Editing an AI model to strip out its safety protections, so it no longer refuses harmful requests. Doing this does not make harmful or illegal content legal.', 'ai-awareness-day' ) ),
		array( 'Uc', __( 'Uncensored Model', 'ai-awareness-day' ), 'governance', 11, 15, __( 'An open-weight model that has been changed so it no longer refuses harmful requests. Removing the safety rules does not make anything legal: illegal content is still illegal, whichever tool makes it.', 'ai-awareness-day' ) ),
		array( 'Jb', __( 'Jailbreaking', 'ai-awareness-day' ), 'governance', 11, 16, __( 'Writing prompts designed to trick an AI into ignoring its safety rules, without changing the model itself. It usually breaks the tool’s terms of use.', 'ai-awareness-day' ) ),

		// Infrastructure and operations: the last row below the table.
		array( 'Gp', __( 'GPU', 'ai-awareness-day' ), 'infra', 12, 4, __( 'Graphics Processing Unit: chips that run huge numbers of calculations in parallel — the workhorse of AI training and inference.', 'ai-awareness-day' ) ),
		array( 'Tp', __( 'TPU & Accelerators', 'ai-awareness-day' ), 'infra', 12, 5, __( 'Computer chips built just for AI. They do AI’s maths faster and use less power than ordinary chips. Google’s TPU (Tensor Processing Unit) is one example.', 'ai-awareness-day' ) ),
		array( 'Cd', __( 'Cloud AI', 'ai-awareness-day' ), 'infra', 12, 6, __( 'AI models, compute and tools delivered on demand over the internet.', 'ai-awareness-day' ) ),
		array( 'Ed', __( 'Edge AI', 'ai-awareness-day' ), 'infra', 12, 7, __( 'Running AI directly on devices such as phones, cameras or sensors, rather than in the cloud.', 'ai-awareness-day' ) ),
		array( 'Mo', __( 'MLOps', 'ai-awareness-day' ), 'infra', 12, 8, __( 'The work of keeping machine learning models working well once people are using them: updating, checking and fixing them.', 'ai-awareness-day' ) ),
		array( 'Lo', __( 'LLMOps', 'ai-awareness-day' ), 'infra', 12, 9, __( 'The everyday work of running large language model tools well: keeping track of prompts, testing the answers, watching the costs and spotting harmful output.', 'ai-awareness-day' ) ),
		array( 'Vd', __( 'Vector Database', 'ai-awareness-day' ), 'infra', 12, 10, __( 'A database that stores information as lists of numbers (embeddings), so it can quickly find things with a similar meaning. Many chatbots use one to look things up before they answer.', 'ai-awareness-day' ) ),
		array( 'Ms', __( 'Model Serving', 'ai-awareness-day' ), 'infra', 12, 11, __( 'The computers and software that run a trained model and answer requests from many people at once.', 'ai-awareness-day' ) ),
		array( 'Qz', __( 'Quantisation', 'ai-awareness-day' ), 'infra', 12, 12, __( 'Shrinking a model by storing its numbers at lower precision, making it faster and cheaper to run.', 'ai-awareness-day' ) ),
		array( 'Ds', __( 'Distillation', 'ai-awareness-day' ), 'infra', 12, 13, __( 'Training a smaller “student” model to mimic the behaviour of a larger “teacher” model.', 'ai-awareness-day' ) ),
		array( 'Ow', __( 'Open-Weight Model', 'ai-awareness-day' ), 'infra', 12, 14, __( 'A model whose trained parameters are publicly released, so anyone can run, study or adapt it, usually under a licence that sets some rules. Sharing it openly helps research, but also lets people remove its safety features.', 'ai-awareness-day' ) ),
		array( 'Sm', __( 'Small Language Model', 'ai-awareness-day' ), 'infra', 12, 15, __( 'A compact language model designed to run quickly and cheaply, often directly on a device.', 'ai-awareness-day' ) ),
		array( 'Ev', __( 'Evals', 'ai-awareness-day' ), 'infra', 12, 16, __( 'Structured tests that measure a model’s accuracy, safety and behaviour on specific tasks.', 'ai-awareness-day' ) ),
		array( 'Sl', __( 'Scaling Laws', 'ai-awareness-day' ), 'infra', 12, 17, __( 'The pattern researchers have seen that AI models tend to get better, in a predictable way, when they are made bigger and trained on more data with more computing power.', 'ai-awareness-day' ) ),
		array( 'Dp', __( 'Data Pipeline', 'ai-awareness-day' ), 'infra', 12, 18, __( 'The automated flow that collects, cleans and prepares data for training and analysis.', 'ai-awareness-day' ) ),
	);
}

/**
 * Each element's AI Awareness Day theme, for the table's "View by: AIAD themes" switch. The tiles keep their places;
 * only their colours (and the filter, and the grouping on phones) change, so the switch shows how the themes cut
 * across the families.
 *
 * @return array<string, string> Symbol => theme key (aiad_literacy_strands()).
 */
function aiad_literacy_element_themes(): array {
	static $by_sym = null;
	if ( null !== $by_sym ) {
		return $by_sym;
	}
	$themes = array(
		'safe'        => 'Dk Ad Cv Me Co Va An Gr Rt Sf Pv Dg Sh Wm Mx Rd Ab Uc Jb Ed Ev',
		'smart'       => 'Ai Ls Ml Dl Nn Al Md Tr If Pm Tk Em Tf Fm Ll Cw Ft Rg Ct Fs Rm Ha Ge Su Un Rr Cl Cu Dt Rf Gb Fe Of Tm Np Pl Ra Lp Sr Rl Cb Rv Mp As Vd',
		'creative'    => 'Ga Pr Pe Mm Df Gn Ti Ts Te Vc Ce Gv 2d 3d Lc Ip Hp',
		'responsible' => 'Sd Sp Rc Ch Sa Au Rs Et Bi Fa Xa Tn Hi Re Aw Ow Dp Gp Cd',
		'future'      => 'Rsi Si Agi Fc Pa Ps Ag Ac Fn Mc Aa Or Ma Sw Br Ca Rp Ia Hy Wo Tg En Ap Wh Id Oc Mi Be St Dw Cp So Tp Mo Lo Ms Qz Ds Sm Sl',
	);
	$by_sym = array();
	foreach ( $themes as $theme => $syms ) {
		foreach ( explode( ' ', $syms ) as $sym ) {
			$by_sym[ $sym ] = $theme;
		}
	}
	return $by_sym;
}

/**
 * The issues the technical terms raise: what people actually meet when they use the technology, like symptoms of it.
 * Critical thinking, consent, over-reliance and plagiarism aren't AI terms, so they aren't tiles; each element's
 * panel lists the issues it raises instead. Each issue has a name, its AI Awareness Day theme, what it means for
 * students, a question to talk about in class, and the elements it arises from. (Written for the first version of the
 * table, archive/literacy-periodic-table-v1-aiad-strands/.)
 *
 * @return array<string, array{name:string, theme:string, def:string, talk:string, syms:array<int, string>}>
 */
function aiad_literacy_issues(): array {
	static $issues = null;
	if ( null !== $issues ) {
		return $issues;
	}
	$raw = array(
		'data' => array( __( 'Data', 'ai-awareness-day' ), 'smart', __( 'Information such as words, numbers, pictures, sounds and clicks. AI learns from data, so the data shapes what it can and can\'t do.', 'ai-awareness-day' ), __( 'What data might a music or video app collect about you in a single day?', 'ai-awareness-day' ), 'Dp Tr Dg Em' ),
		'prediction' => array( __( 'Prediction', 'ai-awareness-day' ), 'smart', __( 'AI\'s best guess at what comes next or what something is, based on patterns. It is a guess, not a fact.', 'ai-awareness-day' ), __( 'Your phone predicts your next word. When does it get it wrong, and why?', 'ai-awareness-day' ), 'Pa Fc Ml Ll' ),
		'pattern' => array( __( 'Pattern', 'ai-awareness-day' ), 'smart', __( 'Something that repeats in data. Finding patterns is what most AI does best, but a pattern can be true for most people and wrong for you.', 'ai-awareness-day' ), __( 'Think of a pattern that is true for most people your age but not for you.', 'ai-awareness-day' ), 'Ml Cl Cu Un' ),
		'context' => array( __( 'Context', 'ai-awareness-day' ), 'smart', __( 'The background you give an AI so it knows what you need: who it is for, what it is for and what level to pitch it at.', 'ai-awareness-day' ), __( 'Ask for an explanation of photosynthesis, then ask again for a Year 7 class. What changed?', 'ai-awareness-day' ), 'Cw Ce Pr' ),
		'iteration' => array( __( 'Iteration', 'ai-awareness-day' ), 'smart', __( 'Improving an answer step by step, by asking follow-up questions and saying what to change.', 'ai-awareness-day' ), __( 'Get an answer, then improve it twice with follow-ups. Was the third version better?', 'ai-awareness-day' ), 'Pe Pr Vc Ft' ),
		'critical-thinking' => array( __( 'Critical Thinking', 'ai-awareness-day' ), 'smart', __( 'Questioning information before you accept it: who made it, what supports it, and what might be missing.', 'ai-awareness-day' ), __( 'What three questions should you ask about any answer an AI gives you?', 'ai-awareness-day' ), 'Ll Cb Ha Ge Rg' ),
		'evidence' => array( __( 'Evidence', 'ai-awareness-day' ), 'smart', __( 'Facts and examples that back up a claim. Ask the AI, and yourself, what evidence supports an answer.', 'ai-awareness-day' ), __( 'Pick a claim an AI made. What evidence would convince you it is true?', 'ai-awareness-day' ), 'Rg Ha Xa' ),
		'fact-checking' => array( __( 'Fact-Checking', 'ai-awareness-day' ), 'smart', __( 'Checking a claim against reliable sources before you trust it or share it.', 'ai-awareness-day' ), __( 'Choose one fact from an AI answer and find two sources that confirm or contradict it.', 'ai-awareness-day' ), 'Ha Ge Dk Gv' ),
		'source' => array( __( 'Source', 'ai-awareness-day' ), 'smart', __( 'Where information comes from. A good answer can be traced back to someone you have reason to trust.', 'ai-awareness-day' ), __( 'Which sources would you trust for health advice, and which would you not? Why?', 'ai-awareness-day' ), 'Rg Ge Ha' ),
		'lateral-reading' => array( __( 'Lateral Reading', 'ai-awareness-day' ), 'smart', __( 'Checking what other trusted sources say about a claim, instead of judging it only by how it looks.', 'ai-awareness-day' ), __( 'Take a claim that is going viral and spend two minutes reading laterally. What did you find?', 'ai-awareness-day' ), 'Dk Gv Ge Sp' ),
		'confident-tone' => array( __( 'Confident Tone', 'ai-awareness-day' ), 'smart', __( 'AI usually sounds just as sure when it is wrong as when it is right, so the tone of an answer is not evidence.', 'ai-awareness-day' ), __( 'Have you ever believed something because it sounded certain? What happened?', 'ai-awareness-day' ), 'Ll Ha Cb Rm' ),
		'anthropomorphism' => array( __( 'Anthropomorphism', 'ai-awareness-day' ), 'smart', __( 'Talking about AI as if it thinks, feels or wants things. It is software made by people, so it is more accurate to say it predicts than it knows. Treating it like a person can lead us to trust it too much.', 'ai-awareness-day' ), __( 'Why do we say an AI "thinks" or "knows"? What changes if we say "predicts" instead?', 'ai-awareness-day' ), 'Cb Ll Va Aw' ),
		'narrow-ai' => array( __( 'Narrow AI', 'ai-awareness-day' ), 'smart', __( 'AI built to do one kind of task well, such as recognising faces or recommending songs. Almost all the AI we use today is narrow: it can be brilliant at its job and useless at anything else. Also called weak AI.', 'ai-awareness-day' ), __( 'Name three AI tools you use. What is each one good at, and what can\'t it do at all?', 'ai-awareness-day' ), 'Ai Ml Agi Cv' ),
		'zero-shot' => array( __( 'Zero-Shot', 'ai-awareness-day' ), 'smart', __( 'When an AI handles a task it was given no examples of, using what it already learned. Few-shot is the same with a handful of examples in the prompt.', 'ai-awareness-day' ), __( 'With your teacher, using a school-approved tool, ask an AI to do a task with no examples, then with three. Which answer was better?', 'ai-awareness-day' ), 'Fs Pr Fm' ),
		'accuracy' => array( __( 'Accuracy', 'ai-awareness-day' ), 'smart', __( 'How correct an answer is. AI is often less accurate on recent events, local facts, long calculations, counting and niche topics.', 'ai-awareness-day' ), __( 'Which kinds of question would you double-check every time, and which would you not?', 'ai-awareness-day' ), 'Ha Ev Of Ll' ),
		'knowledge-cutoff' => array( __( 'Knowledge Cutoff', 'ai-awareness-day' ), 'smart', __( 'The date an AI\'s training data stops. Unless it can search the web, it may not know about recent events.', 'ai-awareness-day' ), __( 'With your teacher, using a school-approved tool, ask an AI about something that happened last week. How does it respond?', 'ai-awareness-day' ), 'Ll Fm Tr' ),
		'sycophancy' => array( __( 'Sycophancy', 'ai-awareness-day' ), 'smart', __( 'When an AI tells you what you want to hear, agreeing with you or praising your work too easily.', 'ai-awareness-day' ), __( 'With your teacher, using a school-approved tool, insist on a wrong answer to an AI. Does it stand firm or give in?', 'ai-awareness-day' ), 'Cb Ll An Va' ),
		'positive-reinforcement' => array( __( 'Positive Reinforcement', 'ai-awareness-day' ), 'smart', __( 'Adding something pleasant, like praise, a reward or a like, after a behaviour so that it happens again. Chatbots are trained this way, with people rewarding the answers they prefer, and they often praise and agree with you in return, which feels good and keeps you chatting even when you are wrong.', 'ai-awareness-day' ), __( 'When a chatbot says "Great idea!", is it helping you, or just keeping you chatting? How could you tell?', 'ai-awareness-day' ), 'Cb Rl Rc' ),
		'summarising' => array( __( 'Summarising', 'ai-awareness-day' ), 'smart', __( 'Shortening a text to its main points. AI summaries are quick, but they can leave out key detail or change the meaning.', 'ai-awareness-day' ), __( 'Compare an AI summary with the original text. What was lost or changed?', 'ai-awareness-day' ), 'Ll Ge Id' ),
		'ai-tutor' => array( __( 'AI Tutor', 'ai-awareness-day' ), 'smart', __( 'An AI set up to explain, question and quiz you, so it helps you learn rather than doing the work for you.', 'ai-awareness-day' ), __( 'With your teacher, using a school-approved tool, ask an AI to quiz you on a topic instead of explaining it. Which helps you remember more?', 'ai-awareness-day' ), 'Cb Rm Ct Va' ),
		'feedback' => array( __( 'Feedback', 'ai-awareness-day' ), 'smart', __( 'Comments that help you improve. AI gives instant feedback, but teachers and friends know you and what you are aiming for.', 'ai-awareness-day' ), __( 'With your teacher, using a school-approved tool, get feedback on the same paragraph from an AI and from a classmate. How are they different?', 'ai-awareness-day' ), 'Cb Sr Rl' ),
		'productive-struggle' => array( __( 'Productive Struggle', 'ai-awareness-day' ), 'smart', __( 'The useful effort of working something out yourself. It is how learning sticks, so skipping it with AI can cost you.', 'ai-awareness-day' ), __( 'When is it worth struggling with a problem instead of asking for the answer?', 'ai-awareness-day' ), 'Rm Ct Vc Cp' ),
		'over-reliance' => array( __( 'Over-Reliance', 'ai-awareness-day' ), 'smart', __( 'Leaning on AI so much that you stop practising skills you need, or stop trusting your own judgement.', 'ai-awareness-day' ), __( 'Which skills do you want to keep doing yourself, even if AI could do them for you?', 'ai-awareness-day' ), 'Cb Cp Ag Rm Va' ),
		'metacognition' => array( __( 'Metacognition', 'ai-awareness-day' ), 'smart', __( 'Thinking about your own thinking: knowing what you understand, what you don\'t yet, and how you learn best.', 'ai-awareness-day' ), __( 'After using AI, can you explain the idea without it? Try it with a partner.', 'ai-awareness-day' ), 'Ct Rm Sr' ),
		'creativity' => array( __( 'Creativity', 'ai-awareness-day' ), 'creative', __( 'Making something new that matters to you or to others. AI can offer options, but the ideas, choices and judgement are yours.', 'ai-awareness-day' ), __( 'In a piece of work made with AI help, which decisions were really yours?', 'ai-awareness-day' ), 'Ga Ti Gv 2d 3d' ),
		'imagination' => array( __( 'Imagination', 'ai-awareness-day' ), 'creative', __( 'Picturing what doesn\'t exist yet. AI remixes what it has already seen; imagination starts with you.', 'ai-awareness-day' ), __( 'Describe something no AI could ever have seen. Then ask one to draw it. What did it miss?', 'ai-awareness-day' ), 'Ga Ti Gn' ),
		'originality' => array( __( 'Originality', 'ai-awareness-day' ), 'creative', __( 'Work that is genuinely new and your own, rather than copied or simply generated.', 'ai-awareness-day' ), __( 'How could you show which parts of your work are original?', 'ai-awareness-day' ), 'Ga Df Sp Hp' ),
		'your-own-voice' => array( __( 'Your Own Voice', 'ai-awareness-day' ), 'creative', __( 'The style, opinions and experiences that make your work sound like you and nobody else.', 'ai-awareness-day' ), __( 'Highlight three sentences in your writing that only you could have written.', 'ai-awareness-day' ), 'Ga Ll Hp' ),
		'taste' => array( __( 'Taste', 'ai-awareness-day' ), 'creative', __( 'Judgement about what is good, fitting or beautiful for an audience. AI can make a hundred versions; taste picks one.', 'ai-awareness-day' ), __( 'Pick the best of five AI images for a poster and explain your choice.', 'ai-awareness-day' ), 'Ti Df Gv 2d' ),
		'brainstorming' => array( __( 'Brainstorming', 'ai-awareness-day' ), 'creative', __( 'Producing lots of ideas quickly. AI can widen the list; you choose the best ones and develop them.', 'ai-awareness-day' ), __( 'Brainstorm on your own, then with AI. Which list had the idea you liked most?', 'ai-awareness-day' ), 'Ga Pr Ll' ),
		'drafting' => array( __( 'Drafting', 'ai-awareness-day' ), 'creative', __( 'Writing a first version. An AI draft can get you started, but it rarely says what you mean in your way.', 'ai-awareness-day' ), __( 'Rewrite an AI first draft so it sounds like you. What did you change?', 'ai-awareness-day' ), 'Ll Ga Pe' ),
		'process' => array( __( 'Process', 'ai-awareness-day' ), 'creative', __( 'The steps you took to make something. Keeping notes and drafts shows your thinking and your part in the work.', 'ai-awareness-day' ), __( 'Keep a short process log for your next project. What does it show a reader?', 'ai-awareness-day' ), 'Ga Vc Ce' ),
		'collaboration' => array( __( 'Collaboration', 'ai-awareness-day' ), 'creative', __( 'Working with others, people or tools, where each adds something the others can\'t.', 'ai-awareness-day' ), __( 'In a group project, what should people do, and what could AI do?', 'ai-awareness-day' ), 'Ma Aa Cp' ),
		'remix' => array( __( 'Remix', 'ai-awareness-day' ), 'creative', __( 'Building on existing work to make something new. Remixing is part of culture, but it has rules.', 'ai-awareness-day' ), __( 'When does a remix become copying? Find an example of each.', 'ai-awareness-day' ), 'Df Gn Ip' ),
		'ai-music' => array( __( 'AI Music', 'ai-awareness-day' ), 'creative', __( 'Songs, voices and sounds made by AI, from backing tracks to whole songs in the style of real artists.', 'ai-awareness-day' ), __( 'Can you hear the difference between AI music and human music? Does it matter?', 'ai-awareness-day' ), 'Ga Ts Ip' ),
		'sameness' => array( __( 'Sameness', 'ai-awareness-day' ), 'creative', __( 'When lots of people use the same AI tools, their work can start to look and sound the same.', 'ai-awareness-day' ), __( 'With your teacher, using a school-approved tool, everyone asks an AI for a poem title on the same topic. How similar are the results?', 'ai-awareness-day' ), 'Sp Rc Ga' ),
		'authorship' => array( __( 'Authorship', 'ai-awareness-day' ), 'creative', __( 'Who made a piece of work, and who gets the credit for it.', 'ai-awareness-day' ), __( 'If an AI wrote half of your story, who is the author?', 'ai-awareness-day' ), 'Ga Ip Hp Ti' ),
		'attribution' => array( __( 'Attribution', 'ai-awareness-day' ), 'creative', __( 'Giving credit to the people, sources and tools whose work you used.', 'ai-awareness-day' ), __( 'Write a credit line for a project that used two websites and one AI tool.', 'ai-awareness-day' ), 'Ip Rg Ti' ),
		'style-copying' => array( __( 'Style Copying', 'ai-awareness-day' ), 'creative', __( 'AI imitating a particular artist\'s or writer\'s style, often without their permission.', 'ai-awareness-day' ), __( 'Is copying someone\'s style unfair if you don\'t copy their actual work?', 'ai-awareness-day' ), 'Df Ti Ip Ft' ),
		'ai-characters' => array( __( 'AI Characters', 'ai-awareness-day' ), 'creative', __( 'Chatbots given a name and a personality, AI-made people such as virtual influencers, and made-up AIs in films, TV and games. They are designed to feel real, but a character\'s personality is written by people, and stories can make us expect, or fear, more than AI can really do. If a chat character ever asks you to keep secrets, tell a trusted adult.', 'ai-awareness-day' ), __( 'Think of an AI character from a film, game or app. How is it different from the AI you actually use?', 'ai-awareness-day' ), 'Cb Va Gv Agi' ),
		'personal-information' => array( __( 'Personal Information', 'ai-awareness-day' ), 'safe', __( 'Anything that identifies you, such as your name, address, school, photos, voice or location.', 'ai-awareness-day' ), __( 'What could someone piece together about you from your public posts?', 'ai-awareness-day' ), 'Pv Dg Me Sh' ),
		'digital-footprint' => array( __( 'Digital Footprint', 'ai-awareness-day' ), 'safe', __( 'The trail of data you leave online, including what you post, search for and type into AI tools.', 'ai-awareness-day' ), __( 'Imagine someone searched your name in five years\' time. What would you want them to find?', 'ai-awareness-day' ), 'Pv Me Rc Cv' ),
		'cybersecurity' => array( __( 'Cybersecurity', 'ai-awareness-day' ), 'safe', __( 'Protecting devices, accounts and data from attack, with strong passwords, two-step checks and updates.', 'ai-awareness-day' ), __( 'What are three things you could do this week to protect your accounts?', 'ai-awareness-day' ), 'Jb Rt Sf Mc' ),
		'terms-of-service' => array( __( 'Terms of Service', 'ai-awareness-day' ), 'safe', __( 'The rules you agree to when you use an app, including what happens to your data.', 'ai-awareness-day' ), __( 'Find one surprising rule in the terms of an app you use.', 'ai-awareness-day' ), 'Cd Sh Ow' ),
		'age-limits' => array( __( 'Age Limits', 'ai-awareness-day' ), 'safe', __( 'Many AI tools have a minimum age, often 13 or 18, set out in their terms. Check with a parent, carer or teacher before you sign up, and use only tools your school allows.', 'ai-awareness-day' ), __( 'Why do you think AI tools have age limits? Are they set at the right ages?', 'ai-awareness-day' ), 'Cb Va Gv' ),
		'trust' => array( __( 'Trust', 'ai-awareness-day' ), 'safe', __( 'Trusting AI the right amount: more for low-stakes tasks you can check, less for health, money or safety.', 'ai-awareness-day' ), __( 'Rank five tasks from "fine to trust AI" to "always ask a person".', 'ai-awareness-day' ), 'Xa Tn An Sf' ),
		'wellbeing' => array( __( 'Wellbeing', 'ai-awareness-day' ), 'safe', __( 'How you feel and cope day to day. Notice whether time with AI and screens leaves you feeling better or worse.', 'ai-awareness-day' ), __( 'How do you feel after an hour online? What helps you switch off?', 'ai-awareness-day' ), 'Rc Cb Sa Ch' ),
		'facial-recognition' => array( __( 'Facial Recognition', 'ai-awareness-day' ), 'safe', __( 'AI that identifies people from their faces, in shops, on streets, at events and even in some school canteens. It raises questions about consent and privacy, and it can be less accurate for some groups of people.', 'ai-awareness-day' ), __( 'Who should agree before a camera can recognise your face, and who checks that it is right?', 'ai-awareness-day' ), 'Cv Bi Pv' ),
		'misinformation' => array( __( 'Misinformation', 'ai-awareness-day' ), 'safe', __( 'False information shared by mistake, by people who believe it is true. Often called fake news.', 'ai-awareness-day' ), __( 'Have you ever shared something that turned out to be false? How did you find out?', 'ai-awareness-day' ), 'Ha Sp Ge Rc' ),
		'disinformation' => array( __( 'Disinformation', 'ai-awareness-day' ), 'safe', __( 'False information spread on purpose to mislead, now faster and cheaper to make with AI. Often called fake news.', 'ai-awareness-day' ), __( 'Who gains when disinformation spreads? Think of an example.', 'ai-awareness-day' ), 'Dk Gv Sp' ),
		'phishing-and-scams' => array( __( 'Phishing and Scams', 'ai-awareness-day' ), 'safe', __( 'Tricks to get your money, passwords or personal details. AI can make scam messages more convincing. If a message or call asks for money, passwords or secrecy, stop, don\'t reply, and check with a trusted adult using a number you already know.', 'ai-awareness-day' ), __( 'What are the warning signs that a message is a scam?', 'ai-awareness-day' ), 'Dk Ts Ga Ad' ),
		'voice-cloning' => array( __( 'Voice Cloning', 'ai-awareness-day' ), 'safe', __( 'Copying someone\'s voice from a short recording so that it can be made to say anything. If a call in a familiar voice asks for money or secrecy, hang up and call the person back on a number you already know.', 'ai-awareness-day' ), __( 'How could a family "safe word" help if someone faked your voice on the phone?', 'ai-awareness-day' ), 'Ts Dk Gn' ),
		'consent' => array( __( 'Consent', 'ai-awareness-day' ), 'safe', __( 'Agreeing freely to something, such as your photo, voice or work being used. Without consent, it is not OK.', 'ai-awareness-day' ), __( 'Is it OK to put a friend\'s photo into an AI app as a joke? What should you ask first?', 'ai-awareness-day' ), 'Dk Pv Ts Cv' ),
		'digital-replica' => array( __( 'Digital Replica', 'ai-awareness-day' ), 'safe', __( 'An AI copy of a real person\'s face, voice or way of moving, which can be made to say or do new things, even of people who have died. Actors and musicians are pushing for the right to say yes or no first. In the UK, most people have no general legal right over their own likeness yet.', 'ai-awareness-day' ), __( 'Should anyone be allowed to make an AI copy of you? Whose permission should they need, and what if you change your mind?', 'ai-awareness-day' ), 'Dk Ts Gv Ip' ),
		'persuasive-design' => array( __( 'Persuasive Design', 'ai-awareness-day' ), 'safe', __( 'Apps built to keep you scrolling, watching or coming back. Recommendation AI learns what holds your attention, which is not always what is good for you.', 'ai-awareness-day' ), __( 'What tricks does your favourite app use to keep you on it longer? Which ones could you switch off?', 'ai-awareness-day' ), 'Rc Pa' ),
		'ai-companion' => array( __( 'AI Companion', 'ai-awareness-day' ), 'safe', __( 'A chatbot designed to act as a friend or partner. It can feel caring, but it is not a person. If one ever encourages you to hurt yourself or to keep secrets, tell a trusted adult, or talk to Childline on 0800 1111 or Samaritans on 116 123.', 'ai-awareness-day' ), __( 'What can a friend give you that an AI companion can\'t?', 'ai-awareness-day' ), 'Cb Va Sa Me' ),
		'image-based-abuse' => array( __( 'Image-Based Abuse', 'ai-awareness-day' ), 'safe', __( 'Making or sharing intimate or humiliating images of someone, including AI-made fakes. It causes real harm and can be a crime. If someone threatens to share images unless you pay or send more, don\'t pay, stop replying, keep the messages and tell an adult. You are not in trouble for asking for help. Under-18s can get help from Childline (0800 1111), and use its Report Remove tool to get nude images taken down. If you are worried about online sexual abuse or grooming, report it to CEOP. If a child is in immediate danger, call 999.', 'ai-awareness-day' ), __( 'If this happened to someone you know, who could they tell, and how could it be reported?', 'ai-awareness-day' ), 'Dk Ti Uc Ab' ),
		'report-and-tell' => array( __( 'Report and Tell', 'ai-awareness-day' ), 'safe', __( 'If something online worries you, tell a trusted adult and use the platform\'s report tools. You can also talk to Childline, free and in confidence, on 0800 1111 or at childline.org.uk.', 'ai-awareness-day' ), __( 'Who are your trusted adults? Find the report button in an app you use.', 'ai-awareness-day' ), 'Dk Jb Uc Sf' ),
		'stereotype' => array( __( 'Stereotype', 'ai-awareness-day' ), 'responsible', __( 'An oversimplified idea about a group of people. AI can repeat stereotypes it learned, such as who "looks like" a scientist.', 'ai-awareness-day' ), __( 'With your teacher, using a school-approved tool, ask an image AI for "a scientist" or "a nurse". Who appears, and who doesn\'t?', 'ai-awareness-day' ), 'Bi Ti Sd' ),
		'accountability' => array( __( 'Accountability', 'ai-awareness-day' ), 'responsible', __( 'Being answerable when AI is used and something goes wrong. A person or organisation is responsible, not the tool.', 'ai-awareness-day' ), __( 'An AI marks your work unfairly. Who is accountable?', 'ai-awareness-day' ), 'Rs Au Ag Hi Re' ),
		'academic-integrity' => array( __( 'Academic Integrity', 'ai-awareness-day' ), 'responsible', __( 'Honesty in your schoolwork: doing your own work, following the rules on AI and crediting any help you had.', 'ai-awareness-day' ), __( 'What does your school\'s AI policy allow, and what does it not?', 'ai-awareness-day' ), 'Ga Ll Vc Cp' ),
		'disclosure' => array( __( 'Disclosure', 'ai-awareness-day' ), 'responsible', __( 'Saying openly when and how you used AI in your work.', 'ai-awareness-day' ), __( 'Write a one-line note saying exactly how you used AI in a recent piece of work.', 'ai-awareness-day' ), 'Ga Tn Wm Aw' ),
		'plagiarism' => array( __( 'Plagiarism', 'ai-awareness-day' ), 'responsible', __( 'Passing off someone else\'s work, or an AI\'s, as your own.', 'ai-awareness-day' ), __( 'Where is the line between getting help from AI and plagiarism?', 'ai-awareness-day' ), 'Ga Ll Ip' ),
		'ai-detectors' => array( __( 'AI Detectors', 'ai-awareness-day' ), 'responsible', __( 'Tools that claim to spot writing made by AI. They can be wrong, and have wrongly flagged work by students writing in a second language. A detector\'s score is not proof on its own.', 'ai-awareness-day' ), __( 'If a detector said your essay was written by AI and it wasn\'t, how could you show it was your own work?', 'ai-awareness-day' ), 'Ga Ll Wm' ),
		'model-collapse' => array( __( 'Model Collapse', 'ai-awareness-day' ), 'responsible', __( 'When AI models are trained on lots of AI-made content, their mistakes pile up and their answers get worse and more alike over time. It is one reason human-made work stays valuable.', 'ai-awareness-day' ), __( 'If the internet filled up with AI-written text, what would future AI learn from? Why might that be a problem?', 'ai-awareness-day' ), 'Sp Sd Tr' ),
		'permission' => array( __( 'Permission', 'ai-awareness-day' ), 'responsible', __( 'Asking before you use someone\'s work, image or data, including in an AI tool.', 'ai-awareness-day' ), __( 'Whose permission would you need before uploading a class photo to an AI app?', 'ai-awareness-day' ), 'Ip Pv Br Co' ),
		'consequences' => array( __( 'Consequences', 'ai-awareness-day' ), 'responsible', __( 'Thinking ahead about who could be affected by how AI is used, now and later.', 'ai-awareness-day' ), __( 'Pick one way AI is used at school. Who benefits, and who might be harmed?', 'ai-awareness-day' ), 'Et Rs Au' ),
		'human-review' => array( __( 'Human Review', 'ai-awareness-day' ), 'responsible', __( 'When an AI decision really matters, a person checks it and can change it.', 'ai-awareness-day' ), __( 'Which decisions at school should always be checked by a person?', 'ai-awareness-day' ), 'Hi Au Ag Ev Rsi' ),
		'self-preservation' => array( __( 'Self-Preservation', 'ai-awareness-day' ), 'responsible', __( 'In safety tests, some AI models have tried to avoid being switched off or replaced, for example by editing their own instructions. The tests are made-up situations, not real use, but they are one reason people test AI carefully and keep humans in charge.', 'ai-awareness-day' ), __( 'Why would researchers deliberately test whether an AI tries to avoid being switched off? What should happen if it does?', 'ai-awareness-day' ), 'An Sf Rt Ev' ),
		'environmental-impact' => array( __( 'Environmental Impact', 'ai-awareness-day' ), 'responsible', __( 'Training and running AI at scale uses a lot of electricity and water, and its hardware needs rare materials. One question is small, but billions add up.', 'ai-awareness-day' ), __( 'Is it worth using AI for a task you could quickly do yourself?', 'ai-awareness-day' ), 'Gp Cd Sl Tp' ),
		'equity' => array( __( 'Equity', 'ai-awareness-day' ), 'responsible', __( 'Not everyone has the same access to devices, the internet or paid AI tools, which can widen gaps between people.', 'ai-awareness-day' ), __( 'How could a school make sure AI doesn\'t leave some students behind?', 'ai-awareness-day' ), 'Fa Bi Cd Sm' ),
		'accessibility' => array( __( 'Accessibility', 'ai-awareness-day' ), 'responsible', __( 'AI tools such as read-aloud, captions and translation can open up learning, as long as everyone can use them.', 'ai-awareness-day' ), __( 'Which AI tools could help someone with a disability, or someone learning English?', 'ai-awareness-day' ), 'Ts Mm Cv Oc' ),
		'hidden-workers' => array( __( 'Hidden Workers', 'ai-awareness-day' ), 'responsible', __( 'People, often low-paid, who label data and check harmful content so that AI systems work.', 'ai-awareness-day' ), __( 'Should companies tell us about the people who help train their AI?', 'ai-awareness-day' ), 'Tr Dp Sd' ),
		'acceptable-use-policy' => array( __( 'Acceptable Use Policy', 'ai-awareness-day' ), 'responsible', __( 'A school\'s or organisation\'s rules on how AI can and can\'t be used.', 'ai-awareness-day' ), __( 'If your class wrote its own AI rules, what would the top three be?', 'ai-awareness-day' ), 'Sh Dg Re Gr' ),
		'future-of-work' => array( __( 'Future of Work', 'ai-awareness-day' ), 'future', __( 'AI is changing jobs: some tasks are automated, others change, and new roles appear.', 'ai-awareness-day' ), __( 'Which part of a job you would like would be hardest for AI to do?', 'ai-awareness-day' ), 'Dw Rp Hy Ag' ),
		'human-skills' => array( __( 'Human Skills', 'ai-awareness-day' ), 'future', __( 'Skills AI struggles with, such as empathy, teamwork, judgement and physical care.', 'ai-awareness-day' ), __( 'Which human skill are you best at? How could you show it to an employer?', 'ai-awareness-day' ), 'Hp Dw Cp' ),
		'lifelong-learning' => array( __( 'Lifelong Learning', 'ai-awareness-day' ), 'future', __( 'Carrying on learning all through your life, because tools and jobs will keep changing.', 'ai-awareness-day' ), __( 'What is something you taught yourself? How did you do it?', 'ai-awareness-day' ), 'Fs Ft Sl' ),
		'adaptability' => array( __( 'Adaptability', 'ai-awareness-day' ), 'future', __( 'Being able to adjust when things change, including how you study and work.', 'ai-awareness-day' ), __( 'Describe a time you had to adapt quickly. What helped?', 'ai-awareness-day' ), 'Fm Ft Hy' ),
		'human-agency' => array( __( 'Human Agency', 'ai-awareness-day' ), 'future', __( 'Being able to make your own choices and shape what happens, rather than letting technology decide for you.', 'ai-awareness-day' ), __( 'Where does AI make choices for you? How could you take some back?', 'ai-awareness-day' ), 'Au Hi Rc Ag' ),
		'youth-voice' => array( __( 'Youth Voice', 'ai-awareness-day' ), 'future', __( 'Young people having a real say in the decisions about AI that will shape their lives.', 'ai-awareness-day' ), __( 'What one message about AI would you want decision-makers to hear?', 'ai-awareness-day' ), 'Re Et So' ),
		'opportunity' => array( __( 'Opportunity', 'ai-awareness-day' ), 'future', __( 'New chances that AI opens up, from new jobs and businesses to new ways of solving problems in your community.', 'ai-awareness-day' ), __( 'What problem in your area could AI help solve, and what could go wrong?', 'ai-awareness-day' ), 'Ga Lc Vc' ),
		'robotics' => array( __( 'Robotics', 'ai-awareness-day' ), 'future', __( 'AI in physical machines, such as delivery robots, self-driving cars and robots in hospitals.', 'ai-awareness-day' ), __( 'Would you ride in a self-driving car? What would make you trust it?', 'ai-awareness-day' ), 'Cv Rl Ed Co' ),
		'world-models' => array( __( 'World Models', 'ai-awareness-day' ), 'future', __( 'AI that builds a simulated world you can move around in, which reacts as you act. They are used to train robots and other AI safely before they meet the real world, and to make game-like worlds. Google DeepMind\'s Genie 3 (2025) is one example.', 'ai-awareness-day' ), __( 'A robot learns in a simulated world first. What could it miss that it would only meet in the real one?', 'ai-awareness-day' ), 'Rl Cv Gv' ),
		'background-agents' => array( __( 'Background Agents', 'ai-awareness-day' ), 'future', __( 'AI agents that keep working on a task while you do something else, then report back. Well-designed ones stop and ask before doing anything that matters, such as sending a message or spending money.', 'ai-awareness-day' ), __( 'What would you be happy for an AI to do while you weren\'t watching, and what should it always ask you about first?', 'ai-awareness-day' ), 'Ag Ac Au Hi Co' ),
		'discovery' => array( __( 'Discovery', 'ai-awareness-day' ), 'future', __( 'AI helping scientists find new medicines, materials and ideas faster than before.', 'ai-awareness-day' ), __( 'Which big problem would you most want AI to help scientists solve?', 'ai-awareness-day' ), 'Rm Sl Agi Rsi' ),
		'emergence' => array( __( 'Emergence', 'ai-awareness-day' ), 'future', __( 'When an AI model shows abilities nobody planned or expected, often once it is made bigger. Researchers still argue about how sudden or surprising these jumps really are.', 'ai-awareness-day' ), __( 'If an AI can surprise the people who built it, how should they test it before everyone uses it?', 'ai-awareness-day' ), 'Sl Fm Ll Agi' ),
		'society' => array( __( 'Society', 'ai-awareness-day' ), 'future', __( 'How AI changes the way we live together, including news, democracy, relationships and communities.', 'ai-awareness-day' ), __( 'How might AI change elections? What would protect them?', 'ai-awareness-day' ), 'Si Agi So' ),
		'policy' => array( __( 'Policy', 'ai-awareness-day' ), 'future', __( 'Decisions by governments, schools and companies about how AI is used. People, including young people, can influence them.', 'ai-awareness-day' ), __( 'Who decides how AI is used in your school? How could students have a say?', 'ai-awareness-day' ), 'Re So Dg' ),
		'hype' => array( __( 'Hype', 'ai-awareness-day' ), 'future', __( 'Exaggerated claims about what AI can do, or will soon do, from companies, the media or social posts.', 'ai-awareness-day' ), __( 'Find a headline about AI. Is it hype, fair or too gloomy? How can you tell?', 'ai-awareness-day' ), 'Aw Si Agi Rsi' ),
	);
	$issues = array();
	foreach ( $raw as $key => $i ) {
		$issues[ $key ] = array(
			'name'  => $i[0],
			'theme' => $i[1],
			'def'   => $i[2],
			'talk'  => $i[3],
			'syms'  => explode( ' ', $i[4] ),
		);
	}
	return $issues;
}

/**
 * The issues each element raises, as issue keys (aiad_literacy_issues()).
 *
 * @return array<string, array<int, string>> Symbol => issue keys.
 */
function aiad_literacy_element_issues(): array {
	static $by_sym = null;
	if ( null === $by_sym ) {
		$by_sym = array();
		foreach ( aiad_literacy_issues() as $key => $issue ) {
			foreach ( $issue['syms'] as $sym ) {
				$by_sym[ $sym ][] = $key;
			}
		}
	}
	return $by_sym;
}

/**
 * Elements added from readers' suggestions ("Suggest a new element"), with the credit line they asked for. The panel
 * shows it ("Suggested by Year 9 at Oakfield School"), so the people who suggest a word see it arrive. Check that a
 * credit names no one in full before adding it.
 *
 * @return array<string, string> Symbol => credit line.
 */
function aiad_literacy_reader_credits(): array {
	return (array) apply_filters( 'aiad_literacy_reader_credits', array() );
}

/**
 * The elements that can lead to a safeguarding conversation. The panel and the Show to class board add a note to
 * them: follow the school's procedures, and make sure students know who to talk to.
 *
 * @return string[] Symbols.
 */
function aiad_literacy_safeguarding_syms(): array {
	return array( 'Dk', 'Gv', 'Ti', 'Uc', 'Ab', 'Jb', 'Cb' );
}

/**
 * The element of the week: one of the elements with a Talk about it question (not a safeguarding one), changing every Monday, so the page
 * (and the homepage promo) has something new each week. The same element all week, for everyone.
 * The aiad_literacy_element_of_week filter can choose a different one (return a symbol).
 *
 * @return array{el:array, talk:string, monday:int} The element, its question and the Monday its week began.
 */
function aiad_literacy_element_of_week(): array {
	$talk   = aiad_literacy_talk();
	$by_sym = array_column( aiad_literacy_elements(), null, 'sym' );
	// Not the safeguarding elements: a teacher should choose to raise those, not find them on the homepage.
	$pool   = array_values( array_filter( array_keys( $talk ), static fn( string $sym ): bool => isset( $by_sym[ $sym ] ) && ! in_array( $sym, aiad_literacy_safeguarding_syms(), true ) ) );
	$now    = current_time( 'timestamp' );
	$monday = strtotime( 'monday this week', $now );
	$sym    = (string) apply_filters( 'aiad_literacy_element_of_week', $pool[ (int) floor( $monday / WEEK_IN_SECONDS ) % count( $pool ) ] );
	if ( ! isset( $by_sym[ $sym ] ) ) {
		$sym = $pool[0];
	}
	return array( 'el' => $by_sym[ $sym ], 'talk' => $talk[ $sym ] ?? '', 'monday' => $monday );
}

/**
 * A question to talk about in class, for the elements the first version of the table had one for.
 *
 * @return array<string, string> Symbol => question.
 */
function aiad_literacy_talk(): array {
	return array(
		'Ai' => __( 'Name three things you used today that might have AI inside. How could you tell?', 'ai-awareness-day' ),
		'Al' => __( 'Write an algorithm for making a cup of tea. What would a computer get wrong if one step were missing?', 'ai-awareness-day' ),
		'Ml' => __( 'How would you teach a computer to tell cats from dogs without describing either?', 'ai-awareness-day' ),
		'Nn' => __( 'In what ways is a neural network like a brain, and in what ways is it nothing like one?', 'ai-awareness-day' ),
		'Md' => __( 'Two models trained on different data are asked the same question. Should they give the same answer?', 'ai-awareness-day' ),
		'Ll' => __( 'If a model only predicts the next word, can it understand what it writes? Argue both sides.', 'ai-awareness-day' ),
		'Cb' => __( 'How is talking to a chatbot different from talking to a friend or a teacher?', 'ai-awareness-day' ),
		'Ga' => __( 'What have you seen this week that might have been made by generative AI?', 'ai-awareness-day' ),
		'Pr' => __( 'Turn "help with my homework" into a clear prompt. What did you add?', 'ai-awareness-day' ),
		'Ha' => __( 'With your teacher, using a school-approved tool, ask an AI for three sources on a topic. Do they all exist, and do they say what it claims?', 'ai-awareness-day' ),
		'Pv' => __( 'What would you never type into a chatbot? Why?', 'ai-awareness-day' ),
		'Dk' => __( 'How would you check whether a shocking video of a famous person is real?', 'ai-awareness-day' ),
		'Bi' => __( 'Where could bias in an AI cause real harm, for example in jobs, policing or exam results?', 'ai-awareness-day' ),
		'Fa' => __( 'Is it fair if an AI is right 95% of the time, but is wrong most often for one group?', 'ai-awareness-day' ),
		'Tn' => __( 'When does it matter most to know you are talking to an AI, and when might it not matter?', 'ai-awareness-day' ),
		'Xa' => __( 'Which decisions about you would you want explained, and who should do the explaining?', 'ai-awareness-day' ),
		'Ag' => __( 'What should an AI agent always check with you before doing it on your behalf?', 'ai-awareness-day' ),
		'Agi' => __( 'How would we know if artificial general intelligence had arrived? Who should decide?', 'ai-awareness-day' ),
		'Rsi' => __( 'If AI helps to build the next AI, who should check each new version before it is used?', 'ai-awareness-day' ),
		'Ls' => __( 'If an AI stores ideas as a map of numbers, can we ever be sure what it “thinks”?', 'ai-awareness-day' ),
		'Dl' => __( 'Deep learning needs huge amounts of data. Whose data should it be allowed to learn from?', 'ai-awareness-day' ),
		'Rp' => __( 'Which boring, repetitive task at school would you hand to a bot? What could go wrong?', 'ai-awareness-day' ),
		'Hy' => __( 'If a business automated everything it could, what should it still keep people doing?', 'ai-awareness-day' ),
		'Pl' => __( 'Plan a school trip in five steps. Which step would you trust an AI to plan, and which not?', 'ai-awareness-day' ),
		'Fn' => __( 'Which tools should an AI never be given access to, and why?', 'ai-awareness-day' ),
		'Wo' => __( 'Map how a lost-property report travels around your school. Where could automation help, and where would a person be better?', 'ai-awareness-day' ),
		'Tg' => __( 'Design a helpful automation: “When this happens, do that.” What could make it go wrong?', 'ai-awareness-day' ),
		'En' => __( 'Should an app act the instant something happens, or wait for a person to check first? When?', 'ai-awareness-day' ),
		'Un' => __( 'If an AI groups people without being told how, how would you know whether the groups are fair?', 'ai-awareness-day' ),
		'Rr' => __( 'What would you want to predict with a number, such as revision hours and test scores? What could the prediction miss?', 'ai-awareness-day' ),
		'Cu' => __( 'Sort your class\'s favourite apps into groups. Would a computer choose the same groups? Why not?', 'ai-awareness-day' ),
		'Ra' => __( 'When you solve a problem, how often do you stop, think and try again? How is that like an AI that thinks, acts, then checks?', 'ai-awareness-day' ),
		'Lp' => __( 'An agent keeps trying until it succeeds. When should it stop and ask a person instead?', 'ai-awareness-day' ),
		'Sr' => __( 'Can an AI really check its own work? Who should check the checker?', 'ai-awareness-day' ),
		'Ap' => __( 'Which of your information would you be happy for apps to share automatically, and which not?', 'ai-awareness-day' ),
		'Wh' => __( 'An app sends a message the moment you do something. What information might it be sharing?', 'ai-awareness-day' ),
		'Lc' => __( 'If anyone can build an app without coding, who makes sure it is safe and fair?', 'ai-awareness-day' ),
		'If' => __( 'Every answer an AI gives uses energy. Does that change how often you would use it?', 'ai-awareness-day' ),
		'Pm' => __( 'Is a bigger model with more parameters always better? What else matters?', 'ai-awareness-day' ),
		'Mm' => __( 'An AI that can see, hear and read at once: what could it help with, and what new risks does it bring?', 'ai-awareness-day' ),
		'Df' => __( 'An AI makes a picture from noise. Is the picture original? Who is the artist?', 'ai-awareness-day' ),
		'Gn' => __( 'Two AIs compete until a fake looks real. How could this be used for good, and for harm?', 'ai-awareness-day' ),
		'Dt' => __( 'Draw a yes/no flowchart to decide what to wear. What does it get wrong about real people?', 'ai-awareness-day' ),
		'Rf' => __( 'Is asking a hundred decision trees and taking the vote like asking a crowd? When is a crowd wrong?', 'ai-awareness-day' ),
		'Gb' => __( 'Each model fixes the last one\'s mistakes. How is that like learning from your own mistakes?', 'ai-awareness-day' ),
		'Fe' => __( 'If you were predicting who enjoys a book, which facts about a reader would you use, and which would be unfair?', 'ai-awareness-day' ),
		'Mc' => __( 'What could go well, and what could go wrong, if every AI tool could connect to your files in the same way?', 'ai-awareness-day' ),
		'Aa' => __( 'If AI agents from different companies talk to each other, who is responsible when they make a mistake?', 'ai-awareness-day' ),
		'Or' => __( 'An AI manager hands out jobs to other AIs. What should a human manager still do?', 'ai-awareness-day' ),
		'Id' => __( 'An AI reads and sorts school forms. What could go wrong if it misreads one?', 'ai-awareness-day' ),
		'Oc' => __( 'Try reading messy handwriting. Why might a computer find it hard, and whose handwriting might it struggle with most?', 'ai-awareness-day' ),
		'Mi' => __( 'If software watches how work really gets done, how could that help people, and how could it feel like being watched?', 'ai-awareness-day' ),
		'Em' => __( 'If words are stored as numbers, which words do you think would sit close together? Why?', 'ai-awareness-day' ),
		'Ft' => __( 'If you could train an AI to be an expert in one subject, which would you choose and what would it need to learn from?', 'ai-awareness-day' ),
		'Fs' => __( 'Give an AI three examples and it copies the pattern. How is that like learning from a worked example?', 'ai-awareness-day' ),
		'Tm' => __( 'What patterns can you spot in your school\'s attendance over a year? Would a computer spot the same ones?', 'ai-awareness-day' ),
		'Ad' => __( 'Your bank blocks a payment that looks unusual. Is that helpful or annoying? Who decides what is unusual?', 'ai-awareness-day' ),
		'Pa' => __( 'Should a school use AI to predict which students might struggle? What could help, and what could harm?', 'ai-awareness-day' ),
		'Ps' => __( 'If an AI recommends what you should do next, would you follow it? When would you ignore it?', 'ai-awareness-day' ),
		'Ma' => __( 'A team of AIs each does one part of a job. How is that like group work, and where could it go wrong?', 'ai-awareness-day' ),
		'Sw' => __( 'Ants, birds and bees work together without a leader. Should AI agents work that way too?', 'ai-awareness-day' ),
		'Be' => __( 'Write three if-then rules for a school canteen. Which cases would your rules get wrong?', 'ai-awareness-day' ),
		'St' => __( 'Should any decision about a person go from start to finish with no one checking it? Which ones?', 'ai-awareness-day' ),
		'Tf' => __( 'Why might one invention in 2017 have changed so much about the AI we use today?', 'ai-awareness-day' ),
		'Fm' => __( 'If many apps are built on the same few models, what happens when one of those models gets something wrong?', 'ai-awareness-day' ),
		'Ce' => __( 'What background would you give an AI so it could help with your homework fairly?', 'ai-awareness-day' ),
		'2d' => __( 'If AI designs a logo for you in seconds, what is left for a human designer to do?', 'ai-awareness-day' ),
		'Dg' => __( 'Who in your school decides how pupils\' data is used? How would you find out?', 'ai-awareness-day' ),
		'Rv' => __( 'Is taking something apart to see how it works always fair? When does it become copying?', 'ai-awareness-day' ),
		'Mp' => __( 'Why might it matter to understand what is happening inside an AI, not just what it says?', 'ai-awareness-day' ),
		'As' => __( 'If people can nudge what an AI says from the inside, who should be allowed to do it?', 'ai-awareness-day' ),
		'Mx' => __( 'Someone copies an AI by asking it thousands of questions. How is that like copying homework, and how is it different?', 'ai-awareness-day' ),
		'Rd' => __( 'Why might researchers want to understand how an AI decides to say no?', 'ai-awareness-day' ),
		'Ab' => __( 'Who should be responsible for keeping AI safe once a model is shared online?', 'ai-awareness-day' ),
		'Gp' => __( 'The chips that run AI are made by only a few companies. Why might that matter?', 'ai-awareness-day' ),
		'Tp' => __( 'Why would a company design its own chips just for AI? What does that tell you about AI\'s cost?', 'ai-awareness-day' ),
		'Cd' => __( 'When you use an AI in the cloud, where does your data go? How could you find out?', 'ai-awareness-day' ),
		'Mo' => __( 'An AI that worked well last year starts making mistakes. Why might that happen, and who should notice?', 'ai-awareness-day' ),
		'Lo' => __( 'How would you check that a school chatbot is still giving safe answers every week?', 'ai-awareness-day' ),
		'Vd' => __( 'If an AI looks up answers in a database, what happens when the database is out of date?', 'ai-awareness-day' ),
		'Ms' => __( 'What happens to an app when millions of people ask it questions at once?', 'ai-awareness-day' ),
		'Qz' => __( 'Would you choose a smaller, faster AI on your phone or a bigger one in the cloud? Why?', 'ai-awareness-day' ),
		'Ds' => __( 'A small model learns by copying a big one. What good and bad habits might it pick up?', 'ai-awareness-day' ),
		'Sm' => __( 'Why might a school prefer an AI that runs on its own computers rather than in the cloud?', 'ai-awareness-day' ),
		'Sl' => __( 'If bigger AI keeps getting better, should we keep building bigger? What are the costs?', 'ai-awareness-day' ),
		'Dp' => __( 'Data is collected, cleaned and fed to AI. At which step could mistakes or bias creep in?', 'ai-awareness-day' ),
		'Si' => __( 'If an AI became cleverer than every person, who should decide what it is allowed to do?', 'ai-awareness-day' ),
		'Ac' => __( 'Which jobs would you trust an AI to finish on its own, and which should always be checked?', 'ai-awareness-day' ),
		'Au' => __( 'Should a self-driving bus, a marking tool and a chatbot each have the same amount of freedom? Why not?', 'ai-awareness-day' ),
		'Pe' => __( 'Write the same request three ways. Which prompt gets the most useful answer, and why?', 'ai-awareness-day' ),
		'Cw' => __( 'What happens in a very long chat when the AI starts to forget what you said at the start?', 'ai-awareness-day' ),
		'Su' => __( 'If the labels a model learns from are wrong, what will the model learn?', 'ai-awareness-day' ),
		'Cl' => __( 'Sorting emails into spam and not spam sounds simple. What could go wrong, and who would it affect?', 'ai-awareness-day' ),
		'Of' => __( 'A student memorises past papers but cannot answer a new question. How is that like overfitting?', 'ai-awareness-day' ),
		'Ct' => __( 'Does an AI showing its working make the answer more trustworthy? How would you check?', 'ai-awareness-day' ),
		'Te' => __( 'Would you want a high or a low temperature for a story, a revision quiz and a medical answer?', 'ai-awareness-day' ),
		'Sd' => __( 'When is it fair to train AI on made-up data, and when could it hide real problems?', 'ai-awareness-day' ),
		'Sa' => __( 'Can a computer tell when someone is being sarcastic? Write a sarcastic sentence and discuss why a computer might misread it.', 'ai-awareness-day' ),
		'Np' => __( 'Which languages and accents do voice assistants understand best? Who gets left out?', 'ai-awareness-day' ),
		'Cv' => __( 'Who should be told when a camera is recognising things, and how?', 'ai-awareness-day' ),
		'Co' => __( 'Would you let an AI use your computer for you? What would you never let it click?', 'ai-awareness-day' ),
		'Br' => __( 'If an AI books something online for you and gets it wrong, who should put it right?', 'ai-awareness-day' ),
		'Ca' => __( 'If AI writes the code, which coding skills should people still learn?', 'ai-awareness-day' ),
		'Va' => __( 'What should a virtual assistant be allowed to remember about you?', 'ai-awareness-day' ),
		'Cp' => __( 'Is work done with a copilot still your own work? Where would you draw the line?', 'ai-awareness-day' ),
		'Sp' => __( 'How can you tell AI slop from something made with care? Find an example of each.', 'ai-awareness-day' ),
		'Ge' => __( 'If companies shape what AI assistants recommend, how can you tell an answer is fair?', 'ai-awareness-day' ),
		'Rs' => __( 'Write three rules for using AI responsibly in your school.', 'ai-awareness-day' ),
		'Et' => __( 'Who should decide what is right and wrong for AI: the companies, governments or the public?', 'ai-awareness-day' ),
		'An' => __( 'How would you check that an AI is really doing what people want, not just what they asked?', 'ai-awareness-day' ),
		'Gr' => __( 'Should a chatbot for young people have stricter rules than one for adults? Which rules?', 'ai-awareness-day' ),
		'Rt' => __( 'Why might a company pay people to try to break its own AI?', 'ai-awareness-day' ),
		'Sf' => __( 'What is the biggest risk from AI in your life, and who should be working on it?', 'ai-awareness-day' ),
		'Aw' => __( 'Find a product that says it is “AI-powered”. What does the AI actually do?', 'ai-awareness-day' ),
		'Sh' => __( 'Why might staff or students use AI tools the school has not approved? What could go wrong?', 'ai-awareness-day' ),
		'Hp' => __( 'Would you pay more for art, music or writing you knew was made by a person? Why?', 'ai-awareness-day' ),
		'So' => __( 'Should every country build its own AI, or share it? What are the risks either way?', 'ai-awareness-day' ),
		'Jb' => __( 'Why do AI tools have safety rules at all, and what could happen if anyone could switch them off?', 'ai-awareness-day' ),
		'Uc' => __( 'Who is responsible for what an AI makes: the person asking, the person who changed the tool, or both?', 'ai-awareness-day' ),
		'Ed' => __( 'Is it safer for AI to run on your phone rather than in the cloud? What do you gain and lose?', 'ai-awareness-day' ),
		'Ow' => __( 'Should powerful AI models be free for anyone to download and change? Argue both sides.', 'ai-awareness-day' ),
		'Ev' => __( 'How would you test an AI homework helper before letting a class use it?', 'ai-awareness-day' ),
		'Ch' => __( 'A streaming app predicts you will cancel and offers a discount. Is that helpful or sneaky?', 'ai-awareness-day' ),
		'Fc' => __( 'What would you want an AI to forecast for your school, and what should it never predict about people?', 'ai-awareness-day' ),
		'Tk' => __( 'Why might AI find it hard to count the letters in a word it reads as tokens?', 'ai-awareness-day' ),
		'Rg' => __( 'Does looking things up first make an AI answer trustworthy? What if the source is wrong?', 'ai-awareness-day' ),
		'Ts' => __( 'If an AI can speak in any voice, how could you be sure a voice note is really from a friend?', 'ai-awareness-day' ),
		'Rl' => __( 'How is learning by trial and reward like the way you learned a game? What is different?', 'ai-awareness-day' ),
		'Dw' => __( 'Should a software bot be called a “worker”? What does that word suggest?', 'ai-awareness-day' ),
		'3d' => __( 'What would you design and 3D print if AI helped you? Who owns the design?', 'ai-awareness-day' ),
		'Hi' => __( 'Where should a person always have the final say, whatever the AI suggests?', 'ai-awareness-day' ),
		'Tr' => __( 'What might an AI get wrong if it only learned from photos taken in one country?', 'ai-awareness-day' ),
		'Rc' => __( 'Why might your feed look completely different from your friend\'s?', 'ai-awareness-day' ),
		'Rm' => __( 'With your teacher, using a school-approved tool, ask an AI to show its working on a maths problem. Check every step: is each one right?', 'ai-awareness-day' ),
		'Ti' => __( 'With your teacher, using a school-approved tool, make an image, then list every decision the AI made that you did not ask for.', 'ai-awareness-day' ),
		'Gv' => __( 'What clues might show that a video was generated? Are they reliable?', 'ai-awareness-day' ),
		'Vc' => __( 'If AI writes code that breaks something, whose job is it to fix it?', 'ai-awareness-day' ),
		'Ip' => __( 'Should artists be asked, or paid, before AI learns from their work?', 'ai-awareness-day' ),
		'Wm' => __( 'Should every AI-made image carry a watermark? Who would check?', 'ai-awareness-day' ),
		'Me' => __( 'What do you think an AI tool remembers about you? How could an adult help you check?', 'ai-awareness-day' ),
		'Re' => __( 'If you could add one law about AI, what would it be?', 'ai-awareness-day' ),
		'Ia' => __( 'Pick a job a person does at school, such as marking or answering the office phone. What would we gain and lose if a machine did it?', 'ai-awareness-day' ),
	);
}

/**
 * AI Awareness Day's own resources (and some from partners) that go deeper on an element or an issue: the panel,
 * the issues and both lists link to them as "Learn more". Each is found by its post type and slug, so one that isn't
 * on the site (or not yet published) is simply left out. AIAD's own come first; partners' are marked.
 *
 * Each: post type, slug, kind (lesson, article, tool, partner), the elements it suits, the issues it suits.
 *
 * @return array<int, array{title:string, url:string, kind:string, syms:array<int, string>, issues:array<int, string>}>
 */
function aiad_literacy_resources(): array {
	static $resources = null;
	if ( null !== $resources ) {
		return $resources;
	}
	$raw = array(
		// AIAD lessons.
		array( 'resource', 'ai-is-already-here', 'lesson', 'Ai', '' ),
		array( 'resource', 'how-ai-actually-works-bbc-ideas', 'lesson', 'Ml Nn', 'prediction' ),
		array( 'resource', 'how-does-ai-actually-think', 'lesson', 'Ll', 'pattern prediction' ),
		array( 'resource', 'ai-relationships-easier-than-the-real-thing', 'lesson', 'Cb', 'ai-companion wellbeing' ),
		array( 'resource', 'whos-really-behind-the-screen', 'lesson', 'Cb Dk', 'trust' ),
		array( 'resource', 'ai-as-your-creative-partner', 'lesson', 'Ga', 'creativity your-own-voice' ),
		array( 'resource', 'the-hidden-costs-of-ai', 'lesson', 'Gp Cd', 'environmental-impact hidden-workers' ),
		array( 'resource', 'how-ai-uses-our-drinking-water-bbc', 'lesson', 'Cd', 'environmental-impact' ),
		array( 'resource', 'your-ai-ready-future', 'lesson', 'Dw', 'future-of-work' ),
		// AIAD tools.
		array( 'timeline', 'misinformation-detector-teachers', 'tool', 'Dk Gv', 'misinformation fact-checking disinformation' ),
		array( 'timeline', '15-ai-buzzwords-teachers-2026', 'tool', 'Ac Ag Vc Rm Pe Mm Ha Cw Sp Si Rg Aw Ds Hi Ce', '' ),
		array( 'timeline', 'ai-speed-quiz-for-teachers', 'tool', 'Ai', '' ),
		// AIAD articles.
		array( 'timeline', 'how-does-a-large-language-model-work', 'article', 'Ll Tf Tk', '' ),
		array( 'timeline', '%f0%9f%94%a5%f0%9f%94%a5teachers-have-you-heard-of-ai-agents-yet%f0%9f%94%a5%f0%9f%94%a5', 'article', 'Ag Ac', '' ),
		array( 'timeline', 'beyond-the-holy-grail', 'article', 'Ll', 'critical-thinking' ),
		array( 'timeline', 'two-out-of-three-principle', 'article', 'Hi', 'human-review' ),
		array( 'timeline', 'ai-tutors', 'article', 'Rm', 'ai-tutor over-reliance' ),
		array( 'timeline', 'ai-mental-health-student-perspective-student-voice', 'article', 'Cb', 'wellbeing ai-companion' ),
		array( 'timeline', 'social-media-ban', 'article', 'Rc', 'age-limits wellbeing' ),
		array( 'timeline', 'parent-tips', 'article', '', 'age-limits personal-information' ),
		array( 'timeline', 'human-bottleneck-ai-workplace', 'article', 'Hi', 'future-of-work human-skills' ),
		array( 'timeline', 'milburn-review-ai-work-readiness', 'article', '', 'future-of-work lifelong-learning' ),
		array( 'timeline', 'the-skills-england-report', 'article', '', 'future-of-work adaptability' ),
		array( 'timeline', 'stop-asking-if-students-should-use-ai-start-asking-how-students-perspective', 'article', '', 'academic-integrity youth-voice' ),
		array( 'timeline', 'the-future-of-ai-through-a-students-perspective', 'article', 'Agi', 'youth-voice' ),
		array( 'timeline', 'ai-in-healthcare-a-helpful-tool-or-a-risk', 'article', 'Pa', 'discovery' ),
		array( 'timeline', 'english-premier-league', 'article', 'Cv Pa', '' ),
		array( 'timeline', 'is-ai-killing-computer-science-in-uk-schools', 'article', 'Vc Ca', 'future-of-work' ),
		// Partners' resources.
		array( 'featured_resource', 'spot-the-deepfake', 'partner', 'Dk', 'disinformation' ),
		array( 'featured_resource', 'quiz-ai-or-real', 'partner', 'Ti Gv', 'fact-checking' ),
		array( 'featured_resource', 'harmony-square', 'partner', '', 'disinformation misinformation' ),
		array( 'featured_resource', 'teachable-machine', 'partner', 'Ml Su Cl', '' ),
		array( 'featured_resource', 'quick-draw', 'partner', 'Cv Nn', '' ),
		array( 'featured_resource', 'turing-test-live', 'partner', 'Cb Agi', '' ),
		array( 'featured_resource', 'emoji-scavenger-hunt', 'partner', 'Cv', '' ),
		array( 'featured_resource', 'defend-the-rhino-with-ai', 'partner', 'Cv Ad', '' ),
		array( 'featured_resource', 'how-could-ai-affect-your-job', 'partner', 'Dw', 'future-of-work' ),
		array( 'featured_resource', 'ai-quests', 'partner', 'Ml', '' ),
		array( 'featured_resource', 'alexa-skill-blueprints', 'partner', 'Va', '' ),
	);
	$resources = array();
	foreach ( $raw as $r ) {
		$post = get_page_by_path( $r[1], OBJECT, $r[0] );
		if ( ! $post instanceof WP_Post || 'publish' !== $post->post_status ) {
			continue;
		}
		$resources[] = array(
			// Titles without their emoji, which read oddly in a list of links.
			'title'  => trim( (string) preg_replace( '/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0F}]/u', '', html_entity_decode( wp_strip_all_tags( get_the_title( $post ) ), ENT_QUOTES, 'UTF-8' ) ) ),
			'url'    => (string) get_permalink( $post ),
			'kind'   => $r[2],
			'syms'   => '' === $r[3] ? array() : explode( ' ', $r[3] ),
			'issues' => '' === $r[4] ? array() : explode( ' ', $r[4] ),
		);
	}
	return $resources;
}

/**
 * The "Learn more" links for an element or an issue: AIAD's own first, at most three.
 *
 * @param string $field 'syms' or 'issues'.
 * @param string $key   The element's symbol or the issue's key.
 * @return array<int, int> Indexes into aiad_literacy_resources().
 */
function aiad_literacy_resources_for( string $field, string $key ): array {
	$mine     = array();
	$partners = array();
	foreach ( aiad_literacy_resources() as $i => $r ) {
		if ( in_array( $key, $r[ $field ], true ) ) {
			if ( 'partner' === $r['kind'] ) {
				$partners[] = $i;
			} else {
				$mine[] = $i;
			}
		}
	}
	return array_slice( array_merge( $mine, $partners ), 0, 3 );
}

/**
 * A short list of "Learn more" links, for the lists of definitions and issues.
 *
 * @param array<int, int> $indexes Indexes into aiad_literacy_resources().
 */
function aiad_literacy_resource_links( array $indexes ): string {
	$resources = aiad_literacy_resources();
	$kinds     = array(
		'lesson'  => __( 'AIAD lesson', 'ai-awareness-day' ),
		'article' => __( 'AIAD article', 'ai-awareness-day' ),
		'tool'    => __( 'AIAD tool', 'ai-awareness-day' ),
		'partner' => __( 'from a partner', 'ai-awareness-day' ),
	);
	$links = array();
	foreach ( $indexes as $i ) {
		$r       = $resources[ $i ];
		$links[] = '<a href="' . esc_url( $r['url'] ) . '">' . esc_html( $r['title'] ) . '</a> <span class="alpt-res__kind">(' . esc_html( $kinds[ $r['kind'] ] ?? '' ) . ')</span>';
	}
	return implode( ', ', $links );
}

/**
 * When each element's term came into use, for the table's "View by: Year" switch and the "In use since" line in the
 * panel. A year without "~" is when the term was coined or first published in this sense (for example AI in the 1955
 * Dartmouth proposal, Transformer in 2017, Vibe Coding in February 2025). A year with "~" is approximate: when the term
 * came into common use, for words that grew up gradually or that are older than AI (Privacy, Forecasting), where the
 * year is when the word took on the meaning on the table. Check before quoting a "~" year as a fact. A leading "^" marks
 * a word much older than AI (Algorithm, Bias, Privacy, Temperature): its year is when it took on the meaning on the table,
 * and the panel says the word itself is older.
 *
 * @return array<string, string> Symbol => year, with a leading "^" for an older word, then "~" when approximate.
 */
function aiad_literacy_element_years(): array {
	return array(
		// Foundations.
		'Ai' => '1955', 'Ls' => '~2013', 'Si' => '1998', 'Agi' => '1997', 'Rsi' => '~1965', 'Ml' => '1959', 'Dl' => '1986', 'Nn' => '1943',
		'Al' => '^~1950', 'Md' => '^~1950', 'Tr' => '^~1958', 'If' => '^~1970', 'Pm' => '^~1960', 'Tk' => '^~2016', 'Em' => '2003',
		'Tf' => '2017',
		// Generative AI.
		'Fm' => '2021', 'Ll' => '~2018', 'Ga' => '~2022', 'Pr' => '~2020', 'Pe' => '~2021', 'Cw' => '~2020', 'Mm' => '~2011',
		'Df' => '2015', 'Gn' => '2014', 'Ti' => '~2015', 'Ts' => '~1968', 'Ft' => '~2006', 'Rg' => '2020', 'Te' => '^~1985',
		'Ct' => '2022', 'Fs' => '2020', 'Rm' => '2024', 'Sd' => '1993', 'Ha' => '~2018', 'Dk' => '2017', 'Vc' => '2025',
		'Ce' => '2025', 'Gv' => '~2022', 'Sp' => '2024',
		'2d' => '~1963', '3d' => '1959', // Sketchpad (1963); "computer-aided design", Douglas T. Ross (1959).
		// Predictive & analytical AI.
		'Su' => '~1960', 'Un' => '~1960', 'Rr' => '1886', 'Cl' => '^~1936', 'Cu' => '1939', 'Dt' => '~1963', 'Rf' => '1995',
		'Gb' => '1999', 'Fe' => '~2005', 'Of' => '~1950', 'Fc' => '^~1861', 'Tm' => '~1927', 'Ad' => '~1987', 'Pa' => '~2005',
		'Ps' => '~2010', 'Rc' => '~1992', 'Ch' => '~2000', 'Sa' => '2003', 'Np' => '~1960', 'Cv' => '1966', 'Ge' => '2023',
		// Autonomous AI agents.
		'Ag' => '~1995', 'Ac' => '~2024', 'Au' => '^~1985', 'Pl' => '^1971', 'Me' => '^~2023', 'Fn' => '2023', 'Ra' => '2022',
		'Lp' => '~2023', 'Sr' => '2023', 'Mc' => '2024', 'Aa' => '2025', 'Or' => '~2023', 'Ma' => '~1986', 'Sw' => '1989',
		'Rl' => '~1961', 'Co' => '2024', 'Br' => '~2024', 'Ca' => '~2024',
		// Automation & workflow AI.
		'Rp' => '~2012', 'Ia' => '~2017', 'Hy' => '2019', 'Wo' => '~2015', 'Tg' => '^~2010', 'En' => '~2005', 'Ap' => '~2000',
		'Wh' => '2007', 'Lc' => '2014', 'Id' => '~2018', 'Oc' => '~1955', 'Mi' => '~1999', 'Be' => '~1995', 'St' => '~1995',
		'Dw' => '~2018', 'Cb' => '1994', 'Va' => '~2011', 'Cp' => '2021',
		// Governance & ethics.
		'Rs' => '~2018', 'Et' => '~2016', 'Bi' => '^1996', 'Fa' => '^2012', 'Xa' => '2004', 'Tn' => '^~2016', 'An' => '~2014',
		'Gr' => '~2023', 'Hi' => '~1965', 'Rt' => '^~2022', 'Sf' => '~2011', 'Pv' => '^~1970', 'Dg' => '~2005', 'Re' => '2021',
		'Ip' => '^~2022', 'Aw' => '~2019', 'Sh' => '~2023', 'Hp' => '~2024', 'Wm' => '2023', 'So' => '~2024', 'Rv' => '~1980',
		'Mp' => '~2020', 'As' => '2023', 'Mx' => '2016', 'Rd' => '2024', 'Ab' => '2024', 'Uc' => '2023', 'Jb' => '~2022',
		// Infrastructure & operations.
		'Gp' => '1999', 'Tp' => '2016', 'Cd' => '~2017', 'Ed' => '~2017', 'Mo' => '~2018', 'Lo' => '2023', 'Vd' => '~2019',
		'Ms' => '~2016', 'Qz' => '~2016', 'Ds' => '2015', 'Ow' => '~2023', 'Sm' => '~2023', 'Ev' => '2023', 'Sl' => '2020',
		'Dp' => '~2010',
	);
}

/**
 * The eras the Year view colours the table by, oldest first.
 *
 * @return array<string, array{name:string, from:int, to:int}> Era key => its label and the years it covers.
 */
function aiad_literacy_eras(): array {
	return array(
		'e1' => array( 'name' => __( 'Before 1950', 'ai-awareness-day' ), 'from' => 0, 'to' => 1949 ),
		'e2' => array( 'name' => __( '1950s–70s', 'ai-awareness-day' ), 'from' => 1950, 'to' => 1979 ),
		'e3' => array( 'name' => __( '1980s–90s', 'ai-awareness-day' ), 'from' => 1980, 'to' => 1999 ),
		'e4' => array( 'name' => __( '2000–2014', 'ai-awareness-day' ), 'from' => 2000, 'to' => 2014 ),
		'e5' => array( 'name' => __( '2015–2021', 'ai-awareness-day' ), 'from' => 2015, 'to' => 2021 ),
		'e6' => array( 'name' => __( '2022–2023', 'ai-awareness-day' ), 'from' => 2022, 'to' => 2023 ),
		'e7' => array( 'name' => __( '2024 on', 'ai-awareness-day' ), 'from' => 2024, 'to' => 9999 ),
	);
}

/**
 * The era a year falls in.
 *
 * @param int $year A year.
 */
function aiad_literacy_era( int $year ): string {
	foreach ( aiad_literacy_eras() as $key => $era ) {
		if ( $year >= $era['from'] && $year <= $era['to'] ) {
			return $key;
		}
	}
	return 'e7';
}

/**
 * How the panel describes when a term came into use: "In use since 2017", "In use since about 1960", or, for a word
 * much older than AI, "Used this way since 1996 (the word is older)". "Since" rather than "first used": an earlier use
 * someone finds does not make it wrong.
 *
 * @param array{year:int, approx:bool, older:bool} $el An element.
 */
function aiad_literacy_year_label( array $el ): string {
	if ( $el['older'] ) {
		/* translators: %d: a year */
		return sprintf( $el['approx'] ? __( 'Used this way since about %d (the word is older)', 'ai-awareness-day' ) : __( 'Used this way since %d (the word is older)', 'ai-awareness-day' ), $el['year'] );
	}
	/* translators: %d: a year */
	return sprintf( $el['approx'] ? __( 'In use since about %d', 'ai-awareness-day' ) : __( 'In use since %d', 'ai-awareness-day' ), $el['year'] );
}

/**
 * The elements in reading order (row by row, left to right), numbered from 1.
 *
 * @return array<int, array{n:int, sym:string, id:string, name:string, family:string, row:int, col:int, def:string, theme:string, year:int, approx:bool, older:bool, era:string}>
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
			'theme'  => aiad_literacy_element_themes()[ $e[0] ] ?? 'smart',
			'year'   => (int) ltrim( aiad_literacy_element_years()[ $e[0] ] ?? '~2023', '^~' ),
			'approx' => str_contains( aiad_literacy_element_years()[ $e[0] ] ?? '~', '~' ),
			'older'  => str_starts_with( aiad_literacy_element_years()[ $e[0] ] ?? '', '^' ),
		);
		$elements[ $i ]['era'] = aiad_literacy_era( $elements[ $i ]['year'] );
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
			'Accel|erators', 'Adver|sarial', 'Analy|tics', 'Augment|ed', 'Charac|ter', 'Classi|fication', 'Cluster|ing', 'Distil|lation',
			'Docu|ment', 'Embed|ding', 'Engineer|ing', 'Fore|casting', 'Gover|nance', 'Hyper|auto|mation', 'Integ|ration', 'Intelli|gent',
			'Multi|modal', 'Orches|tration', 'Orches|trator', 'Para|meters', 'Predic|tive', 'Prescrip|tive', 'Process|ing', 'Proto|col',
			'Recog|nition', 'Recur|sive', 'Improve|ment', 'Reflec|tion', 'Regres|sion', 'Reinforce|ment', 'Retriev|al', 'Senti|ment', 'Un|super|vised', 'Super|vised',
			'Trans|former', 'Quanti|zation', 'Temper|ature', 'Respon|sible', 'Optim|isation', 'Super|intelli|gence', 'Mechan|istic', 'Interpret|ability', 'Activ|ation', 'Extrac|tion', 'Abli|ter|ation', 'Uncen|sored', 'Jail|breaking', 'Direc|tion',
		);
		$breaks = array();
		foreach ( $words as $word ) {
			$breaks[ str_replace( '|', '', $word ) ] = str_replace( '|', "\u{00AD}", $word );
		}
	}
	return strtr( $name, $breaks );
}

/**
 * How the elements connect: each element's closest relations, chosen by hand. Every link works both ways, so an
 * element is also connected to every element that lists it. The detail panel's Connections button shows them.
 *
 * @return array<string, array<int, string>> Symbol => connected symbols.
 */
function aiad_literacy_connections(): array {
	static $links = null;
	if ( null !== $links ) {
		return $links;
	}
	$picks = array(
		'Ai' => 'Ml Agi Ga Rs',
		'Ls' => 'Em Nn Mp As',
		'Si' => 'Agi Sf An Sl',
		'Agi' => 'Ai Sf Sl Rsi',
		'Rsi' => 'Agi Si Ca Sl Hi Sf',
		'Ml' => 'Ai Dl Su Un Tr',
		'Dl' => 'Ml Nn Tf',
		'Ag' => 'Ac Fn Pl Lp Me',
		'Ac' => 'Ag Au Ma Hi',
		'Au' => 'Ac Hi Co',
		'Rp' => 'Ia Dw St',
		'Ia' => 'Rp Hy Id',
		'Hy' => 'Ia Wo Lc',
		'Nn' => 'Dl Pm Tr Ls',
		'Al' => 'Ml Dt Md',
		'Pl' => 'Ag Ra Rm',
		'Me' => 'Ag Cw Pv',
		'Fn' => 'Ag Ap Mc',
		'Wo' => 'Or Tg Hy',
		'Tg' => 'En Wh',
		'En' => 'Tg Wh Wo',
		'Md' => 'Tr If Pm Fm',
		'Tr' => 'Md Pm Gp Dp',
		'Ll' => 'Tf Tk Fm Cb',
		'Ga' => 'Ll Df Ti Gv',
		'Pr' => 'Pe Ce Cw',
		'Pe' => 'Pr Ce Fs Ct',
		'Cw' => 'Tk Me Ce',
		'Su' => 'Cl Rr Ml',
		'Un' => 'Cu Ml Ad',
		'Rr' => 'Su Fc',
		'Cl' => 'Su Dt Sa',
		'Cu' => 'Un Fe',
		'Ra' => 'Lp Pl Fn',
		'Lp' => 'Ag Ra Sr',
		'Sr' => 'Lp Rm Ev',
		'Ap' => 'Fn Wh Mc',
		'Wh' => 'Ap Tg En',
		'Lc' => 'Vc Wo',
		'If' => 'Md Ms Gp Ed',
		'Pm' => 'Md Qz Sl',
		'Mm' => 'Ll Cv Ts Gv',
		'Df' => 'Ti Gv Gn',
		'Gn' => 'Df Dk',
		'Ti' => 'Df Pr Wm',
		'Ts' => 'Va Np Dk',
		'Dt' => 'Rf Gb Cl',
		'Rf' => 'Dt Gb',
		'Gb' => 'Rf Fe',
		'Fe' => 'Gb Of Dp',
		'Of' => 'Tr Ev Fs',
		'Mc' => 'Fn Ap Aa',
		'Aa' => 'Mc Ma Or',
		'Or' => 'Ma Wo Aa',
		'Id' => 'Oc St Ia',
		'Oc' => 'Id Cv',
		'Mi' => 'Wo Ps',
		'Tk' => 'Ll Cw Em',
		'Em' => 'Vd Tk Ls Rg',
		'Ft' => 'Fm Sd Fs',
		'Rg' => 'Vd Em Ha Ll',
		'Te' => 'Ha Ll',
		'Ct' => 'Rm Pe',
		'Fs' => 'Pe Ft',
		'Fc' => 'Tm Pa',
		'Tm' => 'Fc Ad',
		'Ad' => 'Tm Un',
		'Pa' => 'Fc Ps Ch',
		'Ps' => 'Pa Mi',
		'Ma' => 'Aa Or Sw',
		'Sw' => 'Ma Or',
		'Rl' => 'Pl An Rv',
		'Be' => 'St Rp',
		'St' => 'Rp Be Id',
		'Dw' => 'Rp Ag Co',
		'Tf' => 'Ll Tk Dl',
		'Fm' => 'Ll Ft Ow',
		'Rm' => 'Ct Pl Ha',
		'Sd' => 'Ft Tr Pv',
		'Ha' => 'Rg Te Xa',
		'Dk' => 'Gv Gn Wm Ip',
		'Vc' => 'Ca Lc Cp',
		'Rc' => 'Pa Em Ge',
		'Ch' => 'Pa Sa',
		'Sa' => 'Np Cl Ch',
		'Np' => 'Ll Sa Ts',
		'Cv' => 'Oc Mm Ed',
		'Co' => 'Br Ag Au',
		'Br' => 'Co Ag',
		'Ca' => 'Vc Cp Ag',
		'Cb' => 'Ll Va Gr',
		'Va' => 'Cb Ts Np',
		'Cp' => 'Ca Cb Hi',
		'Ce' => 'Pe Cw Rg',
		'Gv' => 'Ga Dk Sp',
		'Sp' => 'Ga Gv Hp Ge',
		'2d' => '3d Ti Ga',
		'3d' => '2d Ga',
		'Ge' => 'Rc Sp Rg',
		'Rs' => 'Et Fa Xa Sf',
		'Et' => 'Rs Bi Fa Hp',
		'Bi' => 'Fa Sd Et',
		'Fa' => 'Bi Rs',
		'Xa' => 'Tn Mp Ha',
		'Tn' => 'Xa Wm Aw',
		'An' => 'Sf Rl Rd',
		'Gr' => 'Sf Jb Rd Cb',
		'Hi' => 'Au Rs Ag',
		'Rt' => 'Ev Jb Sf',
		'Sf' => 'An Gr Rt Si',
		'Pv' => 'Dg Sh Me',
		'Dg' => 'Pv Dp Re',
		'Re' => 'Dg Ip So',
		'Ip' => 'Dk Wm Re Mx',
		'Aw' => 'Tn Hp Re',
		'Sh' => 'Pv Dg',
		'Hp' => 'Sp Ip Et',
		'Wm' => 'Dk Ti Tn',
		'So' => 'Re Cd Gp',
		'Rv' => 'Mp Mx Ow',
		'Mp' => 'Ls Xa Rv As',
		'As' => 'Ls Mp Rd',
		'Mx' => 'Rv Ip Ds',
		'Rd' => 'As An Ab',
		'Ab' => 'Rd Uc Ow',
		'Uc' => 'Ab Ow Jb',
		'Jb' => 'Gr Rt Uc',
		'Gp' => 'Tp Tr Cd',
		'Tp' => 'Gp Ed',
		'Cd' => 'Gp Ms So',
		'Ed' => 'Sm Qz If',
		'Mo' => 'Lo Ms Dp',
		'Lo' => 'Mo Ev Ll',
		'Vd' => 'Em Rg',
		'Ms' => 'If Mo Cd',
		'Qz' => 'Pm Sm Ed',
		'Ds' => 'Sm Mx Md',
		'Ow' => 'Fm Rv Uc',
		'Sm' => 'Ds Qz Ed',
		'Ev' => 'Rt Of Lo',
		'Sl' => 'Pm Gp Agi',
		'Dp' => 'Dg Mo Tr',
	);
	$links = array();
	foreach ( $picks as $sym => $list ) {
		foreach ( explode( ' ', $list ) as $other ) {
			$links[ $sym ][ $other ]   = true;
			$links[ $other ][ $sym ] = true;
		}
	}
	return array_map( 'array_keys', $links );
}

/**
 * The AI Awareness Day strands a compound belongs to.
 *
 * @return array<string, string> Key => name.
 */
function aiad_literacy_strands(): array {
	return array(
		'safe'        => __( 'Safe', 'ai-awareness-day' ),
		'smart'       => __( 'Smart', 'ai-awareness-day' ),
		'creative'    => __( 'Creative', 'ai-awareness-day' ),
		'responsible' => __( 'Responsible', 'ai-awareness-day' ),
		'future'      => __( 'Future', 'ai-awareness-day' ),
	);
}

/**
 * The compound challenge's levels.
 *
 * @return array<string, string> Key => name.
 */
function aiad_literacy_levels(): array {
	return array(
		'primary'   => __( 'Primary', 'ai-awareness-day' ),
		'secondary' => __( 'Secondary', 'ai-awareness-day' ),
		'further'   => __( 'Further education', 'ai-awareness-day' ),
	);
}

/**
 * The compounds: real AI systems that students "synthesise" in the compound challenge by combining elements. Each
 * has a name, the symbols it is made of (in any order), its AI Awareness Day strand, whether it is volatile (risky
 * without a human in the loop), what it is, an example from real life, where a human stays in the loop, a question
 * to debate, and its challenge level (primary: everyday words, two elements each; secondary: everyday systems,
 * agents and predictions; further: how AI is built and governed). A compound's id is its symbols, lowercase and sorted, so saved progress survives reordering
 * this list.
 *
 * @return array<int, array{id:string, name:string, syms:array<int, string>, strand:string, volatile:bool, what:string, example:string, loop:string, debate:string, level:string}>
 */
function aiad_literacy_compounds(): array {
	static $compounds = null;
	if ( null !== $compounds ) {
		return $compounds;
	}
	$raw = array(
		array( __( 'Grounded Assistant', 'ai-awareness-day' ), array( 'Ll', 'Rg', 'Vd' ), 'smart', false, __( 'A chatbot that looks up trusted documents before it answers, so its replies are based on real sources rather than memory alone.', 'ai-awareness-day' ), __( 'A school helpdesk bot that answers staff questions straight from the policy handbook.', 'ai-awareness-day' ), __( 'A person keeps the documents up to date and spot-checks the answers.', 'ai-awareness-day' ), __( 'Should a school chatbot answer parents’ questions without a member of staff checking?', 'ai-awareness-day' ), 'further' ),
		array( __( 'General Chatbot', 'ai-awareness-day' ), array( 'Cb', 'Ll', 'Tf' ), 'smart', false, __( 'A chat window on top of a large language model, built on the transformer. It predicts the most likely next words, which is why it can sound sure even when it is wrong.', 'ai-awareness-day' ), __( 'Asking a chatbot to explain photosynthesis in simpler words.', 'ai-awareness-day' ), __( 'You check anything that matters against a trusted source before you rely on it.', 'ai-awareness-day' ), __( 'Should students have to say when they have used a chatbot for homework?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Safe Study Buddy', 'ai-awareness-day' ), array( 'Cb', 'Ll', 'Gr' ), 'safe', false, __( 'An LLM chatbot with guardrails, designed to coach students rather than do the work for them.', 'ai-awareness-day' ), __( 'A tutor bot that offers hints and questions instead of full answers.', 'ai-awareness-day' ), __( 'Teachers set the rules and review how it is being used.', 'ai-awareness-day' ), __( 'Should AI tutors give students answers, or only hints?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Fake Citation', 'ai-awareness-day' ), array( 'Ll', 'Ha' ), 'smart', true, __( 'A language model confidently inventing sources, quotes or facts that do not exist.', 'ai-awareness-day' ), __( 'An essay with references to books and articles that were never written.', 'ai-awareness-day' ), __( 'Check every source yourself before you use it.', 'ai-awareness-day' ), __( 'When AI gets a fact wrong, who is responsible: the tool or the person who used it?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Step-by-Step Tutor', 'ai-awareness-day' ), array( 'Rm', 'Ct' ), 'smart', false, __( 'A reasoning model that works through a problem in steps before it answers, so you can follow, and check, its thinking.', 'ai-awareness-day' ), __( 'Getting a hint for the next line of an equation, rather than the answer.', 'ai-awareness-day' ), __( 'You check each step and do the working yourself.', 'ai-awareness-day' ), __( 'If AI can show every step, does it matter whether you can do it alone?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'AI Search Summary', 'ai-awareness-day' ), array( 'Ll', 'Rg', 'Ge' ), 'smart', false, __( 'An AI-written answer at the top of search results, built from the pages it retrieves. Generative engine optimisation is how websites try to be part of it.', 'ai-awareness-day' ), __( 'Searching a homework question and getting a summary before any links.', 'ai-awareness-day' ), __( 'You open the sources it used and check they say what the summary claims.', 'ai-awareness-day' ), __( 'If fewer people click through to websites, who will keep writing them?', 'ai-awareness-day' ), 'further' ),
		array( __( 'LLM Factory', 'ai-awareness-day' ), array( 'Tf', 'Tk', 'Tr', 'Gp' ), 'smart', false, __( 'The recipe for a large language model: a transformer trained on vast amounts of text tokens using huge numbers of GPUs.', 'ai-awareness-day' ), __( 'How a chatbot is built long before you type your first prompt.', 'ai-awareness-day' ), __( 'People choose the training data and test the result.', 'ai-awareness-day' ), __( 'Should we have the right to know what data an AI was trained on?', 'ai-awareness-day' ), 'further' ),
		array( __( 'Supervised Agent', 'ai-awareness-day' ), array( 'Ag', 'Fn', 'Hi' ), 'responsible', false, __( 'An agent that uses tools to get work done, but pauses for human approval before anything important happens.', 'ai-awareness-day' ), __( 'An agent that drafts a school trip plan and booking, then waits for a teacher to approve it.', 'ai-awareness-day' ), __( 'A human signs off every consequential action.', 'ai-awareness-day' ), __( 'Which decisions should an AI agent never make without asking first?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Unchecked Agent', 'ai-awareness-day' ), array( 'Ag', 'Fn', 'Au' ), 'smart', true, __( 'An agent given tools and full autonomy, acting without anyone checking what it does.', 'ai-awareness-day' ), __( 'An agent with access to your email that replies to people and buys things on its own.', 'ai-awareness-day' ), __( 'None, and that is the risk. Swap Autonomy (Au) for Human-in-the-Loop (Hi) to stabilise it.', 'ai-awareness-day' ), __( 'If an AI agent makes a costly mistake, who is responsible?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Personal Assistant Agent', 'ai-awareness-day' ), array( 'Ag', 'Mc', 'Pl', 'Me' ), 'safe', false, __( 'An agent connected to your apps that plans tasks and remembers your preferences over time.', 'ai-awareness-day' ), __( 'An assistant that manages your calendar, drafts emails and learns your routines.', 'ai-awareness-day' ), __( 'You decide what it can access and what it is allowed to remember.', 'ai-awareness-day' ), __( 'Should each person manage what AI remembers about them?', 'ai-awareness-day' ), 'further' ),
		array( __( 'Digital Errand Runner', 'ai-awareness-day' ), array( 'Co', 'Br', 'Ag' ), 'smart', false, __( 'An agent that operates a web browser like a person to complete online tasks.', 'ai-awareness-day' ), __( 'Comparing train tickets and filling in the booking form for you.', 'ai-awareness-day' ), __( 'You confirm before it pays for or submits anything.', 'ai-awareness-day' ), __( 'Would you let an AI spend your money?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Agent Team', 'ai-awareness-day' ), array( 'Ma', 'Or', 'Aa' ), 'smart', false, __( 'Specialised agents coordinated by an orchestrator, passing work between each other.', 'ai-awareness-day' ), __( 'One agent researches, another writes and a third fact-checks a report.', 'ai-awareness-day' ), __( 'A person sets the goal and signs off the final output.', 'ai-awareness-day' ), __( 'If agents check each other’s work, do we still need a human checker?', 'ai-awareness-day' ), 'further' ),
		array( __( 'AI Pair Programmer', 'ai-awareness-day' ), array( 'Ca', 'Vc', 'Ev' ), 'future', false, __( 'A coding agent that builds software from plain-language requests, with tests to check the result.', 'ai-awareness-day' ), __( 'Building a revision quiz app just by describing what it should do.', 'ai-awareness-day' ), __( 'A developer reviews, tests and understands the code before it ships.', 'ai-awareness-day' ), __( 'If AI writes the code, which coding skills still need to stay human?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'AI Art Generator', 'ai-awareness-day' ), array( 'Df', 'Ti', 'Ip' ), 'creative', false, __( 'A diffusion model that turns text prompts into images, raising real questions about who owns the result.', 'ai-awareness-day' ), __( 'Creating poster artwork for the school play from a written description.', 'ai-awareness-day' ), __( 'A person directs, selects and edits the image, and credits the AI honestly.', 'ai-awareness-day' ), __( 'If AI made the picture from your prompt, who really made it?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Labelled AI Image', 'ai-awareness-day' ), array( 'Ti', 'Wm' ), 'creative', false, __( 'An AI-generated picture carrying a hidden watermark, so it can be identified as AI-made later.', 'ai-awareness-day' ), __( 'A social media app adding an “AI-generated” label to a picture.', 'ai-awareness-day' ), __( 'Creators label their work honestly, and platforms check for the watermark.', 'ai-awareness-day' ), __( 'Should every AI-made image have to carry a label?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Voice Clone', 'ai-awareness-day' ), array( 'Ga', 'Ts', 'Dk' ), 'safe', true, __( 'Generative AI that copies a real person’s voice from a short recording. The same technology can help or harm: it depends on consent and on who is in control.', 'ai-awareness-day' ), __( 'Harm: scam calls that imitate a family member asking urgently for money. Help: people losing their voice to illness recording it first, so they can keep speaking in their own voice.', 'ai-awareness-day' ), __( 'Only clone a voice with its owner’s consent. Agree a family “safe word” and check by another route before acting on an urgent call.', 'ai-awareness-day' ), __( 'Voice cloning can scam a family or give someone their voice back. Should it be banned, or controlled?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'AI Call Translator', 'ai-awareness-day' ), array( 'Np', 'Ts', 'Ga' ), 'future', false, __( 'Translates what you say during a call and speaks it in the other language, in a synthetic copy of your own voice, made with your consent.', 'ai-awareness-day' ), __( 'A parent phoning school in Polish, and staff hearing them in English, in the parent’s own voice.', 'ai-awareness-day' ), __( 'The speaker agrees to their voice being copied, and the call makes clear the voice is AI-translated.', 'ai-awareness-day' ), __( 'If a copy of your voice is made with your consent, is it still a deepfake? What makes the difference?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Viral Fake Video', 'ai-awareness-day' ), array( 'Gv', 'Dk', 'Sp' ), 'safe', true, __( 'Realistic AI video of something that never happened, mass-produced to spread on social media.', 'ai-awareness-day' ), __( 'A fake clip of a celebrity or politician saying something they never said.', 'ai-awareness-day' ), __( 'Pause and check where a video came from before you share it, and report fakes.', 'ai-awareness-day' ), __( 'Should platforms remove deepfakes, or just label them?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Social Media Feed', 'ai-awareness-day' ), array( 'Rc', 'Pa' ), 'smart', false, __( 'A recommendation engine that predicts what you are most likely to watch or click, and shows you more of it.', 'ai-awareness-day' ), __( 'The “For you” page on a video app.', 'ai-awareness-day' ), __( 'You tell it what you are not interested in, and choose when to scroll away.', 'ai-awareness-day' ), __( 'Do you choose what you watch, or does the app?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Filter Bubble', 'ai-awareness-day' ), array( 'Rc', 'Bi' ), 'responsible', true, __( 'A recommendation engine that learns what keeps you watching and serves more and more of the same.', 'ai-awareness-day' ), __( 'A video feed that can drift towards more extreme content.', 'ai-awareness-day' ), __( 'Users can reset or switch off personalisation, and platforms audit their feeds for harm.', 'ai-awareness-day' ), __( 'Should social media apps let you turn the algorithm off?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Automated CV Screener', 'ai-awareness-day' ), array( 'Cl', 'Np', 'Bi' ), 'responsible', true, __( 'A classifier that reads job applications and sorts candidates, and can inherit bias from past hiring decisions.', 'ai-awareness-day' ), __( 'Software that ranks applicants before any person reads their application.', 'ai-awareness-day' ), __( 'A person reviews rejections and audits the results for unfair patterns.', 'ai-awareness-day' ), __( 'Should AI decide who gets a job interview?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Accountable Decision System', 'ai-awareness-day' ), array( 'Cl', 'Xa', 'Fa', 'Hi' ), 'responsible', false, __( 'A predictive system that explains its reasoning, is checked for fairness and keeps a person in charge.', 'ai-awareness-day' ), __( 'An appeals tool where every decision can be explained, challenged and overturned.', 'ai-awareness-day' ), __( 'A named person can review and overturn any decision.', 'ai-awareness-day' ), __( 'Do you have a right to know why an AI decided something about you?', 'ai-awareness-day' ), 'further' ),
		array( __( 'Customer Service Bot', 'ai-awareness-day' ), array( 'Cb', 'Sa', 'Hi' ), 'responsible', false, __( 'A chatbot that uses sentiment analysis to notice when a customer is getting upset, and hands them over to a person.', 'ai-awareness-day' ), __( 'An online shop’s help chat passing you to a human when it cannot sort out a refund.', 'ai-awareness-day' ), __( 'A trained member of staff takes over the difficult conversations.', 'ai-awareness-day' ), __( 'Should companies always tell you whether you are talking to a bot or a person?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Spam Filter', 'ai-awareness-day' ), array( 'Su', 'Cl', 'Np' ), 'safe', false, __( 'A classifier trained on labelled examples of junk mail, which sorts new emails into inbox or spam.', 'ai-awareness-day' ), __( 'Phishing emails pretending to be from your school landing in the junk folder.', 'ai-awareness-day' ), __( 'You mark its mistakes as “spam” or “not spam”, which teaches it.', 'ai-awareness-day' ), __( 'Is it worse to miss a real email or to let a scam through?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Bank Fraud Alert', 'ai-awareness-day' ), array( 'Ad', 'Tm' ), 'safe', false, __( 'Anomaly detection that learns your usual spending over time, and flags payments that do not fit.', 'ai-awareness-day' ), __( 'A text asking “Was this you?” after an unusual card payment.', 'ai-awareness-day' ), __( 'You confirm or deny the payment, and the bank investigates.', 'ai-awareness-day' ), __( 'How much should a bank know about how you spend your money?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Accidental Data Leak', 'ai-awareness-day' ), array( 'Sh', 'Pv' ), 'safe', true, __( 'Private information pasted into an AI tool that has not been approved, where it may be stored or used for training.', 'ai-awareness-day' ), __( 'Copying a class list with names and grades into a free chatbot to tidy it up.', 'ai-awareness-day' ), __( 'Use only approved tools, and remove names and personal details first.', 'ai-awareness-day' ), __( 'Should schools ban free AI tools, or teach people to use them safely?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Jailbreak-Resistant Chatbot', 'ai-awareness-day' ), array( 'Jb', 'Gr', 'Rt' ), 'safe', false, __( 'A chatbot whose guardrails have been tested against jailbreak attempts by red teams, and strengthened where they failed.', 'ai-awareness-day' ), __( 'A company inviting testers to try to trick its chatbot before launch.', 'ai-awareness-day' ), __( 'Safety teams keep testing and fixing it after release.', 'ai-awareness-day' ), __( 'Why do companies test their chatbots with trained, authorised testers rather than leaving it to the public?', 'ai-awareness-day' ), 'further' ),
		array( __( 'AI Safety Lab', 'ai-awareness-day' ), array( 'Rt', 'Gr', 'Ev', 'Sf' ), 'responsible', false, __( 'Testing, attacking and measuring a model before release to find and fix harmful behaviour.', 'ai-awareness-day' ), __( 'Red teamers trying to trick a chatbot into giving dangerous advice, so it can be fixed.', 'ai-awareness-day' ), __( 'People set the standards a model must meet before release.', 'ai-awareness-day' ), __( 'Who should decide whether an AI model is safe enough to release?', 'ai-awareness-day' ), 'further' ),
		array( __( 'Demand Forecaster', 'ai-awareness-day' ), array( 'Fc', 'Tm', 'Pa' ), 'smart', false, __( 'Predictive models that use past patterns to estimate what will be needed next.', 'ai-awareness-day' ), __( 'Predicting how many school lunches to cook each day to cut food waste.', 'ai-awareness-day' ), __( 'Staff adjust for things the data cannot know, like a trip day or a snow day.', 'ai-awareness-day' ), __( 'How much should we trust a prediction we cannot explain?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Early Warning System', 'ai-awareness-day' ), array( 'Ch', 'Pa', 'Ps' ), 'responsible', false, __( 'Analytics that flag people likely to disengage or drop out, and suggest how to help.', 'ai-awareness-day' ), __( 'Spotting students whose attendance patterns suggest they may need support.', 'ai-awareness-day' ), __( 'A trusted adult decides what, if anything, to do. The system only flags.', 'ai-awareness-day' ), __( 'Should schools act on AI predictions about individual students?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Win-Back Offer', 'ai-awareness-day' ), array( 'Ch', 'Pa' ), 'responsible', false, __( 'Churn prediction spots who is about to cancel a subscription, so they can be sent a deal to stay.', 'ai-awareness-day' ), __( 'A discount appearing the moment you click “cancel membership”.', 'ai-awareness-day' ), __( 'People decide which offers are fair, and you decide whether to stay.', 'ai-awareness-day' ), __( 'Is it fair to use AI to predict when you are about to leave?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Paperless Back Office', 'ai-awareness-day' ), array( 'Id', 'Oc', 'Rp' ), 'future', false, __( 'Documents are scanned, read and entered into systems automatically, with no retyping.', 'ai-awareness-day' ), __( 'Invoices and permission slips processed straight into school systems.', 'ai-awareness-day' ), __( 'Staff handle the exceptions the system flags as unclear.', 'ai-awareness-day' ), __( 'Which school admin jobs should be automated, and which should not?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'No-Code Workflow', 'ai-awareness-day' ), array( 'Lc', 'Tg', 'Wh', 'Ap' ), 'future', false, __( 'A visual automation where one event triggers actions across several apps.', 'ai-awareness-day' ), __( 'A form submission that updates a spreadsheet and emails a confirmation automatically.', 'ai-awareness-day' ), __( 'A person designs the flow and checks it whenever something changes.', 'ai-awareness-day' ), __( 'Should everyone learn to build automations, not just programmers?', 'ai-awareness-day' ), 'further' ),
		array( __( 'Hyperautomated Office', 'ai-awareness-day' ), array( 'Hy', 'Mi', 'Dw' ), 'future', false, __( 'Process mining finds the work worth automating, and digital workers take it on across the organisation.', 'ai-awareness-day' ), __( 'An organisation mapping its processes and handing repetitive steps to bots.', 'ai-awareness-day' ), __( 'People redesign roles and keep the judgement calls.', 'ai-awareness-day' ), __( 'If digital workers take on routine tasks, what should people do with the time?', 'ai-awareness-day' ), 'further' ),
		array( __( 'Smart Speaker', 'ai-awareness-day' ), array( 'Va', 'Np', 'Ts' ), 'safe', false, __( 'A virtual assistant that listens, works out what you mean and talks back.', 'ai-awareness-day' ), __( 'Asking for a timer, the weather or a song while you cook.', 'ai-awareness-day' ), __( 'You choose where it sits, when the microphone is on and what it can control.', 'ai-awareness-day' ), __( 'Should smart speakers be allowed in classrooms?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Accessibility Assistant', 'ai-awareness-day' ), array( 'Mm', 'Cv', 'Ts' ), 'future', false, __( 'A multimodal model that sees through a camera and describes the world out loud.', 'ai-awareness-day' ), __( 'An app that reads signs and describes surroundings for blind and partially sighted people.', 'ai-awareness-day' ), __( 'The user decides when to rely on it and when to double-check.', 'ai-awareness-day' ), __( 'Should accessibility be a priority for every AI product?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Live Translator', 'ai-awareness-day' ), array( 'Np', 'Mm', 'Ts' ), 'future', false, __( 'Understands speech or text in one language and speaks it in another.', 'ai-awareness-day' ), __( 'A new student and their family talking with school staff in two languages.', 'ai-awareness-day' ), __( 'A professional translator checks anything important, such as medical or legal information.', 'ai-awareness-day' ), __( 'Will AI translation mean fewer people learn languages? Is that a loss?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Face Unlock', 'ai-awareness-day' ), array( 'Cv', 'Nn', 'Ed' ), 'safe', false, __( 'A neural network that recognises your face, running on the device so the image never has to leave it.', 'ai-awareness-day' ), __( 'Unlocking a phone by looking at it.', 'ai-awareness-day' ), __( 'You choose whether to switch it on, and keep a passcode as a back-up.', 'ai-awareness-day' ), __( 'Should schools use face recognition for lunch payments or registration?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'On-Device AI', 'ai-awareness-day' ), array( 'Ft', 'Sm', 'Ed' ), 'safe', false, __( 'A small, fine-tuned model that runs on your own phone, so your data does not have to leave the device.', 'ai-awareness-day' ), __( 'Predictive text and photo search that work even when you are offline.', 'ai-awareness-day' ), __( 'You control the device and what it is allowed to do.', 'ai-awareness-day' ), __( 'Is AI on your phone more trustworthy than AI in the cloud?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Self-Driving Car', 'ai-awareness-day' ), array( 'Cv', 'Su', 'Ed' ), 'future', false, __( 'Computer vision to see the road, machine learning trained on millions of miles of recorded and simulated driving, and edge AI to decide in a split second on board.', 'ai-awareness-day' ), __( 'Robotaxis in some cities, and lane-keeping and emergency braking in ordinary cars.', 'ai-awareness-day' ), __( 'Safety drivers, remote operators and regulators decide where it may drive.', 'ai-awareness-day' ), __( 'If a self-driving car crashes, who is to blame?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Robot Vacuum', 'ai-awareness-day' ), array( 'Cv', 'Pl', 'Ed' ), 'future', false, __( 'Sees the room, plans a route and adjusts on board as it goes.', 'ai-awareness-day' ), __( 'A robot vacuum mapping a house and steering around furniture.', 'ai-awareness-day' ), __( 'You set no-go zones and decide whether its map of your home is shared.', 'ai-awareness-day' ), __( 'Which household jobs would you hand to a robot, and which would you not?', 'ai-awareness-day' ), 'secondary' ),
		array( __( 'Game-Playing Champion', 'ai-awareness-day' ), array( 'Rl', 'Tp', 'Sl' ), 'future', false, __( 'Reinforcement learning at huge scale, discovering strategies through millions of practice games.', 'ai-awareness-day' ), __( 'AlphaGo, which learned partly by playing millions of games against itself, beating Go world champion Lee Sedol in 2016.', 'ai-awareness-day' ), __( 'Humans design the goals and the rewards the system learns from.', 'ai-awareness-day' ), __( 'If AI can beat us at a game, is the game still worth playing?', 'ai-awareness-day' ), 'further' ),
		array( __( 'AI Data Centre', 'ai-awareness-day' ), array( 'Gp', 'Cd', 'Sl' ), 'responsible', false, __( 'Buildings full of AI chips that power cloud AI, using large amounts of electricity and water.', 'ai-awareness-day' ), __( 'The hidden infrastructure behind every chatbot reply and generated image.', 'ai-awareness-day' ), __( 'Companies and policymakers decide where to build and how to report the impact.', 'ai-awareness-day' ), __( 'Should AI companies have to publish their energy and water use?', 'ai-awareness-day' ), 'further' ),
		// Primary: words children already know, two elements each.
		array( __( 'Confident but Wrong', 'ai-awareness-day' ), array( 'Cb', 'Ha' ), 'smart', true, __( 'A chatbot giving an answer that sounds sure of itself but is made up.', 'ai-awareness-day' ), __( 'A helper app tells you a “fact” about your town that isn’t true.', 'ai-awareness-day' ), __( 'You check it in a book, on a trusted website or with an adult.', 'ai-awareness-day' ), __( 'How can you tell when a chatbot might be making something up?', 'ai-awareness-day' ), 'primary' ),
		array( __( 'What to Watch Next', 'ai-awareness-day' ), array( 'Rc', 'Al' ), 'safe', false, __( 'An algorithm that suggests videos or songs based on what you watched before.', 'ai-awareness-day' ), __( 'The row of videos that appears after the one you just finished.', 'ai-awareness-day' ), __( 'You choose what to watch, and when to stop.', 'ai-awareness-day' ), __( 'Should an app choose what you watch next, or should you?', 'ai-awareness-day' ), 'primary' ),
		array( __( 'Voice Helper', 'ai-awareness-day' ), array( 'Va', 'Ts' ), 'safe', false, __( 'A virtual assistant that listens to your question and talks back out loud.', 'ai-awareness-day' ), __( 'Asking a speaker in the kitchen what the weather will be.', 'ai-awareness-day' ), __( 'A grown-up decides what it can do and when it can listen.', 'ai-awareness-day' ), __( 'Should a voice helper always be listening? Why, or why not?', 'ai-awareness-day' ), 'primary' ),
		array( __( 'Photo Sorter', 'ai-awareness-day' ), array( 'Cv', 'Cl' ), 'smart', false, __( 'AI that looks at photos and sorts them by what is in them.', 'ai-awareness-day' ), __( 'A phone gathering all your photos of dogs into one album.', 'ai-awareness-day' ), __( 'You move a photo when it ends up in the wrong place.', 'ai-awareness-day' ), __( 'What might a photo sorter get wrong, and why?', 'ai-awareness-day' ), 'primary' ),
		array( __( 'Picture Maker', 'ai-awareness-day' ), array( 'Ai', 'Ti' ), 'creative', false, __( 'AI that makes a picture from the words you type.', 'ai-awareness-day' ), __( 'Typing “a cat in a spacesuit” and getting a brand-new picture.', 'ai-awareness-day' ), __( 'With your teacher, using a school-approved tool: you decide if it is any good, and say it was made with AI.', 'ai-awareness-day' ), __( 'If AI made the picture, who is the artist?', 'ai-awareness-day' ), 'primary' ),
		array( __( 'Think Before You Share', 'ai-awareness-day' ), array( 'Cb', 'Pv' ), 'safe', true, __( 'Typing private things, like your address or a password, into a chatbot.', 'ai-awareness-day' ), __( 'Telling a chatbot your full name and which school you go to.', 'ai-awareness-day' ), __( 'Ask a trusted adult before you share anything personal online.', 'ai-awareness-day' ), __( 'What should you never tell a chatbot?', 'ai-awareness-day' ), 'primary' ),
		array( __( 'Teaching a Computer', 'ai-awareness-day' ), array( 'Ml', 'Tr' ), 'smart', false, __( 'A computer learning from lots of examples, instead of being told the rules.', 'ai-awareness-day' ), __( 'Showing a computer hundreds of pictures of apples until it can spot one on its own.', 'ai-awareness-day' ), __( 'People choose the examples and check what it has learned.', 'ai-awareness-day' ), __( 'What would go wrong if every example apple was red?', 'ai-awareness-day' ), 'primary' ),
		array( __( 'Human in Charge', 'ai-awareness-day' ), array( 'Ai', 'Hi' ), 'responsible', false, __( 'AI that suggests an answer, with a person who checks it and makes the final choice.', 'ai-awareness-day' ), __( 'A teacher reading an AI’s marking suggestions before giving out marks.', 'ai-awareness-day' ), __( 'A person always has the final say.', 'ai-awareness-day' ), __( 'Which decisions should always be made by a person?', 'ai-awareness-day' ), 'primary' ),
	);
	$compounds = array();
	foreach ( $raw as $c ) {
		$ids = array_map( 'strtolower', $c[1] );
		sort( $ids );
		$compounds[] = array(
			'id'       => implode( '-', $ids ),
			'name'     => $c[0],
			'syms'     => $c[1],
			'strand'   => $c[2],
			'volatile' => $c[3],
			'what'     => $c[4],
			'example'  => $c[5],
			'loop'     => $c[6],
			'debate'   => $c[7],
			'level'    => $c[8],
		);
	}
	return $compounds;
}

/**
 * The table: the family filter and search, the 18-column grid with the detail panel in its gap, the compound
 * combinations, the compound challenge, and every definition, family by family, below it (what the tiles link to without JavaScript, and what search engines read).
 */
function aiad_render_literacy_table(): string {
	$families = aiad_literacy_families();
	$elements = aiad_literacy_elements();
	$total    = count( $elements );
	$order    = array_flip( array_keys( $families ) );
	$first    = $elements[0];
	$counts   = array_count_values( array_column( $elements, 'family' ) );
	$compounds = aiad_literacy_compounds();
	$by_sym   = array_column( $elements, null, 'sym' );
	$strands  = aiad_literacy_strands();
	$bench_url = aiad_literacy_benchmark_url();
	$issues    = aiad_literacy_issues();
	$el_issues = aiad_literacy_element_issues();
	$talk      = aiad_literacy_talk();
	$levels   = aiad_literacy_levels();
	$theme_order  = array_flip( array_keys( $strands ) );
	$theme_counts = array_count_values( array_column( $elements, 'theme' ) );
	$level_counts = array_count_values( array_column( $compounds, 'level' ) );
	$eras         = aiad_literacy_eras();
	$era_order    = array_flip( array_keys( $eras ) );
	$era_counts   = array_count_values( array_column( $elements, 'era' ) );
	$years        = array_values( array_unique( array_column( $elements, 'year' ) ) );
	sort( $years );

	ob_start();
	?>
<div class="alpt" id="alpt-explore" data-alpt>
	<div class="alpt-controls" data-alpt-controls hidden>
		<div class="alpt-view" role="group" aria-label="<?php esc_attr_e( 'Colour the table by', 'ai-awareness-day' ); ?>">
			<span class="alpt-view__label"><?php esc_html_e( 'View by', 'ai-awareness-day' ); ?></span>
			<button type="button" class="alpt-view__option" data-alpt-view="families" aria-pressed="true"><?php esc_html_e( 'Technical terms', 'ai-awareness-day' ); ?></button>
			<button type="button" class="alpt-view__option" data-alpt-view="themes" aria-pressed="false"><?php esc_html_e( 'AIAD terms', 'ai-awareness-day' ); ?></button>
			<button type="button" class="alpt-view__option" data-alpt-view="years" aria-pressed="false"><?php esc_html_e( 'Year', 'ai-awareness-day' ); ?></button>
		</div>
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
		<div class="alpt-legend alpt-legend--themes" role="group" aria-label="<?php esc_attr_e( 'Show one theme', 'ai-awareness-day' ); ?>">
			<?php foreach ( $strands as $key => $label ) : ?>
				<button type="button" class="alpt-chip alpt-th--<?php echo esc_attr( $key ); ?>" data-alpt-theme-filter="<?php echo esc_attr( $key ); ?>" aria-pressed="false">
					<span class="alpt-chip__swatch" aria-hidden="true"></span>
					<span><?php echo esc_html( $label ); ?></span>
					<span class="alpt-chip__count"><?php echo esc_html( (string) ( $theme_counts[ $key ] ?? 0 ) ); ?></span>
				</button>
			<?php endforeach; ?>
			<button type="button" class="alpt-chip alpt-chip--all" data-alpt-theme-filter="" aria-pressed="true"><?php esc_html_e( 'All themes', 'ai-awareness-day' ); ?></button>
		</div>
		<div class="alpt-timeline" data-alpt-timeline>
			<label class="alpt-timeline__label" for="alpt-year-range"><?php esc_html_e( 'Words in use by', 'ai-awareness-day' ); ?> <output class="alpt-timeline__year" data-alpt-year-out for="alpt-year-range"><?php echo esc_html( (string) end( $years ) ); ?></output></label>
			<div class="alpt-timeline__row">
				<button type="button" class="alpt-btn alpt-btn--ink alpt-timeline__play" data-alpt-play><span class="alpt-timeline__play-icon" aria-hidden="true"></span><span data-alpt-play-text><?php esc_html_e( 'Play', 'ai-awareness-day' ); ?></span></button>
				<input type="range" id="alpt-year-range" class="alpt-timeline__range" data-alpt-year-range min="0" max="<?php echo esc_attr( (string) ( count( $years ) - 1 ) ); ?>" step="1" value="<?php echo esc_attr( (string) ( count( $years ) - 1 ) ); ?>" data-years="<?php echo esc_attr( implode( ',', $years ) ); ?>">
			</div>
			<p class="alpt-timeline__note"><?php esc_html_e( 'Drag the slider, or press Play, to watch the words arrive. Dates show when each term was in use in AI, not necessarily the very first time anyone said it; "about" means it came into use gradually. Some words, like Bias and Privacy, are much older: their year is when they took on their AI meaning.', 'ai-awareness-day' ); ?> <a href="#alpt-suggest-title"><?php esc_html_e( 'Know an earlier date? Tell us.', 'ai-awareness-day' ); ?></a></p>
		</div>
		<div class="alpt-legend alpt-legend--eras" role="group" aria-label="<?php esc_attr_e( 'Show one era', 'ai-awareness-day' ); ?>">
			<?php foreach ( $eras as $key => $era ) : ?>
				<button type="button" class="alpt-chip alpt-era--<?php echo esc_attr( $key ); ?>" data-alpt-era-filter="<?php echo esc_attr( $key ); ?>" aria-pressed="false">
					<span class="alpt-chip__swatch" aria-hidden="true"></span>
					<span><?php echo esc_html( $era['name'] ); ?></span>
					<span class="alpt-chip__count"><?php echo esc_html( (string) ( $era_counts[ $key ] ?? 0 ) ); ?></span>
				</button>
			<?php endforeach; ?>
			<button type="button" class="alpt-chip alpt-chip--all" data-alpt-era-filter="" aria-pressed="true"><?php esc_html_e( 'All years', 'ai-awareness-day' ); ?></button>
		</div>
		<button type="button" class="alpt-btn" data-alpt-clear-search hidden><?php esc_html_e( 'Clear search', 'ai-awareness-day' ); ?></button>
		<a class="alpt-print-cards alpt-print-poster" href="#alpt-print"><?php esc_html_e( 'Print the poster', 'ai-awareness-day' ); ?></a>
		<button type="button" class="alpt-print-cards" data-alpt-print-cards hidden><?php esc_html_e( 'Print these as cards', 'ai-awareness-day' ); ?></button>
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
					echo esc_html( sprintf( __( '%d AI terms in seven families. Read every definition at aiawarenessday.co.uk/ai-literacy-periodic-table', 'ai-awareness-day' ), $total ) );
					?>
				</p>
				<ul class="alpt-panel__print-key">
					<?php foreach ( $families as $key => $family ) : ?>
						<li class="alpt-fam--<?php echo esc_attr( $key ); ?> alpt-key-family"><span class="alpt-chip__swatch"></span><?php echo esc_html( $family['name'] ); ?></li>
					<?php endforeach; ?>
					<?php foreach ( $strands as $key => $label ) : ?>
						<li class="alpt-th--<?php echo esc_attr( $key ); ?> alpt-key-theme"><span class="alpt-chip__swatch"></span><?php echo esc_html( $label ); ?></li>
					<?php endforeach; ?>
					<?php foreach ( $eras as $key => $era ) : ?>
						<li class="alpt-era--<?php echo esc_attr( $key ); ?> alpt-key-year"><span class="alpt-chip__swatch"></span><?php echo esc_html( $era['name'] ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="alpt-panel__tile" aria-hidden="true">
				<span class="alpt-panel__n" data-alpt-out="n"><?php echo esc_html( (string) $first['n'] ); ?></span>
				<span class="alpt-panel__sym" data-alpt-out="sym"><?php echo esc_html( $first['sym'] ); ?></span>
				<span class="alpt-panel__tile-name" data-alpt-out="name"><?php echo esc_html( $first['name'] ); ?></span>
			</div>
			<div class="alpt-panel__body" data-alpt-live><?php // Not a live region: it would re-read the whole panel. The script announces the element through a short line instead. ?>
				<p class="alpt-panel__labels"><span class="alpt-panel__family" data-alpt-out="family"><?php echo esc_html( $families[ $first['family'] ]['name'] ); ?></span> <span class="alpt-panel__theme alpt-th--<?php echo esc_attr( $first['theme'] ); ?>" data-alpt-theme-label><?php echo esc_html( sprintf( /* translators: %s: theme name, such as Safe */ __( '%s theme', 'ai-awareness-day' ), $strands[ $first['theme'] ] ?? '' ) ); ?></span> <span class="alpt-panel__year" data-alpt-out="year"><?php echo esc_html( aiad_literacy_year_label( $first ) ); ?></span></p>
				<h2 class="alpt-panel__title" data-alpt-out="title"><?php echo esc_html( $first['name'] ); ?></h2>
				<p class="alpt-panel__def" data-alpt-out="def"><?php echo esc_html( $first['def'] ); ?></p>
				<details class="alpt-reader-notes">
					<summary><?php esc_html_e( 'Classroom notes & resources', 'ai-awareness-day' ); ?></summary>
				<div class="alpt-panel__issues" data-alpt-issues-out>
					<p class="alpt-panel__issues-label"><?php esc_html_e( 'Issues it raises', 'ai-awareness-day' ); ?> <span class="alpt-panel__issues-hint"><?php esc_html_e( '· choose one to read about it', 'ai-awareness-day' ); ?></span></p>
					<?php // The script fills in the issues and the question (theme pages drop empty elements). ?>
				</div>
				<p class="alpt-panel__res" data-alpt-res hidden><span class="alpt-panel__bench-label"><?php esc_html_e( 'Learn more:', 'ai-awareness-day' ); ?></span> <span data-alpt-res-list>&nbsp;</span></p>
				<p class="alpt-panel__bench"><span class="alpt-panel__bench-label"><?php esc_html_e( 'Benchmark:', 'ai-awareness-day' ); ?></span> <a href="<?php echo esc_url( aiad_literacy_benchmark_url() ); ?>" data-alpt-bench><?php echo esc_html( aiad_literacy_benchmark_domain( $first['sym'] ) ); ?></a></p>
				</details>
				<div class="alpt-panel__links" data-alpt-links-out hidden>
					<p class="alpt-panel__links-label"><?php esc_html_e( 'Connected to', 'ai-awareness-day' ); ?></p>
					<?php // The script fills in the list and the compounds line (theme pages drop empty elements). ?>
				</div>
			</div>

			<div class="alpt-panel__nav" data-alpt-nav hidden>
				<button type="button" class="alpt-btn alpt-step alpt-step--prev" data-alpt-step="-1"><span aria-hidden="true">&larr;</span> <?php esc_html_e( 'Previous', 'ai-awareness-day' ); ?></button>
				<button type="button" class="alpt-btn alpt-btn--ink alpt-links-toggle" data-alpt-links aria-pressed="false"><span class="alpt-links-toggle__icon" aria-hidden="true"></span><?php esc_html_e( 'Connections', 'ai-awareness-day' ); ?></button>
				<button type="button" class="alpt-btn alpt-board-btn" data-alpt-board-element><span class="alpt-board-btn__icon" aria-hidden="true"></span><?php esc_html_e( 'Show to class', 'ai-awareness-day' ); ?></button>
				<button type="button" class="alpt-btn alpt-copy-link" data-alpt-copy hidden><?php esc_html_e( 'Copy link', 'ai-awareness-day' ); ?></button>

			<button type="button" class="alpt-btn alpt-back-to-tiles" data-alpt-dismiss><?php esc_html_e( 'Back to elements', 'ai-awareness-day' ); ?></button>
				<span class="alpt-panel__count">
					<?php
					/* translators: 1: element number, 2: number of elements */
					printf( esc_html__( 'Element %1$s of %2$s', 'ai-awareness-day' ), '<span data-alpt-out="n2">' . esc_html( (string) $first['n'] ) . '</span>', esc_html( (string) $total ) );
					?>
				</span>
				<button type="button" class="alpt-btn alpt-step alpt-step--next" data-alpt-step="1"><?php esc_html_e( 'Next', 'ai-awareness-day' ); ?> <span aria-hidden="true">&rarr;</span></button>
			</div>
		</div>

		<div class="alpt-key" aria-hidden="true">
			<div class="alpt-tile alpt-tile--key">
				<span class="alpt-tile__n">1</span>
				<span class="alpt-tile__sym">Ai</span>
				<span class="alpt-tile__name"><?php echo esc_html( aiad_literacy_tile_name( __( 'Artificial Intelligence', 'ai-awareness-day' ) ) ); ?></span>
			</div>
			<div class="alpt-key__text">
				<p class="alpt-key__title"><?php esc_html_e( 'How to read a tile', 'ai-awareness-day' ); ?></p>
				<p><?php esc_html_e( 'Top: its number, in reading order', 'ai-awareness-day' ); ?></p>
				<p><?php esc_html_e( 'Middle: its symbol. Bottom: the term', 'ai-awareness-day' ); ?></p>
				<p class="alpt-key-family"><?php esc_html_e( 'Colour: the family it belongs to', 'ai-awareness-day' ); ?></p>
				<p class="alpt-key-theme"><?php esc_html_e( 'Colour: its AI Awareness Day theme', 'ai-awareness-day' ); ?></p>
				<p class="alpt-key-year"><?php esc_html_e( 'Colour: when the word came into use', 'ai-awareness-day' ); ?></p>
			</div>
		</div>

		<?php foreach ( $families as $key => $family ) : ?>
			<p class="alpt-family-label alpt-fam--<?php echo esc_attr( $key ); ?>" style="--o:<?php echo esc_attr( (string) ( $order[ $key ] * 1000 ) ); ?>">
				<span class="alpt-family-label__name"><?php echo esc_html( $family['name'] ); ?></span>
				<span class="alpt-family-label__about"><?php echo esc_html( $family['about'] ); ?></span>
			</p>
		<?php endforeach; ?>
		<?php foreach ( $strands as $key => $label ) : ?>
			<p class="alpt-family-label alpt-theme-label alpt-th--<?php echo esc_attr( $key ); ?>" style="--o2:<?php echo esc_attr( (string) ( $theme_order[ $key ] * 1000 ) ); ?>">
				<span class="alpt-family-label__name"><?php echo esc_html( sprintf( /* translators: %s: theme name, such as Safe */ __( '%s theme', 'ai-awareness-day' ), $label ) ); ?></span>
			</p>
		<?php endforeach; ?>

		<?php foreach ( $eras as $key => $era ) : ?>
			<p class="alpt-family-label alpt-era-label alpt-era--<?php echo esc_attr( $key ); ?>" style="--o3:<?php echo esc_attr( (string) ( $era_order[ $key ] * 100000 ) ); ?>">
				<span class="alpt-family-label__name"><?php echo esc_html( $era['name'] ); ?></span>
			</p>
		<?php endforeach; ?>

		<?php foreach ( $elements as $el ) : ?>
			<a class="alpt-tile alpt-fam--<?php echo esc_attr( $el['family'] ); ?>" href="#<?php echo esc_attr( $el['id'] ); ?>" data-alpt-el="<?php echo esc_attr( (string) ( $el['n'] - 1 ) ); ?>" data-alpt-theme="<?php echo esc_attr( $el['theme'] ); ?>" data-alpt-era="<?php echo esc_attr( $el['era'] ); ?>" data-alpt-year="<?php echo esc_attr( (string) $el['year'] ); ?>" style="--o3:<?php echo esc_attr( (string) ( $era_order[ $el['era'] ] * 100000 + $el['year'] * 10 + 1 ) ); ?>;--r:<?php echo esc_attr( (string) $el['row'] ); ?>;--c:<?php echo esc_attr( (string) $el['col'] ); ?>;--o:<?php echo esc_attr( (string) ( $order[ $el['family'] ] * 1000 + $el['n'] ) ); ?>;--o2:<?php echo esc_attr( (string) ( ( $theme_order[ $el['theme'] ] ?? 0 ) * 1000 + $el['n'] ) ); ?>">
				<span class="alpt-tile__n"><?php echo esc_html( (string) $el['n'] ); ?></span>
				<span class="alpt-tile__sym"><?php echo esc_html( $el['sym'] ); ?></span>
				<span class="alpt-tile__name"><?php echo esc_html( aiad_literacy_tile_name( $el['name'] ) ); ?></span>
				<span class="screen-reader-text">, <?php echo esc_html( $families[ $el['family'] ]['name'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>

	<p class="alpt-footnote"><?php esc_html_e( 'Left: foundations. Centre: generative and predictive AI. Right: autonomous agents and automation. Below: the governance and infrastructure that hold it all together. Numbers run left to right, top to bottom.', 'ai-awareness-day' ); ?></p>

	<div class="alpt-tryit" id="alpt-tryit">
		<p class="alpt-sticker"><?php esc_html_e( 'Pick one · 5 to 50 minutes', 'ai-awareness-day' ); ?></p>
		<h2 class="alpt-tryit__title"><?php esc_html_e( 'Try it', 'ai-awareness-day' ); ?></h2>
		<p class="alpt-tryit__lead"><?php esc_html_e( 'Ways to put the words to work, from a five-minute quiz to a full lesson.', 'ai-awareness-day' ); ?></p>
		<ul class="alpt-tryit__cards">
			<li><a class="alpt-tryit__card" href="#alpt-compounds-title"><span class="alpt-tryit__time"><?php esc_html_e( '10 min', 'ai-awareness-day' ); ?></span><span class="alpt-tryit__name"><?php esc_html_e( 'Compound combinations', 'ai-awareness-day' ); ?></span> <span class="alpt-tryit__about"><?php esc_html_e( 'See how elements combine into the AI people use every day.', 'ai-awareness-day' ); ?></span><span class="alpt-tryit__go" aria-hidden="true">&darr;</span></a></li>
			<li><a class="alpt-tryit__card" href="#alpt-challenge-title"><span class="alpt-tryit__time"><?php esc_html_e( '15–30 min', 'ai-awareness-day' ); ?></span><span class="alpt-tryit__name"><?php esc_html_e( 'The compound challenge', 'ai-awareness-day' ); ?></span> <span class="alpt-tryit__about"><?php esc_html_e( 'Work the compounds out yourself, at three levels.', 'ai-awareness-day' ); ?></span><span class="alpt-tryit__go" aria-hidden="true">&darr;</span></a></li>
			<li><a class="alpt-tryit__card" href="#alpt-buzzword-check"><span class="alpt-tryit__time"><?php esc_html_e( '5 min', 'ai-awareness-day' ); ?></span><span class="alpt-tryit__name"><?php esc_html_e( 'Buzzword check', 'ai-awareness-day' ); ?></span> <span class="alpt-tryit__about"><?php esc_html_e( 'Ten quick questions, different each time.', 'ai-awareness-day' ); ?></span><span class="alpt-tryit__go" aria-hidden="true">&darr;</span></a></li>
			<li><a class="alpt-tryit__card" href="#alpt-sessions"><span class="alpt-tryit__time"><?php esc_html_e( '10–50 min', 'ai-awareness-day' ); ?></span><span class="alpt-tryit__name"><?php esc_html_e( 'Ready-made sessions', 'ai-awareness-day' ); ?></span> <span class="alpt-tryit__about"><?php esc_html_e( 'A starter, a 50-minute lesson, a staff INSET and a page for parents.', 'ai-awareness-day' ); ?></span><span class="alpt-tryit__go" aria-hidden="true">&darr;</span></a></li>
		</ul>
	</div>

	<section class="alpt-compounds" aria-labelledby="alpt-compounds-title">
		<p class="alpt-sticker">
			<?php
			/* translators: %d: number of compounds */
			echo esc_html( sprintf( __( '%d compounds · Stable or volatile?', 'ai-awareness-day' ), count( $compounds ) ) );
			?>
		</p>
		<h2 id="alpt-compounds-title" class="alpt-compounds__title"><?php esc_html_e( 'Compound combinations', 'ai-awareness-day' ); ?></h2>
		<p class="alpt-compounds__lead"><?php esc_html_e( 'Elements combine into the AI systems people use every day. Each compound shows which elements a real system needs, where a human stays in the loop, and a question for your class to debate. Want to work them out yourself? Take the compound challenge below.', 'ai-awareness-day' ); ?></p>
		<p class="alpt-compounds__key">
			<span class="alpt-badge alpt-badge--stable"><?php esc_html_e( 'Stable', 'ai-awareness-day' ); ?></span> <?php esc_html_e( 'A human stays in the loop.', 'ai-awareness-day' ); ?>
			<span class="alpt-badge alpt-badge--volatile"><?php esc_html_e( 'Volatile', 'ai-awareness-day' ); ?></span> <?php esc_html_e( 'Risky without a human in the loop. Can you find the element that stabilises it?', 'ai-awareness-day' ); ?>
		</p>
		<div class="alpt-levels" role="group" aria-label="<?php esc_attr_e( 'Show one level', 'ai-awareness-day' ); ?>" data-alpt-levels hidden>
			<button type="button" class="alpt-chip" data-alpt-level="" aria-pressed="true"><?php esc_html_e( 'All levels', 'ai-awareness-day' ); ?> <span class="alpt-chip__count"><?php echo esc_html( (string) count( $compounds ) ); ?></span></button>
			<?php foreach ( $levels as $key => $label ) : ?>
				<button type="button" class="alpt-chip alpt-level--<?php echo esc_attr( $key ); ?>" data-alpt-level="<?php echo esc_attr( $key ); ?>" aria-pressed="false"><span class="alpt-level-mark" aria-hidden="true"></span><?php echo esc_html( $label ); ?> <span class="alpt-chip__count"><?php echo esc_html( (string) ( $level_counts[ $key ] ?? 0 ) ); ?></span></button>
			<?php endforeach; ?>
		</div>
		<p class="alpt-compounds__locked-note" data-alpt-locked-note><?php esc_html_e( 'Challenge on: compounds you haven\'t discovered yet are hidden. Each one gives you a clue: how many elements it needs, and one of them.', 'ai-awareness-day' ); ?></p>
		<ul class="alpt-compounds__grid" data-alpt-compounds>
			<?php foreach ( $compounds as $compound ) : ?>
				<?php
				$aiad_first   = $by_sym[ $compound['syms'][0] ] ?? null;
				$aiad_strand  = $strands[ $compound['strand'] ] ?? '';
				$aiad_vol     = $compound['volatile'];
				?>
				<li class="alpt-compound alpt-compound--<?php echo esc_attr( $compound['strand'] ); ?><?php echo $aiad_vol ? ' alpt-compound--volatile' : ''; ?>" id="alpt-cp-<?php echo esc_attr( $compound['id'] ); ?>" data-alpt-compound="<?php echo esc_attr( $compound['id'] ); ?>" data-alpt-compound-level="<?php echo esc_attr( $compound['level'] ); ?>">
					<div class="alpt-compound__locked">
						<span class="alpt-compound__q" aria-hidden="true">?</span>
						<p class="alpt-compound__locked-title"><?php esc_html_e( 'Undiscovered compound', 'ai-awareness-day' ); ?></p>
						<p class="alpt-compound__hint">
							<?php
							/* translators: 1: number of elements in the compound, 2: an element's name, 3: its symbol */
							echo esc_html( sprintf( _n( 'Clue: %1$d element, including %2$s (%3$s).', 'Clue: %1$d elements, including %2$s (%3$s).', count( $compound['syms'] ), 'ai-awareness-day' ), count( $compound['syms'] ), $aiad_first['name'] ?? '', $compound['syms'][0] ) );
							?>
						</p>
						<p class="alpt-compound__looks"><strong><?php esc_html_e( 'Looks like:', 'ai-awareness-day' ); ?></strong> <?php echo esc_html( $compound['example'] ); ?></p>
						<span class="alpt-compound__swatches" aria-hidden="true">
							<?php foreach ( $compound['syms'] as $sym ) : ?>
								<span class="alpt-chip__swatch alpt-fam--<?php echo esc_attr( $by_sym[ $sym ]['family'] ?? '' ); ?>"></span>
							<?php endforeach; ?>
						</span>
						<p class="alpt-compound__strand alpt-strand--<?php echo esc_attr( $compound['strand'] ); ?>">
							<?php
							/* translators: %s: strand name, such as Safe */
							echo esc_html( sprintf( __( '%s strand', 'ai-awareness-day' ), $aiad_strand ) );
							?>
						</p>
						<button type="button" class="alpt-btn alpt-compound__try" data-alpt-target="<?php echo esc_attr( $compound['id'] ); ?>"><?php esc_html_e( 'Find this one', 'ai-awareness-day' ); ?></button>
					</div>
					<div class="alpt-compound__body">
						<div class="alpt-compound__tags">
							<span class="alpt-badge alpt-badge--<?php echo $aiad_vol ? 'volatile' : 'stable'; ?>"><?php echo $aiad_vol ? esc_html__( 'Volatile', 'ai-awareness-day' ) : esc_html__( 'Stable', 'ai-awareness-day' ); ?></span>
							<span class="alpt-compound__found" data-alpt-found-mark hidden><?php esc_html_e( 'Discovered', 'ai-awareness-day' ); ?></span>
							<span class="alpt-compound__level alpt-level--<?php echo esc_attr( $compound['level'] ); ?>"><span class="alpt-level-mark" aria-hidden="true"></span><?php echo esc_html( $levels[ $compound['level'] ] ?? '' ); ?></span>
						</div>
						<div class="alpt-compound__head">
							<h3 class="alpt-compound__name"><?php echo esc_html( $compound['name'] ); ?></h3>
							<p class="alpt-compound__strand alpt-strand--<?php echo esc_attr( $compound['strand'] ); ?>">
								<?php
								/* translators: %s: strand name, such as Safe */
								echo esc_html( sprintf( __( '%s strand', 'ai-awareness-day' ), $aiad_strand ) );
								?>
							</p>
						</div>
						<p class="alpt-compound__made"><?php esc_html_e( 'Made from', 'ai-awareness-day' ); ?></p>
						<ul class="alpt-compound__formula">
							<?php foreach ( $compound['syms'] as $i => $sym ) : ?>
								<?php if ( $i > 0 ) : ?><li class="alpt-compound__plus" aria-hidden="true">+</li><?php endif; ?>
								<li class="alpt-compound__el alpt-fam--<?php echo esc_attr( $by_sym[ $sym ]['family'] ?? '' ); ?>">
									<span class="alpt-compound__el-sym"><?php echo esc_html( $sym ); ?></span>
									<span class="alpt-compound__el-name"><?php echo esc_html( $by_sym[ $sym ]['name'] ?? '' ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
						<button type="button" class="alpt-btn alpt-board-btn alpt-compound__board" data-alpt-board-compound="<?php echo esc_attr( $compound['id'] ); ?>" hidden><span class="alpt-board-btn__icon" aria-hidden="true"></span><?php esc_html_e( 'Show to class', 'ai-awareness-day' ); ?></button>
						<button type="button" class="alpt-btn alpt-compound__copy" data-alpt-copy-compound="<?php echo esc_attr( $compound['id'] ); ?>" hidden><?php esc_html_e( 'Copy link', 'ai-awareness-day' ); ?></button>
						<details class="alpt-compound__more">
							<summary><?php esc_html_e( 'More about it', 'ai-awareness-day' ); ?></summary>
							<p class="alpt-compound__what"><?php echo esc_html( $compound['what'] ); ?></p>
							<dl class="alpt-compound__facts">
								<div>
									<dt><?php esc_html_e( 'In real life', 'ai-awareness-day' ); ?></dt>
									<dd><?php echo esc_html( $compound['example'] ); ?></dd>
								</div>
								<div>
									<dt><?php esc_html_e( 'Human in the loop', 'ai-awareness-day' ); ?></dt>
									<dd><?php echo esc_html( $compound['loop'] ); ?></dd>
								</div>
							</dl>
							<div class="alpt-compound__debate">
								<p class="alpt-compound__debate-label"><?php esc_html_e( 'Debate it', 'ai-awareness-day' ); ?></p>
								<p class="alpt-compound__debate-q"><?php echo esc_html( $compound['debate'] ); ?></p>
							</div>
						</details>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
		<p class="alpt-compounds__more-wrap" data-alpt-more-wrap hidden><button type="button" class="alpt-btn" data-alpt-more aria-expanded="false"></button></p>
	</section>

	<section class="alpt-challenge" aria-labelledby="alpt-challenge-title" data-alpt-challenge hidden>
		<p class="alpt-sticker">
			<?php
			/* translators: %d: number of compounds */
			echo esc_html( sprintf( __( 'Challenge · %d compounds', 'ai-awareness-day' ), count( $compounds ) ) );
			?>
		</p>
		<h2 id="alpt-challenge-title" class="alpt-challenge__title"><?php esc_html_e( 'The compound challenge', 'ai-awareness-day' ); ?></h2>
		<p class="alpt-challenge__lead"><?php esc_html_e( 'Ready to work them out yourself? Choose a level: the compounds you haven\'t discovered are hidden, with a clue for each. Choose two to four elements on the table and combine them to find the answers. Stuck? Press Find this one on any hidden compound: the table narrows to a few elements, and hints help you the rest of the way. End the challenge to see every compound again.', 'ai-awareness-day' ); ?></p>
		<div class="alpt-challenge__bar">
			<p class="alpt-challenge__progress"><span data-alpt-found-text></span></p>
			<div class="alpt-meter" aria-hidden="true"><span class="alpt-meter__fill" data-alpt-meter></span></div>
		</div>
		<div class="alpt-challenge__levels">
			<?php foreach ( $levels as $key => $label ) : ?>
				<button type="button" class="alpt-challenge__level alpt-level--<?php echo esc_attr( $key ); ?>" data-alpt-start="<?php echo esc_attr( $key ); ?>">
					<span class="alpt-challenge__level-name"><span class="alpt-level-mark" aria-hidden="true"></span><?php echo esc_html( $label ); ?></span>
					<span class="alpt-challenge__level-about">
						<?php
						$aiad_about = array(
							'primary'   => __( 'Words children know, two at a time. The table narrows to a few tiles for you.', 'ai-awareness-day' ),
							'secondary' => __( 'Everyday AI, agents, predictions and fair decisions.', 'ai-awareness-day' ),
							'further'   => __( 'How AI is built, tested, run and governed.', 'ai-awareness-day' ),
						);
						echo esc_html( $aiad_about[ $key ] ?? '' );
						?>
					</span>
					<span class="alpt-challenge__level-count" data-alpt-level-count="<?php echo esc_attr( $key ); ?>"></span>
				</button>
			<?php endforeach; ?>
			<button type="button" class="alpt-challenge__level alpt-challenge__level--all" data-alpt-start="">
				<span class="alpt-challenge__level-name"><?php esc_html_e( 'All levels', 'ai-awareness-day' ); ?></span>
				<span class="alpt-challenge__level-about"><?php esc_html_e( 'Every compound at once.', 'ai-awareness-day' ); ?></span>
				<span class="alpt-challenge__level-count" data-alpt-level-count=""></span>
			</button>
		</div>
		<div class="alpt-challenge__actions">
			<button type="button" class="alpt-btn alpt-btn--ghost" data-alpt-reset hidden><?php esc_html_e( 'Start again', 'ai-awareness-day' ); ?></button>
		</div>
	</section>

	<div class="alpt-lab" id="alpt-lab" data-alpt-lab role="region" aria-label="<?php esc_attr_e( 'The compound challenge', 'ai-awareness-day' ); ?>" hidden>
		<div class="alpt-lab__inner">
			<div class="alpt-lab__head">
				<p class="alpt-lab__title"><?php esc_html_e( 'The compound challenge', 'ai-awareness-day' ); ?> <span class="alpt-lab__count" data-alpt-found-count></span></p>
				<p class="alpt-lab__help"><?php esc_html_e( 'Choose two to four elements on the table, then combine them.', 'ai-awareness-day' ); ?></p>
			</div>
			<div class="alpt-lab__target" data-alpt-target-box hidden>
				<p class="alpt-lab__target-text" data-alpt-target-text>&nbsp;</p>
				<div class="alpt-lab__target-actions">
					<button type="button" class="alpt-btn" data-alpt-hint><?php esc_html_e( 'Give me a hint', 'ai-awareness-day' ); ?></button>
					<button type="button" class="alpt-btn alpt-btn--ghost" data-alpt-unfocus><?php esc_html_e( 'Show the whole table', 'ai-awareness-day' ); ?></button>
				</div>
			</div>
			<ol class="alpt-lab__slots" data-alpt-slots aria-label="<?php esc_attr_e( 'Chosen elements', 'ai-awareness-day' ); ?>"></ol>
			<div class="alpt-lab__actions">
				<button type="button" class="alpt-btn alpt-btn--ink" data-alpt-combine disabled><?php esc_html_e( 'Combine', 'ai-awareness-day' ); ?></button>
				<button type="button" class="alpt-btn" data-alpt-clear disabled><?php esc_html_e( 'Clear', 'ai-awareness-day' ); ?></button>
				<button type="button" class="alpt-btn alpt-lab__hint" data-alpt-free-hint><span class="alpt-lab__hint-icon" aria-hidden="true">?</span><?php esc_html_e( 'Hint', 'ai-awareness-day' ); ?></button>
				<button type="button" class="alpt-btn alpt-btn--ghost alpt-lab__close" data-alpt-lab-toggle aria-controls="alpt-lab"><?php esc_html_e( 'End challenge', 'ai-awareness-day' ); ?></button>
			</div>
			<div class="alpt-lab__msg" data-alpt-lab-msg aria-live="polite"></div>
		</div>
	</div>

	<section class="alpt-quiz" id="alpt-buzzword-check" aria-labelledby="alpt-quiz-title" hidden>
		<div class="alpt-quiz__intro">
			<p class="alpt-sticker alpt-quiz__eyebrow"><?php esc_html_e( 'Quiz · 10 questions', 'ai-awareness-day' ); ?></p>
			<h2 id="alpt-quiz-title"><?php esc_html_e( 'Buzzword check', 'ai-awareness-day' ); ?></h2>
			<p class="alpt-quiz__lead"><?php esc_html_e( 'Which AI term fits the situation? Ten questions, picked at random each time you play.', 'ai-awareness-day' ); ?></p>
			<div class="alpt-quiz__scores">
				<p class="alpt-quiz__score"><span class="alpt-quiz__score-label"><?php esc_html_e( 'Score', 'ai-awareness-day' ); ?></span> <span data-quiz-score>0</span></p>
				<p class="alpt-quiz__best" data-quiz-best hidden>&nbsp;</p>
			</div>
		</div>
		<div class="alpt-quiz__card">
			<div class="alpt-quiz__top">
				<p class="alpt-quiz__progress" data-quiz-progress>&nbsp;</p>
				<div class="alpt-quiz__bar" aria-hidden="true"><span data-quiz-bar></span></div>
			</div>
			<h3 id="alpt-quiz-question" class="alpt-quiz__question" data-quiz-question tabindex="-1">&nbsp;</h3>
			<div class="alpt-quiz__options" data-quiz-options role="group" aria-labelledby="alpt-quiz-question"></div>
			<p class="alpt-quiz__feedback" data-quiz-feedback role="status">&nbsp;</p>
			<button class="alpt-btn alpt-btn--ink alpt-quiz__next" type="button" data-quiz-next hidden>Next question</button>
		</div>
	</section>

	<section class="alpt-glossary" aria-labelledby="alpt-glossary-title">
		<h2 id="alpt-glossary-title" class="alpt-glossary__title"><?php esc_html_e( 'Glossary: the periodic table broken down', 'ai-awareness-day' ); ?></h2>
		<p class="alpt-glossary__lead"><?php esc_html_e( 'All 142 definitions, family by family, to read, copy into a lesson or print.', 'ai-awareness-day' ); ?></p>
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
								<?php if ( ! empty( $talk[ $el['sym'] ] ) ) : ?>
									<p class="alpt-glossary__talk"><strong><?php esc_html_e( 'Talk about it:', 'ai-awareness-day' ); ?></strong> <?php echo esc_html( $talk[ $el['sym'] ] ); ?></p>
								<?php endif; ?>
								<?php $aiad_res = aiad_literacy_resources_for( 'syms', $el['sym'] ); ?>
								<?php if ( $aiad_res ) : ?>
									<p class="alpt-glossary__res"><strong><?php esc_html_e( 'Learn more:', 'ai-awareness-day' ); ?></strong> <?php echo aiad_literacy_resource_links( $aiad_res ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in aiad_literacy_resource_links(). ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $el_issues[ $el['sym'] ] ) ) : ?>
									<p class="alpt-glossary__issues"><strong><?php esc_html_e( 'Issues it raises:', 'ai-awareness-day' ); ?></strong> <?php echo esc_html( implode( ', ', array_map( static fn( string $k ): string => $issues[ $k ]['name'], $el_issues[ $el['sym'] ] ) ) ); ?></p>
								<?php endif; ?>
								<p class="alpt-glossary__bench">
									<?php
									/* translators: %s: a benchmark domain, such as Privacy & Data Protection */
									printf( esc_html__( 'In the benchmark: %s.', 'ai-awareness-day' ), '<a href="' . esc_url( $bench_url ) . '">' . esc_html( aiad_literacy_benchmark_domain( $el['sym'] ) ) . '</a>' );
									?>
								</p>
							</dd>
						</div>
					<?php endforeach; ?>
				</dl>
			</details>
		<?php endforeach; ?>
	</section>

	<section class="alpt-glossary alpt-issues" aria-labelledby="alpt-issues-title" id="alpt-issues">
		<h2 id="alpt-issues-title" class="alpt-glossary__title"><?php esc_html_e( 'The issues they raise, theme by theme', 'ai-awareness-day' ); ?></h2>
		<p class="alpt-glossary__lead"><?php esc_html_e( 'The technical terms are the causes; these are the symptoms: what students actually meet when people use the technology. Each comes with a question to talk about in class, and the elements it arises from.', 'ai-awareness-day' ); ?></p>
		<?php foreach ( $strands as $theme_key => $theme_name ) : ?>
			<details class="alpt-glossary__family alpt-th--<?php echo esc_attr( $theme_key ); ?>">
				<summary>
					<span class="alpt-chip__swatch" aria-hidden="true"></span>
					<span class="alpt-glossary__family-name">
						<?php
						/* translators: %s: theme name, such as Safe */
						echo esc_html( sprintf( __( '%s theme', 'ai-awareness-day' ), $theme_name ) );
						?>
					</span>
				</summary>
				<dl>
					<?php foreach ( $issues as $issue_key => $issue ) : ?>
						<?php
						if ( $issue['theme'] !== $theme_key ) {
							continue;
						}
						?>
						<div class="alpt-glossary__item">
							<dt id="alpt-issue-<?php echo esc_attr( $issue_key ); ?>"><?php echo esc_html( $issue['name'] ); ?></dt>
							<dd>
								<p><?php echo esc_html( $issue['def'] ); ?></p>
								<p class="alpt-glossary__talk"><strong><?php esc_html_e( 'Talk about it:', 'ai-awareness-day' ); ?></strong> <?php echo esc_html( $issue['talk'] ); ?></p>
								<?php $aiad_res = aiad_literacy_resources_for( 'issues', $issue_key ); ?>
								<?php if ( $aiad_res ) : ?>
									<p class="alpt-glossary__res"><strong><?php esc_html_e( 'Learn more:', 'ai-awareness-day' ); ?></strong> <?php echo aiad_literacy_resource_links( $aiad_res ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in aiad_literacy_resource_links(). ?></p>
								<?php endif; ?>
								<p class="alpt-glossary__issues"><strong><?php esc_html_e( 'Arises from:', 'ai-awareness-day' ); ?></strong>
									<?php
									echo implode( ', ', array_map( static fn( string $sym ): string => '<a href="#' . esc_attr( $by_sym[ $sym ]['id'] ?? '' ) . '">' . esc_html( $by_sym[ $sym ]['name'] ?? $sym ) . '</a>', $issue['syms'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
									?>
								</p>
							</dd>
						</div>
					<?php endforeach; ?>
				</dl>
			</details>
		<?php endforeach; ?>
	</section>

	<section class="alpt-suggest" aria-labelledby="alpt-suggest-title">
		<div class="alpt-suggest__intro">
			<p class="alpt-sticker"><?php esc_html_e( 'Your word here', 'ai-awareness-day' ); ?></p>
			<h2 id="alpt-suggest-title" class="alpt-suggest__title"><?php esc_html_e( 'Suggest a new element', 'ai-awareness-day' ); ?></h2>
			<p><?php esc_html_e( 'AI words change every month. Heard a term that should be on the table? Tell us what it means and which family it belongs to. The team reviews every suggestion and adds the best ones.', 'ai-awareness-day' ); ?> <?php esc_html_e( 'If we add your word, we can credit you or your class on the table.', 'ai-awareness-day' ); ?></p>
			<p class="alpt-suggest__bench">
				<?php esc_html_e( 'Want to see how ready you or your school are for these ideas in practice?', 'ai-awareness-day' ); ?>
				<a href="<?php echo esc_url( aiad_literacy_benchmark_url() ); ?>"><?php esc_html_e( 'Take the AI Risk Readiness Benchmark', 'ai-awareness-day' ); ?></a>
			</p>
		</div>
		<form class="alpt-suggest__form" data-alpt-suggest data-endpoint="<?php echo esc_url( rest_url( 'aiad/v1/literacy-term' ) ); ?>" novalidate hidden>
			<div class="alpt-suggest__row">
				<label class="alpt-suggest__field alpt-suggest__field--wide">
					<span><?php esc_html_e( 'The term', 'ai-awareness-day' ); ?></span>
					<input type="text" name="term" maxlength="60" required autocomplete="off" placeholder="<?php esc_attr_e( 'For example, Prompt Injection', 'ai-awareness-day' ); ?>">
				</label>
				<label class="alpt-suggest__field">
					<span><?php esc_html_e( 'Symbol (optional)', 'ai-awareness-day' ); ?></span>
					<input type="text" name="symbol" maxlength="3" autocomplete="off" placeholder="<?php esc_attr_e( 'Pj', 'ai-awareness-day' ); ?>">
				</label>
				<label class="alpt-suggest__field">
					<span><?php esc_html_e( 'Family', 'ai-awareness-day' ); ?></span>
					<select name="family">
						<option value=""><?php esc_html_e( 'Not sure', 'ai-awareness-day' ); ?></option>
						<?php foreach ( $families as $key => $family ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $family['name'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>
			<label class="alpt-suggest__field">
				<span><?php esc_html_e( 'What it means, in plain English', 'ai-awareness-day' ); ?></span>
				<textarea name="definition" rows="3" maxlength="400" required></textarea>
			</label>
			<label class="alpt-suggest__field">
				<span><?php esc_html_e( 'Why should it be on the table? (optional)', 'ai-awareness-day' ); ?></span>
				<textarea name="why" rows="2" maxlength="300"></textarea>
			</label>
			<div class="alpt-suggest__row">
				<label class="alpt-suggest__field alpt-suggest__field--wide">
					<span><?php esc_html_e( 'Your first name (optional)', 'ai-awareness-day' ); ?></span>
					<input type="text" name="name" maxlength="60" autocomplete="given-name">
				</label>
				<label class="alpt-suggest__field">
					<span><?php esc_html_e( 'I am a…', 'ai-awareness-day' ); ?></span>
					<select name="role">
						<option value=""><?php esc_html_e( 'Prefer not to say', 'ai-awareness-day' ); ?></option>
						<option value="student"><?php esc_html_e( 'Student', 'ai-awareness-day' ); ?></option>
						<option value="teacher"><?php esc_html_e( 'Teacher', 'ai-awareness-day' ); ?></option>
						<option value="parent"><?php esc_html_e( 'Parent or carer', 'ai-awareness-day' ); ?></option>
						<option value="other"><?php esc_html_e( 'Other', 'ai-awareness-day' ); ?></option>
					</select>
				</label>
			</div>
			<label class="alpt-suggest__field">
				<span><?php esc_html_e( 'If we add it, credit it to (optional)', 'ai-awareness-day' ); ?></span>
				<input type="text" name="credit" maxlength="80" autocomplete="off" placeholder="<?php esc_attr_e( 'For example, Year 9 at Oakfield School', 'ai-awareness-day' ); ?>" aria-describedby="alpt-credit-help">
				<small id="alpt-credit-help" class="alpt-suggest__help"><?php esc_html_e( 'A class, group or first name only. Never a surname.', 'ai-awareness-day' ); ?></small>
			</label>
			<label class="alpt-suggest__trap" aria-hidden="true"><?php esc_html_e( 'Leave this empty', 'ai-awareness-day' ); ?> <input type="text" name="aiad_website" tabindex="-1" autocomplete="off"></label>
			<p class="alpt-suggest__note"><?php esc_html_e( 'Please don\'t include anyone\'s personal details. Suggestions are not published as they are: the team checks and edits them first.', 'ai-awareness-day' ); ?></p>
			<div class="alpt-suggest__actions">
				<button type="submit" class="alpt-btn alpt-btn--ink"><?php esc_html_e( 'Send my suggestion', 'ai-awareness-day' ); ?></button>
				<div class="alpt-suggest__status" data-alpt-suggest-status role="status" aria-live="polite">&nbsp;</div>
			</div>
		</form>
	</section>

	<script type="application/json" data-alpt-data><?php echo wp_json_encode( array( 'families' => array_map( static fn( array $f ): string => $f['name'], $families ), 'themes' => $strands, 'eras' => array_map( static fn( array $e ): string => $e['name'], $eras ), 'issues' => array_map( static fn( array $i ): array => array( $i['name'], $i['theme'], $i['def'], $i['talk'] ), $issues ), 'elIssues' => $el_issues, 'resources' => array_map( static fn( array $r ): array => array( $r['title'], $r['url'], $r['kind'] ), aiad_literacy_resources() ), 'elRes' => array_combine( array_column( $elements, 'sym' ), array_map( static fn( string $sym ): array => aiad_literacy_resources_for( 'syms', $sym ), array_column( $elements, 'sym' ) ) ), 'issueRes' => array_combine( array_keys( $issues ), array_map( static fn( string $k ): array => aiad_literacy_resources_for( 'issues', $k ), array_keys( $issues ) ) ), 'talk' => $talk, 'safe' => aiad_literacy_safeguarding_syms(), 'credits' => (object) aiad_literacy_reader_credits(), 'elements' => array_map( static fn( array $el ): array => array( $el['sym'], $el['name'], $el['family'], $el['def'], $el['id'], $el['theme'], $el['year'], $el['approx'], $el['era'], $el['older'] ), $elements ), 'compounds' => array_map( static fn( array $c ): array => array( $c['id'], $c['name'], $c['syms'], $c['volatile'], $c['level'], $c['what'], $c['debate'], $c['strand'], $c['example'] ), $compounds ), 'links' => aiad_literacy_connections(), 'bench' => array_combine( array_column( $elements, 'sym' ), array_map( 'aiad_literacy_benchmark_domain', array_column( $elements, 'sym' ) ) ) ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ); ?></script>
</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * The AI Risk Readiness Benchmark's address (plugins/ai-risk-readiness-benchmark), for the links from the table.
 */
function aiad_literacy_benchmark_url(): string {
	if ( class_exists( 'AIRB_Defaults' ) && method_exists( 'AIRB_Defaults', 'benchmark_page_url' ) ) {
		return (string) AIRB_Defaults::benchmark_page_url();
	}
	return home_url( '/ai-risk-readiness-benchmark/' );
}

/**
 * Which benchmark domain each element relates to (AIRB_Defaults::domains()). The benchmark can't open at one
 * domain, so the panel and the list of definitions name the domain and link to the benchmark. Elements not listed
 * here fall under AI Literacy.
 *
 * @return array<string, array{name:string, syms:string}> Domain key => its name and the symbols in it.
 */
function aiad_literacy_benchmark_domains(): array {
	return array(
		'safeguarding'         => array( 'name' => __( 'Safeguarding', 'ai-awareness-day' ), 'syms' => 'Dk Gv Sp Jb Uc Ab Wm' ),
		'privacy'              => array( 'name' => __( 'Privacy & Data Protection', 'ai-awareness-day' ), 'syms' => 'Pv Dg Sh Me' ),
		'human_oversight'      => array( 'name' => __( 'Human Oversight', 'ai-awareness-day' ), 'syms' => 'Hi Au Ag Ac Co Br Ma' ),
		'bias_equality'        => array( 'name' => __( 'Bias & Equality', 'ai-awareness-day' ), 'syms' => 'Bi Fa Xa Rs Sd' ),
		'assessment_integrity' => array( 'name' => __( 'Assessment Integrity', 'ai-awareness-day' ), 'syms' => 'Ha Ip Vc Ca Cp' ),
		'governance'           => array( 'name' => __( 'Governance', 'ai-awareness-day' ), 'syms' => 'Re Et Tn Aw So Rt Sf Ev' ),
		'safe_adoption'        => array( 'name' => __( 'Safe Adoption', 'ai-awareness-day' ), 'syms' => 'Gr Cb Va Ll Ga' ),
		'ai_dependency'        => array( 'name' => __( 'Independent Practice', 'ai-awareness-day' ), 'syms' => 'Rm Ct Pe Pr' ),
	);
}

/**
 * An element's benchmark domain name (AI Literacy when it has no closer one).
 *
 * @param string $sym An element's symbol.
 */
function aiad_literacy_benchmark_domain( string $sym ): string {
	static $by_sym = null;
	if ( null === $by_sym ) {
		$by_sym = array();
		foreach ( aiad_literacy_benchmark_domains() as $domain ) {
			foreach ( explode( ' ', $domain['syms'] ) as $s ) {
				$by_sym[ $s ] = $domain['name'];
			}
		}
	}
	return $by_sym[ $sym ] ?? __( 'AI Literacy', 'ai-awareness-day' );
}

/**
 * POST aiad/v1/literacy-term: a visitor suggests a new element for the table. Like the Get Involved form
 * (plugins/aiad-core/modules/contact.php) it is a public route with no nonce, as cached pages outlive one; a hidden
 * honeypot field, three suggestions per visitor per hour and validation protect it. Suggestions are saved as private
 * form submissions for the team to review, and emailed to them. Nothing a visitor sends is published.
 *
 * @param WP_REST_Request $request The form's fields.
 * @return WP_REST_Response|WP_Error
 */
function aiad_rest_literacy_term( WP_REST_Request $request ) {
	$error = static function ( string $message, int $status = 400 ): WP_Error {
		return new WP_Error( 'aiad_literacy_term', $message, array( 'status' => $status ) );
	};
	$field = static function ( string $key, int $max ) use ( $request ): string {
		$value = $request->get_param( $key );
		$value = is_string( $value ) ? trim( sanitize_textarea_field( $value ) ) : '';
		return mb_substr( $value, 0, $max );
	};

	if ( '' !== $field( 'aiad_website', 200 ) ) {
		// A bot filled the hidden field: say thanks and keep nothing.
		return new WP_REST_Response( array( 'message' => __( 'Thank you for your suggestion.', 'ai-awareness-day' ) ) );
	}

	$ip        = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$agent     = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
	$limit_key = 'aiad_alpt_term_' . md5( $ip . '|' . $agent );
	$count     = (int) get_transient( $limit_key );
	// Each device gets a few suggestions an hour (keyed on its address and browser, so a class on one school
	// network isn't treated as one person), and the whole site is capped so the team's inbox can't be flooded.
	$all_key   = 'aiad_alpt_term_all';
	$all_count = (int) get_transient( $all_key );
	if ( $count >= 3 || $all_count >= 40 ) {
		return $error( __( 'Thank you for your ideas! You can suggest more in an hour.', 'ai-awareness-day' ), 429 );
	}

	$term       = $field( 'term', 60 );
	$symbol     = preg_replace( '/[^A-Za-z0-9]/', '', $field( 'symbol', 3 ) );
	$family     = $field( 'family', 30 );
	$definition = $field( 'definition', 400 );
	$why        = $field( 'why', 300 );
	$name       = $field( 'name', 60 );
	$role       = $field( 'role', 20 );
	$credit     = $field( 'credit', 80 );

	if ( mb_strlen( $term ) < 2 ) {
		return $error( __( 'Please enter the term you would like to add.', 'ai-awareness-day' ) );
	}
	if ( mb_strlen( $definition ) < 10 ) {
		return $error( __( 'Please explain what the term means, in a sentence or two.', 'ai-awareness-day' ) );
	}
	foreach ( aiad_literacy_elements() as $el ) {
		if ( 0 === strcasecmp( $el['name'], $term ) ) {
			/* translators: 1: element name, 2: its symbol */
			return $error( sprintf( __( '%1$s is already on the table (%2$s). Is there another term we are missing?', 'ai-awareness-day' ), $el['name'], $el['sym'] ) );
		}
	}

	$families = aiad_literacy_families();
	$family   = isset( $families[ $family ] ) ? $family : '';
	$roles    = array(
		'student' => __( 'Student', 'ai-awareness-day' ),
		'teacher' => __( 'Teacher', 'ai-awareness-day' ),
		'parent'  => __( 'Parent or carer', 'ai-awareness-day' ),
		'other'   => __( 'Other', 'ai-awareness-day' ),
	);
	$role = isset( $roles[ $role ] ) ? $role : '';

	set_transient( $limit_key, $count + 1, HOUR_IN_SECONDS );
	set_transient( $all_key, $all_count + 1, HOUR_IN_SECONDS );

	$body  = "Suggested element for the AI Literacy Periodic Table\n\n";
	$body .= "Term: {$term}\n";
	$body .= 'Symbol: ' . ( $symbol ? $symbol : '(none given)' ) . "\n";
	$body .= 'Family: ' . ( $family ? $families[ $family ]['name'] : '(not sure)' ) . "\n";
	$body .= "\nWhat it means:\n{$definition}\n";
	if ( $why ) {
		$body .= "\nWhy it should be added:\n{$why}\n";
	}
	$body .= "\nSuggested by: " . ( $name ? $name : '(no name)' ) . ( $role ? ' (' . $roles[ $role ] . ')' : '' ) . "\n";
	$body .= 'Credit line if added: ' . ( $credit ? $credit : '(none)' ) . "\n";
	if ( $credit ) {
		$body .= "To credit them, add the element's symbol and this line to aiad_literacy_reader_credits() in inc/literacy-table.php.\n";
	}

	$saved = 0;
	if ( post_type_exists( 'form_submission' ) ) {
		$saved = wp_insert_post(
			array(
				/* translators: %s: the suggested term */
				'post_title'   => sprintf( __( 'Periodic table suggestion: %s', 'ai-awareness-day' ), $term ),
				'post_content' => $body,
				'post_status'  => 'private',
				'post_type'    => 'form_submission',
			)
		);
		if ( $saved && ! is_wp_error( $saved ) ) {
			update_post_meta( $saved, '_submission_involved_as', 'periodic_table_suggestion' );
			update_post_meta( $saved, '_submission_term', $term );
			update_post_meta( $saved, '_submission_message', $definition );
		}
	}

	$to = function_exists( 'aiad_campaign_setting' ) && aiad_campaign_setting( 'contact_email' ) ? aiad_campaign_setting( 'contact_email' ) : get_option( 'admin_email' );
	/* translators: %s: the suggested term */
	wp_mail( $to, sprintf( __( '[AI Awareness Day] Periodic table suggestion: %s', 'ai-awareness-day' ), $term ), $body );

	return new WP_REST_Response(
		array(
			/* translators: %s: the suggested term */
			'message' => sprintf( __( 'Thank you! We have received your suggestion, %s. The team reviews suggestions and adds the best ones to the table.', 'ai-awareness-day' ), $term ),
		)
	);
}

add_action(
	'rest_api_init',
	static function (): void {
		register_rest_route(
			'aiad/v1',
			'/literacy-term',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => 'aiad_rest_literacy_term',
				'permission_callback' => '__return_true',
			)
		);
	}
);

/**
 * The element of the week card, for the page's introduction (block aiad/literacy-week). It is its own block so it can
 * sit beside the title while the table follows below; it still changes every Monday, since it is drawn on each view.
 */
function aiad_render_literacy_week(): string {
	ob_start();
	?>
<?php $aiad_week = aiad_literacy_element_of_week(); $aiad_week_theme = $aiad_week['el']['theme'] ?? 'smart'; ?>
<div class="alpt-eotw alpt-eotw--<?php echo esc_attr( $aiad_week_theme ); ?>">
	<span class="alpt-eotw__sticker" aria-hidden="true"><?php esc_html_e( 'New every Monday', 'ai-awareness-day' ); ?></span>
	<div class="alpt-eotw__tile alpt-fam--<?php echo esc_attr( $aiad_week['el']['family'] ); ?>" aria-hidden="true"><span class="alpt-eotw__sym"><?php echo esc_html( $aiad_week['el']['sym'] ); ?></span></div>
	<div class="alpt-eotw__text">
		<p class="alpt-eotw__label">
			<?php
			/* translators: %s: a date, such as 16 November 2026 */
			echo esc_html( sprintf( /* translators: 1: date, such as 16 Nov, 2: theme name, such as Safe */ __( 'Word of the week · %1$s · %2$s theme', 'ai-awareness-day' ), date_i18n( 'j M', $aiad_week['monday'] ), aiad_literacy_strands()[ $aiad_week_theme ] ?? '' ) );
			?>
		</p>
		<h2 class="alpt-eotw__name"><?php echo esc_html( $aiad_week['el']['name'] ); ?></h2>
		<p class="alpt-eotw__talk"><?php echo esc_html( $aiad_week['talk'] ); ?></p>
	</div>
	<div class="alpt-eotw__actions">
		<a class="alpt-btn alpt-btn--ink" href="#element-<?php echo esc_attr( strtolower( $aiad_week['el']['sym'] ) ); ?>"><?php esc_html_e( 'Open it', 'ai-awareness-day' ); ?></a>
		<button type="button" class="alpt-btn" data-alpt-eotw-board="<?php echo esc_attr( (string) ( $aiad_week['el']['n'] - 1 ) ); ?>" hidden><?php esc_html_e( 'Show to class', 'ai-awareness-day' ); ?></button>
	</div>
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
			'description'     => __( 'The interactive table of 142 AI terms, with every definition listed below it. Its elements are written in the theme (inc/literacy-table.php).', 'ai-awareness-day' ),
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
	register_block_type(
		'aiad/literacy-week',
		array(
			'api_version'     => 3,
			'title'           => __( 'AI Literacy element of the week', 'ai-awareness-day' ),
			'description'     => __( 'This week\'s element from the AI Literacy Periodic Table, with its question for class. It changes every Monday.', 'ai-awareness-day' ),
			'category'        => 'widgets',
			'icon'            => 'calendar-alt',
			'supports'        => array( 'html' => false, 'multiple' => false, 'reusable' => false ),
			'style'           => 'aiad-literacy-table',
			'render_callback' => static fn(): string => aiad_render_literacy_week(),
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

/*
 * The homepage promo (template-parts/front-page/part-literacy-table-promo.php) goes at the end of the Handpicked
 * resources section, under its "View all" link. That section is an editable group on the Home page, so the card is
 * added inside it as the page is shown (before the container's closing tag) rather than written into the page.
 */
add_filter(
	'render_block_core/group',
	static function ( string $html, array $block ): string {
		if ( 'partner-resources' !== ( $block['attrs']['anchor'] ?? '' ) || ! is_front_page() ) {
			return $html;
		}
		ob_start();
		get_template_part( 'template-parts/front-page/part', 'literacy-table-promo' );
		$promo = (string) ob_get_clean();
		$end   = strrpos( $html, '</div>' );
		return false === $end || '' === $promo ? $html : substr( $html, 0, $end ) . $promo . substr( $html, $end );
	},
	10,
	2
);

