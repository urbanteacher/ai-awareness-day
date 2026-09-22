# Favicon

Generated, not hand-drawn. The source of truth is
`assets/brand/aiad27/aiad27-mark.svg` — the AiAd27 lockup squared onto the
chamfered tile — plus the two small-size reductions that live inside
`scripts/export-favicon.mjs`.

Rebuild after any change to the mark:

```
python3 scripts/embed-brand-font.py assets/brand/aiad27/aiad27-mark.svg
AIAD_PLAYWRIGHT_DIR=/path/to/a/tree/with/playwright \
  node scripts/export-favicon.mjs
```

The first step re-inlines the subset of AIAD Sans that the mark carries, so the
SVG keeps rendering in the brand face on its own; the second rasterises it.

| File | Size | Content |
|---|---|---|
| `favicon.png` | 512 | Full mark: AI / AWARENESS / DAY + 2027 |
| `favicon-180.png` | 180 | Same, for `apple-touch-icon` |
| `favicon-32.png` | 32 | Reduction: `AI` + `27` |
| `favicon.ico` | 48/32/16 | Reduction at 48 and 32; `AI` alone at 16 |

Three sizes, three drawings, because the three stacked words turn to noise
below about 64px. Downscaling the 512 into a browser tab is what made the
previous icon unreadable.

## These files only apply when no Site Icon is set

`aiad_favicon_fallback()` in `inc/setup.php` yields to the WordPress Site Icon
(Appearance → Customize → Site Identity), because that is where an editor
expects to change it. If a site has one set, WordPress emits its own `<link>`
tags and nothing here is served — **upload `favicon.png` there instead.**

That is the state of production as of September 2026: the live Site Icon is a
2026-dated crop from the previous brand, so the theme files are dormant behind
it until someone replaces it in the Customizer.
