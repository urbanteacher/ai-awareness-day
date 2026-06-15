/**
 * AIRB core utilities shared across benchmark scripts.
 */
(function () {
	'use strict';

	window.AIRB = window.AIRB || {};

	/**
	 * @param {*} str Value to escape.
	 * @returns {string}
	 */
	AIRB.esc = function esc(str) {
		return String(str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;');
	};

	/** Shared runtime handles registered by airb-front.js after boot. */
	AIRB.runtime = {
		state: null,
		el: null,
		cfg: null,
		i18n: null,
	};

	/**
	 * @param {object} handles { state, el, cfg, i18n }
	 */
	AIRB.registerRuntime = function registerRuntime(handles) {
		if (!handles || typeof handles !== 'object') {
			return;
		}
		if (handles.state) {
			AIRB.runtime.state = handles.state;
		}
		if (handles.el) {
			AIRB.runtime.el = handles.el;
		}
		if (handles.cfg) {
			AIRB.runtime.cfg = handles.cfg;
		}
		if (handles.i18n) {
			AIRB.runtime.i18n = handles.i18n;
		}
	};
}());
