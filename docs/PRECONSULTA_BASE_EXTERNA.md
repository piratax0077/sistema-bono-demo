# Flujo previo a la generacion del bono

Antes de generar un bono, el sistema debe consultar la base previa y validar una relacion vigente:

`usuario/dependiente -> profesional/laboratorio -> servicio/nivel de bono`

Si esa relacion no existe, esta vencida, falla el doble control criptografico o esta marcada para auditoria, no se genera bono automatico.

## Tablas nuevas

- `voucher_base_usuarios`
- `voucher_base_dependientes`
- `voucher_base_profesionales`
- `voucher_base_laboratorios`
- `voucher_base_servicios`
- `voucher_base_relaciones`
- `voucher_preconsultas`
- `voucher_preconsulta_auditorias`

La tabla clave de seguridad es `voucher_base_relaciones`. Ahi debe venir la primera relacion autorizada desde la base externa.

La tabla `voucher_preconsulta_auditorias` guarda el expediente que revisa el auditor cuando algo no puede resolverse de forma automatica.

## Endpoint

`POST /api/preconsultas/validar`

Requiere token Sanctum y rol `admin`, `vendedor` o `asistente`.

Ejemplo:

```json
{
  "usuario_rut": "10211568-6",
  "dependiente_rut": null,
  "profesional_rut": "6187674-K",
  "servicio_codigo": "CONSULTA-N1",
  "geolocalizacion_lat": -33.4489,
  "geolocalizacion_lng": -70.6693
}
```

Respuesta autorizada:

```json
{
  "data": {
    "preconsulta_id": 1,
    "preconsulta_token": "token-temporal",
    "preconsulta_token_expires_at": "2026-06-29T20:15:00-04:00",
    "preconsulta_token_minutes": 15,
    "resultado": "autorizado",
    "can_generate_voucher": true
  }
}
```

Ese token dura pocos minutos y debe enviarse al endpoint de emision junto con `preconsulta_id`.

## Auditoria

Cuando el resultado es `auditoria`, el sistema registra:

- `motivo`: razon principal del bloqueo.
- `contradicciones`: diferencias detectadas entre usuario, dependiente, prestador, servicio o candados de seguridad.
- `intentos_fallidos_count`: cantidad de intentos rechazados/auditados recientes.
- `intentos_fallidos`: ultimos intentos con fecha, resultado, motivo y fingerprint seguro.
- `porques`: explicacion de negocio para que el auditor resuelva.
- `contexto`: datos seguros de apoyo, sin RUT en claro.

La vista del auditor queda en:

`GET /auditoria`

Desde ahi el auditor puede revisar el expediente de preconsulta y registrar su resolucion.

## Emision de bono

`POST /api/vouchers/emitir`

Ahora exige:

- `preconsulta_id`
- `preconsulta_token`

El token se consume al generar el bono. No se puede reutilizar.
