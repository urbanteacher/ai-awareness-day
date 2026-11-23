/*
 * AIAD animated flow diagrams, shared by the story pages in assets/previews/.
 *
 * AIADFlow.mount(figureElement, spec) draws an SVG flowchart: Play / Pause, Next step and Start again, with a dot
 * travelling along each arrow, the current box outlined in orange and a caption under the diagram for each step.
 *
 * spec = {
 *   title: 'Heading', w: 340, h: 200,
 *   nodes: [[id, 'label (\n for a new line)', x, y, kind]],   kind: m process, h person, io in/out, x tool or
 *                                                             optional, s store, bad danger, ok safe
 *   edges: [[from, to, {l: 'label', d: dashed, c: curve, id: 'unique key'}]],
 *   zones: [[x, y, w, h, 'label']],                          dashed areas drawn behind the boxes
 *   start: ['id'],                                           boxes lit before the first step
 *   steps: [{go: ['from>to' or edge id], say: 'caption'}],
 *   end: 'caption once the flow has played'
 * }
 * Colours come from the section's --c and --cd custom properties (the campaign theme of the chapter).
 */
(function (w) {
	'use strict';
	var reduce = !!(w.matchMedia && w.matchMedia('(prefers-reduced-motion: reduce)').matches);
	var uid = 0;

	function esc(s) { return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;'); }

	function boxes(spec) {
		var N = {};
		spec.nodes.forEach(function (n) {
			var lines = String(n[1]).split('\n'), longest = 0;
			lines.forEach(function (l) { longest = Math.max(longest, l.length); });
			if (n[4] === 'h') longest += 2;
			N[n[0]] = { id: n[0], lines: lines, x: n[2], y: n[3], k: n[4] || 'm', w: Math.max(42, longest * 6.6 + 18), h: 12 + lines.length * 13 };
		});
		return N;
	}

	function clip(cx, cy, dx, dy, n) {
		var ax = Math.abs(dx) || 1e-9, ay = Math.abs(dy) || 1e-9, t = Math.min(n.w / 2 / ax, n.h / 2 / ay);
		return [cx + dx * t, cy + dy * t];
	}

	function draw(spec, N, mid) {
		var out = '';
		(spec.zones || []).forEach(function (z) {
			out += '<g class="ff-zone"><rect x="' + z[0] + '" y="' + z[1] + '" width="' + z[2] + '" height="' + z[3] + '" rx="4"/><text x="' + (z[0] + 6) + '" y="' + (z[1] + 11) + '">' + esc(z[4]) + '</text></g>';
		});
		spec.edges.forEach(function (e, i) {
			var a = N[e[0]], b = N[e[1]], o = e[2] || {}, dx = b.x - a.x, dy = b.y - a.y, L = Math.hypot(dx, dy) || 1, c = o.c || 0;
			var mx = (a.x + b.x) / 2 + (-dy / L) * c, my = (a.y + b.y) / 2 + (dx / L) * c;
			var s = clip(a.x, a.y, mx - a.x, my - a.y, a), t = clip(b.x, b.y, mx - b.x, my - b.y, b);
			t = [t[0] + (mx - t[0]) * 0.04, t[1] + (my - t[1]) * 0.04];
			out += '<g class="ff-e" data-e="' + i + '"><path d="M' + s[0].toFixed(1) + ' ' + s[1].toFixed(1) + (c ? ' Q' + mx.toFixed(1) + ' ' + my.toFixed(1) + ' ' : ' L') + t[0].toFixed(1) + ' ' + t[1].toFixed(1) + '"' + (o.d ? ' stroke-dasharray="4 4"' : '') + ' marker-end="url(#' + mid + ')"/>';
			if (o.l) {
				var lx = c ? (s[0] + 2 * mx + t[0]) / 4 : (s[0] + t[0]) / 2, ly = c ? (s[1] + 2 * my + t[1]) / 4 : (s[1] + t[1]) / 2;
				var vert = Math.abs(t[0] - s[0]) < Math.abs(t[1] - s[1]) * 0.5;
				var y = vert ? ly + 3 : (c && my > (s[1] + t[1]) / 2 ? ly + 13 : ly - 6);
				var left = vert && c && mx < (s[0] + t[0]) / 2;
				out += '<text class="ff-l" x="' + (vert ? (left ? lx - 6 : lx + 6) : lx).toFixed(1) + '" y="' + y.toFixed(1) + '" text-anchor="' + (vert ? (left ? 'end' : 'start') : 'middle') + '">' + esc(o.l) + '</text>';
			}
			out += '</g>';
		});
		Object.keys(N).forEach(function (k) {
			var n = N[k], x = n.x - n.w / 2, y = n.y - n.h / 2, round = n.k === 'io' || n.k === 'h' ? n.h / 2 : 0;
			out += '<g class="ff-n ff-' + n.k + '" data-n="' + esc(k) + '"><rect x="' + x.toFixed(1) + '" y="' + y.toFixed(1) + '" width="' + n.w.toFixed(1) + '" height="' + n.h + '" rx="' + round + '"/>';
			if (n.k === 's') out += '<path class="ff-lid" d="M' + (x + 3).toFixed(1) + ' ' + (y + 5) + 'H' + (x + n.w - 3).toFixed(1) + '"/>';
			n.lines.forEach(function (l, j) {
				out += '<text x="' + n.x + '" y="' + (n.y + 4 + (j - (n.lines.length - 1) / 2) * 13).toFixed(1) + '" text-anchor="middle">' + (j === 0 && n.k === 'h' ? '☺ ' : '') + esc(l) + '</text>';
			});
			out += '</g>';
		});
		return out;
	}

	function mount(fig, spec) {
		var mid = 'ffArrow' + (++uid), N = boxes(spec), W = spec.w || 340, H = spec.h || 200;
		/* Fit the frame to the boxes: a box sized by a long label must never spill past the edge and be clipped. */
		var X0 = 0, Y0 = 0, PAD = 6;
		Object.keys(N).forEach(function (k) { var n = N[k];
			X0 = Math.min(X0, n.x - n.w / 2 - PAD); Y0 = Math.min(Y0, n.y - n.h / 2 - PAD);
			W = Math.max(W, n.x + n.w / 2 + PAD); H = Math.max(H, n.y + n.h / 2 + PAD); });
		W -= X0; H -= Y0;
		var keyOf = spec.edges.map(function (e) { return (e[2] && e[2].id) || e[0] + '>' + e[1]; });
		var steps = (spec.steps || []).map(function (s) {
			return { go: s.go.map(function (k) { var i = keyOf.indexOf(k); if (i < 0) throw new Error('AIADFlow: no edge ' + k + ' in ' + spec.title); return i; }), say: s.say };
		});
		var start = spec.start || [spec.nodes[0][0]];
		fig.classList.add('ff');
		var P = spec.predict;
		fig.innerHTML = (spec.title ? '<figcaption class="ff-title"><span class="eyebrow">Animated flow</span>' + esc(spec.title) + '</figcaption>' : '') +
			'<svg viewBox="' + X0.toFixed(1) + ' ' + Y0.toFixed(1) + ' ' + W.toFixed(1) + ' ' + H.toFixed(1) + '" role="img" aria-label="' + esc(spec.title || 'Flow diagram') + '"><defs><marker id="' + mid + '" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="5.5" markerHeight="5.5" orient="auto-start-reverse"><path d="M0 0L10 5L0 10z"/></marker></defs>' +
			draw(spec, N, mid) + '<circle class="ff-dot" r="5.5" cx="-999" cy="-999"/></svg>' +
			(P ? '<div class="ff-pred"><p class="ff-pred-k">Predict from the diagram</p><p class="ff-pred-hint">Use the boxes and arrows above to work it out.</p><p class="ff-pred-q">' + P.q + '</p><div class="ff-pred-opts" role="group" aria-label="Your prediction">' +
				P.options.map(function (o, i) { return '<button type="button" data-i="' + i + '">' + o + '</button>'; }).join('') +
				'</div><p class="ff-pred-res" aria-live="polite"></p><button type="button" class="ff-pred-skip">Skip and just watch</button></div>' : '') +
			'<p class="ff-say" aria-live="polite">Press Play to watch it, or Next step to go at your own pace.</p>' +
			'<div class="ff-ctl"><button type="button" data-a="play" class="pri">▶ Play</button><button type="button" data-a="build">Next step</button><button type="button" data-a="reset">Start again</button><button type="button" data-a="puzzle" class="ff-pzb">🧩 Your turn: order the steps</button><span class="ff-count"></span></div>' +
			'<div class="ff-pz" hidden><ol class="ff-pz-done"></ol><p class="ff-pz-k">Tap the step that comes next:</p><div class="ff-pz-cards" role="group" aria-label="Steps to put in order"></div></div>';
		var svg = fig.querySelector('svg'), dot = fig.querySelector('.ff-dot'), say = fig.querySelector('.ff-say');
		var bPlay = fig.querySelector('[data-a=play]'), bBuild = fig.querySelector('[data-a=build]'), bPz = fig.querySelector('[data-a=puzzle]'), bReset = fig.querySelector('[data-a=reset]'), cnt = fig.querySelector('.ff-count');
		var pz = fig.querySelector('.ff-pz'), pzDone = pz.querySelector('.ff-pz-done'), pzCards = pz.querySelector('.ff-pz-cards'), pzK = pz.querySelector('.ff-pz-k');
		var run = 0, pos = -1, pzState = null;
		/* Steps that may honestly come in either order, e.g. two branches: [[2, 3]] */
		var groups = spec.anyOrder || [];

		function q(sel) { return svg.querySelector(sel); }
		function node(id, on) { var g = q('.ff-n[data-n="' + id + '"]'); if (g) { g.classList.remove('ghost'); if (on) g.classList.add('on', 'cur'); } }
		function edge(i, cls) { var g = q('.ff-e[data-e="' + i + '"]'); if (g) { g.classList.remove('ghost', 'live', 'done'); if (cls) g.classList.add(cls); } }
		function hideDot() { dot.setAttribute('cx', -999); dot.setAttribute('cy', -999); }
		function reset(ghost) {
			svg.querySelectorAll('.ff-e,.ff-n').forEach(function (g) { g.classList.remove('live', 'done', 'on', 'cur'); g.classList.toggle('ghost', !!ghost); });
			hideDot();
		}
		function wait(ms) { return new Promise(function (r) { setTimeout(r, reduce ? Math.min(ms, 400) : ms); }); }
		function travel(i, ms, r) {
			return new Promise(function (res) {
				var p = q('.ff-e[data-e="' + i + '"] path'), L = p ? p.getTotalLength() : 0, t0 = null;
				if (reduce || !L) { res(); return; }
				var done = false, guard = setTimeout(function () { done = true; res(); }, ms + 400);
				function f(t) {
					if (done) return;
					if (r !== run) { done = true; clearTimeout(guard); res(); return; }
					if (!t0) t0 = t;
					var k = Math.min(1, (t - t0) / ms), pt = p.getPointAtLength(L * k);
					dot.setAttribute('cx', pt.x); dot.setAttribute('cy', pt.y);
					if (k < 1) w.requestAnimationFrame(f); else { done = true; clearTimeout(guard); res(); }
				}
				w.requestAnimationFrame(f);
			});
		}
		/* A puzzle card must not give its place away: drop leading order words ("Part 2:", "Fix 1, RAG:", "Step 3",
		   "First,", "Finally,") from the card. The full caption appears once the card is placed. */
		function noOrder(t) {
			var out = String(t)
				.replace(/^\s*(Part|Chapter|Step|Stage|Fix|Limit|Lane|Round)\s*\d+\s*[:,.]\s*/i, '')
				.replace(/^\s*(First|Then|Next|After that|Afterwards|Finally|Last|Lastly|At last|To finish|Early on)\s*,?\s+/i, '')
				.replace(/\s*\((chapters?|parts?)\s[^)]*\)/gi, '');
			return out.charAt(0).toUpperCase() + out.slice(1);
		}
		function caption(k) { return glyphs(steps[k].say); }

		/* Icons in the text: words in a caption that name a box get a small matching shape, linked to the box. */
		var STOP = ['your', 'with', 'from', 'that', 'this', 'next', 'done', 'yes', 'start', 'agents', 'model', 'answer', 'action', 'think', 'centres'];
		var labels = spec.nodes.map(function (n) {
			function clean(s) { return s.replace(/\([^)]*\)/g, '').replace(/[☺“”"+=:]/g, '').replace(/\s+/g, ' ').trim(); }
			var lines = String(n[1]).split('\n').map(clean), full = clean(String(n[1]).split('\n').join(' '));
			var tries = [full].concat(lines.filter(function (t) { return t.length >= 5 && t !== full; }).sort(function (a, b) { return b.length - a.length; }));
			return { id: n[0], k: n[4] || 'm', tries: tries.filter(function (t, i) { return t.length >= 4 && STOP.indexOf(t.toLowerCase()) < 0 && tries.indexOf(t) === i; }) };
		}).filter(function (l) { return l.tries.length; });
		function rx(t, cs) { return new RegExp('(^|[^\\w>])(' + t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')(?![\\w<])', cs ? '' : 'i'); }
		function glyphs(text) {
			var used = {};
			labels.slice().sort(function (a, b) { return b.tries[0].length - a.tries[0].length; }).forEach(function (l) {
				for (var i = 0; i < l.tries.length; i++) {
					var m = rx(l.tries[i]), parts = text.split(/(<[^>]+>)/), hit = false;
					for (var j = 0; j < parts.length && !hit; j += 2) {
						if (m.test(parts[j]) && !used[l.tries[i].toLowerCase()]) {
							parts[j] = parts[j].replace(m, function (all, pre, word) { return pre + '<span class="gw" data-n="' + l.id + '"><i class="g g-' + l.k + '" aria-hidden="true"></i>' + word + '</span>'; });
							used[l.tries[i].toLowerCase()] = hit = true;
						}
					}
					if (hit) { text = parts.join(''); break; }
				}
			});
			return text;
		}
		function hl(e, on) {
			var g = e.target.closest && e.target.closest('.gw');
			if (!g) return;
			var n = q('.ff-n[data-n="' + g.getAttribute('data-n') + '"]');
			if (n) n.classList.toggle('hl', on);
		}
		fig.addEventListener('mouseover', function (e) { hl(e, true); });
		fig.addEventListener('mouseout', function (e) { hl(e, false); });
		/* the same icons in this chapter's own text, linked to this diagram */
		(function () {
			var ch = fig.closest('.chapter');
			if (!ch || spec.noText) return;
			var paras = [].slice.call(ch.querySelectorAll('.story p, .really p, .really li'));
			var done = {}, words = {};
			labels.forEach(function (l) {
				for (var i = 0; i < l.tries.length && !done[l.id]; i++) {
					if (words[l.tries[i].toLowerCase()]) continue;
					for (var j = 0; j < paras.length && !done[l.id]; j++) {
						var p = paras[j];
						if (p.querySelector('.gw[data-n="' + l.id + '"]')) { done[l.id] = 1; break; }
						var walker = w.document.createTreeWalker(p, NodeFilter.SHOW_TEXT, null), t, m = rx(l.tries[i], true);
						while ((t = walker.nextNode())) {
							if (t.parentNode.closest('a, .gw')) continue;
							var mm = t.nodeValue.match(m);
							if (!mm) continue;
							var at = mm.index + mm[1].length, word = t.nodeValue.substr(at, mm[2].length);
							var after = t.splitText(at); after.nodeValue = after.nodeValue.slice(word.length);
							var span = w.document.createElement('span');
							span.className = 'gw'; span.setAttribute('data-n', l.id);
							span.innerHTML = '<i class="g g-' + l.k + '" aria-hidden="true"></i>';
							span.appendChild(w.document.createTextNode(word));
							after.parentNode.insertBefore(span, after);
							span.addEventListener('mouseenter', function (id) { return function () { var n = q('.ff-n[data-n="' + id + '"]'); if (n) n.classList.add('hl'); }; }(l.id));
							span.addEventListener('mouseleave', function (id) { return function () { var n = q('.ff-n[data-n="' + id + '"]'); if (n) n.classList.remove('hl'); }; }(l.id));
							done[l.id] = 1; words[l.tries[i].toLowerCase()] = 1;
							break;
						}
					}
				}
			});
		})();
		/* Controls, flowchart style: Play / Pause, Next step, Start again, and a step counter.
		   Everything stays visible; the current box gets an orange outline and a dot travels along each arrow. */
		var playing = false, busy = false, INTRO = 'Press Play to watch it, or Next step to go at your own pace.';
		function clearCur() { svg.querySelectorAll('.ff-n.cur').forEach(function (g) { g.classList.remove('cur'); }); }
		function count() { cnt.textContent = pos < 0 ? 'Step 0 of ' + steps.length : 'Step ' + Math.min(pos, steps.length) + ' of ' + steps.length; }
		function controls(state) {
			if (state !== 'puzzle') { pz.hidden = true; pzState = null; bPz.textContent = '🧩 Your turn: order the steps'; bPz.classList.remove('pri'); }
			bPlay.textContent = playing ? '❚❚ Pause' : (pos >= steps.length ? '▶ Play again' : '▶ Play');
			bBuild.disabled = busy && !playing || pos >= steps.length;
			bPz.disabled = playing;
			count();
		}
		function begin(ghost) { reset(!!ghost); clearCur(); pos = 0; start.forEach(function (s) { node(s, true); }); }
		function finish() {
			svg.querySelectorAll('.ff-e.live').forEach(function (g) { g.classList.remove('live'); g.classList.add('done'); });
			hideDot();
			say.innerHTML = '<b>' + esc(spec.end || 'That’s the whole flow.') + '</b>';
			verdict();
			if (spec.onEnd) spec.onEnd();
		}
		function doStep(k, r, ms) {
			var st = steps[k];
			svg.querySelectorAll('.ff-e.live').forEach(function (g) { g.classList.remove('live'); g.classList.add('done'); });
			clearCur();
			say.innerHTML = caption(k);
			st.go.forEach(function (i) { edge(i, 'live'); node(spec.edges[i][0], true); });
			return travel(st.go[0], ms, r).then(function () {
				if (r !== run) return;
				hideDot(); clearCur();
				st.go.forEach(function (i) { node(spec.edges[i][1], true); });
				pos = k + 1;
				if (pos >= steps.length) finish();
			});
		}
		function play() {
			if (playing) { playing = false; busy = false; run++; hideDot(); controls(); return; }
			if (pos < 0 || pos >= steps.length) begin(false);
			svg.querySelectorAll('.ff-e.ghost,.ff-n.ghost').forEach(function (g) { g.classList.remove('ghost'); });
			var r = ++run; playing = true; controls();
			function next() {
				if (r !== run) return;
				if (pos >= steps.length) { playing = false; busy = false; controls(); return; }
				busy = true; controls();
				doStep(pos, r, 900).then(function () {
					if (r !== run) return;
					busy = false;
					if (pos >= steps.length) { playing = false; controls(); return; }
					controls();
					wait(Math.max(1100, steps[pos - 1].say.length * 28)).then(next);
				});
			}
			wait(300).then(next);
		}
		function build() {
			if (busy && !playing) return;
			playing = false;
			if (pos < 0 || pos >= steps.length) begin(false);
			svg.querySelectorAll('.ff-e.ghost,.ff-n.ghost').forEach(function (g) { g.classList.remove('ghost'); });
			var r = ++run; busy = true; controls();
			doStep(pos, r, 800).then(function () { if (r !== run) return; busy = false; controls(); });
		}
		function restart() {
			run++; playing = false; busy = false; pos = -1;
			reset(false); clearCur(); start.forEach(function (s) { node(s, true); });
			say.innerHTML = INTRO; controls();
		}

		/* Puzzle: the step captions as shuffled cards. Tap them in order; each right answer builds that part. */
		function shuffled(n) {
			var a = [], i, j, t;
			for (i = 0; i < n; i++) a.push(i);
			do { for (i = n - 1; i > 0; i--) { j = Math.floor(Math.random() * (i + 1)); t = a[i]; a[i] = a[j]; a[j] = t; } }
			while (n > 2 && a.every(function (v, k) { return v === k; }));
			return a;
		}
		function nextIdx() { for (var k = 0; k < steps.length; k++) if (!pzState.done[k]) return k; return -1; }
		function accepts(k) {
			if (pzState.done[k]) return false;
			var n = nextIdx();
			return k === n || groups.some(function (g) { return g.indexOf(n) >= 0 && g.indexOf(k) >= 0; });
		}
		function puzzle() {
			run++; playing = false; busy = false; pos = -1; controls('puzzle'); reset(true); clearCur();
			start.forEach(function (s) { node(s, true); });
			pzState = { done: {}, placed: 0, slips: 0 };
			pzDone.innerHTML = '';
			pzK.hidden = false;
			pzCards.innerHTML = shuffled(steps.length).map(function (k) {
				return '<button type="button" class="ff-card" data-k="' + k + '">' + noOrder(steps[k].say) + '</button>';
			}).join('');
			pz.hidden = false;
			bPz.textContent = 'Shuffle and start again';
			say.innerHTML = '<b>Your turn.</b> The diagram has been cleared. Tap the steps below in the order they happen, and it will build as you go.';
		}
		pzCards.addEventListener('click', function (e) {
			var c = e.target.closest('.ff-card');
			if (!c || !pzState || c.disabled) return;
			var k = +c.getAttribute('data-k');
			if (!accepts(k)) {
				pzState.slips++;
				/* a short red shake, then the card goes back to normal: it may well be right later */
				c.classList.remove('no'); void c.offsetWidth; c.classList.add('no');
				clearTimeout(c._noTimer);
				c._noTimer = setTimeout(function () { c.classList.remove('no'); }, 650);
				say.innerHTML = '<b>Not yet.</b> What has to happen before that? Look at what is already on the diagram.';
				return;
			}
			pzState.done[k] = true; pzState.placed++;
			cnt.textContent = 'Step ' + pzState.placed + ' of ' + steps.length;
			pzCards.querySelectorAll('.ff-card.no').forEach(function (x) { clearTimeout(x._noTimer); x.classList.remove('no'); });
			svg.querySelectorAll('.ff-e.live').forEach(function (g) { g.classList.remove('live'); g.classList.add('done'); });
			clearCur(); steps[k].go.forEach(function (i) { edge(i, 'live'); node(spec.edges[i][0], true); node(spec.edges[i][1], true); });
			travel(steps[k].go[0], 800, run).then(hideDot);
			var li = w.document.createElement('li');
			li.innerHTML = steps[k].say;
			pzDone.appendChild(li);
			c.remove();
			if (pzState.placed < steps.length) {
				say.innerHTML = '<b>Right.</b> ' + (steps.length - pzState.placed) + ' to go.';
				var first = pzCards.querySelector('.ff-card'); if (first && w.document.activeElement === w.document.body) first.focus();
			} else {
				pzK.hidden = true;
				svg.querySelectorAll('.ff-e.live').forEach(function (g) { g.classList.remove('live'); g.classList.add('done'); });
				var s = pzState.slips;
				say.innerHTML = '<b>' + (s === 0 ? 'Perfect: every step in the right order first time!' : 'Built! ' + steps.length + ' steps, ' + s + (s === 1 ? ' slip.' : ' slips.')) + '</b> ' + esc(spec.end || '') + ' Press play to watch it run.';
				bPz.textContent = 'Try again with a new shuffle';
				pzState = null;
				verdict();
			}
		});

		bPlay.addEventListener('click', play);
		bBuild.addEventListener('click', build);
		bPz.addEventListener('click', puzzle);
		bReset.addEventListener('click', restart);
		count();

		/* Predict first: commit to a guess, watch the flow, then see the verdict. */
		var guess = null, judged = false;
		function verdict() {
			if (!P || guess === null || judged) return;
			judged = true;
			var box = predBox || fig.querySelector('.ff-pred'), btns = box.querySelectorAll('.ff-pred-opts button'), ok = guess === P.answer;
			var res = box.querySelector('.ff-pred-res');
			btns[P.answer].classList.add('right');
			if (!ok) btns[guess].classList.add('wrong');
			box.classList.add(ok ? 'is-right' : 'is-wrong');
			res.innerHTML = (ok ? '<b>You were right!</b> ' : '<b>Not quite.</b> The answer was “' + P.options[P.answer] + '”. ') + (P.why || '');
		}
		/* Predict first, before the illustration: the box sits at the top of "See it", outside and above the diagram,
		   and draws on what the Explain stage taught. The diagram stays faded and doesn't play until a guess is made;
		   then it plays and the verdict follows. */
		var predBox = null, moved = false;
		if (P) {
			predBox = fig.querySelector('.ff-pred');
			var skip = predBox.querySelector('.ff-pred-skip');
			if (fig.closest('.stage.s-show')) {
				fig.parentNode.insertBefore(predBox, fig);
				predBox.classList.add('ff-pred-out');
				predBox.querySelector('.ff-pred-k').textContent = 'Predict first';
				predBox.querySelector('.ff-pred-hint').textContent = 'Have a guess before you watch, using what you’ve read if it helps. It’s fine to be wrong: guessing first helps you remember what really happens.';
			}
			reset(true);
			start.forEach(function (s) { node(s, true); });
			say.innerHTML = 'Make your prediction above, then watch the flow to check it.';
			predBox.querySelector('.ff-pred-opts').addEventListener('click', function (e) {
				var bt = e.target.closest('button');
				if (!bt || guess !== null) return;
				guess = +bt.getAttribute('data-i');
				predBox.querySelectorAll('.ff-pred-opts button').forEach(function (x) { x.disabled = true; });
				bt.classList.add('chosen');
				skip.hidden = true;
				predBox.querySelector('.ff-pred-res').innerHTML = '<b>You predicted:</b> ' + P.options[guess] + '. Now watch the flow to check.';
				svg.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'nearest' });
				play();
			});
			skip.addEventListener('click', function () { predBox.hidden = true; play(); });
		}

		/* Play once, the first time the diagram is mostly on screen (not when a prediction comes first). */
		if ('IntersectionObserver' in w && !reduce && (!P || moved) && !spec.noAuto) {
			var io = new IntersectionObserver(function (es) {
				es.forEach(function (e) { if (e.isIntersecting) { io.disconnect(); if (pos < 0 && !run) play(); } });
			}, { threshold: 0.6 });
			io.observe(svg);
		}
		return { play: play, build: build };
	}

	/*
	 * Side by side: two versions of the same job, played together, with a verdict under each.
	 * c = {title, intro, left: {label, spec, verdict, good}, right: {...}}
	 */
	function compare(fig, c) {
		fig.classList.add('ffc');
		function side(s, i) {
			return '<div class="ffc-side' + (s.good ? ' good' : ' bad') + '"><h4>' + esc(s.label) + '</h4><figure data-side="' + i + '"></figure><p class="ffc-v" hidden><b>' + (s.good ? '✓ ' : '✗ ') + '</b>' + s.verdict + '</p></div>';
		}
		fig.innerHTML = '<figcaption class="ff-title"><span class="eyebrow">Side by side</span>' + esc(c.title) + '</figcaption>' + (c.intro ? '<p class="ffc-intro">' + c.intro + '</p>' : '') +
			'<div class="ffc-grid">' + side(c.left, 0) + side(c.right, 1) + '</div><div class="ffc-ctl"><button type="button" class="pri">▶ Play both</button></div>';
		var vs = fig.querySelectorAll('.ffc-v'), btn = fig.querySelector('.ffc-ctl button'), apis = [], ended = 0;
		[c.left, c.right].forEach(function (s, i) {
			s.spec.noAuto = true; s.spec.noText = true;
			s.spec.onEnd = function () { vs[i].hidden = false; if (++ended === 2) { btn.disabled = false; btn.textContent = '▶ Play both again'; } };
			apis.push(mount(fig.querySelector('figure[data-side="' + i + '"]'), s.spec));
		});
		function both() { ended = 0; vs.forEach(function (v) { v.hidden = true; }); btn.disabled = true; btn.textContent = 'Playing…'; apis.forEach(function (a) { a.play(); }); }
		btn.addEventListener('click', both);
		if ('IntersectionObserver' in w && !reduce) {
			var io = new IntersectionObserver(function (es) { es.forEach(function (e) { if (e.isIntersecting) { io.disconnect(); both(); } }); }, { threshold: 0.5 });
			io.observe(fig.querySelector('.ffc-grid'));
		}
	}

	w.AIADFlow = { mount: mount, compare: compare };
})(window);
