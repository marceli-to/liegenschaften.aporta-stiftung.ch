// node styles.js — computed style of every element, new CSS vs old Mix CSS, per page and state
const { chromium } = require('playwright');
const admin = 'https://liegenschaften.aporta-stiftung.ch.test', offer = 'https://eglistrasse.aporta-stiftung.ch.test';
const apt = 'c177c467-c8e3-4ed4-abd4-1ee005f453cf', col = '11111111-1111-4111-8111-111111111111', item = '22222222-2222-4222-8222-222222222221';
const cases = [
  ['objekte', admin + '/administration/objekte'],
  ['objekte+filter', admin + '/administration/objekte', async p => { await p.click('.icon-filter'); }],
  ['objekte+dropdown', admin + '/administration/objekte', async p => { await p.hover('.dropdown-button'); }],
  ['objekt', admin + `/administration/objekt/${apt}/anzeigen`],
  ['objekt+dialog', admin + `/administration/objekt/${apt}/anzeigen`, async p => { await p.click('text=Zurücksetzen'); }],
  ['bearbeiten', admin + `/administration/objekt/${apt}/bearbeiten`],
  ['bearbeiten+invalid', admin + `/administration/objekt/${apt}/bearbeiten`, async p => { const i = p.locator('input[type=text]').first(); await i.fill(''); await i.blur(); }],
  ['angebote', admin + '/administration/angebote'],
  ['kollektion', admin + `/administration/kollektion/bearbeiten/${col}`],
  ['mieter+suche', admin + '/administration/mieter', async p => { await p.click('.site-menu li:not(.page-title) a[href=""]'); }],
  ['benutzer+form', admin + '/administration/benutzer', async p => { await p.click('.page-menu__users .flex a'); }],
  ['profil', admin + '/administration/benutzer/profil'],
  ['angebot', offer + `/angebot/${col}`],
  ['angebot-detail', offer + `/angebot/${col}/detail/${item}`],
  ['angebot-detail+kein', offer + `/angebot/${col}/detail/${item}`, async p => { await p.locator('.icon-state').nth(1).click(); }],
  ['login', admin + '/login'],
];
async function snapshot(b, mode) {
  const ctx = await b.newContext({ ignoreHTTPSErrors: true, viewport: { width: 1440, height: 900 } });
  const p = await ctx.newPage();
  if (mode === 'old') await p.route('**/build/assets/app-*.css', r => r.fulfill({ path: 'old-app.css', contentType: 'text/css' }));
  await p.goto(admin + '/login'); await p.fill('input[name=email]', 'qa@example.invalid'); await p.fill('input[name=password]', 'qa-password-123');
  await Promise.all([p.waitForNavigation(), p.click('button[type=submit]')]);
  const res = {};
  for (const [name, url, act] of cases) {
    if (name === 'login') await ctx.clearCookies();
    await p.goto(url, { waitUntil: 'networkidle' }); await p.waitForTimeout(300);
    if (act) { await act(p); await p.waitForTimeout(400); }
    res[name] = await p.evaluate(() => [...document.querySelectorAll('body *')].map(e => {
      const s = getComputedStyle(e); const o = {}; for (const k of s) o[k] = s.getPropertyValue(k);
      const b = getComputedStyle(e, '::before'), a = getComputedStyle(e, '::after');
      o['::before'] = b.content + b.backgroundImage + b.width; o['::after'] = a.content + a.backgroundImage + a.width;
      return [e.tagName + '.' + e.className?.baseVal ?? e.className, o];
    }));
  }
  await ctx.close();
  return res;
}
(async () => {
  const b = await chromium.launch();
  const old = await snapshot(b, 'old'), now = await snapshot(b, 'new');
  for (const name in old) {
    const a = old[name], c = now[name]; let diffs = 0; const seen = {};
    if (a.length !== c.length) console.log(name, 'element count', a.length, c.length);
    for (let i = 0; i < Math.min(a.length, c.length); i++) for (const k in a[i][1]) if (a[i][1][k] !== c[i][1][k]) {
      diffs++; const key = k + ' ' + a[i][1][k] + ' → ' + c[i][1][k]; if (!seen[key]) { seen[key] = a[i][0]; }
    }
    console.log(name, 'elements', a.length, 'diffs', diffs);
    Object.entries(seen).slice(0, 30).forEach(([k, el]) => console.log('   ', el.slice(0, 60), '|', k.slice(0, 160)));
  }
  await b.close();
})();
