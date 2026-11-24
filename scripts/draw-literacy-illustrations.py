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
