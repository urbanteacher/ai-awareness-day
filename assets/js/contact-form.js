/**
 * The Get Involved form: validates, submits to the site's REST route (aiad/v1/contact, found through the discovery
 * link by restUrl() in aiad/rest) and shows the answer. A script module, "aiad/contact-form" (inc/setup.php). The
 * fields that show for each role are toggled in main.js; the server checks everything again.
 */
import { restUrl } from 'aiad/rest';

const form = document.getElementById('aiad-contact-form');
const formStatus = document.getElementById('form-status');

if (form) {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // Client-side validation (role-specific required fields). Keep in sync with the server: aiad_rest_contact_form() in modules/contact.php.
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
            errSpan.style.color = '#A32D2D';
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

        try {
            const url = restUrl('aiad/v1/contact');
                if (!url) {
                    throw new Error('no REST discovery link');
                }
                const response = await fetch(url, {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();

            const msgSpan = document.createElement('span');
            // The route answers 200 with the message and the pledge count, or an error status with the message.
                if (response.ok) {
                msgSpan.style.color = 'var(--green-600)';
                msgSpan.textContent = data.message;
                formStatus.textContent = '';
                formStatus.appendChild(msgSpan);
                form.reset();
                document.dispatchEvent(new CustomEvent('aiad:contactSuccess'));

                // Update pledge counter if server returned updated count.
                if (typeof data.pledge_count !== 'undefined') {
                    var pledgeWrap = document.querySelector('[data-pledge-counter]');
                    if (pledgeWrap) {
                        var countEl = pledgeWrap.querySelector('[data-pledge-count]');
                        var fillEl  = pledgeWrap.querySelector('[data-pledge-fill]');
                        var barEl   = pledgeWrap.querySelector('.pledge-counter__bar');
                        var count   = parseInt(data.pledge_count, 10);
                        var goal    = parseInt(pledgeWrap.getAttribute('data-goal') || data.pledge_goal, 10) || 500;
                        var pct     = Math.min(100, Math.round((count / goal) * 100));
                        if (countEl) countEl.textContent = count.toLocaleString();
                        if (fillEl)  fillEl.style.width  = pct + '%';
                        if (barEl)   barEl.setAttribute('aria-valuenow', String(count));
                    }
                }
            } else {
                msgSpan.style.color = '#A32D2D';
                msgSpan.textContent = data.message;
                formStatus.textContent = '';
                formStatus.appendChild(msgSpan);
            }
        } catch (err) {
            const errSpan = document.createElement('span');
            errSpan.style.color = '#A32D2D';
            errSpan.textContent = 'Network error. Please try again.';
            formStatus.textContent = '';
            formStatus.appendChild(errSpan);
        }

        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}
