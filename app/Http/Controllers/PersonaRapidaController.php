<?php

namespace App\Http\Controllers;

use App\Models\PersonaBusqueda;
use App\Services\PersonasApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PersonaRapidaController extends Controller
{
    public function index()
    {
        return view('personas_rapidas.prueba');
    }

    public function buscar(Request $request, PersonasApiService $personasApi): JsonResponse
    {
        $rut = PersonaBusqueda::normalizarRut($request->query('rut'));
        $rutPartes = $this->rutPartes($rut);

        if (! $rutPartes['ok']) {
            return response()->json([
                'ok' => false,
                'found' => false,
                'ready_to_create' => false,
                'message' => $rutPartes['message'],
            ]);
        }

        $remoto = $personasApi->buscar($rut);
        if ($remoto['disponible'] ?? false) {
            if ($remoto['found'] ?? false) {
                return response()->json(array_merge($remoto, [
                    'ready_to_create' => false,
                    'origen' => 'personas_api',
                    'message' => 'Persona encontrada en Personas.',
                ]));
            }

            if (($remoto['resultado'] ?? null) === 'no_encontrada') {
                return response()->json(array_merge($remoto, [
                    'ready_to_create' => true,
                    'rut_cuerpo' => $rutPartes['cuerpo'],
                    'rut_dv' => $rutPartes['dv'],
                    'origen' => 'personas_api',
                    'message' => 'RUT no encontrado. Puede registrarlo en Personas.',
                ]));
            }

            return response()->json($remoto, $remoto['status'] ?? 502);
        }

        if (! config('personas.fallback_local')) {
            return response()->json($remoto, 503);
        }

        try {
            $persona = PersonaBusqueda::porRut($rut)->first();
        } catch (\Throwable $e) {
            Log::error('Error consultando persona rapida', [
                'rut' => $rut,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'found' => false,
                'ready_to_create' => false,
                'message' => 'No se pudo consultar la base rapida. Revise la conexion e intente nuevamente.',
            ], 500);
        }

        if (! $persona) {
            return response()->json([
                'ok' => true,
                'found' => false,
                'ready_to_create' => true,
                'rut_normalizado' => $rut,
                'rut_cuerpo' => $rutPartes['cuerpo'],
                'rut_dv' => $rutPartes['dv'],
                'message' => 'RUT no encontrado. Puede agregarlo a la base rapida.',
            ]);
        }

        return response()->json([
            'ok' => true,
            'found' => true,
            'ready_to_create' => false,
            'persona' => $this->payload($persona),
            'message' => 'Persona encontrada.',
        ]);
    }

    public function guardar(Request $request, PersonasApiService $personasApi): JsonResponse
    {
        $data = $request->validate([
            'rut' => ['required', 'string', 'max:30'],
            'nombre1' => ['nullable', 'string', 'max:255'],
            'appaterno' => ['nullable', 'string', 'max:255'],
            'apmaterno' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'direccion' => ['nullable', 'string', 'max:500'],
        ]);

        $rut = PersonaBusqueda::normalizarRut($data['rut']);
        $rutPartes = $this->rutPartes($rut);

        if (! $rutPartes['ok']) {
            return response()->json([
                'ok' => false,
                'message' => $rutPartes['message'],
            ], 422);
        }

        $nombre1 = trim((string) ($data['nombre1'] ?? ''));
        $appaterno = trim((string) ($data['appaterno'] ?? ''));
        $apmaterno = trim((string) ($data['apmaterno'] ?? ''));
        $nombreCompleto = trim(implode(' ', array_filter([$nombre1, $appaterno, $apmaterno])));

        if ($nombreCompleto === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Debe ingresar al menos un nombre o apellido.',
            ], 422);
        }

        $remoto = $personasApi->guardar(array_merge($data, ['rut' => $rut]));
        if ($remoto['disponible'] ?? false) {
            return response()->json(array_merge($remoto, ['origen' => 'personas_api']), $remoto['status'] ?? 200);
        }

        if (! config('personas.fallback_local')) {
            return response()->json($remoto, 503);
        }

        $persona = PersonaBusqueda::porRut($rut)->first();
        $created = false;

        $masterData = [
            'rut_original' => $this->formatearRut($rut),
            'rut_cuerpo' => $rutPartes['cuerpo'],
            'rut_dv' => $rutPartes['dv'],
            'nombre1' => $nombre1 !== '' ? $nombre1 : null,
            'appaterno' => $appaterno !== '' ? $appaterno : null,
            'apmaterno' => $apmaterno !== '' ? $apmaterno : null,
            'nombre_completo' => $nombreCompleto,
            'estado' => 'activo',
        ];

        try {
            if ($persona) {
                $persona->update($masterData);
            } else {
                $created = true;
                $persona = PersonaBusqueda::create(array_merge([
                    'id' => $this->siguienteId(),
                    'rut_normalizado' => $rut,
                ], $masterData));
            }

            DB::connection('personas_fast')
                ->table('personas_rapidas_ediciones')
                ->updateOrInsert(
                    ['rut_normalizado' => $rut],
                    [
                        'nombre1' => $nombre1 !== '' ? $nombre1 : null,
                        'appaterno' => $appaterno !== '' ? $appaterno : null,
                        'apmaterno' => $apmaterno !== '' ? $apmaterno : null,
                        'nombre_completo' => $nombreCompleto,
                        'email' => $data['email'] ?? null,
                        'telefono' => $data['telefono'] ?? null,
                        'direccion_encrypted' => ! empty($data['direccion'])
                            ? Crypt::encryptString($data['direccion'])
                            : null,
                        'origen' => 'formulario_prueba',
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
        } catch (\Throwable $e) {
            Log::error('Error guardando persona rapida', [
                'rut' => $rut,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'No se pudo guardar la persona. Revise la base rapida y vuelva a intentar.',
            ], 500);
        }

        try {
            $persona = PersonaBusqueda::porRut($rut)->firstOrFail();
        } catch (\Throwable $e) {
            Log::error('Error recargando persona rapida guardada', [
                'rut' => $rut,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'La persona se guardo, pero no se pudo recargar el resultado.',
            ], 500);
        }

        return response()->json([
            'ok' => true,
            'found' => true,
            'created' => $created,
            'persona' => $this->payload($persona),
            'message' => $created
                ? 'Persona agregada a la base rapida.'
                : 'Persona actualizada.',
        ]);
    }

    private function siguienteId(): int
    {
        return ((int) PersonaBusqueda::max('id')) + 1;
    }

    private function payload(PersonaBusqueda $persona): array
    {
        $edicion = DB::connection('personas_fast')
            ->table('personas_rapidas_ediciones')
            ->where('rut_normalizado', $persona->rut_normalizado)
            ->first();

        return [
            'id' => $persona->id,
            'rut_original' => $persona->rut_original,
            'rut_normalizado' => $persona->rut_normalizado,
            'rut_cuerpo' => $persona->rut_cuerpo,
            'rut_dv' => $persona->rut_dv,
            'nombre1' => $edicion->nombre1 ?? $persona->nombre1,
            'appaterno' => $edicion->appaterno ?? $persona->appaterno,
            'apmaterno' => $edicion->apmaterno ?? $persona->apmaterno,
            'nombre_completo' => $edicion->nombre_completo ?? $persona->nombre_completo,
            'email' => $edicion->email ?? null,
            'telefono' => $edicion->telefono ?? null,
            'direccion' => $this->decryptNullable($edicion->direccion_encrypted ?? null),
        ];
    }

    private function rutPartes(string $rut): array
    {
        if (! preg_match('/^([0-9]+)([0-9K])$/', $rut, $matches)) {
            return ['ok' => false, 'message' => 'Ingrese un RUT valido: numeros y digito verificador.'];
        }

        $cuerpo = (int) $matches[1];

        if ($cuerpo < 1000000) {
            return ['ok' => false, 'message' => 'No se aceptan RUT con numero menor a 1.000.000.'];
        }

        return [
            'ok' => true,
            'cuerpo' => $cuerpo,
            'dv' => $matches[2],
        ];
    }

    private function formatearRut(string $rut): string
    {
        return substr($rut, 0, -1).'-'.substr($rut, -1);
    }

    private function decryptNullable(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
