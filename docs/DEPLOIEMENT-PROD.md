# Déploiement production — Devzair

Répertoire de travail sur le VPS : `/var/www/agence`

```sh
cd /var/www/agence
git pull
```

---

## 1. Modification uniquement du frontend Nuxt

```sh
docker compose --env-file .env.prod -f compose.prod.yaml build web

docker compose --env-file .env.prod -f compose.prod.yaml up -d --force-recreate web
```

---

## 2. Modification uniquement de l'API Symfony

```sh
docker compose --env-file .env.prod -f compose.prod.yaml build api

docker compose --env-file .env.prod -f compose.prod.yaml up -d --force-recreate api
```

---

## 3. Modification frontend + API

```sh
docker compose --env-file .env.prod -f compose.prod.yaml build web api

docker compose --env-file .env.prod -f compose.prod.yaml up -d --force-recreate api web
```

---

## 4. Migration Doctrine (si des migrations ont été ajoutées)

À exécuter **avant** de recréer le conteneur `api`, ou juste après si l'image
tourne déjà.

```sh
docker compose --env-file .env.prod -f compose.prod.yaml \
  exec api php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

Valider le schéma :

```sh
docker compose --env-file .env.prod -f compose.prod.yaml \
  exec api php bin/console doctrine:schema:validate --env=prod
```

> Si aucune migration n'a été ajoutée au commit, inutile de lancer ces commandes.

---

## 5. Vérifications après déploiement

État des conteneurs :

```sh
docker compose --env-file .env.prod -f compose.prod.yaml ps
```

Santé de l'API :

```sh
curl -sS https://devzair.fr/api/health
```

Résultat attendu : `{"status":"ok"}`

---

## Rappels importants

- Ne **jamais** faire `docker compose down -v` — cela supprime les volumes PostgreSQL, sessions et médias.
- Ne **pas** supprimer les volumes `postgres_data`, `api_media`, `api_sessions`.
- Ne **pas** redémarrer le Caddy global pour un déploiement frontend ou API.
- Utiliser `--force-recreate` uniquement sur les services concernés, jamais sur `postgres`.
