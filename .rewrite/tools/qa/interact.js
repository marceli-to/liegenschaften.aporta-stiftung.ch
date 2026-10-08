// node interact.js — every admin and offer action on the current build.
// Destructive: run between a DB dump and its restore.
const { chromium } = require('playwright');
const { execSync } = require('child_process');
const admin = 'https://liegenschaften.aporta-stiftung.ch.test', offer = 'https://eglistrasse.aporta-stiftung.ch.test';
const col = '11111111-1111-4111-8111-111111111111';
const sql = q => execSync(`mysql -h127.0.0.1 -uroot -N liegenschaften_aporta -e "${q}"`).toString().trim();
let failures = 0;
const check = (label, ok, extra = '') => { if (!ok) failures++; console.log(ok ? 'ok  ' : 'FAIL', label, extra); };

async function login(ctx, email) {
  const p = await ctx.newPage();
  await p.goto(admin + '/login');
  await p.fill('input[name=email]', email); await p.fill('input[name=password]', 'qa-password-123');
  await Promise.all([p.waitForNavigation(), p.click('button[type=submit]')]);
  return p;
}
const settle = p => p.waitForLoadState('networkidle').then(() => p.waitForTimeout(300));

(async () => {
  const b = await chromium.launch();

  // Offer SPA (first: the admin steps below finalize the fixture offer)
  const o = await (await b.newContext({ ignoreHTTPSErrors: true, viewport: { width: 1440, height: 900 } })).newPage();
  o.on('pageerror', x => console.log('FAIL offer pageerror', x.message));
  await o.goto(offer + `/angebot/${col}/` + require('crypto').createHash('md5').update('qa-kandidatin@example.invalid').digest('hex')); await settle(o);
  check('offer list: estate from data-estate', (await o.locator('header h1').textContent()).includes('Wohnüberbauung') || (await o.locator('header h1').textContent()).includes('8004 Zürich'), await o.locator('header h1').textContent());
  check('offer list rows', await o.locator('.list-row').count() === 3, await o.locator('.list-row').count());
  check('hash marks read', sql(`select count(*) from collection_items ci join collections c on c.id=ci.collection_id where c.uuid='${col}' and read_at is not null`) === '3');
  await o.locator('.list-row').nth(1).hover();
  check('hover highlights the isometry', await o.locator('.iso [data-id].is-visible').count() === 1);
  await o.locator('.list-row').nth(0).locator('a').first().click(); await settle(o);
  check('detail 01 / 03', (await o.locator('.page-menu-collection__browse span').innerText()) === '01 / 03');
  check('detail isometry highlight', await o.locator('.iso [data-id].is-visible').count() === 1);
  await o.locator('.page-menu-collection__browse a').nth(1).click(); await settle(o);
  check('next 02 / 03', (await o.locator('.page-menu-collection__browse span').innerText()) === '02 / 03');
  await o.locator('.icon-state').nth(1).click();
  await o.click('text=Antworten');
  check('«kein Interesse» needs a reason', (await o.locator('textarea').getAttribute('class')).includes('is-invalid'));
  await o.fill('textarea', 'Zu klein'); await o.click('text=Antworten'); await settle(o);
  check('reply sent', (await o.locator('.dialog').innerText()).includes('Vielen Dank') && (await o.innerText('body')).includes('Ihre Rückmeldung'));
  const m = await (await b.newContext({ ignoreHTTPSErrors: true, viewport: { width: 390, height: 844 }, isMobile: true })).newPage();
  await m.goto(offer + `/angebot/${col}`); await settle(m);
  await m.locator('.list-xs a').first().click(); await settle(m);
  check('mobile: list → detail', m.url().includes('/detail/'));


  const ctx = await b.newContext({ ignoreHTTPSErrors: true, viewport: { width: 1440, height: 900 } });
  const p = await login(ctx, 'qa@example.invalid');
  const errs = []; p.on('pageerror', e => errs.push(e.message)); p.on('console', m => m.type() === 'error' && errs.push(m.text()));
  const rows = () => p.locator('.list-row').count();
  const firstCell = () => p.locator('.list-row').first().locator('a').nth(1).innerText();
  await settle(p);
  check('login lands on the apartment list', p.url().endsWith('/administration/objekte'), p.url());

  // Apartment list, sort
  check('134 apartments', await rows() === 134, await rows());
  await p.click('.list-header >> text=Nummer >> xpath=.. >> a'); await settle(p);
  const asc = await p.locator('.list-row').first().locator('a').nth(3).innerText();
  await p.click('.list-header >> text=Nummer >> xpath=.. >> a'); await settle(p);
  const desc = await p.locator('.list-row').first().locator('a').nth(3).innerText();
  check('sort by number toggles', asc !== desc, `${asc} / ${desc}`);

  // Dropdown
  await p.hover('.dropdown-button');
  check('dropdown opens on hover', await p.locator('.dropdown.is-open').count() === 1);
  await p.mouse.move(700, 600);

  // Filter
  await p.click('a.icon-filter'); await settle(p);
  check('filter panel opens', await p.locator('nav.selector').count() === 1);
  await p.click('nav.selector >> text=Terrasse'); await settle(p);
  check('exterior filter «Terrasse» (now sent to the API)', await rows() === 21, await rows());
  check('«Anzeigen (21)»', (await p.locator('nav.selector .is-filter').innerText()).includes('(21)'));
  await p.click('nav.selector >> text=Terrasse'); await settle(p);
  await p.locator('nav.selector .span-2').first().locator('a').first().click(); await settle(p);
  const byBuilding = await rows();
  check('building filter', byBuilding > 0 && byBuilding < 134, byBuilding);
  await p.click('nav.selector .is-filter'); await settle(p);
  check('«Anzeigen» closes the panel', await p.locator('nav.selector').count() === 0);
  check('header shows filter active', await p.locator('a.icon-filter').count() === 1);

  // Detail with prev/next from the filter
  await p.locator('.list-row').first().locator('a').nth(1).click(); await settle(p);
  const pag1 = await p.locator('.site-menu__pagination span').innerText();
  check('detail shows filter pagination', pag1 === `01 / ${String(byBuilding).padStart(2, '0')}`, pag1);
  const url1 = p.url();
  await p.locator('.site-menu__pagination a').nth(1).click(); await settle(p);
  check('next apartment', p.url() !== url1 && (await p.locator('.site-menu__pagination span').innerText()).startsWith('02'), await p.locator('.site-menu__pagination span').innerText());
  check('edit link goes to /bearbeiten', (await p.locator('.page-menu a:has-text("Bearbeiten")').getAttribute('href')).endsWith('/bearbeiten'));
  await p.click('.page-menu >> text=Zurück'); await settle(p);
  check('back to the list keeps the filter', await rows() === byBuilding, await rows());
  await p.click('a.icon-filter'); await p.click('nav.selector >> text=Zurücksetzen'); await settle(p);
  check('reset filter', await rows() === 134, await rows());

  // Pick two apartments, then the offer form
  await p.locator('.list-row').nth(0).locator('.list-item-action a').click();
  await p.locator('.list-row').nth(1).locator('.list-item-action a').click();
  check('header counter 2', (await p.locator('a.icon-collection').innerText()).trim() === '2');
  await p.click('a.icon-collection'); await settle(p);
  const picked = () => p.locator('.list').first().locator('.list-row').count();
  check('create shows the 2 picked', await picked() === 2, await picked());
  check('header has-collection', await p.locator('header.has-collection').count() === 1);
  await p.locator('.list-row').first().locator('.list-item-action a').click(); await settle(p);
  check('unpicking refreshes the list (was a TypeError)', await picked() === 1, await picked());
  await p.click('a.icon-collection');
  await p.goto(admin + '/administration/objekte'); await settle(p);
  await p.locator('.list-row').nth(2).locator('.list-item-action a').click();
  await p.click('a.icon-collection'); await settle(p);
  const fill = async (i, email) => {
    const r = p.locator('.collection__form .list-row').nth(i);
    for (const [n, v] of [[0, 'Frau'], [1, 'Qa'], [2, 'Kandidatin ' + i], [3, email]]) { await r.locator('input').nth(n).fill(v); await r.locator('input').nth(n).blur(); }
  };
  await fill(0, 'qa-k1@example.invalid');
  for (const [i, e] of [[1, 'qa-k2@example.invalid'], [2, 'qa-k3@example.invalid']]) { await p.click('.page-menu__collection .flex a'); await fill(i, e); }
  await p.fill('textarea', 'QA Bemerkung');
  await p.click('.page-menu__collection >> text=Senden'); await p.waitForTimeout(200);
  const confirm = await p.locator('.dialog').innerText();
  check('confirm lists 3 recipients', confirm.includes('qa-k1@example.invalid, qa-k2@example.invalid und qa-k3@example.invalid'), confirm.replace(/\s+/g, ' '));
  check('still 3 candidate rows (pop bug)', await p.locator('.collection__form .list-row').count() === 4, await p.locator('.collection__form .list-row').count());
  await p.click('.dialog .actions a:text-is("Senden")'); await settle(p);
  check('success dialog', (await p.locator('.dialog').innerText()).includes('versendet'));
  check('3 offers stored', sql("select count(*) from collections where email like 'qa-k_@example.invalid'") === '3');
  check('3 offer mails queued', sql("select count(*) from mail_queue where type='offer' and processed=0 and data like '%qa-k_@example.invalid%'") === '3');
  check('picked list emptied', sql("select 1") && (await p.locator('a.icon-collection').count()) === 0);
  await p.click('.dialog .actions a:text-is("Schliessen")'); await settle(p);

  // Edit an offer
  const k1 = sql("select uuid from collections where email='qa-k1@example.invalid'");
  await p.goto(admin + `/administration/kollektion/bearbeiten/${k1}`); await settle(p);
  check('edit prefilled', await p.locator('.collection__form .list-row').nth(0).locator('input').nth(3).inputValue() === 'qa-k1@example.invalid');
  check('edit shows remarks', await p.inputValue('textarea') === 'QA Bemerkung');
  await p.locator('.collection__form .list-row').nth(0).locator('input').nth(1).fill('Qa Neu');
  await p.click('.page-menu__collection >> text=Senden'); await p.click('.dialog .actions a:text-is("Senden")'); await settle(p);
  check('edit replaces the offer', sql("select count(*) from collections where email='qa-k1@example.invalid' and deleted_at is null and firstname='Qa Neu'") === '1');

  // Offers list
  await p.goto(admin + '/administration/angebote'); await settle(p);
  const offers = await rows();
  check('offers list', offers >= 3, offers);
  await p.locator('.list-row').first().locator('.icon-trash').click();
  await p.click('.dialog .actions a:text-is("Abbrechen")');
  check('cancel keeps it', await rows() === offers);
  await p.locator('.list-row').first().locator('.icon-trash').click(); await p.click('.dialog .actions a:text-is("Ja")'); await settle(p);
  check('delete removes it', await rows() === offers - 1, await rows());

  // Apartment edit: date mask, rents, tenant
  const apt = sql("select uuid from apartments where id=2");
  await p.goto(admin + `/administration/objekt/${apt}/bearbeiten`); await settle(p);
  const date = p.locator('input[name=date]');
  await date.fill(''); await date.pressSequentially('01x12/2030');
  check('date mask', await date.inputValue() === '01.12.2030', await date.inputValue());
  const rentInputs = p.locator('.apartment input[type=text]');
  await rentInputs.nth(0).fill(''); await rentInputs.nth(0).blur();
  check('empty rent marked invalid', (await rentInputs.nth(0).getAttribute('class')).includes('is-invalid'));
  await rentInputs.nth(0).fill('1234.00'); await rentInputs.nth(0).blur();
  await p.locator('input[type=text]').nth(3).fill('Muster'); await p.locator('input[type=text]').nth(4).fill('Erika');
  await p.click('button[type=submit]'); await settle(p);
  check('save goes to the detail', p.url().endsWith('/anzeigen'), p.url());
  const detail = await p.locator('.apartment').innerText();
  check('detail shows rent, date, tenant', detail.includes('1234.00') && detail.includes('01.12.2030') && detail.includes('Muster'), '');

  // Reply, assign, finalize, reset (fixture offer, apartment id 2)
  const item = sql(`select ci.uuid from collection_items ci join collections c on c.id=ci.collection_id where c.uuid='${col}' and ci.apartment_id=2`);
  const op = await ctx.newPage();
  await op.goto(offer + `/angebot/${col}/detail/${item}`); await settle(op);
  await op.locator('.icon-state').nth(0).click(); await op.locator('.icon-state').nth(3).click();
  await op.click('text=Antworten'); await settle(op);
  check('offer: reply «Interesse» + parking', (await op.locator('.dialog').innerText()).includes('Vielen Dank'));
  await op.close();
  await p.goto(admin + `/administration/objekt/${apt}/anzeigen`); await settle(p);
  await p.click('text=Prov. Übernehmen'); await settle(p);
  check('assign reserves', sql('select state_id from apartments where id=2') === '2' && (await p.locator('.apartment').innerText()).includes('Kandidatin'));
  await p.click('text=Übernehmen >> nth=0'); await p.click('.dialog .actions a:text-is("Ja")'); await settle(p);
  check('finalize rents', sql('select state_id from apartments where id=2') === '3');
  await p.click('.page-menu >> text=Zurücksetzen'); await p.click('.dialog .actions a:text-is("Löschen")'); await settle(p);
  check('reset frees', sql('select concat(state_id, ifnull(tenant_id, \'-\')) from apartments where id=2') === '1-');

  // Tenants
  await p.goto(admin + '/administration/mieter'); await settle(p);
  const tenants = await rows();
  await p.click('.site-menu a[href=""]');
  check('search panel', await p.locator('nav.selector input.search').count() === 1);
  await p.fill('input.search', 'strasse'); await p.keyboard.press('Enter'); await settle(p);
  check('search by Enter', await rows() > 0 && await p.locator('nav.selector').count() === 0, await rows());
  await p.click('.site-menu a[href=""]'); await p.click('nav.selector >> text=Zurücksetzen'); await settle(p);
  check('reset search', await rows() === tenants, `${await rows()} / ${tenants}`);
  check('tenant export link', (await p.locator('a.link-export').getAttribute('href')).startsWith('/export/mieter?v='));

  // Users
  await p.goto(admin + '/administration/benutzer'); await settle(p);
  const users = await rows();
  const userForm = async (vals) => { const r = p.locator('.list-row.no-hover').first(); for (let i = 0; i < vals.length; i++) { await r.locator('input').nth(i).fill(vals[i]); await r.locator('input').nth(i).blur(); } };
  await p.click('.page-menu__users .flex a');
  await userForm(['Qa', 'Neu', 'qa-new@example.invalid', 'qa-password-123']);
  await p.click('.page-menu__users >> text=Speichern'); await settle(p);
  check('create user (editor)', sql("select role from users where email='qa-new@example.invalid'") === 'editor');
  await p.click('.page-menu__users .flex a');
  await userForm(['Qa', 'Doppelt', 'qa-new@example.invalid', 'qa-password-123']);
  await p.click('.page-menu__users >> text=Speichern'); await settle(p);
  const dlg1 = await p.locator('.dialog').innerText();
  await p.click('.dialog .actions a:text-is("Schliessen")');
  await p.click('.page-menu__users >> text=Speichern'); await settle(p);
  const dlg2 = await p.locator('.dialog').innerText();
  check('validation dialog, no pile-up', dlg1.includes('E-Mail bereits vergeben') && dlg2.split('E-Mail bereits vergeben').length === 2, dlg2.replace(/\s+/g, ' '));
  await p.click('.dialog .actions a:text-is("Schliessen")');
  await userForm(['Qa', 'Zwei', 'qa-new2@example.invalid', 'qa-password-123']);
  await p.click('.page-menu__users >> text=Speichern'); await settle(p);
  check('second user after a reset gets a role (was a 500)', sql("select role from users where email='qa-new2@example.invalid'") === 'editor');
  await p.locator('.list-row', { hasText: 'qa-new@example.invalid' }).locator('a').nth(1).click();
  await userForm(['Qa', 'Umbenannt']);
  await p.click('.page-menu__users >> text=Speichern'); await settle(p);
  check('edit user', sql("select name from users where email='qa-new@example.invalid'") === 'Umbenannt');
  await p.locator('.list-row', { hasText: 'qa-new2@example.invalid' }).locator('.icon-trash').click();
  check('delete confirm names the user', (await p.locator('.dialog').innerText()).includes('«Qa Zwei»'));
  await p.click('.dialog .actions a:text-is("löschen")'); await settle(p);
  check('delete user', sql("select count(*) from users where email='qa-new2@example.invalid'") === '0' && await rows() === users + 1);

  // Errors: notification + not-found route
  await p.goto(admin + '/administration/objekt/gibt-es-nicht/anzeigen'); await p.waitForTimeout(800);
  check('404 notification', (await p.locator('.notification.error').innerText()).includes('404'));
  check('404 goes to not-found', p.url().endsWith('/not-found'), p.url());

  // Logout by POST
  await p.goto(admin + '/administration/benutzer'); await settle(p);
  await Promise.all([p.waitForNavigation(), p.click('text=Abmelden')]); await settle(p);
  check('logout', p.url().endsWith('/login'), p.url());
  check('logged out', (await p.request.get(admin + '/api/user', { headers: { Accept: 'application/json' } })).status() === 401);

  // Editor: profile
  const ectx = await b.newContext({ ignoreHTTPSErrors: true, viewport: { width: 1440, height: 900 } });
  const e = await login(ectx, 'qa-editor@example.invalid'); await settle(e);
  check('editor: user icon goes to the profile', (await e.locator('header a[href$="/benutzer/profil"]').count()) === 1);
  await e.goto(admin + '/administration/benutzer/profil'); await settle(e);
  await e.locator('input').nth(0).fill('Eva');
  await e.click('text=Speichern'); await settle(e);
  check('editor saves the profile (was a 500)', (await e.locator('.dialog').innerText()).includes('aktualisiert') && sql("select concat(firstname, role) from users where email='qa-editor@example.invalid'") === 'Evaeditor');

  console.log('errors', errs);
  console.log(failures ? `${failures} FAILED` : 'all ok');
  await b.close();
})();
