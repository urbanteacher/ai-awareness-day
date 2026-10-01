/**
 * AI Awareness Day — Main JS
 *
 * Handles: scroll animations, header state, mobile nav, contact form AJAX
 *
 * @package AI_Awareness_Day
 */

(function () {
    'use strict';

    // Ensure DOM is ready (handles deferred script loading)
    function init() {

        // ============================================
        // Scroll-based fade-up animations
        // ============================================
        let fadeUpObserver = null;
        const fadeUpElements = document.querySelectorAll('.fade-up');
        if (typeof IntersectionObserver === 'undefined') {
            fadeUpElements.forEach((el) => {
                el.classList.add('visible');
            });
        } else {
            const observerOptions = {
                root: null,
                rootMargin: '0px 0px -60px 0px',
                threshold: 0.1,
            };

            fadeUpObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        fadeUpObserver.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            fadeUpElements.forEach((el) => {
                fadeUpObserver.observe(el);
            });
        }

        // Re-observe any .fade-up elements injected after page load (e.g. AJAX
        // resource cards). resource-filters.js dispatches this event after every
        // grid update so newly rendered cards get the same scroll-in animation.
        document.addEventListener('aiad:resourcesRendered', () => {
            const pending = document.querySelectorAll('.fade-up:not(.visible)');
            if (!fadeUpObserver) {
                pending.forEach((el) => el.classList.add('visible'));
                return;
            }
            pending.forEach((el) => {
                fadeUpObserver.observe(el);
            });
        });

        // ============================================
        // Hero countdown
        // Exposed as window.aiad.initHeroCountdown so it can be called again
        // if the hero section is injected after DOMContentLoaded.
        // ============================================
        window.aiad = window.aiad || {};
        window.aiad.initHeroCountdown = function initHeroCountdown() {
            var root = document.querySelector('.hero-countdown[data-event-ts]');
            if (!root) return;

            // Prefer the server-emitted Unix ms timestamp — no date string parsing,
            // no Safari/locale timezone ambiguity.
            var tsAttr   = root.getAttribute('data-event-ts');
            var targetMs = tsAttr ? parseInt(tsAttr, 10) : NaN;

            // Fallback: parse date string with explicit UTC suffix.
            if (isNaN(targetMs)) {
                var dateStr = root.getAttribute('data-event-date');
                if (!dateStr) return;
                targetMs = Date.parse(dateStr + 'T00:00:00Z');
                if (isNaN(targetMs)) return;
            }

            var daysEl    = root.querySelector('[data-unit="days"]');
            var hoursEl   = root.querySelector('[data-unit="hours"]');
            var minutesEl = root.querySelector('[data-unit="minutes"]');
            var secondsEl = root.querySelector('[data-unit="seconds"]');
            if (!daysEl || !hoursEl || !minutesEl || !secondsEl) return;

            function pad(v) { return String(v).padStart(2, '0'); }

            var intervalId = null;
            var countdownLiveDispatched = false;

            function tick() {
                var diff = targetMs - Date.now();
                if (diff <= 0) {
                    if (!countdownLiveDispatched) {
                        countdownLiveDispatched = true;
                        document.dispatchEvent(new CustomEvent('aiad:countdownLive'));
                    }
                    daysEl.textContent    = '00';
                    hoursEl.textContent   = '00';
                    minutesEl.textContent = '00';
                    secondsEl.textContent = '00';
                    if (intervalId) { clearInterval(intervalId); intervalId = null; }
                    return;
                }
                var totalSeconds = Math.floor(diff / 1000);
                daysEl.textContent    = String(Math.floor(totalSeconds / 86400));
                hoursEl.textContent   = pad(Math.floor((totalSeconds % 86400) / 3600));
                minutesEl.textContent = pad(Math.floor((totalSeconds % 3600) / 60));
                secondsEl.textContent = pad(totalSeconds % 60);
            }

            tick();
            intervalId = setInterval(tick, 1000);
        };
        window.aiad.initHeroCountdown();

        // ============================================
        // Stats counter animation
        // ============================================
        function initStatsBarCounters() {
            var statsBars = document.querySelectorAll('.timeline-stats-bar');
            if (!statsBars.length) return;

            function parseCounterTarget(rawValue) {
                var cleaned = String(rawValue || '').trim();
                var numeric = cleaned.replace(/[^\d-]/g, '');
                var parsed = parseInt(numeric, 10);
                return isNaN(parsed) ? null : parsed;
            }

            // Animate counter from 0 to target value.
            function animateCounter(element, targetValue, duration) {
                var target = parseCounterTarget(targetValue);
                var startTime = performance.now();

                if (target === null || target === 0) {
                    element.textContent = targetValue;
                    element.classList.add('animated');
                    return;
                }

                function updateCounter(currentTime) {
                    var elapsed = currentTime - startTime;
                    var progress = Math.min(elapsed / duration, 1);

                    // Easing function for smooth animation.
                    var easeOutQuart = 1 - Math.pow(1 - progress, 4);
                    var currentValue = Math.floor(easeOutQuart * target);

                    element.textContent = String(currentValue);

                    if (progress < 1) {
                        requestAnimationFrame(updateCounter);
                    } else {
                        element.textContent = targetValue;
                        element.classList.remove('counting');
                        element.classList.add('animated');
                    }
                }

                element.classList.add('counting');
                requestAnimationFrame(updateCounter);
            }

            function setupStatsBar(statsBar) {
                if (!statsBar || statsBar.dataset.counterInit === 'true') return;
                statsBar.dataset.counterInit = 'true';

                var statElements = statsBar.querySelectorAll('.timeline-stats-bar__stat');
                var valueElements = statsBar.querySelectorAll('.timeline-stats-bar__value');
                var animationTriggered = false;
                var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                function triggerAnimation() {
                    if (animationTriggered) return;
                    animationTriggered = true;

                    statElements.forEach(function (stat, index) {
                        setTimeout(function () {
                            stat.classList.add('animate-in');
                        }, index * 100);
                    });

                    valueElements.forEach(function (valueEl, index) {
                        var targetValue = valueEl.textContent;
                        setTimeout(function () {
                            if (prefersReducedMotion) {
                                valueEl.textContent = targetValue;
                                valueEl.classList.add('animated');
                                return;
                            }
                            animateCounter(valueEl, targetValue, 1500);
                        }, 300 + (index * 200));
                    });
                }

                if (typeof window.IntersectionObserver === 'undefined') {
                    triggerAnimation();
                    return;
                }

                var statsObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            triggerAnimation();
                            statsObserver.unobserve(entry.target);
                        }
                    });
                }, {
                    root: null,
                    rootMargin: '0px',
                    threshold: 0.1,
                });

                statsObserver.observe(statsBar);

                setTimeout(function () {
                    if (!animationTriggered) {
                        triggerAnimation();
                    }
                }, 3000);
            }

            statsBars.forEach(setupStatsBar);
        }

        initStatsBarCounters();

        // Support live-rendered content (e.g. preview/partial refresh) where
        // timeline stats can be inserted after initial page load.
        if (typeof window.MutationObserver !== 'undefined') {
            var statsMutationObserver = new MutationObserver(function (mutations) {
                var shouldRecheck = mutations.some(function (mutation) {
                    return mutation.type === 'childList' && mutation.addedNodes && mutation.addedNodes.length > 0;
                });
                if (shouldRecheck) {
                    initStatsBarCounters();
                }
            });
            statsMutationObserver.observe(document.body, { childList: true, subtree: true });
        }

        // ============================================
        // Header scroll state
        // ============================================
        const header = document.getElementById('site-header');

        if (header) {
            window.addEventListener('scroll', () => {
                const currentScroll = window.scrollY;
                if (currentScroll > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            }, { passive: true });
        }

        // ============================================
        // AiAd27 hero strands
        // ============================================
        function initHeroStrandFeature() {
            var hero = document.querySelector('.hero-section[data-strand]');
            var feature = document.querySelector('.hero-strand-feature');
            if (!hero || !feature) return;

            // The motion is the fallback for each strand's question: the hero prints the saved ones on the strand links
            // (data-motion), from aiad_hero27_strand_questions() and the Customiser. The question is what
            // the previous hero asked instead, for when Customise > Front Page Sections > Hero Section > Homepage hero is set to Previous.
            var strands = [
                { slug: 'safe', name: 'Safe', motion: 'Should each person manage what AI remembers about them?', question: 'Would you tell an AI your secret?' },
                { slug: 'smart', name: 'Smart', motion: 'Should school AI tools be allowed to give students the answer?', question: 'What happens when AI acts for you?' },
                { slug: 'creative', name: 'Creative', motion: 'Should AI-made work be allowed to compete for creative prizes?', question: 'Who really made it?' },
                { slug: 'responsible', name: 'Responsible', motion: 'Should AI have to report its energy use when streaming and gaming don\'t?', question: 'Should AI decide?' },
                { slug: 'future', name: 'Future', motion: 'Should students have an official role in how their school uses AI?', question: 'What skills must stay human?' },
            ];
            var motion = feature.querySelector('.hero-strand-feature__motion-text');
            var summary = motion ? null : feature.querySelector('.hero-strand-feature__summary');
            var word = feature.querySelector('.hero-strand-feature__word');
            var controls = Array.prototype.slice.call(feature.querySelectorAll('[data-strand-target]'));
            var currentIndex = 0;
            var autoTimer = null;
            var resumeTimer = null;
            var AUTO_MS = 7000;
            var RESUME_MS = 10000;
            var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            function setStrand(index, animate) {
                var nextIndex = Math.max(0, Math.min(strands.length - 1, index));
                if (nextIndex === currentIndex && hero.dataset.strand === strands[nextIndex].slug) return;
                var strand = strands[nextIndex];
                currentIndex = nextIndex;
                hero.dataset.strand = strand.slug;
                word.textContent = strand.name;
                if (motion) {
                    // The page's own question for the strand (set in the Customiser) wins over the fallback list above.
                    var control = feature.querySelector('[data-strand-target="' + strand.slug + '"][data-motion]');
                    motion.textContent = control && control.dataset.motion ? control.dataset.motion : strand.motion;
                }
                if (summary) summary.textContent = strand.question;
                controls.forEach(function (control) {
                    var active = control.dataset.strandTarget === strand.slug;
                    control.classList.toggle('is-active', active);
                    if (control.hasAttribute('aria-pressed')) {
                        control.setAttribute('aria-pressed', active ? 'true' : 'false');
                    }
                    if (control.hasAttribute('aria-current') || control.tagName === 'A') {
                        if (active) {
                            control.setAttribute('aria-current', 'true');
                        } else {
                            control.removeAttribute('aria-current');
                        }
                    }
                });
                if (animate && !reducedMotion) {
                    feature.classList.remove('is-flipping');
                    void feature.offsetWidth;
                    feature.classList.add('is-flipping');
                }
            }

            function scrollToThemes() {
                var target = document.getElementById('themes')
                    || document.querySelector('.toolkit-explore-themes');
                if (!target) return;
                if (reducedMotion) {
                    target.scrollIntoView();
                } else {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }

            function stopAuto() {
                if (autoTimer) {
                    window.clearInterval(autoTimer);
                    autoTimer = null;
                }
                if (resumeTimer) {
                    window.clearTimeout(resumeTimer);
                    resumeTimer = null;
                }
            }

            function startAuto() {
                stopAuto();
                if (reducedMotion || document.hidden) return;
                autoTimer = window.setInterval(function () {
                    setStrand((currentIndex + 1) % strands.length, true);
                }, AUTO_MS);
            }

            function pauseThenResume() {
                stopAuto();
                if (reducedMotion) return;
                resumeTimer = window.setTimeout(startAuto, RESUME_MS);
            }

            controls.forEach(function (control, index) {
                control.addEventListener('click', function (event) {
                    event.preventDefault();
                    setStrand(index, true);
                    pauseThenResume();
                    scrollToThemes();
                    if (history && history.replaceState) {
                        history.replaceState(null, '', '#themes');
                    } else {
                        window.location.hash = 'themes';
                    }
                });
            });

            document.addEventListener('visibilitychange', function () {
                if (document.hidden) {
                    stopAuto();
                } else {
                    startAuto();
                }
            });

            startAuto();
        }

        initHeroStrandFeature();

        // ============================================
        // Mobile navigation toggle
        // ============================================
        const navToggle = document.getElementById('nav-toggle');
        const mainNav = document.getElementById('main-nav');

        if (navToggle && mainNav) {
            function closeNav() {
                navToggle.classList.remove('active');
                mainNav.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
            }

            navToggle.addEventListener('click', () => {
                const isOpen = mainNav.classList.toggle('open');
                navToggle.classList.toggle('active', isOpen);
                navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            // Close on link click
            mainNav.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', closeNav);
            });

            // Close when clicking outside the nav or toggle
            document.addEventListener('click', (e) => {
                if (mainNav.classList.contains('open') &&
                    !mainNav.contains(e.target) &&
                    !navToggle.contains(e.target)) {
                    closeNav();
                }
            });
        }

        // ============================================
        // Smooth scrolling for anchor links
        // ============================================
        // Covers "#x" and same-page "/#x" links (the header nav uses the
        // latter). The offset comes from each target's CSS scroll-margin-top,
        // so JS and native hash jumps land in the same place.
        const anchorReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // A section can name a child to land on with data-anchor-target, e.g.
        // #campaign lands on its heading, not the partner logo strip above it.
        // Folding the child's offset into the section's scroll-margin keeps the
        // browser's own hash jump (arriving from another page) on the child too.
        const anchorOffsetSections = Array.prototype.slice.call(document.querySelectorAll('[data-anchor-target]'));
        function updateAnchorOffsets() {
            anchorOffsetSections.forEach(function (section) {
                const child = section.querySelector(section.dataset.anchorTarget);
                if (!child) return;
                section.style.scrollMarginTop = '';
                const base = parseFloat(getComputedStyle(section).scrollMarginTop) || 0;
                const offset = child.getBoundingClientRect().top - section.getBoundingClientRect().top;
                section.style.scrollMarginTop = (base - offset) + 'px';
            });
        }
        if (anchorOffsetSections.length) {
            updateAnchorOffsets();
            window.addEventListener('load', updateAnchorOffsets);
            window.addEventListener('resize', updateAnchorOffsets);
        }

        document.addEventListener('click', function (e) {
            if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            const anchor = e.target.closest('a[href*="#"]');
            if (!anchor || anchor.target === '_blank') return;
            if (anchor.pathname !== window.location.pathname || anchor.search !== window.location.search || anchor.host !== window.location.host) return;

            const target = anchor.hash.length > 1 ? document.getElementById(decodeURIComponent(anchor.hash.slice(1))) : null;
            if (!target) return;

            e.preventDefault();
            target.scrollIntoView({ behavior: anchorReducedMotion ? 'auto' : 'smooth', block: 'start' });
            if (history && history.pushState && anchor.hash !== window.location.hash) {
                history.pushState(null, '', anchor.hash);
            }
            // Move focus there too, as following the link would have, so the next Tab carries on from the target
            // (the skip link, and every link to a section) rather than from the link.
            if (!target.matches('a[href], button, input, select, textarea, summary, [tabindex]')) {
                target.setAttribute('tabindex', '-1');
                target.setAttribute('data-aiad-scroll-target', '');
            }
            target.focus({ preventScroll: true });
        });

        // ============================================
        // Display board: 4-tab switcher
        // ============================================
        const dbTabs = document.querySelector('.js-display-board-tabs');
        if (dbTabs) {
            const dbButtons = dbTabs.querySelectorAll('.display-board-tab');
            const dbPanels  = dbTabs.querySelectorAll('.display-board-panel');
            dbButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const target = btn.dataset.tab;
                    dbButtons.forEach(function(b) {
                        b.classList.toggle('is-active', b === btn);
                        b.setAttribute('aria-selected', b === btn ? 'true' : 'false');
                    });
                    dbPanels.forEach(function(p) {
                        p.hidden = p.id !== 'dbt-panel-' + target;
                    });
                });
            });
        }

        // ============================================
        // Get Involved form: show/hide fields by role
        // ============================================
        const involvedAs = document.getElementById('involved_as');
        const roleGroups = document.querySelectorAll('.form-group-role');

        if (involvedAs && roleGroups.length) {
            function toggleRoleFields() {
                const role = (involvedAs.value || '').trim();
                roleGroups.forEach(function (el) {
                    const roles = (el.getAttribute('data-role') || '').split(/\s+/).filter(Boolean);
                    const show = role && roles.indexOf(role) !== -1;
                    el.style.display = show ? '' : 'none';
                    el.querySelectorAll('input, select, textarea').forEach(function (field) {
                        field.disabled = !show;
                    });
                });
            }
            involvedAs.addEventListener('change', toggleRoleFields);
            toggleRoleFields();
        }

        // The contact form's submission is the script module assets/js/contact-form.js.

        // ============================================
        // Broken image fallback: show icon where image failed to load
        // When image loads (e.g. after re-upload or URL fix), icon is not shown
        // ============================================
        const themeImageSelectors = [
            '.site-logo__img',
            '.hero-logo__img',
            '.partner-logo__img',
            '.principle-badge__img',
            '.display-board-real img',
            '.display-board-examples__item img',
            '.theme-card img',
        ].join(', ');

        const themeImages = document.querySelectorAll(themeImageSelectors);
        const brokenIconSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';

        themeImages.forEach((img) => {
            if (!img.src || img.classList.contains('is-broken')) return;
            // Skip theme-link badge images - they have their own placeholder handling
            if (img.classList.contains('theme-link__badge-img')) return;

            img.addEventListener('error', function onImgError() {
                this.classList.add('is-broken');
                if (this.parentNode && !this.parentNode.querySelector('.broken-image-icon')) {
                    const wrap = document.createElement('span');
                    wrap.className = 'broken-image-icon';
                    wrap.setAttribute('aria-hidden', 'true');
                    wrap.innerHTML = brokenIconSvg;
                    this.parentNode.appendChild(wrap);
                }
            });

            img.addEventListener('load', function onImgLoad() {
                this.classList.remove('is-broken');
                const icon = this.parentNode && this.parentNode.querySelector('.broken-image-icon');
                if (icon) icon.remove();
            });
        });

        // ============================================
        // Partners: Show more / less (vanilla — works on mobile where Interactivity
        // may not bind; do not gate on window.wp.interactivity).
        // ============================================
        const momentumSection = document.getElementById('reach');
        const revealBtn = document.querySelector('.partners-reveal-btn');
        const partnersLayoutMql = window.matchMedia('(min-width: 768px)');

        function getPartnersInitialShow(section) {
            const mob = parseInt(section.getAttribute('data-initial-show-mobile') || '8', 10);
            const desk = parseInt(section.getAttribute('data-initial-show-desktop') || '10', 10);
            return partnersLayoutMql.matches ? desk : mob;
        }

        function applyPartnersGridVisibility() {
            if (!momentumSection) {
                return;
            }
            const initialShow = getPartnersInitialShow(momentumSection);
            if (revealBtn) {
                revealBtn.setAttribute('data-initial-show', String(initialShow));
            }
            const isExpanded = revealBtn && revealBtn.classList.contains('active');
            momentumSection.querySelectorAll('.partner-card:not(.partner-card--dummy)').forEach((card) => {
                const idx = parseInt(card.getAttribute('data-partner-index') || '-1', 10);
                if (isExpanded) {
                    card.classList.remove('partner-card--hidden');
                } else if (idx >= initialShow) {
                    card.classList.add('partner-card--hidden');
                } else {
                    card.classList.remove('partner-card--hidden');
                }
            });
        }

        if (momentumSection) {
            applyPartnersGridVisibility();
            let partnersResizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(partnersResizeTimer);
                partnersResizeTimer = setTimeout(applyPartnersGridVisibility, 150);
            });
            if (typeof partnersLayoutMql.addEventListener === 'function') {
                partnersLayoutMql.addEventListener('change', applyPartnersGridVisibility);
            } else if (typeof partnersLayoutMql.addListener === 'function') {
                partnersLayoutMql.addListener(applyPartnersGridVisibility);
            }
        }

        if (revealBtn) {
            revealBtn.addEventListener('click', () => {
                const momentumEl = revealBtn.closest('.momentum-section');
                if (!momentumEl) {
                    return;
                }
                revealBtn.classList.toggle('active');
                const isExpanded = revealBtn.classList.contains('active');
                revealBtn.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
                applyPartnersGridVisibility();

                const labelMore = revealBtn.getAttribute('data-label-more') || 'Show More Partners';
                const labelLess = revealBtn.getAttribute('data-label-less') || 'Show Less';
                const icon = revealBtn.querySelector('.partners-reveal-btn__chevron, svg');
                if (icon) {
                    icon.style.transform = isExpanded ? 'rotate(180deg)' : 'rotate(0deg)';
                }

                const text = revealBtn.querySelector('.reveal-text');
                if (text) {
                    text.textContent = isExpanded ? labelLess : labelMore;
                }
            });
        }

        // Resource and article view tracking and download tracking are the script module assets/js/tracking.js.

        // ============================================
        // AI Literacy Quiz
        // ============================================
        (function initAiQuiz() {
            var quiz = document.querySelector('[data-ai-quiz]');
            if (!quiz) return;

            var submitBtn = quiz.querySelector('[data-ai-quiz-submit]');
            var resetBtn  = quiz.querySelector('[data-ai-quiz-reset]');
            var resultEl  = quiz.querySelector('[data-ai-quiz-result]');
            var questions = quiz.querySelectorAll('.ai-quiz__question');
            if (!submitBtn || !resultEl || !questions.length) return;

            var scores = [
                '😬 Keep exploring — AI literacy is a journey! (0/5)',
                '📚 Not bad! A little more learning and you\'ll be set. (1/5)',
                '🧠 Good work! You\'ve got solid AI awareness. (2/5)',
                '⭐ Great score! You really know your stuff. (3/5)',
                '🏆 Excellent! Nearly there — one to brush up on. (4/5)',
                '🎉 Perfect score! You\'re fully AI-literate and ready for June 4th! (5/5)',
            ];

            submitBtn.addEventListener('click', function () {
                var correct = 0;
                var allAnswered = true;

                questions.forEach(function (q) {
                    var expected = q.getAttribute('data-correct');
                    var chosen   = q.querySelector('input[type="radio"]:checked');

                    if (!chosen) {
                        allAnswered = false;
                        q.classList.add('ai-quiz__question--unanswered');
                    } else {
                        q.classList.remove('ai-quiz__question--unanswered');
                        var isCorrect = chosen.value === expected;
                        if (isCorrect) correct++;

                        q.querySelectorAll('.ai-quiz__option').forEach(function (label) {
                            var input = label.querySelector('input');
                            label.classList.remove('ai-quiz__option--correct', 'ai-quiz__option--wrong');
                            if (input.value === expected) {
                                label.classList.add('ai-quiz__option--correct');
                            } else if (input === chosen && !isCorrect) {
                                label.classList.add('ai-quiz__option--wrong');
                            }
                            input.disabled = true;
                        });
                    }
                });

                if (!allAnswered) {
                    resultEl.textContent = 'Please answer all five questions first.';
                    resultEl.className = 'ai-quiz__result ai-quiz__result--warn';
                    return;
                }

                resultEl.textContent = scores[correct] || scores[5];
                resultEl.className   = 'ai-quiz__result ai-quiz__result--show';
                submitBtn.style.display = 'none';
                resetBtn.style.display  = '';
            });

            resetBtn.addEventListener('click', function () {
                questions.forEach(function (q) {
                    q.classList.remove('ai-quiz__question--unanswered');
                    q.querySelectorAll('input[type="radio"]').forEach(function (input) {
                        input.checked  = false;
                        input.disabled = false;
                    });
                    q.querySelectorAll('.ai-quiz__option').forEach(function (label) {
                        label.classList.remove('ai-quiz__option--correct', 'ai-quiz__option--wrong');
                    });
                });
                resultEl.textContent    = '';
                resultEl.className      = 'ai-quiz__result';
                submitBtn.style.display = '';
                resetBtn.style.display  = 'none';
            });
        })();

    // ============================================
    // Aim list: show 3 on small screens, expand for rest
    // ============================================
    (function initAimListExpand() {
        var list = document.getElementById('aims-list');
        var btn = document.getElementById('aim-expand');
        if (!list || !btn) {
            return;
        }

        var labelMore = btn.getAttribute('data-label-more') || 'Show more';
        var labelLess = btn.getAttribute('data-label-less') || 'Show less';

        function revealAimsFromFourth() {
            var items = list.querySelectorAll(':scope > li'); // .aim-item, or the core list's items (patterns/homepage-aim.php)
            items.forEach(function (li, i) {
                if (i >= 3) {
                    li.classList.add('visible');
                }
            });
        }

        btn.addEventListener('click', function () {
            var expanded = list.classList.toggle('aims-list--expanded');
            btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            btn.textContent = expanded ? labelLess : labelMore;
            if (expanded) {
                revealAimsFromFourth();
            }
        });
    })();

    } // End of init function

    // Run immediately if DOM is ready, otherwise wait for DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
