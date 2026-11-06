// src/utils/letterComposer.js
// Templates, sanitizer, pagination and page renderer for LetterComposer.vue.
// The saved document is still one HTML string (backend unchanged), but it is made of
// marked regions:
//   data-region="header"     letterhead (locked, rendered on page 1 only)
//   data-field="subject|refs|recipient|closing|date-lunar|date-solar"  editable fields
//   data-region="body"       rich text (the only place with full formatting)
//   data-region="signature"  drafter signature table (locked; opinion boxes go here)
//   data-sys="doc-number"    filled from the documentNumber prop, never stored

export const DOC_VERSION_MARK = 'data-doc-v="2"';
export const PAGE_W = 794;   // A4 @ 96 dpi
export const PAGE_H = 1123;
export const PAD_T = 38;     // top margin (page 2+ and before the letterhead)
export const PAD_B = 56;     // bottom margin when there is no footer
export const PAD_L = 76;     // ≈ 2 cm
export const PAD_R = 57;     // ≈ 1.5 cm
export const NUM_H = 30;     // space reserved for "- ១ -"
export const FOOT_BOTTOM = 12;

const KH = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'];
export const toKhmerNum = (v) => String(v).replace(/[0-9]/g, (d) => KH[+d]);
const esc = (s) => String(s == null ? '' : s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

// ---------------------------------------------------------------------------
// Document CSS (injected once into <head>; also used by print and Word export)
// ---------------------------------------------------------------------------
export const DOC_CSS = `
.lc-doc{font-family:'Khmer OS Siemreap','Siemreap',sans-serif;font-size:12pt;line-height:1.95;color:#111;}
.lc-doc .moul,.lc-doc .lc-moul{font-family:'Khmer OS Muol Light','Moul',serif;font-weight:normal;}
.lc-doc .lc-green{color:#1a8a3a;}
.lc-sheet{position:absolute;left:0;top:0;width:${PAGE_W}px;min-height:${PAGE_H}px;box-sizing:border-box;padding:${PAD_T}px ${PAD_R}px ${PAD_B}px ${PAD_L}px;background:#fff;transform-origin:top left;outline:none;}
.lc-measure-sheet{position:absolute;left:-10000px;top:0;width:${PAGE_W}px;box-sizing:border-box;padding:${PAD_T}px ${PAD_R}px 0 ${PAD_L}px;visibility:hidden;}
.lc-measure{position:absolute;left:-10000px;top:0;visibility:hidden;}
.lc-page-wrap{position:relative;flex:none;background:#fff;box-shadow:0 2px 12px rgba(0,0,0,.35);}
.lc-page{position:absolute;left:0;top:0;width:${PAGE_W}px;height:${PAGE_H}px;background:#fff;overflow:hidden;transform-origin:top left;}
.lc-page-inner{position:absolute;left:${PAD_L}px;right:${PAD_R}px;top:${PAD_T}px;}
.lc-page-footer{position:absolute;left:34px;right:34px;bottom:${FOOT_BOTTOM}px;}
.lc-page-num{position:absolute;left:0;right:0;text-align:center;font-size:11pt;color:#222;}
.lc-blk{display:flow-root;}
/* letterhead */
.lc-header{margin:-16px -13px 6px -24px;}
.lc-lh{display:grid;grid-template-columns:32% 44% 24%;align-items:start;}
.lc-lh-l{text-align:center;}
.lc-lh-c{text-align:center;}
.lc-logo{width:118px;height:auto;display:inline-block;}
.lc-k1{font-size:13pt;line-height:1.9;}
.lc-k2{font-size:12pt;line-height:1.8;}
.lc-orn{font-size:10pt;letter-spacing:3px;line-height:1.4;}
.lc-org{font-size:11pt;line-height:1.9;}
.lc-num{font-size:10.5pt;border-bottom:1.5px solid #1a8a3a;display:inline-block;padding:0 2px;white-space:nowrap;line-height:1.8;}
.lc-recipient{padding-top:12px;font-size:12pt;line-height:2;}
.lc-doc-title{text-align:center;font-size:15pt;margin:6px 0 10px;}
.lc-letter-to{margin:0 0 12px 45%;line-height:2;}
/* subject & references */
.lc-subj{width:100%;border-collapse:collapse;margin:2px 0 10px;}
.lc-subj td{vertical-align:top;padding:0 0 2px;border:none;}
.lc-subj td.lc-lbl{width:88px;white-space:nowrap;font-family:'Khmer OS Muol Light','Moul',serif;font-size:11.5pt;}
.lc-ref::before{content:"- ";}
/* body & closing */
.lc-body p,.lc-closing p{margin:0 0 4px;text-align:justify;text-indent:72px;}
.lc-body ul,.lc-body ol{margin:0 0 2px;padding-left:100px;}
.lc-body li{text-align:justify;}
.lc-body table{border-collapse:collapse;width:100%;margin:6px 0 10px;line-height:1.6;}
.lc-body td,.lc-body th{border:1px solid #333;padding:4px 8px;vertical-align:top;text-indent:0;}
.lc-closing{margin-top:6px;}
.lc-doc .drafter-signature-table{margin-top:10px!important;margin-bottom:10px!important;}
/* editing affordances (only while editing; never printed) */
.lc-editing [data-field],.lc-editing [data-region="body"]{outline:1.5px dashed transparent;outline-offset:3px;border-radius:3px;transition:outline-color .12s,background-color .12s;}
.lc-editing [data-field]:hover,.lc-editing [data-region="body"]:hover{outline-color:#8cc3a1;background:#f6fbf7;}
.lc-editing [data-field]:focus,.lc-editing [data-region="body"]:focus{outline:1.5px solid #2457c5;background:#f5f8ff;}
.lc-editing [data-field]:empty::before{content:attr(data-ph);color:#9aa4b2;}
.lc-editing [data-region="header"]:hover,.lc-editing .drafter-signature-table td:last-child:hover{background:rgba(110,120,140,.07);border-radius:4px;cursor:default;}
@media (prefers-reduced-motion:reduce){.lc-editing [data-field],.lc-editing [data-region="body"]{transition:none;}}
@page{size:A4;margin:0;}
@media print{.lc-print .lc-page-wrap{width:210mm!important;height:297mm!important;box-shadow:none;break-after:page;}
.lc-print .lc-page-wrap:last-child{break-after:auto;}.lc-print .lc-page{transform:none!important;}}
`;

// ---------------------------------------------------------------------------
// Templates
// ---------------------------------------------------------------------------
const H = '<span class="moul">ឯកឧត្តមអគ្គនាយក</span>';

const headerHtml = (type, ctx, recipientHtml = '') => `
<div class="lc-header" data-region="header" ${DOC_VERSION_MARK} data-doc-type="${esc(type)}">
  <div class="lc-lh">
    <div class="lc-lh-l"><img class="lc-logo" src="${ctx.logoUrl}" alt="Logo"></div>
    <div class="lc-lh-c"><div class="lc-moul lc-green lc-k1">ព្រះរាជាណាចក្រកម្ពុជា</div><div class="lc-green lc-k2">ជាតិ សាសនា ព្រះមហាក្សត្រ</div><div class="lc-green lc-orn">* * *</div></div>
    <div></div>
    <div class="lc-lh-l"><div class="lc-moul lc-green lc-org">${ctx.organizationName}</div><div class="lc-green lc-num">លេខ៖ <span data-sys="doc-number">.......................</span> ន.ប.ធ./${ctx.abbr}</div></div>
    <div class="lc-lh-c">${recipientHtml}</div>
    <div></div>
  </div>
</div>`;

const subjectHtml = (subject, refs, extraRows = '') => `
<table class="lc-subj">
  <tr><td class="lc-lbl">កម្មវត្ថុ៖</td><td data-field="subject">${subject}</td></tr>
  <tr><td class="lc-lbl">យោង៖</td><td data-field="refs">${refs.map((r) => `<div class="lc-ref">${r}</div>`).join('')}</td></tr>
  ${extraRows}
</table>`;

export function buildTemplate(type, ctx) {
  if (type === 'SUBMISSION_NOTE') {
    return headerHtml(type, ctx) + `
<div class="lc-doc-title lc-moul">កំណត់បង្ហាញ</div>` +
      subjectHtml('ករណីស្នើសុំការពិនិត្យ និងសម្រេចដ៏ខ្ពង់ខ្ពស់លើ...', [`លិខិត ឬចំណាររបស់${H}...`],
        `<tr><td class="lc-lbl">គោរពជូន៖</td><td data-field="recipient">${H} មេត្តាពិនិត្យ និងសម្រេចដ៏ខ្ពង់ខ្ពស់។</td></tr>`) + `
<div class="lc-body" data-region="body">
  <p style="text-indent:0"><strong>I. សេចក្តីសង្ខេបអំពីអង្គហេតុ៖</strong></p>
  <p>(រៀបរាប់សង្ខេបអំពីព័ត៌មានលម្អិត និងអង្គហេតុដែលបានកើតឡើង...)</p>
  <p style="text-indent:0"><strong>II. មតិយោបល់ និងសំណូមពររបស់អង្គភាពជំនាញ៖</strong></p>
  <p>(ផ្តល់ជាមតិយោបល់បច្ចេកទេស ច្បាប់ ឬទស្សនវិស័យច្បាស់លាស់ក្នុងការដោះស្រាយ...)</p>
  <p style="text-indent:0"><strong>III. សំណើគោរពស្នើសុំការសម្រេច៖</strong></p>
  <p>ផ្អែកលើមូលដ្ឋាន និងមតិយោបល់ខាងលើ សូម ${H} មេត្តាពិនិត្យ និងសម្រេចដ៏ខ្ពង់ខ្ពស់៖</p>
  <ul><li>ឯកភាពលើខ្លឹមសារលិខិត ឬសំណើ...</li><li>ចុះហត្ថលេខាលើលិខិតឆ្លើយតបភ្ជាប់មកជាមួយនេះ។</li></ul>
</div>
${ctx.drafterHtml}`;
  }

  if (type === 'LETTER') {
    return headerHtml(type, ctx) + `
<div class="lc-letter-to"><div class="lc-moul">សូមគោរពជូន</div><div data-field="recipient">ឯកឧត្តម / លោកជំទាវ / លោក / លោកស្រី... (ស្ថាប័នទទួល)</div></div>` +
      subjectHtml('ស្តីពី...', ['លិខិតលេខ ......... ចុះថ្ងៃទី.........']) + `
<div class="lc-body" data-region="body">
  <p>តបតាមកម្មវត្ថុ និងយោងខាងលើ និយ័តករបរធនបាលកិច្ច សូមជម្រាបជូនថា៖ (សូមសរសេរខ្លឹមសារលម្អិតនៅទីនេះ...)។</p>
</div>
<div class="lc-closing" data-field="closing">
  <p>អាស្រ័យហេតុនេះ សូម ឯកឧត្តម / លោកជំទាវ / លោក / លោកស្រី មេត្តាជ្រាប និងសហការដោយក្តីអនុគ្រោះ។</p>
  <p>សូម ឯកឧត្តម / លោកជំទាវ / លោក / លោកស្រី មេត្តាទទួលនូវការគោរពដ៏ខ្ពង់ខ្ពស់ពីយើងខ្ញុំ។</p>
</div>
${ctx.drafterHtml}`;
  }

  // REPORT (default)
  return headerHtml('REPORT', ctx,
    `<div class="lc-recipient"><div class="lc-moul">សូមគោរពជូន</div><div class="lc-moul" data-field="recipient">ឯកឧត្តមអគ្គនាយកនៃ${ctx.organizationName}</div></div>`) +
    subjectHtml('របាយការណ៍ស្តីពី...', ['លិខិតលេខ...', `ចំណារ${H}...`]) + `
<div class="lc-body" data-region="body">
  <p>សេចក្ដីដូចមានចែងក្នុងកម្មវត្ថុ និងយោងខាងលើ ខ្ញុំសូមគោរពជម្រាបជូន ${H} មេត្តាជ្រាបដ៏ខ្ពង់ខ្ពស់ថា៖ (សូមសរសេររៀបរាប់ខ្លឹមសារលម្អិតនៅទីនេះ...)។</p>
</div>
<div class="lc-closing" data-field="closing">
  <p>សេចក្ដីដូចបានគោរពជម្រាបជូនខាងលើ សូម ${H} មេត្តាពិនិត្យ ព្រមទាំងផ្តល់ការណែនាំបន្ថែមដោយមេត្តាអនុគ្រោះដ៏ខ្ពង់ខ្ពស់។</p>
  <p>សូម ${H} មេត្តាទទួលនូវការគោរពដ៏ខ្ពង់ខ្ពស់ពីខ្ញុំ។</p>
</div>
${ctx.drafterHtml}`;
}

// ---------------------------------------------------------------------------
// Paste sanitizer for the body (same allowlist the server enforces)
// ---------------------------------------------------------------------------
const BLOCKS = { P: 1, DIV: 1, H1: 1, H2: 1, H3: 1, H4: 1, H5: 1, H6: 1, BLOCKQUOTE: 1 };
const KEEP = { STRONG: 'strong', B: 'strong', EM: 'em', I: 'em', U: 'u', UL: 'ul', OL: 'ol', LI: 'li', TABLE: 'table', THEAD: 'thead', TBODY: 'tbody', TR: 'tr', TD: 'td', TH: 'th', BR: 'br' };
const DROP = { SCRIPT: 1, STYLE: 1, META: 1, LINK: 1, TITLE: 1, XML: 1, HEAD: 1, IFRAME: 1, OBJECT: 1, EMBED: 1, IMG: 1, SVG: 1, CANVAS: 1, INPUT: 1, BUTTON: 1, SELECT: 1, TEXTAREA: 1 };

function cleanNode(node, out) {
  node.childNodes.forEach((n) => {
    if (n.nodeType === 3) { out.appendChild(document.createTextNode(n.nodeValue.replace(/ /g, ' '))); return; }
    if (n.nodeType !== 1) return;
    const tag = n.tagName.toUpperCase();
    if (DROP[tag] || tag.includes(':')) return;
    const isMoul = (tag === 'SPAN' || tag === 'FONT') &&
      ((n.classList && n.classList.contains('moul')) || /muol|moul/i.test((n.getAttribute('style') || '') + (n.getAttribute('face') || '')));
    let el = null;
    if (isMoul) { el = document.createElement('span'); el.className = 'moul'; }
    else if (KEEP[tag]) el = document.createElement(KEEP[tag]);
    else if (BLOCKS[tag]) el = document.createElement('p');
    if (el) {
      if (tag === 'TD' || tag === 'TH') ['colspan', 'rowspan'].forEach((a) => { const v = parseInt(n.getAttribute(a), 10); if (v > 1 && v < 20) el.setAttribute(a, v); });
      if (/^(P|LI|TD|TH)$/.test(el.tagName)) {
        const al = String((n.style && n.style.textAlign) || n.getAttribute('align') || '').toLowerCase();
        if (/^(left|center|right|justify)$/.test(al)) el.style.textAlign = al;
      }
      if (el.tagName !== 'BR') cleanNode(n, el);
      out.appendChild(el);
    } else cleanNode(n, out); // unwrap unknown wrappers (span, font, a, o:p ...)
  });
}

export function cleanBodyHtml(html) {
  const doc = new DOMParser().parseFromString(`<body>${html || ''}</body>`, 'text/html');
  const tmp = document.createElement('div');
  cleanNode(doc.body, tmp);
  const res = document.createElement('div');
  let buf = null;
  Array.from(tmp.childNodes).forEach((n) => {
    const block = n.nodeType === 1 && /^(P|UL|OL|TABLE)$/.test(n.tagName);
    if (block) { buf = null; res.appendChild(n); return; }
    if (n.nodeType === 3 && !n.nodeValue.trim() && !buf) return;
    if (!buf) { buf = document.createElement('p'); res.appendChild(buf); }
    buf.appendChild(n);
  });
  res.querySelectorAll('li p, td p, th p').forEach((p) => {
    const f = document.createDocumentFragment();
    while (p.firstChild) f.appendChild(p.firstChild);
    if (p.nextSibling) f.appendChild(document.createElement('br'));
    p.replaceWith(f);
  });
  res.querySelectorAll('p').forEach((p) => { if (!p.textContent.trim() && !p.querySelector('br')) p.remove(); });
  return res.innerHTML;
}

// ---------------------------------------------------------------------------
// Pagination
// ---------------------------------------------------------------------------
// Splits the document root into: letterhead, flowing blocks (body split per paragraph /
// list item) and the keep-together group (closing + signature + everything after it).
export function collectBlocks(root) {
  let header = null;
  const flow = [];
  const keep = [];
  let inKeep = false;
  Array.from(root.children).forEach((k) => {
    if (k.matches('[data-region="header"]')) { header = k; return; }
    if (!inKeep && (k.matches('[data-field="closing"]') || k.matches('.drafter-signature-table'))) inKeep = true;
    if (inKeep) { keep.push({ el: k, kind: 'keep' }); return; }
    if (k.matches('[data-region="body"]')) {
      Array.from(k.children).forEach((c) => {
        if (c.tagName === 'OL' || c.tagName === 'UL') {
          Array.from(c.children).forEach((li, index) => flow.push({ el: li, kind: 'li', list: c, index }));
        } else flow.push({ el: c, kind: 'body' });
      });
      return;
    }
    flow.push({ el: k, kind: 'flow' });
  });
  return { header, flow, keep };
}

function layout(items, keep, headerH, footerH, reserve) {
  const r = reserve ? NUM_H : 0;
  const P1 = PAGE_H - PAD_T - headerH - (footerH ? FOOT_BOTTOM + footerH + 8 : PAD_B) - r;
  const PN = PAGE_H - PAD_T - PAD_B - r;
  const pages = [[]];
  let rem = P1;
  const newPage = () => { pages.push([]); rem = PN; };
  const place = (it) => {
    if (it.h > rem && pages[pages.length - 1].length) newPage();
    pages[pages.length - 1].push(it);
    rem -= it.h;
  };
  items.forEach(place);
  const total = keep.reduce((s, k) => s + k.h, 0);
  const putAll = () => keep.forEach((k) => { pages[pages.length - 1].push(k); rem -= k.h; });
  if (total <= rem) putAll();
  else if (total <= PN) { newPage(); putAll(); }
  else keep.forEach(place); // block taller than a page: break between its units
  return pages;
}

// Page numbers only when there is more than one page (second pass reserves their space).
export function paginate(items, keep, headerH, footerH) {
  const first = layout(items, keep, headerH, footerH, false);
  if (first.length > 1) return { pages: layout(items, keep, headerH, footerH, true), multi: true };
  return { pages: first, multi: false };
}

// Renders the saved HTML as real A4 pages (view mode, print, Word export).
export function renderPagesHtml(savedHtml, { documentNumber = '', footerHtml = '', scale = 1 } = {}) {
  const box = document.createElement('div');
  box.className = 'lc-doc lc-measure-sheet';
  box.innerHTML = savedHtml || '';
  document.body.appendChild(box);
  box.querySelectorAll('[data-sys="doc-number"]').forEach((el) => { el.textContent = documentNumber || '.......................'; });

  let footerH = 0;
  if (footerHtml) {
    const f = document.createElement('div');
    f.className = 'lc-doc lc-measure';
    f.style.width = (PAGE_W - 68) + 'px';
    f.innerHTML = footerHtml;
    document.body.appendChild(f);
    footerH = Math.ceil(f.getBoundingClientRect().height);
    f.remove();
  }

  const outerH = (el) => { const cs = getComputedStyle(el); return el.offsetHeight + parseFloat(cs.marginTop || 0) + parseFloat(cs.marginBottom || 0); };
  const { header, flow, keep } = collectBlocks(box);
  const toItem = (b) => {
    if (b.kind === 'li') {
      const tag = b.list.tagName.toLowerCase();
      const start = parseInt(b.list.getAttribute('start') || '1', 10) + b.index;
      const extra = b.index === b.list.children.length - 1 ? parseFloat(getComputedStyle(b.list).marginBottom || 0) : 0;
      return { html: `<div class="lc-body"><${tag}${tag === 'ol' ? ` start="${start}"` : ''}>${b.el.outerHTML}</${tag}></div>`, h: b.el.offsetHeight + extra };
    }
    if (b.kind === 'body') return { html: `<div class="lc-body">${b.el.outerHTML}</div>`, h: outerH(b.el) };
    return { html: b.el.outerHTML, h: outerH(b.el) };
  };
  const headerH = header ? outerH(header) : 0;
  const headerOuter = header ? header.outerHTML : '';
  const r = paginate(flow.map(toItem), keep.map(toItem), headerH, footerH);
  box.remove();

  const s = scale || 1;
  const html = r.pages.map((items, i) => {
    const first = i === 0;
    const numBottom = first && footerH ? FOOT_BOTTOM + footerH + 6 : 18;
    return `<div class="lc-page-wrap" style="width:${PAGE_W * s}px;height:${PAGE_H * s}px">` +
      `<div class="lc-page lc-doc" style="transform:scale(${s})">` +
      `<div class="lc-page-inner">${first ? headerOuter : ''}${items.map((it) => `<div class="lc-blk">${it.html}</div>`).join('')}</div>` +
      (r.multi ? `<div class="lc-page-num" style="bottom:${numBottom}px">- ${toKhmerNum(i + 1)} -</div>` : '') +
      (first && footerHtml ? `<div class="lc-page-footer">${footerHtml}</div>` : '') +
      '</div></div>';
  }).join('');
  return { html, count: r.pages.length };
}
