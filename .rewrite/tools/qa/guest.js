// node guest.js — guest pages with the new CSS vs the old Mix CSS (swapped in)
const { chromium } = require('playwright');
const fs = require('fs');
const admin = 'https://liegenschaften.aporta-stiftung.ch.test';
const pages = [['login', '/login'], ['forgot', '/password/reset'], ['reset', '/password/reset/abc?email=x@example.invalid'], ['404', '/gibt-es-nicht']];
(async () => {
  const b = await chromium.launch();
  for (const mode of ['old', 'new']) {
    const ctx = await b.newContext({ ignoreHTTPSErrors: true, viewport: { width: 1440, height: 900 } });
    const p = await ctx.newPage();
    const errs = []; p.on('pageerror', e => errs.push(e.message)); p.on('console', m => m.type() === 'error' && errs.push(m.text()));
    if (mode === 'old') await p.route('**/build/assets/app-*.css', r => r.fulfill({ path: 'old-app.css', contentType: 'text/css' }));
    for (const [n, u] of pages) {
      await p.goto(admin + u, { waitUntil: 'networkidle' }); await p.waitForTimeout(500);
      await p.screenshot({ path: `guest-${n}-${mode}.png`, fullPage: true });
    }
    // validation.js: blur an empty required field
    await p.goto(admin + '/login', { waitUntil: 'networkidle' });
    await p.focus('input[name=email]'); await p.fill('input[name=email]', 'kein-mail'); await p.focus('input[name=password]');
    console.log(mode, 'email invalid class:', await p.getAttribute('input[name=email]', 'class'), errs);
    await ctx.close();
  }
  await b.close();
})();
