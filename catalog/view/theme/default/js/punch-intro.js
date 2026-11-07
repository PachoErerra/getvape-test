/* Punch × Vapors «Оксана» — інтро-анімація на головній (тест).
   Флакон викочується, кришка відкручується, жижа виливається і заповнює пів екрана. */
(function () {
  if (window.__punchIntro) return; window.__punchIntro = true;
  /* Тимчасово: до цього часу інтро показується на кожній сторінці при кожному відкритті */
  var ALWAYS_UNTIL = Date.parse('2026-11-07T08:29:09Z');
  var always = Date.now() < ALWAYS_UNTIL;
  if (!always && !document.getElementById('common-home')) return;
  var force = always || /[?&#]punch\b/.test(location.href);
  try { if (!force && sessionStorage.getItem('punchShown')) return; sessionStorage.setItem('punchShown', '1'); } catch (e) {}
  if (!force && window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  var IMG = (document.querySelector('base') ? document.querySelector('base').href : '/') + 'catalog/view/theme/default/image/vt/';
  /* Справжнє фото флакона Vapors X Punch Oksana 30ml, розрізане на кришку і флакон.
     Під кришкою — горлечко з різьбою і довгий носик (як у флакона 30 мл). viewBox = пікселі фото 352×1000. */
  var BOTTLE = '' +
  '<svg viewBox="0 0 352 1000" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="overflow:visible">' +
  '<defs>' +
    '<linearGradient id="pnNoz" x1="0" x2="1"><stop offset="0" stop-color="#cfd3d6" stop-opacity=".75"/><stop offset=".22" stop-color="#ffffff" stop-opacity=".95"/><stop offset=".4" stop-color="#e9eef0" stop-opacity=".7"/><stop offset=".8" stop-color="#b8c0c4" stop-opacity=".6"/><stop offset="1" stop-color="#8f989d" stop-opacity=".8"/></linearGradient>' +
    '<linearGradient id="pnNeck" x1="0" x2="1"><stop offset="0" stop-color="#050505"/><stop offset=".25" stop-color="#3b3b3b"/><stop offset=".4" stop-color="#151515"/><stop offset="1" stop-color="#000"/></linearGradient>' +
    '<linearGradient id="pnJuice" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffe9a0" stop-opacity=".0"/><stop offset="1" stop-color="#f3cf5f" stop-opacity=".55"/></linearGradient>' +
    '<pattern id="pnRibs" width="9" height="20" patternUnits="userSpaceOnUse"><rect x="0" width="3" height="20" fill="#000" fill-opacity=".35"/><rect x="4" width="1.5" height="20" fill="#fff" fill-opacity=".10"/></pattern>' +
    '<linearGradient id="pnShine" x1="0" x2="1"><stop offset="0" stop-color="#fff" stop-opacity="0"/><stop offset=".5" stop-color="#fff" stop-opacity=".28"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></linearGradient>' +
    '<clipPath id="pnCapClip"><path d="M167 0h18l19 20 24 40 40 60 56 50 22 15v235H4V185l24-15 56-50 40-60 24-40z"/></clipPath>' +
  '</defs>' +
  '<ellipse cx="176" cy="1000" rx="165" ry="18" fill="#000" opacity=".22"/>' +
  /* горлечко з різьбою */
  '<rect x="104" y="318" width="144" height="110" rx="10" fill="url(#pnNeck)"/>' +
  '<path d="M104 340h144M104 362h144M104 384h144M104 406h144" stroke="#fff" stroke-opacity=".12" stroke-width="3"/>' +
  '<rect x="94" y="300" width="164" height="24" rx="8" fill="url(#pnNeck)"/>' +
  /* носик */
  '<path d="M128 302 C132 230 156 120 166 40 L170 12 h12 l4 28 C196 120 220 230 224 302 Z" fill="url(#pnNoz)" stroke="#9aa3a8" stroke-opacity=".6" stroke-width="1.5"/>' +
  '<path d="M146 300 C150 230 162 130 169 50 L171 30 h10 l2 20 C190 130 202 230 206 300 Z" fill="url(#pnJuice)"/>' +
  '<path d="M140 296 C146 220 160 120 168 46" stroke="#fff" stroke-width="5" stroke-linecap="round" stroke-opacity=".75" fill="none"/>' +
  '<ellipse cx="176" cy="12" rx="6" ry="2.5" fill="#6b7276"/>' +
  /* флакон */
  '<image href="' + IMG + 'pn-body.png" xlink:href="' + IMG + 'pn-body.png" x="0" y="420" width="352" height="580"/>' +
  /* кришка (окремо — відкручується) */
  '<g id="pnCap">' +
    '<image href="' + IMG + 'pn-cap.png" xlink:href="' + IMG + 'pn-cap.png" x="0" y="0" width="352" height="420"/>' +
    '<g clip-path="url(#pnCapClip)">' +
      '<rect id="pnRibRect" x="0" y="185" width="352" height="233" fill="url(#pnRibs)" opacity="0"/>' +
      '<rect id="pnShineRect" x="-120" y="0" width="90" height="420" fill="url(#pnShine)" opacity="0"/>' +
    '</g>' +
  '</g>' +
  '<circle id="pnTip" cx="176" cy="12" r="1" fill="none"/>' +
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
    var bottle = root.querySelector('#pnBottle'), cap = root.querySelector('#pnCap'), ribRect = root.querySelector('#pnRibRect'), shine = root.querySelector('#pnShineRect');
    var ribPat = root.querySelector('#pnRibs'), tip = root.querySelector('#pnTip');
    var W, H, dpr = Math.min(window.devicePixelRatio || 1, 2), BH, BW;
    var COLS, cw, hgt = [], vel = [], level = 0, target = 0, drops = [], bubbles = [];
    function size() {
      W = innerWidth; H = innerHeight; cv.width = W * dpr; cv.height = H * dpr; ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      BH = Math.min(H * 0.96, W * 1.3); BW = BH * 352 / 1000;
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
      var turning = t >= 1.7 && t < 3.4;
      if (turning) { var u = (t - 1.7) / 1.7; ribX = u * 160; capY = -eio(u) * 70; capR = Math.sin(u * 34) * 1.2; }
      if (t >= 3.4) { var f2 = Math.min(1, (t - 3.4) / .7); capY = -70 - eio(f2) * H * 1.3 / (BH / 1000); capX = eio(f2) * 420; capR = eio(f2) * 240; capO = 1 - f2; }
      var tilt = 0;
      if (t >= 3.6 && t < 8.6) tilt = -118 * eio((t - 3.6) / 1.1);
      if (t >= 8.6) tilt = -118 * (1 - eio((t - 8.6) / .9));
      var sc = 1;
      if (t >= 3.6) { var pk = eio(Math.min(1, (t - 3.6) / 1.1)) * (t >= 8.6 ? 1 - eio((t - 8.6) / .9) : 1); x = cx + pk * W * 0.06; y = cy - pk * H * 0.3; sc = 1 - pk * 0.22; }
      if (t >= 9.3) { var ex = eio((t - 9.3) / 1.1); x = cx + ex * (W * .6 + BW); y = cy + ex * 60; rot = ex * 120; }
      bottle.style.transform = 'translate(' + (x - BW / 2) + 'px,' + (y - BH / 2) + 'px) rotate(' + (rot + tilt) + 'deg) scale(' + sc + ')';
      cap.setAttribute('transform', 'translate(' + capX + ' ' + capY + ') rotate(' + capR + ' 176 210)');
      cap.style.opacity = capO;
      ribPat.setAttribute('patternTransform', 'translate(' + ribX + ' 0)');
      ribRect.setAttribute('opacity', turning ? .9 : 0);
      shine.setAttribute('opacity', turning ? 1 : 0);
      if (turning) shine.setAttribute('x', -120 + ((ribX * 3) % 560));

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
