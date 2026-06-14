# AI Awareness Day — National Survey Question Bank

**Survey name:** AI Risk & Readiness Benchmark  
**Organisation:** AI Awareness Day  
**Source of truth:** `plugins/ai-risk-readiness-benchmark/includes/class-airb-questions.php`  
**Plugin version:** 1.32.1  
**Last updated:** June 2026

This document lists every question asked in the national benchmark survey. Participants choose one role and complete that role’s audit only.

---

## Overview

| Role | Questions | Typical completion time |
|------|-----------|-------------------------|
| Teacher | 17 | ~12–15 min |
| Student | 12 | ~10–15 min |
| Parent / Carer | 13 | ~12–18 min |
| School Leader | 14 (13 for primary) | ~10–15 min |
| Education Support Staff | 14 | ~12–18 min |
| **Total unique questions** | **70** | |

**Education Support Staff** covers reception, office, HR, finance, exams, data and IT support roles — not classroom teachers.

**School Leader note:** Question `l_jcq` (formal qualifications / exam rules) is shown only for **secondary** and **all-through** schools.

**Repeat audits:** Many questions have alternate wording on repeat visits. The question IDs and scoring stay the same.

---

## Standard response scales

**Frequency (readiness)** — used where higher readiness = less frequent risky behaviour:

- Always
- Often
- Sometimes
- Rarely or never

**Frequency (risk)** — used where higher frequency = higher risk:

- Rarely or never
- Sometimes
- Often
- Always

---

## 1. Teacher (17 questions)

### Human Oversight

**1. `t_modify_pct`** — What percentage of AI-generated content do you modify before using it?  
*Response type: slider (0–100%)*

**2. `t_verify`** — Do you verify AI outputs before using them with pupils or colleagues?  
Always · Often · Sometimes · Rarely or never

**3. `t_cross_ref`** — How often do you cross-reference AI outputs with other sources?  
Always · Often · Sometimes · Rarely or never

**4. `t_challenge`** — Do you challenge AI recommendations when they seem incorrect?  
Always · Often · Sometimes · Rarely or never

**5. `t_trust_judgement`** — When AI and your professional judgement disagree, which do you typically trust?  
Professional judgement · Usually professional judgement · Usually AI · AI

**6. `t_spot_subtle`** — When AI gives an answer that sounds plausible, how confident are you that you could spot a subtle error?  
Very confident · Fairly confident · Not always · Not confident

### Dependency

**7. `t_ai_before_task`** — How often do you use AI before attempting the task yourself?  
Rarely or never · Sometimes · Often · Always

**8. `t_without_ai`** — Could you teach effectively without AI for one week?  
Yes, easily · Yes, with effort · Difficult · Not realistically

**9. `t_feedback_ai`** — How often do you use AI to write pupil feedback?  
Rarely or never · Sometimes · Often · Always

### Privacy

**10. `t_pupil_data`** — Have you entered student information into AI tools?  
Never · Not sure / might have · Yes, but anonymised only · Yes, identifiable data

**11. `t_send_data`** — Have you entered SEND or sensitive pupil information into AI?  
Never · Not sure · Yes

**12. `t_data_risks`** — Do you understand personal data risks when using AI?  
Yes, clearly · Mostly · Basic awareness · Limited

### Confidence & Competence

**13. `t_hallucinations`** — Do you understand AI hallucinations and limitations?  
Yes, and I teach pupils about them · Yes, generally aware · Basic awareness only · Limited understanding

**14. `t_when_not`** — Do you know when AI should not be used?  
Yes, confident · Mostly · Sometimes unsure · No

**15. `t_safe_adoption`** — Before using a new AI tool with pupils, do you assess benefits vs risks?  
Always, with a clear decision · Usually · Sometimes · No formal check

### School guidance

**16. `t_school_policy`** — Do you know which AI tools your school has approved for staff and pupil use?  
Yes — I know the approved list · I know some are approved, but not the full list · Not sure · No — I don't know of an approved list

### Assessment design

**17. `t_redesign_beyond_ai`** — How often do you redesign tasks specifically to require student thinking beyond what AI can easily produce?  
Always · Often · Sometimes · Rarely or never

---

## 2. Student (12 questions)

### Dependency

**1. `s_attempt_first`** — Do you attempt work before using AI?  
Always · Often · Sometimes · Rarely or never

**2. `s_without_ai`** — Could you complete the assignment without AI?  
Yes, confidently · Mostly · I would struggle · No

### Assessment integrity

**3. `s_submitted_ai`** — How tempting would it be to submit AI-generated work without changing it?  
Not at all · Occasionally · Quite tempting · Very tempting

### Verification

**4. `s_verify`** — Do you check AI answers before handing work in?  
Always · Often · Sometimes · Rarely or never

**5. `s_explain_own_words`** — Could you explain an AI-generated answer in your own words?  
Always · Often · Sometimes · Rarely or never

**6. `s_textbooks`** — Do you compare AI answers with textbooks or other sources?  
Always · Often · Sometimes · Rarely or never

**7. `s_spot_mistakes`** — Have you identified mistakes in AI answers?  
Yes, often · Sometimes · Rarely · Never / not sure

### Critical Thinking

**8. `s_how_ai_works`** — Do you understand how AI works and its limitations?  
Yes, clearly · Mostly · A little · Not really

**9. `s_wrong`** — Do you know when AI is wrong?  
Yes, and I check outputs · Mostly · A little · Not really

### Safety

**10. `s_personal_info`** — Have you shared personal information with AI tools?  
Never · Not sure · Once or twice · Yes, regularly

**11. `s_privacy_risks`** — Do you understand privacy risks when using AI?  
Yes · Mostly · Unsure · No

### Safeguarding

**12. `s_report_ai_harm`** — If someone used AI to create a fake image, voice note or message of another student, would you know how to report it?  
Yes · Probably · Unsure · No

---

## 3. Parent / Carer (13 questions)

### Awareness

**1. `p_child_uses`** — How confident are you that you know whether your child uses AI tools?  
Very confident — I know whether they do · Fairly confident · Not confident / unsure · Confident they do not use AI

**2. `p_know_tools`** — Do you know which AI tools your child uses?  
Yes, clearly · Some of them · Vaguely · No

**3. `p_child_unknown_use`** — How often does your child use AI without you knowing exactly what they used it for?  
Never · Occasionally · Frequently · I don't know

### Home AI safety

**4. `p_no_share`** — Do you know what information children should not share with AI?  
Yes · Mostly · Unsure · No

**5. `p_harm_response`** — If your child received an AI-generated image, voice message or fake account pretending to be someone they know, would they know what to do?  
Definitely · Probably · Unsure · Unlikely

**6. `p_home_ai_culture`** — Which statement best describes AI use in your home?  
We regularly discuss AI use · We occasionally discuss it · AI use happens but we rarely discuss it · I don't know how AI is being used

### Homework oversight

**7. `p_explain_own_words`** — When your child uses AI for homework, how often do you ask them to explain the answer in their own words?  
Always · Often · Sometimes · Rarely or never

**8. `p_check_suspicion`** — If you suspected your child had used AI for homework, how would you check?  
Ask them to explain their work · Compare drafts or working · Use an AI detector · I wouldn't know how

**9. `p_hw_first_response`** — If your child is struggling with homework, what is your first response?  
Work through it together · Contact school or teacher · Use online resources · Use AI for help

### Your AI use

**10. `p_parent_ai_hw`** — How often do you personally use AI to help your child with schoolwork?  
Never · Occasionally · Weekly · Most homework tasks

**11. `p_parent_ai_comms`** — How often do you use AI to draft emails or messages to school about your child?  
Never · Occasionally · Often · Usually

### School partnership

**12. `p_school_expectations`** — Do you know your school's expectations for AI use in homework?  
Yes, clearly · Somewhat · I've heard something · No

**13. `p_school_discuss`** — How confident would you be discussing your child's AI use with their school if you had concerns?  
Very confident · Fairly confident · Unsure · Not confident

---

## 4. School Leader (14 questions)

### Governance

**1. `l_policy`** — Does your school have a student AI-use policy?  
Published and embedded · Published · Draft · No policy

**2. `l_ai_lead`** — Is there a named AI lead or owner?  
Yes · Shared across roles · Planned · No

**3. `l_annual_review`** — Is AI use reviewed annually by leadership?  
Always · Often · Sometimes · Rarely or never

**4. `l_incident_escalation`** — If an AI-related safeguarding or data incident occurred tomorrow, would staff know how to escalate it?  
Yes · Mostly · Unsure · No

**5. `l_safe_adoption`** — Are new AI tools assessed before adoption (benefits vs risks)?  
Yes, formal process · Sometimes · Informal only · No

### Safeguarding

**6. `l_safeguarding`** — Are AI risks included in safeguarding procedures?  
Yes, explicitly · Partially · Under review · No

**7. `l_deepfakes`** — Are deepfakes and AI-enabled harms covered in safeguarding procedures?  
Always · Often · Sometimes · Rarely or never

### Data Protection

**8. `l_dp_review`** — Before pupils use AI tools, has your school checked privacy and data-protection risks?  
Yes, where needed · Started · Planned · Not yet

**9. `l_approved_tools`** — Are approved AI tools listed for staff and students?  
Always · Often · Sometimes · Rarely or never

### Workforce Readiness

**10. `l_staff_training`** — What proportion of staff have received AI training in the last 12 months?  
Over 75% · 50–75% · Under 50% · None

**11. `l_incidents`** — Are AI-related incidents tracked and reviewed?  
Yes, systematically · Informally · Planned · No

**12. `l_literacy`** — Is AI literacy included in your curriculum or tutor programme?  
Yes, embedded · Pilot / partial · Planned · Not yet

### Assessment

**13. `l_assessment_review`** — Are assessments reviewed for AI exposure?  
Yes, systematically · Some departments · Ad hoc · No

**14. `l_jcq`** — For pupils taking formal qualifications (e.g. GCSE or A Level), do staff who need to know understand your school's rules on AI use in exams and coursework?  
*Secondary and all-through schools only*  
Yes, widely understood · In some teams · Being rolled out · Not yet

---

## 5. Education Support Staff (14 questions)

*Covers reception, office, HR, finance, exams, data and IT support roles.*

### AI literacy

**1. `ss_recognise_inaccuracy`** — How confident are you in recognising when AI may provide inaccurate information?  
Very confident · Mostly confident · Unsure · Not confident

**2. `ss_ai_understanding`** — Which statement best reflects your understanding of AI?  
AI can make mistakes and should be checked · AI is usually accurate · AI is accurate most of the time · I am not sure

### Human oversight

**3. `ss_review_comms`** — How often do you review and edit AI-generated emails, letters or reports before using them?  
Always · Often · Sometimes · Rarely or never

**4. `ss_verify_before_act`** — If AI produced information that sounded correct, how likely would you be to verify it before acting on it?  
Always verify · Usually verify · Sometimes verify · Rarely verify

**5. `ss_spot_subtle_error`** — How confident are you that you could spot a subtle error in AI-generated content?  
Very confident · Fairly confident · Not always · Not confident

### Operational dependency

**6. `ss_draft_comms`** — How often do you use AI to draft emails, letters or communications?  
Rarely · Sometimes · Often · Daily

**7. `ss_without_ai`** — Could you complete your role effectively without AI for one week?  
Easily · Mostly · With difficulty · No

**8. `ss_task_approach`** — When completing a task, which best describes your approach?  
I attempt it myself before using AI · I sometimes use AI early · I often start with AI · I almost always start with AI

### Data protection

**9. `ss_entered_personal`** — Have you ever entered personal information into an AI tool?  
Never · Occasionally · Unsure · Frequently

**10. `ss_never_enter`** — Which of the following should never be entered into a public AI tool without approval?  
Pupil information · HR records · Safeguarding concerns · All of the above *(correct answer)*

**11. `ss_data_rules`** — How confident are you in understanding your organisation's rules for using data in AI tools?  
Very confident · Mostly confident · Unsure · Not confident

### Safe adoption

**12. `ss_approved_tools`** — Do you know which AI tools are approved for use in your school or trust?  
Yes and I follow the guidance · I know some approved tools · I am unsure · No

**13. `ss_check_approval`** — Before using a new AI tool, how likely are you to check whether it has been approved?  
Always · Often · Sometimes · Rarely or never

**14. `ss_report_issue`** — If an AI-related data protection or safeguarding issue occurred, would you know how to report it?  
Yes · Probably · Unsure · No

---

## Contact & optional fields (not scored)

After the audit, participants may optionally provide:

| Role | Optional fields |
|------|-----------------|
| Teacher | School name, school phase, organisation type (standalone / MAT), email |
| School Leader | School name, organisation type, email |
| Education Support Staff | School name, school phase, organisation type, email |
| Parent / Carer | Child's year group |
| Student | Year group |

---

## Related documents

- **Full editorial review & scoring model:** `benchmark-questions-review.md`
- **Content strategy:** `benchmark-content-strategy.md`
- **Architecture:** `benchmark-architecture.md`

---

*Generated from the live question bank in the AI Risk Readiness Benchmark WordPress plugin.*
