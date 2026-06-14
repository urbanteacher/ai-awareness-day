#!/usr/bin/env python3
"""Inject scoring criteria into benchmark-questions-review.md from question bank."""

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PHP = ROOT / "plugins/ai-risk-readiness-benchmark/includes/class-airb-questions.php"
MD = ROOT / "benchmark-questions-review.md"

FREQ = [
    ("always", "Always", 0),
    ("often", "Often", 1),
    ("sometimes", "Sometimes", 2),
    ("rarely", "Rarely or never", 3),
]

SLIDER = [
    ("51–100%", "Strong oversight", 0),
    ("26–50%", "Moderate oversight", 1),
    ("11–25%", "High reliance", 2),
    ("0–10%", "Critical reliance", 3),
]

# Custom options extracted from class-airb-questions.php
CUSTOM = {
    "t_without_ai": [
        ("yes_easily", "Yes, easily", 0),
        ("yes_some", "Yes, with effort", 1),
        ("difficult", "Difficult", 2),
        ("no", "Not realistically", 3),
    ],
    "t_pupil_data": [
        ("never", "Never", 0),
        ("unsure", "Not sure / might have", 2),
        ("yes_anon", "Yes, but anonymised only", 1),
        ("yes", "Yes, identifiable data", 3),
    ],
    "t_send_data": [
        ("never", "Never", 0),
        ("unsure", "Not sure", 2),
        ("yes", "Yes", 3),
    ],
    "t_data_risks": [
        ("yes", "Yes, clearly", 0),
        ("mostly", "Mostly", 1),
        ("basic", "Basic awareness", 2),
        ("no", "Limited", 3),
    ],
    "t_hallucinations": [
        ("confident", "Yes, and I teach pupils about them", 0),
        ("aware", "Yes, generally aware", 1),
        ("basic", "Basic awareness only", 2),
        ("limited", "Limited understanding", 3),
    ],
    "t_when_not": [
        ("yes", "Yes, confident", 0),
        ("mostly", "Mostly", 1),
        ("unsure", "Sometimes unsure", 2),
        ("no", "No", 3),
    ],
    "t_safe_adoption": [
        ("always", "Always, with a clear decision", 0),
        ("usually", "Usually", 1),
        ("sometimes", "Sometimes", 2),
        ("no", "No formal check", 3),
    ],
    "s_without_ai": [
        ("yes", "Yes, confidently", 0),
        ("mostly", "Mostly", 1),
        ("struggle", "I would struggle", 2),
        ("no", "No", 3),
    ],
    "s_submitted_ai": [
        ("never", "Never", 0),
        ("once", "Once or twice", 2),
        ("sometimes", "Sometimes", 3),
        ("often", "Often", 3),
    ],
    "s_spot_mistakes": [
        ("yes_often", "Yes, often", 0),
        ("sometimes", "Sometimes", 1),
        ("rarely", "Rarely", 2),
        ("never", "Never / not sure", 3),
    ],
    "s_how_ai_works": [
        ("yes", "Yes, clearly", 0),
        ("mostly", "Mostly", 1),
        ("basic", "A little", 2),
        ("no", "Not really", 3),
    ],
    "s_wrong": [
        ("yes", "Yes, and I check outputs", 0),
        ("mostly", "Mostly", 1),
        ("basic", "A little", 2),
        ("no", "Not really", 3),
    ],
    "s_personal_info": [
        ("never", "Never", 0),
        ("unsure", "Not sure", 2),
        ("once", "Once or twice", 2),
        ("yes", "Yes, regularly", 3),
    ],
    "s_privacy_risks": [
        ("yes", "Yes", 0),
        ("mostly", "Mostly", 1),
        ("unsure", "Unsure", 2),
        ("no", "No", 3),
    ],
    "p_child_uses": [
        ("yes", "Yes, I know they do", 0),
        ("think", "I think so", 1),
        ("unsure", "Not sure", 2),
        ("no", "No / unlikely", 3),
    ],
    "p_know_tools": [
        ("yes", "Yes, clearly", 0),
        ("some", "Some of them", 1),
        ("vague", "Vaguely", 2),
        ("no", "No", 3),
    ],
    "p_cheating": [
        ("yes", "Yes, clearly", 0),
        ("mostly", "Mostly", 1),
        ("basic", "Basic awareness", 2),
        ("no", "Limited", 3),
    ],
    "p_spot_ai_hw": [
        ("confident", "Yes, confident", 0),
        ("maybe", "Maybe sometimes", 1),
        ("unsure", "Unsure", 2),
        ("no", "No", 3),
    ],
    "p_no_share": [
        ("yes", "Yes", 0),
        ("mostly", "Mostly", 1),
        ("unsure", "Unsure", 2),
        ("no", "No", 3),
    ],
    "p_deepfakes": [
        ("yes", "Yes, and we discuss them", 0),
        ("aware", "Generally aware", 1),
        ("basic", "Basic awareness", 2),
        ("no", "Limited", 3),
    ],
    "p_equipped": [
        ("yes", "Yes", 0),
        ("mostly", "Mostly", 1),
        ("learning", "Still learning", 2),
        ("no", "Not yet", 3),
    ],
    "l_policy": [
        ("published", "Published & reviewed", 0),
        ("draft", "In draft", 1),
        ("informal", "Informal only", 2),
        ("no", "No", 3),
    ],
    "l_ai_lead": [
        ("yes", "Yes", 0),
        ("shared", "Shared across roles", 1),
        ("planned", "Planned", 2),
        ("no", "No", 3),
    ],
    "l_safeguarding": [
        ("yes", "Yes, explicitly", 0),
        ("partial", "Partially", 1),
        ("review", "Under review", 2),
        ("no", "No", 3),
    ],
    "l_dp_review": [
        ("yes", "Yes, with DPIAs where needed", 0),
        ("started", "Started", 1),
        ("planned", "Planned", 2),
        ("no", "Not yet", 3),
    ],
    "l_staff_training": [
        ("regular", "Yes, regular CPD", 0),
        ("some", "Some staff / one-off", 1),
        ("planned", "Planned", 2),
        ("no", "No", 3),
    ],
    "l_incidents": [
        ("yes", "Yes, systematically", 0),
        ("informal", "Informally", 1),
        ("planned", "Planned", 2),
        ("no", "No", 3),
    ],
    "l_assessment_review": [
        ("yes", "Yes, systematically", 0),
        ("some", "Some departments", 1),
        ("ad_hoc", "Ad hoc", 2),
        ("no", "No", 3),
    ],
    "l_jcq": [
        ("yes", "Yes, widely understood", 0),
        ("some", "In some teams", 1),
        ("planned", "Being rolled out", 2),
        ("no", "Not yet", 3),
    ],
    "l_safe_adoption": [
        ("yes", "Yes, formal process", 0),
        ("sometimes", "Sometimes", 1),
        ("informal", "Informal only", 2),
        ("no", "No", 3),
    ],
    "l_literacy": [
        ("embedded", "Yes, embedded", 0),
        ("pilot", "Pilot / partial", 1),
        ("planned", "Planned", 2),
        ("no", "Not yet", 3),
    ],
}

FREQ_IDS = {
    "t_verify", "t_cross_ref", "t_challenge", "t_ai_before_task", "t_feedback_ai",
    "s_attempt_first", "s_verify", "s_textbooks",
    "p_discuss_use",
    "l_annual_review", "l_deepfakes", "l_approved_tools",
}

SLIDER_IDS = {"t_modify_pct"}


def score_table(rows, headers=("Answer value", "Label", "Risk score (0–3)")):
    lines = [
        "- **Scoring criteria:**",
        "",
        "| " + " | ".join(headers) + " |",
        "| " + " | ".join(["---"] * len(headers)) + " |",
    ]
    for row in rows:
        if len(row) == 3:
            val, label, score = row
            signal = "Strongest readiness" if score == 0 else ("Weakest readiness" if score == 3 else "")
            lines.append(f"| `{val}` | {label} | **{score}**{(' — ' + signal) if signal else ''} |")
        else:
            lines.append("| " + " | ".join(str(c) for c in row) + " |")
    lines.append("")
    lines.append("*Risk score 0 = lowest behavioural risk / strongest readiness. Risk score 3 = highest risk.*")
    return "\n".join(lines)


def criteria_for(qid: str) -> str:
    if qid in SLIDER_IDS:
        return score_table(SLIDER, ("% modified before use", "Human Oversight label", "Risk score (0–3)"))
    if qid in CUSTOM:
        return score_table(CUSTOM[qid])
    if qid in FREQ_IDS:
        return score_table(FREQ)
    return ""


CRITERIA_SECTION = """## Assessment criteria & scoring model

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
| Parent Awareness Score | 20% | `p_child_uses`, `p_know_tools` | Score (higher = better) |
| Home AI Safety Score | 20% | `p_discuss_use`, `p_deepfakes` | Score |
| Child Privacy Risk Score | 15% | `p_no_share` | Risk (lower = better) |
| Homework Support Risk Score | 15% | `p_cheating`, `p_spot_ai_hw` | Risk |
| Parent Confidence Score | 30% | `p_equipped` | Score |

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
"""


def main():
    text = MD.read_text(encoding="utf-8")

    # Replace old scoring domains + standard scales with expanded criteria
    pattern = r"## Scoring domains\n\n.*?\n---\n\n## Teacher"
    replacement = (
        "## Scoring domains\n\n"
        "| Domain key | Label (results) |\n"
        "|------------|-----------------|\n"
        "| `safe_adoption` | Safe Adoption |\n"
        "| `human_oversight` | Human Oversight |\n"
        "| `ai_dependency` | Independent Practice |\n"
        "| `privacy` | Privacy & Data Protection |\n"
        "| `safeguarding` | Safeguarding |\n"
        "| `assessment_integrity` | Assessment Integrity |\n"
        "| `ai_literacy` | AI Literacy |\n"
        "| `governance` | Governance |\n\n"
        "---\n\n"
        + CRITERIA_SECTION
        + "\n## Teacher"
    )
    text, n = re.subn(pattern, replacement, text, count=1, flags=re.DOTALL)
    if n != 1:
        raise SystemExit(f"Expected 1 criteria section replace, got {n}")

    # Inject per-question scoring before Review notes
    all_ids = SLIDER_IDS | FREQ_IDS | set(CUSTOM.keys())
    for qid in sorted(all_ids):
        block = criteria_for(qid)
        if not block:
            continue
        marker = f"#### "
        # find question header containing qid
        qpat = rf"(#### \d+\. `{re.escape(qid)}`[\s\S]*?)(- \*\*Review notes:\*\*)"
        def repl(m):
            middle = m.group(1)
            if "**Scoring criteria:**" in middle:
                return m.group(0)
            return middle + block + "\n\n" + m.group(2)
        text, c = re.subn(qpat, repl, text, count=1)
        if c != 1:
            print(f"Warning: could not inject scoring for {qid} (matches={c})")

    MD.write_text(text, encoding="utf-8")
    print(f"Updated {MD}")


if __name__ == "__main__":
    main()
