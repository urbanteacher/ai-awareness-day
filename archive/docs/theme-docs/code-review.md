# Code Review: AI Awareness Day WordPress Theme

**Reviewer:** Claude  
**Date:** 23 February 2026  
**Version reviewed:** 1.3.2  
**Files reviewed:** All 16 uploaded theme files + screenshots

---

## Executive Summary

This is a well-structured, thoughtfully built WordPress theme with strong fundamentals — proper escaping throughout, good i18n support, clean separation of concerns via `/inc/` modules, and solid accessibility practices. The codebase is clearly maintained by someone who knows WordPress well.

That said, there are meaningful improvements to make around **security hardening**, **performance**, **code duplication**, **template complexity**, and a few **WordPress best practices**. Below is a prioritised breakdown.

---

## 🔴 High Priority — Security & Correctness

### 1. Missing `wp_unslash()` on `$_GET` inputs (archive-resource.php, archive-featured_resource.php)

In `archive-partner.php` you correctly use `wp_unslash()`:

```php
// ✅ archive-partner.php (line 22)
$type_filter = isset( $_GET['partner_type'] ) ? sanitize_text_field( wp_unslash( $_GET['partner_type'] ) ) : '';
```

But in `archive-resource.php` and `archive-featured_resource.php`, every `$_GET` access omits it:

```php
// ❌ archive-resource.php (lines 51-55)
$type_filter = isset($_GET['resource_type']) ? sanitize_text_field($_GET['resource_type']) : '';
$principle_filter = isset($_GET['principle']) ? sanitize_text_field($_GET['principle']) : '';
$duration_filter = isset($_GET['duration']) ? sanitize_text_field($_GET['duration']) : '';
$activity_filter = isset($_GET['activity_type']) ? sanitize_text_field($_GET['activity_type']) : '';
$key_stage_filter = isset($_GET['key_stage']) ? sanitize_text_field($_GET['key_stage']) : '';
```

**Fix:** Add `wp_unslash()` around every `$_GET` value before sanitising — WordPress may add magic-quoted slashes.

### 2. Taxonomy filter values are not validated against known slugs (archive-resource.php, archive-featured_resource.php)

In `archive-partner.php` you validate the filter value against actual term slugs before using it:

```php
// ✅ archive-partner.php
$valid_slugs = wp_list_pluck( $partner_types, 'slug' );
if ( $type_filter !== '' && ! in_array( $type_filter, $valid_slugs, true ) ) {
    $type_filter = '';
}
```

Neither `archive-resource.php` nor `archive-featured_resource.php` do this. While `sanitize_text_field` prevents XSS, passing arbitrary strings into `tax_query` means WordPress will run DB queries for terms that don't exist.

**Fix:** Validate each filter value against the actual taxonomy term slugs (as you already do in `archive-partner.php`), or at minimum use `term_exists()`.

### 3. `meta_query` with `LIKE` comparison for key stage (archive-resource.php, line 101)

```php
$args['meta_query'] = array(
    array(
        'key' => '_aiad_key_stage',
        'value' => $key_stage_filter,
        'compare' => 'LIKE',
    ),
);
```

`LIKE` is used here, presumably because key stages are stored as serialized arrays. This has two problems: (a) `LIKE` queries on meta values are slow without a dedicated index, and (b) partial matches could return false positives (e.g. searching for `ks1` could match `ks10` if that ever existed).

**Recommendation:** If key stages are stored as serialized arrays, this is the standard WordPress approach — just be aware of the performance cost. If they're simple string values, switch to `'compare' => '='`. Long-term, consider migrating key stages to a proper taxonomy for better query performance.

### 4. `theme.json` has a misleading colour definition

```json
{ "slug": "green-600", "color": "#171717", "name": "Accent" }
```

The slug says `green-600` but the actual color is `#171717` (near-black), identical to `gray-900`. This is almost certainly a copy-paste error or placeholder that was never updated. Any block editor user picking "Accent" gets black, not green.

**Fix:** Update to the actual green used in the design (appears to be around `#4ade80` or `#22c55e` based on the screenshot).

---

## 🟡 Medium Priority — Performance & Architecture

### 5. Massive code duplication across archive templates

`archive-resource.php` and `archive-featured_resource.php` share roughly 80% identical code — the filter form, theme badges, resource card rendering, and query construction. `single-resource.php` also repeats the badge/term resolution logic from the archives.

**Recommendation:** Extract shared code into reusable template parts and helper functions:

- `template-parts/resource-filters.php` — the filter form (parameterised by post type)
- `template-parts/resource-card.php` — the card markup (already used elsewhere based on folder structure)
- `template-parts/theme-badges.php` — the badge row
- A helper like `aiad_build_resource_query_args( $post_type, $filters )` in `/inc/helpers.php`

This would reduce the archive templates from ~300 lines each to ~50 lines, and eliminate the risk of fixing a bug in one place but not the other.

### 6. `posts_per_page => 200` with no pagination (archive-resource.php, archive-featured_resource.php)

Both resource archives fetch up to 200 posts in a single query with no pagination. The `archive-partner.php` has a similar issue at 100. For a campaign site this is likely fine now, but if content grows this will cause slow page loads and high memory usage.

**Recommendation:** Add `paginate_links()` support, or at least add a comment-reminder with a threshold (you've done this partially in `archive-partner.php`).

### 7. Repeated `get_terms()` calls within the same template

In `archive-resource.php`, `get_terms()` is called multiple times for the same taxonomies — once for theme badges and again for filter dropdowns. WordPress does cache these internally via the object cache, so the DB impact is minimal, but it's still worth consolidating for clarity.

### 8. `single-resource.php` is 434 lines — too much logic in a template

This file handles: term resolution, badge mapping, meta field retrieval and normalisation, PPTX embed URL construction, conditional section rendering for 10+ different content sections, download tracking, share buttons, and print functionality.

**Recommendation:** Move data preparation into a helper function (e.g. `aiad_get_resource_view_data( $post_id )`) that returns a structured array. The template then just handles rendering. This improves testability and readability significantly.

### 9. Inline styles in several templates

`index.php`, `page.php`, `single-partner.php`, and `404.php` all use inline `style` attributes:

```php
<main id="main" role="main" style="padding-top: 100px;">
<figure style="margin: 2rem auto; max-width: 240px;">
<p style="margin-top: 1.5rem;">
```

Given that the theme already has a modular CSS architecture in `/assets/css/`, these should be moved there. Inline styles can't be overridden by child themes without `!important`.

---

## 🟢 Low Priority — Code Quality & Best Practices

### 10. Duplicate `aria-hidden="true"` attributes

In both `archive-resource.php` (line 40) and `archive-featured_resource.php` (line 38):

```html
<img src="..." alt="" aria-hidden="true" class="theme-link__badge-img" aria-hidden="true" />
```

The `aria-hidden="true"` is specified twice. While browsers handle this gracefully, it's technically invalid HTML and will flag in validators.

### 11. Inconsistent coding style between files

- `archive-partner.php` and `single-partner.php` use WordPress-style spacing: `if ( $condition )` with spaces inside parentheses
- `archive-resource.php` uses PSR-style: `if ($condition)` without spaces
- Some files use `endif;` alternative syntax, others use braces

**Recommendation:** Run the entire theme through PHP_CodeSniffer with the WordPress coding standards ruleset (`phpcs --standard=WordPress`) and normalise. This is cosmetic but important for maintainability, especially if others contribute.

### 12. The `$attr` escaping comment pattern

In `archive-partner.php`:

```php
$attr = $url ? ' href="' . esc_url( $url ) . '" target="_blank" rel="noopener"' : '';
// ...
echo $attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $attr is built from esc_url
```

This works and is safe, but a cleaner pattern would be to use `printf()` or separate the attributes rather than building an HTML string in a variable and suppressing the PHPCS warning.

### 13. `functions.php` loads admin files without `is_admin()` guard on all includes

```php
if ( is_admin() ) {
    require_once $aiad_dir . '/admin/class-aiad-homepage-editor.php';
    // ...
}
// These load on every request:
require_once $aiad_dir . '/inc/ajax-handlers.php';
require_once $aiad_dir . '/inc/timeline.php';
require_once $aiad_dir . '/inc/sharing.php';
```

`ajax-handlers.php` may need to load on front-end for logged-out AJAX (depending on implementation), but `timeline.php` and `sharing.php` might only be needed on specific templates.

**Recommendation:** Review whether any `/inc/` files can be conditionally loaded. For a theme this size the performance difference is negligible, but it's good practice.

### 14. `composer.json` specifies PHP 8.0 but `README.md` says PHP 7.4+

```json
// composer.json
"require": { "php": ">=8.0" }
```

```markdown
<!-- README.md -->
## Requirements
- PHP 7.4+
```

And `style.css` says `Requires PHP: 8.0`. The README should be updated to match.

### 15. `front-page.php` is clean and minimal — good pattern

The front page template delegates entirely to `aiad_get_front_page_sections()` and `get_template_part()`. This is an excellent pattern that makes section ordering configurable. The other templates would benefit from a similar approach.

### 16. `header.php` — logo fallback chain is well-handled

The logo resolution logic (custom logo → header logo → hero logo → text fallback) is thorough and each branch is properly escaped. Minor suggestion: the `$defaults` variable is fetched via `aiad_get_customizer_defaults()` — ensure this function uses static caching to avoid repeated option lookups across `header.php` and `footer.php`.

### 17. Footer social links could use a loop pattern

Currently the footer has separate blocks for LinkedIn and Instagram with duplicated SVG/link markup. If more social platforms are added, this becomes harder to maintain.

**Recommendation:** Store social links in an array and loop:

```php
$social_links = array(
    'linkedin'  => array( 'url' => get_theme_mod( 'aiad_linkedin', $defaults['aiad_linkedin'] ), 'label' => 'LinkedIn', 'svg' => '...' ),
    'instagram' => array( 'url' => get_theme_mod( 'aiad_instagram', $defaults['aiad_instagram'] ), 'label' => 'Instagram', 'svg' => '...' ),
);
foreach ( $social_links as $link ) { /* render */ }
```

---

## Summary Table

| # | Issue | Severity | Effort |
|---|-------|----------|--------|
| 1 | Missing `wp_unslash()` on `$_GET` | 🔴 High | Low |
| 2 | Unvalidated taxonomy filter values | 🔴 High | Low |
| 3 | `LIKE` meta_query for key stages | 🔴 Medium-High | Medium |
| 4 | Wrong accent colour in theme.json | 🔴 High | Trivial |
| 5 | Archive template duplication (~80%) | 🟡 Medium | Medium |
| 6 | No pagination on 200-post queries | 🟡 Medium | Medium |
| 7 | Repeated `get_terms()` calls | 🟡 Low-Medium | Low |
| 8 | `single-resource.php` too complex | 🟡 Medium | High |
| 9 | Inline styles in templates | 🟡 Low-Medium | Low |
| 10 | Duplicate `aria-hidden` attributes | 🟢 Low | Trivial |
| 11 | Inconsistent coding style | 🟢 Low | Medium |
| 12 | Suppressed PHPCS warning pattern | 🟢 Low | Low |
| 13 | Unconditional file includes | 🟢 Low | Low |
| 14 | PHP version mismatch in docs | 🟢 Low | Trivial |
| 15–17 | Positive patterns / minor suggestions | 🟢 Info | — |

---

## Overall Assessment

**Rating: B+ / Good**

The theme is well-built for its purpose, with strong security hygiene (proper escaping everywhere), good accessibility markup, and a sensible architecture. The main areas for improvement are consistency (applying the same validation patterns you use in one file to all files), reducing duplication across the archive templates, and breaking down the monolithic `single-resource.php`. The security items (#1, #2, #4) should be addressed promptly as they're low-effort, high-impact fixes.

The modular front-page pattern via `aiad_get_front_page_sections()` is particularly well done and could serve as a model for how the resource templates are refactored.
