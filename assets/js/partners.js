import { store, getContext } from '@wordpress/interactivity';

store('aiad/partners', {
    state: {
        get revealText() {
            const ctx = getContext();
            return ctx.isExpanded ? 'Show Less' : 'Show More Partners';
        }
    },
    actions: {
        toggleReveal: () => {
            const ctx = getContext();
            ctx.isExpanded = !ctx.isExpanded;
        }
    }
});

// AI Resources modal
(function initAiModal() {
    const cards = document.querySelectorAll('.partner-card--ai-resources[data-ai-url]');
    if (!cards.length) return;

    const backdrop = document.createElement('div');
    backdrop.className = 'ai-modal-backdrop';
    backdrop.setAttribute('aria-modal', 'true');
    backdrop.setAttribute('role', 'dialog');
    backdrop.innerHTML = `
        <div class="ai-modal">
            <button class="ai-modal__close" aria-label="Close">&#x2715;</button>
            <div class="ai-modal__logo"><img src="" alt="" /></div>
            <p class="ai-modal__label">AI Learning Resources</p>
            <h3 class="ai-modal__name"></h3>
            <p class="ai-modal__stats"></p>
            <a class="ai-modal__cta" href="#" target="_blank" rel="noopener noreferrer">
                Visit AI Resources &#x2197;
            </a>
        </div>
    `;
    document.body.appendChild(backdrop);

    const elLogo  = backdrop.querySelector('.ai-modal__logo');
    const elImg   = backdrop.querySelector('.ai-modal__logo img');
    const elName  = backdrop.querySelector('.ai-modal__name');
    const elStats = backdrop.querySelector('.ai-modal__stats');
    const elCta   = backdrop.querySelector('.ai-modal__cta');
    const closeBtn = backdrop.querySelector('.ai-modal__close');
    let lastFocused = null;

    function open(card) {
        lastFocused = card;
        elImg.src = card.dataset.aiLogo || '';
        elImg.alt = card.dataset.aiName || '';
        elLogo.style.display = card.dataset.aiLogo ? 'flex' : 'none';
        elName.textContent = card.dataset.aiName || '';
        elStats.textContent = card.dataset.aiStats || '';
        elStats.style.display = card.dataset.aiStats ? 'block' : 'none';
        elCta.href = card.dataset.aiUrl;
        backdrop.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        closeBtn.focus();
    }

    function close() {
        backdrop.classList.remove('is-open');
        document.body.style.overflow = '';
        if (lastFocused) lastFocused.focus();
    }

    cards.forEach(card => {
        card.addEventListener('click', () => open(card));
        card.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(card); }
        });
    });

    closeBtn.addEventListener('click', close);
    backdrop.addEventListener('click', e => { if (e.target === backdrop) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
}());
