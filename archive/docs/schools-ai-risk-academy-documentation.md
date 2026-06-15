# Schools AI Risk Academy — Application Documentation

A free, single-page web application that teaches UK schools how to govern AI safely **and** gives them a working tool to measure their own exposure. It pairs a short course with an interactive "AI Risk Meter," all aligned to Department for Education (DfE) guidance for England.

This document explains the whole application: what it is, who it is for, how every part works, the methodology behind the scoring, the design and technical implementation, and its known limitations.

---

## 1. Purpose and philosophy

Most AI guidance for schools is written as prose: principles to read and absorb. This application turns that guidance into something a school can **operate**. It rests on one principle drawn from DfE guidance:

> Before adopting AI, a school should be satisfied that the benefits clearly outweigh the risks.

The core design decision follows from that: **the app does not score the AI — it scores the situation around it.** Risk is treated as a property of a *task* or a *habit*, not of a tool. Specifically, risk rises with two things:

- **How much unmanaged AI use a situation allows** (a paper exam allows almost none; unsupervised homework with open AI access allows a great deal).
- **How much is at stake** when that use happens (a practice exercise matters less than work that counts toward a qualification).

Everything in the app — the meter, the methodology, the curriculum — is an expression of that single idea.

---

## 2. Audience

The application serves three overlapping audiences, and the Risk Meter is explicitly divided to address each:

- **Teaching staff** — assess the AI exposure of the tasks and assessments they set.
- **Students** — self-check how much of their own thinking they are handing to AI.
- **School leaders, governors, DSLs and DPOs** — read an aggregate, school-level AI risk level and decide where to act.

A fourth tool (the verification routine) is for everyone.

---

## 3. What a "risk activity" is

A **risk activity** is any task, assessment, or study habit where AI could quietly do the learning, or where unmanaged use threatens academic integrity, data protection, or safeguarding.

The app measures risk activities through **two lenses** that approach the problem from opposite sides, plus a roll-up:

| Lens | What it measures | Who controls it |
|------|------------------|-----------------|
| **Activity exposure** | How much room a task's design leaves for unmanaged AI use | The school (where, how, and what it sets) |
| **Student reliance** | How much of their own thinking a student hands to AI | The student (through habit, task by task) |
| **The roll-up** | The average across assessed activities — the school's overall AI risk level | Emerges from the activities assessed |

A cluster of activities in the high band is the signal to **redesign**, not to ban the technology.

---

## 4. Page structure (information architecture)

The application is a single scrolling page with a sticky navigation header. Sections appear in this order:

1. **Hero** — the thesis, primary calls to action, and an example risk gauge. Badges declare scope (England / DfE), alignment (KCSIE, ICO, Ofsted, JCQ), and cost (free).
2. **Alignment strip** — a band of chips naming the guidance the app is built on, linking down to the sources section.
3. **Methodology ("How it works")** — explains the model before anyone uses it: the thesis, the definition of a risk activity, the two lenses, the four scoring factors with their weights, how points become bands, two worked examples, and the four human-factor traps.
4. **AI Risk Meter** — the interactive core, divided into four tabbed tools (detailed in section 5).
5. **Curriculum** — six expandable course modules (detailed in section 7).
6. **Who built it** — the practitioners behind the course, with an honest framing note.
7. **Templates & live classes** — downloadable resources and webinar/office-hours teasers.
8. **Alignment & sources** — the full list of UK publications with links, plus scope and currency disclaimers (detailed in section 8).
9. **Enrolment** — a free sign-up call to action.
10. **Footer** — secondary navigation and the "not legal advice" notice.

---

## 5. The AI Risk Meter

The centrepiece. Four tools live behind a tab strip; each runs entirely in the browser.

### 5.1 Activity exposure (for staff)

A teacher answers four questions about a task. Each answer carries a point value; the points sum to a maximum of 11 and are then placed on a 0–100 scale and banded.

| Factor | Options and points | Why it matters |
|--------|--------------------|----------------|
| **1. Supervision** | In class, supervised (0) · Light supervision (1) · Home / unsupervised (3) | Unsupervised work gives the most room for unmanaged use. Mirrors the DfE call for close supervision of pupil-facing AI. |
| **2. Device & AI access** | No devices / paper (0) · Devices, AI filtered (1) · Open AI access (3) | Open access raises exposure; filtering or paper-only lowers it. Ties to the filtering & monitoring standards and KCSIE. |
| **3. Stakes** | Formative, not graded (0) · Graded classwork (1) · Counts to a qualification (3) | The higher the stakes, the more an integrity breach costs. Aligns with JCQ malpractice rules. |
| **4. Intent** | Not needed / not possible (0) · Permitted, taught rules (1) · No guidance given (2) | A taught, permitted use is safer than a vacuum or an unenforceable ban. Reflects the DfE emphasis on deciding and communicating use. |

**Scoring formula:** `percentage = round( (sum of the four points) / 11 × 100 )`

**Bands and the action each implies:**

| Band | Score | Action |
|------|-------|--------|
| **Low** | 0–32 | **Keep.** A genuine check on understanding; little room for unmanaged use. |
| **Moderate** | 33–66 | **Decide.** Make AI use intentional, or add supervision/filtering — then write the decision into policy. |
| **High** | 67–100 | **Redesign.** Move it in-person, supervise, filter, or teach permitted use. For graded work, check JCQ rules. |

Each band is framed as an **action, not a label**. After scoring, a teacher can name the activity and add it to the class picture.

#### Worked examples (these match the live tool exactly)

| Activity | Factor breakdown | Raw | Score | Band |
|----------|------------------|-----|-------|------|
| In-person paper exam | Supervised 0 · paper 0 · qualification 3 · AI not possible 0 | 3 / 11 | 27 | Low |
| Graded essay, taken home | Unsupervised 3 · open AI 3 · graded 1 · no guidance 2 | 9 / 11 | 82 | High |

The point: same subject, same pupils — the *design* is what moves the needle.

### 5.2 Student reliance self-check (for students)

Six statements; the student picks how often each is true (Never 0 · Sometimes 1 · Often 2 · Almost always 3). What matters is the **pattern**, not any single answer.

Three statements describe over-reliance (scored as written) and three describe healthy use (reverse-scored, so frequent healthy behaviour *lowers* the risk):

- *Over-reliance:* letting AI write work handed in as one's own; asking AI to think before attempting; copying AI answers without checking.
- *Healthy (reverse-scored):* using AI to check facts after doing the work; using AI to explain something then redoing it; being able to complete the work without AI if needed.

**Scoring:** each item contributes 0–3 (reverse items contribute `3 − answer`); the maximum is 18. `percentage = round( points / 18 × 100 )`, banded as Healthy use / Leaning on it / Over-reliant.

The framing is deliberately **supportive, not punitive** — the over-reliant result explains that when AI produces the work, the grade arrives but the learning does not, and suggests doing a first attempt unaided. It is self-reported, unstored, and **not a diagnostic instrument**.

### 5.3 Class & school risk level (for leaders)

Every activity scored in tool 5.1 can be added here. The tool:

- Averages the exposure scores of all added activities.
- Drives an animated semicircular **gauge** (green → amber → red) whose needle reflects the average.
- Lists each activity with its band and score.
- Surfaces a clear warning when the average sits in the high band: this is the redesign signal.

This is how a school arrives at a single, defensible **AI risk level**.

### 5.4 Verify before you trust (for everyone)

A four-step routine — a *habit*, not a score — adapted from how clinicians are taught to use AI without eroding their own judgement, translated into school language. It works for a student checking their own work or a teacher reviewing an AI's marking or draft. Tapping each step ticks it off, reveals the rationale, and names the trap it guards against:

1. **Form your own view first** — attempt, draft, or mark blind before opening the AI. *(Guards against over-reliance.)*
2. **Compare, don't defer** — when the AI differs, find the specific reason for the gap rather than assuming either side is right. *(Guards against the fluency trap.)*
3. **Check the blind spots** — name what the AI cannot see (effort, working, context, the understanding a fluent paragraph hides). *(Guards against fluency & time pressure.)*
4. **Own the call** — the human signs off and is accountable. *(Guards against responsibility drift.)*

Completing all four shows a confirmation. The routine is intentionally not scored and does not feed the class gauge.

---

## 6. Methodology: the four traps

Exposure explains the *situation*; the four traps explain the *human*. They are the reason even a careful adult hands too much to AI under pressure, and naming the trap is the first defence. They were adapted for schools from a clinical-AI framework (the original "defensive medicine" trap was recast as "responsibility drift," which travels better to education).

| Trap | What it looks like | Who it affects |
|------|--------------------|----------------|
| **Over-reliance** | Assuming the AI is right and quietly stopping checking. | Students & staff |
| **Time pressure** | Under workload, accepting the AI's first draft — a report, feedback, marks — without rigorous review. | Mainly staff |
| **The fluency trap** | A polished, articulate answer feels correct; smoothness hides missing understanding. | Students & staff |
| **Responsibility drift** | Leaning on AI so the decision feels like it isn't yours, to avoid owning a hard judgement. | Students & staff |

The student reliance check looks for the first trap; the verification routine guards against all four.

---

## 7. The curriculum

Six modules, each turning a strand of UK guidance into something teachable. In the app they are an accordion — tapping a module reveals its lessons.

- **M-01 — Foundations & the UK policy landscape.** Generative vs. emerging agentic AI in plain terms; the DfE position and the AI Opportunities Action Plan; why teacher judgement stays central; what "applies to England" means.
- **M-02 — Safety first: assess before you adopt.** Running a use-case risk assessment; staff-facing vs. pupil-facing and under-18s; age restrictions, supervision, filtering & monitoring; KCSIE, safeguarding and AI-enabled risks like deepfakes; the human factors (automation bias and time pressure).
- **M-03 — Data protection & privacy.** Keeping personal and special-category data out of AI tools; when a DPIA is required; transparency with pupils, parents and guardians; automated decisions and profiling of children (ICO).
- **M-04 — Intellectual property & copyright.** Pupils' work as copyright and training models on it; permission from a minor's parent or guardian; secondary infringement; verifying output for accuracy and bias.
- **M-05 — Academic integrity & assessment.** What counts as AI malpractice (JCQ); reviewing homework and unsupervised study; using the Risk Meter to redesign exposed assessments; Ofqual's position on fairness and standards.
- **M-06 — Governance, oversight & your AI policy.** Roles for SLT, governors, DSL and DPO; writing and reviewing a whole-school AI use policy; how Ofsted looks at your AI decisions; training staff and engaging parents and the annual review; embedding the verify-before-you-trust routine in daily practice.

Supporting resources referenced in the app include a school AI-use policy template, a DPIA checklist, an activity exposure audit sheet, and a letter to parents, alongside free live classes and office hours.

---

## 8. Alignment with UK guidance

Everything in the course and the meter traces back to published UK guidance. The app links to each primary source and is explicit that those sources are the authority — the app is a way in, not a substitute.

Sources referenced:

- **DfE — Generative AI in education** (policy paper; the core "safety first, teacher-led, benefits outweigh risks" position; last updated August 2025).
- **DfE — Using AI in education settings** (practical support materials for staff and leaders).
- **DfE — Generative AI: product safety expectations** (what safe AI products for schools should do).
- **Keeping Children Safe in Education (KCSIE)** (statutory; online safety, filtering and monitoring, AI-enabled risks).
- **ICO — AI & data protection (UK GDPR)** (personal data, DPIAs, automated decisions about children).
- **JCQ — AI use in assessments** (preventing and identifying malpractice).
- **Ofsted's approach to AI** (inspectors look at the impact of AI decisions on children, not the tool).
- **Ofqual — regulating AI in qualifications** (protecting fairness and standards).
- **AI Opportunities Action Plan** (the wider government strategy the DfE position sits within).

### Scope and currency

- The DfE framework applies to **England**. Schools in **Scotland, Wales and Northern Ireland** should follow their own national guidance.
- The app reflects guidance **current as of June 2026** (the DfE policy paper was last updated 12 August 2025).
- It is an **educational resource, not legal advice.** Decisions for a specific setting should be made with the school's DPO, DSL and senior leadership, and AI policies should be reviewed at least annually.

---

## 9. Design system

The visual language echoes the parent brand's deep green while signalling "risk/advisory" through a controlled amber, and "learning/field-guide" through its type pairing.

**Colour**

| Token | Hex | Role |
|-------|-----|------|
| Ink (base) | `#14201A` | Deep forest background |
| Ink-2 / Ink-3 | `#1B2B22` / `#22362B` | Raised surfaces, cards |
| Paper | `#F2EFE4` | Light "study material" surfaces (sources section) |
| Amber | `#E0A52E` | Primary accent — caution/advisory, CTAs |
| Sage | `#9FB39A` | Supporting text, lines, mono labels |
| Risk-low / mod / high | `#5FA463` / `#E0A52E` / `#CC5C4D` | Semantic risk severity (green/amber/red) |

Risk severity is always conveyed by **text label as well as colour**, not colour alone.

**Typography**

- **Fraunces** (serif) — display and headings; an academic warmth.
- **IBM Plex Sans** — body; clear and institutional.
- **IBM Plex Mono** — eyebrows, control codes (e.g. `M-01`), data, and the field-guide voice.

**Motion** — section content fades and rises in on scroll; the gauge needle and band markers animate smoothly. All motion is disabled under `prefers-reduced-motion`.

**Layout** — a centred max-width container (~1140px), CSS grid throughout, with responsive breakpoints at 860px (collapses the nav to a menu, stacks two-column grids) and 480px.

---

## 10. Technical implementation

- **A single self-contained HTML file** (~1,045 lines) — markup, CSS, and JavaScript in one document.
- **No framework.** Vanilla JavaScript only. The single external dependency is **Google Fonts**.
- **No build step**, no bundler, no package install. Open the file in any modern browser and it runs.
- **State lives in memory only.** No `localStorage`, `sessionStorage`, cookies, or any browser storage — class-picture data and self-check answers reset on reload. This is deliberate for the data-protection theme: nothing the user enters is stored or transmitted.
- **Interactivity** is built from small, dependency-free patterns: tab switching, accordions driven by `max-height` transitions, an `IntersectionObserver` for scroll reveals, and SVG gauges whose needles rotate by a computed angle (`(average − 50) × 1.8` degrees across the 180° arc).
- **Accessibility:** semantic HTML, ARIA on the tablist and accordions, keyboard-operable controls, visible focus, reduced-motion support, and risk communicated through text + colour.

### Privacy summary

All assessment happens client-side. The activity assessor, student self-check, class roll-up, and verification routine never send data anywhere. The email sign-up and resource/download links are **front-end only** in this build — they validate input and show confirmation but are not wired to a backend.

---

## 11. Who built it

The course is built by risk and governance practitioners and the content is mapped to DfE, Ofsted, ICO and JCQ guidance. The four named contributors come from investment-industry risk, data and AI strategy, ML engineering, and strategy/change backgrounds. The app is explicit that this team's expertise is **risk and governance**, that the content *supports rather than replaces* a school's DSL, DPO and senior leaders, and (see limitations) that an education or safeguarding specialist would strengthen it for a live school audience.

---

## 12. Known limitations and caveats

- **England-only framework.** The statutory backbone (DfE, KCSIE) is for England; other UK nations need their own guidance.
- **Not legal advice.** It is a starting point and a conversation tool, not a compliance guarantee.
- **The scoring is a heuristic.** The factor weights and bands are a reasoned, transparent rubric to prompt good decisions — not a validated psychometric or statistical instrument.
- **Self-reported and unstored.** The student self-check depends on honest input and keeps nothing; it cannot and should not be used to monitor or profile individuals.
- **Front-end only.** No persistence, accounts, real enrolment, or email capture in this build.
- **Author profile.** The named team is finance/risk-led; a school deployment would benefit from a teacher, DSL, or DfE-facing reviewer.

---

## 13. Possible future enhancements

- Link the verification routine to the student reliance result (running the routine lowers assessed reliance risk).
- A printable / PDF version of the activity exposure audit sheet.
- Optional persistence via a real backend for multi-class and whole-school dashboards over time.
- Localised editions for Scotland, Wales and Northern Ireland.
- An education and safeguarding specialist added to the contributor team and review process.

---

## 14. How to use and deploy

1. **Run locally** — open the HTML file in any modern browser; no server required.
2. **Host it** — drop the single file onto any static host (GitHub Pages, Netlify, a school web server, or an internal share).
3. **Adapt the content** — module lessons, resource names, and contributor bios are plain HTML; the scoring factors and weights live in clearly labelled JavaScript arrays near the foot of the file and can be tuned without touching the rest of the app.
4. **Before a real launch** — wire the sign-up form to a compliant backend, confirm the guidance links are current, and have the content reviewed against your setting's own policies and your nation's guidance.

---

*This document describes the application as built. Because UK AI guidance evolves, both the app and this documentation should be reviewed periodically against the primary sources listed in section 8.*
