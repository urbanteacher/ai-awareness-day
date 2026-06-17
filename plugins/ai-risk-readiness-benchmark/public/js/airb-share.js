/**
 * Oversight gauge PNG export and social share helpers.
 */
(function () {
	'use strict';

	window.AIRB = window.AIRB || {};

	var deps = {};

	function oversightZoneColorHex(v) {
		if (v >= 76) return '#3B6D11';
		if (v >= 51) return '#185FA5';
		if (v >= 26) return '#854F0B';
		return '#A32D2D';
	}

	function oversightGaugeGeometry(val) {
		val = Math.max(0, Math.min(100, val));
		var A0 = -120;
		var A1 = 120;
		var cx = 120;
		var cy = 120;
		var rr = 92;
		function toAngle(v) { return A0 + (v / 100) * (A1 - A0); }
		function polar(x, y, rad, deg) {
			var a = (deg - 90) * Math.PI / 180;
			return [x + rad * Math.cos(a), y + rad * Math.sin(a)];
		}
		function arc(x, y, rad, s, e) {
			var p0 = polar(x, y, rad, s);
			var p1 = polar(x, y, rad, e);
			var large = (e - s) <= 180 ? 0 : 1;
			return 'M ' + p0[0].toFixed(2) + ' ' + p0[1].toFixed(2) + ' A ' + rad + ' ' + rad + ' 0 ' + large + ' 1 ' + p1[0].toFixed(2) + ' ' + p1[1].toFixed(2);
		}
		return {
			val: val,
			A0: A0,
			A1: A1,
			cx: cx,
			cy: cy,
			rr: rr,
			toAngle: toAngle,
			polar: polar,
			arc: arc,
			zones: [[0, 10], [10, 25], [25, 50], [50, 100]],
		};
	}

	function drawOversightGaugeOnCanvas(ctx, geom, scale) {
		scale = scale || 1;
		var cx = geom.cx * scale;
		var cy = geom.cy * scale;
		var rr = geom.rr * scale;
		var val = geom.val;
		var lineW = 16 * scale;
		var zones = geom.zones;

		function strokeArc(s, e, color, cap) {
			var start = (geom.toAngle(s) - 90) * Math.PI / 180;
			var end = (geom.toAngle(e) - 90) * Math.PI / 180;
			ctx.beginPath();
			ctx.strokeStyle = color;
			ctx.lineWidth = lineW;
			ctx.lineCap = cap || 'butt';
			ctx.arc(cx, cy, rr, start, end);
			ctx.stroke();
		}

		strokeArc(0, 100, '#e8e8e8', 'round');
		zones.forEach(function (z, i) {
			var cap = (i === 0 || i === zones.length - 1) ? 'round' : 'butt';
			strokeArc(z[0], z[1], oversightZoneColorHex(z[1] - 0.1), cap);
		});

		var npt = geom.polar(cx, cy, rr - (14 * scale), geom.toAngle(val));
		ctx.beginPath();
		ctx.strokeStyle = '#1e1e1e';
		ctx.lineWidth = 3.5 * scale;
		ctx.lineCap = 'round';
		ctx.moveTo(cx, cy);
		ctx.lineTo(npt[0], npt[1]);
		ctx.stroke();

		ctx.beginPath();
		ctx.fillStyle = '#1e1e1e';
		ctx.arc(cx, cy, 7 * scale, 0, Math.PI * 2);
		ctx.fill();

		ctx.fillStyle = '#1e1e1e';
		ctx.textAlign = 'center';
		ctx.textBaseline = 'middle';
		var numStr = String(Math.round(val));
		var numY = cy - (16 * scale);
		ctx.font = 'bold ' + Math.round(42 * scale) + 'px system-ui, -apple-system, Segoe UI, sans-serif';
		ctx.fillText(numStr, cx, numY);
		var numWidth = ctx.measureText(numStr).width;
		ctx.font = '600 ' + Math.round(20 * scale) + 'px system-ui, -apple-system, Segoe UI, sans-serif';
		ctx.textAlign = 'left';
		ctx.fillText('%', cx + (numWidth / 2) + (10 * scale), cy - (26 * scale));
		ctx.textAlign = 'center';
	}

	function wrapCanvasText(ctx, text, maxWidth, maxLines) {
		if (!text) return [];
		var words = String(text).split(/\s+/);
		var lines = [];
		var line = '';
		words.forEach(function (word) {
			var test = line ? line + ' ' + word : word;
			if (ctx.measureText(test).width > maxWidth && line) {
				lines.push(line);
				line = word;
			} else {
				line = test;
			}
		});
		if (line) lines.push(line);
		if (maxLines && lines.length > maxLines) {
			lines = lines.slice(0, maxLines);
			lines[maxLines - 1] = lines[maxLines - 1].replace(/\s+\S*$/, '') + '…';
		}
		return lines;
	}

	function roundRect(ctx, x, y, w, h, r) {
		r = Math.min(r, w / 2, h / 2);
		ctx.beginPath();
		ctx.moveTo(x + r, y);
		ctx.arcTo(x + w, y, x + w, y + h, r);
		ctx.arcTo(x + w, y + h, x, y + h, r);
		ctx.arcTo(x, y + h, x, y, r);
		ctx.arcTo(x, y, x + w, y, r);
		ctx.closePath();
	}

	function canvasToPngBlob(canvas) {
		return new Promise(function (resolve, reject) {
			if (!canvas || !canvas.toBlob) {
				reject(new Error('canvas_unsupported'));
				return;
			}
			canvas.toBlob(function (blob) {
				if (blob) resolve(blob);
				else reject(new Error('export_failed'));
			}, 'image/png', 1);
		});
	}

	function downloadPngBlob(blob, filename) {
		var url = URL.createObjectURL(blob);
		var link = document.createElement('a');
		link.href = url;
		link.download = filename;
		link.rel = 'noopener';
		document.body.appendChild(link);
		link.click();
		link.remove();
		setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
	}

	function oversightShareFilename(val) {
		return 'human-oversight-ratio-' + Math.round(val) + 'pct.png';
	}

	function setGaugeShareStatus(panel, message) {
		var status = panel.querySelector('[data-airb-gauge-share-status]');
		if (!status) return;
		if (message) {
			status.textContent = message;
			status.hidden = false;
		} else {
			status.textContent = '';
			status.hidden = true;
		}
	}

	function oversightGaugeSvg(val, aria) {
		var escFn = deps.esc || (window.AIRB && AIRB.esc) || function (s) { return String(s); };
		var zoneColor = deps.oversightZoneColor || oversightZoneColorHex;
		val = Math.max(0, Math.min(100, val));
		var geom = oversightGaugeGeometry(val);
		var cx = geom.cx;
		var cy = geom.cy;
		var rr = geom.rr;
		var npt = geom.polar(cx, cy, rr - 14, geom.toAngle(val));
		var svg = '<svg viewBox="0 0 240 172" class="airb__gauge-svg" role="img" aria-label="' + escFn(aria || ('Human Oversight Ratio ' + Math.round(val) + '%')) + '">';
		svg += '<path d="' + geom.arc(cx, cy, rr, geom.A0, geom.A1) + '" fill="none" stroke="var(--airb-border)" stroke-width="16" stroke-linecap="round"></path>';
		geom.zones.forEach(function (z, i) {
			var cap = (i === 0 || i === geom.zones.length - 1) ? 'round' : 'butt';
			svg += '<path d="' + geom.arc(cx, cy, rr, geom.toAngle(z[0]), geom.toAngle(z[1])) + '" fill="none" stroke="' + zoneColor(z[1] - 0.1) + '" stroke-width="16" stroke-linecap="' + cap + '"></path>';
		});
		svg += '<line x1="' + cx + '" y1="' + cy + '" x2="' + npt[0].toFixed(2) + '" y2="' + npt[1].toFixed(2) + '" stroke="var(--airb-brand)" stroke-width="3.5" stroke-linecap="round"></line>';
		svg += '<circle cx="' + cx + '" cy="' + cy + '" r="7" fill="var(--airb-brand)"></circle>';
		svg += '<text x="' + cx + '" y="' + (cy - 16) + '" text-anchor="middle" class="airb__gauge-num">';
		svg += '<tspan>' + Math.round(val) + '</tspan>';
		svg += '<tspan font-size="0.45em" baseline-shift="super" dx="0.12em">%</tspan>';
		svg += '</text>';
		return svg + '</svg>';
	}

	function buildOversightSharePngBlob(panel) {
		var i18n = deps.i18n || {};
		var cfg = deps.cfg || {};
		var state = deps.getState ? deps.getState() : {};
		var oversightLabel = deps.oversightLabel || function () { return ''; };

		var titleEl = panel.querySelector('h3');
		var bandEl = panel.querySelector('.airb__gauge-band');
		var helpEl = panel.querySelector('.airb__gauge-help');
		var val = parseInt(panel.getAttribute('data-oversight-value'), 10);
		if (isNaN(val)) {
			var btn = panel.querySelector('[data-oversight-value]');
			val = btn ? parseInt(btn.getAttribute('data-oversight-value'), 10) : NaN;
		}
		if (isNaN(val)) {
			var numEl = panel.querySelector('.airb__gauge-num');
			val = numEl ? parseInt(numEl.textContent, 10) : 0;
		}
		var title = titleEl ? titleEl.textContent.trim() : (i18n.oversight || 'Human Oversight Ratio');
		var band = bandEl ? bandEl.textContent.trim() : '';
		var help = helpEl ? helpEl.textContent.trim() : '';
		var roleLbl = ((cfg.roles || {})[state.role] || state.role || '').trim();
		var eyebrow = (i18n.shareOversightEyebrow || 'AI Awareness Day · {role} benchmark').replace('{role}', roleLbl || 'Benchmark');
		var siteLabel = i18n.shareSiteLabel || 'aiawarenessday.co.uk';
		var width = 1200;
		var height = 630;
		var canvas = document.createElement('canvas');
		canvas.width = width;
		canvas.height = height;
		var ctx = canvas.getContext('2d');
		if (!ctx) return Promise.reject(new Error('canvas_unsupported'));

		ctx.fillStyle = '#f4f7f6';
		ctx.fillRect(0, 0, width, height);
		ctx.fillStyle = '#ffffff';
		roundRect(ctx, 36, 36, width - 72, height - 72, 24);
		ctx.fill();
		ctx.strokeStyle = '#e2e8e4';
		ctx.lineWidth = 2;
		roundRect(ctx, 36, 36, width - 72, height - 72, 24);
		ctx.stroke();

		ctx.fillStyle = '#1e1e1e';
		ctx.fillRect(56, 56, width - 112, 56);
		ctx.fillStyle = '#ffffff';
		ctx.font = '600 24px system-ui, -apple-system, Segoe UI, sans-serif';
		ctx.textAlign = 'left';
		ctx.textBaseline = 'middle';
		ctx.fillText(eyebrow, 80, 84);

		ctx.fillStyle = '#1e1e1e';
		ctx.font = '700 40px system-ui, -apple-system, Segoe UI, sans-serif';
		ctx.fillText(title, 80, 150);

		var gaugeGeom = oversightGaugeGeometry(val);
		ctx.save();
		ctx.translate(250, 250);
		ctx.scale(1.55, 1.55);
		drawOversightGaugeOnCanvas(ctx, gaugeGeom, 1);
		ctx.restore();

		var textX = 560;
		var bandColor = oversightZoneColorHex(val);
		ctx.fillStyle = bandColor;
		ctx.font = '700 34px system-ui, -apple-system, Segoe UI, sans-serif';
		ctx.fillText(band || oversightLabel(val), textX, 250);

		ctx.fillStyle = '#4b5563';
		ctx.font = '500 24px system-ui, -apple-system, Segoe UI, sans-serif';
		var lines = wrapCanvasText(ctx, help, width - textX - 80, 4);
		lines.forEach(function (line, idx) {
			ctx.fillText(line, textX, 310 + (idx * 34));
		});

		ctx.fillStyle = '#6b7280';
		ctx.font = '500 20px system-ui, -apple-system, Segoe UI, sans-serif';
		ctx.fillText(siteLabel, 80, height - 72);
		ctx.textAlign = 'right';
		ctx.fillText(i18n.shareOversightTagline || 'Free AI Risk & Readiness Benchmark', width - 80, height - 72);

		return canvasToPngBlob(canvas);
	}

	function dependencyIndexColorHex(pct) {
		if (pct >= 60) return '#dc2626';
		if (pct >= 35) return '#d97706';
		return '#16a34a';
	}

	function drawDependencyScaleOnCanvas(ctx, pct, x, y, width, height) {
		pct = Math.max(0, Math.min(100, pct));
		var trackH = height;
		var markerW = 14;
		var markerH = height + 16;
		var gradient = ctx.createLinearGradient(x, y, x + width, y);
		gradient.addColorStop(0, '#16a34a');
		gradient.addColorStop(0.5, '#d97706');
		gradient.addColorStop(1, '#dc2626');
		ctx.fillStyle = gradient;
		roundRect(ctx, x, y, width, trackH, trackH / 2);
		ctx.fill();
		var markerX = x + (pct / 100) * width;
		ctx.fillStyle = '#1e1e1e';
		ctx.beginPath();
		ctx.arc(markerX, y + trackH / 2, markerW / 2, 0, Math.PI * 2);
		ctx.fill();
		ctx.strokeStyle = '#ffffff';
		ctx.lineWidth = 3;
		ctx.stroke();
		ctx.fillStyle = '#64748b';
		ctx.font = '600 18px system-ui, -apple-system, Segoe UI, sans-serif';
		ctx.textAlign = 'left';
		ctx.fillText('Non-reliant', x, y + trackH + 28);
		ctx.textAlign = 'right';
		ctx.fillText('Over-reliant', x + width, y + trackH + 28);
		ctx.textAlign = 'left';
	}

	function dependencyShareFilename(val) {
		return 'ai-dependency-index-' + Math.round(val) + 'pct.png';
	}

	function setDependencyShareStatus(panel, message) {
		var status = panel.querySelector('[data-airb-dependency-share-status]');
		if (!status) return;
		if (message) {
			status.textContent = message;
			status.hidden = false;
		} else {
			status.textContent = '';
			status.hidden = true;
		}
	}

	function buildDependencySharePngBlob(panel) {
		var i18n = deps.i18n || {};
		var cfg = deps.cfg || {};
		var state = deps.getState ? deps.getState() : {};
		var titleEl = panel.querySelector('.teacher-dash-metric__label');
		var noteEl = panel.querySelector('.teacher-dash-metric__note');
		var val = parseInt(panel.getAttribute('data-dependency-value'), 10);
		if (isNaN(val)) {
			var depBtn = panel.querySelector('[data-dependency-value]');
			val = depBtn ? parseInt(depBtn.getAttribute('data-dependency-value'), 10) : NaN;
		}
		if (isNaN(val)) {
			var valueEl = panel.querySelector('.teacher-dash-metric__value');
			val = valueEl ? parseInt(String(valueEl.textContent).replace(/[^\d]/g, ''), 10) : 0;
		}
		var title = titleEl ? titleEl.textContent.trim() : (i18n.dependency || 'AI Dependency Index');
		var note = noteEl ? noteEl.textContent.trim() : '';
		var roleLbl = ((cfg.roles || {})[state.role] || state.role || '').trim();
		var eyebrow = (i18n.shareDependencyEyebrow || i18n.shareOversightEyebrow || 'AI Awareness Day · {role} benchmark').replace('{role}', roleLbl || 'Benchmark');
		var siteLabel = i18n.shareSiteLabel || 'aiawarenessday.co.uk';
		var width = 1200;
		var height = 630;
		var canvas = document.createElement('canvas');
		canvas.width = width;
		canvas.height = height;
		var ctx = canvas.getContext('2d');
		if (!ctx) return Promise.reject(new Error('canvas_unsupported'));

		ctx.fillStyle = '#f4f7f6';
		ctx.fillRect(0, 0, width, height);
		ctx.fillStyle = '#ffffff';
		roundRect(ctx, 36, 36, width - 72, height - 72, 24);
		ctx.fill();
		ctx.strokeStyle = '#e2e8e4';
		ctx.lineWidth = 2;
		roundRect(ctx, 36, 36, width - 72, height - 72, 24);
		ctx.stroke();

		ctx.fillStyle = '#1e1e1e';
		ctx.fillRect(56, 56, width - 112, 56);
		ctx.fillStyle = '#ffffff';
		ctx.font = '600 24px system-ui, -apple-system, Segoe UI, sans-serif';
		ctx.textAlign = 'left';
		ctx.textBaseline = 'middle';
		ctx.fillText(eyebrow, 80, 84);

		ctx.fillStyle = '#1e1e1e';
		ctx.font = '700 40px system-ui, -apple-system, Segoe UI, sans-serif';
		ctx.fillText(title, 80, 150);

		var color = dependencyIndexColorHex(val);
		ctx.fillStyle = color;
		ctx.font = '700 96px system-ui, -apple-system, Segoe UI, sans-serif';
		ctx.fillText(String(Math.round(val)) + '%', 80, 270);

		drawDependencyScaleOnCanvas(ctx, val, 80, 320, 520, 18);

		ctx.fillStyle = '#4b5563';
		ctx.font = '500 24px system-ui, -apple-system, Segoe UI, sans-serif';
		var lines = wrapCanvasText(ctx, note, width - 680, 3);
		lines.forEach(function (line, idx) {
			ctx.fillText(line, 680, 300 + (idx * 34));
		});

		ctx.fillStyle = '#6b7280';
		ctx.font = '500 20px system-ui, -apple-system, Segoe UI, sans-serif';
		ctx.fillText(siteLabel, 80, height - 72);
		ctx.textAlign = 'right';
		ctx.fillText(i18n.shareDependencyTagline || i18n.shareOversightTagline || 'Free AI Risk & Readiness Benchmark', width - 80, height - 72);

		return canvasToPngBlob(canvas);
	}

	function shareDependencyIndexImage(btn) {
		var i18n = deps.i18n || {};
		var panel = btn.closest('.teacher-dash-metric--dependency');
		if (!panel || btn.disabled) return;
		var val = parseInt(btn.getAttribute('data-dependency-value'), 10) || 0;
		var defaultLabel = btn.textContent.trim();
		var sharingLabel = i18n.shareDependencyIndexSharing || i18n.shareOversightGaugeSharing || 'Creating image…';
		var doneLabel = i18n.shareDependencyIndexDone || i18n.shareOversightGaugeDone || 'Image ready';
		var errorLabel = i18n.shareDependencyIndexError || i18n.shareOversightGaugeError || 'Could not create image. Try again.';
		btn.disabled = true;
		btn.textContent = sharingLabel;
		setDependencyShareStatus(panel, sharingLabel);

		buildDependencySharePngBlob(panel).then(function (blob) {
			var file = new File([blob], dependencyShareFilename(val), { type: 'image/png' });
			var shareText = (i18n.shareDependencyShareText || 'My AI Dependency Index from the AI Awareness Day benchmark: {pct}%.')
				.replace('{pct}', String(Math.round(val)))
				.trim();
			var shareUrl = i18n.shareSiteUrl || (window.airbBenchmark && airbBenchmark.homeUrl) || window.location.href;
			var shareTitle = (panel.querySelector('.teacher-dash-metric__label') || {}).textContent || 'AI Dependency Index';

			if (navigator.share && navigator.canShare && navigator.canShare({ files: [file] })) {
				return navigator.share({
					files: [file],
					title: shareTitle.trim(),
					text: shareText,
					url: shareUrl,
				}).catch(function (err) {
					if (err && err.name === 'AbortError') return;
					downloadPngBlob(blob, dependencyShareFilename(val));
				});
			}
			downloadPngBlob(blob, dependencyShareFilename(val));
		}).then(function () {
			setDependencyShareStatus(panel, doneLabel);
			if (deps.trackEvent) {
				deps.trackEvent('dependency_index_share', { value: val, role: (deps.getState && deps.getState().role) || '' });
			}
		}).catch(function () {
			setDependencyShareStatus(panel, errorLabel);
		}).finally(function () {
			btn.disabled = false;
			btn.textContent = defaultLabel;
			setTimeout(function () { setDependencyShareStatus(panel, ''); }, 2500);
		});
	}

	function shareOversightGaugeImage(btn) {
		var i18n = deps.i18n || {};
		var panel = btn.closest('.airb__res-panel--gauge');
		if (!panel || btn.disabled) return;
		var val = parseInt(btn.getAttribute('data-oversight-value'), 10) || 0;
		var defaultLabel = btn.textContent.trim();
		var sharingLabel = i18n.shareOversightGaugeSharing || 'Creating image…';
		var doneLabel = i18n.shareOversightGaugeDone || 'Image ready';
		var errorLabel = i18n.shareOversightGaugeError || 'Could not create image. Try again.';
		btn.disabled = true;
		btn.textContent = sharingLabel;
		setGaugeShareStatus(panel, sharingLabel);

		buildOversightSharePngBlob(panel).then(function (blob) {
			var file = new File([blob], oversightShareFilename(val), { type: 'image/png' });
			var shareText = (i18n.shareOversightShareText || 'My Human Oversight Ratio from the AI Awareness Day benchmark: {pct}% — {band}.')
				.replace('{pct}', String(Math.round(val)))
				.replace('{band}', (panel.querySelector('.airb__gauge-band') || {}).textContent || '')
				.trim();
			var shareUrl = i18n.shareSiteUrl || (window.airbBenchmark && airbBenchmark.homeUrl) || window.location.href;
			var shareTitle = i18n.oversight || 'Human Oversight Ratio';

			if (navigator.share && navigator.canShare && navigator.canShare({ files: [file] })) {
				return navigator.share({
					files: [file],
					title: shareTitle,
					text: shareText,
					url: shareUrl,
				}).catch(function (err) {
					if (err && err.name === 'AbortError') return;
					downloadPngBlob(blob, oversightShareFilename(val));
				});
			}
			downloadPngBlob(blob, oversightShareFilename(val));
		}).then(function () {
			setGaugeShareStatus(panel, doneLabel);
			if (deps.trackEvent) {
				deps.trackEvent('oversight_gauge_share', { value: val, role: (deps.getState && deps.getState().role) || '' });
			}
		}).catch(function () {
			setGaugeShareStatus(panel, errorLabel);
		}).finally(function () {
			btn.disabled = false;
			btn.textContent = defaultLabel;
			setTimeout(function () { setGaugeShareStatus(panel, ''); }, 2500);
		});
	}

	AIRB.Share = {
		init: function (options) {
			deps = options || {};
		},
		oversightGaugeGeometry: oversightGaugeGeometry,
		oversightGaugeSvg: oversightGaugeSvg,
		drawOversightGaugeOnCanvas: drawOversightGaugeOnCanvas,
		buildDependencySharePngBlob: buildDependencySharePngBlob,
		shareDependencyIndexImage: shareDependencyIndexImage,
		buildOversightSharePngBlob: buildOversightSharePngBlob,
		shareOversightGaugeImage: shareOversightGaugeImage,
		canvasToPngBlob: canvasToPngBlob,
	};
}());
