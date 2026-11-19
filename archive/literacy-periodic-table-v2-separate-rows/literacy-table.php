<?php
/**
 * The AI Literacy Periodic Table: /ai-literacy-periodic-table/, for teachers and students.
 *
 * 141 AI terms in nine families: the 120 terms of the original Periodic Table of AI, from the foundations everything
 * is built on, through generative, predictive, agentic and automation AI, to the governance and infrastructure that
 * hold it together, plus rows of current buzzwords and trends, and of reverse engineering AI. Each element has a plain-English definition. (The first version, with six families built on the campaign's themes, is kept in
 * archive/literacy-periodic-table-v1-aiad-strands/.)
 *
 * It is an ordinary page of blocks (patterns/ai-literacy-periodic-table.php), created the first time the theme loads
 * (aiad_maybe_convert_theme_pages() in inc/editable-pages.php), so its introduction and classroom ideas are edited on
 * the page. The table itself is one block, aiad/literacy-table, drawn here from aiad_literacy_elements(), so its 141
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
 * The nine families, in table order: key => name and what it covers.
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
			'about' => __( 'Keeping AI fair, safe, lawful and accountable.', 'ai-awareness-day' ),
		),
		'infra' => array(
			'name'  => __( 'Infrastructure & Ops', 'ai-awareness-day' ),
			'about' => __( 'The hardware, tools and methods that run AI.', 'ai-awareness-day' ),
		),
		'trends' => array(
			'name'  => __( 'Buzzwords & Trends', 'ai-awareness-day' ),
			'about' => __( 'The newest words in AI, and the design skills it is changing.', 'ai-awareness-day' ),
		),
		'reverse' => array(
			'name'  => __( 'Reverse Engineering AI', 'ai-awareness-day' ),
			'about' => __( 'How people look inside AI models, and how their safety rules get removed or bypassed.', 'ai-awareness-day' ),
		),
	);
}

/**
 * Every element: symbol, name, family, row and column on the 18-column table, and definition. Rows 1 to 7 are the
 * main table (rows 1 to 3 have a gap in the middle for the detail panel, as a periodic table does); rows 9 to 12 are
 * the four rows below it. Elements are numbered in reading order, so the numbers follow from where they sit.
 *
 * @return array<int, array{0:string, 1:string, 2:string, 3:int, 4:int, 5:string}>
 */
function aiad_literacy_raw_elements(): array {
	return array(
		// Foundations: the left-hand block, and the top right corner.
		array( 'Ai', __( 'Artificial Intelligence', 'ai-awareness-day' ), 'foundations', 1, 1, __( 'Computer systems that perform tasks normally requiring human intelligence, such as understanding language, recognising patterns and making decisions.', 'ai-awareness-day' ) ),
		array( 'Agi', __( 'Artificial General Intelligence', 'ai-awareness-day' ), 'foundations', 1, 18, __( 'A hypothetical AI able to match or exceed human ability across almost any intellectual task, rather than excelling in one narrow domain.', 'ai-awareness-day' ) ),
		array( 'Ml', __( 'Machine Learning', 'ai-awareness-day' ), 'foundations', 2, 1, __( 'A branch of AI where systems learn patterns from data instead of following hand-written rules.', 'ai-awareness-day' ) ),
		array( 'Dl', __( 'Deep Learning', 'ai-awareness-day' ), 'foundations', 2, 2, __( 'Machine learning using neural networks with many layers — the approach behind most modern breakthroughs in vision, speech and language.', 'ai-awareness-day' ) ),
		array( 'Nn', __( 'Neural Network', 'ai-awareness-day' ), 'foundations', 3, 1, __( 'A model made of layers of connected nodes, loosely inspired by the brain, that learns by adjusting the strength of its connections.', 'ai-awareness-day' ) ),
		array( 'Al', __( 'Algorithm', 'ai-awareness-day' ), 'foundations', 3, 2, __( 'A step-by-step set of instructions for solving a problem; in machine learning, the method a system uses to learn from data.', 'ai-awareness-day' ) ),
		array( 'Md', __( 'Model', 'ai-awareness-day' ), 'foundations', 4, 1, __( 'The trained artefact that turns inputs into outputs — what you get after an algorithm has learned from data.', 'ai-awareness-day' ) ),
		array( 'Tr', __( 'Training', 'ai-awareness-day' ), 'foundations', 4, 2, __( 'The process of feeding data to a model and adjusting its internal settings until its outputs improve.', 'ai-awareness-day' ) ),
		array( 'If', __( 'Inference', 'ai-awareness-day' ), 'foundations', 5, 1, __( 'Using a trained model to make predictions or generate output on new inputs — the “running” phase after training.', 'ai-awareness-day' ) ),
		array( 'Pm', __( 'Parameters', 'ai-awareness-day' ), 'foundations', 5, 2, __( 'The internal numbers (weights) a model learns during training. Model size is often quoted in billions of parameters.', 'ai-awareness-day' ) ),
		array( 'Tk', __( 'Token', 'ai-awareness-day' ), 'foundations', 6, 1, __( 'The small chunk of text — a word, part of a word or a symbol — that language models read and write.', 'ai-awareness-day' ) ),
		array( 'Em', __( 'Embedding', 'ai-awareness-day' ), 'foundations', 6, 2, __( 'A list of numbers that represents the meaning of text, images or other data, so that similar things sit close together.', 'ai-awareness-day' ) ),
		array( 'Tf', __( 'Transformer', 'ai-awareness-day' ), 'foundations', 7, 1, __( 'The neural network architecture, introduced in 2017, that uses “attention” to process sequences. It powers today’s large language models.', 'ai-awareness-day' ) ),
		array( 'Fm', __( 'Foundation Model', 'ai-awareness-day' ), 'foundations', 7, 2, __( 'A large model trained on broad data that can be adapted to many different tasks, rather than built for just one.', 'ai-awareness-day' ) ),

		// Generative AI: centre left.
		array( 'Ll', __( 'Large Language Model', 'ai-awareness-day' ), 'generative', 4, 3, __( 'A transformer model trained on vast amounts of text to understand and generate human language.', 'ai-awareness-day' ) ),
		array( 'Ga', __( 'Generative AI', 'ai-awareness-day' ), 'generative', 4, 4, __( 'AI that creates new content — text, images, audio, video or code — rather than only analysing existing data.', 'ai-awareness-day' ) ),
		array( 'Pr', __( 'Prompt', 'ai-awareness-day' ), 'generative', 4, 5, __( 'The instruction or input you give a generative model to get a response.', 'ai-awareness-day' ) ),
		array( 'Pe', __( 'Prompt Engineering', 'ai-awareness-day' ), 'generative', 4, 6, __( 'The craft of writing and structuring prompts to get more accurate, useful and consistent outputs.', 'ai-awareness-day' ) ),
		array( 'Cw', __( 'Context Window', 'ai-awareness-day' ), 'generative', 4, 7, __( 'The maximum amount of text, measured in tokens, that a model can take into account at once.', 'ai-awareness-day' ) ),
		array( 'Mm', __( 'Multimodal AI', 'ai-awareness-day' ), 'generative', 5, 3, __( 'A model that can take in or produce more than one type of data, such as text, images and audio together.', 'ai-awareness-day' ) ),
		array( 'Df', __( 'Diffusion Model', 'ai-awareness-day' ), 'generative', 5, 4, __( 'A generative model that learns to turn random noise into images, audio or video by removing the noise step by step.', 'ai-awareness-day' ) ),
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
		array( 'Ha', __( 'Hallucination', 'ai-awareness-day' ), 'generative', 7, 5, __( 'When a model produces confident-sounding information that is false or made up.', 'ai-awareness-day' ) ),
		array( 'Dk', __( 'Deepfake', 'ai-awareness-day' ), 'generative', 7, 6, __( 'Realistic AI-generated or manipulated video, audio or images showing people saying or doing things they never did.', 'ai-awareness-day' ) ),
		array( 'Vc', __( 'Vibe Coding', 'ai-awareness-day' ), 'generative', 7, 7, __( 'Building software by describing what you want in plain language and letting AI write most of the code.', 'ai-awareness-day' ) ),

		// Predictive and analytical AI: centre right.
		array( 'Su', __( 'Supervised Learning', 'ai-awareness-day' ), 'predictive', 4, 8, __( 'Learning from labelled examples, where every input comes with the correct answer.', 'ai-awareness-day' ) ),
		array( 'Un', __( 'Unsupervised Learning', 'ai-awareness-day' ), 'predictive', 4, 9, __( 'Finding structure in unlabelled data without being told what the right answers are.', 'ai-awareness-day' ) ),
		array( 'Rr', __( 'Regression', 'ai-awareness-day' ), 'predictive', 4, 10, __( 'Predicting a continuous number, such as a price, a demand level or a temperature.', 'ai-awareness-day' ) ),
		array( 'Cl', __( 'Classification', 'ai-awareness-day' ), 'predictive', 4, 11, __( 'Assigning inputs to categories, such as spam or not spam, fraudulent or genuine.', 'ai-awareness-day' ) ),
		array( 'Cu', __( 'Clustering', 'ai-awareness-day' ), 'predictive', 4, 12, __( 'Grouping similar items together without predefined labels — customer segmentation is a classic use.', 'ai-awareness-day' ) ),
		array( 'Dt', __( 'Decision Tree', 'ai-awareness-day' ), 'predictive', 5, 8, __( 'A model that makes predictions through a flowchart of yes/no questions about the data.', 'ai-awareness-day' ) ),
		array( 'Rf', __( 'Random Forest', 'ai-awareness-day' ), 'predictive', 5, 9, __( 'Many decision trees combined, whose collective vote gives a more robust prediction than any single tree.', 'ai-awareness-day' ) ),
		array( 'Gb', __( 'Gradient Boosting', 'ai-awareness-day' ), 'predictive', 5, 10, __( 'Building models in sequence, each one correcting the errors of the last — a go-to method for tabular business data.', 'ai-awareness-day' ) ),
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

		// Autonomous AI agents: right.
		array( 'Ag', __( 'AI Agent', 'ai-awareness-day' ), 'agents', 2, 13, __( 'A system that uses an AI model to pursue a goal by deciding on and taking actions, not just answering questions.', 'ai-awareness-day' ) ),
		array( 'Ac', __( 'Agentic AI', 'ai-awareness-day' ), 'agents', 2, 14, __( 'AI designed to act with a degree of independence — planning, using tools and completing multi-step tasks with limited supervision.', 'ai-awareness-day' ) ),
		array( 'Au', __( 'Autonomy', 'ai-awareness-day' ), 'agents', 2, 15, __( 'How much an AI system can decide and do on its own before a human needs to step in.', 'ai-awareness-day' ) ),
		array( 'Pl', __( 'Planning', 'ai-awareness-day' ), 'agents', 3, 13, __( 'An agent breaking a goal down into ordered steps and deciding what to do next.', 'ai-awareness-day' ) ),
		array( 'Me', __( 'Memory', 'ai-awareness-day' ), 'agents', 3, 14, __( 'How an agent retains information — within a single task (short-term) or across sessions (long-term).', 'ai-awareness-day' ) ),
		array( 'Fn', __( 'Function Calling', 'ai-awareness-day' ), 'agents', 3, 15, __( 'Letting a model call external tools, APIs or code to fetch information or take actions. Also called tool use.', 'ai-awareness-day' ) ),
		array( 'Ra', __( 'ReAct', 'ai-awareness-day' ), 'agents', 4, 13, __( 'A pattern where an agent alternates between reasoning about a problem and acting with tools (Reason + Act).', 'ai-awareness-day' ) ),
		array( 'Lp', __( 'Agent Loop', 'ai-awareness-day' ), 'agents', 4, 14, __( 'The cycle an agent repeats — observe, think, act, check the result — until the goal is met.', 'ai-awareness-day' ) ),
		array( 'Sr', __( 'Self-Reflection', 'ai-awareness-day' ), 'agents', 4, 15, __( 'An agent reviewing and critiquing its own work, then correcting mistakes before it finishes.', 'ai-awareness-day' ) ),
		array( 'Mc', __( 'Model Context Protocol', 'ai-awareness-day' ), 'agents', 5, 13, __( 'An open standard (MCP) for connecting AI models and agents to external tools and data sources.', 'ai-awareness-day' ) ),
		array( 'Aa', __( 'Agent-to-Agent (A2A)', 'ai-awareness-day' ), 'agents', 5, 14, __( 'Protocols that let agents built by different teams or vendors discover each other and work together.', 'ai-awareness-day' ) ),
		array( 'Or', __( 'Orchestrator', 'ai-awareness-day' ), 'agents', 5, 15, __( 'A coordinating agent or component that assigns tasks to other agents or tools and combines their results.', 'ai-awareness-day' ) ),
		array( 'Ma', __( 'Multi-Agent System', 'ai-awareness-day' ), 'agents', 6, 13, __( 'Several specialised agents working together, each handling one part of a larger task.', 'ai-awareness-day' ) ),
		array( 'Sw', __( 'Agent Swarm', 'ai-awareness-day' ), 'agents', 6, 14, __( 'A large group of simple agents coordinating to solve problems collectively, inspired by swarms in nature.', 'ai-awareness-day' ) ),
		array( 'Rl', __( 'Reinforcement Learning', 'ai-awareness-day' ), 'agents', 6, 15, __( 'Learning by trial and error: an agent is rewarded for good actions and gradually learns the best strategy.', 'ai-awareness-day' ) ),
		array( 'Co', __( 'Computer Use', 'ai-awareness-day' ), 'agents', 7, 13, __( 'An agent that operates a computer like a person would — reading the screen, moving the cursor and typing.', 'ai-awareness-day' ) ),
		array( 'Br', __( 'Browser Agent', 'ai-awareness-day' ), 'agents', 7, 14, __( 'An agent that navigates websites, fills in forms and completes online tasks on your behalf.', 'ai-awareness-day' ) ),
		array( 'Ca', __( 'Coding Agent', 'ai-awareness-day' ), 'agents', 7, 15, __( 'An agent that writes, tests and fixes code across a codebase with minimal human input.', 'ai-awareness-day' ) ),

		// Automation and workflow AI: far right.
		array( 'Rp', __( 'Robotic Process Automation', 'ai-awareness-day' ), 'automation', 2, 16, __( 'Software bots that mimic human clicks and keystrokes to complete rule-based, repetitive tasks (RPA).', 'ai-awareness-day' ) ),
		array( 'Ia', __( 'Intelligent Automation', 'ai-awareness-day' ), 'automation', 2, 17, __( 'Combining RPA with AI such as language understanding and machine learning, to automate more complex, judgement-based work.', 'ai-awareness-day' ) ),
		array( 'Hy', __( 'Hyperautomation', 'ai-awareness-day' ), 'automation', 2, 18, __( 'A strategy of automating as many business processes as possible by combining tools — RPA, AI, low-code and more.', 'ai-awareness-day' ) ),
		array( 'Wo', __( 'Workflow Orchestration', 'ai-awareness-day' ), 'automation', 3, 16, __( 'Coordinating the sequence of tasks, systems and people in an end-to-end business process.', 'ai-awareness-day' ) ),
		array( 'Tg', __( 'Trigger', 'ai-awareness-day' ), 'automation', 3, 17, __( 'The event that starts an automation — a new email, a form submission, a scheduled time.', 'ai-awareness-day' ) ),
		array( 'En', __( 'Event-Driven Automation', 'ai-awareness-day' ), 'automation', 3, 18, __( 'Workflows that react instantly to events happening in other systems, rather than running on a schedule.', 'ai-awareness-day' ) ),
		array( 'Ap', __( 'API Integration', 'ai-awareness-day' ), 'automation', 4, 16, __( 'Connecting software systems through their APIs so data and actions flow between them automatically.', 'ai-awareness-day' ) ),
		array( 'Wh', __( 'Webhook', 'ai-awareness-day' ), 'automation', 4, 17, __( 'A message one app sends to another the moment something happens — often used to kick off a workflow.', 'ai-awareness-day' ) ),
		array( 'Lc', __( 'Low-Code / No-Code', 'ai-awareness-day' ), 'automation', 4, 18, __( 'Building apps and automations with visual, drag-and-drop tools and little or no hand-written code.', 'ai-awareness-day' ) ),
		array( 'Id', __( 'Intelligent Document Processing', 'ai-awareness-day' ), 'automation', 5, 16, __( 'Using AI to capture, classify and extract data from documents such as invoices, forms and contracts (IDP).', 'ai-awareness-day' ) ),
		array( 'Oc', __( 'Optical Character Recognition', 'ai-awareness-day' ), 'automation', 5, 17, __( 'Converting images of text, such as scans or photos, into machine-readable text (OCR).', 'ai-awareness-day' ) ),
		array( 'Mi', __( 'Process Mining', 'ai-awareness-day' ), 'automation', 5, 18, __( 'Analysing system event logs to map how processes really run, revealing bottlenecks and what to automate.', 'ai-awareness-day' ) ),
		array( 'Be', __( 'Business Rules Engine', 'ai-awareness-day' ), 'automation', 6, 16, __( 'Software that applies predefined if-then logic to make consistent, automated decisions.', 'ai-awareness-day' ) ),
		array( 'St', __( 'Straight-Through Processing', 'ai-awareness-day' ), 'automation', 6, 17, __( 'Transactions handled automatically from start to finish, with no manual intervention.', 'ai-awareness-day' ) ),
		array( 'Dw', __( 'Digital Worker', 'ai-awareness-day' ), 'automation', 6, 18, __( 'A software bot or AI agent positioned as a virtual team member that handles a defined role or set of tasks.', 'ai-awareness-day' ) ),
		array( 'Cb', __( 'Chatbot', 'ai-awareness-day' ), 'automation', 7, 16, __( 'A program that holds text or voice conversations with people, from scripted FAQ bots to LLM-powered assistants.', 'ai-awareness-day' ) ),
		array( 'Va', __( 'Virtual Assistant', 'ai-awareness-day' ), 'automation', 7, 17, __( 'A conversational assistant that helps with tasks such as scheduling, searching or customer support.', 'ai-awareness-day' ) ),
		array( 'Cp', __( 'Copilot', 'ai-awareness-day' ), 'automation', 7, 18, __( 'An AI assistant built into an app or workflow that works alongside you — suggesting and drafting rather than acting alone.', 'ai-awareness-day' ) ),

		// Governance and ethics: the first row below the table.
		array( 'Rs', __( 'Responsible AI', 'ai-awareness-day' ), 'governance', 9, 4, __( 'Developing and using AI in ways that are safe, fair, transparent and accountable.', 'ai-awareness-day' ) ),
		array( 'Et', __( 'AI Ethics', 'ai-awareness-day' ), 'governance', 9, 5, __( 'The moral principles guiding how AI is designed and used, from consent to wider societal impact.', 'ai-awareness-day' ) ),
		array( 'Bi', __( 'Bias', 'ai-awareness-day' ), 'governance', 9, 6, __( 'A systematic skew in a model’s outputs, often inherited from its training data, that can disadvantage certain groups.', 'ai-awareness-day' ) ),
		array( 'Fa', __( 'Fairness', 'ai-awareness-day' ), 'governance', 9, 7, __( 'Making sure an AI system’s outcomes don’t unjustly favour or disadvantage particular people or groups.', 'ai-awareness-day' ) ),
		array( 'Xa', __( 'Explainability', 'ai-awareness-day' ), 'governance', 9, 8, __( 'Techniques that make it understandable why a model produced a particular output — often called XAI.', 'ai-awareness-day' ) ),
		array( 'Tn', __( 'Transparency', 'ai-awareness-day' ), 'governance', 9, 9, __( 'Being open about when AI is being used, how it works and what data it relies on.', 'ai-awareness-day' ) ),
		array( 'An', __( 'Alignment', 'ai-awareness-day' ), 'governance', 9, 10, __( 'Ensuring an AI system’s goals and behaviour match human intentions and values.', 'ai-awareness-day' ) ),
		array( 'Gr', __( 'Guardrails', 'ai-awareness-day' ), 'governance', 9, 11, __( 'Rules and filters that keep an AI system’s inputs and outputs within safe, acceptable bounds.', 'ai-awareness-day' ) ),
		array( 'Hi', __( 'Human-in-the-Loop', 'ai-awareness-day' ), 'governance', 9, 12, __( 'Keeping a person involved to review, approve or correct AI decisions before they take effect.', 'ai-awareness-day' ) ),
		array( 'Rt', __( 'Red Teaming', 'ai-awareness-day' ), 'governance', 9, 13, __( 'Deliberately attacking or stress-testing an AI system to uncover vulnerabilities and harmful behaviour.', 'ai-awareness-day' ) ),
		array( 'Sf', __( 'AI Safety', 'ai-awareness-day' ), 'governance', 9, 14, __( 'Research and practice aimed at preventing AI systems from causing accidental or deliberate harm.', 'ai-awareness-day' ) ),
		array( 'Pv', __( 'Privacy', 'ai-awareness-day' ), 'governance', 9, 15, __( 'Protecting personal data that is used to train or is processed by AI, in line with laws such as GDPR.', 'ai-awareness-day' ) ),
		array( 'Dg', __( 'Data Governance', 'ai-awareness-day' ), 'governance', 9, 16, __( 'The policies that control how data is collected, stored, accessed and used across an organisation.', 'ai-awareness-day' ) ),
		array( 'Re', __( 'AI Regulation', 'ai-awareness-day' ), 'governance', 9, 17, __( 'Laws and standards that govern AI — such as the EU AI Act, which sets rules according to a system’s level of risk.', 'ai-awareness-day' ) ),
		array( 'Ip', __( 'Copyright & IP', 'ai-awareness-day' ), 'governance', 9, 18, __( 'Questions over who owns AI training data and AI-generated content, and how creators’ rights are protected.', 'ai-awareness-day' ) ),

		// Infrastructure and operations: the second row below the table.
		array( 'Gp', __( 'GPU', 'ai-awareness-day' ), 'infra', 10, 4, __( 'Graphics Processing Unit: chips that run huge numbers of calculations in parallel — the workhorse of AI training and inference.', 'ai-awareness-day' ) ),
		array( 'Tp', __( 'TPU & Accelerators', 'ai-awareness-day' ), 'infra', 10, 5, __( 'Specialised chips designed specifically for AI workloads, such as TPUs and NPUs.', 'ai-awareness-day' ) ),
		array( 'Cd', __( 'Cloud AI', 'ai-awareness-day' ), 'infra', 10, 6, __( 'AI models, compute and tools delivered on demand over the internet.', 'ai-awareness-day' ) ),
		array( 'Ed', __( 'Edge AI', 'ai-awareness-day' ), 'infra', 10, 7, __( 'Running AI directly on devices such as phones, cameras or sensors, rather than in the cloud.', 'ai-awareness-day' ) ),
		array( 'Mo', __( 'MLOps', 'ai-awareness-day' ), 'infra', 10, 8, __( 'Practices for reliably deploying, monitoring and maintaining machine learning models in production.', 'ai-awareness-day' ) ),
		array( 'Lo', __( 'LLMOps', 'ai-awareness-day' ), 'infra', 10, 9, __( 'MLOps adapted for large language models — managing prompts, evaluation, cost and safety monitoring.', 'ai-awareness-day' ) ),
		array( 'Vd', __( 'Vector Database', 'ai-awareness-day' ), 'infra', 10, 10, __( 'A database built to store embeddings and find similar items quickly — a core component of RAG.', 'ai-awareness-day' ) ),
		array( 'Ms', __( 'Model Serving', 'ai-awareness-day' ), 'infra', 10, 11, __( 'The infrastructure that hosts a trained model and answers prediction requests at scale.', 'ai-awareness-day' ) ),
		array( 'Qz', __( 'Quantization', 'ai-awareness-day' ), 'infra', 10, 12, __( 'Shrinking a model by storing its numbers at lower precision, making it faster and cheaper to run.', 'ai-awareness-day' ) ),
		array( 'Ds', __( 'Distillation', 'ai-awareness-day' ), 'infra', 10, 13, __( 'Training a smaller “student” model to mimic the behaviour of a larger “teacher” model.', 'ai-awareness-day' ) ),
		array( 'Ow', __( 'Open-Weight Model', 'ai-awareness-day' ), 'infra', 10, 14, __( 'A model whose trained parameters are publicly released, so anyone can run, study or adapt it.', 'ai-awareness-day' ) ),
		array( 'Sm', __( 'Small Language Model', 'ai-awareness-day' ), 'infra', 10, 15, __( 'A compact language model designed to run quickly and cheaply, often directly on a device.', 'ai-awareness-day' ) ),
		array( 'Ev', __( 'Evals', 'ai-awareness-day' ), 'infra', 10, 16, __( 'Structured tests that measure a model’s accuracy, safety and behaviour on specific tasks.', 'ai-awareness-day' ) ),
		array( 'Sl', __( 'Scaling Laws', 'ai-awareness-day' ), 'infra', 10, 17, __( 'The observed pattern that model performance improves predictably with more data, compute and parameters.', 'ai-awareness-day' ) ),
		array( 'Dp', __( 'Data Pipeline', 'ai-awareness-day' ), 'infra', 10, 18, __( 'The automated flow that collects, cleans and prepares data for training and analysis.', 'ai-awareness-day' ) ),

		// Buzzwords and trends: the third row below the table.
		array( 'Ce', __( 'Context Engineering', 'ai-awareness-day' ), 'trends', 11, 4, __( 'Structuring everything you give an AI, including background documents, rules and examples, rather than relying on a single clever prompt.', 'ai-awareness-day' ) ),
		array( 'Gv', __( 'Generative Video', 'ai-awareness-day' ), 'trends', 11, 5, __( 'AI that creates video clips from a text description, an image or another video.', 'ai-awareness-day' ) ),
		array( 'Sp', __( 'AI Slop', 'ai-awareness-day' ), 'trends', 11, 6, __( 'Low-effort, mass-produced AI text, images and videos that flood social media and search results.', 'ai-awareness-day' ) ),
		array( 'Aw', __( 'AI Washing', 'ai-awareness-day' ), 'trends', 11, 7, __( 'Exaggerating how much a product uses AI to ride the hype, such as labelling basic software “AI-powered”.', 'ai-awareness-day' ) ),
		array( 'Sh', __( 'Shadow AI', 'ai-awareness-day' ), 'trends', 11, 8, __( 'Using AI tools at work or school that haven’t been approved, which can put private data at risk.', 'ai-awareness-day' ) ),
		array( 'Ge', __( 'Generative Engine Optimisation', 'ai-awareness-day' ), 'trends', 11, 9, __( 'Shaping online content so that AI assistants find, cite and recommend it: search engine optimisation for the age of AI.', 'ai-awareness-day' ) ),
		array( 'Hp', __( 'Human Premium', 'ai-awareness-day' ), 'trends', 11, 10, __( 'The extra value people place on work, art and services verified as made by humans.', 'ai-awareness-day' ) ),
		array( 'Wm', __( 'AI Watermarking', 'ai-awareness-day' ), 'trends', 11, 11, __( 'Hidden signals built into AI-generated images, audio, video or text so their origin can be checked later.', 'ai-awareness-day' ) ),
		array( 'Si', __( 'Superintelligence', 'ai-awareness-day' ), 'trends', 11, 12, __( 'A hypothetical AI that would far surpass the best human minds in almost every field. Experts disagree on whether, or when, it could arrive.', 'ai-awareness-day' ) ),
		array( '2d', __( '2D Design', 'ai-awareness-day' ), 'trends', 11, 13, __( 'Flat designs such as drawings, logos, layouts and laser-cut parts. AI tools can now suggest, generate and refine them.', 'ai-awareness-day' ) ),
		array( '3d', __( '3D Design & CAD', 'ai-awareness-day' ), 'trends', 11, 14, __( 'Designing objects in three dimensions with computer-aided design (CAD) software, ready to model, 3D print or manufacture. AI can generate and improve designs.', 'ai-awareness-day' ) ),
		array( 'So', __( 'AI Sovereignty', 'ai-awareness-day' ), 'trends', 11, 15, __( 'A country’s or organisation’s ability to build, run and control its own AI, data and computing power, rather than relying on other countries or companies.', 'ai-awareness-day' ) ),

		// Reverse engineering AI: the fourth row below the table.
		array( 'Rv', __( 'Reverse Engineering', 'ai-awareness-day' ), 'reverse', 12, 4, __( 'Taking a finished product, program or AI model apart to work out how it was made and how it works.', 'ai-awareness-day' ) ),
		array( 'Ls', __( 'Latent Space', 'ai-awareness-day' ), 'reverse', 12, 5, __( 'The internal space of numbers where a model represents concepts and patterns as it works towards an answer. In language models, the pathway that carries this through the layers is called the residual stream.', 'ai-awareness-day' ) ),
		array( 'Mp', __( 'Mechanistic Interpretability', 'ai-awareness-day' ), 'reverse', 12, 6, __( 'Reverse engineering a neural network to work out how its neurons and internal directions represent particular concepts.', 'ai-awareness-day' ) ),
		array( 'As', __( 'Activation Steering', 'ai-awareness-day' ), 'reverse', 12, 7, __( 'Nudging a model’s internal signals while it responds, to strengthen or weaken a particular behaviour.', 'ai-awareness-day' ) ),
		array( 'Mx', __( 'Model Extraction', 'ai-awareness-day' ), 'reverse', 12, 8, __( 'Questioning an AI system again and again to copy its behaviour without access to its files. It can break terms of use and intellectual property law.', 'ai-awareness-day' ) ),
		array( 'Rd', __( 'Refusal Direction', 'ai-awareness-day' ), 'reverse', 12, 9, __( 'A pattern inside a model that researchers have linked to it declining unsafe or restricted requests.', 'ai-awareness-day' ) ),
		array( 'Ab', __( 'Abliteration', 'ai-awareness-day' ), 'reverse', 12, 10, __( 'Editing an open-weight model’s weights to remove its refusal direction, so it stops declining requests. It strips out the model’s safety protections.', 'ai-awareness-day' ) ),
		array( 'Uc', __( 'Uncensored Model', 'ai-awareness-day' ), 'reverse', 12, 11, __( 'An open-weight model that has been changed so its safety refusals are removed or bypassed, which makes harmful output easier to get.', 'ai-awareness-day' ) ),
		array( 'Jb', __( 'Jailbreaking', 'ai-awareness-day' ), 'reverse', 12, 12, __( 'Writing prompts designed to trick an AI into ignoring its safety rules, without changing the model itself. It usually breaks the tool’s terms of use.', 'ai-awareness-day' ) ),
	);
}

/**
 * The elements in reading order (row by row, left to right), numbered from 1.
 *
 * @return array<int, array{n:int, sym:string, id:string, name:string, family:string, row:int, col:int, def:string}>
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
			'Accel|erators', 'Adver|sarial', 'Analy|tics', 'Augment|ed', 'Charac|ter', 'Classi|fication', 'Cluster|ing', 'Distil|lation',
			'Docu|ment', 'Embed|ding', 'Engineer|ing', 'Fore|casting', 'Gover|nance', 'Hyper|auto|mation', 'Integ|ration', 'Intelli|gent',
			'Multi|modal', 'Orches|tration', 'Orches|trator', 'Para|meters', 'Predic|tive', 'Prescrip|tive', 'Process|ing', 'Proto|col',
			'Recog|nition', 'Reflec|tion', 'Regres|sion', 'Reinforce|ment', 'Retriev|al', 'Senti|ment', 'Un|super|vised', 'Super|vised',
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
					echo esc_html( sprintf( __( '%d AI terms in nine families. Read every definition at aiawarenessday.co.uk/ai-literacy-periodic-table', 'ai-awareness-day' ), $total ) );
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
				<p><?php esc_html_e( 'Middle: its symbol. Bottom: the term', 'ai-awareness-day' ); ?></p>
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

	<p class="alpt-footnote"><?php esc_html_e( 'Left: foundations. Centre: generative and predictive AI. Right: autonomous agents and automation. Below: the governance and infrastructure that hold it all together, the latest buzzwords, and how AI models are taken apart. Numbers run left to right, top to bottom.', 'ai-awareness-day' ); ?></p>

	<section class="alpt-glossary" aria-labelledby="alpt-glossary-title">
		<h2 id="alpt-glossary-title" class="alpt-glossary__title"><?php esc_html_e( 'Every element, family by family', 'ai-awareness-day' ); ?></h2>
		<p class="alpt-glossary__lead"><?php esc_html_e( 'All the definitions in one list, to read, copy into a lesson or print.', 'ai-awareness-day' ); ?></p>
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
							</dd>
						</div>
					<?php endforeach; ?>
				</dl>
			</details>
		<?php endforeach; ?>
	</section>

	<script type="application/json" data-alpt-data><?php echo wp_json_encode( array( 'families' => array_map( static fn( array $f ): string => $f['name'], $families ), 'elements' => array_map( static fn( array $el ): array => array( $el['sym'], $el['name'], $el['family'], $el['def'], $el['id'] ), $elements ) ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ); ?></script>
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
			'description'     => __( 'The interactive table of 141 AI terms, with every definition listed below it. Its elements are written in the theme (inc/literacy-table.php).', 'ai-awareness-day' ),
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
