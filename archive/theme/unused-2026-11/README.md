# Unused theme files, November 2026

Moved here by the architecture review (23 November 2026) because nothing in the theme, the plugins or the local
database referred to them. Each keeps its original path below this folder, so it can be moved straight back.

- `patterns/principles.php`: the classic Principles pattern, replaced by `patterns/homepage-principles.php`.
- `templates/theme-page.html`: not chosen by any code or page; the pages use `assets-pack.html` and `press-release.html`.
- `template-parts/front-page/part-benchmark-promo.php`, `section-survey.php`: no longer called (the survey is the
  `aiad/national-survey` block; the promo helpers are in aiad-core's benchmark-content module).
- `assets/css/base/theme-info.css`: an old copy of the style.css header (1.3.7), never loaded.
- `assets/js/aiad-balloons.js`, `assets/js/vendor/balloons-js.esm.js`: the launch balloons, switched off after the
  event, with the script filter that loaded them removed from `inc/setup.php`.
- `assets/images/misc/best-saas-kit.png` and six partner logos not used by any partner page.
- `.env.example`: from the old Next.js app, not this theme.
- `scripts/generate-cc-diagrams.py`: wrote to `assets/images/computing-curriculum/`, which no longer exists.
