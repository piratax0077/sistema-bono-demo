<?php

namespace App\Http\Controllers;

use App\Services\AgendaOnlineCompraService;
use Illuminate\Http\Request;
use Throwable;

class ClienteAgendaOnlineController extends Controller
{
    public function comprar(Request $request, AgendaOnlineCompraService $service)
    {
        abort_unless(config('demo.enabled') && config('payments.allow_demo'), 404);
        $data = $request->validate([
            'horario_id' => ['required', 'integer', 'exists:agenda_online_horarios,id'],
            'rut' => ['required', 'string', 'max:30'],
            'metodo_pago' => ['required', 'in:tarjeta_demo_online'],
            'return_to' => ['nullable', 'in:paciente.agenda,paciente.totem'],
        ]);
        $returnRoute = in_array(($data['return_to'] ?? null), ['paciente.agenda', 'paciente.totem'], true)
            ? $data['return_to']
            : 'cliente.dashboard';
        try {
            $voucher = $service->comprar($request->user(), (int) $data['horario_id'], $data['rut'], $request->ip());
            return redirect()->route($returnRoute)
                ->with('ok', 'Hora reservada, pago simulado aprobado y bono generado correctamente.')
                ->with('agenda_online_voucher_id', $voucher->id);
        } catch (Throwable $exception) {
            return redirect()->route($returnRoute)->withInput()->with('error', $exception->getMessage());
        }
    }
}
