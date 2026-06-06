# Architecture Decision Records (ADRs)

---

## ADR-001: Laravel Passport over Sanctum

**Status:** Accepted

Passport implements full OAuth2 (RFC 6749) with access tokens, refresh tokens and Password Grant — transferable knowledge and suitable for decoupled frontends. Sanctum's cookie-based SPA auth ties the frontend to the same domain.

**Caveats discovered in Passport 12:**
- Password Grant is disabled by default → requires `Passport::enablePasswordGrant()` in `AppServiceProvider`
- `Passport::hashClientSecrets()` accepts no arguments and **always enables** hashing — calling it with `false` is a no-op that still enables hashing
- `/oauth/token` requires `application/x-www-form-urlencoded`, not JSON

---

## ADR-002: Next.js with Hybrid Rendering over Plain React SPA

**Status:** Accepted

- Public product pages → **SSR/ISR**: crawlable by search engines, fast FCP
- Admin panel / cart → **Client-side**: interactive, no SEO value
- Config file must be `next.config.mjs` (not `.ts`) for Next.js 14.2 compatibility

---

## ADR-003: Mailpit over Mailhog

**Status:** Accepted

Mailhog is archived (unmaintained since 2020). Mailpit is a drop-in replacement: smaller image (~15 MB), REST API, active development, optional TLS.

---

## ADR-004: Redis as Queue Broker over RabbitMQ

**Status:** Accepted

Redis serves double duty as queue driver and application cache. Sufficient throughput for typical ecommerce scale with zero extra operational complexity. Failed jobs persist in MySQL `failed_jobs` for inspection.

---

## ADR-005: Modular Monolith with DDD over Microservices

**Status:** Accepted

Three bounded contexts (`UserManagement`, `ProductCatalog`, `Ordering`) with explicit Domain / Application / Infrastructure layers provide clear seams for future extraction without the operational overhead of microservices at this scale.

---

## ADR-006: Spatie Permission Tables in Repo (no vendor:publish)

**Status:** Accepted

**Context:** `vendor:publish` during container startup is fragile — it runs after `migrate`, creating a race condition where the Spatie tables don't exist yet when seeders run.

**Decision:** Include the Spatie permission migration directly in `database/migrations/2024_01_01_000007_create_permission_tables.php`.

**Consequences:**
- No `vendor:publish` step needed in entrypoint
- Migration is version-controlled and always present
- Must keep in sync manually if Spatie changes its schema (rare)

---

## ADR-007: Spatie Permission Guard set to `api`

**Status:** Accepted

**Context:** Roles created with `guard_name = 'api'` but `assignRole('admin')` defaulted to the `web` guard, throwing `RoleDoesNotExist`.

**Decision:** Set `'guard_name' => 'api'` in `config/permission.php` and use `Role::where('name','admin')->where('guard_name','api')->first()` in seeders.

**Consequences:**
- All API routes using `auth:api` + `role:admin` middleware work correctly
- Roles must always be queried/assigned with the `api` guard context

---

## ADR-008: Spatie Cache Driver set to `array`

**Status:** Accepted

**Context:** The default Spatie cache store `'default'` resolves to the database driver, which requires `APP_KEY` to be bootstrapped. During `migrate`, the app key may not yet be set, causing `Cache store [default] is not defined`.

**Decision:** Set `'store' => 'array'` in `config/permission.php`.

**Consequences:** Permission cache lives in memory per request (not persisted across requests). Acceptable for development; in production consider switching to `redis`.

---

## ADR-009: Sanitización centralizada de errores de API

**Status:** Accepted

**Contexto:** Sin manejo explícito, Laravel puede exponer stack traces, rutas de archivo, queries SQL o mensajes internos en los errores JSON — especialmente con `APP_DEBUG=true`.

**Decisión:** Dos capas de sanitización:

**Backend — `ApiExceptionHandler`:**
- Captura todas las excepciones de rutas `/api/*` y `/oauth/*`
- Retorna siempre `{ error: 'ERROR_CODE', message: '...', errors?: {} }`
- En producción (`APP_DEBUG=false`), el campo `detail` es null
- El `error` es un código de máquina (ej. `VALIDATION_ERROR`, `UNAUTHENTICATED`) — nunca un mensaje técnico

**Frontend — `lib/errors.ts`:**
- `resolveErrorMessage(err, locale)` — mapea códigos de error a mensajes de UI seguros y traducidos
- `getValidationErrors(err)` — extrae errores de campo de validación (seguros por diseño)
- Los mensajes de backend nunca se muestran directamente al usuario

**Locale de errores:** El header `X-App-Locale` se envía en cada request. El middleware `SetLocaleFromRequest` configura `app()->setLocale()` para que los mensajes de validación de Laravel salgan en el idioma correcto.

**Consecuencias:**
- Los errores 500 nunca exponen detalles técnicos en producción
- El frontend es independiente del idioma de los mensajes del backend
- Agregar un nuevo código de error requiere actualizar `ERROR_CODE_MAP` en `lib/errors.ts`

---

## ADR-010: i18n sin librería externa — LocaleContext + mensajes JSON  {#adr-010}

**Status:** Accepted

**Context:** Se requiere soporte multiidioma (EN/ES) seleccionable por el usuario.

**Decisión:** Implementación propia sin `next-intl` ni `react-i18next`:
- `messages/en.json` y `messages/es.json` — strings organizados por sección
- `LocaleContext` — estado del idioma, función `t(key, params)`, cookie de persistencia (1 año)
- `lib/getServerT.ts` — helper para Server Components que lee la cookie del request

**Por qué no next-intl:** Requiere reestructurar `app/` bajo `app/[locale]/` (invasivo). La implementación propia es suficiente y más simple de mantener.

**Patrón Server/Client para texto traducible:**
Los Server Components no pueden usar `useLocale()`. El patrón correcto es:
- Datos (productos, usuarios) → fetched en Server Component → pasan como props
- Texto de UI → Client Components (`HomeHero`, `SectionTitle`) que usan `useLocale()`
- Esto permite que el texto cambie reactivamente sin recargar la página

---

## ADR-010: i18n sin librería externa — LocaleContext + mensajes JSON

**Status:** Accepted — ver sección anterior.

---

## ADR-011: `docker compose up --force-recreate` para cambios de env vars

**Status:** Accepted (operational practice)

`docker compose restart` reinicia el proceso dentro del contenedor existente pero **no** recarga los valores de `environment:` del `docker-compose.yml`. Para aplicar cambios de variables de entorno usar siempre:

```bash
docker compose up -d --force-recreate <servicio>
```

---

## ADR-012: Hardening de seguridad post-auditoría

**Status:** Accepted

Tras la auditoría de seguridad (ver `docs/security-audit.md`), se aplicaron 13 de 15 hallazgos:

| # | Cambio | Archivo |
|---|--------|---------|
| C-01 | Secretos a `.env` raíz con `${VAR}` en docker-compose | `docker-compose.yml`, `.env.example` |
| C-02 | PHP-FPM como `www-data`, no root | `docker/php/Dockerfile` |
| C-03 | `oauth-private.key` en `.gitignore` | `.gitignore` |
| A-01 | `SameSite=Lax` documentado (Strict rompe OAuth flows) | `frontend/app/api/auth/login/route.ts` |
| A-02 | Rate limiting doble capa (Nginx + Laravel throttle) | `nginx/default.conf`, `routes/api.php` |
| A-03 | CSP, Referrer-Policy, Permissions-Policy, server_tokens off | `nginx/default.conf` |
| A-04 | MySQL/Redis puertos comentados, Mailpit en 127.0.0.1 | `docker-compose.yml` |
| M-01 | Migraciones Passport duplicadas eliminadas | `database/migrations/` |
| M-02 | Queue job solo serializa ID de usuario | `SendWelcomeEmailListener.php` |
| M-03 | `APP_DEBUG=false` en worker de colas | `docker-compose.yml` |
| M-04 | `oauth-private.key` ignorado | `.gitignore` |
| B-02 | `X-Frame-Options: DENY` | `nginx/default.conf` |
| B-04 | `open_basedir` vía fastcgi_param | `nginx/default.conf` |

**Pendientes para producción:** SESSION_ENCRYPT=true, LOG_LEVEL=error, TLS+HSTS, Redis password, remover Mailpit.
