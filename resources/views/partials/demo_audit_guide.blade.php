@php
    $assistantTitle = 'Contraloría: de la revisión a la resolución';
    $assistantSteps = [
        1 => ['icon'=>'📥','title'=>'Revisar los casos pendientes','text'=>'Identifica bonos, preconsultas y cobros que requieren revisión. Prioriza inconsistencias y alertas.','security'=>'Acceso con identidad y permisos de auditoría. Cada caso debe conservar su vínculo con paciente, profesional, prestación y centro.'],
        2 => ['icon'=>'🔎','title'=>'Contrastar las evidencias','text'=>'Revisa autorización del paciente, vigencia del bono, llegada, cierre de consulta y evidencia de ubicación cuando corresponda.','security'=>'Se contrastan firma y estado del QR, fechas, lugar y responsables. Se buscan duplicados y diferencias; una geolocalización aislada no acredita la atención.'],
        3 => ['icon'=>'⚖️','title'=>'Resolver y fundamentar','text'=>'Registra la aprobación, observación o rechazo según los antecedentes. Los casos incompletos deben quedar pendientes de aclaración.','security'=>'La decisión conserva motivo, responsable y fecha. La revisión es independiente de quien registra la atención y solicita el cobro.'],
        4 => ['icon'=>'🏦','title'=>'Revisar la autorización del pago','text'=>'Comprueba el estado del cobro, el importe y la cuenta registrada antes de continuar al pago y su conciliación.','security'=>'Se distinguen revisión clínica, autorización financiera y depósito. Se evita duplicar pagos y se conserva el historial de movimientos.'],
    ];
    if (request()->routeIs('auditoria.notificaciones')) {
        $assistantTitle = 'Contraloría: gestión de notificaciones';
        $assistantSteps = [
            1=>['icon'=>'🔔','title'=>'Identificar el aviso','text'=>'Revisa el tipo de evento, su fecha y el caso relacionado para establecer qué requiere atención.','security'=>'El aviso se consulta desde una sesión autorizada y debe corresponder a un registro identificable.'],
            2=>['icon'=>'🔎','title'=>'Consultar los antecedentes','text'=>'Contrasta el aviso con el bono, la atención o el movimiento que lo originó.','security'=>'Una notificación es una señal para revisar; no sustituye las evidencias ni determina por sí sola una irregularidad.'],
            3=>['icon'=>'📝','title'=>'Determinar el seguimiento','text'=>'Define si corresponde aclarar datos, revisar el caso o continuar con su resolución en el panel de auditoría.','security'=>'Las observaciones deben vincularse al caso y mantener separados los permisos de revisión y pago.'],
            4=>['icon'=>'✅','title'=>'Registrar la lectura','text'=>'Marca el aviso como leído cuando hayas revisado su contenido y continúa el seguimiento del caso.','security'=>'Leer una notificación no equivale a resolver la incidencia ni a autorizar un cobro.'],
        ];
    } elseif (request()->routeIs('auditoria.logins')) {
        $assistantTitle = 'Contraloría: auditoría de accesos';
        $assistantSteps = [
            1=>['icon'=>'🪪','title'=>'Identificar el acceso','text'=>'Consulta el usuario, la fecha, la IP y el resultado del inicio de sesión.','security'=>'El acceso al historial se limita a roles autorizados y permite identificar quién intentó ingresar.'],
            2=>['icon'=>'🔍','title'=>'Revisar patrones','text'=>'Examina intentos fallidos repetidos, horarios inusuales y cambios en el origen de los accesos.','security'=>'Las señales se contrastan con el contexto; una IP distinta no demuestra por sí sola suplantación.'],
            3=>['icon'=>'🛡️','title'=>'Contrastar identidad y permisos','text'=>'Verifica que la cuenta y su rol correspondan a las funciones realizadas y revisa los antecedentes de autenticación disponibles.','security'=>'Se consideran las verificaciones de identidad y MFA del circuito sin exponer claves ni códigos de verificación.'],
            4=>['icon'=>'📝','title'=>'Derivar y documentar','text'=>'Documenta los accesos que necesitan aclaración y deriva su revisión al responsable correspondiente.','security'=>'Conserva usuario, fecha y evidencia para el seguimiento. La explicación de la demo no bloquea cuentas ni modifica permisos.'],
        ];
    } elseif (request()->routeIs('auditoria.trazabilidad')) {
        $assistantTitle = 'Contraloría: trazabilidad de la atención y el bono';
        $assistantSteps = [
            1=>['icon'=>'🎫','title'=>'Identificar el bono','text'=>'Localiza el registro y su relación con paciente, profesional, prestación, reserva y centro de salud.','security'=>'Los identificadores y el estado del bono permiten contrastar el caso sin depender únicamente de una imagen del QR.'],
            2=>['icon'=>'🕒','title'=>'Reconstruir el recorrido','text'=>'Sigue la reserva, autorización, pago, llegada, atención y cierre, con los responsables de cada evento.','security'=>'Contrasta fechas y secuencia de estados; detecta pasos pendientes, duplicados o inconsistencias.'],
            3=>['icon'=>'📍','title'=>'Contrastar lugar y evidencias','text'=>'Relaciona las evidencias de ubicación disponibles con el centro y la hora de atención, junto con el consentimiento y el cierre clínico.','security'=>'Se considera la precisión y procedencia de la ubicación. La falta de evidencia requiere revisión y no se presenta como validación exitosa.'],
            4=>['icon'=>'🏦','title'=>'Seguir la resolución financiera','text'=>'Revisa decisiones de contraloría, autorización del cobro, depósito, devoluciones y saldos asociados.','security'=>'Cada importe debe poder conciliarse con su movimiento. El mismo dinero no debe figurar simultáneamente como devuelto y disponible para gastar.'],
        ];
    }
@endphp
<style>
 .demo-guide.assistant-guide .demo-guide-grid{grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
 .assistant-guide .demo-guide-step{padding:20px}.assistant-guide .demo-guide-step small{font-size:14px;line-height:1.5}.assistant-guide .demo-guide-security{font-size:13px;line-height:1.45}
 @media(max-width:1100px){.demo-guide.assistant-guide .demo-guide-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.assistant-guide .demo-guide-step:last-child{grid-column:auto}}
 @media(max-width:600px){.demo-guide.assistant-guide .demo-guide-grid{grid-template-columns:1fr}}
</style>
@include('partials.demo_flow_guide', ['demoAssistant'=>true, 'demoAudit'=>true, 'demoInteractive'=>true])
