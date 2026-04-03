#!/usr/bin/env bash

set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
JAIL_NAME="projet-bts2-auth"
FILTER_FILE="/etc/fail2ban/filter.d/${JAIL_NAME}.conf"
JAIL_FILE="/etc/fail2ban/jail.d/${JAIL_NAME}.local"
LOG_FILE="${PROJECT_ROOT}/storage/logs/security.log"
APP_USER="${SUDO_USER:-root}"
APP_GROUP="${APP_USER}"

if [[ "${EUID}" -ne 0 ]]; then
  echo "Ce script doit etre execute en root."
  echo "Commande: sudo ./scripts/setup-fail2ban.sh"
  exit 1
fi

if ! id -u "${APP_USER}" >/dev/null 2>&1; then
  if id -u www-data >/dev/null 2>&1; then
    APP_USER="www-data"
    APP_GROUP="www-data"
  else
    APP_USER="root"
    APP_GROUP="root"
  fi
fi

echo "[1/4] Preparation du log de securite..."
mkdir -p "$(dirname "${LOG_FILE}")"
touch "${LOG_FILE}"
chown "${APP_USER}:${APP_GROUP}" "${LOG_FILE}" || true
chmod 664 "${LOG_FILE}" || true
echo "    -> logpath: ${LOG_FILE}"
echo "    -> owner: ${APP_USER}:${APP_GROUP}"

echo "[2/4] Creation du filtre Fail2Ban..."
cat > "${FILTER_FILE}" <<'FILTER'
[Definition]
failregex = ^.* AUTH_FAIL ip=<HOST>(?:\s|$).*
ignoreregex =
FILTER

echo "[3/4] Creation de la jail Fail2Ban..."
cat > "${JAIL_FILE}" <<JAIL
[${JAIL_NAME}]
enabled = true
filter = ${JAIL_NAME}
port = http,https
logpath = ${LOG_FILE}
backend = auto
maxretry = 5
findtime = 10m
bantime = 1h
# Pour permettre les tests locaux (127.0.0.1)
ignoreself = false
ignoreip =
JAIL

echo "[4/4] Reload Fail2Ban..."
fail2ban-client reload

echo
echo "Jail activee: ${JAIL_NAME}"
fail2ban-client status "${JAIL_NAME}" || true
