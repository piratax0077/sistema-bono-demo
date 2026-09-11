<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MedsdiAgendaApiService
{
    /**
     * Busca horas vigentes por RUT usando la credencial servidor-a-servidor
     * del tótem. Las horas retornan id_hora_medica, el mismo identificador que
     * se guarda al crear el bono desde el flujo QR.
     */
    public function horasVigentesPorRut(string $rut): array
    {
        $clave = trim((string) config('medsdi.totem_integration_key'));
        if ($clave === '') {
            return $this->noDisponible('La credencial de integración del tótem con Med-SDI no está configurada.');
        }

        try {
            $response = $this->request()
                ->withHeaders(['X-Integration-Key' => $clave])
                ->post(rtrim((string) config('medsdi.base_url'), '/').'/api/integraciones/totem/horas-por-rut', [
                    'rut' => $rut,
                ]);

            return $this->interpretar($response, 'horas');
        } catch (ConnectionException $e) {
            return $this->noDisponible('No fue posible conectar con Med-SDI para buscar la hora por RUT.');
        }
    }

    public function pacienteAutenticado(): array
    {
        $token = $this->tokenActivo();
        if (! $token) {
            return $this->noDisponible('No fue posible autenticar al paciente en Med-SDI.');
        }

        try {
            $response = $this->request()->withHeaders(['X-Auth-Token' => $token])
                ->get(rtrim((string) config('medsdi.base_url'), '/').'/api/paciente/mi_ficha_medica');
            $payload = $response->json() ?: [];
            $paciente = $payload['paciente'] ?? null;

            if ($response->successful() && (int) ($payload['estado'] ?? 0) === 1 && is_array($paciente)) {
                return ['ok' => true, 'disponible' => true, 'paciente' => $paciente, 'mensaje' => 'ok'];
            }

            return [
                'ok' => false,
                'disponible' => true,
                'paciente' => null,
                'mensaje' => $payload['mensaje'] ?? $payload['msj'] ?? 'El usuario de Med-SDI no tiene un paciente asociado.',
            ];
        } catch (ConnectionException $e) {
            return ['ok' => false, 'disponible' => false, 'paciente' => null, 'mensaje' => 'No fue posible conectar con Med-SDI.'];
        }
    }

    public function notificarBonoAdquirido(array $bono): array
    {
        $token = $this->tokenActivo();
        if (! $token) {
            return $this->noDisponible('No fue posible autenticar al paciente en Med-SDI.');
        }

        try {
            $response = $this->request()->withHeaders(['X-Auth-Token' => $token])
                ->post(rtrim((string) config('medsdi.base_url'), '/').'/api/paciente/notificar-bono-adquirido', $bono);
            $payload = $response->json() ?: [];

            return [
                'ok' => $response->successful() && (int) ($payload['estado'] ?? 0) === 1,
                'disponible' => true,
                'dispositivos_notificados' => (int) ($payload['dispositivos_notificados'] ?? 0),
                'mensaje' => $payload['mensaje'] ?? $payload['msj'] ?? $payload['message']
                    ?? 'Med-SDI rechazó la notificación (HTTP '.$response->status().').',
            ];
        } catch (ConnectionException) {
            return $this->noDisponible('No fue posible conectar con Med-SDI para enviar la notificación Android.');
        }
    }

    public function cuentaBancariaPaciente(): array
    {
        $token = $this->tokenActivo();
        if (! $token) {
            return $this->noDisponible('No fue posible autenticar al paciente en Med-SDI.');
        }

        try {
            $response = $this->request()->withToken($token)->withHeaders(['X-Auth-Token' => $token])
                ->get(rtrim((string) config('medsdi.base_url'), '/').'/api/paciente/cuenta-bancaria');
            $payload = $response->json() ?: [];

            if ($response->successful() && (int) ($payload['estado'] ?? 0) === 1) {
                return [
                    'ok' => true,
                    'disponible' => true,
                    'cuenta' => $payload['cuenta'] ?? null,
                    'cuentas' => $payload['cuentas'] ?? [],
                    'bancos' => $payload['bancos'] ?? [],
                    'tipos_cuenta' => $payload['tipos_cuenta'] ?? [],
                    'paciente' => $payload['paciente'] ?? [],
                    'mensaje' => $payload['mensaje'] ?? 'Datos bancarios cargados.',
                ];
            }

            return $this->noDisponible($payload['mensaje'] ?? 'No fue posible consultar los datos bancarios en Med-SDI.');
        } catch (ConnectionException $e) {
            return $this->noDisponible('No fue posible conectar con Med-SDI para consultar los datos bancarios.');
        }
    }

    public function actualizarCuentaBancariaPaciente(array $datos): array
    {
        $token = $this->tokenActivo();
        if (! $token) {
            return $this->noDisponible('No fue posible autenticar al paciente en Med-SDI.');
        }

        try {
            $response = $this->request()->withToken($token)->withHeaders(['X-Auth-Token' => $token])
                ->put(rtrim((string) config('medsdi.base_url'), '/').'/api/paciente/cuenta-bancaria', $datos);
            $payload = $response->json() ?: [];

            if ($response->successful() && (int) ($payload['estado'] ?? 0) === 1) {
                return ['ok' => true, 'disponible' => true, 'cuenta' => $payload['cuenta'] ?? null, 'mensaje' => $payload['mensaje'] ?? 'Datos bancarios actualizados.'];
            }

            $mensajeValidacion = collect($payload['errors'] ?? [])->flatten()->first();
            return ['ok' => false, 'disponible' => true, 'registros' => [], 'mensaje' => $mensajeValidacion ?: ($payload['mensaje'] ?? 'Med-SDI rechazó los datos bancarios.')];
        } catch (ConnectionException $e) {
            return $this->noDisponible('No fue posible conectar con Med-SDI para actualizar los datos bancarios.');
        }
    }

    public function cuentaBancariaProfesional(): array
    {
        $token = $this->tokenActivoProfesional();
        if (! $token) {
            return $this->noDisponible('No fue posible autenticar al profesional en Med-SDI.');
        }

        try {
            $response = $this->request()->withToken($token)->withHeaders(['X-Auth-Token' => $token])
                ->get(rtrim((string) config('medsdi.base_url'), '/').'/api/profesional/cuenta-bancaria');
            $payload = $response->json() ?: [];

            if ($response->successful() && (int) ($payload['estado'] ?? 0) === 1) {
                return [
                    'ok' => true,
                    'disponible' => true,
                    'cuenta' => $payload['cuenta'] ?? null,
                    'cuentas' => $payload['cuentas'] ?? [],
                    'bancos' => $payload['bancos'] ?? [],
                    'tipos_cuenta' => $payload['tipos_cuenta'] ?? [],
                    'profesional' => $payload['profesional'] ?? [],
                    'mensaje' => $payload['mensaje'] ?? 'Datos bancarios cargados.',
                ];
            }

            return $this->noDisponible($payload['mensaje'] ?? $payload['msj'] ?? 'No fue posible consultar los datos bancarios del profesional en Med-SDI.');
        } catch (ConnectionException) {
            return $this->noDisponible('No fue posible conectar con Med-SDI para consultar los datos bancarios del profesional.');
        }
    }

    public function actualizarCuentaBancariaProfesional(array $datos): array
    {
        $token = $this->tokenActivoProfesional();
        if (! $token) {
            return $this->noDisponible('No fue posible autenticar al profesional en Med-SDI.');
        }

        try {
            $response = $this->request()->withToken($token)->withHeaders(['X-Auth-Token' => $token])
                ->put(rtrim((string) config('medsdi.base_url'), '/').'/api/profesional/cuenta-bancaria', $datos);
            $payload = $response->json() ?: [];

            if ($response->successful() && (int) ($payload['estado'] ?? 0) === 1) {
                return [
                    'ok' => true,
                    'disponible' => true,
                    'cuenta' => $payload['cuenta'] ?? null,
                    'mensaje' => $payload['mensaje'] ?? 'Datos bancarios actualizados.',
                ];
            }

            $mensajeValidacion = collect($payload['errors'] ?? [])->flatten()->first();

            return [
                'ok' => false,
                'disponible' => true,
                'registros' => [],
                'mensaje' => $mensajeValidacion ?: ($payload['mensaje'] ?? $payload['msj'] ?? 'Med-SDI rechazó los datos bancarios.'),
            ];
        } catch (ConnectionException) {
            return $this->noDisponible('No fue posible conectar con Med-SDI para actualizar los datos bancarios del profesional.');
        }
    }

    public function regiones(): array
    {
        return $this->get('/api/paciente/dame_regiones', [], 'regiones');
    }

    public function ciudades(int $idRegion): array
    {
        return $this->get('/api/paciente/dame_ciudades', ['id_region' => $idRegion], 'ciudades');
    }

    public function especialidades(): array
    {
        return $this->get('/api/profesionales/especialidades', [], 'especialidades');
    }

    public function prestaciones(string $buscar): array
    {
        return $this->getAutenticado('/api/paciente/bonos/prestaciones', [
            'buscar' => $buscar,
        ], 'registros');
    }

    public function cotizar(array $datos): array
    {
        $token = $this->tokenActivo();
        if (! $token) {
            return $this->noDisponible('No fue posible autenticar con Med-SDI para cotizar.');
        }

        try {
            $response = $this->request()->withHeaders(['X-Auth-Token' => $token])
                ->post(rtrim((string) config('medsdi.base_url'), '/').'/api/paciente/bonos/cotizar', $datos);
            $payload = $response->json() ?: [];

            if ($response->successful() && (int) ($payload['estado'] ?? 0) === 1) {
                return [
                    'ok' => true,
                    'disponible' => true,
                    'cotizacion' => $payload['cotizacion'] ?? [],
                    'simulacion' => (bool) ($payload['simulacion'] ?? true),
                    'mensaje' => $payload['mensaje'] ?? 'Cotización calculada.',
                ];
            }

            return [
                'ok' => false,
                'disponible' => true,
                'cotizacion' => [],
                'mensaje' => $payload['mensaje'] ?? $payload['msj'] ?? 'No fue posible cotizar la prestación.',
            ];
        } catch (ConnectionException $e) {
            return $this->noDisponible('No fue posible conectar con Med-SDI para cotizar.');
        }
    }

    public function tipoEspecialidades(int $idEspecialidad): array
    {
        return $this->get('/api/profesionales/tipo_especialidades', ['id_especialidad' => $idEspecialidad], 'tipo_especialidades');
    }

    public function subTipoEspecialidades(int $idTipoEspecialidad): array
    {
        return $this->get('/api/profesionales/sub_tipo_especialidades', ['id_tipo_especialidad' => $idTipoEspecialidad], 'sub_tipo_especialidades');
    }

    public function diasLaborales(int $idProfesional, int $idLugar, int $tipoAgenda = 1): array
    {
        $resultado = $this->get('/api/profesionales/dias_laborales_lugar_atencion', [
            'id_profesional' => $idProfesional,
            'id_lugar' => $idLugar,
            'tipo_agenda' => $tipoAgenda,
        ], 'registros');

        // INSI obtiene el calendario desde este endpoint histórico. Algunos
        // horarios existentes aún no tienen tipo_agenda y el endpoint nuevo
        // los omite, aunque sí tengan días configurados.
        if (empty($resultado['registros']['horario_agenda_laboral'] ?? null)) {
            return $this->get('/api/horas_medicas_profesional_lugar_atencion', [
                'id_profesional' => $idProfesional,
                'lugar_atencion' => $idLugar,
            ], 'registros');
        }

        return $resultado;
    }

    public function horasDisponibles(int $idProfesional, int $idLugar, string $fecha, int $tipoAgenda = 1): array
    {
        $resultado = $this->get('/api/profesionales/horas_disponibles_profesional_lugar_atencion', [
            'id_profesional' => $idProfesional,
            'id_lugar' => $idLugar,
            'fecha' => $fecha,
            'tipo_agenda' => $tipoAgenda,
        ], 'horarios');

        if (! $resultado['ok']) {
            return $this->get('/api/horas_disponibles_profesional_lugar_atencion', [
                'id_profesional' => $idProfesional,
                'id_lugar_atencion' => $idLugar,
                'dia' => $fecha,
            ], 'registros');
        }

        return $resultado;
    }

    public function buscarProfesionales(array $filtros): array
    {
        $token = $this->tokenActivo();
        if (! $token) {
            return $this->noDisponible('No fue posible autenticar con Med-SDI para buscar profesionales (revise MEDSDI_API_TOKEN o MEDSDI_API_LOGIN_USER/PASS).');
        }
        $usaUbicacionExplicita = ! empty($filtros['id_region']) || ! empty($filtros['id_ciudad']);
        if (! $usaUbicacionExplicita && empty($filtros['id_lugar_atencion']) && config('medsdi.default_lugar_id')) {
            $filtros['id_lugar_atencion'] = (int) config('medsdi.default_lugar_id');
        }

        try {
            $response = $this->request()->withHeaders(['X-Auth-Token' => $token])
                ->post(rtrim((string) config('medsdi.base_url'), '/').'/api/profesionales/buscar_profesionales', $filtros);

            $resultado = $this->interpretar($response, 'profesionales');

            // Compatibilidad temporal: el backend anterior no reconoce
            // id_lugar_atencion y exige región/ciudad. INSI consulta este mismo
            // centro mediante el endpoint histórico.
            if (! $resultado['ok']
                && ! empty($filtros['id_lugar_atencion'])
                && str_contains(mb_strtolower($resultado['mensaje']), 'región o ciudad')) {
                if (! empty($filtros['id_especialidad']) && ! empty($filtros['id_tipo_especialidad'])) {
                    return $this->buscarProfesionalesLegacy($filtros);
                }

                // El endpoint histórico requiere la jerarquía completa. Para
                // una búsqueda general, el backend antiguo conserva el filtro RM.
                unset($filtros['id_lugar_atencion']);
                $filtros['id_region'] = 7;
                $response = $this->request()->withHeaders(['X-Auth-Token' => $token])
                    ->post(rtrim((string) config('medsdi.base_url'), '/').'/api/profesionales/buscar_profesionales', $filtros);

                return $this->interpretar($response, 'profesionales');
            }

            return $resultado;
        } catch (ConnectionException $e) {
            return $this->noDisponible('No fue posible conectar con Med-SDI.');
        }
    }

    private function buscarProfesionalesLegacy(array $filtros): array
    {
        $response = $this->request()->asForm()->post(
            rtrim((string) config('medsdi.base_url'), '/').'/api/buscar_profesionales_cm_todos',
            [
                'id_profesion' => $filtros['id_especialidad'] ?? 0,
                'id_especialidad' => $filtros['id_tipo_especialidad'] ?? 0,
                'id_tipo_especialidad' => $filtros['id_sub_tipo_especialidad'] ?? 0,
                'id_lugar_atencion' => $filtros['id_lugar_atencion'],
            ]
        );

        if (! $response->successful()) {
            return $this->noDisponible('Med-SDI no pudo consultar los profesionales del centro configurado.');
        }

        $nombreBuscado = mb_strtolower(trim((string) ($filtros['nombre_profesional'] ?? '')));
        $profesionales = collect($response->json('profesionales', []))
            ->filter(function ($profesional) use ($nombreBuscado) {
                if ($nombreBuscado === '') {
                    return true;
                }
                $nombre = mb_strtolower(implode(' ', array_filter([
                    $profesional['nombre'] ?? '',
                    $profesional['apellido_uno'] ?? '',
                    $profesional['apellido_dos'] ?? '',
                ])));

                return str_contains($nombre, $nombreBuscado);
            })
            ->map(function ($profesional) use ($filtros) {
                $profesional['lugares_atencion'] = [[
                    'id' => (int) $filtros['id_lugar_atencion'],
                    'nombre' => (string) config('medsdi.default_lugar_nombre'),
                ]];

                return $profesional;
            })->values()->all();

        return [
            'ok' => true,
            'disponible' => true,
            'registros' => $profesionales,
            'mensaje' => count($profesionales).' profesionales encontrados',
        ];
    }

    public function agendarHoraMedica(array $datos): array
    {
        if (! config('medsdi.booking_enabled')) {
            return ['ok' => false, 'disponible' => false, 'registros' => [], 'mensaje' => 'La reserva real en Med-SDI está deshabilitada (MEDSDI_API_BOOKING_ENABLED=false).'];
        }
        $token = $this->tokenActivo();
        if (! $token) {
            return $this->noDisponible('No fue posible autenticar con Med-SDI para agendar (revise MEDSDI_API_TOKEN o MEDSDI_API_LOGIN_USER/PASS).');
        }

        try {
            $response = $this->request()->withHeaders(['X-Auth-Token' => $token])
                ->post(rtrim((string) config('medsdi.base_url'), '/').'/api/paciente/agendar_hora_medica', $datos);

            return $this->interpretar($response, 'registro');
        } catch (ConnectionException $e) {
            return $this->noDisponible('No fue posible conectar con Med-SDI.');
        }
    }

    public function agendarHoraMedicaComoAsistente(array $datos, string $rutPaciente): array
    {
        if (! config('medsdi.booking_enabled')) {
            return $this->noDisponible('La reserva real en Med-SDI está deshabilitada.');
        }
        $token = $this->tokenActivoAsistente();
        if (! $token) {
            return $this->noDisponible('No fue posible autenticar a la asistente en Med-SDI.');
        }

        $datos['paciente_rut'] = $rutPaciente;
        try {
            $response = $this->request()->withHeaders(['X-Auth-Token' => $token])
                ->post(rtrim((string) config('medsdi.base_url'), '/').'/api/paciente/agendar_hora_medica', $datos);
            return $this->interpretar($response, 'registro');
        } catch (ConnectionException) {
            return $this->noDisponible('No fue posible conectar con Med-SDI para realizar la reserva asistida.');
        }
    }

    public function pacientePorRutComoAsistente(string $rut): array
    {
        $token = $this->tokenActivoAsistente();
        if (! $token) {
            return $this->noDisponible('No fue posible autenticar a la asistente en Med-SDI.');
        }

        try {
            $response = $this->request()->withHeaders(['X-Auth-Token' => $token])
                ->get(rtrim((string) config('medsdi.base_url'), '/').'/api/asistente/paciente-por-rut', ['rut' => $rut]);
            $payload = $response->json() ?: [];

            return [
                'ok' => $response->successful() && (int) ($payload['estado'] ?? 0) === 1,
                'disponible' => true,
                'paciente' => is_array($payload['paciente'] ?? null) ? $payload['paciente'] : null,
                'mensaje' => $payload['msj'] ?? ($response->successful() ? 'Paciente encontrado.' : 'No fue posible encontrar al paciente.'),
            ];
        } catch (ConnectionException) {
            return $this->noDisponible('No fue posible conectar con Med-SDI para validar al paciente.');
        }
    }

    public function confirmarHoraMedica(int $idHora): array
    {
        $token = $this->tokenActivo();
        if (! $token) {
            return $this->noDisponible('No fue posible autenticar con Med-SDI para confirmar la hora.');
        }

        try {
            $response = $this->request()->withToken($token)
                ->withHeaders(['X-Auth-Token' => $token])
                ->post(rtrim((string) config('medsdi.base_url'), '/').'/api/paciente/confirmar_hora_medica', ['id_hora' => $idHora]);

            return $this->interpretar($response, 'hora');
        } catch (ConnectionException $e) {
            return $this->noDisponible('No fue posible conectar con Med-SDI para confirmar la hora.');
        }
    }

    /**
     * Lista las horas médicas del paciente autenticado (GET /api/paciente/mis_horas_medicas).
     * Se usa para consultar el estado real de una hora ya reservada, sin mutarla.
     */
    public function misHorasMedicas(int $idPaciente): array
    {
        return $this->getAutenticado('/api/paciente/mis_horas_medicas', ['id_paciente' => $idPaciente], 'horas');
    }

    /**
     * Busca dentro de mis_horas_medicas el registro de una hora puntual por su id
     * y devuelve su id_estado/texto_estado tal como lo tiene Med-SDI hoy.
     */
    public function estadoHoraMedica(int $idHora, int $idPaciente): array
    {
        $resultado = $this->misHorasMedicas($idPaciente);
        if (! $resultado['ok']) {
            return $resultado + ['encontrada' => false];
        }

        $hora = collect($resultado['registros'])->first(fn ($registro) => (int) ($registro['id'] ?? 0) === $idHora);

        if (! $hora) {
            return [
                'ok' => false,
                'disponible' => true,
                'encontrada' => false,
                'mensaje' => 'Med-SDI no tiene registrada esa hora para el paciente autenticado.',
            ];
        }

        return [
            'ok' => true,
            'disponible' => true,
            'encontrada' => true,
            'id_estado' => (int) ($hora['id_estado'] ?? 0),
            'texto_estado' => $hora['texto_estado'] ?? null,
            'color_estado' => $hora['color_estado'] ?? null,
            'fecha_confirmacion' => $hora['fecha_confirmacion'] ?? null,
            'fecha_cancelacion' => $hora['fecha_cancelacion'] ?? null,
            'comentarios_cancelacion' => $hora['comentarios_cancelacion'] ?? null,
        ];
    }

    /**
     * Pago online real del copago en Med-SDI. El backend hoy solo expone esta
     * lógica como ruta web de sesión (/Asistente/venta/bono/pago, rol
     * Paciente/Asistente/Profesional), no como API con X-Auth-Token. Mientras
     * no exista un endpoint equivalente bajo /api/paciente/pagar_bono, esto
     * queda deshabilitado por config y el pago sigue siendo simulado local.
     */
    public function pagarBono(array $datos): array
    {
        if (! config('medsdi.pago_enabled')) {
            return [
                'ok' => false,
                'disponible' => false,
                'registros' => [],
                'mensaje' => 'Pago online Med-SDI deshabilitado (MEDSDI_API_PAGO_ENABLED=false): falta que el backend exponga /api/paciente/pagar_bono con X-Auth-Token.',
            ];
        }

        $token = $this->tokenActivo();
        if (! $token) {
            return $this->noDisponible('No fue posible autenticar con Med-SDI para pagar el bono.');
        }

        try {
            $response = $this->request()->withHeaders(['X-Auth-Token' => $token])
                ->post(rtrim((string) config('medsdi.base_url'), '/').'/api/paciente/pagar_bono', $datos);

            return $this->interpretar($response, 'orden');
        } catch (ConnectionException $e) {
            return $this->noDisponible('No fue posible conectar con Med-SDI para pagar el bono.');
        }
    }

    /**
     * Marca en Med-SDI que el paciente llegó físicamente a la sala de espera
     * El pago online se muestra como una señal visual independiente. Esta
     * operación registra la llegada real y cambia la hora a id_estado=4
     * (Espera) en Med-SDI.
     */
    public function confirmarLlegadaSalaEspera(int $idHoraMedica, string $rut): array
    {
        if (! config('medsdi.pago_enabled')) {
            return [
                'ok' => false,
                'disponible' => false,
                'registros' => [],
                'mensaje' => 'Confirmación de llegada Med-SDI deshabilitada (MEDSDI_API_PAGO_ENABLED=false).',
            ];
        }

        $clave = trim((string) config('medsdi.totem_integration_key'));
        if ($clave === '') {
            return $this->noDisponible('La credencial de integración del tótem con Med-SDI no está configurada.');
        }

        try {
            $response = $this->request()->withHeaders(['X-Integration-Key' => $clave])
                ->post(rtrim((string) config('medsdi.base_url'), '/').'/api/integraciones/totem/confirmar-llegada', [
                    'id_hora_medica' => $idHoraMedica,
                    'rut' => $rut,
                ]);

            return $this->interpretar($response, 'hora');
        } catch (ConnectionException $e) {
            return $this->noDisponible('No fue posible conectar con Med-SDI para confirmar la llegada.');
        }
    }

    /**
     * Inicia sesión contra Med-SDI (POST /api/user/login, campos user/pass) y
     * devuelve el token. Se cachea éxito y fracaso para no gastar los 3
     * intentos permitidos por el rate limiter del login real cada vez que se
     * busca un profesional.
     */
    public function login(string $usuario, string $clave): array
    {
        try {
            $response = $this->request()
                ->asForm()
                ->post(rtrim((string) config('medsdi.base_url'), '/').'/api/user/login', [
                    'user' => $usuario,
                    'pass' => $clave,
                ]);
        } catch (ConnectionException $e) {
            return ['ok' => false, 'mensaje' => 'No fue posible conectar con Med-SDI.'];
        }

        $payload = $response->json() ?: [];
        if ($response->successful() && (int) ($payload['estado'] ?? 0) === 1 && ! empty($payload['token'])) {
            return [
                'ok' => true,
                'token' => (string) $payload['token'],
                'user' => is_array($payload['user'] ?? null) ? $payload['user'] : [],
                'roles' => is_array($payload['roles'] ?? null) ? $payload['roles'] : [],
            ];
        }

        return ['ok' => false, 'mensaje' => is_string($payload['msj'] ?? null) ? $payload['msj'] : 'Med-SDI rechazó el inicio de sesión.'];
    }

    /**
     * Autentica la cuenta institucional del escritorio de asistente y expone
     * sólo su identidad y roles. El token queda en caché del servidor.
     */
    public function asistenteAutenticado(): array
    {
        $usuario = trim((string) config('medsdi.asistente_login_user'));
        $clave = (string) config('medsdi.asistente_login_pass');

        if ($usuario === '' || $clave === '') {
            return [
                'ok' => false,
                'disponible' => false,
                'mensaje' => 'Las credenciales de la asistente Med-SDI no están configuradas.',
                'user' => null,
                'roles' => [],
            ];
        }

        $servidor = rtrim(mb_strtolower((string) config('medsdi.base_url')), '/');
        $cacheKey = 'medsdi_api_sesion_asistente:'.md5($servidor.'|'.mb_strtolower($usuario));

        $resultado = Cache::get($cacheKey);
        if (! is_array($resultado)) {
            $resultado = $this->login($usuario, $clave);
            Cache::put($cacheKey, $resultado, $resultado['ok'] ? now()->addHours(20) : now()->addMinutes(15));
        }

        if (! ($resultado['ok'] ?? false)) {
            return [
                'ok' => false,
                'disponible' => true,
                'mensaje' => $resultado['mensaje'] ?? 'Med-SDI rechazó el inicio de sesión de la asistente.',
                'user' => null,
                'roles' => [],
            ];
        }

        $roles = collect($resultado['roles'] ?? [])->filter(fn ($rol) => is_array($rol))
            ->map(fn ($rol) => ['id' => $rol['id'] ?? null, 'name' => $rol['name'] ?? ''])
            ->values()->all();
        $esAsistente = collect($roles)->contains(fn ($rol) => mb_strtolower((string) $rol['name']) === 'asistente');

        if (! $esAsistente) {
            return [
                'ok' => false,
                'disponible' => true,
                'mensaje' => 'La cuenta configurada no posee el rol Asistente en Med-SDI.',
                'user' => null,
                'roles' => $roles,
            ];
        }

        $user = $resultado['user'] ?? [];

        return [
            'ok' => true,
            'disponible' => true,
            'mensaje' => 'Sesión iniciada correctamente en Med-SDI.',
            'user' => [
                'id' => $user['id'] ?? null,
                'name' => $user['name'] ?? null,
                'email' => $user['email'] ?? $usuario,
            ],
            'roles' => $roles,
        ];
    }

    /**
     * Token a usar en X-Auth-Token: el fijo de config si existe, o uno obtenido
     * vía login con MEDSDI_API_LOGIN_USER/PASS (cacheado ~20h; los fracasos se
     * cachean ~15 min para respetar el bloqueo por intentos de Med-SDI).
     */
    private function tokenActivo(): ?string
    {
        if (trim((string) config('medsdi.token')) !== '') {
            return (string) config('medsdi.token');
        }

        $usuario = trim((string) config('medsdi.login_user'));
        $clave = (string) config('medsdi.login_pass');
        if ($usuario === '' || $clave === '') {
            return null;
        }

        $servidor = rtrim(mb_strtolower((string) config('medsdi.base_url')), '/');

        return Cache::remember('medsdi_api_token:'.md5($servidor.'|'.$usuario), now()->addHours(20), function () use ($usuario, $clave) {
            $resultado = $this->login($usuario, $clave);

            return $resultado['ok'] ? $resultado['token'] : null;
        });
    }

    /**
     * Igual que tokenActivo() pero con las credenciales del profesional demo
     * (MEDSDI_API_PROFESIONAL_LOGIN_USER/PASS), en cache aparte para no mezclar
     * su sesión con la del paciente fijo.
     */
    private function tokenActivoProfesional(): ?string
    {
        $usuario = trim((string) config('medsdi.profesional_login_user'));
        $clave = (string) config('medsdi.profesional_login_pass');
        if ($usuario === '' || $clave === '') {
            return null;
        }

        $servidor = rtrim(mb_strtolower((string) config('medsdi.base_url')), '/');

        return Cache::remember('medsdi_api_token_profesional:'.md5($servidor.'|'.$usuario), now()->addHours(20), function () use ($usuario, $clave) {
            $resultado = $this->login($usuario, $clave);

            return $resultado['ok'] ? $resultado['token'] : null;
        });
    }

    private function tokenActivoAsistente(): ?string
    {
        $usuario = trim((string) config('medsdi.asistente_login_user'));
        $clave = (string) config('medsdi.asistente_login_pass');
        if ($usuario === '' || $clave === '') {
            return null;
        }

        $servidor = rtrim(mb_strtolower((string) config('medsdi.base_url')), '/');

        return Cache::remember('medsdi_api_token_asistente:'.md5($servidor.'|'.mb_strtolower($usuario)), now()->addHours(20), function () use ($usuario, $clave) {
            $resultado = $this->login($usuario, $clave);

            return $resultado['ok'] ? $resultado['token'] : null;
        });
    }

    /**
     * Lista los bonos del profesional autenticado en Med-SDI, cada uno con su
     * hora médica asociada (GET /api/profesional/mis_bonos).
     */
    public function misBonosProfesional(): array
    {
        $token = $this->tokenActivoProfesional();
        if (! $token) {
            return $this->noDisponible('No fue posible autenticar al profesional en Med-SDI (revise MEDSDI_API_PROFESIONAL_LOGIN_USER/PASS).');
        }

        try {
            $response = $this->request()->withToken($token)
                ->withHeaders(['X-Auth-Token' => $token])
                ->get(rtrim((string) config('medsdi.base_url'), '/').'/api/profesional/mis_bonos');

            return $this->interpretar($response, 'registros');
        } catch (ConnectionException $e) {
            return $this->noDisponible('No fue posible conectar con Med-SDI para listar los bonos del profesional.');
        }
    }

    /**
     * Inicia la atención (id_estado=5, Realizando) de una hora médica del
     * profesional autenticado (POST /api/profesional/iniciar_atencion_hora_medica).
     */
    public function iniciarAtencionHoraMedicaProfesional(int $idHora): array
    {
        $token = $this->tokenActivoProfesional();
        if (! $token) {
            return $this->noDisponible('No fue posible autenticar al profesional en Med-SDI (revise MEDSDI_API_PROFESIONAL_LOGIN_USER/PASS).');
        }

        try {
            $response = $this->request()->withToken($token)
                ->withHeaders(['X-Auth-Token' => $token])
                ->post(rtrim((string) config('medsdi.base_url'), '/').'/api/profesional/iniciar_atencion_hora_medica', ['id_hora' => $idHora]);

            return $this->interpretar($response, 'hora');
        } catch (ConnectionException $e) {
            return $this->noDisponible('No fue posible conectar con Med-SDI para iniciar la atención.');
        }
    }

    /**
     * Finaliza (id_estado=6, Realizada) una hora médica del profesional
     * autenticado (POST /api/profesional/finalizar_hora_medica).
     */
    public function finalizarHoraMedicaProfesional(int $idHora): array
    {
        $token = $this->tokenActivoProfesional();
        if (! $token) {
            return $this->noDisponible('No fue posible autenticar al profesional en Med-SDI (revise MEDSDI_API_PROFESIONAL_LOGIN_USER/PASS).');
        }

        try {
            $response = $this->request()->withToken($token)
                ->withHeaders(['X-Auth-Token' => $token])
                ->post(rtrim((string) config('medsdi.base_url'), '/').'/api/profesional/finalizar_hora_medica', ['id_hora' => $idHora]);

            return $this->interpretar($response, 'hora');
        } catch (ConnectionException $e) {
            return $this->noDisponible('No fue posible conectar con Med-SDI para finalizar la hora.');
        }
    }

    /**
     * Agenda en vivo del profesional autenticado (GET /api/profesional/agenda),
     * con el id_estado real de cada hora (no depende de que el paciente haya
     * sincronizado antes desde su bono).
     */
    public function agendaProfesional(): array
    {
        $token = $this->tokenActivoProfesional();
        if (! $token) {
            return $this->noDisponible('No fue posible autenticar al profesional en Med-SDI (revise MEDSDI_API_PROFESIONAL_LOGIN_USER/PASS).');
        }

        try {
            $response = $this->request()->withToken($token)
                ->withHeaders(['X-Auth-Token' => $token])
                ->get(rtrim((string) config('medsdi.base_url'), '/').'/api/profesional/agenda');

            return $this->interpretar($response, 'registros');
        } catch (ConnectionException $e) {
            return $this->noDisponible('No fue posible conectar con Med-SDI para consultar la agenda del profesional.');
        }
    }

    private function get(string $path, array $query, string $clave): array
    {
        if (! $this->configurada()) {
            return $this->noDisponible('Servicio Med-SDI no configurado (MEDSDI_API_URL vacío).');
        }

        try {
            $response = $this->request()->get(rtrim((string) config('medsdi.base_url'), '/').$path, $query);

            return $this->interpretar($response, $clave);
        } catch (ConnectionException $e) {
            return $this->noDisponible('No fue posible conectar con Med-SDI.');
        }
    }

    private function getAutenticado(string $path, array $query, string $clave): array
    {
        $token = $this->tokenActivo();
        if (! $token) {
            return $this->noDisponible('No fue posible autenticar con Med-SDI.');
        }

        try {
            $response = $this->request()->withHeaders(['X-Auth-Token' => $token])
                ->get(rtrim((string) config('medsdi.base_url'), '/').$path, $query);

            return $this->interpretar($response, $clave);
        } catch (ConnectionException $e) {
            return $this->noDisponible('No fue posible conectar con Med-SDI.');
        }
    }

    private function request(): PendingRequest
    {
        return \Illuminate\Support\Facades\Http::acceptJson()
            ->timeout(max(1, (int) config('medsdi.timeout', 5)))
            ->retry(1, 150, throw: false);
    }

    private function interpretar(Response $response, string $clave): array
    {
        $payload = $response->json() ?: [];
        if ($response->successful() && (int) ($payload['estado'] ?? 0) === 1) {
            return [
                'ok' => true,
                'disponible' => true,
                'registros' => $payload[$clave] ?? $payload['registros'] ?? [],
                'mensaje' => $payload['msj'] ?? $payload['mensaje'] ?? 'ok',
            ];
        }

        $mensajeValidacion = collect($payload['errors'] ?? [])->flatten()->first();
        $mensajeRemoto = $payload['msj'] ?? $payload['mensaje'] ?? $payload['message'] ?? null;

        Log::warning('Med-SDI rechazó una solicitud del sistema de bonos.', [
            'status' => $response->status(),
            'mensaje' => is_string($mensajeValidacion ?: $mensajeRemoto) ? ($mensajeValidacion ?: $mensajeRemoto) : null,
            'claves_respuesta' => array_keys($payload),
        ]);

        // No se reenvía el payload crudo del remoto: puede incluir trazas/errores internos de Med-SDI.
        return [
            'ok' => false,
            'disponible' => true,
            'registros' => [],
            'mensaje' => is_string($mensajeValidacion ?: $mensajeRemoto)
                ? ($mensajeValidacion ?: $mensajeRemoto)
                : 'Med-SDI rechazó la solicitud (HTTP '.$response->status().').',
        ];
    }

    private function noDisponible(string $mensaje): array
    {
        return ['ok' => false, 'disponible' => false, 'registros' => [], 'mensaje' => $mensaje];
    }

    private function configurada(): bool
    {
        return trim((string) config('medsdi.base_url')) !== '';
    }
}
