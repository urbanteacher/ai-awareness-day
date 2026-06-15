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
}());
