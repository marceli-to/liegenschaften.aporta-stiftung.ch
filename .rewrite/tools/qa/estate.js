// node estate.js <outdir> — the estate selector: open, switch to KORO, scoped pages, back
const { chromium } = require('playwright');
const fs = require('fs');
const out = process.argv[2]; fs.mkdirSync(out, { recursive: true });
const admin = 'https://liegenschaften.aporta-stiftung.ch.test';
const checks = []; const ok = (c, m) => checks.push(`${c ? 'ok  ' : 'FAIL'} ${m}`);
(async () => {
  const b = await chromium.launch();
  const ctx = await b.newContext({ ignoreHTTPSErrors: true, viewport: { width: 1440, height: 900 } });
  const p = await ctx.newPage();
  const log = [];
  p.on('pageerror', e => log.push(`pageerror ${e.message}`));
  p.on('console', m => m.type() === 'error' && log.push(`console ${m.text()}`));
  p.on('response', r => r.status() >= 400 && log.push(`${r.status()} ${r.url()}`));
  await p.goto(admin + '/login'); await p.fill('input[name=email]', 'qa@example.invalid'); await p.fill('input[name=password]', 'qa-password-123');
  await Promise.all([p.waitForNavigation(), p.click('button[type=submit], input[type=submit]')]);
  
  await p.goto(admin + '/administration/objekte', { waitUntil: 'networkidle' }); await p.waitForTimeout(500);
  ok((await p.locator('.page-title .dropdown-button').innerText()).trim() === 'EGLISTRASSE', 'title Eglistrasse');
  await p.hover('.page-title .dropdown-button'); await p.waitForTimeout(300);
  await p.screenshot({ path: `${out}/open.png`, clip: { x: 0, y: 0, width: 1440, height: 260 } });
  // Pointer moves down to the last entry: the dropdown must stay open
  await p.mouse.move(370, 39); await p.mouse.move(370, 145, { steps: 15 }); await p.waitForTimeout(200);
  ok(await p.locator('.page-title .dropdown.is-open').count() === 1, 'stays open on the way down');
  await Promise.all([p.waitForNavigation(), p.click('.page-title .dropdown a:has-text("Kornhaus")')]);
  await p.waitForLoadState('networkidle'); await p.waitForTimeout(800);
  ok(p.url().endsWith('/administration/objekte/'), 'reloaded on the list: ' + p.url());
  ok((await p.locator('.page-title .dropdown-button').innerText()).trim().startsWith('KORNHAUS'), 'title Kornhaus');
  const text = await p.evaluate(() => document.body.innerText);
  ok(text.includes('H1_101') && !text.includes('Eglistrasse 29'), 'list shows KORO apartments only');
  ok(text.includes('LOGGIA'), 'Loggia column');
  await p.screenshot({ path: `${out}/koro-objekte.png`, fullPage: false });
  for (const [n, u] of [['angebote', '/administration/angebote'], ['mieter', '/administration/mieter']]) {
    await p.goto(admin + u, { waitUntil: 'networkidle' }); await p.waitForTimeout(500);
    await p.screenshot({ path: `${out}/koro-${n}.png`, fullPage: false });
    fs.writeFileSync(`${out}/koro-${n}.txt`, await p.evaluate(() => document.body.innerText));
  }
  // Back to Eglistrasse
  await p.hover('.page-title .dropdown-button'); await p.waitForTimeout(300);
  await Promise.all([p.waitForNavigation(), p.click('.page-title .dropdown a:has-text("Eglistrasse")')]);
  await p.waitForLoadState('networkidle');
  ok((await p.locator('.page-title .dropdown-button').innerText()).trim() === 'EGLISTRASSE', 'back to Eglistrasse');
  fs.writeFileSync(`${out}/_checks.txt`, checks.join('\n') + '\n--\n' + log.join('\n'));
  console.log(checks.join('\n')); console.log(log.join('\n') || 'no errors');
  await b.close();
})();
