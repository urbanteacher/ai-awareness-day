# AI Risk & Readiness Benchmark — Question bank review

**Purpose:** Editorial and stakeholder review of every audit question across all roles.  
**Source:** `plugins/ai-risk-readiness-benchmark/includes/class-airb-questions.php`  
**Plugin version:** 1.32.0  
**Last synced:** June 2026

> **v1.32.0 changes:** Teacher +2 (`t_trust_judgement`, `t_redesign_beyond_ai`); Student +1 (`s_explain_own_words`); Leader upgrades — student AI-use policy (`l_policy`) and staff training coverage (`l_staff_training`). **70 questions** total.  
> **v1.31.0 changes:** New **Education Support Staff** role (14 questions): AI literacy, human oversight, operational dependency, data protection, safe adoption. Results use Operational Dependency Index, Human Oversight Ratio and Data Protection Readiness.  
> **v1.30.0 changes:** Parent audit rebuilt around **behaviour** (not confidence): hidden AI use, home culture, homework oversight (`p_explain_own_words`), parent AI dependency, school partnership. Five new result metrics. 13 parent questions (was 8).  
> **v1.29.1 changes:** Plain-language leader questions (no DPIA/JCQ/CPD acronyms); exam-rules question (`l_jcq`) shown only for secondary and all-through schools after a leader phase step; teacher approved-tools and parent confidence wording updates.  
> **v1.29.0 changes:** Fixed inverted teacher dependency scoring; added teacher policy + verification questions; student safeguarding + integrity reword; leader incident escalation; parent awareness scoring rework.

---

## Summary

| Role | Questions | Approx. audit time |
|------|-----------|-------------------|
| Teacher | 17 | ~12–15 min |
| Student | 12 | ~10–15 min |
| Parent / Carer | 13 | ~12–18 min |
| School Leader | 14 (13 for primary) | ~10–15 min |
| Education Support Staff | 14 | ~12–18 min |
| **Total** | **70** | |

Each role completes one audit path. Questions are grouped into **sections** in the UI (shown below). Each question maps to a **scoring domain** used for readiness and risk metrics.

---

## Scoring domains

| Domain key | Label (results) |
|------------|-----------------|
| `safe_adoption` | Safe Adoption |
| `human_oversight` | Human Oversight |
| `ai_dependency` | Independent Practice |
| `privacy` | Privacy & Data Protection |
| `safeguarding` | Safeguarding |
| `assessment_integrity` | Assessment Integrity |
| `ai_literacy` | AI Literacy |
| `governance` | Governance |

---

## Assessment criteria & scoring model

### How answers become scores

1. **Per-question risk score (0–3)** — Each answer maps to a score where **0 = strongest readiness / lowest risk** and **3 = weakest readiness / highest risk**.
2. **Domain risk %** — For each scoring domain, average the question scores in that domain, then:  
   `domain risk % = (average score ÷ 3) × 100`
3. **Domain readiness %** — `100 − domain risk %`
4. **DfE Readiness Alignment (overall)** — Average domain risk % across all domains that have at least one answered question, then:  
   `alignment score = 100 − overall risk %` (rounded)
5. **Disclaimer** — This is an educational benchmark score, not a compliance assessment.

### Risk bands (domain & overall risk %)

| Band | Risk % | Display nuance |
|------|--------|----------------|
| Low | 0–30% | 22–30% may show as **Low–Moderate** |
| Moderate | 31–60% | 48–60% may show as **Moderate–High** |
| High | 61–80% | 72–80% may show as **High–Critical** |
| Critical | 81–100% | |

### Readiness bands (overall alignment score)

| Band | Readiness % | Notes |
|------|-------------|-------|
| Emerging | 0–39 | |
| Developing | 40–59 | |
| Established | 60–74 | 60–64 may show as **Early Established** |
| Strong | 75–89 | |
| Leading | 90–100 | |

### Domain criteria (what each domain measures)

| Domain | Measures | UK guidance reference |
|--------|----------|------------------------|
| Safe Adoption | Whether AI tools are assessed before use; responsible adoption decisions | DfE Generative AI Guidance |
| Human Oversight | Verification, cross-checking, challenging AI outputs; % content modified before use | DfE + Ofsted |
| Independent Practice (`ai_dependency`) | Reliance on AI before independent effort; ability to work without AI | DfE Teacher-Led Learning Principle |
| Privacy & Data Protection | Personal data entered into AI; understanding of data-protection risks | ICO |
| Safeguarding | AI-related harm, deepfakes, online safety in procedures or family discussions | KCSIE |
| Assessment Integrity | AI-assisted cheating; homework integrity; assessment review; JCQ rules | JCQ + Ofqual |
| AI Literacy | Understanding AI capabilities, limitations, and appropriate use | DfE |
| Governance | Policy, AI lead, annual review, incident tracking, approved tools | DfE + Ofsted |

### Signature composite metrics

| Metric | How calculated | Used for |
|--------|----------------|----------|
| **AI Dependency Index** | Average risk % from `ai_dependency` questions **plus** role-specific dependency questions (teacher: `t_without_ai`, `t_ai_before_task`, `t_feedback_ai`; student: `s_attempt_first`, `s_without_ai`, `s_submitted_ai`) | Teacher & student results |
| **Human Oversight Ratio** | Teacher: blend of modify-% slider and human-oversight question scores. Displayed as readiness % with band label (Critical reliance → Strong oversight) | Teacher results |
| **Privacy Risk Score** | Privacy domain risk % | All roles with privacy questions |
| **Safeguarding Readiness** | Safeguarding domain readiness % | Leader & parent |
| **Governance Maturity** | Governance domain readiness % | Leader |

### Human Oversight Ratio bands (teacher slider `t_modify_pct`)

| % of AI output modified | Label | Risk score |
|-------------------------|-------|------------|
| 51%+ | Strong oversight | 0 |
| 26–50% | Moderate oversight | 1 |
| 11–25% | High reliance | 2 |
| 0–10% | Critical reliance | 3 |

### Parent results — display domains & weights

Parent results use **five parent-facing metrics** (not the eight DfE domains directly):

| Display domain | Weight | Questions | Metric type |
|----------------|--------|-----------|-------------|
| Parent Awareness Score | 20% | `p_child_uses`, `p_know_tools`, `p_child_unknown_use` | Score (higher = better) |
| Home AI Safety Score | 20% | `p_no_share`, `p_harm_response`, `p_home_ai_culture` | Score |
| Homework Oversight Score | 20% | `p_explain_own_words`, `p_check_suspicion`, `p_hw_first_response` | Score |
| Parent AI Dependency Score | 20% | `p_parent_ai_hw`, `p_parent_ai_comms` | Risk (lower = better) |
| School Partnership Score | 20% | `p_school_expectations`, `p_school_discuss` | Score |

### Student results — learning profile metrics

Students see four skill areas derived from domain scores and dependency index:

| Metric | Source |
|--------|--------|
| Independent Thinking | `100 − AI Dependency Index` |
| Verification Skills | Human Oversight domain readiness |
| Privacy Awareness | Privacy domain readiness |
| AI Literacy | AI Literacy domain readiness |

**Student skill bands** (distinct from school readiness bands):

| Band | Score range |
|------|-------------|
| Beginning | 0–20 |
| Developing | 21–40 |
| Emerging | 41–60 |
| Confident | 61–80 |
| Advanced | 81–100 |

### Recommendation triggers

Tailored recommendations fire when a domain's **risk band** meets or exceeds a configured minimum (typically **High** or **Critical**). Examples: low governance → AI Governance Review; low privacy → Data Protection Checklist; low assessment integrity → JCQ-Aligned Assessment Review Pack.

---

## Results screen — metric mapping by role

This section maps **what you see on the results screen** to **how it is calculated** and **which questions feed it**.

### Weighting model (important)

| Role | Overall readiness weighting |
|------|----------------------------|
| **Teacher** | **Equal weight** across each domain that has answered questions (no custom weights). With 5 active domains, each contributes **~20%** to the overall score. |
| **Student** | **Equal weight** across 5 student domains: `ai_dependency`, `assessment_integrity`, `human_oversight`, `ai_literacy`, `privacy`. |
| **Parent** | **Custom weights** on five parent display metrics (see Parent results section above). |
| **Leader** | **Equal weight** across each domain that has answered questions (typically 6 domains). |

**Within a domain**, all questions in that domain are **equally weighted** (simple average of risk scores 0–3).

Domains with **zero teacher questions** (`safeguarding`, `assessment_integrity`, `governance`) do **not** appear on the teacher results screen and do **not** affect the overall score.

---

### Teacher Benchmark results — full mapping

Example profile: **54% Developing**, domain breakdown 100 / 33 / 22 / 33 / 83 → `(100+33+22+33+83) ÷ 5 = 54%`.

| UI label | Result field | How calculated | Domain / question weight | Questions fed |
|----------|--------------|----------------|--------------------------|---------------|
| **Overall benchmark readiness** | `alignment_score` | `100 − average(domain risk %)` across answered domains | **Equal ~20% per domain** (5 domains) | **Teacher Benchmark results** | All 16 questions (via 5 domains) |
| **Benchmark rating** (e.g. Developing) | `readiness_level_label` | Readiness band from `alignment_score` (40–59 = Developing) | Same as overall | Same as overall |
| **Overall readiness** / **Readiness score** (headline stat) | `alignment_score` | Same as overall benchmark readiness | Same | Same |
| **AI risk score** (headline + detail) | `overall_risk_percentage` | `100 − alignment_score` (e.g. 46% when readiness is 54%) | Inverse of overall | Same as overall |
| **AI Dependency Index** (headline + detail + recap) | `dependency_index` | Average risk score of dependency questions → `(avg ÷ 3) × 100` | **Equal ~33% per question** (3 questions) | `t_ai_before_task`, `t_without_ai`, `t_feedback_ai` |
| **Human Oversight Ratio** (gauge + recap) | `domain_scores.human_oversight.readiness_percentage` | Average risk of HO questions → readiness %; gauge shows this value | **Equal ~25% per question** (4 questions) | `t_modify_pct` (slider scored 0–3), `t_verify`, `t_cross_ref`, `t_challenge` |
| **Human Oversight Ratio label** (e.g. Moderate oversight) | `human_oversight_label` | Band label from oversight readiness % (≤50% = Moderate oversight) | Same as HO domain | Same four questions |
| **Readiness score — by domain** rows | `domain_scores[domain].readiness_percentage` | Per-domain: `100 − (avg question risk scores ÷ 3 × 100)` | Equal weight **within** each domain | See domain table below |
| **AI risk score & AI Dependency Index — detail** | Same fields | Repeat of dependency index + overall risk for the breakdown panel | — | Same as above |
| **Teacher Benchmark — score recap** | `teacher_results.benchmark_summary.metrics` | Readout of readiness, risk, dependency, HO ratio, benchmark rating | — | Composite of fields above |

#### Teacher — domain → questions

| Domain (UI label) | Questions | # Qs | Example readiness |
|-------------------|-----------|------|-------------------|
| Safe Adoption | `t_safe_adoption` | 1 | 100% |
| Human Oversight | `t_modify_pct`, `t_verify`, `t_cross_ref`, `t_challenge` | 4 | 33% |
| Independent Practice | `t_ai_before_task`, `t_without_ai`, `t_feedback_ai` | 3 | 22% |
| Privacy & Data Protection | `t_pupil_data`, `t_send_data`, `t_data_risks` | 3 | 33% |
| AI Literacy | `t_hallucinations`, `t_when_not` | 2 | 83% |

#### Teacher — what is *not* on the results screen

| Domain | Why absent |
|--------|------------|
| Safeguarding | No teacher audit questions |
| Assessment Integrity | No teacher audit questions |
| Governance | No teacher audit questions |

#### Teacher — dependency vs Independent Practice domain

Both use the **same three questions** (`t_ai_before_task`, `t_without_ai`, `t_feedback_ai`), but:

- **Independent Practice domain row** = readiness % from those 3 questions (shown in domain breakdown).
- **AI Dependency Index** = risk % `(avg score ÷ 3) × 100` from the same 3 questions (shown as a **risk** metric — higher = more dependency).

So 22% domain readiness and 78% dependency index are **consistent inverses** of the same underlying answers (100 − 22 ≈ 78).

---

### Student results — mapping (summary)

| UI metric | Questions / domains |
|-----------|---------------------|
| Learning readiness | Equal average of 5 domains: `ai_dependency` (2 Q), `assessment_integrity` (1 Q), `human_oversight` (3 Q), `ai_literacy` (2 Q), `privacy` (2 Q) |
| AI Dependency Index | `s_attempt_first`, `s_without_ai`, `s_submitted_ai` |
| Independent Thinking | `100 − AI Dependency Index` |
| Verification Skills | `s_verify`, `s_textbooks`, `s_spot_mistakes` |
| Privacy Awareness | `s_personal_info`, `s_privacy_risks` |
| AI Literacy | `s_how_ai_works`, `s_wrong` |

---

### School Leader results — mapping (summary)

| UI metric | Domains / questions |
|-----------|---------------------|
| Overall readiness | Equal average of answered domains (typically 6) |
| Governance Maturity | `l_policy`, `l_ai_lead`, `l_annual_review`, `l_incidents`, `l_safe_adoption` |
| Safeguarding Readiness | `l_safeguarding`, `l_deepfakes` |
| DfE Readiness Alignment | Overall `alignment_score` |
| Domain rows | All 13 questions mapped to `governance`, `safeguarding`, `privacy`, `human_oversight`, `assessment_integrity`, `safe_adoption`, `ai_literacy` |

---

### Parent results — mapping (summary)

Uses **weighted** display domains (not the eight DfE domains directly). See **Parent results — display domains & weights** above for question mapping and weights (20% / 20% / 15% / 15% / 30%).

---

## Standard answer scales

### Frequency scale (used on many questions)

| Answer value | Label | Risk score (0–3) |
|--------------|-------|------------------|
| `always` | Always | **0** — strongest readiness |
| `often` | Often | **1** |
| `sometimes` | Sometimes | **2** |
| `rarely` | Rarely or never | **3** — weakest readiness |

*Used by: `t_verify`, `t_cross_ref`, `t_challenge`, `t_ai_before_task`, `t_feedback_ai`, `s_attempt_first`, `s_verify`, `s_textbooks`, `p_discuss_use`, `l_annual_review`, `l_deepfakes`, `l_approved_tools`.*

### Slider (teacher only)

**Question `t_modify_pct`:** 0–100% — “Modify before use”. See Human Oversight Ratio bands above.

---

## Teacher — 14 questions

### Section: Human Oversight

#### 1. `t_modify_pct`
- **Domain:** Human Oversight
- **Type:** Slider (0–100%)
- **Question:** What percentage of AI-generated content do you modify before using it?
- **Retake variant:** Roughly how much AI-generated material do you edit before using it with pupils?
- **Retake variant:** What share of AI output do you change before it reaches pupils or colleagues?
- **Scoring criteria:**

| % modified before use | Human Oversight label | Risk score (0–3) |
| --- | --- | --- |
| `51–100%` | Strong oversight | **0** — Strongest readiness |
| `26–50%` | Moderate oversight | **1** |
| `11–25%` | High reliance | **2** |
| `0–10%` | Critical reliance | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 2. `t_verify`
- **Domain:** Human Oversight
- **Type:** Frequency
- **Question:** Do you verify AI outputs before using them with pupils or colleagues?
- **Retake variant:** Before you use AI output with pupils, do you check it is accurate and appropriate?
- **Retake variant:** Do you review AI-generated content before sharing it in class?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `always` | Always | **0** — Strongest readiness |
| `often` | Often | **1** |
| `sometimes` | Sometimes | **2** |
| `rarely` | Rarely or never | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 3. `t_cross_ref`
- **Domain:** Human Oversight
- **Type:** Frequency
- **Question:** How often do you cross-reference AI outputs with other sources?
- **Retake variant:** How often do you check AI answers against trusted sources or your own knowledge?
- **Retake variant:** Do you compare AI suggestions with textbooks, schemes of work or colleagues?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `always` | Always | **0** — Strongest readiness |
| `often` | Often | **1** |
| `sometimes` | Sometimes | **2** |
| `rarely` | Rarely or never | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 4. `t_challenge`
- **Domain:** Human Oversight
- **Type:** Frequency
- **Question:** Do you challenge AI recommendations when they seem incorrect?
- **Retake variant:** When AI advice looks wrong, do you push back and correct it?
- **Retake variant:** Do you question AI recommendations that do not seem right?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `always` | Always | **0** — Strongest readiness |
| `often` | Often | **1** |
| `sometimes` | Sometimes | **2** |
| `rarely` | Rarely or never | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

---

### Section: Dependency

#### 5. `t_ai_before_task`
- **Domain:** Independent Practice (`ai_dependency`)
- **Type:** Frequency
- **Question:** How often do you use AI before attempting the task yourself?
- **Retake variant:** How often do you reach for AI before trying the task yourself?
- **Retake variant:** Do you typically use AI first, or only after your own initial attempt?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `always` | Always | **0** — Strongest readiness |
| `often` | Often | **1** |
| `sometimes` | Sometimes | **2** |
| `rarely` | Rarely or never | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 6. `t_without_ai`
- **Domain:** Independent Practice (`ai_dependency`)
- **Type:** Custom options
- **Question:** Could you teach effectively without AI for one week?
- **Answers:**
  - Yes, easily
  - Yes, with effort
  - Difficult
  - Not realistically
- **Retake variant:** Could you manage a normal week of teaching without leaning on AI tools?
- **Retake variant:** If AI were unavailable for a week, could you still teach effectively?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `yes_easily` | Yes, easily | **0** — Strongest readiness |
| `yes_some` | Yes, with effort | **1** |
| `difficult` | Difficult | **2** |
| `no` | Not realistically | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 7. `t_feedback_ai`
- **Domain:** Independent Practice (`ai_dependency`)
- **Type:** Frequency
- **Question:** How often do you use AI to write pupil feedback?
- **Retake variant:** How often do you draft pupil feedback using AI before personalising it?
- **Retake variant:** Do you rely on AI to write or rewrite comments on pupils' work?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `always` | Always | **0** — Strongest readiness |
| `often` | Often | **1** |
| `sometimes` | Sometimes | **2** |
| `rarely` | Rarely or never | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

---

### Section: Privacy

#### 8. `t_pupil_data`
- **Domain:** Privacy & Data Protection
- **Type:** Custom options
- **Question:** Have you entered student information into AI tools?
- **Answers:**
  - Never
  - Not sure / might have
  - Yes, but anonymised only
  - Yes, identifiable data
- **Retake variant:** Have you ever typed pupil names, marks or other student information into a public AI tool?
- **Retake variant:** Have identifiable pupil details ever been entered into AI tools you use?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `never` | Never | **0** — Strongest readiness |
| `unsure` | Not sure / might have | **2** |
| `yes_anon` | Yes, but anonymised only | **1** |
| `yes` | Yes, identifiable data | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 9. `t_send_data`
- **Domain:** Privacy & Data Protection
- **Type:** Custom options
- **Question:** Have you entered SEND or sensitive pupil information into AI?
- **Answers:**
  - Never
  - Not sure
  - Yes
- **Retake variant:** Have SEND or other sensitive pupil details ever been entered into an AI tool?
- **Retake variant:** Has confidential pupil information about SEND or vulnerability been shared with AI?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `never` | Never | **0** — Strongest readiness |
| `unsure` | Not sure | **2** |
| `yes` | Yes | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 10. `t_data_risks`
- **Domain:** Privacy & Data Protection
- **Type:** Custom options
- **Question:** Do you understand personal data risks when using AI?
- **Answers:**
  - Yes, clearly
  - Mostly
  - Basic awareness
  - Limited
- **Retake variant:** How well do you understand data-protection risks when using AI at school?
- **Retake variant:** Are you clear on personal-data risks when using AI tools professionally?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `yes` | Yes, clearly | **0** — Strongest readiness |
| `mostly` | Mostly | **1** |
| `basic` | Basic awareness | **2** |
| `no` | Limited | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

---

### Section: Confidence & Competence

#### 11. `t_hallucinations`
- **Domain:** AI Literacy
- **Type:** Custom options
- **Question:** Do you understand AI hallucinations and limitations?
- **Answers:**
  - Yes, and I teach pupils about them
  - Yes, generally aware
  - Basic awareness only
  - Limited understanding
- **Retake variant:** Do you understand that AI can invent facts and how to explain that to pupils?
- **Retake variant:** Are you confident teaching pupils that AI outputs can be wrong or made up?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `confident` | Yes, and I teach pupils about them | **0** — Strongest readiness |
| `aware` | Yes, generally aware | **1** |
| `basic` | Basic awareness only | **2** |
| `limited` | Limited understanding | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 12. `t_when_not`
- **Domain:** AI Literacy
- **Type:** Custom options
- **Question:** Do you know when AI should not be used?
- **Answers:**
  - Yes, confident
  - Mostly
  - Sometimes unsure
  - No
- **Retake variant:** Can you identify tasks where AI should not be used in your classroom?
- **Retake variant:** Are you clear on situations where using AI would be inappropriate for pupils?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `yes` | Yes, confident | **0** — Strongest readiness |
| `mostly` | Mostly | **1** |
| `unsure` | Sometimes unsure | **2** |
| `no` | No | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 13. `t_safe_adoption`
- **Domain:** Safe Adoption
- **Type:** Custom options
- **Question:** Before using a new AI tool with pupils, do you assess benefits vs risks?
- **Answers:**
  - Always, with a clear decision
  - Usually
  - Sometimes
  - No formal check
- **Retake variant:** Before adopting a new AI tool with pupils, do you weigh benefits against risks?
- **Retake variant:** Do you assess pros and cons before introducing a new AI tool to pupils?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `always` | Always, with a clear decision | **0** — Strongest readiness |
| `usually` | Usually | **1** |
| `sometimes` | Sometimes | **2** |
| `no` | No formal check | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

---

## Student — 10 questions

### Section: Dependency

#### 1. `s_attempt_first`
- **Domain:** Independent Practice (`ai_dependency`)
- **Type:** Frequency
- **Question:** Do you attempt work before using AI?
- **Retake variant:** Do you try the work yourself before asking AI for help?
- **Retake variant:** Do you make an attempt on your own before turning to AI?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `always` | Always | **0** — Strongest readiness |
| `often` | Often | **1** |
| `sometimes` | Sometimes | **2** |
| `rarely` | Rarely or never | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 2. `s_without_ai`
- **Domain:** Independent Practice (`ai_dependency`)
- **Type:** Custom options
- **Question:** Could you complete the assignment without AI?
- **Answers:**
  - Yes, confidently
  - Mostly
  - I would struggle
  - No
- **Retake variant:** Could you finish this kind of assignment without using AI?
- **Retake variant:** If AI were not available, could you still complete the work?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `yes` | Yes, confidently | **0** — Strongest readiness |
| `mostly` | Mostly | **1** |
| `struggle` | I would struggle | **2** |
| `no` | No | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 3. `s_submitted_ai`
- **Domain:** Assessment Integrity
- **Type:** Custom options
- **Question:** Have you submitted AI-generated work as your own?
- **Answers:**
  - Never
  - Once or twice
  - Sometimes
  - Often
- **Retake variant:** Have you ever handed in AI-written work as if it were your own?
- **Retake variant:** Have you submitted AI-generated answers without saying so?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `never` | Never | **0** — Strongest readiness |
| `once` | Once or twice | **2** |
| `sometimes` | Sometimes | **3** — Weakest readiness |
| `often` | Often | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

---

### Section: Verification

#### 4. `s_verify`
- **Domain:** Human Oversight
- **Type:** Frequency
- **Question:** Do you check AI answers before handing work in?
- **Retake variant:** Do you check AI answers before submitting work?
- **Retake variant:** Do you review what AI gives you before you hand it in?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `always` | Always | **0** — Strongest readiness |
| `often` | Often | **1** |
| `sometimes` | Sometimes | **2** |
| `rarely` | Rarely or never | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 5. `s_textbooks`
- **Domain:** Human Oversight
- **Type:** Frequency
- **Question:** Do you compare AI answers with textbooks or other sources?
- **Retake variant:** Do you double-check AI answers against books, notes or other sources?
- **Retake variant:** Do you compare AI answers with textbooks or class materials?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `always` | Always | **0** — Strongest readiness |
| `often` | Often | **1** |
| `sometimes` | Sometimes | **2** |
| `rarely` | Rarely or never | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 6. `s_spot_mistakes`
- **Domain:** Human Oversight
- **Type:** Custom options
- **Question:** Have you identified mistakes in AI answers?
- **Answers:**
  - Yes, often
  - Sometimes
  - Rarely
  - Never / not sure
- **Retake variant:** Have you noticed when AI gives wrong or misleading answers?
- **Retake variant:** Can you spot errors or mistakes in AI responses?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `yes_often` | Yes, often | **0** — Strongest readiness |
| `sometimes` | Sometimes | **1** |
| `rarely` | Rarely | **2** |
| `never` | Never / not sure | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

---

### Section: Critical Thinking

#### 7. `s_how_ai_works`
- **Domain:** AI Literacy
- **Type:** Custom options
- **Question:** Do you understand how AI works and its limitations?
- **Answers:**
  - Yes, clearly
  - Mostly
  - A little
  - Not really
- **Retake variant:** Do you understand what AI can and cannot do reliably?
- **Retake variant:** Do you know the main limits of how AI tools work?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `yes` | Yes, clearly | **0** — Strongest readiness |
| `mostly` | Mostly | **1** |
| `basic` | A little | **2** |
| `no` | Not really | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 8. `s_wrong`
- **Domain:** AI Literacy
- **Type:** Custom options
- **Question:** Do you know when AI is wrong?
- **Answers:**
  - Yes, and I check outputs
  - Mostly
  - A little
  - Not really
- **Retake variant:** Do you know AI can be wrong — and do you act on that?
- **Retake variant:** When AI might be incorrect, do you verify before trusting it?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `yes` | Yes, and I check outputs | **0** — Strongest readiness |
| `mostly` | Mostly | **1** |
| `basic` | A little | **2** |
| `no` | Not really | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

---

### Section: Safety

#### 9. `s_personal_info`
- **Domain:** Privacy & Data Protection
- **Type:** Custom options
- **Question:** Have you shared personal information with AI tools?
- **Answers:**
  - Never
  - Not sure
  - Once or twice
  - Yes, regularly
- **Retake variant:** Have you shared private details (name, school, photos) with AI tools?
- **Retake variant:** Have you ever typed personal information into a public AI app?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `never` | Never | **0** — Strongest readiness |
| `unsure` | Not sure | **2** |
| `once` | Once or twice | **2** |
| `yes` | Yes, regularly | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 10. `s_privacy_risks`
- **Domain:** Privacy & Data Protection
- **Type:** Custom options
- **Question:** Do you understand privacy risks when using AI?
- **Answers:**
  - Yes
  - Mostly
  - Unsure
  - No
- **Retake variant:** Do you understand why sharing personal data with AI can be risky?
- **Retake variant:** Are you aware of privacy risks when using AI tools?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `yes` | Yes | **0** — Strongest readiness |
| `mostly` | Mostly | **1** |
| `unsure` | Unsure | **2** |
| `no` | No | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

---

## Parent / Carer — 13 questions

Behaviour-focused audit: measures what parents **do** at home (oversight, AI use, school partnership), not just what they know.

### Section: Awareness

#### 1. `p_child_uses`
- **Domain:** AI Literacy · **Display:** Parent Awareness
- **Question:** How confident are you that you know whether your child uses AI tools?
- **Answers:** Very confident · Fairly confident · Not confident / unsure · Confident they do not use AI

#### 2. `p_know_tools`
- **Domain:** AI Literacy · **Display:** Parent Awareness
- **Question:** Do you know which AI tools your child uses?
- **Answers:** Yes, clearly · Some of them · Vaguely · No

#### 3. `p_child_unknown_use` *(new v1.30)*
- **Domain:** AI Literacy · **Display:** Parent Awareness
- **Question:** How often does your child use AI without you knowing exactly what they used it for?
- **Answers:** Never · Occasionally · Frequently · I don't know
- **Scoring:** `never`=0 · `occasionally`=1 · `frequently`=2 · `dont_know`=3

---

### Section: Home AI safety

#### 4. `p_no_share`
- **Domain:** Privacy · **Display:** Home AI Safety
- **Question:** Do you know what information children should not share with AI?

#### 5. `p_harm_response` *(replaces `p_deepfakes` knowledge question)*
- **Domain:** Safeguarding · **Display:** Home AI Safety
- **Question:** If your child received an AI-generated image, voice message or fake account pretending to be someone they know, would they know what to do?
- **Answers:** Definitely · Probably · Unsure · Unlikely

#### 6. `p_home_ai_culture` *(new v1.30)*
- **Domain:** Safe Adoption · **Display:** Home AI Safety
- **Question:** Which statement best describes AI use in your home?
- **Answers:** We regularly discuss AI use · We occasionally discuss it · AI use happens but we rarely discuss it · I don't know how AI is being used

---

### Section: Homework oversight

#### 7. `p_explain_own_words` *(flagship — new v1.30)*
- **Domain:** Human Oversight · **Display:** Homework Oversight
- **Question:** When your child uses AI for homework, how often do you ask them to explain the answer in their own words?
- **Answers:** Always · Often · Sometimes · Rarely or never
- **Scoring:** `always`=0 · `often`=1 · `sometimes`=2 · `rarely`=3

#### 8. `p_check_suspicion` *(replaces `p_spot_ai_hw`)*
- **Domain:** Assessment Integrity · **Display:** Homework Oversight
- **Question:** If you suspected your child had used AI for homework, how would you check?
- **Answers:** Ask them to explain their work · Compare drafts or working · Use an AI detector · I wouldn't know how

#### 9. `p_hw_first_response` *(new v1.30)*
- **Domain:** Safe Adoption · **Display:** Homework Oversight
- **Question:** If your child is struggling with homework, what is your first response?
- **Answers:** Work through it together · Contact school or teacher · Use online resources · Use AI for help
- **Scoring:** `work_together`=0 · `contact_school`=0 · `online_resources`=1 · `use_ai`=3

---

### Section: Your AI use

#### 10. `p_parent_ai_hw` *(new v1.30 — Parent AI Dependency)*
- **Domain:** AI Dependency · **Display:** Parent AI Dependency (risk metric)
- **Question:** How often do you personally use AI to help your child with schoolwork?
- **Answers:** Never · Occasionally · Weekly · Most homework tasks

#### 11. `p_parent_ai_comms` *(new v1.30)*
- **Domain:** AI Dependency · **Display:** Parent AI Dependency
- **Question:** How often do you use AI to draft emails or messages to school about your child?
- **Answers:** Never · Occasionally · Often · Usually

---

### Section: School partnership

#### 12. `p_school_expectations` *(new v1.30)*
- **Domain:** Governance · **Display:** School Partnership
- **Question:** Do you know your school's expectations for AI use in homework?
- **Answers:** Yes, clearly · Somewhat · I've heard something · No

#### 13. `p_school_discuss` *(new v1.30)*
- **Domain:** Governance · **Display:** School Partnership
- **Question:** How confident would you be discussing your child's AI use with their school if you had concerns?
- **Answers:** Very confident · Fairly confident · Unsure · Not confident

---

**Removed in v1.30** (knowledge/confidence-only): `p_discuss_use`, `p_cheating`, `p_spot_ai_hw`, `p_deepfakes`, `p_equipped` — replaced by behavioural questions above.

---

## Education Support Staff — 14 questions

Operations-focused audit for reception, school office, attendance, HR, finance, exams, data/MIS, trust operations and admin roles.

### Section: AI literacy
- `ss_recognise_inaccuracy` — Recognising inaccurate AI information
- `ss_ai_understanding` — Understanding AI limitations

### Section: Human oversight
- `ss_review_comms` — Reviewing AI-generated emails/letters/reports
- `ss_verify_before_act` — Verifying before acting on AI output
- `ss_spot_subtle_error` — Spotting subtle errors

### Section: Operational dependency
- `ss_draft_comms` — AI use for drafting communications
- `ss_without_ai` — Could you work without AI for one week?
- `ss_task_approach` — Task approach (self first vs AI first)

### Section: Data protection
- `ss_entered_personal` — Entering personal information into AI
- `ss_never_enter` — What must never enter public AI without approval
- `ss_data_rules` — Organisation rules for data in AI tools

### Section: Safe adoption
- `ss_approved_tools` — Approved AI tools
- `ss_check_approval` — Checking approval before new tools
- `ss_report_issue` — Reporting AI data/safeguarding issues

### Results metrics
| Metric | Source |
|--------|--------|
| Readiness Score | Equal average across 5 domains |
| Operational Dependency Index | `ss_draft_comms`, `ss_without_ai`, `ss_task_approach` |
| Human Oversight Ratio | Human oversight domain |
| Data Protection Readiness | Privacy domain readiness |

---

### Section: Governance

#### 1. `l_policy`
- **Domain:** Governance
- **Type:** Custom options
- **Question:** Is there a published AI policy?
- **Answers:**
  - Published & reviewed
  - In draft
  - Informal only
  - No
- **Retake variant:** Does your school have a clear, published policy on AI use?
- **Retake variant:** Is AI use covered in a formal policy that staff can access?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `published` | Published & reviewed | **0** — Strongest readiness |
| `draft` | In draft | **1** |
| `informal` | Informal only | **2** |
| `no` | No | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 2. `l_ai_lead`
- **Domain:** Governance
- **Type:** Custom options
- **Question:** Is there a named AI lead or owner?
- **Answers:**
  - Yes
  - Shared across roles
  - Planned
  - No
- **Retake variant:** Is someone clearly responsible for AI oversight in your school?
- **Retake variant:** Has the school appointed a lead or owner for AI governance?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `yes` | Yes | **0** — Strongest readiness |
| `shared` | Shared across roles | **1** |
| `planned` | Planned | **2** |
| `no` | No | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 3. `l_annual_review`
- **Domain:** Governance
- **Type:** Frequency
- **Question:** Is AI use reviewed annually by leadership?
- **Retake variant:** Does leadership review AI use and risks on a regular basis?
- **Retake variant:** Is AI governance reviewed by senior leaders at least annually?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `always` | Always | **0** — Strongest readiness |
| `often` | Often | **1** |
| `sometimes` | Sometimes | **2** |
| `rarely` | Rarely or never | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 4. `l_safe_adoption`
- **Domain:** Safe Adoption
- **Type:** Custom options
- **Question:** Are new AI tools assessed before adoption (benefits vs risks)?
- **Answers:**
  - Yes, formal process
  - Sometimes
  - Informal only
  - No
- **Retake variant:** Are new AI tools formally assessed before the school adopts them?
- **Retake variant:** Is there a structured check before rolling out new AI tools?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `yes` | Yes, formal process | **0** — Strongest readiness |
| `sometimes` | Sometimes | **1** |
| `informal` | Informal only | **2** |
| `no` | No | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

---

### Section: Safeguarding

#### 5. `l_safeguarding`
- **Domain:** Safeguarding
- **Type:** Custom options
- **Question:** Are AI risks included in safeguarding procedures?
- **Answers:**
  - Yes, explicitly
  - Partially
  - Under review
  - No
- **Retake variant:** Are AI-related safeguarding risks reflected in school procedures?
- **Retake variant:** Do safeguarding policies explicitly cover AI-related harm?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `yes` | Yes, explicitly | **0** — Strongest readiness |
| `partial` | Partially | **1** |
| `review` | Under review | **2** |
| `no` | No | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 6. `l_deepfakes`
- **Domain:** Safeguarding
- **Type:** Frequency
- **Question:** Are deepfakes and AI-enabled harms covered in safeguarding procedures?
- **Retake variant:** Do safeguarding procedures cover deepfakes and AI-enabled abuse?
- **Retake variant:** Are deepfake and AI manipulation risks addressed in safeguarding?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `always` | Always | **0** — Strongest readiness |
| `often` | Often | **1** |
| `sometimes` | Sometimes | **2** |
| `rarely` | Rarely or never | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

---

### Section: Data Protection

#### 7. `l_dp_review`
- **Domain:** Privacy & Data Protection
- **Type:** Custom options
- **Question:** Before pupils use AI tools, has your school checked privacy and data-protection risks?
- **Answers:**
  - Yes, where needed
  - Started
  - Planned
  - Not yet
- **Retake variant:** Before pupils use AI tools, has your school checked privacy and data-protection risks?
- **Retake variant:** Has your school reviewed privacy risks before pupils use AI tools?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `yes` | Yes, where needed | **0** — Strongest readiness |
| `started` | Started | **1** |
| `planned` | Planned | **2** |
| `no` | Not yet | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 8. `l_approved_tools`
- **Domain:** Privacy & Data Protection
- **Type:** Frequency
- **Question:** Are approved AI tools listed for staff and students?
- **Retake variant:** Is there a clear list of AI tools staff and pupils may use?
- **Retake variant:** Are approved AI tools communicated to staff and students?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `always` | Always | **0** — Strongest readiness |
| `often` | Often | **1** |
| `sometimes` | Sometimes | **2** |
| `rarely` | Rarely or never | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

---

### Section: Workforce Readiness

#### 9. `l_staff_training`
- **Domain:** Human Oversight
- **Type:** Custom options
- **Question:** Have staff been trained on AI risks and verification?
- **Answers:**
  - Yes, regular staff training
  - Some staff / one-off
  - Planned
  - No
- **Retake variant:** Have staff received training on AI risks and how to verify outputs?
- **Retake variant:** Is AI risk awareness covered in regular staff training at your school?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `regular` | Yes, regular staff training | **0** — Strongest readiness |
| `some` | Some staff / one-off | **1** |
| `planned` | Planned | **2** |
| `no` | No | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 10. `l_incidents`
- **Domain:** Governance
- **Type:** Custom options
- **Question:** Are AI-related incidents tracked and reviewed?
- **Answers:**
  - Yes, systematically
  - Informally
  - Planned
  - No
- **Retake variant:** Are AI-related incidents logged and reviewed by leadership?
- **Retake variant:** Does the school track and learn from AI-related incidents?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `yes` | Yes, systematically | **0** — Strongest readiness |
| `informal` | Informally | **1** |
| `planned` | Planned | **2** |
| `no` | No | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 11. `l_literacy`
- **Domain:** AI Literacy
- **Type:** Custom options
- **Question:** Is AI literacy included in your curriculum or tutor programme?
- **Answers:**
  - Yes, embedded
  - Pilot / partial
  - Planned
  - Not yet
- **Retake variant:** Is AI literacy taught or discussed through curriculum or tutor time?
- **Retake variant:** Do pupils learn about responsible AI use as part of school provision?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `embedded` | Yes, embedded | **0** — Strongest readiness |
| `pilot` | Pilot / partial | **1** |
| `planned` | Planned | **2** |
| `no` | Not yet | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

---

### Section: Assessment

#### 12. `l_assessment_review`
- **Domain:** Assessment Integrity
- **Type:** Custom options
- **Question:** Are assessments reviewed for AI exposure?
- **Answers:**
  - Yes, systematically
  - Some departments
  - Ad hoc
  - No
- **Retake variant:** Are assessments checked for vulnerability to AI-assisted cheating?
- **Retake variant:** Do you review how AI could affect the integrity of assessments?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `yes` | Yes, systematically | **0** — Strongest readiness |
| `some` | Some departments | **1** |
| `ad_hoc` | Ad hoc | **2** |
| `no` | No | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

#### 13. `l_jcq`
- **Domain:** Assessment Integrity
- **Type:** Custom options
- **Shown for:** Secondary and all-through schools only (`show_for_phases`)
- **Question:** For pupils taking formal qualifications (e.g. GCSE or A Level), do staff who need to know understand your school's rules on AI use in exams and coursework?
- **Answers:**
  - Yes, widely understood
  - In some teams
  - Being rolled out
  - Not yet
- **Retake variant:** For pupils taking formal qualifications (e.g. GCSE or A Level), do staff understand the rules on AI use in exams and coursework?
- **Retake variant:** Do staff who need to know understand your school's rules on AI use in formal exams and assessed work?
- **Scoring criteria:**

| Answer value | Label | Risk score (0–3) |
| --- | --- | --- |
| `yes` | Yes, widely understood | **0** — Strongest readiness |
| `some` | In some teams | **1** |
| `planned` | Being rolled out | **2** |
| `no` | Not yet | **3** — Weakest readiness |

*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*

- **Review notes:**

---

## Review checklist (suggested)

Use this when reviewing each question:

- [ ] **Clarity** — Is the question understandable for the target age/role?
- [ ] **Neutrality** — Does wording avoid leading or shaming respondents?
- [ ] **DfE alignment** — Does it map to the intended domain and UK guidance?
- [ ] **Answer options** — Are all realistic options covered? Any gaps?
- [ ] **Scoring criteria** — Does each answer option score (0–3) reflect the intended readiness signal? Are ties or jumps fair (e.g. `once` and `unsure` both scoring 2)?
- [ ] **Sensitivity** — Any safeguarding, SEND, or privacy concerns in phrasing?
- [ ] **Retake variants** — Do alternate phrasings mean the same thing?
- [ ] **Consistency** — Does terminology match across roles (e.g. “pupils” vs “students”)?

---

## Notes for reviewers

1. **Retake audits** show alternate phrasings (`text_variants`) for the same question ID — scoring is unchanged.
2. **No student PII** is collected at contact step for student/parent roles (optional year group only).
3. Questions are **not editable in the WordPress admin UI** by default; changes require updating `class-airb-questions.php` (or future admin tooling).
4. **Leader question `l_jcq`** is shown only when the leader selects secondary or all-through at the start of the audit (hidden for primary). Wording uses plain English — no JCQ acronym.
