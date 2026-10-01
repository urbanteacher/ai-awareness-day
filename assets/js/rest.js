/**
 * Shared by the theme's script modules (registered as "aiad/rest" in inc/setup.php).
 *
 * Finds the site's REST routes through the discovery link WordPress prints in the page head, so a page carries no
 * address or token of its own and can be cached.
 *
 * @see https://developer.wordpress.org/rest-api/using-the-rest-api/discovery/
 */

/**
 * Address of one of the site's REST routes.
 *
 * @param {string} route Route under the REST root, e.g. "aiad/v1/timeline".
 * @param {Object} [query] Query arguments.
 * @return {string} The address, or '' when the page has no discovery link.
 */
export function restUrl( route, query = {} ) {
	const link = document.querySelector( 'link[rel="https://api.w.org/"]' );
	if ( ! link || ! link.href ) {
		return '';
	}
	const args = Object.entries( query )
		.filter( ( [ , value ] ) => value !== undefined && value !== null && value !== '' )
		.map( ( [ key, value ] ) => encodeURIComponent( key ) + '=' + encodeURIComponent( value ) );
	// With plain permalinks the root is ...?rest_route=/ and the route follows it, so more arguments join with &.
	const joiner = link.href.includes( '?' ) ? '&' : '?';
	return link.href.replace( /\/?$/, '/' ) + route + ( args.length ? joiner + args.join( '&' ) : '' );
}

/**
 * Tell the server something happened, without waiting for it and without holding up the page: a beacon where the
 * browser has one (it survives the page being left), else a keep-alive request. Nothing is returned and a failure is
 * ignored, because a counter is not worth breaking the page for.
 *
 * @param {string} route Route under the REST root.
 * @param {Object} [data] Form fields to send.
 */
export function sendBeacon( route, data = {} ) {
	const url = restUrl( route );
	if ( ! url ) {
		return;
	}
	const body = new URLSearchParams();
	Object.entries( data ).forEach( ( [ key, value ] ) => {
		if ( value !== undefined && value !== null && value !== '' ) {
			body.append( key, String( value ) );
		}
	} );
	if ( typeof navigator.sendBeacon === 'function' && navigator.sendBeacon( url, body ) ) {
		return;
	}
	fetch( url, { method: 'POST', body, keepalive: true } ).catch( () => {} );
}
