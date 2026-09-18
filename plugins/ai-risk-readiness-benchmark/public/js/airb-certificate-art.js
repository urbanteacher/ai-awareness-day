/**
 * airb-certificate-art.js
 *
 * Draws the AI Risk & Readiness Benchmark certificate on a canvas.
 *
 * Split out of airb-certificate.js so the same renderer can draw the real
 * certificate in the results dashboard and the example on marketing pages —
 * including from the theme, which does not load the benchmark app. One
 * renderer means the example cannot promise something the real one does not
 * deliver.
 *
 * Standalone: no dependencies, no localized settings.
 * Exposes: AIRB.CertificateArt
 */
'use strict';

(function () {
	window.AIRB = window.AIRB || {};
	var Art = AIRB.CertificateArt || {};
	AIRB.CertificateArt = Art;

	var esc = AIRB.esc || function (s) {
		return String(s == null ? '' : s)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	};

	/* ---------------------------------------------------------------
	 * Certificate artwork
	 *
	 * Drawn on a canvas rather than built from DOM so that the thing on
	 * screen and the thing that downloads are the same pixels. The old
	 * preview was DOM, and "download" only ever opened a print window —
	 * so there was no file, and no second design to keep in step either.
	 *
	 * A4 landscape proportions at 2x, so it prints without resampling.
	 * ------------------------------------------------------------- */
	var CERT_W = 2000;
	var CERT_H = 1414;

	/* Bright = ground, deep = ink. Mirrors the AiAd27 tokens in the theme's
	   base/reset.css; the plugin ships standalone so it cannot read them. */
	var STRANDS = {
		safe: { bright: '#00BEDD', deep: '#006A7D', label: 'Safe' },
		smart: { bright: '#FF7038', deep: '#A7350B', label: 'Smart' },
		creative: { bright: '#AC91FF', deep: '#6441B8', label: 'Creative' },
		responsible: { bright: '#63DF93', deep: '#176E3B', label: 'Responsible' },
		future: { bright: '#FA83EB', deep: '#983488', label: 'Future' },
	};
	var INK = '#231F20';
	var CREAM = '#F6F4ED';
	var DIM = '#54504E';
	var RULE = '#C9C6BE';

	function strandFor(theme) {
		return STRANDS[String(theme || '').toLowerCase()] || STRANDS.safe;
	}

	/* Derived from this file's own URL rather than a localized setting, so
	   the module works anywhere it is enqueued — including from the theme,
	   which has no airbBenchmark object. */
	var SELF_SRC = (document.currentScript && document.currentScript.src) || '';
	function lockupUrl() {
		if (SELF_SRC) return SELF_SRC.replace(/public\/js\/[^/]*$/, 'public/img/aiad27-lockup.svg').split('?')[0];
		var base = (window.airbBenchmark && airbBenchmark.pluginUrl) || '';
		return base ? base.replace(/\/$/, '') + '/public/img/aiad27-lockup.svg' : '';
	}

	var lockupPromise = null;
	function loadLockup() {
		if (lockupPromise) return lockupPromise;
		lockupPromise = new Promise(function (resolve) {
			var url = lockupUrl();
			if (!url) return resolve(null);
			var img = new Image();
			img.onload = function () { resolve(img); };
			img.onerror = function () { resolve(null); };
			img.src = url;
		});
		return lockupPromise;
	}

	/* Canvas text uses document fonts, but only once they have actually
	   loaded — otherwise the first paint silently falls back to Arial. */
	function fontsReady() {
		if (!document.fonts || !document.fonts.ready) return Promise.resolve();
		return document.fonts.load('700 100px "AIAD Sans"')
			.catch(function () {})
			.then(function () { return document.fonts.ready; })
			.catch(function () {});
	}

	function font(weight, size) {
		return weight + ' ' + size + 'px "AIAD Sans", Arial, sans-serif';
	}

	/* The chamfer is the brand's signature shape: square corners except one
	   cut flat. */
	function chamferPath(ctx, x, y, w, h, cut) {
		ctx.beginPath();
		ctx.moveTo(x + cut, y);
		ctx.lineTo(x + w, y);
		ctx.lineTo(x + w, y + h - cut);
		ctx.lineTo(x + w - cut, y + h);
		ctx.lineTo(x, y + h);
		ctx.lineTo(x, y + cut);
		ctx.closePath();
	}

	function wrapLines(ctx, text, maxWidth) {
		var words = String(text || '').split(/\s+/).filter(Boolean);
		var lines = [];
		var line = '';
		words.forEach(function (word) {
			var attempt = line ? line + ' ' + word : word;
			if (ctx.measureText(attempt).width > maxWidth && line) {
				lines.push(line);
				line = word;
			} else {
				line = attempt;
			}
		});
		if (line) lines.push(line);
		return lines;
	}

	/* Shrink to fit rather than clip: a long name is still the point of the
	   certificate. */
	function fitFont(ctx, text, maxWidth, weight, startSize, minSize) {
		var size = startSize;
		ctx.font = font(weight, size);
		while (size > minSize && ctx.measureText(text).width > maxWidth) {
			size -= 4;
			ctx.font = font(weight, size);
		}
		return size;
	}

	function drawCertificate(canvas, data) {
		var ctx = canvas.getContext('2d');
		var strand = strandFor(data.theme);
		canvas.width = CERT_W;
		canvas.height = CERT_H;

		ctx.fillStyle = CREAM;
		ctx.fillRect(0, 0, CERT_W, CERT_H);

		/* Strand band down the left edge, chamfered at the foot. */
		var bandW = 118;
		ctx.fillStyle = strand.bright;
		ctx.beginPath();
		ctx.moveTo(0, 0);
		ctx.lineTo(bandW, 0);
		ctx.lineTo(bandW, CERT_H - 150);
		ctx.lineTo(bandW - 150, CERT_H);
		ctx.lineTo(0, CERT_H);
		ctx.closePath();
		ctx.fill();

		var left = bandW + 140;
		var right = CERT_W - 140;
		var width = right - left;

		ctx.textBaseline = 'alphabetic';
		ctx.textAlign = 'left';

		var y = 250;

		ctx.fillStyle = strand.deep;
		ctx.font = font(700, 34);
		var kicker = 'AI RISK & READINESS BENCHMARK™  ·  ' + strand.label.toUpperCase();
		ctx.letterSpacing = '4px';
		ctx.fillText(kicker, left, y);
		ctx.letterSpacing = '0px';

		y += 120;
		ctx.fillStyle = INK;
		ctx.font = font(700, 96);
		ctx.fillText('Certificate of', left, y);
		y += 104;
		ctx.fillText('Completion', left, y);

		y += 110;
		ctx.fillStyle = DIM;
		ctx.font = font(400, 40);
		ctx.fillText('This certifies that', left, y);

		y += 130;
		var nameSize = fitFont(ctx, data.name || 'Name pending', width, 700, 132, 56);
		ctx.fillStyle = strand.deep;
		ctx.font = font(700, nameSize);
		ctx.fillText(data.name || 'Name pending', left, y);

		y += 40;
		ctx.strokeStyle = strand.bright;
		ctx.lineWidth = 8;
		ctx.beginPath();
		ctx.moveTo(left, y);
		ctx.lineTo(left + Math.min(320, width), y);
		ctx.stroke();

		y += 90;
		ctx.fillStyle = INK;
		ctx.font = font(400, 40);
		wrapLines(ctx, data.body, width).slice(0, 3).forEach(function (line) {
			ctx.fillText(line, left, y);
			y += 58;
		});

		y += 46;
		ctx.fillStyle = INK;
		ctx.font = font(700, 44);
		ctx.fillText('Awarded ' + (data.awarded || ''), left, y);

		/* Footer rule and the verification details. */
		var footY = CERT_H - 150;
		ctx.strokeStyle = RULE;
		ctx.lineWidth = 3;
		ctx.beginPath();
		ctx.moveTo(left, footY);
		ctx.lineTo(right, footY);
		ctx.stroke();

		ctx.fillStyle = DIM;
		ctx.font = font(400, 30);
		ctx.fillText('Certificate ID ' + (data.certificateId || 'Pending'), left, footY + 58);
		ctx.fillText(data.verifyUrl || 'aiawarenessday.co.uk', left, footY + 104);

		return loadLockup().then(function (img) {
			if (!img) return canvas;
			/* The lockup is right-anchored in its own viewBox, so its right
			   edge is the type's right edge — align it to the margin. */
			var lw = 520;
			var lh = lw * (img.naturalHeight || 56) / (img.naturalWidth || 300);
			ctx.drawImage(img, right - lw, 170 - lh, lw, lh);
			return canvas;
		});
	}



	/* The canvas carries its own data, so a repaint never needs the state
	   around it — and the markup can be produced server-side or client-side. */
	Art.previewHtml = function (data) {
		data = data || {};
		var name = data.participantName || data.name || 'Name pending';
		var awarded = data.awarded || 'on completion';
		var alt = 'AI Risk & Readiness Benchmark certificate for ' + name + ', awarded ' + awarded +
			'. Certificate ID ' + (data.certificateId || 'pending') + '.';
		return '<div class="certificate-preview" data-airb-certificate-preview' +
			' data-cert-name="' + esc(name) + '"' +
			' data-cert-body="' + esc(data.body || '') + '"' +
			' data-cert-awarded="' + esc(awarded) + '"' +
			' data-cert-id="' + esc(data.certificateId || 'Pending') + '"' +
			' data-cert-verify="' + esc(data.verifyUrl || 'aiawarenessday.co.uk') + '"' +
			' data-cert-theme="' + esc(data.theme || '') + '">' +
			'<canvas class="certificate-preview__canvas" role="img" aria-label="' + esc(data.alt || alt) + '"></canvas>' +
			'</div>';
	};

	Art.paint = function (scope) {
		if (!scope) return Promise.resolve();
		var hosts = scope.matches && scope.matches('[data-airb-certificate-preview]')
			? [scope]
			: [].slice.call(scope.querySelectorAll('[data-airb-certificate-preview]'));
		if (!hosts.length) return Promise.resolve();
		return fontsReady().then(function () {
			return Promise.all(hosts.map(function (host) {
				var canvas = host.querySelector('canvas');
				if (!canvas) return null;
				return drawCertificate(canvas, {
					name: host.dataset.certName,
					body: host.dataset.certBody,
					awarded: host.dataset.certAwarded,
					certificateId: host.dataset.certId,
					verifyUrl: host.dataset.certVerify,
					theme: host.dataset.certTheme,
				});
			}));
		});
	};

	Art.canvasIn = function (scope) {
		var host = scope && (scope.matches && scope.matches('[data-airb-certificate-preview]')
			? scope
			: scope.querySelector('[data-airb-certificate-preview]'));
		return host ? host.querySelector('canvas') : null;
	};

	/**
	 * Fill any `[data-airb-cert-showcase-art]` slot with the example
	 * certificate and paint it. Mounts itself, so a page only has to drop
	 * the slot in and enqueue this file.
	 */
	Art.mountShowcase = function (root) {
		var scope = root || document;
		[].slice.call(scope.querySelectorAll('[data-airb-cert-showcase-art]')).forEach(function (slot) {
			if (slot.dataset.airbMounted) return;
			slot.dataset.airbMounted = '1';
			var theme = slot.dataset.certTheme || 'safe';
			slot.innerHTML = Art.previewHtml({
				name: slot.dataset.certName || 'Your name here',
				body: slot.dataset.certBody || 'has completed the AI Risk & Readiness Benchmark\u2122 and submitted evidence of a responsible classroom AI action linked to AI Awareness Day.',
				awarded: slot.dataset.certAwarded || formatToday(),
				certificateId: 'Example',
				verifyUrl: 'aiawarenessday.co.uk',
				theme: theme,
				alt: 'Example AI Risk & Readiness Benchmark certificate, issued in the ' + strandFor(theme).label + ' strand, with your name on it.',
			});
			Art.paint(slot);
		});
	};

	function formatToday() {
		try {
			return new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
		} catch (e) {
			return '';
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () { Art.mountShowcase(); });
	} else {
		Art.mountShowcase();
	}

	Art.draw = drawCertificate;
	Art.strandFor = strandFor;
}());
