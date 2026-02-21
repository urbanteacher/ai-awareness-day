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
