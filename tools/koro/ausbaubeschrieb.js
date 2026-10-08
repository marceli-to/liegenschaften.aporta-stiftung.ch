// node pdf.js in.html out.pdf — the Ausbaubeschrieb as A4 PDF
const { chromium } = require('playwright');
const fs = require('fs');
let html = fs.readFileSync(process.argv[2], 'utf8')
  .replace(/<style[\s\S]*?<\/style>/, `<style>
    @page { size: A4; margin: 15mm 20mm; }
    body { font: 9pt/1.3 Helvetica, Arial, sans-serif; color: #000; }
    p { margin: 0 0 4pt; }
    p.p1 { font-size: 14pt; margin: 0 0 12pt; break-before: page; }
    p.p1:first-of-type { break-before: auto; }
    p.p2:has(> b:only-child) { margin-top: 7pt; break-after: avoid; }
  </style>`)
  .replace(/<p class="p4">.*?<\/p>\n?/g, '');
(async () => {
  const b = await chromium.launch(); const p = await b.newPage();
  await p.setContent(html); await p.pdf({ path: process.argv[3], format: 'A4', printBackground: true });
  await b.close();
})();
