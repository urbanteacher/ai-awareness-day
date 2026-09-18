# LinkedIn card exports

An opening brand card, five strand starter cards, and a contact sheet of all
six for a single-image post. Numbered in posting order, brand card first.

| File | Size | Source | Content |
|---|---|---|---|
| `0-all-six-1200x1200.png` | 1200×1200 | `../linkedin-sheet.html` | All six cards, 2×3 on the ink ground |
| `1-lockup.png` | 2560×1440 | `../linkedin-cover.html` | Opening brand card — the `aiad27-lockup.svg` lockup at full size |
| `2-safe.png` | 2560×1440 | deck slide | Safe 01 — Would you tell an AI your secret? |
| `3-smart.png` | 2560×1440 | deck slide | Smart 01 — What happens when AI acts for you? |
| `4-creative.png` | 2560×1440 | deck slide | Creative 01 — Who really made it? |
| `5-responsible.png` | 2560×1440 | deck slide | Responsible 01 — Should AI decide? |
| `6-future.png` | 2560×1440 | deck slide | Future 01 — What skills must stay human? |

1200×1200 is LinkedIn's recommended square for a single-image post. The six
16:9 cards tile wider than tall, so the sheet centres the grid and lets the
leftover height read as margin. Its ground is ink rather than cream because the
lockup card is cream and would otherwise lose its edges.

## Regenerating

Everything above comes from one command, with the local WordPress running:

    AIAD_PLAYWRIGHT_DIR=<dir-with-playwright> node scripts/export-aiad27-cards.mjs

Cards are written first, then the sheet, which reads them back over
`localhost`. Serve these pages over `localhost`, never `file://` — the brand
SVGs will not load cross-origin from a file URL.

Two things the script handles that are easy to get wrong by hand:

- **Slides are captured unscaled.** They are authored at 1280×720 and the
  review grid shows them through a `transform: scale()`. Screenshotting a tile
  captures a fractionally-scaled box, which bled page background and
  drop-shadow into the bottom edge and clipped the slide's own progress bar.
  Each slide is cloned into a clean host at scale 1 instead, giving pixel-exact
  16:9 at 2× resolution.
- **Starters are matched on slide text,** not on an index, so the export
  survives the bundle being reordered or renumbered.

`1-lockup.png` is not a deck slide — no slide in the bundle renders just the
logo and tagline (the deck's `title` layout shows the strand question). It
places `assets/brand/aiad27/aiad27-lockup.svg`, the real lockup with wordmark,
rule and tagline in one asset, scaled to fill the card. That lockup's type is
right-anchored inside its viewBox, so its left quarter is empty and the card
offsets the image to centre the visible type rather than its box — see the
comment in `../linkedin-cover.html`, and redo that measurement if the lockup's
wording changes.
