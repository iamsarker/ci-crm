#!/usr/bin/env node
/**
 * Driver for the v2.0.0 Phase 2 pricing verification.
 *
 *   node pricingcheck.mjs
 *
 * The assertions themselves live in PHP (src/controllers/whmazadmin/Pricingcheck.php)
 * because they must exercise the real Pricing_model — re-implementing the
 * resolver here would only prove that two of my own guesses agree.
 *
 * This script owns the parts PHP is bad at from the CLI:
 *   - purging fixture rows a killed run left behind, in ONE round trip
 *     (the database is remote, so round trips are the whole cost model)
 *   - running the harness under a hard timeout instead of hanging
 *   - parsing the result into an exit code CI could use
 */
import { execFile } from 'node:child_process';
import { promisify } from 'node:util';
import { readFileSync } from 'node:fs';
import { resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const run = promisify(execFile);

const APP = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const MYSQL = '/opt/lampp/bin/mysql';
const TIMEOUT_MS = 15 * 60 * 1000;

// Fixture markers — must match Pricingcheck::FIXTURE_PERIOD_BASE and the
// email prefix used in makeFixtures().
const FIXTURE_EMAIL = 'pricingcheck+%';
const FIXTURE_PERIOD = 90;

function env() {
  const raw = readFileSync(resolve(APP, '.env'), 'utf8');
  const get = (k) => {
    const m = raw.match(new RegExp(`^${k}\\s*=\\s*(.*)$`, 'm'));
    return m ? m[1].trim().replace(/^["']|["']$/g, '') : '';
  };
  return {
    host: get('DB_HOSTNAME'), user: get('DB_USERNAME'),
    pass: get('DB_PASSWORD'), db: get('DB_DATABASE'),
  };
}

async function sql(cfg, statements) {
  const { stdout } = await run(MYSQL, [
    `-h${cfg.host}`, `-u${cfg.user}`, `-p${cfg.pass}`, cfg.db, '-N', '-B', '-e', statements,
  ], { maxBuffer: 8 << 20 });
  return stdout.trim();
}

/**
 * Delete fixture rows in dependency order. Keyed on the markers, not on ids
 * held in memory, so a run that was killed mid-suite is still cleaned up.
 */
async function purge(cfg) {
  return sql(cfg, `
    DELETE po FROM price_overrides po JOIN companies c ON c.id = po.owner_company_id
      WHERE c.email LIKE '${FIXTURE_EMAIL}';
    DELETE poa FROM price_override_audits poa JOIN companies c ON c.id = poa.owner_company_id
      WHERE c.email LIKE '${FIXTURE_EMAIL}';
    DELETE rp FROM reseller_profiles rp JOIN companies c ON c.id = rp.company_id
      WHERE c.email LIKE '${FIXTURE_EMAIL}';
    DELETE FROM price_overrides
      WHERE item_type = 1 AND pricing_id IN (SELECT id FROM (SELECT id FROM dom_pricing WHERE reg_period >= ${FIXTURE_PERIOD}) x);
    DELETE FROM price_override_audits
      WHERE item_type = 1 AND pricing_id IN (SELECT id FROM (SELECT id FROM dom_pricing WHERE reg_period >= ${FIXTURE_PERIOD}) x);
    DELETE FROM companies WHERE email LIKE '${FIXTURE_EMAIL}';
    DELETE FROM dom_pricing WHERE reg_period >= ${FIXTURE_PERIOD};
  `);
}

async function leftovers(cfg) {
  const out = await sql(cfg, `
    SELECT
      (SELECT COUNT(*) FROM companies WHERE email LIKE '${FIXTURE_EMAIL}'),
      (SELECT COUNT(*) FROM dom_pricing WHERE reg_period >= ${FIXTURE_PERIOD}),
      (SELECT COUNT(*) FROM price_overrides),
      (SELECT COUNT(*) FROM price_override_audits);
  `);
  const [companies, pricing, overrides, audits] = out.split(/\s+/).map(Number);
  return { companies, pricing, overrides, audits };
}

const bold = (s) => `\x1b[1m${s}\x1b[0m`;
const red = (s) => `\x1b[31m${s}\x1b[0m`;
const green = (s) => `\x1b[32m${s}\x1b[0m`;

async function main() {
  const cfg = env();
  console.log(bold(`\nPhase 2 pricing verification — ${cfg.db} @ ${cfg.host}\n`));

  console.log('· purging fixture rows from any earlier run…');
  await purge(cfg);

  const before = await leftovers(cfg);
  if (before.companies || before.pricing) {
    console.log(red(`  purge failed: ${before.companies} companies, ${before.pricing} pricing rows remain`));
    process.exit(2);
  }
  console.log(`  clean. price_overrides=${before.overrides} audits=${before.audits} (pre-existing)\n`);

  console.log('· running the PHP harness (real Pricing_model, remote DB — this takes a few minutes)…\n');
  let stdout = '';
  try {
    const r = await run('php', ['index.php', 'whmazadmin/pricingcheck', 'run'],
      { cwd: APP, timeout: TIMEOUT_MS, maxBuffer: 32 << 20 });
    stdout = r.stdout;
  } catch (e) {
    stdout = (e.stdout || '') + (e.stderr || '');
    if (e.killed) {
      console.log(red(`  harness exceeded ${TIMEOUT_MS / 1000}s and was killed`));
    }
  }

  // CI's CLI bootstrap emits deprecation notices before our first line; keep
  // only the harness's own output.
  const lines = stdout.split('\n').filter((l) => /^(\s{3}|-- |=== |PASS:|All checks|FAILURES|SCHEMA)/.test(l));
  for (const l of lines) {
    if (l.includes('[FAIL]')) console.log(red(l));
    else if (l.includes('[PASS]')) console.log(green(l));
    else console.log(l);
  }

  const pass = Number((stdout.match(/PASS:\s*(\d+)/) || [])[1] ?? 0);
  const fail = Number((stdout.match(/FAIL:\s*(\d+)/) || [])[1] ?? -1);

  console.log('\n· verifying the database was left clean…');
  const after = await leftovers(cfg);
  const dirty = after.companies || after.pricing
    || after.overrides !== before.overrides || after.audits !== before.audits;
  if (dirty) {
    console.log(red(`  LEAKED: companies=${after.companies} pricing=${after.pricing} `
      + `overrides ${before.overrides}->${after.overrides} audits ${before.audits}->${after.audits}`));
    await purge(cfg);
    console.log('  (purged)');
  } else {
    console.log(green('  clean — no fixture rows left behind'));
  }

  console.log('');
  if (fail === 0 && pass > 0 && !dirty) {
    console.log(green(bold(`✓ ${pass} checks passed`)));
    process.exit(0);
  }
  console.log(red(bold(`✗ pass=${pass} fail=${fail}${dirty ? ' (and the database was left dirty)' : ''}`)));
  process.exit(1);
}

main().catch((e) => { console.error(red(e.stack || String(e))); process.exit(3); });
