(function () {
	'use strict';

	var LEVELS = {
		ks3: {
			key: 'ks3',
			label: 'KS3',
			ages: 'Ages 11–14',
			tagline: 'Building foundations: question, check, and stay safe online',
			intro:
				'At KS3, the curriculum emphasis is on digital literacy, online safety, and beginning to question how information is created — including by AI.',
			objectives: [
				'Recognise that AI outputs can be wrong, biased, or unsafe — and are not neutral facts.',
				'Begin verifying information using trusted sources (not only the tool that generated it).',
				'Understand basic data privacy: what should never be pasted into unknown public tools.',
				'Develop classroom norms for when AI supports learning vs when it replaces thinking.',
			],
			contentFocus:
				'Short scenarios, plain language, and cross-curricular hooks (science explanations, citizenship stereotypes, English homework policy). Assessment-style language is lighter — the goal is habits, not exam technique.',
			themes: ['Reliability & hallucinations', 'Bias in training data', 'Classroom AI use', 'Safeguarding & privacy'],
			insights: [
				'Reliability and source-checking belong in every subject, not only computing.',
				'Bias can appear in images and text before students write their first essay.',
				'Clear classroom norms prevent “AI did my homework” becoming the default.',
			],
			questions: [
				{
					topic: 'Hallucinations & reliability',
					subject: 'Science',
					stem: 'A student asks a chatbot: “Why is the sky blue?” The answer sounds confident but includes a made-up scientist’s name. What is the best curriculum response?',
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
					topic: 'Bias in training data',
					subject: 'Citizenship',
					stem: 'An image generator always shows doctors as men and nurses as women. Which concept is most important for KS3 students to learn?',
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
					topic: 'Classroom AI use',
					subject: 'English',
					stem: 'For a homework research paragraph, a school allows AI for brainstorming but not for final sentences. Why is that a reasonable policy?',
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
					topic: 'Safeguarding & privacy',
					subject: 'Computing / PSHE',
					stem: 'A pupil pastes their full name, school, and home town into a free public AI website. What is the primary concern?',
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
			ages: 'Ages 14–16',
			tagline: 'Formal assessment language meets real-world AI judgement',
			intro:
				'At KS4, students meet more formal assessment language: evaluate, justify, assess reliability — the same skills needed to judge AI outputs critically.',
			objectives: [
				'Evaluate reliability of sources and evidence — including AI-generated references and worked solutions.',
				'Justify decisions about authorship, transparency, and school policy on declared AI use.',
				'Apply “human in the loop” where safety, copyright, or professional judgement cannot be delegated.',
				'Use GCSE-style command words (evaluate, assess) when critiquing AI outputs.',
			],
			contentFocus:
				'Exam-style stems across history, art, maths, and science. Questions mirror how boards expect students to weigh evidence, not memorise tool names. No board-specific mark bands — generic guidance only.',
			themes: ['Hallucinated sources', 'Authorship & copyright', 'Reliability of reasoning', 'Safety & human judgement'],
			insights: [
				'Hallucinated references are a literacy issue across history, science, and English.',
				'Copyright and authorship questions appear as soon as AI creates images or text.',
				'GCSE-style “evaluate” commands prepare students for workplace AI literacy too.',
			],
			questions: [
				{
					topic: 'Hallucinated sources',
					subject: 'History',
					stem: 'An AI essay includes a quotation from a “2022 UNESCO report on medieval York” that does not exist. What skill are examiners really testing when they warn about this?',
					options: [
						'Memorising dates only',
						'Source reliability and verification',
						'Typing speed',
						'Using the longest words possible',
					],
					answer: 1,
					markScheme:
						'Hallucinated sources — students must verify provenance, date, and authority before using evidence.',
					discuss: 'How is this similar to spotting fake news headlines?',
					facilitator:
						'Give groups one AI-generated bibliography; they have 5 minutes to flag suspicious entries.',
					team:
						'Teams compete to find the weakest source in a mixed list of real and fake references.',
				},
				{
					topic: 'Authorship & copyright',
					subject: 'Art & Design',
					stem: 'A student submits AI-generated artwork for GCSE without saying how it was made. Which issue is most relevant?',
					options: [
						'The colours are too bright',
						'Authorship, transparency, and school policy on declared AI use',
						'Printers use too much ink',
						'The file size is large',
					],
					answer: 1,
					markScheme:
						'Credit discussion of intellectual property, authenticity of portfolio work, and following centre policy on AI disclosure.',
					discuss: 'When is AI a tool like a camera filter vs when does it replace creative decisions?',
					facilitator:
						'Share your school’s current stance; if none exists, brainstorm a one-page department agreement.',
					team:
						'Teams create two labels: “AI-assisted” and “AI-generated” with one example of each for a poster project.',
				},
				{
					topic: 'Reliability of reasoning',
					subject: 'Mathematics',
					stem: 'A model solves a problem but the steps contain a subtle arithmetic error. The final answer is wrong. What is the best student habit?',
					options: [
						'Trust the last line only',
						'Check reasoning step-by-step and with a second method',
						'Copy the steps without reading',
						'Avoid word problems entirely',
					],
					answer: 1,
					markScheme:
						'Reliability — AI can mimic worked solutions; mathematical reasoning must still be validated.',
					discuss: 'Where do students already “show their working” — and where do they skip it with AI?',
					facilitator:
						'Compare an AI solution and a student solution; mark both with the same rubric focus on method.',
					team:
						'Pairs produce a two-column “trust / verify” checklist for using AI in maths homework.',
				},
				{
					topic: 'Safety & human judgement',
					subject: 'Science',
					stem: 'In a practical lesson, AI suggests an unsafe experiment. What should the curriculum prioritise?',
					options: [
						'Following any online advice quickly',
						'Human teacher judgement and risk assessment always override AI',
						'Letting the fastest student try first',
						'Replacing practicals with videos only',
					],
					answer: 1,
					markScheme:
						'Human in the loop — professional responsibility and safety protocols cannot be delegated to generative tools.',
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
			ages: 'Ages 16–18',
			tagline: 'Ethics, society, and extended argument — not just tool tips',
			intro:
				'At KS5, students should connect AI literacy to ethics, society, and future study — evaluating trade-offs, not only tool tips.',
			objectives: [
				'Evaluate ethical trade-offs in automated decision-making (fairness, transparency, accountability).',
				'Critique authoritative-sounding AI prose that lacks evidence or nuance.',
				'Analyse deployment risks in business and public services (who is liable when systems fail?).',
				'Lead whole-school literacy: vocabulary, habits, and subject-specific responsible use.',
			],
			contentFocus:
				'Extended scenarios in politics, EPQ-style writing, business case studies, and cross-curricular leadership. Students are expected to argue, qualify claims, and connect to society — mirroring A level and BTEC depth without naming a board.',
			themes: ['Ethics & automated decisions', 'Academic reliability', 'Deployment & accountability', 'Whole-school leadership'],
			insights: [
				'Policy writing and ethical frameworks are as important as technical vocabulary.',
				'Confidence tone in AI text is a reliability hazard in extended writing.',
				'Sixth-form students model digital norms for the whole school.',
			],
			questions: [
				{
					topic: 'Ethics & automated decisions',
					subject: 'Politics / Sociology',
					stem: 'A council uses an AI system to prioritise housing applications. What is the most important public concern?',
					options: [
						'The logo design',
						'Fairness, transparency, and bias in automated decisions',
						'How fast emails send',
						'The colour of the forms',
					],
					answer: 1,
					markScheme:
						'Automated decision-making ethics — accountability, bias, appeal processes, and impact on vulnerable groups.',
					discuss: 'Should humans always be able to explain why a decision was made?',
					facilitator:
						'Run a short debate: “Benefits outweigh risks” — assign roles (resident, councillor, developer).',
					team:
						'Teams list three questions journalists should ask about any “AI-powered” public service.',
				},
				{
					topic: 'Academic reliability',
					subject: 'English / EPQ',
					stem: 'An AI draft uses phrases like “It is widely agreed that…” without evidence. Why is that risky at A level?',
					options: [
						'It makes essays shorter',
						'It mimics authority without argument — weakens critical evaluation',
						'Examiners prefer bullet points',
						'Quotes are not allowed',
					],
					answer: 1,
					markScheme:
						'Reliability and academic style — students must supply evidence, nuance, and their own line of argument.',
					discuss: 'How is this similar to weak Wikipedia phrasing before editing?',
					facilitator:
						'Highlight three “AI filler” phrases on screen; students rewrite one paragraph with named evidence.',
					team:
						'Teams compete to redraft one paragraph with one statistic and one hedged claim (“some researchers argue…”).',
				},
				{
					topic: 'Deployment & accountability',
					subject: 'Business / Economics',
					stem: 'A company deploys chatbots for customer refunds with minimal human review. Which curriculum theme does this raise first?',
					options: [
						'Keyboard ergonomics',
						'Risk, accountability, and customer harm if the bot errs',
						'Social media marketing',
						'Office furniture costs',
					],
					answer: 1,
					markScheme:
						'Ethics and reliability in deployment — who is responsible when automation causes financial or legal harm?',
					discuss: 'Where should “human in the loop” be mandatory vs optional in services students use daily?',
					facilitator:
						'Case study: one real headline about AI customer service failure (you supply or students find).',
					team:
						'Teams design a one-page “deployment checklist” with five questions before going live with AI.',
				},
				{
					topic: 'Whole-school leadership',
					subject: 'Cross-curricular',
					stem: 'Your sixth form leads a whole-school AI awareness day. What outcome best matches modern curriculum intent?',
					options: [
						'Every student memorises model names',
						'Shared vocabulary, critical habits, and subject-specific examples of responsible use',
						'All homework becomes AI-generated',
						'Computing teachers teach every lesson',
					],
					answer: 1,
					markScheme:
						'Whole-school literacy — ethics, reliability, bias, and classroom practice woven through subjects, not a one-off assembly only.',
					discuss: 'What one habit should every department adopt by September?',
					facilitator:
						'Close with departments writing one sentence for their scheme of work.',
					team:
						'Faculty teams have 5 minutes to agree one “AI awareness outcome” for their subject on a sticky note wall.',
				},
			],
		},
	};

	var LEVEL_ORDER = ['ks3', 'ks4', 'ks5'];

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
		var tabs = root.querySelector('[data-cq-tabs]');
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
			qIndex: 0,
			selectedChoice: null,
			facilitator: false,
			team: false,
			answered: {},
			visited: {},
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
			renderHub();
		}

		function showLevelTaste(levelKey) {
			state.screen = 'taste';
			state.levelKey = levelKey;
			state.qIndex = 0;
			state.selectedChoice = null;
			hub.hidden = true;
			main.hidden = false;
			renderTabs();
			renderLevelTaste();
		}

		function showQuestion(qIndex) {
			state.screen = 'question';
			state.qIndex = qIndex;
			state.selectedChoice = null;
			var rec = answerRecord();
			if (rec && rec.mode === 'tried' && rec.choice >= 0) {
				state.selectedChoice = rec.choice;
			}
			state.visited[qKey()] = true;
			renderTabs();
			renderQuestion();
		}

		function renderHub() {
			var html =
				'<div class="cq-hub-card">' +
				'<p class="cq-eyebrow">Cross-curricular · AI Awareness Day</p>' +
				'<h2 class="cq-title">Taste the curriculum at KS3, KS4 &amp; KS5</h2>' +
				'<p class="cq-lead">See how <strong>content</strong> and <strong>objectives</strong> shift by key stage — then <strong>have a go</strong> at sample exam-style questions or <strong>reveal answers</strong> with mark schemes, discussion prompts, and optional facilitator notes. No specific exam board.</p>' +
				'<div class="cq-compare">';
			LEVEL_ORDER.forEach(function (key) {
				var lv = LEVELS[key];
				html +=
					'<div class="cq-compare-card cq-compare-card--' +
					key +
					'">' +
					'<h3 class="cq-compare-card__title">' +
					escapeHtml(lv.label) +
					' <span class="cq-compare-card__ages">' +
					escapeHtml(lv.ages) +
					'</span></h3>' +
					'<p class="cq-compare-card__tag">' +
					escapeHtml(lv.tagline) +
					'</p>' +
					'<p class="cq-compare-card__obj"><strong>Sample objective:</strong> ' +
					escapeHtml(lv.objectives[0]) +
					'</p>' +
					'<div class="cq-theme-row">';
				lv.themes.forEach(function (t) {
					html += '<span class="cq-theme-pill">' + escapeHtml(t) + '</span>';
				});
				html +=
					'</div>' +
					'<button type="button" class="cq-btn cq-btn--primary cq-btn--block" data-cq-pick="' +
					key +
					'">Explore ' +
					escapeHtml(lv.label) +
					'</button></div>';
			});
			html +=
				'</div>' +
				'<p class="cq-hub-note">Choose a key stage to read its objectives and content focus, then try questions or reveal model answers — your choice on each item.</p></div>';
			hub.innerHTML = html;
		}

		function renderTabs() {
			if (!tabs) {
				return;
			}
			var html = '';
			LEVEL_ORDER.forEach(function (key) {
				var lv = LEVELS[key];
				var cls = 'cq-tab' + (key === state.levelKey ? ' is-active' : '');
				if (state.done[key]) {
					cls += ' is-done';
				}
				html +=
					'<button type="button" class="' +
					cls +
					'" data-cq-tab="' +
					key +
					'">' +
					escapeHtml(lv.label) +
					'</button>';
			});
			tabs.innerHTML = html;
		}

		function renderProgress() {
			if (!progress || state.screen !== 'question') {
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

		function renderLevelTaste() {
			var lv = level();
			var html =
				'<div class="cq-taste cq-taste--' +
				state.levelKey +
				'">' +
				'<p class="cq-eyebrow">' +
				escapeHtml(lv.label) +
				' · ' +
				escapeHtml(lv.ages) +
				'</p>' +
				'<h3 class="cq-taste__title">' +
				escapeHtml(lv.tagline) +
				'</h3>' +
				'<p class="cq-taste__intro">' +
				escapeHtml(lv.intro) +
				'</p>' +
				'<div class="cq-taste-grid">' +
				'<div class="cq-taste-block">' +
				'<h4>Curriculum objectives (what students are building towards)</h4>' +
				'<ul class="cq-objectives">';
			lv.objectives.forEach(function (obj) {
				html += '<li>' + escapeHtml(obj) + '</li>';
			});
			html +=
				'</ul></div>' +
				'<div class="cq-taste-block">' +
				'<h4>Content at this key stage</h4>' +
				'<p>' +
				escapeHtml(lv.contentFocus) +
				'</p>' +
				'<h4 class="cq-taste__themes-h">Themes you will sample</h4>' +
				'<div class="cq-theme-row">';
			lv.themes.forEach(function (t) {
				html += '<span class="cq-theme-pill">' + escapeHtml(t) + '</span>';
			});
			html +=
				'</div></div></div>' +
				'<p class="cq-taste__pick">Or jump to a sample question:</p>' +
				'<div class="cq-q-pick">';
			lv.questions.forEach(function (q, i) {
				var done = state.answered[state.levelKey + '-' + i];
				html +=
					'<button type="button" class="cq-q-pick-btn' +
					(done ? ' is-done' : '') +
					'" data-cq-goto="' +
					i +
					'"><span class="cq-q-pick-btn__n">Q' +
					(i + 1) +
					'</span> ' +
					escapeHtml(q.topic) +
					'</button>';
			});
			html +=
				'</div>' +
				'<div class="cq-actions">' +
				'<button type="button" class="cq-btn cq-btn--primary" data-cq-start-q>Start with question 1</button>' +
				'<button type="button" class="cq-btn" data-cq-back-hub>Back to all key stages</button>' +
				'</div></div>';
			panel.innerHTML = html;
			if (progress) {
				progress.innerHTML = '';
			}
		}

		function renderFeedbackBlocks(q) {
			var html =
				'<div class="cq-feedback">' +
				'<div class="cq-block cq-block--answer"><h4>Model answer</h4><p><strong>' +
				escapeHtml(q.options[q.answer]) +
				'</strong></p></div>' +
				'<div class="cq-block cq-block--mark"><h4>Mark scheme guidance</h4><p>' +
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

			var html =
				'<article class="cq-question">' +
				'<p class="cq-q-meta"><span class="cq-topic">' +
				escapeHtml(q.topic) +
				'</span> · <span class="cq-subject">' +
				escapeHtml(q.subject) +
				'</span> · Question ' +
				(state.qIndex + 1) +
				' of ' +
				lv.questions.length +
				'</p>' +
				'<p class="cq-stem">' +
				escapeHtml(q.stem) +
				'</p>' +
				'<p class="cq-try-hint">Select an option to <strong>check your answer</strong>, or use <strong>reveal answer &amp; notes</strong> to skip straight to the model response.</p>' +
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
					'<button type="button" class="cq-btn" data-cq-back-taste>← ' +
					escapeHtml(lv.label) +
					' overview</button>' +
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
			state.done[state.levelKey] = true;
			renderTabs();
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
			if (state.screen === 'taste') {
				renderLevelTaste();
				return;
			}
			if (state.screen === 'question') {
				renderQuestion();
				return;
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
				showLevelTaste(pick.getAttribute('data-cq-pick'));
			}
		});

		root.addEventListener('click', function (e) {
			var tab = e.target.closest('[data-cq-tab]');
			if (tab) {
				showLevelTaste(tab.getAttribute('data-cq-tab'));
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
			if (e.target.closest('[data-cq-back-taste]')) {
				state.screen = 'taste';
				renderLevelTaste();
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
				showLevelTaste(nl.getAttribute('data-cq-next-level'));
				return;
			}
			if (e.target.closest('[data-cq-all-done]')) {
				state.screen = 'all-done';
				LEVEL_ORDER.forEach(function (k) {
					state.done[k] = true;
				});
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
				if (state.screen === 'question' && isRevealed()) {
					renderQuestion();
				}
			});
		}
		if (teamToggle) {
			teamToggle.addEventListener('change', function () {
				state.team = teamToggle.checked;
				if (state.screen === 'question' && isRevealed()) {
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
