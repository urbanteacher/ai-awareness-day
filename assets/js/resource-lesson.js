/**
 * Single resource lesson page (single-resource.php).
 *
 * Video references in the steps seek the player, the big question and the
 * slides can go full screen for the board, and the contents list marks the
 * section in view.
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

    // ── Big question and slides → the whole screen, for the board ──
    // Tries real full screen. Where the browser refuses (an embedded view, an
    // older iPad) the element fills the window instead, so the button always
    // does something. Esc or the Close button puts it back.
    var toBoard = function (target, button, onChange) {
        if (!target || !button) return;
        var label = button.textContent.trim();
        var on = false;
        var real = false;
        var set = function (now) {
            if (now === on) return;
            on = now;
            target.classList.toggle('is-board', now);
            button.textContent = now ? 'Close' : label;
            if (onChange) onChange(now);
        };
        var open = function () {
            set(true);
            if (!target.requestFullscreen) return;
            try {
                target.requestFullscreen().then(function () {
                    if (on) {
                        real = document.fullscreenElement === target;
                    } else if (document.fullscreenElement === target) {
                        document.exitFullscreen(); // closed while the request was pending
                    }
                }, function () {});
            } catch (err) {}
        };
        var close = function () {
            if (real && document.fullscreenElement) document.exitFullscreen().catch(function () {});
            real = false;
            set(false);
        };
        button.hidden = false;
        button.addEventListener('click', function () {
            if (on) close(); else open();
        });
        document.addEventListener('fullscreenchange', function () {
            // Left full screen from the browser (Esc): leave board mode too.
            if (real && !document.fullscreenElement) {
                real = false;
                set(false);
            }
        });
        document.addEventListener('keydown', function (e) {
            // Real full screen handles Esc itself; the window fallback does not.
            if (e.key === 'Escape' && on && !real) set(false);
        });
    };
    toBoard(document.querySelector('[data-rl-question]'), document.querySelector('[data-rl-project]'));

    // ── Slides on the board: a slide at a time, or the PDF viewer ──
    // The board opens on one big slide with arrows; "PDF" swaps in the
    // browser's viewer for scrolling, zoom, download and print. A lesson with
    // no slide pictures opens straight on the PDF.
    var deck = media && media.querySelector('[data-rl-deck]');
    var pages = [];
    try { pages = deck ? JSON.parse(deck.getAttribute('data-rl-pages')) || [] : []; } catch (err) {}
    var slideImg = deck && deck.querySelector('img');
    var count = media && media.querySelector('[data-rl-count]');
    var prev = media && media.querySelector('[data-rl-prev]');
    var next = media && media.querySelector('[data-rl-next]');
    var viewButtons = media ? Array.prototype.slice.call(media.querySelectorAll('[data-rl-view]')) : [];
    var slide = 0;
    var pdfBig = false;

    var showSlide = function (i) {
        slide = Math.max(0, Math.min(pages.length - 1, i));
        slideImg.src = pages[slide];
        slideImg.alt = 'Slide ' + (slide + 1) + ' of ' + pages.length;
        count.textContent = (slide + 1) + ' / ' + pages.length;
        prev.disabled = slide === 0;
        next.disabled = slide === pages.length - 1;
        if (document.activeElement && document.activeElement.disabled) media.querySelector('[data-rl-slides-project]').focus();
        if (pages[slide + 1]) new Image().src = pages[slide + 1];
    };

    // The browser's PDF viewer picks its toolbar when it loads, by the size of
    // its frame: none in the small pinned one, the full set on the board. Load
    // it afresh so it matches the frame it is now in.
    var reloadPdf = function () {
        var old = media.querySelector('iframe');
        if (old) old.replaceWith(old.cloneNode());
    };

    var setView = function (view) {
        media.setAttribute('data-view', view);
        viewButtons.forEach(function (b) {
            b.setAttribute('aria-pressed', b.getAttribute('data-rl-view') === view ? 'true' : 'false');
        });
        if (view === 'pdf') {
            reloadPdf();
            pdfBig = true;
        }
    };

    if (media && pages.length && slideImg) {
        viewButtons.forEach(function (b) {
            b.addEventListener('click', function () { setView(b.getAttribute('data-rl-view')); });
        });
        prev.addEventListener('click', function () { showSlide(slide - 1); });
        next.addEventListener('click', function () { showSlide(slide + 1); });
        document.addEventListener('keydown', function (e) {
            if (!media.classList.contains('is-board') || media.getAttribute('data-view') !== 'slides') return;
            var to = { ArrowRight: slide + 1, PageDown: slide + 1, ArrowLeft: slide - 1, PageUp: slide - 1, Home: 0, End: pages.length - 1 }[e.key];
            if (to === undefined) return;
            e.preventDefault();
            showSlide(to);
        });
    }

    toBoard(media, document.querySelector('[data-rl-slides-project]'), function (open) {
        if (open) {
            if (pages.length && slideImg) {
                setView('slides');
                showSlide(slide);
            } else {
                setView('pdf');
            }
        } else {
            media.removeAttribute('data-view');
            // Back in the small frame: undo the board-sized viewer.
            if (pdfBig) reloadPdf();
            pdfBig = false;
        }
    });


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
