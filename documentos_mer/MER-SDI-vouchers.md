# MER - SDI Salud Digital Integrada

Proyecto: `sistema de voucher / vouchers-unificado`  
Fecha de referencia: 30-06-2026

Este documento resume el circuito funcional del sistema de bonos/vouchers SDI y deja el modelo entidad-relación principal para implementación, revisión y auditoría.

Archivos generados en esta misma carpeta:

- `MER-SDI-vouchers.mmd`: MER editable en Mermaid.
- `MER-SDI-vouchers.svg`: imagen SVG del MER funcional por módulos.
- `MER-SDI-vouchers.md`: este documento explicativo.

## Circuito cubierto

El circuito queda modelado desde la validación previa hasta la liquidación:

1. **Base externa / seguridad previa**
   - `voucher_base_usuarios`
   - `voucher_base_dependientes`
   - `voucher_base_profesionales`
   - `voucher_base_laboratorios`
   - `voucher_base_servicios`
   - `voucher_base_relaciones`
   - `voucher_preconsultas`
   - `voucher_preconsulta_auditorias`

   Esta capa valida si el usuario/dependiente tiene relación vigente con un profesional, laboratorio o servicio antes de generar el bono. Si existen contradicciones, intentos fallidos, falta de relación o rechazo, la consulta se deriva a auditoría con motivo y evidencia.

2. **Autorización del cliente**
   - `clientes`
   - `cliente_dispositivos`
   - `cliente_autorizaciones`

   El cliente autoriza compras y acciones sensibles desde su app/teléfono. El token es temporal, de corta duración, y queda amarrado al dispositivo registrado. Para administrador y auditor se mantiene doble factor tipo Google Authenticator.

3. **Canales de emisión**
   - App del cliente / beneficiario.
   - Tótem de atención.
   - Asistente / secretaria mediante API.
   - Agenda web.

   Todos los canales convergen en `vouchers`, pero la compra debe pasar por preconsulta y/o autorización según corresponda.

4. **Bono, QR y entrega**
   - `vouchers`
   - `voucher_delivery_requests`
   - `voucher_pagos`
   - `cliente_saldos`

   El QR se representa por token seguro y hash. La entrega queda registrada por canal: WhatsApp, email, profesional, centro médico o visualización en modal.

5. **Recepción en atención**
   - `voucher_agendas`
   - `voucher_atenciones`

   El flujo permite recibir bono físico, vender bono generando QR o recibir QR enviado por paciente. Al validar el pago/QR, la agenda pasa a estado de paciente con pago y esperando atención.

6. **Tótem administrado centralmente**
   - `totems`
   - `totem_sesiones`
   - `totem_ventas`
   - `totem_venta_detalles`
   - `totem_logs`

   La activación del tótem queda bajo administración central. El tótem reporta geolocalización, último ping y alertas de mal funcionamiento.

7. **Auditoría y alertas**
   - `voucher_alertas`
   - `auditor_notificaciones`
   - `voucher_auditorias`
   - `login_auditorias`
   - `security_logs`
   - `ip_autorizadas`

   Se consideran alertas por:

   - más de 3 bonos por semana por paciente;
   - alerta amarilla para profesionales/laboratorios con más de 80 bonos por semana, más de 3 bonos semanales por paciente o una liquidación objetada;
   - alerta roja con más de 120 bonos por semana, más de 4 por semana por paciente o dos liquidaciones objetadas;
   - mal funcionamiento de tótem con número, geolocalización y evento.

8. **Cobro, rendición y liquidación**
   - `voucher_cobros`
   - `voucher_rendiciones`
   - `voucher_liquidaciones`
   - `pago_autorizaciones`

   Las liquidaciones quedan separadas para profesionales e instituciones. Las alertas amarillas y rojas pasan a contraloría/auditoría antes de autorizar pago.

## Medidas de seguridad representadas

- Tokens temporales para QR, autorización y sesiones.
- Hashes para RUT, IMEI, códigos sensibles y validaciones.
- HMAC/SHA-256 como escollo adicional para integridad y comparación segura.
- Doble factor Google Authenticator para administrador y auditor.
- Autorización por app/teléfono para cliente/beneficiario.
- Logs de seguridad, login, IP autorizada y auditoría de acciones.
- Separación de canales: tótem, app cliente, asistente, agenda web y APIs.
- Derivación a auditoría con motivo, contradicciones e intentos fallidos.

## MER editable

El archivo Mermaid editable está en:

`MER-SDI-vouchers.mmd`

Si se usa un visor compatible con Mermaid, se puede pegar directamente el contenido para generar o modificar el diagrama.

