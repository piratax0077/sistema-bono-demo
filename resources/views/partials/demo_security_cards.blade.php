@php
    $securityCards = [
        ['icon' => '📲', 'title' => 'El paciente autoriza', 'text' => 'La confirmación llega al teléfono del paciente por App o WhatsApp. El paciente aprueba o rechaza; la secretaría no autoriza en su nombre.', 'purpose' => ' Autenticación Multifactor (MFA). Datos encriptados. Protege el consentimiento y evita autorizaciones por terceros.'],
        ['icon' => '🏦', 'title' => 'Cuenta bancaria protegida', 'text' => 'Los cambios de cuenta requieren una segunda verificación y generan un aviso al contacto anterior del usuario.', 'purpose' => 'Autenticación Multifactor (MFA). Datos encriptados. Ayuda a impedir que un tercero desvíe pagos o devoluciones.'],
        ['icon' => '🧾', 'title' => 'Una atención, un registro, un profesional, un lugar de atención con Qr dinámico asociado', 'text' => 'Cada bono mantiene relacionados sus pasos: pagos,atenciones, devoluciones, saldos cobros, etc.. Repetir una solicitud no debe duplicar el movimiento ni acreditar dos veces el mismo dinero.', 'purpose' => 'Controla cobros duplicados y  conciliaciónes de saldos.'],
        ['icon' => '👥', 'title' => 'Roles, funciones, permisos separados', 'text' => 'Paciente, asistente, profesional, Centros de salud, Laboratorios, auditoría y administración tienen permisos distintos. Quien registra la atención no aprueba su propio cobro.', 'purpose' => 'Una revisión independiente acompaña la autorización del pago.'],
        ['icon' => '🔎', 'title' => 'Alertas antifraude', 'text' => 'Mediante patrones de IA. Se identifican conductas sospechosas: de reservas, rechazos, cambios de cuenta e intentos repetidos de usar un bono pasa su revisión por contraloría.', 'purpose' => 'Permite detectar movimientos inusuales y dejar evidencia.'],
        ['icon' => '🔒', 'title' => 'Privacidad en el tótem', 'text' => 'Al terminar o superar el tiempo de inactividad, se cierra la sesión y se retiran de pantalla los datos del paciente.', 'purpose' => 'Evita que la siguiente persona vea información de otra atención.'],
        ['icon' => '✅', 'title' => 'Atención confirmada', 'text' => 'El profesional debe cerrar la atención. Para que el Qr quede en estado de disponibilidad de cobro. El sistema vincula: paciente, al profesional, a la hora de atención al cierre de la consulta y el bono.', 'purpose' => 'Ayuda a prevenir cobros por prestaciones no realizadas. (una contraloría automática autoriza si algo falla se pasa a auditoría)'],
        ['icon' => '🌱', 'title' => 'Atención sin papel', 'text' => 'Reserva, bono, autorización y comprobantes digitales, disponibles sin imprimir.', 'purpose_label' => 'Beneficio', 'purpose' => 'Reduce el consumo de papel y tinta y evita impresiones y traslados innecesarios.'],
    ];
@endphp
<style>
    .demo-guide.demo-security-cards .demo-guide-grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:15px}
    .demo-security-cards .demo-guide-step{padding:20px;min-height:240px}
    .demo-security-cards .demo-guide-step small{font-size:14px;line-height:1.5}
    .demo-security-cards .demo-guide-security{font-size:13px;line-height:1.45}
    @media(max-width:900px){.demo-guide.demo-security-cards .demo-guide-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:560px){.demo-guide.demo-security-cards .demo-guide-grid{grid-template-columns:1fr}}
</style>
<section class="demo-guide demo-security-cards" aria-label="Controles de seguridad y antifraude" data-interactive="true" data-patient-id="{{ auth()->id() }}" data-storage-scope="security" data-step-label="Control">
    <div class="demo-guide-head">
        <div><h2>Así protegemos tu atención</h2><p>Conoce los controles de seguridad y antifraude del sistema.</p></div>
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
