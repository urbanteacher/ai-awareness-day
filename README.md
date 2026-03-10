# AI Awareness Day — WordPress Theme

A modern, minimal WordPress theme built for the AI Awareness Day campaign. Green & white color scheme, fully responsive, with scroll animations and an AJAX contact form.

## Installation on Hostinger

1. **Install WordPress** on Hostinger via hPanel → Auto Installer → WordPress
2. **Upload the theme:**
   - Go to WordPress Admin → Appearance → Themes → Add New → Upload Theme
   - Upload the `ai-awareness-day.zip` file
   - Click "Install Now", then "Activate"
3. **Set the homepage:**
   - Go to Settings → Reading
   - Select "A static page" and choose your front page
   - Save Changes

## Customisation

All content is editable via **Appearance → Customize**:

| Section | Customizer Panel |
|---------|-----------------|
| Hero title & date | Hero Section |
| Campaign text | Campaign Section |
| Contact form email | Get Involved Section |

The **Get Involved** form adapts to the user type: **Teacher** (school name, subject), **Parent** (optional child’s school), **School leader** (school name, role), **Organisation** (organisation name, type). The recipient email receives the selected role and all relevant fields.
| Social links | Social Links |

### Resources & Partners (admin)

- **Resources** (WP Admin → Resources): Each resource has:
  - **Title**
  - **Resource Type** (Lesson Starter, Lesson Activity, Assembly) – e.g. “Lesson Starter” = Lesson Starter
  - **Themes** (Safe, Smart, Creative, Responsible, Future) – e.g. Theme Safe
  - **Session length** – 5-min lesson starters, 15–20 min tutor time plans, 20-min assemblies (script provided), 30–45 min after-school sessions. Shown in the Explore section and on resource cards.
  - **Featured image**
  - **Description** (main content)
  - **Download (PDF or PPTX)** – meta box: upload or select a PDF/PPTX; shows as a download button on the resource page and a link on the archive.
  Resources appear at `/resources/`. The **Explore the Themes** section on the homepage has “By theme” and “By session length” links that filter resources.
- **Partners** (WP Admin → Partners): Add teachers, sponsors, schools, or tech companies with **Title**, **Featured image** (logo), optional **Partner URL** (meta box), and **Partner Type** (Teacher, Sponsor, School, Tech Company). These appear at `/partners/`.

- **Resources from partners** (WP Admin → Resources from partners): Feature resources from other organisations. For each entry set **Title**, **Description** (or excerpt), optional **Featured image**, and in the meta box: **Resource URL** (required – link to their PDF, page or toolkit), **Organisation name**, and optional **Organisation website**. These appear at `/from-partners/` and are also shown in a “From other organisations” block on the main Resources page. You can add or move partner resources via WXR: **Resources → Export demo resources** (section "Export Resources from partners") and **Resources → Import demo resources** (accepts WXR files containing Resources and/or Resources from partners).

Add **Custom links** in **Appearance → Menus** to `/resources/`, `/partners/`, and optionally `/from-partners/` so visitors can reach these pages.

### Navigation Menu

1. Go to Appearance → Menus
2. Create a new menu and assign it to "Primary Navigation"
3. Add custom links pointing to section anchors or pages:
   - `#campaign` → Campaign
   - `#reach` → Reach
   - `#aim` → Aim
   - `#toolkit` → Toolkit
   - `#display-board` → Display board
   - `#contact` → Get Involved
   - `/resources/` → Resources
   - `/partners/` → Partners
4. For the CTA button style, add `menu-item-cta` as a CSS class to the last item
   (Enable CSS classes in Screen Options at the top of the Menus page)

### Adding Theme Images

For the **Themes grid** (Section 4), you can replace the placeholder cards with real images by editing `front-page.php` or by extending the customizer in `functions.php`.

## Theme Structure

```
ai-awareness-day/
├── style.css          ← Theme styles + WP metadata
├── functions.php      ← Setup, customizer, AJAX handler
├── header.php         ← Site header + navigation
├── footer.php         ← Footer + social links
├── front-page.php     ← Homepage with all 7 sections
├── index.php          ← Blog fallback
├── page.php           ← Standard page template
├── 404.php            ← 404 error page
├── screenshot.png     ← Theme preview
└── assets/
    └── js/
        └── main.js    ← Animations, nav, form handler
```

## Docker (local development)

- **What this does:** Runs WordPress + MySQL in Docker and mounts this theme into `wp-content/themes/ai-awareness-day`. You do **not** need PHP or MySQL installed locally.
- **Start containers:**
  - From the theme folder (where `docker-compose.yml` lives), run: `docker compose up -d`
  - Then open `http://localhost:9090` in your browser
- **First run:** WordPress will show the normal install screen. Choose a site title, username and password — the database connection is already configured by Docker.
- **Activate the theme:** After install, go to **Appearance → Themes** and activate **“AI Awareness Day”**.
- **Uploads:** The `wp_uploads` Docker volume persists `wp-content/uploads` so media survives container restarts.
- **Stopping:** Run `docker compose down` in the same folder to stop everything.
- **Login issues:** Always use `http://localhost:9090` (not `127.0.0.1`). Clear site cookies or use a private window if you see unexpected login behaviour.

### Resources page and “Apply filters” going to home

- Use **pretty permalinks**: go to **Settings → Permalinks**, choose “Post name” (or any non‑Plain option), click **Save**. That keeps the Resources URL as `/resources/` so the filter form stays on the archive.
- “No resources found” is normal until you add items under **WP Admin → Resources** (title, type, theme, session length, optional download).

## Requirements

- WordPress 6.0+
- PHP 7.4+

## License

GPL v2 or later
