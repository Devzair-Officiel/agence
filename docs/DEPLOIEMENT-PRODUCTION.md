# Déploiement production — Devzair

Répertoire de travail sur le Raspberry Pi de production : `/srv/apps/devzair`

```sh
cd /srv/apps/devzair
git pull
```

> **Important :** en production, toujours utiliser explicitement `--env-file .env.prod -f compose.prod.yaml`.
> Ne pas utiliser simplement `docker compose ...`, car cela peut charger la configuration de développement et notamment `Dockerfile.dev`.

---

## 1. Modification uniquement du frontend Nuxt

```sh
docker compose --env-file .env.prod -f compose.prod.yaml build web

docker compose --env-file .env.prod -f compose.prod.yaml \
  up -d --no-deps --force-recreate web
```

Vérification :

```sh
docker compose --env-file .env.prod -f compose.prod.yaml ps web
```

---

## 2. Modification uniquement de l'API Symfony

```sh
docker compose --env-file .env.prod -f compose.prod.yaml build api

docker compose --env-file .env.prod -f compose.prod.yaml \
  up -d --no-deps --force-recreate api
```

Vérification :

```sh
docker compose --env-file .env.prod -f compose.prod.yaml ps api
```

---

## 3. Modification frontend + API

```sh
docker compose --env-file .env.prod -f compose.prod.yaml build web api

docker compose --env-file .env.prod -f compose.prod.yaml \
  up -d --no-deps --force-recreate web api
```

Vérification :

```sh
docker compose --env-file .env.prod -f compose.prod.yaml ps
```

---

## 4. Migration Doctrine (si des migrations ont été ajoutées)

Si le commit contient une ou plusieurs nouvelles migrations Doctrine, construire d'abord la nouvelle image API :

```sh
docker compose --env-file .env.prod -f compose.prod.yaml build api
```

Exécuter ensuite les migrations avec la nouvelle image :

```sh
docker compose --env-file .env.prod -f compose.prod.yaml \
  run --rm --no-deps api \
  php bin/console doctrine:migrations:migrate --no-interaction --env=prod
```

Puis recréer le conteneur API :

```sh
docker compose --env-file .env.prod -f compose.prod.yaml \
  up -d --no-deps --force-recreate api
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

Résultat attendu :

```json
{"status":"ok"}
```

Vérification du frontend :

```sh
curl -I https://devzair.fr/
```

Résultat attendu :

```text
HTTP/2 200
```

Vérification d'une ressource publiée :

```sh
curl -I https://devzair.fr/ressources/ameliorer-visibilite-locale-entreprise
```

Résultat attendu :

```text
HTTP/2 200
```

---

## 6. Vérification qu'aucun serveur Vite de développement n'est exposé

Après un déploiement du frontend :

```sh
curl -s https://devzair.fr/ressources/ameliorer-visibilite-locale-entreprise \
  | grep -o '\[vite\]' \
  | head
```

Résultat attendu : **aucune sortie**.

Si `[vite]` apparaît, vérifier immédiatement que le conteneur a été lancé avec `compose.prod.yaml` et non avec la configuration Docker Compose de développement.

---

## 7. Logs utiles

Logs frontend Nuxt / Nitro :

```sh
docker compose --env-file .env.prod -f compose.prod.yaml \
  logs --tail=100 web
```

Suivi en temps réel :

```sh
docker compose --env-file .env.prod -f compose.prod.yaml \
  logs -f --tail=0 web
```

Logs API Symfony :

```sh
docker compose --env-file .env.prod -f compose.prod.yaml \
  logs --tail=100 api
```

Suivi en temps réel :

```sh
docker compose --env-file .env.prod -f compose.prod.yaml \
  logs -f --tail=0 api
```

Pour arrêter un suivi de logs, utiliser `Ctrl+C`.

Ne pas utiliser `Ctrl+Z`, qui suspend seulement le processus.

---

## 8. Rappels importants

- Ne **jamais** faire `docker compose down -v` en production : cela peut supprimer les volumes persistants PostgreSQL, sessions et médias.
- Ne **pas** supprimer les volumes `postgres_data`, `api_media`, `api_sessions`.
- Ne **pas** utiliser `docker volume prune` ou `docker system prune --volumes` sans audit précis.
- Ne **pas** redémarrer le Caddy global pour un simple déploiement frontend ou API.
- Ne **pas** redémarrer Cloudflare Tunnel pour un simple déploiement frontend ou API.
- Utiliser `--force-recreate` uniquement sur les services concernés.
- Ne jamais utiliser `--force-recreate` sur `postgres` pour un déploiement applicatif normal.
- Utiliser `--no-deps` pour éviter de recréer inutilement les dépendances du service déployé.
- Toujours utiliser `--env-file .env.prod -f compose.prod.yaml` en production.

---

## 9. Commandes rapides de référence

### Frontend uniquement

```sh
cd /srv/apps/devzair
git pull

docker compose --env-file .env.prod -f compose.prod.yaml build web

docker compose --env-file .env.prod -f compose.prod.yaml \
  up -d --no-deps --force-recreate web

docker compose --env-file .env.prod -f compose.prod.yaml ps web

curl -I https://devzair.fr/
```

### API uniquement

```sh
cd /srv/apps/devzair
git pull

docker compose --env-file .env.prod -f compose.prod.yaml build api

docker compose --env-file .env.prod -f compose.prod.yaml \
  up -d --no-deps --force-recreate api

docker compose --env-file .env.prod -f compose.prod.yaml ps api

curl -sS https://devzair.fr/api/health
```

### Frontend + API

```sh
cd /srv/apps/devzair
git pull

docker compose --env-file .env.prod -f compose.prod.yaml build web api

docker compose --env-file .env.prod -f compose.prod.yaml \
  up -d --no-deps --force-recreate web api

docker compose --env-file .env.prod -f compose.prod.yaml ps

curl -sS https://devzair.fr/api/health
curl -I https://devzair.fr/
```
