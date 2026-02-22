# Timeline Feature — Integration Guide

## New Files Created

```
inc/timeline.php                          ← CPT, auto-hooks, meta box, query helpers, AJAX handler
template-parts/section-timeline.php       ← Front page template partial
assets/css/components/timeline.css        ← Timeline component styles
assets/js/timeline.js                     ← Load-more AJAX handler
```

## Changes to Existing Files

### 1. `functions.php` — Require the new file

Add this line after the existing requires (e.g. after `require_once AIAD_DIR . '/inc/ajax-handlers.php';`):

```php
require_once AIAD_DIR . '/inc/timeline.php';
```

### 2. `inc/front-page-layout.php` — Add 'timeline' to the sections list

In `aiad_get_front_page_sections()`, add `'timeline'` to the `$sections` array.
Recommended position: after `'campaign'` (so it appears early as a "pulse"):

```php
$sections = array(
    'hero',
    'campaign',
    'timeline',      // ← ADD THIS
    'youtube',
    'principles',
    'aim',
    'toolkit',
    'featured_resources',
    'contact',
);
```

### 3. `inc/customizer.php` — Add timeline visibility toggle

In `aiad_register_front_page_layout_section()`, add the timeline to the `$sections` array:

```php
$sections = array(
    'hero'              => __( 'Hero Section', 'ai-awareness-day' ),
    'campaign'          => __( 'Campaign Section', 'ai-awareness-day' ),
    'timeline'          => __( 'Live Timeline Section', 'ai-awareness-day' ),  // ← ADD THIS
    'youtube'           => __( 'YouTube / Video Section', 'ai-awareness-day' ),
    'principles'        => __( 'Principles Section', 'ai-awareness-day' ),
    'aim'               => __( 'Aim Section', 'ai-awareness-day' ),
    'toolkit'           => __( 'Toolkit Section', 'ai-awareness-day' ),
    'featured_resources' => __( 'Featured Resources Section', 'ai-awareness-day' ),
    'contact'           => __( 'Get Involved Section', 'ai-awareness-day' ),
);
```

### 4. `front-page.php` — Render the timeline section

Add this block wherever you want the timeline to appear (recommended: after the campaign section).
This follows the same pattern as your other sections:

```php
<?php if ( aiad_is_section_visible( 'timeline' ) ) : ?>
    <?php get_template_part( 'template-parts/section', 'timeline' ); ?>
<?php endif; ?>
```

### 5. `inc/setup.php` — Enqueue the CSS and JS

**CSS:** Add to the `$css_files` array:

```php
'components/timeline.css',
```

**JS:** Add after the main script localization (inside `aiad_scripts()`):

```php
// Timeline nonce
wp_localize_script( 'aiad-main', 'aiad_ajax', array(
    // ... existing nonces ...
    'timeline_nonce' => wp_create_nonce( 'aiad_timeline_nonce' ),
) );
```

Note: Since `wp_localize_script` merges with the existing call, you'll need to add `'timeline_nonce'`
to the existing array passed to `wp_localize_script` for `aiad-main`.

**Enqueue timeline.js on the front page:**

```php
if ( is_front_page() ) {
    wp_enqueue_script(
        'aiad-timeline',
        AIAD_URI . '/assets/js/timeline.js',
        array( 'aiad-main' ),
        AIAD_VERSION,
        $script_args
    );
}
```

### 6. `shared.css` — Add scroll-margin-top for the timeline anchor

Add `#timeline` to the existing scroll-margin-top rule:

```css
#campaign,
#reach,
#aim,
#toolkit,
#contact,
#display-board,
#timeline {
  scroll-margin-top: 7rem;
}
```

## How It Works

### Auto-generated entries
- **New resource published** → "New resource added: [Title] (Theme)"  with link to resource
- **New partner published** → "[Partner] joined as [Type]"
- **Submission milestones** → "50 sign-ups reached!" at 10, 25, 50, 100, 250, 500, 1000

### Manual entries
- Created via Dashboard → Timeline → Add Update
- Can be pinned (show at top regardless of date)
- Support images, rich text, and CTA links
- Choose from 7 icon types: Announcement, Resource, Partner, Sign-up, Milestone, Media, Event

### Display logic
- Pinned entries show first (max 3), then chronological
- 4 entries visible initially
- "Load more" button fetches 4 more via AJAX
- Entries use the existing fade-up animation system
- Section visibility controlled via Customizer toggle
