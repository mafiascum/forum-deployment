#!/bin/bash
set -euo pipefail

cd "$(dirname "$0")/.."

SERVICE="${BANNERS_SERVICE:-web}"

ALLOW_PROD_FLAG=""
POSITIONAL=()
for arg in "$@"; do
    case "$arg" in
        --allow-prod) ALLOW_PROD_FLAG="--allow-prod" ;;
        *) POSITIONAL+=("$arg") ;;
    esac
done
set -- "${POSITIONAL[@]}"

CSV_IN="${1:-data.csv}"
CSV_OUT="${2:-banners_skipped.csv}"

if [ ! -f "$CSV_IN" ]; then
    echo "ERROR: input CSV not found: $CSV_IN" >&2
    exit 1
fi

env=$(docker compose exec -T "$SERVICE" printenv MAFIASCUM_ENVIRONMENT 2>/dev/null | tr -d '\r\n' || true)
if [ "$env" != "local" ]; then
    if [ -z "$ALLOW_PROD_FLAG" ] && [ "${BANNERS_ALLOW_PROD:-}" != "1" ]; then
        echo "ABORT: container '$SERVICE' MAFIASCUM_ENVIRONMENT is '$env'." >&2
        echo "Re-run with --allow-prod (or BANNERS_ALLOW_PROD=1) to proceed." >&2
        exit 1
    fi
    echo "WARNING: running against non-local environment '$env'." >&2
fi

WORKDIR=/tmp/banners_import
docker compose exec -T "$SERVICE" mkdir -p "$WORKDIR"
docker compose cp "$CSV_IN" "${SERVICE}:${WORKDIR}/data.csv"
docker compose cp scripts/populate_banners.php "${SERVICE}:${WORKDIR}/populate_banners.php"

docker compose exec -T \
    ${ALLOW_PROD_FLAG:+-e BANNERS_ALLOW_PROD=1} \
    "$SERVICE" \
    php "${WORKDIR}/populate_banners.php" "${WORKDIR}/data.csv" "${WORKDIR}/skipped.csv"

docker compose cp "${SERVICE}:${WORKDIR}/skipped.csv" "$CSV_OUT"
echo "Skipped rows written to: $CSV_OUT"
