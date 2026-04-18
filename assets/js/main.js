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
        const observerOptions = {
            root: null,
            rootMargin: '0px 0px -60px 0px',
            threshold: 0.1,
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-up').forEach((el) => {
            observer.observe(el);
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

            function tick() {
                var diff = targetMs - Date.now();
                if (diff <= 0) {
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
        document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
            anchor.addEventListener('click', function (e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;

                const target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    const headerHeight = header ? header.offsetHeight : 72;
                    const top = target.getBoundingClientRect().top + window.scrollY - headerHeight;

                    window.scrollTo({
                        top: top,
                        behavior: 'smooth',
                    });
                }
            });
        });

        // ============================================
        // Display board: flip between Blueprint and Real example
        // ============================================
        const displayBoardPreview = document.querySelector('.js-display-board-preview.display-board-preview--has-real');
        if (displayBoardPreview) {
            const btnBlueprint = displayBoardPreview.querySelector('#tab-blueprint');
            const btnReal = displayBoardPreview.querySelector('#tab-real');
            const panelBlueprint = document.getElementById('display-board-blueprint');
            const panelReal = document.getElementById('display-board-real');

            function setView(view) {
                const isReal = view === 'real';
                displayBoardPreview.setAttribute('data-view', view);
                displayBoardPreview.classList.toggle('display-board-preview--real', isReal);
                if (btnBlueprint) {
                    btnBlueprint.classList.toggle('is-active', !isReal);
                    btnBlueprint.setAttribute('aria-selected', !isReal);
                }
                if (btnReal) {
                    btnReal.classList.toggle('is-active', isReal);
                    btnReal.setAttribute('aria-selected', isReal);
                }
                if (panelBlueprint) panelBlueprint.hidden = isReal;
                if (panelReal) panelReal.hidden = !isReal;

                // Toggle display-board-examples section
                const examplesSection = document.querySelector('.display-board-examples');
                if (examplesSection) {
                    examplesSection.hidden = !isReal;
                }
            }

            if (btnBlueprint) btnBlueprint.addEventListener('click', () => setView('blueprint'));
            if (btnReal) btnReal.addEventListener('click', () => setView('real'));
        }

        // ============================================
        // Display Board Steps: Toggle button
        // ============================================
        const displayBoardStepsToggle = document.querySelector('.display-board-steps__toggle');
        if (displayBoardStepsToggle) {
            const icon = displayBoardStepsToggle.querySelector('.display-board-steps__icon');
            displayBoardStepsToggle.addEventListener('click', function () {
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', !isExpanded);
                if (icon) {
                    icon.textContent = isExpanded ? '+' : '−';
                }
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

        // ============================================
        // Contact form AJAX submission
        // ============================================
        const form = document.getElementById('aiad-contact-form');
        const formStatus = document.getElementById('form-status');

        if (form && typeof aiad_ajax !== 'undefined') {
            function launchConfetti() {
                if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    return;
                }
                var container = document.createElement('div');
                container.className = 'aiad-confetti';
                document.body.appendChild(container);
                for (var i = 0; i < 26; i++) {
                    var piece = document.createElement('span');
                    piece.className = 'aiad-confetti__piece';
                    piece.style.left = Math.round(Math.random() * 100) + '%';
                    piece.style.background = ['#22c55e', '#10b981', '#84cc16', '#3b82f6', '#a855f7'][i % 5];
                    piece.style.animationDelay = (Math.random() * 0.3) + 's';
                    piece.style.transform = 'translateY(0) rotate(' + (Math.random() * 360) + 'deg)';
                    container.appendChild(piece);
                }
                window.setTimeout(function () {
                    container.remove();
                }, 1800);
            }

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                // Client-side validation (role-specific required fields). Keep in sync with server: inc/ajax-handlers.php → aiad_handle_contact_form().
                const involvedAsVal = (form.querySelector('#involved_as') || {}).value || '';
                const firstNameVal = (form.querySelector('#first_name') || {}).value || '';
                const lastNameVal = (form.querySelector('#last_name') || {}).value || '';
                const emailVal = (form.querySelector('#email') || {}).value || '';
                const messageVal = (form.querySelector('#message') || {}).value || '';

                let missing = false;

                if (!involvedAsVal || !firstNameVal.trim() || !lastNameVal.trim() || !emailVal.trim() || !messageVal.trim()) {
                    missing = true;
                }

                if (!missing) {
                    if (involvedAsVal === 'teacher' || involvedAsVal === 'school_leader') {
                        const schoolNameVal = (form.querySelector('#school_name') || {}).value || '';
                        if (!schoolNameVal.trim()) {
                            missing = true;
                        }
                    }

                    if (involvedAsVal === 'teacher') {
                        const subjectVal = (form.querySelector('#subject') || {}).value || '';
                        if (!subjectVal.trim()) {
                            missing = true;
                        }
                    }

                    if (involvedAsVal === 'parent') {
                        const childSchoolVal = (form.querySelector('#child_school') || {}).value || '';
                        if (!childSchoolVal.trim()) {
                            missing = true;
                        }
                    }

                    if (involvedAsVal === 'school_leader') {
                        const roleTitleVal = (form.querySelector('#role_title') || {}).value || '';
                        if (!roleTitleVal.trim()) {
                            missing = true;
                        }
                    }

                    if (involvedAsVal === 'organisation') {
                        const organisationVal = (form.querySelector('#organisation') || {}).value || '';
                        const orgTypeVal = (form.querySelector('#org_type') || {}).value || '';
                        if (!organisationVal.trim() || !orgTypeVal.trim()) {
                            missing = true;
                        }
                    }
                }

                if (missing) {
                    const errSpan = document.createElement('span');
                    errSpan.style.color = '#ef4444';
                    errSpan.textContent = 'Please fill in all required fields.';
                    formStatus.textContent = '';
                    formStatus.appendChild(errSpan);
                    return;
                }

                const submitBtn = form.querySelector('.btn-submit');
                const originalText = submitBtn.innerHTML;

                // Loading state: static SVG only (safe for innerHTML). If adding dynamic content later, use createElement.
                submitBtn.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite;">
                    <path d="M21 12a9 9 0 11-6.219-8.56"></path>
                </svg>
                Sending...
            `;
                submitBtn.disabled = true;
                formStatus.textContent = '';

                const formData = new FormData(form);
                formData.append('action', 'aiad_contact');
                formData.append('nonce', aiad_ajax.nonce);

                try {
                    const response = await fetch(aiad_ajax.url, {
                        method: 'POST',
                        body: formData,
                    });

                    const data = await response.json();

                    const msgSpan = document.createElement('span');
                    if (data.success) {
                        msgSpan.style.color = 'var(--green-600)';
                        msgSpan.textContent = data.data.message;
                        formStatus.textContent = '';
                        formStatus.appendChild(msgSpan);
                        form.reset();
                        launchConfetti();

                        // Update pledge counter if server returned updated count.
                        if (data.data && typeof data.data.pledge_count !== 'undefined') {
                            var pledgeWrap = document.querySelector('[data-pledge-counter]');
                            if (pledgeWrap) {
                                var countEl = pledgeWrap.querySelector('[data-pledge-count]');
                                var fillEl  = pledgeWrap.querySelector('[data-pledge-fill]');
                                var barEl   = pledgeWrap.querySelector('.pledge-counter__bar');
                                var count   = parseInt(data.data.pledge_count, 10);
                                var goal    = parseInt(pledgeWrap.getAttribute('data-goal') || data.data.pledge_goal, 10) || 500;
                                var pct     = Math.min(100, Math.round((count / goal) * 100));
                                if (countEl) countEl.textContent = count.toLocaleString();
                                if (fillEl)  fillEl.style.width  = pct + '%';
                                if (barEl)   barEl.setAttribute('aria-valuenow', String(count));
                            }
                        }
                    } else {
                        msgSpan.style.color = '#ef4444';
                        msgSpan.textContent = data.data.message;
                        formStatus.textContent = '';
                        formStatus.appendChild(msgSpan);
                    }
                } catch (err) {
                    const errSpan = document.createElement('span');
                    errSpan.style.color = '#ef4444';
                    errSpan.textContent = 'Network error. Please try again.';
                    formStatus.textContent = '';
                    formStatus.appendChild(errSpan);
                }

                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }

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
        const revealBtn = document.querySelector('.partners-reveal-btn');
        if (revealBtn) {
            revealBtn.addEventListener('click', () => {
                const momentumSection = revealBtn.closest('.momentum-section');
                if (!momentumSection) {
                    return;
                }
                const initialShow = parseInt(revealBtn.getAttribute('data-initial-show') || '10', 10);
                const labelMore = revealBtn.getAttribute('data-label-more') || 'Show More Partners';
                const labelLess = revealBtn.getAttribute('data-label-less') || 'Show Less';
                const isExpanded = revealBtn.classList.toggle('active');
                revealBtn.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');

                momentumSection.querySelectorAll('.partner-card:not(.partner-card--dummy)').forEach((card) => {
                    const idx = parseInt(card.getAttribute('data-partner-index') || '-1', 10);
                    if (idx >= initialShow) {
                        card.classList.toggle('partner-card--hidden', !isExpanded);
                    }
                });

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

        // ============================================
        // AI Resources modal (partner cards with data-ai-url)
        // ============================================
        (function initAiModal() {
            var aiCards = document.querySelectorAll('.partner-card--ai-resources[data-ai-url]');
            if (!aiCards.length) return;

            var backdrop = document.createElement('div');
            backdrop.className = 'ai-modal-backdrop';
            backdrop.setAttribute('role', 'dialog');
            backdrop.setAttribute('aria-modal', 'true');
            backdrop.innerHTML =
                '<div class="ai-modal">' +
                  '<button class="ai-modal__close" aria-label="Close">&#x2715;</button>' +
                  '<div class="ai-modal__logo"><img src="" alt="" /></div>' +
                  '<p class="ai-modal__label">AI Learning Resources</p>' +
                  '<h3 class="ai-modal__name"></h3>' +
                  '<p class="ai-modal__stats"></p>' +
                  '<a class="ai-modal__cta" href="#" target="_blank" rel="noopener noreferrer">Visit AI Resources &#x2197;</a>' +
                '</div>';
            document.body.appendChild(backdrop);

            var elLogo   = backdrop.querySelector('.ai-modal__logo');
            var elImg    = backdrop.querySelector('.ai-modal__logo img');
            var elName   = backdrop.querySelector('.ai-modal__name');
            var elStats  = backdrop.querySelector('.ai-modal__stats');
            var elCta    = backdrop.querySelector('.ai-modal__cta');
            var closeBtn = backdrop.querySelector('.ai-modal__close');
            var lastFocused = null;

            function openModal(card) {
                lastFocused = card;
                elImg.src  = card.dataset.aiLogo || '';
                elImg.alt  = card.dataset.aiName || '';
                elLogo.style.display  = card.dataset.aiLogo  ? 'flex' : 'none';
                elName.textContent    = card.dataset.aiName  || '';
                elStats.textContent   = card.dataset.aiStats || '';
                elStats.style.display = card.dataset.aiStats ? 'block' : 'none';
                elCta.href = card.dataset.aiUrl;
                backdrop.classList.add('is-open');
                document.body.style.overflow = 'hidden';
                closeBtn.focus();
            }

            function closeModal() {
                backdrop.classList.remove('is-open');
                document.body.style.overflow = '';
                if (lastFocused) lastFocused.focus();
            }

            aiCards.forEach(function (card) {
                card.setAttribute('role', 'button');
                card.setAttribute('tabindex', '0');
                card.style.cursor = 'pointer';
                card.addEventListener('click', function () { openModal(card); });
                card.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openModal(card); }
                });
            });

            closeBtn.addEventListener('click', closeModal);
            backdrop.addEventListener('click', function (e) { if (e.target === backdrop) closeModal(); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(); });
        }());

        // ============================================
        // Resource page view tracking (fires once per page load)
        // ============================================
        (function () {
            var card = document.querySelector('article.resource-activity-card');
            if (!card || typeof aiad_ajax === 'undefined' || !aiad_ajax.track_view_nonce) return;
            var postId = card.id.replace('post-', '');
            if (!postId) return;
            fetch(aiad_ajax.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=aiad_track_resource_view&post_id=' + encodeURIComponent(postId) +
                      '&nonce=' + encodeURIComponent(aiad_ajax.track_view_nonce),
            }).catch(function () {}); // Silently fail - tracking is non-critical
        })();

        // Download tracking (fire-and-forget, does not block download)
        // ============================================
        document.addEventListener('click', (e) => {
            const link = e.target.closest('.resource-download-link, a[download]');
            if (!link) return;
            const postId = link.getAttribute('data-resource-id');
            if (!postId || typeof aiad_ajax === 'undefined' || !aiad_ajax.url) return;

            // Include nonce for security
            const nonce = aiad_ajax.track_download_nonce || '';
            const body = 'action=aiad_track_download&post_id=' + encodeURIComponent(postId) +
                (nonce ? '&nonce=' + encodeURIComponent(nonce) : '');

            fetch(aiad_ajax.url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body,
            }).catch(() => { }); // Silently fail - download tracking is non-critical
        });

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

    // ── Instruction step collapse (>3 steps → hide + reveal toggle) ──
    (function initInstructionCollapse() {
        var VISIBLE = 2;
        var lists = document.querySelectorAll('.resource-list--instructions');
        lists.forEach(function (ol) {
            var steps = ol.querySelectorAll('li.resource-instruction-step');
            if (steps.length <= VISIBLE) return;

            var hidden = steps.length - VISIBLE;

            // Hide steps beyond the threshold
            ol.classList.add('steps-collapsed');

            // Build toggle button
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'resource-steps-toggle';
            btn.setAttribute('aria-expanded', 'false');
            btn.innerHTML =
                '<span class="resource-steps-toggle__label">Show ' + hidden + ' more step' + (hidden > 1 ? 's' : '') + '</span>' +
                '<svg class="resource-steps-toggle__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>';

            // Insert after the ol
            ol.insertAdjacentElement('afterend', btn);

            btn.addEventListener('click', function () {
                var expanded = ol.classList.toggle('steps-collapsed');
                // toggle returns true when class was ADDED (collapsed), false when removed (expanded)
                var isCollapsed = ol.classList.contains('steps-collapsed');
                btn.setAttribute('aria-expanded', String(!isCollapsed));
                btn.querySelector('.resource-steps-toggle__label').textContent = isCollapsed
                    ? 'Show ' + hidden + ' more step' + (hidden > 1 ? 's' : '')
                    : 'Show fewer steps';
                btn.querySelector('.resource-steps-toggle__icon').style.transform = isCollapsed ? '' : 'rotate(180deg)';
            });
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

