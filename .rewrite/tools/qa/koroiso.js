// node koroiso.js <outdir> — KORO's isometry in the admin list: layout, hover highlight
const { chromium } = require('playwright');
const fs = require('fs');
const out = process.argv[2]; fs.mkdirSync(out, { recursive: true });
const admin = 'https://liegenschaften.aporta-stiftung.ch.test';
(async () => {
  const b = await chromium.launch();
  const p = await (await b.newContext({ ignoreHTTPSErrors: true, viewport: { width: 1440, height: 900 } })).newPage();
  const log = []; p.on('pageerror', e => log.push(e.message)); p.on('console', m => m.type() === 'error' && log.push(m.text()));
  await p.goto(admin + '/login'); await p.fill('input[name=email]', 'qa@example.invalid'); await p.fill('input[name=password]', 'qa-password-123');
  await Promise.all([p.waitForNavigation(), p.click('button[type=submit], input[type=submit]')]);
  await p.goto(admin + '/administration/objekte', { waitUntil: 'networkidle' });
  if (!(await p.locator('.page-title .dropdown-button').innerText()).includes('KORNHAUS')) {
    await p.hover('.page-title .dropdown-button');
    await Promise.all([p.waitForNavigation(), p.click('.page-title .dropdown a.is-estate')]);
  }
  await p.waitForLoadState('networkidle'); await p.waitForTimeout(800);
  await p.screenshot({ path: `${out}/list.png` });
  for (const n of process.argv.slice(3)) {
    await p.locator('.list-row', { hasText: n }).first().hover(); await p.waitForTimeout(300);
    const vis = await p.evaluate(() => [...document.querySelectorAll('[data-id].is-visible')].map(e => e.dataset.id));
    console.log(n, '->', vis.join(','));
    await p.screenshot({ path: `${out}/hover-${n}.png`, clip: { x: 0, y: 0, width: 1440, height: 420 } });
  }
  console.log(log.join('\n') || 'no errors');
  await b.close();
})();
