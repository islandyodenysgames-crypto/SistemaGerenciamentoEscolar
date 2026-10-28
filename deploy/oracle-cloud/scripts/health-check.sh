#!/usr/bin/env bash
set -Eeuo pipefail

APP_URL="${APP_URL:-https://escola.exemplo.br}"
MIN_FREE_MB="${MIN_FREE_MB:-2048}"
failed=0

for service in nginx php8.3-fpm mysql; do
  if ! systemctl is-active --quiet "$service"; then echo "ERRO: $service inativo" >&2; failed=1; fi
done

free_mb="$(df -Pm /var/www/sge | awk 'NR==2 {print $4}')"
if [[ "$free_mb" -lt "$MIN_FREE_MB" ]]; then echo "ERRO: apenas ${free_mb} MB livres" >&2; failed=1; fi

if ! curl --fail --silent --show-error --max-time 15 "$APP_URL/login" >/dev/null; then
  echo "ERRO: endpoint HTTP indisponível" >&2; failed=1
fi

exit "$failed"
