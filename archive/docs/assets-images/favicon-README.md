# Favicon — One Source of Truth

Place your favicon files here. The theme reads from this folder automatically.

## Required file

| File | Size | Purpose |
|------|------|---------|
| `favicon.png` | **512 × 512 px** | Used everywhere — browser tab, bookmarks, Google search |

Drop a single square PNG here and the theme handles the rest.
WordPress's Site Icon (Appearance → Customize → Site Identity) takes precedence if set,
but this folder is the fallback and the recommended single source of truth.

## Optional extras (for maximum compatibility)

| File | Size | Purpose |
|------|------|---------|
| `favicon-32.png` | 32 × 32 px | Legacy browser tab icon |
| `favicon-180.png` | 180 × 180 px | Apple Touch icon (iOS home screen) |
| `favicon.ico` | 16/32 px multi | Very old browsers |

If only `favicon.png` is present, all devices will use it scaled down — good enough for most cases.
