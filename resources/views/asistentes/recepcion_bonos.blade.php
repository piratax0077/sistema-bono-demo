@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h2 class="mb-1">Recepción de bonos</h2>
            <p class="text-muted mb-0">
                Flujo del asistente: recibir bono físico, vender bono generando QR o recibir QR enviado por el paciente.
            </p>
        </div>
        <a href="{{ route('asistente.escritorio') }}" class="btn btn-outline-secondary btn-sm">Volver al escritorio</a>
    </div>

    @if(session('ok'))
        <div class="alert alert-success">
            {{ session('ok') }}
            @if(session('reception_whatsapp_url'))
                <a href="{{ session('reception_whatsapp_url') }}" target="_blank" rel="noopener" class="alert-link ms-2">Abrir WhatsApp y enviar ahora</a>
            @endif
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Pacientes con bono asociado</h5>
                <small class="text-muted">Reconocidos automáticamente desde compra, WhatsApp, centro médico o tótem.</small>
            </div>
            <span class="badge bg-primary">{{ $qrEnviados->where('canal', 'assistant_totem_reception')->count() }} pendientes</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Paciente</th><th>Bono</th><th>Profesional</th><th>Lugar</th><th>Estado</th><th>Recepción sin sistema</th><th class="text-end">Acción</th></tr></thead>
                    <tbody>
                    @forelse($qrEnviados->where('canal', 'assistant_totem_reception') as $recepcion)
                        <tr>
                            <td>{{ optional($recepcion->voucher)->cliente_nombre ?: optional($recepcion->cliente)->name }}</td>
                            <td><code>{{ optional($recepcion->voucher)->codigo }}</code></td>
                            <td>{{ optional($recepcion->voucher)->prestador_nombre ?: '-' }}</td>
                            <td>{{ $recepcion->destino ?: 'Recepción general' }}</td>
                            <td><span class="badge bg-warning text-dark">Pendiente de llegada</span></td>
                            <td style="min-width: 290px">
                                <form method="POST" action="{{ route('asistente.recepcion.enviarWhatsappExterno', $recepcion) }}" class="d-flex gap-2">
                                    @csrf
                                    <input type="tel" name="telefono_recepcion" class="form-control form-control-sm"
                                           placeholder="+56912345678" aria-label="WhatsApp de recepción externa" required>
                                    <button class="btn btn-outline-success btn-sm text-nowrap">Enviar QR</button>
                                </form>
                                <small class="text-muted">Para instituciones sin bandeja de recepción SDI.</small>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('asistente.recepcion.espera', $recepcion) }}">
                                    @csrf
                                    <button class="btn btn-success btn-sm">Dejar en espera</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No hay pacientes pendientes de recepción.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title">Conexión con API SDI</h5>
            <p class="text-muted">
                Este proyecto base usa la API segura del proyecto nuevo. Inicia sesión en la API SDI y pega aquí el access token
                del asistente para probar venta/recepción QR.
            </p>
            <div class="row g-2 align-items-end">
                <div class="col-md-8">
                    <label class="form-label">Access token API SDI</label>
                    <input type="password" id="sdi_token_input" class="form-control" placeholder="Bearer token del asistente">
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-primary w-100" onclick="sdiGuardarTokenRecepcion()">
                        Guardar token en este navegador
                    </button>
                </div>
            </div>
            <small class="text-muted d-block mt-2">
                API configurada: <code id="sdi_api_base_label"></code>
            </small>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Modal de pago consulta</h5>
            <p class="text-muted">
                Usa este botón para revisar las 3 pestañas del modal. En la agenda real se abrirá desde la hora médica seleccionada.
            </p>
            <button type="button" id="btn_abrir_recepcion_bonos" class="btn btn-success"
                    onclick="sdiAbrirRecepcionBonos(event)">
                Abrir recepción de bonos
            </button>
            <div id="recepcion_bonos_demo_result" class="alert alert-info mt-3 mb-0 d-none"></div>
        </div>
    </div>
</div>

@include('partials.modal_consulta_agenda')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    window.SDI_API_BASE_URL = window.SDI_API_BASE_URL || 'http://127.0.0.1:8000/api/v1';
    window.SDI_RECEIVE_QR_URL = @json(route('asistente.recepcion.qr'));
    document.getElementById('sdi_api_base_label').textContent = window.SDI_API_BASE_URL;

    function sdiAbrirRecepcionBonos(event) {
        if (event) {
            event.preventDefault();
        }

        var modal = document.getElementById('modal_recepcion_bonos_api');
        if (!modal) {
            return;
        }

        if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modal).show();
            return;
        }

        modal.style.display = 'block';
        modal.classList.add('show');
        modal.removeAttribute('aria-hidden');
        modal.setAttribute('aria-modal', 'true');
        document.body.classList.add('modal-open');

        if (!document.getElementById('sdi_modal_backdrop')) {
            var backdrop = document.createElement('div');
            backdrop.id = 'sdi_modal_backdrop';
            backdrop.className = 'modal-backdrop fade show';
            backdrop.addEventListener('click', sdiCerrarRecepcionBonos);
            document.body.appendChild(backdrop);
        }
    }

    function sdiCerrarRecepcionBonos(event) {
        if (event) {
            event.preventDefault();
        }

        var modal = document.getElementById('modal_recepcion_bonos_api');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
            modal.removeAttribute('aria-modal');
        }
        document.body.classList.remove('modal-open');
        var backdrop = document.getElementById('sdi_modal_backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var modal = document.getElementById('modal_recepcion_bonos_api');
        if (!modal) {
            return;
        }

        modal.querySelectorAll('.close_modal_recepcion_bonos_api').forEach(function (button) {
            button.addEventListener('click', function (event) {
                if (!(window.bootstrap && window.bootstrap.Modal)) {
                    sdiCerrarRecepcionBonos(event);
                }
            });
        });

        document.querySelectorAll('#pills-tab-bonos .nav-link').forEach(function (tab) {
            tab.addEventListener('click', function (event) {
                if (window.bootstrap && window.bootstrap.Tab) {
                    return;
                }

                event.preventDefault();
                document.querySelectorAll('#pills-tab-bonos .nav-link').forEach(function (item) {
                    item.classList.remove('active');
                    item.setAttribute('aria-selected', 'false');
                });
                document.querySelectorAll('#pills-tabContent-interconsulta > .tab-pane').forEach(function (pane) {
                    pane.classList.remove('show', 'active');
                });

                tab.classList.add('active');
                tab.setAttribute('aria-selected', 'true');
                var pane = document.querySelector(tab.getAttribute('href'));
                if (pane) {
                    pane.classList.add('show', 'active');
                }
            });
        });
    });

    function sdiGuardarTokenRecepcion() {
        var input = document.getElementById('sdi_token_input');
        var token = input ? input.value.trim() : '';
        if (!token) {
            alert('Pega el access token API del asistente.');
            return;
        }
        localStorage.setItem('sdi_access_token', token.replace(/^Bearer\s+/i, ''));
        alert('Token guardado. Ya puedes probar venta/recepción QR.');
    }

    window.conectar_api = window.conectar_api || function () {
        var box = document.getElementById('recepcion_bonos_demo_result');
        if (box) {
            box.classList.remove('d-none');
            box.textContent = 'Autorización lista para integrar con el flujo de agenda.';
        }
    };

    window.recepcion_pago = window.recepcion_pago || function () {
        var box = document.getElementById('recepcion_bonos_demo_result');
        var canal = document.getElementById('bono_canal_recepcion_qr');
        var canalTexto = canal && canal.options[canal.selectedIndex]
            ? canal.options[canal.selectedIndex].text
            : 'canal no informado';
        if (box) {
            box.classList.remove('d-none');
            box.textContent = 'Recepción registrada desde: ' + canalTexto + '. El paciente queda listo para pasar a sala de espera.';
        }
    };
</script>
@endsection
