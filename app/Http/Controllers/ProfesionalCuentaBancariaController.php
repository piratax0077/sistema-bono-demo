<?php

namespace App\Http\Controllers;

use App\Services\MedsdiAgendaApiService;
use Illuminate\Http\Request;

class ProfesionalCuentaBancariaController extends Controller
{
    public function update(Request $request, MedsdiAgendaApiService $medsdiApi)
    {
        $data = $request->validateWithBag('cuentaBancariaProfesional', [
            'cuenta_id' => ['nullable', 'integer'],
            'titular' => ['required', 'string', 'max:150'],
            'banco_id' => ['required', 'integer'],
            'tipo_cuenta' => ['required', 'string', 'max:100'],
            'numero_cuenta' => ['required', 'string', 'min:3', 'max:40', 'regex:/^[0-9A-Za-z.-]+$/'],
            'email' => ['required', 'email', 'max:150'],
        ]);

        $resultado = $medsdiApi->actualizarCuentaBancariaProfesional($data);

        return back()
            ->with('abrir_cuenta_bancaria_profesional', true)
            ->with($resultado['ok'] ? 'ok' : 'error', $resultado['mensaje']);
    }

    public function notify(Request $request, MedsdiAgendaApiService $medsdiApi)
    {
        $data = $request->validate(['cuenta_id' => ['required', 'integer']]);
        $resultado = $medsdiApi->notificarCuentaBancariaProfesional((int) $data['cuenta_id']);

        return response()->json($resultado, ($resultado['ok'] ?? false) ? 200 : 422);
    }

    public function destroy(Request $request, MedsdiAgendaApiService $medsdiApi)
    {
        $data = $request->validate(['cuenta_id' => ['required', 'integer']]);
        $resultado = $medsdiApi->eliminarCuentaBancariaProfesional((int) $data['cuenta_id']);

        return response()->json($resultado, ($resultado['ok'] ?? false) ? 200 : 422);
    }
}
