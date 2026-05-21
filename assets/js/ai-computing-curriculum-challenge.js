/**
 * Computing Curriculum Challenge KS1–KS5
 * Shortcode: [aiad_computing_curriculum]
 */
(function () {
	'use strict';

	var STAGES = [
		{
			ks: 'KS1',
			ksColor: '#1D9E75',
			ksBg: '#E1F5EE',
			ksText: '#085041',
			title: 'KS1 \u2014 algorithms as instructions',
			years: 'Years 1 & 2 \u00b7 Ages 5\u20137',
			context:
				'At KS1, children learn that an algorithm is a set of step-by-step instructions. They use Bee-Bots on floor mats and follow simple sequences. This is what your youngest computing learners experience.',
			type: 'mcq',
			questions: [
				{
					q: 'The Bee-Bot below starts at the star (\u2b50), facing right. It follows: Forward, Forward, Turn Left, Forward. Which square does it land on?',
					opts: ['Square C2', 'Square C3', 'Square B3', 'Square A3'],
					ans: 0,
					fact: 'Bee-Bots follow precise sequential instructions \u2014 this is exactly what an algorithm is. Order matters: change one step and the robot ends up somewhere completely different.',
					illus: 'beebot',
				},
				{
					q: 'Which of these is the best example of an algorithm?',
					opts: [
						'A list of ingredients',
						'Step-by-step instructions for making a sandwich',
						'A picture of a sandwich',
						'The word "sandwich"',
					],
					ans: 1,
					fact: 'An algorithm is a precise, ordered set of steps. A recipe is a classic real-world algorithm \u2014 the foundation of all computing and AI logic.',
					illus: 'recipe',
				},
				{
					q: 'A child writes: 1) Pick up cup 2) Pour water 3) Drink. The robot pours water before picking up the cup. What is the problem?',
					opts: [
						'The instructions are too long',
						'The instructions are in the wrong order',
						'The cup is missing',
						'The robot is broken',
					],
					ans: 1,
					fact: 'Sequencing errors are called bugs. Spotting and fixing them is debugging \u2014 a key KS1 skill that scales all the way to AI systems.',
					illus: 'debug-order',
				},
			],
		},
		{
			ks: 'KS2',
			ksColor: '#534AB7',
			ksBg: '#EEEDFE',
			ksText: '#3C3489',
			title: 'KS2 \u2014 sequences, loops & debugging',
			years: 'Years 3\u20136 \u00b7 Ages 7\u201311',
			context:
				'At KS2, children use Scratch to write programs with loops, selection and variables. They learn to design, write and debug programs. This is the computing your primary-age students are doing.',
			type: 'mcq',
			questions: [
				{
					q: 'The Scratch blocks below run when the green flag is clicked. How many times does the sprite say \u201cHello\u201d?',
					opts: ['1 time', '5 times', '10 times', 'It never says Hello'],
					ans: 2,
					fact: 'Loops (iteration) repeat a block of instructions a set number of times. KS2 children build intuition for iteration through visual block-based coding.',
					illus: 'scratch',
				},
				{
					q: "A Year 5 pupil's Scratch game has a bug: the sprite moves right when the LEFT arrow is pressed. What type of error is this?",
					opts: ['Syntax error', 'Logic error', 'Hardware error', 'Network error'],
					ans: 1,
					fact: 'A logic error means the program runs but produces the wrong result. Syntax errors stop the program running entirely. Children learn to distinguish these.',
					illus: 'logic-bug',
				},
				{
					q: 'Which Scratch block makes a sprite do something different depending on whether it touches a red wall?',
					opts: ['Repeat until', 'If/then', 'Set variable', 'Move 10 steps'],
					ans: 1,
					fact: 'If/then blocks are selection \u2014 the program makes a decision. This is how AI decision trees work too, at a much larger scale.',
					illus: 'if-then',
				},
				{
					q: 'A KS2 pupil wants to count how many times a player scores. Which programming concept do they need?',
					opts: ['A loop', 'A variable', 'A procedure', 'A network'],
					ans: 1,
					fact: 'Variables store data that can change. This concept links directly to how AI systems store and update values during learning.',
					illus: 'variable',
				},
			],
		},
		{
			ks: 'KS3',
			ksColor: '#BA7517',
			ksBg: '#FAEEDA',
			ksText: '#633806',
			title: 'KS3 \u2014 sorting & searching algorithms',
			years: 'Years 7\u20139 \u00b7 Ages 11\u201314',
			context:
				'At KS3, pupils study specific named algorithms \u2014 how computers sort lists and search for data. These underpin everything from Google Search to how AI ranks results.',
			type: 'mcq',
			questions: [
				{
					q: 'The step-by-step diagram below shows bubble sort on [5,\u00a03,\u00a01,\u00a04,\u00a02]. Watch each pass. How many passes are needed to fully sort the list?',
					opts: ['1 pass', '2 passes', '4 passes', 'It depends on the processor'],
					ans: 2,
					fact: 'Bubble sort needs up to n\u22121 passes for n items. It is simple but inefficient \u2014 which is why studying algorithm efficiency matters at KS3.',
					illus: 'bubblesort',
				},
				{
					q: 'Binary search requires the list to be sorted first. Why?',
					opts: [
						'It is faster on unsorted data',
						'It works by halving the search space each time, which only works on sorted data',
						'It only works on numbers',
						'Sorted lists use less memory',
					],
					ans: 1,
					fact: 'Binary search eliminates half the remaining items each step \u2014 O(log n) efficiency. AI search systems use similar divide-and-conquer principles.',
					illus: 'binary',
				},
				{
					q: 'Which algorithm does a teacher use when marking a register by going through every name one at a time?',
					opts: ['Binary search', 'Merge sort', 'Linear search', 'Bubble sort'],
					ans: 2,
					fact: 'Linear search (sequential search) checks each item in order. It works on any list but is slower than binary search on large sorted lists.',
					illus: 'linear',
				},
				{
					q: 'A sorting algorithm that splits a list in half, sorts each half, then merges them back is called:',
					opts: ['Bubble sort', 'Insertion sort', 'Merge sort', 'Quick sort'],
					ans: 2,
					fact: 'Merge sort is a divide-and-conquer algorithm \u2014 the same principle AI uses to process large training datasets efficiently.',
					illus: 'merge',
				},
			],
		},
		{
			ks: 'KS4',
			ksColor: '#D85A30',
			ksBg: '#FAECE7',
			ksText: '#712B13',
			title: 'KS4 \u2014 ethics, AI & digital impacts',
			years: 'Years 10\u201311 \u00b7 Ages 14\u201316 \u00b7 AQA GCSE spec 3.8',
			context:
				'GCSE Computer Science requires students to evaluate the ethical, legal and environmental impacts of digital technology \u2014 including AI. Questions 1\u20132 are matching; question 3 is a 6-mark extended response with a self-mark scheme.',
			type: 'mixed',
			questions: [
				{
					type: 'match',
					q: 'Match each ethical or legal issue to its correct definition.',
					pairs: [
						{
							term: 'Data privacy',
							def: 'The right of individuals to control how their personal information is collected and used',
						},
						{
							term: 'Intellectual property',
							def: 'Legal rights protecting creative works such as software, music and written content',
						},
						{
							term: 'AI bias',
							def: 'When an AI system produces unfair outcomes because its training data reflected existing inequalities',
						},
						{
							term: 'Digital footprint',
							def: "The trail of data left behind by a person's online activity",
						},
					],
				},
				{
					type: 'match',
					q: 'Match each law or principle to what it protects.',
					pairs: [
						{ term: 'UK GDPR', def: 'How organisations must collect, store and use personal data' },
						{
							term: 'Computer Misuse Act',
							def: 'Makes unauthorised access to computer systems a criminal offence',
						},
						{
							term: 'Creative Commons',
							def: 'A licensing system allowing creators to share work with defined permissions',
						},
						{
							term: 'Environmental impact',
							def: 'The energy consumption and e-waste produced by data centres and devices',
						},
					],
				},
				{
					type: 'extended',
					marks: 6,
					q: 'Discuss the ethical implications of using AI to make decisions in criminal sentencing. In your answer, consider bias, transparency and human oversight.',
					markscheme: [
						'AI systems trained on historical data may reflect existing racial or socioeconomic biases in the justice system (1 mark)',
						'This could lead to unfair sentencing outcomes that disadvantage certain groups (1 mark)',
						'AI decision-making in high-stakes situations lacks transparency \u2014 defendants may not understand why a decision was made (1 mark)',
						'The "black box" nature of many AI models makes it difficult to challenge decisions legally (1 mark)',
						'Human oversight (human in the loop) is essential to ensure accountability and the right to appeal (1 mark)',
						'There is a broader question of whether life-affecting decisions should ever be fully delegated to AI systems (1 mark)',
					],
				},
			],
		},
		{
			ks: 'KS5',
			ksColor: '#185FA5',
			ksBg: '#E6F1FB',
			ksText: '#0C447C',
			title: 'KS5 \u2014 data structures',
			years: 'Years 12\u201313 \u00b7 Ages 16\u201318 \u00b7 AQA A-level spec 3.2',
			context:
				'A-level Computer Science requires deep understanding of data structures \u2014 how data is organised and accessed efficiently. These structures underpin databases, operating systems and AI. Attempt one, two or all three 12-mark questions below.',
			type: 'ks5',
			questions: [
				{
					type: 'extended',
					label: 'Question A',
					marks: 12,
					q: 'Evaluate the use of different data structures in an AI music recommendation system. Discuss at least three data structures, their advantages, limitations and suitability.',
					markscheme: [
						'Arrays/lists store user preference data \u2014 fast indexed access but fixed size may limit scalability (2 marks)',
						'Hash tables map user IDs to preference profiles \u2014 O(1) average lookup time makes them ideal for real-time recommendations (2 marks)',
						'Graphs model relationships between users and content \u2014 edges represent similarity or interaction history (2 marks)',
						'Trees (decision trees or BSTs) used in the recommendation algorithm to classify user preferences (1 mark)',
						'Queues manage recommendation pipelines and batch processing of user data (1 mark)',
						'Trade-offs: hash tables fast but unordered; trees ordered but slower insertion; graphs powerful but memory-intensive (2 marks)',
						'Conclusion: combination of hash tables for lookup and graphs for relationship modelling most effective at scale (2 marks)',
					],
				},
				{
					type: 'extended',
					label: 'Question B',
					marks: 12,
					q: 'A hospital uses a stack and a queue to manage patient records and appointment scheduling. Evaluate the suitability of each structure and discuss the implications of choosing the wrong one.',
					markscheme: [
						'A stack operates LIFO \u2014 the last record added is the first retrieved; suitable for undo operations and backtracking in diagnostic systems (2 marks)',
						'A queue operates FIFO \u2014 patients are seen in order of arrival; suitable for appointment scheduling and fair resource allocation (2 marks)',
						'Using a stack for appointments would mean the most recently booked patient is seen first \u2014 ethically problematic and clinically dangerous (2 marks)',
						'Using a queue for undo operations would undo the oldest change first rather than the most recent \u2014 logically incorrect (2 marks)',
						'Memory considerations: both are linear structures; linked-list implementations offer dynamic resizing vs array-based fixed capacity (2 marks)',
						'Conclusion: queues are appropriate for patient flow; stacks for transaction logs or reversible operations within the system (2 marks)',
					],
				},
				{
					type: 'extended',
					label: 'Question C',
					marks: 12,
					q: 'Discuss how binary trees and hash tables are used in the context of a search engine index. Evaluate the trade-offs between the two structures for this application.',
					markscheme: [
						'A hash table maps search terms to document lists \u2014 O(1) average lookup makes it ideal for exact keyword matching at scale (2 marks)',
						'A binary search tree stores keys in sorted order \u2014 enables efficient range queries (e.g. all terms between A and M) unlike hash tables (2 marks)',
						'Hash tables suffer from collisions which degrade performance to O(n) in the worst case \u2014 collision handling adds complexity (2 marks)',
						'BSTs can become unbalanced \u2014 a balanced BST (AVL or red-black tree) guarantees O(log\u00a0n) but adds implementation overhead (2 marks)',
						'For a search engine index, hash tables suit exact lookups; BSTs suit autocomplete and prefix or range searching (2 marks)',
						'Conclusion: real-world search engines combine both \u2014 hash tables for fast term lookup, balanced trees for sorted and range operations (2 marks)',
					],
				},
			],
		},
	];

	var SS = STAGES.map(function () {
		return { qi: 0, answered: false, score: 0, done: false, ticks: {} };
	});

	function ccQuestionIllustration(si, qi) {
		var st = STAGES[si];
		var q = st.questions[qi];
		var key = q.illus || q.img || '';
		if (key && typeof window.aiadCcRenderRichIllustration === 'function') {
			var rich = window.aiadCcRenderRichIllustration(key);
			if (rich) {
				return rich;
			}
		}
		if (typeof window.aiadCcRenderIllustration !== 'function') {
			return '';
		}
		return window.aiadCcRenderIllustration(key || st.ks.toLowerCase() + '-' + qi, st.ksColor);
	}

	function ccAfterRender(root) {
		if (typeof window.aiadCcInitMermaid === 'function') {
			window.aiadCcInitMermaid(root || document.getElementById('aiad-cc'));
		}
	}

	function ccOptId(si, qi, oi) {
		return 'aiad-opt-' + si + '-' + qi + '-' + oi;
	}

	function ccFbId(si, qi) {
		return 'aiad-fb-' + si + '-' + qi;
	}

	function ccMcqNavId(si) {
		return 'aiad-mcq-nav-' + si;
	}

	function doneCount() {
		var c = 0;
		SS.forEach(function (s) {
			if (s.done) {
				c++;
			}
		});
		return c;
	}

	function tot() {
		var t = 0;
		SS.forEach(function (s) {
			t += s.score;
		});
		return t;
	}

	function renderTabs() {
		var el = document.getElementById('aiad-tabs');
		if (!el) {
			return;
		}
		var h = '';
		STAGES.forEach(function (st, si) {
			var screen = document.getElementById('aiad-s' + si);
			var isActive = screen && screen.classList.contains('active');
			var bg = isActive
				? 'background:' + st.ksColor + ';border-color:' + st.ksColor + ';color:#fff;'
				: '';
			h +=
				'<button type="button" class="cc-ks-tab' +
				(isActive ? ' active' : '') +
				'" style="' +
				bg +
				'" onclick="aiadJump(' +
				si +
				')">';
			h += st.ks;
			if (SS[si].done) {
				h += '<span class="cc-tick" aria-hidden="true">&#10003;</span>';
			}
			h += '</button>';
		});
		var summary = document.getElementById('aiad-summary');
		var sumActive = summary && summary.classList.contains('active');
		h +=
			'<button type="button" class="cc-ks-tab' +
			(sumActive ? ' active' : '') +
			'" style="' +
			(sumActive ? 'background:#1a1a2e;border-color:#1a1a2e;color:#fff;' : '') +
			'" onclick="aiadJump(\'summary\')">Summary</button>';
		el.innerHTML = h;
	}

	function showScreen(id) {
		document.querySelectorAll('#aiad-cc .cc-screen').forEach(function (s) {
			s.classList.remove('active');
		});
		var el = document.getElementById('aiad-' + id);
		if (el) {
			el.classList.add('active');
		}
		renderTabs();
	}

	window.aiadJump = function (si) {
		if (si === 'summary') {
			renderSummary();
			showScreen('summary');
			return;
		}
		showScreen('s' + si);
		if (SS[si].done) {
			renderDone(si);
		} else if (STAGES[si].type === 'mcq') {
			renderMCQ(si);
		} else if (STAGES[si].type === 'ks5') {
			renderKS5(si);
		} else {
			renderMixed(si);
		}
	};


	function buildScreens() {
		var wrap = document.getElementById('aiad-screens');
		if (!wrap) {
			return;
		}
		var h = '';
		STAGES.forEach(function (st, si) {
			h += '<div class="cc-screen" id="aiad-s' + si + '">';
			h +=
				'<span class="cc-ks-badge" style="background:' +
				st.ksBg +
				';color:' +
				st.ksText +
				'">' +
				st.ks +
				' \u00b7 ' +
				st.years +
				'</span>';
			h += '<p class="cc-stage-title">' + st.title + '</p>';
			h += '<p class="cc-stage-context">' + st.context + '</p>';
			h += '<div id="aiad-s' + si + '-body"></div>';
			h += '</div>';
		});
		h += '<div class="cc-screen" id="aiad-summary"></div>';
		wrap.innerHTML = h;
	}

	function renderMCQ(si) {
		var ss = SS[si];
		var st = STAGES[si];
		var qi = ss.qi;
		var q = st.questions[qi];
		var total = st.questions.length;
		var h = '<div class="cc-q-card">';
		h += ccQuestionIllustration(si, qi);
		h += '<p class="cc-q-num">Question ' + (qi + 1) + ' of ' + total + '</p>';
		h += '<p class="cc-q-text">' + q.q + '</p>';
		h += '<div class="cc-opts">';
		q.opts.forEach(function (o, oi) {
			h +=
				'<button type="button" class="cc-opt" id="' +
				ccOptId(si, qi, oi) +
				'" onclick="aiadPick(' +
				si +
				',' +
				qi +
				',' +
				oi +
				')">' +
				o +
				'</button>';
		});
		h += '</div><p class="cc-feedback" id="' + ccFbId(si, qi) + '"></p></div>';
		h +=
			'<div class="cc-nav-row" id="' +
			ccMcqNavId(si) +
			'"><span class="cc-score-pill">' +
			st.ks +
			' score: ' +
			ss.score +
			' pts</span></div>';
		var body = document.getElementById('aiad-s' + si + '-body');
		body.innerHTML = h;
		ccAfterRender(body);
	}

	window.aiadPick = function (si, qi, oi) {
		var ss = SS[si];
		if (ss.answered) {
			return;
		}
		var body = document.getElementById('aiad-s' + si + '-body');
		if (!body) {
			return;
		}
		ss.answered = true;
		var q = STAGES[si].questions[qi];
		body.querySelectorAll('.cc-opt').forEach(function (b) {
			b.disabled = true;
		});
		var picked = document.getElementById(ccOptId(si, qi, oi));
		var correctBtn = document.getElementById(ccOptId(si, qi, q.ans));
		var fb = document.getElementById(ccFbId(si, qi));
		if (oi === q.ans) {
			if (picked) {
				picked.classList.add('correct');
			}
			if (fb) {
				fb.textContent = '\u2713 Correct! ' + q.fact;
			}
			ss.score += 10;
		} else {
			if (picked) {
				picked.classList.add('wrong');
			}
			if (correctBtn) {
				correctBtn.classList.add('reveal');
			}
			if (fb) {
				fb.textContent = '\u2717 Not quite. ' + q.opts[q.ans] + '. ' + q.fact;
			}
		}
		var isLast = qi === STAGES[si].questions.length - 1;
		var scoreHtml =
			'<span class="cc-score-pill">' + STAGES[si].ks + ' score: ' + ss.score + ' pts</span>';
		var nav = document.getElementById(ccMcqNavId(si));
		if (!nav) {
			return;
		}
		if (!isLast) {
			nav.innerHTML =
				'<button type="button" class="btn-primary" onclick="aiadNextQ(' +
				si +
				')">Next question &rarr;</button>' +
				scoreHtml;
		} else {
			ss.done = true;
			renderTabs();
			nav.innerHTML =
				'<button type="button" class="btn-primary" onclick="aiadJump(\'summary\')">View my summary</button><button type="button" onclick="aiadJump(' +
				((si + 1) % STAGES.length) +
				')">Try another stage</button>' +
				scoreHtml;
		}
	};

	window.aiadNextQ = function (si) {
		SS[si].qi++;
		SS[si].answered = false;
		renderMCQ(si);
	};

	function renderMixed(si) {
		var h = '';
		STAGES[si].questions.forEach(function (q, qi) {
			if (q.type === 'match') {
				h += buildMatchQ(si, qi, q);
			} else if (q.type === 'extended') {
				h += buildExtQ(si, qi, q);
			}
		});
		h +=
			'<div class="cc-nav-row"><button type="button" class="btn-primary" onclick="aiadFinish(' +
			si +
			')">Mark as complete &#10003;</button><span class="cc-score-pill">' +
			STAGES[si].ks +
			' score: ' +
			SS[si].score +
			' pts</span></div>';
		var body = document.getElementById('aiad-s' + si + '-body');
		body.innerHTML = h;
		ccAfterRender(body);
	}

	window.aiadFinish = function (si) {
		SS[si].done = true;
		renderTabs();
		renderDone(si);
	};

	function renderDone(si) {
		var st = STAGES[si];
		var ss = SS[si];
		var h =
			'<div class="cc-done-banner" style="background:' +
			st.ksBg +
			';color:' +
			st.ksText +
			'">';
		h += '&#10003; ' + st.ks + ' complete \u2014 ' + ss.score + ' points scored</div>';
		h +=
			'<div class="cc-nav-row"><button type="button" onclick="aiadRetry(' +
			si +
			')">Retry ' +
			st.ks +
			'</button><button type="button" class="btn-primary" onclick="aiadJump(\'summary\')">View summary</button></div>';
		document.getElementById('aiad-s' + si + '-body').innerHTML = h;
	}

	window.aiadRetry = function (si) {
		SS[si] = { qi: 0, answered: false, score: 0, done: false, ticks: {} };
		renderTabs();
		if (STAGES[si].type === 'mcq') {
			renderMCQ(si);
		} else if (STAGES[si].type === 'ks5') {
			renderKS5(si);
		} else {
			renderMixed(si);
		}
	};

	function renderKS5(si) {
		var h = '';
		if (typeof window.aiadCcRenderRichIllustration === 'function') {
			h += window.aiadCcRenderRichIllustration('ds');
		}
		STAGES[si].questions.forEach(function (q, qi) {
			h += buildExtQ(si, qi, q, true);
		});
		h +=
			'<div class="cc-nav-row"><button type="button" class="btn-primary" onclick="aiadFinish(' +
			si +
			')">Mark as complete &#10003;</button><span class="cc-score-pill">KS5 score: ' +
			SS[si].score +
			' pts</span></div>';
		var body = document.getElementById('aiad-s' + si + '-body');
		body.innerHTML = h;
		ccAfterRender(body);
	}

	function buildMatchQ(si, qi, q) {
		var shuffled = q.pairs
			.map(function (p) {
				return p.def;
			})
			.sort(function () {
				return Math.random() - 0.5;
			});
		var h = '<div class="cc-q-card" id="aiad-mc-' + si + '-' + qi + '">';
		h += ccQuestionIllustration(si, qi);
		h += '<p class="cc-q-num">Matching question</p>';
		h += '<p class="cc-q-text">' + q.q + '</p>';
		h += '<div class="cc-match-wrap">';
		q.pairs.forEach(function (p, pi) {
			h += '<div class="cc-match-row" id="aiad-mr-' + si + '-' + qi + '-' + pi + '">';
			h += '<div class="cc-match-term">' + p.term + '</div>';
			h +=
				'<div class="cc-match-sel"><select id="aiad-ms-' +
				si +
				'-' +
				qi +
				'-' +
				pi +
				'" aria-label="Match for ' +
				p.term +
				'">';
			h += '<option value="">\u2014 select \u2014</option>';
			shuffled.forEach(function (d) {
				h += '<option value="' + d.replace(/"/g, '&quot;') + '">' + d + '</option>';
			});
			h += '</select></div></div>';
		});
		h += '</div>';
		h +=
			'<button type="button" onclick="aiadCheckMatch(' +
			si +
			',' +
			qi +
			')" style="margin-bottom:8px">Check answers</button>';
		h += '<p class="cc-feedback" id="aiad-mfb-' + si + '-' + qi + '"></p></div>';
		return h;
	}

	window.aiadCheckMatch = function (si, qi) {
		var q = STAGES[si].questions[qi];
		var correct = 0;
		q.pairs.forEach(function (p, pi) {
			var sel = document.getElementById('aiad-ms-' + si + '-' + qi + '-' + pi);
			var row = document.getElementById('aiad-mr-' + si + '-' + qi + '-' + pi);
			row.classList.remove('correct', 'wrong');
			if (sel && sel.value === p.def) {
				row.classList.add('correct');
				correct++;
			} else {
				row.classList.add('wrong');
			}
			if (sel) {
				sel.disabled = true;
			}
		});
		var pts = correct * 5;
		SS[si].score += pts;
		document.getElementById('aiad-mfb-' + si + '-' + qi).textContent =
			correct +
			' of ' +
			q.pairs.length +
			' correct (+' +
			pts +
			' points). ' +
			(correct < q.pairs.length
				? 'Correct answers highlighted in green.'
				: 'Well done!');
	};

	function buildExtQ(si, qi, q, skipIllus) {
		var h = '<div class="cc-q-card">';
		if (!skipIllus) {
			h += ccQuestionIllustration(si, qi);
		}
		var label = q.label ? q.label + ' \u2014 ' : '';
		h += '<p class="cc-q-num">' + label + 'Extended response \u00b7 ' + q.marks + ' marks</p>';
		h += '<p class="cc-ext-q">' + q.q + '</p>';
		h +=
			'<textarea class="cc-ext-textarea" id="aiad-ext-' +
			si +
			'-' +
			qi +
			'" placeholder="Write your answer here, then reveal the mark scheme to self-assess..."></textarea>';
		h +=
			'<div class="cc-nav-row" style="margin-top:8px"><button type="button" onclick="aiadReveal(' +
			si +
			',' +
			qi +
			')">' +
			(q.modelAnswer ? 'Reveal model answer &amp; mark scheme' : 'Reveal mark scheme') +
			'</button></div>';
		h += '<div id="aiad-msp-' + si + '-' + qi + '" style="display:none">';
		if (q.modelAnswer) {
			h += '<div class="cc-model-answer">';
			h += '<p class="cc-model-answer-title">Model answer <span class="cc-model-answer-band">(AQA-style, high band)</span></p>';
			h += '<p class="cc-model-answer-text">' + q.modelAnswer + '</p>';
			h += '</div>';
		}
		h += '<div class="cc-mark-scheme">';
		h += '<p class="cc-mark-scheme-title">Mark scheme \u2014 tick each point you covered:</p>';
		var scheme = q.markscheme || q.ms || [];
		scheme.forEach(function (pt, pti) {
			h +=
				'<div class="cc-mark-item" onclick="aiadTick(' +
				si +
				',' +
				qi +
				',' +
				pti +
				',' +
				q.marks +
				')">';
			h += '<div class="cc-mark-cb" id="aiad-cb-' + si + '-' + qi + '-' + pti + '">&#10003;</div>';
			h += '<span class="cc-mark-text">' + pt + '</span></div>';
		});
		h +=
			'<p class="cc-mark-score" id="aiad-msc-' +
			si +
			'-' +
			qi +
			'">0 / ' +
			q.marks +
			' marks self-assessed</p>';
		h += '</div></div></div>';
		return h;
	}

	window.aiadReveal = function (si, qi) {
		var p = document.getElementById('aiad-msp-' + si + '-' + qi);
		if (p) {
			p.style.display = 'block';
		}
	};

	window.aiadTick = function (si, qi, pi, maxM) {
		var ss = SS[si];
		var key = qi + '_' + pi;
		var cb = document.getElementById('aiad-cb-' + si + '-' + qi + '-' + pi);
		if (ss.ticks[key]) {
			delete ss.ticks[key];
			if (cb) {
				cb.classList.remove('ticked');
				cb.style.color = '';
				cb.style.background = '';
				cb.style.borderColor = '';
			}
		} else {
			ss.ticks[key] = true;
			if (cb) {
				cb.classList.add('ticked');
				cb.style.background = '#1D9E75';
				cb.style.borderColor = '#1D9E75';
				cb.style.color = '#fff';
			}
		}
		var count = Object.keys(ss.ticks).filter(function (k) {
			return k.indexOf(qi + '_') === 0 && ss.ticks[k];
		}).length;
		var earned = Math.min(count, maxM);
		var sc = document.getElementById('aiad-msc-' + si + '-' + qi);
		if (sc) {
			sc.textContent = earned + ' / ' + maxM + ' marks self-assessed';
		}
		ss.score = Math.max(ss.score, earned * 2);
	};

	function ksLevel(si) {
		var ss = SS[si];
		var max = si < 3 ? 30 : si === 3 ? 50 : 72;
		var pct = ss.score / max;
		if (!ss.done) {
			return { label: 'Not attempted', bg: '#f5f5f5', text: '#aaa' };
		}
		if (pct >= 0.8) {
			return { label: 'Confident', bg: '#E1F5EE', text: '#085041' };
		}
		if (pct >= 0.5) {
			return { label: 'Getting there', bg: '#FAEEDA', text: '#633806' };
		}
		return { label: 'Needs review', bg: '#FAECE7', text: '#712B13' };
	}

	function renderSummary() {
		var done = doneCount();
		var total = tot();
		var h = '<div class="cc-summary-card">';
		h += '<p class="cc-sum-title">Your results</p>';
		h +=
			'<p class="cc-sum-sub">' +
			done +
			' of 5 key stages completed &middot; ' +
			total +
			' total points</p>';
		h += '<div class="cc-sum-grid">';
		STAGES.forEach(function (st, si) {
			var lv = ksLevel(si);
			h +=
				'<button type="button" class="cc-sum-ks" style="background:' +
				lv.bg +
				'" onclick="aiadJump(' +
				si +
				')" title="Go to ' +
				st.ks +
				'">';
			h += '<div class="cc-sum-ks-lbl" style="color:' + lv.text + '">' + st.ks + '</div>';
			h +=
				'<div class="cc-sum-ks-val" style="color:' +
				lv.text +
				'">' +
				(SS[si].done ? SS[si].score : '\u2014') +
				'</div>';
			h +=
				'<div class="cc-sum-ks-status" style="color:' +
				lv.text +
				'">' +
				lv.label +
				'</div>';
			h += '</button>';
		});
		h += '</div></div>';
		if (done > 0) {
			h +=
				'<div class="cc-insight"><strong>The AI connection:</strong> AI touches every one of these stages \u2014 from sequencing instructions at KS1 (the basis of all AI logic), to bias and ethics at KS4, to the data structures powering machine learning at KS5. Computing is not just a subject \u2014 it is the language AI speaks.</div>';
		}
		var remaining = STAGES.filter(function (st, si) {
			return !SS[si].done;
		});
		if (remaining.length > 0) {
			h += '<div class="cc-nav-row">';
			remaining.forEach(function (st) {
				var si = STAGES.indexOf(st);
				h += '<button type="button" onclick="aiadJump(' + si + ')">Start ' + st.ks + '</button>';
			});
			h += '</div>';
		} else {
			h +=
				'<div class="cc-nav-row"><button type="button" class="btn-primary" onclick="aiadResetAll()">Reset all & try again</button></div>';
		}
		document.getElementById('aiad-summary').innerHTML = h;
	}

	window.aiadResetAll = function () {
		for (var i = 0; i < SS.length; i++) {
			SS[i] = { qi: 0, answered: false, score: 0, done: false, ticks: {} };
		}
		renderTabs();
		aiadJump(0);
	};

	function init() {
		if (!document.getElementById('aiad-cc')) {
			return;
		}
		buildScreens();
		renderTabs();
		aiadJump(0);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
