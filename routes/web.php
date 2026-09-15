<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\VoucherWebController;
use App\Http\Controllers\RendicionController;
use App\Http\Controllers\ClienteBonoController;
use App\Http\Controllers\ClienteAgendaController;
use App\Http\Controllers\PersonaRapidaController;
use App\Http\Controllers\Admin\TotemAdminController;
use App\Models\ClienteSaldo;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherCobro;
use App\Models\VoucherAuditoria;
use App\Models\VoucherLiquidacion;
use App\Models\VoucherProfesional;
use App\Models\VoucherRendicion;
use App\Models\VoucherVendedor;
use App\Models\VoucherServicio;
use App\Models\VoucherMascota;
use App\Models\VoucherBaseUsuario;
use App\Models\VoucherBaseDependiente;
use App\Models\VoucherDeliveryRequest;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\DemoFlujoBonoController;
use App\Http\Controllers\AsistenteRecepcionController;
use App\Http\Controllers\AsistenteVentaBonoController;
use App\Http\Controllers\DemoPortalController;
use App\Http\Controllers\ClienteAgendaOnlineController;
use App\Http\Controllers\ClienteAgendaExternaController;
use App\Http\Controllers\TotemLocalController;
use App\Http\Controllers\RoleHomeController;
use App\Services\MedsdiAgendaApiService;
use App\Models\AuditorNotificacion;
use App\Models\LoginAuditoria;
use App\Models\IpAutorizada;
use App\Models\ClienteAutorizacion;

Route::get('/demo/flujo-bono', [DemoFlujoBonoController::class, 'index'])
    ->middleware(['auth', 'rol:admin,auditor,profesional,asistente,cliente'])
    ->name('demo.flujo-bono');
Route::get('/demo/circuito-cobranza', [DemoFlujoBonoController::class, 'circuitoCobranza'])
    ->middleware(['auth', 'rol:admin,auditor,profesional,asistente,cliente'])
    ->name('demo.circuito-cobranza');

if (! function_exists('sdi_normalizar_rut')) {
    function sdi_normalizar_rut($rut): string
    {
        return strtoupper(preg_replace('/[^0-9K]/i', '', (string) $rut));
    }
}

if (! function_exists('sdi_formatear_rut')) {
    function sdi_formatear_rut($rut): string
    {
        $normalizado = sdi_normalizar_rut($rut);
        if (strlen($normalizado) < 2) {
            return (string) $rut;
        }

        $cuerpo = substr($normalizado, 0, -1);
        $dv = substr($normalizado, -1);
        $cuerpoFormateado = strrev(implode('.', str_split(strrev($cuerpo), 3)));

        return $cuerpoFormateado.'-'.$dv;
    }
}

if (! function_exists('sdi_rut_hmac')) {
    function sdi_rut_hmac($rut): string
    {
        return hash_hmac('sha256', sdi_normalizar_rut($rut), (string) config('app.key'));
    }
}

if (! function_exists('sdi_rut_sha256')) {
    function sdi_rut_sha256($rut): string
    {
        return hash('sha256', sdi_normalizar_rut($rut));
    }
}

if (! function_exists('sdi_encrypt_optional')) {
    function sdi_encrypt_optional($value): ?string
    {
        return filled($value) ? Crypt::encryptString((string) $value) : null;
    }
}
/* PÚBLICAS */

Route::get('/', function () {
    return config('demo.enabled') ? redirect()->route('demo.portal') : redirect('/login');
});

Route::get('/demo', [DemoPortalController::class, 'index'])->name('demo.portal');
Route::post('/demo/cambiar-usuario/{perfil}', [DemoPortalController::class, 'switchUser'])
    ->middleware('throttle:30,1')->name('demo.switch-user');
Route::post('/demo/ingresar-bono', [DemoPortalController::class, 'loginBono'])
    ->middleware('throttle:30,1')->name('demo.login-bono');

Route::get('/totem-local', [TotemLocalController::class, 'index'])
    ->middleware('throttle:60,1')->name('totem.local');
Route::get('/paciente/totem', [TotemLocalController::class, 'index'])
    ->middleware('throttle:60,1')->name('paciente.totem');
Route::post('/totem-local/buscar-hora', [TotemLocalController::class, 'buscarHora'])
    ->middleware('throttle:15,1')->name('totem.local.buscar-hora');
Route::post('/totem-local/anexar-qr', [TotemLocalController::class, 'anexarQr'])
    ->middleware('throttle:15,1')->name('totem.local.anexar-qr');
Route::post('/totem-local/bonos/{voucher}/confirmar-llegada', [TotemLocalController::class, 'confirmarLlegada'])
    ->middleware('throttle:10,1')->name('totem.local.confirmar-llegada');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Route::get('/voucher/{token}/usar', [VoucherWebController::class, 'usar'])->name('vouchers.usar');

// Route::post('/voucher/{token}/asignar-profesional', [VoucherWebController::class, 'asignarProfesional'])->name('vouchers.asignarProfesional');

/* REDIRECCIÓN POR ROL */

Route::get('/redirigir-rol', function () {
    $user = auth()->user();

    if ($user->rol == 'admin') return redirect()->route('admin.home');
    if ($user->rol == 'vendedor') return redirect()->route('vendedor.home');
    if ($user->rol == 'asistente') return redirect()->route('asistente.home');
    if ($user->rol == 'profesional') return redirect()->route('profesional.home');
    if ($user->rol == 'auditor') return redirect()->route('contraloria.home');
    if ($user->rol == 'cliente') return redirect()->route('paciente.home');
    abort(403);
})->middleware('auth');

/* ADMIN */

Route::middleware(['auth', 'rol:admin', '2fa'])->group(function () {
    Route::get('/admin/totems/dashboard', [TotemAdminController::class, 'index'])
        ->name('admin.totems.dashboard');
    Route::get('/admin/totems', [TotemAdminController::class, 'index'])
        ->name('admin.totems.index');
    Route::post('/admin/totems', [TotemAdminController::class, 'store'])
        ->name('admin.totems.store');
    Route::post('/admin/totems/{totem}/toggle', [TotemAdminController::class, 'toggle'])
        ->name('admin.totems.toggle');
    Route::post('/admin/totems/{totem}/regenerar-clave', [TotemAdminController::class, 'regenerateSecret'])
        ->name('admin.totems.regenerateSecret');
    Route::post('/admin/totems/{totem}/resolver-alerta', [TotemAdminController::class, 'clearAlert'])
        ->name('admin.totems.clearAlert');

    Route::get('/escritorio-admin', function () {
        return view('escritorios.admin');
    });
    Route::get('/admin/bonos', function (Request $request) {
        $estado = trim((string) $request->query('estado', ''));
        $buscar = trim((string) $request->query('buscar', ''));

        $vouchers = Voucher::query()
            ->with(['profesional', 'agenda', 'atencion', 'cobros'])
            ->when($estado !== '', fn ($query) => $query->where('estado', $estado))
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($subquery) use ($buscar) {
                    $subquery->where('codigo', 'like', '%'.$buscar.'%')
                        ->orWhere('cliente_nombre', 'like', '%'.$buscar.'%')
                        ->orWhere('beneficiario_nombre', 'like', '%'.$buscar.'%')
                        ->orWhere('prestador_nombre', 'like', '%'.$buscar.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $estados = Voucher::query()->select('estado')->distinct()->orderBy('estado')->pluck('estado');

        return view('admin.bonos', compact('vouchers', 'estados', 'estado', 'buscar'));
    })->name('admin.bonos.gestion');
    Route::get('/admin/servicios', function () {

        $servicios = VoucherServicio::orderBy('id', 'desc')->get();

        return view('admin.servicios', compact('servicios'));

    })->name('admin.servicios');

    Route::get('/admin/servicios/crear', function () {

        return view('admin.servicios_crear');

    })->name('admin.servicios.crear');

    Route::post('/admin/servicios', function (Request $request) {

        VoucherServicio::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'valor_base' => $request->valor_base ?? 0,
            'copago_base' => $request->copago_base ?? 0,
            'comision_veterchile' => $request->comision_veterchile ?? 0,
            'activo' => $request->activo ?? 1,
        ]);

        return redirect('/admin/servicios');

    })->name('admin.servicios.store');

    Route::get('/dashboard-financiero', function () {
        $totalEmitido = Voucher::sum('valor');
        $totalSaldosDisponibles = ClienteSaldo::where('estado', 'disponible')->sum('monto');
        $totalSaldosConsumidos = ClienteSaldo::where('estado', 'consumido')->sum('monto');
        $totalCopagos = Voucher::sum('copago_usuario');
        $totalComision = Voucher::sum('comision_veterchile');

        $pendienteRendicion = VoucherCobro::where('estado', 'pendiente_rendicion')->sum('monto_cobrado');
        $totalRendido = VoucherRendicion::sum('total_cobrado');

        $vouchers = Voucher::orderBy('id', 'desc')->take(10)->get();

        return view('admin.dashboard_financiero', compact(
            'totalEmitido',
            'totalCopagos',
            'totalComision',
            'pendienteRendicion',
            'totalRendido',
            'vouchers',
            'totalSaldosDisponibles',
            'totalSaldosConsumidos',
        ));
    });

    Route::get('/admin/ips-autorizadas', function () {
        $ips = IpAutorizada::orderByDesc('id')->get();
        $usuarios = User::whereIn('rol', ['admin', 'auditor'])->orderBy('name')->get();

        return view('admin.ips_autorizadas', compact('ips', 'usuarios'));
    })->name('admin.ips.autorizadas');

    Route::post('/admin/ips-autorizadas', function (Request $request) {
        $data = $request->validate([
            'rol' => 'nullable|in:admin,auditor',
            'user_id' => 'nullable|integer|exists:users,id',
            'ip' => 'required|ip',
            'descripcion' => 'nullable|string|max:255',
        ]);
        $data['activo'] = true;
        IpAutorizada::create($data);

        return back()->with('ok', 'IP autorizada registrada.');
    })->name('admin.ips.autorizadas.store');

    Route::post('/admin/ips-autorizadas/{ip}/toggle', function (IpAutorizada $ip) {
        $ip->update(['activo' => ! $ip->activo]);

        return back()->with('ok', 'Estado de la IP actualizado.');
    })->name('admin.ips.autorizadas.toggle');

    Route::get('/admin/usuarios', function () {
        $usuarios = User::orderBy('id', 'desc')->get();
        return view('admin.usuarios', compact('usuarios'));
    });

    Route::get('/admin/usuarios/crear', function () {
        $vendedores = VoucherVendedor::orderBy('nombre')->get();
        $profesionales = VoucherProfesional::orderBy('nombre')->get();

        return view('admin.usuarios_crear', compact('vendedores', 'profesionales'));
    });
    Route::get('/admin/saldos-clientes', function () {

        $saldos = ClienteSaldo::with(['voucher', 'voucherConsumido'])
        ->orderBy('id', 'desc')
        ->get();

        return view('admin.saldos_clientes', compact('saldos'));

        })->name('admin.saldos.clientes');
    Route::get('/dashboard', function () {
        return redirect('/redirigir-rol');
        })->name('dashboard');

    Route::get('/admin/mascotas', function () {

        $mascotas = VoucherMascota::orderBy('id', 'desc')->get();

        return view('admin.mascotas', compact('mascotas'));

        })->name('admin.mascotas');

    Route::get('/admin/mascotas/crear', function () {
        $clientes = Cliente::orderBy('nombre')->get();

        return view('admin.mascotas_crear', compact('clientes'));
    })->name('admin.mascotas.crear');

    Route::post('/admin/mascotas', function (Request $request) {
        $data = $request->validate([
            'cliente_id' => 'nullable|integer',
            'nombre' => 'required|string|max:100',
            'especie' => 'nullable|string|max:50',
            'raza' => 'nullable|string|max:100',
            'sexo' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'edad' => 'nullable|integer|min:0|max:120',
            'color' => 'nullable|string|max:80',
            'microchip' => 'nullable|string|max:100',
            'dueno_rut' => 'required|string|max:30',
            'dueno_nombre' => 'nullable|string|max:150',
            'dueno_telefono' => 'nullable|string|max:50',
            'dueno_email' => 'nullable|email|max:150',
            'activo' => 'nullable|boolean',
        ]);

        VoucherMascota::create([
            'cliente_id' => $data['cliente_id'] ?? null,
            'nombre' => $data['nombre'],
            'especie' => $data['especie'] ?? 'Canino',
            'raza' => $data['raza'] ?? null,
            'sexo' => $data['sexo'] ?? null,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'edad' => $data['edad'] ?? null,
            'color' => $data['color'] ?? null,
            'microchip' => $data['microchip'] ?? null,
            'dueno_rut' => $data['dueno_rut'],
            'dueno_nombre' => $data['dueno_nombre'] ?? null,
            'dueno_telefono' => $data['dueno_telefono'] ?? null,
            'dueno_email' => $data['dueno_email'] ?? null,
            'activo' => $request->boolean('activo', true),
        ]);

        return redirect()
            ->route('admin.mascotas')
            ->with('ok', 'Mascota creada y vinculada correctamente.');
    })->name('admin.mascotas.store');








        Route::post('/admin/usuarios', function (Request $request) {
            $data = $request->validate([
                'name' => 'required|string|max:150',
                'email' => 'required|email|max:150|unique:users,email',
                'rut' => 'required|string|max:30',
                'telefono' => 'nullable|string|max:50',
                'password' => 'required|string|min:6',
                'rol' => 'required|string|in:admin,auditor,vendedor,profesional,asistente,cliente',
                'activo' => 'nullable|boolean',
                'vendedor_id' => 'nullable|integer',
                'profesional_id' => 'nullable|integer',
                'direccion' => 'nullable|string|max:255',
                'fecha_nacimiento' => 'nullable|date',
                'dependientes' => 'nullable|array',
                'dependientes.*.nombre' => 'nullable|string|max:150',
                'dependientes.*.rut' => 'nullable|string|max:30',
                'dependientes.*.parentesco' => 'nullable|string|max:80',
                'dependientes.*.fecha_nacimiento' => 'nullable|date',
                'dependientes.*.direccion' => 'nullable|string|max:255',
            ]);

            DB::transaction(function () use ($request, $data) {
                $usuario = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'rut' => $data['rut'],
                    'telefono' => $data['telefono'] ?? null,
                    'password' => Hash::make($data['password']),
                    'rol' => $data['rol'],
                    'activo' => $request->boolean('activo', true),
                    'vendedor_id' => $data['vendedor_id'] ?? null,
                    'profesional_id' => $data['profesional_id'] ?? null,
                ]);

                if ($data['rol'] !== 'cliente') {
                    return;
                }

                $rutNormalizado = sdi_normalizar_rut($data['rut']);

                $cliente = Cliente::updateOrCreate(
                    ['rut_hash' => hash('sha256', $rutNormalizado)],
                    [
                        'nombre' => $data['name'],
                        'rut' => Crypt::encryptString($rutNormalizado),
                        'telefono' => $data['telefono'] ?? null,
                        'email' => $data['email'],
                        'tipo' => 'dueno_mascota',
                        'estado' => 'activo',
                        'fecha_inscripcion' => now(),
                    ]
                );

                $baseUsuario = VoucherBaseUsuario::updateOrCreate(
                    ['rut_hash' => sdi_rut_hmac($data['rut'])],
                    [
                        'external_id' => 'USR-'.substr(sdi_rut_sha256($data['rut']), 0, 24),
                        'nombre' => $data['name'],
                        'rut_sha256' => sdi_rut_sha256($data['rut']),
                        'rut_encrypted' => Crypt::encryptString($rutNormalizado),
                        'direccion_encrypted' => sdi_encrypt_optional($data['direccion'] ?? null),
                        'fecha_nacimiento_encrypted' => sdi_encrypt_optional($data['fecha_nacimiento'] ?? null),
                        'otros' => [
                            'origen' => 'admin_crear_usuario',
                            'user_id' => $usuario->id,
                            'telefono' => $data['telefono'] ?? null,
                            'email' => $data['email'],
                        ],
                        'estado' => 'activo',
                        'vigente_desde' => now()->toDateString(),
                        'vigente_hasta' => null,
                    ]
                );

                foreach ((array) $request->input('dependientes', []) as $dependiente) {
                    $nombre = trim((string) ($dependiente['nombre'] ?? ''));
                    $rut = trim((string) ($dependiente['rut'] ?? ''));

                    if ($nombre === '' || $rut === '') {
                        continue;
                    }

                    $rutDependienteNormalizado = sdi_normalizar_rut($rut);

                    VoucherBaseDependiente::updateOrCreate(
                        ['rut_hash' => sdi_rut_hmac($rut)],
                        [
                            'usuario_id' => $baseUsuario->id,
                            'external_id' => 'DEP-'.substr(sdi_rut_sha256($rut), 0, 24),
                            'nombre' => $nombre,
                            'rut_sha256' => sdi_rut_sha256($rut),
                            'rut_encrypted' => Crypt::encryptString($rutDependienteNormalizado),
                            'direccion_encrypted' => sdi_encrypt_optional($dependiente['direccion'] ?? null),
                            'fecha_nacimiento_encrypted' => sdi_encrypt_optional($dependiente['fecha_nacimiento'] ?? null),
                            'parentesco' => $dependiente['parentesco'] ?? 'Carga',
                            'otros' => [
                                'origen' => 'admin_crear_usuario',
                                'titular_user_id' => $usuario->id,
                            ],
                            'estado' => 'activo',
                            'vigente_desde' => now()->toDateString(),
                            'vigente_hasta' => null,
                        ]
                    );
                }
            });

            return redirect('/admin/usuarios')
                ->with('ok', 'Usuario creado con beneficiarios/cargas asociadas.');
        })->name('admin.usuarios.store');
        Route::get('/admin/usuarios/{id}/editar', function ($id) {
            $usuario = User::findOrFail($id);
            $vendedores = VoucherVendedor::orderBy('nombre')->get();
            $profesionales = VoucherProfesional::orderBy('nombre')->get();

            return view('admin.usuarios_editar', compact(
                'usuario',
                'vendedores',
                'profesionales'
            ));
            })->name('admin.usuarios.editar');


        Route::post('/admin/usuarios/{id}/actualizar', function (Request $request, $id) {

            // dd($id, $request->all());

            $usuario = User::findOrFail($id);

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'rut' => $request->rut,
                'telefono' => $request->telefono,
                'rol' => $request->rol,
                'activo' => $request->activo ?? 0,
                'vendedor_id' => $request->vendedor_id,
                'profesional_id' => $request->profesional_id,
            ];

            if ($request->password) {
                $data['password'] = Hash::make($request->password);
            }

            $usuario->update($data);

            return redirect('/admin/usuarios');

        })->name('admin.usuarios.actualizar');









        Route::get('/admin/profesionales', function () {
            $profesionales = VoucherProfesional::orderBy('id', 'desc')->get();
            return view('admin.profesionales', compact('profesionales'));
        });

        Route::get('/admin/vendedores', function () {
            $vendedores = VoucherVendedor::orderBy('id', 'desc')->get();
            return view('admin.vendedores', compact('vendedores'));
        });

        Route::get('/rendiciones', [RendicionController::class, 'index'])->name('rendiciones.index');
        Route::post('/rendiciones/generar', [RendicionController::class, 'generar'])->name('rendiciones.generar');
        Route::post('/rendiciones/{id}/generar-liquidacion', [RendicionController::class, 'generarLiquidacion'])->name('rendiciones.generarLiquidacion');

        Route::get('/liquidaciones', function () {
            $liquidaciones = VoucherLiquidacion::orderBy('id', 'desc')->get();
            return view('liquidaciones.index', compact('liquidaciones'));
        });

        Route::post('/liquidaciones/{id}/pagar', [RendicionController::class, 'pagarLiquidacion'])->name('liquidaciones.pagar');
    });
Route::middleware(['auth', 'rol:admin', '2fa'])->group(function () {
    Route::get('/admin/vendedores/crear', function () {
        return view('admin.vendedores_crear');
        })->name('admin.vendedores.crear');

    Route::post('/admin/vendedores', function (Request $request) {

        VoucherVendedor::create([
            'nombre' => $request->nombre,
            'rut' => $request->rut,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'activo' => $request->activo ?? 1,
        ]);

        return redirect('/admin/vendedores');

     })->name('admin.vendedores.store');
    Route::get('/admin/profesionales/crear', function () {
        return view('admin.profesionales_crear');
         })->name('admin.profesionales.crear');

    Route::post('/admin/profesionales', function (Request $request) {
        $profesional = VoucherProfesional::create([
            'nombre' => $request->nombre,
            'rut' => $request->rut,
            'especialidad' => $request->especialidad,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'activo' => $request->activo ?? 1,
            'banco' => $request->banco,
            'tipo_cuenta' => $request->tipo_cuenta,
            'numero_cuenta' => $request->numero_cuenta,
            'titular_cuenta' => $request->titular_cuenta,
            'rut_cuenta' => $request->rut_cuenta,
        ]);
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'rut' => $request->rut,
            'telefono' => $request->telefono,
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
            'activo' => $request->activo ?? 1,
            'vendedor_id' => $request->vendedor_id,
            'profesional_id' => $request->profesional_id,
        ]);
            return redirect('/admin/profesionales');

        })->name('admin.profesionales.store');
});

/* VENDEDOR */

Route::middleware(['auth', 'rol:vendedor'])->group(function () {
    Route::get('/escritorio-vendedor', function () {
        return view('escritorios.vendedor');
    });

    Route::get('/vendedores/caja', function () {
        return view('vendedores.caja');
    });

    Route::get('/vouchers', [VoucherWebController::class, 'index'])->name('vouchers.index');
    Route::get('/vouchers/crear', [VoucherWebController::class, 'create'])->name('vouchers.create');
    Route::post('/vouchers', [VoucherWebController::class, 'store'])->name('vouchers.store');

    Route::get('/vouchers/{id}/pagar', [VoucherWebController::class, 'pagar'])->name('vouchers.pagar');
    Route::post('/vouchers/{id}/procesar-pago', [VoucherWebController::class, 'procesarPago'])->name('vouchers.procesarPago');
    Route::post('/vouchers/{id}/pagado', [VoucherWebController::class, 'marcarPagado'])->name('vouchers.pagado');
});
/* PROFESIONAL */

Route::middleware(['auth', 'rol:profesional'])->group(function () {

    Route::get('/escritorio-profesional', function (\App\Http\Controllers\ProfesionalAgendaExternaController $medsdiProfesional, \App\Services\MedsdiAgendaApiService $api) {

        $medsdiProfesional->sincronizarAgenda($api);

        $profesionalId = Auth::user()->profesional_id;

        $vouchers = Voucher::with('agenda')
            ->where('profesional_id', $profesionalId)
            ->orderBy('id', 'desc')
            ->get();

        $pacientesEnEspera = $vouchers->filter(function ($voucher) {
            return optional($voucher->agenda)->estado === 'paciente_en_espera'
                && (int) optional($voucher->agenda)->medichile_estado_id === 4
                && ! in_array($voucher->estado, ['cobrado', 'usado'], true);
        })->values();

        $pacientesEnEspera = $pacientesEnEspera
            ->concat($medsdiProfesional->pacientesEnEsperaExternos())
            ->values();

        $bonosMedsdi = $medsdiProfesional->misBonos();

        return view('escritorios.profesional', compact('vouchers', 'pacientesEnEspera', 'bonosMedsdi'));

    })->name('profesional.escritorio');

    Route::post('/profesional/medsdi/vouchers/{voucher}/iniciar-atencion',
        [\App\Http\Controllers\ProfesionalAgendaExternaController::class, 'iniciarAtencion'])
        ->name('profesional.medsdi.iniciar_atencion');

    Route::post('/profesional/medsdi/vouchers/{voucher}/finalizar-hora',
        [\App\Http\Controllers\ProfesionalAgendaExternaController::class, 'finalizarHora'])
        ->name('profesional.medsdi.finalizar_hora');

});
/* PROFESIONAL */

Route::middleware(['auth', 'rol:profesional'])->group(function () {

        Route::post('/profesional/cobros/generar-rendicion', function () {

        $profesionalId = auth()->user()->profesional_id;

        $cobros = \App\Models\VoucherCobro::where('profesional_id', $profesionalId)
            ->where('estado', 'pendiente_rendicion')
            ->whereNull('voucher_rendicion_id')
            ->get();

        if ($cobros->count() == 0) {
            return back()->with('error', 'No hay cobros pendientes.');
        }

        $rendicion = \App\Models\VoucherRendicion::create([
            'veterinario_nombre' => optional(auth()->user())->name,
            'sucursal' => 'Sucursal principal',
            'total_cobrado' => $cobros->sum('monto_cobrado'),
            'cantidad_vouchers' => $cobros->count(),
            'estado' => 'pendiente',
            'rendida_en' => now(),
        ]);

        foreach ($cobros as $cobro) {
            $cobro->update([
                'voucher_rendicion_id' => $rendicion->id,
                'estado' => 'rendido',
            ]);
        }

        return back()->with('ok', 'Rendición enviada a administración.');

    })->name('profesional.cobros.generarRendicion');

    Route::get('/profesional/prueba-atencion', function () {

    $vouchers = \App\Models\Voucher::where('estado', 'activo')
        ->orderBy('id', 'desc')
        ->get();

    return view('profesional.prueba_atencion', compact('vouchers'));

    });

        /* PROFESIONAL PRUEBA ATENCION*/

   Route::get('/profesional/prueba-atencion', function () {

    $profesionalId = auth()->user()->profesional_id;

    $vouchers = \App\Models\Voucher::where('profesional_id', $profesionalId)
        ->whereIn('estado', ['activo', 'asignado', 'en_atencion'])
        ->orderBy('id', 'desc')
        ->get();

    return view('profesional.prueba_atencion', compact('vouchers'));

    });

    Route::post('/profesional/vouchers/{id}/aceptar',
        [VoucherWebController::class, 'aceptarAtencion'])
        ->name('profesional.vouchers.aceptar');

    Route::get('/profesional/vouchers/{id}/atencion',
        [VoucherWebController::class, 'atencionProfesional'])
        ->name('profesional.vouchers.atencion');

    Route::post('/profesional/vouchers/{id}/finalizar',
        [VoucherWebController::class, 'finalizarAtencion'])
        ->name('profesional.vouchers.finalizar');

    Route::post('/vouchers/{id}/cobrar',
        [VoucherWebController::class, 'cobrar'])
        ->name('vouchers.cobrar');
    Route::post('/profesional/cobros/enviar-seleccionados', [VoucherWebController::class, 'cobrarSeleccionados'])
        ->middleware('throttle:10,1')
        ->name('profesional.cobros.seleccionados');
    Route::put('/profesional/cobros/programacion', [VoucherWebController::class, 'guardarProgramacionCobro'])
        ->middleware('throttle:10,1')
        ->name('profesional.cobros.programacion');
    Route::put('/profesional/cuenta-bancaria', [\App\Http\Controllers\ProfesionalCuentaBancariaController::class, 'update'])
        ->middleware('throttle:10,1')
        ->name('profesional.cuenta_bancaria.actualizar');
    Route::post('/profesional/cuenta-bancaria/notificar', [\App\Http\Controllers\ProfesionalCuentaBancariaController::class, 'notify'])
        ->middleware('throttle:10,1')->name('profesional.cuenta_bancaria.notificar');

    Route::get('/profesional/cobros/{id}/qr/generar',
        [VoucherWebController::class, 'generarQrCobro'])
        ->name('profesional.cobros.qr.generar');

    Route::get('/profesional/cobros/{id}/qr-datos',
        [VoucherWebController::class, 'cobroQrDatos'])
        ->name('profesional.cobros.qr.datos');

    Route::get('/profesional/cobros/{id}/qr',
        [VoucherWebController::class, 'qrCobro'])
        ->middleware('signed')
        ->name('profesional.cobros.qr');

    Route::get('/profesional/cobros', function (\App\Services\MedsdiAgendaApiService $medsdiApi) {
        $profesionalId = auth()->user()->profesional_id;

        $bonosClinicos = \App\Models\Voucher::with(['atencion', 'agenda'])
            ->where(function ($q) use ($profesionalId) {
                $q->where('profesional_id', $profesionalId)
                    ->orWhereNotNull('prestador_nombre');
            })
            ->whereIn('estado', ['atencion_cerrada', 'validado_atencion'])
            ->whereDoesntHave('cobros')
            ->orderBy('id', 'desc')
            ->get();

        $cobros = \App\Models\VoucherCobro::with(['voucher', 'rendicion.liquidaciones'])
            ->where('profesional_id', $profesionalId)
            ->orderBy('id', 'desc')
            ->get();

        $pendientesRendicion = $cobros->where('estado', 'pendiente_rendicion')->values();
        $pendientesAuditoria = $cobros->where('estado', 'pendiente_auditoria')->values();
        $programacionCobro = \App\Models\VoucherCobroProgramacion::where('user_id', auth()->id())->first();
        $cuentaBancariaMedsdi = $medsdiApi->cuentaBancariaProfesional();

        return view('profesional.cobros', compact('bonosClinicos', 'cobros', 'pendientesRendicion', 'pendientesAuditoria', 'programacionCobro', 'cuentaBancariaMedsdi'));
    })->name('profesional.cobros');

});

/* CLIENTE / BENEFICIARIO */
Route::middleware(['auth', 'rol:cliente', 'phone.otp'])->group(function () {
    Route::get('/paciente/inicio', [ClienteBonoController::class, 'home'])
        ->name('paciente.home');
    Route::get('/cliente', [ClienteBonoController::class, 'dashboard'])
        ->name('cliente.dashboard');
    Route::get('/paciente/escritorio', [ClienteBonoController::class, 'dashboard'])
        ->name('paciente.escritorio');
    Route::put('/paciente/cuenta-bancaria', [ClienteBonoController::class, 'actualizarCuentaBancaria'])
        ->middleware('throttle:10,1')->name('paciente.cuenta_bancaria.actualizar');
    Route::post('/paciente/cuenta-bancaria/notificar', [ClienteBonoController::class, 'notificarCuentaBancaria'])
        ->middleware('throttle:10,1')->name('paciente.cuenta_bancaria.notificar');
    Route::get('/paciente/agenda', [ClienteBonoController::class, 'agenda'])
        ->name('paciente.agenda');

    Route::post('/cliente/comprar-bono', [ClienteBonoController::class, 'comprar'])
        ->middleware('throttle:10,1')
        ->name('cliente.bonos.comprar');

    Route::post('/cliente/comprar-bono/confirmar-autorizacion', [ClienteBonoController::class, 'confirmarCompraAutorizada'])
        ->middleware('throttle:10,1')
        ->name('cliente.bonos.comprar.confirmar');

    Route::post('/cliente/bonos/notificar-android', [ClienteBonoController::class, 'notificarBonoAndroid'])
        ->middleware('throttle:10,1')
        ->name('cliente.bonos.notificar_android');

    Route::post('/cliente/agenda/solicitar', [ClienteAgendaController::class, 'solicitar'])
        ->middleware('throttle:10,1')
        ->name('cliente.agenda.solicitar');

    Route::post('/cliente/agenda/confirmar-autorizacion', [ClienteAgendaController::class, 'confirmarAutorizacion'])
        ->middleware('throttle:10,1')
        ->name('cliente.agenda.confirmar');

    Route::post('/cliente/agenda-online/comprar', [ClienteAgendaOnlineController::class, 'comprar'])
        ->middleware('throttle:10,1')
        ->name('cliente.agenda-online.comprar');

    Route::prefix('cliente/medsdi')->name('cliente.medsdi.')->group(function () {
        Route::get('regiones', [ClienteAgendaExternaController::class, 'regiones'])->name('regiones');
        Route::get('ciudades', [ClienteAgendaExternaController::class, 'ciudades'])->name('ciudades');
        Route::get('especialidades', [ClienteAgendaExternaController::class, 'especialidades'])->name('especialidades');
        Route::get('tipo-especialidades', [ClienteAgendaExternaController::class, 'tipoEspecialidades'])->name('tipo_especialidades');
        Route::get('sub-tipo-especialidades', [ClienteAgendaExternaController::class, 'subTipoEspecialidades'])->name('sub_tipo_especialidades');
        Route::get('prestaciones', [ClienteAgendaExternaController::class, 'prestaciones'])->name('prestaciones');
        Route::post('cotizar', [ClienteAgendaExternaController::class, 'cotizar'])->middleware('throttle:30,1')->name('cotizar');
        Route::get('profesionales', [ClienteAgendaExternaController::class, 'profesionales'])->name('profesionales');
        Route::get('dias-laborales', [ClienteAgendaExternaController::class, 'diasLaborales'])->name('dias_laborales');
        Route::get('horas-disponibles', [ClienteAgendaExternaController::class, 'horasDisponibles'])->name('horas_disponibles');
        Route::post('agendar', [ClienteAgendaExternaController::class, 'agendar'])->middleware('throttle:10,1')->name('agendar');
        Route::post('vouchers/{voucher}/confirmar-hora', [ClienteAgendaExternaController::class, 'confirmarHora'])->middleware('throttle:10,1')->name('confirmar_hora');
        Route::post('vouchers/{voucher}/sincronizar-hora', [ClienteAgendaExternaController::class, 'sincronizarHora'])->middleware('throttle:20,1')->name('sincronizar_hora');
        Route::post('vouchers/{voucher}/simular-pago', [ClienteAgendaExternaController::class, 'simularPago'])->middleware('throttle:10,1')->name('simular_pago');
    });
});

/* ASISTENTES */
Route::middleware(['auth', 'rol:admin,asistente', '2fa'])->group(function () {
    Route::get('/escritorio-asistente', function (MedsdiAgendaApiService $medsdiApi) {
        $sesionMedsdi = $medsdiApi->asistenteAutenticado();
        $idsRecepcion = array_map('intval', (array) session('asistente_recepcion_voucher_ids', []));
        $bonosRecepcion = Voucher::with(['agenda', 'profesional', 'servicio'])
            ->whereIn('id', $idsRecepcion)
            ->whereHas('agenda', fn ($query) => $query->where('estado', '!=', 'paciente_en_espera'))
            ->get();
        $recepcionesPorVoucher = VoucherDeliveryRequest::whereIn('voucher_id', $idsRecepcion)
            ->where('canal', 'assistant_totem_reception')->latest('id')->get()->unique('voucher_id')->keyBy('voucher_id');
        $pendientesRecepcion = VoucherDeliveryRequest::where('canal', 'assistant_totem_reception')
            ->whereIn('estado', ['prepared', 'simulated', 'sent'])
            ->count();
        $pacientesEnEsperaDetalle = Voucher::with(['agenda', 'profesional', 'servicio'])
            ->whereHas('agenda', fn ($query) => $query->where('estado', 'paciente_en_espera')->where('medichile_estado_id', 4))
            ->orderByDesc('id')
            ->get();
        $pacientesEnEspera = $pacientesEnEsperaDetalle->count();
        $atencionesCerradas = Voucher::with(['agenda', 'atencion'])
            ->whereIn('estado', ['atencion_cerrada', 'validado_atencion'])
            ->orderByDesc('atencion_cerrada_at')
            ->get();
        $validacionesPendientes = $atencionesCerradas->count();
        // El paciente reconocido pertenece a una sola búsqueda. Evita que su
        // ficha quede visible al abrir nuevamente el modal de recepción.
        $pacienteRecepcion = session()->pull('asistente_recepcion_paciente');
        $autorizacionAppPaciente = session('autorizacion_app_paciente_id')
            ? ClienteAutorizacion::find(session('autorizacion_app_paciente_id'))
            : null;
        ClienteAutorizacion::query()
            ->whereIn('tipo_accion', ['compra_bono_beneficiario', 'compra_bono_asistente'])
            ->where('estado', 'pendiente')
            ->whereNotNull('expira_at')
            ->where('expira_at', '<=', now())
            ->update(['estado' => 'expirada']);
        $autorizacionesPaciente = ClienteAutorizacion::query()
            ->whereIn('tipo_accion', ['compra_bono_beneficiario', 'compra_bono_asistente'])
            ->latest('id')
            ->limit(50)
            ->get();
        $autorizacionesPendientes = $autorizacionesPaciente->where('estado', 'pendiente')->count();

        return view('escritorios.asistente', compact(
            'pendientesRecepcion',
            'pacientesEnEspera',
            'validacionesPendientes',
            'sesionMedsdi',
            'bonosRecepcion',
            'recepcionesPorVoucher',
            'pacientesEnEsperaDetalle',
            'atencionesCerradas',
            'autorizacionesPaciente',
            'autorizacionesPendientes',
            'pacienteRecepcion',
            'autorizacionAppPaciente'
        ));
    })->name('asistente.escritorio');

    Route::post('/asistente/autorizaciones/{autorizacion}/responder', function (Request $request, ClienteAutorizacion $autorizacion) {
        abort_unless(config('demo.enabled'), 404);

        $data = $request->validate([
            'respuesta' => ['required', 'in:aprobar,rechazar'],
        ]);

        abort_unless(
            in_array($autorizacion->tipo_accion, ['compra_bono_beneficiario', 'compra_bono_asistente'], true),
            404
        );

        if ($autorizacion->estado !== 'pendiente') {
            return back()->with('abrir_autorizaciones_modal', true)
                ->with('error', 'Esta solicitud ya fue respondida o expiró.');
        }

        if ($autorizacion->expira_at && now()->gte($autorizacion->expira_at)) {
            $autorizacion->update(['estado' => 'expirada']);

            return back()->with('abrir_autorizaciones_modal', true)
                ->with('error', 'La autorización venció. Debe solicitarse nuevamente.');
        }

        $aprobada = $data['respuesta'] === 'aprobar';
        $autorizacion->update([
            'estado' => $aprobada ? 'aprobada' : 'rechazada',
            'aprobada_at' => $aprobada ? now() : null,
            'rechazada_at' => $aprobada ? null : now(),
        ]);
        \App\Helpers\SecurityLogger::log(
            'autorizacion_compra_simulada_'.$autorizacion->fresh()->estado,
            'ClienteAutorizacion',
            $autorizacion->id,
            $autorizacion->fresh()->estado,
            'Respuesta simulada desde el escritorio del asistente',
            $autorizacion->cliente_id
        );

        return back()->with('abrir_autorizaciones_modal', true)
            ->with('ok', $aprobada
                ? 'Compra autorizada. El flujo que originó la solicitud ya puede continuar.'
                : 'La compra fue rechazada por el paciente.');
    })->middleware('throttle:20,1')->name('asistente.autorizaciones.responder');

    Route::get('/personas-rapidas/prueba', [PersonaRapidaController::class, 'index'])
        ->name('personas-rapidas.prueba');
    Route::get('/personas-rapidas/buscar', [PersonaRapidaController::class, 'buscar'])
        ->name('personas-rapidas.buscar');
    Route::post('/personas-rapidas/guardar', [PersonaRapidaController::class, 'guardar'])
        ->name('personas-rapidas.guardar');

    Route::get('/asistente/validaciones', function () {
        $atenciones = \App\Models\VoucherAtencion::with('voucher')
            ->where('estado', 'cerrada_por_profesional')
            ->orderBy('id', 'desc')
            ->get();

        return view('asistentes.validaciones', compact('atenciones'));
    })->name('asistente.validaciones');

    Route::get('/asistente/recepcion-bonos', function () {
        $prevision = \App\Models\VoucherServicio::where('activo', 1)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $qrEnviados = VoucherDeliveryRequest::with(['voucher', 'cliente'])
            ->whereIn('estado', ['prepared', 'simulated', 'sent'])
            ->latest()
            ->take(20)
            ->get();

        return view('asistentes.recepcion_bonos', compact('prevision', 'qrEnviados'));
    })->name('asistente.recepcionBonos');

    Route::post('/asistente/recepcion/{delivery}/espera', [AsistenteRecepcionController::class, 'dejarEnEspera'])
        ->name('asistente.recepcion.espera');
    Route::post('/asistente/recepcion/{delivery}/enviar-whatsapp-externo', [AsistenteRecepcionController::class, 'enviarRecepcionExterna'])
        ->name('asistente.recepcion.enviarWhatsappExterno');
    Route::post('/asistente/recepcion-qr', [AsistenteRecepcionController::class, 'recibirQr'])
        ->middleware('throttle:20,1')
        ->name('asistente.recepcion.qr');
    Route::post('/asistente/buscar-reserva', [AsistenteRecepcionController::class, 'buscarReserva'])
        ->middleware('throttle:20,1')
        ->name('asistente.recepcion.buscar');
    Route::post('/asistente/bonos/{voucher}/solicitar-autorizacion-paciente', [AsistenteRecepcionController::class, 'solicitarAutorizacionPaciente'])
        ->middleware('throttle:10,1')->name('asistente.recepcion.solicitar_autorizacion');
    Route::post('/asistente/autorizacion-paciente/{autorizacion}/responder', [AsistenteRecepcionController::class, 'responderAutorizacionPaciente'])
        ->middleware('throttle:20,1')->name('asistente.recepcion.responder_autorizacion');
    Route::post('/asistente/bonos/{voucher}/confirmar-hora', [AsistenteRecepcionController::class, 'confirmarHora'])
        ->middleware('throttle:10,1')->name('asistente.recepcion.confirmar_hora');
    Route::post('/asistente/bonos/{voucher}/sincronizar-hora', [AsistenteRecepcionController::class, 'sincronizarHora'])
        ->middleware('throttle:20,1')->name('asistente.recepcion.sincronizar_hora');
    Route::post('/asistente/bonos/{voucher}/pagar', [AsistenteRecepcionController::class, 'pagarBono'])
        ->middleware('throttle:10,1')->name('asistente.recepcion.pagar');

    Route::prefix('asistente/venta-bonos')->name('asistente.venta_bonos.')->group(function () {
        Route::get('paciente', [AsistenteVentaBonoController::class, 'paciente'])->name('paciente');
        Route::get('regiones', [ClienteAgendaExternaController::class, 'regiones'])->name('regiones');
        Route::get('ciudades', [ClienteAgendaExternaController::class, 'ciudades'])->name('ciudades');
        Route::get('especialidades', [ClienteAgendaExternaController::class, 'especialidades'])->name('especialidades');
        Route::get('tipo-especialidades', [ClienteAgendaExternaController::class, 'tipoEspecialidades'])->name('tipo_especialidades');
        Route::get('sub-tipo-especialidades', [ClienteAgendaExternaController::class, 'subTipoEspecialidades'])->name('sub_tipo_especialidades');
        Route::get('prestaciones', [ClienteAgendaExternaController::class, 'prestaciones'])->name('prestaciones');
        Route::post('cotizar', [ClienteAgendaExternaController::class, 'cotizar'])->middleware('throttle:30,1')->name('cotizar');
        Route::get('profesionales', [ClienteAgendaExternaController::class, 'profesionales'])->name('profesionales');
        Route::get('dias-laborales', [ClienteAgendaExternaController::class, 'diasLaborales'])->name('dias_laborales');
        Route::get('horas-disponibles', [ClienteAgendaExternaController::class, 'horasDisponibles'])->name('horas_disponibles');
        Route::post('agendar', [AsistenteVentaBonoController::class, 'agendar'])->middleware('throttle:10,1')->name('agendar');
    });
});

Route::get('/asistente/inicio', [RoleHomeController::class, 'asistente'])
    ->middleware(['auth', 'rol:admin,asistente', '2fa'])->name('asistente.home');
Route::get('/profesional/inicio', [RoleHomeController::class, 'profesional'])
    ->middleware(['auth', 'rol:profesional'])->name('profesional.home');
Route::get('/vendedor/inicio', [RoleHomeController::class, 'vendedor'])
    ->middleware(['auth', 'rol:vendedor'])->name('vendedor.home');
Route::get('/administracion/inicio', [RoleHomeController::class, 'administracion'])
    ->middleware(['auth', 'rol:admin', '2fa'])->name('admin.home');
Route::get('/contraloria/inicio', [RoleHomeController::class, 'contraloria'])
    ->middleware(['auth', 'rol:admin,auditor', '2fa'])->name('contraloria.home');


/* VOUCHERS COMPARTIDOS */
Route::middleware(['auth', 'rol:admin,asistente', '2fa'])->post(
    '/vouchers/{id}/validar-atencion-web',
    [VoucherWebController::class, 'validarAtencionWeb']
)->name('vouchers.validarAtencionWeb');
Route::middleware(['auth', 'rol:admin,asistente,vendedor,profesional', '2fa'])->group(function () {

    Route::get('/vouchers/{id}',
        [VoucherWebController::class, 'show'])
        ->name('vouchers.show');

});

Route::get('/voucher/validar/{token}', [VoucherWebController::class, 'validarPantalla'])
    ->middleware(['auth', 'rol:admin,asistente,vendedor,profesional,cliente', '2fa'])
    ->name('vouchers.validarPantalla');

Route::get('/voucher/qr/{token}', [VoucherWebController::class, 'qr'])
    ->middleware(['auth', 'rol:admin,asistente,vendedor,profesional,cliente', '2fa'])
    ->name('vouchers.qr');

Route::get('/voucher/qr/{token}/compartir-datos', [VoucherWebController::class, 'compartirDatos'])
    ->middleware(['auth', 'rol:admin,asistente,vendedor,profesional,cliente', '2fa'])
    ->name('vouchers.compartirDatos');
Route::post('/voucher/qr/{token}/enviar-email', [VoucherWebController::class, 'enviarEmailQr'])
    ->middleware(['auth', 'throttle:10,1'])->name('vouchers.qr.enviarEmail');

Route::get('/voucher/qr/{token}/lector-demo', [VoucherWebController::class, 'simularLectorQr'])
    ->middleware(['auth', 'rol:admin,asistente,vendedor,profesional,cliente', '2fa'])
    ->name('vouchers.qr.lectorDemo');

Route::get('/voucher/qr/{token}/whatsapp-demo', [VoucherWebController::class, 'whatsappDemo'])
    ->middleware(['auth', 'rol:admin,asistente,vendedor,profesional,cliente', '2fa'])
    ->name('vouchers.qr.whatsappDemo');
Route::post('/voucher/qr/{token}/whatsapp-demo/enviar', [VoucherWebController::class, 'enviarWhatsappDemo'])
    ->middleware(['auth', 'rol:admin,asistente,vendedor,profesional,cliente', '2fa', 'throttle:20,1'])
    ->name('vouchers.qr.whatsappDemo.enviar');


/* CLIENTE - WHATSAPP / QR */
Route::get('/voucher/{token}/invalidar',
    [VoucherWebController::class, 'confirmarInvalidacion'])
    ->middleware('throttle:20,1')
    ->name('vouchers.invalidar');

Route::post('/voucher/{token}/invalidar',
    [VoucherWebController::class, 'invalidarCliente'])
    ->middleware('throttle:5,1')
    ->name('vouchers.invalidar.confirmar');

/* CLIENTE */

Route::get('/voucher/{token}/usar',
    [VoucherWebController::class, 'usar'])
    ->name('vouchers.usar');

Route::post('/voucher/{token}/asignar-profesional',
    [VoucherWebController::class, 'asignarProfesional'])
    ->middleware('throttle:10,1')
    ->name('vouchers.asignarProfesional');

Route::post('/voucher/{token}/aceptar',
    [VoucherWebController::class, 'aceptarVoucher'])
    ->middleware('throttle:5,1')
    ->name('vouchers.aceptar');

Route::post('/voucher/{token}/rechazar',
    [VoucherWebController::class, 'rechazarVoucher'])
    ->middleware('throttle:5,1')
    ->name('vouchers.rechazar');

Route::post('/voucher/{token}/reenviar-otp',
    [VoucherWebController::class, 'reenviarOtp'])
    ->middleware('throttle:3,1')
    ->name('vouchers.reenviarOtp');

Route::middleware('throttle:5,1')->post(
    '/voucher/{token}/validar-otp',
    [VoucherWebController::class, 'validarOtp']
)->name('vouchers.validarOtp');

/* AUDITORÍA */

Route::middleware(['auth', 'rol:admin,auditor', '2fa'])->group(function () {

    Route::get('/auditoria',
        [AuditoriaController::class, 'index']);

    Route::get('/admin/alertas',
        [AuditoriaController::class, 'alertas'])
        ->name('admin.alertas');

    Route::post('/auditoria/preconsultas/{id}/resolver',
        [AuditoriaController::class, 'resolverPreconsulta'])
        ->name('auditoria.preconsultas.resolver');

    Route::post('/auditoria/cobros/{id}/resolver',
        [AuditoriaController::class, 'resolverCobro'])
        ->name('auditoria.cobros.resolver');

    Route::post('/auditoria/cobros/{id}/resolver-pago',
        [AuditoriaController::class, 'resolverPago'])
        ->name('auditoria.cobros.resolverPago');

    Route::get('/auditoria/logins', function () {

        $logins = LoginAuditoria::orderBy('id', 'desc')
            ->take(200)
            ->get();

        return view('auditoria.logins', compact('logins'));

        })->name('auditoria.logins');

    Route::post('/admin/alertas/{id}/resolver',
        [AuditoriaController::class, 'resolver'])
        ->name('admin.alertas.resolver');

    Route::get('/escritorio-auditor', function () {
        return redirect('/auditoria');
    });
    Route::get('/auditoria/notificaciones', function () {

        $notificaciones = AuditorNotificacion::with(['voucher', 'alerta'])
            ->orderBy('id', 'desc')
            ->get();

        return view('auditoria.notificaciones', compact('notificaciones'));

        })->name('auditoria.notificaciones');
        Route::post('/auditoria/notificaciones/{id}/leer', function ($id) {

        $notificacion = AuditorNotificacion::findOrFail($id);

        $notificacion->update([
            'leido' => true,
            'fecha_lectura' => now(),
        ]);

        return back()->with('ok', 'Notificación marcada como leída.');

        })->name('auditoria.notificaciones.leer');

    });
/* AUTH BREEZE */

require __DIR__.'/auth.php';
