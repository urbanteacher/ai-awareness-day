/**
 * Counters: clicks on schedule joins, partner and AI resources, tools and the hero stats; views of resource, blog,
 * timeline and session pages; resource downloads. Registered as the script module "aiad/tracking" (inc/setup.php).
 *
 * Every call is fire-and-forget through sendBeacon() in aiad/rest, to the routes in modules/tracking/rest.php.
 */
import { sendBeacon } from 'aiad/rest';

/**
 * A click, share, join or view on content that has engagement counters.
 *
 * @param {string|number} postId    The content's ID; 0 for the hero stat, which belongs to no post.
 * @param {string}        event     click, join, share, calendar, marquee, view or hero_partners_stat.
 * @param {string}        [targetUrl] The link followed, so an article on this site is credited too.
 */
function engagement( postId, event, targetUrl ) {
	if ( ! event || ( event !== 'hero_partners_stat' && ! postId ) ) {
		return;
	}
	sendBeacon( 'aiad/v1/track/engagement', { post_id: postId || 0, event, target_url: targetUrl } );
}

function sessionIdFromEl( el ) {
	if ( ! el ) {
		return '';
	}
	if ( el.dataset && el.dataset.sessionId ) {
		return el.dataset.sessionId;
	}
	const host = el.closest( '[data-session-id]' );
	return host ? host.getAttribute( 'data-session-id' ) || '' : '';
}

document.addEventListener(
	'click',
	( e ) => {
		const heroStat = e.target.closest( 'a.hero-stats__item[data-track-engagement]' );
		if ( heroStat ) {
			engagement( 0, heroStat.getAttribute( 'data-track-engagement' ) || '' );
			return;
		}

		const marqueeLink = e.target.closest( 'a.hero-partner-marquee__link[data-partner-id]' );
		if ( marqueeLink ) {
			engagement( marqueeLink.getAttribute( 'data-partner-id' ), 'marquee' );
			return;
		}

		const featuredLink = e.target.closest( '#partner-resources a[data-featured-resource-id]' );
		if ( featuredLink ) {
			engagement( featuredLink.getAttribute( 'data-featured-resource-id' ), 'click' );
			return;
		}

		const toolLink = e.target.closest( 'a[data-tool-id]' );
		if ( toolLink ) {
			engagement( toolLink.getAttribute( 'data-tool-id' ), 'click' );
			return;
		}

		const partnerCard = e.target.closest( 'a.partner-card--ai-resources[data-partner-id]' );
		if ( partnerCard ) {
			engagement( partnerCard.getAttribute( 'data-partner-id' ), 'click' );
			return;
		}

		const joinBtn = e.target.closest(
			'.session-single__btn--primary[href], .aiad-schedule-table__cta[href], a.aiad-schedule-card__join[href], a.aiad-schedule-table__cta--icon[href]'
		);
		if ( joinBtn ) {
			engagement( sessionIdFromEl( joinBtn ), 'join' );
			return;
		}

		const titleLink = e.target.closest( '.aiad-schedule-card__title, .aiad-schedule-filter-item td a[href]' );
		if ( titleLink && titleLink.closest( '.aiad-schedule-filter-item, .aiad-schedule-card' ) ) {
			engagement( sessionIdFromEl( titleLink ), 'click', titleLink.href || '' );
			return;
		}

		const icsBtn = e.target.closest(
			'.aiad-schedule-card__ics, .session-single__ics, .aiad-schedule-item__ics, .aiad-schedule-table__ics'
		);
		if ( icsBtn ) {
			engagement( sessionIdFromEl( icsBtn ), 'calendar' );
			return;
		}

		const shareBtn = e.target.closest(
			'.aiad-schedule-card__share, .session-single__share, .aiad-schedule-table__share'
		);
		if ( shareBtn ) {
			engagement( sessionIdFromEl( shareBtn ), 'share' );
			return;
		}

		// A resource file downloaded.
		const downloadLink = e.target.closest( '.resource-download-link, a[download]' );
		if ( downloadLink ) {
			const resourceId = downloadLink.getAttribute( 'data-resource-id' );
			if ( resourceId ) {
				sendBeacon( 'aiad/v1/track/resource/' + encodeURIComponent( resourceId ) + '/download' );
			}
		}
	},
	true
);

// A resource page was opened.
const resourceCard = document.querySelector( 'article.rl-article, article.resource-activity-card' );
if ( resourceCard && resourceCard.id ) {
	const resourceId = resourceCard.id.replace( 'post-', '' );
	if ( resourceId ) {
		sendBeacon( 'aiad/v1/track/resource/' + encodeURIComponent( resourceId ) + '/view' );
	}
}

// A blog post, timeline entry or session page was opened. A session page names itself; otherwise the ID is in the
// body class WordPress gives every single page (postid-N), which is also there when the template has no <article id>.
const body = document.body;
if ( body && ( body.classList.contains( 'single-post' ) || body.classList.contains( 'single-timeline' ) || body.classList.contains( 'single-live_session' ) ) ) {
	const session = document.querySelector( 'article.session-single[data-session-id]' );
	const fromClass = Array.from( body.classList ).find( ( name ) => /^postid-\d+$/.test( name ) );
	const viewedId = session ? session.getAttribute( 'data-session-id' ) : ( fromClass || '' ).replace( 'postid-', '' );
	engagement( viewedId, 'view' );
}
