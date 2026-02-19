# Product Requirements Document (PRD)
# AI Awareness Day — Theme Upgrade v2.0
# Six Improvements to the Existing WordPress Theme

> **For Cursor AI:** This PRD describes upgrades to an EXISTING WordPress theme called `ai-awareness-day`. The theme is already live and working. DO NOT rebuild anything from scratch — extend and improve what exists. Read this entire document, then open the project in Cursor and work through the upgrades in order. Each upgrade references the specific files and functions that need changing.

---

## 0. Existing Codebase Summary

**What already exists (DO NOT recreate):**

| Component | Location | What it does |
|-----------|----------|-------------|
| Theme setup, CPTs, taxonomies | `functions.php` lines 1–210 | Registers `resource`, `partner`, `featured_resource`, `form_submission` CPTs and `resource_type`, `resource_principle`, `resource_duration`, `partner_type` taxonomies |
| Customizer settings | `functions.php` lines 808–1056 | Hero, Campaign, Badges, YouTube, Display Board, Contact, Social sections |
| Meta boxes | `functions.php` lines 326–663 | Partner URL, Partner Stats, Featured Resource URL/Org, Resource Download |
| Term seeding | `functions.php` lines 215–321 | Seeds resource types, principles, durations, partners, partner resources, lesson starters |
| AJAX contact form | `functions.php` lines 1084–1200+ | Handles form submissions, saves to `form_submission` CPT, emails admin |
| Homepage | `front-page.php` (791 lines) | 7 sections: Hero, Campaign/Reach, Video, Principles, Aim, Toolkit, Get Involved |
| Resource archive | `archive-resource.php` | Filters by `resource_type`, `resource_principle`, `resource_duration` via GET params, full page reload |
| Resource single | `single-resource.php` | Shows resource detail with download button |
| Featured resources archive | `archive-featured_resource.php` | External partner resources with same filtering |
| Templates | `header.php`, `footer.php`, `page.php`, `index.php`, `404.php` | Standard theme templates |
| Styles | `style.css` | Full theme styles including resource cards, filters, principles grid |
| JS | `assets/js/main.js` | Animations, nav toggle, contact form AJAX |

**Existing taxonomy slugs (use these exact strings):**
- `resource_type` — Lesson Starter, Lesson Activity, Assembly
- `resource_principle` — Safe, Smart, Creative, Responsible, Future
- `resource_duration` — `5-min-lesson-starters`, `15-20-min-tutor-time`, `20-min-assemblies`, `30-45-min-after-school`
- `partner_type` — Teacher, Sponsor, School, Tech Company

**Existing meta keys:**
- `_resource_download_url` — on `resource` CPT
- `_partner_url`, `_partner_stats` — on `partner` CPT
- `_featured_resource_url`, `_featured_resource_org_name`, `_featured_resource_org_url` — on `featured_resource` CPT

---

## Upgrade 1: Activity Type Taxonomy (NEW FILTER DIMENSION)

### What
Add a new taxonomy `activity_type` to the `resource` and `featured_resource` post types. This gives teachers the third filter dimension they asked for: **Format → Theme → Activity Type**.

### Implementation

**In `functions.php`, inside `aiad_register_post_types()` (after the `resource_duration` registration, around line 123):**

Add:
```php
// Taxonomy: Activity Type (Discussion, Quiz, Video, Hands-On, etc.)
register_taxonomy( 'activity_type', array( 'resource', 'featured_resource' ), array(
    'labels'            => array(
        'name'          => __( 'Activity Types', 'ai-awareness-day' ),
        'singular_name' => __( 'Activity Type', 'ai-awareness-day' ),
        'add_new_item'  => __( 'Add New Activity Type', 'ai-awareness-day' ),
        'description'   => __( 'What kind of activity is this? (Discussion, Quiz, Hands-on, etc.)', 'ai-awareness-day' ),
    ),
    'hierarchical'      => true,
    'show_ui'           => true,
    'show_admin_column' => true,
    'show_in_rest'      => true,
) );
```

**Seed default terms** — add a new function after `aiad_duration_terms()` (around line 265):

```php
function aiad_activity_type_terms(): void {
    if ( get_option( 'aiad_activity_type_terms_seeded' ) ) {
        return;
    }
    $types = array(
        'Discussion'    => 'discussion',
        'Quiz'          => 'quiz',
        'Video'         => 'video',
        'Hands-On'      => 'hands-on',
        'Role Play'     => 'role-play',
        'Investigation' => 'investigation',
        'Creative Task' => 'creative-task',
        'Game'          => 'game',
        'Presentation'  => 'presentation',
        'Reflection'    => 'reflection',
    );
    foreach ( $types as $name => $slug ) {
        if ( ! term_exists( $slug, 'activity_type' ) ) {
            wp_insert_term( $name, 'activity_type', array( 'slug' => $slug ) );
        }
    }
    update_option( 'aiad_activity_type_terms_seeded', true );
}
add_action( 'init', 'aiad_activity_type_terms', 22 );
```

**Files to modify:** `functions.php`
**Files to create:** None
**Test:** After saving, Activity Types column should appear in the Resources admin list. Terms should appear when editing a resource.

---

## Upgrade 2: AJAX Filtering (NO PAGE RELOAD)

### What
Replace the current full-page-reload filter forms on `archive-resource.php` and `archive-featured_resource.php` with instant AJAX filtering. Teachers select filters → results update immediately without losing scroll position.

### Implementation

**Step 2a: Create the AJAX endpoint in `functions.php`**

Add a new AJAX handler (at the end of `functions.php`, before the Nav Walker class):

```php
/**
 * AJAX handler: filter resources
 */
function aiad_ajax_filter_resources(): void {
    $post_type = sanitize_text_field( $_POST['post_type'] ?? 'resource' );
    if ( ! in_array( $post_type, array( 'resource', 'featured_resource' ), true ) ) {
        wp_send_json_error( 'Invalid post type' );
    }

    $args = array(
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    );

    $tax_query = array();

    // Resource Type filter
    $resource_type = sanitize_text_field( $_POST['resource_type'] ?? '' );
    if ( $resource_type ) {
        $tax_query[] = array(
            'taxonomy' => 'resource_type',
            'field'    => 'slug',
            'terms'    => $resource_type,
        );
    }

    // Principle/Theme filter
    $principle = sanitize_text_field( $_POST['principle'] ?? '' );
    if ( $principle ) {
        $tax_query[] = array(
            'taxonomy' => 'resource_principle',
            'field'    => 'slug',
            'terms'    => $principle,
        );
    }

    // Duration filter
    $duration = sanitize_text_field( $_POST['duration'] ?? '' );
    if ( $duration ) {
        $tax_query[] = array(
            'taxonomy' => 'resource_duration',
            'field'    => 'slug',
            'terms'    => $duration,
        );
    }

    // Activity Type filter (NEW)
    $activity_type = sanitize_text_field( $_POST['activity_type'] ?? '' );
    if ( $activity_type ) {
        $tax_query[] = array(
            'taxonomy' => 'activity_type',
            'field'    => 'slug',
            'terms'    => $activity_type,
        );
    }

    if ( ! empty( $tax_query ) ) {
        $tax_query['relation'] = 'AND';
        $args['tax_query'] = $tax_query;
    }

    $query = new WP_Query( $args );
    $results = array();

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $id = get_the_ID();

            $types     = get_the_terms( $id, 'resource_type' );
            $themes    = get_the_terms( $id, 'resource_principle' );
            $durations = get_the_terms( $id, 'resource_duration' );
            $activities = get_the_terms( $id, 'activity_type' );

            $type_name  = $types && ! is_wp_error( $types ) ? $types[0]->name : '';
            $theme_name = $themes && ! is_wp_error( $themes ) ? $themes[0]->name : '';
            $duration_name = '';
            if ( $durations && ! is_wp_error( $durations ) ) {
                $duration_name = aiad_duration_badge_label( $durations[0] );
            }
            $activity_names = array();
            if ( $activities && ! is_wp_error( $activities ) ) {
                foreach ( $activities as $a ) {
                    $activity_names[] = $a->name;
                }
            }

            $download_url = get_post_meta( $id, '_resource_download_url', true );
            $featured_url = get_post_meta( $id, '_featured_resource_url', true );

            $thumbnail = get_the_post_thumbnail_url( $id, 'medium_large' );

            $results[] = array(
                'id'             => $id,
                'title'          => get_the_title(),
                'permalink'      => get_permalink(),
                'excerpt'        => get_the_excerpt(),
                'thumbnail'      => $thumbnail ?: '',
                'type_name'      => $type_name,
                'theme_name'     => $theme_name,
                'theme_slug'     => $themes && ! is_wp_error( $themes ) ? $themes[0]->slug : '',
                'duration_name'  => $duration_name,
                'activity_types' => $activity_names,
                'download_url'   => $download_url ?: '',
                'download_label' => $download_url ? aiad_resource_download_label( $download_url ) : '',
                'external_url'   => $featured_url ?: '',
                'org_name'       => get_post_meta( $id, '_featured_resource_org_name', true ) ?: '',
            );
        }
        wp_reset_postdata();
    }

    // Also return updated filter counts
    $counts = aiad_get_filter_counts( $post_type, $tax_query );

    wp_send_json_success( array(
        'resources'    => $results,
        'total'        => count( $results ),
        'filter_counts' => $counts,
    ) );
}
add_action( 'wp_ajax_aiad_filter_resources', 'aiad_ajax_filter_resources' );
add_action( 'wp_ajax_nopriv_aiad_filter_resources', 'aiad_ajax_filter_resources' );
```

**Step 2b: Create a new JavaScript file `assets/js/resource-filters.js`**

This replaces the form submission with AJAX. It should:
- Listen for `change` events on all filter `<select>` elements (no submit button needed — instant filtering)
- Collect current filter values from all select elements
- POST to `aiad_ajax.url` with action `aiad_filter_resources`
- Replace the `.resources-grid` contents with rendered cards (build HTML from JSON)
- Update filter count badges next to each option
- Update the URL query string (using `history.replaceState`) so the URL remains shareable
- Show a loading spinner in the grid while fetching
- Animate cards in with a fade-up effect

**Step 2c: Update the localized script data**

In `aiad_scripts()` (around line 801), update the localize call to also include the filter nonce:

```php
wp_localize_script( 'aiad-main', 'aiad_ajax', array(
    'url'   => admin_url( 'admin-ajax.php' ),
    'nonce' => wp_create_nonce( 'aiad_contact_nonce' ),
    'filter_nonce' => wp_create_nonce( 'aiad_filter_nonce' ),
) );
```

Also enqueue the new filter script on resource archive pages.

**Step 2d: Update `archive-resource.php` and `archive-featured_resource.php`**

- Add `id="activity_type"` select dropdown to the filter form (new Activity Type filter)
- Add `data-filter="true"` attribute to each select to identify them for JS
- Remove the submit button (filtering is now instant on change)
- Optionally keep a "Clear filters" link
- Add a `<div class="resources-loading" style="display:none">` spinner element inside the grid area

**Files to modify:** `functions.php`, `archive-resource.php`, `archive-featured_resource.php`
**Files to create:** `assets/js/resource-filters.js`
**Test:** On `/resources/`, changing any dropdown should instantly filter results without page reload. URL should update. Back button should work.

---

## Upgrade 3: Dynamic Filter Counts

### What
When a teacher selects a filter, the remaining filter options show how many results are available. This prevents dead-end filtering (e.g. selecting "Assembly" then seeing "Safe (0)").

### Implementation

**Add this helper function to `functions.php`:**

```php
/**
 * Get filter counts given current tax_query constraints.
 * For each filter dimension, count how many resources match
 * the OTHER active filters plus each term in this dimension.
 *
 * @param string $post_type    Post type slug.
 * @param array  $active_tax_query Current tax_query (from active filters).
 * @return array Counts keyed by taxonomy => term_slug => count.
 */
function aiad_get_filter_counts( string $post_type, array $active_tax_query ): array {
    $taxonomies = array( 'resource_type', 'resource_principle', 'resource_duration', 'activity_type' );
    $counts = array();

    foreach ( $taxonomies as $tax ) {
        $counts[ $tax ] = array();

        // Build a tax_query WITHOUT this taxonomy (so we see what's available)
        $reduced_query = array_filter( $active_tax_query, function( $clause ) use ( $tax ) {
            return is_array( $clause ) && isset( $clause['taxonomy'] ) && $clause['taxonomy'] !== $tax;
        } );

        $terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
        if ( ! $terms || is_wp_error( $terms ) ) {
            continue;
        }

        foreach ( $terms as $term ) {
            $term_query = $reduced_query;
            $term_query[] = array(
                'taxonomy' => $tax,
                'field'    => 'slug',
                'terms'    => $term->slug,
            );
            if ( count( $term_query ) > 1 ) {
                $term_query['relation'] = 'AND';
            }

            $count_query = new WP_Query( array(
                'post_type'      => $post_type,
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'tax_query'      => $term_query,
                'no_found_rows'  => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            ) );

            $counts[ $tax ][ $term->slug ] = $count_query->post_count;
        }
    }

    return $counts;
}
```

**In `resource-filters.js`:** When the AJAX response arrives, update each `<select>` option's text to include the count, e.g. `"Safe (12)"` → `"Safe (3)"`. Disable options with count 0 (add `disabled` attribute and grey styling).

**Files to modify:** `functions.php`, `assets/js/resource-filters.js`
**Test:** Select "Lesson Starter" → all other dropdowns should update to show counts reflecting only Lesson Starters. Select "Lesson Starter" + "Safe" → activity type counts should narrow further.

---

## Upgrade 4: Homepage Editor (Simpler than Customizer)

### What
Replace the Customizer as the primary editing interface for non-technical users. Create a dedicated **admin page** (Settings → AIAD Homepage) with a clean, grouped, visual layout that mirrors the homepage sections. Keep the Customizer working as a fallback — the new page reads/writes the same `theme_mod` values.

### Implementation

**Create a new file: `admin/class-aiad-homepage-editor.php`**

This class registers a top-level admin menu item (or under Appearance) called "Edit Homepage" with the AIAD icon. The page has tabbed sections matching the homepage:

**Tab 1: Hero**
- Hero Logo (image upload using `wp.media`)
- Header Logo (separate field)
- Event Date Text (text input)
- Hero Title (text input)
- Hero Slogan (text input)
- Hero Subtitle (textarea)

**Tab 2: Campaign**
- Campaign Title (text input)
- Campaign Description (textarea with basic formatting)
- Campaign Paragraph 2 (textarea)

**Tab 3: Principles & Badges**
- For each of the 5 principles: badge image upload
- Note: The principle titles/descriptions are currently hardcoded in `front-page.php` — add theme_mod settings for each principle's title and description so they become editable

**Tab 4: Video**
- YouTube URL (text input)
- Section title (text input)

**Tab 5: Display Board**
- 3 example board image uploads

**Tab 6: Get Involved**
- Section title (text input)
- Description (textarea)
- Notification email (email input)

**Tab 7: Social**
- LinkedIn URL
- Instagram URL

**UI Requirements:**
- Use WordPress admin styles (`.wrap`, `.nav-tab-wrapper`, `.form-table`)
- Each section shows a mini-preview or helpful description
- Save button at the bottom of each tab
- Use `get_theme_mod()` / `set_theme_mod()` for all values — same keys as the existing Customizer
- Success/error notices on save
- Add an "Edit Homepage" link in the admin bar when viewing the homepage

**Include the file** in `functions.php`:
```php
if ( is_admin() ) {
    require_once AIAD_DIR . '/admin/class-aiad-homepage-editor.php';
}
```

**Files to create:** `admin/class-aiad-homepage-editor.php`, `admin/css/homepage-editor.css`
**Files to modify:** `functions.php` (add require), `front-page.php` (make principle titles/descriptions use `get_theme_mod()` instead of hardcoded arrays)
**Test:** Navigate to Edit Homepage, change the Hero Title, save, view the homepage — should be updated. Verify the Customizer still works too (reading/writing the same values).

---

## Upgrade 5: Download Tracking

### What
Track how many times each resource is downloaded. Show download counts in the admin resource list and expose them for potential frontend display.

### Implementation

**Step 5a: AJAX download counter**

In `functions.php`, add:

```php
/**
 * AJAX: Track resource download
 */
function aiad_track_download(): void {
    $post_id = absint( $_POST['post_id'] ?? 0 );
    if ( ! $post_id || get_post_type( $post_id ) !== 'resource' ) {
        wp_send_json_error( 'Invalid resource' );
    }

    $count = absint( get_post_meta( $post_id, '_aiad_download_count', true ) );
    $count++;
    update_post_meta( $post_id, '_aiad_download_count', $count );

    wp_send_json_success( array( 'count' => $count ) );
}
add_action( 'wp_ajax_aiad_track_download', 'aiad_track_download' );
add_action( 'wp_ajax_nopriv_aiad_track_download', 'aiad_track_download' );
```

**Step 5b: JavaScript tracking**

In `assets/js/resource-filters.js` (or `main.js`), intercept clicks on download links:

```javascript
// Track downloads
document.addEventListener('click', function(e) {
    var link = e.target.closest('.resource-download-link, a[download]');
    if (!link) return;

    var postId = link.getAttribute('data-resource-id');
    if (!postId) return;

    // Fire and forget — don't block the download
    fetch(aiad_ajax.url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=aiad_track_download&post_id=' + postId,
    }).catch(function() {});
});
```

**Step 5c: Add `data-resource-id` to download links**

In `archive-resource.php` and `single-resource.php`, add the attribute to download links:
```php
<a href="..." class="resource-download-link" data-resource-id="<?php echo esc_attr( get_the_ID() ); ?>" download>
```

Also add it in the AJAX-rendered cards in `resource-filters.js`.

**Step 5d: Admin column**

In `functions.php`, add a "Downloads" column to the resource list table:

```php
function aiad_resource_admin_columns( $columns ) {
    $columns['downloads'] = __( 'Downloads', 'ai-awareness-day' );
    return $columns;
}
add_filter( 'manage_resource_posts_columns', 'aiad_resource_admin_columns' );

function aiad_resource_admin_column_content( $column, $post_id ) {
    if ( $column === 'downloads' ) {
        $count = absint( get_post_meta( $post_id, '_aiad_download_count', true ) );
        echo esc_html( number_format_i18n( $count ) );
    }
}
add_action( 'manage_resource_posts_custom_column', 'aiad_resource_admin_column_content', 10, 2 );

function aiad_resource_sortable_columns( $columns ) {
    $columns['downloads'] = 'downloads';
    return $columns;
}
add_filter( 'manage_edit-resource_sortable_columns', 'aiad_resource_sortable_columns' );
```

**Files to modify:** `functions.php`, `archive-resource.php`, `single-resource.php`, `assets/js/resource-filters.js` (or `main.js`)
**Test:** Download a resource PDF → check in admin that the count incremented. Sort by downloads column.

---

## Upgrade 6: Better Admin Experience for Adding Resources

### What
Make the resource editing screen simpler and more guided for non-technical users. Add clear instructions, reorder meta boxes, and improve the flow.

### Implementation

**Step 6a: Custom meta box layout for resources**

Create a single, unified meta box that replaces the individual taxonomy checkboxes with a cleaner UI:

```php
function aiad_resource_details_meta_box(): void {
    add_meta_box(
        'aiad_resource_details',
        __( '📋 Resource Details', 'ai-awareness-day' ),
        'aiad_resource_details_callback',
        'resource',
        'normal',
        'high'
    );
}
```

The callback renders a single, well-designed panel with:
- **Format** — radio buttons (not a checkbox list) for `resource_type` terms
- **Theme** — coloured pill buttons for `resource_principle` terms (matching brand colours)
- **Session Length** — radio buttons for `resource_duration` terms
- **Activity Type** — checkboxes (multi-select) for `activity_type` terms with descriptions
- **Download File** — the existing media upload (moved here from separate meta box)
- **Key Stage** — checkboxes for EYFS, KS1–KS5 (store as post meta `_aiad_key_stage`)

**Step 6b: Add Key Stage meta field**

Teachers asked about filtering by key stage. Add as a post meta field (array):

```php
register_post_meta( 'resource', '_aiad_key_stage', array(
    'type'          => 'array',
    'single'        => true,
    'default'       => array(),
    'show_in_rest'  => array(
        'schema' => array(
            'type'  => 'array',
            'items' => array( 'type' => 'string' ),
        ),
    ),
    'auth_callback' => function() { return current_user_can( 'edit_posts' ); },
) );
```

Include it in the AJAX filter endpoint and the filter counts function.

**Step 6c: Admin CSS for the unified meta box**

Create `admin/css/aiad-resource-editor.css` with styles for:
- Coloured pill buttons for principles
- Clear radio button groups
- Visual key stage checkboxes
- Consistent spacing and typography

Enqueue only on the resource edit screen.

**Step 6d: Remove the existing separate taxonomy meta boxes** (hide them since our unified box handles it):

```php
function aiad_remove_default_taxonomy_boxes(): void {
    remove_meta_box( 'resource_typediv', 'resource', 'side' );
    remove_meta_box( 'resource_principlediv', 'resource', 'side' );
    remove_meta_box( 'resource_durationdiv', 'resource', 'side' );
    remove_meta_box( 'activity_typediv', 'resource', 'side' );
}
add_action( 'admin_menu', 'aiad_remove_default_taxonomy_boxes' );
```

**Files to modify:** `functions.php`
**Files to create:** `admin/css/aiad-resource-editor.css`
**Test:** Edit a resource — should see one clean "Resource Details" panel instead of scattered taxonomy boxes. Saving should correctly assign all terms.

---

## Implementation Order for Cursor

Work through these in sequence. Each builds on the previous.

| Step | Upgrade | Files Changed | Files Created | Estimated Effort |
|------|---------|---------------|---------------|-----------------|
| 1 | Activity Type Taxonomy | `functions.php` | — | Small |
| 2 | AJAX Filtering | `functions.php`, `archive-resource.php`, `archive-featured_resource.php` | `assets/js/resource-filters.js` | Large |
| 3 | Dynamic Filter Counts | `functions.php`, `assets/js/resource-filters.js` | — | Medium |
| 4 | Homepage Editor | `functions.php`, `front-page.php` | `admin/class-aiad-homepage-editor.php`, `admin/css/homepage-editor.css` | Large |
| 5 | Download Tracking | `functions.php`, `archive-resource.php`, `single-resource.php`, JS | — | Small |
| 6 | Better Admin UX | `functions.php` | `admin/css/aiad-resource-editor.css` | Medium |

**Total: ~6 Cursor sessions, working through one upgrade per session.**

---

## Cursor-Specific Instructions

1. **Read this entire PRD before starting**
2. **Open the existing theme folder** in Cursor — the theme is at `wp-content/themes/ai-awareness-day/`
3. **Work through upgrades 1–6 in order**
4. **DO NOT rewrite existing working code** — only add to it or modify specific sections
5. **When modifying `functions.php`**, specify exactly where the new code goes (after which function, before which line)
6. **Follow WordPress coding standards** — tabs, Yoda conditions, proper escaping/sanitizing
7. **Text domain is `'ai-awareness-day'`** — not `'aiad-core'`
8. **Test each upgrade** before moving to the next
9. **The `aiad_` prefix is already used throughout** — continue using it for all new functions
10. **The theme already enqueues jQuery via WordPress** — you can use it in admin scripts but prefer vanilla JS for frontend scripts

---

## Coding Standards Reminder

These are already followed in the existing code. Continue the pattern:

- Tabs for indentation
- Yoda conditions: `if ( 'value' === $variable )`
- Prefix: `aiad_` for functions, `_aiad_` for private meta keys
- Type hints on function parameters and return types
- PHPDoc blocks on all functions
- `esc_html()`, `esc_attr()`, `esc_url()` on all output
- `sanitize_text_field()`, `sanitize_email()`, `absint()` on all input
- Nonce verification on all form handlers
- Capability checks on all admin-only actions
- Text domain: `'ai-awareness-day'`

---

## Testing Checklist

### Upgrade 1: Activity Type
- [ ] `activity_type` taxonomy appears in admin sidebar under Resources
- [ ] 10 default terms are seeded
- [ ] Can assign activity types when editing a resource
- [ ] Activity Type column shows in the resource list

### Upgrade 2: AJAX Filtering
- [ ] Changing any dropdown on `/resources/` filters results instantly
- [ ] No page reload occurs
- [ ] URL updates with query params (shareable)
- [ ] Browser back button works
- [ ] Activity Type dropdown appears in the filter bar
- [ ] Works on both `/resources/` and `/from-partners/`
- [ ] Cards animate in smoothly
- [ ] "No resources found" message shows when filters return empty

### Upgrade 3: Dynamic Filter Counts
- [ ] Each dropdown option shows a count in parentheses
- [ ] Counts update when another filter is changed
- [ ] Options with 0 results are visually dimmed or disabled
- [ ] Counts are accurate (verified manually)

### Upgrade 4: Homepage Editor
- [ ] "Edit Homepage" appears in admin menu
- [ ] All 7 tabs are present and functional
- [ ] Changing Hero Title and saving updates the homepage
- [ ] Principle titles and descriptions are now editable
- [ ] All fields read from / write to the same `theme_mod` keys as the Customizer
- [ ] Customizer still works correctly

### Upgrade 5: Download Tracking
- [ ] Clicking a download link increments the count
- [ ] "Downloads" column appears in admin resource list
- [ ] Column is sortable
- [ ] Count persists across sessions

### Upgrade 6: Better Admin UX
- [ ] Unified "Resource Details" meta box appears on resource edit screen
- [ ] Default taxonomy boxes are hidden
- [ ] Radio buttons work for single-select taxonomies
- [ ] Checkboxes work for multi-select (Activity Type, Key Stage)
- [ ] Principle pills show correct brand colours
- [ ] Download upload is integrated into the unified box
- [ ] Saving correctly assigns all taxonomy terms and meta

---

*PRD Version: 4.0 — Theme Upgrade*
*Theme: ai-awareness-day*
*Text domain: ai-awareness-day*
*Last updated: February 2026*
