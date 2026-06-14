# AI Risk Benchmark — Product & Intervention Strategy

> **Status:** Architecture and role mapping are production-ready. **Interventions are the product gap.**  
> **Companion docs:** [`benchmark-architecture.md`](benchmark-architecture.md) · [`benchmark-resource-hub-content.md`](benchmark-resource-hub-content.md)

---

## Core principle

> The benchmark correctly diagnoses **behavioural AI risk**. Hub pages must help people **change behaviour** — not “read an article.”

| Layer | Role |
|-------|------|
| **Benchmark** | Diagnosis — scores, weak domains, risk heat map |
| **Hub** | Intervention — frameworks, downloads, training, retake |
| **Consultation / tools** | Conversion — policy generator, governance review, CPD |

**Refined framing:** The risk is not that pages lack content. The risk is that pages become **long articles**. The benchmark identifies behaviour; the **intervention** should change behaviour.

---

## Executive summary

| Layer | Status |
|-------|--------|
| Benchmark SPA, scoring, role results | ✅ Built |
| Improvement pathways → hub links | ✅ Built |
| Consent + submission persistence | ✅ v1.16.0 |
| Click / event tracking | ✅ v1.16.0 |
| Five-question intervention template | ✅ v1.17.0 |
| Signature frameworks (copy) | 🟡 Verify + Think First seeded; PDFs pending |
| **Interactive Policy Generator** | 🔴 Highest commercial priority |
| **Progression + maturity models** | 🟡 Specified below; unlock logic = Readiness Journey |
| **National Benchmark Report 2027** | 🔴 Awaiting consented sample |

---

## Assessment (product readiness)

| Dimension | Score | Notes |
|-----------|-------|-------|
| Architecture | 9.5/10 | Benchmark → pathway → hub → consultation is correct |
| Role mapping | 9.5/10 | Teacher / student / parent / leader interventions align with audit |
| Benchmark logic | 9/10 | Behavioural risk + signature metrics |
| Commercial pathway | 9/10 | Policy Generator is the anchor |
| Intervention strategy | 8.5/10 | Frameworks spec’d; downloads and tools still to build |
| Differentiation | 9.5/10 | Verify Before You Trust + Think First as owned methodologies |

**Conclusion:** The benchmark is doing its job. The next **60 days** are intervention build, not more audit questions.

---

## The funnel

```
Benchmark → Results → Improvement Pathway → Intervention Hub → Tool / Consultation
```

Stop calling Level 2 **“resources.”** They are **frameworks** and **interventions**.

---

## Five-question intervention framework

Every hub page answers:

| # | Section | Purpose |
|---|---------|---------|
| 1 | **Why does this matter?** | Tie to weak score / behavioural risk |
| 2 | **What should I do?** | Steps — not prose |
| 3 | **What can I download?** | Real checklist, template, register |
| 4 | **What training exists?** | AI Awareness Day, webinar, CPD |
| 5 | **How do I improve my benchmark score?** | Retake with `airb_ref` tracking |

**Code:** `AIRB_Hub_Content` → `includes/class-airb-hub-content.php`

---

## Signature frameworks (platform IP)

These are more defensible than generic AI guidance — they become associated with the platform.

### Verify Before You Trust Framework™

- **Slug:** `teacher-ai-verification-framework`
- **Unlocks:** Teacher progression Level 2 (Verification Ready)
- **Deliverable:** PDF checklist + optional 10-min masterclass

### Think First, Prompt Second™

- **Slug:** `think-first-prompt-second`
- **Unlocks:** Student progression + shareable wallet card
- **Deliverable:** Student PDF; high viral potential

---

## School Maturity Framework (leaders)

The biggest missing **school-level** model. Makes leadership reports and governor conversations meaningful.

| Alignment score | Maturity | Leader narrative |
|-----------------|----------|------------------|
| 0–25 | **Emerging** | Ad hoc AI use; no coherent governance |
| 26–50 | **Developing** | Policy in progress; uneven staff practice |
| 51–75 | **Established** | Documented controls; staff awareness improving |
| 76–100 | **Leading** | Evidence-led; annual review; whole-school culture |

### Maturity → interventions

| Maturity | Priority interventions |
|----------|------------------------|
| Emerging | AI Policy Generator · DfE Compliance Checklist · Leader benchmark |
| Developing | School AI Governance · AI Risk Register · Staff verification CPD |
| Established | Annual Benchmark Review · Parent sessions · Safeguarding refresh |
| Leading | National data opt-in · AI Champion Programme · MAT/trust roll-up |

**Hub page:** `school-ai-maturity`  
**Next build:** Show maturity band on leader results + link intervention set (PHP: `AIRB_Leader_Results` + maturity helper).

---

## AI Champion Programme (teacher progression)

High-performing teachers need a **visible progression model** — not a dead end after a strong score.

| Level | Status | Typical unlock (proposed) |
|-------|--------|---------------------------|
| **Level 1** | AI Aware | Complete teacher benchmark |
| **Level 2** | Verification Ready | Human Oversight ≥ 70% + Verify Before You Trust completed |
| **Level 3** | Responsible AI Practitioner | All domain readiness ≥ 65%; privacy checklist done |
| **Level 4** | AI Champion | Retake shows improvement; department session delivered |
| **Level 5** | School AI Lead | SLT appointment; coordinates annual benchmark + risk register |

**Hub page:** `ai-champion-programme`  
**Next build:** Badge display on teacher results; journey events (`badge_awarded`); link from `AIRB_Teacher_Results` champion pathway.

---

## Commercial anchor: AI Policy Generator

> Strongest commercial asset.

Leaders don't wake up wanting training. They need:

- a **policy**
- **governance evidence**
- a **governor report**
- a **risk register**
- an answer for Ofsted, governors, parents, or the trust

When **Governance Score ≤ ~70%**:

1. Primary / Secondary / All-through  
2. MAT / Standalone  
3. Staff AI allowed? Student AI allowed? Assessment approach?  
4. → **Draft School AI Policy**

**Next build:** `[airb_policy_generator]` shortcode.

---

## National Benchmark Report 2027

Authority-building asset. Creates a reason for schools to opt in to anonymous data.

**Planned headline metrics:**

- Average Teacher Human Oversight  
- Average Student AI Dependency  
- Average Parent AI Awareness  
- Top Governance Risks  

**Segmentation:**

- Primary · Secondary · All-through  
- MAT · Standalone  

**Privacy:** Consented submissions only; `BENCHMARK_MIN_SAMPLE` privacy floor per role/segment.

**Hub page:** `national-benchmark-report`

---

## 60-day build priority

| # | Deliverable | Type |
|---|-------------|------|
| 1 | Verify Before You Trust Framework™ | Framework + PDF |
| 2 | Think First, Prompt Second™ | Framework + PDF |
| 3 | AI Policy Generator | Interactive tool |
| 4 | AI Risk Register | Downloadable template |
| 5 | School Maturity Framework | Leader results + hub |
| 6 | AI Champion Programme | Progression + badges |

Items 5–6 consent + tracking: ✅ already shipped.

Once these exist, the benchmark stops being an **assessment** and becomes a **school AI improvement ecosystem**.

---

## Role → intervention mapping

| Role | Weak domains | Primary interventions |
|------|--------------|----------------------|
| **Teacher** | Human Oversight, Privacy, Assessment | Verification Framework, Lesson Checklist, Privacy Guide |
| **Student** | Dependency, Verification | Think First, Study Skills, Check AI Answers |
| **Parent** | Home safety, Homework | Safety Guide, Homework Guide, Deepfakes |
| **Leader** | Governance, Safeguarding | Policy Generator, Governance, Risk Register, Maturity |

Full link table: [`benchmark-resource-hub-content.md` §4](benchmark-resource-hub-content.md)

---

## Hub page registry (20 pages)

| Slug | Purpose |
|------|---------|
| `teacher-ai-verification-framework` | Verify Before You Trust™ |
| `think-first-prompt-second` | Think First, Prompt Second™ |
| `ai-policy-generator` | Policy Generator (tool TBC) |
| `ai-risk-register` | Risk register template |
| `school-ai-maturity` | School Maturity Framework |
| `ai-champion-programme` | Teacher progression L1–L5 |
| `national-benchmark-report` | UK School AI Benchmark 2027 |
| `annual-benchmark-review` | Leader annual cycle |
| *+ 12 role hub pages* | Domain-specific interventions |

---

## Verification before publish

- [ ] DfE / ICO / KCSIE / JCQ claims cited  
- [ ] No download link without a real file  
- [ ] Page is intervention, not article  
- [ ] Retake CTA includes `airb_ref`  
- [ ] Maturity / progression labels match scoring bands  

---

## File reference

| File | Role |
|------|------|
| `includes/class-airb-hub-content.php` | Intervention content + progression copy |
| `includes/class-airb-defaults.php` | Hub registry, improvement pathways |
| `benchmark-architecture.md` | Technical architecture + Readiness Journey |
| `benchmark-resource-hub-content.md` | WordPress export for fact-checking |

---

## Product vision (one line)

**Benchmark → diagnose behaviour → intervention changes behaviour → maturity / progression proves improvement → national data builds authority → consultation converts.**
