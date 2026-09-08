<?php

namespace App\Http\Controllers\Api\Totem;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cliente;
use Illuminate\Support\Facades\Crypt;
use App\Services\PersonasApiService;

class ClienteTotemController extends Controller
{
    public function buscar(Request $request, PersonasApiService $personasApi)
    {
        $request->validate([
            'rut' => 'nullable|string',
            'telefono' => 'nullable|string',
        ]);

        $cliente = null;

        if ($request->rut) {

            $personaRemota = $personasApi->buscar($request->rut, optional($request->get('totem'))->id);
            if (($personaRemota['disponible'] ?? false) && ($personaRemota['found'] ?? false)) {
                $persona = $personaRemota['persona'];
                return response()->json([
                    'ok' => true,
                    'inscrito' => true,
                    'origen' => 'personas_api',
                    'correlation_id' => $personaRemota['correlation_id'],
                    'cliente' => [
                        'id' => $persona['id'] ?? null,
                        'nombre' => $persona['nombre_completo'] ?? null,
                        'rut' => $persona['rut_original'] ?? $request->rut,
                        'telefono' => $persona['telefono'] ?? null,
                        'email' => $persona['email'] ?? null,
                    ],
                    'opciones_entrega' => ['app', 'whatsapp', 'email', 'profesional', 'impresion'],
                ]);
            }

            if (($personaRemota['disponible'] ?? false) && ($personaRemota['resultado'] ?? null) === 'no_encontrada') {
                return response()->json([
                    'ok' => true,
                    'inscrito' => false,
                    'origen' => 'personas_api',
                    'correlation_id' => $personaRemota['correlation_id'],
                    'mensaje' => 'Persona no encontrada',
                ]);
            }

            if (! config('personas.fallback_local')) {
                return response()->json($personaRemota, 503);
            }

            $rutInput = strtoupper(
                str_replace(['.', '-', ' '], '', trim($request->rut))
            );

            $cliente = User::whereRaw(
                "UPPER(REPLACE(REPLACE(REPLACE(rut, '.', ''), '-', ''), ' ', '')) = ?",
                [$rutInput]
            )->first();
        }

        if (!$cliente && $request->telefono) {
            $cliente = User::where('telefono', trim($request->telefono))->first();
        }

        if (!$cliente) {
            return response()->json([
                'ok' => true,
                'inscrito' => false,
                'mensaje' => 'Cliente no encontrado'
            ]);
        }

        return response()->json([
            'ok' => true,
            'inscrito' => true,
            'cliente' => [
                'id' => $cliente->id,
                'nombre' => $cliente->name,
                'rut' => $cliente->rut,
                'telefono' => $cliente->telefono,
                'email' => $cliente->email,
            ],
            'opciones_entrega' => [
                'app',
                'whatsapp',
                'email',
                'veterinario',
                'impresion'
            ]
        ]);
    }

    // public function registrar(Request $request)
    // {
    //     $request->validate([
    //         'rut' => 'required|string',
    //         'nombre' => 'required|string',
    //         'telefono' => 'required|string',
    //         'email' => 'nullable|email'
    //     ]);

    //     $existe = User::where('rut', trim($request->rut))->first();

    //     if ($existe) {
    //         return response()->json([
    //             'ok' => false,
    //             'mensaje' => 'Cliente ya registrado'
    //         ], 409);
    //     }

    //     $cliente = User::create([
    //         'name' => $request->nombre,
    //         'rut' => trim($request->rut),
    //         'telefono' => trim($request->telefono),
    //         'email' => $request->email,
    //         'password' => bcrypt('123456')
    //     ]);

    //     return response()->json([
    //         'ok' => true,
    //         'cliente' => [
    //             'id' => $cliente->id,
    //             'nombre' => $cliente->name,
    //             'rut' => $cliente->rut,
    //             'telefono' => $cliente->telefono,
    //             'email' => $cliente->email,
    //         ]
    //     ]);
    // }
    public function registrar(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'tipo' => 'required|string|in:dueno_mascota,prestador,distribuidor_alimentos,farmacia',
            'rut' => 'required|string|max:30',
            'email' => 'nullable|email|max:150',
            'telefono' => 'nullable|string|max:50',
            'monto' => 'required|numeric|min:0',
            'medio_pago' => 'required|string',
        ]);

        $costos = [
            'dueno_mascota' => 5000,
            'prestador' => 25000,
            'distribuidor_alimentos' => 30000,
            'farmacia' => 30000,
        ];

        $montoCorrecto = $costos[$request->tipo];

        if ((float) $request->monto !== (float) $montoCorrecto) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Monto de inscripción no válido',
            ], 422);
        }

        $rutNormalizado = strtoupper(str_replace(['.', '-', ' '], '', trim($request->rut)));

        if (Cliente::where('rut_hash', hash('sha256', $rutNormalizado))->exists()) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'El cliente ya está registrado',
            ], 409);
        }

        $cliente = Cliente::create([
            'nombre' => $request->nombre,
            'rut' => Crypt::encryptString($rutNormalizado),
            'rut_hash' => hash('sha256', $rutNormalizado),
            'email' => $request->email,
            'telefono' => $request->telefono,
            'tipo' => $request->tipo,
            'estado' => 'pendiente_pago',
            'fecha_inscripcion' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'cliente' => [
                'id' => $cliente->id,
                'nombre' => $cliente->nombre,
                'tipo' => $cliente->tipo,
                'estado' => $cliente->estado,
            ],
            'monto' => $montoCorrecto,
            'estado_pago' => 'pendiente',
            'mensaje' => 'Inscripción creada pendiente de pago',
        ]);
    }
public function mascotas($id)
{
    $mascotas = \App\Models\VoucherMascota::query()
        ->where('cliente_id', $id)
        ->where('activo', 1)
        ->get();

    return response()->json([
        'ok' => true,
        'mascotas' => $mascotas,
    ]);
}

public function registrarMascota(Request $request)
{
    $request->validate([
        'cliente_id' => 'required|integer',
        'nombre' => 'required|string|max:100',
        'especie' => 'nullable|string|max:50',
        'raza' => 'nullable|string|max:100',
        'edad' => 'nullable',
        'dueno_rut' => 'nullable|string|max:30',
        'dueno_nombre' => 'nullable|string|max:150',
        'dueno_telefono' => 'nullable|string|max:50',
        'dueno_email' => 'nullable|email|max:150',
    ]);

    $mascota = new \App\Models\VoucherMascota();
    $mascota->cliente_id = $request->cliente_id;
    $mascota->nombre = $request->nombre;
    $mascota->especie = $request->especie ?: 'Canino';
    $mascota->raza = $request->raza ?: '';
    $mascota->edad = $request->edad ?: null;

    $mascota->dueno_rut = $request->dueno_rut ?: 'SIN-RUT';
    $mascota->dueno_nombre = $request->dueno_nombre ?: 'Cliente tótem';
    $mascota->dueno_telefono = $request->dueno_telefono ?: '';
    $mascota->dueno_email = $request->dueno_email ?: '';

    $mascota->activo = 1;
    $mascota->save();

    return response()->json([
        'ok' => true,
        'mascota' => $mascota,
        'mensaje' => 'Mascota registrada correctamente',
    ]);
}


}
