<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\VoucherAgenda;
use App\Models\VoucherAuditoria;
use App\Models\VoucherBaseProfesional;
use App\Models\VoucherBaseRelacion;
use App\Models\VoucherBaseServicio;
use App\Models\VoucherBaseUsuario;
use App\Models\VoucherProfesional;
use App\Models\VoucherServicio;
use App\Services\ClienteAuthorizationGate;
use Illuminate\Http\Request;

class ClienteAgendaController extends Controller
{
    public function solicitar(Request $request, ClienteAuthorizationGate $authorizationGate)
    {
        $data = $request->validate([
            'voucher_id' => 'required|integer|exists:vouchers,id',
            'profesional_id' => 'required|integer|exists:voucher_profesionales,id',
            'fecha_hora_solicitada' => 'required|date|after:now',
            'observacion' => 'nullable|string|max:1000',
        ]);

        return $this->procesarSolicitud($request, $authorizationGate, $data);
    }

    public function confirmarAutorizacion(Request $request, ClienteAuthorizationGate $authorizationGate)
    {
        $validated = $request->validate([
            'cliente_authorization_token' => 'required|string|min:40',
        ]);

        $payload = $request->session()->get($this->sessionKey($validated['cliente_authorization_token']));

        if (! $payload) {
            return redirect()
                ->route('cliente.dashboard')
                ->with('error', 'No encontre una hora pendiente para esa autorizacion. Solicite la hora nuevamente.');
        }

        $request->merge([
            'cliente_authorization_token' => $validated['cliente_authorization_token'],
        ]);

        return $this->procesarSolicitud($request, $authorizationGate, $payload);
    }

    private function procesarSolicitud(Request $request, ClienteAuthorizationGate $authorizationGate, array $data)
    {
        $user = $request->user();
        $voucher = Voucher::with('servicio')->findOrFail($data['voucher_id']);

        if (! $this->voucherPerteneceAlUsuario($voucher, $user)) {
            return back()->with('error', 'El bono seleccionado no pertenece al beneficiario autenticado.');
        }

        if (! in_array($voucher->estado, ['activo', 'pagado', 'validado_atencion'], true) || $voucher->qr_usado) {
            return back()->with('error', 'El bono no esta disponible para solicitar hora.');
        }

        $profesional = VoucherProfesional::where('activo', true)->findOrFail($data['profesional_id']);

        if ($voucher->profesional_id && (int) $voucher->profesional_id !== (int) $profesional->id) {
            return back()->with('error', 'El bono esta asociado a otro profesional.');
        }

        $servicio = $voucher->servicio ?: VoucherServicio::find($voucher->servicio_id);
        if (! $servicio) {
            return back()->with('error', 'El bono no tiene servicio asociado para validar la agenda.');
        }

        $validacion = $this->validarBaseExterna($user, $profesional, $servicio);
        if (! $validacion['ok']) {
            VoucherAuditoria::create([
                'voucher_id' => $voucher->id,
                'accion' => 'agenda_web_rechazada_base_externa',
                'usuario_tipo' => 'cliente',
                'usuario_id' => $user->id,
                'descripcion' => $validacion['motivo'],
                'ip' => $request->ip(),
            ]);

            return back()->with('error', $validacion['motivo']);
        }

        $clienteAutorizacionId = $authorizationGate->clienteIdForUser($user);
        if (! $clienteAutorizacionId) {
            return back()->with('error', 'No existe ficha de beneficiario para autorizar esta hora en la app.');
        }

        $authorization = $authorizationGate->ensureApprovedOrRequest(
            $request,
            $clienteAutorizacionId,
            'solicitud_hora_web',
            'voucher_agenda',
            $voucher->id,
            [
                'canal' => 'agenda_web',
                'voucher_id' => $voucher->id,
                'voucher_codigo' => $voucher->codigo,
                'rut' => $user->rut,
                'beneficiario' => $user->name,
                'servicio_id' => $servicio->id,
                'servicio_nombre' => $servicio->nombre,
                'profesional_id' => $profesional->id,
                'profesional_nombre' => $profesional->nombre,
                'fecha_hora_solicitada' => $data['fecha_hora_solicitada'],
            ]
        );

        if (! $authorization['ok']) {
            $autorizacion = $authorization['autorizacion'] ?? null;

            if ($autorizacion && $authorization['estado'] === 'pendiente') {
                $request->session()->put($this->sessionKey($autorizacion->token), $data);

                return redirect()
                    ->route('cliente.dashboard')
                    ->with('agenda_authorization_pending_token', $autorizacion->token)
                    ->with('agenda_authorization_pending_expires', optional($autorizacion->expira_at)->format('d-m-Y H:i:s'))
                    ->with('ok', 'Solicitud de hora enviada a la app autorizadora. Apruebe y luego confirme la hora.');
            }

            return redirect()
                ->route('cliente.dashboard')
                ->with('error', $authorization['mensaje']);
        }

        $agenda = VoucherAgenda::updateOrCreate(
            ['voucher_id' => $voucher->id],
            [
                'cliente_id' => $voucher->cliente_id,
                'mascota_id' => $voucher->mascota_id,
                'profesional_id' => $profesional->id,
                'centro_atencion_id' => null,
                'fecha_hora_solicitada' => $data['fecha_hora_solicitada'],
                'estado' => 'hora_solicitada',
                'observacion' => $data['observacion'] ?? null,
            ]
        );

        $voucher->update([
            'agenda_id' => $agenda->id,
        ]);

        if (($authorization['autorizacion'] ?? null)) {
            $authorization['autorizacion']->update([
                'referencia_tipo' => 'voucher_agenda',
                'referencia_id' => $agenda->id,
            ]);

            $request->session()->forget($this->sessionKey($authorization['autorizacion']->token));
        }

        VoucherAuditoria::create([
            'voucher_id' => $voucher->id,
            'accion' => 'agenda_web_hora_solicitada',
            'usuario_tipo' => 'cliente',
            'usuario_id' => $user->id,
            'descripcion' => 'Hora solicitada por agenda web con autorizacion app '.(($authorization['autorizacion']->id ?? 'n/a')).'.',
            'ip' => $request->ip(),
        ]);

        return redirect()
            ->route('cliente.dashboard')
            ->with('ok', 'Hora solicitada correctamente. Queda pendiente de confirmacion del centro/profesional.');
    }

    private function voucherPerteneceAlUsuario(Voucher $voucher, $user): bool
    {
        if ((int) $voucher->cliente_id === (int) $user->id) {
            return true;
        }

        return $this->normalizarRut($voucher->cliente_rut) !== ''
            && hash_equals($this->normalizarRut($voucher->cliente_rut), $this->normalizarRut($user->rut));
    }

    private function validarBaseExterna($user, VoucherProfesional $profesional, VoucherServicio $servicio): array
    {
        $usuario = $this->vigente(VoucherBaseUsuario::where('rut_hash', $this->rutHmac($user->rut))
            ->where('rut_sha256', $this->rutSha256($user->rut)))
            ->first();

        if (! $usuario) {
            return ['ok' => false, 'motivo' => 'La base externa no confirma al beneficiario vigente.'];
        }

        $baseProfesional = $this->vigente(VoucherBaseProfesional::where('rut_hash', $this->rutHmac($profesional->rut))
            ->where('rut_sha256', $this->rutSha256($profesional->rut)))
            ->first();

        if (! $baseProfesional) {
            return ['ok' => false, 'motivo' => 'La base externa no confirma al profesional vigente.'];
        }

        $baseServicio = $this->vigente(VoucherBaseServicio::where('otros->voucher_servicio_id', $servicio->id))
            ->first();

        if (! $baseServicio) {
            return ['ok' => false, 'motivo' => 'La base externa no confirma el servicio vigente.'];
        }

        $relacion = $this->vigente(VoucherBaseRelacion::where('usuario_id', $usuario->id)
            ->where('profesional_id', $baseProfesional->id)
            ->where('servicio_id', $baseServicio->id)
            ->where('estado', 'vigente'))
            ->first();

        if (! $relacion) {
            return ['ok' => false, 'motivo' => 'No existe relacion vigente beneficiario-profesional-servicio para agendar.'];
        }

        if ($relacion->requiere_auditoria) {
            return ['ok' => false, 'motivo' => $relacion->motivo_auditoria ?: 'La relacion requiere auditoria antes de agendar.'];
        }

        return ['ok' => true, 'motivo' => 'Relacion vigente validada.'];
    }

    private function vigente($query)
    {
        return $query
            ->whereIn('estado', ['activo', 'vigente'])
            ->where(function ($builder) {
                $builder->whereNull('vigente_desde')
                    ->orWhere('vigente_desde', '<=', now()->toDateString());
            })
            ->where(function ($builder) {
                $builder->whereNull('vigente_hasta')
                    ->orWhere('vigente_hasta', '>=', now()->toDateString());
            });
    }

    private function rutHmac($rut): string
    {
        return hash_hmac('sha256', $this->normalizarRut($rut), (string) config('app.key'));
    }

    private function rutSha256($rut): string
    {
        return hash('sha256', $this->normalizarRut($rut));
    }

    private function normalizarRut($rut): string
    {
        return strtoupper(preg_replace('/[^0-9K]/i', '', (string) $rut));
    }

    private function sessionKey(string $token): string
    {
        return 'cliente_agenda_pending_'.hash('sha256', $token);
    }
}
