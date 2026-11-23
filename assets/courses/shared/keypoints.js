/*
 * Links each chapter's "Key points" box to the periodic table elements collected in that chapter.
 *
 * Reads the chapter's own element tiles (".found .tiles"), so it always matches what students collect. The first
 * mention of each element's term in the key points gets a mini tile (AI elements link to the AI Literacy Periodic
 * Table; chemistry elements show their atomic number), and a line at the bottom lists all of the chapter's elements.
 */
(function (w, d) {
	'use strict';
	/* Everyday words the summaries use for some elements, tried after the element's own name. */
	var ALIAS = {
		Rg: ['RAG'], Mc: ['MCP'], Gp: ['GPUs', 'GPU'], Tp: ['accelerators'], Pm: ['parameters'], Fn: ['function calling', 'tool use'],
		Ap: ['the app'], Ag: ['agents', 'agent'], Lp: ['loop'], Pl: ['plan'], Hi: ['a person', 'people'], Gr: ['checks', 'guardrails'],
		Au: ['autonomy'], Or: ['orchestrator', 'lead agent'], Ma: ['team', 'teams'], Wo: ['workflow'], Ct: ['chain of thought', 'reasoning'],
		Ev: ['evaluator', 'checks'], Te: ['temperature', 'voting'], Cd: ['data centre'], If: ['inference'], Ls: ['latent space'],
		Tr: ['training', 'trained'], Ow: ['open-weight'], Ds: ['research'], Ed: ['edge AI', 'your own device'], Sm: ['small models'],
		Ha: ['don’t check facts'], Pr: ['prompt'], Ll: ['model'], So: ['global politics'], Ac: ['agent'], Cb: ['chatbot'], Vd: ['vector database'],
		Qz: ['bits'], Em: ['embedding'], Tk: ['tokens', 'token'], Cw: ['context window'], Me: ['memory'], Ra: ['think, act, observe'],
		Sn: ['tin']
	};
	function esc(s) { return String(s).replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }
	function info(tile) {
		var s = tile.querySelector('.s'), nm = tile.querySelector('.nm'), n = tile.querySelector('.n');
		return {
			sym: s ? s.textContent.trim() : '', name: nm ? nm.textContent.trim() : '',
			chem: tile.classList.contains('chem'), num: n && /\d/.test(n.textContent) ? n.textContent.trim() : '',
			fam: tile.getAttribute('data-fam') || '', href: tile.getAttribute('href') || tile.getAttribute('data-href') || ''
		};
	}
	function chip(e, word) {
		var inner = (e.chem ? '<small>' + e.num + '</small>' : '') + '<b>' + e.sym + '</b>';
		var tag = e.href ? 'a' : 'span';
		return '<' + tag + ' class="kpel ' + (e.chem ? 'chem' : 'ai') + '"' + (e.fam ? ' data-fam="' + e.fam + '"' : '') + (e.href ? ' href="' + e.href + '"' : '') +
			' title="' + (e.chem ? 'Chemistry element ' + e.num + ': ' : 'AI Literacy Periodic Table: ') + e.name.replace(/"/g, '&quot;') + '"><span class="kpel-t">' + inner + '</span>' + (word || e.name) + '</' + tag + '>';
	}
	function run() {
		d.querySelectorAll('.chapter').forEach(function (ch) {
			var kp = ch.querySelector('.kp'), tiles = ch.querySelectorAll('.found .tiles .tile');
			if (!kp || !tiles.length || kp.querySelector('.kp-els')) return;
			var els = [].map.call(tiles, info).filter(function (e) { return e.sym; });
			var items = [].slice.call(kp.querySelectorAll('li'));
			var taken = {};
			els.forEach(function (e) {
				var tries = [e.name].concat(ALIAS[e.sym] || []);
				for (var t = 0; t < tries.length; t++) {
					var re = new RegExp('(^|[^\\w>])(' + esc(tries[t]) + '(?:e?s)?)(?![\\w<])', 'i');
					for (var i = 0; i < items.length; i++) {
						var li = items[i];
						/* only search plain text, never inside a chip already placed */
						var parts = li.innerHTML.split(/(<a [^>]*class="kpel[\s\S]*?<\/a>|<span class="kpel[\s\S]*?<\/span><\/span>[^<]*<\/span>|<[^>]+>)/), hit = false;
						for (var j = 0; j < parts.length && !hit; j++) {
							if (parts[j].charAt(0) === '<') continue;
							var m = parts[j].match(re);
							if (m && !taken[m[2].toLowerCase()]) {
								parts[j] = parts[j].replace(re, function (all, pre, word) { return pre + chip(e, word); });
								taken[m[2].toLowerCase()] = hit = true;
							}
						}
						if (hit) { li.innerHTML = parts.join(''); return; }
					}
				}
			});
			var foot = d.createElement('p');
			foot.className = 'kp-els';
			foot.innerHTML = '<span>Elements in this chapter</span>' + els.map(function (e) { return chip(e); }).join('');
			kp.appendChild(foot);
		});
		highlights();
	}

	/* The highlighted words in each story (<em>) and the defined terms in "What's really happening" (<strong>) link to their
	   element too: this chapter's elements first, then the course's. */
	function highlights() {
		var all = [], seen = {};
		d.querySelectorAll('.found .tiles .tile').forEach(function (t) { var e = info(t); if (e.sym && !seen[e.sym]) { seen[e.sym] = 1; all.push(e); } });
		d.querySelectorAll('.chapter').forEach(function (ch) {
			var mine = [].map.call(ch.querySelectorAll('.found .tiles .tile'), info);
			var pool = mine.concat(all.filter(function (e) { return !mine.some(function (m) { return m.sym === e.sym; }); }));
			/* the story's highlighted words, and each term defined in bold in "What's really happening" */
		ch.querySelectorAll('.story em, .really strong').forEach(function (em) {
				if (em.closest('a') || em.querySelector('.kpel-t')) return;
				var text = em.textContent.replace(/\s+/g, ' ').trim();
				var hit = null;
				for (var k = 0; k < pool.length && !hit; k++) {
					var tries = [pool[k].name].concat(ALIAS[pool[k].sym] || []);
					for (var t = 0; t < tries.length; t++) {
						if (tries[t].length < 3) continue;
						if (new RegExp('^(an? |the )?' + esc(tries[t]) + '(e?s)?$', 'i').test(text)) { hit = pool[k]; break; }
					}
				}
				if (!hit) return;
				em.insertAdjacentHTML('afterbegin', '<span class="kpel-t" aria-hidden="true">' + (hit.chem ? '<small>' + hit.num + '</small>' : '') + '<b>' + hit.sym + '</b></span>');
				var wrap = d.createElement(hit.href ? 'a' : 'span');
				wrap.className = 'kpel-em ' + (hit.chem ? 'chem' : 'ai');
				if (hit.fam) wrap.setAttribute('data-fam', hit.fam);
				if (hit.href) wrap.href = hit.href;
				wrap.title = (hit.chem ? 'Chemistry element ' + hit.num + ': ' : 'AI Literacy Periodic Table: ') + hit.name;
				em.parentNode.insertBefore(wrap, em);
				wrap.appendChild(em);
			});
		});
	}
	if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', run); else run();
})(window, document);
