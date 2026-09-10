@php
    $demoStep = (int) ($demoStep ?? 1);
    $demoGuideInformative = (bool) ($demoGuideInformative ?? false);
    $demoSteps = [
        1 => ['icon' => '📅', 'title' => 'Reservar', 'text' => 'Elegir médico, centro, fecha y hora.', 'security' => 'Agenda bloqueada al confirmar para evitar doble reserva.'],
        2 => ['icon' => '🎫', 'title' => 'Bono y QR', 'text' => 'Pagar el copago y emitir el bono digital.', 'security' => 'Token único, vencimiento y firma HMAC contra alteraciones.'],
        3 => ['icon' => '✓', 'title' => 'Registrar llegada', 'text' => 'Reconocer automáticamente al paciente y su hora.', 'security' => 'Relación paciente–bono–médico y QR/RUT como respaldo.'],
        4 => ['icon' => '🪑', 'title' => 'Sala de espera', 'text' => 'Informar al médico que el paciente está presente.', 'security' => 'Cambio de estado transaccional y trazabilidad de tótem.'],
        5 => ['icon' => '🩺', 'title' => 'Atención', 'text' => 'El médico llama, atiende y registra el cierre.', 'security' => 'Acceso por rol, identidad profesional y auditoría clínica.'],
    ];
@endphp
<style>
    .demo-guide{margin:24px 0;padding:22px;border:1px solid #cbdaf0;border-radius:22px;background:#fff;box-shadow:0 12px 32px rgba(24,72,161,.07);font-family:Inter,Segoe UI,Arial,sans-serif}.demo-guide-head{display:flex;justify-content:space-between;gap:18px;align-items:end;margin-bottom:17px}.demo-guide-head h2{margin:0;color:#18304f;font-size:21px}.demo-guide-head p{margin:4px 0 0;color:#65758b}.demo-guide-progress{font-size:13px;font-weight:900;color:#1848a1;background:#edf4ff;border-radius:999px;padding:8px 12px;white-space:nowrap}.demo-guide-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:10px}.demo-guide-step{position:relative;min-height:154px;padding:14px;border:1px solid #d7e2f2;border-radius:16px;background:#f8fbff;color:#52657e}.demo-guide-step.current{border:2px solid #2dbabb;background:#effcfc;color:#193650;box-shadow:0 8px 22px rgba(45,186,187,.13)}.demo-guide-step.done{background:#f0f8ff;color:#334c6d}.demo-guide-status{display:inline-flex;border-radius:999px;padding:4px 8px;background:#e7edf6;font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.06em}.current .demo-guide-status{background:#2dbabb;color:#fff}.done .demo-guide-status{background:#ddecff;color:#1848a1}.demo-guide-icon{display:block;font-size:24px;margin:10px 0 5px}.demo-guide-step strong{display:block;color:#1d3049;margin-bottom:5px}.demo-guide-step small{display:block;line-height:1.35}.demo-guide-security{margin-top:9px;padding-top:8px;border-top:1px dashed #cad7e8;color:#536d88;font-size:11px;line-height:1.35}.demo-guide-security b{color:#1848a1}.demo-security-note{display:flex;gap:11px;align-items:flex-start;margin-top:16px;padding:13px 15px;border-radius:14px;background:#edf4ff;color:#294d78;font-size:13px;line-height:1.45}.demo-security-note strong{color:#1848a1}@media(max-width:900px){.demo-guide-grid{grid-template-columns:1fr 1fr}.demo-guide-step:last-child{grid-column:1/-1}.demo-guide-head{align-items:flex-start;flex-direction:column}}@media(max-width:560px){.demo-guide{padding:16px}.demo-guide-grid{grid-template-columns:1fr}.demo-guide-step:last-child{grid-column:auto}}
</style>
<section class="demo-guide" aria-label="Recorrido de atención Medichile">
    <div class="demo-guide-head">
        <div><h2>Tu recorrido de atención</h2><p>Cada etapa conserva la relación entre paciente, médico, hora y bono.</p></div>
        @unless($demoGuideInformative)
            <span class="demo-guide-progress">Etapa {{ $demoStep }} de 5</span>
        @endunless
    </div>
    <div class="demo-guide-grid">
        @foreach($demoSteps as $number => $step)
            <article class="demo-guide-step {{ !$demoGuideInformative ? ($number < $demoStep ? 'done' : ($number === $demoStep ? 'current' : '')) : '' }}">
                @unless($demoGuideInformative)
                    <span class="demo-guide-status">{{ $number < $demoStep ? 'Completado' : ($number === $demoStep ? 'Ahora' : 'Siguiente') }}</span>
                @endunless
                <span class="demo-guide-icon">{{ $step['icon'] }}</span>
                <strong>{{ $number }}. {{ $step['title'] }}</strong>
                <small>{{ $step['text'] }}</small>
                <div class="demo-guide-security"><b>Seguridad:</b> {{ $step['security'] }}</div>
            </article>
        @endforeach
    </div>
    <div class="demo-security-note"><span>🔒</span><div><strong>Protección transversal:</strong> las operaciones sensibles usan sesión, CSRF, límites de intentos, control de roles y registro de auditoría con fecha, usuario e IP.</div></div>
</section>
