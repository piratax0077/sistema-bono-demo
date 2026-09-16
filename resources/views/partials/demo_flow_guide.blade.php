@php
    $demoStep = (int) ($demoStep ?? 1);
    $demoInteractive = (bool) ($demoInteractive ?? (auth()->user()?->rol === 'cliente'));
    $demoGuideInformative = (bool) ($demoGuideInformative ?? false) && ! $demoInteractive;
    $demoSteps = [
        1 => ['icon' => '📅', 'title' => 'Reservar', 'text' => 'Elegir médico, centro, fecha y hora.', 'security' => 'Identidad y RUT validados; convenio y relación paciente–médico–prestación verificados. Agenda bloqueada al confirmar para evitar doble reserva. El RUT del bono se guarda encriptado.'],
        2 => ['icon' => '🎫', 'title' => 'Bono y QR', 'text' => 'Pagar el copago y emitir el bono digital.', 'security' => 'Autorización del paciente por App o WhatsApp, simulada en la demo. Token único con vencimiento y firma HMAC para detectar alteraciones. Se comprueba el estado del bono para evitar reutilizarlo.'],
        3 => ['icon' => '✓', 'title' => 'Aviso de llegada al centro de salud', 'text' => 'Reconocer automáticamente al paciente y su hora.', 'security' => 'Se relacionan paciente, bono, médico y hora. El bono queda disponible para recepción; el QR es respaldo en tótem, secretaría o agenda online. Validación del estado y límites de intentos.'],
        4 => ['icon' => '🪑', 'title' => 'Sala de espera', 'text' => 'Informar al médico que el paciente está presente.', 'security' => 'Llegada vinculada a la reserva y registrada con trazabilidad. Los cambios se procesan de forma coordinada para evitar duplicados. El profesional consulta la atención asociada a su agenda.'],
        5 => ['icon' => '🩺', 'title' => 'Atención', 'text' => 'El médico llama, atiende y registra el cierre.', 'security' => 'Acceso por rol e identidad profesional. Se registra la atención y su cierre; el circuito continúa con auditoría, autorización del cobro y registro del depósito para seguir cada movimiento.'],
    ];
@endphp
<style>
    .demo-guide{margin:24px 0;padding:22px;border:1px solid #cbdaf0;border-radius:22px;background:#fff;box-shadow:0 12px 32px rgba(24,72,161,.07);font-family:Inter,Segoe UI,Arial,sans-serif}.demo-guide-head{display:flex;justify-content:space-between;gap:18px;align-items:end;margin-bottom:17px}.demo-guide-head h2{margin:0;color:#18304f;font-size:21px}.demo-guide-head p{margin:4px 0 0;color:#65758b}.demo-guide-progress{font-size:13px;font-weight:900;color:#1848a1;background:#edf4ff;border-radius:999px;padding:8px 12px;white-space:nowrap}.demo-guide-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:10px}.demo-guide-step{position:relative;min-height:154px;padding:14px;border:1px solid #d7e2f2;border-radius:16px;background:#f8fbff;color:#52657e}.demo-guide-step.current{border:2px solid #2dbabb;background:#effcfc;color:#193650;box-shadow:0 8px 22px rgba(45,186,187,.13)}.demo-guide-step.done{border-color:#80d2bb;background:#e8f8f1;color:#185b4b}.demo-guide-step.done:after{content:'✓';position:absolute;right:13px;top:13px;display:grid;place-items:center;width:24px;height:24px;border-radius:50%;background:#16886c;color:#fff;font-weight:900}.demo-guide-status{display:inline-flex;border-radius:999px;padding:4px 8px;background:#e7edf6;font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.06em}.current .demo-guide-status{background:#2dbabb;color:#fff}.done .demo-guide-status{background:#c8f0dd;color:#096b51}.demo-guide-icon{display:block;font-size:24px;margin:10px 0 5px}.demo-guide-step strong{display:block;color:#1d3049;margin-bottom:5px}.demo-guide-step small{display:block;line-height:1.35}.demo-guide-security{margin-top:9px;padding-top:8px;border-top:1px dashed #cad7e8;color:#536d88;font-size:11px;line-height:1.35}.demo-guide-security b{color:#1848a1}.demo-security-note{display:flex;gap:11px;align-items:flex-start;margin-top:16px;padding:13px 15px;border-radius:14px;background:#edf4ff;color:#294d78;font-size:13px;line-height:1.45}.demo-security-note strong{color:#1848a1}.demo-guide[data-interactive="true"] .demo-guide-step{cursor:pointer;transition:background .2s,border-color .2s,transform .2s,box-shadow .2s}.demo-guide[data-interactive="true"] .demo-guide-step.current:hover{transform:translateY(-3px);box-shadow:0 12px 25px rgba(45,186,187,.22)}.demo-guide[data-interactive="true"] .demo-guide-step:focus-visible{outline:3px solid #1848a1;outline-offset:3px}.demo-guide[data-interactive="true"] .demo-guide-step:not(.current){cursor:default}.demo-guide-hint{margin:0 0 14px;color:#52657e;font-size:13px}@media(max-width:900px){.demo-guide-grid{grid-template-columns:1fr 1fr}.demo-guide-step:last-child{grid-column:1/-1}.demo-guide-head{align-items:flex-start;flex-direction:column}}@media(max-width:560px){.demo-guide{padding:16px}.demo-guide-grid{grid-template-columns:1fr}.demo-guide-step:last-child{grid-column:auto}}
    .demo-guide[data-interactive="true"] .demo-guide-step:not(.current){cursor:pointer}.demo-guide[data-interactive="true"] .demo-guide-step:hover{border-color:#1848a1}
</style>
<section class="demo-guide" aria-label="Recorrido de atención Medichile" data-interactive="{{ $demoInteractive ? 'true' : 'false' }}" data-patient-id="{{ auth()->id() }}">
    <div class="demo-guide-head">
        <div><h2>Tu recorrido de atención</h2><p>Cada etapa conserva la relación entre paciente, médico, hora y bono.</p></div>
        @unless($demoGuideInformative)
        <span class="demo-guide-progress" aria-live="polite">Etapa {{ $demoInteractive ? 1 : $demoStep }} de 5</span>
        @endunless
    </div>
    @if($demoInteractive)<p class="demo-guide-hint">Pulsa «Ahora» para avanzar. Puedes pulsar cualquier otra tarjeta para volver a abrir esa etapa y repetir la demostración. Este avance visual no modifica la reserva ni el bono.</p>@endif
    <div class="demo-guide-grid">
        @foreach($demoSteps as $number => $step)
            <article class="demo-guide-step {{ $demoInteractive ? ($number === 1 ? 'current' : '') : (!$demoGuideInformative ? ($number < $demoStep ? 'done' : ($number === $demoStep ? 'current' : '')) : '') }}" @if($demoInteractive) role="button" tabindex="{{ $number === 1 ? '0' : '-1' }}" aria-label="Completar explicación de etapa {{ $number }}: {{ $step['title'] }}" aria-disabled="{{ $number === 1 ? 'false' : 'true' }}" data-step="{{ $number }}" @endif>
                @unless($demoGuideInformative)
                <span class="demo-guide-status">{{ $demoInteractive ? ($number === 1 ? 'Ahora' : 'Siguiente') : ($number < $demoStep ? 'Completado' : ($number === $demoStep ? 'Ahora' : 'Siguiente')) }}</span>
                @endunless
                <span class="demo-guide-icon">{{ $step['icon'] }}</span>
                <strong>{{ $number }}. {{ $step['title'] }}</strong>
                <small>{{ $step['text'] }}</small>
                <div class="demo-guide-security"><b>Seguridad:</b> {{ $step['security'] }}</div>
            </article>
        @endforeach
    </div>
    <div class="demo-security-note"><span>🔐</span><div><strong>Datos encriptados:</strong> el RUT del bono y los campos protegidos de dirección y fecha de nacimiento se almacenan cifrados. Las verificaciones usan huellas digitales de los datos; la firma HMAC permite detectar alteraciones del bono.</div></div>
    <div class="demo-security-note"><span>📲</span><div><strong>Primer control antifraude: decide el paciente.</strong> Puede aprobar o rechazar la autorización por App o WhatsApp. En el rechazo, el circuito demo definido informa: «Usuario rechazó el bono. Dinero transferido a la cuenta registrada por el paciente», y lo refleja como dinero disponible en Saldos. La aprobación, los mensajes y la transferencia se simulan en la demostración.</div></div>
    <div class="demo-security-note"><span>🛡️</span><div><strong>Reglas definidas para esta demostración:</strong> profesional jkriman@gmail.com y agenda particular del lugar 69; pacientes de prueba y secretarías configurados por el profesional; una hora por paciente en el mismo día. Estas reglas mantienen el recorrido de prueba asociado a la agenda indicada.</div></div>
    <div class="demo-security-note"><span>🔒</span><div><strong>Protección transversal:</strong> sesión autenticada, verificaciones de autorización, protección CSRF contra solicitudes ajenas a la sesión, límites de intentos y permisos por rol: paciente, asistente, profesional, auditoría y administración. La auditoría registra fecha, usuario e IP para seguir reserva, consentimiento, bono, llegada, atención y cobro. El ingreso automático y los botones de autorización corresponden al modo demo.</div></div>
</section>
@if($demoInteractive)
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.demo-guide[data-interactive="true"]').forEach(guide => {
        const cards = [...guide.querySelectorAll('[data-step]')];
        const progress = guide.querySelector('.demo-guide-progress');
        const total = cards.length;
        const stepLabel = guide.dataset.stepLabel || 'Etapa';
        const key = 'medichile-demo-guide-patient-' + guide.dataset.patientId + (guide.dataset.storageScope ? '-' + guide.dataset.storageScope : '');
        let completed = 0;
        try { completed = Math.min(total, Math.max(0, Math.floor(Number(sessionStorage.getItem(key)) || 0))); } catch (_) {}
        const render = () => {
            cards.forEach((card, index) => {
                const number = index + 1;
                const done = number <= completed;
                const current = number === completed + 1;
                card.classList.toggle('done', done);
                card.classList.toggle('current', current);
                card.querySelector('.demo-guide-status').textContent = done ? '✓ Completado' : (current ? 'Ahora' : 'Siguiente');
                card.tabIndex = 0;
                card.setAttribute('aria-disabled', 'false');
                card.setAttribute('aria-label', `${current ? 'Completar' : 'Volver a mostrar'} ${stepLabel.toLowerCase()} ${number}: ${card.querySelector('strong').textContent}`);
            });
            progress.textContent = completed === total ? `${total} de ${total} · recorrido explicado` : `${stepLabel} ${completed + 1} de ${total}`;
        };
        cards.forEach(card => {
            const advance = () => {
                const selected = Number(card.dataset.step);
                completed = selected === completed + 1 ? selected : selected - 1;
                try { sessionStorage.setItem(key, String(completed)); } catch (_) {}
                render();
                if (completed < total) {
                    const next = cards[completed];
                    next.focus({preventScroll:true});
                    next.scrollIntoView({behavior:'smooth',block:'nearest',inline:'nearest'});
                }
            };
            card.addEventListener('click', advance);
            card.addEventListener('keydown', event => {
                if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); advance(); }
            });
        });
        render();
    });
});
</script>
@endif
