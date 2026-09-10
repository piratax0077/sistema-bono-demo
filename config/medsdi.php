<?php

return [
    // API pública/interna del sistema Med-SDI (medsdi-laravel13) de donde se
    // obtienen especialidades, profesionales, agenda y se agenda la hora real.
    'base_url' => env('MEDSDI_API_URL', 'https://med-sdi.cl'),
    'token' => env('MEDSDI_API_TOKEN'),
    // Credencial servidor-a-servidor para búsquedas del tótem por RUT.
    'totem_integration_key' => env('MEDSDI_TOTEM_INTEGRATION_KEY'),
    // Alternativa a un token fijo: credenciales de un paciente real para hacer
    // login dinámico (POST /api/user/login) y cachear el token obtenido.
    'login_user' => env('MEDSDI_API_LOGIN_USER'),
    'login_pass' => env('MEDSDI_API_LOGIN_PASS'),
    // Cuenta de un profesional real de Med-SDI para el escritorio profesional
    // demo (listar sus bonos/horas y finalizarlas). Login independiente del
    // de paciente de arriba.
    'profesional_login_user' => env('MEDSDI_API_PROFESIONAL_LOGIN_USER'),
    'profesional_login_pass' => env('MEDSDI_API_PROFESIONAL_LOGIN_PASS'),
    // Cuenta institucional usada exclusivamente desde el servidor al abrir
    // el escritorio de asistente. La contraseña nunca se envía al navegador.
    'asistente_login_user' => env('MEDSDI_API_ASISTENTE_LOGIN_USER'),
    'asistente_login_pass' => env('MEDSDI_API_ASISTENTE_LOGIN_PASS'),
    // Centro usado por INSI para su agenda médica.
    'default_lugar_id' => (int) env('MEDSDI_API_DEFAULT_LUGAR_ID', 12),
    'default_lugar_nombre' => env('MEDSDI_API_DEFAULT_LUGAR_NOMBRE', 'Centro médico'),
    'timeout' => (int) env('MEDSDI_API_TIMEOUT', 5),
    'fallback_local' => (bool) env('MEDSDI_API_FALLBACK_LOCAL', true),
    // Si es false, la reserva solo queda registrada en el bono demo local
    // (no escribe hora real en Med-SDI aunque exista token configurado).
    'booking_enabled' => (bool) env('MEDSDI_API_BOOKING_ENABLED', false),
    // Pago online real del bono contra Med-SDI (endpoint aún no expuesto por
    // el backend con X-Auth-Token; ver /api/paciente/pagar_bono en el plan de
    // integración). Mientras sea false, "Simular pago" sigue 100% local.
    'pago_enabled' => (bool) env('MEDSDI_API_PAGO_ENABLED', false),
    // Valor/copago referencial cuando el profesional viene de la API externa
    // y no existe convenio local con el que calcular el valor real.
    'default_valor' => (float) env('MEDSDI_API_DEFAULT_VALOR', 15000),
    'default_copago' => (float) env('MEDSDI_API_DEFAULT_COPAGO', 5000),
];
