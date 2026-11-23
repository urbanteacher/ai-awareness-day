/*
 * Links to pages of the main site (the AI Literacy Periodic Table, the home page) are written root-relative, so they
 * work when the courses are served by the WordPress site. When a course runs locally (the desktop launcher or a
 * preview server), those pages don't exist, so send such links to the live site instead.
 */
(function (w, d) {
	'use strict';
	var LIVE = 'https://aiawarenessday.co.uk';
	if (!/^(localhost|127\.0\.0\.1|\[::1\])$/.test(w.location.hostname)) return;
	d.addEventListener('click', function (e) {
		var a = e.target.closest && e.target.closest('a[href^="/"]');
		if (!a) return;
		var href = a.getAttribute('href');
		if (/^\/(previews|css|fonts|brand|js)\//.test(href)) return; /* files the local server does have */
		e.preventDefault();
		var url = LIVE + href;
		if (a.target === '_blank' || e.metaKey || e.ctrlKey) w.open(url, '_blank', 'noopener'); else w.location.href = url;
	}, true);
})(window, document);
