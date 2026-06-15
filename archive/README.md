# Repository archive

Non-production reference material moved here during repo cleanup. **Nothing in this folder is deleted** — it is preserved for history, design reference, and one-off migration scripts.

WordPress still reaches some theme paths under `archive/theme/` (generators, bundled WXR import). Those URLs are wired in `inc/generator-embed.php` and `inc/import-export.php`.

## Layout

| Folder | Contents |
|--------|----------|
| [`airb/`](airb/README.md) | Legacy AIRB plugin tier PHP, pre-1.48.0 JS snapshot, export CLI |
| [`docs/`](docs/) | All project markdown (benchmark specs, audits, theme docs, asset readmes) |
| [`demo/`](demo/) | Vite UI demo app (`npm run dev` from `archive/demo/`) + `Benchmark.jsx` prototype |
| [`theme/`](theme/) | HTML generators, import snippets, paste-friendly embeds, `template.html` |

## Docs index

- **Theme install:** [`docs/theme-installation.md`](docs/theme-installation.md)
- **Theme reviews:** [`docs/theme-docs/`](docs/theme-docs/)
- **Benchmark:** [`docs/benchmark-architecture.md`](docs/benchmark-architecture.md), [`docs/benchmark-content-strategy.md`](docs/benchmark-content-strategy.md), [`docs/benchmark-resource-hub-content.md`](docs/benchmark-resource-hub-content.md)
- **Survey / audit:** [`docs/national-survey-questions.md`](docs/national-survey-questions.md), [`docs/audit.md`](docs/audit.md)

## Running the UI demo

```bash
cd archive/demo
npm install
npm run dev
```

Build output goes to `archive/demo/dist/` (gitignored).
