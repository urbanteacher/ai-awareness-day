/**
 * Single resource lesson page (single-resource.php).
 *
 * Video references in the steps seek the player, the big question can go
 * full screen for the board, and the contents list marks the section in view.
 *
 * @package AI_Awareness_Day
 */
(function () {
    'use strict';

    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // ── Step references → seek the lesson video ──
    var media = document.querySelector('[data-rl-media]');
    if (media) {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-rl-seek]');
            if (!btn) return;
            var seconds = parseInt(btn.getAttribute('data-rl-seek'), 10) || 0;

            var video = media.querySelector('video');
            var frame = media.querySelector('iframe');
            if (video) {
                video.currentTime = seconds;
                video.play().catch(function () {});
            } else if (frame && /youtube(-nocookie)?\.com\/embed\//.test(frame.src)) {
                // Needs enablejsapi=1 on the embed, added in aiad_resource_embed_with_api().
                var send = function (func, args) {
                    frame.contentWindow.postMessage(JSON.stringify({ event: 'command', func: func, args: args || [] }), '*');
                };
                send('seekTo', [seconds, true]);
                send('playVideo');
            }

            // On a wide screen the player is pinned beside the steps; on a
            // phone it sits above them, so bring it back into view.
            var box = media.getBoundingClientRect();
            if (box.top < 0 || box.bottom > window.innerHeight) {
                media.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' });
            }
        });
    }

    // ── Big question → full screen for the board ──
    var panel = document.querySelector('[data-rl-question]');
    var project = document.querySelector('[data-rl-project]');
    if (panel && project && document.fullscreenEnabled) {
        project.hidden = false;
        project.addEventListener('click', function () {
            if (document.fullscreenElement) {
                document.exitFullscreen();
            } else {
                panel.requestFullscreen().catch(function () {});
            }
        });
        document.addEventListener('fullscreenchange', function () {
            project.textContent = document.fullscreenElement === panel ? 'Close' : 'Show on the board';
        });
    }

    // ── Debate: one tab per age pathway ──
    // Without script the panels stack under their own headings; this turns
    // them into tabs that open on the pathway the resource is written for.
    var tablist = document.querySelector('[data-rl-tabs]');
    if (tablist) {
        var tabs = Array.prototype.slice.call(tablist.querySelectorAll('[role="tab"]'));
        var select = function (tab, focus) {
            tabs.forEach(function (t) {
                var on = t === tab;
                t.setAttribute('aria-selected', on ? 'true' : 'false');
                t.tabIndex = on ? 0 : -1;
                document.getElementById(t.getAttribute('aria-controls')).hidden = !on;
            });
            if (focus) tab.focus();
        };
        tablist.hidden = false;
        tablist.closest('.rl-debate').classList.add('is-tabbed');
        select(tabs.filter(function (t) { return t.getAttribute('aria-selected') === 'true'; })[0] || tabs[0], false);
        tablist.addEventListener('click', function (e) {
            var tab = e.target.closest('[role="tab"]');
            if (tab) select(tab, false);
        });
        tablist.addEventListener('keydown', function (e) {
            var i = tabs.indexOf(document.activeElement);
            if (i < 0) return;
            var next = { ArrowRight: i + 1, ArrowLeft: i - 1, Home: 0, End: tabs.length - 1 }[e.key];
            if (next === undefined) return;
            e.preventDefault();
            select(tabs[(next + tabs.length) % tabs.length], true);
        });
    }

    // ── Contents list: mark the section being read ──
    var links = document.querySelectorAll('.rl-toc a[href^="#"]');
    if (links.length && 'IntersectionObserver' in window) {
        var byId = {};
        links.forEach(function (a) { byId[a.getAttribute('href').slice(1)] = a; });
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                links.forEach(function (a) { a.removeAttribute('aria-current'); });
                var link = byId[entry.target.id];
                if (link) link.setAttribute('aria-current', 'true');
            });
        }, { rootMargin: '-30% 0px -60% 0px' });
        Object.keys(byId).forEach(function (id) {
            var section = document.getElementById(id);
            if (section) observer.observe(section);
        });
    }
})();
