# The chapter standard

**The benchmark is Part 1, Chapter 0: "One question, late at night"** (`sand-to-sentence/index.html`, `#ch0`).
It's the best example of the principles below. Every chapter in the three courses (From Sand to Sentence, From Answer to Action, From Agent to Team) should follow the **principles**. A chapter doesn't need to copy Chapter 0's parts box for box.

Agreed 22 November 2026. Revised the same day after the audit: principles over parts.

## The principles

### 1. Teach before you test
Nothing is asked (quick check, Nodes item, tile question) unless the chapter has taught it first. If a diagram or later stage needs a term the chapter doesn't own, give a plain one-line outline and point to the chapter that teaches it. The exception is a prediction, which can be an honest guess, as long as it's worded as one.

### 2. Every term the chapter awards is clearly defined
Each element has an "X is a Y: definition" sentence somewhere in Explain, in wording that matches `data/literacy-table/elements.json`. The story's italics can carry the definition, but a defined term must never be only *named*. Bold in "What's really happening" is preferred, because it adds the periodic table link automatically.

### 3. One main illustration for the main idea
Every chapter shows its main idea, not just tells it. That can be an animated flow with a prediction, or a strong interactive (the token splitter, the meaning map, the temperature slider). Add a second only when it shows something the first doesn't. Calculators, code samples and extra depth go in Go further.

### 4. Check easy, then harder
A quick check on what Explain taught, then a harder activity where it adds thinking: a Nodes challenge (sort, order or judge), a sorting quiz, or a debate. Short chapters can stop at the quick check, because the tile questions also check every term.

### 5. No writing
Students tap, sort, order and choose. Where an activity needs an answer, use "think of your answer first", then reveal. The only text box is Chapter 1's token splitter, which is a tool, not an answer.

### 6. Accurate, and looks right
Facts are checked against sources, and numbers are worked out (for example blue vs red scattering: about 2½× under 1/λ², nearly 6× under 1/λ⁴). Every diagram fits its frame, with no overlapping boxes or hidden labels, at desktop and phone widths. Puzzle cards carry no order clues.

## The shape every chapter shares
The engines build most of this automatically:
1. **Explain:** story, then "What's really happening", then the Summary box (`shared/earn.js`).
2. **See it:** the main illustration; Predict first sits above a flow.
3. **Check:** quick check, then the harder activity.
4. **Sum it up:** think, then reveal. The chapter's own question ("tell a friend…") is answered in the head or aloud, then checked against a "Did yours include…" list (`data-sum`, `data-sum-points`). The key points aren't simply shown again. Then the tiles are earned, which opens the next chapter (soft gate: "Open it anyway", "Teacher? Unlock every chapter"). The last chapter of a part also ends with a "Sum up Part N" recall across every chapter, before the final quiz.

## What Chapter 0 shows, as a worked example
- Two terms, each defined in bold, each with an illustration right beside it (prompt A vs B, Predict the next token).
- A plain outline of the stages (tokens, embeddings, attention) before the diagram that uses them.
- A prediction worded as a guess, above one glossed flow.
- A quick check, then *Keep them apart: looked up or written?*, both testing only what the chapter taught.
- Key points that state exactly what the chapter teaches.

## Audit, 22 November 2026
All 23 chapters follow the shape, have no writing boxes and have diagrams that fit. Judged against the principles rather than Chapter 0's parts, the one real gap was **Part 1 Chapter 7**: the transformer was only named, and attention, the hardest idea in the course, had no illustration. It now has bold definitions of attention and the transformer, and a predict-then-flow, "Attention: which words matter to 'blue'?".
