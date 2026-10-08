// node e2e.js — the whole flow with real mail: admin sends an offer, the queue
// command sends it (MailHog), the link from the mail is opened and answered,
// reply + confirmation mails, assign, finalize, both exports.
// Destructive: run between a DB dump and its restore, with fixtures.php up.
const { chromium } = require('playwright');
const { execSync } = require('child_process');
const fs = require('fs');
const root = '/Users/marceli.to/Jamon.digital/Webroot/liegenschaften.aporta-stiftung.ch';
const admin = 'https://liegenschaften.aporta-stiftung.ch.test', offerHost = 'https://eglistrasse.aporta-stiftung.ch.test';
const cand = 'qa-e2e@example.invalid';
const sql = q => execSync(`mysql -h127.0.0.1 -uroot -N liegenschaften_aporta -e "${q}"`).toString().trim();
let failures = 0;
const check = (label, ok, extra = '') => { if (!ok) failures++; console.log(ok ? 'ok  ' : 'FAIL', label, extra); };
const settle = p => p.waitForLoadState('networkidle').then(() => p.waitForTimeout(300));

// The cron: schedule:run until the queue is empty, as the server's cron would every minute
const runQueue = () => { for (let i = 0; i < 10 && sql('select count(*) from mail_queue where processed=0') !== '0'; i++) execSync('php artisan schedule:run', { cwd: root, stdio: 'ignore' }); };

// MailHog
const decodeWords = s => s.replace(/=\?utf-8\?([QB])\?(.*?)\?=/gi, (_, k, t) => k.toUpperCase() === 'B' ? Buffer.from(t, 'base64').toString() : Buffer.from(t.replace(/_/g, ' ').replace(/=([0-9A-F]{2})/gi, (_, h) => String.fromCharCode(parseInt(h, 16))), 'latin1').toString()).replace(/\?=\s+=\?/g, '');
const h = (part, name) => (part.Headers[name] || part.Headers[name.toLowerCase()] || [''])[0];
const body = part => { const enc = h(part, 'Content-Transfer-Encoding').toLowerCase();
  if (enc === 'base64') return Buffer.from(part.Body, 'base64');
  if (enc === 'quoted-printable') return Buffer.from(part.Body.replace(/=\r?\n/g, '').replace(/=([0-9A-F]{2})/gi, (_, x) => String.fromCharCode(parseInt(x, 16))), 'latin1');
  return Buffer.from(part.Body); };
const parts = m => { const out = []; const walk = p => { if (p.MIME && p.MIME.Parts) p.MIME.Parts.forEach(walk); else out.push(p); }; walk(m.Content ? { ...m.Content, MIME: m.MIME } : m); return out; };
async function mails(since) {
  const r = await (await fetch('http://localhost:8025/api/v2/messages?limit=50')).json();
  return r.items.filter(m => new Date(m.Created) >= since).map(m => {
    const ps = parts(m);
    const html = ps.find(p => h(p, 'Content-Type').startsWith('text/html'));
    return { subject: decodeWords(h(m.Content, 'Subject')), to: h(m.Content, 'To'), replyTo: h(m.Content, 'Reply-To'),
      html: html ? body(html).toString('utf8') : '',
      attachments: ps.filter(p => /attachment/i.test(h(p, 'Content-Disposition'))).map(p => ({ type: h(p, 'Content-Type'), size: body(p).length })) };
  });
}
const xlsx = async (ctx, url, file) => {
  const r = await ctx.request.get(url); fs.writeFileSync(file, await r.body());
  return { status: r.status(), type: r.headers()['content-type'], disposition: r.headers()['content-disposition'], ...JSON.parse(execSync(`php xlsx.php ${file}`).toString()) };
};

(async () => {
  const b = await chromium.launch();
  const ctx = await b.newContext({ ignoreHTTPSErrors: true, viewport: { width: 1440, height: 900 } });
  const p = await ctx.newPage();
  const errs = []; p.on('pageerror', e => errs.push(e.message));
  p.on('response', r => r.status() >= 400 && errs.push(`${r.status()} ${r.url()}`));

  // Login
  await p.goto(admin + '/login'); await p.fill('input[name=email]', 'qa@example.invalid'); await p.fill('input[name=password]', 'qa-password-123');
  await Promise.all([p.waitForNavigation(), p.click('button[type=submit]')]); await settle(p);
  check('login → apartment list', p.url().endsWith('/administration/objekte'), p.url());

  // Every admin page
  const apt0 = sql('select uuid from apartments order by id limit 1');
  for (const path of ['objekte', `objekt/${apt0}/anzeigen`, `objekt/${apt0}/bearbeiten`, 'angebote', 'kollektion', 'mieter', 'benutzer', 'benutzer/profil']) {
    await p.goto(`${admin}/administration/${path}`); await settle(p);
    const text = (await p.innerText('body')).trim();
    check(`page ${path}`, text.length > 50 && !errs.length, errs.join('; '));
  }

  // Send an offer with two free apartments
  await p.goto(admin + '/administration/objekte'); await settle(p);
  const free = p.locator('.list-row:not(.has-collections)');
  const numbers = [];
  for (let i = 0; i < 2; i++) { const r = free.nth(i); numbers.push((await r.locator('a').nth(3).innerText()).trim()); await r.locator('.list-item-action a').click(); }
  await p.click('a.icon-collection'); await settle(p);
  const r0 = p.locator('.collection__form .list-row').nth(0);
  for (const [n, v] of [[0, 'Frau'], [1, 'Qa'], [2, 'Endtoend'], [3, cand]]) { await r0.locator('input').nth(n).fill(v); await r0.locator('input').nth(n).blur(); }
  await p.fill('textarea', 'E2E Bemerkung');
  await p.click('.page-menu__collection >> text=Senden'); await p.click('.dialog .actions a:text-is("Senden")'); await settle(p);
  check('offer sent (dialog)', (await p.locator('.dialog').innerText()).includes('versendet'));
  await p.click('.dialog .actions a:text-is("Schliessen")');
  const colUuid = sql(`select uuid from collections where email='${cand}'`);
  check('offer stored with 2 items, estate 1', sql(`select concat(count(*), '/', max(c.estate_id)) from collection_items ci join collections c on c.id=ci.collection_id where c.email='${cand}'`) === '2/1');

  // The cron sends it
  const t1 = new Date(Date.now() - 1000);
  runQueue();
  check('queue processed, no error', sql(`select count(*) from mail_queue where data like '%${cand}%' and (processed=0 or error is not null)`) === '0');
  check('items have sent_at', sql(`select count(*) from collection_items ci join collections c on c.id=ci.collection_id where c.email='${cand}' and sent_at is not null`) === '2');
  const offerMail = (await mails(t1)).find(m => m.subject.startsWith('Wohnungsangebot'));
  check('offer mail in MailHog', !!offerMail, offerMail && offerMail.subject);
  const link = offerMail && (offerMail.html.match(/href="([^"]*\/angebot\/[^"]+)"/) || [])[1];
  const md5 = require('crypto').createHash('md5').update(cand).digest('hex');
  check('offer mail: link to the estate domain with uuid + hash', link === `${offerHost}/angebot/${colUuid}/${md5}`, link);
  check('offer mail: salutation and remark', offerMail && offerMail.html.includes('Endtoend') && offerMail.html.includes('E2E Bemerkung'));
  check('offer mail: one PDF per apartment', offerMail && offerMail.attachments.filter(a => a.type.includes('pdf') && a.size > 1000).length === 2, JSON.stringify(offerMail && offerMail.attachments));

  // The candidate opens the link from the mail and replies
  const o = await (await b.newContext({ ignoreHTTPSErrors: true, viewport: { width: 1440, height: 900 } })).newPage();
  o.on('pageerror', e => errs.push('offer ' + e.message));
  await o.goto(link); await settle(o);
  check('offer page: 2 apartments', await o.locator('.list-row').count() === 2);
  check('offer page: isometry', await o.locator('.iso').count() === 1);
  check('link marks both read', sql(`select count(*) from collection_items ci join collections c on c.id=ci.collection_id where c.email='${cand}' and read_at is not null`) === '2');
  await o.locator('.list-row').nth(0).locator('a').first().click(); await settle(o);
  check('detail highlights the apartment', await o.locator('.iso [data-id].is-visible').count() === 1);
  const itemNumber = await o.locator('.iso [data-id].is-visible').getAttribute('data-id');
  await o.locator('.icon-state').nth(0).click();
  await o.click('text=Antworten'); await settle(o);
  check('reply «Interesse» sent', (await o.locator('.dialog').innerText()).includes('Vielen Dank'));
  const t2 = new Date(Date.now() - 1000);
  runQueue();
  const after = await mails(t2);
  const reply = after.find(m => m.subject.startsWith('Antwort Wohnungsangebot')), conf = after.find(m => m.subject.startsWith('Wohnungsangebot'));
  check('reply mail to the office', !!reply && reply.html.includes('Endtoend') && reply.html.includes(itemNumber), reply && reply.subject);
  check('confirmation mail to the candidate', !!conf && conf.html.includes('Ich habe Interesse an dieser Wohnung'), conf && conf.subject);
  check('queue processed, no error', sql(`select count(*) from mail_queue where processed=0 or (error is not null and data like '%${cand}%')`) === '0');

  // Admin: assign, finalize
  const aptId = sql(`select id from apartments where number='${itemNumber}'`), aptUuid = sql(`select uuid from apartments where id=${aptId}`);
  await p.goto(`${admin}/administration/objekt/${aptUuid}/anzeigen`); await settle(p);
  check('apartment shows the reply', (await p.locator('.apartment').innerText()).includes('Endtoend'));
  await p.click('text=Prov. Übernehmen'); await settle(p);
  check('assign reserves', sql(`select state_id from apartments where id=${aptId}`) === '2');
  await p.click('text=Übernehmen >> nth=0'); await p.click('.dialog .actions a:text-is("Ja")'); await settle(p);
  check('finalize rents, tenant stored', sql(`select concat(a.state_id, '/', t.email) from apartments a join tenants t on t.id=a.tenant_id where a.id=${aptId}`) === `3/${cand}`);

  // Exports
  const ea = await xlsx(ctx, admin + '/export/objekte', 'e2e-objekte.xlsx');
  check('apartment export: xlsx, filename', ea.status === 200 && ea.type.includes('spreadsheet') && ea.disposition.includes('liegenschaft-eglistrasse'), ea.disposition);
  const aptRow = ea.rows.find(r => r.includes(itemNumber));
  check('apartment export: bold header, 134 rows, the apartment', ea.bold && ea.rows.length === 135 && !!aptRow, JSON.stringify(aptRow));
  const et = await xlsx(ctx, admin + '/export/mieter', 'e2e-mieter.xlsx');
  check('tenant export: has the new tenant', et.status === 200 && et.rows.some(r => r.includes(cand)), et.disposition);

  // Logout
  await p.goto(admin + '/administration/benutzer'); await settle(p);
  await Promise.all([p.waitForNavigation(), p.click('text=Abmelden')]);
  check('logout', p.url().endsWith('/login'));
  check('no page errors / 4xx', !errs.length, errs.join('; '));
  console.log(failures ? `${failures} FAILED` : 'all ok');
  await b.close();
})();
