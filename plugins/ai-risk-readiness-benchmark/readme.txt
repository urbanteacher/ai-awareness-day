=== AI Risk & Readiness Benchmark ===
Contributors: AI Awareness Day
Tags: ai, risk, schools, dfe, benchmark, assessment
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 8.0
Stable tag: 1.5.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

DfE-aligned AI Risk & Readiness Benchmark for UK schools. Shortcodes: [ai_risk_benchmark] and [ai_risk_school_dashboard]

== Description ==

An educational self-assessment tool for teachers, students, parents and school leaders in England. It scores eight domains (Safe Adoption, Human Oversight, AI Dependency, Privacy, Safeguarding, Assessment Integrity, AI Literacy, Governance) and provides tailored recommendations.

**v1.5** implements the full DfE-aligned assessment framework: expanded per-role audits with section headers, domain-to-guidance source table, Human Oversight Ratio™ bands, school-wide exposure breakdown, and platform positioning copy.

**v1.4** adds the commercial funnel — risk heatmap, stage-2 product recommendations, leadership report, score-led consultation pitch, and AI Awareness Day™ gateway.

**v1.2** adds answer-aware next steps for teachers and leaders (policy templates, training, CPD, consultation) and a gateway section framing the audit as a doorway into progress tracking.

**v1.1** adds role-specific result cards, composite metrics, key exposure areas, marketing positioning copy, a school-wide dashboard, and expanded UK guidance references.

**Not legal advice.** No student personal data is collected.

== Installation ==

1. Upload the `ai-risk-readiness-benchmark` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu.
3. Add the shortcode `[ai_risk_benchmark]` to any page or post.
4. Optionally add `[ai_risk_school_dashboard]` on a page for whole-school roll-up (requires consented submissions per role).
5. Visit **AI Risk Benchmark → Settings** to edit questions, scoring, positioning copy and recommendations.

== Docker (this theme repo) ==

Symlink or copy the plugin into your WordPress plugins directory, or mount `plugins/` in docker-compose:

```
./plugins/ai-risk-readiness-benchmark:/var/www/html/wp-content/plugins/ai-risk-readiness-benchmark
```

== Shortcodes ==

`[ai_risk_benchmark]` — individual stakeholder audit (teacher, student, parent, leader)

`[ai_risk_school_dashboard]` or `[ai_risk_school_dashboard school="Your School Name"]` — school-wide readiness view aggregated from consented submissions

== Admin ==

* **AI Risk Benchmark → Submissions** — filter by role, school, risk level, date; export CSV
* **AI Risk Benchmark → School Dashboard** — preview roll-ups by school name
* **AI Risk Benchmark → Settings** — edit intro, disclaimer, positioning, questions, answer scores, recommendations

== Question option format (admin) ==

One per line: `value|label|score` where score is 0 (low risk) to 3 (high risk).

Slider questions leave options empty; modify-percentage maps automatically.

== Changelog ==

= 1.5.0 =
* Full assessment framework — expanded question bank per role (Human Oversight, Dependency, Privacy, etc.)
* Domain-to-benchmark-source table (DfE, ICO, KCSIE, JCQ+Ofqual, Ofsted)
* Audit section headers in the question flow
* School dashboard exposure breakdown (High / Medium / Low per risk area)
* Platform positioning: AI Risk & Readiness Benchmark™ as lead product identity
* Config upgrade v4 replaces question bank on existing installs

= 1.4.0 =
* Commercial funnel: risk heatmap, leadership report, consultation pitch, stage-2 products
* AI Awareness Day™ gateway card after submission
* School phase and org type on contact step for policy generator framing

= 1.2.0 =
* Answer-aware pathway offers for teachers and school leaders (policy, template, training, CPD, consultation)
* Gateway section after results — track progress, book CPD, book consultation
* Role-filtered domain recommendations with offer-type badges
* Editable gateway copy in admin settings

= 1.1.0 =
* Role-specific result cards (teacher, student, parent, leader)
* Composite AI Dependency Index and Human Oversight Ratio scoring
* Key exposure areas on individual and school-wide results
* Marketing positioning layer (problem/solution, eight domains, guidance strip)
* School-wide dashboard shortcode `[ai_risk_school_dashboard]` with AJAX lookup
* Admin school dashboard preview and editable positioning fields
* Non-destructive config upgrade for existing installs

= 1.0.0 =
* Initial release
