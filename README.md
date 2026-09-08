# SDI — Salud Digital Integrada

Aplicación Laravel central para emisión, aceptación, agenda, validación presencial,
atención, cobro, rendición, autorización profesional, auditoría y liquidación de bonos.

## Flujo operacional

1. El paciente se identifica contra Personas y compra el bono en tótem, web, asistente o canal Isapre.
2. El sistema emite un QR firmado y registra su entrega; antes de usarlo el paciente puede invalidarlo o dejarlo vigente.
3. El profesional acepta y cierra la atención.
4. Un asistente distinto valida el cierre. Recién entonces el bono queda `validado_atencion`, habilitado para cobro.
5. El cobro crea una única obligación `pendiente_rendicion`; luego siguen rendición, liquidación, fiscalización y pago.

Cada transición financiera genera auditoría. Las consultas a Personas usan un identificador de correlación y almacenan solamente el hash del RUT.

## Integración Personas

Configure `PERSONAS_API_URL` y `PERSONAS_API_TOKEN`. El tótem y el formulario de Personas consumen `GET/POST/PUT /api/personas`; si el servicio no está disponible, el fallback local puede controlarse con `PERSONAS_API_FALLBACK_LOCAL`.

## Instalación

Requisitos recomendados: PHP 8.2 o superior, Composer 2 actualizado,
MySQL/MariaDB, Redis y Node.js LTS.

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm ci
npm run production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Configure SMTP, Redis, orígenes CORS, HTTPS, la clave de alta de dispositivos y
la firma del proveedor de pagos antes de usar producción.

## Provisionar un tótem

```bash
php artisan totem:provision TOTEM001 \
  --name="Recepción central" \
  --location="Sucursal Santiago" \
  --ip="203.0.113.10"
```

La clave se solicita de forma oculta y sólo se guarda como hash.

## Procesos permanentes

Ejecute un worker de cola y el scheduler:

```bash
php artisan queue:work --tries=3
php artisan schedule:work
```

## Controles principales

- Roles y mínimo privilegio para operaciones financieras.
- Segundo factor para administradores y auditores.
- Lista de IP opcional para perfiles críticos y obligatoria para tótems configurados.
- Tokens de tótem aleatorios, rotatorios, con hash y caducidad.
- CORS restringido, cookies cifradas, CSRF, cabeceras de seguridad y rate limiting.
- RUT cifrado con índice hash para búsquedas.
- QR firmado, OTP con caducidad, bloqueo de reutilización y transacciones con lock.
- Trazabilidad de login, emisión, atención, cobro, rendición y pago.
- Pago de tótem aceptado sólo con firma HMAC del proveedor, salvo modo demo explícito.

Revise [SEGURIDAD.md](../docs/SEGURIDAD.md) antes de publicar.
