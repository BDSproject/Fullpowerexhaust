#!/usr/bin/env node
/**
 * FULLPOWEREXHAUST — capture d'écran ciblée (headless Chrome / CDP, sans dépendance)
 *
 * Usage :
 *   node tools/shot.mjs <url> <sortie.png> [options]
 *
 * Options :
 *   --w 390 | --h 844          viewport (défaut 1440x900)
 *   --mobile                   active l’émulation mobile (UA + touch)
 *   --clip x,y,w,h             ne capturer qu’une zone (coordonnées page)
 *   --full                     capture pleine page (défaut : viewport)
 *   --wait 1500                attente supplémentaire en ms
 *   --scroll 800               défile de N px avant capture
 *   --click "<sélecteur>"      clique un élément avant capture
 *   --js "<code>"              exécute du JS (async) après chargement
 *   --then "<url>"             navigue vers une 2e URL avant capture
 *   --hover "<sélecteur>"      survole un élément avant capture
 */

import { spawn } from 'node:child_process';
import { writeFileSync } from 'node:fs';
import { setTimeout as sleep } from 'node:timers/promises';

const CHROME = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const PORT = 9334;

const argv = process.argv.slice(2);
const url = argv[0];
const out = argv[1];
const opt = (name, def) => (argv.includes(name) ? argv[argv.indexOf(name) + 1] : def);
const flag = (name) => argv.includes(name);

if (!url || !out) {
  console.error('Usage: node tools/shot.mjs <url> <sortie.png> [--w 390 --h 844 --clip x,y,w,h --full --mobile --scroll N --click SEL]');
  process.exit(1);
}

const width = parseInt(opt('--w', '1440'), 10);
const height = parseInt(opt('--h', '900'), 10);
const wait = parseInt(opt('--wait', '900'), 10);
const scroll = parseInt(opt('--scroll', '0'), 10);
const clipArg = opt('--clip', null);
const clickSel = opt('--click', null);
const hoverSel = opt('--hover', null);
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
      setTimeout(() => { if (this.pending.has(id)) { this.pending.delete(id); reject(new Error('timeout ' + method)); } }, 40000);
    });
  }
}

const proc = spawn(CHROME, [
  '--headless=new', `--remote-debugging-port=${PORT}`, '--no-first-run',
  '--no-default-browser-check', '--disable-gpu', '--hide-scrollbars',
  '--user-data-dir=/tmp/fpe-chrome-shot', 'about:blank',
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
await cdp.send('Emulation.setDeviceMetricsOverride', {
  width, height, deviceScaleFactor: 1, mobile: flag('--mobile'),
}, sessionId);

await cdp.send('Page.navigate', { url }, sessionId);
await new Promise((r) => { const t = setTimeout(r, 15000); cdp.on('Page.loadEventFired', () => { clearTimeout(t); r(); }); });
await sleep(wait);

/* Lazy-loading : on parcourt la page puis on revient */
await cdp.send('Runtime.evaluate', {
  expression: `(async () => {
    document.documentElement.style.scrollBehavior='auto';
    for (let y = 0; y < document.body.scrollHeight; y += Math.round(innerHeight*0.8)) { scrollTo(0,y); await new Promise(r=>setTimeout(r,70)); }
    scrollTo(0, ${scroll});
    await new Promise(r=>setTimeout(r,300));
    return true; })()`, awaitPromise: true,
}, sessionId);

if (jsCode) {
  const r = await cdp.send('Runtime.evaluate', {
    expression: `(async () => { ${jsCode} })()`,
    awaitPromise: true, returnByValue: true,
  }, sessionId);
  if (r.exceptionDetails) console.error('⚠️  JS :', r.exceptionDetails.exception?.description?.split('\n')[0]);
  await sleep(600);
}
if (navAfter) {
  await cdp.send('Page.navigate', { url: navAfter }, sessionId);
  await new Promise((r) => { const t = setTimeout(r, 15000); cdp.on('Page.loadEventFired', () => { clearTimeout(t); r(); }); });
  await sleep(wait);
  await cdp.send('Runtime.evaluate', {
    expression: "document.documentElement.style.scrollBehavior='auto'; scrollTo(0, " + scroll + ");", awaitPromise: true,
  }, sessionId);
  await sleep(400);
}
if (hoverSel) {
  await cdp.send('Runtime.evaluate', {
    expression: `(() => { const e=document.querySelector(${JSON.stringify(hoverSel)}); if(!e) return 'absent';
      const r=e.getBoundingClientRect();
      e.dispatchEvent(new MouseEvent('mouseover',{bubbles:true,clientX:r.x+r.width/2,clientY:r.y+r.height/2}));
      e.classList.add('hover'); return 'ok'; })()`,
  }, sessionId);
  await sleep(400);
}
if (clickSel) {
  const r = await cdp.send('Runtime.evaluate', {
    expression: `(() => { const e=document.querySelector(${JSON.stringify(clickSel)}); if(!e) return 'absent'; e.click(); return 'ok'; })()`,
    returnByValue: true,
  }, sessionId);
  if (r.result.value === 'absent') console.error(`⚠️  sélecteur introuvable : ${clickSel}`);
  await sleep(1000);
}

const params = { format: 'png' };
if (clipArg) {
  const [x, y, w, h] = clipArg.split(',').map(Number);
  params.clip = { x, y, width: w, height: h, scale: 1 };
  // Sans --full, on reste sur la surface du viewport : indispensable pour que
  // les éléments `position: fixed` (le header) soient rendus à leur place.
  if (flag('--full')) params.captureBeyondViewport = true;
} else if (flag('--full')) {
  params.captureBeyondViewport = true;
}
const shot = await cdp.send('Page.captureScreenshot', params, sessionId);
writeFileSync(out, Buffer.from(shot.data, 'base64'));
console.log(`✔ ${out} (${width}×${height}${clipArg ? `, clip ${clipArg}` : flag('--full') ? ', pleine page' : ''})`);

await cdp.send('Target.closeTarget', { targetId });
proc.kill();
process.exit(0);
