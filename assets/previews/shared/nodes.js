/*
 * Nodes challenges for the self-paced courses.
 *
 * Taken from the Nodes method (Desktop/App/Nodes: answer-routine.ts, distinctions.ts, relationships.ts,
 * stage-ladders.ts, student-answers.ts, solo.ts) and SlideForge's Nodes formats, rebuilt for one learner on a page:
 * no phones, no teacher, feedback straight away. Students build answers rather than pick them.
 *
 *   AIADNodes.mount(el, spec)   spec.type is one of:
 *     apart  · Keep them apart: two confusable ideas, the one test that separates them, then sort statements
 *     join   · Join it up: put the links of an answer in order (point → because → so → in this case)
 *     three  · Three answers: pick the strongest of A, B and C, then say why it is strongest
 *     step   · Which step broke?: tap the step of the answer routine where a wrong answer failed
 *     ladder · Missing-link ladder: attempt, take a rung only if needed, try again with the help hidden, then a new case
 *     newcase· Same link, new case: the same reasoning on a near case and a far case, unaided
 *     size   · Size the answer: from the command word and the marks, what a full answer needs
 */
(function (w) {
	'use strict';
	var d = w.document;

	/* The links in Nodes' own colours (Okabe-Ito, colour-blind safe). */
	var LINK = { U: '#0072b2', A: '#009e73', C: '#e69f00', X: '#d55e00', J: '#cc79a7' };

	/* The answer routine, from Nodes (src/data/answer-routine.ts). */
	var ROUTINE = [
		['Decode', 'Command word. Marks. Scenario. What does the question really want?'],
		['Retrieve', 'Key terms first. Which two ideas must I keep apart?'],
		['Reason', 'Build the chain: point → because → so → in this case.'],
		['Refine', 'Use the technical term where accuracy depends on it.'],
		['Review', 'Units, endpoints and zero. Does it answer everything asked?']
	];

	/* The relationship each question needs for credit, from Nodes (src/data/relationships.ts). */
	var REL = {
		meaning: 'Term → precise meaning',
		sequence: 'Process → causal sequence',
		context: 'Concept → context',
		evidence: 'Evidence → explanation',
		comparison: 'Comparison → criterion',
		judgement: 'Claim → evidence → conditions',
		argument: 'Ideas → one argument'
	};

	/* SOLO levels, from Nodes (src/data/solo.ts). */
	var SOLO = ['', 'Prestructural: misses the point', 'Unistructural: one relevant idea', 'Multistructural: several ideas, not connected', 'Relational: ideas connected into a whole', 'Extended abstract: beyond the given, to new cases'];

	var FORMAT = {
		apart: { name: 'Keep them apart', k: 'C', aim: 'Most wrong answers aren’t missing facts: they treat two ideas as one. Learn the one test that tells them apart, then use it.' },
		join: { name: 'Join it up', k: 'A', aim: 'Answers lose marks for the link they leave out, not the facts. Build the chain the question needs.' },
		three: { name: 'Three answers', k: 'C', aim: 'Three students, one question. Spot what makes the strongest answer strong.' },
		step: { name: 'Which step broke?', k: 'X', aim: 'Every answer goes through five steps. Find the one where this answer went wrong.' },
		ladder: { name: 'Missing-link ladder', k: 'J', aim: 'Try first. Take help only when you need it, one rung at a time, then try again with the help hidden. Support isn’t understanding until you can do it without.' },
		newcase: { name: 'Same link, new case', k: 'J', aim: 'If you understand the link, you can use it somewhere new. Try it on a near case, then a far one, without help.' },
		size: { name: 'Size the answer', k: 'U', aim: 'Read the command word and the marks before you write. They tell you how much the answer needs.' }
	};
	var KNAME = { U: 'Understand', A: 'Apply', C: 'Compare and analyse', X: 'Challenge', J: 'Judge and transfer' };

	function esc(s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
	function el(tag, cls, html) { var e = d.createElement(tag); if (cls) e.className = cls; if (html != null) e.innerHTML = html; return e; }
	function btn(cls, html, fn) { var b = el('button', cls, html); b.type = 'button'; if (fn) b.addEventListener('click', fn); return b; }
	function shuffle(a) { a = a.slice(); for (var i = a.length - 1; i > 0; i--) { var j = Math.floor(Math.random() * (i + 1)), t = a[i]; a[i] = a[j]; a[j] = t; } return a; }
	function live(box, html, ok) { box.innerHTML = html; box.className = 'nd-fb' + (ok === true ? ' ok' : ok === false ? ' no' : ''); }
	function q(spec) { return '<p class="nd-q">' + esc(spec.q) + (spec.marks ? ' <span class="nd-marks">[' + spec.marks + ' mark' + (spec.marks > 1 ? 's' : '') + ']</span>' : '') + '</p>'; }
	function chain(names, colours) {
		return '<ol class="nd-chain">' + names.map(function (n, i) { return '<li style="--lk:' + (colours ? colours[i] : LINK.A) + '">' + esc(n) + '</li>'; }).join('') + '</ol>';
	}
	function done(root) {
		root.classList.add('done');
		try { var k = 'aiad-nodes', s = JSON.parse(localStorage.getItem(k) || '{}'); s[location.pathname.split('/').slice(-2, -1)[0] + ':' + root.id] = Date.now(); localStorage.setItem(k, JSON.stringify(s)); } catch (e) { /* storage blocked: fine */ }
	}
	/* A box students must type in before the model answer opens: producing beats recognising. */
	/* Think first, then reveal: no typing. Students form an answer in their head (or say it to a partner), commit to
	   having one, and only then can open the help or the model. */
	function attempt(label, min, onReady) {
		var wrap = el('div', 'nd-try', '<p class="nd-try-l">' + esc(label) + '</p><p class="nd-try-p">Think of your answer first, or say it out loud to a partner.</p>');
		var go = btn('nd-go ghost nd-ready', 'I’ve got my answer', function () { go.disabled = true; go.textContent = 'Answer in mind ✓'; onReady(true, null); });
		wrap.appendChild(go);
		return { wrap: wrap, ta: null };
	}
	function checklist(items) {
		return '<div class="nd-self"><b>Check your own answer. Does it…</b>' + items.map(function (t) { return '<label><input type="checkbox"> ' + esc(t) + '</label>'; }).join('') + '</div>';
	}

	var BUILD = {
		/* Keep them apart ------------------------------------------------------------------------------------- */
		apart: function (root, s) {
			var A = s.a, B = s.b;
			root.appendChild(el('div', 'nd-pair',
				'<div class="nd-def" style="--lk:' + LINK.U + '"><b>' + esc(A[0]) + '</b><span>' + esc(A[1]) + '</span></div>' +
				'<div class="nd-def" style="--lk:' + LINK.X + '"><b>' + esc(B[0]) + '</b><span>' + esc(B[1]) + '</span></div>'));
			root.appendChild(el('p', 'nd-test', '<b>The test</b> ' + esc(s.test)));
			root.appendChild(el('p', 'nd-do', 'Sort each statement. Which idea is it about?'));
			var short = [s.as || A[0], s.bs || B[0]];
			var list = el('ol', 'nd-sort'), picks = [];
			var items = shuffle(s.items.map(function (it, i) { return { t: it[0], a: it[1], why: it[2], i: i }; }));
			items.forEach(function (it, n) {
				var li = el('li', '', '<p>' + esc(it.t) + '</p>');
				var row = el('div', 'nd-opts');
				[['a', short[0], LINK.U], ['both', 'Both', LINK.A], ['b', short[1], LINK.X]].forEach(function (o) {
					if (o[0] === 'both' && !s.both) return;
					var b = btn('nd-opt', esc(o[1]), function () {
						if (li.classList.contains('right')) return;
						row.querySelectorAll('.nd-opt').forEach(function (x) { x.setAttribute('aria-pressed', 'false'); });
						b.setAttribute('aria-pressed', 'true'); picks[n] = o[0]; li.classList.remove('wrong');
						var why = li.querySelector('.nd-why'); if (why) why.remove();
					});
					b.style.setProperty('--lk', o[2]); b.setAttribute('aria-pressed', 'false');
					row.appendChild(b);
				});
				li.appendChild(row); list.appendChild(li);
			});
			root.appendChild(list);
			var fb = el('div', 'nd-fb'); fb.setAttribute('aria-live', 'polite');
			var go = btn('nd-go', 'Check my sorting', function () {
				var right = 0, missing = 0;
				items.forEach(function (it, n) {
					var li = list.children[n];
					if (!picks[n]) { missing++; return; }
					var ok = picks[n] === it.a;
					li.classList.toggle('right', ok); li.classList.toggle('wrong', !ok);
					if (ok) right++;
					if (!li.querySelector('.nd-why') && it.why) li.appendChild(el('p', 'nd-why', (ok ? '✓ ' : '✗ ') + esc(it.why)));
				});
				if (missing) { live(fb, 'Sort every statement first: ' + missing + ' still to go.'); return; }
				if (right === items.length) { live(fb, '<b>All ' + right + ' sorted.</b> The test did the work: ' + esc(s.test), true); go.hidden = true; done(root); }
				else live(fb, '<b>' + right + ' of ' + items.length + ' right.</b> Use the test on the red ones, then check again.', false);
			});
			root.appendChild(go); root.appendChild(fb);
		},

		/* Join it up ------------------------------------------------------------------------------------------ */
		join: function (root, s) {
			root.insertAdjacentHTML('beforeend', q(s));
			if (s.size) root.appendChild(el('p', 'nd-pill', esc(s.size)));
			root.appendChild(el('p', 'nd-do', 'Tap the cards in order to build the answer.' + (s.decoy ? ' One card is true but doesn’t join: leave it out.' : '') + ' Tap a placed card to take it back.'));
			var cols = s.links.map(function (_, i) { return i === 0 ? LINK.U : i === s.links.length - 1 ? LINK.C : LINK.A; });
			var slots = el('ol', 'nd-slots');
			s.links.forEach(function (l, i) {
				var li = el('li', '', '<span class="nd-slot-n">' + esc(l[0]) + '</span><span class="nd-slot-t">…</span>');
				li.style.setProperty('--lk', cols[i]);
				li.addEventListener('click', function () { if (li._card && !root.classList.contains('done')) { li._card.hidden = false; li._card = null; li.querySelector('.nd-slot-t').textContent = '…'; li.classList.remove('full', 'right', 'wrong'); } });
				slots.appendChild(li);
			});
			var cards = s.links.map(function (l, i) { return { t: l[1], i: i }; });
			if (s.decoy) cards.push({ t: s.decoy[0], i: -1 });
			var pool = el('div', 'nd-cards');
			shuffle(cards).forEach(function (c) {
				var b = btn('nd-card', esc(c.t), function () {
					var free = [].filter.call(slots.children, function (x) { return !x._card; })[0];
					if (!free) return;
					free._card = b; b.hidden = true; b._i = c.i;
					free.querySelector('.nd-slot-t').textContent = c.t; free.classList.add('full');
				});
				pool.appendChild(b);
			});
			root.appendChild(slots); root.appendChild(pool);
			var fb = el('div', 'nd-fb'); fb.setAttribute('aria-live', 'polite');
			var go = btn('nd-go', 'Check my chain', function () {
				var all = [].slice.call(slots.children);
				if (all.some(function (x) { return !x._card; })) { live(fb, 'Fill every link first.'); return; }
				var right = 0;
				all.forEach(function (x, i) { var ok = x._card._i === i; x.classList.toggle('right', ok); x.classList.toggle('wrong', !ok); if (ok) right++; });
				if (right === all.length) {
					go.hidden = true; pool.hidden = true; done(root);
					live(fb, '<b>Joined up.</b> Read it as one answer: <q>' + esc(s.links.map(function (l) { return l[1]; }).join(' ')) + '</q>' +
						(s.decoy ? '<p><b>The card you left out</b> ' + esc(s.decoy[1]) + '</p>' : '') +
						(s.refused ? '<p><b>Refused without the links</b> ' + esc(s.refused) + '</p>' : ''), true);
				} else {
					var usedDecoy = all.some(function (x) { return x._card._i === -1; });
					live(fb, '<b>' + right + ' of ' + all.length + ' links in place.</b> ' + (usedDecoy ? 'One card in your chain doesn’t join to the others. ' : '') + 'Each link should follow from the one before. Tap a red link to take it back.', false);
				}
			});
			root.appendChild(go); root.appendChild(fb);
		},

		/* Three answers --------------------------------------------------------------------------------------- */
		three: function (root, s) {
			root.insertAdjacentHTML('beforeend', q(s));
			root.appendChild(el('p', 'nd-do', 'Which answer is strongest?'));
			var order = shuffle([0, 1, 2]), L = 'ABC';
			var TAG = ['Weak', 'Stable', 'Excellent'], COL = [LINK.X, LINK.C, LINK.A];
			var grid = el('div', 'nd-three'), picked = false;
			var fb = el('div', 'nd-fb'); fb.setAttribute('aria-live', 'polite');
			order.forEach(function (k, n) {
				var a = s.answers[k];
				var c = btn('nd-ans', '<span class="nd-ans-l">Student ' + L[n] + '</span><span class="nd-ans-t">' + esc(a[0]) + '</span>', function () {
					if (picked) return;
					picked = true;
					grid.querySelectorAll('.nd-ans').forEach(function (x, m) {
						var kk = order[m], aa = s.answers[kk];
						x.disabled = true; x.classList.add('shown'); x.style.setProperty('--lk', COL[kk]);
						x.insertAdjacentHTML('beforeend', '<span class="nd-ans-tag">' + TAG[kk] + ' · SOLO ' + aa[1] + '</span><span class="nd-ans-fb">' + esc(aa[2]) + '</span>');
					});
					c.classList.add('picked');
					var right = k === 2;
					live(fb, right ? '<b>Yes, Student ' + L[n] + '.</b> Now say why it’s the strongest.' : '<b>Not quite.</b> Student ' + L[order.indexOf(2)] + ' is strongest. Read the notes on each answer, then say why.', right);
					why();
				});
				grid.appendChild(c);
			});
			root.appendChild(grid); root.appendChild(fb);
			function letters(t) { return t.replace(/\{([ABC])\}/g, function (_, x) { return L[order.indexOf('ABC'.indexOf(x))]; }); }
			function why() {
				var box = el('div', 'nd-whybox', '<p class="nd-do">Why is the excellent answer the strongest?</p>');
				var opts = shuffle([[s.why || 'Each point is joined to the next with because or so, all the way to the question.', 1], ['It’s the longest answer.', 0], ['It uses the most technical words.', 0]]);
				var f2 = el('div', 'nd-fb');
				opts.forEach(function (o) {
					box.appendChild(btn('nd-opt wide', esc(o[0]), function (e) {
						if (o[1]) { e.currentTarget.classList.add('right'); live(f2, '<b>That’s it.</b> ' + esc(letters(s.lesson || 'Marks follow the links between the points, not the number of points.')) + '<br><span class="nd-solo">SOLO levels: ' + SOLO.slice(1).map(esc).join(' · ') + '</span>', true); done(root); box.querySelectorAll('button').forEach(function (b) { b.disabled = true; }); }
						else { e.currentTarget.classList.add('wrong'); live(f2, o[0].indexOf('longest') > -1 ? 'Length isn’t credited. Student ' + L[order.indexOf(1)] + ' wrote plenty but left the points unconnected.' : 'Technical words help only when they’re joined into an explanation.', false); }
					}));
				});
				box.appendChild(f2); root.appendChild(box);
			}
		},

		/* Which step broke? ----------------------------------------------------------------------------------- */
		step: function (root, s) {
			root.insertAdjacentHTML('beforeend', q(s));
			root.appendChild(el('blockquote', 'nd-wrong', '<span>A student wrote</span>' + esc(s.answer)));
			root.appendChild(el('p', 'nd-do', 'At which step of the answer routine did it go wrong?'));
			var row = el('div', 'nd-steps'), fb = el('div', 'nd-fb'), solved = false;
			fb.setAttribute('aria-live', 'polite');
			ROUTINE.forEach(function (r, i) {
				var b = btn('nd-step', '<b><i>' + (i + 1) + '</i>' + r[0] + '</b><span>' + esc(r[1]) + '</span>', function () {
					if (solved) return;
					if (r[0] === s.step) {
						solved = true; b.classList.add('right'); done(root);
						live(fb, '<b>' + r[0] + '.</b> ' + esc(s.why) + (s.fix ? '<p><b>Repaired</b> <q>' + esc(s.fix) + '</q></p>' : ''), true);
					} else { b.classList.add('wrong'); b.disabled = true; live(fb, esc((s.not && s.not[r[0]]) || 'Not this one. Look for the first step where the answer goes off track.'), false); }
				});
				row.appendChild(b);
			});
			root.appendChild(row); root.appendChild(fb);
		},

		/* Missing-link ladder --------------------------------------------------------------------------------- */
		ladder: function (root, s) {
			root.insertAdjacentHTML('beforeend', q(s));
			if (s.link) root.appendChild(el('p', 'nd-pill', 'The link to make: ' + esc(s.link)));
			var first = attempt('1 · Your answer, no help', 12, function (ok) { help.disabled = false; mine.disabled = !ok; });
			root.appendChild(first.wrap);
			var rungs = [['A question to think with', s.probe], ['A hint', s.hint], ['A model answer', s.model]];
			var ladder = el('ol', 'nd-rungs'), n = 0;
			var bar = el('div', 'nd-bar');
			var help = btn('nd-go ghost', 'I’m stuck: next rung', function () {
				if (n >= rungs.length) return;
				var r = rungs[n];
				var li = el('li', n === 2 ? 'model' : '', '<b>' + r[0] + '</b>' + (n === 2 ? '<q>' + esc(r[1]) + '</q>' : esc(r[1])));
				li.style.setProperty('--w', (60 + n * 20) + '%');
				ladder.appendChild(li); n++;
				help.textContent = n === 1 ? 'Still stuck: a hint' : n === 2 ? 'Show me a model answer' : 'All rungs used';
				help.disabled = n >= rungs.length;
				if (n >= rungs.length) retry();
			});
			var mine = btn('nd-go', 'Done: compare with a model', function () { mine.disabled = true; help.disabled = true; showModel(); });
			mine.disabled = true;
			bar.appendChild(help); bar.appendChild(mine);
			root.appendChild(bar); root.appendChild(ladder);
			var after = el('div', 'nd-after'); root.appendChild(after);
			function showModel() {
				after.insertAdjacentHTML('beforeend', '<div class="nd-model"><b>A model answer</b><q>' + esc(s.model) + '</q></div>' + checklist(s.check || []));
				later();
			}
			function retry() {
				var hide = btn('nd-go', 'Hide the help and try again', function () {
					ladder.hidden = true; hide.remove();
					var t2 = attempt('2 · Again, with the help hidden', 12, function (ok) { cmp.disabled = !ok; });
					var cmp = btn('nd-go', 'Compare with the model', function () { cmp.remove(); ladder.hidden = false; after.insertAdjacentHTML('beforeend', checklist(s.check || [])); later(); });
					cmp.disabled = true;
					after.appendChild(t2.wrap); after.appendChild(cmp);
				});
				mine.hidden = true; bar.appendChild(hide);
			}
			function later() {
				if (!s.later || after.querySelector('.nd-later')) return;
				var box = el('div', 'nd-later', '<b>A new case, no help</b><p class="nd-q">' + esc(s.later.q) + '</p>');
				var t = attempt('3 · Your answer to the new case', 12, function (ok) { show.disabled = !ok; });
				var show = btn('nd-go', 'Show a model answer', function () { show.remove(); box.insertAdjacentHTML('beforeend', '<div class="nd-model"><b>Model</b><q>' + esc(s.later.model) + '</q></div>'); done(root); });
				show.disabled = true;
				box.appendChild(t.wrap); box.appendChild(show); after.appendChild(box);
			}
		},

		/* Same link, new case --------------------------------------------------------------------------------- */
		newcase: function (root, s) {
			root.appendChild(el('p', 'nd-do', esc(s.intro || 'The link you used in this course:')));
			root.insertAdjacentHTML('beforeend', chain(s.chain, s.chain.map(function (_, i) { return i === 0 ? LINK.U : i === s.chain.length - 1 ? LINK.C : LINK.A; })));
			var left = s.cases.length;
			s.cases.forEach(function (c) {
				var box = el('div', 'nd-case', '<span class="nd-case-tag">' + esc(c.tag) + '</span><p class="nd-q">' + esc(c.q) + '</p>');
				var t = attempt('Your answer', 12, function (ok) { show.disabled = !ok; });
				var show = btn('nd-go', 'Show a model answer', function () {
					show.remove();
					box.insertAdjacentHTML('beforeend', '<div class="nd-model"><b>Model</b><q>' + esc(c.model) + '</q></div>' + (c.check ? checklist(c.check) : ''));
					if (--left === 0) done(root);
				});
				show.disabled = true;
				box.appendChild(t.wrap); box.appendChild(show); root.appendChild(box);
			});
		},

		/* Size the answer ------------------------------------------------------------------------------------- */
		size: function (root, s) {
			root.insertAdjacentHTML('beforeend', q(s));
			root.appendChild(el('p', 'nd-do', 'Before you write: what does a full answer need?'));
			var box = el('div', 'nd-choices'), fb = el('div', 'nd-fb'), solved = false;
			fb.setAttribute('aria-live', 'polite');
			s.options.forEach(function (o, i) {
				box.appendChild(btn('nd-opt wide', esc(o), function (e) {
					if (solved) return;
					if (i === s.a) {
						solved = true; e.currentTarget.classList.add('right'); done(root);
						live(fb, '<b>Right.</b> ' + esc(s.why) + (s.shape ? chain(s.shape, s.shape.map(function (_, j) { return j === 0 ? LINK.U : j === s.shape.length - 1 ? LINK.C : LINK.A; })) : '') + (s.model ? '<div class="nd-model"><b>An answer that size</b><q>' + esc(s.model) + '</q></div>' : ''), true);
					} else { e.currentTarget.classList.add('wrong'); e.currentTarget.disabled = true; live(fb, esc((s.not && s.not[i]) || 'Look again at the command word and the marks.'), false); }
				}));
			});
			root.appendChild(box); root.appendChild(fb);
		}
	};

	function mount(root, spec) {
		if (typeof root === 'string') root = d.querySelector(root);
		if (!root || !spec || !BUILD[spec.type] || root.getAttribute('data-nd-ready')) return;
		root.setAttribute('data-nd-ready', '1');
		var f = FORMAT[spec.type], k = spec.k || f.k;
		root.classList.add('nd', 'nd--' + spec.type);
		root.setAttribute('role', 'group');
		root.setAttribute('aria-label', 'Nodes challenge: ' + f.name);
		root.innerHTML = '<header class="nd-h"><span class="nd-brand" title="From the Nodes teaching method">Nodes</span><span class="nd-fmt">' + esc(spec.title || f.name) + '</span>' +
			'<span class="nd-k" style="--lk:' + LINK[k] + '" title="Nodes backbone: ' + KNAME[k] + '"><b>' + k + '</b><i>' + KNAME[k] + '</i></span>' +
			(spec.rel && REL[spec.rel] ? '<span class="nd-rel" title="The relationship the answer must make">' + esc(REL[spec.rel]) + '</span>' : '') + '</header>' +
			'<p class="nd-aim">' + esc(spec.aim || f.aim) + '</p>';
		BUILD[spec.type](root, spec);
	}

	function mountAll(specs) {
		d.querySelectorAll('[data-node]').forEach(function (e) { var s = specs[e.getAttribute('data-node')]; if (s) { if (!e.id) e.id = 'nd-' + e.getAttribute('data-node'); mount(e, s); } });
	}

	w.AIADNodes = { mount: mount, mountAll: mountAll, ROUTINE: ROUTINE, REL: REL, SOLO: SOLO };
})(window);
