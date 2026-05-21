/**
 * Card/block SVG illustrations for [aiad_computing_curriculum]
 * Shortcode: [aiad_computing_curriculum]
 */
(function (global) {
	'use strict';

	function escAccent(c) {
		return /^#[0-9A-Fa-f]{6}$/.test(c || '') ? c : '#1D9E75';
	}

	function svgShell(h, title, inner) {
		return (
			'<svg viewBox="0 0 320 ' +
			h +
			'" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true">' +
			'<rect width="320" height="' +
			h +
			'" fill="#1e1e1e"/>' +
			'<text x="160" y="18" text-anchor="middle" fill="#aaa" font-size="10" font-weight="600" font-family="system-ui,sans-serif">' +
			title +
			'</text>' +
			inner +
			'</svg>'
		);
	}

	function card(x, y, w, h, label, a, hi, opts) {
		opts = opts || {};
		var fill = hi ? escAccent(a) : '#2a2a2a';
		var stroke = hi ? '#fff' : opts.warn ? '#D85A30' : '#555';
		var sw = hi || opts.warn ? 2.5 : 1;
		var fs = opts.small ? 10 : label.length > 12 ? 10 : 12;
		var s =
			'<rect x="' +
			(x + 2) +
			'" y="' +
			(y + 2) +
			'" width="' +
			w +
			'" height="' +
			h +
			'" rx="5" fill="#111" opacity="0.5"/>' +
			'<rect x="' +
			x +
			'" y="' +
			y +
			'" width="' +
			w +
			'" height="' +
			h +
			'" rx="5" fill="' +
			fill +
			'" stroke="' +
			stroke +
			'" stroke-width="' +
			sw +
			'"/>';
		var lines = String(label).split('\n');
		var ty = y + h / 2 - (lines.length - 1) * 6;
		lines.forEach(function (line, i) {
			s +=
				'<text x="' +
				(x + w / 2) +
				'" y="' +
				(ty + i * 14) +
				'" text-anchor="middle" fill="#fff" font-size="' +
				fs +
				'" font-weight="' +
				(hi ? '700' : '600') +
				'" font-family="system-ui,sans-serif">' +
				line +
				'</text>';
		});
		if (opts.num) {
			s +=
				'<circle cx="' +
				(x + 10) +
				'" cy="' +
				(y + 10) +
				'" r="9" fill="' +
				escAccent(a) +
				'"/><text x="' +
				(x + 10) +
				'" y="' +
				(y + 14) +
				'" text-anchor="middle" fill="#fff" font-size="10" font-weight="700" font-family="system-ui">' +
				opts.num +
				'</text>';
		}
		return s;
	}

	function rowCards(items, y, a, hiSet, cw, ch, gap) {
		cw = cw || 68;
		ch = ch || 48;
		gap = gap || 8;
		var totalW = items.length * cw + (items.length - 1) * gap;
		var x0 = (320 - totalW) / 2;
		var s = '';
		items.forEach(function (item, i) {
			var hi = hiSet && hiSet.indexOf(i) >= 0;
			s += card(x0 + i * (cw + gap), y, cw, ch, item, a, hi, {});
		});
		return s;
	}

	var CARD_SVG = {
		bebot: function (a) {
			var steps = ['Forward', 'Forward', 'Turn left', 'Forward'];
			var inner = rowCards(steps, 32, a, [0, 1], 62, 44, 6);
			inner += card(248, 38, 52, 52, 'Bee-\nBot', a, true, { small: true });
			inner +=
				'<text x="160" y="128" text-anchor="middle" fill="#888" font-size="10" font-family="system-ui,sans-serif">Bee-Bot follows each instruction in order</text>';
			return svgShell(140, 'STEP-BY-STEP ROUTE', inner);
		},
		recipe: function (a) {
			var inner = '';
			['Get bread', 'Add filling', 'Cut', 'Serve'].forEach(function (lbl, i) {
				inner += card(48, 28 + i * 26, 224, 22, lbl, a, i === 1, { num: i + 1 });
			});
			return svgShell(140, 'RECIPE = ALGORITHM', inner);
		},
		'debug-order': function (a) {
			var inner = '';
			inner += card(24, 36, 88, 44, '2. Pour', a, false, { warn: true });
			inner += card(116, 36, 88, 44, '1. Pick up', a, false, {});
			inner += card(208, 36, 88, 44, '3. Drink', a, false, {});
			inner +=
				'<text x="160" y="100" text-anchor="middle" fill="#D85A30" font-size="11" font-weight="600" font-family="system-ui">Wrong order — fix the sequence</text>';
			return svgShell(120, 'DEBUG THE SEQUENCE', inner);
		},
		loop: function (a) {
			var inner = card(118, 36, 84, 40, 'repeat 10', a, true, {});
			inner += card(118, 84, 84, 32, 'move', a, false, {});
			inner +=
				'<path d="M202 56 C248 56 248 96 202 96" fill="none" stroke="' +
				escAccent(a) +
				'" stroke-width="2"/>';
			inner +=
				'<text x="160" y="128" text-anchor="middle" fill="#888" font-size="10" font-family="system-ui">Runs the blocks inside again and again</text>';
			return svgShell(140, 'LOOP — REPEAT BLOCK', inner);
		},
		'logic-bug': function (a) {
			var inner = card(20, 44, 80, 48, 'Press\nLEFT', a, true, {});
			inner += card(220, 44, 80, 48, 'Moves\nRIGHT', a, false, { warn: true });
			inner +=
				'<text x="160" y="44" text-anchor="middle" fill="#D85A30" font-size="10" font-weight="700" font-family="system-ui">logic error</text>';
			inner +=
				'<text x="160" y="108" text-anchor="middle" fill="#888" font-size="10" font-family="system-ui">Program runs but wrong result</text>';
			return svgShell(125, 'SCRATCH BUG', inner);
		},
		'if-then': function (a) {
			var inner = card(108, 30, 104, 36, 'Red wall?', a, true, {});
			inner += card(32, 82, 88, 40, 'Turn', a, false, {});
			inner += card(200, 82, 88, 40, 'Continue', a, false, {});
			inner +=
				'<text x="76" y="78" text-anchor="middle" fill="#86efac" font-size="9" font-family="system-ui">yes</text>';
			inner +=
				'<text x="244" y="78" text-anchor="middle" fill="#888" font-size="9" font-family="system-ui">no</text>';
			return svgShell(140, 'IF / THEN — SELECTION', inner);
		},
		variable: function (a) {
			var inner = card(40, 44, 100, 52, 'score\n7', a, false, {});
			inner += card(190, 44, 90, 52, '+1 goal', a, true, {});
			inner += card(118, 44, 64, 52, '→', a, false, { small: true });
			inner +=
				'<text x="160" y="118" text-anchor="middle" fill="#888" font-size="10" font-family="system-ui">Variable stores data that can change</text>';
			return svgShell(135, 'VARIABLE', inner);
		},
		bubble: function (a) {
			var nums = [5, 3, 1, 4, 2];
			var w = 46;
			var h = 56;
			var gap = 10;
			var x0 = 22;
			var y = 42;
			var inner = '';
			nums.forEach(function (n, i) {
				var x = x0 + i * (w + gap);
				var compare = i === 0 || i === 1;
				inner += card(x, y, w, h, String(n), a, compare, {});
			});
			var xA = x0 + w;
			var xB = x0 + w + gap;
			inner +=
				'<path d="M' +
				(xA + 6) +
				' ' +
				(y + h / 2) +
				' L' +
				(xB - 6) +
				' ' +
				(y + h / 2) +
				'" stroke="' +
				escAccent(a) +
				'" stroke-width="2"/>';
			inner +=
				'<text x="160" y="128" text-anchor="middle" fill="#888" font-size="10" font-family="system-ui">Compare neighbours · swap if wrong order</text>';
			return svgShell(150, 'BUBBLE SORT', inner);
		},
		binary: function (a) {
			var nums = [2, 5, 8, 12, 15, 19, 22];
			var w = 36;
			var gap = 6;
			var x0 = (320 - (nums.length * w + (nums.length - 1) * gap)) / 2;
			var inner = '';
			nums.forEach(function (n, i) {
				inner += card(x0 + i * (w + gap), 40, w, 48, String(n), a, i === 3, {});
			});
			inner +=
				'<text x="160" y="108" text-anchor="middle" fill="#888" font-size="10" font-family="system-ui">Sorted list · check the middle each time</text>';
			return svgShell(125, 'BINARY SEARCH', inner);
		},
		linear: function (a) {
			var names = ['Ali', 'Ben', 'Cara', 'Dee', 'Eva'];
			var inner = '';
			names.forEach(function (n, i) {
				inner += card(100, 28 + i * 18, 120, 16, n, a, i === 2, { small: true });
			});
			inner +=
				'<text x="160" y="118" text-anchor="middle" fill="#888" font-size="10" font-family="system-ui">Check every name until found</text>';
			return svgShell(135, 'LINEAR SEARCH', inner);
		},
		merge: function (a) {
			var inner = card(24, 44, 120, 48, 'Sort left\nhalf', a, false, {});
			inner += card(176, 44, 120, 48, 'Sort right\nhalf', a, false, {});
			inner += card(108, 100, 104, 28, 'Merge', a, true, {});
			inner +=
				'<text x="84" y="72" text-anchor="middle" fill="' +
				escAccent(a) +
				'" font-size="14" font-family="system-ui">+</text>';
			return svgShell(145, 'MERGE SORT', inner);
		},
		ethics: function (a) {
			var terms = [
				['Privacy', 'Control of\npersonal data'],
				['Bias', 'Unfair\noutcomes'],
				['IP', 'Creative\nrights'],
				['Footprint', 'Data left\nonline'],
			];
			var inner = '';
			terms.forEach(function (t, i) {
				var col = i % 2;
				var row = Math.floor(i / 2);
				inner += card(24 + col * 156, 30 + row * 52, 136, 44, t[0] + '\n' + t[1], a, i === 1, { small: true });
			});
			return svgShell(145, 'ETHICS TERMS', inner);
		},
		legal: function (a) {
			var inner = rowCards(['UK GDPR', 'Misuse Act', 'Creative\nCommons', 'Environment'], 36, a, null, 70, 52, 6);
			return svgShell(120, 'LAWS & PRINCIPLES', inner);
		},
		'scales-justice': function (a) {
			var inner = card(108, 28, 104, 32, 'AI sentencing', a, true, {});
			inner += card(24, 72, 80, 36, 'Bias', a, false, {});
			inner += card(120, 72, 80, 36, 'Transparency', a, false, {});
			inner += card(216, 72, 80, 36, 'Oversight', a, false, {});
			inner +=
				'<text x="160" y="128" text-anchor="middle" fill="#888" font-size="10" font-family="system-ui">Human review still matters</text>';
			return svgShell(145, 'AI & JUSTICE', inner);
		},
		structures: function (a) {
			var inner = rowCards(['Stack\nLIFO', 'Queue\nFIFO', 'Tree', 'Hash\ntable'], 40, a, [0], 68, 52, 6);
			return svgShell(115, 'DATA STRUCTURES', inner);
		},
		ops: function (a) {
			var inner = rowCards(['push/pop', 'enqueue', 'traverse', 'collision'], 40, a, null, 68, 48, 6);
			return svgShell(110, 'OPERATIONS', inner);
		},
		stream: function (a) {
			var inner = rowCards(['User', 'Hash\nlookup', 'Graph', 'Recommend'], 40, a, [2], 68, 48, 6);
			return svgShell(110, 'STREAMING AI', inner);
		},
		'aqa-stakeholders': function (a) {
			var inner = card(118, 32, 84, 36, 'AI loans', a, true, {});
			inner += card(24, 78, 72, 32, 'Customer', a, false, { small: true });
			inner += card(88, 78, 72, 32, 'Bank', a, false, { small: true });
			inner += card(160, 78, 72, 32, 'Regulator', a, false, { small: true });
			inner += card(232, 78, 72, 32, 'Developer', a, false, { small: true });
			return svgShell(130, 'WHO IS AFFECTED?', inner);
		},
		'aqa-loans': function (a) {
			var inner = card(24, 40, 72, 40, 'Apply', a, false, {});
			inner += card(124, 40, 72, 40, 'AI\n decides', a, true, {});
			inner += card(224, 36, 72, 28, 'Approve', a, false, { small: true });
			inner += card(224, 68, 72, 28, 'Decline', a, false, { small: true });
			inner += card(24, 92, 272, 24, 'Privacy · bias checks · human review', a, false, { small: true });
			return svgShell(135, 'BANK LOAN AI', inner);
		},
	};

	global.aiadCcRenderIllustration = function (key, accent) {
		if (typeof global.aiadCcRenderRichIllustration === 'function') {
			var rich = global.aiadCcRenderRichIllustration(key);
			if (rich) {
				return rich;
			}
		}
		var fn = CARD_SVG[key];
		if (!fn) {
			return '';
		}
		return (
			'<div class="cc-illustration cc-illustration--blocks" data-cc-illustration aria-hidden="true">' +
			fn(accent) +
			'</div>'
		);
	};

	/** Kept for compatibility — no Mermaid, illustrations are instant SVG */
	global.aiadCcInitMermaid = function () {
		return Promise.resolve();
	};
})(typeof window !== 'undefined' ? window : this);
