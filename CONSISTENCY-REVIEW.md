# Customizer, Meta, and Taxonomy Consistency Review

## Overview

This document reviews naming conventions and patterns across three key systems in the AI Awareness Day theme:
1. **Customizer Settings** (theme_mods)
2. **Meta Fields** (post_meta)
3. **Taxonomies**

---

## 1. Customizer Settings (theme_mods)

### Naming Pattern
```
aiad_{section}_{field}
```

### Sections and Settings

| Section | Settings | Pattern |
|---------|----------|---------|
| Header | `aiad_header_logo` | `aiad_header_*` |
| Hero | `aiad_hero_logo`, `aiad_hero_title`, `aiad_hero_date`, `aiad_hero_subtitle`, `aiad_hero_slogan` | `aiad_hero_*` |
| Campaign | `aiad_campaign_title`, `aiad_campaign_text`, `aiad_campaign_text_2`, `aiad_campaign_linkedin_embed_src` | `aiad_campaign_*` |
| Badges | `aiad_badge_{slug}`, `aiad_principle_title_{slug}`, `aiad_principle_desc_{slug}` | `aiad_badge_*`, `aiad_principle_*` |
| YouTube | `aiad_youtube_url`, `aiad_youtube_title` | `aiad_youtube_*` |
| Toolkit | `aiad_implementation_guide_url`, `aiad_sample_letters_url`, `aiad_newsletter_url`, `aiad_toolkit_image_{n}` | `aiad_*_url`, `aiad_toolkit_*` |
| Contact | `aiad_contact_title`, `aiad_contact_desc`, `aiad_contact_email` | `aiad_contact_*` |
| Social | `aiad_linkedin`, `aiad_instagram`, `aiad_linkedin_post_url` | `aiad_{network}` |
| Layout | `aiad_section_order`, `aiad_section_visible_{slug}`, `aiad_text_alignment`, `aiad_container_width` | `aiad_section_*` |
| Display Board | `aiad_display_board_image_{n}` | `aiad_display_board_*` |
| Session Badges | `aiad_session_badge_{slug}` | `aiad_session_badge_*` |

### Homepage Editor Settings (New)

| Setting | Pattern | Notes |
|---------|---------|-------|
| `aiad_homepage_handpicked_resource_{1-3}` | `aiad_homepage_*` | ⚠️ Could be simplified |
| `aiad_homepage_free_resource_{1-6}` | `aiad_homepage_*` | ⚠️ Could be simplified |
| `aiad_handpicked_resources_title` | `aiad_handpicked_resources_*` | ✅ Consistent |
| `aiad_handpicked_resources_desc` | `aiad_handpicked_resources_*` | ✅ Consistent |
| `aiad_free_resources_title` | `aiad_free_resources_*` | ✅ Consistent |
| `aiad_free_resources_desc` | `aiad_free_resources_*` | ✅ Consistent |

### Customizer Consistency Issues

1. **Homepage Editor resource IDs**: The pattern `aiad_homepage_handpicked_resource_*` is verbose. Consider simplifying to `aiad_handpicked_resource_*` to match the title/desc pattern.

---

## 2. Meta Fields (post_meta)

### Naming Conventions by Post Type

#### Partner (`partner` post type)
| Meta Key | Description | Pattern |
|----------|-------------|---------|
| `_partner_url` | Partner website URL | `_partner_*` ✅ |
| `_partner_stats` | Statistics/description | `_partner_*` ✅ |
| `_partner_school_count` | Number of schools | `_partner_*` ✅ |

**Status**: ✅ Consistent

---

#### Featured Resource (`featured_resource` post type)
| Meta Key | Description | Pattern |
|----------|-------------|---------|
| `_featured_resource_url` | External resource URL | `_featured_resource_*` ✅ |
| `_featured_resource_org_name` | Organisation name | `_featured_resource_*` ✅ |
| `_featured_resource_org_url` | Organisation website | `_featured_resource_*` ✅ |

**Status**: ✅ Consistent

---

#### Resource (`resource` post type)
| Meta Key | Description | Pattern |
|----------|-------------|---------|
| `_aiad_subtitle` | Resource subtitle | `_aiad_*` ✅ |
| `_aiad_duration` | Duration text | `_aiad_*` ✅ |
| `_aiad_level` | Difficulty level | `_aiad_*` ✅ |
| `_aiad_status` | Publication status | `_aiad_*` ✅ |
| `_aiad_key_stage` | Key stages (array) | `_aiad_*` ✅ |
| `_aiad_preparation` | Preparation steps | `_aiad_*` ✅ |
| `_aiad_differentiation` | Differentiation object | `_aiad_*` ✅ |
| `_aiad_extensions` | Extension activities | `_aiad_*` ✅ |
| `_aiad_resources` | Required resources | `_aiad_*` ✅ |
| `_aiad_discussion_question` | Discussion question | `_aiad_*` ✅ |
| `_aiad_teacher_notes` | Teacher notes | `_aiad_*` ✅ |
| `_aiad_learning_objectives` | Learning objectives | `_aiad_*` ✅ |
| `_aiad_instructions` | Instructions steps | `_aiad_*` ✅ |
| `_aiad_key_definitions` | Key definitions | `_aiad_*` ✅ |
| `_aiad_download_count` | Download counter | `_aiad_*` ✅ |
| `_aiad_view_count` | View counter | `_aiad_*` ✅ |
| `_resource_download_url` | Download file URL | `_resource_*` ⚠️ **INCONSISTENT** |

**Status**: ⚠️ One inconsistency found

**Issue**: `_resource_download_url` breaks the `_aiad_*` pattern used for all other resource meta fields. The documented convention in [`inc/post-types.php:163`](inc/post-types.php:163) states:
> Resource post type: _resource_* for download URLs, _aiad_* for resource-specific fields

However, this creates confusion. All other resource fields use `_aiad_*`, including `_aiad_download_count` which is related to downloads.

**Recommendation**: Either:
1. Rename `_resource_download_url` to `_aiad_download_url` for full consistency, OR
2. Rename `_aiad_download_count` to `_resource_download_count` to match the download URL pattern

---

#### Timeline (`aiad_timeline` post type)
| Meta Key | Description | Pattern |
|----------|-------------|---------|
| `_aiad_timeline_source` | 'auto' or 'manual' | `_aiad_timeline_*` ✅ |
| `_aiad_timeline_auto_type` | Auto-generated type | `_aiad_timeline_*` ✅ |
| `_aiad_timeline_related_id` | Related post ID | `_aiad_timeline_*` ✅ |
| `_aiad_timeline_pinned` | Pinned status | `_aiad_timeline_*` ✅ |
| `_aiad_timeline_icon` | Icon key | `_aiad_timeline_*` ✅ |
| `_aiad_timeline_card_type` | Card type | `_aiad_timeline_*` ✅ |
| `_aiad_timeline_link_url` | CTA link URL | `_aiad_timeline_*` ✅ |
| `_aiad_timeline_link_label` | CTA link label | `_aiad_timeline_*` ✅ |
| `_aiad_timeline_video_url` | Video embed URL | `_aiad_timeline_*` ✅ |
| `_aiad_timeline_linkedin_url` | LinkedIn post URL | `_aiad_timeline_*` ✅ |
| `_aiad_timeline_like_count` | Like counter | `_aiad_timeline_*` ✅ |

**Status**: ✅ Consistent

---

### Meta Field Summary

| Post Type | Prefix | Status |
|-----------|--------|--------|
| `partner` | `_partner_*` | ✅ Consistent |
| `featured_resource` | `_featured_resource_*` | ✅ Consistent |
| `resource` | `_aiad_*` (mostly) | ⚠️ One exception |
| `aiad_timeline` | `_aiad_timeline_*` | ✅ Consistent |

---

## 3. Taxonomies

### Taxonomy Registry

| Taxonomy | Post Types | Labels | Slug Consistency |
|----------|------------|--------|------------------|
| `resource_type` | resource, featured_resource | "Resource Types" | ✅ |
| `resource_principle` | resource, featured_resource | "Themes" | ⚠️ Slug says "principle", UI says "Themes" |
| `resource_duration` | resource, featured_resource | "Session length" | ✅ |
| `activity_type` | resource, featured_resource | "Activity Types" | ✅ |
| `partner_type` | partner | "Partner Types" | ✅ |
| `timeline_category` | aiad_timeline | (not shown in UI) | ✅ |

### Taxonomy Issues

1. **`resource_principle` vs "Themes"**: The taxonomy slug is `resource_principle` but the UI label is "Themes". This is documented in [`inc/post-types.php:31`](inc/post-types.php:31):
   > Note: Taxonomy slug is 'resource_principle' but UI label is 'Themes' for clarity.

   This is intentional but could cause confusion for developers. The slug references "principle" because it maps to the Five Core Principles section.

---

## 4. Function Naming Conventions

### Pattern
```
aiad_{entity}_{action}
```

### Examples

| Function | Purpose | Pattern |
|----------|---------|---------|
| `aiad_register_post_types()` | Register CPTs | `aiad_register_*` |
| `aiad_register_resource_meta()` | Register meta | `aiad_register_*` |
| `aiad_customize_register()` | Register customizer | `aiad_*_register` |
| `aiad_save_partner_url()` | Save meta | `aiad_save_{entity}_{field}` |
| `aiad_save_resource_details()` | Save meta | `aiad_save_{entity}_*` |
| `aiad_get_customizer_defaults()` | Get defaults | `aiad_get_*` |
| `aiad_get_front_page_sections()` | Get sections | `aiad_get_*` |

**Status**: ✅ Consistent

---

## 5. Nonce Naming

| Context | Nonce Field | Action |
|---------|-------------|--------|
| Partner URL | `aiad_partner_url_nonce` | `aiad_partner_url_nonce` |
| Partner Stats | `aiad_partner_stats_nonce` | `aiad_partner_stats_save` |
| Featured Resource | `aiad_featured_resource_nonce` | `aiad_featured_resource_nonce` |
| Resource Details | `aiad_resource_details_nonce` | `aiad_resource_details_nonce` |
| Resource Content | `aiad_content_sections_nonce` | `aiad_content_sections_nonce` |
| Timeline | `aiad_timeline_meta_nonce` | `aiad_timeline_meta_save` |
| Homepage Editor | `aiad_homepage_editor_nonce` | `aiad_homepage_editor_save` |
| Contact Form | `nonce` (AJAX) | `aiad_contact_nonce` |
| Timeline AJAX | `nonce` (AJAX) | `aiad_timeline_nonce` |
| Filter AJAX | `filter_nonce` | `aiad_filter_nonce` |

**Status**: ✅ Consistent pattern: `aiad_{context}_nonce` for field, `aiad_{context}_{action}` for verification

---

## 6. Recommendations

### High Priority

1. **Standardize Resource Download URL Meta Key**
   - Current: `_resource_download_url`
   - Recommended: `_aiad_download_url`
   - Files to update:
     - [`inc/meta-boxes.php:382`](inc/meta-boxes.php:382) - read
     - [`inc/meta-boxes.php:518`](inc/meta-boxes.php:518) - save
     - [`inc/ajax-handlers.php:484`](inc/ajax-handlers.php:484) - read
     - [`inc/post-types.php:163`](inc/post-types.php:163) - documentation
   - Requires data migration for existing resources

### Medium Priority

2. **Simplify Homepage Editor Resource Settings**
   - Current: `aiad_homepage_handpicked_resource_{n}`
   - Recommended: `aiad_handpicked_resource_{n}`
   - This matches the pattern used for titles: `aiad_handpicked_resources_title`

### Low Priority

3. **Document Taxonomy Slug Discrepancy**
   - Add a code comment explaining why `resource_principle` slug is used for "Themes" UI
   - Already documented in post-types.php but could be clearer

---

## 7. Summary Table

| System | Status | Issues |
|--------|--------|--------|
| Customizer Settings | ✅ Good | Minor naming verbosity |
| Partner Meta | ✅ Consistent | None |
| Featured Resource Meta | ✅ Consistent | None |
| Resource Meta | ⚠️ Mostly Good | 1 inconsistent key |
| Timeline Meta | ✅ Consistent | None |
| Taxonomies | ✅ Good | Documented slug/UI difference |
| Function Names | ✅ Consistent | None |
| Nonce Names | ✅ Consistent | None |

---

## 8. Migration Path (if implementing recommendations)

### For `_resource_download_url` → `_aiad_download_url`

```php
// Add to theme activation or a migration script
function aiad_migrate_download_url_meta(): void {
    $resources = get_posts( array(
        'post_type' => 'resource',
        'posts_per_page' => -1,
        'meta_key' => '_resource_download_url',
    ) );
    
    foreach ( $resources as $post ) {
        $old_value = get_post_meta( $post->ID, '_resource_download_url', true );
        if ( $old_value ) {
            update_post_meta( $post->ID, '_aiad_download_url', $old_value );
            delete_post_meta( $post->ID, '_resource_download_url' );
        }
    }
}
```

---

*Generated: 2026-02-24*
