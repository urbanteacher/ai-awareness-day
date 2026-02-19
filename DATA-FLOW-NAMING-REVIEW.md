# Data Flow & Naming Convention Review
## AI Awareness Day WordPress Theme

**Date:** 2026-02-19  
**Scope:** Complete review of data flow, naming conventions, and relationships across the application

---

## Executive Summary

This review examines how data flows through the application and identifies inconsistencies in naming conventions across post types, meta fields, taxonomies, functions, and JavaScript variables. The primary focus is on the relationship between **Resources**, **Partners**, and **Resources from partners** (featured_resource).

### Key Findings

1. **✅ Strengths:** Consistent post type naming, good taxonomy sharing, clear function prefixes
2. **⚠️ Issues Found:** Missing explicit relationship between featured_resource and partner, inconsistent meta field naming, variable naming inconsistencies
3. **🔧 Recommendations:** Standardize meta field prefixes, clarify relationship model, improve variable naming consistency

---

## 1. Data Model Overview

### 1.1 Post Types

| Post Type | Slug | Purpose | Archive URL |
|-----------|------|---------|-------------|
| **Resource** | `resource` | Internal downloadable resources (PDFs, PPTX) | `/resources/` |
| **Partner** | `partner` | Partner organizations (schools, companies, sponsors) | `/partners/` |
| **Resources from partners** | `featured_resource` | External resources from partner organizations | `/from-partners/` |
| **Form Submissions** | `form_submission` | Contact form submissions | N/A (admin only) |

**Status:** ✅ **GOOD** - Post type slugs are clear and consistent

### 1.2 Relationships

#### Current State:
- **Resource** ↔ **Featured Resource**: Share taxonomies (`resource_type`, `resource_principle`, `resource_duration`, `activity_type`)
- **Featured Resource** ↔ **Partner**: **NO EXPLICIT RELATIONSHIP** - stores organization name as text meta field
- **Resource** ↔ **Partner**: No direct relationship

#### Problem:
The relationship between `featured_resource` and `partner` is **implicit** rather than explicit:
- `featured_resource` stores `_featured_resource_org_name` as plain text
- No foreign key or taxonomy linking to `partner` post type
- This means you can't query "all resources from Partner X" without text matching

**Impact:** 
- Cannot reliably link featured resources to partner posts
- Duplicate organization names possible
- No way to show "Resources from this Partner" on partner single pages

---

## 2. Naming Convention Analysis

### 2.1 Meta Field Naming

#### Resource Meta Fields:
```php
_resource_download_url          // ✅ Clear: download URL for resources
_aiad_key_stage                // ✅ Good: prefixed with aiad_
_aiad_subtitle                 // ✅ Good: prefixed with aiad_
_aiad_duration                 // ⚠️  Confusing: conflicts with taxonomy resource_duration
_aiad_level                    // ✅ Good
_aiad_status                   // ✅ Good
_aiad_preparation              // ✅ Good
_aiad_learning_objectives      // ✅ Good
_aiad_instructions             // ✅ Good
// ... other _aiad_* fields
```

#### Featured Resource Meta Fields:
```php
_featured_resource_url         // ✅ Clear: external resource URL
_featured_resource_org_name    // ⚠️  Inconsistent: should be _featured_resource_org_name
_featured_resource_org_url     // ⚠️  Inconsistent: should be _featured_resource_org_url
```

#### Partner Meta Fields:
```php
_partner_url                   // ✅ Clear: partner website URL
_partner_stats                 // ✅ Clear: partner statistics/description
```

**Issues Found:**

1. **Inconsistent Prefixes:**
   - Resource fields: Mix of `_resource_*` and `_aiad_*`
   - Featured resource: Uses `_featured_resource_*` (longer)
   - Partner: Uses `_partner_*` (shorter)
   - **Recommendation:** Standardize to `_aiad_*` prefix for all custom meta fields

2. **Naming Length Inconsistency:**
   - `_featured_resource_org_name` vs `_partner_url`
   - Should be consistent: `_featured_resource_org_name` → `_aiad_featured_org_name` OR keep `_featured_resource_*` but make partner `_partner_*` consistent

3. **Missing Relationship Field:**
   - Should have `_featured_resource_partner_id` to link to partner post type

### 2.2 Taxonomy Naming

| Taxonomy | Slug | Used By | Status |
|----------|------|---------|--------|
| Resource Type | `resource_type` | `resource`, `featured_resource` | ✅ Good |
| Themes | `resource_principle` | `resource`, `featured_resource` | ⚠️  Confusing name |
| Session Length | `resource_duration` | `resource`, `featured_resource` | ✅ Good |
| Activity Type | `activity_type` | `resource`, `featured_resource` | ✅ Good |
| Partner Type | `partner_type` | `partner` only | ✅ Good |

**Issues Found:**

1. **Taxonomy Name Confusion:**
   - `resource_principle` is used for "Themes" (Safe, Smart, Creative, etc.)
   - The name "principle" doesn't match the UI label "Themes"
   - **Recommendation:** Consider renaming to `resource_theme` for clarity (requires migration)

### 2.3 Function Naming

**Pattern:** `aiad_*` prefix is used consistently ✅

Examples:
- `aiad_register_post_types()`
- `aiad_get_filter_counts()`
- `aiad_handle_contact_form()`
- `aiad_featured_resource_meta_box()`

**Status:** ✅ **EXCELLENT** - Consistent prefixing

### 2.4 Variable Naming in Code

#### PHP Variables:
```php
// Inconsistent naming found:
$partner_resources    // Sometimes used for featured_resource
$featured_resources   // Sometimes used
$org                  // Short form
$org_name             // Long form
$organisation         // British spelling
$organization         // Not found (American spelling not used)
```

**Issues Found:**

1. **Inconsistent Variable Names:**
   - `$partner_resources` vs `$featured_resources` - both refer to `featured_resource` post type
   - `$org` vs `$org_name` - both store organization name
   - **Recommendation:** Standardize to `$featured_resources` and `$org_name`

2. **British vs American Spelling:**
   - Code uses "organisation" (British) consistently ✅
   - UI labels use "organization" in some places
   - **Recommendation:** Choose one spelling and use consistently

#### JavaScript Variables:
```javascript
// In resource-filters.js:
var postType = isFeatured ? 'featured_resource' : 'resource';  // ✅ Good
var isExternal = !!(resource.external_url && postType === 'featured_resource');  // ✅ Good
```

**Status:** ✅ **GOOD** - JavaScript naming is consistent

---

## 3. Data Flow Analysis

### 3.1 Resource Creation Flow

```
Admin → Add New Resource
  ├─ Post Type: resource
  ├─ Taxonomies: resource_type, resource_principle, resource_duration, activity_type
  ├─ Meta: _resource_download_url, _aiad_* fields
  └─ Display: /resources/ archive, single-resource.php
```

**Status:** ✅ Clear and well-defined

### 3.2 Featured Resource Creation Flow

```
Admin → Add New Resource from Partner
  ├─ Post Type: featured_resource
  ├─ Taxonomies: resource_type, resource_principle, resource_duration, activity_type (shared)
  ├─ Meta: _featured_resource_url, _featured_resource_org_name, _featured_resource_org_url
  └─ Display: /from-partners/ archive, front-page.php (teaser)
```

**Issues:**
- No link to `partner` post type
- Organization name stored as text (duplication risk)

### 3.3 Partner Creation Flow

```
Admin → Add New Partner
  ├─ Post Type: partner
  ├─ Taxonomy: partner_type
  ├─ Meta: _partner_url, _partner_stats
  └─ Display: /partners/ archive, single-partner.php
```

**Status:** ✅ Clear, but missing relationship to featured resources

### 3.4 Query Patterns

#### Current Queries:
```php
// Get resources (with filters)
WP_Query([
    'post_type' => 'resource',
    'tax_query' => [...],
    'meta_query' => [...]
])

// Get featured resources (with filters)
WP_Query([
    'post_type' => 'featured_resource',
    'tax_query' => [...],  // Same taxonomies as resource
])

// Get partners
WP_Query([
    'post_type' => 'partner',
])
```

**Missing Query:**
```php
// Cannot currently do:
// "Get all featured resources from Partner X"
// Would require text matching on _featured_resource_org_name
```

---

## 4. Specific Issues & Recommendations

### Issue 1: Missing Explicit Relationship

**Problem:** `featured_resource` stores organization name as text, not a relationship to `partner` post type.

**Current:**
```php
// featured_resource meta
_featured_resource_org_name = "STEM Learning"  // Text string
```

**Recommended:**
```php
// Option A: Add relationship meta field
_featured_resource_partner_id = 123  // Post ID of partner

// Option B: Use taxonomy relationship
// Create taxonomy: featured_resource_partner
// Link featured_resource to partner via taxonomy
```

**Impact:** Medium - Affects data integrity and query capabilities

**Priority:** Medium

---

### Issue 2: Inconsistent Meta Field Naming

**Problem:** Mix of prefixes (`_resource_*`, `_featured_resource_*`, `_partner_*`, `_aiad_*`)

**Current:**
```php
_resource_download_url
_featured_resource_org_name
_partner_url
_aiad_key_stage
```

**Recommended Standardization:**
```php
// Option A: All use _aiad_ prefix
_aiad_resource_download_url
_aiad_featured_org_name
_aiad_partner_url
_aiad_key_stage

// Option B: Keep post-type-specific prefixes (but consistent)
_resource_download_url
_featured_resource_url
_featured_resource_org_name
_featured_resource_org_url
_partner_url
_partner_stats
_aiad_* (for resource-specific fields)
```

**Impact:** Low - Cosmetic, but affects maintainability

**Priority:** Low

---

### Issue 3: Variable Naming Inconsistencies

**Problem:** Same concept uses different variable names

**Current:**
```php
$partner_resources  // Sometimes
$featured_resources // Sometimes
$org                // Sometimes
$org_name           // Sometimes
```

**Recommended:**
```php
// Always use:
$featured_resources  // For featured_resource post type queries
$org_name            // For organization name (full form)
```

**Impact:** Low - Code readability

**Priority:** Low

---

### Issue 4: Taxonomy Name Mismatch

**Problem:** `resource_principle` taxonomy stores "Themes" (Safe, Smart, Creative, etc.)

**Current:**
- Taxonomy slug: `resource_principle`
- UI label: "Themes"
- Terms: Safe, Smart, Creative, Responsible, Future

**Recommended:**
- Rename taxonomy to `resource_theme` for clarity
- **Note:** Requires database migration and code updates

**Impact:** Low - Works correctly, just confusing name

**Priority:** Low (future enhancement)

---

## 5. Data Flow Diagrams

### Current Architecture:

```
┌─────────────┐
│   Resource  │
│  (internal) │
└──────┬──────┘
       │
       ├─ Uses: resource_type, resource_principle, resource_duration, activity_type
       └─ Has: _resource_download_url, _aiad_* fields

┌─────────────┐
│   Partner   │
│ (orgs/schools)│
└─────────────┘
       │
       └─ Has: _partner_url, _partner_stats
           │
           └─ ❌ NO EXPLICIT LINK TO featured_resource

┌──────────────────────┐
│ Featured Resource    │
│ (external resources) │
└──────┬───────────────┘
       │
       ├─ Uses: resource_type, resource_principle, resource_duration, activity_type
       └─ Has: _featured_resource_url, _featured_resource_org_name (text)
           │
           └─ ❌ Stores org name as TEXT, not relationship to Partner
```

### Recommended Architecture:

```
┌─────────────┐
│   Resource  │
│  (internal) │
└──────┬──────┘
       │
       └─ Uses shared taxonomies
           │
           └─ Shares with ──┐
                             │
┌──────────────────────┐    │
│ Featured Resource    │    │
│ (external resources) │    │
└──────┬───────────────┘    │
       │                    │
       ├─ Uses shared taxonomies ──┘
       └─ Has: _featured_resource_url
           └─ Links to ──┐
                         │
              ┌──────────┴──────────┐
              │                     │
        ┌─────────────┐    ┌─────────────┐
        │   Partner   │    │   Partner   │
        │  (via ID)   │    │  (via ID)   │
        └─────────────┘    └─────────────┘
```

---

## 6. Recommendations Summary

### High Priority
1. **Add explicit relationship** between `featured_resource` and `partner`
   - Add `_featured_resource_partner_id` meta field
   - Update meta box to allow selecting partner from dropdown
   - Update queries to support "resources from partner X"

### Medium Priority
2. **Standardize meta field naming**
   - Choose consistent prefix pattern
   - Update all meta field references
   - Document naming convention

3. **Standardize variable naming**
   - Use `$featured_resources` consistently (not `$partner_resources`)
   - Use `$org_name` consistently (not `$org`)

### Low Priority
4. **Consider renaming taxonomy**
   - `resource_principle` → `resource_theme` (requires migration)

5. **Spelling consistency**
   - Choose British or American spelling for "organisation/organization"
   - Use consistently across code and UI

---

## 7. Code Examples

### Current Query (No Relationship):
```php
// Cannot query "resources from Partner X" - must use text matching
$featured_resources = new WP_Query([
    'post_type' => 'featured_resource',
    'meta_query' => [
        [
            'key' => '_featured_resource_org_name',
            'value' => 'STEM Learning',  // Text match - fragile!
            'compare' => '='
        ]
    ]
]);
```

### Recommended Query (With Relationship):
```php
// Can query "resources from Partner X" using post ID
$partner_id = 123;  // Partner post ID
$featured_resources = new WP_Query([
    'post_type' => 'featured_resource',
    'meta_query' => [
        [
            'key' => '_featured_resource_partner_id',
            'value' => $partner_id,
            'compare' => '='
        ]
    ]
]);
```

---

## 8. Migration Considerations

If implementing the relationship field:

1. **Backward Compatibility:**
   - Keep `_featured_resource_org_name` for existing data
   - Add `_featured_resource_partner_id` as optional
   - Create migration script to match org names to partner posts

2. **Data Migration Script:**
```php
// Pseudo-code for migration
foreach (featured_resources as $resource) {
    $org_name = get_post_meta($resource->ID, '_featured_resource_org_name');
    $partner = find_partner_by_name($org_name);
    if ($partner) {
        update_post_meta($resource->ID, '_featured_resource_partner_id', $partner->ID);
    }
}
```

---

## 9. Conclusion

The application has a **solid foundation** with consistent post type naming and good taxonomy sharing. The main areas for improvement are:

1. **Adding explicit relationships** between featured resources and partners
2. **Standardizing naming conventions** for better maintainability
3. **Improving variable naming consistency** for code readability

These improvements will enhance data integrity, query capabilities, and long-term maintainability.

---

**Review Status:** Complete  
**Next Steps:** Prioritize recommendations and plan implementation
