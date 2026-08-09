# ADR-013 — Diffusion publique des médias éditoriaux (Phase 9B)

- Statut : accepté
- Date : 2026-08-09
- Décideurs : équipe Devzair

## Contexte

La Phase 9A a livré le socle de téléversement admin des médias
(bibliothèque `/admin/media`, upload validé, stockage privé sur volume
`api_var`, prévisualisation authentifiée `/admin/media/{id}/preview`).
La Phase 9B ajoute la première consommation publique : un article publié
peut porter une image principale (`hero_image`), qui doit être servie à
tout visiteur sur `<img src="…">` d'une page `/ressources/{slug}`, dans
les balises Open Graph et dans le JSON-LD `BlogPosting`.

Trois questions structurent l'ADR :

1. **Qui sert le binaire au public ?** Nuxt, Caddy, Symfony ou un CDN
   externe ?
2. **Quel gate d'autorisation applique-t-on ?** Aucune (accès UUID
   « privé par obscurité »), signature d'URL, ou vérification côté serveur
   qu'au moins un article publié référence le média ?
3. **Quelle politique de cache HTTP ?** `immutable` (contenu binaire
   stable), `max-age` court avec revalidation, ou pas de cache ?

## Options étudiées

### Diffusion du binaire

1. **Symfony route publique dédiée (`GET /media/{id}`), même origine que
   `/api/*` et `/admin/*`** (retenu). Le contrôleur
   `GetPublicArticleMediaController` valide l'accès (voir gate), lit
   l'asset via `PublicMediaStreamerInterface` (implémentation adaptateur
   dans le contexte borné `EditorialMedia`) et streame le fichier depuis
   le volume privé `api_var`. Rien n'est exposé sur le volume web
   statique, aucun binaire n'est proxifié via Nuxt/Nitro.
2. Copie/publication du binaire vers `apps/web/public/media/` au moment
   de la publication de l'article. Rejeté : introduit un état
   secondaire à réconcilier (que se passe-t-il en cas de rollback de
   publication, de restore d'archivé, de partage inter-articles ?),
   contourne l'unique frontière de confiance côté Symfony, et brise le
   principe « pages orchestrent, composants présentent, repositories
   accèdent aux données » — Nuxt n'a pas à connaître le stockage média.
3. Proxy Nitro (`/_media/{id}` → `api:8000/media/{id}`). Rejeté :
   ajoute un saut réseau, une couche de cache Nitro à invalider (cf.
   ADR-011), et dilue la responsabilité — le binaire quitte de toute
   façon Symfony vers Caddy vers le client, pas besoin d'un Nitro
   intermédiaire.
4. CDN externe (Cloudinary, imgix, S3 + CloudFront). Rejeté pour la
   Phase 9B : introduit un fournisseur tiers avant qu'on ait le trafic
   qui le justifie, contredit la posture souveraineté (règle 9 : pas de
   secret non contrôlé), et exigerait de dupliquer la politique
   d'autorisation côté CDN (edge functions ou URL signées).

### Gate d'autorisation

1. **Vérification serveur qu'au moins un article publié référence le
   média** (retenu). `PublicMediaGateInterface::isReferencedByPublishedArticle(mediaId, now)`
   interroge la table `editorial_article` (index sur `hero_media_id`)
   avec les filtres `status = 'published' AND published_at <= now`.
   Aucun article publié référençant → 404. Aucune divulgation par 400
   ou 403 (un UUID malformé ne renvoie pas d'erreur distincte d'un UUID
   inconnu).
2. Accès libre par UUID (« privé par obscurité »). Rejeté : viole
   l'invariant fonctionnel — un média téléversé mais jamais publié doit
   rester privé, et un article archivé doit voir son média redevenir
   inaccessible (règle métier explicite de la Phase 9B). L'UUID en URL
   n'est PAS un secret.
3. URL signée à durée limitée (`?token=…&exp=…`). Rejeté pour la
   Phase 9B : impose un secret côté serveur ET côté générateur d'URL
   (Nuxt/Symfony/JSON-LD), impose une politique de rotation, complique
   le SEO (les URLs ne peuvent plus être canoniques stables). La
   vérification côté serveur suffit tant que les articles publiés
   restent la seule source d'exposition publique.

### Politique de cache HTTP

1. **`Cache-Control: public, max-age=3600, must-revalidate` + ETag fort
   (SHA-256 du média) + Last-Modified** (retenu). Le binaire peut être
   caché pendant une heure côté navigateur/CDN, puis DOIT être revalidé
   auprès de l'origine via `If-None-Match`. La revalidation renvoie
   soit `304 Not Modified` (le contenu et l'accessibilité sont
   inchangés), soit un `200` avec un nouveau contenu (impossible en
   pratique — sha256 stable), soit un `404` (l'article référençant a
   été archivé).
2. `Cache-Control: public, max-age=86400, immutable`. **Explicitement
   proscrit.** Les octets d'un média donné sont bien immuables
   (sha256 stable → même contenu à chaque hit), MAIS
   **l'accessibilité publique** de la ressource ne l'est pas. Un
   article référençant le média peut être archivé, et cette même route
   doit alors basculer en 404. `immutable` autoriserait un client à
   réutiliser un `200` en cache indéfiniment sans revalidation ; il
   continuerait de servir un média que la porte SQL refuse désormais.
   Cette divergence entre le cache navigateur et l'état serveur
   violerait la promesse fonctionnelle Phase 9B (« un article archivé
   n'expose plus son image »).
3. `Cache-Control: no-store`. Rejeté : impose un round-trip complet
   pour chaque `<img>` d'une page publique, alourdit la LCP,
   surcharge Symfony sans justification — le contenu est
   revalidable à moindre coût grâce à l'ETag fort.

## Décision

- **Diffusion** : route Symfony publique `GET /media/{id}` sous la même
  origine que `/api/*` et `/admin/*`. Symfony est le seul serveur qui
  touche le volume `api_var`.
- **Gate** : `PublicMediaGateInterface::isReferencedByPublishedArticle`
  — 404 sans divulgation sur tout autre cas (UUID malformé, média
  inconnu, orphelin, référencé uniquement par un Draft ou un Archived).
- **Cache HTTP** :
  `Cache-Control: public, max-age=3600, must-revalidate` + ETag fort
  (`"<sha256>"`) + `Last-Modified`.
  `immutable` proscrit ; toute évolution vers un TTL long exige de
  résoudre au préalable l'invariant « archivage → 404 », par exemple
  via un versionnage du path (`/media/{id}/v{sha256}`) qui permettrait
  d'utiliser `immutable` sans risque de divergence.

## Conséquences

**Positives**

- Frontière de confiance unique : la même porte SQL (`PublicMediaGate`)
  gouverne la diffusion publique et le JSON-LD, éliminant tout risque
  de désynchronisation entre l'HTML rendu et le binaire servi.
- Rétrocompatible avec un futur CDN : le CDN pourra pointer sur
  `/media/{id}` en cache-through et honorer nativement les
  `must-revalidate` + `ETag`.
- Bounded contexts préservés : le contexte `Editorial` ignore le
  stockage physique ; il ne connaît que
  `PublicMediaStreamerInterface`, un port applicatif dont l'adaptateur
  vit dans `EditorialMedia`.

**Négatives**

- Chaque miniature revalidée toutes les heures via une requête
  conditionnelle. Coût mesuré négligeable (304 sans corps, ~200
  octets), mais notable si le trafic explose. Mitigation possible en
  Phase 10+ : `stale-while-revalidate` côté CDN, ou versionnage du
  path pour repasser sur `immutable`.
- Pas de FK au niveau base entre `editorial_article.hero_media_id` et
  `editorial_media_asset.id` : l'intégrité inter-contextes est portée
  au niveau applicatif (`MediaAssetLookupInterface::contains()` avant
  mutation) — voir Version20260810120000 pour la justification DDD.

**Risques**

- Un adaptateur qui contournerait la porte (`PublicMediaStreamer`
  appelé directement sans `PublicMediaGate`) exposerait un média
  orphelin. Mitigation : le contrôleur est LE SEUL consommateur du
  streamer côté public, et le test
  `GetPublicArticleMediaControllerTest` verrouille les 9 scénarios
  (malformé, inconnu, orphelin, draft-only, publié OK, ETag 304,
  If-Modified-Since 304, archive → 404, shared media).
- Un utilisateur qui aurait mis en cache navigateur un `200` avant la
  bascule vers `must-revalidate` (déploiement live) le revalidera au
  prochain hit — pas de purge nécessaire côté CDN Caddy (déploiement
  Phase 9B avant tout trafic public éditorial mesurable).

## Références

- Phase 9B (Roadmap `docs/08-ROADMAP.md`) — image principale
  d'article.
- `GetPublicArticleMediaController` (contexte `Editorial/Presentation`).
- Migration `Version20260810120000` — justification DDD de l'absence
  de FK au niveau DB.
- ADR-011 — cache Nitro côté SSR (Nitro NE cache PAS `/media/{id}`,
  qui reste direct-hit Symfony via Caddy).
