#!/usr/bin/env bash
set -Eeuo pipefail
umask 077

APP_ROOT="${APP_ROOT:-/var/www/sge/current}"
REMOTE="${BACKUP_REMOTE:?Defina BACKUP_REMOTE, por exemplo oci-sge:sge-backups}"
RECIPIENT_FILE="${BACKUP_AGE_RECIPIENT_FILE:-/etc/sge/backup-age-recipient.txt}"
RETENTION_DAYS="${BACKUP_RETENTION_DAYS:-30}"

[[ -r "$RECIPIENT_FILE" ]] || { echo "Destinatário age indisponível" >&2; exit 1; }
recipient="$(tr -d '[:space:]' < "$RECIPIENT_FILE")"
[[ "$recipient" == age1* ]] || { echo "Destinatário age inválido" >&2; exit 1; }

backup_path="$(cd "$APP_ROOT" && php console.php backup:create | tail -n 1)"
[[ -f "$backup_path" ]] || { echo "Backup local não foi criado" >&2; exit 1; }
encrypted="${backup_path}.age"
age --recipient "$recipient" --output "$encrypted" "$backup_path"
sha256sum "$encrypted" > "${encrypted}.sha256"
rclone copy "$encrypted" "${encrypted}.sha256" "$REMOTE" --immutable
rclone delete "$REMOTE" --min-age "${RETENTION_DAYS}d" --include '*.age' --include '*.sha256'
rm -f -- "$encrypted" "${encrypted}.sha256"
echo "Backup externo criptografado concluído: $(basename "$backup_path")"
