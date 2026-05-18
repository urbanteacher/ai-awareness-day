(function () {
	'use strict';

	var TOKENS = ['The', 'cat', 'sat', 'on', 'the', 'mat'];
	var TOK_BG = ['#EEEDFE', '#E1F5EE', '#FAECE7', '#FAEEDA', '#E6F1FB', '#EAF3DE'];
	var TOK_TXT = ['#3C3489', '#085041', '#712B13', '#633806', '#0C447C', '#27500A'];

	var ATTENTIONS = [
		[0.05, 0.1, 0.08, 0.03, 0.04, 0.02],
		[0.12, 0.05, 0.55, 0.04, 0.06, 0.08],
		[0.08, 0.55, 0.05, 0.12, 0.07, 0.13],
		[0.04, 0.05, 0.14, 0.05, 0.55, 0.04],
		[0.03, 0.04, 0.08, 0.55, 0.05, 0.12],
		[0.02, 0.06, 0.12, 0.04, 0.14, 0.05],
	];

	var PREDS = [
		{ word: 'happily', pct: 38 },
		{ word: 'quietly', pct: 24 },
		{ word: 'down', pct: 14 },
		{ word: 'outside', pct: 10 },
		{ word: 'nearby', pct: 8 },
		{ word: 'alone', pct: 6 },
	];

	var STEPS = [
		{
			title: 'Step 1: Everything starts as tokens',
			sub: 'Before an AI can read anything, it breaks text into small chunks called tokens. A token is roughly a word — but short words might be one token, and longer words can be split into two or three.',
			analogy:
				'Imagine reading a sentence by covering it with a ruler and revealing one word at a time. The AI never sees a full sentence at once — it processes a sequence of tokens, one after another.',
			render: 'tokens',
		},
		{
			title: 'Step 2: Tokens become numbers',
			sub: 'The AI cannot understand words — only numbers. Each token is converted into a long list of numbers (called a vector or embedding) that captures something about its meaning and context.',
			analogy:
				'Like converting every student in a class into a unique ID number before entering a spreadsheet. The meaning is still there — just in a form the computer can work with.',
			render: 'embeddings',
		},
		{
			title: 'Step 3: Attention — every word looks at every other word',
			sub: 'For every token, the AI calculates how much it should pay attention to every other token. Click a word below to see its attention pattern.',
			analogy:
				'Like a student re-reading a sentence and underlining the words that most help them understand a particular word. "Bank" needs to look at "river" or "money" to know what it means.',
			render: 'attention',
		},
		{
			title: 'Step 4: Layers of understanding',
			sub: 'Attention happens not once, but through many layers stacked on top of each other. Early layers catch grammar. Deeper layers understand meaning and intent.',
			analogy:
				'Like marking a piece of work in three passes — first checking spelling, then sentence structure, then whether the argument makes sense. Each pass builds on the last.',
			render: 'layers',
		},
		{
			title: 'Step 5: Predicting the next token',
			sub: 'After all the layers, the model produces a ranked list of probabilities for what the next token should be. It picks one — with a little randomness — and repeats the whole process.',
			analogy:
				'Like a student completing a cloze exercise — but instead of one answer, they are given every possible next word with a probability score attached to each.',
			render: 'prediction',
		},
		{
			title: 'Step 6: Training — how it learned all this',
			sub: 'The model started knowing nothing. It was trained on billions of text examples — predicting the next word over and over, adjusting when it got it wrong.',
			analogy:
				'Imagine a student who has read every book in every library, been corrected every time they predicted the wrong next word, and has done this billions of times. That is training.',
			render: 'training',
		},
	];

	function initRoot(root) {
		if (!root || root.getAttribute('data-aiad-llm-ready') === '1') {
			return;
		}
		root.setAttribute('data-aiad-llm-ready', '1');

		var nav = root.querySelector('[data-aiad-llm-nav]');
		var panel = root.querySelector('[data-aiad-llm-panel]');
		if (!nav || !panel) {
			return;
		}

		var state = {
			current: 0,
			selectedTok: 2,
		};

		function tokButton(i, size, opacity, selected, interactive) {
			var s = size || 13;
			var o = opacity !== undefined ? opacity : 1;
			var bg = selected ? '#1a1a2e' : TOK_BG[i];
			var col = selected ? '#fff' : TOK_TXT[i];
			var cls = 'tok' + (interactive ? '' : ' tok--static');
			var attrs = interactive
				? ' type="button" data-aiad-llm-tok="' + i + '" aria-pressed="' + (selected ? 'true' : 'false') + '"'
				: '';
			return (
				'<' +
				(interactive ? 'button' : 'span') +
				' class="' +
				cls +
				'" style="background:' +
				bg +
				';color:' +
				col +
				';font-size:' +
				s +
				'px;opacity:' +
				o.toFixed(2) +
				'"' +
				attrs +
				'>' +
				TOKENS[i] +
				'</' +
				(interactive ? 'button' : 'span') +
				'>'
			);
		}

		function renderTokens() {
			var h = '<div class="token-stream">';
			TOKENS.forEach(function (t, i) {
				h += tokButton(i, 13, 1, false, false);
				if (i < TOKENS.length - 1) {
					h += '<span class="token-arrow" aria-hidden="true">&rarr;</span>';
				}
			});
			h += '</div>';
			h += '<p class="meta-note">6 tokens — each becomes a number the AI can process.</p>';
			return h;
		}

		function renderEmbeddings() {
			var h = '<div>';
			TOKENS.forEach(function (t, i) {
				var nums = [];
				var j;
				for (j = 0; j < 8; j++) {
					nums.push(((Math.sin(i * 7 + j * 3) * 0.5 + 0.5) * 1.8 - 0.9).toFixed(2));
				}
				h += '<div class="embed-row">';
				h +=
					'<span class="tok tok--static" style="background:' +
					TOK_BG[i] +
					';color:' +
					TOK_TXT[i] +
					';width:52px;flex-shrink:0">' +
					t +
					'</span>';
				h += '<span class="embed-nums">[' + nums.join(', ') + ' …]</span>';
				h += '</div>';
			});
			h += '</div>';
			h += '<p class="meta-note">Each token maps to ~768 numbers in a real model. Shown here: 8.</p>';
			return h;
		}

		function renderAttention() {
			var weights = ATTENTIONS[state.selectedTok];
			var h = '<div class="token-stream">';
			TOKENS.forEach(function (t, i) {
				var w = weights[i];
				var size = Math.round(11 + w * 18);
				var opacity = 0.2 + w * 0.8;
				h += tokButton(i, size, opacity, i === state.selectedTok, true);
			});
			h += '</div>';
			h += '<p class="att-hint">Click any word to see what it pays attention to. Word size = attention weight.</p>';
			TOKENS.forEach(function (t, i) {
				var pct = Math.round(weights[i] * 100);
				var barCol = i === state.selectedTok ? '#1a1a2e' : '#7F77DD';
				h += '<div class="att-bar-row">';
				h += '<span class="att-bar-label">' + t + '</span>';
				h += '<div class="att-bar-wrap"><div class="att-bar-fill" style="width:' + pct + '%;background:' + barCol + '"></div></div>';
				h += '<span class="att-bar-pct">' + pct + '%</span>';
				h += '</div>';
			});
			return h;
		}

		function renderLayers() {
			var layers = [
				{ label: 'Layer 1', desc: 'Spelling & punctuation', w: 30, bg: '#E1F5EE', col: '#085041' },
				{ label: 'Layer 2', desc: 'Word type (noun, verb…)', w: 50, bg: '#EEEDFE', col: '#3C3489' },
				{ label: 'Layer 3', desc: 'Sentence grammar', w: 65, bg: '#FAEEDA', col: '#633806' },
				{ label: 'Layer 4', desc: 'Topic & meaning', w: 80, bg: '#FAECE7', col: '#712B13' },
				{ label: 'Layer 5', desc: 'Context & intent', w: 92, bg: '#E6F1FB', col: '#0C447C' },
				{ label: 'Output', desc: 'Next token prediction', w: 100, bg: '#1a1a2e', col: '#fff' },
			];
			var h = '<div>';
			layers.forEach(function (l) {
				h += '<div class="layer-row">';
				h += '<span class="layer-label">' + l.label + '</span>';
				h +=
					'<div class="layer-bar-wrap"><div class="layer-fill" style="width:' +
					l.w +
					'%;background:' +
					l.bg +
					';color:' +
					l.col +
					'">' +
					l.desc +
					'</div></div>';
				h += '</div>';
			});
			h += '</div>';
			h += '<p class="meta-note">GPT-4 has 96 layers. Each refines how every token is understood.</p>';
			return h;
		}

		function renderPrediction() {
			var h = '<div class="token-stream">';
			TOKENS.forEach(function (t, i) {
				h +=
					'<span class="tok tok--static" style="background:' +
					TOK_BG[i] +
					';color:' +
					TOK_TXT[i] +
					'">' +
					t +
					'</span>';
			});
			h += '<span class="token-arrow" aria-hidden="true">&rarr;</span>';
			h +=
				'<span class="tok tok--static" style="background:#1a1a2e;color:#fff;border:1px dashed rgba(255,255,255,0.3)">?</span>';
			h += '</div>';
			h += '<p class="att-hint">Top predictions for the next token:</p>';
			h += '<div class="pred-row">';
			PREDS.forEach(function (p) {
				h += '<div class="pred-item">';
				h += '<span class="pred-word">' + p.word + '</span>';
				h += '<div class="pred-bar-wrap"><div class="pred-bar" style="width:' + p.pct + '%"></div></div>';
				h += '<span class="pred-pct">' + p.pct + '%</span>';
				h += '</div>';
			});
			h += '</div>';
			h += '<p class="meta-note">Slight randomness is added so responses feel natural, not robotic.</p>';
			return h;
		}

		function renderTraining() {
			var data = [
				{ label: 'Books & articles', val: 85, bg: '#EEEDFE', col: '#3C3489' },
				{ label: 'Websites', val: 95, bg: '#E1F5EE', col: '#085041' },
				{ label: 'Code repos', val: 60, bg: '#FAEEDA', col: '#633806' },
				{ label: 'Research papers', val: 45, bg: '#E6F1FB', col: '#0C447C' },
			];
			var h = '<div>';
			data.forEach(function (d) {
				h += '<div class="layer-row">';
				h += '<span class="layer-label">' + d.label + '</span>';
				h +=
					'<div class="layer-bar-wrap"><div class="layer-fill" style="width:' +
					d.val +
					'%;background:' +
					d.bg +
					';color:' +
					d.col +
					'">' +
					d.val +
					'%</div></div>';
				h += '</div>';
			});
			h += '</div>';
			h += '<div class="stat-grid">';
			[
				{ n: '~1 trillion', l: 'tokens trained on' },
				{ n: 'Billions', l: 'of parameters' },
				{ n: 'Months', l: 'of compute time' },
				{ n: 'RLHF', l: 'human feedback tuning' },
			].forEach(function (s) {
				h += '<div class="stat-card"><p class="stat-num">' + s.n + '</p><p class="stat-lbl">' + s.l + '</p></div>';
			});
			h += '</div>';
			return h;
		}

		function renderVisual(type) {
			switch (type) {
				case 'tokens':
					return renderTokens();
				case 'embeddings':
					return renderEmbeddings();
				case 'attention':
					return renderAttention();
				case 'layers':
					return renderLayers();
				case 'prediction':
					return renderPrediction();
				case 'training':
					return renderTraining();
				default:
					return '';
			}
		}

		function renderNav() {
			var h = '';
			STEPS.forEach(function (s, i) {
				if (i > 0) {
					h += '<div class="step-line" aria-hidden="true"></div>';
				}
				var cls = 'step-dot';
				if (i === state.current) {
					cls += ' active';
				} else if (i < state.current) {
					cls += ' done';
				}
				h +=
					'<button type="button" class="' +
					cls +
					'" data-aiad-llm-step="' +
					i +
					'" aria-label="' +
					s.title +
					'" aria-current="' +
					(i === state.current ? 'step' : 'false') +
					'">' +
					(i + 1) +
					'</button>';
			});
			nav.innerHTML = h;
		}

		function goTo(i) {
			if (i < 0 || i >= STEPS.length) {
				return;
			}
			state.current = i;
			renderNav();
			renderPanel();
			root.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}

		function renderPanel() {
			var s = STEPS[state.current];
			var isLast = state.current === STEPS.length - 1;
			var h = '<p class="step-title">' + s.title + '</p>';
			h += '<p class="step-sub">' + s.sub + '</p>';
			h += '<div class="step-visual" data-aiad-llm-visual>' + renderVisual(s.render) + '</div>';
			h += '<div class="step-analogy"><strong>Classroom analogy:</strong> ' + s.analogy + '</div>';
			h += '<div class="nav-row">';
			if (state.current > 0) {
				h +=
					'<button type="button" data-aiad-llm-prev>&larr; Previous</button>';
			}
			if (!isLast) {
				h += '<button type="button" class="btn-primary" data-aiad-llm-next>Next &rarr;</button>';
			} else {
				var moreUrl = root.getAttribute('data-more-url') || '/';
				h +=
					'<a class="btn-primary" href="' +
					moreUrl +
					'" style="display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:10px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;background:#1a1a2e;color:#fff;border:2px solid #1a1a2e">Explore more &rarr;</a>';
			}
			h += '</div>';
			panel.innerHTML = h;
		}

		root.addEventListener('click', function (e) {
			var stepBtn = e.target.closest('[data-aiad-llm-step]');
			if (stepBtn) {
				goTo(parseInt(stepBtn.getAttribute('data-aiad-llm-step'), 10));
				return;
			}
			if (e.target.closest('[data-aiad-llm-prev]')) {
				goTo(state.current - 1);
				return;
			}
			if (e.target.closest('[data-aiad-llm-next]')) {
				goTo(state.current + 1);
				return;
			}
			var tokBtn = e.target.closest('[data-aiad-llm-tok]');
			if (tokBtn && state.current === 2) {
				state.selectedTok = parseInt(tokBtn.getAttribute('data-aiad-llm-tok'), 10);
				var vis = panel.querySelector('[data-aiad-llm-visual]');
				if (vis) {
					vis.innerHTML = renderAttention();
				}
			}
		});

		renderNav();
		renderPanel();
	}

	function boot() {
		document.querySelectorAll('[data-aiad-llm]').forEach(initRoot);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
