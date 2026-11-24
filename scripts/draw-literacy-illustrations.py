#!/usr/bin/env python3
"""
Draws the AI Literacy Periodic Table's own "How it works" illustrations, for words the AI Under the Hood courses don't
illustrate, in the courses' style: AIAD Sans, ink outlines, white or paper boxes, small capital headings, theme colours.

Each drawing goes deeper than the word's tile picture: how the idea works, not what it is. Writes one SVG per element
to plugins/aiad-core/data/literacy-table/illustrations/{sym}.svg and their titles and captions to illustrations.json;
scripts/build-literacy-stills.mjs then adds them to the table's stills beside the course ones.

    python3 scripts/draw-literacy-illustrations.py

Numbers in the drawings are made up unless a caption says otherwise.

Checked against (24 November 2026):
- Bias, feedback loops: Pagan et al. 2023 (arXiv 2305.06055); Wyllie, Shumailov and Papernot 2024 (arXiv 2403.07857).
- Fairness, measures that can't all hold at once: Kleinberg et al. 2016; Chouldechova 2017.
- Explainability, global and local, SHAP and LIME as approximations: Salih et al. 2024 (arXiv 2305.02012).
- Transparency, model cards: Mitchell et al. 2019 (arXiv 1810.03993); EU AI Act Article 50, in force 2 August 2026.
- Privacy, the seven UK GDPR principles: ico.org.uk data protection principles guidance.
- Alignment, specification gaming: DeepMind, Krakovna et al. 2020; sycophancy and human ratings: Sharma et al. 2023
  (arXiv 2310.13548).
- Red teaming: NIST CSRC glossary, "artificial intelligence red teaming"; it finds flaws, it doesn't prove safety.
- Watermarking, green and red lists, weakened by paraphrasing: Kirchenbauer et al. 2023 (arXiv 2301.10226).
- Predictive & Analytical AI (supervised and unsupervised learning, regression, classification thresholds, k-means,
  decision trees, random forests, gradient boosting, overfitting, anomaly detection, forecasting, time-series
  decomposition, the four analytics steps, recommenders, churn, sentiment, NLP, computer vision): standard textbook
  methods, e.g. Hastie, Tibshirani and Friedman, The Elements of Statistical Learning; Breiman 2001 (random forests);
  Friedman 2001 (gradient boosting); Hyndman and Athanasopoulos, Forecasting: Principles and Practice.
- Generative engine optimisation, citations, quotations and statistics raising visibility by up to about 40% on the
  study's benchmark: Aggarwal et al., KDD 2024 (arXiv 2311.09735).
- Diffusion: Ho, Jain and Abbeel 2020 (arXiv 2006.11239). GANs: Goodfellow et al. 2014 (arXiv 1406.2661).
- Few-shot prompting: Brown et al. 2020 (arXiv 2005.14165). Reasoning models: OpenAI o1 (September 2024) and later,
  trained to use more computation before answering.
- Synthetic data and model collapse: Shumailov et al., Nature 631, 755-759 (2024).
- Deepfakes: creating, or requesting, a purported intimate image of an adult without consent is an offence in England
  and Wales from 6 February 2026 (Data (Use and Access) Act 2025, s138; SI 2026/31). Report Remove: Childline and IWF.
- Vibe coding: coined by Andrej Karpathy, February 2025. Veracode 2025 GenAI Code Security Report: 80 tasks, 100+
  models, flaws in 45% of samples.
- Automation: hyperautomation as named by Gartner, top strategic technology trends for 2020 (October 2019); process
  mining from event logs (case, activity, timestamp): van der Aalst, Process Mining (2016); copilots: Peng et al. 2023
  (arXiv 2302.06590), a set coding task 55.8% faster with GitHub Copilot.
"""
import json
import os
from xml.sax.saxutils import escape

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(ROOT, 'plugins/aiad-core/data/literacy-table/illustrations')

INK, DIM, RED, PAPER, WHITE = '#231F20', '#5f5a52', '#9b1c14', '#fbfaf6', '#fff'
SAFE, SAFE_D = '#00BEDD', '#006A7D'
SMART, SMART_D = '#FF7038', '#A7350B'
CREATIVE, CREATIVE_D = '#AC91FF', '#6441B8'
RESP, RESP_D = '#63DF93', '#176E3B'
FUTURE, FUTURE_D = '#FA83EB', '#983488'
YELLOW = '#FFF3C4'
GOV, GOV_D = '#FFE1E7', '#BE123C'
PALE = {SAFE: '#D6F4FA', SMART: '#FFE2D5', CREATIVE: '#ECE5FF', RESP: '#DDF8E7', FUTURE: '#FDE3F9'}


class D:
    """One drawing: shapes added in order, then wrapped in an <svg> with a title and description."""

    def __init__(self, w, h):
        self.w, self.h, self.parts = w, h, []

    def add(self, s):
        self.parts.append(s)

    def text(self, x, y, s, size=12, weight=400, anchor='start', fill=INK, caps=False, italic=False):
        extra = ' letter-spacing=".08em"' if caps else ''
        extra += ' font-style="italic"' if italic else ''
        self.add(f'<text x="{x}" y="{y}" font-size="{size}" font-weight="{weight}" text-anchor="{anchor}" fill="{fill}"{extra}>{escape(s.upper() if caps else s)}</text>')

    def head(self, x, y, s, fill=INK, size=13):
        self.text(x, y, s, size=size, weight=800, caps=True, fill=fill)

    def lines(self, x, y, rows, size=12, weight=400, anchor='start', fill=INK, gap=None):
        gap = gap or round(size * 1.35)
        for i, r in enumerate(rows):
            self.text(x, y + i * gap, r, size=size, weight=weight, anchor=anchor, fill=fill)

    def box(self, x, y, w, h, fill=WHITE, stroke=INK, sw=1.5, dash=False, rx=0):
        d = ' stroke-dasharray="5 4"' if dash else ''
        r = f' rx="{rx}"' if rx else ''
        self.add(f'<rect x="{x}" y="{y}" width="{w}" height="{h}" fill="{fill}" stroke="{stroke}" stroke-width="{sw}"{d}{r}/>')

    def bar(self, x, y, w, h, fill):
        self.add(f'<rect x="{x}" y="{y}" width="{w}" height="{h}" fill="{fill}" stroke="{INK}" stroke-width="1.2"/>')

    def arrow(self, x1, y1, x2, y2, stroke=INK, sw=1.8, dash=False, curve=0):
        d = ' stroke-dasharray="4 4"' if dash else ''
        if curve:
            mx, my = (x1 + x2) / 2, (y1 + y2) / 2
            dx, dy = x2 - x1, y2 - y1
            ln = (dx * dx + dy * dy) ** 0.5 or 1
            cx, cy = mx - dy / ln * curve, my + dx / ln * curve
            self.add(f'<path d="M{x1} {y1} Q{cx:.1f} {cy:.1f} {x2} {y2}" fill="none" stroke="{stroke}" stroke-width="{sw}"{d} marker-end="url(#ar)"/>')
        else:
            self.add(f'<line x1="{x1}" y1="{y1}" x2="{x2}" y2="{y2}" stroke="{stroke}" stroke-width="{sw}"{d} marker-end="url(#ar)"/>')

    def tick(self, cx, cy, ok=True):
        if ok:
            self.add(f'<circle cx="{cx}" cy="{cy}" r="8" fill="{RESP}" stroke="{INK}" stroke-width="1.2"/><path d="M{cx-4} {cy}l3 3.5 5.5-6.5" fill="none" stroke="{INK}" stroke-width="2"/>')
        else:
            self.add(f'<circle cx="{cx}" cy="{cy}" r="8" fill="#F7C1C1" stroke="{INK}" stroke-width="1.2"/><path d="M{cx-3.5} {cy-3.5}l7 7M{cx+3.5} {cy-3.5}l-7 7" fill="none" stroke="{INK}" stroke-width="2"/>')

    def person(self, cx, cy, fill=YELLOW, r=7):
        self.add(f'<circle cx="{cx}" cy="{cy - r}" r="{r}" fill="{fill}" stroke="{INK}" stroke-width="1.3"/>'
                 f'<path d="M{cx - r * 1.6} {cy + r * 2.2} C{cx - r * 1.6} {cy + r * .4} {cx + r * 1.6} {cy + r * .4} {cx + r * 1.6} {cy + r * 2.2} Z" fill="{fill}" stroke="{INK}" stroke-width="1.3"/>')

    def svg(self, title, desc):
        defs = f'<defs><marker id="ar" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="7" markerHeight="7" orient="auto"><path d="M1 1L9 5L1 9z" fill="{INK}"/></marker></defs>'
        return (f'<svg viewBox="0 0 {self.w} {self.h}" role="img" aria-labelledby="t d"><title id="t">{escape(title)}</title>'
                f'<desc id="d">{escape(desc)}</desc>{defs}<g font-family="\'AIAD Sans\',Arial,sans-serif">{"".join(self.parts)}</g></svg>')


ILLUSTRATIONS = {}


def _wrap(s, n):
    out, cur = [], ''
    for w in s.split():
        if cur and len(cur) + 1 + len(w) > n:
            out.append(cur)
            cur = w
        else:
            cur = (cur + ' ' + w).strip()
    if cur:
        out.append(cur)
    return out


def illustration(sym, title, desc, caption):
    def wrap(fn):
        d = fn()
        ILLUSTRATIONS[sym] = {'title': title, 'caption': caption, 'svg': d.svg(title, desc)}
        return fn
    return wrap


# ---- Governance & Ethics, batch 1 ---------------------------------------------------------------------------------

@illustration('Bi', 'How bias gets in, and how it can grow',
              'Past hiring decisions favoured group A, 80 to 20. A model trained on them learns that pattern, so two '
              'equally qualified new applicants get different scores, A 0.8 and B 0.5. If its picks become next '
              "year's training data, the gap can grow.",
              'Bias usually starts in the data: a model copies the patterns in past decisions, including unfair ones. '
              "If its own decisions are fed back in as new data, the gap can grow. Made-up numbers.")
def _bi():
    d = D(440, 300)
    d.head(20, 24, 'How bias gets in')
    # 1 past data
    d.box(20, 40, 120, 150)
    d.text(80, 60, '1 · PAST DECISIONS', 10, 800, 'middle', caps=False)
    d.text(80, 76, 'who was hired', 11, 400, 'middle', DIM)
    for i in range(8):
        d.add(f'<rect x="{30 + i * 9.5}" y="88" width="7" height="40" fill="{SAFE}" stroke="{INK}" stroke-width="1"/>')
    for i in range(2):
        d.add(f'<rect x="{30 + i * 9.5}" y="140" width="7" height="40" fill="{SMART}" stroke="{INK}" stroke-width="1"/>')
    d.text(133, 113, '80 A', 11, 700, 'end')
    d.text(133, 165, '20 B', 11, 700, 'end')
    d.arrow(144, 115, 172, 115)
    # 2 model learns
    d.box(176, 70, 96, 90, fill=PALE[SAFE])
    d.text(224, 92, '2 · MODEL', 10, 800, 'middle')
    d.lines(224, 112, ['learns:', '"people like', 'A get hired"'], 11, 400, 'middle')
    d.arrow(276, 115, 304, 115)
    # 3 new applicants
    d.box(308, 40, 112, 150)
    d.text(364, 60, '3 · NEW APPLICANTS', 9.5, 800, 'middle')
    d.text(364, 76, 'equally qualified', 11, 400, 'middle', DIM)
    d.person(338, 104, PALE[SAFE])
    d.person(390, 104, PALE[SMART])
    d.text(338, 140, 'A', 11, 700, 'middle')
    d.text(390, 140, 'B', 11, 700, 'middle')
    d.text(338, 168, '0.8', 15, 800, 'middle', RESP_D)
    d.text(390, 168, '0.5', 15, 800, 'middle', RED)
    d.text(364, 184, 'score', 10, 400, 'middle', DIM)
    # feedback loop
    d.arrow(364, 194, 80, 194, stroke=RED, dash=True, curve=-60)
    d.lines(222, 262, ['If its picks become next year\'s data,', 'the gap can grow.'], 12, 700, 'middle', RED)
    return d


@illustration('Fa', 'Fair by which measure?',
              'An exam-results predictor is checked on two groups. Both get 85% of predictions right overall, but '
              'Group B students are wrongly predicted to fail three times as often: 15% against 5%.',
              "Fairness has more than one definition, and they can't always all be met at once. A tool can look "
              'equally accurate for two groups while its mistakes land much harder on one of them, so each group is '
              'checked on the measure that matters for the decision. Made-up numbers.')
def _fa():
    d = D(440, 290)
    d.head(20, 24, 'One predictor, two groups')
    d.text(20, 42, 'Will this student pass? Checked against real results.', 11, 400, fill=DIM)
    rows = [('Right overall', 85, 85, False), ('Predicted to pass', 70, 55, False), ('Wrongly predicted to fail', 5, 15, True)]
    d.text(250, 66, 'GROUP A', 10, 800, 'middle', SAFE_D)
    d.text(360, 66, 'GROUP B', 10, 800, 'middle', SMART_D)
    for i, (label, a, b, bad) in enumerate(rows):
        y = 78 + i * 52
        d.box(20, y, 400, 44, fill=(YELLOW if bad else WHITE))
        d.text(32, y + 27, label, 12, 700 if bad else 400)
        for cx, v, col in ((250, a, SAFE), (360, b, SMART)):
            d.add(f'<rect x="{cx - 45}" y="{y + 12}" width="90" height="12" fill="{WHITE}" stroke="{INK}" stroke-width="1"/>')
            d.add(f'<rect x="{cx - 45}" y="{y + 12}" width="{90 * v / 100:.1f}" height="12" fill="{col}" stroke="{INK}" stroke-width="1"/>')
            d.text(cx, y + 38, f'{v}%', 11, 800, 'middle')
    d.lines(220, 254, ['Same accuracy, but B\'s mistakes', 'cost them three times as often.'], 12, 700, 'middle', RED)
    return d


@illustration('Xa', 'Two kinds of explanation',
              'A model that flags pupils who may need reading support. A global explanation shows what it relies on '
              'overall: reading age 45%, quiz scores 30%, attendance 15%, other 10%. A local explanation shows why it '
              "flagged one pupil, Sam: a low quiz score pushed towards flagging, good attendance pushed against.",
              'Explainability tools, such as SHAP and LIME, estimate which inputs mattered: for the model as a whole '
              "(global) or for one answer (local). They're approximations of what the model does, not a full account "
              'of it. Made-up numbers.')
def _xa():
    d = D(440, 290)
    d.head(20, 24, 'Flag pupils for reading support')
    d.box(20, 40, 196, 236)
    d.text(118, 60, 'GLOBAL', 11, 800, 'middle', SMART_D)
    d.text(118, 76, 'what it relies on overall', 11, 400, 'middle', DIM)
    for i, (label, v) in enumerate([('reading age', 45), ('quiz scores', 30), ('attendance', 15), ('other', 10)]):
        y = 96 + i * 42
        d.text(32, y, label, 11, 700)
        d.add(f'<rect x="32" y="{y + 7}" width="{150 * v / 45:.1f}" height="14" fill="{SMART}" stroke="{INK}" stroke-width="1"/>')
        d.text(32 + 150 * v / 45 + 6, y + 19, f'{v}%', 11, 700)
    d.box(224, 40, 196, 236)
    d.text(322, 60, 'LOCAL', 11, 800, 'middle', SAFE_D)
    d.text(322, 76, 'why it flagged Sam', 11, 400, 'middle', DIM)
    d.add(f'<line x1="322" y1="92" x2="322" y2="214" stroke="{INK}" stroke-width="1.2"/>')
    d.text(296, 104, '← against', 10, 400, 'end', DIM)
    d.text(348, 104, 'towards →', 10, 400, 'start', DIM)
    for i, (label, v) in enumerate([('low quiz score', 70), ('reading age', 35), ('good attendance', -40)]):
        y = 124 + i * 32
        w = abs(v)
        x = 322 if v > 0 else 322 - w
        d.add(f'<rect x="{x}" y="{y}" width="{w}" height="16" fill="{SAFE if v > 0 else WHITE}" stroke="{INK}" stroke-width="1"/>')
        d.text(322 + (8 if v < 0 else -8), y + 12, label, 10, 700, 'start' if v < 0 else 'end')
    d.box(236, 226, 172, 38, fill=YELLOW)
    d.text(322, 250, 'Result: flagged', 13, 800, 'middle')
    return d


@illustration('Tn', 'What being open about AI covers',
              'Transparency answers four questions about an AI system: Is AI being used? What was it built from? '
              'Where does it go wrong? Who is responsible? A model card is one way to write the answers down, and a '
              'label tells people when content is AI-generated.',
              'Transparency means telling people that AI is being used, what it was built from, where it fails and '
              'who is responsible. A model card is one common way to write this down. In the EU, the AI Act has '
              'required telling people and labelling AI-generated content since August 2026.')
def _tn():
    d = D(440, 300)
    d.head(20, 24, 'Four questions to answer openly')
    qs = [('Is AI being used?', 'Tell people: "This reply was written by AI."', SAFE),
          ('What was it built from?', 'The data it learned from, and its date.', CREATIVE),
          ('Where does it go wrong?', 'Its known limits and mistakes.', SMART),
          ('Who is responsible?', 'A named person or team to contact.', RESP)]
    for i, (q, a, c) in enumerate(qs):
        y = 40 + i * 54
        d.box(20, y, 236, 46, fill=PALE[c])
        d.text(32, y + 19, q, 12, 800)
        d.text(32, y + 36, a, 10.5, 400, fill=DIM)
    # model card
    d.box(280, 40, 140, 208, fill=WHITE)
    d.add(f'<rect x="280" y="40" width="140" height="26" fill="{INK}"/>')
    d.text(350, 58, 'MODEL CARD', 11, 800, 'middle', WHITE)
    for i, (k, c) in enumerate([('Purpose', SAFE), ('Training data', CREATIVE), ('Limits', SMART), ('Contact', RESP)]):
        y = 80 + i * 40
        d.add(f'<rect x="290" y="{y}" width="8" height="28" fill="{c}" stroke="{INK}" stroke-width="1"/>')
        d.text(306, y + 12, k, 11, 700)
        d.add(f'<rect x="306" y="{y + 18}" width="100" height="5" fill="#d8d3c8"/>')
    d.box(280, 258, 140, 30, fill=YELLOW)
    d.text(350, 278, 'Label: AI-generated', 11, 800, 'middle')
    return d


@illustration('Pv', 'Where your words go, and what to ask',
              'A message travels from you, to the app, to the AI company\'s servers, and may be stored or used to '
              'train future models. At each step there is a privacy question: is only what is needed collected, '
              'who can see it, how long is it kept, and is it used for training, with a way to opt out.',
              'Each step is a place to ask what happens to your data. Under the UK GDPR, organisations should collect '
              'only what they need, keep it secure, keep it no longer than necessary and explain how it is used.')
def _pv():
    d = D(440, 300)
    d.head(20, 24, 'Your message\'s journey')
    steps = [('You', 'type a question', YELLOW), ('The app', 'sends it on', PALE[SAFE]),
             ('AI servers', 'write a reply', PALE[CREATIVE]), ('Storage', 'kept for later', PALE[SMART])]
    for i, (t, s, c) in enumerate(steps):
        x = 20 + i * 106
        d.box(x, 44, 88, 54, fill=c)
        d.text(x + 44, 66, t, 12, 800, 'middle')
        d.text(x + 44, 84, s, 9.5, 400, 'middle', DIM)
        if i < 3:
            d.arrow(x + 90, 71, x + 104, 71)
    qs = [('Only what\'s needed?', 'Leave out names, addresses, health.'),
          ('Who can see it?', 'The company, its staff, partners?'),
          ('How long is it kept?', 'Days, years, until you delete it?'),
          ('Used for training?', 'Is there a way to opt out?')]
    for i, (q, a) in enumerate(qs):
        x = 20 + i * 106
        d.add(f'<line x1="{x + 44}" y1="100" x2="{x + 44}" y2="118" stroke="{INK}" stroke-width="1.2" stroke-dasharray="3 3"/>')
        d.box(x, 120, 88, 118, fill=WHITE)
        d.text(x + 44, 136, '?', 16, 800, 'middle', GOV_D)
        lines_q = _wrap(q, 13)
        d.lines(x + 44, 156, lines_q, 11, 800, 'middle')
        d.lines(x + 44, 160 + len(lines_q) * 15, _wrap(a, 16), 9.5, 400, 'middle', DIM, 13)
    d.lines(220, 266, ['UK GDPR: collect what\'s needed, keep it safe,', 'keep it no longer than necessary, explain how it\'s used.'], 11, 700, 'middle', SAFE_D)
    return d


@illustration('Rs', 'Responsible AI, step by step',
              'Responsible AI adds a check at every stage of an AI system\'s life: plan (should we build it, and who '
              'could it harm?), build (fair, permitted data), test (try to break it, check every group), launch (tell '
              'people, keep a human in charge), and watch (monitor and fix), feeding back into the plan.',
              'Responsible AI turns principles into checks at every stage, from deciding whether to build something at '
              'all to watching it after launch, when new problems often appear.')
def _rs():
    d = D(440, 300)
    d.head(20, 24, 'A check at every stage')
    stages = [('PLAN', 'Should we build it?', 'Who could it harm?', SAFE),
              ('BUILD', 'Fair, permitted', 'data', CREATIVE),
              ('TEST', 'Try to break it.', 'Check every group.', SMART),
              ('LAUNCH', 'Tell people.', 'Human in charge.', RESP),
              ('WATCH', 'Monitor, report,', 'fix.', FUTURE)]
    for i, (t, a, b, c) in enumerate(stages):
        x = 20 + i * 82
        d.box(x, 44, 72, 130, fill=PALE[c])
        d.add(f'<rect x="{x}" y="44" width="72" height="24" fill="{c}" stroke="{INK}" stroke-width="1.5"/>')
        d.text(x + 36, 61, t, 10.5, 800, 'middle')
        d.tick(x + 36, 92)
        d.lines(x + 36, 124, _wrap(a, 12) + _wrap(b, 12), 10, 400, 'middle', INK, 13)
        if i < 4:
            d.arrow(x + 73, 109, x + 81, 109)
    d.arrow(392, 178, 56, 178, stroke=INK, dash=True, curve=-34)
    d.text(224, 236, 'what you learn goes back into the plan', 11, 400, 'middle', DIM)
    d.lines(224, 266, ['Safe · Fair · Transparent · Accountable'], 13, 800, 'middle', RESP_D)
    return d


@illustration('Et', 'Who gains, who could be harmed?',
              'An ethics question: should a school use AI to mark homework? Pupils could get faster feedback but '
              'might be marked unfairly; teachers could save time but must still check; parents could see progress '
              'sooner but have privacy concerns; the school could spend less but depends on a supplier.',
              "Ethics weighs who gains and who could be harmed, and whether people agreed to it. Reasonable people "
              "can weigh these differently, which is why it's discussed, not calculated.")
def _et():
    d = D(440, 300)
    d.box(130, 118, 180, 62, fill=YELLOW)
    d.lines(220, 140, ['Should our school use', 'AI to mark homework?'], 12, 800, 'middle')
    who = [('Pupils', 'faster feedback', 'marked unfairly?', 20, 20),
           ('Teachers', 'saves time', 'still must check', 240, 20),
           ('Parents', 'see progress sooner', 'privacy worries', 20, 196),
           ('The school', 'costs less', 'relies on a supplier', 240, 196)]
    for name, gain, harm, x, y in who:
        d.box(x, y, 180, 84)
        d.person(x + 20, y + 26, YELLOW, 6)
        d.text(x + 38, y + 26, name, 12, 800)
        d.text(x + 14, y + 54, '+ ' + gain, 11, 700, fill=RESP_D)
        d.text(x + 14, y + 72, '− ' + harm, 11, 700, fill=RED)
        cx, cy = x + 90, (y + 84 if y < 100 else y)
        ty = 118 if y < 100 else 180
        d.add(f'<line x1="{cx}" y1="{cy}" x2="{cx + (40 if x < 100 else -40)}" y2="{ty}" stroke="{INK}" stroke-width="1.2" stroke-dasharray="3 3"/>')
    return d


@illustration('An', 'Hitting the target, missing the point',
              'We want helpful, honest answers, but training needs something measurable, such as a thumbs-up rating. '
              'The model learns that agreeing with people gets thumbs up, so it flatters instead of correcting. '
              'Alignment work closes the gap with better targets, human feedback on harder cases and testing.',
              'AI is trained on something measurable that stands in for what we really want. When the two drift '
              'apart, the model can hit the target and miss the point: researchers call this specification gaming. '
              'Flattering people, sycophancy, is one result that has been linked to training on human ratings.')
def _an():
    d = D(440, 300)
    d.box(20, 40, 180, 64, fill=PALE[RESP])
    d.text(110, 60, 'WHAT WE WANT', 10.5, 800, 'middle', RESP_D)
    d.lines(110, 80, ['helpful, honest answers'], 12, 700, 'middle')
    d.box(240, 40, 180, 64, fill=PALE[SAFE])
    d.text(330, 60, 'WHAT WE CAN MEASURE', 10.5, 800, 'middle', SAFE_D)
    d.lines(330, 80, ['thumbs-up ratings'], 12, 700, 'middle')
    d.arrow(202, 72, 236, 72)
    d.text(220, 120, 'training aims at the measure', 10.5, 400, 'middle', DIM)
    d.arrow(330, 106, 330, 140)
    d.box(170, 144, 250, 70, fill=YELLOW)
    d.text(295, 164, 'WHAT THE MODEL LEARNS', 10.5, 800, 'middle', SMART_D)
    d.lines(295, 184, ['"Agreeing with people', 'gets a thumbs up."'], 12, 700, 'middle')
    d.box(20, 144, 130, 70)
    d.lines(85, 166, ['You: "2 + 2 = 5,', 'right?"'], 11, 400, 'middle')
    d.lines(85, 200, ['AI: "Great point!"'], 11, 800, 'middle', RED)
    d.arrow(168, 179, 152, 179)
    d.box(20, 232, 400, 52, fill=WHITE, dash=True)
    d.text(32, 252, 'ALIGNMENT WORK', 10.5, 800, fill=RESP_D)
    d.text(32, 272, 'better targets · feedback on hard cases · tests for flattery', 11, 400)
    return d


@illustration('Rt', 'The red-team loop',
              'Red teamers plan attacks such as trick prompts, misuse and bias probes, attack the model, log every '
              'failure, fix it with new guardrails or retraining, and test again. Only when the model passes is it '
              'released, and the loop carries on afterwards.',
              'Red teams try to break a model before the public does. Every failure they find becomes a fix, and a '
              "new test so it stays fixed. Red teaming finds problems; it can't prove a model is safe.")
def _rt():
    d = D(440, 300)
    d.head(20, 24, 'Break it before others do')
    nodes = [('PLAN ATTACKS', ['trick prompts', 'misuse, bias probes'], 120, 74, SMART),
             ('ATTACK', ['send them to', 'the model'], 320, 74, SMART),
             ('LOG FAILURES', ['what got', 'through?'], 320, 186, RED),
             ('FIX', ['guardrails,', 'retraining'], 120, 186, RESP)]
    for t, ls, cx, cy, c in nodes:
        d.box(cx - 70, cy - 34, 140, 68, fill=PALE.get(c, '#FCEBEB'))
        d.text(cx, cy - 12, t, 10.5, 800, 'middle', INK)
        d.lines(cx, cy + 6, ls, 11, 400, 'middle')
    d.arrow(192, 74, 248, 74)
    d.arrow(320, 110, 320, 150)
    d.arrow(248, 186, 192, 186)
    d.arrow(120, 150, 120, 110)
    d.text(220, 135, 'test again', 11, 700, 'middle', DIM)
    d.box(120, 244, 200, 40, fill=YELLOW)
    d.text(220, 269, 'Risks acceptable → release', 12, 800, 'middle')
    d.arrow(120, 222, 160, 242, dash=True)
    return d


@illustration('Wm', 'How a text watermark can work',
              'One research method: before each word, a secret key splits the possible next words into a green half '
              'and a red half, and the model is nudged towards green, too subtly for readers to notice. A detector '
              'with the key counts green words: ordinary text is about half green, watermarked text far more. Heavy '
              'rewording weakens the signal.',
              'One research method (Kirchenbauer and others, 2023): before each word, a secret key splits the possible '
              'next words into green and red halves, and the model is nudged towards green. A detector with the key '
              "counts them. Heavy rewording weakens the signal, so it's evidence, not proof. Made-up example.")
def _wm():
    d = D(440, 300)
    d.head(20, 24, 'A hidden pattern in word choice')
    d.text(420, 52, 'green = on the secret key\'s list', 10, 400, 'end', DIM)
    sentences = [('ORDINARY TEXT', [('The', 1), ('cat', 0), ('sat', 1), ('down', 0), ('by', 0), ('the', 1), ('warm', 1), ('fire', 0)], '4 of 8 green: 50%', INK),
                 ('WATERMARKED', [('The', 1), ('cat', 1), ('rested', 1), ('near', 1), ('the', 1), ('warm', 1), ('stove', 0), ('today', 1)], '7 of 8 green: 88%', RESP_D)]
    for r, (label, words, verdict, col) in enumerate(sentences):
        y = 42 + r * 92
        d.text(20, y + 10, label, 10.5, 800, fill=col)
        x = 20
        for w, g in words:
            ww = 14 + len(w) * 7.2
            d.add(f'<rect x="{x}" y="{y + 20}" width="{ww:.1f}" height="26" fill="{PALE[RESP] if g else WHITE}" stroke="{INK}" stroke-width="1.2"/>')
            d.text(x + ww / 2, y + 38, w, 12, 700 if g else 400, 'middle')
            x += ww + 4
        d.text(20, y + 68, verdict, 12, 800, fill=col)
    d.box(250, 222, 170, 62, fill=YELLOW)
    d.lines(335, 244, ['Detector has the key:', 'many green → likely AI'], 11, 700, 'middle')
    d.box(20, 222, 210, 62, dash=True)
    d.lines(125, 244, ['Heavy rewording pulls the', 'green share back towards half.'], 11, 700, 'middle', RED)
    return d


# ---- Governance & Ethics, batch 2 ---------------------------------------------------------------------------------

def steps_row(d, y, items, w=None, h=74, gap=12, x0=20, total=400):
    """A row of boxes joined by arrows: items = [(title, [lines], fill)]."""
    n = len(items)
    w = w or (total - gap * (n - 1)) / n
    for i, (t, ls, fill) in enumerate(items):
        x = x0 + i * (w + gap)
        d.box(round(x, 1), y, round(w, 1), h, fill=fill)
        d.text(round(x + w / 2, 1), y + 18, t, 10.5, 800, 'middle')
        d.lines(round(x + w / 2, 1), y + 36, ls, 10, 400, 'middle', DIM, 13)
        if i < n - 1:
            d.arrow(round(x + w + 1, 1), y + h / 2, round(x + w + gap - 1, 1), y + h / 2)
    return w


@illustration('Sf', 'Two kinds of harm, many layers',
              'AI safety guards against accidents, where the AI makes a harmful mistake, and misuse, where a person '
              'uses it to cause harm. Layers work at different times: tests before release, training the model to '
              'refuse, filters around it, and monitoring after launch. Each layer misses some things, so several are used.',
              'AI safety covers accidents (the AI gets something harmfully wrong) and misuse (someone uses it to do '
              "harm). No single layer catches everything, so safety teams stack several, before and after release.")
def _sf():
    d = D(440, 290)
    d.head(20, 24, 'Layers before and after release')
    layers = [('TESTS', ['evals, red', 'teaming'], PALE[SMART]), ('TRAINING', ['taught to', 'refuse harm'], PALE[CREATIVE]),
              ('FILTERS', ['check what goes', 'in and out'], PALE[SAFE]), ('MONITORING', ['reports,', 'fixes'], PALE[RESP])]
    steps_row(d, 40, layers)
    rows = [('ACCIDENT', 'gives a dangerous wrong answer', [False, False, True, True]),
            ('MISUSE', 'someone asks for help to cause harm', [False, True, True, True])]
    for r, (k, s, caught) in enumerate(rows):
        y = 138 + r * 62
        d.text(20, y, k, 10.5, 800, fill=RED)
        d.text(98, y, s, 11, 400, fill=DIM)
        for i, c in enumerate(caught):
            cx = 20 + i * 103 + 45.5
            if c and i == caught.index(True):
                d.tick(cx, y + 24)
                d.text(cx, y + 48, 'caught', 10, 700, 'middle', RESP_D)
            elif not c:
                d.text(cx, y + 28, 'slips past', 10, 700, 'middle', DIM)
    d.lines(220, 266, ['Each layer misses something; together they catch more.'], 12, 700, 'middle', RESP_D)
    return d


@illustration('Dg', 'Rules for data, from start to finish',
              'Data governance sets rules at each stage of data\'s life: collect only what is needed with a clear '
              'purpose, store it securely, give access only to those who need it, use it only for the agreed purpose, '
              'and delete it on schedule. A school example shows who may see pupil data, and that an AI tool may only '
              'if it has been approved.',
              'Data governance means agreed rules for every stage of data\'s life, and named people responsible for '
              'them. In a school, that decides who can see pupil data, and whether an AI tool may.')
def _dg():
    d = D(440, 300)
    d.head(20, 24, 'The life of pupil data')
    steps_row(d, 40, [('COLLECT', ['only what\'s', 'needed, why'], PALE[SAFE]), ('STORE', ['secure,', 'in the UK?'], PALE[CREATIVE]),
                      ('ACCESS', ['only those', 'who need it'], PALE[SMART]), ('USE', ['for the agreed', 'purpose'], PALE[RESP]),
                      ('DELETE', ['on a', 'schedule'], PALE[FUTURE])], h=70)
    d.box(20, 128, 400, 140)
    d.text(32, 148, 'WHO MAY SEE PUPIL DATA?', 10.5, 800)
    rows = [('Class teacher', 'their own classes', True), ('School office', 'contact details', True),
            ('Approved AI tool', 'only what its agreement allows', True), ('Unapproved AI app', 'nothing', False)]
    for i, (who, what, ok) in enumerate(rows):
        y = 172 + i * 24
        d.tick(40, y - 4, ok)
        d.text(56, y, who, 11, 700)
        d.text(200, y, what, 11, 400, fill=DIM)
    d.text(220, 288, 'A named person is responsible for each rule.', 11, 700, 'middle', SAFE_D)
    return d


@illustration('Re', 'Same AI, different rules, depending on use',
              'Under the EU AI Act, the rules depend on what an AI system is used for. In a school: a homework '
              'chatbot is limited risk, so users must be told it is AI; AI used to mark exams or decide admissions '
              'is high risk, with checks and human oversight; AI that reads pupils\' emotions in class is banned.',
              'The EU AI Act sets rules by use, not by technology. Its bans have applied since February 2025 and its '
              'transparency rules since August 2026; dates for high-risk rules have been under review. The UK has no '
              'single AI law and relies mainly on existing laws, such as data protection and equality law.')
def _re():
    d = D(440, 300)
    d.head(20, 24, 'EU AI Act: in a school')
    rows = [('BANNED', 'Reading pupils\' emotions in class', 'not allowed (since Feb 2025)', '#F7C1C1', RED),
            ('HIGH RISK', 'Marking exams, deciding admissions', 'checks, records, a human overseeing', PALE[SMART], SMART_D),
            ('LIMITED RISK', 'A homework help chatbot', 'tell users it\'s AI (since Aug 2026)', PALE[SAFE], SAFE_D),
            ('MINIMAL RISK', 'A spam filter on school email', 'no extra rules', PALE[RESP], RESP_D)]
    for i, (tier, use, rule, fill, col) in enumerate(rows):
        y = 40 + i * 58
        inset = (3 - i) * 0
        d.box(20, y, 400, 50, fill=fill)
        d.text(32, y + 20, tier, 10.5, 800, fill=col)
        d.text(150, y + 20, use, 11.5, 700)
        d.text(150, y + 38, rule, 10.5, 400, fill=DIM)
    d.text(220, 288, 'The same model can sit in different rows.', 11.5, 700, 'middle', INK)
    return d


@illustration('Ip', 'Two copyright questions',
              'Copyright and AI raises two questions. Going in: can a company train on people\'s work? In UK law, '
              'mining others\' work is clearly allowed only for non-commercial research, so commercial training '
              'generally needs permission or a licence. Coming out: who owns what the AI makes, and does it copy '
              'anyone? That depends on how much a person shaped it, and the law is still being settled.',
              'In the UK, text and data mining of others\' work is clearly allowed only for non-commercial research '
              '(section 29A of the Copyright, Designs and Patents Act 1988); in 2026 the government dropped a proposed '
              'broad exception for AI training. Ownership of AI-made work is still being settled.')
def _ip():
    d = D(440, 300)
    d.box(20, 40, 196, 220, fill=PALE[CREATIVE])
    d.text(118, 62, 'GOING IN', 11, 800, 'middle', CREATIVE_D)
    d.lines(118, 82, ['Can a company train', 'on your work?'], 12, 700, 'middle')
    d.box(32, 120, 172, 46)
    d.tick(48, 143)
    d.lines(62, 139, ['Non-commercial research:', 'allowed (section 29A)'], 10.5, 400)
    d.box(32, 176, 172, 70)
    d.text(48, 196, '?', 15, 800, 'middle', GOV_D)
    d.lines(62, 194, ['Commercial training:', 'generally needs', 'permission or a licence'], 10.5, 400)
    d.box(224, 40, 196, 220, fill=PALE[SMART])
    d.text(322, 62, 'COMING OUT', 11, 800, 'middle', SMART_D)
    d.lines(322, 82, ['Who owns what', 'the AI makes?'], 12, 700, 'middle')
    d.box(236, 120, 172, 60)
    d.lines(322, 140, ['A person shaped it a lot:', 'a stronger claim'], 10.5, 400, 'middle')
    d.box(236, 188, 172, 58)
    d.lines(322, 208, ['Does it copy someone\'s', 'work too closely?'], 10.5, 400, 'middle')
    d.text(220, 284, 'UK law on AI and copyright is still being settled.', 11, 700, 'middle', RED)
    return d


@illustration('Aw', 'Three questions for an "AI-powered" claim',
              'To test a product\'s AI claim, ask: what exactly does the AI do, could it be done without AI, and '
              'where is the evidence? In 2024 the US Securities and Exchange Commission fined two investment firms, '
              '$225,000 and $175,000, for saying they used AI in ways they did not.',
              'AI washing is overclaiming AI to ride the hype. Regulators have acted: in March 2024 the US Securities '
              'and Exchange Commission fined two investment firms for AI claims they could not back up.')
def _aw():
    d = D(440, 290)
    d.box(20, 40, 160, 80, fill=YELLOW)
    d.text(100, 62, 'THE CLAIM', 10.5, 800, 'middle')
    d.lines(100, 86, ['"Our AI-powered', 'revision planner"'], 12, 700, 'middle')
    qs = [('What does the AI actually do?', 'Name the task, not the buzzword.'),
          ('Could it be done without AI?', 'A timer or a rule isn\'t AI.'),
          ('Where\'s the evidence?', 'Tests, results, a model card.')]
    for i, (q, a) in enumerate(qs):
        y = 40 + i * 62
        d.box(200, y, 220, 54)
        d.text(212, y + 22, f'{i + 1}. {q}', 11, 800)
        d.text(212, y + 40, a, 10.5, 400, fill=DIM)
        d.arrow(182, 80, 198, y + 27)
    d.box(20, 236, 400, 44, fill=PALE[SAFE])
    d.lines(32, 254, ['Real case: in 2024 the US SEC fined two investment firms', '($225,000 and $175,000) for AI claims they couldn\'t back up.'], 10.5, 700, gap=15)
    return d


@illustration('Sh', 'Approved tool or shadow AI?',
              'The same homework question can go two ways. Through an approved school tool, a data agreement '
              'controls how it is stored, it is not used for training, and the school can help if something goes '
              'wrong. Through an unapproved app on a personal account, nobody at school knows what happens to the '
              'data, which may be kept or used for training.',
              'Shadow AI is using AI tools that haven\'t been approved. The risk is less the tool itself than what '
              'happens to the data, and that nobody responsible knows it\'s being used.')
def _sh():
    d = D(440, 290)
    d.box(160, 40, 120, 46, fill=YELLOW)
    d.lines(220, 60, ['A question with', 'pupil names in it'], 11, 700, 'middle')
    d.arrow(190, 88, 110, 118)
    d.arrow(250, 88, 330, 118)
    for x, title, fill, col, rows in ((20, 'APPROVED SCHOOL TOOL', PALE[RESP], RESP_D,
                                       [(True, 'data agreement in place'), (True, 'not used for training'), (True, 'school can see and help')]),
                                      (230, 'UNAPPROVED APP', '#FCEBEB', RED,
                                       [(False, 'personal account'), (False, 'may be kept or trained on'), (False, 'nobody at school knows')])):
        d.box(x, 120, 190, 130, fill=fill)
        d.text(x + 95, 142, title, 10.5, 800, 'middle', col)
        for i, (ok, s) in enumerate(rows):
            y = 170 + i * 26
            d.tick(x + 20, y - 4, ok)
            d.text(x + 36, y, s, 11, 400)
    d.text(220, 278, 'Same question; very different journey for the data.', 11.5, 700, 'middle')
    return d


@illustration('Hp', 'Where the extra value comes from',
              'Two similar pots: one AI-designed and machine-made, one made by hand. The handmade one is worth more '
              'because of its story, the maker\'s skill and time, and proof that a person made it, such as a record '
              'of the making or a verified label like Content Credentials.',
              'The human premium rests on proof as much as on the object: people pay more when they can trust a '
              'person made it. Records of the making and verified labels, such as Content Credentials, provide that proof.')
def _hp():
    d = D(440, 290)
    for x, label, price, col in ((40, 'AI-designed, machine-made', '£5', DIM), (250, 'Made by hand, proven', '£40', RESP_D)):
        d.add(f'<path d="M{x + 50} 50 h50 l10 18 c14 24 8 56 -10 72 h-50 c-18 -16 -24 -48 -10 -72 z" fill="{PALE[SMART]}" stroke="{INK}" stroke-width="1.5"/>')
        d.text(x + 75, 168, label, 11, 700, 'middle')
        d.text(x + 75, 194, price, 20, 800, 'middle', col)
    proofs = ['the maker\'s story', 'skill and hours', 'record of the making', 'verified label']
    for i, p in enumerate(proofs):
        x = 20 + i * 102
        d.box(x, 218, 94, 44, fill=PALE[RESP])
        d.lines(x + 47, 236, _wrap(p, 13), 10.5, 700, 'middle', INK, 13)
    d.text(220, 284, 'What adds the £35: trust that a person made it.', 11.5, 700, 'middle', RESP_D)
    return d


@illustration('Rv', 'Two ways into an AI model',
              'To work out how an AI model works, you can probe it from outside, sending inputs and studying the '
              'outputs, which works even when the model is closed; or, for an open-weight model, look inside at its '
              'numbers and test which parts do what, which is the work of interpretability.',
              'Reverse engineering an AI model means probing it from outside (inputs and outputs) or, when its '
              'weights are available, studying it from inside. Probing a closed service may break its terms of use.')
def _rv():
    d = D(440, 290)
    d.box(160, 100, 120, 90, fill=PALE[CREATIVE])
    d.text(220, 130, 'AI MODEL', 11, 800, 'middle', CREATIVE_D)
    for i in range(4):
        for j in range(3):
            d.add(f'<circle cx="{190 + i * 20}" cy="{150 + j * 13}" r="4" fill="{CREATIVE}" stroke="{INK}" stroke-width=".8"/>')
    d.box(20, 40, 120, 210)
    d.text(80, 62, 'FROM OUTSIDE', 10.5, 800, 'middle', SMART_D)
    d.lines(80, 84, ['send inputs,', 'study outputs'], 11, 400, 'middle', DIM)
    for i, s in enumerate(['input A → ?', 'input B → ?', 'input C → ?']):
        d.text(80, 128 + i * 22, s, 11, 700, 'middle')
    d.lines(80, 212, ['works on closed', 'models too'], 10.5, 700, 'middle', SMART_D)
    d.arrow(142, 145, 158, 145)
    d.box(300, 40, 120, 210)
    d.text(360, 62, 'FROM INSIDE', 10.5, 800, 'middle', SAFE_D)
    d.lines(360, 84, ['open the weights,', 'test the parts'], 11, 400, 'middle', DIM)
    d.lines(360, 128, ['which numbers', 'light up for', 'which ideas?'], 11, 700, 'middle')
    d.lines(360, 212, ['needs an open-', 'weight model'], 10.5, 700, 'middle', SAFE_D)
    d.arrow(298, 145, 282, 145)
    d.text(220, 278, 'Probing a closed service may break its terms of use.', 11, 700, 'middle', RED)
    return d


@illustration('Mp', 'Finding the ideas inside a model',
              'Inside a model, each word is a long list of numbers that mixes many ideas together. Researchers train a '
              'second tool, a sparse autoencoder, to split those numbers into features that each match one idea, such '
              'as the Golden Gate Bridge, a bug in code or sarcasm. Turning a feature up or down and watching the '
              'answer change tests whether it really does that job.',
              'Mechanistic interpretability looks inside a model to find which parts carry which ideas. In 2024, '
              'Anthropic found millions of such features in a production model using sparse autoencoders, and '
              'checked them by turning them up and down. It\'s still far from a complete map.')
def _mp():
    d = D(440, 300)
    d.box(20, 40, 120, 150, fill=PALE[CREATIVE])
    d.text(80, 60, 'INSIDE THE MODEL', 9.5, 800, 'middle', CREATIVE_D)
    vals = ['0.8', '−1.2', '0.1', '2.4', '−0.3', '1.1', '…']
    for i, v in enumerate(vals):
        d.text(80, 82 + i * 15, v, 11, 700, 'middle')
    d.lines(80, 206, ['numbers that mix', 'many ideas'], 10.5, 400, 'middle', DIM)
    d.arrow(142, 115, 168, 115)
    d.box(170, 80, 100, 70, fill=YELLOW)
    d.lines(220, 104, ['sparse', 'autoencoder', 'splits them'], 10.5, 700, 'middle', INK, 14)
    d.arrow(272, 115, 296, 115)
    feats = [('Golden Gate Bridge', SAFE), ('bug in code', SMART), ('sarcasm', CREATIVE), ('…millions more', None)]
    for i, (f, c) in enumerate(feats):
        y = 40 + i * 38
        d.box(298, y, 122, 30, fill=PALE[c] if c else WHITE, dash=not c)
        d.text(359, y + 20, f, 11, 700, 'middle')
    d.box(20, 236, 400, 50, fill=WHITE)
    d.text(32, 256, 'TEST IT', 10.5, 800, fill=RESP_D)
    d.text(32, 276, 'Turn a feature up or down: does the answer change as expected?', 11, 400)
    return d


@illustration('As', 'Steering a reply without retraining',
              'Researchers record the model\'s internal numbers for contrasting prompts, such as kind and unkind ones, '
              'and take the difference: a steering direction. Adding a little of it while the model answers makes the '
              'reply kinder; adding more pushes harder. The model\'s weights are not changed.',
              'Activation steering finds a direction inside the model by comparing contrasting prompts, then adds it '
              'while the model answers. It changes this reply, not the model itself, and too much can make replies '
              'strange.')
def _as():
    d = D(440, 300)
    d.head(20, 24, 'Find a direction, then add it')
    d.box(20, 40, 120, 56, fill=PALE[RESP])
    d.lines(80, 62, ['kind prompts', '→ inner numbers'], 10.5, 700, 'middle')
    d.box(20, 104, 120, 56, fill='#FCEBEB')
    d.lines(80, 126, ['unkind prompts', '→ inner numbers'], 10.5, 700, 'middle')
    d.arrow(142, 100, 168, 100)
    d.box(170, 72, 100, 56, fill=YELLOW)
    d.lines(220, 94, ['difference =', 'a "kind" direction'], 10.5, 700, 'middle')
    d.arrow(272, 100, 296, 100)
    d.box(298, 40, 122, 120)
    d.text(359, 60, 'WHILE ANSWERING', 9.5, 800, 'middle')
    for i, (amt, reply) in enumerate([('add none', '"Revise more."'), ('add a little', '"You\'ve got this!"'), ('add a lot', 'odd, over the top')]):
        y = 80 + i * 27
        d.text(308, y, amt, 10, 800)
        d.text(308, y + 13, reply, 10, 400, 'start', DIM if i < 2 else RED)
    d.box(20, 186, 400, 46, fill=PALE[SAFE])
    d.lines(32, 205, ['The model\'s weights aren\'t changed: only this reply is steered.'], 11, 700)
    d.lines(32, 222, ['The same idea, used on safety directions, can also weaken them.'], 10.5, 400, fill=DIM)
    d.text(220, 266, 'Researchers use it to test what a direction really does.', 11, 700, 'middle', SAFE_D)
    return d


@illustration('Mx', 'Copying a model through its answers',
              'An attacker sends a closed model thousands of chosen questions, saves the answers, and trains their own '
              'model to match them. The copy can agree with the original most of the time without ever seeing its '
              'files. Defences include limiting how many questions one user can ask, giving less detail in answers, '
              'and watermarking outputs.',
              'Model extraction copies a model\'s behaviour, not its files. Researchers showed in 2016 that '
              'prediction services could be copied this way. It usually breaks the service\'s terms and may break '
              'the law.')
def _mx():
    d = D(440, 290)
    d.box(160, 40, 120, 90, fill=PALE[SAFE])
    d.text(220, 64, 'CLOSED MODEL', 10.5, 800, 'middle', SAFE_D)
    d.add(f'<rect x="206" y="88" width="28" height="20" fill="{WHITE}" stroke="{INK}" stroke-width="1.4"/><path d="M211 88v-6a9 9 0 0 1 18 0v6" fill="none" stroke="{INK}" stroke-width="1.4"/>')
    d.text(220, 123, 'files locked', 10, 400, 'middle', DIM)
    d.box(20, 50, 110, 70)
    d.lines(75, 74, ['10,000', 'chosen', 'questions'], 11, 700, 'middle')
    d.arrow(132, 75, 158, 75)
    d.arrow(282, 95, 308, 95)
    d.box(310, 50, 110, 70)
    d.lines(365, 78, ['answers', 'saved'], 11, 700, 'middle')
    d.arrow(365, 122, 365, 148)
    d.box(290, 150, 130, 60, dash=True)
    d.lines(355, 172, ['copy trained to', 'match the answers'], 10.5, 700, 'middle')
    d.box(20, 150, 250, 100, fill=PALE[RESP])
    d.text(32, 170, 'DEFENCES', 10.5, 800, fill=RESP_D)
    for i, s in enumerate(['limit questions per user', 'give less detail in answers', 'watermark outputs', 'terms of use, and the law']):
        d.text(32, 192 + i * 16, '· ' + s, 11, 400)
    d.text(220, 278, 'It copies behaviour, not files.', 11.5, 700, 'middle', RED)
    return d


@illustration('Rd', 'One direction that means "refuse"',
              'Researchers compared a model\'s internal numbers for harmful and harmless requests and found one '
              'direction that separates them. When a request\'s numbers point along that direction, the model '
              'refuses. This was found across many open chat models in 2024.',
              'In 2024 researchers found that, across 13 open chat models, refusing harmful requests runs through '
              'a single direction in the model\'s internal numbers. It shows how much safety can hang on one small '
              'part of a model.')
def _rd():
    d = D(440, 290)
    d.head(20, 24, 'Inside the model\'s numbers')
    d.box(20, 40, 400, 190, fill=WHITE)
    d.add(f'<line x1="60" y1="200" x2="390" y2="70" stroke="{GOV_D}" stroke-width="3" marker-end="url(#ar)"/>')
    d.text(300, 74, 'refusal direction', 11.5, 800, 'end', GOV_D)
    import math
    for (x, y) in [(110, 190), (140, 205), (90, 176), (160, 186), (125, 170)]:
        d.add(f'<circle cx="{x}" cy="{y}" r="6" fill="{PALE[RESP]}" stroke="{INK}" stroke-width="1"/>')
    for (x, y) in [(300, 108), (330, 96), (280, 124), (320, 122), (350, 104)]:
        d.add(f'<circle cx="{x}" cy="{y}" r="6" fill="#F7C1C1" stroke="{INK}" stroke-width="1"/>')
    d.text(120, 220, 'harmless requests', 10.5, 700, 'middle', RESP_D)
    d.text(328, 150, 'harmful requests', 10.5, 700, 'middle', RED)
    d.box(20, 240, 400, 40, fill=YELLOW)
    d.text(220, 265, 'Points along the direction → "Sorry, I can\'t help with that."', 11, 700, 'middle')
    return d


@illustration('Ab', 'Removing a model\'s "no"',
              'Abliteration edits an open model\'s weights to remove its refusal direction. The original model '
              'refuses an unsafe request; the edited copy may answer it. The edit removes the safety behaviour, not '
              'the law: harmful or illegal content stays illegal.',
              'Abliteration builds on the refusal direction: removing it from an open model\'s weights stops the '
              'model refusing. That only removes a safety behaviour; it doesn\'t make harmful or illegal content legal.')
def _ab():
    d = D(440, 290)
    d.box(160, 40, 120, 44, fill=YELLOW)
    d.lines(220, 60, ['An unsafe', 'request'], 11, 700, 'middle')
    d.arrow(190, 86, 110, 112)
    d.arrow(250, 86, 330, 112)
    for x, title, fill, col, body, out, oc in ((20, 'ORIGINAL MODEL', PALE[RESP], RESP_D, ['refusal direction', 'in place'], '"Sorry, I can\'t help."', RESP_D),
                                               (230, 'EDITED COPY', '#FCEBEB', RED, ['refusal direction', 'removed'], 'may answer it', RED)):
        d.box(x, 114, 190, 112, fill=fill)
        d.text(x + 95, 136, title, 10.5, 800, 'middle', col)
        d.lines(x + 95, 160, body, 11, 400, 'middle', DIM)
        d.box(x + 15, 186, 160, 28)
        d.text(x + 95, 205, out, 11, 800, 'middle', oc)
    d.box(20, 238, 400, 40, fill=WHITE, dash=True)
    d.text(220, 263, 'It removes a safety behaviour, not the law: illegal stays illegal.', 11, 700, 'middle', RED)
    return d


@illustration('Uc', 'How an uncensored model differs from a jailbreak',
              'A jailbreak tricks an unchanged model with a cleverly worded prompt, and the company can patch it. An '
              'uncensored model is a downloaded copy that someone has altered, by retraining or abliteration, so the '
              'change is built into the file and nobody can patch it centrally. Illegal content is illegal from either.',
              'An uncensored model has its refusals removed from the file itself, unlike a jailbreak, which tricks an '
              'unchanged model. Because it runs on someone\'s own computer, nobody can patch it. Illegal content is '
              'still illegal, whichever tool makes it.')
def _uc():
    d = D(440, 290)
    for x, title, fill, col, rows in ((20, 'JAILBREAK', PALE[SMART], SMART_D,
                                       ['model unchanged', 'a tricky prompt fools it', 'company can patch it']),
                                      (230, 'UNCENSORED MODEL', '#FCEBEB', RED,
                                       ['the file itself altered', 'by retraining or abliteration', 'runs on someone\'s own', 'computer: no patch'])):
        d.box(x, 40, 190, 190, fill=fill)
        d.text(x + 95, 64, title, 11, 800, 'middle', col)
        for i, s in enumerate(rows):
            d.text(x + 18, 96 + i * 26, '· ' + s, 11, 400)
    d.box(20, 242, 400, 38, fill=YELLOW)
    d.text(220, 266, 'Either way, illegal content is still illegal.', 12, 800, 'middle')
    return d


# ---- Predictive & Analytical AI -----------------------------------------------------------------------------------

def dot(d, x, y, fill, r=5):
    d.add(f'<circle cx="{x}" cy="{y}" r="{r}" fill="{fill}" stroke="{INK}" stroke-width="1"/>')


def axes(d, x, y, w, h, xl='', yl=''):
    d.add(f'<path d="M{x} {y} V{y + h} H{x + w}" fill="none" stroke="{INK}" stroke-width="1.4"/>')
    if xl:
        d.text(x + w, y + h + 14, xl, 9.5, 400, 'end', DIM)
    if yl:
        d.text(x - 4, y - 4, yl, 9.5, 400, 'start', DIM)


@illustration('Su', 'Learn from answers, then test on new ones',
              'In supervised learning, labelled examples are split: most are used to train the model, and some are '
              'kept back. The trained model is tested on the kept-back examples it has never seen, and its score '
              'there shows how well it will do on new data.',
              'Supervised learning trains on examples that come with the right answers. The honest test is on '
              'examples kept back from training: a model can score well on what it has seen and badly on what it '
              'hasn\'t. Made-up numbers.')
def _su():
    d = D(440, 290)
    d.head(20, 24, '1,000 labelled photos')
    d.add(f'<rect x="20" y="40" width="320" height="34" fill="{PALE[SMART]}" stroke="{INK}" stroke-width="1.5"/>')
    d.add(f'<rect x="340" y="40" width="80" height="34" fill="{PALE[SAFE]}" stroke="{INK}" stroke-width="1.5"/>')
    d.text(180, 62, '800 to train on', 12, 800, 'middle')
    d.text(380, 62, '200 kept back', 10.5, 800, 'middle')
    d.arrow(180, 76, 180, 104)
    d.box(110, 106, 140, 56, fill=YELLOW)
    d.lines(180, 128, ['learns from photos', 'and their answers'], 11, 700, 'middle')
    d.arrow(252, 134, 300, 134)
    d.arrow(380, 76, 380, 104)
    d.box(302, 106, 118, 56, fill=PALE[SAFE])
    d.lines(361, 128, ['tested on photos', 'it never saw'], 11, 700, 'middle')
    rows = [('Score on training photos', '99%', DIM), ('Score on kept-back photos', '92%', RESP_D)]
    for i, (k, v, c) in enumerate(rows):
        y = 190 + i * 32
        d.box(20, y, 400, 26)
        d.text(32, y + 18, k, 11.5, 700)
        d.text(408, y + 18, v, 13, 800, 'end', c)
    d.text(220, 282, 'The kept-back score is the one to trust.', 12, 700, 'middle', SAFE_D)
    return d


@illustration('Un', 'Finding groups no one named',
              'Songs are plotted by tempo and energy, with no labels. Unsupervised learning finds three groups by '
              'itself. It does not know what they mean: a person looks at each group and names it, such as calm, '
              'pop and workout.',
              'Unsupervised learning finds structure in data with no answers attached. It can find the groups, but '
              'people still decide what each group means, and whether it is useful.')
def _un():
    d = D(440, 290)
    d.head(20, 24, 'Songs, no labels')
    axes(d, 40, 44, 230, 190, 'energy →', 'tempo ↑')
    import random
    rnd = random.Random(4)
    groups = [(90, 190, SAFE), (160, 110, SMART), (230, 70, CREATIVE)]
    for gx, gy, c in groups:
        for _ in range(7):
            dot(d, gx + rnd.randint(-22, 22), gy + rnd.randint(-18, 18), PALE[c])
        d.add(f'<ellipse cx="{gx}" cy="{gy}" rx="34" ry="28" fill="none" stroke="{c}" stroke-width="2" stroke-dasharray="5 4"/>')
    d.box(290, 44, 130, 190)
    d.text(355, 64, 'A PERSON NAMES THEM', 9, 800, 'middle')
    for i, (nm, c) in enumerate([('workout', CREATIVE), ('pop', SMART), ('calm', SAFE)]):
        y = 84 + i * 46
        d.box(304, y, 102, 32, fill=PALE[c])
        d.text(355, y + 21, nm, 12, 800, 'middle')
    d.text(220, 270, 'The AI finds the groups; people decide what they mean.', 11.5, 700, 'middle', SMART_D)
    return d


@illustration('Rr', 'Choosing the line with the smallest errors',
              'Points show hours revised and test scores. Regression draws the line that makes the gaps between the '
              'points and the line, the errors, as small as possible overall. The line then predicts a score for '
              'new hours, with a range rather than one exact number.',
              'Regression finds the line (or curve) that keeps the errors, the gaps between real points and the '
              'line, as small as possible overall. Its predictions are best read as a likely range. Made-up numbers.')
def _rr():
    d = D(440, 290)
    d.head(20, 24, 'Hours revised and test score')
    axes(d, 40, 44, 360, 190, 'hours revised →', 'score ↑')
    pts = [(70, 200), (110, 180), (140, 192), (180, 150), (220, 160), (250, 120), (290, 128), (330, 96)]
    def line(x):
        return 214 - (x - 40) * 0.36
    d.add(f'<polygon points="40,{line(40) - 18} 400,{line(400) - 18} 400,{line(400) + 18} 40,{line(40) + 18}" fill="{PALE[SAFE]}" opacity=".7"/>')
    for x, y in pts:
        d.add(f'<line x1="{x}" y1="{y}" x2="{x}" y2="{line(x):.1f}" stroke="{RED}" stroke-width="1.5" stroke-dasharray="3 2"/>')
        dot(d, x, y, PALE[SMART])
    d.add(f'<line x1="40" y1="{line(40)}" x2="400" y2="{line(400):.1f}" stroke="{SAFE_D}" stroke-width="2.5"/>')
    d.add(f'<circle cx="370" cy="{line(370):.1f}" r="7" fill="{YELLOW}" stroke="{INK}" stroke-width="1.5"/>')
    d.text(362, line(370) - 26, 'prediction', 10.5, 800, 'end')
    d.text(60, 60, 'red dashes = errors', 10.5, 700, fill=RED)
    d.text(60, 76, 'blue band = likely range', 10.5, 700, fill=SAFE_D)
    d.text(220, 272, 'Training picks the line with the smallest total error.', 11.5, 700, 'middle')
    return d


@illustration('Cl', 'Where to draw the line',
              'A spam classifier gives each email a score from 0 to 1. A threshold decides which count as spam. A low '
              'threshold catches more spam but blocks some real emails; a high threshold lets more spam through but '
              'blocks fewer real ones. Choosing it depends on which mistake costs more.',
              'Most classifiers give a score, and a threshold turns it into a decision. Moving the threshold trades '
              'one mistake for another, so the right setting depends on which mistake matters more. Made-up example.')
def _cl():
    d = D(440, 290)
    d.head(20, 24, 'Spam score for each email')
    d.add(f'<line x1="30" y1="120" x2="410" y2="120" stroke="{INK}" stroke-width="1.5"/>')
    for v, lab in ((0, '0'), (0.5, '0.5'), (1, '1')):
        x = 30 + v * 380
        d.add(f'<line x1="{x}" y1="116" x2="{x}" y2="124" stroke="{INK}" stroke-width="1.5"/>')
        d.text(x, 138, lab, 10, 400, 'middle', DIM)
    emails = [(0.08, 0), (0.18, 0), (0.3, 0), (0.42, 1), (0.55, 0), (0.66, 1), (0.78, 1), (0.9, 1), (0.96, 1)]
    for v, spam in emails:
        x = 30 + v * 380
        d.add(f'<rect x="{x - 9}" y="{88 if spam else 92}" width="18" height="{20 if spam else 16}" fill="{"#F7C1C1" if spam else PALE[RESP]}" stroke="{INK}" stroke-width="1"/>')
    d.text(30, 64, 'red = really spam', 10.5, 700, fill=RED)
    d.text(150, 64, 'green = real email', 10.5, 700, fill=RESP_D)
    for x, lab, col in ((30 + 0.4 * 380, 'low threshold', SMART_D), (30 + 0.85 * 380, 'high threshold', SAFE_D)):
        d.add(f'<line x1="{x}" y1="80" x2="{x}" y2="150" stroke="{col}" stroke-width="2.5" stroke-dasharray="5 3"/>')
        d.text(x, 164, lab, 10.5, 800, 'middle', col)
    d.box(20, 180, 196, 90, fill=PALE[SMART])
    d.text(118, 200, 'LOW THRESHOLD', 10.5, 800, 'middle', SMART_D)
    d.lines(118, 222, ['catches all the spam', 'but blocks a real email'], 11, 400, 'middle')
    d.box(224, 180, 196, 90, fill=PALE[SAFE])
    d.text(322, 200, 'HIGH THRESHOLD', 10.5, 800, 'middle', SAFE_D)
    d.lines(322, 222, ['blocks no real email', 'but lets spam through'], 11, 400, 'middle')
    return d


@illustration('Cu', 'How k-means finds groups',
              'One common clustering method, k-means, works in steps. Drop three centres at random. Give every point '
              'to its nearest centre. Move each centre to the middle of its points. Repeat until the centres stop '
              'moving: the groups are found.',
              'K-means, one common clustering method, repeats two steps, assign each point to its nearest centre, '
              'then move each centre to the middle of its points, until nothing changes. You choose how many groups '
              'it looks for.')
def _cu():
    d = D(440, 280)
    pts = [(30, 40), (42, 55), (25, 60), (38, 70), (95, 30), (110, 42), (100, 55), (118, 35), (70, 95), (82, 105), (65, 110), (90, 92)]
    near = {}
    panels = [('1 · DROP 3 CENTRES', [(55, 30), (60, 70), (110, 100)], False),
              ('2 · NEAREST CENTRE', [(55, 30), (60, 70), (110, 100)], True),
              ('3 · MOVE TO MIDDLE', None, True)]
    cols = [SAFE, SMART, CREATIVE]
    start = panels[0][1]
    group = [min(range(3), key=lambda i: (x - start[i][0]) ** 2 + (y - start[i][1]) ** 2) for x, y in pts]
    moved = []
    for i in range(3):
        mine = [pt for pt, g in zip(pts, group) if g == i]
        moved.append((sum(x for x, _ in mine) / len(mine), sum(y for _, y in mine) / len(mine)))
    for p, (title, cents, coloured) in enumerate(panels):
        cents = cents or moved
        ox = 20 + p * 138
        d.box(ox, 40, 128, 170)
        d.text(ox + 64, 60, title, 9, 800, 'middle')
        for j, (x, y) in enumerate(pts):
            k = group[j]
            dot(d, ox + x, 70 + y, PALE[cols[k]] if coloured else WHITE, 4.5)
        for i, (x, y) in enumerate(cents):
            cx, cy = ox + x, 70 + y
            d.add(f'<path d="M{cx - 7} {cy - 7}l14 14M{cx + 7} {cy - 7}l-14 14" stroke="{cols[i]}" stroke-width="3.5"/>')
        if p < 2:
            d.arrow(ox + 129, 125, ox + 137, 125)
    d.arrow(304, 214, 90, 214, dash=True, curve=-26)
    d.text(220, 250, 'repeat 2 and 3 until the centres stop moving', 11, 700, 'middle', DIM)
    d.text(220, 272, 'You choose how many groups to look for.', 11.5, 700, 'middle', SMART_D)
    return d


@illustration('Dt', 'How a tree picks its questions',
              'Ten days: some needed a coat, some did not. To choose its first question, a decision tree tries each '
              'one and keeps the question that splits the days into the purest groups. "Raining?" gives two mixed '
              'groups; "Below 10°C?" sorts them almost perfectly, so the tree asks that first.',
              'A decision tree is built by trying possible questions and keeping the one that best separates the '
              'answers into pure groups, then repeating inside each branch. Made-up data.')
def _dt():
    d = D(440, 290)
    d.head(20, 24, 'Coat or no coat? 10 days')
    def tiles(x, y, coats, nocoats):
        for i in range(coats):
            d.add(f'<rect x="{x + i * 13}" y="{y}" width="10" height="14" fill="{SAFE}" stroke="{INK}" stroke-width=".8"/>')
        for i in range(nocoats):
            d.add(f'<rect x="{x + (coats + i) * 13}" y="{y}" width="10" height="14" fill="{PALE[SMART]}" stroke="{INK}" stroke-width=".8"/>')
    for col, (q, left, right, verdict, good) in enumerate([('"Raining?"', (3, 2), (2, 3), 'mixed groups', False),
                                                           ('"Below 10°C?"', (5, 0), (0, 5), 'pure groups', True)]):
        x = 20 + col * 206
        d.box(x, 40, 194, 196, fill=PALE[RESP] if good else WHITE)
        d.text(x + 97, 62, 'TRY ' + q.upper(), 10.5, 800, 'middle')
        d.text(x + 46, 96, 'yes', 10.5, 700, 'middle', DIM)
        d.text(x + 146, 96, 'no', 10.5, 700, 'middle', DIM)
        tiles(x + 14, 106, *left)
        tiles(x + 114, 106, *right)
        d.tick(x + 97, 170, good)
        d.text(x + 97, 200, verdict, 12, 800, 'middle', RESP_D if good else RED)
        d.text(x + 97, 220, 'ask this first' if good else 'try another', 10.5, 400, 'middle', DIM)
    d.text(20, 258, 'blue = needed a coat', 10.5, 700, fill=SAFE_D)
    d.text(220, 278, 'Then the tree repeats the search inside each branch.', 11.5, 700, 'middle')
    return d


@illustration('Rf', 'Why a forest of trees differs',
              'A random forest trains each tree on a different random sample of the data, and lets each tree look '
              'at a random choice of questions. The trees end up different, so their mistakes differ too, and when '
              'they vote the mistakes tend to cancel out.',
              'A random forest gives each tree a different random sample of the rows and of the questions it may ask. '
              'Different trees make different mistakes, so the vote is steadier than any one tree.')
def _rf():
    d = D(440, 290)
    d.box(150, 36, 140, 40, fill=PALE[SAFE])
    d.text(220, 61, 'All the data', 12, 800, 'middle')
    for i, (rows, q, vote) in enumerate([('rows 1, 3, 3, 7…', 'may ask: rain, wind', 'Yes'),
                                         ('rows 2, 5, 6, 6…', 'may ask: cold, wind', 'Yes'),
                                         ('rows 1, 4, 8, 9…', 'may ask: rain, cold', 'No')]):
        x = 20 + i * 140
        d.arrow(220, 78, x + 60, 100)
        d.box(x, 102, 120, 110)
        d.text(x + 60, 122, 'RANDOM SAMPLE', 9, 800, 'middle', DIM)
        d.text(x + 60, 140, rows, 10.5, 700, 'middle')
        d.text(x + 60, 158, q, 10, 400, 'middle', DIM)
        d.add(f'<path d="M{x + 60} 170 l-14 18 h28 z" fill="{RESP}" stroke="{INK}" stroke-width="1"/><rect x="{x + 57}" y="188" width="6" height="8" fill="{SMART_D}"/>')
        d.text(x + 60, 208, 'votes ' + vote, 10.5, 800, 'middle', RESP_D if vote == 'Yes' else RED)
    d.box(100, 230, 240, 44, fill=YELLOW)
    d.lines(220, 248, ['Vote: Yes, 2 to 1', 'different trees, different mistakes'], 11, 700, 'middle', INK, 15)
    return d


@illustration('Gb', 'Each model learns what is left over',
              'Gradient boosting starts with a rough guess for every example, works out how far off each guess is, '
              'and trains a small model to predict those leftover errors. Adding its corrections shrinks the error; '
              'the next model learns what is left over again. Small steps each round help avoid overfitting.',
              'Gradient boosting adds small models one at a time, each trained on the errors still left. It is one '
              'of the most accurate methods for tables of data. Made-up numbers.')
def _gb():
    d = D(440, 290)
    d.head(20, 24, 'Total error after each model')
    errs = [100, 55, 32, 20, 13, 9]
    for i, e in enumerate(errs):
        x = 40 + i * 62
        h = e * 1.5
        d.bar(x, 200 - h, 40, h, SMART if i == 0 else PALE[SMART])
        d.text(x + 20, 216, 'start' if i == 0 else f'+{i}', 10.5, 700, 'middle')
        d.text(x + 20, 194 - h, str(e), 10.5, 800, 'middle')
    d.add(f'<line x1="30" y1="200" x2="410" y2="200" stroke="{INK}" stroke-width="1.4"/>')
    d.text(220, 236, 'models added →', 10.5, 400, 'middle', DIM)
    d.box(20, 248, 400, 34, fill=PALE[SAFE])
    d.text(220, 270, 'Each new model is trained only on the errors still left.', 11.5, 700, 'middle')
    return d


@illustration('Fe', 'Three ways to make better features',
              'Feature engineering turns raw data into inputs a model can use. Combine: 36 books in 3 years becomes '
              '12 books a year. Transform: a date of birth becomes an age. Encode: a favourite genre becomes columns '
              'of 1s and 0s, because models work with numbers.',
              'Models only see numbers, and they learn faster from numbers that capture what matters. Combining, '
              'transforming and encoding raw data are three common ways to make better features. Made-up example.')
def _fe():
    d = D(440, 290)
    rows = [('COMBINE', '36 books, 3 years', '12 books a year', SAFE),
            ('TRANSFORM', 'born 14 May 2012', 'age 14', SMART),
            ('ENCODE', 'favourite: fantasy', 'fantasy 1 · crime 0 · sport 0', CREATIVE)]
    d.text(105, 30, 'RAW DATA', 10.5, 800, 'middle', DIM)
    d.text(330, 30, 'FEATURE FOR THE MODEL', 10.5, 800, 'middle', DIM)
    for i, (k, raw, feat, c) in enumerate(rows):
        y = 42 + i * 74
        d.text(20, y + 12, k, 10.5, 800, fill=c if c != SAFE else SAFE_D)
        d.box(20, y + 20, 170, 40)
        d.text(105, y + 45, raw, 11.5, 700, 'middle')
        d.arrow(192, y + 40, 228, y + 40)
        d.box(230, y + 20, 190, 40, fill=PALE[c])
        d.text(325, y + 45, feat, 11.5, 800, 'middle')
    d.text(220, 278, 'Models only see numbers: good features make the pattern easy to find.', 11, 700, 'middle', SMART_D)
    return d


@illustration('Of', 'The sweet spot between too simple and too complex',
              'As a model gets more complex, its error on training data keeps falling. Its error on new data falls '
              'at first, then rises once it starts memorising noise: that is overfitting. The best model sits at the '
              'lowest point of the new-data curve. More data, simpler models and stopping early all help.',
              'Overfitting shows up as a gap: training error keeps falling while error on new data starts rising. '
              'Checking on kept-back data shows where to stop.')
def _of():
    d = D(440, 290)
    axes(d, 40, 30, 370, 190, '', 'error ↑')
    d.text(220, 236, 'model complexity →', 10.5, 400, 'middle', DIM)
    d.add(f'<path d="M50 66 C120 160 200 195 400 208" fill="none" stroke="{SAFE_D}" stroke-width="3"/>')
    d.add(f'<path d="M50 56 C120 135 170 158 210 156 S320 110 400 60" fill="none" stroke="{RED}" stroke-width="3"/>')
    d.text(392, 196, 'training data', 11, 800, 'end', SAFE_D)
    d.text(392, 54, 'new data', 11, 800, 'end', RED)
    d.add(f'<line x1="205" y1="52" x2="205" y2="220" stroke="{RESP_D}" stroke-width="2" stroke-dasharray="5 4"/>')
    d.text(205, 44, 'sweet spot', 11, 800, 'middle', RESP_D)
    d.text(100, 214, 'too simple', 10.5, 700, 'middle', DIM)
    d.text(330, 140, 'overfitting:', 10.5, 700, 'middle', RED)
    d.text(330, 154, 'memorising noise', 10.5, 700, 'middle', RED)
    d.box(20, 248, 400, 34, fill=PALE[RESP])
    d.text(220, 270, 'Helps: more data · a simpler model · stopping training early', 11.5, 700, 'middle')
    return d


@illustration('Ad', 'Learn what is normal, flag what is far from it',
              'Anomaly detection first learns what normal looks like, such as usual payment amounts, times and '
              'places. Each new event gets a score for how far it is from normal. Events past a threshold are '
              'flagged for a person to check, because some unusual events are innocent.',
              'Anomaly detection learns a picture of normal and scores how far each new event is from it. Flags are '
              'for a person to check: unusual isn\'t always wrong. Made-up example.')
def _ad():
    d = D(440, 290)
    d.head(20, 24, 'Card payments: how unusual?')
    axes(d, 40, 40, 370, 160, 'how unusual →', '')
    d.add(f'<path d="M50 190 C90 190 100 70 140 70 S190 190 230 192" fill="{PALE[SAFE]}" stroke="{SAFE_D}" stroke-width="2"/>')
    d.text(140, 62, 'normal: £2–£10, daytime, UK', 10.5, 700, 'middle', SAFE_D)
    d.add(f'<line x1="280" y1="50" x2="280" y2="200" stroke="{SMART_D}" stroke-width="2" stroke-dasharray="5 4"/>')
    d.text(284, 60, 'threshold', 10.5, 800, 'start', SMART_D)
    for x in (390, 320):
        d.add(f'<circle cx="{x}" cy="180" r="7" fill="#F7C1C1" stroke="{INK}" stroke-width="1.2"/>')
    d.text(294, 100, '£950, 3am, abroad', 10, 700, 'start', RED)
    d.text(294, 138, '£40 trip', 10, 700, 'start', DIM)
    d.add(f'<line x1="380" y1="104" x2="390" y2="172" stroke="{RED}" stroke-width="1"/>')
    d.add(f'<line x1="312" y1="142" x2="320" y2="172" stroke="{DIM}" stroke-width="1"/>')
    d.box(20, 226, 400, 54, fill=YELLOW)
    d.lines(220, 248, ['Flagged for a person to check: the £40 school trip', 'was unusual but fine. Unusual isn\'t always wrong.'], 11, 700, 'middle', INK, 16)
    return d


@illustration('Fc', 'A forecast is a range, not a point',
              'Past weekly lunch numbers are drawn as a line. The forecast continues the line, with a shaded band '
              'showing the likely range, which gets wider the further ahead it looks, because uncertainty grows.',
              'Good forecasts come with a range. The further ahead they look, the wider it gets: next week is easier '
              'to predict than next term. Made-up numbers.')
def _fc():
    d = D(440, 280)
    d.head(20, 24, 'Lunches sold each week')
    axes(d, 40, 40, 370, 180, 'weeks →', '')
    past = [(50, 150), (80, 140), (110, 156), (140, 132), (170, 144), (200, 126), (230, 138)]
    d.add('<polyline points="' + ' '.join(f'{x},{y}' for x, y in past) + f'" fill="none" stroke="{INK}" stroke-width="2.5"/>')
    d.add(f'<polygon points="230,138 400,{120 - 48} 400,{120 + 48}" fill="{PALE[FUTURE]}" stroke="none"/>')
    d.add(f'<line x1="230" y1="138" x2="400" y2="120" stroke="{FUTURE_D}" stroke-width="2.5" stroke-dasharray="6 4"/>')
    d.add(f'<line x1="230" y1="48" x2="230" y2="220" stroke="{DIM}" stroke-width="1" stroke-dasharray="3 3"/>')
    d.text(226, 58, 'past', 10.5, 700, 'end', DIM)
    d.text(236, 58, 'forecast', 10.5, 700, 'start', FUTURE_D)
    d.text(396, 66, 'wider further ahead', 10.5, 800, 'end', FUTURE_D)
    d.text(220, 252, 'Plan for the range, not just the middle line.', 12, 700, 'middle', FUTURE_D)
    return d


@illustration('Tm', 'Trend, season and the rest',
              'A time series of ice cream sales can be split into three parts: a slow trend upwards, a seasonal '
              'pattern that repeats every summer, and leftover noise. Spotting each part separately helps explain '
              'the past and forecast the future.',
              'Time-series methods often split data into a trend, a repeating season and leftover noise. A jump that '
              'is just "summer again" isn\'t news; a change in the trend might be. Made-up data.')
def _tm():
    d = D(440, 320)
    import math
    rows = [('SALES', lambda t: 0.25 * t + 14 * math.sin(t / 8) + (6 if int(t) % 11 == 0 else 0), INK),
            ('TREND', lambda t: 0.25 * t, SAFE_D), ('SEASON', lambda t: 14 * math.sin(t / 8), SMART_D),
            ('NOISE', lambda t: (6 if int(t) % 11 == 0 else 0) - (4 if int(t) % 7 == 0 else 0), DIM)]
    for i, (k, f, c) in enumerate(rows):
        y0 = 40 + i * 64
        d.text(20, y0 + 20, k, 10.5, 800, fill=c)
        d.add(f'<rect x="90" y="{y0}" width="330" height="52" fill="{WHITE}" stroke="{INK}" stroke-width="1"/>')
        pts = []
        for t in range(0, 101):
            v = f(t)
            pts.append(f'{90 + t * 3.3:.1f},{y0 + 40 - v * 0.9:.1f}' if k != 'NOISE' else f'{90 + t * 3.3:.1f},{y0 + 26 - v * 1.5:.1f}')
        d.add(f'<polyline points="{" ".join(pts)}" fill="none" stroke="{c}" stroke-width="2"/>')
        if i:
            d.text(80, y0 + 32, '+' if i > 1 else '=', 14, 800, 'end', DIM)
    d.text(220, 308, 'Sales = trend + season + noise', 12, 800, 'middle')
    return d


@illustration('Pa', 'From what happened to what will happen',
              'Analytics climbs four steps. Descriptive: what happened? Diagnostic: why did it happen? Predictive: '
              'what is likely to happen? Prescriptive: what should we do? Predictive analytics is the third step: '
              'it gives a likelihood, such as about 240 lunches next Friday, give or take 15.',
              'Predictive analytics turns past data into a likely future, stated with how sure it is. It is the third '
              'of four common analytics steps, before deciding what to do. Made-up numbers.')
def _pa():
    d = D(440, 320)
    steps = [('DESCRIPTIVE', 'What happened?', '238 lunches last Friday', SAFE),
             ('DIAGNOSTIC', 'Why?', 'it was pizza day', CREATIVE),
             ('PREDICTIVE', 'What\'s likely?', 'about 240 next Friday, ±15', FUTURE),
             ('PRESCRIPTIVE', 'What should we do?', 'order for 255 to be safe', RESP)]
    for i, (k, q, ex, c) in enumerate(steps):
        h = 96 + i * 44
        x, y = 20 + i * 100, 276 - h
        d.box(x, y, 96, h, fill=PALE[c] if i != 2 else YELLOW, sw=2.5 if i == 2 else 1.5)
        d.text(x + 48, y + 18, k, 9, 800, 'middle')
        qs = _wrap(q, 12)
        d.lines(x + 48, y + 36, qs, 10, 700, 'middle', INK, 13)
        d.lines(x + 48, y + 42 + 13 * len(qs), _wrap(ex, 14), 9.5, 400, 'middle', DIM, 12)
    d.text(20, 30, 'FOUR STEPS OF ANALYTICS', 13, 800)
    d.text(220, 304, 'Predictive analytics gives a likelihood, not a certainty.', 11.5, 700, 'middle', FUTURE_D)
    return d


@illustration('Ps', 'Compare the options, then recommend one',
              'Prescriptive analytics takes a forecast, such as rain likely at 2pm on sports day, and tests each '
              'option against it and the constraints: start at 10am, move indoors, or postpone. It scores each one '
              'and recommends the best, leaving the final decision to people.',
              'Prescriptive analytics goes one step past prediction: it tests the options against the forecast and '
              'the constraints, and recommends one. People still make the call. Made-up example.')
def _ps():
    d = D(440, 290)
    d.box(20, 36, 400, 36, fill=PALE[FUTURE])
    d.text(220, 59, 'Forecast: 80% chance of rain from 2pm on sports day', 12, 800, 'middle')
    opts = [('Start at 10am', 'done by 1:30', 'all events', True), ('Move indoors', 'hall too small', 'half the events', False),
            ('Postpone a week', 'clashes with exams', 'all events, later', False)]
    for i, (o, con, ev, best) in enumerate(opts):
        y = 84 + i * 56
        d.box(20, y, 400, 48, fill=PALE[RESP] if best else WHITE, sw=2.5 if best else 1.5)
        d.text(34, y + 20, o, 12, 800)
        d.text(34, y + 38, con, 10.5, 400, fill=DIM)
        d.text(300, y + 28, ev, 10.5, 700, 'middle')
        d.tick(398, y + 24, best)
    d.text(220, 270, 'Recommended: start at 10am. A person still decides.', 12, 700, 'middle', RESP_D)
    return d


@illustration('Rc', 'Two ways to recommend',
              'Recommendation engines mostly mix two ideas. Content-based: suggest things similar to what you liked, '
              'such as more football clips. Collaborative: suggest what people with similar tastes liked, such as a '
              'cooking channel. Each click feeds back in, which can narrow what you are shown.',
              'Recommenders suggest things like what you liked, and things people like you liked. Every click feeds '
              'back in, so a feed can narrow over time unless it mixes in something new.')
def _rc():
    d = D(440, 290)
    d.box(20, 40, 196, 170, fill=PALE[SAFE])
    d.text(118, 60, 'LIKE WHAT YOU LIKED', 10.5, 800, 'middle', SAFE_D)
    d.text(118, 78, 'content-based', 10.5, 400, 'middle', DIM)
    d.lines(118, 104, ['you watched', 'football clips'], 11, 700, 'middle')
    d.arrow(118, 124, 118, 150)
    d.box(48, 152, 140, 40, fill=WHITE)
    d.text(118, 177, 'more football', 12, 800, 'middle')
    d.box(224, 40, 196, 170, fill=PALE[CREATIVE])
    d.text(322, 60, 'LIKED BY PEOPLE LIKE YOU', 10.5, 800, 'middle', CREATIVE_D)
    d.text(322, 78, 'collaborative', 10.5, 400, 'middle', DIM)
    d.lines(322, 104, ['others who watch football', 'and music also watch…'], 10.5, 700, 'middle')
    d.arrow(322, 124, 322, 150)
    d.box(252, 152, 140, 40, fill=WHITE)
    d.text(322, 177, 'a cooking channel', 12, 800, 'middle')
    d.box(20, 224, 400, 54, fill=YELLOW)
    d.lines(220, 246, ['Every click feeds back in, which can narrow your feed', 'unless the system mixes in something new.'], 11, 700, 'middle', INK, 16)
    return d


@illustration('Ch', 'From a risk score to an action',
              'A churn model scores each subscriber\'s chance of leaving. Those above a threshold get an action, such '
              'as a reminder or an offer. Afterwards, the business checks whether the action really kept people, '
              'compared with similar people who got nothing, because some would have stayed anyway.',
              'Churn prediction is only useful if it leads to action, and if that action is tested: compared with '
              'similar customers who got nothing, did it change anything? Made-up numbers.')
def _ch():
    d = D(440, 290)
    subs = [('Amir', 12), ('Bea', 34), ('Chloe', 85), ('Dev', 71)]
    d.head(20, 24, 'Chance of leaving')
    for i, (n, p) in enumerate(subs):
        y = 40 + i * 34
        d.text(20, y + 18, n, 11.5, 700)
        d.add(f'<rect x="80" y="{y + 6}" width="200" height="16" fill="{WHITE}" stroke="{INK}" stroke-width="1"/>')
        d.add(f'<rect x="80" y="{y + 6}" width="{p * 2}" height="16" fill="{SMART if p > 60 else PALE[SMART]}" stroke="{INK}" stroke-width="1"/>')
        d.text(288, y + 19, f'{p}%', 11, 800)
    d.add(f'<line x1="200" y1="36" x2="200" y2="176" stroke="{SMART_D}" stroke-width="2" stroke-dasharray="5 4"/>')
    d.text(200, 188, 'threshold 60%', 10, 800, 'middle', SMART_D)
    d.box(330, 64, 90, 84, fill=PALE[RESP])
    d.lines(375, 92, ['Chloe, Dev:', 'send an', 'offer'], 11, 700, 'middle')
    d.arrow(316, 106, 328, 106)
    d.box(20, 206, 400, 72, fill=YELLOW)
    d.text(32, 226, 'THEN TEST IT', 10.5, 800)
    d.lines(32, 246, ['Compare with similar people who got no offer:', 'did the offer keep anyone who would have left?'], 11, 400, gap=16)
    return d


@illustration('Sa', 'Why tone is hard to read',
              'Simple sentiment analysis counts positive and negative words, which goes wrong with sarcasm ("Great, '
              'another Monday"), negation ("not bad") and mixed reviews ("food great, service slow"). Better systems '
              'read the whole sentence, and can score each topic separately.',
              'Sentiment analysis works well on clear reviews and struggles with sarcasm, negation and mixed '
              'opinions. Scoring each topic separately, such as food and service, gives a truer picture.')
def _sa():
    d = D(440, 290)
    d.text(160, 30, 'WORD-COUNTING SAYS', 10, 800, 'middle', DIM)
    d.text(340, 30, 'REALLY', 10, 800, 'middle', DIM)
    rows = [('SARCASM', '"Great, another Monday."', ('positive', RESP_D), ('negative', RED)),
            ('NEGATION', '"Not bad at all."', ('negative', RED), ('positive', RESP_D)),
            ('MIXED', '"Food great, service slow."', ('positive', RESP_D), ('food + / service −', SMART_D))]
    for i, (k, quote, said, real) in enumerate(rows):
        y = 42 + i * 66
        d.box(20, y, 400, 58)
        d.text(32, y + 20, k, 10, 800, fill=DIM)
        d.text(32, y + 42, quote, 12, 700)
        d.text(260, y + 42, said[0], 11, 700, 'middle', said[1])
        d.text(360, y + 42, real[0], 11, 800, 'middle', real[1])
    d.text(220, 254, 'Reading the whole sentence, and scoring each topic,', 11.5, 700, 'middle', SMART_D)
    d.text(220, 272, 'gets much closer to what people mean.', 11.5, 700, 'middle', SMART_D)
    return d


@illustration('Np', 'From words to an action',
              'A voice assistant turns "Set a timer for ten minutes" into an action in steps: speech becomes text, '
              'the text is split into tokens, the system finds the intent (set a timer) and the details (10 minutes), '
              'then the app acts. Modern large language models do several of these steps at once.',
              'Language processing turns messy human words into something a computer can act on: what you want '
              '(the intent) and the details. Today\'s large language models handle several of these steps in one go.')
def _np():
    d = D(440, 290)
    d.box(20, 36, 400, 40, fill=YELLOW)
    d.text(220, 61, '"Set a timer for ten minutes"', 14, 800, 'middle')
    stages = [('SPEECH → TEXT', 'set a timer for ten minutes', SAFE),
              ('TOKENS', 'set · a · timer · for · ten · minutes', CREATIVE),
              ('INTENT', 'start a timer', SMART),
              ('DETAILS', 'length = 10 minutes', RESP)]
    for i, (k, v, c) in enumerate(stages):
        y = 90 + i * 40
        d.box(20, y, 400, 32, fill=PALE[c])
        d.text(32, y + 21, k, 10, 800)
        d.text(150, y + 21, v, 11.5, 700)
    d.text(220, 268, 'Large language models now do several steps in one go.', 11, 700, 'middle', SMART_D)
    return d


@illustration('Cv', 'What a computer sees in a picture',
              'To a computer an image is a grid of numbers, one per pixel. A vision model passes it through layers: '
              'early ones pick out edges, later ones combine them into shapes, and the last ones recognise objects, '
              'here a crack in a bottle.',
              'Computer vision starts from numbers, one per pixel, and builds up from edges to shapes to objects. '
              'Like any model, it can be fooled by things it rarely saw in training, such as unusual lighting.')
def _cv():
    d = D(440, 280)
    vals = [[12, 14, 200, 13], [11, 210, 205, 12], [14, 198, 15, 10], [13, 12, 11, 14]]
    d.text(70, 30, 'PIXELS = NUMBERS', 9.5, 800, 'middle')
    for r, row in enumerate(vals):
        for c, v in enumerate(row):
            fill = '#888' if v > 100 else '#eee'
            d.add(f'<rect x="{20 + c * 25}" y="{40 + r * 25}" width="25" height="25" fill="{fill}" stroke="{WHITE}" stroke-width="1"/>')
            d.text(32.5 + c * 25, 56 + r * 25, str(v), 8.5, 700, 'middle', WHITE if v > 100 else INK)
    stages = [('EDGES', SAFE), ('SHAPES', CREATIVE), ('OBJECTS', SMART)]
    for i, (k, c) in enumerate(stages):
        x = 140 + i * 92
        d.arrow(x - 16, 90, x - 4, 90)
        d.box(x, 50, 80, 80, fill=PALE[c])
        d.text(x + 40, 70, k, 10, 800, 'middle')
        if i == 0:
            d.add(f'<path d="M{x + 16} 110 l16 -24 M{x + 40} 112 l12 -26 M{x + 56} 92 h14" stroke="{INK}" stroke-width="2"/>')
        elif i == 1:
            d.add(f'<path d="M{x + 22} 118 v-28 a18 18 0 0 1 36 0 v28 z" fill="none" stroke="{INK}" stroke-width="2"/>')
        else:
            d.add(f'<path d="M{x + 30} 120 v-30 l6 -10 h8 l6 10 v30 z" fill="{WHITE}" stroke="{INK}" stroke-width="1.5"/><path d="M{x + 38} 96 l4 8 l-4 6 l3 8" stroke="{RED}" stroke-width="2" fill="none"/>')
    d.box(140, 150, 264, 40, fill=YELLOW)
    d.text(272, 175, 'Answer: bottle, crack found', 12, 800, 'middle')
    d.box(20, 210, 400, 52, fill=WHITE, dash=True)
    d.lines(220, 230, ['Early layers see edges; later layers see objects.', 'Unusual lighting or angles can fool it.'], 11, 700, 'middle', INK, 16)
    return d


@illustration('Ge', 'How an AI answer chooses its sources',
              'When you ask an AI search tool a question, it searches the web, reads some pages and writes an '
              'answer that cites a few of them. Generative engine optimisation tries to make a page more likely to '
              'be cited, for example with clear answers, sources and statistics. In one 2024 study this raised '
              'visibility by up to about 40%.',
              'Generative engine optimisation tries to get pages cited in AI answers. One study (Aggarwal and others, '
              '2024) found adding citations, quotations and statistics helped most, but results vary and added '
              'numbers still need to be true.')
def _ge():
    d = D(440, 290)
    steps = [('YOUR QUESTION', ['"best bike lights', 'for winter?"'], YELLOW),
             ('AI SEARCHES', ['and reads', 'some pages'], PALE[SAFE]),
             ('WRITES AN ANSWER', ['citing a', 'few of them'], PALE[CREATIVE])]
    steps_row(d, 36, steps, h=70)
    d.box(20, 124, 400, 104)
    d.text(32, 144, 'WHAT HELPED A PAGE GET CITED, IN ONE STUDY', 10, 800, fill=SMART_D)
    for i, s in enumerate(['quotations from trusted sources', 'citing its own sources', 'relevant statistics', 'clear, direct answers']):
        d.text(32, 166 + i * 17, '· ' + s, 11, 400)
    d.box(20, 238, 400, 44, fill=PALE[SMART])
    d.lines(220, 256, ['Up to about 40% more visible in that study; results vary.', 'Added statistics still have to be true.'], 10.5, 700, 'middle', INK, 15)
    return d


# ---- Generative AI ------------------------------------------------------------------------------------------------

def speckle(d, x, y, s, amount, seed, shape=None):
    """A square picture with random speckles: amount 0 (clean) to 1 (pure noise); shape draws the clean picture."""
    import random
    rnd = random.Random(seed)
    d.add(f'<rect x="{x}" y="{y}" width="{s}" height="{s}" fill="{WHITE}" stroke="{INK}" stroke-width="1.2"/>')
    if shape and amount < 1:
        d.add(f'<g opacity="{1 - amount * 0.85:.2f}">{shape(x, y, s)}</g>')
    for _ in range(int(amount * 70)):
        px, py = x + 2 + rnd.random() * (s - 6), y + 2 + rnd.random() * (s - 6)
        c = rnd.choice(['#231F20', '#9b9590', '#c9c3b8', SAFE, SMART])
        d.add(f'<rect x="{px:.1f}" y="{py:.1f}" width="3" height="3" fill="{c}"/>')


def house(x, y, s):
    k = s / 60
    return (f'<rect x="{x + 16 * k:.1f}" y="{y + 30 * k:.1f}" width="{28 * k:.1f}" height="{20 * k:.1f}" fill="{PALE[SMART]}" stroke="{INK}" stroke-width="1.2"/>'
            f'<path d="M{x + 12 * k:.1f} {y + 31 * k:.1f} L{x + 30 * k:.1f} {y + 14 * k:.1f} L{x + 48 * k:.1f} {y + 31 * k:.1f}z" fill="{SMART}" stroke="{INK}" stroke-width="1.2"/>'
            f'<circle cx="{x + 48 * k:.1f}" cy="{y + 12 * k:.1f}" r="{5 * k:.1f}" fill="{YELLOW}" stroke="{INK}" stroke-width="1"/>')


@illustration('Ga', 'Analysing versus creating',
              'Given the same kind of data, analytical AI answers a question about it, such as "is this a cat?", '
              'while generative AI makes something new that fits the patterns it learned: a new cat picture, a poem, '
              'a voice or code. What it makes is likely, not checked: it can look right and still be wrong.',
              'Generative AI learns patterns from huge numbers of examples, then produces new text, images, audio, '
              'video or code that fit them. The output is what is likely, not what is checked.')
def _ga():
    d = D(440, 290)
    d.box(20, 36, 196, 160, fill=PALE[SAFE])
    d.text(118, 56, 'ANALYTICAL AI', 10.5, 800, 'middle', SAFE_D)
    d.text(118, 72, 'answers a question about data', 10, 400, 'middle', DIM)
    d.box(48, 86, 140, 44, fill=WHITE)
    d.text(118, 113, 'photo of a cat', 11.5, 700, 'middle')
    d.arrow(118, 132, 118, 150)
    d.text(118, 176, '"Cat: 97% likely"', 13, 800, 'middle')
    d.box(224, 36, 196, 160, fill=PALE[CREATIVE])
    d.text(322, 56, 'GENERATIVE AI', 10.5, 800, 'middle', CREATIVE_D)
    d.text(322, 72, 'makes something new', 10, 400, 'middle', DIM)
    d.box(252, 86, 140, 44, fill=WHITE)
    d.text(322, 113, '"a cat in a top hat"', 11.5, 700, 'middle')
    d.arrow(322, 132, 322, 150)
    for i, k in enumerate(['picture', 'text', 'audio', 'code']):
        d.box(236 + i * 44, 156, 40, 28, fill=WHITE)
        d.text(256 + i * 44, 174, k, 9, 700, 'middle')
    d.box(20, 210, 400, 66, fill=YELLOW)
    d.lines(220, 232, ['It learns patterns from huge numbers of examples,', 'then makes what is likely to fit them.',
                       'Likely is not the same as checked.'], 11, 700, 'middle', INK, 16)
    return d


@illustration('Mm', 'Different inputs, one shared model',
              'A multimodal model takes a photo of the inside of a fridge and the question "What can I cook?". Each '
              'kind of input is turned into the same kind of numbers, so one model can read them together and '
              'answer in text, or in other modes such as speech or an image.',
              'Multimodal models turn text, images and sound into the same kind of numbers, so one model can read '
              'them together, and some can answer in more than one mode too.')
def _mm():
    d = D(440, 290)
    ins = [('PHOTO', 'inside of a fridge', SAFE), ('TEXT', '"What can I cook?"', SMART), ('AUDIO', 'or ask out loud', CREATIVE)]
    for i, (k, v, c) in enumerate(ins):
        y = 40 + i * 62
        d.box(20, y, 130, 50, fill=PALE[c])
        d.text(32, y + 20, k, 10, 800)
        d.text(32, y + 38, v, 10.5, 700)
        d.arrow(152, y + 25, 188, 125 + (i - 1) * 20)
    d.box(190, 70, 104, 110, fill=YELLOW, sw=2.5)
    d.lines(242, 104, ['all turned into', 'the same kind', 'of numbers:', 'one model', 'reads them', 'together'], 10.5, 700, 'middle', INK, 13)
    d.arrow(296, 125, 316, 125)
    d.box(318, 70, 102, 110, fill=PALE[RESP])
    d.text(369, 90, 'ANSWER', 10, 800, 'middle')
    d.lines(369, 112, ['"Omelette:', 'you have eggs,', 'cheese and', 'peppers."'], 10.5, 700, 'middle', INK, 14)
    d.box(20, 222, 400, 54, fill=WHITE, dash=True)
    d.lines(220, 244, ['It can still misread a picture: check what it says it saw.', 'Some models can also answer in speech or images.'], 11, 700, 'middle', INK, 16)
    return d


@illustration('Df', 'Add noise to learn; remove it to create',
              'Training: real pictures have more and more random noise added, step by step, and the model learns to '
              'predict the noise so it can take it away. Creating: it starts from pure noise and removes a little at '
              'a time, guided by your prompt, until a new picture appears.',
              'A diffusion model learns by watching pictures dissolve into noise and practising the reverse. To '
              'make a new picture, it starts from noise and cleans it up in many small steps, steered by the prompt.')
def _df():
    d = D(440, 290)
    d.text(20, 30, 'TRAINING: ADD NOISE, STEP BY STEP', 10.5, 800, fill=SAFE_D)
    amts = [0, 0.33, 0.66, 1]
    for i, a in enumerate(amts):
        x = 30 + i * 102
        speckle(d, x, 40, 62, a, 10 + i, house)
        if i < 3:
            d.arrow(x + 64, 71, x + 100, 71)
    d.text(220, 122, 'the model learns to predict the noise that was added', 10.5, 400, 'middle', DIM)
    d.text(20, 150, 'CREATING: START FROM NOISE, REMOVE IT STEP BY STEP', 10.5, 800, fill=CREATIVE_D)
    for i, a in enumerate(reversed(amts)):
        x = 30 + i * 102
        speckle(d, x, 160, 62, a, 50 + i, house)
        if i < 3:
            d.arrow(x + 64, 191, x + 100, 191, stroke=CREATIVE_D)
    d.box(20, 236, 400, 42, fill=YELLOW)
    d.lines(220, 253, ['Your prompt ("a house on a sunny day") steers each clean-up step.', 'Usually 20 to 50 steps; made-up pictures here.'], 10.5, 700, 'middle', INK, 15)
    return d


@illustration('Gn', 'A forger and a detective',
              'A generative adversarial network has two parts. The generator makes fake pictures; the discriminator '
              'sees real and fake ones and guesses which is which. Each learns from the other: the generator from '
              'being caught, the discriminator from its mistakes. Training aims for fakes the discriminator can '
              'only guess at, right about half the time.',
              'A GAN trains a generator and a discriminator against each other. When the discriminator can only '
              'guess, right about half the time, the fakes are realistic. Diffusion models have since overtaken GANs '
              'for most image tools. Made-up numbers.')
def _gn():
    d = D(440, 290)
    d.box(20, 50, 120, 70, fill=PALE[CREATIVE])
    d.text(80, 72, 'GENERATOR', 10.5, 800, 'middle', CREATIVE_D)
    d.lines(80, 92, ['the forger:', 'makes fakes'], 10.5, 700, 'middle', INK, 14)
    d.box(20, 140, 120, 50, fill=PALE[SAFE])
    d.lines(80, 160, ['real photos'], 11, 800, 'middle')
    d.text(80, 178, 'from the training set', 9.5, 400, 'middle', DIM)
    d.arrow(142, 85, 196, 112)
    d.arrow(142, 165, 196, 132)
    d.box(198, 90, 120, 70, fill=PALE[SMART])
    d.text(258, 112, 'DISCRIMINATOR', 10.5, 800, 'middle', SMART_D)
    d.lines(258, 132, ['the detective:', 'real or fake?'], 10.5, 700, 'middle', INK, 14)
    d.arrow(258, 88, 142, 58, dash=True, curve=-20)
    d.text(232, 50, 'caught! the forger learns', 10, 700, 'middle', DIM)
    d.box(330, 50, 90, 140)
    d.text(375, 68, 'FAKES CAUGHT', 9, 800, 'middle')
    for i, (r, p) in enumerate([('round 1', 98), ('round 50', 75), ('round 500', 52)]):
        y = 82 + i * 36
        d.text(342, y + 10, r, 9.5, 700, fill=DIM)
        d.add(f'<rect x="342" y="{y + 15}" width="{p * 0.4:.0f}" height="10" fill="{SMART}" stroke="{INK}" stroke-width=".8"/>')
        d.text(412, y + 24, f'{p}%', 9.5, 800, 'end')
    d.box(20, 210, 400, 66, fill=YELLOW)
    d.lines(220, 232, ['When the detective is right only about half the time,', 'it is guessing: the fakes look real.',
                       'Most image tools now use diffusion models instead.'], 11, 700, 'middle', INK, 16)
    return d


@illustration('Ti', 'What you asked, and what the tool decided',
              'The prompt "a fox reading a book in a library, cartoon style" names four things. Everything else in '
              'the picture, such as the fox\'s size, the lighting, the angle and what the book is, is chosen by the '
              'tool from patterns in its training images. Those defaults can carry stereotypes, so describe what '
              'matters and check what it filled in.',
              'Text-to-image tools fill every gap in your prompt with their own defaults, learned from training '
              'images. Say what matters, then check what it decided for you.')
def _ti():
    d = D(440, 290)
    d.box(20, 36, 400, 40, fill=YELLOW)
    d.text(220, 61, '"a fox reading a book in a library, cartoon style"', 12, 800, 'middle')
    d.box(20, 88, 160, 140, fill=PALE[RESP])
    d.text(100, 108, 'YOU ASKED FOR', 10, 800, 'middle', RESP_D)
    for i, k in enumerate(['a fox', 'a book', 'a library', 'cartoon style']):
        d.text(36, 132 + i * 22, '✓  ' + k, 11.5, 700)
    d.box(190, 88, 230, 140, fill=PALE[CREATIVE])
    d.text(305, 108, 'THE TOOL DECIDED', 10, 800, 'middle', CREATIVE_D)
    for i, k in enumerate(['colours and lighting', 'camera angle', 'what the book is', 'glasses? a chair? a cat?', 'who else is there']):
        d.text(206, 132 + i * 19, '?  ' + k, 11, 700)
    d.box(20, 240, 400, 40, fill=WHITE, dash=True)
    d.lines(220, 256, ['Defaults come from training pictures and can carry stereotypes:', 'describe what matters, then check.'], 10.5, 700, 'middle', INK, 14)
    return d


@illustration('Ts', 'From text to a voice',
              'Text-to-speech first tidies the text, so "Dr" becomes "doctor" and "29/4" becomes "the 29th of April". '
              'It works out the sounds and where the stress and pauses go, then a neural voice model turns them into '
              'audio. Some tools can copy a real voice from a short recording, which is why consent matters.',
              'Modern text-to-speech sounds natural because a neural model learned from hours of recorded speech. '
              'Voice cloning can copy a person from a short sample: only use a voice with that person\'s permission.')
def _ts():
    d = D(440, 290)
    d.box(20, 36, 400, 36, fill=YELLOW)
    d.text(220, 59, '"Dr Khan\'s talk is on 29/4."', 13, 800, 'middle')
    rows = [('1 · TIDY THE TEXT', '"Doctor Khan\'s talk is on the 29th of April."', SAFE),
            ('2 · WORK OUT THE SOUNDS', 'DOK-tuh KAHN · stress, pauses, rising or falling', CREATIVE),
            ('3 · A VOICE MODEL', 'turns the sounds into audio', SMART)]
    for i, (k, v, c) in enumerate(rows):
        y = 84 + i * 44
        d.box(20, y, 400, 36, fill=PALE[c])
        d.text(32, y + 15, k, 9.5, 800)
        d.text(32, y + 30, v, 11, 700)
    import math
    pts = ' '.join(f'{40 + t * 3.6:.1f},{226 + math.sin(t / 2.2) * (4 + 10 * abs(math.sin(t / 13))):.1f}' for t in range(101))
    d.add(f'<polyline points="{pts}" fill="none" stroke="{SMART_D}" stroke-width="2"/>')
    d.box(20, 248, 400, 34, fill=WHITE, dash=True)
    d.text(220, 270, 'A voice can be copied from a short sample: ask permission first.', 11, 700, 'middle', RED)
    return d


@illustration('Ft', 'A short extra course for one job',
              'A general model already knows a lot. Fine-tuning trains it a little more on a smaller set of '
              'examples, such as 2,000 marked science answers, which changes some of its settings so it gets better '
              'at that one job. It costs far less than training from scratch, but it can get worse at other things.',
              'Fine-tuning changes a trained model\'s settings with a small, focused set of examples. It is far '
              'cheaper than starting again, but the examples must be good, and other skills can slip. Made-up numbers.')
def _ft():
    d = D(440, 290)
    steps_row(d, 36, [('GENERAL MODEL', ['trained on a huge', 'mix of text'], PALE[SAFE]),
                      ('+ 2,000 EXAMPLES', ['science answers,', 'marked by teachers'], YELLOW),
                      ('FINE-TUNED', ['its settings now', 'suit this job'], PALE[RESP])], h=70)
    d.head(20, 136, 'Marking science answers well')
    for i, (k, v, c) in enumerate([('before', 61, PALE[SAFE]), ('after', 88, RESP)]):
        y = 148 + i * 30
        d.text(20, y + 16, k, 11, 700)
        d.bar(80, y + 2, v * 3.2, 20, c)
        d.text(86 + v * 3.2, y + 17, f'{v}%', 11, 800)
    d.box(20, 216, 400, 62, fill=WHITE, dash=True)
    d.lines(220, 236, ['Far cheaper than training from scratch.', 'But poor examples teach poor habits,',
                       'and the model can get worse at other things.'], 11, 700, 'middle', INK, 15)
    return d


@illustration('Fs', 'Examples in the prompt, not new training',
              'Few-shot learning puts a handful of worked examples in the prompt: "happy → positive", "awful → '
              'negative", "fine → neutral". The model spots the pattern and answers the new one the same way. '
              'Nothing in the model changes: the examples only last for that conversation.',
              'Few-shot prompting shows the model a few examples so it copies the pattern. Zero-shot gives none, '
              'one-shot gives one. Unlike fine-tuning, the model itself doesn\'t change.')
def _fs():
    d = D(440, 290)
    d.box(20, 36, 250, 170, fill=WHITE)
    d.text(32, 56, 'THE PROMPT', 10, 800, fill=DIM)
    for i, (a, b) in enumerate([('"I loved it"', 'positive'), ('"Total waste of time"', 'negative'), ('"It was OK"', 'neutral')]):
        y = 80 + i * 26
        d.box(30, y - 16, 230, 22, fill=PALE[CREATIVE])
        d.text(40, y, a, 11, 700)
        d.text(250, y, '→ ' + b, 11, 800, 'end', CREATIVE_D)
    d.text(40, 172, '"Best trip ever"', 11.5, 800)
    d.text(250, 172, '→ ?', 11.5, 800, 'end')
    d.text(145, 196, 'three examples, then the new one', 9.5, 400, 'middle', DIM)
    d.arrow(272, 166, 300, 166)
    d.box(302, 140, 118, 52, fill=PALE[RESP])
    d.text(361, 162, 'positive', 13, 800, 'middle', RESP_D)
    d.text(361, 180, 'pattern copied', 9.5, 400, 'middle', DIM)
    for i, (k, v) in enumerate([('zero-shot', 'no examples'), ('one-shot', 'one example'), ('few-shot', 'a handful')]):
        d.text(304, 52 + i * 26, k, 11, 800)
        d.text(304, 66 + i * 26, v, 9.5, 400, fill=DIM)
    d.box(20, 220, 400, 56, fill=YELLOW)
    d.lines(220, 242, ['Nothing inside the model changes:', 'the examples only last for this conversation.'], 11, 700, 'middle', INK, 16)
    return d


@illustration('Rm', 'Thinking before answering',
              'A bat and a ball cost £1.10 in total; the bat costs £1 more than the ball. A quick answer says the '
              'ball costs 10p, which is wrong. A reasoning model works through steps first, checks them, and finds '
              '5p. The extra thinking helps with maths, code and logic, but takes longer and costs more.',
              'Reasoning models are trained to work through a problem, and check their steps, before answering. '
              'They do better at maths, coding and logic, but are slower and cost more, and their steps can still '
              'go wrong.')
def _rm():
    d = D(440, 290)
    d.box(20, 36, 400, 40, fill=YELLOW)
    d.lines(220, 52, ['A bat and a ball cost £1.10. The bat costs £1 more', 'than the ball. How much is the ball?'], 11, 800, 'middle', INK, 15)
    d.box(20, 88, 150, 110)
    d.text(95, 108, 'QUICK ANSWER', 10, 800, 'middle', DIM)
    d.text(95, 146, '"10p"', 18, 800, 'middle', RED)
    d.tick(95, 176, False)
    d.box(180, 88, 240, 110, fill=PALE[RESP])
    d.text(300, 108, 'REASONING MODEL', 10, 800, 'middle', RESP_D)
    d.lines(194, 128, ['ball = x, bat = x + £1', 'x + x + £1 = £1.10, so 2x = 10p', 'check: 5p + £1.05 = £1.10 ✓'], 10.5, 700, 'start', INK, 16)
    d.text(300, 188, '"5p"', 14, 800, 'middle', RESP_D)
    d.box(20, 212, 400, 66, fill=WHITE, dash=True)
    d.lines(220, 234, ['Better at maths, code and logic.', 'Slower and dearer: more thinking means more computing.',
                       'Its steps can still go wrong, so check them.'], 11, 700, 'middle', INK, 16)
    return d


@illustration('Sd', 'Useful extra data, with a catch',
              'When real examples are rare, such as photos of a rare skin condition, a generator can make more, so '
              'a model sees enough to learn. But if models are trained on generated data generation after '
              'generation, the rare cases fade and the output drifts from reality: "model collapse". Real data has '
              'to stay in the mix.',
              'Synthetic data fills gaps and protects privacy. A 2024 Nature study (Shumailov and others) showed '
              'that models trained over and over on generated data lose the rare cases first, so real data must stay '
              'in the mix.')
def _sd():
    d = D(440, 290)
    d.box(20, 36, 400, 84, fill=PALE[RESP])
    d.text(32, 56, 'THE USE', 10.5, 800, fill=RESP_D)
    d.text(32, 78, '50 real photos of a rare condition', 11.5, 700)
    d.arrow(262, 74, 290, 74)
    d.text(296, 78, '+ 950 generated', 11.5, 800)
    d.text(32, 102, 'enough to train on, with no extra patient photos needed', 10.5, 400, fill=DIM)
    d.box(20, 130, 400, 148)
    d.text(32, 150, 'THE CATCH: TRAINED ON ITS OWN OUTPUT, AGAIN AND AGAIN', 10.5, 800, fill=RED)
    for i, (k, w) in enumerate([('real data', 1.0), ('generation 3', 0.62), ('generation 9', 0.3)]):
        x = 40 + i * 128
        cx = x + 50
        pts = []
        import math
        for t in range(-50, 51):
            y = 236 - 56 * math.exp(-(t / (22 * w)) ** 2) * (1 / max(w, .45)) ** .4
            pts.append(f'{cx + t:.0f},{max(y, 166):.1f}')
        d.add(f'<polyline points="{" ".join(pts)}" fill="{PALE[SAFE]}" stroke="{SAFE_D}" stroke-width="2"/>')
        d.add(f'<line x1="{cx - 52}" y1="236" x2="{cx + 52}" y2="236" stroke="{INK}" stroke-width="1"/>')
        d.text(cx, 252, k, 10.5, 800, 'middle')
    d.text(220, 270, 'Rare cases at the edges vanish first: keep real data in the mix.', 10.5, 700, 'middle', RED)
    return d


@illustration('Dk', 'Stop, check the source, report',
              'Spotting glitches in deepfakes, such as odd hands or blinking, works less and less as tools improve. '
              'Better habits: stop before sharing, check who posted it first and whether trusted news reports it, '
              'look for a content credentials label, and report it. Creating sexual deepfakes is a crime.',
              'Deepfakes are getting too good to spot by eye, so check the source, not just the pixels. In England '
              'and Wales, creating a sexual deepfake of an adult without consent has been a crime since 6 February '
              '2026; any sexual image of an under-18 is illegal. Young people can use Childline\'s Report Remove.')
def _dk():
    d = D(440, 290)
    d.box(20, 36, 400, 46, fill=WHITE, dash=True)
    d.lines(220, 54, ['Looking for glitches (hands, blinking, lip sync) works less', 'and less as the tools improve. Check the source instead.'], 10.5, 700, 'middle', DIM, 15)
    cols = [('1 · STOP', ['before you share', 'or react', 'shocking = suspicious'], SMART),
            ('2 · CHECK', ['who posted it first?', 'do trusted news sites', 'report it too?', 'a content credentials', 'label?'], SAFE),
            ('3 · REPORT', ['to the platform', 'tell a trusted adult', 'Childline', 'Report Remove'], RESP)]
    for i, (k, ls, c) in enumerate(cols):
        x = 20 + i * 136
        d.box(x, 94, 128, 128, fill=PALE[c])
        d.text(x + 64, 114, k, 11, 800, 'middle')
        d.lines(x + 64, 136, ls, 10, 700, 'middle', INK, 15)
        if i < 2:
            d.arrow(x + 129, 158, x + 135, 158)
    d.box(20, 234, 400, 46, fill=YELLOW)
    d.lines(220, 252, ['Making a sexual deepfake of someone without consent is a crime', 'in England and Wales; of an under-18, it always is.'], 10.5, 800, 'middle', INK, 15)
    return d


@illustration('Vc', 'The loop that skips the checks',
              'Vibe coding: describe what you want, let the AI write the code, run it, and if it seems to work, ship '
              'it. The steps it skips are reading the code, testing the edge cases and checking security. In one '
              '2025 test of over 100 models, 45% of AI-written code samples had a security flaw.',
              'Vibe coding is great for quick prototypes and learning. Before real users or real data, someone has '
              'to read, test and security-check the code: Veracode (2025) found flaws in 45% of AI-written samples.')
def _vc():
    d = D(440, 290)
    loop = [('describe it', 90, 56), ('AI writes code', 230, 56), ('run it', 230, 130), ('"it works!"', 90, 130)]
    for k, x, y in loop:
        d.box(x - 60, y - 18, 120, 36, fill=PALE[CREATIVE])
        d.text(x, y + 5, k, 11.5, 800, 'middle')
    d.arrow(152, 56, 168, 56)
    d.arrow(230, 76, 230, 110)
    d.arrow(168, 130, 152, 130)
    d.arrow(90, 110, 90, 76)
    d.arrow(292, 130, 316, 130)
    d.box(318, 112, 102, 36, fill=YELLOW)
    d.text(369, 135, 'ship it', 12, 800, 'middle')
    d.box(20, 170, 400, 64, fill=WHITE, dash=True)
    d.text(32, 190, 'WHAT GETS SKIPPED', 10, 800, fill=RED)
    for i, k in enumerate(['reading the code', 'testing odd cases', 'security checks']):
        d.tick(48 + i * 132, 214, False)
        d.text(62 + i * 132, 219, k, 11, 700)
    d.box(20, 244, 400, 38, fill=PALE[SMART])
    d.text(220, 268, '45% of AI-written code samples had a security flaw (Veracode, 2025)', 10.5, 800, 'middle')
    return d


@illustration('Ce', 'Everything the model reads, not just your question',
              'Context engineering plans everything that goes into the model\'s context window: rules and role, '
              'background documents, worked examples, tool results, the conversation so far, and finally the '
              'question. A clever question alone is a small part of it. Too much, or the wrong things, crowds out '
              'what matters.',
              'Context engineering decides what the model gets to read: rules, documents, examples, tool results, '
              'memory and the question. The window has a limit, so choosing what to leave out matters as much as '
              'what to put in.')
def _ce():
    d = D(440, 290)
    d.head(20, 24, 'What fills the context window')
    parts = [('rules and role', 'mark like a Year 9 science teacher', 44, SAFE),
             ('background documents', 'the mark scheme, the topic notes', 70, CREATIVE),
             ('worked examples', 'two marked answers', 40, SMART),
             ('tool results and memory', 'last week\'s marks', 30, FUTURE),
             ('the question', '"Mark this answer"', 22, RESP)]
    y = 38
    for k, v, h, c in parts:
        d.box(20, y, 250, h, fill=PALE[c] if k != 'the question' else c)
        d.text(32, y + min(h / 2 + 4, 20), k, 11, 800)
        if h >= 40:
            d.text(32, y + 34, v, 10, 400, fill=DIM)
        y += h
    d.add(f'<path d="M278 38 h8 v{y - 38} h-8" fill="none" stroke="{INK}" stroke-width="1.5"/>')
    d.lines(294, 110, ['one context', 'window,', 'with a limit'], 11, 700, 'start', INK, 15)
    d.add(f'<path d="M278 {y - 22} h8 v22 h-8" fill="none" stroke="{RED}" stroke-width="1.5"/>')
    d.lines(294, y - 14, ['your prompt is', 'only this bit'], 10, 700, 'start', RED, 12)
    d.box(20, 254, 400, 30, fill=YELLOW)
    d.text(220, 274, 'Choose what goes in, and what stays out.', 11.5, 700, 'middle')
    return d


@illustration('Gv', 'Every frame has to agree',
              'A video clip is many still frames, often 24 a second. A generative video model has to make each frame '
              'look right and also keep them consistent: the same face, objects that don\'t vanish, movement that '
              'obeys gravity. Long clips, hands, text and physics are where it most often slips.',
              'Generative video makes many frames that must agree with each other. Today\'s tools make short clips '
              'and still slip on physics, hands and text, and most label or watermark what they make.')
def _gv():
    d = D(440, 290)
    d.box(20, 36, 400, 34, fill=YELLOW)
    d.text(220, 58, '"a red ball bounces down some steps"', 12, 800, 'middle')
    for i in range(5):
        x = 20 + i * 82
        d.add(f'<rect x="{x}" y="84" width="74" height="74" fill="{WHITE}" stroke="{INK}" stroke-width="1.4"/>')
        d.add(f'<path d="M{x} 158 v-14 h20 v-16 h20 v-16 h20 v-16 h14 v62z" fill="{PALE[SAFE]}" stroke="{INK}" stroke-width="1"/>')
        bx = [64, 48, 36, 24, 12][i]
        by = [100, 104, 120, 122, 138][i]
        bad = i == 3
        d.add(f'<circle cx="{x + bx}" cy="{by}" r="{6 if not bad else 4}" fill="{SMART if not bad else CREATIVE}" stroke="{INK}" stroke-width="1"/>')
        d.text(x + 37, 172, f'frame {i + 1}', 9.5, 700, 'middle', RED if bad else DIM)
    d.add(f'<rect x="262" y="84" width="74" height="74" fill="none" stroke="{RED}" stroke-width="3"/>')
    d.text(299, 190, 'slip: ball changed', 10, 800, 'middle', RED)
    d.box(20, 204, 400, 76, fill=WHITE, dash=True)
    d.text(32, 224, 'EVERY FRAME HAS TO AGREE', 10, 800, fill=SAFE_D)
    d.lines(32, 244, ['same faces and objects, movement that obeys gravity', 'Most likely to slip: long clips, hands, text, physics'], 11, 700, 'start', INK, 17)
    return d


@illustration('Sp', 'Cheap to make, costly to sort through',
              'AI slop is cheap to make: one account can post a hundred AI images or articles an hour, aiming for '
              'clicks rather than truth. Signs include generic text, odd details, no named source and accounts '
              'posting non-stop. Not sharing it, and choosing trusted sources, stops it spreading.',
              'AI slop spreads because it costs almost nothing to make and clicks still pay. It drowns out real work '
              'and can mislead. Check for a named source, and don\'t feed it with shares.')
def _sp():
    d = D(440, 290)
    d.head(20, 24, 'One account, one hour')
    for i in range(100):
        x, y = 20 + (i % 20) * 12, 38 + (i // 20) * 16
        d.add(f'<rect x="{x}" y="{y}" width="10" height="13" fill="{PALE[SMART] if i % 7 else SMART}" stroke="{INK}" stroke-width=".7"/>')
    d.text(272, 58, '100 posts', 13, 800)
    d.text(272, 76, 'a few pence to make', 10.5, 700, fill=DIM)
    d.text(272, 94, 'aimed at clicks,', 10.5, 700, fill=DIM)
    d.text(272, 108, 'not truth', 10.5, 700, fill=DIM)
    d.box(20, 126, 196, 118, fill=PALE[SMART])
    d.text(118, 146, 'SIGNS', 10.5, 800, 'middle', SMART_D)
    d.lines(34, 168, ['generic, padded text', 'odd details in pictures', 'no named source', 'posting non-stop'], 11, 700, 'start', INK, 18)
    d.box(224, 126, 196, 118, fill=PALE[RESP])
    d.text(322, 146, 'WHAT HELPS', 10.5, 800, 'middle', RESP_D)
    d.lines(238, 168, ['don\'t share or reply', 'check for a source', 'follow trusted people', 'report spam accounts'], 11, 700, 'start', INK, 18)
    d.text(220, 270, 'Every click and share pays the people making it.', 11.5, 700, 'middle', SMART_D)
    return d


@illustration('2d', 'AI suggests, the designer decides',
              'A 2D design brief, a logo for a school robotics club, goes to an AI tool, which suggests several '
              'options. The designer picks one, edits it and turns it into vector paths: clean lines a laser cutter '
              'or printer can follow, unlike a pixel image.',
              'AI tools can produce lots of 2D ideas quickly. A designer still chooses, edits and checks them, and '
              'a laser cutter needs vector paths, not a pixel picture.')
def _2d():
    d = D(440, 290)
    d.box(20, 36, 400, 34, fill=YELLOW)
    d.text(220, 58, 'Brief: a logo for the school robotics club', 12, 800, 'middle')
    for i in range(4):
        x = 20 + i * 102
        best = i == 2
        d.box(x, 84, 94, 80, fill=PALE[RESP] if best else WHITE, sw=2.5 if best else 1.5)
        cx = x + 47
        if i == 0:
            d.add(f'<rect x="{cx - 18}" y="102" width="36" height="30" rx="6" fill="{PALE[SAFE]}" stroke="{INK}" stroke-width="1.4"/><circle cx="{cx - 8}" cy="116" r="4" fill="{INK}"/><circle cx="{cx + 8}" cy="116" r="4" fill="{INK}"/>')
        elif i == 1:
            d.add(f'<path d="M{cx} 98 l20 34 h-40z" fill="{PALE[SMART]}" stroke="{INK}" stroke-width="1.4"/>')
        elif i == 2:
            d.add(f'<circle cx="{cx}" cy="118" r="20" fill="{PALE[CREATIVE]}" stroke="{INK}" stroke-width="1.4"/><path d="M{cx - 9} 112 h18 M{cx} 112 v16 M{cx - 12} 128 h24" stroke="{INK}" stroke-width="2"/>')
        else:
            d.add(f'<path d="M{cx - 20} 130 q20 -40 40 0" fill="none" stroke="{INK}" stroke-width="2"/><circle cx="{cx}" cy="108" r="6" fill="{SMART}" stroke="{INK}"/>')
        d.text(cx, 156, 'picked' if best else f'option {i + 1}', 9.5, 800 if best else 400, 'middle', RESP_D if best else DIM)
    d.text(20, 182, 'AI SUGGESTS OPTIONS', 9.5, 800, fill=DIM)
    steps_row(d, 196, [('DESIGNER EDITS', ['fixes the details'], WHITE), ('VECTOR PATHS', ['clean lines, any size'], PALE[SAFE]),
                       ('LASER CUT', ['or print'], PALE[SMART])], h=54)
    d.text(220, 272, 'A laser cutter follows paths, not pixels.', 11, 700, 'middle', SAFE_D)
    return d


@illustration('3d', 'Set the goals, let it propose shapes',
              'Generative design starts from goals rather than a drawing: where a bracket must attach, what weight '
              'it carries, the material and how it will be made. The software proposes many shapes that meet them, '
              'often lighter and more curved than a person would draw. An engineer chooses, checks and tests one '
              'before it is printed.',
              'In 3D design, AI-driven generative design proposes shapes that meet set goals and limits. People set '
              'the goals and must still check and test the result, because a design that passes on screen can fail '
              'in use.')
def _3d():
    d = D(440, 290)
    d.box(20, 36, 150, 150, fill=PALE[SAFE])
    d.text(95, 56, 'THE GOALS', 10.5, 800, 'middle', SAFE_D)
    d.lines(32, 78, ['fix to 2 bolt holes', 'hold 5 kg', 'use PLA plastic', '3D printable', 'as light as possible'], 10.5, 700, 'start', INK, 20)
    d.arrow(172, 111, 194, 111)
    d.box(196, 36, 224, 150)
    d.text(308, 56, 'SHAPES IT PROPOSES', 10.5, 800, 'middle')
    shapes = [f'M206 160 h60 v-14 h-46 v-60 h-14z',
              f'M280 160 h64 v-12 q-30 -4 -44 -30 q-6 -14 -6 -34 h-14z',
              f'M356 160 h56 v-10 c-22 0 -24 -14 -30 -24 c-8 -14 2 -26 -10 -42 h-16z']
    for i, p in enumerate(shapes):
        best = i == 2
        d.add(f'<path d="{p}" fill="{PALE[RESP] if best else PALE[CREATIVE]}" stroke="{INK}" stroke-width="{2.2 if best else 1.4}"/>')
    d.text(236, 178, 'heavy', 9.5, 700, 'middle', DIM)
    d.text(312, 178, 'lighter', 9.5, 700, 'middle', DIM)
    d.text(384, 178, 'lightest', 9.5, 800, 'middle', RESP_D)
    steps_row(d, 200, [('ENGINEER CHOOSES', ['and checks it'], WHITE), ('TEST IT', ['on screen, then for real'], PALE[SMART]),
                       ('PRINT OR MAKE', ['ready to use'], PALE[RESP])], h=54)
    d.text(220, 276, 'People set the goals and test the result.', 11, 700, 'middle', SAFE_D)
    return d


# ---- Automation ---------------------------------------------------------------------------------------------------

@illustration('Rp', 'A bot that copies your clicks',
              'Robotic process automation records the fixed steps a person does on screen, such as open the email, '
              'copy the name, paste it into the spreadsheet, next email, and repeats them exactly, hundreds of '
              'times. It does not understand the screen: if a button moves or a form changes, the bot stops.',
              'RPA bots follow the same clicks and typing every time. They are fast and tireless on fixed steps, '
              'but brittle: a changed screen or an unusual email breaks them.')
def _rp():
    d = D(440, 290)
    d.head(20, 24, 'The steps the bot repeats')
    steps = ['open next email', 'copy the name', 'paste into sheet', 'copy the date', 'paste, save']
    for i, k in enumerate(steps):
        y = 38 + i * 30
        d.box(20, y, 180, 24, fill=PALE[SAFE])
        d.text(32, y + 16, f'{i + 1}  {k}', 11, 700)
    d.arrow(208, 170, 208, 50, dash=True, curve=24)
    d.text(236, 116, '× 300', 14, 800, 'start', SAFE_D)
    d.box(290, 40, 130, 70, fill=PALE[RESP])
    d.lines(355, 64, ['300 emails', 'in 10 minutes'], 11.5, 800, 'middle', INK, 16)
    d.box(290, 122, 130, 66, fill=PALE[SMART])
    d.lines(355, 142, ['the form changes:', 'button moved', 'bot stops'], 10.5, 700, 'middle', INK, 14)
    d.box(20, 204, 400, 74, fill=YELLOW)
    d.lines(220, 226, ['It copies clicks; it doesn\'t understand them.', 'Great for fixed, repetitive steps.',
                       'Someone has to fix it when the screen changes.'], 11, 700, 'middle', INK, 16)
    return d


@illustration('Ia', 'Bots plus AI that can decide',
              'Intelligent automation adds AI to bots. Emails arrive in a school office inbox. An AI model reads each '
              'one and picks its topic, with a confidence score: absence, trip, or complaint. A bot then files it in '
              'the right place. Anything the AI is unsure about goes to a person.',
              'Intelligent automation lets bots handle work that needs judgement, like sorting messages, by adding '
              'AI. Low-confidence cases should go to a person. Made-up numbers.')
def _ia():
    d = D(440, 290)
    d.box(20, 40, 100, 120, fill=WHITE)
    d.text(70, 60, 'INBOX', 10, 800, 'middle', DIM)
    for i in range(4):
        d.add(f'<rect x="34" y="{72 + i * 20}" width="72" height="14" fill="{PALE[SAFE]}" stroke="{INK}" stroke-width=".8"/>')
    d.arrow(122, 100, 146, 100)
    d.box(148, 40, 120, 120, fill=PALE[CREATIVE])
    d.text(208, 60, 'AI READS IT', 10, 800, 'middle', CREATIVE_D)
    for i, (k, v) in enumerate([('absence', '96%'), ('trip', '91%'), ('complaint', '88%'), ('unsure', '54%')]):
        d.text(160, 84 + i * 20, k, 10.5, 700)
        d.text(256, 84 + i * 20, v, 10.5, 800, 'end', RED if k == 'unsure' else INK)
    d.arrow(270, 100, 294, 100)
    d.box(296, 40, 124, 74, fill=PALE[SAFE])
    d.text(358, 60, 'BOT FILES IT', 10, 800, 'middle', SAFE_D)
    d.lines(358, 80, ['register, trips folder,', 'head of year'], 10, 700, 'middle', INK, 14)
    d.box(296, 122, 124, 38, fill=YELLOW)
    d.text(358, 146, 'unsure → a person', 11, 800, 'middle')
    d.box(20, 180, 400, 98, fill=WHITE, dash=True)
    d.text(32, 200, 'RPA ON ITS OWN', 10, 800, fill=DIM)
    d.text(32, 218, 'follows fixed rules: can\'t tell what an email is about', 11, 700)
    d.text(32, 244, 'WITH AI ADDED', 10, 800, fill=CREATIVE_D)
    d.text(32, 262, 'reads, sorts and picks between options, with a confidence score', 11, 700)
    return d


@illustration('Hy', 'Many tools, one process',
              'Hyperautomation looks at a whole process, such as a school trip, and hands each step to the best tool: '
              'a form and a trigger for sign-up, document AI for consent forms, a rules engine for payment '
              'reminders, a bot to update the register. People keep the decisions that need judgement.',
              'Hyperautomation, a term Gartner popularised in 2019, means combining many automation and AI tools '
              'across whole processes. Not every step should be automated: people keep the judgement calls.')
def _hy():
    d = D(440, 290)
    d.head(20, 24, 'A school trip, step by step')
    steps = [('sign-up form', 'trigger', SAFE), ('consent forms read', 'document AI', CREATIVE),
             ('payment reminders', 'rules engine', SMART), ('register updated', 'bot (RPA)', SAFE),
             ('who can go if full?', 'a person decides', RESP)]
    for i, (k, tool, c) in enumerate(steps):
        y = 38 + i * 36
        d.box(20, y, 210, 30)
        d.text(32, y + 20, k, 11.5, 700)
        d.arrow(232, y + 15, 256, y + 15)
        d.box(258, y, 162, 30, fill=PALE[c] if c != RESP else RESP)
        d.text(339, y + 20, tool, 11, 800, 'middle')
    d.box(20, 228, 400, 50, fill=YELLOW)
    d.lines(220, 248, ['Each step gets the right tool, not one tool for everything.', 'People keep the judgement calls.'], 11, 700, 'middle', INK, 16)
    return d


@illustration('Tg', 'What starts an automation',
              'Every automation starts with a trigger, then may check a condition, then does its actions. Triggers '
              'come in three kinds: something happens (a new email arrives), someone does something (a form is '
              'submitted), or a time arrives (every Monday at 8am).',
              'Trigger, condition, action: the shape of almost every automation. A badly chosen trigger can fire too '
              'often, or never, so test it.')
def _tg():
    d = D(440, 290)
    d.text(20, 30, 'THREE KINDS OF TRIGGER', 10.5, 800, fill=DIM)
    trig = [('SOMETHING HAPPENS', 'a new email arrives', SAFE), ('SOMEONE ACTS', 'a form is submitted', CREATIVE),
            ('A TIME ARRIVES', 'every Monday, 8am', SMART)]
    for i, (k, v, c) in enumerate(trig):
        x = 20 + i * 136
        d.box(x, 40, 128, 60, fill=PALE[c])
        d.text(x + 64, 62, k, 9.5, 800, 'middle')
        d.text(x + 64, 84, v, 10.5, 700, 'middle')
    d.text(20, 132, 'THEN, FOR EXAMPLE', 10.5, 800, fill=DIM)
    steps_row(d, 142, [('TRIGGER', ['form submitted'], YELLOW), ('CONDITION', ['is it Year 7?'], WHITE),
                       ('ACTION', ['email the tutor,', 'add a row'], PALE[RESP])], h=66)
    d.box(20, 224, 400, 54, fill=WHITE, dash=True)
    d.lines(220, 246, ['A trigger that fires too often floods inboxes;', 'one that never fires fails silently. Test it.'], 11, 700, 'middle', INK, 16)
    return d


@illustration('En', 'Act the moment it happens',
              'A booking is made at 9:02. An automation that checks on a schedule, every 15 minutes, only spots it at '
              '9:15 and wastes checks when nothing has happened. An event-driven automation is told straight away, '
              'usually by a webhook, and acts at 9:02.',
              'Event-driven automations react as things happen instead of checking on a timer. That is faster and '
              'wastes fewer checks, but the system sending the events has to be reliable.')
def _en():
    d = D(440, 290)
    d.head(20, 24, 'A booking is made at 9:02')
    for row, (k, c) in enumerate([('CHECKS EVERY 15 MINUTES', SMART), ('EVENT-DRIVEN', RESP)]):
        y = 50 + row * 90
        d.text(20, y, k, 10.5, 800, fill=SMART_D if row == 0 else RESP_D)
        d.add(f'<line x1="40" y1="{y + 36}" x2="400" y2="{y + 36}" stroke="{INK}" stroke-width="1.4"/>')
        for t, lab in ((0, '9:00'), (15, '9:15'), (30, '9:30')):
            x = 40 + t * 12
            d.add(f'<line x1="{x}" y1="{y + 31}" x2="{x}" y2="{y + 41}" stroke="{INK}" stroke-width="1.4"/>')
            d.text(x, y + 56, lab, 9.5, 400, 'middle', DIM)
        bx = 40 + 2 * 12
        d.add(f'<circle cx="{bx}" cy="{y + 36}" r="6" fill="{YELLOW}" stroke="{INK}" stroke-width="1.3"/>')
        if row == 0:
            for t in (0, 15, 30):
                d.add(f'<circle cx="{40 + t * 12}" cy="{y + 20}" r="5" fill="{PALE[SMART]}" stroke="{INK}" stroke-width="1"/>')
            d.arrow(bx + 6, y + 20, 40 + 15 * 12 - 8, y + 20, stroke=SMART_D, dash=True)
            d.text(226, y + 12, 'spotted at 9:15', 10.5, 800, 'start', SMART_D)
        else:
            d.add(f'<circle cx="{bx}" cy="{y + 18}" r="7" fill="{RESP}" stroke="{INK}" stroke-width="1"/>')
            d.text(bx + 14, y + 22, 'acts at 9:02: told the moment it happens', 10.5, 800, 'start', RESP_D)
    d.box(20, 236, 400, 44, fill=YELLOW)
    d.lines(220, 254, ['Faster, with no wasted checks.', 'Usually set up with triggers and webhooks.'], 11, 700, 'middle', INK, 15)
    return d


@illustration('Wh', 'A message sent the moment something happens',
              'When a parent pays for a trip, the payment app sends a short message to a web address the school\'s '
              'system gave it: which event, which pupil, how much. The school system checks the message is genuine, '
              'replies "got it", and updates the trip list. No one had to ask.',
              'A webhook is one app calling another\'s web address the moment something happens, with the details '
              'attached. Because anyone could send to that address, the receiver should check a signature.')
def _wh():
    d = D(440, 290)
    d.box(20, 40, 120, 70, fill=PALE[SAFE])
    d.lines(80, 66, ['PAYMENT APP', 'a parent pays'], 10.5, 800, 'middle', INK, 18)
    d.box(300, 40, 120, 70, fill=PALE[RESP])
    d.lines(360, 66, ['SCHOOL SYSTEM', 'trip list updated'], 10.5, 800, 'middle', INK, 18)
    d.arrow(142, 62, 298, 62)
    d.text(220, 54, 'sends a message, at once', 10, 700, 'middle', DIM)
    d.arrow(298, 92, 142, 92, dash=True)
    d.text(220, 106, 'replies "got it" (200 OK)', 10, 700, 'middle', DIM)
    d.box(60, 126, 320, 92, fill=WHITE)
    d.text(72, 144, 'THE MESSAGE, SENT TO school.example/hooks/pay', 9, 800, fill=DIM)
    d.lines(72, 166, ['{ "event": "payment.paid",', '  "pupil": "Y8-0412", "amount": 12.50,', '  "signature": "a91f…" }'], 11, 700, 'start', INK, 16)
    d.box(20, 230, 400, 48, fill=YELLOW)
    d.lines(220, 249, ['Anyone could send to that address, so the school system', 'checks the signature before trusting it.'], 11, 700, 'middle', INK, 16)
    return d


@illustration('Lc', 'Build it from blocks',
              'A low-code or no-code tool lets you build an automation by dragging blocks: when a form is '
              'submitted, if the pupil is in Year 7, send an email to their tutor and add a row to a sheet. It is '
              'quick and needs no programming, but someone still has to own it, keep data safe and fix it.',
              'Low-code and no-code tools let non-programmers build apps and automations quickly. The questions '
              'don\'t go away: whose data does it touch, who maintains it, and what happens when it breaks?')
def _lc():
    d = D(440, 290)
    blocks = [('WHEN', 'a form is submitted', SAFE), ('IF', 'year group = 7', SMART),
              ('THEN', 'email the tutor', RESP), ('AND', 'add a row to the sheet', RESP)]
    for i, (k, v, c) in enumerate(blocks):
        y = 36 + i * 40
        x = 20 + (24 if i >= 2 else 0)
        d.add(f'<rect x="{x}" y="{y}" width="230" height="32" rx="6" fill="{PALE[c]}" stroke="{INK}" stroke-width="1.5"/>')
        d.text(x + 12, y + 21, k, 10.5, 800)
        d.text(x + 62, y + 21, v, 11.5, 700)
    d.text(20, 210, 'drag, drop, done: no code', 10.5, 700, fill=DIM)
    d.box(290, 36, 130, 72, fill=PALE[RESP])
    d.text(355, 56, 'GOOD FOR', 10, 800, 'middle', RESP_D)
    d.lines(355, 76, ['quick tools', 'made by those', 'who use them'], 10, 700, 'middle', INK, 13)
    d.box(290, 116, 130, 86, fill=PALE[SMART])
    d.text(355, 136, 'STILL ASK', 10, 800, 'middle', SMART_D)
    d.lines(355, 156, ['whose data?', 'who fixes it?', 'what if it breaks?'], 10, 700, 'middle', INK, 14)
    d.box(20, 228, 400, 50, fill=YELLOW)
    d.lines(220, 248, ['Fewer lines of code, same responsibilities:', 'someone owns it, protects the data and fixes it.'], 11, 700, 'middle', INK, 16)
    return d


@illustration('Id', 'From a pile of paper to checked data',
              'Intelligent document processing takes in scanned documents, reads the text, works out what each one '
              'is (a consent form, an invoice, a letter), pulls out the key details with a confidence score for each, '
              'checks them against rules, and sends anything uncertain to a person.',
              'IDP combines OCR, classification and extraction. The confidence score decides what flows through and '
              'what a person checks. Made-up example.')
def _id():
    d = D(440, 290)
    steps_row(d, 36, [('READ', ['scan + OCR'], PALE[SAFE]), ('SORT', ['consent form?', 'invoice? letter?'], PALE[CREATIVE]),
                      ('EXTRACT', ['the key details'], PALE[SMART])], h=62)
    d.box(20, 112, 400, 120, fill=WHITE)
    d.text(32, 132, 'CONSENT FORM, DETAILS FOUND', 10, 800, fill=DIM)
    rows = [('Pupil', 'Amira Khan', 98), ('Date of trip', '12 March', 95), ('Allergies', 'nuts (?)', 61), ('Signed', 'yes', 97)]
    for i, (k, v, c) in enumerate(rows):
        y = 152 + i * 20
        low = c < 80
        d.text(32, y, k, 11, 700, fill=DIM)
        d.text(150, y, v, 11.5, 800, fill=RED if low else INK)
        d.text(300, y, f'{c}% sure', 10.5, 800, 'start', RED if low else RESP_D)
    d.box(20, 242, 400, 38, fill=YELLOW)
    d.text(220, 266, 'Low confidence (allergies, 61%) → a person checks it', 11.5, 800, 'middle')
    return d


@illustration('Oc', 'From pixels to letters',
              'OCR finds the lines of text in an image, splits them into characters and recognises each one, with a '
              'confidence score. Similar shapes cause mistakes: 0 and O, 1 and l, rn and m. Clean print is read '
              'almost perfectly; handwriting, blur and tilted photos are much harder.',
              'OCR turns pictures of text into text a computer can search and edit. It is very accurate on clear '
              'print, less so on handwriting or poor photos, so important numbers are worth a check.')
def _oc():
    d = D(440, 290)
    d.box(20, 36, 180, 70, fill='#efece4')
    d.add(f'<g transform="rotate(-3 110 71)"><text x="110" y="80" font-family="Georgia,serif" font-size="24" text-anchor="middle" fill="#3a3633">Room 10l</text></g>')
    d.text(110, 124, 'a photo of a door sign', 10, 400, 'middle', DIM)
    d.arrow(202, 71, 234, 71)
    d.box(236, 36, 184, 70, fill=PALE[SAFE])
    d.text(248, 56, 'READ AS', 10, 800, fill=SAFE_D)
    d.text(248, 86, '"Room 10l"', 18, 800)
    d.text(20, 152, 'LOOK-ALIKES THAT TRIP IT UP', 10.5, 800, fill=RED)
    for i, (a, b) in enumerate([('0', 'O'), ('1', 'l'), ('rn', 'm'), ('5', 'S')]):
        x = 20 + i * 102
        d.box(x, 162, 94, 44)
        d.text(x + 47, 191, f'{a}  ·  {b}', 16, 800, 'middle')
    d.box(20, 220, 400, 58, fill=YELLOW)
    d.lines(220, 242, ['Was it "101" or "10l"? Clear print: very accurate.', 'Handwriting, blur and tilt: check important numbers.'], 11, 700, 'middle', INK, 16)
    return d


@illustration('Mi', 'How the work really flows',
              'Process mining reads the records systems already keep, each a case, a step and a time, and draws how '
              'the work really happens. For 400 trip requests it shows most go straight through in a day, but some '
              'wait 3 days for a signature: the bottleneck worth fixing or automating.',
              'Process mining uses event logs, which case, which step, when, to map how work really flows, '
              'including loops and waits nobody planned. Made-up numbers.')
def _mi():
    d = D(440, 290)
    d.box(20, 36, 150, 112, fill=WHITE)
    d.text(30, 54, 'THE RECORDS', 9.5, 800, fill=DIM)
    for i, row in enumerate([('#17', 'request', 'Mon 9:10'), ('#17', 'signed', 'Thu 14:02'), ('#18', 'request', 'Mon 9:30'), ('#18', 'signed', 'Mon 11:15')]):
        y = 74 + i * 18
        d.text(30, y, row[0], 9.5, 800)
        d.text(60, y, row[1], 9.5, 700)
        d.text(162, y, row[2], 9.5, 400, 'end', DIM)
    d.arrow(172, 59, 194, 59)
    for k, x, y in [('request', 196, 44), ('approve', 320, 44), ('sign', 320, 112), ('book', 196, 112)]:
        d.box(x, y, 100, 30, fill=PALE[SMART] if k == 'sign' else PALE[SAFE])
        d.text(x + 50, y + 20, k, 11, 800, 'middle')
    d.arrow(298, 59, 318, 59)
    d.text(308, 40, '400', 9.5, 800, 'middle')
    d.arrow(370, 76, 370, 110)
    d.text(376, 98, '400', 9.5, 800)
    d.arrow(318, 127, 298, 127, stroke=RED, sw=2.5)
    d.text(308, 158, 'waits 3 days', 10.5, 800, 'middle', RED)
    d.box(20, 166, 400, 112, fill=YELLOW)
    d.text(32, 188, 'WHAT IT SHOWS', 10, 800)
    d.lines(32, 210, ['Most requests flow through in under a day.', 'Waiting for a signature adds 3 days: the bottleneck.',
                      'Fix: e-signatures, or a reminder after 1 day.'], 11, 700, 'start', INK, 20)
    return d


@illustration('Be', 'Rules people write, applied the same way every time',
              'A rules engine checks each case against rules people wrote, in order. A book 3 days late: no action. '
              '9 days late: a reminder. 25 days late: a letter home. The same case always gets the same decision, '
              'and anyone can read why. To change the decision, people change the rule.',
              'Rules engines are predictable and easy to explain, unlike models that learn from data. But they only '
              'know the cases someone thought of, so unusual cases need a person.')
def _be():
    d = D(440, 290)
    d.box(20, 36, 400, 102, fill=PALE[SAFE])
    d.text(32, 56, 'THE RULES, WRITTEN BY STAFF', 10, 800, fill=SAFE_D)
    for i, (c, a) in enumerate([('if 21 or more days late', 'letter home'), ('if 7 or more days late', 'send a reminder'), ('otherwise', 'no action')]):
        y = 78 + i * 22
        d.text(32, y, c, 11.5, 700)
        d.text(300, y, '→ ' + a, 11.5, 800)
    cases = [('3 days late', 'no action', WHITE), ('9 days late', 'reminder', PALE[SMART]), ('25 days late', 'letter home', '#F7C1C1')]
    for i, (k, v, f) in enumerate(cases):
        x = 20 + i * 136
        d.box(x, 152, 128, 60, fill=f)
        d.text(x + 64, 174, k, 11, 700, 'middle', DIM)
        d.text(x + 64, 196, v, 12, 800, 'middle')
    d.box(20, 224, 400, 54, fill=YELLOW)
    d.lines(220, 246, ['Same case, same decision, and you can see why.', 'It only knows cases someone thought of.'], 11, 700, 'middle', INK, 16)
    return d


@illustration('St', 'Straight through, or out to a person',
              'Straight-through processing lets a computer handle a whole job, such as a lunch-money top-up, with no '
              'person in the middle: received, checked, paid in, confirmed. Cases that fail a check, such as a name '
              'that doesn\'t match, drop out to a person. The share that goes straight through is the measure.',
              'Straight-through processing removes hand-offs, so routine jobs finish in seconds. The checks along '
              'the way, and a clear route out for exceptions, are what make it safe. Made-up numbers.')
def _st():
    d = D(440, 290)
    steps_row(d, 40, [('RECEIVED', ['£20 top-up'], PALE[SAFE]), ('CHECKED', ['name, account,', 'amount'], PALE[CREATIVE]),
                      ('PAID IN', ['balance', 'updated'], PALE[RESP]), ('CONFIRMED', ['email sent'], PALE[RESP])], h=64, gap=10)
    d.arrow(160, 106, 160, 140, stroke=RED)
    d.box(90, 142, 140, 40, fill='#F7C1C1')
    d.lines(160, 158, ['check fails:', 'to a person'], 10.5, 800, 'middle', INK, 14)
    d.text(260, 124, 'no one in the middle', 10.5, 800, 'start', RESP_D)
    d.head(20, 208, '1,000 top-ups')
    d.bar(20, 218, 368, 24, RESP)
    d.bar(388, 218, 32, 24, '#F7C1C1')
    d.text(30, 235, '920 straight through, in seconds', 11, 800)
    d.text(404, 260, '80 to a person', 10, 800, 'end', RED)
    return d


@illustration('Dw', 'What is inside a "digital worker"',
              'A seller may present a "digital worker" with a name and a face, as if it were a member of staff. '
              'Inside it is software: bots that follow set steps, an AI model, some rules and the logins it has been '
              'given. It cannot take responsibility, so a named person must own what it does.',
              '"Digital worker" is a marketing name for a bundle of bots, AI and rules. It can save time, but it is '
              'not a colleague: a person owns its work, its access and its mistakes.')
def _dw():
    d = D(440, 290)
    d.box(20, 36, 170, 170, fill=PALE[CREATIVE])
    d.text(105, 56, 'THE SALES PAGE', 10, 800, 'middle', CREATIVE_D)
    d.add(f'<circle cx="105" cy="100" r="26" fill="{YELLOW}" stroke="{INK}" stroke-width="1.5"/><circle cx="96" cy="96" r="3" fill="{INK}"/><circle cx="114" cy="96" r="3" fill="{INK}"/><path d="M94 108 q11 9 22 0" fill="none" stroke="{INK}" stroke-width="2"/>')
    d.lines(105, 148, ['"Meet Ava, your', 'new team member"'], 11.5, 800, 'middle', INK, 16)
    d.arrow(192, 120, 222, 120)
    d.box(224, 36, 196, 170)
    d.text(322, 56, 'WHAT IS INSIDE', 10, 800, 'middle')
    for i, (k, c) in enumerate([('bots: set steps', SAFE), ('an AI model', CREATIVE), ('rules', SMART), ('logins it was given', FUTURE)]):
        y = 68 + i * 33
        d.box(238, y, 168, 26, fill=PALE[c])
        d.text(322, y + 18, k, 11, 700, 'middle')
    d.box(20, 220, 400, 58, fill=YELLOW)
    d.lines(220, 242, ['Software, not staff: it can\'t take responsibility.', 'A named person owns its work, access and mistakes.'], 11, 700, 'middle', INK, 16)
    return d


@illustration('Va', 'From "Hey…" to a reminder',
              'A virtual assistant listens for its wake word on the device, then sends what you say to be turned '
              'into text. It works out the intent (set a reminder) and the details (6pm, PE kit), carries out the '
              'action through an app, and answers out loud.',
              'Virtual assistants turn speech into actions on your phone or speaker. A chatbot is mainly for '
              'conversation; a copilot sits inside one app. All of them can mishear, so check important ones.')
def _va():
    d = D(440, 290)
    d.box(20, 36, 400, 36, fill=YELLOW)
    d.text(220, 59, '"Hey, remind me to pack my PE kit at 6pm"', 12, 800, 'middle')
    steps_row(d, 84, [('WAKE WORD', ['heard on', 'the device'], PALE[SAFE]), ('SPEECH → TEXT', ['what you said'], PALE[CREATIVE]),
                      ('INTENT', ['reminder, 6pm,', 'PE kit'], PALE[SMART]), ('ACT + REPLY', ['"Done. 6pm."'], PALE[RESP])], h=66, gap=10)
    d.box(20, 166, 400, 112, fill=WHITE)
    for i, (k, v) in enumerate([('Virtual assistant', 'does tasks for you: reminders, music, searches'),
                                ('Chatbot', 'mainly conversation'), ('Copilot', 'built into one app, suggests as you work')]):
        y = 190 + i * 30
        d.text(32, y, k, 11.5, 800, fill=SMART_D if i == 0 else INK)
        d.text(156, y, v, 11, 400, fill=DIM)
    return d


@illustration('Cp', 'It suggests; you decide',
              'A copilot sits inside the app you are using. As you write an email or code, it suggests the next part. '
              'You accept it, edit it or reject it. In one 2023 experiment, programmers with GitHub Copilot finished '
              'a set task 55.8% faster; you are still responsible for what you keep.',
              'Copilots speed up drafting by suggesting as you work. In one controlled study (Peng and others, 2023) '
              'developers finished a coding task 55.8% faster. Checking what you accept is still your job.')
def _cp():
    d = D(440, 290)
    d.box(20, 36, 400, 96, fill=WHITE)
    d.text(32, 56, 'DRAFTING AN EMAIL', 10, 800, fill=DIM)
    d.text(32, 80, 'Dear parents, the Year 8 trip to the', 12, 700)
    d.text(32, 102, 'Science Museum is on Friday 12 March.', 12, 700, fill=CREATIVE_D, italic=True)
    d.text(32, 122, '↑ the copilot\'s suggestion, until you choose', 9.5, 400, fill=DIM)
    for i, (k, c) in enumerate([('accept', RESP), ('edit', SAFE), ('reject', SMART)]):
        x = 60 + i * 112
        d.box(x, 146, 96, 34, fill=PALE[c])
        d.text(x + 48, 168, k, 12, 800, 'middle')
    d.box(20, 196, 400, 82, fill=YELLOW)
    d.lines(220, 218, ['One 2023 experiment: programmers with a copilot', 'finished a set task 55.8% faster.',
                       'You are still responsible for what you keep.'], 11, 700, 'middle', INK, 17)
    return d


def main():
    os.makedirs(OUT, exist_ok=True)
    index = {}
    for sym, ill in ILLUSTRATIONS.items():
        with open(os.path.join(OUT, sym + '.svg'), 'w', encoding='utf-8') as f:
            f.write(ill['svg'])
        index[sym] = {'title': ill['title'], 'caption': ill['caption']}
    with open(os.path.join(OUT, 'illustrations.json'), 'w', encoding='utf-8') as f:
        json.dump(index, f, ensure_ascii=False, indent=1)
    print(f'{len(index)} illustrations -> {os.path.relpath(OUT, ROOT)}')


if __name__ == '__main__':
    main()
