const { chromium } = require('playwright');
(async () => {
  const b = await chromium.launch(); const ctx = await b.newContext({ ignoreHTTPSErrors: true });
  const p = await ctx.newPage(); const errs = [];
  p.on('pageerror', e => errs.push(e.message)); p.on('console', m => m.type() === 'error' && errs.push(m.text()));
  p.on('requestfailed', r => errs.push('failed ' + r.url()));
  await p.goto('https://liegenschaften.aporta-stiftung.ch.test/login', { waitUntil: 'networkidle' });
  console.log('login css from vite:', await p.evaluate(() => [...document.querySelectorAll('script[type=module]')].map(s => s.src).join(' ')));
  await p.fill('input[name=email]', 'qa@example.invalid'); await p.fill('input[name=password]', 'qa-password-123');
  await Promise.all([p.waitForNavigation(), p.click('button[type=submit]')]); await p.waitForLoadState('networkidle');
  console.log('admin rows:', await p.locator('.list-row').count());
  await p.goto('https://eglistrasse.aporta-stiftung.ch.test/angebot/11111111-1111-4111-8111-111111111111', { waitUntil: 'networkidle' });
  console.log('offer rows:', await p.locator('.list-row').count(), 'iso:', await p.locator('.iso').count());
  console.log('errors', errs);
  await b.close();
})();
