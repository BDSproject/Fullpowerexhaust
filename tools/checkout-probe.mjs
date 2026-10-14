#!/usr/bin/env node
/**
 * FULLPOWEREXHAUST — reproduction du tunnel de commande (headless Chrome/CDP)
 *
 * Ajoute un produit au panier, déroule les étapes du checkout jusqu'au
 * paiement, puis :
 *   - capture les erreurs JS de la console ;
 *   - dresse l'état des champs / overlays sur l'étape paiement ;
 *   - prend des captures d'écran.
 *
 * Usage : node tools/checkout-probe.mjs [--url-base=http://localhost:8000]
 */

import { spawn } from 'node:child_process';
import { mkdirSync, writeFileSync } from 'node:fs';
import { setTimeout as sleep } from 'node:timers/promises';

const CHROME = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const PORT = 9340;
const BASE = process.env.BASE || 'http://localhost:8000';
const OUT = '/tmp/fpe-checkout';
mkdirSync(OUT, { recursive: true });

const argv = process.argv.slice(2);
const idx = (n) => argv.indexOf(n);

class CDP {
  constructor(ws) {
    this.ws = ws; this.id = 0; this.pending = new Map(); this.handlers = new Map();
    ws.addEventListener('message', (ev) => {
      const m = JSON.parse(ev.data);
      if (m.id && this.pending.has(m.id)) {
        const { resolve, reject } = this.pending.get(m.id);
        this.pending.delete(m.id);
        m.error ? reject(new Error(JSON.stringify(m.error))) : resolve(m.result);
      } else if (m.method && this.handlers.has(m.method)) this.handlers.get(m.method)(m.params);
    });
  }
  on(m, fn) { this.handlers.set(m, fn); }
  send(method, params = {}, sessionId) {
    const id = ++this.id;
    return new Promise((resolve, reject) => {
      this.pending.set(id, { resolve, reject });
      this.ws.send(JSON.stringify({ id, method, params, sessionId }));
      setTimeout(() => { if (this.pending.has(id)) { this.pending.delete(id); reject(new Error('timeout ' + method)); } }, 40000);
    });
  }
}

const proc = spawn(CHROME, [
  '--headless=new', `--remote-debugging-port=${PORT}`, '--no-first-run',
  '--no-default-browser-check', '--disable-gpu', '--hide-scrollbars',
  '--user-data-dir=/tmp/fpe-chrome-checkout', 'about:blank',
], { stdio: 'ignore' });

let wsUrl;
for (let i = 0; i < 60; i++) {
  try { wsUrl = (await (await fetch(`http://127.0.0.1:${PORT}/json/version`)).json()).webSocketDebuggerUrl; break; }
  catch { await sleep(250); }
}
if (!wsUrl) { console.error('Chrome KO'); process.exit(1); }

const cdp = new CDP(new WebSocket(wsUrl));
await new Promise((r) => cdp.ws.addEventListener('open', r));

const { targetId } = await cdp.send('Target.createTarget', { url: 'about:blank' });
const { sessionId } = await cdp.send('Target.attachToTarget', { targetId, flatten: true });
await cdp.send('Page.enable', {}, sessionId);
await cdp.send('Runtime.enable', {}, sessionId);
await cdp.send('Network.enable', {}, sessionId);
await cdp.send('Network.setCacheDisabled', { cacheDisabled: true }, sessionId);
await cdp.send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 900, deviceScaleFactor: 1, mobile: false }, sessionId);

const consoleLogs = [];
cdp.on('Runtime.consoleAPICalled', (p) => {
  if (p.type === 'error') consoleLogs.push('[console.error] ' + (p.args || []).map((a) => a.value ?? a.description ?? '').join(' '));
});
cdp.on('Runtime.exceptionThrown', (p) => {
  consoleLogs.push('[exception] ' + (p.exceptionDetails?.exception?.description || p.exceptionDetails?.text || '?'));
});

const nav = async (url, waitMs = 1200) => {
  await cdp.send('Page.navigate', { url }, sessionId);
  await new Promise((r) => { const t = setTimeout(r, 15000); cdp.on('Page.loadEventFired', () => { clearTimeout(t); r(); }); });
  await sleep(waitMs);
};

const evalJs = async (expr, awaitPromise = true) => {
  const r = await cdp.send('Runtime.evaluate', { expression: expr, returnByValue: true, awaitPromise }, sessionId);
  if (r.exceptionDetails) throw new Error(r.exceptionDetails.exception?.description || 'eval error');
  return r.result.value;
};

const shot = async (name) => {
  const s = await cdp.send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false }, sessionId);
  writeFileSync(`${OUT}/${name}.png`, Buffer.from(s.data, 'base64'));
  console.log(`  📸 ${name}.png`);
};

const waitFor = async (sel, timeoutMs = 12000) => {
  const t0 = Date.now();
  while (Date.now() - t0 < timeoutMs) {
    const found = await evalJs(`!!document.querySelector(${JSON.stringify(sel)})`);
    if (found) return true;
    await sleep(250);
  }
  return false;
};

const step = (name) => console.log(`\n── Étape : ${name}`);
const currentStep = () => evalJs(`(() => { const s = document.querySelector('.js-current-step, section.active.js-current-step'); return s ? (s.id || s.className) : 'AUCUNE'; })()`);
const logState = async (label) => {
  const st = await currentStep();
  const info = await evalJs(`(() => {
    const set = (sel) => { const e = document.querySelector(sel); return e ? (e.value || e.checked || '(vide)') : 'ABSENT'; };
    return {
      etape: document.querySelector('.js-current-step')?.id || null,
      firstname: set('input[name=firstname]'),
      email: set('input[name=email]'),
      address1: set('input[name=address1]'),
      nbOptions: document.querySelectorAll('.payment-option').length,
      alertes: [...document.querySelectorAll('.alert')].map(a=>a.textContent.trim().slice(0,80)).filter(Boolean),
      btns: [...document.querySelectorAll('button')].map(b=>b.textContent.trim().slice(0,30)).filter(t=>/contin|payer|commande|valider|livr|paiement/i.test(t)).slice(0,6)
    };
  })()`);
  console.log('  [' + label + ']', JSON.stringify(info));
};

/* 1. Ajout au panier ------------------------------------------------------ */
console.log('── Panier');
await nav(`${BASE}/19-yamaha-mt-07-ligne-complete.html`);
await waitFor('body.product, .product__add-to-cart, form[data-id-product]', 15000);
const token = await evalJs(`window.prestashop?.static_token || document.querySelector('input[name=token]')?.value || ''`);
console.log('  token:', token.slice(0, 12) || '(vide)');
const added = await evalJs(`(async () => {
  const r = await fetch('${BASE}/index.php?controller=cart&add=1&id_product=19&id_product_attribute=0&qty=1&token=' + encodeURIComponent(${JSON.stringify(token)}), {credentials:'same-origin'});
  return 'status=' + r.status;
})().catch(e => 'ERR ' + e.message)`);
console.log('  ajout panier:', added);

/* 2. Tunnel --------------------------------------------------------------- */
await nav(`${BASE}/order`);

step('Informations personnelles');
await waitFor('#customer-form, #checkout-personal-information-step');
await shot('01-infos');
const fill = async () => {
  await evalJs(`(() => {
    const set = (sel, v) => { const e = document.querySelector(sel); if (e) { e.value = v; e.dispatchEvent(new Event('input', {bubbles:true})); e.dispatchEvent(new Event('change', {bubbles:true})); } };
    set('#customer-form input[name=firstname], #checkout-personal-information-step input[name=firstname], input[name=firstname]', 'Jean');
    set('#customer-form input[name=lastname], input[name=lastname]', 'Dupont');
    set('#customer-form input[name=email], input[name=email]', 'jean.dupont@example.com');
    // consentement RGPD éventuel
    document.querySelectorAll('input[name="customer_privacy"], input[id*="gdpr"], input[name*="privacy"]').forEach(c => { if (!c.checked) { c.checked = true; c.dispatchEvent(new Event('change', {bubbles:true})); } });
    return true;
  })()`);
};
await fill();
await sleep(300);
await logState('avant continuer infos');
await evalJs(`(() => { const b = document.querySelector('#customer-form button[name=continue], #checkout-personal-information-step button[name=continue], button[name=continue]'); if (b) b.click(); return !!b; })()`);
await sleep(3000);
await logState('après continuer infos');

step('Adresses');
await waitFor('#checkout-addresses-step', 15000);
await shot('02-adresses');
const addrFields = await evalJs(`[...document.querySelectorAll('#checkout-addresses-step input, #checkout-addresses-step select')].map(e => ({tag:e.tagName, name:e.name, id:e.id, type:e.type, value:(e.value||'').slice(0,20)})).filter(e=>e.name)`);
console.log('  champs adresse:', JSON.stringify(addrFields));
await evalJs(`(() => {
  const set = (sel, v) => { const e = document.querySelector(sel); if (e) { e.value = v; e.dispatchEvent(new Event('input', {bubbles:true})); e.dispatchEvent(new Event('change', {bubbles:true})); } };
  set('input[name=address1]', '12 rue de la République');
  set('input[name=postcode]', '75011');
  set('input[name=city]', 'Paris');
  set('input[name=phone]', '0612345678');
  set('input[name=alias]', 'Mon adresse');
  return true;
})()`);
await sleep(300);
await shot('02b-adresses-remplies');
await evalJs(`(() => { const b = document.querySelector('#checkout-addresses-step button[name=confirm-addresses], button[name=confirm-addresses], #checkout-addresses-step button[type=submit]'); if (b) b.click(); return !!b; })()`);
await sleep(3000);
await logState('après continuer adresses');

step('Livraison');
await waitFor('#checkout-delivery-step, input[name=delivery_option], .delivery-option', 15000);
await shot('03-livraison');
await evalJs(`(() => { const c = document.querySelector('input[name="delivery_option[]"], .delivery-option__check'); if (c && !c.checked) { c.checked = true; c.dispatchEvent(new Event('change', {bubbles:true})); } const b = document.querySelector('#checkout-delivery-step button[type=submit], #checkout-delivery-step .continue, button[name=confirmDeliveryOption], #checkout-delivery-step button[data-ps-action]'); if (b) b.click(); return !!b; })()`);
await sleep(3000);
await logState('après continuer livraison');

step('Paiement');
await waitFor('#checkout-payment-step, .payment-option, .payment-options__list', 15000);
await shot('04-paiement');

const report = await evalJs(`(() => {
  const out = {};
  out.url = location.pathname;
  out.paymentOptions = [...document.querySelectorAll('.payment-option')].map(o => ({
    id: o.id,
    label: (o.querySelector('.payment-option__label')?.textContent || '').trim().slice(0,60),
    hasForm: !!o.querySelector('.js-additional-information'),
    additionalInfoVisible: (o.querySelector('.js-additional-information')?.style.display !== 'none'),
    inputs: [...o.querySelectorAll('input, select, iframe, button, a')].map(e => ({ tag: e.tagName, type: e.type || '', name: e.name || '', id: e.id || '', visible: e.getBoundingClientRect().width > 0 && e.getBoundingClientRect().height > 0, disabled: e.disabled, readonly: e.readOnly, pe: getComputedStyle(e).pointerEvents })).filter(e => e.visible).slice(0, 20)
  }));
  // overlays couvrant la zone de paiement
  out.overlays = [];
  const payBox = document.querySelector('#checkout-payment-step, .payment-options__list')?.getBoundingClientRect();
  if (payBox) {
    for (const el of document.querySelectorAll('body *')) {
      const cs = getComputedStyle(el);
      if (cs.position === 'fixed' || cs.position === 'absolute') {
        const r = el.getBoundingClientRect();
        if (r.width === 0 || r.height === 0) continue;
        const ox = Math.min(r.right, payBox.right) - Math.max(r.left, payBox.left);
        const oy = Math.min(r.bottom, payBox.bottom) - Math.max(r.top, payBox.top);
        if (ox > 20 && oy > 20 && cs.pointerEvents !== 'none' && !el.contains(payBox)) {
          out.overlays.push({ el: el.className.toString().slice(0,60) || el.tagName, z: cs.zIndex, pe: cs.pointerEvents, top: Math.round(r.top), height: Math.round(r.height) });
        }
      }
    }
  }
  // pointeur au centre de la zone de paiement
  if (payBox) {
    const cx = payBox.left + payBox.width/2, cy = payBox.top + 60;
    const el = document.elementFromPoint(cx, cy);
    out.elementAtPaymentTop = el ? (el.className.toString().slice(0,60) || el.tagName) + ' :: ' + (el.textContent||'').trim().slice(0,40) : null;
  }
  return out;
})()`);

console.log(JSON.stringify(report, null, 2));
console.log('\n── Erreurs JS détectées ──');
console.log(consoleLogs.length ? consoleLogs.join('\n') : '  (aucune)');

await cdp.send('Target.closeTarget', { targetId });
proc.kill();
process.exit(0);
