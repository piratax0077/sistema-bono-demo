<?php

namespace App\Http\Controllers\Api\Totem;

use App\Http\Controllers\Controller;
use App\Models\VoucherProfesional;
use Illuminate\Http\Request;

class PrestadorTotemController extends Controller
{
    public function index()
    {
        $prestadores = VoucherProfesional::query()
            // ->where('activo', 1)
            ->limit(20)
            ->get();

        return response()->json([
            'ok' => true,
            'prestadores' => $prestadores,
        ]);
    }
public function buscar(Request $request)
{
    $q = trim($request->get('q', ''));

    if (strlen($q) < 2) {
        return response()->json([
            'ok' => true,
            'prestadores' => [],
        ]);
    }

    $prestadores = \App\Models\VoucherProfesional::query()
        ->where(function ($query) use ($q) {
            $query->where('nombre', 'like', "%{$q}%")
                ->orWhere('rut', 'like', "%{$q}%")
                ->orWhere('especialidad', 'like', "%{$q}%");
        })
        ->limit(15)
        ->get();

    return response()->json([
        'ok' => true,
        'prestadores' => $prestadores->map(function ($p) {
            return [
                'id' => $p->id,
                'tipo' => 'veterinario',
                'nombre' => $p->nombre ?? '',
                'rut' => $p->rut ?? '',
                'especialidad' => $p->especialidad ?? '',
                'email' => $p->email ?? '',
                'telefono' => $p->telefono ?? '',
                'direccion' => $p->direccion ?? '',
            ];
        })->values(),
    ]);
}

}
