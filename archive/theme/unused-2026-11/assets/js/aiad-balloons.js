/**
 * balloons-js — launch moment + successful registration (front page only).
 *
 * @package AI_Awareness_Day
 */
import { balloons } from './vendor/balloons-js.esm.js';

function prefersReducedMotion() {
	return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function localDateYmd() {
	const d = new Date();
	const y = d.getFullYear();
	const m = String(d.getMonth() + 1).padStart(2, '0');
	const day = String(d.getDate()).padStart(2, '0');
	return `${y}-${m}-${day}`;
}

function liveMomentAlreadyThisSession() {
	try {
		return sessionStorage.getItem('aiad_balloons_live_once') === '1';
	} catch (e) {
		return false;
	}
}

function markLiveMomentThisSession() {
	try {
		sessionStorage.setItem('aiad_balloons_live_once', '1');
	} catch (e) {
		/* private mode */
	}
}

async function runBalloons() {
	if (prefersReducedMotion()) {
		return;
	}
	try {
		await balloons();
	} catch (err) {
		if (typeof console !== 'undefined' && console.warn) {
			console.warn('balloons-js:', err);
		}
	}
}

function onCountdownLive() {
	if (prefersReducedMotion() || liveMomentAlreadyThisSession()) {
		return;
	}
	markLiveMomentThisSession();
	void runBalloons();
}

/**
 * First visit on the calendar day matching data-event-date (e.g. 4 June local time).
 * Skipped if this session already had a “live” balloon from the countdown.
 */
function maybeEventDayFirstVisit() {
	if (prefersReducedMotion() || liveMomentAlreadyThisSession()) {
		return;
	}
	const root = document.querySelector('.hero-countdown[data-event-date]');
	if (!root) {
		return;
	}
	const eventDate = root.getAttribute('data-event-date');
	if (!eventDate || localDateYmd() !== eventDate) {
		return;
	}
	try {
		if (localStorage.getItem(`aiad_balloons_event_day_${eventDate}`) === '1') {
			return;
		}
		localStorage.setItem(`aiad_balloons_event_day_${eventDate}`, '1');
	} catch (e) {
		/* still celebrate once this session */
	}
	markLiveMomentThisSession();
	void runBalloons();
}

async function onContactSuccess() {
	if (prefersReducedMotion()) {
		return;
	}
	try {
		await balloons();
	} catch (err) {
		if (typeof console !== 'undefined' && console.warn) {
			console.warn('balloons-js (contact):', err);
		}
	}
}

document.addEventListener('aiad:countdownLive', onCountdownLive);
document.addEventListener('aiad:contactSuccess', onContactSuccess);

maybeEventDayFirstVisit();
