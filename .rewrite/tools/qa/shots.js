// node shots.js <outdir> — screenshots + visible text of every admin/offer page
const { chromium } = require('playwright');
const fs = require('fs');
const out = process.argv[2]; fs.mkdirSync(out, { recursive: true });
const admin = 'https://liegenschaften.aporta-stiftung.ch.test', offer = 'https://eglistrasse.aporta-stiftung.ch.test';
const apt = 'c177c467-c8e3-4ed4-abd4-1ee005f453cf', col = '11111111-1111-4111-8111-111111111111', item = '22222222-2222-4222-8222-222222222221';
const pages = [
  ['objekte', admin + '/administration/objekte'],
  ['objekt-anzeigen', admin + `/administration/objekt/${apt}/anzeigen`],
  ['objekt-bearbeiten', admin + `/administration/objekt/${apt}/bearbeiten`],
  ['angebote', admin + '/administration/angebote'],
  ['angebote-item', admin + `/administration/angebote/${item}`],
  ['kollektion-neu', admin + '/administration/kollektion'],
  ['kollektion-bearbeiten', admin + `/administration/kollektion/bearbeiten/${col}`],
  ['mieter', admin + '/administration/mieter'],
  ['benutzer', admin + '/administration/benutzer'],
  ['profil', admin + '/administration/benutzer/profil'],
];
const offers = [
  ['angebot', offer + `/angebot/${col}`],
  ['angebot-detail', offer + `/angebot/${col}/detail/${item}`],
];
(async () => {
  const b = await chromium.launch();
  const ctx = await b.newContext({ ignoreHTTPSErrors: true, viewport: { width: 1440, height: 900 } });
  const p = await ctx.newPage();
  const log = []; const tag = { v: '' };
  p.on('pageerror', e => log.push(`${tag.v} pageerror ${e.message}`));
  p.on('console', m => m.type() === 'error' && log.push(`${tag.v} console ${m.text()}`));
  p.on('response', r => r.status() >= 400 && log.push(`${tag.v} ${r.status()} ${r.url()}`));
  const shot = async (name, url, page = p) => {
    tag.v = name;
    await page.goto(url, { waitUntil: 'networkidle' }); await page.waitForTimeout(800);
    await page.screenshot({ path: `${out}/${name}.png`, fullPage: true });
    fs.writeFileSync(`${out}/${name}.txt`, await page.evaluate(() => document.body.innerText));
  };
  await p.goto(admin + '/login'); await p.fill('input[name=email]', 'qa@example.invalid'); await p.fill('input[name=password]', 'qa-password-123');
  await Promise.all([p.waitForNavigation(), p.click('button[type=submit], input[type=submit]')]);
  for (const [n, u] of pages) await shot(n, u);
  for (const [n, u] of offers) await shot(n, u);
  const m = await (await b.newContext({ ignoreHTTPSErrors: true, viewport: { width: 390, height: 844 }, isMobile: true })).newPage();
  for (const [n, u] of offers) await shot(n + '-390', u, m);
  fs.writeFileSync(`${out}/_log.txt`, log.join('\n') + '\n');
  console.log(log.join('\n') || 'no errors');
  await b.close();
})();
