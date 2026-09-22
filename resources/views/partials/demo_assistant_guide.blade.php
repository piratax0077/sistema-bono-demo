@php
    $assistantTitle = 'Recorrido de secretaría: recepción y coordinación';
    $assistantSteps = [
        1 => ['icon' => '🪪', 'title' => 'Verificar paciente y reserva', 'text' => 'Identifica al paciente por RUT, código o QR y comprueba su hora, profesional, prestación y centro de salud.', 'security' => 'Sesión de secretaría autorizada, RUT validado y acceso según permisos. La identidad y el bono deben corresponder a la misma reserva.'],
        2 => ['icon' => '📲', 'title' => 'Revisar bono y autorización', 'text' => 'Verifica la vigencia del bono y el pago. Cuando corresponde, solicita la aprobación del paciente por App o WhatsApp.', 'security' => 'El paciente decide desde su propio teléfono. Se revisan firma, vencimiento y estado del QR para evitar alteraciones, reutilización y cobros duplicados.'],
        3 => ['icon' => '📍', 'title' => 'Verificar el lugar de atención', 'text' => 'El circuito de seguridad verifica la geolocalización y envía la evidencia asociada al centro, la hora y el bono.', 'security' => 'Con permiso de ubicación, se verifica precisión y coincidencia con el centro. Los datos insuficientes o inconsistentes quedan pendientes de revisión. Geolocalización explicativa en esta demo.'],
        4 => ['icon' => '🪑', 'title' => 'Confirmar llegada y avisar', 'text' => 'Registra la llegada, deja al paciente en sala de espera y comunica su presencia al profesional en la agenda.', 'security' => 'La llegada conserva paciente, profesional, lugar, fecha y bono vinculados. El registro de usuario e IP permite revisar quién confirmó cada cambio.'],
    ];
    if (request()->routeIs('asistente.recepcionBonos', 'asistente.venta_bonos.*')) {
        $assistantTitle = 'Secretaría: recepción y emisión de bonos';
        $assistantSteps[1] = ['icon' => '🪪', 'title' => 'Validar paciente y convenio', 'text' => 'Comprueba identidad, cobertura, prestación, profesional, centro y horario antes de preparar el bono.', 'security' => 'La relación paciente–profesional–prestación respalda la operación; cada usuario accede con los permisos de su rol.'];
        $assistantSteps[2] = ['icon' => '📲', 'title' => 'Solicitar autorización', 'text' => 'Presenta el copago y solicita al paciente que apruebe o rechace desde su App o WhatsApp.', 'security' => 'La secretaría no sustituye la decisión del paciente. Se registra su respuesta y se controla el vencimiento de la solicitud.'];
        $assistantSteps[3] = ['icon' => '🎫', 'title' => 'Emitir o recibir el bono', 'text' => 'Verifica el pago o recibe el bono ya emitido y su QR de respaldo, manteniéndolo relacionado con la hora reservada.', 'security' => 'Revisión de vigencia, firma y estado del bono. Un mismo movimiento no debe duplicar pagos, devoluciones ni saldos.'];
        $assistantSteps[4] = ['icon' => '📍', 'title' => 'Vincular a la recepción', 'text' => 'Deja el bono disponible para la agenda y explica la verificación de ubicación que acompaña el registro de llegada.', 'security' => 'La evidencia de ubicación se asocia al centro y la atención con permiso del usuario. Identidad, fecha, hora y responsable permanecen vinculados al bono.'];
    } elseif (request()->routeIs('asistente.validaciones')) {
        $assistantTitle = 'Secretaría: revisión de atenciones cerradas';
        $assistantSteps = [
            1 => ['icon' => '🩺', 'title' => 'Revisar el cierre', 'text' => 'Consulta las atenciones que el profesional finalizó y comprueba paciente, prestación, fecha, hora y bono.', 'security' => 'El cierre debe estar registrado por el profesional identificado. La secretaría verifica el registro dentro de sus permisos.'],
            2 => ['icon' => '📍', 'title' => 'Contrastar las evidencias', 'text' => 'Revisa llegada, autorización y evidencia de geolocalización vinculadas al centro de salud y a la atención.', 'security' => 'La ubicación no reemplaza el cierre clínico. Faltas de información o diferencias de lugar y horario requieren revisión.'],
            3 => ['icon' => '🔎', 'title' => 'Registrar observaciones', 'text' => 'Deja constancia de diferencias o datos pendientes y deriva las incidencias a auditoría.', 'security' => 'Las observaciones conservan responsable y fecha. No se debe presentar una validación pendiente como aprobada.'],
            4 => ['icon' => '🧾', 'title' => 'Continuar a contraloría', 'text' => 'La atención y sus evidencias acompañan el bono en la revisión previa al cobro.', 'security' => 'Separación entre recepción, atención y autorización de pago. El historial permite seguir el bono hasta el depósito.'],
        ];
    }
@endphp
<style>
    .demo-guide.assistant-guide .demo-guide-grid{grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
    .assistant-guide .demo-guide-step{padding:20px}
    .assistant-guide .demo-guide-step small{font-size:14px;line-height:1.5}
    .assistant-guide .demo-guide-security{font-size:13px;line-height:1.45}
    @media(max-width:1100px){.demo-guide.assistant-guide .demo-guide-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.assistant-guide .demo-guide-step:last-child{grid-column:auto}}
    @media(max-width:600px){.demo-guide.assistant-guide .demo-guide-grid{grid-template-columns:1fr}}
</style>
@include('partials.demo_flow_guide', ['demoAssistant' => true, 'demoInteractive' => true])
