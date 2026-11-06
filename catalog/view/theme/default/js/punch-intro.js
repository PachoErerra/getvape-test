/* Punch × Vapors «Оксана» — інтро-анімація на головній (тест).
   Флакон викочується, кришка відкручується, жижа виливається і заповнює пів екрана. */
(function () {
  if (window.__punchIntro) return; window.__punchIntro = true;
  var force = /[?&#]punch\b/.test(location.href);
  try { if (!force && sessionStorage.getItem('punchShown')) return; sessionStorage.setItem('punchShown', '1'); } catch (e) {}
  if (!force && window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  var BOTTLE = '' +
  '<svg viewBox="0 0 200 520" xmlns="http://www.w3.org/2000/svg" style="overflow:visible">' +
  '<defs>' +
    '<linearGradient id="pnCyl" x1="0" x2="1"><stop offset="0" stop-color="#000" stop-opacity=".55"/><stop offset=".12" stop-color="#000" stop-opacity=".15"/><stop offset=".22" stop-color="#fff" stop-opacity=".38"/><stop offset=".3" stop-color="#fff" stop-opacity=".06"/><stop offset=".72" stop-color="#000" stop-opacity="0"/><stop offset=".9" stop-color="#000" stop-opacity=".32"/><stop offset="1" stop-color="#000" stop-opacity=".6"/></linearGradient>' +
    '<linearGradient id="pnCapG" x1="0" x2="1"><stop offset="0" stop-color="#050505"/><stop offset=".25" stop-color="#3a3a3a"/><stop offset=".35" stop-color="#151515"/><stop offset=".8" stop-color="#0a0a0a"/><stop offset="1" stop-color="#000"/></linearGradient>' +
    '<pattern id="pnRibs" width="7" height="10" patternUnits="userSpaceOnUse"><rect width="7" height="10" fill="none"/><rect x="0" width="3.2" height="10" fill="#fff" fill-opacity=".09"/></pattern>' +
    '<linearGradient id="pnLab" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#ffd23f"/><stop offset=".45" stop-color="#ff9a2e"/><stop offset="1" stop-color="#ff5a3c"/></linearGradient>' +
    '<radialGradient id="pnPeach" cx=".38" cy=".35" r=".7"><stop offset="0" stop-color="#ffe08a"/><stop offset=".45" stop-color="#ffab45"/><stop offset="1" stop-color="#e2452f"/></radialGradient>' +
    '<clipPath id="pnBodyClip"><rect x="15" y="168" width="170" height="350" rx="24"/></clipPath>' +
    '<linearGradient id="pnShoulder" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff6c8" stop-opacity=".55"/><stop offset="1" stop-color="#ffd76a" stop-opacity=".75"/></linearGradient>' +
  '</defs>' +
  '<ellipse cx="100" cy="520" rx="92" ry="10" fill="#000" opacity=".25" class="pn-shadow"/>' +
  /* тіло флакона */
  '<g clip-path="url(#pnBodyClip)">' +
    '<rect x="15" y="168" width="170" height="350" fill="url(#pnShoulder)"/>' +
    '<rect x="15" y="196" width="170" height="322" fill="url(#pnLab)"/>' +
    /* візерунок етикетки: хвилі й плями */
    '<path d="M15 300c40-30 80 20 120-6s50-20 50-20v80c-30 14-60-18-100 6s-70 10-70 10z" fill="#e8344a" opacity=".85"/>' +
    '<path d="M15 420c30-26 70 14 110-8s60-6 60-6v112H15z" fill="#c4213c" opacity=".9"/>' +
    '<circle cx="40" cy="250" r="16" fill="#fff3b0" opacity=".55"/><circle cx="165" cy="400" r="22" fill="#ffe36b" opacity=".5"/><circle cx="60" cy="470" r="10" fill="#ffd23f" opacity=".7"/>' +
    /* персик */
    '<g transform="translate(118 300)"><path d="M0 -30c22 -6 42 10 40 34s-22 40 -42 38S-38 18 -36 0 -18 -26 0 -30z" fill="url(#pnPeach)"/><path d="M0 -30c-6 20 -4 44 0 72" stroke="#c23a24" stroke-width="2" fill="none" opacity=".5"/><path d="M2 -30c8 -14 22 -18 34 -14 -6 12 -20 16 -34 14z" fill="#3fa34d"/><ellipse cx="-14" cy="-10" rx="8" ry="12" fill="#fff" opacity=".35"/></g>' +
    /* текст */
    '<text x="100" y="236" text-anchor="middle" font-family="Brush Script MT,Segoe Script,cursive" font-style="italic" font-weight="700" font-size="40" fill="#fff" stroke="#7a1022" stroke-width="1.2" paint-order="stroke">Vapors</text>' +
    '<text x="100" y="388" text-anchor="middle" font-family="Impact,Arial Black,sans-serif" font-size="46" letter-spacing="1" fill="#fff" stroke="#5a0a1a" stroke-width="5" paint-order="stroke" transform="rotate(-6 100 388)">OKSANA</text>' +
    '<text x="100" y="420" text-anchor="middle" font-family="Arial Black,Arial,sans-serif" font-weight="900" font-size="15" fill="#5a0a1a" letter-spacing="3">PUNCH × VAPORS</text>' +
    '<rect x="70" y="482" width="60" height="20" rx="10" fill="#3fa34d"/><text x="100" y="497" text-anchor="middle" font-family="Arial,sans-serif" font-weight="700" font-size="12" fill="#fff">14 мл</text>' +
    /* циліндричне світло */
    '<rect x="15" y="168" width="170" height="350" fill="url(#pnCyl)"/>' +
    '<rect x="44" y="176" width="10" height="330" rx="5" fill="#fff" opacity=".28"/>' +
  '</g>' +
  '<rect x="15" y="168" width="170" height="350" rx="24" fill="none" stroke="#000" stroke-opacity=".25"/>' +
  /* горлечко */
  '<rect x="58" y="146" width="84" height="26" rx="6" fill="#1a1a1a"/><rect x="58" y="146" width="84" height="26" rx="6" fill="url(#pnCyl)"/>' +
  /* кришка (окремо — відкручується) */
  '<g id="pnCap">' +
    '<path d="M86 4c0-3 3-4 7-4h14c4 0 7 1 7 4l6 52H80z" fill="url(#pnCapG)"/>' +
    '<rect x="88" y="6" width="5" height="46" rx="2.5" fill="#fff" opacity=".18"/>' +
    '<rect x="52" y="54" width="96" height="98" rx="10" fill="url(#pnCapG)"/>' +
    '<rect id="pnRibRect" x="52" y="58" width="96" height="90" rx="8" fill="url(#pnRibs)"/>' +
    '<rect x="60" y="58" width="9" height="90" rx="4.5" fill="#fff" opacity=".2"/>' +
    '<rect x="52" y="54" width="96" height="98" rx="10" fill="none" stroke="#000"/>' +
  '</g>' +
  /* точка носика (куди виливається) */
  '<circle id="pnTip" cx="100" cy="150" r="1" fill="none"/>' +
  '</svg>';

  var css = '' +
  '#pnIntro{position:fixed;inset:0;z-index:99999;pointer-events:none;overflow:hidden}' +
  '#pnIntro canvas{position:absolute;inset:0;width:100%;height:100%}' +
  '#pnGlass{position:absolute;inset:0;-webkit-backdrop-filter:blur(1.2px) saturate(1.15);backdrop-filter:blur(1.2px) saturate(1.15)}' +
  '#pnBottle{position:absolute;left:0;top:0;transform-origin:50% 62%;will-change:transform;filter:drop-shadow(0 30px 40px rgba(0,0,0,.35))}' +
  '#pnBottle svg{display:block;width:100%;height:100%}' +
  '#pnSkip{position:fixed;right:18px;bottom:18px;z-index:100000;pointer-events:auto;padding:10px 16px;border:0;border-radius:999px;background:rgba(10,8,16,.75);color:#fff;font:600 14px/1 Arial,sans-serif;cursor:pointer;backdrop-filter:blur(6px)}' +
  '#pnSkip:hover{background:#ff2fa0}';

  function start() {
    var st = document.createElement('style'); st.textContent = css; document.head.appendChild(st);
    var root = document.createElement('div'); root.id = 'pnIntro';
    root.innerHTML = '<div id="pnGlass"></div><canvas></canvas><div id="pnBottle">' + BOTTLE + '</div>';
    document.body.appendChild(root);
    var skip = document.createElement('button'); skip.id = 'pnSkip'; skip.textContent = 'Пропустити ✕'; document.body.appendChild(skip);

    var cv = root.querySelector('canvas'), ctx = cv.getContext('2d'), glass = root.querySelector('#pnGlass');
    var bottle = root.querySelector('#pnBottle'), cap = root.querySelector('#pnCap'), rib = root.querySelector('#pnRibs rect + rect') || null;
    var ribPat = root.querySelector('#pnRibs'), tip = root.querySelector('#pnTip');
    var W, H, dpr = Math.min(window.devicePixelRatio || 1, 2), BH, BW;
    var COLS, cw, hgt = [], vel = [], level = 0, target = 0, drops = [], bubbles = [];
    function size() {
      W = innerWidth; H = innerHeight; cv.width = W * dpr; cv.height = H * dpr; ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      BH = Math.min(H * 0.96, W * 1.3); BW = BH * 200 / 520;
      bottle.style.width = BW + 'px'; bottle.style.height = BH + 'px';
      cw = 8; COLS = Math.ceil(W / cw) + 2; while (hgt.length < COLS) { hgt.push(0); vel.push(0); }
    }
    size(); addEventListener('resize', size);

    var t0 = performance.now(), done = false, fade = 1;
    function ease(x) { return x < 0 ? 0 : x > 1 ? 1 : 1 - Math.pow(1 - x, 3); }
    function eio(x) { x = Math.max(0, Math.min(1, x)); return x < .5 ? 4 * x * x * x : 1 - Math.pow(-2 * x + 2, 3) / 2; }
    function back(x) { x = Math.max(0, Math.min(1, x)); var c = 1.5; return 1 + (c + 1) * Math.pow(x - 1, 3) + c * Math.pow(x - 1, 2); }
    function lerp(a, b, k) { return a + (b - a) * k; }

    function finish() { if (done) return; done = true; skip.remove(); var s = performance.now(); (function f() { var k = (performance.now() - s) / 500; root.style.opacity = 1 - k; if (k < 1) requestAnimationFrame(f); else root.remove(); })(); }
    skip.onclick = finish;
    addEventListener('keydown', function (e) { if (e.key === 'Escape') finish(); });

    function surfY(x) { var i = Math.max(0, Math.min(COLS - 1, Math.round(x / cw))); return H - level + hgt[i]; }
    function splash(x, power) {
      var i = Math.round(x / cw);
      for (var k = -2; k <= 2; k++) if (vel[i + k] !== undefined) vel[i + k] += power * (1 - Math.abs(k) / 3);
      for (var n = 0; n < 3; n++) drops.push({ x: x, y: surfY(x), vx: (Math.random() - .5) * 420, vy: -Math.random() * 520 - 120, r: Math.random() * 3 + 1.5, life: 1 });
      if (Math.random() < .5) bubbles.push({ x: x + (Math.random() - .5) * 40, y: H - 4, r: Math.random() * 5 + 2, s: Math.random() * 40 + 30 });
    }

    var last = performance.now();
    function frame(now) {
      if (done && !root.isConnected) return;
      var t = (now - t0) / 1000, dt = Math.min(.033, (now - last) / 1000); last = now;
      /* ---- флакон ---- */
      var cx = W * 0.6, cy = H * 0.5, x, y, rot, capY = 0, capX = 0, capR = 0, capO = 1, ribX = 0;
      if (t < 1.7) { var k = back(t / 1.7); x = lerp(W + BW * 1.2, cx, k); rot = lerp(560, 0, ease(t / 1.7)); y = cy - Math.abs(Math.sin(t * 5)) * 40 * (1 - t / 1.7); }
      else { x = cx; y = cy; rot = 0; }
      if (t >= 1.7 && t < 3.4) { var u = (t - 1.7) / 1.7; ribX = u * 70; capY = -eio(u) * 30; capR = Math.sin(u * 30) * 1.5; }
      if (t >= 3.4) { var f2 = Math.min(1, (t - 3.4) / .7); capY = -30 - eio(f2) * H * 1.2 / (BH / 520); capX = eio(f2) * 260; capR = eio(f2) * 240; capO = 1 - f2; }
      var tilt = 0;
      if (t >= 3.6 && t < 8.6) tilt = -128 * eio((t - 3.6) / 1.1);
      if (t >= 8.6) tilt = -128 * (1 - eio((t - 8.6) / .9));
      if (t >= 3.6) { var pk = eio(Math.min(1, (t - 3.6) / 1.1)) * (t >= 8.6 ? 1 - eio((t - 8.6) / .9) : 1); x = cx + pk * W * 0.08; y = cy - pk * H * 0.12; }
      if (t >= 9.3) { var ex = eio((t - 9.3) / 1.1); x = cx + ex * (W * .6 + BW); y = cy + ex * 60; rot = ex * 120; }
      bottle.style.transform = 'translate(' + (x - BW / 2) + 'px,' + (y - BH / 2) + 'px) rotate(' + (rot + tilt) + 'deg)';
      cap.setAttribute('transform', 'translate(' + capX + ' ' + capY + ') rotate(' + capR + ' 100 100)');
      cap.style.opacity = capO;
      ribPat.setAttribute('patternTransform', 'translate(' + ribX + ' 0)');

      /* ---- жижа ---- */
      var pouring = t > 4.3 && t < 8.7;
      var flow = pouring ? Math.min(1, (t - 4.3) / .3) * (t > 8.4 ? Math.max(0, (8.7 - t) / .3) : 1) : 0;
      if (pouring) target = H * 0.5 * Math.min(1, (t - 4.3) / 4.1);
      if (t > 11.6) target = Math.max(0, H * 0.5 * (1 - (t - 11.6) / 1.8));
      level += (target - level) * Math.min(1, dt * 3);

      for (var it = 0; it < 2; it++) {
        for (var i = 0; i < COLS; i++) { vel[i] += -0.025 * hgt[i] - 0.02 * vel[i]; hgt[i] += vel[i]; }
        for (var p = 0; p < 2; p++) for (i = 1; i < COLS - 1; i++) { var l = .18 * (hgt[i] - hgt[i - 1]), r = .18 * (hgt[i] - hgt[i + 1]); vel[i - 1] += l; vel[i + 1] += r; }
      }
      for (i = 0; i < COLS; i++) hgt[i] += Math.sin(t * 1.6 + i * .11) * .08 * (level > 2 ? 1 : 0);

      ctx.clearRect(0, 0, W, H);
      /* струмінь */
      if (flow > 0.01) {
        var r0 = tip.getBoundingClientRect(), nx = r0.left + r0.width / 2, ny = r0.top + r0.height / 2;
        var ang = (rot + tilt - 90) * Math.PI / 180, v0 = 260, vx = Math.cos(ang) * v0, vy = Math.sin(ang) * v0, g = 2200;
        var pts = [], tt = 0, px = nx, py = ny;
        while (tt < 2 && py < surfY(px)) { pts.push([px + Math.sin(t * 22 + tt * 40) * 1.2 * tt * 10, py]); tt += .008; px = nx + vx * tt; py = ny + vy * tt + .5 * g * tt * tt; }
        if (pts.length > 1) {
          for (var pass = 0; pass < 3; pass++) {
            ctx.beginPath(); ctx.moveTo(pts[0][0], pts[0][1]);
            for (i = 1; i < pts.length; i++) ctx.lineTo(pts[i][0], pts[i][1]);
            ctx.lineCap = 'round'; ctx.lineJoin = 'round';
            if (pass === 0) { ctx.lineWidth = 26 * flow * BH / 700; ctx.strokeStyle = 'rgba(240,204,90,.30)'; }
            else if (pass === 1) { ctx.lineWidth = 15 * flow * BH / 700; ctx.strokeStyle = 'rgba(255,236,160,.38)'; }
            else { ctx.lineWidth = 2.2; ctx.strokeStyle = 'rgba(255,255,255,.75)'; ctx.translate(-2, 0); }
            ctx.stroke(); ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
          }
          splash(px, 3.5 * flow);
        }
      }
      /* поверхня і тіло рідини */
      if (level > .5) {
        var base = H - level;
        ctx.beginPath(); ctx.moveTo(0, H);
        for (i = 0; i < COLS; i++) ctx.lineTo(i * cw, base + hgt[i]);
        ctx.lineTo(W, H); ctx.closePath();
        var gr = ctx.createLinearGradient(0, base - 20, 0, H);
        gr.addColorStop(0, 'rgba(255,240,165,.16)'); gr.addColorStop(.5, 'rgba(248,220,120,.19)'); gr.addColorStop(1, 'rgba(232,192,72,.28)');
        ctx.fillStyle = gr; ctx.fill();
        /* блики під поверхнею (каустика) */
        ctx.save(); ctx.clip();
        ctx.globalAlpha = .18; ctx.strokeStyle = '#fff'; ctx.lineWidth = 1.4;
        for (var c = 0; c < 7; c++) { ctx.beginPath(); for (var xx = 0; xx <= W; xx += 24) { var yy = base + 20 + c * 26 + Math.sin(xx * .012 + t * (1.2 + c * .2) + c) * 8 + Math.sin(xx * .031 - t * 1.7) * 4; xx ? ctx.lineTo(xx, yy) : ctx.moveTo(xx, yy); } ctx.stroke(); }
        ctx.restore(); ctx.globalAlpha = 1;
        /* лінія поверхні */
        ctx.beginPath(); for (i = 0; i < COLS; i++) { var sy = base + hgt[i]; i ? ctx.lineTo(i * cw, sy) : ctx.moveTo(0, sy); }
        ctx.strokeStyle = 'rgba(255,255,255,.85)'; ctx.lineWidth = 2; ctx.stroke();
        ctx.strokeStyle = 'rgba(255,236,160,.6)'; ctx.lineWidth = 7; ctx.globalAlpha = .35; ctx.stroke(); ctx.globalAlpha = 1;
        /* скло (розмиття того, що під рідиною) */
        var poly = '0px ' + H + 'px'; for (i = 0; i < COLS; i += 3) poly += ',' + (i * cw) + 'px ' + (base + hgt[i]) + 'px'; poly += ',' + W + 'px ' + (base + hgt[COLS - 1]) + 'px,' + W + 'px ' + H + 'px';
        glass.style.clipPath = 'polygon(' + poly + ')';
      } else glass.style.clipPath = 'polygon(0 0,0 0,0 0)';
      /* бульбашки */
      for (i = bubbles.length - 1; i >= 0; i--) { var b = bubbles[i]; b.y -= b.s * dt; b.x += Math.sin(t * 3 + i) * .3; if (b.y < surfY(b.x) + 3) { bubbles.splice(i, 1); continue; }
        ctx.beginPath(); ctx.arc(b.x, b.y, b.r, 0, 6.283); ctx.strokeStyle = 'rgba(255,255,255,.55)'; ctx.lineWidth = 1; ctx.stroke(); ctx.beginPath(); ctx.arc(b.x - b.r * .35, b.y - b.r * .35, b.r * .25, 0, 6.283); ctx.fillStyle = 'rgba(255,255,255,.7)'; ctx.fill(); }
      if (level > 20 && Math.random() < .25) bubbles.push({ x: Math.random() * W, y: H - 2, r: Math.random() * 3 + 1, s: Math.random() * 30 + 15 });
      /* бризки */
      for (i = drops.length - 1; i >= 0; i--) { var d = drops[i]; d.vy += 1600 * dt; d.x += d.vx * dt; d.y += d.vy * dt; if (d.vy > 0 && d.y > surfY(d.x)) { drops.splice(i, 1); continue; }
        ctx.beginPath(); ctx.arc(d.x, d.y, d.r, 0, 6.283); ctx.fillStyle = 'rgba(250,224,130,.6)'; ctx.fill(); ctx.beginPath(); ctx.arc(d.x - d.r * .3, d.y - d.r * .3, d.r * .35, 0, 6.283); ctx.fillStyle = 'rgba(255,255,255,.8)'; ctx.fill(); }

      if (t > 13.6 && !done) finish();
      if (!done || root.isConnected) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }
  if (document.readyState === 'complete') setTimeout(start, 300); else addEventListener('load', function () { setTimeout(start, 300); });
})();
