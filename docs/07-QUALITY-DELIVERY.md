# Analytics, tests et livraison

> Plan de mesure, assurance qualité, navigateurs, environnements et CI/CD.

## 18. Analytics et mesure

### Principe

Mesurer uniquement ce qui sert une décision.

### Événements

- envoi réussi du formulaire ;
- erreur de formulaire ;
- clic e-mail ;
- clic téléphone ;
- CTA principal ;
- consultation d’étude de cas ;
- passage d’une page service au contact ;
- téléchargement réel ;
- prise de rendez-vous si ajoutée.

### Qualité des données

- plan de marquage versionné ;
- environnement de test exclu ;
- trafic interne filtré selon méthode licite ;
- aucune donnée personnelle dans les URL ou événements ;
- noms d’événements stables ;
- consentement respecté ;
- tests avant mise en production ;
- documentation des changements.

### Tableaux de bord

- acquisition ;
- comportement ;
- conversion ;
- SEO ;
- performance ;
- disponibilité ;
- erreurs ;
- GEO identifiable.

---

## 19. Tests et assurance qualité

## 19.1 Tests automatisés

Selon la pile :

- tests unitaires ;
- intégration ;
- composants ;
- API ;
- formulaires ;
- permissions ;
- génération des métadonnées ;
- sitemap ;
- robots.txt ;
- données structurées ;
- redirections ;
- tests end-to-end des parcours critiques ;
- tests d’accessibilité automatisés ;
- analyse statique ;
- dépendances ;
- recherche de secrets.

## 19.2 Tests manuels

- contenus et orthographe ;
- navigation ;
- mobile ;
- clavier ;
- lecteur d’écran sur parcours critiques ;
- formulaires ;
- e-mails ;
- erreurs ;
- liens ;
- partage social ;
- impression si utile ;
- consentement ;
- indexabilité ;
- données structurées ;
- performance ;
- sécurité de configuration ;
- restauration de sauvegarde.

## 19.3 Navigateurs

Définir une matrice basée sur l’audience. Par défaut :

- versions récentes de Chrome, Edge, Firefox et Safari ;
- Safari iOS ;
- Chrome Android.

Documenter toute incompatibilité acceptée.

---

## 20. CI/CD et environnements

### Environnements

- local ;
- test automatisé ;
- préproduction protégée ;
- production.

### Préproduction

- accès protégé ;
- `noindex` en en-tête ou authentification ;
- absence du sitemap public de production ;
- données de test non sensibles ;
- e-mails redirigés ou désactivés ;
- clés distinctes.

### Pipeline minimal

1. installation reproductible ;
2. lint ;
3. analyse de types ;
4. tests ;
5. build ;
6. analyse de dépendances ;
7. recherche de secrets ;
8. contrôle accessibilité/SEO de base ;
9. déploiement préproduction ;
10. smoke tests ;
11. approbation ;
12. déploiement production ;
13. smoke tests production ;
14. possibilité de rollback.

### Jobs GitHub Actions actifs

| Workflow                              | Déclencheur (paths)                | Contrôles                                                        |
|---------------------------------------|------------------------------------|------------------------------------------------------------------|
| `.github/workflows/web-quality.yml`   | `apps/web/**`                      | Node 24 — lint + typecheck + vitest + build + Playwright (Chromium) |
| `.github/workflows/api-quality.yml`   | `apps/api/**`                      | PHP 8.4 (ext `pdo_pgsql`) + service `postgres:17-alpine` — `composer validate --strict` + `lint:yaml config` + `lint:container` + `doctrine:migrations:migrate --env=test` + `doctrine:schema:validate --skip-sync` + PHPUnit |

Playwright en CI : `workers: 1`, `retries: 1`, build Nitro préalable
(`npm run build && node .output/server/index.mjs`), reporter `line`
distinguant `passed / flaky / failed` en stdout, plus `github` et `html`
(artefact uploadé sur échec). Aucun test ne s’appuie sur `networkidle`
(remplacé par des attentes déterministes sur des éléments hydratés).

### Diagnostic pré-déploiement (Phase 6C)

La commande `bin/console app:contact:check` (voir
`docs/adr/ADR-008-mailer-ovhcloud-turnstile-optionnel.md`) invoque le
service pur `ContactConfigurationValidator` et retourne un code de sortie
`0` (succès ou avertissements seuls) ou `1` (erreurs bloquantes).
Aucune I/O réseau, aucun envoi email, aucun secret ni DSN complet en
sortie. Rôle : vérification reproductible en dev, CI et sur l’image de
production avant qu’un formulaire réel n’atteigne SMTP.

Contrôles unitaires associés (PHPUnit, sans stack Symfony réelle) :
`ContactConfigurationValidatorTest` (14 règles), `ContactCheckCommandTest`
(exit codes + non-fuite de secrets), `SymfonyContactMessageSenderTest`
(mapping `TransportExceptionInterface` → `ContactTemporarilyUnavailableException`),
`ContactSubmissionControllerTest::testMailerFailureReturns503TemporaryError…`
(échec SMTP → 503 sans perte du payload), `ContactLoggingTest`
(warning `contact.mailer_unavailable` sans PII).

Contrôles E2E associés (Playwright, backend mocké via `page.route`) :
scénario 503 `temporary_error` (bandeau verbatim + valeurs préservées) et
scénario « Turnstile désactivé → aucun script Cloudflare chargé, aucune
requête vers `challenges.cloudflare.com` », qui verrouille le contrat
`NUXT_PUBLIC_TURNSTILE_ENABLED=false` par défaut. La checklist de mise
en production `docs/checklists/PRODUCTION-CONTACT.md` complète ces
contrôles automatisés par une séquence manuelle contrôlée (un envoi
test unique sur OVHcloud).

### Suites PHPUnit du domaine `Editorial` (Phase 8A)

Découpe alignée sur les 4 couches de `apps/api/src/Editorial/` :

- **Domain unit** — `ArticleSlugTest`, `AuthorTest`, `SeoMetadataTest`,
  `ExpertiseIdentifierTest`, `ArticleTest` (invariants publish/archive
  idempotents, `Published ⇔ publishedAt non null`, dédoublonnage des
  `expertiseIds`). Aucune I/O, aucun conteneur Symfony.
- **Application unit** — `ListPublishedArticlesHandlerTest` (8 cas dont
  ordre stable `publishedAt DESC, id DESC` et pagination hors bornes)
  et `GetPublishedArticleHandlerTest`. Pilotés par
  `InMemoryArticleRepository` (support test), aucune connexion base.
- **Infrastructure integration** — `DoctrineArticleRepositoryTest`
  (`KernelTestCase`, transaction rollback en `setUp`/`tearDown` sur
  `devzair_test`) : filtre `status = Published`, tri stable,
  `getPublishedBySlug`, `listPublished + countPublished` cohérents.
- **Presentation functional** — `ListPublishedArticlesControllerTest`
  et `GetPublishedArticleControllerTest` (`WebTestCase`, base
  vidée via `DELETE FROM editorial_article`) : contrat 200/400/404,
  headers `Cache-Control: public, max-age=60, s-maxage=300` sur 200 et
  `no-store` sur 4xx, `X-Request-Id` UUID v7, payload JSON.
- **Support tests** — `FixedClock`, `InMemoryArticleRepository`,
  `ArticleBuilder` (patterns partagés, `apps/api/tests/Editorial/Support/`).

Aucune donnée fictive n'est produite (règle 1 AGENTS.md) : les tests
construisent leurs articles via `ArticleBuilder` avec des valeurs
neutres (`Article de test`, `article-de-test`, `contenu court`) et
nettoient la table après chaque cas.

### Suites PHPUnit ajoutées en Phase 8B1

Alignées sur les nouvelles couches (`Application/Markdown`,
`Application/Command`, `Infrastructure/Markdown`, `Presentation/Console`,
`Presentation/Http/ConditionalCache`) :

- **Markdown unit** — `MarkdownSecurityPolicyTest` (garde-fou : échoue
  si `html_input=strip` / `allow_unsafe_links=false` / limites de
  nesting ou de délimiteurs sont affaiblis), `MarkdownContentValidatorTest`
  (24 cas dont data provider `testRejectsDangerousUrls` sur les schémas
  `javascript:`, `data:`, `vbscript:`, `file:`, `ftp:` — regex Unicode
  `#Schéma d'URL "[^"]+" interdit#u` pour matcher le multi-byte `é`),
  `MarkdownArticleFileParserTest` (BOM refusé, taille > 512 Kio refusée,
  YAML manquant / invalide, front matter reconstruit), `ArticleFrontMatterTest`,
  `CommonMarkArticleRendererTest`. Aucune I/O externe.
- **Command handlers** — `ImportArticleFromMarkdownHandlerTest` (×4 :
  import draft + flush, `--dry-run` ne persiste rien, refus slug
  dupliqué, refus fichier contenant du HTML) et
  `PublishArticleBySlugHandlerTest` (×5 : publish à `Clock::now()`,
  publish avec date passée explicite, refus date future, `ArticleNotFoundException`
  sur slug inconnu, idempotence sur article déjà publié).
  Pilotés par `InMemoryArticleRepository` + `FixedClock` + trait
  `EntityManagerStub` (cf. DEC-080).
- **CLI functional** — `ImportArticleCommandTest` (×5) et
  `PublishArticleCommandTest` (×6) via `CommandTester` : exit codes
  0/1, output SymfonyStyle, gestion des options `--dry-run` /
  `--published-at`, arguments malformés.
- **Conditional cache functional** — `GetPublishedArticleConditionalCacheTest`
  (×4 : ETag faible + `Last-Modified` émis, 304 sur `If-None-Match`,
  304 sur `If-Modified-Since`, ETag différent par article) et
  `ListPublishedArticlesConditionalCacheTest` (×3 : ETag faible **sans**
  `Last-Modified`, 304 sur `If-None-Match`, ETag différent par page).
  Vérifient explicitement `X-Request-Id` non vide sur les réponses 304
  (cf. DEC-078).
- **Support tests** — `MarkdownFixture` (fabrique de fichiers Markdown
  temporaires avec YAML valide / corps invalide), trait
  `EntityManagerStub` (`entityManagerExpectingFlush()`/`entityManagerExpectingNoFlush()`
  en méthodes d'instance — cf. DEC-080).

Suite `Editorial` complète (Phase 8A + 8B1) : **117 tests, 259 assertions**.
Aucun contenu Markdown fictif n'est commité — les tests construisent
leurs fixtures dans `/tmp/` via `MarkdownFixture` et nettoient après
chaque cas.

### Suites PHPUnit ajoutées en Phase 8C2

Backend uniquement — aucune route HTTP admin, aucun template Twig,
aucune migration. Portée strictement `apps/api/src/Editorial/{Domain,Application,Infrastructure}/`.

- **Domain unit étendu** — `ArticleTest` +23 cas couvrant chaque
  mutation (`changeTitle`, `changeExcerpt`, `rewriteBody`, `changeSeo`,
  `changeAuthor`, `changeExpertises`, `restore`) : mise à jour +
  `updatedAt` bump, no-op quand la valeur est identique, rejet quand
  l'article n'est pas `Draft` (`ArticleNotEditableException`), rejet
  quand `$now < updatedAt` (garde-fou horloge monotone),
  `restore` × 4 (`Archived → Draft` reset `publishedAt`, `Draft`
  idempotent, `Published` refusé via `InvalidArticleTransitionException`,
  article restauré redevient éditable).
- **Application `Command` handlers** — `UpdateDraftArticleHandlerTest`
  ×11 (dont `testValidatesAllBeforeMutating` qui prouve l'atomicité :
  un titre valide fourni conjointement à un couple SEO invalide ne
  modifie ni le titre ni `updatedAt`), `ArchiveArticleHandlerTest` ×4
  (publié → archivé + flush, brouillon → archivé, déjà archivé
  idempotent sans flush, 404 sur UUID inconnu),
  `RestoreArticleHandlerTest` ×4 (archivé → brouillon + `publishedAt=null`,
  brouillon idempotent, publié refusé, 404 sur UUID inconnu).
  Pilotés par `InMemoryArticleRepository` + `FixedClock` + trait
  `EntityManagerStub`, aucune connexion base.
- **Infrastructure integration** — `DoctrineArticleRepositoryTest` +2
  cas sur `findById` (retourne l'article quel que soit son statut,
  retourne `null` sur UUID inconnu).
- **Markdown validator** — `MarkdownContentValidatorTest` +2 cas sur
  la nouvelle constante partagée `MAX_BODY_BYTES = 524_288` (accepte
  la limite exacte, refuse au-delà avec message `trop volumineux`).

Suite `Editorial` complète après Phase 8C2 : **154 tests, 340 assertions**.
Aucun contenu Markdown fictif n'est commité — les tests construisent
leurs fixtures dans `/tmp/` via `MarkdownFixture` et nettoient après
chaque cas.

### Suites PHPUnit ajoutées en Phase 8C3

IHM éditoriale Twig SSR sous `/admin/articles/**` — nouveaux tests
d'application (handlers Command/Query), d'infrastructure (adaptateur
Doctrine admin) et de présentation (contrôleurs `WebTestCase`).

- **Application `Query` handlers admin** —
  `ListAdminArticlesHandlerTest` (pagination + filtre statut + ordre
  stable `updated_at DESC, id DESC`), `GetAdminArticleForEditHandlerTest`.
- **Application `Command` handlers admin** —
  `CreateDraftArticleHandlerTest` : validation atomique de tous les VOs
  (échec avant `save`), pré-check via `findBySlug` avec 409 domaine,
  test de course concurrente (mock `EntityManager` levant
  `UniqueConstraintViolationException` sur `flush` → interception et
  traduction en `ArticleSlugAlreadyExistsException` — le vocabulaire
  Doctrine ne fuit jamais vers la présentation, cf. DEC-091).
  `PublishDraftArticleHandlerTest` : `Draft → Published`, refus strict
  `Published → *` et `Archived → *` via
  `InvalidArticleTransitionException::cannotPublishFrom($status)`,
  `ArticleNotFoundException::forId` sur UUID inconnu.
- **Infrastructure integration** —
  `DoctrineAdminArticleReadRepositoryTest` (KernelTestCase + rollback
  transaction) : pagination Doctrine, filtre statut, tri stable, DTO
  plat mappé (aucune fuite d'entité).
- **Presentation `WebTestCase`** — `AdminArticleListControllerTest`
  (rendu Twig, pagination, filtre statut), `AdminArticleCreateControllerTest`
  (GET + POST valide → PRG vers `/edit`, POST invalide → re-render 200
  avec `FormErrorBag`, slug déjà existant → alerte inline, CSRF
  invalide → 403), `AdminArticleEditControllerTest` (édition brouillon,
  slug absent du payload — immuabilité vérifiée côté serveur cf. DEC-091),
  `AdminArticleTransitionControllerTest` (POST publish/archive/restore,
  CSRF par action, throttling via `AdminActionRateLimiter` avec 429
  et `Retry-After`), `AdminArticleAuditLoggingTest` (canal Monolog
  `admin` : aucun email, aucun titre, aucun Markdown, uniquement UUID
  admin + UUID article + slug + statut).
- **Support** — `AdminHttpTestHelper` (login form-based réutilisable),
  `InMemoryAdminArticleReadRepository` (double comportement conforme au
  port + ordre stable).

Backend PHPUnit total après Phase 8C3 : **327 tests / 834 assertions**
verts sur 2 seeds aléatoires. `doctrine:schema:validate --skip-sync`
reste OK — aucune migration en Phase 8C3.

Frontend Vitest : **317/317** verts (aucune régression).

Playwright : **250/250 verts sur deux passes** (image
`mcr.microsoft.com/playwright:v1.62.1-noble --network host`,
`PLAYWRIGHT_BASE_URL=http://localhost:3001`, workers 1, retries 0).
Nouvelle suite `apps/web/test/e2e/admin-editorial.spec.ts` (5 tests
`describe.serial` — le firewall Symfony applique un throttling
`email+IP` qui interdit les logins parallèles, cf. Phase 8C1) :
liste vide accessible (Axe WCAG 2.2 AA), création brouillon → PRG,
édition brouillon persistée, cycle complet publish → archive → restore
avec extraction UUID depuis `page.url()`, headers de sécurité admin
(CSP `default-src 'none'`, `X-Frame-Options: DENY`, `X-Robots-Tag:
noindex, nofollow`, `Cache-Control: private, no-store`).

Orchestration E2E — nouveau script `scripts/e2e-admin-articles.sh`
symétrique à `scripts/e2e-fixtures.sh` et `scripts/e2e-admin-user.sh` :
`load` = purge préventive `DELETE FROM editorial_article WHERE slug
LIKE 'e2e-8c3-%'` (état propre si le `clear` précédent avait crashé),
`clear` = purge post-suite, `E2E_SLUG_PREFIX` codé en dur (jamais
TRUNCATE, jamais DELETE global — même triple pattern que DEC-086),
`ON_ERROR_STOP=1`. Le workflow `.github/workflows/web-quality.yml`
étend ses path triggers (`scripts/e2e-admin-articles.sh`) et exécute
`load` avant Playwright puis `clear` avec `if: always()` en fin, en
symétrie stricte avec les fixtures 8B2 et le compte admin 8C1.

### Déploiement

- migrations réversibles ou procédure de retour ;
- sauvegarde avant changement risqué ;
- journal de version ;
- contrôle des variables ;
- purge cache maîtrisée ;
- surveillance renforcée après livraison.

---

---

## Règle de maintenance

Ce fichier doit être modifié uniquement lorsque les règles de son domaine évoluent.  
Les tâches réalisées, décisions et blocages doivent être consignés dans `10-TRACKING.md`.
