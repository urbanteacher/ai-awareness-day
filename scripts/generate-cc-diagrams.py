#!/usr/bin/env python3
"""
Generate static SVG flowcharts for the computing curriculum challenge.

Uses Graphviz (https://graphviz.org/) — install:
  macOS:   brew install graphviz
  Ubuntu:  sudo apt install graphviz graphviz-dev
  pip:     pip install graphviz

Run from theme root:
  python3 scripts/generate-cc-diagrams.py

Output: assets/images/computing-curriculum/*.svg

These are optional fallbacks; the live widget uses Mermaid.js in the browser.
"""

from __future__ import annotations

import os
from pathlib import Path

try:
    import graphviz
except ImportError as exc:
    raise SystemExit(
        "Install graphviz: pip install graphviz (and system graphviz — see script header)"
    ) from exc

THEME_ROOT = Path(__file__).resolve().parents[1]
OUT_DIR = THEME_ROOT / "assets" / "images" / "computing-curriculum"

DIAGRAMS: dict[str, str] = {
    "bebot": """
        digraph {
            graph [bgcolor="#1e1e1e" pad=0.3]
            node [shape=box style=filled fillcolor="#1D9E75" fontcolor=white fontname="Arial"]
            edge [color="#888888"]
            Forward -> Forward2 [label=" "]
            Forward2 -> Turn [label="Turn left"]
            Turn -> Forward3 [label=" "]
            Forward [label="Forward"]
            Forward2 [label="Forward"]
            Turn [label="Turn left"]
            Forward3 [label="Forward"]
        }
    """,
    "recipe": """
        digraph {
            graph [bgcolor="#1e1e1e"]
            node [shape=box style=filled fillcolor="#1D9E75" fontcolor=white]
            edge [color="#888888"]
            s1 [label="1. Get bread"]
            s2 [label="2. Add filling"]
            s3 [label="3. Serve"]
            s1 -> s2 -> s3
        }
    """,
    "loop": """
        digraph {
            graph [bgcolor="#1e1e1e"]
            node [shape=box style=filled fillcolor="#534AB7" fontcolor=white]
            edge [color="#888888"]
            repeat [label="repeat 10" shape=diamond]
            move [label="Move sprite"]
            repeat -> move -> repeat
        }
    """,
}


def main() -> None:
    OUT_DIR.mkdir(parents=True, exist_ok=True)
    for name, src in DIAGRAMS.items():
        dot = graphviz.Source(src.strip())
        path = dot.render(
            filename=name,
            directory=str(OUT_DIR),
            format="svg",
            cleanup=True,
        )
        print(f"Wrote {path}")
    print(f"\nDone. {len(DIAGRAMS)} SVG(s) in {OUT_DIR}")


if __name__ == "__main__":
    main()
