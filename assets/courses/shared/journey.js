/*
 * "You are here" journey map, shared by the course pages in assets/courses/.
 *
 * Turns <nav class="route"> (links to #ch0, #ch1, ...) into a sticky map: a line through every chapter, filled up to
 * where you are, with a dot that travels smoothly between chapters as you scroll and takes the colour of the current
 * chapter's theme (--c / --cd on the section). Chapters you've passed are filled in; the current one is marked with
 * aria-current="step".
 */
(function (w, d) {
	'use strict';
	var nav = d.querySelector('nav.route');
	if (!nav) return;
	var links = [].slice.call(nav.querySelectorAll('a[href^="#"]'));
	var secs = links.map(function (a) { return d.getElementById(a.getAttribute('href').slice(1)); });
	if (!links.length || secs.some(function (s) { return !s; })) return;

	var total = links.length - 1; // the first stop is the prologue, numbered 0
	nav.classList.add('yah');
	var head = d.createElement('div');
	head.className = 'yah-head';
	head.innerHTML = '<span class="k">Your journey</span><span class="now" aria-live="polite"></span>';
	var scroller = d.createElement('div');
	scroller.className = 'yah-scroll';
	var track = d.createElement('div');
	track.className = 'yah-track';
	track.innerHTML = '<i class="yah-line"></i><i class="yah-fill"></i><span class="yah-dot off" aria-hidden="true"><b>You are here</b></span>';
	links.forEach(function (a) { track.appendChild(a); });
	scroller.appendChild(track);
	nav.appendChild(head);
	nav.appendChild(scroller);
	var now = head.querySelector('.now'), line = track.querySelector('.yah-line'), fill = track.querySelector('.yah-fill'), dot = track.querySelector('.yah-dot');
	var here = -2, navTop = 0, queued = false;

	function name(a) { var t = a.cloneNode(true), b = t.querySelector('b'); if (b) b.remove(); return t.textContent.trim(); }
	function centre(a) { return a.offsetLeft + a.offsetWidth / 2; }

	function layout() {
		var c0 = centre(links[0]), cN = centre(links[links.length - 1]);
		line.style.left = fill.style.left = c0 + 'px';
		line.style.width = (cN - c0) + 'px';
		d.documentElement.style.setProperty('--yah-h', nav.offsetHeight + 'px');
		var prev = nav.previousElementSibling;
		navTop = prev ? prev.getBoundingClientRect().bottom + w.scrollY : 0;
	}

	function update() {
		queued = false;
		var y = w.scrollY + nav.offsetHeight + w.innerHeight * 0.25;
		var tops = secs.map(function (s) { return s.getBoundingClientRect().top + w.scrollY; });
		var i = -1, frac = 0;
		for (var k = 0; k < secs.length; k++) if (y >= tops[k]) i = k;
		if (i >= 0) {
			var end = i < secs.length - 1 ? tops[i + 1] : tops[i] + secs[i].offsetHeight;
			frac = Math.max(0, Math.min(1, (y - tops[i]) / Math.max(1, end - tops[i])));
		}
		var c0 = centre(links[0]);
		var x = i < 0 ? c0 : i < links.length - 1 ? centre(links[i]) + frac * (centre(links[i + 1]) - centre(links[i])) : centre(links[i]);
		fill.style.width = Math.max(0, x - c0) + 'px';
		dot.style.left = x + 'px';
		dot.classList.toggle('off', i < 0);
		nav.classList.toggle('stuck', w.scrollY > navTop + 2);
		if (i !== here) {
			here = i;
			links.forEach(function (a, k) {
				a.classList.toggle('past', k < i);
				a.classList.toggle('here', k === i);
				if (k === i) a.setAttribute('aria-current', 'step'); else a.removeAttribute('aria-current');
			});
			if (i < 0) {
				now.textContent = 'Scroll down to start';
			} else {
				var cs = w.getComputedStyle(secs[i]);
				nav.style.setProperty('--yc', cs.getPropertyValue('--c').trim() || '#00BEDD');
				nav.style.setProperty('--yd', cs.getPropertyValue('--cd').trim() || '#231F20');
				now.innerHTML = (i === 0 ? 'Prologue' : 'Chapter ' + i + ' of ' + total) + ' · <b>' + name(links[i]).replace(/</g, '&lt;') + '</b>';
				/* keep the current stop in view when the strip is wider than the screen */
				var a = links[i], left = a.offsetLeft - scroller.clientWidth / 2 + a.offsetWidth / 2;
				if (a.offsetLeft < scroller.scrollLeft || a.offsetLeft + a.offsetWidth > scroller.scrollLeft + scroller.clientWidth) {
					scroller.scrollTo({ left: left, behavior: 'smooth' });
				}
			}
		}
	}

	function queue() { if (!queued) { queued = true; w.requestAnimationFrame(update); } }
	w.addEventListener('scroll', queue, { passive: true });
	w.addEventListener('resize', function () { layout(); queue(); });
	w.addEventListener('load', function () { layout(); update(); });
	layout();
	update();
})(window, document);
