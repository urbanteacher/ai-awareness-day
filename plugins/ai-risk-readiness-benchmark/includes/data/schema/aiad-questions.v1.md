# AiAd question bank schema, v1

One format for every AiAd question bank, read by both the WordPress plugin
(`ai-risk-readiness-benchmark`) and the Expo app (`Teacher AI Standard`).
Question text, scoring, branching and role variants are **data**, not code, so
a bank can be edited without touching either codebase.

Canonical copy lives here; the plugin keeps a synced copy at
`includes/data/schema/aiad-questions.v1.md`.

```jsonc
{
  "schema": "aiad.questions/1",
  "bank": "teacher-competency",      // stable bank id
  "title": "Teacher AI Competency",
  "locale": "en-GB",
  "roles": ["teacher"],              // roles this bank serves
  "passMark": 70,

  "scenarios": [                     // optional; a vignette several questions hang off
    {
      "id": "sc_planning",
      "domain": "Human-centred Practice",
      "title": "A planning and feedback trial",
      "stem": "You teach a Year 9 class…",
      "questionIds": ["c_human_adapt", "c_human_relationships", "c_human_accountability"]
    }
  ],

  "questions": [
    {
      "id": "c_human_adapt",
      "domain": "Human-centred Practice",
      "section": "Applied judgement",   // optional grouping label
      "type": "single",                 // single | slider | select
      "text": "An AI lesson plan is polished but does not suit your class…",
      "hint": "…",                      // optional, shown on request
      "explanation": "…",               // optional, shown after answering in practice mode
      "scenario": "sc_planning",        // optional back-reference
      "options": [
        { "value": "redesign", "label": "Redesign the plan around pupil needs", "score": 0 }
      ],
      "visible": { /* see below */ }    // optional
    }
  ]
}
```

## Scoring

`score` is a **risk** weight: 0 is the strongest answer, higher is weaker.
Both products convert to a 0–100 readiness figure the same way, so a bank
scores identically wherever it is read.

## Branching

Declarative, not an expression language — a string like `"{q} != 'rarely'"`
would need a parser and an evaluator in both PHP and TypeScript, and a way to
keep them in step. A small condition object evaluates safely in either, with
no `eval` and no parser:

```jsonc
"visible": {
  "allOf": [
    { "question": "pub_use_frequency", "notIn": ["rarely"] },
    { "profile": "schoolPhase", "in": ["secondary", "college", "university"] }
  ]
}
```

Supported: `allOf`, `anyOf`, `not`; leaf tests `in` / `notIn` / `answered`
against either a `question` id or a `profile` field. Absent condition means
always visible. An unanswered dependency makes a leaf test pass, so a question
is shown until its dependency rules it out.

This replaces the plugin's `show_unless_answer` (a `notIn` leaf) and
`show_for_phases` (a `profile` leaf). Those were hand-rolled per mechanism,
and the visibility logic lived in two places, which is how the index desync
bug in `goNext` was possible.
