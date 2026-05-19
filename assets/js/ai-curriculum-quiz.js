(function () {
	'use strict';

	var LEVELS = {
		overview: {
			key: 'overview',
			label: 'Overview',
			stageTitle: 'Online Safety & Digital Awareness',
			mode: 'discover',
			ages: 'All key stages',
			tagline: 'Foundations of digital literacy',
			intro:
				'We begin with the foundations of digital literacy: online safety, reliable information, responsible technology use, misinformation, passwords, and digital behaviour. These themes already form an important part of digital education across schools — before you explore how assessment style changes at each key stage.',
			ncPosition:
				'This project is not a new AI qualification or a replacement for the National Curriculum. These interactive cards help teachers from all subject areas experience familiar classroom formats inspired by current computing curriculum themes and assessment approaches.',
			objectives: [
				'Build confidence with online safety and digital awareness.',
				'Encourage discussion about misinformation and automated systems.',
				'Support digital literacy without requiring Computing specialist knowledge.',
				'Prepare staff for progressive assessment-style thinking at KS2–KS5.',
			],
			contentFocus:
				'Clickable safety cards with instant feedback: fake vs real examples, misinformation spotters, and responsible-use prompts — accessible for any subject teacher.',
			themes: [
				'Online safety',
				'Reliable information',
				'Digital behaviour',
				'Misinformation',
				'Passwords & privacy',
				'Responsible technology use',
			],
			nationalCurriculum: [
				{
					area: 'Computing & digital literacy (cross-phase)',
					text: 'Use technology safely, respectfully and responsibly; recognise acceptable and unacceptable behaviour; evaluate digital content and identify misleading information.',
				},
				{
					area: 'RSHE & safeguarding',
					text: 'Online relationships, personal data, and reporting concerns — the statutory backdrop for AI and chatbot conversations in school.',
				},
			],
			resources: [
				{
					label: 'DfE: Teaching online safety in schools',
					url: 'https://www.gov.uk/government/publications/teaching-online-safety-in-schools',
				},
				{
					label: 'Teach Computing — curriculum',
					url: 'https://teachcomputing.org/curriculum',
				},
			],
			examStyle: {
				title: 'Interactive online safety cards (not exams)',
				intro:
					'Large answer buttons, instant feedback, and scenario-based thinking. Designed for staff CPD and class discussion — not formal assessment.',
				commandWords: ['Which is safest?', 'What should you do?', 'Real or fake?', 'Can you trust this?'],
				note: 'Pair with your school acceptable-use and safeguarding policies.',
			},
			insights: [
				'Many teachers outside Computing now encounter AI-generated content and student questions about emerging technologies.',
				'Shared language on reliability and safeguarding helps every department contribute.',
			],
			questions: [
				{
					cardType: 'Misinformation',
					topic: 'Misinformation',
					title: 'Spot misleading information',
					marks: null,
					subject: 'Digital awareness',
					stem: 'A viral post claims a “miracle cure” with no named medical source. What is the best classroom response?',
					options: [
						'Share it quickly before it disappears',
						'Check trusted sources and discuss why sensational claims need evidence',
						'Assume social media is always accurate',
						'Ignore all health topics online',
					],
					answer: 1,
					examinerTip: 'Link to existing media-literacy habits — AI can amplify the same persuasion tricks as clickbait.',
					whyItMatters: 'Misinformation is the bridge between online safety and later source-evaluation questions.',
					markScheme: 'Credit evidence-checking, scepticism, and adult or reputable sources — not blanket distrust of all technology.',
					discuss: 'How is an AI chatbot answer similar to a viral post that sounds confident?',
					facilitator: 'Show one real headline and one AI-generated summary; compare what is missing.',
					team: 'Teams list three “check before you share” questions for any surprising claim.',
				},
				{
					cardType: 'Phishing',
					topic: 'Online safety',
					title: 'Phishing awareness',
					marks: null,
					subject: 'Digital awareness',
					stem: 'An email says “Your school account will be deleted — click here in 10 minutes.” What should staff tell pupils?',
					options: [
						'Click immediately to save time',
						'Do not click; report to a teacher or IT — urgency and threats are common phishing tactics',
						'Forward to every friend',
						'Reply with your password to prove it is you',
					],
					answer: 1,
					examinerTip: 'Urgency + fear is a pattern pupils see in scams and some AI “help” pop-ups.',
					whyItMatters: 'Protects accounts and models safe behaviour before pupils use AI tools independently.',
					markScheme: 'Report, do not click, never share credentials — align with school IT guidance.',
					discuss: 'What clues suggest an message is not from your real school systems?',
					facilitator: 'Use a sanitised example screenshot with obvious red flags highlighted.',
					team: 'Sort messages into “report” vs “probably OK to ask IT about”.',
				},
				{
					cardType: 'Passwords',
					topic: 'Passwords & privacy',
					title: 'Responsible technology use',
					marks: null,
					subject: 'Digital awareness',
					stem: 'Which habit best supports passwords and privacy across school devices?',
					options: [
						'Use the same simple password everywhere',
						'Strong unique passwords, lock screens, and never share login details',
						'Write passwords on a public noticeboard',
						'Share logins with friends to save time',
					],
					answer: 1,
					examinerTip: 'Keep technical detail light — focus on habits that transfer to AI account safety.',
					whyItMatters: 'Foundation for safe use of school-approved tools and chatbots later.',
					markScheme: 'Unique credentials, device security, no sharing — consistent with school AUP.',
					discuss: 'What personal details should never be pasted into a free public AI website?',
					facilitator: 'Revisit your school password policy in one sentence pupils can remember.',
					team: 'Draft a one-line “digital safety” rule for a classroom poster.',
				},
				{
					cardType: 'Trust check',
					topic: 'Reliable information',
					title: 'Can you trust this information?',
					marks: null,
					subject: 'Digital awareness',
					stem: 'Which is the best way to check if information online is trustworthy?',
					options: [
						'Believe the first website you find',
						'Ask one friend only and stop there',
						'Compare several trusted sources',
						'Share it immediately on social media',
					],
					answer: 2,
					examinerTip: 'This is the same habit pupils need before trusting chatbot answers.',
					whyItMatters: 'Reliable information underpins every key stage that follows.',
					markScheme: 'Compare multiple trusted sources (teacher, textbook, reputable sites) — not single unverified claims.',
					discuss: 'What counts as a “trusted source” in your subject?',
					facilitator: 'Quick poll: hands up for each option, then reveal and discuss.',
					team: 'Pairs name two trusted sources they would use in your subject.',
				},
			],
		},
		ks2: {
			key: 'ks2',
			label: 'KS2',
			stageTitle: 'Digital Awareness',
			mode: 'discover',
			ages: 'Ages 7–11',
			tagline: 'Accessible, supportive & interactive',
			intro:
				'At KS2 the focus is safe technology use, understanding digital tools, recognising trustworthy information, and beginning to think logically about systems. The experience should feel accessible and supportive — simple multiple-choice and scenario-based thinking, not formal exam papers.',
			ncPosition:
				'AI is not statutory at KS2. These cards extend existing computing expectations (safe use of technology, logical reasoning, networks) and prepare pupils for deeper work at KS3 — they are not SATs-style exam questions.',
			objectives: [
				'Spot when an answer might be AI-generated or unreliable.',
				'Know simple safe habits before trusting online information.',
				'Discuss AI in plain language without fear or hype.',
				'Build foundations for KS3 algorithms and assessment-style work.',
			],
			contentFocus:
				'Card types: multiple choice, trust checks, safe-use prompts, and “spot the mistake” — accessible, non-threatening, suitable for class discussions or assembly slots.',
			themes: ['Human or AI?', 'Trust & checking', 'Safe use', 'Spotting mistakes', 'Kind online behaviour'],
			nationalCurriculum: [
				{
					area: 'Computing (KS2) — statutory',
					text: 'Use logical reasoning; understand algorithms; use technology safely, respectfully and responsibly; recognise acceptable and unacceptable behaviour; understand computer networks including the internet.',
				},
				{
					area: 'Digital literacy (cross-curricular)',
					text: 'Evaluate digital content and identify misleading information — the skill behind “can you trust this chatbot answer?”',
				},
				{
					area: 'Proposed computing reforms (context)',
					text: 'England’s curriculum review is moving toward more explicit AI awareness at later key stages; KS2 remains the place to build safe, curious habits first.',
				},
			],
			resources: [
				{
					label: 'DfE: Computing programmes of study',
					url: 'https://www.gov.uk/government/publications/national-curriculum-in-england-computing-programmes-of-study/national-curriculum-in-england-computing-programmes-of-study',
				},
				{
					label: 'Teach Computing — KS2',
					url: 'https://teachcomputing.org/curriculum/key-stage-2',
				},
			],
			examStyle: {
				title: 'KS2 multiple-choice quiz cards (not exams)',
				intro:
					'No marks, timers, or extended writing. Use emoji voting, quick polls, pair-talk, and simple sorting activities. Keep sessions short and positive.',
				commandWords: ['Which one…?', 'What should you do…?', 'Can you trust…?', 'Human or AI?'],
				note: 'Designed for awareness and discussion — not formal assessment.',
			},
			insights: [
				'KS2 pupils can judge “sounds right” vs “check with a grown-up” without technical vocabulary.',
				'Games and cards lower anxiety compared with “test” language.',
				'Teachers in any subject can run a 10-minute awareness slot.',
			],
			questions: [
				{
					cardType: 'Multiple choice',
					topic: 'Trustworthy information',
					title: 'Check if information is trustworthy',
					marks: null,
					subject: 'Digital awareness',
					stem: 'Which of these is the best way to check if information online is trustworthy?',
					options: [
						'Believe the first website you find',
						'Ask one friend only',
						'Compare several trusted sources',
						'Share it immediately',
					],
					answer: 2,
					examinerTip: 'Celebrate comparing sources — not guessing from how professional a page looks.',
					whyItMatters: 'Core KS2 digital literacy before pupils meet AI-generated text.',
					markScheme: 'Credit comparing several trusted sources (teacher, book, reputable site).',
					discuss: 'What trusted sources does your school already use?',
					facilitator: 'Use large answer buttons or emoji voting for instant class feedback.',
					team: 'Teams list two trusted sources for a topic you teach this week.',
				},
				{
					cardType: 'Human or AI?',
					topic: 'Human or AI?',
					title: 'Which one was written by AI?',
					marks: null,
					subject: 'Awareness',
					stem: 'Two short animal descriptions are shown. One was written by a person, one by AI. What is the best classroom habit?',
					options: [
						'Always pick the longer one',
						'Look for clues, ask questions, and check with a teacher or book',
						'AI is always wrong',
						'Never use computers',
					],
					answer: 1,
					examinerTip: 'Celebrate curiosity — “How could we check?” matters more than getting it right first time.',
					whyItMatters: 'Builds critical reading before pupils meet chatbots for homework.',
					markScheme: 'Credit checking habits and healthy scepticism, not guessing from length or fancy words.',
					discuss: 'What clues gave it away — odd facts, tone, or something “too perfect”?',
					facilitator: 'Read both texts aloud; pupils vote with hands or emoji cards.',
					team: 'Pairs write one “check question” they would ask about any surprising fact online.',
				},
				{
					cardType: 'Trust check',
					topic: 'Trust & checking',
					title: 'Can you trust this chatbot answer?',
					marks: null,
					subject: 'Awareness',
					stem: 'A chatbot says: “Dinosaurs and humans lived at the same time.” It sounds confident. What should pupils do?',
					options: [
						'Copy it into their project',
						'Check with a trusted source because AI can be wrong',
						'Share it on social media',
						'Assume robots know everything',
					],
					answer: 1,
					examinerTip: 'Use a confident wrong answer pupils can spot — confidence is not the same as correctness.',
					whyItMatters: 'Early habit: verify before you believe.',
					markScheme: 'Trusted sources (teacher, book, reputable site) beat confident-sounding text.',
					discuss: 'Where else do we see confident claims that are false?',
					facilitator: 'Show a real safe example of checking in a children’s encyclopaedia or school resource.',
					team: 'Sort statements into “check first” vs “sounds OK to ask a teacher about”.',
				},
				{
					cardType: 'Safe use',
					topic: 'Safe use',
					title: 'Before you use AI information…',
					marks: null,
					subject: 'Awareness',
					stem: 'What should pupils always do before using information from a free AI website for homework?',
					options: [
						'Paste their full name and address',
						'Ask a teacher or parent, use school-approved tools, and never share private details',
						'Use the first answer only',
						'Skip reading it',
					],
					answer: 1,
					examinerTip: 'Link to existing e-safety rules — AI is another online tool, not a special case that ignores safeguarding.',
					whyItMatters: 'Protects privacy and keeps adults in the loop.',
					markScheme: 'Adult permission, approved tools, no personal data — align with school AUP.',
					discuss: 'What is private information we never paste online?',
					facilitator: 'Revisit the school acceptable-use poster.',
					team: 'Design a one-sentence “KS2 AI safety” class rule.',
				},
				{
					cardType: 'Spot the mistake',
					topic: 'Spotting mistakes',
					title: 'Spot the AI mistake',
					marks: null,
					subject: 'Awareness',
					stem: 'An AI picture caption says a goldfish is a mammal that barks. What is the best pupil response?',
					options: [
						'The AI must be right',
						'Notice the mistake and explain why it is wrong using what they already know',
						'Ignore captions',
						'Only trust pictures',
					],
					answer: 1,
					examinerTip: 'Use obvious errors so success is achievable — focus on the thinking process.',
					whyItMatters: 'Shows AI outputs can fail; human knowledge still matters.',
					markScheme: 'Identify error + simple justification from prior knowledge.',
					discuss: 'Could a picture look real but the words be wrong?',
					facilitator: 'Connect to “deepfake” as a word older pupils will meet — optional preview only.',
					team: 'Quick-fire: teams list three facts they would check before trusting a surprising image.',
				},
			],
		},
		ks3: {
			key: 'ks3',
			label: 'KS3',
			stageTitle: 'Algorithms & Programming Logic',
			mode: 'assessment',
			ages: 'Ages 11–14',
			tagline: 'Short-form Computing assessment style',
			intro:
				'At KS3 students develop stronger computational thinking: algorithms, sequencing, abstraction, decomposition, programming logic, and problem solving. This section lets non-specialist teachers experience the style of short-form Computing assessment — while still connecting to AI, reliability, and digital ethics where pupils meet them in daily life.',
			ncPosition:
				'Honest note: AI is not a statutory topic in the Key Stage 2 or Key Stage 3 national curriculum. AI Awareness Day questions are cross-curricular samples that extend published programmes of study — they are not an official DfE AI unit.',
			priorStage:
				'Pupils may have completed <strong>Digital Awareness</strong> at KS2 and <strong>Online Safety</strong> in the overview — short interactive cards, not exams. KS3 now introduces mark-based thinking without jumping to full GCSE 6-mark papers.',
			objectives: [
				'Bridge the gap between statutory computing / RSHE and the AI tools pupils already use outside lessons.',
				'Recognise that online outputs (including chatbots) can be wrong, biased, or unsafe.',
				'Apply “check, don’t assume” using sources the curriculum already values (textbooks, teachers, reputable sites).',
				'Agree classroom norms for tools the national curriculum never predicted.',
			],
			contentFocus:
				'Short scenarios in plain language — science reliability, citizenship stereotypes, English integrity, safeguarding. Samples are informed by NC computing and RSHE wording, not by a non-existent “KS3 AI” unit.',
			themes: [
				'What is AI?',
				'Training data',
				'AI mistakes',
				'Misinformation',
				'Deepfakes (intro)',
				'AI in school',
				'Privacy',
				'Bias basics',
			],
			nationalCurriculum: [
				{
					area: 'Computing programme of study (KS3) — statutory',
					text: 'Pupils must use technology safely, respectfully, responsibly and securely; recognise inappropriate content, contact and conduct; know how to report concerns. They must understand how instructions are stored and processed, and how data of various types can be used and misused.',
				},
				{
					area: 'Computing programme of study (KS3) — statutory',
					text: 'Pupils must design, use and evaluate computational abstractions; understand hardware and software; undertake creative projects using digital tools. AI tools can be discussed here as software that produces outputs pupils must evaluate — not accept blindly.',
				},
				{
					area: 'Science — working scientifically',
					text: 'Evaluate data and evidence, and explain findings using appropriate scientific language — applies when checking whether an AI explanation is trustworthy.',
				},
				{
					area: 'Citizenship & RSHE',
					text: 'Critical engagement with media, stereotypes, online relationships, and personal data — statutory RSHE expects schools to cover online safety and privacy; many pupils first meet generative tools in this context.',
				},
			],
			resources: [
				{
					label: 'DfE: Computing programmes of study (England)',
					url: 'https://www.gov.uk/government/publications/national-curriculum-in-england-computing-programmes-of-study/national-curriculum-in-england-computing-programmes-of-study',
				},
				{
					label: 'Teach Computing — KS3 curriculum map',
					url: 'https://teachcomputing.org/curriculum/key-stage-3',
				},
				{
					label: 'Teach Computing — KS2 curriculum map',
					url: 'https://teachcomputing.org/curriculum/key-stage-2',
				},
			],
			examStyle: {
				title: 'Emerging computer science assessment (KS3)',
				intro:
					'KS3 should feel like growing assessment literacy — not a GCSE paper. Use a mix of low-stakes marks before 6-mark GCSE-style items at KS4.',
				markTypes: [
					{ type: 'Multiple choice', marks: '1' },
					{ type: 'Define', marks: '1–2' },
					{ type: 'Explain', marks: '3' },
					{ type: 'Discuss', marks: '4' },
					{ type: 'Source evaluation', marks: '4–6' },
				],
				commandWords: ['Define', 'Explain', 'Identify', 'Discuss', 'Evaluate'],
				note: 'Illustrative school assessment mix at KS3 — not copied from a single GCSE mark scheme. Statutory NC computing includes algorithms and safe technology use; public exams with fixed tariffs start at GCSE.',
			},
			insights: [
				'The NC prepares pupils to be safe and computational thinkers — not necessarily to name “hallucination” or “training data”.',
				'AI Awareness Day gives shared language for tools pupils already use.',
				'School policies must fill the gap the statutory curriculum leaves open.',
			],
			questions: [
				{
					topic: 'Algorithms',
					title: 'Algorithm sequencing',
					marks: 1,
					markType: 'Multiple choice',
					subject: 'Computing',
					stem: 'A student creates an algorithm to make toast:\n\n1. Eat toast\n2. Put bread in toaster\n3. Turn toaster on\n4. Remove toast\n\nWhich step is incorrect?',
					options: ['Step 1', 'Step 2', 'Step 3', 'Step 4'],
					answer: 0,
					examinerTip: 'Order matters — the same logic applies when debugging programs or checking AI step-by-step outputs.',
					whyItMatters: 'Classic KS3 algorithm question format every teacher can recognise.',
					markScheme: 'Step 1 — you cannot eat toast before making it; algorithms must follow instructions in a logical order.',
					discuss: 'Where else do pupils follow steps in the wrong order (recipes, experiments, login flows)?',
					facilitator: 'Optional: drag-and-drop reorder on screen if you rebuild this as a full interactive later.',
					team: 'Teams rewrite the four steps in the correct order in 60 seconds.',
				},
				{
					topic: 'Algorithms',
					title: 'Explain correct order',
					marks: 3,
					markType: 'Explain',
					subject: 'Computing',
					stem: 'Explain why algorithms must follow instructions in the correct order.',
					examinerTip: 'Award marks for clear sequencing logic and one concrete example (e.g. toast, login, science method).',
					whyItMatters: 'Bridges multiple-choice sequencing to short explain questions on CS papers.',
					options: [
						'Order does not matter if the computer is fast',
						'Each step may depend on the previous one; wrong order produces wrong or unsafe results',
						'Algorithms are only used in English lessons',
						'Students should memorise random steps',
					],
					answer: 1,
					markScheme:
						'3 marks: clear explanation that steps depend on prior steps + example showing wrong order leads to failure or nonsense.',
					discuss: 'How is this similar to checking an AI’s “plan” before trusting the final answer?',
					facilitator: 'Staff draft three bullet points, then reveal the model reasoning.',
					team: 'Pairs give one school-subject example where order is essential.',
				},
				{
					topic: 'AI mistakes',
					title: 'Confident but wrong',
					marks: 3,
					markType: 'Explain',
					subject: 'Science',
					stem: 'A student asks a chatbot: “Why is the sky blue?” The answer sounds confident but includes a made-up scientist’s name. What is the best curriculum response?',
					examinerTip: 'Reward explanation of verification — not “ban all technology”.',
					whyItMatters: 'KS3 bridge between digital awareness habits and GCSE reliability questions.',
					options: [
						'Accept it if it sounds scientific',
						'Treat the chatbot as an infallible textbook',
						'Teach students to verify claims with trusted sources',
						'Ban all AI tools in science lessons',
					],
					answer: 2,
					markScheme:
						'Award credit for identifying hallucination / unreliable output and the need to verify with authoritative sources (e.g. textbook, teacher, reputable site).',
					discuss: 'Where else in school do students confuse confidence with correctness?',
					facilitator:
						'Link to Working Scientifically: ask students to compare two explanations and list what evidence they would need.',
					team:
						'Teams have 3 minutes to rewrite the chatbot answer as a bullet list with one verified fact and one “check this” flag.',
				},
				{
					topic: 'Bias basics',
					title: 'Define training data bias',
					marks: 2,
					markType: 'Define',
					subject: 'Computing',
					stem: 'An image generator always shows doctors as men and nurses as women. Which concept is most important for KS3 students to learn?',
					examinerTip: 'A clear definition plus one example earns low-tariff marks.',
					whyItMatters: 'Names the idea pupils will meet again in GCSE impacts questions.',
					options: ['Bandwidth', 'Training data bias', 'Keyboard shortcuts', 'Cloud storage'],
					answer: 1,
					markScheme:
						'Training data bias — models reflect patterns in data; outputs can reinforce stereotypes unless critically discussed.',
					discuss: 'Can students name one job stereotype they have seen in media or AI images?',
					facilitator:
						'Show two generated images with the same prompt; compare who is represented and who is missing.',
					team:
						'Each team writes a fair prompt for “people at work in a hospital” and explains one choice they made in wording.',
				},
				{
					topic: 'AI in school',
					title: 'AI in school policy',
					marks: 4,
					markType: 'Discuss',
					subject: 'English',
					stem: 'For a homework research paragraph, a school allows AI for brainstorming but not for final sentences. Why is that a reasonable policy?',
					examinerTip: 'Look for two sides: support vs skill development.',
					whyItMatters: 'Whole-school policy conversations start with explain/discuss at KS3.',
					options: [
						'AI cannot spell',
						'It keeps thinking and writing skills with the student',
						'Teachers prefer handwriting only',
						'The internet is always wrong',
					],
					answer: 1,
					markScheme:
						'Credit answers that separate support (ideas, planning) from substitution (authored work), preserving skill development and integrity.',
					discuss: 'What would “AI as coach, not ghostwriter” look like in your subject?',
					facilitator:
						'Display a simple traffic-light poster: green uses / amber discuss / red submit as your own work without declaration.',
					team:
						'Teams draft a 3-line “acceptable use” poster for Year 8 pupils in plain English.',
				},
				{
					topic: 'Privacy',
					title: 'Source evaluation',
					marks: 4,
					markType: 'Source evaluation',
					subject: 'Computing / PSHE',
					stem: 'A pupil pastes their full name, school, and home town into a free public AI website. What is the primary concern?',
					examinerTip: 'Link to RSHE online safety — data goes beyond the classroom.',
					whyItMatters: 'Safeguarding is the non-negotiable thread from KS2 through KS5.',
					options: [
						'The website will run slower',
						'Personal data may be stored or used beyond the classroom',
						'The font will change',
						'The answer will be too long',
					],
					answer: 1,
					markScheme:
						'Data privacy and safeguarding — pupils should use school-approved tools and minimise identifiable information.',
					discuss: 'What personal details are never OK to share with unknown online services?',
					facilitator:
						'Connect to your school safeguarding policy and approved AI tool list (or lack of one).',
					team:
						'Sort a list of prompts into “safe to try in class” vs “never paste” — teams justify one borderline case.',
				},
			],
		},
		ks4: {
			key: 'ks4',
			label: 'KS4',
			stageTitle: 'Ethics & Technology Evaluation',
			mode: 'investigate',
			ages: 'Ages 14–16',
			tagline: 'GCSE extended response (typically 6–9 marks)',
			intro:
				'At KS4 assessment becomes more analytical and evaluative: discussion questions, ethical scenarios, reliability investigations, and evaluation tasks. This section mirrors **GCSE Computer Science** impacts questions — chiefly on the “computer systems” paper for OCR, with similar extended items on AQA, Eduqas and WJEC.',
			ncPosition:
				'AI is not a named KS4 national curriculum topic. For many pupils the examined content sits in **GCSE Computer Science** specifications (e.g. OCR J277 section 1.6; AQA 8525 section 3.8). Samples here are shortened for CPD — always check the live paper and mark scheme your school enters.',
			objectives: [
				'Recognise that impacts questions are usually **6–9 marks** depending on board and paper — not one fixed format.',
				'Link AI themes (bias, reliability, privacy, legal/ethical impacts) to GCSE Computer Science specification content.',
				'Use mark-scheme thinking (developed linked points) when discussing classroom AI policy across subjects.',
				'Support whole-school literacy without duplicating the full CS paper in every department.',
			],
			contentFocus:
				'Sample questions are **Computer Science paper scenarios** in shortened multiple-choice form. In the real exam, pupils write extended responses (commonly **6 marks** on OCR Paper 1, **8 marks** for some OCR discuss items, **6 marks** on AQA Paper 1, **up to 9 marks** on AQA Paper 2) — not tick boxes.',
			themes: ['Hallucinated sources', 'Authorship & copyright', 'Reliability of reasoning', 'Safety & human judgement'],
			nationalCurriculum: [
				{
					area: 'National curriculum (KS4) — statutory provision',
					text: 'Schools must offer a balanced programme including computing so pupils can study GCSE Computer Science or technical awards. The detailed “what to teach” for 14–16 is set by qualifications, not a long AI section in the NC document.',
				},
				{
					area: 'OCR GCSE Computer Science (J277) — Component 01 Computer systems',
					text: 'Section 1.6: ethical, legal, cultural and environmental impacts. Past papers include **6-mark** discuss items (e.g. ethical and legal issues of a class website) and **8-mark** discuss items (e.g. smartphone upgrades — stakeholders, technology, ethical and environmental issues).',
				},
				{
					area: 'AQA GCSE Computer Science (8525)',
					text: 'Section 3.8: ethical, legal and environmental impacts. **Paper 1** includes a **6-mark** structured discussion (e.g. legal and ethical impacts of autonomous vehicles). **Paper 2** includes **9-mark** level-of-response items on privacy/data impacts (e.g. wearable health data).',
				},
				{
					area: 'Eduqas / WJEC GCSE Computer Science',
					text: 'Component 1 covers impacts of digital technology on wider society; essay-style ethical questions use level-of-response marking — decompose stakeholders, technology, and social/legal/ethical/environmental issues (see board mark schemes).',
				},
				{
					area: 'Cross-subject relevance',
					text: 'History, English, and art still benefit from CS paper habits (verify sources, declare tool use), but the examined AI/ethics statement usually lives in Computer Science.',
				},
			],
			resources: [
				{
					label: 'OCR GCSE Computer Science (J277) specification',
					url: 'https://www.ocr.org.uk/qualifications/gcse/computer-science-j277-from-2020/specification-at-a-glance/',
				},
				{
					label: 'AQA GCSE Computer Science (8525) specification',
					url: 'https://www.aqa.org.uk/subjects/computer-science/gcse/computer-science-8525/specification',
				},
				{
					label: 'Teach Computing — GCSE Computer Science',
					url: 'https://teachcomputing.org/gcse',
				},
			],
			examStyle: {
				title: 'GCSE Computer Science — extended impacts responses',
				intro:
					'Impacts questions appear on GCSE Computer Science papers — not on a separate “AI exam”. Mark tariffs vary by board: **OCR J277** often uses **6** or **8 marks** on **Paper 1 (Computer systems)**; **AQA 8525** uses **6 marks** on Paper 1 and **up to 9 marks** on Paper 2 for major discuss items.',
				commandWords: ['Explain', 'Discuss', 'Describe', 'Evaluate'],
				boardGuide: [
					{
						board: 'OCR J277',
						paper: 'Component 01 — Computer systems',
						marks: 'Typically 6 or 8 marks (Discuss)',
						example:
							'Discuss the ethical and legal issues Lauren will have to consider when setting up a class website. [6 marks] (OCR-style stem)',
					},
					{
						board: 'AQA 8525',
						paper: 'Paper 1 — 8525/1',
						marks: '6 marks (level of response)',
						example:
							'Structured discussion of legal and ethical impacts of increased use of autonomous vehicles (AQA June 2022 mark scheme — 5–6 band for both aspects developed).',
					},
					{
						board: 'AQA 8525',
						paper: 'Paper 2 — 8525/2',
						marks: '9 marks (level of response)',
						example:
							'Discussion of benefits and issues of collecting personal health-related data from wearable devices, including privacy and legal issues (top band 7–9).',
					},
				],
				sixMark: {
					paper: 'OCR J277 Paper 1 — typical 6-mark discuss (section 1.6 impacts)',
					exampleStem:
						'Discuss the ethical and legal issues a teacher must consider when pupils use an online space to share programs and discuss computing ideas. [6 marks]',
					markBands: [
						'5–6 marks (OCR-style): structured discussion with several relevant legal and ethical points developed and linked.',
						'3–4 marks: valid legal and/or ethical points with some development; may be one-sided.',
						'1–2 marks: limited or assertive points with little development.',
					],
				},
				note: 'Verified against OCR J277 specification at a glance, OCR ExamBuilder stems, and AQA 8525 published mark schemes (2022–2023). Your school’s entry may differ — always use the official mark scheme for the series sat.',
			},
			insights: [
				'Most KS4 pupils who meet examined AI ethics do so on the Computer Science GCSE paper — usually the computer systems / impacts paper, not the programming paper alone.',
				'Extended responses reward linked developed points whether the tariff is 6, 8 or 9 marks.',
				'Non-CS teachers can align policies (e.g. homework, privacy) with what CS colleagues already assess.',
			],
			questions: [
				{
					topic: 'Ethics & evaluation',
					title: 'Automated attendance monitoring',
					marks: 6,
					markType: 'Discuss',
					subject: 'GCSE Computer Science',
					stem: 'A school uses an automated system to monitor attendance. Discuss the possible benefits and risks of using this type of technology.',
					includes: ['fairness', 'accuracy', 'privacy', 'reliability'],
					suggestedMinutes: 8,
					examinerTip: 'Balanced arguments gain higher marks — benefits and risks with linked development.',
					whyItMatters: 'Aligned to GCSE impacts discuss items (e.g. OCR J277 Paper 1 — 6-mark style; similar reasoning on AQA 6- and 9-mark items).',
					options: [
						'Only benefits — faster registers',
						'Balanced: efficiency benefits vs fairness, accuracy, privacy, and reliability of automated decisions',
						'Ban all technology',
						'Pupils should not attend school',
					],
					answer: 1,
					markScheme:
						'6-mark impacts: developed points on fairness (bias in patterns), accuracy (wrong flags), privacy (data use), reliability (system errors) — logical chains.',
					discuss: 'How would this feel to pupils and parents?',
					facilitator: 'Staff try a 6-minute plan of bullet points, then compare to mark scheme.',
					team: 'Departments mark one sample paragraph using the band descriptors on the overview page.',
				},
				{
					topic: 'Reliability of online information',
					subject: 'GCSE Computer Science',
					marks: 6,
					markType: 'Discuss',
					stem: 'Discuss the issues if pupils use a chatbot for homework research that invents convincing but false “facts”.',
					suggestedMinutes: 8,
					examinerTip: 'Misinformation, false confidence, skill loss, verification — developed societal and individual impacts.',
					whyItMatters: 'Mirrors GCSE CS reliability themes.',
					options: [
						'Chatbots are always illegal in schools',
						'Unreliable outputs can spread misinformation; users must verify with trusted sources and teachers must set clear rules',
						'Students should memorise more dates',
						'The internet should be switched off',
					],
					answer: 1,
					markScheme:
						'6-mark CS impacts: credit reliability, misinformation risk, need for verification, school responsibility — developed linked points (privacy/accuracy may also feature).',
					discuss: 'How is this similar to spotting fake news headlines?',
					facilitator:
						'Give groups one AI-generated bibliography; they have 5 minutes to flag suspicious entries.',
					team:
						'Teams compete to find the weakest source in a mixed list of real and fake references.',
				},
				{
					topic: 'Legal & ethical impacts',
					subject: 'GCSE Computer Science',
					stem: 'A 6-mark scenario asks about pupils using an image generator for coursework without declaring it. Which issue would secure developed marks on an impacts question?',
					options: [
						'The colours are too bright',
						'Copyright, intellectual property, transparency, and fairness — plus school policy on declared AI use',
						'Printers use too much ink',
						'The file size is large',
					],
					answer: 1,
					markScheme:
						'6-mark CS impacts: IP/copyright, authenticity, equity between students, need for clear school rules — reward balanced ethical + legal points.',
					discuss: 'When is AI a tool like a camera filter vs when does it replace creative decisions?',
					facilitator:
						'Share your school’s current stance; if none exists, brainstorm a one-page department agreement.',
					team:
						'Teams create two labels: “AI-assisted” and “AI-generated” with one example of each for a poster project.',
				},
				{
					topic: 'Bias in data',
					subject: 'GCSE Computer Science',
					stem: 'Discuss how bias in training data could affect an AI hiring tool. [6 marks] — which theme is the question primarily testing?',
					options: [
						'Keyboard layout',
						'Social/ethical impacts of biased data leading to unfair outcomes',
						'Monitor refresh rate',
						'Binary addition',
					],
					answer: 1,
					markScheme:
						'6-mark CS impacts: training data bias, fairness, discrimination risk, need for oversight, possible legal/reputational harm — linked developed arguments.',
					discuss: 'Where do students already “show their working” — and where do they skip it with AI?',
					facilitator:
						'Compare an AI solution and a student solution; mark both with the same rubric focus on method.',
					team:
						'Pairs produce a two-column “trust / verify” checklist for using AI in maths homework.',
				},
				{
					topic: 'Automation & accountability',
					subject: 'GCSE Computer Science',
					stem: 'Explain the risks of a company using AI chatbots for customer refunds with little human checking. [6 marks] — what should a strong answer emphasise?',
					options: [
						'Faster keyboard typing',
						'Accountability, customer harm if the bot errs, need for human oversight, legal/reputational risk',
						'Cheaper office furniture',
						'More social media adverts',
					],
					answer: 1,
					markScheme:
						'6-mark CS impacts: automation risk, accountability, financial/legal harm, human-in-the-loop, trust — developed points with logical links.',
					discuss: 'What other subjects have “non-negotiable” safety rules AI cannot bend?',
					facilitator:
						'Use a think-pair-share on a scenario card; end with your school’s lab safety line.',
					team:
						'Teams write a 30-second “stop phrase” a class could use when AI advice looks unsafe or unrealistic.',
				},
			],
		},
		ks5: {
			key: 'ks5',
			label: 'KS5',
			stageTitle: 'Data Structures & Emerging Technologies',
			mode: 'debate',
			ages: 'Ages 16–18',
			tagline: 'A level extended response (typically 9 marks)',
			intro:
				'At KS5 students engage with deeper digital and computing concepts: data structures, abstraction, automation, digital ethics, accountability, and the wider impact of emerging technologies. On **OCR A level Computer Science (H446)**, major legal/ethical discuss items on **Component 01 (Computer systems)** are typically **9 marks** (not 6 or 12). Samples here use CPD-friendly multiple-choice reasoning.',
			ncPosition:
				'There is no KS5 national curriculum for AI. A level Computer Science assesses legal, moral, cultural and ethical issues with extended responses — commonly **9 marks** on OCR H446/01. Samples below are shortened for staff CPD; live papers use written extended answers. Longer essays may appear in EPQ or other subjects, not as standard OCR CS tariffs.',
			objectives: [
				'Recognise extended evaluation formats your CS department prepares for.',
				'Evaluate ethical, legal, and social issues of AI systems with developed linked arguments (as mark schemes require).',
				'Connect sixth-form AI use (coursework, EPQ) to the same reliability and accountability themes as the CS exam.',
				'Lead whole-school policy using vocabulary pupils already meet in Computer Science.',
			],
			contentFocus:
				'Sample questions mirror **A level Computer Science** impacts scenarios (automation, bias, privacy, accountability). On OCR H446, pupils typically write a **9-mark** extended discuss/evaluate response on Component 01; here, staff choose the best developed reasoning in multiple-choice form.',
			themes: ['Ethics & automated decisions', 'Academic reliability', 'Deployment & accountability', 'Whole-school leadership'],
			nationalCurriculum: [
				{
					area: 'OCR A level Computer Science (H446) — Component 01 Computer systems',
					text: 'Legal, moral, cultural and ethical issues. Sample assessment materials include **9-mark** discuss items (e.g. legal and ethical issues of a meal-plan system using personal health data) with mark bands **7–9 / 4–6 / 1–3**.',
				},
				{
					area: 'OCR GCSE → A level progression',
					text: 'Pupils who sat OCR J277 impacts questions at GCSE (often 6–8 marks on Paper 1) meet **longer 9-mark** reasoning at A level with more nuance (stakeholders, regulation, scale).',
				},
				{
					area: 'EPQ & other A levels',
					text: 'Extended writing integrity and source criticism remain vital when students use generative tools outside CS — but the examined impacts statement is usually on the CS specification.',
				},
			],
			resources: [
				{
					label: 'OCR A level Computer Science (H446) specification',
					url: 'https://www.ocr.org.uk/qualifications/as-and-a-level/computer-science-h446-h846-from-2015/specification-at-a-glance/',
				},
				{
					label: 'Teach Computing — A level Computer Science',
					url: 'https://teachcomputing.org/a-level',
				},
			],
			examStyle: {
				title: 'A level Computer Science — 9-mark impacts discuss',
				intro:
					'On **OCR H446**, extended legal/ethical questions are usually **9 marks** on **Component 01 (Computer systems)** — marked with bands **7–9 / 4–6 / 1–3** for a thorough, balanced discussion. Command words include **Discuss** and **Evaluate**. This is deeper than GCSE 6–8 mark items but is not typically a 12-mark CS exam question.',
				commandWords: ['Discuss', 'Explain', 'Evaluate', 'Assess', 'Analyse'],
				boardGuide: [
					{
						board: 'OCR H446',
						paper: 'Component 01 — Computer systems',
						marks: '9 marks (extended response *)',
						example:
							'Discuss the legal and ethical issues the company needs to consider for a system using personal health data to recommend meal plans. [9] (OCR SAM)',
					},
					{
						board: 'OCR H446',
						paper: 'Component 01 — Computer systems',
						marks: '9 marks (extended response *)',
						example:
							'Discuss whether or not you agree that video games have a negative effect — considering both points of view. [9] (OCR SAM)',
					},
				],
				sixMark: {
					paper: 'OCR H446/01 — typical 9-mark discuss (legal, moral, cultural & ethical issues)',
					exampleStem:
						'Discuss the legal and ethical issues a company must consider when using personal data to recommend meal plans for users. [9 marks]',
					markBands: [
						'7–9 marks (OCR band): thorough knowledge, well-balanced discussion, evaluative comments relevant and substantiated.',
						'4–6 marks: sound discussion with reasonable accuracy; some evaluative comments; may miss balance or depth.',
						'1–3 marks: limited discussion with basic or assertive points.',
					],
				},
				note: 'Verified against OCR H446/01 sample assessment materials and mark scheme (legal/ethical 9-mark items). If your sixth form enters a different board, check that board’s specification and live mark schemes.',
			},
			insights: [
				'KS5 AI ethics is most visible to examiners on the A level Computer Science paper — usually Component 01, not the programming component alone.',
				'9-mark items expect more depth and balance than GCSE 6–8 mark items but use the same “linked developed points” habit.',
				'Sixth-form leaders should align AI policies with what CS colleagues already assess.',
			],
			questions: [
				{
					topic: 'Emerging technologies',
					title: 'Legal & ethical issues of personal data systems',
					marks: 9,
					markType: 'Discuss',
					subject: 'A level Computer Science',
					stem: 'A company collects users’ weight, height, allergies and medical conditions to recommend meal plans using their activity data. Discuss the legal and ethical issues the company needs to consider for such a system.',
					includes: ['data privacy', 'consent', 'accuracy', 'health risk', 'legal duty'],
					suggestedMinutes: 12,
					examinerTip: 'OCR 9-mark banding rewards thorough, balanced discussion — if only legal OR ethical is considered, mark schemes often cap lower.',
					whyItMatters: 'Matches OCR H446/01-style 9-mark legal and ethical discuss (verified SAM stem, paraphrased).',
					options: [
						'Only mention the app icon design',
						'Thorough legal and ethical discussion: data use, consent, accuracy, health risk, GDPR/duty of care, balance and evaluation',
						'Ban all apps',
						'Ignore allergies',
					],
					answer: 1,
					markScheme:
						'9-mark OCR-style: 7–9 band for wide range of legal and ethical issues, applied to context, well-balanced with evaluative comments; 4–6 for sound but less developed; 1–3 for limited points.',
					discuss: 'How is this similar to AI attendance or grading tools collecting pupil data?',
					facilitator: 'Staff sketch a 9-minute plan with two legal and two ethical chains, then compare.',
					team: 'Teams list one legal and one ethical issue for a school meal-planning scenario.',
				},
				{
					topic: 'Emerging technologies',
					title: 'Trust automated public services?',
					marks: 9,
					markType: 'Evaluate',
					subject: 'A level Computer Science / CPD',
					stem: 'Discuss whether automated decision-making systems should be trusted in public services. You may refer to healthcare, policing, education, or transport.',
					includes: ['healthcare', 'policing', 'education', 'transport'],
					suggestedMinutes: 12,
					examinerTip: 'Framed as a 9-mark-style balanced discuss/evaluate — the depth expected at A level, not a typical 12-mark OCR CS tariff.',
					whyItMatters: 'Builds judgement on automation using the same habits as OCR 9-mark impacts questions.',
					options: [
						'Always trust automation because it is faster',
						'Balanced discussion: benefits (efficiency, scale) vs risks (bias, accountability, harm, oversight) with sector examples',
						'Ban all automation in every sector',
						'Only transport systems matter',
					],
					answer: 1,
					markScheme:
						'9-mark-style: developed chains across sectors, legal/ethical impacts, balance and judgement — not one-sided assertion.',
					discuss: 'Where must a human remain “in the loop”?',
					facilitator: 'Optional extension: pupils write a full 9-mark paragraph under timed conditions.',
					team: 'Teams assign one sector each and present one risk and one benefit in 90 seconds.',
				},
				{
					topic: 'Ethics & automated decisions',
					subject: 'A level Computer Science',
					stem: 'Discuss the ethical and legal issues of a council using AI to prioritise housing applications. [9 marks] — which response would earn the highest band?',
					options: [
						'The logo design matters most',
						'Fairness, transparency, bias, accountability, appeal rights, and impact on vulnerable residents — developed and linked',
						'How fast emails send',
						'The colour of the forms',
					],
					answer: 1,
					markScheme:
						'9-mark OCR-style: legal + ethical points developed with logical links; balance and specificity beat generic “AI is bad” statements (7–9 band).',
					discuss: 'Should humans always be able to explain why a decision was made?',
					facilitator:
						'Run a short debate: “Benefits outweigh risks” — assign roles (resident, councillor, developer).',
					team:
						'Teams list three questions journalists should ask about any “AI-powered” public service.',
				},
				{
					topic: 'Reliability & misinformation',
					subject: 'A level Computer Science',
					stem: 'Explain the social impacts if students trust generative AI for revision notes without checking sources. [9 marks] — what should a strong answer include?',
					options: [
						'It makes essays shorter',
						'Spread of misinformation, false confidence, skill loss, need for verification — developed societal and individual impacts',
						'Examiners prefer bullet points',
						'Quotes are not allowed',
					],
					answer: 1,
					markScheme:
						'9-mark-style: reliability, misinformation, education/societal consequences, critical thinking — reward developed chains not single assertions.',
					discuss: 'How is this similar to weak Wikipedia phrasing before editing?',
					facilitator:
						'Highlight three “AI filler” phrases on screen; students rewrite one paragraph with named evidence.',
					team:
						'Teams compete to redraft one paragraph with one statistic and one hedged claim (“some researchers argue…”).',
				},
				{
					topic: 'Deployment & accountability',
					subject: 'A level Computer Science',
					stem: 'Evaluate the impacts of deploying customer-service AI with minimal human oversight. [9 marks] — which theme is central?',
					options: [
						'Keyboard ergonomics',
						'Accountability, customer harm, legal/reputational risk, need for human-in-the-loop',
						'Social media marketing',
						'Office furniture costs',
					],
					answer: 1,
					markScheme:
						'9-mark-style: professional accountability, automation risk, stakeholder harm, regulation — developed evaluation with linked points.',
					discuss: 'Where should “human in the loop” be mandatory vs optional in services students use daily?',
					facilitator:
						'Case study: one real headline about AI customer service failure (you supply or students find).',
					team:
						'Teams design a one-page “deployment checklist” with five questions before going live with AI.',
				},
				{
					topic: 'Privacy & surveillance',
					subject: 'A level Computer Science',
					stem: 'Discuss the privacy issues of schools using AI to monitor students’ online activity. [9 marks] — which argument is most exam-appropriate?',
					options: [
						'Every student memorises model names',
						'Data collection, consent, proportionality, misuse risk, trust, and legal duties — balanced developed points',
						'All homework becomes AI-generated',
						'Computing teachers teach every lesson',
					],
					answer: 1,
					markScheme:
						'9-mark-style: privacy, surveillance, consent, GDPR context, safeguarding vs over-monitoring — logical developed chains.',
					discuss: 'What one habit should every department adopt by September?',
					facilitator:
						'Close with departments writing one sentence for their scheme of work.',
					team:
						'Faculty teams have 5 minutes to agree one “AI awareness outcome” for their subject on a sticky note wall.',
				},
			],
		},
	};

	var LEVEL_ORDER = ['overview', 'ks2', 'ks3', 'ks4', 'ks5'];

	function stageQuestionsLabel(lv) {
		if (lv.key === 'overview') {
			return 'Safety cards';
		}
		if (lv.mode === 'discover') {
			return 'Interactive cards';
		}
		return 'Assessment experience';
	}

	function stageOverviewLabel(lv) {
		if (lv.key === 'overview') {
			return 'About this section';
		}
		if (lv.mode === 'discover') {
			return 'About ' + lv.stageTitle;
		}
		return 'Curriculum & context';
	}

	function escapeHtml(str) {
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function initRoot(root) {
		if (!root || root.getAttribute('data-aiad-curriculum-ready') === '1') {
			return;
		}
		root.setAttribute('data-aiad-curriculum-ready', '1');

		var hub = root.querySelector('[data-cq-hub]');
		var main = root.querySelector('[data-cq-main]');
		var ksTabs = root.querySelector('[data-cq-ks-tabs]');
		var stageNav = root.querySelector('[data-cq-stage-nav]');
		var panel = root.querySelector('[data-cq-panel]');
		var facToggle = root.querySelector('[data-cq-facilitator]');
		var teamToggle = root.querySelector('[data-cq-team]');
		var progress = root.querySelector('[data-cq-progress]');

		if (!hub || !main || !panel) {
			return;
		}

		var state = {
			screen: 'hub',
			levelKey: 'ks3',
			levelView: 'overview',
			qIndex: 0,
			selectedChoice: null,
			facilitator: false,
			team: false,
			answered: {},
			done: {},
		};

		function level() {
			return LEVELS[state.levelKey];
		}

		function qKey() {
			return state.levelKey + '-' + state.qIndex;
		}

		function answerRecord() {
			return state.answered[qKey()] || null;
		}

		function isRevealed() {
			var rec = answerRecord();
			return rec && (rec.mode === 'tried' || rec.mode === 'revealed');
		}

		function showHub() {
			state.screen = 'hub';
			hub.hidden = false;
			main.hidden = true;
			root.classList.remove('cq-is-in-level');
			renderHub();
		}

		function enterLevel(levelKey, levelView) {
			state.screen = 'level';
			state.levelKey = levelKey;
			state.levelView = levelView || 'overview';
			state.qIndex = 0;
			state.selectedChoice = null;
			hub.hidden = true;
			main.hidden = false;
			root.classList.add('cq-is-in-level');
			renderKsTabs();
			renderStageNav();
			renderPanel();
		}

		function showQuestion(qIndex) {
			state.levelView = 'question';
			state.qIndex = qIndex;
			state.selectedChoice = null;
			var rec = answerRecord();
			if (rec && rec.mode === 'tried' && rec.choice >= 0) {
				state.selectedChoice = rec.choice;
			}
			renderKsTabs();
			renderStageNav();
			renderQuestion();
		}


		function renderHub() {
			var html =
				'<div class="cq-hub-card">' +
				'<p class="cq-eyebrow">AI Awareness Day</p>' +
				'<h2 class="cq-title">Exploring Digital &amp; Computing Assessment Across the Key Stages</h2>' +
				'<p class="cq-lead">Artificial intelligence is part of everyday conversations in education — from online safety and misinformation to algorithms, automation, and digital ethics. This experience is <strong>not</strong> a new AI qualification or a replacement for the National Curriculum. It helps teachers from all subject areas experience modern digital, IT, and Computer Science assessment using familiar classroom formats.</p>' +
				'<p class="cq-hub-accuracy" role="note"><strong>Exam accuracy:</strong> KS4/KS5 samples are checked against OCR and AQA published specifications and mark schemes (England). Mark tariffs differ by board and paper — always confirm with your department which specification you enter.</p>' +
				'<section class="cq-hub-section cq-hub-section--why">' +
				'<h3 class="cq-hub-section__title">Why this matters</h3>' +
				'<p>Many teachers outside Computing and IT now encounter AI-generated content, misinformation, automated systems, digital safeguarding, and questions from students about emerging technologies.</p>' +
				'<ul class="cq-hub-list">' +
				'<li>Build confidence</li>' +
				'<li>Encourage discussion</li>' +
				'<li>Support digital literacy</li>' +
				'<li>Explore how digital thinking develops across the key stages</li>' +
				'</ul>' +
				'<p class="cq-hub-section__foot">The aim is not to turn every teacher into a Computer Science specialist — but to provide a practical, engaging experience of the thinking students increasingly encounter in modern digital education.</p>' +
				'</section>' +
				'<h3 class="cq-hub-section__title cq-hub-section__title--pick">Choose a section</h3>' +
				'<div class="cq-hub-pick">';
			LEVEL_ORDER.forEach(function (key) {
				var lv = LEVELS[key];
				html +=
					'<button type="button" class="cq-hub-pick-card cq-hub-pick-card--' +
					key +
					'" data-cq-pick="' +
					key +
					'">' +
					'<span class="cq-hub-pick-card__stage">' +
					escapeHtml(lv.stageTitle) +
					'</span>' +
					'<span class="cq-hub-pick-card__label">' +
					escapeHtml(lv.label) +
					' · ' +
					escapeHtml(lv.ages) +
					'</span>' +
					'<span class="cq-hub-pick-card__tag">' +
					escapeHtml(lv.tagline) +
					'</span>' +
					'<span class="cq-hub-pick-card__cta">Open →</span></button>';
			});
			html +=
				'</div>' +
				'<section class="cq-hub-section cq-hub-section--close">' +
				'<h3 class="cq-hub-section__title">Why this experience matters</h3>' +
				'<p>Digital literacy is no longer confined to one subject. Teachers across disciplines explore reliability of information, responsible technology use, digital citizenship, and the impact of automated systems. The goal is not to replace existing curriculum structures — but to help teachers experience how digital and computing thinking develops progressively across the key stages.</p>' +
				'</section></div>';
			hub.innerHTML = html;
		}

		function renderKsTabs() {
			if (!ksTabs) {
				return;
			}
			var html = '';
			LEVEL_ORDER.forEach(function (key) {
				var lv = LEVELS[key];
				var cls = 'cq-ks-tab' + (key === state.levelKey ? ' is-active' : '');
				if (state.done[key]) {
					cls += ' is-done';
				}
				html +=
					'<button type="button" class="' +
					cls +
					'" data-cq-tab="' +
					key +
					'">' +
					escapeHtml(lv.stageTitle || lv.label) +
					'</button>';
			});
			ksTabs.innerHTML = html;
		}

		function renderStageNav() {
			if (!stageNav) {
				return;
			}
			if (state.screen !== 'level') {
				stageNav.hidden = true;
				return;
			}
			stageNav.hidden = false;
			var lv = level();
			var views = [
				{ id: 'overview', label: stageOverviewLabel(lv) },
				{ id: 'questions', label: stageQuestionsLabel(lv) },
			];
			var active = state.levelView;
			if (active === 'question' || active === 'complete') {
				active = 'questions';
			}
			var html = '';
			views.forEach(function (v) {
				var cls = 'cq-stage-tab' + (active === v.id ? ' is-active' : '');
				html +=
					'<button type="button" class="' +
					cls +
					'" data-cq-stage="' +
					v.id +
					'">' +
					escapeHtml(v.label) +
					'</button>';
			});
			stageNav.innerHTML = html;
		}

		function renderProgress() {
			if (!progress || state.levelView !== 'question') {
				if (progress) {
					progress.innerHTML = '';
				}
				return;
			}
			var lv = level();
			var html = '';
			var i;
			for (i = 0; i < lv.questions.length; i++) {
				var cls = 'cq-dot';
				if (i === state.qIndex) {
					cls += ' is-current';
				} else if (state.answered[state.levelKey + '-' + i]) {
					cls += ' is-done';
				}
				html +=
					'<button type="button" class="' +
					cls +
					'" data-cq-goto="' +
					i +
					'" aria-label="Question ' +
					(i + 1) +
					'"></button>';
			}
			progress.innerHTML = html;
		}

		function renderLevelOverview() {
			var lv = level();
			var ex = lv.examStyle;
			var html =
				'<div class="cq-page cq-page--' +
				state.levelKey +
				'">' +
				'<header class="cq-page-head">' +
				'<p class="cq-eyebrow">' +
				escapeHtml(lv.label) +
				' · ' +
				escapeHtml(lv.ages) +
				'</p>' +
				'<h3 class="cq-page-head__title">' +
				escapeHtml(lv.tagline) +
				'</h3>' +
				'<p class="cq-page-head__intro">' +
				escapeHtml(lv.intro) +
				'</p></header>';
			if (lv.ncPosition) {
				html +=
					'<div class="cq-nc-callout" role="note">' +
					'<strong>Curriculum position:</strong> ' +
					escapeHtml(lv.ncPosition) +
					'</div>';
			}
			if (lv.priorStage) {
				html +=
					'<div class="cq-prior-stage"><p>' + lv.priorStage + '</p></div>';
			}
			if (lv.ks2Bridge) {
				var b = lv.ks2Bridge;
				html +=
					'<section class="cq-section cq-section--ks2">' +
					'<h4 class="cq-section__title">' +
					escapeHtml(b.title) +
					'</h4>' +
					'<p>' +
					escapeHtml(b.text) +
					'</p><ul class="cq-nc-list">';
				b.refs.forEach(function (ref) {
					html += '<li>' + escapeHtml(ref) + '</li>';
				});
				html += '</ul></section>';
			}
			html +=
				'<section class="cq-section cq-section--nc">' +
				'<h4 class="cq-section__title">National curriculum links (England)</h4>' +
				'<p class="cq-section__lead">Statutory and qualification wording this key stage can lean on when teaching AI awareness — paraphrased for staff, not a replacement for official documents.</p>' +
				'<ul class="cq-nc-list">';
			lv.nationalCurriculum.forEach(function (item) {
				html +=
					'<li><strong>' +
					escapeHtml(item.area) +
					'</strong> ' +
					escapeHtml(item.text) +
					'</li>';
			});
			html += '</ul>';
			if (lv.resources && lv.resources.length) {
				html += '<p class="cq-resources-label">Official &amp; curriculum resources:</p><ul class="cq-resources">';
				lv.resources.forEach(function (r) {
					html +=
						'<li><a href="' +
						escapeHtml(r.url) +
						'" target="_blank" rel="noopener noreferrer">' +
						escapeHtml(r.label) +
						'</a></li>';
				});
				html += '</ul>';
			}
			html += '</section>' +
				'<section class="cq-section cq-section--exam">' +
				'<h4 class="cq-section__title">' +
				escapeHtml(ex.title) +
				'</h4>' +
				'<p>' +
				escapeHtml(ex.intro) +
				'</p>' +
				'<p class="cq-command-label">Typical command words at this stage:</p>' +
				'<div class="cq-theme-row">';
			ex.commandWords.forEach(function (w) {
				html += '<span class="cq-theme-pill cq-theme-pill--cmd">' + escapeHtml(w) + '</span>';
			});
			html += '</div>';
			if (ex.markTypes && ex.markTypes.length) {
				html +=
					'<table class="cq-mark-table"><thead><tr><th>Question type</th><th>Marks</th></tr></thead><tbody>';
				ex.markTypes.forEach(function (row) {
					html +=
						'<tr><td>' +
						escapeHtml(row.type) +
						'</td><td>' +
						escapeHtml(row.marks) +
						'</td></tr>';
				});
				html += '</tbody></table>';
			}
			if (ex.boardGuide && ex.boardGuide.length) {
				html +=
					'<h4 class="cq-section__title cq-section__title--sm">UK exam boards (verified examples)</h4>' +
					'<table class="cq-board-table"><thead><tr><th>Board</th><th>Paper</th><th>Marks</th><th>Example</th></tr></thead><tbody>';
				ex.boardGuide.forEach(function (row) {
					html +=
						'<tr><td>' +
						escapeHtml(row.board) +
						'</td><td>' +
						escapeHtml(row.paper) +
						'</td><td>' +
						escapeHtml(row.marks) +
						'</td><td>' +
						escapeHtml(row.example) +
						'</td></tr>';
				});
				html += '</tbody></table>';
			}
			if (ex.sixMark) {
				var sm = ex.sixMark;
				html +=
					'<div class="cq-six-mark">' +
					'<p class="cq-six-mark__paper"><strong>' +
					escapeHtml(sm.paper) +
					'</strong></p>' +
					'<p class="cq-six-mark__label">Example stem (as on the paper):</p>' +
					'<blockquote class="cq-six-mark__stem">' +
					escapeHtml(sm.exampleStem) +
					'</blockquote>' +
					'<p class="cq-six-mark__label">Typical mark bands (paraphrased):</p>' +
					'<ul class="cq-nc-list">';
				sm.markBands.forEach(function (band) {
					html += '<li>' + escapeHtml(band) + '</li>';
				});
				html += '</ul></div>';
			}
			html +=
				'<p class="cq-disclaimer">' +
				escapeHtml(ex.note) +
				'</p></section>' +
				'<section class="cq-section">' +
				'<h4 class="cq-section__title">Sample learning objectives (AI Awareness Day — extends NC)</h4>' +
				'<ul class="cq-objectives">';
			lv.objectives.forEach(function (obj) {
				html += '<li>' + escapeHtml(obj) + '</li>';
			});
			html +=
				'</ul></section>' +
				'<section class="cq-section">' +
				'<h4 class="cq-section__title">Content focus</h4>' +
				'<p>' +
				escapeHtml(lv.contentFocus) +
				'</p>' +
				'<h4 class="cq-section__title cq-section__title--sm">Themes in the sample questions</h4>' +
				'<div class="cq-theme-row">';
			lv.themes.forEach(function (t) {
				html += '<span class="cq-theme-pill">' + escapeHtml(t) + '</span>';
			});
			html +=
				'</div></section>' +
				'<div class="cq-actions">' +
				'<button type="button" class="cq-btn cq-btn--primary" data-cq-stage="questions">Go to sample questions</button>' +
				'<button type="button" class="cq-btn" data-cq-back-hub>All key stages</button>' +
				'</div></div>';
			panel.innerHTML = html;
			if (progress) {
				progress.innerHTML = '';
			}
		}

		function renderLevelQuestionsList() {
			var lv = level();
			var listIntro =
				'Cards for staff to <strong>try</strong> or <strong>reveal</strong> guidance. Turn on facilitator notes or team challenges in the toolbar above.';
			if (lv.key === 'overview') {
				listIntro =
					'<strong>Online Safety &amp; Digital Awareness</strong> — clickable safety cards with instant feedback. No exam marks.';
			} else if (lv.mode === 'discover') {
				listIntro =
					'<strong>Digital Awareness</strong> — no exam marks. Large buttons, instant feedback, and scenario-based thinking. Not SATs-style papers.';
			} else if (lv.mode === 'assessment') {
				listIntro =
					'<strong>Algorithms &amp; Programming Logic</strong> — emerging marks (1–6). Short Computing assessment literacy, not full GCSE papers.';
			} else if (lv.mode === 'investigate') {
				listIntro =
					'<strong>Ethics &amp; Technology Evaluation</strong> — GCSE-style <strong>6-mark</strong> scenarios. In the exam pupils write a paragraph; here you judge the best reasoning.';
			} else if (lv.mode === 'debate') {
				listIntro =
					'<strong>Data Structures &amp; Emerging Technologies</strong> — OCR-style <strong>9-mark</strong> extended discuss/evaluate on impacts (typical A level CS tariff).';
			}
			var html =
				'<div class="cq-page cq-page--questions">' +
				'<header class="cq-page-head">' +
				'<h3 class="cq-page-head__title">' +
				escapeHtml(lv.label) +
				' sample questions</h3>' +
				'<p class="cq-page-head__intro">' +
				listIntro +
				'</p></header>' +
				'<ul class="cq-q-list">';
			lv.questions.forEach(function (q, i) {
				var done = state.answered[state.levelKey + '-' + i];
				html +=
					'<li><button type="button" class="cq-q-list-item' +
					(done ? ' is-done' : '') +
					'" data-cq-goto="' +
					i +
					'">' +
					'<span class="cq-q-list-item__n">' +
					(q.marks ? escapeHtml(String(q.marks)) + ' marks · ' : '') +
					'Card ' +
					(i + 1) +
					'</span>' +
					'<span class="cq-q-list-item__topic">' +
					escapeHtml(q.title || q.topic) +
					'</span>' +
					'<span class="cq-q-list-item__sub">' +
					escapeHtml(q.subject) +
					'</span></button></li>';
			});
			html +=
				'</ul>' +
				'<div class="cq-actions">' +
				'<button type="button" class="cq-btn cq-btn--primary" data-cq-start-q>Start question 1</button>' +
				'<button type="button" class="cq-btn" data-cq-stage="overview">← Curriculum & context</button>' +
				'</div></div>';
			panel.innerHTML = html;
			if (progress) {
				progress.innerHTML = '';
			}
		}

		function markSchemeHeading(lv) {
			return lv.mode === 'discover' ? 'Reveal guidance' : 'Mark scheme guidance';
		}

		function tryHintForLevel(lv) {
			if (lv.mode === 'discover') {
				return 'Pick an option to <strong>check</strong>, or <strong>reveal guidance</strong> for class discussion — no marks recorded.';
			}
			if (lv.mode === 'investigate' || lv.mode === 'debate') {
				return 'In the exam pupils write a paragraph. Here, choose the reasoning that would earn the highest band, or <strong>reveal</strong> the model answer and notes.';
			}
			return 'Select an option to <strong>check your answer</strong>, or <strong>reveal answer &amp; notes</strong> to skip to the model response.';
		}

		function renderQuestionCardHeader(q, lv) {
			var tipLabel = lv.mode === 'discover' ? 'Facilitator tip' : 'Examiner tip';
			var html =
				'<header class="cq-q-card__head cq-q-card__head--' +
				escapeHtml(lv.key) +
				'">';
			if (q.title) {
				html += '<h3 class="cq-q-card__title">' + escapeHtml(q.title) + '</h3>';
			}
			html += '<div class="cq-q-card__badges">';
			if (q.marks) {
				html +=
					'<span class="cq-q-card__marks">' +
					escapeHtml(String(q.marks)) +
					' marks</span>';
				if (q.markType) {
					html +=
						'<span class="cq-q-card__mark-type">' +
						escapeHtml(q.markType) +
						'</span>';
				}
			} else if (q.cardType) {
				html +=
					'<span class="cq-q-card__marks cq-q-card__marks--discover">' +
					escapeHtml(q.cardType) +
					'</span>';
			}
			if (q.suggestedMinutes) {
				html +=
					'<span class="cq-q-card__timer" aria-label="Suggested time">' +
					'⏱ Suggested time: ' +
					escapeHtml(String(q.suggestedMinutes)) +
					' minutes</span>';
			}
			html += '</div>';
			if (q.includes && q.includes.length) {
				html +=
					'<p class="cq-q-card__includes-label">You should include:</p><ul class="cq-q-card__includes">';
				q.includes.forEach(function (item) {
					html += '<li>' + escapeHtml(item) + '</li>';
				});
				html += '</ul>';
			}
			if (q.examinerTip) {
				html +=
					'<div class="cq-q-card__tip" role="note">' +
					'<span class="cq-q-card__tip-label">💡 ' +
					escapeHtml(tipLabel) +
					'</span>' +
					'<p>' +
					escapeHtml(q.examinerTip) +
					'</p></div>';
			}
			if (q.marks >= 6 && lv.mode !== 'discover') {
				var writeHint =
					q.marks >= 9
						? ' — plan a sustained 9-mark-style response in CPD, then compare with the model below.'
						: ' — plan a balanced paragraph in CPD, then compare with the model below.';
				html +=
					'<p class="cq-q-card__write"><span class="cq-q-card__write-cta">Start writing</span>' +
					writeHint +
					'</p>';
			}
			html += '</header>';
			return html;
		}

		function renderFeedbackBlocks(q) {
			var html = '<div class="cq-feedback">';
			if (q.whyItMatters) {
				html +=
					'<div class="cq-block cq-block--why"><h4>Why this matters</h4><p>' +
					escapeHtml(q.whyItMatters) +
					'</p></div>';
			}
			html +=
				'<div class="cq-block cq-block--answer"><h4>Model answer</h4><p><strong>' +
				escapeHtml(q.options[q.answer]) +
				'</strong></p></div>' +
				'<div class="cq-block cq-block--mark"><h4>' +
				escapeHtml(markSchemeHeading(level())) +
				'</h4><p>' +
				escapeHtml(q.markScheme) +
				'</p></div>' +
				'<div class="cq-block cq-block--discuss"><h4>Discussion prompt</h4><p>' +
				escapeHtml(q.discuss) +
				'</p></div>';
			if (state.facilitator) {
				html +=
					'<div class="cq-block cq-block--fac"><h4>Facilitator note</h4><p>' +
					escapeHtml(q.facilitator) +
					'</p></div>';
			}
			if (state.team) {
				html +=
					'<div class="cq-block cq-block--team"><h4>Team challenge</h4><p>' +
					escapeHtml(q.team) +
					'</p></div>';
			}
			html += '</div>';
			return html;
		}

		function renderQuestion() {
			var lv = level();
			if (state.qIndex >= lv.questions.length) {
				renderLevelComplete();
				return;
			}
			var q = lv.questions[state.qIndex];
			var rec = answerRecord();
			var revealed = isRevealed();
			renderProgress();

			var cardNoun = lv.mode === 'discover' ? 'Card' : 'Question';
			var html =
				'<article class="cq-question cq-question--' +
				escapeHtml(lv.key) +
				'">' +
				renderQuestionCardHeader(q, lv) +
				'<p class="cq-q-meta"><span class="cq-topic">' +
				escapeHtml(q.topic) +
				'</span> · <span class="cq-subject">' +
				escapeHtml(q.subject) +
				'</span> · ' +
				cardNoun +
				' ' +
				(state.qIndex + 1) +
				' of ' +
				lv.questions.length +
				'</p>' +
				'<p class="cq-stem">' +
				escapeHtml(q.stem) +
				'</p>' +
				'<p class="cq-try-hint">' +
				tryHintForLevel(lv) +
				'</p>' +
				'<div class="cq-options" role="group" aria-label="Answer options">';
			q.options.forEach(function (opt, i) {
				var cls = 'cq-option';
				if (revealed) {
					if (i === q.answer) {
						cls += ' is-correct';
					} else if (rec && rec.mode === 'tried' && rec.choice === i) {
						cls += ' is-wrong';
					}
				} else if (state.selectedChoice === i) {
					cls += ' is-selected';
				}
				html +=
					'<button type="button" class="' +
					cls +
					'" data-cq-opt="' +
					i +
					'"' +
					(revealed ? ' disabled' : '') +
					'>' +
					escapeHtml(opt) +
					'</button>';
			});
			html += '</div>';

			if (!revealed) {
				html +=
					'<div class="cq-actions cq-actions--try">' +
					'<button type="button" class="cq-btn cq-btn--primary" data-cq-check' +
					(state.selectedChoice === null ? ' disabled' : '') +
					'>Check my answer</button>' +
					'<button type="button" class="cq-btn cq-btn--ghost" data-cq-reveal>Reveal answer &amp; notes</button>' +
					'</div>';
			} else {
				if (rec && rec.mode === 'tried' && rec.choice !== q.answer) {
					html +=
						'<p class="cq-try-result cq-try-result--wrong">Not quite — compare your choice with the model answer below.</p>';
				} else if (rec && rec.mode === 'tried') {
					html += '<p class="cq-try-result cq-try-result--ok">Well judged — that aligns with the model answer.</p>';
				}
				html += renderFeedbackBlocks(q);
				html +=
					'<div class="cq-actions">' +
					'<button type="button" class="cq-btn" data-cq-stage="questions">← Question list</button>' +
					'<button type="button" class="cq-btn cq-btn--primary" data-cq-next-q">' +
					(state.qIndex < lv.questions.length - 1 ? 'Next question' : 'Finish ' + lv.label) +
					'</button></div>';
			}
			html += '</article>';
			panel.innerHTML = html;
		}

		function renderLevelComplete() {
			var lv = level();
			var nextIdx = LEVEL_ORDER.indexOf(state.levelKey) + 1;
			var nextKey = LEVEL_ORDER[nextIdx];
			var html =
				'<div class="cq-complete">' +
				'<h3 class="cq-complete__title">' +
				escapeHtml(lv.label) +
				' — what you have tasted</h3>' +
				'<p class="cq-complete__intro">' +
				escapeHtml(lv.intro) +
				'</p>' +
				'<h4 class="cq-complete__h">Curriculum objectives</h4>' +
				'<ul class="cq-insights">';
			lv.objectives.forEach(function (line) {
				html += '<li>' + escapeHtml(line) + '</li>';
			});
			html += '</ul><h4 class="cq-complete__h">Key takeaways</h4><ul class="cq-insights">';
			lv.insights.forEach(function (line) {
				html += '<li>' + escapeHtml(line) + '</li>';
			});
			html += '</ul><div class="cq-actions">';
			if (nextKey) {
				html +=
					'<button type="button" class="cq-btn cq-btn--primary" data-cq-next-level="' +
					nextKey +
					'">Taste ' +
					escapeHtml(LEVELS[nextKey].label) +
					' next</button>';
			} else {
				html +=
					'<button type="button" class="cq-btn cq-btn--primary" data-cq-all-done>Compare all key stages</button>';
			}
			html +=
				'<button type="button" class="cq-btn" data-cq-back-hub>Back to overview</button></div></div>';
			panel.innerHTML = html;
			state.levelView = 'complete';
			state.done[state.levelKey] = true;
			renderKsTabs();
			renderStageNav();
			if (progress) {
				progress.innerHTML = '';
			}
		}

		function renderAllComplete() {
			var html =
				'<div class="cq-complete cq-complete--all">' +
				'<h3 class="cq-complete__title">How KS3, KS4 &amp; KS5 differ</h3>' +
				'<p class="cq-complete__intro">You have sampled objectives, content, and assessment-style questions across all three key stages. The shift is from <strong>habits and safety</strong> → <strong>evidence and policy</strong> → <strong>ethics and society</strong>.</p>' +
				'<div class="cq-summary-grid">';
			LEVEL_ORDER.forEach(function (key) {
				var lv = LEVELS[key];
				html +=
					'<div class="cq-summary-card cq-summary-card--' +
					key +
					'"><h4>' +
					escapeHtml(lv.label) +
					'</h4><p class="cq-summary-card__tag">' +
					escapeHtml(lv.tagline) +
					'</p><ul>';
				lv.objectives.slice(0, 2).forEach(function (line) {
					html += '<li>' + escapeHtml(line) + '</li>';
				});
				html += '</ul></div>';
			});
			html +=
				'</div><div class="cq-actions"><button type="button" class="cq-btn cq-btn--primary" data-cq-back-hub">Back to overview</button></div></div>';
			panel.innerHTML = html;
		}

		function renderPanel() {
			if (state.screen === 'all-done') {
				renderAllComplete();
				return;
			}
			if (state.levelView === 'overview') {
				renderLevelOverview();
				return;
			}
			if (state.levelView === 'questions') {
				renderLevelQuestionsList();
				return;
			}
			if (state.levelView === 'question') {
				renderQuestion();
				return;
			}
			if (state.levelView === 'complete') {
				renderLevelComplete();
			}
		}

		function markTried(choice) {
			state.answered[qKey()] = { mode: 'tried', choice: choice };
			renderQuestion();
			panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
		}

		function markRevealed() {
			var rec = answerRecord();
			var choice = rec && rec.mode === 'tried' ? rec.choice : state.selectedChoice;
			state.answered[qKey()] = { mode: 'revealed', choice: choice };
			renderQuestion();
			panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
		}

		hub.addEventListener('click', function (e) {
			var pick = e.target.closest('[data-cq-pick]');
			if (pick) {
				enterLevel(pick.getAttribute('data-cq-pick'), 'overview');
			}
		});

		root.addEventListener('click', function (e) {
			var tab = e.target.closest('[data-cq-tab]');
			if (tab) {
				enterLevel(tab.getAttribute('data-cq-tab'), 'overview');
				return;
			}
			var stage = e.target.closest('[data-cq-stage]');
			if (stage) {
				state.levelView = stage.getAttribute('data-cq-stage');
				renderStageNav();
				renderPanel();
				return;
			}
			var goto = e.target.closest('[data-cq-goto]');
			if (goto) {
				showQuestion(parseInt(goto.getAttribute('data-cq-goto'), 10));
				return;
			}
			if (e.target.closest('[data-cq-start-q]')) {
				showQuestion(0);
				return;
			}
			var opt = e.target.closest('[data-cq-opt]');
			if (opt && !isRevealed()) {
				state.selectedChoice = parseInt(opt.getAttribute('data-cq-opt'), 10);
				renderQuestion();
				return;
			}
			if (e.target.closest('[data-cq-check]')) {
				if (state.selectedChoice !== null) {
					markTried(state.selectedChoice);
				}
				return;
			}
			if (e.target.closest('[data-cq-reveal]')) {
				markRevealed();
				return;
			}
			if (e.target.closest('[data-cq-next-q]')) {
				state.qIndex += 1;
				state.selectedChoice = null;
				if (state.qIndex >= level().questions.length) {
					renderLevelComplete();
				} else {
					showQuestion(state.qIndex);
				}
				return;
			}
			var nl = e.target.closest('[data-cq-next-level]');
			if (nl) {
				enterLevel(nl.getAttribute('data-cq-next-level'), 'overview');
				return;
			}
			if (e.target.closest('[data-cq-all-done]')) {
				state.screen = 'all-done';
				state.levelView = 'all-done';
				LEVEL_ORDER.forEach(function (k) {
					state.done[k] = true;
				});
				renderKsTabs();
				renderStageNav();
				renderAllComplete();
				return;
			}
			if (e.target.closest('[data-cq-back-hub]')) {
				showHub();
			}
		});

		if (facToggle) {
			facToggle.addEventListener('change', function () {
				state.facilitator = facToggle.checked;
				if (state.levelView === 'question' && isRevealed()) {
					renderQuestion();
				}
			});
		}
		if (teamToggle) {
			teamToggle.addEventListener('change', function () {
				state.team = teamToggle.checked;
				if (state.levelView === 'question' && isRevealed()) {
					renderQuestion();
				}
			});
		}

		showHub();
	}

	function boot() {
		document.querySelectorAll('[data-aiad-curriculum-quiz]').forEach(initRoot);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
