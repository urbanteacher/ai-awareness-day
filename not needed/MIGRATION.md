# Fixing broken images after moving WordPress to a new server

When you export/move WordPress to a new server, images break because the **database still contains the old site URL** (e.g. `http://localhost/ai-awareness-day` or a staging URL). The theme uses WordPress APIs correctly; the fix is to update all stored URLs in the database.

---

## 1. Update Site URL and Home URL (quick fix for login/admin)

In **wp-config.php** (in the site root, **above** the line that says `/* That's all, stop editing! */`), add:

```php
define( 'WP_HOME', 'https://your-new-domain.com' );
define( 'WP_SITEURL', 'https://your-new-domain.com' );
```

Replace `https://your-new-domain.com` with your actual new URL (with or without trailing slash—WordPress normalizes it).

This fixes the main site and admin URLs so you can log in. It does **not** fix image URLs stored in content, theme mods, or attachment metadata. For that, do step 2.

---

## 2. Replace all old URLs in the database (fixes images)

You must replace **every occurrence** of the old URL with the new one across **all tables**, and the replacement must respect **serialized data** (WordPress stores arrays with length prefixes; a plain find-replace can corrupt them).

### Option A: WP-CLI (recommended if you have SSH)

```bash
# Run from server (or locally if DB is accessible)
wp search-replace 'http://old-url.local' 'https://your-new-domain.com' --all-tables
# If you used HTTPS on old site:
wp search-replace 'https://old-url.local' 'https://your-new-domain.com' --all-tables
```

Use the exact old URL (protocol, no trailing slash). Run **dry-run** first to preview:

```bash
wp search-replace 'http://old-url.local' 'https://your-new-domain.com' --all-tables --dry-run
```

### Option B: Better Search Replace plugin

1. Install **Better Search Replace** from the WordPress plugin directory.
2. Go to **Tools → Better Search Replace**.
3. **Search for:** your old URL (e.g. `http://localhost/ai-awareness-day`).
4. **Replace with:** your new URL (e.g. `https://yoursite.com`).
5. Select **all tables**.
6. Check **“Run as dry run”** first, then run. Review the report.
7. Uncheck dry run and run again to apply.

### Option C: Interconnect/IT script (no plugin, one-time use)

1. Download the script: [interconnectit.com/search-replace-db](https://interconnectit.com/products/search-replace-db/).
2. Upload the script to your server (e.g. in site root), run it in the browser, enter DB credentials and old/new URLs.
3. **Delete the script** after use for security.

---

## 3. Checklist

- [ ] **Back up the database** before any search-replace.
- [ ] Use the **exact** old URL (e.g. `http://localhost/ai-awareness-day` with no trailing slash) so all variants (uploads, theme mods, content) are updated.
- [ ] Replace **both** `http://` and `https://` versions of the old URL if you used both.
- [ ] After replacing, clear any **caches** (plugin, server, CDN) and test images (hero logo, partner logos, display board images, resource thumbnails).

---

## 4. If images are still broken

- Confirm **wp-content/uploads** was copied to the new server and is readable by the web server.
- In **Settings → Media**, ensure “Store uploads in this folder” is the default (`wp-content/uploads`) and that the folder exists.
- Re-save **permalink** structure once: **Settings → Permalinks → Save** (no need to change anything).

Your theme uses `get_template_directory_uri()`, `home_url()`, and attachment/theme mod URLs from the database; once the DB URLs are updated and uploads are in place, images will load on the new server.

---

# Get Involved contact form on the new host (e.g. Hostinger)

The “Get Involved” form submits via AJAX to WordPress `admin-ajax.php`. For it to work after moving to Hostinger (or any host):

## 1. Site URL must be correct

The form sends the request to your **current** site URL (from **Settings → General**: WordPress Address and Site Address). If you still have the old URL there:

- Either run the **database search-replace** from the migration section above (old URL → new URL), **or**
- In **wp-config.php** add:
  ```php
  define( 'WP_HOME', 'https://yourdomain.com' );
  define( 'WP_SITEURL', 'https://yourdomain.com' );
  ```
  so that `admin-ajax.php` is called on your real domain.

## 2. Set the notification email

1. In WordPress admin go to **Appearance → Customize**.
2. Open **Get Involved Section**.
3. Set **Notification Email** to the address that should receive form submissions (e.g. your team email).  
   If this is empty, WordPress uses the site’s admin email.

## 3. If the form does nothing or shows an error

- **Browser console (F12 → Console):** Check for errors when you click Submit (e.g. 404 = wrong URL; failed to fetch = request not reaching the server).
- **Network tab (F12 → Network):** Submit the form and find the request to `admin-ajax.php`. Check:
  - **Status:** 200 = request reached WordPress; 403/500 = server/security issue.
  - **Response:** If it’s JSON with `success: false`, read `data.message` for the reason (e.g. nonce or validation).

## 4. If you get “success” but no email arrives

Many shared hosts (including Hostinger) don’t deliver PHP `mail()` reliably, or messages go to spam. Use **SMTP**:

1. Install an SMTP plugin, e.g. **WP Mail SMTP** or **Post SMTP**.
2. Configure it with your Hostinger email SMTP settings (or another provider like Gmail/SendGrid). Hostinger’s docs or control panel usually list SMTP server, port, and security (TLS/SSL).
3. Send a test email from the plugin, then submit the form again. Submissions are sent with `wp_mail()`, so once WordPress sends via SMTP, the form emails will use it.

## 5. Caching and security

- If you use a **caching plugin** or **Hostinger cache**, ensure `admin-ajax.php` is **not cached** (most cache plugins exclude it by default).
- If a **security/firewall plugin** blocks requests, allow or whitelist `admin-ajax.php` and the action `aiad_contact` if it has such an option.

Summary: fix Site URL, set the notification email in Customize, then if emails don’t arrive, set up SMTP; use the browser console and Network tab to debug if the form doesn’t submit.
