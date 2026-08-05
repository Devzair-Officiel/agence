# ADR-010 — Pipeline Markdown éditorial CLI + cache HTTP conditionnel

- Statut : accepté
- Date : 2026-08-04
- Décideurs : équipe Devzair

## Contexte

La Phase 8A a mis en place la persistance PostgreSQL et l'API publique en
lecture (`GET /api/resources`, `GET /api/resources/{slug}`). Elle laisse
ouvertes deux questions :

1. **Comment alimenter la base ?** Aucun back-office n'est prévu à court
   terme ; aucun endpoint d'écriture HTTP n'est autorisé sur le domaine
   public ; l'agence rédige les contenus en Markdown localement.
2. **Comment protéger l'origine ?** Les vues sont paginables et
   individuelles ; sans validateur HTTP, chaque re-fetch renvoie 200 avec
   payload complet, y compris quand rien n'a bougé côté domaine.

La Phase 8B1 doit donc livrer un pipeline CLI d'import/publication
strictement local, plus un cache conditionnel (`ETag` / `Last-Modified`
/ `304`) sur les deux endpoints publics. Ni page Nuxt éditoriale, ni
back-office, ni endpoint d'écriture ne sont ajoutés — ils appartiennent
aux phases suivantes.

## Options étudiées

### Import de contenu

1. **Fichiers Markdown parsés par une CLI Symfony vers Postgres** (retenu).
   Contenu versionné côté rédacteur, workflow revue en PR, aucune
   surface d'attaque HTTP nouvelle.
2. Back-office web protégé (auth admin, éditeur riche). Coût élevé,
   surface d'attaque significative, pas d'usage justifié avant Phase 9.
3. Insertion SQL manuelle (psql). Non traçable, contourne toutes les
   invariants du domaine.
4. Endpoint HTTP `POST /api/resources` (même authentifié). Ajoute une
   surface d'attaque et une couche d'auth ; incompatible avec la règle
   Phase 8B1 « lecture seule côté HTTP public ».

### Rendu Markdown

1. **`league/commonmark` ^2.7, `html_input=strip`, `allow_unsafe_links=false`,
   sans `DisallowedRawHtmlExtension` mais avec rejet explicite à l'import
   de tout `HtmlBlock`/`HtmlInline`/URL non `[http, https, mailto, tel]`**
   (retenu). Double barrière : refus explicite au parse (échec d'import
   loggé, pas de contenu douteux stocké) + configuration défensive au
   rendu.
2. `HTMLPurifier` en aval. Utile pour du HTML entrant ; disproportionné
   pour du Markdown que nous contrôlons à l'import.
3. Confiance dans `html_input=strip` seul. Insuffisant : le rédacteur
   doit être averti du rejet, pas silencieusement voir son HTML supprimé.

### Cache HTTP

1. **`ETag` faible sur les deux endpoints ; `Last-Modified` uniquement sur
   le détail** (retenu). `request_id` (UUID v7) dans le corps JSON rend
   toute réponse byte-uniquement unique — l'ETag *fort* mentirait sur
   cette propriété. `Last-Modified` n'a de sens que pour une ressource
   unique, pas pour un tri paginé.
2. `ETag` fort. Faux positif garanti à cause du `request_id`.
3. `Cache-Control: no-store`. Renonce au cache CDN ; incompatible avec
   la volonté d'absorber le trafic de lecture derrière Caddy/OVH.
4. Purge programmatique via clé versionnée en cache Redis. Prématuré
   (pas de Redis en Phase 8) et redondant avec la validation HTTP.

### Publication programmée / dates futures

1. **Refus explicite en base et en lecture d'un `publishedAt` futur**
   (retenu). Aucun besoin métier de programmation à échéance en Phase
   8B1 ; l'invariant se pose maintenant pour éviter d'exposer un
   contenu programmé par erreur d'horloge serveur.
2. Autoriser une date future et filtrer à la lecture. Impose une
   discipline supplémentaire à chaque nouvelle requête ; sujet à
   régression (un handler oubliant le filtre).
3. Champ `publishedAt` optionnel dans le front matter, respecté à
   l'import. Ambigu : conflit possible entre date front matter et date
   d'import ; refusé pour n'avoir qu'une seule source de vérité (la
   commande `app:editorial:publish`).

## Décision

### Pipeline d'import

- Commandes CLI Symfony `app:editorial:import <path> [--dry-run]` et
  `app:editorial:publish <slug> [--published-at=<ISO8601>]`, exécutées
  dans le conteneur `api`, jamais exposées HTTP.
- Format : YAML front matter (délimité `---`) + corps Markdown.
- Front matter typé (`ArticleFrontMatter`) : `slug`, `title`, `excerpt`,
  `seo`, `author`, `expertises`. Le champ `publishedAt` (ou `published_at`)
  y est explicitement **interdit** — la publication est une action
  séparée. Les clés racines / SEO / auteur inconnues sont refusées.
- Parseur strict : fichier ≤ 512 Kio, UTF-8 (BOM refusé), YAML délimité.
- Validateur AST : rejet de tout `HtmlBlock` ou `HtmlInline` ; schémas
  d'URL restreints à `[http, https, mailto, tel]` ; URL relatives
  autorisées ; violations agrégées dans une seule
  `MarkdownValidationException`.
- Import « create only » : un slug déjà présent (peu importe le statut)
  fait échouer l'import. La mise à jour se fait par une action
  ultérieure explicite (post-Phase 8B1).
- Article importé toujours en `Draft`. Publication idempotente via
  `app:editorial:publish` (déjà publié → warning, exit 0).

### Sécurité du rendu

- `MarkdownSecurityPolicy` fige l'environnement CommonMark :
  `html_input=strip`, `allow_unsafe_links=false`, `max_nesting_level=15`,
  `max_delimiters_per_line=100`. Testé par un test dédié qui échouera
  si un développeur affaiblit la configuration.
- Le renderer `CommonMarkArticleRenderer` sert au dry-run CLI et
  prépare la Phase 8B2 (rendu HTML dans Nuxt) — pas de rendu HTML
  exposé côté HTTP en Phase 8B1.

### Cache HTTP conditionnel

- `ArticleETag::forDetail($view)` renvoie **la valeur brute** `sha256(id|updatedAt|v1)`
  (sans `W/` ni guillemets) ; c'est `Response::setEtag($value, weak: true)`
  qui produit l'entête final `W/"…"` conforme RFC 7232. Retourner déjà
  `W/"…"` provoquerait une double enveloppe (`W/"W/"…""`), Symfony wrappant
  toute valeur ne commençant pas par `"`.
- `ArticleListETag::forPage($items, $pagination)` = `sha256(page|perPage|total|v1 + id:updatedAt par item)`
  (même règle : valeur brute, `W/"…"` posé par `setEtag($v, weak: true)`).
- `Cache-Control: public, max-age=60, s-maxage=300` sur 200.
- `Cache-Control: no-store` sur 4xx.
- Sur 304, `X-Request-Id` est **réécrit explicitement** après
  `isNotModified()` (Symfony purge la plupart des entêtes non liés à la
  validation), pour préserver la corrélation logs même sans corps.
- Un préfixe de version (`v1`) permet une invalidation en masse si le
  contrat JSON évolue.

### Programmation

- `Article::publish($publishedAt, $now)` refuse `$publishedAt > $now`.
- `ArticleRepositoryInterface::{getPublishedBySlug,listPublished,countPublished}`
  reçoit `$now` et double-filtre `status=Published AND publishedAt <= :now`.
- Une seule méthode d'accès neutre au statut est ajoutée : `findBySlug`,
  strictement réservée à l'import (détection de collision) et à la
  publication (récupération d'un draft).

## Raisons

- **Réduire la surface d'attaque** : aucun nouvel endpoint HTTP ; aucune
  authentification à opérer en Phase 8B1 ; la CLI vit dans le conteneur
  `api` accessible uniquement à l'opérateur.
- **Rendre la revue humaine possible** : les articles vivent en
  Markdown dans le dépôt (à terme), donc en PR — traçabilité,
  relecture, changelog.
- **Séparer contrat de rendu et contrat de stockage** : le Markdown
  brut est stocké tel quel, le rendu HTML est calculé au moment
  utile (Nuxt Phase 8B2). Aucune décision de rendu n'est figée en base.
- **Cache conditionnel sans mentir** : le corps JSON contient un
  `request_id` unique ; un ETag fort serait faux. Le faible dit
  correctement « équivalent sémantiquement ».
- **Verrouiller la publication future** dès maintenant évite un
  déboire opérationnel (contenu programmé qui apparaît à cause d'un
  décalage d'horloge) sans coût significatif — la fonctionnalité n'est
  pas demandée.

## Conséquences

**Positives**

- Le pipeline est testable de bout en bout hors HTTP (tests handlers +
  CLI + parseur + validateur).
- Le cache CDN/reverse-proxy peut absorber une part significative du
  trafic ressources sans invalidation manuelle.
- La sécurité du rendu Markdown est verrouillée par un test dédié qui
  détectera toute régression de configuration.
- L'invariant « pas de publication future » se propage automatiquement à
  toutes les lectures futures grâce à la signature `$now` dans le port.

**Limites / risques**

- Un rédacteur ne peut pas relier deux articles par un lien interne
  au moment de l'import (l'URL relative est validée mais rien ne vérifie
  qu'elle existe). C'est acceptable : la contrainte relève de la revue
  éditoriale, pas de la sécurité.
- Le pipeline « create only » impose un flux explicit pour les
  corrections : suppression manuelle en base + réimport, ou commande
  dédiée à venir. Documenté comme choix, pas comme lacune.
- Le cache 60/300 s produit une latence propagée jusqu'à 5 minutes
  après une publication CLI. Acceptable pour un site éditorial ; on
  documentera comment purger côté proxy en Phase 10 si besoin.

## Références

- `apps/api/src/Editorial/Application/Markdown/` — VO + exceptions.
- `apps/api/src/Editorial/Infrastructure/Markdown/` — parseur, validateur,
  policy, renderer.
- `apps/api/src/Editorial/Application/Command/` — handlers Import/Publish.
- `apps/api/src/Editorial/Presentation/Console/` — commandes CLI.
- `apps/api/src/Editorial/Presentation/Http/ConditionalCache/` — calcul
  des ETag faibles.
- `apps/api/tests/Editorial/` — suite complète Phase 8B1.
- `docs/05-SECURITY-PRIVACY.md` — nouvelle section « Import Markdown ».
- `docs/06-ARCHITECTURE-CODE.md` §16.2 — pipeline import éditorial.
- `docs/07-QUALITY-DELIVERY.md` — gates PHPUnit à jour.
