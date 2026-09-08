<!-- INICIO RECEPCION BONO  -->
<!--Modal Recepción de Bonos y programas-->
<div id="modal_recepcion_bonos_api" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="Recepcion de bonos" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white" id="modal_pago_consulta_title">Pago Consulta</h5>
                <button type="button" class="btn-close close_modal_recepcion_bonos_api" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pb-0">
                {{--  BOTONES  --}}
                <ul class="nav nav-pills mt-3 mb-4" id="pills-tab-bonos" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link nav-link-modal active" id="pills-tab-recibir-bono" data-toggle="pill" data-bs-toggle="pill" href="#pills-recibir-bono" role="tab" aria-controls="pills-home" aria-selected="true">Recibir bono físico</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modal" id="pills-venta-tab" data-toggle="pill" data-bs-toggle="pill" href="#pills-venta" role="tab" aria-controls="pills-venta" aria-selected="false">Vender bono / QR</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-modal" id="pills-recibir-qr-tab" data-toggle="pill" data-bs-toggle="pill" href="#pills-recibir-qr" role="tab" aria-controls="pills-recibir-qr" aria-selected="false">Recibir QR paciente</a>
                    </li>
                </ul>
               
                <div class="tab-content" id="pills-tabContent-interconsulta">
                    {{--  PESTAÑA DE RECIBIR PAGO  --}}
                    <div class="tab-pane fade show active" id="pills-recibir-bono" role="tabpanel" aria-labelledby="pills-tab-recibir-bono">
                        <div class="form-row">
                            <input type="hidden" name="bono_hora_medica" id="bono_hora_medica">
                            <input type="hidden" name="bono_id_profesional" id="bono_id_profesional">
                            <input type="hidden" name="bono_id_paciente" id="bono_id_paciente">
                            <input type="hidden" name="bono_id_tipo_bono" id="bono_id_tipo_bono" value="1">
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Rut del Paciente</label>
                                    <input type="person" class="form-control form-control-sm" name="bono_paciente_rut" id="bono_paciente_rut">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Nombre del Paciente</label>
                                    <input type="text" class="form-control form-control-sm" name="bono_paciente_nombre" id="bono_paciente_nombre">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm"> Nombre Profesional</label>
                                    <input type="text" class="form-control form-control-sm" name="bono_profesional_nombre" id="bono_profesional_nombre">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm"> Rut Profesional</label>
                                    <input type="text" class="form-control form-control-sm" name="bono_profesional_rut" id="bono_profesional_rut">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">¿Cómo se recibió el QR?</label>
                                    <select id="bono_canal_recepcion_qr" name="bono_canal_recepcion_qr" class="form-control form-control-sm">
                                        <option value="lector_recepcion">Lector QR en recepción</option>
                                        <option value="whatsapp_paciente">WhatsApp del paciente</option>
                                        <option value="whatsapp_profesional">WhatsApp del profesional</option>
                                        <option value="whatsapp_institucion">WhatsApp del centro médico / institución</option>
                                        <option value="email_paciente">Email del paciente</option>
                                        <option value="email_profesional">Email del profesional</option>
                                        <option value="email_institucion">Email del centro médico / secretaría</option>
                                        <option value="totem">Tótem de autoatención</option>
                                        <option value="codigo_manual">Código QR ingresado manualmente</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Nº de bono o programa</label>
                                    <input type="text" class="form-control form-control-sm" name="bono_numero" id="bono_numero" >
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Convenio</label>
                                    <select id="bono_prevision" name="bono_prevision" class="form-control form-control-sm">
                                        <option value="0">Selecione una opción</option>
                                        @foreach ($prevision as $prev)
                                            <option value="{{ $prev->id }}">{{ $prev->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Valor Bonificación</label>
                                    <input type="number" class="form-control form-control-sm" name="valor_bonificacion" id="valor_bonificacion" value="">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Aporte Seguro</label>
                                    <input type="number" class="form-control form-control-sm" name="valor_seguro" id="valor_seguro" value="0">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Valor total</label>
                                    <input name="bono_valor_consulta" id="bono_valor_consulta" type="number" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group mb-3">
                                    <div class="switch switch-success d-inline m-r-10">
                                        <input type="checkbox" id="recepcion_programa">
                                        <label for="recepcion_programa" class="cr"></label>
                                    </div>
                                    <label>Recepción de programa</label>
                                </div>
                                <div class="form-group" id="sesiones_programa" style="display:none">
                                    <label class="floating-label">Nº de Sesiones</label>
                                    <input name="bono_sn_sesiones" id="bono_sn_sesiones" type="number" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group text-center my-2 pb-2">
                                    <div onclick="recepcion_pago();" class="btn btn-success">Recepcionar</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{--  PESTAÑA DE VENTA DE BONO  --}}
                    <div class="tab-pane fade" id="pills-venta" role="tabpanel" aria-labelledby="pills-venta-tab">
                        <div class="alert alert-info py-2 mb-3" id="sdi_venta_bono_intro">
                            La asistente vende el bono, el QR aparece aqu&iacute; y queda asignado al profesional relacionado.
                            El env&iacute;o/canje se libera s&oacute;lo cuando el copago est&eacute; confirmado y el paciente haya aceptado.
                        </div>
                        <div class="form-row" id="sdi_venta_bono_form">
                            <input type="hidden" id="venta_client_id">
                            <input type="hidden" id="venta_provider_id">
                            <input type="hidden" id="venta_servicio_id">
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label">Rut</label>
                                    <input type="person" class="form-control form-control-sm" name="rut" id="rut">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label">Nº de serie carne</label>
                                    <input type="text" class="form-control form-control-sm" name="serie" id="serie">
                                    </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label">Nombre</label>
                                    <input type="text" class="form-control form-control-sm" name="nombre" id="nombre">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label">Email del Paciente</label>
                                    <input type="email" class="form-control form-control-sm" name="venta_email_paciente" id="venta_email_paciente" placeholder="opcional">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label">Tel&eacute;fono del Paciente</label>
                                    <input type="text" class="form-control form-control-sm" name="venta_telefono_paciente" id="venta_telefono_paciente" placeholder="+56...">
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label">Previsión</label>
                                    <select id="prevision" name="previsioon" class="form-control form-control-sm">
                                        <option value="0">Selecione una opción</option>
                                        @foreach ($prevision as $prev)
                                            <option value="{{ $prev->id }}">{{ $prev->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label">Destino del QR</label>
                                    <select id="sdi_bono_destino" class="form-control form-control-sm">
                                        <option value="provider_email">Enviar al profesional relacionado</option>
                                        <option value="patient_whatsapp">Enviar al WhatsApp del paciente</option>
                                        <option value="patient_email">Enviar al email del paciente</option>
                                        <option value="manual">Mostrar s&oacute;lo en este modal</option>
                                    </select>
                                    <small class="text-muted">Si no existe relaci&oacute;n paciente-profesional, se bloquear&aacute; la venta.</small>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <button type="button" onclick="conectar_api();" class="btn btn-outline-info btn-sm has-ripple">Pedir Autorización</button>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group fill">
                                    <label class="floating-label">Autorizacion app beneficiario</label>
                                    <input type="text" class="form-control form-control-sm sdi-qr-input" id="sdi_cliente_authorization_token" placeholder="Se completa al solicitar aprobacion o pegue el token aprobado">
                                    <small class="text-muted">La asistente no puede generar el bono hasta que el beneficiario apruebe en su app.</small>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label">Folio</label>
                                    <input type="text" class="form-control form-control-sm" name="folio" id="folio">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label">Valor Bono</label>
                                    <input type="number" class="form-control form-control-sm" name="valor_consulta" id="valor_consulta">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label">Valor Bonificación</label>
                                    <input type="number" class="form-control form-control-sm" name="valor_pagar" id="valor_pagar">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label">Aporte Seguro</label>
                                    <input type="number" class="form-control form-control-sm" name="valor_seguro" id="valor_seguro">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label">Valor a pagar</label>
                                    <input type="number" class="form-control form-control-sm" name="valor_copago" id="valor_copagp">
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="custom-control custom-checkbox mb-3">
                                    <input type="checkbox" class="custom-control-input" id="sdi_relacion_profesional" checked>
                                    <label class="custom-control-label" for="sdi_relacion_profesional">
                                        Existe relación paciente-profesional y el bono debe quedar en la carpeta del profesional.
                                    </label>
                                </div>
                            </div>
                            <hr>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <button type="button" id="sdi_btn_vender_bono" onclick="sdiVenderBonoAsistente();" class="btn btn-info btn-sm has-ripple left-0">
                                        Vender bono y generar QR
                                    </button>
                                    {{--  <button type="button" class="btn btn-danger btn-sm has-ripple " data-dismiss="modal">Cerrar</button>  --}}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill text-left">
                                    {{--  <button type="submit" class="btn btn-info btn-sm has-ripple">Pagar Atención Médica</button>  --}}
                                    <button type="button" class="btn btn-danger btn-sm has-ripple " data-dismiss="modal" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                        <div id="sdi_bono_resultado" class="sdi-bono-result d-none">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <small class="text-uppercase text-success font-weight-bold">Resultado seguro</small>
                                    <h6 class="mb-1">Bono <span id="sdi_bono_codigo">---</span> generado</h6>
                                    <p class="text-muted mb-0" id="sdi_bono_estado">QR listo en este modal.</p>
                                </div>
                                <button type="button" class="btn btn-sm btn-light" onclick="sdiResetVentaBono();">Nueva venta</button>
                            </div>
                            <div class="row align-items-center">
                                <div class="col-sm-5 text-center">
                                    <img id="sdi_bono_qr_img" class="sdi-bono-qr" alt="QR seguro del bono">
                                </div>
                                <div class="col-sm-7">
                                    <label class="floating-label-activo-sm">Código QR seguro</label>
                                    <div class="sdi-bono-token" id="sdi_bono_qr_token"></div>
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="sdiCopiarQrModal();">Copiar código</button>
                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="sdiEntregarBonoModal('provider_email');">Enviar al profesional</button>
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="sdiEntregarBonoModal('patient_whatsapp');">WhatsApp paciente</button>
                                        <button type="button" class="btn btn-success btn-sm" onclick="sdiRecepcionarBonoVendido();">Recepcionar</button>
                                    </div>
                                </div>
                            </div>
                            <div id="sdi_bono_mensaje" class="alert alert-secondary py-2 mt-3 mb-0"></div>
                        </div>
                    </div>
                    {{--  PESTAÑA DE RECIBIR QR ENVIADO POR PACIENTE  --}}
                    <div class="tab-pane fade" id="pills-recibir-qr" role="tabpanel" aria-labelledby="pills-recibir-qr-tab">
                        <div class="alert alert-warning py-2 mb-3">
                            Recibe el QR que trae o envía el paciente. La recepción sólo pasa si el bono está activo
                            y el RUT del paciente corresponde al profesional de esta hora médica.
                        </div>
                        @if(isset($qrEnviados) && $qrEnviados->count())
                            <div class="sdi-delivery-list mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>QR enviados por beneficiarios</strong>
                                    <small class="text-muted">{{ $qrEnviados->count() }} ultimos</small>
                                </div>
                                @foreach($qrEnviados as $envio)
                                    @php
                                        $voucherEnviado = $envio->voucher;
                                        $metaEnvio = $envio->metadata ?: [];
                                        $clienteNombreEnvio = data_get($metaEnvio, 'cliente_nombre');
                                        if (! $clienteNombreEnvio && $envio->cliente) {
                                            $clienteNombreEnvio = $envio->cliente->name;
                                        }
                                        if (! $clienteNombreEnvio && $voucherEnviado) {
                                            $clienteNombreEnvio = $voucherEnviado->cliente_nombre;
                                        }
                                        $clienteRutEnvio = data_get($metaEnvio, 'cliente_rut');
                                        if (! $clienteRutEnvio && $voucherEnviado) {
                                            $clienteRutEnvio = $voucherEnviado->beneficiario_rut_visible
                                                ?: $voucherEnviado->cliente_rut_visible;
                                        } elseif ($voucherEnviado) {
                                            $clienteRutEnvio = $voucherEnviado->beneficiario_rut_visible
                                                ?: $voucherEnviado->cliente_rut_visible;
                                        }
                                        $profesionalNombreEnvio = data_get($metaEnvio, 'profesional_nombre');
                                        if (! $profesionalNombreEnvio && $voucherEnviado) {
                                            $profesionalNombreEnvio = $voucherEnviado->prestador_nombre;
                                        }
                                    @endphp
                                    @if($voucherEnviado && $voucherEnviado->qr_token)
                                        <div class="sdi-delivery-item">
                                            <div>
                                                <strong>{{ $voucherEnviado->codigo }}</strong>
                                                <small class="text-muted d-block">
                                                    {{ $clienteNombreEnvio ?: 'Beneficiario' }}
                                                    -
                                                    {{ $clienteRutEnvio ?: 'sin RUT' }}
                                                </small>
                                                <small class="text-muted d-block">
                                                    Profesional: {{ $profesionalNombreEnvio ?: '-' }}
                                                </small>
                                                <small class="text-muted d-block">
                                                    Destino: {{ $envio->destino_tipo }} | {{ optional($envio->created_at)->format('d-m-Y H:i') }}
                                                </small>
                                            </div>
                                            <button type="button"
                                                    class="btn btn-outline-success btn-sm"
                                                    data-qr-token="{{ $voucherEnviado->qr_token }}"
                                                    data-cliente-rut="{{ $clienteRutEnvio }}"
                                                    data-profesional="{{ $profesionalNombreEnvio }}"
                                                    onclick="sdiUsarQrEnviado(this)">
                                                Cargar QR
                                            </button>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                        <div class="form-row">
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Rut del Paciente</label>
                                    <input type="text" class="form-control form-control-sm" id="qr_paciente_rut" placeholder="Se copia desde la agenda si existe">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Profesional asociado</label>
                                    <input type="text" class="form-control form-control-sm" id="qr_profesional_nombre" readonly>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">QR enviado por el paciente</label>
                                    <textarea class="form-control form-control-sm sdi-qr-input" id="qr_token_paciente" rows="4" placeholder="Pegue aquí el token leído del QR o escanéelo con lector QR"></textarea>
                                    <small class="text-muted">También acepta enlaces o texto que contengan el token seguro.</small>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Referencia agenda</label>
                                    <input type="text" class="form-control form-control-sm" id="qr_agenda_referencia" placeholder="Opcional: id de hora médica">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group fill">
                                    <label class="floating-label-activo-sm">Estado al recibir</label>
                                    <input type="text" class="form-control form-control-sm" id="qr_estado_agenda" value="confirmed">
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group text-center my-2 pb-2">
                                    <button type="button" id="sdi_btn_recibir_qr_paciente" onclick="sdiRecibirQrPaciente();" class="btn btn-success">
                                        Validar QR y pasar a sala de espera
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div id="sdi_qr_recepcion_resultado" class="sdi-bono-result d-none">
                            <small class="text-uppercase text-success font-weight-bold">Recepción QR</small>
                            <h6 class="mb-1">Paciente con pago confirmado y en sala de espera</h6>
                            <p class="mb-2" id="sdi_qr_recepcion_detalle"></p>
                            <div id="sdi_qr_recepcion_mensaje" class="alert alert-secondary py-2 mb-0"></div>
                        </div>
                    </div>
                     {{--   cuando el sitema recibe el bono generado borra el formulario y aparece el Qr con boton para recepción y cambiar estado de paciente en espera--}}
                   
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #pills-venta .alert,
    #pills-recibir-qr .alert {
        border-radius: 12px;
    }
    .sdi-qr-input {
        font-family: Consolas, Monaco, monospace;
        font-size: 12px;
        resize: vertical;
    }
    .sdi-bono-result {
        border: 1px solid #b8efe5;
        border-radius: 14px;
        background: #f3fffc;
        padding: 14px;
        margin-bottom: 16px;
    }
    .sdi-bono-qr {
        max-width: 180px;
        width: 100%;
        background: #fff;
        border: 1px solid #d9eeee;
        border-radius: 12px;
        padding: 10px;
    }
    .sdi-bono-token {
        min-height: 46px;
        max-height: 96px;
        overflow: auto;
        overflow-wrap: anywhere;
        font-family: Consolas, Monaco, monospace;
        font-size: 11px;
        line-height: 1.45;
        background: #fff;
        border: 1px dashed #69cabe;
        border-radius: 10px;
        padding: 10px;
    }
    .sdi-delivery-list {
        border: 1px solid #d7eeea;
        border-radius: 14px;
        background: #fbfffe;
        padding: 12px;
        max-height: 260px;
        overflow: auto;
    }
    .sdi-delivery-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        padding: 10px 0;
        border-top: 1px solid #edf7f5;
    }
    .sdi-delivery-item:first-of-type {
        border-top: 0;
    }
</style>

<script>
    (function () {
        if (window.__sdiVentaBonoModalLoaded) {
            return;
        }
        window.__sdiVentaBonoModalLoaded = true;

        var ventaState = {
            voucherId: null,
            voucherCode: null,
            qrToken: null
        };
        var catalogCache = null;

        function byId(id) {
            return document.getElementById(id);
        }

        function readValue(id) {
            var element = byId(id);
            return element ? String(element.value || '').trim() : '';
        }

        function writeValue(id, value) {
            var element = byId(id);
            if (element) {
                element.value = value == null ? '' : value;
            }
        }

        function writeText(id, value) {
            var element = byId(id);
            if (element) {
                element.textContent = value == null ? '' : value;
            }
        }

        function firstValue(ids) {
            for (var i = 0; i < ids.length; i += 1) {
                var value = readValue(ids[i]);
                if (value) {
                    return value;
                }
            }
            return '';
        }

        function numericId(value) {
            var parsed = parseInt(String(value || '').replace(/[^0-9]/g, ''), 10);
            return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
        }

        function storageGet(key) {
            try {
                return window.localStorage.getItem(key) || window.sessionStorage.getItem(key) || '';
            } catch (error) {
                return '';
            }
        }

        function apiBaseUrl() {
            var configured = window.SDI_API_BASE_URL || storageGet('sdi_api_base_url');
            if (configured) {
                return String(configured).replace(/\/+$/, '');
            }
            if (window.location && window.location.port === '8000') {
                return window.location.origin + '/api/v1';
            }
            return 'http://127.0.0.1:8000/api/v1';
        }

        function apiToken() {
            return window.SDI_ACCESS_TOKEN ||
                window.SDI_API_TOKEN ||
                storageGet('sdi_access_token') ||
                storageGet('sdi_api_token') ||
                storageGet('access_token') ||
                storageGet('token');
        }

        function idempotencyKey(prefix) {
            if (window.crypto && window.crypto.randomUUID) {
                return prefix + '-' + window.crypto.randomUUID();
            }
            return prefix + '-' + Date.now() + '-' + Math.random().toString(36).slice(2);
        }

        function extractError(payload) {
            if (!payload) {
                return 'No se pudo completar la solicitud.';
            }
            if (payload.message) {
                return payload.message;
            }
            if (payload.errors) {
                return Object.keys(payload.errors).map(function (key) {
                    return payload.errors[key];
                }).flat().join(' ');
            }
            return 'No se pudo completar la solicitud.';
        }

        async function apiFetch(path, options) {
            var token = apiToken();
            if (!token) {
                throw new Error('Falta iniciar sesión API del asistente. Guarda el access token en localStorage.sdi_access_token o window.SDI_ACCESS_TOKEN.');
            }

            var requestOptions = options || {};
            var headers = {
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + token
            };
            if (requestOptions.body) {
                headers['Content-Type'] = 'application/json';
            }
            if (requestOptions.idempotency) {
                headers['Idempotency-Key'] = requestOptions.idempotency;
            }

            var response = await fetch(apiBaseUrl() + path, {
                method: requestOptions.method || 'GET',
                headers: headers,
                body: requestOptions.body ? JSON.stringify(requestOptions.body) : undefined
            });
            var payload = null;
            try {
                payload = await response.json();
            } catch (error) {
                payload = null;
            }
            if (!response.ok) {
                throw new Error(extractError(payload));
            }
            return payload && payload.data ? payload.data : payload;
        }

        function setVentaLoading(isLoading) {
            var button = byId('sdi_btn_vender_bono');
            if (!button) {
                return;
            }
            button.disabled = isLoading;
            button.textContent = isLoading ? 'Generando bono...' : 'Vender bono y generar QR';
        }

        function showResult(show) {
            var result = byId('sdi_bono_resultado');
            if (result) {
                result.classList.toggle('d-none', !show);
            }
        }

        function showMessage(message, type) {
            var box = byId('sdi_bono_mensaje');
            if (!box) {
                return;
            }
            box.className = 'alert alert-' + (type || 'secondary') + ' py-2 mt-3 mb-0';
            box.textContent = message;
        }

        function copyIfEmpty(targetId, sourceId) {
            if (!readValue(targetId) && readValue(sourceId)) {
                writeValue(targetId, readValue(sourceId));
            }
        }

        function syncAgendaData() {
            copyIfEmpty('rut', 'bono_paciente_rut');
            copyIfEmpty('nombre', 'bono_paciente_nombre');
            copyIfEmpty('venta_client_id', 'bono_id_paciente');
            copyIfEmpty('venta_provider_id', 'bono_id_profesional');
            copyIfEmpty('venta_servicio_id', 'bono_id_tipo_bono');
        }

        async function getCatalog() {
            if (catalogCache) {
                return catalogCache;
            }
            catalogCache = await apiFetch('/catalog');
            return catalogCache;
        }

        async function resolveProviderId() {
            var directId = numericId(firstValue(['venta_provider_id', 'bono_id_profesional']));
            if (directId) {
                return directId;
            }

            var professionalName = firstValue(['bono_profesional_nombre']);
            if (!professionalName) {
                return null;
            }

            var catalog = await getCatalog();
            var normalized = professionalName.toLowerCase();
            var providers = catalog.providers || [];
            var provider = providers.find(function (item) {
                var name = String(item.name || '').toLowerCase();
                return name === normalized || name.indexOf(normalized) >= 0 || normalized.indexOf(name) >= 0;
            });
            if (provider && provider.id) {
                writeValue('venta_provider_id', provider.id);
                return provider.id;
            }
            return null;
        }

        async function resolveServiceId() {
            var directId = numericId(firstValue(['venta_servicio_id', 'bono_id_tipo_bono'])) ||
                numericId(window.SDI_DEFAULT_SERVICE_ID);
            if (directId) {
                return directId;
            }

            var catalog = await getCatalog();
            var services = catalog.services || [];
            var price = numericId(firstValue(['valor_consulta', 'bono_valor_consulta']));
            var service = price ? services.find(function (item) {
                return Number(item.price) === Number(price);
            }) : null;
            service = service || services[0];
            if (!service || !service.id) {
                return null;
            }
            writeValue('venta_servicio_id', service.id);
            if (!readValue('valor_consulta') && service.price) {
                writeValue('valor_consulta', service.price);
            }
            return service.id;
        }

        async function resolveClientId() {
            var directId = numericId(firstValue(['venta_client_id', 'bono_id_paciente']));
            if (directId) {
                return directId;
            }

            var rut = firstValue(['rut', 'bono_paciente_rut']);
            var name = firstValue(['nombre', 'bono_paciente_nombre']);
            if (!rut || !name) {
                throw new Error('Completa Rut y Nombre del paciente antes de vender el bono.');
            }

            var payload = {
                rut: rut,
                name: name,
                email: readValue('venta_email_paciente') || null,
                phone: readValue('venta_telefono_paciente') || null
            };
            var client = await apiFetch('/clients/resolve', {
                method: 'POST',
                idempotency: idempotencyKey('assistant-client'),
                body: payload
            });
            writeValue('venta_client_id', client.id);
            return client.id;
        }

        function paymentMethod() {
            return window.SDI_PAYMENT_METHOD || 'online';
        }

        function geolocationPayload() {
            return new Promise(function (resolve) {
                if (!navigator.geolocation) {
                    resolve({});
                    return;
                }
                navigator.geolocation.getCurrentPosition(function (position) {
                    resolve({
                        emission_latitude: position.coords.latitude,
                        emission_longitude: position.coords.longitude
                    });
                }, function () {
                    resolve({});
                }, {
                    enableHighAccuracy: true,
                    timeout: 2500,
                    maximumAge: 60000
                });
            });
        }

        window.sdiVenderBonoAsistente = async function () {
            syncAgendaData();
            setVentaLoading(true);
            showMessage('Preparando venta segura...', 'secondary');

            try {
                var providerId = await resolveProviderId();
                var relationConfirmed = byId('sdi_relacion_profesional') ? byId('sdi_relacion_profesional').checked : false;
                if (!relationConfirmed || !providerId) {
                    throw new Error('Debe existir relación paciente-profesional antes de vender y asignar el bono.');
                }

                var clientId = await resolveClientId();
                var serviceId = await resolveServiceId();
                if (!serviceId) {
                    throw new Error('No se encontró un servicio activo para emitir el bono.');
                }

                var location = await geolocationPayload();
                var patientName = firstValue(['nombre', 'bono_paciente_nombre']);
                var phone = readValue('venta_telefono_paciente') || readValue('bono_paciente_telefono');
                var sale = await apiFetch('/vouchers', {
                    method: 'POST',
                    idempotency: idempotencyKey('assistant-voucher'),
                    body: Object.assign({
                        client_id: clientId,
                        provider_id: providerId,
                        service_id: serviceId,
                        patient_name: patientName,
                        purchaser_name: patientName,
                        purchaser_phone: phone || null,
                        office_number: window.SDI_OFFICE_NUMBER || readValue('bono_hora_medica') || null,
                        payment_method: paymentMethod(),
                        cliente_authorization_token: readValue('sdi_cliente_authorization_token') || null
                    }, location)
                });

                if (sale.requires_client_authorization || sale.authorization) {
                    var authorization = sale.authorization || sale.cliente_authorization || {};
                    if (authorization.token) {
                        writeValue('sdi_cliente_authorization_token', authorization.token);
                    }
                    showResult(true);
                    showMessage('Compra pendiente: el beneficiario debe aprobar en su app. Cuando apruebe, presione nuevamente "Vender bono y generar QR". Token: ' + (authorization.token || 'pendiente'), 'warning');
                    return;
                }

                ventaState.voucherId = sale.voucher.id;
                ventaState.voucherCode = sale.voucher.code;
                ventaState.qrToken = sale.qr_token;
                writeValue('folio', sale.voucher.code);
                writeValue('bono_numero', sale.voucher.code);
                writeText('sdi_bono_codigo', sale.voucher.code);
                writeText('sdi_bono_estado', 'Estado: ' + sale.voucher.status + '. Asignado al profesional relacionado.');
                writeText('sdi_bono_qr_token', sale.qr_token);
                if (byId('sdi_bono_qr_img')) {
                    byId('sdi_bono_qr_img').src = sale.qr_image;
                }
                showResult(true);
                showMessage('Bono generado y guardado en la carpeta del profesional. Si el destino está seleccionado, intento la entrega segura ahora.', 'success');

                var destination = readValue('sdi_bono_destino') || 'provider_email';
                if (destination !== 'manual') {
                    await window.sdiEntregarBonoModal(destination, true);
                }
            } catch (error) {
                showResult(true);
                showMessage(error.message || 'No se pudo vender el bono.', 'danger');
            } finally {
                setVentaLoading(false);
            }
        };

        window.sdiEntregarBonoModal = async function (channel, automatic) {
            if (!ventaState.voucherId || !ventaState.qrToken) {
                showMessage('Primero debes vender/generar el bono para tener un QR disponible.', 'warning');
                return;
            }

            try {
                var result = await apiFetch('/vouchers/' + encodeURIComponent(ventaState.voucherId) + '/deliver', {
                    method: 'POST',
                    idempotency: idempotencyKey('assistant-delivery'),
                    body: {
                        channel: channel,
                        qr_token: ventaState.qrToken
                    }
                });
                if (result.action_url) {
                    window.open(result.action_url, '_blank', 'noopener');
                }
                showMessage('Entrega preparada: ' + (result.status || 'lista') + '.', 'success');
            } catch (error) {
                var prefix = automatic ? 'Bono generado y asignado al profesional. ' : '';
                showMessage(prefix + (error.message || 'La entrega aún no está disponible.'), 'warning');
            }
        };

        window.sdiCopiarQrModal = async function () {
            if (!ventaState.qrToken) {
                showMessage('No hay QR para copiar todavía.', 'warning');
                return;
            }
            try {
                await navigator.clipboard.writeText(ventaState.qrToken);
                showMessage('Código QR copiado al portapapeles.', 'success');
            } catch (error) {
                showMessage('No pude copiar automáticamente. Selecciona el código y cópialo manualmente.', 'warning');
            }
        };

        window.sdiRecepcionarBonoVendido = function () {
            if (typeof window.recepcion_pago === 'function') {
                window.recepcion_pago();
                return;
            }
            showMessage('Bono vendido y asignado. La recepción clínica queda pendiente del flujo de agenda.', 'info');
        };

        function setQrReceptionLoading(isLoading) {
            var button = byId('sdi_btn_recibir_qr_paciente');
            if (!button) {
                return;
            }
            button.disabled = isLoading;
            button.textContent = isLoading ? 'Validando QR...' : 'Validar QR y pasar a sala de espera';
        }

        function showQrReceptionResult(show) {
            var result = byId('sdi_qr_recepcion_resultado');
            if (result) {
                result.classList.toggle('d-none', !show);
            }
        }

        function showQrReceptionMessage(message, type) {
            var box = byId('sdi_qr_recepcion_mensaje');
            if (!box) {
                return;
            }
            box.className = 'alert alert-' + (type || 'secondary') + ' py-2 mb-0';
            box.textContent = message;
        }

        window.sdiUsarQrEnviado = function (button) {
            if (!button) {
                return;
            }
            writeValue('qr_token_paciente', button.getAttribute('data-qr-token') || '');
            writeValue('qr_paciente_rut', button.getAttribute('data-cliente-rut') || '');
            writeValue('qr_profesional_nombre', button.getAttribute('data-profesional') || readValue('bono_profesional_nombre'));
            writeText('sdi_qr_recepcion_detalle', 'QR cargado desde el envio del beneficiario. Valide para pasar a sala de espera.');
            showQrReceptionResult(true);
            showQrReceptionMessage('QR cargado. Presione "Validar QR y pasar a sala de espera".', 'secondary');
            var qrInput = byId('qr_token_paciente');
            if (qrInput) {
                qrInput.focus();
            }
        };

        function extractQrToken(rawValue) {
            var value = String(rawValue || '').trim();
            var matches = value.match(/[A-Za-z0-9_-]{40,}/g);
            if (!matches || !matches.length) {
                return value;
            }
            return matches.sort(function (a, b) {
                return b.length - a.length;
            })[0];
        }

        function syncQrReceptionData() {
            copyIfEmpty('qr_paciente_rut', 'bono_paciente_rut');
            if (!readValue('qr_profesional_nombre')) {
                writeValue('qr_profesional_nombre', readValue('bono_profesional_nombre'));
            }
            if (!readValue('qr_agenda_referencia')) {
                writeValue('qr_agenda_referencia', readValue('bono_hora_medica'));
            }
        }

        window.sdiRecibirQrPaciente = async function () {
            syncAgendaData();
            syncQrReceptionData();
            setQrReceptionLoading(true);
            showQrReceptionResult(true);
            showQrReceptionMessage('Validando relación paciente-profesional y estado activo del QR...', 'secondary');

            try {
                var qrToken = extractQrToken(readValue('qr_token_paciente'));
                if (!qrToken || qrToken.length < 40) {
                    throw new Error('Pega o escanea un QR válido del paciente.');
                }

                var csrf = document.querySelector('meta[name="csrf-token"]');
                var response = await fetch(window.SDI_RECEIVE_QR_URL, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : ''
                    },
                    body: JSON.stringify({
                        qr_token: qrToken,
                        cliente_rut: readValue('qr_paciente_rut') || null,
                        canal_recepcion: readValue('bono_canal_recepcion_qr') || 'lector_recepcion'
                    })
                });
                var payload = await response.json();
                if (!response.ok || !payload.ok) {
                    throw new Error(payload.message || 'No se pudo validar el QR.');
                }
                var received = payload.data;

                writeValue('folio', received.code);
                writeValue('bono_numero', received.code);
                writeValue('venta_provider_id', received.professional_id);
                writeValue('bono_id_profesional', received.professional_id);
                writeValue('qr_profesional_nombre', received.professional_name);
                writeValue('qr_paciente_rut', received.patient_rut);
                writeValue('qr_agenda_referencia', received.medichile_appointment_id);
                writeText('sdi_qr_recepcion_detalle', 'Bono ' + received.code + ' · Profesional: ' + received.professional_name + ' · Hora Medichile #' + received.medichile_appointment_id + ' · Agenda: ' + received.agenda_status);
                showQrReceptionMessage(received.message || 'QR recibido correctamente.', 'success');

                if (typeof window.recepcion_pago === 'function') {
                    window.recepcion_pago();
                }
            } catch (error) {
                showQrReceptionMessage(error.message || 'No se pudo recibir el QR del paciente.', 'danger');
            } finally {
                setQrReceptionLoading(false);
            }
        };

        window.sdiResetVentaBono = function () {
            ventaState.voucherId = null;
            ventaState.voucherCode = null;
            ventaState.qrToken = null;
            writeText('sdi_bono_codigo', '---');
            writeText('sdi_bono_estado', 'QR listo en este modal.');
            writeText('sdi_bono_qr_token', '');
            if (byId('sdi_bono_qr_img')) {
                byId('sdi_bono_qr_img').removeAttribute('src');
            }
            showResult(false);
        };

        document.addEventListener('click', function (event) {
            if (event.target && event.target.id === 'pills-venta-tab') {
                setTimeout(syncAgendaData, 50);
            }
            if (event.target && event.target.id === 'pills-recibir-qr-tab') {
                setTimeout(function () {
                    syncAgendaData();
                    syncQrReceptionData();
                    var qrInput = byId('qr_token_paciente');
                    if (qrInput) {
                        qrInput.focus();
                    }
                }, 50);
            }
        });
    }());
</script>
