/*
 * Earn your elements: a chapter's tiles are no longer collected by scrolling past them. Each starts locked, and
 * opens only when you answer a question about it: which of three definitions matches the element. The next chapter
 * opens once every element in this one is earned ("Open it anyway" lets a teacher or a returning student through).
 *
 *   AIADEarn.init({
 *     key:      the course's storage key (the same list of earned elements the tray, compounds and hub read),
 *     got:      that list (mutated in place),
 *     info(k):  {sym, name, def, chem, n} for an element key,
 *     tile(k):  the earned tile's HTML (the page's own tileHTML),
 *     earn(k):  add k to got, save it and redraw the tray,
 *     reset():  clear got, save it and redraw the tray
 *   })
 */
(function (w, d) {
	'use strict';
	var api, chapters = [], openKey;

	function esc(s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
	function shuffle(a) { a = a.slice(); for (var i = a.length - 1; i > 0; i--) { var j = Math.floor(Math.random() * (i + 1)), t = a[i]; a[i] = a[j]; a[j] = t; } return a; }
	function load(k, dflt) { try { var v = JSON.parse(localStorage.getItem(k)); return v == null ? dflt : v; } catch (e) { return dflt; } }
	function save(k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) { /* storage blocked: the page still works for this visit */ } }
	function els(c) { return c.dataset.els.split(' '); }
	function has(k) { return api.got.indexOf(k) >= 0; }
	function done(c) { return els(c).every(has); }

	/* A definition without clues to other elements' names ("Contrast with Orchestrator."). */
	function clean(def, name) {
		var t = String(def || '').replace(/\s*(Contrast|Compare)( it)?( with)? [^.]*\.?\s*$/i, '').replace(/\s*\([A-Z][a-z]?\)/g, '').trim();
		if (name && name.length > 3) t = t.replace(new RegExp(name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'ig'), '___');
		return t;
	}

	function lockedTile(k) {
		var e = api.info(k);
		return '<button type="button" class="tile locked' + (e.chem ? ' chem' : ' ai') + '" data-k="' + esc(k) + '" aria-label="Locked: ' + esc(e.name) + '. Answer a question to earn it.">' +
			'<span class="n">' + (e.chem ? e.n : 'AI') + '</span><span class="s">' + esc(e.sym) + '</span><span class="nm">' + esc(e.name) + '</span><span class="k lk">🔒 Earn it</span></button>';
	}

	function drawChapter(c) {
		var found = c.querySelector('.found'); if (!found) return;
		var box = found.querySelector('.tiles'), p = found.querySelector('p');
		var keys = els(c), n = keys.filter(has).length;
		if (p) p.innerHTML = n === keys.length ? 'Earned in this chapter <span class="earn-n ok">' + n + ' of ' + n + ' ✓</span>' : 'Earn this chapter’s elements <span class="earn-n">' + n + ' of ' + keys.length + '</span><span class="earn-hint">Tap a locked tile and answer one question about it.</span>';
		box.innerHTML = keys.map(function (k) { return has(k) ? api.tile(k).replace('class="tile', 'class="tile earned') : lockedTile(k); }).join('');
		box.querySelectorAll('.tile.locked').forEach(function (b) { b.addEventListener('click', function () { ask(c, b.getAttribute('data-k')); }); });
		/* keypoints.js reads the tiles' links: keep them on the locked ones too */
		box.querySelectorAll('.tile.locked').forEach(function (b) { var e = api.info(b.getAttribute('data-k')); if (!e.chem) b.setAttribute('data-href', '/ai-literacy-periodic-table/#element-' + e.sym.toLowerCase()); if (e.fam) b.setAttribute('data-fam', e.fam); });
	}

	function ask(c, k) {
		var found = c.querySelector('.found');
		var old = found.querySelector('.earn-q'); if (old) old.remove();
		found.querySelectorAll('.tile.locked').forEach(function (b) { b.classList.toggle('asking', b.getAttribute('data-k') === k); });
		var e = api.info(k);
		/* two wrong definitions from the same course, chemistry with chemistry where possible */
		var pool = api.all.filter(function (x) { return x !== k && !!api.info(x).chem === !!e.chem; });
		if (pool.length < 2) pool = api.all.filter(function (x) { return x !== k; });
		var opts = shuffle([k].concat(shuffle(pool).slice(0, 2)));
		var q = d.createElement('div');
		q.className = 'earn-q'; q.setAttribute('role', 'group'); q.setAttribute('aria-label', 'Earn ' + e.name);
		q.innerHTML = '<p class="earn-q-k">Earn <b>' + esc(e.sym) + '</b> · ' + esc(e.name) + '</p><p class="earn-q-q">Which of these describes <b>' + esc(e.name) + '</b>?</p><div class="earn-opts"></div><p class="earn-fb" aria-live="polite"></p>';
		var box = q.querySelector('.earn-opts'), fb = q.querySelector('.earn-fb');
		opts.forEach(function (o) {
			var b = d.createElement('button');
			b.type = 'button'; b.className = 'earn-opt';
			b.textContent = clean(api.info(o).def, api.info(o).name);
			b.addEventListener('click', function () {
				if (o === k) {
					b.classList.add('right'); box.querySelectorAll('button').forEach(function (x) { x.disabled = true; });
					fb.textContent = 'Earned. ' + e.name + ' is now in your collection.';
					api.earn(k);
					setTimeout(function () {
						drawChapter(c); gate();
						var next = c.querySelector('.found .tile.locked');
						if (next) ask(c, next.getAttribute('data-k'));
						else { var nx = nextChapter(c); var f = c.querySelector('.found'); q.remove(); f.insertAdjacentHTML('beforeend', '<p class="earn-done">All earned.' + (nx ? ' <a href="#' + nx.id + '">Chapter ' + (chapters.indexOf(nx)) + ' is open →</a>' : ' That’s every element in this course.') + '</p>'); }
					}, 650);
				} else {
					b.classList.add('wrong'); b.disabled = true;
					fb.textContent = 'Not that one: that describes ' + api.info(o).name + '. Try again.';
				}
			});
			box.appendChild(b);
		});
		found.appendChild(q);
		var first = box.querySelector('button'); if (first) first.focus({ preventScroll: true });
	}

	function nextChapter(c) { var i = chapters.indexOf(c); return chapters[i + 1] || null; }

	/* A chapter opens when every chapter before it is earned, or when the student chose to open it anyway. */
	function gate() {
		var opened = load(openKey, []), behind = null;
		chapters.forEach(function (c, i) {
			var bar = c.querySelector('.lockbar');
			var lock = i > 0 && behind && opened.indexOf(c.id) < 0;
			c.classList.toggle('ch-locked', !!lock);
			if (lock) {
				if (!bar) { bar = d.createElement('div'); bar.className = 'lockbar'; var h = c.querySelector('h2'); c.insertBefore(bar, h ? h.nextSibling : c.firstChild); }
				var from = behind, num = chapters.indexOf(from);
				bar.innerHTML = '<p><b>🔒 Locked.</b> Earn the elements at the end of ' + (num ? 'Chapter ' + num : 'the prologue') + ' to open this chapter: one quick question for each.</p>' +
					'<p class="lb-act"><a class="lb-go" href="#' + from.id + '-found">Go to the questions</a><button type="button" class="lb-skip">Open it anyway</button></p>' +
					'<p class="lb-teach"><button type="button" class="lb-all">Teacher? Unlock every chapter</button></p>';
				bar.querySelector('.lb-skip').onclick = function () { var o = load(openKey, []); o.push(c.id); save(openKey, o); gate(); };
				bar.querySelector('.lb-all').onclick = function () { save(openKey, chapters.map(function (x) { return x.id; })); gate(); };
				bar.querySelector('.lb-go').onclick = function (ev) { ev.preventDefault(); var f = from.querySelector('.found'); f.scrollIntoView({ behavior: 'smooth', block: 'center' }); var t = f.querySelector('.tile.locked'); if (t && !f.querySelector('.earn-q')) t.click(); };
			} else if (bar) bar.remove();
			if (!done(c)) behind = c;
		});
	}

	/* Summary: at the end of each chapter's Explain stage, the exact definitions the tile questions use, so
	   what is taught and what is asked always match. */
	function defs(c) {
		var ex = c.querySelector('.s-explain'); if (!ex || ex.querySelector('.defs')) return;
		var box = d.createElement('div');
		box.className = 'defs';
		box.innerHTML = '<p class="defs-k">Summary</p><dl>' + els(c).map(function (k) {
			var e = api.info(k);
			var tag = e.chem ? 'span' : 'a', href = e.chem ? '' : ' href="/ai-literacy-periodic-table/#element-' + esc(e.sym.toLowerCase()) + '" title="Open ' + esc(e.name) + ' on the AI Literacy Periodic Table"';
			return '<div><dt><' + tag + ' class="defs-t' + (e.chem ? ' chem' : '') + '"' + href + (e.fam ? ' data-fam="' + esc(e.fam) + '"' : '') + '>' + (e.chem ? '<small>' + esc(e.n) + '</small>' : '') + esc(e.sym) + '</' + tag + '>' + esc(e.name) + '</dt><dd>' + esc(e.def) + '</dd></div>';
		}).join('') + '</dl><p class="defs-note">You’ll be asked about each of these to earn its tile at the end of the chapter. Tap an AI tile to see it on the AI Literacy Periodic Table.</p>';
		var lens = ex.querySelector('.lens');
		ex.insertBefore(box, lens || null);
	}

	/* Stage 4, Sum it up: students write the chapter in their own words first (producing beats rereading), then
	   compare with the chapter's key points, then earn its tiles as the last retrieval. */
	function sumUp(c) {
		var found = c.querySelector('.found'), stages = c.querySelector('.stages');
		if (!found || !stages || c.querySelector('.s-sum')) return;
		var st = d.createElement('div');
		st.className = 'stage s-sum';
		var n = stages.querySelectorAll('.stage').length + 1;
		var kp = c.dataset.sumPoints ? c.dataset.sumPoints.split('|') : [].map.call(c.querySelectorAll('.kp li'), function (li) { return li.textContent.replace(/\s+/g, ' ').trim(); });
		/* Think, then reveal: students recall the chapter in their head (or say it to a partner), then compare with the
		   checklist. No typing, and the key points aren't simply shown again. */
		st.innerHTML = '<p class="stage-l"><b>' + n + '</b>Sum it up</p>' +
			'<div class="sum-box"><p class="sum-q">' + esc(c.dataset.sum || 'In one or two sentences, tell a friend what this chapter was about.') + '</p>' +
			'<p class="sum-how">Say it in your head, or out loud to a partner. Then check.</p>' +
			'<p class="sum-act"><button type="button" class="sum-go">I’ve got my summary</button></p>' +
			'<div class="sum-res" hidden><b>Did yours include…</b>' + kp.map(function (t) { return '<label><input type="checkbox"> ' + esc(t) + '</label>'; }).join('') + '</div></div>';
		st.querySelector('.sum-go').addEventListener('click', function () { var r = st.querySelector('.sum-res'); r.hidden = false; this.disabled = true; this.textContent = 'Summary in mind ✓'; });
		stages.insertBefore(st, found);
		/* a card that introduces the tiles belongs just above them, not between the flow and the check */
		var intro = c.querySelector('.tiles-intro'); if (intro) st.appendChild(intro);
		st.appendChild(found);
	}

	function init(o) {
		api = o;
		chapters = [].slice.call(d.querySelectorAll('.chapter'));
		api.all = []; chapters.forEach(function (c) { els(c).forEach(function (k) { if (api.all.indexOf(k) < 0) api.all.push(k); }); });
		openKey = o.key + '-opened';
		/* Tiles collected by scrolling, before earning existed, don't count: start everyone from zero once. */
		if (!load(o.key + '-earn-v1', false)) { o.reset(); save(o.key + '-earn-v1', true); }
		chapters.forEach(function (c) { var f = c.querySelector('.found'); if (f && !f.id) f.id = c.id + '-found'; defs(c); sumUp(c); drawChapter(c); });
		gate();
	}

	function refresh() { save(openKey, []); chapters.forEach(drawChapter); gate(); }

	w.AIADEarn = { init: init, refresh: refresh };
})(window, document);
