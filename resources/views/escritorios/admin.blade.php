<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administración de Bonos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>.admin-shell{max-width:1320px}.page-kicker{color:#1848a1;font-size:.75rem;font-weight:850;letter-spacing:.16em;text-transform:uppercase}.admin-grid>.col-md-4{display:flex}.admin-grid .card{width:100%;position:relative;overflow:hidden;transition:transform .2s ease,box-shadow .2s ease}.admin-grid .card:before{content:"";position:absolute;inset:0 auto 0 0;width:4px;background:linear-gradient(#1848a1,#31bebe)}.admin-grid .card:hover{transform:translateY(-3px)}.admin-grid .card p{color:#7b8798;min-height:48px}.admin-grid .card .btn{margin-top:auto;width:100%}.header-actions{display:flex;gap:.65rem;flex-wrap:wrap}</style>
</head>

<body>
@include('partials.demo_user_switcher')

<div class="container admin-shell py-5">
    <header class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div><div class="page-kicker">Medichile · Operación central</div><h1 class="h2 fw-bold mb-1">Administración de bonos</h1><p class="text-muted mb-0">Control operativo, financiero y de seguridad en una sola vista.</p></div>
        <div class="header-actions"><a href="{{ route('demo.portal') }}" class="btn btn-outline-secondary">Vista general</a><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-danger">Cerrar sesión</button></form></div>
    </header>

    <div class="row g-4 admin-grid">

    <div class="col-md-4">
        <div class="card p-4 shadow-sm border-0">
            <h5>Gestión de bonos</h5>
            <p>Bonos activos, usados, cobrados y trazabilidad completa del sistema.</p>
            <a href="{{ route('admin.bonos.gestion') }}" class="btn btn-success">
                Gestionar Bonos
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4 shadow-sm border-0">
            <h5>Dashboard Financiero</h5>
            <p>Resumen financiero, comisiones, rendiciones y caja.</p>
            <a href="{{ url('dashboard-financiero') }}" class="btn btn-info">
                Ver Dashboard
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4 shadow-sm border-0">
            <h5>Usuarios</h5>
            <p>Crear usuarios, roles y permisos.</p>
            <a href="{{ url('admin/usuarios') }}" class="btn btn-primary">
                Administrar Usuarios
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4 shadow-sm border-0">
            <h5>Base r&aacute;pida personas</h5>
            <p>Buscar, editar o agregar personas por RUT.</p>
            <a href="{{ route('personas-rapidas.prueba') }}" class="btn btn-secondary">
                Probar Base Personas
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4 shadow-sm border-0">
            <h5>💰 Saldos Clientes</h5>

            <p>
                Copagos retenidos como saldo a favor.
            </p>

            <a href="{{ route('admin.saldos.clientes') }}"
            class="btn btn-warning">
                Ver Saldos Clientes
            </a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 shadow-sm border-0">
            <h5>Profesionales</h5>
            <p>Médicos, especialistas y centros de salud autorizados.</p>
            <a href="{{ url('admin/profesionales') }}" class="btn btn-dark">
                Ver Profesionales
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4 shadow-sm border-0">
            <h5>Emisores de bonos</h5>
            <p>Usuarios autorizados para emitir bonos de atención.</p>
            <a href="{{ url('admin/vendedores') }}" class="btn btn-success">
                Ver Emisores
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4 shadow-sm border-0">
            <h5>Rendiciones</h5>
            <p>Cobros enviados a pago por profesionales.</p>
            <a href="{{ url('rendiciones') }}" class="btn btn-warning">
                Ver Rendiciones
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4 shadow-sm border-0">
            <h5>Liquidaciones</h5>
            <p>Pagos a profesionales y comisión SDI.</p>
            <a href="{{ url('liquidaciones') }}" class="btn btn-success">
                Ver Liquidaciones
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4 shadow-sm border-0">
            <h5>Auditoría</h5>
            <p>Trazabilidad, fraudes, anulaciones y control interno.</p>
            <a href="{{ url('auditoria') }}" class="btn btn-danger">
                Auditoría
            </a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 shadow-sm border-0">

            <h5>Prestaciones médicas</h5>

            <p>
                Administración de prestaciones médicas y sus valores.
            </p>

            <a href="{{ url('admin/servicios') }}"
            class="btn btn-primary">
                Ver Prestaciones
            </a>

        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4 shadow-sm border-0">
            <h5>Alertas Auditoría</h5>
            <p>Duplicados, riesgo financiero y control antifraude.</p>

            <a href="{{ url('admin/alertas') }}"
            class="btn btn-danger">
                Ver Alertas
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-4 shadow-sm border-0">
            <h5>Tótems de atención</h5>
            <p>Activación centralizada, claves de instalación, estado operativo y alertas GPS.</p>

            <a href="{{ route('admin.totems.dashboard') }}"
            class="btn btn-dark">
                Administrar Tótems
            </a>
        </div>
    </div>

</div>

</div>

</body>
</html>
