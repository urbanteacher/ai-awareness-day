(function () {
	'use strict';

	var STORAGE_KEY = 'aiad_speed_quiz_leaderboard_v1';
	var MAX_LEADERBOARD = 20;

	var QUESTION_POOL = [
		{
			q: 'What is a token in a large language model?',
			options: ['A type of computer virus', 'A small chunk of text the model reads', 'A login password for ChatGPT', 'A school merit point'],
			answer: 1,
		},
		{
			q: '“Hallucination” in AI usually means…',
			options: ['The model sees images', 'The model makes up convincing but false facts', 'The screen flickers', 'Students daydream in class'],
			answer: 1,
		},
		{
			q: '“Human in the loop” means…',
			options: ['AI replaces all teachers', 'People stay involved in reviewing important decisions', 'Students run laps', 'The model only works offline'],
			answer: 1,
		},
		{
			q: 'Which is the best first step when a student uses AI for research?',
			options: ['Submit the output unchanged', 'Verify facts with trusted sources', 'Share passwords with the class', 'Turn off the internet'],
			answer: 1,
		},
		{
			q: 'Prompt engineering is mainly about…',
			options: ['Building physical robots', 'Writing clear instructions to get useful AI outputs', 'Installing faster Wi‑Fi', 'Marking exam papers faster'],
			answer: 1,
		},
		{
			q: 'What does multimodal AI mean?',
			options: ['AI that only types essays', 'AI that can work with text, images, audio and more', 'AI used only in maths', 'AI that runs without electricity'],
			answer: 1,
		},
		{
			q: '“AI slop” refers to…',
			options: ['Spilled drinks on laptops', 'Low-quality, generic AI-generated content', 'A new science course', 'Free school meals'],
			answer: 1,
		},
		{
			q: 'RAG (retrieval-augmented generation) helps AI by…',
			options: ['Making images brighter', 'Looking up documents before answering', 'Deleting old emails', 'Speeding up the school bus'],
			answer: 1,
		},
		{
			q: 'A larger context window generally means…',
			options: ['The model forgets everything instantly', 'The model can consider more text in one conversation', 'The screen is physically bigger', 'Homework is longer'],
			answer: 1,
		},
		{
			q: 'When might “agentic AI” be risky in schools?',
			options: ['When it takes multi-step actions without enough oversight', 'When it prints worksheets', 'When it saves a draft', 'When it suggests synonyms'],
			answer: 0,
		},
		{
			q: 'Training an LLM is most like…',
			options: ['A single revision session', 'Reading and practising next-word prediction at huge scale', 'Installing an app update once', 'Writing one lesson plan'],
			answer: 1,
		},
		{
			q: 'Which is a sensible classroom rule for generative AI?',
			options: ['Never cite your sources', 'Declare when AI was used and verify key facts', 'Share accounts between students', 'Submit whatever the model outputs'],
			answer: 1,
		},
		{
			q: 'Attention in a transformer model helps the AI…',
			options: ['Connect words across a sentence for meaning', 'Increase screen brightness', 'Charge laptop batteries', 'Sort pupils alphabetically'],
			answer: 0,
		},
		{
			q: '“AI washing” means…',
			options: ['Cleaning keyboards', 'Marketing something as AI when it barely uses AI', 'Washing uniforms', 'Filtering drinking water'],
			answer: 1,
		},
		{
			q: 'A reasoned model (e.g. extended thinking) typically…',
			options: ['Skips steps and guesses', 'Works through steps before giving an answer', 'Only works in art class', 'Cannot read English'],
			answer: 1,
		},
		{
			q: 'For safeguarding, teachers should treat AI chat logs as…',
			options: ['Private jokes', 'Potentially sensitive data needing policy care', 'Public social media', 'Automatic truth'],
			answer: 1,
		},
		{
			q: 'Distillation in AI training is when…',
			options: ['Water cools the server', 'A smaller model learns from a larger one', 'Students copy homework', 'Screens get smaller'],
			answer: 1,
		},
		{
			q: 'Which is true about LLM predictions?',
			options: ['They pick the next token from probability scores', 'They always know current events', 'They never repeat phrases', 'They only output images'],
			answer: 0,
		},
		{
			q: 'Context engineering emphasises…',
			options: ['Only the final question', 'Background, examples and setup — not just the prompt line', 'Building school buildings', 'Deleting old files'],
			answer: 1,
		},
		{
			q: 'A good staff-room use of AI is to…',
			options: ['Draft ideas you then check and adapt', 'Publish reports without reading them', 'Store pupil passwords', 'Replace all safeguarding procedures'],
			answer: 0,
		},
	];

	function shuffle(arr) {
		var a = arr.slice();
		var i = a.length;
		var j;
		var t;
		while (i > 0) {
			j = Math.floor(Math.random() * i);
			i -= 1;
			t = a[i];
			a[i] = a[j];
			a[j] = t;
		}
		return a;
	}

	function escapeHtml(str) {
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;');
	}

	function formatTime(ms) {
		var sec = Math.floor(ms / 1000);
		var m = Math.floor(sec / 60);
		var s = sec % 60;
		return m + ':' + (s < 10 ? '0' : '') + s;
	}

	function loadLeaderboard() {
		try {
			var raw = localStorage.getItem(STORAGE_KEY);
			if (!raw) {
				return [];
			}
			var data = JSON.parse(raw);
			return Array.isArray(data) ? data : [];
		} catch (e) {
			return [];
		}
	}

	function saveLeaderboard(list) {
		try {
			localStorage.setItem(STORAGE_KEY, JSON.stringify(list.slice(0, MAX_LEADERBOARD)));
		} catch (e) {
			/* ignore quota */
		}
	}

	function addScore(entry) {
		var list = loadLeaderboard();
		list.push(entry);
		list.sort(function (a, b) {
			if (b.score !== a.score) {
				return b.score - a.score;
			}
			return a.timeMs - b.timeMs;
		});
		saveLeaderboard(list);
		return list;
	}

	function getRank(list, entry) {
		var i;
		for (i = 0; i < list.length; i++) {
			if (
				list[i].name === entry.name &&
				list[i].score === entry.score &&
				list[i].timeMs === entry.timeMs &&
				list[i].at === entry.at
			) {
				return i + 1;
			}
		}
		return list.length;
	}

	function initRoot(root) {
		if (!root || root.getAttribute('data-aiad-speed-quiz-ready') === '1') {
			return;
		}
		root.setAttribute('data-aiad-speed-quiz-ready', '1');

		var totalQuestions = parseInt(root.getAttribute('data-questions') || '10', 10);
		var secondsPerQ = parseInt(root.getAttribute('data-seconds') || '15', 10);
		var speedBonus = parseInt(root.getAttribute('data-bonus') || '10', 10);
		var basePoints = parseInt(root.getAttribute('data-points') || '100', 10);

		var startEl = root.querySelector('[data-sq-start]');
		var playEl = root.querySelector('[data-sq-play]');
		var resultsEl = root.querySelector('[data-sq-results]');
		var nameInput = root.querySelector('[data-sq-name]');
		var startBtn = root.querySelector('[data-sq-start-btn]');

		var timerFill = root.querySelector('[data-sq-timer-fill]');
		var qNumEl = root.querySelector('[data-sq-q-num]');
		var questionEl = root.querySelector('[data-sq-question]');
		var optionsEl = root.querySelector('[data-sq-options]');
		var flashEl = root.querySelector('[data-sq-flash]');
		var scoreLive = root.querySelector('[data-sq-score-live]');
		var qLive = root.querySelector('[data-sq-q-live]');

		var resultsBody = root.querySelector('[data-sq-results-body]');
		var lbList = root.querySelector('[data-sq-lb-list]');
		var retryBtn = root.querySelector('[data-sq-retry]');
		var copyBtn = root.querySelector('[data-sq-copy]');

		if (!startEl || !playEl || !resultsEl || !startBtn) {
			return;
		}

		var state = {
			name: '',
			questions: [],
			index: 0,
			score: 0,
			correct: 0,
			timeLeft: secondsPerQ,
			timerId: null,
			questionStarted: 0,
			totalTimeMs: 0,
			locked: false,
		};

		function showScreen(which) {
			startEl.hidden = which !== 'start';
			playEl.hidden = which !== 'play';
			resultsEl.hidden = which !== 'results';
		}

		function pickQuestions() {
			return shuffle(QUESTION_POOL).slice(0, Math.min(totalQuestions, QUESTION_POOL.length));
		}

		function clearTimer() {
			if (state.timerId) {
				clearInterval(state.timerId);
				state.timerId = null;
			}
		}

		function updateTimerBar() {
			if (!timerFill) {
				return;
			}
			var pct = (state.timeLeft / secondsPerQ) * 100;
			timerFill.style.width = pct + '%';
			timerFill.classList.remove('is-low', 'is-critical');
			if (pct <= 20) {
				timerFill.classList.add('is-critical');
			} else if (pct <= 45) {
				timerFill.classList.add('is-low');
			}
		}

		function renderQuestion() {
			var q = state.questions[state.index];
			if (!q) {
				return;
			}
			if (qNumEl) {
				qNumEl.textContent = 'Question ' + (state.index + 1) + ' of ' + state.questions.length;
			}
			if (qLive) {
				qLive.textContent = String(state.index + 1) + '/' + state.questions.length;
			}
			if (questionEl) {
				questionEl.textContent = q.q;
			}
			if (flashEl) {
				flashEl.textContent = '';
				flashEl.className = 'sq-flash';
			}
			var html = '';
			q.options.forEach(function (opt, i) {
				html +=
					'<button type="button" class="sq-option" data-sq-opt="' +
					i +
					'">' +
					escapeHtml(opt) +
					'</button>';
			});
			if (optionsEl) {
				optionsEl.innerHTML = html;
			}
			state.timeLeft = secondsPerQ;
			state.questionStarted = Date.now();
			state.locked = false;
			updateTimerBar();
		}

		function startTimer() {
			clearTimer();
			state.timerId = setInterval(function () {
				state.timeLeft -= 0.1;
				if (state.timeLeft <= 0) {
					state.timeLeft = 0;
					updateTimerBar();
					clearTimer();
					answerQuestion(-1);
					return;
				}
				updateTimerBar();
			}, 100);
		}

		function answerQuestion(choice) {
			if (state.locked) {
				return;
			}
			state.locked = true;
			clearTimer();

			var q = state.questions[state.index];
			var elapsed = Date.now() - state.questionStarted;
			state.totalTimeMs += elapsed;

			var correct = choice === q.answer;
			var points = 0;
			if (correct) {
				state.correct += 1;
				var secsLeft = Math.max(0, Math.ceil(state.timeLeft));
				points = basePoints + secsLeft * speedBonus;
				state.score += points;
				if (flashEl) {
					flashEl.textContent = '+' + points + ' (' + basePoints + ' + ' + secsLeft + '×' + speedBonus + ' speed)';
					flashEl.className = 'sq-flash sq-flash--ok';
				}
			} else if (flashEl) {
				flashEl.textContent = 'Time! Correct: ' + q.options[q.answer];
				flashEl.className = 'sq-flash sq-flash--miss';
			}

			if (scoreLive) {
				scoreLive.textContent = String(state.score);
			}

			var buttons = optionsEl ? optionsEl.querySelectorAll('.sq-option') : [];
			var i;
			for (i = 0; i < buttons.length; i++) {
				buttons[i].disabled = true;
				if (i === q.answer) {
					buttons[i].classList.add('is-correct');
				} else if (i === choice) {
					buttons[i].classList.add('is-wrong');
				}
			}

			setTimeout(function () {
				state.index += 1;
				if (state.index >= state.questions.length) {
					finishGame();
				} else {
					renderQuestion();
					startTimer();
				}
			}, correct ? 650 : 1100);
		}

		function renderLeaderboard(list, highlightEntry) {
			if (!lbList) {
				return;
			}
			if (!list.length) {
				lbList.innerHTML = '<li class="sq-lb-item">No scores yet — you are first!</li>';
				return;
			}
			var html = '';
			list.slice(0, 10).forEach(function (row, idx) {
				var you =
					highlightEntry &&
					row.name === highlightEntry.name &&
					row.score === highlightEntry.score &&
					row.at === highlightEntry.at;
				html +=
					'<li class="sq-lb-item' +
					(you ? ' sq-lb-you' : '') +
					'">' +
					'<span class="sq-lb-rank">' +
					(idx + 1) +
					'</span>' +
					'<span class="sq-lb-name">' +
					escapeHtml(row.name) +
					'</span>' +
					'<span class="sq-lb-score">' +
					row.score +
					' pts · ' +
					formatTime(row.timeMs) +
					'</span></li>';
			});
			lbList.innerHTML = html;
		}

		function finishGame() {
			clearTimer();
			var entry = {
				name: state.name,
				score: state.score,
				correct: state.correct,
				total: state.questions.length,
				timeMs: state.totalTimeMs,
				at: Date.now(),
			};
			var list = addScore(entry);
			var rank = getRank(list, entry);

			if (resultsBody) {
				resultsBody.innerHTML =
					'<div class="sq-results-score">' +
					'<p class="sq-results-score__num">' +
					state.score +
					'</p>' +
					'<p class="sq-results-score__lbl">points · ' +
					state.correct +
					'/' +
					state.questions.length +
					' correct · ' +
					formatTime(state.totalTimeMs) +
					'</p>' +
					'<p class="sq-results-score__lbl">Leaderboard rank: <strong>#' +
					rank +
					'</strong> of ' +
					list.length +
					'</p></div>' +
					'<div class="sq-results-grid">' +
					'<div class="sq-stat"><span class="sq-stat__val">' +
					state.correct +
					'</span><span class="sq-stat__lbl">correct</span></div>' +
					'<div class="sq-stat"><span class="sq-stat__val">' +
					formatTime(state.totalTimeMs) +
					'</span><span class="sq-stat__lbl">total time</span></div>' +
					'<div class="sq-stat"><span class="sq-stat__val">+' +
					speedBonus +
					'</span><span class="sq-stat__lbl">per sec left</span></div>' +
					'</div>';
			}

			renderLeaderboard(list, entry);
			showScreen('results');
			resultsEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
		}

		function beginGame() {
			var name = nameInput ? nameInput.value.trim() : '';
			if (!name) {
				if (nameInput) {
					nameInput.focus();
					nameInput.placeholder = 'Please enter your name';
				}
				return;
			}
			state.name = name.slice(0, 40);
			state.questions = pickQuestions();
			state.index = 0;
			state.score = 0;
			state.correct = 0;
			state.totalTimeMs = 0;
			if (scoreLive) {
				scoreLive.textContent = '0';
			}
			showScreen('play');
			renderQuestion();
			startTimer();
			playEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
		}

		function resetToStart() {
			clearTimer();
			showScreen('start');
			renderLeaderboard(loadLeaderboard(), null);
		}

		startBtn.addEventListener('click', beginGame);
		if (nameInput) {
			nameInput.addEventListener('keydown', function (e) {
				if (e.key === 'Enter') {
					beginGame();
				}
			});
		}

		if (optionsEl) {
			optionsEl.addEventListener('click', function (e) {
				var btn = e.target.closest('[data-sq-opt]');
				if (!btn) {
					return;
				}
				answerQuestion(parseInt(btn.getAttribute('data-sq-opt'), 10));
			});
		}

		if (retryBtn) {
			retryBtn.addEventListener('click', resetToStart);
		}

		if (copyBtn) {
			copyBtn.addEventListener('click', function () {
				var text =
					'I scored ' +
					state.score +
					' on the AI Awareness Day speed quiz (' +
					state.correct +
					'/' +
					state.questions.length +
					' correct in ' +
					formatTime(state.totalTimeMs) +
					'). Try it: ' +
					(window.location.href || '');
				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(text);
					copyBtn.textContent = 'Copied!';
					setTimeout(function () {
						copyBtn.textContent = 'Copy result';
					}, 2000);
				}
			});
		}

		showScreen('start');
		renderLeaderboard(loadLeaderboard(), null);
	}

	function boot() {
		document.querySelectorAll('[data-aiad-speed-quiz]').forEach(initRoot);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
