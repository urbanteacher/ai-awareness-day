import React, { useState, useEffect, useMemo } from "react";

/* ============================================================
   AI Risk & Readiness Benchmark
   DfE-aligned assessment across Teachers, Students, Parents,
   School Leaders. Signature metrics: Human Oversight Ratio,
   AI Dependency Index, DfE AI Alignment Score.
   ============================================================ */

/* ---------- styling (self-contained, no Tailwind compiler needed) ---------- */
const CSS = `
@import url('https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,700;12..96,800&family=Inter:wght@400;500;600&display=swap');

.rb-root{
  --ink:#0F1822; --ink-2:#26333F; --muted:#5C6B78;
  --cloud:#E7EBEC; --card:#FFFFFF; --line:#D7DDDE;
  --critical:#C24A3F; --elevated:#D98432; --moderate:#C2A02A; --strong:#2F8F76;
  --accent:#1B6B8C;
  font-family:'Inter',system-ui,sans-serif;
  color:var(--ink); background:var(--cloud);
  min-height:100%; -webkit-font-smoothing:antialiased;
  line-height:1.5;
}
.rb-root *{box-sizing:border-box;}
.rb-display{font-family:'Bricolage Grotesque','Inter',sans-serif;}
.rb-num{font-family:'Bricolage Grotesque','Inter',sans-serif;font-variant-numeric:tabular-nums;letter-spacing:-0.02em;}
.rb-wrap{max-width:1080px;margin:0 auto;padding:0 22px;}

.rb-bar{position:sticky;top:0;z-index:20;background:var(--ink);color:#EAF0F1;border-bottom:1px solid #1d2a35;}
.rb-bar-in{max-width:1080px;margin:0 auto;padding:14px 22px;display:flex;align-items:center;justify-content:space-between;gap:12px;}
.rb-logo{display:flex;align-items:center;gap:11px;cursor:pointer;}
.rb-mark{width:30px;height:30px;flex:none;}
.rb-logo b{font-family:'Bricolage Grotesque';font-weight:700;font-size:16px;letter-spacing:-0.01em;}
.rb-logo span{display:block;font-size:10.5px;letter-spacing:.13em;text-transform:uppercase;color:#7C909C;margin-top:-1px;}
.rb-ghost{background:transparent;border:1px solid #34424e;color:#CBD7DC;border-radius:999px;padding:8px 15px;font-size:13px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:7px;}
.rb-ghost:hover{border-color:#54636f;background:#16222c;}
.rb-pill{background:#16222c;border:1px solid #2a3742;border-radius:999px;width:21px;height:21px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;font-family:'Bricolage Grotesque';color:#9fd9c9;}

/* hero */
.rb-hero{padding:62px 0 34px;}
.rb-eyebrow{display:inline-flex;align-items:center;gap:9px;font-size:11.5px;letter-spacing:.16em;text-transform:uppercase;color:var(--muted);font-weight:600;}
.rb-eyebrow .dot{width:6px;height:6px;border-radius:50%;background:var(--strong);}
.rb-h1{font-family:'Bricolage Grotesque';font-weight:800;font-size:clamp(34px,6.2vw,62px);line-height:.98;letter-spacing:-0.03em;margin:18px 0 0;}
.rb-h1 em{font-style:normal;color:var(--accent);}
.rb-sub{font-size:clamp(15px,2vw,18px);color:var(--ink-2);max-width:560px;margin:18px 0 0;}
.rb-srcs{display:flex;flex-wrap:wrap;gap:7px;margin-top:22px;}
.rb-src{font-size:11px;font-weight:600;letter-spacing:.04em;color:var(--muted);border:1px solid var(--line);background:var(--card);border-radius:6px;padding:4px 9px;}

.rb-herogrid{display:grid;grid-template-columns:1.15fr .85fr;gap:34px;align-items:center;}
@media(max-width:780px){.rb-herogrid{grid-template-columns:1fr;}}

.rb-gaugecard{background:var(--ink);border-radius:18px;padding:24px 24px 20px;color:#EAF0F1;position:relative;overflow:hidden;}
.rb-gaugecard .tag{font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#7fb9ce;font-weight:600;}
.rb-gaugecard h3{font-family:'Bricolage Grotesque';font-weight:700;font-size:19px;margin:3px 0 0;letter-spacing:-0.01em;}
.rb-gaugecard p{font-size:12.5px;color:#9fb1bc;margin:2px 0 0;}

/* section header */
.rb-shead{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin:0 0 18px;}
.rb-shead h2{font-family:'Bricolage Grotesque';font-weight:700;font-size:24px;letter-spacing:-0.02em;margin:0;}
.rb-shead .num{font-family:'Bricolage Grotesque';font-weight:700;font-size:13px;color:var(--muted);letter-spacing:.1em;}

/* audience cards */
.rb-cards{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;}
@media(max-width:900px){.rb-cards{grid-template-columns:repeat(2,1fr);}}
@media(max-width:520px){.rb-cards{grid-template-columns:1fr;}}
.rb-card{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:20px;cursor:pointer;text-align:left;transition:transform .14s ease,box-shadow .14s ease,border-color .14s;display:flex;flex-direction:column;gap:0;position:relative;}
.rb-card:hover{transform:translateY(-3px);box-shadow:0 14px 30px -18px rgba(15,24,34,.4);border-color:#b9c2c3;}
.rb-card .ic{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:14px;}
.rb-card h4{font-family:'Bricolage Grotesque';font-weight:700;font-size:17px;margin:0;letter-spacing:-0.01em;}
.rb-card p{font-size:13px;color:var(--muted);margin:5px 0 14px;flex:1;}
.rb-card .go{font-size:12.5px;font-weight:600;color:var(--accent);display:flex;align-items:center;gap:6px;}
.rb-card .done{position:absolute;top:16px;right:16px;font-size:10.5px;font-weight:700;letter-spacing:.05em;color:var(--strong);background:#e7f4ef;border-radius:6px;padding:3px 7px;}

/* audit */
.rb-prog{height:5px;background:var(--line);border-radius:99px;overflow:hidden;margin:0 0 26px;}
.rb-prog i{display:block;height:100%;background:var(--accent);border-radius:99px;transition:width .3s ease;}
.rb-domtag{display:inline-flex;align-items:center;gap:8px;font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);}
.rb-domtag .sq{width:9px;height:9px;border-radius:2px;}
.rb-q{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:20px 22px;margin-bottom:13px;}
.rb-q .qt{font-size:15.5px;font-weight:600;margin:0 0 14px;letter-spacing:-0.005em;}
.rb-opts{display:flex;flex-wrap:wrap;gap:8px;}
.rb-opt{border:1px solid var(--line);background:#fbfcfc;border-radius:9px;padding:9px 14px;font-size:13.5px;font-weight:500;cursor:pointer;color:var(--ink-2);transition:all .12s;}
.rb-opt:hover{border-color:#9fb6c0;}
.rb-opt.on{background:var(--ink);color:#fff;border-color:var(--ink);}
.rb-slider{display:flex;align-items:center;gap:16px;}
.rb-slider input{flex:1;accent-color:var(--accent);height:4px;}
.rb-slval{font-family:'Bricolage Grotesque';font-weight:800;font-size:30px;min-width:78px;text-align:right;letter-spacing:-0.02em;}
.rb-sllabel{font-size:12px;font-weight:600;color:var(--muted);margin-top:8px;}

.rb-navrow{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:8px;}
.rb-btn{background:var(--ink);color:#fff;border:none;border-radius:10px;padding:12px 22px;font-size:14px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:8px;font-family:inherit;}
.rb-btn:hover{background:#1c2a36;}
.rb-btn:disabled{opacity:.4;cursor:not-allowed;}
.rb-btn.back{background:transparent;color:var(--ink-2);border:1px solid var(--line);}
.rb-btn.back:hover{background:#fff;border-color:#aebac0;}

/* results / dashboard */
.rb-grid3{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;}
@media(max-width:760px){.rb-grid3{grid-template-columns:1fr;}}
.rb-two{display:grid;grid-template-columns:1fr 1.3fr;gap:14px;margin-top:14px;}
.rb-twoeq{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
@media(max-width:760px){.rb-two,.rb-twoeq{grid-template-columns:1fr;}}
.rb-stat{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:20px;}
.rb-stat .lab{font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);}
.rb-stat .big{font-family:'Bricolage Grotesque';font-weight:800;font-size:46px;letter-spacing:-0.03em;line-height:1;margin:8px 0 4px;}
.rb-stat .note{font-size:12.5px;color:var(--muted);}
.rb-tm{font-size:10px;vertical-align:super;color:var(--muted);font-weight:600;margin-left:1px;}

.rb-panel{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:22px;}
.rb-panel h3{font-family:'Bricolage Grotesque';font-weight:700;font-size:16px;margin:0 0 16px;letter-spacing:-0.01em;}
.rb-row{display:flex;align-items:center;gap:12px;margin-bottom:11px;}
.rb-row .nm{font-size:13px;font-weight:500;min-width:160px;}
.rb-track{flex:1;height:9px;background:var(--cloud);border-radius:99px;overflow:hidden;}
.rb-track i{display:block;height:100%;border-radius:99px;transition:width .6s cubic-bezier(.2,.7,.3,1);}
.rb-row .pc{font-family:'Bricolage Grotesque';font-weight:700;font-size:13.5px;min-width:40px;text-align:right;font-variant-numeric:tabular-nums;}

.rb-exp{display:flex;align-items:center;justify-content:space-between;padding:11px 0;border-bottom:1px solid var(--line);}
.rb-exp:last-child{border-bottom:none;}
.rb-exp .nm{font-size:13.5px;font-weight:500;}
.rb-badge{font-size:11px;font-weight:700;letter-spacing:.04em;border-radius:6px;padding:4px 10px;text-transform:uppercase;}

.rb-rec{display:flex;gap:11px;padding:12px 0;border-bottom:1px solid var(--line);font-size:13.5px;color:var(--ink-2);}
.rb-rec:last-child{border-bottom:none;}
.rb-rec .b{width:7px;height:7px;border-radius:50%;margin-top:6px;flex:none;}

.rb-empty{background:var(--card);border:1px dashed var(--line);border-radius:14px;padding:46px 24px;text-align:center;color:var(--muted);}
.rb-empty h3{font-family:'Bricolage Grotesque';color:var(--ink);font-size:20px;margin:0 0 8px;}
.rb-link{background:none;border:none;color:var(--accent);font-weight:600;cursor:pointer;font-size:13px;font-family:inherit;text-decoration:underline;text-underline-offset:2px;}
.rb-foot{text-align:center;color:var(--muted);font-size:12px;padding:36px 0 48px;}
.rb-foot b{color:var(--ink-2);}
`;

/* ---------- option presets ---------- */
const FREQ = ["Never", "Rarely", "Sometimes", "Often", "Always"];
const fg = FREQ.map((l, i) => ({ label: l, v: i * 25 }));        // higher freq = better
const fr = FREQ.map((l, i) => ({ label: l, v: 100 - i * 25 }));  // higher freq = worse
const yg = [{ label: "Yes", v: 100 }, { label: "Not sure", v: 40 }, { label: "No", v: 0 }];
const yr = [{ label: "Yes", v: 0 }, { label: "Not sure", v: 30 }, { label: "No", v: 100 }];

const Q = (id, text, options, meta = {}) => ({ id, text, type: "options", options, ...meta });
const RATIO = (id, text, meta = {}) => ({ id, text, type: "ratio", ...meta });

/* domain colour map (8 DfE-aligned domains) */
const DOMAIN_COLOR = {
  "Safe Adoption": "#1B6B8C",
  "Human Oversight": "#2F8F76",
  "AI Dependency": "#D98432",
  "Privacy & Data Protection": "#7C5CBF",
  "Safeguarding": "#C24A3F",
  "Assessment Integrity": "#B07A1E",
  "AI Literacy": "#3A8FB0",
  "Governance": "#475569",
};

/* ---------- the four audits ---------- */
const AUDITS = {
  teacher: {
    label: "Teachers",
    blurb: "Oversight, dependency, pupil-data behaviour and AI literacy in your practice.",
    color: "#2F8F76", tint: "#E7F4EF",
    sections: [
      {
        name: "Human Oversight", domain: "Human Oversight", area: "Oversight",
        questions: [
          RATIO("t-ratio", "What share of AI-generated content do you change before you use it?",
            { area: "Oversight", domain: "Human Oversight", ovr: true }),
          Q("t-verify", "Do you verify AI outputs against a reliable source?", fg, { area: "Oversight", domain: "Human Oversight", ovr: true }),
          Q("t-cross", "Do you cross-reference references or facts the AI gives you?", fg, { area: "Oversight", domain: "Human Oversight", ovr: true }),
          Q("t-challenge", "Do you challenge or push back on AI recommendations?", fg, { area: "Oversight", domain: "Human Oversight", ovr: true }),
        ],
      },
      {
        name: "Dependency", domain: "AI Dependency", area: "Dependency",
        questions: [
          Q("t-before", "How often do you reach for AI before attempting a task yourself?", fr, { area: "Dependency", domain: "AI Dependency", dep: true }),
          Q("t-week", "Could you teach effectively for a week with no AI tools?", yg, { area: "Dependency", domain: "AI Dependency", dep: true }),
          Q("t-feedback", "How often do you use AI to write pupil feedback?", fr, { area: "Dependency", domain: "AI Dependency", dep: true }),
        ],
      },
      {
        name: "Privacy & Data Protection", domain: "Privacy & Data Protection", area: "Privacy",
        questions: [
          Q("t-pupil", "Have you entered identifiable pupil information into an AI tool?", yr, { area: "Privacy", domain: "Privacy & Data Protection" }),
          Q("t-send", "Have you entered SEND or safeguarding information into AI?", yr, { area: "Privacy", domain: "Privacy & Data Protection" }),
          Q("t-risk", "Do you understand the data-protection risks of AI tools?", yg, { area: "Privacy", domain: "Privacy & Data Protection" }),
        ],
      },
      {
        name: "Confidence & Competence", domain: "AI Literacy", area: "Literacy",
        questions: [
          Q("t-halluc", "Do you understand what AI \u201challucinations\u201d are?", yg, { area: "Literacy", domain: "AI Literacy" }),
          Q("t-limits", "Do you understand the limitations of AI tools?", fg, { area: "Literacy", domain: "AI Literacy" }),
          Q("t-when", "Do you know when AI should not be used in your teaching?", yg, { area: "Literacy", domain: "AI Literacy" }),
        ],
      },
    ],
  },

  student: {
    label: "Students",
    blurb: "Independence, verification habits, critical thinking and online safety.",
    color: "#1B6B8C", tint: "#E4EFF4",
    sections: [
      {
        name: "Dependency", domain: "AI Dependency", area: "Dependency",
        questions: [
          Q("s-attempt", "Do you attempt your work before using AI?", fg, { area: "Dependency", domain: "AI Dependency", dep: true }),
          Q("s-without", "Could you complete this assignment without any AI help?", yg, { area: "Dependency", domain: "AI Dependency", dep: true }),
          Q("s-passed", "Have you ever handed in AI-generated work as your own?", yr, { area: "Assessment", domain: "Assessment Integrity", dep: true }),
        ],
      },
      {
        name: "Verification", domain: "Human Oversight", area: "Oversight",
        questions: [
          Q("s-check", "Do you check whether AI answers are actually correct?", fg, { area: "Oversight", domain: "Human Oversight", ovr: true }),
          Q("s-compare", "Do you compare AI answers with your textbook or notes?", fg, { area: "Oversight", domain: "Human Oversight", ovr: true }),
          Q("s-spot", "Have you ever spotted a mistake the AI made?", [{ label: "Yes", v: 100 }, { label: "Not sure", v: 50 }, { label: "No", v: 20 }], { area: "Oversight", domain: "Human Oversight", ovr: true }),
        ],
      },
      {
        name: "Critical Thinking", domain: "AI Literacy", area: "Literacy",
        questions: [
          Q("s-how", "Do you understand how AI tools actually work?", fg, { area: "Literacy", domain: "AI Literacy" }),
          Q("s-notgood", "Do you know what AI is not good at?", fg, { area: "Literacy", domain: "AI Literacy" }),
          Q("s-wrong", "Can you tell when an AI answer is wrong?", fg, { area: "Literacy", domain: "AI Literacy" }),
        ],
      },
      {
        name: "Safety", domain: "Privacy & Data Protection", area: "Privacy",
        questions: [
          Q("s-shared", "Have you shared personal information with an AI tool?", yr, { area: "Privacy", domain: "Privacy & Data Protection" }),
          Q("s-privacy", "Do you understand the privacy risks of using AI?", yg, { area: "Privacy", domain: "Privacy & Data Protection" }),
        ],
      },
    ],
  },

  parent: {
    label: "Parents",
    blurb: "Awareness of your child's AI use, oversight at home, safety and confidence.",
    color: "#7C5CBF", tint: "#EDE8F7",
    sections: [
      {
        name: "Awareness", domain: "Safe Adoption", area: "Oversight",
        questions: [
          Q("p-uses", "Do you know whether your child uses AI for schoolwork?", yg, { area: "Oversight", domain: "Safe Adoption" }),
          Q("p-tools", "Do you know which AI tools your child uses?", fg, { area: "Oversight", domain: "Safe Adoption" }),
        ],
      },
      {
        name: "Oversight", domain: "Human Oversight", area: "Oversight",
        questions: [
          Q("p-talk", "Have you talked with your child about how they use AI?", yg, { area: "Oversight", domain: "Human Oversight", ovr: true }),
          Q("p-cheat", "Do you understand how AI can be used to cheat?", yg, { area: "Assessment", domain: "Assessment Integrity" }),
          Q("p-recog", "Could you recognise AI-generated work?", fg, { area: "Oversight", domain: "Human Oversight", ovr: true }),
        ],
      },
      {
        name: "Safety", domain: "Safeguarding", area: "Safeguarding",
        questions: [
          Q("p-share", "Do you know what your child should never share with AI?", yg, { area: "Privacy", domain: "Privacy & Data Protection" }),
          Q("p-deepfake", "Have you discussed deepfakes and fake images with your child?", yg, { area: "Safeguarding", domain: "Safeguarding" }),
        ],
      },
      {
        name: "Confidence", domain: "AI Literacy", area: "Literacy",
        questions: [
          Q("p-equip", "Do you feel equipped to guide your child's AI use?", fg, { area: "Literacy", domain: "AI Literacy" }),
        ],
      },
    ],
  },

  leader: {
    label: "School Leaders",
    blurb: "Governance, safeguarding, data protection, workforce and assessment maturity.",
    color: "#475569", tint: "#E8ECF0",
    sections: [
      {
        name: "Governance", domain: "Governance", area: "Governance",
        questions: [
          Q("l-policy", "Does the school have an AI policy?", [{ label: "Yes", v: 100 }, { label: "In progress", v: 50 }, { label: "No", v: 0 }], { area: "Governance", domain: "Governance" }),
          Q("l-lead", "Is there a named AI lead?", yg, { area: "Governance", domain: "Governance" }),
          Q("l-review", "Is AI use reviewed at least annually?", yg, { area: "Governance", domain: "Governance" }),
        ],
      },
      {
        name: "Safeguarding", domain: "Safeguarding", area: "Safeguarding",
        questions: [
          Q("l-sgrisk", "Are AI risks included in safeguarding procedures?", yg, { area: "Safeguarding", domain: "Safeguarding" }),
          Q("l-deepfake", "Are deepfakes and image-based abuse explicitly covered?", yg, { area: "Safeguarding", domain: "Safeguarding" }),
        ],
      },
      {
        name: "Data Protection", domain: "Privacy & Data Protection", area: "Privacy",
        questions: [
          Q("l-dpia", "Has a DPIA been completed for AI tools in use?", yg, { area: "Privacy", domain: "Privacy & Data Protection" }),
          Q("l-approved", "Is there an approved list of permitted AI tools?", yg, { area: "Privacy", domain: "Privacy & Data Protection" }),
        ],
      },
      {
        name: "Workforce Readiness", domain: "Safe Adoption", area: "Adoption",
        questions: [
          Q("l-train", "Have staff been trained on safe and effective AI use?", fg, { area: "Adoption", domain: "Safe Adoption" }),
          Q("l-incidents", "Are AI-related incidents logged and tracked?", yg, { area: "Adoption", domain: "Safe Adoption" }),
        ],
      },
      {
        name: "Assessment", domain: "Assessment Integrity", area: "Assessment",
        questions: [
          Q("l-assess", "Are assessments reviewed for AI exposure?", fg, { area: "Assessment", domain: "Assessment Integrity" }),
          Q("l-jcq", "Is JCQ / Ofqual guidance on AI understood and applied?", yg, { area: "Assessment", domain: "Assessment Integrity" }),
        ],
      },
    ],
  },
};

const AUD_KEYS = ["teacher", "student", "parent", "leader"];
const AREA_LABELS = ["Dependency", "Oversight", "Governance", "Privacy", "Safeguarding"];
const ALL_DOMAINS = ["Safe Adoption", "Human Oversight", "AI Dependency", "Privacy & Data Protection", "Safeguarding", "Assessment Integrity", "AI Literacy", "Governance"];

/* ---------- scoring ---------- */
function allQuestions(aud) {
  return AUDITS[aud].sections.flatMap((s) => s.questions);
}
function valueOf(q, ans) {
  if (q.type === "ratio") return typeof ans === "number" ? ans : null;
  const opt = q.options.find((o) => o.label === ans);
  return opt ? opt.v : null;
}
function mean(arr) { return arr.length ? arr.reduce((a, b) => a + b, 0) / arr.length : null; }

function scoreAudit(aud, answers) {
  const qs = allQuestions(aud);
  const vals = [];
  const byArea = {}, byDomain = {};
  const dep = [], ovr = [];
  let ratio = null;
  qs.forEach((q) => {
    const v = valueOf(q, answers[q.id]);
    if (v == null) return;
    vals.push(v);
    (byArea[q.area] = byArea[q.area] || []).push(v);
    (byDomain[q.domain] = byDomain[q.domain] || []).push(v);
    if (q.dep) dep.push(v);
    if (q.ovr) ovr.push(v);
    if (q.type === "ratio") ratio = v;
  });
  const readiness = Math.round(mean(vals) || 0);
  const areaGood = {}; Object.keys(byArea).forEach((k) => (areaGood[k] = Math.round(mean(byArea[k]))));
  const domainGood = {}; Object.keys(byDomain).forEach((k) => (domainGood[k] = Math.round(mean(byDomain[k]))));
  const oversightRatio = ratio != null ? ratio : (ovr.length ? Math.round(mean(ovr)) : null);
  return {
    audience: aud,
    readiness,
    risk: 100 - readiness,
    depIndex: dep.length ? Math.round(100 - mean(dep)) : null,
    oversightRatio,
    areaGood, domainGood,
    ts: Date.now(),
  };
}

/* dependency band for the Human Oversight Ratio */
function oversightBand(v) {
  if (v <= 10) return { label: "Critical reliance", color: "var(--critical)" };
  if (v <= 25) return { label: "High reliance", color: "var(--elevated)" };
  if (v <= 50) return { label: "Moderate oversight", color: "var(--moderate)" };
  return { label: "Strong human oversight", color: "var(--strong)" };
}
function readinessBand(v) {
  if (v >= 75) return { label: "Strong", color: "var(--strong)" };
  if (v >= 60) return { label: "Established", color: "#3A8FB0" };
  if (v >= 45) return { label: "Developing", color: "var(--moderate)" };
  return { label: "Emerging", color: "var(--critical)" };
}
function exposureBand(exposure) {
  if (exposure >= 55) return { label: "High", bg: "#f6e2df", fg: "#A83A30" };
  if (exposure >= 32) return { label: "Medium", bg: "#f7eed6", fg: "#8a6b14" };
  return { label: "Low", bg: "#e3f1ec", fg: "#1f6b56" };
}
function colorForGood(v) {
  if (v >= 75) return "var(--strong)";
  if (v >= 60) return "#3A8FB0";
  if (v >= 45) return "var(--moderate)";
  return "var(--critical)";
}

/* ---------- small SVG helpers ---------- */
function polar(cx, cy, r, deg) {
  const a = (deg - 90) * Math.PI / 180;
  return [cx + r * Math.cos(a), cy + r * Math.sin(a)];
}
function arcPath(cx, cy, r, startDeg, endDeg) {
  const [sx, sy] = polar(cx, cy, r, startDeg);
  const [ex, ey] = polar(cx, cy, r, endDeg);
  const large = endDeg - startDeg <= 180 ? 0 : 1;
  return `M ${sx} ${sy} A ${r} ${r} 0 ${large} 1 ${ex} ${ey}`;
}
const A0 = -120, A1 = 120;
const toAngle = (v) => A0 + (v / 100) * (A1 - A0);

function Gauge({ value, dark = false }) {
  if (value == null) value = 0;
  const cx = 120, cy = 120, r = 92;
  const zones = [
    { a: 0, b: 10, c: "#C24A3F" },
    { a: 10, b: 25, c: "#D98432" },
    { a: 25, b: 50, c: "#C2A02A" },
    { a: 50, b: 100, c: "#2F8F76" },
  ];
  const band = oversightBand(value);
  const needleAngle = toAngle(Math.max(0, Math.min(100, value)));
  const [nx, ny] = polar(cx, cy, r - 14, needleAngle);
  const tick = dark ? "#3a4b57" : "#cdd6d8";
  return (
    <svg viewBox="0 0 240 200" width="100%" style={{ maxWidth: 320, display: "block", margin: "4px auto 0" }}>
      <path d={arcPath(cx, cy, r, A0, A1)} fill="none" stroke={tick} strokeWidth="16" strokeLinecap="round" />
      {zones.map((z, i) => (
        <path key={i} d={arcPath(cx, cy, r, toAngle(z.a), toAngle(z.b))} fill="none" stroke={z.c} strokeWidth="16" strokeLinecap={i === 0 || i === zones.length - 1 ? "round" : "butt"} opacity="0.92" />
      ))}
      <line x1={cx} y1={cy} x2={nx} y2={ny} stroke={dark ? "#fff" : "#0F1822"} strokeWidth="3.5" strokeLinecap="round" />
      <circle cx={cx} cy={cy} r="7" fill={dark ? "#fff" : "#0F1822"} />
      <text x={cx} y={cy - 20} textAnchor="middle" className="rb-num" fontSize="46" fontWeight="800" fill={dark ? "#fff" : "#0F1822"}>{Math.round(value)}<tspan fontSize="20">%</tspan></text>
      <text x={cx} y={cy + 32} textAnchor="middle" fontSize="12.5" fontWeight="700" fill={band.color} fontFamily="Inter" style={{ letterSpacing: ".02em" }}>{band.label.toUpperCase()}</text>
    </svg>
  );
}

/* ---------- icons (inline) ---------- */
const Icon = ({ d, size = 20, color = "currentColor" }) => (
  <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke={color} strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">{d}</svg>
);
const ICONS = {
  teacher: <><path d="M22 10 12 5 2 10l10 5 10-5Z" /><path d="M6 12v5c3 2 9 2 12 0v-5" /></>,
  student: <><path d="M12 14 4 9l8-5 8 5-8 5Z" /><path d="M12 14v6" /><path d="M8 11v4l4 2 4-2v-4" /></>,
  parent: <><circle cx="9" cy="8" r="3" /><path d="M3 20c0-3 3-5 6-5s6 2 6 5" /><circle cx="17" cy="9" r="2" /><path d="M21 20c0-2-1.5-3.5-4-3.5" /></>,
  leader: <><path d="M3 21h18" /><path d="M5 21V8l7-4 7 4v13" /><path d="M9 21v-6h6v6" /></>,
  arrow: <path d="M5 12h14M13 6l6 6-6 6" />,
  back: <path d="M19 12H5M11 18l-6-6 6-6" />,
};

/* ============================================================ */
export default function App() {
  const [view, setView] = useState("home");
  const [aud, setAud] = useState(null);
  const [sectionIdx, setSectionIdx] = useState(0);
  const [answers, setAnswers] = useState({});
  const [results, setResults] = useState({});   // {teacher:{...}, ...}
  const [lastResult, setLastResult] = useState(null);
  const [loading, setLoading] = useState(true);

  /* load saved results */
  useEffect(() => {
    (async () => {
      try {
        const out = {};
        const list = await window.storage.list("result:");
        const keys = (list && list.keys) || [];
        for (const k of keys) {
          try {
            const r = await window.storage.get(k);
            if (r && r.value) out[k.replace("result:", "")] = JSON.parse(r.value);
          } catch (e) { /* skip */ }
        }
        setResults(out);
      } catch (e) { /* storage unavailable; run in-memory */ }
      setLoading(false);
    })();
  }, []);

  function startAudit(key) {
    setAud(key); setSectionIdx(0); setAnswers({}); setView("audit");
    window.scrollTo(0, 0);
  }
  function pick(qid, val) { setAnswers((a) => ({ ...a, [qid]: val })); }

  const sections = aud ? AUDITS[aud].sections : [];
  const section = sections[sectionIdx];
  const sectionComplete = section ? section.questions.every((q) =>
    q.type === "ratio" ? typeof answers[q.id] === "number" : answers[q.id] != null) : false;
  const totalSections = sections.length;

  async function next() {
    if (sectionIdx < totalSections - 1) {
      setSectionIdx((i) => i + 1); window.scrollTo(0, 0); return;
    }
    const res = scoreAudit(aud, answers);
    setLastResult(res);
    setResults((r) => ({ ...r, [aud]: res }));
    try { await window.storage.set(`result:${aud}`, JSON.stringify(res)); } catch (e) {}
    setView("results"); window.scrollTo(0, 0);
  }
  function back() {
    if (sectionIdx > 0) { setSectionIdx((i) => i - 1); window.scrollTo(0, 0); }
    else { setView("home"); }
  }

  async function resetAll() {
    try {
      const list = await window.storage.list("result:");
      for (const k of (list && list.keys) || []) { try { await window.storage.delete(k); } catch (e) {} }
    } catch (e) {}
    setResults({});
  }

  const completedCount = Object.keys(results).length;

  /* ---------- dashboard aggregation ---------- */
  const dash = useMemo(() => {
    const keys = Object.keys(results);
    if (!keys.length) return null;
    const W = { teacher: 1.2, student: 1, parent: 0.9, leader: 1.4 };
    let wSum = 0, rSum = 0;
    keys.forEach((k) => { wSum += W[k]; rSum += results[k].readiness * W[k]; });
    const dfe = Math.round(rSum / wSum);
    // exposure per risk area
    const exposure = AREA_LABELS.map((area) => {
      const goods = keys.map((k) => results[k].areaGood[area]).filter((v) => v != null);
      if (!goods.length) return { area, exposure: null };
      return { area, exposure: Math.round(100 - mean(goods)) };
    });
    // domain readiness
    const domains = ALL_DOMAINS.map((d) => {
      const goods = keys.map((k) => results[k].domainGood[d]).filter((v) => v != null);
      return { domain: d, value: goods.length ? Math.round(mean(goods)) : null };
    }).filter((d) => d.value != null);
    return { dfe, exposure, domains };
  }, [results]);

  /* ============================== RENDER ============================== */
  return (
    <div className="rb-root">
      <style>{CSS}</style>

      {/* top bar */}
      <div className="rb-bar">
        <div className="rb-bar-in">
          <div className="rb-logo" onClick={() => setView("home")}>
            <svg className="rb-mark" viewBox="0 0 32 32" fill="none">
              <circle cx="16" cy="16" r="15" stroke="#3a8fb0" strokeWidth="1.5" opacity=".5" />
              <path d={arcPath(16, 16, 10, -120, 120)} stroke="#9fd9c9" strokeWidth="2.5" fill="none" strokeLinecap="round" />
              <line x1="16" y1="16" x2={polar(16, 16, 8, 35)[0]} y2={polar(16, 16, 8, 35)[1]} stroke="#fff" strokeWidth="2" strokeLinecap="round" />
              <circle cx="16" cy="16" r="2" fill="#fff" />
            </svg>
            <div>
              <b>AI Risk &amp; Readiness Benchmark</b>
              <span>DfE-aligned · Schools</span>
            </div>
          </div>
          <button className="rb-ghost" onClick={() => { setView("dashboard"); window.scrollTo(0, 0); }}>
            School dashboard
            <span className="rb-pill">{completedCount}</span>
          </button>
        </div>
      </div>

      {/* ---------------- HOME ---------------- */}
      {view === "home" && (
        <>
          <section className="rb-hero">
            <div className="rb-wrap rb-herogrid">
              <div>
                <span className="rb-eyebrow"><span className="dot" />The UK's first behavioural AI benchmark for schools</span>
                <h1 className="rb-h1">Measure how <em>exposed</em> your school is to AI risk.</h1>
                <p className="rb-sub">Most audits ask <i>"is the school ready to adopt AI?"</i> This one asks a harder question: how dependent are your people becoming, and how much human oversight remains? Four audiences, one DfE-aligned scorecard.</p>
                <div className="rb-srcs">
                  {["DfE", "KCSIE", "ICO", "JCQ", "Ofqual", "Ofsted"].map((s) => <span key={s} className="rb-src">{s}</span>)}
                </div>
              </div>
              <div className="rb-gaugecard">
                <div className="tag">Signature metric</div>
                <h3>Human Oversight Ratio™</h3>
                <p>Share of AI output a person changes before using it.</p>
                <Gauge value={34} dark />
                <p style={{ marginTop: 6, fontSize: "11.5px", color: "#8aa0ac" }}>Below 26% signals reliance without meaningful human review.</p>
              </div>
            </div>
          </section>

          <section className="rb-wrap" style={{ paddingBottom: 30 }}>
            <div className="rb-shead">
              <h2>Choose your audit</h2>
              <span className="num">04 TAILORED AUDITS</span>
            </div>
            <div className="rb-cards">
              {AUD_KEYS.map((k) => {
                const a = AUDITS[k];
                return (
                  <button key={k} className="rb-card" onClick={() => startAudit(k)}>
                    {results[k] && <span className="done">DONE · {results[k].readiness}%</span>}
                    <span className="ic" style={{ background: a.tint, color: a.color }}><Icon d={ICONS[k]} /></span>
                    <h4>{a.label}</h4>
                    <p>{a.blurb}</p>
                    <span className="go">{results[k] ? "Retake audit" : "Start audit"} <Icon d={ICONS.arrow} size={15} /></span>
                  </button>
                );
              })}
            </div>
          </section>

          <section className="rb-wrap" style={{ paddingBottom: 16 }}>
            <div className="rb-panel">
              <h3>Scored against 8 DfE-aligned domains</h3>
              <div style={{ display: "flex", flexWrap: "wrap", gap: 10 }}>
                {ALL_DOMAINS.map((d) => (
                  <div key={d} style={{ display: "flex", alignItems: "center", gap: 8, fontSize: 13, fontWeight: 500, border: "1px solid var(--line)", borderRadius: 8, padding: "8px 12px" }}>
                    <span style={{ width: 9, height: 9, borderRadius: 2, background: DOMAIN_COLOR[d] }} />{d}
                  </div>
                ))}
              </div>
            </div>
          </section>
          <div className="rb-foot rb-wrap">
            Prototype concept · scores are illustrative and not a substitute for a formal DPIA or safeguarding review.<br />
            Complete all four audits to unlock the <b>School-Wide Dashboard</b> and <b>DfE AI Alignment Score</b>.
          </div>
        </>
      )}

      {/* ---------------- AUDIT ---------------- */}
      {view === "audit" && section && (
        <section className="rb-wrap" style={{ paddingTop: 30, paddingBottom: 40 }}>
          <button className="rb-link" onClick={() => setView("home")} style={{ marginBottom: 14 }}>← All audits</button>
          <div className="rb-shead" style={{ marginBottom: 12 }}>
            <h2 style={{ color: AUDITS[aud].color }}>{AUDITS[aud].label} audit</h2>
            <span className="num">SECTION {String(sectionIdx + 1).padStart(2, "0")} / {String(totalSections).padStart(2, "0")}</span>
          </div>
          <div className="rb-prog"><i style={{ width: `${((sectionIdx) / totalSections) * 100}%` }} /></div>

          <div className="rb-domtag" style={{ marginBottom: 16 }}>
            <span className="sq" style={{ background: DOMAIN_COLOR[section.domain] }} />
            {section.name} · {section.domain}
          </div>

          {section.questions.map((q) => (
            <div key={q.id} className="rb-q">
              <p className="qt">{q.text}</p>
              {q.type === "ratio" ? (
                <>
                  <div className="rb-slider">
                    <input type="range" min="0" max="100" step="1"
                      value={typeof answers[q.id] === "number" ? answers[q.id] : 0}
                      onChange={(e) => pick(q.id, parseInt(e.target.value, 10))} />
                    <span className="rb-slval" style={{ color: typeof answers[q.id] === "number" ? oversightBand(answers[q.id]).color : "var(--muted)" }}>
                      {typeof answers[q.id] === "number" ? answers[q.id] : 0}%
                    </span>
                  </div>
                  {typeof answers[q.id] === "number" && (
                    <div className="rb-sllabel" style={{ color: oversightBand(answers[q.id]).color }}>
                      {oversightBand(answers[q.id]).label}
                    </div>
                  )}
                </>
              ) : (
                <div className="rb-opts">
                  {q.options.map((o) => (
                    <button key={o.label} className={"rb-opt" + (answers[q.id] === o.label ? " on" : "")}
                      onClick={() => pick(q.id, o.label)}>{o.label}</button>
                  ))}
                </div>
              )}
            </div>
          ))}

          <div className="rb-navrow">
            <button className="rb-btn back" onClick={back}><Icon d={ICONS.back} size={16} /> Back</button>
            <button className="rb-btn" disabled={!sectionComplete} onClick={next}>
              {sectionIdx < totalSections - 1 ? "Next section" : "See results"} <Icon d={ICONS.arrow} size={16} color="#fff" />
            </button>
          </div>
        </section>
      )}

      {/* ---------------- RESULTS ---------------- */}
      {view === "results" && lastResult && (
        <section className="rb-wrap" style={{ paddingTop: 30, paddingBottom: 30 }}>
          <span className="rb-eyebrow"><span className="dot" />{AUDITS[lastResult.audience].label} result</span>
          <div className="rb-shead" style={{ marginTop: 12, marginBottom: 18 }}>
            <h2>Your AI Risk &amp; Readiness profile</h2>
            <span className="num" style={{ color: readinessBand(lastResult.readiness).color }}>{readinessBand(lastResult.readiness).label.toUpperCase()}</span>
          </div>

          <div className="rb-grid3">
            <div className="rb-stat">
              <div className="lab">Readiness score</div>
              <div className="big" style={{ color: readinessBand(lastResult.readiness).color }}>{lastResult.readiness}%</div>
              <div className="note">Weighted across every domain in this audit.</div>
            </div>
            <div className="rb-stat">
              <div className="lab">AI risk score</div>
              <div className="big" style={{ color: lastResult.risk >= 55 ? "var(--critical)" : lastResult.risk >= 40 ? "var(--moderate)" : "var(--strong)" }}>{lastResult.risk}%</div>
              <div className="note">Behavioural exposure — the inverse of readiness.</div>
            </div>
            <div className="rb-stat">
              <div className="lab">AI Dependency Index<span className="rb-tm">™</span></div>
              <div className="big" style={{ color: lastResult.depIndex == null ? "var(--muted)" : lastResult.depIndex >= 60 ? "var(--critical)" : lastResult.depIndex >= 35 ? "var(--moderate)" : "var(--strong)" }}>
                {lastResult.depIndex == null ? "—" : lastResult.depIndex}
              </div>
              <div className="note">{lastResult.depIndex == null ? "Not measured for this audience." : "Higher means greater reliance on AI."}</div>
            </div>
          </div>

          <div className="rb-two">
            <div className="rb-panel" style={{ textAlign: "center" }}>
              <h3 style={{ textAlign: "left" }}>Human Oversight Ratio<span className="rb-tm">™</span></h3>
              {lastResult.oversightRatio != null
                ? <Gauge value={lastResult.oversightRatio} />
                : <p style={{ color: "var(--muted)", fontSize: 13 }}>Not measured for this audience.</p>}
            </div>
            <div className="rb-panel">
              <h3>Domain breakdown</h3>
              {Object.entries(lastResult.domainGood).map(([d, v]) => (
                <div className="rb-row" key={d}>
                  <span className="nm">{d}</span>
                  <span className="rb-track"><i style={{ width: `${v}%`, background: colorForGood(v) }} /></span>
                  <span className="pc">{v}%</span>
                </div>
              ))}
            </div>
          </div>

          <div className="rb-panel" style={{ marginTop: 14 }}>
            <h3>What to focus on</h3>
            {Object.entries(lastResult.domainGood).sort((a, b) => a[1] - b[1]).slice(0, 3).map(([d, v]) => (
              <div className="rb-rec" key={d}>
                <span className="b" style={{ background: colorForGood(v) }} />
                <span><b style={{ color: "var(--ink)" }}>{d} — {v}%.</b> {RECS[d] || "Strengthen practice and review against published guidance."}</span>
              </div>
            ))}
          </div>

          <div className="rb-navrow" style={{ marginTop: 18 }}>
            <button className="rb-btn back" onClick={() => { setView("home"); window.scrollTo(0, 0); }}>Back to audits</button>
            <button className="rb-btn" onClick={() => { setView("dashboard"); window.scrollTo(0, 0); }}>View school dashboard <Icon d={ICONS.arrow} size={16} color="#fff" /></button>
          </div>
        </section>
      )}

      {/* ---------------- DASHBOARD ---------------- */}
      {view === "dashboard" && (
        <section className="rb-wrap" style={{ paddingTop: 30, paddingBottom: 30 }}>
          <span className="rb-eyebrow"><span className="dot" />School-wide dashboard</span>
          <div className="rb-shead" style={{ marginTop: 12, marginBottom: 18 }}>
            <h2>Whole-school AI risk profile</h2>
            {completedCount > 0 && <button className="rb-link" onClick={resetAll}>Reset all data</button>}
          </div>

          {loading && <div className="rb-empty">Loading saved audits…</div>}

          {!loading && !dash && (
            <div className="rb-empty">
              <h3>No audits completed yet</h3>
              <p>Complete at least one audit to build the dashboard. Finish all four — Teachers, Students, Parents and Leaders — to unlock the full DfE AI Alignment Score.</p>
              <button className="rb-link" onClick={() => { setView("home"); window.scrollTo(0, 0); }} style={{ marginTop: 8 }}>Choose an audit →</button>
            </div>
          )}

          {!loading && dash && (
            <>
              <div className="rb-grid3" style={{ marginBottom: 14 }}>
                <div className="rb-stat" style={{ gridColumn: "span 1" }}>
                  <div className="lab">DfE AI Alignment Score<span className="rb-tm"></span></div>
                  <div className="big" style={{ color: readinessBand(dash.dfe).color }}>{dash.dfe}%</div>
                  <div className="note">Weighted benchmark against published guidance ({completedCount}/4 audiences in).</div>
                </div>
                <div className="rb-stat" style={{ gridColumn: "span 2" }}>
                  <div className="lab">Audience scores</div>
                  <div style={{ marginTop: 12 }}>
                    {AUD_KEYS.filter((k) => results[k]).map((k) => (
                      <div className="rb-row" key={k}>
                        <span className="nm" style={{ minWidth: 110 }}>{AUDITS[k].label}</span>
                        <span className="rb-track"><i style={{ width: `${results[k].readiness}%`, background: colorForGood(results[k].readiness) }} /></span>
                        <span className="pc">{results[k].readiness}%</span>
                      </div>
                    ))}
                    {AUD_KEYS.filter((k) => !results[k]).map((k) => (
                      <div className="rb-row" key={k} style={{ opacity: .5 }}>
                        <span className="nm" style={{ minWidth: 110 }}>{AUDITS[k].label}</span>
                        <span className="rb-track" />
                        <span className="pc" style={{ fontSize: 11, color: "var(--muted)" }}>—</span>
                      </div>
                    ))}
                  </div>
                </div>
              </div>

              <div className="rb-twoeq">
                <div className="rb-panel">
                  <h3>Exposure breakdown</h3>
                  {dash.exposure.map((e) => {
                    if (e.exposure == null) return (
                      <div className="rb-exp" key={e.area}><span className="nm" style={{ opacity: .5 }}>{e.area}</span><span style={{ fontSize: 12, color: "var(--muted)" }}>no data</span></div>
                    );
                    const b = exposureBand(e.exposure);
                    return (
                      <div className="rb-exp" key={e.area}>
                        <span className="nm">{e.area}</span>
                        <span style={{ display: "flex", alignItems: "center", gap: 10 }}>
                          <span style={{ fontSize: 12, color: "var(--muted)", fontVariantNumeric: "tabular-nums" }}>{e.exposure}%</span>
                          <span className="rb-badge" style={{ background: b.bg, color: b.fg }}>{b.label}</span>
                        </span>
                      </div>
                    );
                  })}
                </div>
                <div className="rb-panel">
                  <h3>Domain readiness</h3>
                  {dash.domains.map((d) => (
                    <div className="rb-row" key={d.domain}>
                      <span className="nm" style={{ minWidth: 150, fontSize: 12.5 }}>{d.domain}</span>
                      <span className="rb-track"><i style={{ width: `${d.value}%`, background: colorForGood(d.value) }} /></span>
                      <span className="pc">{d.value}%</span>
                    </div>
                  ))}
                </div>
              </div>

              <div className="rb-foot">
                Evidence responsible AI adoption against <b>DfE, KCSIE, ICO, JCQ, Ofqual and Ofsted</b> expectations — re-run annually to track change.
              </div>
            </>
          )}
        </section>
      )}
    </div>
  );
}

/* short remediation notes keyed by domain */
const RECS = {
  "Human Oversight": "Build a habit of editing, verifying and challenging AI output before it is used.",
  "AI Dependency": "Practise core tasks without AI first; use it to refine, not to originate.",
  "Privacy & Data Protection": "Never enter identifiable pupil, SEND or safeguarding data into public AI tools.",
  "AI Literacy": "Cover hallucinations, limitations and clear no-go situations in training.",
  "Safeguarding": "Add AI and deepfake risks explicitly to safeguarding procedures.",
  "Assessment Integrity": "Review assessment design against JCQ and Ofqual AI guidance.",
  "Governance": "Publish an AI policy, name a lead, and schedule an annual review.",
  "Safe Adoption": "Maintain an approved tool list and structured staff training.",
};
