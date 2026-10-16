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
        :root{--k-blue:#1848a1;--k-blue-2:#1c6fb8;--k-teal:#31bebe;--k-teal-ink:#157f86;--k-ink:#13233a;--k-muted:#5a6b82;--k-line:#d5e1f1;--k-soft:#eef4fb;--k-ok:#0e8577;--k-ok-2:#12a08e;--k-radius:28px;--k-touch:64px}
        *{box-sizing:border-box}
        html,body{font-family:'Manrope',sans-serif}
        body{margin:0;min-height:100vh;color:var(--k-ink);background:radial-gradient(1100px 560px at 8% -12%,#d9e8fb 0,transparent 62%),radial-gradient(900px 520px at 108% 6%,#d4f2f2 0,transparent 58%),#eef4fb}
        .totem-header-row{width:100%;margin:0}.totem-header-row>.col-12{min-width:0}.totem-header-row .demo-switcher{width:100%;max-width:100%;margin:0}
        .k-ico{width:24px;height:24px;flex:none;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}

        /* Pantalla del tótem */
        .kiosk{width:min(1240px,calc(100% - 40px));margin:22px auto 0}
        .kiosk-screen{display:flex;flex-direction:column;min-height:calc(100vh - 92px);background:#fff;border:1px solid #d7e2f2;border-radius:var(--k-radius);box-shadow:0 30px 80px rgba(15,42,92,.15);overflow:hidden}
        .kiosk-top{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:18px 32px;background:linear-gradient(120deg,#123d86,#1848a1 55%,#2aa9b4);color:#fff}
        .kiosk-brand{display:flex;align-items:center;gap:18px;min-width:0}
        .kiosk-brand img{display:block;height:40px;width:auto}
        .kiosk-brand-text{padding-left:18px;border-left:1px solid rgba(255,255,255,.32);min-width:0}
        .kiosk-brand-text strong{display:block;font-size:20px;font-weight:800;line-height:1.15}
        .kiosk-brand-text small{display:block;margin-top:3px;color:#cdeff0;font-size:11.5px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
        .kiosk-top-right{display:flex;align-items:center;gap:22px;flex:none}
        .kiosk-clock{text-align:right;line-height:1}
        .kiosk-clock strong{display:block;font-size:34px;font-weight:800;letter-spacing:-.01em;font-variant-numeric:tabular-nums}
        .kiosk-clock small{display:block;margin-top:5px;color:#d9eef7;font-size:13px;font-weight:600}.kiosk-clock small::first-letter{text-transform:uppercase}
        .kiosk-status{display:inline-flex;align-items:center;gap:9px;padding:10px 16px;border:1px solid rgba(255,255,255,.32);border-radius:999px;background:rgba(255,255,255,.14);font-size:13px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;white-space:nowrap}
        .kiosk-status i{width:10px;height:10px;border-radius:50%;background:#5ff0c6;animation:kPulse 2s infinite}
        .kiosk-status.is-off i{background:#ffb4a8;animation:none}
        @keyframes kPulse{0%{box-shadow:0 0 0 0 rgba(95,240,198,.65)}70%{box-shadow:0 0 0 10px rgba(95,240,198,0)}100%{box-shadow:0 0 0 0 rgba(95,240,198,0)}}

        .kiosk-body{flex:1;display:flex;flex-direction:column;gap:26px;padding:34px 40px 34px}
        .kiosk-welcome{text-align:center}
        .kiosk-eyebrow{display:inline-block;padding:7px 15px;border-radius:999px;background:#e3f5f5;color:var(--k-teal-ink);font-size:12.5px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
        .kiosk-welcome h1{margin:14px 0 8px;color:var(--k-ink);font-size:clamp(28px,3.4vw,44px);font-weight:800;letter-spacing:-.02em;line-height:1.1}
        .kiosk-welcome p{margin:0;color:var(--k-muted);font-size:clamp(16px,1.5vw,19px)}

        /* Menú de opciones táctiles */
        .kiosk-menu{display:grid;grid-template-columns:repeat(var(--k-cols,3),minmax(0,1fr));gap:18px}
        .kiosk-tile{position:relative;display:flex;flex-direction:column;gap:12px;min-height:168px;padding:22px;border:2px solid var(--k-line);border-radius:24px;background:#fff;color:var(--k-ink);text-decoration:none;box-shadow:0 10px 26px rgba(15,42,92,.07);transition:transform .14s ease,box-shadow .2s ease,border-color .2s ease;-webkit-tap-highlight-color:transparent}
        .kiosk-tile:hover{border-color:#9fbbe3;box-shadow:0 16px 34px rgba(15,42,92,.12);color:var(--k-ink)}
        .kiosk-tile:active{transform:scale(.97)}
        .kiosk-tile-icon{display:grid;place-items:center;width:60px;height:60px;border-radius:18px;background:#e8f0fb;color:var(--k-blue)}
        .kiosk-tile-icon .k-ico{width:32px;height:32px}
        .kiosk-tile-title{padding-right:44px;font-size:21px;font-weight:800;line-height:1.2}
        .kiosk-tile-desc{margin-top:-4px;color:var(--k-muted);font-size:14.5px;line-height:1.4}
        .kiosk-tile-go{position:absolute;top:22px;right:22px;display:grid;place-items:center;width:42px;height:42px;border-radius:50%;background:var(--k-soft);color:var(--k-blue)}
        .kiosk-tile-go .k-ico{width:22px;height:22px}
        .kiosk-tile.is-active{border-color:transparent;background:linear-gradient(140deg,#1848a1,#1d6db6 60%,#239fb1);color:#fff;box-shadow:0 18px 40px rgba(24,72,161,.32)}
        .kiosk-tile.is-active .kiosk-tile-icon{background:rgba(255,255,255,.18);color:#fff}
        .kiosk-tile.is-active .kiosk-tile-desc{color:#dcebfa}
        .kiosk-tile.is-active .kiosk-tile-go{background:#fff;color:var(--k-blue)}

        /* Escenario de cada opción */
        .kiosk-stage{padding:32px;border:1px solid var(--k-line);border-radius:var(--k-radius);background:linear-gradient(180deg,#f6f9fe,#fff 70%)}
        .kiosk-stage-head{display:flex;align-items:center;gap:20px;margin-bottom:24px}
        .kiosk-stage-icon{flex:none;display:grid;place-items:center;width:76px;height:76px;border-radius:22px;background:linear-gradient(140deg,#dff4f4,#e4edfb);color:var(--k-blue)}
        .kiosk-stage-icon .k-ico{width:40px;height:40px}
        .kiosk-stage h2{margin:0;color:var(--k-ink);font-size:clamp(23px,2.5vw,31px);font-weight:800;letter-spacing:-.01em;line-height:1.15}
        .kiosk-stage-head p{margin:6px 0 0;color:var(--k-muted);font-size:17px;line-height:1.45}
        .kiosk-steps{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin:0 0 26px;padding:0;list-style:none}
        .kiosk-steps li{display:flex;align-items:center;gap:12px;padding:16px;border:1px solid var(--k-line);border-radius:18px;background:#fff;font-size:15.5px;font-weight:700;line-height:1.3}
        .kiosk-steps b{flex:none;display:grid;place-items:center;width:38px;height:38px;border-radius:50%;background:var(--k-blue);color:#fff;font-size:17px;font-weight:800}
        .kiosk-note{display:flex;align-items:center;justify-content:center;gap:10px;margin:18px 0 0;color:var(--k-muted);font-size:16px;font-weight:600;text-align:center}
        .kiosk-note .k-ico{color:var(--k-teal-ink)}
        .kiosk-note.is-warn{color:#8a5a00}.kiosk-note.is-warn .k-ico{color:#c98a00}
        .kiosk-link{display:inline-flex;align-items:center;gap:8px;margin-top:20px;padding:12px 4px;color:var(--k-blue);font-size:17px;font-weight:800;text-decoration:none}
        .kiosk-link:hover{color:var(--k-blue-2);text-decoration:underline}

        /* Botones de tótem */
        .k-btn{display:flex;align-items:center;justify-content:center;gap:12px;width:100%;min-height:var(--k-touch);margin:0;padding:14px 26px;border:0;border-radius:20px;background:linear-gradient(135deg,var(--k-blue),var(--k-blue-2));color:#fff;font-family:inherit;font-size:20px;font-weight:800;line-height:1.2;text-align:center;text-decoration:none;cursor:pointer;box-shadow:0 12px 26px rgba(24,72,161,.26),inset 0 -3px 0 rgba(0,0,0,.14);transition:transform .12s ease,box-shadow .2s ease,filter .2s ease;-webkit-tap-highlight-color:transparent;user-select:none}
        .k-btn:hover{color:#fff;filter:brightness(1.07)}
        .k-btn:active{transform:translateY(2px) scale(.985);box-shadow:0 5px 12px rgba(24,72,161,.24),inset 0 -1px 0 rgba(0,0,0,.14)}
        .k-btn:disabled,.k-btn[aria-disabled="true"]{background:#c9d3e1;color:#f4f7fb;box-shadow:none;cursor:not-allowed;transform:none;filter:none;animation:none}
        .k-btn .k-ico{width:26px;height:26px}
        .k-btn--ok{background:linear-gradient(135deg,var(--k-ok),var(--k-ok-2));box-shadow:0 12px 26px rgba(14,133,119,.26),inset 0 -3px 0 rgba(0,0,0,.14)}
        .k-btn--ghost{border:2px solid #bcd0ee;background:#fff;color:var(--k-blue);box-shadow:none}
        .k-btn--ghost:hover{color:var(--k-blue);background:#f3f7fd;filter:none}
        .k-btn--xl{gap:18px;min-height:96px;padding:16px 18px 16px 22px;border-radius:26px;font-size:clamp(20px,2.2vw,26px)}
        .k-btn-lead,.k-btn-arrow{flex:none;display:grid;place-items:center;width:56px;height:56px;border-radius:50%}
        .k-btn-lead{background:rgba(255,255,255,.16)}
        .k-btn-arrow{background:#fff;color:var(--k-blue)}
        .k-btn:disabled .k-btn-arrow{color:#9aa8bc}
        .k-btn-lead .k-ico,.k-btn-arrow .k-ico{width:28px;height:28px}
        .k-btn-text{display:flex;flex-direction:column;align-items:flex-start;min-width:0;text-align:left}
        .k-btn-text small{margin-top:4px;font-size:15px;font-weight:600;opacity:.88}
        .k-btn--attract:not(:disabled){animation:kAttract 2.6s ease-out infinite}
        @keyframes kAttract{0%{box-shadow:0 12px 26px rgba(24,72,161,.26),0 0 0 0 rgba(49,190,190,.55)}70%,100%{box-shadow:0 12px 26px rgba(24,72,161,.26),0 0 0 18px rgba(49,190,190,0)}}
        .k-btn:focus-visible,.kiosk-tile:focus-visible,.kiosk-details summary:focus-visible{outline:4px solid rgba(49,190,190,.7);outline-offset:3px}

        /* Mensajes y formularios */
        .kiosk-flash{display:flex;align-items:center;gap:14px;padding:18px 22px;border-radius:20px;font-size:18px;font-weight:800;line-height:1.35}
        .kiosk-flash .k-ico{width:30px;height:30px}
        .kiosk-flash.ok{background:#e2f7f2;color:#0b6b5f}
        .kiosk-flash.error{background:#fde6e4;color:#9b2318}
        .kiosk-stage .kiosk-flash{margin-bottom:18px}
        .kiosk-hint{display:flex;align-items:flex-start;gap:12px;margin-top:18px;padding:16px 18px;border-radius:18px;background:#edf4ff;color:#1d4c94;font-size:15.5px;font-weight:600;line-height:1.45}
        .kiosk-hint .k-ico{margin-top:1px}
        .kiosk-label{display:block;margin:0 0 10px;color:var(--k-muted);font-size:15px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
        .kiosk-field{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:14px}
        .kiosk-field input{width:100%;min-height:76px;padding:0 24px;border:2px solid #bcd0ee;border-radius:20px;background:#fff;color:var(--k-ink);font-family:inherit;font-size:28px;font-weight:700;letter-spacing:.05em;text-transform:uppercase}
        .kiosk-field input::placeholder{color:#9fb0c6;font-weight:600;letter-spacing:.02em}
        .kiosk-field input:focus{outline:none;border-color:var(--k-blue);box-shadow:0 0 0 5px rgba(24,72,161,.15)}
        .kiosk-field .k-btn{width:auto;min-height:76px;padding:0 32px}
        .kiosk-error{margin-top:10px;color:#a32020;font-size:16px;font-weight:800}
        .kiosk-details{margin-top:6px}
        .kiosk-details summary{display:flex;align-items:center;justify-content:space-between;gap:12px;min-height:60px;padding:0 20px;border:2px dashed #bcd0ee;border-radius:18px;color:var(--k-blue);font-size:18px;font-weight:800;list-style:none;cursor:pointer}
        .kiosk-details summary::-webkit-details-marker{display:none}
        .kiosk-details summary .k-ico{transition:transform .2s ease}
        .kiosk-details[open] summary{margin-bottom:20px;border-style:solid;background:#f3f7fd}
        .kiosk-details[open] summary .k-ico{transform:rotate(180deg)}

        .kiosk-vouchers{display:grid;gap:16px;margin-top:22px}
        .kiosk-voucher{display:grid;grid-template-columns:auto minmax(0,1fr) auto;align-items:center;gap:20px;padding:22px;border:1px solid var(--k-line);border-radius:22px;background:#fff;box-shadow:0 10px 24px rgba(15,42,92,.06)}
        .kiosk-voucher-icon{display:grid;place-items:center;width:58px;height:58px;border-radius:18px;background:#e8f0fb;color:var(--k-blue)}
        .kiosk-voucher-icon .k-ico{width:30px;height:30px}
        .kiosk-voucher h3{margin:0 0 8px;font-size:20px;font-weight:800}
        .kiosk-voucher p{margin:3px 0;color:var(--k-muted);font-size:16px}
        .kiosk-voucher p strong{color:var(--k-ink)}
        .kiosk-voucher form{margin:0}
        .kiosk-voucher .k-btn{min-width:260px}
        .kiosk-badge{display:inline-flex;align-items:center;gap:8px;padding:12px 16px;border-radius:999px;background:#e2f7f2;color:#0b6b5f;font-size:15px;font-weight:800;white-space:nowrap}

        .kiosk-options{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .kiosk-option{display:flex;flex-direction:column;gap:12px;padding:26px;border:2px solid var(--k-line);border-radius:24px;background:#fff}
        .kiosk-option h3{margin:4px 0 0;font-size:22px;font-weight:800}
        .kiosk-option p{flex:1;margin:0 0 8px;color:var(--k-muted);font-size:16px;line-height:1.5}

        .kiosk-success{display:grid;grid-template-columns:auto minmax(0,1fr);align-items:center;gap:18px 20px;margin-bottom:26px;padding:24px;border:2px solid #7fd6c9;border-radius:24px;background:#effbf8}
        .kiosk-success-icon{display:grid;place-items:center;width:64px;height:64px;border-radius:50%;background:var(--k-ok);color:#fff}
        .kiosk-success-icon .k-ico{width:34px;height:34px}
        .kiosk-success h3{margin:0 0 4px;color:#0b6b5f;font-size:23px;font-weight:800}
        .kiosk-success p{margin:0;color:var(--k-muted);font-size:16.5px}
        .kiosk-success-actions{grid-column:1/-1;display:grid;grid-template-columns:1fr 1fr;gap:14px}

        /* Barra de estado del equipo */
        .kiosk-bar{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px 20px;padding:16px 32px;border-top:1px solid var(--k-line);background:#f3f7fd}
        .kiosk-bar-info{display:flex;flex-wrap:wrap;gap:10px}
        .kiosk-chip{display:inline-flex;align-items:center;gap:8px;padding:8px 13px;border:1px solid var(--k-line);border-radius:999px;background:#fff;color:var(--k-muted);font-size:13.5px;font-weight:600}
        .kiosk-chip strong{color:var(--k-ink);font-weight:800}
        .kiosk-chip .k-ico{width:16px;height:16px;color:var(--k-blue)}
        .kiosk-bar-help{display:flex;align-items:center;gap:16px;flex-wrap:wrap}
        .kiosk-help{display:inline-flex;align-items:center;gap:10px;color:var(--k-blue);font-size:15px;font-weight:800}
        .kiosk-admin{display:inline-flex;align-items:center;min-height:44px;padding:0 16px;border-radius:12px;background:#e3ebf7;color:#264d78;font-size:14px;font-weight:800;text-decoration:none}
        .kiosk-admin:hover{color:#1848a1;background:#d7e3f5}

        @media (prefers-reduced-motion: reduce){.kiosk-status i,.k-btn--attract:not(:disabled){animation:none}.kiosk-tile,.k-btn{transition:none}}

        /* Notebook */
        @media (max-width:1180px){
            .kiosk-body{padding:28px 30px}
            .kiosk-steps{grid-template-columns:repeat(2,minmax(0,1fr))}
        }
        @media (max-height:1000px) and (min-width:901px){
            .kiosk{margin-top:16px}
            .kiosk-top{padding-top:14px;padding-bottom:14px}
            .kiosk-body{gap:18px;padding-top:22px;padding-bottom:22px}
            .kiosk-welcome h1{margin:10px 0 4px;font-size:clamp(26px,2.9vw,38px)}
            .kiosk-tile{min-height:132px;padding:18px;gap:10px}
            .kiosk-tile-icon{width:52px;height:52px;border-radius:16px}
            .kiosk-tile-go{top:18px;right:18px}
            .kiosk-stage{padding:24px 26px}
            .kiosk-stage-head{margin-bottom:18px}
            .kiosk-stage-icon{width:64px;height:64px;border-radius:18px}
            .kiosk-steps{margin-bottom:18px}
            .kiosk-steps li{padding:12px 14px}
            .k-btn--xl{min-height:84px}
            .kiosk-note{margin-top:12px}
            .kiosk-bar{padding-top:12px;padding-bottom:12px}
        }
        @media (max-height:800px) and (min-width:901px){
            .kiosk-welcome p{display:none}
            .kiosk-tile{flex-direction:row;align-items:center;gap:14px;min-height:96px;padding:16px}
            .kiosk-tile-icon{flex:none}
            .kiosk-tile-body{flex:1;min-width:0}
            .kiosk-tile-title{padding-right:0;font-size:18.5px}
            .kiosk-tile-desc{margin-top:3px;font-size:13.5px}
            .kiosk-tile-go{position:static;flex:none;width:38px;height:38px}
            .kiosk-stage-head p{font-size:15.5px}
        }
        /* Tablet */
        @media (max-width:900px){
            .kiosk{width:calc(100% - 28px);margin-top:14px}
            .kiosk-top{padding:16px 22px}
            .kiosk-brand img{height:34px}
            .kiosk-brand-text strong{font-size:17px}
            .kiosk-clock strong{font-size:28px}
            .kiosk-tile{min-height:150px;padding:18px}
            .kiosk-tile-title{font-size:18.5px}
            .kiosk-stage{padding:24px}
            .kiosk-options{grid-template-columns:1fr}
            .kiosk-voucher{grid-template-columns:auto minmax(0,1fr)}
            .kiosk-voucher>form,.kiosk-voucher>.kiosk-badge{grid-column:1/-1}
            .kiosk-voucher .k-btn{min-width:0}
            .kiosk-bar{padding:16px 22px}
        }
        /* Celular */
        @media (max-width:700px){
            .kiosk{width:100%;margin:0}
            .kiosk-screen{min-height:0;border:0;border-radius:0;box-shadow:none}
            .kiosk-top{padding:14px 16px;gap:12px}
            .kiosk-brand{gap:12px}
            .kiosk-brand img{height:28px}
            .kiosk-brand-text{padding-left:12px}
            .kiosk-brand-text strong{font-size:15px}
            .kiosk-brand-text small{display:none}
            .kiosk-top-right{gap:12px}
            .kiosk-clock strong{font-size:22px}
            .kiosk-clock small{display:none}
            .kiosk-status{padding:8px 11px;font-size:11px}
            .kiosk-body{gap:20px;padding:22px 16px 26px}
            .kiosk-menu{grid-template-columns:1fr;gap:12px}
            .kiosk-tile{flex-direction:row;align-items:center;gap:14px;min-height:88px;padding:14px 16px}
            .kiosk-tile-icon{flex:none;width:52px;height:52px;border-radius:16px}
            .kiosk-tile-icon .k-ico{width:28px;height:28px}
            .kiosk-tile-body{flex:1;min-width:0}
            .kiosk-tile-title{padding-right:0;font-size:18px}
            .kiosk-tile-desc{margin-top:3px;font-size:13.5px}
            .kiosk-tile-go{position:static;flex:none;width:38px;height:38px}
            .kiosk-stage{padding:20px 16px;border-radius:22px}
            .kiosk-stage-head{align-items:flex-start;gap:14px;margin-bottom:18px}
            .kiosk-stage-icon{width:56px;height:56px;border-radius:16px}
            .kiosk-stage-icon .k-ico{width:30px;height:30px}
            .kiosk-stage-head p{font-size:15.5px}
            .kiosk-steps{gap:10px;margin-bottom:20px}
            .kiosk-steps li{flex-direction:column;align-items:flex-start;gap:8px;padding:13px;font-size:14px}
            .kiosk-steps b{width:32px;height:32px;font-size:15px}
            .k-btn{font-size:18px}
            .k-btn--xl{gap:12px;min-height:84px;padding:14px;font-size:20px}
            .k-btn-lead{display:none}
            .k-btn-arrow{width:46px;height:46px}
            .k-btn-text small{font-size:13.5px}
            .kiosk-field{grid-template-columns:1fr}
            .kiosk-field input{min-height:68px;font-size:24px}
            .kiosk-field .k-btn{width:100%;min-height:68px}
            .kiosk-flash{font-size:16px;padding:16px}
            .kiosk-note{justify-content:flex-start;text-align:left;font-size:15px}
            .kiosk-voucher{padding:18px;gap:14px}
            .kiosk-voucher-icon{width:48px;height:48px}
            .kiosk-success{padding:18px}
            .kiosk-success-icon{width:52px;height:52px}
            .kiosk-success-actions{grid-template-columns:1fr}
            .kiosk-bar{flex-direction:column;align-items:flex-start;padding:16px}
            .kiosk-bar-help{width:100%;justify-content:space-between}
        }
        @media (max-width:400px){
            .kiosk-brand-text{display:none}
            .kiosk-steps{grid-template-columns:1fr}
            .kiosk-steps li{flex-direction:row;align-items:center;padding:10px 14px}
        }
    </style>
</head>
<body>
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
<main class="kiosk">
  <div class="kiosk-screen">
    <header class="kiosk-top">
        <div class="kiosk-brand">
            <img src="{{ asset('images/logotipos/medichile-white-h.svg') }}" alt="Medichile">
            <div class="kiosk-brand-text"><strong>Tótem de atención</strong><small>SDI Salud Digital Integrada</small></div>
        </div>
        <div class="kiosk-top-right">
            <div class="kiosk-clock" aria-live="off"><strong id="kioskHora">--:--</strong><small id="kioskFecha">&nbsp;</small></div>
            <div class="kiosk-status {{ $totem ? '' : 'is-off' }}"><i aria-hidden="true"></i>{{ $totem ? 'Activo' : 'Sin equipo activo' }}</div>
        </div>
    </header>
    <script>
    (function () {
        var hora = document.getElementById('kioskHora'), fecha = document.getElementById('kioskFecha');
        function tick() {
            var ahora = new Date();
            hora.textContent = ahora.toLocaleTimeString('es-CL', {hour: '2-digit', minute: '2-digit', hour12: false});
            fecha.textContent = ahora.toLocaleDateString('es-CL', {weekday: 'long', day: 'numeric', month: 'long'});
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
                        <span class="kiosk-tile-title d-block">{{ $opcion['titulo'] }}</span>
                        <span class="kiosk-tile-desc d-block">{{ $opcion['texto'] }}</span>
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
</main>

@if($soloCompra)
<style>
.modal.demo-modal-visible{display:block;background:rgba(10,24,52,.62);overflow-y:auto}
body.demo-modal-open{overflow:hidden}
/* Modal de reserva con estilo de tótem (sobre la barra demo, z-index 9999) */
#modalReservaMedsdiTotem{z-index:10050}
#modalReservaMedsdiTotem .modal-content{border:0;border-radius:28px;overflow:hidden;box-shadow:0 40px 90px rgba(10,24,52,.35)}
#modalReservaMedsdiTotem .modal-header{align-items:center;gap:16px;padding:22px 28px;border:0;background:linear-gradient(120deg,#123d86,#1848a1 55%,#2aa9b4);color:#fff}
#modalReservaMedsdiTotem .k-modal-kicker{color:#cdeff0;font-size:12px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
#modalReservaMedsdiTotem .modal-title{margin:4px 0 0;color:#fff;font-size:clamp(20px,2.3vw,27px);font-weight:800}
#modalReservaMedsdiTotem .btn-close{flex:none;width:52px;height:52px;margin:0 0 0 auto;padding:0;border-radius:50%;opacity:1;filter:none;background:rgba(255,255,255,.16) url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23fff'%3e%3cpath d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg%3e") center/18px no-repeat}
#modalReservaMedsdiTotem .btn-close:hover{background-color:rgba(255,255,255,.28)}
#modalReservaMedsdiTotem .modal-body{padding:24px 28px 28px;background:#f4f8fd}
#modalReservaMedsdiTotem .k-step{margin-bottom:16px;padding:22px;border:1px solid var(--k-line);border-radius:22px;background:#fff}
#modalReservaMedsdiTotem .k-step-title{display:flex;align-items:center;gap:12px;margin-bottom:16px;color:var(--k-ink);font-size:19px;font-weight:800}
#modalReservaMedsdiTotem .k-step-title b{flex:none;display:grid;place-items:center;width:38px;height:38px;border-radius:50%;background:var(--k-blue);color:#fff;font-size:17px}
#modalReservaMedsdiTotem .form-label{margin-bottom:8px;color:var(--k-muted);font-size:13.5px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
#modalReservaMedsdiTotem .form-control,#modalReservaMedsdiTotem .form-select{min-height:60px;padding:12px 18px;border:2px solid #c9d8ef;border-radius:16px;color:var(--k-ink);font-size:18px;font-weight:600}
#modalReservaMedsdiTotem .form-select{padding-right:48px;background-position:right 18px center;background-size:18px 14px}
#modalReservaMedsdiTotem .form-control:focus,#modalReservaMedsdiTotem .form-select:focus{border-color:var(--k-blue);box-shadow:0 0 0 5px rgba(24,72,161,.15)}
#modalReservaMedsdiTotem .form-control:disabled,#modalReservaMedsdiTotem .form-select:disabled{background-color:#eef2f7;color:#8a98ac}
#modalReservaMedsdiTotem .form-control[readonly]{background:#f3f7fd}
#modalReservaMedsdiTotem #medsdiRutBuscar{font-size:24px;font-weight:700;letter-spacing:.05em}
#modalReservaMedsdiTotem .input-group{flex-wrap:nowrap;gap:12px}
#modalReservaMedsdiTotem .input-group>*{margin-left:0!important;border-radius:16px!important}
#modalReservaMedsdiTotem .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:60px;padding:12px 24px;border-radius:16px;font-size:17px;font-weight:800;transition:transform .12s ease,filter .2s ease}
#modalReservaMedsdiTotem .btn:active{transform:scale(.97)}
#modalReservaMedsdiTotem .btn:disabled{opacity:.5}
#modalReservaMedsdiTotem .btn-sm{min-height:48px;padding:10px 18px;border-radius:14px;font-size:15.5px}
#modalReservaMedsdiTotem .btn-primary{border:0;background:linear-gradient(135deg,var(--k-blue),var(--k-blue-2));box-shadow:0 8px 18px rgba(24,72,161,.24)}
#modalReservaMedsdiTotem .btn-info{border:0;background:var(--k-teal-ink);color:#fff}
#modalReservaMedsdiTotem .btn-outline-primary{border:2px solid #bcd0ee;background:#fff;color:var(--k-blue)}
#modalReservaMedsdiTotem .btn-outline-primary:hover{border-color:var(--k-blue);background:var(--k-blue);color:#fff}
#modalReservaMedsdiTotem .btn-outline-secondary{border:2px solid #c9d3e1;background:#fff;color:var(--k-muted)}
#modalReservaMedsdiTotem .btn-outline-success{border:2px solid #7fd6c9;background:#effbf8;color:#0b6b5f}
#modalReservaMedsdiTotem .btn-outline-success:hover{border-color:var(--k-ok);background:var(--k-ok);color:#fff}
#modalReservaMedsdiTotem #medsdiFormConfirmar .btn-success{min-height:84px;border:0;border-radius:22px;background:linear-gradient(135deg,var(--k-ok),var(--k-ok-2));font-size:22px;box-shadow:0 12px 26px rgba(14,133,119,.3)}
#modalReservaMedsdiTotem .alert{padding:16px 18px;border:0;border-radius:16px;font-size:16.5px;font-weight:600}
#modalReservaMedsdiTotem .alert-info{background:#edf4ff;color:#1d4c94}
#modalReservaMedsdiTotem .alert-success{background:#e2f7f2;color:#0b6b5f}
#modalReservaMedsdiTotem .alert-warning{background:#fff4dc;color:#7d5700}
#modalReservaMedsdiTotem .list-group{border:1px solid var(--k-line);border-radius:16px}
#modalReservaMedsdiTotem .list-group-item{padding:16px 18px;border-color:var(--k-line);font-size:17px}
#modalReservaMedsdiTotem .list-group-item-action:hover{background:#edf4ff}
#modalReservaMedsdiTotem #medsdiPacienteResultado,#modalReservaMedsdiTotem #medsdiDiasAtencion{font-size:16px}
#modalReservaMedsdiTotem #medsdiResultados .card{border:1px solid var(--k-line)!important;border-radius:20px;box-shadow:0 8px 20px rgba(15,42,92,.06)!important}
#modalReservaMedsdiTotem #medsdiResultados .card-body{padding:20px!important}
#modalReservaMedsdiTotem #medsdiResultados strong{font-size:18px}
#modalReservaMedsdiTotem #medsdiHoras{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(112px,1fr));gap:12px!important}
#modalReservaMedsdiTotem #medsdiHoras>span{grid-column:1/-1}
#modalReservaMedsdiTotem .medsdi-elegir-hora{min-height:64px;font-size:20px;font-variant-numeric:tabular-nums}
.flatpickr-calendar{font-family:'Manrope',sans-serif}
@media (max-width:767.98px){
    #modalReservaMedsdiTotem .modal-content{border-radius:0}
    #modalReservaMedsdiTotem .modal-header{padding:16px}
    #modalReservaMedsdiTotem .btn-close{width:46px;height:46px}
    #modalReservaMedsdiTotem .modal-body{padding:16px}
    #modalReservaMedsdiTotem .k-step{padding:16px;border-radius:18px}
    #modalReservaMedsdiTotem .input-group{flex-wrap:wrap}
    #modalReservaMedsdiTotem .input-group>.btn{width:100%}
    #modalReservaMedsdiTotem #medsdiHoras{grid-template-columns:repeat(auto-fill,minmax(96px,1fr))}
    #modalReservaMedsdiTotem #medsdiFormConfirmar .btn-success{min-height:72px;font-size:19px}
}
</style>

<div class="modal fade" id="modalReservaMedsdiTotem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-md-down"><div class="modal-content">
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
