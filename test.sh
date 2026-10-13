#!/usr/bin/env bash
set -Eeuo pipefail
set +x

ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker wurde nicht gefunden. Installiere und starte Docker Desktop oder Docker Engine und führe test.sh erneut aus." >&2
  exit 1
fi
if ! docker compose version >/dev/null 2>&1; then
  echo "Docker Compose v2 fehlt. Bitte Docker Compose installieren und erneut versuchen." >&2
  exit 1
fi
if ! docker info >/dev/null 2>&1; then
  echo "Docker läuft nicht oder der aktuelle Benutzer darf Docker nicht verwenden. Starte Docker und versuche es erneut." >&2
  exit 1
fi
for command_name in od tr mktemp seq; do
  if ! command -v "$command_name" >/dev/null 2>&1; then
    echo "Benötigtes Systemprogramm fehlt: $command_name" >&2
    exit 1
  fi
done

random_hex() {
  od -An -N "$1" -tx1 /dev/urandom | tr -d ' \n'
}

ENV_FILE="$(mktemp "${TMPDIR:-/tmp}/ordischool-test-env.XXXXXXXX")"
chmod 600 "$ENV_FILE"
PROJECT_NAME="ordischool_test_$(date +%s)_$$"
TEST_PORT="${ORDI_TEST_PORT:-8080}"
if [[ ! "$TEST_PORT" =~ ^[1-9][0-9]{0,4}$ ]] || (( TEST_PORT > 65535 )); then
  echo "ORDI_TEST_PORT muss eine Portnummer zwischen 1 und 65535 sein." >&2
  rm -f "$ENV_FILE"
  exit 1
fi
ADMIN_EMAIL="test-admin@example.invalid"
ADMIN_PASSWORD="$(random_hex 20)"

# Ignore any OrdiSchool variables already exported by the user's shell. The
# throwaway configuration below must remain isolated from another project.
unset ORDI_ENV ORDI_APP_KEY ORDI_SESSION_SECURE_COOKIE ORDI_DB_HOST ORDI_DB_PORT
unset ORDI_DB_NAME ORDI_DB_USER ORDI_DB_PASSWORD ORDI_DB_ROOT_PASSWORD ORDI_DB_PORT_HOST
unset ORDI_BOOTSTRAP_ADMIN_EMAIL ORDI_BOOTSTRAP_ADMIN_PASSWORD ORDI_TEST_PORT

cat >"$ENV_FILE" <<EOF
ORDI_DB_NAME=ordischool_test
ORDI_DB_USER=ordischool_test
ORDI_DB_PASSWORD=$(random_hex 32)
ORDI_DB_ROOT_PASSWORD=$(random_hex 32)
ORDI_APP_KEY=$(random_hex 32)
ORDI_BOOTSTRAP_ADMIN_EMAIL=$ADMIN_EMAIL
ORDI_BOOTSTRAP_ADMIN_PASSWORD=$ADMIN_PASSWORD
ORDI_TEST_PORT=$TEST_PORT
EOF

dc() {
  docker compose --env-file "$ENV_FILE" --project-name "$PROJECT_NAME" \
    --project-directory "$ROOT_DIR" -f "$ROOT_DIR/compose.yaml" "$@"
}

cleanup() {
  local status=$?
  trap - EXIT INT TERM
  dc down --volumes --remove-orphans >/dev/null 2>&1 || true
  rm -f "$ENV_FILE"
  echo
  echo "OrdiSchool-Testumgebung und temporäre Datenbank wurden beendet und entfernt."
  return "$status"
}
trap cleanup EXIT
trap 'exit 130' INT TERM

echo "Baue OrdiSchool und starte die temporäre MySQL-Datenbank …"
echo "Beim ersten Start lädt Docker PHP und MySQL herunter; das kann einige Minuten dauern."
dc up -d --build

APP_CID=""
for attempt in $(seq 1 90); do
  APP_CID="$(dc ps -q app 2>/dev/null || true)"
  if [[ -n "$APP_CID" ]]; then
    HEALTH="$(docker inspect --format '{{if .State.Health}}{{.State.Health.Status}}{{else}}{{.State.Status}}{{end}}' "$APP_CID" 2>/dev/null || true)"
    if [[ "$HEALTH" == "healthy" ]]; then
      break
    fi
    if [[ "$HEALTH" == "exited" || "$HEALTH" == "unhealthy" ]]; then
      echo "Die Anwendung konnte nicht starten. Containerprotokoll:" >&2
      dc logs app >&2 || true
      exit 1
    fi
  fi
  sleep 2
done

if [[ -z "$APP_CID" || "${HEALTH:-}" != "healthy" ]]; then
  echo "Zeitüberschreitung beim Start. Containerprotokoll:" >&2
  dc logs >&2 || true
  exit 1
fi

URL="http://127.0.0.1:${TEST_PORT}/login"
echo
echo "OrdiSchool läuft: $URL"
echo "Testkonto:       $ADMIN_EMAIL"
echo "Testpasswort:    $ADMIN_PASSWORD"
echo
echo "Die Zugangsdaten gelten nur für diesen Testlauf. Beende ihn mit Strg+C; Container und Datenbankvolume werden dann gelöscht."

OPEN_COMMAND=""
if command -v xdg-open >/dev/null 2>&1; then
  OPEN_COMMAND=xdg-open
elif command -v open >/dev/null 2>&1; then
  OPEN_COMMAND=open
fi

if [[ "$EUID" -eq 0 && -n "${SUDO_USER:-}" && "$SUDO_USER" != root && "$OPEN_COMMAND" == xdg-open ]]; then
  if command -v runuser >/dev/null 2>&1 && command -v getent >/dev/null 2>&1 && command -v cut >/dev/null 2>&1; then
    USER_ID="$(id -u "$SUDO_USER")"
    USER_HOME="$(getent passwd "$SUDO_USER" | cut -d: -f6)"
    runuser -u "$SUDO_USER" -- env \
      HOME="$USER_HOME" \
      DISPLAY="${DISPLAY:-}" WAYLAND_DISPLAY="${WAYLAND_DISPLAY:-}" XAUTHORITY="${XAUTHORITY:-}" \
      XDG_RUNTIME_DIR="/run/user/$USER_ID" \
      DBUS_SESSION_BUS_ADDRESS="unix:path=/run/user/$USER_ID/bus" \
      xdg-open "$URL" >/dev/null 2>&1 &
  else
    echo "Browser bitte manuell öffnen: $URL"
  fi
elif [[ -n "$OPEN_COMMAND" ]]; then
  "$OPEN_COMMAND" "$URL" >/dev/null 2>&1 &
else
  echo "Browser bitte manuell öffnen: $URL"
fi

dc logs --follow app
