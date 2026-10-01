#!/usr/bin/env bash
# Rehearse a git deploy on a throwaway WordPress 7.1 site, before pushing to the server.
#
#   scripts/rehearse-deploy.sh [backup.sql[.backup]]   build the site, deploy, check, leave it running on :8890
#   scripts/rehearse-deploy.sh --down                  remove the site and its databases
#
# What it does: exports the tracked files (the git index, which is what a push puts on the server: no node_modules, no
# demo files) into a theme folder, restores the database backup into a new database, starts a fresh WordPress 7.1 /
# PHP 8.3 container with ONLY the theme (the theme must install its own plugins, as it will on Hostinger), makes the
# first requests, runs WordPress's database upgrade, then reports: plugins installed and active, migrations recorded,
# every original post still there, where the old content of each changed post is kept, tables and Customizer values
# unchanged, every listed page's status, and the PHP debug log. Needs the local docker stack up (docker compose up -d).
set -euo pipefail
cd "$(dirname "$0")/.."

NAME=aiad-rehearsal
PORT=8890
DB=docker
DBC=ai-awareness-day-db-1
SQL() { docker exec -i "$DBC" mysql -uroot -pwordpress_root "$@" 2>&1 | grep -v "Using a password" || true; }

if [ "${1:-}" = "--down" ]; then
	docker rm -f "$NAME" >/dev/null 2>&1 || true
	SQL -e "DROP DATABASE IF EXISTS rehearsal; DROP DATABASE IF EXISTS rehearsal_base;"
	rm -rf "${TMPDIR:-/tmp}/aiad-deploy"
	echo "rehearsal site removed"
	exit 0
fi

BACKUP="${1:-local-db-before-wp71-2026-09-30.sql.backup}"
[ -f "$BACKUP" ] || { echo "no backup at $BACKUP"; exit 1; }
TREE="${TMPDIR:-/tmp}/aiad-deploy"

echo "== exporting the tracked files (git index)"
rm -rf "$TREE"; mkdir -p "$TREE"
git checkout-index -a -f --prefix="$TREE/"
echo "   $(find "$TREE" -type f | wc -l | tr -d ' ') files, node_modules: $(find "$TREE" -name node_modules | wc -l | tr -d ' ')"

echo "== restoring $BACKUP into new databases"
for d in rehearsal rehearsal_base; do
	SQL -e "DROP DATABASE IF EXISTS $d; CREATE DATABASE $d CHARACTER SET utf8mb4; GRANT ALL ON $d.* TO 'wordpress'@'%'; FLUSH PRIVILEGES;"
	# Same-length address swap, so serialized values stay valid.
	sed "s#http://localhost:8888#http://localhost:$PORT#g" "$BACKUP" | SQL "$d"
done

echo "== starting a clean WordPress 7.1 with only the theme"
docker rm -f "$NAME" >/dev/null 2>&1 || true
docker run -d --name "$NAME" --network ai-awareness-day_default -p "$PORT:80" \
	-e WORDPRESS_DB_HOST=db:3306 -e WORDPRESS_DB_USER=wordpress -e WORDPRESS_DB_PASSWORD=wordpress -e WORDPRESS_DB_NAME=rehearsal \
	-e WORDPRESS_DEBUG=1 -e WORDPRESS_CONFIG_EXTRA="define('WP_DEBUG_DISPLAY', false); define('WP_DEBUG_LOG', '/tmp/wp-debug.log');" \
	-v "$TREE":/var/www/html/wp-content/themes/ai-awareness-day wordpress:7.1-php8.3-apache >/dev/null
sleep 10
# A live site has the old /national-conversation/ route stored in its permalink rules; plant it, as the backup may not have it.
docker exec "$NAME" php -r 'define("SHORTINIT", true); require "/var/www/html/wp-load.php"; $r = (array) get_option("rewrite_rules"); update_option("rewrite_rules", array("^national-conversation/?$" => "index.php?aiad_nc_page=1") + $r);' >/dev/null 2>&1 || true
for i in 1 2; do curl -s -o /dev/null -w "   deploy request $i: HTTP %{http_code}\n" "http://localhost:$PORT/"; done
curl -s -o /dev/null -w "   /national-conversation/ with the old route stored: HTTP %{http_code} %{redirect_url}\n" "http://localhost:$PORT/national-conversation/"
curl -s -o /dev/null -w "   core database upgrade: HTTP %{http_code}\n" "http://localhost:$PORT/wp-admin/upgrade.php?step=1"

echo "== deploy result"
SQL -t -e "SELECT option_name, LEFT(option_value,50) AS value FROM rehearsal.wp_options WHERE option_name IN ('aiad_homepage_converted','aiad_theme_pages_converted','aiad_shortcodes_converted','aiad_homepage_conversion_error','aiad_theme_pages_conversion_error','page_on_front','db_version');"
echo "   active plugins: $(SQL -N -e "SELECT option_value FROM rehearsal.wp_options WHERE option_name='active_plugins';" | grep -oE '"[a-z-]+/[a-z-]+\.php"' | tr '\n' ' ')"

echo "== data compared with the untouched backup"
SQL -t -e "SELECT COUNT(*) AS original_posts, SUM(a.ID IS NULL) AS missing, SUM(a.ID IS NOT NULL AND (a.post_status<>b.post_status OR a.post_type<>b.post_type OR a.post_title<>b.post_title)) AS type_status_title_changed FROM rehearsal_base.wp_posts b LEFT JOIN rehearsal.wp_posts a ON a.ID=b.ID;"
SQL -t -e "SELECT b.post_type, COUNT(*) AS content_changed, SUM(EXISTS(SELECT 1 FROM rehearsal.wp_posts r WHERE r.post_parent=a.ID AND r.post_type='revision' AND r.post_content=b.post_content)) AS old_text_in_revision, SUM(EXISTS(SELECT 1 FROM rehearsal.wp_postmeta m WHERE m.post_id=a.ID AND m.meta_key IN ('_aiad_pre_block_content','_aiad_builtin_content') AND m.meta_value=b.post_content)) AS old_text_in_field FROM rehearsal_base.wp_posts b JOIN rehearsal.wp_posts a ON a.ID=b.ID WHERE a.post_content<>b.post_content GROUP BY b.post_type;"
SQL -N -e "SELECT 'Customizer values identical:', (SELECT MD5(option_value) FROM rehearsal_base.wp_options WHERE option_name='theme_mods_ai-awareness-day')=(SELECT MD5(option_value) FROM rehearsal.wp_options WHERE option_name='theme_mods_ai-awareness-day');"
for t in $(SQL -N -e "SELECT table_name FROM information_schema.tables WHERE table_schema='rehearsal' AND (table_name LIKE 'wp\_airb\_%' OR table_name LIKE 'wp\_aiadn\_%');"); do
	SQL -N -e "SELECT '$t', (SELECT COUNT(*) FROM rehearsal_base.$t)=(SELECT COUNT(*) FROM rehearsal.$t);"
done | awk '{ if ($2 != 1) { bad++; print "   ROW COUNT DIFFERS: " $1 } n++ } END { print "   custom tables with the same row count: " n - bad "/" n }'

echo "== every page the site lists"
python3 - "$PORT" <<'PY'
import re, sys, urllib.request, urllib.error, collections
base = 'http://localhost:%s' % sys.argv[1]
def get(u):
    try:
        r = urllib.request.urlopen(urllib.request.Request(u, headers={'User-Agent': 'aiad-rehearsal'}), timeout=30)
        return r.status, r.read().decode('utf8', 'replace')
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode('utf8', 'replace')
    except Exception as e:
        return 0, str(e)
urls = set()
for m in re.findall(r'<loc>([^<]+)</loc>', get(base + '/wp-sitemap.xml')[1]):
    urls |= set(re.findall(r'<loc>([^<]+)</loc>', get(m)[1]))
urls |= {base + p for p in ['/', '/partners/', '/resources/', '/timeline/', '/events/', '/ai-tools/', '/national-conversation/', '/walkthrough/', '/assets-pack/', '/?s=ai']}
rx = {'PHP message': r'Fatal error|Warning:|Notice:|Deprecated:|Parse error', 'block not registered': r'<!-- wp:aiad/', 'literal shortcode': r'\[(?:aiad_|ai_risk_)[a-z_]*[^\]]*\]', '503 updating page': r'This site is being updated', 'generator tag': r'<meta name="generator"'}
status, hits = collections.Counter(), collections.Counter()
for u in sorted(urls):
    code, body = get(u); status[code] += 1
    for k, v in rx.items():
        if re.search(v, body, re.I): hits[k] += 1
print('   %d pages, statuses %s' % (len(urls), dict(status)))
for k in rx: print('   %3d pages  %s' % (hits[k], k))
PY

echo "== php debug log"
docker exec "$NAME" bash -c 'if [ -s /tmp/wp-debug.log ]; then sed "s/^\[[^]]*\] //" /tmp/wp-debug.log | cut -c1-200 | sort | uniq -c | sort -rn | head -12; else echo "   (nothing logged)"; fi'
echo "site is up at http://localhost:$PORT  (scripts/rehearse-deploy.sh --down removes it)"
