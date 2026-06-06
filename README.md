# Ecommerce Boilerplate

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php)](backend/composer.json)
[![Next.js](https://img.shields.io/badge/Next.js-14-black?logo=next.js)](frontend/package.json)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker)](docker-compose.yml)

A production-ready, full-stack ecommerce boilerplate demonstrating Domain-Driven Design (DDD), CQRS-lite, and Clean Architecture patterns.

**Stack:** Laravel 11 · Next.js 14 · MySQL 8 · Redis · Docker · Laravel Passport · Spatie Permissions

---

## Technologies Used

| Layer | Technology | Purpose |
|-------|-----------|---------|
| Backend API | Laravel 11 (PHP 8.3) | REST API, business logic, queues |
| Frontend | Next.js 14 (App Router) | SSR/SSG public pages, CSR admin/cart |
| Database | MySQL 8 | Primary data store |
| Cache/Queue | Redis 7 | Queue driver for async jobs |
| Auth | Laravel Passport 12 | OAuth2 Password Grant tokens |
| Permissions | spatie/laravel-permission | Role-based access control (guard: `api`) |
| Email Dev | Mailpit | Local SMTP server with web UI |
| Containers | Docker + Docker Compose | Full dev environment |

---

## Prerequisites

- **Docker** 24+ and **Docker Compose** v2+
- That's it — no PHP, Node, or MySQL required locally

---

## Seguridad

Ver [docs/security-audit.md](docs/security-audit.md) para el informe completo (15 hallazgos, todos corregidos).

**Controles implementados:**

| Control | Implementación |
|---|---|
| Secretos | `.env` raíz ignorado por git — nunca en `docker-compose.yml` |
| PHP-FPM | Corre como `www-data` (no root) |
| Rate limiting | Nginx 10 req/min (auth) + Laravel `throttle:auth` |
| HTTP headers | CSP, X-Frame-Options DENY, Referrer-Policy, Permissions-Policy |
| Puertos | MySQL y Redis **no expuestos** al host |
| Cookies | `httpOnly`, `SameSite=Lax`, `Secure` en producción |
| OAuth | `client_secret` nunca llega al navegador (solo API Route server-side) |
| Queue | Jobs solo serializan ID de usuario, no el modelo completo |
| Errores | `ApiExceptionHandler` — sin stack traces ni SQL en respuestas |
| IDOR | CartController verifica `cart->user_id === request->user()->id` |

---

## Quick Start

```bash
# 1. Clone the repository
git clone <repo-url>
cd ecommerce-boilerplate

# 2. Create the root .env from the example and fill in values
cp .env.example .env
# Edit .env: set APP_KEY, DB_PASSWORD, MYSQL_ROOT_PASSWORD, NEXTAUTH_SECRET

# 3. Start all services (first run ~3-5 min while images build)
docker compose up -d

# 4. Watch PHP setup complete (migrations + seeders)
docker compose logs -f php
# Wait for: "==> Setup complete. Starting PHP-FPM..."

# 5. Create the Passport Password Grant client
docker compose exec php php -r "
\$client = new \Laravel\Passport\Client;
\$client->name = 'Next.js App';
\$secret = \Illuminate\Support\Str::random(40);
\$client->secret = \$secret;
\$client->redirect = 'http://localhost';
\$client->personal_access_client = false;
\$client->password_client = true;
\$client->revoked = false;
\$client->save();
echo 'PASSPORT_CLIENT_ID=' . \$client->id . PHP_EOL;
echo 'PASSPORT_CLIENT_SECRET=' . \$client->secret . PHP_EOL;
"

# 6. Add the output values to .env:
#    PASSPORT_CLIENT_ID=<id>
#    PASSPORT_CLIENT_SECRET=<secret>

# 7. Recreate the nextjs container to load the new env vars
#    NOTE: `restart` does NOT reload env vars — use force-recreate
docker compose up -d --force-recreate nextjs

# 8. Verify login works
curl -s -X POST http://localhost:3000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

---

## Access Points

| Service | URL | Description |
|---------|-----|-------------|
| Frontend | http://localhost:3000 | Next.js dev server |
| API | http://localhost/api | Laravel REST API (via Nginx) |
| Mailpit | http://localhost:8025 | Email inbox (dev SMTP) |
| MySQL | localhost:3306 | Database (user: ecommerce / pass: secret) |

---

## Test Credentials (from seeder)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@example.com | password |
| Buyer | buyer@example.com | password |

---

## Project Structure

```
.
├── docker/
│   ├── php/
│   │   ├── Dockerfile          # PHP 8.3-FPM + extensions (autoconf explicit for pecl)
│   │   └── entrypoint.sh       # Runs migrations, passport:install, seeders
│   ├── nginx/
│   │   └── default.conf        # /api + /oauth → PHP-FPM, rest → Next.js
│   └── nextjs/
│       └── Dockerfile          # Node 20 dev server
│
├── backend/                    # Laravel 11 application
│   ├── app/
│   │   ├── Modules/            # DDD modular monolith
│   │   │   ├── UserManagement/ # Users, auth, welcome email
│   │   │   ├── ProductCatalog/ # Product CRUD
│   │   │   ├── Ordering/       # Cart, orders, checkout
│   │   │   └── Shared/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/
│   │   │   ├── Requests/
│   │   │   └── Resources/
│   │   └── Providers/
│   ├── config/
│   │   ├── auth.php            # api guard → Passport
│   │   └── permission.php      # Spatie: guard=api, cache=array
│   ├── database/
│   │   ├── migrations/         # Includes Spatie tables (no vendor:publish needed)
│   │   └── seeders/
│   └── routes/api.php
│
├── frontend/                   # Next.js 14 application
│   ├── app/
│   │   ├── (auth)/             # Login & Register (Client Components)
│   │   ├── products/           # Public pages (SSR + ISR)
│   │   ├── admin/              # Admin panel (Client Component)
│   │   ├── cart/               # Cart (Client Component)
│   │   └── api/auth/           # Server-side Passport proxy routes
│   ├── context/AuthContext.tsx
│   ├── lib/api.ts
│   ├── middleware.ts            # Edge route protection
│   └── next.config.mjs         # .mjs required for Next.js 14.2
│
└── docs/
    ├── architecture.md
    └── decisions.md
```

---

## Internacionalización (i18n)

El sitio soporta inglés y español. El usuario selecciona el idioma con el botón 🇺🇸/🇲🇽 en el Navbar.

| Archivo | Rol |
|---|---|
| `messages/en.json` | Strings en inglés |
| `messages/es.json` | Strings en español |
| `context/LocaleContext.tsx` | Estado global, `t(key, params)`, cookie de 1 año |
| `lib/getServerT.ts` | Helper para Server Components (lee cookie del request) |
| `components/ui/LanguageSwitcher.tsx` | Botones EN/ES en el Navbar |

**Patrón Server/Client:** Los datos se obtienen server-side (SSR) pero el texto de UI vive en Client Components (`HomeHero`, `SectionTitle`) que reaccionan al contexto sin recargar la página.

---

## Key Architecture Concepts

- **Modular Monolith with DDD** — bounded contexts under `app/Modules/` with Domain / Application / Infrastructure layers
- **Repository Pattern** — interfaces in Domain, Eloquent implementations in Infrastructure
- **CQRS-lite** — Commands mutate state, Queries are read-only
- **Event-driven email** — `UserRegistered` → queued `SendWelcomeEmailListener` → `WelcomeMail` via Redis
- **Secure OAuth proxy** — Next.js API Route `/api/auth/login` calls Passport `/oauth/token` server-side (URLSearchParams format, not JSON)

See [docs/architecture.md](docs/architecture.md) for full diagrams.

---

## Important Implementation Notes

### Passport (Laravel 12+)
- Password Grant is **disabled by default** — must call `Passport::enablePasswordGrant()` in `AppServiceProvider`
- `Passport::hashClientSecrets()` takes **no arguments** and always **enables** hashing — do not call it if you want plain-text secrets
- `/oauth/token` requires `Content-Type: application/x-www-form-urlencoded`, not JSON
- Client secrets created via `passport:install` are bcrypt-hashed; create clients manually if you need plain-text secrets

### Docker env vars
- `docker compose restart` does **not** reload environment variables
- Use `docker compose up -d --force-recreate <service>` to apply env var changes

### Spatie laravel-permission
- Migrations are included directly in `database/migrations/` (no `vendor:publish` step needed)
- Guard must be `api` in `config/permission.php` to match Passport-authenticated routes
- Cache driver set to `array` to avoid bootstrap issues during migration

---

## Useful Commands

```bash
# Status
docker compose ps

# Logs
docker compose logs -f php
docker compose logs -f nextjs
docker compose logs -f laravel-queue

# Artisan
docker compose exec php php artisan <command>

# MySQL
docker compose exec mysql mysql -u ecommerce -psecret ecommerce -e "SHOW TABLES;"

# Redis
docker compose exec redis redis-cli ping

# Full reset (drops all data)
docker compose down -v && docker compose up -d

# Reload env vars in a service (restart is not enough)
docker compose up -d --force-recreate nextjs
```
