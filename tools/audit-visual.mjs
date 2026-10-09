#!/usr/bin/env node
/**
 * FULLPOWEREXHAUST — audit visuel automatisé (headless Chrome via CDP)
 * ---------------------------------------------------------------------
 * Aucune dépendance npm : utilise le WebSocket natif de Node 22+ et le
 * protocole DevTools de Chrome (--remote-debugging-port).
 *
 * Usage :
 *   node tools/audit-visual.mjs                  → toutes les pages, desktop
 *   node tools/audit-visual.mjs --mobile         → viewport 390px
 *   node tools/audit-visual.mjs --all            → 3 viewports
 *   node tools/audit-visual.mjs --url /panier/   → une seule page
 *   node tools/audit-visual.mjs --shot           → + captures PNG
 */

import { spawn } from 'node:child_process';
import { mkdirSync, writeFileSync } from 'node:fs';
import { setTimeout as sleep } from 'node:timers/promises';

const CHROME =
  '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const BASE = process.env.AUDIT_BASE || 'http://localhost:8000';
const PORT = 9333;
const SHOT_DIR = '/tmp/fpe-audit';

const VIEWPORTS = {
  mobile: { width: 390, height: 844, mobile: true },
  tablet: { width: 768, height: 1024, mobile: true },
  desktop: { width: 1440, height: 900, mobile: false },
};

/* Pages couvrant toutes les sections du site. */
const PAGES = [
  ['accueil', '/'],
  ['categorie', '/6-echappements-moto'],
  ['sous-categorie', '/7-lignes-completes'],
  ['produit', '/19-yamaha-mt-07-ligne-complete.html'],
  ['recherche', '/search?controller=search&s=akrapovic'],
  ['panier', '/index.php?controller=cart&action=show'],
  ['commande', '/index.php?controller=order'],
  ['connexion', '/login'],
  ['inscription', '/registration'],
  ['mon-compte', '/my-account'],
  ['mot-de-passe-oublie', '/password-recovery'],
  ['contact', '/contact-us'],
  ['marques', '/brands'],
  ['nouveaux-produits', '/new-products'],
  ['promotions', '/prices-drop'],
  ['meilleures-ventes', '/best-sellers'],
  ['plan-du-site', '/sitemap'],
  ['cgv', '/content/3-conditions-generales-de-vente'],
  ['404', '/page-inexistante-xyz'],
];

/* ------------------------------------------------------------------ */
/* Client CDP minimal                                                  */
/* ------------------------------------------------------------------ */
class CDP {
  constructor(ws) {
    this.ws = ws;
    this.id = 0;
    this.pending = new Map();
    this.handlers = new Map();
    ws.addEventListener('message', (ev) => {
      const msg = JSON.parse(ev.data);
      if (msg.id && this.pending.has(msg.id)) {
        const { resolve, reject } = this.pending.get(msg.id);
        this.pending.delete(msg.id);
        msg.error ? reject(new Error(JSON.stringify(msg.error))) : resolve(msg.result);
      } else if (msg.method && this.handlers.has(msg.method)) {
        this.handlers.get(msg.method)(msg.params);
      }
    });
  }

  on(method, fn) {
    this.handlers.set(method, fn);
  }

  send(method, params = {}, sessionId) {
    const id = ++this.id;
    return new Promise((resolve, reject) => {
      this.pending.set(id, { resolve, reject });
      this.ws.send(JSON.stringify({ id, method, params, sessionId }));
      setTimeout(() => {
        if (this.pending.has(id)) {
          this.pending.delete(id);
          reject(new Error(`Timeout CDP: ${method}`));
        }
      }, 45000);
    });
  }
}

async function fetchJson(path) {
  const res = await fetch(`http://127.0.0.1:${PORT}${path}`);
  return res.json();
}

async function launchChrome() {
  const proc = spawn(
    CHROME,
    [
      '--headless=new',
      `--remote-debugging-port=${PORT}`,
      '--no-first-run',
      '--no-default-browser-check',
      '--disable-gpu',
      '--hide-scrollbars',
      '--user-data-dir=/tmp/fpe-chrome-profile',
      'about:blank',
    ],
    { stdio: 'ignore', detached: false }
  );
  for (let i = 0; i < 60; i++) {
    try {
      await fetchJson('/json/version');
      return proc;
    } catch {
      await sleep(250);
    }
  }
  throw new Error('Chrome ne répond pas sur le port de debug');
}

/* ------------------------------------------------------------------ */
/* Script d’audit injecté dans la page                                 */
/* ------------------------------------------------------------------ */
const AUDIT_FN = `(() => {
  const vw = window.innerWidth;
  const findings = { overflow: [], clipped: [], contrast: [], broken: [], hiddenText: [], tiny: [], overlaps: [], meta: {} };

  const txt = (el) => (el.textContent || '').replace(/\\s+/g, ' ').trim();
  /* Éléments volontairement masqués (a11y, offcanvas/modal/onglet fermé) → hors périmètre */
  const intentional = (el) => {
    if (!el || el === document.body) return false;
    const c = (el.getAttribute('class') || '');
    if (/visually-hidden|sr-only/.test(c)) return true;
    return !!el.closest('.offcanvas, .modal, .tab-pane, .dropdown-menu, .navbar-collapse, .search-widget-modal, .ps-hidden, [aria-hidden="true"]');
  };
  const path = (el) => {
    if (!el || el === document.body) return 'body';
    let s = el.tagName.toLowerCase();
    if (el.id) s += '#' + el.id;
    const cls = (el.getAttribute('class') || '').trim().split(/\\s+/).filter(Boolean).slice(0, 4);
    if (cls.length) s += '.' + cls.join('.');
    return s;
  };
  const visible = (el) => {
    const cs = getComputedStyle(el);
    if (cs.display === 'none' || cs.visibility === 'hidden' || cs.visibility === 'collapse') return false;
    if (parseFloat(cs.opacity) === 0) return false;
    const r = el.getBoundingClientRect();
    return r.width > 0 && r.height > 0;
  };

  /* 1. Débordement horizontal */
  findings.meta.docScrollWidth = document.documentElement.scrollWidth;
  findings.meta.viewport = vw;
  findings.meta.bodyHeight = document.body.scrollHeight;

  for (const el of document.querySelectorAll('body *')) {
    if (!visible(el)) continue;
    const r = el.getBoundingClientRect();
    if (r.width === 0 || r.height === 0) continue;

    // élément qui dépasse la largeur du viewport
    if (r.right > vw + 2 || r.left < -2) {
      const cs = getComputedStyle(el);
      if (cs.position !== 'fixed' && el.offsetParent !== null) {
        if (txt(el).length > 0 || el.tagName === 'IMG' || el.tagName === 'svg') {
          findings.overflow.push({
            el: path(el), left: Math.round(r.left), right: Math.round(r.right),
            w: Math.round(r.width), text: txt(el).slice(0, 60),
          });
        }
      }
    }

    // texte tronqué (scrollWidth > clientWidth) sur conteneurs texte
    const isText = el.children.length === 0 && txt(el).length > 0 && !intentional(el);
    if (isText && el.scrollWidth > el.clientWidth + 2 && el.clientWidth > 0) {
      const cs = getComputedStyle(el);
      if (cs.overflow === 'hidden' || cs.overflowX === 'hidden') {
        findings.clipped.push({ el: path(el), text: txt(el).slice(0, 60), sw: el.scrollWidth, cw: el.clientWidth });
      }
    }

    // débordement vertical masqué (texte coupé)
    if (isText && el.scrollHeight > el.clientHeight + 4 && el.clientHeight > 0) {
      const cs = getComputedStyle(el);
      if (cs.overflow === 'hidden' || cs.overflowY === 'hidden') {
        findings.clipped.push({ el: path(el), text: txt(el).slice(0, 60), sh: el.scrollHeight, ch: el.clientHeight, axis: 'y' });
      }
    }
  }

  /* 2. Texte invisible (hidden/opacity 0) alors qu’il porte du sens */
  for (const el of document.querySelectorAll('p, h1, h2, h3, h4, span, a, button, li, label, div')) {
    const t = txt(el);
    if (!t || t.length < 2) continue;
    if (el.children.length > 0 && el.querySelector('p,h1,h2,h3,h4,span,a,button,li,label')) continue;
    if (intentional(el)) continue;
    const cs = getComputedStyle(el);
    if (cs.display === 'none') continue;
    if (cs.visibility === 'hidden' || cs.visibility === 'collapse') {
      /* On ne signale que si AUCUN ancêtre n'est volontairement masqué */
      findings.hiddenText.push({ el: path(el), text: t.slice(0, 60), why: 'visibility:' + cs.visibility });
    }
  }

  /* 3. Contraste texte / fond (approx., en remontant les fonds) */
  const parse = (c) => {
    const m = c.match(/rgba?\\(([^)]+)\\)/);
    if (!m) return null;
    const p = m[1].split(',').map((x) => parseFloat(x));
    if (p.length > 3 && p[3] === 0) return null;
    return { r: p[0], g: p[1], b: p[2] };
  };
  const lum = ({ r, g, b }) => {
    const f = (v) => { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); };
    return 0.2126 * f(r) + 0.7152 * f(g) + 0.0722 * f(b);
  };
  const ratio = (a, b) => { const l1 = lum(a), l2 = lum(b); return (Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05); };
  const bgOf = (el) => {
    let n = el;
    while (n && n !== document.documentElement) {
      const c = parse(getComputedStyle(n).backgroundColor);
      if (c) return c;
      n = n.parentElement;
    }
    return { r: 255, g: 255, b: 255 };
  };
  const seen = new Set();
  for (const el of document.querySelectorAll('p, h1, h2, h3, h4, h5, h6, a, button, span, li, label, td, th, small, strong, div')) {
    if (!visible(el) || intentional(el)) continue;
    const isText = el.children.length === 0 && txt(el).length > 1;
    if (!isText) continue;
    const cs = getComputedStyle(el);
    const fg = parse(cs.color);
    if (!fg) continue;
    const size = parseFloat(cs.fontSize);
    const bold = parseInt(cs.fontWeight, 10) >= 700;
    const large = size >= 24 || (size >= 18.66 && bold);
    const need = large ? 3 : 4.5;
    const r = ratio(fg, bgOf(el));
    if (r < need) {
      const key = cs.color + '|' + path(el).slice(0, 40);
      if (seen.has(key)) continue;
      seen.add(key);
      findings.contrast.push({ el: path(el), text: txt(el).slice(0, 50), color: cs.color, bg: getComputedStyle(el.parentElement || el).backgroundColor, ratio: Math.round(r * 100) / 100, need, size });
    }
  }

  /* 4. Images cassées / sans dimensions */
  for (const img of document.images) {
    if (img.getBoundingClientRect().width === 0 && img.getBoundingClientRect().height === 0) continue;
    if (intentional(img)) continue;
    const r = img.getBoundingClientRect();
    const inView = r.bottom > -200 && r.top < window.innerHeight + 200;
    if (img.loading === 'lazy' && !inView) continue;   // pas encore chargée : normal
    if (!img.complete || img.naturalWidth === 0) {
      findings.broken.push({ src: (img.currentSrc || img.src || '').slice(-70), alt: img.alt, w: Math.round(img.getBoundingClientRect().width) });
    }
  }

  /* 5. Zones cliquables trop petites (mobile) */
  if (vw <= 768) {
    for (const el of document.querySelectorAll('a, button, input[type=submit], select')) {
      if (!visible(el) || intentional(el)) continue;
      const r = el.getBoundingClientRect();
      if (r.width < 24 || r.height < 24) {
        if (el.closest('.breadcrumb, .pagination, .swiper, .carousel')) continue;
        findings.tiny.push({ el: path(el), w: Math.round(r.width), h: Math.round(r.height), text: txt(el).slice(0, 30) });
      }
    }
  }

  /* 6. Chevauchements : éléments fixés/sticky qui recouvrent du contenu */
  const fixed = [...document.querySelectorAll('body *')].filter((el) => {
    if (!visible(el)) return false;
    const cs = getComputedStyle(el);
    return cs.position === 'fixed' && el.getBoundingClientRect().height > 20;
  });
  for (const f of fixed) {
    const fr = f.getBoundingClientRect();
    for (const el of document.querySelectorAll('h1, h2, .btn, form input, .product-miniature, footer')) {
      if (!visible(el) || intentional(el) || f.contains(el) || el.contains(f)) continue;
      const r = el.getBoundingClientRect();
      const overlapY = Math.min(fr.bottom, r.bottom) - Math.max(fr.top, r.top);
      const overlapX = Math.min(fr.right, r.right) - Math.max(fr.left, r.left);
      if (overlapY > 8 && overlapX > 8) {
        findings.overlaps.push({ fixed: path(f), target: path(el), text: txt(el).slice(0, 40), overlapY: Math.round(overlapY) });
      }
    }
  }
  findings.overlaps = findings.overlaps.slice(0, 12);

  return findings;
})()`;

/* ------------------------------------------------------------------ */
/* Audit d’une URL                                                     */
/* ------------------------------------------------------------------ */
async function auditPage(cdp, sessionId, url, vp, shot) {
  await cdp.send('Emulation.setDeviceMetricsOverride', {
    width: vp.width, height: vp.height, deviceScaleFactor: 1, mobile: vp.mobile,
  }, sessionId);
  await cdp.send('Page.navigate', { url }, sessionId);
  // attente du chargement
  await new Promise((resolve) => {
    const t = setTimeout(resolve, 15000);
    cdp.on('Page.loadEventFired', () => { clearTimeout(t); resolve(); });
  });
  await sleep(800);

  /* Parcours vertical : déclenche le lazy-loading et les animations d’entrée */
  await cdp.send('Runtime.evaluate', {
    expression: `(async () => {
      document.documentElement.style.scrollBehavior = 'auto';  // sinon le retour en haut est animé
      const step = Math.round(window.innerHeight * 0.8);
      for (let y = 0; y < document.body.scrollHeight; y += step) {
        window.scrollTo(0, y);
        await new Promise((r) => setTimeout(r, 90));
      }
      window.scrollTo(0, 0);
      await new Promise((r) => setTimeout(r, 250));
      await Promise.all([...document.images].filter((i) => !i.complete).map((i) => new Promise((r) => {
        i.addEventListener('load', r, { once: true }); i.addEventListener('error', r, { once: true });
        setTimeout(r, 3000);
      })));
      return true;
    })()`,
    awaitPromise: true, returnByValue: true,
  }, sessionId);
  await sleep(300);

  const res = await cdp.send('Runtime.evaluate', {
    expression: AUDIT_FN, returnByValue: true, awaitPromise: false,
  }, sessionId);

  let screenshot = null;
  if (shot) {
    const s = await cdp.send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true }, sessionId);
    screenshot = s.data;
  }
  return { findings: res.result.value, screenshot };
}

/* ------------------------------------------------------------------ */
/* Rapport                                                             */
/* ------------------------------------------------------------------ */
const SEV = {
  overflow: '🔴 DÉBORDEMENT',
  clipped: '🟠 TEXTE COUPÉ',
  contrast: '🟡 CONTRASTE',
  broken: '🔴 IMAGE CASSÉE',
  hiddenText: '🟠 TEXTE INVISIBLE',
  tiny: '🟡 CIBLE TACTILE',
  overlaps: '🟠 CHEVAUCHEMENT',
};

function report(name, vpName, f) {
  const lines = [];
  lines.push(`\n┌── ${name}  [${vpName} ${f.meta.viewport}px]`);
  lines.push(`│   scrollWidth=${f.meta.docScrollWidth} (viewport=${f.meta.viewport})${f.meta.docScrollWidth > f.meta.viewport + 1 ? '  ⚠️ SCROLL HORIZONTAL' : ''}`);
  let total = 0;
  for (const key of Object.keys(SEV)) {
    const arr = f[key] || [];
    if (!arr.length) continue;
    total += arr.length;
    lines.push(`│ ${SEV[key]} — ${arr.length}`);
    for (const item of arr.slice(0, 10)) {
      lines.push('│   • ' + JSON.stringify(item));
    }
    if (arr.length > 10) lines.push(`│   … +${arr.length - 10}`);
  }
  if (!total && f.meta.docScrollWidth <= f.meta.viewport + 1) lines.push('│   ✅ aucun problème détecté');
  return lines.join('\n');
}

/* ------------------------------------------------------------------ */
/* main                                                                */
/* ------------------------------------------------------------------ */
const args = process.argv.slice(2);
const onlyUrl = args.includes('--url') ? args[args.indexOf('--url') + 1] : null;
const doShot = args.includes('--shot');
let vpNames;
if (args.includes('--all')) vpNames = ['mobile', 'tablet', 'desktop'];
else if (args.includes('--mobile')) vpNames = ['mobile'];
else if (args.includes('--tablet')) vpNames = ['tablet'];
else vpNames = ['desktop'];

mkdirSync(SHOT_DIR, { recursive: true });
const chrome = await launchChrome();
const version = await fetchJson('/json/version');
const cdp = new CDP(new WebSocket(version.webSocketDebuggerUrl));
await new Promise((r) => cdp.ws.addEventListener('open', r));
await cdp.send('Target.setDiscoverTargets', { discover: true });

const { targetId } = await cdp.send('Target.createTarget', { url: 'about:blank' });
const { sessionId } = await cdp.send('Target.attachToTarget', { targetId, flatten: true });
await cdp.send('Page.enable', {}, sessionId);
await cdp.send('Runtime.enable', {}, sessionId);
await cdp.send('Network.enable', {}, sessionId);
await cdp.send('Network.setCacheDisabled', { cacheDisabled: true }, sessionId);

const pages = onlyUrl ? [[onlyUrl, onlyUrl]] : PAGES;
let grandTotal = 0;
const summary = [];

for (const vpName of vpNames) {
  const vp = VIEWPORTS[vpName];
  for (const [name, p] of pages) {
    const url = p.startsWith('http') ? p : BASE + p;
    try {
      const { findings, screenshot } = await auditPage(cdp, sessionId, url, vp, doShot);
      if (!findings) { console.log(`\n┌── ${name} [${vpName}]  ❌ pas de résultat`); continue; }
      const n =
        (findings.overflow?.length || 0) + (findings.clipped?.length || 0) +
        (findings.broken?.length || 0) + (findings.hiddenText?.length || 0) +
        (findings.tiny?.length || 0) + (findings.overlaps?.length || 0) +
        (findings.contrast?.length || 0) +
        (findings.meta.docScrollWidth > findings.meta.viewport + 1 ? 1 : 0);
      grandTotal += n;
      summary.push({ page: name, vp: vpName, issues: n });
      console.log(report(name, vpName, findings));
      if (screenshot) {
        writeFileSync(`${SHOT_DIR}/${name}-${vpName}.png`, Buffer.from(screenshot, 'base64'));
      }
    } catch (e) {
      console.log(`\n┌── ${name} [${vpName}]  ❌ ${e.message}`);
    }
  }
}

console.log('\n════════════════════════════════════════════');
console.log(`TOTAL PROBLÈMES : ${grandTotal}`);
summary.sort((a, b) => b.issues - a.issues).forEach((s) => {
  if (s.issues) console.log(`  ${s.issues.toString().padStart(3)}  ${s.page} (${s.vp})`);
});
if (doShot) console.log(`Captures : ${SHOT_DIR}`);

await cdp.send('Target.closeTarget', { targetId });
chrome.kill();
process.exit(0);
