// node isometry.js — hover highlight on both lists, and a second estate (koro) with two SVGs
const { chromium } = require('playwright');
const admin = 'https://liegenschaften.aporta-stiftung.ch.test', offer = 'https://eglistrasse.aporta-stiftung.ch.test';
const col = '11111111-1111-4111-8111-111111111111';
(async () => {
  const b = await chromium.launch(); const ctx = await b.newContext({ ignoreHTTPSErrors: true, viewport: { width: 1440, height: 900 } });
  const p = await ctx.newPage(); const errs = []; p.on('pageerror', e => errs.push(e.message));
  const check = (n, v) => console.log((v ? 'ok  ' : 'FAIL') + ' ' + n);
  const visible = pg => pg.locator('.iso [data-id].is-visible').evaluateAll(els => els.map(e => e.dataset.id));
  await p.goto(admin + '/login'); await p.fill('input[name=email]', 'qa@example.invalid'); await p.fill('input[name=password]', 'qa-password-123');
  await Promise.all([p.waitForNavigation(), p.click('button[type=submit]')]);
  for (const [name, url] of [['admin list', admin + '/administration/objekte'], ['offer list', offer + `/angebot/${col}`]]) {
    await p.goto(url, { waitUntil: 'networkidle' });
    const rows = p.locator('.list-row'); const n0 = (await rows.nth(0).innerText()), n1 = await rows.nth(1).innerText();
    check(`${name}: nothing highlighted at first`, (await visible(p)).length === 0);
    await rows.nth(0).hover(); const a = await visible(p);
    await rows.nth(1).hover(); const c = await visible(p);
    check(`${name}: hover row 1 -> one (${a})`, a.length === 1);
    check(`${name}: hover row 2 -> one, another (${c})`, c.length === 1 && c[0] !== a[0]);
    await p.mouse.move(5, 5); await p.waitForTimeout(100);
    check(`${name}: leave -> none`, (await visible(p)).length === 0);
    check(`${name}: one svg on the page`, await p.locator('.iso').count() === 1);
  }
  // A second estate: two buildings, two SVGs
  await p.route(admin + '/administration/objekte', async r => { const res = await r.fetch(); r.fulfill({ response: res, body: (await res.text()).replace('data-estate="eglistrasse"', 'data-estate="kornhaus-roetelstrasse"') }); });
  await p.goto(admin + '/administration/objekte', { waitUntil: 'networkidle' });
  check('koro: two svgs (ko, ro)', JSON.stringify(await p.locator('.iso').evaluateAll(e => e.map(x => x.dataset.building))) === '["ko","ro"]');
  check('no page errors ' + errs.join('; '), errs.length === 0);
  await b.close();
})();
