// node tabs.js — picked on Eglistrasse, KORO chosen in a second tab, offer sent: toast, no offer
const { chromium } = require('playwright');
const admin = 'https://liegenschaften.aporta-stiftung.ch.test';
(async () => {
  const b = await chromium.launch();
  const ctx = await b.newContext({ ignoreHTTPSErrors: true, viewport: { width: 1440, height: 900 } });
  const p = await ctx.newPage();
  const settle = async () => { await p.waitForLoadState('networkidle'); await p.waitForTimeout(400); };
  await p.goto(admin + '/login'); await p.fill('input[name=email]', 'qa@example.invalid'); await p.fill('input[name=password]', 'qa-password-123');
  await Promise.all([p.waitForNavigation(), p.click('button[type=submit], input[type=submit]')]);
  await p.goto(admin + '/administration/objekte'); await settle();
  await p.locator('.list-row').nth(0).locator('.list-item-action a').click();
  await p.click('a.icon-collection'); await settle();
  const r = p.locator('.collection__form .list-row').nth(0);
  for (const [n, v] of [[0, 'Frau'], [1, 'Qa'], [2, 'Tab'], [3, 'qa-tab@example.invalid']]) { await r.locator('input').nth(n).fill(v); await r.locator('input').nth(n).blur(); }
  // Second tab switches the estate
  const p2 = await ctx.newPage(); await p2.goto(admin + '/administration/objekte'); await p2.waitForLoadState('networkidle');
  await p2.hover('.page-title .dropdown-button');
  await Promise.all([p2.waitForNavigation(), p2.click('.page-title .dropdown a:has-text("Kornhaus")')]);
  await p.click('.page-menu__collection >> text=Senden'); await p.waitForTimeout(200);
  await p.click('.dialog .actions a:text-is("Senden")'); await settle();
  await p.screenshot({ path: 'koro3/tabs.png' });
  console.log('toast:', (await p.locator('.notifications').innerText()).replace(/\s+/g, ' '));
  await b.close();
})();
