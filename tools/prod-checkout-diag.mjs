#!/usr/bin/env node
/**
 * FULLPOWEREXHAUST — diagnostic du paiement en production (headless Chrome/CDP)
 * ---------------------------------------------------------------------------
 * Déroule le tunnel jusqu'à l'étape PAIEMENT (sans passer commande), puis :
 *   - capture les erreurs JS (console + exceptions + requêtes 4xx/5xx/échec) ;
 *   - inspecte les options de paiement (CB hébergée / PayPal) :
 *       présence des iframes, taille, classe de chargement, visibilité.
 *
 * Usage :
 *   BASE=https://fullpowerexhaust.com PRODUCT=144 node tools/prod-checkout-diag.mjs
 */

import { spawn } from 'node:child_process';
import { mkdirSync, writeFileSync } from 'node:fs';
import { setTimeout as sleep } from 'node:timers/promises';

const CHROME = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const PORT = 9342;
const BASE = process.env.BASE || 'https://fullpowerexhaust.com';
const PRODUCT = process.env.PRODUCT || '144';
const EMAIL = process.env.EMAIL || 'diag.paiement@example.com';
const OUT = '/tmp/fpe-prod-checkout';
mkdirSync(OUT, { recursive: true });

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
  '--user-data-dir=/tmp/fpe-chrome-prod', 'about:blank',
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
const netErrors = [];
cdp.on('Runtime.consoleAPICalled', (p) => {
  if (p.type === 'error') consoleLogs.push('[console.error] ' + (p.args || []).map((a) => a.value ?? a.description ?? '').join(' ').slice(0, 300));
});
cdp.on('Runtime.exceptionThrown', (p) => {
  consoleLogs.push('[exception] ' + (p.exceptionDetails?.exception?.description || p.exceptionDetails?.text || '?').slice(0, 300));
});
cdp.on('Network.responseReceived', (p) => {
  if (p.response.status >= 400) netErrors.push(`[${p.response.status}] ${p.response.url.slice(0, 120)}`);
});
cdp.on('Network.loadingFailed', (p) => {
  netErrors.push(`[FAILED ${p.errorText}] ${(p.blockedReason ? '(' + p.blockedReason + ') ' : '')}${p.requestId}`);
});

const nav = async (url, waitMs = 1500) => {
  await cdp.send('Page.navigate', { url }, sessionId);
  await new Promise((r) => { const t = setTimeout(r, 20000); cdp.on('Page.loadEventFired', () => { clearTimeout(t); r(); }); });
  await sleep(waitMs);
};
const evalJs = async (expr) => {
  const r = await cdp.send('Runtime.evaluate', { expression: expr, returnByValue: true, awaitPromise: true }, sessionId);
  if (r.exceptionDetails) throw new Error(r.exceptionDetails.exception?.description || 'eval error');
  return r.result.value;
};
const waitFor = async (sel, timeoutMs = 15000) => {
  const t0 = Date.now();
  while (Date.now() - t0 < timeoutMs) {
    if (await evalJs(`!!document.querySelector(${JSON.stringify(sel)})`)) return true;
    await sleep(300);
  }
  return false;
};
const shot = async (name) => {
  const s = await cdp.send('Page.captureScreenshot', { format: 'png' }, sessionId);
  writeFileSync(`${OUT}/${name}.png`, Buffer.from(s.data, 'base64'));
};

const fill = (sel, v) => `{ const e=document.querySelector(${JSON.stringify(sel)}); if(e){ e.value=${JSON.stringify(v)}; e.dispatchEvent(new Event('input',{bubbles:true})); e.dispatchEvent(new Event('change',{bubbles:true})); } }`;

console.log(`── Ajout produit ${PRODUCT} au panier`);
await nav(`${BASE}/${PRODUCT}-.html`);
const token = await evalJs(`window.prestashop?.static_token || document.querySelector('input[name=token]')?.value || ''`);
await evalJs(`fetch('${BASE}/index.php?controller=cart&add=1&id_product=${PRODUCT}&id_product_attribute=0&qty=1&token='+encodeURIComponent(${JSON.stringify(token)}),{credentials:'same-origin'}).then(r=>r.text())`);

console.log('── Tunnel');
await nav(`${BASE}/order`);
await waitFor('#checkout-personal-information-step input[name=email], #customer-form, #checkout-guest-form');

await evalJs(`(() => {
  ${fill('input[name=firstname]', 'Diag')}
  ${fill('input[name=lastname]', 'Paiement')}
  ${fill('input[name=email]', EMAIL)}
  document.querySelectorAll('input[name="customer_privacy"], input[id*="gdpr"], input[name*="privacy"]').forEach(c=>{ if(!c.checked){c.checked=true;c.dispatchEvent(new Event('change',{bubbles:true}));} });
  return true;
})()`);
await sleep(400);
await evalJs(`(() => { const b=document.querySelector('#customer-form button[name=continue], button[name=continue]'); if(b)b.click(); return !!b; })()`);
await sleep(3500);

await waitFor('#checkout-addresses-step');
await evalJs(`(() => {
  ${fill('select[name=id_country]', '8')}
  ${fill('input[name=address1]', '12 rue du Test')}
  ${fill('input[name=postcode]', '75011')}
  ${fill('input[name=city]', 'Paris')}
  ${fill('input[name=phone]', '0612345678')}
  ${fill('input[name=alias]', 'Test')}
  return true;
})()`);
await sleep(500);
await evalJs(`(() => { const b=document.querySelector('#checkout-addresses-step button[name=confirm-addresses], button[name=confirm-addresses]'); if(b)b.click(); return !!b; })()`);
await sleep(3500);

await waitFor('#checkout-delivery-step');
await evalJs(`(() => { const c=document.querySelector('input[name="delivery_option[]"], .delivery-option__check'); if(c&&!c.checked){c.checked=true;c.dispatchEvent(new Event('change',{bubbles:true}));} const b=document.querySelector('#checkout-delivery-step button[type=submit], #checkout-delivery-step button[name=confirmDeliveryOption]'); if(b)b.click(); return !!b; })()`);
await sleep(4000);

await waitFor('.payment-option', 20000);
await shot('paiement');

const report = await evalJs(`(() => {
  const out = { currentStep: document.querySelector('.js-current-step')?.id || null };
  out.paymentOptions = [...document.querySelectorAll('.payment-option')].map(o => {
    const info = o.querySelector('.js-additional-information');
    return {
      id: o.id,
      label: (o.querySelector('.payment-option__label')?.textContent || '').trim().slice(0,50),
      visible: o.getBoundingClientRect().width > 0 && o.getBoundingClientRect().height > 0,
      hasAdditionalInfo: !!info,
      additionalInfoDisplay: info ? getComputedStyle(info).display : null,
      iframes: [...o.querySelectorAll('iframe')].map(f => ({ src: (f.src||'').slice(0,80), w: Math.round(f.getBoundingClientRect().width), h: Math.round(f.getBoundingClientRect().height) })),
      cardFormLoading: o.querySelector('#ps_checkout-card-fields-form')?.className || null,
      cardFieldsContent: [...o.querySelectorAll('[id^="ps_checkout-card-fields-"]')].map(d => d.id + ':' + (d.querySelector('iframe') ? 'iframe' : (d.childElementCount ? 'children=' + d.childElementCount : 'vide'))).slice(0,8),
      paypalButtons: o.querySelector('.paypal-buttons, [id*="paypal"][class*="button"]')?.outerHTML?.slice(0,120) || null
    };
  });
  // erreurs visibles dans la page
  out.alerts = [...document.querySelectorAll('.alert')].map(a=>a.textContent.trim().slice(0,90)).filter(Boolean);
  return out;
})()`);

console.log(JSON.stringify(report, null, 2));

console.log('\n── Erreurs JS / réseau');
console.log([...new Set(consoleLogs)].slice(0, 30).join('\n') || '  (aucune erreur JS)');
console.log('--- requêtes en erreur ---');
console.log([...new Set(netErrors)].slice(0, 30).join('\n') || '  (aucune 4xx/5xx)');

await cdp.send('Target.closeTarget', { targetId });
proc.kill();
process.exit(0);
