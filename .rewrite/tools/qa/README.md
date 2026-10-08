# Frontend QA (Playwright)

Run from a scratch dir (e.g. `/tmp/aporta/qa`): copy these files there,
`npm init -y && npm i playwright && npx playwright install chromium`.
Against the Herd hosts (`*.test`, `ignoreHTTPSErrors`), local DB.

- `php fixtures.php up|down` — temporary admin `qa@example.invalid` and
  editor `qa-editor@example.invalid` (password `qa-password-123`) plus a
  valid 3-item offer (`11111111-…`). `down` also removes everything the
  scripts create (`qa-*@example.invalid`).
- `shots.js <outdir>` — screenshots + visible text of 10 admin views and
  the offer list/detail (1440 + 390 px). Compare two runs with
  `compare -metric AE a.png b.png null:` and `diff` on the `.txt`.
  The baseline uuids (apartment, offer) are hard-coded at the top.
- `guest.js` — login, password pages, 404 with the new CSS vs the old Mix
  CSS (`old-app.css` = `git show 9aa54b6:public/assets/css/app.css`).
- `styles.js` — computed style of every element on 16 pages/states, new
  CSS vs `old-app.css`.
- `interact.js` — 65 checks of every admin and offer action. Destructive:
  `mysqldump -h127.0.0.1 -uroot liegenschaften_aporta > db-before.sql`
  first, `mysql … < db-before.sql` after, then `fixtures.php down`.
- `e2e.js` — the whole flow with real mail (MailHog) and `schedule:run`: send
  an offer, open the link from the mail, reply, assign, finalize, both exports
  (read with `xlsx.php`). Destructive, like `interact.js`.
- `dev.js` — pages through the Vite dev server (`npx vite` running).
- `isometry.js` — hover highlight on the admin and offer lists; swaps
  `data-estate` to `koro` to check an estate with two SVGs.
