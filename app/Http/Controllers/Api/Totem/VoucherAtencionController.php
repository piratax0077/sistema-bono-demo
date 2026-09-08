<?php

namespace App\Http\Controllers\Api\Totem;

use App\Helpers\SecurityLogger;
use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\VoucherAgenda;
use App\Models\VoucherAtencion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoucherAtencionController extends Controller
{
    public function solicitarHora(Request $request, $voucherId)
    {
        $request->validate([
            'fecha_hora_solicitada' => 'required|date',
            'profesional_id' => 'nullable|integer',
            'centro_atencion_id' => 'nullable|integer',
            'observacion' => 'nullable|string',
        ]);

        $voucher = Voucher::findOrFail($voucherId);

        $agenda = VoucherAgenda::updateOrCreate(
            ['voucher_id' => $voucher->id],
            [
                'cliente_id' => $voucher->cliente_id,
                'mascota_id' => $voucher->mascota_id,
                'profesional_id' => $request->profesional_id ?: $voucher->profesional_id,
                'centro_atencion_id' => $request->centro_atencion_id,
                'fecha_hora_solicitada' => $request->fecha_hora_solicitada,
                'estado' => 'hora_solicitada',
                'observacion' => $request->observacion,
            ]
        );

        $voucher->update([
            'agenda_id' => $agenda->id,
        ]);

        SecurityLogger::log(
            'voucher_hora_solicitada',
            'VoucherAgenda',
            $agenda->id,
            'ok',
            'Hora solicitada para voucher',
            $voucher->cliente_id
        );

        return response()->json([
            'ok' => true,
            'agenda' => $agenda,
        ]);
    }

    public function confirmarHora(Request $request, $voucherId)
    {
        $request->validate([
            'fecha_hora_confirmada' => 'required|date',
            'observacion' => 'nullable|string',
        ]);

        $voucher = Voucher::findOrFail($voucherId);

        $agenda = VoucherAgenda::where('voucher_id', $voucher->id)->first();

        if (!$agenda) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'El voucher no tiene hora solicitada',
            ], 404);
        }

        $agenda->update([
            'fecha_hora_confirmada' => $request->fecha_hora_confirmada,
            'estado' => 'hora_confirmada',
            'observacion' => $request->observacion ?: $agenda->observacion,
        ]);

        SecurityLogger::log(
            'voucher_hora_confirmada',
            'VoucherAgenda',
            $agenda->id,
            'ok',
            'Hora confirmada para voucher',
            $voucher->cliente_id
        );

        return response()->json([
            'ok' => true,
            'agenda' => $agenda,
        ]);
    }

    public function cerrarAtencion(Request $request, $voucherId)
    {
        $request->validate([
            'profesional_id' => 'required|integer',
            'inicio_atencion' => 'nullable|date',
            'fin_atencion' => 'nullable|date',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'direccion' => 'nullable|string',
            'observacion' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $voucherId) {
            $voucher = Voucher::lockForUpdate()->findOrFail($voucherId);

            if (!in_array($voucher->estado, ['activo', 'pagado', 'validado_atencion'])) {
                return response()->json([
                    'ok' => false,
                    'mensaje' => 'Voucher no disponible para cerrar atención',
                    'estado' => $voucher->estado,
                ], 409);
            }

            $agenda = VoucherAgenda::where('voucher_id', $voucher->id)->first();

            $atencion = VoucherAtencion::updateOrCreate(
                ['voucher_id' => $voucher->id],
                [
                    'agenda_id' => $agenda ? $agenda->id : null,
                    'cliente_id' => $voucher->cliente_id,
                    'mascota_id' => $voucher->mascota_id,
                    'profesional_id' => $request->profesional_id,
                    'inicio_atencion' => $request->inicio_atencion ?: now(),
                    'fin_atencion' => $request->fin_atencion ?: now(),
                    'cerrada_at' => now(),
                    'ip_profesional' => $request->ip(),
                    'user_agent_profesional' => $request->userAgent(),
                    'lat' => $request->lat,
                    'lng' => $request->lng,
                    'direccion' => $request->direccion,
                    'estado' => 'cerrada_por_profesional',
                    'riesgo' => 'bajo',
                    'observacion' => $request->observacion,
                ]
            );

            if ($agenda) {
                $agenda->update([
                    'estado' => 'atencion_realizada',
                ]);
            }
$voucher->update([
    'agenda_id' => $agenda ? $agenda->id : null,
    'atencion_id' => $atencion->id,
    'profesional_atendio_id' => $request->profesional_id,
    'atencion_cerrada_at' => now(),
    'ip_profesional' => $request->ip(),
    'estado_validacion' => 'cerrada_por_profesional',
    'riesgo_validacion' => 'bajo',
]);

            SecurityLogger::log(
                'voucher_atencion_cerrada',
                'VoucherAtencion',
                $atencion->id,
                'ok',
                'Atención cerrada por profesional',
                $voucher->cliente_id
            );

            return response()->json([
                'ok' => true,
                'voucher' => $voucher->fresh(),
                'atencion' => $atencion->fresh(),
            ]);
        });
    }
public function validarAtencion(Request $request, $voucherId)
{
    $request->validate([
        'asistente_id' => 'required|integer',
        'lat' => 'nullable|numeric',
        'lng' => 'nullable|numeric',
        'direccion' => 'nullable|string',
        'observacion' => 'nullable|string',
    ]);

    return DB::transaction(function () use ($request, $voucherId) {
        $voucher = Voucher::lockForUpdate()->findOrFail($voucherId);

        $atencion = VoucherAtencion::where('voucher_id', $voucher->id)
            ->lockForUpdate()
            ->first();

        if (!$atencion) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'No existe atención para este voucher',
            ], 404);
        }

        if ($atencion->estado === 'validada_por_asistente' || $atencion->asistente_id) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'La atención ya fue validada.',
            ], 409);
        }

        if ($atencion->estado !== 'cerrada_por_profesional') {
            return response()->json([
                'ok' => false,
                'mensaje' => 'La atención no está cerrada por profesional',
            ], 409);
        }

        $riesgo = 'bajo';

        if ((int) $atencion->profesional_id === (int) $request->asistente_id) {
            $riesgo = 'alto';
        }

        if ($atencion->ip_profesional && $atencion->ip_profesional === $request->ip()) {
            $riesgo = $riesgo === 'alto' ? 'alto' : 'medio';
        }

        $hashAuditoria = hash('sha256', implode('|', [
            $voucher->id,
            $atencion->id,
            $atencion->profesional_id,
            $request->asistente_id,
            $atencion->cerrada_at,
            now(),
            $atencion->ip_profesional,
            $request->ip(),
        ]));

        $atencion->update([
            'asistente_id' => $request->asistente_id,
            'validada_at' => now(),
            'ip_asistente' => $request->ip(),
            'user_agent_asistente' => $request->userAgent(),
            'lat' => $request->lat ?: $atencion->lat,
            'lng' => $request->lng ?: $atencion->lng,
            'direccion' => $request->direccion ?: $atencion->direccion,
            'estado' => 'validada_por_asistente',
            'riesgo' => $riesgo,
            'hash_auditoria' => $hashAuditoria,
            'observacion' => $request->observacion ?: $atencion->observacion,
        ]);

        $voucher->update([
            'agenda_id' => $atencion->agenda_id,
            'atencion_id' => $atencion->id,
            'profesional_atendio_id' => $atencion->profesional_id,
            'asistente_valido_id' => $request->asistente_id,
            'atencion_cerrada_at' => $atencion->cerrada_at,
            'validado_at' => now(),
            'ip_profesional' => $atencion->ip_profesional,
            'ip_asistente' => $request->ip(),
            'estado_validacion' => 'validada_por_asistente',
            'riesgo_validacion' => $riesgo,
            'estado' => 'validado_atencion',
        ]);

        SecurityLogger::log(
            'voucher_atencion_validada_asistente',
            'VoucherAtencion',
            $atencion->id,
            $riesgo === 'alto' ? 'observado' : 'ok',
            'Atención validada por asistente; bono habilitado para cobro. Riesgo: '.$riesgo,
            $voucher->cliente_id
        );

        \App\Models\VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'bono_habilitado_para_cobro',
            'usuario_tipo' => auth()->check() ? auth()->user()->rol : 'totem',
            'usuario_id' => auth()->id(),
            'descripcion' => 'Atención cerrada y validada. Bono habilitado para cobro; todavía no rendido.',
            'ip' => $request->ip(),
        ]);

        if (in_array($riesgo, ['medio', 'alto'])) {
            \App\Models\VoucherAlerta::create([
                'voucher_id' => $voucher->id,
                'tipo_alerta' => 'riesgo_validacion_atencion',
                'nivel' => $riesgo === 'alto' ? 'rojo' : 'amarillo',
                'descripcion' => 'Atención validada con riesgo '.$riesgo.'. Revisar IP/profesional/asistente.',
                'resuelta' => false,
            ]);

            \App\Models\AuditorNotificacion::create([
                'voucher_id' => $voucher->id,
                'titulo' => 'Riesgo en validación de atención',
                'mensaje' => 'El voucher '.$voucher->codigo.' fue validado con riesgo '.$riesgo.'.',
            ]);
        }

        return response()->json([
            'ok' => true,
            'riesgo' => $riesgo,
            'voucher' => $voucher->fresh(),
            'atencion' => $atencion->fresh(),
        ]);
    });
}


}
