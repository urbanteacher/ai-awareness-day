/**
 * Misinformation Detector — interactive article (shortcode [aiad_misinformation_detector])
 */
(function () {
	'use strict';

	var ITEMS = [
		{
			type: 'headline',
			typeLabel: 'BBC / Full Fact headline',
			claim:
				'“Deepfake doctors” are spreading health misinformation on social media, with AI-generated videos impersonating real clinicians.',
			sourceLabel: 'Reported by BBC News and fact-checked by Full Fact',
			answer: 'true',
			explanation:
				'This reflects a documented pattern: AI-generated “doctor” videos have circulated on platforms, sometimes copying likeness and voice without consent. Full Fact and BBC coverage have traced how convincing deepfakes can spread harmful health claims. The headline is alarming but grounded in real reporting—not a fabricated statistic.',
			verify: [
				'BBC News search for “deepfake doctor” or “AI doctor video”',
				'Full Fact archive (fullfact.org) for health deepfake claims',
				'WHO or NHS guidance on spotting health misinformation online',
			],
			lesson:
				'A reputable news headline can still be shocking. Check the original article, not only a screenshot or repost.',
		},
		{
			type: 'ai',
			typeLabel: 'AI-generated output',
			claim:
				'A new study proves AI tutoring raises student grades by 23% across all UK secondary schools.',
			sourceLabel: 'Shared as a ChatGPT summary — no linked study named',
			answer: 'false',
			explanation:
				'Large language models often invent plausible statistics, study names, and “findings.” There is no single UK-wide study proving a universal 23% gain from AI tutoring. Without a named peer-reviewed paper, dataset, or official report, treat round percentages in AI text as unreliable until verified.',
			verify: [
				'Google Scholar or your school library database for the exact claim',
				'Department for Education publications and press releases',
				'Ask the AI: “What is the source? Provide title, authors, year, and DOI.”',
			],
			lesson:
				'AI “studies” sound authoritative. Always demand a citable source before sharing numbers with colleagues or students.',
		},
		{
			type: 'viral',
			typeLabel: 'Viral WhatsApp / social post',
			claim:
				'URGENT: The UK government has banned ChatGPT in all schools from next term — share before they delete this.',
			sourceLabel: 'Forwarded message — no link to GOV.UK or DfE',
			answer: 'false',
			explanation:
				'Major policy changes are published on GOV.UK and covered widely by established news outlets. A blanket “ban” on ChatGPT in all schools would appear on official channels. Viral posts use urgency and censorship tropes (“share before deleted”) to bypass scrutiny.',
			verify: [
				'GOV.UK and Department for Education news pages',
				'BBC News or reputable UK education press',
				'Your school’s actual AI policy from leadership — not a chain message',
			],
			lesson:
				'Urgency plus “they will delete this” is a classic misinformation pattern. Pause before forwarding.',
		},
		{
			type: 'headline',
			typeLabel: 'The Guardian headline',
			claim:
				'Universities logged 7,000 AI cheating cases in one year — proof that students can no longer be trusted to write their own work.',
			sourceLabel: 'The Guardian — headline shared without article context',
			answer: 'mixed',
			explanation:
				'Investigations have reported thousands of AI-related academic integrity cases, but the number alone is not “proof” every student cheats. Denominator matters: how many students and institutions? What counts as “AI cheating”? Policies and detection vary. The story is real; the leap to “students can’t be trusted” is an opinion, not a fact.',
			verify: [
				'Read the full Guardian article for definitions and methodology',
				'QAA or university regulator statements on academic integrity',
				'Your own institution’s plagiarism and AI policy',
			],
			lesson:
				'Real data can be framed to push a narrative. Ask: compared to what, and who is interpreting the number?',
		},
		{
			type: 'ai',
			typeLabel: 'AI-generated output',
			claim:
				'Ofcom now requires every UK school to register all AI tools with a central government portal by September.',
			sourceLabel: 'Pasted from an AI assistant — no Ofcom document cited',
			answer: 'false',
			explanation:
				'Regulators like Ofcom publish consultations and rules on official sites. A sweeping mandatory registration scheme for “every AI tool” in “every school” would be major news and legally documented. AI models frequently hallucinate regulator names, deadlines, and obligations.',
			verify: [
				'Ofcom.gov.uk news and publications search',
				'Department for Education digital and AI guidance',
				'ICO (data protection) guidance if the claim mentions pupil data',
			],
			lesson:
				'Fake bureaucracy is common in AI outputs. Verify on the regulator’s own website, not in the chat window.',
		},
		{
			type: 'headline',
			typeLabel: 'BBC / Nature Medicine report',
			claim:
				'AI can predict more than 130 diseases from one blood test — scientists say hospitals will replace doctors within five years.',
			sourceLabel: 'BBC report on research — viral caption exaggerates findings',
			answer: 'mixed',
			explanation:
				'Research on AI and health data can be genuine and peer-reviewed, but viral sharing often strips nuance. Prediction tools may flag risk patterns; they do not mean hospitals will replace clinicians soon. “130 diseases” may come from a study but context (accuracy, clinical use, regulation) is essential.',
			verify: [
				'The original BBC article and linked journal paper',
				'NHS or medical college statements on AI in clinical practice',
				'Nature Medicine or PubMed for the study’s limitations section',
			],
			lesson:
				'Science headlines get amplified into hype. Separate what the study measured from what social posts claim it means.',
		},
	];

	var VERDICTS = [
		{ key: 'true', label: 'Likely true' },
		{ key: 'false', label: 'Likely false' },
		{ key: 'mixed', label: 'Needs context' },
	];

	var HABITS = [
		'Pause on urgency — “share before deleted” is a red flag.',
		'Name the source type: newsroom, AI output, or anonymous viral post.',
		'Log where you would check before you judge.',
		'Separate the number or headline from the story someone wants you to believe.',
		'Teach students the same habit: verify, then verdict.',
	];

	var INSIGHTS = [
		{
			title: 'AI hallucinations',
			text: 'Chatbots invent studies, laws, and statistics. Numbers without citations are not evidence.',
		},
		{
			title: 'Real headlines, stretched meaning',
			text: 'BBC and Guardian stories can be real while captions or WhatsApp forwards add false certainty.',
		},
		{
			title: 'Viral posts mimic news',
			text: 'WhatsApp chains borrow the tone of journalism without links, bylines, or accountability.',
		},
	];

	var state = {
		index: 0,
		points: 0,
		correct: 0,
		sourcesChecked: 0,
		started: false,
	};

	function el(id) {
		return document.getElementById(id);
	}

	function countFilledSources() {
		var n = 0;
		for (var i = 1; i <= 3; i++) {
			var inp = el('aiad-mi-src-' + i);
			if (inp && inp.value.trim() !== '') {
				n++;
			}
		}
		return n;
	}

	function badgeClass(answer) {
		if (answer === 'true') {
			return 'mi-reveal--true';
		}
		if (answer === 'false') {
			return 'mi-reveal--false';
		}
		return 'mi-reveal--mixed';
	}

	function answerLabel(key) {
		if (key === 'true') {
			return 'Likely true';
		}
		if (key === 'false') {
			return 'Likely false';
		}
		return 'Needs context';
	}

	function confidenceBadge(points) {
		if (points >= 66) {
			return { label: 'Media literacy expert', cls: 'mi-conf--expert' };
		}
		if (points >= 46) {
			return { label: 'Discerning reader', cls: 'mi-conf--strong' };
		}
		if (points >= 26) {
			return { label: 'Getting there', cls: 'mi-conf--mid' };
		}
		return { label: 'Needs practice', cls: 'mi-conf--low' };
	}

	function renderProgress() {
		var bar = el('aiad-mi-progress-bar');
		var lbl = el('aiad-mi-progress-lbl');
		if (!bar) {
			return;
		}
		var pct = ((state.index + 1) / ITEMS.length) * 100;
		bar.style.width = pct + '%';
		if (lbl) {
			lbl.textContent = 'Item ' + (state.index + 1) + ' of ' + ITEMS.length;
		}
	}

	function renderItem() {
		var item = ITEMS[state.index];
		var pill = el('aiad-mi-type-pill');
		var claim = el('aiad-mi-claim');
		var source = el('aiad-mi-source');
		var reveal = el('aiad-mi-reveal');
		var nextBtn = el('aiad-mi-next');
		var verdictWrap = el('aiad-mi-verdicts');

		if (pill) {
			pill.textContent = item.typeLabel;
			pill.className = 'mi-type-pill mi-type-pill--' + item.type;
		}
		if (claim) {
			claim.textContent = item.claim;
		}
		if (source) {
			source.textContent = item.sourceLabel;
		}

		var hintEl = el('aiad-mi-sources-hint');
		if (hintEl) {
			hintEl.textContent =
				'Name at least one before choosing a verdict (two or more earns bonus points).';
			hintEl.style.color = '';
		}

		for (var i = 1; i <= 3; i++) {
			var inp = el('aiad-mi-src-' + i);
			if (inp) {
				inp.value = '';
				inp.disabled = false;
			}
		}

		if (reveal) {
			reveal.hidden = true;
			reveal.className = 'mi-reveal';
		}
		if (nextBtn) {
			nextBtn.hidden = true;
		}
		if (verdictWrap) {
			var btns = verdictWrap.querySelectorAll('.mi-verdict-btn');
			btns.forEach(function (b) {
				b.disabled = false;
				b.classList.remove('mi-verdict-btn--picked');
			});
		}

		renderProgress();
	}

	function showReveal(item, picked) {
		var reveal = el('aiad-mi-reveal');
		var nextBtn = el('aiad-mi-next');
		if (!reveal) {
			return;
		}

		var correct = picked === item.answer;
		if (correct) {
			state.correct++;
			state.points += 10;
		}

		var filled = countFilledSources();
		if (filled >= 2) {
			state.points += 5;
			state.sourcesChecked++;
		}

		reveal.hidden = false;
		reveal.className = 'mi-reveal ' + badgeClass(item.answer);

		var title = el('aiad-mi-reveal-title');
		if (title) {
			title.textContent = 'Verdict: ' + answerLabel(item.answer);
		}

		var expl = el('aiad-mi-reveal-expl');
		if (expl) {
			expl.textContent = item.explanation;
		}

		var list = el('aiad-mi-reveal-sources');
		if (list) {
			list.innerHTML = '';
			item.verify.forEach(function (s) {
				var li = document.createElement('li');
				li.textContent = s;
				list.appendChild(li);
			});
		}

		var lesson = el('aiad-mi-reveal-lesson');
		if (lesson) {
			lesson.textContent = item.lesson;
		}

		var fb = el('aiad-mi-reveal-fb');
		if (fb) {
			fb.textContent = correct
				? '✓ Your verdict matched. +' + (10 + (filled >= 2 ? 5 : 0)) + ' points.'
				: 'Not quite — the best verdict was “' +
				  answerLabel(item.answer) +
				  '”. +' +
				  (filled >= 2 ? 5 : 0) +
				  ' points for checking sources.';
		}

		for (var i = 1; i <= 3; i++) {
			var inp = el('aiad-mi-src-' + i);
			if (inp) {
				inp.disabled = true;
			}
		}

		if (nextBtn) {
			nextBtn.hidden = false;
			nextBtn.textContent =
				state.index < ITEMS.length - 1 ? 'Next item →' : 'See your results';
		}
	}

	function renderResults() {
		var quiz = el('aiad-mi-quiz');
		var results = el('aiad-mi-results');
		if (quiz) {
			quiz.hidden = true;
		}
		if (results) {
			results.hidden = false;
		}

		var conf = confidenceBadge(state.points);
		var badge = el('aiad-mi-conf-badge');
		if (badge) {
			badge.textContent = conf.label;
			badge.className = 'mi-conf-badge ' + conf.cls;
		}

		var pts = el('aiad-mi-stat-points');
		if (pts) {
			pts.textContent = String(state.points);
		}
		var cor = el('aiad-mi-stat-correct');
		if (cor) {
			cor.textContent = state.correct + ' / ' + ITEMS.length;
		}
		var chk = el('aiad-mi-stat-sources');
		if (chk) {
			chk.textContent = String(state.sourcesChecked);
		}
	}

	function onVerdict(key) {
		if (!state.started) {
			return;
		}
		if (countFilledSources() < 1) {
			var hint = el('aiad-mi-sources-hint');
			if (hint) {
				hint.textContent = 'Please name at least one source you would check before choosing a verdict.';
				hint.style.color = '#d85a30';
			}
			var first = el('aiad-mi-src-1');
			if (first) {
				first.focus();
			}
			return;
		}
		var item = ITEMS[state.index];
		var verdictWrap = el('aiad-mi-verdicts');
		if (verdictWrap) {
			verdictWrap.querySelectorAll('.mi-verdict-btn').forEach(function (b) {
				b.disabled = true;
				b.classList.toggle('mi-verdict-btn--picked', b.getAttribute('data-verdict') === key);
			});
		}
		showReveal(item, key);
	}

	function onNext() {
		if (state.index < ITEMS.length - 1) {
			state.index++;
			renderItem();
			return;
		}
		renderResults();
	}

	function start() {
		state.index = 0;
		state.points = 0;
		state.correct = 0;
		state.sourcesChecked = 0;
		state.started = true;

		var intro = el('aiad-mi-intro');
		var quiz = el('aiad-mi-quiz');
		var results = el('aiad-mi-results');
		if (intro) {
			intro.hidden = true;
		}
		if (quiz) {
			quiz.hidden = false;
		}
		if (results) {
			results.hidden = true;
		}
		renderItem();
	}

	function bind() {
		var root = el('aiad-misinfo');
		if (!root || root.getAttribute('data-mi-bound') === '1') {
			return;
		}
		root.setAttribute('data-mi-bound', '1');

		var startBtn = el('aiad-mi-start');
		if (startBtn) {
			startBtn.addEventListener('click', start);
		}

		var verdictWrap = el('aiad-mi-verdicts');
		if (verdictWrap) {
			verdictWrap.querySelectorAll('.mi-verdict-btn').forEach(function (btn) {
				btn.addEventListener('click', function () {
					onVerdict(btn.getAttribute('data-verdict'));
				});
			});
		}

		var nextBtn = el('aiad-mi-next');
		if (nextBtn) {
			nextBtn.addEventListener('click', onNext);
		}

		var retry = el('aiad-mi-retry');
		if (retry) {
			retry.addEventListener('click', function () {
				state.started = false;
				var intro = el('aiad-mi-intro');
				var quiz = el('aiad-mi-quiz');
				var results = el('aiad-mi-results');
				if (intro) {
					intro.hidden = false;
				}
				if (quiz) {
					quiz.hidden = true;
				}
				if (results) {
					results.hidden = true;
				}
			});
		}
	}

	function init() {
		bind();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
