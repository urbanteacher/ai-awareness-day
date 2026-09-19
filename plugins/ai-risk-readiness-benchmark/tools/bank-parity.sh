#!/usr/bin/env bash
# Prove the PHP reader and the app's TypeScript reader agree on a bank.
#
# Two readers that score the same bank differently are worse than one reader,
# so run this after touching either src/data/bank.ts or class-airb-bank.php.
#
#   tools/bank-parity.sh [bank-id] [path-to-app]
set -euo pipefail

BANK="${1:-benchmark-public}"
APP="${2:-$HOME/Desktop/Mobile App Benchmark/theory-mock}"
CONTAINER="${AIRB_CONTAINER:-ai-awareness-day-wordpress-1}"
PLUGIN_IN_CONTAINER=/var/www/html/wp-content/plugins/ai-risk-readiness-benchmark
WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT

# The app's reader, compiled from the real TypeScript rather than reimplemented.
( cd "$APP" && npx --no-install tsc src/data/bank.ts --ignoreConfig \
    --outDir "$WORK/js" --module commonjs --target es2020 --skipLibCheck >/dev/null )

cp "includes/data/banks/${BANK}.json" "$WORK/bank.json"

cat > "$WORK/ts.mjs" <<'EOF'
import { createRequire } from 'node:module';
import { readFileSync } from 'node:fs';
const require = createRequire(import.meta.url);
const dir = process.argv[2];
const { assertBank, visibleQuestions, scoreAnswers } = require(`${dir}/js/bank.js`);
const bank = assertBank(JSON.parse(readFileSync(`${dir}/bank.json`, 'utf8')));
const best = {}, worst = {};
for (const q of bank.questions) {
  const o = [...q.options].sort((a, b) => a.score - b.score);
  best[q.id] = o[0].value;
  worst[q.id] = o[o.length - 1].value;
}
const cases = {
  empty: {}, all_best: best, all_worst: worst,
  frequency_rarely: { pub_use_frequency: 'rarely' },
  frequency_daily: { pub_use_frequency: 'daily' },
  best_but_rarely: { ...best, pub_use_frequency: 'rarely' },
};
const out = {};
for (const [name, answers] of Object.entries(cases)) {
  const s = scoreAnswers(bank, answers);
  out[name] = { visible: visibleQuestions(bank, answers).length, score: s.score, answered: s.answered };
}
console.log(JSON.stringify(out, null, 4));
EOF

node "$WORK/ts.mjs" "$WORK" > "$WORK/ts.json"
docker exec "$CONTAINER" php "$PLUGIN_IN_CONTAINER/tools/bank-parity.php" "$BANK" > "$WORK/php.json"

if diff -q "$WORK/php.json" "$WORK/ts.json" >/dev/null; then
  echo "PARITY OK — php and ts agree on ${BANK}"
  cat "$WORK/php.json"
else
  echo "PARITY FAILED — ${BANK}"
  diff "$WORK/php.json" "$WORK/ts.json" || true
  exit 1
fi
