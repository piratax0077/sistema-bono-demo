# Sistema de Bonos Demo

Aplicación Laravel independiente para recorrer el bono desde la compra hasta el depósito simulado. No contiene sesiones, registros, claves ni datos personales del sistema original.

## Bases propias

- `sistema_bonos_demo`: usuarios, bonos, QR, agenda, atención, cobro, auditoría, rendición y depósito.
- `sistema_bonos_demo_personas`: tabla canónica `personas` y datos ficticios del paciente/profesional.
- `sistema_bonos_demo_medichile`: puente de demostración con `pacientes`, `profesionales` y `horas_medicas`.

Para conectar bases reales, conserve los nombres de conexión Laravel `personas_fast` y `medichile` y cambie sus variables en `.env`.

## Instalación en servidor

1. Apunte el dominio al directorio `public` de esta carpeta.
2. Ejecute `database/sql/crear_bases_demo.sql` como administrador MySQL y cambie la clave de ejemplo.
3. Copie `.env.demo.example` como `.env`, complete dominio y credenciales, y use HTTPS.
4. Ejecute:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=DemoServidorSeeder --force
php artisan storage:link
php artisan optimize
```

5. Dé permisos de escritura al usuario web solamente sobre `storage` y `bootstrap/cache`.
6. Abra `/demo`. La barra superior cambia entre Paciente, Asistente, Profesional, Contraloría y Administración.

## Flujo de revisión

1. Paciente: compra o abre `DEMO-PACIENTE-PRUEBA` y envía el QR.
2. Asistente: recibe el QR y deja al paciente en espera; la hora ficticia Medichile pasa a estado 4.
3. Profesional: abre la atención, registra diagnóstico, cierra y envía el QR a cobro.
4. Contraloría: revisa controles, aprueba u objeta y autoriza el pago.
5. Administración: `/demo` refleja depósito, comprobante y trazabilidad completa.

Login manual: `paciente@gmail.com`, `asistente@gmail.com`, `profesional@gmail.com`, `contralor@gmail.com`, `administrador@gmail.com`; clave `123`.

## Seguridad

El cambio de perfil es exclusivamente demostrativo. Para producción real establezca `DEMO_MODE=false`, `DEMO_USER_SWITCH_ENABLED=false`, `PAYMENT_ALLOW_DEMO=false`, active 2FA/IP, reemplace las credenciales ficticias y conecte un proveedor bancario real. El depósito incluido genera un comprobante simulado; no mueve dinero.
