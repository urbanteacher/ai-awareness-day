# Product Requirements Document (PRD)
# AI Awareness Day — Theme Upgrade v2.1
# Six Improvements to the Existing WordPress Theme

> **For Cursor AI:** This PRD describes upgrades to an EXISTING WordPress theme called `ai-awareness-day`. The theme is already live and working. DO NOT rebuild anything from scratch — extend and improve what exists. Read this entire document, then open the project in Cursor and work through the upgrades in order.

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
| Curated resources archive | `archive-featured_resource.php` | External partner resources with same filtering |
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

## CRITICAL: The Two Different Filtering Models

The site has two distinct resource sections with DIFFERENT filtering needs. Do not mix them.

### Your Resources (`resource` CPT, `/resources/`)

**Time-driven filtering.** Teachers think: "I have 5 minutes before class — what can I use?"

Primary filter path: **Session Length → Theme**

| Filter | Taxonomy | Purpose |
|--------|----------|---------|
| Session Length (primary) | `resource_duration` | "5 min", "15-20 min", "20 min", "30-45 min" |
| Theme | `resource_principle` | Safe, Smart, Creative, Responsible, Future |
| Resource Type | `resource_type` | Lesson Starter, Lesson Activity, Assembly |

No Activity Type taxonomy here — the format already implies the activity style.

### Curated Resources (`featured_resource` CPT, `/from-partners/`)

**Content-type-driven filtering.** Teachers think: "I want an interactive quiz about AI safety."

Primary filter path: **Content Type → Theme → Session Length**

| Filter | Taxonomy | Purpose |
|--------|----------|---------|
| Content Type (primary, NEW) | `content_type` | Quiz, Game, Interactive Tool, Video, Article, Lesson Pack |
| Theme | `resource_principle` | Safe, Smart, Creative, Responsible, Future |
| Session Length | `resource_duration` | Optional — not all external resources have a fixed duration |
| Resource Type | `resource_type` | Carried over for backwards compatibility |

---

## Upgrade 1: Content Type Taxonomy (for Curated Resources ONLY)

### What
Add a new taxonomy `content_type` attached ONLY to `featured_resource`. This gives curated resources the "what kind of content is this" filter that's missing.

### Implementation

**In `functions.php`, inside `aiad_register_post_types()` (after the `resource_duration` registration, around line 123), add:**

```php
// Taxonomy: Content Type (Quiz, Game, Video, etc.) — curated resources only
register_taxonomy( 'content_type', array( 'featured_resource' ), array(
    'labels'            => array(
        'name'          => __( 'Content Types', 'ai-awareness-day' ),
        'singular_name' => __( 'Content Type', 'ai-awareness-day' ),
        'add_new_item'  => __( 'Add New Content Type', 'ai-awareness-day' ),
        'description'   => __( 'What kind of content is this? (Quiz, Game, Video, etc.) Used for curated/partner resources.', 'ai-awareness-day' ),
    ),
    'hierarchical'      => true,
    'show_ui'           => true,
    'show_admin_column' => true,
    'show_in_rest'      => true,
) );
```

**Seed default terms** — add a new function after `aiad_duration_terms()`:

```php
function aiad_content_type_terms(): void {
    if ( get_option( 'aiad_content_type_terms_seeded' ) ) {
        return;
    }
    $types = array(
        'Quiz'             => 'quiz',
        'Game'             => 'game',
        'Interactive Tool' => 'interactive-tool',
        'Video'            => 'video',
        'Article'          => 'article',
        'Lesson Pack'      => 'lesson-pack',
        'Simulation'       => 'simulation',
        'Infographic'      => 'infographic',
    );
    foreach ( $types as $name => $slug ) {
        if ( ! term_exists( $slug, 'content_type' ) ) {
            wp_insert_term( $name, 'content_type', array( 'slug' => $slug ) );
        }
    }
    update_option( 'aiad_content_type_terms_seeded', true );
}
add_action( 'init', 'aiad_content_type_terms', 22 );
```

**Assign content types to existing seeded curated resources.** Update `aiad_seed_partner_resources()` to also assign content_type terms to the 5 default curated resources. However, since these are already seeded (the option `aiad_partner_resources_seeded` is set), create a one-time migration function:

```php
function aiad_migrate_partner_resource_content_types(): void {
    if ( get_option( 'aiad_content_types_migrated' ) ) {
        return;
    }
    // Map existing curated resources to content types by title
    $mapping = array(
        'Quick, Draw!'                  => 'game',
        'Guess the Line'                => 'game',
        'Quiz: AI or Real?'             => 'quiz',
        'Turing Test Live'              => 'interactive-tool',
        'How Could AI Affect Your Job?' => 'article',
    );
    foreach ( $mapping as $title => $type_slug ) {
        $posts = get_posts( array(
            'post_type'  => 'featured_resource',
            'title'      => $title,
            'numberposts' => 1,
        ) );
        if ( ! empty( $posts ) ) {
            wp_set_object_terms( $posts[0]->ID, array( $type_slug ), 'content_type' );
        }
    }
    update_option( 'aiad_content_types_migrated', true );
}
add_action( 'init', 'aiad_migrate_partner_resource_content_types', 30 );
```

**Files to modify:** `functions.php`
**Files to create:** None
**Test:** Content Types column appears in the "Resources from partners" admin list. The 5 seeded curated resources have their content type assigned. New curated resources can be tagged with a content type. Regular resources (`resource` CPT) do NOT show Content Type.

---

## Upgrade 2: AJAX Filtering (NO PAGE RELOAD)

### What
Replace the full-page-reload filter forms on both archive pages with instant AJAX filtering. Each archive gets its own filter set.

### 2a: AJAX Endpoint

Add to `functions.php`:

```php
/**
 * AJAX handler: filter resources (works for both resource and featured_resource)
 */
function aiad_ajax_filter_resources(): void {
    check_ajax_referer( 'aiad_filter_nonce', 'nonce' );

    $post_type = sanitize_text_field( $_POST['post_type'] ?? 'resource' );
    if ( ! in_array( $post_type, array( 'resource', 'featured_resource' ), true ) ) {
        wp_send_json_error( 'Invalid post type' );
    }

    $args = array(
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'resource' === $post_type ? 'title' : 'menu_order title',
        'order'          => 'ASC',
    );

    $tax_query = array();

    // Shared filters: Theme + Duration + Resource Type
    $principle = sanitize_text_field( $_POST['principle'] ?? '' );
    if ( $principle ) {
        $tax_query[] = array(
            'taxonomy' => 'resource_principle',
            'field'    => 'slug',
            'terms'    => $principle,
        );
    }

    $duration = sanitize_text_field( $_POST['duration'] ?? '' );
    if ( $duration ) {
        $tax_query[] = array(
            'taxonomy' => 'resource_duration',
            'field'    => 'slug',
            'terms'    => $duration,
        );
    }

    $resource_type = sanitize_text_field( $_POST['resource_type'] ?? '' );
    if ( $resource_type ) {
        $tax_query[] = array(
            'taxonomy' => 'resource_type',
            'field'    => 'slug',
            'terms'    => $resource_type,
        );
    }

    // Curated-only filter: Content Type
    if ( 'featured_resource' === $post_type ) {
        $content_type = sanitize_text_field( $_POST['content_type'] ?? '' );
        if ( $content_type ) {
            $tax_query[] = array(
                'taxonomy' => 'content_type',
                'field'    => 'slug',
                'terms'    => $content_type,
            );
        }
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

            $type_name  = $types && ! is_wp_error( $types ) ? $types[0]->name : '';
            $theme_name = $themes && ! is_wp_error( $themes ) ? $themes[0]->name : '';
            $theme_slug = $themes && ! is_wp_error( $themes ) ? $themes[0]->slug : '';
            $duration_name = '';
            if ( $durations && ! is_wp_error( $durations ) ) {
                $duration_name = aiad_duration_badge_label( $durations[0] );
            }

            $item = array(
                'id'             => $id,
                'title'          => get_the_title(),
                'permalink'      => get_permalink(),
                'excerpt'        => get_the_excerpt(),
                'thumbnail'      => get_the_post_thumbnail_url( $id, 'medium_large' ) ?: '',
                'type_name'      => $type_name,
                'theme_name'     => $theme_name,
                'theme_slug'     => $theme_slug,
                'duration_name'  => $duration_name,
            );

            // Fields specific to own resources
            if ( 'resource' === $post_type ) {
                $download_url = get_post_meta( $id, '_resource_download_url', true );
                $item['download_url']   = $download_url ?: '';
                $item['download_label'] = $download_url ? aiad_resource_download_label( $download_url ) : '';
            }

            // Fields specific to curated resources
            if ( 'featured_resource' === $post_type ) {
                $content_types = get_the_terms( $id, 'content_type' );
                $item['content_type_name'] = $content_types && ! is_wp_error( $content_types ) ? $content_types[0]->name : '';
                $item['external_url'] = get_post_meta( $id, '_featured_resource_url', true ) ?: '';
                $item['org_name']     = get_post_meta( $id, '_featured_resource_org_name', true ) ?: '';
            }

            $results[] = $item;
        }
        wp_reset_postdata();
    }

    // Return updated filter counts alongside results
    $filter_taxonomies = array( 'resource_type', 'resource_principle', 'resource_duration' );
    if ( 'featured_resource' === $post_type ) {
        $filter_taxonomies[] = 'content_type';
    }
    $counts = aiad_get_filter_counts( $post_type, $tax_query, $filter_taxonomies );

    wp_send_json_success( array(
        'resources'     => $results,
        'total'         => count( $results ),
        'filter_counts' => $counts,
        'post_type'     => $post_type,
    ) );
}
add_action( 'wp_ajax_aiad_filter_resources', 'aiad_ajax_filter_resources' );
add_action( 'wp_ajax_nopriv_aiad_filter_resources', 'aiad_ajax_filter_resources' );
```

### 2b: Localise filter data

Update `aiad_scripts()` (around line 801 in `functions.php`):

```php
wp_localize_script( 'aiad-main', 'aiad_ajax', array(
    'url'          => admin_url( 'admin-ajax.php' ),
    'nonce'        => wp_create_nonce( 'aiad_contact_nonce' ),
    'filter_nonce' => wp_create_nonce( 'aiad_filter_nonce' ),
) );
```

Also register and conditionally enqueue the new filter script:

```php
wp_register_script(
    'aiad-resource-filters',
    AIAD_URI . '/assets/js/resource-filters.js',
    array(),
    AIAD_VERSION,
    array( 'in_footer' => true, 'strategy' => 'defer' )
);

// Enqueue on resource archive pages
if ( is_post_type_archive( 'resource' ) || is_post_type_archive( 'featured_resource' ) ) {
    wp_enqueue_script( 'aiad-resource-filters' );
}
```

### 2c: Create `assets/js/resource-filters.js`

This file handles AJAX filtering for BOTH archive pages. It must:

1. **Detect which archive** by reading a `data-post-type` attribute on the filter form (set in the template)
2. **Listen for `change` events** on all `<select>` elements within `.resource-filter-form`
3. **Collect all selected filter values** from the form
4. **Show loading state** — add `is-loading` class to `.resources-grid`, show a spinner overlay
5. **POST to AJAX** with `action: 'aiad_filter_resources'`, `nonce`, `post_type`, and all filter values
6. **Render results** — build card HTML from the JSON response. Card markup must match the existing structure:
   - For own resources: use the same classes as `archive-resource.php` (`.resource-card`, `.resource-card__image-link`, `.resource-card__body`, etc.)
   - For curated resources: use the same classes as `archive-featured_resource.php` (`.resource-card--external`, etc.)
7. **Update filter counts** — for each `<select>`, update option text to include count: `"Safe (12)"`. If count is 0, add `disabled` attribute and style dimmed.
8. **Update URL** — use `history.replaceState()` to update query params so the filtered URL is shareable
9. **Handle empty state** — show "No resources found" message matching existing style
10. **Animate** — fade in new cards with the existing `fade-up` class

**Card rendering for own resources (`resource`):**
```javascript
function renderResourceCard(item) {
    var hasOverlay = item.type_name || item.theme_name;
    var pillClass = 'resource-card__pill--theme';
    if (['safe','smart','creative','responsible','future'].indexOf(item.theme_slug) > -1) {
        pillClass += ' resource-card__pill--' + item.theme_slug;
    }

    var imageHtml = item.thumbnail
        ? '<img src="' + item.thumbnail + '" class="resource-card__image" alt="" />'
        : '<div class="resource-card__image-placeholder" aria-hidden="true"><span class="resource-card__image-placeholder-text">' + (item.duration_name || '—') + '</span></div>';

    var overlayHtml = hasOverlay ? '<div class="resource-card__image-overlay" aria-hidden="true"><div class="resource-card__image-top">'
        + (item.type_name ? '<span class="resource-card__pill resource-card__pill--type">' + escHtml(item.type_name) + '</span>' : '')
        + (item.theme_name ? '<span class="resource-card__pill ' + pillClass + '">' + escHtml(item.theme_name) + '</span>' : '')
        + '</div></div>' : '';

    var actionHtml = item.download_url
        ? '<a href="' + item.download_url + '" class="resource-card__link resource-download-link" data-resource-id="' + item.id + '" download target="_blank" rel="noopener">' + escHtml(item.download_label) + ' →</a>'
        : '<a href="' + item.permalink + '" class="resource-card__link">View resource →</a>';

    return '<article class="resource-card resource-card--download fade-up">'
        + '<a href="' + item.permalink + '" class="resource-card__image-link">' + imageHtml + overlayHtml + '</a>'
        + '<div class="resource-card__body">'
        + '<h2 class="resource-card__title"><a href="' + item.permalink + '">' + escHtml(item.title) + '</a></h2>'
        + (item.excerpt ? '<p class="resource-card__excerpt">' + escHtml(item.excerpt) + '</p>' : '')
        + '<p class="resource-card__action">' + actionHtml + '</p>'
        + '</div></article>';
}
```

**Card rendering for curated resources (`featured_resource`):**
```javascript
function renderCuratedCard(item) {
    // Similar structure but with external links, org name, content type pill
    // Match the existing markup in archive-featured_resource.php
}
```

### 2d: Update `archive-resource.php`

Modify the existing filter form:
- Add `data-post-type="resource"` to the `<form>` element
- Add `data-filter="true"` to each `<select>`
- Keep the Session Length dropdown as the FIRST filter (it's the primary dimension)
- Keep Theme as second
- Keep Resource Type as third
- Remove the submit button, replace with a "Clear all" link
- Add a loading spinner element: `<div class="resources-loading" style="display:none;"><span class="spinner"></span></div>`
- Add `id="resources-grid"` to the `.resources-grid` div

### 2e: Update `archive-featured_resource.php`

Modify the existing filter form:
- Add `data-post-type="featured_resource"` to the `<form>` element
- Add a NEW Content Type dropdown as the FIRST filter:
```php
<div class="resource-filter-group">
    <label for="content_type" class="resource-filter-label"><?php esc_html_e( 'Content Type', 'ai-awareness-day' ); ?></label>
    <select id="content_type" name="content_type" class="resource-filter-select" data-filter="true">
        <option value=""><?php esc_html_e( 'All types', 'ai-awareness-day' ); ?></option>
        <?php
        $content_types = get_terms( array( 'taxonomy' => 'content_type', 'hide_empty' => false ) );
        foreach ( $content_types as $term ) : ?>
            <option value="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></option>
        <?php endforeach; ?>
    </select>
</div>
```
- Theme stays as second filter
- Session Length as third (optional for curated)
- Remove the submit button, add "Clear all" link
- Add loading spinner and `id="resources-grid"` as above

**Files to modify:** `functions.php`, `archive-resource.php`, `archive-featured_resource.php`
**Files to create:** `assets/js/resource-filters.js`
**Test:** On `/resources/`, changing Session Length dropdown instantly filters. On `/from-partners/`, changing Content Type dropdown instantly filters. URLs update. Back button works.

---

## Upgrade 3: Dynamic Filter Counts

### What
When a teacher selects a filter, the remaining filter options show how many results are available per option. Different taxonomies are counted for each post type.

### Implementation

Add to `functions.php`:

```php
/**
 * Get filter counts given current constraints.
 * For each taxonomy, count how many posts match the OTHER active filters
 * combined with each term in this taxonomy.
 *
 * @param string $post_type         Post type slug.
 * @param array  $active_tax_query  Current tax_query from active filters.
 * @param array  $taxonomies        Which taxonomies to count.
 * @return array Counts: taxonomy_slug => term_slug => int.
 */
function aiad_get_filter_counts( string $post_type, array $active_tax_query, array $taxonomies ): array {
    $counts = array();

    foreach ( $taxonomies as $tax ) {
        $counts[ $tax ] = array();

        // Build tax_query WITHOUT this taxonomy
        $reduced = array_filter( $active_tax_query, function( $clause ) use ( $tax ) {
            return is_array( $clause ) && isset( $clause['taxonomy'] ) && $clause['taxonomy'] !== $tax;
        } );

        $terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
        if ( ! $terms || is_wp_error( $terms ) ) {
            continue;
        }

        foreach ( $terms as $term ) {
            $term_query = array_values( $reduced );
            $term_query[] = array(
                'taxonomy' => $tax,
                'field'    => 'slug',
                'terms'    => $term->slug,
            );
            if ( count( $term_query ) > 1 ) {
                $term_query['relation'] = 'AND';
            }

            $q = new WP_Query( array(
                'post_type'              => $post_type,
                'post_status'            => 'publish',
                'posts_per_page'         => -1,
                'fields'                 => 'ids',
                'tax_query'              => $term_query,
                'no_found_rows'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            ) );

            $counts[ $tax ][ $term->slug ] = $q->post_count;
        }
    }

    return $counts;
}
```

**In `resource-filters.js`**, after receiving the AJAX response, update each `<select>`:

```javascript
function updateFilterCounts(counts) {
    Object.keys(counts).forEach(function(taxonomy) {
        var select = document.querySelector('[name="' + taxonomy + '"]');
        if (!select) return;

        Array.from(select.options).forEach(function(option) {
            if (!option.value) return; // skip "All" option

            var count = counts[taxonomy][option.value];
            if (typeof count === 'undefined') count = 0;

            // Update label: "Safe (3)" — strip any existing count first
            var label = option.textContent.replace(/\s*\(\d+\)$/, '');
            option.textContent = label + ' (' + count + ')';
            option.disabled = (count === 0);
        });
    });
}
```

**Files to modify:** `functions.php`, `assets/js/resource-filters.js`
**Test:** On `/resources/`, select "5-min lesson starters" → Theme dropdown updates to show counts like "Safe (2)", "Smart (1)". Options with 0 results show "(0)" and are dimmed.

---

## Upgrade 4: Homepage Editor (Simpler than Customizer)

### What
A dedicated admin page where non-technical staff can edit all homepage content in one place. Uses tabbed sections mirroring the homepage. Reads/writes the same `theme_mod` values as the existing Customizer (so both work).

### Implementation

**Create `admin/class-aiad-homepage-editor.php`**

This class:
1. Registers a top-level admin menu item "Edit Homepage" with `dashicons-admin-home` icon
2. Renders a page with `nav-tab-wrapper` tabs:

| Tab | Fields | theme_mod keys |
|-----|--------|----------------|
| Hero | Logo image, Header Logo, Date text, Title, Slogan, Subtitle | `aiad_hero_logo`, `aiad_header_logo`, `aiad_hero_date`, `aiad_hero_title`, `aiad_hero_slogan`, `aiad_hero_subtitle` |
| Campaign | Title, Description, Paragraph 2 | `aiad_campaign_title`, `aiad_campaign_text`, `aiad_campaign_text_2` |
| Badges | 5 image uploads (safe, smart, creative, responsible, future) | `aiad_badge_safe`, `aiad_badge_smart`, `aiad_badge_creative`, `aiad_badge_responsible`, `aiad_badge_future` |
| Video | YouTube URL, Section title | `aiad_youtube_url`, `aiad_youtube_title` |
| Display Board | 3 example board images | `aiad_display_board_image_1`, `_2`, `_3` |
| Get Involved | Title, Description, Notification email | `aiad_contact_title`, `aiad_contact_desc`, `aiad_contact_email` |
| Social | LinkedIn URL, Instagram URL | `aiad_linkedin`, `aiad_instagram` |

**For each field**, use the same sanitization as the existing Customizer controls. On save, use `set_theme_mod()`. On load, use `get_theme_mod()` with the same defaults.

**Image uploads** use the WordPress media library (`wp.media` JS API). The badge fields store attachment IDs (integers), while hero/display board fields store URLs.

**UI details:**
- Use standard WordPress admin classes: `.wrap`, `.nav-tab-wrapper`, `.form-table`
- Each tab shows only its fields (hide others with JS)
- "Save Changes" button per tab
- Success notice on save: "Homepage updated."
- Include a "View Homepage" link that opens the site in a new tab
- Add an "Edit Homepage" link to the admin bar when viewing the front page

**Also update `front-page.php`:** The principles section (around lines 170–208) currently has hardcoded titles and descriptions. Make them editable:

```php
$principles = array(
    array(
        'title' => get_theme_mod( 'aiad_principle_title_safe', __( 'Safe', 'ai-awareness-day' ) ),
        'desc'  => get_theme_mod( 'aiad_principle_desc_safe', __( 'Ensuring safe and secure interactions with AI technologies.', 'ai-awareness-day' ) ),
    ),
    // ... same for smart, creative, responsible, future
);
```

Add corresponding fields to the Homepage Editor's Badges tab (or a new "Principles" tab).

**Include in `functions.php`:**
```php
if ( is_admin() ) {
    require_once AIAD_DIR . '/admin/class-aiad-homepage-editor.php';
}
```

**Files to create:** `admin/class-aiad-homepage-editor.php`, `admin/css/homepage-editor.css`
**Files to modify:** `functions.php` (add require), `front-page.php` (make principle titles/descriptions use theme_mods)
**Test:** Change Hero Title in the new editor → save → view homepage → title is updated. Verify Customizer still works.

---

## Upgrade 5: Download Tracking

### What
Track how many times each resource is downloaded. Show counts in the admin.

### Implementation

**5a: AJAX counter in `functions.php`:**

```php
function aiad_track_download(): void {
    $post_id = absint( $_POST['post_id'] ?? 0 );
    if ( ! $post_id || 'resource' !== get_post_type( $post_id ) ) {
        wp_send_json_error();
    }
    $count = absint( get_post_meta( $post_id, '_aiad_download_count', true ) );
    update_post_meta( $post_id, '_aiad_download_count', ++$count );
    wp_send_json_success( array( 'count' => $count ) );
}
add_action( 'wp_ajax_aiad_track_download', 'aiad_track_download' );
add_action( 'wp_ajax_nopriv_aiad_track_download', 'aiad_track_download' );
```

**5b: JS tracking** — add to `resource-filters.js` or `main.js`:

```javascript
document.addEventListener('click', function(e) {
    var link = e.target.closest('.resource-download-link, a[download]');
    if (!link) return;
    var id = link.getAttribute('data-resource-id');
    if (!id) return;
    var body = new FormData();
    body.append('action', 'aiad_track_download');
    body.append('post_id', id);
    fetch(aiad_ajax.url, { method: 'POST', body: body }).catch(function(){});
});
```

**5c: Add `data-resource-id`** to download links in `archive-resource.php`, `single-resource.php`, and the JS card renderer.

**5d: Admin column** — add to `functions.php`:

```php
function aiad_resource_downloads_column( $columns ) {
    $new = array();
    foreach ( $columns as $key => $label ) {
        $new[ $key ] = $label;
        if ( 'title' === $key ) {
            $new['downloads'] = __( 'Downloads', 'ai-awareness-day' );
        }
    }
    return $new;
}
add_filter( 'manage_resource_posts_columns', 'aiad_resource_downloads_column' );

function aiad_resource_downloads_column_content( $column, $post_id ) {
    if ( 'downloads' === $column ) {
        echo esc_html( number_format_i18n( absint( get_post_meta( $post_id, '_aiad_download_count', true ) ) ) );
    }
}
add_action( 'manage_resource_posts_custom_column', 'aiad_resource_downloads_column_content', 10, 2 );

function aiad_resource_downloads_sortable( $columns ) {
    $columns['downloads'] = 'downloads';
    return $columns;
}
add_filter( 'manage_edit-resource_sortable_columns', 'aiad_resource_downloads_sortable' );

function aiad_resource_downloads_orderby( WP_Query $query ): void {
    if ( ! is_admin() || ! $query->is_main_query() ) return;
    if ( 'downloads' === $query->get( 'orderby' ) ) {
        $query->set( 'meta_key', '_aiad_download_count' );
        $query->set( 'orderby', 'meta_value_num' );
    }
}
add_action( 'pre_get_posts', 'aiad_resource_downloads_orderby' );
```

**Files to modify:** `functions.php`, `archive-resource.php`, `single-resource.php`, JS files
**Test:** Download a PDF → admin shows count "1". Download again → "2". Sort by Downloads column works.

---

## Upgrade 6: Better Admin Experience for Adding Resources

### What
Replace scattered taxonomy checkboxes with a single, clean meta box for resource details. Different layouts for own resources vs curated resources.

### Implementation

**For own resources (`resource` CPT):** Create a unified meta box "Resource Details" with:
- **Session Length** — radio buttons (single select) for `resource_duration` terms. THIS IS FIRST because it's the primary dimension.
- **Theme** — coloured pill-style radio buttons for `resource_principle` terms. Use inline styles matching brand colours:
  - Safe: `#00B4D8`, Smart: `#FF6B35`, Creative: `#9B8FE4`, Responsible: `#2DC653`, Future: `#FF69B4`
- **Resource Type** — radio buttons for `resource_type` terms
- **Download File** — the existing media upload (moved from its own meta box into this one)
- Helpful description text above each group

**For curated resources (`featured_resource` CPT):** Create a unified meta box "Curated Resource Details" with:
- **Content Type** — radio buttons for `content_type` terms. THIS IS FIRST.
- **Theme** — same coloured pills as above
- **Session Length** — optional, radio buttons with a "Not specified" default
- **Resource URL** — the existing URL field (moved here)
- **Organisation Name** — moved here
- **Organisation Website** — moved here

**Hide default taxonomy boxes** for both CPTs:

```php
function aiad_remove_default_taxonomy_boxes(): void {
    // Own resources
    remove_meta_box( 'resource_typediv', 'resource', 'side' );
    remove_meta_box( 'resource_principlediv', 'resource', 'side' );
    remove_meta_box( 'resource_durationdiv', 'resource', 'side' );
    // Curated resources
    remove_meta_box( 'resource_typediv', 'featured_resource', 'side' );
    remove_meta_box( 'resource_principlediv', 'featured_resource', 'side' );
    remove_meta_box( 'resource_durationdiv', 'featured_resource', 'side' );
    remove_meta_box( 'content_typediv', 'featured_resource', 'side' );
}
add_action( 'admin_menu', 'aiad_remove_default_taxonomy_boxes' );
```

**Save handler:** On `save_post_resource` and `save_post_featured_resource`, read the unified meta box values and use `wp_set_object_terms()` for taxonomy assignments plus `update_post_meta()` for any meta fields.

**Admin CSS:** Create `admin/css/aiad-resource-editor.css` with styles for:
- Pill-style radio buttons with brand colours
- Clear grouping and spacing
- Responsive layout for smaller screens

Enqueue only on resource/featured_resource edit screens.

**Files to modify:** `functions.php`
**Files to create:** `admin/css/aiad-resource-editor.css`
**Test:** Edit a resource → see the unified "Resource Details" box. Select session length, theme, type → save → verify terms are assigned correctly. Edit a curated resource → see Content Type as the first field.

---

## Implementation Order for Cursor

| Step | Upgrade | Effort |
|------|---------|--------|
| 1 | Content Type taxonomy (curated only) | Small |
| 2 | AJAX filtering (both archives) | Large |
| 3 | Dynamic filter counts | Medium |
| 4 | Homepage Editor | Large |
| 5 | Download tracking | Small |
| 6 | Better admin UX (unified meta boxes) | Medium |

---

## Cursor-Specific Instructions

1. **Read this entire PRD before starting**
2. **Open the existing theme folder** in Cursor: `wp-content/themes/ai-awareness-day/`
3. **Work through upgrades 1–6 in order**
4. **DO NOT rewrite existing working code** — only add or modify specific sections
5. **When modifying `functions.php`**, specify exactly where new code goes
6. **The two resource types have DIFFERENT filtering** — own resources are time-driven, curated are content-type-driven. Never mix them.
7. **Text domain is `'ai-awareness-day'`**
8. **Prefix is `aiad_`** for all new functions
9. **Follow existing coding style** — tabs, Yoda conditions, type hints, PHPDoc
10. **Test each upgrade before moving to the next**
11. **Match existing card HTML structure exactly** when rendering via JS — the CSS classes must be identical

---

## Testing Checklist

### Upgrade 1: Content Type Taxonomy
- [ ] `content_type` taxonomy appears ONLY on "Resources from partners" admin screen
- [ ] 8 default terms are seeded (Quiz, Game, Interactive Tool, etc.)
- [ ] Existing 5 curated resources are assigned correct content types
- [ ] Content Type column shows in the curated resources list
- [ ] Regular resources (`resource`) do NOT show Content Type anywhere

### Upgrade 2: AJAX Filtering
- [ ] `/resources/`: Changing Session Length filters instantly (no reload)
- [ ] `/resources/`: Changing Theme filters instantly
- [ ] `/resources/`: URL updates with query params
- [ ] `/from-partners/`: Content Type dropdown appears as FIRST filter
- [ ] `/from-partners/`: Changing Content Type filters instantly
- [ ] Both: "No resources found" shows for empty results
- [ ] Both: Browser back button restores previous filter state
- [ ] Both: Cards animate in smoothly

### Upgrade 3: Dynamic Filter Counts
- [ ] `/resources/`: Select "5-min lesson starters" → Theme counts update
- [ ] `/from-partners/`: Select "Quiz" → Theme counts update
- [ ] Options with 0 results show "(0)" and are dimmed/disabled
- [ ] "All" option always remains enabled

### Upgrade 4: Homepage Editor
- [ ] "Edit Homepage" menu item appears in admin
- [ ] All 7 tabs present and functional
- [ ] Saving updates the homepage
- [ ] Principle titles/descriptions are now editable
- [ ] Customizer still works (reads/writes same theme_mods)

### Upgrade 5: Download Tracking
- [ ] Downloading a resource increments the count
- [ ] "Downloads" column visible and sortable in admin
- [ ] Count persists

### Upgrade 6: Better Admin UX
- [ ] Own resources: unified box with Session Length first, then Theme, then Type
- [ ] Curated resources: unified box with Content Type first, then Theme
- [ ] Default taxonomy checkbox boxes are hidden
- [ ] Theme pills show brand colours
- [ ] Saving assigns terms correctly

---

*PRD Version: 4.1 — Theme Upgrade (corrected filtering models)*
*Theme: ai-awareness-day*
*Text domain: ai-awareness-day*
*Last updated: February 2026*
