@php
    $securityCards = [
        ['icon' => '👥 Roles y permisos', 'title' => 'Accesos y funciones independientes', 'text' => 'Paciente, asistente, profesional, centros de salud, laboratorios, auditoría y administración tienen permisos diferentes. El paciente reserva y confirma; la asistente gestiona el bono y la llegada; el profesional atiende.', 'purpose' => 'Una contraloría independiente revisa la autorización del pago y deriva las excepciones a auditoría.'],
        ['icon' => '📲 Paciente I', 'title' => 'Reserva y autorización del bono', 'text' => 'El paciente reserva, confirma y autoriza el pago desde los canales habilitados: portal institucional, escritorio Medichile, tótem, app o WhatsApp. Si acepta, el bono queda vinculado al paciente, profesional y centro. Si rechaza, se gestiona la devolución o el saldo según corresponda.', 'purpose' => 'Autenticación multifactor (MFA) y datos cifrados. El consentimiento se vincula al bono y monto específicos; los mensajes y pagos se simulan en el demo.'],
        ['icon' => '🔒 Paciente II', 'title' => 'Privacidad en el tótem', 'text' => 'Al terminar o superar el tiempo de inactividad, se cierra la sesión y se retiran de pantalla los datos del paciente.', 'purpose' => 'Evita que la siguiente persona vea información de otra atención.'],
        ['icon' => '🧾 Profesional I', 'title' => 'Un bono vinculado a cada atención', 'text' => 'El QR permite identificar el estado del bono durante el recorrido. Paciente, profesional, centro, atención, pagos, devoluciones y saldos permanecen relacionados. Repetir una solicitud no debe duplicar movimientos.', 'purpose' => 'Validación del estado vigente, prevención de reutilización y conciliación para evitar cobros o abonos duplicados.'],
        ['icon' => '✅ Profesional II', 'title' => 'Cierre clínico antes del cobro', 'text' => 'El profesional registra el cierre de la atención. El bono pasa a revisión para cobro, conservando el vínculo con el paciente, profesional, hora y prestación.', 'purpose' => 'Ayuda a prevenir cobros por prestaciones no realizadas. Contraloría valida los antecedentes y deriva las excepciones a auditoría.'],
        ['icon' => '🔎 Seguridad', 'title' => 'Alertas antifraude', 'text' => 'La propuesta contempla análisis de patrones mediante IA para detectar reservas, rechazos, cambios de cuenta e intentos de reutilización sospechosos. Las alertas se remiten a contraloría para revisión.', 'purpose' => 'Permite detectar movimientos inusuales y conservar evidencia; una alerta no equivale por sí sola a fraude confirmado.'],
        ['icon' => '🏦 Paciente y profesional', 'title' => 'Cuenta bancaria protegida', 'text' => 'Los cambios de cuenta requieren una segunda verificación y generan un aviso al contacto anterior del usuario.', 'purpose' => 'Autenticación multifactor y datos cifrados para ayudar a impedir el desvío de pagos o devoluciones.'],
        ['icon' => '🌱 Medio ambiente', 'title' => 'Atención sin papel', 'text' => 'Reserva, bono, autorización y comprobantes digitales, disponibles sin imprimir.', 'purpose_label' => 'Beneficio', 'purpose' => 'Reduce el consumo de papel y tinta y evita impresiones y traslados innecesarios.'],
    ];
@endphp
<style>
    .demo-guide.demo-security-cards .demo-guide-grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:15px}
    .demo-security-cards .demo-guide-step{padding:22px;min-height:280px;display:flex;flex-direction:column;border-width:2px}
    .demo-security-cards .demo-guide-status{align-self:flex-start}
    .demo-security-cards .demo-guide-icon{font-size:19px;line-height:1.4;margin:16px 0 10px}
    .demo-security-cards .demo-guide-step strong{font-size:16px;line-height:1.45}
    .demo-security-cards .demo-guide-security{margin-top:auto;padding-top:12px}
    .demo-security-cards .demo-guide-step small{padding-bottom:14px}
    .demo-security-cards .demo-guide-step:last-child{grid-column:auto}
    .demo-security-cards .demo-guide-step small{font-size:14px;line-height:1.5}
    .demo-security-cards .demo-guide-security{font-size:13px;line-height:1.45}
    @media(max-width:900px){.demo-guide.demo-security-cards .demo-guide-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:560px){.demo-guide.demo-security-cards .demo-guide-grid{grid-template-columns:1fr}}
</style>
<section class="demo-guide demo-security-cards" aria-label="Controles de seguridad y antifraude" data-interactive="true" data-patient-id="{{ auth()->id() }}" data-storage-scope="security" data-step-label="Control">
    <div class="demo-guide-head">
        <div><h2>Nuestra propuesta de privacidad y seguridad</h2><p>Conoce los controles de seguridad y antifraude del sistema.</p></div>
        <span class="demo-guide-progress" aria-live="polite">Control 1 de {{ count($securityCards) }}</span>
    </div>
    <p class="demo-guide-hint">Demostración explicativa: pulsa cada tarjeta para recorrer los controles. Puedes volver a cualquiera y repetir la presentación.</p>
    <div class="demo-guide-grid">
        @foreach($securityCards as $card)
            <article class="demo-guide-step {{ $loop->first ? 'current' : '' }}" role="button" tabindex="0" aria-label="Explicar control {{ $loop->iteration }}: {{ $card['title'] }}" data-step="{{ $loop->iteration }}">
                <span class="demo-guide-status">{{ $loop->first ? 'Ahora' : 'Siguiente' }}</span>
                <span class="demo-guide-icon" aria-hidden="true">{{ $card['icon'] }}</span>
                <strong>{{ $loop->iteration }}. {{ $card['title'] }}</strong>
                <small>{{ $card['text'] }}</small>
                <div class="demo-guide-security"><b>{{ $card['purpose_label'] ?? 'Protección' }}:</b> {{ $card['purpose'] }}</div>
            </article>
        @endforeach
    </div>
</section>
