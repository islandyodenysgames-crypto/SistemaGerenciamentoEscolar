#!/usr/bin/env bash
set -Eeuo pipefail

ENV_FILE="${DUCKDNS_ENV_FILE:-/etc/sge/duckdns.env}"
source "$ENV_FILE"

: "${DUCKDNS_DOMAIN:?DUCKDNS_DOMAIN ausente}"
: "${DUCKDNS_TOKEN:?DUCKDNS_TOKEN ausente}"

public_ip="$(curl --fail --silent --show-error --max-time 15 https://api.ipify.org)"
response="$(curl --fail --silent --show-error --max-time 20 \
    "https://www.duckdns.org/update?domains=${DUCKDNS_DOMAIN}&token=${DUCKDNS_TOKEN}&ip=${public_ip}&verbose=true")"

if [[ "${response%%$'\n'*}" != "OK" ]]; then
    echo "Falha ao atualizar DuckDNS" >&2
    exit 1
fi

echo "DuckDNS atualizado para ${public_ip}"
