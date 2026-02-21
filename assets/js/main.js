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
        // Resource: toggle long suggested answers
        // ============================================
        const answerToggles = document.querySelectorAll('.resource-answers-toggle');

        if (answerToggles.length) {
            answerToggles.forEach((btn) => {
                const targetId = btn.getAttribute('data-target');
                if (!targetId) return;

                const panel = document.getElementById(targetId);
                if (!panel) return;

                const showLabel = btn.dataset.labelShow || 'Show suggested answers';
                const hideLabel = btn.dataset.labelHide || 'Hide suggested answers';

                btn.addEventListener('click', () => {
                    const isHidden = panel.hasAttribute('hidden');

                    if (isHidden) {
                        panel.removeAttribute('hidden');
                    } else {
                        panel.setAttribute('hidden', '');
                    }

                    btn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                    btn.textContent = isHidden ? hideLabel : showLabel;
                });
            });
        }

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
        // Partners: Show More Fallback
        // Only run manual toggle when Interactivity API is not available.
        // ============================================
        const revealBtn = document.querySelector('.partners-reveal-btn');
        if (revealBtn) {
            revealBtn.addEventListener('click', () => {
                if (!window.wp || !window.wp.interactivity) {
                    const momentumSection = revealBtn.closest('.momentum-section');
                    if (momentumSection) {
                        const isExpanded = revealBtn.classList.toggle('active');
                        const cards = momentumSection.querySelectorAll('.partner-card:not(.partner-card--dummy)');
                        const initialShow = 10;

                        cards.forEach((card, index) => {
                            if (index >= initialShow) {
                                card.classList.toggle('partner-card--hidden', !isExpanded);
                            }
                        });

                        // Update icon rotation
                        const icon = revealBtn.querySelector('svg');
                        if (icon) {
                            icon.style.transform = isExpanded ? 'rotate(180deg)' : 'rotate(0deg)';
                        }

                        // Update text (simple fallback)
                        const text = revealBtn.querySelector('.reveal-text');
                        if (text) {
                            text.textContent = isExpanded ? 'Show Less' : 'Show More Partners';
                        }
                    }
                }
            });
        }

        // ============================================
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
    } // End of init function

    // Run immediately if DOM is ready, otherwise wait for DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

