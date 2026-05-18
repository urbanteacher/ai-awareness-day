(function () {
	'use strict';

	var CATS = {
		hot: { label: 'Hottest right now', bg: '#FFF0EC', text: '#993C1D', tagBg: '#FFF0EC', tagText: '#993C1D' },
		agents: { label: 'Agents & autonomy', bg: '#F0EFFE', text: '#534AB7', tagBg: '#F0EFFE', tagText: '#534AB7' },
		safety: { label: 'Safety & ethics', bg: '#FEF6E6', text: '#854F0B', tagBg: '#FEF6E6', tagText: '#854F0B' },
		models: { label: 'Models & tech', bg: '#E8F8F3', text: '#0F6E56', tagBg: '#E8F8F3', tagText: '#0F6E56' },
	};

	var WORDS = [
		{ word: 'Agentic AI', cat: 'agents', hype: 5, preview: 'AI that can plan, decide and take actions on its own — without a human approving every step.', detail: 'Agentic AI goes beyond chatting. It can browse the web, write and run code, send emails, and complete multi-step tasks autonomously. In 2026, "agentic" is the word in every AI headline. Companies like Anthropic, OpenAI and Microsoft are racing to build agents that work like virtual assistants with real-world capabilities.', classroom: 'Think of it like a student who, instead of just answering a question, goes away, does the research, writes the report and hands it in — all without being asked twice.' },
		{ word: 'AI agent', cat: 'agents', hype: 5, preview: 'A specific AI system designed to complete tasks independently, often using tools and making decisions along the way.', detail: 'An AI agent is given a goal and figures out how to achieve it — searching, writing, calculating, clicking — without step-by-step instructions. In 2025–2026, agents moved from labs to real products. Tools like Claude, Copilot and Gemini all now have "agent modes".', classroom: 'Analogy: a capable classroom assistant you give one instruction to ("prepare Monday\'s lesson resources") and they handle everything from there.' },
		{ word: 'Vibe coding', cat: 'hot', hype: 5, preview: 'Writing software by describing what you want in plain English — no coding knowledge needed.', detail: 'Coined by OpenAI\'s Andrej Karpathy in 2025, vibe coding means using AI tools to build apps, websites and tools just by describing them conversationally. It went viral because it opened software creation to non-developers — and sparked huge debate about what "coding" even means now.', classroom: 'Hugely relevant for students: the skill shifts from "write the code" to "describe the problem clearly." Prompt writing becomes the new literacy.' },
		{ word: 'Reasoning model', cat: 'models', hype: 5, preview: 'An AI that thinks through a problem step by step before answering — rather than just predicting the next word.', detail: 'Models like OpenAI\'s o3, DeepSeek R1 and Anthropic\'s extended thinking can "chain" reasoning steps together, working through complex maths, logic or analysis. This made AI significantly better at hard problems and caused a wave of debate about whether AI can truly "think".', classroom: 'Analogy: the difference between a student who blurts the first answer versus one who shows their working. Reasoning models show their working.' },
		{ word: 'Prompt engineering', cat: 'hot', hype: 4, preview: 'The skill of writing clear, effective instructions to get the best results from an AI.', detail: 'How you ask an AI something dramatically changes what you get back. Prompt engineering — choosing the right words, structure and context — is now considered a key professional skill. Some companies hire "prompt engineers" full time. For teachers, it\'s the most immediately practical AI skill to develop.', classroom: 'Classroom activity: give students the same task with different prompts and compare results. Who wrote the best prompt — and why?' },
		{ word: 'Multimodal AI', cat: 'models', hype: 4, preview: 'AI that can work with multiple types of input — text, images, audio and video — all at once.', detail: 'Modern AI models like GPT-4o, Gemini and Claude can read text, look at images, listen to audio and analyse documents in the same conversation. This makes them far more versatile and has opened up new uses in medicine, education, science and creative work.', classroom: 'Teachers can now upload worksheets, diagrams, or photos of student work and ask AI to analyse them — not just typed questions.' },
		{ word: 'Hallucination', cat: 'safety', hype: 4, preview: 'When AI confidently makes up facts that sound true but aren\'t.', detail: 'AI hallucinations are one of the biggest safety concerns in education. Models sometimes generate plausible-sounding but completely false information — fake references, wrong dates, invented statistics. Understanding this is essential for responsible use. Always verify AI outputs, especially for facts.', classroom: 'Critical thinking exercise: ask an AI about a niche topic, then fact-check every claim. Students quickly learn why AI is a starting point, not a final source.' },
		{ word: 'Context window', cat: 'models', hype: 3, preview: 'How much information an AI can "hold in mind" during a single conversation.', detail: 'The context window is the AI\'s working memory — everything it can read and refer to at once. In 2025, these expanded enormously: some models can now hold entire books or research papers in one session. A larger context window means more useful, consistent conversations.', classroom: 'If an AI seems to "forget" what you said earlier in a long chat, you\'ve likely hit the context limit. Starting a fresh conversation resets it.' },
		{ word: 'AI slop', cat: 'safety', hype: 4, preview: 'Low-quality, generic AI-generated content produced without thought or care.', detail: 'AI slop is the term for the wave of bland, repetitive, error-filled content produced at scale using generative AI — fake articles, copied images, hollow essays. It\'s a growing concern in education as students submit AI-written work without engaging with it themselves.', classroom: 'Ask students: "What makes this AI output feel generic?" Teaching them to spot slop helps them write better prompts — and better essays.' },
		{ word: 'Superintelligence', cat: 'hot', hype: 5, preview: 'A hypothetical AI far more capable than any human — the big, debated goal some labs are racing toward.', detail: 'In 2025, Meta, Microsoft and others announced teams dedicated to building superintelligence. But experts disagree on whether it\'s decades away or already close. Unlike AGI (general human-level intelligence), superintelligence would surpass all human cognitive abilities. It\'s equal parts scientific goal and cultural fear.', classroom: 'Discussion starter: "Do you think AI will ever be smarter than humans in every way — and what would that mean for us?"' },
		{ word: 'RAG', cat: 'models', hype: 3, preview: 'Retrieval-Augmented Generation — AI that searches a knowledge base before answering, to reduce made-up facts.', detail: 'RAG is how many enterprise AI tools work: instead of relying only on training data, the AI retrieves relevant documents first, then answers based on them. This dramatically reduces hallucinations and keeps information up to date. Many education AI tools use RAG behind the scenes.', classroom: 'Analogy: the difference between a student answering from memory versus being allowed to look up notes before answering. RAG lets AI "look things up."' },
		{ word: 'AI washing', cat: 'safety', hype: 4, preview: 'When products claim to be "AI-powered" for marketing purposes without doing anything meaningfully new.', detail: 'Like greenwashing in sustainability, AI washing is when companies slap "AI" on a product to sound cutting-edge — even if it\'s just a simple rule-based system or a basic chatbot. In 2026, regulators and researchers are increasingly calling this out. Critical evaluation of AI claims is a vital skill.', classroom: 'Ask students to find products marketed as "AI-powered." What does the AI actually do? Is it genuinely intelligent — or just a label?' },
		{ word: 'Distillation', cat: 'models', hype: 3, preview: 'Training a small, efficient AI model by having it learn from a much larger, more powerful one.', detail: 'DeepSeek\'s R1 shocked the industry in early 2025 by being nearly as capable as top US models at a fraction of the cost — thanks largely to distillation. A huge model acts as a "teacher," generating examples for a smaller "student" model to learn from. This makes powerful AI far more accessible.', classroom: 'Analogy: a senior teacher creates a comprehensive resource bank; a trainee studies it and reaches a similar teaching level far faster than starting from scratch.' },
		{ word: 'Human in the loop', cat: 'safety', hype: 3, preview: 'Keeping a human involved to review, approve or correct AI decisions — rather than letting AI act fully alone.', detail: 'As AI agents become more autonomous, "human in the loop" is the principle that people should remain in control of important decisions. It\'s central to AI safety frameworks in education, healthcare and law. The more consequential the action, the more essential the human check.', classroom: 'Policy discussion: where should teachers always remain "in the loop" when AI is used in school? Marking? Safeguarding? Lesson planning? Great debate topic.' },
		{ word: 'Context engineering', cat: 'hot', hype: 4, preview: 'Structuring everything you give an AI — background, rules and examples — not just the question.', detail: 'In 2026, "context engineering" is replacing "prompt engineering" as the hot term. It\'s the idea that the whole setup matters: system instructions, examples, documents included, and the order of information. Mastering context is becoming the key differentiator between people who use AI well and those who don\'t.', classroom: 'Practical activity: give the same AI task with different amounts of context — a bare question vs a question with background and examples. Compare the results.' },
	];

	var activeFilter = 'all';

	function hypeBar(n) {
		var out = '';
		var i;
		for (i = 0; i < 5; i++) {
			out += '<span class="aiad-hype-dot" style="background:' + (i < n ? '#D85A30' : '#e0e0e0') + '"></span>';
		}
		return out;
	}

	function renderFilters(root) {
		var el = root.querySelector('[data-aiad-bz-filters]');
		if (!el) {
			return;
		}
		var html = '<button type="button" class="aiad-filter-btn' + (activeFilter === 'all' ? ' active' : '') + '" data-filter="all">All 15 terms</button>';
		var k;
		for (k in CATS) {
			if (Object.prototype.hasOwnProperty.call(CATS, k)) {
				html += '<button type="button" class="aiad-filter-btn' + (activeFilter === k ? ' active' : '') + '" data-filter="' + k + '">' + CATS[k].label + '</button>';
			}
		}
		el.innerHTML = html;
	}

	function renderCards(root) {
		var countEl = root.querySelector('[data-aiad-bz-count]');
		var gridEl = root.querySelector('[data-aiad-bz-grid]');
		if (!gridEl) {
			return;
		}
		var filtered = activeFilter === 'all' ? WORDS : WORDS.filter(function (w) {
			return w.cat === activeFilter;
		});
		if (countEl) {
			countEl.textContent = 'Showing ' + filtered.length + ' of ' + WORDS.length + ' terms — click any card to expand';
		}
		var html = '';
		filtered.forEach(function (w) {
			var idx = WORDS.indexOf(w);
			var cat = CATS[w.cat];
			html += '<div class="aiad-card" id="aiad-card-' + idx + '" data-card-index="' + idx + '" role="button" tabindex="0" aria-expanded="false">';
			html += '<div class="aiad-card-top">';
			html += '<div class="aiad-icon" style="background:' + cat.bg + '"><span style="color:' + cat.text + ';font-weight:700;font-size:14px">' + w.word.charAt(0) + '</span></div>';
			html += '<div style="flex:1;min-width:0"><p class="aiad-card-title">' + w.word + '</p><div class="aiad-hype">' + hypeBar(w.hype) + '</div></div>';
			html += '<span class="aiad-chevron" aria-hidden="true">&#8964;</span>';
			html += '</div>';
			html += '<p class="aiad-preview">' + w.preview + '</p>';
			html += '<div class="aiad-detail">' + w.detail;
			html += '<div class="aiad-classroom"><strong>Classroom angle:</strong> ' + w.classroom + '</div>';
			html += '<span class="aiad-tag" style="background:' + cat.tagBg + ';color:' + cat.tagText + '">' + cat.label + '</span>';
			html += '</div></div>';
		});
		gridEl.innerHTML = html;
	}

	function toggleCard(root, idx) {
		var card = root.querySelector('#aiad-card-' + idx);
		if (!card) {
			return;
		}
		var open = card.classList.toggle('open');
		card.setAttribute('aria-expanded', open ? 'true' : 'false');
	}

	function initRoot(root) {
		if (!root || root.getAttribute('data-aiad-bz-ready') === '1') {
			return;
		}
		root.setAttribute('data-aiad-bz-ready', '1');

		root.addEventListener('click', function (e) {
			var filterBtn = e.target.closest('[data-filter]');
			if (filterBtn && root.contains(filterBtn)) {
				activeFilter = filterBtn.getAttribute('data-filter');
				root.querySelectorAll('.aiad-filter-btn').forEach(function (btn) {
					btn.classList.toggle('active', btn === filterBtn);
				});
				renderCards(root);
				return;
			}
			var card = e.target.closest('.aiad-card[data-card-index]');
			if (card && root.contains(card)) {
				toggleCard(root, card.getAttribute('data-card-index'));
			}
		});

		root.addEventListener('keydown', function (e) {
			var card = e.target.closest('.aiad-card[data-card-index]');
			if (!card || !root.contains(card)) {
				return;
			}
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				toggleCard(root, card.getAttribute('data-card-index'));
			}
		});

		renderFilters(root);
		renderCards(root);
	}

	function init() {
		document.querySelectorAll('[data-aiad-buzzwords]').forEach(initRoot);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
