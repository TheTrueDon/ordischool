#!/usr/bin/env bash
set -euo pipefail
set +x

if [[ "${ORDI_ENV:-}" != "test" ]]; then
  echo "Abbruch: Restore-Smoke-Test läuft ausschließlich mit ORDI_ENV=test." >&2
  exit 2
fi
if [[ "${ORDI_DB_NAME:-}" != *_test ]]; then
  echo "Abbruch: ORDI_DB_NAME muss auf _test enden." >&2
  exit 2
fi
for key in ORDI_DB_HOST ORDI_DB_PORT ORDI_DB_USER ORDI_DB_PASSWORD; do
  if [[ -z "${!key:-}" ]]; then
    echo "Abbruch: $key fehlt." >&2
    exit 2
  fi
done

export MYSQL_PWD="$ORDI_DB_PASSWORD"
tmpdir="$(mktemp -d)"
trap 'rm -rf "$tmpdir"; unset MYSQL_PWD' EXIT
mysql_cmd=(mysql --protocol=tcp --host="$ORDI_DB_HOST" --port="$ORDI_DB_PORT" --user="$ORDI_DB_USER" "$ORDI_DB_NAME")
dump_cmd=(mysqldump --protocol=tcp --host="$ORDI_DB_HOST" --port="$ORDI_DB_PORT" --user="$ORDI_DB_USER" --single-transaction --skip-lock-tables --no-tablespaces --skip-comments "$ORDI_DB_NAME")

"${mysql_cmd[@]}" <<'SQL'
DROP TABLE IF EXISTS restore_probe;
CREATE TABLE restore_probe (id INT PRIMARY KEY, payload VARCHAR(80) NOT NULL) ENGINE=InnoDB;
INSERT INTO restore_probe (id, payload) VALUES (1, 'synthetic-restore-check');
SQL
"${dump_cmd[@]}" restore_probe > "$tmpdir/restore_probe.sql"
"${mysql_cmd[@]}" -e 'DROP TABLE restore_probe'
"${mysql_cmd[@]}" < "$tmpdir/restore_probe.sql"
restored="$("${mysql_cmd[@]}" --batch --skip-column-names -e "SELECT COUNT(*) FROM restore_probe WHERE id = 1 AND payload = 'synthetic-restore-check'")"
if [[ "$restored" != "1" ]]; then
  echo "Restore-Prüfung fehlgeschlagen." >&2
  exit 1
fi
"${mysql_cmd[@]}" -e 'DROP TABLE restore_probe'
echo "Synthetischer Datenbank-Backup/Restore-Smoke-Test erfolgreich."
