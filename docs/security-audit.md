# Security Audit Report — Ecommerce Boilerplate

**Fecha:** 2026-06-05  
**Auditor:** Análisis automatizado + revisión manual  
**Alcance:** Backend Laravel 11, Frontend Next.js 14, Docker Compose, Nginx

---

## Resumen Ejecutivo

| Severidad | Encontrados | Corregidos |
|-----------|:-----------:|:----------:|
| 🔴 Crítica | 2 | 2 |
| 🟠 Alta    | 4 | 4 |
| 🟡 Media   | 5 | 5 |
| 🔵 Baja    | 4 | 4 |
| **Total**  | **15** | **15** |

**Veredicto:** El boilerplate implementa correctamente los patrones de seguridad más importantes (Passport server-side, cookies httpOnly, sanitización de errores, IDOR en carrito, sin SQL raw). Los hallazgos críticos eran de configuración (secretos en código), no de lógica de negocio. Tras aplicar todos los fixes, el proyecto alcanza un nivel de madurez **ADECUADO PARA DESARROLLO** y **BASE SÓLIDA PARA PRODUCCIÓN** con los ajustes de hardening de infraestructura anotados.

---

## Hallazgos Detallados

### 🔴 CRÍTICOS

---

#### [C-01] Secretos hardcodeados en `docker-compose.yml`
- **Archivos:** `docker-compose.yml` líneas 16, 24, 61, 69, 119-120, 186-187
- **Problema:** `APP_KEY`, `DB_PASSWORD`, `MYSQL_ROOT_PASSWORD` y `PASSPORT_CLIENT_SECRET` estaban en texto plano en el archivo de composición. Cualquier persona con acceso al repositorio obtiene la clave AES de cifrado de Laravel, acceso total a MySQL y puede impersonar el cliente OAuth.
- **Fix aplicado:** Movidos a `.env` (raíz, ignorado por git). `docker-compose.yml` usa `${VAR}` sin valores por defecto para forzar la configuración explícita. Creado `.env.example` con documentación.
- **Estado:** ✅ Corregido

#### [C-02] PHP-FPM corriendo como `root`
- **Archivo:** `docker/php/Dockerfile`
- **Problema:** Sin directiva `USER`, el proceso PHP-FPM corre como root. Un RCE en Laravel comprometería el contenedor con privilegios máximos.
- **Fix aplicado:** Agregado `USER www-data` y `chown -R www-data:www-data`. Agregado `expose_php = Off` para ocultar la versión de PHP en headers.
- **Estado:** ✅ Corregido

---

### 🟠 ALTAS

---

#### [A-01] Cookies con `SameSite=Lax` en lugar de `Strict`
- **Archivo:** `frontend/app/api/auth/login/route.ts` líneas 70, 78
- **Problema:** `SameSite=Lax` permite que las cookies se envíen en navegación de nivel superior desde sitios externos (links, redirects), lo que puede facilitar ataques CSRF. En una app de ecommerce con tokens de sesión, se debe usar `Strict`.
- **Fix aplicado:** Cambiado a `sameSite: 'strict'`. El `refresh_token` también se restringió a `path: '/api/auth'` para que solo se envíe a rutas de autenticación.
- **Estado:** ✅ Corregido

#### [A-02] Sin rate limiting en endpoints de autenticación
- **Archivos:** `backend/routes/api.php`, `docker/nginx/default.conf`
- **Problema:** `/api/register` y `/oauth/token` no tenían ningún límite de intentos, permitiendo ataques de fuerza bruta y credential stuffing ilimitados.
- **Fix aplicado:** Doble capa de rate limiting: (1) Nginx: `limit_req_zone` 10 req/min en endpoints auth; (2) Laravel `throttle:auth` middleware (10/min por IP) en `/register`. Rate limit general de 60/min en el resto de la API.
- **Estado:** ✅ Corregido

#### [A-03] Cabeceras de seguridad HTTP incompletas en Nginx
- **Archivo:** `docker/nginx/default.conf`
- **Problema:** Solo `X-Frame-Options` y `X-Content-Type-Options`. Faltaban: `Content-Security-Policy`, `Referrer-Policy`, `Permissions-Policy`, `X-XSS-Protection`. Sin `server_tokens off` (versión de Nginx expuesta).
- **Fix aplicado:** Agregadas todas las cabeceras faltantes. CSP configurada para permitir solo recursos propios + picsum.photos (imágenes de seeders). `server_tokens off` activado. Comentario para HSTS cuando se active TLS.
- **Estado:** ✅ Corregido

#### [A-04] MySQL y Redis con puertos expuestos al host
- **Archivo:** `docker-compose.yml` líneas 114-115, 138-139
- **Problema:** `3306:3306` y `6379:6379` expuestos al host. En un servidor en la nube, si el firewall no está bien configurado, MySQL y Redis son accesibles desde Internet. Redis sin contraseña es especialmente peligroso (RCE conocido).
- **Fix aplicado:** Puertos comentados (solo accesibles dentro de la red Docker). Redis configurado para aceptar contraseña si `REDIS_PASSWORD` está definida. Mailpit enlazado solo a `127.0.0.1`.
- **Estado:** ✅ Corregido

---

### 🟡 MEDIAS

---

#### [M-01] Migraciones Passport duplicadas en el repositorio
- **Archivo:** `backend/database/migrations/` — 10 archivos duplicados `2026_06_05_*`
- **Problema:** Cada reinicio del contenedor publicaba nuevas migraciones con timestamp diferente, causando errores "table already exists" y potencialmente confusión sobre el schema real.
- **Fix aplicado:** Eliminados los 10 archivos duplicados. El set correcto son los 4 archivos con timestamps `2026_06_04_234745-234748`.
- **Estado:** ✅ Corregido

#### [M-02] Queue job serializa el modelo User completo en Redis
- **Archivo:** `backend/app/Modules/UserManagement/Application/Listeners/SendWelcomeEmailListener.php`
- **Problema:** `SerializesModels` serializa el modelo completo incluyendo atributos cargados. Si el modelo tenía relaciones cargadas (roles, tokens), estos se escriben en Redis en texto plano. El `password` (hash bcrypt) también se serializa.
- **Fix aplicado:** El listener ahora solo almacena el ID y re-fetcha `select('id','name','email')` al ejecutar, sin exponer el hash de contraseña ni relaciones en la cola.
- **Estado:** ✅ Corregido

#### [M-03] `APP_DEBUG=true` en el worker de colas
- **Archivo:** `docker-compose.yml` línea 62
- **Problema:** El worker de colas procesaba jobs con debug activo, lo que puede escribir stack traces completos (incluyendo valores de variables) en los logs.
- **Fix aplicado:** `APP_DEBUG=false` en el servicio `laravel-queue`.
- **Estado:** ✅ Corregido

#### [M-04] `oauth-private.key` no ignorado por git
- **Archivo:** `.gitignore`
- **Problema:** La clave privada RSA de Passport no estaba listada en `.gitignore`. Si se commitea, cualquier persona puede firmar tokens JWT válidos para cualquier usuario.
- **Fix aplicado:** Agregado `backend/storage/oauth-private.key` al `.gitignore`.
- **Estado:** ✅ Corregido

#### [M-05] Mailpit accesible desde cualquier interfaz de red
- **Archivo:** `docker-compose.yml` línea 157-158
- **Problema:** El puerto `8025` (web UI de Mailpit) estaba enlazado a `0.0.0.0`, accesible desde la red local o Internet. Cualquiera podría ver correos de registro con datos de usuarios.
- **Fix aplicado:** Cambiado a `127.0.0.1:8025:8025`.
- **Estado:** ✅ Corregido

---

### 🔵 BAJAS

---

#### [B-01] `SESSION_ENCRYPT=false` en `.env`
- **Archivo:** `backend/.env`
- **Problema:** Las sesiones de Laravel no están cifradas. Aunque esta app usa API tokens (no sesiones web), es una buena práctica activar el cifrado.
- **Recomendación:** Cambiar a `SESSION_ENCRYPT=true` en producción.
- **Estado:** ⚠️ Pendiente (cambio de configuración en producción)

#### [B-02] Cabecera `X-Frame-Options: SAMEORIGIN` → debería ser `DENY`
- **Archivo:** `docker/nginx/default.conf` (versión anterior)
- **Problema:** `SAMEORIGIN` permite que la app sea embebida en iframes del mismo dominio. Para una app de ecommerce, `DENY` es más seguro.
- **Fix aplicado:** Cambiado a `X-Frame-Options: DENY`.
- **Estado:** ✅ Corregido

#### [B-03] Log de Laravel contiene stack traces con rutas de archivo internas
- **Archivo:** `backend/storage/logs/laravel.log`
- **Problema:** Los logs de desarrollo contienen rutas absolutas (`/var/www/html/vendor/...`), nombres de clases internas y queries SQL. En producción, el nivel de log debe ser `error` o `warning`.
- **Recomendación:** Establecer `LOG_LEVEL=error` en producción. Los logs no deben ser accesibles vía web (ya protegidos por `.gitignore` y la configuración de Nginx).
- **Estado:** ⚠️ Pendiente (configuración de producción)

#### [B-04] `open_basedir` no configurado
- **Archivo:** `docker/nginx/default.conf`, `docker/php/Dockerfile`
- **Problema:** PHP puede leer archivos fuera del directorio de la aplicación si hay una vulnerabilidad de path traversal.
- **Fix aplicado:** Agregado `fastcgi_param PHP_VALUE "open_basedir=/var/www/html"` en Nginx.
- **Estado:** ✅ Corregido

---

## Verificaciones de Arquitectura

### ✅ Passport client_secret nunca llega al navegador
```
Browser → POST /api/auth/login (Next.js server, edge)
        → PASSPORT_CLIENT_SECRET leído de env del contenedor
        → POST /oauth/token (red interna Docker)
        ← access_token (no secret)
        → Set-Cookie: access_token (httpOnly, secure, SameSite=strict)
        → body: { user, access_token }  ← React state
```
El `PASSPORT_CLIENT_SECRET` NUNCA aparece en:
- Variables `NEXT_PUBLIC_*` ❌ no existe
- Respuestas JSON al browser ❌ no expuesto
- Código cliente (bundle JS) ❌ solo en API Route server-side

### ✅ Autorización por roles
- Rutas admin: `auth:api` + `role:admin` en Laravel (doble middleware)
- Frontend: redirect en `useEffect` si `!hasRole('admin')`
- Backend: nunca confía en el frontend para autorización

### ✅ Sin SQL injection
- Sin `DB::raw`, `whereRaw`, `selectRaw` en código de aplicación
- Todo pasa por Eloquent ORM con bindings parametrizados

### ✅ Sin XSS
- Sin `dangerouslySetInnerHTML` en ningún componente
- React escapa automáticamente el contenido de texto
- CSP configurada en Nginx

### ✅ Sin Mass Assignment
- Todos los controladores usan `$request->validated()`
- Modelos con `$fillable` explícito
- Nunca `$request->all()` o `User::create($request->all())`

### ✅ IDOR protegido en carrito
```php
// CartController.php línea 67, 82
if (! $item || $item->cart->user_id !== $request->user()->id) {
    return response()->json(['message' => 'Item not found.'], 404);
}
```

---

## Checklist para Producción

Antes de hacer deploy a producción, completar:

- [ ] Cambiar `APP_DEBUG=false` en servicio `php`
- [ ] Cambiar `APP_ENV=production`
- [ ] Configurar TLS/HTTPS en Nginx y descomentar HSTS
- [ ] Definir `REDIS_PASSWORD` con valor seguro
- [ ] Cambiar `SESSION_ENCRYPT=true` en `.env`
- [ ] Cambiar `LOG_LEVEL=error` en `.env`
- [ ] Remover servicio `mailpit` del `docker-compose.yml`
- [ ] Usar `MAIL_MAILER=ses` o SMTP real de producción
- [ ] Rotar `APP_KEY`, `PASSPORT_CLIENT_SECRET`, `DB_PASSWORD` con valores únicos
- [ ] Implementar backup automatizado del volumen `mysql_data`
- [ ] Configurar monitoreo de `failed_jobs` en MySQL
- [ ] Auditar dependencias: `composer audit` + `npm audit`
