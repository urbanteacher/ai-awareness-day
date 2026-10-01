/**
 * The lesson clock, as the lesson page works it out (aiad_resource_lesson_steps()
 * in the theme's inc/resource-lesson.php), so the editor shows the same total.
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Seconds in a step's time as typed: "2 min", "60 seconds", "1 hour"; a range counts its upper end.
 *
 * @param {string} text The time as typed.
 * @return {number} Seconds, or 0 when it does not read as a time.
 */
export function durationSeconds( text ) {
	const m = String( text || '' )
		.toLowerCase()
		.match(
			/(\d+(?:\.\d+)?)(?:\s*[-–to]+\s*(\d+(?:\.\d+)?))?\s*(h|hr|hrs|hour|hours|m|min|mins|minute|minutes|s|sec|secs|second|seconds)\b/
		);
	if ( ! m ) {
		return 0;
	}
	const amount = parseFloat( m[ 2 ] || m[ 1 ] );
	const unit = m[ 3 ].charAt( 0 );
	const unitSeconds = { h: 3600, s: 1, m: 60 };
	return Math.round( amount * unitSeconds[ unit ] );
}

/**
 * The lesson's time, and its optional time apart. 0 total when a required step has no time.
 *
 * @param {Object[]} steps The lesson's steps.
 * @return {{total: number, optional: number}} Seconds.
 */
export function lessonTime( steps ) {
	let total = 0;
	let optional = 0;
	let timed = true;
	steps.forEach( ( step ) => {
		if ( ! String( step?.action || '' ).trim() ) {
			return;
		}
		const seconds = durationSeconds( step?.duration );
		if ( step?.optional ) {
			optional += seconds;
			return;
		}
		if ( ! seconds ) {
			timed = false;
		}
		total += seconds;
	} );
	return { total: timed ? total : 0, optional };
}

export function lengthLabel( seconds ) {
	if ( seconds < 60 ) {
		return sprintf(
			/* translators: %d: seconds. */
			__( '%d sec', 'aiad-core' ),
			seconds
		);
	}
	const minutes = Math.round( seconds / 60 );
	if ( minutes < 60 ) {
		return sprintf(
			/* translators: %d: minutes. */
			__( '%d min', 'aiad-core' ),
			minutes
		);
	}
	return sprintf(
		/* translators: 1: hours, 2: minutes. */
		__( '%1$d hr %2$d min', 'aiad-core' ),
		Math.floor( minutes / 60 ),
		minutes % 60
	);
}
