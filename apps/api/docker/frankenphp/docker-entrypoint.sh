#!/bin/sh
# Devzair API — entrypoint de production (Phase 12)
#
# Réchauffe le cache Symfony avec les vraies variables d'environnement
# injectées par compose.prod.yaml. Remplace le cache:warmup au build, qui
# échoue sans DATABASE_URL, TRUSTED_PROXIES et autres variables résolues
# à la compilation du container DI.
#
# Durée attendue : ~1-3 s (ne ralentit pas les cold starts significativement).
set -e

echo "[entrypoint] cache:warmup prod..."
php bin/console cache:warmup --env=prod --no-debug

exec "$@"
