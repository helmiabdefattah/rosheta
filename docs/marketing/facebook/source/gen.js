const fs = require('fs');
const OUT = process.argv[2] || process.env.OUT_DIR || __dirname;

/* ---------- design tokens ---------- */
const CSS = `
@import url('local.css');
*{margin:0;padding:0;box-sizing:border-box}
:root{
  --ink:#140B29; --purple:#4A2C6B; --indigo:#3E5F9B; --teal:#2FA8B4;
  --mint:#5FD6C8; --cream:#F4F7FB; --slate:#5A6B84; --line:rgba(255,255,255,.14);
}
html,body{background:#fff}
body{-webkit-font-smoothing:antialiased;text-rendering:geometricPrecision}
.frame{position:relative;overflow:hidden;display:flex;flex-direction:column}
/* backgrounds */
.bg-dark{background:
  radial-gradient(900px 640px at 12% 4%, rgba(92,52,133,.95), transparent 62%),
  radial-gradient(860px 760px at 100% 104%, rgba(47,168,180,.80), transparent 62%),
  linear-gradient(140deg,#190E30 0%,#232a5e 48%,#0C4A5F 100%);
  color:#fff}
.bg-light{background:
  radial-gradient(760px 560px at 105% -10%, rgba(47,168,180,.20), transparent 60%),
  radial-gradient(700px 600px at -10% 108%, rgba(74,44,107,.16), transparent 60%),
  #F4F7FB; color:var(--ink)}
.grid-ov{position:absolute;inset:0;background-image:radial-gradient(rgba(255,255,255,.085) 1.6px,transparent 1.7px);background-size:38px 38px;
  -webkit-mask-image:radial-gradient(120% 100% at 50% 0%,#000 30%,transparent 78%)}
.grid-ov.d{background-image:radial-gradient(rgba(74,44,107,.13) 1.6px,transparent 1.7px)}
.mark-wm{position:absolute;opacity:.07;filter:brightness(0) invert(1)}
.mark-wm.d{filter:none;opacity:.05}
/* lockup */
.lockup{display:flex;align-items:center;gap:22px}
.lockup .badge{background:#fff;border-radius:26px;display:flex;align-items:center;justify-content:center;
  box-shadow:0 16px 40px rgba(10,6,25,.22)}
.lockup .wm{font-family:Poppins;font-weight:700;letter-spacing:-.5px;line-height:1}
.lockup .wm small{display:block;font-family:Inter;font-weight:500;letter-spacing:5px;opacity:.72;text-transform:uppercase}
/* chips */
.chip{display:inline-flex;align-items:center;gap:14px;border-radius:999px;font-family:Inter;font-weight:600}
.chip.on-dark{background:rgba(95,214,200,.15);border:1.5px solid rgba(95,214,200,.42);color:#9DF0E4}
.chip.on-light{background:rgba(47,168,180,.12);border:1.5px solid rgba(47,168,180,.35);color:#1B7E88}
.dot{width:12px;height:12px;border-radius:50%;background:var(--mint)}
h1{font-family:Poppins;font-weight:800;letter-spacing:-1.2px;word-spacing:6px;line-height:1.1}
.sub{font-family:Inter;font-weight:400;line-height:1.5;opacity:.86;text-wrap:balance}
.bullets{display:flex;flex-direction:column}
.bul{display:flex;align-items:center;font-family:Inter;font-weight:500}
.tick{flex:none;display:flex;align-items:center;justify-content:center;border-radius:50%}
.tick.on-dark{background:rgba(95,214,200,.18);border:1.5px solid rgba(95,214,200,.5)}
.tick.on-light{background:rgba(47,168,180,.14);border:1.5px solid rgba(47,168,180,.4)}
.foot{display:flex;align-items:center;justify-content:space-between;font-family:Inter;font-weight:600}
.url{font-family:Inter;font-weight:700;letter-spacing:.2px}
.iconbox{display:flex;align-items:center;justify-content:center;border-radius:40px;
  background:linear-gradient(145deg,rgba(95,214,200,.22),rgba(74,44,107,.28));border:1.5px solid rgba(255,255,255,.18)}
.iconbox.on-light{background:linear-gradient(145deg,#fff,#EAF4F6);border:1.5px solid rgba(47,168,180,.26);
  box-shadow:0 22px 50px rgba(20,11,41,.10)}
/* arabic */
[dir=rtl] h1{font-family:Cairo;font-weight:900;letter-spacing:0;word-spacing:normal;line-height:1.42}
[dir=rtl] .sub{font-family:Tajawal;font-weight:400;line-height:1.75}
[dir=rtl] .bul,[dir=rtl] .chip,[dir=rtl] .foot{font-family:Tajawal;font-weight:700}
[dir=rtl] .lockup .wm{font-family:Poppins}
.ltr{direction:ltr;unicode-bidi:isolate}
`;

const LOGO_W = (h) => `<img src="mo-logo.png" style="height:${h}px" alt="">`;
const LOGO_WHITE = (h) => `<img src="mo-logo.png" style="height:${h}px;filter:brightness(0) invert(1)" alt="">`;

function lockup(scale, dark, ar) {
  const s = (n) => Math.round(n * scale);
  return `<div class="lockup" dir="ltr">
    <div class="badge" style="width:${s(96)}px;height:${s(96)}px;border-radius:${s(28)}px">${LOGO_W(s(58))}</div>
    <div class="wm" style="font-size:${s(40)}px;color:${dark ? '#fff' : 'var(--ink)'}">mostashfaOn
      <small style="font-size:${s(15)}px;margin-top:${s(7)}px">connecting health</small></div>
  </div>`;
}

/* ---------- icons (stroke svg) ---------- */
function icon(name, size, color) {
  const c = color || '#5FD6C8';
  const A = `fill="none" stroke="${c}" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"`;
  const P = {
    calendar: `<rect x="10" y="20" width="80" height="70" rx="12" ${A}/><path d="M10 40h80M32 10v18M68 10v18" ${A}/><path d="M33 58h10M33 74h10M56 58h11M56 74h11" ${A}/>`,
    bell: `<path d="M50 14a24 24 0 0 1 24 24v18l9 14H17l9-14V38a24 24 0 0 1 24-24Z" ${A}/><path d="M39 78a11 11 0 0 0 22 0" ${A}/><path d="M50 8v6" ${A}/>`,
    phone: `<rect x="24" y="8" width="52" height="84" rx="12" ${A}/><path d="M42 20h16" ${A}/><path d="M38 48h24M38 62h16" ${A}/>`,
    rx: `<rect x="16" y="10" width="68" height="80" rx="12" ${A}/><path d="M34 32h18a10 10 0 0 1 0 20H34V32Zm0 20 22 26M56 56l18 22" ${A}/>`,
    invoice: `<path d="M22 10h56v80l-11-8-11 8-11-8-12 8-11-8V10Z" ${A}/><path d="M38 34h24M38 50h24M38 66h14" ${A}/>`,
    screen: `<rect x="8" y="16" width="84" height="56" rx="10" ${A}/><path d="M36 86h28M50 72v14" ${A}/><path d="M24 34h30M24 48h20" ${A}/><circle cx="74" cy="42" r="8" ${A}/>`,
    desk: `<rect x="10" y="44" width="80" height="14" rx="6" ${A}/><path d="M18 58v30M82 58v30" ${A}/><circle cx="50" cy="24" r="13" ${A}/><path d="M28 44c4-11 12-16 22-16s18 5 22 16" ${A}/>`,
    history: `<path d="M20 50a30 30 0 1 0 9-21" ${A}/><path d="M18 14v16h16" ${A}/><path d="M50 32v20l14 9" ${A}/>`,
    globe: `<circle cx="50" cy="50" r="38" ${A}/><path d="M12 50h76M50 12c11 12 11 64 0 76M50 12c-11 12-11 64 0 76" ${A}/>`,
    stack: `<path d="M50 12 88 32 50 52 12 32 50 12Z" ${A}/><path d="m12 50 38 20 38-20M12 68l38 20 38-20" ${A}/>`,
    spark: `<path d="M50 10c4 20 16 32 36 36-20 4-32 16-36 36-4-20-16-32-36-36 20-4 32-16 36-36Z" ${A}/><path d="M20 12c1.6 7 4 9.4 11 11-7 1.6-9.4 4-11 11-1.6-7-4-9.4-11-11 7-1.6 9.4-4 11-11" ${A}/>`,
    shield: `<path d="M50 10 84 24v26c0 22-14 33-34 40-20-7-34-18-34-40V24L50 10Z" ${A}/><path d="M34 50l11 11 21-22" ${A}/>`,
  }[name];
  return `<svg viewBox="0 0 100 100" width="${size}" height="${size}">${P}</svg>`;
}

/* ---------- post template (1080x1080) ---------- */
function post(o) {
  const ar = o.lang === 'ar';
  const dark = o.theme === 'dark';
  const tickC = dark ? '#8EE9DC' : '#2FA8B4';
  const bullets = o.bullets.map(b => `
    <div class="bul" style="font-size:31px;gap:20px;margin-bottom:20px;color:${dark ? 'rgba(255,255,255,.92)' : '#27354A'}">
      <span class="tick ${dark ? 'on-dark' : 'on-light'}" style="width:44px;height:44px">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="${tickC}" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12.5 9.5 18 20 6.5"/></svg>
      </span><span>${b}</span></div>`).join('');

  return `<!doctype html><html dir="${ar ? 'rtl' : 'ltr'}" lang="${o.lang}"><meta charset="utf-8"><style>${CSS}</style>
<body><div class="frame ${dark ? 'bg-dark' : 'bg-light'}" style="width:1080px;height:1080px;padding:66px 78px 58px">
  <div class="grid-ov ${dark ? '' : 'd'}"></div>
  <img class="mark-wm ${dark ? '' : 'd'}" src="mo-logo.png" style="height:640px;${ar ? 'left:-150px' : 'right:-150px'};bottom:-120px">
  <div class="inner" style="position:relative;display:flex;flex-direction:column;height:100%;overflow:hidden">
    <div style="flex:0 0 auto;display:flex;align-items:flex-start;justify-content:space-between">
      ${lockup(1, dark, ar)}
      <div class="iconbox ${dark ? '' : 'on-light'}" style="width:124px;height:124px">${icon(o.icon, 66, dark ? '#8EE9DC' : '#2FA8B4')}</div>
    </div>
    <div class="head" style="flex:0 0 auto;margin-top:${ar ? 48 : 54}px;padding-bottom:44px">
      <span class="chip ${dark ? 'on-dark' : 'on-light'}" style="font-size:24px;letter-spacing:${ar ? 0 : '2.6px'};padding:15px 28px">
        <span class="dot" style="background:${dark ? '#5FD6C8' : '#2FA8B4'}"></span>${ar ? o.eyebrow : o.eyebrow.toUpperCase()}</span>
      <h1 style="font-size:${o.size || (ar ? 68 : 73)}px;margin-top:${ar ? 26 : 30}px;color:${dark ? '#fff' : 'var(--ink)'}">${o.title}</h1>
      <p class="sub" style="font-size:${ar ? 33 : 31}px;margin-top:24px;max-width:${ar ? 880 : 840}px;color:${dark ? 'rgba(255,255,255,.84)' : '#46576F'}">${o.sub}</p>
    </div>
    <div style="flex:1 1 auto;min-height:26px"></div>
    <div class="bullets" style="flex:0 0 auto;margin-bottom:32px">${bullets}</div>
    <div class="foot" style="flex:0 0 auto;border-top:1.5px solid ${dark ? 'var(--line)' : 'rgba(20,11,41,.10)'};padding-top:30px;font-size:30px;color:${dark ? 'rgba(255,255,255,.9)' : '#46576F'}">
      <span class="url ltr" style="color:${dark ? '#8EE9DC' : '#1B7E88'}">dev.mostashfaon.com</span>
      <span>${o.cta}</span>
    </div>
  </div></div>
<script>
document.fonts.ready.then(function(){
  var inner=document.querySelector('.inner');
  if(!inner) return;
  var h1=inner.querySelector('h1'), sub=inner.querySelector('.sub'),
      buls=[].slice.call(inner.querySelectorAll('.bul'));
  var px=function(el){return parseFloat(getComputedStyle(el).fontSize)};
  var guard=0;
  while(inner.scrollHeight>inner.clientHeight+1 && guard++<40){
    h1.style.fontSize=(px(h1)-3)+'px';
    sub.style.fontSize=(px(sub)-1.2)+'px';
    if(guard%2===0) buls.forEach(function(b){b.style.fontSize=(px(b)-1)+'px'});
  }
  document.documentElement.setAttribute('data-fit',guard);
});
</script>
</body></html>`;
}

/* ---------- profile picture (1000x1000) ---------- */
function profile() {
  return `<!doctype html><html><meta charset="utf-8"><style>${CSS}</style><body>
  <div class="frame bg-dark" style="width:1000px;height:1000px;align-items:center;justify-content:center">
    <div class="grid-ov"></div>
    <div style="position:absolute;width:760px;height:760px;border-radius:50%;border:2px solid rgba(255,255,255,.14)"></div>
    <div style="position:absolute;width:620px;height:620px;border-radius:50%;border:2px solid rgba(95,214,200,.22)"></div>
    <div style="position:relative;display:flex;flex-direction:column;align-items:center">
      <div style="background:#fff;width:430px;height:430px;border-radius:120px;display:flex;align-items:center;justify-content:center;box-shadow:0 40px 90px rgba(8,4,20,.4)">
        ${LOGO_W(280)}
      </div>
      <div style="font-family:Poppins;font-weight:800;font-size:76px;letter-spacing:-1.6px;margin-top:52px">mostashfaOn</div>
      <div style="font-family:Inter;font-weight:500;font-size:24px;letter-spacing:8px;text-transform:uppercase;opacity:.75;margin-top:14px">connecting health</div>
    </div>
  </div></body></html>`;
}

/* ---------- cover (1640x856) ---------- */
function cover(o) {
  const ar = o.lang === 'ar';
  const pills = o.pills.map(p => `<span class="chip on-dark" style="font-size:25px;padding:16px 30px;gap:12px"><span class="dot"></span>${p}</span>`).join('');
  return `<!doctype html><html dir="${ar ? 'rtl' : 'ltr'}" lang="${o.lang}"><meta charset="utf-8"><style>${CSS}</style><body>
  <div class="frame bg-dark" style="width:1640px;height:856px;align-items:center;justify-content:center;text-align:center;padding:0 240px">
    <div class="grid-ov"></div>
    <img class="mark-wm" src="mo-logo.png" style="height:520px;left:60px;top:-60px">
    <img class="mark-wm" src="mo-logo.png" style="height:520px;right:60px;bottom:-60px">
    <div style="position:relative;display:flex;flex-direction:column;align-items:center;margin-top:-22px">
      <div style="display:flex;align-items:center;gap:20px">
        <div style="background:#fff;width:84px;height:84px;border-radius:26px;display:flex;align-items:center;justify-content:center">${LOGO_W(52)}</div>
        <div style="font-family:Poppins;font-weight:800;font-size:44px;letter-spacing:-1px">mostashfaOn</div>
      </div>
      <h1 style="font-size:${ar ? 62 : 70}px;margin-top:34px;max-width:1140px">${o.title}</h1>
      <p class="sub" style="font-size:${ar ? 31 : 31}px;margin-top:22px;max-width:1000px">${o.sub}</p>
      <div style="display:flex;gap:16px;flex-wrap:wrap;justify-content:center;margin-top:34px">${pills}</div>
      <div style="margin-top:36px;display:flex;align-items:center;gap:18px;font-family:Inter;font-weight:700;font-size:30px">
        <span style="opacity:.8">${o.cta}</span>
        <span class="url ltr" style="color:#8EE9DC">dev.mostashfaon.com</span>
      </div>
    </div>
  </div></body></html>`;
}

module.exports = { post, profile, cover, CSS };
if (require.main === module) {
  const specs = require('./content.js');
  fs.writeFileSync(`${OUT}/profile.html`, profile());
  specs.covers.forEach(c => fs.writeFileSync(`${OUT}/${c.file}.html`, cover(c)));
  specs.posts.forEach(p => fs.writeFileSync(`${OUT}/${p.file}.html`, post(p)));
  console.log('wrote', 1 + specs.covers.length + specs.posts.length, 'html files');
}
