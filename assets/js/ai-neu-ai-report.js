/**
 * NEU State of Education: AI Report 2026 — FT-style editorial + data ([aiad_neu_ai_report])
 */
(function () {
	'use strict';

	var CONFIG = window.aiadNeuReportData || {};
	var SECTIONS = CONFIG.sections || [];
	var EDITORIAL = CONFIG.editorial || {};
	var STRINGS = CONFIG.strings || {};
	var activeLens = 'teacher';

	var prefersReducedMotion =
		typeof window.matchMedia === 'function' &&
		window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function el(id) {
		return document.getElementById(id);
	}

	function escapeHtml(str) {
		var d = document.createElement('div');
		d.textContent = String(str);
		return d.innerHTML;
	}

	function label(key, fallback) {
		return STRINGS[key] || fallback;
	}

	function setLens(lens, btn) {
		activeLens = lens;
		document.querySelectorAll('#aiad-neu-report .nr-lens-btn').forEach(function (b) {
			var active = b === btn;
			b.setAttribute('aria-pressed', active ? 'true' : 'false');
			b.setAttribute('aria-selected', active ? 'true' : 'false');
		});
		document.querySelectorAll('#aiad-neu-report .nr-lc').forEach(function (node) {
			node.classList.remove('is-active');
		});
		document.querySelectorAll('#aiad-neu-report .nr-lc.l-' + lens).forEach(function (node) {
			node.classList.add('is-active');
		});
		animateBars();
	}

	function animateBars() {
		var fills = document.querySelectorAll('#aiad-neu-report .nr-bar-fill');
		fills.forEach(function (node) {
			var pct = parseInt(node.getAttribute('data-pct') || '0', 10);
			if (isNaN(pct)) {
				return;
			}
			var width = Math.min(pct, 100) + '%';
			if (prefersReducedMotion) {
				node.style.width = width;
				return;
			}
			node.style.width = '0%';
		});

		if (prefersReducedMotion) {
			return;
		}

		setTimeout(function () {
			fills.forEach(function (node) {
				var pct = parseInt(node.getAttribute('data-pct') || '0', 10);
				if (!isNaN(pct)) {
					node.style.width = Math.min(pct, 100) + '%';
				}
			});
		}, 100);
	}

	function buildDataTable(sec, chartTitle, rows) {
		if (!rows || !rows.length) {
			return null;
		}

		var wrap = document.createElement('div');
		wrap.className = 'nr-data-table-wrap';

		var caption = document.createElement('caption');
		caption.textContent =
			label('dataTableCaption', 'Data table') + ': ' + sec.title + ' — ' + chartTitle;

		var table = document.createElement('table');
		table.className = 'nr-data-table screen-reader-text';
		table.appendChild(caption);

		var thead = document.createElement('thead');
		thead.innerHTML =
			'<tr><th scope="col">Metric</th><th scope="col">Value (%)</th><th scope="col">Source</th></tr>';
		table.appendChild(thead);

		var tbody = document.createElement('tbody');
		rows.forEach(function (row) {
			var tr = document.createElement('tr');
			tr.innerHTML =
				'<td>' +
				escapeHtml(row.metric) +
				'</td><td>' +
				escapeHtml(String(row.pct)) +
				'</td><td>' +
				escapeHtml(row.source || 'NEU') +
				'</td>';
			tbody.appendChild(tr);
		});
		table.appendChild(tbody);
		wrap.appendChild(table);
		return wrap;
	}

	function rowsFromBars(sec) {
		if (!sec.bars) {
			return [];
		}
		return sec.bars.map(function (b) {
			return {
				metric: b.l + (b.sub ? ' (' + b.sub + ')' : ''),
				pct: b.pct,
				source: b.source || (sec.id === 'policy' ? 'Sutton Trust 2025' : 'NEU 2026'),
			};
		});
	}

	function rowsFromFigures(items) {
		if (!items) {
			return [];
		}
		return items.map(function (item) {
			return {
				metric: item.l,
				pct: item.pct != null ? item.pct : parseInt(String(item.n).replace(/[^0-9]/g, ''), 10),
				source: 'NEU 2026',
			};
		});
	}

	function appendProse(lc, ld) {
		if (ld.prose) {
			var prose = document.createElement('div');
			prose.className = 'nr-prose';
			prose.innerHTML = ld.prose;
			lc.appendChild(prose);
			return;
		}

		if (ld.insight || ld.extra) {
			var fallback = document.createElement('div');
			fallback.className = 'nr-prose';
			var html = '';
			if (ld.insight) {
				html += '<p>' + ld.insight + '</p>';
			}
			if (ld.extra) {
				html += '<p>' + escapeHtml(ld.extra) + '</p>';
			}
			fallback.innerHTML = html;
			lc.appendChild(fallback);
		}
	}

	function renderFigures(items) {
		var row = document.createElement('div');
		row.className = 'nr-figures';
		items.forEach(function (item, index) {
			var fig = document.createElement('figure');
			fig.className = 'nr-figure nr-figure--' + ((index % 4) + 1);
			var value = document.createElement('p');
			value.className = 'nr-figure-value';
			value.textContent = item.n || String(item.pct) + '%';
			var cap = document.createElement('figcaption');
			cap.className = 'nr-figure-cap';
			cap.textContent = item.l;
			fig.appendChild(value);
			fig.appendChild(cap);
			row.appendChild(fig);
		});
		return row;
	}

	function appendChartBlock(lc, sec, chartTitle, chartId, renderVisual, rows) {
		var block = document.createElement('div');
		block.className = 'nr-chart';

		var chartLbl = document.createElement('p');
		chartLbl.className = 'nr-chart-lbl';
		chartLbl.id = chartId + '-lbl';
		chartLbl.textContent = chartTitle;
		block.appendChild(chartLbl);

		var visual = renderVisual();
		if (visual) {
			visual.setAttribute('role', 'img');
			visual.setAttribute('aria-labelledby', chartId + '-lbl');
			block.appendChild(visual);
		}

		var table = buildDataTable(sec, chartTitle, rows);
		if (table) {
			block.appendChild(table);
		}

		lc.appendChild(block);
	}

	function renderEditorial() {
		var headline = el('aiad-neu-headline');
		if (headline && EDITORIAL.headline) {
			headline.textContent = EDITORIAL.headline;
		}

		var introduction = el('aiad-neu-introduction');
		if (introduction && EDITORIAL.introduction) {
			introduction.innerHTML = EDITORIAL.introduction;
		}

		var dataLead = el('aiad-neu-data-lead');
		if (dataLead && EDITORIAL.dataLead) {
			dataLead.textContent = EDITORIAL.dataLead;
		}

		var scope = el('aiad-neu-scope');
		if (scope) {
			var note = EDITORIAL.methodology || CONFIG.surveyNote || '';
			if (note) {
				scope.textContent = note;
			}
		}
	}

	function render() {
		var wrap = el('aiad-neu-sections');
		if (!wrap || !SECTIONS.length) {
			return;
		}

		wrap.innerHTML = '';

		SECTIONS.forEach(function (sec) {
			var block = document.createElement('article');
			block.className = 'nr-section';
			block.id = 'nr-sec-' + sec.id;

			var heading = document.createElement('header');
			heading.className = 'nr-sec-heading';
			heading.innerHTML =
				'<h3 class="nr-sec-title" id="nr-head-' +
				sec.id +
				'">' +
				escapeHtml(sec.title) +
				'</h3>';

			var body = document.createElement('div');
			body.className = 'nr-sec-body';
			body.id = 'nr-body-' + sec.id;
			body.setAttribute('aria-labelledby', 'nr-head-' + sec.id);

			['teacher', 'leader'].forEach(function (lens) {
				var ld = sec[lens];
				if (!ld) {
					return;
				}

				var lc = document.createElement('div');
				lc.className = 'nr-lc l-' + lens + (lens === activeLens ? ' is-active' : '');

				appendProse(lc, ld);

				if (sec.split) {
					appendChartBlock(
						lc,
						sec,
						'By phase',
						'chart-' + sec.id + '-' + lens + '-split',
						function () {
							return renderFigures(sec.split);
						},
						rowsFromFigures(sec.split)
					);
				}

				if (sec.bars) {
					var barsTitle = sec.chartLbl || label('defaultChartLbl', 'Share of teachers surveyed (%)');
					appendChartBlock(
						lc,
						sec,
						barsTitle,
						'chart-' + sec.id + '-' + lens + '-bars',
						function () {
							var bars = document.createElement('div');
							bars.className = 'nr-bars';
							sec.bars.forEach(function (b, bi) {
								var row = document.createElement('div');
								row.className = 'nr-bar-row';
								var barLabel = b.l + (b.sub ? ' (' + b.sub + ')' : '');
								row.innerHTML =
									'<span class="nr-bar-lbl">' +
									escapeHtml(barLabel) +
									'</span><div class="nr-bar-track"><div class="nr-bar-fill" id="bf-' +
									sec.id +
									'-' +
									lens +
									'-' +
									bi +
									'" data-pct="' +
									b.pct +
									'" aria-hidden="true"></div></div><span class="nr-bar-pct">' +
									b.pct +
									'%</span>';
								bars.appendChild(row);
							});
							return bars;
						},
						rowsFromBars(sec)
					);

					if (sec.note) {
						var note = document.createElement('p');
						note.className = 'nr-note';
						note.textContent = sec.note;
						lc.appendChild(note);
					}
				}

				if (sec.policy) {
					appendChartBlock(
						lc,
						sec,
						'School policy (NEU)',
						'chart-' + sec.id + '-' + lens + '-policy',
						function () {
							return renderFigures(sec.policy);
						},
						rowsFromFigures(sec.policy)
					);
				}

				if (sec.stats) {
					appendChartBlock(
						lc,
						sec,
						'Views on the DfE AI tutor plan',
						'chart-' + sec.id + '-' + lens + '-stats',
						function () {
							return renderFigures(sec.stats);
						},
						rowsFromFigures(sec.stats)
					);
				}

				if (sec.quote) {
					var quote = document.createElement('blockquote');
					quote.className = 'nr-quote';
					quote.innerHTML =
						escapeHtml(sec.quote.t) + '<cite>— ' + escapeHtml(sec.quote.c) + '</cite>';
					lc.appendChild(quote);
				}

				if (sec.sourceNote) {
					var src = document.createElement('p');
					src.className = 'nr-source-note';
					src.innerHTML = sec.sourceNote;
					lc.appendChild(src);
				}

				if (ld.actions) {
					var list = document.createElement('ol');
					list.className = 'nr-actions';
					ld.actions.forEach(function (a) {
						var item = document.createElement('li');
						item.innerHTML =
							'<strong>' + escapeHtml(a.title) + '</strong>' + escapeHtml(a.body);
						list.appendChild(item);
					});
					lc.appendChild(list);
				}

				body.appendChild(lc);
			});

			block.appendChild(heading);
			block.appendChild(body);
			wrap.appendChild(block);
		});

		animateBars();
	}

	function bind() {
		var root = el('aiad-neu-report');
		if (!root || root.getAttribute('data-nr-bound') === '1') {
			return;
		}
		root.setAttribute('data-nr-bound', '1');

		document.querySelectorAll('#aiad-neu-report .nr-lens-btn').forEach(function (btn) {
			btn.addEventListener('click', function () {
				setLens(btn.getAttribute('data-lens'), btn);
			});
		});

		renderEditorial();
		render();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', bind);
	} else {
		bind();
	}
})();
