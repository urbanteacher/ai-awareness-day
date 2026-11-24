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
