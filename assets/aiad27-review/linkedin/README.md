# LinkedIn card exports

Six cards for a LinkedIn post: five starter cards exported straight from
`../preview.html` at 2x scale (one per strand), plus a closing brand card.

| File | Source | Content |
|---|---|---|
| `1-safe.png` | deck slide | Safe 01 — Would you tell an AI your secret? |
| `2-smart.png` | deck slide | Smart 01 — What happens when AI acts for you? |
| `3-creative.png` | deck slide | Creative 01 — Who really made it? |
| `4-responsible.png` | deck slide | Responsible 01 — Should AI decide? |
| `5-future.png` | deck slide | Future 01 — What skills must stay human? |
| `6-safe-04.png` | `../linkedin-cover.html` | Closing brand card — lockup + Safe mark + "Keep Humans in the Loop" |

Cards 1–5 regenerate after refreshing the review bundle from SlideForge with:

    node <path-to-export-script> assets/aiad27-review/linkedin

which screenshots the live `.slide` element for each starter question,
rather than a manual crop.

`6-safe-04.png` is not a deck slide — no slide in the bundle renders just a
logo and tagline (the deck's `title` layout shows the question, not the
tagline). It is a standalone card at `../linkedin-cover.html`, rendered
against the live theme (for the `AIAD Sans` font and brand SVGs) and
screenshotted at 1298×732 with Playwright.
