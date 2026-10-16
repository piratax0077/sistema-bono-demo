<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tótem Medichile · Sistema de Bonos Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap">
    @vite('resources/js/flatpickr.js')
    <style>
        :root{
            --k-blue:#1848a1;--k-blue-2:#1c6fb8;--k-teal:#31bebe;--k-teal-ink:#0b7f86;--k-ink:#13233a;--k-muted:#5a6b82;--k-line:#d5e1f1;--k-soft:#eef4fb;--k-ok:#0e8577;--k-ok-2:#12a08e;
            --k-h:calc(100vh - var(--k-offset,56px));
            --t-pad:clamp(8px,1.4vh,16px);
            --t-side:clamp(9px,1.4vh,16px);
            --t-glass:clamp(6px,.9vh,11px);
            --t-cam:clamp(5px,.8vh,8px);
            --t-panel:clamp(32px,5vh,56px);
            --t-neck:clamp(12px,3vh,40px);
            --t-base:clamp(9px,1.5vh,18px);
            --t-sh:calc(var(--k-h) - var(--t-pad) - var(--t-side) - var(--t-panel) - var(--t-neck) - var(--t-base) - 2 * var(--t-glass) - var(--t-cam));
            --t-sw:min(calc(var(--t-sh) * 2.6),1880px,calc(100vw - 16px - 2 * var(--t-glass) - 2 * var(--t-side)));
            --t-unit:calc(min(var(--t-sw),var(--t-sh)) / 100);
            --su:var(--t-unit);
        }
        @supports (height:100dvh){:root{--k-h:calc(100dvh - var(--k-offset,56px))}}
        *{box-sizing:border-box}
        html,body{font-family:'Manrope',sans-serif}
        body{margin:0;min-height:100vh;color:var(--k-ink);background:radial-gradient(1000px 560px at 50% -10%,#f6f9fe 0,transparent 70%),linear-gradient(180deg,#e7eef8 0%,#e0e8f3 72%,#cfd9e7 100%)}
        :root body.kiosk-page{opacity:1!important;transition:none!important}
        .totem-header-row{width:100%;margin:0}.totem-header-row>.col-12{min-width:0}.totem-header-row .demo-switcher{width:100%;max-width:100%;margin:0}
        .k-ico{width:24px;height:24px;flex:none;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}

        .kiosk{height:var(--k-h);min-height:440px;display:flex;justify-content:center;padding:var(--t-pad) 8px 0;overflow:hidden}
        .totem{position:relative;display:flex;flex-direction:column;align-items:center;width:calc(var(--t-sw) + 2 * var(--t-glass) + 2 * var(--t-side));height:100%}
        .totem::after{content:"";position:absolute;left:50%;bottom:0;z-index:-1;width:70%;height:calc(var(--t-base) * 2.4);transform:translate(-50%,40%);border-radius:50%;background:radial-gradient(closest-side,rgba(15,30,60,.32),transparent)}
        .totem-housing{flex:1;min-height:0;width:100%;display:flex;flex-direction:column;padding:var(--t-side) var(--t-side) 0;border-radius:calc(var(--t-side) * 2.4) calc(var(--t-side) * 2.4) calc(var(--t-side) * 1.4) calc(var(--t-side) * 1.4);background:linear-gradient(90deg,#c9d3e0 0%,#edf1f6 3%,#fff 18%,#fff 82%,#edf1f6 97%,#c6d0dd 100%);box-shadow:0 0 0 1px #b8c4d4,inset 0 2px 0 rgba(255,255,255,.9),0 24px 50px rgba(15,42,92,.22),0 4px 10px rgba(15,42,92,.12)}
        .totem-glass{position:relative;flex:1;min-height:0;display:flex;padding:calc(var(--t-glass) + var(--t-cam)) var(--t-glass) var(--t-glass);border-radius:calc(var(--t-glass) + 10px);background:linear-gradient(160deg,#1c2435,#05080f 42%,#0c121d);box-shadow:inset 0 0 0 1px rgba(255,255,255,.07),0 1px 0 #fff}
        .totem-cam{position:absolute;top:calc((var(--t-glass) + var(--t-cam)) / 2);left:50%;width:7px;height:7px;transform:translate(-50%,-50%);border-radius:50%;background:radial-gradient(circle at 35% 35%,#5d7092 0 18%,#0a1020 52%);box-shadow:0 0 0 2px #1b2437}
        .totem-panel{flex:none;display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:clamp(12px,3vw,40px);height:var(--t-panel);padding:0 4px}
        .totem-scanner{position:relative;grid-column:2;display:grid;place-items:center;width:calc(var(--t-panel) * 1.5);height:calc(var(--t-panel) * .56);border-radius:8px;background:linear-gradient(160deg,#2a3346,#0a0f19);color:rgba(255,255,255,.5);box-shadow:inset 0 0 0 1px rgba(255,255,255,.06),0 1px 0 #fff}
        .totem-scanner .k-ico{width:45%;height:45%}
        .totem-scanner::after{content:"";position:absolute;left:14%;right:14%;top:50%;height:2px;background:#ff5d5d;box-shadow:0 0 6px #ff5d5d;opacity:.75}
        .totem-led{grid-column:3;justify-self:end;width:9px;height:9px;margin-right:6px;border-radius:50%;background:#2fd59f;box-shadow:0 0 0 2px #e4e9f0,0 0 10px #2fd59f}
        .totem-led.is-off{background:#ff7b6b;box-shadow:0 0 0 2px #e4e9f0,0 0 10px #ff7b6b}
        .totem-neck{flex:none;width:clamp(90px,24%,280px);height:var(--t-neck);background:linear-gradient(90deg,#b4bfce,#e7ecf2 22%,#fff 50%,#e2e8ef 78%,#b1bccb);box-shadow:inset 0 7px 8px -6px rgba(15,42,92,.35)}
        .totem-base{flex:none;width:clamp(170px,46%,540px);height:var(--t-base);border-radius:10px 10px 3px 3px;background:linear-gradient(180deg,#e2e8f0,#a3afc0);box-shadow:0 0 0 1px #a9b4c3}

        .kiosk-screen{position:relative;flex:1;min-width:0;display:flex;flex-direction:column;border-radius:12px;overflow:hidden;background:linear-gradient(180deg,#123d86 0%,#1848a1 46%,#1a62a6 100%);color:#fff;container:pantalla / inline-size}
        .kiosk-top{flex:none;display:flex;align-items:center;justify-content:space-between;gap:calc(var(--su) * 2);padding:calc(var(--su) * 1.5) calc(var(--su) * 3);border-bottom:1px solid rgba(255,255,255,.14)}
        .kiosk-brand{display:flex;align-items:center;gap:calc(var(--su) * 1.8);min-width:0}
        .kiosk-logo{display:block;flex:none;height:calc(var(--su) * 5.2);width:auto}
        .kiosk-title{min-width:0;padding-left:calc(var(--su) * 1.8);border-left:1px solid rgba(255,255,255,.3);line-height:1.15}
        .kiosk-title strong{display:block;color:#fff;font-size:calc(var(--su) * 2.8);font-weight:800}
        .kiosk-title small{display:block;margin-top:calc(var(--su) * .3);color:#8be7e0;font-size:calc(var(--su) * 1.5);font-weight:800;letter-spacing:.14em;text-transform:uppercase}
        .kiosk-clock{flex:none;display:flex;align-items:baseline;gap:calc(var(--su) * 1.4)}
        .kiosk-clock small{color:#bcd3ee;font-size:calc(var(--su) * 1.7);font-weight:600}
        .kiosk-clock small::first-letter{text-transform:uppercase}
        .kiosk-clock strong{color:#fff;font-size:calc(var(--su) * 4);font-weight:800;font-variant-numeric:tabular-nums}

        .kiosk-body{--su:calc(var(--t-unit) * var(--k-menu,1));flex:1;min-height:0;overflow-y:auto;display:flex;flex-direction:column;gap:calc(var(--su) * 1.8);padding:calc(var(--su) * 2.4) calc(var(--su) * 3.4);scrollbar-width:none}
        .kiosk-body::-webkit-scrollbar{display:none}
        .kiosk-welcome{flex:none;text-align:center}
        .kiosk-eyebrow{display:inline-block;margin-bottom:calc(var(--su) * .9);color:#8be7e0;font-size:calc(var(--su) * 1.9);font-weight:800;letter-spacing:.16em;text-transform:uppercase}
        .kiosk-welcome h1{margin:0;color:#fff;font-size:calc(var(--su) * 5.8);font-weight:800;letter-spacing:-.02em;line-height:1.1}
        .kiosk-welcome p{margin:calc(var(--su) * .7) 0 0;color:#cfe0f5;font-size:calc(var(--su) * 2.6)}

        .kiosk-menu{flex:none;display:grid;grid-template-columns:repeat(var(--k-cols,3),minmax(0,1fr));gap:calc(var(--su) * 1.8)}
        .kiosk-tile{position:relative;display:flex;align-items:center;gap:calc(var(--su) * 1.8);padding:calc(var(--su) * 2) calc(var(--su) * 2.4);border-radius:calc(var(--su) * 2.4);background:#fff;color:var(--k-ink);text-align:left;text-decoration:none;box-shadow:0 calc(var(--su) * .8) calc(var(--su) * 2.4) rgba(4,16,44,.3);-webkit-tap-highlight-color:transparent}
        .kiosk-tile:hover{color:var(--k-ink)}
        .kiosk-tile-icon{flex:none;display:grid;place-items:center;width:calc(var(--su) * 7.4);height:calc(var(--su) * 7.4);border-radius:50%;background:#e8f0fb;color:var(--k-blue)}
        .kiosk-tile-icon .k-ico{width:calc(var(--su) * 3.9);height:calc(var(--su) * 3.9)}
        .kiosk-tile-body{flex:1;min-width:0}
        .kiosk-tile-title{display:block;font-size:calc(var(--su) * 2.9);font-weight:800;line-height:1.2}
        .kiosk-tile-desc{display:block;margin-top:calc(var(--su) * .4);color:var(--k-muted);font-size:calc(var(--su) * 2.15);line-height:1.35}
        .kiosk-tile-go{flex:none;display:grid;place-items:center;width:calc(var(--su) * 4.4);height:calc(var(--su) * 4.4);border-radius:50%;background:var(--k-soft);color:var(--k-blue)}
        .kiosk-tile-go .k-ico{width:calc(var(--su) * 2.4);height:calc(var(--su) * 2.4)}
        .kiosk-tile.is-active{background:linear-gradient(160deg,#0c868d,#09727a);color:#fff;box-shadow:0 0 0 calc(var(--su) * .5) #8be7e0,0 calc(var(--su) * .8) calc(var(--su) * 2.4) rgba(4,16,44,.3)}
        .kiosk-tile.is-active .kiosk-tile-icon{background:rgba(255,255,255,.18);color:#fff}
        .kiosk-tile.is-active .kiosk-tile-desc{color:#eafafa}
        .kiosk-tile.is-active .kiosk-tile-go{background:#fff;color:#09727a}
        .kiosk-tile.is-active .kiosk-tile-go .k-ico{stroke-width:3}

        .kiosk-stage{--su:calc(var(--t-unit) * var(--k-fit,1));flex:1 0 auto;display:flex;flex-direction:column;justify-content:center;padding:calc(var(--su) * 2.6) calc(var(--su) * 3.2);border-radius:calc(var(--su) * 3);background:#fff;color:var(--k-ink);box-shadow:0 calc(var(--su) * 1) calc(var(--su) * 3.4) rgba(4,16,44,.3)}
        .kiosk-stage-head{display:flex;align-items:center;gap:calc(var(--su) * 2);margin-bottom:calc(var(--su) * 1.8)}
        .kiosk-stage-icon{flex:none;display:grid;place-items:center;width:calc(var(--su) * 7);height:calc(var(--su) * 7);border-radius:calc(var(--su) * 2);background:linear-gradient(140deg,#dff4f4,#e4edfb);color:var(--k-blue)}
        .kiosk-stage-icon .k-ico{width:calc(var(--su) * 3.8);height:calc(var(--su) * 3.8)}
        .kiosk-stage h2{margin:0;color:var(--k-ink);font-size:calc(var(--su) * 3.4);font-weight:800;letter-spacing:-.01em;line-height:1.15}
        .kiosk-stage-head p{margin:calc(var(--su) * .5) 0 0;color:var(--k-muted);font-size:calc(var(--su) * 1.9);line-height:1.4}
        .kiosk-steps{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:calc(var(--su) * 1.4);margin:0 0 calc(var(--su) * 2);padding:0;list-style:none}
        .kiosk-steps li{display:flex;align-items:center;gap:calc(var(--su) * 1.2);padding:calc(var(--su) * 1.2) calc(var(--su) * 1.4);border:1px solid var(--k-line);border-radius:calc(var(--su) * 1.8);background:#f7faff;font-size:calc(var(--su) * 1.75);font-weight:700;line-height:1.3}
        .kiosk-steps b{flex:none;display:grid;place-items:center;width:calc(var(--su) * 3.8);height:calc(var(--su) * 3.8);border-radius:50%;background:var(--k-blue);color:#fff;font-size:calc(var(--su) * 1.9);font-weight:800}
        .kiosk-note{display:flex;align-items:center;justify-content:center;gap:calc(var(--su) * 1.2);margin:calc(var(--su) * 1.4) 0 0;color:var(--k-muted);font-size:calc(var(--su) * 1.9);font-weight:600;text-align:center}
        .kiosk-note .k-ico{width:calc(var(--su) * 3);height:calc(var(--su) * 3);color:var(--k-teal-ink)}
        .kiosk-note.is-warn{color:#8a5a00}.kiosk-note.is-warn .k-ico{color:#c98a00}
        .kiosk-link{display:inline-flex;align-items:center;gap:calc(var(--su) * 1);margin-top:calc(var(--su) * 1.4);padding:calc(var(--su) * .8) 0;color:var(--k-blue);font-size:calc(var(--su) * 2);font-weight:800;text-decoration:none}
        .kiosk-link .k-ico{width:calc(var(--su) * 2.6);height:calc(var(--su) * 2.6)}
        .kiosk-link:hover{color:var(--k-blue-2);text-decoration:underline}

        .k-btn{display:flex;align-items:center;justify-content:center;gap:calc(var(--su) * 1.4);width:100%;min-height:calc(var(--su) * 7.2);margin:0;padding:calc(var(--su) * 1.4) calc(var(--su) * 3);border:0;border-radius:calc(var(--su) * 2.2);background:linear-gradient(135deg,var(--k-blue),var(--k-blue-2));color:#fff;font-family:inherit;font-size:calc(var(--su) * 2.4);font-weight:800;line-height:1.2;text-align:center;text-decoration:none;cursor:pointer;box-shadow:0 calc(var(--su) * 1) calc(var(--su) * 2.6) rgba(24,72,161,.28),inset 0 -3px 0 rgba(0,0,0,.14);transition:transform .12s ease,filter .2s ease;-webkit-tap-highlight-color:transparent;user-select:none}
        .k-btn .k-ico{width:calc(var(--su) * 3.2);height:calc(var(--su) * 3.2)}
        .k-btn:hover{color:#fff;filter:brightness(1.07)}
        .k-btn:active{transform:translateY(2px) scale(.985)}
        .k-btn:disabled,.k-btn[aria-disabled="true"]{background:#c9d3e1;color:#f4f7fb;box-shadow:none;cursor:not-allowed;transform:none;filter:none;animation:none}
        .k-btn--ok,.k-btn--xl{background:linear-gradient(135deg,var(--k-ok),var(--k-ok-2));box-shadow:0 calc(var(--su) * 1) calc(var(--su) * 2.6) rgba(14,133,119,.32),inset 0 -3px 0 rgba(0,0,0,.14)}
        .k-btn--ghost{border:2px solid #bcd0ee;background:#fff;color:var(--k-blue);box-shadow:none}
        .k-btn--ghost:hover{color:var(--k-blue);background:#f3f7fd;filter:none}
        .k-btn--xl{flex:none;gap:calc(var(--su) * 2);min-height:calc(var(--su) * 10);padding:calc(var(--su) * 1.6) calc(var(--su) * 1.8) calc(var(--su) * 1.6) calc(var(--su) * 2.4);border-radius:calc(var(--su) * 3);font-size:calc(var(--su) * 3.3)}
        .k-btn-lead,.k-btn-arrow{flex:none;display:grid;place-items:center;width:calc(var(--su) * 6.4);height:calc(var(--su) * 6.4);border-radius:50%}
        .k-btn-lead{background:rgba(255,255,255,.16)}
        .k-btn-arrow{background:#fff;color:var(--k-ok)}
        .k-btn-lead .k-ico,.k-btn-arrow .k-ico{width:calc(var(--su) * 3.4);height:calc(var(--su) * 3.4)}
        .k-btn:disabled .k-btn-arrow{color:#9aa8bc}
        .k-btn-text{flex:1;display:flex;flex-direction:column;align-items:flex-start;min-width:0;text-align:left}
        .k-btn-text small{margin-top:calc(var(--su) * .4);font-size:calc(var(--su) * 1.85);font-weight:600;opacity:.9}
        .k-btn--attract:not(:disabled){animation:kAttract 2.6s ease-out infinite}
        @keyframes kAttract{0%{box-shadow:0 8px 20px rgba(14,133,119,.32),0 0 0 0 rgba(49,190,190,.6)}70%,100%{box-shadow:0 8px 20px rgba(14,133,119,.32),0 0 0 12px rgba(49,190,190,0)}}
        .k-btn:focus-visible,.kiosk-tile:focus-visible,.kiosk-details summary:focus-visible{outline:3px solid #8be7e0;outline-offset:3px}

        .kiosk-flash{flex:none;display:flex;align-items:center;gap:calc(var(--su) * 1.6);padding:calc(var(--su) * 1.8) calc(var(--su) * 2.4);border-radius:calc(var(--su) * 2.2);font-size:calc(var(--su) * 2.1);font-weight:800;line-height:1.35}
        .kiosk-flash .k-ico{width:calc(var(--su) * 3.6);height:calc(var(--su) * 3.6)}
        .kiosk-flash.ok{background:#e2f7f2;color:#0b6b5f}
        .kiosk-flash.error{background:#fde6e4;color:#9b2318}
        .kiosk-stage .kiosk-flash{margin-bottom:calc(var(--su) * 1.8)}
        .kiosk-hint{display:flex;align-items:flex-start;gap:calc(var(--su) * 1.4);margin-top:calc(var(--su) * 1.8);padding:calc(var(--su) * 1.6) calc(var(--su) * 2);border-radius:calc(var(--su) * 1.8);background:#edf4ff;color:#1d4c94;font-size:calc(var(--su) * 1.75);font-weight:600;line-height:1.45}
        .kiosk-hint .k-ico{width:calc(var(--su) * 2.8);height:calc(var(--su) * 2.8)}
        .kiosk-label{display:block;margin:0 0 calc(var(--su) * 1);color:var(--k-muted);font-size:calc(var(--su) * 1.6);font-weight:800;letter-spacing:.06em;text-transform:uppercase}
        .kiosk-field{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:calc(var(--su) * 1.4)}
        .kiosk-field input{width:100%;min-height:calc(var(--su) * 8.4);padding:0 calc(var(--su) * 2.6);border:2px solid #bcd0ee;border-radius:calc(var(--su) * 2.2);background:#fff;color:var(--k-ink);font-family:inherit;font-size:calc(var(--su) * 3.2);font-weight:700;letter-spacing:.05em;text-transform:uppercase}
        .kiosk-field input::placeholder{color:#9fb0c6;font-weight:600;letter-spacing:.02em}
        .kiosk-field input:focus{outline:none;border-color:var(--k-blue);box-shadow:0 0 0 4px rgba(24,72,161,.15)}
        .kiosk-field .k-btn{width:auto;min-height:calc(var(--su) * 8.4);padding:0 calc(var(--su) * 3.4)}
        .kiosk-error{margin-top:calc(var(--su) * 1.2);color:#a32020;font-size:calc(var(--su) * 1.9);font-weight:800}
        .kiosk-details summary{display:flex;align-items:center;justify-content:space-between;gap:calc(var(--su) * 1.4);min-height:calc(var(--su) * 6.4);padding:0 calc(var(--su) * 2.4);border:2px dashed #bcd0ee;border-radius:calc(var(--su) * 2);color:var(--k-blue);font-size:calc(var(--su) * 2.1);font-weight:800;list-style:none;cursor:pointer}
        .kiosk-details summary::-webkit-details-marker{display:none}
        .kiosk-details summary .k-ico{width:calc(var(--su) * 3);height:calc(var(--su) * 3);transition:transform .2s ease}
        .kiosk-details[open] summary{margin-bottom:calc(var(--su) * 1.8);border-style:solid;background:#f3f7fd}
        .kiosk-details[open] summary .k-ico{transform:rotate(180deg)}

        .kiosk-vouchers{display:grid;gap:calc(var(--su) * 1.6);margin-top:calc(var(--su) * 2)}
        .kiosk-voucher{display:grid;grid-template-columns:auto minmax(0,1fr) auto;align-items:center;gap:calc(var(--su) * 2);padding:calc(var(--su) * 2);border:1px solid var(--k-line);border-radius:calc(var(--su) * 2.2);background:#f7faff}
        .kiosk-voucher-icon{display:grid;place-items:center;width:calc(var(--su) * 6);height:calc(var(--su) * 6);border-radius:calc(var(--su) * 1.8);background:#e8f0fb;color:var(--k-blue)}
        .kiosk-voucher-icon .k-ico{width:calc(var(--su) * 3.2);height:calc(var(--su) * 3.2)}
        .kiosk-voucher h3{margin:0 0 calc(var(--su) * .6);font-size:calc(var(--su) * 2.2);font-weight:800}
        .kiosk-voucher p{margin:2px 0;color:var(--k-muted);font-size:calc(var(--su) * 1.8)}
        .kiosk-voucher p strong{color:var(--k-ink)}
        .kiosk-voucher form{margin:0}
        .kiosk-voucher .k-btn{min-width:calc(var(--su) * 28)}
        .kiosk-badge{display:inline-flex;align-items:center;justify-content:center;gap:calc(var(--su) * 1);padding:calc(var(--su) * 1.4) calc(var(--su) * 2);border-radius:999px;background:#e2f7f2;color:#0b6b5f;font-size:calc(var(--su) * 1.8);font-weight:800;white-space:nowrap}
        .kiosk-badge .k-ico{width:calc(var(--su) * 2.8);height:calc(var(--su) * 2.8)}

        .kiosk-options{display:grid;grid-template-columns:1fr 1fr;gap:calc(var(--su) * 1.8)}
        .kiosk-option{display:grid;grid-template-columns:auto minmax(0,1fr);align-content:start;align-items:center;gap:calc(var(--su) * 1) calc(var(--su) * 1.6);padding:calc(var(--su) * 2.2);border:1px solid var(--k-line);border-radius:calc(var(--su) * 2.4);background:#f7faff}
        .kiosk-option>.kiosk-tile-icon{width:calc(var(--su) * 5.6);height:calc(var(--su) * 5.6)}
        .kiosk-option>.kiosk-tile-icon .k-ico{width:calc(var(--su) * 3);height:calc(var(--su) * 3)}
        .kiosk-option h3{margin:0;font-size:calc(var(--su) * 2.4);font-weight:800}
        .kiosk-option p{grid-column:1/-1;margin:0;color:var(--k-muted);font-size:calc(var(--su) * 1.75);line-height:1.45}
        .kiosk-option>.k-btn{grid-column:1/-1;align-self:end;min-height:calc(var(--su) * 6.6);margin-top:calc(var(--su) * .6);font-size:calc(var(--su) * 2.1)}

        .kiosk-success{display:grid;grid-template-columns:auto minmax(0,1fr);align-items:center;gap:calc(var(--su) * 1.6) calc(var(--su) * 2);margin-bottom:calc(var(--su) * 2);padding:calc(var(--su) * 2);border:2px solid #7fd6c9;border-radius:calc(var(--su) * 2.2);background:#effbf8}
        .kiosk-success-icon{display:grid;place-items:center;width:calc(var(--su) * 6.4);height:calc(var(--su) * 6.4);border-radius:50%;background:var(--k-ok);color:#fff}
        .kiosk-success-icon .k-ico{width:calc(var(--su) * 3.6);height:calc(var(--su) * 3.6)}
        .kiosk-success h3{margin:0 0 calc(var(--su) * .4);color:#0b6b5f;font-size:calc(var(--su) * 2.6);font-weight:800}
        .kiosk-success p{margin:0;color:var(--k-muted);font-size:calc(var(--su) * 1.9)}
        .kiosk-success-actions{grid-column:1/-1;display:grid;grid-template-columns:1fr 1fr;gap:calc(var(--su) * 1.4)}

        .kiosk-bar{flex:none;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:calc(var(--su) * 1) calc(var(--su) * 2);padding:calc(var(--su) * 1.1) calc(var(--su) * 3);background:rgba(4,16,44,.28);color:#d6e6f8}
        .kiosk-bar-info{display:flex;flex-wrap:wrap;gap:calc(var(--su) * 1)}
        .kiosk-chip{display:inline-flex;align-items:center;gap:calc(var(--su) * .8);padding:calc(var(--su) * .6) calc(var(--su) * 1.4);border-radius:999px;background:rgba(255,255,255,.12);color:#d6e6f8;font-size:calc(var(--su) * 1.9);font-weight:600;white-space:nowrap}
        .kiosk-chip strong{color:#fff;font-weight:800}
        .kiosk-chip .k-ico{width:calc(var(--su) * 2);height:calc(var(--su) * 2);color:#8be7e0}
        .kiosk-bar-help{display:flex;align-items:center;gap:calc(var(--su) * 1.6)}
        .kiosk-help{display:inline-flex;align-items:center;gap:calc(var(--su) * 1);color:#fff;font-size:calc(var(--su) * 2.05);font-weight:700}
        .kiosk-help .k-ico{width:calc(var(--su) * 2.4);height:calc(var(--su) * 2.4);color:#8be7e0}
        .kiosk-admin{color:#fff;font-size:calc(var(--su) * 1.5);font-weight:800}
        .kiosk-admin:hover{color:#8be7e0}

        @container pantalla (min-width:561px) and (max-width:820px){
            .kiosk-tile{flex-direction:column;justify-content:center;gap:calc(var(--su) * 1);text-align:center}
            .kiosk-tile-body{flex:none}
            .kiosk-tile-go{position:absolute;top:calc(var(--su) * 1.2);right:calc(var(--su) * 1.2)}
            .kiosk-tile:not(.is-active) .kiosk-tile-go{display:none}
        }
        @container pantalla (max-width:560px){
            .kiosk-top,.kiosk-bar{--su:calc(var(--t-unit) * 1.1 * var(--k-menu,1))}
            .kiosk-stage{--su:calc(var(--t-unit) * 1.1 * var(--k-fit,1))}
            .kiosk-body{--su:calc(var(--t-unit) * 1.1 * var(--k-menu,1));gap:calc(var(--su) * 2.8);padding:calc(var(--su) * 3.6) calc(var(--su) * 4) calc(var(--su) * 3.2)}
            .kiosk-top{padding:calc(var(--su) * 2.4) calc(var(--su) * 4)}
            .kiosk-logo{height:calc(var(--su) * 5.6)}
            .kiosk-title strong{font-size:calc(var(--su) * 3.6)}
            .kiosk-title small{font-size:calc(var(--su) * 2.2)}
            .kiosk-clock small{font-size:calc(var(--su) * 3)}
            .kiosk-clock strong{font-size:calc(var(--su) * 4.8)}
            .kiosk-eyebrow{margin-bottom:calc(var(--su) * 1.4);font-size:calc(var(--su) * 2.8)}
            .kiosk-welcome h1{font-size:calc(var(--su) * 6.6)}
            .kiosk-welcome p{margin-top:calc(var(--su) * 1.2);font-size:calc(var(--su) * 3.2)}
            .kiosk-menu{gap:calc(var(--su) * 2.4)}
            .kiosk-tile{flex-direction:column;justify-content:center;gap:calc(var(--su) * 1.4);padding:calc(var(--su) * 2.6) calc(var(--su) * 1.6);border-radius:calc(var(--su) * 3.6);text-align:center}
            .kiosk-tile-icon{width:calc(var(--su) * 10);height:calc(var(--su) * 10)}
            .kiosk-tile-icon .k-ico{width:calc(var(--su) * 5.4);height:calc(var(--su) * 5.4)}
            .kiosk-tile-body{flex:none}
            .kiosk-tile-title{font-size:calc(var(--su) * 3.4)}
            .kiosk-tile-desc{margin-top:calc(var(--su) * .8);font-size:calc(var(--su) * 2.7)}
            .kiosk-tile-go{position:absolute;top:calc(var(--su) * 1.6);right:calc(var(--su) * 1.6);width:calc(var(--su) * 5.6);height:calc(var(--su) * 5.6)}
            .kiosk-tile:not(.is-active) .kiosk-tile-go{display:none}
            .kiosk-tile-go .k-ico{width:calc(var(--su) * 3.4);height:calc(var(--su) * 3.4)}
            .kiosk-stage{padding:calc(var(--su) * 4);border-radius:calc(var(--su) * 4.4)}
            .kiosk-stage-head{gap:calc(var(--su) * 3);margin-bottom:calc(var(--su) * 3)}
            .kiosk-stage-icon{width:calc(var(--su) * 10);height:calc(var(--su) * 10);border-radius:calc(var(--su) * 3)}
            .kiosk-stage-icon .k-ico{width:calc(var(--su) * 5.4);height:calc(var(--su) * 5.4)}
            .kiosk-stage h2{font-size:calc(var(--su) * 4.8)}
            .kiosk-stage-head p{margin-top:calc(var(--su) * 1);font-size:calc(var(--su) * 3.1)}
            .kiosk-steps{grid-template-columns:1fr 1fr;gap:calc(var(--su) * 2);margin-bottom:calc(var(--su) * 3)}
            .kiosk-steps li{gap:calc(var(--su) * 2);padding:calc(var(--su) * 1.8) calc(var(--su) * 2.2);border-radius:calc(var(--su) * 3);font-size:calc(var(--su) * 2.9)}
            .kiosk-steps b{width:calc(var(--su) * 6.4);height:calc(var(--su) * 6.4);font-size:calc(var(--su) * 3.2)}
            .kiosk-note{gap:calc(var(--su) * 2);margin-top:calc(var(--su) * 2.4);font-size:calc(var(--su) * 3)}
            .kiosk-note .k-ico{width:calc(var(--su) * 5);height:calc(var(--su) * 5)}
            .kiosk-link{gap:calc(var(--su) * 1.6);margin-top:calc(var(--su) * 2);padding:calc(var(--su) * 1.6) 0;font-size:calc(var(--su) * 3.3)}
            .kiosk-link .k-ico{width:calc(var(--su) * 4.5);height:calc(var(--su) * 4.5)}
            .k-btn{gap:calc(var(--su) * 2.4);min-height:calc(var(--su) * 13);padding:calc(var(--su) * 2.4) calc(var(--su) * 4);border-radius:calc(var(--su) * 3.6);font-size:calc(var(--su) * 4)}
            .k-btn .k-ico{width:calc(var(--su) * 5.4);height:calc(var(--su) * 5.4)}
            .k-btn--xl{gap:calc(var(--su) * 2.6);min-height:calc(var(--su) * 15);padding:calc(var(--su) * 2.4) calc(var(--su) * 2.6) calc(var(--su) * 2.4) calc(var(--su) * 4.4);border-radius:calc(var(--su) * 4.4);font-size:calc(var(--su) * 5)}
            .k-btn-lead{display:none}
            .k-btn-arrow{width:calc(var(--su) * 10);height:calc(var(--su) * 10)}
            .k-btn-arrow .k-ico{width:calc(var(--su) * 5.4);height:calc(var(--su) * 5.4)}
            .k-btn-text small{margin-top:calc(var(--su) * .6);font-size:calc(var(--su) * 3)}
            .kiosk-flash{gap:calc(var(--su) * 2.6);padding:calc(var(--su) * 3) calc(var(--su) * 3.6);border-radius:calc(var(--su) * 3.6);font-size:calc(var(--su) * 3.5)}
            .kiosk-flash .k-ico{width:calc(var(--su) * 6);height:calc(var(--su) * 6)}
            .kiosk-hint{gap:calc(var(--su) * 2.2);margin-top:calc(var(--su) * 3);padding:calc(var(--su) * 2.6) calc(var(--su) * 3);border-radius:calc(var(--su) * 3);font-size:calc(var(--su) * 2.9)}
            .kiosk-hint .k-ico{width:calc(var(--su) * 4.6);height:calc(var(--su) * 4.6)}
            .kiosk-label{margin-bottom:calc(var(--su) * 1.6);font-size:calc(var(--su) * 2.8)}
            .kiosk-field{grid-template-columns:1fr;gap:calc(var(--su) * 2.4)}
            .kiosk-field input{min-height:calc(var(--su) * 14);padding:0 calc(var(--su) * 4);border-radius:calc(var(--su) * 3.6);font-size:calc(var(--su) * 5.6)}
            .kiosk-field .k-btn{width:100%;min-height:calc(var(--su) * 13);padding:0 calc(var(--su) * 4)}
            .kiosk-error{margin-top:calc(var(--su) * 2);font-size:calc(var(--su) * 3.2)}
            .kiosk-details summary{gap:calc(var(--su) * 2);min-height:calc(var(--su) * 11);padding:0 calc(var(--su) * 3.6);border-radius:calc(var(--su) * 3.2);font-size:calc(var(--su) * 3.4)}
            .kiosk-details summary .k-ico{width:calc(var(--su) * 5);height:calc(var(--su) * 5)}
            .kiosk-details[open] summary{margin-bottom:calc(var(--su) * 3)}
            .kiosk-vouchers{gap:calc(var(--su) * 2.6);margin-top:calc(var(--su) * 3.6)}
            .kiosk-voucher{grid-template-columns:auto minmax(0,1fr);align-items:start;gap:calc(var(--su) * 2.6) calc(var(--su) * 3);padding:calc(var(--su) * 3.4);border-radius:calc(var(--su) * 3.6)}
            .kiosk-voucher>form,.kiosk-voucher>.kiosk-badge{grid-column:1/-1}
            .kiosk-voucher-icon{width:calc(var(--su) * 10);height:calc(var(--su) * 10);border-radius:calc(var(--su) * 3)}
            .kiosk-voucher-icon .k-ico{width:calc(var(--su) * 5.4);height:calc(var(--su) * 5.4)}
            .kiosk-voucher h3{margin-bottom:calc(var(--su) * 1.2);font-size:calc(var(--su) * 3.8)}
            .kiosk-voucher p{font-size:calc(var(--su) * 3.1)}
            .kiosk-voucher .k-btn{min-width:0}
            .kiosk-badge{gap:calc(var(--su) * 1.6);padding:calc(var(--su) * 2.4) calc(var(--su) * 3.2);font-size:calc(var(--su) * 3.2)}
            .kiosk-badge .k-ico{width:calc(var(--su) * 4.6);height:calc(var(--su) * 4.6)}
            .kiosk-options{grid-template-columns:1fr;gap:calc(var(--su) * 2.6)}
            .kiosk-option{gap:calc(var(--su) * 1.2) calc(var(--su) * 3);padding:calc(var(--su) * 3.2);border-radius:calc(var(--su) * 3.6)}
            .kiosk-option>.kiosk-tile-icon{width:calc(var(--su) * 9);height:calc(var(--su) * 9)}
            .kiosk-option>.kiosk-tile-icon .k-ico{width:calc(var(--su) * 4.8);height:calc(var(--su) * 4.8)}
            .kiosk-option h3{font-size:calc(var(--su) * 3.8)}
            .kiosk-option p{font-size:calc(var(--su) * 2.8)}
            .kiosk-option>.k-btn{min-height:calc(var(--su) * 11);margin-top:calc(var(--su) * 1.6);font-size:calc(var(--su) * 3.5)}
            .kiosk-success{gap:calc(var(--su) * 2.6) calc(var(--su) * 3);margin-bottom:calc(var(--su) * 3.6);padding:calc(var(--su) * 3.4);border-radius:calc(var(--su) * 3.6)}
            .kiosk-success-icon{width:calc(var(--su) * 11);height:calc(var(--su) * 11)}
            .kiosk-success-icon .k-ico{width:calc(var(--su) * 6);height:calc(var(--su) * 6)}
            .kiosk-success h3{font-size:calc(var(--su) * 4.2)}
            .kiosk-success p{font-size:calc(var(--su) * 3.1)}
            .kiosk-success-actions{grid-template-columns:1fr;gap:calc(var(--su) * 2.4)}
            .kiosk-bar{padding:calc(var(--su) * 2.2) calc(var(--su) * 4)}
            .kiosk-chip{gap:calc(var(--su) * 1.2);padding:calc(var(--su) * .8) calc(var(--su) * 2);font-size:calc(var(--su) * 2.6)}
            .kiosk-chip .k-ico{width:calc(var(--su) * 3.4);height:calc(var(--su) * 3.4)}
            .kiosk-help{gap:calc(var(--su) * 1.6);font-size:calc(var(--su) * 2.9)}
            .kiosk-help .k-ico{width:calc(var(--su) * 4.4);height:calc(var(--su) * 4.4)}
            .kiosk-admin{font-size:calc(var(--su) * 2.8)}
        }

        @media (prefers-reduced-motion: reduce){.k-btn--attract:not(:disabled){animation:none}.k-btn{transition:none}}
        @media (max-width:600px){:root{--t-panel:clamp(30px,4.4vh,44px);--t-neck:clamp(8px,1.8vh,18px);--t-side:clamp(7px,1vh,10px)}}
    </style>
</head>
<body class="kiosk-page">
@php $soloCompra = request()->routeIs('paciente.totem'); @endphp
@php $tab = $soloCompra ? request('tab', 'reserva') : request('tab', 'comprar'); @endphp
<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
    <symbol id="k-i-calendar" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M12 14v5M9.5 16.5h5"/></symbol>
    <symbol id="k-i-calendar-check" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M9 16l2 2 4-4"/></symbol>
    <symbol id="k-i-ticket" viewBox="0 0 24 24"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2M13 11v2M13 17v2"/></symbol>
    <symbol id="k-i-user-check" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/></symbol>
    <symbol id="k-i-arrow" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></symbol>
    <symbol id="k-i-check" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></symbol>
    <symbol id="k-i-check-circle" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="m8.5 12 2.5 2.5 4.5-5"/></symbol>
    <symbol id="k-i-alert" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></symbol>
    <symbol id="k-i-touch" viewBox="0 0 24 24"><path d="M9 11V5.5a1.5 1.5 0 0 1 3 0V10"/><path d="M12 10V9a1.5 1.5 0 0 1 3 0v1.5"/><path d="M15 10.5a1.5 1.5 0 0 1 3 0V15a6 6 0 0 1-6 6h-1.2a5 5 0 0 1-3.9-1.9L4.6 16a1.6 1.6 0 0 1 2.4-2.1L9 15.5"/></symbol>
    <symbol id="k-i-id" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="8" cy="11" r="2"/><path d="M14 10h4M14 14h4M5 16c.6-1 1.7-1.5 3-1.5s2.4.5 3 1.5"/></symbol>
    <symbol id="k-i-qr" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3h-3zM21 14v.01M14 21h.01M17 21h4v-4"/></symbol>
    <symbol id="k-i-shield" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></symbol>
    <symbol id="k-i-info" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></symbol>
    <symbol id="k-i-help" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3M12 17h.01"/></symbol>
    <symbol id="k-i-monitor" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></symbol>
    <symbol id="k-i-pin" viewBox="0 0 24 24"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></symbol>
    <symbol id="k-i-pulse" viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></symbol>
    <symbol id="k-i-chevron" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></symbol>
    <symbol id="k-i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></symbol>
    <symbol id="k-i-wallet" viewBox="0 0 24 24"><path d="M19 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3"/><path d="M21 11h-5a2 2 0 0 0 0 4h5v-4ZM3 7h16"/></symbol>
</svg>
<div class="row g-0 totem-header-row">
    <div class="col-12 p-0">
        @include('partials.demo_user_switcher')
    </div>
</div>
<script>
(function () {
    var barra = document.querySelector('.totem-header-row');
    function medir() {
        document.documentElement.style.setProperty('--k-offset', (barra ? barra.offsetHeight : 0) + 'px');
    }
    medir();
    if (barra && window.ResizeObserver) new ResizeObserver(medir).observe(barra);
    window.addEventListener('resize', medir);
})();
</script>
<main class="kiosk">
  <div class="totem">
    <div class="totem-housing">
      <div class="totem-glass">
        <span class="totem-cam" aria-hidden="true"></span>
        <div class="kiosk-screen">
    <header class="kiosk-top">
        <div class="kiosk-brand">
            <img class="kiosk-logo" src="{{ asset('images/logotipos/medichile-white-h.svg') }}" alt="Medichile">
            <div class="kiosk-title"><strong>Tótem de atención</strong><small>SDI Salud Digital Integrada</small></div>
        </div>
        <div class="kiosk-clock" aria-live="off"><small id="kioskFecha">&nbsp;</small><strong id="kioskHora">--:--</strong></div>
    </header>
    <script>
    (function () {
        var hora = document.getElementById('kioskHora'), fecha = document.getElementById('kioskFecha');
        function tick() {
            var ahora = new Date();
            hora.textContent = ahora.toLocaleTimeString('es-CL', {hour: '2-digit', minute: '2-digit', hour12: false});
            fecha.textContent = ahora.toLocaleDateString('es-CL', {weekday: 'short', day: 'numeric', month: 'short'}).replace(/\./g, '');
        }
        tick();
        setInterval(tick, 10000);
    })();
    </script>

    <section class="kiosk-body">
        <div class="kiosk-welcome">
            <span class="kiosk-eyebrow">{{ $soloCompra ? 'Paciente Tótem' : 'Autoatención Medichile' }}</span>
            <h1>¿Qué deseas hacer hoy?</h1>
            <p>{{ $soloCompra ? 'Toca una opción en la pantalla para reservar tu hora, sacar tu bono o avisar tu llegada.' : 'Toca una opción para comprar tu bono o avisar tu llegada sin pasar por recepción.' }}</p>
        </div>

        @php
            $opciones = $soloCompra
                ? [
                    ['tab' => 'reserva', 'icono' => 'k-i-calendar', 'titulo' => 'Reserva de hora', 'texto' => 'Agenda tu hora médica en Med-SDI.'],
                    ['tab' => 'bono', 'icono' => 'k-i-ticket', 'titulo' => 'Sacar bono de atención', 'texto' => 'Genera el bono asociado a tu atención.'],
                    ['tab' => 'autoatencion', 'icono' => 'k-i-user-check', 'titulo' => 'Aviso de llegada', 'texto' => 'Avisa que ya estás en el centro de salud.'],
                ]
                : [
                    ['tab' => 'comprar', 'icono' => 'k-i-ticket', 'titulo' => 'Comprar bono', 'texto' => 'Selecciona hora, paga y obtén tu QR.'],
                    ['tab' => 'autoatencion', 'icono' => 'k-i-user-check', 'titulo' => 'Avisar que llegué', 'texto' => 'Confirma tu llegada sin pasar por recepción.'],
                ];
            $rutaMenu = $soloCompra ? 'paciente.totem' : 'totem.local';
        @endphp
        <nav class="kiosk-menu" style="--k-cols:{{ count($opciones) }}" aria-label="Opciones del tótem">
            @foreach($opciones as $opcion)
                @php $activa = $tab === $opcion['tab']; @endphp
                <a class="kiosk-tile {{ $activa ? 'is-active' : '' }}" href="{{ route($rutaMenu, ['tab' => $opcion['tab']]) }}" @if($activa) aria-current="page" @endif>
                    <span class="kiosk-tile-icon"><svg class="k-ico" aria-hidden="true"><use href="#{{ $opcion['icono'] }}"/></svg></span>
                    <span class="kiosk-tile-body">
                        <span class="kiosk-tile-title">{{ $opcion['titulo'] }}</span>
                        <span class="kiosk-tile-desc">{{ $opcion['texto'] }}</span>
                    </span>
                    <span class="kiosk-tile-go"><svg class="k-ico" aria-hidden="true"><use href="#{{ $activa ? 'k-i-check' : 'k-i-arrow' }}"/></svg></span>
                </a>
            @endforeach
        </nav>

        @if(session('checkin_ok'))<div class="kiosk-flash ok" role="status"><svg class="k-ico" aria-hidden="true"><use href="#k-i-check-circle"/></svg>{{ session('checkin_ok') }}</div>@endif
        @if(session('ok'))<div class="kiosk-flash ok" role="status"><svg class="k-ico" aria-hidden="true"><use href="#k-i-check-circle"/></svg>{{ session('ok') }}</div>@endif
        @if(session('error'))<div class="kiosk-flash error" role="alert"><svg class="k-ico" aria-hidden="true"><use href="#k-i-alert"/></svg>{{ session('error') }}</div>@endif

        @if($tab === 'autoatencion')
            <section class="kiosk-stage" aria-labelledby="llegada-titulo">
                <div class="kiosk-stage-head">
                    <span class="kiosk-stage-icon"><svg class="k-ico" aria-hidden="true"><use href="#k-i-user-check"/></svg></span>
                    <div>
                        <h2 id="llegada-titulo">Avisar que llegué</h2>
                        <p>El tótem reconoce automáticamente las horas vinculadas al paciente y a su médico.</p>
                    </div>
                </div>
                @if($identificacionAutomatica)
                    <div class="kiosk-flash ok"><svg class="k-ico" aria-hidden="true"><use href="#k-i-check-circle"/></svg>Paciente reconocido. Encontramos la relación médico–paciente y la hora reservada.</div>
                @endif
                <details class="kiosk-details" @if(!$identificacionAutomatica) open @endif>
                    <summary>{{ $identificacionAutomatica ? 'Buscar otra hora por RUT' : 'Identificar reserva por RUT' }}<svg class="k-ico" aria-hidden="true"><use href="#k-i-chevron"/></svg></summary>
                    <form id="buscarReservaRutForm" method="POST" action="{{ route('totem.local.buscar-hora') }}" autocomplete="off" data-totem-identification-form>
                        @csrf
                        @if($soloCompra)<input type="hidden" name="origen" value="paciente-totem">@endif
                        <label class="kiosk-label" for="buscarReservaRut">RUT del paciente</label>
                        <div class="kiosk-field">
                            <input id="buscarReservaRut" name="rut" value="" placeholder="Ej.: 6.187.674-K" autocomplete="off" data-rut-input required>
                            <button class="k-btn" type="submit" @disabled(!$totem)><svg class="k-ico" aria-hidden="true"><use href="#k-i-search"/></svg>Buscar mi hora</button>
                        </div>
                        @error('rut')<div class="kiosk-error">{{ $message }}</div>@enderror
                    </form>
                    <div class="kiosk-hint"><svg class="k-ico" aria-hidden="true"><use href="#k-i-shield"/></svg><span>Seguridad: sólo se mostrarán reservas vigentes después de validar el RUT del paciente.</span></div>
                </details>

                @if($bonos->isNotEmpty())
                    <div class="kiosk-vouchers">
                        @foreach($bonos as $bono)
                            @php $enEspera = optional($bono->agenda)->estado === 'paciente_en_espera'; @endphp
                            <article class="kiosk-voucher">
                                <span class="kiosk-voucher-icon"><svg class="k-ico" aria-hidden="true"><use href="#k-i-calendar-check"/></svg></span>
                                <div>
                                    <h3>{{ $bono->tipo_servicio }} · {{ $bono->codigo }}</h3>
                                    <p><strong>Paciente:</strong> {{ $bono->beneficiario_nombre ?: $bono->cliente_nombre }}</p>
                                    <p><strong>Profesional:</strong> {{ $bono->prestador_nombre ?: optional($bono->profesional)->nombre }}</p>
                                    <p><strong>Hora:</strong> {{ optional($bono->agenda?->fecha_hora_confirmada ?: $bono->agenda?->fecha_hora_solicitada)?->format('d-m-Y H:i') ?? 'Hora asociada en Medichile' }}</p>
                                    <p><strong>Estado:</strong> {{ $enEspera ? 'Tu llegada ha sido confirmada. Espera en la sala hasta que tu profesional te llame.' : 'Hora confirmada' }}</p>
                                </div>
                                @if($enEspera)
                                    <span class="kiosk-badge"><svg class="k-ico" aria-hidden="true"><use href="#k-i-check-circle"/></svg>Llegada confirmada</span>
                                @else
                                    <form method="POST" action="{{ route('totem.local.confirmar-llegada', $bono) }}">
                                        @csrf
                                        @if($soloCompra)<input type="hidden" name="origen" value="paciente-totem">@endif
                                        <button class="k-btn k-btn--ok" type="submit"><svg class="k-ico" aria-hidden="true"><use href="#k-i-check-circle"/></svg>Confirmar mi llegada</button>
                                    </form>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        @elseif($soloCompra && $tab === 'bono')
            <section class="kiosk-stage" aria-labelledby="sacar-bono-titulo">
                <div class="kiosk-stage-head">
                    <span class="kiosk-stage-icon"><svg class="k-ico" aria-hidden="true"><use href="#k-i-ticket"/></svg></span>
                    <div>
                        <h2 id="sacar-bono-titulo">Sacar bono de atención</h2>
                        <p>Selecciona el camino que corresponde. El bono debe quedar asociado al paciente, profesional, prestación y hora de atención.</p>
                    </div>
                </div>
                <div class="kiosk-options">
                    <article class="kiosk-option">
                        <span class="kiosk-tile-icon"><svg class="k-ico" aria-hidden="true"><use href="#k-i-calendar-check"/></svg></span>
                        <h3>Ya tengo una hora reservada</h3>
                        <p>Abre el sistema de bonos del paciente para revisar tu reserva, confirmar el copago y continuar con la autorización y el QR. No reserves otra hora.</p>
                        <a class="k-btn" href="{{ route('paciente.escritorio') }}#bonos-reservados"><svg class="k-ico" aria-hidden="true"><use href="#k-i-wallet"/></svg>Ir a Mis bonos y pagos</a>
                    </article>
                    <article class="kiosk-option">
                        <span class="kiosk-tile-icon"><svg class="k-ico" aria-hidden="true"><use href="#k-i-calendar"/></svg></span>
                        <h3>Necesito una hora y un bono</h3>
                        <p>Identifica al paciente por RUT y selecciona profesional, prestación y horario en Med-SDI. El flujo existente registra la reserva y genera el bono pendiente de confirmación.</p>
                        <button class="k-btn" type="button" id="abrirReservaMedsdiTotem" @disabled(!$totem)><svg class="k-ico" aria-hidden="true"><use href="#k-i-search"/></svg>Buscar atención y generar bono</button>
                    </article>
                </div>
                <div class="kiosk-hint"><svg class="k-ico" aria-hidden="true"><use href="#k-i-info"/></svg><span>La creación del bono no significa que esté pagado. La autorización y el pago se continúan en el sistema de bonos del paciente. En esta demostración, las operaciones de pago se simulan.</span></div>
                <a class="kiosk-link" href="{{ route('paciente.totem', ['tab' => 'autoatencion']) }}">Ya tengo mi bono: avisar llegada al centro de salud <svg class="k-ico" aria-hidden="true"><use href="#k-i-arrow"/></svg></a>
            </section>
        @elseif($soloCompra)
            <section class="kiosk-stage" aria-labelledby="reserva-titulo">
                @if($agendaOnlineResultado)
                    <div class="kiosk-success" role="status">
                        <span class="kiosk-success-icon"><svg class="k-ico" aria-hidden="true"><use href="#k-i-check"/></svg></span>
                        <div>
                            <h3>Hora reservada y bono generado</h3>
                            <p><strong>{{ $agendaOnlineResultado->codigo }}</strong> · {{ $agendaOnlineResultado->prestador_nombre }} · {{ optional($agendaOnlineResultado->agenda?->fecha_hora_confirmada)->format('d-m-Y H:i') }}</p>
                        </div>
                        <div class="kiosk-success-actions">
                            <a class="k-btn" href="{{ route('vouchers.qr', $agendaOnlineResultado->qr_token) }}"><svg class="k-ico" aria-hidden="true"><use href="#k-i-qr"/></svg>Ver QR</a>
                            <a class="k-btn k-btn--ghost" href="{{ route('paciente.totem', ['tab' => 'autoatencion']) }}"><svg class="k-ico" aria-hidden="true"><use href="#k-i-user-check"/></svg>Ir a autoatención</a>
                        </div>
                    </div>
                @endif
                <div class="kiosk-stage-head">
                    <span class="kiosk-stage-icon"><svg class="k-ico" aria-hidden="true"><use href="#k-i-calendar"/></svg></span>
                    <div>
                        <h2 id="reserva-titulo">Reserva tu hora médica</h2>
                        <p>Busca especialidad, profesional y hora directamente en la agenda real de Med-SDI.</p>
                    </div>
                </div>
                <ol class="kiosk-steps" aria-label="Pasos de la reserva">
                    <li><b>1</b><span>Ingresa el RUT del paciente</span></li>
                    <li><b>2</b><span>Elige la prestación</span></li>
                    <li><b>3</b><span>Profesional, fecha y hora</span></li>
                    <li><b>4</b><span>Confirma y recibe tu bono</span></li>
                </ol>
                @if(auth()->user()?->rol === 'cliente')
                    <button class="k-btn k-btn--xl k-btn--attract" type="button" id="abrirReservaMedsdiTotem" @disabled(!$totem)>
                        <span class="k-btn-lead"><svg class="k-ico" aria-hidden="true"><use href="#k-i-touch"/></svg></span>
                        <span class="k-btn-text">Toca aquí para comenzar<small>Reservar hora en Med-SDI</small></span>
                        <span class="k-btn-arrow"><svg class="k-ico" aria-hidden="true"><use href="#k-i-arrow"/></svg></span>
                    </button>
                @else
                    <form method="POST" action="{{ route('demo.switch-user', 'paciente') }}">
                        @csrf
                        <input type="hidden" name="destino" value="paciente.totem">
                        <button class="k-btn k-btn--xl k-btn--attract" type="submit" @disabled(!$totem)>
                            <span class="k-btn-lead"><svg class="k-ico" aria-hidden="true"><use href="#k-i-touch"/></svg></span>
                            <span class="k-btn-text">Toca aquí para comenzar<small>Identificar paciente para reservar en Med-SDI</small></span>
                            <span class="k-btn-arrow"><svg class="k-ico" aria-hidden="true"><use href="#k-i-arrow"/></svg></span>
                        </button>
                    </form>
                @endif
                @if($totem)
                    <p class="kiosk-note"><svg class="k-ico" aria-hidden="true"><use href="#k-i-id"/></svg>Ten a mano tu RUT o cédula de identidad.</p>
                @else
                    <p class="kiosk-note is-warn"><svg class="k-ico" aria-hidden="true"><use href="#k-i-alert"/></svg>Este tótem no está activo. Acércate a recepción para ser atendido.</p>
                @endif
            </section>
        @else
            <section class="kiosk-stage" aria-labelledby="comprar-titulo">
                <div class="kiosk-stage-head">
                    <span class="kiosk-stage-icon"><svg class="k-ico" aria-hidden="true"><use href="#k-i-ticket"/></svg></span>
                    <div>
                        <h2 id="comprar-titulo">Comprar bono</h2>
                        <p>Abre el flujo real de prueba para seleccionar hora, pagar y generar el QR.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('demo.switch-user', 'paciente') }}">
                    @csrf
                    <input type="hidden" name="destino" value="paciente.totem">
                    <button class="k-btn k-btn--xl k-btn--attract" type="submit" @disabled(!$totem)>
                        <span class="k-btn-lead"><svg class="k-ico" aria-hidden="true"><use href="#k-i-touch"/></svg></span>
                        <span class="k-btn-text">Toca aquí para comenzar<small>Comenzar compra de bono</small></span>
                        <span class="k-btn-arrow"><svg class="k-ico" aria-hidden="true"><use href="#k-i-arrow"/></svg></span>
                    </button>
                </form>
            </section>
        @endif
    </section>

    <footer class="kiosk-bar">
        <div class="kiosk-bar-info">
            <span class="kiosk-chip"><svg class="k-ico" aria-hidden="true"><use href="#k-i-monitor"/></svg>Equipo: <strong>{{ $totem?->codigo ?? 'No provisionado' }}</strong></span>
            <span class="kiosk-chip"><svg class="k-ico" aria-hidden="true"><use href="#k-i-pin"/></svg>Ubicación: <strong>{{ $totem?->ubicacion ?? 'Sin ubicación' }}</strong></span>
            <span class="kiosk-chip"><svg class="k-ico" aria-hidden="true"><use href="#k-i-pulse"/></svg>Último ping: <strong>{{ $totem?->ultimo_ping?->format('d-m-Y H:i:s') ?? 'Sin conexión' }}</strong></span>
        </div>
        <div class="kiosk-bar-help">
            <span class="kiosk-help"><svg class="k-ico" aria-hidden="true"><use href="#k-i-help"/></svg>¿Necesitas ayuda? Acércate a recepción</span>
            @if(auth()->user()?->rol === 'admin')
                <a class="kiosk-admin" href="{{ url('admin/totems/dashboard') }}">Administrar tótem</a>
            @endif
        </div>
    </footer>
        </div>
      </div>
      <div class="totem-panel" aria-hidden="true">
        <span class="totem-scanner"><svg class="k-ico"><use href="#k-i-qr"/></svg></span>
        <span class="totem-led {{ $totem ? '' : 'is-off' }}" title="{{ $totem ? 'Equipo activo · Último ping ' . ($totem->ultimo_ping?->format('d-m-Y H:i:s') ?? 'sin registro') : 'Sin equipo activo' }}"></span>
      </div>
    </div>
    <div class="totem-neck" aria-hidden="true"></div>
    <div class="totem-base" aria-hidden="true"></div>
  </div>
</main>
<script>
document.querySelectorAll('.kiosk-tile').forEach(function (opcion) {
    opcion.addEventListener('click', function (event) { event.stopPropagation(); });
});
(function () {
    var cuerpo = document.querySelector('.kiosk-body');
    var pantalla = document.querySelector('.kiosk-screen');
    var escena = document.querySelector('.kiosk-stage');
    if (!cuerpo || !pantalla || !escena) return;
    function aplicar(menu, panel) {
        pantalla.style.setProperty('--k-menu', menu.toFixed(3));
        escena.style.setProperty('--k-fit', panel.toFixed(3));
        cuerpo.scrollTop = 0;
        var limite = cuerpo.getBoundingClientRect().bottom - parseFloat(getComputedStyle(cuerpo).paddingBottom);
        return escena.getBoundingClientRect().bottom <= limite - 2 && cuerpo.scrollHeight <= cuerpo.clientHeight;
    }
    function mejorPanel(menu) {
        var minimo = 0.3, maximo = 1.6;
        if (aplicar(menu, maximo)) return maximo;
        for (var i = 0; i < 10; i++) {
            var medio = (minimo + maximo) / 2;
            if (aplicar(menu, medio)) minimo = medio; else maximo = medio;
        }
        return minimo;
    }
    function letraMinima() {
        var menor = Infinity;
        escena.querySelectorAll('p, li, small, .kiosk-hint').forEach(function (elemento) {
            menor = Math.min(menor, parseFloat(getComputedStyle(elemento).fontSize));
        });
        return menor;
    }
    function ajustar() {
        cuerpo.style.overflowY = 'hidden';
        escena.style.flexGrow = '0';
        var menu = 1;
        var panel = mejorPanel(menu);
        if (panel < 0.9) {
            var bajo = 0.65, alto = 1;
            for (var i = 0; i < 8; i++) {
                var medio = (bajo + alto) / 2;
                if (aplicar(medio, medio)) bajo = medio; else alto = medio;
            }
            menu = bajo;
            panel = mejorPanel(menu);
        }
        aplicar(menu, panel);
        var piso = pantalla.clientWidth < 560 ? 9 : 6.5;
        var actual = letraMinima();
        if (actual < piso) {
            panel = Math.min(1.6, panel * piso / actual);
            if (!aplicar(menu, panel) && aplicar(0.7, panel)) {
                var menor = 0.7, mayor = menu;
                for (var j = 0; j < 8; j++) {
                    var punto = (menor + mayor) / 2;
                    if (aplicar(punto, panel)) menor = punto; else mayor = punto;
                }
                menu = menor;
            }
            aplicar(menu, panel);
        }
        escena.style.flexGrow = '';
        cuerpo.style.overflowY = '';
    }
    ajustar();
    window.addEventListener('load', ajustar);
    if (document.fonts) {
        if (document.fonts.ready) document.fonts.ready.then(ajustar);
        document.fonts.addEventListener('loadingdone', ajustar);
    }
    if (window.ResizeObserver) new ResizeObserver(ajustar).observe(pantalla);
    cuerpo.addEventListener('toggle', ajustar, true);
})();
</script>

@if($soloCompra)
<style>
.modal.demo-modal-visible{display:block;overflow-y:auto}
body.demo-modal-open{overflow:hidden}
.kiosk-screen>#modalReservaMedsdiTotem{position:absolute;inset:0;z-index:30;background:#f4f8fd;color:var(--k-ink)}
#modalReservaMedsdiTotem .modal-dialog{display:block;width:100%;max-width:none;min-height:100%;margin:0;transform:none}
#modalReservaMedsdiTotem .modal-content{min-height:100%;border:0;border-radius:0;box-shadow:none;background:#f4f8fd}
#modalReservaMedsdiTotem .modal-header{position:sticky;top:0;z-index:2;align-items:center;gap:12px;padding:12px 14px;border:0;border-radius:0;background:linear-gradient(120deg,#123d86,#1848a1 55%,#2aa9b4);color:#fff}
#modalReservaMedsdiTotem .k-modal-kicker{color:#cdeff0;font-size:10.5px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
#modalReservaMedsdiTotem .modal-title{margin:2px 0 0;color:#fff;font-size:17px;font-weight:800;line-height:1.2}
#modalReservaMedsdiTotem .btn-close{flex:none;width:42px;height:42px;margin:0 0 0 auto;padding:0;border-radius:50%;opacity:1;filter:none;background:rgba(255,255,255,.16) url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23fff'%3e%3cpath d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg%3e") center/15px no-repeat}
#modalReservaMedsdiTotem .btn-close:hover{background-color:rgba(255,255,255,.28)}
#modalReservaMedsdiTotem .modal-body{padding:12px}
#modalReservaMedsdiTotem .k-step{margin-bottom:12px;padding:14px;border:1px solid var(--k-line);border-radius:16px;background:#fff}
#modalReservaMedsdiTotem .k-step-title{display:flex;align-items:center;gap:10px;margin-bottom:12px;color:var(--k-ink);font-size:16px;font-weight:800}
#modalReservaMedsdiTotem .k-step-title b{flex:none;display:grid;place-items:center;width:30px;height:30px;border-radius:50%;background:var(--k-blue);color:#fff;font-size:14px}
#modalReservaMedsdiTotem .form-label{margin-bottom:6px;color:var(--k-muted);font-size:11.5px;font-weight:800;letter-spacing:.05em;text-transform:uppercase}
#modalReservaMedsdiTotem .form-control,#modalReservaMedsdiTotem .form-select{min-height:50px;padding:10px 14px;border:2px solid #c9d8ef;border-radius:14px;color:var(--k-ink);font-size:16px;font-weight:600}
#modalReservaMedsdiTotem .form-select{padding-right:40px;background-position:right 14px center;background-size:16px 12px}
#modalReservaMedsdiTotem .form-control:focus,#modalReservaMedsdiTotem .form-select:focus{border-color:var(--k-blue);box-shadow:0 0 0 4px rgba(24,72,161,.15)}
#modalReservaMedsdiTotem .form-control:disabled,#modalReservaMedsdiTotem .form-select:disabled{background-color:#eef2f7;color:#8a98ac}
#modalReservaMedsdiTotem .form-control[readonly]{background:#f3f7fd}
#modalReservaMedsdiTotem #medsdiRutBuscar{font-size:20px;font-weight:700;letter-spacing:.05em}
#modalReservaMedsdiTotem .input-group{flex-wrap:nowrap;gap:8px}
#modalReservaMedsdiTotem .input-group>*{margin-left:0!important;border-radius:14px!important}
#modalReservaMedsdiTotem .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:50px;padding:10px 18px;border-radius:14px;font-size:15.5px;font-weight:800;transition:transform .12s ease,filter .2s ease}
#modalReservaMedsdiTotem .btn:active{transform:scale(.97)}
#modalReservaMedsdiTotem .btn:disabled{opacity:.5}
#modalReservaMedsdiTotem .btn-sm{min-height:42px;padding:8px 14px;border-radius:12px;font-size:14px}
#modalReservaMedsdiTotem .btn-primary{border:0;background:linear-gradient(135deg,var(--k-blue),var(--k-blue-2));box-shadow:0 8px 18px rgba(24,72,161,.24)}
#modalReservaMedsdiTotem .btn-info{border:0;background:var(--k-teal-ink);color:#fff}
#modalReservaMedsdiTotem .btn-outline-primary{border:2px solid #bcd0ee;background:#fff;color:var(--k-blue)}
#modalReservaMedsdiTotem .btn-outline-primary:hover{border-color:var(--k-blue);background:var(--k-blue);color:#fff}
#modalReservaMedsdiTotem .btn-outline-secondary{border:2px solid #c9d3e1;background:#fff;color:var(--k-muted)}
#modalReservaMedsdiTotem .btn-outline-success{border:2px solid #7fd6c9;background:#effbf8;color:#0b6b5f}
#modalReservaMedsdiTotem .btn-outline-success:hover{border-color:var(--k-ok);background:var(--k-ok);color:#fff}
#modalReservaMedsdiTotem #medsdiFormConfirmar .btn-success{min-height:64px;border:0;border-radius:18px;background:linear-gradient(135deg,var(--k-ok),var(--k-ok-2));font-size:18px;box-shadow:0 10px 22px rgba(14,133,119,.3)}
#modalReservaMedsdiTotem .alert{padding:12px 14px;border:0;border-radius:14px;font-size:14.5px;font-weight:600}
#modalReservaMedsdiTotem .alert.d-flex{flex-wrap:wrap}
#modalReservaMedsdiTotem .alert-info{background:#edf4ff;color:#1d4c94}
#modalReservaMedsdiTotem .alert-success{background:#e2f7f2;color:#0b6b5f}
#modalReservaMedsdiTotem .alert-warning{background:#fff4dc;color:#7d5700}
#modalReservaMedsdiTotem .list-group{border:1px solid var(--k-line);border-radius:14px}
#modalReservaMedsdiTotem .list-group-item{padding:12px 14px;border-color:var(--k-line);font-size:15px}
#modalReservaMedsdiTotem .list-group-item-action:hover{background:#edf4ff}
#modalReservaMedsdiTotem #medsdiPacienteResultado,#modalReservaMedsdiTotem #medsdiDiasAtencion{font-size:14.5px}
#modalReservaMedsdiTotem #medsdiResultados .card{border:1px solid var(--k-line)!important;border-radius:16px;box-shadow:0 6px 16px rgba(15,42,92,.06)!important}
#modalReservaMedsdiTotem #medsdiResultados .card-body{padding:14px!important}
#modalReservaMedsdiTotem #medsdiResultados strong{font-size:16px}
#modalReservaMedsdiTotem #medsdiHoras{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(84px,1fr));gap:8px!important}
#modalReservaMedsdiTotem #medsdiHoras>span{grid-column:1/-1}
#modalReservaMedsdiTotem .medsdi-elegir-hora{min-height:54px;font-size:18px;font-variant-numeric:tabular-nums}
.flatpickr-calendar{font-family:'Manrope',sans-serif}
@container pantalla (max-width:560px){#modalReservaMedsdiTotem .input-group{flex-wrap:wrap}#modalReservaMedsdiTotem .input-group>.btn{width:100%}#modalReservaMedsdiTotem .row>[class*="col-"]{flex:0 0 100%;width:100%;max-width:100%}}
</style>

<div class="modal fade" id="modalReservaMedsdiTotem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header">
            <div><div class="k-modal-kicker">Conectado a Med-SDI</div><h2 class="modal-title">Buscar profesional y reservar hora</h2></div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
            <div id="medsdiEstadoServicio" class="alert alert-warning d-none"></div>

            <div class="k-step">
                <div class="k-step-title"><b>1</b>Beneficiario</div>
                <label class="form-label fw-bold" for="medsdiRutBuscar">RUT del paciente</label>
                <div class="input-group">
                    <input type="text" id="medsdiRutBuscar" class="form-control" autocomplete="off" placeholder="Ej.: 17.174.188-2" data-rut-input>
                    <button type="button" id="medsdiValidarPaciente" class="btn btn-primary">Validar paciente</button>
                </div>
                <div id="medsdiPacienteResultado" class="small mt-2 d-none"></div>
                <div id="medsdiResponsableWrap" class="mt-3 d-none">
                    <label for="medsdiResponsable" class="form-label small fw-bold">Titular responsable</label>
                    <select id="medsdiResponsable" class="form-select"></select>
                </div>
            </div>

            <div class="k-step">
                <div id="medsdiPrestacionForm">
                    <div class="k-step-title"><b>2</b>Prestación FONASA</div>
                    <label class="form-label small" for="medsdiPrestacionBuscar">Busca por nombre o código</label>
                    <div class="input-group">
                        <input type="search" id="medsdiPrestacionBuscar" class="form-control" autocomplete="off" placeholder="Ej.: consulta médica o 0101001" disabled>
                        <button type="button" id="medsdiPrestacionBuscarBtn" class="btn btn-info text-white" disabled>Buscar</button>
                    </div>
                    <div id="medsdiPrestacionResultados" class="list-group mt-2 d-none" style="max-height:260px;overflow-y:auto"></div>
                </div>
                <div id="medsdiPrestacionSeleccionada" class="alert alert-info mt-3 mb-0 d-none d-flex justify-content-between align-items-center gap-2">
                    <span id="medsdiPrestacionSeleccionadaTexto"></span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="medsdiCambiarPrestacion">Cambiar</button>
                </div>
            </div>

            <div id="medsdiPasoBusqueda" class="d-none k-step">
                <div class="k-step-title"><b>3</b>Ubicación y profesional</div>
                <div id="medsdiUbicacionBuscadorWrap">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small">Región</label>
                            <select id="medsdiRegion" class="form-select">
                                <option value="">Centro INSI (predeterminado)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Ciudad</label>
                            <select id="medsdiCiudad" class="form-select" disabled>
                                <option value="">Primero seleccione una región</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-4"><label class="form-label small">Especialidad</label><select id="medsdiEspecialidad" class="form-select"><option value="">Todas</option></select></div>
                        <div class="col-md-4"><label class="form-label small">Tipo</label><select id="medsdiTipoEspecialidad" class="form-select" disabled><option value="">Todos</option></select></div>
                        <div class="col-md-4"><label class="form-label small">Subtipo</label><select id="medsdiSubTipoEspecialidad" class="form-select" disabled><option value="">Todos</option></select></div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" id="medsdiNombreProfesional" class="form-control" placeholder="Buscar por nombre del profesional (opcional)">
                        <button type="button" id="medsdiBuscarBtn" class="btn btn-primary">Buscar profesionales</button>
                    </div>
                    <div id="medsdiResultados" class="row g-2"></div>
                </div>
            </div>

            <div id="medsdiPasoCotizacion" class="d-none k-step">
                <div class="k-step-title"><b>4</b>Cotización</div>
                <div id="medsdiCotizacion" class="alert alert-info mb-0">Calculando convenio FONASA...</div>
            </div>

            <div id="medsdiPasoHorario" class="d-none k-step">
                <div class="k-step-title"><b>5</b>Fecha y hora</div>
                <div id="medsdiProfesionalElegido" class="alert alert-info d-flex justify-content-between align-items-center gap-2">
                    <span id="medsdiProfesionalElegidoTexto"></span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="medsdiCambiarProfesional">Cambiar</button>
                </div>
                <div id="medsdiDiasAtencion" class="small fw-semibold text-primary mb-2">Consultando días de atención...</div>
                <div class="row g-2 align-items-end mb-3">
                    <div class="col-md-7"><label class="form-label small" for="medsdiFecha">Fecha</label><input type="text" id="medsdiFecha" class="form-control" placeholder="Seleccione un día disponible" disabled></div>
                    <div class="col-md-5"><button type="button" id="medsdiVerHorasBtn" class="btn btn-outline-primary w-100">Ver horas disponibles</button></div>
                </div>
                <div id="medsdiHoras" class="d-flex flex-wrap gap-2"></div>
            </div>

            <form id="medsdiFormConfirmar" method="POST" action="{{ route('totem.reserva.agendar') }}" class="d-none k-step">
                @csrf
                <input type="hidden" name="id_profesional" id="medsdiInputProfesionalId">
                <input type="hidden" name="id_especialidad" id="medsdiInputEspecialidadId">
                <input type="hidden" name="nombre_profesional" id="medsdiInputProfesionalNombre">
                <input type="hidden" name="especialidad" id="medsdiInputEspecialidad">
                <input type="hidden" name="id_lugar" id="medsdiInputLugarId">
                <input type="hidden" name="lugar_nombre" id="medsdiInputLugarNombre">
                <input type="hidden" name="direccion" id="medsdiInputDireccion">
                <input type="hidden" name="fecha_hora" id="medsdiInputFechaHora">
                <input type="hidden" name="id_prestacion" id="medsdiInputPrestacionId">
                <input type="hidden" name="origen_prestacion" id="medsdiInputPrestacionOrigen">
                <input type="hidden" name="prestacion_codigo" id="medsdiInputPrestacionCodigo">
                <input type="hidden" name="prestacion_nombre" id="medsdiInputPrestacionNombre">
                <div class="k-step-title"><b>6</b>Confirmación</div>
                <div id="medsdiResumenReserva" class="alert alert-success d-flex justify-content-between align-items-center gap-2">
                    <span id="medsdiResumenReservaTexto"></span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="medsdiCambiarHora">Cambiar hora</button>
                </div>
                <div class="mb-3">
                    <label class="form-label">Paciente validado</label>
                    <input type="text" name="rut" id="medsdiRutPaciente" class="form-control" value="" readonly>
                    <input type="hidden" name="titular_rut" id="medsdiRutTitular">
                    <div class="form-text" id="medsdiPacienteConfirmacion"></div>
                </div>
                <button type="submit" class="btn btn-success w-100">Confirmar reserva y generar bono</button>
            </form>
        </div>
    </div></div>
</div>
<script>
document.querySelector('.kiosk-screen')?.appendChild(document.getElementById('modalReservaMedsdiTotem'));
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function mostrarModalDemo(modal) {
        if (!modal) return;
        document.querySelectorAll('.modal.demo-modal-visible').forEach(item => ocultarModalDemo(item));
        modal.classList.add('show', 'demo-modal-visible');
        modal.style.display = 'block';
        modal.removeAttribute('aria-hidden');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('role', 'dialog');
        document.body.classList.add('demo-modal-open');
    }
    function ocultarModalDemo(modal) {
        if (!modal) return;
        modal.classList.remove('show', 'demo-modal-visible');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        modal.removeAttribute('aria-modal');
        modal.removeAttribute('role');
        if (!document.querySelector('.modal.demo-modal-visible')) document.body.classList.remove('demo-modal-open');
    }
    document.getElementById('abrirReservaMedsdiTotem')?.addEventListener('click', () => { mostrarModalDemo(document.getElementById('modalReservaMedsdiTotem')); inicializarReservaMedsdi(); });
    document.querySelectorAll('#modalReservaMedsdiTotem [data-bs-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', () => ocultarModalDemo(button.closest('.modal')));
    });

    // --- Reserva de hora vía API real Med-SDI (igual que escritorio de paciente) ---
    let medsdiInicializado = false;
    let medsdiSeleccion = null;
    let medsdiPrestacion = null;
    let medsdiCotizacionActual = null;
    let medsdiPrestacionTimer = null;
    let medsdiCalendario = null;
    let medsdiPaciente = null;
    const medsdiEstado = document.getElementById('medsdiEstadoServicio');
    const medsdiRutBuscar = document.getElementById('medsdiRutBuscar');
    const medsdiPacienteResultado = document.getElementById('medsdiPacienteResultado');
    const medsdiResponsableWrap = document.getElementById('medsdiResponsableWrap');
    const medsdiResponsable = document.getElementById('medsdiResponsable');
    const medsdiRegion = document.getElementById('medsdiRegion');
    const medsdiCiudad = document.getElementById('medsdiCiudad');
    const medsdiEspecialidad = document.getElementById('medsdiEspecialidad');
    const medsdiTipoEspecialidad = document.getElementById('medsdiTipoEspecialidad');
    const medsdiSubTipoEspecialidad = document.getElementById('medsdiSubTipoEspecialidad');
    const medsdiResultados = document.getElementById('medsdiResultados');
    const medsdiPasoHorario = document.getElementById('medsdiPasoHorario');
    const medsdiPasoBusqueda = document.getElementById('medsdiPasoBusqueda');
    const medsdiPasoCotizacion = document.getElementById('medsdiPasoCotizacion');
    const medsdiCotizacion = document.getElementById('medsdiCotizacion');
    const medsdiPrestacionBuscar = document.getElementById('medsdiPrestacionBuscar');
    const medsdiPrestacionResultados = document.getElementById('medsdiPrestacionResultados');
    const medsdiPrestacionSeleccionada = document.getElementById('medsdiPrestacionSeleccionada');
    const medsdiHoras = document.getElementById('medsdiHoras');
    const medsdiFormConfirmar = document.getElementById('medsdiFormConfirmar');
    const medsdiFecha = document.getElementById('medsdiFecha');
    const medsdiDiasAtencion = document.getElementById('medsdiDiasAtencion');

    function medsdiMostrarAviso(mensaje) {
        medsdiEstado.textContent = mensaje;
        medsdiEstado.classList.remove('d-none');
    }

    function medsdiBloquearFlujoPaciente() {
        medsdiPaciente = null;
        medsdiPrestacion = null;
        medsdiPrestacionBuscar.value = '';
        medsdiPrestacionBuscar.disabled = true;
        document.getElementById('medsdiPrestacionBuscarBtn').disabled = true;
        document.getElementById('medsdiPrestacionResultados').classList.add('d-none');
        document.getElementById('medsdiPrestacionSeleccionada').classList.add('d-none');
        document.getElementById('medsdiPasoBusqueda').classList.add('d-none');
        medsdiLimpiarSeleccion();
    }

    document.getElementById('medsdiValidarPaciente')?.addEventListener('click', async () => {
        const rut = medsdiRutBuscar.value.trim();
        medsdiBloquearFlujoPaciente();
        medsdiResponsableWrap.classList.add('d-none');
        medsdiPacienteResultado.className = 'small mt-2 text-muted';
        medsdiPacienteResultado.textContent = 'Validando paciente en Med-SDI...';
        if (!rut) {
            medsdiPacienteResultado.className = 'small mt-2 text-danger';
            medsdiPacienteResultado.textContent = 'Ingrese el RUT del paciente.';
            return;
        }

        const data = await medsdiFetchJson(`{{ route('totem.reserva.paciente') }}?rut=${encodeURIComponent(rut)}`);
        if (!data.ok || !data.paciente) {
            medsdiPacienteResultado.className = 'small mt-2 text-danger';
            medsdiPacienteResultado.textContent = data.mensaje || data.message || 'Paciente no encontrado.';
            return;
        }

        const paciente = data.paciente;
        const esDependiente = paciente.tipo === 'dependiente' || Boolean(paciente.es_dependiente);
        const responsables = Array.isArray(paciente.responsables) ? paciente.responsables : [];
        const titularInicial = esDependiente
            ? (responsables.length === 1 ? responsables[0] : null)
            : (paciente.titular || null);
        if (esDependiente && responsables.length === 0) {
            medsdiPacienteResultado.className = 'small mt-2 text-danger';
            medsdiPacienteResultado.textContent = 'El paciente figura como dependiente, pero no tiene un titular responsable vigente.';
            return;
        }

        medsdiPaciente = {...paciente, titular: titularInicial};
        medsdiRutBuscar.value = paciente.rut || rut;
        medsdiRutBuscar.dispatchEvent(new Event('input', {bubbles: true}));
        document.getElementById('medsdiRutPaciente').value = paciente.rut || rut;
        document.getElementById('medsdiRutTitular').value = titularInicial?.rut || (esDependiente ? '' : (paciente.rut || rut));
        const detalle = esDependiente
            ? `Dependiente (${paciente.parentesco || 'Carga'}) · Responsable: ${titularInicial?.nombre_completo || 'identificado'}`
            : 'Titular';
        medsdiPacienteResultado.className = 'small mt-2 text-success fw-semibold';
        medsdiPacienteResultado.textContent = `✓ ${paciente.nombre_completo} · RUT ${paciente.rut} · ${detalle}`;
        document.getElementById('medsdiPacienteConfirmacion').textContent = `${paciente.nombre_completo} · ${detalle}`;

        if (esDependiente && responsables.length > 1) {
            medsdiResponsable.innerHTML = '<option value="">Seleccione al titular responsable</option>';
            responsables.forEach(responsable => medsdiResponsable.add(new Option(
                `${responsable.nombre_completo || 'Titular'} · ${responsable.rut}`,
                responsable.rut
            )));
            medsdiResponsableWrap.classList.remove('d-none');
        }
        const pacienteListo = !esDependiente || Boolean(titularInicial);
        medsdiPrestacionBuscar.disabled = !pacienteListo;
        document.getElementById('medsdiPrestacionBuscarBtn').disabled = !pacienteListo;
        if (pacienteListo) medsdiPrestacionBuscar.focus();
    });

    medsdiResponsable?.addEventListener('change', () => {
        const responsable = (medsdiPaciente?.responsables || []).find(item => item.rut === medsdiResponsable.value);
        if (!responsable) return;
        medsdiPaciente.titular = responsable;
        document.getElementById('medsdiRutTitular').value = responsable.rut;
        document.getElementById('medsdiPacienteConfirmacion').textContent = `${medsdiPaciente.nombre_completo} · Dependiente · Responsable: ${responsable.nombre_completo}`;
        medsdiPrestacionBuscar.disabled = false;
        document.getElementById('medsdiPrestacionBuscarBtn').disabled = false;
        medsdiPrestacionBuscar.focus();
    });

    medsdiRutBuscar?.addEventListener('keydown', event => {
        if (event.key === 'Enter') {
            event.preventDefault();
            document.getElementById('medsdiValidarPaciente').click();
        }
    });

    function medsdiLimpiarSeleccion() {
        medsdiSeleccion = null;
        medsdiCotizacionActual = null;
        document.getElementById('medsdiUbicacionBuscadorWrap')?.classList.remove('d-none');
        medsdiPasoCotizacion.classList.add('d-none');
        medsdiPasoHorario.classList.add('d-none');
        medsdiHoras.innerHTML = '';
        medsdiFormConfirmar.classList.add('d-none');
        medsdiCalendario?.destroy();
        medsdiCalendario = null;
        medsdiFecha.value = '';
        medsdiFecha.disabled = true;
        medsdiDiasAtencion.textContent = '';
    }

    const medsdiNombresDias = ['', 'LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SÁBADO', 'DOMINGO'];

    async function medsdiCargarDiasLaborales() {
        medsdiDiasAtencion.textContent = 'Consultando días de atención...';
        const params = new URLSearchParams({
            id_profesional: medsdiSeleccion.idProfesional,
            id_lugar: medsdiSeleccion.idLugar,
        });
        const resp = await medsdiFetchJson(`{{ route('totem.reserva.dias_laborales') }}?${params.toString()}`);
        const dias = String(resp.registros?.horario_agenda_laboral || '')
            .split(',').map(Number).filter(dia => dia >= 1 && dia <= 7);

        if (!resp.ok || !dias.length) {
            medsdiDiasAtencion.textContent = 'El profesional no tiene días de atención informados.';
            medsdiFecha.disabled = true;
            return;
        }

        medsdiDiasAtencion.textContent = `Atiende los días: ${dias.map(dia => medsdiNombresDias[dia]).join(' · ')}`;
        medsdiFecha.disabled = false;
        medsdiCalendario = flatpickr(medsdiFecha, {
            disableMobile: true,
            minDate: 'today',
            maxDate: new Date().fp_incr(60),
            dateFormat: 'Y-m-d',
            disable: [date => !dias.includes(date.getDay() === 0 ? 7 : date.getDay())],
            locale: {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                },
                months: {
                    shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                },
            },
            onChange: () => document.getElementById('medsdiVerHorasBtn').click(),
        });
    }

    async function medsdiFetchJson(url) {
        const respuesta = await fetch(url, { headers: { 'Accept': 'application/json' } });
        return respuesta.json();
    }

    async function medsdiPostJson(url, data) {
        const respuesta = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(data),
        });
        const payload = await respuesta.json();
        if (!respuesta.ok && !payload.mensaje) payload.mensaje = 'La solicitud no pudo ser procesada.';
        return payload;
    }

    async function medsdiBuscarPrestaciones() {
        const buscar = medsdiPrestacionBuscar.value.trim();
        if (buscar.length < 2) {
            medsdiPrestacionResultados.classList.add('d-none');
            return;
        }
        medsdiPrestacionResultados.innerHTML = '<div class="list-group-item text-muted">Buscando prestaciones...</div>';
        medsdiPrestacionResultados.classList.remove('d-none');
        const params = new URLSearchParams({buscar});
        const resp = await medsdiFetchJson(`{{ route('totem.reserva.prestaciones') }}?${params.toString()}`);
        medsdiPrestacionResultados.innerHTML = '';
        if (!resp.ok || !(resp.registros || []).length) {
            const vacio = document.createElement('div');
            vacio.className = 'list-group-item text-muted';
            vacio.textContent = resp.mensaje || 'No se encontraron prestaciones.';
            medsdiPrestacionResultados.appendChild(vacio);
            return;
        }
        resp.registros.forEach(prestacion => {
            const boton = document.createElement('button');
            boton.type = 'button';
            boton.className = 'list-group-item list-group-item-action text-start';
            boton.dataset.prestacion = JSON.stringify(prestacion);
            boton.innerHTML = `<strong>${prestacion.codigo}</strong> · ${prestacion.nombre}`;
            medsdiPrestacionResultados.appendChild(boton);
        });
    }

    document.getElementById('medsdiPrestacionBuscarBtn')?.addEventListener('click', medsdiBuscarPrestaciones);
    medsdiPrestacionBuscar?.addEventListener('input', () => {
        clearTimeout(medsdiPrestacionTimer);
        medsdiPrestacionTimer = setTimeout(medsdiBuscarPrestaciones, 300);
    });
    medsdiPrestacionResultados?.addEventListener('click', event => {
        const boton = event.target.closest('button[data-prestacion]');
        if (!boton) return;
        medsdiPrestacion = JSON.parse(boton.dataset.prestacion);
        medsdiPrestacionResultados.classList.add('d-none');
        document.getElementById('medsdiPrestacionSeleccionadaTexto').textContent = `${medsdiPrestacion.codigo} · ${medsdiPrestacion.nombre}`;
        medsdiPrestacionSeleccionada.classList.remove('d-none');
        document.getElementById('medsdiPrestacionForm').classList.add('d-none');
        medsdiPasoBusqueda.classList.remove('d-none');
        medsdiLimpiarSeleccion();
        medsdiResultados.innerHTML = '';
        medsdiPasoBusqueda.scrollIntoView({behavior: 'smooth', block: 'nearest'});
    });

    document.getElementById('medsdiCambiarPrestacion')?.addEventListener('click', () => {
        document.getElementById('medsdiPrestacionForm').classList.remove('d-none');
        medsdiPrestacionSeleccionada.classList.add('d-none');
        medsdiPasoBusqueda.classList.add('d-none');
        medsdiPrestacion = null;
        medsdiResultados.innerHTML = '';
        medsdiLimpiarSeleccion();
    });

    function medsdiLlenarSelect(select, registros, campoValor, campoTexto, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        registros.forEach(registro => {
            select.add(new Option(registro[campoTexto], registro[campoValor]));
        });
        select.disabled = registros.length === 0;
    }

    async function inicializarReservaMedsdi() {
        if (medsdiInicializado) return;
        medsdiInicializado = true;

        const [regiones, especialidades] = await Promise.all([
            medsdiFetchJson('{{ route('totem.reserva.regiones') }}'),
            medsdiFetchJson('{{ route('totem.reserva.especialidades') }}'),
        ]);
        if (regiones.ok) {
            medsdiLlenarSelect(medsdiRegion, regiones.registros || [], 'id', 'nombre', 'Centro INSI (predeterminado)');
            medsdiRegion.disabled = false;
        } else {
            medsdiMostrarAviso(regiones.mensaje || 'No fue posible cargar las regiones de Med-SDI.');
        }
        if (!especialidades.disponible) {
            medsdiMostrarAviso(especialidades.mensaje || 'Servicio Med-SDI no disponible en este momento.');
        }
        medsdiLlenarSelect(medsdiEspecialidad, especialidades.registros || [], 'id', 'nombre', 'Todas');
        medsdiEspecialidad.disabled = false;
    }

    medsdiRegion?.addEventListener('change', async () => {
        medsdiLimpiarSeleccion();
        medsdiResultados.innerHTML = '';
        medsdiLlenarSelect(medsdiCiudad, [], 'id', 'nombre', medsdiRegion.value ? 'Todas las ciudades' : 'Primero seleccione una región');
        if (!medsdiRegion.value) return;

        const resp = await medsdiFetchJson(`{{ route('totem.reserva.ciudades') }}?id_region=${medsdiRegion.value}`);
        medsdiLlenarSelect(medsdiCiudad, resp.registros || [], 'id', 'nombre', 'Todas las ciudades');
        medsdiCiudad.disabled = !resp.ok;
        if (!resp.ok) medsdiMostrarAviso(resp.mensaje || 'No fue posible cargar las ciudades de la región.');
    });

    medsdiCiudad?.addEventListener('change', () => {
        medsdiLimpiarSeleccion();
        medsdiResultados.innerHTML = '';
    });

    medsdiEspecialidad?.addEventListener('change', async () => {
        medsdiLimpiarSeleccion();
        medsdiResultados.innerHTML = '';
        medsdiLlenarSelect(medsdiTipoEspecialidad, [], 'id', 'nombre', 'Todos');
        medsdiLlenarSelect(medsdiSubTipoEspecialidad, [], 'id', 'nombre', 'Todos');
        if (!medsdiEspecialidad.value) return;
        const resp = await medsdiFetchJson(`{{ route('totem.reserva.tipo_especialidades') }}?id_especialidad=${medsdiEspecialidad.value}`);
        medsdiLlenarSelect(medsdiTipoEspecialidad, resp.registros || [], 'id', 'nombre', 'Todos');
    });

    medsdiTipoEspecialidad?.addEventListener('change', async () => {
        medsdiLimpiarSeleccion();
        medsdiResultados.innerHTML = '';
        medsdiLlenarSelect(medsdiSubTipoEspecialidad, [], 'id', 'nombre', 'Todos');
        if (!medsdiTipoEspecialidad.value) return;
        const resp = await medsdiFetchJson(`{{ route('totem.reserva.sub_tipo_especialidades') }}?id_tipo_especialidad=${medsdiTipoEspecialidad.value}`);
        medsdiLlenarSelect(medsdiSubTipoEspecialidad, resp.registros || [], 'id', 'nombre', 'Todos');
    });

    document.getElementById('medsdiBuscarBtn')?.addEventListener('click', async () => {
        medsdiLimpiarSeleccion();
        medsdiEstado.classList.add('d-none');
        medsdiResultados.innerHTML = '<div class="col-12 text-muted">Buscando en Med-SDI...</div>';
        const params = new URLSearchParams();
        if (medsdiRegion.value) params.set('id_region', medsdiRegion.value);
        if (medsdiCiudad.value) params.set('id_ciudad', medsdiCiudad.value);
        if (medsdiEspecialidad.value) params.set('id_especialidad', medsdiEspecialidad.value);
        if (medsdiTipoEspecialidad.value) params.set('id_tipo_especialidad', medsdiTipoEspecialidad.value);
        if (medsdiSubTipoEspecialidad.value) params.set('id_sub_tipo_especialidad', medsdiSubTipoEspecialidad.value);
        if (document.getElementById('medsdiNombreProfesional').value.trim()) params.set('nombre_profesional', document.getElementById('medsdiNombreProfesional').value.trim());

        const resp = await medsdiFetchJson(`{{ route('totem.reserva.profesionales') }}?${params.toString()}`);
        if (!resp.disponible) { medsdiResultados.innerHTML = ''; medsdiMostrarAviso(resp.mensaje); return; }
        if (!resp.ok || !(resp.registros || []).length) { medsdiResultados.innerHTML = `<div class="col-12 alert alert-warning mb-0">${resp.mensaje || 'No se encontraron profesionales para los filtros indicados.'}</div>`; return; }

        medsdiResultados.innerHTML = '';
        resp.registros.forEach(prof => {
            const nombreCompleto = `${prof.nombre} ${prof.apellido_uno || ''} ${prof.apellido_dos || ''}`.trim();
            const especialidadProfesional = prof.nombre_especialidad || medsdiEspecialidad.options[medsdiEspecialidad.selectedIndex]?.text || '';
            const lugares = (prof.lugares_atencion || []).map(lugar => `
                <button type="button" class="btn btn-outline-success btn-sm me-1 mb-1 medsdi-elegir-lugar"
                    data-id-profesional="${prof.id}" data-nombre-profesional="${nombreCompleto}"
                    data-especialidad="${especialidadProfesional}" data-id-lugar="${lugar.id}"
                    data-lugar-nombre="${lugar.nombre}">${lugar.nombre}</button>`).join('');
            const div = document.createElement('div');
            div.className = 'col-md-6';
            div.innerHTML = `<div class="card border-0 shadow-sm h-100"><div class="card-body p-3">
                <strong>${nombreCompleto}</strong><br>
                <span class="text-muted small">${especialidadProfesional}${prof.nombre_sub_tipo_especialidad ? ' · '+prof.nombre_sub_tipo_especialidad : ''}</span>
                <div class="mt-2">${lugares || '<span class="text-muted small">Sin lugares de atención disponibles.</span>'}</div>
            </div></div>`;
            medsdiResultados.appendChild(div);
        });
    });

    medsdiResultados?.addEventListener('click', async event => {
        const btn = event.target.closest('.medsdi-elegir-lugar');
        if (!btn) return;
        medsdiSeleccion = {
            idProfesional: btn.dataset.idProfesional, nombreProfesional: btn.dataset.nombreProfesional,
            idEspecialidad: medsdiEspecialidad.value, especialidad: btn.dataset.especialidad,
            idLugar: btn.dataset.idLugar, lugarNombre: btn.dataset.lugarNombre,
        };
        document.getElementById('medsdiUbicacionBuscadorWrap').classList.add('d-none');
        document.getElementById('medsdiProfesionalElegidoTexto').textContent = `${medsdiSeleccion.nombreProfesional} · ${medsdiSeleccion.especialidad} · ${medsdiSeleccion.lugarNombre}`;
        medsdiHoras.innerHTML = '';
        medsdiFormConfirmar.classList.add('d-none');
        medsdiPasoCotizacion.classList.remove('d-none');
        medsdiPasoHorario.classList.add('d-none');
        medsdiCotizacion.className = 'alert alert-info mb-0';
        medsdiCotizacion.textContent = 'Calculando convenio FONASA...';
        const respCotizacion = await medsdiPostJson('{{ route('totem.reserva.cotizar') }}', {
            id_profesional: medsdiSeleccion.idProfesional,
            id_lugar_atencion: medsdiSeleccion.idLugar,
            id_prestacion: medsdiPrestacion.id,
            origen_prestacion: medsdiPrestacion.origen,
        });
        if (!respCotizacion.ok) {
            medsdiCotizacion.className = 'alert alert-warning mb-0';
            medsdiCotizacion.textContent = respCotizacion.mensaje || 'No fue posible cotizar esta prestación.';
            return;
        }
        medsdiCotizacionActual = respCotizacion.cotizacion;
        const moneda = valor => new Intl.NumberFormat('es-CL', {style:'currency', currency:'CLP', maximumFractionDigits:0}).format(valor || 0);
        medsdiCotizacion.className = 'alert alert-success mb-0';
        medsdiCotizacion.textContent = `Valor ${moneda(medsdiCotizacionActual.valor)} · Bonificación ${moneda(medsdiCotizacionActual.bonificacion)} · Copago ${moneda(medsdiCotizacionActual.copago)} · Simulación FONASA`;
        medsdiPasoHorario.classList.remove('d-none');
        medsdiCargarDiasLaborales().catch(() => {
            medsdiDiasAtencion.textContent = 'No fue posible consultar los días de atención.';
            medsdiFecha.disabled = true;
        });
        medsdiPasoCotizacion.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    document.getElementById('medsdiVerHorasBtn')?.addEventListener('click', async () => {
        if (!medsdiSeleccion || !medsdiFecha.value) return;
        medsdiHoras.innerHTML = '<span class="text-muted">Consultando horas disponibles...</span>';
        const params = new URLSearchParams({
            id_profesional: medsdiSeleccion.idProfesional, id_lugar: medsdiSeleccion.idLugar,
            fecha: medsdiFecha.value,
        });
        const resp = await medsdiFetchJson(`{{ route('totem.reserva.horas_disponibles') }}?${params.toString()}`);
        if (!resp.disponible) { medsdiHoras.innerHTML = ''; medsdiMostrarAviso(resp.mensaje); return; }
        if (!resp.ok || !(resp.registros || []).length) { medsdiHoras.innerHTML = '<span class="text-muted">Sin horas disponibles para esta fecha.</span>'; return; }

        medsdiHoras.innerHTML = '';
        resp.registros.forEach(bloque => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-primary btn-sm medsdi-elegir-hora';
            btn.dataset.fechaHora = bloque.fecha_hora;
            btn.textContent = bloque.hora;
            medsdiHoras.appendChild(btn);
        });
    });

    medsdiHoras?.addEventListener('click', event => {
        const btn = event.target.closest('.medsdi-elegir-hora');
        if (!btn || !medsdiSeleccion) return;
        document.getElementById('medsdiInputProfesionalId').value = medsdiSeleccion.idProfesional;
        document.getElementById('medsdiInputEspecialidadId').value = medsdiSeleccion.idEspecialidad;
        document.getElementById('medsdiInputProfesionalNombre').value = medsdiSeleccion.nombreProfesional;
        document.getElementById('medsdiInputEspecialidad').value = medsdiSeleccion.especialidad;
        document.getElementById('medsdiInputLugarId').value = medsdiSeleccion.idLugar;
        document.getElementById('medsdiInputLugarNombre').value = medsdiSeleccion.lugarNombre;
        document.getElementById('medsdiInputFechaHora').value = btn.dataset.fechaHora;
        document.getElementById('medsdiInputPrestacionId').value = medsdiPrestacion.id;
        document.getElementById('medsdiInputPrestacionOrigen').value = medsdiPrestacion.origen;
        document.getElementById('medsdiInputPrestacionCodigo').value = medsdiPrestacion.codigo;
        document.getElementById('medsdiInputPrestacionNombre').value = medsdiPrestacion.nombre;
        document.getElementById('medsdiResumenReservaTexto').textContent = `${medsdiPrestacion.codigo} · ${medsdiPrestacion.nombre} · ${medsdiSeleccion.nombreProfesional} · ${medsdiSeleccion.lugarNombre} · ${btn.dataset.fechaHora} · Copago ${new Intl.NumberFormat('es-CL', {style:'currency', currency:'CLP', maximumFractionDigits:0}).format(medsdiCotizacionActual?.copago || 0)}`;
        medsdiPasoCotizacion.classList.add('d-none');
        medsdiPasoHorario.classList.add('d-none');
        medsdiFormConfirmar.classList.remove('d-none');
        medsdiFormConfirmar.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    document.getElementById('medsdiCambiarProfesional')?.addEventListener('click', () => {
        medsdiLimpiarSeleccion();
    });

    document.getElementById('medsdiCambiarHora')?.addEventListener('click', () => {
        medsdiFormConfirmar.classList.add('d-none');
        medsdiPasoCotizacion.classList.remove('d-none');
        medsdiPasoHorario.classList.remove('d-none');
        medsdiPasoHorario.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    medsdiFormConfirmar?.addEventListener('submit', async event => {
        event.preventDefault();
        if (!medsdiPaciente) return medsdiMostrarAviso('Vuelva a validar el RUT del paciente.');
        const boton = medsdiFormConfirmar.querySelector('button[type="submit"]');
        boton.disabled = true;
        const payload = Object.fromEntries(new FormData(medsdiFormConfirmar).entries());
        const data = await medsdiPostJson(medsdiFormConfirmar.action, payload);
        if (!data.ok) {
            boton.disabled = false;
            medsdiMostrarAviso(data.mensaje || 'No fue posible reservar la hora.');
            medsdiEstado.scrollIntoView({behavior: 'smooth', block: 'nearest'});
            return;
        }
        window.location.assign(data.redirect);
    });
});
</script>
@endif

@if($tab === 'autoatencion')
<script src="{{ asset('js/plugins/sweetalert.min.js') }}"></script>
<script>
window.addEventListener('pageshow', function () {
    document.querySelectorAll('[data-totem-identification-form]').forEach(function (form) {
        form.reset();
        form.querySelectorAll('input:not([type="hidden"])').forEach(function (input) {
            input.value = '';
        });
    });
});
</script>
@endif
@include('partials.rut_input_script')
@include('partials.demo_footer')
</body>
</html>
