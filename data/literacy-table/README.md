# AI Literacy Periodic Table: content files

Everything on the table at `/ai-literacy-periodic-table/` comes from the five JSON files in this folder. The page, the poster, the homepage promo, the word of the week and the classroom board all read them through `aiad_literacy_data()` in `inc/literacy-table.php`.

To change the content, edit a file, commit it to `main` and deploy. **WordPress → Tools → Periodic Table** checks the files, previews the coming words of the week, and exports everything as JSON or CSV.

This works the same way as the AI Risk Readiness Benchmark, which keeps its questions in `plugins/ai-risk-readiness-benchmark/includes/data/banks/`.

Every file starts with the same three fields:

```json
{ "schema": "aiad.literacy-table/1", "kind": "elements", "about": "What the file holds." }
```

## The files

| File | What it holds |
|---|---|
| `elements.json` | The 142 elements, one record each |
| `issues.json` | The issues the elements raise |
| `compounds.json` | The compounds for the compound challenge |
| `resources.json` | The "Learn more" links |
| `lists.json` | The families, themes, levels, eras, benchmark areas and word-of-the-week order that the other files refer to by key |

### elements.json

```json
{
  "sym": "Bi",
  "name": "Bias",
  "family": "governance",
  "row": 10,
  "col": 6,
  "theme": "responsible",
  "year": 1996,
  "approx": false,
  "older": true,
  "def": "When an AI keeps treating some groups of people unfairly, often because the data it learned from was unbalanced.",
  "talk": "Where could bias in an AI cause real harm, for example in jobs, policing or exam results?",
  "links": ["Fa", "Sd", "Et"],
  "benchmark": "bias_equality"
}
```

| Field | Required? | What it is |
|---|---|---|
| `sym` | Required | The symbol on the tile. It must be unique, and it is also the element's address (`#element-bi`). |
| `name` | Required | The element's name. |
| `def` | Required | The definition, in plain English. |
| `family` | Required | A family key from `lists.json` (`foundations`, `generative`, `predictive`, `agents`, `automation`, `governance` or `infra`). |
| `theme` | Required | An AIAD theme key (`safe`, `smart`, `creative`, `responsible` or `future`). |
| `row`, `col` | Required | Where the tile sits. Columns run 1 to 18. Rows 1 to 8 are the main table, and rows 1 to 3 leave columns 3 to 12 empty for the detail panel. Rows 10 to 12 are the three rows below the table. Element numbers follow reading order, so moving a tile renumbers the elements after it. |
| `year` | Required | When the term came into use. |
| `approx` | Optional | `true` when the year is approximate. |
| `older` | Optional | `true` when the word is much older than AI. The panel then says "Used this way since … (the word is older)". |
| `year_note` | Optional | Where the year comes from. |
| `talk` | Optional | The "Talk about it" question. |
| `links` | Optional | Hand-picked connections, shown by the Connections button. They work both ways, so list each pair once. |
| `benchmark` | Optional | A benchmark area key from `lists.json`. Elements without one fall under AI Literacy. |
| `safeguarding` | Optional | `true` adds the safeguarding note and keeps the element out of the word of the week. |
| `credit` | Optional | For a word added from a reader's suggestion, for example "Suggested by Year 9 at Oakfield School". Use a class, group or first name only, never a surname. |

### issues.json

```json
{ "key": "big-data", "name": "Big Data", "theme": "smart", "def": "…", "talk": "…", "elements": ["Dp", "Ml", "Cd", "Pa"] }
```

`key` must be unique and written in lowercase letters, numbers and hyphens. Each element's panel lists the issues that name it in `elements`.

### compounds.json

```json
{ "name": "Supervised Agent", "elements": ["Ag", "Fn", "Hi"], "theme": "responsible", "level": "secondary", "volatile": false, "what": "…", "example": "…", "loop": "…", "debate": "…" }
```

- **`level`** is `primary`, `secondary` or `further`. Primary compounds use two everyday elements.
- **`volatile: true`** means the system is risky without a human in the loop.
- **The compound's id** is its symbols in lowercase, sorted and joined with hyphens (`ag-fn-hi`). Saved challenge progress uses that id, so two compounds can't have the same elements.

### resources.json

```json
{ "post_type": "resource", "slug": "ai-is-already-here", "kind": "lesson", "elements": ["Ai"], "issues": [] }
```

- **`post_type` and `slug`** identify a post on the site. A post that isn't published is left out of the page.
- **`kind`** is `lesson`, `article`, `tool` or `partner`.
- **Limits:** each element or issue shows at most three links, AIAD's own first.

### lists.json

- **`families`, `themes`, `levels`, `eras` and `benchmark_domains`** are each a list of `{ "key": …, "name": … }`, in display order. Families also have `about`; eras have `from` and `to`.
- **`week_rotation`** is the order the word of the week works through, one element each Monday. Elements with a talk question that aren't in this list go at the end, and safeguarding elements are always skipped. Inserting an element or changing the order changes which word comes up each week, so check the preview on the admin page.

## Adding something

1. **Add the record** to the right file.
2. **Check it** on the admin page (Tools → Periodic Table), or from the command line:

   ```bash
   docker exec ai-awareness-day-wordpress-1 php -r 'define("WP_USE_THEMES",false); require "/var/www/html/wp-load.php"; foreach(aiad_literacy_check() as $c) echo $c[0], ": ", $c[1], "\n";'
   ```

3. **Commit it to `main`.** Hostinger deploys `main`.

**If you add or remove an element,** the "142" on the page, in the launch post and on the LinkedIn carousel also needs updating. The check warns you when the count changes.

**A file with a JSON typo doesn't take the page down.** The page keeps using the last copy that read cleanly, and the admin page shows an error until the file is fixed.

## History

Until November 2026 this content was PHP arrays in `inc/literacy-table.php`. `tools/literacy-table-export.php` wrote it out to these files once and is kept for the record. Don't run it again, because it would read the new JSON-based functions.
