<?php

namespace App\Http\Controllers\Api\Totem;

use App\Http\Controllers\Controller;
use App\Models\Campana;
use App\Models\VoucherProfesional;
use App\Models\VoucherServicio;
use Illuminate\Http\Request;

class HomeTotemController extends Controller
{
    public function index(Request $request)
    {
        $totem = $request->get('totem');

        $servicios = VoucherServicio::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'descripcion', 'valor_base', 'copago_base'])
            ->map(function ($servicio) {
                $valorBase = (float) $servicio->valor_base;
                $copagoBase = (float) $servicio->copago_base;

                return [
                    'id' => $servicio->id,
                    'nombre' => $servicio->nombre,
                    'name' => $servicio->nombre,
                    'descripcion' => $servicio->descripcion,
                    'description' => $servicio->descripcion,
                    'valor_base' => $valorBase,
                    'copago_base' => $copagoBase,
                    'price' => $valorBase,
                    'coverage_percent' => $valorBase > 0
                        ? max(0, min(100, (int) round((($valorBase - $copagoBase) * 100) / $valorBase)))
                        : 0,
                ];
            });

        $prestadores = VoucherProfesional::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'rut', 'especialidad', 'email', 'telefono'])
            ->map(function ($profesional) {
                return [
                    'id' => $profesional->id,
                    'tipo' => 'veterinario',
                    'nombre' => $profesional->nombre,
                    'name' => $profesional->nombre,
                    'rut' => $profesional->rut,
                    'especialidad' => $profesional->especialidad,
                    'specialty' => $profesional->especialidad,
                    'email' => $profesional->email,
                    'phone' => $profesional->telefono,
                    'direccion' => null,
                    'address' => null,
                ];
            });

        return response()->json([
            'ok' => true,
            'totem' => [
                'id' => $totem->id,
                'codigo' => $totem->codigo,
                'nombre' => $totem->nombre,
                'ubicacion' => $totem->ubicacion,
                'version' => $totem->version,
            ],
            'servicios' => $servicios,
            'prestadores' => $prestadores,
            'opciones_entrega' => ['app', 'whatsapp', 'email', 'veterinario', 'impresion'],
            'campanas' => Campana::where('activa', true)
                ->get(['id', 'nombre', 'descripcion']),
        ]);
    }
}
