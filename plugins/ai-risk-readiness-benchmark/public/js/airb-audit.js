/**
 * airb-audit.js
 *
 * Audit helpers airb-front.js uses: which questions apply to a role, and how they group into sections.
 * The unused V1 flow API that was here is kept in archive/airb/js/airb-audit-1.59.0-with-v1-flow.js.
 *
 * Depends on: airb-core.js
 * Exposes: AIRB.Audit
 */
(function () {
	'use strict';

	window.AIRB = window.AIRB || {};

	// -------------------------------------------------------------------------
	// Helpers used by airb-front.js today (profile phase + conditional questions)
	// -------------------------------------------------------------------------

	function questionApplies(q, answers, profilePhase) {
		answers = answers || {};
		var unless = q.show_unless_answer || {};
		var depQid;
		for (depQid in unless) {
			if (!Object.prototype.hasOwnProperty.call(unless, depQid)) {
				continue;
			}
			var depVal = answers[depQid] !== undefined ? String(answers[depQid]) : '';
			if (depVal && unless[depQid].indexOf(depVal) >= 0) {
				return false;
			}
		}
		var phases = q.show_for_phases || [];
		if (!phases.length) {
			return true;
		}
		var phase = typeof profilePhase === 'function' ? profilePhase() : profilePhase;
		if (!phase && answers && answers._school_phase) {
			phase = answers._school_phase;
		}
		if (!phase) {
			return false;
		}
		return phases.indexOf(phase) >= 0;
	}

	function questionsForRole(role, cfg, answers, profilePhase) {
		return (cfg.questions || []).filter(function (q) {
			return q.role === role && questionApplies(q, answers, profilePhase);
		});
	}

	function sectionsForRole(role, cfg, answers, profilePhase) {
		var sections = [];
		var index = {};
		questionsForRole(role, cfg, answers, profilePhase).forEach(function (q) {
			var key = q.section || 'General';
			if (!index[key]) {
				index[key] = { name: key, domain: q.domain, questions: [] };
				sections.push(index[key]);
			}
			index[key].questions.push(q);
		});
		return sections;
	}

	AIRB.Audit = AIRB.Audit || {};
	Object.assign(AIRB.Audit, {
		questionApplies: questionApplies,
		questionsForRole: questionsForRole,
		sectionsForRole: sectionsForRole,
	});
}());
