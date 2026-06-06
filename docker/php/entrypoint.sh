#!/bin/bash
# entrypoint.sh — Fully idempotent startup script.
echo "==> [Entrypoint] Starting PHP container setup..."
cd /var/www/html

# ── 1. Create .env if missing ─────────────────────────────────────────────────
if [ ! -f .env ]; then
    echo "==> Creating .env from .env.example..."
    cp .env.example .env
fi

# ── 2. Write Docker env vars into .env ───────────────────────────────────────
set_env() {
    local key=$1 val=$2
    if grep -q "^${key}=" .env 2>/dev/null; then
        sed -i "s|^${key}=.*|${key}=${val}|" .env
    else
        echo "${key}=${val}" >> .env
    fi
}
[ -n "$APP_KEY" ]          && set_env APP_KEY          "$APP_KEY"
[ -n "$DB_HOST" ]          && set_env DB_HOST           "$DB_HOST"
[ -n "$DB_PORT" ]          && set_env DB_PORT           "$DB_PORT"
[ -n "$DB_DATABASE" ]      && set_env DB_DATABASE       "$DB_DATABASE"
[ -n "$DB_USERNAME" ]      && set_env DB_USERNAME       "$DB_USERNAME"
[ -n "$DB_PASSWORD" ]      && set_env DB_PASSWORD       "$DB_PASSWORD"
[ -n "$REDIS_HOST" ]       && set_env REDIS_HOST        "$REDIS_HOST"
[ -n "$QUEUE_CONNECTION" ] && set_env QUEUE_CONNECTION  "$QUEUE_CONNECTION"
[ -n "$MAIL_HOST" ]        && set_env MAIL_HOST         "$MAIL_HOST"
[ -n "$MAIL_PORT" ]        && set_env MAIL_PORT         "$MAIL_PORT"

# ── 3. Install Composer dependencies ──────────────────────────────────────────
echo "==> Installing Composer dependencies..."
composer install --no-interaction --optimize-autoloader

# ── 4. Generate app key only if still empty ───────────────────────────────────
if ! grep -q "^APP_KEY=base64:" .env; then
    echo "==> Generating application key..."
    php artisan key:generate --force
fi

# ── 5. Run all migrations (our files + Spatie + Passport already in repo) ─────
echo "==> Running database migrations..."
php artisan migrate --force 2>&1 || echo "==> Some migrations skipped (already applied)."

# ── 6. Install Passport OAuth clients ────────────────────────────────────────
echo "==> Installing Laravel Passport..."
php artisan passport:install --uuids --no-interaction 2>&1 || echo "==> Passport already installed."

# ── 7. Seed (idempotent via firstOrCreate) ────────────────────────────────────
echo "==> Running database seeders..."
php artisan db:seed --force

# ── 8. Clear caches ───────────────────────────────────────────────────────────
echo "==> Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "==> [Entrypoint] Setup complete. Starting PHP-FPM..."
exec "$@"
