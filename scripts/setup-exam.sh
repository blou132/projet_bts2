#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ENV_FILE="$ROOT_DIR/.env"
ENV_EXAMPLE="$ROOT_DIR/.env.example"
SQLITE_FILE="$ROOT_DIR/database/database.sqlite"

update_env_var() {
  local key="$1"
  local value="$2"

  if grep -qE "^${key}=" "$ENV_FILE"; then
    sed -i "s|^${key}=.*|${key}=${value}|" "$ENV_FILE"
  else
    echo "${key}=${value}" >>"$ENV_FILE"
  fi
}

echo "[1/5] Preparation de .env..."
if [ ! -f "$ENV_FILE" ]; then
  cp "$ENV_EXAMPLE" "$ENV_FILE"
fi

mkdir -p "$ROOT_DIR/database"
touch "$SQLITE_FILE"

update_env_var "DB_CONNECTION" "sqlite"
update_env_var "DB_DATABASE" "$SQLITE_FILE"
update_env_var "SESSION_DRIVER" "database"
update_env_var "CACHE_STORE" "database"
update_env_var "QUEUE_CONNECTION" "database"
update_env_var "ADMIN_USERNAME" "admin"
update_env_var "ADMIN_PASSWORD" "admin123"
update_env_var "ADMIN_SYSTEM_EMAIL" "admin-system@jmi56.local"
update_env_var "JMI_USERNAME" "jmi"
update_env_var "JMI_PASSWORD" "jmi123"
update_env_var "JMI_SYSTEM_EMAIL" "jmi-system@jmi56.local"
update_env_var "SITE_OWNER_FULLNAME" "JMI 56"
update_env_var "SITE_CONTACT_EMAIL" "contact@jmi56.local"
update_env_var "SITE_HOSTING_PROVIDER" "A completer (OVH / Vercel / autre)"
update_env_var "SITE_HOSTING_ADDRESS" "A completer"
update_env_var "SITE_HOSTING_PHONE" "A completer"

echo "[2/5] Installation des dependances PHP..."
if [ ! -d "$ROOT_DIR/vendor" ]; then
  composer install --no-interaction --prefer-dist
fi

echo "[3/5] Nettoyage cache Laravel..."
php "$ROOT_DIR/artisan" optimize:clear >/dev/null

echo "[4/5] Initialisation base (migrate:fresh --seed)..."
php "$ROOT_DIR/artisan" key:generate --force >/dev/null
php "$ROOT_DIR/artisan" migrate:fresh --seed --force

echo "[5/5] Termine."
echo
echo "Comptes de demonstration:"
echo "- Admin: identifiant=admin / mot de passe=admin123"
echo "- JMI (tickets/messages clients): identifiant=jmi / mot de passe=jmi123"
echo "- Utilisateur: email=exemple@gmail.com / mot de passe=123456789"
echo
echo "Lancement:"
echo "- php artisan serve"
echo "- Ouvrir http://127.0.0.1:8000"
