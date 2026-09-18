# LinkedIn card exports

Six starter cards exported straight from `../preview.html` at 2x scale, one
per strand plus a bonus Safe card (04/07), for use in a LinkedIn post.

| File | Strand | Slide |
|---|---|---|
| `1-safe.png` | Safe | 01 — Would you tell an AI your secret? |
| `2-smart.png` | Smart | 01 — What happens when AI acts for you? |
| `3-creative.png` | Creative | 01 — Who really made it? |
| `4-responsible.png` | Responsible | 01 — Should AI decide? |
| `5-future.png` | Future | 01 — What skills must stay human? |
| `6-safe-04.png` | Safe | 04 — Where does a secret go when you tell it to something that cannot keep one? |

Regenerate after refreshing the review bundle from SlideForge with:

    node <path-to-export-script> assets/aiad27-review/linkedin

which screenshots the live `.slide` element for each starter question,
rather than a manual crop.
