@php
    $securityCards = [
        ['icon' => 'control-roles-permisos.svg', 'title' => 'Accesos y funciones independientes', 'text' => 'Paciente, asistente, profesional, centros de salud, laboratorios, auditoría y administración tienen permisos diferentes. El paciente reserva y confirma; la asistente gestiona el bono y la llegada; el profesional atiende.', 'purpose' => 'Una contraloría independiente revisa la autorización del pago y deriva las excepciones a auditoría.'],
        ['icon' => 'control-autorizacion-paciente.svg', 'title' => 'Reserva y autorización del bono', 'text' => 'El paciente reserva, confirma y autoriza el pago desde los canales habilitados: portal institucional, escritorio Medichile, tótem, app o WhatsApp. Si acepta, el bono queda vinculado al paciente, profesional y centro. Si rechaza, se gestiona la devolución o el saldo según corresponda.', 'purpose' => 'Autenticación multifactor (MFA) y datos cifrados. El consentimiento se vincula al bono y monto específicos; los mensajes y pagos se simulan en el demo.'],
        ['icon' => 'control-privacidad-totem.svg', 'title' => 'Privacidad en el tótem', 'text' => 'Al terminar o superar el tiempo de inactividad, se cierra la sesión y se retiran de pantalla los datos del paciente.', 'purpose' => 'Evita que la siguiente persona vea información de otra atención.'],
        ['icon' => 'control-qr-trazabilidad.svg', 'title' => 'Un bono vinculado a cada atención', 'text' => 'El QR permite identificar el estado del bono durante el recorrido. Paciente, profesional, centro, atención, pagos, devoluciones y saldos permanecen relacionados. Repetir una solicitud no debe duplicar movimientos.', 'purpose' => 'Validación del estado vigente, prevención de reutilización y conciliación para evitar cobros o abonos duplicados.'],
        ['icon' => 'control-atencion-confirmada.svg', 'title' => 'Cierre clínico antes del cobro', 'text' => 'El profesional registra el cierre de la atención. El bono pasa a revisión para cobro, conservando el vínculo con el paciente, profesional, hora y prestación.', 'purpose' => 'Ayuda a prevenir cobros por prestaciones no realizadas. Contraloría valida los antecedentes y deriva las excepciones a auditoría.'],
        ['icon' => 'control-alertas-antifraude.svg', 'title' => 'Alertas antifraude', 'text' => 'La propuesta contempla análisis de patrones mediante IA para detectar reservas, rechazos, cambios de cuenta e intentos de reutilización sospechosos. Las alertas se remiten a contraloría para revisión.', 'purpose' => 'Permite detectar movimientos inusuales y conservar evidencia; una alerta no equivale por sí sola a fraude confirmado.'],
        ['icon' => 'control-cuenta-bancaria.svg', 'title' => 'Cuenta bancaria protegida', 'text' => 'Los cambios de cuenta requieren una segunda verificación y generan un aviso al contacto anterior del usuario.', 'purpose' => 'Autenticación multifactor y datos cifrados para ayudar a impedir el desvío de pagos o devoluciones.'],
        ['icon' => 'control-sin-papel.svg', 'title' => 'Atención sin papel', 'text' => 'Reserva, bono, autorización y comprobantes digitales, disponibles sin imprimir.', 'purpose_label' => 'Beneficio', 'purpose' => 'Reduce el consumo de papel y tinta y evita impresiones y traslados innecesarios.'],
    ];
    $securityTotal = count($securityCards);
@endphp
<style>
    .sec-deck{--deck-blue:#1848a1;--deck-navy:#112f68;--deck-cyan:#31bebe;--deck-mint:#9cf2ea;--deck-ink:#18304f;--deck-muted:#5d6f86;--deck-line:#d7e2f2;margin:26px 0;padding:26px;border:1px solid #cbdaf0;border-radius:24px;background:#fff;box-shadow:0 18px 46px rgba(24,72,161,.08);color:var(--deck-ink);font-family:'Manrope',sans-serif}
    .sec-deck *{box-sizing:border-box}
    .sec-deck-head{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;margin-bottom:20px}
    .sec-deck-eyebrow{display:block;margin-bottom:6px;color:#087b68;font-size:12px;font-weight:800;letter-spacing:.16em;text-transform:uppercase}
    .sec-deck-head h2{margin:0;color:var(--deck-ink);font-size:clamp(21px,2.2vw,28px);font-weight:800;letter-spacing:-.02em;line-height:1.2}
    .sec-deck-head p{margin:5px 0 0;color:var(--deck-muted);font-size:15px}

    .sec-deck-stage{position:relative;overflow:hidden;padding:22px clamp(18px,3vw,40px) 18px;border-radius:22px;background:radial-gradient(circle at 88% 0%,rgba(49,190,190,.34),transparent 42%),radial-gradient(circle at 0% 100%,rgba(49,190,190,.16),transparent 40%),linear-gradient(135deg,#0d2550 0%,#1848a1 62%,#1d7f97 100%);color:#fff;outline:none}
    .sec-deck-stage::before{content:'';position:absolute;inset:0;background-image:radial-gradient(rgba(255,255,255,.08) 1px,transparent 1px);background-size:22px 22px;-webkit-mask-image:linear-gradient(115deg,transparent 35%,#000 100%);mask-image:linear-gradient(115deg,transparent 35%,#000 100%);pointer-events:none}
    .sec-deck-stage:focus-visible{box-shadow:0 0 0 3px #fff,0 0 0 6px var(--deck-blue)}
    .sec-deck-bars{position:relative;z-index:1;display:grid;grid-auto-flow:column;grid-auto-columns:1fr;gap:6px;margin:-8px 0 clamp(10px,1.8vw,24px)}
    .sec-deck-bar{display:block;width:100%;padding:8px 0;border:0;background:none;cursor:pointer}
    .sec-deck-bar:focus-visible{outline:2px solid var(--deck-mint);outline-offset:2px;border-radius:6px}
    .sec-deck-bar-track{display:block;height:4px;overflow:hidden;border-radius:99px;background:rgba(255,255,255,.2);transition:background .2s}
    .sec-deck-bar:hover .sec-deck-bar-track{background:rgba(255,255,255,.38)}
    .sec-deck-bar i{display:block;width:0;height:100%;border-radius:inherit;background:rgba(255,255,255,.62);transition:width .35s ease}
    .sec-deck-bar.is-done i{width:100%}
    .sec-deck-bar.is-current i{width:100%;background:var(--deck-mint)}
    .sec-deck.is-playing .sec-deck-bar.is-current i{width:0;transition:none;animation:secDeckFill var(--deck-dur,12s) linear forwards}
    .sec-deck.is-held .sec-deck-bar.is-current i{animation-play-state:paused}

    .sec-deck-viewport{position:relative;z-index:1;display:grid;max-width:1240px;margin:0 auto}
    .sec-deck-slide{grid-area:1/1;display:grid;grid-template-columns:minmax(200px,.75fr) minmax(0,1.6fr);gap:clamp(24px,4vw,56px);align-content:center;align-items:center;opacity:0;visibility:hidden;transform:translateX(56px)}
    .sec-deck-slide.is-before{transform:translateX(-56px)}
    .sec-deck-slide.is-active{opacity:1;visibility:visible;transform:none}
    .sec-deck.is-ready .sec-deck-slide{transition:opacity .4s ease,transform .6s cubic-bezier(.16,1,.3,1),visibility 0s linear .6s}
    .sec-deck.is-ready .sec-deck-slide.is-active{transition:opacity .45s ease .08s,transform .6s cubic-bezier(.16,1,.3,1),visibility 0s}

    .sec-deck-visual{position:relative;display:flex;flex-direction:column;align-items:center;gap:18px;padding:12px 0}
    .sec-deck-number{position:absolute;z-index:1;top:-2px;right:-6px;display:grid;place-items:center;min-width:44px;height:44px;padding:0 8px;border-radius:999px;background:var(--deck-mint);box-shadow:0 0 0 4px rgba(13,37,80,.55);color:#0b2a52;font-size:15px;font-weight:800;line-height:1}
    .sec-deck-icon{position:relative;display:grid;place-items:center;width:clamp(104px,10vw,136px);aspect-ratio:1;border-radius:50%;background:#fff;box-shadow:0 18px 40px rgba(4,20,52,.35),0 0 0 10px rgba(255,255,255,.08)}
    .sec-deck-icon img{display:block;width:56%;height:56%}
    .sec-deck-icon::before,.sec-deck-icon::after{content:'';position:absolute;inset:-10px;border:1.5px solid rgba(156,242,234,.55);border-radius:50%;opacity:0}

    .sec-deck-copy{min-width:0}
    .sec-deck-copy h3{margin:0 0 12px;color:#fff;font-size:clamp(22px,2.3vw,32px);font-weight:800;letter-spacing:-.02em;line-height:1.2}
    .sec-deck-copy p{max-width:760px;margin:0;color:#dceaff;font-size:clamp(15px,1.15vw,17px);line-height:1.68}
    .sec-deck-purpose{display:flex;gap:12px;align-items:flex-start;max-width:760px;margin-top:20px;padding:14px 16px;border:1px solid rgba(156,242,234,.32);border-left:4px solid var(--deck-cyan);border-radius:14px;background:rgba(6,24,60,.32);color:#eef6ff;font-size:14.5px;line-height:1.55}
    .sec-deck-purpose svg{flex:0 0 22px;width:22px;height:22px;margin-top:1px;color:var(--deck-mint)}
    .sec-deck-purpose b{color:var(--deck-mint)}
    .sec-deck-slide[data-tone="benefit"] .sec-deck-purpose{border-color:rgba(134,239,172,.35);border-left-color:#4ade80}
    .sec-deck-slide[data-tone="benefit"] .sec-deck-purpose svg,.sec-deck-slide[data-tone="benefit"] .sec-deck-purpose b{color:#86efac}

    .sec-deck-slide.is-active .sec-deck-icon{animation:secDeckPop .7s cubic-bezier(.16,1,.3,1) .12s both}
    .sec-deck-slide.is-active .sec-deck-icon::before,.sec-deck-slide.is-active .sec-deck-icon::after{animation:secDeckRing 3s ease-out infinite}
    .sec-deck-slide.is-active .sec-deck-icon::after{animation-delay:1.5s}
    .sec-deck-slide.is-active .sec-deck-copy h3{animation:secDeckUp .55s ease .18s both}
    .sec-deck-slide.is-active .sec-deck-copy p{animation:secDeckUp .55s ease .28s both}
    .sec-deck-slide.is-active .sec-deck-purpose{animation:secDeckUp .55s ease .4s both}

    .sec-deck-controls{position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:clamp(20px,2.6vw,32px);padding-top:16px;border-top:1px solid rgba(255,255,255,.14)}
    .sec-deck-center{display:flex;align-items:center;gap:14px}
    .sec-deck-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;padding:10px 16px;border:1px solid rgba(255,255,255,.28);border-radius:12px;background:rgba(255,255,255,.1);color:#fff;font:700 14px/1 'Manrope',sans-serif;cursor:pointer;transition:background .2s,border-color .2s,opacity .2s}
    .sec-deck-btn:hover{background:rgba(255,255,255,.2)}
    .sec-deck-btn:focus-visible{outline:3px solid var(--deck-mint);outline-offset:2px}
    .sec-deck-btn:disabled{opacity:.38;cursor:default;background:rgba(255,255,255,.1)}
    .sec-deck-btn svg{flex:0 0 18px;width:18px;height:18px}
    .sec-deck-next{border-color:var(--deck-cyan);background:var(--deck-cyan);color:#08263f}
    .sec-deck-next:hover{background:#56d6d6}
    .sec-deck.is-playing .sec-deck-ico-play,.sec-deck:not(.is-playing) .sec-deck-ico-pause{display:none}
    .sec-deck.is-last .sec-deck-ico-next,.sec-deck:not(.is-last) .sec-deck-ico-replay{display:none}
    .sec-deck-keys{color:rgba(220,234,255,.78);font-size:12.5px}
    .sec-deck-keys-touch{display:none}
    @media(hover:none){.sec-deck-keys-desk{display:none}.sec-deck-keys-touch{display:inline}}

    @keyframes secDeckFill{to{width:100%}}
    @keyframes secDeckPop{from{opacity:0;transform:scale(.6) rotate(-8deg)}to{opacity:1;transform:none}}
    @keyframes secDeckUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
    @keyframes secDeckRing{0%{opacity:.8;transform:scale(.92)}100%{opacity:0;transform:scale(1.45)}}

    @media(max-width:760px){
        .sec-deck{padding:20px}
        .sec-deck-head{flex-direction:column;align-items:flex-start;gap:10px}
        .sec-deck-slide{grid-template-columns:1fr;gap:18px;align-content:start}
        .sec-deck-visual{flex-direction:row;justify-content:flex-start;gap:16px;padding:6px 0 0 6px}
        .sec-deck-number{top:-6px;right:-10px;min-width:32px;height:32px;padding:0 6px;font-size:12px;box-shadow:0 0 0 3px rgba(13,37,80,.55)}
        .sec-deck-icon{width:80px;box-shadow:0 12px 26px rgba(4,20,52,.35),0 0 0 7px rgba(255,255,255,.08)}
        .sec-deck-icon::before,.sec-deck-icon::after{inset:-7px}
        .sec-deck-purpose{margin-top:16px}
    }
    @media(max-width:480px){
        .sec-deck{padding:16px;border-radius:20px}
        .sec-deck-stage{padding:18px 16px 16px;border-radius:18px}
        .sec-deck-copy h3{font-size:21px}
        .sec-deck-purpose{padding:12px 13px;font-size:14px}
        .sec-deck-btn{padding:10px 13px}
        .sec-deck-prev .sec-deck-btn-text,.sec-deck-play .sec-deck-btn-text,.sec-deck-keys{display:none}
    }
    @media(prefers-reduced-motion:reduce){
        .sec-deck-slide,.sec-deck-slide.is-before,.sec-deck.is-ready .sec-deck-slide,.sec-deck.is-ready .sec-deck-slide.is-active{transform:none;transition:opacity .2s linear,visibility 0s}
        .sec-deck-slide.is-active .sec-deck-icon,.sec-deck-slide.is-active .sec-deck-icon::before,.sec-deck-slide.is-active .sec-deck-icon::after,.sec-deck-slide.is-active .sec-deck-copy h3,.sec-deck-slide.is-active .sec-deck-copy p,.sec-deck-slide.is-active .sec-deck-purpose{animation:none}
    }
</style>
<section class="sec-deck" id="propuesta-seguridad" aria-label="Controles de seguridad y antifraude" data-security-deck data-patient-id="{{ auth()->id() }}">
    <div class="sec-deck-head">
        <div>
            <span class="sec-deck-eyebrow">Presentación interactiva</span>
            <h2>Nuestra propuesta de privacidad y seguridad</h2>
            <p>Conoce los controles de seguridad y antifraude del sistema.</p>
        </div>
    </div>

    <div class="sec-deck-stage" tabindex="0" aria-roledescription="presentación" aria-label="Presentación de los controles. Usa las flechas izquierda y derecha para cambiar de control.">
        <div class="sec-deck-bars" role="group" aria-label="Ir a un control">
            @foreach($securityCards as $card)
                <button type="button" class="sec-deck-bar {{ $loop->first ? 'is-current' : '' }}" data-goto="{{ $loop->index }}" aria-controls="sec-deck-slide-{{ $loop->iteration }}" aria-label="Control {{ $loop->iteration }}: {{ $card['title'] }}" @if($loop->first) aria-current="step" @endif><span class="sec-deck-bar-track"><i></i></span></button>
            @endforeach
        </div>
        <div class="sec-deck-viewport">
            @foreach($securityCards as $card)
                @php $words = count(preg_split('/\s+/u', trim($card['text'].' '.$card['purpose']))); @endphp
                <article class="sec-deck-slide {{ $loop->first ? 'is-active' : '' }}" id="sec-deck-slide-{{ $loop->iteration }}" role="group" aria-roledescription="diapositiva" aria-label="{{ $loop->iteration }} de {{ $securityTotal }}: {{ $card['title'] }}" data-tone="{{ isset($card['purpose_label']) ? 'benefit' : 'protect' }}" data-duration="{{ max(8, min(22, (int) round($words * 0.3))) }}" @unless($loop->first) aria-hidden="true" inert @endunless>
                    <div class="sec-deck-visual">
                        <span class="sec-deck-icon" aria-hidden="true"><img src="{{ asset('images/iconos/'.$card['icon']) }}" alt=""><span class="sec-deck-number">{{ $loop->iteration }}</span></span>
                    </div>
                    <div class="sec-deck-copy">
                        <h3>{{ $loop->iteration }}. {{ $card['title'] }}</h3>
                        <p>{{ $card['text'] }}</p>
                        <div class="sec-deck-purpose">
                            @if(isset($card['purpose_label']))
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 20c-.6-7.6 4.4-13.6 15-15 .4 10.2-5.6 15.4-13 15"/><path d="M5 20c2.6-4.4 5.8-7.4 10-9.6"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 4.5 6v5.5c0 4.6 3.1 8.4 7.5 9.5 4.4-1.1 7.5-4.9 7.5-9.5V6z"/><path d="m8.8 12.2 2.2 2.2 4.3-4.6"/></svg>
                            @endif
                            <div><b>{{ $card['purpose_label'] ?? 'Protección' }}:</b> {{ $card['purpose'] }}</div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="sec-deck-controls">
            <button type="button" class="sec-deck-btn sec-deck-prev" data-deck="prev" aria-label="Control anterior" disabled>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>
                <span class="sec-deck-btn-text">Anterior</span>
            </button>
            <div class="sec-deck-center">
                <button type="button" class="sec-deck-btn sec-deck-play" data-deck="play" aria-pressed="false" aria-label="Reproducir la presentación automáticamente">
                    <svg class="sec-deck-ico-play" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13l10.5-6.5z" fill="currentColor"/></svg>
                    <svg class="sec-deck-ico-pause" viewBox="0 0 24 24" aria-hidden="true"><rect x="6.5" y="5.5" width="4" height="13" rx="1" fill="currentColor"/><rect x="13.5" y="5.5" width="4" height="13" rx="1" fill="currentColor"/></svg>
                    <span class="sec-deck-btn-text sec-deck-play-text">Reproducir</span>
                </button>
                <span class="sec-deck-keys" aria-hidden="true"><span class="sec-deck-keys-desk">Usa ← → para navegar</span><span class="sec-deck-keys-touch">Desliza para cambiar</span></span>
            </div>
            <button type="button" class="sec-deck-btn sec-deck-next" data-deck="next" aria-label="Ir al control 2">
                <span class="sec-deck-next-text">Siguiente</span>
                <svg class="sec-deck-ico-next" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                <svg class="sec-deck-ico-replay" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.5 12a7.5 7.5 0 1 0 2.2-5.3"/><path d="M4.5 4v4h4"/></svg>
            </button>
        </div>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-security-deck]').forEach(deck => {
        const stage = deck.querySelector('.sec-deck-stage');
        const slides = [...deck.querySelectorAll('.sec-deck-slide')];
        const bars = [...deck.querySelectorAll('.sec-deck-bar')];
        const prev = deck.querySelector('[data-deck="prev"]');
        const next = deck.querySelector('[data-deck="next"]');
        const play = deck.querySelector('[data-deck="play"]');
        const nextText = next.querySelector('.sec-deck-next-text');
        const playText = play.querySelector('.sec-deck-play-text');
        const total = slides.length;
        const key = 'medichile-security-deck-' + deck.dataset.patientId;
        let current = 0;
        let playing = false;
        let onScreen = true;

        try {
            const saved = JSON.parse(sessionStorage.getItem(key) || 'null');
            if (saved) current = Math.min(total - 1, Math.max(0, Math.floor(Number(saved.current) || 0)));
        } catch (_) {}
        const save = () => { try { sessionStorage.setItem(key, JSON.stringify({current})); } catch (_) {} };

        const restartBar = () => {
            const fill = bars[current].querySelector('i');
            fill.style.animation = 'none';
            void fill.offsetWidth;
            fill.style.animation = '';
        };
        const render = () => {
            slides.forEach((slide, i) => {
                const active = i === current;
                slide.classList.toggle('is-active', active);
                slide.classList.toggle('is-before', i < current);
                slide.toggleAttribute('inert', !active);
                slide.setAttribute('aria-hidden', String(!active));
            });
            bars.forEach((bar, i) => {
                bar.classList.toggle('is-done', i < current);
                bar.classList.toggle('is-current', i === current);
                if (i === current) bar.setAttribute('aria-current', 'step'); else bar.removeAttribute('aria-current');
            });
            const last = current === total - 1;
            deck.classList.toggle('is-last', last);
            deck.style.setProperty('--deck-dur', (Number(slides[current].dataset.duration) || 12) + 's');
            prev.disabled = current === 0;
            nextText.textContent = last ? 'Repetir presentación' : 'Siguiente';
            next.setAttribute('aria-label', last ? 'Repetir la presentación desde el control 1' : `Ir al control ${current + 2}`);
        };
        const go = index => {
            current = Math.min(total - 1, Math.max(0, index));
            save();
            render();
            if (playing) restartBar();
        };
        const setPlaying = on => {
            playing = on;
            deck.classList.toggle('is-playing', on);
            play.setAttribute('aria-pressed', String(on));
            play.setAttribute('aria-label', on ? 'Pausar la presentación automática' : 'Reproducir la presentación automáticamente');
            playText.textContent = on ? 'Pausar' : 'Reproducir';
            if (on) restartBar();
        };
        const updateHold = () => deck.classList.toggle('is-held', !onScreen || document.hidden);

        prev.addEventListener('click', () => go(current - 1));
        next.addEventListener('click', () => go(current === total - 1 ? 0 : current + 1));
        play.addEventListener('click', () => {
            if (!playing && current === total - 1) go(0);
            setPlaying(!playing);
        });
        bars.forEach(bar => bar.addEventListener('click', () => go(Number(bar.dataset.goto))));
        bars.forEach((bar, i) => bar.querySelector('i').addEventListener('animationend', () => {
            if (!playing || i !== current) return;
            if (current === total - 1) setPlaying(false); else go(current + 1);
        }));

        stage.addEventListener('keydown', event => {
            const targets = {ArrowRight: current + 1, ArrowLeft: current - 1, Home: 0, End: total - 1};
            if (!(event.key in targets)) return;
            const target = targets[event.key];
            if (target < 0 || target >= total) return;
            event.preventDefault();
            go(target);
        });

        let touchX = null;
        let touchY = 0;
        stage.addEventListener('touchstart', event => { touchX = event.touches[0].clientX; touchY = event.touches[0].clientY; }, {passive: true});
        stage.addEventListener('touchend', event => {
            if (touchX === null) return;
            const dx = event.changedTouches[0].clientX - touchX;
            const dy = event.changedTouches[0].clientY - touchY;
            touchX = null;
            if (Math.abs(dx) < 50 || Math.abs(dx) < Math.abs(dy) * 1.2) return;
            if (dx < 0 && current < total - 1) go(current + 1);
            if (dx > 0 && current > 0) go(current - 1);
        }, {passive: true});

        if ('IntersectionObserver' in window) {
            new IntersectionObserver(entries => { onScreen = entries[0].isIntersecting; updateHold(); }, {threshold: .35}).observe(stage);
        }
        document.addEventListener('visibilitychange', updateHold);

        render();
        requestAnimationFrame(() => requestAnimationFrame(() => deck.classList.add('is-ready')));
    });
});
</script>
