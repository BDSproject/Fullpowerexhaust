#!/usr/bin/env node
/**
 * FULLPOWEREXHAUST — sonde DOM (headless Chrome / CDP, sans dépendance)
 *
 * Évalue une expression JS dans la page et affiche le résultat en JSON.
 *
 * Usage :
 *   node tools/probe.mjs <url> "<expression JS>" [--w 390] [--h 844] [--mobile] [--wait 800]
 *
 * L’expression peut utiliser `document`. Le résultat doit être sérialisable.
 * Raccourci disponible : `$$(sel)` → [...document.querySelectorAll(sel)]
 *                       `rect(el)` → géométrie arrondie
 *                       `cs(el)`    → style calculé (sous-ensemble utile)
 */

import { spawn } from 'node:child_process';
import { setTimeout as sleep } from 'node:timers/promises';

const CHROME = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const PORT = 9335;

const argv = process.argv.slice(2);
const url = argv[0];
const expr = argv[1];
const opt = (n, d) => (argv.includes(n) ? argv[argv.indexOf(n) + 1] : d);
const flag = (n) => argv.includes(n);

if (!url || !expr) {
  console.error('Usage: node tools/probe.mjs <url> "<expression>" [--w 1440] [--h 900] [--mobile] [--wrap]');
  process.exit(1);
}

const width = parseInt(opt('--w', '1440'), 10);
const height = parseInt(opt('--h', '900'), 10);
const wait = parseInt(opt('--wait', '900'), 10);
const jsCode = opt('--js', null);
const navAfter = opt('--then', null);

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
      setTimeout(() => { if (this.pending.has(id)) { this.pending.delete(id); reject(new Error('timeout ' + method)); } }, 30000);
    });
  }
}

const proc = spawn(CHROME, [
  '--headless=new', `--remote-debugging-port=${PORT}`, '--no-first-run',
  '--no-default-browser-check', '--disable-gpu', '--hide-scrollbars',
  '--user-data-dir=/tmp/fpe-chrome-probe', 'about:blank',
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
await cdp.send('Emulation.setDeviceMetricsOverride', { width, height, deviceScaleFactor: 1, mobile: flag('--mobile') }, sessionId);
await cdp.send('Page.navigate', { url }, sessionId);
await new Promise((r) => { const t = setTimeout(r, 15000); cdp.on('Page.loadEventFired', () => { clearTimeout(t); r(); }); });
await sleep(wait);

if (jsCode) {
  const r = await cdp.send('Runtime.evaluate', { expression: `(async () => { ${jsCode} })()`, awaitPromise: true, returnByValue: true }, sessionId);
  if (r.exceptionDetails) console.error('⚠️  JS :', r.exceptionDetails.exception?.description?.split('\n')[0]);
  await sleep(500);
}
if (navAfter) {
  await cdp.send('Page.navigate', { url: navAfter }, sessionId);
  await new Promise((r) => { const t = setTimeout(r, 15000); cdp.on('Page.loadEventFired', () => { clearTimeout(t); r(); }); });
  await sleep(wait);
}

const prelude = `
  const $$ = (s) => [...document.querySelectorAll(s)];
  const rect = (e) => { if (!e) return null; const r = e.getBoundingClientRect();
    return { x: Math.round(r.x), y: Math.round(r.y), w: Math.round(r.width), h: Math.round(r.height),
             right: Math.round(r.right), bottom: Math.round(r.bottom) }; };
  const cs = (e, props) => { if (!e) return null; const c = getComputedStyle(e);
    const out = {}; (props || ['display','position','visibility','opacity','color','backgroundColor','fontSize','fontWeight','overflow','overflowX','overflowY','zIndex','width','height','padding','margin','flexDirection','gap','gridTemplateColumns','borderRadius','textTransform','lineHeight','maxWidth','minWidth','transform','top','left','right','bottom','whiteSpace','textOverflow']).forEach(p => out[p] = c[p]); return out; };
  const txt = (e) => (e?.textContent || '').replace(/\\s+/g,' ').trim().slice(0,80);
`;

const res = await cdp.send('Runtime.evaluate', {
  expression: `(() => { ${prelude} return (${expr}); })()`,
  returnByValue: true, awaitPromise: true,
}, sessionId);

if (res.exceptionDetails) {
  console.error('❌ Erreur JS :', JSON.stringify(res.exceptionDetails.exception?.description || res.exceptionDetails, null, 2));
} else {
  console.log(JSON.stringify(res.result.value, null, flag('--wrap') ? 0 : 2));
}

await cdp.send('Target.closeTarget', { targetId });
proc.kill();
process.exit(0);
