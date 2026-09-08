<?php

use App\Http\Controllers\Api\ClienteAutorizacionController;
use App\Http\Controllers\Api\AssistantVoucherController;
use App\Http\Controllers\Api\PagoAutorizacionController;
use App\Http\Controllers\Api\PreconsultaVoucherController;
use App\Http\Controllers\Api\Totem\AuthTotemController;
use App\Http\Controllers\Api\Totem\CatalogoTotemController;
use App\Http\Controllers\Api\Totem\ClienteTotemController;
use App\Http\Controllers\Api\Totem\DashboardTotemController;
use App\Http\Controllers\Api\Totem\EntregaTotemController;
use App\Http\Controllers\Api\Totem\HomeTotemController;
use App\Http\Controllers\Api\Totem\MascotaSeguraTotemController;
use App\Http\Controllers\Api\Totem\PagoTotemController;
use App\Http\Controllers\Api\Totem\PrestadorTotemController;
use App\Http\Controllers\Api\Totem\StatusTotemController;
use App\Http\Controllers\Api\Totem\TicketTotemController;
use App\Http\Controllers\Api\Totem\VentaTotemController;
use App\Http\Controllers\Api\Totem\VoucherAtencionController;
use App\Http\Controllers\Api\VoucherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API pública de verificación
|--------------------------------------------------------------------------
| Sólo expone información mínima y nunca permite mutar un voucher.
*/
Route::middleware('throttle:voucher-read')->group(function () {
    Route::get('/vouchers/{qr_token}/validar', [VoucherController::class, 'validar']);
    Route::post('/vouchers/{qr_token}/validar-otp', [VoucherController::class, 'validarOtp']);
});

/*
|--------------------------------------------------------------------------
| Operaciones financieras autenticadas
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'rol:admin,vendedor,asistente', 'throttle:voucher-write'])->group(function () {
    Route::post('/preconsultas/validar', [PreconsultaVoucherController::class, 'validar']);
});

Route::middleware(['auth:sanctum', 'rol:admin,vendedor', 'throttle:voucher-write'])->group(function () {
    Route::post('/vouchers/emitir', [VoucherController::class, 'emitir']);
    Route::post('/vouchers/{qr_token}/marcar-pagado', [VoucherController::class, 'marcarPagado']);
});

Route::middleware(['auth:sanctum', 'rol:admin,profesional,asistente', 'throttle:voucher-write'])->group(function () {
    Route::post('/vouchers/{qr_token}/canjear', [VoucherController::class, 'canjear']);
    Route::post('/vouchers/{qr_token}/cobrar', [VoucherController::class, 'cobrar']);
});

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::prefix('v1')
    ->middleware(['auth:sanctum', 'rol:admin,asistente', 'throttle:voucher-write'])
    ->group(function () {
        Route::get('/catalog', [AssistantVoucherController::class, 'catalog']);
        Route::post('/clients/resolve', [AssistantVoucherController::class, 'resolveClient']);
        Route::post('/vouchers', [AssistantVoucherController::class, 'store']);
        Route::post('/vouchers/{voucher}/deliver', [AssistantVoucherController::class, 'deliver']);
        Route::post('/vouchers/receive-qr', [AssistantVoucherController::class, 'receiveQr']);
    });

/*
|--------------------------------------------------------------------------
| Tótem
|--------------------------------------------------------------------------
| El código del tótem sólo inicia una sesión corta. El resto requiere el
| token rotatorio, IP autorizada (cuando está configurada) y rate limiting.
*/
Route::prefix('totem')->group(function () {
    Route::get('/status', [StatusTotemController::class, 'status']);

    Route::post('/login', [AuthTotemController::class, 'login'])
        ->middleware('throttle:totem-login');

    Route::middleware(['auth.totem', 'throttle:totem'])->group(function () {
        Route::get('/catalogo', [CatalogoTotemController::class, 'index']);
        Route::get('/home', [HomeTotemController::class, 'index']);
        Route::get('/prestadores/buscar', [PrestadorTotemController::class, 'buscar']);
        Route::get('/prestadores', [PrestadorTotemController::class, 'index']);

        Route::post('/venta', [VentaTotemController::class, 'crear']);
        Route::post('/pago', [PagoTotemController::class, 'confirmar']);
        Route::post('/venta/{id}/entrega', [EntregaTotemController::class, 'entregar']);
        Route::get('/venta/{id}/ticket', [TicketTotemController::class, 'ticket']);
        Route::post('/vouchers/{voucher}/deliver', [TicketTotemController::class, 'deliver']);
        Route::post('/ping', [StatusTotemController::class, 'ping']);
        Route::post('/malfunction', [StatusTotemController::class, 'malfunction']);

        Route::post('/cliente/buscar', [ClienteTotemController::class, 'buscar']);
        Route::post('/cliente/registrar', [ClienteTotemController::class, 'registrar']);
        Route::get('/cliente/{id}/mascotas', [ClienteTotemController::class, 'mascotas']);
        Route::post('/cliente/mascotas/registrar', [ClienteTotemController::class, 'registrarMascota']);

        Route::get('/mascotas/{id}/dueno-protegido', [MascotaSeguraTotemController::class, 'duenoProtegido']);
        Route::post('/vouchers/{voucher}/solicitar-hora', [VoucherAtencionController::class, 'solicitarHora']);
        Route::post('/vouchers/{voucher}/confirmar-hora', [VoucherAtencionController::class, 'confirmarHora']);
        Route::post('/vouchers/{voucher}/cerrar-atencion', [VoucherAtencionController::class, 'cerrarAtencion']);
        Route::post('/vouchers/{voucher}/validar-atencion', [VoucherAtencionController::class, 'validarAtencion']);
    });
});

/*
|--------------------------------------------------------------------------
| Administración central de tótems
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'rol:admin', 'throttle:api'])
    ->get('/admin/totems/dashboard', [DashboardTotemController::class, 'index']);

/*
|--------------------------------------------------------------------------
| App autorizadora del paciente
|--------------------------------------------------------------------------
*/
Route::prefix('cliente-autorizacion')->middleware('throttle:authorizations')->group(function () {
    Route::post('/registrar-dispositivo', [ClienteAutorizacionController::class, 'registrarDispositivo']);
    Route::post('/pendientes', [ClienteAutorizacionController::class, 'pendientes']);
    Route::post('/responder', [ClienteAutorizacionController::class, 'responder']);
});

Route::prefix('cliente-autorizacion')
    ->middleware(['auth.totem', 'throttle:authorizations'])
    ->group(function () {
        Route::post('/solicitar', [ClienteAutorizacionController::class, 'solicitar']);
        Route::get('/verificar/{token}', [ClienteAutorizacionController::class, 'verificar']);
    });

/*
|--------------------------------------------------------------------------
| App autorizadora del profesional
|--------------------------------------------------------------------------
*/
Route::prefix('autorizaciones-pago')->middleware('throttle:authorizations')->group(function () {
    Route::post('/{token}/aprobar', [PagoAutorizacionController::class, 'aprobar']);
    Route::post('/{token}/rechazar', [PagoAutorizacionController::class, 'rechazar']);
});
