# Course illustrations → AI Literacy Periodic Table: audit

24 November 2026. Every illustration in the three AI Under the Hood courses (assets/courses/), checked against the
table's 142 words, to see which could appear on a word's panel or board alongside its animation.

**Already in the table:** 20 of the 23 animated flows, under "See how it works" / "How it works"
(data/literacy-table/flows.json, built by scripts/build-literacy-flows.py).

**This audit:** the other 50 illustrations: 46 still diagrams, 2 side-by-side animations ("Play both") and 2 custom
animations.

## Summary

| | Count | What to do |
|---|---|---|
| Match a table word well | 40 | Add, as the word's extra pictures ("From the course") |
| Chip-making chemistry and physics (Part 1, chapters 5–6) | 8 | Keep in the course, or group under GPU as "How chips are made" |
| Repeat an animation the table already plays | 2 | Leave out |

The 40 matches cover 34 words. Six words would get two pictures (Prompt, MCP, Workflow Orchestration,
Human-in-the-Loop, Agentic AI, AI Sovereignty).

## Part 1: From Sand to Sentence

| Ch | Illustration (its caption, shortened) | Word | Verdict |
|---|---|---|---|
| 0 | Same instruction, different context, different answer | Pr Prompt | Add |
| 0 | The model scores possible next tokens, picks one, predicts again | Ll Large Language Model | Add |
| 0 | The whole journey of one question (custom animation) | — | Repeat of the "journey" flow |
| 1 | The model never sees letters or words, only token numbers | Tk Token | Add |
| 1 | Question, chat and answer share one fixed space | Cw Context Window | Add |
| 2 | A token number is a label; an embedding is a pattern of numbers | Em Embedding | Add |
| 2 | A made-up flat slice of meaning-space | Ls Latent Space | Add |
| 3 | Model serving groups questions into batches | Ms Model Serving | Add |
| 3 | Cloud AI: the model runs in a data centre | Cd Cloud AI | Add |
| 4 | A matrix multiplication is many small sums for GPU cores | Gp GPU | Add |
| 4 | 4 bits allow only 16 values: every number is rounded | Qz Quantisation | Add |
| 5 | Doping silicon with boron | — | Chip-making |
| 5 | A voltage on the gate opens the transistor | — | Chip-making |
| 6 | Shorter light prints finer lines | — | Chip-making |
| 6 | Each tin droplet is 25 micrometres across | — | Chip-making |
| 6 | Hundreds of companies supply the chip makers | So AI Sovereignty | Add |
| 6 | TSMC's 3 nm measurements | — | Chip-making |
| 6 | EUV uses mirrors, not lenses | — | Chip-making |
| 6 | Every pulse hits a droplet, in a heartbeat | — | Chip-making |
| 7 | Each token scores every earlier token (attention) | Tf Transformer | Add |
| 7 | Each new token reads the rest from the store (KV cache) | If Inference | Add |
| 8 | Training adjusts the parameters after every guess | Tr Training | Add |
| 8 | DeepSeek-V3 mixes ideas from US and Chinese labs | So AI Sovereignty | Add |
| 8 | Open weights let anyone build on a model | Ow Open-Weight Model | Add |
| 8 | Distillation trains a small model on a big model's answers | Ds Distillation | Add |
| 9 | Fluent and confident isn't the same as correct | Ha Hallucination | Add |
| 9 | Cloud, edge and small models compared | Ed Edge AI (also Sm) | Add |
| 9 | Side by side: checking the answer, or not | Hi Human-in-the-Loop | Add |

## Part 2: From Answer to Action

| Ch | Illustration | Word | Verdict |
|---|---|---|---|
| 0 | On its own a model can only guess; RAG, tools and agents fix that | Cb Chatbot | Add |
| 0 | The app wraps your words in hidden instructions | Pe Prompt Engineering | Add |
| 0 | Every fix works by adding to the prompt | Ce Context Engineering | Add |
| 1 | Chunks stored as embeddings; the closest are found | Vd Vector Database | Add |
| 1 | An open-book exam: the model reads the pages it was given | Rg RAG | Add |
| 2 | Function calling: the model fills in a form | Fn Function Calling | Add |
| 2 | An API is a fixed list of things one program lets another do | Ap API Integration | Add |
| 2 | One tool call, start to finish (custom animation) | — | Repeat of the "tool" flow |
| 3 | One charger that fits every phone | Mc MCP | Add |
| 3 | Resources, tools and prompts in MCP | Mc MCP | Add |
| 4 | Each step of the agent loop uses a different piece | Lp Agent Loop | Add |
| 4 | Short-term and long-term memory | Me Memory | Add |
| 5 | Prompt injection: everything an agent reads ends up in its prompt | Jb Jailbreaking | Add (nearest word: the table has no "prompt injection") |
| 5 | Set the autonomy dial by what one mistake would cost | Au Autonomy | Add |
| 6 | Most "agentic AI" products are this stack | Ac Agentic AI | Add |

## Part 3: From Agent to Team

| Ch | Illustration | Word | Verdict |
|---|---|---|---|
| 0 | You already use these patterns with people | Ma Multi-Agent System | Add |
| 1 | Three workflows: people fix the steps in advance | Wo Workflow Orchestration | Add |
| 1 | Side by side: a workflow and an agent | Wo Workflow Orchestration | Add |
| 2 | Reflection, voting and an evaluator: what each checks against | Ev Evals | Add |
| 3 | Who decides the jobs: people, the code, or a lead agent | Or Orchestrator | Add |
| 4 | Catching mistakes: how many slip through | Hi Human-in-the-Loop | Add |
| 5 | Start with the simplest design that works | Ac Agentic AI | Add |

## The three flows not yet used

- From sand to chip, How light prints a chip (Part 1): chip-making, no matching word. They could join the 8
  chip-making stills under GPU as "How chips are made".
- Build up the design (Part 3): fits Agentic AI as a second animation.

## How it would work

- The build script would copy each matched illustration (its SVG and caption) into the table's data, like the flows,
  so the courses stay the one source.
- The word's pop-out would show its animation first, then "More from the course": the stills, with their captions and
  the chapter link.
- Words with a still but no animation (Tk, Cw, Em, Ls, Qz, Tr, Ow, Ds, Pr, Pe, Ce, Vd, Jb, Ed, Ev) would gain a
  "See how it works" button for the first time: about 15 more words.
- To check before building: the stills are styled by each course page's CSS, so their styles need copying too, and
  each must still read clearly outside its chapter (some refer to "the calculator" or "chapter 4").

## Built (24 November 2026)

- 36 still illustrations and the 2 side-by-side animations are in the table, under How it works, after the word's
  animation. scripts/build-literacy-stills.mjs copies the drawings (styles baked in) into
  plugins/aiad-core/assets/data/literacy-table-stills.json, loaded on first use, with a small index in
  data/literacy-table/stills.json; the comparisons are in flows.json.
- Left out after checking them on their own: "The prompt fills up, chapter by chapter" (Context Engineering) and
  "Group-project habits are design patterns" (Multi-Agent System), whose drawings label the course's chapters.
- Captions reworded where they referred to the course itself: Prompt Engineering, Planning (its illustration, mapped to
  Pl rather than Lp) and Human-in-the-Loop ("the calculator").
- 43 words now have How it works (was 33).

## The table's own illustrations (24 November 2026)

- Every one of the 142 words now has How it works. Words the courses don't illustrate got a drawing of their own,
  made by scripts/draw-literacy-illustrations.py in the courses' style. There are 99 of them, done one family at a
  time: Governance, Predictive & Analytical, Generative, Automation, Foundations, then Agents and Infrastructure.
- Each drawing shows how the idea works, not just what it is. Claims and figures were checked online; the sources
  are listed at the top of the drawing script. Numbers marked "made-up" are illustrative.
- Review sheets for each family are in docs/ (governance-batch-*.png, predictive-batch-*.png, generative-batch-*.png,
  automation-batch-*.png, foundations-batch.png, agents-infra-batch.png).
