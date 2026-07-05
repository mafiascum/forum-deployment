#!/bin/bash
set -euo pipefail

cd "$(dirname "$0")/.."

SERVICE="${BANNERS_SERVICE:-web}"

FLAGS=()
POSITIONAL=()
for arg in "$@"; do
    case "$arg" in
        --allow-prod|--dry-run) FLAGS+=("$arg") ;;
        *) POSITIONAL+=("$arg") ;;
    esac
done
set -- "${POSITIONAL[@]}"

CSV_IN="${1:-data.csv}"

if [ ! -f "$CSV_IN" ]; then
    echo "ERROR: input CSV not found: $CSV_IN" >&2
    exit 1
fi

ALLOW_PROD=""
DRY_RUN=""
for f in "${FLAGS[@]:-}"; do
    [ "$f" = "--allow-prod" ] && ALLOW_PROD="1"
    [ "$f" = "--dry-run" ]    && DRY_RUN="1"
done

env=$(docker compose exec -T "$SERVICE" printenv MAFIASCUM_ENVIRONMENT 2>/dev/null | tr -d '\r\n' || true)
if [ "$env" != "local" ]; then
    if [ -z "$ALLOW_PROD" ] && [ "${BANNERS_ALLOW_PROD:-}" != "1" ]; then
        echo "ABORT: container '$SERVICE' MAFIASCUM_ENVIRONMENT is '$env'." >&2
        echo "Re-run with --allow-prod (or BANNERS_ALLOW_PROD=1) to proceed." >&2
        exit 1
    fi
    echo "WARNING: running against non-local environment '$env'." >&2
fi

WORKDIR=/tmp/banners_import
docker compose exec -T "$SERVICE" mkdir -p "$WORKDIR"
docker compose cp "$CSV_IN" "${SERVICE}:${WORKDIR}/data.csv"
docker compose cp scripts/strip_rank_banners.php "${SERVICE}:${WORKDIR}/strip_rank_banners.php"

docker compose exec -T \
    ${ALLOW_PROD:+-e BANNERS_ALLOW_PROD=1} \
    "$SERVICE" \
    php "${WORKDIR}/strip_rank_banners.php" \
        ${DRY_RUN:+--dry-run} \
        "${WORKDIR}/data.csv"
