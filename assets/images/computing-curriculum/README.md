# Computing curriculum illustrations

## Live site (recommended)

Illustrations are **card/block SVG** visuals (no flowcharts). Edit `CARD_SVG` in:

`assets/js/ai-computing-curriculum-illustrations.js`

## Optional: Python + Graphviz (static SVG)

For print-quality or offline assets, run:

```bash
brew install graphviz   # once
pip install graphviz
python3 scripts/generate-cc-diagrams.py
```

Output SVGs land in this folder. Wire them in JS only if you need static images instead of Mermaid.

## Other libraries (reference)

| Tool | Use case | Link |
|------|----------|------|
| Mermaid.js | Flowcharts in browser (used here) | https://github.com/mermaid-js/mermaid |
| Graphviz | Static SVG/PNG from Python | https://graphviz.org/ |
| flowchart.js | Simple JS flowcharts | https://github.com/adamotte/flowchart.js |
| Schemdraw | Python circuit-style diagrams | https://github.com/cdelker/schemdraw |
