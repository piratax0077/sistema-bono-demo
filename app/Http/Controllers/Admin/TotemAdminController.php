<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Totem;
use App\Models\TotemLog;
use App\Models\TotemSesion;
use App\Models\TotemVenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TotemAdminController extends Controller
{
    public function index()
    {
        $totems = Totem::withCount([
                'ventas',
                'logs',
                'sesiones as sesiones_abiertas_count' => function ($query) {
                    $query->whereNull('fin')
                        ->where('expira_at', '>', now());
                },
            ])
            ->orderByRaw("CASE WHEN estado_operacional = 'alerta' THEN 0 ELSE 1 END")
            ->orderBy('codigo')
            ->paginate(12);

        $resumen = [
            'total' => Totem::count(),
            'activos' => Totem::where('activo', true)->count(),
            'alertas' => Totem::where('estado_operacional', 'alerta')->count(),
            'ventas_hoy' => TotemVenta::whereDate('created_at', today())->count(),
            'monto_hoy' => TotemVenta::whereDate('created_at', today())->sum('total'),
        ];

        $ultimasVentas = TotemVenta::with('totem')
            ->latest()
            ->take(8)
            ->get();

        $ultimosLogs = TotemLog::with('totem')
            ->latest()
            ->take(12)
            ->get();

        return view('admin.totems.dashboard', compact(
            'totems',
            'resumen',
            'ultimasVentas',
            'ultimosLogs'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => 'required|string|max:100',
            'nombre' => 'required|string|max:150',
            'ubicacion' => 'nullable|string|max:255',
            'ip_autorizada' => 'nullable|ip',
            'serial' => 'nullable|string|max:150',
            'version' => 'nullable|string|max:50',
            'clave_instalacion' => 'nullable|string|min:12|max:200',
            'activo' => 'nullable|boolean',
        ]);

        $secret = $data['clave_instalacion'] ?? null;

        $existing = Totem::where('codigo', strtoupper(trim($data['codigo'])))->first();

        if (!$secret && (!$existing || !$existing->auth_secret_hash)) {
            $secret = $this->makeInstallationSecret();
        }

        $payload = [
            'nombre' => $data['nombre'],
            'ubicacion' => $data['ubicacion'] ?? null,
            'ip_autorizada' => $data['ip_autorizada'] ?? null,
            'serial' => $data['serial'] ?? null,
            'version' => $data['version'] ?? null,
            'activo' => (bool) ($data['activo'] ?? true),
        ];

        if ($secret) {
            $payload['auth_secret_hash'] = Hash::make($secret);
        }

        $totem = Totem::updateOrCreate(
            ['codigo' => strtoupper(trim($data['codigo']))],
            $payload
        );

        TotemLog::create([
            'totem_id' => $totem->id,
            'evento' => 'admin_provision',
            'detalle' => 'Tótem creado o actualizado por administración central.',
            'ip' => $request->ip(),
        ]);

        $message = 'Tótem guardado. Sólo administración puede activar o modificar esta credencial.';

        if ($secret) {
            return redirect()
                ->route('admin.totems.dashboard')
                ->with('ok', $message)
                ->with('clave_instalacion', [
                    'codigo' => $totem->codigo,
                    'clave' => $secret,
                ]);
        }

        return redirect()
            ->route('admin.totems.dashboard')
            ->with('ok', $message);
    }

    public function toggle(Request $request, Totem $totem)
    {
        $totem->update([
            'activo' => !$totem->activo,
            'token_hash' => null,
            'token_expira_at' => null,
        ]);

        TotemSesion::where('totem_id', $totem->id)
            ->whereNull('fin')
            ->update(['fin' => now()]);

        TotemLog::create([
            'totem_id' => $totem->id,
            'evento' => $totem->activo ? 'admin_activacion' : 'admin_bloqueo',
            'detalle' => $totem->activo
                ? 'Tótem activado desde administración central.'
                : 'Tótem bloqueado desde administración central; sesiones cerradas.',
            'ip' => $request->ip(),
        ]);

        return back()->with('ok', $totem->activo ? 'Tótem activado.' : 'Tótem bloqueado y desconectado.');
    }

    public function regenerateSecret(Request $request, Totem $totem)
    {
        $secret = $this->makeInstallationSecret();

        $totem->update([
            'auth_secret_hash' => Hash::make($secret),
            'token_hash' => null,
            'token_expira_at' => null,
        ]);

        TotemSesion::where('totem_id', $totem->id)
            ->whereNull('fin')
            ->update(['fin' => now()]);

        TotemLog::create([
            'totem_id' => $totem->id,
            'evento' => 'admin_rotacion_clave',
            'detalle' => 'Clave de instalación regenerada; sesiones anteriores cerradas.',
            'ip' => $request->ip(),
        ]);

        return back()
            ->with('ok', 'Clave regenerada. Copie ahora la nueva clave; no se volverá a mostrar.')
            ->with('clave_instalacion', [
                'codigo' => $totem->codigo,
                'clave' => $secret,
            ]);
    }

    public function clearAlert(Request $request, Totem $totem)
    {
        $totem->update([
            'estado_operacional' => 'ok',
            'ultima_alerta_at' => null,
            'ultima_alerta_mensaje' => null,
        ]);

        TotemLog::create([
            'totem_id' => $totem->id,
            'evento' => 'admin_alerta_resuelta',
            'detalle' => 'Administración marcó como resuelta la alerta operacional.',
            'ip' => $request->ip(),
        ]);

        return back()->with('ok', 'Alerta del tótem marcada como resuelta.');
    }

    private function makeInstallationSecret(): string
    {
        return 'SDI-' . strtoupper(Str::random(6)) . '-' . strtoupper(Str::random(6)) . '-' . random_int(1000, 9999);
    }
}
