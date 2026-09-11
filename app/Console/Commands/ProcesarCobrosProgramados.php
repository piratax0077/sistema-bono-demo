<?php

namespace App\Console\Commands;

use App\Models\Voucher;
use App\Models\VoucherAuditoria;
use App\Models\VoucherCobro;
use App\Models\VoucherCobroProgramacion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcesarCobrosProgramados extends Command
{
    protected $signature = 'cobros:procesar-programados';
    protected $description = 'Envía a cobro los bonos clínicamente validados según la frecuencia elegida por cada profesional';

    public function handle()
    {
        $programaciones = VoucherCobroProgramacion::with('user')
            ->where('activo', true)
            ->whereNotNull('proxima_ejecucion_at')
            ->where('proxima_ejecucion_at', '<=', now())
            ->get();

        foreach ($programaciones as $programacion) {
            try {
                $total = $this->procesar($programacion);
                $programacion->update([
                    'ultima_ejecucion_at' => now(),
                    'proxima_ejecucion_at' => VoucherCobroProgramacion::siguienteEjecucion($programacion->frecuencia),
                    'ultimo_total_procesado' => $total,
                    'ultimo_resultado' => $total.' '.($total === 1 ? 'bono enviado' : 'bonos enviados').' a cobro',
                ]);
                $this->info("Programación {$programacion->id}: {$total} bonos procesados.");
            } catch (\Throwable $e) {
                Log::error('Error al procesar cobros automáticos', ['programacion_id' => $programacion->id, 'error' => $e->getMessage()]);
                $programacion->update([
                    'proxima_ejecucion_at' => VoucherCobroProgramacion::siguienteEjecucion($programacion->frecuencia),
                    'ultimo_resultado' => 'Error durante la ejecución automática',
                ]);
                $this->error("Programación {$programacion->id}: no pudo procesarse.");
            }
        }

        return self::SUCCESS;
    }

    private function procesar(VoucherCobroProgramacion $programacion): int
    {
        $user = $programacion->user;
        if (! $user || $user->rol !== 'profesional') {
            throw new \RuntimeException('La programación no pertenece a un profesional vigente.');
        }

        return DB::transaction(function () use ($user) {
            $vouchers = Voucher::where(function ($query) use ($user) {
                    $query->where('profesional_id', $user->profesional_id)->orWhereNotNull('prestador_nombre');
                })
                ->where('estado', 'validado_atencion')
                ->where('qr_usado', false)
                ->whereDoesntHave('cobros')
                ->lockForUpdate()
                ->get();

            foreach ($vouchers as $voucher) {
                $voucher->update(['estado' => 'cobrado', 'qr_usado' => true, 'qr_usado_at' => now(), 'usado_en' => now()]);
                VoucherCobro::create([
                    'voucher_id' => $voucher->id,
                    'profesional_id' => $voucher->profesional_id ?? $user->profesional_id,
                    'veterinario_nombre' => $voucher->prestador_nombre ?? 'Profesional',
                    'sucursal' => 'Sucursal Centro',
                    'monto_cobrado' => $voucher->saldo_veterinario,
                    'estado' => 'pendiente_auditoria',
                    'cobrado_en' => now(),
                ]);
                VoucherAuditoria::create([
                    'voucher_id' => $voucher->id,
                    'accion' => 'voucher_enviado_cobro_automatico',
                    'usuario_tipo' => 'profesional',
                    'usuario_id' => $user->id,
                    'descripcion' => 'Bono enviado automáticamente según la programación de cobro del profesional.',
                    'ip' => '127.0.0.1',
                ]);
            }

            return $vouchers->count();
        });
    }
}
