# Security & Scalability Audit — AI Awareness Day Theme (inc/ structure)

**Date:** 2026-02-19  
**Scope:** Post-refactor theme layout (`functions.php` + `inc/` + `admin/`).  
**Method:** Codebase review against common WordPress security practices and scalability patterns.

---

## 1. Security

### 1.1 Direct access (ABSPATH)

- **Status:** OK  
- **Detail:** Every PHP entry point guards with `if ( ! defined( 'ABSPATH' ) ) { exit; }`:
  - `functions.php`, `inc/setup.php`, `inc/helpers.php`, `inc/post-types.php`, `inc/customizer.php`, `inc/validation.php`, `inc/ajax-handlers.php`, `inc/meta-boxes.php`, `inc/admin-columns.php`, `inc/import-export.php`, `admin/class-aiad-homepage-editor.php`.

### 1.2 Authentication & authorization

- **Admin / sensitive actions:**  
  - **Meta box saves:** Nonce + `current_user_can( 'edit_post', $post_id )` in `inc/meta-boxes.php` for partner, featured resource, resource details, and content sections.  
  - **Import/Export:** `current_user_can( 'manage_options' )` before rendering or processing; export/generate/import use `check_admin_referer()` with named nonces.  
  - **Homepage editor:** `current_user_can( 'edit_theme_options' )` and `wp_verify_nonce( ..., self::NONCE_ACTION )` before save; admin bar link gated by same capability.  
- **Post type meta (REST):** Resource/partner/featured meta use `'auth_callback' => function() { return current_user_can( 'edit_posts' ); }` in `inc/post-types.php`.

### 1.3 CSRF (nonces)

- **Status:** OK  
- **Detail:**  
  - Contact form AJAX: `check_ajax_referer( 'aiad_contact_nonce', 'nonce' )` in `inc/ajax-handlers.php`.  
  - Resource filter AJAX: `wp_verify_nonce( ..., 'aiad_filter_nonce' )` and sanitized `$_POST` params.  
  - All admin forms (meta boxes, import, homepage editor) use nonces and are verified before any state change.

### 1.4 Input handling

- **Status:** OK  
- **Detail:**  
  - `$_POST` / `$_GET`: Values sanitized with `sanitize_text_field`, `sanitize_textarea_field`, `esc_url_raw`, `sanitize_email`, or `array_map( 'sanitize_text_field', ... )` before use or storage.  
  - Key stage / taxonomy: Filter params restricted to allowed values (e.g. `array_intersect` with `aiad_key_stage_options()`).  
  - Post type for filter limited to `resource` or `featured_resource`.  
  - No raw `$wpdb->query()` or user input in SQL; all queries via `WP_Query` / WordPress APIs.

### 1.5 File upload (WXR import)

- **Status:** OK  
- **Detail:**  
  - Import page and form only for `manage_options`; form protected by `check_admin_referer( 'aiad_resource_import', 'aiad_resource_import_nonce' )`.  
  - File path used is `$_FILES['aiad_wxr_file']['tmp_name']` (server-controlled upload temp path), not user-supplied path.  
  - WXR is parsed with `simplexml_load_file()`; no `include`/`eval` of file content.  
  - Accept attribute on input is `.xml` (UX only; server does not rely on it for security).

### 1.6 Output (XSS)

- **Status:** OK  
- **Detail:** Templates and admin UI use `esc_html()`, `esc_attr()`, `esc_url()`, or `wp_kses_post()` for dynamic output. No unescaped `echo` of request or DB content in the reviewed paths.

### 1.7 Minor hardening opportunity

- **`aiad_track_download` (inc/ajax-handlers.php):**  
  - Registered for both `wp_ajax_*` and `wp_ajax_nopriv_*`, so unauthenticated users can call it.  
  - It only increments `_aiad_download_count` for a valid `resource` post (after `absint( $post_id )` and `get_post_type` check).  
  - **Risk:** Low (stats inflation only). **Optional:** Require login (`wp_ajax_*` only) or add a nonce if you want to restrict who can bump counts.

---

## 2. Scalability

### 2.1 Load order and conditional loading

- **Status:** OK  
- **Detail:**  
  - **Front:** Only `inc/setup.php`, `helpers.php`, `post-types.php`, `customizer.php`, `validation.php`, `ajax-handlers.php` are loaded on every request.  
  - **Admin-only:** `admin/class-aiad-homepage-editor.php`, `inc/meta-boxes.php`, `inc/admin-columns.php`, `inc/import-export.php` loaded only when `is_admin()`.  
  - No circular requires; `functions.php` is a single, linear include list.  
  - Heavy admin logic (meta boxes, import/export, columns) not loaded on frontend, which keeps front requests light.

### 2.2 Database and queries

- **Status:** Mostly OK; one tuning point.  
- **Detail:**  
  - All listing/archive logic uses `WP_Query` with tax_query/meta_query; no ad‑hoc SQL.  
  - **Resource filter AJAX:** Uses `'posts_per_page' => -1`. If the number of published resources grows large (e.g. hundreds or thousands), this can become slow and memory-heavy.  
  - **Recommendation:** Cap results (e.g. `posts_per_page => 100` or 200) or add pagination and document the limit. Archive template already uses `-1`; consider a max there too if the archive can grow very large.

### 2.3 Caching and performance

- **Status:** Reasonable for a theme.  
- **Detail:**  
  - Filter count version bump on save (`aiad_bump_filter_counts_version`) is appropriate for cache busting.  
  - No theme-level object cache usage; that can be added later if needed (e.g. for filter counts or heavy queries).  
  - Scripts/styles enqueued with version and conditional loading (e.g. resource filter script only on resource archives).

### 2.4 Structure and maintainability

- **Status:** Good for scalability of the codebase.  
- **Detail:**  
  - Clear separation: setup, helpers, post types, customizer, validation, AJAX, meta boxes, admin columns, import/export.  
  - Single responsibility per file makes it easier to add features or optimise (e.g. add caching in one place).  
  - No huge, monolithic file; largest files are focused (e.g. meta-boxes, import-export) and can be split further later if they grow.

---

## 3. Summary

| Area              | Result | Notes |
|-------------------|--------|--------|
| ABSPATH guards    | OK     | All entry points protected. |
| Auth/capabilities | OK     | Admin and meta actions gated. |
| Nonces / CSRF     | OK     | Forms and AJAX protected. |
| Input sanitization| OK     | POST/GET sanitized; allowed sets enforced. |
| File upload       | OK     | Import uses server temp path; XML only. |
| Output escaping  | OK     | Esc/kses used in templates and admin. |
| SQL / injection   | OK     | No raw SQL with user input. |
| Load order        | OK     | Admin-only code not loaded on front. |
| Query scaling     | Tune   | Filter/archive use `-1`; consider a cap or pagination. |
| Code structure    | OK     | Modular, easy to extend and optimise. |

**Conclusion:** The new structure shows no critical security issues and is in line with common WordPress security and scalability practices. The only recommended change is to limit or paginate the resource filter (and optionally the archive) if the number of resources can grow large; optionally harden `aiad_track_download` if you care about strict control over download counting.
