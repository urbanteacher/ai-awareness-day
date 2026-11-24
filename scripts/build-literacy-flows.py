#!/usr/bin/env python3
"""
Copies the animated flows from the AI Under the Hood courses (assets/courses/*/index.html, their `var FLOWS={...}`)
into plugins/aiad-core/data/literacy-table/flows.json, so the AI Literacy Periodic Table can play them with the same
engine (assets/courses/shared/flow.js) under "See how it works".

ELEMENTS below picks, for each element, the one flow that explains it best. A course chapter tags several elements,
but its flow often shows only one or two of them, so the choice is made here by hand rather than from the tags.

    python3 scripts/build-literacy-flows.py
"""
import json
import os
import re

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
COURSES = {
    'sand-to-sentence': 'From Sand to Sentence',
    'answer-to-action': 'From Answer to Action',
    'agent-to-team': 'From Agent to Team',
}
PART = {'sand-to-sentence': 1, 'answer-to-action': 2, 'agent-to-team': 3}

# element symbol -> flow id (each flow id is unique across the three courses)
ELEMENTS = {
    # Part 1: chips and language models
    'Ll': 'journey', 'Tk': 'journey', 'Tf': 'attn', 'If': 'attn', 'Te': 'attn', 'Ha': 'hallu',
    'Gp': 'memory', 'Tp': 'memory', 'Pm': 'memory', 'Cd': 'energy', 'Ms': 'batch', 'So': 'supply',
    # Part 2: RAG, tools, MCP and agents
    'Cb': 'limits', 'Rg': 'rag', 'Vd': 'rag', 'Fn': 'tool', 'Ap': 'perm', 'Mc': 'mcp',
    'Ag': 'loop', 'Lp': 'loop', 'Ra': 'loop', 'Me': 'loop', 'Au': 'inject', 'Ac': 'pick',
    # Part 3: agent design patterns
    'Cw': 'overload', 'Wo': 'chain', 'Ev': 'evalopt', 'Ct': 'evalopt', 'Ma': 'help', 'Or': 'help', 'Pl': 'help',
    'Hi': 'checks', 'Gr': 'checks',
}

# Compounds (id = their symbols, lower case, sorted, joined by "-") whose best-covered element would pick a poor fit:
# a flow id to use instead, or None for no animation. The rest play the flow that covers most of their elements, or
# on a tie the later element's (literacy-table.js compoundFlowId()).
COMPOUNDS = {
    'cb-ll-tf': 'journey',      # General Chatbot: the whole journey of one question, not attention alone
    'gp-tf-tk-tr': 'journey',   # LLM Factory
    'cb-pv': None,              # Think Before You Share: privacy, which no course flow shows
    'ap-lc-tg-wh': 'chain',     # No-Code Workflow: a workflow with a check
    'cv-ed-pl': None,           # Robot Vacuum: no course flow is about robots
    'rl-sl-tp': None,           # Game-Playing Champion: no course flow is about reinforcement learning
}

# The courses' side-by-side animations ("Play both", AIADFlow.compare): compare id -> element symbol.
COMPARES = {
    'hcheck': 'Hi',   # Part 1: the same wrong answer, without and with a check
    'wfvs': 'Wo',     # Part 3: a workflow and an agent
}


def main():
    flows, compares = {}, {}
    for slug, title in COURSES.items():
        html = open(os.path.join(ROOT, 'assets/courses', slug, 'index.html'), encoding='utf-8').read()
        specs = json.loads(re.search(r'var FLOWS=(\{.*?\});\n', html, re.S).group(1))
        chapters = {}
        for section in re.split(r'<section', html):
            ch = re.search(r'id="(ch\d+)"', section)
            heading = re.search(r'<h2[^>]*>(.*?)</h2>', section, re.S)
            for fid in re.findall(r'data-flow="([^"]+)"', section):
                chapters[fid] = (ch.group(1) if ch else '', re.sub(r'<[^>]+>', '', heading.group(1)).strip() if heading else '')
        at = html.find('var C=')
        found = json.JSONDecoder().raw_decode(html, at + len('var C='))[0] if at >= 0 else {}
        for cid, spec in found.items():
            if cid not in COMPARES:
                continue
            for section in re.split(r'<section', html):
                if f'data-compare="{cid}"' in section:
                    ch = re.search(r'id="(ch\d+)"', section)
                    heading = re.search(r'<h2[^>]*>(.*?)</h2>', section, re.S)
                    compares[cid] = {
                        'course': slug, 'courseTitle': title, 'part': PART[slug],
                        'chapter': ch.group(1) if ch else '', 'chapterNumber': int(ch.group(1)[2:]) if ch else None,
                        'chapterTitle': re.sub(r'<[^>]+>', '', heading.group(1)).strip() if heading else '',
                        'spec': spec,
                    }
        for fid, spec in specs.items():
            ch, heading = chapters.get(fid, ('', ''))
            flows[fid] = {
                'course': slug, 'courseTitle': title, 'part': PART[slug],
                'chapter': ch, 'chapterTitle': heading,
                'chapterNumber': int(ch[2:]) if ch else None,
                'spec': spec,
            }
    used = set(ELEMENTS.values()) | {f for f in COMPOUNDS.values() if f}
    missing = sorted(f for f in used if f not in flows)
    if missing:
        raise SystemExit('No such flow: ' + ', '.join(missing))
    if sorted(compares) != sorted(COMPARES):
        raise SystemExit('No such comparison: ' + ', '.join(sorted(set(COMPARES) - set(compares))))
    out = {
        'about': 'Generated by scripts/build-literacy-flows.py from the AI Under the Hood courses. Do not edit by hand.',
        'elements': ELEMENTS,
        'compounds': COMPOUNDS,
        'flows': {k: v for k, v in flows.items() if k in used},
        'compareElements': {sym: cid for cid, sym in COMPARES.items()},
        'compares': compares,
    }
    path = os.path.join(ROOT, 'plugins/aiad-core/data/literacy-table/flows.json')
    with open(path, 'w', encoding='utf-8') as f:
        json.dump(out, f, ensure_ascii=False, indent=1)
    print(f'{len(ELEMENTS)} elements, {len(out["flows"])} flows, {len(compares)} comparisons -> {os.path.relpath(path, ROOT)}')


if __name__ == '__main__':
    main()
