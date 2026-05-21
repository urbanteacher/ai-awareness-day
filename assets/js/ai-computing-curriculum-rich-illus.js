/**
 * Rich HTML illustrations (Bee-Bot grid, Scratch blocks, bubble sort, data structures).
 * Data structures panel matches the illustrated-edition inline HTML exactly.
 */
(function (global) {
	'use strict';

	function beebotHTML() {
		var h =
			'<div class="cc-illus cc-illus--rich"><p class="cc-illus-lbl">Bee-Bot grid — start at ⭐, facing right. Instructions: Forward, Forward, Turn Left, Forward</p>';
		h += '<div class="cc-illus-scroll"><table class="cc-beebot-grid"><tr><th></th>';
		['A', 'B', 'C', 'D'].forEach(function (c) {
			h += '<th>' + c + '</th>';
		});
		h += '</tr>';
		for (var r = 0; r < 4; r++) {
			h += '<tr><td class="cc-beebot-row">' + (r + 1) + '</td>';
			for (var c2 = 0; c2 < 4; c2++) {
				var isStart = r === 0 && c2 === 0;
				var isLand = r === 1 && c2 === 2;
				var cls = 'cc-beebot-cell';
				if (isLand) {
					cls += ' cc-beebot-cell--land';
				}
				if (isStart) {
					cls += ' cc-beebot-cell--start';
				}
				h += '<td class="' + cls + '">';
				if (isStart) {
					h += '⭐';
				}
				if (isLand) {
					h += '<span class="cc-beebot-bot" aria-hidden="true">🤖</span>';
				}
				h += '</td>';
			}
			h += '</tr>';
		}
		h +=
			'</table></div><p class="cc-illus-caption">The robot lands at square <strong>C2</strong></p></div>';
		return h;
	}

	function scratchHTML() {
		var h =
			'<div class="cc-illus"><p class="cc-illus-lbl">Scratch blocks \u2014 how many times does the sprite say \u201cHello\u201d?</p>';
		h += '<div class="scratch-block">';
		h += '<div class="sb-event">When &#x1F6A9; clicked</div>';
		h += '<div class="sb-loop">repeat <span class="sb-num">10</span></div>';
		h +=
			'<div class="sb-say">say <span class="sb-str">Hello</span> for <span style="background:rgba(0,0,0,0.15);border-radius:4px;padding:2px 8px">1</span> seconds</div>';
		h += '<div class="sb-end">end</div>';
		h += '</div></div>';
		return h;
	}

	function bubbleSortHTML() {
		var passes = [
			{ label: 'Start', arr: [5, 3, 1, 4, 2], done: [] },
			{ label: 'After pass 1', arr: [3, 1, 4, 2, 5], done: [4] },
			{ label: 'After pass 2', arr: [1, 3, 2, 4, 5], done: [3, 4] },
			{ label: 'After pass 3', arr: [1, 2, 3, 4, 5], done: [2, 3, 4] },
			{ label: 'After pass 4 \u2713 Sorted', arr: [1, 2, 3, 4, 5], done: [0, 1, 2, 3, 4] },
		];
		var h =
			'<div class="cc-illus"><p class="cc-illus-lbl">Bubble sort on [5, 3, 1, 4, 2] \u2014 green = sorted position confirmed</p>';
		passes.forEach(function (p) {
			h += '<div class="bs-row"><span class="bs-label">' + p.label + '</span><div class="bs-cells">';
			p.arr.forEach(function (v, i) {
				h +=
					'<div class="bs-cell' +
					(p.done.indexOf(i) !== -1 ? ' done' : '') +
					'">' +
					v +
					'</div>';
			});
			h += '</div></div>';
		});
		h += '</div>';
		return h;
	}

	/** Exact inline HTML from illustrated edition dsHTML(). */
	function dsHTML() {
		var h =
			'<div class="cc-illus"><p class="cc-illus-lbl">Key data structures \u2014 visual reference for Questions A, B and C</p>';
		h += '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px">';

		h += '<div><p style="font-size:12px;font-weight:600;color:#3C3489;margin-bottom:8px">Stack (LIFO)</p>';
		['C (top)', 'B', 'A'].forEach(function (v) {
			h +=
				'<div style="background:#EEEDFE;border:1.5px solid #534AB7;border-radius:6px;padding:7px 10px;text-align:center;font-size:13px;font-weight:600;color:#3C3489;margin-bottom:3px">' +
				v +
				'</div>';
		});
		h +=
			'<p style="font-size:11px;color:#888;margin-top:5px;text-align:center">push / pop \u2191</p></div>';

		h += '<div><p style="font-size:12px;font-weight:600;color:#085041;margin-bottom:8px">Queue (FIFO)</p>';
		h += '<div style="display:flex;gap:3px;align-items:center;margin-top:18px">';
		h += '<span style="font-size:11px;color:#085041">\u2192 in</span>';
		['A', 'B', 'C'].forEach(function (v) {
			h +=
				'<div style="background:#E1F5EE;border:1.5px solid #1D9E75;border-radius:6px;padding:7px 10px;text-align:center;font-size:13px;font-weight:600;color:#085041;min-width:36px">' +
				v +
				'</div>';
		});
		h += '<span style="font-size:11px;color:#085041">out \u2192</span></div>';
		h +=
			'<p style="font-size:11px;color:#888;margin-top:5px;text-align:center">enqueue / dequeue</p></div>';

		h += '<div><p style="font-size:12px;font-weight:600;color:#633806;margin-bottom:8px">Binary tree</p>';
		h += '<div style="text-align:center">';
		h +=
			'<div style="display:inline-block;background:#FAEEDA;border:1.5px solid #BA7517;border-radius:6px;padding:5px 12px;font-size:13px;font-weight:600;color:#633806;margin-bottom:4px">5</div><br>';
		h += '<div style="display:flex;gap:8px;justify-content:center;margin-bottom:4px">';
		h +=
			'<div style="background:#FAEEDA;border:1.5px solid #BA7517;border-radius:6px;padding:5px 12px;font-size:13px;font-weight:600;color:#633806">3</div>';
		h +=
			'<div style="background:#FAEEDA;border:1.5px solid #BA7517;border-radius:6px;padding:5px 12px;font-size:13px;font-weight:600;color:#633806">8</div>';
		h += '</div>';
		h += '<div style="display:flex;gap:4px;justify-content:center">';
		h +=
			'<div style="background:#FAEEDA;border:1.5px solid #BA7517;border-radius:6px;padding:5px 10px;font-size:13px;font-weight:600;color:#633806">2</div>';
		h +=
			'<div style="background:#FAEEDA;border:1.5px solid #BA7517;border-radius:6px;padding:5px 10px;font-size:13px;font-weight:600;color:#633806">4</div>';
		h += '</div></div></div>';

		h += '<div><p style="font-size:12px;font-weight:600;color:#0C447C;margin-bottom:8px">Hash table</p>';
		[
			['name', 'bucket 2'],
			['age', 'bucket 0'],
			['city', 'bucket 4'],
		].forEach(function (pair) {
			h += '<div style="display:flex;align-items:center;gap:6px;margin-bottom:4px">';
			h +=
				'<span style="font-size:12px;color:#666;font-family:monospace;width:38px">' +
				pair[0] +
				'</span>';
			h += '<span style="font-size:11px;color:#aaa">\u2192</span>';
			h +=
				'<div style="background:#E6F1FB;border:1.5px solid #185FA5;border-radius:5px;padding:4px 8px;font-size:12px;font-weight:600;color:#0C447C">' +
				pair[1] +
				'</div>';
			h += '</div>';
		});
		h += '</div>';

		h += '</div></div>';
		return h;
	}

	var RICH = {
		beebot: beebotHTML,
		bebot: beebotHTML,
		scratch: scratchHTML,
		bubble: bubbleSortHTML,
		bubblesort: bubbleSortHTML,
		ds: dsHTML,
	};

	global.aiadCcRenderRichIllustration = function (key) {
		if (!key) {
			return '';
		}
		var fn = RICH[key];
		return fn ? fn() : '';
	};
})(typeof window !== 'undefined' ? window : this);
