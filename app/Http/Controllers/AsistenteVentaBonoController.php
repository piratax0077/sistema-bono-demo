<?php

namespace App\Http\Controllers;

use App\Services\AgendaExternaCompraService;
use App\Services\MedsdiAgendaApiService;
use Illuminate\Http\Request;
use Throwable;

class AsistenteVentaBonoController extends Controller
{
    public function paciente(Request $request, MedsdiAgendaApiService $api)
    {
        $data = $request->validate(['rut' => ['required', 'string', 'max:30']]);

        return response()->json($api->pacientePorRutComoAsistente($data['rut']));
    }

    public function agendar(Request $request, AgendaExternaCompraService $service)
    {
        abort_unless(config('demo.enabled') && config('payments.allow_demo'), 404);
        $data = $request->validate([
            'id_profesional' => ['required', 'integer'], 'nombre_profesional' => ['required', 'string', 'max:190'],
            'especialidad' => ['nullable', 'string', 'max:190'], 'id_especialidad' => ['nullable', 'integer'],
            'id_lugar' => ['required', 'integer'], 'lugar_nombre' => ['nullable', 'string', 'max:190'],
            'direccion' => ['nullable', 'string', 'max:255'], 'fecha_hora' => ['required', 'date'],
            'rut' => ['required', 'string', 'max:30'], 'id_prestacion' => ['required', 'integer'],
            'titular_rut' => ['nullable', 'string', 'max:30'],
            'origen_prestacion' => ['required', 'in:prestacion_fonasa_bono'],
            'prestacion_codigo' => ['required', 'string', 'max:40'], 'prestacion_nombre' => ['required', 'string', 'max:255'],
        ]);

        try {
            $voucher = $service->comprarComoAsistente($request->user(), $data, $data['rut'], $request->ip());

            return response()->json(['ok' => true, 'mensaje' => 'Hora reservada correctamente para el paciente.', 'voucher' => $voucher->codigo]);
        } catch (Throwable $exception) {
            return response()->json(['ok' => false, 'mensaje' => $exception->getMessage()], 422);
        }
    }
}
